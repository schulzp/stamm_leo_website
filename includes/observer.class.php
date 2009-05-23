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

/*
* @description  The Observer class allows events to be attached to 
* different events or state changes within classes that inherit from 
* this class.
*/

class Observer extends SkyBlueObject {

    /* 
    * @description  User-registered events
    */

    var $_events = array();
    
    /**
    * @description  Class constructor
    * @return void
    */
    
    function __construct() {}
    
    /**
    * @description  Class constructor (legacy)
    * @return void
    */
    
    function Observer() {
        $this->__construct();
    }
    
    /**
    * @description  Adds a custom event for later use
    * @param string The event name
    * @return void
    */
    
    function addEvent($event) {
		if ($this->isRegistered($event)) return;
		$this->_events[$event] = array();
    }
    
    /*
    * @description  Checks to see if a particular event has any registered callbacks
    * @param string The name of the event
    * @param bool   Whether or not the event has any callbacks
    */
    
    function hasCallbacks($event) {
        if ($this->isRegistered($event)) {
            return (count($this->_events[$event]) > 0);
        }
        return false;
    }
    
    /*
    * @description  Checks to see if an event has already been registered
    * @param string The name of the event
    * @return bool  Whether or not the event is registered
    */
    
    function isRegistered($event) {
        return array_key_exists($event, $this->_events);
    }
    
    /**
    * @description  Registers a custom event
    * @param string The event name
    * @param mixed  A string name of a function or array of class, method to call
    * @param array  The arguments to pass to the callback
    * @return void
    */
    
    function register($event, $callback) {
		$this->addEvent($event);
		array_push($this->_events[$event], $callback);
    }
    
    /**
    * @description  Fires all methods attached to a custom event
    * @param string The event name to fire
    * @param array  The data arguments on which to operate (if any)
    * @return void
    */
    
    function trigger($event, $data=null) {
        if ($this->isRegistered($event)) {
            $callbacks = &$this->_events[$event];
			for ($i=0; $i<count($callbacks); $i++) {
				if (is_callable($callbacks[$i])) {
				    $callback = $callbacks[$i];
				    $callbacks[$i] = null;
					$data = call_user_func($callback, $data);
				}
			}
        }
        return $data;
    }
}

?>