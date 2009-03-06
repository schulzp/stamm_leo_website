<?php
/* $Id: class.mail2crypt_phpcms.php,v 1.1.2.23 2006/06/18 18:07:31 ignatius0815 Exp $ */
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
   |    Henning Poerschke (hpoe)
   |    Tobias Dönz (tobiasd)
   |    Beate Paland (beate76)
   |    Markus Richert (e157m369)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

/*********************************************
  phpMail2Crypt v1.5.3 May 10 2003
  (Re)implementation of phpMail2Crypt sans PAX :)
  Automatic e-mail address spam proofing
  (c) 2002-2003 by Henning Poerschke
*********************************************/

/**
* class Mail2Crypt
*
* Mail2Crypt hides e-mail addresses from spambots.
*
* Mail2Crypt was designed to keep e-mail addresses from beeing harvested off websites
* while still maintaining accessibility. It uses JavaScript encryption, image and ASCII
* character substitution. Addresses remain accessible to non-JavaScript-enabled clients.
*
* Mail2Crypt uses a special tag sytax:
* <code><!-- MAIL2CRYPT mail@domain.xyz,link_text,icon,alternate_noscript_link --></code>
*
* @link      http://phpcms.de
* @package   phpCMS 1.2.0
* @copyright Copyright (c) 2002, 2003 Henning Poerschke
* @author    Henning Poerschke <hpoe@phpcms.de>
* @version   1.5.3 ($Date: 2006/06/18 18:07:31 $) $Revision: 1.1.2.23 $
* @access    public
*/
class Mail2Crypt {

	/**
	* string holds document root
	*
	* @access private
	* @var string
	*/
	var $_root;

	/**
	* string holds path to @-icon
	*
	* @access private
	* @var string
	*/
	var $_img_at;

	/**
	* string holds path to mailto-icon
	*
	* @access private
	* @var string
	*/
	var $_img_mailto;


	/**
	* automatical (TRUE) or manual (FALSE) inclusion of the external JS file
	*
	* @access private
	* @var boolean
	*/
	var $_autoadd_js = TRUE;

	/**
	* Constructor
	*
	* Sets some variables and creates external JS file if necessary
	*
	* @access public
	*
	*/
	function Mail2Crypt() {
		global
			$DEFAULTS,
			$HELPER,
			$MESSAGES;

		if(!defined("_M2C_")) {
			define("_M2C_", true);
		}

		if($DEFAULTS->MAIL2CRYPT == 'on') {
			// set some variables
			$this->_root = $DEFAULTS->DOCUMENT_ROOT;
			$this->_img_at = $this->_correctPath($DEFAULTS->MAIL2CRYPT_IMG).'at.gif';
			$this->_img_mailto = $this->_correctPath($DEFAULTS->MAIL2CRYPT_IMG).'mail.gif';

			// was path given correctly?
			$js_file = $this->_root.$this->_correctPath($DEFAULTS->MAIL2CRYPT_JS).'js_mail2crypt.js';
			// If JS file doesn't exist in the right place try to create a new one
			if(!file_exists($js_file)) {
				$mail2crypt_js = $this->Mail2CryptJS();
				$fp = @fopen($js_file, 'w+');
				if($fp) {
					fwrite($fp, "$mail2crypt_js");
					fclose($fp);
				} else {
					$js_code = $HELPER->html_entities($mail2crypt_js);
					ExitError(90, $MESSAGES['MAIL2CRYPT'][1], "$js_file<br />", "<pre>$js_code</pre>");
				}
			} // end if

		} // end if

	} // end function Mail2Crypt


