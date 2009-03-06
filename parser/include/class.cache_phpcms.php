<?php
/* $Id: class.cache_phpcms.php,v 1.8.2.37 2006/06/18 18:07:29 ignatius0815 Exp $ */
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

class CheckFile {
	var $parms = '';
	var $name  = '';
	var $path  = '';
	var $CACHE = FALSE;
	var $CACHE_TAG = '';  // contains the realname tag
	var $CACHE_PAGE = '';  // contains the path to the MD5-cachefile
	var $REAL_PAGE = '';  // contains the path of the  contentfile

	function CheckFile() {
		global
			$DEFAULTS,
			$PHPCMS,
			$PHP,
			$MESSAGES;

		$DEFAULTS->StartPage = $DEFAULTS->SCRIPT_PATH.'/'.$DEFAULTS->SCRIPT_NAME . '?phpcmsaction=FRAMESET';

		$PfadUndDatei = $this->GetFile();

		$this->name = basename($PfadUndDatei);
		$this->path = dirname($PfadUndDatei);
		if($this->path == '\\' OR $this->path == '/') {
			$this->path = '';
		}
		if(strstr(strtolower($this->name),'phpcmscredits')) {
			include(PHPCMS_INCLUDEPATH.'/class.lib_phpcmsrus.php');
			exit;
		}
		// there's no contentfile with this name -> errorpage or errormessage
		if(!file_exists($DEFAULTS->DOCUMENT_ROOT.$this->path.'/'.$this->name)) {
			$errorname = basename($DEFAULTS->ERROR_PAGE_404);
			$errorpath = dirname($DEFAULTS->ERROR_PAGE_404);
			if($errorpath == '\\' OR $errorpath == '/') {
				$errorpath = '';
			}
			if(!isset($DEFAULTS->ERROR_PAGE_404) OR
				$DEFAULTS->ERROR_PAGE_404 == '' OR
				!file_exists($DEFAULTS->DOCUMENT_ROOT.$errorpath.'/'.$errorname)) {
				ExitError(7, $DEFAULTS->DOCUMENT_ROOT.$this->path.'/'.$this->name);
			} else {
				$this->name = $errorname;
				$this->path = $errorpath;
			}
		}
		if(substr($this->name, -strlen($DEFAULTS->PAGE_EXTENSION)) != $DEFAULTS->PAGE_EXTENSION) {
			$extension = substr ($this->name, strrpos($this->name, '.'));
			$extension = strtoupper($extension);

			// graphics-caching
			if($extension == '.GIF' OR
				$extension == '.PNG' OR
				$extension == '.JPG' OR
				$extension == '.CSS' OR
				$extension == '.JS' OR
				$extension == '.HTM' OR
				$extension == '.HTML')

			{	if(isset($GLOBALS["HTTP_IF_MODIFIED_SINCE"]) AND $DEFAULTS->CACHE_CLIENT == 'on') {
					$FILE_NAME = $this->path.'/'.$this->name;
					$FILE_TIME = filemtime($DEFAULTS->DOCUMENT_ROOT.$FILE_NAME);
					$OrigDate = trim(gmdate("D, d M Y H:i:s", $FILE_TIME)." GMT");
					$RequestDate = trim($GLOBALS["HTTP_IF_MODIFIED_SINCE"]);
					if($OrigDate == $RequestDate) {
						if($PHP->API() == 'cgi') {
							Header('Status: 304 Not Modified');
						} else {
							Header('HTTP/1.1 304 Not Modified');
						}
						exit;
					}
				}
				$fsize = filesize($DEFAULTS->DOCUMENT_ROOT.$this->path.'/'.$this->name);
				$fd = fopen($DEFAULTS->DOCUMENT_ROOT.$this->path.'/'.$this->name, "rb");
				$contents = fread($fd, $fsize);
				$contents = trim($contents);
				$fsize = strlen($contents);
				fclose($fd);
				if($extension == '.PNG') {
					Header("Content-type: image/png");
				}
				if($extension == '.GIF') {
					Header("Content-type: image/gif");
				}
				if($extension == '.JPG' || $extension == '.JPEG' || $extension == '.JPE') {
					Header("Content-type: image/jpeg");
				}
				if($extension == '.CSS') {
					Header("Content-type: text/css");
				}
				if($extension == '.JS') {
					Header("Content-type: application/x-javascript");
				}
				if($extension == '.HTM' ||  $extension == '.HTML') {
					Header("Content-type: text/html");
				}
				if($DEFAULTS->CACHE_CLIENT != 'on') {
					$PHP->NoCache();
				} else {
					Header("Cache-Control: public");
					Header("Expires: ".gmdate("D, d M Y H:i:s", time() + $DEFAULTS->PROXY_CACHE_TIME)." GMT");
					Header("Last-Modified: ".gmdate("D, d M Y H:i:s", filemtime($DEFAULTS->DOCUMENT_ROOT.$this->path.'/'.$this->name))." GMT");
					$PHP->p3pHeader();
				}
				Header("Content-Length: ".$fsize);
				echo $contents;
				exit;
			}
			Header('Location: '.$DEFAULTS->DOMAIN_NAME.$this->path.'/'.$this->name);
			exit;
		}
		// if request is for verification only, answer and exit
		$FILE_NAME = $this->path.'/'.$this->name;
		if((!isset($PHPCMS->_query_string['template']) OR $PHPCMS->_query_string['template'] == '') AND
		   !in_array('debug', array_keys($PHPCMS->_query_string),TRUE) AND ($DEFAULTS->CACHE_CLIENT == 'on')) {
			if(isset($GLOBALS["HTTP_IF_MODIFIED_SINCE"])) {
				$OrigDate = trim(gmdate("D, d M Y H:i:s", filemtime($DEFAULTS->DOCUMENT_ROOT.$FILE_NAME))." GMT");
				$RequestDate = trim($GLOBALS["HTTP_IF_MODIFIED_SINCE"]);
				if($OrigDate == $RequestDate) {
					if($PHP->API() == 'cgi') {
						Header('Status: 304 Not Modified');
					} else {
						Header('HTTP/1.1 304 Not Modified');
					}
					exit;
				}
			}
		}

		if(!extension_loaded('zlib')) { // check if gzip is installed
			$DEFAULTS->GZIP = 'off';
		}

		if(isset($PHPCMS->_query_string['template']) && $PHPCMS->_query_string['template'] != '') {
			$DEFAULTS->CACHE_STATE = 'off';
		}
		if(in_array('debug', array_keys($PHPCMS->_query_string),TRUE)) {
			$DEFAULTS->CACHE_STATE = 'off';
		}

		// check for newer cached page
		$CACHE_PATH = $DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->CACHE_DIR;

		// BOF MD5-encoded filenames in the cache (mjahn)
		$this->REAL_PAGE = $GLOBALS['_SERVER']['SERVER_NAME'];
		if(strtolower(substr($this->REAL_PAGE,0,4)) == 'www.') {
			$this->REAL_PAGE = substr($this->REAL_PAGE,4);
		}
		$this->REAL_PAGE .= $FILE_NAME;
		// MD5 encoding of the filename
		$MD5_NAME = md5(substr($this->REAL_PAGE,0,-strlen($DEFAULTS->PAGE_EXTENSION)));
		$MD5_NAME .= $DEFAULTS->PAGE_EXTENSION;
		$FILE_NAME = $MD5_NAME;
		$this->CACHE_TAG = '<!-- PHPCMS FILENAME '.$this->REAL_PAGE.' -->'."\n";
		// EOF MD5-encoded filenames in the cache (mjahn)

		$this->CACHE_PAGE = $CACHE_PATH.'/'.$FILE_NAME;
		if($DEFAULTS->CACHE_STATE != 'on' OR isset($PHPCMS->_query_string['template']) ) {
			$this->CACHE = false;
			if($DEFAULTS->GZIP == 'on' AND !strstr($FILE_NAME, $DEFAULTS->DYN_EXTENSION)) {
				$FILE_NAME = str_replace($DEFAULTS->PAGE_EXTENSION, '.gz', $FILE_NAME);
				$this->CACHE_PAGE = $CACHE_PATH.'/'.$FILE_NAME;
			}
			return;
		}
		// check if there is a cached page for this contentfile
		// and check if the cachefile has to be recreated

		// is there a static cachefile without gzip
		if(file_exists($this->CACHE_PAGE)) {
			$PageFiletime = filemtime($DEFAULTS->DOCUMENT_ROOT.$this->path.'/'.$this->name);
			$CachedPageFileTime = filemtime ($this->CACHE_PAGE);
			if($PageFiletime < $CachedPageFileTime) {
				// actual non-gzip-cachefile but GZIP is now on
				// -> create a new gzip-cachefile
				// -> delete the old one
				if ($DEFAULTS->GZIP == 'on') {
					$this->CACHE = false;
					unlink($this->CACHE_PAGE);  // delete the old non-gzip-cachfile
					$this->CACHE_PAGE = str_replace($DEFAULTS->PAGE_EXTENSION, '.gz', $this->CACHE_PAGE);
				}
				// actual non-gzip-cachfeile and GZIP is off
				else {
					$this->CACHE = true;
				}
			// old cachfile
			} else {
				$this->CACHE = false;
			}
			if (filesize($this->CACHE_PAGE)==0) {
			  $this->CACHE = false;
			}
			return true;
		}
		$temp = $CACHE_PATH.'/'.str_replace($DEFAULTS->PAGE_EXTENSION, '.gz', $FILE_NAME);
		// is there a static cachefile with gzip
		if(file_exists($temp)) {
			$this->CACHE_PAGE = $temp;
			$PageFiletime = filemtime($DEFAULTS->DOCUMENT_ROOT.$this->path.'/'.$this->name);
			$CachedPageFileTime = filemtime($this->CACHE_PAGE);
			if($PageFiletime < $CachedPageFileTime) {
				// actual gzip-cachefile but GZIP is now off
				// -> create a new non-gzip-cachefile
				// -> delete the old one
				if ($DEFAULTS->GZIP == 'off') {
					$this->CACHE = false;
					unlink($this->CACHE_PAGE);  // delete the old gzip-cachfile
					$this->CACHE_PAGE = str_replace('.gz',$DEFAULTS->PAGE_EXTENSION, $this->CACHE_PAGE);
				}
				// actual gzip-cachefile AND GZIP is on -> all OK
				else {
					$this->CACHE = true;
				}
				$this->CACHE = true;
			} else {
				$this->CACHE = false;
			}
			if (filesize($this->CACHE_PAGE)==0) {
			  $this->CACHE = false;
			}
			return true;
		}
		$temp = $CACHE_PATH.'/'.str_replace($DEFAULTS->PAGE_EXTENSION, $DEFAULTS->DYN_EXTENSION, $FILE_NAME);
		// dynamic cache with or without gzip
		if(file_exists($temp)) {
			$this->CACHE_PAGE = $temp;
			$PageFiletime = filemtime($DEFAULTS->DOCUMENT_ROOT.$this->path.'/'.$this->name);
			$CachedPageFileTime = filemtime ($this->CACHE_PAGE);
			if($PageFiletime < $CachedPageFileTime) {
				$this->CACHE = true;
			} else {
				$this->CACHE = false;
			}
			if (filesize($this->CACHE_PAGE)==0) {
			  $this->CACHE = false;
			}
			return true;
		}
		// if there's no cachefile of any kind
		// -> create a new one
		if($DEFAULTS->GZIP == 'on') {
			$FILE_NAME = str_replace($DEFAULTS->PAGE_EXTENSION, '.gz', $FILE_NAME);
			$this->CACHE_PAGE = $CACHE_PATH.'/'.$FILE_NAME;
		}
		$this->CACHE = false;
	} // end of constructor Checkfile

