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

defined('MAIN_DASHBOARD') or
define('MAIN_DASHBOARD',
'<!-- <h2 id="want_to_edit">I want to edit my:</h2> -->
<ul>
    <li id="btn-dash-page"><a href="'.BASE_PAGE.'?mgroup=pages&mgr=page" 
           onmouseover="$(\'#pages_info\').show();"
           onmouseout="$(\'#pages_info\').hide();"><span class="hide">Pages</span></a>
    </li>
    <li id="btn-dash-pictures"><a href="'.BASE_PAGE.'?mgroup=pictures&mgr=media" 
           onmouseover="$(\'#pictures_info\').show();"
           onmouseout="$(\'#pictures_info\').hide();"><span class="hide">Files</span></a>
    </li>
    <li id="btn-dash-collections"><a href="'.BASE_PAGE.'?mgroup=collections&mgr=dashboard" 
           onmouseover="$(\'#collections_info\').show();"
           onmouseout="$(\'#collections_info\').hide();"><span class="hide">Collections</span></a>
    </li>
    <li id="btn-dash-templates"><a href="'.BASE_PAGE.'?mgroup=templates&mgr=dashboard" 
           onmouseover="$(\'#templates_info\').show();"
           onmouseout="$(\'#templates_info\').hide();"><span class="hide">Templates</span></a>
    </li>
    <li id="btn-dash-settings"><a href="'.BASE_PAGE.'?mgroup=settings&mgr=dashboard" 
           onmouseover="$(\'#settings_info\').show();"
           onmouseout="$(\'#settings_info\').hide();"><span class="hide">Site Settings</span></a>
   </li>
</ul>
<div id="pages_info" class="msg-info">
    <p>
        Add, edit, publish or delete pages. Enter text and pictures for your pages here.
    </p>
</div>
<div id="pictures_info" class="msg-info">
    <p>
        Upload pictures, PDFs, and other files here. 
    </p>
</div>
<div id="collections_info" class="msg-info">
    <p>
        Edit web links, photo galleries, contacts, 
        meta data and other &quot;Collections of Things&quot;.
    </p>
</div>
<div id="templates_info" class="msg-info">
    <p>
        Edit the HTML and CSS for your site skin here.
    </p>
</div>
<div id="settings_info" class="msg-info">
    <p>
        Change your personal details, username, and 
        password or check your messages here.
    </p>
</div>
'."\n"
);


defined('DASH_LINK') or
define('DASH_LINK',
'<br /><br />
<div class="dashboard">
    <ul id="sys_mods_menu">
        <li class="link_to_dashboard">
            <a href="'.BASE_PAGE.'?mgroup={mgroup}&amp;mgr=dashboard">Back to Dashboard</a>
        </li>
    </ul>
    <div class="clear"></div>
</div>'."\n"
);

define('DASH_BLOCK',
'<div class="dashboard" style="width: auto;">
<ul id="{group}_mods_menu" style="width: auto;">
{dashbuttons}
</ul>
<div class="clear"></div>
</div>'."\n"
);

defined('DASH_BUTTON') or
define('DASH_BUTTON',
'<li><a href="{link}"{javascript}>{title}</a></li>'."\n");

defined('SUB_MENU_BLOCK') or
define('SUB_MENU_BLOCK', 
'<!-- <div class="dashboard_submenu"> -->
{submenu}
<!-- </div> -->'."\n" 
);

$admindashboard = new AdminDashboard;

class AdminDashboard 
{

    var $mgr             = NULL;
    var $mgroup          = NULL;
    var $dashboards      = NULL;
    var $submenu         = NULL;
    var $javascript      = NULL;
    var $linktodashboard = NULL;
    
    function __construct() 
    {
        $this->Init();
        $this->Show();
    }
    
    function AdminDashboard()
    {
        $this->__construct();
    }

    function Init() 
    {
        global $Core;
        global $config;

        $this->GetQuery();
        $this->GetDashboards();
        asort($this->dashboards);
    }
    
    function GetQuery()
    {
        global $Core;

        $mgr = $Core->GetVar($_GET, 'mgr', NULL);
        $this->task = strToLower($mgr);
        $this->mgroup = $Core->GetVar($_GET, 'mgroup', NULL);
    }
    
