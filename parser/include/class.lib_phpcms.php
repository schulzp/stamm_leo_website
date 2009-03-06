<?php
/* $Id: class.lib_phpcms.php,v 1.3.2.24 2006/06/18 18:07:31 ignatius0815 Exp $ */
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
   |	Martin Jahn (mjahn)
   |	Henning Poerschke (hpoe)
   |	Thilo Wagner (ignatius0815)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

//############################################################
// Diese Library liefert folgende Funktionen:
//
// *) Version ( $number )
// *) API ( void )
// *) OS ( void )
// *) GetDocRoot ( void )
//
// Zum Zeitpunkt der Erstellung werden folgende Umgebungen
// unterstützt: WinNT + IIS5 (ISAPI), Win98 + Apache
// (CGI u. API) AIX + Apache (Apache-API)
// Linux + Apache (CGI u. API).
//
// Im folgenden werden die Funktionen im einzelnen erklärt:
//
// Version ( $number )
// ===================
//
// Die Funktion erwartet als Argument eine Zahl. Diese Zahl
// entspricht der zurückgelieferten Stelle der PHP-Versions-
// nummer. Im Normalfall sind PHP-Versionsnummern aufgebaut
// wie folgt: Die erste Stelle liefert die aktuelle
// Versionsnummer, gefolgt von einem Punkt gefolgt (meistens)
// von einer Null gefolgt von einem Punkt gefolgt von der
// Unterversionsnummer gefolgt von einem Zusatz.
// Beispiel: 4.0.3pl1
// In diesem Fall liefert Die Funktion mit dem Argument "1"
// den Wert "4", mit dem Argument "2" den Wert "0" mit
// dem Argument "3" den Wert "3" und mit dem Argument "4"
// den Wert "pl1". Das ist nützlich, wenn Funktionen
// eingesetzt werden sollen, die nur mit bestimmten
// Versionen von PHP lauffähig sind.
//
// API ( void )
// ============
//
// Diese Funktion liefert den Wert "mod" zurück, wenn
// PHP als Server-API (Modul) läuft und den Wert "cgi"
// wenn PHP als CGI-Programm ausgeführt wird.
//
// OS ( void )
// ===========
//
// Unter dem Betriebssystem Windows, wird von dieser
// Funktion der String "win" zurückgeben. Läuft auf dem
// Computer ein Unix oder Linux, gibt die Funktion
// den String "nix" zurück.
//
// GetDocRoot ( void )
// ===================
//
// Da unter unterschiedlichen Betriebssystemen der Wert
// DOCUMENT_ROOT unterschiedlich gesetzt wird und vor
// allem beim Einsatz von Virtuellen Hosts dieser
// Wert oft falsch belegt wird, liefert diese Funktion
// den ABSOLUTEN Pfad zum Document-Root des Webservers
// zurück.
//
// GetScriptName ( void )
// ======================
//
// Liefert den Namen des Scripts, das gerade ausgeführt
// wird.
//
// GetScriptPath ( void )
// ======================
//
// Liefert den Pfad relativ zum Document-Root jenes Scripts,
// das gerade ausgeführt wird.
//############################################################

if(!defined("_LIBPHPCMS_")) {
	define("_LIBPHPCMS_", TRUE);
}

class LibphpCMS {

	var $_debug = false;
	var $myVersion;
	var $myAPI;
	var $myOS;
	var $_path_translated;
	var $_path_info;
	var $_document_root;
	var $_domain_name;

	function LibphpCMS() {
		$this->myVersion = $this->getVersion();
		$this->myAPI = $this->getAPI();
		$this->myOS = $this->getOS();
		$this->GetPaths();
	}

	// get the detailed version-number
	function getVersion() {
		$temp = phpversion();
		$_myVersion[1] = substr($temp, 0, strpos($temp, '.'));
		$temp = substr($temp, strpos($temp, '.' ) + 1);
		$_myVersion[2] = substr($temp, 0, strpos($temp, '.'));
		$temp = substr($temp, strpos($temp, '.') + 1);
		$_myVersion[3] = substr($temp, 0, 1);
		$_myVersion[4] = substr($temp, 1);
		return $_myVersion;
	}

	function Version($number) {
		return $this->myVersion[$number];
	}

