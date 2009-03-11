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

define('NO_META_STRING', 
    'To add meta data, use the Meta Manager in the collections section');

class page extends manager
{

    var $menus;
    var $menuitems;
    var $updatesitemap = true;
    var $clear_cache = true;

    function __construct() 
    {
        global $Core;
        $this->bundles = $Core->xmlHandler->ParserMain(SB_XML_DIR . 'bundle.xml');
        $this->Init();
    }
    
    function page() {
        $this->__construct();
    }

    function AddEventHandlers() {
        $this->AddEventHandler('OnBeforeLoad','InitObjFilter');
        // $this->AddEventHandler('OnBeforeSave', 'PrepareForSave');
    }

    function PublishedValueToString()
    {
        for ($i=0; $i<count($this->objs); $i++)
        {
            $this->objs[$i]->published = $this->objs[$i]->published == 1 ? 'Yes' : 'No' ;
        }
    }
    
    function MenuIdToString()
    {
        global $Core;
        
        $menus = $Core->xmlHandler->ParserMain(SB_MENUS_FILE);
        for ($i=0; $i<count($this->objs); $i++)
        {
            $obj = &$this->objs[$i];
            $m = $Core->SelectObj($menus, $obj->menu);
            $obj->menu = isset($m->title) ? $m->title : null;
        }
    }
    
    function InitProps() 
    {
        $this->SetProp('headings', array('Name', 'Page Type', 'Published', 'Menu', 'Tasks'));
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
        $this->SetProp('cols', array('name', 'pagetype', 'published', 'menu'));
    }
    
    function InitObjFilter()
    {
        $this->SetProp('filter', 'show');
        $this->SetProp('filterprop', 'pagetype');
    }
    
    function DefineConstants()
    {
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
        $Core->SBRedirect("admin.php?mgroup=pages&mgr=page");
    }
    
