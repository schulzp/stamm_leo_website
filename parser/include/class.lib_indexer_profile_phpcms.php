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
# Gibt erste Formularseite aus
########################################################################

function input_form($message='') {

	global $session, $formdata, $profiledata, $MESSAGES;

	if (!isset($formdata->dazu) OR $formdata->dazu != $MESSAGES['HTTP_SRC'][90]) {
			// initialize at first call
			unset_all();
		if($message == 'edit') {
			// edit profile
			$url = 'http://'.$session->vars['editprofile']['host'][0];
			for($i=1;$i<count($session->vars['editprofile']['host']);$i++) {
				$session->vars['host'][$i] = $session->vars['editprofile']['host'][$i];
			}
			$message = '';
		}
		else {
			// new profile
			$url = 'http://';
			unset($session->vars['editprofile']);
		}
	}
	else {
		add_start();
		return;
	}

	echo '<div id="output">'."\n";
	$c_form = new form();
	$c_form->set_bgcolor('#FCFCFC');
	$c_form->set_border_color('#004400');
	$c_form->set_width('500');
	$c_form->set_left_size('100');

	$c_form->add_area('0');
	$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][86]); // Suchprofil erstellen - Schritt 1/7
	if($message=='') {
		/*
		Im ersten Schritt geben Sie die Adressen der zu indizierenden Seiten an.
		Es werden nur Seiten indiziert, die innerhalb des angegebenen Servers liegen.<p>
		Um z.B. alle Seiten des Servers "http://phpcms.de/" zu indizieren,
		und um bei der Adresse "http://phpcms.de/homepage/index.htm"
		zu starten, geben Sie als Adresse:<p>
		<blockquote><b>http://phpcms.de/homepage/index.htm</b></blockquote>
		an. Geben Sie nun die Startadresse der zu indizierenden Seiten ein:
		*/
		$temp = $MESSAGES['HTTP_SRC'][87];
	}
	else {
		$temp = $message;
	}
	$c_form->add_area_show_textarea('0', $temp);

	$c_form->add_area('1');
	$c_form->set_area_title('1', $MESSAGES['HTTP_SRC'][88]); // Daten
	$c_form->add_area_hidden_value('1','phpcmsaction', 'HTTPINDEX');
	$c_form->add_area_hidden_value('1','action', 'add_start');
	$c_form->add_area_input_text('1', 'url','40', $MESSAGES['HTTP_SRC'][89],$url); // Adresse:
	$c_form->add_button('submit', 'dazu', $MESSAGES['HTTP_SRC'][90]); // hinzufügen
	$c_form->compose_form();

	echo "\n".'</div><!-- output -->'."\n";

} // end input_form

########################################################################
# Gibt zweite Formularseite aus
########################################################################

function add_start($message='') {

	global $session, $formdata, $MESSAGES, $PHP_SELF;

	if (!isset($formdata->wahl) OR $formdata->wahl != $MESSAGES['HTTP_SRC'][21]) {
		if($message!='edit') {
			// prüfen, ob die Startadresse valide ist
			$temp = check_adress();
			if(!is_array($temp)) {
				$message = $temp;
			}
			else {
				$session->vars['host'][] = $temp['host'].$temp['path'];
				$session->vars['host'] = explode(':=',implode(':=',$session->vars['host']));
			}
		}
		else {
			$message = '';
		}
	}
	else {
		start_exklude();
		return;
	}

	echo '<div id="output">'."\n";
	$c_form = new form();
	$c_form->set_bgcolor('#FCFCFC');
	$c_form->set_border_color('#004400');
	$c_form->set_width('500');
	$c_form->set_left_size('100');

	$c_form->add_area('0');
	$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][86]); //Suchprofil erstellen - Schritt 1/7
	if($message=='') {
		/*
		Der Indexer holt nur Seiten von dem angegebenen Server.
		Wenn außer jenen Seiten, die auf dem Server der Startadresse liegen,
		Seiten von weiteren Servern bei der Indizierung berücksichtigt werden
		sollen, können Sie diese Server hier erfassen.<p> Geben Sie entweder
		weitere Server an, oder gehen Sie mit "weiter" zum nächsten Schritt:
		*/
		$temp = $MESSAGES['HTTP_SRC'][91];
	}
	else {
		$temp = $message;
	}
	$c_form->add_area_show_textarea('0', $temp);

	$c_form->add_area('2');
	$c_form->set_area_title('2', $MESSAGES['HTTP_SRC'][94]); // Server hinzufügen
	$c_form->add_area_hidden_value('2','phpcmsaction', 'HTTPINDEX');
	$c_form->add_area_hidden_value('2','action', 'add_start');
	$c_form->add_area_input_text('2', 'url','40', $MESSAGES['HTTP_SRC'][56],'http://'); // Adresse
	$c_form->add_button('submit', 'wahl', $MESSAGES['HTTP_SRC'][90]); // hinzufügen
	$c_form->add_button('submit', 'wahl', $MESSAGES['HTTP_SRC'][21]); // weiter
	$c_form->compose_form();

	echo '<br />';
	$c_form2 = new form();
	$c_form2->set_bgcolor('#FCFCFC');
	$c_form2->set_border_color('#004400');
	$c_form2->set_width('500');
	$c_form2->set_left_size('100');

	$c_form2->add_area('1');
	$c_form2->set_area_title('1', $MESSAGES['HTTP_SRC'][92]); //Server
	$i=0; $j=1;
	if(isset($session->vars['host']) AND count($session->vars['host']) > 0) {
		$message = '<table border="0" cellspacing="1" cellpadding="0" width="405">';
		$message.= '<tr><td background="gif/indexer/h_trenner.gif" colspan="2"><img src="gif/indexer/nix.gif" width="2" height=1 border=0 vspace=0 hspace=0></td></tr>';
		foreach($session->vars['host'] as $server) {
			$message.= '<tr>';
			$message.= '<td width="100%">'.$c_form2->normal_font.$MESSAGES['HTTP_SRC'][51].$j.': http://'.$server.'</font></td>'; // Adresse
			$message.= '<form action="'.$session->write_link($PHP_SELF).'">';
			$message.= '<input type="hidden" name="phpcmsaction" value="HTTPINDEX">';
			$message.= '<input type="hidden" name="host" value="'.$i.'">';
			$message.= '<input type="hidden" name="action" value="delete_host">';
			$message.= '<td width="16"><input type="image" src="gif/indexer/delete.gif" width="16" height="16" alt="'.$MESSAGES['HTTP_SRC'][145].'" title="'.$MESSAGES['HTTP_SRC'][145].'" border="0"></td>'; // Server löschen
			$message.= '</form>';
			$message.= '</tr>';
			$message.= '<tr><td background="gif/indexer/h_trenner.gif" colspan="2"><img src="gif/indexer/nix.gif" width="2" height=1 border=0 vspace=0 hspace=0></td></tr>';
			$i++; $j++;
		}
		$message.= '</table>';
		$c_form2->add_area_show_textarea('1', $message);
	}
	else {
		$c_form2->add_area_show_text('1', $MESSAGES['HTTP_SRC'][56], $MESSAGES['HTTP_SRC'][93]); // noch keine Startadresse erfasst
	}
	$c_form2->compose_form();
	echo "\n".'</div><!-- output -->'."\n";

} // end add_start

