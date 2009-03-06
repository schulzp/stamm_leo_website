<?php
/* $Id: class.phpcms.php,v 1.1.2.37 2006/06/19 14:51:15 ignatius0815 Exp $ */
/*
   +----------------------------------------------------------------------+
   | phpCMS Content Management System - Version 1.2
   +----------------------------------------------------------------------+
   | phpCMS is Copyright (c) 2001-2006 by the phpCMS Team
   +----------------------------------------------------------------------+
   | This program is free software; you can redistribute it and/or modify
   | it under the terms of the GNU General Public License as published by
   | the Free Software Foundation; either version 2 of the License, or
   | (at your option) any later version.
   |
   | This program is distributed in the hope that it will be useful, but
   | WITHOUT ANY WARRANTY; without even the implied warranty of
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
   | General Public License for more details.
   |
   | You should have received a copy of the GNU General Public License
   | along with this program; if not, write to the Free Software
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston,
   | MA  02111-1307, USA.
   +----------------------------------------------------------------------+
   | Contributors:
   |    Markus Richert (e157m369)
   |    Tobias Dönz (tobiasd)
   |    Henning Poerschke (hpoe)
   |    Markus Richert (e157m369)
   |    Thilo Wagner (ignatius0815)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

/*
********************************************
  class PHPCMS in class.phpcms.php

  created:   Markus Richert, 2003-02-25
  extended:  Markus Richert, 2003-03-04

  purpose:   Cleaning up the server given
             scope on phpCMS startup and
             whenever called from within
             the system to (re)set a var.

             Separating special separated
             GET-vars, even with multi-
             dimensional arrays.
********************************************
*/
class PHPCMS {
  // this must be edited for every new version
  var $VERSION = '1.2.2';
  // Status of the Release: CVS, alpha, beta etc.
  var $RELEASE = '';

  var $GET_VARS_SEPARATORS;
  var $GET_VARS_SYSTEM_HANDLE;
  var $GET_VARS;
  var $_REQUEST_URI;
  var $_request_uri; // at the moment only used in the Debug-Tool
  var $_QUERY_STRING;
  var $_query_string;

  var $TIMER;

  // init the params to separate the GET-vars with special strings
  function PHPCMS() {
    // array of the strings, which are separators additional to the servers defaults
    // the '?' is unconditionally needed by the phpCMS parser
    // the '&' is needed additionally to extract e.g. $REQUEST_URI
    // but you can add more separators on your own, but be careful!
    $this->GET_VARS_SEPARATORS = array('?', '&', '|:');
    // this is the internal handle of this class that all separators from the array above
    // are reduced to, to get better performance with the separations
    // BE CAREFUL CHANGING THIS: none of the separator strings above can be part of this handle!
    $this->GET_VARS_SYSTEM_HANDLE = '|->|';
  }

  /*
   function to extract the special separated GET-params
   */
  function extract_special_separated(&$ARRAY, $key, $value, $root, $motherkey) {
    /*
     setting where in the array-tree of the server-given GET-vars we are now
     */
    $dir = '';
    if(is_array($root)) {
      foreach($root as $dirpart) {
        $dir .= '[\''.$dirpart.'\']';
      }
    }

    if (isset($dir)) {
      $dir .= '[\''.$motherkey.'\']';
    } else {
      $dir = '[\''.$motherkey.'\']';
    }
    $root[] = $motherkey;

    if(is_array($value)) {
      // the actually called entry is a subsequent array and not a string, so recurse it
      foreach($value as $motherkey => $svalue) {
        $this->extract_special_separated($ARRAY, $key, $svalue, $root, $motherkey);
      }
    } else {
      /*
       we found a string of the array!
       now replace the different additional separators with the internal handle
       */
      foreach($this->GET_VARS_SEPARATORS as $sep) {
        $value = str_replace($sep, $this->GET_VARS_SYSTEM_HANDLE, $value);
      }

      /*
       ready to start with the extraction itself
       */
      if(stristr($value, $this->GET_VARS_SYSTEM_HANDLE)) {
        /*
         handle is part of this value, so exploding this string
           into an array is next to do
         */
        $multi = explode($this->GET_VARS_SYSTEM_HANDLE, $value);
        if($multi[0]) {
          /*
           set the first part (value of this server-given key) to $temp[1]
           */
          eval('$temp[1]'.$dir.' = $multi[0];');
        }
        else {
          $temp[1] = array();
        }
        // exploding the other part-strings to a key and their value
        for($i = 1; $i < count($multi); $i++) {
          $one = explode('=', $multi[$i]);
          // and set up the new array with them
          $new[$one[0]] = $value = isset($one[1]) ? $one[1] : '';
        }

        // walk through this new array watching for additional arrays
        foreach($new as $nkey => $nvalue) {
          if(stristr($nkey, '[') !== false) {
            $mainkey = substr($nkey, 0, strpos($nkey, '['));
            $subkeys = substr($nkey, strpos($nkey, '['));
            // and create those additional arrays in $temp[2]
            eval('$temp[2][\''.$mainkey.'\'][\''.$subkeys.'\'] = $nvalue;');
          } else {
            // but if this key of the new array is a normal string, set it to $temp[2]
            $temp[2][$nkey] = $nvalue;
          }
        }
      } else {
        // if we are here, there was no extraction handle in the string, so set it to $temp[1]
        eval('$temp[1]'.$dir.' = $value;');
        $temp[2] = array();
      }

      //echo '$ARRAY ('.__LINE__.') ';
      //print_r($ARRAY);
      // now do a recursive merge with the $temp arrays and the existing GET_VARS from previous loops
      if(isset($ARRAY) && is_array($ARRAY)) {
        $ARRAY = array_merge_recursive($ARRAY, $temp[1], $temp[2]);
      }
      else {
        $ARRAY = array_merge_recursive($temp[1], $temp[2]);
      }
      //echo '$ARRAY ('.__LINE__.') ';
      //print_r($ARRAY);

    }
  } // end extract_special_seperated

