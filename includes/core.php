<?php

/**
* @version		1.1 RC1 2008-11-20 21:18:00 $
* @package		SkyBlueCanvas
* @copyright	Copyright (C) 2005 - 2008 Scott Edwin Lewis. All rights reserved.
* @license		GNU/GPL, see COPYING.txt
* SkyBlueCanvas is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYING.txt for copyright notices and details.
*/

defined('SKYBLUE') or die(basename(__FILE__));

/**
* @author Scott Lewis <scott@bright-crayon.com>
* @version $Id: core.php, v 0.1 2006/08/14 05:17:00 scottlewis Exp $
* @copyright (C) 2006 Bright-Crayon, LLC
* @license Commercial
* @package SkyBlue
*
* The Core class contains 'core' functionality that is used throughout
* the SkyBlue system. This class includes sub-classes to perform various
* tasks such as XML parsing and generation, mail functions and HTML generation.
*
* The Core class also includes functionality to work with the file system, 
* language support, string manipulation, array manipulation ...
*/

class Core extends Observer {

    /**
     * Used to pass messages about the result of a user action from one
     * request (page) to another.
     *
     * @access public
     * @var    string
     */
    
    var $MSG = '...';
    
    /** @var RESET A flag to indicate whether or not to reset the
    *   MSG variable. Deprecated.
    */
    
    /**
     * Helper class to handle XML-related tasks. Stores the xmlHandler
     * object as defined by {@link xml.parser.php}.
     *
     * @access public
     * @var    object
     */
    
    var $xmlHandler = null;

    /**
     * Not implemented. DataSource will eventually be the bridge between 
     * the core and the data storage abstraction classes.
     *
     * @access public
     * @var    object
     */
    
    var $datasource = null;

    /**
     * Sub-class to handle Mail-related tasks. 
     *
     * @access public
     * @var    object
     */
    
    var $postmaster = null;
    
    /**
     * Helper class for sorting arrays of objects.
     * {@link plugin.quicksort.php}
     *
     * @access public
     * @var    object
     */
    
    var $quicksort = null;

    /**
     * A unique one-time page-level identifier to make sure that
     * any form submitted to the site, was created by the site.
     *
     * @access public
     * @var    string
     */

    var $token = null;

    /**
     * Array to hold the installed language package.
     *
     * @access public
     * @var    array
     */
    
    var $terms = null;

    /**
     * The relative path of the Core depending on whether
     * the Core is being loaded by the back end or front end.
     *
     * @access public
     * @var    string
     */
        
    var $path = null;
    
    /**
     * SkyBlue can display pop-up dialogs using HTML
     * and the $_SESSION array. This is dependent on the Skin class.
     *
     * @access public
     * @var    string
     */
    
    var $dialog = null;
    
    /**
    * The events on which callbacks can be fired
    */

    var $events = array();
    
    /**
    * The session lifetime
    */
    
    var $lifetime = 3600;
    
    function __construct($options=array()) {
        $this->path = Filter::get($options, 'path', '');
        $this->lifetime = Filter::get($options, 'lifetime', 3600);
        
        if (!defined('SB_BASE_PATH'))
        {
            define('SB_BASE_PATH', $this->path);
        }
        
        $this->declareEvents($options);
        $this->LoadConstants();
        $this->IsValidSite();
        $this->LoadHelperClasses();
        $this->GetActiveSkin();
        $this->InitSession($this->lifetime);
    }
    
    function Core($options=array()) {
        $this->__construct($options);
    }
    
    function declareEvents($config) {
        if (isset($config['events'])) {
            $events = $config['events'];
            for ($i=0; $i<count($events); $i++) {
                $this->addEvent($events[$i]);
            }
        }
    }
    
    function RegisterEvent($event, $callback) {
        $this->register($event, $callback);
    }

    function IsValidSite() {
        if (!file_exists(SB_SITE_DATA_DIR)) {
            die("No data directory was found for this installation");
        }
    }
    
    function editor($selector, &$html, $editor) {
        $code = null;
        
        $selectors = array($selector);
        if (strpos($selector, ',') !== false) {
            $selectors = explode(',', $selector);
        }
		for ($i=0; $i<count($selectors); $i++) {
			if ($this->hasSelector(trim($selectors[$i]), $html)) {
				$code = FileSystem::buffer(SB_EDITORS_DIR . "$editor/header.php");
			}
		}
        $html = str_replace(TOKEN_EDITOR, $code, $html);
    }
    
    function hasSelector($selector, &$html) {
        $attr = 'class';
        if ($selector{0} == '#') {
            $attr = 'id';
        }
        $selector = substr($selector, 1, strlen($selector)-1);        
		preg_match_all("/$attr=\"([^\"]+)\"/i", $html, $matches);
		if (count($matches) == 2) {
			$elements = array();
			$matches = $matches[1];
			for ($i=0; $i<count($matches); $i++) {
				$bits = explode(" ", $matches[$i]);
				for ($j=0; $j<count($bits); $j++) {
					if (trim($bits[$j]) != "") {
						$elements[] = $bits[$j];
					}
				}
			}
			if (in_array($selector, $elements)) {
				return true;
			}
		}
		return false;
    }
    
    function GetActiveSkin() {
        if (file_exists(SB_XML_DIR.'activeskin.xml')) {
            $skins = $this->xmlHandler->ParserMain(SB_XML_DIR.'activeskin.xml');
            if (count($skins)) {
                $skin  = $skins[0];
                if (isset($skin->activeskin) && !empty($skin->activeskin))
                {
                    $activeskin = $skin->activeskin;
                }
            }
        }

        defined('ACTIVE_SKIN_DIR') or
        define('ACTIVE_SKIN_DIR', SB_SKINS_DIR.$activeskin.'/');
        
        defined('ACTIVE_SKIN_CSS_DIR') or
        define('ACTIVE_SKIN_CSS_DIR', ACTIVE_SKIN_DIR.'css/');
        
        defined('ACTIVE_SKIN_IMG_DIR') or
        define('ACTIVE_SKIN_IMG_DIR', ACTIVE_SKIN_DIR.'images/');
        
        defined('MEDIA_CSS_DIR') or
        define('MEDIA_CSS_DIR', ACTIVE_SKIN_DIR.'media.styles/');
    }
    
    function LoadHelperClasses() {
        $this->DoInclude(SB_XML_PARSER_FILE,   __LINE__, __FUNCTION__);
        $this->DoInclude(SB_DATASOURCE_FILE,   __LINE__, __FUNCTION__);
        $this->DoInclude(SB_POSTMASTER_FILE,   __LINE__, __FUNCTION__);
        $this->DoInclude(SB_HTML_FACTORY_FILE, __LINE__, __FUNCTION__);
    
        $this->xmlHandler = new xmlHandler;
        $this->datasource = new datasource;
        $this->postmaster = new postmaster;
        $this->HTML = new html;
    }
    
    function FileNotFound($missingFile, $line, $func) {
        die('<b>Fatal Error</b><br />'.
             $missingFile.' was not found.<br />'.
             '<b>Line:</b> '.$line.'<br />'.
             '<b>Func:</b> '.$func
       );
    }
    
    function DoInclude($file, $lineNumber, $funcName) {
        if (file_exists($file)) {
            require_once($file);
        } 
        else {
            $this->FileNotFound($file, $lineNumber, $funcName);
        }
    }

    function LoadConstants() {
        define('SB_CONF_DIR',     SB_BASE_PATH.'configs/');
        define('SB_CONF_SERVER',  SB_CONF_DIR.'server.consts.php');
        define('SB_CONF_DIRS',    SB_CONF_DIR.'dirs.consts.php');
        define('SB_CONF_FILES',   SB_CONF_DIR.'files.consts.php');
        define('SB_CONF_STRINGS', SB_CONF_DIR.'strings.consts.php');
        define('SB_CONF_TOKENS',  SB_CONF_DIR.'tokens.consts.php');
        define('SB_CONF_REGEX',   SB_CONF_DIR.'regex.consts.php');

        $this->DoInclude(SB_CONF_SERVER,  __LINE__, __FUNCTION__);
        $this->DoInclude(SB_CONF_DIRS,    __LINE__, __FUNCTION__);
        $this->DoInclude(SB_CONF_FILES,   __LINE__, __FUNCTION__);
        $this->DoInclude(SB_CONF_STRINGS, __LINE__, __FUNCTION__);
        $this->DoInclude(SB_CONF_TOKENS,  __LINE__, __FUNCTION__);
        $this->DoInclude(SB_CONF_REGEX,   __LINE__, __FUNCTION__);

        $this->DefineUserURL();
    }
    
