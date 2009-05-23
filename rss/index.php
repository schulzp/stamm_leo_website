<?php

/**
* @version        1.1 RC1 2008-12-04 00:43:00 $
* @package        SkyBlueCanvas
* @copyright      Copyright (C) 2005 - 2008 Scott Edwin Lewis. All rights reserved.
* @license        GNU/GPL, see COPYING.txt
* SkyBlueCanvas is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYING.txt for copyright notices and details.
*/

define('DS', DIRECTORY_SEPARATOR);

define('SKYBLUE', 1);
define('_SBC_ROOT_', '../');
define('BASE_PAGE', 'index.php');

require_once(_SBC_ROOT_ . 'base.php');
require_once('./functions.php');

$Filter = new Filter;

$Router = new Router(_SBC_ROOT_);

$Core = new Core(array(
    'path'     => _SBC_ROOT_,
    'lifetime' => 3600,
    'events'   => array(
        'OnBeforeInitPage',
        'OnBeforeShowPage',
        'OnAfterShowPage',
        'OnRenderPage',
        'OnAfterLoadStory',
        'OnBeforeUnload'
   )
));

$config = $Core->LoadConfig();

define('RSS_META_FILE',         SB_XML_DIR . 'meta.xml');
define('RSS_PAGE_FILE',         SB_XML_DIR . 'page.xml');
define('RSS_TEXT_LENGTH', 500);
define('RSS_NO_DESCRIPTION', 'No description available.');

$meta     = $Core->xmlHandler->ParserMain(RSS_META_FILE);
$pages    = $Core->xmlHandler->ParserMain(RSS_PAGE_FILE);

$fragments = FileSystem::list_dirs(ACTIVE_SKIN_DIR . 'fragments/');

header('Content-type: text/xml');
?>    
<rss version="2.0">
    <channel>
        <title><?php echo SB_SITE_NAME; ?></title>
        <link><?php echo FULL_URL; ?></link>
        <description><![CDATA[<?php echo rss_site_description(); ?>]]></description>
        <copyright><?php echo date('Y') . ' - ' . FULL_URL; ?></copyright>
        <language><?php echo SB_LANGUAGE; ?></language>
        <generator>SkyBlueCanvas <?php echo SB_VERSION; ?></generator>
        <!-- Page Items -->
        <?php foreach ($pages as $item) : ?>
        <?php if (!rss_syndicated($item)) continue; ?>
        <item>
            <guid><?php echo $Router->GetLink($item->id); ?></guid>
            <pubDate><?php echo rss_date($item->modified); ?></pubDate>
            <title><?php echo $item->title; ?></title>
            <link><?php echo $Router->GetLink($item->id); ?></link>
            <description><![CDATA[<?php echo rss_story_text($item->story); ?>]]></description>
        </item>
        <?php endforeach; ?>
        <!-- Fragments Feeds -->
        <?php
            for ($i=0; $i<count($fragments); $i++) {
                if (file_exists($fragments[$i] . 'rss.php')) {
                    @include($fragments[$i] . 'rss.php');
                }
            }
        ?>
    </channel>
</rss>