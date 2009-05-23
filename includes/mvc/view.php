<?php

class View extends Observer {
    
    var $data;
    var $tokens;
    var $vars;
    var $view;
    var $view_name;
    var $model;
    var $message;
    
    function __construct($model=null, $path=null) {
        $this->path = $path;
        $this->model = $model;
        $this->tokens = array();
        $this->setData($this->model->getData());
    }
    
    function View($model=null, $path=null) {
        $this->__construct($model, $path);
    }
    
    function setView($name) {
        $this->view_name = $name;
        $this->view = $this->buffer_view(
        	"{$this->path}{$this->view_name}.php"
        );
    }
    
    function setData($data) {
        $this->data = $data;
    }
    
    function setModel(&$model) {
        $this->model = $model;
    }
    
    function display() {
        $this->resolveVars();
        return $this->view;
    }
    
    function buffer_view($view_file) {
        ob_start();
        include($view_file);
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }
    
    function setMessage($message) {
        $this->message = $message;
    }
    
    function getMessage() {
        return $this->message;
    }
    
    function assign($token, $value) {
        $this->tokens[$token] = $value;
    }
    
    function resolveVars() {
        if (!count($this->tokens)) return;
        foreach ($this->tokens as $token=>$value) {
            if (is_array($value)) continue;
            $this->view = str_replace("[[$token]]", $value, $this->view);
        }
    }

    function getVar($key, $default=null) {
        if (isset($this->tokens[$key])) {
			return $this->tokens[$key];
		}
		return $default;
    }
    
}

?>