    function DefineUserURL() {
        define('SB_MY_URL',   '');
        define('SB_RSS_FEED', SB_RSS_DIR);
        define('SB_ADMIN_URL', '');
    }
    
    /**
    * LoadConfig loads the configuration files. Non-GUI-editable configs
    * are stored in ~/configs/. GUI-editable configs are stored in
    * ~/xml.system/configuration.xml.
    * @access private
    * @return array
    */
    
    function LoadConfig() {

        // LOAD EDITABLE CONFIGS: DO NOT DELETE
        
        $config = array();
        if (file_exists(SB_CONFIG_XML_FILE)) {
            $configObjs = $this->xmlHandler->ParserMain(SB_CONFIG_XML_FILE);
            
            $configObj = null;
            if (count($configObjs) > 0) {
                $configObj = $configObjs[0];
            } 
            
            if (!empty($configObj)) {
                foreach($configObj as $k=>$v)
                {
                    $config[$k] = trim($v);
                }
            }
        }
        
        // END EDITABLE CONFIGS
        
        $config = $this->DefineBaseURI($config);
        
        if (!defined('USE_SEF_URLS')) {
            define('USE_SEF_URLS', file_exists($this->path . '.htaccess') ? 1 : 0 );
        }
        
        if (isset($config['site_url']) && !empty($config['site_url'])) {
            $url = $config['site_url'];
            if (strpos($url, 'http://') === false) {
                $url = "http://$url";
            }
            if ($url{strlen($url)-1} != '/') {
                $url .= '/';
            }
            $config['site_url'] = $url;
            define('FULL_URL', $config['site_url']);
        } 
        else {
            define('FULL_URL', null);
        }
        
        if (isset($config['site_name']) && !empty($config['site_name'])) {
            define('SB_SITE_NAME', $config['site_name']);
        } 
        else {
            define('SB_SITE_NAME', null);
        }
        
        if (isset($config['site_slogan']) && !empty($config['site_slogan'])) {
            define('SB_SITE_SLOGAN', $config['site_slogan']);
        } 
        else {
            define('SB_SITE_SLOGAN', null);
        }
        
        return $config;
    }
    
    function DefineBaseURI($config) {
        $config['base_uri'] = SB_MY_URL;
        define('BASE_URI', SB_MY_URL);
        return $config;
    }
    
    /**
    * Determines if the instance of SkyBlue is a new or un-configured
    * installation. If true, the "Start" screen is loaded. If no user-installed
    * skin is found this function will create a skin directory tree and install
    * the first basic structural HTML file for the skin. The directory tree
    * is installed automatically so that the system does not encounter any
    * errors arising from trying to access something that does not exist.
    * @access public
    * @return void
    */

    function CheckInstall() {
        $files   = array(
            SB_LOGIN_FILE,
            SB_CONFIG_XML_FILE,
            SB_PAGE_FILE,
            SB_MENU_GRP_FILE
        );
        for ($i=0; $i<count($files); $i++) {
            if (!file_exists($files[$i])) {
                $this->SBRedirect(SB_SETUP_PAGE);
            }
        }
    }

    function GetUnreadMailCount() {
	    $count = 0;
        if (is_dir(SB_SITE_EMAIL_DIR)) {
            $files = $this->ListFilesOptionalRecurse(SB_SITE_EMAIL_DIR, 0, array());
            for ($i=0; $i<count($files); $i++) {
                if ($files[$i] != SB_EMAIL_ERROR_LOG) {
	                $name = basename($files[$i]);
                    if ($name{0} == '~') $count++;
                }
            }
        }
        return $count;
    }
    
    /**
    * Ensures that the newly added item has a unique ID property.
    * @access public
    * @param refernce $objs a reference to the array of objects.
    * @return integer
    */

    function GetNewID(&$objs) {
        $ids = array();
        for ($i=0; $i<count($objs); $i++) {
            if (is_object($objs[$i])) {
                array_push($ids, $objs[$i]->id);
            }
            else {
                array_push($ids, $i);
            }
        }
        sort($ids);
        $id = count($ids) > 0 ? intval($ids[count($ids)-1]) : 1 ;
        $id++;
        return $id;
    }
    
    /**
    * Loads the currently installed localization package.
    * @access private
    * @return void
    */
    
    function LoadLanguage() {
        $this->terms = array();
        if (file_exists(SB_LANG_FILE)) {
            include(SB_LANG_FILE);
            $this->terms = $TERMS;
        }
    }
    
    /**
    * Loads the plugin specified by $plugin.
    * All plugins must reside in their own directory in ~/plugins/. The plugin
    * directory name must match the plugin name exactly. The plugin
    * class file name must be named <name>.class.php.
    * @access public
    * @param string $plugin the name of the plugin to load.
    * @return void
    */
    
    function LoadPlugin($plugin) {
		global $config;
		
		if (!empty($plugin)) {
			$path = SB_PLUGIN_DIR.$plugin.'/'.$plugin.'.class.php';
			set_include_path(get_include_path() . PATH_SEPARATOR . $path);
			if (file_exists($path)) {
				require($path);
				return new $plugin;
			} 
			else {
				trigger_error(
					'SkyBlueCanvas Says: File '.$plugin.' does not exist. '
					.  __FILE__ . ': on line ' . __LINE__
				);
			}
		} 
		else {
			trigger_error(
				'SkyBlueCanvas Says: No Plugin specified. '
				. __FILE__ . ': on line ' . __LINE__
			);
		}
    }

    function LoadUserPlugins() {
	    if (is_dir(SB_USER_PLUGINS_DIR)) {
	        $files = $this->ListFilesOptionalRecurse(SB_USER_PLUGINS_DIR, 0, array());
	        for ($i=0; $i<count($files); $i++) {
	            $name = basename($files[$i]);
	            if ($name{0} == '_') continue;
	            include_once($files[$i]);
	        }
	    }
	}
	    
    function UpdateSitemap() {
        $this->LoadPlugin(SB_SITEMAPPER_CLASS);
    }
    
    /**
    * Verifies that the object being acted on has a unique ID.
    * This function should be called before any Save or Delete action, 
    * otherwise, unexpected results may occur.
    * @access public
    * @param integer $id the local ID variable.
    * @return void
    */
    
    function RequireID($id, $redirect) {
        if (empty($id)) {
            $this->ExitEvent(3, $redirect);
        }
    }
    
    /**
    * A globally available and generic Cancel function.
    * @access public
    * @param string $redirect The URL to which to redirect the user agent.
    * @return void
    */
    
    function Cancel($redirect) {
        $this->ExitEvent(2, $redirect);
    }
    
    /**
    * @deprecated Use Uplaoder class
    */
    
    function UploadFile($file, $dest, $allowtypes, $maxsize=5000000, $targets=array()) {
        $Uploader = new Uploader($allowtypes, $targets);
        return $Uploader->upload($file, $dest);
    }
    
    /**
    * Peforms upload and move tasks when uploading multiple files
    * via an HTML form.
    *
    * @param array - the file input value from the HTML form.
    * @param array - the destination for the new file.
    * @param array - the file types to allow to be uploaded
    * @param int   - the maximum filesize to allow to be uploaded.
    */
    
    function UploadMultipleFiles(
        $files=array(), 
        $dest=array(), 
        $allowTypes=array(), 
        $maxsize=5000000,
        $targets=array()
    ) {
        $exitCodes = array();
        
        $count = count($files['upload']['name']);
        
        for ($i=0; $i<$count; $i++) {
            $fname = $files['upload']['name'][$i];
            $ftype = $files['upload']['type'][$i];
            
            if (!in_array($ftype, $allowTypes)) {
                list($exitCodes[], $newFiles[]) = array('-1', '');
            } 
            else {
                $this->CheckTrailingSlash($dest[$i]);
                
                $file['name']     = $files['upload']['name'][$i];
                $file['type']     = $files['upload']['type'][$i];
                $file['tmp_name'] = $files['upload']['tmp_name'][$i];
                $file['error']    = $files['upload']['error'][$i];
                $file['size']     = $files['upload']['size'][$i];
                
                list($exitCodes[], $newFiles[]) = 
                  $this->UploadFile($file, $dest[$i], $allowTypes, $maxsize, $targets);
            }
        }
        return array($exitCodes, $newFiles);
    }
    
