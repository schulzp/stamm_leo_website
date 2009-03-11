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

class MenuItem {
    var $id;
    var $title;
    var $menumodule;
    var $link;
    var $parent;
    
    function __construct($page)
    {
		$this->id         = $page->id;
		$this->title      = $page->name;
		$this->menumodule = $page->menu;
		$this->link       = 'pid='.$page->id;
		$this->parent     = $page->parent;
    }
    
    function MenuItem($page)
    {
        $this->__construct($page);
    }
    
    function get($prop, $default=null)
    {
        if (isset($this->$prop)) return $this->$prop;
        return $default;
    }
}

class menubuilder 
{
    var $module    = null;
    var $menuitems = null;
    var $pages     = null;
    var $pid       = null;
    var $cid       = null;
    
    function __construct() 
    {
        global $Core;
        $this->pid = $Core->GetVar($_GET, 'pid', DEFAULT_PAGE);
        $this->pages = $Core->xmlHandler->ParserMain(SB_PAGE_FILE);
        $this->menuitems = array();
    }
    
    function menubuilder()
    {
        $this->__construct();
    }
    
    function GetMenuItems()
    {
        foreach ($this->pages as $page)
        {
            if ($page->menu == $this->menuid && 
                intval($page->published)) 
            {
                array_push($this->menuitems, new MenuItem($page));
            }
        }
    }

    function loadmenu($menuid) 
    {
        global $Core;

        $this->menuid  = $menuid;
        $this->module  = $Core->SelectObj(
            $Core->xmlHandler->ParserMain(SB_MENUS_FILE), 
            $this->menuid
        );
        
        $this->GetMenuItems();
        
        $menuitems = null;
        foreach ($this->menuitems as $item) 
        {
            if ($item->parent == 'null' ||
                 empty($item->parent)) 
            {
                $menuitems .= $this->menuitem($item);
            }
        }

        $header = null;
        if ($this->module->showtitle == 1)
        {
            $header = $Core->HTML->MakeElement(
                'h2',
                array(),
                $this->module->title
            ) . "\n";
        }
        return $header . $Core->HTML->MakeElement(
            'div',
            array('class' => $this->module->menutype . '-menu'),
            $Core->HTML->MakeElement(
                'ul',
                array('id'=> $this->StyleSelector($this->module->title)),
                $menuitems
            )
        );
    }

    function menuitem($item) {
        global $Core;
        global $Router;

        $attrs = array('id' => 'menu-' . $this->StyleSelector($item->title));
        if ($Core->GetVar($_GET, 'pid', DEFAULT_PAGE) == $item->id)
        {
            $attrs['class'] = 'active';
        }
        return $Core->HTML->MakeElement(
            'li',
            $attrs,
            $Core->HTML->MakeElement(
                'a',
                array('href' => $Router->GetLink($item->id)),
                $Core->HTML->MakeElement(
                    'span',
                    array('class'=>'linktext'),
                    ucwords($item->title)
                )
            ) . $this->getChildren($item)
        ) . "\n";
    }
    
    function getChildren($menu) 
    {
        global $Core;
        
        $submenus = array();
        foreach($this->menuitems as $item) 
        {
            if ($item->parent == $menu->id && !empty($item->parent) && !empty($menu->id)) 
            {
                array_push($submenus, $item);
            }
        }
        
        if (!count($submenus)) return null;

        $menuitems = null;
        foreach ($submenus as $s) 
        {
            $menuitems .= $this->menuitem($s, 1);
        }
        return "\n" . $Core->HTML->MakeElement(
            'ul',
            array(),
            $menuitems
        ) . "\n";
    }
    
    function StyleSelector($str)
    {
        $str = strtolower($str);
        $selector = null;
        $chars = "-_abcdefghijklmnopqrstuvwxyz0123456789";
        for ($i=0; $i<strlen($str); $i++)
        {
            if (strpos($chars, $str{$i}) !== false)
            {
                $selector .= $str{$i};
            }
            else
            {
                $selector .= '-';
            }
        }
        return $selector;
    }

}
?>