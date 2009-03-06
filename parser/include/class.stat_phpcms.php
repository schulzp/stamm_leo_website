<?php
/* $Id: class.stat_phpcms.php,v 1.4.2.22 2006/06/18 18:07:32 ignatius0815 Exp $ */
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
   |    Wolfgang Ulmer (wulmer)
   |    Thilo Wagner (ignatius0815)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

// correct some variables
if($DEFAULTS->STATS_DIR[0] == '.') {
	$DEFAULTS->StatDir = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->SCRIPT_PATH.'/'.$DEFAULTS->STATS_DIR;
} else {
	$DEFAULTS->StatDir = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->STATS_DIR;
}
if($DEFAULTS->STATS_CURRENT[0] == '.') {
	$DEFAULTS->StatFile = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->SCRIPT_PATH.'/'.$DEFAULTS->STATS_CURRENT.'/'.$DEFAULTS->STATS_FILE;
} else {
	$DEFAULTS->StatFile = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->STATS_CURRENT.'/'.$DEFAULTS->STATS_FILE;
}
if($DEFAULTS->STATS_BACKUP[0] == '.') {
	$DEFAULTS->StatBackupDir = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->SCRIPT_PATH.'/'.$DEFAULTS->STATS_BACKUP;
} else {
	$DEFAULTS->StatBackupDir = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->STATS_BACKUP;
}

if(strlen($DEFAULTS->STATS_REFERER_IGNORE) > 0) {
	$DEFAULTS->STATS_REFERER_IGNORE = $DEFAULTS->STATS_REFERER_IGNORE.';no referer';
} else {
	$DEFAULTS->STATS_REFERER_IGNORE = 'no referer';
}

// functions started here
class Stats {
	function sortStatFile() {
		asort($this->File[0]);
		$j = 0;
		while(list($i) = each($this->File[0])) {
			$temp[0][$j] = $this->File[0][$i];
			$temp[1][$j] = $this->File[1][$i];
			$temp[2][$j] = $this->File[2][$i];
			$temp[3][$j] = $this->File[3][$i];
			$temp[4][$j] = $this->File[4][$i];
			$temp[5][$j] = $this->File[5][$i];
			$temp[6][$j] = $this->File[6][$i];
			$j++;
		}
		unset($this->File);
		for($i = 0; $i < $j; $i++) {
			$this->File[0][$i] = $temp[0][$i];
			$this->File[1][$i] = $temp[1][$i];
			$this->File[2][$i] = $temp[2][$i];
			$this->File[3][$i] = $temp[3][$i];
			$this->File[4][$i] = $temp[4][$i];
			$this->File[5][$i] = $temp[5][$i];
			$this->File[6][$i] = $temp[6][$i];
		}
	}

	function addFile($StatFile) {
		// adds a file to the stat-object
		$i = 0;
		$fp = @fopen($StatFile, 'r');
		if($fp) {
			//echo $StatFile;
			while(!feof($fp)) {
				$entry = trim(fgets($fp, 4096));
				if($entry != '') {
					$Temp[$i] = $entry."\n";
					$i++;
				}
			}
			fclose($fp);
		}

		if(!isset($Temp)) {
			return;
		}

		$FileCounter = count($Temp);
		if(isset($this->File[0])) {
			$StartStat = count($this->File[0]);
		} else {
			$StartStat = 0;
		}

		for($i = 0; $i < $FileCounter; $i++) {
			$temp = explode(';', $Temp[$i]);
			$this->File[0][$StartStat+$i] = $temp[0];
			$this->File[1][$StartStat+$i] = $temp[1];
			$this->File[2][$StartStat+$i] = $temp[2];
			$this->File[3][$StartStat+$i] = $temp[3];
			$this->File[4][$StartStat+$i] = $temp[4];
			$this->File[5][$StartStat+$i] = $temp[5];
			$this->File[6][$StartStat+$i] = $temp[6];
		}
	}

	function Stats($StatFile) {
		// inializing the stat-object
		global $DEFAULTS;

		// define constants
		$this->Date = 0;
		$this->IP = 1;
		$this->HTTP = 2;
		$this->METHOD = 3;
		$this->Referer = 4;
		$this->Browser = 5;
		$this->URL = 6;

		// add file to stat-object
		$this->addFile($StatFile);
	}
}

function GetIP($Stat) {
	// get the IP-adresses for a stat-object
	global $DEFAULTS;

	// get and count the IP's to ignore
	$Ignore = explode(';', $DEFAULTS->STATS_IP_IGNORE);
	$CountIgnore = count($Ignore);
	$j = 0;

	// sort the arry on IP's
	asort($Stat->File[$Stat->IP]);
	while(list($i) = each($Stat->File[$Stat->IP])) {
		$IP[$j] = $Stat->File[$Stat->IP][$i];
		$j++;
	}
	$i = 0;

	// put the IP's in the result-array and count the IP's
	while($i < $j) {
		$Result['number'][$i] = $IP[$i];
		$Result['count'][$i] = 1;
		$actual = $i;
		$i++;
		while($i < $j AND $IP[$i] == $IP[$i-1]) {
			$Result['count'][$actual]++;
			$i++;
		}
	}
	$j = 0;

	// sort the array on occurance
	arsort($Result['count']);
	while(list($i) = each($Result['count'])) {
		if(trim($Result['number'][$i]) == '') {
			continue;
		}
		for($k = 0; $k < $CountIgnore; $k++) {
			if(strlen(trim($Ignore[$k])) == 0 ) {
				continue;
			}
			if(stristr($Result['number'][$i], trim($Ignore[$k]))) {
				continue 2;
			}
		}
		$ReturnArray['count'][$j] = $Result['count'][$i];
		$ReturnArray['number'][$j] = $Result['number'][$i];
		$j++;
		if($j > $DEFAULTS->STATS_IP_COUNT) {
			break;
		}
	}
	if(isset($ReturnArray)) {
		return $ReturnArray;
	} else {
		return;
	}
}

function GetURL($Stat) {
	// get the called URL's from the stat-oject
	global $DEFAULTS;
	$ReturnArray = array();

	// get and count the URL's to ignore
	$ToIgnore = explode(';', $DEFAULTS->STATS_REFERER_IGNORE);
	$j = 0;
	asort($Stat->File[$Stat->URL]);
	while(list($i) = each($Stat->File[$Stat->URL])) {
		if(trim($Stat->File[$Stat->Referer][$i]) == '') {
			$Stat->File[$Stat->Referer][$i] = 'no referer';
		}
		$Counter = count($ToIgnore);
		for($l = 0; $l < $Counter; $l++) {
			if(strlen(trim($ToIgnore[$l])) == 0) {
				continue;
			}
			if(stristr($Stat->File[$Stat->Referer][$i], $ToIgnore[$l])) {
				continue 2;
			}
		}
		$Temp['url'][$j] = $Stat->File[$Stat->URL][$i];
		$Temp['referer'][$j] = $Stat->File[$Stat->Referer][$i];
		$j++;
		if($j > $DEFAULTS->STATS_URL_COUNT) {
			break;
		}
	}
	$i = 0;
	$k = 0;
	while($i < $j) {
		$ReturnArray['url'][$k]['adr'] = $Temp['url'][$i];
		$ReturnArray['url'][$k]['count'] = 1;
		$ReturnArray['url'][$k]['referer']['adr'][0] = $Temp['referer'][$i];
		$ReturnArray['url'][$k]['referer']['count'][0] = 1;
		$i++;
		while($i < $j AND trim($Temp['url'][$i]) == trim($Temp['url'][$i-1])) {
			$ReturnArray['url'][$k]['count']++;
			$pos = InArray($Temp['referer'][$i],$ReturnArray['url'][$k]['referer']['adr']);
			if($pos != -1) {
				$ReturnArray['url'][$k]['referer']['count'][$pos]++;
			} else {
				$CurrentRef = count($ReturnArray['url'][$k]['referer']['adr']);
				$ReturnArray['url'][$k]['referer']['adr'][$CurrentRef] = $Temp['referer'][$i];
				$ReturnArray['url'][$k]['referer']['count'][$CurrentRef] = 1;
			}
			$i++;
		}
		$k++;
	}
	return $ReturnArray;
}

function InArray($needle, $heystack) {
	// checks for needle in Array
	// returns pos if found and -1 if not found

	$Counter = count($heystack);
	for($i=0; $i < $Counter; $i++) {
		if(trim($needle) == trim($heystack[$i])) {
			return $i;
		}
	}
	return -1;
}

