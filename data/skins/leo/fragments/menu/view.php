<?php

global $Router; 
global $Filter;

$currMenu   = $Core->SelectObj($Core->xmlHandler->ParserMain(SB_XML_DIR . 'menus.xml'), (int)$params['menu']);
$currItem   = $Core->SelectObj($data, $Filter->get($_GET, 'pid', DEFAULT_PAGE));

$helper     = new MenuHelper($data, $currItem);

?>
<?php if (in_array($params['menu'], $helper->getMenuIds())) : ?>
<div class="hlist">
    <?php if (count($data)) : ?>
        <ul>
            <?php foreach ($data as $item) : ?>
                <?php if (!$helper->isValid($item)) continue; ?>
                <?php if ($item->menu != $currMenu->id) continue; ?>
                <li<?php echo ($helper->isActive($item) ? ' class="active"' : '') ?>>
                    <?php echo $helper->getLink($item); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
<?php endif; ?>
