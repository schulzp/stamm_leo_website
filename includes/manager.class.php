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

define('MGR_FORM',
"<fieldset>\n".
"    <form id=\"mgrform\" method=\"post\" action=\"{redirect}\"{encoding}>\n".
"       <!-- <div id=\"top-buttons\">{buttons}</div> -->\n" . 
"        <table id=\"top-buttons\" cellpadding=\"0\" cellspacing=\"0\">\n".
"            <tr>\n" . 
"                <td align=\"left\">{tabs}</td>\n" . 
"                <td align=\"right\">{buttons}</td>\n" . 
"            </tr>\n" .
"        </table>\n" .
"       <div id=\"overflowwrapper\" {style:formdiv} style=\"clear: both;\">\n".
"        <table id=\"{objtype}\"".
" class=\"linkstable\" ".
" cellpadding=\"0\" ".
" cellspacing=\"0\" {style:formtable}>\n".
"            {tableheading}\n".
"            {html}\n".
"        </table>\n".
"       </div>\n".
"        {buttons}\n".
"        {countfield}\n".
"    </form>\n".
"</fieldset>");

define('MGR_NOT_VALID_EMAIL',"That does not appear to be a valid email.");
define('MGR_NOT_EMPTY'," cannot be empty.");
define('MGR_NOT_VALID_URL',"That does not appear to be a valid url.");
define('MGR_MUST_BE_NUMBER'," must be a number.");


/**
* The purpose of this class is to allow developers to easily add
* new components (extensions) to SkyBlue. This class contains all the
* routines needed for /most/ component managers.
* 
* The Manager class contains the base functions necessary for SkyBlue 
* component creation. To use this class, your component class should inherit
* from this class using the 'extends' keyword.
* 
* <code>
* Example: class MyClass extends manager
* {
*     // The class code goes here...
* }
* </code>
*
* @package SkyBlue
*/


class manager extends SkyBlueObject
{

    var $id            = null;
    var $mgr           = null;
    var $name          = null;
    var $obj           = null;
    var $objs          = array();
    var $objtype       = null;
    var $objtypes      = null;
    var $filter        = null;
    var $filterprop    = null;
    var $filterval     = null;
    var $datasrc       = null;
    var $redirect      = null;
    var $button        = null;
    var $buttons       = array();
    var $tabs          = array();
    var $headings      = null;
    var $tasks         = null;
    var $cols          = null;
    var $editor        = null;
    var $html          = null;
    var $skin          = null;
    var $rangefields   = null;
    var $showcancel    = null;
    var $mgroup        = null;
    var $countfield    = null;
    var $form_encoding = null;
    var $OnBeforeSave  = null;
    var $OnAfterSave   = null;
    var $OnBeforeLoad  = null;
    var $OnAfterLoad   = null;
    var $OnBeforeTrigger = null;
    var $OnBeforeShow  = null;
    var $OnBeforeViewItems = array();
    var $styles        = array();
    var $validation    = array();
    var $updatesitemap = false;

    var $datasources   = array();
    
    function __construct() 
    {
        ;
    }
    
    function manager()
    {
        $this->__construct();
    }

    function Init()
    {
    
        // Capture the manager class first
        
        $this->InitMgr();
    
        // Get the class of obj being manipulated
        
        $this->InitObjType();
    
        // We define our constants next since they are usually used to 
        // symbolically point to resources. InitConstants() is empty in 
        // this super-class. It is meant to be over-ridden by derivative
        // classes.
        
        $this->InitConstants();
        
        // Add any event handlers defined by the derivative class
        
        $this->AddEventHandlers();
    
        // Execute any OnBeforeLoad callbacks
        
        $this->BeforeLoad();
        
        // If this manager has a corresponding front-end module, make sure
        // the module is installed before loading the manager.
        
        $this->FindModule();
        
        // Capture the button the user clicked
        
        $this->InitButton();
        
        // Make sure the data source exists
        
        $this->InitDataSource();
        
        // Determine the redirect for form actions
        
        $this->InitRedirect();
        
        // Load all objects of this class
        
        $this->InitObjs();
        
        // Determine the specific object
        
        $this->InitItemID();
        
        // Load the object into memory
        
        $this->LoadObj();
        
        // Execute any OnBeforeTrigger callbacks
        
        $this->BeforeTrigger();
        
        // Execute the event that corresponds to the user's last action
        
        $this->Trigger();
        
        // Execute any OnBeforeShow callbacks
        
        $this->BeforeShow();
        
        // Show the results of the system event
        
        $this->Show();
    }
    
    // InitConstants() is meant to be over-ridden by the derivative
    // class if it is needed. The derivative class does not need
    // to call the function as it is called automatically as part
    // of the object Initialization.
    
