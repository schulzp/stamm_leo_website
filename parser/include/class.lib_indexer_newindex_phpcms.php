<?php
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
   |    Henning Poerschke (hpoe)
   |    Markus Richert (e157m369)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

########################################################################
# Cookie laden
########################################################################
if(!isset($session->vars['cookie'])) {
	$session->vars['cookie'] = new cookie_container;
}

########################################################################
# Startet einen neuen Indiziervorgang
########################################################################

function start_create() {

	global $session, $formdata, $PHPCMS_INDEXER_TEMP_SAVE_PATH, $MESSAGES;

	// initialize
	unset_all();

	// delete temp directory
	$d = dir($PHPCMS_INDEXER_TEMP_SAVE_PATH);
	while($entry = $d->read()) {
		if ($entry == '.' OR $entry == '..' OR $entry == '.htaccess') {
			continue;
		}
		if(is_file($PHPCMS_INDEXER_TEMP_SAVE_PATH.$entry)) {
			unlink ($PHPCMS_INDEXER_TEMP_SAVE_PATH.$entry);
		}
	}
	$d->close();

	# Profil laden und anzeigen
	$profiles = read_profiles();

	# Gewähltes Profil in die Session laden
	$session->vars['startadress'] 			= $profiles[$formdata->profilname]['host'][0]; //volle Startadresse
	$session->vars['url_adress'][0]			= $session->vars['startadress'];
	$session->vars['host'] 					= $profiles[$formdata->profilname]['host'];
	$session->vars['host'][0] 				= substr($session->vars['startadress'],0,strpos($session->vars['startadress'],'/')).'/'; //nur Server der Startadresse
	$session->vars['exclude'] 				= $profiles[$formdata->profilname]['exklude'];
	$session->vars['include'] 				= $profiles[$formdata->profilname]['include'];
	$session->vars['robots'] 				= $profiles[$formdata->profilname]['robots'];
	$session->vars['meta'] 					= $profiles[$formdata->profilname]['meta'];
	$session->vars['savedata'] 				= $profiles[$formdata->profilname]['savedata'];
	$session->vars['gzip'] 					= $profiles[$formdata->profilname]['gzip'];
	$session->vars['noextensions']			= $profiles[$formdata->profilname]['noextensions'];
	$session->vars['stopword']				= $profiles[$formdata->profilname]['stopword'];
	$session->vars['wordlength']			= $profiles[$formdata->profilname]['wordlength'];
	$session->vars['buffer']				= $profiles[$formdata->profilname]['buffer'];
	$session->vars['description']			= $profiles[$formdata->profilname]['description'];
	$session->vars['meta_desc']				= $profiles[$formdata->profilname]['meta_desc'];
	$session->vars['url_pattern']			= $profiles[$formdata->profilname]['url_pattern'];
	$session->vars['url_replacement']		= $profiles[$formdata->profilname]['url_replacement'];
	$session->vars['url_failure'] 			= Array();
	$session->vars['url_to_spider'] 		= Array();
	$session->vars['url_to_spider'][0] 		= 0;
	$session->vars['url_have_spidered']		= Array();
	$session->vars['url_name']				= Array();
	$session->vars['url_have_indexed']  	= Array();
	$session->vars['url_have_indexed_name'] = Array();
	$session->vars['progress']				= 0;
	$session->vars['reference']				= Array();



	if (!isset($session->vars['exclude']))
		$session->vars['exclude'] = Array();

	# cookie-container erzeugen
	$session->vars['cookie'] = new cookie_container;

	# robots holen
	get_robots();

	# Zur Kontrolle vor dem Start nochmals ausgeben
	echo '<div id="output">'."\n";
	$c_form 		= new form();
	$c_form->set_bgcolor('#FCFCFC');
	$c_form->set_border_color('#004400');
	$c_form->method = 'GET';
	$c_form->set_width('500');

	$c_form->add_area('0');
	$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][32]); // Volltextindex erstellen - Schritt 1/5
	/* 	Sie haben das hier angezeigte Profil zur Erstellung des Volltextindexes
		gewählt. Um den Index jetzt zu erstellen, betätigen Sie die Schaltfläche
		"weiter". Danach startet der Spider, der die zu indizierenden Seiten auf
		Ihr System downlädt. Das wird einige Zeit in Anspruch nehmen, abhängig
		von der Größe Ihrer Site. Sollte der Spider nicht mit einer Erfolgs-
		meldung anhalten, sondern während des Vorgangs "hängenbleiben", drücken
		Sie einfach die "aktualisieren" Schaltfläche Ihres Browsers. Der Spider
		setzt dann an der unterbrochenen Stelle fort. */
	$message = $MESSAGES['HTTP_SRC'][33];
	$c_form->add_area_show_textarea('0', $message);
	$c_form->add_area_hidden_value('0','phpcmsaction', 'HTTPINDEX');
	$c_form->add_area_hidden_value('0','action', 'start_spider');

	$c_form->add_area('1');
	$c_form->set_area_title('1', $MESSAGES['HTTP_SRC'][34]); // Basisdaten
	$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][35], 'http://'.$session->vars['startadress']);	//Startadresse:
	if ($session->vars['robots'] === TRUE)
		$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][36], $MESSAGES['HTTP_SRC'][37]); // robots.txt: berücksichtigen
	else
		$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][36], $MESSAGES['HTTP_SRC'][38]);// robots.txt: nicht berücksichtigen
	if ($session->vars['meta'] === TRUE)
		$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][39], $MESSAGES['HTTP_SRC'][37]); // robot-META-TAGS: berücksichtigen
	else
		$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][39], $MESSAGES['HTTP_SRC'][38]); // robot-META-TAGS: nicht berücksichtigen

	if ($session->vars['meta_desc'] === TRUE)
		$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][40], $MESSAGES['HTTP_SRC'][37]); // desc-META-TAGS:  berücksichtigen
	else
		$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][40], $MESSAGES['HTTP_SRC'][38]); // desc-META-TAGS: nicht berücksichtigen

	if ($session->vars['gzip'] === '1')
		$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][41], $MESSAGES['HTTP_SRC'][42]);	// Komprimierung: benutzen
	else
		$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][41], $MESSAGES['HTTP_SRC'][43]); // Komprimierung: benutzen
	$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][44], $session->vars['savedata']); // Datenverzeichnis

	if (isset($session->vars['noextensions']) AND count($session->vars['noextensions']) > 0)
		{
		$ext = implode(';',$session->vars['noextensions']);
		$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][45],	$ext); //Nicht zu ind. Ext.:
		}

	$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][46], $session->vars['stopword']); // Stopwortdatei:
	$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][47], $session->vars['wordlength']); // Min. Wortlänge:
	$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][48], $session->vars['buffer']); // Puffergröße:
	$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][49], $session->vars['description']); // Beschreibungstext:

	$c_form->add_area('2');
	$c_form->set_area_title('2', $MESSAGES['HTTP_SRC'][50]); // Erlaubte Server
	$i=1;
	foreach($session->vars['host'] as $server)
		{
		$c_form->add_area_show_text('2', $MESSAGES['HTTP_SRC'][51].$i.':',	'http://'.$server); // Adresse
		$i++;
		}

	$area = 2;

	if (isset($session->vars['exclude']) AND count($session->vars['exclude']) > 0)
		{
		$area++;
		$c_form->add_area($area);
		$c_form->set_area_title($area, $MESSAGES['HTTP_SRC'][52]); // Auszuschließende Adressteile
		$i=1;
		foreach($session->vars['exclude'] as $addr)
			{
			$c_form->add_area_show_text($area, $MESSAGES['HTTP_SRC'][53].$i.':',	$addr); //Ausschluß
			$i++;
			}
		}

	if (isset($session->vars['include']) AND count($session->vars['include']) > 0)
		{
		$area++;
		$c_form->add_area($area);
		$c_form->set_area_title($area, $MESSAGES['HTTP_SRC'][54]); // Einzuschließende Adressteile
		$i=1;
		foreach($session->vars['include'] as $addr)
			{
			$c_form->add_area_show_text($area, $MESSAGES['HTTP_SRC'][55].$i.':',	$addr); //Einschluß
			$i++;
			}
		}

	$area++;
	$c_form->add_area($area);
	$c_form->set_area_title($area,$MESSAGES['HTTP_SRC'][150]); // URLs ändern
	$c_form->add_area_show_text($area,$MESSAGES['HTTP_SRC'][151],$session->vars['url_pattern']); // Pattern:
	$c_form->add_area_show_text($area,$MESSAGES['HTTP_SRC'][152],$session->vars['url_replacement']); // Replacement:

	$c_form->add_button('submit', 'wahl2', $MESSAGES['HTTP_SRC'][21]); // weiter
	$c_form->compose_form();
	echo "\n".'</div><!-- output -->'."\n";

	}

