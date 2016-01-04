<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */



define('T12L_ROOT', '../');



// Settings
$t12l_detail_template                = 'setting.tpl.html';

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
require 'setting_form.inc.php';


// Validate form
$message = array();
if ($form->validate()) {
    
    // Write data as settings    
    if (false == $t12l['demo_mode']) {
        foreach ($t12l['_post'] AS $name => $value)
        {
            if (!in_array($name, $t12l['setting_names'])) {
                continue;
            }
            t12l_setting::write($name, $value);
        }
        $t12l['message'][] = $t12l['text']['txt_update_data_successful'];
    } else {
        $t12l['message'][] = $t12l['text']['txt_disabled_in_demo_mode'];
    }
}


// Get setting data
$settings = t12l_setting::read_all();
$input_data = array_merge($t12l, $settings);
$form->setDefaults($input_data);


require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($out->get_object, true);
$form->accept($renderer);


// Assign array with form data
$out->assign('form', $renderer->toArray()); 


// Current script server path 
if (false == $t12l['demo_mode']) {
    $script_server_path = str_replace('admin', '', str_replace('\\', '/', getenv('DOCUMENT_ROOT') . dirname($_SERVER['PHP_SELF'])));
} else {
    $script_server_path = '/example/path/to/comment/script/';
}
$out->assign('script_server_path', $script_server_path);

// -----------------------------------------------------------------------------


// Tabber default 
$tabber_default = array(
                    'tabber_generalsettings_default'    => '',
                    'tabber_mailsettings_default'       => '',
                    );
                    
if (isset($t12l['post']['save_tab_general'])) {
    $tabber_default['tabber_generalsettings_default'] = 'tabbertabdefault';
}
if (isset($t12l['_post']['save_tab_mail'])) {
    $tabber_default['tabber_mailsettings_default'] = 'tabbertabdefault';
}
                    
$out->assign($tabber_default);

// -----------------------------------------------------------------------------



// Module list
$out->assign('module_list', t12l_module::module_list());

// -----------------------------------------------------------------------------


// Output
$out->assign('display_setting_navigation', true);
$out->finish();






?>
