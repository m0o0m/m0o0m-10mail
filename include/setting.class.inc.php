<?php

/** 
 * GentleSource Comment Script - setting.class.inc.php
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


//require_once 'database.class.inc.php';




/**
 * Handle comments
 */
class t12l_setting
{




// -----------------------------------------------------------------------------




    /**
     * Write setting to database
     * 
     * @access public
     */
    function write($name, $value)
    {
        global $t12l;
        
        if (t12l_setting::read($name)) {
            $data = array('setting_value' => $value);
            $where = "setting_name = ?";
            $where_data = array($name);
            t12l_database::update('setting', $data, $where, $where_data);
        } else {
            $data = array('setting_name' => $name, 'setting_value' => $value);
            t12l_database::insert('setting', $data);
        }
        $n2s[$name] = $value;
    }

// -----------------------------------------------------------------------------




    /**
     * Get setting from database
     * 
     * @access public
     */
    function read($name)
    {
        global $t12l;
        
        $sql = "SELECT setting_name, setting_value 
                FROM " . T12L_SETTING_TABLE . "
                WHERE setting_name = ?";        
        if ($db = t12l_database::query($sql, array($name))) {
            $res = $db->fetchRow();    
            if (sizeof($res) > 0) {
                return $res;
            }
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Get settings
     */
    function read_all()
    {
        global $t12l;
        $list = array();
        $sql = "SELECT      setting_name, setting_value 
                FROM        " .  T12L_SETTING_TABLE;
                
        if ($db = t12l_database::connection()) {            
            if ($res =& $db->query($sql)) {
                if (PEAR::isError($res)) {
                    t12l_system_debug::add_message($res->getMessage(), $res->getDebugInfo(), 'error', $res->getBacktrace());
                    t12l_system_debug::add_message('SQL Statement', $sql, 'error');
                    return false;
                }
                while ($row = $res->fetchRow()) 
                {
//                    if (!in_array($row['setting_name'], $t12l['setting_names'])) {
//                        continue;
//                    }
                    t12l_clean_output($row);                
                    $list[$row['setting_name']] = $row['setting_value'];
                }
            }
        }
        return $list;
    }

//------------------------------------------------------------------------------




    /**
     * Count a setting value up
     * 
     * @access public
     */
    function increase($name)
    {
        global $t12l;
        
        if ($setting = t12l_setting::read($name)) {
            $data = array('setting_value' => $setting['setting_value'] + 1);
            $where = "setting_name = ?";
            $where_data = array($name);
            t12l_database::update('setting', $data, $where, $where_data);
        } else {
            $data = array('setting_name' => $name, 'setting_value' => 1);
            t12l_database::insert('setting', $data);
        }
    }

// -------------------------------------



} // End of class








?>
