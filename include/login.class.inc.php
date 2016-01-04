<?php

/** 
 * GentleSource Comment Script - identifier.class.inc.php
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */

require_once 'session.class.inc.php';




/**
 * Handle logins
 */
class t12l_login
{




    /**
     * Start login process
     * 
     */
    function t12l_login($level = 1)
    {
        global $t12l;
        
        
        if ($level <= 0) {
            return true;
        }
        
        if ($this->status() == true) {
            $this->login_exists();
        } else {
            if (t12l_gpc_vars('d') == 'r') {   
                $this->reset_form();
            } elseif (t12l_gpc_vars('c')) {
                $this->reset_password();
            } else {
                $this->login_starts();
            }
        }

        //Log user out
        if (t12l_gpc_vars('l') == 'o') {        
            t12l_session::destroy();
            header('Location: ' . $t12l['logout_redirect'] . dirname($_SERVER['PHP_SELF']) . '/');
            exit; 
        }
                
    }

// -----------------------------------------------------------------------------




    /**
     * Return login status
     * 
     */
    function status()
    {
        if ($data = t12l_session::get()
                and isset($data['login_status'])
                and $data['login_status'] == true) {
            return true;
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Manage existing login
     * 
     */
    function login_exists()
    {
        return true;
    }

// -----------------------------------------------------------------------------




    /**
     * Start login
     * 
     */
    function login_starts()
    {
        global $t12l;
        
        // Configuration
        $detail_template                = 'login.tpl.html';
        $t12l['alternative_template']    = 'admin';
        $message                        = array();
    
        // Includes
        require_once 'HTML/QuickForm.php';
    
        // Start output handling
        $out = new t12l_output($detail_template);
    
        // Start form field handling
        $form = new HTML_QuickForm('login', 'POST');
        require_once 'login_form.inc.php';


        // Validate form
        if ($form->validate()) {
            // Get login data
            if ($ser = t12l_setting::read('administration_login')) {
                $login_data = unserialize($ser['setting_value']);
                if (t12l_gpc_vars('login_name') == $login_data['login']
                        and md5(t12l_gpc_vars('password')) == $login_data['password']) {
                    $login_data['login_status'] = true;
                    t12l_session::add($login_data);
                    header('Location: ' . $t12l['login_redirect'] . $_SERVER['PHP_SELF']);
                    exit; 
                } else {
                    $t12l['message'][] = $t12l['text']['txt_login_failed'];
                }
            }
        }


        require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';    
        $renderer =& new HTML_QuickForm_Renderer_ArraySmarty($out->get_object, true);
           
        $form->accept($renderer);


        // Assign array with form data
        $out->assign('form', $renderer->toArray()); 
        
        
        // Output
        $out->finish();
        exit;

    }

// -----------------------------------------------------------------------------




    /**
     * Reset
     * 
     */
    function reset_form()
    {
        global $t12l;
        
        // Configuration
        $detail_template                = 'reset.tpl.html';
        $message                        = array();
        $show_form                      = true;
    
        // Includes
        require_once 'HTML/QuickForm.php';
    
        // Start output handling
        $out = new t12l_output($detail_template);
    
        // Start form field handling
        $form = new HTML_QuickForm('login', 'POST');
        require_once 'reset_form.inc.php';


        // Validate form
        if ($form->validate()) {
            // Get login data
            if ($ser = t12l_setting::read('administration_login')) {
                $login_data = unserialize($ser['setting_value']);
                if (isset($t12l['_post']['login_name']) 
                        and $t12l['_post']['login_name'] == $login_data['login']) {
                    if ($this->reset_mail() == true) {
                        $t12l['message'][] = $t12l['text']['txt_reset_mail_sent'];
                        $show_form = false;
                    } else {
                        $t12l['message'][] = $t12l['text']['txt_reset_mail_not_sent'];
                    } 
                } else {
                    $t12l['message'][] = $t12l['text']['txt_login_name_not_exists'];
                }
            }
            
        }


        require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';    
        $renderer =& new HTML_QuickForm_Renderer_ArraySmarty($out->get_object, true);
           
        $form->accept($renderer);


        // Assign array with form data
        $out->assign('form', $renderer->toArray()); 
        
        
        // Output
        $t12l['alternative_template'] = 'admin';
        $out->assign('show_form', $show_form);
        $out->finish();
        exit;

    }

// -----------------------------------------------------------------------------




    /**
     * Send reset mail
     * 
     */
    function reset_mail()
    {
        global $t12l;
        
        // Create link
        $random = t12l_create_random(20); 
        $part   = $t12l['mail_link'];
        $link[] = $part['protocol']; 
        $link[] = $part['server']; 
        $link[] = $part['path'];
        $link[] = '?c=' .  $random;
        
        // Add code to admin account
        if ($ser = t12l_setting::read('administration_login')) {
            $login_data = unserialize($ser['setting_value']);
            $arr = array(   'login'         => $login_data['login'],
                            'email'         => $login_data['email'],
                            'password'      => $login_data['password'],
                            'reset_code'    => $random
                            );
            $ser = serialize($arr);
            t12l_setting::write('administration_login', $ser);
        } else {
            return false;
        }
        
        
        // Send reset mail
        $detail_template                = 'reset.tpl.txt';
        $t12l['alternative_template']    = 'mail';
    
        // Start output handling
        $out = new t12l_output($detail_template);
        $out->assign('reset_link', join('', $link)); 
        $coutput = $out->finish_mail();
        
        // Send mail off
        include 'mail.class.inc.php';        
        if (t12l_mail::send( $login_data['email'], 
                            $t12l['text']['txt_reset_mail_subject'],                            
                            $coutput, 
                            $t12l['mail_from'])) {
            return true;
        }
        
    }

// -----------------------------------------------------------------------------




    /**
     * Reset user password
     * 
     */
    function reset_password()
    {
        global $t12l;
        
        // Configuration
        $detail_template                = 'reset_password.tpl.html';
        $t12l['alternative_template']    = 'admin';
        $message                        = array();
    
        // Includes
        require_once 'HTML/QuickForm.php';
    
        // Start output handling
        $out = new t12l_output($detail_template);
    
        // Start form field handling
        $form = new HTML_QuickForm('login', 'POST');
        require_once 'reset_password_form.inc.php';
        $form->setDefaults(array('c' => t12l_gpc_vars('c')));


        // Validate form
        $show_form = true;
        if ($form->validate()) {
            // Get login data
            if ($ser = t12l_setting::read('administration_login')) {                
                // Change admin password
                if ($ser = t12l_setting::read('administration_login')) {
                    $login_data = unserialize($ser['setting_value']);
                    if (isset($login_data['reset_code'])
                            and $login_data['reset_code'] == $t12l['_post']['c']) {
                        $arr = array(   'login'         => $login_data['login'],
                                        'email'         => $login_data['email'],
                                        'password'      => md5($t12l['_post']['password'])
                                        );
                        $ser = serialize($arr);
                        t12l_setting::write('administration_login', $ser);
                        $t12l['message'][] = $t12l['text']['txt_new_password_set'];
                        $show_form = false;
                    } else {
                        $t12l['message'][] = $t12l['text']['txt_reset_code_not_exists'];
                    }
                }
            }
            
        }


        require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';    
        $renderer =& new HTML_QuickForm_Renderer_ArraySmarty($out->get_object, true);
           
        $form->accept($renderer);


        // Assign array with form data
        $out->assign('form', $renderer->toArray()); 
        

        // Output
        $out->assign(array('show_form' => $show_form));
        $out->finish();
        exit;

    }

// -----------------------------------------------------------------------------
    
    
} // End of class







?>