function GetReferer($Stat) {
	global $DEFAULTS;
	$ReturnArray = array();

	$Ignore = explode(';', $DEFAULTS->STATS_REFERER_IGNORE);
	$j = 0;
	asort($Stat->File[$Stat->Referer]);
	while(list($i) = each($Stat->File[$Stat->Referer])) {
		$Temp[$j]= $Stat->File[$Stat->Referer][$i];
		$j++;
	}
	$i = 0;
	while($i < $j) {
		$Result['Referer'][$i] = $Temp[$i];
		$Result['count'][$i] = 1;
		$actual = $i;
		$i++;
		while($i < $j AND $Temp[$i] == $Temp[$i-1]) {
			$Result['count'][$actual]++;
			$i++;
		}
	}
	$j = 0;
	arsort($Result['count']);
	$CountIgnore = count($Ignore);
	while(list($i) = each($Result['count'])) {
		if(trim($Result['Referer'][$i]) == '') {
			continue;
		}
		for($k = 0; $k < $CountIgnore; $k++) {
			if(strlen(trim($Ignore[$k])) == 0) {
				continue;
			}
			if(stristr($Result['Referer'][$i], trim($Ignore[$k]))) {
				continue 2;
			}
		}
		$ReturnArray['referer'][$j] = $Result['Referer'][$i];
		$ReturnArray['count'][$j] = $Result['count'][$i];
		$j++;
		if($j > $DEFAULTS->STATS_REFERER_COUNT) {
			break;
		}
	}
	return $ReturnArray;
}

function GetIPSum($Stat) {
	$j = 0;
	asort($Stat->File[$Stat->IP]);
	while(list($i) = each($Stat->File[$Stat->IP])) {
		$Temp[$j]= $Stat->File[$Stat->IP][$i];
		$j++;
	}
	$i = 0;
	$IPcount = 0;
	while($i < $j) {
		$i++;
		$IPcount++;

		while($i < $j AND $Temp[$i] == $Temp[$i-1]) {
			$i++;
		}
	}
	return $IPcount;
}

function CheckUpUser($index, $Stat) {
	// debug !!
	// DebugFile('CheckUpUser!');
	$FutureTime = $Stat->File[$Stat->Date][$index]+1800;
	$i = $index + 1;
	$ArryCount = count($Stat->File[$Stat->Date]);

	while($i < $ArryCount AND $Stat->File[$Stat->Date][$i] < $FutureTime) {
		// debug !!
		// DebugFile($i);
		if($Stat->File[$Stat->IP][$i] == $Stat->File[$Stat->IP][$index]) {
			return true;
		}
		$i++;
	}
	return false;
}

function GetUser($Stat) {
	// debug !!
	// DebugFile("GET USER!!!!");
	$user = 0;

	$ArryCount = count($Stat->File[$Stat->IP]);
	for($i = 0; $i < $ArryCount; $i++) {
		// debug !!
		// DebugFile("GET USER>".$i);
		if(!CheckUpUser($i, $Stat))
			$user++;
		}
	return $user;
}

function MakeFiles() {
	global $DEFAULTS,$PHP,$MESSAGES;

	if(!$PHP->LockFile($DEFAULTS->StatFile, 'set')) {
		echo $MESSAGES[169];
	}
	$Stat = new Stats($DEFAULTS->StatFile);
	$ArrayCount = count($Stat->File[$Stat->Date]);
	if(!isset($Stat->File[$Stat->Date][0])) {
		$PHP->LockFile($DEFAULTS->StatFile, 'release');
		return;
	}
	$TargetFileName = date("YmdHis", $Stat->File[$Stat->Date][0]).'-'.date("YmdHis", $Stat->File[$Stat->Date][$ArrayCount - 1]).'.txt';
	$TargetFile = $DEFAULTS->StatBackupDir.'/'.$TargetFileName;
	if($Stat->File[$Stat->Date][0] != 0) {
		copy($DEFAULTS->StatFile, $TargetFile);
		@unlink($DEFAULTS->StatFile);
		$fp = @fopen($DEFAULTS->StatFile, 'a+');
		if($fp) {
			fclose($fp);
		}
	}
	$PHP->LockFile($DEFAULTS->StatFile, 'release');
	if($Stat->File[$Stat->Date][0] != 0) {
		InitFiles($Stat);
	}
}

function DebugFile($entry) {
	$GLOBALS['dp'] = fopen('C:/debug.txt', 'a+');
	fwrite($GLOBALS['dp'], $entry."\n", strlen($entry."\n"));
	fclose($GLOBALS['dp']);
}

function InitFiles($Stat) {
	global $DEFAULTS, $PHP, $MESSAGES;

	$ArrayCount = count($Stat->File[$Stat->Date]);

	$i = 0;
	while($i < $ArrayCount) {
		$CurrentYear = date("Y", $Stat->File[$Stat->Date][$i]);
		$YearDir = $DEFAULTS->StatDir.'/'.date("Y", $Stat->File[$Stat->Date][$i]);
		if(!file_exists($YearDir)) {
			mkdir ($YearDir, 0777);
		}
		while($i < $ArrayCount AND $CurrentYear == date("Y", $Stat->File[$Stat->Date][$i])) {
			$CurrentMonth = date("m", $Stat->File[$Stat->Date][$i]);
			$MonthDir = $YearDir.'/'.date("m", $Stat->File[$Stat->Date][$i]);
			if(!file_exists($MonthDir)) {
				mkdir ($MonthDir, 0777);
			}
			while($i < $ArrayCount AND $CurrentMonth == date("m", $Stat->File[$Stat->Date][$i])) {
				// debug !!
				// DebugFile($i);
				$CurrentDay = date("d", $Stat->File[$Stat->Date][$i]);
				$DayFile = $MonthDir.'/'.date("d", $Stat->File[$Stat->Date][$i]).'.txt';
				$fp = fopen($DayFile, 'a');
				$entry = $Stat->File[$Stat->Date][$i].';'.$Stat->File[$Stat->IP][$i].';'.$Stat->File[$Stat->HTTP][$i].';';
				$entry = $entry.$Stat->File[$Stat->METHOD][$i].';'.$Stat->File[$Stat->Referer][$i].';';
				$entry = $entry.$Stat->File[$Stat->Browser][$i].';'.$Stat->File[$Stat->URL][$i];
				fwrite($fp, $entry, strlen($entry));
				$i++;
				while($i < $ArrayCount AND $CurrentDay == date("d", $Stat->File[$Stat->Date][$i]) AND $CurrentMonth == date("m", $Stat->File[$Stat->Date][$i])) {
					// debug !!
					// DebugFile($i);
					$entry = $Stat->File[$Stat->Date][$i].';'.$Stat->File[$Stat->IP][$i].';'.$Stat->File[$Stat->HTTP][$i].';';
					$entry = $entry.$Stat->File[$Stat->METHOD][$i].';'.$Stat->File[$Stat->Referer][$i].';';
					$entry = $entry.$Stat->File[$Stat->Browser][$i].';'.$Stat->File[$Stat->URL][$i];
					fwrite($fp, $entry, strlen($entry));
					$i++;
				}
				fclose($fp);
				DrawActionRow($MESSAGES[150].$CurrentYear.'/'.$CurrentMonth.'/'.$CurrentDay);
				WriteDaySummary($CurrentYear,$CurrentMonth,$CurrentDay);
			}
			DrawActionRow($MESSAGES[150].$CurrentYear.'/'.$CurrentMonth);
			WriteMonthSummary($CurrentYear,$CurrentMonth);
		}
		DrawActionRow($MESSAGES[150].$CurrentYear);
		WriteYearSummary($CurrentYear);
	}
	DrawActionRow($MESSAGES[151]);
	WriteWholeSummary();
}

