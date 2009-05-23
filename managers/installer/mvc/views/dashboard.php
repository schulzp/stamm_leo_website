<?php

/**
* @version      RC 1.1 2008-12-12 19:47:43 $
* @package      SkyBlueCanvas
* @copyright    Copyright (C) 2005 - 2008 Scott Edwin Lewis. All rights reserved.
* @license      GNU/GPL, see COPYING.txt
* SkyBlueCanvas is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYING.txt for copyright notices and details.
*/

defined('SKYBLUE') or die(basename(__FILE__));

$Request = new RequestObject;
$Filter = new Filter;
$com = $Request->get('com', 'manager');

?>
<style type="text/css">
    @import "managers/installer/styles.css";
</style>
<script type="text/javascript">
function hide_mgr_message() {
    $("#mgr-message").fadeOut("slow");
};
$(function() {
    setTimeout('hide_mgr_message()', 5000);
});
$(function() {
    $("#install_button").bind("click", function(e) {
        if ($("#upload_file").val() == "") {
            e.preventDefault();
        }
    });
    $("#new-button").bind("click", function(e) {
        e.preventDefault();
        $("#new-button").toggle("slow", function() {
            $("#install-form").toggle("slow");
        });
    });
    $("#cancel-button").bind("click", function(e) {
        e.preventDefault();
        $("#install-form").toggle("slow", function() {
            $("#new-button").toggle("slow");
        });
    });
});
</script>
<?php if ($message = $this->getMessage()) : ?>
<div id="mgr-message" class="msg-<?php echo $Filter->get($message, 'type'); ?>">
<h2><?php echo $Filter->get($message, 'title'); ?></h2>
<p><?php echo $Filter->get($message, 'message'); ?></p>
</div>
<?php endif; ?>
<div class="dash-controls">
    <div id="install-form-div">
        <div id="new-button">
            <button name="new" id="new-button" class="button" onclick="javascript:void(0);">Install New</button>
        </div>
        <form method="post" action="<?php echo INSTALLER_URL . '&com=' . $com ?>" id="install-form" enctype="multipart/form-data">
            <fieldset>
                <h2>Install A New <?php echo ucwords($com); ?></h2>
                <input type="hidden" name="com" value="<?php echo $com; ?>" />
                <input type="hidden" name="action" value="upload" />
                <input type="file" name="package" size="22" id="upload_file" />
                <input type="submit" name="action" value="Install" class="button" id="install_button" />&nbsp;
                <input type="submit" name="action" value="cancel" class="button" id="cancel-button" />
            </fieldset>
        </form>
        <div class="clear"></div>
    </div>
    <ul class="tabs">
        <li><a href="<?php echo INSTALLER_URL; ?>&com=manager" class="<?php echo $com == 'manager' ? 'active' : 'off'; ?>">Managers</a></li>
        <li><a href="<?php echo INSTALLER_URL; ?>&com=module" class="<?php echo $com == 'module' ? 'active' : 'off'; ?>">Modules</a></li>
        <li><a href="<?php echo INSTALLER_URL; ?>&com=skin" class="<?php echo $com == 'skin' ? 'active' : 'off'; ?>">Skins</a></li>
        <li><a href="<?php echo INSTALLER_URL; ?>&com=fragment" class="<?php echo $com == 'fragment' ? 'active' : 'off'; ?>">Fragments</a></li>
    </ul>
</div>
<div style="clear: both;"></div>
<table callpadding="0" cellspacing="0" class="linkstable">
<tr>
    <th>Name</th>
    <th>Tasks</th>
</tr>
<?php if (!count($this->data)) : ?>
    <tr>
        <td colspan="2">No items to display</td>
    </tr>
<?php else : ?>
<?php for ($i=0; $i<count($this->data); $i++) : ?>
    <tr class="<?php echo $i % 2 == 0 ? 'even-row' : 'odd-row'; ?>">
        <td width="90%">
            <?php echo $this->data[$i]; /*  . " " . $Request->get('com', 'manager') */ ?>
        </td>
        <td width="10%">
            <a href="<?php echo INSTALLER_URL; ?>&com=<?php echo $com; ?>&action=delete&item=<?php echo $this->data[$i]; ?>" 
               onclick="return confirmDelete('<?php echo 'the ' . $this->data[$i] . ' ' . $com; ?>');">
                <img src="ui/admin/images/task_delete.gif" 
                     title="Un-install <?php echo $this->data[$i] . ' ' . $com; ?>" 
                     alt="Un-install <?php echo $this->data[$i] . ' ' . $com; ?>" 
                     style="border: none; position: relative; top: 3px;"
                     />
            </a>
        </td>
    </tr>
<?php endfor; ?>
<?php endif; ?>
</table>