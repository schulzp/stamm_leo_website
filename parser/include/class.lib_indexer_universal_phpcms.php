<?php
/* $Id: class.lib_indexer_universal_phpcms.php,v 1.3.2.32 2006/06/18 18:07:31 ignatius0815 Exp $ */
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
   |    Beate Paland (beate76)
   |    Henning Poerschke (hpoe)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

// include cookie container
include_once(PHPCMS_INCLUDEPATH.'/class.lib_indexer_cookiecontainer_phpcms.php');

/**********************************************************************
 * Read the indexer profiles
 *********************************************************************/

function read_profiles() {

	global $PHPCMS_INDEXER_SAVE_FILE_NAME;

	$filename = PHPCMS_INCLUDEPATH.'/'.$PHPCMS_INDEXER_SAVE_FILE_NAME;
	if(!file_exists($filename)) {
		return array();
	}

	$data = file($filename);
	$data = trim($data[1]);
	$data = stripslashes($data);
	$data = str_replace ('\\10',chr(10),$data);
	$data = str_replace ('\\n\\r',"\n\r",$data);
	$data = str_replace ('\\n',"\n",$data);
	$data = str_replace ('\\r',"\r",$data);
	$data = str_replace ('@@',"\\",$data);

	return unserialize ($data);

} // end read_profiles

/**********************************************************************
 * Write the indexer profiles
 *********************************************************************/

function write_profiles($data) {

	global $PHPCMS_INDEXER_SAVE_FILE_NAME;

	$filename = PHPCMS_INCLUDEPATH.'/'.$PHPCMS_INDEXER_SAVE_FILE_NAME;

	$data = serialize ($data);
	$data = str_replace ("\\",'@@',$data);
	$data = str_replace ("\n\r",'\\\\n\\\\r',$data);
	$data = str_replace ("\n",'\\\\n',$data);
	$data = str_replace ("\r",'\\\\r',$data);
	$data = str_replace (chr(10),'\\\\10',$data);
	addslashes ($data);
	$data = $data."\n";

	$fp = fopen($filename, 'wb+');
	fwrite ($fp, "<?php/*\n", 8);
	fwrite ($fp, $data, strlen($data));
	fwrite ($fp, "*/?".">\n", 5);
	fclose($fp);

} // end write_profiles

/**********************************************************************
 * Initialize the session variables
 *********************************************************************/

function unset_all() {

	global $session;

	unset(
		$session->vars['startadress'],
		$session->vars['url_adress'],
		$session->vars['url_have_spidered'],
		$session->vars['url_have_indexed'],
		$session->vars['url_to_spider'],
		$session->vars['url_failure'],
		$session->vars['url_name'],
		$session->vars['url_pattern'],
		$session->vars['url_replacement'],
		$session->vars['host'],
		$session->vars['exclude'],
		$session->vars['include'],
		$session->vars['robots'],
		$session->vars['meta'],
		$session->vars['savedata'],
		$session->vars['gzip'],
		$session->vars['noextensions'],
		$session->vars['stopword'],
		$session->vars['wordlength'],
		$session->vars['buffer'],
		$session->vars['index_len'],
		$session->vars['meta_desc'],
		$session->vars['reference']);

} // end unset_all

/**********************************************************************
 * Remove session ID
 *********************************************************************/

function remove_sid($id,&$alink) {

	if(strpos(strtoupper($alink), strtoupper($id)) !== false) {
		$store = FALSE;
		$full_sid = substr($alink, strpos($alink,$id)-1);
		$fsid = substr($alink, strpos($alink,$id));

		if(strpos($fsid, '&') !== false) {
			$fsid = substr($fsid,0,strpos($fsid, '&')-1);
			$store=TRUE;
		}

		if(strpos($fsid, '#') !== false) {
			$fsid = substr($fsid,0,strpos($fsid, '#'));
		}

		if(strpos($fsid,'?') !== false OR $store==TRUE) {
			$alink = $full_sid[0].str_replace($fsid,'?',$alink);
		}
		else {
			$alink = str_replace($full_sid,'',$alink);
		}

		if($alink[strlen($alink)-1] == '?' OR $alink[strlen($alink)-1] == '&') {
			$alink = substr($alink,-1);
		}

		if($alink[0] == '?' OR $alink[0] == '&') {
			$alink = substr($alink,1);
		}
	} // endif

} // end remove_sid

/**********************************************************************
 * Remove unwanted characters
 *********************************************************************/

