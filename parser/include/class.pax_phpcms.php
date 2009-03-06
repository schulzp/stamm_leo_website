<?php
/* $Id: class.pax_phpcms.php,v 1.7.2.22 2006/06/18 18:07:32 ignatius0815 Exp $ */
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
   |    Wernhard Ch. Kempinger
   |    Tobias Dönz (tobiasd)
   |    Henning Poerschke (hpoe)
   |    Markus Richert (e157m369)
   |    Andre Meiske (guandalug)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

if(!defined("_PAX_")) {
	define("_PAX_", TRUE);
}

// functions for PAX - phpCMS preparser
// Author: Wernhard Kempinger, 2001 -2002

function ProcessTags($tags, $line) {
	$html = '';
	global
		$color,
		$size,
		$bullets;

	// Remove spaces.
	$tags = trim($tags);

	// Found the beginning of the bulleted list.
	if(ereg("\\\pnindent", $tags)) {
		$html .= '<ul><li>';
		$bullets += $line;
		$tags = ereg_replace("\\\par", "", $tags);
		$tags = ereg_replace("\\\(tab)", "", $tags);
	}
	if($line - $bullets == 0) {
		$tags = ereg_replace("\\\par", "", $tags);
	} elseif($line - $bullets == 1) {
		if(ereg("\\\pntext", $tags)) {
			$html .= '<li>';
			$tags = ereg_replace("\\\par", "", $tags);
			$tags = ereg_replace("\\\(tab)", "", $tags);
			$bullets++;
		} else {
			$html .= '</ul>';
			$bullets = 0;
		}
	}
	// Convert Bold.
	if(ereg("\\\b0", $tags)) {
		$html .= '</b>';
	} elseif(ereg("\\\b", $tags)) {
		$html .= '<b>';
	}
	// Convert Italic.
	if(ereg("\\\i0", $tags)) {
		$html .= '</i>';
	} elseif(ereg("\\\i", $tags)) {
		$html .= '<i>';
	}
	// Convert Underline.
	if(ereg("\\\ulnone", $tags)) {
		$html .= '</u>';
	} elseif(ereg("\\\ul", $tags)) {
		$html .= '<u>';
	}
	// Convert Alignments.
	if(ereg("\\\pard\\\qc", $tags)) {
		$html .= '<div align="center">';
	} elseif(ereg("\\\pard\\\qr", $tags)) {
		$html .= '<div align="right">';
	} elseif(ereg("\\\pard", $tags)) {
		$html .= '<div align="left">';
	}

	// Remove \pard from the tags so it doesn't get confused with \par.
	$tags = ereg_replace("\\\pard", "", $tags);

	// Convert line breaks.
	if(ereg("\\\par", $tags)) {
		$html .= '<br />';
	}

	// Use the color table to capture the font color changes.
	if(ereg("\\\cf[0-9]", $tags)) {
		global $fcolor;
		$numcolors = count($fcolor);
		for($i = 0; $i < $numcolors; $i++) {
			$test = "\\\cf".($i + 1);
			if(ereg($test, $tags)) {
				$color = $fcolor[$i];
			}
		}
	}

	// Capture font size changes.
	if(ereg("\\\fs[0-9][0-9]", $tags, $temp)) {
		$size = ereg_replace("\\\fs", "", $temp[0]);
		$size /= 2;
		if($size <= 10) {
			$size = 1;
		} elseif($size <= 12) {
			$size = 2;
		} elseif($size <= 14) {
			$size = 3;
		} elseif($size <= 16) {
			$size = 4;
		} elseif($size <= 18) {
			$size = 5;
		} elseif($size <= 20) {
			$size = 6;
		} elseif($size <= 22) {
			$size = 7;
		} else {
			$size = 8;
		}
	}

	// If there was a font color or size change, change the font tag now.
	if(@ereg("(\\\cf[0-9])||(\\\fs[0-9][0-9])", $tags)) {
		$html .= '</font><font size="'.$size.'" color="'.$color.'">';
	}

	// Replace \tab with alternating spaces and nonbreakingwhitespaces.
	if(@ereg("\\\(tab)", $tags)) {
		$html .= '&nbsp; &nbsp; &nbsp; &nbsp; ';
	}

	return $html;
}

