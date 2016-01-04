<?php

/** 
 * GentleSource News Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




// Settings
define('T12L_ROOT', '../');
define('T12L_ALTERNATIVE_TEMPLATE', 'admin');
define('T12L_LOGIN_LEVEL', 1);

$t12l_detail_template    = 'module.tpl.html';

// -----------------------------------------------------------------------------




// Include
require T12L_ROOT . 'include/core.inc.php';

$data = array('module' => t12l_gpc_vars('m'));
t12l_module::call_module('module_send_file', $data, $t12l['module_additional']); 


// Start output handling
$out = new t12l_output($t12l_detail_template);

// -----------------------------------------------------------------------------



// Install module
if ($module = t12l_gpc_vars('i')) {
    if (false == $t12l['demo_mode']) {
        if (t12l_module::install($module)) {
            header('Location: ' . $t12l['server_protocol'] . 
                                $t12l['server_name'] . 
                                dirname($_SERVER['PHP_SELF']) . 
                                '/module.php?im=s');
            exit;
        }
    } else {
        $t12l['message'][] = $t12l['text']['txt_disabled_in_demo_mode'];
    }
}

// -----------------------------------------------------------------------------



// Uninstall module
if ($module = t12l_gpc_vars('u') and t12l_gpc_vars('c') == 'y') {
    if (false == $t12l['demo_mode']) {
        if (t12l_module::uninstall($module)) {
            header('Location: ' . $t12l['server_protocol'] . 
                                $t12l['server_name'] . 
                                dirname($_SERVER['PHP_SELF']) . 
                                '/module.php?um=s');
            exit;
        }
    } else {
        $t12l['message'][] = $t12l['text']['txt_disabled_in_demo_mode'];
    }
}
if ($module = t12l_gpc_vars('u') and t12l_gpc_vars('c') != 'y') {
    if (false == $t12l['demo_mode']) {
        $delete_confirmation = array(
                                'dialogue'  => 1,
                                'module'    => $module
                                );
        $out->assign('delete_confirmation', $delete_confirmation);
    } else {
        $t12l['message'][] = $t12l['text']['txt_disabled_in_demo_mode'];
    }
}

// -----------------------------------------------------------------------------



// Success messages
if (t12l_gpc_vars('um') == 's') {
    $t12l['message'][] = $t12l['text']['txt_uninstall_module_successful'];
}
if (t12l_gpc_vars('im') == 's') {
    $t12l['message'][] = $t12l['text']['txt_install_module_successful'];
}

// -----------------------------------------------------------------------------



// Module order
if ($module = t12l_gpc_vars('o') and $direction = t12l_gpc_vars('d')) {
    if (false == $t12l['demo_mode']) {
        t12l_module::order($module, $direction);
    } else {
        $t12l['message'][] = $t12l['text']['txt_disabled_in_demo_mode'];
    }
}

// -----------------------------------------------------------------------------




if ($module = t12l_gpc_vars('m')) {
//    $out->assign('administration_form', t12l_module::administration($module));
    $module_result = t12l_module::administration($module);
//    t12l_print_a($module_result['module_form']);
    $out->assign('module_message',      $module_result['module_form']['module_message']);
    $out->assign('administration_form', array_merge($module_result['module_form']['elements'], $module_result['module_form']['module_additional']));
    $out->assign('form_attributes',     $module_result['module_form']['attributes']);
    $out->assign('module_title',        $module_result['module_title']);
    $out->assign('module_description',  $module_result['module_description']);
    $out->assign('module_name',         $module_result['module_name']);
    $out->assign('display_form',        true);
}

// -----------------------------------------------------------------------------



// List all installed modules
$module_list = t12l_module::module_list();

// -----------------------------------------------------------------------------



// List all available modules
function sort_modules($a, $b)
{                
    $x = $a['installed'];
    $y = $b['installed'];

    if ($x == $y) return 0;
    return ($x < $y) ? 1 : -1;
}
if (!t12l_gpc_vars('m')) {
    $available_modules = t12l_module::available_module_list();
//    usort($available_modules, 'sort_modules');
    $out->assign('available_modules', $available_modules);    
}

// -----------------------------------------------------------------------------




// Output
$out->assign('module_list', $module_list);
$out->assign('display_setting_navigation', true);
$out->finish();






?>
