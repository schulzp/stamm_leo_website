<?php
/* $Id: class.parser_template_phpcms.php,v 1.7.2.37 2006/06/18 18:07:32 ignatius0815 Exp $ */
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
   |    Beate Paland (beate76)
   |    Henning Poerschke (hpoe)
   |    Markus Richert (e157m369)
   |    Thilo Wagner (ignatius0815)
   /    Martin Jahn (mjahn)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

/**
 * @package phpcms
 * @subpackage parser
 */

/**
 * This is one of the main class of phpCMS. It puts together the information that were
 * collected in class.realtime_phpcms.php. These are the menutree, the menutemplate, the
 * contentfields and the tagfile. It also calls the pluginhandler and includes TOC and
 * SEARCHRESULTS if neccessary.
 *
 * @package phpcms
 * @subpackage parser
 */
class template {
	var $name = '';
	var $path = '';

	function template($filename) {
		global $PAGE, $DEFAULTS;

		$this->content = new File($filename);
		$this->path = dirname($filename);
		$this->name = basename($filename);
		if(!file_exists($this->path.'/'.$this->name) AND !stristr($this->path, 'HTTP://')) {
			ExitError(10);
		}
	}

	function LineReplacer($menu, $needle, $value) {
		$temp = stristr($menu, $needle);
		if($temp) {
			$PartOne = substr($menu, 0, strpos($menu, $needle));
			$PartTwo = substr($menu, strpos($menu, $needle) + strlen($needle));
			if(stristr($PartTwo, $needle)) {
				$PartTwo = $this->LineReplacer($PartTwo, $needle, $value);
			}
			$menu = $PartOne.$value.$PartTwo;
		}
		return $menu;
	}

	function ReplaceEntry($Menu, $FieldName, $FieldValue) {
		global $DEFAULTS;

		$MenuCount = count($Menu);
		$needle = $DEFAULTS->START_FIELD.$FieldName.$DEFAULTS->STOP_FIELD;
		for($i = 0; $i < $MenuCount; $i++) {
			$Menu[$i] = $this->LineReplacer($Menu[$i], $needle, $FieldValue);
		}
		return $Menu;
	}

	function MakeSearchResult($Template) {
		global $DEFAULTS;

		$DEFAULTS->SEARCHTERM_MIN_LENGTH = 3;

		include(PHPCMS_INCLUDEPATH.'/class.parser_search_phpcms.php');
		$SEARCH_RESULTS = new SEARCH_RESULTS;
		$SEARCH_RESULTS->parse_search_results($Template);

		if(isset($SEARCH_RESULTS->ReturnArray[0])) {
			return $SEARCH_RESULTS->ReturnArray;
		} else {
			return false;
		}
	}

