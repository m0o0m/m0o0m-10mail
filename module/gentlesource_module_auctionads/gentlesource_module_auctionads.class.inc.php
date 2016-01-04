<?php

/** 
 * GentleSource Module
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 * 
 * Version 1.0
 */




/**
 * Module Text Link Ads
 */
class gentlesource_module_auctionads extends gentlesource_module_common
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
    function gentlesource_module_auctionads(&$settings)
    {
        $this->text = $this->load_language();
        
        // Configuration
        $this->add_property('name',         $this->text['txt_module_name']);
        $this->add_property('description',  $this->text['txt_module_description']);
        $this->add_property('trigger',  
                                array(  
                                        'module_demo',
                                        'module_call_auctionads',
                                        )
                                );
        
        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',  
                                array(  
                                        'module_auctionads_active',
                                        'module_auctionads_ad_client',
                                        'module_auctionads_ad_campaign',
                                        'module_auctionads_ad_format',
                                        'module_auctionads_ad_kw',
                                        'module_auctionads_color_border',
                                        'module_auctionads_color_bg',
                                        'module_auctionads_color_heading',
                                        'module_auctionads_color_text',
                                        'module_auctionads_color_link',
                                        'module_auctionads_new_window',
                                        'module_auctionads_style',
                                        )
                                );
        
        // Default values
        $this->add_property('module_auctionads_active',         'N');
        $this->add_property('module_auctionads_ad_client',      '');
        $this->add_property('module_auctionads_ad_campaign',    '');
        $this->add_property('module_auctionads_ad_format',      '');
        $this->add_property('module_auctionads_ad_kw',          '');
        $this->add_property('module_auctionads_color_border',   '#75E505');
        $this->add_property('module_auctionads_color_bg',       '#FFFFFF');
        $this->add_property('module_auctionads_color_heading',  '#00A0E2');
        $this->add_property('module_auctionads_color_text',     '#000000');
        $this->add_property('module_auctionads_color_link',     '#FFFFFF');
        $this->add_property('module_auctionads_new_window',     'N');
        $this->add_property('module_auctionads_style',          '');
        
        // Get settings from database
        $this->get_settings();
        
        // Set module status 
        $this->status('module_auctionads_active', 'N');
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
        
        $settings['module_auctionads_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_enable_module'],
            'description'   => $this->text['txt_enable_module_description'],
            'required'      => true
            );
        
        $settings['module_auctionads_ad_client'] = array(
            'type'          => 'string',
            'label'         => $this->text['txt_ad_client'],
            'description'   => $this->text['txt_ad_client_description'],
            'required'      => false
            );
        
        $settings['module_auctionads_ad_campaign'] = array(
            'type'          => 'string',
            'label'         => $this->text['txt_ad_campaign'],
            'description'   => $this->text['txt_ad_campaign_description'],
            'required'      => false
            );
        
        $settings['module_auctionads_ad_kw'] = array(
            'type'          => 'textarea',
            'label'         => $this->text['txt_ad_keywords'],
            'description'   => $this->text['txt_ad_keywords_description'],
            'attribute'     => array('style' => 'height:200px;width:300px;'),
            'required'      => false
            );
        
        $settings['module_auctionads_ad_format'] = array(
            'type'          => 'select',
            'label'         => $this->text['txt_ad_format'],
            'description'   => '',
            'required'      => true,
            'option'        => array(
                                '728x90'    => '728 x 90 ' .    $this->text['txt_ad_format_leaderboard'],
                                '468x60'    => '468 x 60 ' .    $this->text['txt_ad_format_banner'],
                                '336x280'   => '336 x 280 ' .   $this->text['txt_ad_format_large_rectangle'],
                                '300x250'   => '300 x 250 ' .   $this->text['txt_ad_format_medium_rectangle'],
                                '250x250'   => '250 x 250 ' .   $this->text['txt_ad_format_square'],
                                '234x60'    => '234 x 60 ' .    $this->text['txt_ad_format_half_banner'],
                                '180x150'   => '180 x 150 ' .   $this->text['txt_ad_format_small_rectangle'],
                                '160x600'   => '160 x 600 ' .   $this->text['txt_ad_format_wide_skyscraper'],
                                '125x125'   => '125 x 125 ' .   $this->text['txt_ad_format_button'],
                                '120x600'   => '120 x 600 ' .   $this->text['txt_ad_format_skyscraper'],
                                '120x240'   => '120 x 240 ' .   $this->text['txt_ad_format_vertical_banner'],
                                )
            );
        
        $settings['module_auctionads_color_border'] = array(
            'type'          => 'color',
            'label'         => $this->text['txt_border_color'],
            'description'   => '',
            'required'      => false
            );
        
        $settings['module_auctionads_color_bg'] = array(
            'type'          => 'color',
            'label'         => $this->text['txt_background_color'],
            'description'   => '',
            'required'      => false
            );
        
        $settings['module_auctionads_color_heading'] = array(
            'type'          => 'color',
            'label'         => $this->text['txt_heading_color'],
            'description'   => '',
            'required'      => false
            );
        
        $settings['module_auctionads_color_text'] = array(
            'type'          => 'color',
            'label'         => $this->text['txt_text_color'],
            'description'   => '',
            'required'      => false
            );
        
        $settings['module_auctionads_color_link'] = array(
            'type'          => 'color',
            'label'         => $this->text['txt_link_color'],
            'description'   => '',
            'required'      => false
            );
        
        $settings['module_auctionads_new_window'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_new_window'],
            'description'   => '',
            'required'      => false
            );
        
        $settings['module_auctionads_style'] = array(
            'type'          => 'string',
            'label'         => $this->text['txt_style'],
            'description'   => $this->text['txt_style_description'],
            'required'      => false
            );

        return $settings;
    }

