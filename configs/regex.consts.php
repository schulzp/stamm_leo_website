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
# SB_REGEX_NUM defines the Regular Expression for a number.
# ###################################################################################

define('SB_REGEX_NUM', "^[-]?[0-9]+([\.][0-9]+)?$");

# ###################################################################################
# SB_REGEX_EMAIL defines the Regular Express for an email address.
# ###################################################################################

define('SB_REGEX_EMAIL', 
    "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$");

# ###################################################################################
# SB_REGEX_URL defines the Regular Express for an URL.
# ###################################################################################

define('SB_REGEX_URL', 
    '!^((ht|f)tps?\:\/\/)?[a-zA-Z]{1}([\w\-]+\.)+([\w]{2,5})/?$!i');

# ###################################################################################
# REGEX_IMG defines the Regular Express for finding all <img ../> in a string.
# ###################################################################################

define('REGEX_IMG', '/<img.*?[^>]+>/im');

# ###################################################################################
# REGEX_NAME_ATTR defines the Regular Express for the name attribute in a string.
# This is mainly used for cleaning up the output from HTML Tidy, which incorrectly 
# adds the name attribute to images (that won't validate as XHMTL strict).
# ###################################################################################

define('REGEX_NAME_ATTR', '/(name="[^"]*")/im');

# ###################################################################################
# REGEX_NAME_ATTR defines the Regular Express for the name attribute in a string.
# This is mainly used for cleaning up the output from HTML Tidy, which incorrectly 
# adds the name attribute to images (that won't validate as XHMTL strict).
# ###################################################################################

define('REGEX_SRC_ATTR', '/(src="([^"]+)")/');

# ###################################################################################
# REGEX_NAME_ATTR defines the Regular Express for the name attribute in a string.
# This is mainly used for cleaning up the output from HTML Tidy, which incorrectly 
# adds the name attribute to images (that won't validate as XHMTL strict).
# ###################################################################################

define('REGEX_EMPTY_ATTR', '/[a-zA-Z]+="[\s]*"/');

# ###################################################################################
# SB_REGEX_TOKEN defines the Regular Expression for detecting SkyBlue tokens.
# ###################################################################################

define('SB_REGEX_TOKEN', "/{([a-zA-Z0-9]+)}/");

# ###################################################################################
# SB_REGEX_REGION_TOKEN defines the Regular Expression for detecting SkyBlue
# skin region tokens.
# ###################################################################################

define('SB_REGEX_REGION_TOKEN', "/{region:([a-zA-Z0-9.-]+)}/");

# ###################################################################################
# SB_REGEX_AMP defines the Regular Expression for detecting an ampersand that 
# is not encoded or part of an HTML entity.
# ###################################################################################

define('SB_REGEX_AMP',
    "/&(?!(?i:\#((x([\dA-F]){1,5})|".
    "(104857[0-5]|10485[0-6]\d|".
    "1048[0-4]\d\d|104[0-7]\d{3}|".
    "10[0-3]\d{4}|0?\d{1,6}))|".
    "([A-Za-z\d.]{2,31}));)/"
   );

# ###################################################################################
# Regular expressions for object token matching
# ###################################################################################

define('SB_REGEX_OBJ_TOKEN', "/{OBJ:[^}]*}/i");
define('SB_REGEX_OBJ_PRE', '{OBJ:');
define('SB_REGEX_OBJ_END', '}');

# ###################################################################################
# '/^[\s]*\/\/(.*?)$/im' -> matches // End of line comments
# '/^[\s]*#(.*)$/im' -> matches # single line comments
# ###################################################################################

define('SB_REGEX_EOL_COMMENT', '/^[\s]*\/\/(.*?)$/im');
define('SB_REGEX_SINGLELINE_COMMENT', '/^[\s]*#(.*)$/im');

# ###################################################################################
# SB_REGEX_MYCONFIG defines the Regular Expression for matching config settings in
# myconfig.php. Before parsing the config settings, EOL and Single-Line comments are
# stripped from the stream using SB_REGEX_EOL_COMMENT and SB_REGEX_SINGLELINE_COMMENT 
# and preg_replace($pattern, $replacement, $subject);
#
# Example:
#
# sb_conf('MY_URL', 'http://www.mydomain.com');
#
# Returns:
#
# array(
#     [0] => Array(
#                [0]=> ('MY_URL', 'http://www.mydomain.com')
#            )
#     [1] => Array(
#               [0]=> MY_URL
#            )
#     [2] => Array(
#               [0]=> 'http://www.mydomain.com'
#            )
# )
#
# ###################################################################################

define('SB_REGEX_MYCONFIG', '/\(\'(.*?)\',(.*?)\)/i');

?>