########################################################################
# String für Prozessanzeige aufbauen
########################################################################

function progress_bar($prozent)
	{
	$done = ceil($prozent/2);
	$message = '<table height="15" border="1" cellspacing="0">'."\n";
	$message.= '<tr><td bgcolor="#000000" align="left">';
	for ($i=0; $i<$done; $i++)
		$message.= '<img src="gif/indexer/led.gif" alt="" border="0" width="8" height="15" />';
	for ($i=$done; $i<50; $i++)
		$message.= '<img src="gif/indexer/dled.gif" alt="" border="0" width="8" height="15" />';
	$message.= '</td></tr></table>';
	return $message;
	}

########################################################################
# Nun den Spider starten
########################################################################

function start_spider()
	{
	global $formdata, $session, $PHPCMS_INDEXER_TEMP_SAVE_PATH, $MESSAGES, $PHP_SELF;

	# $body ist eine leere Variable, die später den Seiteninhalt enthält
	while(($urls = get_urls($body)) === FALSE)
		;

	# Seite schreiben
	if(strlen($body) != 0)
		{
		$index 													= count($session->vars['url_have_spidered']);
		$session->vars['url_have_spidered'][$index] 			= $session->vars['url_to_spider'][0];
		$current_page											= $session->vars['url_to_spider'][0];
		$session->vars['url_name'][$index] 						= md5(uniqid(microtime(),1)).'.htm';

		$fp = fopen($PHPCMS_INDEXER_TEMP_SAVE_PATH.$session->vars['url_name'][$index], 'wb+');
		fwrite($fp,$body,strlen($body));
		fclose($fp);
		}

	# Ersten Eintrag entfernen
	array_shift ($session->vars['url_to_spider']);

	if (is_array($urls))
		{
		$urls = array_unique($urls);
		foreach ($urls as $alink)
			{

			if (!in_array($alink,$session->vars['url_adress'],TRUE) AND trim($alink)!= '')
				{
				$index 											= count($session->vars['url_adress']);
				$session->vars['url_adress'][$index] 			= $alink;
				$session->vars['url_to_spider'][] 				= $index;
				$session->vars['reference'][$current_page][] 	= $index;
				}
			else if(trim($alink)!= '')
				{
				$index 											= array_search($alink,$session->vars['url_adress']);
				$session->vars['reference'][$current_page][] 	= $index;
				}
			}
		}

	if (!isset($session->vars['url_to_spider']) OR count($session->vars['url_to_spider']) == 0)
		{
		start_indexer();
		return;
		}

	if (isset($session->vars['url_to_spider']) AND count($session->vars['url_to_spider'])>0)
		{
		# Prozessanzeige kalkulieren
		$AllStat = count($session->vars['url_to_spider'])+count($session->vars['url_have_spidered']);
		$prozent = ($AllStat/100);
		$RelStat = floor(count($session->vars['url_have_spidered'])/$prozent);
		$session->vars['progress'] = $RelStat;

		echo '<div id="output">'."\n";
		$c_form = new form();
		$c_form->set_bgcolor('#FCFCFC');
		$c_form->set_border_color('#004400');
		$c_form->set_width('500');

		$c_form->add_area('0');
		$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][32]); // Volltextindex erstellen - Schritt 1/5

		$adress = $session->vars['url_adress'][$session->vars['url_to_spider'][0]];

		$this_path = substr($adress,strpos($adress,'/'));
		$this_host = substr($adress,0,strpos($adress,'/'));
		$c_form->add_area_show_text('0', $MESSAGES['HTTP_SRC'][56], 'http://'.$this_host.'<br />'.$this_path); // Adresse
		$c_form->add_area_show_text('0', $MESSAGES['HTTP_SRC'][57], count($session->vars['url_failure'])); // Tote Links bisher

		$c_form->add_area('1');
		$c_form->set_area_title('1', $MESSAGES['HTTP_SRC'][58]); // Fortschritt
		// Bisher "xxx" von "yyy" Adressen bearbeitet.
		$message = $MESSAGES['HTTP_SRC'][59].count($session->vars['url_have_spidered']).$MESSAGES['HTTP_SRC'][60].$AllStat.$MESSAGES['HTTP_SRC'][61];
		$message.= progress_bar($session->vars['progress']);
		$c_form->add_area_show_textarea('1', $message);

		$c_form->compose_form();
		echo "\n".'</div><!-- output -->'."\n";

