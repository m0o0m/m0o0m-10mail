<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */ 




/**
 * Check and get GPC vars
 */
function t12l_gpc_vars($variable, $default = '')
{   
    global $t12l;
    
    if (isset($t12l['_get'][$variable])) {
        return $t12l['_get'][$variable];
    }  
    if (isset($t12l['_post'][$variable])) {
        return $t12l['_post'][$variable];
    }  
    if (isset($t12l['_cookie'][$variable])) {
        return $t12l['_cookie'][$variable];
    }  
    if ($default != '') {
        return $default;
    }
}

// -----------------------------------------------------------------------------
                
                
                
                
/**
 * Format numbers to given format
 *
 * @param float $number 
 * @return string
 */
function t12l_format_number($number)
{   
    global $conf;
    
    $number = number_format($number, $conf['decimal_places'], $conf['decimals_delimiter'], $conf['thousands_delimiter']);
    return $number;
}

// -----------------------------------------------------------------------------
      
            
           
            
/**
 * Convert given number into float
 *
 * @param string $number 
 * @return float
 */
function t12l_clean_number($number)
{   
    global $conf;
    $pieces    = explode($conf['decimals_delimiter'], $number);
    $pieces[0] = preg_replace('#[^0-9]#', '', $pieces[0]);
    return (float) join('.', $pieces);
}

// -----------------------------------------------------------------------------
          

     
      
/**
 * Provide the content of a specified language file
 *
 */     
function t12l_load_language($language)
{
    global $conf;
    $res = array();
    $path = $conf['language_directory'] . 'language.' . $language . 'inc.php';
    if (is_file($path)) {
        include $path;
        $res = $txt;
    }
    return $res;
} 
    
//------------------------------------------------------------------------------




/**
 * 
 */
function t12l_print_a($ar, $htmlize = 0)
{
    if ($htmlize == 1) {
        if (is_array($ar)) {
            array_walk($ar, create_function('&$ar', 'if (is_string($ar)) {$ar = htmlspecialchars($ar);}'));
        } else {
            $ar = htmlspecialchars($ar);
        }
    }
  
    echo '<pre>';
    print_r($ar);
    echo '</pre>';
}

//------------------------------------------------------------------------------




/**
 * 
 */
function t12l_array_append()
{
    $args = func_get_args();
    $arr  = array();
      
    for ($i = 0; $i < count($args); $i++)
    {
        if (empty($args[$i])) {
            continue;
        }
      
        if (!is_array($args[$i])) {
            trigger_error('Supplied argument is not an array', E_USER_NOTICE);
        }
      
        while (list($key, $val) = each($args[$i]))
        {
            $arr[$key] = $val;
        }
    }
    return $arr;
}

//------------------------------------------------------------------------------
                                




// Utf8 encode
function t12l_utf8_encode($value)
{
    global $t12l;
    
    if (function_exists('iconv')) {
        $encoded = iconv($t12l['text']['txt_charset'], 'UTF-8', $value);
    } else {
        $encoded = false;
    }
    
    if ($encoded == false) {
        $encoded = utf8_encode($value);
    }
    return $encoded;    
}

//------------------------------------------------------------------------------




// HTML entities for input
function t12l_entity_input(&$value)
{
    if (is_array($value)) {
        array_walk($value, 't12l_entity_input');
        return;
    }
//    $value = htmlentities($value);
    $value = strip_tags($value);
}

//------------------------------------------------------------------------------




// Clean input
function t12l_clean_input(&$value)
{
    if (is_array($value)) {
        array_walk($value, 't12l_clean_input');
        return;
    }

    if (ini_get('magic_quotes_gpc')) {
        $value = stripslashes($value);
    }
    $value = addslashes($value);
}

//------------------------------------------------------------------------------




// Clean output
function t12l_clean_output(&$value)
{
    if (is_array($value)) {
        array_walk($value, 't12l_clean_output');
        return;
    }

    $value = stripslashes($value);
}

//------------------------------------------------------------------------------




// Output content and terminate the script
function t12l_exit($content = '')
{
    $html = '<html>
               <head>
                 <title></title>
               </head>
               <body>' . $content . '</body>
             </html>';
             
     exit($html);     
}

//------------------------------------------------------------------------------




// Unset all global variables
function t12l_unset_globals()
{
    if (ini_get('register_globals')) {
        foreach ($_REQUEST as $k => $v) {
            unset($GLOBALS[$k]);
        }
    }
}

//------------------------------------------------------------------------------



          
/**
 * Create random string
 *
 */     
function t12l_create_random($length, $pool = '')
{
    $random = '';
  
    if (empty($pool)) {
        $pool    = 'abcdefghkmnpqrstuvwxyz';
        $pool   .= '23456789';
    }
  
    srand ((double)microtime()*1000000);
                
    for($i = 0; $i < $length; $i++) 
    {
        $random .= substr($pool,(rand()%(strlen ($pool))), 1);
    }
  
    return $random;
}

//------------------------------------------------------------------------------



if (!function_exists('file_put_contents')) {
    function file_put_contents($filename, $content, $flags = 0) {
        if (!($file = fopen($filename, ($flags & 1) ? 'a' : 'w'))) {
            return false;
        }
        $n = fwrite($file, $content);
        fclose($file);
        return $n ? $n : false;
    }
}



if (!function_exists('file_get_contents')) {
    function file_get_contents($filename, $flags = 0) {
        $content = join('', file($filename));
        return $content;
    }
}






?>