// -----------------------------------------------------------------------------




    /**
     * Processing the content
     * 
     */
    function process($trigger, &$settings, &$data, &$additional)
    {
        if ($this->get_property('module_auctionads_ad_client') == '') {
            return false;
        }
        
        if ($trigger == 'module_demo'
                and isset($data['module'])
                and trim($data['module']) == get_class($this)) {

            $this->set_output($trigger, $this->auction_ads('auctionads_demo.tpl.html'));
        }
        
        if ($trigger == 'module_call_auctionads') {
            $this->set_output($trigger, $this->auction_ads('auctionads.tpl.html'));
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Processing the content
     * 
     */
    function auction_ads($template)
    {
        $arr = array(
                'ad_client'         => trim($this->get_property('module_auctionads_ad_client')),
                'ad_campaign'       => trim($this->get_property('module_auctionads_ad_campaign')),
                'ad_width'          => substr($this->get_property('module_auctionads_ad_format'), 0, strpos($this->get_property('module_auctionads_ad_format'), 'x')),
                'ad_height'         => substr($this->get_property('module_auctionads_ad_format'), strpos($this->get_property('module_auctionads_ad_format'), 'x')+1),
                'ad_keywords'       => trim(str_replace("\r", '', str_replace("\n", '', $this->get_property('module_auctionads_ad_kw')))),
                'color_border'      => trim(str_replace('#', '', $this->get_property('module_auctionads_color_border'))),
                'color_bg'          => trim(str_replace('#', '', $this->get_property('module_auctionads_color_bg'))),
                'color_heading'     => trim(str_replace('#', '', $this->get_property('module_auctionads_color_heading'))),
                'color_text'        => trim(str_replace('#', '', $this->get_property('module_auctionads_color_text'))),
                'color_link'        => trim(str_replace('#', '', $this->get_property('module_auctionads_color_link'))),
                'options'           => ($this->get_property('module_auctionads_new_window') == 'N') ? '' : 'n',
                'style'             => $this->get_property('module_auctionads_style'),
                );
                
        $out = $this->get_output_object();
        $out->set_template_dir($this->get_property('module_path') . 'template/'); 
        $out->assign($arr);
        $content = $out->fetch($template);
        
        return $content;
    }

// -----------------------------------------------------------------------------

} // End of class






?>