function ProcessWord($word) {
	// Replace \\ with \
	$word = ereg_replace("[\\]{2,}", "\\", $word);

	// Replace \{ with {
	$word = ereg_replace("[\\][\{]", "\{", $word);

	// Replace \} with }
	$word = ereg_replace("[\\][\}]", "\}", $word);

	// Replace 2 spaces with one space.
	$word = ereg_replace("  ", "&nbsp;&nbsp;", $word);

	return $word;
}

/*********************************************
  make combo from menu with all menues
*********************************************/
function _setPAXCOMBOALL($contentfile) {
	global
		$PAXMENUDEF,
		$CHECK_PAGE;

	$countcontentfile = count($contentfile);
	$nrcombo = 0;

	for($contcount = 0; $contcount < $countcontentfile; $contcount++) {
		if(preg_match_all("/<!-- PAXCOMBOALL ([a-zA-Z0-9_,:~#=\|\-\+\.\*\s]+) -->/", $contentfile[$contcount], $matches)) {
			foreach($matches[1] as $Pax_MenuPara) {
				list($M_DISPAY_FIELD, $M_SIZE, $M_MULTI, $M_FORM, $M_FIRSTENTRY) = explode(",", $Pax_MenuPara);

				$M_DISPAY_FIELD = trim($M_DISPAY_FIELD);
				$M_SIZE = trim($M_SIZE);
				$M_MULTI = trim(strtoupper($M_MULTI));
				$M_FORM = trim(strtoupper($M_FORM));

				$PAX_Tag = '<!-- PAXCOMBOALL '.$Pax_MenuPara.' -->';

				if($M_MULTI == 'ON') $multicombo = ' multiple';

				$Menues = count($PAXMENUDEF);

				if($M_SIZE) {
					$CSIZE = ' size="'.$M_SIZE.'"';
				} else {
					$CSIZE = '';
				}

				if($M_FORM == 'ON') {
					$paxcombo .= "\n<form action=\"$CHECK_PAGE->path/$CHECK_PAGE->name\">\n<select name=\"PAXCOMBONESTED$nrcombo\"$CSIZE onchange=\"document.location=this.form.PAXCOMBONESTED$nrcombo.options[this.form.PAXCOMBONESTED$nrcombo.selectedIndex].value\"$multicombo>";
				} else {
					$paxcombo .= "\n<select name=\"PAXCOMBONESTED$nrcombo\"$CSIZE$multicombo>";
				}

				if(strlen($M_FIRSTENTRY) > 0) {
					$paxcombo .= "\n<option value=\"$CHECK_PAGE->path/$CHECK_PAGE->name\">$M_FIRSTENTRY</option>";
				}

				for($MenuCount = 0; $MenuCount < $Menues; $MenuCount ++) {
					$MenuZeilen = count($PAXMENUDEF[$MenuCount]);

					// für jedes menü eine group einziehen
					$paxcombo .= "\n".'<optgroup label="-">';

					for($ZeilenCount = 0; $ZeilenCount < $MenuZeilen; $ZeilenCount++) {

						$MenuZellen = count($PAXMENUDEF[$MenuCount][$ZeilenCount]);
						for($ZellenCount = 0; $ZellenCount < $MenuZellen; $ZellenCount++) {
							$PAX_Class = $PAXMENUDEF[$MenuCount][$ZeilenCount][$ZellenCount]['CLASS'];
							$PAX_Value = $PAXMENUDEF[$MenuCount][$ZeilenCount][$ZellenCount]['VALUE'];
							$PAX_Field = $PAXMENUDEF[$MenuCount][$ZeilenCount][$ZellenCount]['FIELD'];

							if($M_DISPAY_FIELD == $PAX_Field) {
								$dispay = $PAX_Value;
								$MDISPAY = TRUE;
							}
							if("LINK" == $PAX_Field) {
								$value =  $PAX_Value;
								$MVALUE = TRUE;
							}
							if($MDISPAY AND $MVALUE) {
								$paxcombo .= "\n".'<option label="'.$dispay.'" value="'.$value.'">'.$dispay.'</option>';
								$MDISPAY = FALSE;
								$MVALUE = FALSE;
							}
						}
					}
					$paxcombo .= '</optgroup>'."\n";
				}
			}
			if($M_FORM == 'ON') {
				$paxcombo .= "\n".'</select></form>';
			} else {
				$paxcombo .= "\n".'</select>';
			}

			$contentfile[$contcount] = str_replace('<!-- PAXCOMBOALL '.$Pax_MenuPara.' -->', $paxcombo, $contentfile[$contcount]);
			unset($multicombo);
			unset($paxcombo);
			unset($selected);
			unset($dispay);
			unset($value);
			$nrcombo++;
		}
	}
	return $contentfile;
} // END func _setPAXCOMBOALL

