<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


$form->addElement('text', 'hostname', $t12l['text']['txt_hostname']);
$form->addElement('text', 'database', $t12l['text']['txt_database']);
$form->addElement('text', 'username', $t12l['text']['txt_username']);
$form->addElement('password', 'dbpassword', $t12l['text']['txt_password']);
$form->addElement('text', 'prefix', $t12l['text']['txt_table_prefix']);
$form->setDefaults(array('prefix' => 't12l_'));

$form->addElement('text', 'login_name', $t12l['text']['txt_login_name']);
$form->addElement('text', 'email', $t12l['text']['txt_email']);
$form->addElement('password', 'password', $t12l['text']['txt_password']);
$form->addElement('password', 'repeat', $t12l['text']['txt_password_repeat']);

$form->addElement('submit', 'install', $t12l['text']['txt_install']);

$form->addRule('hostname', $t12l['text']['txt_enter_hostname'], 'required');
$form->addRule('database', $t12l['text']['txt_enter_database'], 'required');
$form->addRule('username', $t12l['text']['txt_enter_username'], 'required');
$form->addRule('dbpassword', $t12l['text']['txt_enter_password'], 'required');

$form->addRule('login_name', $t12l['text']['txt_enter_login_name'], 'required');
$form->addRule('login_name', $t12l['text']['txt_syntax_alphanumeric'], 'alphanumeric');
$form->addRule('email', $t12l['text']['txt_enter_email'], 'required');
$form->addRule('email', $t12l['text']['txt_syntax_email'], 'email');
$form->addRule('password', $t12l['text']['txt_enter_password'], 'required');
$form->addRule('repeat', $t12l['text']['txt_repeat_password'], 'required');
$form->addRule(array('password', 'repeat'), $t12l['text']['txt_passwords_do_not_match'], 'compare');






?>
