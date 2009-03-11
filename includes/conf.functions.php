<?php

/**
* @version		1.1 RC1 2008-11-20 21:18:00 $
* @package		SkyBlueCanvas
* @copyright	Copyright (C) 2005 - 2008 Scott Edwin Lewis. All rights reserved.
* @license		GNU/GPL, see COPYING.txt
* SkyBlueCanvas is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYING.txt for copyright notices and details.
*/

# ###################################################################################
# The SKYBLUE constant must always be defined by the main entry point 
# (i.e., the index.php file). This prevents direct access to any sub-files.
# ###################################################################################

defined('SKYBLUE') or die(basename(__FILE__));

function sb_conf($conf, $val) {
    $conf = strtoupper($conf);
    
    // Handle special case config settings
    
    switch ($conf) {
        case 'SITE':
            conf_site($val);
            break;
        default:
            sb_define($conf, $val);
            break;
    }
}

function sb_define($def, $val) {
    defined($def) or
    define($def, $val);
}

function conf_site($val) {
    if (defined('SITE')) return;
    define('SITE', $val);
}

function sb_isset($conf) {
    $consts = get_defined_constants();
    return isset($consts[strtoupper($conf)]);
}

# ###################################################################################
# If SkyBlueServer is running locally (i.e., not from a web-connected server), the 
# mode is automatically set to DEV. This is necessary because SkyBlueCanvas 
# requires a valid domain to run properly. We can add a couple of minor hacks 
# to run locally for development and debugging.
# ###################################################################################

if (!defined('DEV_MODE')) {
	if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' ||
		strpos($_SERVER['REMOTE_ADDR'],'192.168.1') !== false)
	{
		define('DEV_MODE',1);
	}
	else {
		define('DEV_MODE',0);
	}
}

# ###################################################################################
# HOST is the IP of the current server.
#
# SB_LOCALHOST is used during DEV mode. This may no longer be in use but leave 
# this constant intact until it can be verified that it is no longer in use.
# ###################################################################################

if (defined('DEV_MODE') && DEV_MODE == 1) {
    define('SB_LOCALHOST', 1);
}
else {
    define('SB_LOCALHOST', 0);
}

function print_gzipped_page() {

    global $HTTP_ACCEPT_ENCODING;
    if (headers_sent()) {
        $encoding = false;
    }
    elseif (strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false) {
        $encoding = 'x-gzip';
    }
    elseif (strpos($HTTP_ACCEPT_ENCODING,'gzip') !== false) {
        $encoding = 'gzip';
    }
    else {
        $encoding = false;
    }

    if ($encoding) {
        $contents = ob_get_contents();
        ob_end_clean();
        header('Content-Encoding: '.$encoding);
        print("\x1f\x8b\x08\x00\x00\x00\x00\x00");
        $size = strlen($contents);
        $contents = gzcompress($contents, 9);
        $contents = substr($contents, 0, $size);
        print($contents);
        exit(0);
    }
    else {
        ob_end_flush();
        exit();
    }
}

?>
