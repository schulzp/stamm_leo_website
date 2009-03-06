<?php
/* $Id: class.layout_phpcms.php,v 1.12.2.40 2006/06/18 18:07:30 ignatius0815 Exp $ */
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
   |    Beate Paland (beate76)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

if(isset($_GET['language']) && $_GET['language'] != '') {
	include(PHPCMS_INCLUDEPATH.'/language.'.$_GET[language]);
} else {
	include(PHPCMS_INCLUDEPATH.'/language.'.$DEFAULTS->LANGUAGE);
}
$DEFAULTS->SELF = $DEFAULTS->SCRIPT_PATH.'/'.$DEFAULTS->SCRIPT_NAME;

class document {
	function document() {
		global $DEFAULTS;

		$this->BACKGROUND_COLOR   = '#FFFFFF';
		$this->LINK_COLOR         = '#006600';
		$this->VLINK_COLOR        = '#006600';
		$this->ALINK_COLOR        = '#006600';
		$this->DARK_COLOR         = '#006600';
		$this->ROW_DARK           = '#EEEEEE';
		$this->ROW_LIGHT          = '#EEEEEE';
		$this->MENU_LINK_COLOR    = '#FFFFFF';
		$this->LOGIN_BORDER_COLOR = '#006600';
		$this->LOGIN_BG_COLOR     = '#EEFFEE';
		$this->HOMEURL            = 'http://phpcms.de';
		$this->LOGO               = $DEFAULTS->SCRIPT_PATH.'/gif/logo.gif';
		$this->FONT_FACE          = 'Verdana, Helvetica, Arial, sans-serif';
		$this->FONT_SIZE          = '2';
		$this->HEAD_LINE_FONT     = '<font face="'.$this->FONT_FACE.'" size="'.($this->FONT_SIZE+2).'" color="'.$this->MENU_LINK_COLOR.'">';
		$this->TABLE_FONT         = '<font face="'.$this->FONT_FACE.'" size="'.$this->FONT_SIZE.'">';
		$this->SUB_TABLE_FONT     = '<font face="'.$this->FONT_FACE.'" size="'.($this->FONT_SIZE-1).'">';
		$this->STATUS_FONT        = '<font face="'.$this->FONT_FACE.'" size="'.$this->FONT_SIZE.'" color="'.$this->MENU_LINK_COLOR.'">';
		$this->LEAD_FONT          = '<font face="'.$this->FONT_FACE.'" size="'.($this->FONT_SIZE+1).'"><b>';
	}

	function header_html($title, $style='', $additions='', $doctype) {
		global $DEFAULTS;

		switch($doctype) {
			case "xhtml1t":

				$header =
					'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" '."\n".
					' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n".
					'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$DEFAULTS->LANGUAGE.'" lang="'.$DEFAULTS->LANGUAGE.'">'."\n".
					'<head>'."\n".
					'<meta http-equiv="content-type" content="text/html;charset=iso-8859-1" />'."\n".
					'<meta http-equiv="content-style-type" content="text/css" />'."\n".
					'<meta http-equiv="content-script-type" content="text/javascript" />'."\n".
					'<title>'.$title.'</title>'."\n";
				if($style != '') {
					$header .=
						'<style type="text/css" media="screen"><!--/*--><![CDATA[/*><!--*/'."\n".
									$style."\n".
						'/*]]>*/--></style>'."\n";
						}

				if($additions != '') {
					$header .= $additions."\n";
				}
				$header .= '</head>'."\n";
				break;

			case "html4t":

				$header =
					'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" '."\n".
					' "http://www.w3.org/TR/html4/loose.dtd">'."\n".
					'<html>'."\n".
					'<head>'."\n".
					'<meta http-equiv="content-type" content="text/html;charset=iso-8859-1" />'."\n".
					'<meta http-equiv="content-style-type" content="text/css" />'."\n".
					'<meta http-equiv="content-script-type" content="text/javascript" />'."\n".
					'<title>'.$title.'</title>'."\n";
				if($style != '') {
					$header .=
						'<style type="text/css" media="screen"><!--/*--><![CDATA[/*><!--*/'."\n".
									$style."\n".
						'/*]]>*/--></style>'."\n";
						}

				if($additions != '') {
					$header .= $additions."\n";
				}
				$header .= '</head>'."\n";
				break;
		}
		echo $header;
	}

