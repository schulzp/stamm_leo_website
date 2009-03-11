<?php 
global $Filter;
global $Router;

$pageId = $Filter->get($_GET, 'pid', DEFAULT_PAGE);

foreach (array_reverse(MenuHelper::staticBreadcrumen($pageId)) as $id) {
    $item = $Core->SelectObj($data, $id);
    $url  = $Router->GetLink($id);

    if ($item->id == $pageId)
        echo "<span>{$item->name}</span>";
    else
        echo "<a href=\"{$url}\">{$item->name}</a> &middot; ";
}
?>
