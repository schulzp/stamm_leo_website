<?php
/* $Id: class.options_phpcms.php,v 1.21.2.53 2006/06/18 18:07:31 ignatius0815 Exp $ */
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
   |    Martin Jahn (mjahn)
   |    Henning Poerschke (hpoe)
   |    Markus Richert (e157m369)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

function FehlerMeldungen() {
	global
		$DEFAULTS,
		$MESSAGES,
		$DOCUMENT;

	DrawHeader($MESSAGES['ERRORCODES'][11]);
	DrawTopLine($MESSAGES['ERRORCODES'][11]);
	echo $DOCUMENT->TABLE_FONT;
	echo '<table cellpadding="4" border="0">'."\n";
	reset($MESSAGES['ERRORCODES']);
	while(list($key, $value) = each($MESSAGES['ERRORCODES'])) {
		if($key != 11) {
			echo '<tr><td valign="top"><font size="2"><b>'.substr($value, 0, 3).'</b></font></td><td><font size="2">'.substr($value, 3).'</font></td></tr>'."\n";
		}
	}
	echo '</table></font>'."\n";
	DrawBottomLine('&nbsp;');
	DrawFooter();
}

function hilfe() {
	global
		$DEFAULTS,
		$MESSAGES,
		$DOCUMENT;

	DrawHeader($MESSAGES['HELP'][11]);
	DrawTopLine($MESSAGES['HELP'][11]);
	echo $DOCUMENT->TABLE_FONT.
		$MESSAGES['HELP'][12].
		'</font>';
	DrawBottomLine('&nbsp;');
	DrawFooter();
}