	// get module or cgi
	function getAPI() {
		global $SERVER_SOFTWARE;

		if ($this->_debug) echo "<b>getAPI:</b><br>\n";
		$_version = $this->Version(1).$this->Version(2).$this->Version(3);
		if($_version >= 401) {
			$sapi_type = php_sapi_name();
			if ($this->_debug) echo "SAPI Type:$sapi_type<br>\n";
			if(strpos($sapi_type, "apache") !== false) {
				$_myAPI = 'mod';
			}
			elseif(strpos($sapi_type, "isapi") !== false) {
				$_myAPI = 'mod';
			}
			elseif(strpos($sapi_type, "cgi") !== false) {
				$_myAPI = 'cgi';
			}
		} else {
			if ($this->_debug) echo "SERVER SOFTWARE: $SERVER_SOFWARE<br>\n";
			if(strpos(strtoupper($SERVER_SOFTWARE), "PHP") !== false AND strpos(strtoupper($SERVER_SOFTWARE), "SCRIPT") === false) {
				$_myAPI = 'mod';
			} else {
				$_myAPI = 'cgi';
			}
		}
		if ($this->_debug) echo "Found API: $_myAPI<br>\n";
		return $_myAPI;
	}

	function API() {
		return $this->myAPI;
	}
	// get OS
	function getOS() {
		global $SERVER_SOFTWARE, $PATH;

		if ($this->_debug) echo "<b>getOS:</b><br>\n";

		$_myOS = 'unknown';

		// first try if the php constant PHP_OS ist set:
		if (defined('PHP_OS')) {
			if ($this->_debug) echo "PHP_OS:".PHP_OS."<br>\n";
			if (strtoupper(substr(PHP_OS,0,3)) == 'WIN' ) {
				$_myOS = 'win';
			}
			else if (strtoupper(PHP_OS) == 'LINUX'  ||
			         strtoupper(PHP_OS) == 'DARWIN' ||
			         strtoupper(PHP_OS) == 'AIX'    ||
			         strtoupper(PHP_OS) == 'SUNOS') {
				$_myOS = 'nix';
			}
		}

		if ($_myOS == 'unknown' && isset($SERVER_SOFTWARE)) {
		 	// PHP_OS was not set to a know value, so try to recognize
		 	// the OS with other methods (fallback)
			if ($this->_debug) echo "SERVER_SOFTWARE: $SERVER_SOFTWARE<br>\n";
			if(strpos(strtoupper($SERVER_SOFTWARE), 'LINUX') !== false) {
				$_myOS = 'nix';
			}
			elseif(strpos(strtoupper($SERVER_SOFTWARE), 'UNIX') !== false) {
				$_myOS = 'nix';
			}
			elseif(strpos(strtoupper($SERVER_SOFTWARE), 'WIN') !== false) {
				$_myOS = 'win';
			}
			elseif(strpos(strtoupper($SERVER_SOFTWARE), 'MICROSOFT') !== false) {
				$_myOS = 'win';
			}
		}

		if ($_myOS == 'unknown' && isset($PATH)) {
			if ($this->_debug) echo "PATH: $PATH<br>\n";
			if(stristr($PATH, 'C:')) {
				$_myOS = 'win';
			}
			elseif(stristr($PATH, 'D:')) {
				$_myOS = 'win';
			}
			elseif(stristr($PATH, 'E:')) {
				$_myOS = 'win';
			}
			elseif(stristr($PATH, '/X11')) {
				$_myOS = 'nix';
			}
		}

		if ($_myOS == 'unknown') {
			// phpCMS wasn't able to determine the Server OS, so let's
			// assume it is some kind of unix which should work in most cases
			if ($this->_debug) echo "Unknown OS.. setting to 'nix'<br>\n";
			$_myOS = 'nix';
		}
		if ($this->_debug) echo "Found OS: $_myOS<br>\n";
		return $_myOS;
	}

	function OS() {
		return $this->myOS;
	}

