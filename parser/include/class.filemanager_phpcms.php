<?php
/* $Id: class.filemanager_phpcms.php,v 1.2.2.38 2006/06/18 18:07:29 ignatius0815 Exp $ */
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
   |    Tobias Dönz (tobiasd)
   |    Beate Paland (beate76)
   |    Markus Richert (e157m369)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

/*
********************************************
  class FILEMANAGER
  in class.filemanager_phpcms.php

  created the basics:
             Michael Brauchl
  rebuild and extended:
             Markus Richert, 2003-04-09

  purpose:   This is the phpCMS FileManager

             Self-construkting class by call
             with 'new FILEMANAGER'.
********************************************
*/

class FILEMANAGER {
	var $WORK_DIR;
	var $FILEURL;

	var $TOOLBAR;
	var $LINK;
	var $STATUS;
	var $FORM;
	var $CONTENT;
	var $MESSAGES;

	var $COLS;
	var $FILETYPES;
	var $TEMPLATES;
	var $LIST;
	var $SORT;

	////////////////////////////////////////////////////////////////////////////////
	// BASE-FUNCTION OF THE FILEMANGER /////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////
	function FILEMANAGER() {
		global $DEFAULTS, $DOCUMENT, $_FILES, $_REQUEST;

		$this->FORM = '';
		$this->MESSAGES = $GLOBALS['MESSAGES']['FILEMANAGER'];
		// set the working-directory
		if(!isset($_REQUEST['WORK_DIR'])) {
			$DEFAULTS->FILEMANAGER_STARTDIR = trim($DEFAULTS->FILEMANAGER_STARTDIR);
			if(substr($DEFAULTS->FILEMANAGER_STARTDIR, strlen($DEFAULTS->FILEMANAGER_STARTDIR) - 1) != '/') {
				$DEFAULTS->FILEMANAGER_STARTDIR = $DEFAULTS->FILEMANAGER_STARTDIR.'/';
			}
			if(is_dir($DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->FILEMANAGER_STARTDIR)) {
				$this->WORK_DIR = $DEFAULTS->FILEMANAGER_STARTDIR;
			} else {
				$this->WORK_DIR = '/';
				$this->STATUS = $this->MESSAGES['STATUS']['NO_STARTDIR'];
			}
		} else {
			$this->WORK_DIR = $_REQUEST['WORK_DIR'];
		}
		if(strrpos($this->WORK_DIR, '/') != strlen($this->WORK_DIR) - 1) {
			$this->WORK_DIR .= '/';
		}
		// correct cache-directory
		if(strrpos($DEFAULTS->CACHE_DIR, '/') != strlen($DEFAULTS->CACHE_DIR) - 1) {
			$DEFAULTS->CACHE_DIR .= '/';
		}
		// prepare fileurl
		if(isset($_REQUEST['fileurl']))
			$this->FILEURL = rawurldecode($_REQUEST['fileurl']);
		else
			$this->FILEURL = '';
		// set the sortation-mode
		if(isset($_REQUEST['SORT']) AND is_array($_REQUEST['SORT'])) {
			$key = array_keys($_REQUEST['SORT']);
			$this->SORT = explode('||', $key[0]);
		} else {
			$this->SORT = array('filename', 'asc');
		}
		// set the default-link
		$this->LINK = $DEFAULTS->SELF.'?phpcmsaction=FILEMANAGER&SORT['.$this->SORT[0].'||'.$this->SORT[1].']=true&WORK_DIR='.$this->WORK_DIR;
		// set the table-vars for display
		$this->COLS = array(
			array($this->blindgif(), 22, 'center'),
			array($this->blindgif(), 22, 'center'),
			array($this->blindgif(), 22, 'center'),
			array($this->blindgif(), 30, 'center'),
			array('Name',            '', 'left'  ),
			array('Size',            70, 'right' ),
			array('Modified',       160, 'right' ),
			array('Perm\'s',         60, 'right' ),
			array($this->blindgif(),  1, 'center')
		);
		// load filetypes and templates registry
		include(PHPCMS_INCLUDEPATH.'/filemanager/config.filemanager.php');

		// the user pressed CANCEL? -> set the $action to nothing
		if(isset($_REQUEST['cancel'])) {
			$_REQUEST['action'] = '';
		}
		// $action via toolbars?
		if(isset($_REQUEST['tool_action']) AND is_array($_REQUEST['tool_action'])) {
			$key = array_keys($_REQUEST['tool_action']);
			$_REQUEST['action'] = $key[0];
		}
		$LOAD = true;
		// switch on action
		if(!isset($_REQUEST['action'])){
			$_REQUEST['action'] = '';
		}
		switch($_REQUEST['action']) {
			// go to document_root
			case 'home':
				$this->WORK_DIR = '/';
				// changing the work_dir needs to reset the default-link
				$this->LINK = $DEFAULTS->SELF.'?phpcmsaction=FILEMANAGER&SORT['.$this->SORT[0].'||'.$this->SORT[1].']=true&WORK_DIR='.$this->WORK_DIR;
				$this->TOOLBAR[10] = array(
					array('chdr', $this->MESSAGES['TOOLBAR']['RELOAD'], 'reload.gif'),
				);
				break;

			// display file with highlighting
			case 'show':
				$this->show();
				$this->TOOLBAR[10] = array(
					array(''),
					array('chdr', $this->MESSAGES['TOOLBAR']['CHDR'], 'filetypes/folder.gif'),
					array('edit', $this->MESSAGES['TOOLBAR']['EDIT'])
				);
				break;

			// edit file
			case 'edit':
				$this->edit();
				$filetype = $this->check_filetype(basename($_REQUEST['fileurl']));
				$this->TOOLBAR[10] = array(
					array(''),
					array('chdr', $this->MESSAGES['TOOLBAR']['CHDR'], 'filetypes/folder.gif'),
					array('show', $this->MESSAGES['TOOLBAR']['SHOW'], 'filetypes/'.$filetype['icon'])
				);
				break;

			// create directory or file
			case 'create':
				$this->create();
				break;

			// copy/move/rename file or directory
			case 'move':
				$this->TOOLBAR[10] = array(
					array(''),
					array('chdr', $this->MESSAGES['TOOLBAR']['CHDR'], 'filetypes/folder.gif'),
				);
				$this->move();
				break;

			// delete file or directory
			case 'delete':
				$this->TOOLBAR[10] = array(
					array(''),
					array('chdr', $this->MESSAGES['TOOLBAR']['CHDR'], 'filetypes/folder.gif'),
				);
				$this->delete();
				break;

			// upload file
			case 'upload':
			 	if($_FILES['userfile']['name']) {
					$done = @copy($_FILES['userfile']['tmp_name'], $DEFAULTS->DOCUMENT_ROOT.$this->WORK_DIR.$_FILES['userfile']['name']);
					if($done) {
						$this->STATUS = $this->MESSAGES['STATUS']['UPLOAD']['FROM'].$_FILES['userfile']['name'].$this->MESSAGES['STATUS']['UPLOAD']['TO'].$this->WORK_DIR.$this->MESSAGES['STATUS']['UPLOAD']['SUCCESS'];
					} else {
						$this->STATUS = $this->MESSAGES['STATUS']['UPLOAD']['FROM'].$_FILES['userfile']['name'].$this->MESSAGES['STATUS']['UPLOAD']['TO'].$this->WORK_DIR.$this->MESSAGES['STATUS']['UPLOAD']['FAILED'];
					}
				} else {
					$this->STATUS = $this->MESSAGES['STATUS']['UPLOAD']['NO_FILE'];
				}
				break;

			// default
			default:
				$this->TOOLBAR[10] = array(
					array('chdr', $this->MESSAGES['TOOLBAR']['RELOAD'], 'reload.gif'),
				);
				// set cacheview delete-all-action
				if($this->WORK_DIR == $DEFAULTS->CACHE_DIR) {
					if(!is_dir($DEFAULTS->DOCUMENT_ROOT.$this->WORK_DIR)) {
						$this->STATUS[] = $this->MESSAGES['STATUS']['CACHE']['NO_DIR'];
						$LOAD = false;
					} else {
						$this->STATUS[] = $this->MESSAGES['STATUS']['CACHE']['SHOW'];
						$this->TOOLBAR[20] = array(
							array(''),
							array('clear_cache', $this->MESSAGES['TOOLBAR']['CLEAR_CACHE'], 'delete_all.gif'),
						);
					}
					// set the status of caching
					if($DEFAULTS->CACHE_STATE == 'on') {
						$this->STATUS[] = $this->MESSAGES['STATUS']['CACHE']['ON'];
					} else {
						$this->STATUS[] = $this->MESSAGES['STATUS']['CACHE']['OFF'];
					}
					// execute deleting cache
					if(isset($_REQUEST['action']) AND $_REQUEST['action'] == 'clear_cache') {
						$handle = opendir($DEFAULTS->DOCUMENT_ROOT.$this->WORK_DIR);
						while(false != ($cachefile = readdir($handle))) {
							if(is_dir($DEFAULTS->DOCUMENT_ROOT.$this->WORK_DIR.'/'.$cachefile) OR $cachefile[0] == '.') {
								continue;
							}
							$done = @unlink($DEFAULTS->DOCUMENT_ROOT.$this->WORK_DIR.'/'.$cachefile);
						}
						$done ? $msg = $this->MESSAGES['STATUS']['CACHE']['CLEARED'] : $msg = $this->MESSAGES['STATUS']['CACHE']['CLEAR_FAILED'];
						$this->STATUS[] = $msg;
						closedir($handle);
					}
				}
		}
		DrawHeader($this->MESSAGES['TITLE']);
		DrawTopLine($this->MESSAGES['TITLE']);
		// display the top toolbar
		echo $DOCUMENT->TABLE_FONT.'&nbsp;'.$this->WORK_DIR.'</font></td></tr>';
		if($LOAD) {
			echo '<form name="select" method="POST" action="'.$this->LINK.'">'.
				'<tr><td>'.$this->ToolBar('top').'</td></tr>'.
				$this->FORM.
				'<tr><td>';
		}

		// display the content from an action-function ...
		if(isset($this->CONTENT) AND is_array($this->CONTENT)) {
			echo implode("\n", $this->CONTENT);
		} elseif(isset($this->CONTENT)) {
			echo $this->CONTENT;
		// ... or display the directory-list
		} else {
			if($LOAD) {
				$this->LoadDir();
				$this->DisplayDir();
				echo '</td></tr></form>';
			}
			// display the status line
			if(isset($this->STATUS) AND is_array($this->STATUS)){
				$MSG = implode('<br />', $this->STATUS);
			} else {
				$MSG = '';
			}
			if($MSG) {
				echo '<tr><td bgcolor="#eeeeee"><b>'.$DOCUMENT->TABLE_FONT.$MSG.'</font></b></td></tr>';
				echo '<tr><td>'.$this->blindgif('', '12').'</td></tr>';
			}
			if($LOAD AND $this->WORK_DIR != $DEFAULTS->CACHE_DIR) {
				// display the instant-actions title
				echo '<tr><td bgcolor="'.$DOCUMENT->DARK_COLOR.'">'.$DOCUMENT->STATUS_FONT.'INSTANT-ACTIONS</font></td></tr>';
				echo '<tr><td bgcolor="#EEEEEE"><table border="0" cellspacing="0" cellpadding="0" width="100%">';

				// loading the template-list
				if(is_readable(PHPCMS_INCLUDEPATH.'/filemanager/')) {
					$templates = $DOCUMENT->TABLE_FONT.'&nbsp;'.$this->MESSAGES['INSTANT']['TEMPLATE']['TITLE'].'<select name="template">';
					$templates .= '<option value="empty">'.$this->MESSAGES['INSTANT']['TEMPLATE']['EMPTY'].'</option>';
					foreach($this->TEMPLATES as $template) {
						$templates .= '<option value="'.$template[0].'"';
						if(isset($_REQUEST['template']) AND $_REQUEST['template'] == $template[0]) {
							$templates .= ' selected';
						}
						$templates .= '>'.$template[1].'</option>';
					}
					$templates .= '</select></font>';
				}

				// display the instant-actions
				$instants = array(
					array('upload',  $this->MESSAGES['INSTANT']['UPLOAD'],  'upload', 'userfile',    'file', '60'),
					array('newdir',  $this->MESSAGES['INSTANT']['NEWDIR'],  'create', 'create_dir',  'text', '60'),
					array('newfile', $this->MESSAGES['INSTANT']['NEWFILE'], 'create', 'create_file', 'text', '40', $templates),
				);
				foreach($instants as $instant) {
					if(!isset($instant[6])){
						$instant[6] = '';
					}
					echo '<tr><form enctype="multipart/form-data" method="post" action="'.$this->LINK.'">'.
						'<td width="22"><input type="image" src="'.$DEFAULTS->SCRIPT_PATH.'/gif/filemanager/'.$instant[0].'.gif" width="16" heigth="16" border="0" title="'.$instant[1].'"></td>'.
						'<td><input type="hidden" name="action" value="'.$instant[2].'"><input name="'.$instant[3].'" type="'.$instant[4].'" size="'.$instant[5].'">'.$instant[6].'</td>'.
						'</form></tr>';
				}
				echo '</table>';
			}
		}
		if($LOAD AND $this->WORK_DIR != $DEFAULTS->CACHE_DIR) {
			echo '</td></tr>';
		}

		// display the filespace needed
		$DEFAULTS->FILEMANAGER_DIRSIZE == 'on' ?
			$DS = $this->MESSAGES['DIRSIZE']['ACTIVE'].$this->display_size($this->size('dir', $DEFAULTS->DOCUMENT_ROOT.$this->WORK_DIR)) :
			$DS = $this->MESSAGES['DIRSIZE']['INACTIVE'];
		echo '<tr><td bgcolor="'.$DOCUMENT->DARK_COLOR.'" align="right">'.$DOCUMENT->STATUS_FONT.$DS.'</font></td></tr>'.
			'</table>';
		DrawFooter();
		exit;
	}

////////////////////////////////////////////////////////////////////////////////
// FUNCTIONS FOR THE ACTIONS ///////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////////////////////
	// DISPLAY THE DIRECTORIES AND FILES ///////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////
	function DisplayDir() {
		global $DEFAULTS, $DOCUMENT, $_REQUEST;

		$el = "\n";
		// display the head row
		echo '<table border="0" cellspacing="0" cellpadding="1" width="100%">'.$el;
		echo $this->DisplayRow('top');
		// display the sort-row
		$sorter = array('', 'extension', 'show', 'browse', 'filename', 'size', 'changed', 'permissions', '');
		echo $this->DisplayRow('sort', $sorter);

		// walk through the directory- and filelist
		foreach(array_keys($this->LIST) as $list) {
			foreach(array_keys($this->LIST[$list]) as $sortkey) {
				foreach($this->LIST[$list][$sortkey] as $file) {
					// reload submitted selections
					if(isset($_REQUEST['SELECTION']) AND is_array($_REQUEST['SELECTION'])) {
						in_array($file['enc_fileurl'], $_REQUEST['SELECTION'], TRUE) ? $chk = ' checked' : $chk = '';
					} else {
						$chk = '';
					}
					// set the content for each file, some will be replaced going on...
					$values = array(
						array('<input type="checkbox" name="SELECTION[]" value="'.$file['enc_fileurl'].'" title="SELECT '.$file['filename'].'"'.$chk.'>', '#eeeeee'),
						array($this->blindgif()),
						array($this->blindgif()),
						array($this->blindgif()),
						array($DOCUMENT->TABLE_FONT.$file['shortname'].'</font>'),
						array($DOCUMENT->TABLE_FONT.$this->display_size($file['size']).'</font>'),
						array($DOCUMENT->TABLE_FONT.$file['changedate'].'</font>'),
						array($DOCUMENT->TABLE_FONT.$file['permissions'].'</font>'),
						array($this->blindgif()),
					);
					// it is a directory
					if($list == 'dir' OR $list == 'parent') {
						// set the parent arrow
						if($file['filename'] == '..') {
							$values[0] = array($this->blindgif(), '#eeeeee');
							$values[4] = array($DOCUMENT->TABLE_FONT.'<b>..</b></font>');
							$link = substr($this->LINK, 0, strpos($this->LINK, '&WORK_DIR=')).'&WORK_DIR='.str_replace('\\', '/', dirname($this->WORK_DIR));
							$file['filename'] = '';
						} else {
							$link = $this->LINK.$file['filename'];
						}
						// check for executable dir and display a link there or not
						if(isset($file['perm']) AND $file['perm'] == 'exec_read' AND isset($file['icon'])) {
							$msg = $this->MESSAGES['DIRLIST']['CHDR']['SHOW'];
							$values[1] = array('<a href="'.$link.'&action=chdr"><img src="'.$DEFAULTS->SCRIPT_PATH.'/gif/filemanager/filetypes/'.$file['icon'].'" border="0" title="'.$msg[0].$file['filename'].$msg[1].'"></a>', '#eeeeee');
						} elseif(isset($file['icon'])) {
							$msg = $this->MESSAGES['DIRLIST']['CHDR']['NO_PERMS'];
							$values[1] = array('<img src="'.$DEFAULTS->SCRIPT_PATH.'/gif/filemanager/filetypes/'.$file['icon'].'" border="0" title="'.$msg[0].$file['filename'].$msg[1].'">');
						}
						$values[2] = array($this->blindgif());
					// it is a file
					} elseif($list == 'file') {
						// set the show-link or not
						if($file['show']) {
							$msg = $this->MESSAGES['DIRLIST']['SHOWFILE'];
							$values[1] = array('<a href="'.$this->LINK.'&action=show&fileurl='.$file['enc_fileurl'].'&show='.$file['show'].'"><img src="'.$DEFAULTS->SCRIPT_PATH.'/gif/filemanager/filetypes/'.$file['icon'].'" border="0" title="'.$msg[0].$file['filename'].$msg[1].'"></a></td>', '#eeeeee');
						} else {
							$values[1] = array('<img src="'.$DEFAULTS->SCRIPT_PATH.'/gif/filemanager/filetypes/'.$file['icon'].'" border="0"></td>');
						}
						// set the edit-link or not
						if($file['show'] == 'text') {
							$msg = $this->MESSAGES['DIRLIST']['EDITFILE'];
							$values[2] = array('<a href="'.$this->LINK.'&action=edit&fileurl='.$file['enc_fileurl'].'"><img src="'.$DEFAULTS->SCRIPT_PATH.'/gif/filemanager/edit.gif" border="0" title="'.$msg[0].$file['filename'].$msg[1].'"></a>', '#eeeeee');
						} else {
							$values[2] = array($this->blindgif());
						}
					}
					// set the browse-link or not
					if(isset($file['browse'])) {
						$msg = $this->MESSAGES['DIRLIST']['BROWSE'];
						$values[3] = array('<a href="'.$file['browseurl'].'" target="_blank"><img src="'.$DEFAULTS->SCRIPT_PATH.'/gif/filemanager/browse.gif" border="0" title="'.$msg[0].$file['displayname'].$msg[1].'"></a>');
					} else {
						$values[3] = array($this->blindgif());
					}
					// now display the row for this file
					echo $this->DisplayRow(false, $values);
				}
			}
		}
		// display the toolbar for selected files
		echo $this->ToolBar('actions');
		echo $this->DisplayRow('blind', '8');
		echo '</table>';
	}

