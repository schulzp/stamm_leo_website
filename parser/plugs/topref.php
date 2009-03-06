<?php
/* $Id: topref.php,v 1.1.2.16 2006/06/18 18:10:47 ignatius0815 Exp $ */
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
   |    Henning Poerschke (hpoe)
   |    Martin Jahn (mjahn)
   |    Beate Paland (beate76)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

/* topref.php  phpCMS TopRef v1.2  17.04.03
---------------------------------------------------------------------
NOTE: phpCMS TopRef is designed to be used with phpCMS 1.2 or higher
---------------------------------------------------------------------


USAGE NOTES (en)

0. Implementing topref.php

0.1 Turn on "Referrer Logging" under Configuration within the GUI.


You may use topref.php either with PAX or as a plug-in.

1.0 Setting within topref.php

1.1 Using topref.php with PAX

1.1.1 Set: $display_mode = 'pax';

1.1.2 Put a PAXPHP call exactly where you want the output to display in your content file:

<!-- PAXPHP topref
include ('/server/root/htdocs/parser/plugs/topref.php');
PAXPHP topref -->

Using PAX offers the advantage that your page may still be dynamically cached.


1.2 Using topref.php as a plug-in

1.2.1 Set: $display_mode = 'plugin';

1.2.2 Put a plug-in call to topref.php somewhere near the top of your content file:

{PLUGIN FILE="$plugindir/topref.php" TYPE="DYNAMIC"}

1.2.3 Within the {CONTENT} section of your content file put these tags:

<!-- PLUGIN:TOPREF show='topref' -->   (This displays the list of top referrers.)

<!-- PLUGIN:TOPREF show='lastref' -->   (This displays the list of last referrers.)


2. Configuration

2.1 Display Options

2.1.1 Top Referrers: set $show_top to the number of top referrers you want displayed

2.1.2 Last Referrers: set $show_last to the number of last referrers you want displayed

2.1.3 Time of Last Referrers: set $show_time to "true" (w/o quotes) if you want to display
the visiting time and date in the title attribute in the last referrer table

2.1.3.1 Use $time_format to format the time and date display


2.2 Filter Options

2.2.1 $domains_excluded lets to define a list of domains (or parts thereof) that you do not
wish to display in your top last and last referrer tables


2.3 Miscellaneous/Debugging

2.3.1 Set $show_duration to "true" (w/o quotes) if you want to display the amount of time
it takes to process the referrer data.

-------------------------------------------
HINWEISE ZUR BENUTZUNG (de)
-------------------------------------------

0. Verwendung von topref.php

0.1 Im GUI unter Konfiguration  "Referrer Logging" aktivieren.


topref.php kann entweder mit PAX, als Script oder als Plug-In verwendet werden.

1.0 Einstellungen innerhalb topref.php

1.1 Verwendung von topref.php mit PAX

1.1.1 Einstellung: $display_mode = 'pax';

1.1.2 Einen PAXPHP Aufruf an der Stelle innerhalb der Content-Datei platzieren, an der die Ausgabe erscheinen soll:

<!-- PAXPHP topref
include ('/server/root/htdocs/parser/plugs/topref.php');
PAXPHP topref -->

Die Einbindung mit PAX bietet den Vorteil, dass die entsprechende Seite noch dynamisch gecachet werden kann.


1.2 Verwendung von  topref.php als Plug-In

1.2.1 Einstellung: $display_mode = 'plugin';

1.2.2 Einen Plug-In Aufruf am Beginn der Content-Datei platzieren:

{PLUGIN FILE="$plugindir/topref.php" TYPE="DYNAMIC"}

1.2.3 Innerhalb des {CONTENT} Feldes der Content-Datei diese Tags platzieren:

<!-- PLUGIN:TOPREF show='topref' -->   (Zeigt die Liste der Top-Referrer.)

<!-- PLUGIN:TOPREF show='lastref' -->   (Zeigt die Liste der letzten Referrer.)


2. Konfiguration

2.1 Darstellungsoptionen

2.1.1 Top Referrer: Anzahl der darzustellenden Top-Referrer in $show_top eintragen

2.1.2 Letzte Referrer: Anzahl der darzustellenden Letzten Referrer in $show_last eintragen

2.1.3 Zeitpunkt der Letzten Referrer: setze $show_time auf "true" (ohne Anführungszeichen)
wenn in der Liste der Letzten Referrer Datum und Uhrzeit des Seitenaufrufes im Title Attribut
angezeigt werden sollen

2.1.3.1 Verwende $time_format zur Einstellung des Datumsformats


2.2 Filteroptionen

2.2.1 In $domains_excluded kann eine Liste von Domains definiert werden, die nicht als
Referrer angezeigt werden sollen.


2.3 Verschiedenes/Debugging

2.3.1 Setze $show_duration auf "true" (ohne Anführungszeichen), um die für die Auswertung der
Referrer Daten benötigte Zeit anzuzeigen.

*/

