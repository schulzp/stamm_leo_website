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

defined('SKYBLUE') or die(basename(__FILE__));

class block extends manager {

    var $menus;
    var $menuitems;
    var $updatesitemap = false;
    var $clear_cache = true;

    public function __construct() {
        global $Core;
        $this->bundles = $Core->xmlHandler->ParserMain(SB_XML_DIR . 'bundle.xml');
        $this->Init();
    }

    function AddEventHandlers() {
        $this->AddEventHandler('OnBeforeLoad','InitObjFilter');
    }

    private function PublishedValueToString() {
        for ($i=0; $i<count($this->objs); $i++)
        {
            $this->objs[$i]->published = $this->objs[$i]->published == 1 ? 'Yes' : 'No' ;
        }
    }

    private function MenuIdToString() {
        global $Core;

        $menus = $Core->xmlHandler->ParserMain(SB_MENUS_FILE);
        for ($i=0; $i<count($this->objs); $i++)
        {
            $obj = &$this->objs[$i];
            $m = $Core->SelectObj($menus, $obj->menu);
            $obj->menu = isset($m->title) ? $m->title : null;
        }
    }

    function InitProps() {
        $this->SetProp('headings', array('Name', 'Block Type', 'Published', 'Menu', 'Tasks'));
        $this->SetProp(
            'tasks', 
            array(
                'up', 
                'down',
                TASK_SEPARATOR,
                'publish',
                TASK_SEPARATOR, 
                'edit', 
                'delete'
            )
        );
        $this->SetProp('cols', array('name', 'blocktype', 'published', 'menu'));
    }

    function InitObjFilter() {
        $this->SetProp('filter', 'show');
        $this->SetProp('filterprop', 'blocktype');
    }

    function DefineConstants() {
        global $Core;
    }

    function clearCache() {
        global $Core;
        $Cache = new Cache("");
        if ($Cache->clearAll()) {
            $message = "The cache has been cleared"; 
            $class = 'confirm';
        }
        else {
            $message = "Not all cache files were cleared"; 
            $class = 'error';
        }
        $Core->SetSessionMessage($message, $class);
        $Core->SBRedirect(MGR_DEFAULT_REDIRECT);
    }

    function Trigger() {
        global $Core;

        switch ($this->button) {
        case 'add':
        case 'edit':
        case 'editblock':
            $this->AddButton('Save');
            $this->InitSkin();
            $this->InitEditor();
            $this->Edit();
            break;
        case 'clearcache':
            $this->clearCache();
            break;
        case 'save':
            if (DEMO_MODE) {
                $Core->ExitDemoEvent($this->redirect);
            }
            $this->PrepareForSave();
            $this->SaveItems();
            $Core->UpdateSitemap();
            break;

        case 'delete':
        case 'deleteblock':
            if (DEMO_MODE) {
                $Core->ExitDemoEvent($this->redirect);
            }
            $this->DeleteItem();
            break;

        case 'publish':
        case 'unpublish':
        case 'publishblock':
        case 'unpublishblock':
            if (DEMO_MODE) $Core->ExitDemoEvent($this->redirect);
            $this->publishPage();
            break;

        case 'cancel':
            $Core->ExitEvent(2, $this->redirect);
            break;

        case '':
            $this->filterObjs();
            $this->AddButton('Add');
            $this->AddButton('Clear Cache');
            $this->InitProps();
            $this->PublishedValueToString();
            $this->MenuIdToString();
            $this->ViewItems();
            break;

        default:
            parent::Trigger();
            break;
        }
    }

    private function publishPage() {
        global $Core;

        $this->obj->published = $this->obj->published ? 0 : 1;

        $isUniqueName = $this->isUniqueName();

        if (!$isUniqueName) {
            $this->obj->published = 0;
            $this->obj->isdefault = 0;
            $Core->SetSessionMessage(
                "This block name is not unique. " . 
                "In order for things to work properly, you should " . 
                "choose another name. Your changes have been save " . 
                "but you will not be able to publish this block " . 
                "until it is re-named.",
                'warning'
            );
        }

        $Core->InsertObj($this->objs, $this->obj, 'id');
        $xml = $Core->xmlHandler->ObjsToXML($this->objs, $this->objtype);

        $dataSaved = $Core->WriteFile($this->datasrc, $xml, 1);

        if ($dataSaved && $isUniqueName) {
            $Core->ExitEvent(1, $this->redirect);
        } else if ($dataSaved && !$isUniqueName) {
            $Core->SBRedirect($this->redirect);
        } else {
            $Core->ExitEvent(0, $this->redirect);
        }
    }

