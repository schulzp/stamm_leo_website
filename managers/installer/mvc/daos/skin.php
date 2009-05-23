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

class SkinDAO {

    var $data;
    var $directory;

    function __construct() {
        $this->setDirectory(SB_SKINS_DIR);
    }

    function SkinDAO() {
        $this->__construct();
    }
    
    function setDirectory($directory) {
        $this->directory = $directory;
    }
    
    function save($package) {
    
        $Filter = new Filter;
        
        $name = $this->getName($Filter->get($package, 'name', null));
        
        if ($Filter->get($package, 'error', false)) {
            // An HTTP error occurred
            return false;
        }
        else if (empty($name)) {
            // An empty file name was posted
            return false;
        }
        else if ($this->exists($name)) {
            return false;
        }
        
        $Uploader = new Uploader(
            array(
				'application/x-zip-compressed',
				'application/x-gzip-compressed',
				'application/x-zip',
				'application/x-gzip',
				'application/zip'
			),
            array(SB_TMP_DIR)
        );
        
        list($result, $tmpfile) = $Uploader->upload($package, SB_TMP_DIR);
        
        if (intval($result) != 1) {
            // The file was not uploaded
            return false;
        }
        
        // handle the file move to the managers dir
        
        if (!FileSystem::make_dir($this->directory . $name)) {
            // The target directory could not be created
            return false;
        }
        
        return $this->unzip($tmpfile, $this->directory . $name);
    }
    
    function getName($zip) {
        $dir_name = null;
        $bits = explode('.', $zip);
        if (count($bits) == 2) {
            $dir_name = $bits[0];
        }
        return $dir_name;
    }
    
    function unzip($pkg, $dir) {
        global $Core;

        if (!file_exists($pkg) || !is_dir($dir)) {
            return false;
        }
        $unzipOk = $Core->Unzip($pkg, $dir);
        FileSystem::delete_file($pkg);
        return $unzipOk;
    }
        
    function delete($name) {
        if ($this->exists($name)) {
            return FileSystem::delete_dir("{$this->directory}{$name}/", false);
        }
        return false;
    }
    
    function index() {
        $data = FileSystem::list_dirs($this->directory, 0);
        $this->data = array();
        for ($i=0; $i<count($data); $i++) {
            array_push($this->data, basename($data[$i]));
        }
    }
    
    function getData() {
        return $this->data;
    }
    
    function exists($name) {
        return (!empty($name) && file_exists($this->directory . $name));
    }
    
}

?>