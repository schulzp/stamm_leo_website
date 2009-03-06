<?php
/* $Id: class.lib_spider_phpcms.php,v 1.2.2.11 2006/06/18 18:07:31 ignatius0815 Exp $ */
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
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');


//############################################################
// Lib spider_phpcms
//
// Diese Library liefert folgende Funktionen:
//
// *) FileSpider ( File )
// *) WebSpider ( Adress )
//
// Im folgenden werden die Funktionen im einzelnen erklärt:
//
// FileSpider ( File )
// ========================
//
// Diese Funktion lädt eine Seite vom lokalen Verzeichnisbaum
// und parsed diese mit phpCMS. Zu spidernde Links werden
// im Globalen Array $DEFAULTS->ToSpider abgelegt.
// Die Funktion liefert eine Seite als String zurück.
// Die Variable $DEFAULTS->WriteToDir bezeichnet das
// Verzeichnis, in dem diese Seite abgelegt wird.
//
// WebSpider ( Adress )
// =========================
//
// Noch nicht implementiert.
//############################################################

class spider_phpcms {
	function spider_phpcms() {
	}

	function FindInArray($Needle, $Heystack) {
		global $PHP;

		if($PHP->Version(1) == 4) {
			if(!isset($Heystack[0])) {
				return false;
			}
			if(in_array(trim($Needle),$Heystack,TRUE)) {
				return true;
			} else {
				return false;
			}
		} else {
			if(!isset($Heystack[0])) {
				return false;
			}
			$HeystackCount = count($Heystack);
			for($i = 0; $i < $HeystackCount; $i++) {
				if(trim($Heystack[$i]) == trim($Needle)) {
					return true;
				}
			}
			return false;
		}
		return false;
	}

	function FindArrayString($Needle, $Heystack) {
		$NeedleCount = count($Needle);
		for($i = 0; $i < $NeedleCount; $i++) {
			if(stristr($Heystack, trim($Needle[$i]))) {
				return true;
			}
		}
		return false;
	}

	function RemoveArrayEntry($Needle, $Heystack) {
		$HeystackCount = count($Heystack);
		$j = 0;
		for($i = 0; $i < $HeystackCount; $i++) {
			if(trim($Heystack[$i]) == trim($Needle)) {
				continue;
			}
			$temp[$j] = $Heystack[$i];
			$j++;
		}
		if(isset($temp[0])) {
			return $temp;
		}
		$temp = array();
		return $temp;
	}

	function RemoveEntry($Entry) {
		global $DEFAULTS;

		$DEFAULTS->ToSpider = $this->RemoveArrayEntry($Entry,$DEFAULTS->ToSpider);
		if(!$this->FindInArray($Entry, $DEFAULTS->AllreadySpidered)) {
			$DEFAULTS->AllreadySpidered[count($DEFAULTS->AllreadySpidered)] = trim($Entry);
		}
	}

	function CopyFile($Source, $Target) {
		$TempDir = dirname($Target);

		if(!file_exists($TempDir)) {
			$this->CreateDir($TempDir);
		}
		if(file_exists($Source) AND !is_dir($Source)) {
			copy($Source, $Target);
		}
	}

	function CreateDir($TempDir) {
		global $DEFAULTS;

		if(!file_exists(dirname($TempDir))) {
			$this->CreateDir(dirname($TempDir));
		}

		$DEFAULTS->CreateDir[count($DEFAULTS->CreateDir)] = $TempDir;
		if(!file_exists($TempDir)) {
			mkdir($TempDir, 0777);
		}
		clearstatcache();
		return;
	}

