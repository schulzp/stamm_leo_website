<?php
/* $Id: lib.log_referrer_phpcms.php,v 1.1.2.7 2006/06/18 18:07:32 ignatius0815 Exp $ */
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
   |    Henning Poerschke (hpoe)
   |    Martin Jahn (mjahn)
   |    Tobias Doenz (tobiasd)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

// get current time
$ref_time = time();

// set referrer to 'none' if not defined
if(!isset($GLOBALS['_SERVER']['HTTP_REFERER'])) {
	$referrer = 'none';
}
else {
	$referrer = $GLOBALS['_SERVER']['HTTP_REFERER'];
}

// set path for referrer file
if($DEFAULTS->REFERRER_DIR[0] == '.') {
	$ref_dir = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->SCRIPT_PATH.'/'.$DEFAULTS->REFERRER_DIR;
} else {
	$ref_dir = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->REFERRER_DIR;
}
$DEFAULTS->RefLog = $ref_dir.'/'.$DEFAULTS->REFERRER_FILE;
$ref_log = $DEFAULTS->RefLog;

if($ref_fp = fopen($ref_log,"r+b")) {

	// lock file, read and unserialize data
	flock($ref_fp, LOCK_EX);
	$ref_fsize = filesize($ref_log);
	$input = fread($ref_fp, $ref_fsize);
	$input = trim($input);
	$ref_data = unserialize($input);

	// correct referrer contents
	$referrer = str_replace("&amp;", "&", $referrer);
	//$referrer = stripslashes($referrer);

	// parse referrer
	$ref_url = parse_url($referrer);

	// set referrer entry parts ...
	if(!isset($ref_url['host']) || $ref_url['host'] == '') {
		// ... when referrer not set
		$referrer = 'none';
		$ref_domain = 'none';
		$ref_last = '';
	}
	else {
		// ... when referrer set
		$ref_url['host'] = strtolower($ref_url['host']);
		$ref_domain = str_replace("www.", "", $ref_url['host']);
		if (isset ($ref_url ['path'])) {
			$ref_last = $ref_url['host'].$ref_url['path'];
		} else {
			$ref_last = $ref_url['host'];
		}
		if(isset($ref_url['query'])) {
			$ref_last .= '?'.$ref_url['query'];
		}
	}

	$reloadlock = false;
	// test if reload lock should be used
	if(isset($GLOBALS['_SERVER']['REMOTE_ADDR']) AND isset($ref_data[$ref_domain]['x']) AND
	   $GLOBALS['_SERVER']['REMOTE_ADDR'] == $ref_data[$ref_domain]['x']) {
		if($ref_time - $ref_data[$ref_domain]['t'] <= $DEFAULTS->REF_RELOAD_LOCK) {
			$reloadlock = true;
		}
	}

	// set count value for this referrer
	if(isset($ref_data[$ref_domain]['c']) AND $ref_data[$ref_domain]['c'] > 0) {
		if($reloadlock == false) {
			$ref_data[$ref_domain]['c']++;
		}
	} else {
		$ref_data[$ref_domain]['c'] = 1;
	}

	// remove chars that would corrupt serialization!
	//$ref_last = addslashes($ref_last);
	$ref_last = str_replace("{", "", $ref_last);
	$ref_last = str_replace("}", "", $ref_last);

	// set entry contents
	$ref_data[$ref_domain]['p'] = $ref_last; // the referrer itself
	$ref_data[$ref_domain]['t'] = $ref_time; // time of last referrer occurance
	$ref_data[$ref_domain]['x'] = $GLOBALS['_SERVER']['REMOTE_ADDR']; // remote address which sent referrer

	// delete oldest entry from $ref_data if max referrer count reached
	if(count($ref_data) > $DEFAULTS->STATS_REFERER_COUNT) {
		$ref_domain = key($ref_data);
		foreach($ref_data as $domain => $data) {
			if($data['t'] < $ref_data[$ref_domain]['t']) {
				$ref_domain = $domain;
			}
		}
		unset($ref_data[$ref_domain]);
	} // end if

	// write data, unlock and close file
	rewind($ref_fp);
	fwrite($ref_fp, serialize($ref_data));
	flock($ref_fp, LOCK_UN);
	fclose($ref_fp);

} // end if

// If we don't remove all this data it remains available through $GLOBALS
// This could be used to display referrers on every page without topref.php
// having to read the log file...
unset(
	$input,
	$ref_data,
	$ref_dir,
	$ref_domain,
	$ref_fsize,
	$ref_last,
	$ref_time,
	$ref_url,
	$reloadlock);

?>