	function header_frameset() {

	}
}


function GetStartPage () {
	global $DEFAULTS,$PHP;
	if($DEFAULTS->STEALTH == 'on') {
		$DEFAULTS->StartPage = $DEFAULTS->SCRIPT_PATH.'/help/index_stealth.'.$DEFAULTS->LANGUAGE.'.html';
	} else {
		$DEFAULTS->StartPage = $DEFAULTS->SCRIPT_PATH.'/'.$DEFAULTS->SCRIPT_NAME.'?file='.$DEFAULTS->SCRIPT_PATH.'/help/index_nonstealth.'.$DEFAULTS->LANGUAGE.'.html';
	}
	$DEFAULTS->NaviPage = $DEFAULTS->SCRIPT_PATH.'/'.$DEFAULTS->SCRIPT_NAME.'?phpcmsaction=NAV';
}

function DrawFrameset() {
	global $DEFAULTS, $PHP;
	GetStartPage();

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" '."\n";
	echo '  "http://www.w3.org/TR/xhtml1/DTD/DTD/xhtml1-frameset.dtd">'."\n";
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$DEFAULTS->LANGUAGE.'" lang="'.$DEFAULTS->LANGUAGE.'">'."\n";
	echo '<head>'."\n";
	echo '<meta http-equiv="content-type" content="text/html;charset=iso-8859-1" />'."\n";
	//echo '<meta http-equiv="content-style-type" content="text/css" />'."\n";
	echo '<meta name="robots" content="noindex,nofollow" />'."\n";
	echo '<title>phpCMS @ '.$GLOBALS["SERVER_NAME"].'</title>'."\n";
	//echo '<style type="text/css" media="screen"><!--/*--><![CDATA[/*><!--*/'."\n";
	//echo 'frameset frame, frameborder {border:0px none;margin:0px;padding:0px;}'."\n";
	//echo '#content {backbround-color:'.$DOCUMENT->BACKGROUND_COLOR.'}'."\n";
	//echo '/*]]>*/--></style>'."\n";
	echo '</head>'."\n";
	echo 	'<frameset cols="190, *">'."\n";
	echo 	'<frame src="'.$DEFAULTS->NaviPage.'" name="navi" scrolling="auto" frameborder="0" marginwidth="0" marginheight="0" />'."\n";
	echo 	'<frame src="'.$DEFAULTS->StartPage.'" name="content" scrolling="auto" noresize="noresize" marginwidth="0" marginheight="0" frameborder="0" />'."\n";
	echo	'</frameset>'."\n";
	echo '</html>'."\n";
}

$DOCUMENT = new document;

if($phpcmsaction == 'LOGOUT') {
	setcookie('phpCMS_Admin', '', time() - 2592000, '/', '', 0);
	$PHPCMS->set_environment_var('POST', 'seceret', '');
	$PHPCMS->set_environment_var('COOKIE', 'phpCMS_Admin', '');
	$phpcmsaction = 'FRAMESET';
}

if($phpcmsaction == 'FRAMESET') {
	DrawFrameset();
	exit;
}

function validPassword($pw) {
	global $DEFAULTS;

	$value = trim($DEFAULTS->PASS);
	$value = md5($value);
	if($pw == $value) {
		return true;
	}
	return false;
}

