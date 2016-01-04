<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


$form->addElement('hidden', 'c');
$form->addElement('password', 'password', $t12l['text']['txt_password']);
$form->addElement('password', 'repeat', $t12l['text']['txt_password_repeat']);
$form->addElement('submit', 'save', $t12l['text']['txt_submit']);

$form->addRule('password', $t12l['text']['txt_enter_password'], 'required');
$form->addRule('repeat', $t12l['text']['txt_repeat_password'], 'required');
$form->addRule(array('password', 'repeat'), $t12l['text']['txt_passwords_do_not_match'], 'compare');








?>