	function MakeToc($TocClass, $TT, $TSuffix, $ShowActives) {
		global $MENU, $HELPER, $DEFAULTS;

		$ArrayCount = 0;
		$MenuCount = count($MENU->menuname);
		$PageClass = $this->GetPageClass();
		unset($Toc);

		for($MenuName = 0; $MenuName < $MenuCount; $MenuName++) {
			$InsertedMenu = FALSE;
			if($TT != '' AND $TSuffix != '') {
				$TocTemplate = $TT.$TSuffix;
			} elseif($TT != '') {
				$TocTemplate = $TT;
			} elseif($TSuffix != '') {
				$TocTemplate = $MENU->menuname[$MenuName].$TSuffix;
			} else {
				$TocTemplate = $MENU->menuname[$MenuName];
			}

			unset($ItemCount);
			$ItemCount = count($MENU->menuFieldValues[$MenuName]);
			for($MenuItem = 0; $MenuItem < $ItemCount; $MenuItem++) {
				$ActiveClass = substr($MENU->menuFieldValues[$MenuName][$MenuItem][0], 0, strlen($TocClass));
				if($ActiveClass != $TocClass) {
					continue 1;
				}
				$ExtensionActiveClass = substr($MENU->menuFieldValues[$MenuName][$MenuItem][0], strlen($TocClass) + 1);
				if(strstr($ExtensionActiveClass, '.')) {
					continue 1;
				}
				// menuitem above the actual found
				if(trim($ExtensionActiveClass) == '') {
					continue 1;
				}
				if($ShowActives == '1') {
					$aKlasse = $MENU->menuFieldValues[$MenuName][$MenuItem][0];
					$pKlasse = substr($PageClass, 0, strlen($aKlasse));
				}
				unset($TempToc);
				if(isset($pKlasse) AND isset($aKlasse) AND $pKlasse == $aKlasse AND $ShowActives == '1') {
					$tempset = trim('TOC.'.$TocTemplate.'.AKTIV');
					$TempToc = $MENU->TEMPLATE->content->{$tempset};
					if($TempToc == '') {
						$tempset = trim('TOC.'.$TocTemplate.'.ACTIVE');
					}
					if($pKlasse == $PageClass AND isset($MENU->TEMPLATE->content->{'TOC.'.$TocTemplate.'.SELF'})) {
						$tempset = trim('TOC.'.$TocTemplate.'.SELF');
					}
				} else {
					$tempset = trim('TOC.'.$TocTemplate.'.NORMAL');
				}
				$TempToc = $MENU->TEMPLATE->content->{$tempset};

				if(!isset($MENU->TEMPLATE->content->{$tempset})) {
					ExitError(14,"$tempset");
					exit;
				}
				if($InsertedMenu == FALSE) {
					$InsertedMenu = TRUE;
				}

				$FieldCount = count($MENU->menuFieldValues[$MenuName][$MenuItem]);
				for($Field = 0; $Field < $FieldCount; $Field++) {
					$TempToc = $this->ReplaceEntry($TempToc, $MENU->menuFieldNames[$MenuName][$Field], $MENU->menuFieldValues[$MenuName][$MenuItem][$Field]);
				}
				$TempTocCount = count($TempToc);
				for($n = 0; $n < $TempTocCount; $n++) {
					$Toc[$ArrayCount] = $TempToc[$n];
					$ArrayCount++;
					$tempset = trim('TOC.'.$TocTemplate.'.BETWEEN');
					if ($MenuItem >= 0 && isset($MENU->TEMPLATE->content->{$tempset}) && $MenuItem < $ItemCount-1) {
						$Toc[$ArrayCount] = $MENU->TEMPLATE->content->{$tempset}[$n];
						$ArrayCount++;
					}

				}
				$CheckTocClass = $MENU->menuFieldValues[$MenuName][$MenuItem][0];
				unset($AddToc);
				$AddToc = $this->MakeToc($CheckTocClass, $TT, $TSuffix, $ShowActives);
				if($AddToc) {
					unset($CountAddToc);
					$CountAddToc = count($AddToc);
					for($CountAdd = 0; $CountAdd < $CountAddToc; $CountAdd++) {
						$Toc[$ArrayCount] = $AddToc[$CountAdd];
						$ArrayCount++;
					}
				}
			}

			$ReturnCount = 0;
			if($InsertedMenu == TRUE) {
				$tempset = trim('TOC.'.$TocTemplate.'.PRE');
				if(isset($MENU->TEMPLATE->content->{$tempset})) {
					$count = count($MENU->TEMPLATE->content->{$tempset});
					for($m = 0; $m < $count; $m++) {
						$ReturnArray[$ReturnCount] = $MENU->TEMPLATE->content->{$tempset}[$m];
						$ReturnCount++;
					}
				}
				for($m = 0; $m < $ArrayCount; $m++) {
					$ReturnArray[$ReturnCount] = $Toc[$m];
					$ReturnCount++;


				}



				$tempset = trim('TOC.'.$TocTemplate.'.PAST');
				if(isset($MENU->TEMPLATE->content->{$tempset})) {
					$count = count($MENU->TEMPLATE->content->{$tempset});
					for($m = 0; $m < $count; $m++) {
						$ReturnArray[$ReturnCount] = $MENU->TEMPLATE->content->{$tempset}[$m];
						$ReturnCount++;
					}
				}
			}
		}
		if(isset($ReturnArray)) {
			return $ReturnArray;
		} else {
			return false;
		}
	}