function delete_host() {

	global $session,$formdata;

	if(count($session->vars['host']) > 1) {
		unset($session->vars['host'][$formdata->host]);
		$session->vars['host'] = explode(':=',implode(':=',$session->vars['host']));
	}
	add_start('edit');

} // end delete_host

########################################################################
# Erster Schirm Exklude
########################################################################

function start_exklude() {

	global $session, $formdata, $MESSAGES;

	echo '<div id="output">'."\n";
	$c_form = new form();
	$c_form->set_bgcolor('#FCFCFC');
	$c_form->set_border_color('#004400');
	$c_form->set_width('500');
	$c_form->set_left_size('130');

	$c_form->add_area('0');
	$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][95]); // Suchprofil erstellen - Schritt 2/7
	/*
	Im zweiten Schritt geben Sie an, wie mit Angaben in der Datei "robots.txt"
	und in den META-TAGS der HTML-Seiten umgegangen werden soll.<p>'
	Sie haben im nächsten Schritt noch zusätzlich die Möglichkeit Adressen
	anzugeben, unterhalb derer keinesfalls inidiziert werden soll.
	*/
	$c_form->add_area_show_textarea('0', $MESSAGES['HTTP_SRC'][96]);

	if(isset($session->vars['editprofile'])) {
		$robots[1] = $session->vars['editprofile']['robots'] ? ' checked' : '';
		$robots[0] = $session->vars['editprofile']['robots'] ? '' : ' checked';
		$meta[1] = $session->vars['editprofile']['meta'] ? ' checked' : '';
		$meta[0] = $session->vars['editprofile']['meta'] ? '' : ' checked';
		$meta_desc[1] = $session->vars['editprofile']['meta_desc'] ? ' checked': '';
		$meta_desc[0] = $session->vars['editprofile']['meta_desc'] ? '': ' checked';
	}
	else {
		$robots[1] = '';
		$robots[0] = ' checked';
		$meta[1] = '';
		$meta[0] = ' checked';
		$meta_desc[1] = '';
		$meta_desc[0] = ' checked';
	}

	$c_form->add_area('1');
	$c_form->set_area_title('1', $MESSAGES['HTTP_SRC'][97]); // Voreinstellung
	$c_form->add_area_hidden_value('1','action', 'continue_exklude');
	$c_form->add_area_hidden_value('1','phpcmsaction', 'HTTPINDEX');

	$message = '<nobr><input type="radio" name="robots" value="1"'.$robots[1].'> '.$MESSAGES['HTTP_SRC'][37].'</nobr><br />'."\n"; // berücksichtigen
	$message.= '<nobr><input type="radio" name="robots" value="0"'.$robots[0].'> '.$MESSAGES['HTTP_SRC'][38].'</nobr>'."\n"; // nicht berücksichtigen
	$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][36], $message); // robots.txt
	$message = '<nobr><input type="radio" name="meta" value="1"'.$meta[1].'> '.$MESSAGES['HTTP_SRC'][37].'</nobr><br />'."\n"; // berücksichtigen
	$message.= '<nobr><input type="radio" name="meta" value="0"'.$meta[0].'> '.$MESSAGES['HTTP_SRC'][38].'</nobr>'."\n"; // nicht berücksichtigen
	$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][39], $message); // robot-META-TAGS
	$message = '<nobr><input type="radio" name="meta_desc" value="1"'.$meta_desc[1].'> '.$MESSAGES['HTTP_SRC'][37].'</nobr><br />'."\n"; // berücksichtigen
	$message.= '<nobr><input type="radio" name="meta_desc" value="0"'.$meta_desc[0].'> '.$MESSAGES['HTTP_SRC'][38].'</nobr>'."\n"; // nicht berücksichtigen
	$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][40], $message);	// description-META-TAGS

	$c_form->add_button('submit', 'dazu', $MESSAGES['HTTP_SRC'][21]); // weiter
	$c_form->compose_form();
	echo "\n".'</div><!-- output -->'."\n";

} // end start_exklude


########################################################################
# Zweiter Schirm Exklude
########################################################################

