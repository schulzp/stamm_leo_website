<?php
/* $Id: class.debug_phpcms.php,v 1.1.2.17 2006/06/18 18:07:29 ignatius0815 Exp $ */
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
   |    Markus Richert (e157m369)
   |    Tobias Dönz (tobiasd)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

/*
********************************************
  class DEBUG in class.debug_phpcms.php

  created the basics:
             Michael Brauchl
  rebuild and extended:
             Markus Richert, 2003-03-10

  purpose:   This is the phpCMS Debug-Mode

             Self-construkting class by call
             with 'new DEBUG'.
********************************************
*/

class DEBUG {
	var $debug_mode;
	var $RU;
	var $DEBUG_PASS;
	var $font = '<font size="2" face="Verdana, Arial, Helvetica, sans-serif">';

	function DEBUG() {
		global $PHPCMS, $DEFAULTS, $PAGE, $MENU, $HELPER, $start, $_POST, $MESSAGES, $seceret, $_COOKIE;

		$this->debug_mode = 'DEBUG';

		// set the lowercase $PHPCMS->_request_uri-array used for case-save comparisons
		$PHPCMS->set_case_insensitive_keys($PHPCMS->_REQUEST_URI, '$this->_request_uri', '', '');
		// set the RU-link
		unset($PHPCMS->_request_uri['debug']);
		$this->RU = $PHPCMS->restore_request_string_from_array($PHPCMS->_request_uri, '', '').'?debug=';

		// check password
		if(isset($seceret)) {
			$seceret = md5($seceret);
		}
		// check for posted password and set initial cookies
		if(isset($seceret) AND $this->validPassword($seceret)) {
			$login = $this->checkPassword($seceret);
			setcookie("phpCMSdebug1", $seceret, time() + 3600, "/", "", 0);
			setcookie("phpCMSdebug2", $seceret, time() + 1800, "/", "", 0);
		}
		// check for each of the cookies with valid password
		if(isset($_COOKIE['phpCMSdebug2']) AND $this->validPassword($_COOKIE['phpCMSdebug2'])) {
			$login = $this->checkPassword($_COOKIE['phpCMSdebug2']);
		} elseif (isset($_COOKIE['phpCMSdebug1']) AND $this->validPassword($_COOKIE['phpCMSdebug1'])) {
			$login = $this->checkPassword($_COOKIE['phpCMSdebug1']);
		}
		if(isset($login) && $login != '') {
			// reset cookie not coming to timeout while working
			if(isset($_COOKIE['phpCMSdebug1']) AND !isset($_COOKIE['phpCMSdebug2'])) {
				setcookie("phpCMSdebug2", $seceret, time() + 3600, "/", "", 0);
			} elseif(!isset($_COOKIE['phpCMSdebug1']) AND isset($_COOKIE['phpCMSdebug2'])) {
				setcookie("phpCMSdebug1", $seceret, time() + 3600, "/", "", 0);
			}

			// switch on actions
			switch(strtoupper($GLOBALS['_REQUEST']['debug'])) {
				case 'CONTENT_FILE':
					$this->PrintFile($PAGE->content->lines, 'Content-File');
					break;
				case 'CONTENT_REP':
					for($i = 0; $i < count($PAGE->content->lines); $i++) {
						$PAGE->content->lines[$i] = $HELPER->ChangeTags($PAGE->content->lines[$i], $PAGE->tagfile->tags);
					}
					$this->PrintFile($PAGE->content->lines, 'Content-File (Tag-Replaced)');
					break;
				case 'TEMPLATE_FILE':
					$DEFAULTS->TEMPLATE = new template($DEFAULTS->TEMPLATE);
					$this->PrintFile($DEFAULTS->TEMPLATE->content->lines, 'Template-File');
					break;
				case 'TEMPLATE_REP':
					$DEFAULTS->TEMPLATE = new template($DEFAULTS->TEMPLATE);
					$this->PrintFile($DEFAULTS->TEMPLATE->PreParse($DEFAULTS->TEMPLATE->content->lines), 'Template-File (Field- & Tag-Replaced)');
					break;
				case 'TAGS':
					$this->PrintTags();
					break;
				case 'MENU_FILE':
					$this->PrintFile($MENU->content->lines, 'Menu-File', false);
					break;
				case 'MENU_NAME':
					$this->PrintMenu('MENU (sort by NAME)', 'menuname', 'menuKlasse');
					break;
				case 'MENU_CLASS':
					$this->PrintMenu('MENU (sort by CLASS)', 'menuKlasse', 'menuname');
					break;
				case 'PAGE':
					$this->debug_mode = 'PAGE';
					break;
				case 'TIME':
					$DEFAULTS->TEMPLATE = new template($DEFAULTS->TEMPLATE);
					$DEFAULTS->TEMPLATE->content->lines = $DEFAULTS->TEMPLATE->PreParse($DEFAULTS->TEMPLATE->content->lines);
					echo $this->PrintTimeRow(true);
					for($i = 0; $i < count($DEFAULTS->TEMPLATE->content->lines); $i++) {
						echo $DEFAULTS->TEMPLATE->content->lines[$i];
					}
					break;
				case 'VARS':
					$this->PrintDefaults();
					break;
				case 'DEV':
					$this->PrintArray('$GLOBALS');
					break;
				case 'DEV_EXT':
					$this->PrintArray($_POST['debug_load_array']);
					break;
				case 'DEBUGMENU':
					$this->PrintDebugMenu();
					break;
				case 'DEBUGCMD':
					$this->PrintCMD();
					break;
				case 'LOGOUT':
					setcookie("phpCMSdebug1", $seceret, time() - 2592000, '/', '', 0);
					setcookie("phpCMSdebug2", $seceret, time() - 2592000, '/', '', 0);
					unset($login);
					break;
				default:
					$this->PrintFrameset();
			}
		}
		if(!isset($login) || $login == '') {
			// display login box
			if(isset($seceret) AND $this->validPassword($seceret) AND !$this->checkPassword($seceret)) {
				$message = 'Your admin password is not<br />individually set or too short.<br />Please set it in default.php';
			} else {
				$message = 'The phpCMS DebugTool requires<br />login with your admin password!';
			}
			echo '<html>'.
				'<head>'.
				'<title>phpCMS - DebugTool</title>'.
				'</head>'.
				'<body onLoad="document.LOGIN.seceret.focus()">'.
				'<form method="POST" name="LOGIN" action="'.$this->RU.'DEBUG" target="_top">'.
				'<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%"><tr><td align="center">'.
				'<table border="0" cellspacing="0" cellpadding="2" bgcolor="#006600"><tr><td>'.
				'<table border="0" cellspacing="0" cellpadding="3" bgcolor="#eeeeee">'.
				'<tr><td bgcolor="#EEFFEE">'.$this->font.$message.'</font></td></tr>'.
				'<tr><td bgcolor="#EEFFEE" align="center"><table border="0" cellspacing="0" cellpadding="3">'.
				'<tr><td>'.$this->font.'<input type="PASSWORD" name="seceret" value="" size="15" maxsize="20">&nbsp;<input type="SUBMIT" name="SUBMIT" value="Login"></td></tr>'.
				'</table></td></tr>'.
				'<tr><td bgcolor="#EEFFEE" align="center">'.$this->font.'<a href="'.substr($this->RU, 0, -7).'">Back to PageView</a></td></tr>'.
				'</table></td></tr></table>'.
				'</td></tr></table>'.
				'</form></body>'.
				'</html>';
			exit;
		}
	}