    /**
    * Adds a trailing slash to a path string when needed.
    *
    * @param string - the path to which to add the trailing slash.
    */
    
    function CheckTrailingSlash(&$path) {
        $path .= $path{strlen($path)-1} != '/' ? '/' : '' ;
    }
    
    /**
    * Nearly every class (manager) in SkyBlue uses the SetSessionMessage() function
    * to display the result of any action. This function was created to streamline
    * the code and to have a universal method for exiting an action.
    * 
    * New exitcodes (cases) can be added as long as they are 
    * added to the end of the list.
    * 
    * ExitEvent() should be called when some user action/event is completed
    * and the page needs to be redirected to some location such as the main
    * screen of a manager.
    * 
    * Examples:
    * 
    * - After saving an item
    * - After deleting an item
    * - When some required information is missing and the
    *   action/event should not be allowed to proceed.
    * - When the user Cancels an action/event
    * 
    * Cases (exit codes) 0-3 are fixed and should not be changed.
    *
    * @param int    - the exit code for the last action.
    * @param string - the URL to which to redirect the browser.
    */
    
    function ExitEvent($code, $redirect) {
        $msg = null;
        $class = null;
        if (intval($code) > 0)  {
			$code  = 'EXITCODE_'.$code;
			$msg   = $this->terms[$code]['str'];
			$class = $this->terms[$code]['class'];
        }
        $this->SetSessionMessage($msg, $class);
        $this->SBRedirect($redirect);
    }
    
    function ExitDemoEvent($redirect) {
        $this->SetSessionMessage(MSG_FEATURE_DISABLED_IN_DEMO, 'warning');
        $this->SBRedirect($redirect);
    }
    
    function ExitRestrictedEvent($redirect, $msg) {
        $this->SetSessionMessage($msg, 'warning');
        $this->SBRedirect($redirect);
    }
    
    function ExitWithWarning($msg, $redirect) {
        $this->SetSessionMessage($msg, 'warning');
        $this->SBRedirect($redirect);
    }
    
    function ExitWithErrorMessage($msg, $redirect) {
        $this->SetSessionMessage($msg, 'error');
        $this->SBRedirect($redirect);
    }
    
    function ExitWithError($msg, $redirect) {
        $_SESSION['LASTERROR'] = "<div class=\"error\"><p>$msg</p></div>\n";
        $this->SBRedirect($redirect);
    }
    
    function GetLastError() {
        if (trim($this->MSG) == '...') {
            $this->MSG = null;
        }
        if (isset($_SESSION['LASTERROR'])) {
            $this->MSG .= $_SESSION['LASTERROR'];
            unset($_SESSION['LASTERROR']);
        }
    }

	function DefineDefaultPage() {
	    $defaultPage = null;
	    $pages = $this->xmlHandler->ParserMain(SB_PAGE_FILE);
	    foreach ($pages as $p) {
	        if (intval($p->isdefault) == 1) {
	            $defaultPage = $p->id;
	        }
	    }
	    if (empty($defaultPage) && count($pages)) {
	        $defaultPage = $pages[0]->id;
	    }
	    define('DEFAULT_PAGE', $defaultPage);
	}
	
    /**
    * Builds an HTML select element for the pages created in the 
    * SkyBlue Admin area.
    *
    * @param string - the currently selected option in the select list.
    */
    
    function PageSelector($selected='') {
        global $config;
        
        $pages = array();
        if (file_exists(SB_PAGE_FILE)) {
            $pages = $this->xmlHandler->ParserMain(SB_PAGE_FILE);
        }
        $options = array();
        $options[] = $this->MakeOption(' -- Select Page -- ', '');
        foreach ($pages as $p) {
            $s = $p->id == $selected ? 1 : 0 ;
            array_push($options, $this->MakeOption($p->title, $p->id, $s));
        }
        return $this->SelectList($options, 'page');
    }
    
    /**
    * Builds an HTML select element for the files in the specified directory.
    *
    * @param string - the path to the directory for which to create a file selector.
    * @param string - the HTML input name for the selector.
    * @param string - the currently selected option in the select list
    * @param bool   - whether or not to trim any file extensions from the 
    * files in the specified directory.
    */
    
    function BuildFileSelector($dir, $selector_name, $selected='', $trimext=0, $fullpath=1) {
        $files = $this->ListFilesOptionalRecurse($dir);
        $options = array();
        $options[] = $this->MakeOption(' -- Select A File -- ', '', 0);
        for ($i=0; $i<count($files); $i++) {
            $name = basename($files[$i]);
            $name = $trimext == 1 ? $this->TrimExtension($name) : $name ;
            if (!$fullpath) {
                $files[$i] = basename($files[$i]);
            }
            $s = $files[$i] == $selected ? 1 : 0 ;
            array_push($options, $this->MakeOption($name, $files[$i], $s));
        }
        return $this->SelectList($options, $selector_name);
    }    
    
    /**
    * Validates a piece of data for being a specified type. This function
    * is useful for validating FORM fields.
    *
    * @param string - the data string to validate.
    * @param string - the type of validation to perform.
    */
    
    function ValidateField($value, $validation) {
        switch ($validation) {
            case 'notempty':
            case 'notnull':
                return trim($value) == "" ? false : true ;
                break;
            case 'number':
                return ereg (SB_REGEX_NUM, $value);
                break;
            case 'email':
                return eregi(SB_REGEX_EMAIL, $value);
                break;
            case 'url':
                return preg_match(SB_REGEX_URL, $value);
                break;
            default:
                return true;
                break;
        }
    }
    
    /**
    * Used in pagination routines and other routines that need to capture
    * a subset of a set of data.
    *
    * @param int - the offset within the data set.
    * @param int - number of items in the data set.
    * @param int - the minimum number of items to include in the subset.
    */
    
    function CalcStartOfRange($offset, $item_count, $min=0) {
        return $this->GetNumInRange(
            ($offset * $item_count) - $item_count, 
            $item_count, 
            $min
        );
    }
    
    /**
    * Verifies that the specified number is within the range of
    * the minimum and maximum range specified.
    *
    * @param int - the number to check.
    * @param int - the maximum number in the set.
    * @param int - the minimum number in the set.
    */
    
    function GetNumInRange($num, $max, $min=1) {
        if ($num >= $min) {
            $newNum = $num <= $max ? $num : $max ;        
        } 
        else {
            $newNum = $min;
        }
        return $newNum;
    }
    
    /**
    * Performs an array_slice
    *
    * @param array - the original data set.
    * @param int   - the starting index for the slice.
    * @param int   - the number of items in the data set.
    */
    
    function GetArraySubset(&$arr, $offset, $item_count) {
        return array_slice($arr, $offset, $item_count);
    }
    
    /**
    * Calculates the number of subsets needed to hold the data
    * set as determined by the maximum number of items allowed
    * in a subset.
    *
    * @param array - the original data set.
    * @param int   - the number of items in the data set.
    */
    
    function CalcNumOfPages(&$items, $items_per_page) {
        return ceil(count($items) / $items_per_page);
    }
    
    /**
    * A useful function for retrieving a variable from any array or a default 
    * value if the array index is not set. The htmlentities function is used 
    * to prevent the injection of malicious HTML, JavaScript or SQL code. 
    *
    * @param array  - the array from which to retrieve the desired value.
    * @param string - the array index of the value to return.
    * @param string - the default value to return if the index is not found.
    * @param bool   - whether or not to HTML encode the returned value.
    * @param bool   - whether or not to enforce alpha-numeric values.
    */
    
    /*
    * @deprecated  User Filter class
    */
    
    function GetVar($arr, $index, $default, $htmlfilter=1, $alphafilter=0) {
        return Filter::get($arr, $index, $default, $htmlfilter);
    }
    
