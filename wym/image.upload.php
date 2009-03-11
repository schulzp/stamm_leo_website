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
require_once(SB_BASE_PATH.'includes/conf.functions.php');
require_once(SB_BASE_PATH.'configs/server.consts.php');
require_once(SB_BASE_PATH.'configs/dirs.consts.php');
require_once(SB_BASE_PATH.'configs/files.consts.php');
require_once(SB_BASE_PATH.'configs/strings.consts.php');
require_once(SB_BASE_PATH.'configs/tokens.consts.php');
require_once(SB_BASE_PATH.'configs/regex.consts.php');
require_once(SB_BASE_PATH.'includes/object.class.php');
require_once(SB_BASE_PATH.'includes/observer.class.php');
require_once(SB_BASE_PATH.'includes/error.class.php');
require_once(SB_BASE_PATH.'includes/core.php');
require_once(SB_BASE_PATH.'includes/uploader.php');

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

$dirs = FileSystem::list_dirs('../data/media/');

$message = '<h2>Upload a new Image</h2>';
$success = false;

$index = $Core->GetVar($_GET, 'wym', 0);
    
if (isset($_FILES['upload']) && !empty($_FILES['upload']['name'])) {

    $file = $_FILES['upload'];
    $dest = $_POST['upload_dir'];
    $types = array(
        'image/png',
        'image/jpeg',
        'image/gif',
        'application/mpeg'
    );
    
	$targets = FileSystem::list_dirs(SB_MEDIA_DIR);
	array_push($targets, SB_DOWNLOADS_DIR);
	array_push($targets, SB_UPLOADS_DIR);
	array_push($targets, ACTIVE_SKIN_DIR . "images/");
    
    list($exitCode, $newfile) = $Core->UploadFile($file, $dest, $types, 5000000, $targets);
    
    if ($exitCode == 1) {
        $success = true;
        $message = '<div class="msg-success-small"><h2>Success!</h2></div>';
    }
    else {
        $message = '<div class="msg-error-small"><h2>An unknown error occurred</h2></div>'; 
    }
}

?>
<html>
    <head>
        <script type="text/javascript" src="../ui/admin/js/jquery.js"></script>
        <link type="text/css" rel="stylesheet" href="../ui/elements/css/elements.css" />
        <?php if ($success) : ?>
        <script type="text/javascript">
            $(function() {
                var file_path = "image.php?index=<?php echo $index; ?>";
                $.get(file_path, function(data) {
                    try {
                        if (parent.jQuery('.modalData')[0]) {
                            $(parent.jQuery('.modalData')[0]).html(data);
                        }
                    }
                    catch(e) {
                        $("#message").html(
                            "<h2>Warning:</h2>" + 
                            "<p>Your image has been uploaded but the " + 
                            "thumbnail viewer could not be updated.</p>"
                        );
                    }
                });
            });
        </script>
        <?php endif; ?>
        <script type="text/javascript">
            $(function() {
                $("#upload_button").bind("click", function(e) {
                    if ($("#upload_file").val() == "") {
                        e.preventDefault();
                    }
                });
            });
        </script>
        <style>
          #upload_button {
            display: block;
            color: #222;
            font-weight: bold;
            font-size: 12px;
            background: #869BAE;
            border-top: 1px solid #9196A1;
            border-left: 1px solid #9196A1;
            border-right: 1px solid #333;
            border-bottom: 1px solid #333;
            cursor: pointer;
            padding: 4px 10px;
            margin-top: 4px;
          }
          h2 {
            font-size: 14px; 
            font-family: Arial;
          }
          * {font-size: 12px;}
          div.msg-success-small {
            border-color: #80C881;
            background: #A6FFA5;
            padding: 3px;
            margin: 0px 0px 6px 0px;
          }
          div.msg-success-small h2 {
            padding: 0px;
            margin: 0px;
          }
          
        </style>
    </head>
    <body>
        <div id="message">
            <?php echo $message; ?>
        </div>
        <form id="mgrform" method="post" action="image.upload.php?wym=<?php echo $index; ?>" enctype="multipart/form-data" >
        
            <input id="upload_file" type="file" name="upload" size="12" />
        
            <select name="upload_dir" size="1" id="upload_dir">
                <?php for ($i=0; $i<count($dirs); $i++) : ?>
                <option value="<?php echo $dirs[$i]; ?>"><?php echo $dirs[$i]; ?></option>
                <?php endfor; ?>
            </select>
        
            <input type="hidden" name="MAX_FILE_SIZE" value="6291456" />
            
            <button name="upload" id="upload_button">Upload</button>
        </form>
    </body>
</html>