<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




// Settings
define('T12L_ROOT', './');
define('T12L_LOGIN_LEVEL', 0);
$frontend_language                  = true;


// Include
require T12L_ROOT . 'include/core.inc.php';
require 'email.class.inc.php';


$t12l_obj = new t12l_email();

echo $t12l_obj->main();





?>