    /**
    * Performs the actual HTML encoding in the GetVar() function. 
    *
    * @param mixed - datum to HTML encode. Can be a string or an array.
    * If an array, all values in the array will be encoded.
    */
    
    /*
    * @deprecated  User Filter class
    */

    function SafeValue($var) {
        if (is_array($var)) {
            $safe = array();
            foreach ($var as $k=>$v) {
                $safe[$k] = trim(strip_tags($v));
            }
        } 
        else {
            $safe = trim(strip_tags($var));
        }
        return $safe;
    }
    
    /**
    * Verifies that a value is comprised only of alpha-numeric characters. 
    *
    * @param string - the text shred to check for alpha-numeracy.
    */
    
    function AlphaNumericFilter($value) {
        return ctype_alnum($value);
    }
    
    function stripslashes_deep($value) {
       $value = is_array($value) 
           ? array_map('stripslashes_deep', $value) 
           : stripslashes($value);
       return $value;
    }

    /**
    *  SBRedirect redirects the browser to a page
    *  specified by the $url argument.
    *
    * @param string - the URL to which to redirect the browser.
    */
    
    function SBRedirect($url) {
        if (headers_sent()) {
            echo "<script>document.location.href='".$url."';</script>\n";
        } 
        else {
            header("Location: $url");
        }
        exit(0);
    }

    /**
    * GetLink returns the HREF URL in the proper format depending on whether or not
    * USE_SEF_URLS is set to true or not.
    *
    * @param string $title  The text for the SEF_URL
    * @param int    $PageID The id of the page to display the object.
    * @param int    $ObjID  The id of the individual object to be displayed.
    */
    
    function SafeURLFormat($str) {
        for ($i=0; $i<strlen($str); $i++) {
            if (strpos(SB_SAFE_URL_CHARS, $str{$i}) === false) {
                $str{$i} = '-';
            }
        }
        return $str;
    }
    
    /*
    * @Deprecated  Use Router::GetLink()
    */
    
	function GetLink($title, $PageID, $ObjID, $useFullURL=0) {
        if (defined('USE_SEF_URLS') && USE_SEF_URLS == 1) {
            $search = array('[amp]', '&amp;', '&');
            $replace = array('-and-');
            $title = str_replace($search, $replace, $title);
            $title = str_replace(' - ', '-', $title);
            $title = $this->SafeURLFormat($title);
            $link  = $title.'-pg-'.$PageID.(!empty($ObjID)?'-'.$ObjID:null).'.htm';
        } 
        else {
            $link = 'index.php?pid=' . $PageID . (!empty($ObjID) ? '&amp;show='.$ObjID : null);
        }
        return ($useFullURL ? FULL_URL . $link : $link );
    }
    
    /**
    * initialize a session
    * @param int - the session lifetime in seconds.
    */
    
    function InitSession($lifetime=1800) {
        session_id();
        session_set_cookie_params($lifetime);
        session_start();
        
        if (!isset($_SESSION['MSG'])) {
            $_SESSION['MSG']   = '...';
        } 
        else {
            $this->MSG = $_SESSION['MSG'];
        }
        if ($this->MSG != '...') {
            $_SESSION['MSG']   = '...';
        }
    }
    
    /**
    * This function is used to set a message for the result of a user action
    * in a SkyBlue Admin Component.
    *
    * @param string - the message to store in the $_SESSION for use
    * on the subsequent page.
    * @param string - the type of message. The message type should correspond
    * to a CSS selector class name.
    */
    
    function SetSessionMessage($msg, $type='confirm') {
		$class = 'generic';
		$heading = null;
        switch ($type) {
            case 'error':
				$class   = 'error';
				$heading = 'Error!';
                break;
            case 'confirm':
				$class   = 'success';
				$heading = 'Success!';
                break;
            case 'warning':
				$class   = 'warning';
				$heading = 'Warning!';
                break;
            case 'info':
				$class   = 'info';
				$heading = 'Note:';
                break;
            default:
                break;
        }
		$_SESSION['MSG'] = 
		"<div class=\"msg-$class\">\n" . 
		"    <h2>$heading</h2>\n" . 
		"    <p>$msg</p>\n" . 
		"</div>\n";
    }
    
    /**
    * validates the admin request
    * 
    * @param string - the name of the requested action.
    */
    
    function ValidateRequest($task, $return=false) {
        $username = null;
        $objs = $this->xmlHandler->ParserMain(SB_LOGIN_FILE);
        $obj = count($objs) ? $objs[0] : null ;
        if (!empty($obj)) {
            $username = isset($obj->username) ? $obj->username : null ;
        }
        
        if ($task != 'login') {
            if (isset($_SESSION)) {
                if (empty($username)) {
                    if (!$return) {
                        $this->ForceLogin();
                    }
                    else {
                        return false;
                    }
                } else if (!isset($_SESSION['USERNAME']) || $username != $_SESSION['USERNAME']) {
                    if (!$return) {
                        $this->ForceLogin();
                    }
                    else {
                        return false;
                    }
                }
                return true;
            }
            return false;
        }
    }
    
    /**
    * This function clears the $_SESSION array and redirects the
    * user to the login page. This function is only used by the Admin
    * section of SkyBlue.
    */
    
    function ForceLogin() {
        $_SESSION = array();
        $this->SBRedirect(BASE_PAGE);
    }
    
    /**
    * Loads the the Admin Component.
    * 
    * @param string - the name of the component to load.
    */
    
    function LoadContent($mgr) {
        global $config;

        if ($this->ValidatePath(SB_MANAGERS_DIR . "{$mgr}/{$mgr}.class.php")) {
            include(SB_MANAGERS_DIR . "{$mgr}/{$mgr}.class.php");
            $component = new $mgr;
        }
    }
    
    /**
    * Loads an admin module.
    */
    
    function LoadModuleAdmin2($mod) {
        if ($this->ValidatePath(SB_INC_DIR.'mod.'.$mod.'.php')) {
            include(SB_INC_DIR.'mod.'.$mod.'.php');
        }
    }
    
    
    /* ============================================= */
       // FILE, DIRECTORY & PATH HANDLING FUNCTIONS
    /* ============================================= */
    
    /**
    * Allows component and module code to capture the output of executable 
    * or HTML code in a temporary buffer so the text can be pre-processed
    * or use asynchronously.
    *
    * @param string - the path to the file to buffer.
    */
    
    /*
    * @deprecated Use FileSystem class
    */
    
    function OutputBuffer($input) {
        return FileSystem::buffer($input);
    }
    
    /**
    * A single parameter function for reading the contents of a file.
    *
    * @param string - the path to the file to read.
    */
    
    /*
    * @deprecated Use FileSystem class
    */
    
    function SBReadFile($file) {
        return FileSystem::read_file($file);
    }
    
    /**
    * Writes data to a file.
    *
    * @param string - the path to the file to write.
    * @param string - the data to write to the file.
    * @param bool   - whether or not to append the data to the file.
    */
    
    /*
    * @deprecated Use FileSystem class
    */
    
    function WriteFile($file, $str, $append=0) {
        return FileSystem::write_file($file, $str, 'w+');
    }

    /**
    * Creates the data source if it does not already exist.
    *
    * @param string $src  The file path for the data source
    * @param array  $objs An array of the data objects to be saved
    * @param string $type The name of the object type being saved
    * @return int The boolean integer result of the data source creation
    */
    
    function InitDataSource($src, $objs, $type) {
        if (!file_exists($src)) {
            $xml = $this->xmlHandler->ObjsToXML($objs, $type);
            return $this->WriteFile($src, $xml, 1);
        }
        return true;
    }
        
    /**
    * Moves or renames a file.
    *
    * @param string - the existing path to the file.
    * @param string - the nex path to the file.
    */
    
    /*
    * @deprecated Use FileSystem class
    */
    
    function MoveFile($from, $to) {
        FileSystem::move_file($from, $to);
    }
    
    /**
    * This functio strips the file extension from a file name or path.
    * 
    * @param string - the name or path of the file.
    */
    
    function TrimExtension($file) {
        $file_arr = explode('.', $file);
        return $file_arr[0];
    }
    
    /**
    * Returns the file extension of a file (an example of bad function
    * naming. It should be 'getFileExtension()'.
    *
    * @param string - the name or path of the file.
    */
    
