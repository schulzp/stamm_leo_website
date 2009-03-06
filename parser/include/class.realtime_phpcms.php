<?php
/* $Id: class.realtime_phpcms.php,v 1.7.2.41 2006/06/18 18:07:32 ignatius0815 Exp $ */
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
   |    Tobias D�nz (tobiasd)
   |    Martin Jahn (mjahn)
   |    Henning Poerschke (hpoe)
   |    Markus Richert (e157m369)
   |    Wolfgang Ulmer (wulmer)
   |    Thilo Wagner (ignatius0815)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

function SplitPaxTag(&$PartOne, &$PartTwo, &$ScriptField) {
	$PartOne = substr($PartTwo, 0, strpos($PartTwo, '[##PAXCODE'));
	$PartTwo = substr($PartTwo, strpos($PartTwo, '[##PAXCODE'));

	$ScriptField = substr($PartTwo, 3, strpos($PartTwo, '##]') - 3);

	$PartTwo = substr($PartTwo, 6 + strlen($ScriptField));
	$ScriptField = trim($ScriptField);
}

function SplitScriptTag(&$PartOne, &$PartTwo, &$Skript) {
	global $DEFAULTS, $PAGE, $CHECK_PAGE;

	$PartOne = substr($PartTwo, 0, strpos($PartTwo, '[*SCRIPT'));
	$PartTwo = substr($PartTwo, strpos($PartTwo, '[*SCRIPT'));

	$ScriptField = substr($PartTwo, 2, strpos($PartTwo, '*]') - 2);

	$PartTwo = substr($PartTwo, 4 + strlen($ScriptField));
	$ScriptField = trim($ScriptField);

	$temp = trim($PAGE->content->{$ScriptField}[0]);
	if(substr($temp, 0, 1) <> '/' AND strtoupper(substr($temp, 0, 7)) <> 'HTTP://') {
		if(strtoupper(substr($temp, 0, 5)) == '$HOME') {
			$Skript = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->PROJECT_HOME.substr($temp, 5);
		} elseif(strtoupper(substr($temp, 0, 10)) == '$PLUGINDIR') {
			$Skript = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->PLUGINDIR.substr($temp, 10);
		} else {
			$Skript = $DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'/'.$temp;
		}
	} elseif(substr($temp, 0, 1) == '/') {
		$Skript = $DEFAULTS->DOCUMENT_ROOT.$temp;
	} else {
		$Skript = $temp;
	}
}

function MakeGlobalVars($Skript) {
  global $PHPCMS;
	if(strpos($Skript, "?")) {
		$mde_incstr = substr($Skript, strpos($Skript, "?") + 1);
		$mde_incstr_arr = explode("&", $mde_incstr);
		$mde_counter = 0;
		while($mde_counter < count($mde_incstr_arr)) {
			$mde_incstr_val = explode("=", $mde_incstr_arr[$mde_counter]);
			$GLOBALS[$mde_incstr_val[0]] = $mde_incstr_val[1];
			$GLOBALS['REQUEST'][$mde_incstr_val[0]] = $mde_incstr_val[1];
			$GLOBALS['_GET_POST'][$mde_incstr_val[0]] = $mde_incstr_val[1];
			$_GET[$mde_incstr_val[0]] = $mde_incstr_val[1];
			$_REQUEST[$mde_incstr_val[0]] = $mde_incstr_val[1];
			$PHPCMS->_REQUEST_URI[$mde_incstr_val[0]] = $mde_incstr_val[1];
			$PHPCMS->_request_uri[$mde_incstr_val[0]] = $mde_incstr_val[1];
			$PHPCMS->_QUERY_STRING[$mde_incstr_val[0]] = $mde_incstr_val[1];
			$PHPCMS->_query_string[$mde_incstr_val[0]] = $mde_incstr_val[1];
			$mde_counter++;
		}
		$Skript = substr($Skript, 0, strpos($Skript, "?"));
	}
	return $Skript;
}

