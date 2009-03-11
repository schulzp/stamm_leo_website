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

class media extends manager {

    var $media;
    var $allowedTypes;
    var $ImageMimes;
    var $targets;
    
    function __construct() {
        $this->Init();
    }
    
    function media() {
        $this->__construct();
    }
    
    function AddEventHandlers() {
        $this->AddEventHandler('OnBeforeLoad', 'InitAllowedTypes');
        $this->AddEventHandler('OnBeforeLoad', 'InitImageMimes');
        $this->AddEventHandler('OnBeforeLoad', 'setTargets');
    }
    
    function InitAllowedTypes() {
        $this->allowedTypes   = array();
        $this->allowedTypes[] = 'image/png';
        $this->allowedTypes[] = 'image/jpeg';
        $this->allowedTypes[] = 'image/pjpeg';
        $this->allowedTypes[] = 'image/gif';
        $this->allowedTypes[] = 'application/mpeg';
        $this->allowedTypes[] = 'application/x-shockwave-flash';
        $this->allowedTypes[] = 'video/mpeg';
        $this->allowedTypes[] = 'video/x-ms-wmv';
        $this->allowedTypes[] = 'application/x-ms-wmv';
        $this->allowedTypes[] = 'application/x-oleobject';
        $this->allowedTypes[] = 'application/pdf';
        $this->allowedTypes[] = 'application/x-zip';
        $this->allowedTypes[] = 'application/x-gzip';
    }
    
    function InitImageMimes() {
        $this->ImageMimes   = array();
        $this->ImageMimes[] = 'image/png';
        $this->ImageMimes[] = 'image/jpeg';
        $this->ImageMimes[] = 'image/gif';
        $this->ImageMimes[] = 'image/pjpeg';
    }
    
    function setTargets() {
        $this->targets = FileSystem::list_dirs(SB_MEDIA_DIR);
        array_push($this->targets, SB_DOWNLOADS_DIR);
        array_push($this->targets, SB_UPLOADS_DIR);
    }
    
    function InitConstants() {
        define('CANNOT_MOVE_TO_SAME_DIR', 
                'Cannot move file to same location.');
        define('NO_IMAGE_NAME', 'No file name found.');
        define('NO_MOVE_FROM', 'No /Move From/ Directory specified.');
        define('NO_MOVE_TO', 'No /Move To/ Directory specified.');
        define('IMG_ALREADY_EXISTS', 
                'A file with that name already exists in the specified '.
                ' location. You can rename the file and then move it.');
        define('SAME_NAME', 'You cannot rename a file to the same name.');
        define('NO_FILE_SELECTED', 'No file selected');
        define('NO_MAX_FILE_SIZE', 'MAX_FILE_SIZE not specified');
        define('COULD_NOT_UPLOAD', 'Files could not be uploaded.');
        define('WAS_UPLOADED', ' was successfully uploaded.');
        define('WAS_NOT_UPLOADED', ' could not be uploaded.');
    }

    function InitProps() {
        $this->SetProp('headings', array('Name', 'Tasks'));
        $this->SetProp('cols', array('name'));
        $this->SetProp('tasks', array('edit', 'delete'));
    }
        
    function Trigger() {
        global $Core;
        switch ($this->button) {
            case 'move':
                $this->skintype = 'move';
                $this->buttons[0]['value'] = 'Move File';
                $this->buttons[0]['js'] = NULL;
                $this->InitSkin('move');
                $this->InitMoveEditor();
                $this->Edit();
                break;
                
            case 'movefile':
                if (DEMO_MODE) {
                    $Core->ExitDemoEvent($this->redirect);
                }
                else {
                    $this->DoMoveFile();
                }
                break;

            case 'rename':
                $this->buttons[0]['value'] = 'Rename File';
                $this->buttons[0]['js'] = NULL;
                $this->InitSkin('rename');
                $this->InitRenameEditor();
                $this->Edit();
                break;
                
            case 'renamefile':
                if (DEMO_MODE) {
                    $Core->ExitDemoEvent($this->redirect);
                }
                else {
                    $this->DoRenameFile();
                }
                break;
                
            case 'add':
                $this->buttons[0]['value'] = 'Upload';
                $this->buttons[0]['js'] = NULL;
                $this->InitSkin('upload');
                $this->InitUploadEditor();
                $this->Edit();
                break;
                
            case 'upload':
                if (DEMO_MODE) {
                    $Core->ExitDemoEvent($this->redirect);
                }
                else {
                    $this->DoUpLoad();
                }
                break;
                
            case 'delete':
            case 'deletemedia':
                if (DEMO_MODE) {
                    $Core->ExitDemoEvent($this->redirect);
                }
                else {
                    $this->DeleteFile();
                }
                break;
                
            case 'cancel':
                $Core->ExitEvent(2, $this->redirect);
                break;
                
            default: 
                $this->buttons[0]['value'] = 'Add';
                $this->buttons[0]['js'] = NULL;
                $this->ViewItems();
                $this->AddCountField();
                break;
        }
    }
    
