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

class Meta extends manager
{
    
    function __construct() 
    {
        $this->Init();
    }
    
    function Meta()
    {
        $this->__construct();
    }
    
    function AddEventHandlers()
    {
        $this->AddEventHandler('OnBeforeLoad','InitObjTypes');
        $this->AddEventHandler('OnBeforeShow','DefineButtons');
    }
    
    function TestEvtH1()
    {
        echo "<h2>The first OnBeforeLoadEvent</h2>";
    }
    
    function TestEvtH2()
    {
        global $Core;
        $Core->Dump($this);
    }
    
    function InitObjFilter()
    {
        $this->SetProp('filter', 'show');
        $this->SetProp('filterprop', 'metagroup');
    }
    
    function addFilterVar()
    {
        global $Core;
        $filterVar = NULL;
        if (isset($this->filter) && !empty($this->filter))
        {
            $filter = $Core->GetVar($_GET, $this->filter, NULL);
            if (!empty($filter))
            {
                $filterVar = '&'.$this->filter.'='.$filter;
            }
        }
        return $filterVar;
    }
    
    function InitObjTypes()
    {
        $this->SetProp('objtypes', array('meta', 'metagroups'));
    }
    
    function InitProps() 
    {
        if (empty($this->button) ||
             strpos($this->button, 'view') !== FALSE)
        {
            if ($this->objtype == 'meta')
            {
                $this->SetProp('headings', array('Name', 'Groups', 'Tasks'));
                $this->SetProp('tasks', array('edit', 'delete'));
                $this->SetProp('cols', array('name', 'metagroups'));
            } 
            else 
            {
                $this->SetProp('headings', array('Group Name', 'Tasks'));
                $this->SetProp('tasks', array('edit', 'delete'));
                $this->SetProp('cols', array('name'));
            }
        }
    }
    
    function ResetTableHeadings()
    {
        $this->SetProp('headings', null); // array());
        $this->SetProp('tasks', null); // ;
        $this->SetProp('cols', null); // ;
        $this->InitProps();
    }
    
    function MetagroupIDtoName()
    {
        global $Core;
        
        $file = SB_XML_DIR.'metagroups.xml';
        if (file_exists($file))
        {
            $groups = $Core->xmlHandler->ParserMain($file);
            for ($i=0; $i<count($this->objs); $i++)
            {
                if (!isset($this->objs[$i]->metagroups))
                {
                    $this->objs[$i]->metagroups = NULL;
                }
                $myGroups = explode(',', $this->objs[$i]->metagroups);
                $this->objs[$i]->metagroups = NULL;
                $grpnames = array();
                for ($j=0; $j<count($myGroups); $j++)
                {
                    $myGroups[$j] = trim($myGroups[$j]);
                    $grp = $Core->SelectObj($groups, $myGroups[$j]);
                    if (isset($grp->name))
                    {
                        $grpnames[] = $grp->name;
                    } else {
                        $grpnames[] = $myGroups[$j];
                    }
                }
                $this->objs[$i]->metagroups = implode(', ', $grpnames);
            }
        }
    }

    function DefineButtons()
    {
        global $Core;
        if ($this->showcancel != 1)
        {
            if ($this->objtype == 'meta')
            {
                $add = ' Item';
                $view = ' Groups';
            } 
            else 
            {
                $add = ' Group';
                $view = ' Items';
            }
            $this->AddButton('Add'.$add);
            $this->AddButton('View'.$view);
        }
    }
    
    function Trigger()
    {
        global $Core;
        
        $this->InitProps();
        
        switch ($this->button) 
        {
            case 'addgroup':
            case 'editmetagroups':
                $this->UpdateReferences('metagroups');
                $this->showcancel = 1;
                $this->AddButton('Save');
                $this->InitSkin();
                $this->InitEditor();
                $this->Edit();
                break;
                
            case 'additem':
            case 'editmeta':
            case 'edititem':
            case 'add':
            case 'edit':
                $this->UpdateReferences('meta');
                $this->showcancel = 1;
                $this->AddButton('Save');
                $this->InitSkin();
                $this->InitEditor();
                $this->Edit();
                break;
                
            case 'save':
                $this->UpdateReferences(
                    $Core->GetVar($_POST,'objtype',$this->objtype));
                $this->SaveItems();
                break;
                
            case 'delete':
            case 'deleteitems':
            case 'deletemeta':
                $this->UpdateReferences('meta');
                $this->DeleteItem();
                break;
                
            case 'deletemetagroups':
                $this->UpdateReferences('metagroups');
                $this->DeleteItem();
                break;
                
            case 'cancel':
                $this->UpdateReferences(
                    $Core->GetVar($_POST,'objtype',$this->mgr));
                $this->Cancel();
                break;
                
            case 'viewgroups':
                $this->showcancel = 0;
                $this->UpdateReferences('metagroups');
                $this->ResetTableHeadings();
                $this->ViewItems();
                break;
                
            case 'viewitems':
            case 'view':
            case 'viewmeta':
            default:
                $this->UpdateReferences('meta');
                $this->MetagroupIDtoName();
                $this->showcancel = 0;
                $this->ViewItems();
                break;
                
            default:
                $this->UpdateReferences(
                    $Core->GetVar($_GET,'objtype',$this->mgr));
                if ($this->objtype == 'meta')
                {
                    $this->MetagroupIDtoName();
                }
                $this->showcancel = 0;
                $this->ViewItems();
                break;
        }
    }
    
