<?php

/** 
 * GentleSource Comment Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 *
 * @todo Add @ to eval
 * 
 * @todo Un-comment delete expired
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
$t12l['software']                    = 'GentleSource Temporary E-mail';
$t12l['version']                    = '1.4.0';
$t12l['login_status']               = false;
$t12l['alternative_template']       = defined('T12L_ALTERNATIVE_TEMPLATE') ? T12L_ALTERNATIVE_TEMPLATE : '';
$t12l['message']                    = array();
$t12l['module_additional']          = array();
$t12l['output']                     = array();

ini_set('session.use_trans_sid', 0);




// Include
require 'system_debug.class.inc.php';
require 'query.class.inc.php';
require 'database.class.inc.php';
require 'setting.class.inc.php';
require 'time.class.inc.php';
require 'module.class.inc.php';
require 'output.class.inc.php';
require 'default.inc.php';
require 'language.class.inc.php';



// Set path
$t12l['template_path']   = T12L_ROOT . $t12l['template_directory'];
$t12l['cache_path']      = T12L_ROOT . $t12l['cache_directory'];




/**
 * Database field - form field mapping
 * Key:   database field name 
 * Value: form field name
 */                                
$t12l['mapping']['setting'] = array(
                                'setting_name'              => 'setting_name',
                                'setting_value'             => 'setting_value'
                                );




// Table fields to be inserted or updated in database                                
$t12l['db_fields']['address'] = array(
                                'address_id',
                                'address_email',
                                'address_timestamp',
                                'address_ip',
                                'address_hostname',
                                'address_user_agent',
                                'address_session_id'
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




// Allowed form fields to be used for insert and update                                    
$t12l['form_fields']['setting'] = array(  
                                    'setting_name',
                                    'setting_value'
                                    );




// Setting names to be written and read
$t12l['setting_names'] = array(
                            'database_version',
                            'default_language',
                            'display_language_selection',
                            'use_utf8',
                            'frontend_language',
                            'shut_down',
                            'display_shut_down_message',
                            'shut_down_message',
                            'lifetime',
                            'lifetime_unit',
                            'email_address_host_name',
                            'mailbox_hostname',
                            'mailbox_username',
                            'mailbox_password',
                            'mailbox_connect_ssl',
                            'website_name',
                            'website_description',
                            'download_mail_process_key',
                            'script_url',
                            'non_validate_command',
                            'allow_set_email_address',
                            'activate_syndication',
                            'debug_mode'
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




// Get setting data
if ($t12l_settings = t12l_setting::read_all()) {
    if (isset($t12l_settings['installed_modules'])) {
        $t12l_settings['installed_modules'] = unserialize($t12l_settings['installed_modules']);
    }
    $t12l = array_merge($t12l, $t12l_settings);
}


if ($t12l['debug_mode'] == 'Y') {
    ini_set('error_reporting', E_ALL);
} else {
    ini_set('error_reporting', 0);
}

// -----------------------------------------------------------------------------




// Include language file
$t12l_language = $t12l['default_language'];
if ($language = t12l_setting::read('default_language')) {
    $t12l_language = $language['setting_value'];
}
if (isset($frontend_language)) {
    $t12l_language = $t12l['frontend_language'];
    if ($language = t12l_setting::read('frontend_language')) {
        $t12l_language = $language['setting_value'];
    }
}

$t12l['current_language']    = t12l_language::get($t12l_language);
$t12l['text']                = t12l_language::load($t12l['current_language']);

$t12l['website_name']        = $t12l['text']['txt_disposable_email_address'];
$t12l['website_description'] = $t12l['text']['txt_disposable_email_address_note'];

// -----------------------------------------------------------------------------




// Settings
$time_units = array(
                    'seconds'   => $t12l['text']['txt_seconds'],
                    'minutes'   => $t12l['text']['txt_minutes'],
                    'hours'     => $t12l['text']['txt_hours'],
                    'days'      => $t12l['text']['txt_days'],
                    'weeks'     => $t12l['text']['txt_weeks'],
                    'months'    => $t12l['text']['txt_months'],
                    'years'     => $t12l['text']['txt_years']
                );
$t12l['enable_moderation']               = 'N';
$t12l['text']['txt_postpone_expiration'] = sprintf($t12l['text']['txt_postpone_expiration'], $t12l['lifetime'], $time_units[$t12l['lifetime_unit']]);

                
// -----------------------------------------------------------------------------




// Manage update
include 'update.class.inc.php';
$t12l_update = new t12l_update;
if ($t12l_update->status() != true) {
    $t12l_update->start();
}
// -----------------------------------------------------------------------------




// Prepare data for output
$administration_account = unserialize($t12l['administration_login']);
$t12l['website_description'] = sprintf($t12l['website_description'], $t12l['lifetime'], $time_units[$t12l['lifetime_unit']]);
$t12l['output'] = array(
                    'administrator_email'           => $administration_account['email'],
                    'software'                      => $t12l['software'],
                    'version'                       => $t12l['version'],
                    'demo_mode'                     => $t12l['demo_mode'],
                    'debug_mode'                    => $t12l['debug_mode'],
                    'shut_down'                     => $t12l['shut_down'],
                    'display_shut_down_message'     => $t12l['display_shut_down_message'],
                    'shut_down_message'             => $t12l['shut_down_message'],
                    'script_url'                    => $t12l['script_url'],
                    'complete_script_url'           => $t12l['server_protocol'] . $t12l['server_name'] . $t12l['script_url'],
                    'mail_download_url'             => $t12l['server_protocol'] . $t12l['server_name'] . $t12l['script_url'] . 'download.php?get=mail&key=' . $t12l['download_mail_process_key'],
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
                    'email_address_host_name'       => $t12l['email_address_host_name'],
                    'allow_set_email_address'       => $t12l['allow_set_email_address'],
                    'allow_delete_email'            => $t12l['allow_delete_email'],
                    'activate_syndication'          => $t12l['activate_syndication'],
                    'automatic_email_check'         => $t12l['automatic_email_check'],
                    'display_setting_navigation'    => false
                    );


// -----------------------------------------------------------------------------




t12l_module::call_module('core', $t12l['module_additional'], $t12l['module_additional']);

// -----------------------------------------------------------------------------




// Login
require 'login.class.inc.php';
if (T12L_LOGIN_LEVEL > 0) {
    $t12l_login = new t12l_login(T12L_LOGIN_LEVEL);
    if ($t12l_login->status() == true) {
        $t12l['login_status'] = true;
    }
}

// -----------------------------------------------------------------------------


?>