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

class ActiveskinDAO {

    var $data;
    var $source;

    function __construct() {
        $this->setSource(SB_XML_DIR . 'activeskin.xml');
    }

    function ActiveskinDAO() {
        $this->__construct();
    }
    
    function setSource($file) {
        $this->source = $file;
    }
    
    function save() {
        global $Core;
        $result = FileSystem::write_file(
            $this->source,
            $Core->xmlHandler->objsToXml($this->getData(), 'activeskin')
        );
        $this->index();
        return $result;
    }
    
    function index() {
        global $Core;
        $objs = $Core->xmlHandler->ParserMain($this->source);
        $this->setData(array());
        if (count($objs)) {
            $this->setData($objs);
        }
        return $this->getData();
    }
    
    function getActiveSkin() {
        $this->index();
        $objs = $this->getData();
        if (count($objs) && isset($objs[0]->activeskin)) {
            $this->setData($objs[0]->activeskin);
        }
    }
    
    function activateSkin($name) {
        global $Core;
        $this->index();
        $objs = $this->getData();
        $objs[0]->activeskin = $name;
        return $this->save();
    }
    
    function getData() {
        return $this->data;
    }
    
    function setData($data) {
        $this->data = $data;
    }
    
}

?>