function continue_exklude($message='') {

	global $session,$formdata, $MESSAGES, $PHP_SELF;

	# robots und Meta auswerten
	if(isset($formdata->robots)) {
		if ($formdata->robots == 0) {
			$session->vars['robots'] = FALSE;
		}
		else {
			$session->vars['robots'] = TRUE;
		}

		if ($formdata->meta == 0) {
			$session->vars['meta'] = FALSE;
		}
		else {
			$session->vars['meta'] = TRUE;
		}

		if ($formdata->meta_desc == 0) {
			$session->vars['meta_desc'] = FALSE;
		}
		else {
			$session->vars['meta_desc'] = TRUE;
		}

		if(isset($session->vars['editprofile'])) {
			for($i=0;$i<count($session->vars['editprofile']['exklude']);$i++) {
				$session->vars['exclude'][$i] = $session->vars['editprofile']['exklude'][$i];
			}
		}
	}

	# wenn fertig weiter
	if (isset($formdata->wahl1) AND $formdata->wahl1 == $MESSAGES['HTTP_SRC'][21]) { // weiter
		if(isset($session->vars['editprofile'])) {
			for($i=0;$i<count($session->vars['editprofile']['include']);$i++) {
				$session->vars['include'][$i] = $session->vars['editprofile']['include'][$i];
			}
		}
		continue_include();
		return;
	}

	# wenn nicht erstaufruf prüfen ob valide Adresse übergeben
	if (!isset($formdata->dazu) OR $formdata->dazu != $MESSAGES['HTTP_SRC'][21]) { // weiter
		if($message != 'edit' AND $formdata->url != '') {
			if (strtoupper(substr($formdata->url,0,7)) == 'HTTP://') {
				$formdata->url = substr($formdata->url,7);
			}
			foreach($session->vars['host'] as $server) {
				if (stristr($server,$formdata->url)) {
					$message = $MESSAGES['HTTP_SRC'][98].$formdata->url; //'Der von Ihnen angegeben Ausschluß "http://'
					$message.= $MESSAGES['HTTP_SRC'][99].$server.$MESSAGES['HTTP_SRC'][100]; // '" aus dem Server "http://''" bewirkt, dass dieser Server '
					// überhaupt nicht indiziert wird. Aus diesem Grund ist dieser Ausschluß nicht zulässig!';
					break;
				}
			}
			if ($message == '') {
				$session->vars['exclude'][] = $formdata->url;
				$session->vars['exclude'] = explode(':=',implode(':=',$session->vars['exclude']));
			}
		}
		else {
			$message = '';
		}
	} // end if

	echo '<div id="output">'."\n";
	$c_form = new form();
	$c_form->set_bgcolor('#FCFCFC');
	$c_form->set_border_color('#004400');
	$c_form->set_width('500');
	$c_form->set_left_size('80');

	$startadr = substr($session->vars['host'][0],0,strpos($session->vars['host'][0],'/'));

	$c_form->add_area('0');
	$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][101]); //'Suchprofil erstellen - Schritt 3/7'
	if ($message == '') {
		/*
		Sie haben nun die Möglichkeit Adressteile anzugeben, bei deren Vorkommen
		keinesfalls inidiziert werden soll.<p> Ein Beispiel: Sie wollen nicht,
		dass die Dateien im Verzeichnis "/test" das im Root Ihres Servers
		"http://$startadr" liegt indiziert werden. Geben Sie in diesem Fall:
		<blockquote><b>'.$startadr.'/test/</b></blockquote><b>ohne</b>
		führendes http:// als Adresse für den Ausschluß an. Sie können auch
		Adressteile ohne Server für den Ausschluß angeben.
		*/
		$message = $MESSAGES['HTTP_SRC'][102].$startadr.$MESSAGES['HTTP_SRC'][103].$startadr.$MESSAGES['HTTP_SRC'][104];
	}
	$c_form->add_area_show_textarea('0', $message);

	if ( count ( $session->vars['host'] ) > 0 ) {
		$c_form->add_area('1');
		$c_form->set_area_title('1', $MESSAGES['HTTP_SRC'][92]); // Server
		$i=1;
		foreach($session->vars['host'] as $server) {
			$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][51].$i.':','http://'.$server); // Adresse
			$i++;
		}
	}

	$c_form->add_area('2');
	$c_form->set_area_title('2', $MESSAGES['HTTP_SRC'][105]); //Adressteile ausschließen
	$c_form->add_area_hidden_value('2','action', 'continue_exklude');
	$c_form->add_area_hidden_value('2','phpcmsaction', 'HTTPINDEX');
	$c_form->add_area_input_text('2', 'url','40', $MESSAGES['HTTP_SRC'][89],''); // 'Adresse:'
	$c_form->add_button('submit', 'wahl1', $MESSAGES['HTTP_SRC'][90]); // 'hinzufügen'
	$c_form->add_button('submit', 'wahl1', $MESSAGES['HTTP_SRC'][21]); // 'weiter'
	$c_form->compose_form();


	echo '<br />';
	$c_form2 = new form();
	$c_form2->set_bgcolor('#FCFCFC');
	$c_form2->set_border_color('#004400');
	$c_form2->set_width('500');
	$c_form2->set_left_size('80');

	$c_form2->add_area('2');
	$c_form2->set_area_title('2', $MESSAGES['HTTP_SRC'][53]); // Ausschluß
	$i=0; $j=1;
	if(isset($session->vars['exclude'])) {
		$message = '<table border="0" cellspacing="1" cellpadding="0" width="405">';
		$message.= '<tr><td background="gif/indexer/h_trenner.gif" colspan="2"><img src="gif/indexer/nix.gif" width="2" height=1 border=0 vspace=0 hspace=0></td></tr>';
		foreach($session->vars['exclude'] as $addr) {
			$message.= '<tr>';
			$message.= '<td width="100%">'.$c_form2->normal_font.$MESSAGES['HTTP_SRC'][51].$j.': '.$addr.'</font></td>'; // Adresse
			$message.= '<form action="'.$session->write_link($PHP_SELF).'">';
			$message.= '<input type="hidden" name="phpcmsaction" value="HTTPINDEX">';
			$message.= '<input type="hidden" name="exclude" value="'.$i.'">';
			$message.= '<input type="hidden" name="action" value="delete_exklude">';
			$message.= '<td width="16"><input type="image" src="gif/indexer/delete.gif" width="16" height="16" alt="'.$MESSAGES['HTTP_SRC'][146].'" title="'.$MESSAGES['HTTP_SRC'][146].'" border="0"></td>'; // Ausschluss löschen
			$message.= '</form>';
			$message.= '</tr>';
			$message.= '<tr><td background="gif/indexer/h_trenner.gif" colspan="2"><img src="gif/indexer/nix.gif" width="2" height=1 border=0 vspace=0 hspace=0></td></tr>';
			$i++; $j++;
		}
		$message.= '</table>';
		$c_form2->add_area_show_textarea('2', $message);
	}
	else {
		$c_form2->add_area_show_text('2', $MESSAGES['HTTP_SRC'][89],$MESSAGES['HTTP_SRC'][93]); //Adresse:'noch keine Adresse erfasst.'
	}
	$c_form2->compose_form();
	echo "\n".'</div><!-- output -->'."\n";

} // end continue_exklude

