<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


$form->addElement('text', 'login_name', $t12l['text']['txt_login_name']);
$form->addElement('text', 'email', $t12l['text']['txt_email']);
$form->addElement('password', 'password', $t12l['text']['txt_password']);
$form->addElement('password', 'repeat', $t12l['text']['txt_password_repeat']);
$form->addElement('submit', 'save', $t12l['text']['txt_save_account']);

$form->addRule('login_name', $t12l['text']['txt_enter_login_name'], 'required');
$form->addRule('login_name', $t12l['text']['txt_syntax_alphanumeric'], 'alphanumeric');
$form->addRule('email', $t12l['text']['txt_enter_email'], 'required');
$form->addRule('email', $t12l['text']['txt_syntax_email'], 'email');
$form->addRule('password', $t12l['text']['txt_enter_password'], 'required');
$form->addRule('repeat', $t12l['text']['txt_repeat_password'], 'required');
$form->addRule(array('password', 'repeat'), $t12l['text']['txt_passwords_do_not_match'], 'compare');








?>