	////////////////////////////////////////////////////////////////////////////////
	// SHOW A FILE /////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////
	function show() {
		global $DEFAULTS, $DOCUMENT, $_REQUEST;

		if($_REQUEST['show'] == 'image') {
			// show the image-file
			$this->CONTENT = '<center><img src="'.$this->FILEURL.'"></center>';
			$this->STATUS = $this->MESSAGES['STATUS']['SHOW']['FILE'].$this->FILEURL;
		} elseif($_REQUEST['show'] == 'text') {
			$this->CONTENT[] = '<table border="0" cellspacing="1" cellpadding="3" width="100%">';
			$this->COLS = array(
				array('', '5" valign="top', 'left'),
				array('', false, 'left'),
			);
			$this->CONTENT[] = '<input type="hidden" name="fileurl" value="'.$this->FILEURL.'">';
			// highlight the filecode or submitted code
			ob_start();
			if(isset($_REQUEST['tempview']) && $_REQUEST['tempview'] != '') {
				highlight_string($_REQUEST['code']);
				$this->CONTENT[] = '<input type="hidden" name="tempview" value="true"><input type="hidden" name="code" value="'.htmlspecialchars($_REQUEST['code']).'">'.
					'<input type="hidden" name="show" value="text"><input type="hidden" name="action" value="edit">';
				$this->CONTENT[] = $this->DisplayRow(false, array(array($this->blindgif(), '#eeeeee'), array($DOCUMENT->TABLE_FONT.$this->MESSAGES['SHOW']['EDIT_INFO'].'</font>', '#eeeeee')));
				$this->CONTENT[] = $this->DisplayRow(false, array(array($this->blindgif(), '#eeeeee'), array('<input type="submit" name="confirm" value="'.$this->MESSAGES['SAVE'].'">', '#eeeeee')));
				$this->CONTENT[] = $this->DisplayRow('blind', '8');
				$msg = $this->MESSAGES['STATUS']['SHOW']['EDIT'];
				$this->STATUS = $msg[0].$this->FILEURL.$msg[1];
			} else {
				highlight_file($DEFAULTS->DOCUMENT_ROOT.$this->FILEURL);
				$msg = $this->MESSAGES['STATUS']['SHOW']['FILE'];
				$this->STATUS = $msg[0].$this->FILEURL.$msg[1];
			}
			$buffer = explode('<br />', ob_get_contents());
			ob_end_clean();
			// print code into a table by line
			foreach($buffer as $key => $value) {
				$this->CONTENT[] = $this->DisplayRow(false, array(array('<code>'.$key.'</code>', '#cccccc'), array('<code>'.$value.'</code>', '#eeeeee')));
			}
			$this->CONTENT[] = '</table>';
		}
	}