    function GetImageExtension($file) {
        $file_arr = explode('.', $file);
        if (count($file_arr) > 1) {
            return $file_arr[(count($file_arr)-1)];
        }
        return null;
    }
    
    /**
    * Returns the name of a sub-directory withing a path specified
    * by the position of the sub-dir within the path. The positions begin
    * at zero (same as arrays).
    *
    * @param string - the full path.
    * @param int    - the offset of the desired sub-directory within the path.
    */

    function SubDirFromPath($path, $index) {
        $dirs = explode('/', $path);
        
        if ($dirs[count($dirs)-1] == '') {
            $dirs = array_slice($dirs, 0, count($dirs)-1);
        }
        
        if ($index == 'last') {
            $index = count($dirs)-1;
            return $dirs[$index];
        }
        if ($index == 'first') {
            return $dirs[0];
        }
        $index--;
        return $dirs[$index];
    }
    
    /**
    * Lists all of the files within a directory tree. This function is
    * recursive so you will get ALL of the files within the directory tree.
    * Files are returned in alphabetical order but the sorting is performed
    * on the full path so the names of directories will affect the sort order.
    *
    * @param string - the top-level directory to read.
    * @param array  - an array of file paths. Typically left null when called.
    */
    
    /*
    * @deprecated Use FileSystem class
    */

    function ListFiles($dir, $files=array()) {
        return $this->ListFilesOptionalRecurse($dir, 1, $files);
    }
    
    /**
    * Lists all of the files within a directory tree. This function can be
    * optionally recursive or not. If you need to list the files in a single
    * directory, use this function with argument 2 set to 0.
    *
    * @param string - the top-level directory to read.
    * @param bool   - whether or not to recurse the directory tree of dir.
    * @param array  - an array of file paths. Typically left null when called.
    */
    
    /*
    * @deprecated Use FileSystem class
    */
    
    function ListFilesOptionalRecurse($dir, $recurse=1, $files=array()) {
        return FileSystem::list_files($dir, $recurse, $files);
    }
    
    /**
    * Lists all of the directories within a directory tree. This function is
    * recursive so it lists all child directories of the top-level node (dir).
    *
    * @param string - the top-level directory to read.
    * @param array  - an array of directory paths. Typically left null when called.
    */
    
    /*
    * @deprecated Use FileSystem class
    */
    
    function ListDirs($dir, $dirs=array()) {
        return $this->ListDirsOptionalRecurse($dir, 1, $dirs);
    }
    
    /**
    * Lists all of the directories within a directory tree. This function is
    * optionally recursive. To list the sub-directories of only the first level
    * of the specified top-level directory, set argument 2 to 0.
    *
    * @param string - the top-level directory to read.
    * @param bool   - whether or not to recurse the directory tree.
    * @param array  - an array of directory paths. Typically left null when called.
    */
    
    /*
    * @deprecated Use FileSystem class
    */
    
    function ListDirsOptionalRecurse($dir, $recurse=1, $dirs=array()) {
        return FileSystem::list_dirs($dir, $recurse, $dirs);
    }
    
    /*
    * @deprecated Use FileSystem class
    */
    
    function InitDir($dir) {
        if (is_dir($dir)) return true;
        return FileSystem::make_dir($dir);
    }
    
    /*
    * @deprecated Use FileSystem class
    */
    
    function NukeDir($dir) {
        return FileSystem::delete_dir($dir);
    }
    
    function ExecNukeDir($dir, $ContentsOnly=1) {
		return FileSystem::delete_dir($dir, $ContentsOnly);
    }
    
    function CopyDir($from, $to) {
        return FileSystem::copy_dir($from, $to);
    }
    
    function ExecCopyDir($from, $to) {
        return FileSystem::copy_dir($from, $to);
    }
    
    // Unzip() will unpack a zip archive located in $file. Be sure to 
    // move the archive to the location in which it is to be unpacked.
    
    function Unzip($file, $destination) {
        if (!file_exists($file)) {
            return false;
        }

        if (class_exists("ZipArchive"))
        {
            $zip = new ZipArchive;
            if ($zip->open($file) === true)
            {
                $zip->extractTo($destination);
                $zip->close();
                return true;
            }
        }

        $disabled = ini_get('disabled_functions');
        $disarr = explode(',', $disabled);
        if (!in_array('exec', $disarr))
        {
            exec(BIN_UNZIP.' -d'.$destination.' '.$file, $res);
            return $res;
        }

        return false;
    }
        
    /**
    * Lists all of the directories within a directory tree down to a specified
    * depth.
    *
    * @param string - the top-level directory to read.
    * @param bool   - whether or not to recurse the directory tree.
    * @param int    - the depth to which to recurse the directory tree.
    * @param array  - an array of directory paths. Typically left null when called.
    */
    
    function ListDirsToLevel($dir, $dirs=array(), $depth='', $lvl=0) {
        $lvl++;
        ini_set('max_execution_time', 10);
        if (!is_dir($dir)) {
            die ('No such directory.');
        }
        if ($root = @opendir($dir)) {
            while ($file = readdir($root)) {
                if ($file{0} == '.') {
                    continue;
                }
                if (is_dir($dir.$file)) {
                    $dirs[] = $dir.$file.'/';
                    if ($depth > 1 && $depth < $lvl) {
                        $dirs = array_merge(
                            $dirs, 
                            $this->ListDirs($dir.$file.'/', $depth, $lvl)
                        );
                    }
                } 
                else {
                    continue;
                }
            }
        }
        return $dirs;
    }
    
    /**
    *
    * Transforms a flat array into COLS X ROWS. 
    * This is very useful for building
    * tables of specified COLS X ROWS sizes.
    *
    * For example, if you have an array of 8 items that you
    * want returned as a 3 X 3 matrix, pass the array and 
    * $colcount = 3.
    *
    * Example:
    *
    * <pre>
    * $arr = array(0, 1, 2, 3, 4, 5, 6, 7);
    * ArrayToMatrix($arr, 3);
    *
    * Returns:
    *
    * array(
    *    [0]=> array(0, 1, 2)
    *    [1]=> array(3, 4, 5)
    *    [3]=> array(6, 7, 8)
    *  )
    *
    * Which is an array representation of:
    *
    *  0 | 1 | 2
    * -----------
    *  3 | 4 | 5
    * -----------
    *  6 | 7 | 8
    *
    * If the array is smaller than the matrix size, 
    * the remaining cells will be filled with null (n) values.
    *
    * Example 7 items in a 3 X 3 matrix:
    *
    * $arr = array(0, 1, 2, 3, 4, 5, 6);
    * ArrayToMatrix($arr, 3);
    * Returns:
    *
    * array(
    *    [0]=> array(0, 1, 2)
    *    [1]=> array(3, 4, 5)
    *    [3]=> array(6, , )
    *  )
    *
    * Which is an array representation of:
    *
    *  0 | 1 | 2
    * -----------
    *  3 | 4 | 5
    * -----------
    *  6 |   |  
    * </pre>
    *
    * @param array   $arr the flat array to convert to a multi-dim array.
    * @param integer $colcount the number of columns in the matrix.
    * @return array
    */
    
    function ArrayToMatrix($arr, $colcount=3) {
        $matrix = array();
        $rowcount = ceil(count($arr)/$colcount);
        for ($i=0, $offset=0; $i<$rowcount; $i++, $offset+=$colcount) {
            for ($j=$offset; $j<($offset+$colcount); $j++) {
                $matrix[$i][$j] = isset($arr[$j]) ? $arr[$j] : null ;
            }
        }
       return $matrix;
    }
    
    /*
    * The difference between ArrayToGrid() and ArrayToMatrix() is that
    * ArrayToGrid() resests the cell indices at the beginning of each column, 
    * whereas ArrayToMatrix() numbers the cells sequentially regardless of the
    * column in which they fall.
    * 
    * Examples:
    *
    * ArrayToGrid()
    *
    *  0 | 1 | 2
    * -----------
    *  0 | 1 | 2
    * -----------
    *  0 | 1 | 2
    *
    *
    * ArrayToMatrix()
    *
    *  0 | 1 | 2
    * -----------
    *  3 | 4 | 5
    * -----------
    *  6 | 7 | 8
    *
    */
    