	function crypt_mailto($lines) {
		global
			$DEFAULTS;

		$m2c_used = false;
		// look at each line separately
		for($linescount = 0; $linescount < count($lines); $linescount++) {

			// take note of where to include JavaScript decryption code if needed
			if($this->_autoadd_js AND (strpos(strtolower($lines[$linescount]),'</head>') !== FALSE)) {
				$inject_js_line = $linescount;
			}

			if(preg_match_all("/<!-- MAIL[2]?CRYPT (.*?) --[^>]*?".">/si", $lines[$linescount], $matches)) {
				$m2c_used = true;
				foreach($matches[1] as $thismatch) {
					// NOTE:
					// <!-- MAILCRYPT embed --> and <!-- MAILCRYPT include -->
					// are no longer needed, and support will be dropped
					// call to js_mail2crypt.js is inserted into <head>
					if($thismatch == 'include' OR $thismatch == 'embed') {
						continue;
					}

					// Convert e-mail address
					// <!-- MAIL2CRYPT info@domain.com,link text,icon,alternate noscript link -->
					// the followin line may create a notice message (Notice: Undefined offset: ...)
					$MC_params = explode(",", $thismatch);
					// e-mail address:
					$MC_address = isset($MC_params[0]) ? trim($MC_params[0]) : '';
					// link text:
					$MC_text = isset($MC_params[1]) ? trim($MC_params[1]) : '';
					// icon (built-in or custom)
					$MC_icon = isset($MC_params[2]) ? trim($MC_params[2]) : '';
					// noscript: paranoid (off) or page link
					$MC_noscript = isset($MC_params[3]) ? trim($MC_params[3]) : '';

					// if Mail2Crypt is off, display e-mail address unencrypted
					if($DEFAULTS->MAIL2CRYPT != 'on') {
						// to do: make noscript-area behavior similar to JS behavior
						$lines[$linescount] = str_replace('<!-- MAIL2CRYPT '.$thismatch.' -->', $MC_address, $lines[$linescount]);
						$lines[$linescount] = str_replace('<!-- MAILCRYPT '.$thismatch.' -->', $MC_address, $lines[$linescount]);
						continue;
					}

				// populate JS vars...
					// scramble e-mail address
					$nospamplease = '';
					for($i = 0; $i < strlen($MC_address); $i++) {
						$nospamplease .= ord(substr($MC_address, $i, $i + 1)) - 23;
					}

					// link text
					$idontlikespam = $MC_text;

					// icons...
					if($MC_icon != '') {
						// default icon is wished            //or path to iconfile is incorrect
						if(strtolower($MC_icon) == 'icon') { // || !file_exists("$this->_root/$MC_icon")) {
							// set default-mail-icon
							$MC_icon = $this->_img_mailto;
						}
						$nothanks = $MC_icon;
						if(file_exists("$this->_root/$MC_icon")) {
							$size = GetImageSize("$this->_root/$MC_icon");
							$no = $MC_icon_w = $size[0];
							$way = $MC_icon_h = $size[1];
						} else {
							$MC_icon = '';
							$nothanks = '';
							$no = '';
							$way = '';
						}
					}
					else {
						$nothanks = '';
						$no = '';
						$way = '';
					}

					// build JS code
					$crypt = '<script type="text/javascript" ><!--//--><![CDATA[//><!-- '."\n";
					$crypt .= 'showmail("'.$nospamplease.'", "'.$idontlikespam.'", "'.$nothanks.'", "'.$no.'", "'.$way.'")'."\n";
					$crypt .= '//--><!]]></script>';

					// clean up vars that are valid for this match only
					unset(
						$nospamplease,
						$idontlikespam,
						$nothanks,
						$no,
						$way);

					// done with JS code for this match
					// next: build <noscript> area
					if((strtolower($MC_noscript) != 'paranoid') && ($MC_noscript != 'p')) { // off;link;/contact.htm
						// prepare @-image
						// only do this once!
						if(!isset($at_subst)) {
							$at_subst = $this->_makeImageTag($this->_img_at,"&#64;");
						}

						// prepare mailto or custom image
						if($MC_icon != '') {
							$MC_icon = '<img src="'.$MC_icon.'" width="'.$MC_icon_w.'" height="'.$MC_icon_h.'" border="0" alt="" />';
						}
						// prepare e-mail address
						$parts = explode('@', $MC_address);
						$chars[0] = '';
						for($i = 0; $i < strlen($parts[0]); $i++) {
							$chars[0] .= "&#".ord(substr($parts[0], $i, $i + 1)).";";
						}
						$chars[1] = '';
						for($i=0; $i<strlen($parts[1]); $i++) {
							$chars[1] .= "&#".ord(substr($parts[1], $i, $i + 1)).";";
						}
						$email = $chars[0].$at_subst.$chars[1];

						// cases to consider...

						// no link text, no icon, no page link
						if($MC_text == '' AND $MC_icon == '' AND $MC_noscript == '') {
							$noscript = $email;
						}
						// page link; no link text, no icon
						elseif($MC_text == '' AND $MC_icon == '' AND $MC_noscript != '') {
							$noscript = '<a href="'.$MC_noscript.'">'.$email.'</a>';
						}
						// link text; no icon, no page link
						elseif($MC_text != '' AND $MC_icon == '' AND $MC_noscript == '') {
							$noscript = $MC_text.' ('.$email.')';
						}
						// link text, page link; no icon
						elseif($MC_text != '' AND $MC_icon == '' AND $MC_noscript != '') {
							$noscript = '<a href="'.$MC_noscript.'">'.$MC_text.'</a>';
						}
						// icon; no link text, no page link
						elseif($MC_text == '' AND $MC_icon != '' AND $MC_noscript == '') {
							$noscript = $email.' '.$MC_icon;
						}
						// icon, page link; no link text
						elseif($MC_text == '' AND $MC_icon != '' AND $MC_noscript != '') {
							$noscript = '<a href="'.$MC_noscript.'">'.$MC_icon.'</a>';
						}
						// icon, link text; no page link
						elseif($MC_text != '' AND $MC_icon != '' AND $MC_noscript == '') {
							$noscript = $MC_text.' '.$MC_icon.' ('.$email.')';
						}
						// link text,  icon, page link
						else {
							$noscript = '<a href="'.$MC_noscript.'">'.$MC_text.'</a> '.$MC_icon;
						}

						$crypt .= '<noscript>'.$noscript.'</noscript>';

						// clean up vars that are valid for this match only
						unset(
							$size,
							$chars,
							$MC_text,
							$MC_icon,
							$MC_noscript,
							$noscript);
					} // build <noscript> area

					if($DEFAULTS->MAIL2CRYPT == 'on') {
						$lines[$linescount] = str_replace('<!-- MAIL2CRYPT '.$thismatch.' -->', $crypt, $lines[$linescount]);
						$lines[$linescount] = str_replace('<!-- MAILCRYPT '.$thismatch.' -->', $crypt, $lines[$linescount]);
					}

				} // end foreach (match in line)

			} // endif (match found)

		} // end for (each line)

		// now, if there were any matches, insert JS into <head>
		if($DEFAULTS->MAIL2CRYPT == 'on' AND $this->_autoadd_js AND $m2c_used) {
			$js_include = '<script type="text/javascript" src="'.$DEFAULTS->MAIL2CRYPT_JS.'js_mail2crypt.js"></script>'."\n".'</head>';
			$lines[$inject_js_line] = str_replace("</head>", $js_include, $lines[$inject_js_line]);
			// and also for non-valid pages :(
			$lines[$inject_js_line] = str_replace("</HEAD>", $js_include, $lines[$inject_js_line]);
			// we could check if the closing head tag was found and output an error message if not (tobiasd)
		}
		return $lines;

	} // end crypt_mailto