function MailingList() {
	global
		$MESSAGES,
		$DOCUMENT;

	DrawHeader($MESSAGES['LIST'][11]);
	DrawTopLine($MESSAGES['LIST'][11]);
	echo '<table border="0" cellspacing="2" cellpadding="2" width="100%">';
	WriteSeparatorLine($MESSAGES['LIST'][12]);
	echo '<tr><td colspan="2">'.$DOCUMENT->TABLE_FONT.$MESSAGES['LIST'][13].'<a href="http://sourceforge.net/mailarchive/forum.php?forum=phpcms-news" target="_blank">'.$MESSAGES['LIST'][14].'</a>.</font></td></tr>';
	WriteSeparatorLine($MESSAGES['LIST'][15]);
	echo '<tr><td colspan="2"><p>'.$DOCUMENT->TABLE_FONT.$MESSAGES['LIST'][16].'<br />&nbsp;</font></p></td></tr>'.
		'<form action="http://lists.sourceforge.net/lists/subscribe/phpcms-news" method="post">'.
		'<tr><td width="60%" bgcolor="'.$DOCUMENT->ROW_DARK.'">'.$DOCUMENT->TABLE_FONT.$MESSAGES['LIST'][17].'</font></td><td width="40%" bgcolor="'.$DOCUMENT->ROW_DARK.'"><input size="30" name="email"></td></tr>'.
		'<tr><td colSpan="2">'.$DOCUMENT->SUB_TABLE_FONT.$MESSAGES['LIST'][18].'</font></td></tr>'.
		'<tr><td bgcolor="'.$DOCUMENT->ROW_DARK.'">'.$DOCUMENT->TABLE_FONT.$MESSAGES['LIST'][19].'</font></td><td bgcolor="'.$DOCUMENT->ROW_DARK.'"><input type="password" size="15" name="pw"></td></tr>'.
		'<tr><td bgcolor="'.$DOCUMENT->ROW_DARK.'">'.$DOCUMENT->TABLE_FONT.$MESSAGES['LIST'][20].'</font></td><td bgcolor="'.$DOCUMENT->ROW_DARK.'"><input type="password" size="15" name="pw-conf"></td></tr>'.
		'<tr><td colSpan="2" align="center"><input type="hidden" value="0" name="digest"><input type="submit" value="'.$MESSAGES['LIST'][21].'" name="email-button"></td></tr>'.
		'</form></table>';
	DrawBottomLine('&nbsp;');
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

function GetCacheTime() {
	global $DEFAULTS, $PARSER;
	$FieldName = '$this->PROXY_CACHE_TIME';
	$count = count($PARSER);
	for($i = 0; $i < $count; $i++) {
		if(stristr($PARSER[$i], $FieldName)) {
			$Value = substr($PARSER[$i], strpos($PARSER[$i], $FieldName) + strlen($FieldName));
			$Value = substr($Value, strpos($Value, '=') + 1);
			$Value = substr($Value, 0, strpos($Value, ';'));
			$Value = substr($Value, strrpos($Value, '*') + 1);
			$Value = trim($Value);
			return $Value;
		}
	}
	return false;
}

function GetReloadLockTime() {
	global $DEFAULTS, $PARSER;
	$FieldName = '$this->REF_RELOAD_LOCK';
	$count = count($PARSER);
	for($i = 0; $i < $count; $i++) {
		if(stristr($PARSER[$i], $FieldName)) {
			$Value = substr($PARSER[$i], strpos($PARSER[$i], $FieldName) + strlen($FieldName));
			$Value = substr($Value, strpos($Value, '=') + 1);
			$Value = substr($Value, 0, strpos($Value, ';'));
			//$Value = substr($Value, strrpos($Value, '*') + 1);
			$Value = trim($Value);
			return $Value;
		}
	}
	return false;
}

function WriteChanges() {
	global
		$PARSER,
		$DEFAULTS,
		$INIFILE,
		$config_field;

	reset($config_field);
	while(list($key, ) = each($config_field)) {
		$config_field[$key] = trim($config_field[$key]);
	}

	reset($PARSER);
	while(list($pkey, $pvalue) = each($PARSER)) {
		reset($config_field);
		while(list($key, $value) = each($config_field)) {
			if(!stristr($PARSER[$pkey], '$this->'.$key)) {
				continue;
			} else {
				$temp = substr($PARSER[$pkey], 0, strpos($PARSER[$pkey], '='));
				$temp = trim(strtolower($temp));
				if($temp == '$this->'.$key) {
					if($key == 'proxy_cache_time') {
						$PartOne = substr($PARSER[$pkey], 0, strpos($PARSER[$pkey], '= ') + 2);
						$PartTwo = substr($PARSER[$pkey], strrpos($PARSER[$pkey], ';'));
						$PARSER[$pkey] = $PartOne.'60*60*24*'.$config_field[$key].$PartTwo;
					} elseif($key == 'ref_reload_lock') {
						$PartOne = substr($PARSER[$pkey], 0, strpos($PARSER[$pkey], '= ') + 2);
						$PartTwo = substr($PARSER[$pkey], strrpos($PARSER[$pkey], ';'));
						$PARSER[$pkey] = $PartOne.$config_field[$key].$PartTwo;
					} elseif($key == 'pass' AND strlen($config_field['pass']) < $DEFAULTS->PASS_MIN_LENGTH) {
						$PartOne = substr($PARSER[$pkey], 0, strpos($PARSER[$pkey], '= \'') + 3);
						$PartTwo = substr($PARSER[$pkey], strrpos($PARSER[$pkey], '\';'));
						$PARSER[$pkey] = $PartOne.$DEFAULTS->PASS.$PartTwo;
						$returner = 'nopass';
					} else {
						$PartOne = substr($PARSER[$pkey], 0, strpos($PARSER[$pkey], '= \'') + 3);
						$PartTwo = substr($PARSER[$pkey], strrpos($PARSER[$pkey], '\';'));
						$PARSER[$pkey] = $PartOne.$config_field[$key].$PartTwo;
					}
				}
			}
		}
	}

	$fp = @fopen($INIFILE, 'wb');
	if($fp) {
		for($i = 0; $i < count($PARSER); $i++) {
			fwrite($fp, $PARSER[$i]);
		}
		fclose($fp);
	} else {
		return false;
	}

	$DEFAULTS->CACHE_DIR = $config_field['cache_dir'];
	$DEFAULTS->LANGUAGE = $config_field['language'];
	if($DEFAULTS->LANGUAGE == 'us') {
		$DEFAULTS->LANGUAGE = 'en';
	}
	if(isset($returner) && $returner) {
		return $returner;
	} else {
		return true;
	}
}

function WriteInputLine($Text, $Text2, $FieldName, $DefaultName='',
	// the params for the JS-Hide
		// send the $FieldName of the radio-parent, send a '' not to group anywhere
		$ChildGroup='',
		// send 'on' for to show when parent is set to 'on', send 'off' for show when 'off', send a '' to do nothing
		$ParentMode='',
	// some vars to set an <textarea> alternatively, including params for size and wrapping-mode and style-size to fit the sizes better in vertical
		// $cols is for non-css browsers only (i.e. NS4x), $style_width might be around $cols*11
		$Type='text', $wrap='virtual', $rows='2', $cols='45', $style_width='500') {

	global
		$LastColor,
		$DEFAULTS,
		$DOCUMENT,
		$PHP,
		$MESSAGES;

	// set the global groups-array for later use
	if($ChildGroup) {
		$GLOBALS['OptGroups'][$ChildGroup][] = array($FieldName, $ParentMode);
	}

	if(!isset($LastColor)) {
		$LastColor = $DOCUMENT->ROW_DARK;
	}
	if($LastColor == $DOCUMENT->ROW_LIGHT) {
		$LastColor = $DOCUMENT->ROW_DARK;
	} else {
		$LastColor = $DOCUMENT->ROW_LIGHT;
	}

	if($DefaultName == 'GetCacheTime()') {
		$Field = GetCacheTime();
	} elseif($DefaultName == 'GetReloadLockTime()') {
		$Field = GetReloadLockTime();
	} else {
		$Field = GetField($DefaultName);
	}

	echo '<tr bgcolor="'.$LastColor.'" id="'.$FieldName.'">';
	if($Type == 'textarea') {
		echo '<td colspan="2" valign="top">'.$DOCUMENT->TABLE_FONT."\n";
	} else {
		echo '<td>'.$DOCUMENT->TABLE_FONT."\n";
	}

	echo '<a href="'.$PHP->GetScriptPath().'/help/options.'.$DEFAULTS->LANGUAGE.'.html#'.$FieldName.'" title="'.$MESSAGES['OPTIONS'][02].'">'.$Text.':</a>'.$Text2;

	echo '</font>';
	if($Type == 'textarea') {
		echo '<br />'.$DOCUMENT->TABLE_FONT."\n".
			'<textarea name="config_field['.$FieldName.']" rows="'.$rows.'" cols="'.$cols.'" style="width:'.$style_width.'px;" wrap="'.$wrap.'">'.$Field.'</textarea>'."\n";
	} else {
		echo '</td><td valign="TOP">'.$DOCUMENT->TABLE_FONT."\n".
			'<input id="field_'.$FieldName.'" type="'.$Type.'" name="config_field['.$FieldName.']" value="'.$Field.'" size="15" maxsize="30" style="width:150px;">'."\n";
	}
	if($Type == 'password') {
		echo '<script type="text/javascript">'."\n".'<!--'."\n".
             'document.write("<font size=\"1\"><a href=\"#\" onclick=\"alert(\''.
             $MESSAGES['OPTIONS'][85].': '.$Field.'\'); return false;\">'.
             $MESSAGES['OPTIONS'][84].'...</a></font>");'.
             "\n".'-->'."\n".'</script>'."\n";
		$GLOBALS['new_fields'][strtoupper($FieldName)] = $Field;
	}
	echo '</font></td></tr>'."\n";
}

function WriteRadioLine($Text, $Text2, $FieldName, $DefaultName='',
	// the params for the JS-Hide
		// send the $FieldName of the radio-parent, send a '' not to group anywhere
		$ChildGroup='',
		// send 'on' for to show when parent is set to 'on', send 'off' for show when 'off', send a '' to do nothing
		$ParentMode='') {

	global
		$LastColor,
		$DEFAULTS,
		$MESSAGES,
		$DOCUMENT,
		$PHP;

	// set the global groups-array for later use
	if($ChildGroup) {
		$GLOBALS['OptGroups'][$ChildGroup][] = array($FieldName, $ParentMode);
	}

	if($LastColor == $DOCUMENT->ROW_LIGHT) {
		$LastColor = $DOCUMENT->ROW_DARK;
	} else {
		$LastColor = $DOCUMENT->ROW_LIGHT;
	}

	echo '<tr bgcolor="'.$LastColor.'" id="'.$FieldName.'"><td>'.$DOCUMENT->TABLE_FONT."\n";
	echo '<a href="'.$PHP->GetScriptPath().'/help/options.'.$DEFAULTS->LANGUAGE.'.html#'.$FieldName.'" title="'.$MESSAGES['OPTIONS'][02].'">'.$Text.':</a>'.$Text2;

	echo '</font></td><td valign="TOP">'.$DOCUMENT->TABLE_FONT."\n";
	if(GetField($DefaultName) == 'on') {
		$opt1 = 'value="on" checked="checked" ';
		$opt2 = 'value="off" ';
	} else {
		$opt1 = 'value="on" ';
		$opt2 = 'value="off" checked="checked" ';
	}
	echo '<input id="field_'.$FieldName.'" name="config_field['.$FieldName.']" type="RADIO" '.$opt1.'style="background-color: '.$LastColor.';" onclick="check_all()"><a onclick="select(\'field_'.$FieldName.'\', \'0\', \'1\')" style="cursor: default">'.$MESSAGES[38].'</a>';
	echo '<input id="field_'.$FieldName.'" name="config_field['.$FieldName.']" type="RADIO" '.$opt2.'style="background-color: '.$LastColor.';" onclick="check_all()"><a onclick="select(\'field_'.$FieldName.'\', \'1\', \'0\')" style="cursor: default">'.$MESSAGES[39].'</a></font></td>'."\n";
	echo '</tr>'."\n";
}

function WriteOptionLine($Text, $Text2, $FieldName, $DefaultName='',
	// the params for the JS-Hide
		// send the $FieldName of the radio-parent, send a '' not to group anywhere
		$ChildGroup='',
		// send 'on' for to show when parent is set to 'on', send 'off' for show when 'off', send a '' to do nothing
		$ParentMode='',
	// the values-array
		$OptionValues) {

	global
		$LastColor,
		$DEFAULTS,
		$DOCUMENT,
		$MESSAGES,
		$PHP;

	$PARSER_PATH = $PHP->GetScriptPath();

	// set the global groups-array for later use
	if($ChildGroup) {
		$GLOBALS['OptGroups'][$ChildGroup][] = array($FieldName, $ParentMode);
	}

	if($LastColor == $DOCUMENT->ROW_LIGHT) {
		$LastColor = $DOCUMENT->ROW_DARK;
	} else {
		$LastColor = $DOCUMENT->ROW_LIGHT;
	}

	echo '<tr bgcolor="'.$LastColor.'" id="'.$FieldName.'"><td>'.$DOCUMENT->TABLE_FONT."\n";
	echo '<a href="'.$PARSER_PATH.'/help/options.'.$DEFAULTS->LANGUAGE.'.html#'.$FieldName.'" title="'.$MESSAGES['OPTIONS'][02].'">'.$Text.':</a>'.$Text2;
	echo '</font></td><td valign="TOP">'.$DOCUMENT->TABLE_FONT.'<select id="field_'.$FieldName.'" name="config_field['.$FieldName.']" size="1" STYLE="width:128px;">'."\n";
	$CountOptions = count($OptionValues);
	for($i = 0; $i < $CountOptions; $i++) {
		echo '<option value="'.$OptionValues[$i][0].'"';
		if($DEFAULTS->{strtoupper($FieldName)} == $OptionValues[$i][0]) {
			echo ' selected';
		}
		echo '> '.$OptionValues[$i][1];
	}
	echo '</select></font></td></tr>'."\n";
}

function WriteSeparatorLine($title) {
	global $DOCUMENT;

	echo '<tr><td bgcolor="'.$DOCUMENT->BACKGROUND_COLOR.'" width="60%">&nbsp;</td><td bgcolor="'.$DOCUMENT->BACKGROUND_COLOR.'" width="40%">&nbsp;</td></tr>'."\n".
		'<tr><td bgcolor="'.$DOCUMENT->DARK_COLOR.'" colspan="2">'.
		$DOCUMENT->STATUS_FONT.$title.'</font></td></tr>'."\n";
}

function konfiguration() {
	global
		$PHPCMS,
		$PHP,
		$conf_action,
		$DEFAULTS,
		$PARSER,
		$INIFILE,
		$MESSAGES,
		$DOCUMENT;

	$PARSER = @file($INIFILE);

	if(isset($conf_action) AND strtoupper($conf_action) == 'WRITE') {
		$done = WriteChanges();
		include(PHPCMS_INCLUDEPATH.'/language.'.$DEFAULTS->LANGUAGE);
		$PARSER = @file($INIFILE);
		if($done === 'nopass') {
			$status = $MESSAGES['OPTIONS']['NOPASS'][0].$DEFAULTS->PASS_MIN_LENGTH.$MESSAGES['OPTIONS']['NOPASS'][1];
		} else {
			$done ? $status = $MESSAGES['OPTIONS']['SAVED'] : $status = $MESSAGES['OPTIONS']['FAILED'];
		}
	} else {
		// checking the default.php's version
		$fp = fopen($INIFILE, 'rb');
		$firstline = fgets($fp, 1024);
		fclose($fp);

		$firstline = substr($firstline, strpos($firstline, '//') + 2);
		$firstline = substr($firstline, 0, strpos($firstline, '//'));

		// Load the converter
		if($firstline != $PHPCMS->VERSION.$PHPCMS->RELEASE AND strtoupper($conf_action) != 'EXITCONVERT') {
			include(PHPCMS_INCLUDEPATH.'/class.setup_phpcms.php');
			$SETUP = new SETUP;
			$SETUP->convert_defaults();
		}
	}

	GetStartPage();
	DrawHeader($MESSAGES['OPTIONS'][11], 'a{text-decoration:none;}'."\n". 'show{display: table-row;}'."\n". '.hide{display: none;}', '', 'onload="check_all(); reload_nav()"');
	DrawTopLine($MESSAGES['OPTIONS'][11]);

	echo '<table border="0" cellspacing="2" cellpadding="2" width="100%">'."\n".
		'<form name="form" method="post" action="'.$DEFAULTS->SELF.'">'."\n".
		'<input type="hidden" name="action" value="CONF">'."\n".
		'<input type="hidden" name="phpcmsaction" value="OPTIONS">'."\n".
		'<input type="hidden" name="conf_action" value="WRITE">'."\n".
		'<input type="hidden" name="cache_dir_set" value="'.$DEFAULTS->CACHE_DIR.'">'."\n".
		'<input type="hidden" name="language_set" value="'.$DEFAULTS->LANGUAGE.'">'."\n";

	WriteSeparatorLine($MESSAGES['OPTIONS'][01]);
	echo '<tr bgcolor="'.$DOCUMENT->ROW_DARK.'">'."\n".
		'<td valign="bottom">'.$DOCUMENT->TABLE_FONT.'&nbsp;</font></td>'."\n".
		'<td valign="bottom">'.$DOCUMENT->TABLE_FONT.'<input type="submit" name="SUBMIT" value="'.$MESSAGES[46].'"></font></td>'."\n".
		'</tr>';
	if(isset($status) && $status != '') {
		echo '<tr bgcolor="'.$DOCUMENT->ROW_DARK.'"><td valign="bottom" colspan="2">'.$DOCUMENT->TABLE_FONT.$status.'</font></td></tr>';
	}

	/*---------------------------------------------------*/
		WriteSeparatorLine($MESSAGES['OPTIONS'][21]);
	//	WriteInputLine($MESSAGES['OPTIONS'][22], $MESSAGES['OPTIONS']['NO_SLASH'], 'domain_name',         '$this->DOMAIN_NAME');
		WriteInputLine($MESSAGES['OPTIONS'][23], '',                               'page_extension',      '$this->PAGE_EXTENSION');
		WriteInputLine($MESSAGES['OPTIONS'][24], '',                               'page_defaultname',    '$this->PAGE_DEFAULTNAME');
		WriteInputLine($MESSAGES['OPTIONS'][25], '',                               'tempext',             '$this->TEMPEXT');
		WriteInputLine($MESSAGES['OPTIONS'][26], $MESSAGES['OPTIONS']['DOC_ROOT'], 'global_project_file', '$this->GLOBAL_PROJECT_FILE');
		WriteInputLine($MESSAGES['OPTIONS'][27], $MESSAGES['OPTIONS']['DOC_ROOT'], 'global_project_home', '$this->GLOBAL_PROJECT_HOME');
		WriteInputLine($MESSAGES['OPTIONS'][28], $MESSAGES['OPTIONS']['DOC_ROOT'], 'plugindir',           '$this->PLUGINDIR');
	/*---------------------------------------------------*/
		WriteSeparatorLine($MESSAGES['OPTIONS'][31]);
		WriteInputLine($MESSAGES['OPTIONS'][32], '',                               'start_field',         '$this->START_FIELD');
		WriteInputLine($MESSAGES['OPTIONS'][33], '',                               'stop_field',          '$this->STOP_FIELD');
		WriteInputLine($MESSAGES['OPTIONS'][94], '',                               'menu_delimiter',          '$this->MENU_DELIMITER');
		WriteInputLine($MESSAGES['OPTIONS'][95], '',                               'tag_delimiter',          '$this->TAG_DELIMITER');
		WriteRadioLine($MESSAGES['OPTIONS'][34], '',                               'pax',                 '$this->PAX');
		WriteRadioLine($MESSAGES['OPTIONS'][35], '',                               'paxtags',             '$this->PAXTAGS');
 		WriteRadioLine($MESSAGES['OPTIONS'][36], '',                               'mail2crypt',           '$this->MAIL2CRYPT');
 		WriteInputLine($MESSAGES['OPTIONS'][37], $MESSAGES['OPTIONS']['DOC_ROOT'],  'mail2crypt_js',           '$this->MAIL2CRYPT_JS',      'mail2crypt', 'on');
 		WriteInputLine($MESSAGES['OPTIONS'][38], $MESSAGES['OPTIONS']['DOC_ROOT'],  'mail2crypt_img',           '$this->MAIL2CRYPT_IMG',      'mail2crypt', 'on');
	/*---------------------------------------------------*/
		WriteSeparatorLine($MESSAGES['OPTIONS'][41]);
		WriteRadioLine($MESSAGES['OPTIONS'][42], '',                               'cache_state',         '$this->CACHE_STATE');
		WriteInputLine($MESSAGES['OPTIONS'][43], $MESSAGES['OPTIONS']['DOC_ROOT'], 'cache_dir',           '$this->CACHE_DIR',      'cache_state', 'on');
		WriteRadioLine($MESSAGES['OPTIONS'][44], '',                               'cache_client',        '$this->CACHE_CLIENT');
		WriteInputLine($MESSAGES['OPTIONS'][45], '',                               'proxy_cache_time',    'GetCacheTime()',        'cache_client', 'on');
	/*---------------------------------------------------*/
		WriteSeparatorLine($MESSAGES['OPTIONS'][51]);
		WriteRadioLine($MESSAGES['OPTIONS'][52], '',                               'gzip',                '$this->GZIP');
		WriteRadioLine($MESSAGES['OPTIONS'][53], '',                               'stealth',             '$this->STEALTH');
		WriteRadioLine($MESSAGES['OPTIONS'][54], '',                               'stealth_secure',      '$this->STEALTH_SECURE', 'stealth', 'on');
		WriteInputLine($MESSAGES['OPTIONS'][55], '',                               'nolinkchange',        '$this->NOLINKCHANGE',   'stealth', 'off', 'textarea');
		WriteRadioLine($MESSAGES['OPTIONS'][59], '',                               'tags_error',          '$this->TAGS_ERROR');
		WriteRadioLine($MESSAGES['OPTIONS'][56], '',                               'debug',               '$this->DEBUG');
		WriteInputLine($MESSAGES['OPTIONS'][57], $MESSAGES['OPTIONS']['DOC_ROOT'], 'error_page',          '$this->ERROR_PAGE',     'debug', 'off');
		WriteInputLine($MESSAGES['OPTIONS'][58], $MESSAGES['OPTIONS']['DOC_ROOT'], 'error_page_404',      '$this->ERROR_PAGE_404', 'debug', 'off');
	/*---------------------------------------------------*/
		WriteSeparatorLine($MESSAGES['P3PHEADER'][1]);
		WriteRadioLine($MESSAGES['P3PHEADER'][2], '',                              'p3p_header',          '$this->P3P_HEADER');
		WriteInputLine($MESSAGES['P3PHEADER'][3], '',                              'p3p_policy',          '$this->P3P_POLICY',     'p3p_header', 'on', 'textarea');
		WriteInputLine($MESSAGES['P3PHEADER'][4], '',                              'p3p_href',            '$this->P3P_HREF',       'p3p_header', 'on');
	/*---------------------------------------------------*/
		WriteSeparatorLine($MESSAGES['OPTIONS'][96]);
		WriteRadioLine($MESSAGES['OPTIONS'][97], '',                               'i18n',               '$this->I18N');
		WriteInputLine($MESSAGES['OPTIONS'][109], $MESSAGES['OPTIONS'][97], 		'i18n_default_language',           '$this->I18N_DEFAULT_LANGUAGE',      'i18n', 'on');
		WriteInputLine($MESSAGES['OPTIONS'][98], $MESSAGES['OPTIONS'][99], 			'i18n_possible_languages',       '$this->I18N_POSSIBLE_LANGUAGES',  'i18n', 'on');
		$i18nmode[0] = array('SUFFIX', $MESSAGES ['OPTIONS'] [102]);
		$i18nmode[1] = array('DIR', $MESSAGES ['OPTIONS'] [103]);
		$i18nmode[2] = array('VAR', $MESSAGES ['OPTIONS'] [104]);
		$i18nmode[3] = array('HOST', $MESSAGES ['OPTIONS'] [105]);
		$i18nmode[4] = array('SESSION', $MESSAGES ['OPTIONS'] [106]);
		for($i = 0; $i < count($i18nmode); $i++) {
			$OptionValues[$i] = $i18nmode [$i];
		}
		WriteOptionLine($MESSAGES['OPTIONS'][100], $MESSAGES['OPTIONS'][101],		'i18n_mode',          '$this->I18N_MODE',     'i18n', 'on', $OptionValues);
		WriteInputLine($MESSAGES['OPTIONS'][107], '', 								'i18n_paramname',        '$this->I18N_PARAMNAME',   'i18n', 'on');

	/*---------------------------------------------------*/
		WriteSeparatorLine($MESSAGES['OPTIONS'][71]);
		WriteInputLine($MESSAGES['OPTIONS'][72], $MESSAGES['OPTIONS']['DOC_ROOT'], 'filemanager_startdir','$this->FILEMANAGER_STARTDIR');
		WriteRadioLine($MESSAGES['OPTIONS'][73], '',                               'filemanager_dirsize', '$this->FILEMANAGER_DIRSIZE');
		WriteInputLine($MESSAGES['OPTIONS'][74][0], $MESSAGES['OPTIONS'][74][1],   'filemanager_area_size', '$this->FILEMANAGER_AREA_SIZE');
		WriteInputLine($MESSAGES['OPTIONS'][75][0], $MESSAGES['OPTIONS'][75][1],   'filemanager_shortname_length', '$this->FILEMANAGER_SHORTNAME_LENGTH');
		WriteInputLine($MESSAGES['OPTIONS'][76][0], $MESSAGES['OPTIONS'][76][1],   'cacheview_shortname_length', '$this->CACHEVIEW_SHORTNAME_LENGTH');
	/*---------------------------------------------------*/
		WriteSeparatorLine($MESSAGES['OPTIONS'][81]);
		$languages[0] = array('de', 'Deutsch', 'Sprachdatei FEHLT !!');
		$languages[1] = array('en', 'English', 'NO language file !!');
		for($i = 0; $i < count($languages); $i++) {
			if(file_exists(PHPCMS_INCLUDEPATH.'/language.'.$languages[$i][0])) {
				$OptionValues[$i][0] = $languages[$i][0];
				$OptionValues[$i][1] = $languages[$i][1];
			} else {
				$OptionValues[$i][0] = $languages[$i][0];
				$OptionValues[$i][1] = $languages[$i][2];
			}
		}
		WriteOptionLine($MESSAGES['OPTIONS'][82], '',                              'language',            '$this->LANGUAGE',       '', '', $OptionValues);
		WriteInputLine($MESSAGES['OPTIONS'][83], '',                               'pass',                '$this->PASS',           '', '', 'password');
		WriteRadioLine($MESSAGES['OPTIONS'][86], '',                               'enable_online_editor','$this->ENABLE_ONLINE_EDITOR');
		WriteRadioLine($MESSAGES['OPTIONS'][87], $MESSAGES['OPTIONS']['FIREWALL'], 'update',              '$this->UPDATE');
	/*---------------------------------------------------*/
		WriteSeparatorLine($MESSAGES['OPTIONS'][01]);

	if(isset($LastColor) AND $LastColor == $DOCUMENT->ROW_LIGHT) {
		$LastColor = $DOCUMENT->ROW_DARK;
	} else {
		$LastColor = $DOCUMENT->ROW_LIGHT;
	}
/*---------------------------------------------------*/
	echo '<tr bgcolor="'.$LastColor.'">'."\n".
		'<td valign="bottom">'.$DOCUMENT->TABLE_FONT.'&nbsp;</font></td>'."\n".
		'<td valign="bottom">'.$DOCUMENT->TABLE_FONT.'<input type="submit" name="SUBMIT" value="'.$MESSAGES[46].'"></font></td>'."\n".
		'</tr></form></table>';

/*---------------------------------------------------*/
	echo '<script language="JavaScript">'."\n".
		'function reload_nav() {'."\n";
	if(($_POST['language_set'] AND $_POST['language_set'] != $DEFAULTS->LANGUAGE)
		OR ($_POST['cache_dir_set'] AND $_POST['cache_dir_set'] != $DEFAULTS->CACHE_DIR)) {
		echo '	parent.navi.location.href="'.$DEFAULTS->NaviPage.'&language='.$DEFAULTS->LANGUAGE.'";'."\n";
	}
	echo '}'."\n\n";

	echo 'function select(field, option, shadow) {'."\n".
		'	if(document.getElementsByName) {'."\n".
		'		if(document.getElementsByName(field)[option].checked == true) {'."\n".
		'			document.getElementsByName(field)[shadow].checked = true;'."\n".
		'		} else {'."\n".
		'			document.getElementsByName(field)[option].checked = true;'."\n".
		'		}'."\n".
		'		check_all();'."\n".
		'	}'."\n".
		'}'."\n\n";

	echo 'function check_all() {'."\n".
		'	if(document.getElementsByName) {'."\n";
	if($GLOBALS['OptGroups']) {
		foreach($GLOBALS['OptGroups'] as $key => $value) {
			foreach($value as $skey => $value) {
				if($value[1] == 'on') {
					$mode = array('show', 'hide');
				} else {
					$mode = array('hide', 'show');
				}
				echo '		if(document.getElementsByName("config_field['.$key.']")[0].checked == true) {'."\n".
					'			document.getElementById("'.$value[0].'").className = "'.$mode[0].'";'."\n".
					'		} else if(document.getElementsByName("config_field['.$key.']")[1].checked == true) {'."\n".
					'			document.getElementById("'.$value[0].'").className = "'.$mode[1].'";'."\n".
					'		}'."\n";
			}
			echo "\n";
		}
	}
	echo '	}'."\n".
		'}'."\n".
		'</script>'."\n";

	DrawBottomLine($MESSAGES['OPTIONS'][12]);
	DrawFooter();
}

function folder_exist($folder_path) {
	clearstatcache();
	$create_dir = @mkdir($folder_path, 0755);
	if(!$create_dir) {
		return TRUE;
	} else {
		@rmdir($folder_path);
		return FALSE;
	}
}

switch(strtoupper($action)) {
	case 'LIST':
		MailingList();
		break;

	case 'FEHLER':
		FehlerMeldungen();
		break;

	case 'HELP':
		hilfe();
		break;

	case 'INFO':
		$style =
		'body, td { font-family: arial, helvetica, sans-serif; font-size: 95%; }'."\n".
		'h1 {clear:both; font-family: arial, helvetica, sans-serif; font-size: 120%; font-weight: bold;}'."\n".
		'h2 {clear:both;margin:2em 0 1em 0;  font-family: arial, helvetica, sans-serif; font-size: 110%; font-weight: bold;}'."\n".
		'a { text-decoration: none; }'."\n".
		'a:hover { text-decoration: underline; }'."\n".
		'hr { clear:both; width: 600px; align: center; background-color: #cccccc; border: 0px; height: 1px;}'."\n".
		'table {clear:both; text-align:left;margin: 0 0 1em 0}'."\n".
		'th { font-family: arial, helvetica, sans-serif; font-size: 140%; font-weight: bold; }'."\n".
		'.p {text-align: left;}'."\n".
		'.e {background-color: #ccccff; font-weight: bold;}'."\n".
		'.h {background-color: #9999cc; font-weight: bold;}'."\n".
		'.v {background-color: #cccccc;}'."\n".
		'i {color: #666666;}'."\n".
		'img {float: right; border: 0px;}'."\n";
		DrawHeader($MESSAGES['INFO'][11], $style);

		DrawTopLine($MESSAGES['INFO'][11]);

// BOF Fix phpinfo
		ob_start();
		phpinfo();
		$infobuffer .= ob_get_contents();
		ob_end_clean();
		preg_match_all("=<body[^>]*>(.*)</body>=siU", $infobuffer, $a);
		$phpinfo = $a[1][0];
		$phpinfo = str_replace( ';','; ', $phpinfo );
		$phpinfo = str_replace( ',',', ', $phpinfo );
		$phpinfo = str_replace( '<br>','<br />', $phpinfo );
		$phpinfo = str_replace( 'align="center"','align="left"', $phpinfo );
		$phpinfo = str_replace( 'align="right"','align="left"', $phpinfo );
		echo "$phpinfo";
// EOF Fix phpinfo

		DrawBottomLine('&nbsp;');
		DrawFooter();
		break;

	case 'CONF':
		konfiguration();
		break;

	default:
		konfiguration();
		break;
}

?>