    function ArrayToGrid($arr, $colcount=3) {
        $grid = array();
        $rowcount = ceil(count($arr)/$colcount);
        $trueindex = 0;
        for ($i=0; $i<$rowcount; $i++) {
            for ($j=0; $j<$colcount; $j++) {
                $grid[$i][$j] = isset($arr[$trueindex]) ? $arr[$trueindex] : null ;
                $trueindex++;
            }
        }
       return $grid;
    }
    
    /**
    * Counts the number of objects in an array that have some property
    * value matching a test string.
    *
    * @param array - the array of objects to test.
    * @param bool  - the property to search on.
    * @param int   - the value to search for.
    */
    
    function CountObjs($objs, $key, $value) {
        $x=0;
        foreach ($objs as $obj) {
            if ($obj->$key == $value) {
                $x++;
            }
        }
        return $x;
    }
    
    /**
    * Binds an associative array of data to an object where the names 
    * of the object properties match the names of the array indices.
    *
    * Exmaple: Binding the values of a POSTed form to an object.
    * 
    * @param object - the object to which to bind the array.
    * @param array  - the associative array to bind to the object.
    */
    
    function ArrayToObj($obj, $arr) {
        foreach ($arr as $k=>$v) {
            if ($k == 'submit') continue;
            $obj->$k = is_array($v) ? implode(', ', $v) : $v ;
        }    
        return $obj;
    }
    
    /**
    * Updates the property values of an object with the values of
    * an associateve array. If no new values are passed in the array, 
    * the existing object properties will be maintained.
    *
    * @param object - the object to which to bind the array.
    * @param array  - the associative array to bind to the object.
    */
    
    function UpdateObjFromArray($obj, $arr) {
        $obj2 = new stdClass;
        foreach ($obj as $k=>$v) {
            if (trim($k) != '') {
                $obj2->$k = $v;
            }
        }
        foreach($arr as $k=>$v) {
            if (trim($k) != '') {
                $obj2->$k = $v;
            }
        }
        foreach($obj2 as $k=>$v) {
            if (!isset($arr[$k])) {
                unset($obj2->$k);
            }
        }
        return $obj2;
    }
    
    /**
    * Select an object by the 'id' property from an array of objects.
    *
    * @param array - the array of objects from which to select.
    * @param int   - the id of the object to select.
    */
    
    function SelectObj($objs, $id) {
        if (count($objs) < 1) {
            return false;
        }
        foreach ($objs as $obj) {
            if ($obj->id == $id) {
                return $obj;
            }
        }
        return false;
    }
    
    /**
    * Select an object by a specified property from an array of objects.
    *
    * @param array  - the array of objects from which to select.
    * @param string - the name of the property to search on.
    * @param string - the value to search for.
    */
    
    function SelectObjByKey($objs, $key, $match) {
        foreach ($objs as $obj) {
            if ($obj->$key == $match) {
                return $obj;
            }
        }
        return false;
    }
    
    /**
    * Selects all objects from an array where a named property
    * matches a specified value.
    *
    * @param array  - the array of objects.
    * @param string - the name of the property to search on.
    * @param string - the value to search for.
    */
    
    function SelectObjsByKey($objs, $key, $match) {
        $rows = array();
        foreach ($objs as $obj) {
            if ($obj->$key == $match) {
                array_push($rows, $obj);
            }
        }
        return $rows;
    }
    
    /**
    * Inserts an object into an array by matching a named property
    * with a specified value.
    *
    * @param object - the object to insert.
    * @param array  - the array of objects.
    * @param string - the name of the property to search on.
    * @param string - the value to search for.
    */
    
    function InsertObjByKey($obj, $objs, $key, $match) {
        for ($i=0; $i<count($objs); $i++) {
            if ($objs[$i]->$key == $match) {
                $objs[$i] = $obj;
            }
        }
        return $objs;
    }
    
    /**
    * purpose:   To select an item from within an array of objects or arrays
    *            where the desired object may be stored in a property/key of 
    *            some other object/array.
    *
    * Note:      This function will work with an array of objects or
    *            An array of associative arrays. Each associative array
    *            is converted to an object on the fly and is converted back
    *            to an array so the return type matches the input type.
    *
    * Warning:   This function will NOT work on an array of scalar arrays.
    *
    * example:
    *
    * $item = SelectObjFromTree($myObjs, 'id', 2, 'children');
    *
    * Array
    * (
    *     [0] => stdClass Object
    *         (
    *             [id] => 1
    *             [title] => Parent Item 1
    *             [parent] => 
    *             [children] => Array
    *                 (
    *                     [0] => stdClass Object
    *                         (
    *                             [id] => 2
    *                             [title] => Child Item 1
    *                             [parent] => 1
    *                       )
    * 
    *               )
    * 
    *       )
    * 
    *     [1] => stdClass Object
    *         (
    *             [id] => 3
    *             [title] => Parent Item 2
    *             [parent] => 
    *       )
    * 
    *     [2] => stdClass Object
    *         (
    *             [id] => 4
    *             [title] => Parent Item 3
    *             [parent] => 
    *       )
    * 
    *)    
    *
    * The example above will return $objs[0]->children[0].
    *
    * @param array  - the array of objects.
    * @param string - the object property to search on.
    * @param string - the value for which to search.
    * @param string - the name of the property potentially holding the
    * nested objects.
    */
    
    function SelectItemFromTree($objs, $key, $match, $children) {
        $returnType = 'object';
        for ($i=0; $i<count($objs); $i++) {
            $parent = $objs[$i];
            
            if (is_array($parent)) {
                $returnType = 'array';
                $parent = (object) $parent;
            }
            if ($parent->$key == $match) {
                if ($returnType == 'array') {
                    $parent = (array) $parent;
                }
                return $parent;
            } 
            else {
                if (isset($parent->$children)) {
                    foreach ($parent->$children as $child) {
                        if (is_array($child)) {
                            $returnType = 'array';
                            $child = (object) $child;
                        }
                        if (isset($child->$key) && 
                             !empty($child->$key) && 
                             $child->$key == $match)
                        {
                            if ($returnType == 'array') {
                                $child = (array) $child;
                            }
                            return $child;
                        }
                    }
                }
            }
        }
        return false;
    }
    
    /**
    * Tests whether or not an object with a named property matching
    * a specified value is in an array. Similar to PHP's in_arry().
    *
    * @param object - the object to search for.
    * @param array  - the array of objects.
    * @param string - the name of the property to match on.
    */
    
    function ObjInArray($obj, $array, $key) {
        foreach ($array as $a) {
            if ($a->$key == $obj->$key) {
                return true;
            }
        }
        return false;
    }
    
    /**
    * Returns the property of a parent object matching the search string.
    *
    * @param array  - the array of objects to search.
    * @param object - the object of whose parent the property is needed.
    * @param string - the name of the property to return.
    * @param string - the default value to return if no match is found.
    */
    
    function GetParentProperty($objs, $obj, $key, $default) {
        $property = $default;
        if (isset($obj->$key) && 
             $obj->$key != 'null' && 
             trim($obj->$key) != '')
        {
            $pid       = $obj->parent;
            $parentObj = $this->SelectObj($objs, $pid);
            $property  = $parentObj->$key;
        }
        return $property;
    }
    
    /**
    * Returns all objects from an array whose named property
    * matches the search string.
    *
    * @param array  - the array of objects to search.
    * @param string - the name of the property to search on.
    * @param string - the value to search for.
    */
    
    function SelectObjs($objs, $k, $v) {
        $v = strToLower($v);
        $matches = array();
        foreach($objs as $obj) {
            if (isset($obj->$k) && strToLower($obj->$k) == $v) {
                $matches[] = $obj;
            }
        }
        return $matches;
    }
    
    /**
    * Inserts an object into an array where the object property
    * matches the search property. If no match is found, the object
    * is inserted at the end of the array.
    *
    * @param array  - the array of objects to search.
    * @param object - the object to be inserted.
    * @param string - the object property to match on.
    */
    
    function InsertObj($objs, $obj, $match) {
        $marker = 0;
        for ($i=0; $i<count($objs); $i++) {
            if ($objs[$i]->$match == $obj->$match) {
                $objs[$i] = $obj;
                $marker = 1;    
            }
        }
        if ($marker === 0) {
            array_push($objs, $obj);
        }
        return $objs;
    }
    