/*********************************************
  make combo from menu with special fields
*********************************************/
function _setPAXCOMBO($contentfile) {
	global
		$PAXMENUDEF,
		$CHECK_PAGE;

	$nrcombo = 0;
	$countcontentfile = count($contentfile);

	for($contcount = 0; $contcount < $countcontentfile; $contcount++) {
		if(preg_match_all("/<!-- PAXCOMBO ([a-zA-Z0-9_,:~#=\|\-\+\.\*\s]+) -->/", $contentfile[$contcount], $matches)) {
			foreach($matches[1] as $Pax_MenuPara) {
				list($M_MENUNAME, $M_DISPAY_FIELD, $M_OPT_VALUES, $M_VAL_SELECTED, $M_SIZE, $M_MULTI, $M_FORM, $M_FIRSTENTRY) = explode(",", $Pax_MenuPara);

				$M_MENUNAME = trim($M_MENUNAME);
				$M_DISPAY_FIELD = trim($M_DISPAY_FIELD);
				$M_OPT_VALUES = trim($M_OPT_VALUES);
				$M_VAL_SELECTED = trim($M_VAL_SELECTED);
				$M_SIZE = trim($M_SIZE);
				$M_MULTI = trim(strtoupper($M_MULTI));
				$M_FORM = trim(strtoupper($M_FORM));

				$PAX_Tag = '<!-- PAXCOMBO '.$Pax_MenuPara.' -->';

				if($M_MULTI == 'ON') {
					$multicombo = ' multiple';
				}

				$Menues = count($PAXMENUDEF);

				for($MenuCount = 0; $MenuCount < $Menues;  $MenuCount++) {
					if($M_MENUNAME == $PAXMENUDEF[$MenuCount]['MENU']) {
						if($M_SIZE) {
							$CSIZE = ' size="'.$M_SIZE.'"';
						} else {
							$CSIZE ='';
						}

						if($M_FORM == 'ON') {
							$paxcombo .= "\n<form style=\"display: inline\" action=\"$CHECK_PAGE->path/$CHECK_PAGE->name\">\n<select name=\"PAXCOMBO_$M_MENUNAME$nrcombo\"$CSIZE onchange=\"document.location=this.form.PAXCOMBO_$M_MENUNAME$nrcombo.options[this.form.PAXCOMBO_$M_MENUNAME$nrcombo.selectedIndex].value\"$multicombo>";
						} else {
							$paxcombo .= "\n<select name=\"PAXCOMBO_$M_MENUNAME$nrcombo\"$CSIZE$multicombo>";
						}

						if(strlen($M_FIRSTENTRY) > 0) {
							$paxcombo .= "\n<option value=\"$CHECK_PAGE->path/$CHECK_PAGE->name\">$M_FIRSTENTRY</option>";
						}

						// richtiges menu gefunden, select füllen
						$MenuZeilen = count($PAXMENUDEF[$MenuCount]);

						for($ZeilenCount = 0; $ZeilenCount < $MenuZeilen; $ZeilenCount++) {
							$MenuZellen = count($PAXMENUDEF[$MenuCount][$ZeilenCount]);
							for($ZellenCount = 0; $ZellenCount < $MenuZellen; $ZellenCount++) {
								$PAX_Class = &$PAXMENUDEF[$MenuCount][$ZeilenCount][$ZellenCount]['CLASS'];
								$PAX_Value = &$PAXMENUDEF[$MenuCount][$ZeilenCount][$ZellenCount]['VALUE'];
								$PAX_Field = &$PAXMENUDEF[$MenuCount][$ZeilenCount][$ZellenCount]['FIELD'];

								if($M_DISPAY_FIELD == $PAX_Field) {
									$dispay = $PAX_Value;
									$MDISPAY = TRUE;
								}

								if($M_OPT_VALUES == $PAX_Field) {
									$value = str_replace(" ", "_", $PAX_Value);
									$MVALUE = TRUE;
								}

								if($M_VAL_SELECTED == ($ZeilenCount + 1)) {
									$selected = ' selected';
								} else {
									$selected = '';
								}

								if($MDISPAY AND $MVALUE) {
									$paxcombo .= "\n".'<option value="'.$value.'"'.$selected.'>'.$dispay.'</option>';
									$MDISPAY = FALSE;
									$MVALUE = FALSE;
								}
							}
						}
					}
				}

				if($M_FORM == 'ON') {
					$paxcombo .= "\n".'</select></form>';
				} else {
					$paxcombo .= "\n".'</select>';
				}

				$contentfile[$contcount] = str_replace('<!-- PAXCOMBO '.$Pax_MenuPara.' -->', $paxcombo, $contentfile[$contcount]);
				unset($multicombo);
				unset($paxcombo);
				unset($selected);
				unset($dispay);
				unset($value);
				$nrcombo++;
			}
		}
	}
	return $contentfile;
} // END func _setPAXCOMBO

