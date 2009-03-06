<?php
/* $Id: class.setup_phpcms.php,v 1.1.2.16 2006/06/20 06:27:38 ignatius0815 Exp $ */
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
   |    Markus Richert (e157m369)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

class SETUP {
	var $MESSAGES;

	function SETUP() {
		global $MESSAGES;
		error_reporting(E_ERROR);

		$this->MESSAGES = $MESSAGES['SETUP'];
	}

	function load_defaults() {
		global $PHPCMS, $PHP;

		return array(
			array('', 'PASS', 'YourPasswordHere'),
			"\n\n",
			array('!', 'DOMAIN_NAME', '$PHP->GetDomainName()'),
			array('', 'PAGE_EXTENSION', '.htm'),
			array('', 'PAGE_DEFAULTNAME', 'index'),
			array('', 'TEMPEXT', '.tpl'),
			array('', 'GLOBAL_PROJECT_FILE', '/template/home.ini'),
			array('', 'GLOBAL_PROJECT_HOME', '/'),
			array('', 'PLUGINDIR', '/parser/plugs'),
			"\n\n",
			array('', 'START_FIELD', '{'),
			array('', 'STOP_FIELD', '}'),
			array('', 'MENU_DELIMITER', ';'),
			array('', 'TAG_DELIMITER', ','),
			array('', 'PAX', 'off'),
			array('', 'PAXTAGS', 'off'),
			array('', 'MAIL2CRYPT', 'on'),
			array('', 'MAIL2CRYPT_JS', '/'),
			array('', 'MAIL2CRYPT_IMG', '/parser/gif/'),
			"\n\n",
			array('', 'CACHE_STATE', 'off'),
			array('', 'CACHE_DIR', '/parser/cache'),
			array('', 'CACHE_CLIENT', 'off'),
			array('!', 'PROXY_CACHE_TIME', '60*60*24*7'),
			"\n\n",
			array('', 'GZIP', 'off'),
			array('', 'STEALTH', 'off'),
			array('', 'STEALTH_SECURE', 'off'),
			array('', 'NOLINKCHANGE', '.gif;.jpg;.css;.js;.php;.txt;.zip;.pdf'),
			array('', 'DEBUG', 'on'),
			array('', 'ERROR_PAGE', '/error.htm'),
			array('', 'ERROR_PAGE_404', '/404.htm'),
			array('', 'TAGS_ERROR', 'on'),
			"\n\n",
			array('', 'P3P_HEADER', 'off'),
			array('', 'P3P_POLICY', ''),
			array('', 'P3P_HREF', '/w3c/p3p.xml'),
			"\n\n",
			array('', 'STATS', 'off'),
			array('', 'STATS_DIR', '/parser/stat'),
			array('', 'STATS_CURRENT', '/parser/stat/current'),
			array('', 'STATS_FILE', 'stat.txt'),
			array('', 'STATS_BACKUP', '/parser/stat/backup'),
			array('', 'STATS_REFERER_COUNT', '100'),
			array('', 'STATS_REFERER_IGNORE', '10.10.10.10;0'),
			array('', 'STATS_IP_COUNT', '200'),
			array('', 'STATS_IP_IGNORE', '10.10.10.10;0'),
			array('', 'STATS_URL_COUNT', '200'),
			array('', 'REFERRER', 'off'),
			array('', 'REFERRER_DIR', '/parser/stat'),
			array('', 'REFERRER_FILE', 'referrer.txt'),
			array('!', 'REF_RELOAD_LOCK', '60'),
			"\n\n",
			array('', 'FILEMANAGER_STARTDIR', '/parser/'),
			array('', 'FILEMANAGER_DIRSIZE', 'off'),
			array('', 'FILEMANAGER_AREA_SIZE', '85,21'),
			array('', 'FILEMANAGER_SHORTNAME_LENGTH', '22'),
			array('', 'CACHEVIEW_SHORTNAME_LENGTH', '45'),
			"\n\n",
			array('', 'LANGUAGE', 'en'),
			array('!', 'PASS_MIN_LENGTH', '6'),
			array('', 'ENABLE_ONLINE_EDITOR', 'off'),
			array('', 'UPDATE', 'off'),
			"\n\n",
			array('!', 'VERSION', '$PHPCMS->VERSION.$PHPCMS->RELEASE'),
			"\n\n",
			array('', 'PROJECT', 'PROJECT'),
			array('', 'TAGFILE', ''),
			array('', 'TEMPLATE', ''),
			array('', 'MENU', ''),
			array('', 'MENUTEMPLATE', ''),
			"\n\n",
			array('', 'COMMENT', ';'),
			array('', 'DYN_EXTENSION', '.dyn'),
			array('', 'PROJECTFILENAME', ''),
			array('!', 'DOCUMENT_ROOT', '$PHP->GetDocRoot()'),
			array('!', 'SCRIPT_PATH', '$PHP->GetScriptPath()'),
			array('!', 'SCRIPT_NAME', '$PHP->GetScriptName()'),
			"\n\n",
			array('', 'FIX_PHP_OB_BUG', 'off'),
			array('', 'ERROR_ALL', 'off'),
			"\n\n",
			array('', 'I18N', 'off'),
			array('', 'I18N_DEFAULT_LANGUAGE', 'en'),
			array('', 'I18N_POSSIBLE_LANGUAGES', 'en,de'),
			array('', 'I18N_MODE', 'lang'),
			"\n\n",
			array('', 'CONTENT_SEARCH', 'off'),
			array('', 'CONTENT_SEARCH_FIELDNAME', 'CONTENT_SEARCH'),
			array('', 'CONTENT_TOC', 'off'),
			array('', 'CONTENT_TOC_FIELDNAME', 'CONTENT_TOC'),
			"\n\n",
			array('', 'CHARSET', 'iso-8859-1'),
		);
	}

