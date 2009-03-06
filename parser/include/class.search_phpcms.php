<?php
/* $Id: class.search_phpcms.php,v 1.3.2.18 2006/06/18 18:07:32 ignatius0815 Exp $ */
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
   |    Beate Paland (beate76)
   |    Henning Poerschke (hpoe)
   |    Markus Richert (e157m369)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

if(!defined("_SESSION_")) {
	include(PHPCMS_INCLUDEPATH.'/class.session_phpcms.php');
}
if(!defined("_ERROR_")) {
	include(PHPCMS_INCLUDEPATH.'/class.lib_error_phpcms.php');
}
if(!defined("_DATACONTAINER_")) {
	include(PHPCMS_INCLUDEPATH.'/class.lib_data_file_phpcms.php');
}
if(!defined("_FORM_")) {
	include(PHPCMS_INCLUDEPATH.'/class.form_phpcms.php');
}
if(!class_exists('document')) {
	include(PHPCMS_INCLUDEPATH.'/class.layout_phpcms.php');
}
include(PHPCMS_INCLUDEPATH.'/language.'.$DEFAULTS->LANGUAGE );

// some helpful global functions
function soft_exit() {
	global $session;
	$session->close();
	exit;
}

function make_link($option) {
	global
		$session,
		$DEFAULTS;

	$url = $DEFAULTS->SCRIPT_PATH.'/'.$DEFAULTS->SCRIPT_NAME.'?phpcmsaction=search&action='.$option;
	return $session->write_link($url);
}

// writes text out to the browser. if the indexer later is used
// as plugin or as part of phpCMS, we can easily omit header-errors,
// because we could write to a buffer instead to the browser.
function b_write($text) {
	echo $text."\n";
}

// authenticate user
$session = new session;

