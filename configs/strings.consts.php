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

# ###################################################################################
# SB_SITEMAPPER_CLASS is the string name of the Sitemapper plugin class
# ###################################################################################

define('SB_SITEMAPPER_CLASS', 'sitemapper');
define('SB_SAFE_URL_CHARS',   'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-');

# ###################################################################################
# SB_NOT_ENOUGH_PRIVILEGES is the HTML for the message telling the user that 
# they do not have sufficient privileges to access a particular manager or 
# feature.
# ###################################################################################

defined('SB_NOT_ENOUGH_PRIVILEGES') or
define('SB_NOT_ENOUGH_PRIVILEGES', 
        '<h2 class="caution">'.
        'You do not have enough access privileges to access this manager.'.
        '</h2>'."\r\n");

# ###################################################################################
# The following constants define various messages displayed during the 
# SetUp Assistant process.
# ###################################################################################

define('INST_ERR_STR', '<h2 class="install_error">{str}</h2>');

define('INST_USERNAME_NULL', 
    str_replace('{str}', 'Please Enter A Username', INST_ERR_STR));
define('INST_PASSWORD_NULL', 
    str_replace('{str}', 'Please Enter A Password', INST_ERR_STR));
define('INST_PASSWORD_MISMATCH', 
    str_replace('{str}', 'Your Password and Confirmation did not match.', INST_ERR_STR));
define('INST_PASSWORD_NOT_SAVED', 
    str_replace('{str}', 'Your Username and Password Could Not Be Saved', INST_ERR_STR));
define('INST_EMAIL_NULL', 
    str_replace('{str}', 'Please Enter An Email Address', INST_ERR_STR));
define('INST_SITENAME_NULL', 
    str_replace('{str}', 'Please enter a name for your site', INST_ERR_STR));
define('INST_URL_NULL', 
    str_replace('{str}', 'Your URL cannot be left blank', INST_ERR_STR));
define('INST_ADMINURL_NULL', 
    str_replace('{str}', 'Your Admin URL cannot be left blank', INST_ERR_STR));
define('INST_CONFIG_NOT_SAVED', 
    str_replace('{str}', 'Your Contact Info Could Not Be Saved.', INST_ERR_STR));
define('INST_INST_DEFAULT_STORY_FILE', 'home.page.txt');
define('INST_DEFAULT_STORY',
        '<h2>Welcome!</h2>'."\n".
        '<p>We have not yet added any content to our site. '.
        'Please check back soon.</p>'
       );
define('INST_NO_SKIN_SELECTED', 
    str_replace('{str}', 'Please select a skin to install.', INST_ERR_STR));       
define('INST_FATAL_INSTALL_ERROR', 
    str_replace('{str}', 'SkyBlue could not complete the base installation.', INST_ERR_STR));

# ###################################################################################    
# SB_MSG_STRING defines the default HTML block for displaying SkyBlue Admin UI
# messages.
# ###################################################################################

define('SB_MSG_STRING', '<h2 class="message">{msg}</h2>');
define('SB_MSG_EDIT',   '<h2 class="edit">{msg}</h2>');

# ###################################################################################
# SB_FATAL_INSTALL_ERROR defines the message seen when the SetUp Assistant 
# cannot complete the setup process.
# ###################################################################################

define('SB_FATAL_INSTALL_ERROR', 
    'SkyBlue could not complete the base installation.');

# ###################################################################################
# SB_NO_ITEMS_TO_DISPLAY defines the message seen when there are no data 
# items to display in the Managers list view.
# ###################################################################################

define('SB_NO_ITEMS_TO_DISPLAY', 'No items to display');

# ###################################################################################
# NO_ITEMS_TO_ORDER_STRING defines the message seen when there are no items to order, 
# i.e., there are 0-1 items in a list.
# ###################################################################################

define('NO_ITEMS_TO_ORDER_STRING', 
    'You can order items after more than one item has been created');

