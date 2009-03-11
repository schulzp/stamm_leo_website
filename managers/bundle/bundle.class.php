<?php

/*
- loop through all managers
  if the manager has a corresponding php module, add it to the array
  if the manager has a corresponding xml module, add it to the array
  
- show each module on the Page Manager along with a checkbox
- show a region selector next to each module

---

- capture all selected modules in an array
- capture all regions in an array

---

- when a page is loaded, check its module attr for list of modules
- make sure each module exists
*/

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

class Module {
    var $id;
    var $name;
    var $bundletype;
    var $page;
    var $region;
    var $published;
    var $cantarget;
    
    function __construct() {
        ;
    }
    
    function Module() {
        $this->__construct();
    }
}

class bundle extends manager {

    var $mustUpdate = false;
    var $region_count = 0;
    
    function __construct() {
        $this->Init();
    }
    
    function bundle() {
        $this->__construct();
    }
    
    function InitConstants() {
        global $Core;
        define('BUNDLE_SKIN_FILE', SB_MANAGERS_DIR.$this->objtype . '/html/form.'.$this->objtype.'.html');
        define('BUNDLE_DISABLED', SB_MANAGERS_DIR.$this->objtype . '/html/form.disabled.html');
        define('MODEL_FILE_PATH', SB_MANAGERS_DIR.'{module}/{module}.model.php');
    }

    function InitProps() {
        $this->SetProp('headings', array('Name', 'Type', 'Published', 'Tasks'));
        $this->SetProp('tasks', array('edit'));
        $this->SetProp('cols', array('name', 'bundletype', 'published'));
    }
    
    function Filter() {
        global $Core;
        
        $filtered = array();
        foreach ($this->objs as $obj) {
            if ($obj->bundletype == 'module') {
                $bits = explode('.', $obj->name);

                $file = str_replace('{module}', $bits[1], MODEL_FILE_PATH);
                if (file_exists($file)) {
                    $model_name = $bits[1].'_model';
                    include_once($file);
                    $model = new $model_name;
                    if (isset($model->cantarget)) {
                        $obj->cantarget = $model->cantarget;
                    } 
                    else {
                        $obj->cantarget = 0;
                    }
                    unset($model);
                } 
                else {
                    $obj->cantarget = 1;
                }
            }
            if (intval($obj->cantarget) == 1) {
                $filtered[] = $obj;
            }
        }
        $this->objs = $filtered;
        unset($filtered);
    }
    
    function ConvertPublishedValue() {
        global $Core;
        
        for ($i=0; $i<count($this->objs); $i++) {
            if (isset($this->objs[$i]->published)) {
                if (isset($this->objs[$i]->type) && 
                     $this->objs[$i]->type == 'module' &&
                     !file_exists(SB_USER_MODS_DIR.$this->objs[$i]->name))
                {
                    $this->objs[$i]->published = 'Missing File';
                } 
                else if ($this->objs[$i]->published == 1) {
                    $this->objs[$i]->published = 'Yes';
                }
                else {
                    $this->objs[$i]->published = ' -- ';
                }
            }
        }
    }
    
    function GetPageNames() {
        global $Core;

        if (file_exists(SB_PAGE_FILE)) {
            $pages = $Core->xmlHandler->ParserMain(SB_PAGE_FILE);
        }
        for ($i=0; $i<count($this->objs); $i++) {
            if (isset($this->objs[$i]->page)) {
                $arr = explode(',', $this->objs[$i]->page);
                $this->objs[$i]->pagename = implode('', $arr) != '' ? NULL : ' -- ' ;
                if (count($arr) != count($pages)) {
                    for ($j=0; $j<count($arr); $j++) {
                        $page = $Core->SelectObj($pages, $arr[$j]);
                        if (isset($page->title)) {
                            $pagename = $page->title;
                        } 
                        else {
                            $pagename = $arr[$j];
                        }
                        if (!empty($this->objs[$i]->pagename) && !empty($pagename)) {
                            $this->objs[$i]->pagename .= ', '.$pagename;
                        } 
                        else {
                            $this->objs[$i]->pagename .= $pagename;
                        }
                    }
                } 
                else {
                    if ($this->objs[$i]->pagename != ' -- ') {
                        $this->objs[$i]->pagename = 'All Pages';
                    }
                }
            }
        }
    }
    