    function InitConstants()
    {
        ;
    }
    
    // AddEventHandlers() is meant to be over-ridden by the derivative
    // class if it is needed. The derivative class does not need
    // to call the function as it is called automatically as part
    // of the object Initialization.
    
    function AddEventHandlers()
    {
        ;
    }
    
    // Trigger() is meant to be over-ridden by the derivative
    // class if it is needed. The derivative class does not need
    // to call the function as it is called automatically as part
    // of the object Initialization.
    //
    // If your derivative class is a simple, 1-dimensional editor,
    // meaning it only works on one type of object,
    // You do not even need to over-ride this function.
    //
    // Example 1-Dimensional class: links
    //
    // Example 2-Dimensional class: links, links groups
    
    function Trigger()
    {
        global $Core;
        switch($this->button) 
        {
            case 'add':
            case 'edit':
            case 'edit'.$this->objtype:
            case 'add'.$this->objtype:
                $this->AddButton('Save');
                $this->InitSkin();
                $this->InitEditor();
                $this->Edit();
                break;
                
            case 'up'.$this->objtype:
                $this->ReorderObjs(true);
				$this->SaveItems();
                break;
            case 'down'.$this->objtype:
                $this->ReorderObjs(false);
				$this->SaveItems();
                break;

            case 'save':
                if (DEMO_MODE) $Core->ExitDemoEvent($this->redirect);
                $this->SaveItems();
                break;
                
            case 'delete':
            case 'delete'.$this->objtype:
                if (DEMO_MODE) $Core->ExitDemoEvent($this->redirect);
                $this->DeleteItem();
                break;
                
            case 'cancel':
                $this->Cancel();
                break;
                
            default: 
                $this->AddButton('Add');
                $this->InitProps();
                $this->ViewItems();
                break;
        }
    }
    
    /* ** EXTERNAL FUNCTIONS FOR GETTING/SETTING PROPERTIES ** */
    
    /*
    
        GetProp()
        SetProp()
        AddProp()
        
        - These are interfaces that your class should use to set get/set
        properties of the parent class.
        
    */

    function GetProp($key)
    {
        if (array_key_exists($key, $this))
        {
            return $this->$key;
        }
        return false;
    }
    
    function SetProp($key, $value)
    {
        if (array_key_exists($key, $this))
        {
            $this->$key = $value;
        }
    }
    
    function AddProp($key, $value)
    {
        if (array_key_exists($key, $this))
        {
            if (is_array($this->$key))
            {
                $arr = $this->$key;
                for ($i=0; $i<count($value); $i++)
                {
                    $arr[] = $value[$i];
                }
                $this->$key = $arr;
            } 
            else 
            {
                $this->$key = $value;
            }
        }
    }

    function AddTab($link, $text, $attrs=array())
    {
        if (!is_array($this->tabs))
        {
			$this->tabs = array($this->tabs);
		}
		$this->tabs[] = array('link'=>$link, 'text'=>$text, 'attrs'=>$attrs);
    }
    
    function AddFieldValidation($field,$validation=null)
    {
        if (!in_array($field,$this->validation))
        {
            $this->validation[$field] = $validation;
        }
    }
    
    function AddEventHandler($event,$callback)
    {
        if (is_array($this->$event))
        {
            if (!in_array($callback,$this->$event))
            {
                $arr = $this->$event;
                $arr[] = $callback;
                $this->$event = $arr;
            }
        }
        else if (!empty($this->$event))
        {
            $oldcallback = $this->$event;
            $this->$event = array();
            $arr = array($oldcallback,$callback);
            $this->$event = $arr;
        }
        else
        {
            $this->$event = array($callback);
        }
    }

    function BeforeViewItems()
    {
        if (!empty($this->OnBeforeViewItems))
        {
            if (is_array($this->OnBeforeViewItems))
            {
                for($i=0; $i<count($this->OnBeforeViewItems); $i++)
                {
                    $Callback = $this->OnBeforeViewItems[$i];
                    if (is_callable(array($this,$Callback)))
                    {
                        $this->$Callback();
                    }
                }
            }
            else
            {
                $Callback = $this->OnBeforeViewItems;
                if (is_callable(array($this,$Callback)))
                {
                    $this->$Callback();
                }
            }
        }
    }
    
    function BeforeLoad()
    {
        if (!empty($this->OnBeforeLoad))
        {
            if (is_array($this->OnBeforeLoad))
            {
                for($i=0; $i<count($this->OnBeforeLoad); $i++)
                {
                    $Callback = $this->OnBeforeLoad[$i];
                    if (is_callable(array($this,$Callback)))
                    {
                        $this->$Callback();
                    }
                }
            }
            else
            {
                $Callback = $this->OnBeforeLoad;
                if (is_callable(array($this,$Callback)))
                {
                    $this->$Callback();
                }
            }
        }
    }
    