function set_cookie(
				$cookie_val,
				$cookie_name,
				$cookie_life = 0,
				$cookie_path = '/',
				$cookie_domain = '',
				$cookie_secure = 0) {

	if($cookie_domain == '') {
		$cookie_domain = $GLOBALS['SERVER_NAME'];
	}
	if($cookie_domain == 'localhost') {
		$cookie_domain = $GLOBALS['SERVER_ADDR'];
	}
	elseif(substr_count($cookie_domain,".") < 2) {
		$cookie_domain = ".".$cookie_domain;
	}
	if($cookie_path{0} != '/') {
		$cookie_path = '/'.$cookie_path;
	}
	if($cookie_life > 0) {
		$cookie_life = time() + $cookie_life;
	}
	setcookie($cookie_val, $cookie_name, $cookie_life, $cookie_path, $cookie_host, $cookie_secure);
}

// crypt delivered password
if(isset($login)) {
	$seceret = md5($seceret);
	setcookie('phpCMS_Admin', $seceret, 0, '/', '', 0);
	//echo($seceret.'<br />'.$_COOKIE['phpCMS_Admin']);
}
// load the cookie's value
if(isset($_COOKIE['phpCMS_Admin']) AND validPassword($_COOKIE['phpCMS_Admin'])) {
	$seceret = $_COOKIE['phpCMS_Admin'];
}

// Starting with the default.php version-check
$fp = fopen(PHPCMS_INCLUDEPATH.'/default.php', 'rb');
$firstline = fgets($fp, 1024);
fclose($fp);

$firstline = substr($firstline, strpos($firstline, '//') + 2);
$firstline = substr($firstline, 0, strpos($firstline, '//'));

$loginstyle =
	'body {width:180px;height:auto;margin:5px;}'."\n".
	'#page {width:170px;'."\n".
	'margin:0;'."\n".
	'padding:0;}';