    function Trigger() {
        global $Core;
        switch ($this->button) {
            case 'add':
            case 'edit':
            case 'editbundle':
                $this->AddButton('Save');
                $this->InitSkin();
                $this->InitEditor();
                $this->Edit();
                break;
                
            case 'save':
                if (DEMO_MODE) $Core->ExitDemoEvent($this->redirect);
                $this->BalancePageRegionValues();
                $this->SaveItems();
                break;
                
            case 'back':
                Core::SBRedirect("admin.php?mgroup=collections&mgr=bundle");
                break;
                
            case 'cancel':
                $Core->ExitEvent(2, $this->redirect);
                break;
                
            default: 
                $this->InitProps(); 
                $this->UpdateBundleList();
                $this->Filter();
                $this->ConvertPublishedValue();
                $this->GetPageNames();
                $this->ViewItems();
                break;
        }
    }
    
    function BalancePageRegionValues() {
        if (count($_POST['page']) < count($_POST['region'])) {
            for ($i=0; $i<count($_POST['region']); $i++) {
                if (!empty($_POST['region'][$i])) {
                    array_push($regions, $_POST['region'][$i]);
                }
            }
            $_POST['region'] = $regions;
        }
    }
    
    function InitSkin() {
        global $Core;
        $this->skin = FileSystem::read_file(BUNDLE_SKIN_FILE);
    }
    
    function InitEditor() {
        global $Core;

        // Set the form message

        $this->SetFormMessage('name', 'Bundle');
        
        // Initialize the object properties to empty strings or
        // the properties of the object being edited

        $_OBJ = $this->InitObjProps($this->skin, $this->obj);
        
        foreach ($this->obj as $k=>$v) {
            if (!isset($_OBJ[$k]) || empty($_OBJ[$k])) {
                $_OBJ[$k] = $v;
            }
        }
        
        // This step creates a $form array to pass to BuildForm().
        // BuildForm() merges the $obj properites with the form HTML.
        
        $form['ID']            = $this->GetItemID($_OBJ);
        $form['NAME']          = $this->GetObjProp($_OBJ, 'name', null);
        $form['SELECTORS']     = $this->MakeSelectorTable($this->PageSelector($_OBJ));
        $form['PUBLISHED']     = $this->GetPublished($_OBJ);
        $form['CANTARGET']     = $this->GetObjProp($_OBJ, 'cantarget', 1);;
        $form['BUNDLETYPE']    = $this->GetObjProp($_OBJ, 'bundletype', null);;
        $form['SOURCE']        = $this->GetObjProp($_OBJ, 'source', null);
        $form['ENGINE']        = $this->GetObjProp($_OBJ, 'engine', null);
        $form['LOADAS']        = $this->GetObjProp($_OBJ, 'loadas', null);;
        $this->BuildForm($form);
    }
    
    function GetPublished($obj) {
        global $Core;
        $s = 0;
        if (!isset($obj['published']) ||
             $obj['published'] != 0)
        {
            $s = 1;
        }
        $obj['published'] = $Core->YesNoList('published', $s);
        return $obj['published'];
    }
    
