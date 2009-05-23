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

/**
* This class is not yet implemented. Its purpose is to act as a bridge between
* the SkyBlue Core and the datasource. Planned datasources include XML
* and standard SQL databases (MySQL, PostGres, etc.).
*
* @package SkyBlue
*/

class datasource 
{

    var $type       = NULL;
    var $sourcename = NULL;
    var $connection = NULL;
    var $username   = NULL;
    var $password   = NULL;
    var $test       = NULL;
    
    function __construct() {}
    
    function datasource()
    {
        $this->__construct();
    }

    function test()
    {
        echo '<h2>'.$this->test.'</h2>';
    }
    
    function loadObjects()
    {
        global $Core;
        if ( $this->type == 'xml' )
        {
            $objs = $Core->xmlHandler->ParserMain( $this->connection );
            return $objs;
        }
    }
}

?>