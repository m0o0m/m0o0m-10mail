<?php

/** 
 * GentleSource Temporary E-mail - email.class.inc.php
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


require_once 'database.class.inc.php';
require 'content.class.inc.php';




/**
 * Manage e-mails
 * 
 */
class t12l_email extends t12l_content
{
    
    
    
    /**
     * Number of characters in excerpt
     */
    var $excerpt_length = 200;
    
    var $delete_emails  = true;
    var $maxlength      = 20;    
    var $runs           = 10;
    var $runtime        = 300;
    
    var $detail_template  = 'email.tpl.html';
    
    /**
     * Lifetime of an email address in seconds
     */
    var $lifetime   = 900;
    
    /**
     * Number of e-mails to be downloaded during one connection to the mailbox
     */
    var $download_email_num = 200;
    
    /**
     * Number of connections to the mailbox during one download attempt
     */
    var $download_connection_num = 3;
    
// -----------------------------------------------------------------------------




    /**
     * Main
     * 
     */
    function main()
    {
        global $t12l;
    
        
        
        $message = array();
        
        // -----------------------------------------------------------------------------
        
        
        
        
        // Check for module standalone call
        if (t12l_gpc_vars('module')) {
            $module_data = array('data' => t12l_gpc_vars('module'));
            t12l_module::call_module('standalone', $module_data, $t12l['module_additional']);
            exit;
        }
        
        // -----------------------------------------------------------------------------
        
        
        
        
        // Serve feed
        if (t12l_gpc_vars('d') == 'feed') {
            $this->feed();
            exit;
        }
        
        // -----------------------------------------------------------------------------
        
        
        
        
        // Serve gadget
        if (t12l_gpc_vars('d') == 'gadget') {
            if ($gadget_function = t12l_gpc_vars('f')) {
                $this->gadget($gadget_function);
                exit;
            }
            exit;
        }
        
        // -----------------------------------------------------------------------------
        
        
        
        
        // Check for template
        if ($template = t12l_gpc_vars('t')) {
            $this->template($template);
        }
        
        // -----------------------------------------------------------------------------
        
        
        
        
        // Start mail handling
        $mail = new t12l_email();
        
        $mail->lifetime = t12l_time::convert_to_seconds($t12l['lifetime'], $t12l['lifetime_unit']);
        
        // -----------------------------------------------------------------------------
        
        
        
        
        // XMLHTTPRequests
        if (t12l_gpc_vars('d') == 'xhr') {
            if ($xhr_function = t12l_gpc_vars('f')) {
                $mail->xmlhttprequest($xhr_function);
                exit;
            }
            
            include 'xmlhttprequest.class.inc.php';
            t12l_xmlhttprequest::server();
            exit;
        }
        
        // -----------------------------------------------------------------------------
        
        
        
        
        // Ditch e-mail address
        if (t12l_gpc_vars('ditchemail')) {
            $mail->destroy_address();
            header('Location: ' . $t12l['server_protocol'] . $t12l['server_name'] . $t12l['script_url']);
        }
        
        // -----------------------------------------------------------------------------
        
        
        
        
        // Delete e-mail
        $delete_mail_dialogue   = false;
        $yes_delete_query       = '';
        $no_keep_query          = '';
        if ($t12l['allow_delete_email'] == 'Y'
                and $mail_id = t12l_gpc_vars('m')
                and t12l_gpc_vars('d') == 'd') {
            if (t12l_gpc_vars('c') == 'y') {
                if ($mail->delete_mail($mail_id)) {
                    header('Location: ' . $t12l['server_protocol'] . $t12l['server_name'] . $t12l['script_url']);
                }
            } else {
                $t12l['message'][] = $t12l['text']['txt_sure_delete_mail'];
                $delete_mail_dialogue = true;
                $yes_delete_query = '?m=' . $mail_id . '&amp;d=d&amp;c=y';
                if ($mail_id = t12l_gpc_vars('m')) {
                    $no_keep_query = '?m=' . $mail_id;
                }
            }
        }
        
        // -----------------------------------------------------------------------------
        
        
        
        
        // Get e-mail address
        if (t12l_gpc_vars('getemail')) {
            if ($mail->get_address()) {
                header('Location: ' . $t12l['server_protocol'] . $t12l['server_name'] . $_SERVER['REQUEST_URI']);
                exit;
            } else {
                $t12l['message'][] = $t12l['text']['txt_could_not_create_email'];
            }
        }
        
        // -----------------------------------------------------------------------------
        
        
        
        
        // Build e-mail address from input and from defined e-mail host name
        if (isset($t12l['_post']['setemail'])) {
            $_POST['setemailaddressintern'] = $_POST['setemailaddress'] . '@' . $t12l['email_address_host_name'];
        }
        
        
        // Handle and validate form
        require_once 'HTML/QuickForm.php';
        
        
        // Start form handler
        $form = new HTML_QuickForm('setemailform', 'POST', $t12l['script_url'] . '?d=xhr&f=setaddress', '', 'onsubmit="return !HTML_AJAX.formSubmit(this, \'temporary_address\');"');
//        $form = new HTML_QuickForm('setemailform', 'POST', $t12l['script_url']);
        
        
        // Get form configuration
        require 'email_form.inc.php';
        
        
        // Validate form
        if (isset($t12l['_post']['setemail'])
                and $form->validate()) {
            if ($t12l['allow_set_email_address'] == 'Y'
                    and $mail->get_address(true)) {
                header('Location: ' . $t12l['server_protocol'] . $t12l['server_name'] . $_SERVER['REQUEST_URI']);
                exit;
            } else {
                $t12l['message'][] = $t12l['text']['txt_could_not_create_email'];
            }
        }
        
        
        
        require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
        $renderer =& new HTML_QuickForm_Renderer_ArraySmarty($this->output->get_object, true);
                   
        $form->accept($renderer);
        
        
        // Assign array with form data
        $this->assign('setemailform', $renderer->toArray()); 
        
        // -----------------------------------------------------------------------------
        
        
        
        $show_address = false;
        if (t12l_session::get('address_id') or t12l_gpc_vars('a')) {
            $show_address = true;
        }
        $this->assign('show_address', $show_address);
        
        // -----------------------------------------------------------------------------
        
        
        
        
        if (t12l_session::get('address_id') or t12l_gpc_vars('a')) {
            $address_data = $mail->get_address();
            $address_data['minutes_left'] = round($address_data['time_left'] / 60); 
            $this->assign($address_data);
        }
        
        // -----------------------------------------------------------------------------
        
        
        // Get mail content
        $show_mail_details  = false;
        $mail_details       = array();
        if ($mail_id = t12l_gpc_vars('m')) {
            if ($mail_details = $mail->get_mail($mail_id)) {
                $show_mail_details = true;
                $show_mail_list    = false;
            }
        }
        $this->assign($mail_details);
        
        // -----------------------------------------------------------------------------
        
        
        // Get mail list
        $show_mail_list = false;
        if ($show_mail_details == false
                and $address = t12l_session::get('address_id')) {
//            include 'emaillist.class.inc.php';
//            $mail_list_setup = array(   'direction' => $t12l['frontend_order'],
//                                        'limit'     => 0);
//            $mail_list = new t12l_email_list(false, $mail_list_setup);
            $email_message_result = $this->email_message_list();
            if (isset($email_message_result['mail_data'])) {
                $this->assign('mail_list', $email_message_result['mail_data']);
                $show_mail_list = true;
            }
//            $mail_list_values = $mail_list->values();
            $this->assign($email_message_result['mail_list_values']);
        }
        
        // -----------------------------------------------------------------------------
        
        
        // Display reply form and send e-mail
        $show_reply_form = false;
        if (t12l_session::get('address_id')    
                and t12l_gpc_vars('d') == 'r' 
                and $mail_id = t12l_gpc_vars('m')
                and $show_mail_details == true) {
            $show_reply_form = true;    
            require_once 'HTML/QuickForm.php';
            $form = new HTML_QuickForm('reply', 'POST', $t12l['script_url'] . '?m=' . $mail_id);
            $form->addElement('textarea', 'text', $t12l['text']['txt_reply'], array('rows' => 8, 'cols' => 30));
            $form->addElement('submit', 'save', $t12l['text']['txt_submit']);
            $form->addElement('hidden', 'm');
            $form->addElement('hidden', 'd');
            $form->addRule('text',   $t12l['text']['txt_enter_reply_text'], 'required');
            $defaults = array(  'd' => 'r',
                                'm' => $mail_id,
                                );
            $form->setConstants($defaults);
            
            if ($form->validate()) {
                if ($mail->send_reply($mail_id)) {
                    $t12l['message'][] = $t12l['text']['txt_reply_has_been_sent'];
                    $show_reply_form = false;
                } else {
                    $t12l['message'][] = $t12l['text']['txt_reply_has_not_been_sent'];
                }
            }
        
            require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
            $renderer =& new HTML_QuickForm_Renderer_ArraySmarty($this->output->get_object, true);               
            $form->accept($renderer);
            $this->assign('reply', $renderer->toArray());
        }    
        
        // -----------------------------------------------------------------------------
        
        
        // Delete expired e-mails and addresses
        $mail->delete_expired();
        
        // -----------------------------------------------------------------------------
        
        
        
        
        // Reset time
        if (t12l_gpc_vars('resettime')) {
            $mail->reset_time();
            header('Location: ' . $t12l['server_protocol'] . $t12l['server_name'] . $_SERVER['REQUEST_URI']);
        }
        
        // -----------------------------------------------------------------------------       




        // Get statistics
        $statistics = array(
                        'received_emails'       => $t12l['received_emails'],
                        'valid_emails'          => $t12l['sequence_mail'],
                        'expired_emails'        => $t12l['expired_emails'],
                        'sent_emails'           => $t12l['sent_emails'],
                        'created_addresses'     => $t12l['created_addresses'],
                        );
                        
        $this->assign($statistics);        
        
        // -----------------------------------------------------------------------------
        
        
        // Output
        $this->assign('page_data', array('page_title' => $t12l['text']['txt_disposable_email_address']));
        $this->assign('show_mail_details',   $show_mail_details);
        $this->assign('show_mail_list',      $show_mail_list);
        $this->assign('show_reply_form',     $show_reply_form);
        $this->assign('message',             $message);
        $this->assign('delete_mail_dialogue',$delete_mail_dialogue);
        $this->assign('yes_delete_query',    $yes_delete_query);
        $this->assign('no_keep_query',       $no_keep_query);
        
        
        return $this->finish();
    }
    
// -----------------------------------------------------------------------------




