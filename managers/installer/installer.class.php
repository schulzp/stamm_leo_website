<?php

/**
* @version        RC 1.0.3.2 2008-04-24 15:03:43 $
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

define('INSTALLER_URL', 'admin.php?mgroup=collections&mgr=installer');

define('INSTALLER_DIR', dirname(__FILE__));

require_once(SB_INC_DIR . 'mvc/loader.php');

Loader::load('includes.mvc.mvc');
Loader::load('managers.installer.mvc.controllers.*');
Loader::load('managers.installer.mvc.models.*');
Loader::load('managers.installer.mvc.daos.*');

class installer {

    var $com;
    var $action;
    var $valid_coms = array(
        'manager',
        'module',
        'fragment',
        'skin'
    );

    function __construct() {
        global $Core;
        
        $Request = new RequestObject;
        
        $this->com = $Request->get('com');
        
        if (empty($this->com)) {
            $this->com = 'manager';
            $Request->com = 'manager';
        }

        if (!in_array($this->com, $this->valid_coms)) {
            Core::SBRedirect(INSTALLER_URL . "&com=manager");
        }

        $Controller = MVC::getController($Request);
        $Controller->execute();
        $Controller->display();
    }

    function installer() {
        $this->__construct();
    }
}

?>