<?php

/**
* @version    RC 1.1 2008-12-12 19:47:43 $
* @package    SkyBlueCanvas
* @copyright  Copyright (C) 2005 - 2008 Scott Edwin Lewis. All rights reserved.
* @license    GNU/GPL, see COPYING.txt
* SkyBlueCanvas is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYING.txt for copyright notices and details.
*/

defined('SKYBLUE') or die(basename(__FILE__));

class PageDAO {

    var $data;
    var $source;

    function __construct() {
        $this->setSource(SB_XML_DIR . 'page.xml');
    }

    function PageDAO() {
        $this->__construct();
    }
    
    function setSource($file) {
        $this->source = $file;
    }
    
    function save() {
        global $Core;
        return FileSystem::write_file(
            $this->source,
            $Core->xmlHandler->objsToXml($this->getData(), 'page')
        );
    }
    
    function delete($id) {
        $this->index();
        $pages = $this->getData();
        $filtered = array();
        foreach ($pages as $page) {
            if ($page->id !== $id) {
                array_push($filtered, $page);
            }
        }
        $this->setData($filtered);
        return $this->save();
    }
    
    function insert($obj) {
        $this->index();
        $pages = $this->getData();
        $found = false;
        for ($i=0; $i<count($pages); $i++) {
            if ($pages[$i]->id == $obj->id) {
                $pages[$i] = $obj;
                $found = true;
            }
        }
        if (!$found) {
            array_push($pages, $obj);
        }
        $this->setData($pages);
    }
    
    function index() {
        global $Core;
        $this->data = $Core->xmlHandler->ParserMain($this->source);
        return $this->getData();
    }
    
    function getItem($key, $match) {
        $this->index();
        $pages = $this->getData();
        foreach ($pages as $page) {
            if (isset($page->$key) && $page->$key == $match) {
                return $page;
            }
        }
        return null;
    }
    
    function getData() {
        return $this->data;
    }
    
    function setData($data) {
        $this->data = $data;
    }
    
}

?>