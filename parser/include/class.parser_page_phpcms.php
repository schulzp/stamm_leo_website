<?php
/* $Id: class.parser_page_phpcms.php,v 1.3.2.18 2006/06/18 18:07:31 ignatius0815 Exp $ */
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
   |    Martin Jahn (mjahn)
   |    Markus Richert (e157m369)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

class Page {
	function Page() {
		global $DEFAULTS;
		if($this->ReadPage() == 'html') {
			$DEFAULTS->PageType = 'html';
			return;
		}
		$this->CheckProject();
		$this->ReadProject();
		$this->ReplaceTemplate();
		$this->ProcessPlugins();
	}

	function ReadPage() {
		global $DEFAULTS, $CHECK_PAGE;
		// Read the page into an array
		$this->content = new File($DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'/'.$CHECK_PAGE->name);
		if($this->content->ReadFields($this) == 'html') {
			return 'html';
		}
	}

	function CheckProject() {
		global $DEFAULTS, $CHECK_PAGE;
		// Get the name of the path to the projectfile and check for availableness
    if (isset($this->content->PROJECT)) {
      $temp = trim($this->content->PROJECT[0]);
    } else {
      $temp = false;
    }
		if(!$temp) {
			$temp = $DEFAULTS->GLOBAL_PROJECT_FILE;
		}
		if(substr($temp, 0, 1) <> '/' AND strtoupper(substr($temp, 0, 7)) <> 'HTTP://') {
			$DEFAULTS->PROJECTFILENAME = $DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'/'.$temp;
		} elseif(substr($temp, 0, 1) == '/') {
			$DEFAULTS->PROJECTFILENAME = $DEFAULTS->DOCUMENT_ROOT.$temp;
		} else {
			$DEFAULTS->PROJECTFILENAME = $temp;
		}

		if(!file_exists($DEFAULTS->PROJECTFILENAME) OR strlen($temp) == 0) {
			if(isset($DEFAULTS->DEBUG) AND $DEFAULTS->DEBUG == 'on') {
				echo $DEFAULTS->PROJECTFILENAME.'<br />';
			}
			ExitError(9);
		}
		$this->project = new File($DEFAULTS->PROJECTFILENAME);
		$this->project->SetParas();
	}

	function ReadProject() {
		global $DEFAULTS, $plugindir;
		// Read the projectfile and set the values of the project
		$counter = count($this->project->tags);
		$DEFAULTS->PROJECT_HOME = $DEFAULTS->GLOBAL_PROJECT_HOME;
		for($i = 0; $i < $counter; $i++) {
			switch(strtoupper($this->project->tags[$i][0])) {
				// check Project-Root
				case 'HOME':
					//$DEFAULTS->PROJECT_HOME = trim($this->project->tags[$i][1]);
					$temp = trim($this->project->tags[$i][1]);
					if(!stristr($temp, '$GLOBAL_HOME')) {
						$DEFAULTS->PROJECT_HOME = trim($this->project->tags[$i][1]);
					}
					if(trim($DEFAULTS->PROJECT_HOME) == '/') {
						$DEFAULTS->PROJECT_HOME = '';
						$this->project->tags[$i][1] = '';
					}
					break;

				// check PlugIn Dir for this Project. if set, then overrule Standard from default
				case 'PLUGINDIR':
					$temp = trim($this->project->tags[$i][1]);
					if(!stristr($temp, '$PLUGINDIR')) {
						$DEFAULTS->PLUGINDIR = trim($this->project->tags[$i][1]);
					} else {
						$DEFAULTS->PLUGINDIR = $DEFAULTS->PLUGINDIR;
					}
					if(trim($DEFAULTS->PLUGINDIR) == '/') {
						$DEFAULTS->PLUGINDIR = '';
						$this->project->tags[$i][1] = '';
					}
					$plugindir = $DEFAULTS->PLUGINDIR;
					break;

				// read tagfile
				case 'TAGS':
					$temp = trim($this->project->tags[$i][1]);
					if(strlen($temp) != 0) {
						if(strtoupper(substr($temp, 0, 5)) == '$HOME') {
							$temp = $DEFAULTS->PROJECT_HOME.substr($temp, 5);
						}
						if(strtoupper(substr($temp, 0, 10)) == '$PLUGINDIR') {
							$temp = $DEFAULTS->PLUGINDIR.substr($temp, 10);
						}
						if(file_exists($DEFAULTS->DOCUMENT_ROOT.$temp)) {
							$DEFAULTS->TAGFILE = $DEFAULTS->DOCUMENT_ROOT.$temp;
							$this->tagfile = new File($DEFAULTS->TAGFILE);
							$this->tagfile->SetParas();
						} else {
							ExitError(12, $DEFAULTS->DOCUMENT_ROOT.$temp );
							//ExitError(12);
						}
					} else {
						unset($DEFAULTS->TAGFILE);
					}
					break;

				// read template
				case 'TEMPLATE':
					$temp = trim($this->project->tags[$i][1]);
					if(strtoupper(substr($temp, 0, 5)) == '$HOME') {
						$temp = $DEFAULTS->PROJECT_HOME.substr($temp, 5);
					} elseif(strtoupper(substr($temp, 0, 10)) == '$PLUGINDIR') {
						$temp = $DEFAULTS->PLUGINDIR.substr($temp, 10);
					}
					if(file_exists($DEFAULTS->DOCUMENT_ROOT.$temp)) {
						$DEFAULTS->TEMPLATE = $DEFAULTS->DOCUMENT_ROOT.$temp;
					} else {
						ExitError(10, $DEFAULTS->TEMPLATE);
					}
					break;

				// read menu
				case 'MENU':
					$temp = trim($this->project->tags[$i][1]);
					if(strtoupper(substr($temp, 0, 5)) == '$HOME') {
						$temp = $DEFAULTS->PROJECT_HOME.substr($temp, 5);
					} elseif(strtoupper(substr($temp, 0, 10)) == '$PLUGINDIR') {
						$temp = $DEFAULTS->PLUGINDIR.substr($temp, 10);
					}
					if(strlen($temp) != 0) {
						if(file_exists($DEFAULTS->DOCUMENT_ROOT.$temp)) {
							$DEFAULTS->MENU = $DEFAULTS->DOCUMENT_ROOT.$temp;
						} else {
							ExitError(13);
						}
					} else {
						unset($DEFAULTS->MENU);
					}
					break;

				// read menutemplate
				case 'MENUTEMPLATE':
					$temp = trim($this->project->tags[$i][1]);
					if(strtoupper(substr($temp, 0, 5)) == '$HOME') {
						$temp = $DEFAULTS->PROJECT_HOME.substr($temp, 5);
					} elseif(strtoupper(substr($temp, 0, 10)) == '$PLUGINDIR') {
						$temp = $DEFAULTS->PLUGINDIR.substr($temp,10);
					}
					if(isset($DEFAULTS->MENU)) {
						if(strlen($temp) != 0) {
							if(file_exists($DEFAULTS->DOCUMENT_ROOT.$temp)) {
								$DEFAULTS->MENUTEMPLATE = $DEFAULTS->DOCUMENT_ROOT.$temp;
							} else {
								ExitError(11);
							}
						} else {
							ExitError(14);
						}
					} else {
						unset($DEFAULTS->MENUTEMPLATE);
					}
					break;

				// read editpassword
				case 'EDITPASSWORD':
					$DEFAULTS->EDITPASSWORD = trim($this->project->tags[$i][1]);
					break;
			}
		}
		if(!isset($this->tagfile)) {
			ExitError(25);
		}
	}