function delete_exklude() {

	global $session,$formdata;

	unset($session->vars['exclude'][$formdata->exclude]);
	if(count($session->vars['exclude']) <= 1 AND $session->vars['exclude'][0] == '' AND $session->vars['exclude'][1] == '' ) {
		unset($session->vars['exclude']);
	}
	else {
		$session->vars['exclude'] = explode(':=',implode(':=',$session->vars['exclude']));
	}
	continue_exklude('edit');

} // end delete_exklude

########################################################################
# Einschlüsse
########################################################################

function continue_include($message = '') {

	global $session, $formdata, $MESSAGES, $PHP_SELF;

	# prüfen
	if (!isset($formdata->wahl) OR $formdata->wahl != $MESSAGES['HTTP_SRC'][21]) {
		if($message != 'edit' AND $formdata->url != '') {
			if (!isset($formdata->wahl1) OR $formdata->wahl1 != $MESSAGES['HTTP_SRC'][21]) {
				$adress = check_adress();
				if (!is_array($adress)) {
					$message = $adress;
				}
				else {
					$adress['path'] = substr($adress['path'],0,-1);
					$found = FALSE;
					//echo 'Host:'.$adress['host'].'<br />';
					//echo 'Path:'.$adress['path'].'<br />';
					//print_r($session->vars['host']);
					foreach($session->vars['host'] as $server) {
						if(stristr($server,$adress['host'].$adress['path'])) {
							$found = TRUE;
							break;
						}
					}
					if ($found === FALSE) {
						/*
						Die von Ihnen angegebene Adresse für den Einschluß liegt nicht unterhalb ';
						der angegebenen Startadresse. Es wird in diesem Fall keine Seite indiziert!';
						*/
						$message = $MESSAGES['HTTP_SRC'][106];
					}
				}
				if ($message == '') {
					$session->vars['include'][] = $formdata->url;
					$session->vars['include'] = explode(':=',implode(':=',$session->vars['include']));
				}
			} // end if
		} // end if
		else {
			$message = '';
		}
	} // end if
	else {
		continue_urlchange();
		return;
	}

	# Meldung ausgeben

	echo '<div id="output">'."\n";
	$c_form = new form();
	$c_form->set_bgcolor('#FCFCFC');
	$c_form->set_border_color('#004400');
	$c_form->set_width('500');
	$c_form->set_left_size('80');

	$c_form->add_area('0');
	$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][107]); // Suchprofil erstellen - Schritt 4/7

	if ($message == '') {
		$startadr = substr($session->vars['host'][0],0,strpos($session->vars['host'][0],'/'));
		/*
		Sie haben nun die Möglichkeit Adressteile anzugeben, die in jeder
		Adresse vorhanden sein müssen, damit eine Seite indiziert wird.<p>
		Ein Beispiel: Sie wollen, dass nur Seiten indiziert werden, die im
		Verzeichnis "/test" das im Root Ihres Servers "http://$startadr
		liegen. Geben Sie in diesem Fall:<blockquote><b>http://
		$startadr.'/test/</b></blockquote> als Adresse für den Einschluß an.
		*/
		$message = $MESSAGES['HTTP_SRC'][108].$startadr.$MESSAGES['HTTP_SRC'][109].$startadr.$MESSAGES['HTTP_SRC'][110];
	}
	$c_form->add_area_show_textarea('0', $message);

	if ( count ( $session->vars['host'] ) > 0 ) {
		$c_form->add_area('1');
		$c_form->set_area_title('1', $MESSAGES['HTTP_SRC'][92]); //Server
		$i=1;
		foreach($session->vars['host'] as $server) {
			$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][51].$i.':',	'http://'.$server); //Adresse
			$i++;
		}
	}

	$c_form->add_area('2');
	$c_form->set_area_title('2', $MESSAGES['HTTP_SRC'][112]); // Adressteile einschließen
	$c_form->add_area_hidden_value('2','action', 'continue_include');
	$c_form->add_area_hidden_value('2','phpcmsaction', 'HTTPINDEX');
	$c_form->add_area_input_text('2', 'url','40', $MESSAGES['HTTP_SRC'][89],	''); //'Adresse:'
	$c_form->add_button('submit', 'wahl', $MESSAGES['HTTP_SRC'][90]); //hinzufügen
	$c_form->add_button('submit', 'wahl', $MESSAGES['HTTP_SRC'][21]); // weiter
	$c_form->compose_form();


	echo '<br />';
	$c_form2 = new form();
	$c_form2->set_bgcolor('#FCFCFC');
	$c_form2->set_border_color('#004400');
	$c_form2->set_width('500');
	$c_form2->set_left_size('80');

	$c_form2->add_area('2');
	$c_form2->set_area_title('2', $MESSAGES['HTTP_SRC'][111]);	// 'Einschluß'
	$i=0; $j=1;

	if(isset($session->vars['include'])) {
		$message = '<table border="0" cellspacing="1" cellpadding="0" width="405">';
		$message.= '<tr><td background="gif/indexer/h_trenner.gif" colspan="2"><img src="gif/indexer/nix.gif" width="2" height=1 border=0 vspace=0 hspace=0></td></tr>';
		foreach($session->vars['include'] as $addr) {
			$message.= '<tr>';
			$message.= '<td width="100%">'.$c_form2->normal_font.$MESSAGES['HTTP_SRC'][51].$j.': '.$addr.'</font></td>'; // Adresse
			$message.= '<form action="'.$session->write_link($PHP_SELF).'">';
			$message.= '<input type="hidden" name="phpcmsaction" value="HTTPINDEX">';
			$message.= '<input type="hidden" name="include" value="'.$i.'">';
			$message.= '<input type="hidden" name="action" value="delete_include">';
			$message.= '<td width="16"><input type="image" src="gif/indexer/delete.gif" width="16" height="16" alt="'.$MESSAGES['HTTP_SRC'][147].'" title="'.$MESSAGES['HTTP_SRC'][147].'" border="0"></td>'; // Einschluss löschen
			$message.= '</form>';
			$message.= '</tr>';
			$message.= '<tr><td background="gif/indexer/h_trenner.gif" colspan="2"><img src="gif/indexer/nix.gif" width="2" height=1 border=0 vspace=0 hspace=0></td></tr>';
			$i++; $j++;
		}
		$message.= '</table>';
		$c_form2->add_area_show_textarea('2', $message);
	}
	else {
		$c_form2->add_area_show_text('2', $MESSAGES['HTTP_SRC'][89], $MESSAGES['HTTP_SRC'][93]);//Adresse:noch keine Adresse erfasst.
	}
	$c_form2->compose_form();
	echo "\n".'</div><!-- output -->'."\n";

} // end continue_include