if((($DEFAULTS->PASS == '')
	OR (strtoupper($DEFAULTS->PASS) == 'YOURPASSWORDHERE')
	OR (strtoupper($DEFAULTS->PASS) == 'PHPCMS')
	OR (strtoupper($DEFAULTS->PASS) == 'CMS')
	OR (strtoupper($DEFAULTS->PASS) == 'TEST')
	OR (strtoupper($DEFAULTS->PASS) == 'PASS')
	OR (strlen($DEFAULTS->PASS) < $DEFAULTS->PASS_MIN_LENGTH))
	AND $firstline == $PHPCMS->VERSION.$PHPCMS->RELEASE) {
	// If default.php's version is not the current of the parser, the password may be even one from the blacklist

	DrawHeader($MESSAGES['NAVIGATION'][24], $loginstyle);
	echo
		'<form method="post" name="LOGIN" action="'.$DEFAULTS->SELF.'">'."\n".
		'<input type="hidden" name="phpcmsaction" value="NAV" />'."\n".
		'<input type="hidden" name="login" value="login" />'."\n".
		'<table border="0" cellspacing="0" cellpadding="0" width="180">'."\n".
		'<tr><td valign="top">'."\n".
		'<table border="0" cellspacing="0" cellpadding="2" bgcolor="'.$DOCUMENT->LOGIN_BORDER_COLOR.'"><tr><td>'."\n".
		'<table border="0" cellspacing="0" cellpadding="3" bgcolor="'.$DOCUMENT->LOGIN_BG_COLOR.'">'."\n".
		'<tr><td colspan="2" bgcolor="'.$DOCUMENT->LOGIN_BG_COLOR.'">'.$DOCUMENT->TABLE_FONT.'<span style="color:red; font-weight:bold">'."\n";
	if(strlen($DEFAULTS->PASS) < $DEFAULTS->PASS_MIN_LENGTH) {
		echo($MESSAGES['NAVIGATION'][27].'</span>'.$MESSAGES['NAVIGATION'][28]);
	} else {
		echo($MESSAGES['NAVIGATION'][25].'</span>'.$MESSAGES['NAVIGATION'][26]);
	}
	echo "\n".'</font></td></tr>'.
		'<tr><td bgcolor="'.$DOCUMENT->LOGIN_BG_COLOR.'">'.$DOCUMENT->TABLE_FONT.$MESSAGES['NAVIGATION'][22].':</font></td>'."\n".
		'<td>'.$DOCUMENT->TABLE_FONT.'<input type="password" name="seceret" value="" size="10" maxlength="20" /></td></tr>'."\n".
		'<tr><td>&nbsp;</td><td>'.$DOCUMENT->TABLE_FONT.'<input type="submit" name="submit" value="'.$MESSAGES['NAVIGATION'][23].'" /></td></tr>'."\n".
		'</table></td></tr></table>'."\n".
		'</td></tr>'."\n".
		'</table>&nbsp;'."\n".
		'<table border="0" cellspacing="0" cellpadding="0" width="180">'."\n".
		'<tr><td valign="top">'."\n".
		'<table border="0" cellspacing="0" cellpadding="2" bgcolor="'.$DOCUMENT->LOGIN_BORDER_COLOR.'"><tr><td>'."\n".
		'<table border="0" cellspacing="0" cellpadding="3" bgcolor="'.$DOCUMENT->LOGIN_BG_COLOR.'">'."\n".
		'<tr><td colspan="2" bgcolor="'.$DOCUMENT->LOGIN_BG_COLOR.'">'.$DOCUMENT->TABLE_FONT.$MESSAGES['NOPASSES'].'</font></td></tr>'."\n".
		'</table></td></tr></table>'."\n".
		'</td></tr>'."\n".
		'</table>'."\n".
		'</form>'."\n";
		DrawFooter();
	exit;
} elseif(!isset($seceret) || !validPassword($seceret)) {
	DrawHeader($MESSAGES['NAVIGATION'][20], $loginstyle, '', 'onload="document.LOGIN.seceret.focus()"');
	echo
		'<form name="LOGIN" id="LOGIN"  method="post" action="'.$DEFAULTS->SELF.'">'."\n".
		'<input type="hidden" name="phpcmsaction" value="NAV" />'."\n".
		'<input type="hidden" name="login" value="login" />'."\n".
		'<table border="0" cellspacing="0" cellpadding="0" width="180">'."\n".
		'<tr><td valign="top">'."\n".
		'<table border="0" cellspacing="0" cellpadding="2" bgcolor="'.$DOCUMENT->LOGIN_BORDER_COLOR.'"><tr><td>'."\n".
		'<table border="0" cellspacing="0" cellpadding="3" bgcolor="'.$DOCUMENT->LOGIN_BG_COLOR.'">'."\n".
		'<tr><td colspan="2" bgcolor="'.$DOCUMENT->LOGIN_BG_COLOR.'">'.$DOCUMENT->TABLE_FONT.$MESSAGES['NAVIGATION'][21].'</font></td></tr>'."\n".
		'<tr><td bgcolor="'.$DOCUMENT->LOGIN_BG_COLOR.'">'.$DOCUMENT->TABLE_FONT.$MESSAGES['NAVIGATION'][22].':</font></td>'."\n".
		'<td>'.$DOCUMENT->TABLE_FONT.'<input type="password" name="seceret" value="" size="10" maxlength="20" /></font></td></tr>'."\n".
		'<tr><td>&nbsp;</td><td>'.$DOCUMENT->TABLE_FONT.'<input type="submit" name="submit" value="'.$MESSAGES['NAVIGATION'][23].'" /></font></td></tr>'."\n".
		'</table></td></tr></table>'."\n".
		'</td></tr>'."\n".
		'</table>'."\n".
		'</form>'."\n";
		DrawFooter();
	exit;
}

function DrawTopLine($title) {
	global $DOCUMENT;

	echo '<table border="0" cellspacing="3" cellpadding="3" width="600"><tr><td bgcolor="'.$DOCUMENT->DARK_COLOR.'">'."\n".
		$DOCUMENT->HEAD_LINE_FONT.$title.'</font></td></tr>'."\n".
		'<tr><td>'."\n";
}

