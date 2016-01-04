<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


$form->addElement('text', 'login_name', $t12l['text']['txt_login_name'], array('tabindex' => 1));
$form->addElement('password', 'password', $t12l['text']['txt_password'], array('tabindex' => 2));
$form->addElement('submit', 'login', $t12l['text']['txt_login'], array('tabindex' => 3));

$form->addRule('login_name', $t12l['text']['txt_enter_login_name'], 'required');
$form->addRule('password', $t12l['text']['txt_enter_password'], 'required');








?>
