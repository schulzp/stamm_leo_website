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

function ReplaceContentToken($token, $content, &$shred)
{
	$shred= str_replace($token, $content, $shred);
}

function LoadBuffers(&$skin)
{
	if (isset($_SESSION['buffer']))
	{
	    foreach ($_SESSION['buffer'] as $k=>$v)
	    {
	        $skin = str_replace('{buffer:'.trim($k).'}', trim($v), $skin);
	    }
	}
}

function GetAdminTemplate($mgr)
{
	global $Core;
	if ($mgr == 'main')
	{
	    return $Core->OutputBuffer(SB_ADMIN_SKIN_MAIN);
	} 
	return $Core->OutputBuffer(SB_ADMIN_SKIN_INDEX);
}

function LoadManager($mgr)
{
	global $Core;
	$content = null;
	ob_start();
	$Core->LoadContent($mgr);
	$content = ob_get_contents();
	ob_end_clean();
	return $content;
}

function LoadAdminModules($mgr)
{
	global $Core;
	$dashboard = null;
	if ($mgr != 'login') 
	{
	    ob_start();
	    $Core->LoadModuleAdmin2('dashboard');
	    $dashboard = ob_get_contents();
	    ob_end_clean();
	}
	return $dashboard;
}

function IsWymCompatible()
{
    $wymcompatible = false;
    if (isset($_SERVER['HTTP_USER_AGENT']))
    {
        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
    }
    if (strpos($ua, 'safari') === false && strpos($ua, 'opera') === false)
    {
        $wymcompatible = true;
    }
    return $wymcompatible;
}

// Function for building the breadcrumbs

function BreadCrumbs()
{
    global $Core;
    
    if ($Core->GetVar($_GET, 'mgr', 'login') == 'login')
    {
        return null;
    }
    
    $arrow = null;
    
    $mgroup  = $Core->GetVar($_GET, 'mgroup', null);
    $mgr     = $Core->GetVar($_GET, 'mgr', null);
    $objtype = $Core->GetVar($_GET, 'objtype', null);
    
    $html  = null;
    $html .= '<li><a href="'.BASE_PAGE.'?mgr=main">Main Dashboard</a></li>'."\n";
    if (!empty($mgroup))
    {
        $dashboard = $mgroup != 'pages' ? 'dashboard' : 'page' ;
        $dashboard = $mgroup != 'pictures' ? $dashboard : 'media';

        $class = ($dashboard == $mgr) ? ' class="active"' : null;

        $html .= "<li id=\"tab-$dashboard\"$class>\n";
        $html .= '<a href="'.BASE_PAGE.'?mgroup=';
        $html .= $mgroup.'&mgr='.$dashboard;
        $html .= '">';
        $html .= $mgroup != 'pictures' ? ucwords($mgroup) : 'Media' ;
        $html .= "</a>\n";
        $html .= "</li>\n";
        if (!empty($mgr) && 
             $mgr != 'dashboard' &&
             $mgr != 'page' &&
             $mgr != 'media')
        {
            switch($mgr)
            {
                case 'skineditor':
                    $str = 'Skin Manager';
                    break;
                case 'csseditor':
                    $str = 'CSS Manager';
                    break;
                case 'portfolio':
                    $str = 'Gallery Manager';
                    break;
                case 'bundle':
                    $str = 'Publishing Manager';
                    break;
                case 'configuration':
                    $str = 'Default Contact Info';
                    break;
                case 'skininstaller':
                    $str = 'Install Skins';
                    break;
                default:
                    $str = $mgr;
                    break;
            }
            $html .= "<li class=\"active\">\n";
            $html .= '<a href="'.BASE_PAGE.'?mgroup=';
            $html .= $mgroup.'&mgr='.$mgr;
            $html .= '">';
            $html .= ucwords($str);
            $html .= "</a>\n";
            $html .= "</li>\n";
        }
    }
    return '<ul id="tabs">'."\n".$html.'</ul>'."\n";
    return $html;
}

?>
