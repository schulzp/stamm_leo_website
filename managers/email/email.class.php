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

class email extends manager
{
    function __construct() 
    {
        $this->Init();
    }
    
    function email()
    {
        $this->__construct();
    }
    
    function InitProps() 
    {
        $this->SetProp('headings', array('Name', 'Tasks'));
        $this->SetProp('tasks', array('view', 'delete'));
        $this->SetProp('cols', array('name'));
    }
    
    function Trigger()
    {
        global $Core;
        switch ($this->button) 
        {
            case 'view':
            case 'viewemail':
                $this->InitSkin();
                $this->InitEditor();
                $this->Edit();
                break;
        
            case 'delete':
            case 'deleteemail':
                if (DEMO_MODE) $Core->ExitDemoEvent($this->redirect);
                $this->DeleteItem();
                break;
                
            case 'back':
            case 'cancel':
                $Core->SBRedirect($this->redirect);
                break;
            
            default: 
                $this->InitProps();
                $this->ViewItems();
                break;
        }
    }
    
    function InitEditor() 
    {
        global $Core;
        
        // Set the form message
        
        $this->SetFormMessage('Email', 'Email');
        
        // Initialize the object properties to empty strings or
        // the properties of the object being edited
        
        $_OBJ = $this->InitObjProps($this->skin, $this->obj);
        
        // This step creates a $form array to pass to buildForm().
        // buildForm() merges the $obj properites with the form HTML.

        $form['ID']      = $this->GetItemID($_OBJ);
        $form['NAME']    = $this->GetFromAddress($this->GetObjProp($_OBJ,'name'));
        $form['CONTENT'] = $this->GetEmailContent($_OBJ,'content');

        $this->MarkAsRead($this->GetObjProp($_OBJ, 'name'));
        $this->BuildForm($form);
    }

    function MarkAsRead($name)
    {
        global $Core;
        if (empty($name) || 
            $name{0} != '~' || 
            !file_exists(SB_SITE_EMAIL_DIR.$name)) 
        {
            return;
        }
        $Core->MoveFile(
            SB_SITE_EMAIL_DIR . $name, 
            SB_SITE_EMAIL_DIR . substr($name, 1)
        );
    }
    
    function GetFromAddress($name)
    {
        if (strpos($name, '.') !== false)
        {
            $bits = explode('.', $name);
            if (count($bits) < 3) return $name;
            return implode('.', array_slice($bits, 0, count($bits)-2));
        }
        return $name;
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
    
    function GetEmailContent()
    {
        global $Core;
        
        $path = SB_SITE_EMAIL_DIR;
        if (!empty($this->obj->name) &&
             file_exists($path.$this->obj->name))
        {
            return $Core->SBReadFile($path.$this->obj->name);
        } else {
            return NULL;
        }
    }
    
    function InitObjs()
    {
        global $Core;
        
        if (DEMO_MODE) {
            $this->objs = array(); 
            return;
        }
        
        
        $path = SB_SITE_EMAIL_DIR;
        if (is_dir($path))
        {
            $files = $Core->ListFilesOptionalRecurse($path, 0, array());
            for ($i=0; $i<count($files); $i++)
            {
                if ($files[$i] != SB_EMAIL_ERROR_LOG)
                {
                    $obj = new stdClass;
                    $obj->id = $i + 1;
                    $obj->name = basename($files[$i]);
                    $this->objs[] = $obj;
                }
            }
        }
    }

    function ViewItems() 
    {
        global $Core;
        
        $this->include_th = 1;
        $this->html = null;

        $this->BeforeViewItems();
        
        $id = 0;
        if (count($this->objs) > 0) 
        {
        
            if (count($this->objs) > SB_MAX_LIST_ROWS)
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
        
            foreach ($this->objs as $obj) 
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
                        $val = $this->GetFromAddress($val);
                    }
                    if ($key == 'name')
                    {
                        if ($val{0} == '~') 
                        {
                            $val = substr($val, 1);
                            $val = "<strong style=\"color: #000;\">$val</strong>";
                        }
                    }
                    $this->html .= 
                        "<td valign=\"middle\" class=\"$class\">$val</td>\n";
                }

                $this->html .= "<td style=\"width: " . TASK_COL_WIDTH . "px;\" valign=\"middle\" class=\"$class\">\n";
                for ($i=0; $i<count($this->tasks); $i++)
                {
                    $this->html .= '<a href="'.$this->redirect.
                                   '&amp;sub='.$this->tasks[$i].$this->objtype.
                                   '&amp;id='.$obj->id;
                    if (strToLower($this->tasks[$i]) == 'delete')
                    {
                        $name = $this->cols[0];
                        $prop = 'item';
                        if (isset($obj->name) && !empty($obj->name))
                        {
                            $prop = $obj->name;
                        }
                        else if (isset($obj->title) && !empty($obj->title))
                        {
                            $prop = $obj->title;
                        }
                        $this->html .= '" onclick="'.
                                       str_replace('{name}', $prop, sCONFIRM_DELETE_JS);
                    }
                    $this->html .= '">'.ucwords($this->tasks[$i]);
                    $this->html .= '</a>';
                }
                $this->html .= "</tr>\n";    
                $id++;
            }
        } 
        else 
        {
            $this->html .= 
            "<tr>\n".
            "<td colspan=\"3\" width=\"100%\">".
            (
              DEMO_MODE
              ? "This feature has been disabled in the SkyBlueCanvas demo." 
              : SB_NO_ITEMS_TO_DISPLAY
            ) .
            "</td>\n".
            "</tr>\n";
        }
    }
    
    function LoadObj()
    {
        global $Core;
        for ($i=0; $i<count($this->objs); $i++)
        {
            if ($this->objs[$i]->id == $this->id)
            {
                $this->obj = $this->objs[$i];
            }
        }
        if (!isset($this->obj->id))
        {
            $this->obj = array();
        }
    }
    
    function InitObjType()
    {
        $this->objtype = 'email';
    }
    
    function InitDataSource()
    {
        return;
    }
    
    function DeleteItem()
    {
        global $Core;
        
        $name = $this->obj->name;
        $file = SB_SITE_EMAIL_DIR.$name;
        if (file_exists($file) && !is_dir($file))
        {
            $Core->ExitEvent(intval(unlink($file)), $this->redirect);
        } else {
            $Core->ExitEvent(0, $this->redirect);
        }
    }
}

?>