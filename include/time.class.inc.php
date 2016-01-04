<?php
 
/**
 * GentleSource Comment Script -  time.class.inc.php
 * 
 * @copyright   (C) Ralf Stadtaus , {@link http://www.gentlesource.com/}
 * 
 */




/**
 * Date and time handler
 */
class t12l_time
{




    /**
     * Current timestamp
     */    
    function current_timestamp()
    {
        global $t12l;
        return mktime() + ($t12l['time_difference'] * 60);
    }
    
//------------------------------------------------------------------------------




    /**
     * Current day (00:00) as timestamp
     */    
    function current_day()
    {
        $timestamp = t12l_time::current_timestamp();
        $day = mktime(  0, 
                        0, 
                        0, 
                        date('m', $timestamp),
                        date('d', $timestamp),
                        date('Y', $timestamp));
        return $day;
    }
    
//------------------------------------------------------------------------------




    /**
     * Formats timestamp to date
     */    
    function format_date($timestamp = 0)
    {
        global $t12l;

        if ($timestamp <= 0) {
            return '';
        }
        
        return date($t12l['text']['txt_date_format'], $timestamp);
    }
    
//------------------------------------------------------------------------------




    /**
     * Formats timestamp to time
     */    
    function format_time($timestamp = 0)
    {
        global $t12l;

        if ($timestamp <= 0) {
            return '';
        }
        
        return date($t12l['text']['txt_time_format'], $timestamp);
    }
    
//------------------------------------------------------------------------------




    /**
     * Get days in seconds
     */    
    function days_to_seconds($days)
    {
        return ($days * 24 * 60 * 60);
    }
    
//------------------------------------------------------------------------------




    /**
     * Convert into seconds
     */    
    function convert_to_seconds($number, $unit)
    {
        if ($unit == 'seconds') {
            return $number;
        }
        if ($unit == 'minutes') {
            $result = $number * 60;
            return $result;
        }
        if ($unit == 'hours') {
            $result = $number * 60 * 60;
            return $result;
        }
        if ($unit == 'days') {
            $result = $number * 60 * 60 * 24;
            return $result;
        }
        if ($unit == 'weeks') {
            $result = $number * 60 * 60 * 24 * 7;
            return $result;
        }
        if ($unit == 'months') {
            $result = mktime(
                            date('H', t12l_time::current_timestamp()), 
                            date('i', t12l_time::current_timestamp()),
                            date('s', t12l_time::current_timestamp()),
                            date('m', t12l_time::current_timestamp()) + $number,
                            date('d', t12l_time::current_timestamp()),
                            date('Y', t12l_time::current_timestamp())
                            );
            $result = $result - t12l_time::current_timestamp();
            return $result;
        }
        if ($unit == 'years') {
            $result = mktime(
                            date('H', t12l_time::current_timestamp()), 
                            date('i', t12l_time::current_timestamp()),
                            date('s', t12l_time::current_timestamp()),
                            date('m', t12l_time::current_timestamp()),
                            date('d', t12l_time::current_timestamp()),
                            date('Y', t12l_time::current_timestamp()) + $number
                            );
            $result = $result - t12l_time::current_timestamp();
            return $result;
        }
    }
    
//------------------------------------------------------------------------------




    /**
     * Convert seconds into time and unit
     */    
    function convert_to_unit($seconds)
    {
        $result = $seconds;
        
        // Seconds        
        return $result;
    }
    
//------------------------------------------------------------------------------





}








?>