function delete_include() {

	global $session,$formdata;

	unset($session->vars['include'][$formdata->include]);
	if(count($session->vars['include']) <= 1 AND $session->vars['include'][0] == '' AND $session->vars['include'][1] == '' ) {
		unset($session->vars['include']);
	}
	else {
		$session->vars['include'] = explode(':=',implode(':=',$session->vars['include']));
	}
	continue_include('edit');

} // end delete_include

/****************************
 * Search- and Replace-Path *
 ****************************/

function continue_urlchange($message='') {

	global $session,$formdata,$MESSAGES;
	// von continue_include

	if (isset($formdata->wahl1) AND $formdata->wahl1 == $MESSAGES['HTTP_SRC'][21]) { // Weiter
			$session->vars['url_pattern'] = str_replace('\\\\','\\',$formdata->url_pattern);
			$session->vars['url_replacement'] = $formdata->url_replacement;
			server_options();
			return;
	}
	else {
		$url_pattern = isset($session->vars['editprofile']) ? $session->vars['editprofile']['url_pattern'] : '';
		$url_replacement = isset($session->vars['editprofile']) ? $session->vars['editprofile']['url_replacement'] : '';
	}

	echo '<div id="output">'."\n";
	$c_form = new form();
	$c_form->set_bgcolor('#FCFCFC');
	$c_form->set_border_color('#004400');
	$c_form->set_width('500');
	$c_form->set_left_size('100');

	$c_form->add_area('0');
	$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][148]); // Suchprofil erstellen - Schritt 5/7
	if($message=='') {
		/*
		In diesem Schritt können Sie einen regulären Ausdruck definieren,
		mit dem Teile der URLs nach dem Indizieren geändert werden.<p>
		Der HTTP-Indexer speichert absolute Links zu den gespiderten
		Dateien. Dadurch können beliebige Server gespidert und für eine Volltextsuche
		verwendet werden. Das Erstellen des Volltextindex auf einem anderen Server
		als wie dem öffentlich zugänglichen (z.B. auf einem lokalen Testserver) ist
		dadurch aber nicht möglich. Vor allem aus Performancegründen wäre dies aber
		wünschenswert. Dies wird durch die Einstellungen auf dieser Seite ermöglicht.<p>
		Sie können dazu ein Pattern (einen regulären Ausdruck) definieren. Alle URL-Teile auf
		die dieses Pattern passt, wird dann durch das Replacement ersetzt. Dazu wird
		die PHP-Funktion <a href="http://www.php.net/manual/de/function.preg-replace.php"
		target="_blank">preg_replace</a> verwendet. Beachten Sie dabei die spezielle
		Syntax für das Pattern. <p>Ein Beispiel:<br />
		Ihr Testserver im Intranet heißt "http://phpcms.de.local", der öffentlich zugängliche
		Server hat den Namen "http://phpcms.de". Zum Ersetzen von "phpcms.de.local" durch
		"phpcms.de" können Sie das<br />
		<b>Pattern "/phpcms\.de\.local/"</b><br />
		und das<br />
		<b>Replacement "phpcms.de"</b><br />
		verwenden.
		*/
		$temp = $MESSAGES['HTTP_SRC'][149];
	}
	else {
		$temp = $message;
	}
	$c_form->add_area_show_textarea('0', $temp);

	$c_form->add_area('1');
	$c_form->set_area_title('1', $MESSAGES['HTTP_SRC'][150]); // URLs ändern
	$c_form->add_area_hidden_value('1','phpcmsaction', 'HTTPINDEX');
	$c_form->add_area_hidden_value('1','action', 'continue_urlchange');
	$c_form->add_area_input_text('1', 'url_pattern','40', $MESSAGES['HTTP_SRC'][151],$url_pattern); // Pattern:
	$c_form->add_area_input_text('1', 'url_replacement','40', $MESSAGES['HTTP_SRC'][152],$url_replacement); // Replacement:
	$c_form->add_button('submit', 'wahl1', $MESSAGES['HTTP_SRC'][21]); // weiter
	$c_form->compose_form();
	echo "\n".'</div><!-- output -->'."\n";

} // end continue_urlchange

########################################################################
# Servereinstellungen
########################################################################