	function validPassword($pw) {
		global $DEFAULTS;

		$value = trim($DEFAULTS->PASS);
		$this->DEBUG_PASS['plain'] = $value;
		$this->DEBUG_PASS['crypt'] = $value = md5($value);
		if($pw == $value) {
			return true;
		}
		return false;
	}

	function checkPassword($pass) {
		global $DEFAULTS;

		$nopasses = array('YOURPASSWORDHERE', 'PHPCMS', 'CMS', 'TEST', 'TESTER', 'PASS', 'PASSWORD', 'PASSWORT');

		foreach($nopasses as $value) {
			if($pass == md5($value)) {
				return false;
			}
		}
		if(strlen($this->DEBUG_PASS['plain']) < $DEFAULTS->PASS_MIN_LENGTH) {
			return false;
		}
		return true;
	}

	function PrintFrameset() {
		echo '<html><head><title>phpCMS - DebugTool</title><meta name="robots" content="noindex, nofollow" />'.
			'<frameset cols="190, *" border="0" frameborder="0" framespacing="0">'.
			'<frame src="'.$this->RU.'DEBUGMENU" name="debug_menu" marginwidth="0" marginheight="0" scrolling="auto">'.
			'	<frameset rows="80, *" border="0" frameborder="0" framespacing="0">'.
			'	<frame src="'.$this->RU.'DEBUGCMD" name="debug_cmd" marginwidth="0" marginheight="0" scrolling="auto" noresize="noresize">'.
			'	<frame src="'.$this->RU.'PAGE" name="debug_file" marginwidth="0" marginheight="0" scrolling="auto" noresize="noresize">'.
			'	</frameset>'.
			'</frameset>'.
			'</head></html>';
	}

