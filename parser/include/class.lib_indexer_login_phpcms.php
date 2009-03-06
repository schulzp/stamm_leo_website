<?php
/* $Id: class.lib_indexer_login_phpcms.php,v 1.2.2.17 2006/06/18 18:07:30 ignatius0815 Exp $ */
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
   |    Henning Poerschke (hpoe)
   |    Markus Richert (e157m369)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

########################################################################
# shutdown function
########################################################################

function shut_down()
	{
	global $session;

	$session->close();
	}

register_shutdown_function ('shut_down');

########################################################################
# Menüeinträge
########################################################################

function menu()
	{
	global $PHP_SELF, $QUERY_STRING, $session, $DEFAULTS;

	$menu_rows = file(PHPCMS_INCLUDEPATH.'/lib.http_indexer_menu.'.$DEFAULTS->LANGUAGE);

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" '."\n".
	  '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n".
	'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$DEFAULTS->LANGUAGE.'" lang="'.$DEFAULTS->LANGUAGE.'">'."\n".
	'<head><meta http-equiv="content-type" content="text/html;charset=iso-8859-1" />'."\n".
	'<meta http-equiv="content-style-type" content="text/css" />'."\n".
	'<meta name="Content-Language"  content="'.$DEFAULTS->LANGUAGE.'" />'."\n".
	'<title>phpCMS - HTTP-Indexer</title>'."\n".
	'<meta name="robots" content="noindex, nofollow" />'."\n".
	'<style type="text/css" media="screen"><!--/*--><![CDATA[/*><!--*/'."\n".
	'html, body {'."\n".
	'margin: 0px; padding: 0px;'."\n".
	'width: 100%;'."\n".
	'height: 100%;'."\n".
	'}'."\n".
	'body {'."\n".
	'background-color: #ffffff;'."\n".
	'font-family: Arial, Helvetica, Verdana, sans-serif;'."\n".
	'font-size: 90%;'."\n".
	'}'."\n".

	'#topbar {'."\n".
	'width:600px;'."\n".
	'height:20px;'."\n".
	'border: 0 none;'."\n".
	'margin: 3px 0 0px 3px;'."\n".
	'padding:4px 0 0 5px;'."\n".
	'color: white;'."\n".
	'background-color: #006600;'."\n".
	'font-family: Verdana, Arial, Helvetica, sans-serif;'."\n".
	'font-weight: bold;'."\n".
	'font-size: 15px;'."\n".
	'}'."\n".

	'#indexermenu {'."\n".
	'clear: both;'."\n".
	'width: 500px;'."\n".
	'height: auto;'."\n".
	'margin: 5px 0px 10px 3px ;'."\n".
	'padding:1px 0 3px 0;'."\n".
	'border: 1px solid black;'."\n".
	'background-color: #A9A9A9;'."\n".
	'text-align: center;'."\n".
	'font-weight: normal;'."\n".
	'font-size: 12px;'."\n".
	'}'."\n".

	'#indexermenu a:link,'."\n".
	'#indexermenu a:visited,'."\n".
	'#indexermenu a:active {'."\n".
	'color: white;'."\n".
	'margin: 0px 3px;'."\n".
	'text-decoration: none;'."\n".
	'}'."\n".
	'#indexermenu a:hover {'."\n".
	'color: #006600;'."\n".
	'margin: 0px 3px;'."\n".
	'text-decoration: none;'."\n".
	'}'."\n".

	'#output {'."\n".
	'width: 500px;'."\n".
	'height: auto;'."\n".
	'margin: 20px 0px 20px 0px ;'."\n".
	'padding: 0px 0px 0px 5px ;'."\n".
	'clear: both;'."\n".
	//'border: 1px solid black;'."\n".
	'}'."\n".
	'/*]]>*/--></style>'."\n".
	'</head><body>'."\n";

	echo '<div id="topbar">HTTP-Indexer</div>'."\n";
	echo '<div id="indexermenu"> ::'."\n";

	foreach($menu_rows as $entry)
		{
		$temp = explode(';', $entry);
		if (trim($temp[1]) == (basename($PHP_SELF).'?'.$QUERY_STRING))
			echo '<a href="'.$session->write_link(trim($temp[1])).'">'.trim($temp[0]).'</a> ::';
		else
			echo '<a href="'.$session->write_link($PHP_SELF.'?phpcmsaction=HTTPINDEX&action='.trim($temp[1])).'">'.trim($temp[0]).'</a> ::';
		}

	echo '</div>'."\n";
	}

$formdata = new get_form;

?>