	////////////////////////////////////////////////////////////////////////////////
	// EDIT A FILE /////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////
	function edit() {
		global $DEFAULTS, $_REQUEST;

		$FIELDS = $ERROR = $MSG = $CONTENT = '';
		// load the file
		if(isset($_REQUEST['tempview']) && $_REQUEST['tempview'] != '') {
			$CONTENT = $_REQUEST['code'].'</textarea>';
			$FIELDS = '<input type="hidden" name="tempview" value="true">';
		} else {
			$fp = @fopen($DEFAULTS->DOCUMENT_ROOT.$this->FILEURL, "r");
			if($fp) {
				$contents = fread($fp, filesize($DEFAULTS->DOCUMENT_ROOT.$this->FILEURL));
				$CONTENT = htmlspecialchars($contents);
			} else {
				$msg = $this->MESSAGES['EDIT']['LOAD_ERROR'];
				$ERROR = $msg[0].$this->FILEURL.$msg[1];
			}
		}
		// set some hidden fields
		$FIELDS .= '<input type="hidden" name="fileurl" value="'.$this->FILEURL.'">'.
			'<input type="hidden" name="show" value="text">'.
			'<input type="hidden" name="tempview" value="true">'.
			'<input type="hidden" name="action" value="edit">';
		// display a textarea containing the file
		$MSG = array($this->MESSAGES['STATUS']['EDIT'], $this->MESSAGES['EDIT']['SAVE_ERROR']);
		$this->display_edit($MSG, $CONTENT, $FIELDS, $ERROR);
	}

