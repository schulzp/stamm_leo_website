<?php

/**
* @version        RC 1.1 2008-12-12 19:47:43 $
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

class SkinController extends Controller {

    var $model;
    var $action;
    var $activeskin;
    var $viewName;
    var $pages;
    var $view_path = 'managers/skinner/mvc/views/';

    function __construct($Request) {
        parent::__construct($Request);
        $this->addActionHandler('index',    'doIndex');
        $this->addActionHandler('delete',   'doDelete');
        $this->addActionHandler('install',  'doSave');
        $this->addActionHandler('activate', 'doActivate');
        $this->addActionHandler('showmap',  'showMap');
        $this->addActionHandler('update',   'doUpdate');
        $this->addActionHandler('cancel',   'doCancel');
        $this->setActiveSkin($this->_getActiveSkin());
        $this->setPages($this->_getPages());
    }
    
    function SkinController($Request) {
        $this->__construct($Request);
    }
    
    function display() {
        $this->view->assign('activeskin', $this->getActiveSkin());
        $viewName = $this->getViewName();
        if (empty($viewName)) {
            $this->view->assign('view.name', 'Dashboard');
            $this->view->setView('dashboard');
        }
        else {
            $this->view->setView($viewName);
        }
        parent::display();
    }

    function doIndex() {
        $this->model->index();
    }
    
    function doActivate() {
        global $Core;
        $Filter = new Filter;
        $item = $Filter->get($this->request, 'item');
        
        list($old_diff, $new_diff) = $this->_getLayoutTypesDiff($item);
        
        if (count($old_diff) && count($new_diff)) {
            $this->_showLayoutMap($item, $old_diff, $new_diff);
        }
        else {
            $this->_activateSkin($item);
        }
    }
    
    function doCancel() {
        $this->_setMessage(
			'info',
			'Note',
			'User cancelled. No changes were saved'
		);
        Core::SBRedirect(SKINNER_URL . "&com=skin");
    }
    
    function doDelete($Request) {
        $item = $Request->get('item', null);
        $this->model->index();
        if (count($this->model->getData()) == 1) {
            $this->_setMessage(
                'error',
                'Oops!',
                "You cannot delete the selected skin because it is the only one installed."
            );
        }
        else if ($this->getActiveSkin() == $item) {
            $this->_setMessage(
                'error',
                'Oops!',
                "You cannot delete the active skin."
            );
        }
        else if ($this->model->delete($item)) {
            $this->_setMessage(
                'success',
                'Success!',
                $item . " Skin was successfully deleted."
            );
        }
        else {
            $this->_setMessage(
                'error',
                'Oops!',
                $item . " Skin could not be deleted."
            );
        }
        Core::SBRedirect(SKINNER_URL . "&com=skin");
    }
    
    function doSave($Request) {
        $Filter = new Filter;
        $package = @$_FILES['package'];
        $item = $Filter->get($package, 'name', null);
        if ($this->model->save($package)) {
            $this->_setMessage(
                'success',
                'Success!',
                $item . " Skin was successfully installed."
            );
        }
        else {
            $this->_setMessage(
                'error',
                'Oops!',
                $item . " Skin could not be installed."
            );
        }
        Core::SBRedirect(SKINNER_URL . "&com=skin");
    }
    
    function _activateSkin($name) {
        global $Core;
        $model = MVC::getModel('activeskin');
        if ($model->activateSkin($name)) {
            $this->_setMessage(
                'success',
                'Success!',
                $name . " was successfully activated."
            );
        }
        else {
            $this->_setMessage(
                'error',
                'Oops!',
                $name . " could not be activated"
            );
        }
        Core::SBRedirect(SKINNER_URL . "&com=skin");
    }
    
    function doUpdate() {
        global $Core;
        $Filter = new Filter;
        $model = MVC::getModel('page');
        $new_skin = $Filter->get($_POST, 'name');
        $old_layouts = $Filter->get($_POST, 'old_layouts');
        $new_layouts = $Filter->get($_POST, 'new_layouts');
        $pages = $this->getPages();
        for ($i=0; $i<count($old_layouts); $i++) {
			for ($x=0; $x<count($pages); $x++) {
				$page =& $pages[$x];
				if ($page->pagetype == $old_layouts[$i]) {
				    $page->pagetype = $new_layouts[$i];
				}
			}
	    }
	    $model->setData($pages);
	    if ($model->save()) {
	        $this->_activateSkin($new_skin);
	    }
	    else {
	        $this->_setMessage(
                'error',
                'Oops!',
                $new_skin . " could not be activated"
            );
	        Core::SBRedirect(SKINNER_URL . "&com=skin");
	    }
    }
    
    function getActiveSkin() {
        return $this->activeskin;
    }
    
    function _getActiveSkin() {
        $model = MVC::getModel('activeskin');
        return $model->getActiveSkin();
    }
    
    function setActiveSkin($activeskin) {
        $this->activeskin = $activeskin;
    }
    
    function _showLayoutMap($skin_name, $old_diff, $new_diff) {
        $this->view->assign('old_layouts', $old_diff);
        $this->view->assign('new_layouts', $new_diff);
        $this->view->assign('skin_name',   $skin_name);
        $this->setViewName('layout.map');
    }
    
    function _getPages() {
        $model = MVC::getModel('page');
        $model->index();
        return $model->getData();
        $dao = new PageDAO;
        $dao->index();
        return $dao->getData();
    }
    
    function setPages($pages) {
        $this->pages = $pages;
    }
    
    function getPages() {
        return $this->pages;
    }
    
    function _getOldLayouts() {
        $pages = $this->getPages();
        $layouts = array();
        foreach ($pages as $page) {
            if (!in_array($page->pagetype, $layouts)) {
                array_push($layouts, $page->pagetype);
            }
        }
        return $layouts;
    }
    
    function _getNewLayouts($skin_name) {
        $layouts = array();
        $files = FileSystem::list_files(SB_SKINS_DIR . "$skin_name/");
        for ($i=0; $i<count($files); $i++) {
            $file = basename($files[$i]);
            if (substr($file, 0, 4) == 'skin') {
                array_push($layouts, str_replace(array('skin.', '.html'), null, $file));
            }
        }
        return $layouts;
    }
    
    function setViewName($viewName) {
        $this->viewName = $viewName;
    }
    
    function getViewName() {
        return $this->viewName;
    }
    
    function _getLayoutTypesDiff($skin_name) {
        $old_layouts = $this->_getOldLayouts();
        $new_layouts = $this->_getNewLayouts($skin_name);
        
        $old_diff = array();
        $new_diff = array();
        
        for ($i=0; $i<count($old_layouts); $i++) {
            if (!in_array($old_layouts[$i], $new_layouts)) {
                array_push($old_diff, $old_layouts[$i]);
            }
        }
        
        for ($i=0; $i<count($new_layouts); $i++) {
            array_push($new_diff, $new_layouts[$i]);
        }
        return array($old_diff, $new_diff);
    }

}

?>