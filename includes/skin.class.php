<?php

/**
* @version        1.1 RC1 2008-11-20 21:18:00 $
* @package        SkyBlueCanvas
* @copyright      Copyright (C) 2005 - 2008 Scott Edwin Lewis. All rights reserved.
* @license        GNU/GPL, see COPYING.txt
* SkyBlueCanvas is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYING.txt for copyright notices and details.
*/

defined('SKYBLUE') or die(basename(__FILE__));

define('SKIN_FILE_NOT_FOUND',       'File: {file} was not found');
define('SKIN_PAGE_REGION_MISMATCH', 'Page-to-region map mis-match');
define('SKIN_PAGE_ID_NOT_SET',      'Page ID Not Set');

/**
* @package SkyBlue
*/

class Skin extends Observer {

    // Page properties
    
    var $pageID         = null;
    var $pageObj        = null;
    var $pageRegions    = array();
    var $html           = null;
    
    // Page Region Map
    
    var $contentMap     = array();
    
    // File Paths
    
    var $skeletonPath   = null;
    var $pageXmlPath       = null;
    var $tmplPath       = null;
    var $metaFilePath   = null;
    var $bundlePath     = null;
    
    // Directory Paths
    
    var $rsrcDirPath    = null;
    var $skinsDirPath   = null;
    var $xmlDirPath     = null;
    var $storyDirPath   = null;
    var $skinCSSPath    = null;
    
    // The un-parsed Skin File
    
    var $tmpl           = null;
    
    // Error Handling Properties
    
    var $isError        = false;
    var $errors         = array();
    
    var $buffer         = array();
    
    var $is_legacy;

    // helper classes

    var $GalleryBuilder = null;
    var $MenuBuilder    = null;
    var $BundleFactory  = null;
    
    function __construct($pageId, $xmldir=null, $debug=0) {
        $this->set_page_id($pageId);
        $this->set_xml_dir_path($xmldir);
        $this->set_skeleton_path();
        $this->set_page_xml_path();
        $this->set_skins_dir_path();
        $this->set_skins_css_path();
        $this->set_meta_file_paths();
        $this->set_bundle_file_path();
        $this->set_story_dir_path();
        $this->get_page_obj();
        
        if ($this->isLegacySkin()) {
			$this->get_page_tmpl();
			$this->parse_page_tmpl();
			$this->get_content_items();
			$this->set_page_title();
        }
        else {
			$this->get_content_items();
			$this->set_page_title();
			$this->get_page_tmpl();
			$this->parse_page_tmpl();
        }

        $this->build_page();
        $this->show_errors();
    }
    
    function isLegacySkin() {
        if (empty($this->is_legacy)) {
			$skin_file = ACTIVE_SKIN_DIR . "skin.{$this->pageObj->pagetype}.html";
			if (file_exists($skin_file)) {
				$skin = FileSystem::read_file($skin_file);
			}
            else {
                return true;
            }
			if (strpos($skin, '</html>') === false && 
				strpos($skin, '<html>') === false) {
				return true;
			}
			return false;
        }
        return $this->is_legacy;
    }
    
    function show_errors() {
        if ($this->isError) {
            $cerr  = "<h2>The following errors occurred in the Skin class</h2>\n";
            $cerr .= "<p>[file: " . __FILE__ . "]</p>\n";
            $cerr .= "<ul>\n";
            for ($i=0; $i<count($this->errors); $i++)
            {
                $cerr .= "<li>Error #{$this->errors[$i][0]} : {$this->errors[$i][0]}</li>\n";
            }
            $cerr = "</ul>\n";
            die($cerr);
        }
    }
    
    function Skin($pageId, $xmldir=null, $debug=0) {
        $this->__construct($pageId, $xmldir, $debug);
    }
    
    function set_page_id($pageId) {
        $this->pageID = $pageId;
    }
    
    function set_xml_dir_path($xmlDirPath) {
        $this->xmlDirPath = 
            empty($xmlDirPath) ? SB_XML_DIR : $xmlDirPath ;
    }
    
    function set_skeleton_path() {
        $this->skeletonPath = SB_PAGE_SKELETON_FILE;
    }
    
    function set_page_xml_path() {
        $this->pageXmlPath = SB_PAGE_FILE;
    }
    
    function set_skins_dir_path() {
        $this->skinsDirPath = ACTIVE_SKIN_DIR;
    }
    
