<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




/**
 * 
 */
class t12l_update
{
    
    /**
     * @var string
     * @access private
     */
//    var $default;

// -----------------------------------------------------------------------------




    /**
     * Manage installation
     * 
     */
    function t12l_update()
    {
    }
    
// -----------------------------------------------------------------------------  




    /**
     * Check if script and database table structure version match 
     * 
     */
    function status()
    {
        global $t12l;
        
        if (!isset($t12l['database_version'])) {
            $database_version = '1.0.0';
        } else {
            $database_version = $t12l['database_version'];
        } 
        $script_version = $t12l['version'];
        if ($t12l['version'] == '1.0') {
            $script_version = '1.0.0';
        }
        
        if (version_compare($script_version, $database_version) <= 0) {
            return true;
        }       
    }
    
// -----------------------------------------------------------------------------  




    /**
     * Start installation process
     * 
     */
    function start()
    {
        global $t12l;
        
        // Configuration
        $detail_template                = 'update.tpl.html';
        $message                        = array();
        
        $t12l['website_name']           = $t12l['text']['txt_disposable_email_address'];
        $t12l['website_description']    = $t12l['text']['txt_website_down_maintenance'];
        $t12l['output'] = array(
                    'software'                      => $t12l['software'],
                    'version'                       => $t12l['version'],
                    'demo_mode'                     => $t12l['demo_mode'],
                    'shut_down'                     => $t12l['shut_down'],
                    'display_shut_down_message'     => $t12l['display_shut_down_message'],
                    'shut_down_message'             => $t12l['shut_down_message'],
                    'script_url'                    => $t12l['script_url'],
                    'complete_script_url'           => $t12l['server_protocol'] . $t12l['server_name'] . $t12l['script_url'],
                    'website_name'                  => $t12l['website_name'],
                    'website_description'           => $t12l['website_description'],
                    'website_title'                 => str_replace(array("\r", "\n"), '', strip_tags($t12l['website_name'])),
                    'website_meta_description'      => str_replace(array("\r", "\n"), '', strip_tags($t12l['website_description'])),
                    'website_utf8_title'            => t12l_utf8_encode(htmlspecialchars($t12l['website_name'])),
                    'website_utf8_description'      => t12l_utf8_encode(htmlspecialchars($t12l['website_description'])),
                    'display_language_selection'    => $t12l['display_language_selection'],
                    'language_selector_mode'        => $t12l['language_selector_mode'],
                    'available_languages'           => $t12l['available_languages'],
                    'page_url_encoded'              => urlencode($t12l['server_protocol'] . $t12l['server_name'] . getenv('REQUEST_URI')),
                    );
              
              
        // Includes
        require_once 'HTML/QuickForm.php';
    
        // Start output handling
        $out = new t12l_output($detail_template);
    
        // Start form field handling
        $form = new HTML_QuickForm('install', 'POST');
        require_once 'update_form.inc.php';


        // Validate form
        $show_form = true;
        $db_error  = false;
        if ($form->validate()) {
            
            $dsn = array(   'phptype'   => 'mysql',
                            'hostspec'  => $t12l['_post']['hostname'],
                            'database'  => $t12l['_post']['database'],
                            'username'  => $t12l['_post']['username'],
                            'password'  => $t12l['_post']['dbpassword']
                            );
            
            
            // Check if dsn data from the form match the dsn data from dbconfig.php 
            $database_data = true;
            foreach ($dsn AS $key => $value)
            {
                if (!isset($t12l['dsn'][$key]) or $t12l['dsn'][$key] != $value) {
                    $database_data = false;                    
                }
            }

            if ($database_data != true) {
                $t12l['message'][] = $t12l['text']['txt_enter_correct_database_data'];
            }
            
            // Check if admin data from the form match the admin data from the settings 
            $admin_data = false;
            if ($ser = t12l_setting::read('administration_login')) {
                $login_data = unserialize($ser['setting_value']);
                if ($t12l['_post']['login_name'] == $login_data['login']
                        and md5($t12l['_post']['password']) == $login_data['password']) {
                    $admin_data = true;
                }
            }            

            if ($admin_data != true) {
                $t12l['message'][] = $t12l['text']['txt_enter_correct_admin_data'];
            }
            
            // Process update if everything is okay
            if ($database_data == true and $admin_data == true){
                
                
                if (!$this->process($dsn)) {
                    $t12l['message'][]  = $t12l['text']['txt_update_failed'];
                } else {                    
                      
                    t12l_setting::write('database_version', $t12l['version']);

                    $t12l['message'][] = $t12l['text']['txt_update_successful'];
                    $show_form = false;
                }
            }
            
        } else {
            if (sizeof($t12l['_post']) > 0) {
                $t12l['message'][]  = $t12l['text']['txt_fill_out_required'];
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




    /**
     * Connect to database
     * 
     * @access private
     */
    function connect($dsn)
    {
        global $t12l;
        if (!isset($GLOBALS['database_connection'])) {
            $db =& MDB2::connect($dsn);
            if (PEAR::isError($db)) {
                t12l_system_debug::add_message($db->getMessage(), $db->getDebugInfo(), 'system');
            } else {
                $db->setFetchMode(MDB2_FETCHMODE_ASSOC);
                $GLOBALS['database_connection'] = $db;
            }
        }
        if (isset($GLOBALS['database_connection'])) {
            return $GLOBALS['database_connection'];
        }
    }

//------------------------------------------------------------------------------




    /**
     * Database query
     * 
     * @access public
     * @param string $sql SQL statement
     * 
     * @return mixed  a new DB_result object for successful SELECT queries
     *                 or DB_OK for successul data manipulation queries.
     *                 A DB_Error object on failure.
     */
    function query($dsn, $sql)
    {
        if ($db = $this->connect($dsn)) {
            $res =& $db->query($sql);
            if (PEAR::isError($res)) {
                t12l_system_debug::add_message($res->getMessage(), $res->getDebugInfo(), 'error', $res->getBacktrace());
                t12l_system_debug::add_message('SQL Statement', $sql, 'error');
                return false;
            } else {
                return $res;
            }
        }        
    }

//------------------------------------------------------------------------------




    /**
     * Process SQL statements
     * 
     * @access private
     */
    function process($dsn)
    {
        global $t12l;

        $error = false;
        $sql = $this->parse_sql($this->select_update_files());
        reset($sql);
        foreach ($sql AS $statement)
        {
            // Replace prefix
            $statement = str_replace('{prefix}', strtolower($t12l['database_table_prefix']), $statement);
            if (!$this->query($dsn, $statement)) {
                $error = true;
            }
        }
        if ($error == false) {
            return true;
        }
    }

//------------------------------------------------------------------------------




    /**
     * Parse SQL file
     * 
     * @access private
     */
    function parse_sql($sql)
    {
        if (!is_array($sql)) {
            $statement  = explode("\n", $sql);
        } else {
            $statement = $sql;
        }
        $num        = sizeof($statement);
        $previous   = '';
        $result     = array();
        for ($i = 0; $i < $num; $i++) {
            $line = trim($statement[$i]); 
            // Check for line breaks within lines
            if (substr($line, -1) != ';') {
                $previous .= $line;
                continue;
            } 
    
            if ($previous != '') {
                $line = $previous . $line;
            } 
            $previous = '';
    
            $result[] = $line;
        } 
    
        if (isset($result)) {
            return $result;
        } 
    } 


//------------------------------------------------------------------------------




    /**
     * 
     */
    function select_update_files()
    {
        global $t12l;
        $list = array();
        require_once 'Find.php';
        if ($items = &File_Find::glob( '#update_(.*?)\.sql#', T12L_ROOT . 'include/sql/', "perl" )) {
//            asort($items);
            
            if (isset($t12l['database_version'])) {
                $current_version = $t12l['database_version'];
                if ($current_version == '1.0') {
                    $current_version = '1.0.0';
                }
            } else {
                $current_version = '1.0.0';
            }
            
            // $ver contains the older version
            // $sion contains the newer version
            while (list($key, $val) = each($items))
            {
                $new_version = substr($val, strlen('update_'));
                $new_version = substr($new_version, 0, strrpos($new_version, '-'));
                
                if (version_compare($new_version, $current_version) >= 0) {
                    $list[$new_version] = $val;
                }
                
            }
        }
        ksort($list);
        
        $sql = array();
        foreach ($list AS $file)
        {
            $sql[] = file_get_contents(T12L_ROOT . 'include/sql/' . $file);
        }
        return join("\n", $sql);
    } 


//------------------------------------------------------------------------------





} // End of class








?>