    function BeforeTrigger()
    {
        if (!empty($this->OnBeforeTrigger))
        {
            if (is_array($this->OnBeforeTrigger))
            {
                for($i=0; $i<count($this->OnBeforeTrigger); $i++)
                {
                    $Callback = $this->OnBeforeTrigger[$i];
                    if (is_callable(array($this,$Callback)))
                    {
                        $this->$Callback();
                    }
                }
            }
            else
            {
                $Callback = $this->OnBeforeTrigger;
                if (is_callable(array($this,$Callback)))
                {
                    $this->$Callback();
                }
            }
        }
    }
    
    function BeforeShow()
    {
        if (!empty($this->OnBeforeShow))
        {
            if (is_array($this->OnBeforeShow))
            {
                for($i=0; $i<count($this->OnBeforeShow); $i++)
                {
                    $Callback = $this->OnBeforeShow[$i];
                    if (is_callable(array($this,$Callback)))
                    {
                        $this->$Callback();
                    }
                }
            }
            else
            {
                $Callback = $this->OnBeforeShow;
                if (is_callable(array($this,$Callback)))
                {
                    $this->$Callback();
                }
            }
        }
    }
    
    function AfterLoad()
    {
        if (!empty($this->OnAfterLoad))
        {
            if (is_array($this->OnAfterLoad))
            {
                for($i=0; $i<count($this->OnAfterLoad); $i++)
                {
                    $Callback = $this->OnAfterLoad[$i];
                    if (is_callable(array($this,$Callback)))
                    {
                        $this->$Callback();
                    }
                }
            }
            else
            {
                $Callback = $this->OnAfterLoad;
                if (is_callable(array($this,$Callback)))
                {
                    $this->$Callback();
                }
            }
        }
    }
    
    function BeforeSave()
    {
        if (!empty($this->OnBeforeSave))
        {
            if (is_array($this->OnBeforeSave))
            {
                for($i=0; $i<count($this->OnBeforeSave); $i++)
                {
                    $Callback = $this->OnBeforeSave[$i];
                    if (is_callable(array($this,$Callback)))
                    {
                        $this->$Callback();
                    }
                }
            }
            else
            {
                $Callback = $this->OnBeforeSave;
                if (is_callable(array($this,$Callback)))
                {
                    $this->$Callback();
                }
            }
        }
    }
        
    function GetItemID($obj)
    {
        global $Core;
        if (!isset($obj['id']) ||
             empty($obj['id']))
        {
            $id = $Core->GetNewID($this->obs);
        } else {
            $id = $obj['id'];
        }
        return $id;
    }
    
    /* ** INITIALIZATION FUNCTIONS ** */
    
    function GetContext($field, $trace='')
    {
        if (isset($_SESSION['REQ_'.$field]))
            return $_SESSION['REQ_'.$field];
        else
            return null;
    }

    function SetContext($field, $value)
    {
        $_SESSION['REQ_'.$field] = $value;
    }

    function InitItemID()
    {
        global $Core;
        $this->id = $Core->GetVar($_REQUEST, 'id', null);
        if ($this->id <= 0 && $this->objtype == $this->GetContext('TYPE'))
        {
           $id = $this->GetContext('ID');
           $this->id = $id ? $id : 0;
        }
    }
    
    function InitMgr()
    {
        global $Core;
        $this->mgr    = $Core->GetVar($_GET, 'mgr', null);
        $this->mgroup = $Core->GetVar($_GET, 'mgroup', null);
    }

    function InitSkin()
    {
        global $Core;
        $this->skin = $Core->OutputBuffer(
            SB_MANAGERS_DIR.$this->mgr.'/html/form.'.$this->objtype.'.html');
    }
    
    function GetEditorForm()
    {
        global $Core;
        $form = str_replace('{manager}',$this->mgr, SB_EDITOR_FORM_PATH);
        $form = str_replace('{objtype}',$this->objtype, $form);
        $this->skin = $Core->outputBuffer($form);
    }
    
    function FindModule()
    {
        global $Core;
        if (isset($this->hasmodule) && $this->hasmodule && 
             !file_exists(SB_USER_MODS_DIR.'mod.'.
                 strtolower($this->mgr).'.php'))
        {
            $Core->SBRedirect(
                'admin.php?mgroup='.$this->mgroup.'&mgr=dashboard');
        }
    }
    