/*********************************************
  grab menudefinitions from CLASS MENU
*********************************************/
function _getPAXMENU() {
	global $PAXMENUDEF, $MENU;

	$CountMenues = count($MENU->menuname);
	for($mainmenu = 0; $mainmenu < $CountMenues; $mainmenu++) {
		$PAXMENUDEF[$mainmenu] = array('MENU' => $MENU->menuname[$mainmenu]);
		$FieldCount = count($MENU->menuFieldValues[$mainmenu]);

		for($sub1menu = 0; $sub1menu < $FieldCount; $sub1menu++) {
			$ValueCount = count($MENU->menuFieldValues[$mainmenu][$sub1menu]);
			for($k = 0; $k < $ValueCount; $k++) {
				$PAXMENUFIELD = trim($MENU->menuFieldNames[$mainmenu][$k]);
				$PAXMENUVALUE = trim($MENU->menuFieldValues[$mainmenu][$sub1menu][$k]);

				if($PAXMENUFIELD == 'CLASS') {
					$PAXMENUCLASS = trim($MENU->menuFieldValues[$mainmenu][$sub1menu][$k]);
				}

				// menüs in arry zuweisen
				if(strlen($PAXMENUFIELD) > 0) {
					$PAXMENUDEF[$mainmenu][$sub1menu][$k] = array('CLASS' => $PAXMENUCLASS, 'FIELD' => $PAXMENUFIELD, 'VALUE' => $PAXMENUVALUE);
				}
			}
		}
	}
} // end func PAX_loadmenu