    function GetDashboards()
    {
        global $Core;
        global $config;
        
        $mgrs = array();
        $mgrs = $Core->ListDirsToLevel(SB_MANAGERS_DIR, array(), 1, 0);
        for ($i=0; $i<count($mgrs); $i++)
        {
            $name = str_replace(SB_MANAGERS_DIR, NULL, $mgrs[$i]);
            $name = str_replace('/', NULL, $name);
            $file = $name.'.dashboard.php';
            
            if (file_exists($mgrs[$i].$file))
            {
                $this->dashboards[] = $mgrs[$i].$file;
            }
        }
    }
    
    function LoadDashboards() 
    {
        global $Core;
        
        $sysMods  = array();
        $userMods = array();
        $modules  = array();
        $modules['settings'] = array();
        $modules['passive']  = array();
        $modules['active']   = array();

        $html = NULL;
        $x = 1;
        
        $path = get_include_path();
        foreach ($this->dashboards as $dashboardfile)
        {
            set_include_path($path . PATH_SEPARATOR . dirname($dashboardfile));
            include($dashboardfile);
            $filename = basename($dashboardfile);
            $bits = array();
            $bits = explode('.', $filename);
            $classname = $bits[0].'_dashboard';
            $dashboard = new $classname;
            
            if (!isset($dashboard->gid))
            {
                $dashboard->gid = 1;
            }
            
            if ($dashboard->mgroup == $this->mgroup &&
                SB_GID >= $dashboard->gid)
            {
                if (isset($dashboard->hasmodule) &&
                     $dashboard->hasmodule == true &&
                     !file_exists(SB_USER_MODS_DIR.'mod.'.$bits[0].'.php'))
                {
                    continue;
                }
                if (isset($dashboard->group) && 
                     !empty($dashboard->group))
                {
                    if (!isset($modules[$dashboard->group]))
                    {
                        $modules[$dashboard->group] = array();
                    }
                    $modules[$dashboard->group][] = $dashboard;
                }

                if ($this->task != 'dashboard')
                {
                    if ($classname == $this->task.'_dashboard') 
                    {
                        $this->linktodashboard = $dashboard->linktodashboard;
                        if ($dashboard->hassubmenu)
                        {
                            ob_start();
                            $dashboard->load();
                            $submenu = ob_get_contents();
                            ob_end_clean();
                        } 
                        else 
                        {
                            $submenu = null;
                        }
                        $this->submenu = str_replace(
                            '{submenu}', $submenu, SUB_MENU_BLOCK);
                    }
                }
            }
        }
        
        $html = NULL;
        if (empty($this->task) || $this->task == 'dashboard')
        {
            $html = '<br /><br />'."\n";
            foreach ($modules as $group=>$mods)
            {
                if (count($mods))
                {
                    $buttons = NULL;
                    for ($i=0; $i<count($mods); $i++)
                    {
                        $button = str_replace('{link}', BASE_PAGE . $mods[$i]->link, DASH_BUTTON);
                        $button = str_replace('{javascript}', $this->javascript, $button);
                        $button = str_replace('{title}', $mods[$i]->title, $button);
                        $buttons .= $button;
                    }
                    $block = str_replace('{group}', $group, DASH_BLOCK);
                    $html .= str_replace('{dashbuttons}', $buttons, $block);
                }
            }
        }
        return $html;
    }
    
    function AddDashLink()
    {
        return str_replace('{mgroup}', $this->mgroup, DASH_LINK);
    }
    
    function Show() 
    {
        if ($this->task == 'main')
        {
            $this->ShowMain();
        } else {
            $this->ShowSub();
        }
    }
    
    function ShowSub()
    {
        $html = $this->LoadDashboards();
        if ($this->linktodashboard)
        {
            # echo $this->AddDashLink();
        }
        echo $html;
        if (isset($this->submenu)) 
        {
            echo $this->submenu;
        }
        
    }
    
    function ShowMain()
    {
        echo MAIN_DASHBOARD;
    }

}

?>