	////////////////////////////////////////////////////////////////////////////////
	// EDIT A FILE (DISPLAY-FUNCTION, USED WITH EDIT AND FILE-CREATE) //////////////
	////////////////////////////////////////////////////////////////////////////////
	function display_edit($MSG, $CONTENT, $FIELDS, $ERROR) {
		global $DEFAULTS, $_REQUEST;

		if(isset($_REQUEST['confirm']) AND $this->FILEURL) {
			// user has done editing.
			$fp = @fopen($DEFAULTS->DOCUMENT_ROOT.$this->FILEURL, "w");
			if($fp) {
				// save and return to main screen
				fputs($fp, stripslashes($_REQUEST['code']));
				fclose($fp);
				$this->STATUS = $MSG[0]['SAVED'][0].$this->FILEURL.$MSG[0]['SAVED'][1];
				return;
			} else {
				// saving to file failed, show edit again
				$ERROR = $MSG[1][0].$this->FILEURL.$MSG[1][1];
				$this->STATUS = $MSG[0]['FAILED']['FILE'].$this->FILEURL.$MSG[0]['FAILED']['FILE'][1];
				$write_error = true;
			}
		}
		if($this->FILEURL) {
			// prepare table
			$this->CONTENT[] = '<table border="0" cellspacing="0" cellpadding="6" width="100%">';
			$this->COLS = array(
				array('', false, 'center'),
			);
			// display file in textarea
			if($ERROR) {
				$this->CONTENT[] = $this->DisplayRow(false, array(array($ERROR, '#eeeeee')));
			}
			$SIZE = explode(',', $DEFAULTS->FILEMANAGER_AREA_SIZE);
			$this->CONTENT[] = $this->DisplayRow(false, array(array(
				'<textarea name="code" cols="'.trim($SIZE[0]).'" rows="'.trim($SIZE[1]).'" wrap="off">'.$CONTENT.'</textarea>', '#eeeeee'
			)));
			// display the submit-buttons
			$this->CONTENT[] = $this->DisplayRow(false, array(array(
				$FIELDS.
				'<input type="submit" name="confirm" value="'.$this->MESSAGES['SAVE'].'">&nbsp;'.
				'<input type="submit" name="cancel" value="'.$this->MESSAGES['CANCEL'].'">', '#eeeeee'
			)));
			$this->CONTENT[] = '</table>';
			if(!isset($write_error) || $write_error == '') {
				// set status-message for 'working...'
				$this->STATUS = $MSG[0]['WORKING'][0].$this->FILEURL.$MSG[0]['WORKING'][1];
			}
		}
	}

	////////////////////////////////////////////////////////////////////////////////
	// CREATE A FILE OR DIRECTORY //////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////
	function create() {
		global $DEFAULTS, $_REQUEST;

		$MSG = $CONTENT = $FIELDS = $ERROR = '';

		if(isset($_REQUEST['create_dir']) && $_REQUEST['create_dir'] != '') {
			$this->FILEURL = $this->WORK_DIR.$_REQUEST['create_dir'];
			if(file_exists($DEFAULTS->DOCUMENT_ROOT.$this->FILEURL)) {
				// directory already exists
				$msg = $this->MESSAGES['STATUS']['CREATE']['FAILED']['IS_DIR'];
				$this->STATUS = $msg[0].$this->FILEURL.$msg[1];
			} else {
				// create directory
				$oldumask = umask(0);
				$done = @mkdir($DEFAULTS->DOCUMENT_ROOT.$this->FILEURL, 0775);
				umask($oldumask);
				$done ? $msg = $this->MESSAGES['STATUS']['CREATE']['MADE_DIR'] : $msg = $this->MESSAGES['STATUS']['CREATE']['FAILED']['DIR'];
				$this->STATUS = $msg[0].$this->FILEURL.$msg[1];
			}
		} elseif($_REQUEST['create_file']) {
			$this->FILEURL = $this->WORK_DIR.$_REQUEST['create_file'];
			if(file_exists($DEFAULTS->DOCUMENT_ROOT.$this->FILEURL)) {
				// file already exists
				$msg = $this->MESSAGES['STATUS']['CREATE']['FAILED']['IS_FILE'];
				$this->STATUS = $msg[0].$this->FILEURL.$msg[1];
			} else {
				// load the selected template
				if(isset ($_REQUEST['template']) AND $_REQUEST['template'] != 'empty') {
					$fp = @fopen(PHPCMS_INCLUDEPATH.'/filemanager/template.'.$_REQUEST['template'].'.txt', "r");
					if($fp) {
						$contents = fread($fp, filesize(PHPCMS_INCLUDEPATH.'/filemanager/template.'.$_REQUEST['template'].'.txt'));
						$CONTENT = htmlspecialchars($contents);
					} else {
						$msg = $this->MESSAGES['CREATE']['LOAD_ERROR'];
						$ERROR = $msg[0].$this->FILEURL.$msg[1];
					}
				}
				// set some hidden fields
				$FIELDS = '<input type="hidden" name="create_file" value="'.$_REQUEST['create_file'].'">'.
					'<input type="hidden" name="template" value="'.
					(isset ($_REQUEST ['template']) ? $_REQUEST ['template'] : '').
					'">'. // what should $template be?
					'<input type="hidden" name="action" value="create">';
				// display a textarea that will be the file
				$MSG = array($this->MESSAGES['STATUS']['CREATE'], $this->MESSAGES['CREATE']['SAVE_ERROR']);
				$this->display_edit($MSG, $CONTENT, $FIELDS, $ERROR);
			}
		} elseif(isset($_REQUEST['create_dir'])) {
			// dirname not given
			$this->STATUS = $this->MESSAGES['STATUS']['CREATE']['FAILED']['NO_DIR'];
		} elseif(isset($_REQUEST['create_file'])) {
			// filename not given
			$this->STATUS = $this->MESSAGES['STATUS']['CREATE']['FAILED']['NO_FILE'];
		}
	}