	function PrintDebugMenu() {
		$menu = array(
			array(
				array('CONTENT_FILE',  '', 'Content-File'),
				array('CONTENT_REP',   '', 'Content-File', 'Tag-Replaced'),
				array('TEMPLATE_FILE', '', 'Template-File'),
				array('TEMPLATE_REP',  '', 'Template-File', 'Field- & Tag-<br />&nbsp;Replaced'),
				array('TAGS',          '', 'TAGS'),
			),
			array(
				array('MENU_FILE',     '', 'Menu-File'),
				array('MENU_NAME',     '', 'MENU', 'sort by NAME'),
				array('MENU_CLASS',    '', 'MENU', 'sort by CLASS'),
			),
			array(
				array('PAGE',          '', 'Parsed Page'),
				array('TIME',          '', 'Parsed Page', 'with stopped Time,<br />but no SCRIPT or PAX'),
			),
			array(
				array('VARS',          '', '$DEFAULT vars'),
				array('DEV',           '', '$GLOBALS printout'),
			),
			array(
				array('LOGOUT',        '_top', 'Logout'),
			),
		);

		$this->PrintHeader('', '8');
		for($i = 0; $i < count($menu); $i++) {
			for($j = 0; $j < count($menu[$i]); $j++) {

				if(!isset($menu[$i][$j][1]) || $menu[$i][$j][1] == '') {
					$menu[$i][$j][1] = 'debug_file';
				}

				if(isset($menu[$i][$j][3]) && $menu[$i][$j][3] != '') {
					$menu[$i][$j][3] = '<br />('.$menu[$i][$j][3].')';
				}
				else {
					$menu[$i][$j][3] = '';
				}

				echo '<tr><td bgcolor="#eeeeee">'.$this->font.'<a href="'.$this->RU.$menu[$i][$j][0].'" target="'.$menu[$i][$j][1].'">'.$menu[$i][$j][2].'</a>'.$menu[$i][$j][3].'</font></td></tr>';
			}
			echo '<tr><td>&nbsp;</td></tr>';
		}
		$this->PrintFooter();
	}

	function PrintCMD() {
		$this->PrintHeader('', '8');
		echo '<tr><td bgcolor="#eeeeee">'.$this->font.'<form method="post" action="'.$this->RU.'DEV_EXT" target="debug_file"">'.
			'Here you can submit any variable, array or object descriptor to be shown...<br />'.
			'<input type="submit" value="Show me...">&nbsp;<input type="text" name="debug_load_array" value="$_SERVER" size="80"></font></td></tr>';
		$this->PrintFooter();
	}

	function PrintHeader($title = '', $padding = '5') {
		echo '<html><body>';
		$title ? $title = '<h3><font face="Verdana, Arial, Helvetica, sans-serif">'.$title.'</font></h3>' : $title = '';
		echo $title.'<table border="0" cellpadding="'.$padding.'">'."\n";
	}

	function PrintFooter() {
		echo '</table></body></html>'."\n";
	}

	function PrintTimeRow($standalone, $colspan = false) {
		global $PHPCMS;

		$standalone ? $table[0] = '<table>' : $table[0] = '';
		$standalone ? $table[1] = '</table>' : $table[1] = '';

		$colspan ? $colspan = ' colspan="'.$colspan.'"' : $colspan;
		return $table[0].'<tr><td'.$colspan.'>'.$this->font.'Time needed: '.$PHPCMS->TIMER['NEEDED'].'</font></td></tr>'.$table[1];
	}

	function PrintRow($cols) {
		echo '<tr>'."\n";
		if(is_array($cols)) {
			foreach($cols as $col) {
				echo '<td bgcolor="'.$col[0].'"';
				if(isset($col[2])) {
					echo ' nowrap="nowrap"';
				}
				echo '>'.$this->font.htmlspecialchars($col[1]).'</font></td>'."\n";
			}
		}
		echo '</tr>'."\n";
	}

	function PrintSpaceRow($cols) {
		echo '<tr><td colspan="'.$cols.'">'.$this->font.'&nbsp;</font></td></tr>'."\n";
	}

	function PrintTags() {
		global $DEFAULTS, $PAGE;

		$this->PrintHeader('Tags');
		for($i = 0; $i < count($PAGE->tagfile->tags); $i++) {
			if(!stristr($PAGE->tagfile->tags[$i][0], '$pass')) {
				$this->PrintRow(array(array('#dddddd', $PAGE->tagfile->tags[$i][0])));
				$this->PrintRow(array(array('#eeeeee', $PAGE->tagfile->tags[$i][1])));
				$this->PrintSpaceRow('1');
			}
		}
		$this->PrintFooter();
	}

