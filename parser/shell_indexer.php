<?php
/* $Id: shell_indexer.php,v 1.1.2.9 2006/06/18 18:06:10 ignatius0815 Exp $ */
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
   |    Thilo Wagner (ignatius)
   +----------------------------------------------------------------------+
*/

/*
 * Shell indexer for the phpCMS full text search
 *
 * Usage:
 * php shell_indexer.php profilename
 * OR
 * php shell_indexer.php profilenumber
 *
 * Note: You MUST define a search profile with the HTTP indexer GUI first!
 *
 * Configuration:
 * - The default values should be fine, adjust them only when necessary
 * - $DOCUMENT_ROOT: absolute path to root directory of the web server
 * - PHPCMS_INCLUDEPATH: path to include directory (the one with many
 *   .php files)
 *
 */

// protect include files from direct execution
define('PHPCMS_RUNNING',1);

// settings
$DOCUMENT_ROOT = dirname(__FILE__).'/..';
define('PHPCMS_INCLUDEPATH', dirname(__FILE__).'/include/');

/************************************************/

// check the PHP version
$php_version = explode('.', phpversion());
if(($php_version[0] == 4 AND $php_version[1] == 0 AND $php_version[2] < 1) OR ($php_version[0] < 4)) {
	die('<b>This is phpCMS 1.2.0.</b><br /><br />The Shell-Indexer no longer supports PHP 4.0.1 or older!<br /><br />Please update to the latest version of PHP 4...');
}

set_time_limit(0);
$formdata = Array();

$PHPCMS_INDEXER_SAVE_FILE_NAME 	= 'defaults_indexer.php';
include(PHPCMS_INCLUDEPATH.'class.lib_indexer_universal_phpcms.php');

// get the name of the default language from the default.php file
// without including it (not perfect but didn't had any better idea)
$defaults_file = @file(PHPCMS_INCLUDEPATH.'default.php');
$found = false;
foreach ($defaults_file as $line) {
	$line = strtolower(trim($line));
	if (substr($line,0,15) == '$this->language') {
		list ($temp,$language_file) = split('=',$line);
		$language_file = str_replace("'" ,'',$language_file);
		$language_file = str_replace(";" ,'',$language_file);
		$language_file = trim($language_file);
		$language_file = PHPCMS_INCLUDEPATH . 'language.' . $language_file;
		$found = true;
		break;
	}
}
if ($found) {
	include($language_file);
} else {
	die("Error in shell indexer: Could not include languagefile $language_file\n");
}
unset($found);
unset($defaults_file);
unset($line);
unset($temp);
unset($language_file);

/************************************************/

// class session needed for compatibility
class session {
	function session() {
		$this->vars = Array();
	}
}
$session = new session;

// load cookie
if(!isset($session->vars['cookie'])) {
	$session->vars['cookie'] = new cookie_container;
}

/************************************************/

$start_time = time();

// initialize
unset_all();

// load profiles
$profiles = read_profiles();

// set path variables
$session->vars['script_path'] = dirname(__FILE__);
$session->vars['temp_path'] = str_replace('\\','/',$session->vars['script_path']).'/temp';
$session->vars['script_name'] = basename(__FILE__);

// get parameter
$profile = $argv[1];
if(is_numeric($profile)) {
	$profilenames = array_keys($profiles);
	$session->vars['profilename'] = $profilenames[$profile-1];
}
else {
	$session->vars['profilename'] = $profile;
}

if (!isset($profiles[$session->vars['profilename']])) {
	echo "============================================================\n";
	echo "Fehler: Profil '".$session->vars['profilename']."' ist nicht vorhanden!\n";
	echo "Folgende Profile sind vorhanden (Auswahl durch\n";
	echo "Angabe des Namens oder der Nummer als Parameter):\n";
	// profile list with numbers
	$profilenames = array_keys($profiles);
	for($i=0;$i<count($profilenames);$i++) {
		echo sprintf("%2d: ",$i+1).$profilenames[$i]."\n";
	} // end for
	echo "============================================================\n";
	exit;
}

