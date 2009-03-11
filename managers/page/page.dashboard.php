<?php

/**
* @version		RC 1.0.3.2 2008-04-24 15:03:43 $
* @package		SkyBlueCanvas
* @copyright	Copyright (C) 2005 - 2008 Scott Edwin Lewis. All rights reserved.
* @license		GNU/GPL, see COPYING.txt
* SkyBlueCanvas is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYING.txt for copyright notices and details.
*/

class page_dashboard 
{

    var $title      = 'Page Manager';
    var $link       = '?mgroup=pages&mgr=page';
    var $mgroup     = 'pages';
    var $group      = 'passive';
    var $hassubmenu = 0;
    
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
    
    function page_dashboard()
    {
        $this->__construct();
    }
    
    function InitDashLink()
    {
        $this->linktodashboard = 0;
        return;
        
        switch ( $this->GetEvent() ) 
        {
            case 'add':
            case 'edit':
            case 'save':
            case 'delete':
            case 'cancel':
                $this->linktodashboard = 0;
                break;

            default: 
                $this->linktodashboard = 1;
                break;
        }
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
    
    function Load() {}

}
?>