//echo('file: '.__FILE__);
		echo '<META http-equiv="refresh" content="0; URL='.$session->write_link($PHP_SELF.'?phpcmsaction=HTTPINDEX&action=start_spider').'">';
		return;
		}
	}

########################################################################
# Indexer starten
########################################################################

function start_indexer()
	{
	global $session, $formdata, $MESSAGES;

	# Intialwert für Indexer setzen
	$session->vars['to_index'] = count($session->vars['url_have_spidered']);

	echo '<div id="output">'."\n";
	$c_form = new form();
	$c_form->set_bgcolor('#FCFCFC');
	$c_form->set_border_color('#004400');
	$c_form->method = 'GET';
	$c_form->set_width('500');

	$num_failure = count($session->vars['url_failure']);

	$c_form->add_area('0');
	$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][62]); // Volltextindex erstellen - Schritt 2/5
	// Fehler: Die Indizierung kann nicht fortgesetzt werden, da keine Datei gelesen werden konnte.
	// Möglicher Weise ist die Startseite in der robots.txt oder durch einen robots Meta-Tag
	// von der Indizierung ausgeschlossen
	// Bitte wählen Sie den Menüpunkt "Indizieren" und editieren Sie das Suchprofil!
	if($session->vars['to_index'] == 0) {
		$message = $MESSAGES['HTTP_SRC'][160] . $MESSAGES['HTTP_SRC'][161] . $MESSAGES['HTTP_SRC'][162];
	} else {
		// Der Spidervorgang ist nun beendet. Dabei wurden "xxx" Dateien erfolgreich gespidert.
		$message = $MESSAGES['HTTP_SRC'][63].$session->vars['to_index'].$MESSAGES['HTTP_SRC'][64];
		if($num_failure == 0)
			$message.= $MESSAGES['HTTP_SRC'][65]; // Es wurden keine toten Links gefunden.
		elseif($num_failure == 1)
			$message.= $MESSAGES['HTTP_SRC'][66]; // Es wurde ein toter Link gefunden. Der tote Link wird Ihnen im folgenden Abschnitt angezeigt.
		else
			$message.= $MESSAGES['HTTP_SRC'][67].$num_failure.$MESSAGES['HTTP_SRC'][68]; // 'Es wurden '.$num_failure.' tote Links gefunden. Die toten Links werden Ihnen im folgenden Abschnitt angezeigt.
		if($num_failure > 0)
			{
			$message.= $MESSAGES['HTTP_SRC'][69]; //Sie haben nun die Möglichkeit abzubrechen, indem Sie einfach zu einem anderen Menüpunkt
			}
		$c_form->add_area_show_textarea('0', $message);
		$c_form->add_area_hidden_value('0','action', 'continue_indexer');
		$c_form->add_area_hidden_value('0','phpcmsaction', 'HTTPINDEX');

		if($num_failure > 0)
			{
			$c_form->add_area('1');
			$c_form->set_area_title('1', $MESSAGES['HTTP_SRC'][70]); // 'Tote Links beim Spidern'
			$message = '';
			foreach($session->vars['url_failure'] as $failure)
				{
				$message.= '<b>http://'.$session->vars['url_adress'][$failure].'</b><br />';
				reset($session->vars['reference']);
				while(list($adress, $value) = each($session->vars['reference']))
					{
					if (in_array($failure,$value,TRUE))
						$message.= '<li><a href="http://'.$session->vars['url_adress'][$adress].'">http://'.$session->vars['url_adress'][$adress].'</a></li><br />';
					}
				}
			$c_form->add_area_show_textarea('1', $message);
			}

		$c_form->add_area('2');
		$c_form->set_area_title('2', $MESSAGES['HTTP_SRC'][71]); // Erklärung des 2. Schrittes
		/* In diesem Schritt wird nun der Wortindex erstellt. Das wird wieder, abhängig von der Größe
		Ihrer Site, einige Zeit in Anspruch nehmen. Bei Unterbrechungen einfach wieder die
		"Aktualisieren-Schaltfläche" Ihres Browsers drücken. Bei diesem Schritt wird eine temporäre
		Datenbank aufgebaut. Das kann den Platzverbrauch Ihres Webspaces temporär mehr als verdoppeln.
		Wenn Sie bei Ihrem Provider Plattenplatzbeschränkung haben, sollten Sie phpCMS lokal
		installieren und die Indizierung auf Ihrem lokalen Rechner durchführen und anschließend die erzeugten
		Indizies in das Zielverzeichnis bei Ihrem Provider hochladen. Die erzeugten Indizies sind
		wieder wesentlich kleiner als die temporäre Datenbank.*/
		$c_form->add_area_show_textarea('2', $MESSAGES['HTTP_SRC'][72]);

		$c_form->add_button('submit', 'wahl2', $MESSAGES['HTTP_SRC'][21]); // Weiter
	}
	$c_form->compose_form();
	echo "\n".'</div><!-- output -->'."\n";

}

