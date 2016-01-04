<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




// Settings
define('T12L_ROOT', '../');
define('T12L_ALTERNATIVE_TEMPLATE', 'admin');
define('T12L_LOGIN_LEVEL', 1);

$t12l_detail_template    = 'configuration.tpl.html';

// -----------------------------------------------------------------------------




// Include
require T12L_ROOT . 'include/core.inc.php';

// Start output handling
$out = new t12l_output($t12l_detail_template);


$out->assign('module_list', t12l_module::module_list());

// -----------------------------------------------------------------------------




// Output
$out->assign('display_setting_navigation', true);
$out->finish();






?>