	function write_file($filename, $content) {
		$fp = @fopen($filename, 'wb');
		if($fp) {
			fwrite($fp, $content);
			fclose($fp);
			return true;
		} else {
			return false;
		}
	}

	function convert_defaults() {
		global
			$PHPCMS,
			$PHP,
			$conf_action,
			$DEFAULTS,
			$INIFILE,
			$DOCUMENT;

		if(isset($conf_action) AND strtoupper(substr($conf_action, 0, 7)) == 'CONVERT') {
			// backup the default.php
			if(@copy($INIFILE, substr($INIFILE, 0, -4).'.bak.php') OR strtoupper($conf_action) == 'CONVERT_NOBAK') {
				$backup_done = true;
			} else {
				DrawHeader($this->MESSAGES['BACKUP_FAILED']['TITLE']);
				DrawTopLine($this->MESSAGES['BACKUP_FAILED']['TITLE']);
				echo $DOCUMENT->TABLE_FONT.$this->MESSAGES['BACKUP_FAILED']['INFO'].
					'<p align="center"><form method="post" action="'.$DEFAULTS->SELF.'">'."\n".
					'<input type="hidden" name="phpcmsaction" value="OPTIONS">'."\n".
					'<input type="hidden" name="action" value="CONF">'."\n".
					'<input type="hidden" name="conf_action" value="CONVERT_NOBAK">'."\n".
					'<input type="submit" name="submit" value="'.$this->MESSAGES['BACKUP_FAILED']['SUBMIT'].'"></form></p></font>'."\n";
				DrawBottomLine($this->MESSAGES['BACKUP_FAILED']['STATUS']);
				DrawFooter();
				Exit;
			}
		}

		if(isset($conf_action) AND strtoupper(substr($conf_action, 0, 7)) == 'CONVERT' AND $backup_done) {
			// loading default-values
			$default_vars = $this->load_defaults();
			// loading old default-values, which are no more used, but could be part of any old-version's default.php
			// they will be filtered out, because instead they will appear as user-defined in the converted default.php.
			$sortout_vars = array(
				'CHECKDIRSIZE',
			);

			// loading your old values from default.php
			$fp = fopen($INIFILE, 'rb');
			while(!feof($fp)) {
				$old_ini_file[] = ereg_replace("[ \t]", "", fgets($fp, 1024));
			}
			fclose($fp);

			// put them into an associative array for the systemvalues and the userdefineds
			reset($old_ini_file);
			while(list(, $lvalue) = each($old_ini_file)) {
				if(stristr($lvalue, '$this->')) {
					$j = false;
					reset($default_vars);
					while(list(, $value) = each($default_vars)) {
						if(stristr($lvalue, '$this->'.$value[1].'=')) {
							if($value[0] != '') {
								$oldvalues[$value[1]] = substr($lvalue, strpos($lvalue, '=') + 1);
								$oldvalues[$value[1]] = substr($oldvalues[$value[1]], 0, strrpos($oldvalues[$value[1]], ';'));
							} else {
								$oldvalues[$value[1]] = substr($lvalue, strpos($lvalue, '=\'') + 2);
								$oldvalues[$value[1]] = substr($oldvalues[$value[1]], 0, strrpos($oldvalues[$value[1]], '\';'));
							}
							$j = true;
						}
					}
					if(!$j) {
						$ukey = substr($lvalue, strpos($lvalue, '$this->') + 7);

						if(substr($ukey, strpos($ukey, '='), 1) != '\'') {
							$upar = '!';
						} else {
							$upar = '';
						}
						$ukey = substr($ukey, 0, strpos($ukey, '='));

						if($upar != '') {
							$uvalue = substr($lvalue, strpos($lvalue, '=') + 1);
							$uvalue = substr($uvalue, 0, strpos($uvalue, ';'));
						} else {
							$uvalue = substr($lvalue, strpos($lvalue, '=\'') + 2);
							$uvalue = substr($uvalue, 0, strpos($uvalue, '\';'));
						}

						// Now separate the real userdefineds from the outdated ones
						foreach($sortout_vars as $sortout) {
							if($ukey == $sortout) {
								// we have the guarantee, that there will be no critical value, means there will be no user-redefineds.
								// so in 1.2.0 we just skip this value and don't load it separate to let the user confirm the deletion.
								continue 2;
							}
						}

						$user_defined[] = array($upar, $ukey, $uvalue);
					}
				}
			}
			// merge the old-value-array with the default-array (default-key => old-value)
			reset($default_vars);
			while(list($dkey, $dvalue) = each($default_vars)) {
				reset($oldvalues);
				while(list($okey, $ovalue) = each($oldvalues)) {
					if($okey == $dvalue[1]) {
						$merged_values[$dkey] = array($dvalue[0], $okey, $ovalue);
					}
				}
			}
			// set non-existing fields to the defaults and set the version to the default
			reset($default_vars);
			while(list($dkey, $dvalue) = each($default_vars)) {
				if($merged_values[$dkey][1] != $dvalue[1]) {
					$merged_values[$dkey] = $dvalue;
				}
				if($merged_values[$dkey][1] == 'VERSION') {
					$merged_values[$dkey] = $dvalue;
				}
			}
			// finally add the spacelines between the blocks and the user-defineds from the old_ini_file
			if(isset($user_defined)) {
				$spacer = +1;
			} else {
				$spacer = -1;
			}
			for($i = 0; $i <= count($default_vars) + $spacer; $i++) {
				if(!isset($merged_values[$i]) AND isset($user_defined)) {
					$merged_values[$i] = "\n";
				}
			}
			if(isset($user_defined)) {
				reset($user_defined);
				while(list($key, $value) = each($user_defined)) {
					$merged_values[] = $value;
				}
			}
			// now sort it as given in the default-array
			ksort($merged_values);
			// and put the new file together...
			$new_ini_file = '<?php //'.$PHPCMS->VERSION.$PHPCMS->RELEASE.'//please don\'t change anything in this line!//Bitte in dieser Zeile nichts ändern!//'."\n\n".
				'class defaults {'."\n".
				'	function defaults() {'."\n".
				'		global $PHP, $PHPCMS;'."\n".
				'		if(!defined("_DEFAULTS_")) {'."\n".
				'			define("_DEFAULTS_", TRUE);'."\n".
				'		}'."\n\n";
			reset($merged_values);
			while(list(, $value) = each($merged_values)) {
				if(!is_array($value)) {
					$new_ini_file .= $value;
					continue;
				}
				$new_ini_file .= '		';
				if($value[0] == '//') {
					$new_ini_file .= '//';
				}
				$new_ini_file .= '$this->'.$value[1].' = ';
				if($value[0] == '') {
					$new_ini_file .= '\''.$value[2].'\';';
				} else {
					$new_ini_file .= $value[2].';';
				}
				$new_ini_file .= "\n";
			}
			$new_ini_file .= '	}'."\n".'}'."\n".'?'.'>';

			// write the changes into the file ... Conversion done
			$done = $this->write_file($INIFILE, $new_ini_file);

			// and face the changed file to the user
			DrawHeader($this->MESSAGES['CONVERT']['TITLE']);
			DrawTopLine($this->MESSAGES['CONVERT']['TITLE']);
			if($done) {
				echo '<p>'.$DOCUMENT->TABLE_FONT.$this->MESSAGES['CONVERT']['SAVED'].'</font></p>'."\n".
					'<textarea cols="72" rows="30" wrap="off">'.$new_ini_file.'</textarea>'."\n".
					'<p align="center"><form method="post" action="'.$DEFAULTS->SELF.'">'."\n".
					'<input type="hidden" name="phpcmsaction" value="OPTIONS">'."\n".
					'<input type="hidden" name="action" value="CONF">'."\n".
					'<input type="hidden" name="conf_action" value="EXITCONVERT">'."\n".
					'<input type="submit" name="SUBMIT" value="'.$this->MESSAGES['BACK_TO_CONFIG'].'"></form></p></font>'."\n";
			} else {
				echo '<p>'.$DOCUMENT->TABLE_FONT.$this->MESSAGES['CONVERT']['FAILED'].'</font></p>'."\n".
					'<p align="center"><form method="post" action="'.$DEFAULTS->SELF.'">'."\n".
					'<input type="hidden" name="phpcmsaction" value="OPTIONS">'."\n".
					'<input type="hidden" name="action" value="CONF">'."\n".
					'<input type="hidden" name="conf_action" value="CONVERT">'."\n".
					'<input type="submit" name="SUBMIT" value="'.$this->MESSAGES['CONVERT']['SUBMIT'].'"></form></p></font>'."\n";
			}
			DrawBottomLine($this->MESSAGES['CONVERT']['STATUS']);
			DrawFooter();
			Exit;
		} else {
			DrawHeader($this->MESSAGES['CONVERT']['TITLE']);
			DrawTopLine($this->MESSAGES['CONVERT']['TITLE']);
			echo $DOCUMENT->TABLE_FONT.$this->MESSAGES['CONVERT']['INFO'].
				'<p align="center"><form method="post" action="'.$DEFAULTS->SELF.'">'."\n".
				'<input type="hidden" name="phpcmsaction" value="OPTIONS">'."\n".
				'<input TYPE="hidden" NAME="action" value="CONF">'."\n".
				'<input TYPE="hidden" name="conf_action" value="CONVERT">'."\n".
				'<input type="submit" name="SUBMIT" value="'.$this->MESSAGES['CONVERT']['SUBMIT'].'"></form></p></font>'."\n";
			DrawBottomLine($this->MESSAGES['CONVERT']['STATUS']);
			DrawFooter();
			Exit;
		}
	}
}

?>