function DrawBottomLine($status) {
	global $DOCUMENT;

	echo '</td></tr>'."\n".
		'<tr bgcolor="'.$DOCUMENT->DARK_COLOR.'"><td>'.$DOCUMENT->STATUS_FONT.$status.'</font></td></tr>'."\n".
		'</table>'."\n";
}

function DrawHeader($title='', $style='', $additional='', $body='', $doctype='') {
	global
		$DOCUMENT,
		$DEFAULTS,
		$PASS,
		$MESSAGES;

		$defaulttitle = 'phpCMS @ '. $GLOBALS['_SERVER']['SERVER_NAME'];
		if($title != '') {
			$defaulttitle = $title .' :: '. $defaulttitle;
		}
		$defaultstyle =
		'html, body {'.
			'background-color:'. $DOCUMENT->BACKGROUND_COLOR .";\n".
			'border:0px none;'."\n".
			'width:100%;'."\n".
			'height:100%;'."\n".
			'margin:0;'."\n".
			'padding:0;'."\n".
			"}\n".
		'#page {'."\n".
			'width: 100%;'."\n".
			'height:100%;'."\n".
			'border:0px none;'."\n".
			"}\n".
		'a:link, body a:link {'."\n".
			'color:'.$DOCUMENT->LINK_COLOR .';'.
			"}\n".
		'a:visited {'."\n".
			'color:'.$DOCUMENT->VLINK_COLOR .';'.
			"}\n".
		'a:active {'."\n".
			'color:'.$DOCUMENT->ALINK_COLOR .';'.
			"}\n";
		//'a:hover {color:orange}';

		if($style != '') {
			$defaultstyle.= "\n". $style;
		}
		if($doctype == '') {
			$defaultdoctype = 'xhtml1t';
		} else {
			$defaultdoctype = $doctype;
		}
	$DOCUMENT->header_html($defaulttitle, $defaultstyle, $additional, $defaultdoctype);
	echo
		'<body '.$body.'>'."\n".'<div id="page">'."\n";
}

function DrawMenuEntry($page, $message, $action) {
	global $DEFAULTS;

	echo "\n\n".'<tr>'."\n".
		'<form method="post" action="'.$DEFAULTS->SELF.'" target="content">'."\n".
		'<input name="action" value="'.$action.'" type="hidden" />'."\n".
		'<input name="phpcmsaction" value="'.$page.'" type="hidden" />'."\n".
		'<td><input type="image" src="gif/nav/'.$message.'.gif" border="0" /></td></form></tr>'."\n\n";
}

function DrawFooter() {
	echo '</div></body></html>';
}

function CheckUpdateFile() {
	global $PHPCMS;

	$actversionfile = @file('http://phpcms.de/actversion.ver');

	if(!$actversionfile) return false;
	if(trim(strtoupper($actversionfile[0])) != trim(strtoupper($PHPCMS->VERSION.$PHPCMS->RELEASE))) {
		$check = trim($actversionfile[0]);
	} else {
		$check = 'current';
	}
	return $check;
} //CheckUpdateFile()

function CheckUpdate() {
	global $DOCUMENT;
	global $MESSAGES;

	DrawHeader('', 'html,body{background-color:'.$DOCUMENT->DARK_COLOR.';color:#C0C0C0;font-size:12px;font-family:Arial,sans-serif}');

	echo '<div align="center" id="update" style="border:1px dotted #C0C0C0">';

	$actversion = CheckUpdateFile();
	if(!$actversion) {
		echo '<b>no connection to update-server...</b>'."\n";
	} elseif($actversion == 'current') {
		echo $MESSAGES['NAVIGATION'][30];
	} else {
		echo '<a href="http://www.phpcms.de" target="_blank" style="color:#FF9900">'.$MESSAGES['NAVIGATION'][31].'<br />phpCMS '.$actversion.'</a>';
	}
	echo '</div>'."\n";

	DrawFooter();
}

