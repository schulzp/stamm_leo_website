<?php
/* $Id: class.edit_phpcms.php,v 1.7.2.40 2006/06/18 18:07:29 ignatius0815 Exp $ */
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
   |    Martin Jahn (mjahn)
   |    Henning Poerschke (hpoe)
   |    Markus Richert (e157m369)
   |    Thilo Wagner (ignatius0815)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

if($DEFAULTS->ENABLE_ONLINE_EDITOR == 'on') {

	class CheckFile {
		function CheckFile() {
			global
				$DEFAULTS,
				$show,
				$PHP,
				$MESSAGES,
				$PHPCMS;

			$DEFAULTS->StartPage = $DEFAULTS->SCRIPT_PATH.'/'.$DEFAULTS->SCRIPT_NAME.'?phpcmsaction=FRAMESET';

			$PfadUndDatei = $this->GetFile();
			$this->name = basename($PfadUndDatei);
			$this->path = dirname($PfadUndDatei);

			if($this->path == '\\' OR $this->path == '/') {
				$this->path = '';
			}

			if(!file_exists($DEFAULTS->DOCUMENT_ROOT.$this->path.'/'.$this->name)) {
				ExitError(7, $DEFAULTS->DOCUMENT_ROOT.$this->path.'/'.$this->name);
			}

			if(substr($this->name, -strlen($DEFAULTS->PAGE_EXTENSION)) != $DEFAULTS->PAGE_EXTENSION) {
				Header('Location: '.$DEFAULTS->DOMAIN_NAME.$this->path.'/'.$this->name);
				exit;
			}

			// if request is for verification only, answer and exit
			$FILE_NAME = $this->path.'/'.$this->name;
			if((!isset($PHPCMS->_query_string['template']) OR $PHPCMS->_query_string['template'] == '') AND
			   !in_array('debug', array_keys($PHPCMS->_query_string),TRUE) AND ($DEFAULTS->CACHE_CLIENT == 'on')) {
				if(strlen($GLOBALS["HTTP_IF_MODIFIED_SINCE"]) > 0) {
					$OrigDate = trim(gmdate("D, d M	Y H:i:s", filemtime($DEFAULTS->DOCUMENT_ROOT.$FILE_NAME))." GMT");
					$RequestDate = trim($GLOBALS["HTTP_IF_MODIFIED_SINCE"]);
					if($OrigDate == $RequestDate) {
						if($PHP->API() == 'cgi') {
							Header('Status: 304 Not Modified');
						} else {
							Header('HTTP/1.1 304 Not Modified');
						}
						exit;
					}
				}
			}
			// check for newer cached page
			$CACHE_PATH = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->CACHE_DIR;
			$FILE_NAME = str_replace("/", "_", $FILE_NAME);
			$this->CACHE_PAGE = $CACHE_PATH.'/'.$FILE_NAME;

			if($DEFAULTS->CACHE_STATE != 'on' OR isset ($PHPCMS->_query_string['template'])) {
				$this->CACHE = false;
				if($DEFAULTS->GZIP == 'on' AND !stristr($FILE_NAME, $DEFAULTS->DYN_EXTENSION)) {
					$FILE_NAME = str_replace($DEFAULTS->PAGE_EXTENSION, '.gz', $FILE_NAME);
					$this->CACHE_PAGE = $CACHE_PATH.'/'.$FILE_NAME;
				}
				return;
			}

			if(file_exists($this->CACHE_PAGE)) {
				$PageFiletime = filemtime($DEFAULTS->DOCUMENT_ROOT.$this->path.'/'.$this->name);
				$CachedPageFileTime = filemtime($this->CACHE_PAGE);
				if($PageFiletime < $CachedPageFileTime) {
					$this->CACHE = true;
				} else {
					$this->CACHE = false;
				}
				return;
			}

			$temp = $CACHE_PATH.'/'.str_replace($DEFAULTS->PAGE_EXTENSION, '.gz', $FILE_NAME);
			if(file_exists($temp)) {
				$this->CACHE_PAGE = $temp;
				$PageFiletime = filemtime($DEFAULTS->DOCUMENT_ROOT.$this->path.'/'.$this->name);
				$CachedPageFileTime = filemtime($this->CACHE_PAGE);
				if($PageFiletime < $CachedPageFileTime) {
					$this->CACHE = true;
				} else {
					$this->CACHE = false;
				}
				return;
			}

			$temp = $CACHE_PATH.'/'.str_replace($DEFAULTS->PAGE_EXTENSION, $DEFAULTS->DYN_EXTENSION, $FILE_NAME);
			if(file_exists($temp)) {
				$this->CACHE_PAGE = $temp;
				$PageFiletime = filemtime($DEFAULTS->DOCUMENT_ROOT.$this->path.'/'.$this->name);
				$CachedPageFileTime = filemtime	($this->CACHE_PAGE);
				if($PageFiletime < $CachedPageFileTime) {
					$this->CACHE = true;
				} else {
					$this->CACHE = false;
				}
				return;
			}

			if($DEFAULTS->GZIP == 'on'){
				$FILE_NAME = str_replace($DEFAULTS->PAGE_EXTENSION, '.gz', $FILE_NAME);
				$this->CACHE_PAGE = $CACHE_PATH.'/'.$FILE_NAME;
			}
		}

		function GetFile() {
			global
				$QUERY_STRING,
				$DEFAULTS;

			if(stristr($QUERY_STRING, 'FILE=')) {
				// extracting filequery
				$pos = strpos(strtoupper($QUERY_STRING), 'FILE=');

				$temp = substr($QUERY_STRING, $pos + 5);
				if($pos = strpos($temp, '?')) {
					$temp = substr($temp, 0, $pos);
				}
				if($pos = strpos($temp, '&')) {
					$temp = substr($temp, 0, $pos);
				}

				// filequery is empty? -> set the defaultvalue
				if(trim($temp) == '') {
					$temp = '/'.$DEFAULTS->PAGE_DEFAULTNAME.$DEFAULTS->PAGE_EXTENSION;
				}
				// filequery exists, but filename is empty? -> set the defaultvalue for filename
				if(!stristr($temp, $DEFAULTS->PAGE_EXTENSION)) {
					if(substr($temp, -1) != '/') {
						$temp = trim($temp).'/'.$DEFAULTS->PAGE_DEFAULTNAME.$DEFAULTS->PAGE_EXTENSION;
					} else {
						$temp = trim($temp).$DEFAULTS->PAGE_DEFAULTNAME.$DEFAULTS->PAGE_EXTENSION;
					}
				}
			}

			if(strlen($temp) == 0) {
				Header('Location: '.$DEFAULTS->DOMAIN_NAME.$DEFAULTS->StartPage);
				exit;
			} else {
				return $temp;
			}
		}
	}

	function validPassword($pw) {
		global $DEFAULTS, $EDIT_PASSWORDS;

		if (isset($DEFAULTS->EDITPASSWORD)) {
			$pw_array = split(',', $DEFAULTS->EDITPASSWORD);
		}
		else {
			$pw_array = array();
		}
		foreach($pw_array as $key => $value) {
			$EDIT_PASSWORDS['plain'][$key] = $value = trim($value);
			$EDIT_PASSWORDS['crypt'][$key] = $value = md5($value);
			if($pw == $value) {
				return true;
			}
		}
		return false;
	}

	function checkPassword($pass) {
		global $DEFAULTS, $EDIT_PASSWORDS;

		$nopasses = array('YOURPASSWORDHERE', 'PHPCMS', 'CMS', 'TEST', 'TESTER', 'PASS', 'PASSWORD', 'PASSWORT');

		foreach($nopasses as $value) {
			if($pass == md5($value)) {
				return false;
			}
		}
		foreach($EDIT_PASSWORDS['crypt'] as $key => $value) {
			if($pass == $value) {
				if(strlen($EDIT_PASSWORDS['plain'][$key]) < $DEFAULTS->PASS_MIN_LENGTH) {
					return false;
				}
			}
		}
		return true;
	}

	include(PHPCMS_INCLUDEPATH.'/class.parser_phpcms.php');
	include(PHPCMS_INCLUDEPATH.'/language.'.$DEFAULTS->LANGUAGE);

	if(!extension_loaded('zlib')) {
		$DEFAULTS->GZIP = 'off';
	}
	include(PHPCMS_INCLUDEPATH.'/class.gzip_phpcms.php');

	$CHECK_PAGE = new CheckFile;
	$GZIP = new gzip;
	$HELPER = new helper;
	$PAGE = new Page;

	$DEFAULTS->GZIP = 'off';
	$DEFAULTS->CACHE_STATE = 'off';
	$DEFAULTS->CACHE_CLIENT = 'off';
	$DEFAULTS->SCRIPT_PATH = $PHP->GetScriptPath();
	$DEFAULTS->SCRIPT_NAME = $PHP->GetScriptName();
	$DEFAULTS->SELF = $DEFAULTS->SCRIPT_PATH.'/'.$DEFAULTS->SCRIPT_NAME;

	$pageURL = $CHECK_PAGE->path.'/'.$CHECK_PAGE->name;
	if($DEFAULTS->STEALTH == 'off') {
		$pageURL = $DEFAULTS->SELF.'?file='.$pageURL;
	}

	if(isset($seceret)) {
		$seceret = md5($seceret);
	}
	// check for posted password and set initial cookies
	if(isset($seceret) AND validPassword($seceret)) {
		$login = checkPassword($seceret);
		setcookie("phpCMSedit1", $seceret, time() + 3600, "/", "", 0);
		setcookie("phpCMSedit2", $seceret, time() + 3600, "/", "", 0);
	}
	// check for each of the cookies with valid password
	if(isset($_COOKIE['phpCMSedit2']) AND validPassword($_COOKIE['phpCMSedit2'])) {
		$login = checkPassword($_COOKIE['phpCMSedit2']);
	} elseif (isset($_COOKIE['phpCMSedit1']) AND validPassword($_COOKIE['phpCMSedit1'])) {
		$login = checkPassword($_COOKIE['phpCMSedit1']);
	}
	if(isset($login) && $login != '') {
		// reset cookie not coming to timeout while working
		if(isset($_COOKIE['phpCMSedit1']) AND !isset($_COOKIE['phpCMSedit2'])) {
			setcookie("phpCMSedit2", $seceret, time() + 3600, "/", "", 0);
		} elseif(!isset($_COOKIE['phpCMSedit1']) AND isset($_COOKIE['phpCMSedit2'])) {
			setcookie("phpCMSedit1", $seceret, time() + 3600, "/", "", 0);
		}

		if(!isset($_POST['EDITACTION'])) {
			$_POST['EDITACTION'] = 'VIEW';
		}
		switch(strtoupper($_POST['EDITACTION'])) {
			case 'EDIT':
				$DEFAULTS->EDIT = 'on';
				$DEFAULTS->DOEDIT = 'on';
				unset($PAGE);
				$PAGE = new Page;
				foreach($_POST as $key => $value) {
					stripslashes($key);
					if($key == 'EDITACTION') {
						continue;
					}
					if(isset($PAGE->content->$key)) {
						unset($PAGE->content->$key);
						if($_POST['DECODE']) {
							$PAGE->content->{$key}[0] = stripslashes(urldecode($value));
						} else {
							$PAGE->content->{$key}[0] = stripslashes($value);
						}
					}
				}
				$MENU = new menu;
				$MENU->TEMPLATE = new menutemplate;
				$DEFAULTS->TEMPLATE = new template($DEFAULTS->TEMPLATE);
				$DEFAULTS->TEMPLATE->content->lines = $DEFAULTS->TEMPLATE->PreParse($DEFAULTS->TEMPLATE->content->lines);

				// phpMail2Crypt
				include(PHPCMS_INCLUDEPATH.'/class.mail2crypt_phpcms.php');
				$Mail2Crypt = new Mail2Crypt();
				$DEFAULTS->TEMPLATE->content->lines = $Mail2Crypt->crypt_mailto($DEFAULTS->TEMPLATE->content->lines);

				//$Ausgabe = count($DEFAULTS->TEMPLATE->content->lines);
				//$GZIP->gwrite($DEFAULTS->TEMPLATE->content->lines);
				$GZIP->gzipPassthru($DEFAULTS->TEMPLATE->content->lines);
				if(file_exists($CHECK_PAGE->CACHE_PAGE)) {
					unlink($CHECK_PAGE->CACHE_PAGE);
				}
				exit;

			case 'VIEW':
				$DEFAULTS->EDIT = 'on';
				$DEFAULTS->GZIP = 'off';
				$DEFAULTS->CACHE_STATE = 'off';
				$DEFAULTS->DOEDIT = 'off';
				$k = 0;
				if(isset($_POST)) {
					foreach($_POST as $key => $value) {
						if ($key == 'EDITACTION') {
							continue;
						}
						if(isset($PAGE->content->$key)) {
							$DEFAULTS->EDIT_FIELDS[$k]['name'] = $key;
							$DEFAULTS->EDIT_FIELDS[$k]['value'] = urlencode($value);
							$k++;
						}
					}
				}
				$DEFAULTS->EDIT_FIELDS[$k]['name'] = 'DECODE';
				$DEFAULTS->EDIT_FIELDS[$k]['value'] = 'TRUE';
				unset($PAGE);
				$PAGE = new Page;
				if(isset($_POST)) {
					foreach($_POST as $key => $value) {
						if ($key == 'EDITACTION') {
							continue;
						}
						if(isset($PAGE->content->$key)) {
							unset($PAGE->content->$key);
							$PAGE->content->{$key}[0] = stripslashes($value);
						}
					}
				}
				$MENU = new menu;
				$MENU->TEMPLATE = new menutemplate;
				$DEFAULTS->TEMPLATE = new template($DEFAULTS->TEMPLATE);
				$DEFAULTS->TEMPLATE->content->lines = $DEFAULTS->TEMPLATE->PreParse($DEFAULTS->TEMPLATE->content->lines);

				// phpMail2Crypt
				include(PHPCMS_INCLUDEPATH.'/class.mail2crypt_phpcms.php');
				$Mail2Crypt = new Mail2Crypt();
				$DEFAULTS->TEMPLATE->content->lines = $Mail2Crypt->crypt_mailto($DEFAULTS->TEMPLATE->content->lines);

				//$Ausgabe = count ( $DEFAULTS->TEMPLATE->content->lines );
				//$GZIP->gwrite($DEFAULTS->TEMPLATE->content->lines);
				$GZIP->gzipPassthru($DEFAULTS->TEMPLATE->content->lines);
				if(file_exists($CHECK_PAGE->CACHE_PAGE)) {
					unlink($CHECK_PAGE->CACHE_PAGE);
				}
				exit;

			case 'SAVE':
				$DEFAULTS->EDIT = 'on';
				$DEFAULTS->GZIP = 'off';
				$DEFAULTS->CACHE_STATE = 'off';
				$DEFAULTS->DOEDIT = 'off';
				unset($PAGE);
				$PAGE = new Page;
				foreach($_POST as $key => $value) {
					if ($key == 'EDITACTION') {
						continue;
					}
					if(isset($PAGE->content->$key)) {
						unset($PAGE->content->$key);
						if(isset($_POST['DECODE']) && $_POST['DECODE'] != '') {
							$PAGE->content->{$key}[0] = stripslashes(urldecode($value));
						} else {
							$PAGE->content->{$key}[0] = stripslashes($value);
						}
					}
				}

				// BOF (mjahn) NEW_FEATURE
				// integration an interface for creating backups if the online-editor saves the file
				if(file_exists(PHPCMS_INCLUDEPATH.'/class.edit_backup_phpcms.php')) {
					include(PHPCMS_INCLUDEPATH.'/class.edit_backup_phpcms.php');
				}
				// EOF (mjahn)

				if($fp = fopen($DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'/'.$CHECK_PAGE->name, "w+")) {
					while(list($key, $value) = each($PAGE->content)) {
						if($key == 'lines') {
							continue;
						} elseif($key == 'tags') {
							continue;
						} elseif (stristr ($key, 'CONTENT_PLUGIN_')) {
							// field is a plugin field -> get number of the content-plugin
							$number = substr ($key, 15, strlen ($key) - 14);

							// get the full entry of the plugin
							// get the type of the plugin
							// and create a correct entry for the contentfile
							$key = 'PLUGIN FILE="'.$PAGE->PLUGIN [$number] ['path_orig'].'" TYPE="'.$PAGE->PLUGIN [$number] ['type'].'"';
							$value = array ('');
						}
						$Result = $DEFAULTS->START_FIELD.$key.$DEFAULTS->STOP_FIELD.join('', $value)."\n";
						fwrite($fp, $Result, strlen($Result));
					}
					fclose($fp);
				} else {
					echo 'File error';
					exit;
				}
				if(file_exists($CHECK_PAGE->CACHE_PAGE)) {
					unlink($CHECK_PAGE->CACHE_PAGE);
				}
				Header('Location: '.$DEFAULTS->DOMAIN_NAME.$pageURL."\n");
				exit;

			case 'LOGOUT':
				setcookie("phpCMSedit1", '', time() - 2592000, '/', '', 0);
				setcookie("phpCMSedit2", '', time() - 2592000, '/', '', 0);
				if(file_exists($CHECK_PAGE->CACHE_PAGE)) {
					unlink($CHECK_PAGE->CACHE_PAGE);
				}
				Header('Location: '.$DEFAULTS->DOMAIN_NAME.$pageURL."\n");
				exit;
		}
	} else {
		if(isset($seceret) AND validPassword($seceret) AND !checkPassword($seceret)) {
			$message = $MESSAGES[51];
		} else {
			$message = $MESSAGES[52];
		}
		$font = '<font face="Verdana, Helvetica, Arial, sans-serif" size="2">';
		echo '<html>'.
			'<head>'.
			'<title>'.$MESSAGES[50].'</title>'.
			'</head>'.
			'<body onLoad="document.LOGIN.seceret.focus()">'.
			'<form method="POST" name="LOGIN" action="'.$pageURL.'">'.
			'<input type="HIDDEN" name="phpcmsaction" value="EDIT">'.
			'<input type="HIDDEN" name="EDITACTION" value="VIEW">'.
			'<table border="0" cellspacing="0" cellpadding="0" width="100%" height="100%">'.
			'<tr><td align="CENTER">'.
			'<table border="0" cellspacing="0" cellpadding="2" bgcolor="#006600"><tr><td>'.
			'<table border="0" cellspacing="0" cellpadding="3" bgcolor="#EEFFEE">'.
			'<tr><td colspan="2" bgcolor="#EEFFEE">'.$font.$message.'</font></td></tr>'.
			'<tr><td bgcolor="#EEFFEE"><table border="0" cellspacing="0" cellpadding="3"><tr><td>'.$font.'Login</font></td>'.
			'<td>'.$font.'<input type="PASSWORD" name="seceret" value="" size="15" maxsize="20"></td></tr>'.
			'<tr><td>&nbsp;</td><td>'.$font.'<input type="SUBMIT" name="SUBMIT" value="'.$MESSAGES[112].'"></td></tr>'.
			'</table></td></tr></table></td></tr></table>'.
			'</td></tr>'.
			'</table>'.
			'</form></body>'.
			'</html>';
		exit;
	}
} else {
	include(PHPCMS_INCLUDEPATH.'/class.cache_phpcms.php');
}

?>
