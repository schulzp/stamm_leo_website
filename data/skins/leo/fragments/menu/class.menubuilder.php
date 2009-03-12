<?php
require 'class.menuitem.php';

/**
 * This class offers an interface to work with SkyBlue's page tree
 */
class MenuBuilder {
    private $menus     = null;
    private $pages     = null;
    private $pid       = null;

    /**
     * Constructor
     *
     * @param   array   pages   array of stdClass objets storing page info
     * @param   int     pid     current page id
     */
    public function MenuBuilder(&$pages, $pid) {
        $this->pid       = $pid;
        $this->pages     = $pages;
        $this->menus     = array();
    }

    /**
     * Recursively creates a tree from the pages array
     *
     * @param   MenuItem parent  MenuItem object under which the tree is build
     * @param   int      menuId  menu id (belonging to the current page)
     */
    private function buildTree(MenuItem &$parent, $menuId) {
        foreach ($this->pages as $page) {
            if ($page->parent == $parent->get('id') && $page->menu == $menuId) {
                $child = $parent->addChild($page);
                $this->buildTree($child, $menuId);

                if ($this->pid == $page->id) {
                    $child->setCurent();
                    $this->menus[$menuId]->setCurrentItem($child);
                }
            }
        }
    }

    /**
     * Returns an unordered HTML list with linked items.
     * Active list entries (li) have a css-class 'active'
     * The item belonging to the current page is not a link,
     * istaead the item-title is wrapped in a <strong> tag.
     *
     * @param   int     menuId
     * @param   int     offset  menu layers to skip
     * @param   int     depth   layers to draw
     * @return  str             an unorded HTML list with links
     */
    public function getHTML($menuid, $offset = 0, $depth = 10) {
        global $Core;

        $this->loadTree($menuid);

        return $Core->HTML->MakeElement(
            'ul',
            array(),
            $this->renderTree($this->menus[$menuid], $offset, $depth)
        );
    }

    /**
     * Returns current branch of links as HTML ("You're here: ...").
     * The current item again is not a link, instead is wrapped in
     * a <span> tag.
     *
     * @param   int     menuId
     * @param   str     seperator       this is placed between the links
     * @return  str                     HTML links
     */
    public function getBreadcrumenHTML($menuid, $seperator) {
        global $Core;
        global $Router;

        $this->loadTree($menuid);
        $currentItem = $this->menus[$menuid]->getCurrentItem();
        $activeItems = $currentItem->getParents();
        array_unshift($activeItems, $currentItem);

        $html = '';

        foreach (array_reverse($activeItems) as $item) {
            if ($item->isCurrent()) {
                $html .= $Core->HTML->MakeElement(
                    'span',
                    array(),
                    $item->get('title')
                );
            } else {
                $html .= $Core->HTML->MakeElement(
                    'a',
                    array('href' => $Router->GetLink($item->get('id'))),
                    $item->get('title')
                ) . $seperator;
            }
        }

        return $html;
    }

    /**
     * Ensure the tree is build only once for each menu
     */
    private function loadTree($menuid) {
        if (!array_key_exists($menuid, $this->menus)) {
            $this->menus[$menuid] = new MenuRoot();
            $this->buildTree($this->menus[$menuid], $menuid);
        }
    }

    /**
     * Core routine to transform a tree into HTML
     *
     * @see #getHtml
     */
    private function renderTree($item, $offset, $depth) {
        global $Core;
        global $Router;

        $attribs = array();
        $sublist = '';

        if ($item->isActive() && $item->hasChildren() && ($offset + $depth) > 0) {

            foreach ($item->getChildren() as $child) {
                $sublist .= $this->renderTree($child, $offset-1, $depth-1);
            }

            if (is_null($item->get('title')) || $offset > 0) return $sublist;

            $sublist = $Core->HTML->MakeElement(
                'ul',
                array(),
                $sublist
            );
        }

        if ($offset > 0) return '';

        // add css class 'active' to the LI containing current link
        if ($item->isActive())
            $attribs['class'] = 'active';

        // don't create a to the page we're currently viewing
        if ($item->isCurrent())
            $link = $Core->HTML->MakeElement('strong', array(), $item->get('title'));
        else
            $link = $Core->HTML->MakeElement(
                'a',
                array('href' => $Router->GetLink($item->get('id'))),
                ucwords($item->get('title'))
            );


        return $Core->HTML->MakeElement('li', $attribs, $link . "\n" . $sublist);
    }
}

?>
