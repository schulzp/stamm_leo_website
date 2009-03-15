<?php $pid = $Core->GetVar($_GET, 'pid', DEFAULT_PAGE); ?>

<?php foreach (BlockHelper::getBlocks($params['menuid']) as $block) : ?>
<?php    if (in_array($pid, split(',', $block->pages))) : ?>
    <div class="col_box">
        <h1><?php echo $block->title; ?></h1>
        <div class="col_box_content">
            <?php echo  $Core->trigger('OnAfterFragments', FileSystem::read_file(SB_STORY_DIR . $block->story)); ?>
        </div>
    </div>
<?php    endif; ?>
<?php endforeach; ?>