	function GetPaths() {
		global
			$PATH_TRANSLATED,
			$SCRIPT_FILENAME,
			$PATH_INFO,
			$SCRIPT_NAME,
			$SERVER_NAME,
			$HTTP_HOST,
			$_SERVER;

		if ($this->_debug) {
			echo "<b>getPaths:</b><br>\n";
			echo "Setting Script Name:<br>\n";
		}

		// Get SCRIPT_FILENAME
		if(!isset($this->_path_translated)) {
			if(isset($GLOBALS['PATH_TRANSLATED'])) {
				if ($this->_debug) echo "PATH_TRANSLATED (Global): ".$GLOBALS['PATH_TRANSLATED']."<br>\n";
				$this->_path_translated = $GLOBALS['PATH_TRANSLATED'];
				$this->_script_name = basename($this->_path_translated);
			}
			elseif(isset($GLOBALS['SCRIPT_FILENAME'])) {
				if ($this->_debug) echo "SCRIPT_FILENAME (Global): ".$GLOBALS['SCRIPT_FILENAME']."<br>\n";
				$this->_path_translated = $GLOBALS['SCRIPT_FILENAME'];
				$this->_script_name = basename($this->_path_translated);
			}
			elseif(isset($PATH_TRANSLATED)) {
				if ($this->_debug) echo "PATH_TRANSLATED: ".$PATH_TRANSLATED."<br>\n";
				$this->_path_translated = $PATH_TRANSLATED;
				$this->_script_name = basename($this->_path_translated);
			}
			elseif(isset($_ENV["PATH_TRANSLATED"])) {
				if ($this->_debug) echo "PATH_TRANSLATED (Env): ".$_ENV["PATH_TRANSLATED"]."<br>\n";
				$this->_path_translated = $_ENV["PATH_TRANSLATED"];
				$this->_script_name = basename($this->_path_translated);
			}
			elseif(isset($_SERVER["PATH_TRANSLATED"])) {
				if ($this->_debug) echo "PATH_TRANSLATED (Server): ".$_SERVER["PATH_TRANSLATED"]."<br>\n";
				$this->_path_translated = $_SERVER["PATH_TRANSLATED"];
				$this->_script_name = basename($this->_path_translated);
			}
			elseif(isset($SCRIPT_FILENAME)) {
				if ($this->_debug) echo "SCRIPT_FILENAME: ".$SCRIPT_FILENAME."<br>\n";
				$this->_path_translated = $SCRIPT_FILENAME;
				$this->_script_name = basename($this->_path_translated);
			}
			elseif(isset($_ENV["SCRIPT_FILENAME"])) {
				if ($this->_debug) echo "SCRIPT_FILENAME (Env): ".$_ENV['SCRIPT_FILENAME']."<br>\n";
				$this->_path_translated = $_ENV["SCRIPT_FILENAME"];
				$this->_script_name = basename($this->_path_translated);
			}
			elseif(isset($_SERVER["SCRIPT_FILENAME"])) {
				if ($this->_debug) echo "SCRIPT_FILENAME (Server): ".$_SERVER['SCRIPT_FILENAME']."<br>\n";
				$this->_path_translated = $_SERVER["SCRIPT_FILENAME"];
				$this->_script_name = basename($this->_path_translated);
			}
		} // _path_translated

		if ($this->_debug && isset($this->_script_name)) echo "Found Script Name: ".$this->_script_name."<br>";

		if ($this->_debug) echo "Setting Path Info:<br>\n";

		// Get PATH_INFO
		if(!isset($this->_path_info)) {
			if(isset($GLOBALS['PATH_INFO'])) {
				if ($this->_debug) echo "PATH_INFO (Global): ".$GLOBALS['PATH_INFO']."<br>\n";
				$this->_path_info = $GLOBALS['PATH_INFO'];
			}
			elseif(isset($GLOBALS['SCRIPT_NAME'])) {
				if ($this->_debug) echo "SCRIPT_NAME (Global): ".$GLOBALS['SCRIPT_NAME']."<br>\n";
				$this->_path_info = $GLOBALS['SCRIPT_NAME'];
			}
			elseif(isset($GLOBALS['SCRIPT_URI'])) {
				if ($this->_debug) echo "SCRIPT_URI (Global): ".$GLOBALS['SCRIPT_URI']."<br>\n";
				$this->_path_info = $GLOBALS['SCRIPT_URI'];
			}
			elseif(isset($PATH_INFO)) {
				if ($this->_debug) echo "PATH_INFO: ".$PATH_INFO."<br>\n";
				$this->_path_info = $PATH_INFO;
			}
			elseif(isset($_ENV["PATH_INFO"])) {
				if ($this->_debug) echo "PATH_INFO (Env): ".$_ENV['PATH_INFO']."<br>\n";
				$this->_path_info = $_ENV["PATH_INFO"];
			}
			elseif(isset($_SERVER["PATH_INFO"])) {
				if ($this->_debug) echo "PATH_INFO (Server): ".$_SERVER['PATH_INFO']."<br>\n";
				$this->_path_info = $_SERVER["PATH_INFO"];
			}
			elseif(isset($SCRIPT_NAME)) {
				if ($this->_debug) echo "SCRIPT_NAME: ".$SCRIPT_NAME."<br>\n";
				$this->_path_info = $SCRIPT_NAME;
			}
			elseif(isset($_ENV["SCRIPT_NAME"])) {
				if ($this->_debug) echo "SCRIPT_NAME (Env): ".$_ENV['SCRIPT_NAME']."<br>\n";
				$this->_path_info = $_ENV["SCRIPT_NAME"];
			}
			elseif(isset($_SERVER["SCRIPT_NAME"])) {
				if ($this->_debug) echo "SCRIPT_NAME (Server): ".$_SERVER['SCRIPT_NAME']."<br>\n";
				$this->_path_info = $_SERVER["SCRIPT_NAME"];
			}
			elseif(isset($SCRIPT_URI)) {
				if ($this->_debug) echo "SCRIPT_URI: ".$SCRIPT_URI."<br>\n";
				$this->_path_info = $SCRIPT_URI;
			}
			elseif(isset($_ENV["SCRIPT_URI"])) {
				if ($this->_debug) echo "SCRIPT_URI (Env): ".$_ENV['SCRIPT_URI']."<br>\n";
				$this->_path_info = $_ENV["SCRIPT_URI"];
			}
			elseif(isset($_SERVER["SCRIPT_URI"])) {
				if ($this->_debug) echo "SCRIPT_URI (Server): ".$_SERVER['SCRIPT_URI']."<br>\n";
				$this->_path_info = $_SERVER["SCRIPT_URI"];
			}
		} // $_path_info

		if ($this->_debug && isset($this->_path_info)) echo "Found Path Info: ".$this->_path_info."<br>";

		// Get DOCUMENT_ROOT

		if ($this->_debug) echo "Setting Document Root:<br>\n";

		if(!isset($this->_document_root)) {
			if(isset($GLOBALS['DOCUMENT_ROOT'])) {
				if ($this->_debug) echo "DOCUMENT_ROOT (Global): ".$GLOBALS['DOCUMENT_ROOT']."<br>\n";
				$this->_document_root = $GLOBALS['DOCUMENT_ROOT'];
			}
			elseif(isset($DOCUMENT_ROOT)) {
				if ($this->_debug) echo "DOCUMENT_ROOT: ".$DOCUMENT_ROOT."<br>\n";
				$this->_document_root = $DOCUMENT_ROOT;
			}
			elseif(isset($_ENV["DOCUMENT_ROOT"])) {
				if ($this->_debug) echo "DOCUMENT_ROOT (Env): ".$_ENV['DOCUMENT_ROOT']."<br>\n";
				$this->_document_root = $_ENV["DOCUMENT_ROOT"];
			}
			elseif(isset($_SERVER["DOCUMENT_ROOT"])) {
				if ($this->_debug) echo "DOCUMENT_ROOT (Server): ".$_SERVER['DOCUMENT_ROOT']."<br>\n";
				$this->_document_root = $_SERVER["DOCUMENT_ROOT"];
			}
		}
		// fallback:
		if(!isset($this->_document_root)) {
			if(isset($this->_path_translated) AND isset($this->_path_info)) {
				$this->_path_translated = str_replace('\\\\', '/', $this->_path_translated); // correct path is php is used as CGI in MS IIS5
				$this->_document_root = str_replace($this->_path_info, "", $this->_path_translated);
				if ($this->_debug) echo "DOCUMENT_ROOT Fallback: ".$this->_document_root."<br>\n";
			}
		} // _document_root

		// fallback:
		if(!isset($this->_path_info)) {
			if(isset($this->_path_translated) AND isset($this->_document_root)) {
				$this->_path_info = str_replace($this->_document_root, "", $this->_path_translated);
				if ($this->_debug) echo "PATH_INFO Fallback: ".$this->_path_info."<br>\n";
			}
		}

		if ($this->_debug && isset($this->_document_root)) echo "Found Document Root: ".$this->_document_root."<br>";
		if ($this->_debug) echo "Setting Server Name:<br>\n";

		// Get SERVER_NAME
		if(!isset($this->_server_name)) {
			if(isset($GLOBALS["HTTP_HOST"])) {
				if ($this->_debug) echo "HTTP_HOST (Global): ".$GLOBALS["HTTP_HOST"]."<br>\n";
				$this->_server_name = $GLOBALS["HTTP_HOST"];
				//echo('<br />2 $_server_name: '.$this->_server_name);
			}
			elseif(isset($GLOBALS["SERVER_NAME"])) {
				if ($this->_debug) echo "SERVER_NAME (Global): ".$GLOBALS["SERVER_NAME"]."<br>\n";
				$this->_server_name = $GLOBALS["SERVER_NAME"];
				//echo('<br />2 $_server_name: '.$this->_server_name);
			}
			elseif(isset($HTTP_HOST)) {
				if ($this->_debug) echo "HTTP_HOST: ".$HTTP_HOST."<br>\n";
				$this->_server_name = $HTTP_HOST;
				//echo('<br />1 $_server_name: '.$this->_server_name);
			}
			elseif(isset($_ENV["HTTP_HOST"])) {
				if ($this->_debug) echo "HTTP_HOST (Env): ".$_ENV["HTTP_HOST"]."<br>\n";
				$this->_server_name = $_ENV["HTTP_HOST"];
				//echo('<br />2 $_server_name: '.$this->_server_name);
			}
			elseif(isset($_SERVER["HTTP_HOST"])) {
				if ($this->_debug) echo "HTTP_HOST (Server): ".$_SERVER["HTTP_HOST"]."<br>\n";
				$this->_server_name = $_SERVER["HTTP_HOST"];
				//echo('<br />3 $_server_name: '.$this->_server_name);
			}
			elseif(isset($SERVER_NAME)) {
				if ($this->_debug) echo "SERVER_NAME: ".$SERVER_NAME."<br>\n";
				$this->_server_name = $SERVER_NAME;
				//echo('<br />4 $_server_name: '.$this->_server_name);
			}
			elseif(isset($_ENV["SERVER_NAME"])) {
				if ($this->_debug) echo "SERVER_NAME (Env): ".$_ENV["SERVER_NAME"]."<br>\n";
				$this->_server_name = $_ENV["SERVER_NAME"];
				//echo('<br />5 $_server_name: '.$this->_server_name);
			}
			elseif(isset($_SERVER["SERVER_NAME"])) {
				if ($this->_debug) echo "SERVER_NAME (Server): ".$_SERVER["SERVER_NAME"]."<br>\n";
				$this->_server_name = $_SERVER["SERVER_NAME"];
				//echo('<br />6 $_server_name: '.$this->_server_name);
			}
		} // _server_name

		if ($this->_debug && isset($this->_server_name)) echo "Found Server Name: ".$this->_server_name."<br>";

		// Get domain name
		if(!isset($this->_domain_name)) {

			// get port
			$port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : $_ENV['SERVER_PORT'];
			if(!isset($port)) {
				$port = '80';
			} // end if

			// handle https
			// ignore port if default (80 for http, 443 for https)
			// Note: https on port other than 443 not handled!
			if ($port == '443') {
				$this->_domain_name = 'https';
				$port = '';
			} else {
				$this->_domain_name = 'http';
				if($port == '80') {
					$port = '';
				}
			} // end if

			// set domain name
			$this->_domain_name .= '://'.$this->GetServerName();

			// add port, if no ':' in $this->_server_name
			if(!strstr($this->GetServerName(),':') && $port) {
				$this->_domain_name .= ':'.$port;
			}
			if ($this->_debug) echo "Domain Name: ".$this->_domain_name."<br>";

		} // _domain_name

	} // function GetPaths()