	function GetFile() {
		global $QUERY_STRING, $DEFAULTS;

		if(stristr($QUERY_STRING, 'FILE=')) {
			// extracting filequery
			$pos = strpos(strtoupper($QUERY_STRING), 'FILE=');
			$temp = substr($QUERY_STRING, $pos + 5);
			if($pos = strpos($temp, '?')) {
				$this->parms = substr($temp, $pos);  // save params
				$temp = substr($temp, 0, $pos);
			}
			if($pos = strpos($temp, '&')) {
				$this->parms = substr($temp, $pos);  // save params
				$temp = substr($temp, 0, $pos);
			}
			// recognize and handle '..' in parameter file
			// should fix bug #634963
			$temp = explode('/',$temp);
			for($i=0;$i<count($temp);$i++) {
				if($temp[$i] == '..') {
					unset($temp[$i]);
					if($i > 0) {
						unset($temp[$i-1]);
					}
					$temp = explode('/',implode('/',$temp));
					$i = -1;
				}
			}
			$temp = htmlspecialchars(implode('/',$temp));

			// filequery is empty? -> set the defaultvalue
			if(trim($temp) == '') {
				$temp = '/'.$DEFAULTS->PAGE_DEFAULTNAME;
				$temp.= $DEFAULTS->PAGE_EXTENSION;
			}

			// filequery exists, but filename is empty? -> set the defaultvalue for filename
			if(!stristr($temp, $DEFAULTS->PAGE_EXTENSION) AND
				!stristr($temp, '.gif') AND
				!stristr($temp, '.png') AND
				!stristr($temp, '.jpg') AND
				!stristr($temp, '.js') AND
				!stristr($temp, '.css') AND
				!stristr($temp, '.htm') AND
				!stristr($temp, '.html'))

			{   if(substr($temp, -1) != '/') {
					$temp = trim($temp).'/'.$DEFAULTS->PAGE_DEFAULTNAME;
					$temp.= $DEFAULTS->PAGE_EXTENSION;
				} else {
					$temp = trim($temp).$DEFAULTS->PAGE_DEFAULTNAME;
					$temp.= $DEFAULTS->PAGE_EXTENSION;
				}
			}
		}
		if(!isset($temp)) {
			Header('Location: '.$DEFAULTS->DOMAIN_NAME.$DEFAULTS->StartPage);
			exit;
		} else {
			return $temp;
		}
	} // End of function GetFile()
}

