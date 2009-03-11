<?php

defined('SKYBLUE') or die(basename(__FILE__)."\n");

/**
* The Router class replaces the hard-coded redirection in the .htaccess file
* for providing a flexible base for url-rewriting
* Examples: http://mydomain.tld/sbc/parentpagename/pagename.html
*           -> http://mydomain.tld/sbc/index.php?pid=pageid
*           http://mydomain.tld/sbc/parentpagename/pagename.25.htm
*           -> http://mydomain.tld/sbc/index.php?pid=pageid&show=25
*/

class Router {

    var $request;
    
    /**
    * Used to store the requested "file" passed to SBC via $_GET['route']
    * @access private
    * @var string
    */
    
    var $route;
    
    /**
    * The name of the current page (e.g., About.html)
    * @access private
    * @var string
    */
    
    var $pageName;
    
    /**
    * Used to store the pages form the xml File
    * @access private
    * @var Array
    */
    
    var $pages;
    
    /**
    * Used to store the xmparser
    * needed, because the core allready needs the pid for creating himself.
    * @access privat
    * @var xmlHandler
    */
    
    var $xmlHandler;
    
    /*
    * @constructor
    */
    
    function __construct($path=null) {
        $this->xmlHandler = new xmlHandler;
        $this->pages = $this->xmlHandler->ParserMain(
            empty($path) ? 'data/xml/page.xml' : $path . 'data/xml/page.xml'
        );
    }
    
    /*
    * @constructor (Legacy)
    */
    
    function Router($path=null) {
        $this->__construct($path);
    }
    
    /**
    * Retrieves the page route
    * @access private
    * @return string
    */
    
    function getRoute() {
        $this->route = null;
        if (isset($_GET['route'])) {
            $this->route = $_GET['route'];
        }
        $this->route = $this->removeLastSlash($this->route);
    }
    
    /**
    * @description Determines if the URL format is the legacy format
    * @access private
    * @return boolean
    */
    
    function isLegacyLink() {
       $isLegacyLink = false;
       if (preg_match_all("/pg-([0-9]+)-c([0-9]+)-([0-9]+)\.htm/i", $this->route, $matches)) {
           $_GET['pid']  = $matches[1][0];
           $_GET['cid']  = $matches[2][0];
           $_GET['show'] = $matches[3][0];
           $isLegacyLink = true;
       }
       else if (preg_match_all("/pg-([0-9]+)-c([0-9]+)\.htm/i", $this->route, $matches)) {
           $_GET['pid']  = $matches[1][0];
           $_GET['cid']  = $matches[2][0];
           $isLegacyLink = true;
       }
       else if (preg_match_all("/pg-([0-9]+)-([0-9]+)\.htm/i", $this->route, $matches)) {
           $_GET['pid']  = $matches[1][0];
           $_GET['show'] = $matches[2][0];
           $isLegacyLink = true;
       }
       else if (preg_match_all("/pg-([0-9]+)\.htm/i", $this->route, $matches)) {
           $_GET['pid']  = $matches[1][0];
           $isLegacyLink = true;
       }
       return $isLegacyLink;
    }
    
    /**
    * @description    Gets the text name of the current page
    * @access public
    * @return true|false returns true if everything is gone right, false if not
    */
    
    function getPageName() {
        $Filter = new Filter;
        $pageName = basename($this->route);
        if (empty($this->route)) {
            $defaultPage = $this->getDefaultPage();
            $defaultPage = $defaultPage[0];
            $pageId = $Filter->get($_GET, 'pid', $defaultPage->id);
            $pageName = basename($this->GetSefLink($pageId));
        }
        $this->setPageName($pageName);
        return $this->pageName;
    }
    
    function setPageName($pageName) {
        $this->pageName = $pageName;
    }
    
    /**
    * Used to initiate the routing
    * @access public
    * @return true|false returns true if everything is gone right, false if not
    */
    