function mslashes($String) {
	$String = str_replace("\\", "\\\\", $String);
	return str_replace("'", "\'", $String);
}

//BOF #636228
function fwrite_wrapper ($file, $line) {
  global $dontWriteToCache;
  global $CHECK_PAGE;
  if (!$dontWriteToCache) {
    if (!fwrite($file,$line)) {
      $dontWriteToCache = true;
      fclose($file);
      @unlink($CHECK_PAGE->CACHE_PAGE);
    }
  }
}
// EOF #636228

$dontWriteToCache = false;
$HELPER = new helper();
$PAGE = new Page;
if(isset($DEFAULTS->PageType) AND $DEFAULTS->PageType == 'html') {
	$fsize = filesize($DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'/'.$CHECK_PAGE->name);
	$fd = fopen($DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'/'.$CHECK_PAGE->name, "rb");
	$contents = fread($fd, $fsize);
	$contents = trim($contents);
	$fsize = strlen($contents);
	fclose($fd);
	Header("Content-type: text/html");
	if($DEFAULTS->CACHE_CLIENT != 'on') {
		$PHP->NoCache();
	} else {
		Header("Cache-Control: public");
		Header("Expires: ".gmdate("D, d M Y H:i:s", time() + $DEFAULTS->PROXY_CACHE_TIME).' GMT');
		Header("Last-Modified: ".gmdate("D, d M Y H:i:s", filemtime($DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'/'.$CHECK_PAGE->name)).' GMT');
	}
	Header("Content-Length: ".$fsize);
	echo $contents;
	exit;
}

$MENU = new menu;
$MENU->TEMPLATE = new menutemplate;

// set parsing stop and needed time
$PHPCMS->TIMER['STOP'] = $PHPCMS->get_time(microtime());
$PHPCMS->TIMER['NEEDED'] = $PHPCMS->get_time_passed($PHPCMS->TIMER['START'], $PHPCMS->TIMER['STOP']);

