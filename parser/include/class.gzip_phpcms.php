<?php
/* $Id: class.gzip_phpcms.php,v 1.1.2.13 2006/06/18 18:07:29 ignatius0815 Exp $ */
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
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

/*
  editor: mjahn
  - rewrite the gzip-class so that the real filenames of the contentfiles can be
    saved in the cachefile and that the sourcecode of the page has no more the
    comment with real name in it
*/

class gzip {
	//
	function gzip() {

	}

	// remove the real name comment from the cachefile
	// than deliver the cache-page to the client
	function gprint(&$file) {
		global
			$DEFAULTS,
			$HTTP_ACCEPT_ENCODING,
			$CHECK_PAGE,
			$PHP;

		Header('Content-type: text/html');
		Header('X-Content-Parsed-By: phpCMS '.$DEFAULTS->VERSION);
		$PHP->p3pHeader();
		if($DEFAULTS->CACHE_CLIENT != 'on') {
			$PHP->NoCache();
		} else {
			Header('Cache-Control: public');
			Header('Expires: '.gmdate("D, d M Y H:i:s", time() + $DEFAULTS->PROXY_CACHE_TIME).' GMT');
			Header('Last-Modified: '.gmdate("D, d M Y H:i:s", filemtime($DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'/'.$CHECK_PAGE->name)).' GMT');
		}

		// cachefile is gzip
		if($DEFAULTS->GZIP == 'on'){
			if(stristr($HTTP_ACCEPT_ENCODING, 'gzip') AND $DEFAULTS->GZIP == 'on') {
				if(!stristr($HTTP_ACCEPT_ENCODING, 'x-gzip')) {
					$encoding = 'gzip';
				} else {
					$encoding = 'x-gzip';
				}
				// client does understand gzip
				$ContentLength = filesize($file) - strlen($CHECK_PAGE->CACHE_TAG);
				Header('Content-Length: '.$ContentLength);
				Header('Content-Encoding: '.$encoding);
				Header('X-Content-Encoded-By: phpCMS '.$DEFAULTS->VERSION);

				$fp = fopen($file, 'rb');
				// filter the cache-tag out of the code
				$out = fread($fp,strlen($CHECK_PAGE->CACHE_TAG));
				if ( $out != $CHECK_PAGE->CACHE_TAG) {
					rewind($fp);
				}
				else {
					fseek($fp,strlen($CHECK_PAGE->CACHE_TAG),SEEK_SET);
				}
				fpassthru($fp);
				// cache is off but old cachefile exists -> delete old cachefile
				if($DEFAULTS->CACHE_STATE == 'off') {
					unlink ($file);
				}
				return true;
			}

			// the client does not understand gzip but the cache file is in gzip
			// decompress the file and deliver it uncompress to the client
			$zd = gzopen($file, "rb");
			$out = $out.gzread($zd, 100000);
			gzclose ($zd);

			// remove the entry with the real filename
			$out = str_replace($CHECK_PAGE->CACHE_TAG,'',$out);

			$ContentLength = strlen( $out) - strlen($CHECK_PAGE->CACHE_TAG);
			Header('Content-Length: '.$ContentLength);

			echo $out;
			if($DEFAULTS->CACHE_STATE == 'off') {
				unlink($file);
			}
			return true;
		}
		// cachfile is not gzip
		$ContentLength = filesize($file) - strlen($CHECK_PAGE->CACHE_TAG);
		Header('Content-Length: '.$ContentLength);

		$fp = fopen($file, 'rb');
		// filter the cache-tag out of the code
		$out = fread($fp,strlen($CHECK_PAGE->CACHE_TAG));
		if ( $out != $CHECK_PAGE->CACHE_TAG) {
			rewind($fp);
		} else {
			fseek($fp,strlen($CHECK_PAGE->CACHE_TAG),SEEK_SET);
		}
		fpassthru($fp);

		if ($DEFAULTS->CACHE_STATE == 'off') {
			unlink($file);
		}
		return true;
	}

	// write the gzipped content into the cachefile
	function gwrite(&$contents) {
		global $DEFAULTS, $CHECK_PAGE;

		if($DEFAULTS->CACHE_STATE == 'off') {
			return;
		}
		$Ausgabe = join('', $contents);

		if($DEFAULTS->GZIP == 'on') {
			$Ausgabe = $this->gzip_kompr($Ausgabe);
		}
		$fp = fopen($CHECK_PAGE->CACHE_PAGE, 'wb');
		// prewrite the cache-tag
		fwrite($fp,$CHECK_PAGE->CACHE_TAG);
		// write the encoded content
		fwrite($fp, $Ausgabe);
		fclose($fp);
	}

	// submit the cachefile to the browser
	function gzipPassthru(&$Lines) {
		global $DEFAULTS, $CHECK_PAGE, $PHP;

		$Content = join('', $Lines);
		//$ContentLength = strlen ( $Content );

		Header('Content-type: text/html');
		if((!isset($show)) AND ($DEFAULTS->CACHE_CLIENT == 'on')) {
			Header('Cache-Control: public');
			Header('Expires: '.gmdate("D, d M Y H:i:s", time() + $DEFAULTS->PROXY_CACHE_TIME).' GMT');
			Header('Last-Modified: '.gmdate("D, d M Y H:i:s", filemtime($DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path.'/'.$CHECK_PAGE->name)).' GMT');
		} else {
			$PHP->NoCache();
		}
		Header('X-Content-Parsed-By: phpCMS '.$DEFAULTS->VERSION);
		$PHP->p3pHeader();
		//Header('Vary: Accept-Encoding');

		if($DEFAULTS->GZIP == 'on') {
			$Result = $this->gzip_encode($Content);
			if(strlen(trim($Result)) > 0) {
				$Content = $Result;
				Header('Content-Encoding: '.$DEFAULTS->encoding);
			}
		}
		$ContentLength = strlen($Content);
		Header('Content-Length: '.$ContentLength);
		echo $Content;
	}

	function gzip_encode(&$contents) {
		global $HTTP_ACCEPT_ENCODING, $DEFAULTS;

		if(headers_sent()) {
			return;
		}
		if(!stristr($HTTP_ACCEPT_ENCODING, 'gzip')) {
			return;
		}
		if(!stristr($HTTP_ACCEPT_ENCODING, 'x-gzip')) {
			$DEFAULTS->encoding = 'gzip';
		} else {
			$DEFAULTS->encoding = 'x-gzip';
		}
		$contents = $this->gzip_kompr($contents);
		return $contents;
	}

	function gzip_kompr(&$contents) {
		$gzdata = "\x1f\x8b\x08\x00\x00\x00\x00\x00"; // gzip header
		$size = strlen($contents);
		$crc = crc32($contents);
		$gzdata .= gzcompress($contents, 5);
		$gzdata = substr($gzdata, 0, strlen($gzdata) - 4); // fix crc bug
		$gzdata .= pack("V", $crc).pack("V", $size);
		return $gzdata;
	}

}

?>