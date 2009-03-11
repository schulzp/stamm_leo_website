<?php

/**
* @version    RC 1.1 2008-12-12 19:47:43 $
* @package    SkyBlueCanvas
* @copyright  Copyright (C) 2005 - 2008 Scott Edwin Lewis. All rights reserved.
* @license    GNU/GPL, see COPYING.txt
* SkyBlueCanvas is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYING.txt for copyright notices and details.
*/

defined('SKYBLUE') or die(basename(__FILE__));

class Loader {
    function load($resource) {
        if ($resource{strlen($resource)-1} == "*") {
            $path = str_replace('*', '', $resource);
            $path = str_replace('.', '/', $path);
            $files = FileSystem::list_files($path);
            for ($i=0; $i<count($files); $i++) {
                require_once($files[$i]);
            }
        }
        else {
			$file = str_replace('.', '/', $resource) . '.php';
			if (file_exists($file)) {
				require_once($file);
			}
			else {
				die('No such file ' . $file);
			}
        }
    }
}

class NoSuchFileException extends Exception {
    
}

?>