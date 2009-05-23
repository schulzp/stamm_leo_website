<?php

/*
* The RequestObject encapsulates requests so that specific parameters 
* do not need to be known by the various classes in the request chain.
*/

class RequestObject extends SkyBlueObject {
    function __construct() {
        foreach ($_REQUEST as $k=>$v) {
            $this->set($k, $v);
        }
    }
    
    function RequestObject() {
        $this->__construct();
    }
}

?>