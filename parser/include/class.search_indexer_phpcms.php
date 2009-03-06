<?php
/* $Id: class.search_indexer_phpcms.php,v 1.5.2.19 2006/06/18 18:07:32 ignatius0815 Exp $ */
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
   |    Beate Paland (beate76)
   |    Henning Poerschke (hpoe)
   |    Markus Richert (e157m369)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

// --------------------------------------
// set some basic vars
// --------------------------------------

$PHPCMS_DOC_ROOT = $DEFAULTS->DOCUMENT_ROOT;
$SEARCHDATADIR   = $PHPCMS_DOC_ROOT.$session->vars['datadir'];
$MAX_BYTE_SIZE   = $session->vars['maxbytesize'];
$MIN_WORD_SIZE   = $session->vars['minwordsize'];

$e = 0;
if(isset($session->vars['excludepath1'])) {
	$EXDIR[$e++] = $session->vars['excludepath1'];
}
if(isset($session->vars['excludepath2'])) {
	$EXDIR[$e++] = $session->vars['excludepath2'];
}
if(isset($session->vars['excludepath3'])) {
	$EXDIR[$e++] = $session->vars['excludepath3'];
}
if(isset($session->vars['excludepath4'])) {
	$EXDIR[$e++] = $session->vars['excludepath4'];
}
if(isset($session->vars['excludepath5'])) {
	$EXDIR[$e++] = $session->vars['excludepath5'];
}
if(isset($session->vars['excludepath6'])) {
	$EXDIR[$e++] = $session->vars['excludepath6'];
}
if(isset($session->vars['excludepath7'])) {
	$EXDIR[$e++] = $session->vars['excludepath7'];
}
if(isset($session->vars['excludepath8'])) {
	$EXDIR[$e++] = $session->vars['excludepath8'];
}
unset($e);

// patterns for documents
// ======================
// to get the content from HTML-title and HTML-body change the regex
// for each extension. you can easily add new extensions in the
// same way as for phpCMS here. these values work for me.
$EXTENSION[0]['type']        = 'htm';
$EXTENSION[0]['doc_start']   = '<';
$EXTENSION[0]['name']        = '.htm';
$EXTENSION[0]['title_start'] = "<[:space:]*title[^>]*>";
$EXTENSION[0]['title_stop']  = "<[:space:]*/title[^>]*>";
$EXTENSION[0]['body_start']  = "<body[^>]*>";
$EXTENSION[0]['body_stop']   = "</body[^>]*>";

$EXTENSION[1]['type']        = 'html';
$EXTENSION[1]['doc_start']   = '<';
$EXTENSION[1]['name']        = '.html';
$EXTENSION[1]['title_start'] = "<[:space:]*title[^>]*>";
$EXTENSION[1]['title_stop']  = "<[:space:]*/title[^>]*>";
$EXTENSION[1]['body_start']  = "<body[^>]*>";
$EXTENSION[1]['body_stop']   = "</body[^>]*>";

$PEXTENSION['doc_start']     = $DEFAULTS->START_FIELD;
$PEXTENSION['name']          = $DEFAULTS->PAGE_EXTENSION;

// --------------------------------------
// --------------------------------------

function addIndexField($fieldtoindex) {
	global $DEFAULTS, $PEXTENSION;

	$fieldname = $fieldtoindex;
	$fieldcount = count($PEXTENSION['FieldsToIndex']);
	//echo($fieldcount);
	$PEXTENSION['FieldsToIndex'][($fieldcount)]['field_start']	= '\\'.$DEFAULTS->START_FIELD.$fieldname.'\\'.$DEFAULTS->STOP_FIELD;
	$PEXTENSION['FieldsToIndex'][($fieldcount)]['field_stop']	= '\\'.$DEFAULTS->START_FIELD;
}

// --------------------------------------
// optimize stop words file
// --------------------------------------

function optimize_stopdb($db) {
	global $session;

	if(is_writable($db)) {
		$STOP = file($db);
		$i=0;$c=count($STOP);
		for($i; $i<$c; $i++) {
			$STOP[$i] = string_tolower($STOP[$i]);
		}
		$STOP = array_unique($STOP);
		sort($STOP);

		$fp = @fopen($db, "w+b");
		$i=0;$c=count($STOP);
		for ($i; $i<$c; $i++) {
			fwrite($fp,$STOP[$i]);
		}
		fclose($fp);
		$session->set_var('optimized', true);
		return true;
	}
	return false;
} //function optimize_stopdb($db)

// -----------------------------------------------
// this is the main-function of the file-indexer
// -----------------------------------------------

