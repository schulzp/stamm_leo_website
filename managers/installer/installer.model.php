<?php

defined('SKYBLUE') or die(basename(__FILE__));

class installer_model {

    var $id           = null;
    var $name         = null;
    var $content      = null;
    var $cantarget    = 0;
    var $group        = 'collections';
    var $bundletype   = null;
    var $bundlesource = null;
    var $objtype      = null;
    var $loadas       = null;
    
    function __construct() {
        ;
    }
    
    function installer_model() {
        $this->__construct();
    }

}

?>