function WriteDaySummary($Year, $Month, $Day) {
	global $DEFAULTS;

	// debug !!
	// DebugFile("Start WriteDaySummary!");
	$StatFile = $DEFAULTS->StatDir.'/'.$Year.'/'.$Month.'/'.$Day.'.txt';
	$Stat = new Stats($StatFile);

	// debug !!
	// DebugFile("Get User!");
	$UniqueUsers = GetUser($Stat);

	// debug !!
	// DebugFile("Get IP!");
	$IPSum = GetIPSum($Stat);

	// debug !!
	// DebugFile("Get Hits!");
	$HitsSum = count($Stat->File[$Stat->Date]);

	// debug !!
	// DebugFile("Get Startdate!");
	$StartDate = $Stat->File[$Stat->Date][0];

	// debug !!
	// DebugFile("Get Enddate!");
	$EndDate = $Stat->File[$Stat->Date][($HitsSum - 1)];
	$Scope = floor(($EndDate-$StartDate) / 864);
	$CalcScope = ($EndDate-$StartDate) / 86400;
	$HitsUser = floor(($HitsSum / $UniqueUsers) * 100) / 100;
	$HitsDay = floor(($HitsSum / $CalcScope) * 100) / 100;
	$UserDay = floor(($UniqueUsers / $CalcScope) * 100) / 100;
	$VisitsUser = floor(($UniqueUsers / $IPSum) * 100) / 100;

	$sumFile = $DEFAULTS->StatDir.'/'.$Year.'/'.$Month.'/'.$Day.'.sum';
	$fp = fopen($sumFile, 'w+');

	if(isset($fp)) {
		fwrite($fp, $HitsDay."\n", 1024);
		fwrite($fp, $HitsUser."\n", 1024);
		fwrite($fp, $UserDay."\n", 1024);
		fwrite($fp, $VisitsUser."\n", 1024);
		fwrite($fp, $HitsSum."\n", 1024);
		fwrite($fp, ($Scope / 100 * 24)."\n", 1024);
		fwrite($fp, $StartDate."\n", 1024);
		fwrite($fp, $EndDate."\n", 1024);
		fwrite($fp, $UniqueUsers."\n", 1024);
		fwrite($fp, $IPSum."\n", 1024);
		fclose($fp);
	}

	//DrawActionRow('Get Referer: '.$Year.$Month.$Day);
	// debug !!
	// DebugFile("Get Referer!");
	$Referer = GetReferer($Stat);
	$refFile = $DEFAULTS->StatDir.'/'.$Year.'/'.$Month.'/'.$Day.'.ref';
	$fp = fopen($refFile, 'w+');
	if(isset($Referer['referer']) AND isset($fp)) {
		for($i = 0; $i < count($Referer['referer']); $i++) {
			$entry = $Referer['count'][$i].';'.$Referer['referer'][$i]."\n";
			fwrite($fp, $entry, strlen($entry));
		}
		fclose($fp);
	}

	//DrawActionRow('Get IP: '.$Year.$Month.$Day);
	// debug !!
	// DebugFile("Get IP!");
	$IP = GetIP($Stat);
	$ipFile = $DEFAULTS->StatDir.'/'.$Year.'/'.$Month.'/'.$Day.'.ip';

	$IPArrayCount = count($IP['number']);

	if(isset($IP['count'])) {
		$fp = fopen($ipFile, 'w+');
		for($i = 0; $i < $IPArrayCount; $i++) {
			$entry = $IP['count'][$i].';'.$IP['number'][$i]."\n";
			fwrite($fp, $entry, strlen($entry));
		}
		fclose($fp);
	}

	//DrawActionRow('Get URL: '.$Year.$Month.$Day);
	// debug !!
	// DebugFile("Get URL!");
	$URL = GetURL($Stat);
	$k = 0;
	$Counter = @count($URL['url']);
	for($i = 0; $i < $Counter; $i++) {
		$iCounter = count($URL['url'][$i]['referer']['adr']);
		for($j = 0; $j < $iCounter; $j++) {
			$Entry['sort'][$k] = sprintf("%'010s", $URL['url'][$i]['count']).$URL['url'][$i]['adr'].sprintf("%'010s", $URL['url'][$i]['referer']['count'][$j]);
			$Entry['urlcount'][$k] = $URL['url'][$i]['count'];
			$Entry['urladr'][$k] = trim($URL['url'][$i]['adr']);
			$Entry['refcount'][$k] = $URL['url'][$i]['referer']['count'][$j];
			$Entry['refadr'][$k] = $URL['url'][$i]['referer']['adr'][$j];
			$k++;
		}
	}

	if(isset($Entry['sort'])) {
		arsort($Entry['sort']);
		$urlFile = $DEFAULTS->StatDir.'/'.$Year.'/'.$Month.'/'.$Day.'.url';
		$fp = fopen($urlFile, 'w+');
		if(isset($fp)) {
			while(list($i) = each($Entry['sort'])) {
				$entry = $Entry['urlcount'][$i].';'.$Entry['urladr'][$i].';'.$Entry['refcount'][$i].';'.$Entry['refadr'][$i]."\n";
				fwrite($fp, $entry, strlen($entry));
			}
			fclose($fp);
		}
	}
}

function ConcentrateSum($SumSource, $SumTarget) {
	$CountEntrys = count($SumSource);
	$HitsDay = '';
	$HitsUser = '';
	$UserDay = '';
	$VistisUser = '';
	$HitsSum = '';
	$Scope = '';
	$UniqueUsers = '';
	$IPSum = '';

	for($i = 0; $i < $CountEntrys; $i++) {
		$fp = fopen($SumSource[$i], 'r');
		$HitsDay       = $HitsDay + (trim(fgets($fp, 1024)));
		$HitsUser      = $HitsUser + (trim(fgets($fp, 1024)));
		$UserDay       = $UserDay + (trim(fgets($fp, 1024)));
		$VistisUser    = $VistisUser + (trim(fgets($fp, 1024)));
		$HitsSum       = $HitsSum + (trim(fgets($fp, 1024)));
		$Scope         = $Scope + (trim(fgets($fp, 1024)));
		$StartDate[$i] = trim(fgets($fp, 1024));
		$EndDate[$i]   = trim(fgets($fp, 1024));
		$UniqueUsers   = $UniqueUsers + (trim(fgets($fp, 1024)));
		$IPSum         = $IPSum + (trim(fgets($fp, 1024)));
		fclose($fp);
	}
	if($CountEntrys == 0) {
		$CountEntrys = 1;
	}
	$HitsDay = floor($HitsDay / $CountEntrys);
	$HitsUser = floor($HitsUser / $CountEntrys * 100) / 100;
	$UserDay = floor($UserDay / $CountEntrys);
	$VistisUser = floor($VistisUser / $CountEntrys * 100) / 100;

	$fp = fopen($SumTarget.'.sum', 'w+');
	fwrite($fp, $HitsDay."\n", 1024);
	fwrite($fp, $HitsUser."\n", 1024);
	fwrite($fp, $UserDay."\n", 1024);
	fwrite($fp, $VistisUser."\n", 1024);
	fwrite($fp, $HitsSum."\n", 1024);
	fwrite($fp, ($Scope / 24)."\n", 1024);
	fwrite($fp, $StartDate[0]."\n", 1024);
	fwrite($fp, $EndDate[$CountEntrys - 1]."\n", 1024);
	fwrite($fp, $UniqueUsers."\n", 1024);
	fwrite($fp, $IPSum."\n", 1024);
	fclose ($fp);
}

function ConcentrateIP($IPFile, $IPTarget) {
	$CountEntrys = count($IPFile);
	$j = 0;
	for($i = 0; $i < $CountEntrys; $i++) {
		$fp = fopen($IPFile[$i], 'r');
		while(!feof($fp)) {
			$entry = explode(';', trim(fgets($fp, 1024)));
			if(isset($entry[1])) {
				$IP['number'][$j] = $entry[1];
				$IP['count'][$j] = $entry[0];
				$j++;
			}
		}
		fclose($fp);
	}

	// sort and truncate IP
	if(!isset($IP['number'])) {
		return;
	}

	$i = 0;
	$j = 0;
	asort($IP['number']);
	while(list($i) = each($IP['number'])) {
		$TempIP[$j] = $IP['number'][$i];
		$TempCount[$j] = $IP['count'][$i];
		if(strlen($IP['number'][$i]) > 0) {
			$j++;
		}
	}
	$i = 0;
	$k = 0;
	while($i < $j) {
		$Result['IP'][$k] = $TempIP[$i];
		$Result['count'][$k] = $TempCount[$i];
		$i++;
		while($i < $j AND $TempIP[$i] == $TempIP[$i - 1]) {
			$Result['count'][$k] = $Result['count'][$k] + $TempCount[$i];
			$i++;
		}
		$k++;
	}
	reset($Result['count']);
	arsort($Result['count']);
	// write the IP-file
	$fp = fopen($IPTarget.'.ip', 'w+');
	while(list($i) = each($Result['count'])) {
		if(trim($Result['IP'][$i]) != '') {
			$entry = $Result['count'][$i].';'.$Result['IP'][$i]."\n";
			fwrite($fp, $entry, strlen($entry));
		}
	}
	fclose($fp);
}