    /**
     * Feed
     * 
     */
    function feed()
    {
        global $t12l;
        
        if (!$address = t12l_gpc_vars('a')) {
            return false;
        }
        $address_id = md5($address);
        

        // Default output is RSS2.0
        $rss_template = 'rss_2.0.xml';
        switch (trim(t12l_gpc_vars('f'))) {
            case 'atom':    
                $rss_template = 'atom.xml';
                break;
            case 'rss2':
            default:        
                $rss_template = 'rss_2.0.xml';
                break;
        }
        
        $t12l['alternative_template'] = 'syndication';
        $updated_atom = date('Y-m-d\TH:i:s\Z', t12l_time::current_timestamp());
        $mail_data  = array();
        
        $out = new t12l_output();
        $out->set_global_template($rss_template);
        
        
        if ($t12l['activate_syndication'] == 'Y') {
            include 'emaillist.class.inc.php';
            
            $mail_list_setup = array(   'direction' => $t12l['frontend_order'],
                                        'limit'     => 0);
            $mail_list = new t12l_email_list(false, $mail_list_setup);
            if ($mail_data = $mail_list->get_list($address_id)) {            
                if (isset($article_data[0])) {
                    $updated_atom = $article_data[0]['last_modified_atom'];
                }
            }
        }
                 
        $out->assign('mail_list', $mail_data);
        $out->assign('updated_atom', $updated_atom);
        $out->assign('self_script_url', $t12l['server_protocol'] . $t12l['server_name'] . $t12l['script_url'] . '?d=feed&amp;f=atom&amp;a=' . $address);
            
        // Output
        echo $out->finish_xml();
        exit;
    }
    
// -----------------------------------------------------------------------------