########################################################################
# Hauptroutine indiziervorgang
########################################################################

function continue_indexer()
	{
	global
		$session,
		$formdata,
		$PHPCMS_INDEXER_TEMP_SAVE_PATH,
		$DEFAULTS,
		$MESSAGES,
		$PHP_SELF;

	# Test ob fertig (noch zu schreiben)
	if (count($session->vars['url_have_spidered']) == 0)
		{
		# Startwert für Merger setzen.
		$session->vars['start'] = 0;
		$session->vars['bytes_to_sort'] = filesize($PHPCMS_INDEXER_TEMP_SAVE_PATH.'words.tmp');
		merger1();
		return;
		}

	$index = count($session->vars['url_have_indexed']);

	# Zu indizierende Seite laden
	$index_page = file($PHPCMS_INDEXER_TEMP_SAVE_PATH.$session->vars['url_name'][$index]);
	$index_page = implode('',$index_page);
	$index_page_name = $session->vars['url_adress'][$session->vars['url_have_spidered'][0]];


	# Meldung ausgeben
	echo '<div id="output">'."\n";
	$c_form = new form();
	$c_form->set_bgcolor('FCFCFC');
	$c_form->set_border_color('004400');
	$c_form->method = 'GET';
	$c_form->set_width('500');

	$c_form->add_area('0');
	$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][62]); // Volltextindex erstellen - Schritt 2/5

	$adress = $index_page_name;
	$this_path = substr($adress,strpos($adress,'/'));
	$this_host = substr($adress,0,strpos($adress,'/'));

	$c_form->add_area_show_text('0', $MESSAGES['HTTP_SRC'][56], 'http://'.$this_host.'<br />'.$this_path); // Adresse:
	$c_form->add_area_show_text('0', $MESSAGES['HTTP_SRC'][73], $session->vars['url_name'][$index]); // Lokaler Name:

	# Fortschritt anzeigen
	$RelStat = floor($index/($session->vars['to_index']/100));
	$c_form->add_area('1');
	$c_form->set_area_title('1', $MESSAGES['HTTP_SRC'][58]); // Fortschritt
	// Bisher "xxx" Seiten von "yyyy" Seiten bearbeitet.
	$message = $MESSAGES['HTTP_SRC'][59].$index.$MESSAGES['HTTP_SRC'][60].$session->vars['to_index'].$MESSAGES['HTTP_SRC'][61];
	$message.= progress_bar($RelStat);
	$c_form->add_area_show_textarea	('1', $message);
	$c_form->compose_form();
	echo "\n".'</div><!-- output -->'."\n";

	# Stopwortdatei einlesen und maximale Länge feststellen
	$stopdb = $DEFAULTS->DOCUMENT_ROOT.$session->vars['stopword'];
	$STOP = file($stopdb);