    function get_path() {
        return ACTIVE_SKIN_DIR;
    }
    
    function set_skins_css_path() {
        $this->skinCSSPath = $this->skinsDirPath . 'css/';
    }
    
    function set_meta_file_paths() {
        $this->metaFilePath = SB_META_FILE;
        $this->metaGrpFilePath = SB_META_GRP_FILE;
    }
    
    function set_bundle_file_path() {
        $this->bundlePath = SB_BUNDLE_FILE;
    }
    
    function set_story_dir_path() {
        $this->storyDirPath = SB_STORY_DIR;
    }
    
    function get_html() {
        return $this->html;
    }
    
    function get_meta_data() {
        global $Core;
        $objs = $this->get_meta_objs();

        $keyWordObj = null;
        $keyWordObj = $Core->SelectObjByKey($objs, 'name', 'keywords');
        $descObj = $Core->SelectObjByKey($objs, 'name', 'description');
        
        foreach ($this->get_story_objects() as $story) {
            if ($story->page == $this->pageID) {
				if (isset($keyWordObj->id) && $keyWordObj->id) {
					$keyWordObj->content .= ', '.$story->keywords;
					$objs = $Core->InsertObj($objs, $keyWordObj, 'id');
				}
				else if (!empty($story->keywords)) {
					$keyWordObj = $this->make_meta_obj(
						$objs,
						'keywords',
						$story->keywords,
						''
					);
					$objs = $Core->InsertObj($objs, $keyWordObj, 'id');
				}
				$desc = null;
				if (isset($story->meta_description)) {
					$desc = $story->meta_description;
					
				}
				if (!empty($desc)) {
				    if (isset($descObj->id) && $descObj->id) {
						$objs = $Core->DeleteObj($objs, $descObj->id);
					}
					array_push(
						$objs, 
						$this->make_meta_obj(
							$objs, 
							'description', 
							$desc, 
							null
						)
					);
				}
			}
        }
    
        $BundleFactory = new BundleFactory();
        $meta = $BundleFactory->makeBundle($objs);
        return str_replace('/>', "/>\n", $BundleFactory->makeBundle($objs));
    }

    function build_page() {
        global $Core;
        
        $this->BundleFactory = new BundleFactory();
        $this->html = $this->tmpl;
        
        if ($this->isLegacySkin()) {
			foreach ($this->contentMap as $key=>$objs) {
				if ($bundle_output = $this->BundleFactory->makeBundle($objs)) {
					$this->html = str_replace(
						$key, 
						$bundle_output, 
						$this->html 
					);
				}
				$this->html = str_replace($key, null, $this->html);
			}
        }
        $this->replace_site_vars();
        $this->fix_wym_paths();
    }
    
    function fix_wym_paths() {
        $this->html = str_replace(WYM_RELATIVE_PATH, FULL_URL, $this->html);
    }

    function replace_site_vars() {
        $this->set_var(VAR_SITE_NAME,     SB_SITE_NAME);
        $this->set_var(VAR_SITE_SLOGAN,   SB_SITE_SLOGAN);
        $this->set_var(VAR_SITE_URL,      SB_MY_URL);
        $this->set_var(VAR_SITE_RSS,      SB_RSS_FEED);
        $this->set_var(VAR_SITE_XHTML,    SB_VALIDATE_XHTML);
        $this->set_var(VAR_SITE_CSS,      SB_VALIDATE_CSS);
        $this->set_var(VAR_SITE_NOSCRIPT, SB_NOSCRIPT);
        $this->set_var(VAR_SB_PROD_NAME,  SB_PROD_NAME);
        $this->set_var(VAR_SB_VERSION,    SB_VERSION);
        $this->set_var(VAR_SB_TAGLINE,    SB_TAGLINE);
        $this->set_var(VAR_SB_INFO_LINK,  SKYBLUE_INFO_LINK);
        $this->set_var('[skinclass]', $this->pageObj->pagetype);
        $this->set_var(
            '{page:bodyid}',
            ' id="'. $this->id_format($this->pageObj->name) .'"'
        );
        $this->set_var('{doc:lang}', ' lang="' . SB_LANGUAGE . '"');
        $this->set_var('{page:base_uri}', BASE_URI);
        $this->set_var('{page:title}', $this->page_title);
    }
    
    function get_home_url() {
        global $config;
        global $Filter;
        return $Filter->get($config, 'site_url', '/');
    }
    
