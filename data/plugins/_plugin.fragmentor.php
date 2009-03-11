<?php

defined('SKYBLUE') or die('Unauthorized file request');

/**
* Show a View of Data item(s) in a page region indicated by:
*
* <!--#plugin:fragmentor(name=fragment_name&view=view_name&param1=value1&...)-->
*
* OR
*
* {plugin:fragmentor(name=fragment_name&view=view_name&param1=value1&...)}
*
* NOTE: 
*
* param1=value1 arguments are optional custom parameters that will be 
* passed to your fragment in the $params variable.
*
* @param  string A key=>value paired query string
* @return string The html output of the fragment
*/

global $Core;

$Core->register('OnRenderPage', 'doFragmentorPlugin');

function doFragmentorPlugin($html) {
	$Fragmentor = FragmentorPlugin::getInstance();
	$Fragmentor->execute($html);
	return $Fragmentor->getHtml();
}
    
class FragmentorPlugin {

    var $html;

	static $output = array();
	
	static $objs = array();
	
	function getInstance() {
		static $instance;
		if (!isset($instance)) {
		    $Class = __CLASS__;
		    $instance = new $Class;
		}
		return $instance;
	}
	
	function execute($html) {
		$this->parse_page($html);
	}
	
	function getHtml() {
	    return $this->html;
	}
	
	function parse_page($html) {
	
		if (function_exists('plgSiteVars')) {
        	$html = plgSiteVars($html);
        }
        
        preg_match_all("/(<!--fragment\((.*)\)-->)/i", $html, $tokens);
        if (count($tokens) < 3) return $html;
        $tokens = $tokens[2];
        for ($i=0; $i<count($tokens); $i++) {
            $html = str_replace(
            	"<!--fragment({$tokens[$i]})-->", 
            	$this->execute_fragment($tokens[$i]), 
            	$html
            );
        }
        preg_match_all("/({fragment\((.*)\)})/i", $html, $tokens);
        if (count($tokens) < 3) return $html;
        $tokens = $tokens[2];
        for ($i=0; $i<count($tokens); $i++) {
            $html = str_replace(
            	"{fragment({$tokens[$i]})}", 
            	$this->execute_fragment($tokens[$i]), 
            	$html
            );
        }
        
        $this->html = $html;
	}
	
	function execute_fragment($query) {
	
        global $Core;
        
        static $output = array();
        static $objs = array();
        
        /*
        *  If no arguments are passed, return a null value.
        */

        if (empty($query)) return null;
        
        /*
        * If this fragment and view have already been executed, 
        * we can just return the output and skip the rest of 
        * the code.
        */
        
        if (isset($output[$query])) {
            return $output[$query];
        }
        
        $params = $this->parse_query($query);
        
        $fragment = Filter::get($params, 'name');
        $view = Filter::get($params, 'view', 'view');
        
        if (empty($fragment)) return null;
        if (empty($view)) return null;
        
        /*
        * Re-name 'menu' to 'page' so we get the right 
        * data objects.
        */
        
        $data_file = $fragment == 'menu' ? 'page' : $fragment;
        
        /*
        * If this is the first time the fragment is called, we get the data objects 
        * and store then in a static variable. The next time these data objects are 
        * requested, we do not need to perform another disk read.
        */

        if (!array_key_exists($data_file, $objs)) {
            if (file_exists(SB_XML_DIR . "{$data_file}.xml")) {
                $objs[$data_file] = $Core->xmlHandler->parserMain(SB_XML_DIR . $data_file.'.xml');
            }
            else if (file_exists(SB_XML_DIR . "{$data_file}/{$data_file}.xml")) {
                $objs[$data_file] = $Core->xmlHandler->parserMain(
                    SB_XML_DIR . "{$data_file}/{$data_file}.xml"
                );
            }
        }
        
        $data = Filter::get($objs, $data_file);
        
        /*
        * Store the output in our static variable so we don't need to execute 
        * all of the plugin code if the same fragemnt and view are called again.
        */
        
        $output[$query] = $this->buffer_output(
            $fragment, $data, $view, $params
        );
        
        /*
        * Output the data.
        */
        
        return $output[$query];
    }
    
    /**
    * Buffer the fragment output
    */
    
    function buffer_output($name, $data, $view, $params=array()) {
        global $Core;
        
        static $buffer = array();
        
        $key = "{$name}.{$view}";
        
        if (!array_key_exists($key, $buffer)) {
            $fragment = ACTIVE_SKIN_DIR . "fragments/$name/{$view}.php";
            $functions = ACTIVE_SKIN_DIR . "fragments/$name/functions.php";
            if (!file_exists($fragment)) return null;
            ob_start();
            if (file_exists($functions)) {
                require_once($functions);
            }
            require_once($fragment);
            $buffer[$key] = ob_get_contents();
            ob_end_clean();
            if (function_exists('plgBBCoder')) {
                $buffer[$key] = plgBBCoder($buffer[$key]);
            }
        }
        return $buffer[$key];
    }
    
    /*
    * Parse the query string
    */
    
    function parse_query($str) {
    
        $str = html_entity_decode($str);

        $arr = array();
        
        $pairs = explode('&', $str);
        
        foreach ($pairs as $i) {
            list($name,$value) = explode('=', $i, 2);

            if (isset($arr[$name])) {
                if (is_array($arr[$name])) {
                    $arr[$name][] = $value;
                }
                else {
                    $arr[$name] = array($arr[$name], $value);
                }
            }
            else {
                $arr[$name] = $value;
            }
        }
        return $arr;
    }

}

?>
