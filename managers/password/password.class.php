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

class password extends manager
{
    function __construct() 
    {
        $this->Init();
    }
    
    function password()
    {
        $this->__construct();
    }

    function Trigger()
    {
        global $Core;
        switch ($this->button) 
        {
            case 'save':
                if (DEMO_MODE)
                {
                    $Core->ExitDemoEvent($this->redirect);
                }
                $this->OnBeforeSave = 'HashCredentials';
                $this->SaveItems();
                break;
                
            case 'reset':
                $this->Cancel();
                break;
                
            default:
                $this->AddButton('Save', PW_VALIDATE_PASS_CHANGE);
                $this->AddButton('Reset');
                $this->InitSkin();
                $this->InitEditor();
                $this->Edit();
                $this->SetProp('showcancel', 0);
                break;
        }
    }
    
    
    function HashCredentials()
    {
        global $Core;
        
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        $confirm  = trim($_POST['confirm']);
        
        list($ok, $char) = $this->HasLegalChars($username);
        if (!$ok)
        {
            define('PW_ILLEGAL_USERNAME_CHAR',
                    '"'.$char.'" is an illegal username character. '.
                    'Please use only upper and lower-case letters, '.
                    'numbers and the symbols !@#$%&amp;*');
            $this->HandleError(5);
        }
        
        list($ok, $char) = $this->HasLegalChars($password);
        if (!$ok)
        {
            define('PW_ILLEGAL_PASS_CHAR',
                    '"'.$char.'" is an illegal password character. '.
                    'Please use only upper and lower-case letters, '.
                    'numbers and the symbols !@#$%&amp;*');
            $this->HandleError(6);
        }
        
        if (empty($username))
        {
            $this->HandleError(1);
        } 
        else if (empty($password))
        {
            $this->HandleError(2);
        }
        else if (empty($confirm))
        {
            $this->HandleError(3);
        }
        else if ($password !== $confirm)
        {
            $this->HandleError(4);
        }
        
        $_POST['username'] = md5(
            SB_PASS_SALT.$Core->GetVar($_POST, 'username', NULL) 
           );
        $_POST['password'] = md5(
            SB_PASS_SALT.$Core->GetVar($_POST, 'password', NULL) 
           );
        unset($_POST['confirm']);
        
    }
    
    function HasLegalChars($str)
    {
        for ($i=0; $i<strlen($str); $i++)
        {
            if (strpos(PW_LEGAL_CHARS, $str{$i}) === FALSE)
            {
                return array(0, $str{$i});
            }
        }
        return array(1, NULL);
    }
    
    function InitSkin()
    {
        global $Core;
        
        $file = str_replace('{objtype}', 'password', SB_SKIN_FILE_PATH);
        if (!file_exists($file))
        {
            $Core->FileNotFound($file, __LINE__,
                __FILE__.'::InitSkin()'
               );
        } else {
            $this->skin = $Core->OutputBuffer($file);
        }
    }
    
    function InitEditor() 
    {
        global $Core;

        // Set the form message
        
        
        if (empty($Core->MSG) ||
             trim($Core->MSG) == '...')
        {
            $Core->MSG = NULL;
        }
        
        // Initialize the object properties to empty strings or
        // the properties of the object being edited
        
        $_OBJ = $this->InitObjProps($this->skin, $this->obj);
        
        // This step creates a $form array to pass to BuildForm().
        // BuildForm() merges the $obj properites with the form HTML.
        
        $form['ID']       = $this->GetItemID($_OBJ);
        $form['USERNAME'] = NULL;
        $form['PASSWORD'] = NULL;
        $form['CONFIRM']  = NULL;
        
        $this->BuildForm($form);
    }
    
    //////////////////////////////////////////////////////////////////////////
    //
    // PARENT CLASS OVER-RIDES && NON-STANDARD FUNCTIONS
    //
    // The functions below this point implement OOP polymorphism to over-ride 
    // the functionality of the Manager parent class. This class requires some 
    // extended functionality that does not exist in the abstract parent class.
    //
    // In some cases more than one function is used to change the default
    // functionality but the over-ride must always begin with a name that is
    // identical to the function in the parent class.
    //
    //////////////////////////////////////////////////////////////////////////
    
    // FUNC(): Function(s) to over-ride functionality of parent class.
    
    function Cancel() 
    {
        global $Core;
        $Core->SetSessionMessage(PW_USER_CancelLED, 'warning');
        $Core->SBRedirect($this->redirect);
        exit();
    }
    
    function initConstants()
    {
        define('PW_VALIDATE_PASS_CHANGE',
                ' onclick="return validatePasswordChange(document.forms[0]);"' 
               );
        define('PW_USER_CancelLED',
                'User Canceled - Your Password was not changed');
        define('PW_EMPTY_USERNAME',
                'You did not enter a username.');
        define('PW_EMPTY_PASS',
                'You did not enter a password.');
        define('PW_EMPTY_CONFIRM',
                'You did not confirm your password.');
        define('PW_CONFIRM_MISMATCH',
                'Your password did not match the confirmation entered.');
        define('PW_UNKNOWN_ERROR', 
                'An unknown error occurred.');
        define('PW_LEGAL_CHARS',
                'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'."\n".
                '0123456789!@#$%&*');

    }
    
    function HandleError($code)
    {
        global $Core;
        
        switch ($code)
        {
            case 1:
                $str = PW_EMPTY_USERNAME;
                break;
            case 2:
                $str = PW_EMPTY_PASS;
                break;
            case 3:
                $str = PW_EMPTY_CONFIRM;
                break;
            case 4:
                $str = PW_CONFIRM_MISMATCH;
                break;
            case 5:
                $str = PW_ILLEGAL_USERNAME_CHAR;
                break;
            case 6:
                $str = PW_ILLEGAL_PASS_CHAR;
                break;
            default:
                $str = PW_UNKNOWN_ERROR;
                break;
        }
        
        $Core->SetSessionMessage($str, 'error');
        $Core->SBRedirect($this->redirect);
        exit();
    }
    
    function LoadObj()
    {
        if (count($this->objs))
        {
            $obj = $this->objs[0];
        } else {
            $obj = NULL;
        }
        if (!empty($obj))
        {
            $this->obj = new stdClass;
            foreach ($obj as $k=>$v)
            {
                if (!empty($k))
                {
                    $this->obj->$k = $v;
                }
            }
        }
    }
    
    // END FUNC()
    
}

?>