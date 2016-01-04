<?php

/** 
 * GentleSource Module Common Class
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




/**
 * Common module methods
 * 
 */
class gentlesource_module_common
{


    /**
     * Module settings
     */
    var $module = array();


    /**
     * Module output
     */
    var $output = array();

// -----------------------------------------------------------------------------




    /**
     * Add property to setup
     */
    function add_property($name, $value)
    {
        $this->module[$name] = $value;
    }

// -----------------------------------------------------------------------------




    /**
     * Get property from setup
     */
    function get_property($name)
    {
        if (!isset($this->module[$name])) {
            return false;
        }
        return $this->module[$name];
    }

// -----------------------------------------------------------------------------




    /**
     * Get all property from setup
     */
    function get_all_properties()
    {
        return $this->module;
    }

// -----------------------------------------------------------------------------




    /**
     * Get settings from database and initiate some defaultsettings
     */
    function get_settings()
    {
        global $t12l;

        $properties = $this->get_property('setting_names');
        foreach ($properties AS $name)
        {
            if (array_key_exists($name, $t12l)) {
                $this->add_property($name, $t12l[$name]);

            }
        }
        
        // Default settings
        $this->add_property('module_url', $t12l['script_url'] . 'admin/module.php?m='. get_class($this));
        $this->add_property('module_path', T12L_ROOT . $t12l['module_directory'] . get_class($this) . '/');
        $this->add_property('system_root', T12L_ROOT);
    }

// -----------------------------------------------------------------------------




    /**
     * Set setting
     */
    function set_setting($name, $value)
    {
        t12l_setting::write($name, $value);
    }

// -----------------------------------------------------------------------------




    /**
     * Load language file
     */
    function load_language($language = null)
    {
        global $t12l;
        
        if ($language == null) {
            $language = $t12l['current_language'];
        }

        $default_folder  = 'language/';
        $language_folder = 'language/';
        
        // Use utf-8 folder if it exists
        if ($t12l['use_utf8'] == 'Y'
                and file_exists(T12L_ROOT . $t12l['module_directory'] . '/' . get_class($this) . '/language/utf-8/')) {
            $language_folder = 'language/utf-8/';
        }
        
        // Go back to default language folder if language does not exists in utf-8 folder
        if (!is_file(T12L_ROOT . $t12l['module_directory'] . '/' . get_class($this) . '/' . $language_folder . 'language.' . $language . '.php')
                and is_file(T12L_ROOT . $t12l['module_directory'] . '/' . get_class($this) . '/' . $default_folder . 'language.' . $language . '.php')) {
            $language_folder = $default_folder;
        }
        
        // Go back to default language if language file does not exists
        if (!is_file(T12L_ROOT . $t12l['module_directory'] . '/' . get_class($this) . '/' . $language_folder . 'language.' . $language . '.php')) {
            $language = 'en';
        }
        include T12L_ROOT . $t12l['module_directory'] . '/' . get_class($this) . '/' . $language_folder . 'language.' . $language . '.php';
        
        return $text;
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
        $form['module_name'] = array(
            'type'      => '', // bool|string|number|email|textarea
            'label'     => '', // Label of the field
            'required'  => '', // true|false
            );
        return $form;
    }

// -----------------------------------------------------------------------------




    /**
     * Set Output
     * 
     * @access public
     */
    function set_output($trigger, $content)
    {
        $this->output[$trigger] = $content;
    }

// -----------------------------------------------------------------------------




    /**
     * Get Output
     * 
     * @access public
     */
    function get_output($trigger)
    {
        $content = '';
        if (isset($this->output[$trigger])) {
            $content = $this->output[$trigger];
        }
        return $content;
    }

// -----------------------------------------------------------------------------




    /**
     * Set session variable
     * 
     * @access public
     */
    function set_session_property($arr)
    {
        t12l_session::add($arr);
    }

// -----------------------------------------------------------------------------




    /**
     * Get session variable
     * 
     * @access public
     */
    function get_session_property($item)
    {
        return t12l_session::get($item);
    }

// -----------------------------------------------------------------------------




    /**
     * Get session variable
     * 
     * @access public
     */
    function get_output_object()
    {
        $out = new t12l_output();
        return $out;
    }

// -----------------------------------------------------------------------------




    /**
     * Send e-mail
     * 
     * @access public
     */
    function send_mail($recipient, $subject, $body, $from)
    {
        require_once 'mail.class.inc.php';        
        if (t12l_mail::send( $recipient, 
                            $subject,                            
                            $body, 
                            $from)) {
            return true;
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Return database connection
     * 
     * @access public
     */
    function database_query($sql, $data = array())
    {
        if ($db = t12l_database::query($sql, $data)) {
            return $db;
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Show module status in order to skip processing modules that are turned
     * off
     * 
     * @param string $name Property item that knows whether or not the module is
     * activated
     * @param string $off Value off the item that indicates that the module is
     * turned off
     */
    function status($name, $off)
    {
        if ($this->get_property($name) != $off) {
            $this->add_property('module_active', true);
        } else {
            $this->add_property('module_active', false);
        }
    }

// -----------------------------------------------------------------------------



    /**
     * Return database connection
     * 
     * @access public
     */
    function current_timestamp()
    {
        return t12l_time::current_timestamp();
    }

// -----------------------------------------------------------------------------





} // End of class








?>
