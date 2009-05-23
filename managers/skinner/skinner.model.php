<?php

defined('SKYBLUE') or die(basename(__FILE__));

class skinner_model {

    var $id           = null;
    var $name         = null;
    var $content      = null;
    var $cantarget    = 0;
    var $group        = 'templates';
    var $bundletype   = null;
    var $bundlesource = null;
    var $objtype      = null;
    var $loadas       = null;
    
    function __construct() {
        ;
    }
    
    function skinner_model() {
        $this->__construct();
    }

}

?>