//---------------------------------------------------------------------------
if(in_array('debug', array_keys($PHPCMS->_query_string), TRUE) AND $DEFAULTS->DEBUG == 'on') {
	include(PHPCMS_INCLUDEPATH.'/class.debug_phpcms.php');
	$DEBUG = new DEBUG;
	$debug_mode = $DEBUG->debug_mode;
}
//---------------------------------------------------------------------------
if(!isset($debug_mode) OR $debug_mode == '' OR $debug_mode == 'PAGE') {
	$DEFAULTS->TEMPLATE = new template($DEFAULTS->TEMPLATE);
	$DEFAULTS->TEMPLATE->content->lines = $DEFAULTS->TEMPLATE->PreParse($DEFAULTS->TEMPLATE->content->lines);
	$phpCMS_Ausgabe = count($DEFAULTS->TEMPLATE->content->lines);

	if($DEFAULTS->PAXTAGS == 'on') {
		$DEFAULTS->TEMPLATE->content->lines = setPAXTAGS($DEFAULTS->TEMPLATE->content->lines);
	}

	// phpMail2Crypt
	include(PHPCMS_INCLUDEPATH.'/class.mail2crypt_phpcms.php');
	$Mail2Crypt = new Mail2Crypt();
	$DEFAULTS->TEMPLATE->content->lines = $Mail2Crypt->crypt_mailto($DEFAULTS->TEMPLATE->content->lines);

	global $PAXCODE_ARRAY;

	// write static cache with gzip-compression
	if(!isset($DEFAULTS->SCRIPT) AND ($DEFAULTS->PAX == 'off')) {
		// write content into cachefile with gzip
		$GZIP->gwrite($DEFAULTS->TEMPLATE->content->lines);
		// submit the cachefile to the browser
		$GZIP->gzipPassthru($DEFAULTS->TEMPLATE->content->lines);
		exit;
	}

	// GZIP lt. extension ok, cache off
	$DEFAULTS->CACHE_CLIENT = "off";
	$_SERVER["PHP_SELF"] = $GLOBALS["PHP_SELF"] = $CHECK_PAGE->path.'/'.$CHECK_PAGE->name;
	$phpCMS_i = 0;
	$phpCMS_set = 0;
	$phpCMS_Buffer_Counter = 0;

	while($phpCMS_i < $phpCMS_Ausgabe) {
		if($phpCMS_set == 0) {
			$phpCMS_Line = $DEFAULTS->TEMPLATE->content->lines [$phpCMS_i];
		}
		if(!stristr($phpCMS_Line, '[*SCRIPT') AND !stristr($phpCMS_Line, '[##PAXCODE')) {
			$phpCMS_Buffer[$phpCMS_Buffer_Counter] = $phpCMS_Line;
			$phpCMS_set = 0;
			$phpCMS_i++;
		} else {
			$phpCMS_set = 1;
			$phpCMS_PartOne = '';
			$phpCMS_Skript = '';
			$PaxField = '';

			if(stristr($phpCMS_Line, '[*SCRIPT')) {
				SplitScriptTag($phpCMS_PartOne, $phpCMS_Line, $phpCMS_Skript);
				$phpCMS_Skript = MakeGlobalVars($phpCMS_Skript);
				$phpCMS_Buffer[$phpCMS_Buffer_Counter] = $phpCMS_PartOne;
				chdir($DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path);
				if (isset($DEFAULTS->FIX_PHP_OB_BUG) AND $DEFAULTS->FIX_PHP_OB_BUG == 'on') {
					// starting aditional output buffer to fix a bug in php 4.2.3
					// which causes the output buffer to fail when sessions are
					// set within a script or plugin
					ob_start();
				}
				ob_start();
				if(file_exists($phpCMS_Skript)) {
					include($phpCMS_Skript);
				} else {
					ExitError(20, $phpCMS_Skript);
				}
				unset($phpCMS_TempBuffer);
				$phpCMS_TempBuffer = ob_get_contents();
				ob_end_clean();
				if (isset($DEFAULTS->FIX_PHP_OB_BUG) AND $DEFAULTS->FIX_PHP_OB_BUG == 'on') {
					$phpCMS_TempBuffer = ob_get_contents() . $phpCMS_TempBuffer;
					ob_end_clean();
				}
				$phpCMS_Buffer_Counter++;
				$phpCMS_Buffer[$phpCMS_Buffer_Counter] = $phpCMS_TempBuffer;
			}

			if(stristr($phpCMS_Line, '[##PAXCODE')) {
				SplitPaxTag($phpCMS_PartOne, $phpCMS_Line, $PaxField);
				$phpCMS_Buffer[$phpCMS_Buffer_Counter] = $phpCMS_PartOne;

				chdir($DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path);

				if (isset($DEFAULTS->FIX_PHP_OB_BUG) AND $DEFAULTS->FIX_PHP_OB_BUG == 'on') {
					// starting aditional output buffer to fix a bug in php 4.2.3
					// which causes the output buffer to fail when sessions are
					// set within a script or plugin
					ob_start();
				}

				ob_start();

				for($phpCMS_j = 0; $phpCMS_j < count($PAXCODE_ARRAY); $phpCMS_j++) {
					if($PaxField == 'PAXCODE_'.trim($PAXCODE_ARRAY[$phpCMS_j]['BLOCK'])) {
						eval($PAXCODE_ARRAY[$phpCMS_j]['CODE']);
					}
				}

				unset($phpCMS_TempBuffer);
				unset($PaxField);
				$phpCMS_TempBuffer = ob_get_contents();
				ob_end_clean();

				if (isset($DEFAULTS->FIX_PHP_OB_BUG) AND $DEFAULTS->FIX_PHP_OB_BUG == 'on') {
					$phpCMS_TempBuffer = ob_get_contents() . $phpCMS_TempBuffer;
					ob_end_clean();
				}

				$phpCMS_Buffer_Counter++;
				$phpCMS_Buffer[$phpCMS_Buffer_Counter] = $phpCMS_TempBuffer;
			}
		}
		$phpCMS_Buffer_Counter++;
	}
	$GZIP->gzipPassthru($phpCMS_Buffer);

	// GZIP lt. extension ok, cache on -> dynamic cache
	if((!isset($PHPCMS->_query_string['template']) OR $PHPCMS->_query_string['template'] == '') AND $DEFAULTS->CACHE_STATE == 'on') {
		$CHECK_PAGE->CACHE_PAGE = str_replace('.gz', $DEFAULTS->DYN_EXTENSION, $CHECK_PAGE->CACHE_PAGE);
		$CHECK_PAGE->CACHE_PAGE = str_replace($DEFAULTS->PAGE_EXTENSION, $DEFAULTS->DYN_EXTENSION, $CHECK_PAGE->CACHE_PAGE);
		$phpCMS_fp = fopen($CHECK_PAGE->CACHE_PAGE, "wb");
		$phpCMS_i = 0;
		$phpCMS_set = 0;
		$phpCMS_Buffer_Counter = 0;
		fwrite_wrapper($phpCMS_fp, '<?php'."\n");
		// write the real name of the file into the cache-file
		fwrite_wrapper($phpCMS_fp,'//'.$CHECK_PAGE->CACHE_TAG);
		fwrite_wrapper($phpCMS_fp, '$_SERVER["PHP_SELF"] = $GLOBALS["PHP_SELF"] = $CHECK_PAGE->path."/".$CHECK_PAGE->name;'."\n");
		fwrite_wrapper($phpCMS_fp, '$DEFAULTS->CACHE_CLIENT = "off";'."\n");

		while($phpCMS_i < $phpCMS_Ausgabe) {
			if($phpCMS_set == 0) {
				$phpCMS_Line = $DEFAULTS->TEMPLATE->content->lines [$phpCMS_i];
			}
			if(!stristr($phpCMS_Line, '[*SCRIPT') AND !stristr($phpCMS_Line, '[##PAXCODE')) {
				$phpCMS_Line = '$phpCMSBuffer['.$phpCMS_Buffer_Counter.']=\''.mslashes(rtrim($phpCMS_Line)).'\'."\\n";'."\n";
				fwrite_wrapper($phpCMS_fp, $phpCMS_Line);
				$phpCMS_set = 0;
				$phpCMS_i++;
			} else {
				$phpCMS_set = 1;
				$phpCMS_PartOne = '';
				$phpCMS_Skript = '';

				if(stristr($phpCMS_Line, '[*SCRIPT')) {
					$splitscript_ok = false;
					SplitScriptTag($phpCMS_PartOne, $phpCMS_Line, $phpCMS_Skript);
					$splitscript_ok = TRUE;
				}
				if(stristr($phpCMS_Line, '[##PAXCODE')) {
					$splitpax_ok = false;
					SplitPaxTag($phpCMS_PartOne, $phpCMS_Line, $PaxField);
					$splitpax_ok = TRUE;
				}

				$phpCMS_PartOne = '$phpCMSBuffer['.$phpCMS_Buffer_Counter.']=\''.mslashes(rtrim($phpCMS_PartOne)).'\';'."\n";

				fwrite_wrapper($phpCMS_fp, $phpCMS_PartOne);

				if($splitscript_ok == TRUE) {
					$splitscript_ok = FALSE;

					if(strpos($phpCMS_Skript, "?")) {
						unset($mde_incstr_arr);
						$mde_incstr = substr($phpCMS_Skript, strpos($phpCMS_Skript, "?") + 1);
						$mde_incstr_arr = explode("&", $mde_incstr);
						$mde_counter = 0;
						while($mde_counter < count($mde_incstr_arr)) {
							$mde_incstr_val = explode("=", $mde_incstr_arr[$mde_counter]);
							if (isset($mde_incstr_val[0]) && trim($mde_incstr_val[0])) {
   							    fwrite_wrapper($phpCMS_fp, '$_GET["'.$mde_incstr_val[0].'"] = $_REQUEST ["'.$mde_incstr_val[0].'"] = $GLOBALS ["'.$mde_incstr_val[0].'"] = "'.$mde_incstr_val[1].'";'."\n");
   							    fwrite_wrapper($phpCMS_fp, '$PHPCMS->_request_uri ["'.$mde_incstr_val[0].'"] = $PHPCMS->_REQUEST_URI ["'.$mde_incstr_val[0].'"] = $PHPCMS->_query_string ["'.$mde_incstr_val[0].'"] = $PHPCMS->_QUERY_STRING ["'.$mde_incstr_val[0].'"] = "'.$mde_incstr_val[1].'";'."\n");
							}
							$mde_counter++;
						}
						$phpCMS_Skript = substr($phpCMS_Skript, 0, strpos($phpCMS_Skript, "?"));
					}
					fwrite_wrapper($phpCMS_fp, 'chdir(\''.$DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'\');'."\n");
					fwrite_wrapper($phpCMS_fp, 'ob_start();'."\n");
					if (isset($DEFAULTS->FIX_PHP_OB_BUG) AND $DEFAULTS->FIX_PHP_OB_BUG == 'on') {
						// starting aditional output buffer to fix a bug in php 4.2.3
						// which causes the output buffer to fail when sessions are
						// set within a script or plugin
						fwrite_wrapper($phpCMS_fp, 'ob_start();'."\n");
					}
					fwrite_wrapper($phpCMS_fp, 'if(file_exists("'.$phpCMS_Skript.'")) {'."\n");
					fwrite_wrapper($phpCMS_fp, '	include("'.$phpCMS_Skript.'");'."\n");
					fwrite_wrapper($phpCMS_fp, '} else {'."\n");
					fwrite_wrapper($phpCMS_fp, '	ExitError(20, "'.$phpCMS_Skript.'");'."\n");
					fwrite_wrapper($phpCMS_fp, '}'."\n");
					fwrite_wrapper($phpCMS_fp, 'unset($phpCMS_TempBuffer);'."\n");
					fwrite_wrapper($phpCMS_fp, '$phpCMS_TempBuffer = ob_get_contents();'."\n");
					fwrite_wrapper($phpCMS_fp, 'ob_end_clean();'."\n");
					if (isset($DEFAULTS->FIX_PHP_OB_BUG) AND $DEFAULTS->FIX_PHP_OB_BUG == 'on') {
						fwrite_wrapper($phpCMS_fp, '$phpCMS_TempBuffer = ob_get_contents() . $phpCMS_TempBuffer;'."\n");
						fwrite_wrapper($phpCMS_fp, 'ob_end_clean();'."\n");
					}
					$phpCMS_Buffer_Counter++;
					fwrite_wrapper($phpCMS_fp, '$phpCMSBuffer['.$phpCMS_Buffer_Counter.'] = $phpCMS_TempBuffer;'."\n");
				}

				if($splitpax_ok == TRUE) {
					$splitpax_ok = FALSE;

					fwrite_wrapper($phpCMS_fp, 'chdir(\''.$DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'\');'."\n");
					if (isset($DEFAULTS->FIX_PHP_OB_BUG) AND $DEFAULTS->FIX_PHP_OB_BUG == 'on') {
						// starting aditional output buffer to fix a bug in php 4.2.3
						// which causes the output buffer to fail when sessions are
						// set within a script or plugin
						fwrite_wrapper($phpCMS_fp, 'ob_start();'."\n");
					}
					fwrite_wrapper($phpCMS_fp, 'ob_start();'."\n\n");

					for($phpCMS_j = 0; $phpCMS_j < count($PAXCODE_ARRAY); $phpCMS_j++) {
						if($PaxField == 'PAXCODE_'.trim($PAXCODE_ARRAY[$phpCMS_j]['BLOCK'])) {
							fwrite_wrapper($phpCMS_fp, $PAXCODE_ARRAY[$phpCMS_j]["CODE"]);
						}
					}
					unset($PaxField);

					fwrite_wrapper($phpCMS_fp, 'unset($phpCMS_TempBuffer);'."\n");
					fwrite_wrapper($phpCMS_fp, '$phpCMS_TempBuffer = ob_get_contents();'."\n");
					fwrite_wrapper($phpCMS_fp, 'ob_end_clean();'."\n");
					if (isset($DEFAULTS->FIX_PHP_OB_BUG) AND $DEFAULTS->FIX_PHP_OB_BUG == 'on') {
						fwrite_wrapper($phpCMS_fp, '$phpCMS_TempBuffer = ob_get_contents() . $phpCMS_TempBuffer;'."\n");
						fwrite_wrapper($phpCMS_fp, 'ob_end_clean();'."\n");
					}
					$phpCMS_Buffer_Counter++;
					fwrite_wrapper($phpCMS_fp, '$phpCMSBuffer['.$phpCMS_Buffer_Counter.'] = $phpCMS_TempBuffer;'."\n");
				}
			}
			$phpCMS_Buffer_Counter++;
		}
		fwrite_wrapper($phpCMS_fp, '$GZIP->gzipPassthru($phpCMSBuffer);?>'); //<?
		if ($phpCMS_fp) fclose($phpCMS_fp);
	}
	exit;


	// GZIP lt. extension NICHT ok, cache off
	$phpCMS_i = 0;
	$phpCMS_set = 0;
	while($phpCMS_i < $phpCMS_Ausgabe) {
		if($phpCMS_set == 0) {
			$phpCMS_Line = $DEFAULTS->TEMPLATE->content->lines[$phpCMS_i];
		}
		if(!stristr($phpCMS_Line, '[*SCRIPT') AND !stristr($phpCMS_Line, '[##PAXCODE')) {
			echo $phpCMS_Line;
			$phpCMS_set = 0;
			$phpCMS_i++;
		} else {
			$phpCMS_set = 1;
			$phpCMS_PartOne = '';
			$phpCMS_Skript = '';

			if(stristr($phpCMS_Line, '[*SCRIPT')) {
				SplitScriptTag($phpCMS_PartOne, $phpCMS_Line, $phpCMS_Skript);
				$phpCMS_Skript = MakeGlobalVars($phpCMS_Skript);
				chdir($DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path);
				echo $phpCMS_PartOne;
				if(file_exists($phpCMS_Skript)) {
					include($phpCMS_Skript);
				} else {
					ExitError(20, $phpCMS_Skript);
				}
			}

			if(stristr($phpCMS_Line, '[##PAXCODE')) {
				SplitPaxTag($phpCMS_PartOne, $phpCMS_Line, $PaxField);
				chdir($DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path);
				echo $phpCMS_PartOne;

				for($phpCMS_j = 0; $phpCMS_j < count($PAXCODE_ARRAY); $phpCMS_j++) {
					if($PaxField == 'PAXCODE_'.trim($PAXCODE_ARRAY[$phpCMS_j]['BLOCK'])) {
						eval($PAXCODE_ARRAY[$phpCMS_j]['CODE']);
					}
				}
			}
		}
	}

	// GZIP lt. extension NICHT ok, cache on -> static cache without gzip
	if(!$PHPCMS->_query_string['template'] AND $DEFAULTS->CACHE_STATE == 'on') {
		$CHECK_PAGE->CACHE_PAGE = str_replace('.gz', $DEFAULTS->DYN_EXTENSION, $CHECK_PAGE->CACHE_PAGE);
		$CHECK_PAGE->CACHE_PAGE = str_replace($DEFAULTS->PAGE_EXTENSION, $DEFAULTS->DYN_EXTENSION, $CHECK_PAGE->CACHE_PAGE);
		$phpCMS_fp = fopen($CHECK_PAGE->CACHE_PAGE, "w");

		// write the real name of the file into the cache-file
		fwrite_wrapper($phpCMS_fp,$CHECK_PAGE->CACHE_TAG);

		$phpCMS_i = 0;
		$phpCMS_set = 0;
		while($phpCMS_i < $phpCMS_Ausgabe) {
			if($phpCMS_set == 0) {
				$phpCMS_Line = $DEFAULTS->TEMPLATE->content->lines[$phpCMS_i];
			}

			if(!stristr($phpCMS_Line, '[*SCRIPT') AND !stristr($phpCMS_Line, '[##PAXCODE')) {
				fwrite_wrapper($phpCMS_fp, $phpCMS_Line);
				$phpCMS_set = 0;
				$phpCMS_i++;
			} else {
				$phpCMS_set=1;
				$phpCMS_PartOne = '';
				$phpCMS_Skript = '';

				if(stristr($phpCMS_Line, '[*SCRIPT')) {
					$splitscript_ok = false;
					SplitScriptTag($phpCMS_PartOne, $phpCMS_Line, $phpCMS_Skript);
					$splitscript_ok = TRUE;
				}
				if(stristr($phpCMS_Line, '[##PAXCODE')) {
					SplitPaxTag($phpCMS_PartOne, $phpCMS_Line, $PaxField);
					$splitpax_ok = TRUE;
				}
				if($splitscript_ok == TRUE) {
					$splitscript_ok = FALSE;

					fwrite_wrapper($phpCMS_fp, $phpCMS_PartOne);
					fwrite_wrapper($phpCMS_fp, '<?php'."\n");

					if(strpos($phpCMS_Skript, "?")) {
						unset($mde_incstr_arr);
						$mde_incstr = substr($phpCMS_Skript, strpos($phpCMS_Skript, "?") + 1);
						$mde_incstr_arr = explode("&", $mde_incstr);
						$mde_counter = 0;

						while($mde_counter < count($mde_incstr_arr)) {
							$mde_incstr_val = explode("=", $mde_incstr_arr[$mde_counter]);
							fwrite_wrapper($phpCMS_fp, '$GLOBALS [\''.$mde_incstr_val[0].'\'] = \''.$mde_incstr_val[1].'\';'."\n");
							$mde_counter++;
						}

						$phpCMS_Skript = substr($phpCMS_Skript, 0, strpos($phpCMS_Skript, "?"));
					}
					fwrite_wrapper($phpCMS_fp, 'chdir(\''.$DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'\');'."\n");
					fwrite_wrapper($phpCMS_fp, 'if(file_exists("'.$phpCMS_Skript.'")) {'."\n");
					fwrite_wrapper($phpCMS_fp, '	include("'.$phpCMS_Skript.'");'."\n");
					fwrite_wrapper($phpCMS_fp, '} else {'."\n");
					fwrite_wrapper($phpCMS_fp, '	ExitError(20, "'.$phpCMS_Skript.'");'."\n");
					fwrite_wrapper($phpCMS_fp, '} ?>'."\n");
				}

				if($splitpax_ok == TRUE) {
					$splitpax_ok = FALSE;

					fwrite_wrapper($phpCMS_fp, 'chdir(\''.$DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'\');'."\n");

					for($phpCMS_j = 0; $phpCMS_j < count($PAXCODE_ARRAY); $phpCMS_j++) {
						if($PaxField == 'PAXCODE_'.trim($PAXCODE_ARRAY[$phpCMS_j]['BLOCK'])) {
							//fwrite_wrapper ($phpCMS_fp, '//'.$PAXCODE_ARRAY[$i]['BLOCK']." BEGIN\n");
							fwrite_wrapper($phpCMS_fp, $PAXCODE_ARRAY[$phpCMS_j]["CODE"]);
							//fwrite_wrapper ($phpCMS_fp, "\n//".$PAXCODE_ARRAY[$i]['BLOCK']." END\n");
						}
					}
					unset($PaxField);
				}
			}
  		if ($phpCMS_fp) fclose($phpCMS_fp);
		}
	}
}

?>
