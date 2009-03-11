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

Loader::load('includes.mvc.model');
Loader::load('includes.mvc.view');
Loader::load('includes.mvc.controller');

class MVC extends Observer {

	function getController($Request) {
	    $com = $Request->get('com');
		$ClassName = ucwords($com)."Controller";
		if (class_exists($ClassName)) {
			return new $ClassName($Request);
		}
		return null;
	}
	
	function getModel($type) {
		$ClassName = ucwords($type)."Model";
		if (class_exists($ClassName)) {
			return new $ClassName;
		}
		return null;
	}
	
	function getDAO($type) {
		$ClassName = ucwords($type)."DAO";
		if (class_exists($ClassName)) {
			return new $ClassName;
		}
		return null;
	}
	
	function getView($type) {
		$ClassName = ucwords($type)."View";
		if (class_exists($ClassName)) {
			return new $ClassName;
		}
		return null;
	}

}


?>