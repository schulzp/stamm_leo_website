<?php
/* $Id: class.session_phpcms.php,v 1.2.2.10 2006/06/18 18:07:32 ignatius0815 Exp $ */
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
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

//#######################################################################
// session klasse
//#######################################################################
// autor	Michael Brauchl
// datum	18.04.2001
// version  0.0.1
// lizenz   GPL
//#######################################################################
// diese klasse implementiert session-management für
// PHP3 und PHP4. PHP4 bietet zwar bereits integriertes
// session-management, nur bei skripten die auf beiden
// systemen laufen sollen ist es leider nicht nutzbar.
//
// die komplette logik ist in der klasse session enthalten.
// die classe container wird von der klasse session benötigt
// und bietet ein objekt zur speicherung der sessions.
//
// die klasse erwartet eine korrekt gesetzte konstante
// PHPCMS_INCLUDEPATH, die auf das include-verzeichnis von
// phpcms zeigt.
//
// void new session
// ================
// legt eine neue session an. hier werden die variablen
// LIFE_TIME, GLOBAL_EXPIRES und START_TIME initiert,
// sowie der container zur speicherung der daten angelegt.
// die sessionID wird hier erzeugt.
//
// BOOL cookie = wahr, wenn ein cookie gesetzt wurde, falsch wenn nicht.
// MIXED vars  = array mit allen session-variablen.
//
// void destroy(void)
// ==============
// löscht sessionID, räumt offene sessions auf, die
// bereits abgelaufen sind, löscht ein cookie, setzt
// die session-variablen zurück.
//
// STRING write_link(URL)
// =============================
// liefert eine korrekte URL für den aufruf einer
// seite mit oder ohne sessionID zurück, je nach dem,
// ob ein cookie gesetzt wurde oder nicht.
//
// STRING URL = zieladresse des links
//
// void kill_var(VAR_NAME)
// =======================
// löscht eine einzelne session-variable.
//
// MIXED VAR_NAME = name der session-variable.
//
// void close(void)
// ================
// diese funktion schließt eine session ab.
// diese funktion MUSS am ende jeder session
// aufegrufen werden, um die session-variablen
// in den container zu schreiben. räumt offene
// sessions auf.
//
// void set_var(NAME, VALUE)
// =========================
// setzt die session-variable NAME auf den
// wert VALUE.
//
// STRING NAME = name der session-variable.
// MIXED VALUE = wert der session-variable.
//
//#######################################################################

if(!defined("_SESSION_")) {
	define("_SESSION_", TRUE);
}

// include container-klasse
// derzeit noch hardcodiert - wird in phpCMS integriert.
if(!defined("_SESSION_FILE_")) {
	include(PHPCMS_INCLUDEPATH.'/class.lib_session_file_phpcms.php');
}
// include error - library
// derzeit noch hardcodiert - wird in phpCMS integriert.
if(!defined("_ERROR_")) {
	include(PHPCMS_INCLUDEPATH.'/class.lib_error_phpcms.php');
}
// set debug-mode
$GLOBALS['error_debug'] = false;

class session {
	var $container;
	var $ID;
	var $vars;
	var $life_time;
	var $start_time;
	var $global_expires;
	var $cookie;

	function set_var($name, $value) {
		debug_lines('set_var');
		$this->vars[$name] = $value;
	}

	function get_var($name) {
		debug_lines('get_var');
		return $this->vars[$name];
	}

	function close() {
		debug_lines('close');
		if(isset($this->vars)) {
			$this->container->write_vars($this->ID, $this->vars);
		}
		$this->container->collect_garbage($this->start_time, $this->global_expires);
	}

	function kill_var($name) {
		debug_lines('kill_var');
		unset($this->vars[$name]);
	}

	function write_link($destination) {
		debug_lines('write_link');
		$parameters = false;

		if(strstr($destination, '?')) {
			$pos = strpos($destination, '?');
			$url = substr($destination, 0, $pos);
			$parameters = substr($destination, $pos + 1);
			if($pos = strpos($parameters, 'ID=')) {
				$PartOne = substr($parameters, 0, $pos);
				$PartTwo = substr($parameters, $pos, strpos($parameters, '&'));
				$parameters = $PartOne.$PartTwo;
				if(strlen(trim($parameters)) == 0) {
					$parameters = false;
				}
			}
		} else {
			$url = $destination;
		}

		if(isset($this->cookie) AND $this->cookie == true) {
			if($parameters) {
				return $url.'?'.$parameters;
			} else {
				return $url;
			}
		} else {
			if($parameters) {
				return $url.'?ID='.$this->ID.'&amp;'.$parameters;
			} else {
				return $url.'?ID='.$this->ID;
			}
		}
	}

	function contains_argv(&$ID) {
		debug_lines('contains_argv');

		if(isset($GLOBALS['HTTP_COOKIE_VARS']['ID']) AND $ID = $GLOBALS['HTTP_COOKIE_VARS']['ID']) {
			$this->cookie = true;
			return true;
		}

		if((isset($GLOBALS['HTTP_POST_VARS']['ID'])) AND ($ID = $GLOBALS['HTTP_POST_VARS']['ID']) ) {
			return true;
		}
		if((isset($GLOBALS['HTTP_GET_VARS']['ID'])) AND ($ID = $GLOBALS['HTTP_GET_VARS'] ['ID']) ) {
			return true;
		}
		return false;
	}

	function destroy() {
		debug_lines('destroy');

		$this->container->delete($this->ID);
		$this->container->collect_garbage($this->start_time, $this->global_expires);

		$temptime = time() - 3600;
		if(isset($this->cookie) AND $this->cookie == true) {
			setcookie("ID", $this->ID, $temptime, "/");
		}
		unset($this->vars);
		unset($this->ID);
		return;
	}

	function make_session_id() {
		// erzeugt eine einmalige Session-ID als Hash mit der Länge 32.
		debug_lines('make_session_id');

		if(isset($this->ID)) {
			print_error('session allready definied!', __FILE__, __LINE__);
		}

		// Wenn SID übergeben und Session vorhanden
		if($this->contains_argv($temp) == true) {
			if($this->container->check_session($temp, $this->start_time, $this->life_time) == true) {
				$this->ID = $temp;
				$temptime = time() + $this->life_time;

				setcookie("ID", $this->ID, $temptime, "/");

				$this->container->import_vars($this->ID, $this->vars);

				$this->container->update_time($this->ID);
				return;
			}
		}

		$this->ID = md5(uniqid(rand()));
		$this->container->update_time($this->ID);
		$temptime = time() + $this->life_time;
		setcookie("ID", $this->ID, $temptime, "/");
		return;
	}

	function session() {
		// initiert die Session
		debug_lines('session');

		// setze die lebenszeit der sessions in sekunden. 60*30 sind 30 minuten.
		$this->life_time = 60 * 30;

		// setzt die lebenszeit von sessions die nicht mehr bedient werden in sekunden
		$this->global_expires = 60 * 60 * 6;
		$this->start_time = time();

		// lege Container für Sessionvariablen an
		$this->container = new container;

		// wenn session-ID übergeben, lese die session-ID, sonst erzeuge neue session-ID.
		$this->make_session_id();
		return;
	}
}

?>
