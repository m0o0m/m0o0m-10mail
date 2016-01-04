<?php

/** 
 * GentleSource Temporary E-Mail
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


$form->addElement('text', 'setemailaddress');
$form->addElement('text', 'setemailaddressintern');
$form->addElement('submit', 'setemail', $t12l['text']['txt_set_temporary_email']);
$form->addElement('submit', 'getemail', $t12l['text']['txt_get_temporary_email']);

$form->addRule('setemailaddress', $t12l['text']['txt_enter_email'], 'required');
$form->addRule('setemailaddressintern', $t12l['text']['txt_syntax_email'], 'email');







?>
