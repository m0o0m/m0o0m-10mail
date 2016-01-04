<?php

/** 
 * GentleSource News Script
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


//require_once 'database.class.inc.php';




/**
 * Handle backups
 */
class t12l_backup
{




    /**
     * 
     * 
     * @access public
     */
    function t12l_backup()
    {
    }

// -----------------------------------------------------------------------------




    /**
     * Export data
     * 
     * @access public
     */
    function export()
    {
        global $t12l;
        
        $path = T12L_ROOT . $t12l['backup_directory'];
        
        // Create directory and .htaccess
        if (!is_dir($path)) {
            mkdir($path);    
            $htcontent = "deny from all";
            file_put_contents($path . '.htaccess', $htcontent);
            
        }
        
//        set_time_limit(600);
        ini_set('max_execution_time', 600);
        ignore_user_abort(true);
        
        //Get database content
        $filename   = $path . $this->filename();          
        $source     = array("\x00", "\x0a", "\x0d", "\x1a");
        $target     = array('\0', '\n', '\r', '\Z');       
        $dump       = array();
        while(list($key, $val) = each($t12l['tables']))
        {
            if ($key == 'setting') {
                $sql = "SELECT * FROM " . $val . " WHERE setting_name != 'administration_login' AND setting_name != 'script_url' AND setting_name != 'database_version'";
            } else {
                $sql = "SELECT * FROM " . $val;
            }
            
            if ($res = t12l_database::query($sql)) {
                while ($row = $res->fetchRow())
                {
                    $tmp   = array();
                    $tmp[] = 'INSERT INTO `' . $val . '` ';
                    $tmp[] = '(`' . join('`, `', array_keys($row)) . '`)';
                    $tmp[] = ' VALUES ';
                    $tmp[] = "('" . str_replace($source, $target, join("', '", array_values($row))) . "');";
                    $dump[]= join('', $tmp);
                }                
            }
        }
        $content = join("\n", $dump);
        
        // Write file
        if (file_put_contents($filename, $content)) {
            return true;
        }
    }

// -----------------------------------------------------------------------------




    /**
     * Create file name
     * 
     * @access public
     */
    function filename()
    {
        global $t12l;
        $filename  = $t12l['backup_file_prefix'];
        $filename .= date('Y-m-d_H-i-s', t12l_time::current_timestamp());
        $filename .= '.sql';
        return $filename;
    }

// -----------------------------------------------------------------------------




    /**
     * List available backup files
     * 
     * @access public
     */
    function file_list()
    {
        global $t12l;
        $list = array();
        if (!is_dir(T12L_ROOT . $t12l['backup_directory'])) {
//            return $list;
        }
        require_once 'Find.php';        
        if ($items = &File_Find::glob( '#' . $t12l['backup_file_prefix'] . '(.*?)\.sql#', T12L_ROOT . $t12l['backup_directory'], "perl" )) {
            if (PEAR::isError($items)) {
                t12l_system_debug::add_message($items->getMessage(), $items->getDebugInfo(), 'error', $items->getBacktrace());
                return $list;
            }
            arsort($items);
            while (list($key, $val) = each($items))
            {
                $date = $this->file_date($val);
                $time = str_replace('-', ':', substr($val, strrpos($val, '_') +1, 8));
                $list[] = array('file' => $val,
                                'path' => T12L_ROOT . $t12l['backup_directory'],
                                'date' => $date,
                                'time' => $time
                                );
            }
        }
        return $list;

    }

// -----------------------------------------------------------------------------




    /**
     * Create formatted date from file name
     * 
     * @access public
     */
    function file_date($val)
    {
        global $t12l;
        $date = substr($val, strpos($val, $t12l['backup_file_prefix']) + strlen($t12l['backup_file_prefix']), 10);
        $date = strtotime($date, t12l_time::current_timestamp());
        $date = t12l_time::format_date($date);
        return $date;

    }

// -----------------------------------------------------------------------------




    /**
     * Delete file
     * 
     * @access public
     */
    function delete($file)
    {
        global $t12l;
        if (is_file(T12L_ROOT . $t12l['backup_directory'] . trim($file))) {
            if (unlink(T12L_ROOT . $t12l['backup_directory'] . trim($file))) {
                return true;
            }
        }

    }

// -----------------------------------------------------------------------------




    /**
     * Import file
     * 
     * @access public
     */
    function import($file)
    {
        global $t12l;

        $file = T12L_ROOT . $t12l['backup_directory'] . trim($file);
        $error = false;
//        set_time_limit(600);
        ini_set('max_execution_time', 600);
        ignore_user_abort(true);
        if (is_file($file)) {
            if (!$sql = t12l_installation::parse_sql(file($file))) {
                $t12l['message'][] = $t12l['text']['txt_parse_backup_file_failed'];
                return false;
            }
            reset($sql);
            
            // Truncate tables
            foreach ($t12l['tables'] AS $table => $name)
            {
                if ($table == 'setting') {
                    $del = 'DELETE FROM `' . $name . '` WHERE `setting_name` != \'administration_login\' AND `setting_name` != \'script_url\' AND `setting_name` != \'database_version\'';
                    $res =  t12l_database::query($del);                    
                } else {
                    t12l_database::query('TRUNCATE `' . $name . '`;');
                }
            }
            foreach ($sql AS $statement)
            {
                // Replace prefix
                if (!$res = t12l_database::query($statement)) {
                    $error = true;
                } 
            }
        }
        if ($error == false) {
            return true;
        }

    }

// -----------------------------------------------------------------------------




} // End of class








?>
