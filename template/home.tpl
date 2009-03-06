<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>BdP Stamm LEO</title>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<!-- add your meta tags here -->

<link href="/css/leo_layout.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/leo.js"></script>
<!--[if lte IE 7]>
<link href="/css/patches/patch_leo_layout.css" rel="stylesheet" type="text/css" />
<![endif]-->
</head>
<body>
<div class="page_margins">
    <!-- start: skip link navigation -->
    <!-- end: skip link navigation -->
    <div class="page">
        <div id="header">
            <div id="topnav">
                <!-- start: skip link navigation -->
                <a class="skip" title="skip link" href="#navigation">Skip to the navigation</a><span class="hideme">.</span>
                <a class="skip" title="skip link" href="#content">Skip to the content</a><span class="hideme">.</span>
                <!-- end: skip link navigation -->
                <span>Stamm LEO, Bund deutschen Pfadfinderinnen und Pfadfinder</span>
            </div>
        </div>
        <div id="nav">
            <!-- skiplink anchor: navigation -->
            <a id="navigation" name="navigation"></a>
            <div class="hlist">
                <!-- main navigation: horizontal list -->
                {MENU NAME="MAIN"}
            </div>
        </div>
        <div id="subnav">
            <div class="hlist col1">
                <!-- main navigation: horizontal list -->
                {MENU NAME="SUB1"}
            </div>
            <div class="col3">
                <form method="post" class="yform">
                    <fieldset>
                        <input type="text" name="search_field" id="search_field"
                        value="Suchbegriff" class="type-text" 
                        onfocus="LEO.searchform.clear(this);" onblur="LEO.searchform.reset(this);" />
                        <input type="submit" name="search_smt" id="search_smt"
                        value="Suche" class="type-button" />
                    </fieldset>
                </form>
            </div>
        </div>
        <div id="main">
            <div id="col1">
                <div id="col1_content" class="clearfix">
                    <div class="col_header">
                        <div class="breadcrumen_nav">
                            {MENU NAME="MAIN" MENTEMP="LINE"}{MENU NAME="SUB1" MENTEMP="LINE"}
                        </div>
                    </div>
                    <!-- add your content here -->
                    <h1>{TITLE}</h1>
                    {CONTENT}
                </div>
            </div>
            <div id="col3">
                <div id="col3_content" class="clearfix">
                    <div class="col_box">
                        <h1>Anmeldeformular</h1>
                        <div class="col_box_content">
                            <p>Das Anmeldeformular können Sie über folgenden Link herunterladen</p>
                            <a href="#">Anmelde Formular (PDF, ca. 0,8MB)</a>
                        </div>
                    </div>
                </div>
                <!-- IE Column Clearing -->
                <div id="ie_clearing"> &#160; </div>
            </div>
        </div>
        <!-- begin: #footer -->
        <div id="footer">
            <div class="quickinfo">
                Stamm LEO &nbsp; <a href="#">Kontakt</a> &middot; <a href="#">Impressum</a>
            </div>
            Layout basiert auf <a href="http://www.yaml.de/">YAML</a>
        </div>
    </div>
</div>
</body>
</html>
