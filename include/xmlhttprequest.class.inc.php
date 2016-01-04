<?php
 
/** 
 * GentleSource List Script - list.class.inc.php
 * 
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */




/**
 * 
 */
class t12l_xmlhttprequest
{
    
    
    /**
     * Server
     */
    function server()
    {
        global $t12l;
        
        include 'HTML/AJAX/Server.php';

        $server = new HTML_AJAX_Server();
        $server->setClientJsLocation(T12L_ROOT . $t12l['xmlhttprequest_library_path']);
        $server->handleRequest();
        
    }
    
    
    
    
} // End of class