    /**
     * Get e-mail address
     * 
     * @access public
     */
    function get_address($set = false)
    {
        $data = array();
        t12l_module::call_module('frontend_use_functions', $data, $t12l['module_additional']);
        if ($t12l['module_additional']['allow_use_functions'] == 'N') {
            return false;
        }
        
        // Dispose of old e-mail                
        if (!t12l_gpc_vars('a') and t12l_session::get('address_timestamp') < (t12l_time::current_timestamp() - $this->lifetime)) {
            if ($this->destroy_address()) {
                if ($address = $this->new_address($set)) {
                    $address['time_left'] = $this->lifetime;
                    return $address;
                } else {
                    return false;
                }
            }
        }
                
        // Get address from existing session
        if (!t12l_gpc_vars('a') and $address = t12l_session::get()) {
            $address['time_left'] = $this->lifetime - (t12l_time::current_timestamp() - $address['address_timestamp']);
            return $address;
        }
        
        // Create new e-mail
        if (t12l_gpc_vars('a')) {
            $set = true;
        }
        if ($address = $this->new_address($set)) {
            $address['time_left'] = $this->lifetime;
            return $address;
        } else {
            return false;
        }
    }
    
// -----------------------------------------------------------------------------




    /**
     * Reset time
     * 
     * @access public
     */
    function reset_time()
    {        
        // Create new address
        $address_email      = t12l_session::get('address_email');
        $address_id         = md5($address_email);
        $address_timestamp  = t12l_time::current_timestamp();
        
        t12l_session::add(array('address_timestamp' => $address_timestamp));
             
        // Update timestamp in e-mail table
        $data   = array('mail_timestamp' => $address_timestamp);
        $where  = " mail_address_id = ? ";
        $where_data = array($address_id); 
        if ($res = t12l_database::update('mail', $data, $where, $where_data)) {
            // Update timestamp in address table
            $data  = array('address_timestamp' => $address_timestamp);
            $where = " address_id = ? AND address_session_id = ?";
            $where_data = array($address_id, session_id());
            if ($res = t12l_database::update('address', $data, $where, $where_data)) {
                return true;
            }
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Create new address
     * 
     * @access public
     */
    function new_address($set = false)
    {   
        global $t12l;
        
        if ($set == true and isset($t12l['_post']['setemailaddress'])) {
            $address_email = $t12l['_post']['setemailaddress'] . '@' . $t12l['email_address_host_name'];
        } elseif ($set == true and t12l_gpc_vars('a')) {
            $address_email = t12l_gpc_vars('a');
        } else {
        
            // Create new address
            $address_email      = $this->create_address(8);
        }
        
        
        $address_id         = md5($address_email);
        $address_timestamp  = t12l_time::current_timestamp();
        
             
        // Create new entry        
        $data = array(  'address_id'                => $address_id,
                        'address_email'             => $address_email,
                        'address_timestamp'         => $address_timestamp,
                        'address_ip'                => @$_SERVER['REMOTE_ADDR'],
                        'address_session_id'        => session_id(),
                        );
                         
        // Write address to database
        if ($res = t12l_database::insert('address', $data)) {
            
            // Write address to session
            $session = array(   'address_id'        => $address_id,
                                'address_email'     => $address_email,
                                'address_timestamp' => $address_timestamp,
                                );
            t12l_session::add($session);
            
            // Write address statistics
            t12l_setting::increase('created_addresses');
            
            return $data;
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Create e-mail
     * 
     */
    function create_address($length, $run = 1)
    {
        global $t12l;
        require_once 'Text/Password.php'; 
        $email_address = Text_Password::create($length) . '@' . $t12l['email_address_host_name'];
        $address_id = md5($email_address);
        $sql = "SELECT  address_id FROM " . T12L_ADDRESS_TABLE . " 
                WHERE   address_id = ? ";
        if (!$res = t12l_database::query($sql, array($address_id))) {
            return $email_address;
        }
        if ($res->numRows() > 0) {
            if ($run >= $this->runs) {
                $run = 0;
                $length++;
                if ($length >= $this->maxlength) {
                    t12l_system_debug::add_message('Create Address', 'Too Much Runs', 'error');
                    return false;
                }
            }
            $email_address = $this->create_address($length, ++$run);
        }
        return $email_address;        
    }          

//------------------------------------------------------------------------------




    /**
     * Dispose of e-mail
     * 
     */
    function destroy_address()
    {
        $session = array(   'address_id'        => '',
                            'address_email'     => '',
                            'address_timestamp' => '',
                            );
        t12l_session::add($session);
        return true;
    }          

//------------------------------------------------------------------------------




    /**
     * Trigger download of e-mails
     * 
     */
    function trigger_mail_download()
    {   
        global $t12l;
        
        @ini_set('max_execution_time', $this->runtime);
        
        if (!function_exists('imap_open')
                or $t12l['alternative_mail_download'] == true) {
            require_once 'mailbox.class.inc.php';        
        } else {
            require_once 'mailboximap.class.inc.php';
        }
        
        for ($i = 1; $i <= $this->download_connection_num; $i++)
        {
            $downloaded_emails = $this->mail_download();
            if ($downloaded_emails == false
                    or $downloaded_emails <= 0) {
                t12l_system_debug::add_message('Trigger Mail Download', 'Number of connections: ' . $i, 'debug');
                return false;
            }
        }
        
        t12l_system_debug::add_message('Trigger Mail Download', 'Some mails remaining in mailbox', 'debug');
    }          

//------------------------------------------------------------------------------




    /**
     * Download e-mails and write content to database
     * 
     */
    function mail_download()
    {   
        global $t12l;
        
        $mailbox_add_path = '';
        $mailbox_add_path_ssl = '';
        
        if ($t12l['non_validate_command'] == 'Y') {
            $mailbox_add_path       .= '/novalidate-cert';
            $mailbox_add_path_ssl   .= '/novalidate-cert';
        }        
        
        if (!function_exists('imap_open')
                or $t12l['alternative_mail_download'] == true) {        
            $mail = new t12l_mailbox($this->delete_emails);
        } else {  
            $mail = new t12l_mailboximap();
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
        $mailbox = array(   'hostname'  => $t12l['mailbox_hostname'],
                            'path'      => $mailbox_path,
                            'port'      => $mailbox_port,
                            'user'      => $t12l['mailbox_username'],
                            'password'  => $t12l['mailbox_password'],
                            'tls'       => $mailbox_tls,
                            );
         
        if (!$mail->open($mailbox)) {
            return false;
        }


        // Number of messages in mailbox
        $number = 0;
        $mail->number($number);
        if ($number <= 0) {
            $mail->close();
            return false;
        }
            
        t12l_system_debug::add_message('Mailbox Status', $number . ' mail(s) in mailbox.', 'debug');
        
        $email_number = $this->download_email_num;
        if ($number < $this->download_email_num) {
            $email_number = $number;
        }
        
        // Check To: headers
        for ($i = 1; $i <= $email_number; $i++)
        {
            $message = $mail->fetch_message($i);
            $mail->mime_decode($i);
            
            if (!$to = $mail->get_header($i, 'to')) {                        
                // Delete e-mail
                $mail->delete_message($i); 
                continue;
            }
            
            $to = explode(',', $to);
            $num = sizeof($to);
            for ($t = 0; $t < $num; $t++)
            {
                $mail_address = $to[$t];
                
                if (strpos($mail_address, '<') !== false) {
                    $mail_address = substr($mail_address, strpos($mail_address, '<') + 1);
                }
                
                if (strpos($mail_address, '>') !== false) {                
                    $mail_address = substr($mail_address, 0, strpos($mail_address, '>'));
                }
                
                $mail_address = trim($mail_address);
                $mail_host    = substr($mail_address, strpos($mail_address, '@') + 1);

                if ($mail_host != $t12l['email_address_host_name']) {
                    // Delete e-mail 
                    $mail->delete_message($i);
                    continue;
                }
                
                // Count number of received e-mails
                t12l_setting::increase('received_emails');
                
                // Check address in database
                $address            = $mail_address;
                $address_id         = md5($address);
                $address_timestamp  = t12l_time::current_timestamp() - $this->lifetime;
                $sql = "SELECT  address_id 
                        FROM    " . T12L_ADDRESS_TABLE . " 
                        WHERE   address_id = ? 
                        AND     address_timestamp > ?";
                if (!$res = t12l_database::query($sql, array($address_id, $address_timestamp))) {
                    return false;
                }
                if ($res->numRows() > 0) {
                    t12l_system_debug::add_message('Mail Status', 'Mail is valid: ' . $address, 'debug');
                    
                    $subject    = $mail->get_header($i, 'subject');
                    $body       = $mail->get_body($i);
                    $from       = $mail->get_header($i, 'from');
                    $from       = $mail->get_header($i, 'from');
                    $charset    = $mail->get_character_set($i);
                    if ($this->insert_mail($address, $from, $subject, $body, $charset)) {
                        // Delete e-mail
                        $mail->delete_message($i);
                    }
                } else {
                    // Delete e-mail
                    $mail->delete_message($i);
                    t12l_system_debug::add_message('Mail Status', 'Mail is NOT valid: ' . $address, 'debug');
                    
                    // Count number of expired e-mails
                    t12l_setting::increase('expired_emails');
                }
            }
        }
        if ($this->delete_emails == true) {
            $mail->perform_delete();
        }        
        $mail->close();
        
        
        // Return number of mails in mailbox
        return $number;
    }

//------------------------------------------------------------------------------




    /**
     * Write mail data to database
     * 
     */
    function insert_mail($address, $from, $subject, $body, $charset)
    {                
        // Write into comment table
        if (!$mail_id = t12l_database::next_id('mail')) {
            return false;
        }
        
        $mail_data = array( 'mail_id'           => $mail_id,
                            'mail_address_id'   => md5($address),
                            'mail_from'         => $from,
                            'mail_subject'      => $subject,
                            'mail_excerpt'      => substr(strip_tags($body), 0, $this->excerpt_length),
                            'mail_body'         => $body,
                            'mail_character_set'=> $charset,
                            'mail_timestamp'    => t12l_time::current_timestamp(),
                            );
        if ($res = t12l_database::insert('mail', $mail_data)) {
            return true;
        }
    }          

//------------------------------------------------------------------------------




    /**
     * Get mail details from database
     * 
     * @access public
     */
    function get_mail($mail)
    {
        global $t12l;
        
        if (!is_numeric($mail)) {
            return false;
        }
        
        if ($t12l['public_mails'] == 'N' and !t12l_session::get('address_id')) {
            return false;
        }
        
        $sql = '';
        
        // Private e-mails - only the user that created the e-mail address
        // will be able to read e-mails
        if ($t12l['public_mails'] == 'N' and t12l_session::get('address_id')) {
        $sql = "SELECT  m.*
                FROM    " . T12L_MAIL_TABLE . " AS m
                WHERE   m.mail_id = ?
                AND     m.mail_address_id = ?";
                
                $data = array((int)$mail, t12l_session::get('address_id'));
        }        
        
        // Public e-mails - everyone can read the e-mails
        if ($t12l['public_mails'] == 'Y') {
        $sql = "SELECT  m.*
                FROM    " . T12L_MAIL_TABLE . " AS m
                WHERE   m.mail_id = ?";
                
                $data = array((int)$mail);
        }
        
        if ($sql == '') {
            return false;
        }
        
        t12l_system_debug::add_message('SQL Statement get_mail()', $sql, 'debug');
        if ($db = t12l_database::query($sql, $data)) {
            $res = $db->fetchRow();
            if (PEAR::isError($res)) {
                t12l_system_debug::add_message($res->getMessage(), $res->getDebugInfo(), 'error', $res->getBacktrace());
                t12l_system_debug::add_message('SQL Statement', $sql, 'error');
                return false;
            }
    
            if (sizeof($res) > 0) {

                $res['frontend_date']   = t12l_time::format_date($res['mail_timestamp']);
                $res['frontend_time']   = t12l_time::format_time($res['mail_timestamp']);
                
                $res['frontend_subject']    = $res['mail_subject'];
                $frontend_text              = $res['mail_body'];
                  
                // Convert character sets
                if (function_exists('iconv')
                        and $t12l['text']['txt_charset'] != $res['mail_character_set']) {

                    if ($result = @iconv($res['mail_character_set'], $t12l['text']['txt_charset'] . '//TRANSLIT', $res['mail_subject'])) {
                        $res['frontend_subject'] = $result;
                    }
                    if ($result = @iconv($res['mail_character_set'], $t12l['text']['txt_charset'] . '//TRANSLIT', $frontend_text)) {
                        $frontend_text = $result;
                    }
                }
                
                if ($t12l['message_body_format'] != 'html') {
                    $frontend_text = preg_replace('/<a href="(.*?)"(.*?)>(.*?)<\\/a>/i', '<a href="$1">$3 - $1 </a>', $frontend_text);
                    $frontend_text = preg_replace('/<http(.*?)>/i', 'http$1', $frontend_text);
                
                    $frontend_text = strip_tags($frontend_text);
                    $frontend_text = nl2br(htmlspecialchars($frontend_text));
                }
                
                $res['frontend_subject']    = htmlspecialchars(strip_tags($res['frontend_subject'])); 
                $res['frontend_text']       = $frontend_text;
                $res['frontend_from']       = htmlspecialchars(stripslashes($res['mail_from']));
                
                t12l_module::call_module('frontend_content', $res, $t12l['module_additional']);
                return $res;
            } else {
                $t12l['message'][] = $t12l['text']['txt_mail_not_found'];
                return false;
            }
        }

    }

// -----------------------------------------------------------------------------



    /**
     * Send reply e-mail
     * 
     */
    function send_reply($mail_id)
    {
        global $t12l;
        
        if (!$mail_data = $this->get_mail($mail_id)) {
            return false;
        }
        
        $mail_data['comment']               = $t12l['_post']['text'];
        $mail_data['email']                 = '';
        $mail_data['homepage']              = '';
        $mail_data['comment_author_ip']     = getenv('REMOTE_ADDR');
        $mail_data['name']                  = t12l_session::get('address_email');
        
        $page_data = array(
                        'page_allow_comment'    => ''
                        );
        t12l_module::call_module('frontend_save_content', $mail_data, $page_data);

        // Reply message blocked
        if ($page_data['page_allow_comment'] == 'N') {
            return false;
        }
        
        $search = array("\r", "\n", "%0A", "%0D");
        
        
        // Prepare to
        $reply_to = str_replace($search, '', $mail_data['mail_from']);
        
        // Prepare subject
        $reply_subject = str_replace($search, '', $mail_data['mail_subject']);
                  
        // Convert character sets
        if (function_exists('iconv')
                and $t12l['text']['txt_charset'] != $mail_data['mail_character_set']) {

            if ($result = @iconv($mail_data['mail_character_set'], $t12l['text']['txt_charset'] . '//TRANSLIT', $reply_subject)) {
                $reply_subject = $result;
            }
        }
        $reply_subject = 'RE: ' . $reply_subject;
        
        // Prepare body
        $reply_body = htmlspecialchars(strip_tags($t12l['_post']['text']));        
        
        
        $detail_template                = 'reply.tpl.txt';
        $t12l['alternative_template']   = 'mail';
    
        // Start output handling
        $out = new t12l_output($detail_template);
        $out->assign('reply_body', $reply_body); 
        $reply_coutput = $out->finish_mail();
        
        // Send mail off
        include 'mail.class.inc.php';        
        if (t12l_mail::send($reply_to, 
                            $reply_subject,                            
                            $reply_coutput, 
                            t12l_session::get('address_email'))) {
                                
            // Count number of sent e-mails
            t12l_setting::increase('sent_emails');
            
            return true;
        }
    }          

//------------------------------------------------------------------------------



    /**
     * Delete old mails and addresses
     * 
     */
    function delete_expired()
    {
        $timestamp = (t12l_time::current_timestamp() - $this->lifetime);
        
        // Delete e-mails
        $where = " mail_timestamp < ? LIMIT 500";
        $data = array($timestamp);
        if (!$res = t12l_database::delete(T12L_MAIL_TABLE, $where, $data)) {
            return false;
        }
        
        // Delete addresses
        $where = " address_timestamp < ? LIMIT 500";
        $data = array($timestamp);
        if ($res = t12l_database::delete(T12L_ADDRESS_TABLE, $where, $data)) {
            return true;
        }
    }          

//------------------------------------------------------------------------------



    /**
     * Delete e-mail
     * 
     */
    function delete_mail($mail_id)
    {    
        if (!is_numeric($mail_id)) {
            return false;
        }

        // Delete e-mails
        $where = " mail_id = ? AND mail_address_id = ?";
        $data = array($mail_id, t12l_session::get('address_id'));
        if ($res = t12l_database::delete(T12L_MAIL_TABLE, $where, $data)) {
            return true;
        }
    }          

//------------------------------------------------------------------------------



    /**
     * Get e-mail message list
     * 
     */
    function email_message_list()
    {    
        global $t12l;
        if ($address = t12l_session::get('address_id')) {
            include 'emaillist.class.inc.php';
            $mail_list_setup = array(   'direction' => $t12l['frontend_order'],
                                        'limit'     => 0);
            $mail_list = new t12l_email_list(false, $mail_list_setup);
            $result = array();            
            if ($mail_data = $mail_list->get_list($address)) {
                $result['mail_data'] = $mail_data;
            }
            $result['mail_list_values'] = $mail_list->values();
            return $result;
        }
    }          

//------------------------------------------------------------------------------



    /**
     * XMLHTTPRequest
     * 
     */
    function xmlhttprequest($function)
    {    
        //sleep(1);
        if ($function == 'refresh') {
            $this->xhr_refresh();
        }
        if ($function == 'setaddress') {
            $this->xhr_set_address();
        }
        if ($function == 'message') {
            $this->xhr_message();
        }
    }          

//------------------------------------------------------------------------------



    /**
     * XMLHTTPRequest
     * 
     */
    function xhr_refresh()
    {    
        $detail_template    = 'emailmessagelist.tpl.html';
        $show_mail_list     = false;

        $out = new t12l_output();
        
        $email_message_result = $this->email_message_list();
        if (isset($email_message_result['mail_data'])) {
            $out->assign('mail_list', $email_message_result['mail_data']);
            $show_mail_list = true;
        }        
        
        $out->assign($email_message_result['mail_list_values']);
        $out->assign('show_mail_list', $show_mail_list);
        echo $out->finish_xmlhttprequest($detail_template);
        exit;
    }                 

//------------------------------------------------------------------------------



    /**
     * XMLHTTPRequest
     * 
     */
    function xhr_set_address()
    {   
        global $t12l;
        
        $detail_template    = 'emailaddress.tpl.html';
        
        
        // Build e-mail address from input and from defined e-mail host name
        if (isset($t12l['_post']['setemail'])) {
            $_POST['setemailaddressintern'] = $_POST['setemailaddress'] . '@' . $t12l['email_address_host_name'];
        }
        
        
        // Handle and validate form
        require_once 'HTML/QuickForm.php';
        
        
        // Start form handler
        $form = new HTML_QuickForm('setemailform', 'POST', $t12l['script_url'] . '?d=xhr&f=setaddress', '', 'onsubmit="return !HTML_AJAX.formSubmit(this, \'temporary_address\');"');
        
        
        // Get form configuration
        require 'email_form.inc.php';
        
        
        // Validate form
        $mail = new t12l_email();
        if (isset($t12l['_post']['setemail'])
                and $form->validate()) {
            if ($t12l['allow_set_email_address'] == 'Y'
                    and $mail->get_address(true)) {
                //header('Location: ' . $t12l['server_protocol'] . $t12l['server_name'] . $t12l['script_url']);
                echo '<script type="text/javascript">window.location = \'' . $t12l['server_protocol'] . $t12l['server_name'] . $t12l['script_url'] . '\';</script>';
                exit;
            } else {
                $t12l['message'][] = $t12l['text']['txt_could_not_create_email'];
            }
        }
        
        $out = new t12l_output();
        
        require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
        $renderer =& new HTML_QuickForm_Renderer_ArraySmarty($out->get_object, true);
                   
        $form->accept($renderer);
        
        
        // Assign array with form data
        $out->assign('setemailform', $renderer->toArray()); 
        
        echo $out->finish_xmlhttprequest($detail_template);
        
        exit;
    }          

//------------------------------------------------------------------------------



    /**
     * XMLHTTPRequest
     * 
     */
    function xhr_message()
    {    
        $detail_template    = 'emailmessage.tpl.html';

        $out = new t12l_output();        

        $mail = new t12l_email();
        $mail_details = array();
        if ($mail_id = t12l_gpc_vars('m')) {
            $mail_details = $mail->get_mail($mail_id);
        }
        $out->assign($mail_details);      
        
        echo $out->finish_xmlhttprequest($detail_template);
        exit;
    }          

//------------------------------------------------------------------------------



    /**
     * Gadgets/widgets
     * 
     */
    function gadget($function)
    {    
        if ($function == 'igoogle') {
            $this->igoogle_gadget();
        }
    }          

//------------------------------------------------------------------------------



    /**
     * Google gadget
     * 
     */
    function igoogle_gadget()
    {    
        global $t12l;
        
        $detail_template                = 'gadget.xml';
        $t12l['alternative_template']   = 'syndication';
        

        $out = new t12l_output($detail_template);

        $out->set_global_template($detail_template);            
        
        echo $out->finish_xml();
        exit;
    }          

//------------------------------------------------------------------------------



    /**
     * Template
     * 
     */
    function template()
    {    
        global $t12l;
        
        if ($template = 'gadget') {
            $t12l['alternative_template']   = 'gadget';
            $this->assign('detail_template', $this->output->select_template($this->detail_template));
        }
        
        
    }          

//------------------------------------------------------------------------------




} // End of class








?>