function cleanChars($string) {
	$strip_array = array(
	"!",	":",	"\"",	"'",	"@",
	"&lt",	"&gt",	"$",	"%",	"(",
	")",	"[",	"]",	"{",	"}",
	"?",	"*",	"+",	"|",	"^",
	"'",	"´",	"`",	"~",	"\n", "\r", "\t",
	"¦",	" ",	"=",	"\\",	"/",
	"..",	"	",	"  ",
	"&#8220;", "&#8221;", "&#8222;",
	"&#8217;", "&#8216;",

// en dash
	"&ndash;", "&#8211;", "–",
// em dash
	"&mdash;", "&#8212;", "—",
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
	"…", "&#8230;", "&hellip;",

	";", "&#", "#", "&"
	);
	$string = str_replace($strip_array, ' ', $string);

	// strip double spaces
	while(strpos($string, '  ') !== false) {
		$string = str_replace('  ', ' ', $string);
	}

	// index numbers w/o separators?
	//$string = preg_replace("/(\d+)(\.)?(\d+)/","\\1\\3",$string);
	//$string = preg_replace("/(\d+)(\,)?(\d+)/","\\1\\3",$string);

	//	disallow indexing of numbers and separators?
	//$strip_array = array(".", ",", "1", "2", "3", "4", "5", "6", "7", "8", "9", "0");
	//$string = str_replace($strip_array, ' ', $string);

	$string = preg_replace("/([a-zA-Z]+)(\.) /si","\\1 ",$string);
	$string = preg_replace("/([a-zA-Z]+)(\,) /si","\\1 ",$string);

$string = trim($string);
return $string;
}

//formerly known as make_german()
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
	"Æ"	=>		"æ",
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
	$string = str_replace($key, $value, $string);
	}
$string = strtolower($string);
return $string;
}


