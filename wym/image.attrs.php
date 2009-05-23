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
$id    = $Core->GetVar($_GET, 'id', null);
$alt   = urldecode($Core->GetVar($_GET, 'alt', null));
$title = urldecode($Core->GetVar($_GET, 'title', null));
$class = $Core->GetVar($_GET, 'class', null);

?>

<form id="urlform" method="get" action="javascript:return void(0);">
  <div class="inputdiv">
      <h3>Alt Text:</h3>
      <input type="text" 
             name="alt-text" 
             value="<?php echo $alt; ?>" 
             class="inputfield" 
             id="alt-text"
             />
  </div>
  <div class="inputdiv">
      <h3>Title Text:</h3>
      <input type="text" 
             name="title-text" 
             value="<?php echo $title; ?>" 
             class="inputfield" 
             id="title-text"
             />
  </div>
  <div class="inputdiv">
      <h3>CSS Class:</h3>
      <input type="text" 
             name="css-class" 
             value="<?php echo $class; ?>" 
             class="inputfield" 
             id="css-class"
             />
  </div>
  <div class="inputdivlast">
  <input type="button" 
         class="button" 
         name="save" 
         value="Ok" 
         onclick="SBC.AddImageAttrs(<?php echo $index; ?>, '<?php echo $id; ?>'); SBC.hideOverlay(<?php echo $index; ?>);"
         />
  <input type="button" 
         class="button" 
         name="cancel" 
         value="Cancel" 
         onclick="SBC.hideOverlay(<?php echo $index; ?>);"
         />
  </div>
</form>
