<?php
/* $Id: lib.log_stats_phpcms.php,v 1.1.2.5 2006/06/18 18:07:32 ignatius0815 Exp $ */
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
   |    Tobias Doenz (tobiasd)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

// set referrer to 'none' if not defined
if(!isset($GLOBALS['_SERVER']['HTTP_REFERER'])) {
	$referrer = 'none';
}
else {
	$referrer = $GLOBALS['_SERVER']['HTTP_REFERER'];
}

// set path for referrer file
if($DEFAULTS->STATS_DIR[0] == '.') {
	$stat_dir = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->SCRIPT_PATH.'/'.$DEFAULTS->STATS_DIR;
} else {
	$stat_dir = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->STATS_DIR;
}
if($DEFAULTS->STATS_CURRENT[0] == '.') {
	$stat_file = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->SCRIPT_PATH.'/'.$DEFAULTS->STATS_CURRENT.'/'.$DEFAULTS->STATS_FILE;
} else {
	$stat_file = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->STATS_CURRENT.'/'.$DEFAULTS->STATS_FILE;
}

// create entry
$tr = ';';
$user_agent = str_replace($tr, ',', $GLOBALS['_SERVER']['HTTP_USER_AGENT']);
$entry = time().$tr.$GLOBALS['_SERVER']['REMOTE_ADDR'].$tr.$GLOBALS['_SERVER']['SERVER_PROTOCOL'].
         $tr.$GLOBALS['_SERVER']['REQUEST_METHOD'].$tr.$referrer.$tr.$user_agent.$tr.
         $GLOBALS['_SERVER']['REQUEST_URI']."\n";

// append data to stat file
if($fp = fopen($stat_file, 'a')) {
	flock($fp, LOCK_EX);
	fputs ($fp, $entry, strlen($entry));
	flock($fp, LOCK_UN);
	fclose($fp);
}

?>