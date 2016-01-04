<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




// Settings
define('T12L_ROOT', '../');

$t12l_detail_template                = 'admin_start.tpl.html';

define('T12L_ALTERNATIVE_TEMPLATE', 'admin');
define('T12L_LOGIN_LEVEL', 1);


// Include
require T12L_ROOT . 'include/core.inc.php';


// Start output handling
$out = new t12l_output($t12l_detail_template);


// Get statistics
$statistics = array(
                'received_emails'       => $t12l['received_emails'],
                'valid_emails'          => $t12l['sequence_mail'],
                'expired_emails'        => $t12l['expired_emails'],
                'sent_emails'           => $t12l['sent_emails'],
                'created_addresses'     => $t12l['created_addresses'],
                );
                
$out->assign($statistics);





// Output
$out->finish();






?>
