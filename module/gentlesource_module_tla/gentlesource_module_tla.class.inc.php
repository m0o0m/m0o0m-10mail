<?php

/** 
 * GentleSource Guestbook Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 * 
 * Version 1.0
 */




/**
 * Module Text Link Ads
 */
class gentlesource_module_tla extends gentlesource_module_common
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
    function gentlesource_module_tla(&$settings)
    {
        $this->text = $this->load_language();
        
        // Configuration
        $this->add_property('name',         $this->text['txt_module_name']);
        $this->add_property('description',  $this->text['txt_module_description']);
        $this->add_property('trigger',  
                                array(  
                                        'module_demo',
                                        'module_call_tla',
                                        'frontend_page_footer',
                                        )
                                );
        
        // Settings to be allowed to read from and write to database
        $this->add_property('setting_names',  
                                array(  
                                        'module_tla_active',
                                        'module_tla_local_xml_file',
                                        'module_tla_local_xml_key',
                                        'module_tla_ads_per_row',
                                        'module_tla_ad_font_size',
                                        'module_tla_ad_border_color',
                                        'module_tla_ad_background_color',
                                        'module_tla_ad_link_color',
                                        )
                                );
        
        // Default values
        $this->add_property('module_tla_active',                'N');
        $this->add_property('module_tla_local_xml_file',        'local_tla.xml');
        $this->add_property('module_tla_ads_per_row',           1);
        $this->add_property('module_tla_ad_font_size',          12);
        $this->add_property('module_tla_ad_border_color',       '#CCCCCC');
        $this->add_property('module_tla_ad_background_color',   '#F4F9FF');
        $this->add_property('module_tla_ad_link_color',         '#74A0FF');
        
        // Get settings from database
        $this->get_settings();
        
        // Set module status 
        $this->status('module_tla_active', 'N');

        // Create tla local xml file if it does not exist
        if (!is_file($this->get_property('module_path') . $this->get_property('module_tla_local_xml_file'))) {
            file_put_contents($this->get_property('module_path') . $this->get_property('module_tla_local_xml_file'), '');            
        }
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
        
        $settings['module_tla_active'] = array(
            'type'          => 'bool',
            'label'         => $this->text['txt_enable_module'],
            'description'   => $this->text['txt_enable_module_description'],
            'required'      => true
            );
        
        $settings['module_tla_local_xml_key'] = array(
            'type'          => 'string',
            'label'         => $this->text['txt_tla_xml_key'],
            'description'   => $this->text['txt_tla_xml_key_description'],
            'required'      => false
            );
        
        $settings['module_tla_ads_per_row'] = array(
            'type'          => 'select',
            'label'         => $this->text['txt_ads_per_row'],
            'description'   => '',
            'required'      => true,
            'option'        => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8)
            );
        
        $settings['module_tla_ad_font_size'] = array(
            'type'          => 'select',
            'label'         => $this->text['txt_ad_font_size'],
            'description'   => '',
            'required'      => true,
            'option'        => array(10 => 10, 11 => 11, 12 => 12, 13 => 13, 14 => 14, 15 => 15, 16 => 16, 17 => 17, 18 => 18)
            );
        
        $settings['module_tla_ad_border_color'] = array(
            'type'          => 'color',
            'label'         => $this->text['txt_border_color'],
            'description'   => '',
            'required'      => false
            );
        
        $settings['module_tla_ad_background_color'] = array(
            'type'          => 'color',
            'label'         => $this->text['txt_background_color'],
            'description'   => '',
            'required'      => false
            );
        
        $settings['module_tla_ad_link_color'] = array(
            'type'          => 'color',
            'label'         => $this->text['txt_link_color'],
            'description'   => '',
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
        if ($this->get_property('module_tla_local_xml_key') == '') {
            return false;
        }
        
        if ($trigger == 'module_demo'
                and isset($data['module'])
                and trim($data['module']) == get_class($this)) {

            $tla_content = $this->tla_ads();
            $this->set_output($trigger, $tla_content);
        }
        
        if ($trigger == 'frontend_page_footer'
                or $trigger == 'module_call_tla') {

            $out = $this->get_output_object();
            $out->set_template_dir($this->get_property('module_path') . 'template/'); 
            $out->assign('text_link_ads', $this->tla_ads());
            $content = $out->fetch('tla.tpl.html');
            $this->set_output($trigger, $content);
        }
    }

