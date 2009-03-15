<?php

$page    = $Core->SelectObj($data, $Core->GetVar($_GET, 'pid', DEFAULT_PAGE));
$builder = MenuHelper::GetBuilder($data, $page);

?>

<div class="hlist">
<?php echo $builder->getHTML($page->menu, (int)$params['offset'], (int)$params['depth']); ?>
</div>