    function get_page_title() {
        return $this->page_title;
    }
    
    function get_page_name() {
        return $this->id_format($this->pageObj->name);
    }
    
    function set_var($token, $value) {
        $this->html = str_replace($token, $value, $this->html);
    }
    
    function id_format($str) {
        $good_chars = "abcdefghijklmnopqrstuvwxyz1234567890_-";
        if (strlen($str) == 0) return null;
        $str = strtolower($str);
        for ($i=0; $i<strlen($str); $i++) {
            if (strpos($good_chars, $str{$i}) === false) {
                $str{$i} = "_";
            }
        }
        return $str;
    }
    
    function set_page_title() {
        $title = null;
        if ($this->pageObj->usesitename &&
             defined('SB_SITE_NAME'))
        {
            $title = SB_SITE_NAME.' - ';
        }
        if (!isset($this->pageObj->title) || empty($this->pageObj->title)) {
            $title .= ucwords($this->pageObj->name);
        }
        else {
            $title .= ucwords($this->pageObj->title);
        }
        $this->page_title = $title;
    }
    
    function display() {
        echo $this->html;
    }
    
    function getHtml() {
        return $this->html;
    }
    
    function get_page_obj() {
        global $Core;
        if (!empty($this->pageID)) {
            $this->pageObj = $Core->SelectObj(
                $Core->xmlHandler->ParserMain($this->pageXmlPath), 
                $this->pageID
            );
        } 
        else {
            $this->isError = 1;
            $this->add_error(2, SKIN_PAGE_ID_NOT_SET);
            return false;
        }
        if (empty($this->pageObj)) {
            // 404 Redirect
            header("HTTP/1.0 404 Not Found");
            header("Location: 404-page-not-found");
            exit(0);
        }
    }

    function get_content_items() {
        $this->get_meta_objs();
        $this->get_bundle_objs();
        $this->map_stories_to_region();
        $this->get_style_objs();
        $this->get_script_objs();
        $this->get_links_objs();
    }
    
    function clean_target($obj) {
        
        if (!isset($obj->page) || !isset($obj->region)) {
            return $obj;
        }
    
        $pages   = explode(',', $obj->page);
        $regions = explode(',', $obj->region);
        
        for ($i=0; $i<count($pages); $i++) {
            $pages[$i] = trim($pages[$i]);
            if ($pages[$i] == $this->pageID) {
                $obj->page = $pages[$i];
                if (isset($regions[$i])) {
                    $obj->region = $regions[$i];
                } 
                else {
                    $obj->region = null;
                    $this->isError = 1;
                    $this->add_error(3, SKIN_PAGE_REGION_MISMATCH);
                }
            }
        }
        return $obj;
    }
    
    function map_obj_to_region($obj, $clean=1) {
        if ($clean) $obj = $this->clean_target($obj);
        if (!empty($obj->region) && 
            isset($this->contentMap[$obj->region])) {
            if (!is_array($this->contentMap[$obj->region])) {
                $this->contentMap[$obj->region] = array();
            }
            array_push($this->contentMap[$obj->region], $obj);
        }
    }
    
    function get_links_objs() {
        global $Core;
        
        $objs = array();
        if ($favicon = $this->get_favicon()) {
             array_push($objs, $favicon);
        }
        foreach ($objs as $obj) {
            $obj->loadas = 'link';
            $this->map_obj_to_region($obj, 0);
        }
    }
    
    function get_favicon() {
        $favicon = ACTIVE_SKIN_DIR . 'images/favicon.ico';
        if (file_exists($favicon)) {
            $obj = new stdClass;
            $obj->type = 'link';
            $obj->rel = 'shortcut icon';
            $obj->href = $favicon;
            $obj->region = REGION_LINKS;
            return $obj;
        }
        return null;
    }
    
