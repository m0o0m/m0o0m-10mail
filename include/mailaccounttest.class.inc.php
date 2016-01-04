<?php

/** 
 * GentleSource Temporary E-mail - mailboxtest.class.inc.php
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


require_once 'database.class.inc.php';
require 'content.class.inc.php';




/**
 * Test mailbox
 * 
 */
class t12l_mail_account_test extends t12l_content
{

    
    var $detail_template  = 'mailaccounttest.tpl.html';
    
    /**
     * Mailbox object
     */
    var $mailbox = null;
    
    /**
     * Mailbox data
     */
    var $mailbox_data = array();
    


    /**
     * Template
     * 
     */
    function main()
    {    
        global $t12l;
        
        $message = array();
                
        
        // Check if all required fields are filled in
        $this->required_fields();
        
        
        // Load mailbox code and prepare host, user, password, paths, ssl/tls
        $this->prepare();
        
        
        // Connect to mail box, try to login, interprete response
        if (!$this->open()) {
            if ($errors = $this->mailbox->errors() 
                    and sizeof($errors > 0)) {
                foreach ($errors AS $value) 
                {
                    // Imap
                    if (strpos(strtolower($value), 'authorization failed') !== false) {
                        $message[] = $t12l['text']['txt_mail_account_autorization_failed'];
                    }
                    
                    // Pop3 class
                    if (strpos(strtolower($value), 'authentication failed') !== false) {
                        $message[] = $t12l['text']['txt_mail_account_autorization_failed'];
                    }
                    if (strpos(strtolower($value), 'login failed') !== false) {
                        $message[] = $t12l['text']['txt_mail_account_autorization_failed'];
                    }
                    
                    // Imap
                    if (strpos(strtolower($value), 'host not found') !== false) {
                        $message[] = $t12l['text']['txt_mail_account_hostname_not_found'];
                    }
                    if (strpos(strtolower($value), 'no such host') !== false) {
                        $message[] = $t12l['text']['txt_mail_account_hostname_not_found'];
                    }
                    
                    // Pop3 class
                    if (strpos(strtolower($value), 'could not connect to the host') !== false) {
                        $message[] = $t12l['text']['txt_mail_account_could_not_connect'];
                        $message[] = $value;
                    }
                    
                    if (strpos(strtolower($value), 'certificate') !== false) {
                        $message[] = $t12l['text']['txt_mail_account_certificate_failure'];
                    }

                    // Imap                    
                    if (strpos(strtolower($value), 'invalid remote specification') !== false) {
                        $message[] = $t12l['text']['txt_invalid_remote_specification'];
                    }

                    if (sizeof($message) <= 0
                            or $t12l['debug_mode'] == 'Y') {
                        $message[] = $value;
                    }
                }
            }
        } else {
            $message[] = $t12l['text']['txt_mail_account_test_successful'];
        }

        $this->assign('status_message', $message);

        return $this->finish();
    }          

//------------------------------------------------------------------------------
    



    /**
     * Check if mailbox host, user and password are provided
     */
    function required_fields()
    {    
        global $t12l;         

    }          

//------------------------------------------------------------------------------
    



    /**
     * Prepare mailbox connect
     * 
     */
    function prepare()
    {    
        global $t12l;
        
        $mailbox_add_path = '';
        $mailbox_add_path_ssl = '';
        
        if ($t12l['non_validate_command'] == 'Y') {
            $mailbox_add_path       .= '/novalidate-cert';
            $mailbox_add_path_ssl   .= '/novalidate-cert';
        }    
              
        // Connect to mailbox
        // Imap setting
        $mailbox_path = 'pop3' . $mailbox_add_path;
        // Both
        $mailbox_port = $t12l['mailbox_port'];
        // pop3 class
        $mailbox_tls  = 0;
        
        if ($t12l['mailbox_connect_ssl'] == 'Y') {
            // Imap setting
            $mailbox_path = 'pop3/ssl' . $mailbox_add_path_ssl;
            // Both
            $mailbox_port = $t12l['mailbox_port_ssl'];
            // pop3 class
            $mailbox_tls  = 1;
        }
        $this->mailbox_data = array(   
                                'hostname'  => $t12l['mailbox_hostname'],
                                'path'      => $mailbox_path,
                                'port'      => $mailbox_port,
                                'user'      => $t12l['mailbox_username'],
                                'password'  => $t12l['mailbox_password'],
                                'tls'       => $mailbox_tls,
                                );

                    
        
        if (!function_exists('imap_open')
                or $t12l['alternative_mail_download'] == true) {
            require_once 'mailbox.class.inc.php';   
            $this->mailbox = new t12l_mailbox(false);     
        } else {
            require_once 'mailboximap.class.inc.php';
            $this->mailbox = new t12l_mailboximap();
        }

    }          

//------------------------------------------------------------------------------
    



    /**
     * Open mailbox
     */
    function open()
    {    
        return $this->mailbox->open($this->mailbox_data);
    }          

//------------------------------------------------------------------------------




} // End of class








?>