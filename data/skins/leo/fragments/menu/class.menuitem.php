<?php
/**
 * MenuItem objects represent a page in SkyBlue's page tree.
 */
class MenuItem {

    private $id    = null;
    private $title = null;

    private $isActive  = false;
    private $isCurrent = false;

    private $parent   = null;
    private $children = array();

    /**
     * Constructor
     *
     * @param   MenuItem        parent  a reference to the item's parent item
     * @param   stdClass        page    the page this item wraps
     */
    public function MenuItem(MenuItem &$parent, &$page)
    {
        $this->id     = $page->id;
        $this->title  = $page->name;
        $this->parent =& $parent;
    }

    /**
     * Adds a new item created from +page+ to the item's children
     *
     * @param   stdClass        page    the page the child item wraps
     * @return  MenuItem                the new child item
     */
    public function addChild(&$page) {
        $child =& new MenuItem($this, $page);
        array_push($this->children, $child);
        return $child;
    }


    /**
     * Returns +true+ if the item has children
     *
     * @return  boolean                 true if child-count > 0
     */
    public function hasChildren() {
        return count($this->children) > 0;
    }

    /**
     * Returns +true+ if the parent is not +null+
     *
     * @return  boolean                 true if the item has a parent item
     */
    public function hasParent() {
        return !is_null($this->parent);
    }

    /**
     * Getter
     */
    public function get($prop, $default=null) {
        if (isset($this->$prop)) return $this->$prop;
        return $default;
    }

    /**
     * Returns array of the item's children items
     *
     * @return  array                   array of MenuItem objects
     */
    public function getChildren() {
        return $this->children;
    }

    /**
     * Returns the item's parent item unless overridden
     *
     * @see MenuRoot
     * @return  MenuItem                a MenuItem object
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * Returns all parent items
     *
     * @param   boolean         includeSelf     if true the item itself
     * @return  array                           array of MenuItem objects
     */
    public function getParents($includeSelf = false) {
        $parents = array();
        $item    = $includeSelf ? $this : $this->parent;

        while ($item->hasParent()) {
            array_push($parents, $item);
            $item = $item->getParent();
        }

        return $parents;
    }

    /**
     * Sets isActive token to true
     */
    public function setActive() {
        $this->isActive = true;
    }

    /**
     * Sets isCurrent token to true and marks
     * all its parents as active
     */
    public function setCurent() {
        $this->setActive();
        $this->isCurrent = true;

        foreach ($this->getParents() as $item) {
            $item->setActive();
        }
    }

    public function isActive() {
        return $this->isActive;
    }

    public function isCurrent() {
        return $this->isCurrent;
    }
}


/**
 * Special class which objects are intended to serve as root
 * element for all MenuItem-based trees.
 */
class MenuRoot extends MenuItem {

    private $currentItem = null;

    /**
     * Constructor
     */
    public function MenuRoot() {
        $this->id = '';
        $this->setActive();
        $this->setCurrentItem($this);
    }

    /**
     * Possebility to store an item
     */
    public function setCurrentItem(MenuItem &$item) {
        $this->currentItem = $item;
    }

    /**
     * Returns the item stored via MenuRoot#setCurrentItem
     *
     * @return  MenuItem        this defaults to the item itself
     */
    public function getCurrentItem() {
        return $this->currentItem;
    }
}

?>
