<?php
/* $Id: parser.php,v 1.6.2.43 2006/06/18 18:06:10 ignatius0815 Exp $ */
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
   |    Beate Paland (beate76)
   |    Henning Poerschke (hpoe)
   |    Markus Richert (e157m369)
   |    Thilo Wagner (ignatius0815)
   +----------------------------------------------------------------------+
*/

// protect include files from direct execution
define('PHPCMS_RUNNING',1);

// start parser time-measurement
$PHPCMS_TIMER_START = microtime();

// check the PHP version
$php_version = explode('.', phpversion());
if(($php_version[0] == 4 AND $php_version[1] == 0 AND $php_version[2] < 1) OR ($php_version[0] < 4)) {
	die('<b>This is phpCMS 1.2.2.</b><br /><br />The parser is no more supporting PHP 4.0.1 or older!<br /><br />Please update to the latest version of PHP 4...');
}

// define the phpCMS-internal includepath
// take care of a trailing slash
define('PHPCMS_INCLUDEPATH', dirname(__FILE__).'/include/');

// include the base class to create a clean, PHP-version-independent scope
include(PHPCMS_INCLUDEPATH.'class.phpcms.php');
$PHPCMS = new PHPCMS;
// now cleanup the scope
$PHPCMS->prepare_environment_vars();

// decode and split up the $QUERY_STRING (params the parser.php is called with) into the array $PHPCMS->_QUERY_STRING
$PHPCMS->set_environment_var('ENV',    'QUERY_STRING', urldecode($QUERY_STRING));
$PHPCMS->set_environment_var('SERVER', 'QUERY_STRING', urldecode($QUERY_STRING));
$PHPCMS->extract_special_separated($PHPCMS->_QUERY_STRING, '', '?'.$QUERY_STRING, '', '');
// set the lowercase $PHPCMS->_query_string-array used for case-save comparisons
$PHPCMS->set_case_insensitive_keys($PHPCMS->_QUERY_STRING, '$this->_query_string', '', '');

// prepare, decode and split up the $REQUEST_URI (the users submission) into the array $PHPCMS->_REQUEST_URI
if(!isset($REQUEST_URI) || $REQUEST_URI == '') {
	$REQUEST_URI = $PHP_SELF.'?'.$_SERVER['QUERY_STRING'];
}
$PHPCMS->set_environment_var('ENV',    'REQUEST_URI', urldecode($REQUEST_URI));
$PHPCMS->set_environment_var('SERVER', 'REQUEST_URI', urldecode($REQUEST_URI));
$PHPCMS->extract_special_separated($PHPCMS->_REQUEST_URI, '', '?_URI_='.$REQUEST_URI, '', '');
// set the lowercase $PHPCMS->_request_uri-array used for case-save comparisons
$PHPCMS->set_case_insensitive_keys($PHPCMS->_REQUEST_URI, '$this->_request_uri', '', '');

// set $PHPCMS->TIMER and unset the helper
$PHPCMS->TIMER['START'] = $PHPCMS->get_time($PHPCMS_TIMER_START);
unset($PHPCMS_TIMER_START);


// load helper-library
include(PHPCMS_INCLUDEPATH.'class.lib_error_phpcms.php');
include(PHPCMS_INCLUDEPATH.'class.lib_data_file_phpcms.php');
include(PHPCMS_INCLUDEPATH.'class.lib_phpcms.php');
$PHP = new LibphpCMS;

// load configuration
include(PHPCMS_INCLUDEPATH.'default.php');
$DEFAULTS = new defaults;

// set PHP error-reporting
if(isset($DEFAULTS->ERROR_ALL) && $DEFAULTS->ERROR_ALL == 'on') {
	error_reporting(E_ALL); // 2047
	ini_set('display_errors','1');
}
elseif(isset($DEFAULTS->DEBUG) && $DEFAULTS->DEBUG == 'on') {
	error_reporting(E_ALL & ~E_NOTICE); // 2039
}
else {
	error_reporting(E_ALL & ~(E_NOTICE | E_WARNING)); // 2037
}

// check for EDIT-mode
if((isset($_COOKIE['phpCMSedit1']) OR isset($_COOKIE['phpCMSedit2']))
	AND !isset($_REQUEST['phpcmsaction'])
	AND !in_array('debug', array_keys($PHPCMS->_query_string),TRUE)) {
	$PHPCMS->set_environment_var('POST', 'phpcmsaction', 'EDIT');
	$PHP->NoCache();
}