	function PrintFile($array, $title, $withtime = true, $nowrap = true) {
		global $DEFAULTS, $PAGE;

		$this->PrintHeader($title);
		if($withtime) {
			echo $this->PrintTimeRow(false, 2);
		}
		foreach($array as $key => $value) {
			$this->PrintRow(array(array('#dddddd', $key, true), array('#eeeeee', $value, $nowrap)));
		}
		$this->PrintSpaceRow('2');
		$this->PrintFooter();
	}

	function PrintMenu($title, $style, $by) {
		global $MENU;

		// setting the varnames used
		$vars = array('$MENU->'.$style, '$MENU->'.$by, '$MENU->menuFieldNames', '$MENU->menuFieldValues');
		// get the maximum counting of the vars
		foreach($vars as $var) {
			eval('$counter[] = count('.$var.');');
		}
		rsort($counter);
		// pad the vars to this maximum to be able to multisort
		foreach($vars as $var) {
			eval($var.' = array_pad('.$var.', '.$counter[0].', false);');
		}
		// now sort them
		eval('array_multisort('.implode(', ', $vars).');');

		// and print the results
		$this->PrintHeader($title);
		if(is_array($MENU->$style) AND isset($MENU->{$style}[0])) {
			foreach(array_keys($MENU->$style) as $i) {
				$this->PrintRow(
					array(
						array('#cccccc', 'NAME: '.$MENU->menuname[$i], true),
						array('#cccccc', 'CLASS: '.$MENU->menuKlasse[$i], true)
					)
				);
				echo '<tr>';
				if(is_array($MENU->menuFieldNames[$i])) {
					foreach($MENU->menuFieldNames[$i] as $value) {
						echo '<td bgcolor="#dddddd">'.$this->font.htmlspecialchars($value).'</font></td>';
					}
				}
				echo '</tr>';

				if(is_array($MENU->menuFieldValues[$i])) {
					foreach($MENU->menuFieldValues[$i] as $value) {
						echo '<tr>';
						foreach($value as $vvalue) {
							echo '<td bgcolor="#eeeeee">'.$this->font.htmlspecialchars($vvalue).'</font></td>';
						}
						echo '</tr>';
					}
				}
				$this->PrintSpaceRow(count($MENU->menuFieldNames[$i]));
			}
		} else {
			$style == 'menuname' ? $text = 'entries' : $text = 'classes';
			echo '<tr><td>'.$this->font.'No menu-'.$text.' set...</font></td></tr>';
		}
		$this->PrintFooter();
	}

	function PrintDefaults() {
		global $DEFAULTS, $PAGE;

		$this->PrintHeader('Existing Variables in $DEFAULTS Object');
		foreach($DEFAULTS as $key => $value) {
			$this->PrintRow(array(array('#dddddd', '$DEFAULTS->'.$key, true), array('#eeeeee', $value, true)));
		}
		$this->PrintSpaceRow('2');
		$this->PrintFooter();
	}

	function PrintArray($name) {
		// trimming can't be a wrong thing
		$name = trim($name);

		// patterns for regexp
		$v = "[_a-zA-Z][_a-zA-Z0-9]*";
		$o = "(->[_a-zA-Z][_a-zA-Z0-9]*)*";
		$a = "((\[[_a-zA-Z0-9]+\])|(\['[^[:cntrl:]]+'\]))*";

		// if $name has a valid syntax to be a variable, array or object descriptor
		if(eregi("^[\$]$v($o|$a)*$", $name)) {
			$glob = $name;
			// separate the non-object-part for globalizing with eval()
			if(strstr($glob, '->')) {
				$glob = substr($glob, 0, strpos($glob, '->'));
			}
			// separate the non-array-part for globalizing with eval()
			if(strstr($glob, '[')) {
				$glob = substr($glob, 0, strpos($glob, '['));
			}
			// globalize the first string
			eval('global '.$glob.';');

			// set the $var for the print_r
			eval('$var = '.$name.';');
		} else {
			echo $this->font.'<br /><b>The submitted code would cause an error...</b><br />'.
				'It is not a valid variable, array or object descriptor</font>';
			exit;
		}

		// check if the requested variable exists
		if(!$var) {
			// first check the $name's type and handle it
			if(strstr($name, '[')) {
				$des = 'array';
			} else {
				$des = 'variable';
			}
			// $name containing '->'? so it must be an object
			if(strstr($name, '->')) {
				$des = 'object';
			}
			// error and exit
			echo $this->font.'<br /><b>The '.$des.' '.$varname.$rest.' doesn\'t exist...</b><br />'.
				'It might help you to look in <a href="'.$this->RU.'DEV">$GLOBALS printout</a>, which variables exist.</font>';
			exit;
		}

		// still alive? let's answer the request...
		ob_start();
		echo $name.' = ';
		print_r($var);
		$dev .= ob_get_contents();
		ob_end_clean();
		echo '<pre>'.htmlspecialchars($dev);
	}
}

?>