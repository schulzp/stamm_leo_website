<?php
/* $Id: class.lib_indexer_liste_phpcms.php,v 1.1.1.1.2.16 2006/06/18 18:07:30 ignatius0815 Exp $ */
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

########################################################################
# Gibt einen Liste mit allen gespeicherten Profilen aus
########################################################################

function show_list()
	{
	global $session, $formdata, $MESSAGES, $PHP_SELF;

	# alles löschen
	unset_all();

	echo '<div id="output">'."\n";
	$c_form = new form();
	$c_form->set_bgcolor('#FCFCFC');
	$c_form->set_border_color('#004400');
	$c_form->set_width('500');

	$c_form->add_area('0');
	$c_form->set_area_title('0', $MESSAGES['HTTP_SRC'][22]); // Liste aller Profile
	$message = '</form>'.$MESSAGES['HTTP_SRC'][23].'<br />'; // In dieser Liste können Sie mit den gespeicherten Profilen arbeiten.
	$message.= $MESSAGES['HTTP_SRC'][24].' <img src="gif/indexer/search.gif" width="15" height="15" alt="'.$MESSAGES['HTTP_SRC'][27].'" border="0" /> '; //Durch Klick auf die Schaltfläche
	$message.= $MESSAGES['HTTP_SRC'][25].'<br />'; // laden Sie ein erstelltes Profil zur Erstellung eines neuen Volltextindexes.
	$message.= $MESSAGES['HTTP_SRC'][24].' <img src="gif/indexer/edit.gif" width="16" height="16" alt="'.$MESSAGES['HTTP_SRC'][144].'" border="0" /> '; //Durch Klick auf die Schaltfläche
	$message.= $MESSAGES['HTTP_SRC'][143].'<br />'; // können Sie dieses Profil editieren
	$message.= $MESSAGES['HTTP_SRC'][24].' <img src="gif/indexer/delete.gif" width="16" height="16" alt="'.$MESSAGES['HTTP_SRC'][28].'" border="0" /> '; //Durch Klick auf die Schaltfläche
	$message.= $MESSAGES['HTTP_SRC'][26];	//löschen Sie dieses Profil.
	$c_form->add_area_show_textarea('0', $message);

	$c_form->add_area('1');
	$c_form->set_area_title('1', $MESSAGES['HTTP_SRC'][03]); // Auswahl

	# Liste holen
	$profiles = read_profiles();
	if(count($profiles) > 0)
		{
		$message = '<table border="0" cellspacing="1" cellpadding="0" width="405">';
		$message.= '<tr><td background="gif/indexer/h_trenner.gif" colspan="4"><img src="gif/indexer/nix.gif" width="2" height="1" border="0" vspace="0" hspace="0" /></td></tr>';
		while (list($name,$v) = each($profiles))
			{
			$message.= '<tr>';
			$message.= '<td width="100%">'.$c_form->normal_font.$name.'</font></td>';
			$message.= '<form action="'.$session->write_link($PHP_SELF).'">';
			$message.= '<input type="hidden" name="phpcmsaction" value="HTTPINDEX">';
			$message.= '<input type="hidden" name="profilname" value="'.$name.'">';
			$message.= '<input type="hidden" name="action" value="start_create">';
			$message.= '<td width="15"><input type="image" src="gif/indexer/search.gif" width="15" height="15" alt="'.$MESSAGES['HTTP_SRC'][27].'" title="'.$MESSAGES['HTTP_SRC'][27].'" border="0"></td>'; // Index erstellen
			$message.= '</form>';
			$message.= '<form action="'.$session->write_link($PHP_SELF).'">';
			$message.= '<input type="hidden" name="phpcmsaction" value="HTTPINDEX">';
			$message.= '<input type="hidden" name="profilname" value="'.$name.'">';
			$message.= '<input type="hidden" name="action" value="edit_profile">';
			$message.= '<td width="16"><input type="image" src="gif/indexer/edit.gif" width="16" height="16" alt="'.$MESSAGES['HTTP_SRC'][144].'" title="'.$MESSAGES['HTTP_SRC'][144].'" border="0"></td>'; // Profil editieren
			$message.= '</form>';
			$message.= '<form action="'.$session->write_link($PHP_SELF).'">';
			$message.= '<input type="hidden" name="phpcmsaction" value="HTTPINDEX">';
			$message.= '<input type="hidden" name="profilname" value="'.$name.'">';
			$message.= '<input type="hidden" name="action" value="delete_profile">';
			$message.= '<td width="16"><input type="image" src="gif/indexer/delete.gif" width="16" height="16" alt="'.$MESSAGES['HTTP_SRC'][28].'" title="'.$MESSAGES['HTTP_SRC'][28].'" border="0"></td>'; // Profil löschen
			$message.= '</form>';
			$message.= '</tr>';
			$message.= '<tr><td background="gif/indexer/h_trenner.gif" colspan="4"><img src="gif/indexer/nix.gif" width="2" height=1 border=0 vspace=0 hspace=0></td></tr>';
			}
		$message.= '</table></div>';
		}
	else
		{
		// Es wurden keine gesicherten Suchprofile gefunden. Legen Sie mit
		// "Suchprofil erstellen" erst ein Suchprofil an!
		$message = $MESSAGES['HTTP_SRC'][29];
		}

	$c_form->add_area_show_textarea('1', $message);
	$c_form->compose_form();
	echo "\n".'</div><!-- output -->'."\n";
	}


function edit_profile() {

	global $session,$formdata;

	$profiles = read_profiles();
	// save profile data to edit in $session->vars['editprofile']
	$session->vars['editprofile'] = $profiles[$formdata->profilname];
	// save profile name
	// profile will be deleted at the end of the edit process!
	$session->vars['editprofile']['profilname'] = $formdata->profilname;

	input_form('edit');

} // end edit_profile


########################################################################
# Ein Profil löschen
########################################################################

function delete_profile($profile) {

	$profiles = read_profiles();
	unset($profiles[$profile]);
	write_profiles($profiles);

} // end delete_profile

?>