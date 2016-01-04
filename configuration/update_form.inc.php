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

$form->addElement('text', 'login_name', $t12l['text']['txt_login_name']);
$form->addElement('password', 'password', $t12l['text']['txt_password']);

$form->addElement('submit', 'update', $t12l['text']['txt_update']);

$form->addRule('hostname', $t12l['text']['txt_enter_hostname'], 'required');
$form->addRule('database', $t12l['text']['txt_enter_database'], 'required');
$form->addRule('username', $t12l['text']['txt_enter_username'], 'required');
$form->addRule('dbpassword', $t12l['text']['txt_enter_password'], 'required');

$form->addRule('login_name', $t12l['text']['txt_enter_login_name'], 'required');
$form->addRule('password', $t12l['text']['txt_enter_password'], 'required');






?>