/*********************************************
  scan Files for PAXTAGS and change it
*********************************************/
function _setPAXMENU($contentfile) {
	global $PAXMENUDEF;

	$countcontentfile = count($contentfile);

	for($contcount = 0; $contcount < $countcontentfile; $contcount++) {
		if(preg_match_all("/<!-- PAXMENU ([a-zA-Z0-9_,\.\s]+) -->/", $contentfile[$contcount], $matches)) {
			foreach($matches[1] as $Pax_MenuPara) {
				list($M_DESC, $M_VAL) = explode(",", $Pax_MenuPara);

				$M_DESC = trim($M_DESC);
				$M_VAL = trim($M_VAL);

				$PAX_Tag = '<!-- PAXMENU '.$Pax_MenuPara.' -->';
				$tagFound=false;

				$Menues = count($PAXMENUDEF);
				for($MenuCount = 0; $MenuCount < $Menues; $MenuCount++) {
					$MenuZeilen = count($PAXMENUDEF[$MenuCount]);
					for($ZeilenCount = 0; $ZeilenCount < $MenuZeilen; $ZeilenCount++) {
						$MenuZellen = count($PAXMENUDEF[$MenuCount][$ZeilenCount]);
						for($ZellenCount = 0; $ZellenCount < $MenuZellen; $ZellenCount++) {
							$PAX_Class = $PAXMENUDEF[$MenuCount][$ZeilenCount][$ZellenCount]['CLASS'];
							$PAX_Value = $PAXMENUDEF[$MenuCount][$ZeilenCount][$ZellenCount]['VALUE'];
							$PAX_Desc = $PAXMENUDEF[$MenuCount][$ZeilenCount][$ZellenCount]['FIELD'];

							if(($M_VAL == $PAX_Class) && ($M_DESC == $PAX_Desc)) {
								$contentfile[$contcount] = str_replace('<!-- PAXMENU '.$Pax_MenuPara.' -->', $PAX_Value, $contentfile[$contcount]);
								$tagFound=true;
								break;
							}
						}
						if ($tagFound) break;
					}
					if ($tagFound) break;
				}
			}
		}
	}
	return $contentfile;
}

/*********************************************
  load further files
*********************************************/
function _setPAXINC($template) {
	global $DEFAULTS;
	$countcontentfile = count($template);
	for($_p = 0; $_p < $countcontentfile; $_p++) {
		if(preg_match_all("/<!-- PAXINC ([^\>\$]+) -->/", $template[$_p], $matches)) {
			foreach($matches[1] as $paxinc) {
				if(strtoupper(substr($paxinc, 0, 7 )) <> 'HTTP://')
				{
					$paxinc =  str_replace( '$home', $DEFAULTS->PROJECT_HOME, $paxinc);
					$paxinc =  str_replace( '$plugindir', $DEFAULTS->PLUGINDIR, $paxinc);
					$localfile = $DEFAULTS->DOCUMENT_ROOT.$paxinc;
					$temp =  _PAXfile($localfile);
				}
				else
					$temp =  _PAXfile($paxinc);

				$temp1 = join(" ", $temp);
				$template[$_p]  =  str_replace( '<!-- PAXINC '.$paxinc.' -->', $temp1,  $template[$_p] );
			}
		}
	}
	return $template;
}

