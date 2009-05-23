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

/**
* The Error class is used to created detailed custom errors in the 
* Skin class. The Skin class uses an array to store errors so that
* multiple errors can be returned should they be encountered.
*
* @package SkyBlue
*/

class Error extends SkyBlueObject {
    var $errNum;
    var $errStr;
    
    function __construct($errNum, $errStr) {
        $this->errNum = $errNum;
        $this->errStr = $errStr;
        return $this;
    }
    
    function Error($errNum, $errStr) {
        $this->__construct($errNum, $errStr);
    }
}

?>