    function MakeSelectorTable($selectors) {
        global $Core;
        
        $rows  = NULL;
        $table = NULL;

        $this->BalanceArrays($selectors);
        list($PageSelector, $regionSelector) = $selectors;
        
        $attrs = array();
        $attrs['colspan'] = 1;
        $attrs['class']   = 'strong';
        $space = str_repeat('&nbsp;',6);
        $rows .= $Core->HTML->MakeElement('td', $attrs, 'Page Name', 1);
        $rows  = $this->CleanWhiteSpace($rows);
        
        $attrs = array();
        $attrs['colspan'] = 1;
        $attrs['class']   = 'strong';
        $space = str_repeat('&nbsp;',6);
        $rows .= $Core->HTML->MakeElement('td', $attrs, 'Show?', 1);
        $rows  = $this->CleanWhiteSpace($rows);
        
        $attrs = array();
        $attrs['colspan'] = 1;
        $attrs['class']   = 'strong';
        $rows .= $Core->HTML->MakeElement('td', $attrs, 'Page Region', 1);
        $rows  = $this->CleanWhiteSpace($rows);
        
        for ($i=0; $i<count($PageSelector); $i++) {
            $attrs = array();
            $PageSelector[$i] = $this->CleanWhiteSpace($PageSelector[$i]);
            $cols  = $PageSelector[$i];
            $cols .= $Core->HTML->MakeElement('td', $attrs, $regionSelector[$i], 1);
            $rows .= $Core->HTML->MakeElement('tr', $attrs, $cols, 1);
        }
        $attrs = array();
        $attrs['id']          = 'bundleTargetSelectors';
        $attrs['class']       = 'linksTable';
        $attrs['cellpadding'] = 0;
        $attrs['cellspacing'] = 0;
        $attrs['width']       = '100%';
        $table = $Core->HTML->MakeElement('table', $attrs, '{cdata}', 1);
        $table = $this->CleanWhiteSpace($table);
        return str_replace('{cdata}', "\r\n".$rows."\r\n", $table);
    }
    
    function CleanWhiteSpace($shred) {
        $shred = str_replace("\r\n", '', $shred);
        $max = 100;
        $n=0;
        while (strpos($shred, '  ') !== false && $n<$max) {
            $shred = str_replace('  ', ' ', $shred);
            $n++;
        }
        return $shred;
    }
    
    function BalanceArrays(&$arrs) {
        $max = 0;
        for ($i=0; $i<count($arrs); $i++) {
            $max = count($arrs[$i]) > $max ? count($arrs[$i]) : $max ;
        }
        for ($i=0; $i<count($arrs); $i++) {
            while (count($arrs[$i]) < $max) {
                $arrs[$i][] = NULL;
            }
        }
        return $arrs;
    }
    
    function CSValsToArray($obj, $key) {
        return isset($obj[$key]) ? explode(',', $obj[$key]) : array() ;
    }
    
    function PageSelector($_OBJ) {
        global $Core;
        
        $pageName        = NULL;
        $regionsBySkin   = NULL;
        $PageSelectors   = array();
        $regionSelectors = array();
        
        $_OBJ['pages']   = $this->CSValsToArray($_OBJ, 'page');
        $_OBJ['regions'] = $this->CSValsToArray($_OBJ, 'region');
        
        if (file_exists(SB_PAGE_FILE)) {
        
            $regionsBySkin = $this->GetAllPageRegions();
            
            $pages = $Core->xmlHandler->ParserMain(SB_PAGE_FILE);
            
            foreach ($pages as $page) {
                $selectedRegion = NULL;
                
                if (in_array($page->id, $_OBJ['pages'])) {
                    $offset = $Core->OffsetInArray($_OBJ['pages'], $page->id);
                    if ($offset != -1) {
                        if (isset($_OBJ['regions'][$offset])) {
                            $selectedRegion = $_OBJ['regions'][$offset];
                        }
                    }
                }
                else if (in_array('!'.$page->id, $_OBJ['pages'])) {
                    $offset = $Core->OffsetInArray($_OBJ['pages'], '!'.$page->id);
                    if ($offset != -1) {
                        if (isset($_OBJ['regions'][$offset])) {
                            $selectedRegion = $_OBJ['regions'][$offset];
                        }
                    }
                } 
                else {
                    $selectedRegion = NULL;
                }
                
                if (isset($page->pagetype) && 
                    isset($regionsBySkin[$page->pagetype]) && 
                    count($regionsBySkin[$page->pagetype])) {
                    array_push($regionSelectors, $this->RegionSelector(
                    	'region[]',
                    	$regionsBySkin[$page->pagetype],
                    	$selectedRegion
                	));
                } 
                else {
                    array_push($regionSelectors, 'Undefined Skin Regions');
                }
                
                $options = array();
                $s = 0;
                if (in_array($page->id, $_OBJ['pages'])) {
                    $s = 1;
                }
                array_push($options, $Core->MakeOption('Yes', $page->id, $s));
                $s = 0;
                if (in_array('!'.$page->id, $_OBJ['pages']) ||
                     !in_array($page->id, $_OBJ['pages']))
                {
                    $s = 1;
                }
                array_push($options, $Core->MakeOption('No', '!'.$page->id, $s));
                $input  = '<td>'.$page->name.'</td>';
                $input .= '<td>'.$Core->SelectList($options, 'page[]').'</td>';
                array_push($PageSelectors, $input);
            }
        }
        return array($PageSelectors, $regionSelectors);
    }
    
