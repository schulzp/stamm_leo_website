<?php

// test.php
// tiny sample plugin for phpCMS

	// important: ensure that the plugin can't be executed directly:
	if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

	$current = count($Tags);
	$Tags[$current][0] = '<!-- PLUGIN:TAG -->';
	$Tags[$current][1] = 'phpCMS Plugin-Test';

?>