<?php

/** 
 * GentleSource Temporary E-mail
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




// Settings
define('T12L_ROOT', './');
define('T12L_LOGIN_LEVEL', 0);


// Include
require T12L_ROOT . 'include/coresmall.inc.php';



header('Content-Type: text/html; charset=' . $t12l['text']['txt_charset']);




// Download e-mails
if ($t12l['shut_down'] == 'N' 
        and t12l_gpc_vars('get') == 'mail' 
        and t12l_gpc_vars('key') == $t12l['download_mail_process_key']) {
    
    require 'email.class.inc.php';            
    $mail = new t12l_email();
    $mail->trigger_mail_download();
}



if ($t12l['debug_mode'] == 'Y') {
    $system_messages    = t12l_system_debug::get_messages('system');
    $debug_messages     = t12l_system_debug::get_messages('debug');
    $error_messages     = t12l_system_debug::get_messages('error');
    t12l_print_a($system_messages);
    t12l_print_a($debug_messages);
    t12l_print_a($error_messages);
}





?>