# ###################################################################################
# WILL_SHOW_AFTER_SAVE defines the message seen when there are items that will be 
# displayed after the first save.
# ###################################################################################

define('WILL_SHOW_AFTER_SAVE', 
    'Items will be displayed after saving.');

# ###################################################################################
# NO_GROUPS_STRING defines the message seen when there are no groups defined for a 
# manager that has items and groups.
# ###################################################################################

define('NO_GROUPS_STRING',
    '<strong class="required">Required</strong> - You must create at least '.
    'one group in Groups portion of this Manager.');

# ###################################################################################
# WYSIWYM_PLUGIN_LINKS defines the link pointing to the location of the Wymeditor 
# Plugin avaScript files.
# ###################################################################################

define('WYSIWYM_PLUGIN_LINKS', 
'<script>var BB_PATH = "plugins/";</script>'."\n".
'<script src="plugins/wymeditor/javascript/browser.js"></script>'."\n".
'<script src="plugins/wymeditor/javascript/util.js"></script>'."\n".
'<script src="plugins/wymeditor/javascript/config.js"></script>'."\n".
'<script src="plugins/wymeditor/javascript/wym.js"></script>'."\n".
'<link rel="stylesheet" type="text/css" href="plugins/wymeditor/skins/editor-skin.css" />');

define( 'MSG_FEATURE_DISABLED_IN_DEMO',
    'This feature has been disabled in the SkyBlueCanvas demo. No changes were saved.');
    
define('MSG_NO_DELETE_MENUS',
    'Top Menu and Main Menu cannot be deleted because they are owned by the system.');


define('IMG_TRAIL_HTML', 
'<img src="{camera}" '.
'width="20" height="14" '.
'onmouseover="showtrail(this, \'{src}\');" '.
'onmouseout="hidetrail();" />'.
'&nbsp;&nbsp;|&nbsp;&nbsp;');

# ###################################################################################
# Site Creator Manager strings
# ###################################################################################

define('CANNOT_CHANGE_OWN_GROUPID',
	'You cannot change your own Group ID for security reasons');
define('SET_SKIN_IN_SETUP', 
	'You can set the default skin when you run the Setup Assistant');
define('SET_EMAIL_IN_SETUP', 
	'You can set your email when you run the Setup Assistant');
define('FEATURE_NOT_YET_ENABLED',
	'The *{name}* feature is not yet enabled');
    
define('sWARNING_CLASS', 'warning');
define('sCONFIRM_CLASS', 'confirm');
define('sERROR_CLASS', 'error');

define('sMY_GID_TOKEN', '\'{my:groupid}\'');
define('sMY_URL_TOKEN', '{my:url}');
define('sMY_DEV_URL_TOKEN', '{my:devurl}');
define('sMY_EMAIL_TOKEN', '{my:email}');
define('sMY_ADMINURL_TOKEN', '{my:adminurl}');
define('sMY_DEFAULTSKIN_TOKEN', '{my:defaultskin}');
define('sMY_SITE_STATUS_TOKEN', '{my:sitestatus}');

define('sSTATUS_STAGING', 'staging');
define('sSTATUS_LIVE', 'live');
define('sSTATUS_SUSPENDED', 'suspended');

define('sMY_CONFIG_FILE_NAME', 'myconfig.php');

define('sOBJ_PROP_NOT_EXISTS', 'Object property {prop} does not exist');

# ###################################################################################
# The in-line style for lists that are longer than the max list length.
# ###################################################################################

define('sLIST_OVERFLOW_STYLE', 'style="height: auto; overflow: auto;"');
define('sLIST_OVERFLOW_HEIGHT_STYLE', 'style="height: 400px; overflow: auto;"');
define('sLIST_OVERFLOW_WIDTH_STYLE', 'style="width: 100%;"');

define('sCONFIRM_DELETE_JS', 'return confirmDelete(\'{name}\', 0);');

# ###################################################################################
# Constant for identifying a 404 Not Found error
# ###################################################################################

define('NOT_FOUND', 'notfound');