// -----------------------------------------------------------------------------




    /**
     * TLA
     * 
     */
    function tla_ads() {
    
        // Number of seconds before connection to XML times out
        // (This can be left the way it is)
        $CONNECTION_TIMEOUT = 10;
    
        // Local file to store XML
        // This file MUST be writable by web server
        // You should create a blank file and CHMOD it to 666
        $LOCAL_XML_FILENAME = $this->get_property('module_path') . $this->get_property('module_tla_local_xml_file');
    
        if( !file_exists($LOCAL_XML_FILENAME) ) die("Text Link Ads script error: $LOCAL_XML_FILENAME does not exist. Please create a blank file named $LOCAL_XML_FILENAME.");
        if( !is_writable($LOCAL_XML_FILENAME) ) die("Text Link Ads script error: $LOCAL_XML_FILENAME is not writable. Please set write permissions on $LOCAL_XML_FILENAME.");
    
        if( filemtime($LOCAL_XML_FILENAME) < (time() - 3600) || filesize($LOCAL_XML_FILENAME) < 20) {
            $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : "";
            $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
            tla_updateLocalXML("http://www.text-link-ads.com/xml.php?inventory_key=" . $this->get_property('module_tla_local_xml_key') . "&referer=" . urlencode($request_uri) .  "&user_agent=" . urlencode($user_agent), $LOCAL_XML_FILENAME, $CONNECTION_TIMEOUT);
        }
    
        $xml = tla_getLocalXML($LOCAL_XML_FILENAME);
    
        $arr_xml = tla_decodeXML($xml);
        $width = ceil(100 / $this->get_property('module_tla_ads_per_row'));
        $content = '';        
        if ( is_array($arr_xml) ) {
            $content .= "\n<ul style=\"border: 1px solid " . $this->get_property('module_tla_ad_border_color') . "; border-spacing: 0px; background-color: " . $this->get_property('module_tla_ad_background_color') . "; width: 100%; padding: 0; overflow: hidden; list-style: none; margin: 0;\">\n";
            for ($i = 0; $i < count($arr_xml['URL']); $i++) {
                $content .= "<li style=\"margin: 0; width: " . $width . "%; float: left; clear: none; padding: 0; display: inline;\"><span style=\"display: block; color: #000000; font-size: " . $this->get_property('module_tla_ad_font_size') . "px; padding: 3px; margin: 0; width: 100%;\">".$arr_xml['BeforeText'][$i]." <a style=\"color: " . $this->get_property('module_tla_ad_link_color') . "; font-size: " . $this->get_property('module_tla_ad_font_size') . "px;\" href=\"".$arr_xml['URL'][$i]."\">".$arr_xml['Text'][$i]."</a> ".$arr_xml['AfterText'][$i]."</span></li>\n";
            }
            $content .= "</ul>";
        }
        return $content;    
    }

// -----------------------------------------------------------------------------
    



} // End of class




/**
 * TLA
 * 
 */
function tla_updateLocalXML($url, $file, $time_out)
{
    if($handle = fopen($file, "a")){
            fwrite($handle, "\n");
            fclose($handle);
    }
    if($xml = file_get_contents_tla($url, $time_out)) {
        $xml = substr($xml, strpos($xml,'<?'));

        if ($handle = fopen($file, "w")) {
            fwrite($handle, $xml);
            fclose($handle);
        }
    }
}

// -----------------------------------------------------------------------------




/**
 * TLA
 * 
 */
function tla_getLocalXML($file)
{
    $contents = "";
    if($handle = fopen($file, "r")){
        $contents = fread($handle, filesize($file)+1);
        fclose($handle);
    }
    return $contents;
}

// -----------------------------------------------------------------------------




/**
 * TLA
 * 
 */
function file_get_contents_tla($url, $time_out)
{
    $result = "";
    $url = parse_url($url);

    if ($handle = @fsockopen ($url["host"], 80)) {
        if(function_exists("socket_set_timeout")) {
            socket_set_timeout($handle,$time_out,0);
        } else if(function_exists("stream_set_timeout")) {
            stream_set_timeout($handle,$time_out,0);
        }

        fwrite ($handle, "GET $url[path]?$url[query] HTTP/1.0\r\nHost: $url[host]\r\nConnection: Close\r\n\r\n");
        while (!feof($handle)) {
            $result .= @fread($handle, 40960);
        }
        fclose($handle);
    }

    return $result;
}

// -----------------------------------------------------------------------------




/**
 * TLA
 * 
 */
function tla_decodeXML($xmlstg)
{

    if( !function_exists('html_entity_decode') ){
        function html_entity_decode($string)
        {
           // replace numeric entities
           $string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\1"))', $string);
           $string = preg_replace('~&#([0-9]+);~e', 'chr(\1)', $string);
           // replace literal entities
           $trans_tbl = get_html_translation_table(HTML_ENTITIES);
           $trans_tbl = array_flip($trans_tbl);
           return strtr($string, $trans_tbl);
        }
    }

    $out = "";
    $retarr = "";

    preg_match_all ("/<(.*?)>(.*?)</", $xmlstg, $out, PREG_SET_ORDER);
    $search_ar = array('&#60;', '&#62;', '&#34;');
    $replace_ar = array('<', '>', '"');
    $n = 0;
    while (isset($out[$n]))
    {
        $retarr[$out[$n][1]][] = str_replace($search_ar, $replace_ar,html_entity_decode(strip_tags($out[$n][0])));
        $n++;
    }
    return $retarr;
}

// -----------------------------------------------------------------------------








?>
