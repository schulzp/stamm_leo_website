<?php

    /**
    * Show a View of Data item(s) in a page region indicated by:
    *
    * <!--#plugin:fragment(fragment_name, view_name, property_match=[vals], [page_ids])-->
    *
    * OR
    *
    * {plugin:fragment(fragment_name, view_name, property_match=[vals], [page_ids])}
    *
    * @param  array  An argument array
    * @return string The html output of the fragment
    */

    function fragment($args) {
        global $Core;

        /*
        *  If no arguments are passed, return a null value.
        */

        if (!count($args)) return null;
        
        $source = $args[0];
                
        $view = 'view';
        if (count($args) > 1) {
            $view = $args[1];
        }
        
        /*
        * The prop_match argument is optional. If no prop_match 
        * is supplied, all data objects will be loaded.
        */

        $vals = array();
        $prop_match = null;
        if (count($args) > 2) {
            $arr = $args[2];
            if (is_array($arr)) {
                $keys = array_keys($arr);
                $vals = array_values($arr);
                $prop_match = $keys[0];
                $vals = $vals[0];
            }
        }
        
        /*
        * The pageIds argument is optional. If no pageIds 
        * are supplied, the fragement will display on every page 
        * thate uses this template.
        */
        
        $pageIds = array();
        if (count($args) > 3) {
            $pageIds = $args[3];
        }
        
        
        /*
        * Store the current page id in a variable.
        */
        
        $thisPageId = $Core->GetVar($_GET, 'pid', DEFAULT_PAGE);
       
        /*
        * The page id passed by the plugin call can be a single page id or 
        * an array of page ids. If the page id is set, make sure it matches 
        * the current page id.
        */
        
        if (is_array($pageIds) && 
            count($pageIds) && 
            !in_array($thisPageId, $pageIds))
        {
            return null;
        }
        else if (!is_array($pageIds) && 
            !empty($pageIds) && $thisPageId !== $pageIds)
        {
            return null;
        }
        
        /*
        * Load the data objects from storage
        */
        
        /*
        * Re-name 'menu' to 'page' so we get the right 
        * data objects.
        */
        
        $data_file = $source == 'menu' ? 'page' : $source;
        
        $objs = array();
        if (file_exists(SB_XML_DIR . "{$data_file}.xml")) {
            $objs = $Core->xmlHandler->parserMain(SB_XML_DIR . $data_file.'.xml');
        }
        else if (file_exists(SB_XML_DIR . "{$data_file}/{$data_file}.xml")) {
            $objs = $Core->xmlHandler->parserMain(SB_XML_DIR . "$data_file/$data_file.xml");
        }
        
        /*
        * Output the data.
        */
        
        return fragment_buffer(
            $source, 
            fragment_data($objs, $prop_match, $vals),
            $view
        );
    }
    
    /**
    *
    */
    
    function fragment_buffer($name, $data, $view) {
        global $Core;
        $fragment = ACTIVE_SKIN_DIR . "fragments/$name/{$view}.php";
        $functions = ACTIVE_SKIN_DIR . "fragments/$name/functions.php";
        if (!file_exists($fragment)) return null;
        ob_start();
        if (file_exists($functions)) {
            require_once($functions);
        }
        require($fragment);
        $buffer = ob_get_contents();
        ob_end_clean();
        if (function_exists('plgBBCoder')) {
            $buffer = plgBBCoder($buffer);
        }
        return $buffer;
    }
    
    /**
    * Get the requested data objects
    */
    
    function fragment_data($objs, $prop_match, $vals) {
        if (!count($vals) || empty($prop_match)) return $objs;
        $data = array();
        for ($i=0; $i<count($objs); $i++) {
            $obj =& $objs[$i];
            if (!empty($prop_match) && 
                isset($obj->$prop_match) && 
                in_array($obj->$prop_match, $vals))
            {
                array_push($data, $obj);   
            }
        }
        return $data;
    }

?>
