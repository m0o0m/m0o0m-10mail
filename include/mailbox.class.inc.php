<?php

/** 
 * GentleSource - mailbox.class.inc.php
 * 
 * Wrapper for the Pop3 class and mime decode functions. The latter is intended
 * to be a fall back process from PHP IMAP extension to PHP mailparse extension
 * to PEAR mime class (in that order).
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */
 
 
 
 
require_once 'pop3/pop3.php';
require_once 'Mail/mimeDecode.php';




/**
 * Receive and decode mails
 * 
 */
class t12l_mailbox
{
    
    
    
    /**
     * Delete messages
     */
    var $delete = true;
    
    
    /**
     * Pop3 class object
     */
    var $mail = null;
    
    
    /**
     * Mail Character Set
     */
    var $mail_character_set = null;
    
    
    /**
     * Mesages
     */
    var $message = array();
    
    
    /**
     * Mesage structure
     */
    var $structure = array();
    
// -----------------------------------------------------------------------------




    /**
     * Constructor
     * 
     */
    function t12l_mailbox($delete)
    {
        $this->mail = new pop3_class();
        $this->delete = $delete;
    }
    
// -----------------------------------------------------------------------------




    /**
     * Open connection to mailbox
     * 
     */
    function open($mailbox)
    {        
        $this->mail->hostname = $mailbox['hostname'];
        $this->mail->port     = $mailbox['port'];
        $this->mail->tls      = $mailbox['tls'];
        
        $res = $this->mail->Open($mailbox['hostname'], $mailbox['port']);
        if ($res != '') {
            t12l_system_debug::add_message('Mailbox Error', 'Could not open mailbox. (' . $res . ')', 'error');
            return false;
        }
        $res = $this->mail->Login($mailbox['user'], $mailbox['password']);
        if ($res != '') {
            t12l_system_debug::add_message('Mailbox Error', 'Could not login to mailbox. (' . $res . ')', 'error');
            return false;
        }
        return true;
    }
    
// -----------------------------------------------------------------------------




    /**
     * Number of e-mails in the mailbox
     * 
     */
    function number(&$number)
    {       
        $number = 0;
        $size   = 0;
        $res = $this->mail->Statistics($number, $size);
        if ($res != '') {
            t12l_system_debug::add_message('Mailbox Status', $res , 'debug');
            return false;
        }
        
        if ($number <= 0) {
            return false;
        }
    }
    
// -----------------------------------------------------------------------------




    /**
     * Fetch mail
     * 
     * @param $i 		Number of message to be fetched
     * @param $header	
     * @param $body	
     * 
     */
    function fetch_message($i)
    {
        $header = '';
        $body   = '';
               
        $res = $this->mail->ListMessages($i, 1);        
        $res = $this->mail->RetrieveMessage($i, $header, $body, -1);
        $message = join("\n", $header) . "\n\n" . join("\n", $body);
        $this->message[$i] = $message;
        return $message;
    }
    
// -----------------------------------------------------------------------------




    /**
     * Mark message for deletion from mailbox
     * 
     * @param $i Number of message to be deleted
     */
    function delete_message($i)
    {    
        if ($this->delete == false) {
            return false;
        }
        $this->mail->DeleteMessage($i);
    }
    
// -----------------------------------------------------------------------------




    /**
     * Delete all messages marked for deletion
     * 
     * @param $i Number of message to be deleted
     */
    function perform_delete()
    {    
//        $this->mail->ResetDeletedMessages();        
    }
    
// -----------------------------------------------------------------------------




    /**
     * Close connection to mailbox
     * 
     */
    function close()
    {    
        $this->mail->Close();        
    }
    
// -----------------------------------------------------------------------------




    /**
     * Mime decode
     * 
     */
    function mime_decode($i)
    {
        $input = array(
                    'input'             => $this->message[$i],
                    'include_bodies'    => true,
                    'decode_headers'    => true,
                    'decode_bodies'     => true,
                    );
        $structure = Mail_mimeDecode::decode($input);
        $this->structure[$i] = $structure;
//        t12l_print_a($structure);
    }
    
// -----------------------------------------------------------------------------




    /**
     * Get header
     * 
     */
    function get_header($i, $header)
    {
        $result = '';
        if (array_key_exists($header, $this->structure[$i]->headers)) {
            $result = $this->structure[$i]->headers[$header];
        }
        return $result;
    }
    
// -----------------------------------------------------------------------------




    /**
     * Get character set
     * 
     */
    function get_character_set($i)
    {
        if ($this->mail_character_set == null) {
            $this->get_body($i);
        }
        return $this->mail_character_set;
    }
    
// -----------------------------------------------------------------------------




    /**
     * Get body
     * 
     */
    function get_body($i)
    {
//        $result = '';
//        if (array_key_exists('body', $this->structure[$i])) {
//            $result = $this->structure[$i]->body;
//        }
//        return $result;
        $body = $this->fetch_body($this->structure[$i], $i);
        return $body;
    }
    
// -----------------------------------------------------------------------------




    /**
     * Fetch body and format it according to settings
     * 
     * @param $i Number of message to be fetched
     */
    function fetch_body($structure, $i)
    {    
        global $t12l;
        
        if ($t12l['message_body_format'] == 'complete') {
            if ($result = Mail_mimeDecode::_splitBodyHeader($this->message[$i])) {
                if (isset($result[1])) {
                    return $result[1];
                }
            }
            $body = $this->select_part($structure, 'plain');
            return $body;
        }
        if ($t12l['message_body_format'] == 'html') {
            $type = 'html';
            $body = $this->select_part($structure, $type);
            return $body;
        }
        if ($t12l['message_body_format'] == 'text') {
            $type = 'plain';
            $body = $this->select_part($structure, $type);
            return $body;
        }
        $body = $this->select_part($structure, $type);
        return $body;
    }
    
// -----------------------------------------------------------------------------




    /**
     * 
     */
    function select_part($struct, $type) 
    {
        $parttypes = array('text', 'multipart', 'message', 'application', 'audio', 'image', 'video', 'other');

        switch ($struct->ctype_primary):
            case 'text':
                // {{{
                // Get character set
                if (isset($struct->ctype_parameters['charset'])) {
                    $this->mail_character_set = $struct->ctype_parameters['charset'];
                }
                // }}}

                if ($struct->ctype_secondary == $type) {
                    return $struct->body;
                }
            case 'multipart':
                $r = array (); 
                $i = 0;
                
                if (isset($struct->parts)) {
                    foreach ($struct->parts as $part)
                    {
                        $this->select_part($part, $type);
                        if (strtolower($part->ctype_secondary) == $type) {
                            // {{{
                            // Get character set
                            if (is_array($part->ctype_parameters)) {
                                foreach ($part->ctype_parameters AS $param => $value)
                                {
                                    if ($param == 'charset') {
                                        $this->mail_character_set = $value;
                                    }
                                }
                            }
                            // }}}
                            
                            return $part->body;
                        }
                    }
                }
                
                if (isset($struct->body)) {
                    return $struct->body;
                }
                
                $body = '';
                return $body;
            default:
                $body = '';
                return $body;
        endswitch;
    }

// -----------------------------------------------------------------------------




    /**
     * Display error messages
     */
    function errors() 
    {
        $error = array($this->mail->error);
        return $error;
    }
    
// -----------------------------------------------------------------------------




} // End of class








?>