    function InitObjType()
    {
        global $Core;
        
        $mgr = !empty($this->mgr) ? $this->mgr : null;
        
        // Set $this->objtype to its lowest priority value 'mgr'
        // At the very least, $this->objtype will have a default value
        
        $this->objtype = $mgr;
        
        // Next, check the query string
        
        $this->objtype = $Core->GetVar($_GET, 'objtype', $mgr);
        
        // The button pressed has highest priority
        // Check the $objtypes first for matches with the button
        // and over-ride any previously set values
        
        for ($i=0; $i<count($this->objtypes); $i++)
        {
            if (strpos($this->button, $this->objtypes[$i]) !== FALSE)
            {
                $this->objtype =  $this->objtypes[$i];
            }
        }
        
        $this->objtype = $Core->GetVar($_POST, 'objtype', $this->objtype);
        
    }
    
    function InitDataSource()
    {
        global $Core;

        $this->datasrc = SB_XML_DIR.$this->objtype.'.xml';
        if (!file_exists($this->datasrc))
        {
            $xml = $Core->xmlHandler->ObjsToXML($this->objs, $this->objtype);
            $Core->WriteFile($this->datasrc, $xml, 1);
        }
    }
    
    function InitRedirect()
    {
        global $Core;
        
        define('MGR_DEFAULT_REDIRECT', 
          "admin.php?mgroup=".$this->mgroup. 
          "&mgr=".$this->mgr);
        
        define('MGR_BASE_REDIRECT', 
          "admin.php?mgroup=".$this->mgroup. 
          "&mgr=".$this->mgr."&objtype=".$this->objtype);

        $this->redirect = BASE_PAGE.'?mgroup='.$this->mgroup.
                          '&mgr='.$this->mgr.
                          '&objtype='.$this->objtype;
        if (!empty($this->filter))
        {
            $this->filterval = $Core->GetVar($_GET, $this->filter, null);
            if (!empty($this->filterval))
            {
                $this->redirect .= '&'.$this->filter.'='.$this->filterval;
            }
        }
    }
    
    function UpdateReferences($objtype)
    {
        $this->SetObjType($objtype);
    }

    function SetObjType($objtype)
    {
        global $Core;
        $this->objtype = $objtype;
        $this->redirect = BASE_PAGE.'?mgroup='.$this->mgroup.
                          '&mgr='.$this->mgr.
                          '&objtype='.$objtype;
        if (!empty($this->filter))
        {
            $this->filterval = $Core->GetVar($_GET, $this->filter, null);
            if (!empty($this->filterval))
            {
                $this->redirect .= '&'.$this->filter.'='.$this->filterval;
            }
        }
        $this->InitDataSource();
        $this->InitObjs();
        $this->LoadObj();
    }
    
    function InitObjs($src=null)
    {
        global $Core;
        $src = empty($src) ? $this->datasrc : $src ;
        if (file_exists($src))
        {
            $this->objs = $Core->xmlHandler->ParserMain($src);
        } 
        else 
        {
            $this->objs = array();
        }
    }

    function GetObjects($src)
    {
        global $Core;
        if (file_exists($src))
        {
            return $Core->xmlHandler->ParserMain($src);
        } 
        return array();
    }

    function ObjectSelector($src, $name, $ValueField, $TextField, $selected=null)
    {
        global $Core;
        $keyValuePairs = array();
        $objs = $this->GetObjects($src);
        foreach ($objs as $obj)
        {
            $option = array();
            if (isset($obj->$ValueField) && isset($obj->$TextField))
            {
                $option['value'] = $obj->$ValueField;
                $option['text']  = $obj->$TextField;
            }
            $keyValuePairs[] = $option;
        }
        return $Core->Selector($name, $keyValuePairs, $selected);
    }
    
    function FilterObjs()
    {
        global $Core;
        
        if (!empty($this->filter))
        {
            $objs1 = $this->objs;
            $this->filterval = $Core->GetVar($_GET, $this->filter, FALSE);
            if ($this->filterval && $this->filterval != 'all')
            {
                $this->objs = array();
                foreach ($objs1 as $obj)
                {
                    $prop = $this->filterprop;
                    if ($obj->$prop == $this->filterval)
                    {
                        $this->objs[] = $obj;
                    }
                }
            }
        }
    }
    
    function BuildHeadings() 
    {
        $heading = null;
        if (isset($this->include_th) &&
             $this->include_th == 1)
        {
            $heading  = "<tr>\n";
            for ($i=0; $i<count($this->headings); $i++)
            {
                $heading .= "<th>".$this->headings[$i]."</th>\n";
            }
            $heading .= "</tr>\n";
        }
        return $heading;
    }
    