    private function PrepareForSave() {
        $this->AddFieldValidation('name','notnull');
        $this->SaveBlockText();
    }

    private function isUniqueName() {
        global $Router;
        $blockName = strtolower($Router->normalize($this->obj->name));
        $first = $this->obj->id;
        foreach ($this->objs as $block) {
            if (strtolower($Router->normalize($block->name)) == $blockName && 
                $block->id != $this->obj->id) 
            {
                if ($block->id < $first) {
                    $first = $block->id;
                }
            }
        }
        return ($this->obj->id == $first);
    }

    // @override
    public function SaveItems($redirect) {
        global $Core;

        if (!empty($redirect))
            $this->redirect = $redirect;

        $this->BeforeSave();
    
        foreach ($_POST as $k=>$v) {
            if ($k != 'submit') {
                if (is_array($v)) {
                    for($i=0; $i<count($v); $i++) {
                        $v[$i] = trim($Core->GetVar($v, $i, null));
                    }
                    $val = implode(',', $v);
                } 
                else {
                    $val = trim($Core->GetVar($_POST, $k, null));
                }
                $this->ValidateField($k);
                $arr[$k] = $Core->stripslashes_deep($val);
            }
        }

        $obj = $Core->SelectObj($this->objs, $this->id);
        if (!isset($obj->id) || $obj->id == 0) {
            $obj = $Core->ArrayToObj($obj, $arr);
        } 
        else {
            $obj = $Core->UpdateObjFromArray($obj, $arr);
        }

        $isUniqueName = $this->isUniqueName();
        if (!$isUniqueName) {
            $obj->published = 0;
            $obj->isdefault = 0;
            $Core->SetSessionMessage(
                "This block name is not unique. " . 
                "In order for things to work properly, you should " . 
                "choose another name. Your changes have been save " . 
                "but you will not be able to publish this block " . 
                "until it is re-named.",
                'warning'
            );
        }

        $this->objs = $Core->InsertObj($this->objs, $obj, 'id');

        if (!empty($arr['order'])) {
            $this->objs = $Core->OrderObjs(
                $this->objs, $this->id, $arr['order']
            );
        }
        $xml = $Core->xmlHandler->ObjsToXML($this->objs, $this->objtype);
        $dataSaved = $Core->WriteFile($this->datasrc, $xml, 1);

        if ($dataSaved && $isUniqueName) {
            $Core->ExitEvent(1, $this->redirect);
        }
        else if ($dataSaved && !$isUniqueName) {
            $Core->SBRedirect($this->redirect);
        }
        else {
            $Core->ExitEvent(0, $this->redirect);
        }

    }

    //@override
    public function InitSkin() {
        global $Core;
        $file = str_replace('{objtype}', $this->objtype, SB_SKIN_FILE_PATH);
        $this->skin = FileSystem::read_file($file);
        // $Core->OutputBuffer($file);
    }


    // @override
    protected function InitEditor() {
        global $Core;

        // Set the form message

        $this->SetFormMessage();

        // Initialize the object properties to empty strings or
        // the properties of the object being edited

        $_OBJ = $this->InitObjProps($this->skin, $this->obj);

        // This step creates a $form array to pass to BuildForm().
        // BuildForm() merges the $obj properites with the form HTML.

        $form['ID']            = $this->GetObjID($_OBJ);
        $form['NAME']          = $this->GetObjName($_OBJ);
        $form['TITLE']         = $this->GetObjTitle($_OBJ);
        $form['BLOCKCLASS']    = $this->GetBlockClass($_OBJ);
        $form['MODIFIED']      = $this->GetLastModified($_OBJ);
        $form['ORDER']         = $this->GetObjOrder($_OBJ);
        $form['PAGES']         = $this->GetPages($_OBJ);

        $form['MENU']          = $this->BuildMenuSelector($_OBJ);

        $form['STORY_CONTENT'] = $this->GetStoryContent($_OBJ);
        $form['STORY']         = $this->GetStoryFileName();

        $form['PUBLISHED']     = $this->GetPublished($_OBJ);

        $this->BuildForm($form);
    }

