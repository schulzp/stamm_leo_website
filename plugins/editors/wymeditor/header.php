<?php 

// Load the header links for Wymeditor 

global $Core;

$path = "plugins/editors/";
$csspath = ACTIVE_SKIN_DIR;

$cssname= basename(ACTIVE_SKIN_DIR);

$styleSheetPath = ACTIVE_SKIN_DIR . "css/{$cssname}.css";

$wym_path = SB_EDITORS_DIR . "wymeditor/wymeditor/";
$wym_skin = $wym_path . "skins/silver/";
$wym_plug = $wym_path . "plugins/";

$btnGadgets = "";
if (file_exists(SB_SITE_DATA_DIR . 'plugins/plugin.gadgets.php'))
{
    $btnGadgets = ",{'name': 'Gadget', 'title': 'Gadget', 'css': 'wym_tools_gadget'}\n";
}

$btnSiteVars = "";
if (file_exists(SB_SITE_DATA_DIR . 'plugins/plugin.sitevars.php'))
{
    $btnSiteVars = ",{'name': 'SiteVar', 'title': 'SiteVar', 'css': 'wym_tools_sitevars'}\n";
}

$btnAdZones = "";
$adFiles = $Core->ListFiles(SB_SITE_DATA_DIR . 'ads/');
$adCount = 0;
for ($i=0; $i<count($adFiles); $i++)
{
    $name = basename($adFiles[$i]);
    if ($name{0} != '_') $adCount++;
}
if (file_exists(SB_SITE_DATA_DIR . 'plugins/plugin.ads.php') && $adCount)
{
    $btnAdZones = ",{'name': 'AdZone', 'title': 'AdZone', 'css': 'wym_tools_adzones'}\n";
}

?>

<!--[WYMeditor]-->
<script type="text/javascript" 
        src="<?php echo $wym_path; ?>jquery.wymeditor.js">
</script>

<!-- Load jQuery Plugins -->
<script type="text/javascript" src="<?php echo $wym_path; ?>modal/jquery.simplemodal.js"></script>
<link rel="stylesheet" href="<?php echo $wym_path; ?>modal/simplemodal.css" type="text/css" media="screen"/>
<script type="text/javascript" src="<?php echo $wym_path; ?>jquery.wymeditor.overlay.js"></script>

<!--[if lt IE 7]>
<style type='text/css'>
  #modalContainer a.modalCloseImg{
    background:none;
	right:-14px;
	width:22px;
	height:26px;
    filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(
        src='img/x.png', sizingMethod='scale'
      );
  }
  #modalContainer {
    top: expression((document.documentElement.scrollTop
        || document.body.scrollTop) + Math.round(15 *
        (document.documentElement.offsetHeight
        || document.body.clientHeight) / 100) + 'px');
  }
</style>
<![endif]-->
        
<script type="text/javascript">
jQuery(function() {
    jQuery("#story_content").wymeditor({
        updateSelector:".buttonsave", 
        toolsItems:[
            {'name': 'Bold', 'title': 'Strong', 'css': 'wym_tools_strong'}, 
            {'name': 'Italic', 'title': 'Emphasis', 'css': 'wym_tools_emphasis'},
            {'name': 'Superscript', 'title': 'Superscript',
                'css': 'wym_tools_superscript'},
            {'name': 'Subscript', 'title': 'Subscript',
                'css': 'wym_tools_subscript'},
            {'name': 'InsertOrderedList', 'title': 'Ordered_List',
                'css': 'wym_tools_ordered_list'},
            {'name': 'InsertUnorderedList', 'title': 'Unordered_List',
                'css': 'wym_tools_unordered_list'},
            {'name': 'Indent', 'title': 'Indent', 'css': 'wym_tools_indent'},
            {'name': 'Outdent', 'title': 'Outdent', 'css': 'wym_tools_outdent'},
            {'name': 'Undo', 'title': 'Undo', 'css': 'wym_tools_undo'},
            {'name': 'Redo', 'title': 'Redo', 'css': 'wym_tools_redo'},
            {'name': 'CreateLink', 'title': 'Link', 'css': 'wym_tools_link'},
            {'name': 'Unlink', 'title': 'Unlink', 'css': 'wym_tools_unlink'},
            {'name': 'InsertImage', 'title': 'Image', 'css': 'wym_tools_image'},
            {'name': 'InsertTable', 'title': 'Table', 'css': 'wym_tools_table'},
            {'name': 'Paste', 'title': 'Paste_From_Word',
                'css': 'wym_tools_paste'},
            {'name': 'ToggleHtml', 'title': 'HTML', 'css': 'wym_tools_html'}
            <?php echo $btnGadgets; ?>
            <?php echo $btnSiteVars; ?>
            <?php echo $btnAdZones; ?>
        ],
        relativepath: "<?php echo WYM_RELATIVE_PATH; ?>",
        skin: 'silver',
        stylesheet: '<?php echo $styleSheetPath; ?>',
        postInit: function(wym) {
            SBC.AttachImageDblClick();
            jQuery(".wym_dropdown h2").hover(
                function() {
                    jQuery(this).css("background-position", "0px -18px"); 
                },
                function() {
                    jQuery(this).css("background-position", ""); 
                }
            );
            jQuery(".wymupdate").bind('click', wym, function(e){
                if (e.data.update) e.data.update();
            });
            jQuery('.wym_classes a').bind('click', wym, function(e){
                if (e.data.update) e.data.update();
            });
        }
    });
});
</script>
<!--[/WYMeditor]-->