/*********************************************
  load files and highlight source
*********************************************/
function _setPAXHIGHLIGHT($template) {
	global $DEFAULTS;
	$countcontentfile = count($template);
	for($_p = 0; $_p < $countcontentfile; $_p++) {
		if(preg_match_all("/<!-- PAXHIGHLIGHT ([^\>\$]+) -->/", $template[$_p], $matches)) {
			foreach($matches[1] as $paxinc) {
				if(strtoupper(substr($paxinc, 0, 7)) <> 'HTTP://') {
					$paxinc = str_replace('$home', $DEFAULTS->PROJECT_HOME, $paxinc);
					$paxinc = str_replace('$plugindir', $DEFAULTS->PLUGINDIR, $paxinc);
					$localfile = $DEFAULTS->DOCUMENT_ROOT.$paxinc;
					$temp = _PAXfile($localfile);
				} else {
					$temp = _PAXfile($paxinc);
				}

				$temp1 = join(" ", $temp);
				$temp1 = str_replace("&gt;", ">", $temp1);
				$temp1 = str_replace("&lt;", "<", $temp1);
				$temp1 = str_replace("&amp;", "&", $temp1);
				$temp1 = str_replace('$', '\$', $temp1);
				$temp1 = str_replace('\n', '\\\\n', $temp1);
				$temp1 = str_replace('\r', '\\\\r', $temp1);
				$temp1 = str_replace('\t', '\\\\t', $temp1);
				$temp1 = str_replace("<br />", "\r\n", $temp1);
				$temp1 = str_replace("<br>", "\r\n", $temp1);
				$temp1 = stripslashes($temp1);
				ob_start();
				highlight_string($temp1);
				$buffer = ob_get_contents();
				ob_end_clean();
				$temp1 = $buffer;

				$temp1 = '<blockquote><pre>PHP:<hr>'.$temp1.'<hr></pre></blockquote>';
				$template[$_p] = str_replace('<!-- PAXHIGHLIGHT '.$paxinc.' -->', $temp1, $template[$_p]);
			}
		}
	}
	return $template;
}

/*********************************************
  load simpleRTFfiles and convert to html
*********************************************/
function _setPAXRTF($template) {
	global
		$DEFAULTS,
		$color,
		$size,
		$bullets;
	$countcontentfile = count($template);
	for($_p = 0; $_p < $countcontentfile; $_p++) {
		if(preg_match_all("/<!-- PAXRTF ([^\>\$]+) -->/", $template[$_p], $matches)) {
			foreach($matches[1] as $paxinc) {
				if(strtoupper(substr($paxinc, 0, 7)) <> 'HTTP://') {
					$paxinc = str_replace('$home', $DEFAULTS->PROJECT_HOME, $paxinc);
					$paxinc = str_replace('$plugindir', $DEFAULTS->PLUGINDIR, $paxinc);
					$localfile = $DEFAULTS->DOCUMENT_ROOT.$paxinc;
					$rtfile = _PAXfile($localfile);
				} else {
					$rtfile = _PAXfile($paxinc);
				}

				$color = "000000";
				$size = 1;
				$bullets = 0;

				$fileLength = count($rtfile);
				for($i = 1; $i < $fileLength; $i++) {
					// following functions to parse a rtf-file are written by Jason Stechschulte
					// If the line contains "\colortbl" then we found the color table.
					// We'll have to split it up into each individual red, green, and blue
					// Convert it to hex and then put the red, green, and blue back together.
					// Then store each into an array called fcolor.
					if(ereg("^\{\\\colortbl", $rtfile[$i])) {
						// Split the line by the backslash.
						$colors = explode("\\", $rtfile[$i]);
						$numOfColors = count($colors);
						for($k = 2; $k < $numOfColors; $k++) {
							// Find out how many different colors there are.
							if(ereg("[0-9]+", $colors[$k], $matches)) {
								$match[] = $matches[0];
							}
						}

						// For each color, convert it to hex.
						$numOfColors = count($match);
						for($k = 0; $k < $numOfColors; $k += 3) {
							$red = dechex($match[$k]);
							$red = $match[$k] < 16 ? "0$red" : $red;
							$green = dechex($match[$k + 1]);
							$green = $match[$k +1] < 16 ? "0$green" : $green;
							$blue = dechex($match[$k + 2]);
							$blue = $match[$k + 2] < 16 ? "0$blue" : $blue;
							$fcolor[] = "$red$green$blue";
						}
						$numOfColors = count($fcolor);
					}
					// Or else, we parse the line, pulling off words and tags.
					else {
						$token = "";
						$start = 0;
						$lineLength = strlen($rtfile[$i]);
						for($k = 0; $k < $lineLength; $k++) {
							if($rtfile[$i][$start] == "\\" && $rtfile[$i][$start + 1] != "\\") {
								// We are now dealing with a tag.
								$token .= $rtfile[$i][$k];
								if($rtfile[$i][$k] == " ") {
									$newFile[$i] .= ProcessTags($token, $i);
									$token = "";
									$start = $k + 1;
								} elseif($rtfile[$i][$k] == "\n") {
									$newFile[$i] .= ProcessTags($token, $i);
									$token = "";
								}
							} elseif($rtfile[$i][$start] == "{") {
								// We are now dealing with a tag.
								$token .= $rtfile[$i][$k];
								if($rtfile[$i][$k] == "}") {
									$newFile[$i] .= ProcessTags($token, $i);
									$token = "";
									$start = $k + 1;
								}
							} else {
								// We are now dealing with a word.
								if($rtfile[$i][$k] == "\\" && $rtfile[$i][$k + 1] != "\\" && $rtfile[$i][$k - 1] != "\\") {
									$newFile[$i] .= ProcessWord($token);
									$token = $rtfile[$i][$k];
									$start = $k;
								} else {
									$token .= $rtfile[$i][$k];
								}
							}
						}
					}
				}
				$temp1 = join(" ", $newFile);
				$template[$_p]  =  str_replace( '<!-- PAXRTF '.$paxinc.' -->', $temp1,  $template[$_p] );
			}
		}
	}
	return $template;
}

