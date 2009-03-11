<?php

ob_start("ob_gzhandler");

$time_start = getmicrotime();

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

define('DS', DIRECTORY_SEPARATOR);
define('_ADMIN_', 1);
define('DEMO_MODE', 0);

/*
* SKYBLUE, _SBC_ROOT_ and BASE_PAGE must all be defined before including 
* the base.php file.
*
* SKYBLUE - A security check to make sure any core files are being included 
*           locally.
*
* _SBC_ROOT_ - The relative path to the root from the including file.
*
* BASE_PAGE  - the name of the FrontController page (index.php|admin.php)
*/

define('SKYBLUE', 1);
define('_SBC_ROOT_', './');
define('BASE_PAGE', 'admin.php');

require_once(_SBC_ROOT_ . 'base.php');
require_once('includes/functions.index.php');

$Router = new Router;
/*
* Don't call the route method in admin
* $Router->route();
*/

$Core = new Core(array(
    'path'     => '',
    'lifetime' => 1800,
    'events' => array(
        'OnAuthenticate',
        'OnLoginSuccess',
        'OnLoginFailed',
        'BeforeValidate',
        'BeforeLoadConfig',
        'BeforeLoadLanguage',
        'BeforeLoadAdminModules',
        'BeforeLoadManager',
        'AfterLoadManager'
    )
));

$Core->CheckInstall();

# ###################################################################################
# Determine which manager is being loaded
# ###################################################################################

$mgr = strtolower($Core->GetVar($_GET, VAR_MGR, 'login'));

# ###################################################################################
# Validate the request. If the user is not logged in, 
# they will be re-directed to the login page.
# ###################################################################################

$Core->trigger('BeforeValidate', $mgr);

$Core->ValidateRequest($mgr);

# ###################################################################################
# Load the site config file
# ###################################################################################

$Core->trigger('BeforeLoadConfig');

$config = $Core->LoadConfig();

# ###################################################################################
# Load the currently installed language. 
# This feature is not fully implemented.
# ###################################################################################

$Core->trigger('BeforeLoadLanguage');

$Core->loadLanguage();

# ###################################################################################
# Load the current dashboard into a buffer
# ###################################################################################

$Core->trigger('BeforeLoadAdminModules');

$dashboard = LoadAdminModules($mgr);

# ###################################################################################
# Load the main content into a buffer
# ###################################################################################

$Core->trigger('BeforeLoadManager');

$content = LoadManager($mgr);

$content = $Core->trigger('AfterLoadManager', $content);

# ###################################################################################
# Add global JavaScript variables
# ###################################################################################

$scripts = $Core->HTML->MakeElement(
    'script',
    array('type'=>'text/javascript'),
    'var MY_URL = "'.FULL_URL.'";'."\n" . 
	'var ADMIN_URL = "'.FULL_URL.'";'."\n"
);

$scripts .= $Core->HTML->MakeElement(
    'script',
    array('type'=>'text/javascript'),
    'var SITE_PATH = "'.SITE.'/";'
);

# ###################################################################################
# Get the Admin skin
# ###################################################################################

$html = GetAdminTemplate($mgr);

# ###################################################################################
# Load any content buffered by the current manager
# ###################################################################################

LoadBuffers($html);

# ###################################################################################
# Merge all output with the current skin
# ###################################################################################


ReplaceContentToken(TOKEN_PAGE_TITLE, SB_SITE_NAME.' :: '.ucwords($mgr), $html);
ReplaceContentToken(TOKEN_SCRIPTS,    $scripts,             $html);
ReplaceContentToken(TOKEN_ADMIN_NAV,  BreadCrumbs(),        $html);
ReplaceContentToken(TOKEN_DASHBOARD,  $dashboard,           $html);
ReplaceContentToken(TOKEN_CONTENT,    $content,             $html);
ReplaceContentToken(TOKEN_SB_VERSION, SB_VERSION,           $html);
ReplaceContentToken(TOKEN_SB_NAME,    SB_PROD_NAME,         $html);

$html = str_replace('{doc:lang}', ' lang="'.SB_LANGUAGE.'"', $html);
                         
if (file_exists(INFO_IFRAME_SRC))
{
	ReplaceContentToken(TOKEN_INFO_IFRAME, INFO_IFRAME_TAG, $html);
}

if ($Core->GetVar($_GET, VAR_MGR, 'login') != 'login')
{
	ReplaceContentToken(TOKEN_LINK_LOGOUT, LINK_LOGOUT, $html);
	ReplaceContentToken(
	    TOKEN_LINK_INBOX,  
	    str_replace(
	        TOKEN_UNREAD_MESSAGES, 
	        '&nbsp;(' . $Core->GetUnreadMailCount(). ')', 
	        LINK_INBOX
	    ), 
	    $html
	);
	$Core->editor(
	    '.wymeditor, #story_content, .editor', 
	    $html, 
	    isset($config['site_editor']) ? $config['site_editor'] : 'wymeditor'
	);
}

ReplaceContentToken(TOKEN_ANALYTICS, null, $html);

# ###################################################################################
# Print the HTML to the user-agent
# ###################################################################################



$time_taken = round(getmicrotime()-$time_start,4);
$time = "Generated in $time_taken seconds" ;

echo str_replace('<!--#performance-->', $time, $html);

function getmicrotime() {
    list($usec,$sec) = explode(" ", microtime());
    return ((float)$usec+(float)$sec);
}

ob_flush();

?>