  // convert all keys of an array to lowercase, e.g. used to get PHPCMS->_request_uri
  function set_case_insensitive_keys($source, $target, $root, $motherkey) {
    // setting where in the array-tree of the given source-array we are now
    $dir = '';
    if(is_array($root)) {
      foreach($root as $dirpart) {
        $dir .= '[\''.$dirpart.'\']';
      }
    }
    if($motherkey) {
      $dir .= '[\''.$motherkey.'\']';
      $root[] = $motherkey;
    }
    //print_r($source);
    // walk through the actual array
    if (is_array($source)) {
      foreach($source as $key => $value) {
        if(is_array($value)) {
          $this->set_case_insensitive_keys($value, $target, $root, $key);
        } else {
          eval($target.$dir.'[strtolower($key)] = $value;');
        }
      }
    }
  }

  // restore a request-string from an array, e.g. used to get DEBUG->RU
  function restore_request_string_from_array($source, $root, $motherkey) {

    $dir = $string = $temp = '';

    // setting where in the array-tree of the given source-array we are now
    if(is_array($root)) {
      foreach($root as $dirpart) {
        $dir .= '[\''.$dirpart.'\']';
      }
    }
    if($motherkey) {
      $dir .= $motherkey;
      $root[] = $motherkey;
    }
    // walk through the actual array
    foreach($source as $key => $value) {
      if(is_array($value)) {
        $temp .= $this->restore_request_string_from_array($value, $root, $key);
      } else {
        (isset($dir) && $dir != '') ? $var = $dir.'['.$key.']' : $var = $key;
        $key == '_uri_' ? $string .= $value : $string .= '?'.$var.'='.$value;
      }
    }
    return $string.$temp;
  }