    function mergeAllRegions($regions) {
        $allRegions = array();
        foreach ($regions as $reg) {
            for ($j=0; $j<count($reg); $j++) {
                if (!in_array($reg[$j], $allRegions)) {
                    array_push($allRegions, $reg[$j]);
                }
            }
        }
        return $allRegions;
    }
    
    function GetAllPageRegions() {
        global $Core;
        
        $regions = array();
        
        $skins = $Core->ListFilesOptionalRecurse(
        	ACTIVE_SKIN_DIR, 0 
        );
        if (count($skins)) {
            $regions = $this->GetRegions($skins);
        }
        foreach ($regions as $skin=>$tokens) {
            $this->region_count += count($tokens);
        }
        
        return $regions;
    }
    
    function RegionSelector($inputName, $regions, $selected) {
        global $Core;
        
        $selector = null;
        if (empty($regions)) $regions = array();
        
		$options = array();
		$options[] = $Core->MakeOption(' -- Select Region -- ', NULL, 0);
		for ($i=0; $i<count($regions); $i++) {
			$s = $regions[$i] == $selected ? 1 : 0 ;
			$rname = str_replace(array('{region:','}'),NULL,$regions[$i]);
			$rname = ucwords($rname);
			$options[] = $Core->MakeOption($rname, $regions[$i], $s);
		}
		$selector = $Core->SelectList($options, $inputName);
        return $selector;
    }
    