	function ExtractPath($FIELD) {
		global $PHP, $DEFAULTS;
		// extracting Path from "Field" over the "FILE" attribute

		$TempPath = $PHP->ExtractValue($FIELD, 'FILE');
		if(substr($TempPath, 0, 1) <> '/' AND strtoupper(substr($TempPath, 0, 7)) <> 'HTTP://') {
			if(strtoupper(substr($TempPath, 0, 5)) == '$HOME') {
				$Path = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->PROJECT_HOME.substr($TempPath, 5);
			} elseif(strtoupper(substr($TempPath, 0, 10)) == '$PLUGINDIR') {
				$Path = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->PLUGINDIR.substr($TempPath, 10);
			} else {
				$Path = $this->path.'/'.$TempPath;
			}
		} elseif(substr($TempPath, 0, 1) == '/') {
			$Path = $DEFAULTS->DOCUMENT_ROOT.$TempPath;
		} else {
			$Path = $TempPath;
		}
		return $Path;
	}

	function ReturnEmty($PartOne, $PartTwo) {
		global $DEFAULTS;

		if(!stristr($PartTwo, $DEFAULTS->START_FIELD)) {
			$ReturnArray[0] = $PartOne.$PartTwo;
		} else {
			$AddArray = $this->DoField($PartTwo);
			$CountAdd = count($AddArray);
			for($k = 0; $k < $CountAdd; $k++) {
				$ReturnArray[$k] = $AddArray[$k];
			}
			$ReturnArray[0] = $PartOne.$ReturnArray[0];
		}
		$ReturnArray = $DEFAULTS->TEMPLATE->PreParse($ReturnArray);
		return $ReturnArray;
	}

	function ReturnFull($PartOne, $buffer, $PartTwo) {
		global $DEFAULTS;

		$Count = count($buffer);
		for($i = 0; $i < $Count; $i++) {
			if(isset($buffer[$i])) {
				$ReturnArray[$i] = $buffer[$i];
			} else {
				$ReturnArray[$i] = '';
			}
		}
		if(isset($ReturnArray[0])) {
			$ReturnArray[0] = $PartOne.$ReturnArray[0];
		} else {
			$ReturnArray[0] = $PartOne;
		}
		if(!stristr($PartTwo, $DEFAULTS->START_FIELD)) {
			$ReturnArray[$Count - 1] = $ReturnArray[$Count - 1].$PartTwo;
		} else {
			$AddArray = $this->DoField($PartTwo);
			$CountAdd = count($AddArray);
			if(isset($ReturnArray[$i])) {
				$ReturnArray[$i] = $ReturnArray[$i].$AddArray[0];
			} else {
				$ReturnArray[$i] = $AddArray[0];
			}
			for($k = 1; $k < $CountAdd; $k++) {
				$i++;
				$ReturnArray[$i] = $AddArray[$k];
			}
		}
		$ReturnArray = $DEFAULTS->TEMPLATE->PreParse($ReturnArray);
		return $ReturnArray;
	}

	function GetPageClass() {
		global $DEFAULTS, $PAGE, $MENU, $CHECK_PAGE;

		if(isset($PAGE->content->MENU)) {
			return trim($PAGE->content->MENU[0]);
		}

		$CountMainMenu = count($MENU->menuname);
		for($i = 0; $i < $CountMainMenu; $i++) {
			$FieldCount = count($MENU->menuFieldValues[$i]);
			for($j = 0; $j < $FieldCount; $j++) {
				$ValueCount = count($MENU->menuFieldValues[$i][$j]);
				# set a remarker for finding page without parms
				$PagemenuFound = FALSE;
				for($k = 0; $k < $ValueCount; $k++) {
					if(strtoupper($MENU->menuFieldNames[$i][$k]) == 'CLASS') {
						$ActualClass = $MENU->menuFieldValues[$i][$j][$k];
					}
					if(strtoupper($MENU->menuFieldNames[$i][$k]) == 'LINK') {
						$found = $MENU->menuFieldValues[$i][$j][$k];
						if(isset($CHECK_PAGE->parms) AND strtoupper($found) == strtoupper($CHECK_PAGE->path.'/'.$CHECK_PAGE->name.$CHECK_PAGE->parms)) {
							$PAGE->content->MENU[0] = $ActualClass;
							return $ActualClass;
						}

						if(strstr($found, '?')) {
							$found = substr($found, 0, strpos($found, '?'));
						}
						# look, if we have found a page without parms
						if(strtoupper($found) == strtoupper($CHECK_PAGE->path.'/'.$CHECK_PAGE->name) OR
						   strtoupper($found.$DEFAULTS->PAGE_DEFAULTNAME.$DEFAULTS->PAGE_EXTENSION) == strtoupper($CHECK_PAGE->path.'/'.$CHECK_PAGE->name)) {
							$PAGE->content->MENU[0] = $ActualClass;
							$PagemenuFound = TRUE;
						}

					}
				}
				if ($PagemenuFound == TRUE){
					return $PAGE->content->MENU[0];
				}
			}
		}
		$PAGE->content->MENU[0] = '00';
		return '00';
	}

