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

class contacts_model
{
    var $id         = null;
    var $name       = null;
    var $text       = null;
    var $cantarget  = 1;
    var $loadas     = "module";
    var $bundlesource = 'mod.contacts.php';
    var $bundletype = 'module';
    var $hasmodule  = true;
    
    function __construct() 
    {
        ;
    }
    
    function contacts_model()
    {
        $this->__construct();
    }


}

?>
