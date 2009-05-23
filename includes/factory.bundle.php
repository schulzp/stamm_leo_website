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

defined('SKYBLUE') or die(basename(__FILE__));

/**
* This class is used by the Skin class to generate the HTML output for
* the different SkyBlue Bundle types. The class uses the Factory Pattern
* as described by The Gang of Four in "Design Patterns: Elements of
* Reusable Object-Oriented Softwared" ISBN 0-201-63361-2.
*
* A Bundle is an element that can be placed on a page, whether visible to 
* humans or different types of User Agents (for example Meta tags).
*
* @package SkyBlue
*/


class BundleFactory
{
    var $test;
    
    function makeBundle($objs=array())
    {
        $html = null;
        global $Core;
        if (count($objs) == 0) return null;
        foreach ($objs as $obj)
        {
            $BundleClass = $this->GetBundleClass($obj);
            $bundle = new $BundleClass($obj);
            $html .= $bundle->get_html();
        }
        return $html;
    }
    
    function GetBundleClass(&$obj)
    {
        $class = isset($obj->type) ? $obj->type : null ;
        if (isset($obj->loadas) && !empty($obj->loadas))
        {
            $class = $obj->loadas;
        }
        return ucwords($class).'Bundle';
    }
}

class BundleBundle
{
    var $html;
    
    function __construct($obj) 
    {
        global $Core;
        $class      = ucwords($obj->bundletype).'Bundle';
        $bundle     = new $class($obj);
        $this->html = $bundle->get_html();
    }
    
    function BundleBundle($obj)
    {
        $this->__construct($obj);
    }
    
    function get_html()
    {
        return $this->html;
    }
}

class MetaBundle
{
    var $html;
    function __construct($obj) 
    {
        global $Core;
        $attrs = array();
        $attrs['name'] = $obj->name;
        $attrs['content'] = $obj->content;
        $this->html = $Core->HTML->MakeElement('meta', $attrs, null, 0);
    }
    
    function MetaBundle($obj)
    {
        $this->__construct($obj);
    }

    function get_html()
    {
        return $this->html;
    }
}

class StoryBundle
{
    var $html;
    
    function __construct($obj) 
    {
        global $Core;
        
        $story = null;
        $file  = SB_STORY_DIR . $obj->name;
        
        if (file_exists($file) &&
            !is_dir($file))
        {
            $story = $Core->OutputBuffer($file);
            $story = trim($story);
        }
        $tmp = $Core->trigger('OnAfterLoadStory', $story);
        $this->html = empty($tmp) ? $story : $tmp ;
    }
    
    function StoryBundle($obj)
    {
        $this->__construct($obj);
    }
    
    function get_html()
    {
        return $this->html;
    }
}

class ModuleBundle
{
    var $html;
    function __construct($obj) 
    {
        global $Core;
        $this->html = $Core->OutputBuffer(
            SB_USER_MODS_DIR . $obj->name 
        );
    }
    
    function ModuleBundle($obj)
    {
        $this->__construct($obj);
    }
    
    function get_html()
    {
        return $this->html;
    }
}

class XmlBundle
{
    var $html;
    var $module;
    function __construct($obj) 
    {
        global $Core;
        if (!file_exists(SB_XML_DIR . $obj->source) ||
            !file_exists(SB_USER_MODS_DIR . $obj->engine))
        {
            return;
        }
        preg_match('/\[ID:([0-9])\]/i', $obj->name, $matches);
        
        if (count($matches) < 2) return;
        
        $id = $matches[1];

        set_include_path(get_include_path() . PATH_SEPARATOR . SB_USER_MODS_DIR);
        include(SB_USER_MODS_DIR.$obj->engine);
        $bits = explode('.', $obj->engine);
        $name = $bits[1].'_module';
        
        $module = new $name;
        ob_start();
        $module->Init($id);
        $this->html = ob_get_contents();
        ob_end_clean();        
    }
    
    function XmlBundle($obj)
    {
        $this->__construct($obj);
    }
    function get_html()
    {
        return $this->html;
    }
}

class MenuBundle
{
    var $html;
    var $menubuilder;
    function __construct($obj) 
    {
        if (empty($this->menubuilder))
        {
            require_once(SB_MENU_BUILDER_FILE);
            $this->menubuilder = new menubuilder;
        }
        
        $mid = substr(
            $obj->name, 
            strpos($obj->name, '[ID:'), 
            strpos($obj->name, ']') 
        );
        $mid = str_replace(array('[ID:', ']'), null, $mid);
        $this->html = $this->menubuilder->loadmenu($mid);
    }
    
    function MenuBundle($obj)
    {
        $this->__construct($obj);
    }

    function get_html()
    {
        return $this->html;
    }
}

/** 
* Deprecated
*/

class PortfolioBundle
{
    var $html;

    function get_html()
    {
        return $this->html;
    }
}

class StylesheetBundle
{
    var $html;
    
    function __construct($obj) 
    {
        global $Core;
        $attrs['rel'] = 'stylesheet';
        $attrs['type'] = 'text/css';
        $attrs['href'] = $obj->path;
        $this->html .= $Core->HTML->MakeElement('link', $attrs, null, 0);
    }
    
    function StylesheetBundle($obj)
    {
        $this->__construct($obj);
    }

    function get_html()
    {
        return $this->html;
    }
}

class CsshackBundle
{
    var $html;
    function __construct($obj) 
    {
        global $Core;
        
        $attrs['rel'] = 'stylesheet';
        $attrs['type'] = 'text/css';
        $attrs['href'] = $obj->path;
        $this->html  = '<!--[if '.
                       strtoupper(
                       str_replace(
                           array('_', '.css'), 
                           array(' ', null), 
                           basename($obj->path))).
                       ']>'."\n";
        $this->html .= $Core->HTML->MakeElement('link', $attrs, null, 0);
        $this->html .= '<![endif]-->'."\n";
    }
    
    function CsshackBundle($obj)
    {
        $this->__construct($obj);
    }

    function get_html()
    {
        return $this->html;
    }
}

class LinkBundle
{
    var $html;
    function __construct($obj) 
    {
        global $Core;
        
        $attrs['rel'] = $obj->rel;
        $attrs['href'] = $obj->href;
        if (isset($obj->linktype))
        {
            $attrs['type'] = $obj->linktype;
        }
        $this->html .= $Core->HTML->MakeElement('link', $attrs, null, 0);
    }
    
    function LinkBundle($obj)
    {
        $this->__construct($obj);
    }

    function get_html()
    {
        return $this->html;
    }
}

class JavascriptBundle
{
    var $html;
    function __construct($obj) 
    {
        global $Core;

        $attrs['type'] = 'text/javascript';
        $attrs['src'] = $obj->path;
        $this->html .= $Core->HTML->MakeElement('script', $attrs, null, 1);
    }
    
    function JavascriptBundle($obj)
    {
        $this->__construct($obj);
    }
    
    function get_html()
    {
        return $this->html;
    }
}

?>