function ConcentrateReferer($RefFile, $RefTarget) {
	$CountEntrys = count($RefFile);
	$j = 0;
	for($i = 0; $i < $CountEntrys; $i++) {
		$fp = fopen($RefFile[$i], 'r');
		while(!feof($fp)) {
			$entry = explode(';', trim(fgets($fp, 1024)));
			if(isset($entry[1])) {
				$Temp['adr'][$j] = $entry[1];
				$Temp['count'][$j] = $entry[0];
				$j++;
			}
		}
		fclose($fp);
	}
	// sort and truncate the referer
	$i = 0;
	$j = 0;
	asort($Temp['adr']);
	while(list($i) = each($Temp['adr'])) {
		$Referer['adr'][$j] = $Temp['adr'][$i];
		$Referer['count'][$j] = $Temp['count'][$i];
		$j++;
	}
	unset($Temp);
	$i = 0;
	$k = 0;
	while($i < $j) {
		$Temp['adr'][$k] = $Referer['adr'][$i];
		$Temp['count'][$k] = $Referer['count'][$i];
		$i++;
		while($i < $j AND $Referer['adr'][$i] == $Referer['adr'][$i - 1]) {
			$Temp['count'][$k] = $Temp['count'][$k]+$Referer['count'][$i];
			$i++;
		}
		$k++;
	}
	reset($Temp['count']);
	arsort($Temp['count']);

	// write the file
	$fp = fopen($RefTarget.'.ref', 'w+');
	while(list($i) = each ($Temp['count'])) {
		if(trim($Temp['adr'][$i]) != '') {
			$entry = $Temp['count'][$i].';'.$Temp['adr'][$i]."\n";
			fwrite($fp, $entry, strlen($entry));
		}
	}
	fclose($fp);
}

function ConcentrateUrl($UrlFile, $UrlTarget) {
	$CountEntrys = count($UrlFile);
	$j = 0;
	for($i = 0; $i < $CountEntrys; $i++) {
		$fp = fopen($UrlFile[$i], 'r');
		while(!feof($fp)) {
			$entry = explode(';', trim(fgets($fp, 4096)));
			if(isset($entry[1])) {
				$Url['url']['adr'][$j] = $entry[1];
				$Url['referer']['adr'][$j] = $entry[3];
				$Url['referer']['count'][$j] = $entry[2];
				$Url['sort'][$j] = $Url['url']['adr'][$j].$Url['referer']['adr'][$j];
				$j++;
			}
		}
		fclose($fp);
	}
	unset($Result);
	$i = 0;
	$j = 0;
	asort($Url['sort']);

	while(list($i) = each($Url['sort'])) {
		$Result['url']['adr'][$j]       = $Url['url']['adr'][$i];
		$Result['referer']['count'][$j] = $Url['referer']['count'][$i];
		$Result['referer']['adr'][$j]   = $Url['referer']['adr'][$i];
		$Result['sort'][$j]             = $Url['sort'][$i];
		$j++;
	}
	$i = 0;
	$k = 0;
	$l = 0;
	$m = 0;
	while($i < $j) {
		$TempResult['url'][$k]['count']                = $Result['referer']['count'][$i];
		$TempResult['url'][$k]['adr']                  = $Result['url']['adr'][$i];
		$TempResult['url'][$k]['referer'][$l]['count'] = $Result['referer']['count'][$i];
		$TempResult['url'][$k]['referer'][$l]['adr']   = $Result['referer']['adr'][$i];
		$i++;
		while($i < $j AND $Result['url']['adr'][$i] == $Result['url']['adr'][$i - 1]) {
			$TempResult['url'][$k]['count'] = $TempResult['url'][$k]['count'] + $Result['referer']['count'][$i];
			if($Result['sort'][$i] == $Result['sort'][$i - 1]) {
				$TempResult['url'][$k]['referer'][$l]['count'] = $TempResult['url'][$k]['referer'][$l]['count'] + $Result['referer']['count'][$i];
			} else {
				$l++;
				$TempResult['url'][$k]['referer'][$l]['count'] = $Result['referer']['count'][$i];
				$TempResult['url'][$k]['referer'][$l]['adr']   = $Result['referer']['adr'][$i];
			}
			$i++;
		}
		$k++;
		$l = 0;
	}
	unset($Result);
	$k = 0;
	$Counter = count($TempResult['url']);
	for($i = 0; $i < $Counter; $i++) {
		$iCounter = count($TempResult['url'][$i]['referer']);
		for($j = 0; $j < $iCounter; $j++) {
			if(trim($TempResult['url'][$i]['adr']) == '') {
				continue;
			}
			$Result['count'][$k]    = sprintf("%'010s", $TempResult['url'][$i]['count']).$TempResult['url'][$i]['adr'].sprintf("%'010s", $TempResult['url'][$i]['referer'][$j]['count']);
			$Result['urlcount'][$k] = $TempResult['url'][$i]['count'];
			$Result['urladr'][$k]   = $TempResult['url'][$i]['adr'];
			$Result['refcount'][$k] = $TempResult['url'][$i]['referer'][$j]['count'];
			$Result['refadr'][$k]   = $TempResult['url'][$i]['referer'][$j]['adr'];
			$k++;
		}
	}

	// write the file
	arsort($Result['count']);
	$fp = fopen($UrlTarget.'.url', 'w+');
	while(list($i) = each ($Result['count'])) {
		$entry = $Result['urlcount'][$i].';'.$Result['urladr'][$i];
		$entry = $entry.';'.$Result['refcount'][$i].';'.$Result['refadr'][$i]."\n";
		fwrite($fp, $entry, strlen($entry));
	}
	fclose($fp);
}

function WriteMonthSummary($Year, $Month) {
	global $DEFAULTS;

	$ReadMonthDir  = $DEFAULTS->StatDir.'/'.$Year.'/'.$Month;
	$WriteMonthDir = $DEFAULTS->StatDir.'/'.$Year;

	// get files
	$d = dir($ReadMonthDir);
	$Sumi = 0;
	$Refi = 0;
	$Urli = 0;
	$IPi = 0;
	while($entry = $d->read()) {
		if(stristr($entry, '.sum')) {
			$SumFile[$Sumi] = $ReadMonthDir.'/'.$entry;
			$Sumi++;
			continue;
		}
		if(stristr($entry, '.ref')) {
			$RefFile[$Refi] = $ReadMonthDir.'/'.$entry;
			$Refi++;
			continue;
		}
		if(stristr($entry, '.url')) {
			$UrlFile[$Urli] = $ReadMonthDir.'/'.$entry;
			$Urli++;
			continue;
		}
		if(stristr($entry, '.ip')) {
			$IPFile[$IPi] = $ReadMonthDir.'/'.$entry;
			$IPi++;
			continue;
		}
	}
	$d->close();

	// get summary
	ConcentrateSum($SumFile, $WriteMonthDir.'/'.$Month);

	// get IP's
	ConcentrateIP($IPFile, $WriteMonthDir.'/'.$Month);

	// get the referer's
	ConcentrateReferer($RefFile, $WriteMonthDir.'/'.$Month);

	// get the url's
	ConcentrateUrl($UrlFile,$WriteMonthDir.'/'.$Month);
}

function WriteYearSummary($Year) {
	global $DEFAULTS;

	$ReadYearDir  = $DEFAULTS->StatDir.'/'.$Year;
	$WriteYearDir = $DEFAULTS->StatDir;

	// get files
	$d = dir($ReadYearDir);
	$Sumi = 0;
	$Refi = 0;
	$Urli = 0;
	$IPi = 0;
	while($entry = $d->read()) {
		if(stristr($entry, '.sum')) {
			$SumFile[$Sumi] = $ReadYearDir.'/'.$entry;
			$Sumi++;
			continue;
		}
		if(stristr($entry, '.ref')) {
			$RefFile[$Refi] = $ReadYearDir.'/'.$entry;
			$Refi++;
			continue;
		}
		if(stristr($entry, '.url')) {
			$UrlFile[$Urli] = $ReadYearDir.'/'.$entry;
			$Urli++;
			continue;
		}
		if(stristr($entry, '.ip')) {
			$IPFile[$IPi] = $ReadYearDir.'/'.$entry;
			$IPi++;
			continue;
		}
	}
	$d->close();

	// get summary
	ConcentrateSum($SumFile, $WriteYearDir.'/'.$Year);

	// get IP's
	ConcentrateIP($IPFile, $WriteYearDir.'/'.$Year);

	// get the referer's
	ConcentrateReferer($RefFile, $WriteYearDir.'/'.$Year);

	// get the url's
	ConcentrateUrl($UrlFile, $WriteYearDir.'/'.$Year);
}