	////////////////////////////////////////////////////////////////////////////////
	// MOVE/COPY/RENAME A FILE OR DIRECTORY ////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////
	function move() {
		global $DEFAULTS, $DOCUMENT, $_POST;
		if(is_array($_POST['SELECTION'])) {
			// user has confirmed renaming/moving/copying of the object
			if($_POST['confirm'] AND strlen($_POST['new_dir']) > 0) {
				if($_POST['create_dir']) {
					$oldumask = umask(0);
					$done = @mkdir($DEFAULTS->DOCUMENT_ROOT.$_POST['new_dir'], 0775);
					umask($oldumask);
					$done ? $msg = $this->MESSAGES['MOVE']['MADE_DIR'] : $msg = $this->MESSAGES['MOVE']['FAILED']['MAKE_DIR'];
					$MOVED[] = array($msg[0].'"'.$_POST['new_dir'].'"'.$msg[1]);
				}
				if(file_exists($DEFAULTS->DOCUMENT_ROOT.$_POST['new_dir']) AND is_dir($DEFAULTS->DOCUMENT_ROOT.$_POST['new_dir'])) {
					// the destination-dir is an existing directory
					$target_dir = $_POST['new_dir'];
					if($target_dir[strlen($target_dir) - 1] != '/') {
						// user has given a directory, but no closing '/'...
						$target_dir .= '/';
					}
					// do the action on the selected files
					foreach($_POST['SELECTION'] as $enc_fileurl) {
						$fileurl = rawurldecode($enc_fileurl);
						// set the target's filename
						if($_POST['SELECTION'][1]) {
							$target_file = $_POST['new_file_prefix'].basename($fileurl).$_POST['new_file_suffix'];
						} else {
							$target_file = $_POST['new_file'];
						}
						// set overwrite flag
						$_POST['overwrite'][rawurlencode($target_dir.$target_file)] ? $OVERWRITE = true : $OVERWRITE = false;

						if($fileurl == $target_dir.$target_file) {
							// user has changed nothing...
							$MOVED[] = array('identical', $target_dir.$target_file);
						} elseif($_POST['do'] == 'copy') {
							// copy the file, but first check for changes of the origin
							if(file_exists($DEFAULTS->DOCUMENT_ROOT.$fileurl)) {
								if(file_exists($DEFAULTS->DOCUMENT_ROOT.$target_dir.$target_file) AND !$OVERWRITE AND !$_POST['all_overwrite']) {
									// if the target exists, reprompt for overwrite
									$MOVED[] = array('file_exists', $target_dir.$target_file);
								} else {
									if(is_dir($DEFAULTS->DOCUMENT_ROOT.$fileurl)) {
										// the source is a directory -> walk through it and copy all
										$done = $this->recursive_movedir('copy', $DEFAULTS->DOCUMENT_ROOT.$fileurl, $DEFAULTS->DOCUMENT_ROOT.$target_dir.$target_file);
									} else {
										// the source is a file -> copy it
										$done = @copy($DEFAULTS->DOCUMENT_ROOT.$fileurl, $DEFAULTS->DOCUMENT_ROOT.$target_dir.$target_file);
									}
									$done ? $msg = $this->MESSAGES['MOVE']['COPIED'] : $msg = $this->MESSAGES['MOVE']['FAILED']['COPY'];
									$MOVED[] = array($msg[0].'"'.$fileurl.'"'.$msg[1].'"'.$target_dir.$target_file.'"'.$msg[2]);
								}
							} else {
								// source file no more exists
								$msg = $this->MESSAGES['MOVE']['FAILED']['NOEX_COPY'];
								$MOVED[] = array($msg[0].'"'.$fileurl.'"'.$msg[1]);
							}
						} else {
							// rename the file, but first check for re-submit or changes of the origin
							if(file_exists($DEFAULTS->DOCUMENT_ROOT.$fileurl)) {
								if(file_exists($DEFAULTS->DOCUMENT_ROOT.$target_dir.$target_file) AND !$OVERWRITE AND !$_POST['all_overwrite']) {
									// if the target exists, reprompt for overwrite
									$MOVED[] = array('file_exists', $target_dir.$target_file);
								} else {
									if(is_dir($DEFAULTS->DOCUMENT_ROOT.$fileurl)) {
										// the source is a directory -> walk through it and rename all
										$this->recursive_movedir('rename', $DEFAULTS->DOCUMENT_ROOT.$fileurl, $DEFAULTS->DOCUMENT_ROOT.$target_dir.$target_file);
									} else {
										// the source is a file -> rename it, deleting a possibly existing target-file was confirmed by 'overwrite'
										@unlink($DEFAULTS->DOCUMENT_ROOT.$target_dir.$target_file);
										$done = @rename($DEFAULTS->DOCUMENT_ROOT.$fileurl, $DEFAULTS->DOCUMENT_ROOT.$target_dir.$target_file);
									}
									// if the target dir is the source dir, it must have been a rename, not a move
									if(dirname($fileurl) == $target_dir) {
										$done ? $msg = $this->MESSAGES['MOVE']['RENAMED'] : $msg = $this->MESSAGES['MOVE']['FAILED']['RENAME'];
									} else {
										$done ? $msg = $this->MESSAGES['MOVE']['MOVED'] : $msg = $this->MESSAGES['MOVE']['FAILED']['MOVE'];
									}
									$MOVED[] = array($msg[0].'"'.$fileurl.'"'.$msg[1].'"'.$target_dir.$target_file.'"'.$msg[2]);
								}
							} else {
								// source file no more exists, check for action to show the message
								if(dirname($fileurl) == $target_dir) {
									$msg = $this->MESSAGES['MOVE']['FAILED']['NOEX_MOVE'];
								} else {
									$msg = $this->MESSAGES['MOVE']['FAILED']['NOEX_RENAME'];
								}
								$MOVED[] = array($msg[0].'"'.$fileurl.'"'.$msg[1]);
							}
						}
					}
				} elseif(file_exists($DEFAULTS->DOCUMENT_ROOT.$_POST['new_dir']) AND is_file($DEFAULTS->DOCUMENT_ROOT.$_POST['new_dir'])) {
					// the destination is an existing file
					$msg = $this->MESSAGES['MOVE']['FAILED']['DIR_IS_FILE'];
					$MOVED[] = array($msg[0].'"'.$_POST['new_dir'].'"'.$msg[1]);
				} elseif(!file_exists($DEFAULTS->DOCUMENT_ROOT.$_POST['new_dir'])) {
					// ask to create the dir
					$this->move_display('create_dir');
				}
			} else {
				// show input-fields
				$this->move_display();
			}
		} else {
			$this->STATUS = $this->MESSAGES['STATUS']['MOVE']['NO_SELECT'];
		}
		if(isset($MOVED)) {
			$this->move_display($MOVED);
		}
	}

	////////////////////////////////////////////////////////////////////////////////
	// MOVE/COPY/RENAME (DISPLAY-FUNCTION) /////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////
	function move_display($MOVED = false) {
		global $DEFAULTS, $DOCUMENT, $_POST;

		// set sortation to standard
		$this->SORT = array('filename', 'asc');
		// load the current directory
		$this->LoadDir();

		// list the selected file(s)
		$this->CONTENT[] = '<table border="0" cellspacing="0" cellpadding="3" width="100%">';
		$this->COLS = array(
			array('', false, 'left'),
		);
		$_POST['SELECTION'][1] ? $i = 'MANY' : $i = 'ONE';
		$this->CONTENT[] = $this->DisplayRow(false, array(array(
			$DOCUMENT->TABLE_FONT.'<b>'.$this->MESSAGES['MOVE']['SELECTED'][$i].'</b></font>', '#eeeeee'
		)));
		for($i = 0; $i < count($_POST['SELECTION']); $i++) {
			$_POST['SELECTION'][$i + 1] ? $sep = ',' : $sep = '';
			foreach(array_keys($this->LIST) as $list) {
				foreach(array_keys($this->LIST[$list]) as $sortkey) {
					foreach($this->LIST[$list][$sortkey] as $file) {
						if($file['enc_fileurl'] == $_POST['SELECTION'][$i]) {
							$this->CONTENT[] = $this->DisplayRow(false, array(array(
								$DOCUMENT->TABLE_FONT.'<span title="'.rawurldecode($file['enc_fileurl']).'">'.$file['displayname'].'</span><input type="hidden" name="SELECTION[]" value="'.$_POST['SELECTION'][$i].'">'.$sep.'</font>', '#eeeeee'
							)));
						}
					}
				}
			}
		}
		$this->CONTENT[] = $this->DisplayRow('blind', '8');

		// display action-choice
		$_POST['do'] == 'move' ? $sel[1] = ' checked' : $sel[0] = ' checked';
		$this->CONTENT[] = $this->DisplayRow(false, array(array(
			$DOCUMENT->TABLE_FONT.'<input type="radio" name="do" id="copy" value="copy"'.$sel[0].'><label for="copy">'.$this->MESSAGES['MOVE']['ACTIONS']['COPY'].'</label></font>', '#eeeeee'
		)));
		$this->CONTENT[] = $this->DisplayRow(false, array(array(
			$DOCUMENT->TABLE_FONT.'<input type="radio" name="do" id="move" value="move"'.$sel[1].'><label for="move">'.$this->MESSAGES['MOVE']['ACTIONS']['MOVE'].'</label></font>', '#eeeeee'
		)));
		$this->CONTENT[] = $this->DisplayRow('blind', '8');
		$this->CONTENT[] = $this->DisplayRow(false, array(
			array($DOCUMENT->TABLE_FONT.'<b>'.$this->MESSAGES['MOVE']['TO'].'</b></font>', '#eeeeee'),
		));

		// open the 2-col table
		$this->CONTENT[] = '</table>';
		$this->CONTENT[] = '<table border="0" cellspacing="0" cellpadding="3" width="100%">';
		$this->COLS = array(
			array('', 300, 'left'),
			array('', false, 'left'),
		);
		// input-field for target dir
		$_POST['new_dir'] ? $new_dir = $_POST['new_dir'] : $new_dir = $this->WORK_DIR;
		$this->CONTENT[] = $this->DisplayRow(false, array(
			array($DOCUMENT->TABLE_FONT.$this->MESSAGES['MOVE']['DIRNAME'].'</font>', '#eeeeee'),
			array($DOCUMENT->TABLE_FONT.'<input type="text" name="new_dir" value="'.$new_dir.'" size="40"></font>', '#eeeeee'),
		));

		// input-field(s) for the target filename(s), depending on the number of selected files
		if($_POST['SELECTION'][1]) {
			$this->CONTENT[] = $this->DisplayRow(false, array(
				array($DOCUMENT->TABLE_FONT.$this->MESSAGES['MOVE']['FILENAME']['PREFIX'].'</font>', '#eeeeee'),
				array($DOCUMENT->TABLE_FONT.'<input type="text" name="new_file_prefix" value="'.$_POST['new_file_suffix'].'" size="15"></font>', '#eeeeee'),
			));
			$this->CONTENT[] = $this->DisplayRow(false, array(
				array($DOCUMENT->TABLE_FONT.$this->MESSAGES['MOVE']['FILENAME']['SUFFIX'].'</font>', '#eeeeee'),
				array($DOCUMENT->TABLE_FONT.'<input type="text" name="new_file_suffix" value="'.$_POST['new_file_suffix'].'" size="15"></font>', '#eeeeee'),
			));
		} else {
			$_POST['new_file'] ? $new_file = $_POST['new_file'] : $new_file = rawurldecode($_POST['SELECTION'][0]);
			$this->CONTENT[] = $this->DisplayRow(false, array(
				array($DOCUMENT->TABLE_FONT.$this->MESSAGES['MOVE']['FILENAME']['FILE'].'</font>', '#eeeeee'),
				array($DOCUMENT->TABLE_FONT.'<input type="text" name="new_file" value="'.basename($new_file).'" size="40"></font>', '#eeeeee'),
			));
		}
		$this->CONTENT[] = '</table>';

		// submit-buttons
		$this->CONTENT[] = '<table border="0" cellspacing="0" cellpadding="3" width="100%">';
		$this->COLS = array(
			array('', false, 'center'),
		);
		$this->CONTENT[] = $this->DisplayRow('blind', '8');
		$this->CONTENT[] = $this->DisplayRow(false, array(array(
			'<input type="hidden" name="action" value="move">'.
			'<input type="submit" name="confirm" value="'.$this->MESSAGES['RUN'].'">&nbsp;'.
			'<input type="submit" name="cancel" value="'.$this->MESSAGES['CANCEL'].'">', '#eeeeee'
		)));

		// reprompting for creating a dir or changing the dir/file-values or just to show what's done
		$this->COLS = array(
			array('', false, 'left'),
		);
		if($MOVED) {
			$this->CONTENT[] = $this->DisplayRow('blind', '25');
			$this->CONTENT[] = $this->DisplayRow('blind', '2', '#cccccc');
			if($MOVED == 'create_dir') {
				$msg = $this->MESSAGES['MOVE']['MAKE_DIR'];
				$this->CONTENT[] = $this->DisplayRow(false, array(array(
					$DOCUMENT->TABLE_FONT.$msg[0].'"'.$_POST['new_dir'].'"'.$msg[1].'<br /><input type="checkbox" name="create_dir">'.$msg[2].'</font>', '#eeeeee'),
				));
			} elseif(is_array($MOVED)) {
				foreach($MOVED as $MOVE) {
					if($MOVE[0] == 'identical') {
						$_POST['do'] == 'copy' ? $msg = $this->MESSAGES['MOVE']['FAILED']['NO_SELFCOPY'] : $msg = $this->MESSAGES['MOVE']['FAILED']['NO_SELFMOVE'];
						$this->CONTENT[] = $this->DisplayRow(false, array(array(
							$DOCUMENT->TABLE_FONT.$msg.'</font>', '#eeeeee'),
						));
						break;
					} elseif($MOVE[0] == 'file_exists') {
						$msg = $this->MESSAGES['MOVE']['FAILED']['IS_FILE'];
						$this->CONTENT[] = $this->DisplayRow(false, array(array(
							$DOCUMENT->TABLE_FONT.$msg[0].'"'.$MOVE[1].'"'.$msg[1].'<input type="checkbox" name="overwrite['.rawurlencode($MOVE[1]).']" value="true">'.$msg[2].'</font>', '#eeeeee'),
						));
						$SHOW_OVERWRITE = true;
					} else {
						$this->CONTENT[] = $this->DisplayRow(false, array(array(
							$DOCUMENT->TABLE_FONT.$MOVE[0].'</font>', '#eeeeee'),
						));
					}
				}
				// show the submitter to overwrite all files
				if($SHOW_OVERWRITE) {
					$this->CONTENT[] = $this->DisplayRow('blind', '8');
					$this->CONTENT[] = $this->DisplayRow(false, array(array(
						$DOCUMENT->TABLE_FONT.'<input type="checkbox" name="all_overwrite"> <b>'.$this->MESSAGES['MOVE']['OVERWRITE_ALL'].'</b></font>', '#eeeeee'),
					));
				}
			}
			$this->CONTENT[] = $this->DisplayRow('blind', '8');
		}
		$this->CONTENT[] = '</table>';
	}

