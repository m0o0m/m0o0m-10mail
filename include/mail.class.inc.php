<?php
 
/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


//include 'htmlMimeMail.php';
include 'Mail.php';
include 'Mail/mime.php';




/**
 * Send mails
 */
class t12l_mail
{




    /**
     * Send mails
     */    
    function send($to, $subject, $body, $from)
    {
        global $t12l;
        
        if ($t12l['mail_type'] == 'smtp') {
            $backend = 'smtp';
            $smtp = $t12l['smtp'];
            $params = array(
                        'host'      => $smtp['host'], 
                        'port'      => $smtp['port'], 
                        'localhost' => $smtp['helo'], 
                        'auth'      => $smtp['auth'], 
                        'username'  => $smtp['user'], 
                        'password'  => $smtp['pass']
                        ); 
        } else {
            $backend = 'mail';
            $params = array(); 
        }
        
        $mail =& Mail::factory($backend, $params);
        
//        $mail->setFrom($from);
//        $mail->setReturnPath($from);
//        $mail->setSubject($subject);
//        $mail->setHeadCharset($t12l['text']['txt_charset']);        
//        $mail->setTextCharset($t12l['text']['txt_charset']); 
//        $mail->setText($body);

                    
        $mime = &new Mail_Mime;
        
        $mime_params = array(
                        'head_charset'  => $t12l['text']['txt_charset'],
                        'text_charset'  => $t12l['text']['txt_charset']
                        );
                        
        $headers = array(
                        'From'          => $from,
                        'Return-Path'   => $from,
                        'Subject'       => $subject,
                        );
        
        $mime->setTXTBody($body);
        
        $mime_body      = $mime->get($mime_params);
        $mime_headers   = $mime->headers($headers);
        
        $result = $mail->send(array($to), $mime_headers, $mime_body);
        
        if ($result) {
            return true;
        } else {
            t12l_system_debug::add_message('Sending Mail Failed', join('<br />', $mail->errors), 'system');
        }
    }

    
//------------------------------------------------------------------------------




    /**
     * Send mails
     */    
    function send_depricated($to, $subject, $body, $from)
    {
        global $t12l;
        
        $mail = new htmlMimeMail();
        
        if ($t12l['mail_type'] == 'smtp') {
            $type = 'smtp';
            $smtp = $t12l['smtp'];
            $mail->setSMTPParams($smtp['host'], $smtp['port'], $smtp['helo'], $smtp['auth'], $smtp['user'], $smtp['pass']); 
        } else {
            $type = 'mail'; 
        }
        
        $mail->setFrom($from);
        $mail->setReturnPath($from);
        $mail->setSubject($subject);
        $mail->setHeadCharset($t12l['text']['txt_charset']);        
        $mail->setTextCharset($t12l['text']['txt_charset']); 
        $mail->setText($body);
        $result = $mail->send(array($to), $type);
        if ($result) {
            return true;
        } else {
            t12l_system_debug::add_message('Sending Mail Failed', join('<br />', $mail->errors), 'system');
        }
    }

    
//------------------------------------------------------------------------------





}








?>
