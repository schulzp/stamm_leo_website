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

class media_dashboard 
{
    var $group      = 'passive';
    var $title      = 'Media';
    var $link       = '?mgroup=pictures&amp;mgr=media';
    var $mgroup     = 'pictures';
    var $base       = 1;
    var $hassubmenu = 1;
    
    // $linktodashboard: boolean value that tells the dashboard loader
    // (mod.dashboard.php) whether or not to link back to the section
    // dashboard. Your code should determine under what circumstances 
    // to include the backlink so that the system does not need to have
    // any knowledge of how your manager works.
    // 
    // mod.dashboard.php will build the link and control what the link looks
    // like. The system is set up this way so that the look of the controls
    // is consistent for usability purposes.
    
    var $linktodashboard = NULL;
    
    function __construct() 
    {
        $this->InitDashLink();
    }
    
    function media_dashboard()
    {
        $this->__construct();
    }
    
    function GetEvent()
    {
        global $Core;
        $event = $Core->GetVar($_POST, 'submit', NULL);
        $event = $Core->GetVar($_GET, 'sub', $event);
        $event = str_replace(' ', NULL, $event);
        $event = strtolower($event);
        return $event;
    }
    
    function InitDashLink()
    {
        $this->linktodashboard = 0;
        return;
        switch ($this->GetEvent()) 
        {
            case 'add':
            case 'upload':
            case 'delete':
            case 'move':
            case 'rename':
            case 'cancel':
            case 'view':
                $this->linktodashboard = 0;
                break;

            default: 
                $this->linktodashboard = 1;
                break;
        }
    }
    
    function Load() 
    {
        global $Core;
        global $config;
        
        $event = strtolower($this->GetEvent());
        
        if (strpos($event, 'add') !== FALSE ||
             strpos($event, 'view') !== FALSE ||
             strpos($event, 'rename') !== FALSE ||
             strpos($event, 'move') !== FALSE)
        {
            return;
        }

        $dir       = $Core->GetVar($_GET, 'dir', 'all');
        $dirs      = $Core->ListDirs(SB_MEDIA_DIR);
        $dirs[]    = SB_DOWNLOADS_DIR;
        $dirs[]    = SB_UPLOADS_DIR;
        $dirs[]    = ACTIVE_SKIN_IMG_DIR;
        $dirs      = $Core->SBStrReplace($dirs, SB_MEDIA_DIR, NULL);
        $dirs      = $Core->SBStrReplace($dirs, SB_SITE_DATA_DIR, NULL);
        
        $button    = $Core->GetVar($_POST, 'submit', '');
        $button    = $Core->GetVar($_GET, 'sub', $button);
        
        $url       = $this->link.'&amp;dir=all';
        $options[] = $Core->MakeOption('-- Select A Directory --', 0, 0);
        $options[] = $Core->MakeOption('Show All', $url, 0);
        for ($i=0; $i<count($dirs); $i++) 
        {
            $path = $dirs[$i];
            $selected = 0;
            if ($path == $dir) 
            {
                $selected = 1;
            }
            $url       = $this->link.'&amp;dir='.$path;
            $options[] = $Core->MakeOption($path, $url, $selected);
        }
        $js       = ' onchange="changeloc(this);"';
        $selector = $Core->SelectList($options, 'dir', 1, $js);
        
        echo '<fieldset>'."\r\n";
        echo '<legend>Media Directories</legend>'."\r\n";
        echo $selector."\r\n";
        echo '</fieldset>'."\r\n";
    }

}
?>
