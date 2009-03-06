<?php
/* $Id: class.lib_indexer_analyse_phpcms.php,v 1.1.1.1.2.17 2006/06/18 18:07:30 ignatius0815 Exp $ */
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
   |    Markus Richert (e157m369)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

########################################################################
# Auswahlliste für Prüfung der Suche ausgeben
########################################################################

function analyse_words()
	{
	global $DEFAULTS, $session, $formdata, $MESSAGES;

	# alles löschen
	unset_all();

	# Liste holen
	$profiles = read_profiles();

	# In den Profilen nach bereits distributierten Dateien suchen
	$option = '';
	while (list($name,$v) = each($profiles))
		{
		if (file_exists($DEFAULTS->DOCUMENT_ROOT.$v['savedata'].'/data.db') OR
		    file_exists($DEFAULTS->DOCUMENT_ROOT.$v['savedata'].'/data.gz')) {
			$option[$name] = $name;
			}
		}


	echo '<div id="output">'."\n";
	$c_form = new form();
	$c_form->set_bgcolor('#FCFCFC');
	$c_form->set_border_color('#004400');
	$c_form->set_width('100%');

	$values['10']	= '10';
	$values['20']	= '20';
	$values['50']	= '50';
	$values['100']	= '100';
	$values['200']	= '200';
	$values['500']	= '500';
	$values['1000']	= '1000';
	$values['5000']	= '5000';

	$c_form->add_area('0');
	$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][01]); //Wortanalyse
	if ($option != '')
		{
		// Die Wortanalyse dient zum sinnvollen Aufbau der Stopwortdatei.
		// Es werden die ersten 50 Wörter, nach Häufigkeit des Vorkommens
		// ausgegeben. Wählen Sie einen Volltextindex zur Analyse aus!
		$c_form->add_area_show_textarea('0', $MESSAGES['HTTP_SRC'][02]);

		$c_form->add_area('1');
		$c_form->add_area_hidden_value('1','phpcmsaction', 'HTTPINDEX');
		$c_form->add_area_hidden_value('1','action', 'analyse_now');
		$c_form->set_area_title('1', $MESSAGES['HTTP_SRC'][03]); // Auswahl
		$c_form->add_area_select_box('1', 'profile', $MESSAGES['HTTP_SRC'][04], $option);	//Volltextindex
		$c_form->add_area_select_box('1', 'values', $MESSAGES['HTTP_SRC'][05], $values, '20'); // Anzahl der Wörter
		$c_form->add_button('submit', 'suchen', $MESSAGES['HTTP_SRC'][06]); //analysieren
		}
	else
		{
		// Es wurde kein Volltextindex gefunden. Erstellen Sie erst einen Volltextindex!
		$message = $MESSAGES['HTTP_SRC'][07];
		$c_form->add_area_show_textarea('0', $message);
		}

	$c_form->compose_form();
	echo "\n".'</div><!-- output -->'."\n";
	}


########################################################################
# Analyse durchführen
########################################################################

function analyse_now()
	{
	global $DEFAULTS, $session, $formdata, $MESSAGES;

	# Profil holen
	$profiles = read_profiles();
	$actual_profile = $profiles[$formdata->profile];

	# Daten einlesen
	# Index einlesen
	$SEARCHDATADIR = $DEFAULTS->DOCUMENT_ROOT.$actual_profile['savedata'];
	if ($actual_profile['gzip'] == '1')
		{
		$GZ_READ_BYTES = 2000000;
		$fp = gzopen($SEARCHDATADIR.'/data.gz', 'rb');
		$temp = gzread ($fp, $GZ_READ_BYTES);
		$DataArray = explode ("\n",$temp);
		unset($temp);

		$WordDB = $SEARCHDATADIR.'/words.gz';
		$fp = gzopen($WordDB, 'rb');
		$temp = gzread ($fp, $GZ_READ_BYTES);
		$WordIndex = explode ("\n",$temp);
		unset($temp);

		$fp = gzopen($SEARCHDATADIR.'/files.gz', 'rb');
		$temp = gzread ($fp, $GZ_READ_BYTES);
		$FileDB = explode ("\n",$temp);
		unset($temp);
		}
	else
		{
		$DataArray = file ($SEARCHDATADIR.'/data.db');
		$WordIndex = file ($SEARCHDATADIR.'/words.db');
		$FileDB = file ($SEARCHDATADIR.'/files.db');
		}


	# Häufigkeit einlesen (aufaddieren)
	$count_array = Array();
	$count_data = count($DataArray)-1; // last array element is void (E_ALL fix)

	for ($i=0; $i<$count_data; $i++) {
		$count_array[$i] = 0;
		$temp = explode('+',$DataArray[$i]);
		for ($l=0; $l < count($temp); $l++) {
			list ($PageIndex, $PageCount) = explode( '*', $temp[$l]);
			$count_array[$i] = $count_array[$i]+$PageCount;
		}
	}

	# Zielarray befüllen
	arsort($count_array);
	$result = Array();
	$i = 0;
	while(list($k,$v) = each($count_array))
		{
		if ($i == $formdata->values)
			break;
		$result[$i]['word'] = $WordIndex[$k];
		$result[$i]['count'] = $v;
		$i++;
		}
	$sum_values = $i;

	# Ausgabe
	echo '<div id="output">'."\n";
	$c_form = new form();
	$c_form->set_bgcolor('#FCFCFC');
	$c_form->set_border_color('#004400');
	$c_form->set_width('100%');

	$c_form->add_area('0');
	$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][01]); //Wortanalyse

	$message = '<table border="0" cellspacing="1" cellpadding="0" width="390">';
		$message.= '<tr>';
		$message.= '<td align="right">'.$c_form->normal_font.'<b>'.$MESSAGES['HTTP_SRC'][08].'</b></font></td>'; // Rang
		$message.= '<td width="100%">'.$c_form->normal_font.'&nbsp;&nbsp;<b>'.$MESSAGES['HTTP_SRC'][09].'</b></font></td>'; // Wort
		$message.= '<td align="right">'.$c_form->normal_font.'<b>'.$MESSAGES['HTTP_SRC'][10].'</b></font></td>'; // Vorkommen
		$message.= '</tr>'."\n";

	for ($i=0; $i< $sum_values; $i++)
		{
		$message.= '<tr><td colspan=3 background="gif/indexer/h_trenner.gif"><img src="gif/indexer/nix.gif" width="2" height=1 border=0 vspace=0 hspace=0></td></tr>';
		$message.= '<tr>';
		$message.= '<td align="right">'.$c_form->normal_font.($i+1).'.</font></td>';
		$message.= '<td width="100%">'.$c_form->normal_font.'&nbsp;&nbsp;'.$result[$i]['word'].'</font></td>';
		$message.= '<td align="right">'.$c_form->normal_font.$result[$i]['count'].'</font></td>';
		$message.= '</tr>'."\n";
		}
	$message.= '</table>';
	$c_form->add_area_show_textarea('0', $message);
	$c_form->compose_form();
	echo "\n".'</div><!-- output -->'."\n";
	}

?>