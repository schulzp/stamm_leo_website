<?php

/**
* @version        RC 1.1 2008-12-12 19:47:43 $
* @package        SkyBlueCanvas
* @copyright    Copyright (C) 2005 - 2008 Scott Edwin Lewis. All rights reserved.
* @license        GNU/GPL, see COPYING.txt
* SkyBlueCanvas is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYING.txt for copyright notices and details.
*/

defined('SKYBLUE') or die(basename(__FILE__));

class FragmentModel extends Model {

    var $dao;
    var $data;
        
    function __construct() {
        parent::__construct('fragment');
    }

    function FragmentModel() {
        $this->__construct();
    }
    
    function listItems() {
        parent::index();
    }
    
    function delete($name) {
        return $this->dao->delete($name);
    }
    
    function save($package) {
        return $this->dao->save($package);
    }

}

?>