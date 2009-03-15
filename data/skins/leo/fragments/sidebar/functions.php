<?php

class BlockHelper {

    static function getBlocks($menuId) {
        global $Core;
        static $allBlocks;
        static $blocks;

        if (!isset($allBlocks))
            $allBlocks = $Core->xmlHandler->ParserMain(SB_BLOCK_FILE);

        if (!isset($blocks))
            $blocks = array();

        if (!array_key_exists($menuId, $blocks)) {
            $blocks[$menuId] = array();

            foreach($allBlocks as $block) {
                if ($block->published == '1' && $block->menu == $menuId)
                    $blocks[$menuId][] = $block;
            }
        }

        return $blocks[$menuId];
    }
}

?>