// timestamp for last referrers
if(!function_exists('get_microtime')) {
	function get_microtime() {
		list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
	}
}

if($DEFAULTS->LANGUAGE == 'us') {
	$DEFAULTS->LANGUAGE = 'en';
}
include(PHPCMS_INCLUDEPATH.'/language.'.$DEFAULTS->LANGUAGE);

include(PHPCMS_INCLUDEPATH.'/class.gzip_phpcms.php');

$CHECK_PAGE = new CheckFile;
$GZIP = new gzip;

// load the page out of the cache directory
if(isset($CHECK_PAGE->CACHE) AND $CHECK_PAGE->CACHE == true AND !in_array('debug', array_keys($PHPCMS->_query_string),TRUE)) {
	// static cached with gzip
	if(stristr($CHECK_PAGE->CACHE_PAGE, '.gz')) {
		$GZIP->gprint($CHECK_PAGE->CACHE_PAGE);
		exit;
	}
	// dynamic cached
	if(stristr($CHECK_PAGE->CACHE_PAGE,$DEFAULTS->DYN_EXTENSION)) {
		include($CHECK_PAGE->CACHE_PAGE);
		exit;
	}
	// static cached without gzip
	if(file_exists($CHECK_PAGE->CACHE_PAGE)) {
		Header("Content-type: text/html");
		Header('X-Content-Parsed-By: phpCMS '.$DEFAULTS->VERSION);
		$PHP->p3pHeader();
		if($DEFAULTS->CACHE_CLIENT != 'on') {
			$PHP->NoCache();
		} else {
			Header("Cache-Control: public");
			Header("Expires: ".gmdate("D, d M Y H:i:s", time() + $DEFAULTS->PROXY_CACHE_TIME)." GMT");
			Header("Last-Modified: ".gmdate("D, d M Y H:i:s", filemtime ($DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'/'.$CHECK_PAGE->name))." GMT");
		}
		$ContentLength = filesize($CHECK_PAGE->CACHE_PAGE) - strlen($CHECK_PAGE->CACHE_TAG);
		Header("Content-Length: ".$ContentLength);

		$fp = fopen($CHECK_PAGE->CACHE_PAGE, 'rb');
		// filter the cache-tag out of the code
		$out = fread($fp,strlen($CHECK_PAGE->CACHE_TAG));
		if ( $out != $CHECK_PAGE->CACHE_TAG) {
			rewind($fp);
		} else {
			fseek($fp,strlen($CHECK_PAGE->CACHE_TAG),SEEK_SET);
		}
		fpassthru($fp);
		exit;
	}
}

// recreate the cachepage
include(PHPCMS_INCLUDEPATH.'/class.parser_phpcms.php');
include(PHPCMS_INCLUDEPATH.'/class.realtime_phpcms.php');

?>
