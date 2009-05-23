<?php

class Uploader {

    /**
    * @var array An array of allowed file types
    */

    var $types;
    
    /**
    * @var array An array of valid target directories
    */
    
    var $targets;
    
    /**
    * @var int  The maximum upload size in bytes
    */
    
    var $max_size;
    
    /**
    * @var int  The available disk space in bytes
    */
    
    var $free_space;
    
    /**
    * @var int  A buffer for free disk space
    */ 
    
    var $buffer = 1024;
    
    /**
    * @constructor
    * @description  The class constructor
    * @param array  The allowed file types that can be uploaded
    * @param array  An array of legal destination directories
    * @return void
    */
    
    function __construct($types, $targets) {
        $this->types   = $types;
        $this->targets = $targets;
        $this->set_max_size();
        $this->set_free_space();
    }
    
    /**
    * @constructor  (legacy)
    * @description  The class constructor
    * @param array  The allowed file types that can be uploaded
    * @param array  An array of legal destination directories
    * @return void
    */
    
    function Uploader($types, $targets) {
        $this->__construct($types, $targets);
    }
    
    /**
    * @description  Performs the file upload
    * @param array  The file array containing info about the file being uploaded.
    * @param array  The posix path to the directory where the file will be uploaded.
    * @return array The exit code and the new file path
    */
    
    function upload($file, $dest) {

        if ($dest{strlen($dest)-1} != '/') $dest .= '/';
        
        $fname = $file['name'];
        $ftype = trim($file['type']);
        $fsize = $file['size'];
        $newfile = null;

        if ($fsize > $this->max_size) {
            $exitCode = 7;
        } 
        else if ($fsize > $this->free_space) {
            $exitCode = 8;
        }
        else if (!in_array($ftype, $this->types)) {
			$exitCode = 4;
		}
        else if (!in_array($dest, $this->targets)) {
			$exitCode = 4;
		}
        else {
            $newfile = $dest.$fname;
                
			$max = 100;
			$ticker = 0;
			while (file_exists($newfile) && $ticker < $max) {
				$ticker++;
				$bits = explode('.', $fname);
				$ext = $bits[count($bits)-1];
				$base = implode('.', array_slice($bits, 0, -1));
				$newfile = $dest."$base.$ticker.$ext";
			}
			
			if (is_uploaded_file($file['tmp_name'])) {
				$exitCode = move_uploaded_file($file['tmp_name'], $newfile);
			}
			else {
				$exitCode = 0;
			}
        }
        return array($exitCode, $newfile);
    }
    
    /**
    * @description  Sets the maximum file upload size
    * @return void
    */
    
    function set_max_size() {
        global $max_file_size;
        $ini = ini_get('upload_max_filesize');
        for ($i=0; $i<strlen($ini); $i++) {
            if (is_numeric($ini{$i})) {
                $max_file_size .= $ini{$i};
            }
        }
        $this->max_size = (intval($max_file_size) * 1024) * 1024;
    }
    
    /**
    * @description  Sets the internal free disk space property
    * @return void
    */
    
    function set_free_space() {
        $this->free_space = disk_free_space(SB_SITE_DATA_DIR) - $this->buffer;
    }
   
}

?>