    function InitSkin($type) {
        global $Core;
        
        $file = 'managers/media/html/form.media.'.$type.'.html';
        if (!file_exists($file)) {
            $Core->FileNotFound(
                $file, 
                __LINE__,
                __FILE__.'::InitSkin()'
           );
        } 
        else {
            $this->skin = FileSystem::read_file($file);
        }
    }
    
    function InitMoveEditor() {
        global $Core;

        // Set the form message
        
        $Core->MSG = null;
        
        // Initialize the object properties to empty strings or
        // the properties of the object being edited
        
        $_OBJ = $this->InitObjProps($this->skin, $this->obj);
        
        // This step creates a $form array to pass to BuildForm().
        // BuildForm() merges the $obj properites with the form HTML.
        
        $form['ID']        = $this->GetObjProp($_OBJ, 'id', null);
        $form['NAME']      = basename($this->media[$this->id]);
        $form['MOVE_FROM'] = dirname($this->media[$this->id]).'/';
        $form['MOVE_TO']   = $this->BuildDirSelector(SB_MEDIA_DIR, 'move_to');
        
        $this->BuildForm($form);
    }
    
    function InitRenameEditor() {
        global $Core;

        // Set the form message
        
        $Core->MSG = NULL;
        
        // Initialize the object properties to empty strings or
        // the properties of the object being edited
        
        $_OBJ = $this->InitObjProps($this->skin, $this->obj);
        
        // This step creates a $form array to pass to BuildForm().
        // BuildForm() merges the $obj properites with the form HTML.
        
        $form['ID']        = $this->GetObjProp($_OBJ, 'id', NULL);
        $form['NAME']      = basename($this->media[$this->id]);
        $form['MOVE_FROM'] = dirname($this->media[$this->id]).'/';
        $form['RENAME_TO'] = NULL;
        
        $this->BuildForm($form);
    }
    
    function InitUploadEditor() {
        global $Core;
        
        $this->form_encoding = ' enctype="multipart/form-data" ';

        // Set the form message
        
        $Core->MSG = NULL;
        
        // Initialize the object properties to empty strings or
        // the properties of the object being edited
        
        $_OBJ = $this->InitObjProps($this->skin, $this->obj);
        
        // This step creates a $form array to pass to BuildForm().
        // BuildForm() merges the $obj properites with the form HTML.
        
        $form['MAX_FILE_SIZE'] = SB_MAX_IMG_UPLOAD_SIZE;
        $form['ROWS']          = $this->BuildUploadFields();
        
        $this->BuildForm($form);
    }
    
    function BuildUploadFields() {
        global $Core;
        $n = $Core->GetVar($_POST, 'count', 1);
        $n = $n > 10 ? 10 : $n ;
        
        $rows = NULL;
        for ($i=0; $i<$n; $i++) {
            $selector = $this->BuildDirSelector(SB_MEDIA_DIR, 'uploadDir[]');
            
            $attrs = array();
            $attrs['class'] = 'uploadfield';
            $attrs['type']  = 'file';
            $attrs['name']  = 'upload[]';
            $attrs['size']  = 12;
            $input = $Core->HTML->MakeElement('input', $attrs, NULL);
            $td    = $Core->HTML->MakeElement('td', array(), $input)."\r\n";
            $td   .= $Core->HTML->MakeElement('td', array(), $selector);
            $rows .= $Core->HTML->MakeElement('tr', array(), $td);
        }
        return $rows;
    }
    
    function GetItemID($obj) {
        global $Core;
        
        if (!isset($obj['id']) || empty($obj['id'])) {
            return $Core->GetNewID($this->obs);
        } 
        else {
            return $obj['id'];
        }
    }
    
