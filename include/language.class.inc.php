<?php

/** 
 * GentleSource - language.class.inc.php
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




/**
 * Class name and unique identifier for $GLOBALS array that contains the
 * instance
 */
define('LANGUAGE_CLASS', 't12l_language');
define('LANGUAGE_INSTANCE', 't12l_language_instance');


/**
 * Handle file names and pramameters
 *
 * @access public
 */
class t12l_language
{

    /**
     * @var array Query string values
     * @access private
     */
    var $language;

    //--------------------------------------------------------------------------




    /**
     * Constructor
     *
     * @access private
     */
    function language()
    {
    }

    //--------------------------------------------------------------------------




    /**
     * Create single instance
     *
     */
    function &get_instance()
    {
        if (!isset($GLOBALS[LANGUAGE_INSTANCE])) {
            $GLOBALS[LANGUAGE_INSTANCE] = new t12l_language;
        }

        return $GLOBALS[LANGUAGE_INSTANCE];
    }

    //--------------------------------------------------------------------------




    /**
     *
     */
    function get($default)
    {
        global $t12l;
        $ref =& t12l_language::get_instance();
        $list = array();
        $redirect = false;

        // From post
        if (isset($t12l['_post']['t12l_language_selector']) and
            $t12l['_post']['t12l_language_selector'] != '') {
            $list[] = $t12l['_post']['t12l_language_selector'];
            $redirect = true;
        }

        // From get
        if (isset($t12l['_get']['t12l_language_selector']) and
            $t12l['_get']['t12l_language_selector'] != '') {
            $list[] = $t12l['_get']['t12l_language_selector'];
            $redirect = true;
        }

        // From cookie
        if (isset($t12l['_cookie'][$t12l['language_cookie_name']]) and
            $t12l['_cookie'][$t12l['language_cookie_name']] != '') {
            $list[] = $t12l['_cookie'][$t12l['language_cookie_name']];
        }
        
        // From domain
        $tld = substr($_SERVER['SERVER_NAME'], strrpos($_SERVER['SERVER_NAME'], '.') + 1);
        if (array_key_exists($tld, $t12l['domain_language'])) {
            $list[] = $t12l['domain_language'][$tld];
        }
        

        // From browser environment
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $accept = split(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($accept AS $key => $val)
            {
                if ($pos = strpos($val, ';') and $pos !== 0) {
                    $val = substr($val, 0, $pos);
                }
                $list[] = trim($val);
            }
        }


        $new_list = array();
        $language_folder = $t12l['language_directory'];
        
        // Use utf-8 folder if it exists
        if ($t12l['use_utf8'] == 'Y') {
            $language_folder = $t12l['language_directory_utf8'];
        }
        foreach ($list AS $key => $val)
        {
            if (!array_key_exists($val, $t12l['available_languages'])) {
                continue;
            }
            if (!is_file(T12L_ROOT . $language_folder . 'language.' . $val . '.php')) {
                // Go back to default language folder if language does not exists in utf-8 folder
                if (!is_file(T12L_ROOT . $t12l['language_directory'] . 'language.' . $val . '.php')) {
                    continue;
                }
            }
            $new_list[] = $val;
        }
        if (sizeof($new_list) > 0) {
            $new_language = $new_list[0];
        } else {
//            $language_setting = t12l_setting::read('default_language');
            $new_language = $default;
        }
                
        if (!isset($t12l['_cookie'][$t12l['language_cookie_name']]) 
                or $t12l['_cookie'][$t12l['language_cookie_name']] != $new_language) {
                    
            $ref->set($new_language);
            $ref->language = $new_language;
        }
        
        if (true == $redirect) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . urldecode(trim(t12l_gpc_vars('r'))));
        }

        return $new_language;
    }

    //--------------------------------------------------------------------------




    /**
     *
     */
    function set($language)
    {
        global $t12l;
        $ref =& t12l_language::get_instance();

        // Write cookie
        setcookie(  $t12l['language_cookie_name'],
                    $language,
                    time()+(3600*24*360*10),
                    $t12l['cookie_path'],
                    $t12l['cookie_domain']);
                    
                    
        
//        echo   $t12l['language_cookie_name'] . ' ' .
//                    $language . ' ' .   
//                    (time()+(3600*24*360*10)) . ' ' . 
//                    $t12l['cookie_path'] . ' ' . 
//                    $t12l['cookie_domain'];
    }

    //--------------------------------------------------------------------------




    /**
     * Load the content of a specified language file
     * 
     * @access public
     * @param string $language
     * @param string $item Part of the language file
     */
    function load($language)
    {
        global $t12l;
        $res = array();
        
        $language_folder = $t12l['language_directory'];
        
        
        // Use utf-8 folder if it exists
        if ($t12l['use_utf8'] == 'Y') {
            $language_folder = $t12l['language_directory_utf8'];
        }
        
        // Go back to default language folder if language does not exists in utf-8 folder
        if (!is_file(T12L_ROOT . $language_folder . 'language.' . $language . '.php')
                and is_file(T12L_ROOT . $t12l['language_directory'] . 'language.' . $language . '.php')) {
            $language_folder = $t12l['language_directory'];
        }
        
        $path = T12L_ROOT . $language_folder . 'language.' . $language . '.php';
        
        include $path;
        if (is_file($path)) {
            include $path;
            $res = $text;
        }
        
        return $res;
    }

    //--------------------------------------------------------------------------





} // End of class
?>