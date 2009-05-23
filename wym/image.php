<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 'On');
ini_set(
	'include_path', 
	ini_get('include_path') . ':' . dirname(__FILE__) . ':'
);

define('DS', DIRECTORY_SEPARATOR);
define('SKYBLUE', 1);
define('_ADMIN_', 1);
define('DEMO_MODE', 0);
define('BASE_PAGE', 'admin.php');

define('SB_BASE_PATH', '../');
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

# ###################################################################################
# Load the site config file
# ###################################################################################

$config = $Core->LoadConfig();

# ###################################################################################
# Load the currently installed language. 
# This feature is not fully implemented.
# ###################################################################################

$Core->loadLanguage();

if (!$Core->ValidateRequest("upload_image", true)) {
    die ("<h2>Your session has expired. Please log in.</h2>");
}

$imgs = $Core->ListFiles(SB_MEDIA_DIR);

define('THUMB_SIZE',70);
define('IMAGE_HTML',
'<a href="javascript: void(0);" style="{style}"
     onclick="SBC.InsertImage(\'{src}\',{width},{height},{index}); SBC.hideOverlay({index});">
     <img width="{w}"
           height="{h}"
           src="{src}"
           title="{img.title}"
           />
  </a>');

$index = $Core->GetVar($_GET, 'index', '0');

$rows = null;

$colCount = 6;

for ($i=0; $i<ceil(count($imgs)/$colCount); $i++)
{
    $rows .= "<tr>\n";
    for ($j=0; $j<$colCount; $j++)
    {
        $img = isset($imgs[$j+($i*$colCount)]) ? $imgs[$j+($i*$colCount)] : null ;
        
        if (empty($img))
        {
            $rows .= "<td>&nbsp;</td>\n";
        }
        else
        {
            $src = str_replace(SB_BASE_PATH, null, $img);
		
			list($width, $height) = $Core->ImageDims($img);
			list($w,$h) = $Core->ImageDimsToMaxDim(
				array($width, $height), 
				THUMB_SIZE, 
				THUMB_SIZE
		    );
				
			$w = (THUMB_SIZE - (THUMB_SIZE - $w));
			$h = (THUMB_SIZE - (THUMB_SIZE - $h));
			
			$mTop = floor((THUMB_SIZE - $h)/2);
			$mLeft = floor((THUMB_SIZE - $w)/2);
			
			$mTop += 4;
			$mLeft += 4;
			
			$sWidth  = 82 - $mLeft;
			$sHeight = 82 - $mTop;
			
			$style = "padding: {$mTop}px {$mLeft}px; width: $sWidth; height: $sHeight;";
			
			$title = basename(dirname($src)) . '/' . basename($src);
				   
			$image = str_replace('{src}',       $src, IMAGE_HTML);
			$image = str_replace('{w}',         $w, $image);
			$image = str_replace('{h}',         $h, $image);
			$image = str_replace('{img.title}', basename(dirname($src)) . '/' . basename($src), $image);
			$image = str_replace('{width}',     $width,  $image);
			$image = str_replace('{height}',    $height, $image);
			$image = str_replace('{style}',     $style,  $image);
			$image = str_replace('{index}',     $index,  $image);
			$rows .= "<td>$image</td>\n";
        }
    }
    $rows .= "</tr>\n";
}

?>
<?php if (empty($rows)) : ?>
<form id="urlform" method="get" action="javascript:return void(0);">
  <div>
      <p>It appears that you do not have any images uploaded to 
      your media directory. To use this tool, please upload some 
      images by going to: <strong>Main Dashboard &gt; Media</strong>.
      </p>
  </div>
</form>
<? else : ?>
<div id="imagetable" style="height: 350px; width: 520px; overflow: auto;">
<table id="ImgSelectorThumbList" cellpadding="0" cellspacing="0">
  <?php echo $rows; ?>
</table>
</div>
<style>
  #image-controls {
    width: 500px; 
    height: 100px; 
    overflow: hidden;
  }
  
  #image-controls button {
    display: block;
    cursor: pointer;
  }
  
  #image-controls iframe {
    width: 500px;
    height: 100px;
    border-top: 1px solid #CCC;
    overflow: hidden;
  }
</style>
<div id="image-controls">
<iframe id="uplaod_iframe" src="wym/image.upload.php?wym=<?php echo $index; ?>" frameborder="0"></iframe>
</div>

<? endif; ?>