    function GetRegions() {
        global $Core;
        
        $regions = array();
        $skins   = array();
        
        $path  = ACTIVE_SKIN_DIR;
        $skins = $Core->ListFilesOptionalRecurse($path, 0);
        
        if (count($skins)) {
            for ($i=0; $i<count($skins); $i++) {
                $bits = explode('.', basename($skins[$i]));
                $skin = FileSystem::read_file($skins[$i]);
                $regions[$bits[1]] = $Core->GetPageRegions($skin);
            }
        }
        return $regions;
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
    
    function Show() {
        global $Core;
        
        if ($this->button == 'editbundle' && $this->region_count == 0) {
            echo FileSystem::read_file(BUNDLE_DISABLED);
            return;
        }
        
        $style = NULL;
        $tablestyle = NULL;
        if (count($this->objs) > SB_MAX_LIST_ROWS && $this->button != 'add' &&
             $this->button != 'edit' && $this->button != 'editstory') 
        {
            $style = ' style="height: 500px;" ';
            $tablestyle = ' style="width: 455px;"';
        }

        $html = NULL;
        if (!empty($Core->MSG) && trim($Core->MSG) != '...') {
            $html  = $Core->MSG."\r\n";
        }
        $html .= '<form method="post" action="'.$this->redirect.'" style="margin-top: 15px;">'."\r\n";
        $html .= '<fieldset>'."\r\n";

        if (isset($this->include_th) && $this->include_th == 1) {
            $html .= '<div id="imglist"'. $style .'>'."\r\n";
        }
        
        $class = isset($this->include_th) && $this->include_th == 1 ? 'linkstable' : 'editortable' ;
        
        $html .= '<table class="'.$class.'" cellpadding="0" cellspacing="0"'.$tablestyle.'>'."\r\n";
        $html .= isset($this->include_th) && $this->include_th == 1 ? $this->BuildHeadings() : '' ;
        $html .= $this->html;
        $html .= '</table>'."\r\n";
        if (isset($this->include_th) && $this->include_th == 1) {
            $html .= '</div>'."\r\n";
        }
        $html .= $this->BuildButtons();
        $html .= '</fieldset>'."\r\n";
        $html .= '</form>'."\r\n";
        
        echo $html;
    }
    
    // END Show()
    
    // GROUP: UpdateBundleList(): Dynamically loads newly installed modules,
    //                            menus and galleries.
    
    function UpdateBundleList() {
        global $Core;

        $this->LoadNewBundles();
        if ($this->mustUpdate === true) {
            $records = $Core->xmlHandler->ObjsToXML($this->objs, $this->objtype);
            $Core->WriteFile($this->datasrc, $records, 1);
            $Core->SBRedirect($this->redirect);
        }
    }
    
    function LoadNewBundles() {
        global $Core;
        
        $mgrs = $Core->ListDirsToLevel(SB_MANAGERS_DIR, array(), 1, 0);

        for ($i=0; $i<count($mgrs); $i++) {
            $file = str_replace(SB_MANAGERS_DIR, NULL, $mgrs[$i]);
            $file = str_replace('/', NULL, $file);
            $file .= '.model.php';

            $bits = explode('.', basename($file));
            $name = $bits[0];
            $class = $name.'_model';
            
            // This hack is necessary because all of the portfolio data is stored in a 
            // subdirectory within the XML dir. For the next version of SBC, all data 
            // pertaining to each extension should be in sub-directories.
            
            $dir = $name != 'portfolio' ? $name : 'portfolio/'.$name ;
            $dir = $name != 'disportfolio' ? $name : 'disportfolio/'.$name ;
            $dir = $name != 'discasestudies' ? $name : 'discasestudies/'.$name ;
            
            if (!file_exists($mgrs[$i].$file) || !file_exists(SB_XML_DIR.$dir.'.xml')) {
                continue;
            }
            
            include($mgrs[$i].$file);
            $model = new $class;
            
            if (!$this->CanTarget($model)) continue;
            
            switch ($model->loadas) {
                case 'menu':
                    $this->NewMenuBundle($model);
                    break;
                case 'xml':
                    $this->NewXMLBundle($model, $name);
                    break;
                case 'module':
                    $this->NewModuleBundle($model);
                    break;
            }
        }

        $mods = $Core->ListFiles(SB_USER_MODS_DIR);
        for ($i=0; $i<count($mods); $i++) {
			if (!$this->InBundleLIst(basename($mods[$i])) && 
			    !$this->InManagerList($mgrs, basename($mods[$i])))
			{
			    $this->NewModuleBundle(
					new GenericModel(
						basename($mods[$i]), 
						'module', 
						basename($mods[$i]), 
						'module', 
						1, 
						'collections'
					)
				);
			}
		}
    }

    function InManagerList($mgrs, $name) {
        for ($i=0; $i<count($mgrs); $i++) {
 			$mgrs[$i] = basename($mgrs[$i]);
    	}
        return in_array(str_replace(array('.php', 'mod.'), null, $name), $mgrs);
	}

    function InBundleList($name) {
        foreach ($this->objs as $obj) {
		    if (strtolower($obj->name) == strtolower($name)) {
			    return true;
			}
		}
		return false;
    }
    
    function NewModuleBundle($model) {
        global $Core;
        
        if (!$this->ModuleBundleExists($model->bundlesource) 
             && file_exists(SB_USER_MODS_DIR.$model->bundlesource))
        {
            $mod = new Module;
            $mod->id = $Core->GetNewID($this->objs);
            $mod->name = $model->bundlesource;
            $mod->published = 0;
            $mod->cantarget = 1;
            $mod->bundletype = 'module';
            $mod->loadas = 'module';
            $this->objs[] = $mod;
            unset($mod);
            $this->mustUpdate = true;
        }
    }
    
    function NewXMLBundle($model, $name) {
        global $Core;
        
        $path = SB_XML_DIR.$model->bundlesource;
        $objs = $Core->xmlHandler->ParserMain($path);
        
        if (!count($objs)) return;
        
        foreach ($objs as $obj) {
			if (!$this->XMLBundleExists($model->objtype,$obj->id)) {
				$mod = new Module;
				$mod->id = $Core->GetNewID($this->objs);
				$mod->name = $obj->title.' [ID:'.$obj->id.']';
				$mod->published = 0;
				$mod->cantarget = 1;
				$mod->bundletype = $model->objtype;
				$mod->source = $model->bundlesource;
				$mod->engine = 'mod.'.$name.'.php';
				$mod->loadas = 'xml';
				$this->objs[] = $mod;
				unset($mod);
				$this->mustUpdate = true;
			}
			$this->UpdateBundleNames($model->objtype, $objs);
		}
    }
    
    function NewMenuBundle($model) {
        global $Core;
        
        $path = SB_XML_DIR.$model->bundlesource;
        $objs = $Core->xmlHandler->ParserMain($path);
        
        if (!count($objs)) return;
        
        foreach ($objs as $obj) {
			if (!$this->XMLBundleExists($model->objtype,$obj->id)) {
				$mod = new Module;
				$mod->id = $Core->GetNewID($this->objs);
				$mod->name = $obj->title.' [ID:'.$obj->id.']';
				$mod->published = 0;
				$mod->cantarget = 1;
				$mod->bundletype = $model->objtype;
				$mod->source = $model->bundlesource;
				$mod->engine = 'menubuilder.php';
				$mod->loadas = 'menu';
				$this->objs[] = $mod;
				unset($mod);
				$this->mustUpdate = true;
			}
			$this->UpdateBundleNames($model->objtype, $objs);
		}
    }
    
    function UpdateBundleNames($type,$objs) {
        for($i=0; $i<count($objs); $i++) {
            for ($j=0; $j<count($this->objs); $j++) {
                if ($this->objs[$j]->bundletype == $type) {
                    $regex = "/\[ID:([0-9]+)\]/";
                    if (preg_match_all($regex,$this->objs[$j]->name,$matches)) {
                        if (trim($matches[1][0]) == $objs[$i]->id) {
                            $this->objs[$j]->name = 
                                $objs[$i]->title.' [ID:'.$objs[$i]->id.']';
                        }
                    }
                }
            }
        }
    }
    
    function XMLBundleExists($type,$id) { 
        $bundles = array();
        foreach ($this->objs as $obj) {
            if ($obj->bundletype == $type) {
                $regex = "/\[ID:([0-9]+)\]/";
                if (preg_match_all($regex,$obj->name,$matches)) {
                    array_push($bundles, trim($matches[1][0]));
                }
            }
        }
        if (in_array($id,$bundles)) {
            return true;
        }
        return false;
    }
        
    function ModuleBundleExists($bundle) {
        $bundles = array();
        foreach ($this->objs as $obj) {
            array_push($bundles, $obj->name);
        }
        if (in_array($bundle, $bundles)) {
            return true;
        }
        return false;
    }
    
    function CanTarget($model) {
        if (!isset($model->bundletype) || empty($model->bundletype) ||
             !isset($model->bundlesource) || empty($model->bundlesource) ||
             !isset($model->cantarget) || $model->cantarget == 0)
        {
            return false;
        }
        return true;
    }    
}

class GenericModel {
    var $id;
    var $name;
    var $content;
    var $cantarget;
    var $group;
    var $bundletype;
    var $bundlesource;
    var $objtype;
    var $loadas;
    
    function __construct($name, $type, $bsrc, $loadas, $cantarget, $group) {
        $this->name = $name;
		$this->bundletype = $type;
		$this->bundlesource = $bsrc;
		$this->loadas = $loadas;
		$this->cantarget= $cantarget;
		$this->group = $group;
    }

    function GenericModel($name, $type, $bsrc, $loadas, $cantarget, $group) {
        $this->__construct($name, $type, $bsrc, $loadas, $cantarget, $group);
	}
}
?>