// delete temp directory
$d = dir($session->vars['temp_path']);
while($entry = $d->read()) {
	if ($entry == '.' OR $entry == '..' OR $entry == '.htaccess' OR $entry == 'index.html') {
		continue;
	}
	if(is_file($session->vars['temp_path'].'/'.$entry)) {
		unlink ($session->vars['temp_path'].'/'.$entry);
	}
} // end while
$d->close();

// load profile into session
$session->vars['startadress'] 				= $profiles[$session->vars['profilename']]['host'][0]; //volle Startadresse
$session->vars['url_adress'][0]				= $session->vars['startadress'];
$session->vars['host'] 						= $profiles[$session->vars['profilename']]['host'];
$session->vars['host'][0] 					= substr($session->vars['startadress'],0,strpos($session->vars['startadress'],'/')).'/'; //nur Server der Startadresse
$session->vars['exclude'] 					= $profiles[$session->vars['profilename']]['exklude'];
$session->vars['include'] 					= $profiles[$session->vars['profilename']]['include'];
$session->vars['robots'] 					= $profiles[$session->vars['profilename']]['robots'];
$session->vars['meta'] 						= $profiles[$session->vars['profilename']]['meta'];
$session->vars['savedata'] 					= $profiles[$session->vars['profilename']]['savedata'];
$session->vars['gzip'] 						= $profiles[$session->vars['profilename']]['gzip'];
$session->vars['noextensions']				= $profiles[$session->vars['profilename']]['noextensions'];
$session->vars['stopword']					= $profiles[$session->vars['profilename']]['stopword'];
$session->vars['wordlength']				= $profiles[$session->vars['profilename']]['wordlength'];
$session->vars['buffer']					= $profiles[$session->vars['profilename']]['buffer'];
$session->vars['description']				= $profiles[$session->vars['profilename']]['description'];
$session->vars['meta_desc']					= $profiles[$session->vars['profilename']]['meta_desc'];
$session->vars['url_pattern']				= $profiles[$session->vars['profilename']]['url_pattern'];
$session->vars['url_replacement']			= $profiles[$session->vars['profilename']]['url_replacement'];
$session->vars['url_failure'] 				= Array();
$session->vars['url_to_spider'] 			= Array();
$session->vars['url_to_spider'][0] 			= 0;
$session->vars['url_have_spidered']			= Array();
$session->vars['url_name']					= Array();
$session->vars['url_have_indexed']  		= Array();
$session->vars['url_have_indexed_name']  	= Array();
$session->vars['progress']					= 0;
$session->vars['reference']					= Array();
if (!isset($session->vars['exclude']))	$session->vars['exclude'] = Array();

$session->vars['cookie'] = new cookie_container;

// get robots
get_robots();

// start spider
echo '============================================================'."\r\n";
echo " Starte Spider (Profil '".$session->vars['profilename']."')\r\n";
echo '============================================================'."\r\n";
flush();
@ob_end_flush();

// spider until the end
while (isset($session->vars['url_to_spider']) AND count($session->vars['url_to_spider']) > 0) {

	// $body is a void variable that gets filled with the page contents
	while(($urls = get_urls($body)) === FALSE) {
		; // do nothing
	}

	// write page (possibly redundant, the HTTP indexer with the GUI does the same)
	$body_length = strlen($body);
	if( $body_length != 0 ) {

		$index = count($session->vars['url_have_spidered']);
		$session->vars['url_have_spidered'][$index] = $session->vars['url_to_spider'][0];
		$current_page								= $session->vars['url_to_spider'][0];
		$session->vars['url_name'][$index] 			= md5(uniqid(microtime(),1)).'.htm';

		$fp = fopen($session->vars['temp_path'].'/'.$session->vars['url_name'][$index], 'wb+');
		fwrite($fp,$body,$body_length);
		fclose($fp);
	}

	// delete first array entry (url we spidered)
	array_shift ($session->vars['url_to_spider']);

	if (is_array($urls) AND count($urls) > 0 ) {
		reset ( $urls );
		$urls = array_unique($urls);
		reset ( $urls );
		foreach ($urls as $alink) {
			$alink = trim ( $alink );
			if (!in_array($alink,$session->vars['url_adress'],TRUE) AND $alink != '' ) {
				$index = count($session->vars['url_adress']);
				$session->vars['url_adress'][$index] 		= $alink;
				$session->vars['url_to_spider'][] 			= $index;
				$session->vars['reference'][$current_page][]= $index;
			}
			elseif(trim($alink)!= '') {
				$index = array_search($alink,$session->vars['url_adress']);
				$session->vars['reference'][$current_page][]= $index;
			}
		}
	} // end if

	// status message
	$message = 'Sp:'.sprintf('%04d',count($session->vars['url_have_spidered'])).'/'.sprintf('%04d',(count($session->vars['url_to_spider'])+count($session->vars['url_have_spidered']))).' - '.'http://'.$session->vars['url_adress'][$current_page]."\n";
	if (strlen($message) > 79) {
		$message1 = substr($message,0,79)."\n";
		$message2 = '               '.substr($message,79);
		$message  = $message1.$message2;
	}
	echo $message;

	flush();
	@ob_end_flush();
} // end while

