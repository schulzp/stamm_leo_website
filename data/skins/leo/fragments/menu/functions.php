<?php

class MenuHelper {

    private $data;
    private $item;
    private static $parentIds = array();
    private static $menuIds = array();

    public function MenuHelper(&$data, $item) {
        $this->data      = $data;
        $this->item      = $item;

        $this->setParentIds();
    }
  
    public function isCurrent(&$item) {
        return ($this->item->id == $item->id);
    }

    public function isActive(&$item) {
        return ($this->isCurrent($item) || in_array($item->id, $this->getParentIds()));
    }

    public function getLink(&$item) {
        global $Router;
        if ($this->isCurrent($item))
            return '<strong>' . $item->name . '</strong>';

        return '<a href="' . $Router->GetLink($item->id) . '">' . $item->name . '</a>';
    }

    public function isValid(&$item) {
        return (!empty($item->menu) && $item->published);
    }

    public function getParentIds() {
        return MenuHelper::$parentIds[$this->item->id];
    }

    public function getMenuIds() {
        return MenuHelper::$menuIds[$this->item->id];
    }

    public function getBreadcrumen() {
        return array($item->id) + $this->getParentIds();
    }


    /**
     * Initialize +parentIds+ with ids of the parent objects of +item+
     */
    private function setParentIds() {
        global $Core;

        $parentIds = array();
        $menuIds   = array();
        $item      = $this->item;

        while ($item) {
            if (!empty($item->parent)) {
                array_push($parentIds, $item->parent);
            }

            array_push($menuIds, $item->menu);

            foreach ($this->data as $_item) {
                if ($_item->parent == $item->id) {
                    array_push($menuIds, $_item->menu);
                    break;
                }
            }


            $item = $Core->SelectObj($this->data, $item->parent);
        }

        MenuHelper::$parentIds[$this->item->id] = $parentIds;
        MenuHelper::$menuIds[$this->item->id]   = $menuIds;
    } 
 
    public static function staticParentIds($id) {
        if (array_key_exists($id, MenuHelper::$parentIds))
            return MenuHelper::$parentIds[$id];

        return array();
    }

    public static function staticBreadcrumen($id) {
        $parentIds = MenuHelper::staticParentIds($id);
        array_unshift($parentIds, $id);
        return $parentIds; 
    }
}

?>