# ###################################################################################
# The following constants support SkyBlueCanvas's {site:var} feature.
# It may seem like a lack of planning that the replacement values for all of 
# the vars are not defined here, but this was unavoidable as some of the values 
# are dependent on other constants that are set dynamically. As much as possible 
# I tried to define all values here but in cases where it was necessary to 
# define values elsewhere, I did so where the constants most logically fit
# (i.e., dirs.consts.php, server.consts.php, etc.)
# ###################################################################################

define('SB_VALIDATE_XHTML', 'http://validator.w3.org/check?uri=referer');
define('SB_VALIDATE_CSS', 'http://jigsaw.w3.org/css-validator/check/referer');
define('SB_NOSCRIPT', 
"<noscript>\n".
"    <h2>This site requires that you have JavaScript enabled in your browser.</h2>\n".
"</noscript>\n");

define('VAR_SITE_NAME',     '{site:name}');
define('VAR_SITE_CONTACT',  '{site:contact}');
define('VAR_SITE_URL',      '{site:address}');
define('VAR_SITE_SLOGAN',   '{site:slogan}');
define('VAR_SITE_RSS',      '{site:rss}');
define('VAR_SITE_XHTML',    '{site:xhtml}');
define('VAR_SITE_CSS',      '{site:css}');
define('VAR_SITE_NOSCRIPT', '{site:noscript}');
define('VAR_SB_PROD_NAME',  '{skyblue:name}');
define('VAR_SB_VERSION',    '{skyblue:version}');
define('VAR_SB_TAGLINE',    '{skyblue:tagline}');
define('VAR_SB_INFO_LINK',  '{skyblue:link}');

define('VAR_MGR', 'mgr');

# SkyBlue Pre-defined Content Regions
# {region:header}
# {region:top}
# {region:pathway}
# {region:left}
# {region:main}
# {region:right}
# {region:footer}

define('LINK_LOGOUT', '<a href="admin.php?mgr=login&amp;action=logout">Sign Out</a>');
define('LINK_INBOX',  '<a href="admin.php?mgr=email">Inbox{unread}</a>');
define('TASK_COL_WIDTH', '150');
define('TASK_SEPARATOR', '|');

# ###################################################################################
# The 'Info' iframe on the main dashboard
# ###################################################################################

define('INFO_IFRAME_SRC',   'info.php');
define('INFO_IFRAME_TAG',   '<iframe src="info.php" frameborder="no" scrolling="no"></iframe>');

# ###################################################################################
# DEFAULT_HTML is a default HTML body to use in case the skin HTML file cannot be 
# found for whatever reason. This avoids an error causing the page build to fail.
# ###################################################################################

define('DEFAULT_HTML',
'<div id="wrapper" style="width: 720px; margin: 0px auto;">'."\n".
'    <div id="header">'."\n".
'       <h1>'."\n".
'           <a href="{site:address}">{site:name}</a>'."\n".
'       </h1>'."\n".
'   </div>'."\n".
'   <h2>The specified skin could not be found</h2>'."\n".
'   <div id="top">'."\n".
'       {region:top}'."\n".
'   </div>'."\n".
'   <div id="left">'."\n".
'       {region:left}'."\n".
'   </div>'."\n".
'   <div id="main">'."\n".
'       {region:main}'."\n".
'   </div>'."\n".
'   <div id="right">'."\n".
'       {region:right}'."\n".
'   </div>'."\n".
'   <div id="footer">'."\n".
'       {region:footer}'."\n".
'   </div>'."\n".
'</div>'."\n");

# ###################################################################################
# In rare instances where no default page is set and no 404 page exists, the 
# NO_404_PAGE text is output instead.
# ###################################################################################

define('NO_404_PAGE', 
"<h2>Page Not Found</h2>\n" . 
"<p>The page you requested could not be found (404 Error). Additionally, no appropriate error page could " .
"be found to handle this error.</p>\n" . 
"<p>Please contact the site adimistrator to report this error.</p>\n"
);

?>
