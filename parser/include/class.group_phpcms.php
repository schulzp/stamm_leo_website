<?php
/* $Id: class.group_phpcms.php,v 1.2.2.8 2006/06/18 18:07:29 ignatius0815 Exp $ */
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
// group klasse
//#######################################################################
// autor    Michael Brauchl
// datum    18.04.2001
// version  0.0.1
// lizenz   GPL
//#######################################################################
//
// diese klasse bietet eine interface zu den group-objekten von phpCMS.
//
// void new group
// =============
// legt ein neues group-objekt an. es ist nach der ausführung von
// "new group" noch keine gruppe angelegt.
//
// STRING GROUPID   = eindeutige ID für jeder gruppe
// MIXED STORE      = array zur ablage zusätzlicher objekte
// OBJECT data_container = data_container-klasse wrapper zur db
//
// VOID group(void)
// ===============
// legt einen neuen data_container an.
//
// VOID get_group_data(ID)
// ======================
// liest die daten der gruppe mit der groupid "ID"
// aus dem data_container. die daten werden dann im
// objekt GROUP abgelegt.
//
// STRING ID = groupid länge: 128
//
// VOID save_group_data(ID)
// =======================
// speichert die daten aus dem objekt GROUP im
// data_container unter der groupid "ID".
//
// STRING ID = groupid länge: 128
//
// VOID delete_group(ID)
// ====================
// löscht die gruppe mit der groupid "ID" aus dem
// data_container.
//
// STRING ID = groupid länge: 128
//
// MIXED search_group(FIELD, VALUE)
// ===============================
// durchsucht den gesamten data_container nach allen
// gruppen, bei denen das feld "FIELD" mit dem
// wert "VALUE" übereinstimmt.
// liefert ein array mit den groupids aller
// gefundenen gruppen zurück.
// "*" als wildcard für alle ist erlaubt.
//
// VOID make_new_group(VOID)
// ========================
// legt eine einmalige, neue groupid an.
// die groupid wird im group-objekt abgelegt.
//
//#######################################################################

// include data_container-klasse
// derzeit noch hardcodiert - wird in phpCMS integriert.
// include ('D:/Programme/OpenSA/Apache/htdocs/parser/include/class.lib_data_file_phpcms.php');
// include error - library
// derzeit noch hardcodiert - wird in phpCMS integriert.
// include ('D:/Programme/OpenSA/Apache/htdocs/parser/include/class.lib_error_phpcms.php');
// set debug-mode

if(!defined("_GROUP_")) {
	define("_GROUP_", TRUE);
}

$GLOBALS['error_debug'] = false;

class group {
	var $GROUPID;
	var $NAME;
	var $STORE;
	var $data_container;

	function group($groupdir) {
		debug_lines('group');
		$this->data_container = new data_container($groupdir);
	}

	function get_group_data($ID) {
		debug_lines('get_group_data');
		if(!isset($ID)) {
			print_error('no group-id in call!', __FILE__, __LINE__);
		}
		$this->GROUPID = $ID;
		$this->NAME    = $this->data_container->read_data($ID,'NAME');
		$this->STORE   = $this->data_container->read_data($ID,'STORE');
	}

	function save_group_data($ID) {
		debug_lines('save_group_data');
		if(!isset($ID)) {
			print_error('no group-id in functioncall!', __FILE__, __LINE__);
		}
		$this->data_container->write_data($ID,'NAME',$this->NAME);
		$this->data_container->write_data($ID,'STORE',$this->STORE);
	}

	function delete_group($ID) {
		debug_lines('delete_group');
		if(!isset($ID)) {
			print_error('no group-id in functioncall!', __FILE__, __LINE__);
		}
		$this->data_container->delete_data($ID,'NAME');
		$this->data_container->delete_data($ID,'STORE');
		}

	function search_group($FIELD, $VALUE) {
		debug_lines('search_group');
		if(!isset($FIELD)) {
			print_error ('no field in functioncall!', __FILE__, __LINE__);
		}
		if(!isset($VALUE)) {
			print_error('no value in functioncall!', __FILE__, __LINE__);
		}
		$return_array = $this->data_container->search_data($FIELD, $VALUE);
		return $return_array;
	}

	function make_new_group() {
		debug_lines('make_new_group');
		$this->GROUPID = md5(uniqid(rand()));
	}
}

?>