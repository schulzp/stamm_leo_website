<?php
/* $Id: class.login_phpcms.php,v 1.2.2.9 2006/06/18 18:07:31 ignatius0815 Exp $ */
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

define('PHPCMS_INCLUDEPATH', 'includes/');
//##################################################################
// generische login-funktionen
// läuft standalone und als plugin
// setzt eine zentrale userverwaltung voraus
//
// die konstante PHPCMS_INCLUDEPATH = pfad zu den include-klassen
// muss vor dem Aufruf dieser funktionen gesetzt sein
//
// am ende aller funktionen muß von dem aufrufenden
// programm die funktion clean_exit() aufgerufen werden,
// damit die session geschlossen wird.
//##################################################################

//##################################################################
// testen ob als plugin oder standalone
// und alle erforderlichen libraries einbinden
//##################################################################

if(!defined("_LIBPHPCMS_")) {
	include(PHPCMS_INCLUDEPATH.'/class.lib_phpcms.php');
	$PHP = new LibphpCMS;
}
if(!defined("_DEFAULTS_")) {
	include(PHPCMS_INCLUDEPATH.'/default.php');
	$DEFAULTS = new defaults;
}
if(!defined("_DATACONTAINER_")) {
	include(PHPCMS_INCLUDEPATH.'/class.lib_data_file_phpcms.php');
}
if(!defined("_SESSION_")) {
	include(PHPCMS_INCLUDEPATH.'/class.session_phpcms.php');
	$session = new session;
}
if(!defined("_GROUP_")) {
	include(PHPCMS_INCLUDEPATH.'/class.group_phpcms.php');
}
if(!defined("_USER_")) {
	include(PHPCMS_INCLUDEPATH.'/class.user_phpcms.php');
}
if(!defined("_FORM_")) {
	include(PHPCMS_INCLUDEPATH.'/class.form_phpcms.php');
}


// check phpCMS-installation directory
if(!isset($PHPCMS_INSTALL_DIR)) {
	$lpos = strrpos(PHPCMS_INCLUDEPATH, '/');
	$PHPCMS_INSTALL_DIR = substr(PHPCMS_INCLUDEPATH, 0, $lpos);
}

// check, if there are already groups in the group-directory
function is_group_dir_aviable() {
	global $PHPCMS_INSTALL_DIR;

	if(file_exists($PHPCMS_INSTALL_DIR.'/group/NAME')) {
		return true;
	} else {
		return false;
	}
}

// check, if there is a user in the user-directory an if the superuser already exists
function is_user_dir_aviable() {
	global $PHPCMS_INSTALL_DIR;

	if(file_exists($PHPCMS_INSTALL_DIR.'/user/NICKNAME')) {
		$user = new user($PHPCMS_INSTALL_DIR.'/user');
		if($user->search_user('NICKNAME', 'root')) {
			return true;
		}
	}
	return false;
}

// creates the standard-groups and the superuser "root" with pass "phpcms". opened session needed!
function init() {
	global $PHPCMS_INSTALL_DIR, $session, $DEFAULTS;

	// creating groups
	$group = new group($PHPCMS_INSTALL_DIR.'/group');

	$group->make_new_group();
	$group->NAME = 'ADMIN';
	$group->STORE['DESCRIPTION'] = 'Alle Administratoren von phpCMS.';
	$group->save_group_data($group->GROUPID);

	$group->make_new_group();
	$group->NAME = 'WEBMASTER';
	$group->STORE['DESCRIPTION'] = 'Alle Webmaster von phpCMS.';
	$group->save_group_data($group->GROUPID);

	$group->make_new_group();
	$group->NAME = 'DESIGNER';
	$group->STORE['DESCRIPTION'] = 'Alle Designer von phpCMS.';
	$group->save_group_data($group->GROUPID);

	$group->make_new_group();
	$group->NAME = 'REDAKTEUR';
	$group->STORE['DESCRIPTION'] = 'Alle Redakteure von phpCMS.';
	$group->save_group_data($group->GROUPID);

	$group->make_new_group();
	$group->NAME = 'GUEST';
	$group->STORE['DESCRIPTION'] = 'Websitebesucher und Gäste';
	$group->save_group_data($group->GROUPID);

	// creating superuser
	$user = new user($PHPCMS_INSTALL_DIR.'/user');
	$user->make_new_user();

	$user->VORNAME  = 'root';
	$user->NACHNAME = 'root';
	$user->NICKNAME = 'root';
	$user->EMAIL    = 'root@localhost';
	$user->PASSWORD = 'phpcms';
	$user->GROUPS   = 'ADMIN;WEBMASTER;DESIGNER;REDAKTEUR;GUEST';
	$user->STORE['DESCRIPTION'] = 'Superuser von phpCMS. Dieser User hat alle Rechte';
	$user->save_user_data($user->USERID);
	$session->vars['FIRST_LOGIN'] = true;
}