function WriteWholeSummary() {
	global $DEFAULTS;

	$ReadYearDir  = $DEFAULTS->StatDir;
	$WriteYearDir = $DEFAULTS->StatDir;

	// get files
	$d = dir($ReadYearDir);
	$Sumi = 0;
	$Refi = 0;
	$Urli = 0;
	$IPi = 0;
	while($entry = $d->read()) {
		if(stristr($entry, '.sum') AND !stristr($entry, 'all.')) {
			$SumFile[$Sumi] = $ReadYearDir.'/'.$entry;
			$Sumi++;
			continue;
		}
		if(stristr($entry, '.ref') AND !stristr($entry, 'all.')) {
			$RefFile[$Refi] = $ReadYearDir.'/'.$entry;
			$Refi++;
			continue;
		}
		if(stristr($entry, '.url') AND !stristr($entry, 'all.')) {
			$UrlFile[$Urli] = $ReadYearDir.'/'.$entry;
			$Urli++;
			continue;
		}
		if(stristr($entry, '.ip') AND !stristr($entry, 'all.')) {
			$IPFile[$IPi] = $ReadYearDir.'/'.$entry;
			$IPi++;
			continue;
		}
	}
	$d->close();

	// get summary
	ConcentrateSum($SumFile, $WriteYearDir.'/all');

	// get IP's
	ConcentrateIP($IPFile, $WriteYearDir.'/all');

	// get the referer's
	ConcentrateReferer($RefFile, $WriteYearDir.'/all');

	// get the url's
	ConcentrateUrl($UrlFile, $WriteYearDir.'/all');
}

function WriteRow($text, $entry) {
	global $DOCUMENT;

	echo '<tr><td bgcolor="'.$DOCUMENT->ROW_LIGHT.'">'.$DOCUMENT->TABLE_FONT.$text.
		'</font></td><td bgcolor="'.$DOCUMENT->ROW_LIGHT.'">'.$DOCUMENT->TABLE_FONT.$entry.'</td></tr>'."\n";
}

function StripString($entry) {
	$result = '';
	while(strlen($entry) > 60) {
		$result = $result."\n".substr($entry, 0, 60);
		$entry = substr($entry, 60);
	}
	$result = $result."\n".$entry;
	return $result;
}

function DrawUrlReferer($StatFile) {
	global $DEFAULTS, $DOCUMENT, $MESSAGES;
	$title = $MESSAGES[130];
	DrawHeader($title);
	DrawTopLine($title);

	if($StatFile == 'all') {
		$ShowScope = $MESSAGES[152];
	} else {
		$ShowScope = $StatFile;
	}
	echo '<p>'.$DOCUMENT->LEAD_FONT.$MESSAGES[153].$ShowScope.':</font></p>'."\n";
	$UrlCounter = 0;
	$fp = fopen($DEFAULTS->StatDir.'/'.$StatFile.'.url', 'r');
	while(!feof($fp)) {
		$UrlEntry[$UrlCounter] = explode(';', trim(fgets($fp, 4096)));
		if(strlen($UrlEntry[$UrlCounter][0]) > 0 ) {
			$UrlCounter++;
		}
	}
	fclose($fp);
	$i = 0;
	echo '<table border="0" cellspacing="2" cellpadding="5" width="600">'."\n";
	echo '<tr>'."\n";
	echo '<td colspan = "2" bgcolor="'.$DOCUMENT->ROW_LIGHT.'">'.$DOCUMENT->TABLE_FONT.'<b>'.$MESSAGES[154].'</b></font></td>'."\n";
	echo '<td bgcolor="'.$DOCUMENT->ROW_LIGHT.'">'.$DOCUMENT->TABLE_FONT.'<b>Nr.</b></font></td>'."\n";
	echo '</tr>'."\n";
	while($i < $UrlCounter) {
		if(trim($UrlEntry[$i][1]) == '') {
			$i++;
			continue;
		}
		$m = 1;
		echo '<tr>'."\n";
		echo '<td colspan = "2" bgcolor="'.$DOCUMENT->ROW_LIGHT.'">'.$DOCUMENT->TABLE_FONT.'<b>'.StripString(htmlentities($UrlEntry[$i][1])).'</b></font></td>'."\n";
		echo '<td bgcolor="'.$DOCUMENT->ROW_LIGHT.'">'.$DOCUMENT->TABLE_FONT.'<b>'.htmlentities($UrlEntry[$i][0]).'</b></font></td>'."\n";
		echo '</tr>'."\n";
		echo '<tr>'."\n";
		echo '<td bgcolor="'.$DOCUMENT->ROW_LIGHT.'">'.$DOCUMENT->TABLE_FONT.'&nbsp;</font></td>'."\n";
		echo '<td bgcolor="'.$DOCUMENT->ROW_LIGHT.'">'.$DOCUMENT->TABLE_FONT.'<a href="'.htmlentities($UrlEntry[$i][3]).'" target="new">'.StripString(htmlentities($UrlEntry[$i][3])).'</a></font></td>'."\n";
		echo '<td bgcolor="'.$DOCUMENT->ROW_LIGHT.'">'.$DOCUMENT->TABLE_FONT.htmlentities($UrlEntry[$i][2]).'</font></td>'."\n";
		echo '</tr>'."\n";
		$i++;
		while($i < $UrlCounter AND trim($UrlEntry[$i][1]) == trim($UrlEntry[$i-1][1])) {
			if($m > 30) {
				$i++;
				continue;
			}
			echo '<tr>'."\n";
			echo '<td bgcolor="'.$DOCUMENT->ROW_LIGHT.'">'.$DOCUMENT->TABLE_FONT.'&nbsp;</font></td>'."\n";
			echo '<td bgcolor="'.$DOCUMENT->ROW_LIGHT.'">'.$DOCUMENT->TABLE_FONT.'<a href="'.htmlentities($UrlEntry[$i][3]).'" target="new">'.StripString(htmlentities($UrlEntry[$i][3])).'</a></font></td>'."\n";
			echo '<td bgcolor="'.$DOCUMENT->ROW_LIGHT.'">'.$DOCUMENT->TABLE_FONT.htmlentities($UrlEntry[$i][2]).'</font></td>'."\n";
			echo '</tr>'."\n";
			$m++;
			$i++;
		}
	}
	echo '</table>'."\n";
	WriteButtonLineForm($StatFile,'referrer');
	DrawBottomLine($title);
	DrawFooter();
}

function drawAll($StatFile) {
	global $DEFAULTS, $DOCUMENT, $MESSAGES;

	$title = $MESSAGES[130];
	DrawHeader($title);
	DrawTopLine($title);

	$fp = @fopen($DEFAULTS->StatDir.'/'.$StatFile.'.sum', 'r');
	if(!$fp) {
		echo($MESSAGES[168]);
	} else {
		$HitsDay     = trim(fgets($fp, 1024));
		$HitsUser    = trim(fgets($fp, 1024));
		$UserDay     = trim(fgets($fp, 1024));
		$VistisUser  = trim(fgets($fp, 1024));
		$HitsSum     = trim(fgets($fp, 1024));
		$dummy       = trim(fgets($fp, 1024));
		$StartDate   = trim(fgets($fp, 1024));
		$EndDate     = trim(fgets($fp, 1024));
		$Scope       = (floor(($EndDate - $StartDate) / 864)) / 100;

		$UniqueUsers = trim(fgets($fp, 1024));
		$IPSum       = trim(fgets($fp, 1024));
		fclose($fp);

		if($StatFile=='all') {
			$ShowScope = $MESSAGES[152].' - '.$MESSAGES[166];
		} else {
			$ShowScope = $MESSAGES[178].' - '.$StatFile;
		}
		echo '<p>'.$DOCUMENT->LEAD_FONT.$ShowScope.':</font></p>'."\n";
		echo '<table border="0" cellspacing="2" cellpadding="5" width="600">'."\n";
		WriteRow($MESSAGES[155], date("d.m.Y - H:i:s", $StartDate));// Startdatum
		WriteRow($MESSAGES[156], date("d.m.Y - H:i:s", $EndDate));  // Enddatum
		WriteRow($MESSAGES[157], $Scope);      // Zeitraum in Tagen
		WriteRow($MESSAGES[158], $HitsSum);    // Seitenaufrufe gesamt
		WriteRow($MESSAGES[159], $IPSum);      // Summe aller IP-Adressen
		WriteRow($MESSAGES[160], $UniqueUsers);// User gesamt
		WriteRow($MESSAGES[161], $UserDay);    // User pro Tag
		WriteRow($MESSAGES[162], $VistisUser); // Besuche pro User
		WriteRow($MESSAGES[163], $HitsUser);   // Seitenaufrufe pro User
		WriteRow($MESSAGES[164], $HitsDay);    // Seitenaufrufe pro Tag
		echo '</table>'."\n";
		if ($StatFile == 'all') {
			WriteButtonLineForm($StatFile,'all');
		} else {
			WriteButtonLineForm($StatFile,'daily');
		}
	}
	DrawBottomLine($MESSAGES[130]);
	DrawFooter();
}

