<?php

/**
* @version        RC 1.1 2008-12-12 19:47:43 $
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

class Model extends Observer {

    var $data;

    function __construct($type) {
        $this->dao = MVC::getDAO($type);
    }

    function Model($type) {
        $this->__construct($type);
    }
    
    function index() {
        $this->dao->index();
        $this->setData($this->dao->getData());
    }
    
    function save() {
        /* Child class must define this method */
    }
    
    function delete() {
        /* Child class must define this method */
    }
    
    function create() {
        /* Child class must define this method */
    }
    
    function getItem($id) {
        /* Child class must define this method */
    }
    
    function getData() {
        return $this->data;
    }
    
    function setData($data) {
        $this->data = $data;
    }
}

?>