	function Mail2CryptJS() {

		$mail2crypt_js  = '/* js_mail2crypt.js - v1.5 (c) 2002, 2003 by Henning Poerschke'."\n";
		$mail2crypt_js .= '+----------------------------------------------------------------------+'."\n";
		$mail2crypt_js .= '| phpCMS Content Management System - Version 1.2.0'."\n";
		$mail2crypt_js .= '+----------------------------------------------------------------------+'."\n";
		$mail2crypt_js .= '| phpCMS is Copyright (c) 2001-2003 by Michael Brauchl '."\n";
		$mail2crypt_js .= '| and Contributing phpCMS Team Members'."\n";
		$mail2crypt_js .= '| Mail2Crypt Copyright (c) 2002-2003 Henning Poerschke'."\n";
		$mail2crypt_js .= '+----------------------------------------------------------------------+'."\n";
		$mail2crypt_js .= '| This program is free software; you can redistribute it and/or modify'."\n";
		$mail2crypt_js .= '| it under the terms of the GNU General Public License as published by'."\n";
		$mail2crypt_js .= '| the Free Software Foundation; either version 2 of the License, or'."\n";
		$mail2crypt_js .= '| (at your option) any later version. '."\n";
		$mail2crypt_js .= '|'."\n";
		$mail2crypt_js .= '| This program is distributed in the hope that it will be useful, but'."\n";
		$mail2crypt_js .= '| WITHOUT ANY WARRANTY; without even the implied warranty of'."\n";
		$mail2crypt_js .= '| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU'."\n";
		$mail2crypt_js .= '| General Public License for more details.'."\n";
		$mail2crypt_js .= '|'."\n";
		$mail2crypt_js .= '| You should have received a copy of the GNU General Public License'."\n";
		$mail2crypt_js .= '| along with this program; if not, write to the Free Software'."\n";
		$mail2crypt_js .= '| Foundation, Inc., 59 Temple Place - Suite 330, Boston,'."\n";
		$mail2crypt_js .= '| MA  02111-1307, USA.'."\n";
		$mail2crypt_js .= '+----------------------------------------------------------------------+'."\n";
		$mail2crypt_js .= '*/'."\n";
		$mail2crypt_js .= 'function showmail(nospamplease, idontlikespam, nothanks, no, way){'."\n";
		$mail2crypt_js .= 'var str_out = \'\'; '."\n";
		$mail2crypt_js .= 'var num_out = \'\';'."\n";
		$mail2crypt_js .= 'var num_in;'."\n";
		$mail2crypt_js .= 'var hex = \'0123456789abcdef\''."\n";
    $mail2crypt_js .= 'num_out = nospamplease;  '."\n";
		$mail2crypt_js .= 'for(i = 0; i < num_out.length; i += 2) {'."\n";
    $mail2crypt_js .= 'num_in1 = \'\''."\n";
    $mail2crypt_js .= 'num_in = parseInt(num_out.substr(i,2)) + 23;'."\n";
    $mail2crypt_js .= 'while (num_in != 0) {'."\n";
    $mail2crypt_js .= 'num_in1 = hex.charAt(num_in%16)+num_in1;'."\n";
    $mail2crypt_js .= 'num_in = num_in >> 4;}'."\n";
    $mail2crypt_js .= 'num_in = unescape(\'%\' + num_in1);'."\n";
		$mail2crypt_js .= 'str_out += num_in;'."\n";
		$mail2crypt_js .= 'str_out = unescape(str_out);}'."\n";
		$mail2crypt_js .= 'nospamplease = str_out;'."\n";
		$mail2crypt_js .= 'speakfriendandenter = \'&#109;&#97;&#105;&#108;&#116;&#111;:\' + nospamplease;'."\n";
		$mail2crypt_js .= 'voila = \'<a href="\' + speakfriendandenter + \'" title="\'+ nospamplease + \'">\';'."\n";
		$mail2crypt_js .= 'if((idontlikespam != "") && (nothanks != "")) voila = idontlikespam  + " " + voila;'."\n";
		$mail2crypt_js .= 'else if((idontlikespam != "") && (nothanks == "")) voila += idontlikespam;'."\n";
		$mail2crypt_js .= 'if(nothanks != "") voila += \'<img src="\' + nothanks + \'" width="\' + no + \'" height="\' + way + \'" border="0" alt="\' + nospamplease + \'" />\' ;'."\n";
		$mail2crypt_js .= 'else if((idontlikespam == "") && (nothanks == "")) voila += nospamplease;'."\n";
		$mail2crypt_js .= 'voila += \'</a>\';'."\n";
		$mail2crypt_js .= 'document.write(voila);}'."\n";

		return $mail2crypt_js;

	} // end Mail2CryptJS

	function _correctPath($path) {
		if($path[strlen($path) - 1] != '/') {
			$path  .= '/';
		}
		return $path;
	} // function _correctPath()

	function _makeImageTag($image,$alt="") {
		if(file_exists($this->_root.$image)) {
			$size = GetImageSize($this->_root.$image);
			$this->img_w = $size[0];
			$this->img_h = $size[1];
			$img_tag = '<img src="'.$image.'" width="'.$this->img_w.'" height="'.$this->img_h.'" border="0" alt="'.$alt.'" />';
		}
		return $img_tag;
	} // function _makeImageTag()

} // end class MailCrypt

?>
