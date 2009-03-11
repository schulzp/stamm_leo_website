<?php

/**
* @version		1.1 RC1 2008-11-20 21:18:00 $
* @package		SkyBlueCanvas
* @copyright	Copyright (C) 2005 - 2008 Scott Edwin Lewis. All rights reserved.
* @license		GNU/GPL, see COPYING.txt
* SkyBlueCanvas is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYING.txt for copyright notices and details.
*/

define(
'LOGIN_FAILED', 
"<div class=\"msg-error\">\n" .
"    <h2>Login Failed. Please try again.</h2>\n" .
"</div>\n" . 
"<br />"
);

define( 'LOGIN_Cancel',
        '&nbsp;<input class="button" 
                      type="submit" 
                      name="submit" 
                      value="Home Page" 
                      />' );

define( 'JS_TEST_SCRIPT',
'<script>window.location = "'.BASE_PAGE.'?mgr=login&js=1";</script>' );

define( 'LOGIN_NO_COOKIES', 
'<fieldset id="loginfieldset">
  <form id="loginform" action="#">
    <div id="login_wrapper">
      <table class="linkstable" cellpadding="0" cellspacing="0">
        <tr>
          <td>
            {message}
          </td>
        </tr>
      </table>
    </div>
  </form>
</fieldset>' );

define( 'LOGIN_FORM',
'<div id="login_wrapper">
	{message}
	<form id="loginform" method="post" action="'.BASE_PAGE.'?mgr=login&amp;js=1&amp;try=1">
	<fieldset>
		<div id="login-background">
		<div id="lock">
        <table class="linkstable" cellpadding="0" cellspacing="0">
            <tr>
                <td width="29%" 
                    valign="bottom" 
                    align="right"
                    style="padding-bottom:6px;text-align:right;">Username:</td>
                <td valign="bottom">
                    <input type="text" 
                           class="inputbox" 
                           name="username" 
                           value="" 
                           />
                </td>
            </tr>
            <tr>
                <td width="29%" 
                    valign="top"
                    align="right"
                    style="padding-top:6px;text-align:right;">Password:</td>
                <td valign="top">
                    <input type="password" 
                           class="inputbox" 
                           name="password" 
                           value="" 
                           />
                </td>
            </tr>  
        </table>
		</div>
        </div>
    <input type="hidden" name="act" value="" />
    <input type="hidden" name="attempts" value="{attempts}" />
    <input class="button" 
           type="submit" 
           name="button" 
           value="Login" 
           />
	</fieldset>
</form>
</div>
</div>' );

define( 
'MUST_HAVE_JS_AND_COOKIES',
"<div class=\"msg-error\">\n" . 
"    <h2>Error!</h2>\n" .
"    <p>JavaScript and Cookies must be enabled in your browser. " . 
"    Please enable JavaScript and Cookies and refresh the page." .
"    </p>\n" . 
"</div>\n"
);

define( 
'MUST_HAVE_JS',
"<div class=\"msg-error\">\n" . 
"    <h2>Error!</h2>\n" .
"    <p>JavaScript must be enabled in your browser. " . 
"    Please enable JavaScript and refresh the page." .
"    </p>\n" . 
"</div>\n"
);

define( 
'MUST_HAVE_COOKIES',
"<div class=\"msg-error\">\n" . 
"    <h2>Error!</h2>\n" .
"    <p>Cookies must be enabled in your browser. " . 
"    Please enable Cookies and refresh the page." .
"    </p>\n" . 
"</div>\n"
);

?>
