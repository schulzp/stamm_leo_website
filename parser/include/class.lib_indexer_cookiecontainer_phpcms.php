<?php
/* $Id: class.lib_indexer_cookiecontainer_phpcms.php,v 1.1.2.9 2006/06/18 18:07:30 ignatius0815 Exp $ */
/*
   +----------------------------------------------------------------------+
   | phpCMS Content Management System - Version 1.2
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
   |    Michael Brauchl (mcyra)
   |    Tobias Dönz (tobiasd)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

/*
 * Cookie container
 *
 * manages cookies set by web sites
 *
 * ToDo: delete old cookies, handle path
 *
 */

class cookie_container {

	var $webgrab_user = 'cookie';

	// constructor
	function cookie_container() {

		global $PHPCMS_INDEXER_TEMP_SAVE_PATH;

		// read cookiefile, if available
		$temp_buffer = @file($PHPCMS_INDEXER_TEMP_SAVE_PATH.$this->webgrab_user.'.txt');
		// prepare data
		$temp_buffer = stripslashes($temp_buffer[0]);
		$this->{$this->webgrab_user} = unserialize ($temp_buffer);

	} // end cookie_container

	// encode filename for hash
	function encode_domainname($domain_name) {

		$save_name = substr($domain_name,1);
		$save_name = str_replace ('.','_DOT_',$save_name);
		return $save_name;

	} // end encode_domainname

	// save a cookie in the container
	function put_cookie($cookie,$url) {

		// seperate name form parameter
		if (strstr($cookie,';')) {
			$value_pair   = trim(substr($cookie, 0, strpos($cookie, ';')));
			$cookie_parms = substr($cookie, strpos($cookie, ';')+1);
			$parms = explode(';',$cookie_parms);
			foreach ($parms as $parm) {
				list($k,$v)=explode('=',$parm);
				$temp->$k = $v;
			}
		}
		else {
			$value_pair = trim($cookie);
		}

		$temp->name  = trim(substr($value_pair,0,strpos($value_pair,'=')));
		$temp->value = trim(substr($value_pair,strpos($value_pair,'=')+1));

		$save_name = $this->encode_domainname('.'.$url['domain']);

		while(list($k, $v) = each($temp)) {
			$this->{$this->webgrab_user}->domain[$save_name][$temp->name][$k]=$v;
		}

		unset ($temp);

	} // end put_cookie

	function save() {

		global $PHPCMS_INDEXER_TEMP_SAVE_PATH;

		$towrite = serialize ($this->{$this->webgrab_user});
		addslashes ($towrite);
		$fp = fopen($PHPCMS_INDEXER_TEMP_SAVE_PATH.$this->webgrab_user.'.txt', 'wb');
		fwrite ($fp,$towrite, strlen($towrite));
		fclose($fp);

	} // end save

	function send_cookie($url) {

		$domain = $this->encode_domainname('.'.$url['domain']);

		if (!isset($this->{$this->webgrab_user}->domain[$domain])) {
			return false;
		}

		$RetVal = 'Cookie: ';
		foreach ($this->{$this->webgrab_user}->domain[$domain] as $cookie) {
				$RetVal.= trim($cookie['name']).'='.trim($cookie['value']).'; ';
		}
		$RetVal = substr($RetVal,0,-2)."\r\n";
		return $RetVal;

	} // end send_cookie

} // end class cookie_container

?>