/*********************************************
  lastmod for files
*********************************************/
function _setPAXLASTMOD($contentfile) {
		global
			$CHECK_PAGE,
			$DEFAULTS;

		$countcontentfile = count($contentfile);

		for($contcount = 0; $contcount < $countcontentfile; $contcount++) {
			if(preg_match_all("/<!-- PAXLASTMOD ([a-zA-Z0-9_,:~#=\|\-\+\.\s\S\x]+) -->/", $contentfile[$contcount], $matches)) {
				foreach($matches[1] as $Pax_MenuPara) {
					$lastmod = date($Pax_MenuPara, filemtime($DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'/'.$CHECK_PAGE->name));
					$PAX_Tag = '<!-- PAXLASTMOD '.$Pax_MenuPara.' -->';
					$contentfile[$contcount] = str_replace('<!-- PAXLASTMOD '.$Pax_MenuPara.' -->', $lastmod, $contentfile[$contcount]);
				}
			}
		}
		return $contentfile;
}


/*********************************************
  replace all PAXTAGS with their values
*********************************************/
function setPAXTAGS(&$template) {
	_getPAXMENU();
	$template = _setPAXINC($template);
	$template = _setPAXRTF($template);
	$template = _setPAXHIGHLIGHT($template);
	$template = _setPAXMENU($template);
	$template = _setPAXCOMBO($template);
	$template = _setPAXCOMBOALL($template);
	$template = _setPAXLASTMOD($template);
	$template =  _change_phpCMS_TAGS($template);
	return $template;
}

