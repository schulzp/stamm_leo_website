<?php
/* $Id: class.lib_data_file_phpcms.php,v 1.2.2.8 2006/06/18 18:07:30 ignatius0815 Exp $ */
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
// Daten klasse
//#######################################################################
// autor	Michael Brauchl
// datum	18.04.2001
// version  0.0.2
// lizenz   GPL
//#######################################################################
//
// Diese Klasse stellt einen Container zur Verfügung, mit dem sich
// beliebige Datensätze Speichern und auch wiederfinden lassen.
// Für die weitere Entwicklung von phpCMS ist diese Klasse eine wichtige
// Grundlage. Sie sollte von allen PlugIns genutzt werden um Daten zu speichern.
// Ein späterer Umstieg auf eine Datenbank kann dann 'reibungslos'
// gelingen.
//
// Die Anwendung dieser Klasse :
//
// Anlegen eines Datencontainers erfolgt durch angabe eine Pfades:
//
// $myData = new data_container( [PATH] );
//
// Das Schreiben von Daten :
//
// $myData->write_data( ID, FIELDNAME, FILEDVALUE) ;
//
// ID stellt hierbei einen eindeutigen Identifier dar, und kann frei
//	gewählt werden.
//
// FIELDNAME ist, um den Bezug zur Datenbank zu bewahren, die 'Tabelle'.
//		also eine Bezeichnug der Kategorie oder Rubrik oder der
//		Datenart.
//
// FIELDVALUE Stellt den eigentlichen Wert dar, der zur ID Passt.
//
//
// Das Lesen von Daten :
//
// $result = $myData->read_data(ID,FILEDNAME) ;
//
// Mittels dem Paar ID und FIELDNAME lässt sich auf einfache Weise ein
// Wert wieder auslesen.
//
//
// Das löschen von Daten :
//
// $result = $myData->delete_data(ID,FILEDNAME) ;
//
// Wiederum genügt die Angabe der ID und des FIELDNAMES.
//
//
// Das Suchen von Daten :
//
// $IDs = $myData->search_data(FIELDNAME, SEARCHVALUE) ;
//
// Hiermit lässt sich innerhalb einer Tabelle (FIELDNAME) nach
// einem bestimmten Wert suchen. Setzt man für SEARCHVALUE ein '*'
// ein, dann werden alle IDs zurückgegeben.
// Der Rückgabewert ist also immer ein Array.
//
// Beispiel:
//   Überprüfen ob der Wert bereits existiert :
//	 $IDs = $mydata->search_data("NAMEN", "IRGENDEINER");
//	 if ($IDs)
//		// Wert ist bereits vorhanden
//
// Die ganze Liste abarbeiten :
//	$IDs = $mydata->search_data("NAMEN", "*");
//	for ($i=0;$i<count($IDs);$i++) {
//		$data = $mydata->read_data("NAMEN",$IDs[$i]);
//		echo $data;
//	}
//
// Speicherbare Daten :
//
// PHP-Objekte, Strings, Arrays. Kurz alles was sich mittels PHP
// Serialisieren lässt. Wichtig ist zu vermerken das zwar Arrays
// gespeichert werden, sollten diese Arrays allerdings wiederum Arrays
// enthalten, so muß man sich selbst um die Serialisierung kümmern !
//
//####################################################################
//
// BugFix 7.6.2001 Michael Taupitz
// --------------------------------
// Speicherung von Array funktioniert jetzt. Im Array enthaltene
// Arrays werden allerdings nicht berücksichtigt.
//
//####################################################################

if(!defined("_DATACONTAINER_")) {
	define("_DATACONTAINER_", TRUE);
}

class data_container {
	var $DATA_DIR;

	// Sonderzeichen wie Returns müssen angepasst werden zum Serialisieren.
	function pack_data($data) {
		$data = str_replace("\n\r", '\\n\\r', $data);
		$data = str_replace("\n", '\\n', $data);
		$data = str_replace("\r", '\\r', $data);
		$data = str_replace(chr(10), '\\10', $data);
		return $data;
	}

	function unpack_data($data) {
		$data = str_replace('\\n\\r', "\n\r", $data);
		$data = str_replace('\\n', "\n", $data);
		$data = str_replace('\\r', "\r", $data);
		$data = str_replace('\\10', chr(10), $data);
		return $data;
	}

	function data_container($datadir) {
		debug_lines('data_container');

		$this->DATA_DIR = $datadir;
		if(!file_exists($this->DATA_DIR)) {
			print_error ('directory for storing users: "'.$this->DATA_DIR.'" does not exists.', __FILE__, __LINE__);
		}
		if(!is_dir($this->DATA_DIR)) {
			print_error ('directory for storing users: "'.$this->DATA_DIR.'" is not a directory.', __FILE__, __LINE__);
		}
	}

