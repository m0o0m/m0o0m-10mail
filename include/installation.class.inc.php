<?php

/** 
 * GentleSource Installation Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */







/**
 * 
 */
class t12l_installation
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
    function t12l_installation()
    {
    }
    
// -----------------------------------------------------------------------------  




    /**
     * Check if dbconfig.php exists
     * 
     */
    function status()
    {
        if (is_file(T12L_ROOT . 'dbconfig.php')) {
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
        
        $t12l['current_language']    = t12l_language::get($t12l['default_language']);
        $t12l['text']                = t12l_language::load($t12l['current_language']);
        
        
        $t12l['website_name']           = $t12l['text']['txt_disposable_email_address'];
        $t12l['website_description']    = $t12l['text']['txt_script_not_installed'];
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
                    
        // Configuration
        $detail_template                = 'installation.tpl.html';
        
    
        // Includes
        require_once 'HTML/QuickForm.php';
    
        // Start output handling
        $out = new t12l_output($detail_template);
    
        // Start form field handling
        $form = new HTML_QuickForm('install', 'POST');
        require_once 'installation_form.inc.php';

        
        
        // Check requirements
        $script_path = str_replace('admin', '', str_replace('\\', '/', getenv('DOCUMENT_ROOT') . dirname($_SERVER['PHP_SELF'])));
        if (!is_writable(T12L_ROOT)) {
            $script_folder_writable_status = sprintf($t12l['text']['txt_not_okay'] . ': ' . $t12l['text']['txt_script_folder_not_writable'], $script_path);
        } else {
            $script_folder_writable_status = sprintf($t12l['text']['txt_okay'] . ': ' . $t12l['text']['txt_script_folder_is_writable'], $script_path);
        }
        if (!is_writable($t12l['cache_path'])) {
            $cache_folder_writable_status = sprintf($t12l['text']['txt_not_okay'] . ': ' . $t12l['text']['txt_cache_folder_not_writable'], '/' . $t12l['cache_directory']);
        } else {
            $cache_folder_writable_status = sprintf($t12l['text']['txt_okay'] . ': ' . $t12l['text']['txt_cache_folder_is_writable'], '/' . $t12l['cache_directory']);
        }


        // Validate form
        $show_form = true;
        $db_error  = false;
        if ($form->validate()) {
            define('T12L_ADDRESS_TABLE',    strtolower($t12l['_post']['prefix']) . 'address');
            define('T12L_MAIL_TABLE',       strtolower($t12l['_post']['prefix']) . 'mail');
            define('T12L_SETTING_TABLE',    strtolower($t12l['_post']['prefix']) . 'setting');

            $t12l['tables']['address']      = T12L_ADDRESS_TABLE;
            $t12l['tables']['mail']         = T12L_MAIL_TABLE;
            $t12l['tables']['setting']      = T12L_SETTING_TABLE;
            $dsn = array(   'phptype'   => 'mysql',
                            'hostspec'  => $t12l['_post']['hostname'],
                            'database'  => $t12l['_post']['database'],
                            'username'  => $t12l['_post']['username'],
                            'password'  => $t12l['_post']['dbpassword']
                            );
            $t12l['dsn'] = $dsn;
            if (!$db = $this->connect($dsn)) {
                $t12l['message'][] = $t12l['text']['txt_enter_correct_database_data'];
            } else {
                if (!$this->process($dsn)) {
                    $t12l['message'][] = $t12l['text']['txt_installation_failed'];
                } else {
                    
                    // Set admin account
                    $arr = array(   'login'     => $t12l['_post']['login_name'],
                                    'email'     => $t12l['_post']['email'],
                                    'password'  => md5($t12l['_post']['password'])
                                    );
                    $ser = serialize($arr);
                    t12l_setting::write('administration_login', $ser);
                    
                    // Set script URL
                    // $script_url = str_replace('admin', '', str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])));
                    $script_url = str_replace('//', '/', str_replace('admin', '', str_replace('\\', '/', dirname($_SERVER['PHP_SELF']) . '/')));
                    t12l_setting::write('script_url', $script_url);
                    t12l_setting::write('database_version', $t12l['version']);
                    $show_form = false;
                                               
                    
                    // Write dbconfig.php file
                    $write_file = true;
                    $dsn['prefix'] = strtolower($t12l['_post']['prefix']);
                    if (!$this->install_file(
                                    T12L_ROOT . 'include/dbconfig.php.tpl', 
                                    $dsn, 
                                    T12L_ROOT . 'dbconfig.php')) {
                        $write_file = false;
                        $t12l['message'][] = $t12l['text']['txt_write_dbconfig_failed'];
                    }
                                        
                    
                    // Write settings
                    if ($write_file == true) {
                        $t12l['message'][] = $t12l['text']['txt_installation_successful'];                    
                    }
                }
            }
            
        } else {
            if (sizeof($t12l['_post']) > 0) {
                $t12l['message'][] = $t12l['text']['txt_fill_out_required'];
            }
        }


        require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';    
        $renderer =& new HTML_QuickForm_Renderer_ArraySmarty($out->get_object, true);
           
        $form->accept($renderer);


        // Assign array with form data
        $out->assign('form', $renderer->toArray()); 

        // Output
        $out->assign(array(
                        'show_form'                     => $show_form,
                        'cache_folder_writable_status'  => $cache_folder_writable_status,
                        'script_folder_writable_status' => $script_folder_writable_status,
                        )
                    );
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
        if (!isset($GLOBALS['t12l_database_connection'])) {
            $db =& MDB2::connect($dsn);
            if (PEAR::isError($db)) {
                t12l_system_debug::add_message($db->getMessage(), $db->getDebugInfo(), 'system');
            } else {
                $db->setFetchMode(MDB2_FETCHMODE_ASSOC);
                $GLOBALS['t12l_database_connection'] = $db;
            }
        }
        if (isset($GLOBALS['t12l_database_connection'])) {
            return $GLOBALS['t12l_database_connection'];
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
        $file = T12L_ROOT . 'include/sql/install.sql';
        $error = false;
        if (is_file($file)) {
            $sql = $this->parse_sql(file($file));
            reset($sql);
            foreach ($sql AS $statement)
            {
                // Replace prefix
                $statement = str_replace('{prefix}', strtolower($t12l['_post']['prefix']), $statement);
                if (!$this->query($dsn, $statement)) {
                    $error = true;
                }
            }
        } else {
            t12l_system_debug::add_message('Install File Not Found', $file, 'system');
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
        $num        = count($statement);
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
     * Write files
     * 
     * @param Array $data Data to be written into file
     * @param String $path Path to the place where the file is to be written
     * @param String $template Path to the template to be used
     * @access public
     */
    function install_file($source, $data, $target)
    {
        $content = join('', file($source));

        reset($data);
        foreach ($data AS $marker => $value)
        {
            $content = str_replace('{$' . $marker . '}', $value, $content);
        }
        
        if (file_put_contents($target, $content)) {
            return true;
        }        
    } 


//------------------------------------------------------------------------------





} // End of class








?>