//BOF sort stop.db by hpoe 17.01.03
	sort($STOP);
	$temparray = array();
	for($i=0; $i<count($STOP); $i++) {
		if(!in_array(string_tolower($STOP[$i]),$temparray,TRUE)) {
			$temparray[] = string_tolower($STOP[$i]);
		}
	}
	$fp = fopen($stopdb, 'wb');
	for ($i = 0; $i < count($temparray); $i++) {
		fwrite ($fp,$temparray[$i]);
	}
	fclose ($fp);
	$STOP = file($stopdb);
	unset($temparray);
//EOF sort stop.db

	$STOP_MAX = 0;
	$STOP_COUNT = count($STOP);
	for($i = 0; $i < $STOP_COUNT; $i++) {
		if(strlen($STOP[$i]) > $STOP_MAX) {
			$STOP_MAX = strlen($STOP[$i]);
		}
	}
	unset($STOP_COUNT);
	//$STOP = array_flip($STOP);

	# Datendatei laden, um den aktuellen Eintrag festzustellen
	# Hier kann noch optimiert werden indem man das im Speicher hält.
	if (file_exists($PHPCMS_INDEXER_TEMP_SAVE_PATH.'files.db'))
		$file_db = file($PHPCMS_INDEXER_TEMP_SAVE_PATH.'files.db');
	else
		$file_db = Array();
	$actual_entry = count($file_db);

	# Seite indizieren
	$entry = index_page($index_page, $index_page_name, $STOP, $STOP_MAX, $actual_entry);

	# File-DB schreiben
	$fp = fopen($PHPCMS_INDEXER_TEMP_SAVE_PATH.'files.db','ab+');
	fputs ($fp, $entry, strlen($entry));
	fclose ($fp);
	unset($file_db);


	# Seite aus dem Index-Cache entfernen (noch zu schreiben)
	unlink($PHPCMS_INDEXER_TEMP_SAVE_PATH.$session->vars['url_name'][$index]);

	# Zu indizierende Seiten reduzieren
	array_shift ($session->vars['url_have_spidered']);

	# Indizierte Seite zum Ziel-Array hinzufügen
	$session->vars['url_have_indexed'][$index] = array_search($index_page_name,$session->vars['url_adress']);

	# Cookie-Container schreiben
	$session->vars['cookie']->save();


	# Meta-Refresh ausgeben (noch zu schreiben)
	echo '<META http-equiv="refresh" content="0; URL='.$session->write_link($PHP_SELF.'?phpcmsaction=HTTPINDEX&action=continue_indexer').'">';
	}

########################################################################
# Erster Merger (Mischer)
########################################################################