	// Suchfunktion. Liefert ein Array aller gefundenen IDs zurück.
	function search_data($FIELDNAME, $VALUE) {
		debug_lines('search_data');
		$VALUE = stripslashes($VALUE);

		//	if(!file_exists($this->DATA_DIR.'/'.strtoupper($FIELDNAME))) {
		//		print_error('file for searching '.$FIELDNAME.' is not present.', __FILE__, __LINE__);
		//	}
		if(!file_exists($this->DATA_DIR.'/'.strtoupper($FIELDNAME))) {
			return false;
		}
		clearstatcache();
		$fp = fopen($this->DATA_DIR.'/'.strtoupper($FIELDNAME), 'rb+');
		while(!feof($fp)) {
			$buffer = trim(fgets($fp, 4096));
			if(strlen($buffer) > 0) {
				$buffer = stripslashes($buffer);
				$entry = unserialize($buffer);
				$entry_value = unserialize($this->unpack_data($entry[strtoupper($FIELDNAME)]));
				if($entry_value == $VALUE OR $VALUE == '*') {
					$return_array[] = $entry['ID'];
				}
			}
		}
		fclose($fp);
		if(isset($return_array)) {
			return $return_array;
		} else {
			return;
		}
	}

	// Daten einlesen
	function read_data($ID, $FIELDNAME) {
		debug_lines('read_data');

		if(!file_exists($this->DATA_DIR.'/'.strtoupper($FIELDNAME))) {
			// print_error ('file for storing '.$FIELDNAME.' is not present.', __FILE__, __LINE__);
			return;
		}
		clearstatcache();
		$fp = fopen($this->DATA_DIR.'/'.strtoupper($FIELDNAME), 'rb+');
		while(!feof($fp)) {
			$buffer = trim(fgets($fp,4096));
			if(strlen($buffer) > 0) {
				$buffer = stripslashes($buffer);
				$entry = unserialize($buffer);
				if($entry['ID'] == $ID) {
					$ret_val = unserialize($this->unpack_data($entry[$FIELDNAME]));
					return $ret_val;
				}
			}
		}
		fclose($fp);
	}

	// Daten schreiben
	function write_data($ID, $FIELDNAME, $FIELDVALUE) {
		debug_lines('write_data');

		$FIELDVALUE = stripslashes (serialize($FIELDVALUE));
		//	if(is_array($FIELDVALUE)) {
		//		$FIELDVALUE = implode('', $FIELDVALUE);
		//	}
		$entry['ID'] = $ID;
		$FIELDVALUE = $this->pack_data($FIELDVALUE);
		$entry[$FIELDNAME] = $FIELDVALUE;
		$write_data = serialize($entry);
		$write_data = addslashes($write_data)."\n";

		if(!file_exists($this->DATA_DIR.'/'.strtoupper($FIELDNAME))) {
			$fp = fopen ($this->DATA_DIR.'/'.strtoupper($FIELDNAME), 'wb+');
			fputs($fp, $write_data, strlen($write_data));
			fclose($fp);
		} else {
			$found = false;
			clearstatcache();
			$fp = fopen($this->DATA_DIR.'/'.strtoupper($FIELDNAME), 'rb+');
			while(!feof($fp)) {
				$buffer = trim(fgets($fp, 4096));
				if(strlen($buffer) > 0) {
					$buffer = stripslashes($buffer);
					$value_array[] = $buffer;
				}
			}
			fclose($fp);

			$fp = fopen($this->DATA_DIR.'/'.strtoupper($FIELDNAME), 'wb+');
			if(isset($value_array)) {
				$i = count($value_array);
				for($j = 0; $j < $i; $j++) {
					$entry = unserialize($value_array[$j]);
					if($entry['ID'] == $ID) {
						$entry[$FIELDNAME] = $FIELDVALUE;
						$found = true;
					}
					$write_entry = serialize($entry);
					$write_entry = addslashes($write_entry)."\n";
					fputs($fp, $write_entry, strlen($write_entry));
				}
			}
			if(!$found) {
				fputs($fp, $write_data, strlen($write_data));
				fclose($fp);
			}
		}
	}

	// Daten löschen
	function delete_data($ID, $FIELDNAME) {
		debug_lines('delete_data');

		if(!file_exists($this->DATA_DIR.'/'.strtoupper($FIELDNAME))) {
			echo 'no file: '.$this->DATA_DIR.'/'.strtoupper($FIELDNAME);
			return;
		} else {
			$found = false;
			clearstatcache();

			$fp = fopen($this->DATA_DIR.'/'.strtoupper($FIELDNAME), 'rb+');
			while(!feof($fp)) {
				$buffer = fgets($fp, 4096);
				$buffer = stripslashes($buffer);
				$buffer = trim($buffer);
				if(strlen($buffer) > 0) {
					$value_array[] = $buffer;
				}
			}
			fclose($fp);

			$i = count($value_array);
			$fp = fopen($this->DATA_DIR.'/'.strtoupper($FIELDNAME), 'wb+');
			for($j = 0; $j < $i; $j++) {
				$entry = unserialize($value_array[$j]);
				if($entry['ID'] != $ID) {
					$write_entry = serialize($entry);
					$write_entry = addslashes($write_entry)."\n";
					fputs($fp, $write_entry, strlen($write_entry));
				}
			}
			fclose($fp);
		}
	}
}

?>