  // function to reload the environment on startup
  function prepare_environment_vars() {
    $global_vars = array('GET', 'POST', 'COOKIE', 'ENV', 'SERVER', 'SESSION');

    // extracting special separated GET-Vars using the above recursive function...
    if(isset($GLOBALS['_GET']) && is_array($GLOBALS['_GET'])) {
      $which = '_GET';
    } else {
      $which = 'HTTP_GET_VARS';
    }
    if ( !isset( $this->GET_VARS ) OR !is_array ( $this->GET_VARS ) ) {
      $this->GET_VARS = Array();
    }
    if(is_array($GLOBALS[$which]) AND count($GLOBALS[$which]) > 0 ) {
      foreach($GLOBALS[$which] as $key => $value) {
        /** only for debuging reasons **/
        //echo "\n"."\n"."\n".'DEBUG $GLOBALS['.$which.']['.$key.'] = '.$value."\n";
        /** only for debuging reasons **/
        $this->extract_special_separated($this->GET_VARS, $key, $value, '', $key);
      }
      reset($GLOBALS[$which]);
    }
    // and reset the original GET-vars with them
    $GLOBALS[$which] = $this->GET_VARS;
    unset($this->GET_VARS);

    // run through the $global_vars
    for($i = 0; $i < sizeof($global_vars); $i++) {
      // if set $_* -> map them to $HTTP_*_VARS
      if(isset($GLOBALS["_{$global_vars[$i]}"]) && is_array($GLOBALS["_{$global_vars[$i]}"])) {
        foreach($GLOBALS["_{$global_vars[$i]}"] as $key => $value) {
          $GLOBALS["HTTP_{$global_vars[$i]}_VARS"][$key] = $value;
          if (!isset($GLOBALS[$key])) {
             $GLOBALS[$key] = $value;
          }
        }
        reset($GLOBALS["_{$global_vars[$i]}"]);
      }
      // if set $HTTP_*_VARS -> map them to $_*
      if(isset($GLOBALS["HTTP_{$global_vars[$i]}_VARS"]) && is_array($GLOBALS["HTTP_{$global_vars[$i]}_VARS"])) {
        foreach($GLOBALS["HTTP_{$global_vars[$i]}_VARS"] as $key => $value) {
          $GLOBALS["_{$global_vars[$i]}"][$key] = $value;
          if (!isset($GLOBALS[$key])) {
             $GLOBALS[$key] = $value;
          }
          // map the GPCs to $_REQUEST
          if(($global_vars[$i] == 'GET') || ($global_vars[$i] == 'POST') || ($global_vars[$i] == 'COOKIE')) {
            $GLOBALS['_REQUEST'][$key] = $value;
          }
          // map the GET and POST to $_GET_POST
          if(($global_vars[$i] == 'GET') || ($global_vars[$i] == 'POST')) {
            $GLOBALS['_GET_POST'][$key] = $value;
          }
        }
        reset($GLOBALS["HTTP_{$global_vars[$i]}_VARS"]);
      }
    }
    // finally do the mapping with posted files
    if(isset($GLOBALS['_FILES']) && is_array($GLOBALS['_FILES'])) {
      foreach($GLOBALS['_FILES'] as $key => $value) {
        $GLOBALS['HTTP_POST_FILES'][$key] = $value;
      }
      reset($GLOBALS['_FILES']);
    }
    if(isset($GLOBALS['HTTP_POST_FILES']) && is_array($GLOBALS['HTTP_POST_FILES'])) {
      foreach($GLOBALS['HTTP_POST_FILES'] as $key => $value) {
        $GLOBALS['_FILES'][$key] = $value;
      }
      reset($GLOBALS['HTTP_POST_FILES']);
    }
  }

  // function to add or set an environment-variable
  function set_environment_var($name, $key, $value) {
    $global_vars = array('GET', 'POST', 'COOKIE', 'ENV', 'SERVER', 'SESSION');

    if(in_array($name, $global_vars, TRUE)) {
      $GLOBALS["HTTP_{$name}_VARS"][$key] = $value;
      $GLOBALS["_{$name}"][$key] = $value;
      $GLOBALS[$key] = $value;
      // add it to $_REQUEST
      if(($name == 'GET') || ($name == 'POST') || ($name == 'COOKIE')) {
        $GLOBALS['_REQUEST'][$key] = $value;
      }
      // add it to $_GET_POST
      if(($name == 'GET') || ($name == 'POST')) {
        $GLOBALS['_GET_POST'][$key] = $value;
      }
    } elseif($name == 'FILES') {
      $GLOBALS['HTTP_POST_FILES'][$key] = $value;
      $GLOBALS['_FILES'][$key] = $value;
    } else {
      echo '<p><font size="4" face="Verdana, sans-serif"><b>phpCMS</b></font></p>'.
        '<p><font size="2" face="Verdana, sans-serif"><b>Error:</b><br />'.
        'The function<br />&nbsp;&nbsp;&nbsp;&nbsp;<b>$PHPCMS_PREPARE_SYSTEM->set_environment_var()</b><br />'.
        'is only allowed to use with the following names:<br />';
      for($i = 0; $i < sizeof($global_vars); $i++) {
        echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$global_vars[$i].'<br />';
      }
      echo '&nbsp;&nbsp;&nbsp;&nbsp;FILES<br />&nbsp;<br />'.
        'instead it was used with:<br />&nbsp;&nbsp;&nbsp;&nbsp;<b>'.$name.'</b></font></p>';
      exit();
    }
  }

  // check for acces-violation in secure-stealth-mode
  function check_secure_stealth() {
    global $DEFAULTS;

    if($DEFAULTS->STEALTH == 'on' AND $DEFAULTS->STEALTH_SECURE == 'on') {
      if(isset($this->_REQUEST_URI['file'])) {
        die('This is phpCMS '."$DEFAULTS->VERSION".' in Secure Stealth Mode.<br /><br />You cannot access files via the parser!<br /><br />Access denied!');
      }
    }
  }

  // load the current timestamp
  function get_time($time) {
    list($usec, $sec) = explode(' ', $time);
    return (float)$usec + (float)$sec;
  }

  // get the time, passed between two timestamps
  function get_time_passed($start, $stop) {
    return ($stop * 1) - ($start * 1);
  }
}

?>