	function GetServerName() {
		if(!isset($this->_server_name)) {
			die('phpCMS: GetServerName() failed');
		}
		return $this->_server_name;
	}

	function GetDomainName() {
		if(!isset($this->_domain_name)) {
			die('phpCMS: GetDomainName() failed');
		}
		return $this->_domain_name;
	}

	function GetScriptName() {
		if(!isset($this->_script_name)) {
			die('phpCMS: GetScriptName() failed');
		}
		return $this->_script_name;
	}

	function GetScriptPath() {
		if(!isset($this->_path_info)) {
			die('phpCMS: empty $_path_info in GetScriptPath()');
		}
		return dirname($this->_path_info);
	}

	function GetDocRoot() {
		if(!isset($this->_document_root)) {
			die('phpCMS: GetDocRoot() failed');
		}
		return str_replace('\\', '/', $this->_document_root);
	}

	function LockFile($datei, $status) {
		if($status == 'set') {
			if(file_exists($datei.'.lck')) {
				return false;
			}
			$fp = fopen($datei.'.lck', 'a+');
			fwrite($fp, ' ', 1);
			fclose($fp);
			return true;
		}
		if($status == 'release') {
			unlink($datei.'.lck');
		}
	}

	function NoCache() {
		global $SERVER_SOFTWARE;

		// Header ('Server: '.$SERVER_SOFTWARE.' phpCMS '.$DEFAULTS->VERSION."\n");
		Header("Expires: Mon, 26 Jul 1997 05:00:00 GMT\n");		  // Date in the past
		Header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT\n"); // always modified
		Header("Cache-Control: no-cache, must-revalidate\n");		// HTTP/1.1
		Header("Pragma: no-cache\n");								// HTTP/1.0
		$this->p3pHeader();
	}