    private function BuildMenuSelector($obj) {
        global $Core;

        if (!isset($obj['menu'])) $obj['menu'] = null;

        $this->GetMenus();

        $options = array();
        $options[] = $Core->MakeOption(' -- Select Menu -- ', null);
        $options[] = $Core->MakeOption('No Menu', null, null);
        foreach ($this->menus as $m)
        {
            $s = $m->id == $obj['menu'] ? 1 : 0 ;
            $options[] = $Core->MakeOption($m->title, $m->id, $s);
        }
        return $Core->SelectList($options, 'menu');
    }

    private function GetMenus() {
        global $Core;
        
        if (!file_exists(SB_MENU_GRP_FILE))
        {
            $this->menus = array();
        } else {
            $this->menus = $Core->xmlHandler->ParserMain(SB_MENU_GRP_FILE);
        }
    }

    function GetPages($obj) {
        global $Core;

        if (!isset($obj['pages'])) $obj['pages'] = '';

        $pages     = array();
        if (file_exists(SB_PAGE_FILE))
            $pages = $Core->xmlHandler->ParserMain(SB_PAGE_FILE);

        $selectedIds = split(',', $obj['pages']);

        $options   = array();
        $options[] = $Core->MakeOption('- Everywhere -', 0, $selectedIds[0] == '0');

        foreach($pages as $p) {
            $s = in_array($p->id, $selectedIds);
            $options[] = $Core->MakeOption($p->name, $p->id, $s);
        }

        return $Core->SelectList($options, 'pages[]', 5, 'multiple="true"');
    }

    private function GetLastModified($obj) {
        if (!isset($obj['modified']) || empty($obj['modified']))
            $obj['modified'] = date('Y-m-d\TH:i:s+00:00',time());

        return $obj['modified'];
    }
 
    private function GetObjID($obj) {
        global $Core;
        if (!isset($obj['id']))
            $obj['id'] = $Core->GetNewID($this->objs);

        return $obj['id'];
    }

    private function GetObjName($obj) {
        if (!isset($obj['name']))
            $obj['name'] = 'Untitled';

        return $obj['name'];
    }

    public function GetObjOrder($obj) {
        global $Core;
        if (!isset($obj['title'])) 
            $obj['name'] = null;

        return $Core->OrderSelector2($this->objs, 'name', $obj['name']);
    }

    private function GetObjTitle($obj) {
        if (!isset($obj['title']))
            $obj['title'] = 'No Title Set';

        return $obj['title'];
    }

    private function GetBlockClass($obj) {
        if (!isset($obj['blockclass']))
            $obj['blockclass'] = null;

        return $obj['blockclass'];
    }

    private function GetPublished($obj) {
        global $Core;
        if (!isset($obj['published']) || empty($obj['published']))
            $obj['published'] = 0;
        
        return $Core->YesNoList('published', $obj['published']);
    }

    private function SaveBlockText() {
        global $Core;
        $this->SaveStory(
            $Core->GetVar(
                $_POST,'story', 
                $this->GetStoryFileName()
            ), 
            trim(stripslashes(urldecode($_POST['story_content'])))
        );

        $_POST['modified'] = date('Y-m-d\TH:i:s+00:00',time());
        unset($_POST['block_content']);
    }
    
    public function SetFormMessage() {
        global $Core;

        if ($this->button == 'edit' ||
            $this->button == 'editblock' || 
            $this->button == 'add' ||
            $this->button == 'addblock')
        {
            $itemtitle = isset($this->obj->name) ? $this->obj->name : 'New Page' ;
            if (isset($this->obj->id) && !empty($this->obj->id))
            {
                $itemtitle .= ' (ID: '.$this->obj->id.')';
            }
            $Core->MSG = str_replace('{msg}', $itemtitle, SB_MSG_EDIT);
        }
    }
}

?>