function merger1()
	{
	global $action, $session, $DEFAULTS, $PHPCMS_INDEXER_TEMP_SAVE_PATH, $MESSAGES, $PHP_SELF;

	# startmeldung ausgeben
	if ($action == 'continue_indexer')
		{
		echo '<div id="output">'."\n";
		$c_form = new form();
		$c_form->set_bgcolor('#FCFCFC');
		$c_form->set_border_color('#004400');
		$c_form->method = 'GET';
		$c_form->set_width('500');

		$c_form->add_area('0');
		$c_form->add_area_hidden_value('0','phpcmsaction', 'HTTPINDEX');
		$c_form->add_area_hidden_value('0','action', 'continue_merger1');
		$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][74]); // Volltextindex erstellen - Schritt 3/5
		/*  Der Wortindex wurde erstellt. Im nächsten Schritt werden die
			die einzelnen Wörter nach Länge sortiert.<p>
			Sollten Sie Probleme mit Timeouts bekommen, setzen Sie die Puffergröße im
			Suchprofil herunter. Betätigen Sie die Schaltfläche "weiter" um mit der
			Sortierung zu beginnen.*/
		$c_form->add_area_show_textarea('0', $MESSAGES['HTTP_SRC'][75]);
		$c_form->add_button('submit', 'wahl2', $MESSAGES['HTTP_SRC'][21]); // weiter
		$c_form->compose_form();
		echo "\n".'</div><!-- output -->'."\n";

		return;
		}

	$ende = FALSE;
	$TempWordsCount = 0;
	$index = 0;
	$start = $session->vars['start'];

	$fp = fopen($PHPCMS_INDEXER_TEMP_SAVE_PATH.'words.tmp', 'rb');
	fseek ($fp, $start);

	while ((($TempWordsCount+100) < $session->vars['buffer']) AND !feof($fp))
		{
		$TempWords[$index] = trim(fgets($fp,4096));
		if ( strlen($TempWords[$index]) == 0 ) continue;
		$TempWordsCount = $TempWordsCount+ strlen($TempWords[$index])+1;
		$index++;
		}
	if (feof($fp))
		{
		$ende = TRUE;
		}
	fclose ($fp);

	$next_start = $start+$TempWordsCount;
	$session->set_var('start', $next_start);

	$session->vars['index_len'] = '';
	for ($i=0; $i<$index; $i++)
		{
		list($word, $file) = explode('#',$TempWords[$i]);
		$word_len = strlen($word);

		$fp = fopen ( $PHPCMS_INDEXER_TEMP_SAVE_PATH.'t'.$word_len.'.db', 'ab+' );
		$entry = $TempWords[$i]."\n";
		$wordlen = strlen($entry);
		$session->vars['index_len'] = $session->vars['index_len']+$wordlen;
		fputs ($fp, $entry, $wordlen);
		fclose ($fp);
		}

	if ($ende == TRUE)
		{
		# Startwert für merger2 festlegen
		$dire = $PHPCMS_INDEXER_TEMP_SAVE_PATH;
		$session->vars['word_len_files'] = 0;

		$d = dir ( $dire );
		while ( $entry = $d->read() )
			{
			if ( substr ( $entry, 0, 1 ) == 't' )
				$session->vars['word_len_files']++;
			}
		$session->vars['current_word_len'] = 0;
		merger2();
		return;
		}

	# Meldung ausgeben
	echo '<div id="output">'."\n";
	$c_form = new form();
	$c_form->set_bgcolor('#FCFCFC');
	$c_form->set_border_color('#004400');
	$c_form->method = 'GET';
	$c_form->set_width('500');

	$RelStat = floor($session->vars['index_len']/($session->vars['bytes_to_sort']/100));
	$c_form->add_area('0');
	$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][74]); // Volltextindex erstellen - Schritt 3/5
	// "Bisher "xxx" von "yyy" Bytes sortiert
	$message = $MESSAGES['HTTP_SRC'][59].$session->vars['index_len'].$MESSAGES['HTTP_SRC'][60].$session->vars['bytes_to_sort'].$MESSAGES['HTTP_SRC'][76];
	$message.= progress_bar($RelStat);
	$c_form->add_area_show_textarea	('0', $message);
	$c_form->compose_form();
	echo "\n".'</div><!-- output -->'."\n";

	# Meta-Refresh ausgeben (noch zu schreiben)
	echo '<META http-equiv="refresh" content="0; URL='.$session->write_link($PHP_SELF.'?phpcmsaction=HTTPINDEX&action=continue_merger1').'">';
	}

########################################################################
# Startet einen neuen Indiziervorgang
########################################################################