	////////////////////////////////////////////////////////////////////////////////
	// DELETE A FILE OR DIRECTORY //////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////
	function delete() {
		global $DEFAULTS, $DOCUMENT, $_POST;

		if(isset($_POST['SELECTION'])) {
			// set sortation to standard
			$this->SORT = array('filename', 'asc');
			// load the current directory
			$this->LoadDir();

			if(isset($_POST['confirm']) && $_POST['confirm'] != '') {
				// delete the files/directories
				for($i = 0; $i < count($_POST['SELECTION']); $i++) {
					(isset($_POST['SELECTION'][$i + 1]) && $_POST['SELECTION'][$i + 1] != '') ? $sep = ',' : $sep = '';
					foreach(array_keys($this->LIST) as $list) {
						foreach(array_keys($this->LIST[$list]) as $sortkey) {
							foreach($this->LIST[$list][$sortkey] as $file) {
								if($file['enc_fileurl'] == $_POST['SELECTION'][$i]) {
									$fileurl = rawurldecode($_POST['SELECTION'][$i]);
									if(is_dir($DEFAULTS->DOCUMENT_ROOT.$fileurl)) {
										$done = $this->recursive_rmdir($DEFAULTS->DOCUMENT_ROOT.$fileurl);
										$k = 'DIR';
									} else {
										$done = @unlink($DEFAULTS->DOCUMENT_ROOT.$fileurl);
										$k = 'FILE';
									}
									$done ? $d = 'DONE' : $d = 'FAILED';
									$msg = $this->MESSAGES['DELETE'][$k][$d];
									$DELETED[] = $msg[0].'<span title="'.rawurldecode($file['enc_fileurl']).'">'.$file['displayname'].'</span>'.$msg[1];
								}
							}
						}
					}
				}
			}
			$this->CONTENT[] = '<table border="0" cellspacing="0" cellpadding="3" width="100%">';
			$this->COLS = array(
				array('', false, 'left'),
			);
			if(isset($DELETED) && is_array($DELETED)) {
				// display the results
				foreach($DELETED as $DEL) {
					$this->CONTENT[] = $this->DisplayRow(false, array(array(
						$DOCUMENT->TABLE_FONT.$DEL.'</font>', '#eeeeee'),
					));
				}
			} else {
				// list the selected file(s)
				$this->CONTENT[] = $this->DisplayRow(false, array(array(
					$DOCUMENT->TABLE_FONT.'<b>'.$this->MESSAGES['DELETE']['TITLE'].'</b></font>', '#eeeeee'
				)));
				for($i = 0; $i < count($_POST['SELECTION']); $i++) {
					(isset($_POST['SELECTION'][$i + 1]) && $_POST['SELECTION'][$i + 1] != '') ? $sep = ',' : $sep = '';
					foreach(array_keys($this->LIST) as $list) {
						foreach(array_keys($this->LIST[$list]) as $sortkey) {
							foreach($this->LIST[$list][$sortkey] as $file) {
								if($file['enc_fileurl'] == $_POST['SELECTION'][$i]) {
									$this->CONTENT[] = $this->DisplayRow(false, array(array(
										$DOCUMENT->TABLE_FONT.'<span title="'.rawurldecode($file['enc_fileurl']).'">'.$file['displayname'].'</span>'.
										'<input type="hidden" name="SELECTION[]" value="'.$_POST['SELECTION'][$i].'">'.$sep.'</font>', '#eeeeee'
									)));
								}
							}
						}
					}
				}
				$this->CONTENT[] = $this->DisplayRow('blind', '8');
				$this->CONTENT[] = $this->DisplayRow(false, array(array(
					'<input type="hidden" name="action" value="delete">'.
					'<input type="submit" name="confirm" value="'.$this->MESSAGES['RUN'].'">&nbsp;'.
					'<input type="submit" name="cancel" value="'.$this->MESSAGES['CANCEL'].'">', '#eeeeee'
				)));
			}
			$this->CONTENT[] = '</table>';
		} else {
			$this->STATUS = $this->MESSAGES['STATUS']['DELETE']['NO_SELECT'];
		}
	}

////////////////////////////////////////////////////////////////////////////////
// HELPERS /////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////////////////////
	// LOADING THE CURRENT DIRECTORY INTO $this->LIST AND SORT IT AS REQUESTED /////
	////////////////////////////////////////////////////////////////////////////////
	function LoadDir() {
		global $DEFAULTS, $PHP, $_REQUEST;

		// prepare the array
		$this->LIST = array('parent' => array(), 'dir' => array(), 'file' => array());
		// check for needed exec- and read-permission
		// exec-check not effordable
		// change to better fonction width dir-object
		// correct bug with missing path on linux.

		if($PHP->OS() == 'win'
			OR is_readable($DEFAULTS->DOCUMENT_ROOT.$this->WORK_DIR)) {
			chdir($DEFAULTS->DOCUMENT_ROOT.$this->WORK_DIR);
			//$handle = opendir($DEFAULTS->DOCUMENT_ROOT.$this->WORK_DIR);
			$handle = dir($DEFAULTS->DOCUMENT_ROOT.$this->WORK_DIR);
			// load directories and files into seperate sub-arrays
			while($filename = $handle->read()) {
				$whole_filename = $DEFAULTS->DOCUMENT_ROOT.$this->WORK_DIR.$filename;
				$valid = false;
				if($filename == '.') {
					continue;
				} elseif($filename == '..') {
					$valid = 'parent';
				} elseif(is_dir($whole_filename)) {
					$valid = 'dir';
				} elseif(is_file($whole_filename)) {
					$valid = 'file';
				} else {
					continue;
				}
				unset($props);
				if(isset($valid)) {
					// load the universal file-properties
					$changed = @filemtime($whole_filename);
					$props = array(
						'filename'    => $filename,
						'filepath'    => $DEFAULTS->DOCUMENT_ROOT.$this->WORK_DIR.$filename,
						'enc_fileurl' => rawurlencode($this->WORK_DIR.$filename),
						'size'        => $this->size($valid, $filename),
						'changed'     => $changed,
						'changedate'  => date("d-m-Y H:i:s", $changed),
						'permissions' => sprintf("%o", (@fileperms($whole_filename)) & 0777),
					);
					// set the diplayed filename and browsing-url
					if($this->WORK_DIR == $DEFAULTS->CACHE_DIR
						AND $filename[0] != '.'
						AND !is_dir($props['filepath'])) {
						// get cachefile-extension (static / dynamic cache with or without gzip)
						preg_match("/.*\.(.+)/", $filename, $result);
						$props['cacheext'] = strtoupper($result[1]);
						//extract the real filename out of the cache-file (md5 method)
						$displayname = $this->get_realname($props['filepath']);
						// if there is no real filename in the cachefile use old method to determine the filename
						if((trim($displayname) == '') OR (trim($displayname) == '/')) {
							$displayname = str_replace('_', '/', $filename);
							$props['browseurl']   = '/'.$displayname;
						} else {
							$props['browseurl']   = 'http://'.$displayname;
						}
						$props['displayname'] = 'URL:'.$displayname.':'.$props['cacheext'];
						$shortlen = $DEFAULTS->CACHEVIEW_SHORTNAME_LENGTH;
					} else {
						$props['displayname'] = $filename;
						$props['browseurl']   = $this->WORK_DIR.$filename;
						$shortlen = $DEFAULTS->FILEMANAGER_SHORTNAME_LENGTH;
					}
					// set the shortname with title and dots
					strlen($props['displayname']) > $shortlen + 3 ?
						$props['shortname'] = substr($props['displayname'], 0, $shortlen).'...' :
						$props['shortname'] = $props['displayname'];
					$props['shortname'] = '<span title="'.htmlspecialchars($props['displayname']).'">'.htmlspecialchars($props['shortname']).'</span>';

					if($valid == 'parent' OR $valid == 'dir') {
						// directory need special icon-handling, depending on it's permissions
						if($PHP->OS() == 'win') {
							$props['icon'] = 'folder.gif';
							$props['perm'] = 'exec_read';
						} else {
							//handling of symlinks
							if (is_link($filename)) {
								$props['icon'] = 'symlink.gif';
								$props['perm'] = 'exec_read';
							} else {
								if (is_readable($whole_filename)) {
									$props['icon'] = 'folder.gif';
									$props['perm'] = 'exec_read';
								} else {
									$props['icon'] = 'folder_inactive.gif';
									$props['perm'] = 'none';
								}
							}
						}

						// set the parent-arrow
						if($filename == '..') {
							$props['icon'] = 'parent.gif';
						}
						$props['show']      = false;
						$props['browse']    = true;
						$props['extension'] = '___';
					} elseif($valid == 'file') {
						// it is a file -> add the props for its filetype
						$props = array_merge($props, $this->check_filetype($filename));
					}
					// add the props to the filelist, key is the value of the requested sortation
					if(isset($displayname)) {
						$this->SORT[0] == 'filename' ? $key = 'displayname' : $key = $this->SORT[0];
					} else {
						$key = $this->SORT[0];
					}
					// error message with E_ALL (try to sort files by extensions)
					// Notice: Undefined index: extension
					$this->LIST[$valid][$props[$key]][$props['displayname']] = $props;
				} // end if(isset($valid))
			} // end while
			// set the reverse-comparison, if descending order was requested
			if($this->SORT[1] == 'desc') {
				function cmp($a, $b) {
					return strnatcasecmp($a, $b) * -1;
				}
			} else {
				function cmp($a, $b) {
					return strnatcasecmp($a, $b);
				}
			}
			// sort the filelist
			foreach(array_keys($this->LIST) as $valid) {
				// sort in first instance by requested value
				uksort($this->LIST[$valid], ('cmp'));
				// sort in second instance by filename (userdefinable will be 1.2 ;-))
				foreach(array_keys($this->LIST[$valid]) as $second) {
					uksort($this->LIST[$valid][$second], 'cmp');
				}
			} // end foreach
		} // end if
	} // end LoadDir