$plugindir = $DEFAULTS->PLUGINDIR;

// continue with parsing a given content-file...
if (isset ($_GET ['file']) && !isset($HTTP_POST_VARS['phpcmsaction'] ) AND !isset ($HTTP_GET_VARS['phpcmsaction'])) {
	// write raw statistics entry
	if($DEFAULTS->STATS == 'on') {
		include(PHPCMS_INCLUDEPATH.'/lib.log_stats_phpcms.php');
	}
	// write referrer log
	// to be displayed with topref.php plug-in or alternative script
	if($DEFAULTS->REFERRER == 'on') {
		include(PHPCMS_INCLUDEPATH.'/lib.log_referrer_phpcms.php');
	 }
	// Load the i18n Handler
	if (isset ($_GET ['file']) && isset($DEFAULTS->I18N) && 'on' == $DEFAULTS->I18N) {
		include(PHPCMS_INCLUDEPATH.'/class.lib_i18n_phpcms.php');
		$I18N = &new i18n;
	}
	$PHPCMS->check_secure_stealth();
	include(PHPCMS_INCLUDEPATH.'/class.cache_phpcms.php');
	exit;
}

// ... else load the GUI...
switch(strtoupper($_REQUEST['phpcmsaction'])) {
	case 'FRAMESET':
		include(PHPCMS_INCLUDEPATH.'/class.layout_phpcms.php');
		DrawFrameset();
		break;

	case 'OPTIONS':
		$INIFILE = PHPCMS_INCLUDEPATH.'/default.php';
		include(PHPCMS_INCLUDEPATH.'/class.layout_phpcms.php');
		include(PHPCMS_INCLUDEPATH.'/class.options_phpcms.php');
		break;

	case 'SPIDER':
		include(PHPCMS_INCLUDEPATH.'/class.layout_phpcms.php');
		include(PHPCMS_INCLUDEPATH.'/class.lib_spider_phpcms.php');
		include(PHPCMS_INCLUDEPATH.'/class.parser_phpcms.php');
		include(PHPCMS_INCLUDEPATH.'/class.spider_phpcms.php');
		break;

	case 'STAT':
		$INIFILE = PHPCMS_INCLUDEPATH.'/default.php';
		include(PHPCMS_INCLUDEPATH.'/class.layout_phpcms.php');
		include(PHPCMS_INCLUDEPATH.'/class.stat_phpcms.php');
		break;

	case 'IMAGES':
		include(PHPCMS_INCLUDEPATH.'/class.images_phpcms.php');
		break;

	case 'FILEMANAGER':
		include(PHPCMS_INCLUDEPATH.'/class.layout_phpcms.php');
		include(PHPCMS_INCLUDEPATH.'/class.filemanager_phpcms.php');
		$FILEMANAGER = new FILEMANAGER;
		break;

	case 'EDIT':
		// Load the i18n Handler
		if (isset ($_GET ['file']) && isset($DEFAULTS->I18N) && 'on' == $DEFAULTS->I18N) {
			include(PHPCMS_INCLUDEPATH.'/class.lib_i18n_phpcms.php');
			$I18N = &new i18n;
		}
		include(PHPCMS_INCLUDEPATH.'/class.edit_phpcms.php');
		break;

	case 'NAV':
		include(PHPCMS_INCLUDEPATH.'/class.layout_phpcms.php');
		DrawNavi();
		break;

	case 'LOGOUT':
		include(PHPCMS_INCLUDEPATH.'/class.layout_phpcms.php');
		break;

	case 'UPDATE':
		include(PHPCMS_INCLUDEPATH.'/class.layout_phpcms.php');
		CheckUpdate();
		break;

	case 'SEARCH':
		include(PHPCMS_INCLUDEPATH.'/class.search_phpcms.php');
		break;

	case 'HTTPINDEX':
		include(PHPCMS_INCLUDEPATH.'/class.http_indexer_phpcms.php');
		break;

	default:
		// Load the i18n Handler
		if (isset ($_GET ['file']) && isset($DEFAULTS->I18N) && 'on' == $DEFAULTS->I18N) {
			include(PHPCMS_INCLUDEPATH.'/class.lib_i18n_phpcms.php');
			$I18N = &new i18n;
		}
		$PHPCMS->check_secure_stealth();
		include(PHPCMS_INCLUDEPATH.'/class.cache_phpcms.php');
}

?>