function merger2()
	{
	global $action, $session, $DEFAULTS, $PHPCMS_INDEXER_TEMP_SAVE_PATH, $MESSAGES, $PHP_SELF;

	# startmeldung ausgeben
	if ($action == 'continue_merger1')
		{
		echo '<div id="output">'."\n";
		$c_form = new form();
		$c_form->set_bgcolor('#FCFCFC');
		$c_form->set_border_color('#004400');
		$c_form->method = 'GET';
		$c_form->set_width('500');

		$c_form->add_area('0');
		$c_form->add_area_hidden_value('0','phpcmsaction', 'HTTPINDEX');
		$c_form->add_area_hidden_value('0','action', 'continue_merger2');
		$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][77]); // Volltextindex erstellen - Schritt 4/5
		/*  Die einzelnen Wörter sind nun nach Wortlänge sortiert.<p>
			In diesem Schritt wird nun der Index erstellt, der dann für die Volltextsuche benutzt wird.
			Betätigen Sie die Schaltfläche "weiter" um die Indizierung
			abzuschließen.*/
		$c_form->add_area_show_textarea('0',$MESSAGES['HTTP_SRC'][78]);
		$c_form->add_button('submit', 'wahl2', $MESSAGES['HTTP_SRC'][21]); // weiter
		$c_form->compose_form();
		echo "\n".'</div><!-- output -->'."\n";
		return;
		}

	# öffne directory,
	$dire = $PHPCMS_INDEXER_TEMP_SAVE_PATH;
	$found = FALSE;

	$d = dir ( $dire );
	while ( $entry = $d->read() )
		{
		if ( substr ( $entry, 0, 1 ) == 't' )
			{
			$current_file = $PHPCMS_INDEXER_TEMP_SAVE_PATH.$entry;
			$found = TRUE;
			break;
			}
		}

	# Sind wir fertig?
	if ($found === FALSE)
		{
		# GZIP an
		if ($session->vars['gzip'] == '1' AND extension_loaded ('zlib'))
			{
			$WordIndex = file($PHPCMS_INDEXER_TEMP_SAVE_PATH.'words.db');
			$WordToWrite = implode("",$WordIndex);
			$gp1 = gzopen($PHPCMS_INDEXER_TEMP_SAVE_PATH.'words.gz', 'wb');
			gzwrite ($gp1,$WordToWrite);
			gzclose ($gp1);
			unlink ($PHPCMS_INDEXER_TEMP_SAVE_PATH.'words.db');

			$FileDB = file ($PHPCMS_INDEXER_TEMP_SAVE_PATH.'files.db');
			$FileToWrite = implode("",$FileDB);
			$gp2 = gzopen($PHPCMS_INDEXER_TEMP_SAVE_PATH.'files.gz', 'wb');
			gzwrite ($gp2,$FileToWrite);
			gzclose ($gp2);
			unlink ($PHPCMS_INDEXER_TEMP_SAVE_PATH.'files.db');

			$DataArray = file ($PHPCMS_INDEXER_TEMP_SAVE_PATH.'data.db');
			$DataToWrite = implode("",$DataArray);
			$gp3 = gzopen($PHPCMS_INDEXER_TEMP_SAVE_PATH.'data.gz', 'wb');
			gzwrite ($gp3,$DataToWrite);
			gzclose ($gp3);
			unlink ($PHPCMS_INDEXER_TEMP_SAVE_PATH.'data.db');

			unlink ($PHPCMS_INDEXER_TEMP_SAVE_PATH.'words.tmp');
			}
		else
			{
			unlink ($PHPCMS_INDEXER_TEMP_SAVE_PATH.'words.tmp');
			}
		distribute();
		return;
		}

	$TempArray = file ( $current_file );
	$index = 0;
	for ($i=0; $i<count($TempArray); $i++)
		{
		$TempArray[$i] = trim($TempArray[$i]);
		if (strlen($TempArray[$i]) < 1) continue;

		list ( $word, $seite, $anzahl ) = explode ( '#', $TempArray[$i] );

		if (isset($WordArray[$word]))
			{
			$DataArray[$WordArray[$word]] = $DataArray[$WordArray[$word]].'+'.$seite.'*'.$anzahl;
			}
		else
			{
			$WordArray[$word] = $index;
			$IndexArray[$index] = $word;
			$DataArray[$index] = $seite.'*'.$anzahl;
			$index ++;
			}
		}

	$output_file = $PHPCMS_INDEXER_TEMP_SAVE_PATH.'words.db';

	$fp = fopen ($output_file, 'ab+');
	while ( list($k, $v) = each ($IndexArray) )
		{
		$v = $v."\n";
		$size = strlen( $v );
		fputs ($fp, $v, $size);
		}
	fclose ($fp);

	$output_file = $PHPCMS_INDEXER_TEMP_SAVE_PATH.'data.db';

	$fp = fopen ($output_file, 'ab+');
	while (list($k, $v) = each ($DataArray) )
		{
		$v = $v."\n";
		$size = strlen( $v );
		fputs ($fp, $v, $size);
		}
	fclose ($fp);

	unlink ($current_file);

	# Statusmeldung ausgeben
	echo '<div id="output">'."\n";
	$c_form = new form();
	$c_form->set_bgcolor('#FCFCFC');
	$c_form->set_border_color('#004400');
	$c_form->method = 'GET';
	$c_form->set_width('500');

	$c_form->add_area('0');
	$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][77]); // Volltextindex erstellen - Schritt 4/5
	$c_form->add_area_show_text('0', $MESSAGES['HTTP_SRC'][79], substr ($entry, 1, strrpos ( $entry, '.' )-1).$MESSAGES['HTTP_SRC'][80]); // Wortlänge momentan bearbeitet: "xxx" Zeichen

	# Fortschritt anzeigen
	$session->vars['current_word_len']++;
	$RelStat = floor($session->vars['current_word_len']/($session->vars['word_len_files']/100));
	$c_form->add_area('1');
	$c_form->set_area_title('1', $MESSAGES['HTTP_SRC'][58]); // Fortschritt
	// Bisher "xxx" von "yyy" Wortlängen bearbeitet
	$message = $MESSAGES['HTTP_SRC'][59].$session->vars['current_word_len'].$MESSAGES['HTTP_SRC'][60].$session->vars['word_len_files'].$MESSAGES['HTTP_SRC'][81];
	$message.= progress_bar($RelStat);
	$c_form->add_area_show_textarea('1', $message);

	$c_form->compose_form();
	echo "\n".'</div><!-- output -->'."\n";

	echo '<META http-equiv="refresh" content="0; URL='.$session->write_link($PHP_SELF.'?phpcmsaction=HTTPINDEX&action=continue_merger2').'">';

	}