    function route() {
    
        $this->getRoute();
        
        // If this is not an SEF URL, let the request pass through
        
        if (empty($this->route) && !empty($_GET)) {
            return true;
        }
        
        // If this is a legacy-format link (Name-pg-N.html), 
        // let the request pass through
        
        else if ($this->isLegacyLink()) {
            if (!$this->getPageById($_GET['pid'])) {
                $_GET['pid'] = 'notfound'; // $this->pageNotFound();
            }
            return true;
        }
        
        // Otherwise, process the SEF URL
        
        else {
            
            $pageTree = explode('/', $this->route);
            $selectedPages = array();
            
            foreach ($pageTree as $pageName) {
            
                $treelength = count($selectedPages);
    
                $parentPage = null;
                $parentPageId = null;
                if ($treelength) {
                    $parentPage   = $selectedPages[$treelength-1];
                    $parentPageId = $parentPage->id;
                }
                
                $foundPages = $this->getPageByNameAndParent($pageName, $parentPageId);
                
                if (!count($foundPages)) {
                    $_GET['pid'] = 'notfound'; // $this->pageNotFound();
                    return false;
                }
                
                $isChild = false;
                foreach ($foundPages as $foundPage) {
                    if ($this->checkParent($foundPage, $parentPage)) {
                        array_push($selectedPages, $foundPage);
                        $isChild = true;
                        break;
                    }
                }
                if (!$isChild) return false;
            }
            
            $_GET['pid'] = null;
            if (count($selectedPages)) {
                $_GET['pid'] = $selectedPages[count($selectedPages)-1]->id;
            }
            return true;
        }
        return true;
    }
    
    /**
     * GetLink returns the HREF URL in the proper format depending on whether or not
     * USE_SEF_URLS is set to true or not.
     *
     * @access public
     * @param int     $PageID The id of the page to display the object.
     * @param array   $params Key=>value pairs of URL vars
     * @return string $link
     */
    
    function GetLink($pageID, $params=array()) {
        if (defined('USE_SEF_URLS') && USE_SEF_URLS == 1) {
            $link = $this->getPageTree($pageID);
            if (is_array($params) && count($params)) {
                foreach ($params as $k=>$v) {
                    $link .= "{$k}{$v}";
                }
            }
            return FULL_URL . $link . ".html";
        }
        else {
            return FULL_URL . "index.php?pid={$pageID}" . $this->build_query($params);
        }
    }
    
    /**
     * Builds a URL Query String
     *
     * @param array   $params Key=>value pairs of URL vars
     * @return string $query
     */
    
    function build_query($params) {
        if (function_exists('http_build_query')) {
            return http_build_query($params);
        }
        $query = array();
        foreach ($params as $k=>$v) {
            array_push($query, "$k=$v");
        }
        return implode("&", $query);
    }
    
    /**
     * @description    GetLink returns SEF URL of the requested page
     * @access public
     * @param int     $PageID The id of the page to display the object.
     * @param array   $params Key=>value pairs of URL vars
     * @return string $link
     */
    
    function GetSefLink($pageID, $params=array()) {
        $link = $this->getPageTree($pageID);
        if (is_array($params) && count($params)) {
            foreach ($params as $k=>$v) {
                $link .= "{$k}{$v}";
            }
        }
        return FULL_URL . $link . ".html";
    }
    
    /**
     * @description    Gets a fingerprint of the current page request. 
     * The fingerprint is used to refer to a page without having to 
     * worry about whether the page is being requested via SEF URL or 
     * a URL query string.
     * @access public
     * @return string  A fingerprint of the current page request
     */
     
    function getFingerprint() {
        $str = $this->route;
        if (empty($this->route)) {
            foreach ($_GET as $k=>$v) {
                $str .= "{$k}{$v}";
            }
        }
        return md5($str) . ".html";
    }
    
    /**
    * removes the '.htm' or '.html' from a string
    * @access private
    * @return string
    */
    
    function removeFileExt($string) {
        if (preg_match('/^(.*)\.(htm|html)$/', $string, $matches)) {
            return $matches[1];
        }
        return $string;
    }
    
    /**
    * removes the last / from a string
    * @access private
    * @return string
    */
    
