<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 * 
 * Version 1.0.0
 * 
 * Requrires PEAR Find
 */

define('MODULE_REFERRER_LOG_FILENAME',  'referrer_log.txt');
define('MODULE_REFERRER_LOG_FOLDER',    'logfile/');



/**
 * Referrer Log
 */
class gentlesource_module_referrer_log extends gentlesource_module_common
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
    function gentlesource_module_referrer_log()
    {
        $this->text = $this->load_language();
        
        // Configuration
        $this->add_property('name',         $this->text['txt_module_name']);
        $this->add_property('description',  $this->text['txt_module_description']);
        $this->add_property('trigger',  
                                array(  
                                        'core',
                                        'module_demo',
                                        'module_send_file',
                                        'backend_navigation',
                                        )
                                );
        
        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',  
                                array(  
                                        'module_referrer_log_active',
                                        )
                                );
        
        // Default values
        $this->add_property('module_referrer_log_active',   'N');
        
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
        
        $form['module_referrer_log_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_enable_module'],
            'description'   => $this->text['txt_enable_module_description'],
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
        // Module URL
        $module_url = $_SERVER['PHP_SELF'];
        if (isset($_SERVER['QUERY_STRING']) and $_SERVER['QUERY_STRING'] != '') {
            $module_url .= '?' . $_SERVER['QUERY_STRING'] . '&amp;';
        } else {
            $module_url .= '?m=' . get_class($this) . '&amp;';
        }
        

        // Show link to in admin navigation
        if ('backend_navigation' == $trigger) {
            $out = $this->get_output_object();
            $out->assign($this->text);
            $out->assign('module_path', $this->get_property('module_path'));
            $out->assign('referrer_log', $this->get_property('module_url') . '&amp;show=file');
            $out->set_template_dir($this->get_property('module_path') . 'template/');
            $content = $out->fetch('referrer_link.tpl.html');
            $this->set_output($trigger, $content);
        } 


        // Module Path
        $file = $this->get_property('module_path') . MODULE_REFERRER_LOG_FOLDER . MODULE_REFERRER_LOG_FILENAME;

        // Read log file
        if ($trigger == 'module_send_file' 
                and isset($data['module'])
                and trim($data['module']) == get_class($this)
                and isset($settings['_get']['show']) 
                and $settings['_get']['show'] == 'file') {
            
            if (!is_file($file)) {
                exit;
            }
            
            require_once 'File.php';
            header('Content-Type: text/plain');  
            while (false !== $buf = File::read($file)) 
            {
                if (!PEAR::isError($buf)) {
                    echo $buf;
                }
            }
            exit;
        }
        
        // Display log file
        if ($trigger == 'module_demo'
                and isset($data['module'])
                and trim($data['module']) == get_class($this)) {
            if (is_file($file)) {
                $content  = '<p><a href="' . $module_url . 'show=file" target="_blank">' . $this->text['txt_referrer_log_file'] . '</a></p>';
                $content .= '<p><iframe src="' . $module_url . 'show=file" style="width:100%;height:400px;border:0;padding:0;margin:0;" name="referrerlog"></iframe></p>';
                $this->set_output($trigger, $content);
            }
        }
        
        if ($trigger == 'core') {
            $referrer = '';
            if (isset($_SERVER['HTTP_REFERER']) 
                    and $_SERVER['HTTP_REFERER'] != '') {
                
                $url = parse_url($_SERVER['HTTP_REFERER']);
                if (isset($url['host'])     
                        and $url['host'] != '' 
                        and $url['host'] != $_SERVER['HTTP_HOST']) {
                    $referrer = $_SERVER['HTTP_REFERER'];
                }
            }
            
            if ($referrer != '') {
                
                require_once 'File.php';            
                $fp = new File();
                
                // Create folder and protecting .htaccess    
                $folder = $this->get_property('module_path') . MODULE_REFERRER_LOG_FOLDER;
                if (!is_dir($folder)) {
                    mkdir($folder);
                    $fp->writeLine($folder . '.htaccess', 'Deny from all');
                }
                
                // Log line
                $referrer = strip_tags($referrer);
                $time = $this->current_timestamp();
                $line[] = date($settings['text']['txt_date_format'], $time);
                $line[] = ' ('; 
                $line[] = date($settings['text']['txt_time_format'], $time);
                $line[] = ') - '; 
                $line[] = $referrer;
                $line[] = ' - ';
                $line[] = getenv('REQUEST_URI');
                            
                $fp->writeLine($file, join('', $line));
            }
        }
    }

// -----------------------------------------------------------------------------




} // End of class








?>
