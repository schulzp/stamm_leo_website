<?php

global $Filter;

$page    = $Core->SelectObj($data, $Filter->get($_GET, 'pid', DEFAULT_PAGE));
$builder = MenuHelper::GetBuilder($data, $page);

?>

<div class="hlist">
<?php echo $builder->getHTML($page->menu, (int)$params['offset'], (int)$params['depth']); ?>
</div>