    function removeLastSlash($string) {
        if (preg_match('/^(.*)\/$/', $string, $matches)) {
            return $matches[1];
        }
        return $string;
    }
    
    /**
    * returns a pageobject identified by his name or his id (is_int(...))
    * @access private
    * @param string|int     if it's an integer, the page is identified by his id if not, by his name
    * @return object|false
    */
    
    function getPageByNameAndParent($identifier, $parent) {
        
        $identifier = $this->removeFileExt($identifier);
        
        $bits = explode('.', $identifier);
        $identifier = $bits[0];
        if (count($bits) > 1) {
            $_GET['params'] = array_slice($bits, 1);
        }
        
        if (empty($identifier)) {
            return $this->getDefaultPage();
        }
        
        $selectedPages = array();
        foreach ($this->pages as $page) {
            if ($this->normalize($page->name) == strtolower($identifier) 
                && $page->parent == $parent)
            {
                array_push($selectedPages, $page);
            }
        }
        return $selectedPages;
    }
    
    /**
    * Retrieves the default page
    * @access private
    * @return object
    */
    
    function getDefaultPage() {
        foreach ($this->pages as $page) {
            if (1 == intval($page->isdefault)) {
                return array($page);
            }
        }
        return array();
    }
    
    /**
    * Retrieves a page by its ID
    * @access private
    * @return object
    */
    
    function getPageById($identifier) {
        foreach ($this->pages as $page) {
            if (intval($page->id) == intval($identifier)) {
                return $page;
            }
        }
        return null;
    }
    
    /**
    * Displays a custom 404 page if it exists
    * @access private
    * @return boolean
    */
    
    function pageNotFound() {
        $errorPage = $this->getPageByNameAndParent("404-page-not-found", "");
        if (is_array($errorPage) && count($errorPage)) {
            return $errorPage[0];
        }
        else if (is_object($errorPage)) {
            return $errorPage;
        }
        return null;
        /*
        if (!empty($errorPage)) {
            return 'notfound'; // $errorPage->id;
            $path = null;
            $levels = explode('/', $this->route);
            if (count($levels) > 1) {
                $path = str_repeat('../', count($levels)-1);
            }
            header("HTTP/1.0 404 Not Found");
            header("Location: {$path}404-page-not-found.html");
            exit(404);
        }
        return false;
        */
    }
    
    /**
    * normalizes the string for use in an url
    * @access private
    * @return string
    */
    
    function normalize($str) {
        $str = strtolower($str);
        $str = str_replace(
            array('&auml;', '&ouml;', '&uuml;', '&szlg', '&amp;', ' & ', '&', ' - ', '/', ' / ', ' '),
            array('ae', 'oe', 'ue', 'ss', '-and-', '-and-', '-and-', '-', '-', '-', '-'),
            $str
        );    
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789_-";
        $str = strtolower($str);
        for ($i=0; $i<strlen($str); $i++) {
            if (false === strpos($chars, $str{$i})) {
                $str{$i} = '-';
            }
        }
        $max = 100;
        $n=0;
        while (strpos($str, '--') !== false && $n<$max) {
            $str = str_replace('--', '-', $str);
            $n++;
        }
        return $str;
    }
    
    /**
    * returns an array with the pagename and its parents names
    * @access private
    * @return array of objects
    */
    
    function getPageTree($PageID) {
        $pageTree = array();
        $page = $this->getPageById($PageID);
        array_push($pageTree, $this->normalize($page->name));
    
        while ($page->parent) {
            $page = $this->getPageById($page->parent);
            array_push($pageTree, $this->normalize($page->name));
        }
        $pageTree = array_reverse($pageTree);
        $pageTree = implode('/',$pageTree);
        return $pageTree;
    }
    
    /**
    * Determines if the current page is a top-level page
    * @access private
    * @return boolean
    */
    
    function checkParent($child, $parent) {
        if (!$parent || !$child) return true;
        if ($child->parent != $parent->id) {
            return false;
        }
        return true;
    }
}
?>