/*********************************************
  scan php within files
*********************************************/
function _PAXparser($template) {
	global $PAXCODE_ARRAY;

	$countbrain = count($PAXCODE_ARRAY);
	$counttemplate = count($template);
	if(!isset($newlinecount)) {
		$newlinecount = 0;
	}
	$FIELDNAME = 'PAXPHP';
	$BEGINCODELINE = false;

	// search line by line for phpcode
	for($templines = 0; $templines < $counttemplate; $templines++) {
		$_actualline = $template[$templines];

		// search beginn of delimiter in line
		$foundbegin = strpos($_actualline, "<!-- $FIELDNAME ");
		if(is_int($foundbegin)) {
			// backup all before begin in this line
			if(isset($newlines[$newlinecount])) {
				$newlines[$newlinecount] .= substr($_actualline, 0, $foundbegin);
			}
			else {
				$newlines[$newlinecount] = substr($_actualline, 0, $foundbegin);
			}
			$newlinecount++;

			$blockbeginlen = strlen("<!-- $FIELDNAME ");
			$restline = substr($_actualline, $foundbegin + $blockbeginlen);
			// looking for blockname - a space is delimiter between blockname and code
			$begincode = strpos($restline, ' ');
			$BEGINCODELINE = true;

			// code in same line or not?
			if(is_int($begincode)) {
				// ok, code beginning in same line with a space before
				$blockname = substr($_actualline, $foundbegin + $blockbeginlen, $begincode);
				$_insameline = true;
			} else {
				// code beginning in next line, so line - \n = blockname
				$blockname = substr($_actualline, $foundbegin + $blockbeginlen, -1);
				$_insameline = false;
			}

			$blockendname = $FIELDNAME.' '.$blockname.' -->';
			if(!$_insameline) {
				$templines++;
			}
		} // end if

		if(isset($blockendname) && $blockendname != '') {
			$foundend = strpos($_actualline, $blockendname);
		}
		// is there an end-delimiter in this line?
		if(isset($foundend) && is_int($foundend)) {
			$blockendlen = strlen($blockendname);
			if($_insameline) {
				$_code = substr($_actualline, $foundbegin + $blockbeginlen + strlen($blockname), -$blockendlen - 1);
			}
			$PAXCODE_ARRAY[$countbrain] = array('BLOCK' => $blockname, 'CODE' => $_code);
			$countbrain++;
			// ...and add a seperator to template
			$newlines[$newlinecount - 1] .= '[##PAXCODE_'.$blockname.'##]';
			unset($_code);
			$BEGINCODELINE = false;
		} elseif($BEGINCODELINE == true) {
			//erase space just at the beginning of the line
			if(isset($_code)) {
				$_code .= eregi_replace("^[[:space:]]+", "", $template[$templines]);
			}
			else {
				$_code = eregi_replace("^[[:space:]]+", "", $template[$templines]);
			}
		} else {
			if(isset($newlines[$newlinecount])) {
				$newlines[$newlinecount] .= $_actualline;
			}
			else {
				$newlines[$newlinecount] = $_actualline;
			}
			$newlinecount++;
		} // end else
	} // end for
	return $newlines;
}

/**********************************************************
  change TAGS from phpCMS within PAX for PAXTAGS & PAXPHP
**********************************************************/
function _change_phpCMS_TAGS($template) {
	global
		$PAGE,
		$DEFAULTS;

	$CHANGETAGS = new helper;
	// Make sure the helper knows about the tags to use
	$CHANGETAGS->checkTags($PAGE->tagfile->tags);

	$tcount = count($template);
	for($zulu = 0; $zulu < $tcount; $zulu++) {
		$_actual = $template[$zulu];
		if(strstr(strtoupper($_actual), '<HTML>')) {
			continue;
		}

		if(!strstr($_actual, $DEFAULTS->START_FIELD)) {
			if(strlen(trim($_actual)) > 0 OR isset($DEFAULTS->pre)) {
				$_actual = $CHANGETAGS->ChangeTags($_actual, $PAGE->tagfile->tags);
				$template[$zulu] = $_actual;
			}
		}
	}
	return $template;
}

/*********************************************
  preload file
*********************************************/
function _PAXfile($fname) {
	$text = @file($fname);
	$ctext = count($text);
	// replace win or mac LF or/and CR to *nix
	for($i = 0; $i < $ctext; $i++) {
		$actualline = $text[$i];
		// for win
		$actualline = str_replace("\r\n", "\n", $actualline);
		// for mac
		$actualline = str_replace("\r", "\n", $actualline);
		$text[$i] = $actualline;
	}

	unset($ctext);
	unset($i);
	unset($filename);
	return $text;
}

/*********************************************
  main
*********************************************/
function PAXmain($linesarray) {
	global $HTTP_COOKIE_VARS;

	// editmode active? no PAX
	if(isset($HTTP_COOKIE_VARS["phpCMSedit"])) {
		return $linesarray;
	}

	$linesarray = _PAXparser($linesarray);
	return $linesarray;
}

?>