	////////////////////////////////////////////////////////////////////////////////
	// EXTRACTING THE REAL FILENAME OUT OF A CACHE FILE ////////////////////////////
	////////////////////////////////////////////////////////////////////////////////
	function get_realname($file) {
		$fh = fopen($file, 'rb');
		$temp = fgets($fh, 1024);
		$temp .= fgets($fh, 1024);
		fclose($fh);
		preg_match('/<!--\sPHPCMS\sFILENAME\s(.*?)\s-->\s*/', $temp, $result);
		if(isset($result[1])){
			return $result[1];
		} else {
			return basename($file);
		}
	}

	////////////////////////////////////////////////////////////////////////////////
	// CHECKING THE FILETYE BY MATCHING WITH THE CONFIGURATIONS ////////////////////
	////////////////////////////////////////////////////////////////////////////////
	function check_filetype($filename) {
		$FOUND = false;
		// check for the filetype
		foreach($this->FILETYPES as $value) {
			if(eregi($value[0], $filename, $match)) {
				$props['icon']      = $value[1];
				$props['show']      = $value[2];
				$props['browse']    = $value[3];
				$props['extension'] = $match[0];
				$FOUND = true;
				break;
			}
		}
		// unknown filetype
		if(!$FOUND) {
			$props = $this->DEF_FILETYPE;
		}
		return($props);
	}

	////////////////////////////////////////////////////////////////////////////////
	// COPY/RENAME A DIRECTORY /////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////
	function recursive_movedir($action, $source, $target) {
		if(!is_dir($source)) {
			return false;
		}
		// create the target-dir
		if(!file_exists($target)) {
			$oldumask = umask(0);
			mkdir($target, 0775);
			umask($oldumask);
		}
		$sourcedir = opendir($source);

		while(($dir_entry = readdir($sourcedir)) !== false) {
			if($dir_entry != '.' && $dir_entry != '..') {
				if(is_dir($source.'/'.$dir_entry)) {
					// recurse with the subdir
					$this->recursive_movedir($action, $source.'/'.$dir_entry, $target.'/'.$dir_entry);
				} else {
					// delete an possibly existing target-file, overwrite was confirmed before
					if(file_exists($target.'/'.$dir_entry) AND $action == 'rename') {
						unlink($target.'/'.$dir_entry);
					}
					// it is a file, do the action (copy or rename)
					$action($source.'/'.$dir_entry, $target.'/'.$dir_entry);
				}
			}
		}
		closedir($sourcedir);
		unset($sourcedir);
		// renaming? the empty source-directory has to be deleted
		if($action == 'rename') {
			rmdir($source);
		}
		if(file_exists($target)) {
			return true;
		} else {
			return false;
		}
	}