# Fehler schreiben
if (isset($session->vars['url_failure'][0])) {

	$fp = fopen($session->vars['temp_path'].'/'.'errors.txt','ab+');

	foreach($session->vars['url_failure'] as $failure) {
		$message = '============================================================'."\r\n";
		$message.= ' http://'.$session->vars['url_adress'][$failure]."\r\n";
		$message.= '============================================================'."\r\n";

		reset($session->vars['reference']);
		while(list($adress, $value) = each($session->vars['reference'])) {
			if (in_array($failure,$value,TRUE)) {
				$message.= "\t".'http://'.$session->vars['url_adress'][$adress]."\r\n";
			}
		}
		fwrite($fp,$message, strlen($message));
	} // end foreach

	fclose($fp);

} // end if

unset($session->vars['reference']);

// start indexer
echo '============================================================'."\r\n";
echo ' Starte Indexer.'."\r\n";
echo '============================================================'."\r\n";
flush();
@ob_end_flush();

# Stopwortdatei einlesen und maximale Länge feststellen
$STOP = file ($DOCUMENT_ROOT.$session->vars['stopword']);
$STOP_MAX = 0;
$STOP_COUNT = count($STOP);
for ($i=0; $i<$STOP_COUNT; $i++) {
	$STOP[$i] = trim($STOP[$i]);
	if (strlen($STOP[$i]) > $STOP_MAX) {
		$STOP_MAX = strlen($STOP[$i]);
	}
}
unset ($STOP_COUNT);
$STOP = array_flip($STOP);

# File-DB initialisieren
$actual_entry = 0;

# Intialwert für Indexer setzen
$session->vars['to_index'] = count($session->vars['url_have_spidered']);

# Indexer starten
while (count($session->vars['url_have_spidered']) != 0) {
	$index = count($session->vars['url_have_indexed']);

	# Zu indizierende Seite laden
	$index_page = file($session->vars['temp_path'].'/'.$session->vars['url_name'][$index]);
	$index_page = implode('',$index_page);
	$index_page_name = $session->vars['url_adress'][$session->vars['url_have_spidered'][0]];

	# Statusmeldung ausgeben
	$message = 'Ix:'.sprintf('%04d',$index).'/'.sprintf('%04d',$session->vars['to_index']).' - '.'http://'.$session->vars['url_adress'][$index]."\n";
	if (strlen($message) > 79) {
		$message1 = substr($message,0,79)."\n";
		$message2 = '               '.substr($message,79);
		$message = $message1.$message2;
	}
	echo $message;
	flush();
	@ob_end_flush();

	# Seite indizieren
	$file_db[] = index_page($index_page, $index_page_name, $STOP, $STOP_MAX, $actual_entry);
	$actual_entry++;

	# Seite aus dem Index-Cache entfernen (noch zu schreiben)
	unlink($session->vars['temp_path'].'/'.$session->vars['url_name'][$index]);

	# Zu indizierende Seiten reduzieren
	array_shift ($session->vars['url_have_spidered']);

	# Indizierte Seite zum Ziel-Array hinzufügen
	$session->vars['url_have_indexed'][$index] = array_search($index_page_name,$session->vars['url_adress']);
} // end while