	function ChangeURL($line) {
		global $CHECK_PAGE, $DEFAULTS, $PHP;

		if(isset($DEFAULTS->Prefix)) {
			$att = $DEFAULTS->Prefix;
		} else {
			$att = '';
		}
		$localpath = $CHECK_PAGE->path;
		$temppath = '';

		$tag = 'HREF';
		$PreUrl = $DEFAULTS->SCRIPT_PATH.$DEFAULTS->SCRIPT_NAME.'?file=';
		list($PartOne, $url, $PartTwo) = $this->GetUrl($line, $tag);
		if(stristr($PartTwo, $tag)) {
			$PartTwo = $this->ChangeURL($PartTwo);
		}
		if(strlen($url) == 0) {
			return $PartOne.$PartTwo;
		}
		if(stristr($url, $PreUrl)) {
			$url = trim(substr($url, strlen($PreUrl)));
		}
		while(stristr($url, '../')) {
			// remove '../'
			$url = substr($url, strpos($url, '../') + 3);
			// add the original path
			$temppath = $temppath.substr($localpath, strrpos($localpath, '/'));
			$localpath = substr($localpath, 0, strrpos($localpath, '/'));
		}
		if($this->FindArrayString($DEFAULTS->NoProto, $url)) {
			$this->RemoveEntry($url);
			return $PartOne.$url.$PartTwo;
		}
		if(stristr($url, '#')) {
			$Anchor = substr($url, strrpos($url, '#'));
			$url = substr($url, 0, strrpos($url, '#'));
		} else {   // Added Johannes Graubner
			$Anchor = '';
		}
		if(stristr($url, '.CSS')) {
			if(substr($url, 0, 1) != '/') {
				$url = $localpath.'/'.$url;
			}
			if(!$this->FindInArray($url, $DEFAULTS->SavedFiles)) {
				$this->CopyFile($DEFAULTS->DOCUMENT_ROOT.$url, $DEFAULTS->WriteToDir.$url);
				$DEFAULTS->SavedFiles[count($DEFAULTS->SavedFiles)] = $url;
			}
			$this->RemoveEntry($url);
		}
		if(strlen($url) < 1) {
			if ($Anchor == '') {
				$this->RemoveEntry($url);
				return $PartOne.$url.$PartTwo;
			} else {
				return $PartOne.$Anchor.$PartTwo;
			}

		}
		if(substr($url, 0, 1) != '/') {
			$url = $localpath.'/'.$url;
		}
		$url = str_replace('./', '', $url);

		if(!$this->FindInArray($url, $DEFAULTS->ToSpider) AND !$this->FindInArray($url, $DEFAULTS->AllreadySpidered)) {
			if(strlen(trim($url)) > 0) {
				$DEFAULTS->ToSpider[count($DEFAULTS->ToSpider)] = $url;
			}
		}

		if(stristr(strtoupper($url), 'TEMPLATE=')) {
			$CurrentAppendix = substr($url, strpos(strtoupper($url), 'TEMPLATE=') + 9);
			$CurrentAppendix = str_replace('/', '_', $CurrentAppendix);
			$CurrentAppendix = str_replace('.', '_', $CurrentAppendix);
			$CurrentAppendix = $CurrentAppendix.'_';

			$url = substr($url, 0, strrpos($url, '?'));

			$tempDir = dirname($url);
			$tempFile = basename($url);

			$tempFile = $CurrentAppendix.$tempFile;
			$url = $tempDir.'/'.$tempFile;
		}
		if(substr($url, 0, 1) == '\\') {
			$url = substr($url, 1);
		}
		if(!isset($Anchor)) {
			$Anchor = '';
		}
		$url = $att.substr($url, 1).$Anchor;
		return $PartOne.$url.$PartTwo;
	}

	function GetUrl($line, $tag) {
		// find TAG
		$tagpos = strpos(strtoupper($line), $tag);
		$egalpos = $tagpos + strlen($tag);
		// find =
		while($line[$egalpos] == ' ') {
			$egalpos++;
		}
		// if not found return false
		if($line[$egalpos] != '=') {
			$Result[0] = substr($line, 0, $egalpos);
			$Result[1] = '';
			$Result[2] = substr($line, $egalpos);
			return $Result;
		}
		$urlpos = $egalpos + 1;
		while($line[$urlpos] == ' ') {
			$urlpos++;
		}
		if($line[$urlpos] != '"') {
			$Result[0] = substr($line, 0, $egalpos);
			$Result[1] = '';
			$Result[2] = substr($line, $egalpos);
			return $Result;
		}
		$urlpos++;
		// extract url
		$PartOne = substr($line, 0, $urlpos);
		$url = substr($line, $urlpos);
		$PartTwo = substr($url, strpos($url, '"'));
		$url = substr($url, 0, strpos($url, '"'));
		$Result[0] = $PartOne;
		$Result[1] = $url;
		$Result[2] = $PartTwo;
		return $Result;
	}

	function SaveSRC($line, $tag) {
		global $CHECK_PAGE, $DEFAULTS, $PHP;

		if(isset($DEFAULTS->Prefix)) {
			$att = $DEFAULTS->Prefix;
		} else {
			$att = '';
		}
		$localpath = $CHECK_PAGE->path;
		$temppath = '';

		list($PartOne, $url, $PartTwo) = $this->GetUrl($line, $tag);
		if(stristr($PartTwo, $tag)) {
			$PartTwo = $this->SaveSRC($PartTwo, $tag);
		}
		if(strlen($url) == 0) {
			return $PartOne.$PartTwo;
		}

		while(stristr($url, '../')) {
			// remove '../'
			$url = substr($url, strpos($url, '../') + 3);
			// add the original path
			$temppath = $temppath.substr($localpath, strrpos($localpath, '/'));
			$localpath = substr($localpath, 0, strrpos($localpath, '/'));
		}
		$url = str_replace('./', '', $url);

		if(stristr($url, 'HTTP://') OR stristr($url, '?')) {
			return $PartOne.$url.$PartTwo;
		}

		if(strlen($url) < 1) {
			return $PartOne.$url.$PartTwo;
		}
		if(substr($url, 0, 1) != '/') {
			$url = $localpath.'/'.$url;
		}

		if(!$this->FindInArray($url, $DEFAULTS->SavedFiles)) {
			$this->CopyFile($DEFAULTS->DOCUMENT_ROOT.$url, $DEFAULTS->WriteToDir.$url);
			$DEFAULTS->SavedFiles[count($DEFAULTS->SavedFiles)] = $url;
		}

		$url = $att.substr($url, 1);
		return $PartOne.$url.$PartTwo;
	}

	function CountChar($needle, $stack) {
		$temp = explode($needle, $stack);
		return count($temp);
	}