function server_options() {

	global $session,$formdata,$DEFAULTS,$MESSAGES;

	$message = '';
	if(isset($formdata->wahl2) AND $formdata->wahl2 == $MESSAGES['HTTP_SRC'][21]) {
		$cont = TRUE;
		$path_to_check = $DEFAULTS->DOCUMENT_ROOT.trim($formdata->savedata);

		if (!file_exists($path_to_check)) {
			$cont = FALSE;
			/*
			$message = 'Das von Ihnen angegebene Verzeichnis "'.trim($formdata->savedata).'" ist nicht angelegt. ';
			$message.= 'Wählen Sie ein anderes Verzeichnis, oder legen Sie das Verzeichnis an.';
			*/
			$message = $MESSAGES['HTTP_SRC'][113].trim($formdata->savedata).$MESSAGES['HTTP_SRC'][114];
		}

		if ($cont === TRUE AND !is_dir($path_to_check)) {
			$cont = FALSE;
			/*
			$message = 'Das von Ihnen angegebene Verzeichnis "'.trim($formdata->savedata).'" ist nicht angelegt. ';
			$message.= 'Es wurde eine Datei mit gleichem Namen gefunden! ';
			$message.= 'Wählen Sie ein anderes Verzeichnis, oder legen Sie das Verzeichnis an.';
			*/
			$message = $MESSAGES['HTTP_SRC'][113].trim($formdata->savedata).$MESSAGES['HTTP_SRC'][115];
		}

		# testfile anlegen
		if ($cont === TRUE) {
			@$fp = fopen($path_to_check.'/test.txt', 'wb+');
			if($fp !== FALSE) {
				# alles ok, hat geklappt.
				fclose ($fp);
				@unlink($path_to_check.'/test.txt');
			}
			else {
				$cont = FALSE;
				/*
				$message = 'Der Versuch einen Testdatei in das gewählte Datenverzeichnis "'.trim($formdata->savedata);
				$message.= '" zu schreiben schlug fehl. Das Verzeichnis ist aber vorhanden. Vermutlich fehlen ';
				$message.= 'die erforderlichen Schreibrechte, oder die Datei ist vorhanden und schreibgeschützt.';
				*/
				$message = $MESSAGES['HTTP_SRC'][116].trim($formdata->savedata).$MESSAGES['HTTP_SRC'][117];
			}
		}

		# check ob stopword vorhanden
		if ($cont === TRUE) {
			$path_to_check = $DEFAULTS->DOCUMENT_ROOT.trim($formdata->stopword);
			@$fp = fopen($path_to_check, 'rb');
			if($fp !== FALSE) {
				# alles ok, hat geklappt.
				fclose ($fp);
			}
			else {
				$cont = FALSE;
				/*
				$message = 'Die Stopwortdatei ist nicht vorhanden oder kann nicht gefunden werden. ';
				$message.= 'Legen Sie die angegebene Stopwortdatei an, oder stellen Sie sicher, dass Sie ';
				$message.= 'die Datei mit dem Pfad, absolut ausgehend vom Wurzelverzeichnis Ihres Webservers angegeben haben.';
				*/
				$message = $MESSAGES['HTTP_SRC'][118];
			}
		}

		if ($cont === TRUE AND $formdata->gzip == '1' AND !function_exists ('gzfile')) {
			$cont = FALSE;
			/*
			$message = 'Sie haben Komprimierung mit GZIP gewählt. Leider ist auf Ihrem Server die erforderliche ';
			$message.= 'Extension nicht installiert. Probieren Sie es bitte ohne Komprimierung.';
			*/
			$message = $MESSAGES['HTTP_SRC'][119];
		}

		# hier gehts weiter
		if($cont === TRUE) {
			$session->vars['gzip'] 			= $formdata->gzip;
			$session->vars['savedata'] 	= $formdata->savedata;
			$session->vars['wordlength'] 	= $formdata->wordlength;
			$session->vars['buffer'] 		= str_replace('.','',$formdata->buffer);
			$session->vars['buffer'] 		= str_replace(',','',$session->vars['buffer']);
			$session->vars['description'] = $formdata->description;
			$session->vars['stopword'] 	= $formdata->stopword;
			$temp = trim($formdata->nottoindex);
			if(strlen($temp) > 0) {
				if(substr($temp,-1) == ';') {
					$temp = substr($temp,0,-1);
				}
				$temp = explode(';',$temp);
				$session->vars['noextensions'] = $temp;
			}
			last_check();
			return;
		}
	} // end if
	else {
		$savedata = isset($session->vars['editprofile']) ? $session->vars['editprofile']['savedata'] : '/';
		$nottoindex = isset($session->vars['editprofile']) ? implode(';',$session->vars['editprofile']['noextensions']) : '.zip;.gif;.jpg;.css;.js;.gz;.pdf;.tar;.png';
		if (isset($session->vars['editprofile'])) {
			$gzip[1] = $session->vars['editprofile']['gzip'] ? ' checked' : '';
			$gzip[0] = $session->vars['editprofile']['gzip'] ? '' : ' checked';
		}
		else {
			$gzip[1] = '';
			$gzip[0] = ' checked';
		}
		$stopword = isset($session->vars['editprofile']) ? $session->vars['editprofile']['stopword'] : '/parser/include/stop.db';
		$wordlength = isset($session->vars['editprofile']) ? $session->vars['editprofile']['wordlength'] : '4';
		$buffer = isset($session->vars['editprofile']) ? $session->vars['editprofile']['buffer'] : '200000';
		$description = isset($session->vars['editprofile']) ? $session->vars['editprofile']['description'] : '360';
	}

	echo '<div id="output">'."\n";
	$c_form = new form();
	$c_form->set_bgcolor('#FCFCFC');
	$c_form->set_border_color('#004400');
	$c_form->set_width('500');
	//$c_form->set_left_size('150');

	$c_form->add_area('0');
	$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][120]); // Suchprofil erstellen - Schritt 6/7
	if ($message == '') {
		$message.= $MESSAGES['HTTP_SRC'][121];
	}
	$c_form->add_area_show_textarea('0', $message);


	$c_form->add_area('1');
	$c_form->set_area_title('1', $MESSAGES['HTTP_SRC'][122]); // Servereinstellungen für Volltextsuche
	$c_form->add_area_hidden_value('1','action', 'server_options');
	$c_form->add_area_hidden_value('1','phpcmsaction', 'HTTPINDEX');
	$c_form->add_area_input_text('1', 'savedata','30', $MESSAGES['HTTP_SRC'][123],$savedata);	//Datenverzeichnis
	$c_form->add_area_input_text('1', 'nottoindex','30', $MESSAGES['HTTP_SRC'][124],$nottoindex);	//Nicht zu indizierende Ext
		$message = '<input type="radio" name="gzip" value="1"'.$gzip[1].'> '.$MESSAGES[38].' '."\n"; // an
		$message.= '<input type="radio" name="gzip" value="0"'.$gzip[0].'> '.$MESSAGES[39].' '."\n"; // aus
	$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][125],	$message); // GZIP:
	$c_form->add_area_input_text('1', 'stopword','30', $MESSAGES['HTTP_SRC'][46],$stopword); // Stopwortdatei:
	$c_form->add_area_input_text('1', 'wordlength','10', $MESSAGES['HTTP_SRC'][47],$wordlength); // Minimale Wortlänge:
	$c_form->add_area_input_text('1', 'buffer','20', $MESSAGES['HTTP_SRC'][48],$buffer); // Puffergröße in Byte:
	$c_form->add_area_input_text('1', 'description','20', $MESSAGES['HTTP_SRC'][49],$description); // Beschreibungstext in Zeichen:

	$c_form->add_button('submit', 'wahl2', $MESSAGES['HTTP_SRC'][21]);
	$c_form->compose_form();
	echo "\n".'</div><!-- output -->'."\n";

} // end server_options

