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

/*
* @description  Defines a generic getter and setter for objects
*/

class SkyBlueObject {

    /*
    * @description  Returns the value of a given property if it exists
    * @param string The name of the property to get
    * @param string The default value to return if the requested property does not exist
    * @return mixed The value of the requested property
    */

    function get($prop, $default=null) {
        if (isset($this->$prop)) return $this->$prop;
        return $default;
    }
    
    /*
    * @description  Sets the value of a given property
    * @param string The name of the property to get
    * @param string The value to which to set the given property
    * @return mixed The previous value of the requested property
    */
    
    function set($prop, $value) {
		$previous = $this->get($prop);
		$this->$prop = $value;
		return $previous;
    }
}

?>