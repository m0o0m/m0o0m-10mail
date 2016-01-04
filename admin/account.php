<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */



define('T12L_ROOT', '../');



// Settings
$t12l_detail_template                = 'admin_account.tpl.html';

define('T12L_ALTERNATIVE_TEMPLATE', 'admin');
define('T12L_LOGIN_LEVEL', 1);


// Include
require T12L_ROOT . 'include/core.inc.php';



// Start output handling
$out = new t12l_output($t12l_detail_template);


// Handle and validate form
require_once 'HTML/QuickForm.php';


// Start form handler
$form = new HTML_QuickForm('account', 'POST');


// Get form configuration
require 'account_form.inc.php';


// Validate form
$show_form  = 'yes';
$message    = array();
if ($form->validate()) {
    $show_form = 'no';
    
    // Write data as settings
    if (false == $t12l['demo_mode']) {
        $arr = array(   'login'     => $t12l['_post']['login_name'],
                        'email'     => $t12l['_post']['email'],
                        'password'  => md5($t12l['_post']['password'])
                        );
        $ser = serialize($arr);
        t12l_setting::write('administration_login', $ser);
        $t12l['message'][] = $t12l['text']['txt_update_data_successful'];
    } else {
        $t12l['message'][] = $t12l['text']['txt_disabled_in_demo_mode'];
    }
} else {
    if (sizeof($t12l['_post']) > 0) {
        $t12l['message'][] = $t12l['text']['txt_fill_out_required'];
    }
}


// Get login data
$ser = t12l_setting::read('administration_login');
$login_data = unserialize($ser['setting_value']);
$input_data = array('login_name'    => $login_data['login'],
                    'email'         => $login_data['email']);
$form->setDefaults($input_data);



require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($out->get_object, true);
           
$form->accept($renderer);


// Assign array with form data
$out->assign('form', $renderer->toArray()); 




// Output
$out->assign('show_form', $show_form);
$out->assign('message', $message);
$out->finish();






?>
