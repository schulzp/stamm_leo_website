<?php
/* $Id: class.lib_session_file_phpcms.php,v 1.3.2.13 2006/06/18 18:07:31 ignatius0815 Exp $ */
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
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

//######################################################################################
// container-klasse für sessions
//######################################################################################
// autor    Michael Brauchl
// datum    18.04.2001
// version  0.0.1
// lizenz   GPL
//######################################################################################
//
// void container
// ==============
// intitiert die session, checkt, ob das session-directory
// vorhanden ist und ob es sich um ein directory handelt.
//
// bool check_session(ID START_TIME, LIFE_TIME)
// ============================================
// überprüft ob es einen container mit dieser ID gibt
// und ob die session noch nicht abgelaufen ist.
//
// STRING         ID         = session-id
// UNIX TIMESTAMP START_TIME = zeit an der die momentane session gestartet wurde.
// UNIX TIMESTAMP LIFE_TIME  = maximale lebenszeit einer session in sekunden.
//
// void import_vars(ID, VARS)
// ==========================
// importiert die werte aus dem container in die übergebene variable VARS. VARS wird
// als zeiger übernommen.
//
// STRING ID   = session-id
// MIXED  VARS = variable der session-klasse
//
// void update_time(ID)
// ====================
// ändert den zeitstempel des containers auf jetzt.
//
// STRING ID   = session-id
//
// void delete(ID)
// ===============
// zerstört die daten im container
//
// STRING ID   = session-id
//
// void collect_garbage(START_TIME, GLOBAL_EXPIRES)
// ================================================
// testet ob es container gibt, die die maximale lebensdauer von containern
// überschritten haben. gibt es solche container werden sie zerstört.
//
// UNIX TIMESTAMP START_TIME     = zeit an der die momentane session gestartet wurde.
// UNIX TIMESTAMP GLOBAL_EXPIRES = maximale lebenszeit einer session in sekunden.
//
// void write_vars(ID, VARS)
// =========================
// schreibt die variablen in VARS in den container.
//
// STRING ID   = session-id
// MIXED  VARS = variable der session-klasse
//
//######################################################################################
//
// neu: erwartet PHPCMS_INCLUDEPATH auf den include-path von phpCMS gesetzt!
//
//######################################################################################

if(!defined("_SESSION_FILE_")) {
	define("_SESSION_FILE_", TRUE);
}
if(!defined("SESSION_DIR")) {
	define ('SESSION_DIR', PHPCMS_INCLUDEPATH.'/../session');
}

class container {
	var $file_pointer;

	function check_dir() {
		debug_lines('check_dir');
		if(!file_exists(SESSION_DIR)) {
			print_error('directory for storing sessions: "'.SESSION_DIR.'" does not exists.', __FILE__, __LINE__);
		}
		if(!is_dir(SESSION_DIR)) {
			print_error('directory for storing sessions: "'.SESSION_DIR.'" is not a directory.', __FILE__, __LINE__);
		}
		return;
	}

	function check_session($ID, $start_time, $life_time) {
		// Testet ob die Session mit der ID eine aktive Session ist oder nicht
		debug_lines('make_container');

		// Wenn eine Datei mit dieser Session-ID existiert
		if(file_exists(SESSION_DIR.'/'.$ID)) {
			// Cache löschen
			clearstatcache();
			// Wenn Session vorhanden aber abgelaufen
			if($start_time > (filemtime(SESSION_DIR.'/'.$ID) + $life_time)) {
				if(!is_dir(SESSION_DIR.'/'.$ID)) {
					unlink (SESSION_DIR.'/'.$ID);
				}
				return false;
			}
			return true;
		}
		return false;
	}

	function import_vars($ID, &$vars) {
		// Diese Funktion importiert die Werte
		// aus dem Inhalt einer session in das
		// array session->vars.
		debug_lines('import_vars');

		$var_array = file(SESSION_DIR.'/'.$ID);
		$count_var_array = count($var_array);
		for($i = 0; $i < $count_var_array; $i++) {
			list($key, $value) = explode('||', $var_array[$i]);
			$value = stripslashes($value);
			$value = str_replace('\\10', chr(10), $value);
			$value = str_replace('\\n\\r', "\n\r", $value);
			$value = str_replace('\\n', "\n", $value);
			$value = str_replace('\\r', "\r", $value);
			$value = str_replace('@@', "\\", $value);
			$vars["$key"] = unserialize($value);
		}
		return;
	}

	function write_vars($ID, $vars) {
		// diese funktion schreibt die werte
		// einer session in das container-objekt
		debug_lines('write_vars');

		if(isset($vars)) {
			$this->file_pointer = @fopen(SESSION_DIR.'/'.$ID, 'w+')
				or die('Fatal error: phpCMS session directory not writable!'."\n".__file__."\n on line ".__line__);
			while(list($key, $value) = each($vars)) {
				$writer = serialize ($value);
				$writer = str_replace("\\", '@@', $writer);
				$writer = str_replace("\n\r", '\\\\n\\\\r', $writer);
				$writer = str_replace("\n", '\\\\n', $writer);
				$writer = str_replace("\r", '\\\\r', $writer);
				$writer = str_replace(chr(10), '\\\\10', $writer);
				addslashes($writer);
				fwrite($this->file_pointer, $key.'||', strlen($key) + 2);
				fwrite($this->file_pointer, $writer."\n", strlen($writer) + 1);
			}
			fclose($this->file_pointer);
		}
	}

	function collect_garbage($start_time, $global_expires) {
		// diese funktion wird am ende jeder session
		// aufgerufen und sieht nach, ob abgelaufene
		// container-objekte im session-dir
		// sind und löscht diese bei bedarf.
		debug_lines('collect_garbage');

		$d = dir(SESSION_DIR);
		while($entry = $d->read()) {
			if ($entry == '.' OR $entry == '..' OR $entry == '.htaccess' OR $entry == 'index.html') {
				continue;
			}
			if($start_time > (filemtime(SESSION_DIR.'/'.$entry) + $global_expires)) {
				if(is_file(SESSION_DIR.'/'.$entry)) {
					unlink(SESSION_DIR.'/'.$entry);
				}

			}
		}
		$d->close();
	}

	function update_time($ID) {
		@touch(SESSION_DIR.'/'.$ID);
	}

	function delete($ID) {
		// löscht container-objekt
		debug_lines('delete');
		if(is_file(SESSION_DIR.'/'.$ID)) {
			unlink(SESSION_DIR.'/'.$ID);
		}
	}

	function container() {
		// es wird getestet, ob das session-Verzeichnis vorhanden ist.
		debug_lines('container');
		$this->check_dir();
	}
}

?>
