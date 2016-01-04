<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 * 
 * Version 1.1.0
 */




/**
 * Mail comments to admin
 */
class gentlesource_module_cron extends gentlesource_module_common
{


    /**
     * Text of language file
     */
    var $text = array();

// -----------------------------------------------------------------------------




    /**
     *  Setup
     * 
     * @access public
     */
    function gentlesource_module_cron()
    {
        $this->text = $this->load_language();
        
        // Configuration
        $this->add_property('name',         $this->text['txt_module_name']);
        $this->add_property('description',  $this->text['txt_module_description']);
        $this->add_property('trigger',  
                                array(  
                                        'core'
                                        )
                                );
        
        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',  
                                array(  
                                        'module_cron_active',
                                        'module_cron_url',
                                        'module_cron_time',
                                        'module_cron_last',
                                        )
                                );
        
        // Default values
        $this->add_property('module_cron_active',   'N');
        $this->add_property('module_cron_time',     60); // Seconds
        $this->add_property('module_cron_url_opening_mode', 'fopen'); // fopen, curl
        
        // Get settings from database
        $this->get_settings();
        
        // Set module status 
        $this->status('module_referrer_log_active', 'N');
    }

// -----------------------------------------------------------------------------




    /**
     *  Administration
     * 
     * @access public
     */
    function administration()
    {
        $form = array();
        
        $form['module_cron_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_enable_module'],
            'description'   => $this->requirements(),
            'required'      => true
            );
        
        $form['module_cron_url'] = array(
            'type'          => 'string',
            'label'         => $this->text['txt_url'],
            'description'   => '',
            'required'      => true
            );
            
        $form['module_cron_time'] = array(
            'type'          => 'string',
            'label'         => $this->text['txt_cron_time'],
            'description'   => '',
            'required'      => true
            );

        return $form;
    }

// -----------------------------------------------------------------------------




    /**
     * Processing the content
     * 
     * @access public
     */
    function process($trigger, &$settings, &$data, &$additional)
    {
        if ($this->get_property('module_cron_active') != 'Y') {
            return false;
        }

        if ($trigger == 'core') {
            
            $time       = $this->get_property('module_cron_time');
            $last       = $this->get_property('module_cron_last');
            $current    = $this->current_timestamp();
            if ($current - $time < $last) {
                return false;
            }
            $this->set_setting('module_cron_last', $this->current_timestamp());
            $filename = $this->get_property('module_cron_url');
            
            if ($this->url_openening_mode() == 'fopen') {                
                $handle = fopen($filename, 'r');
                $contents = fread($handle, 4096);
                fclose($handle);
                return true;
            } elseif ($this->url_openening_mode() == 'curl') {            
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $filename);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_exec($ch);
                curl_close($ch);
                return true;
            }
            

        }
    }

// -----------------------------------------------------------------------------




    /**
     * Check requirements - either allow_url_fopen = true or curl functions
     * 
     * @access public
     */
    function url_openening_mode()
    {
        $mode = array();
        if (ini_get('allow_url_fopen') == true
                and function_exists('fopen') == true) {
            $mode[] = 'fopen';
        }
        if (function_exists('curl_init') == true) {
            $mode[] = 'curl';
        }
        if ($this->get_property('module_cron_url_opening_mode') == 'fopen'
                and in_array('fopen', $mode)) {
            array_unshift($mode, 'fopen');
        }
        if ($this->get_property('module_cron_url_opening_mode') == 'curl'
                and in_array('curl', $mode)) {
            array_unshift($mode, 'curl');
        }
        if (isset($mode[0])) {
            return $mode[0];
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Check requirements - either allow_url_fopen = true or curl functions
     * 
     * @access public
     */
    function requirements()
    {
        $text = $this->text['txt_enable_module_description'];
             
        if ($this->url_openening_mode() == 'fopen') {
            $text .= $this->text['txt_url_opening_mode_fopen'];
            return $text;
        } elseif ($this->url_openening_mode() == 'curl') {
            $text .= $this->text['txt_url_opening_mode_curl'];
            return $text;
        }
        $text .= $this->text['txt_url_opening_requirements'];
        
        return $text;
    }

// -----------------------------------------------------------------------------




} // End of class








?>
