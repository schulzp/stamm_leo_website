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
# WARNING: YOU SHOULD NOT EDIT THIS FILE UNLESS YOU KNOW WHAT YOU ARE DOING!!!
#
# Changing any of the settings in this file can very easily cause SkyBlue to 
# not work. Edit this file at your own risk.
# ###################################################################################

# ###################################################################################
# The SKYBLUE constant must always be defined by the main entry point 
# (i.e., the index.php file). This prevents direct access to any sub-files.
# ###################################################################################

defined('SKYBLUE') or die(basename(__FILE__));

# ###################################################################################
# SB_LANG_FILE sets the path to the current language file. Full support for 
# localization has not been implemented as of 16 Jan, 2007. This setting is 
# automatically set so DO NOT EDIT.
# ###################################################################################

sb_conf('SB_LANG_FILE', SB_BASE_PATH.'languages/'.SB_LANGUAGE.'.lang.php');

# ###################################################################################
# SB_PAGE_SKELETON_FILE points to the location of the SkyBlueCanvas page 
# skeleton HTML file. Since every page requires a DOCTYPE, HTML, HEAD and BODY 
# block, SkyBlueCanvas adds them automatically using the page skeleton.
# ###################################################################################

sb_conf('SB_PAGE_SKELETON_FILE', SB_UI_DEFAULTS_DIR.'page.skeleton.html');

# ###################################################################################
# SB_XML_PARSER_FILE points to the location of the XML Parser.
# ###################################################################################

sb_conf('SB_XML_PARSER_FILE', SB_INC_DIR.'xml.parser.php');

# ###################################################################################
# SB_DATASOURCE_FILE points to the location of the DataSource class file. 
# As of 16 Jan, 2007, this feature has not yet been implemented but the plan 
# is to allow the site admin to use XML or SQL for data storage. 
# The DataSource class will be used as a wrapper class so that the calls to 
# Get, Save, Sort, etc. data items will be the same no matter which storage 
# option is used.
# ###################################################################################

sb_conf('SB_DATASOURCE_FILE', SB_INC_DIR.'datasource.php');

# ###################################################################################
# SB_POSTMASTER_FILE points to the location of the SkyBlueServer PostMaster 
# class file. Rather than have Mail functionality defined throughout Managers, 
# Modules, etc., a single Mail class is defined.
# ###################################################################################

sb_conf('SB_POSTMASTER_FILE', SB_INC_DIR.'postmaster.php');

# ###################################################################################
# SB_HTML_FACTORY_FILE points to the location of the HTML Factory class. 
# The class is not used consistently throughout SkyBlue but is available for 
# use so that HTML code is not imbedded in the PHP.
# ###################################################################################

sb_conf('SB_HTML_FACTORY_FILE', SB_INC_DIR.'factory.html.php');

# ###################################################################################
# SB_BUNDLE_FACTORY_FILE points to the location of the Bundle Factory class 
# file. All modules share certain characteristics in common so a Factory 
# Pattern was selected for constructing the output of Bundles.
# ###################################################################################

sb_conf('SB_BUNDLE_FACTORY_FILE', SB_INC_DIR.'factory.bundle.php');

# ###################################################################################
# SB_LOGIN_FILE points to the XML file where the login credentials for a 
# particular site are stored. Though the credentials are salted and 
# fingerprinted, HTACCESS rules should be used to prevent browser access 
# to this file.
# ###################################################################################

sb_conf('SB_LOGIN_FILE', SB_XML_DIR.'password.xml');

# ###################################################################################
# DEPRECATED.
# SB_CONFIG_XML_FILE points to the location of configuration settings stored 
# in XML format. There may be some remnants of this method in some of the older 
# SkyBlueServer code. These remnants will be completely removed in the next 
# major version update after 16 Jan, 2007.
# ###################################################################################

sb_conf('SB_CONFIG_XML_FILE', SB_XML_DIR.'configuration.xml');

# ###################################################################################
# SB_RSS_FILE points to the RSS feed builder file.
# ###################################################################################

sb_conf('SB_RSS_FILE', 'rss/index.php');

# ###################################################################################
# SB_PAGE_FILE points to the XML file containting data about the pages in 
# a particular site.
# ###################################################################################

sb_conf('SB_PAGE_FILE', SB_XML_DIR.'page.xml');

# ###################################################################################
# SB_BUNDLE_FILE points to the XML file containting data about the bundles in 
# a particular site.
# ###################################################################################

sb_conf('SB_BUNDLE_FILE', SB_XML_DIR.'bundle.xml');

# ###################################################################################
# SB_META_GRP_FILE points to the XML file containting data about the meta 
# groups in a particular site.
# ###################################################################################

sb_conf('SB_META_GRP_FILE', SB_XML_DIR.'metagroups.xml');

# ###################################################################################
# SB_META_FILE points to the XML file containting data about the meta items in 
# a particular site.
# ###################################################################################

sb_conf('SB_META_FILE', SB_XML_DIR.'meta.xml');

# ###################################################################################
# DEPRECATED.
# This feature is no longer being used.
# ###################################################################################

