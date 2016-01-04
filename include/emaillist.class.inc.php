<?php
 
/**
 * GentleSource Temporary E-mail -  emaillist.class.inc.php
 * 
 * @copyright   (C) Ralf Stadtaus , {@link http://www.gentlesource.com/}
 * 
 */

require_once 'list.class.inc.php';




/**
 * Generate e-mail list
 */
class t12l_email_list extends t12l_list  
{


    /**
     * Database fields to be selected
     */
    var $fields = array('mail_id',
                        'mail_from',
                        'mail_subject',
                        'mail_excerpt',
                        'mail_character_set',
                        'mail_timestamp',
                        );


    /**
     * Columns that can be sorted
     */
    var $order_columns = array('mail_timestamp');


    /**
     * Identifier to tell different list settings in session apart
     */
    var $identifier = 'maillist';


    /**
     * Default order direction for SQL statement
     * Possible values: ascending|descending
     */
    var $default_order_direction = 'descending';


    /**
     * Default order field for SQL statement
     */
    var $default_order_field = 'tm.mail_timestamp';

//------------------------------------------------------------------------------




    /**
     * Constructor
     */
    function t12l_email_list($use_session, $setup = array())
    {
        global $t12l;

        // Configuration and setup
        if ($use_session == true) {
            $this->use_session = true;
        }            
        $this->t12l_list($setup);
    }

//------------------------------------------------------------------------------




    /**
     * Get mail list
     */
    function get_list($address_id)
    {
        global $t12l;
        
        list($where, $data) = $this->where();
        
        $sql = "SELECT      " . t12l_database::fields('tm', $this->fields) . " 
                            
                FROM        (" . T12L_MAIL_TABLE . " AS tm) 
                            " .  $where . "
                AND         tm.mail_address_id = ?
                ORDER BY    " . $this->order_field . " " . $this->order_direction;
                
        //t12l_system_debug::add_message('SQL Statement get_list()', $sql, 'debug');
        $list = array();
        if ($res = $this->query($sql, array($address_id))) {
            
            // Mail number
            if ($this->order_direction == 'ASC') {
                $mail_number = 0;
            } else {
                $mail_number = $this->num_results;
            }
            
            // Fetch mails
            while ($row = $res->fetchRow()) 
            {
                t12l_clean_output($row);
                
                // Mail number
                if ($this->order_direction == 'ASC') {
                    $mail_number++;
                } else {
                    $mail_number--;
                }
                

                
                $row['frontend_date']   = t12l_time::format_date($row['mail_timestamp']);
                $row['frontend_time']   = t12l_time::format_time($row['mail_timestamp']);
                $row['frontend_number'] = $mail_number;
                
                $row['frontend_subject']    = $row['mail_subject'];
                $row['frontend_text']       = $row['mail_excerpt'];
                  
                // Convert character sets
                if (function_exists('iconv')
                        and $t12l['text']['txt_charset'] != $row['mail_character_set']) {

                    if ($result = @iconv($row['mail_character_set'], $t12l['text']['txt_charset'] . '//TRANSLIT', $row['mail_subject'])) {
                        $row['frontend_subject'] = $result;
                    }
                    if ($result = @iconv($row['mail_character_set'], $t12l['text']['txt_charset'] . '//TRANSLIT', $row['mail_excerpt'])) {
                        $row['frontend_text'] = $result;
                    }
                } 
                
                $row['frontend_subject']    = htmlspecialchars(strip_tags($row['frontend_subject']));                 
                $row['frontend_text']       = htmlspecialchars(strip_tags(str_replace("\n", ' ',  str_replace("\r", ' ',  trim($row['frontend_text'])))));
                $row['frontend_from']       = htmlspecialchars($row['mail_from']); 
                $row['frontend_url']        = $t12l['server_protocol'] . $t12l['server_name'] . $t12l['script_url'] . '?m=' . $row['mail_id'] . '&amp;a=' . urlencode(t12l_session::get('address_email'));
                
                
                $row['utf8_title']          = t12l_utf8_encode($row['frontend_subject']); 
                $row['utf8_excerpt']        = t12l_utf8_encode($row['frontend_text']); 
                $row['last_modified_atom']  = date('Y-m-d\TH:i:s\Z', $row['mail_timestamp']);
                
                t12l_module::call_module('frontend_content', $row, $t12l['module_additional']);
                
                $list[] = $row;
            }
        }
        return $list;
    }

//------------------------------------------------------------------------------



}
?>
