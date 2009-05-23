<?php

define('SKYBLUE', 1);

define('SB_BASE_PATH', str_repeat('../', 1));
include(SB_BASE_PATH.'includes/conf.functions.php');
include(SB_BASE_PATH.'configs/server.consts.php');
include(SB_BASE_PATH.'configs/dirs.consts.php');
include(SB_BASE_PATH.'configs/files.consts.php');
include(SB_BASE_PATH.'configs/strings.consts.php');
include(SB_BASE_PATH.'configs/tokens.consts.php');
include(SB_BASE_PATH.'configs/regex.consts.php');
include(SB_BASE_PATH.'includes/object.class.php');
include(SB_BASE_PATH.'includes/observer.class.php');
include(SB_BASE_PATH.'includes/error.class.php');
include(SB_BASE_PATH.'includes/core.php');
require_once(SB_BASE_PATH.'includes/filesystem.php');
require_once(SB_BASE_PATH.'includes/cache.php');
require_once(SB_BASE_PATH.'includes/filter.php');
require_once(SB_BASE_PATH.'includes/xml.parser.php');
require_once(SB_BASE_PATH.'includes/router.php');
require_once(SB_BASE_PATH.'includes/request.php');

$Core = new Core;

if (!$Core->ValidateRequest("upload_image", true)) {
     die ("<h2>Your session has expired. Please log in.</h2>");
}

$index = $Core->GetVar($_GET, 'index', '0');

$files = $Core->ListFilesOptionalRecurse(SB_SITE_DATA_DIR . 'ads/', 0);

$adzones = null;
for ($i=0; $i<count($files); $i++)
{
    $name = basename($files[$i]);
    if ($name{0} == '_') continue;
    $bits = explode('.', $name);
    $zone = $bits[0];
    $adzones .= '<option value="'.$zone.'">'.$zone.'</option>'."\n";
}

?>
<?php if (empty($adzones)) : ?>
<div>
  <p>Either you do not have any ads created or all of 
  your ads are currently disabled. To use this tool please 
  create and publish at least one ad in <br />
  <strong>Main Dashboard &gt; Collections &gt; Banner Ads</strong>.
  </p>
</div>
<?php else: ?>
<form id="urlform" method="get" action="javascript:return void(0);">
  <p>This tool inserts Banner Ad Zone tokens used by the Banner Ads plugin. 
     The token that is inserted will be replaced with a banner ad created 
     for this zone in <strong>Main&nbsp;Dashboard&nbsp;&gt;&nbsp;Collections&nbsp;&gt;&nbsp;Banner&nbsp;Ads</strong>.
  </p>
  <div class="inputdiv">
      <h3 style="margin-bottom: 4px;">Select An Ad Zone:</h3>
      <select name="adzones" id="adzones">
          <option value=""> -- Ad Zones -- </option>
          <?php echo $adzones; ?>
      </select>
  </div>
  <div class="inputdivlast">
  <input type="button" 
         class="button" 
         name="save" 
         value="Ok" 
         onclick="SBC.InsertAdZone(<?php echo $index; ?>); SBC.hideOverlay(<?php echo $index; ?>);"
         />
  <input type="button" 
         class="button" 
         name="cancel" 
         value="Cancel" 
         onclick="SBC.hideOverlay(<?php echo $index; ?>);"
         />
  </div>
</form>
<?php endif; ?>
