<?php
/* $Id: class.phpcms_defaults.php,v 1.3 2006/05/25 12:29:21 guandalug Exp $ */
/*
   +----------------------------------------------------------------------+
   | phpCMS Content Management System - Version 1.3
   +----------------------------------------------------------------------+
   | phpCMS is Copyright (c) 2001-2006 by the phpCMS Team
   +----------------------------------------------------------------------+
   | This program is free software; you can redistribute it and/or modify
   | it under the terms of the GNU General Public License as published by
   | the Free Software Foundation; either version 2 of the License, or
   | (at your option) any later version.
   |
   | This program is distributed in the hope that it will be useful, but
   | WITHOUT ANY WARRANTY; without even the implied warranty of
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
   | General Public License for more details.
   |
   | You should have received a copy of the GNU General Public License
   | along with this program; if not, write to the Free Software
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston,
   | MA  02111-1307, USA.
   +----------------------------------------------------------------------+
   | Contributors:
   |    Andre Meiske (guandalug)
   +----------------------------------------------------------------------+
*/

/**
 * Contains the class phpcms_defaults that extends the class defaults with additional
 * functions
 *
 * @package phpcms
 * @subpackage configuration
 */

/**
 * Wrapper-Class for the 'defaults' settings for easier accessability
 *
 * Contains additional functions that convert user-given data to useable,
 * parsed / checked content
 *
 * @package phpcms
 * @subpackage configuration
 */
class phpcms_defaults extends defaults {

	/**
	 * Constructor of the extension
	 *
	 * Simply calls the constructor of the class 'defaults'
	 *
	 * @access public
	 */
	function phpcms_defaults()
	{
		defaults::defaults();
	}

	/**
	 * Check a 'boolean' setting for being active.
	 *
	 * Strings interpreted as 'Yes' are (case-independently):
	 *   On, An, Yes, Ja
	 * Additionally, every integer-value != 0 is accepted, as is the value 'true'
	 *
	 * @access public
	 * @var string $setting Name of the setting to be queried
	 * @var bool $default value to return if the setting is unset, defaults to 'false'
	 */
	function is_active($setting,$default=false)
	{
		if (!isset($this->$setting))
		{
			return $default;
		}
		if (is_string($this->$setting))
		{
			$check = strtolower($this->$setting);
			return $check == 'on' || $check == 'an' || $check == 'yes' || $check == 'ja';
		}
		return (bool)$this->$setting;
	}

	/**
	 * Retrieve a path from the defaults
	 *
	 * The function tries to make a given path relative to the document-root:
	 * If it starts with a '/', it's already relative to DocRoot
	 * If the path starts with a '.', strip the dot and prepend the current directory
	 * In any other case, simply prepend the current directory (always relative to the
	 * Document root.
	 *
	 * If '$make_absolute' is set to true (or omitted), DOCUMENT_ROOT is prepended
	 * to the path in order to get the complete path to the file
	 *
	 * @access public
	 * @var string $setting Name of the setting to be queried
	 * @var bool $make_absolute determines if the path should be (filesystem-)absolute, defaults to 'true'
	 */
	function get_path($setting,$make_absolute=true)
	{
		if (!isset($this->$setting) || !is_string($this->$setting))
		{
			return NULL;
		}

		switch($this->$setting[0])
		{
			case '/':
				$prepend = $make_absolute ? $this->DOCUMENT_ROOT : "";
				$result = $prepend.'/'.$this->$setting.'/';
				break;
			case '.':
				$result = $this->get_path("SCRIPT_PATH").substr($this->$setting,1).'/';
				break;
			default:
				$result = $this->get_path("SCRIPT_PATH").'/'.$this->$setting.'/';
				break;
		}
		return str_replace('//','/',$result);
	}

	/**
	 * Retrieve any other setting
	 *
	 * If it's not set, return the given default value
	 *
	 * @access public
	 * @var string $setting Name of the setting to be queried
	 * @var bool $default value to return if the setting is unset, defaults to 0
	 */
	function get_value($setting,$default=0)
	{
		if (!isset($this->$setting))
		{
			return $default;
		} else {
			return $this->$setting;
		}
	}
}