function WriteButtonLineForm($StatFile,$type) {
	global $DEFAULTS,$DOCUMENT,$MESSAGES;

	$ThisMonth = date("Y/m", time());
	echo $DOCUMENT->TABLE_FONT;
	echo '<form action="'.$DEFAULTS->SELF.'" method="post">'."\n";
	echo '<input type="hidden" name="seceret" value="'.$DEFAULTS->PASS.'">'."\n";
	echo '<input type="hidden" name="phpcmsaction" value="stat">'."\n";
	echo '<input type="hidden" name="phpcmsStatFile" value="'.$StatFile.'">'."\n";
	echo '<input type="hidden" name="phpcmsThisMonth" value="'.$ThisMonth.'">'."\n";
	if ($type != 'referrer') {
		echo '<input type="submit" name="action" value="'.$MESSAGES[173].'">'."\n";
	}
	if ($type != 'all') {
		echo '<input type="submit" name="action" value="'.$MESSAGES[175].'">'."\n";
	}
	echo '<input type="submit" name="action" value="'.$MESSAGES[174].'">'."\n";
	echo '</form><br />'."\n";

	echo '</font></font>'."\n";
}

function GetMonthHits($Year, $Month, $Days, $Type) {
	if(!isset($Days['count'])) {
		return;
	}
	$i = 1;
	while(checkdate($Month, $i, $Year) == TRUE AND $i < 32) {
		$ReturnArray[$i] = 0;
		$Counter = count($Days['count']);
		for($j = 0; $j < $Counter; $j++) {
			if(trim($Days['count'][$j]) != $i) {
				continue;
			}
			$fp = fopen($Days['file'][$j], 'r');
			$HitsDay     = trim(fgets($fp, 1024));
			$HitsUser    = trim(fgets($fp, 1024));
			$UserDay     = trim(fgets($fp, 1024));
			$VistisUser  = trim(fgets($fp, 1024));
			$HitsSum     = trim(fgets($fp, 1024));
			$dummy       = trim(fgets($fp, 1024));
			$StartDate   = trim(fgets($fp, 1024));
			$EndDate     = trim(fgets($fp, 1024));
			$Scope       = (floor(($EndDate - $StartDate) / 864)) / 100;
			$UniqueUsers = trim(fgets($fp, 1024));
			$IPSum       = trim(fgets($fp, 1024));
			fclose($fp);
			$ReturnArray[$i] = ${$Type};
		}
		$i++;
	}
	return $ReturnArray;
}

function DoStats() {
	global $DEFAULTS, $DOCUMENT, $MESSAGES;
	$title = $MESSAGES[165];
	DrawHeader($title);
	DrawTopLine($title);
	echo '</td></tr>';
	echo '</table>'."\n";
	flush();

	MakeFiles();
	DrawActionRow('0');

	echo $DOCUMENT->TABLE_FONT.'<b>'.$MESSAGES[148].'!</b></font><br />'."\n";
	echo '<form action="'.$DEFAULTS->SELF.'" method="post">'."\n";
	echo '<input type="hidden" name="seceret" value="'.$DEFAULTS->PASS.'">'."\n";
	echo '<input type="hidden" name="phpcmsaction" value="stat">'."\n";
	echo '<input type="submit" name="submit" value="'.$MESSAGES[149].'">'."\n";
	echo '</form></font></font>'."\n";

	echo '<table border="0" cellspacing="3" cellpadding="3" width="600"><tr><td bgcolor="'.$DOCUMENT->DARK_COLOR.'">';
	echo '<tr><td>';
	DrawBottomLine($title);
	DrawFooter();
}

function DrawActionRow($line) {
	global $DEFAULTS, $DOCUMENT;
	static $CountEntry;

	if(!isset($CountEntry)) {
		$CountEntry = 0;
	} else {
		echo '<script language="JAVASCRIPT">'."\n";
		echo '<!--//'."\n";
		echo 'document.flash'.($CountEntry).'.src = \''.$DEFAULTS->SCRIPT_PATH.'/gif/lighter_off.gif\';'."\n";
		echo '//-->';
		echo '</script>'."\n\n";
		flush();
	}
	if($line == '0') {
		return;
	}
	$CountEntry = $CountEntry + 1;

	echo '<table border="0" cellspacing="0" cellpadding="2" width="600">'."\n";
	echo '<tr>'."\n";
	echo '<td width="14"><img src="'.$DEFAULTS->SCRIPT_PATH.'/gif/lighter_on.gif" name="flash'.$CountEntry.'" width="14" height="14" BORDER="0"></td>'."\n";
	echo '<td align="left" width="580">'.$DOCUMENT->TABLE_FONT.$line.'</font></td>'."\n";
	echo '</tr>'."\n";
	echo '</table>'."\n";

	flush();
}

function ShowMonth($Year, $Month, $SubmitType) {
	global $DEFAULTS, $DOCUMENT, $MESSAGES;

	switch($SubmitType) {
		case $MESSAGES[170]:
			$Type = 'HitsSum';
			break;

		case $MESSAGES[172]:
			$Type = 'IPSum';
			break;

		case $MESSAGES[171]:
			$Type = 'UniqueUsers';
			break;
	}
	$title = $MESSAGES[130];
	DrawHeader($title);
	DrawTopLine($title);

	$nextMonth = $Month + 1;
	$nextYear = $Year;
	if($nextMonth == 13) {
		$nextYear++;
		$nextMonth = 1;
	}
	$nextMonth = sprintf("%'02s", $nextMonth);
	$prevMonth = $Month - 1;
	$prevYear = $Year;
	if($prevMonth == 0) {
		$prevMonth = 12;
		$prevYear--;
	}
	$prevMonth = sprintf("%'02s", $prevMonth);

	echo '<p>'.$DOCUMENT->LEAD_FONT.$SubmitType.' - '.$Month.'/'.$Year.':</font></p>'."\n";
	echo $DOCUMENT->TABLE_FONT;
	echo '<form action="'.$DEFAULTS->SELF.'" method="post">'."\n";
	echo '<input type="hidden" name="seceret" value="'.$DEFAULTS->PASS.'">'."\n";
	echo '<input type="hidden" name="phpcmsaction" value="stat">'."\n";
	echo '<input type="hidden" name="action" value="MONTH">'."\n";
	echo '<input type="hidden" name="phpcmsType" value="'.$SubmitType.'">'."\n";
	echo '<input type="submit" name="phpcmsGoto" value="'.$prevYear.'.'.$prevMonth.'">'."\n";
	echo '&nbsp;&nbsp;';
	echo '<input type="submit" name="nav" value="'.$MESSAGES[176].'">'."\n";
	echo '&nbsp;&nbsp;';
	echo '<input type="submit" name="phpcmsGoto" value="'.$nextYear.'.'.$nextMonth.'">'."\n";
	echo '</form></font></font>'."\n";

	$ReadYearDir = $DEFAULTS->StatDir.'/'.$Year.'/'.$Month;

	if(file_exists($ReadYearDir)) {
		$d = dir($ReadYearDir);
		$Sumi = 0;

		while($entry = $d->read()) {
			if(stristr($entry, '.sum')) {
				$SumFile['file'][$Sumi]  = $ReadYearDir.'/'.$entry;
				$SumFile['count'][$Sumi] = substr(substr($SumFile['file'][$Sumi], -6), 0, 2);
				$Sumi++;
			}
		}
		$d->close();
	}
	if(isset($SumFile)) {
		$Hits = GetMonthHits($Year, $Month, $SumFile, $Type);
	}
	$Minimum = 1000;
	$Maximum = 1;
	for($i = 1; $i < count($Hits) + 1; $i++) {
		if($Hits[$i] > $Maximum) {
			$Maximum = $Hits[$i];
		}
		if($Hits[$i] < $Minimum) {
			$Minimum = $Hits[$i];
		}
	}
	$Teiler = 320 / $Maximum;
	$Multi = $Maximum / 400;
	$Monatszahl = count($Hits);
	if($Monatszahl == 0) {
		$Monatszahl = 1;
	}
	$Width = round(560 / $Monatszahl);
	$nix = $DEFAULTS->SCRIPT_PATH.'/gif/nix.gif';

	echo '<table border="1" cellspacing="0" cellpadding="0" width="600">'."\n";
	echo '<tr>'."\n";

	echo '<td>'."\n";
	echo '<table border="0" cellspacing="0" cellpadding="0">'."\n";

	echo '<tr><td><img src="'.$nix.'" width="1" height="100" border="0"></td>';
	echo '<td valign="top"><font face="Arial, Helvetica, Verdana" size="1">'.sprintf("%'05s", floor(400 * $Multi)).'</font></td></tr>'."\n";

	echo '<tr><td><img src="'.$nix.'" width="1" height="100" border="0"></td>';
	echo '<td valign="top"><font face="Arial, Helvetica, Verdana" size="1">'.sprintf("%'05s", floor(300 * $Multi)).'</font></td></tr>'."\n";

	echo '<tr><td><img src="'.$nix.'" width="1" height="100" border="0"></td>';
	echo '<td valign="top"><font face="Arial, Helvetica, Verdana" size="1">'.sprintf("%'05s", floor(200 * $Multi)).'</font></td></tr>'."\n";

	echo '<tr><td><img src="'.$nix.'" width="1" height="100" border="0"></td>';
	echo '<td valign="top"><font face="Arial, Helvetica, Verdana" size="1">'.sprintf("%'05s", floor(100 * $Multi)).'</font></td></tr>'."\n";

	echo '</table>'."\n";
	echo '</td>'."\n";

	for($i = 1; $i < count($Hits) + 1; $i++) {
		echo '<td align="center" valign="bottom">';
		$BottomHeight = floor($Teiler * $Hits[$i]);
		$TopHeight = 400 - $BottomHeight;
		echo '<table border="0" cellspacing="0" cellpadding="0">'."\n";
		echo '<tr><td bgcolor="#FFFFFF">';
		echo '<img src="'.$nix.'" width="'.$Width.'" height="'.$TopHeight.'" border="0">';
		echo '</td></tr>'."\n";
		if($BottomHeight > 0) {
			echo '<tr><td bgcolor="#DDEEDD">';
			WriteColumnForm($Year, $Month, $i);
			echo '<input type="image" src="'.$nix.'" width="'.$Width.'" ';
			echo 'height="'.$BottomHeight.'" border="0" alt="=> '.$Hits[$i].' <= '.$MESSAGES[177].' '.$Month.'/'.$i.'/'.$Year.'" ';
			echo 'onMouseOver="window.status=\''.$Hits[$i].'=> Click to see details on '.$Month.'/'.$i.'/'.$Year.' <=\';return true;" ';
			echo 'onMouseOut="window.status=\'\';return true" style="height:'.$BottomHeight.'px;width:'.$Width.'px">';
			echo '</form>'."\n";
			echo '</td></tr>'."\n";
		}
		echo '</table>'."\n";
		echo '<font face="Arial, Helvetica, Verdana" size="1">'.sprintf("%'02s", $i).'</font>'."\n";
		echo '</td>'."\n";
	}
	echo '</tr>'."\n";
	echo '</table>'."\n";

	echo $DOCUMENT->TABLE_FONT;

	echo '<form action="'.$DEFAULTS->SELF.'" method="post">'."\n";
	echo '<input type="hidden" name="seceret" value="'.$DEFAULTS->PASS.'">'."\n";
	echo '<input type="hidden" name="phpcmsaction" value="stat">'."\n";
	echo '<input type="hidden" name="phpcmsStatFile" value="'.$Year.'/'.$Month.'">'."\n";
	echo '<input type="hidden" name="action" value="MONTH">'."\n";
	echo '<input type="hidden" name="phpcmsGoto" value="'.$Year.'/'.$Month.'">'."\n";
	if ($Type != 'HitsSum') {
		echo '<input type="submit" name="phpcmsType" value="'.$MESSAGES[170].'">'."\n";
	}
	if ($Type != 'UniqueUsers') {
		echo '<input type="submit" name="phpcmsType" value="'.$MESSAGES[171].'">'."\n";
	}
	if ($Type != 'IPSum') {
		echo '<input type="submit" name="phpcmsType" value="'.$MESSAGES[172].'">'."\n";
	}
	echo '<input type="submit" name="action" value="'.$MESSAGES[175].'">'."\n";
	echo '</form></font></font>'."\n";

	DrawBottomLine($MESSAGES[130]);
	DrawFooter();
}