    function Trigger()
    {
        global $Core;
        switch ($this->button) 
        {
            case 'add':
            case 'edit':
            case 'editpage':
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
            case 'deletepage':
                if (DEMO_MODE) {
                    $Core->ExitDemoEvent($this->redirect);
                }
                $this->NoDeleteDefaultPage();
				$this->DeleteMenuBundleEntry();
				$this->DeleteItem();
                break;
                
            case 'publish':
            case 'unpublish':
            case 'publishpage':
            case 'unpublishpage':
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
    
    function NoDeleteDefaultPage()
    {
        global $Core;
        if ($this->obj->isdefault)
		{
			$Core->ExitWithErrorMessage(
				"The page you tried to delete is set as your home (default) page. " . 
				"The home page cannot be deleted. If you want to delete this " . 
				" page you must first set another page as your default page, then " .
				" delete this one", 
				$this->redirect
			);
		}
    }
    
    function publishPage() {
        global $Core;
        
        if ($this->obj->published) {
            if ($this->obj->isdefault) {
				$Core->ExitWithErrorMessage(
				    "The page you tried to un-publish is set as your home (default) page. " . 
				    "The home page cannot be un-published. If you want to un-publish this " . 
				    " page you must first set another page as your default page, then " .
				    " un-publish this one", 
				    $this->redirect
				);
			}
            $this->obj->published = 0;
        }
        else {
            $this->obj->published = 1;
        }
        
        $isUniqueName = $this->isUniqueName();
        if (!$isUniqueName) {
            $this->obj->published = 0;
            $this->obj->isdefault = 0;
			$Core->SetSessionMessage(
				"This page name is not unique. " . 
				"In order for page routing to work properly, you should " . 
				"choose another name. Your changes have been save " . 
				"but you will not be able to publish this page " . 
				"until it is re-named.",
				'warning'
			);
        }
        
        $Core->InsertObj($this->objs, $this->obj, 'id');
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
    
    function PrepareForSave() {
        $this->AddFieldValidation('name','notnull');
        $this->SetDefaultPage();
        $this->SaveStoryText();
        $this->UpdateMenuPublishing();
        $this->SaveCollections();
    }
    
    function isUniqueName() {
        global $Router;
        $pageName = strtolower($Router->normalize($this->obj->name));
        $first = $this->obj->id;
        foreach ($this->objs as $page) {
            if (strtolower($Router->normalize($page->name)) == $pageName && 
                $page->id != $this->obj->id) 
            {
                if ($page->id < $first) {
                    $first = $page->id;
                }
            }
        }
        return ($this->obj->id == $first);
    }
    
    function SaveItems($redirect='') {
        global $Core;
        
        if (!empty($redirect)) {
            $this->redirect = $redirect;
        }
        
        $this->BeforeSave();
        
        if ($this->updatesitemap) {
            $Core->UpdateSitemap();
        }
        
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
				"This page name is not unique. " . 
				"In order for page routing to work properly, you should " . 
				"choose another name. Your changes have been save " . 
				"but you will not be able to publish this page " . 
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
    
    function SaveCollections()
    {
        global $Core;
        $bundles = isset($_POST['modules']) ? $_POST['modules'] : array() ;
        $regions = isset($_POST['regions']) ? $_POST['regions'] : array() ;
        unset($_POST['modules']);
        unset($_POST['regions']);
        
        for ($i=0; $i<count($this->bundles); $i++)
        {
            $obj = $this->bundles[$i];
			$objPage = $obj->page;
			$objPages = explode(',', $objPage);
			$objPages = $Core->TrimArrayItems($objPages);
			
			$objRegion = $obj->region;
			$objRegions = explode(',', $objRegion);
			$objRegions = $Core->TrimArrayItems($objRegions);
			
			$newPages = array();
			$newRegions = array();
			for ($x=0; $x<count($objPages); $x++)
			{
				if ($objPages[$x] != $this->id && 
					$objPages[$x] != '!'.$this->id)
				{
					array_push($newPages, $objPages[$x]);
					array_push($newRegions, $objRegions[$x]);
				}
			}
			for ($j=0; $j<count($bundles); $j++)
			{
				$bundle =& $bundles[$j];
				if (!isset($regions[$bundle])) continue;
				$region = $regions[$bundle];
                $bits = explode('.', $bundle);
                $id = $bits[1];
				if (isset($regions[$bundle]) && $obj->id == $id)
				{
					$region = $regions[$bundle];
					$bits = explode('.', $bundle);
					$id = $bits[1];
					array_push($newPages, $this->id);
					array_push($newRegions, $region);
					$obj->published = 1;
				}
			}
			$obj->page = implode(',', $newPages);
			$obj->region = implode(',', $newRegions);
			$this->bundles = $Core->InsertObj($this->bundles, $obj, 'id');
        }
        
        $xml = $Core->xmlHandler->ObjsToXML($this->bundles, 'bundle');
	    $Core->WriteFile(SB_BUNDLE_FILE, $xml, 1);
    }
    
    function InitSkin()
    {
        global $Core;
        $file = str_replace('{objtype}', $this->objtype, SB_SKIN_FILE_PATH);
        $this->skin = FileSystem::read_file($file);
        // $Core->OutputBuffer($file);
    }
    
    function InitEditor() 
    {
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
        $form['USESITENAME']   = $this->GetUseSiteName($_OBJ);
        $form['METAGROUP']     = $this->GetMetaGroup($_OBJ);
        $form['PAGETYPE']      = $this->GetPageType($_OBJ);
        $form['MODIFIED']      = $this->GetLastModified($_OBJ);
        $form['ORDER']         = $this->GetObjOrder($_OBJ);
        
        $form['MENU']          = $this->BuildMenuSelector($_OBJ);
        $form['PARENT']        = $this->GetObjParent($_OBJ);
        
        $form['STORY_CONTENT'] = $this->GetStoryContent($_OBJ);
        $form['STORY']         = $this->GetStoryFileName();
        
        $form['KEYWORDS']      = $this->GetObjProp($_OBJ, 'keywords', null);
        $form['ISDEFAULT']     = $this->GetIsDefault($_OBJ);
        $form['META_DESCRIPTION'] = $this->GetObjProp($_OBJ, 'meta_description', null);
        
        $form['PUBLISHED']     = $this->GetPublished($_OBJ);
        
        $form['SYNDICATE']     = $this->GetSyndicated($_OBJ);
        
        $form['COLLECTIONS'] = $this->GetCollectionsTable();

        $this->BuildForm($form);
    }
    
    function GetCollectionsTable()
    {
        global $Core;
        
        $collections = null;
        
        for ($i=0; $i<count($this->bundles); $i++)
        {
            $bundle =& $this->bundles[$i];
			
			$page = $bundle->page;
			$pages = explode(',', $page);
			$pages = $Core->TrimArrayItems($pages);
			
			$region = $bundle->region;
			$regions = explode(',', $region);
			$regions = $Core->TrimArrayItems($regions);
			
			$attrs1 = array(
				'type'  => 'checkbox',
				'name'  => "modules[]",
				'value' => "bundle.{$bundle->id}"
			);

			if (!empty($this->id) && in_array($this->id, $pages))
			{
			    $attrs1['checked'] = 'checked';
			}
			
			$selectedRegion = null;
			for ($x=0; $x<count($pages); $x++)
			{
			    if (empty($this->id) || 
			        !isset($regions[$x]) || 
			        empty($regions[$x]))
			    { continue; }
			    if ($pages[$x] == $this->id && isset($regions[$x]))
			    {
			        $selectedRegion = $regions[$x];
			    }
			}
        
            $collections .= $Core->HTML->MakeElement(
                'tr',
                array(),
                $Core->HTML->MakeElement(
                    'td',
                    array(),
                    $Core->HTML->MakeElement(
                        'input',
                        $attrs1
                    ) . '&nbsp;' . $bundle->name
                ) . 
                $Core->HTML->MakeElement(
                    'td',
                    array(),
                    $this->RegionSelector(
                        "regions[bundle.{$bundle->id}]",
                        $this->GetRegions(),
                        $selectedRegion
                    )
                )
            );
        }
        return $collections;
    }
    
    function RegionSelector($inputName, $regions, $selected=null)
    {
        global $Core;
        
        $selector = null;
        
        if (count($regions))
        {
            $options = array(
                $Core->MakeOption(' -- Select Region -- ', null, 0)
            );
            for ($i=0; $i<count($regions); $i++)
            {
                $s = $regions[$i] == $selected ? 1 : 0 ;
                $rname = str_replace(array('{region:','}'),null,$regions[$i]);
                $rname = ucwords($rname);
                $options[] = $Core->MakeOption($rname, $regions[$i], $s);
            }
            $selector = $Core->SelectList($options, $inputName);
        }
        return $selector;
    }
    
    function GetRegions()
    {
        global $Core;
        
        $regions = array();
        $skins   = array();
        
        $path  = ACTIVE_SKIN_DIR;
        $skins = $Core->ListFilesOptionalRecurse($path, 0);
        
        if (count($skins))
        {
            for ($i=0; $i<count($skins); $i++)
            {
                $these = $Core->GetPageRegions(
                    FileSystem::read_file($skins[$i])
                );
                for ($j=0; $j<count($these); $j++)
                {
                    if (in_array($these[$j], $regions)) continue;
                    array_push($regions, $these[$j]);
                }
            }
        }
        return $regions;
    }
    
    function GetAllPageRegions()
    {
        global $Core;
        
        $regions = array();
        
        $skins = $Core->ListFilesOptionalRecurse(
                     ACTIVE_SKIN_DIR, 0 
                    );
        if (count($skins))
        {
            $regions = $this->GetRegions($skins);
        }
        return $regions;
    }
    
    function GetRegionSelector($selected=null)
    {
        global $Core;
        
        $selector = null;
        
        $opts = array($Core->MakeOption(' -- Choose Region -- ', ''));
        
        for ($i=0; $i<count($regions); $i++)
        {
            $s = 0;
            array_push($opts, $Core->MakeOption($regions[$i], $regions[$i], $s));
        }
        return $Core->SelectList($opts, 'regions');
    }
    
    function BuildMenuSelector($obj)
    {
        global $Core;
        
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
        
    function BuildMetaGroupSelector($group)
    {
        global $Core;
        global $config;
        
        $myGroups = explode(',', $group);
        for ($i=0; $i<count($myGroups); $i++)
        {
            $myGroups[$i] = trim($myGroups[$i]);
        }
        
        $groups = array();
        if (file_exists(SB_META_GRP_FILE))
        {
            $groups = $Core->xmlHandler->ParserMain(SB_META_GRP_FILE);
        }
        $selector  = '<ul>'."\r\n";
        if (count($groups))
        {
			foreach ($groups as $grp)
			{
				$selector .= '<li>'."\r\n";
				$selector .= '<input ';
				$selector .= 'type="checkbox" ';
				$selector .= 'name="metagroup[]" ';
				$selector .= 'value="'.$grp->id.'" ';
				if (in_array($grp->id, $myGroups))
				{
					$selector .= 'checked="checked" ';
				}
				$selector .= '/>';
				$selector .= '&nbsp;'.$grp->name."\r\n";
				$selector .= '</li>'."\r\n";
			}
			$selector .= '</ul>'."\r\n";
        }
        else
        {
            $selector = NO_META_STRING;
        }
        return $selector;
    }
    
    function GetIsDefault($obj)
    {
        global $Core;
        if (!isset($obj['isdefault']) ||
             empty($obj['isdefault']))
        {
            $obj['isdefault'] = 0;
        }
        return $Core->YesNoList('isdefault', $obj['isdefault']);
    }
        
    function GetLastModified($obj)
    {
        if (!isset($obj['modified']) || empty($obj['modified']))
        {
            $obj['modified'] = date('Y-m-d\TH:i:s+00:00',time());
        }
        return $obj['modified'];
    }
    
    function GetMenus()
    {
        global $Core;
        
        if (!file_exists(SB_MENU_GRP_FILE))
        {
            $this->menus = array();
        } else {
            $this->menus = $Core->xmlHandler->ParserMain(SB_MENU_GRP_FILE);
        }
    }
    
    function GetMetaGroup($obj)
    {
        global $Core;
        if (!isset($obj['metagroup']))
        {
            $obj['metagroup'] = null;
        }
        return $this->BuildMetaGroupSelector($obj['metagroup']); 
    }
    
    function GetObjID($obj)
    {
        global $Core;
        if (!isset($obj['id']))
        {
            $obj['id'] = $Core->GetNewID($this->objs);
        }
        return $obj['id'];
    }
    
    function GetObjName($obj)
    {
        if (!isset($obj['name']))
        {
            $obj['name'] = 'Untitled';
        }
        return $obj['name'];
    }
    
    function GetObjOrder($obj)
    {
        global $Core;
        return $Core->OrderSelector2($this->objs, 'name', $obj['name']);
    }
    
    function GetObjParent($obj)
    {
        global $Core;
        
        if (!isset($obj['parent']))
        {
            $obj['parent'] = null;
        }
        
        $options   = array();
        $options[] = $Core->MakeOption(' -- Select Parent -- ', null, null);
        $options[] = $Core->MakeOption('No Parent', null, null);
        foreach($this->objs as $p)
        {
            $thisObj = isset($this->obj->id) ? $this->obj->id : null;
            if ($p->id != $thisObj)
            {
                $s = $p->id == $obj['parent'] ? 1 : 0 ;
                $options[] = $Core->MakeOption($p->name, $p->id, $s);
            }
        }
        return $Core->SelectList($options, 'parent');
    }
    
    function GetObjProp($obj, $prop, $default)
    {
        if (isset($obj[$prop]) && !empty($obj[$prop]))
        {
            $val = $obj[$prop];
        } else {
            $val = $default;
        }
        return $val;
    }
    
    function GetObjTitle($obj)
    {
        if (!isset($obj['title']))
        {
            $obj['title'] = 'No Title Set';
        }
        return $obj['title'];
    }
    
    function GetPageType($obj)
    {
        if (!isset($obj['pagetype']))
        {
            $obj['pagetype'] = null;
        }
        return $this->GetTypeSelector($obj['pagetype']);
    }
    
    function GetPublished($obj)
    {
        global $Core;
        if (!isset($obj['published']) ||
             empty($obj['published']))
        {
            $obj['published'] = 0;
        }
        return $Core->YesNoList('published', $obj['published']);
    }
    
    function GetSyndicated($obj)
    {
        global $Core;
        if (!isset($obj['syndicate']) ||
             empty($obj['syndicate']))
        {
            $obj['syndicate'] = 0;
        }
        return $Core->YesNoList('syndicate', $obj['syndicate']);
    }
        
    function GetTypeSelector($selected=null)
    {
        global $Core;
        global $config;
        
        $files = $Core->ListFilesOptionalRecurse(ACTIVE_SKIN_DIR, 0);
        
        $options    = array();
        for ($i=0; $i<count($files); $i++) 
        {
            $file = basename($files[$i]);
            $bits = explode('.', $file);
            $type = $bits[1];
            $ext  = $bits[count($bits)-1];
            if ($ext == 'html') 
            {
                $value = strToLower($type);
                $text  = ucwords($type);
                $s = $value == $selected ? 1 : 0 ;
                $options[] = $Core->MakeOption($text, $value, $s);
            }
        }
        return $Core->SelectList($options, 'pagetype', 1);
    }
    
    function GetUseSiteName($obj)
    {
        global $Core;
        if (!isset($obj['usesitename']))
        {
            $obj['usesitename'] = null;
        }
        return $Core->YesNoList('usesitename', $obj['usesitename']);
    }
    
    function ReorderPages($up=true) {
        global $Core;

        $ppos = $cpos = $npos = -1;
        $objs = $this->objs;
        for ($i = 0; $i < count($objs) && $npos == -1; $i++) {
            if ($objs[$i]->id != $this->id) {
                if ($cpos == -1) {
                    $ppos = $i;
                }
                else {
                    $npos = $i;
                }
            }
            else {
                $cpos = $i;
            }
        }

        $order = -1;
        if ($up && $ppos != -1) {
            $order = $ppos + 1;
        }
        elseif (!$up && $npos != -1) {
            $order = $npos + 1;
        }
            
        if ($order > 0) {
            $this->objs = $Core->OrderObjs($this->objs, $this->id, $order);
            $xml = $Core->xmlHandler->ObjsToXML($this->objs, $this->objtype);
            $Core->WriteFile($this->datasrc, $xml, 1);
        }
        $Core->SBRedirect($this->redirect);
    }

    function SaveStoryText()
    {
        global $Core;
        $this->SaveStory(
            $Core->GetVar($_POST,'story', $this->GetStoryFileName()), 
            trim(stripslashes(urldecode($_POST['story_content']))));
        $_POST['modified'] = date('Y-m-d\TH:i:s+00:00',time());
        unset($_POST['story_content']);
    }
        
    function SetDefaultPage()
    {
        global $Core;
        
        $isdefault = $Core->GetVar($_POST, 'isdefault', 0);
        if ($isdefault)
        {
            $this->obj->published = 1;
            for ($i=0; $i<count($this->objs); $i++)
            {
                $this->objs[$i]->isdefault = 0;
            }
        }
    }
    
    function SetFormMessage()
    {
        global $Core;
        
        if ($this->button == 'edit' ||
             $this->button == 'editpage' || 
             $this->button == 'add' ||
             $this->button == 'addpage')
        {
            $itemtitle = isset($this->obj->name) ? $this->obj->name : 'New Page' ;
            if (isset($this->obj->id) && !empty($this->obj->id))
            {
                $itemtitle .= ' (ID: '.$this->obj->id.')';
            }
            $Core->MSG = str_replace('{msg}', $itemtitle, SB_MSG_EDIT);
        }
    }
    
    function DeleteMenuBundleEntry()
    {
        global $Core;
        
        $id = $Core->GetVar($_GET, 'id', null);
        $updateFlag = 0;
        
        if (!empty($id))
        {
            $bundles = array();
            if (file_exists(SB_BUNDLE_FILE))
            {
                $bundles = $Core->xmlHandler->ParserMain(SB_BUNDLE_FILE);
            }
            for ($i=0; $i<count($bundles); $i++)
            {
                if ($bundles[$i]->name == 'Main Menu [ID:1]')
                {
                    $page = $bundles[$i]->page;
                    $pages = explode(',', $page);
                    $pages = $Core->TrimArrayItems($pages);
                    
                    $region = $bundles[$i]->region;
                    $regions = explode(',', $region);
                    $regions = $Core->TrimArrayItems($regions);
                    
                    $newPages = array();
                    $newRegions = array();
                    
                    for ($j=0; $j<count($pages); $j++)
                    {
                        if ($pages[$j] != $id &&
                             $pages[$j] != '!'.$id)
                        {
                            $newPages[] = $pages[$j];
                            $newRegions[] = $regions[$j];
                            $updateFlag = 1;
                        }
                    }
                    $bundles[$i]->page = implode(',', $newPages);
                    $bundles[$i]->region = implode(',', $newRegions);
                }
                
                if ($bundles[$i]->name == 'Top Menu [ID:2]')
                {
                    $page = $bundles[$i]->page;
                    $pages = explode(',', $page);
                    $pages = $Core->TrimArrayItems($pages);
                    
                    $region = $bundles[$i]->region;
                    $regions = explode(',', $region);
                    $regions = $Core->TrimArrayItems($regions);
                    
                    $newPages = array();
                    $newRegions = array();
                    
                    for ($j=0; $j<count($pages); $j++)
                    {
                        if ($pages[$j] != $id &&
                             $pages[$j] != '!'.$id)
                        {
                            $newPages[] = $pages[$j];
                            $newRegions[] = $regions[$j];
                            $updateFlag = 1;
                        }
                    }
                    $bundles[$i]->page = implode(',', $newPages);
                    $bundles[$i]->region = implode(',', $newRegions);
                }
            }
            if ($updateFlag)
            {
                $xml = $Core->xmlHandler->ObjsToXML($bundles, 'bundle');
                $Core->WriteFile(SB_BUNDLE_FILE, $xml, 1);
            }
        }
    }
        
    function UpdateMenuPublishing()
    {
        global $Core;
        
        $id   = $Core->GetVar($_POST, 'id', null);
        $menu = $Core->GetVar($_POST, 'menu', 1);
        
        $updateFlag = 0;
        
        if (!empty($id))
        {
            $bundles = array();
            if (file_exists(SB_BUNDLE_FILE))
            {
                $bundles = $Core->xmlHandler->ParserMain(SB_BUNDLE_FILE);
            }
            
            for ($i=0; $i<count($bundles); $i++)
            {
                if ($bundles[$i]->name == 'Main Menu [ID:1]')
                {
                    $page = $bundles[$i]->page;
                    $pages = explode(',', $page);
                    $pages = $Core->TrimArrayItems($pages);
                    
                    $region = $bundles[$i]->region;
                    $regions = explode(',', $region);
                    $regions = $Core->TrimArrayItems($regions);
                    
                    if (!in_array($id, $pages) &&
                         !in_array('!'.$id, $pages))
                    {
                        $updateFlag = 1;
                        $bundles[$i]->page   .= ','.$id;
                        $bundles[$i]->region .= ',{region:left}';
                    }
                }
                
                if ($bundles[$i]->name == 'Top Menu [ID:2]')
                {
                    $page = $bundles[$i]->page;
                    $pages = explode(',', $page);
                    $pages = $Core->TrimArrayItems($pages);
                    
                    $region = $bundles[$i]->region;
                    $regions = explode(',', $region);
                    $regions = $Core->TrimArrayItems($regions);
                
                    if (!in_array($id, $pages) &&
                         !in_array('!'.$id, $pages))
                    {
                        $updateFlag = 1;
                        $bundles[$i]->page   .= ','.$id;
                        $bundles[$i]->region .= ',{region:top}';
                    }
                }
            }
            
            if ($updateFlag)
            {
                $xml = $Core->xmlHandler->ObjsToXML($bundles, 'bundle');
                $Core->WriteFile(SB_BUNDLE_FILE, $xml, 1);
            }
        }
    }
    
}

?>