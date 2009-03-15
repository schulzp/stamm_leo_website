<?php
require 'class.menuitem.php';

/**
 * This class offers an interface to work with SkyBlue's page tree
 */

class MenuBuilder {
    private $DEFAULT_OPTIONS  = array(
        'depth'         => 100,
        'offset'        => 0,
        'link_current'  => false,
        'show_all'      => false,
        'active_class'  => 'active',
        'separator'     => ' &gt; '
    );

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
     * @param   MenuItem parent         MenuItem object under which the tree is build
     * @param   array    pageIdx        indices of pages for this tree
     */
    private function buildTree(MenuItem &$parent, $pageIdx) {
        foreach ($pageIdx as $i) {
            if ($this->pages[$i]->parent == $parent->get('id')) {
                $child = $parent->addChild($this->pages[$i]);
                $this->buildTree($child, array_diff($pageIdx, array($i)));

                if ($this->pid == $this->pages[$i]->id) {
                    $parent->getRoot()->setCurrentItem($child);
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
     * @param   array   options see DEFAULT_OPTIONS
     * @return  str             an unorded HTML list with links
     */
    public function getHTML($menuid, $options) {
        global $Core;

        $options = array_merge($this->DEFAULT_OPTIONS, $options);

        $this->loadTree($menuid);

        return $Core->HTML->MakeElement(
            'ul',
            array(),
            $this->renderTree($this->menus[$menuid], $options['offset'], $options['depth'], $options)
        );
    }

    /**
     * Returns current branch of links as HTML ("You're here: ...").
     * The current item again is not a link, instead is wrapped in
     * a <span> tag.
     *
     * @param   int     menuId
     * @param   array   options         valid keys are separator and link_current
     * @return  str                     HTML links
     */
    public function getBreadcrumenHTML($menuid, $options = array()) {
        global $Core;
        global $Router;

        $options = array_merge($this->DEFAULT_OPTIONS, $options);

        $this->loadTree($menuid);
        $currentItem = $this->menus[$menuid]->getCurrentItem();
        $activeItems = $currentItem->getParents(true);

        $html = '';

        foreach (array_reverse($activeItems) as $item) {
            if ($item->isCurrent() && !$options['link_current']) {
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
                ) . $options['separator'];
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
            $idx = array();
            for ($i = 0; $i < count($this->pages); $i++) {
                if ($this->pages[$i]->menu == $menuid && $this->pages[$i]->published == '1')
                    array_push($idx, $i);
            }
            $this->buildTree($this->menus[$menuid], $idx);
        }
    }

    /**
     * Core routine to transform a tree into HTML
     *
     * @see #getHtml
     */
    private function renderTree($item, $offset, $depth, &$options) {
        global $Core;
        global $Router;

        $attribs = array();
        $sublist = '';
        $active  = $options['show_all'] ? true : $item->isActive();

        if ($active && $item->hasChildren() && ($offset + $depth + 1) > 0) {

            foreach ($item->getChildren() as $child) {
                $sublist .= $this->renderTree($child, $offset-1, $depth-1, $options);
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
            $attribs['class'] = $options['active_class'];

        // don't create a to the page we're currently viewing
        if ($item->isCurrent() && !$options['link_current'])
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