    function InitButton()
    {
        global $Core;
        $this->button = $Core->GetVar($_POST, 'submit', null);
        $this->button = $Core->GetVar($_GET, 'sub', $this->button);
        $this->button = strtolower($this->button);
        $this->button = str_replace(' ', '', $this->button);
        return $this->button;
    }
    
    function LoadObj()
    {
        global $Core;
        if (!empty($this->id))
        {
            $this->obj = $Core->SelectObj($this->objs, $this->id);
        }
    }
    
    /* ** OBJECT EDITING FUNCTIONS ** */
    
    function ValidateField($field)
    {
        global $Core;
        
        if (!isset($_POST[$field]))
        {
            return true;
        }
        
        if (isset($_POST['sub']) && !empty($_POST['sub']))
        {
            $sub = $_POST['sub'];
        }
        else
        {
            // return true;
            $sub = 'add';
        }
        
        $redirect = MGR_BASE_REDIRECT."&sub=".$sub."&id=".$_POST['id'];
                    
        if (isset($this->validation[$field]))
        {
            switch ($this->validation[$field])
            {
                case 'notnull':
                    if (!$Core->ValidateField($_POST[$field],'notnull'))
                    {
                        $Core->ExitWithError(
                            ucwords($field).MGR_NOT_EMPTY, 
                            $redirect);
                        exit;
                    }
                    break;
                case 'email':
                    if (empty($_POST[$field])) return true;
                    if (!$Core->ValidateField($_POST[$field],'email'))
                    {
                        $Core->ExitWithError(
                            MGR_NOT_VALID_EMAIL, 
                            $redirect);
                        exit;
                    }
                    break;
                case 'url':
                    if (!$Core->ValidateField($_POST[$field],'url'))
                    {
                        $Core->ExitWithError(
                            MGR_NOT_VALID_URL, 
                            $redirect);
                        exit;
                    }
                    break;
                case 'number':
                    if (!$Core->ValidateField($_POST[$field],'number'))
                    {
                        $Core->ExitWithError(
                            ucwords($field).MGR_MUST_BE_NUMBER, 
                            $redirect);
                        exit;
                    }
                    break;
                default:
                    break;
            }
        }
    }
        
    function Decode($item,$key='')
    {
        if (!is_array($item))
        {
            if (trim($item) != '')
            {
                return base64_decode($item);
            }
            return null;
        }
        if (isset($item[$key]) && 
             trim($item[$key]) != '')
        {
            return base64_decode($item[$key]);
        }
        return null;
    }
    
    function Encode($item,$key)
    {
        if (!is_array($item))
        {
            if (trim($item) != '')
            {
                return base64_encode($item);
            }
            return null;
        }
        if (isset($item[$key]) && 
             trim($item[$key]) != '')
        {
            return base64_encode($item[$key]);
        }
        return null;
    }

    function SaveItems($redirect='') 
    {
        global $Core;
        
        if (!empty($redirect))
        {
            $this->redirect = $redirect;
        }
        
        $this->BeforeSave();
        
        if ($this->updatesitemap)
        {
            $Core->UpdateSitemap();
        }
        
        foreach ($_POST as $k=>$v)
        {
            if ($k != 'submit')
            {
                if (is_array($v))
                {
                    for($i=0; $i<count($v); $i++)
                    {
                        $v[$i] = trim($Core->GetVar($v, $i, null));
                    }
                    $val = implode(',', $v);
                } 
                else 
                {
                    $val = trim($Core->GetVar($_POST, $k, null));
                }
                $this->ValidateField($k);
                $arr[$k] = $Core->stripslashes_deep($val);
            }
        }
        
        $obj = $Core->SelectObj($this->objs, $this->id);
        if (!isset($obj->id) || $obj->id == 0) 
        {
            $obj = $Core->ArrayToObj($obj, $arr);
        } 
        else 
        {
            $obj = $Core->UpdateObjFromArray($obj, $arr);
        }
        
        $this->objs = $Core->InsertObj($this->objs, $obj, 'id');
        
        if (!empty($arr['order'])) 
        {
            $this->objs = $Core->OrderObjs(
                $this->objs, $this->id, $arr['order']);
        }
        $xml = $Core->xmlHandler->ObjsToXML($this->objs, $this->objtype);
        $result = $Core->WriteFile($this->datasrc, $xml, 1);
        if ($result) $this->clear_cache();
        $Core->ExitEvent(
            $result, 
            $this->redirect
        );
    }
        
