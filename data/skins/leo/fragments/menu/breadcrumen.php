<?php 
$page = $Core->SelectObj($data, $Core->GetVar($_GET, 'pid', DEFAULT_PAGE));

echo MenuHelper::GetBuilder($data, $page)->getBreadcrumenHTML($page->menu, ' &gt; ');
?>