function WriteColumnForm($Year, $Month, $Day) {
	global $DEFAULTS;
	echo '<form action="'.$DEFAULTS->SELF.'" method="post">'."\n";
	echo '<input type="hidden" name="seceret" value="'.$DEFAULTS->PASS.'">'."\n";
	echo '<input type="hidden" name="phpcmsaction" value="stat">'."\n";
	echo '<input type="hidden" name="action" value="show detail">'."\n";
	echo '<input type="hidden" name="phpcmsStatFile" value="'.$Year.'/'.$Month.'/'.sprintf("%'02s", $Day).'">'."\n";
}

function makeMenu() {
	global $DEFAULTS, $DOCUMENT, $MESSAGES;

	$title = $MESSAGES[130];
	DrawHeader($title);
	DrawTopLine($title);
	echo '<p>'.$DOCUMENT->LEAD_FONT.$MESSAGES[141].'</font></p>'."\n";
	echo '<p>'.$DOCUMENT->TABLE_FONT;
	echo '<form method="post" action="'.$DEFAULTS->SELF.'">'."\n";
	echo '<input name="seceret" value="'.$DEFAULTS->PASS.'" type="hidden">'."\n";
	echo '<input name="phpcmsaction" value="stat" type="hidden">'."\n";
	echo '<input name="action" type="radio" value="all" checked style="background-color: '.$DOCUMENT->BACKGROUND_COLOR.';">'.$MESSAGES[142]."\n";
	echo '<br /><input name="action" type="radio" value="month" style="background-color: '.$DOCUMENT->BACKGROUND_COLOR.';">'.$MESSAGES[143]."\n";
	echo '<br /><input name="action" type="radio" value="import stats" style="background-color: '.$DOCUMENT->BACKGROUND_COLOR.';">'.$MESSAGES[144]."\n";
	echo '<br /><input name="action" type="radio" value="recreate" style="background-color: '.$DOCUMENT->BACKGROUND_COLOR.';">'.$MESSAGES[145]."\n";
	echo '<br /><input name="action" type="radio" value="config" style="background-color: '.$DOCUMENT->BACKGROUND_COLOR.';">'.$MESSAGES[136]."\n";
	echo '<br /><input type="submit" name="submit" value="'.$MESSAGES[131].'">'."\n";
	echo '</form>'."\n";
	echo '</p>'."\n";
	DrawBottomLine($title);
	DrawFooter();
}

function makeErrorPage() {
	global $DEFAULTS, $DOCUMENT, $MESSAGES;

	$title = $MESSAGES[130];
	DrawHeader($title);
	DrawTopLine($title);
	echo '<p>'.$DOCUMENT->TABLE_FONT.$MESSAGES[167].'</font></p>'."\n";
	DrawBottomLine($title);
	DrawFooter();
}


function KillDir($Directory, $Except) {
	$d = dir($Directory);
	$CountExcept = count($Except);

	while($entry = $d->read()) {
		$Doit = true;
		if($entry == '.' OR $entry == '..') {
			continue;
		}
		for($i = 0; $i < $CountExcept; $i++) {
			if(stristr($Except[$i], $entry)) {
				$Doit = false;
			}
		}
		if(!$Doit) {
			continue;
		}
		if(!is_dir($Directory.'/'.$entry)) {
			unlink($Directory.'/'.$entry);
			continue;
		}
		KillDir($Directory.'/'.$entry, $Except);
		rmdir($Directory.'/'.$entry);
	}
	$d->close();
	return;
}

function ReCreate() {
	// recreates stat-database from backup
	global $DEFAULTS, $DOCUMENT, $MESSAGES;

	$title = $MESSAGES[130];
	DrawHeader($title);
	DrawTopLine($title);
	echo '</td></tr></table><br />'."\n";
	flush();

	// delete current database
	$Except[0] = $DEFAULTS->STATS_BACKUP;
	$Except[1] = $DEFAULTS->STATS_CURRENT;

	DrawActionRow($MESSAGES[146]);
	KillDir($DEFAULTS->StatDir, $Except);

	// recreate stat-object
	DrawActionRow($MESSAGES[147]);

	$d = dir($DEFAULTS->StatBackupDir);

	while($entry = $d->read()) {
		if($entry == '.' OR $entry == '..' OR $entry == 'dummy.txt') {
			continue;
		}
		if(!isset($Stat)) {
			$Stat = new Stats($DEFAULTS->StatBackupDir.'/'.$entry);
		} else {
			$Stat->addFile($DEFAULTS->StatBackupDir.'/'.$entry);
		}
	}
	$d->close();

	if(isset($Stat)) {
		$Stat->sortStatFile();
		InitFiles($Stat);
		DrawActionRow('0');
	}

	echo $DOCUMENT->TABLE_FONT.'<b>'.$MESSAGES[148].'!</b></font><br />'."\n";
	echo '<form action="'.$DEFAULTS->SELF.'" method="post">'."\n";
	echo '<input type="hidden" name="seceret" value="'.$DEFAULTS->PASS.'">'."\n";
	echo '<input type="hidden" name="phpcmsaction" value="stat">'."\n";
	echo '<input type="submit" name="submit" value="'.$MESSAGES[149].'">'."\n";
	echo '</form></font></font>';

	echo '<table border="0" cellspacing="3" cellpadding="3" width="600"><tr><td bgcolor="'.$DOCUMENT->DARK_COLOR.'">'."\n";
	echo '<tr><td>'."\n";
	DrawBottomLine($title);
	DrawFooter();
}