########################################################################
# Dateien einrichten
########################################################################

function distribute()
	{
	global $action, $session, $DEFAULTS,$PHPCMS_INDEXER_TEMP_SAVE_PATH, $formdata, $MESSAGES;

	$write_to = $DEFAULTS->DOCUMENT_ROOT.$session->vars['savedata'];

	if (isset($formdata->ready) AND $formdata->ready == 'Fertigstellen')
		{
		# Dateien kopieren
		if ($session->vars['gzip'] == '1' AND extension_loaded ('zlib'))
			{
			$result = copy ($PHPCMS_INDEXER_TEMP_SAVE_PATH.'words.gz',$write_to.'/words.gz');
			if ($result === TRUE)
				unlink($PHPCMS_INDEXER_TEMP_SAVE_PATH.'words.gz');

			$result = copy ($PHPCMS_INDEXER_TEMP_SAVE_PATH.'files.gz',$write_to.'/files.gz');
			if ($result === TRUE)
				unlink($PHPCMS_INDEXER_TEMP_SAVE_PATH.'files.gz');

			$result = copy ($PHPCMS_INDEXER_TEMP_SAVE_PATH.'data.gz',$write_to.'/data.gz');
			if ($result === TRUE)
				unlink($PHPCMS_INDEXER_TEMP_SAVE_PATH.'data.gz');
			}
		else
			{
			$result = copy ($PHPCMS_INDEXER_TEMP_SAVE_PATH.'words.db',$write_to.'/words.db');
			if ($result === TRUE)
				unlink($PHPCMS_INDEXER_TEMP_SAVE_PATH.'words.db');

			$result = copy ($PHPCMS_INDEXER_TEMP_SAVE_PATH.'files.db',$write_to.'/files.db');
			if ($result === TRUE)
				unlink($PHPCMS_INDEXER_TEMP_SAVE_PATH.'files.db');

			$result = copy ($PHPCMS_INDEXER_TEMP_SAVE_PATH.'data.db',$write_to.'/data.db');
			if ($result === TRUE)
				unlink($PHPCMS_INDEXER_TEMP_SAVE_PATH.'data.db');
			}
		# Zurück zum Start
		show_list();
		return;
		}

	echo '<div id="output">'."\n";
	$c_form = new form();
	$c_form->set_bgcolor('#FCFCFC');
	$c_form->set_border_color('#004400');
	$c_form->method = 'GET';
	$c_form->set_width('500');

	$c_form->add_area('0');
	$c_form->add_area_hidden_value('0','phpcmsaction', 'HTTPINDEX');
	$c_form->add_area_hidden_value('0','ready', 'Fertigstellen');
	$c_form->add_area_hidden_value('0','action', 'distribute');
	$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][82]); // Volltextindex erstellen - Schritt 5/5
	/*
	Im letzten Schritt werden die Dateien in das von Ihnen gewählte
	Zielverzeichnis "'.$session->vars['savedata'].'" kopiert.<p>
	Um den Kopiervorgang zu starten und die Indizierung abzuschließen
	betätigen Sie die Schaltfläche "Fertigstellen"!
	*/
	$c_form->add_area_show_textarea('0', $MESSAGES['HTTP_SRC'][83].$session->vars['savedata'].$MESSAGES['HTTP_SRC'][84]);

	$c_form->add_button('submit', 'submit', $MESSAGES['HTTP_SRC'][85]); // Fertigstellen
	$c_form->compose_form();
	echo "\n".'</div><!-- output -->'."\n";
	}

?>