    function get_meta_objs() {
        global $Core;
        
        $metaObjs = array();
        $metaGrps = array();
        
        if (file_exists($this->metaFilePath)) {
            $metaObjs = $Core->xmlHandler->ParserMain($this->metaFilePath);
        }
        if (file_exists($this->metaGrpFilePath)) {
            $metaGrps = $Core->xmlHandler->ParserMain($this->metaGrpFilePath);
        }
        
        $objs = array();
        $myGroups = array();
        if (isset($this->pageObj->metagroup)) {
            $myGroups = explode(',', $this->pageObj->metagroup);
            for ($i=0; $i<count($myGroups); $i++) {
                foreach ($metaObjs as $mObj) {
                    $mGrps = array();
                    if (isset($mObj->metagroups) && !empty($mObj->metagroups)) {
                        $mGrps = explode(',', $mObj->metagroups);
                        for ($j=0; $j<count($mGrps); $j++) {
                            $mGrps[$j] = trim($mGrps[$j]);
                        }
                        if (in_array($myGroups[$i], $mGrps)) {
                            $check = $Core->SelectObj($objs, $mObj->id);
                            if (!isset($check->id)) {
                                $mObj->loadas = 'meta';
                                array_push($objs, $mObj);
                            }
                        }
                    }
                }
            }
        }
        
        foreach ($objs as $obj) {
            $obj->region = REGION_META;
            $this->map_obj_to_region($obj, 0);
        }
        return $objs;
    }
    
    function get_script_objs() {
        global $Core;
        global $config;
        
        $skinJS = ACTIVE_SKIN_DIR . 'js/';
        
        if (is_dir($skinJS)) {
            $objs = array();
            if (is_dir(SB_SYSTEM_JS_DIR)) {
                $objs = $Core->ListFiles(SB_SYSTEM_JS_DIR);
            }
            $objs = array_merge($objs,$Core->ListFiles($skinJS));

            $tmpljs = 'skin.' . $this->pageObj->pagetype . '.js';
            for ($i=0; $i<count($objs); $i++) {
                $bname = basename($objs[$i]);

                if ($bname == $tmpljs || 
                    !preg_match("/^skin\.[a-zA-Z0-9]+\.js$/i", $bname)) {
                    $obj = new stdClass;
                    $obj->type   = 'javascript';
                    $obj->loadas = 'javascript';
                    $obj->path   = FULL_URL . str_replace('../', null, $objs[$i]);
                    $obj->region = REGION_SCRIPTS;
                    $this->map_obj_to_region($obj, 0);
                }
            }
        }
    }

    function get_style_objs() {
        global $Core;
        $objs = $Core->ListFiles($this->skinCSSPath);

        if (is_dir(MEDIA_CSS_DIR)) {
            $objs = array_merge($objs, $Core->ListFiles(MEDIA_CSS_DIR));
        }

        $tmplcss = 'skin.' . $this->pageObj->pagetype . '.css';
        for ($i=0; $i<count($objs); $i++) {
            $bname = basename($objs[$i]);
            if ($bname == $tmplcss
                || !preg_match("/^skin\.[a-zA-Z0-9]+\.css$/i", $bname)) {
                $obj = new stdClass;
                $obj->type   = 'stylesheet';
                $obj->path   = FULL_URL . str_replace('../', null, $objs[$i]);
                $obj->region = REGION_STYLES;
                $obj->loadas = 'stylesheet';
                $this->map_obj_to_region($obj, 0);
            }
        }
    }

    function get_bundle_objs() {
        global $Core;
        
        $bundles = array();
        
        if (file_exists($this->bundlePath)) {
            $bundles = $Core->xmlHandler->ParserMain($this->bundlePath);
        }
        foreach ($bundles as $bundle) {
            $bundle->page = !isset($bundle->page) ? null : $bundle->page ;
            $pages = explode(',', $bundle->page);
            for ($i=0; $i<count($pages); $i++) {
                $pages[$i] = trim($pages[$i]);
            }
            if (in_array($this->pageID, $pages)) {
                if (isset($bundle->published) && $bundle->published == 1) {
                    $this->map_obj_to_region($bundle);
                }
            }
        }
    }
    