    function DeleteItem() 
    {
        global $Core;
        
        $Core->RequireID($this->id, $this->redirect);
        
        $obj = $Core->SelectObj($this->objs, $this->id);
        
        if ($obj && isset($obj->story))
        {
            $story = SB_STORY_DIR . $obj->story;
            if (file_exists($story))
            {
                unlink($story);
            }
        }
        
        $this->objs = $Core->DeleteObj($this->objs, $this->id);
        $xml = $Core->xmlHandler->ObjsToXML($this->objs, $this->objtype);
        $result = $Core->WriteFile($this->datasrc, $xml, 1);
        if ($result) $this->clear_cache();
        $Core->ExitEvent(
            $result, $this->redirect
        );
    }
    
    function Edit() 
    {
        global $Core;
        
        $obj = null;
        if (strpos($this->button, 'edit') !== false)
        {
            $Core->RequireID($this->id, $this->redirect);
            $obj = $Core->SelectObj($this->objs, $this->id);
        }
        $this->html       = $this->editor;
        $this->showcancel = 1;
    }
    
    /* ** UI BUILDING FUNCTIONS ** */
    
    // FilterViewItems() is intended to take a list of potential view items and modify
    // the contents and/or order of items based on ownership by a master item.
    // It is meant to be over-ridden by the derivative class if it is needed.
                    
    function FilterViewItems($objs)
    {   
        return $objs;
    }       

    function InitObjProps()
    {
        global $Core;
        $tokens = $this->GetTokenList("/{OBJ:[^}]*}/i", $this->skin);
        foreach($tokens as $k=>$v)
        {
            $prop = str_replace('{OBJ:', '', $v);
            $prop = str_replace('}', '', $prop);
            $prop = strToLower($prop);
            $_OBJ[$prop] = null;
        }
        $_OBJ['id'] = $Core->GetNewID($this->objs);
        
        $obj = $this->obj;
        if (isset($obj->id) && trim($obj->id) != '') 
        {    
            foreach($_OBJ as $k=>$v)
            {
                if (isset($obj->$k))
                {
                    $obj->$k = stripslashes($obj->$k);
                    $_OBJ[$k] = $obj->$k;
                }
            }
        }
        return $_OBJ;
    }
    
    function GetTokenList($pattern, $str) 
    {
          preg_match_all($pattern, $str, $tokens, PREG_SPLIT_DELIM_CAPTURE);
          $return = array();
          for ($i=0; $i<count($tokens); $i++) 
          {
              if (!in_array($tokens[$i][0], $return)) 
              {
                  $return[] = $tokens[$i][0];
              }
          }
          return $return;
    }
    
    function BuildForm($form)
    {
        global $Core;
        $Core->Localize($this->skin);
        foreach($form as $k=>$v)
        {
            $this->skin = str_replace('{OBJ:'.$k.'}', $v, $this->skin);
        }
        $this->editor = $this->skin;
        $this->skin   = null;
    }
    
    function SetFormMessage($ItemKey='name', $NewItemStr=null)
    {
        global $Core;
        if (strpos($this->button,'edit') !== false ||
             strpos($this->button,'add') !== false)
        {
            $itemtitle = isset($this->obj->$ItemKey) ? $this->obj->$ItemKey : 'New '.$NewItemStr ;
            if (isset($this->obj->id) && !empty($this->obj->id))
            {
                $itemtitle .= ' (ID: '.$this->obj->id.')';
            }
            $Core->MSG = str_replace('{msg}', $itemtitle, SB_MSG_EDIT);
        }
    }

    function Show() 
    {
        global $Core;
        
        $form = str_replace('{redirect}',     $this->redirect,        MGR_FORM);
        $form = str_replace('{encoding}',     $this->form_encoding,   $form);
        $form = str_replace('{tabs}',         $this->BuildTabs(),     $form);
        $form = str_replace('{objtype}',      $this->objtype,         $form);
        $form = str_replace('{tableheading}', $this->BuildHeadings(), $form);
        $form = str_replace('{html}',         $this->html,            $form);
        $form = str_replace('{buttons}',      $this->BuildButtons(),  $form);
        $form = str_replace('{countfield}',   $this->countfield,      $form);

        if (!empty($this->styles) && is_array($this->styles))
        {
            foreach ($this->styles as $k=>$v)
            {
                $form = str_replace('{style:'.$k.'}',$v,$form);
            }
        }
        
        $form = preg_replace('/{style:[a-zA-Z0-9]*}/i',null,$form);

        
        $Core->GetLastError();
        
        $message = trim($Core->MSG);
        if (!empty($message) && $message != '...') {
            echo $Core->MSG;
        }

        echo $form;
    }
    