function index_entry($title, &$body, $page) {
	global
		$SEARCHDATADIR,
		$STOP,
		$STOP_MAX,
		$MIN_WORD_SIZE;

	$body = $title.' '.$body;

	// jump back, if the content of the
	// body-field is shorter than the
	// minimum-word-size.
	if(strlen($body) < $MIN_WORD_SIZE) {
		return;
	}
	// remove some unwanted chars
	$body = cleanChars($body);

	// make all words lowercase. search is not casesensitive
	// at this time. this increases performance a lot.
	$body = string_tolower($body);
	$body = trim($body);
	$wordAr = explode(' ', $body);
	$old_val = '';
	$doit = true;
	$indexer = 0;

	sort($wordAr);
	reset($wordAr);
	while(list($key, $val) = each($wordAr)) {
		$val = trim($val);

		if(strlen($val) == 0) {
			continue;
		}

		if($val == $old_val) {
			if($doit == false) {
				continue;
			}
			// same as bevore so only increment the word-counter for this page.
			$ResultArray[$indexer-1]['c']++;
		} else {
			// we dont need to make all checks on every word.
			// if there are two same words, we could also memory the checks.
			$old_val = $val;
			$doit = true;

			// removing some unwanted chars at beginning and end of a word.
			while(substr($val, 0, 1) == '-' OR substr($val, 0, 1) == '_') {
				$val = substr($val, 1);
			}
			while(substr($val, -1) == '-' OR substr($val, -1) == '_') {
				$val = substr($val, 0, -1);
			}

			// checking minimum word-size
			if(!is_numeric($val)) {
				if(strlen($val) < $MIN_WORD_SIZE) {
				$doit = false;
				continue;
				}
			}

			// only check stop-words, if the length of the word to index is shorter then the longest stop-word.
			// checking the stop-word-array is very time-consuming so check only if really nessesary.
			if(strlen($val) < $STOP_MAX) {
				if(CheckArray($STOP, $val)) {
					$doit = false;
					continue;
				}
			}
			if($doit) {
				// all checks passed => add this word to the index.
				$doit = true;
				$ResultArray[$indexer]['n'] = $val;
				$ResultArray[$indexer]['u'] = $page;
				$ResultArray[$indexer]['c'] = 1;
				$indexer++;
			}
		}
	}

	// return the result-array, wich looks like this:
	// $indexer = Array-Index 0-x
	// $ResultArray[$indexer]['n'] = the word to index
	// $ResultArray[$indexer]['u'] = the index of the page in which the word was found
	// $ResultArray[$indexer]['c'] = the occurences of the word in the page
	if(isset($ResultArray)) {
		return $ResultArray;
	} else {
		return;
	}
}

// --------------------------------------
// checks, if an entry is in a array.
// used for checking the stop-word-array
// --------------------------------------

function CheckArray($sarray, $entry) {
	global $PHP;

	$ac = count($sarray);
	for($i = 0; $i < $ac; $i++) {
		if(trim($sarray[$i]) == trim($entry)) {
			return true;
		}
	}
	return false;
}

// --------------------------------------------------
// reduce the length of the body-field to be
// displayed in the search-result. You can change the
// length with the variable $MaxChar.
// --------------------------------------------------

function MakeShortWords($words) {
	global $session;

	$MaxChar = $session->vars['textsize'];

	$words = substr($words, 0, $MaxChar * 2);
	$words = str_replace('  ', ' ', $words);
	$words = substr(trim(strip_tags($words)), 0, $MaxChar);
	$lpos  = strrpos($words, ' ');
	$words = substr($words, 0, $lpos);
	$words = str_replace(';', '##', $words);
	return $words;
}

// --------------------------------------
// trimming the title-string and replace
// all occurences of ";", because we use
// this for splitting the entry later.
// --------------------------------------

function TrimTitle($words) {
	$words = trim($words);
	$words = str_replace(';', '##', $words);
	return $words;
}

// --------------------------------------
// remove unwanted characters
// --------------------------------------

function cleanChars($body) {
	$strip_array = array(
	"!"  , ":"  , '"', "\'", "@",
	"&lt", "&gt", "$", "%" , "(",
	")"  , "["  , "]", "{" , "}",
	"?"  , "*"  , "+", "|" , "^",
	"'"  , "´"  , "`", "~" , "\\",
	"¦"  , " "  , "=", "\n", "/",
	".." , "-"  , "	", "  ",
	"&#8220;", "&#8221;", "&#8222;",
	"&#8217;", "&#8216;",
	//   non-breaking space
	"&nbsp;", "&#160;",
	// ¡ inverted exclamation mark
	"&iexcl;", "&#161;", "¡",
	// ¢ cent sign
	"&cent;", "&#162;", "¢",
	// £ pound sign
	"&pound;", "&#163;", "£",
	// ¤ currency sign
	"&curren;", "&#164;", "¤",
	// ¥ yen sign
	"&yen;", "&#165;", "¥",
	// ¦ broken bar
	"&brvbar;", "&#166;", "¦",
	// § section sign
	"&sect;", "&#167;", "§",
	// ¨ diaeresis
	"&uml;", "&#168;", "¨",
	// © copyright sign
	"&copy;", "&#169;", "©",
	// ª feminine ordinal indicator
	"&ordf;", "&#170;", "ª",
	// « left-pointing double angle quotation mark
	"&laquo;", "&#171;", "«",
	// ¬ not sign
	"&not;", "&#172;", "¬",
	// ­ soft hyphen
	"&shy;", "&#173;",
	// ® registered sign
	"&reg;", "&#174;", "®",
	// ¯ macron
	"&macr;", "&#175;", "¯",
	// ° degree sign
	"&deg;", "&#176;", "°",
	// ± plus-minus sign
	"&plusmn;", "&#177;", "±",
	// ² superscript two
	"&sup2;", "&#178;", "²",
	// ³ superscript three
	"&sup3;", "&#179;", "³",
	// ´ acute accent
	"&acute;", "&#180;", "´",
	// µ micro sign
	"&micro;", "&#181;", "µ",
	// ¶ pilcrow sign
	"&para;", "&#182;", "¶",
	// · middle dot
	"&middot;", "&#183;", "·",
	// ¸ cedilla
	"&cedil;", "&#184;", "¸",
	// ¹ superscript one
	"&sup1;", "&#185;", "¹",
	// º masculine ordinal indicator
	"&ordm;", "&#186;", "º",
	// » right-pointing double angle quotation mark
	"&raquo;", "&#187;", "»",
	// ¼ vulgar fraction one quarter
	"&frac14;", "&#188;", "¼",
	// ½ vulgar fraction one half
	"&frac12;", "&#189;", "½",
	// ¾ vulgar fraction three quarters
	"&frac34;", "&#190;", "¾",
	// ¿ inverted question mark
	"&iquest;", "&#191;", "¿",
	// × multiplication sign
	"&times;", "&#215", "×",
	// Ø latin capital letter O with stroke
	"&Oslash;", "&#216;", "Ø",
	// ÷ division sign
	"&divide;", "&#247;", "÷",
	// ø latin small letter o with stroke
	"&oslash;", "&#248;", "ø",
	// … horizontal ellipse
	"…", "&#8230;", "&hellip;",	";",
	"&#",
	"#",
	"&",
	"  ",
	"  ");

	$body = str_replace($strip_array, ' ', $body);

	// index numbers w/o separators?
	//$body = preg_replace("/(\d+)(\.)?(\d+)/","\\1\\3",$body);
	//$body = preg_replace("/(\d+)(\,)?(\d+)/","\\1\\3",$body);

	//	disallow indexing of numbers and seperators?
	//$strip_array = array(".", ",", "1", "2", "3", "4", "5", "6", "7", "8", "9", "0");
	//$body = str_replace($strip_array, ' ', $body);

	$body = preg_replace("/([a-zA-Z]+)(\.) /si","\\1 ",$body);
	$body = preg_replace("/([a-zA-Z]+)(\,) /si","\\1 ",$body);

	return $body;
}

