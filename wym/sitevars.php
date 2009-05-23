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

if (!$Core->ValidateRequest("upload_image", true)) {
     die ("<h2>Your session has expired. Please log in.</h2>");
}

$index = isset($_GET['index']) ? $_GET['index'] : 0 ;

?>
<form id="urlform" method="get" action="javascript:return void(0);">
  <p>This tool inserts Site Variable tokens used by the SiteVars plugin. 
     Make sure the SiteVars Plugin is installed and enabled.
  </p>
  <h2>SiteVar Example:</h2>
  <p>
  If you insert the SiteVar <strong>[[site.url]]</strong>, the plugin will replace the 
  token <br />with your site's URL.
  </p>
  <div class="inputdiv">
      <h3 style="margin-bottom: 4px;">Select A Site Variable:</h3>
      <select name="sitevars" id="sitevars">
            <option value=""> -- Site Variables -- </option>
            <option value="site.name">Site Name</option>
            <option value="site.url">Site URL</option>
            <option value="site.map">SiteMap</option>
            
            <option value="site.contact_name">Contact Name</option>
            <option value="site.contact_title">Contact Title</option>
            <option value="site.contact_address">Contact Street</option>
            <option value="site.contact_city">Contact City</option>
            <option value="site.contact_state">Contact State</option>
            <option value="site.contact_zip">Contact Zip</option>
            <option value="site.contact_email">Contact Email</option>
            <option value="site.contact_phone">Contact Phone</option>
            <option value="site.contact_fax">Contact Fax</option>
            
            <option value="page.id">Page ID</option>
            <option value="page.title">Page Titel</option>
            <option value="page.url">Page URL</option>
            <option value="page.modified('F d, Y h:i A')">Page Modified</option>
            <option value="page.parent.id">Parent Page ID</option>
            <option value="page.parent.title">Parent Page Title</option>
            
            <option value="page.link(parent)">Parent Page Link</option>
            <option value="page.link(children)">Page Children</option>
            <option value="page.link(first)">Link to First</option>
            <option value="page.link(last)">Link to Last</option>
            <option value="page.link(next)">Link to Next</option>
            <option value="page.link(previous)">Link To Previous</option>
      </select>
  </div>
  <div class="inputdivlast">
  <input type="button" 
         class="button" 
         name="save" 
         value="Ok" 
         onclick="SBC.InsertSiteVar(<?php echo $index; ?>); SBC.hideOverlay(<?php echo $index; ?>);"
         />
  <input type="button" 
         class="button" 
         name="cancel" 
         value="Cancel" 
         onclick="SBC.hideOverlay(<?php echo $index; ?>);"
         />
  </div>
</form>