// menu
function draw_search_menu($message = '') {
	global
		$formdata,
		$DEFAULTS,
		$MESSAGES;

	$DATADB_PATH = substr(PHPCMS_INCLUDEPATH, strlen($DEFAULTS->DOCUMENT_ROOT));
	if(!isset($formdata->startpath)) {
		$formdata->startpath = '/demo-en';
	}
	if(!isset($formdata->excludepath1)) {
		$formdata->excludepath1 = '';
	}
	if(!isset($formdata->excludepath2)) {
		$formdata->excludepath2 = '';
	}
	if(!isset($formdata->excludepath3)) {
		$formdata->excludepath3 = '';
	}
	if(!isset($formdata->excludepath4)) {
		$formdata->excludepath4 = '';
	}
	if(!isset($formdata->excludepath5)) {
		$formdata->excludepath5 = '';
	}
	if(!isset($formdata->excludepath6)) {
		$formdata->excludepath6 = '';
	}
	if(!isset($formdata->excludepath7)) {
		$formdata->excludepath7 = '';
	}
	if(!isset($formdata->excludepath8)) {
		$formdata->excludepath8 = '';
	}
	if(!isset($formdata->datadir)) {
		$formdata->datadir = '/demo-en/search';
	}

	if(!isset($formdata->titlefield)) {
		$formdata->titlefield = 'TITLE';
	}
	if(!isset($formdata->contentfield)) {
		$formdata->contentfield = 'CONTENT';
	}
	if(!isset($formdata->addedfields)) {
		$formdata->addedfields = ';';
	}

	if(!isset($formdata->stopwords)) {
		$formdata->stopwords = $DATADB_PATH.'/stop.db';
	}
	if(!isset($formdata->maxbytesize)) {
		$formdata->maxbytesize = '100000';
	}
	if(!isset($formdata->minwordsize)) {
		$formdata->minwordsize = '3';
	}
	if(!isset($formdata->textsize)) {
		$formdata->textsize = '380';
	}

	DrawHeader($MESSAGES['FILE_SRC'][00]);
	DrawTopLine($MESSAGES['FILE_SRC'][00]);
	//echo '<br />';
	$menu_form = new form();
	$menu_form->set_select('MENU');
	$menu_form->set_callback('');
	$menu_form->set_bgcolor('#eeeeee');
	$menu_form->set_width('400');
	$menu_form->set_left_size('170');

	if(isset($message) AND strlen($message) > 0) {
		$menu_form->add_area('0');
		$menu_form->set_area_title('0', $MESSAGES['FILE_SRC'][10]);
		$menu_form->add_area_show_textarea('0', $message);
	}

	$menu_form->add_area('1');
	$menu_form->set_area_title('1', $MESSAGES['FILE_SRC'][11]);
	$menu_form->add_area_input_text('1', 'datadir',     '45', $MESSAGES['FILE_SRC'][13], $formdata->datadir);
	$menu_form->add_area_input_text('1', 'startpath',   '45', $MESSAGES['FILE_SRC'][12], $formdata->startpath);
	$menu_form->add_area_input_text('1', 'excludepath1','45', $MESSAGES['FILE_SRC']['EXPATH'][1], $formdata->excludepath1);
	$menu_form->add_area_input_text('1', 'excludepath2','45', $MESSAGES['FILE_SRC']['EXPATH'][2], $formdata->excludepath2);
	$menu_form->add_area_input_text('1', 'excludepath3','45', $MESSAGES['FILE_SRC']['EXPATH'][3], $formdata->excludepath3);
	$menu_form->add_area_input_text('1', 'excludepath4','45', $MESSAGES['FILE_SRC']['EXPATH'][4], $formdata->excludepath4);
	$menu_form->add_area_input_text('1', 'excludepath5','45', $MESSAGES['FILE_SRC']['EXPATH'][5], $formdata->excludepath5);
	$menu_form->add_area_input_text('1', 'excludepath6','45', $MESSAGES['FILE_SRC']['EXPATH'][6], $formdata->excludepath6);
	$menu_form->add_area_input_text('1', 'excludepath7','45', $MESSAGES['FILE_SRC']['EXPATH'][7], $formdata->excludepath7);
	$menu_form->add_area_input_text('1', 'excludepath8','45', $MESSAGES['FILE_SRC']['EXPATH'][8], $formdata->excludepath8);

	$menu_form->add_area('2');
	$menu_form->set_area_title('2', $MESSAGES['FILE_SRC'][60]);
	$menu_form->add_area_input_text('2', 'titlefield',   '45', $MESSAGES['FILE_SRC'][61], $formdata->titlefield);
	$menu_form->add_area_input_text('2', 'contentfield',   '45', $MESSAGES['FILE_SRC'][62], $formdata->contentfield);
	$menu_form->add_area_input_text('2', 'addedfields',   '45', $MESSAGES['FILE_SRC'][63], $formdata->addedfields);

	$menu_form->add_area('3');
	$menu_form->set_area_title('3', $MESSAGES['FILE_SRC'][50]);
		$SWUSE[1] = $MESSAGES['FILE_SRC'][52];
		$SWUSE[2] = $MESSAGES['FILE_SRC'][53];
		$SWUSECHK[2] =true;
	$menu_form->add_area_select_radio('3', 'uselocaldb', $MESSAGES['FILE_SRC'][51], $SWUSE, $SWUSECHK);

	$menu_form->add_area_input_text('3', 'stopwords', '45', $MESSAGES['FILE_SRC'][54], $formdata->stopwords);

		$SWSORT[1] = $MESSAGES['FILE_SRC'][56];
		$SWSORT[2] = $MESSAGES['FILE_SRC'][57];
		$SWSORTCHK[2] = true;
	$menu_form->add_area_select_radio('3', 'sortstopdb', $MESSAGES['FILE_SRC'][55], $SWSORT, $SWSORTCHK);

	$menu_form->add_area('4');
	$menu_form->set_area_title('4', $MESSAGES['FILE_SRC'][15]);
	$menu_form->add_area_input_text('4', 'maxbytesize', '10', $MESSAGES['FILE_SRC'][16], $formdata->maxbytesize);

	$menu_form->add_area('5');
	$menu_form->set_area_title('5', $MESSAGES['FILE_SRC'][17]);
	$menu_form->add_area_input_text('5', 'minwordsize', '10', $MESSAGES['FILE_SRC'][18], $formdata->minwordsize);

	$menu_form->add_area('6');
	$menu_form->set_area_title('6', $MESSAGES['FILE_SRC'][19]);
	$menu_form->add_area_input_text('6', 'textsize', '10', $MESSAGES['FILE_SRC'][20], $formdata->textsize);

	$menu_form->add_area('7');
	$menu_form->set_area_title('7', $MESSAGES['FILE_SRC'][21]);
	$GZIP[1] = $MESSAGES['FILE_SRC'][23];
	$GZIP[2] = $MESSAGES['FILE_SRC'][22];
	$GZCHECKED[2] = true;
	$menu_form->add_area_select_radio('7', 'gzipcompr', $MESSAGES['FILE_SRC'][21], $GZIP, $GZCHECKED);


	$menu_form->add_area_hidden_value('1', 'phpcmsaction', 'SEARCH');
	$menu_form->add_area_hidden_value('1', 'action', 'startindex');
	$menu_form->add_button('submit', 'index', $MESSAGES['FILE_SRC'][25]);
	$menu_form->compose_form();
	DrawBottomLine($MESSAGES['FILE_SRC'][00]);
	DrawFooter();
}