// --------------------------------------
// i know. :-)
// --------------------------------------

/*
function make_german(&$body) {
	$body = str_replace('>', '> ', $body);
	$body = str_replace('&Auml;', 'Ä', $body);
	$body = str_replace('&auml;', 'ä', $body);
	$body = str_replace('&ouml;', 'ö', $body);
	$body = str_replace('&Ouml;', 'Ö', $body);
	$body = str_replace('&uuml;', 'ü', $body);
	$body = str_replace('&Uuml;', 'Ü', $body);
	$body = str_replace('&szlig;', 'ß', $body);
	$body = strip_tags($body);
}
*/

// --------------------------------------
// a better strtolower()
// --------------------------------------

function string_tolower($string) {

	$replacement = array(
	// À latin capital letter A with grave
	"À"		=>		"à",
	// Á latin capital letter A with acute
	"Á"		=>		"á",
	// Â latin capital letter A with circumflex
	"Â"		=>		"â",
	// Ã latin capital letter A with tilde
	"Ã"		=>		"ã",
	// Ä latin capital letter A with diaeresis
	"Ä"		=>		"ä",
	// Å latin capital letter A with ring above
	"Å"		=>		"å",
	// Æ latin capital letter AE
	"Æ"		=>		"æ",
	// Ç latin capital letter C with cedilla
	"Ç"		=>		"ç",
	// È latin capital letter E with grave
	"È"		=>		"è",
	// É latin capital letter E with acute
	"É"		=>		"é",
	// Ê latin capital letter E with circumflex
	"Ê"		=>		"ê",
	// Ë latin capital letter E with diaeresis
	"Ë"		=>		"ë",
	// Ì latin capital letter I with grave
	"Ì"		=>		"ì",
	// Í latin capital letter I with acute
	"Í"		=>		"í",
	// Î latin capital letter I with circumflex
	"Î"		=>		"î",
	// Ï latin capital letter I with diaeresis
	"Ï"		=>		"ï",
	// Ð latin capital letter ETH
	"Ð"		=>		"ð",
	// Ñ latin capital letter N with tilde
	"Ñ"		=>		"ñ",
	// Ò latin capital letter O with grave
	"Ò"		=>		"ò",
	// Ó latin capital letter O with acute
	"Ó"		=>		"ó",
	// Ô latin capital letter O with circumflex
	"Ô"		=>		"ô",
	// Õ latin capital letter O with tilde
	"Õ"		=>		"õ",
	// Ö latin capital letter O with diaeresis
	"Ö"		=>		"ö",
	// Ù latin capital letter U with grave
	"Ù"		=>		"ù",
	// Ú latin capital letter U with acute
	"Ú"		=>		"ú",
	// Û latin capital letter U with circumflex
	"Û"		=>		"û",
	// Ü latin capital letter U with diaeresis
	"Ü"		=>		"ü",
	// Ý latin capital letter Y with acute
	"Ý"		=>		"ý",
	// þ latin capital letter THORN
	"þ"		=>		"Þ",
	);

	foreach($replacement as $key=>$value) {
		$string = str_replace ($key, $value, $string);
	}
	$string = strtolower($string);
	return $string;
}

// --------------------------------------
// --------------------------------------

