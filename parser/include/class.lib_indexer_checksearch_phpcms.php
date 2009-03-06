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
   |    Beate Paland (beate76)
   |    Henning Poerschke (hpoe)
   |    Markus Richert (e157m369)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

// #######################################################################
// Auswahlliste für Prüfung der Suche ausgeben
// #######################################################################
function start_check_search() {
	global $DEFAULTS, $session, $formdata, $MESSAGES;
	// alles löschen
	unset_all();
	// Liste holen
	$profiles = read_profiles();
	// In den Profilen nach bereits distributierten Dateien suchen
	$option = '';
	while (list($name, $v) = each($profiles)) {
		if (file_exists($DEFAULTS->DOCUMENT_ROOT . $v['savedata'] . '/data.db') OR
		    file_exists($DEFAULTS->DOCUMENT_ROOT . $v['savedata'] . '/data.gz')) {
			$option[$name] = $name;
		}
	}

	echo '<div id="output">'."\n";
	$c_form = new form();
	$c_form->set_bgcolor('#FCFCFC');
	$c_form->set_border_color('#004400');
	$c_form->set_width('100%');

	$c_form->add_area('0');
	$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][11]); //Test eines Volltextindex
	if ($option != '') {
		$message = $MESSAGES['HTTP_SRC'][12]; //Wählen Sie einen Volltextindex zum Test aus!
		$c_form->add_area_show_textarea('0', $message);

		$c_form->add_area('1');
		$c_form->add_area_hidden_value('1','phpcmsaction', 'HTTPINDEX');
		$c_form->add_area_hidden_value('1', 'action', 'search_now');
		$c_form->add_area_hidden_value('1', 'phpcms_result_start', '0');
		$c_form->add_area_hidden_value('1', 'phpcms_result_count', '5');
		$c_form->set_area_title('1', $MESSAGES['HTTP_SRC'][03]); //Auswahl
		$c_form->add_area_select_box('1', 'profile', $MESSAGES['HTTP_SRC'][04], $option); //Volltextindex
		$c_form->add_area_input_text('1', 'query', '20', $MESSAGES['HTTP_SRC'][13], ''); //Suchbegriff:
		$c_form->add_button('submit', 'suchen', $MESSAGES['HTTP_SRC'][14]); //suchen
	} else {
		// Es wurde kein Volltextindex gefunden. Erstellen Sie erst einen Volltextindex!
		$c_form->add_area_show_textarea('0', $MESSAGES['HTTP_SRC'][07]);
	}

	$c_form->compose_form();
	echo "\n".'</div><!-- output -->'."\n";
}
// #######################################################################
// Zeitmessung für Suche
// #######################################################################
function getmicrotime() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}
// #######################################################################
// Suche durchführen
// #######################################################################
function search_now() {
	global $DEFAULTS, $session, $formdata, $MESSAGES, $PHP_SELF;
	// Profil ermitteln
	$profiles = read_profiles();
	$actual_profile = $profiles[$formdata->profile];

	$gifpath = dirname($PHP_SELF).'/gif/indexer/';

// Creating a faked Parser-Environment for class.parser_search

	// Create the delivery and system-vars
	$GLOBALS['_GET_POST']['query'] = $formdata->query;
	$GLOBALS['_GET_POST']['datadir'] = $actual_profile['savedata'];
	$GLOBALS['_GET_POST']['phpcms_result_start'] = $formdata->phpcms_result_start;
	$GLOBALS['_GET_POST']['phpcms_result_count'] = $formdata->phpcms_result_count;

	$GLOBALS['DEFAULTS']->SEARCHTERM_MIN_LENGTH = $actual_profile['wordlength'];

	// Create search templates
	$Template = 'INDEXER';

	$GLOBALS['MENU']->TEMPLATE->content->{'SEARCH.'.$Template.'.PRE'} = array(
		'<h3 style="margin-top: 12"><b>Ergebnis der Suche:</b></h3>',
		'<p style="font-size: 11px">Suchzeit: <SEARCH_TIME> s<br />',
		'Anzahl der gefundenen Begriffe: <WORD_COUNT><br />',
		'<TERM_EXCLUDED_PRE><SEARCH_TERM_EXCLUDED><TERM_EXCLUDED_PAST>',
		'Anzahl der gefundenen Seiten: <PAGE_COUNT><br />',
		'Suchwort(e): <QUERY_TERM></p>',
		'<table cellpadding="3" cellspacing="0" border="0" width="100%">');

	$GLOBALS['MENU']->TEMPLATE->content->{'SEARCH.'.$Template.'.NORMAL'} = array(
		'<tr><td bgcolor="#EBEBEB"><b>{NUMBER}. <a href="{URL}" target="_blank">{TITLE}</a></b></td></tr>',
		'<tr><td><p>Ranking: {RANKING}</p></td></tr>',
		'<tr><td><p>{TEXT} ...</p></td></tr>',
		'<tr><td><table border="0" cellspacing="0" cellpadding="0"><tr><td valign="top" nowrap>Gefundene Begriffe:</td><td>&nbsp;&nbsp;</td>',
		'<td valign="top" nowrap width="100%">{FWORDS}</td></tr></table></td></tr>',
		'<tr><td colspan="2">&nbsp;</td></tr>');

	$GLOBALS['MENU']->TEMPLATE->content->{'SEARCH.'.$Template.'.PAST'} = array(
		'</table>');

	// Create search tags
	$tags[] = array('<SEARCH_TIME>',          false);
	$tags[] = array('<WORD_COUNT>',           false);
	$tags[] = array('<PAGE_COUNT>',           false);
	$tags[] = array('<SEARCH_TERM_EXCLUDED>', false);
	$tags[] = array('<TERM_EXCLUDED_PRE>',    'Der Begriff <span style="color:darkorange;font-weight:bold">');
	$tags[] = array('<TERM_EXCLUDED_PAST>',   '</span> wurde <span style="color:darkorange;font-weight:bold">nicht berücksichtigt</span>, da er sehr häufig vorkommt.<br />');
	$tags[] = array('<QUERY_TERM>',           false);

	$tags[] = array('<NO_DATA_DIR>',          '<p>&nbsp;</p><p><span style="color:#DC143C;font-weight:bold">Sie haben kein Datenverzeichnis definiert!<br />You did not specify any data directory!</span></p>');
	$tags[] = array('<NO_SEARCH_TERM>',       '<p>&nbsp;</p><p><span style="color:#DC143C;font-weight:bold">Sie haben keinen Suchbegriff eingegeben!</span><br />Geben Sie einen oder mehrere Suchbegriffe ein und versuchen Sie es nochmals!<br /><br /><span style="color:#DC143C;font-weight:bold">You did not enter any search term!</span> <br />Please enter one or several search terms, and try again.</p>');
	$tags[] = array('<SHORT_SEARCH_TERM>',    '<p>&nbsp;</p><p><span style="color:#DC143C;font-weight:bold">Sie haben einen zu kurzen Suchbegriff eingegeben.</span><br />Ihr Suchbegriff sollte wenigstens <strong>'.$actual_profile['wordlength'].' Zeichen</strong> lang sein.</span><br /><br /><span style="color:#DC143C;font-weight:bold">The search term you entered is too short!</span><br />Your search term should be at least <strong>'.$actual_profile['wordlength'].' character</strong> in length.</p>');
	$tags[] = array('<SEARCH_TERM_NONO>',     '<p>&nbsp;</p><p><em style="color:#DC143C;font-weight:bold">Danach</em> werden Sie wohl woanders suchen müssen!<br /><em style="color:#DC143C;font-weight:bold">This</em> you will have to look for someplace else!</p>');
	$tags[] = array('<NO_SEARCH_RESULT>',     '<br /><p><span style="color:black;font-weight:bold">Leider hat Ihre Suche kein Ergebnis geliefert.</span><br />Bitte überprüfen Sie Ihren Suchbegriff und starten Sie eine neue Suche.<br /><br /><span style="color:black;font-weight:bold">Your search did not yield any results.</span><br />Please try another search term.</p>');
	$tags[] = array('<SEARCH_PREV>',          '&laquo;&laquo; prev &laquo;&laquo;');
	$tags[] = array('<SEARCH_MIDDLE>',        '&nsbp;|&nsbp;');
	$tags[] = array('<SEARCH_NEXT>',          '&raquo;&raquo; next &raquo;&raquo;');
	$tags[] = array('<RANK_1>',               '<img src="'.$gifpath.'einstern.gif" border="0" width="15" height="15">');
	$tags[] = array('<RANK_2>',               '<img src="'.$gifpath.'zweistern.gif" border="0" width="30" height="15">');
	$tags[] = array('<RANK_3>',               '<img src="'.$gifpath.'dreistern.gif" border="0" width="45" height="15">');
	$tags[] = array('<RANK_4>',               '<img src="'.$gifpath.'vierstern.gif" border="0" width="60" height="15">');
	$tags[] = array('<RANK_5>',               '<img src="'.$gifpath.'fuenfstern.gif" border="0" width="75" height="15">');

	$tags[] = array('$self',                  $GLOBALS['_SERVER']['PHP_SELF'].'?phpcmsaction=HTTPINDEX&select=&callback=&action=search_now&profile='.$formdata->profile);
	$tags[] = array('$home',                  $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->SCRIPT_PATH);
	$GLOBALS['PAGE']->tagfile->tags = $tags;

	// Load the needed parts of class.parser_template in a new class to fake them also
	class SEARCH_TEMPLATE {
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
	}
	$GLOBALS['DEFAULTS']->TEMPLATE = new SEARCH_TEMPLATE;

// end of faking the Parser

	// now load the search class, that for we do all this ;-)
	include(PHPCMS_INCLUDEPATH.'/class.parser_search_phpcms.php');
	$SEARCH_RESULTS = new SEARCH_RESULTS;
	$SEARCH_RESULTS->parse_search_results($Template);

	// implode the received array to a string
	$message = implode("\n", $SEARCH_RESULTS->ReturnArray);

	foreach($GLOBALS['PAGE']->tagfile->tags as $value) {
		if($value[1] !== false) {
			$message = str_replace($value[0], $value[1], $message);
		}
	}

	echo '<br /><style type="text/css"><!-- #phpcms_search_prev {text-align: left}; #phpcms_search_next {text-align: right} --></style>';
	$c_form = new form();
	$c_form->set_bgcolor('#FCFCFC');
	$c_form->set_border_color('#004400');
	$c_form->set_width('100%');

	$c_form->add_area('0');
	$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][11]); //Test eines Volltextindex

	$c_form->add_area_show_textarea('0', $message);
	$c_form->compose_form();
}

?>