	function ParsePage() {
		global $CHECK_PAGE, $DEFAULTS, $PAGE, $HELPER, $QUERY_STRING;

		unset($DEFAULTS->CurrentAppendix);
		unset($DEFAULTS->PageType);

		if(isset($CHECK_PAGE->QUERY_STRING)) {
			$GLOBALS["QUERY_STRING"] = $CHECK_PAGE->QUERY_STRING;
			$DEFAULTS->CurrentAppendix = substr($CHECK_PAGE->QUERY_STRING, strpos(strtoupper($CHECK_PAGE->QUERY_STRING), 'TEMPLATE=') + 9);
			$DEFAULTS->CurrentAppendix = str_replace('/', '_', $DEFAULTS->CurrentAppendix);
			$DEFAULTS->CurrentAppendix = str_replace('.', '_', $DEFAULTS->CurrentAppendix);
			$DEFAULTS->CurrentAppendix = $DEFAULTS->CurrentAppendix.'_';
		}
		$GLOBALS["PAGE"] = new Page;

		if(!isset($DEFAULTS->PageType)) {
			$DEFAULTS->PageType = '';
		}
		if($DEFAULTS->PageType == 'html') {
			$PageLines = @file($DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'/'.$CHECK_PAGE->name);
		} else {
			$GLOBALS["MENU"] = new menu;
			global $MENU;
			$MENU->TEMPLATE = new menutemplate;
			$DEFAULTS->TEMPLATE = new template($DEFAULTS->TEMPLATE);
			$PageLines = $DEFAULTS->TEMPLATE->PreParse($DEFAULTS->TEMPLATE->content->lines);
		}
		$UpDirCount = $this->CountChar('/', $CHECK_PAGE->path);
		$DEFAULTS->Prefix = '';

		for($i = 1; $i < $UpDirCount; $i++) {
			$DEFAULTS->Prefix = $DEFAULTS->Prefix.'../';
		}
		$CountLines = count($PageLines);
		$Content = '';

		for($i = 0; $i < $CountLines; $i++) {
			// change URL's
			if(stristr($PageLines[$i], 'href')) {
				$PageLines[$i] = $this->ChangeURL($PageLines[$i]);
			}
			// save GIF's und JPG's
			if(stristr($PageLines[$i], 'src')) {
				$PageLines[$i] = $this->SaveSRC($PageLines[$i], 'SRC');
			}
			// save background
			if(stristr($PageLines[$i], 'background=') OR stristr($PageLines[$i], 'background =')) {
				$PageLines[$i] = $this->SaveSRC($PageLines[$i], 'BACKGROUND');
			}
			$Content = $Content.$PageLines[$i];
		}

		$TempDir = $DEFAULTS->WriteToDir.$CHECK_PAGE->path;
		if(!file_exists($TempDir)) {
			$this->CreateDir($TempDir);
		}
		if(isset($DEFAULTS->CurrentAppendix)) {
			$CHECK_PAGE->name = $DEFAULTS->CurrentAppendix.$CHECK_PAGE->name;
		}
		$fp = fopen($TempDir.'/'.$CHECK_PAGE->name, "wb+");
		fwrite($fp, $Content, strlen($Content));
		fclose($fp);
	}

	function FileSpider($File) {
		global $CHECK_PAGE, $DEFAULTS, $PAGE, $HELPER, $QUERY_STRING;

		unset($DEFAULTS->TEMPLATE);
		unset($GLOBALS["PAGE"]);
		unset($GLOBALS["MENU"]);
		unset($GLOBALS["QUERY_STRING"]);
		unset($DEFAULTS->Prefix);
		unset($CHECK_PAGE->error);
		unset($CHECK_PAGE->extension);
		unset($CHECK_PAGE->name);
		unset($CHECK_PAGE->path);

		$CHECK_PAGE->DoFile($File);

		if(isset($CHECK_PAGE->error)) {
			return;
		}
		if($this->FindInArray('.'.$CHECK_PAGE->extension, $DEFAULTS->IgnorFiles)) {
			return;
		}
		if($this->FindInArray('.'.$CHECK_PAGE->extension, $DEFAULTS->SaveFiles)) {
			$url = $CHECK_PAGE->path.'/'.$CHECK_PAGE->name;
			if(!$this->FindInArray($url, $DEFAULTS->SavedFiles)) {
				$this->CopyFile($DEFAULTS->DOCUMENT_ROOT.$url, $DEFAULTS->WriteToDir.$url);
				$DEFAULTS->SavedFiles[count($DEFAULTS->SavedFiles)] = $url;
			}
			return;
		}
		if($this->FindInArray('.'.$CHECK_PAGE->extension, $DEFAULTS->Extensions)) {
			$this->ParsePage();
			return;
		}
		if(strlen(trim($CHECK_PAGE->extension)) == 0) {
			if(file_exists($DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'/index.htm')) {
				$CHECK_PAGE->name = 'index.htm';
			}
			if(file_exists($DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'/index.html')) {
				$CHECK_PAGE->name = 'index.html';
			}
			$this->ParsePage();
			return;
		}
	}
}

?>