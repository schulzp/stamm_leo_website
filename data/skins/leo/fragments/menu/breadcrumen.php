<?php 
global $Filter;

$page = $Core->SelectObj($data, $Filter->get($_GET, 'pid', DEFAULT_PAGE));

echo MenuHelper::GetBuilder($data, $page)->getBreadcrumenHTML($page->menu, ' &gt; ');
?>