// check, if the logged-in user is in the given group. needs opened session and a logged-in user.
function is_user_in_group($group) {
	global $session, $PHPCMS_INSTALL_DIR;

	// root is allowed to do everything
	$user = new user($PHPCMS_INSTALL_DIR.'/user');
	$user->get_user_data($session->vars['USERID']);
	if($user->NICKNAME == 'root') {
		return true;
	}
	// check for entrys in $group
	$check_count = 0;
	if(strstr($group, ';')) {
		$temp = explode(';', $group);
		for($i = 0; $i < count($temp); $i++) {
			$temp[$i] = trim ($temp[$i]);
			if(strlen($temp[$i]) == 0) {
				continue;
			}
			$to_check[$check_count] = $temp[$i];
			$check_count++;
		}
	} else {
		$to_check[$check_count] = trim($group);
		$check_count++;
	}
	$valid = FALSE;
	$groupar = explode(';', $session->vars['USERGROUPS']);
	for($i = 0; $i < $check_count; $i++) {
		while(list($k, $groupname) = each($groupar)) {
			if(trim($groupname) == $to_check[$i]) {
				return TRUE;
			}
		}
	}
}

// show loginscreen
function draw_login_screen($title = 'Login') {
	b_write('<html>');
	b_write('<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">');
	b_write('<tr><td align="center">');
	$login_form = new form();
	$login_form->set_select('CHECK_LOGIN');
	$login_form->set_callback('LOGIN');
	$login_form->set_bgcolor('DDEEDD');
	$login_form->add_area('1');
	$login_form->set_area_title('1', $title);
	$login_form->add_area_input_text('1', 'nickname', '10', 'User:');
	$login_form->add_area_input_password('1', 'password', '10', 'Paßwort:');
	$login_form->add_button('submit', 'submit', 'Login');
	$login_form->compose_form();
	b_write ('</td></tr></table></html>');
	return;
}

// checks, if a user is logged in. If not, show loginscreen
function login() {
	global $session, $user, $PHPCMS_INSTALL_DIR;

	$f_vars = new get_form();

	if(isset($f_vars->select) AND $f_vars->select == 'CHECK_LOGIN') {
		$user = new user($PHPCMS_INSTALL_DIR.'/user');

		$ID = $user->search_user('NICKNAME', $f_vars->nickname);
		if(!isset($ID)) {
			draw_login_screen('Falscher Username');
			clean_exit();
		}
		if(count($ID) > 1) {
			echo 'Mehrere User mit dem gleichen Nicknamen gefunden!';
			clean_exit();
		}

		$user->get_user_data($ID[0]);
		if($user->PASSWORD != $f_vars->password) {
			draw_login_screen('Falsches Paßwort');
			clean_exit();
		}
		$session->vars['USERID'] = $ID[0];
		$session->vars['USERGROUPS'] = $user->GROUPS;
	}

	if(!isset($session->vars['USERID'])) {
		draw_login_screen('Login');
		clean_exit();
	}
}

// if the function b_write for printing to screen does not exist, it will be created here
if(!function_exists('bwrite')) {
	function b_write($str) {
		echo $str;
	}
}

// creating the function clean_exit, if it does not exist
if(!function_exists('clean_exit')) {
	function clean_exit() {
		global $session,$logger;
		$session->close();
		exit;
	}
}

// initializing the base-structur
if(!is_group_dir_aviable()) {
	init();
}
if(!is_user_dir_aviable()) {
	init();
}

// starting the login-dialog.
login();

// creating the superuser at the first login.
if(isset($session->vars['FIRST_LOGIN']) AND $session->vars['FIRST_LOGIN'] == true) {
	include(PHPCMS_INCLUDEPATH.'/class.firstlogin_phpcms.php');
	login();
}

?>