	////////////////////////////////////////////////////////////////////////////////
	// DELETE A WHOLE DIRECTORY ////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////
	function recursive_rmdir($dir) {
		if(!is_dir($dir)) {
			return false;
		}
		$directory = opendir($dir);

		while(($dir_entry = readdir($directory)) !== false) {
			if($dir_entry != '.' && $dir_entry != '..') {
				if(is_dir($dir.'/'.$dir_entry)) {
					$this->recursive_rmdir($dir.'/'.$dir_entry);
				} else {
					unlink($dir.'/'.$dir_entry);
				}
			}
		}
		closedir($directory);
		unset($directory);
		if(@rmdir($dir)) {
			return true;
		} else {
			return false;
		}
	}

	////////////////////////////////////////////////////////////////////////////////
	// EVALUATING THE DIRECTORY- AND FILE-SIZES ////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////
	function size($mode, $file) {
		global $DEFAULTS, $PHP;

		if($mode == 'dir') {
			if($DEFAULTS->FILEMANAGER_DIRSIZE == 'off') {
				return false;
			}
			// exec- and read-permission needed for evaluating the dirsize
			if($PHP->OS() == 'win' OR (is_readable($file))) {
				return $this->get_dirsize($file);
			} else {
				return '???';
			}
		} elseif($mode == 'file') {
			return filesize($file);
		} else {
			return false;
		}
	}

	////////////////////////////////////////////////////////////////////////////////
	// EVALUATE A DIRSIZE (RECURSIVE) //////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////
	function get_dirsize($directory) {
		global $DEFAULTS;
		$total = 0;

		if($directory[strlen($directory) - 1] == '/') {
			$directory = substr($directory, 0, -1);
		}
		if($directory == $DEFAULTS->DOCUMENT_ROOT.'/..') {
			return 0;
		}
		// adding @ for dirsize-bug
		$to_dig = @dir($directory);

		if(is_object($to_dig)) {
			while($entry = $to_dig->read()) {
				if($entry == '..' || $entry == '.') {
					continue;
				}
				if(!is_dir($directory.'/'.$entry)) {
					$total += filesize($directory.'/'.$entry);
				} else {
					$total += $this->get_dirsize($directory.'/'.$entry);
				}
			}
		} else {
			return false;
		}
		return $total;
	}

////////////////////////////////////////////////////////////////////////////////
// BASIC DISPLAY-FUNCTIONS /////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

	////////////////////////////////////////////////////////////////////////////////
	// DISPLAY A FILESIZES HUMAN-READABLE //////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////
	function display_size($file_size) {
		if($file_size >= 1073741824) {
			$file_size = (round($file_size / 1073741824 * 100) / 100).' GB';
		} elseif($file_size >= 1048576) {
			$file_size = (round($file_size / 1048576 * 100) / 100).' MB';
		} elseif($file_size >= 1024) {
			$file_size = (round($file_size / 1024 * 100) / 100).' KB';
		} elseif($file_size != '') {
			$file_size = $file_size.' Byte';
		}
		return $file_size;
	}

	////////////////////////////////////////////////////////////////////////////////
	// DISPLAY A TOOLBAR ///////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////
	function ToolBar($which) {
		global $DEFAULTS;

		$format = '';
		$output = '';
		$spacer = '';

		if($which == 'top') {
			$actions = array(
				array('home', $this->MESSAGES['TOOLBAR']['HOME']),
			);
			if(is_array($this->TOOLBAR)) {
				foreach($this->TOOLBAR as $BAR) {
					$actions = array_merge($actions, $BAR);
				}
			}
			$spacer = '&nbsp;';
			$hf = array('<table border="0" cellspacing="0" cellpadding="0"><tr>', '</tr></table>');
		} elseif($which == 'actions') {
			$actions = array(
				array('move',   $this->MESSAGES['TOOLBAR']['MOVE']),
				array('delete', $this->MESSAGES['TOOLBAR']['DELETE']),
			);
			$format = ' align="center" valign="bottom" width="22"';
			$hf = array('<tr><td'.$format.' bgcolor="#eeeeee" rowspan="2"><img src="'.$DEFAULTS->SCRIPT_PATH.'/gif/filemanager/arrow.gif" width="16" heigth="24"></td><td colspan="'.(count($this->COLS) - 1).'">'.$this->blindgif('16', '4').'</td></tr>'.
				'<tr><td bgcolor="#eeeeee" colspan="'.(count($this->COLS) - 1).'">'.
				'<table border="0" cellspacing="0" cellpadding="0"><tr>', '</tr></table></td></tr>');
		}
		ksort($actions);
		foreach($actions as $action) {
			if (isset($action[2])){
				$img = $action[2];
			} else {
				$img = $action[0].'.gif';
			}
			if(isset($action[0]) && $action[0] != '') {
				if(!isset($action[1])){
					$action[1]='';
				}
				$output .= '<td'.$format.'><input type="IMAGE" name="tool_action['.$action[0].']" src="'.$DEFAULTS->SCRIPT_PATH.'/gif/filemanager/'.$img.'" border="0" width="16" height="16" title="'.$action[1].'">'.$spacer.'</td>'."\n";
			} else {
				$output .= '<td'.$format.'>'.$this->blindgif('16', '16').'</td>';
			}
		}
		return $hf[0].$output.$hf[1];
	}

	////////////////////////////////////////////////////////////////////////////////
	// DISPLAY ONE TABLE-ROW ///////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////
	function DisplayRow($mode, $values = '1', $color = '') {
		global $DEFAULTS, $DOCUMENT, $_REQUEST;

		$output = '<tr>';
		$i = 0;
		foreach($this->COLS as $col) {
			if(!isset($col[1])){
				$col[1] = '';
			}
			if($mode == 'top'){
				$output .= $this->DisplayCell($col[1], $col[2], $DOCUMENT->STATUS_FONT.$col[0].'</font>', $DOCUMENT->DARK_COLOR);
			} elseif($mode == 'sort') {
				if($values[$i] == '') {
					$content = $this->blindgif($col[1], '9');
				} else {
					$this->SORT == array($values[$i], 'asc') ? $active = '_active' : $active = '';
					$content = '<input type="image" src="'.$DEFAULTS->SCRIPT_PATH.'/gif/filemanager/up'.$active.'.gif" name="SORT['.$values[$i].'||asc]" border="0" width="10" height="7">';
					$this->SORT == array($values[$i], 'desc') ? $active = '_active' : $active = '';
					$content .= '<input type="image" src="'.$DEFAULTS->SCRIPT_PATH.'/gif/filemanager/down'.$active.'.gif" name="SORT['.$values[$i].'||desc]" border="0" width="10" height="7">';
				}
				$output .= $this->DisplayCell($col[1], $col[2], $content, '#cccccc');
			} elseif($mode == 'blind') {
				$output .= $this->DisplayCell($col[1], $col[2], $this->blindgif($col[1], $values), $color);
			} elseif(is_array($values)) {
				if(!isset($values[$i][1])){
					$values[$i][1] = '';
				}
				$output .= $this->DisplayCell($col[1], $col[2], $values[$i][0], $values[$i][1]);
			}
			$i++;
		}
		return $output.'</tr>'."\n\n";
	}

	////////////////////////////////////////////////////////////////////////////////
	// DISPLAY ONE TABLE-CELL //////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////
	function DisplayCell($width = false, $align = 'center', $content = false, $color = '') {
		$cellwidth = '';

		if($content === false) {
			$content = $this->blindgif();
		}
		if($width) {
			$cellwidth = ' width="'.$width.'"';
		}
		return '<td'.$cellwidth.' align="'.$align.'" bgcolor="'.$color.'" nowrap="nowrap">'.$content.'</td>'."\n";
	}

	////////////////////////////////////////////////////////////////////////////////
	// DISPLAY A BLINDGIF //////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////
	function blindgif($width = '1', $heigth = '20') {
		global $DEFAULTS;
		return '<img src="'.$DEFAULTS->SCRIPT_PATH.'/gif/nix.gif" width="'.$width.'" height="'.$heigth.'" border="0">';
	}
}

?>
