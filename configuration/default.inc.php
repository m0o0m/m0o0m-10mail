<?php

/** 
 * GentleSource 
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
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



$t12l['script_url']                 = './';
$t12l['template_directory']         = 'template/'; 
$t12l['cache_directory']            = 'cache/';
$t12l['backup_directory']           = 'cache/backup/';
$t12l['xmlhttprequest_library_path']= 'template/jscript/xmlhttprequest/';
$t12l['default_template']           = 'default';
$t12l['global_template_file']       = 'layout.tpl.html';
$t12l['mail_template_file']         = 'layout.tpl.txt';
$t12l['time_difference']            = +0; // Time difference in minutes
$t12l['automatic_identifier']       = false; // false = off, true = on
$t12l['identifier_key']             = 't12l'; // If automatic identifier ?t12l=ID provides identifier
$t12l['default_language']           = 'en';
$t12l['frontend_language']          = 'en';
$t12l['session_vars_name']          = 'T12L_SESS';
$t12l['language_cookie_name']       = 'T12L_LANG';
$t12l['cookie_path']                = '/';
$t12l['cookie_domain']              = '.' . $_SERVER['HTTP_HOST'];
$t12l['backup_file_prefix']         = 'database_backup_';
$t12l['server_protocol']            = 'http://';
$t12l['server_name']                = $_SERVER['HTTP_HOST'];
$t12l['login_redirect']             = $t12l['server_protocol'] . $t12l['server_name'];
$t12l['logout_redirect']            = $t12l['server_protocol'] . $t12l['server_name'];

$t12l['mail_link']                  = array(
                                        'protocol'  => 'http://',
                                        'server'    => $_SERVER['SERVER_NAME'],
                                        'path'      => dirname($_SERVER['PHP_SELF']) . '/'
                                        );

$t12l['debug_mode']                 = 'N';
$t12l['demo_mode']                  = false;

$t12l['module_directory']           = 'module/';
$t12l['installed_modules']          = array(
                                        'gentlesource_module_cron',
                                        );

$t12l['mail_type']                  = 'mail'; // (mail, smtp)
$t12l['mail_from']                  = 'postmaster@' . $_SERVER['SERVER_NAME'];
$t12l['smtp']['host']               = 'example.com';
$t12l['smtp']['port']               = 25;
$t12l['smtp']['helo']               = $_SERVER['SERVER_NAME'];
$t12l['smtp']['auth']               = false;
$t12l['smtp']['user']               = '';
$t12l['smtp']['pass']               = '';

$t12l['language_directory']         = 'language/';
$t12l['language_directory_utf8']    = 'language/utf-8/';
$t12l['use_utf8']                   = 'N';
$t12l['available_languages']        = array(
                                        'nl' => 'Dutch',
                                        'en' => 'English',
                                        'de' => 'German',
                                        'pl' => 'Polish',
                                        'es' => 'Spanish',
                                        );
                                    
$t12l['domain_language']            = array(
                                        'de'    => 'de',
                                        'nl'    => 'nl',
                                        'pl'    => 'pl',
                                        'es'    => 'es',
                                        );
             
$t12l['received_emails']            = 0;
$t12l['sequence_mail']              = 0;
$t12l['expired_emails']             = 0;
$t12l['sent_emails']                = 0;
$t12l['created_addresses']          = 0;                           
$t12l['shut_down']                  = 'N';
$t12l['display_shut_down_message']  = 'Y';
$t12l['shut_down_message']          = '';
$t12l['frontend_order']             = 'descending';
$t12l['lifetime']                   = 15;
$t12l['lifetime_unit']              = 'minutes';

$t12l['email_address_host_name']    = '';
$t12l['download_mail_process_key']  = 'te2k7';

$t12l['mailbox_username']           = '';
$t12l['mailbox_password']           = '';
$t12l['mailbox_hostname']           = '';
$t12l['mailbox_port']               = 110; // 110 POP3 port
$t12l['mailbox_connect_ssl']        = 'N';
$t12l['mailbox_port_ssl']           = 995; // 995 SSL port
$t12l['mailbox_add_path']           = '';
$t12l['mailbox_add_path_ssl']       = '';
$t12l['non_validate_command']       = 'N';

$t12l['alternative_mail_download']  = false;
$t12l['message_body_format']        = 'text'; // text, html, complete

$t12l['display_language_selection'] = 'Y';
$t12l['language_selector_mode']     = 'links'; // links, form
$t12l['allow_set_email_address']    = 'Y';
$t12l['allow_delete_email']         = 'Y';
$t12l['public_mails']               = 'N';
$t12l['activate_syndication']       = 'Y';
$t12l['automatic_email_check']      = 'Y';







?>
