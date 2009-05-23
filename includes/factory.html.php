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
* This class is used create HTML elements. Its primary purpose is to 
* create a true separation between the logic and markup codes.
* The class uses the Factory Pattern as described by The Gang of Four 
* in "Design Patterns: Elements of Reusable Object-Oriented Softwared" 
* ISBN 0-201-63361-2.
*
* @package SkyBlue
*/

class HTML {
    var $xml;
    
    function __construct() {
        ;
    }
    
    function HTML() {
        $this->__construct();
    }
    
    function MakeElement($type, $attrs=array(), $cdata='', $hasCloseTag=1) {
        return $this->node($type, $attrs, $cdata, $hasCloseTag);
    }

    function node($nodeName, $attrs, $cdata, $hasCloseTag) {
        $attrs_str = null;
        if (count($attrs)) {
            $attrs_str = "";
            foreach($attrs as $k=>$v) {
                if (!empty($k)) $attrs_str .= " $k=\"$v\"";
            }
        } 
        $this->xml = "<$nodeName$attrs_str />";
        if ($hasCloseTag) {
            $this->xml = "<$nodeName$attrs_str>" . trim($cdata) . "</$nodeName>\n";
        } 
        return $this->xml;
    }
}

?>