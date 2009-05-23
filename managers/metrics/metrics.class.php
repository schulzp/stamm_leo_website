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

class metrics extends manager
{
    function __construct() 
    {
        $this->Init();
    }
    
    function metrics()
    {
        $this->__construct();
    }
    
    function InitProps() 
    {
        $this->SetProp('headings', array('Name', 'Enabled', 'Tasks'));
        $this->SetProp('tasks', array('publish', TASK_SEPARATOR, 'edit', 'delete'));
        $this->SetProp('cols', array('name', 'published'));
    }
    
    function Trigger()
    {
        global $Core;
        switch ($this->button) 
        {
            case 'add':
            case 'edit':
            case 'addmetrics':
            case 'editmetrics':
                $this->AddButton('Save');
                $this->InitSkin();
                $this->InitEditor();
                $this->Edit();
                break;
        
            case 'delete':
            case 'deletemetrics':
                if (DEMO_MODE) $Core->ExitDemoEvent($this->redirect);
                $this->DeleteItem();
                break;
                
            case 'back':
            case 'cancel':
                $Core->SBRedirect($this->redirect);
                break;
            
            case 'save':
            case 'savemetrics':
                if (DEMO_MODE) $Core->ExitDemoEvent($this->redirect);
                $this->SaveItems();
                break;
                
            case 'publish':
            case 'unpublish':
            case 'publishmetrics':
            case 'unpublishmetrics':
                $this->publish();
                break;
                
            default: 
                $this->AddButton('Add');
                $this->InitProps();
                $this->ViewItems();
                break;
        }
    }
    
    function publish()
    {
        global $Core;

        $name = $this->obj->name;
        if ($name{0} == '_')
        {
            $Core->ExitEvent($Core->MoveFile(
				SB_SITE_DATA_DIR . "metrics/$name", 
				SB_SITE_DATA_DIR . "metrics/" . str_replace('_', null, $name)
			), $this->redirect);
        }
        else
        {
            $Core->ExitEvent($Core->MoveFile(
                SB_SITE_DATA_DIR . "metrics/$name",
                SB_SITE_DATA_DIR . "metrics/_$name"
            ), $this->redirect);
        }
    }
    
    function InitEditor() 
    {
        global $Core;
        
        // Set the form message
        
        $this->SetFormMessage('name', 'Metric');
        
        // Initialize the object properties to empty strings or
        // the properties of the object being edited
        
        $_OBJ = $this->InitObjProps($this->skin, $this->obj);
        
        // This step creates a $form array to pass to buildForm().
        // buildForm() merges the $obj properites with the form HTML.
        
        $form['ID']      = $this->GetItemID($_OBJ);
        $form['NAME']    = $this->GetObjProp($_OBJ,'name');
        $form['CONTENT'] = $this->GetmetricsContent($_OBJ,'content');
        
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
    
    function SaveItems() 
    {
        global $Core;
        
        $name = $Core->GetVar($_POST, 'name', NULL);
        if (isset($_POST['content']) && !empty($_POST['content']))
        {
            $text = $_POST['content'];
        }
 
        $text = stripslashes($text);
        
        if (!empty($name) && !empty($text))
        {
            $name = $this->AddFileExtension($name, 'js');
            $file = SB_SITE_METRICS_DIR.$name;
            $Core->ExitEvent($Core->WriteFile($file, $text, 1), $this->redirect);
        }
        else
        {
            $Core->ExitEvent(0, $this->redirect);
        }
    }
    
    function AddFileExtension($name, $ext)
    {
        $bits = explode('.', $name);
        if ($bits[count($bits)-1] !== $ext)
        {
            $bits[] = $ext;
        }
        return implode('.', $bits);
    }
    
    function GetMetricsContent()
    {
        global $Core;
        
        $path = SB_SITE_METRICS_DIR;
        if (!empty($this->obj->name) &&
             file_exists($path.$this->obj->name))
        {
            return stripslashes($Core->SBReadFile($path.$this->obj->name));
        } else {
            return NULL;
        }
    }
    
    function InitObjs()
    {
        global $Core;
        
        $path = SB_SITE_METRICS_DIR;
        if (is_dir($path))
        {
            $files = $Core->ListFilesOptionalRecurse($path, 0, array());
            for ($i=0; $i<count($files); $i++)
            {
                $name = basename($files[$i]);
				$obj = new stdClass;
				$obj->id = $i + 1;
				$obj->name = $name;
				$obj->published = $name{0} == '_' ? 'No' : 'Yes' ;
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
        $this->objtype = 'metrics';
    }
    
    function InitDataSource()
    {
        return;
    }
    
    function DeleteItem()
    {
        global $Core;
        
        $name = $this->obj->name;
        $file = SB_SITE_METRICS_DIR.$name;
        if (file_exists($file) && !is_dir($file))
        {
            $Core->ExitEvent(intval(unlink($file)), $this->redirect);
        } else {
            $Core->ExitEvent(0, $this->redirect);
        }
    }
}

?>