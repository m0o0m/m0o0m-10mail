<?php

/** 
 * GentleSource - mailboximap.class.inc.php
 * 
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




/**
 * Receive and decode mails
 * 
 */
class t12l_mailboximap
{
    
    
    
    /**
     * Mailbox stream
     */
    var $stream = null;
    
    
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
    
    
    /**
     * Encodings
     */
    var $encoding = array(
                        '7bit',
                        '8bit',
                        'binary',
                        'base64',
                        'quoted-printable',
                        'other'
                        );
    
// -----------------------------------------------------------------------------




    /**
     * Constructor
     * 
     */
    function t12l_mailbox()
    {
    }
    
// -----------------------------------------------------------------------------




    /**
     * Open connection to mailbox
     * 
     */
    function open($details)
    {      
        global $t12l;
        $option = null;
//        if ($t12l['debug_mode'] != 'Y') {
//            $option = OP_SILENT;
//        }
        if ($this->stream = imap_open('{' . $details['hostname'] . ':' . $details['port'] . '/' . $details['path'] . '}INBOX', $details['user'], $details['password'], $option)) {
            return true;
        }
    }
    
// -----------------------------------------------------------------------------




    /**
     * Number of e-mails in the mailbox
     * 
     */
    function number(&$number)
    {    
        $number = imap_num_msg($this->stream);
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
        $structure  = array();
        $headers    = imap_headerinfo($this->stream, $i);
        $body       = imap_fetchbody($this->stream, $i, 1);
        $whole      = imap_fetchstructure($this->stream, $i);
        $structure['headers']   = $headers;
        $structure['body']      = $body;
        $structure['whole']      = $whole;
        $this->structure[$i]    = $structure;
//         t12l_print_a($structure);
    }
    
// -----------------------------------------------------------------------------




    /**
     * Mark message for deletion from mailbox
     * 
     * @param $i Number of message to be deleted
     */
    function delete_message($i)
    {    
        imap_delete($this->stream, $i);
    }
    
// -----------------------------------------------------------------------------




    /**
     * Delete all messages marked for deletion
     * 
     * @param $i Number of message to be deleted
     */
    function perform_delete()
    {    
        imap_expunge($this->stream);        
    }
    
// -----------------------------------------------------------------------------




    /**
     * Close connection to mailbox
     * 
     */
    function close()
    {    
        imap_close($this->stream);       
    }
    
// -----------------------------------------------------------------------------




    /**
     * Mime decode
     * 
     */
    function mime_decode($i)
    {
        // imap_mime_header_decode()
    }
    
// -----------------------------------------------------------------------------




    /**
     * Get header
     * 
     */
    function get_header($i, $header)
    {
        if (!is_object($this->structure[$i]['headers'])) {
            return false;
        }
        
        $object_vars = get_object_vars($this->structure[$i]['headers']);
        
        if ($header == 'to') {
            $header = 'toaddress';
        }
        
        if ($header == 'from') {
            $header = 'fromaddress';
        }
        
        if (!array_key_exists($header, $object_vars)) {                    
            return false;
        }
        $elements = imap_mime_header_decode($object_vars[$header]);
        $num = sizeof($elements);
        $result = '';
        for ($r = 0; $r < $num; $r++)
        {
            $result .= $elements[$r]->text;
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
        $structure  = imap_fetchstructure($this->stream, $i);
//        $body       = $this->decode_body(imap_body($this->stream, $i), $structure->encoding);
//        $body       = $this->decode_body($this->fetch_body($structure, $i), $structure->encoding);
        $body       = $this->fetch_body($structure, $i);
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
            $body = imap_fetchbody($this->stream, $i, 1);
            return $body;
        }
        if ($t12l['message_body_format'] == 'html') {
            $type = 'html';
            $body = imap_fetchbody($this->stream, $i, $this->select_part($structure, $type));
            $body = $this->decode_body($body, $this->select_part_encoding($structure, $type));
            return $body;
        }
        if ($t12l['message_body_format'] == 'text') {
            $type = 'plain';
            $body = imap_fetchbody($this->stream, $i, $this->select_part($structure, $type));
            $body = $this->decode_body($body, $this->select_part_encoding($structure, $type));
            
            return $body;
        }
        $body = imap_fetchbody($this->stream, $i, 2);
        return $body;
    }
    
// -----------------------------------------------------------------------------




    /**
     * 
     */
    function select_part($struct, $type, $pno = 1) 
    {
        $parttypes = array('text', 'multipart', 'message', 'application', 'audio', 'image', 'video', 'other');
        
        switch ($struct->type):
            case 0:
                // {{{
                // Get character set
                if (is_array($struct->parameters)) {
                    foreach ($struct->parameters AS $param)
                    {
                        if ($param->attribute == 'CHARSET') {
                            $this->mail_character_set = $param->value;
                        }
                    }
                }
                // }}}

                return $pno;
            case 1:
                $r = array (); 
                $i = 0;
                foreach ($struct->parts as $part)
                {
                    $pno = $this->select_part($part, $type, $pno . '.' . $i++);
                    if (strtolower($part->subtype) == $type) {
                        // {{{
                        // Get character set
                        if (is_array($part->parameters)) {
                            foreach ($part->parameters AS $param)
                            {
                                if ($param->attribute == 'CHARSET') {
                                    $this->mail_character_set = $param->value;
                                }
                            }
                        }
                        // }}}
                        
                        return $i;
                    }
                }
            case 2:
                return $this->select_part($struct->parts[0], $pno);
            default:
                return substr($pno, 1);
            endswitch;
    }

// -----------------------------------------------------------------------------




    /**
     * 
     */
    function select_part_encoding($struct, $type, $pno = 1) 
    {
        $parttypes = array('text', 'multipart', 'message', 'application', 'audio', 'image', 'video', 'other');
        
        switch ($struct->type):
            case 0:
                return $struct->encoding;
            case 1:
                $r = array (); 
                $i = 0;
                foreach ($struct->parts as $part)
                {
                    $pno = $this->select_part($part, $type, $pno . '.' . $i++);
                    if (strtolower($part->subtype) == $type) {
                        return $part->encoding;
                    }
                }
            case 2:
                return $this->select_part($struct->parts[0], $pno);
            default:
                return $struct->encoding;
            endswitch;
    }

// -----------------------------------------------------------------------------




    /**
     * Decode message body
     * 
     */
    function decode_body($body, $encoding) 
    {
        if (!isset($this->encoding[$encoding])) {
            return false;
        }
        switch ($this->encoding[$encoding]) {
            case 'quoted-printable':
//              return ($charset == 'utf-8')? utf8_decode(imap_utf8(imap_qprint($body))) : imap_qprint($body);
                $content = imap_qprint($body);
                return $content;
            case 'base64':
                return imap_base64($body);
            default:
                return $body;
        }
    }
    
// -----------------------------------------------------------------------------




    /**
     * Display error messages
     */
    function errors() 
    {
        return imap_errors();
    }
    
// -----------------------------------------------------------------------------




} // End of class








?>
