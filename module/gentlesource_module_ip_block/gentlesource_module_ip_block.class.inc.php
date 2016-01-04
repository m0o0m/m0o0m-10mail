<?php

/** 
 * GentleSource Guestbook Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




/**
 * Manage modules
 */
class gentlesource_module_ip_block extends gentlesource_module_common
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
    function gentlesource_module_ip_block()
    {
        $this->text = $this->load_language();
        
        // Configuration
        $this->add_property('name',         $this->text['txt_module_name']);
        $this->add_property('description',  $this->text['txt_module_description']);
        $this->add_property('trigger',  
                                array(  
                                        'frontend_save_content',
                                        'backend_comment_control',
                                        'frontend_use_functions',
                                        )
                                );
        
        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',  
                                array(  
                                        'module_ip_block_mode',
                                        'module_ip_block_list',
                                        )
                                );
        
        // Default values
        $this->add_property('module_ip_block_mode',  'off'); // off, reject, moderate
        
        // Get settings from database
        $this->get_settings();
        
        // Set module status 
        $this->status('module_ip_block_mode', 'off');
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
        
        $settings['module_ip_block_mode'] = array(
            'type'          => 'radio',
            'label'         => $this->text['txt_ip_block'],
            'description'   => $this->text['txt_ip_block_description'],
            'required'      => true,
            'option'        => array(
                                'off'       => $this->text['txt_off'],
                                'moderate'  => $this->text['txt_moderate'],
                                'reject'    => $this->text['txt_reject']
                                ),
            );
        
        $settings['module_ip_block_list'] = array(
            'type'          => 'textarea',
            'label'         => $this->text['txt_ip_addresses'],
            'description'   => $this->text['txt_ip_addresses_description'],            
            'required'      => false,
            'attribute'     => array('rows' => 10, 'cols' => 30),
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
        if ($trigger == 'frontend_use_functions') {
            if ($this->get_property('module_ip_block_mode') == 'reject'
                    and $this->block($data)) {
                $settings['message']['module_spam_check'] = $this->text['txt_error_ip_block'];
                $additional['allow_use_functions'] = 'N';
                return false;
            }
        }
        
        if ($trigger == 'frontend_save_content') {
            
            // Skip check if comment has already been blocked
            if ($additional['page_allow_comment'] == 'N') {
                return false;
            }
            
            if ($this->get_property('module_ip_block_mode') == 'reject'
                    and $this->block($data)) {
                $settings['message']['module_spam_check'] = $this->text['txt_error_ip_block'];
                $additional['page_allow_comment'] = 'N';
                return false;
            }
            
            
            // Skip check if moderation has already been turned on
            if ($settings['enable_moderation'] == 'Y'
                    or isset($data['comment_status'])) {
                return false;
            }
            
            if ($this->get_property('module_ip_block_mode') == 'moderate'
                    and $this->block($data)) {
                $settings['message']['module_spam_check'] = $this->text['txt_notice_moderation'];
                $data['comment_status'] = 100;
                return false;
            }
        }

        if ($trigger == 'backend_comment_control') {
            // Add ip to list
            if (isset($settings['_get']['do'])
                    and $settings['_get']['do'] == banip
                    and isset($settings['_get']['ip'])
                    and $settings['_get']['ip'] != '') {
                $this->set_setting( 'module_ip_block_list', 
                                    $this->get_property('module_ip_block_list') . 
                                        "\n" . 
                                        trim($settings['_get']['ip']));
            }
            
            // Display link
            $out = $this->get_output_object();
            $out->set_template_dir($this->get_property('module_path') . 'template/');        
            $out->assign($this->text);
            $out->assign($data);
            $this->set_output($trigger, $out->fetch('banip.tpl.html'));
            return $out->fetch('banip.tpl.html');
        }
    }

// -----------------------------------------------------------------------------





    /**
     * Block content
     * 
     * @access public
     */
    function block($data)
    {
        $list = $this->get_property('module_ip_block_list');
        if ($list == '') {
            return false;
        }

        $input = @$_SERVER['REMOTE_ADDR'];

        if (isset($data['comment_author_ip']) and $data['comment_author_ip'] == '') {
            $input = $data['comment_author_ip'];
        }
        $arr = explode("\n", $list);
        while (list($key, $var) = each($arr))
        {
            $word = trim($var);
            if ($word == '') {
                continue;
            }
            
            // Check for wildcards
            $preg_word = '#^' . str_replace('%asterisk%', '(.*?)', preg_quote(str_replace('*', '%asterisk%', $word))) . '$#i';
            

            if ($word{0} != '*' and $word{0} != strtolower($input{0})) {
                continue;
            }
            
            if (preg_match($preg_word, $input)) {
                return true;
            }
        }
    }

// -----------------------------------------------------------------------------




} // End of class








?>