    function ViewItems() 
    {
        global $Core;
        
        $this->include_th = 1;
        $this->html = null;

        $this->BeforeViewItems();
        
        $objs = $this->FilterViewItems($this->objs);

        $id = 0;
        if (count($objs) > 0) 
        {
        
            if (count($objs) > SB_MAX_LIST_ROWS)
            {
                $this->styles['formdiv'] = sLIST_OVERFLOW_HEIGHT_STYLE;
                $this->styles['formtable'] = sLIST_OVERFLOW_WIDTH_STYLE;
            }

            if (count($this->tasks) <= 2)
            {
                $taskwidth = count($this->tasks) * 6;
            }
            else
            {
                $taskwidth = count($this->tasks) * 3;
            }
            $colwidth  = floor((100 - $taskwidth) / count($this->cols));
        
            foreach ($objs as $obj) 
            {
                $class = ($id % 2 == 0 ? 'even' : 'odd');
                $this->html .= "<tr>\n";
                for ($i=0; $i<count($this->cols); $i++)
                {
                    $key = $this->cols[$i];
                    $val = null;
                    if (isset($obj->$key))
                    {
                        $val = stripslashes($obj->$key);
                    }
                    $this->html .= 
                        "<td valign=\"middle\" class=\"$class\">$val</td>\n";
                }

                $this->html .= "<td style=\"width: " . TASK_COL_WIDTH . "px;\" valign=\"middle\" class=\"$class\">\n";
                for ($i=0; $i<count($this->tasks); $i++)
                {
                    if ($this->tasks[$i] != TASK_SEPARATOR)
                    {
                        $task = split(":", $this->tasks[$i]);
                        
                        if ($task[0] == 'up' && $id == 0 || 
                            $task[0] == 'down' && $id == count($objs) - 1)
                        {
                            $width = $task[0] == 'up' ? '22' : '20' ;
                            $this->html .= 
                            "<img src=\"" . SB_ADMIN_IMG_DIR.'clear.gif' . 
                            "\" alt=\"disabled task\"" . 
                            "style=\"border: none; position: relative; top: 3px; width: {$width}px; height: 16px;\"/>";
                            continue;
                        }
                        
                        if (isset($obj->published) && $task[0] == 'publish')
                        {
                            if ($obj->published === 0 || strtolower($obj->published) == 'no')
							{
								$task[0] = 'publish';
							}
							else if ($obj->published === 1 || strtolower($obj->published) == 'yes')
                            {
                                $task[0] = 'unpublish';
                            }
                        }
                        
                        $this->html .= '<a href="'.$this->redirect.
                                       '&amp;sub='.$task[0].$this->objtype.
                                       '&amp;id='.$obj->id;
                        $itemName = 'item';
						if (isset($obj->name) && !empty($obj->name))
						{
							$itemName = $obj->name;
						}
						else if (isset($obj->title) && !empty($obj->title))
						{
							$itemName = $obj->title;
						}
                        if (strToLower($this->tasks[$i]) == 'delete' ||
                            !empty($task[0]) && strtolower($task[0]) == 'delete')
                        {
                            $name = $this->cols[0];
                            $this->html .= '" onclick="'.
                                           str_replace('{name}', $itemName, sCONFIRM_DELETE_JS);
                        }
                        $this->html .= '">';
                        
                        if (file_exists(SB_ADMIN_IMG_DIR . 'task_' . $task[0] . '.gif'))
                        {
                            $task[1] = 'task_' . $task[0] . '.gif';
                        }
                        else if (file_exists(SB_ADMIN_IMG_DIR . 'task_' . $task[0] . '.png'))
                        {
                            $task[1] = 'task_' . $task[0] . '.png';
                        }
                        
                        if (empty($task[1]))
                        {
                            $this->html .= ucwords($task[0]);
                        }
                        else
                        {
                            $verb = in_array($task[0], array('up', 'down')) ? 'Order ' : null;
                            $this->html .= 
                            "<img src=\"" . SB_ADMIN_IMG_DIR.$task[1] . 
                            "\" title=\"" . ucwords("$verb{$task[0]} '$itemName'") . 
                            "\" alt=\"" . ucwords("$verb{$task[0]} '$itemName'") . 
                            "\" style=\"border: none; position: relative; top: 3px;\"/>";
                        }
                        $this->html .= '</a>';
                    }
                    else
                        $this->html .= " |";
                }
                $this->html .= "</tr>\n";    
                $id++;
            }
        } 
        else 
        {
            $this->html .= "<tr>\n".
                           "<td colspan=\"3\" width=\"100%\">".
                           SB_NO_ITEMS_TO_DISPLAY.
                           "</td>\n".
                           "</tr>\n";
        }
    }
    
    function Cancel($exitcode=2)
    {
        global $Core;
        $Core->ExitEvent($exitcode, $this->redirect);
    }
    
