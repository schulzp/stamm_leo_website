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

class plugineditor_dashboard 
{
    var $title      = 'Plugins';
    var $link       = '?mgroup=collections&mgr=plugineditor';
    var $mgroup     = 'collections';
    var $group      = 'passive';
    var $hasmodule  = 0;
    var $base       = 0;
    var $hassubmenu = 0;
    var $cantarget  = 0;
    var $gid        = 2;
    
    // $linktodashboard: boolean value that tells the dashboard loader
    // ( mod.dashboard.php ) whether or not to link back to the section
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
    
    function plugineditor_dashboard()
    {
        $this->__construct();
    }
    
    function GetEvent()
    {
        global $Core;
        $event = $Core->GetVar( $_POST, 'submit', NULL );
        $event = $Core->GetVar( $_GET, 'sub', $event );
        $event = str_replace( ' ', NULL, $event );
        $event = strtolower( $event );
        return $event;
    }
    
    function InitDashLink()
    {
        switch ( $this->GetEvent() ) 
        {
            case 'add':
            case 'edit':
            case 'save':
            case 'delete':
            case 'cancel':
            case 'editplugin':
            case 'deleteplugin':
                $this->linktodashboard = 0;
                break;

            case 'view':
            default:
                $this->linktodashboard = 1;
                break;
        }
    }
    
}
?>