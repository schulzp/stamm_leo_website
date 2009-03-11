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
define('SKINNER_URL', 'admin.php?mgroup=templates&mgr=skinner');
define('SKINNER_DIR', dirname(__FILE__));

require_once(SB_INC_DIR . 'mvc/loader.php');

Loader::load('includes.mvc.mvc');
Loader::load('managers.skinner.mvc.controllers.*');
Loader::load('managers.skinner.mvc.models.*');
Loader::load('managers.skinner.mvc.daos.*');

class skinner {

    var $com;
    var $action;
    var $valid_coms = array(
        'skin'
    );

    function __construct() {
        global $Core;
        
        $Request = new RequestObject;
        
        $this->com = $Request->get('com');
        
        if (empty($this->com)) {
            $this->com = 'skin';
            $Request->com = 'skin';
        }

        if (!in_array($this->com, $this->valid_coms)) {
            Core::SBRedirect(SKINNER_URL . "&com=skin");
        }

        $Controller = MVC::getController($Request);
        $Controller->execute();
        $Controller->display();
    }

    function skinner() {
        $this->__construct();
    }
}

?>