<?php

/**
* @version		1.1 RC1 2008-11-20 21:18:00 $
* @package		SkyBlueCanvas
* @copyright	Copyright (C) 2005 - 2008 Scott Edwin Lewis. All rights reserved.
* @license		GNU/GPL, see COPYING.txt
* SkyBlueCanvas is free software. This version may have been modified pursuant
* to the GNU General License, and as distributed it includes or
* is derivative of works licensed under the GNU General License or
* other free or open source software licenses.
* See COPYING.txt for copyright notices and details.
*/

defined('SKYBLUE') or die(basename(__FILE__));

/*
* @description  Handles page file caching
*/

class Cache extends FileSystem {

    /*
    * @var int  The cache lifetime in minutes
    */

    var $lifetime;
    
    /*
    * @var string  The name of the cache file
    */
    
    var $fileName;
    
    /*
    * @var array  An array of pages already checked by isCached.
    */
    
    var $_cached = array();
    
    /*
    * @var int   The age of the file in minutes
    */
    
    var $the_age;
    
    /*
    * @var bool  Does the file exist and is it not expired
    */
    
    var $is_cached;
    
    /*
    * @var bool  The raw cache of the page
    */
    
    var $the_cache;
    
    /*
    * @description   Class constructor
    * @param string  The cache file name
    * @param int     The cache lifetime in minutes
    */
    
    function __construct($fileName, $lifetime=60) {
        $this->fileName = "cache/{$fileName}";
        $this->lifetime = $lifetime;
        $this->set('file_time',  $this->getFileTime());
        $this->set('the_age',    $this->_age());
        $this->set('is_expired', $this->isExpired());
        $this->set('is_cached',  $this->_isCached());
        $this->set('the_cache',  $this->_getCache());
        $this->clearExpiredCache();
    }
        
    /*
    * @description   Class constructor (legacy)
    * @param string  The cache file name
    * @param int     The cache lifetime in minutes
    */
    
    function Cache($fileName, $lifetime=60) {
        $this->__construct($fileName, $lifetime);
    }
    
    /*
    * @description   Public method to get the cache contents
    * @return string The cache contents
    */
    
    function getCache() {
        return $this->the_cache;
    }
    
    /*
    * @description   Private method to read the cache from disk
    * @return string The cache contents
    */
    
    function _getCache() {
        if (!empty($_POST)) return null;
        if ($this->is_cached) {
            return $this->read_file($this->fileName);
        }
        return null;
    }
    
    /*
    * @description   Returns the previously stored boolean
    * @return bool   Whether or not a valid cache file exists
    */
    
    function isCached() {
        return $this->is_cached;
    }
    
    /*
    * @description   Checks to see if a valid cache file exists. Valid 
    * means that no data is being posted in the HTTP Request, the file 
    * exists and is not older than the cache lifetime.
    * @return bool   Whether or not a valid cache file exists
    */
    
    function _isCached() {
        if (!empty($_POST)) return null;
        if (!$this->is_expired && file_exists($this->fileName)) {
            $this->_add_to_cached($this->fileName, $this->file_time);
            return true;
        }
        return false;
    }
    
    /*
    * @description  Deletes the cached page if expired
    * @return void
    */
    
    function clearExpiredCache() {
        if ($this->is_expired) {
            $this->clear($this->fileName);
            $this->_remove_from_cached($this->fileName);
        }
    }
    
    /*
    * @description  Calculates the age of the cache file
    * @return int   The age of the cache file in minutes
    */
    
    function _age() {
        return round(round(abs(
            time() - $this->file_time
        )) / 60);
    }
    
    /*
    * @description  Gets the file_time by checking to see if a local value was previously stored. 
    * if not, the filectime is read via stat.
    * @return int   The time in seconds, that the file was last modified
    */
    
    function getFileTime() {
        if (file_exists($this->fileName)) {
            if (isset($this->_cached[$this->fileName])) {
				return $this->_cached[$this->fileName];
			}
            return filectime($this->fileName);
        }
        return 0;
    }
    
    /* 
    * @description   Determines if a cache file has expired
    * @param string  The cache file path
    * @return bool   Whether or not the file has expired
    */
    
    function isExpired() {
        if (!file_exists($this->fileName)) return true;
        return ($this->the_age > $this->lifetime);
    }
    
    /*
    * @description   Saves the cache file
    * @param string  The content of the cahce file
    * @return bool   Whether or not the cache file was saved
    */
    
    function saveCache($content) {
        if (!empty($_POST) || $this->is_cached) return null;
        $was_written = $this->write_file($this->fileName, $content);
        if ($was_written) {
            $this->_add_to_cached($this->fileName, time());
        }
        return $was_written;
    }
    
    /*
    * @description  Updates the cache file time to the current time
    * @return void
    */
    
    function touchCache() {
        if (file_exists($this->fileName)) {
            touch($this->fileName);
        }
    }
    
    /*
    * @description  Deletes the cache file
    * @return void
    */
    
    function clear() {
        if (file_exists($this->fileName) && !is_dir($this->fileName)) {
            $this->delete_file($this->fileName);
        }
        $this->_remove_from_cached($this->fileName);
    }
    
    /*
    * @description  Removes the file index from _cached array
    * @return void
    */
    
    function _remove_from_cached($filename) {
        if (isset($this->_cached[$filename])) {
            unset($this->_cached[$filename]);
        }
    }
    
    /*
    * @description  Adds the file index to _cached array
    * @return void
    */
    
    function _add_to_cached($filename, $filetime) {
        if (!isset($this->_cached[$filename])) {
            $this->_cached[$filename] = $filetime;
        }
    }
    
    /*
    * @description  Deletes all cahced files
    * @return bool  Whether or not all cached files were deleted
    */
    
    function clearAll() {
        $files = $this->list_files("cache/");
        $count = count($files);
        $ticker = 0;
        for ($i=0; $i<$count; $i++) {
            if (!is_dir($files[$i]) && $this->delete_file($files[$i])) {
                $ticker++;
                $this->_remove_from_cached($files[$i]);
            }
        }
        return $ticker == $count;
    }
}

?>