    function GetObjProp($obj, $prop, $default=null)
    {
        if (isset($obj[$prop]) && 
             !empty($obj[$prop]))
        {
            $val = $obj[$prop];
        } 
        else 
        {
            $val = $default;
        }
        return $val;
    }
    
    function GetObjOrder($obj, $key)
    {
        global $Core;
        if (!isset($obj[$key]))
        {
            return str_replace('{prop}', $key, sOBJ_PROP_NOT_EXISTS);
        }
        return $Core->OrderSelector2($this->objs, $key, $obj[$key]);
    }
    
    function ReorderObjs($up=true, $oidfield='')
    {
        global $Core;

        $oid = 0;
        if (!empty($oidfield))
        {
            $obj = $Core->SelectObj($this->objs, $this->id);
            $oid = $obj->$oidfield;
        }

        $ppos = $cpos = $npos = -1;
        $objs = $this->objs;
        for ($i = 0; $i < count($objs) && $npos == -1; $i++)
        {
            if (   (!empty($oidfield) && $oid && $objs[$i]->$oidfield == $oid)
                || empty($oidField))
            {
                if ($objs[$i]->id != $this->id)
                {
                    if ($cpos == -1)
                       $ppos = $i;
                    else
                       $npos = $i;
                }
                else
                    $cpos = $i;
            }
        }

        $order = -1;
        if ($up && $ppos != -1)
            $order = $ppos + 1;
        elseif (!$up && $npos != -1)
            $order = $npos + 1;
            
        if ($order != -1)
        {
            $this->objs = $Core->OrderObjs($this->objs, $this->id, $order);
            $xml = $Core->xmlHandler->ObjsToXML($this->objs, $this->objtype);
            $Core->WriteFile($this->datasrc, $xml, 1);
            $this->clear_cache();
        }
        $Core->SBRedirect($this->redirect);
    }
    
    function clear_cache() {
        $Cache = new Cache("");
        $Cache->clearAll();
    }

    function GetStoryFileName()
    {
        global $Core;
        
        $story = isset($this->obj->story) && 
                     !empty($this->obj->story) ? 
                         $this->obj->story : null;

        if (empty($story))
        {
            $story = $Core->GetVar($_POST, 'story', null);
            if (empty($story))
            {
                $id = $this->id;
                if (empty($id))
                {
                     $id = $Core->GetVar($_POST, 'id', $Core->GetNewID($this->objs));
                }
                $story = $this->mgr.'.'.$this->objtype.'.'.$id.'.txt';
            }
        }
        if (isset($_POST) && count($_POST))
        {
            $_POST['story'] = $story;
        }
        return $story;
    }
    
    function GetStoryContent($obj) {
        global $Core;
        if (!isset($obj['story']) || empty($obj['story'])) return null;
        if (!file_exists(SB_STORY_DIR . $obj['story'])) return null;
        return $Core->SBReadFile(SB_STORY_DIR . $obj['story']);
    }
    
    function SaveStory($file=null, $text=null) {
        global $Core;
        if (empty($text)) $text = "<!-- text content deleted by admin -->";
        if (!empty($file)) {
            return $Core->WriteFile(SB_STORY_DIR.$file, $text, 1);
        }
        return 0;
    }
    
    function AddButton($str, $js=null)
    {
        $index = count($this->buttons);
        $this->buttons[$index]['value'] = $str;
        $this->buttons[$index]['js'] = $js;
    }

    function BuildTabs()
    {
        global $Core;
        if (!count($this->tabs)) return null;
        
        $ListItems = null;
        for ($i=0; $i<count($this->tabs); $i++)
		{
			$ListItems .= $Core->HTML->MakeElement(
				'li', 
				$this->tabs[$i]['attrs'], 
				$Core->HTML->MakeElement(
					'a', 
					array('href'=>$this->tabs[$i]['link']), 
					$this->tabs[$i]['text']
				)
			);
		}
		return $Core->HTML->MakeElement(
		    'ul',
		    array('id'=>'section-tabs'),
		    $ListItems
		);
    }
    
    function BuildButtons()
    {
        if ($this->showcancel == 1)
        {
            $this->AddButton('Cancel');
        }
        $buttons = null;
        for ($i=0; $i<count($this->buttons); $i++)
        {
            $js       = $this->buttons[$i]['js'];
            $value    = $this->buttons[$i]['value'];
            $id = strtolower(str_replace(' ', '', $value));
            $buttons .= '<input class="wymupdate button" '.
                        'type="submit" name="submit" '.
                        'value="'.$value.'" '.$js.' />'.
                        "\r\n";
        }
        $buttons .= $this->rangefields;
        return $buttons;
    }
    
    function test()
    {
        echo '<h2>Manager Class: loaded successfully</h2>';
    }
}

?>