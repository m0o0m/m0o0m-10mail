<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 *
 * @todo Add @ to eval
 */

  /*****************************************************
  **
  ** THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY
  ** OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
  ** LIMITED   TO  THE WARRANTIES  OF  MERCHANTABILITY,
  ** FITNESS    FOR    A    PARTICULAR    PURPOSE   AND
  ** NONINFRINGEMENT.  IN NO EVENT SHALL THE AUTHORS OR
  ** COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES
  ** OR  OTHER  LIABILITY,  WHETHER  IN  AN  ACTION  OF
  ** CONTRACT,  TORT OR OTHERWISE, ARISING FROM, OUT OF
  ** OR  IN  CONNECTION WITH THE SOFTWARE OR THE USE OR
  ** OTHER DEALINGS IN THE SOFTWARE.
  **
  *****************************************************/


// Prevent hacking attempt
if (!defined('T12L_ROOT')) {
    die();
}


// Define path separator
if (!defined('PATH_SEPARATOR')) {
    if (substr(PHP_OS, 0, 3) == 'WIN') {
        define('PATH_SEPARATOR', ';');
    } else {
        define('PATH_SEPARATOR', ':');
    }
}


// Set include path
$t12l_include_path = 
                T12L_ROOT . 'configuration'. PATH_SEPARATOR .
                T12L_ROOT . 'include'. PATH_SEPARATOR .
                T12L_ROOT . 'include/library'. PATH_SEPARATOR .
                './' . PATH_SEPARATOR .
                ini_get('include_path') . PATH_SEPARATOR;
                
if (function_exists('set_include_path')) {
    set_include_path($t12l_include_path); 
} else {
    ini_set('include_path', $t12l_include_path);
}



// Include
require 'functions.inc.php';




// Clean input
$t12l = array();
t12l_unset_globals();
array_walk($_GET,       't12l_clean_input');
array_walk($_POST,      't12l_clean_input');
array_walk($_COOKIE,    't12l_clean_input');

$t12l['_post']   = $_POST;
$t12l['_get']    = $_GET;
$t12l['_cookie'] = $_COOKIE;




// Settings
$t12l['version']                 = '';
$t12l['login_status']            = false;
$t12l['alternative_template']    = defined('T12L_ALTERNATIVE_TEMPLATE') ? T12L_ALTERNATIVE_TEMPLATE : '';
$t12l['message']                 = array();
$t12l['module_additional']       = array();
$t12l['output']                  = array();




// Include
require 'system_debug.class.inc.php';
require 'query.class.inc.php';
require 'database.class.inc.php';
require 'setting.class.inc.php';
require 'time.class.inc.php';
require 'output.class.inc.php';
require 'default.inc.php';
require 'language.class.inc.php';


if ($t12l['debug_mode'] == 'Y') {
    ini_set('error_reporting', E_ALL);
} else {
    ini_set('error_reporting', E_ALL & ~E_NOTICE);
}




// Set path
$t12l['template_path']   = T12L_ROOT . $t12l['template_directory'];
$t12l['cache_path']      = T12L_ROOT . $t12l['cache_directory'];




/**
 * Database field - form field mapping
 * Key:   database field name 
 * Value: form field name
 */                              
 
// Table fields to be inserted or updated in database                                
$t12l['db_fields']['address'] = array(
                                'address_id',
                                'address_email',
                                'address_timestamp',
                                'address_ip',
                                'address_hostname',
                                'address_user_agent',
                                );
                                
$t12l['db_fields']['mail'] = array(
                                'mail_id',
                                'mail_address_id',
                                'mail_from',
                                'mail_subject',
                                'mail_excerpt',
                                'mail_body',
                                'mail_character_set',
                                'mail_timestamp',
                                );
                                
$t12l['db_fields']['setting'] = array(
                                'setting_name',
                                'setting_value'
                                );




// Setting names to be written and read
$t12l['setting_names'] = array(
                            'database_version',
                            'default_language',
                            'script_url',
                            'frontend_result_number',
                            'frontend_order',
                            'block_content',
                            'block_ip',
                            'word_filter',
                            'enable_moderation',
                            'publish_delay',
                            'email_notification',
                            'notification_email',
                            'display_turn_off_messages',
                            'display_comments',
                            'display_comment_form',
                            'page_registration',
                            );

// -----------------------------------------------------------------------------




// Manage installation
include 'installation.class.inc.php';
$t12l_installation = new t12l_installation;
if ($t12l_installation->status() != true) {
    $t12l_installation->start();
}

// -----------------------------------------------------------------------------




// Database tables
require T12L_ROOT . 'dbconfig.php';
define('T12L_ADDRESS_TABLE',    $t12l['database_table_prefix'] . 'address');
define('T12L_MAIL_TABLE',       $t12l['database_table_prefix'] . 'mail');
define('T12L_SETTING_TABLE',    $t12l['database_table_prefix'] . 'setting');

$t12l['tables']['address']      = T12L_ADDRESS_TABLE;
$t12l['tables']['mail']         = T12L_MAIL_TABLE;
$t12l['tables']['setting']      = T12L_SETTING_TABLE;

// -----------------------------------------------------------------------------




// Language
$t12l['text'] = t12l_language::load($t12l['default_language']);

// -----------------------------------------------------------------------------




// Get setting data
$t12l_settings = t12l_setting::read_all();
$t12l = array_merge($t12l, $t12l_settings);










?>
