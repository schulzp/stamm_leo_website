<?php
/* $Id: counter.php,v 1.1.1.1.2.10 2006/06/18 18:10:47 ignatius0815 Exp $ */
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
   |    Markus Richert (e157m369)
   |    Tobias Doenz (tobiasd)
   +----------------------------------------------------------------------+
*/
// important: ensure that the plugin can't be executed directly:
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

// Counter Plugin:
//
// [DE] Demo-Plugin für den Einsatz von Session in phpCMS 1.2.0.
//      Verwendet die Session-Klasse von phpCMS (Achtung: Schreibrechte
//      für PHP auf das Verzeichnis "/parser/session/" notwendig!)
//      Einbindung des Plugins in einem Template oder einer Contentdatei:
//      {PLUGIN FILE="$plugindir/counter.php" TYPE="DYNAMIC"}
//      Plugin-Tag: <!-- PLUGIN:COUNTER -->
//                  (enthält die Anzahl der Besuche in der aktuellen Session)
//
// [EN] Demo plugin for the use of session with phpCMS 1.2.0.
//      This plugin uses the session class of phpCMS (Note: PHP needs write
//      access for the directory "/parser/session/"!)
//      Include the plugin in a template or content file with:
//      {PLUGIN FILE="$plugindir/counter.php" TYPE="DYNAMIC"}
//      Plugin tag: <!-- PLUGIN:COUNTER -->
//                  (contains the number of visits in the actual session)

/***************************************************************************/

// include the session class (and create a session object)
if(defined("PHPCMS_INCLUDEPATH"))
{
	require_once(PHPCMS_INCLUDEPATH.'/class.session_phpcms.php');
	// session defined as global
	// (necessary because plugins are executed inside a function)
	$GLOBALS['session'] = new session;
}

if(!function_exists('CountUp')) {
	// increments the counter value
	function CountUp() {

		global $session;

		if(isset($session->vars['counter'])) {
			$session->vars['counter']++;
		} else {
			$session->vars['counter'] = 1;
		}
		return $session->vars['counter'];

	} // end CountUp
}

// add the tag for the counter plugin (at the end of the tag-array)
$current = count($Tags);
$Tags[$current][0] = '<!-- PLUGIN:COUNTER -->';
$Tags[$current][1] = CountUp();

// close the session
if(isset($GLOBALS['session'])) {
	$GLOBALS['session']->close();
}

?>