	function p3pHeader() {
		global $DEFAULTS;

		if(isset($DEFAULTS->P3P_HEADER) AND $DEFAULTS->P3P_HEADER == 'on') {
		Header("P3P: CP=\"$DEFAULTS->P3P_POLICY\" policyref=\"$DEFAULTS->P3P_HREF\"");
		}
	}

	function ExtractValue($FIELD, $ValueName) {
		$ValueName = $ValueName.'="';
		$ValueLength = strlen($ValueName);
		$Temp = trim(substr($FIELD, strpos($FIELD, $ValueName) + $ValueLength));
		$Temp = trim(substr($Temp, 0, strpos($Temp, '"')));
		return $Temp;
	}

	function MakePlugin($PluginPath, $PluginType, &$PageContent, &$CacheState, &$ClientCache, &$ProxyCacheTime, &$Tags, &$Menu, &$plugindir) {
		// later: check, if the plugin is active on this page
		// check if the plugin is dynamic. If so set the page to non-cache
		if(strtoupper($PluginType) != 'STATIC') {
			$CacheState = 'off';
			$ClientCache = 'off';
			$ProxyCacheTime = -4;
		}

		// making arguments global to plugin
		reset($GLOBALS);
		$phpCMS = 0;
		$PHPCMS_INCLUDEPATH = PHPCMS_INCLUDEPATH; // global variable from constant
		$toeval = 'global $phpCMS, $PHPCMS_INCLUDEPATH';
		while(list($k, $value) = each($GLOBALS)) {
			if(strlen($k) > 0 AND preg_match('/^[a-zA-Z_][a-zA-Z0-9_]+$/', $k) > 0) {
				$toeval .= ', $'.$k;
			}
		}
		$toeval .= ';';
		eval($toeval);

		reset($GLOBALS);
		chdir($DEFAULTS->DOCUMENT_ROOT.$CHECK_PAGE->path);

		// running the plugin

		if (isset($DEFAULTS->FIX_PHP_OB_BUG) AND $DEFAULTS->FIX_PHP_OB_BUG == 'on') {
			// starting aditional output buffer to fix a bug in php 4.2.3
			// which causes the output buffer to fail when sessions are
			// set within a script or plugin
			ob_start();
		}

		ob_start();

		include($PluginPath);

		$TempBuffer = ob_get_contents();
		ob_end_clean();

		if (isset($DEFAULTS->FIX_PHP_OB_BUG) AND $DEFAULTS->FIX_PHP_OB_BUG == 'on') {
			$TempBuffer = ob_get_contents() . $TempBuffer;
			ob_end_clean();
		}

		// the plugin might have changed some tags so we have to do a syntax
		// check of them again, when we invoke the ChangeTags function the
		// next time
		$DEFAULTS->REREAD_TAGS = true;

		if(isset($PluginBuffer)) {
			if(!isset($TempBuffer)) {
				return $PluginBuffer;
			} else {
				$PluginBuffer[count($PluginBuffer)] = $TempBuffer;
				return $PluginBuffer;
			}
		} else {
			if(!isset($TempBuffer)) {
				return;
			} else {
				$PluginBuffer[0] = $TempBuffer;
				return $PluginBuffer;
			}
		}
	}
}

?>