	function FileExtension($file) {
		$pointpos = strrpos($file, '.');
		$extension = substr($file, $pointpos);
		return strtolower(trim($extension));
	}

	function ReplaceTemplate() {
		global $DEFAULTS, $CHECK_PAGE, $QUERY_STRING;

		if(!isset($QUERY_STRING)) {
			return;
		}
		if(!$temp = stristr($QUERY_STRING, 'template=')) {
			return;
		}
		$temp = substr($temp, 9);
		if(stristr($temp, '&')) {
			$temp = substr($temp, 0, strpos($temp, '&'));
		}
		if(stristr($temp, '?')) {
			$temp = substr($temp, 0, strpos($temp, '?'));
		}
		if($this->FileExtension($temp) <> $DEFAULTS->TEMPEXT) {
			return;
		}
		if(stristr($temp, '/.') == TRUE) {
			return;
		}
		if(stristr($temp, 'default.php') == TRUE) {
			return;
		}
		if(substr($temp, 0, 1) <> '/' AND strtoupper(substr($temp, 0, 7)) <> 'HTTP://') {
			if(strtoupper(substr($temp, 0, 5)) == '$HOME') {
				$DEFAULTS->TEMPLATE = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->PROJECT_HOME.substr($temp, 5);
			} elseif(strtoupper(substr($temp, 0, 10)) == '$PLUGINDIR') {
				$DEFAULTS->TEMPLATE = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->PLUGINDIR.substr($temp, 10);
			} else {
				$DEFAULTS->TEMPLATE = $DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'/'.$temp;
			}
		} elseif(substr($temp, 0, 1) == '/') {
			$DEFAULTS->TEMPLATE = $DEFAULTS->DOCUMENT_ROOT.$temp;
		} elseif(strtoupper(substr($temp, 0, 7)) == 'HTTP://') {
			$DEFAULTS->TEMPLATE = $temp;
		} else {
			$DEFAULTS->TEMPLATE = $DEFAULTS->DOCUMENT_ROOT.$temp;
		}
	}

	function ProcessPlugins() {
		global $DEFAULTS, $PHP, $CHECK_PAGE, $MENU, $plugindir;

		if(!isset($this->PLUGIN)) {
			return;
		}
		$PluginCount = count($this->PLUGIN);
		for($i = 0; $i < $PluginCount; $i++) {
			// correct Path
			unset($TempPath);
			unset($Path);
			unset($buffer);

			$TempPath = $this->PLUGIN[$i]['path'];
			if(substr($TempPath, 0, 1) <> '/' AND strtoupper(substr($TempPath, 0, 7)) <> 'HTTP://') {
				if(strtoupper(substr($TempPath, 0, 5)) == '$HOME') {
					$Path = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->PROJECT_HOME.substr($TempPath, 5);
				} elseif(strtoupper(substr($TempPath, 0, 10)) == '$PLUGINDIR') {
					$Path = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->PLUGINDIR.substr($TempPath, 10);
				} else {
					$Path = $DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'/'.$TempPath;
				}
			} elseif(substr($TempPath, 0, 1) == '/') {
				$Path = $DEFAULTS->DOCUMENT_ROOT.$TempPath;
			} else {
				$Path = $TempPath;
			}
			$this->PLUGIN[$i]['path'] = $Path;

			$buffer = $PHP->MakePlugin(
				$this->PLUGIN[$i]['path'],
				$this->PLUGIN[$i]['type'],
				$this->content,
				$DEFAULTS->CACHE_STATE,
				$DEFAULTS->CACHE_CLIENT,
				$DEFAULTS->PROXY_CACHE_TIME,
				$this->tagfile->tags,
				$MENU,
				$plugindir
			);

			if(isset($buffer)) {
				$this->content->{'CONTENT_PLUGIN_'.$i} = $buffer;
			}
		}
	}
}

?>