function revertDiacritical($string) {
	$string = str_replace('>','> ',$string);
	$string = str_replace('<',' <',$string);

	// (\&[a-zA-Z0-9]+;)(.)*:=(.)*(\&#[0-9]+;)
	// \t$string = str_replace ('\1', ' ', $string);\n\t$string = str_replace ('\4', ' ', $string);

	$replacement = array(

	// À latin capital letter A with grave
	"&Agrave;"		=>		"À",
	"&#192;"		=>		"À",
	// Á latin capital letter A with acute
	"&Aacute;"		=>		"Á",
	"&#193;"		=>		"Á",
	// Â latin capital letter A with circumflex
	"&Acirc;"		=>		"Â",
	"&#194;"		=>		"Â",
	// Ã latin capital letter A with tilde
	"&Atilde;"		=>		"Ã",
	"&#195;"		=>		"Ã",
	// Ä latin capital letter A with diaeresis
	"&Auml;"		=>		"Ä",
	"&#196;"		=>		"Ä",
	// Å latin capital letter A with ring above
	"&Aring;"		=>		"Å",
	"&#197;"		=>		"Å",
	// Æ latin capital letter AE
	"&AElig;"		=>		"Æ",
	"&#198;"		=>		"Æ",
	// Ç latin capital letter C with cedilla
	"&Ccedil;"		=>		"Ç",
	"&#199;"		=>		"Ç",
	// È latin capital letter E with grave
	"&Egrave;"		=>		"È",
	"&#200;"		=>		"È",
	// É latin capital letter E with acute
	"&Eacute;"		=>		"É",
	"&#201;"		=>		"É",
	// Ê latin capital letter E with circumflex
	"&Ecirc;"		=>		"Ê",
	"&#202;"		=>		"Ê",
	// Ë latin capital letter E with diaeresis
	"&Euml;"		=>		"Ë",
	"&#203;"		=>		"Ë",
	// Ì latin capital letter I with grave
	"&Igrave;"		=>		"Ì",
	"&#204;"		=>		"Ì",
	// Í latin capital letter I with acute
	"&Iacute;"		=>		"Í",
	"&#205;"		=>		"Í",
	// Î latin capital letter I with circumflex
	"&Icirc;"		=>		"Î",
	"&#206;"		=>		"Î",
	// Ï latin capital letter I with diaeresis
	"&Iuml;"		=>		"Ï",
	"&#207;"		=>		"Ï",
	// Ð latin capital letter ETH
	"&ETH;"		=>		"Ð",
	"&#208;"		=>		"Ð",
	// Ñ latin capital letter N with tilde
	"&Ntilde;"		=>		"Ñ",
	"&#209;"		=>		"Ñ",
	// Ò latin capital letter O with grave
	"&Ograve;"		=>		"Ò",
	"&#210;"		=>		"Ò",
	// Ó latin capital letter O with acute
	"&Oacute;"		=>		"Ó",
	"&#211;"		=>		"Ó",
	// Ô latin capital letter O with circumflex
	"&Ocirc;"		=>		"Ô",
	"&#212;"		=>		"Ô",
	// Õ latin capital letter O with tilde
	"&Otilde;"		=>		"Õ",
	"&#213;"		=>		"Õ",
	// Ö latin capital letter O with diaeresis
	"&Ouml;"		=>		"Ö",
	"&#214;"		=>		"Ö",
	// Ù latin capital letter U with grave
	"&Ugrave;"		=>		"Ù",
	"&#217;"		=>		"Ù",
	// Ú latin capital letter U with acute
	"&Uacute;"		=>		"Ú",
	"&#218;"		=>		"Ú",
	// Û latin capital letter U with circumflex
	"&Ucirc;"		=>		"Û",
	"&#219;"		=>		"Û",
	// Ü latin capital letter U with diaeresis
	"&Uuml;"		=>		"Ü",
	"&#220;"		=>		"Ü",
	// Ý latin capital letter Y with acute
	"&Yacute;"		=>		"Ý",
	"&#221;"		=>		"Ý",
	// þ latin capital letter THORN
	"&THORN;"		=>		"þ",
	"&#222;"		=>		"þ",
	// ß latin small letter sharp s
	"&szlig;"		=>		"ß",
	"&#223;"		=>		"ß",
	// à latin small letter a with grave
	"&agrave;"		=>		"à",
	"&#224;"		=>		"à",
	// á latin small letter a with acute
	"&aacute;"		=>		"á",
	"&#225;"		=>		"á",
	// â latin small letter a with circumflex
	"&acirc;"		=>		"â",
	"&#226;"		=>		"â",
	// ã latin small letter a with tilde
	"&atilde;"		=>		"ã",
	"&#227;"		=>		"ã",
	// ä latin small letter a with diaeresis
	"&auml;"		=>		"ä",
	"&#228;"		=>		"ä",
	// å latin small letter a with ring above
	"&aring;"		=>		"å",
	"&#229;"		=>		"å",
	// æ latin small letter ae
	"&aelig;"		=>		"æ",
	"&#230;"		=>		"æ",
	// ç latin small letter c with cedilla
	"&ccedil;"		=>		"ç",
	"&#231;"		=>		"ç",
	// è latin small letter e with grave
	"&egrave;"		=>		"è",
	"&#232;"		=>		"è",
	// é latin small letter e with acute
	"&eacute;"		=>		"é",
	"&#233;"		=>		"é",
	// ê latin small letter e with circumflex
	"&ecirc;"		=>		"ê",
	"&#234;"		=>		"ê",
	// ë latin small letter e with diaeresis
	"&euml;"		=>		"ë",
	"&#235;"		=>		"ë",
	// ì latin small letter i with grave
	"&igrave;"		=>		"ì",
	"&#236;"		=>		"ì",
	// í latin small letter i with acute
	"&iacute;"		=>		"í",
	"&#237;"		=>		"í",
	// î latin small letter i with circumflex
	"&icirc;"		=>		"î",
	"&#238;"		=>		"î",
	// ï latin small letter i with diaeresis
	"&iuml;"		=>		"ï ",
	"&#239;"		=>		"ï ",
	// ð latin small letter eth
	"&eth;"		=>		"ð",
	"&#240;"		=>		"ð",
	// ñ latin small letter n with tilde
	"&ntilde;"		=>		"ñ",
	"&#241;"		=>		"ñ",
	// ò latin small letter o with grave
	"&ograve;"		=>		"ò",
	"&#242;"		=>		"ò",
	// ó latin small letter o with acute
	"&oacute;"		=>		"ó",
	"&#243;"		=>		"ó",
	// ô latin small letter o with circumflex
	"&ocirc;"		=>		"ô",
	"&#244;"		=>		"ô",
	// õ latin small letter o with tilde
	"&otilde;"		=>		"õ",
	"&#245;"		=>		"õ",
	// ö latin small letter o with diaeresis
	"&ouml;"		=>		"ö",
	"&#246;"		=>		"ö",
	// ù latin small letter u with grave
	"&ugrave;"		=>		"ù",
	"&#249;"		=>		"ù",
	// ú latin small letter u with acute
	"&uacute;"		=>		"ú",
	"&#250;"		=>		"ú",
	// û latin small letter u with circumflex
	"&ucirc;"		=>		"û",
	"&#251;"		=>		"û",
	// ü latin small letter u with diaeresis
	"&uuml;"		=>		"ü",
	"&#252;"		=>		"ü",
	// ý latin small letter y with acute
	"&yacute;"		=>		"ý",
	"&#253;"		=>		"ý",
	// Þ latin small letter thorn
	"&thorn;"		=>		"Þ",
	"&#254;"		=>		"Þ",
	// ÿ latin small letter y with diaeresis
	"&yuml;"		=>		"ÿ",
	"&#255;"		=>		"ÿ"
	);

	foreach($replacement as $key=>$val) {
		$string = str_replace ($key, $val, $string);
	}

	return $string;
}

