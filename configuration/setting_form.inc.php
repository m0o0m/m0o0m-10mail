<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */



// Shut tool down
$shut_down = array();
$shut_down[] = &HTML_QuickForm::createElement('radio', null, null, $t12l['text']['txt_yes'], 'Y');
$shut_down[] = &HTML_QuickForm::createElement('radio', null, null, $t12l['text']['txt_no'], 'N');
$form->addGroup($shut_down, 'shut_down', $t12l['text']['txt_shut_down']);


// Show or hide turn off messages
$turn_off_messages = array();
$turn_off_messages[] = &HTML_QuickForm::createElement('radio', null, null, $t12l['text']['txt_yes'], 'Y');
$turn_off_messages[] = &HTML_QuickForm::createElement('radio', null, null, $t12l['text']['txt_no'], 'N');
$form->addGroup($turn_off_messages, 'display_shut_down_message', $t12l['text']['txt_display_turn_off_messages']);


// Debug mode
$debug_mode = array();
$debug_mode[] = &HTML_QuickForm::createElement('radio', null, null, $t12l['text']['txt_yes'], 'Y');
$debug_mode[] = &HTML_QuickForm::createElement('radio', null, null, $t12l['text']['txt_no'], 'N');
$form->addGroup($debug_mode, 'debug_mode', $t12l['text']['txt_debug_mode']);


// Shut down message
$form->addElement('textarea', 'shut_down_message', $t12l['text']['txt_shut_down_message']);


// Language
$select =& $form->addElement('select', 'default_language', $t12l['text']['txt_admin_language'], $t12l['available_languages']);
$select->setSize(1);
 
$select =& $form->addElement('select', 'frontend_language', $t12l['text']['txt_frontend_language'], $t12l['available_languages']);
$select->setSize(1); 

$language_selection = array();
$language_selection[] = &HTML_QuickForm::createElement('radio', null, null, $t12l['text']['txt_yes'], 'Y');
$language_selection[] = &HTML_QuickForm::createElement('radio', null, null, $t12l['text']['txt_no'], 'N');
$form->addGroup($language_selection, 'display_language_selection', $t12l['text']['txt_display_language_selection']);

$use_utf8 = array();
$use_utf8[] = &HTML_QuickForm::createElement('radio', null, null, $t12l['text']['txt_yes'], 'Y');
$use_utf8[] = &HTML_QuickForm::createElement('radio', null, null, $t12l['text']['txt_no'], 'N');
$form->addGroup($use_utf8, 'use_utf8', $t12l['text']['txt_use_utf8_language_files']);


// Script URL
$form->addElement('text', 'script_url', $t12l['text']['txt_script_url']);


// E-mail address lifetime
$form->addElement('text', 'lifetime', $t12l['text']['txt_email_lifetime'], array('style' => 'width:140px;'));
$liftetime_select =& $form->addElement('select', 'lifetime_unit', '', $time_units, array('style' => 'width:150px;'));
$liftetime_select->setSize(1);
$form->addRule('lifetime', $t12l['text']['txt_error_required'], 'required');


// Let user choose e-mail address
$set_address = array();
$set_address[] = &HTML_QuickForm::createElement('radio', null, null, $t12l['text']['txt_yes'], 'Y');
$set_address[] = &HTML_QuickForm::createElement('radio', null, null, $t12l['text']['txt_no'], 'N');
$form->addGroup($set_address, 'allow_set_email_address', $t12l['text']['txt_allow_set_email_address']);


// Activate e-mail RSS/Atom feed
$rss_feed = array();
$rss_feed[] = &HTML_QuickForm::createElement('radio', null, null, $t12l['text']['txt_yes'], 'Y');
$rss_feed[] = &HTML_QuickForm::createElement('radio', null, null, $t12l['text']['txt_no'], 'N');
$form->addGroup($set_address, 'activate_syndication', $t12l['text']['txt_activate_syndication']);


// E-mail address domain
$form->addElement('text', 'email_address_host_name', $t12l['text']['txt_email_domain']);


// Mailbox
$form->addElement('text', 'mailbox_hostname', $t12l['text']['txt_mailbox_hostname']);
$form->addElement('text', 'mailbox_username', $t12l['text']['txt_mailbox_username']);
$form->addElement('text', 'mailbox_password', $t12l['text']['txt_mailbox_password']);

$ssl_connection = array();
$ssl_connection[] = &HTML_QuickForm::createElement('radio', null, null, $t12l['text']['txt_yes'], 'Y');
$ssl_connection[] = &HTML_QuickForm::createElement('radio', null, null, $t12l['text']['txt_no'], 'N');
$form->addGroup($ssl_connection, 'mailbox_connect_ssl', $t12l['text']['txt_mailbox_connect_ssl']);

$non_validate = array();
$non_validate[] = &HTML_QuickForm::createElement('radio', null, null, $t12l['text']['txt_yes'], 'Y');
$non_validate[] = &HTML_QuickForm::createElement('radio', null, null, $t12l['text']['txt_no'], 'N');
$form->addGroup($non_validate, 'non_validate_command', $t12l['text']['txt_certificate_validation']);


// Website name and description
$form->addElement('text', 'website_name', $t12l['text']['txt_website_name']);
$form->addElement('textarea', 'website_description', $t12l['text']['txt_website_description']);


// Key for mail download
$form->addElement('text', 'download_mail_process_key', $t12l['text']['txt_mail_download_key']);



$form->addElement('submit', 'save_tab_general', $t12l['text']['txt_save_settings']);
$form->addElement('submit', 'save_tab_mail', $t12l['text']['txt_save_settings']);



//$arr = $form->getRegisteredRules();
//t12l_print_a($arr);




?>
