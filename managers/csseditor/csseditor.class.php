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

class csseditor extends manager
{

    var $mustUpdate = FALSE;
    var $gid = 2;
    
    function __construct() 
    {
        if (SB_GID < $this->gid)
        {
            echo SB_NOT_ENOUGH_PRIVILEGES;
        }
        else
        {
            $this->Init();
        }
    }
    
    function csseditor()
    {
        $this->__construct();
    }

    function InitProps() 
    {
        $this->SetProp('headings', array('Name', 'Tasks'));
        $this->SetProp('cols', array('name'));
        # $this->SetProp('tasks', array('edit', 'delete'));
        if (count($this->objs) > 1)
        {
            $this->SetProp('tasks', array('edit', 'delete'));
        }
        else
        {
            $this->SetProp('tasks', array('edit'));
        }
    }
        
    function Trigger()
    {
        global $Core;
        switch ($this->button) 
        {
            case 'add':
            case 'edit':
            case 'addcss':
            case 'editcss':
                $this->AddButton('Save');
                $this->InitSkin();
                $this->InitEditor();
                $this->Edit();
                break;
                
            case 'save':
            case 'savecss':
                if (DEMO_MODE) $Core->ExitDemoEvent($this->redirect);
                $this->SaveItems();
                break;
                
            case 'delete':
            case 'deletecss':
                if (DEMO_MODE) $Core->ExitDemoEvent($this->redirect);
                $this->DeleteItem();
                break;
                
            case 'cancel':
                $Core->ExitEvent(2, $this->redirect);
                break;
                
            default: 
                $this->AddButton('Add');
                $this->InitProps();
                $this->ViewItems();
                break;
        }
    }
    
    function InitSkin()
    {
        global $Core;
        
        $file = str_replace('{objtype}', 'csseditor', SB_SKIN_FILE_PATH);
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
        
        if ($this->button == 'edit' || $this->button == 'add')
        {
            $itemtitle = isset($this->obj->name) ? $this->obj->name : 'New Style Sheet' ;
            $Core->MSG = '<h2 class="message">'.$itemtitle.'</h2>';
        }
        
        // Initialize the object properties to empty strings or
        // the properties of the object being edited
        
        $_OBJ = $this->InitObjProps($this->skin, $this->obj);
        
        foreach ($this->obj as $k=>$v)
        {
            if (!isset($_OBJ[$k]) || empty($_OBJ[$k]))
            {
                $_OBJ[$k] = $v;
            }
        }
        
        // This step creates a $form array to pass to BuildForm().
        // BuildForm() merges the $obj properites with the form HTML.
        
        $form['NAME'] = !empty($_OBJ['name']) ? $_OBJ['name'] : 'untitled.css' ;
        $form['TEXT'] = $this->GetCSSText();
        $form['PATH'] = $this->GetObjProp($_OBJ, 'path', ACTIVE_SKIN_CSS_DIR.'untitled.css');
        
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
    
    function GetCSSText()
    {
        global $Core;
        
        if (!empty($this->obj->path) && file_exists($this->obj->path))
        {
            return $Core->SBReadFile($this->obj->path);
        } else {
            return null;
        }
    }
    
    function InitObjs()
    {
        global $Core;
        
        $path = ACTIVE_SKIN_CSS_DIR;
        if (is_dir($path))
        {
            $files = $Core->ListFilesOptionalRecurse(ACTIVE_SKIN_CSS_DIR, 0, array());
            for ($i=0; $i<count($files); $i++)
            {
                $obj = new stdClass;
                $obj->id = $i + 1;
                $obj->name = basename($files[$i]);
                $obj->path = $files[$i];
                $this->objs[] = $obj;
            }
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
        $this->objtype = 'css';
    }
    
    function InitDataSource()
    {
        return;
    }
    
    function DeleteItem()
    {
        global $Core;
        
        $file = $this->obj->path;
        if (file_exists($file) && !is_dir($file))
        {
            $Core->ExitEvent(intval(unlink($file)), $this->redirect);
        } else {
            $Core->ExitEvent(0, $this->redirect);
        }
    }
    
    function SaveItems() 
    {
        global $Core;
        
        $name = $Core->GetVar($_POST, 'name', null);
        $path = $Core->GetVar($_POST, 'path', ACTIVE_SKIN_CSS_DIR.'untitled.css');
        if ($Core->GetVar($_POST, 'directory', null) != null)
        {
            $path = ACTIVE_SKIN_DIR.$Core->GetVar($_POST, 'directory', 'css').'/'.$name;
        }
        if (isset($_POST['text']) && !empty($_POST['text']))
        {
            $text = $_POST['text'];
        }
        else
        {
            $text = '/* CSS RULES */';
        }
 
        $text = str_replace('\\', null, $text);
        
        if (!empty($path) && !empty($text))
        {
            $path = $this->AddFileExtension($path, 'css');
            $Core->ExitEvent($Core->WriteFile($path, $text, 1), $this->redirect);
        }
    }
    
    function AddFileExtension($name, $ext)
    {
        $name = strtolower($name);
        $ext  = strtolower($ext);
        $bits = explode('.', $name);
        if ($bits[count($bits)-1] !== $ext)
        {
            $bits[] = $ext;
        }
        return implode('.', $bits);
    }

    // END FUNC()
    
}

?>
