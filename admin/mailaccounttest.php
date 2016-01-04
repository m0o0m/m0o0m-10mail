<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




// Settings
define('T12L_ROOT', '../');
define('T12L_LOGIN_LEVEL', 1);
define('T12L_ALTERNATIVE_TEMPLATE', 'popup');


// Include
require T12L_ROOT . 'include/core.inc.php';
require 'mailaccounttest.class.inc.php';


$t12l_obj = new t12l_mail_account_test();
echo $t12l_obj->main();



?>