    /**
    * Inserts an object into an array at a specified position.
    *
    * @param array  - the array of objects to search.
    * @param int    - the id of the object to be re-positioned.
    * @param int    - the position to which to move the object.
    */
    
    function OrderObjs($objs, $id, $index) {
        
        $last = count($objs) - 1;
        $index--;
    
        $obj = $this->SelectObj($objs, $id);
        $objs = $this->DeleteObj($objs, $id);
        $neworder = array();
        
        switch ($index) {
            case 0:
                array_push($neworder, $obj);
                $objs = array_merge($neworder, $objs);
                break;
            case $last:
                array_push($objs, $obj);
                break;
            default:
                $before = array_slice($objs, 0, $index);
                $after = array_slice($objs, $index);
                array_push($before, $obj);
                $objs = array_merge($before, $after);
                break;
        }
        
        return $objs;
    }
    
    /**
    * Deletes an object from an array.
    *
    * @param array  - the array of objects.
    * @param int    - the id of the object to be deleted.
    */
    
    function DeleteObj($objs, $id) {
        $newObjs = array();
        for ($i=0; $i<count($objs); $i++) {
            if ($objs[$i]->id != $id) {
               array_push($newObjs, $objs[$i]);
            }
        }
        return $newObjs;
    }
    
    /**
    * Replaces needle in haystack. The difference between this function
    * and PHP's str_replace() is that this function will replace needle
    * in an array of haystacks.
    *
    * @param mixed  - the subject of the replacement.
    * @param string - the string to be replaced.
    * @param string - the string with which to replace the needle.
    */
    
    function SBStrReplace($haystack, $needle, $replace) {
        if (is_array($haystack)) {
            for ($i=0; $i<count($haystack); $i++) {
                $pile = $haystack[$i];
                if (strpos($pile, $needle) !== false) {
                    $pile = str_replace($needle, $replace, $pile);
                    $haystack[$i] = $pile;
                }
            }
        } 
        else {
            $haystack = str_replace($needle, $replace, $haystack);
        }
        return $haystack;
    }
    
    /**
    * This function determines which of the width or height is larger, 
    * then determines the scale ratio and scales the image values so that
    * the larger of the image dimensions does not exceed the maximum
    * desired dimension.
    *
    * @param array - an array of current array(width, height) values of the image.
    * @param int   - the maximum width of the image.
    * @param int   - the maximum height of the image.
    */
    
    function ImageDimsToMaxDim($dims, $maxwidth, $maxheight) {
    
        $width    = $dims[0];
        $height    = $dims[1];
        
        $widthratio = 1;
        if ($width > $maxwidth) {
            $widthratio = $maxwidth/$width;
        }
        
        $heightratio = 1;
        if ($height > $maxheight) {
            $heightratio = $maxheight/$height;
        }
        
        $ratio = $heightratio;
        if ($widthratio < $heightratio) {
            $ratio = $widthratio;
        }
        
        // Scale the images
        
        $width     = ceil($width * $ratio);
        $height     = ceil($height * $ratio);
        
        // Let's tweak the scale so the new dims match the max dims exactly
        // If the ratio == 1, no need
        
        if ($ratio == $heightratio && $ratio != 1) {
            if ($height < $maxheight) {
                while ($height < $maxheight) {
                    $ratio = $ratio * 1.01;
                    $height = ceil($height * $ratio);
                }
            }
        }
        
        if ($ratio == $widthratio && $ratio != 1) {
            if ($width < $maxwidth) {
                while ($width < $maxwidth) {
                    $ratio = $ratio * 1.01;
                    $width = ceil($width * $ratio);
                }
            }
        }

        return array($width, $height);
    }
    
    /**
    * Returns the width and height of an image in the format:
    * array(width, height).
    *
    * @param string - the path to the image.
    */
    
    function ImageDims($fp) {
        if (!file_exists($fp) || is_dir($fp)) {
            return array(0, 0);
        }
        return getimagesize($fp);
    }
    
    /**
    * Returns the width of an image.
    *
    * @param string - the path to the image.
    */
    
    function ImageWidth($fp) {
        $dims = $this->ImageDims($fp);
        return $dims[0];
    }
    
    /**
    * Returns the height of an image.
    *
    * @param string - the path to the image.
    */
    
    function ImageHeight($fp) {
        $dims = $this->ImageDims($fp);
        return $dims[1];
    }
    
    function RadioOption($name, $value, $label, $checked=0) {
        $option = '<input type="radio" name="'.$name.'" ';
        $option .= 'value="'.$value.'" ';
        if ($checked) {
            $option .= ' checked="checked" ';
        }
        $option .= '/>&nbsp;'.$label;
        return $option;
    }
    
    function RadioSelector($options) {
        if (!isset($options) || empty($options)) {
            die('Core Says: No radio options given in RadioSelector()');
        }
        return implode("\r\n", $options);
    }

    /**
    * Makes an <option> element for an HTML select list.
    *
    * @param string - the innerHTML of the option element.
    * @param string - form input value of the option element.
    * @param string - the currently selected option.
    */

    function MakeOption($title, $value, $selected='') {
        $sel = '';
        if ($selected == 1) {
            $sel = ' selected="selected"';
        }
        return str_repeat(' ', 8) .
            '<option value="'.$value.'"'.$sel.'>'.$title.'</option>';
    }
    
    function SelectorOptions($opts, $selected=null) {
        $res = array();
        foreach ($opts as $k=>$v) {
            $s = $k == $selected ? ' selected="selected"' : null ;
            array_push($res, '<option value="'.$k.'"'.$s.'>'.$v.'</option>');
        }
        return $res;
    }
    
    /**
    * Makes an <option> group element for an HTML select list.
    *
    * @param array  - an array of option elements.
    * @param string - the label for the option group.
    */
    
    function MakeOptionGroup($options, $label) {
        $html = str_repeat(' ', 4).'<optgroup label="'.$label.'">'."\r\n";
        for ($i=0; $i<count($options); $i++) {
            $html .= $options[$i];
        }
        $html .= str_repeat(' ', 4).'</optgroup>';
        return $html;
    }

    function Selector($name, $keyValuePairs, $selected=null) {
		$html = "<select name=\"$name\">\n";
		for ($i=0; $i<count($keyValuePairs); $i++) {
		    $value = $keyValuePairs[$i]['value'];
			$text  = $keyValuePairs[$i]['text'];
		    $s = $value == $selected ? " selected=\"selected\"" : null ;
		    $html .= "<option value=\"$value\"$s>$text</option>\n";
		}
		$html .= "</select>\n";
		return $html;
    }
    
    /**
    * Makes an HTML select list
    *
    * @param array  - an array of option elements.
    * @param string - the name value of the select list.
    * @param int    - the number of visible options (size) of the select list.
    * @param string - optional JavaScript code for the select list.
    */
    
    function SelectList($arr, $name, $size=1, $js='') {
        $html = '<select name="'.$name.'" size="'.$size.'" '.$js.'>'."\r\n";
        for ($i=0; $i<count($arr); $i++) {
            $html .= $arr[$i]."\r\n";
        }
        $html .= '</select>'."\r\n";
        return $html;
    }
    
    /**
    * Creates an HTML select list with Yes and No values.
    *
    * @param string - the name value of the select list.
    * @param int    - the selected option.
    */
    
    function YesNoList($name, $selected=1) {
        $options = array();
        $selected = intval($selected);
        $s = $selected == 1 ? $s = 1 : 0 ;
        array_push($options, $this->MakeOption('Yes', 1, $s));
        $s = $selected == 0 ? 1 : 0 ;
        array_push($options, $this->MakeOption('No', 0, $s));
        $selector = $this->SelectList($options, $name);
        return $selector;
    }
    
    /**
    * Creates an HTML select list with AM and PM values.
    *
    * @param string - the name value of the select list.
    * @param string - the selected option.
    */
    