// --------------------------------------
// A better strip_tags()
// --------------------------------------

function mstrip_tags($tostrip) {

	//$tostrip = preg_replace ("'<!-- PHPCMS_IGNORE --[^>]*?".">.*?<!-- /PHPCMS_IGNORE -->'si", " ", $tostrip);
	while (preg_match("'<phpcms:ignore[^>]*?>.*?</phpcms:ignore>'si", $tostrip)) {
		$tostrip = preg_replace ("'<phpcms:ignore[^>]*?>.*?</phpcms:ignore>'si", " ", $tostrip); // <?
	}

	//$tostrip = preg_replace ("'<!-- PHPCMS_NOINDEX --[^>]*?".">.*?<!-- /PHPCMS_NOINDEX -->'si", " ", $tostrip);
	while (preg_match("'<phpcms:noindex[^>]*?>.*?</phpcms:noindex>'si", $tostrip)) { // <?
		$tostrip = preg_replace ("'<phpcms:noindex[^>]*?>.*?</phpcms:noindex>'si", " ", $tostrip); // <?
	}

	$search = '/<!--(.*)-->/Uis';
	$tostrip = preg_replace($search,' ',$tostrip);

	$search = '/<[^>]+>/s';
	$tostrip = preg_replace($search,' ',$tostrip);

	$search = '/(&nbsp;)|(&copy;)/is';
	$tostrip = preg_replace($search,' ',$tostrip);

	$search = '/"[<|>]"/is';
	$tostrip = preg_replace($search,' ',$tostrip);

	return $tostrip;
}

// --------------------------------------
// this function does the main work.
// Writing the file-index, writing the
// word-index etc.
// --------------------------------------

function write_file_entry($FileIndex, $actual_entry, $titel, &$body, &$written) {
	global
	$SEARCHDATADIR,
	$PHPCMS_DOC_ROOT,
	$MIN_WORD_SIZE;

	// throw out parts that should be ignored
	$body = mstrip_tags($body);

	// revert unicode and entities to ISO-8859-1 chars
	$body = revertDiacritical($body);

	// throw out parts that should be ignored
	$titel = mstrip_tags($titel);

	// revert unicode and entities to ISO-8859-1 chars
	$titel = revertDiacritical($titel);

	// we need this later for the status-display
	$written.= substr(trim($actual_entry), strlen($PHPCMS_DOC_ROOT)).'<br />';

	$fp = fopen($SEARCHDATADIR.'/files.db', 'ab+');

	// the entry in the file.db looks like this:
	// index_of_page;title-text_of_page;body-text_of_page
	// eg: 33;the title;the body
	$entry = substr(trim($actual_entry), strlen($PHPCMS_DOC_ROOT));
	$entry .=';'.TrimTitle($titel).';'.MakeShortWords($body)."\n";
	fputs($fp, $entry, strlen($entry));
	fclose($fp);

	$ResultArray = index_entry($titel, $body, $FileIndex);
	$indexer = count($ResultArray);

	// for write-performance, we write all
	// index-entrys in one string and append
	// this string to the index.
	$words_to_write = '';
	for($i = 0; $i < $indexer; $i++) {
		unset($entry);
		$entry = $ResultArray[$i]['n'].'#'.$ResultArray[$i]['u'].'#'.$ResultArray[$i]['c'];
		$words_to_write = $words_to_write."\n".$entry;
	}
	if(strlen($words_to_write) > $MIN_WORD_SIZE) {
		$fp = fopen($SEARCHDATADIR.'/words.tmp', 'ab+');
		fwrite($fp, $words_to_write, strlen($words_to_write));
		fclose($fp);
	}
}

// --------------------------------------
// --------------------------------------

function get_dir_list($dire) {
	global
		$DEFAULTS,
		$EXTENSION,
		$PEXTENSION,
		$EXDIR,
		$PHPCMS_DOC_ROOT;

	$d = dir($dire);
	while($entry = $d->read()) {
		$test = substr($dire.'/'.$entry, strlen($PHPCMS_DOC_ROOT));
		if($entry == '.' OR $entry == '..') {
			continue;
		}
		if(CheckArray($EXDIR,$test)) {
			continue;
		}
		if(is_dir($dire.'/'.$entry)) {
			$add_array = get_dir_list($dire.'/'.$entry);
			if(!isset($add_array)) {
				continue;
			}
			for($i = 0; $i < count($add_array); $i++) {
				if(!isset($ReturnArray)) {
					$ReturnArray[0] = $add_array[$i];
				} else {
					$ReturnArray[count($ReturnArray)] = $add_array[$i];
				}
			}
			continue;
		}
		$extension = substr($entry, strrpos($entry, '.'));
		$doit = false;
		if($extension == $DEFAULTS->PAGE_EXTENSION) {
			$doit = true;
		}
		for($i = 0; $i < count($EXTENSION); $i++) {
			if($extension != $EXTENSION[$i]['name']) {
				continue;
			} else {
				$doit = true;
				break;
			}
		}
		if(!$doit) {
			continue;
		}
		if(!isset($ReturnArray)) {
			$ReturnArray[0] = $dire.'/'.$entry;
		} else {
			$ReturnArray[count($ReturnArray)] = $dire.'/'.$entry;
		}
	}
	if(isset($ReturnArray)) {
		return $ReturnArray;
	} else {
		return;
	}
}