# File-DB schreiben
$fp = fopen($session->vars['temp_path'].'/'.'files.db','wb+');
$file_entry = implode('',$file_db);
$file_entry = substr($file_entry,0,-1);
fputs ($fp, $file_entry, strlen($file_entry));
fclose ($fp);
unset($file_db);

# Startwert für Merger setzen.
$session->vars['start'] = 0;
$session->vars['bytes_to_sort'] = filesize($session->vars['temp_path'].'/words.tmp');

# Merger erste Stufe starten
echo '============================================================'."\r\n";
echo ' Starte Merger Stufe 1.'."\r\n";
echo '============================================================'."\r\n";
flush();
@ob_end_flush();

if (file_exists($session->vars['temp_path'].'/'.'words.tmp')) {
	$TempWords = file($session->vars['temp_path'].'/'.'words.tmp');
}
else {
	$TempWords = Array();
}

$index = count($TempWords);
$word_files = Array();

# Wörter sortieren
for ($i=0; $i<$index; $i++) {
	list($word, $file) = explode('#',$TempWords[$i]);
	$word_len = strlen(trim($word));
	if($word_len == 0) {
		continue;
	}

	if($word_len > 0) {
		$word_files[$word_len][] = $TempWords[$i];
	}
} // end for

$session->vars['word_len_files'] = 0;
reset($word_files);
while (list($k, $word_ar) = each($word_files)) {
	$session->vars['word_len_files']++;
	$fp = fopen ($session->vars['temp_path'].'/'.'t'.$k.'.db', 'ab+' );
	$to_write = implode('',$word_ar);
	fputs ($fp, $to_write, strlen($to_write));
	fclose ($fp);
}

$session->vars['current_word_len'] = 0;

# Merger zweite Stufe starten
echo '============================================================'."\r\n";
echo ' Starte Merger Stufe 2.'."\r\n";
echo '============================================================'."\r\n";
flush();
@ob_end_flush();

# öffne directory,
$dire = $session->vars['temp_path'].'/';
$found = FALSE;

$d = dir ( $dire );
while ( $entry = $d->read() ) {
	if ( substr ( $entry, 0, 1 ) == 't' ) {
		$current_file = $session->vars['temp_path'].'/'.$entry;
		$found = TRUE;
		break;
	}
}

# Solange Wortdateien gefunden werden
while ($found === TRUE) {
	# Statusmeldung
	echo 'Wortlaenge: '.substr ($entry, 1, strrpos ( $entry, '.' )-1).' Zeichen'."\r\n";
	flush();
	@ob_end_flush();
	$TempArray = file ( $current_file );
	$index = 0;
	unset($WordArray);
	unset($DataArray);
	unset($IndexArray);


	for ($i=0; $i<count($TempArray); $i++) {
		$TempArray[$i] = trim($TempArray[$i]);
		if (strlen($TempArray[$i]) < 1) continue;

		list ( $word, $seite, $anzahl ) = explode ( '#', $TempArray[$i] );

		if (isset($WordArray[$word])) {
			$DataArray[$WordArray[$word]] = $DataArray[$WordArray[$word]].'+'.$seite.'*'.$anzahl;
		}
		else {
			$WordArray[$word] = $index;
			$IndexArray[$index] = $word;
			$DataArray[$index] = $seite.'*'.$anzahl;
			$index ++;
		}
	} // end for

	$output_file = $session->vars['temp_path'].'/'.'words.db';

	$fp = fopen ($output_file, 'ab+');
	$to_write = implode("\n",$IndexArray);
	$to_write.="\n";
	fputs ($fp, $to_write, strlen($to_write));
	fclose ($fp);

	$output_file = $session->vars['temp_path'].'/'.'data.db';

	$fp = fopen ($output_file, 'ab+');
	$to_write = implode("\n",$DataArray);
	$to_write.="\n";
	fputs ($fp, $to_write, strlen($to_write));
	fclose ($fp);

	unlink ($current_file);

	# Weitere Dateien gefunden?
	$found = FALSE;
	$d = dir ( $dire );
	while ( $entry = $d->read() ) {
		if ( substr ( $entry, 0, 1 ) == 't' ) {
			$current_file = $session->vars['temp_path'].'/'.$entry;
			$found = TRUE;
			break;
		}
	}
} // end while