function unslash_dir(&$directory) {
	if($directory[strlen($directory) - 1] == '/' AND strlen($directory) != 1) {
		$directory = substr($directory, 0, -1);
	}
}

// check formdata
function select_action() {
	global $DEFAULTS, $formdata, $session, $MESSAGES;

	// set values
	if(isset($formdata->index)) {

		$formdata->startpath = trim($formdata->startpath);
		unslash_dir($formdata->startpath);
		if(!file_exists($DEFAULTS->DOCUMENT_ROOT.$formdata->startpath) OR $formdata->startpath == '') {
			draw_search_menu($MESSAGES['FILE_SRC']['ERR'][1]);
			soft_exit();
		} else {
			$session->set_var('startpath', $formdata->startpath);
		}

		$formdata->excludepath1 = trim($formdata->excludepath1);
		unslash_dir($formdata->excludepath1);
		if(!file_exists($DEFAULTS->DOCUMENT_ROOT.$formdata->excludepath1)) {
			draw_search_menu($MESSAGES['FILE_SRC']['EXPATH'][ERR1]);
			soft_exit();
		} else {
			$session->set_var('excludepath1', $formdata->excludepath1);
		}

		$formdata->excludepath2 = trim($formdata->excludepath2);
		unslash_dir($formdata->excludepath2);
		if(!file_exists($DEFAULTS->DOCUMENT_ROOT.$formdata->excludepath2)) {
			draw_search_menu($MESSAGES['FILE_SRC']['EXPATH'][ERR2]);
			soft_exit();
		} else {
			$session->set_var('excludepath2', $formdata->excludepath2);
		}

		$formdata->excludepath3 = trim($formdata->excludepath3);
		unslash_dir($formdata->excludepath3);
		if(!file_exists($DEFAULTS->DOCUMENT_ROOT.$formdata->excludepath3)) {
			draw_search_menu($MESSAGES['FILE_SRC']['EXPATH'][ERR3]);
			soft_exit();
		} else {
			$session->set_var('excludepath3', $formdata->excludepath3);
		}

		$formdata->excludepath4 = trim($formdata->excludepath4);
		unslash_dir($formdata->excludepath4);
		if(!file_exists($DEFAULTS->DOCUMENT_ROOT.$formdata->excludepath4)) {
			draw_search_menu($MESSAGES['FILE_SRC']['EXPATH'][ERR4]);
			soft_exit();
		} else {
			$session->set_var('excludepath4', $formdata->excludepath4);
		}

		$formdata->excludepath5 = trim($formdata->excludepath5);
		unslash_dir($formdata->excludepath5);
		if(!file_exists($DEFAULTS->DOCUMENT_ROOT.$formdata->excludepath5)) {
			draw_search_menu($MESSAGES['FILE_SRC']['EXPATH'][ERR5]);
			soft_exit();
		} else {
			$session->set_var('excludepath5', $formdata->excludepath5);
		}

		$formdata->excludepath6 = trim($formdata->excludepath6);
		unslash_dir($formdata->excludepath6);
		if(!file_exists($DEFAULTS->DOCUMENT_ROOT.$formdata->excludepath6)) {
			draw_search_menu( $MESSAGES['FILE_SRC']['EXPATH'][ERR6] );
			soft_exit();
		} else {
			$session->set_var('excludepath6', $formdata->excludepath6);
		}

		$formdata->excludepath7 = trim($formdata->excludepath7);
		unslash_dir($formdata->excludepath7);
		if(!file_exists($DEFAULTS->DOCUMENT_ROOT.$formdata->excludepath7)) {
			draw_search_menu($MESSAGES['FILE_SRC']['EXPATH'][ERR7]);
			soft_exit();
		} else {
			$session->set_var('excludepath7', $formdata->excludepath7);
		}

		$formdata->excludepath8 = trim($formdata->excludepath8);
		unslash_dir($formdata->excludepath8);
		if(!file_exists($DEFAULTS->DOCUMENT_ROOT.$formdata->excludepath8)) {
			draw_search_menu($MESSAGES['FILE_SRC']['EXPATH'][ERR8]);
			soft_exit();
		} else {
			$session->set_var('excludepath8', $formdata->excludepath8);
		}

		$formdata->datadir = trim($formdata->datadir);
		unslash_dir($formdata->datadir);
		if(!file_exists($DEFAULTS->DOCUMENT_ROOT.$formdata->datadir ) OR $formdata->datadir == '') {
			draw_search_menu($MESSAGES['FILE_SRC']['ERR'][2]);
			soft_exit();
		}

		// check rights
		$fp = @fopen($DEFAULTS->DOCUMENT_ROOT.$formdata->datadir.'/test.db', 'w+');
		if(!$fp) {
			$norights = true;
		} else {
			fclose($fp);
			unlink($DEFAULTS->DOCUMENT_ROOT.$formdata->datadir.'/test.db');
			$session->set_var('datadir', $formdata->datadir);
		}

		if(isset($norights)) {
			draw_search_menu($MESSAGES['FILE_SRC']['ERR'][3]);
			soft_exit();
		}

		// set fields to be indexed
		// set field for page title
		if(trim($formdata->titlefield) == '') {
			draw_search_menu($MESSAGES['FILE_SRC']['ERR'][11]);
			soft_exit();
		} else {
			$session->set_var('titlefield', $formdata->titlefield);
			$fieldstoindex[0] = $formdata->titlefield;
		}
		// set main content field
		if(trim($formdata->contentfield) == '') {
			draw_search_menu($MESSAGES['FILE_SRC']['ERR'][12]);
			soft_exit();
		} else {
			$session->set_var('contentfield', $formdata->contentfield);
			$fieldstoindex[1] = $formdata->contentfield;
		}
		// set additional content fields
		$temp_fields = explode(";", $formdata->addedfields);
		$i=0;$ix=2;$c=count($temp_fields);
		for($i; $i<$c; $i++) {
			if(trim($temp_fields[$i]) != '') {
				$fieldstoindex[$ix] = $temp_fields[$i];
				$ix++;
			}
		}
		$session->set_var('fieldstoindex', implode(":", $fieldstoindex));


		$session->set_var('uselocaldb', $formdata->uselocaldb);
		$formdata->stopwords = trim($formdata->stopwords);

		if($formdata->uselocaldb == $MESSAGES['OFF']) {
			if(!file_exists($DEFAULTS->DOCUMENT_ROOT.$formdata->stopwords) OR $formdata->stopwords == '') {
				draw_search_menu($MESSAGES['FILE_SRC']['ERR'][4]);
				soft_exit();
			} else {
				$session->set_var('stopwords', $DEFAULTS->DOCUMENT_ROOT.$formdata->stopwords);
			}
		} else {
			if(!file_exists($DEFAULTS->DOCUMENT_ROOT.$formdata->datadir.'/stop.db')) {
				$docopy = copy($DEFAULTS->DOCUMENT_ROOT.$formdata->stopwords, $DEFAULTS->DOCUMENT_ROOT.$formdata->datadir.'/stop.db');
				if ($docopy !== TRUE) {
					$fp = @fopen($DEFAULTS->DOCUMENT_ROOT.$formdata->datadir.'/stop.db', 'w+');
					if(!$fp) {
						draw_search_menu($MESSAGES['FILE_SRC']['ERR'][8].
						$DATADB_PATH.'/stop.db'.$MESSAGES['FILE_SRC']['ERR'][9]);
						soft_exit();
					} else {
						fclose($fp);
					}
				}
			}
			$session->set_var('stopwords', $DEFAULTS->DOCUMENT_ROOT.$formdata->datadir.'/stop.db');
		}

		$session->set_var('sortstopdb', $formdata->sortstopdb);
		$session->set_var('optimized', false);

		// check stop.db rights
		if($session->vars['sortstopdb'] == $MESSAGES['ON']) {
			$fp = @fopen($session->vars['stopwords'], 'r+');
			if(!$fp) {
				draw_search_menu($MESSAGES['FILE_SRC']['ERR'][13]);
				soft_exit();
			} else {
				fclose($fp);
			}
		} // check stop.db rights

		$formdata->maxbytesize = trim($formdata->maxbytesize);
		if(!isset($formdata->maxbytesize) OR $formdata->maxbytesize < 1000) {
			draw_search_menu($MESSAGES['FILE_SRC']['ERR'][5]);
			soft_exit();
		} else {
			$session->set_var('maxbytesize', $formdata->maxbytesize);
		}

		$formdata->minwordsize = trim($formdata->minwordsize);
		if(!isset($formdata->minwordsize) OR $formdata->minwordsize < 3) {
			draw_search_menu($MESSAGES['FILE_SRC']['ERR'][6]);
			soft_exit();
		} else {
			$session->set_var('minwordsize', $formdata->minwordsize);
		}

		$formdata->textsize = trim($formdata->textsize);
		if(!isset($formdata->textsize) OR $formdata->textsize < 10) {
			draw_search_menu($MESSAGES['FILE_SRC']['ERR'][7]);
			soft_exit();
		} else {
			$session->set_var('textsize', $formdata->textsize);
		}
		$session->set_var('gzipcompr', $formdata->gzipcompr);

		$session->set_var('task', 'indexer');
		$session->set_var('step', '0');
		indexer();
	}
}

// start indexer
function indexer() {
	global
		$session,
		$DEFAULTS,
		$MESSAGES,
		$SEARCHDATADIR,
		$STOP,
		$STOP_MAX,
		$MIN_WORD_SIZE,
		$EXTENSION,
		$PEXTENSION,
		$EXDIR,
		$PHPCMS_DOC_ROOT,
		$MAX_BYTE_SIZE;

	include(PHPCMS_INCLUDEPATH.'/class.search_indexer_phpcms.php');
	soft_exit();
}

// selection
set_time_limit(0);

$formdata = new get_form;

if(isset($formdata->select)) {
	$select = $formdata->u_select;
}
if(isset($session->vars['select'])) {
	$select = strtoupper($session->vars['select']);
}
if(!isset($select)) {
	$select = '';
}

switch($select) {
	case 'INDEXER':
		indexer();
		soft_exit();
	case 'MENU':
		select_action();
		soft_exit();
	default:
		draw_search_menu($MESSAGES['FILE_SRC'][24]);
}
soft_exit();

?>
