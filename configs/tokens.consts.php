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
# REGION_MAIN, REGION_TOP, REGION_LEFT, REGION_RIGHT, REGION_DATE, 
# REGION_FOOTER, REGION_PATHWAY, REGION_LINKS, REGION_META, REGION_STYLES, 
# REGION_SCRIPTS define the default tokens for the corresponding SkyBlue 
# Skin content regions.
# ###################################################################################

define('REGION_MAIN',    '{region:main}');
define('REGION_TOP',     '{region:top}');
define('REGION_LEFT',    '{region:left}');
define('REGION_RIGHT',   '{region:right}');
define('REGION_FOOTER',  '{region:footer}');
define('REGION_DATE',    '{region:date}');
define('REGION_PATHWAY', '{region:pathway}');
define('REGION_LINKS',   '{region:links}');
define('REGION_META',    '{region:meta}');
define('REGION_STYLES',  '{region:styles}');
define('REGION_SCRIPTS', '{region:scripts}');

# ###################################################################################
# Content replacement tokens
# ###################################################################################

define('TOKEN_BUILD',             '{build}');
define('TOKEN_PAGE_TITLE',        '{page:title}');
define('TOKEN_EDITOR',            '<!--#html.editor.links-->');
define('TOKEN_SCRIPTS',           '{inc:scripts}');
define('TOKEN_ADMIN_NAV',         '<!--{admin.nav}-->');
define('TOKEN_DASHBOARD',         '{page:dashboard}');
define('TOKEN_CONTENT',           '{page:content}');
define('TOKEN_SB_VERSION',        '{skyblue:version}');
define('TOKEN_SB_NAME',           '{skyblue:name}');
define('TOKEN_LINK_LOGOUT',       '<!--{admin.logout}-->');
define('TOKEN_LINK_INBOX',        '<!--{admin.inbox}-->');
define('TOKEN_ANALYTICS',         '<!--analytics-->');
define('TOKEN_INFO_IFRAME',       '<!--{dashboard.info}-->');
define('TOKEN_UNREAD_MESSAGES',   '{unread}');
define('TOKEN_SKYBLUE_INFO_LINK', '{skyblue:link}');
define('TOKEN_BODY_CLASS',        '{body:class}');

?>