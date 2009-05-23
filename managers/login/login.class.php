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

// If this method for handling includes does not work, 
// try adding a 'inc()' function to Core.

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__));
include('login.constants.php');

class login 
{

    var $password   = NULL;
    var $username   = NULL;
    var $storedpass = NULL;
    var $storeduser = NULL;
    var $sessionid  = NULL;
    var $remoteaddr = NULL;
    var $useragent  = NULL;
    var $js         = NULL;
    var $cookies    = NULL;
    var $try        = NULL;
    var $valid      = NULL;
    var $message    = NULL;
    var $attempts   = NULL;
    var $maxtries   = NULL;
    
    function __construct() 
    {
        
        global $Core;
        
        $this->DefineConstants();
        
        $this->InitDataSource();
        
        if ($Core->GetVar($_GET, 'action', NULL) == 'logout') 
        {
            $this->logout();
        }
                
        // Make sure JavaScript & Cookies are enabled
        // in the requesting browser
        
        $this->CheckBrowserConfig();
        
        // Get the submitted login info
        
        $this->GetSubmittedLogin();
        
        // Read the stored username and password
        // only if the user has submitted their
        // username and password
        
        $this->GetStoredLogin();
        
        // Is this the first attempt to login?
        // If not, show the error message.
        
        $this->IsFirstTry();

        // Validate the user-submitted login
        
        $this->ValidateLogin();
        
        // Show the HTML
        
        $this->Show();
        
    }
    
    function login()
    {
        $this->__construct();
    }

    function DefineConstants()
    {
        global $Core;
        
        ;
    }
    
    function InitDataSource()
    {
        global $Core;

        if (!file_exists(SB_LOGIN_FILE))
        {
            $xml = $Core->xmlHandler->ObjsToXML(array(), 'login');
            $Core->WriteFile(SB_LOGIN_FILE, $xml);
        }
    }
    
    function CheckBrowserConfig() {
        global $Core;

        $this->js = $Core->GetVar($_GET, 'js', 0, 1, 0);
       
        if (!$this->js) 
        {
            echo JS_TEST_SCRIPT;
        }
        
        if (!isset($_COOKIE) || empty($_COOKIE)) 
        {
            $this->cookies = 0;
        } else {
            $this->cookies = 1;
        }
        
        if (!$this->js && !$this->cookies) 
        {
            $this->message  = MUST_HAVE_JS_AND_COOKIES;
        } elseif(!$this->js) {
            $this->message  = MUST_HAVE_JS;
        } elseif(!$this->cookies) {
            $this->message  = MUST_HAVE_COOKIES;
        }
    }
    
    function GetSubmittedLogin() 
    {
        global $Core;
        $this->username = md5(
            SB_PASS_SALT.$Core->GetVar($_POST, 'username', ''));
        $this->password = md5(
            SB_PASS_SALT.$Core->GetVar($_POST, 'password', ''));
//        $this->username = $Core->GetVar($_POST, 'username', '');
//        $this->password = $Core->GetVar($_POST, 'password', '');
    }
    
    function GetStoredLogin() 
    {
        global $Core;
        
        if (!empty($this->username) && 
             !empty($this->password)) 
        {
            $objs = array();
            $objs = $Core->xmlHandler->ParserMain(SB_LOGIN_FILE);
            if (count($objs))
            {
                $this->storeduser = $objs[0]->username;
                $this->storedpass = $objs[0]->password;
            }
        }
    }
    
    function IsFirstTry() 
    {
        global $Core;

        if (ctype_alnum($Core->GetVar($_GET, 'try', NULL))) 
        {
            $this->try = $Core->GetVar($_GET, 'try', NULL);
        }
    }
    
    function ValidateLogin() 
    {
        global $Core;
        
        $this->valid = 0;
        if (!empty($this->password) &&
             !empty($this->username) &&
             $this->password == $this->storedpass && 
             $this->username == $this->storeduser) 
        {
            $this->SetSession();
            session_write_close();
            $this->valid = 1;
            $Core->SBRedirect(BASE_PAGE.'?mgr=main');
        } 
    }
    
    function logout() 
    {
        $_SESSION = array();
    }
    
    function SetSession() 
    {
        $_SESSION['USERNAME'] = $this->username;
    }
    
    function Show() 
    {
        global $Core;

        if (!$this->valid) 
        {
            if ($this->try) 
            {
                $this->message = LOGIN_FAILED;
            }
            if ($this->js && $this->cookies) 
            {
                $html = str_replace('{message}', $this->message, LOGIN_FORM);
                $html = str_replace('{Cancel}', "", $html); // LOGIN_Cancel, $html);
                $html = str_replace('{attempts}', $this->attempts, $html);
            } 
            else 
            {
                $html = str_replace('{message}', 
                             $this->message, LOGIN_NO_COOKIES);
            }
            echo $html;
        }
    }

}

?>