function DrawNavi() {
	global
		$DOCUMENT,
		$DEFAULTS,
		$PASS,
		$MESSAGES,
		$PHPCMS;

	GetStartPage();

	if($DEFAULTS->UPDATE == 'on') {
		$actversion = '<iframe src="'.$DEFAULTS->SELF.'?phpcmsaction=update" width="100%" height="40" scrolling="auto" frameborder="0" name="update_checker"></iframe>'."\n";
	}
	$navistyle = '#page {background-color:'.$DOCUMENT->DARK_COLOR.';'."\n".
		'width:170px;'."\n".
		'margin:0;'."\n".
		'padding:0;'."\n".
		'text-align:center;'."\n".
		'}';
	//$navistyle.= '#page {border:1px solid blue}';

	// insert Javascript for loading the start-page into the mainframe (bugfix #679022)
	if(isset($_post['login']) && $_post['login'] != '') {
		DrawHeader('Navigation', $navistyle, '', ' onload="parent.content.location.href=\''.$DEFAULTS->StartPage.'\'"', 'html4t');
	} else {
		DrawHeader('Navigation', $navistyle, '', '', 'html4t');
	}

	echo '<table border="0" cellspacing="0" cellpadding="0" height="100%" align="left">'."\n".
		'<tr><td width="170" valign="top" bgcolor="'.$DOCUMENT->DARK_COLOR.'"><center>'."\n".
		'<a href="'.$DOCUMENT->HOMEURL.'" target="_blank"><img src="'.$DOCUMENT->LOGO.'" width="150" height="60" alt="phpCMS" title="phpCMS.de" border="0" /></a>'."\n".
		'<table border="0" cellspacing="5" cellpadding="0">'."\n";
	if($DEFAULTS->UPDATE == 'on') {
		echo '<tr><td>'.$actversion.'</td></tr>'."\n";
	}
	DrawMenuEntry('OPTIONS',     $MESSAGES['NAVIGATION'][1], 'CONF');
	DrawMenuEntry('FILEMANAGER', $MESSAGES['NAVIGATION'][2], '');
	echo '<tr><td><a href="'.$DEFAULTS->SELF.'?phpcmsaction=FILEMANAGER&SORT[filename||asc]=true&WORK_DIR='.$DEFAULTS->CACHE_DIR.'" target="content"><img src="gif/nav/cache.gif" border="0" alt="" /></a></td></tr>'."\n";
	DrawMenuEntry('STAT',        $MESSAGES['NAVIGATION'][4], '');
	echo '<tr><td><img src="gif/nix.gif" border="0" height="12" alt="" /></td></tr>'."\n";
	DrawMenuEntry('SPIDER',      $MESSAGES['NAVIGATION'][5], '');
	DrawMenuEntry('SEARCH',      $MESSAGES['NAVIGATION'][6], '');
	DrawMenuEntry('HTTPINDEX',      $MESSAGES['NAVIGATION'][11], '');
	/*echo '<tr><form method="post" action="phpcms_indexer.php" target="_blank"><td>'."\n".
		'<input type="hidden" name="select" value="" /><input type="hidden" name="callback" value="" />'."\n".
		'<input type="hidden" name ="password" value="'.$DEFAULTS->PASS.'" size="10" />'."\n".
		'<input type="image" name="LOGIN" value="login" src="gif/nav/http-indexer.gif" border="0" /></td></form></tr>'."\n";
	echo "\n\n".'<tr>'."\n".
		'<form method="post" action="'.$DEFAULTS->SELF.'" target="content"><td>'."\n".
		'<input name="action" value="" type="hidden" />'."\n".
		'<input name="phpcmsaction" value="HTTPINDEX" type="hidden" />'."\n".
		'<input type="hidden" name="select" value="" />'."\n".
		'<input type="hidden" name="callback" value="" />'."\n".
		'<input type="hidden" name ="password" value="'.$DEFAULTS->PASS.'" size="10" />'."\n".
		'<input type="image" name="LOGIN" value="login" src="gif/nav/'.$MESSAGES['NAVIGATION'][11].'.gif" border="0" /></td></form></tr>'."\n\n";
	*/
	/*echo '<tr><form method="post" action="phpcms_indexer.php" target="_blank"><td>'."\n".
		'<input type="hidden" name="select" value="" />'."\n".
		'<input type="hidden" name="callback" value="" />'."\n".
		'<input type="hidden" name ="password" value="'.$DEFAULTS->PASS.'" size="10" />'."\n".
		'<input type="image" name="LOGIN" value="login" src="gif/nav/http-indexer.gif" border="0" /></td></form></tr>'."\n";
	*/
	echo '<tr><td><img src="gif/nix.gif" border="0" height="12" alt="" /></td></tr>'."\n";
	DrawMenuEntry('OPTIONS',     $MESSAGES['NAVIGATION'][7], 'INFO');
	DrawMenuEntry('OPTIONS',     $MESSAGES['NAVIGATION'][8], 'FEHLER');
	if($DEFAULTS->STEALTH == 'on') {
		echo '<tr><td><a href="'.$DEFAULTS->SCRIPT_PATH.'/doc/doc_'.$DEFAULTS->LANGUAGE.'/index.htm" target="content">'.
			'<img src="gif/nav/'.$MESSAGES['NAVIGATION'][9].'.gif" border="0" alt="" /></a></td></tr>'."\n";
	} else {
		echo '<tr><td><a href="'.$DEFAULTS->SELF.'?file='.$DEFAULTS->SCRIPT_PATH.'/doc/doc_'.$DEFAULTS->LANGUAGE.'/index.htm" target="content">'.
			'<img src="gif/nav/'.$MESSAGES['NAVIGATION'][9].'.gif" border="0" alt="" /></a></td></tr>'."\n";
	}
	echo '<tr><td><img src="gif/nix.gif" border="0" height="12" alt="" /></td></tr>'."\n";
	DrawMenuEntry('OPTIONS',     $MESSAGES['NAVIGATION'][10], 'LIST');
	echo '</table></td></tr>'."\n".
		'<tr><td bgcolor="'.$DOCUMENT->DARK_COLOR.'">&nbsp;</td></tr>'."\n".
		'<tr><form method="post" action="'.$DEFAULTS->SELF.'" target="_parent"><input name="phpcmsaction" value="LOGOUT" type="hidden" /><td align="center" valign="top" bgcolor="'.$DOCUMENT->DARK_COLOR.'">'."\n".
		'<a href="'.$DEFAULTS->SELF.'?phpcmsaction=NAV"><img src="gif/nav/reload.jpg" border="0" title="RELOAD"/></a>&nbsp;'."\n".
		'<input type="image" src="gif/nav/logout.jpg" border="0" title="LOGOUT"/></td></form></tr>'."\n".
		'<tr><td align="center" bgcolor="'.$DOCUMENT->DARK_COLOR.'" height="50"><a href="http://www.phpcms.de/" target="_blank">'.$DOCUMENT->STATUS_FONT.$PHPCMS->VERSION.$PHPCMS->RELEASE.'<br />with PAX</font></a></td></tr>'."\n".
		'</table>'."\n";
	DrawFooter();

	/*echo '</td></tr></table>'."\n".
		'<div style="position:absolute;bottom:0px;width:auto;margin:10px;padding:0;border:1px solid white;text-align:center;">'."\n".
		'<div align="center" style="position:static;width:80px;magin:0 auto;font-size:95%">'."\n".
		'<form method="post" action="'.$DEFAULTS->SELF.'">'."\n".
		'<input type="hidden" name="phpcmsaction" value="NAV" />'."\n".
		'<input type="IMAGE" src="gif/nav/reload.jpg" /></form>'."\n".
		'<a href="http://www.phpcms.de/" target="_blank">'.$DOCUMENT->STATUS_FONT.$PHPCMS->VERSION.$PHPCMS->RELEASE.'<br />with PAX</font></a>'."\n".
		'</div></div>';
		*/
}

?>