    function MeridianSelector($meridian='') {
        $opts = array();
        $s = $meridian == 'AM' ? 1 : 0 ;
        array_push($opts, $this->MakeOption('AM', 'AM', $s));
        $s = $meridian == 'PM' ? 1 : 0 ;
        array_push($opts, $this->MakeOption('PM', 'PM', $s));
        $merSelector = $this->SelectList($opts, 'meridian');
        return $merSelector;
    }
    
    /**
    * Returns an array of tokens of form {token} found within a 
    * text blob (for instance a skin).
    *
    * @param string - the text blob to search for tokens.
    */
    
    function GetTokenList($str) {
          preg_match_all(
              SB_REGEX_TOKEN, $str, $tokens, PREG_SPLIT_DELIM_CAPTURE
          );
          $result = array();
          for ($i=0; $i<count($tokens); $i++) {
              if (!in_array($tokens[$i][0], $result)) {
                  array_push($result, $tokens[$i][0]);
              }
          }
          return $result;
    }
    
    /**
    * NOTE: This function will likely be moved to the Skin class in 
    * a future version of SkyBlue.
    *
    * Returns an array of region tokens of form {region:token} found within a 
    * SkyBlue skin.
    *
    * @param string - the skin to search for tokens.
    */
    
    function GetPageRegions($str) {
          preg_match_all(
              SB_REGEX_REGION_TOKEN, $str, $tokens, PREG_SPLIT_DELIM_CAPTURE
          );
          $result = array();
          for ($i=0; $i<count($tokens); $i++) {
              if (!in_array($tokens[$i][0], $result)) {
                  array_push($result, $tokens[$i][0]);
              }
          }
          return $result;
    }
    
    function RegexGetTokens($pattern, $str) {
          preg_match_all(
              $pattern, $str, $tokens, PREG_SPLIT_DELIM_CAPTURE
          );
          $result = array();
          for ($i=0; $i<count($tokens); $i++) {
              if (!in_array($tokens[$i][0], $result)) {
                  array_push($result, $tokens[$i][0]);
              }
          }
          return $result;
    }
    
    /**
    * Adds localization terms to site elements such as form field labels.
    *
    * @param ref - a reference to the skin to Localize
    */
    
    function Localize(&$skin) {
        $tokens = $this->RegexGetTokens("/[TERM:[a-zA-Z0-9]+]/", $skin);
        foreach($tokens as $k=>$v) {
            $term = str_replace('[TERM:', '', $v);
            $term = str_replace(']', '', $term);
            $skin = str_replace($v, $this->terms[$term], $skin);
        }
    }
    
    /**
    * Creates an HTML selector for skin tokens. This function parses the skin
    * to find all the tokens, then creates teh HTML select element.
    *
    * @param string - the skin for which to create the token list.
    * @param string - the name of the HTML select element.
    * @param string - the currently selected token.
    */
        
    function MakeTokenSelector($skin, $name, $selected='') {
        $skinoutput = $this->SBReadFile($skin);
        $tokens     = $this->GetTokenList($skinoutput);
        $list       = array();
        array_push($list, $this->MakeOption(' -- Select Token -- ', ''));
        for ($i=0; $i<count($tokens); $i++) {
            $s = $selected == $tokens[$i] ? 1 : 0 ;
            array_push($list, $this->MakeOption($tokens[$i], $tokens[$i], $s));
        }
        return $this->SelectList($list, $name);
    }
    
    /**
    * DEPRECATED! DO NOT USE!
    * 
    * Creates an HTML selector for ordering objects in an array.
    *
    * @param array  - the array of objects for the selector.
    * @param string - the obj->title property to omit from the selector.
    */
    
    function OrderSelector($objs, $omit) {
        $selector     = '';
        if (count($objs) > 1) {
            $options = array();
            array_push($options, $this->MakeOption(' -- Select Order -- ', '', 0));
            array_push($options, $this->MakeOption('1 - First', 1, 0));
            $ticker = 0;
            foreach ($objs as $obj) {
                if ($omit != $obj->title) {
                    array_push(
                        $options,
                        $this->MakeOption(
                            ($ticker + 2).' - '.$obj->title, 
                            ($ticker + 2), ''
                        )
                    );
                    $ticker++;
                }
            }
            array_push(
                $options,
                $this->MakeOption(
                    ($ticker + 2).' - Last', ($ticker + 2), 0
                )    
            );
            
            $selector  .= $this->SelectList($options, 'order');
            $selector  .= "\r\n";
        }
        else {
            $selector = NO_ITEMS_TO_ORDER_STRING;
        }
        return $selector;
    }
    
    /**
    * Creates an HTML selector for ordering objects in an array. The difference
    * between this function and OrderSelector() is that this function can
    * match the omitted object on any property.
    *
    * @param array  - the array of objects for the selector.
    * @param string - the obj->property on which to match the omitted object.
    * @param string - the value to match on the omitted object.
    */
    
    function OrderSelector2($objs, $key, $value) {
        $selector     = '';
        if (count($objs) > 1) {
            $options = array();
            array_push($options, $this->MakeOption(' -- Select Order -- ', '', 0));
            array_push($options, $this->MakeOption('1&nbsp;&nbsp;&nbsp;- First', 1, 0));
            $ticker = 0;
            foreach ($objs as $obj) {
                if ($value != $obj->$key) {
                    $pad = $ticker + 2 < 10 ? '&nbsp;&nbsp;' : null ;
                    array_push(
                        $options,
                        $this->MakeOption(
                            ($ticker + 2).$pad.' - '.$obj->$key, 
                            ($ticker + 2), ''
                        )
                    );
                    $ticker++;
                }
            }
            array_push(
                $options,
                $this->MakeOption(
                    ($ticker + 2) . ' - Last', ($ticker + 2), 0
                )
            );
            
            $selector  .= $this->SelectList($options, 'order');
            $selector  .= "\r\n";
        }
        else {
            $selector = NO_ITEMS_TO_ORDER_STRING;
        }
        return $selector;
    }
    
    /**
    * Creates an HTML selector for all images in a directory tree.
    *
    * @param string - the name of the HTML select element.
    * @param string - the path to the directory of images.
    * @param string - the currently selected image in the select list.
    * @param string - JavaScript code for selector behaviour.
    */
    
    function ImageSelector($selname, $subdir, $match='', $js='') {
        global $config;
        
        $imgs = $this->ListFiles(SB_MEDIA_DIR.$subdir);
        $options = array();
        array_push($options, $this->MakeOption(' -- Select An Image -- ', '', 0));
        for ($i=0; $i<count($imgs); $i++) {
            $name = basename($imgs[$i]);
            $path = str_replace('../', '', $imgs[$i]);
            $selected = 0;
            if ($match == $path) {
                $selected = 1;
            }
            array_push($options, $this->MakeOption($name, $path, $selected));
        }
        return $this->SelectList($options, $selname, 1, $js);
    }    
    
    /**
    * Validates a file path as being existent or non-existent.
    *
    * @param string - the path to the directory or file.
    */

    function ValidatePath($path) {
        if (file_exists($path)) {
            return true;
        } 
        return false;
    }
        
    function TrimArrayItems($arr) {
        for ($i=0; $i<count($arr); $i++) {
            $arr[$i] = trim($arr[$i]);
        }
        return $arr;
    }
    
    function OffsetInArray($arr, $match) {
        for ($i=0; $i<count($arr); $i++) {
            if ($arr[$i] == $match) {
                return $i;
            }
        }
        return -1;
    }

    /**
    * DEPRECATED! DO NOT USE!
    *
    * This function will be moved to a State Selector
    * plugin that will support localization for countries other than
    * the United States. 
    */
    
    function StateSelector($selected=null) {
	    require_once(SB_LIB_DIR . 'lib.states.php');
		$options = $this->HTML->MakeElement(
			'option', array('value'=>''), ' -- Select State -- '
		);
		foreach ($states as $value=>$text) {
			if (strlen($value) > 2) continue;
			$attrs = array('value'=>strtoupper($value));
			if (strtoupper($value) == $selected) {
				$attrs['selected'] = 'selected';
			}
			$options .= $this->HTML->MakeElement(
				'option', $attrs, ucwords($text)
			);
		}
		return $this->HTML->MakeElement(
			'select',
			array('name'=>'state'),
			$options
		);
	}
    
    function Dump($obj) {
        die('<pre>' . print_r($obj, true) . '</pre>');
    }

}

?>
