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

class configuration extends manager
{

    function __construct() 
    {
        $this->Init();
    }
    
    function configuration()
    {
        $this->__construct();
    }
    
    function AddEventHandlers()
    {
        $this->AddEventHandler('OnBeforeShow', 'HideCancel');
    }
    
    function HideCancel()
    {
        $this->SetProp('showcancel', 0);
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
                $this->SaveItems();
                break;
                
            case 'reset':
                $this->Cancel(9);
                break;
                
            case 'delete':
            case 'deletecontacts':
            case 'cancel':
            case 'add':
            case 'edit':
            case 'addactiveskin':
            case 'editactiveskin':
            default:
                $this->AddButton('Save');
                $this->AddButton('Reset');
                $this->InitSkin();
                $this->InitEditor();
                $this->Edit();
                break;
        }
    }
    
    function InitSkin()
    {
        global $Core;
        
        $file = str_replace('{objtype}', 'configuration', SB_SKIN_FILE_PATH);
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
            $Core->MSG = null;
        }
        
        // Initialize the object properties to empty strings or
        // the properties of the object being edited
        
        $_OBJ = $this->InitObjProps($this->skin, $this->obj);
        
        // This step creates a $form array to pass to BuildForm().
        // BuildForm() merges the $obj properites with the form HTML.

        $form['ID']                = $this->GetItemID($_OBJ);
        $form['SITE_NAME']         = $this->GetObjProp($_OBJ, 'site_name',       null);
        $form['SITE_URL']          = $this->GetObjProp($_OBJ, 'site_url',        null);
        $form['SITE_SLOGAN']       = $this->GetObjProp($_OBJ, 'site_slogan',     null);
        $form['CONTACT_NAME']      = $this->GetObjProp($_OBJ, 'contact_name',    null);
        $form['CONTACT_TITLE']     = $this->GetObjProp($_OBJ, 'contact_title',   null);
        $form['CONTACT_ADDRESS']   = $this->GetObjProp($_OBJ, 'contact_address', null);
        $form['CONTACT_ADDRESS_2'] = $this->GetObjProp($_OBJ, 'contact_address_2', null);
        $form['CONTACT_CITY']      = $this->GetObjProp($_OBJ, 'contact_city',    null);
        $form['CONTACT_STATE']     = $this->GetObjProp($_OBJ, 'contact_state',   null);
        $form['CONTACT_ZIP']       = $this->GetObjProp($_OBJ, 'contact_zip',     null);
        $form['CONTACT_EMAIL']     = $this->GetObjProp($_OBJ, 'contact_email',   null);
        $form['CONTACT_PHONE']     = $this->GetObjProp($_OBJ, 'contact_phone',   null);
        $form['CONTACT_FAX']       = $this->GetObjProp($_OBJ, 'contact_fax',     null);

        $form['SITE_EDITOR']     = $this->GetEditorSelector(
            $this->GetObjProp($_OBJ, 'site_editor', 'wymeditor')
       );
       
       $form['USE_CACHE'] = $Core->YesNoList(
           'use_cache',
           $this->GetObjProp($_OBJ, 'use_cache', 0)
       );
        
        $this->BuildForm($form);
    }
    
    function GetEditorSelector($editor)
    {
        global $Core;
        $editors = $Core->ListDirsOptionalRecurse(SB_PLUGIN_DIR . "editors/", 0);
        $opts = array();
        for ($i=0; $i<count($editors); $i++)
        {
            $ed = basename($editors[$i]);
            $s = $ed == $editor ? 1 : 0;
            $opts[] = $Core->MakeOption($ed, $ed, $s);
        }
        return $Core->SelectList($opts, 'site_editor');
    }
    
    function GetLanguageSelector($language)
    {
        global $Core;
        $langs = $Core->ListDirsOptionalRecurse(SB_LANG_DIR, 0);
        $opts = array();
        for ($i=0; $i<count($langs); $i++)
        {
            $lang = basename($langs[$i]);
            $s = $lang == $language ? 1 : 0;
            $opts[] = $Core->MakeOption($lang, $lang, $s);
        }
        return $Core->SelectList($opts, 'site_language');
        $Core->Dump($langs);
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
    
    function LoadObj()
    {
        if (count($this->objs))
        {
            $obj = $this->objs[0];
        } else {
            $obj = null;
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