if(isset($DEFAULTS->REFERRER) AND $DEFAULTS->REFERRER == 'on') {

	$display_mode = 'plugin'; // plugin or pax

	$show_top = 5; // Number of top referrers to display
	$show_last = 5; // Number of last referrers to display

	$show_time = true; // display date/time of last referrers
	$time_format = "j.n.y - H:i:s"; // de-de (29.2.04 - 13:15:59)
	//$time_format = "n/j/y - H:i:s"; // en-us (2/29/04 - 13:15:59)


	// A (lowercase!) list of referres you do not want to display
	$domains_excluded = array(
							"none", // do not show hits that did not come from a referrer
							//"localhost",
							"example.com",
							);


	$show_duration = true; // false or true

	// in dieser Datei werden die Daten gespeichert: benötigt Lese und Schreibrechte
	$datafile = $DEFAULTS->RefLog;

	if($show_duration) $ref_start = get_microtime();
	if(is_file($datafile)) {
		$input = file($datafile);
		$ref_data = unserialize($input[0]);

		if(gettype($ref_data) == 'array') {
			arsort($ref_data);
			$ix = 0;
			foreach($ref_data as $ref_domain=>$ref_vars) {

				for($iex=0; $iex<count($domains_excluded); $iex++) {
						if(strstr(strtolower($ref_domain), $domains_excluded[$iex])) {
							$ref_domain = '';
						}
					}
				if($ref_domain != '') {
					if ($ix<$show_top) {
						if($ref_domain != 'none') {
							$ref_domain = str_replace("www.", "", $ref_domain);

							$text_top[$ix] = "\n".'<td>'.($ix+1).'.</td><td><a href="http://'.htmlentities(stripslashes($ref_vars['p'])).'">'.htmlentities($ref_domain).'</a></td><td>'.$ref_vars['c'].'</td>';
						} else {
							$text_top[$ix] = "\n".'<td>'.($ix+1).'</td><td>Direct Request</td><td>'.$ref_vars['c'].'</td>';
						}
					}
					if($ref_domain != 'none') {
						$last_ref[$ref_domain]['t'] = $ref_vars['t'];
						$last_ref[$ref_domain]['p'] = $ref_vars['p'];
					}
					$ix++;
				}
			}
		if(gettype($last_ref) == 'array') {
			arsort($last_ref);
			$i = 0;
			foreach($last_ref as $domain=>$val) {
				if($i<$show_last AND $domain != 'none') {
					if($show_time == true) {
						$title = date($time_format, $val['t']);
					} else {
						$title = htmlentities($val['p']);
					}
					$text_last[$i] = "\n".'<td>'.($i+1).'.</td><td><a href="http://'.htmlentities(stripslashes($val['p'])).'" title="'.$title.'">'.htmlentities($domain).'</a></td>'."\n";
					$i++;
				}
			}
		}

	/* _/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
		TOP REFERRRER TABLE
		_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/*/

		$ix = min($ix,$show_top);

		$display_top = "\n".'<!-- phpCMS TopRef v1.1 -->';
		if($show_top > 0 ) {
		$display_top .= "\n".'<table width="65%" border="1" cellspacing="0" cellpadding="4" >'."\n".
			'<tr><td colspan="3" style="font-weight:bold;font-size:120%" class="emline">';

			if($show_top > $ix) {
				$display_top .= 'Top '.$ix.' Referrers';
			} else {
				$display_top .= 'Top '.$show_top.' of '.count($ref_data).' Referrers';
			}
			$display_top .= ' to '.$_SERVER["SERVER_NAME"].'</td></tr>'."\n".
				'<tr><th width="20">No.</th><th>URL</th><th>Hits</th></tr>'."\n";

			for ($xtmp=0; $xtmp < $ix; $xtmp++) {
				$display_top .= '<tr>'.$text_top[$xtmp].'</tr>'."\n";
			}
			$display_top .= '</table>'."\n";

			if($display_mode == 'pax') {
				echo $display_top;
			} else {
				$current = count($Tags);
				$Tags[$current][0] = "<!-- PLUGIN:TOPREF show='topref' -->";
				$Tags[$current][1] = $display_top;
			}
		}

	/* _/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/
		LAST REFERRRER TABLE
		_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/_/*/

		$i = min($i,$show_last);

		if($show_last > 0 ) {
			$display_last = "\n".'<table width="65%" border="1" cellspacing="0" cellpadding="4" >'."\n".
				'<tr><td colspan="2" style="font-weight:bold;font-size:120%" class="emline">';
				if($show_last >= $i) {
					$display_last .= 'Last '.$i.' Referrers';
				} else {
					$display_last .= 'Last '.$show_last.' Referrers';
				}
			$display_last .= ' to '.$_SERVER["SERVER_NAME"].'</td></tr>'."\n".
				'<tr><th width="20">No.</th><th>URL</th></tr>'."\n";

			for ($xtmp=0; $xtmp < $i; $xtmp++) {
				$display_last .= '<tr>'.$text_last[$xtmp].'</tr>'."\n";
			}
			$display_last .= '</table>'."\n";

			if($show_duration) $ref_stop = get_microtime();

			if($display_mode == 'pax') {
				echo '<br /><br />'.$display_last;
				if($show_duration) echo '<p><small>Dauer der Auswertung: '.($ref_stop-$ref_start).'s</small></p>';
			} elseif($display_mode == 'plugin') {
				$current = count($Tags);
				$Tags[$current][0] = "<!-- PLUGIN:TOPREF show='lastref' -->";
				if($show_duration) $display_last .= '<p><small>Duration: '.($ref_stop-$ref_start).'s</small></p>';
				$Tags[$current][1] = $display_last;
			}
		}
		}

	}
}

?>