// --------------------------------------
// now looping through the files which
// should be indexed
// --------------------------------------

function doindex() {
	global
	$DEFAULTS,
	$SEARCHDATADIR,
	$EXTENSION,
	$PEXTENSION,
	$MESSAGES,
	$MAX_BYTE_SIZE,
	$session;

	// because we make more reloads, this checks if we are ready.
	if(!file_exists($SEARCHDATADIR.'/files_to_index.txt') OR filesize($SEARCHDATADIR.'/files_to_index.txt') < 3) {
		unlink($SEARCHDATADIR.'/files_indexed.txt');
		unlink($SEARCHDATADIR.'/files_to_index.txt');
		$session->set_var('start', '0');
		$session->set_var('task', 'MERGER1');
		merger1();
		soft_exit();
	}
	// read the files we have to process
	$dirarray = file($SEARCHDATADIR.'/files_to_index.txt');

	// get the actual fileindex
	if(file_exists($SEARCHDATADIR.'/files_indexed.txt')) {
		$readyarray = file($SEARCHDATADIR.'/files_indexed.txt');
		$filecounter = count($readyarray);
		unset($readyarray);
	} else {
		$filecounter = 0;
	}

	// init some counters
	$entry_counter = 0;
	$continue_index = 1;
	$actual_filesize = 0;
	$written = '';

	while($continue_index == 1) {
		unset($body);
		unset($titel);

		$actual_entry = trim($dirarray[$entry_counter]);

		$raw_value = file($actual_entry);
		$value = join('', $raw_value);
		$value = str_replace("\n", ' ', $value);
		$value = str_replace("\r", ' ', $value);
		$value = str_replace("\t", ' ', $value);
		$epos = strrpos($actual_entry, '.');
		$extension = substr($actual_entry, $epos);

		// if it is an phpCMS-file, use this
		if($extension == $DEFAULTS->PAGE_EXTENSION AND substr($value, 0, strlen($PEXTENSION['doc_start'])) == $PEXTENSION['doc_start']) {
			eregi($PEXTENSION['FieldsToIndex'][0]['field_start']."([^".$PEXTENSION['FieldsToIndex'][0]['field_stop']."]*)", $value, $titel);

			for($i=1; $i<count($PEXTENSION['FieldsToIndex']); $i++) {
				eregi($PEXTENSION['FieldsToIndex'][$i]['field_start']."([^".$PEXTENSION['FieldsToIndex'][$i]['field_stop']."]*)", $value, $bodytemp);
				$body[1] .= $bodytemp[1];
			}
			unset($bodytemp);

			write_file_entry($filecounter, $actual_entry, $titel[1], $body[1], $written);
		} else {
			// walk through all other extensions, which are defined
			for($i = 0; $i < count($EXTENSION); $i++) {
				if($extension != $EXTENSION[$i]['name']) {
					continue;
				}
				eregi($EXTENSION[$i]['title_start']."(.*)".$EXTENSION[$i]['title_stop'], $value, $titel);
				eregi($EXTENSION[$i]['body_start']."(.*)".$EXTENSION[$i]['body_stop'], $value, $body);
				write_file_entry($filecounter, $actual_entry, $titel[1], $body[1], $written);
			}
		}

		$entry_counter++;
		$filecounter++;
		// stop if no more entries are available
		if($entry_counter == count($dirarray)) {
			$continue_index = -1;
		} else {
			// calculate the size of the next file, because we have a
			// time-limit on the server.
			// if the size is bigger as the limit, force a reload of the page.
			$actual_filesize = $actual_filesize + filesize($actual_entry);
			$next_entry = trim($dirarray[$entry_counter]);
			$next_filesize = filesize($next_entry);
			if(($actual_filesize + $next_filesize) > $MAX_BYTE_SIZE) {
				$continue_index = -1;
			}
		}
	}
	// writing the process for the files
	if(file_exists($SEARCHDATADIR.'/files_indexed.txt')) {
		$readyarray = file($SEARCHDATADIR.'/files_indexed.txt');
		$fc = count($readyarray);
		for($i = 0; $i < $entry_counter; $i++) {
			$readyarray[$fc+$i] = $dirarray[$i];
		}
	} else {
		for($i = 0; $i < $entry_counter; $i++) {
			$readyarray[$i] = $dirarray[$i];
		}
	}
	$fp = fopen($SEARCHDATADIR.'/files_indexed.txt', 'w');
	$files_indexed = count($readyarray);
	for($i = 0; $i < $files_indexed; $i++) {
		fputs($fp, trim($readyarray[$i])."\n");
	}
	fclose($fp);

	$fp = fopen($SEARCHDATADIR.'/files_to_index.txt', 'w');
	$files_to_index = count($dirarray);
	for($i = $entry_counter; $i < $files_to_index; $i++) {
		fputs($fp, trim($dirarray[$i])."\n");
	}
	fclose($fp);

	// force the reload of this page
	b_write('<html>');
	b_write('<head>');
	b_write('<meta http-equiv="refresh" content="0; URL='.make_link('&select=INDEXER').'" />');
	b_write('</head>');
	b_write('<body>');
	// write some status information, collected in write_file_entry()
	b_write('<br /><font face="Arial, Helvetica, Verdana" size=3><b>');
	b_write($MESSAGES['FILE_SRC'][26].'</b></font><br />');
	b_write('<font face="Arial, Helvetica, Verdana" size=2>');
	b_write($MESSAGES['FILE_SRC'][27].$files_indexed.$MESSAGES['FILE_SRC'][28].'<br />');
	b_write($MESSAGES['FILE_SRC'][29].$files_to_index.$MESSAGES['FILE_SRC'][30].'<br />');
	b_write($MESSAGES['FILE_SRC'][31].'<hr />');
	b_write($written);
	b_write('</font></body>');
	b_write('</html>');

	soft_exit();
}

