<?php

/** 
 * GentleSource Temporary E-mail
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */






/**
 * News main class
 */
class t12l_content
{

    var $detail_template    = null;
    var $output             = null;



    /**
     * 
     */
    function t12l_content()
    {
        // Start output handling
        $this->output = new t12l_output();
    }

// -----------------------------------------------------------------------------




    /**
     * Assign values to the templates - wrapper of smarty->assign
     * 
     * @param mixed $a Name or associative arrays containing the name/value
     * pairs
     * @param mixed $b Value (can be string or array)
     * 
     * @access public
     */
    function assign($a, $b = null)
    {
        if (is_array($a)) {
            $this->output->assign($a);
            return true;
        }
        $this->output->assign($a, $b);
        return true;
    }
    
// -----------------------------------------------------------------------------  




    function finish()
    {
        $this->output->set_detail_template($this->detail_template);
        
        // Output
        return $this->output->finish(false);       
    }

// -----------------------------------------------------------------------------




} // End of class








?>
