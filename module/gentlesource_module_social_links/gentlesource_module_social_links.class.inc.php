<?php

/** 
 * GentleSource Guestbook Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 * 
 * Version 1.0
 * 
 * Check http://www.twistermc.com/
 */




/**
 * Dummy Module
 */
class gentlesource_module_social_links extends gentlesource_module_common
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
    function gentlesource_module_social_links()
    {
        $this->text = $this->load_language();
        
        // Configuration
        $this->add_property('name',         $this->text['txt_module_name']);
        $this->add_property('description',  $this->text['txt_module_description']);
        $this->add_property('trigger',  
                                array(  
                                        'frontend_content_footer',
                                        'module_demo',
                                        )
                                );
        
        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',  
                                array(  
                                        'module_social_links_active',
                                        )
                                );
        
        // Default values
        $this->add_property('module_social_links_active',  'N');
        
        // Get settings from database
        $this->get_settings();

        // Set module status 
        $this->status('module_social_links_active', 'N');
    }

// -----------------------------------------------------------------------------




    /**
     *  Administration
     * 
     * @access public
     */
    function administration()
    {
        $settings = array();
        
        $settings['module_social_links_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_enable_module'],
            'description'   => $this->text['txt_enable_module_description'],
            'required'      => true
            );

        return $settings;
    }

// -----------------------------------------------------------------------------




    /**
     * Processing the content
     * 
     * @access public
     */
    function process($trigger, &$settings, &$data, &$additional)
    {
        $image_path = $settings['script_url'] . $settings['module_directory'] . get_class($this) . '/template/image/';
        
        if ($trigger == 'module_demo' and $data['module'] == get_class($this)) {        
            $out = $this->get_output_object();
            $out->set_template_dir($this->get_property('module_path') . 'template/');
            $out->assign($this->text);
            
            $page_url   = urlencode($settings['server_protocol'] . $settings['server_name'] . $settings['script_url']);            
            $page_title = urlencode($this->text['txt_module_name']);

            $out->assign('page_url',    $page_url);        
            $out->assign('page_title',  $page_title);
            $out->assign('image_path',  $image_path);
                    
            $content = $out->fetch('link.tpl.html');            
            
            $this->set_output($trigger, $content);
        }

        if ($trigger == 'frontend_content_footer') {
                    
            $out = $this->get_output_object();
            $out->set_template_dir($this->get_property('module_path') . 'template/');
            $out->assign($this->text);
            
            $page_url   = urlencode($settings['server_protocol'] . $settings['server_name'] . $_SERVER['REQUEST_URI']);
            $page_title = urlencode($data['data']['page_title']);

            $out->assign('page_url',    $page_url);        
            $out->assign('page_title',  $page_title);
            $out->assign('image_path',  $image_path);
                    
            $content = $out->fetch('link.tpl.html');            
            $this->set_output($trigger, $content);
        }
    }

// -----------------------------------------------------------------------------




} // End of class








?>
