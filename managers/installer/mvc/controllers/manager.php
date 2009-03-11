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

class ManagerController extends Controller {

    var $model;
    var $action;
    var $view_path = 'managers/installer/mvc/views/';
    
    var $core_objects = array(
        'bundle',
        'configuration',
        'installer',
        'login',
        'media',
        'menus',
        'page',
        'password',
        'skinner',
        'email',
        'meta'
    );

    function __construct($Request) {
        parent::__construct($Request);
        $this->addActionHandler('index',   'doIndex');
        $this->addActionHandler('delete',  'doDelete');
        $this->addActionHandler('install', 'doSave');
    }
    
    function ManagerController($Request) {
        $this->__construct($Request);
    }
    
    function display() {
        $this->view->assign('view.name', 'Dashboard');
        $this->view->setView('dashboard');
        parent::display();
    }

    function doIndex() {
        $this->model->index();
        $data = $this->model->getData();
        $filtered = array();
        for ($i=0; $i<count($data); $i++) {
            if (!$this->is_core($data[$i])) {
                array_push($filtered, $data[$i]);
            }
        }
        $this->model->setData($filtered);
    }
    
    function doDelete($Request) {
        $item = $Request->get('item', null);
        if ($this->is_core($item)) {
            $this->_setMessage(
                'error',
                'Oops!',
                $item . " Manager could not be deleted because it is required."
            );
        }
        else if ($this->model->delete($item)) {
            $this->_setMessage(
                'success',
                'Success!',
                $item . " Manager was successfully deleted."
            );
        }
        else {
            $this->_setMessage(
                'error',
                'Oops!',
                $item . " Manager could not be deleted."
            );
        }
        Core::SBRedirect(INSTALLER_URL . "&com=manager");
    }
    
    function doSave($Request) {
        $Filter = new Filter;
        $package = @$_FILES['package'];
        $item = $Filter->get($package, 'name', null);
        if ($this->model->save($package)) {
            $this->_setMessage(
                'success',
                'Success!',
                $item . " Manager was successfully installed."
            );
        }
        else {
            $this->_setMessage(
                'error',
                'Oops!',
                $item . " Manager could not be installed."
            );
        }
        Core::SBRedirect(INSTALLER_URL . "&com=manager");
    }
    
    function is_core($name) {
        return in_array($name, $this->core_objects);
    }
    
}

?>