function revertDiacritical($string) {
	$string = str_replace('>','> ',$string);

// (\&[a-zA-Z0-9]+;)(.)*:=(.)*(\&#[0-9]+;)
// \t$string = str_replace ('\1', ' ', $string);\n\t$string = str_replace ('\4', ' ', $string);

$replacement = array(


// Ampersand
	"&amp;"		=>	 "&",
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
	"&iuml;"		=>		"ï",
	"&#239;"		=>		"ï",
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

/**********************************************************************
 * our (better) strip_tags
 *********************************************************************/

function mstrip_tags($tostrip) {
	$tr = '[\040|\n|\t|\r]*?';

	$search = '/<'.$tr.'script'.$tr.'[^>]*>'.$tr.'.*?'.$tr.'<'.$tr.'\/script'.$tr.'[^>]*>/is';
		$replace = ' ';
		$tostrip = preg_replace($search,$replace,$tostrip);

	$search = '/<'.$tr.'noscript'.$tr.'[^>]*>'.$tr.'.*?'.$tr.'<'.$tr.'\/noscript'.$tr.'[^>]*>/is';
		$replace = ' ';
		$tostrip = preg_replace($search,$replace,$tostrip);

	$search = '/<'.$tr.'style'.$tr.'[^>]*>'.$tr.'.*?'.$tr.'<'.$tr.'\/style'.$tr.'[^>]*>/Uis';
		$replace = ' ';
		$tostrip = preg_replace($search,$replace,$tostrip);

	$search = '/<!--(.*)-->/Uis';
		$tostrip = preg_replace($search,' ',$tostrip);

	$search = '/<[^>]+>/s';
		$tostrip = preg_replace($search,' ',$tostrip);

	$search = '/"[<|>]"/is';
		$tostrip = preg_replace($search,' ',$tostrip);

	return $tostrip;
	}

/**********************************************************************
 * Reduce the length of the body field for displaying in the search
 * results. Length can be changed with $MaxChar
 *********************************************************************/

function MakeShortWords($words) {
	global $session;

	$MaxChar = $session->vars['description'];

	if(($pos = strpos(strtoupper($words),'<!-- PHPCMS CONTENT STOP -->')) !== false) {
		$words = substr($words,0,$pos);
	}
	$words = revertDiacritical($words);
	$words = mstrip_tags($words);

	// remove some unwanted chars
	$strip_array = array("\n", "\r", "\t");
	$words = str_replace($strip_array, ' ', $words);

	// strip double spaces
	while(strpos($words, '  ') !== false) {
		$words = str_replace('  ', ' ', $words);
	}

	if(strlen($words) > $MaxChar) {
		$words = substr($words,0,$MaxChar);
		$lpos  = strrpos($words,' ');
		$words = substr($words,0,$lpos);
	}
	$words = str_replace(';','##',$words);

	return $words;

} // end MakeShortWords


/**********************************************************************
 * Prepare the body for indexing
 *********************************************************************/

function prepare_body($body) {

	$body = str_replace ('<',' <',$body);
	$body = str_replace ('>','> ',$body);
	$body = revertDiacritical($body);
	$body = string_tolower($body);
	$body = mstrip_tags($body);
	$body = cleanChars($body);
	$body = trim($body);

	return $body;

} // end prepare_body

/**********************************************************************
 * Get relative path from URL
 *********************************************************************/

function get_relative_path($current_path, $url) {

	//echo $current_path.'::'.$url."\n";

	// handle empty urls
	if(!isset($url) OR $url == '') {
		return '/';
	}

	// check if mail address
	$test = preg_replace('/[a-z\-\_\.0-9]*@[a-z\-\_\.0-9]*\.[a-z]*/i','',$url);
	if (trim($test) == '') {
		return '';
	}

	// prepare path
	if($url[0] != '/' AND $url[0] != '#') {

		if(substr($current_path,-1) == '/') {
			// take dir
			$pathdir = $current_path;
		}
		else {
			// delete query string
			if(strstr($current_path,'?')) {
				$pathdir = dirname(substr($current_path,0,strpos($current_path,'?')));
			}
			else {
				$pathdir = dirname($current_path);
			}
			if ($pathdir == '\\') {
				$pathdir = '/';
			}
		} // end else

		// handle '../'
		while (substr($url,0,3) == '../') {
			$url = substr($url,3);
			// that works only for '../' at the beginning of the url (but who cares)
			$pathdir = substr($pathdir,0,strrpos(rtrim($pathdir,'/'),'/'));
		}

		while (substr($url,0,2) == './') {
			$url = substr($url,2);
		}

		if($pathdir != '/') {
			$url = $pathdir.'/'.$url;
		}
		else {
			$url = '/'.$url;
		}

		while (strpos($url,'//') !== false) {
			$url = str_replace('//','/',$url);
		}

	} // endif

	// remove session-id
	while(strpos(strtoupper($url),'FSID=') !== false) {
		remove_sid('FSID=',$url);
	}
	while(strpos(strtoupper($url),'PHPSESSID=') !== false) {
		remove_sid('PHPSESSID=',$url);
	}
	// note: other SID names (e.g. SID) seemes not to be handled

	// remove hash sign (Raute)
	if (strpos($url, '#') !== false) {
		$raute = substr($url, strpos($url,'#'));

		if(strpos($raute,'&') !== false) {
			$raute=substr($raute,0,strpos($raute, '&'));
		}

		if(strpos($raute,'?') !== false) {
			$raute=substr($raute,0,strpos($raute, '?'));
		}

		$url = str_replace($raute,'',$url);
	} // endif
	//echo $url."\n";

	return $url;

} // end get_relative_path

/**********************************************************************
 * Get page and the urls in it
 *********************************************************************/

function get_urls(&$body)
	{
	global $session, $MESSAGES, $DEFAULTS;

	$body='';
	$page='';

	# Zu holende Adresse festlegen
	$adress = $session->vars['url_adress'][$session->vars['url_to_spider'][0]];
	if (strpos($adress,'/') === false)
		{
		$path = '/';
		$host = $adress;
		}
	else
		{
		// this caused bug #753285 HTTP-Indexer chokes on extension-less files
		/*
		$file = substr($adress,strrpos($adress,'/'));
		if ( strpos( $file, '.' ) === false)
			{
			$path = substr($adress,strpos($adress,'/')).'/';
			}
		else
			{
			$path = substr($adress,strpos($adress,'/'));
			}
			*/
		$path = substr($adress,strpos($adress,'/'));
		$host = substr($adress,0,strpos($adress,'/'));
		}

	# Seite holen
	$http_header = get_http($host,$path,$page);
	$http_header_uc = strtoupper($http_header);
	// echo 'Pagesize: '.strlen($page)."\n";
	while (strpos($http_header_uc,'301 MOVED PERMANENTLY') !== false OR
			strpos($http_header_uc,'302 FOUND') !== false  OR
			strpos($http_header_uc,'303 SEE OTHER') !== false  OR
			strpos($http_header_uc,'307 TEMPORARY REDIRECT') !== false  OR
			strpos($http_header_uc,'CONTENT-BASE:') !== false )
		{
		//echo 'Redirection: '.$host.$path.$page."\n";
		flush();
		@ob_end_flush();
		# Wir haben eine Umleitung zu einer anderen Adresse erhalten!
		if (strpos($http_header_uc,'CONTENT-BASE:') !== false)
			{
			preg_match_all('/Content-Base\40*:\40*([^\r]*)\r\n/Uis',$http_header,$matches);
			$newpath = trim($matches[1][0]);
			}
		elseif (strpos($http_header_uc,'LOCATION:') !== false)
			{
			preg_match_all('/Location\40*:\40*([^\r]*)\r\n/Uis',$http_header,$matches);
			$newpath = trim($matches[1][0]);
			}
		else
			{
			echo 'Fehler: kein Location Header bei redirection!'."\n";
			echo $http_header."\n";
			flush();
			@ob_end_flush();
			return TRUE;
			}

		# Abbruch https kann nicht verarbeitet werden
		if (strpos(strtoupper($newpath), 'HTTPS://') !== false)
			return TRUE;

		# Prüfen ob Request auf fremden host (der nicht erlaubt ist)
		if (strpos(strtoupper($newpath), 'HTTP://') !== false)
			{
			$newhost = substr($newpath,8);
			if(strpos($newhost,'/') !== false)
				{
				$newpath = trim(substr($newhost,strpos($newhost,'/')));
				$newhost = substr($newhost,0,strpos($newhost,'/'));
				}
			else
				$newpath = '/';
			# Abbruch wenn nicht erlaubt.
			if (!in_array($newhost.'/',$session->vars['host'],TRUE))
				return TRUE;
			}
		else
			{
			$newhost = $host;
         $newpath = get_relative_path($path, $newpath);
			}

		# Prüfen auf Ausschluß
		foreach($session->vars['exclude'] as $aAddress) {
			if($aAddress != '') {
				if (strpos(strtoupper($newhost.$newpath),strtoupper($aAddress)) !== false)
					return TRUE;
				}
			}

		# Prüfen auf Einschluß
		if(isset($session->vars['include']) AND count($session->vars['include']) > 0)
			{
			$found = FALSE;
			foreach($session->vars['include'] as $aAddress) {
				if($aAddress != '') {
					if (strpos(strtoupper($newhost.$newpath),strtoupper($aAddress)) !== false) {
						$found = TRUE;
						break;
					}
				}
			}
			if ($found === FALSE)
				return TRUE;
			}

		# Alles ok Url holen
		if (in_array($newhost.$newpath,$session->vars['url_adress'],TRUE) === FALSE)
			{
			$session->vars['url_adress'][$session->vars['url_to_spider'][0]] = $newhost.$newpath;
			$host = $newhost;
			$path = $newpath;
			$page = '';
			$http_header = get_http($newhost,$newpath,$page);
			}
		else
			{
			return TRUE;
			}
		}

	# Fehlerbehandlung
 	$chunked_content=trim(str_replace($http_header,'',$page));
	$http_header_uc = strtoupper($http_header);
  	if (strpos($http_header_uc,'TRANSFER-ENCODING: CHUNKED') !== false)
		$t_page=Decode_Chunked_Message($chunked_content);
	else
		$t_page=$chunked_content;

	$error_line1 = 'phpCMS '.$DEFAULTS->VERSION."<br />\n";
	$error_line2 = $MESSAGES[57].$MESSAGES['ERRORCODES'][12];
	if ($page === FALSE
		OR substr($t_page,strlen($error_line1), strlen($error_line2)) == $MESSAGES[57].$MESSAGES['ERRORCODES'][12])
		{
		$session->vars['url_failure'][] = array_search($host.$path,$session->vars['url_adress']);
		return TRUE;
		}

    $page = $t_page;
    unset ( $t_page );

	$status = substr($http_header,0,strpos($http_header,"\r\n"));
	//echo 'HTTP: '.$status."\n<br />";
	if(strpos($status, '200') === false)
		{
		echo 'Status: '.$status."\n";
		flush();
		@ob_end_flush();

		if (	strpos($status,'400') !== false OR
				strpos($status,'401') !== false OR
				strpos($status,'402') !== false OR
				strpos($status,'403') !== false OR
				strpos($status,'404') !== false OR
				strpos($status,'405') !== false OR
				strpos($status,'406') !== false OR
				strpos($status,'407') !== false OR
				strpos($status,'408') !== false OR
				strpos($status,'409') !== false OR
				strpos($status,'410') !== false OR
				strpos($status,'411') !== false OR
				strpos($status,'412') !== false OR
				strpos($status,'413') !== false OR
				strpos($status,'414') !== false OR
				strpos($status,'415') !== false OR
				strpos($status,'416') !== false OR
				strpos($status,'417') !== false OR
				strpos($status,'500') !== false OR
				strpos($status,'501') !== false OR
				strpos($status,'502') !== false OR
				strpos($status,'503') !== false OR
				strpos($status,'504') !== false OR
				strpos($status,'505') !== false)
			{
			$session->vars['url_failure'][] = array_search($host.$path,$session->vars['url_adress']);
			return TRUE;
			}
		}


	if (strpos($http_header_uc, 'CONTENT-TYPE: TEXT/HTML') === false)
		{
		# Inhalt ist keine HTML-Seite, sondern Grafik ZIP o.ä.
		return TRUE;
		}

	$body = $page;

	// BOF new spider nofollow tag
	$tr = '[\040|\n|\t|\r]*';
	$search = '/<!--'.$tr.'PHPCMS_NOFOLLOW'.$tr.'--[^>]*>'.$tr.'.*?'.$tr.'<!--'.$tr.'\/PHPCMS_NOFOLLOW'.$tr.'[^>]*>/is';
	$replace = ' ';
	$page = preg_replace($search,$replace,$page);
	// EOF spider nofollow tag

	# Kommentare entfernen
	$search = "/<!--(.*)-->/Uis";
	$replace = '';
	$page = preg_replace($search,$replace,$page);

	# Checken ob durch META-INFOS erlaubt, wenn META ist on!
	# ALL, NONE, INDEX, NOINDEX, FOLLOW, NOFOLLOW
	$follow = TRUE;

	if ($session->vars['meta'] === TRUE)
		{
		# Vorrangig phpCMS.robots behandeln
		$matches = Array();
		$search = '/<'.$tr.'meta'.$tr.'name'.$tr.'='.$tr.'[\"|\']?'.$tr.'phpCMS.robots'.$tr.'[\"|\']?'.$tr.'content'.$tr.'='.$tr.'[\"|\']?'.$tr.'([noindexfollow\,]*)'.$tr.'[\"|\']?[^>]*>/is';

		if(preg_match($search,$page,$matches) == 1)
			{
			# Behandlung von phpCMS-META TAGS
			$robots_uc = strtoupper(trim($matches[1]));
			if (strpos($robots_uc,'NOINDEX') !== false)
				$body = '';
			if (strpos($robots_uc,'NOFOLLOW') !== false)
				$follow = FALSE;
			}
		else
			{
			$search = '/<'.$tr.'meta'.$tr.'name'.$tr.'='.$tr.'[\"|\']?'.$tr.'robots'.$tr.'[\"|\']?'.$tr.'content'.$tr.'='.$tr.'[\"|\']?'.$tr.'([noindexfollow\,]*)'.$tr.'[\"|\']?[^>]*>/is';
			if(preg_match($search,$page,$matches)==1)
				{
				$robots_uc = strtoupper(trim($matches[1]));
				if (strpos($robots_uc,'NOINDEX') !== false)
					$body = '';
				if (strpos($robots_uc,'NOFOLLOW') !== false)
					$follow = FALSE;
				}
			}
		}


	$retar = Array();

	if($follow === TRUE)
		{
		$trenner = '[\040|\n|\t|\r]*';

		$match = Array();

		# "<a href" holen
		preg_match_all('/<'.$tr.'a[^>]*href'.$tr.'='.$tr.'[\"|\']?([^\"|\'|\40|>]*)[\"|\'|\40][^>]*>/is',$page,$matches);
		if(isset($matches[1]) AND count($matches[1]) > 0)
			$match = $matches[1];
		unset($matches);

		// echo 'Linkanzahl (href): '.count($match)."\n";

		# "<frame src" holen
		$search = "/<".$tr."frame([^>]*src".$tr."=".$tr."[\"|\'|\\\\]*)([^\'|\"|>|\040]*)([\'|\"|>|\040|\\\\]*)/is";
		preg_match_all($search,$page,$matches);
		if(isset($matches[2]) AND count($matches[2]) > 0)
			$match = array_merge($match, $matches[2]);

		// echo 'Linkanzahl (frame): '.count($match)."\n";

		unset($matches);
		# "<meta" holen
		$search = "/content".$trenner."=".$trenner."([\"|\'])?([^\;]*);".$trenner."URL".$trenner."=".$trenner."([^\"|\'|>|\040]+)/iUs";
		preg_match_all($search,$page,$matches);

		if(isset($matches[3]) AND count($matches[3]) > 0)
			$match = array_merge($match, $matches[3]);

		// echo 'Linkanzahl (meta): '.count($match)."\n";

		if(count($match) > 0)
			{
			reset($match);
			foreach($match as $alink) {
				$alink = trim($alink);
				$alink_uc = strtoupper($alink);
				$alink_splitted = parse_url($alink_uc);

				if ($alink_uc == '')
					continue;

				# only links with HTTP scheme or to local files can be handled
				if(isset($alink_splitted['scheme']) && $alink_splitted['scheme'] !== 'HTTP') {
					continue;
				}

				# test on file extensions to be excluded
				$found = FALSE;
				if(isset($alink_splitted['path'])) {
					foreach($session->vars['noextensions'] as $ext) {
						if($ext != '') {
							$ext = strtoupper($ext);
							if(substr($ext,0,1) !== '.') {
								$ext = '.'.$ext;
							}
							$fileext = substr($alink_splitted['path'],-strlen($ext));
							if($ext == $filext) {
								$found = TRUE;
								break;
							}
						}
					}
				}
				if($found === TRUE) {
					continue;
				}

				# test on exclude string in URL
				$found = FALSE;
				foreach($session->vars['exclude'] as $aExcl) {
					if($aExcl != '') {
						if(strpos($alink_uc,strtoupper($aExcl)) !== false) {
							$found = TRUE;
							break;
						}
					}
				}

				if ($found === TRUE) {
					continue;
				}

				# link to external page or internal page with http
				if(isset($alink_splitted['scheme']) && $alink_splitted['scheme'] == 'HTTP') {

					# check for permitted server
					$found = FALSE;

					foreach($session->vars['host'] as $aServer) {
						$aServer = strtoupper(substr($aServer,0,-1));
						if(strpos($alink_splitted['host'],$aServer) !== false) {
							$found = TRUE;
							break;
						}
					}

					if($found === FALSE) {
						continue;
					}

					$thishost = substr($alink,7);
					if (strpos($thishost,'/') !== false) {
						$thispath = substr($thishost,strpos($thishost,'/'));
						$thishost = substr($thishost,0,strpos($thishost,'/'));
						$thispath = get_relative_path($thispath, $thispath);
					} else {
						$thispath = '/';
					}

					$alink = $thishost.$thispath;
					$alink_uc = strtoupper($alink);

					# Prüfen auf Einschluß
					if(isset($session->vars['include']) AND count($session->vars['include']) > 0) {
						$found = FALSE;
						foreach($session->vars['include'] as $aAddress) {
							if($aAddress != '') {
								if (strpos('HTTP://'.$alink_uc,strtoupper($aAddress)) !== false) {
									$found = TRUE;
									break;
								}
							}
						}
						if ($found === FALSE) {
							continue;
						}
					}

					$retar[] = $alink;
					continue;
					}

				# relativer link
				$alink = get_relative_path($path, $alink);
                if (trim($alink) == '')
                   continue;
				$alink = $host.$alink;
				$alink_uc = strtoupper($alink);

				# Prüfen auf Einschluß
				if(isset($session->vars['include']) AND count($session->vars['include']) > 0)
					{
					$found = FALSE;
					foreach($session->vars['include'] as $aAddress)
						{
						if (strpos('HTTP://'.$alink_uc,strtoupper($aAddress)) !== false)
							{
							$found = TRUE;
							break;
							}
						}
					if ($found === FALSE)
						{
						continue;
					}
					}

				$retar[] = $alink;
				}
			unset ($match);
			}

		}
	/*
	for ($i=0; $i < count($retar); $i++) {
		echo '<br />'.$retar[$i]."\n";
	}
	*/

	return $retar;

} // end get_urls

/**********************************************************************
 * Process HTTP request
 *********************************************************************/

function get_http($host,$path,&$page) {

	global $session;
	//echo('path: '.$path.'<br />');

	if(!isset($path) OR $path=='') {
		$path = '/';
	}

	// set port
	if (strstr($host,':')) {
		$pos = strpos($host,':');
		$port = trim(substr($host,$pos+1));
		$host = trim(substr($host, 0, $pos));
	}
	else {
		$port = '80';
	}

	$fp = FALSE;
	for($try=0;$try < 2 && !$fp;$try++) {
		// open socket
		$fp = @fsockopen( $host, $port, $errno, $errstr, 30 );
		if (!$fp){
			sleep(30);
		}
	} // end for

	if (!$fp) {
		$page = FALSE;
		return TRUE;
	}

	fputs( $fp, "GET $path HTTP/1.1\r\n" );
	fputs( $fp, "Host: $host\r\n" );

	if ($cookie = $session->vars['cookie']->send_cookie($host)) {
		fputs( $fp, $cookie );
	}

  	fputs( $fp, "Accept: */*\r\n");
  	fputs( $fp, "User-Agent: Mozilla/4.0 (compatible; phpCMS-Spider)\r\n");
	fputs( $fp, "Connection: close\r\n\r\n" );

	$page = '';
	$page_length = 0;
	# Maximal 2 MB holen
	while (!feof($fp) AND $page_length < 2048000 )
		{
		$page.= fgets( $fp, 1500 );
		$page_length = strlen($page);
		}
	fclose( $fp );

	$http_header = substr($page,0,strpos($page,"\r\n\r\n"));

	// handle cookie
	$all_headers = explode("\r\n",$http_header);
	foreach ($all_headers as $this_header) {
		$header_uc = strtoupper($this_header);
		if (strpos($header_uc, 'SET-COOKIE') !== false) {
			$cookie = substr($this_header, strpos($header_uc, 'SET-COOKIE:')+11);
			$session->vars['cookie']->put_cookie($cookie, $host);
		}
	} // end foreach

  	return $http_header;

} // end get_http

/**********************************************************************
 * Decoder for HTTP 1.1 packages
 *********************************************************************/

function Decode_Chunked_Message($message) {

	$DEBUG = FALSE;
	$CRLF_LENGTH = 2;
	$chunk_pos = 0;
	$crlf_pos = strpos($message ,"\r\n",$chunk_pos);
	$chunk_size = trim(substr($message,$chunk_pos,$crlf_pos));
	$octets_to_read = hexdec($chunk_size);
	$start_read = $crlf_pos + $CRLF_LENGTH;
	$buffer = '';

	while($octets_to_read > 0) {
		if($DEBUG) {
			echo "chunk_pos : $chunk_pos<br />
			crlf_pos : $crlf_pos<br />
			octets_to_read : $octets_to_read ($chunk_size)<br />
			start_read : $start_read<br /><br /><br />";
		}
		$buffer .= substr($message,$start_read,$octets_to_read);

		if($DEBUG) {
			echo "Buffer size:".strlen($buffer)."<br />";
			echo "Buffer content:".htmlentities(substr($message,$start_read,$octets_to_read))."<br />";
		}

		$chunk_pos = $start_read + $octets_to_read + $CRLF_LENGTH;

		if(strlen($message)>$chunk_pos) {
			$crlf_pos = @strpos ($message , "\r\n" , $chunk_pos);
			$chunk_size = trim(substr($message,$chunk_pos,$crlf_pos-$chunk_pos));
			$octets_to_read = hexdec($chunk_size);
			$start_read = $crlf_pos + $CRLF_LENGTH;
		}
		else {
			$octets_to_read = 0;
		}
	} // end while

	return $buffer;

} // end Decode_Chunked_Message

function TrimTitle($words) {
	$words = trim($words);
	$words = mstrip_tags($words);
	$words = revertDiacritical($words);
	$words = str_replace(';', '##', $words);
	return $words;
}

/**********************************************************************
 * Indexes a page
 *********************************************************************/

function index_page($index_page, $index_page_name, $STOP, $STOP_MAX, $actual_entry) {

	global
		$session,
		$DEFAULTS,
		$PHPCMS_INDEXER_TEMP_SAVE_PATH,
		$MESSAGES;

	//echo($STOP_MAX.'<br />');

	// seperator for RegExp
	$tr = '[\040|\n|\t|\r]*';

	// needed for compatibility between shell and GUI indexer (really?)
	if (!isset($session->vars['temp_path'])) {
		$session->vars['temp_path'] = substr($PHPCMS_INDEXER_TEMP_SAVE_PATH,0,-1);
	}

	$matches = Array();

	// strip out any text within comment-style noindex tags
	$search = '/<!--'.$tr.'PHPCMS_NOINDEX'.$tr.'--[^>]*>'.$tr.'.*?'.$tr.'<!--'.$tr.'\/PHPCMS_NOINDEX'.$tr.'[^>]*>/is';
	$replace = ' ';
	$index_page = preg_replace($search,$replace,$index_page);

	// set title
	$search = '/<'.$tr.'TITLE[^>]*>'.$tr.'(.*?)'.$tr.'<'.$tr.'\/TITLE[^>]*>/Uis';
	if(preg_match($search,$index_page,$matches) == 1) {
		// use content of title-tag
		$title = $matches[1];
	}
	else {
		// or page url
		$title = $index_page_name;
	}
	$title = TrimTitle($title);

	$description = '';

	// use meta-tag description?
	if ($session->vars['meta_desc'] === TRUE) {
		// use phpCMS description
		$search = '/<'.$tr.'meta'.$tr.'name'.$tr.'='.$tr.'[\"|\']?'.$tr.'phpCMS.description'.$tr.'[\"|\']?'.$tr.'content'.$tr.'='.$tr.'[\"|\']?'.$tr.'([^\'|\"|>]*)'.$tr.'[\"|\']?[^>]*>/is';
		if(preg_match($search,$index_page,$matches) == 1) {
			$description = trim($matches[1]);
		}
		else {
			$search = '/<'.$tr.'meta'.$tr.'name'.$tr.'='.$tr.'[\"|\']?'.$tr.'description'.$tr.'[\"|\']?'.$tr.'content'.$tr.'='.$tr.'[\"|\']?'.$tr.'([^\'|\"|>]*)'.$tr.'[\"|\']?[^>]*>/is';
			if(preg_match($search,$index_page,$matches) == 1) {
				$description = trim($matches[1]);
			}
		}
	} // end if

	// if no description (from meta tags) available
	if ($description == '') {

		// search for <-- PHPCMS CONTENT START -->
		$search = '/<!--'.$tr.'PHPCMS'.$tr.'CONTENT'.$tr.'START'.$tr.'-->(.*)<'.$tr.'\/BODY[^>]*>/is';
		if(preg_match($search,$index_page,$matches) == 1) {
			$description = trim($matches[1]);
		}
		// or use begin of body if not found
		else {
			$search = '/<'.$tr.'BODY[^>]*>(.*)<'.$tr.'\/BODY[^>]*>/is';
			if(preg_match($search,$index_page,$matches) == 1) {
				$description = trim($matches[1]);
			}
			else {
			// body is void (should that be HTML?)
				$description = $MESSAGES['HTTP_SRC'][00];
			}

		} // end else

	} // end if

	$description = MakeShortWords($description);

	$towrite = 'http://'.str_replace(';','##',$index_page_name);

	if (isset($session->vars['url_pattern']) AND $session->vars['url_pattern'] != '') {
		// replace url parts through entered pattern
		$towrite = preg_replace($session->vars['url_pattern'],$session->vars['url_replacement'],$towrite);
	} // end if

	$file_entry = $towrite.';'.$title.';'.$description."\n";
	//echo $towrite."\n";

	// prepare body
	$body = $index_page;
	$body = prepare_body($body);
	if(strlen($body) < $session->vars['wordlength']) {
		return;
	}

	$words_to_write = '';
	$wordAr = explode(' ', $body);
	$words = array_unique($wordAr);
	unset($wordAr);
	sort($words);

	reset($words);
	while(list($key, $val) = each($words)) {
		$val = trim($val);
		if(strlen($val) == 0) {
			continue;
		}
		if(!is_numeric($val)) {
			if(strlen($val) < $session->vars['wordlength']) {
			continue;
			}
		}
		if(strlen($val) < $STOP_MAX) {
			if(CheckArray($STOP, $val)) {
				continue;
			}
		}
		$words_to_write.= $val.'#'.$actual_entry.'#'.substr_count($body, $val)."\n";
		}
	unset($words);

	if (strlen($words_to_write) > $session->vars['wordlength']) {
		$fp = fopen($session->vars['temp_path'].'/'.'words.tmp','ab+');
		fwrite ($fp, $words_to_write, strlen($words_to_write));
		fclose ( $fp );
	} // end if

	return $file_entry;

} // end index_page

/**********************************************************************
 * Checks, if an entry is in a array
 * used for checking the stop-word-array
 *********************************************************************/

function CheckArray($sarray, $entry) {

	$ac = count($sarray);
	for($i = 0; $i < $ac; $i++) {
		if(trim($sarray[$i]) == trim($entry)) {
			return true;
		}
	}
	return false;

} // end CheckArray

/**********************************************************************
 * Read the robots.txt for every host in the profile
 *********************************************************************/

function get_robots() {

	global $session;

	// if robots.txt should be used
	if ($session->vars['robots'] === TRUE) {

		// for each host
		foreach($session->vars['host'] as $aServer) {

			unset($page);
			$host = 'http://'.substr($aServer,0,-1);
			$path = '/robots.txt';
			$page = @file($host.$path);

			// process robots.txt (if the file exists and isn't void)
			if(isset($page) AND is_array($page) AND count($page) > 0) {

				$page_count = count($page);
				for($i=0; $i < $page_count; $i++) {

					// remove comment
					if (strpos($page[$i],'#') !== false) {
						$page[$i] = substr($page[$i],0,strpos($page[$i],'#'));
					}

					// entry matches phpCMS spider or all clients?
					if ( preg_match('/User-agent:\40?(phpCMS-Spider)|\*/is',$page[$i]) == 1 ) {
						$i++;
						// remove comment
						if (strpos($page[$i],'#' ) !== false) {
							$page[$i] = substr($page[$i],0,strpos($page[$i],'#'));
						}

						$analyse = '';
						while (strpos(strtoupper($page[$i]),'USER-AGENT:') === false AND $i < $page_count ) {
							$analyse .= $page[$i];
							$i++;
							// remove comment
							if ( strpos( $page[$i],'#' ) !== false) {
								$page[$i] = substr ( $page[$i],0,strpos ( $page[$i],'#' ) );
							}
						} // end while
						$i--;

						if (strlen(trim($analyse)) > 0 ) {
							$matches = array();
							preg_match_all('/Disallow:\40?([^\40|\r|\n]*)[\40|\r|\n]/is',$analyse,$matches);

							if (isset($matches[1])) {
								foreach ($matches[1] as $aUrl ) {
									if (!in_array($aUrl,$session->vars['exclude'],TRUE)) {
										$session->vars['exclude'][] = trim($aUrl);
									} // end if

								} // end foreach

							} // end if

						} // end if

					} // end if

				} // end for ($page_count)

			} // end if

		} // end foreach

	} // end if

} // end get_robots

?>
