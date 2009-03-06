<?php
/* $Id: config.filemanager.php,v 1.1.2.13 2006/06/18 18:08:46 ignatius0815 Exp $ */
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
   |    Beate Paland (beate76)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

///////////////////////////////////////////////////////////////////////////////
// This is the filetypes registration.
//
// Note: The pattern "\.htm" will match on ".htm" as well as ".html"-files
//       IMPORTANT: Dots must be escaped, so use "\." instead of ".".
//       First match in the array will break further search
//
// These are the arrays values:
//   - regexp pattern on the filename, used with eregi()
//   - iconfile in /parser/gif/filemanager/filetypes/
//   - param for the 'show' & 'edit'-action
//        ('image': file has to be shown as source of an <img>-tag,
//         'text':  it is raw-text, so display it with PHP's Code-Highlighting,
//                  and make it editable
//         false:   Don't show the file and even the link to show it, but only the icon)
//   - browsing allowed, show the link for it (true or false)

$this->FILETYPES = array(
// phpCMS Files
	// phpCMS ContentFile
	array("(\\$DEFAULTS->PAGE_EXTENSION|\\$DEFAULTS->DYN_EXTENSION)$",
		'webpage.gif',
		'text',
		true,
	),
	// phpCMS ProjectFile
	array("\.ini$",
		'phpcms.project.gif',
		'text',
		false,
	),
	// phpCMS Template
	array("\\$DEFAULTS->TEMPEXT$",
		'phpcms.template.gif',
		'text',
		false,
	),
	// phpCMS MenuFile
	array("\.mnu$",
		'phpcms.menu.gif',
		'text',
		false,
	),
	// phpCMS MenuTemplate
	array("\.mtpl$",
		'phpcms.menutemplate.gif',
		'text',
		false,
	),
	// phpCMS MenuFile
	array("\.tag$",
		'phpcms.tag.gif',
		'text',
		false,
	),
// Web- and Script-Files
	// Apache Webserver security settings
	array("\.(htaccess|htusers)$",
		'security.gif',
		'text',
		false,
	),
	// Webscripts (serverside)
	array("\.(phps|php|php[2-5]?|asp|asa|cgi|pl|shtml|phtml)$",
		'serverscript.gif',
		'text',
		true,
	),
	// Webscripts (clientside)
	array("\.js$",
		'clientscript.gif',
		'text',
		true,
	),
	// Webpages
	array("\.(htm|html)$",
		'webpage.gif',
		'text',
		true,
	),
	// XML Files
	array("\.(xml|xsl|xslt)$",
		'xml.gif',
		'text',
		true,
	),
	// Wap Files
	array("\.(wml|wsl(c|s|sc))$",
		'wap.gif',
		'text',
		true,
	),
	// StyleSheet
	array("\.css$",
		'css.gif',
		'text',
		true,
	),
// Images
	// WebImages
	array("\.(gif|png|jp(g|e|eg)|ico)$",
		'webimage.gif',
		'image',
		true,
	),
	// Bitmaps
	array("\.(bmp|wbmp)$",
		'bitmap.gif',
		'image',
		true,
	),
	// Targa Images
	array("\.(tif|tiff)$",
		'targa.gif',
		'image',
		true,
	),
// Audio and Video
	// Flash Movies
	array("\.(swf|cab)$",
		'flash.gif',
		false,
		true,
	),
	// Audiofiles (Waveform)
	array("\.(wav|mp(2|3|4|a)|vqf|ai(f|ff|fc)|au|snd)$",
		'audio.gif',
		false,
		true,
	),
	// Audiofiles (Midi)
	array("\.(mid|midi)$",
		'midi.gif',
		false,
		true,
	),
	// WindowsMedia Videofiles
	array("\.(asf|wma|wmv|avi)$",
		'winmedia.gif',
		false,
		true,
	),
	// QuickTime File
	array("\.(qt|mov)$",
		'quicktime.gif',
		false,
		true,
	),
	// RealAudio/RealVideo Files
	array("\.(rm|ra|ram)$",
		'realmedia.gif',
		false,
		true,
	),
	// MPEG Movies
	array("\.(mp(e|g|eg)|m1v|mpv2)$",
		'movie.gif',
		false,
		true,
	),
// Specials
	// Archives
	array("\.(zip|rar|sit|gz|tar|tar.gz|gtar|gzip|ace|lha|arj|arc)$",
		'archive.gif',
		false,
		true,
	),
	// Adobe Acrobat-Files
	array("\.(pdf|fdf)$",
		'acrobat.gif',
		false,
		true,
	),
	// Adobe Photoshop File
	array("\.(psd)$",
		'photoshop.gif',
		false,
		true,
	),
	// Microsoft Excel-Files
	array("\.xl(s|a)$",
		'excel.gif',
		false,
		true,
	),
	// Microsoft Word-Files
	array("\.do(c|t)$",
		'word.gif',
		false,
		true,
	),
	// Microsoft Powerpoint-Files
	array("\.pp(t|s|z)$",
		'powerpoint.gif',
		false,
		true,
	),
	// Microsoft Access-Files
	array("\.(md(b|a|e)|db)$",
		'access.gif',
		false,
		true,
	),
	// Helpfiles
	array("\.(hlp|chm)$",
		'help.gif',
		false,
		true,
	),
	// Executables
	array("\.(exe|com|bat|hqx|bin|dll|class)$",
		'executable.gif',
		false,
		true,
	),
// Textfiles
	// Comma/Tab-separated Files
	array("\.(c|t)sv$",
		'csv.gif',
		'text',
		true,
	),
	// RichText Files
	array("\.rt(f|x)$",
		'richtext.gif',
		'text',
		true,
	),
	// PlainText File
	array("\.txt$",
		'text.gif',
		'text',
		true,
	),
	// Link on a file, M$ only
	array("\.lnk$",
		'symlink.gif',
		false,
		false,
	),
);


// set the defaults, used if nothing above matched
$this->DEF_FILETYPE['icon']   = 'text.gif';
$this->DEF_FILETYPE['show']   = 'text';
$this->DEF_FILETYPE['browse'] = false;


///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
// This is the templates registration.
// First value is the part of the filename between 'template.' and '.txt'
// Second value is the description shown in the selector-field of the FileManager

$this->TEMPLATES = array(
	array(
		'html',
		'HTML'
	),
	array(
		'phpcms',
		'phpCMS ContentFile'
	),
);

?>