    function map_stories_to_region() {
        global $Core;
        
        $keyWordObj = null;
        if (isset($this->contentMap[REGION_META]) &&
             count($this->contentMap[REGION_META]))
        {
            $metaObjs = $this->contentMap[REGION_META];
            $keyWordObj = $Core->SelectObjByKey($metaObjs, 'name', 'keywords');
            $descObj = $Core->SelectObjByKey($metaObjs, 'name', 'description');
        }

        foreach ($this->get_story_objects() as $story) {
            $metaObjs = array();
            if ($story->page == $this->pageID) {
                if (isset($story->published) &&
                     $story->published == 1) {
                    $story->loadas = 'story';
                    if (isset($keyWordObj->id) && $keyWordObj->id) {
                        $keyWordObj->content .= ', '.$story->keywords;
                        $metaObjs = $Core->InsertObj($metaObjs, $keyWordObj, 'id');
                        $this->contentMap[REGION_META] = $metaObjs;
                    }
                    else if (!empty($story->keywords)) {
                        $keyWordObj = $this->make_meta_obj(
                            $metaObjs,
                            'keywords',
                            $story->keywords,
                            ''
                        );
                        $metaObjs = $Core->InsertObj($metaObjs, $keyWordObj, 'id');
                    }
                    if (isset($descObj->id) && $descObj->id) {
                        $metaObjs = $Core->DeleteObj($metaObjs, $descObj->id);
                    }
                    $desc = null;
                    if (isset($this->pageObj->meta_description)) {
                        $desc = $this->pageObj->meta_description;
                    }
                    if (trim($desc) != "") {
                        array_push(
                            $metaObjs, 
                            $this->make_meta_obj(
                                $metaObjs, 
                                'description', 
                                $desc, 
                                null
                            )
                        );
                    }
                    $this->contentMap[REGION_META] = isset($metaObjs) ? $metaObjs : array() ;
                    $this->map_obj_to_region($story);
                }
            }
        }
    }
    
    function make_meta_obj($metaObjs, $name, $content, $group) {
        global $Core;
        $obj             = new stdClass;
        $obj->id         = $Core->GetNewID($metaObjs);
        $obj->name       = $name;
        $obj->content    = $content;
        $obj->metagroups = null;
        $obj->loadas     = 'meta';
        $obj->region     = REGION_META;
        return $obj;
    }
    
    function get_story_objects() {
        global $Core;
        
        $pages = $Core->xmlHandler->ParserMain($this->pageXmlPath);
        
        $stories = array();
        foreach ($pages as $page) {
            $story = new stdClass;
            $story->type = 'story';
            $story->id = null;
            $story->name = $page->story;
            $story->modified = $page->modified;
            $story->page = $page->id;
            $story->region = REGION_MAIN;
            $story->published = $page->published;
            $story->syndicate = isset($page->syndicate) ? $page->syndicate : 1;
            $story->keywords = $page->keywords;
            if (isset($page->meta_description)) {
                $story->meta_description = $page->meta_description;
            }
            $obj->loadas = 'story';
            $stories[] = $story;
        }
        return $stories;
    }

    function get_page_tmpl() {
        global $Core; 

        // Load the page skeleton HTML
        
        $skeleton = $this->get_file_contents($this->skeletonPath);
        
        // Load the body template HTML

        $skin_path = SB_SERVER_PATH . str_replace('../', null, $this->skinsDirPath);
        
        $skin_file = "{$skin_path}skin.{$this->pageObj->pagetype}.html";

        $pageBody = DEFAULT_HTML;
        if (file_exists($skin_file)) {
            $pageBody = $this->get_file_contents($skin_file);
        }
        else {
            $pageBody = str_replace('{skin}', 'skin.'.$this->pageObj->pagetype.'.html',
                                    $pageBody);
        }
        if (strpos($pageBody, '</html>') === false) {
            $this->tmpl = str_replace('{page:body}', $pageBody, $skeleton);
        }
        else {
            $this->tmpl = $pageBody;
        }
        
    }
    
    function parse_page_tmpl() {
        $this->pageRegions = $this->get_page_regions($this->tmpl);
        $this->create_content_map();
    }
    
    function create_content_map() {
        for ($i=0; $i<count($this->pageRegions); $i++) {
            $this->contentMap[$this->pageRegions[$i]] = array();
        }
    }
    
    function get_page_regions($str) {
          $regions = array();
          if (preg_match_all(
              SB_REGEX_REGION_TOKEN, 
              $str, $tokens, PREG_SPLIT_DELIM_CAPTURE)) {
			  for ($i=0; $i<count($tokens); $i++) {
				  if (!in_array($tokens[$i][0], $regions)) {
					  array_push($regions, $tokens[$i][0]);
				  }
			  }
          }
          return $regions;
    }
    
    function get_file_contents($src) {
        global $Core;
        if (file_exists($src)) {
            ob_start();
            include($src);
            $buffer = ob_get_contents();
            ob_end_clean();
            return $buffer;
        } 
        $this->isError = 1;
        $this->add_error(1, str_replace('{file}', $src, SKIN_FILE_NOT_FOUND));
        return false;
    }
    
    function add_error($errNum, $errStr) {
        array_push($this->errors, new Error($errNum, $errStr));
    }
}


?>