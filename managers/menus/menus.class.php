<?php

/**
* @version        1.1 RC1 2008-11-20 21:18:00 $
* @package        SkyBlueCanvas
* @copyright    Copyright (C) 2005 - 2008 Scott Edwin Lewis. All rights reserved.
* @license        GNU/GPL, see COPYING.txt
* SkyBlueCanvas is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYING.txt for copyright notices and details.
*/

defined('SKYBLUE') or die(basename(__FILE__));

class menus extends manager
{

    function __construct() 
    {
        $this->Init();
    }
    
    function menus()
    {
        $this->__construct();
    }

    function InitProps() 
    {
        $this->SetProp( 'headings', array( 'Name', 'Tasks' ) );
        $this->SetProp( 'cols', array( 'title' ) );
        $this->SetProp( 'tasks', array( 'edit', 'delete' ) );
    }
        
    function Trigger()
    {
        global $Core;
        switch ( $this->button ) 
        {
            case 'add':
            case 'edit':
            case 'addmenus':
            case 'editmenus':
                $this->AddButton('Save');
                $this->InitSkin();
                $this->InitEditor();
                $this->Edit();
                break;
                
            case 'save':
                if (DEMO_MODE) $Core->ExitDemoEvent($this->redirect);
                $this->PrepareForSave();
                $this->SaveItems();
                break;
                
            case 'delete':
            case 'deletemenus':
                if (DEMO_MODE) $Core->ExitDemoEvent($this->redirect);
                if (in_array($this->id,array(1,2)))
                {
                    $Core->ExitWithWarning($this->redirect, MSG_NO_DELETE_MENUS);
                }
                $this->DeleteItem();
                break;
                
            case 'cancel':
                $Core->ExitEvent( 2, $this->redirect );
                break;
                
            default: 
                $this->AddButton('Add');
                $this->InitProps();
                $this->ViewItems();
                break;
        }
    }
    
    function PrepareForSave()
    {
        $this->AddFieldValidation('title','notnull');
    }
    
    function InitSkin()
    {
        global $Core;
        
        $file = str_replace( '{objtype}', 'menus', SB_SKIN_FILE_PATH );
        if ( !file_exists( $file ) )
        {
            $Core->FileNotFound( $file, __LINE__,
                __FILE__.'::InitSkin()'
                );
        } else {
            $this->skin = $Core->OutputBuffer( $file );
        }
    }
    
    function InitEditor() 
    {
        global $Core;

        // Set the form message
        
        if ( $this->button == 'edit' || 
             $this->button == 'editmenus' ||
             $this->button == 'addmenus' ||
             $this->button == 'add' )
        {
            $itemtitle = isset( $this->obj->title ) ? 
                         $this->obj->title : 
                         'New menu' ;
            $Core->MSG = '<h2 class="edit">'.$itemtitle.'</h2>';
        }
        
        // Initialize the object properties to empty strings or
        // the properties of the object being edited
        
        $_OBJ = $this->InitObjProps( $this->skin, $this->obj );
        
        // This step creates a $form array to pass to BuildForm().
        // BuildForm() merges the $obj properites with the form HTML.
        
        $form['ID']        = $this->GetItemID( $_OBJ );
        $form['TITLE']     = $this->GetObjProp( $_OBJ, 'title', NULL );
        $form['SHOWTITLE'] = $this->ShowTitleSelector( $_OBJ );
        $form['MENUTYPE']  = $this->MenuTypeSelector( $_OBJ );
        $form['ORDER']     = $Core->OrderSelector( $this->objs, $_OBJ['title'] );

        $this->BuildForm( $form );
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
    
    function DeleteItem() 
    {
        global $Core;
        
        $Core->RequireID($this->id, $this->redirect);
        
        $obj = $Core->SelectObj($this->objs, $this->id);
        
        $this->objs = $Core->DeleteObj($this->objs, $this->id);
        $xml = $Core->xmlHandler->ObjsToXML($this->objs, $this->objtype);
        
        $new_bundles = array();
        $old_bundles = $Core->xmlHandler->ParserMain(SB_BUNDLE_FILE);
        $name = "{$obj->title} [ID:{$obj->id}]";
        for ($i=0; $i<count($old_bundles); $i++) {
            if ($old_bundles[$i]->name != $name) {
                array_push($new_bundles, $old_bundles[$i]);
            }
        }
        $Core->WriteFile(
            SB_BUNDLE_FILE,
            $Core->xmlHandler->ObjsToXML($new_bundles, "bundle"), // $this->objtype),
            1
        );
        
        $Core->ExitEvent(
            $Core->WriteFile($this->datasrc, $xml, 1), 
            $this->redirect
        );
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
        
        $found = false;
        $bundles = $Core->xmlHandler->ParserMain(SB_BUNDLE_FILE);
        $name = "[ID:{$obj->id}]";
        for ($i=0; $i<count($bundles); $i++) {
            $bundle =& $bundles[$i];
            if ($bundle->bundletype == "menu" && 
                strpos($bundle->name, $name) !== false) 
            {
                $found = true;
                $bundle->name = "{$obj->title} [ID:{$obj->id}]";
                foreach ($obj as $k=>$v) {
                    if (isset($bundle->$k)) {
                        $bundle->$k == $v;
                    }
                }
            }
        }
        if (!$found) {
            $newObj = new stdClass;
            $newObj->id = $Core->GetNewID($bundles);
            $newObj->bundletype = "menu";
            $newObj->name = "{$obj->title} [ID:{$obj->id}]";
            $newObj->page = "";
            $newObj->region = "";
            $newObj->published = 1;
            $newObj->cantarget = 1;
            $newObj->source = "";
            $newObj->engine = "";
            $newObj->loadas = "menu";
            array_push($bundles, $newObj);
        }
        $Core->WriteFile(
			SB_BUNDLE_FILE,
			$Core->xmlHandler->ObjsToXML($bundles, "bundle"),
			1
		);
        
        $xml = $Core->xmlHandler->ObjsToXML($this->objs, $this->objtype);
        $Core->ExitEvent(
            $Core->WriteFile($this->datasrc, $xml, 1), 
            $this->redirect);
    }
    
    function MenuTypeSelector( $obj )
    {
        global $Core;
        
        $menutype = NULL;
        if ( isset( $obj['menutype'] ) &&
             !empty( $obj['menutype'] ) )
        {
            $menutype = $obj['menutype'];
        }
        
        $selector = NULL;
        $options = array();
        $s = $menutype == 'vertical' ? 1 : 0 ;
        $options[] = $Core->MakeOption( 'Vertical List', 'vertical', $s );
        $s = $menutype == 'horizontal' ? 1 : 0 ;
        $options[] = $Core->MakeOption( 'Horizontal Tabs', 'horizontal', $s );
        return $Core->SelectList( $options, 'menutype' );
    }
    
    function ShowTitleSelector( $obj )
    {
        global $Core;
        $showtitle = 0;
        if ( isset( $obj['showtitle'] ) )
        {
            $showtitle = $obj['showtitle'];
        }
    
        $options = array();
        $s = $showtitle == 1 ? 1 : 0 ;
        $options[] = $Core->RadioOption( 'showtitle', 1, 'Yes', $s );
        $s = $showtitle == 0 ? 1 : 0 ;
        $options[] = $Core->RadioOption( 'showtitle', 0, 'No', $s );
        $selector = $Core->RadioSelector( $options );
        return $selector;
    }
    
    // END FUNC()
    
}

?>
