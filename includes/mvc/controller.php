<?php

/**
* @version        RC 1.1 2008-12-12 19:47:43 $
* @package        SkyBlueCanvas
* @copyright      Copyright (C) 2005 - 2008 Scott Edwin Lewis. All rights reserved.
* @license        GNU/GPL, see COPYING.txt
* SkyBlueCanvas is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYING.txt for copyright notices and details.
*/

defined('SKYBLUE') or die(basename(__FILE__));

class Controller extends Observer {

    var $request;
    var $model;
    var $view;
    var $action;
    var $methods;
    var $action_map;

    function __construct($Request) {
        $this->_getMethods();
        $this->setRequest($Request);
        $this->setModel(MVC::getModel($Request->get('com')));
        $this->setView(new View($this->getModel(), $this->view_path));
    }

    function Controller($Request) {
        $this->__construct($Request);
    }
    
    function setModel(&$model) {
        $this->model = $model;
    }
    
    function getModel() {
        return $this->model;
    }
    
    function setView(&$view) {
        $this->view = $view;
        $this->view->setMessage($this->getMessage());
    }
    
    function setRequest($Request) {
        $this->request = $Request;
    }
    
    function index() {
        /* Must be defined by child class */
    }
        
    function display() {
        echo $this->view->display();
    }
    
    function addActionHandler($action, $callback) {
        $this->action_map[strtolower($action)] = $callback;
    }
    
    function _addMethod($method) {
        array_push($this->methods, strtolower($method));
    }
    
    function _getMethod() {
        $method = strtolower($this->request->get('action', 'index'));
        if (isset($this->action_map[$method])) {
            $method = strtolower($this->action_map[$method]);
        }
        return $method;
    }
    
    function _getMethods() {
        $this->methods = array();
        $methods = get_class_methods(get_class($this));
        foreach ($methods as $method) {
            if (substr($method, 0, 1) != '_') {
                $this->_addMethod($method);
                $this->addActionHandler($method, $method);
            }
        }
    }

    function execute() {
    
        $ResultObject = null;
    
        $method = $this->_getMethod();
        $this->action = $method;
        
        if ($this->_callable($this->action)) {
			if ($this->_authorize($this->action)) {
				$ResultObject = $this->$method($this->request);
				$this->view->setData($this->model->getData());
			}
			else {
				$this->_setMessage(
					'error', 
					'Permission Denied', 
					'You do not have sufficient privileges to perform the requested action'
				);
			}
        }
        else {
			$this->_setMessage(
				'error', 
				'Invalid Request', 
				'The requested action is not valid'
			);
        }

        return $ResultObject;
    }
    
    function _authorize($action) {
        return true;
    }
    
    function _callable($method) {
        if (in_array($method, $this->methods) && 
            is_callable(array($this, $method))) {
            return true;
        }
        return false;
    }
    
    function _call($method, $args) {
        $this->$method($args);
    }
    
    function _getMessage() {
        $message = null;
        if (isset($_SESSION[__CLASS__.'.message'])) {
            $message = $_SESSION[__CLASS__.'.message'];
        }
        $_SESSION[__CLASS__.'.message'] = "";
        return $message;
    }
    
    function _setMessage($type, $title, $message) {
        $_SESSION[__CLASS__.'.message'] = array(
            'type'=>$type, 
            'title' => $title,
            'message'=>$message
        );
    }
    
    function getMessage() {
        return $this->_getMessage();
    }
    
}

?>