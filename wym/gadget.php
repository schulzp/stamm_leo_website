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

$files = $Core->ListFilesOptionalRecurse(SB_SITE_DATA_DIR . 'gadgets/', 0);

$gadgets = null;
for ($i=0; $i<count($files); $i++)
{
    $name = basename($files[$i]);
    if ($name{0} == '_') continue;
    $file = str_replace('.js', null, $name);
    $gadgets .= '<option value="'.$file.'">'.$file.'</option>'."\n";
}

?>
<?php if (empty($gadgets)) : ?>
<div>
  <p>Either you do not have any Gadgets installed or all of 
  your Gadgets are currently disabled. To use this tool please 
  install and enable at least one Gadget in <br />
  <strong>Main Dashboard &gt; Collections &gt; Gadgets</strong>.
  </p>
</div>
<?php else: ?>
<form id="urlform" method="get" action="javascript:return void(0);">
  <p>This tool inserts Gadget tokens used by the Gadgets plugin. 
     The token that is inserted will be replaced with the Gadget created 
     in <strong>Main&nbsp;Dashboard&nbsp;&gt;&nbsp;Collections&nbsp;&gt;&nbsp;Gadgets</strong>.
  </p>
  <div class="inputdiv">
      <h3 style="margin-bottom: 4px;">Select A Gadget:</h3>
      <select name="gadgets" id="gadgets">
          <option value=""> -- Gadgets -- </option>
          <?php echo $gadgets; ?>
      </select>
  </div>
  <div class="inputdivlast">
  <input type="button" 
         class="button" 
         name="save" 
         value="Ok" 
         onclick="SBC.InsertGadget(<?php echo $index; ?>); SBC.hideOverlay(<?php echo $index; ?>);"
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