function GetField($FieldName) {
	global $PARSER;

	$count = count($PARSER);
	for($i = 0; $i < $count; $i++) {
		if(stristr($PARSER[$i], $FieldName)) {
			$Value = substr($PARSER[$i], strpos($PARSER[$i], $FieldName) + strlen($FieldName));
			$Value = substr($Value, strpos($Value, '\'') + 1);
			$Value = substr($Value, 0, strpos($Value, '\''));
			return $Value;
		}
	}
	return false;
}

function WriteInputLine($Link, $Text, $FieldName, $DefaultName) {
	global $LastColor, $DEFAULTS, $DOCUMENT;

	if(isset($LastColor) AND $LastColor == $DOCUMENT->ROW_LIGHT) {
		$LastColor = $DOCUMENT->ROW_DARK;
	} else {
		$LastColor = $DOCUMENT->ROW_LIGHT;
	}
	$Field = GetField($DefaultName);

	echo '<tr bgcolor="'.$LastColor.'">'."\n";
	echo '<td valign="bottom" width="250">'.$DOCUMENT->TABLE_FONT;
	if(strlen($Link) != 0) {
		echo '<a href="'.$DEFAULTS->SCRIPT_PATH.'/help/options.'.$DEFAULTS->LANGUAGE.'.html#'.$Link.'">'.$Text.':</a>'."\n";
	} else {
		echo $Text.':';
	}
	echo '</font></td>'."\n";
	echo '<td valign="top" width="180">'.$DOCUMENT->TABLE_FONT;
	echo '<input type="text" name="'.$FieldName.'" value="'.$Field.'" size="10" maxsize="30" style="width:240px;">'."\n";
	echo '</font></td>'."\n";
	echo '</tr>'."\n";
}

function WriteChanges() {
	global $PARSER, $DEFAULTS;
	global $INIFILE;
	global $statsrefcount;
	global $statsrefignore;
	global $ipcount;
	global $ipignore;
	global $urlcount;

	$statsrefcount = trim($statsrefcount);
	$statsrefignore = trim(str_replace(',', ';', $statsrefignore));
	$ipcount  = trim($ipcount);
	$ipignore = trim($ipignore);
	$urlcount = trim($urlcount);

	$count = count($PARSER);
	$i = 0;
	while(!stristr($PARSER[$i], '$this->STATS_REFERER_COUNT')) {
		$i++;
	}
	$PartOne = substr($PARSER[$i], 0, strpos($PARSER[$i], '= \'') + 3);
	$PartTwo = substr($PARSER[$i], strrpos($PARSER[$i], '\';'));
	$PARSER[$i] = $PartOne.$statsrefcount.$PartTwo;
	$i++;

	$PartOne = substr($PARSER[$i], 0, strpos($PARSER[$i], '= \'') + 3);
	$PartTwo = substr($PARSER[$i], strrpos($PARSER[$i], '\';'));
	$PARSER[$i] = $PartOne.$statsrefignore.$PartTwo;
	$i++;

	$PartOne = substr($PARSER[$i], 0, strpos($PARSER[$i], '= \'') + 3);
	$PartTwo = substr($PARSER[$i], strrpos($PARSER[$i], '\';'));
	$PARSER[$i] = $PartOne.$ipcount.$PartTwo;
	$i++;

	$PartOne = substr($PARSER[$i], 0, strpos($PARSER[$i], '= \'') + 3);
	$PartTwo = substr($PARSER[$i], strrpos($PARSER[$i], '\';'));
	$PARSER[$i] = $PartOne.$ipignore.$PartTwo;
	$i++;

	$PartOne = substr($PARSER[$i], 0, strpos($PARSER[$i], '= \'') + 3);
	$PartTwo = substr($PARSER[$i], strrpos($PARSER[$i], '\';'));
	$PARSER[$i] = $PartOne.$urlcount.$PartTwo;
	$i++;

	$fp = fopen($INIFILE, 'w');
	if($fp) {
		for($i = 0; $i < $count; $i++) {
			fwrite($fp, $PARSER[$i]);
		}
		fclose($fp);
		return true;
	} else {
		return false;
	}
}

function Config() {
	global $conf_action, $DEFAULTS, $PARSER, $INIFILE, $MESSAGES, $DOCUMENT;

	$PARSER = @file($INIFILE);
	if(isset($conf_action) AND strtoupper($conf_action) == 'WRITE') {
		$done = WriteChanges();
		$done ? $status = $MESSAGES['OPTIONS']['SAVED'] : $status = $MESSAGES['OPTIONS']['FAILED'];
		$PARSER = @file($INIFILE);
	}
	DrawHeader($MESSAGES[136]);
	DrawTopLine($MESSAGES[136]);

	echo '<table border="0" cellspacing="2" cellpadding="2" width="100%">'."\n";
	echo '<form action="'.$DEFAULTS->SELF.'" method="post">'."\n";
	echo '<input type="hidden" name="action" value="config">'."\n";
	echo '<input type="hidden" name="phpcmsaction" value="stat">'."\n";
	echo '<input type="hidden" name="conf_action" value="write">'."\n";
	echo '<input type="hidden" name="seceret" value="'.$DEFAULTS->PASS.'">'."\n";

	WriteInputLine('', $MESSAGES[135], 'statsrefcount',  '$this->STATS_REFERER_COUNT');
	WriteInputLine('', $MESSAGES[137], 'statsrefignore', '$this->STATS_REFERER_IGNORE');
	WriteInputLine('', $MESSAGES[138], 'ipcount',        '$this->STATS_IP_COUNT');
	WriteInputLine('', $MESSAGES[139], 'ipignore',       '$this->STATS_IP_IGNORE');
	WriteInputLine('', $MESSAGES[140], 'urlcount',       '$this->STATS_URL_COUNT');

	echo '<tr bgcolor="'.$DOCUMENT->ROW_LIGHT.'">'."\n";
	echo '<td valign="bottom">'.$DOCUMENT->TABLE_FONT.'&nbsp;</font></td>'."\n";
	echo '<td valign="bottom">'.$DOCUMENT->TABLE_FONT.'<input type="submit" name="submit" value="'.$MESSAGES[46].'"></font></td>'."\n";
	echo '</tr>'."\n";
	if($status) {
		echo '<tr bgcolor="'.$DOCUMENT->ROW_LIGHT.'"><td valign="bottom" colspan="2">'.$DOCUMENT->TABLE_FONT.$status.'</font></td></tr>';
	}
	echo '</form>'."\n";
	echo '</table>'."\n";

	DrawBottomLine($MESSAGES[136]);
	DrawFooter();
}

switch(strtoupper($action)) {
	case 'CONFIG':
		Config();
		break;

	case 'RECREATE':
		set_time_limit(0);
		ReCreate();
		break;

	case 'IMPORT STATS':
		set_time_limit(0);
		DoStats();
		break;

	case strtoupper($MESSAGES[173]):
		DrawUrlReferer($GLOBALS["phpcmsStatFile"]);
		break;

	case 'SHOW DETAIL':
		drawAll($GLOBALS["phpcmsStatFile"]);
		break;

	case 'SHOW CHART':
		if($GLOBALS["phpcmsStatFile"] == 'all') {
			drawAll('all');
		} else {
			$Year  = substr($GLOBALS["phpcmsStatFile"], 0, 4);
			$Month = substr($GLOBALS["phpcmsStatFile"], 5, 2);
			ShowMonth($Year, $Month);
		}
		break;

	case strtoupper($MESSAGES[174]):
		$Year  = substr($GLOBALS["phpcmsThisMonth"], 0, 4);
		$Month = substr($GLOBALS["phpcmsThisMonth"], 5, 2);
		ShowMonth($Year, $Month, $MESSAGES[170]);
		break;

	case 'MONTH':
		if(!isset($GLOBALS["phpcmsGoto"])) {
			ShowMonth(date("Y", time()), date("m", time()), $MESSAGES[170]);
		} else {
			$Year  = substr($GLOBALS["phpcmsGoto"], 0, 4);
			$Month = substr($GLOBALS["phpcmsGoto"], 5, 2);
			ShowMonth($Year, $Month, $GLOBALS["phpcmsType"]);
		}
		break;

	case strtoupper($MESSAGES[175]):
		drawAll('all');
		break;

	case 'ALL':
		drawAll('all');
		break;

	default:
		if($DEFAULTS->STATS == 'on') {
			makeMenu();
		} else {
			makeErrorPage();
		}
	exit;
}

?>
