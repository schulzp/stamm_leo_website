<?php
require 'class.menubuilder.php';

class MenuHelper {

    public static function GetBuilder(&$data, &$page) {
        static $builder;

        if (!is_a($builder, 'MenuBuilder'))
            $builder = new MenuBuilder($data, $page->id);

        return $builder;
    }
}

?>
