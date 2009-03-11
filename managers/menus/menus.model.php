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

class menus_model
{
    var $id           = NULL;
    var $title        = NULL;
    var $value        = NULL;
    var $menutype     = NULL;
    var $showtitle    = NULL;
    var $order        = NULL;
    var $cantarget    = 1;
    var $group        = 'navigation';
    var $bundletype   = 'xml';
    var $bundlesource = 'menus.xml';
    var $objtype      = 'menu';
    var $loadas       = "menu";
    
    function __construct() 
    {
        ;
    }
    
    function menu_model()
    {
        $this->__construct();
    }

}

?>