// --------------------------------------
// --------------------------------------

function index() {
	global
		$DEFAULTS,
		$session,
		$SEARCHDATADIR;

	// clean data directory of old index-files
	$stopdb = $session->vars['stopwords'];
	$dire = $SEARCHDATADIR;
	$d = dir($dire);
	while($entry = $d->read()) {
		$entry = trim($entry);
		if(is_dir($SEARCHDATADIR.'/'.$entry)) {
			continue;
		}
		if($entry{0} == '.') {
			continue;
		}
		if($entry == $stopdb) {
			continue;
		}
		if($entry == 'nono.db') {
			continue;
		}
		if($entry == 'log.txt') {
			unlink($SEARCHDATADIR.'/'.$entry);
		}
		if(substr($entry, strlen($entry)-3, 3) == '.db') {
			unlink($SEARCHDATADIR.'/'.$entry);
		}
		elseif(substr($entry, strlen($entry)-4, 4) == '.tmp') {
			unlink($SEARCHDATADIR.'/'.$entry);
		}
	}

	$session->set_var('globaltime', time());

	if(trim($session->vars['startpath']) == '/') {
		$indexdir = $DEFAULTS->DOCUMENT_ROOT;
	} else {
		$indexdir = $DEFAULTS->DOCUMENT_ROOT.$session->vars['startpath'];
	}

	$dirarray = get_dir_list($indexdir);
	chdir($SEARCHDATADIR);
	$fp = fopen('files_to_index.txt', 'w');
	for($i = 0; $i < count($dirarray); $i++) {
		fputs($fp, $dirarray[$i]."\n");
	}
	fclose($fp);
	$session->set_var('task', 'INDEX');
	doindex();
}

// --------------------------------------
// merger-functions
// --------------------------------------

function merger1() {
	global $SEARCHDATADIR, $MAX_BYTE_SIZE, $session, $MESSAGES;

	$starttime = time();
	$TempWordsCount = 0;
	$index = 0;
	$start = $session->vars['start'];

	$fp = fopen($SEARCHDATADIR.'/words.tmp', 'rb');
	fseek($fp, $start);

	while((($TempWordsCount + 100) < $MAX_BYTE_SIZE) AND !feof($fp)) {
		$TempWords[$index] = trim(fgets($fp, 4096));
		if(strlen($TempWords[$index]) == 0) {
			continue;
		}
		$TempWordsCount = $TempWordsCount + strlen($TempWords[$index]) + 1;
		$index++;
	}
	if(feof($fp)) {
		$session->set_var('task', 'MERGER2');
	}
	fclose ($fp);

	$next_start = $start + $TempWordsCount;
	$session->set_var('start', $next_start);

	for($i = 0; $i < $index; $i++) {
		list($word, $file) = explode('#', $TempWords[$i]);
		$word_len = strlen($word);

		$fp = fopen($SEARCHDATADIR.'/t'.$word_len.'.db', 'ab+');
		$entry = $TempWords[$i]."\n";
		fputs($fp, $entry, strlen($entry));
		fclose($fp);
	}

	b_write('<html>');
	b_write('<head>');
	b_write('<META http-equiv="refresh" content="0; URL='.make_link('&select=INDEXER').'">');
	b_write('</head>');
	b_write('<body>');
	// writing some status infos, collected in write_file_entry()
	b_write('<br /><font face="Arial, Helvetica, Verdana" size=3><b>');
	b_write($MESSAGES['FILE_SRC'][32].'</b></font><br />');
	b_write('<font face="Arial, Helvetica, Verdana" size=2>');
	b_write($MESSAGES['FILE_SRC'][33].$next_start.$MESSAGES['FILE_SRC'][34].filesize($SEARCHDATADIR.'/words.tmp').$MESSAGES['FILE_SRC'][35].'<hr />');
	b_write($MESSAGES['FILE_SRC'][36].date("H:i:s", $starttime).'<br />');
	b_write($MESSAGES['FILE_SRC'][37].date("H:i:s", time()).'<br />');
	b_write($MESSAGES['FILE_SRC'][38].date("i:s", (time()-$starttime)).'<br />');
	b_write('</font></body>');
	b_write('</html>');
}

// --------------------------------------
// --------------------------------------