    function InitObjType()
    {
        global $Core;
        
        // Set $this->objtype to its lowest priority value 'mgr'
        // At the very least, $this->objtype will have a default value
        
        $this->objtype = $this->mgr;
        
        // Next, check the query string
        
        $this->objtype = $Core->GetVar($_GET, 'objtype', $this->mgr);
        
        // The button pressed has highest priority
        // Check the $objtypes first for matches with the button
        // and over-ride any previously set values
        
        if (strpos($this->button, 'group') !== FALSE)
        {
            $this->objtype = 'metagroups';
        } 
        else if ($this->button == 'Cancel'|| $this->button == 'save') 
        {
            $this->objtype = $Core->GetVar(
                $_POST, 'objtype', $this->objtype);
        } 
        else 
        {
            $this->objtype = 'meta';
        }
        
    }
    
    function ResetRedirect($objtype, $sub=NULL)
    {
        $this->redirect  = BASE_PAGE.'?mgroup='.$this->mgroup;
        $this->redirect .= '&mgr='.$this->mgr;
        $this->redirect .= '&objtype='.$objtype;
        $this->redirect .= !empty($sub) ? '&sub='.$sub : NULL ;
    }
    
    function InitSkin()
    {
        global $Core;
        $this->skin = $Core->OutputBuffer(
            SB_MANAGERS_DIR.'meta/html/form.'.$this->objtype.'.html');
    }
    
    function InitEditor() 
    {
        global $Core;
        
        // Set the form message
        
        if (strpos($this->button, 'edit') !== FALSE || 
             strpos($this->button, 'add') !== FALSE)
        {
            $objtype = NULL;
            if ($this->objtype == 'meta')
            {
                $objtype = 'New Meta Tag';
            } 
            else if ($this->objtype == 'metagroups')
            {
                $objtype = 'New Meta Tag Group';
            }
            $itemtitle = 
                isset($this->obj->name) ? $this->obj->name : $objtype ;
            if (!empty($itemtitle))
            {
                $Core->MSG = '<h2 class="edit">'.$itemtitle.'</h2>';
            }
        }
        
        // Initialize the object properties to empty strings or
        // the properties of the object being edited
        
        $_OBJ = $this->InitObjProps($this->skin, $this->obj);
        
        // This step creates a $form array to pass to BuildForm().
        // BuildForm() merges the $obj properites with the form HTML.
        
        $form['ID'] = 
          isset($_OBJ['id']) ? $_OBJ['id'] : $Core->GetNewID($this->objs);
        $form['NAME'] = 
          isset($_OBJ['name']) ? $_OBJ['name'] : NULL ;
        
        if ($this->objtype == 'meta')
        {
            $form['CONTENT'] = 
              isset($_OBJ['content']) ? $_OBJ['content'] : NULL;
            
            if (!isset($_OBJ['metagroups']))
            {
                $_OBJ['metagroups'] = NULL;
            }
            $form['METAGROUPS'] = 
              $this->MetaGroupSelector($_OBJ['metagroups']);
        } 
        else 
        {
            $form['ITEMS'] = $this->MetaItemList();
        }

        $this->BuildForm($form);
    }
    
    function MetaItemList()
    {
        global $Core;
        
        $objs = array();
        $file = SB_XML_DIR.'meta.xml';
        if (!file_exists($file))
        {
            return NULL;
        }
        $objs = $Core->xmlHandler->ParserMain($file);
        
        $items = '<ul>'."\r\n";
        foreach($objs as $obj)
        {
            if (!isset($obj->metagroups))
            {
                $obj->metagroups = NULL;
            }
            $grps = explode(',', $obj->metagroups);
            for ($i=0; $i<count($grps); $i++)
            {
                if (trim($grps[$i]) == $this->id)
                {
                    $items .= '<li>'.$obj->name.'</li>'."\r\n";
                }
            }
        }
        $items .= '</ul>'."\r\n";
        return $items;
    }
    
    function MetaGroupSelector($groups=NULL)
    {
        global $Core;
        
        $groups = explode(',', $groups);
        for ($i=0; $i<count($groups); $i++)
        {
            $groups[$i] = trim($groups[$i]);
        }
        
        $objs = array();
        $file = SB_XML_DIR.'metagroups.xml';
        if (!file_exists($file))
        {
            return NULL;
        }
        
        $objs = $Core->xmlHandler->ParserMain($file);
        
        $selector = '<ul>'."\r\n";
        foreach ($objs as $obj)
        {
            $selector .= '<li>'."\r\n";
            $selector .= '<input type="checkbox" ';
            $selector .= 'name="metagroups[]" ';
            $selector .= 'value="'.$obj->id.'" ';
            if (in_array($obj->id, $groups))
            {
                $selector .= 'checked="checked" ';
            }
            $selector .= '/>&nbsp;';
            $selector .= $obj->name."\r\n";
            $selector .= '</li>'."\r\n";
        }
        $selector .= '</ul>'."\r\n";
        return  $selector;
    }
    
}

?>