sb_conf('SB_MENU_GRP_FILE', SB_XML_DIR.'menus.xml');

# ###################################################################################
# SB_SKIN_FILE_PATH points to the form HTML file for the currently selected 
# Editor in the Manager currently in use. DO NOT EDIT this setting.
# ###################################################################################

sb_conf('SB_SKIN_FILE_PATH', 
    SB_MANAGERS_DIR.'{objtype}/html/form.{objtype}.html');

# ###################################################################################
# SB_EDITOR_FORM_PATH is replacing SB_SKIN_FILE_PATH. This will be a gradual 
# replacement as all of the Managers are updated.
# ###################################################################################

sb_conf('SB_EDITOR_FORM_PATH', SB_MANAGERS_DIR.'{manager}/html/form.{objtype}.html');
    
# ###################################################################################
# SITE_ANALYTICS_FILE points to the text file where the base64 encoded analytics
# script code is stored.
# ###################################################################################
    
sb_conf('SITE_ANALYTICS_FILE', SB_XML_DIR.'analytics.txt');

# ###################################################################################
# SB_STORY_XML_FILE points to the XML file containting story meta data in 
# a particular site.
# ###################################################################################

sb_conf('SB_STORY_XML_FILE', SB_XML_DIR.'story.xml');

# ###################################################################################
# SB_MENUS_FILE points to the XML file containting menu meta data for 
# a particular site.
# ###################################################################################

sb_conf('SB_MENUS_FILE', SB_XML_DIR.'menus.xml');

# ###################################################################################
# SB_MENU_BUILDER_FILE points to the location of the MenuBuilder class.
# ###################################################################################

sb_conf('SB_MENU_BUILDER_FILE', SB_INC_DIR.'menubuilder.php');

# ###################################################################################
# SB_ADMIN_SKIN_MAIN points to the location of the HTML Skin file for the 
# main Admin UI Dashboard page.
# ###################################################################################

sb_conf('SB_ADMIN_SKIN_MAIN', SB_ADMIN_SKINS_DIR.'skin.main.html');

# ###################################################################################
# SB_ADMIN_SKIN_INDEX points to the location of the HTML skin file for 
# SkyBlueServer Manager dashboards.
# ###################################################################################

sb_conf('SB_ADMIN_SKIN_INDEX', SB_ADMIN_SKINS_DIR.'skin.index.html');

# ###################################################################################
# SB_MANAGER_CLASS_FILE points to the location of the SkyBlueServer Manager
# super-class file.
# ###################################################################################

sb_conf('SB_MANAGER_CLASS_FILE', SB_INC_DIR.'manager.class.php');

# ###################################################################################
# CAMERA_ICON_GIF points to the location of the camera.gif image file. This 
# image is used when an image that is referenced in the Admin UI cannot be 
# found in the location specified.
# ###################################################################################

sb_conf('CAMERA_ICON_GIF', SB_ADMIN_IMG_DIR.'camera.gif');
sb_conf('SLUG_ICON_GIF', SB_ADMIN_IMG_DIR.'slug.gif');
sb_conf('FILE_ICON_GIF', SB_ADMIN_IMG_DIR.'file.gif');

# ###################################################################################
# SKYBLUE_TEMPLATE_ENGINE points to the location of the SkyBlueCanvas skin 
# class file. This is the class that renders SkyBlueCanvas pages.
# ###################################################################################

sb_conf('SKYBLUE_TEMPLATE_ENGINE', SB_INC_DIR.'skin.class.php');

# ###################################################################################
# SKYBLUE_TEMPLATE_ENGINE points to the location of the SkyBlueCanvas skin 
# class file. This is the class that renders SkyBlueCanvas pages.
# ###################################################################################

sb_conf('SB_EMAIL_ERROR_LOG', SB_SITE_EMAIL_DIR.'error.mail.log');

/*
# ###################################################################################
# SB_BB_CODER_FILE points to the location of the BBCoder file. The BBCoder is 
# the PHP class that converts the BBCode Tags into HTML Tags.
# ###################################################################################

sb_conf('SB_BB_CODER_FILE', SB_INC_DIR.'bbcoder.class.php');

# ###################################################################################
# SB_STATE_ABBR_MAP points to the location of the state abbreviation map. 
# The file maps state names to abbreviations.
# ###################################################################################

sb_conf('SB_STATE_ABBR_MAP', SB_PLUGIN_DIR.'states.abbr.php');

# ###################################################################################
# TOPLEVEL_DOMAINS_FILE points to the location of the top level domains library.
# ###################################################################################

sb_conf('TOPLEVEL_DOMAINS_FILE', SB_LIB_DIR.'lib.topleveldomains.php');
*/

# ###################################################################################
# SB_DEFAULT_SKIN_NAME tells SkyBlue which skin file to look for if the user skin 
# file is not found. This will only be used if the user's skin file is missing. 
# If this file is not found, SkyBlue will fall back on the value of 
# DEFAULT_HTML (configs/strings.const.php)
# ###################################################################################

sb_conf('SB_DEFAULT_SKIN_NAME', 'skin.default.html');

?>