########################################################################
# Letzte Prüfung
########################################################################

function last_check() {

	global $session, $formdata, $MESSAGES;

	$message = '';

	if (isset($formdata->wahl3) AND $formdata->wahl3 === $MESSAGES['HTTP_SRC'][138]) {

		// delete profile when edited
		if(isset($session->vars['editprofile'])) {
			delete_profile($session->vars['editprofile']['profilname']);
			unset($session->vars['editprofile']);
		}

		// read profiles
		$profiles = read_profiles();
		$actual_profile_name = trim($formdata->filename);

		// check if profile with this name already exists
		if(!isset($profiles[$actual_profile_name])) {
			$profiles[$actual_profile_name]['host']			= $session->vars['host'];
			$profiles[$actual_profile_name]['gzip']			= $session->vars['gzip'];
			$profiles[$actual_profile_name]['savedata'] 	= $session->vars['savedata'];
			$profiles[$actual_profile_name]['robots']		= $session->vars['robots'];
			$profiles[$actual_profile_name]['meta']			= $session->vars['meta'];

			if(isset($session->vars['exclude'])) {
				$profiles[$actual_profile_name]['exklude']	= $session->vars['exclude'];
			}
			else {
				$profiles[$actual_profile_name]['exklude']	= Array();
			}

			if(isset($session->vars['include'])) {
				$profiles[$actual_profile_name]['include']	= $session->vars['include'];
			}
			else {
				$profiles[$actual_profile_name]['include']	= Array();
			}
			$profiles[$actual_profile_name]['url_pattern']		= $session->vars['url_pattern'];
			$profiles[$actual_profile_name]['url_replacement']	= $session->vars['url_replacement'];

			$profiles[$actual_profile_name]['noextensions']	= $session->vars['noextensions'];
			$profiles[$actual_profile_name]['stopword']		= $session->vars['stopword'];
			$profiles[$actual_profile_name]['wordlength']	= $session->vars['wordlength'];
			$profiles[$actual_profile_name]['buffer']		= $session->vars['buffer'];
			$profiles[$actual_profile_name]['description']	= $session->vars['description'];
			$profiles[$actual_profile_name]['meta_desc']   	= $session->vars['meta_desc'];
			write_profiles($profiles);
			show_list();
			return;
		}
		else {
			/*
			$message = 'Der von Ihnen gewählte Profilname wird bereits benutzt! ';
			$message.= 'Wählen Sie einen anderen Profilnamen!';
			*/
			$message = $MESSAGES['HTTP_SRC'][126];
		}
	}

	echo '<div id="output">'."\n";
	$c_form = new form();
	$c_form->set_bgcolor('#FCFCFC');
	$c_form->set_border_color('#004400');
	$c_form->set_width('500');
	$c_form->set_left_size('150');

	$c_form->add_area('0');
	$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][127]); // Suchprofil erstellen - Schritt 7/7
	if ($message == '') {
		/*
		$message = 'Hier haben Sie nun noch einmal die Möglichkeit, die eingegebenen Daten zu prüfen. ';
		$message.= 'Sollten Sie Fehler feststellen, erfassen Sie bitte die Daten neu, indem Sie ';
		$message.= 'den Wizard durch Klick auf "Suchprofil erstellen" neu starten.<p>';
		$message.= 'Sind die Daten in Ordnung, dann speichern Sie das eben erstellte Suchprofil unter einem ';
		$message.= 'Namen Ihrer Wahl. Hinweis: Als Profilname hat sich die Startadresse bewährt!';
		*/
		$message = $MESSAGES['HTTP_SRC'][128];
		$show=TRUE;
	}

	$c_form->add_area_show_textarea('0', $message);

	if ( $show === TRUE AND count ( $session->vars['host'] ) > 0 ) {
		$c_form->add_area('1');
		$c_form->set_area_title('1', $MESSAGES['HTTP_SRC'][92]); // Server
		$i=1;

		foreach($session->vars['host'] as $server) {
			$c_form->add_area_show_text('1', $MESSAGES['HTTP_SRC'][51].$i.':',	'http://'.$server); //Adresse
			$i++;
		}

		$c_form->add_area('2');
		$c_form->set_area_title('2', $MESSAGES['HTTP_SRC'][129]); //Robots/META

		if ($session->vars['robots'] === TRUE) {
			$message = $MESSAGES['HTTP_SRC'][130]; // Die Datei "robots.txt" wird berücksichtigt
		}
		else {
			$message = $MESSAGES['HTTP_SRC'][131]; //Die Datei "robots.txt" wird <b>nicht</b> berücksichtigt.
		}

		if ($session->vars['meta'] === TRUE) {
			$message.= $MESSAGES['HTTP_SRC'][132]; //"robot-META-TAGS" in HTML-Dateien werden berücksichtigt.
		}
		else {
			$message.= $MESSAGES['HTTP_SRC'][133]; //"robot-META-TAGS" in HTML-Dateien werden <b>nicht</b> berücksichtigt.
		}
		if ($session->vars['meta_desc'] === TRUE) {
			$message.= $MESSAGES['HTTP_SRC'][134]; //"desc-META-TAGS" in HTML-Dateien werden berücksichtigt.
		}
		else {
			$message.= $MESSAGES['HTTP_SRC'][135]; //"desc-META-TAGS" in HTML-Dateien werden <b>nicht</b> berücksichtigt.
		}
		$c_form->add_area_show_textarea('2', $message);

		if (isset($session->vars['exclude']) AND count($session->vars['exclude']) > 0) {
			$c_form->add_area('3');
			$c_form->set_area_title('3', $MESSAGES['HTTP_SRC'][52]); // 'Ausschlüsse'
			$i=1;
			foreach($session->vars['exclude'] as $addr) {
				$c_form->add_area_show_text('3', $MESSAGES['HTTP_SRC'][51].$i.':',	$addr); // Adresse
				$i++;
			}
		}

		if (isset($session->vars['include']) AND count($session->vars['include']) > 0) {
			$c_form->add_area('4');
			$c_form->set_area_title('4', $MESSAGES['HTTP_SRC'][54]); //Einschlüsse
			$i=1;
			foreach($session->vars['include'] as $addr) {
				$c_form->add_area_show_text('4', $MESSAGES['HTTP_SRC'][51].$i.':',	$addr); // Adresse
				$i++;
			}
		}

		$c_form->add_area('5');
		$c_form->set_area_title('5',$MESSAGES['HTTP_SRC'][150]); //URLs ändern
		$c_form->add_area_show_text('5',$MESSAGES['HTTP_SRC'][151],$session->vars['url_pattern']); // 'Pattern:'
		$c_form->add_area_show_text('5',$MESSAGES['HTTP_SRC'][152],$session->vars['url_replacement']); // 'Replacement:'

		$c_form->add_area('6');
		$c_form->set_area_title('6', $MESSAGES['HTTP_SRC'][122]); //Servereinstellungen
		$c_form->add_area_show_text('6', $MESSAGES['HTTP_SRC'][123],	$session->vars['savedata']);	// 'Datenverzeichnis:'
		if (isset($session->vars['noextensions']) AND count($session->vars['noextensions']) > 0) {
			$ext = implode(';',$session->vars['noextensions']);
			$c_form->add_area_show_text('6', $MESSAGES['HTTP_SRC'][124],	$ext); // 'Extensionen:'
		}
		if ($session->vars['gzip'] == 1) {
			$c_form->add_area_show_text('6', $MESSAGES['HTTP_SRC'][125],$MESSAGES[38]); //'Komprimierung:'on
		}
		else {
			$c_form->add_area_show_text('6',$MESSAGES['HTTP_SRC'][125],$MESSAGES[39]);	//'Komprimierung:'off
		}
		$c_form->add_area_show_text('6', $MESSAGES['HTTP_SRC'][46],		$session->vars['stopword']); // 'Stopwortdatei:'
		$c_form->add_area_show_text('6', $MESSAGES['HTTP_SRC'][47],	$session->vars['wordlength']); // 'Min. Wortlänge:'
		$c_form->add_area_show_text('6', $MESSAGES['HTTP_SRC'][48],		$session->vars['buffer']); // 'Puffergröße:'
		$c_form->add_area_show_text('6', $MESSAGES['HTTP_SRC'][49],$session->vars['description']); // 'Beschreibungstext:'

	}

	$c_form->add_area('7');
	$c_form->set_area_title('7', $MESSAGES['HTTP_SRC'][136]); // Speichern unter
	$c_form->add_area_input_text('7', 'filename','30', $MESSAGES['HTTP_SRC'][137],	'http://'.$session->vars['host'][0]);// Profilname:
	$c_form->add_area_hidden_value ('7','phpcmsaction', 'HTTPINDEX');
	$c_form->add_area_hidden_value ('7','action', 'save_profile');
	$c_form->add_button('submit', 'wahl3', $MESSAGES['HTTP_SRC'][138]);//speichern
	$c_form->compose_form();
	echo "\n".'</div><!-- output -->'."\n";

} // end last_check