	function DoField($Line) {
		global
			$DEFAULTS,
			$PAGE,
			$MENU,
			$HELPER,
			$PHP;

		unset($ReturnBuffer);
		unset($buffer);

		list($PartOne, $FIELD, $PartTwo) = $HELPER->SplitLine($Line, $DEFAULTS->START_FIELD, $DEFAULTS->STOP_FIELD);

		if(substr (strtoupper ($FIELD), 0, 7) == 'PLUGIN ' && (substr($FIELD, strpos($FIELD, 'PLUGIN') - 1, 1) != '_')) {
			// found plugin extracting Name
			// get path to plugin
			$PluginPath = $this->ExtractPath($FIELD);
			// get type of plugin - static or dynamic
			$PluginType = $PHP->ExtractValue($FIELD, 'TYPE');

			$buffer = $PHP->MakePlugin(
				$PluginPath,
				$PluginType,
				$PAGE->content,
				$DEFAULTS->CACHE_STATE,
				$DEFAULTS->CACHE_CLIENT,
				$DEFAULTS->PROXY_CACHE_TIME,
				$PAGE->tagfile->tags,
				$MENU,
				$plugindir
			);

			// check if buffer is filled
			if(!isset($buffer)) {
				$ReturnBuffer = $this->ReturnEmty($PartOne, $PartTwo);
				if (count($ReturnBuffer) == 1 && trim($ReturnBuffer[0]) == '') $ReturnBuffer[0] = '';
				return $ReturnBuffer;
			} else {
				$ReturnBuffer = $this->ReturnFull($PartOne, $buffer, $PartTwo);
				if (count($ReturnBuffer) == 1 && trim($ReturnBuffer[0]) == '') $ReturnBuffer[0] = '';
				return $ReturnBuffer;
			}
		}

		if(strtoupper(substr($FIELD, 0, 6)) == 'SCRIPT') {
			if(!isset($PAGE->content->{$FIELD}[0]) OR strlen(trim($PAGE->content->{$FIELD}[0])) == 0) {
				$ReturnBuffer = $this->ReturnEmty($PartOne, $PartTwo);
				if (count($ReturnBuffer) == 1 && trim($ReturnBuffer[0]) == '') $ReturnBuffer[0] = '';
				return $ReturnBuffer;
			} else {
				$DEFAULTS->SCRIPT = TRUE;
				$buffer[0] = '[*'.$FIELD.'*]';
				$ReturnBuffer = $this->ReturnFull($PartOne, $buffer, $PartTwo);
				if (count($ReturnBuffer) == 1 && trim($ReturnBuffer[0]) == '') $ReturnBuffer[0] = '';
				return $ReturnBuffer;
			}
		}

		if(strtoupper(substr($FIELD, 0, 14)) == 'EDITLINE FILE=') {
			if(!isset($DEFAULTS->EDIT) OR $DEFAULTS->EDIT != 'on') {
				$ReturnBuffer = $this->ReturnEmty($PartOne, $PartTwo);
				return $ReturnBuffer;
			} else {
				if(isset($aTemplate)) {
					unset($aTemplate);
				}
				$aTemplate = new template($this->ExtractPath($FIELD));
				$buffer = $aTemplate->content->lines;
				$ReturnBuffer = $this->ReturnFull($PartOne, $buffer, $PartTwo);
				return $ReturnBuffer;
			}
		}

		if(strtoupper(substr($FIELD, 0, 14)) == 'TEMPLATE FILE=') {
			if(isset($aTemplate)) {
				unset($aTemplate);
			}
			$aTemplate = new template($this->ExtractPath($FIELD));
			$buffer = $aTemplate->content->lines;
			$ReturnBuffer = $this->ReturnFull($PartOne, $buffer, $PartTwo);
			return $ReturnBuffer;
		}

		if(strtoupper(substr($FIELD, 0, 10)) == 'MENU NAME=') {
			$temp = trim(substr($FIELD, strpos($FIELD, 'NAME="') + 6));
			$temp = trim(substr($temp, 0, strpos($temp, '"')));
			$MenuFound = -1;
			$MenuCount = count($MENU->menuname);
			$MenuArrayCounter = 0;
			$set = 0;
			unset($MenuArray);

			$PageClass = $this->GetPageClass();

			for($j = 0; $j < $MenuCount; $j++) {
				if($MENU->menuname[$j] != $temp) {
					if(!stristr($temp, 'CURRENT')) {
						continue;
					}
				}
				$MenuFound = $j;
				$ActualName = $MENU->menuname[$j];

				$ValueCount = count($MENU->menuFieldValues[$MenuFound][0]);
				$EntryCount = count($MENU->menuFieldValues[$MenuFound]);

				// BOF (mjahn)
				// NEW_FEATURES: parameter IFNOTEMPTY in MENU-field in template-files
				// is there an advise to display the menu-templates .PRE and .PAST only
				// if there are menu-entries in this menu?
				if(stristr($FIELD, 'IFNOTEMPTY')) {
					if ($EntryCount == 0) {
						// there are no entries in this menu, so why should we parse a template for this?
						// let's continue with the next menu
						continue;
					}
				}
				// EOF (mjahn)

				if(stristr($FIELD, ' MENTEMP="')) {
					$MenuTemplate = trim(substr($FIELD, strpos($FIELD, 'MENTEMP="') + 9));
					$MenuTemplate = trim(substr($MenuTemplate, 0, strpos($MenuTemplate, '"')));
				} else {
					$MenuTemplate = $ActualName;
				}

				if(stristr($FIELD, ' PARENTCLASS="')) {
					$NewParentClass = trim(substr($FIELD, strpos($FIELD, 'PARENTCLASS="') + 13));
					$NewParentClass = trim(substr($NewParentClass, 0, strpos($NewParentClass, '"')));
				} else unset($NewParentClass);

				$ActualClass = $MENU->menuKlasse[$MenuFound];

				// now parse the found menu
				if (isset($NewParentClass)) {
					$PageMasterClass = substr($NewParentClass, 0, strlen($ActualClass));
				} else {
					$PageMasterClass = substr($PageClass, 0, strlen($ActualClass));
				}

				if($ActualClass != $PageMasterClass AND !stristr($temp, 'CURRENT')) {
					continue;
				}

				$SubPageClass = substr($PageClass, 0, strrpos($PageClass, '.'));
				if($ActualClass != $SubPageClass AND stristr($temp, 'CURRENT')) {
					continue;
				}

				$set = 1;
				$tempset = trim($MenuTemplate.'.PRE');
				if(isset($MENU->TEMPLATE->content->{$tempset})) {
					$count = count($MENU->TEMPLATE->content->{$tempset});
					for($m = 0; $m < $count; $m++) {
						$MenuArray[$MenuArrayCounter] = $MENU->TEMPLATE->content->{$tempset}[$m];
						$MenuArrayCounter++;
					}
				}

				$activeMenuNumber = -1;
				for($k = 0; $k < $EntryCount; $k++) {
					$aKlasse = $MENU->menuFieldValues[$MenuFound][$k][0];
					$pKlasse = substr($PageClass, 0, strlen($aKlasse));

					if ($pKlasse == $aKlasse) {
						$activeMenuNumber = $k;
					}
					else if ($k < ($EntryCount-1)) {
						$aKlasseNext = $MENU->menuFieldValues[$MenuFound][$k+1][0];
						if($pKlasse == $aKlasseNext) {
							$activeMenuNumber = $k+1;
						}
					}

					if($activeMenuNumber == $k) {
						if(isset($MENU->TEMPLATE->content->{$MenuTemplate.'.AKTIV'})) {
							$TempMenu = $MENU->TEMPLATE->content->{$MenuTemplate.'.AKTIV'};
						}
						else {
							$TempMenu = $MENU->TEMPLATE->content->{$MenuTemplate.'.ACTIVE'};
						}
						if($pKlasse == $PageClass AND isset($MENU->TEMPLATE->content->{$MenuTemplate.'.SELF'})) {
							$TempMenu = $MENU->TEMPLATE->content->{$MenuTemplate.'.SELF'};
						}
					} else {
						// PREACTIVE/POSTACTIVE patch by wulmer
						if (
								($activeMenuNumber != -1) &&
								($k == $activeMenuNumber - 1) &&
								(isset($MENU->TEMPLATE->content->{$MenuTemplate.'.PREACTIVE'}))
						) {
							$TempMenu = $MENU->TEMPLATE->content->{$MenuTemplate.'.PREACTIVE'};
						} else if (
							($activeMenuNumber != -1) &&
							($k == $activeMenuNumber + 1) &&
							(isset($MENU->TEMPLATE->content->{$MenuTemplate.'.POSTACTIVE'}))
						) {
							$TempMenu = $MENU->TEMPLATE->content->{$MenuTemplate.'.POSTACTIVE'};
						} else {
							$TempMenu = $MENU->TEMPLATE->content->{$MenuTemplate.'.NORMAL'};
						}
						// End of PREACTIVE/POSTACTIVE patch by wulmer
					}
					for($l = 0; $l < $ValueCount; $l++) {
						$TempMenu = $this->ReplaceEntry($TempMenu, $MENU->menuFieldNames[$MenuFound][$l], $MENU->menuFieldValues[$MenuFound][$k][$l]);
					}

					$TempMenuCount = count($TempMenu);
					for($m = 0; $m < $TempMenuCount; $m++) {
						$MenuArray[$MenuArrayCounter] = $TempMenu[$m];
						$MenuArrayCounter++;

					}

					$tempset = trim($MenuTemplate.'.BETWEEN');
					if ($k>=0 && isset($MENU->TEMPLATE->content->{$tempset}) && $TempMenuCount >1 && $k<$EntryCount-1) {
						// BOF (mjahn) #1228075
						// we have to put all array-fields of the template into the output, not only the first
					    $TempMenu = $MENU->TEMPLATE->content->{$tempset};
						$TempMenuCount = count($TempMenu);
						for($m = 0; $m < $TempMenuCount; $m++) {
							$MenuArray[$MenuArrayCounter] = $TempMenu[$m];
							$MenuArrayCounter++;
						}
						// EOF (mjahn)
					}
				}
				$tempset = trim($MenuTemplate.'.PAST');
				if(!isset($MENU->TEMPLATE->content->{$tempset})) {
					continue;
				}
				$count = count($MENU->TEMPLATE->content->{$tempset});
				for($m = 0; $m < $count; $m++) {
					$MenuArray[$MenuArrayCounter] = $MENU->TEMPLATE->content->{$tempset}[$m];
					$MenuArrayCounter++;
				}
			}
			if($MenuFound == -1 AND $DEFAULTS->DEBUG == 'on') {
				ExitError(18, $FIELD);
			}

			if(!isset($MenuArray)) {
				$ReturnBuffer = $this->ReturnEmty($PartOne, $PartTwo);
				return $ReturnBuffer;
			} else {
				$ReturnBuffer = $this->ReturnFull($PartOne, $MenuArray, $PartTwo);
				return $ReturnBuffer;
			}
		}

		if(strtoupper(substr($FIELD, 0, 11)) == 'TOC CLASS="') {
			$temp == true;
			if (isset ($DEFAULTS->CONTENT_TOC)) {
				if (strtoupper ($DEFAULTS->CONTENT_TOC) == 'OFF') {
					$temp = true;
				} elseif (!isset ($DEFAULTS->CONTENT_TOC_FIELDNAME)) {
					$temp = false;
				} elseif (isset ($PAGE->content->{$DEFAULTS->CONTENT_TOC_FIELDNAME})) {
					$temp = true;
				} else {
					$temp = false;
				}
			}

			if (!$temp) {
				return '';
			}

			if(stristr($FIELD, 'TOCTEMP="')) {
				$TocTemplate = trim(substr($FIELD, strpos($FIELD, 'TOCTEMP="') + 9));
				$TocTemplate = trim(substr($TocTemplate, 0, strpos($TocTemplate, '"')));
			} else {
				$TocTemplate = '';
			}

			if(stristr($FIELD, 'TOCSUFFIX="')) {
				$TocSuffix = trim(substr($FIELD, strpos($FIELD, 'TOCSUFFIX="') + 11));
				$TocSuffix = trim(substr($TocSuffix, 0, strpos($TocSuffix, '"')));
			} else {
				$TocSuffix = '';
			}

			if(stristr($FIELD, 'SHOWACTIVES')) {
				$ShowActives = '1';
			} else {
				$ShowActives = '';
			}

			// which is the mother-MenuClass (including the named)?
			if(stristr($FIELD, 'CLASS="')) {
				$TocClass = substr($FIELD, strpos($FIELD, 'CLASS="') + 7);
				$TocClass = substr($TocClass, 0, strpos($TocClass, '"'));
			} else {
				ExitError(18, $FIELD);
				exit;
			}

			$Toc = $this->MakeToc($TocClass, $TocTemplate, $TocSuffix, $ShowActives);
			if(!isset($Toc)) {
				$ReturnBuffer = $this->ReturnEmty($PartOne, $PartTwo);
				return $ReturnBuffer;
			} else {
				$buffer = $Toc;
				$ReturnBuffer = $this->ReturnFull($PartOne, $buffer, $PartTwo);
				return $ReturnBuffer;
			}
		}

		if(strtoupper(substr($FIELD, 0, 25)) == 'SEARCHRESULT SEARCHTEMP="') {

			$temp == true;
			if (isset ($DEFAULTS->CONTENT_SEARCH)) {
				if (strtoupper ($DEFAULTS->CONTENT_SEARCH) == 'OFF') {
					$temp = true;
				} elseif (!isset ($DEFAULTS->CONTENT_SEARCH_FIELDNAME)) {
					$temp = false;
				} elseif (isset ($PAGE->content->{$DEFAULTS->CONTENT_SEARCH_FIELDNAME})) {
					$temp = true;
				} else {
					$temp = false;
				}
			}

			if (!$temp) {
				return '';
			}

			if(stristr($FIELD, 'SEARCHTEMP="')) {
				$SearchTemplate = trim(substr($FIELD, strpos($FIELD, 'SEARCHTEMP="') + 12));
				$SearchTemplate = trim(substr($SearchTemplate, 0, strpos($SearchTemplate, '"')));
			} else {
				$SearchTemplate = '';
			}

			$SearchResult = $this->MakeSearchResult($SearchTemplate);

			if(!isset($SearchResult)) {
				$ReturnBuffer = $this->ReturnEmty($PartOne, $PartTwo);
				return $ReturnBuffer;
			} else {
				$buffer = $SearchResult;
				$ReturnBuffer = $this->ReturnFull($PartOne, $buffer, $PartTwo);
				return $ReturnBuffer;
			}
		}

		// catch PageField
		$EditType = '';
		if(stristr($FIELD, 'EDITTYPE="')) {
			$EditType = trim(substr($FIELD, strpos($FIELD, 'EDITTYPE="') + 10));
			$EditType = trim(substr($EditType, 0, strpos($EditType, '"')));
			$Rows = trim(substr($FIELD, strpos($FIELD, 'ROWS="') + 6));
			$Rows = trim(substr($Rows, 0, strpos($Rows, '"')));
			$Cols = trim(substr($FIELD, strpos($FIELD, 'COLS="') + 6));
			$Cols = trim(substr($Cols, 0, strpos($Cols, '"')));
			$Size = trim(substr($FIELD, strpos($FIELD, 'SIZE="') + 6));
			$Size = trim(substr($Size, 0, strpos($Size, '"')));
			// EDITTYPE="SELECT" patch by DOC
			$SelectOptions = trim(substr($FIELD, strpos($FIELD, 'OPTIONS="') + 9));
			$SelectOptions = trim(substr($SelectOptions, 0, strpos($SelectOptions, '"')));
			// End of EDITTYPE="SELECT" patch by DOC
			$FIELD = trim(substr($FIELD, 0, strpos($FIELD, 'EDITTYPE="')));
		}

		if(isset($DEFAULTS->EDIT) AND $DEFAULTS->EDIT == 'on' AND $DEFAULTS->DOEDIT == 'on' AND strlen($EditType) > 1 AND isset($PAGE->content->$FIELD)) {
			if($EditType == 'TEXT') {
				if(trim($Size) == '') {
					$Size = 20;
				}
				// BUG: fixed XHTML-errors
				// we should support templates for the editor-fields
				// so the user could also use his own template for each type of editfield
				// perhaps we should add some fields to the menu-template-file or should
				// create a new template-file for the online-editor
				// at least this functionality could be included by using the WYSIWYG-interface
				$PartOne = $PartOne.'<input type="text" name="'.$FIELD.'" size="'.$Size.'" value="';
				$PartTwo = '" />'.$PartTwo;
			}
			if($EditType == 'TEXTAREA') {
				if(trim($Rows) == '') {
					$Rows=20;
				}
				if(trim($Cols) == '') {
					$Cols=80;
				}
				$PartOne = $PartOne.'<textarea name="'.$FIELD.'" rows="'.$Rows.'" cols="'.$Cols.'">';
				$PartTwo = '</textarea>'.$PartTwo;
			}

			// EDITTYPE="SELECT" patch by DOC
			if($EditType == 'SELECT') {
				if(!empty($SelectOptions)) {
					$SelectOptionsArray = split (";",$SelectOptions);
					$PartOne = $PartOne.'<select name="'.$FIELD.'" >';
					$buffer = htmlentities(implode('', $PAGE->content->$FIELD));
					foreach($SelectOptionsArray AS $value ) {
						if ($buffer == $value) {
							$PartOne = $PartOne. '<option selected="selected" value="'.$value.'">'.$value.'</option>';
						} else {
							$PartOne = $PartOne. '<option value="'.$value.'">'.$value.'</option>';
						}
					}
					$PartTwo = '</select>'.$PartTwo;
				}
			}
			//End of EDITTYPE="SELECT" patch by DOC

			/* prepared to use a wysiwyg online editor */
			if($EditType == 'WYSIWYG' && file_exists(PHPCMS_INCLUDEPATH.'/class.edit_wysiwyg_phpcms.php')) {
				$buffer = stripslashes(implode('', $PAGE->content->$FIELD));
				ob_start();
				include(PHPCMS_INCLUDEPATH.'/class.edit_wysiwyg_phpcms.php');
				$wysiwyg_buffer = ob_get_contents();
				ob_end_clean();
				$PartTwo = implode('', $this->ReturnEmty('', $PartTwo));
				$ReturnBuffer[0] = $PartOne.$wysiwyg_buffer.$PartTwo;
				unset($wysiwyg_buffer);
			} else {
				$PartTwo = implode('', $this->ReturnEmty('', $PartTwo));
				// BUG: UTF-8 is not supported by online editor
				// the htmlentites-call must be replaced by a function that supports UTF-8
				// it also could be removed to use UTF-8 with the online-editor
				$buffer = $HELPER->html_entities(implode('', $PAGE->content->$FIELD));
				// $ReturnBuffer[0] = $PartOne.$buffer.$PartTwo;
				// EDITTYPE="SELECT" patch by DOC
				if( $EditType == 'SELECT' ) {
					$ReturnBuffer[0] = $PartOne.$PartTwo;
				} else {
					$ReturnBuffer[0] = $PartOne.$buffer.$PartTwo;
				}
				// End of EDITTYPE="SELECT" patch by DOC
			}
			return $ReturnBuffer;
		} else { // not in Edit-Mode
			if(!isset($PAGE->content->$FIELD)) {
				$ReturnBuffer = $this->ReturnEmty($PartOne, $PartTwo);
				return $ReturnBuffer;
			} else {
				$buffer = $PAGE->content->$FIELD;
				// replace the phpCMS-commands
				$buffer = $HELPER->compute_phpcms_commands ($buffer);
				$ReturnBuffer = $this->ReturnFull($PartOne, $buffer, $PartTwo);
				return $ReturnBuffer;
			}
		}
	}

	function PreParse($lines) {
		global $DEFAULTS, $PAGE, $MENU, $HELPER;

		$ArrayCount	 = count($lines);
		$i = 0;
		$nl = 0;

		while($i < $ArrayCount) {
			// if no field -> continue
			$temp = strstr($lines[$i], $DEFAULTS->START_FIELD);
			if(!$temp) {
				$NewLines[$nl] = $HELPER->ChangeTags($lines[$i], $PAGE->tagfile->tags);
				$i++;
				$nl++;
			} else {
				$Replace = $this->DoField($lines[$i]);
				$CountRep = count($Replace);
				for($k = 0; $k < $CountRep; $k++) {
					if(isset($Replace[$k])) {

						$NewLines[$nl] = $Replace[$k];
						$nl++;
					}
				}
				$i++;
			}
		}

		if(isset($NewLines)) {
			return $NewLines;
		} else {
			return;
		}
	}
}

?>
