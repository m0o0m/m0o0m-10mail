<?php

/** 
 * GentleSource News Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


require 'modulecommon.class.inc.php';




/**
 * Manage modules
 * 
 * Triggers:
 * 
 * frontend_page_header 
 * frontend_page_footer
 * 
 * backend_source_head 		(include i.e. javascript into the HTML head)
 * 
 * frontend_content			(content row after reading from database)
 * backend_content 			(content row after reading from database)
 * 
 * frontend_textarea
 * backend_textarea
 * 
 * frontend_save_comment 
 * frontend_comment_form 
 * backend_comment_control	(button/link list in comment list)
 * 
 * backend_navigation
 * 
 * backend_save_content
 * backend_update_content
 * 
 * date_picker				
 * 
 * module_demo
 * 
 * standalone
 * 
 */
class t12l_module
{




    /**
     * Call module on trigger
     * 
     * Use $t12l['module_additional'] if no genuine $additional is at hand.
     * 
     * @access public
     */     
    function call_module($trigger, &$data, &$additional)
    {
        global $t12l;
        
        $module_container = t12l_module::container();
        reset($module_container);
        while (list($module, $instance) = each($module_container))
        {
            if (in_array($trigger, $instance->get_property('trigger'))
                    and $instance->get_property('module_active') == true) {
                $instance->process($trigger, $t12l, $data, $additional);
            }
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Call module on trigger with output
     * 
     * @access public
     */
    function call_module_output($parameter, &$smarty)
    {
        global $t12l;
        
        if (!isset($parameter['trigger'])) {
            return false;
        } else {
            $trigger = $parameter['trigger'];
        }
        
        $module_container = t12l_module::container();
        $output = array();
        reset($module_container);
        while (list($module, $instance) = each($module_container))
        {
            if (in_array($trigger, $instance->get_property('trigger'))
                    and $instance->get_property('module_active') == true) {
                $instance->process($trigger, $t12l, $parameter, $t12l['module_additional']);
                $output[] = $instance->get_output($trigger);
            }
        }
        return join('', $output);
    }

// -----------------------------------------------------------------------------




    /**
     * Call module on trigger with tab output
     * 
     * @access public
     */
    function call_module_tab_output($parameter, &$smarty)
    {
        global $t12l;
        
        if (!isset($parameter['trigger'])) {
            return false;
        } else {
            $trigger = $parameter['trigger'];
        }
        
        $module_container = t12l_module::container();
        $output = array();
        reset($module_container);
        while (list($module, $instance) = each($module_container))
        {
            if (in_array($trigger, $instance->get_property('trigger'))
                    and $instance->get_property('module_active') == true) {
                $instance->process($trigger, $t12l, $parameter, $t12l['module_additional']);                
                if ($instance->get_output($trigger) == '') {
                    continue;
                }                
                $output[] = '<div class="tabbertab" style="min-height:40px;"><h2>' . $instance->get_property('name') . '</h2>' . $instance->get_output($trigger) . '</div>';
            }
        }
        
        if (sizeof($output) > 0) {
            $result  = '<div class="tabber">';
            $result .= join('', $output);
            $result .= '</div>';
            
            return $result;
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Create module instance
     * 
     * @access public
     */
    function load_module($module, $all = false)
    {
        global $t12l;
        
        if (false == $all and !in_array($module, $t12l['installed_modules'])) {
            t12l_system_debug::add_message('Module Failure', 'Module is not listed in $t12l[\'installed_modules\']');
            return false;
        }
        
        $module_file =  T12L_ROOT . 
                        $t12l['module_directory'] . 
                        $module . '/' .
                        $module . '.class.inc.php';
        if (!is_file($module_file)) {
            t12l_system_debug::add_message('Module Failure', 'Module file not found in: ' . $module_file);
            return false;
        }

        if (!in_array($module_file, get_included_files())) {
            require $module_file;
        }
        
        if (!class_exists($module)) {
            t12l_system_debug::add_message('Module Failure', 'Module class "' . $module . '" does not exist.');
            return false;
        }

        $instance = new $module($t12l);
        return $instance;
    }

// -----------------------------------------------------------------------------




    /**
     * Re-instanciate module
     */
    function reload_module($module)
    {
        global $t12l;
        
        if (!in_array($module, $t12l['installed_modules'])) {
            t12l_system_debug::add_message('Module Failure', 'Module is not listed in $t12l[\'installed_modules\']');
            return false;
        }

        $instance = new $module($t12l);
        return $instance;
    }

// -----------------------------------------------------------------------------




    /**
     * Contains/creates module instances
     * 
     */
    function &container($reload = false, $cache = true)
    {
        global $t12l;
        static $module_data = null;

        if ($reload == false and $cache == true and is_array($module_data)) {
            return $module_data;
        }

        $module_data = array();
        foreach ($t12l['installed_modules'] AS $module)
        {
            trim($module);
            
            if (isset($module_data[$module])) {
                continue;
            }
            
            if ($reload == true and $instance = t12l_module::reload_module($module)) {
                $module_data[$module] = $instance;
                continue;
            }
            
            if ($instance = t12l_module::load_module($module)) {
                $module_data[$module] = $instance;
            }
        }
        return $module_data;
    }

// -----------------------------------------------------------------------------




    /**
     * Administration module list
     * 
     * @access public
     */
    function module_list()
    {
        $module_container = t12l_module::container();
        reset($module_container);
        $module_list = array();
        while (list($module, $instance) = each($module_container))
        {
            // Skip hidden modules or modules without administration
            if($instance->get_property('hidden') == true) {
                continue;
            }
            $module_list[] = array( 'name'          => $instance->get_property('name'),
                                    'description'   => $instance->get_property('description'),
                                    'module'    => $module
                                    );
        }
        return $module_list;
    }

// -----------------------------------------------------------------------------




    /**
     * List of all available modules
     * 
     * @access public
     */
    function available_module_list()
    {
        global $t12l;
        
        $module_container = t12l_module::container();
        reset($module_container);

        $available_modules = t12l_module::folder_list(T12L_ROOT . $t12l['module_directory']);
        asort($available_modules);
        $available_modules = array_merge($t12l['installed_modules'], $available_modules);
        $available_modules = array_unique($available_modules);
        $module_list = array();
        while (list($key, $module) = each($available_modules))
        {
            $module = trim($module);
            
            if (isset($module_container[$module])) {
                $instance = $module_container[$module];
                $install_status = true;
            } else {         
                if (!$instance = t12l_module::load_module($module, true)) {
                    continue;
                } else {
                    $install_status = false;
                }
            }
            $module_list[] = array( 'name'          => $instance->get_property('name'),
                                    'description'   => $instance->get_property('description'),
                                    'module'        => $module,
                                    'installed'     => $install_status
                                    );
        }
        return $module_list;
    }

// -----------------------------------------------------------------------------




    /**
     * Administration
     * 
     * @access public
     */
    function administration($module)
    {
        $module_container = t12l_module::container();
        $admin = $module_container[trim($module)];
        
        $result = array(
                    'module_name'           => $module,
                    'module_title'          => $admin->get_property('name'),
                    'module_description'    => $admin->get_property('description'),
                    'module_form'           => t12l_module::administration_form($admin)
                    );
        return $result;
    }

// -----------------------------------------------------------------------------




    /**
     * Administration form
     * 
     * $property elements:
     * - type
     * - label
     * - description
     * - required
     * - option (radio|select)
     * - attribute
     * 
     * @access public
     */
    function administration_form(&$instance)
    {
        global $t12l;
        
        require_once 'HTML/QuickForm.php';
        
        $form       = new HTML_QuickForm('module_admin', 'POST');
        $settings   = $instance->administration($t12l);
        $additional = array();
        foreach ($settings AS $name => $property)
        {
            $skip = false;
            $add_html = '';
            switch ($property['type']) {
				case 'string':
					$form->addElement('text', $name, $property['label']);
					break;
                                        
				case 'email':
                    $form->addElement('text', $name, $property['label']);
                    $form->addRule($name, $t12l['text']['txt_syntax_email'], 'email');					
					break;
                                        
				case 'numeric':
                    $form->addElement('text', $name, $property['label']);
                    $form->addRule($name, $t12l['text']['txt_syntax_numeric'], 'numeric');					
					break;
                                        
				case 'bool':
                    $bool = array();
                    $bool[] = &HTML_QuickForm::createElement('radio', null, null, $t12l['text']['txt_yes'], 'Y');
                    $bool[] = &HTML_QuickForm::createElement('radio', null, null, $t12l['text']['txt_no'], 'N');
                    $form->addGroup($bool, $name, $property['label'], ' &nbsp; ');					
					break;
                                        
				case 'select':
                    $bool = array();
                    $select =& $form->addElement('select', $name, $property['label'], $property['option']);
                    if (!isset($property['size']) or $property['size'] == '' or !is_numeric($property['size'])) {
                        $select->setSize(1);
                    } else {
                        $select->setSize($property['size']);
                    }					
					break;
                                        
				case 'radio':
                    $radio = array();
                    foreach ($property['option'] AS $value => $label)
                    {
                        $radio[] = &HTML_QuickForm::createElement('radio', null, null, $label, $value);
                    }
                    $form->addGroup($radio, $name, $property['label'], '<br />');					
					break;
                                        
				case 'textarea':
                    $attribute = '';
                    if (isset($property['attribute'])) {
                        $attribute = $property['attribute'];
                    }
                    $form->addElement('textarea', $name, $property['label'], $attribute);

					break;
                    
                case 'color':
                    $color_attribute = array(   'onfocus'   => 'style.backgroundColor = \'\'; style.color = \'\';',
                                                'onblur'    => 'style.backgroundColor = value; style.color = value;');
                    $form->addElement('text', $name, $property['label'], $color_attribute);
                    $add_html = '<script language="javascript">var cp_' . $name . ' = new ColorPicker();cp_' . $name . '.offsetX = 30; document.forms[0].' . $name . '.style.backgroundColor = document.forms[0].' . $name . '.value; document.forms[0].' . $name . '.style.color = document.forms[0].' . $name . '.value;</script><a href="#" onclick="cp_' . $name . '.select(document.forms[0].' . $name . ',\'pick\'); return false;" name="pick" id="pick"><img src="../template/admin/image/icon/color_picker.png" border="0" align="absmiddle" /></a><script language="javascript">cp_' . $name . '.writeDiv()</script>';
                    break;
                                        
				default:
                    $skip = true;
					break;
			}
            if ($skip == false) {
                $additional[] = array(  'description'   => $property['description'],
                                        'add_html'      => $add_html
                                        );    
            }
            
            if ($property['required']) {
                $form->addRule($name, $t12l['text']['txt_error_required'], 'required');
            }
        }
        $form->addElement('submit', 'save', $t12l['text']['txt_save_settings']);
        $additional[] = array(  'description'   => '',
                                'add_html'      => '');
        $form->addElement('hidden', 'm', get_class($instance));
        $additional[] = array(  'description'   => '',
                                'add_html'      => '');
        
        // Validate form
        $message = array();
        if ($form->validate()) {

            // Write data as settings    
            if (false == $t12l['demo_mode']) {
                foreach ($t12l['_post'] AS $name => $value)
                {
                    if (!in_array($name, $instance->get_property('setting_names'))) {
                        continue;
                    }
                    t12l_setting::write($name, $value);
                    $instance->add_property($name, $value);                    
                }
                $message[] = $t12l['text']['txt_update_data_successful'];
                
                // Reload modules
                t12l_module::container(true);

            } else {
                $message[] = $t12l['text']['txt_disabled_in_demo_mode'];
            }
        }
        
        // Get setting data
        $settings = t12l_setting::read_all();
        $input_data = array_merge($instance->get_all_properties(), $settings);
        $form->setDefaults($input_data);
        
        $result = $form->toArray();
        $merged = array();
        foreach ($result['elements'] AS $key => $item)
        {
            if (!isset($item['elements'])) {
                $item['elements'] = false;
            }
            $merged[] = array_merge($item, $additional[$key]); 
        }
        $result['elements']             = $merged;
        $result['module_message']       = $message;
        $result['module_additional']    = array(); //$additional;
        return $result;
    }

// -----------------------------------------------------------------------------




    /**
     * Get module folder list
     */
    function folder_list($path)
    {
        if (!is_dir($path)) {
            return false;
        }
        include 'Find.php';
        $items = &File_Find::glob('#gentlesource_([a-zA-Z0-9]+)#', $path, 'perl');

        if (!is_array($items)) {
            return false;
        }
        return $items;
    }

// -----------------------------------------------------------------------------




    /**
     * Install module
     */
    function install($module)
    {
        global $t12l;
        
        $module = trim($module);
        
        $t12l['installed_modules'][] = $module;
        $installed_modules = serialize(array_unique($t12l['installed_modules']));        
        
        t12l_setting::write('installed_modules', $installed_modules);
        
        $t12l['installed_modules'] = array_unique($t12l['installed_modules']);
        
        return true;
    }

// -----------------------------------------------------------------------------




    /**
     * Uninstall module
     */
    function uninstall($module)
    {
        global $t12l;
        
        $module = trim($module);
        
        $arr = array_flip($t12l['installed_modules']);
        
        unset($arr[$module]);
        
        $arr = array_flip($arr);
        
        $installed_modules = serialize(array_unique($arr));        
        
        t12l_setting::write('installed_modules', $installed_modules);
        
//        $t12l['installed_modules'] = unserialize($t12l['installed_modules']);
        $t12l['installed_modules'] = array_unique($t12l['installed_modules']);

        return true;
    }

// -----------------------------------------------------------------------------




    /**
     * Module order
     */
    function order($module, $direction)
    {
        global $t12l;
        
        $installed_modules = $t12l['installed_modules'];
        
        if ($direction == 'up') {
            $installed_modules = array_reverse($installed_modules);
        }
        
        $arr = array();
        $insert = false;
        foreach ($installed_modules AS $item)
        {
            if ($module == $item) {
                $insert = true;
                continue;
            }
             
            $arr[] = $item;
            
            if ($insert == true) {
                $arr[] = $module;
                $insert = false;
            }
        }
        
        if ($insert == true) {
            $arr[] = $module;
            $insert = false;
        }
        
        $installed_modules = $arr;
        
        if ($direction == 'up') {
            $installed_modules = array_reverse($installed_modules);
        }
        
        $installed_modules = serialize(array_unique($installed_modules));
        
        t12l_setting::write('installed_modules', $installed_modules);
        
//        $t12l['installed_modules'] = unserialize($t12l['installed_modules']);
        $t12l['installed_modules'] = unserialize($installed_modules);
        
        return true;
    }

// -----------------------------------------------------------------------------




} // End of class








?>