########################################################################
# Adresscheck
########################################################################

function check_adress() {

	global $session,$formdata, $MESSAGES;

	$formdata->url = trim($formdata->url);

	$temp = strtoupper(substr($formdata->url,0,7));

	if ($temp != 'HTTP://') {
		/*
		$message = 'Sie haben eine falsche Adresse angegeben:<br />';
		$message.= '<b>'.$formdata->url.'</b><br />';
		$message.= 'Eine korrekte Adresse beginnt immer mit "http://" oder "https://"!';
		*/
		return $MESSAGES['HTTP_SRC'][139].$formdata->url.$MESSAGES['HTTP_SRC'][140];
	}

	$temp = substr($formdata->url,7);

	if(strstr($temp,'/')) {
		$host = substr($temp,0, strpos($temp,'/'));
		$path = substr($temp,strpos($temp,'/'));
		if(!strstr($path,'.') AND substr($path,-1) != '/') {
			$path = $path.'/';
		}
	}
	else {
		$host = $temp;
		$path = '/';
	}

	$test = get_http($host,$path,$page);

	if (strlen($test) < 2) {
		/*
		$message = 'Sie haben eine falsche Adresse angegeben, oder der gewählte Server ist nicht erreichbar:<br />';
		$message.= '<b>'.$formdata->url.'</b><br />';
		*/
		return $MESSAGES['HTTP_SRC'][141].$formdata->url.$MESSAGES['HTTP_SRC'][142];
	}

	$ret['host'] = $host;
	$ret['path'] = $path;
	return $ret;

} // end check_adress

?>