# GZIP an
if ($session->vars['gzip'] == '1' AND extension_loaded ('zlib')) {

	$WordIndex = file($session->vars['temp_path'].'/'.'words.db');
	$WordToWrite = implode("",$WordIndex);
	$gp1 = gzopen($session->vars['temp_path'].'/'.'words.gz', 'wb');
	gzwrite ($gp1,$WordToWrite);
	gzclose ($gp1);
	unlink ($session->vars['temp_path'].'/'.'words.db');

	$FileDB = file ($session->vars['temp_path'].'/'.'files.db');
	$FileToWrite = implode("",$FileDB);
	$gp2 = gzopen($session->vars['temp_path'].'/'.'files.gz', 'wb');
	gzwrite ($gp2,$FileToWrite);
	gzclose ($gp2);
	unlink ($session->vars['temp_path'].'/'.'files.db');

	$DataArray = file ($session->vars['temp_path'].'/'.'data.db');
	$DataToWrite = implode("",$DataArray);
	$gp3 = gzopen($session->vars['temp_path'].'/'.'data.gz', 'wb');
	gzwrite ($gp3,$DataToWrite);
	gzclose ($gp3);
	unlink ($session->vars['temp_path'].'/'.'data.db');

	unlink ($session->vars['temp_path'].'/'.'words.tmp');
}
else {
	unlink ($session->vars['temp_path'].'/'.'words.tmp');
}

# Dateien Schreiben
echo '============================================================'."\r\n";
echo ' Dateien Schreiben.'."\r\n";
echo '============================================================'."\r\n";
flush();
@ob_end_flush();

$write_to = $DOCUMENT_ROOT.$session->vars['savedata'];

# Dateien kopieren
if ($session->vars['gzip'] == '1' AND extension_loaded ('zlib')) {
	$result = copy ($session->vars['temp_path'].'/'.'words.gz',$write_to.'/words.gz');
	if ($result === TRUE) {
		unlink($session->vars['temp_path'].'/'.'words.gz');
	}

	$result = copy ($session->vars['temp_path'].'/'.'files.gz',$write_to.'/files.gz');
	if ($result === TRUE) {
		unlink($session->vars['temp_path'].'/'.'files.gz');
	}

	$result = copy ($session->vars['temp_path'].'/'.'data.gz',$write_to.'/data.gz');
	if ($result === TRUE) {
		unlink($session->vars['temp_path'].'/'.'data.gz');
	}
}
else {
	$result = copy ($session->vars['temp_path'].'/'.'words.db',$write_to.'/words.db');
	if ($result === TRUE) {
		unlink($session->vars['temp_path'].'/'.'words.db');
	}

	$result = copy ($session->vars['temp_path'].'/'.'files.db',$write_to.'/files.db');
	if ($result === TRUE) {
		unlink($session->vars['temp_path'].'/'.'files.db');
	}

	$result = copy ($session->vars['temp_path'].'/'.'data.db',$write_to.'/data.db');
	if ($result === TRUE) {
		unlink($session->vars['temp_path'].'/'.'data.db');
	}
}

$result = FALSE;
if (file_exists($session->vars['temp_path'].'/'.'errors.txt')) {
	$result = copy ($session->vars['temp_path'].'/'.'errors.txt',$write_to.'/errors.txt');
}
if ($result === TRUE) {
	unlink($session->vars['temp_path'].'/'.'errors.txt');
}

# Fertig
echo '============================================================'."\r\n";
echo ' Indizieren beendet.'."\r\n";
echo '============================================================'."\r\n";
echo 'Gesamtdauer: '.sprintf ("%d", time()- $start_time);
flush();
@ob_end_flush();

?>