function merger2() {
	global $SEARCHDATADIR, $session, $MESSAGES;

	$starttime = time();

	// open directory,
	$dire = $SEARCHDATADIR;
	$d = dir($dire);
	while($entry = $d->read()) {
		if(substr($entry, 0, 1) == 't') {
			$current_file = $SEARCHDATADIR.'/'.$entry;
			break;
		}
	}

	// get first temp-file
	if(isset($current_file)) {
		$TempArray = file($current_file);
		$index = 0;
		for($i = 0; $i < count($TempArray); $i++) {
			$TempArray[$i] = trim($TempArray[$i]);
			if(strlen($TempArray[$i]) < 1) {
				continue;
			}

			list($word, $seite, $anzahl) = explode('#', $TempArray[$i]);

			if(isset($WordArray[$word])) {
				$DataArray[$WordArray[$word]] = $DataArray[$WordArray[$word]].'+'.$seite.'*'.$anzahl;
			} else {
				$WordArray[$word] = $index;
				$IndexArray[$index] = $word;
				$DataArray[$index] = $seite.'*'.$anzahl;
				$index++;
			}
		}

		$output_file = $SEARCHDATADIR.'/words.db';

		$fp = fopen($output_file, 'ab+');
		foreach($IndexArray as $k=>$v) {
			$v = $v."\n";
			$size = strlen($v);
			fputs($fp, $v, $size);
		}
		fclose($fp);

		$output_file = $SEARCHDATADIR.'/data.db';

		$fp = fopen($output_file, 'ab+');
		foreach($DataArray as $k=>$v) {
			$v = $v."\n";
			$size = strlen($v);
			fputs($fp, $v, $size);
		}
		fclose($fp);

		unlink($current_file);

		// write timing
		$log_entry = 'current wordlength:'.substr($entry, 1, strrpos($entry, '.') - 1);
		$log_entry .= ' time: '.date("H:i:s", (time() - $starttime))."\n";
		$fp = fopen($SEARCHDATADIR.'/log.txt', 'a+');
		$size = strlen($log_entry);
		fputs($fp, $log_entry, $size);
		fclose($fp);

		b_write('<html>');
		b_write('<head>');
		b_write('<META http-equiv="refresh" content="0; URL='.make_link('&select=INDEXER').'">');
		b_write('</head>');
		b_write('<body>');
		// writing som status infos, collected in write_file_entry()
		b_write('<br /><font face="Arial, Helvetica, Verdana" size=3><b>');
		b_write($MESSAGES['FILE_SRC'][39].'</b></font><br />');
		b_write('<font face="Arial, Helvetica, Verdana" size=2>');
		b_write($MESSAGES['FILE_SRC'][40].substr($entry, 1, strrpos($entry, '.')-1).'<hr />');
		b_write($MESSAGES['FILE_SRC'][36].date("H:i:s", $starttime).'<br />');
		b_write($MESSAGES['FILE_SRC'][37].date("H:i:s", time()).'<br />');
		b_write($MESSAGES['FILE_SRC'][38].date("i:s", (time()-$starttime)).'<br />');
		b_write('</font></body>');
		b_write('</html>');
	} else {
		if(trim($session->vars['gzipcompr']) == $MESSAGES['ON'] AND extension_loaded('zlib')) {
			$WordIndex = file($SEARCHDATADIR.'/words.db');
			$WordToWrite = implode("", $WordIndex);
			$gp1 = gzopen($SEARCHDATADIR.'/words.gz', 'wb');
			gzwrite($gp1, $WordToWrite);
			gzclose($gp1);
			unlink($SEARCHDATADIR.'/words.db');

			$FileDB = file($SEARCHDATADIR.'/files.db');
			$FileToWrite = implode("", $FileDB);
			$gp2 = gzopen($SEARCHDATADIR.'/files.gz', 'wb');
			gzwrite($gp2, $FileToWrite);
			gzclose($gp2);
			unlink($SEARCHDATADIR.'/files.db');

			$DataArray = file($SEARCHDATADIR.'/data.db');
			$DataToWrite = implode("", $DataArray);
			$gp3 = gzopen($SEARCHDATADIR.'/data.gz', 'wb');
			gzwrite($gp3, $DataToWrite);
			gzclose($gp3);
			unlink($SEARCHDATADIR.'/data.db');

			unlink($SEARCHDATADIR.'/words.tmp');
			$globaltime = date("i:s", (time() - $session->vars['globaltime']));
			$session->destroy();

			b_write('<html>');
			b_write('<head><title></title></head>');
			b_write('<body>');
			b_write('<br /><font face="Arial, Helvetica, Verdana" size=3><b>');
			b_write($MESSAGES['FILE_SRC'][41].'</b></font><br />');
			b_write('<font face="Arial, Helvetica, Verdana" size=2>');
			b_write($MESSAGES['FILE_SRC'][42].$globaltime);
			b_write('</font></body>');
			b_write('</html>');
			exit;
		} else {
			unlink($SEARCHDATADIR.'/words.tmp');
			$globaltime = date("i:s", (time() - $session->vars['globaltime']));
			$session->destroy();

			b_write('<html>');
			b_write('<head><title></title></head>');
			b_write('<body>');
			b_write('<br /><font face="Arial, Helvetica, Verdana" size=3><b>');
			b_write($MESSAGES['FILE_SRC'][43].'</b></font><br />');
			b_write('<font face="Arial, Helvetica, Verdana" size=2>');
			b_write($MESSAGES['FILE_SRC'][42].$globaltime);
			b_write('</font></body>');
			b_write('</html>');
			exit;
		}
	}
}

// --------------------------------------
// M A I N
// --------------------------------------

// add index fields:
$indexfields =  explode(":", $session->vars['fieldstoindex']);
for($i=0; $i<count($indexfields); $i++) {
	if($indexfields[$i] != '') {
		addIndexField($indexfields[$i]);
	}
}

// optimize stop word db
$stopdb = $session->vars['stopwords'];

if($session->vars['sortstopdb'] == $MESSAGES['ON'] AND $session->vars['optimized'] === false) {
	optimize_stopdb($stopdb);
}

$STOP = file($stopdb);

$STOP_MAX = 0;
$STOP_COUNT = count($STOP);
for($i = 0; $i < $STOP_COUNT; $i++) {
	if(strlen($STOP[$i]) > $STOP_MAX) {
		$STOP_MAX = strlen($STOP[$i]);
	}
}
unset($STOP_COUNT);

// decide, which task has to be performed
if(isset($session->vars['task'])) {
	$task = $session->vars['task'];
} else {
	$task = '';
}

switch($task) {
	case 'MERGER1':
		merger1();
		soft_exit();
	case 'MERGER2':
		merger2();
		soft_exit();
	case 'INDEX':
		doindex();
		soft_exit();
	case 'STARTINDEX':
		index();
		soft_exit();
	default:
		index();
		soft_exit();
}

?>