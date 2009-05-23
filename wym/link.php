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

$Core->LoadConfig();

if (!$Core->ValidateRequest("upload_image", true)) {
     die ("<h2>Your session has expired. Please log in.</h2>");
}

define('THUMB_SIZE',72);
define('URL_FORM',
'<div class="modal_wrapper">
<form id="urlform" method="get" action="javascript:return void(0);">
  <div class="inputdiv">
      <h3 style="margin-bottom: 4px;">Link to a page from your own site:</h3>
      <select name="internalpage" id="internalpage">
          <option value="">-- Internal Pages --</option>
          {pages}
      </select>
  </div>
  <div class="inputdiv">
      <h3>Or link to a downloadable file:</h3>
      <select name="filedownload" id="filedownload">
          <option value="">-- File Downloads --</option>
          {downloads}
      </select>
  </div>
  <div class="inputdiv">
      <h3>Or Enter a URL for an external link:</h3>
      <input type="text" 
             name="externallink" 
             value="" 
             class="inputfield" 
             id="externallink"
             />
  </div>
  <div class="inputdiv">
      <h3>Now enter a title for your link:</h3>
      <input type="text" 
             name="linktitle" 
             value="" 
             class="inputfield" 
             id="linktitle"
             />
  </div>
  <div class="inputdivlast">
  <input type="button" 
         class="button" 
         name="save" 
         value="Ok" 
         onclick="SBC.restoreSelection({index}); SBC.InsertLink({index}); SBC.hideOverlay({index});"
         />
  <input type="button" 
         class="button" 
         name="cancel" 
         value="Cancel" 
         onclick="SBC.restoreSelection({index}); SBC.hideOverlay({index});"
         />
  </div>
</form>
</div>'."\n");

$index = $Core->GetVar($_GET, 'index', '0');

$files = ListFilesOptionalRecurse(SB_DOWNLOADS_DIR, 1);

$pages = $Core->xmlHandler->ParserMain(SB_PAGE_FILE);

$pagelist = NULL;
$myurl = SB_MY_URL;
foreach ($pages as $p)
{
    $link = $Core->GetLink($p->name, $p->id, '', 1);
    $pagelist .= '<option value="'.$link.'">'.$p->name.'</option>'."\n";
}

$filelist = NULL;
for ($i=0; $i<count($files); $i++)
{
    $file = str_replace(SB_DOWNLOADS_DIR,'',$files[$i]);
    $link = $myurl.'data/downloads/'.$file;
    $filelist .= '<option value="'.$link.'">'.basename($file).'</option>'."\n";
}

$form = str_replace('{pages}',$pagelist,URL_FORM);
$form = str_replace('{downloads}',$filelist,$form);
$form = str_replace('{index}', $index, $form);
echo $form;

function ListFilesOptionalRecurse($dir, $recurse=1, $files=array()) 
{
    ini_set('max_execution_time', 10);
    if (!is_dir($dir)) 
    {
        die ('File: '.__FILE__.'<br/>No such directory: '.$dir);
    }
    if ($root = @opendir($dir))
    {
         while ($file = readdir($root)) 
         {
              if ($file{0} == '.') 
              {
                continue;
              }
              if (is_dir($dir.$file)) 
              {
                  if ($recurse == 0) 
                  {
                      continue;
                  }
                  $files = array_merge($files, ListFilesOptionalRecurse($dir.$file.'/', $recurse));
              } else {
                      $files[] = $dir.$file;
              }
         }
    }
    sort($files);
    return $files;
}

/**
* array public ImageDimsToMaxDim()
*
* This function determines which of the width or height is larger,
* then determines the scale ratio and scales the image values so that
* the larger of the image dimensions does not exceed the maximum
* desired dimension.
*
* @param array - an array of current array(width, height) values of the image.
* @param int   - the maximum width of the image.
* @param int   - the maximum height of the image.
*/

function ImageDimsToMaxDim($dims, $maxwidth, $maxheight) 
{

    $width    = $dims[0];
    $height    = $dims[1];
    
    $widthratio = 1;
    if ($width > $maxwidth) 
    {
        $widthratio = $maxwidth/$width;
    }
    
    $heightratio = 1;
    if ($height > $maxheight) 
    {
        $heightratio = $maxheight/$height;
    }
    
    $ratio = $heightratio;
    if ($widthratio < $heightratio) 
    {
        $ratio = $widthratio;
    }
    
    // Scale the images
    
    $width     = ceil($width * $ratio);
    $height     = ceil($height * $ratio);
    
    // Let's tweak the scale so the new dimes the max dims exactly
    // If the ratio == 1, no need
    
    if ($ratio == $heightratio && $ratio != 1) 
    {
        if ($height < $maxheight) 
        {
            while ($height < $maxheight) 
            {
                $ratio = $ratio * 1.01;
                $height = ceil($height * $ratio);
            }
        }
    }
    
    if ($ratio == $widthratio && $ratio != 1) 
    {
        if ($width < $maxwidth) 
        {
            while ($width < $maxwidth) 
            {
                $ratio = $ratio * 1.01;
                $width = ceil($width * $ratio);
            }
        }
    }

    return array($width, $height);
}

/**
* array public ImageDims()
*
* Returns the width and height of an image in the format:
* array(width, height).
*
* @param string - the path to the image.
*/

function ImageDims($fp) 
{
    if (!file_exists($fp) || is_dir($fp))
    {
        return array(0, 0);
    }
    return getimagesize($fp);
}

/**
* int public ImageWidth()
*
* Returns the width of an image.
*
* @param string - the path to the image.
*/

function ImageWidth($fp) 
{
    $dims = $this->ImageDims($fp);
    return $dims[0];
}

/**
* int public ImageHeight()
*
* Returns the height of an image.
*
* @param string - the path to the image.
*/

function ImageHeight($fp) 
{
    $dims = $this->ImageDims($fp);
    return $dims[1];
}

/**
* bool public ImageResize()
*
* Resizes the actual image in on the file system.
*
* @param string - the path to the image.
* @param int    - the new width of the image.
* @param int    - the new height of the image.
*/

function ImageResize($fp, $dest_w, $dest_h) 
{
    
    $dims        = getimagesize($fp);
    $destination = imagecreatetruecolor($dest_w, $dest_h);
    $ext         = $this->GetImageExtension($fp);
    
    switch ($ext) 
    {
        case 'jpg':
            $source = imagecreatefromjpeg($fp);
            break;
        case 'jpeg':
            $source = imagecreatefromjpeg($fp);
            break;
    }
    
    if (imagecopyresampled($destination, $source, 0, 0, 0, 0, 
                                  $dest_w, $dest_h, $dims[0], $dims[1])) 
    {
        switch ($ext) 
        {
            case 'jpg':
            case 'jpeg':
                imagejpeg($destination, $fp);
                break;
        }
        imagedestroy($source);
        imagedestroy($destination);
    }
    return $success;
}

function WriteFile($file, $str, $append=0) 
{
        $fp = fopen($file, $append == 0 ? 'w+' : 'a');
        if (!$fp) 
        {
            return false;
        }
        if (!fwrite($fp, $str)) 
        {
          return false;
        }
        fclose($fp);
        return true;
}

?>