    function InitRedirect() {
        global $Core;

        $this->redirect = "admin.php?mgroup={$this->mgroup}" . 
            "&mgr={$this->mgr}&objtype={$this->objtype}";
        if (!empty($this->filter)) {
            $this->filterval = $Core->GetVar($_GET, $this->filter, NULL);
            if (!empty($this->filterval)) {
                $this->redirect .= '&'.$this->filter.'='.$this->filterval;
            }
        }
        if (strpos($this->redirect, '&dir=') === FALSE &&
            strpos($this->redirect, '&amp;dir=') === FALSE)
        {
            $this->redirect .= '&dir='.$Core->GetVar($_GET, 'dir', 'all');
        }
    }
    
    function GetFileType($file) {
	    return trim(exec('file -bi ' . $file));
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
    
    function ViewItems() {
        global $Core;
        
		$this->styles['formdiv'] = NULL;
		$this->styles['formtable'] = NULL;
        if (count($this->media) > SB_MAX_LIST_ROWS) {
            $this->styles['formdiv'] = sLIST_OVERFLOW_HEIGHT_STYLE;
            $this->styles['formtable'] = sLIST_OVERFLOW_WIDTH_STYLE;
        }
        
        $attrs = array(
            'src'    => CAMERA_ICON_GIF,
            'width'  => '20',
            'height' => '14',
            'style'  => 'position: relative; top: 3px;'
        );
        
        $icon = $Core->HTML->MakeElement('img', $attrs, '', 0);
        
        $ths = $Core->HTML->MakeElement('th', array('width'=>'20'), '#');
        
        $ths .= $Core->HTML->MakeElement(
            'th', 
            array(), 
            $icon.'&nbsp;|&nbsp;&nbsp;File Name'
        );
        
        $ths .= $Core->HTML->MakeElement(
            'th', 
            array('style' => 'width: 150px; padding-top: 4px;'), 
            'Task'
        );
        
        $this->html .= $Core->HTML->MakeElement('tr', array(), $ths);

        if (count($this->media)) {
            for ($i=0; $i<count($this->media); $i++) {
            
                $image_name = basename($this->media[$i]);
                
				$title = $this->media[$i];
				if (strpos($title, SB_MEDIA_DIR) !== FALSE) {
				   $title = str_replace(SB_MEDIA_DIR, NULL, $title);
				}
				else if (strpos($title, SB_DOWNLOADS_DIR) !== FALSE) {
				    $title = str_replace(SB_DOWNLOADS_DIR, NULL, $title);
				}
				else if (strpos($title, SB_UPLOADS_DIR) !== FALSE) {
				    $title = str_replace(SB_UPLOADS_DIR, NULL, $title);
				}
				else if (strpos($title, SB_SITE_DATA_DIR) !== FALSE) {
				   $title = str_replace(SB_SITE_DATA_DIR, NULL, $title);
				}
                
				$attrs = array('class'=>($i % 2 == 0 ? 'even' : 'odd'));
                $tds = $Core->HTML->MakeElement('td', $attrs, $i+1);
                
                $cw = '20';
                $ch = '14';
                $trail = null;
                $canPreview = true;
                $icon = CAMERA_ICON_GIF;
                if (!in_array(FileSystem::file_ext($this->media[$i]),
                    array('gif', 'png', 'jpg', 'jpeg')
                )) {
                    $canPreview = false;
                    $icon = FILE_ICON_GIF;
					$ch = $Core->ImageHeight($icon);
					$cw = $Core->ImageWidth($icon);
                }
				
				$attrs = array(
					'src'    => $icon,
					'width'  => '20',
					'height' => '14',
					'style'  => 'position: relative; top: 3px;'
				);

                if ($canPreview) {
					$attrs['onmouseover'] = "showtrail(this, '{$this->media[$i]}');";
					$attrs['onmouseout']  = 'hidetrail();';
					# $attrs['rel'] = $this->media[$i];
	            }
	
	            $trail = $Core->HTML->MakeElement('img', $attrs, '', 0);
                $tds .= $Core->HTML->MakeElement(
                    'td', 
                    array('class'=>($i % 2 == 0 ? 'even' : 'odd')), 
                    $trail.'&nbsp;|&nbsp;&nbsp;'.$title
                );

                $buttons = $Core->HTML->MakeElement(
                    'a', 
                    array('href'=>$this->redirect.'&amp;sub=move&amp;id='.$i), 
                    'Move'
                );

                $buttons .= $Core->HTML->MakeElement(
                    'a', 
                    array('href'=>$this->redirect.'&amp;sub=rename&amp;id='.$i), 
                    'Rename'
                );
                
                $buttons .= $Core->HTML->MakeElement(
                    'a', array(
                        'href' => $this->redirect.'&amp;sub=delete&amp;id='.$i,
                        'onclick' => str_replace('{name}', $image_name, sCONFIRM_DELETE_JS)
                    ), 
                    'Delete'
                );
                
                $tds .= $Core->HTML->MakeElement(
                    'td', 
                    array('class'=>($i % 2 == 0 ? 'even' : 'odd')), 
                    $buttons
                );
                
                $this->html .= $Core->HTML->MakeElement('tr', array(), $tds);
            }
        } 
        else {
            $this->html .= $Core->HTML->MakeElement(
                'tr', 
                array('colspan'=>2), 
                $Core->HTML->MakeElement('td', $attrs, SB_NO_ITEMS_TO_DISPLAY)
            );
        }
    }
    
    function AddCountField() {
        global $Core;
        $attrs = array(
            'type'  => 'text',
            'name'  => 'count',
            'value' => '1',
            'size'  => '5',
            'class' => 'countfield'
        );
        
        $field = $Core->HTML->MakeElement('input', $attrs, '');
        $this->countfield = "&nbsp;{$field}&nbsp;Files (number of files to upload)\n";
    }
    
    function BuildDirSelector($path, $name, $select_item=0) {
        global $Core;
        
        $items = array();
        $dirs = $Core->ListDirs($path);
        array_push($dirs, SB_DOWNLOADS_DIR);
        array_push($dirs, SB_UPLOADS_DIR);
        array_push($dirs, ACTIVE_SKIN_DIR . "images/");
        
        if ($select_item) {
            array_push($items, $Core->MakeOption('- Select Directory -', NULL, 1));
        }
        foreach ($dirs as $k=>$v) {
            array_push($items, $Core->MakeOption($v, $v, 0));
        }
        $selector = $Core->SelectList($items, $name);
        return $selector;
    }
    
    function DeleteFile() {
        global $Core;
        
        $original = $this->media[$this->id];
        $name = basename($original);

        if (FileSystem::delete_file($original)) {
            $Core->SetSessionMessage(
                $name.' has been deleted.', 
                'confirm'
            );
        } 
        else {
            $Core->SetSessionMessage(
                $name.' could not be deleted.', 
                'error'
            );
        }
        $Core->SBRedirect($this->redirect);
    }
    
    function DoRenameFile() {
        global $Core;
        
        $name          = $Core->GetVar($_POST, 'name', NULL);
        $move_from     = $Core->GetVar($_POST, 'move_from', NULL);
        $rename_to     = $Core->GetVar($_POST, 'rename_to', NULL);
        
        if (empty($name)) {
            $Core->SetSessionMessage(NO_IMAGE_NAME, 'error');
            $Core->SBRedirect($this->redirect);
        }
        else if ($move_from.$name == $move_from.$rename_to) {
            $Core->SetSessionMessage(SAME_NAME, 'error');
            $Core->SBRedirect($this->redirect);
        }
        
        if (!in_array($move_from.$name, $this->allowedTypes)) {
            $Core->SetSessionMessage(
                'Image '.$name.' could not be renamed to '. $rename_to 
                . ' because the file type is not allowed', 
                'error'
            );
        }
        else if ($Core->MoveFile($move_from.$name, $move_from.$rename_to)) {
            $Core->SetSessionMessage(
                'Image '.$name.' was renamed to '.$rename_to, 
                'confirm'
            );
        } 

        $Core->SBRedirect($this->redirect);
    }
    
    function DoMoveFile() {
        global $Core;
        
        $name          = $Core->GetVar($_POST, 'name', NULL);
        $move_from     = $Core->GetVar($_POST, 'move_from', NULL);
        $move_to       = $Core->GetVar($_POST, 'move_to', NULL);
        
        if (empty($name)) {
            $Core->SetSessionMessage(NO_IMAGE_NAME, 'error');
            $Core->SBRedirect($this->redirect);
        }
        else if (empty($move_from)) {
            $Core->SetSessionMessage(NO_MOVE_FROM, 'error');
            $Core->SBRedirect($this->redirect);
        }
        else if (empty($move_to)) {
            $Core->SetSessionMessage(NO_MOVE_TO, 'error');
            $Core->SBRedirect($this->redirect);
        }
        else if ($move_from == $move_to) {
            $Core->SetSessionMessage(CANNOT_MOVE_TO_SAME_DIR, 'error');
            $Core->SBRedirect($this->redirect);
        }
        else if (file_exists($move_to.$name)) {
            $Core->SetSessionMessage(IMG_ALREADY_EXISTS, 'error');
            $Core->SBRedirect($this->redirect);
        }
        
        if ($Core->MoveFile($move_from.$name, $move_to.$name)) {
            $Core->SetSessionMessage(
                'Image '.$name.' was moved to '.$move_to, 
                'confirm'
            );
        } 
        else {
            $Core->SetSessionMessage(
                'Image '.$name.' could not be moved to '.$move_to, 
                'error'
            );
        }
        $Core->SBRedirect($this->redirect);
    }
    
    function DoUpLoad() {
        global $Core;
        
        if (empty($_FILES['upload']['name'][0])) {
            $Core->SetSessionMessage(NO_FILE_SELECTED, 'error');
        } 
        else if (!isset($_POST['MAX_FILE_SIZE'])) {
            $Core->SetSessionMessage(NO_MAX_FILE_SIZE, 'error');
        } 
        else {
            if (isset($_FILES['upload'])) {
                list($res, $class) = $this->UploadFiles();
                $Core->SetSessionMessage($res, $class); 
            } 
            else {
                $Core->SetSessionMessage(COULD_NOT_UPLOAD, 'error');
            }
        }
        $Core->SBRedirect($this->redirect);
    }
    
    function UploadFiles() {
        global $Core;
        global $config;

        $count = count($_FILES['upload']['name']);
                        
        $br = $count > 1 ? '<br />' : NULL ; 
        
        list($exitCodes, $newFiles) = $Core->UploadMultipleFiles(
        	$_FILES, 
        	$_POST['uploadDir'], 
        	$this->allowedTypes, 
        	5000000,
        	$this->targets
        );
        
        $res = NULL;
        $warn = 0;
        for ($i=0; $i<count($exitCodes); $i++) {
            $e = $exitCodes[$i];
            if ($e == 1) {
                $res .= basename($newFiles[$i]).WAS_UPLOADED.$br;
            } 
            else {
                $res .= basename($newFiles[$i]).WAS_NOT_UPLOADED.$br;
                $warn++;
            }
        }
        if ($warn == count($count)) {
            $class = 'error';
        }
        else if ($warn > 0) {
            $class = 'caution';
        }
        else  {
            $class = 'confirm';
        }
        return array($res, $class);
    }

    function InitDataSource() {
        return;
    }
    
    function InitObjs() {
        global $Core;
        
        if (!is_array($this->media)) $this->media = array();
        
        $subdir = $Core->GetVar($_GET, 'dir', 'all');
        $subdir = $subdir == 'all' ? NULL : $subdir ;
        
        $skindir = str_replace(SB_SITE_DATA_DIR, NULL, ACTIVE_SKIN_IMG_DIR);
        
        if ($Core->GetVar($_GET, 'dir', 'all') == 'all') {
            $this->media = array_merge($this->media, $Core->ListFiles(SB_MEDIA_DIR.$subdir));
            $this->media = array_merge($this->media, $Core->ListFiles(SB_DOWNLOADS_DIR));
            $this->media = array_merge($this->media, $Core->ListFiles(SB_UPLOADS_DIR));
            $this->media = array_merge($this->media, $Core->ListFiles(ACTIVE_SKIN_IMG_DIR));
        }
        else if (strpos($subdir, $skindir) !== FALSE) {
            $this->media = array_merge($this->media, $Core->ListFiles(ACTIVE_SKIN_IMG_DIR));
        }
        else if (strpos($subdir, 'downloads') !== FALSE) {
            $this->media = array_merge($this->media, $Core->ListFiles(SB_DOWNLOADS_DIR));
        }
        else if (strpos($subdir, 'uploads') !== FALSE) {
            $this->media = array_merge($this->media, $Core->ListFiles(SB_UPLOADS_DIR));
        }
        else {
            $this->media = array_merge($this->media, $Core->ListFiles(SB_MEDIA_DIR.$subdir));
        }
    }
    
    function LoadObj() {
        return;
    }

}

?>