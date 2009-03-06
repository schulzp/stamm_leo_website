<?php
/* $Id: class.http_indexer_phpcms.php,v 1.1.2.6 2006/06/18 18:07:29 ignatius0815 Exp $ */
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
   |    Henning Poerschke (hpoe)
   |    Markus Richert (e157m369)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

/* NOTE: this file is mostly identical to "/phpcms_indexer.php" - the standalone HTTP-Indexer */

include(PHPCMS_INCLUDEPATH.'/class.layout_phpcms.php');


// sending some headers
Header("Cache-Control: no-cache, must-revalidate\n"); // HTTP/1.1
Header("Pragma: no-cache\n"); // HTTP/1.0
set_time_limit(0);

// init defaults
$PHPCMS_INDEXER_TEMP_SAVE_PATH = 'temp/';
$PHPCMS_INDEXER_SAVE_FILE_NAME = 'defaults_indexer.php';

// init class
// Cookie-Container initialisieren
// Cookie-Container must be loaded before starting the session.
include (PHPCMS_INCLUDEPATH . '/class.lib_indexer_universal_phpcms.php');

// now the session
if(!defined("_SESSION_")) {
	include (PHPCMS_INCLUDEPATH . '/class.session_phpcms.php');
	$session = new session;
}
if(!defined("_FORM_"))
	include (PHPCMS_INCLUDEPATH . '/class.form_phpcms.php');

if ($DEFAULTS->LANGUAGE == 'us') {
	$DEFAULTS->LANGUAGE = 'en';
}

include (PHPCMS_INCLUDEPATH . '/language.' . $DEFAULTS->LANGUAGE);

include (PHPCMS_INCLUDEPATH . '/class.lib_indexer_login_phpcms.php');
include (PHPCMS_INCLUDEPATH . '/class.lib_indexer_profile_phpcms.php');
include (PHPCMS_INCLUDEPATH . '/class.lib_indexer_liste_phpcms.php');
include (PHPCMS_INCLUDEPATH . '/class.lib_indexer_newindex_phpcms.php');
include (PHPCMS_INCLUDEPATH . '/class.lib_indexer_checksearch_phpcms.php');
include (PHPCMS_INCLUDEPATH . '/class.lib_indexer_analyse_phpcms.php');

if (!isset($formdata->action)) {
	$formdata->action = '';
}

if($formdata->action != 'logout') {
	menu();
}

// #######################################################################
// start hauptprogramm
// #######################################################################
//echo($formdata->action);
switch($formdata->action) {
	// Profiler
	case 'profil':
		input_form();
		break;

	case 'add_start':
		add_start();
		break;

	case 'delete_host':
		delete_host();
		break;

	case 'continue_exklude':
		continue_exklude();
		break;

	case 'delete_exklude':
		delete_exklude();
		break;

	case 'continue_include':
		continue_include();
		break;

	case 'delete_include':
		delete_include();
		break;

	case 'continue_urlchange':
		continue_urlchange();
		break;

	case 'server_options':
		server_options();
		break;

	case 'save_profile':
		last_check();
		break;
	// Profilliste
	case 'select_del_profile';
		show_list();
		break;

	case 'select_generate_index';
		show_list();
		break;

	case 'edit_profile';
		edit_profile();
		break;

	case 'delete_profile';
		delete_profile($formdata->profilname);
		show_list();
		break;

	// Spider
	case 'start_create':
		start_create();
		break;

	case 'start_spider':
		start_spider();
		break;

	case 'get_files':
		get_files();
		break;
	// Indexer
	case 'continue_indexer':
		continue_indexer();
		break;

	case 'continue_merger1':
		merger1();
		break;

	case 'continue_merger2':
		merger2();
		break;

	case 'distribute':
		distribute();
		break;
	// Suche testen
	case 'check_search':
		start_check_search();
		break;

	case 'search_now':
		search_now();
		break;
	// Wortanalyse
	case 'wordanalysis':
		analyse_words();
		break;

	case 'analyse_now':
		analyse_now();
		break;
	// System
	case 'logout':
		$session->destroy();
		$session = new session;
		draw_login();
		break;

	default:
		show_list();
		break;
}

echo '</body>'."\n".'</html>';

?>
