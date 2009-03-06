<?php
/* $Id: class.lib_i18n_phpcms.php,v 1.1.2.4 2006/06/18 18:07:30 ignatius0815 Exp $  */
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
   |    Marcus Obwandner (obw)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

class i18n
{

var $_phpCMSGlobals = FALSE;
var $_S_Lang = FALSE;
var $_B_CleanLang = FALSE;

function i18n ()
{
  $this->_mapGlobals();
  $this->_initActLanguage();
}

function doRedirect($S_ToLang = FALSE)
{
  if ($S_ToLang === FALSE) { $S_ToLang = $this->getLang(); }
  $S_LangURI = $this->getLangURI($S_ToLang);
  header('Location: '.$S_LangURI);
  exit();
  return ;
}

function getLanguageAccept()
{
  global $HTTP_ACCEPT_LANGUAGE;
  $A_Accept = array();
  if (isset($HTTP_ACCEPT_LANGUAGE) && !empty($HTTP_ACCEPT_LANGUAGE))
  {
    $A_Accept = explode(',',$HTTP_ACCEPT_LANGUAGE);
    $A_PosLang = $this->_getDefault('I18N_POSSIBLE_LANGUAGES');
    foreach ($A_Accept as $key => $value)
    {
      if (($pos = strpos($value,';')) !== false)
      {
        $S_Lang = substr($value,0,$pos);
        if (in_array($S_Lang,$A_PosLang)) { return $S_Lang; }
      }
    }
  }
}
function getLangURI($S_ToLang = FALSE, $S_Target = FALSE, $B_WithDomain = TRUE )
{
  if ($S_ToLang === FALSE) { $S_ToLang = $this->getLang(); }
  if ($S_Target === FALSE) { $S_Target = $this->_phpCMSGlobals['_GET_POST']['file']; }
  $S_Mode = $this->_getMode();
  $S_Return = '';
  switch (strtoupper($S_Mode))
  {
    case 'DIR':
      if ($this->_B_CleanLang)
      {
        $S_Return = '/'. $S_ToLang . substr($S_Target,3);
      } else {
        $S_Return = '/'. $S_ToLang . $S_Target;
      }
    break;
    case 'HOST':
      $this->setLang( $this->_parseHost() );
    break;
    case 'SUFFIX':
      $this->setLang( $this->_parseSuffix() );
    break;
    case 'VAR':
      $this->setLang( $this->_parseVar() );
    break;
    case 'SESSION':
      $S_Return = $S_Target;
    break;
    default:
      echo '<h1><em>i18n</em> is missconfigurated, incorrect default value: I18N_MODE';
      exit (0);
    break;
  }
  $S_Session = '';
  if ($this->_phpCMSGlobals['SESSION'] !== FALSE)
  {
    $S_Session = $this->_phpCMSGlobals['SESSION']->getSID();
  }
  if ($S_Session != '' && strstr($S_Return,'?'))
  {
    $S_Session = '&'.$S_Session;
  } elseif ($S_Session != '') {
    $S_Session = '?'.$S_Session;
  }
  return $this->_getDefault('DOMAIN_NAME') . $S_Return . $S_Session;
}
function getLang($B_isCleanSet = FALSE)
{
  if ($this->_S_Lang === FALSE)
  {
    $this->setLang($this->getDefaultLang(), FALSE);
  }
  if (!$B_isCleanSet)
  {
    return $this->_S_Lang;
  } elseif ($this->_B_CleanLang) {
    return $this->_S_Lang;
  }
  return FALSE;
}

function getDefaultLang()
{
  return $this->_getDefault('I18N_DEFAULT_LANGUAGE');
}
function negateFieldName($S_FieldName)
{
  if ($this->_getDefault('I18N_DONEGATION') != 'on') { return FALSE; }
  $S_FieldSuffixSplit = substr($S_FieldName,-3,1);
  if ($S_FieldSuffixSplit != '_') { return FALSE; }
  $S_FieldSuffix = strtolower(substr($S_FieldName,-2));
  if ($this->getLang() == $S_FieldSuffix)
  {
    return substr($S_FieldName,0,strlen($S_FieldName)-3);
  }
  return FALSE;
}
function replaceLangTag($S_Data)
{
	if (!is_string ($S_Data)) {
		return $S_Data;
	}
  if (stristr($S_Data,'$lang'))
  {
    return str_replace(array ('$lang', '$LANG'), array ($this->getLang(), $this->getLang()) ,$S_Data);
  }
  return $S_Data;
}

function setLang($S_Lang, $B_CleanSet = TRUE)
{
	global $_GET_POST;
  if ($S_Lang === FALSE && $this->_S_Lang === FALSE)
  {
    $this->setLang($this->_getDefault('I18N_DEFAULT_LANGUAGE'), FALSE);
  } else {
    $A_PosLang = $this->_getDefault('I18N_POSSIBLE_LANGUAGES');
    if (in_array( $S_Lang, $A_PosLang ) )
    {
      $this->_S_Lang = $S_Lang;
      $this->_B_CleanLang = $B_CleanSet;
      $_GET ['lang'] = $S_Lang;
      $_REQUEST ['lang'] = $S_Lang;
      $_GET_POST ['lang'] = $S_Lang;
    } else {
      if (isset($A_PosLang[0]))
      {
        $this->setLang($A_PosLang[0], FALSE);
      } else {
        echo '<h1><em>i18n</em> is missconfigurated, can\'t select any Language.</h1>';
        exit(0);
      }
    }
  }
}

function _mapGlobals()
{
  if (is_array($this->_phpCMSGlobals)) { return; }
  global $DEFAULTS,$_GET_POST;
  $this->_phpCMSGlobals['DEFAULTS'] = &$DEFAULTS;
  if ($this->_getDefault('SESSION') == 'on')
  {
    global $SESSION;
    $this->_phpCMSGlobals['SESSION'] = &$SESSION;
  } else {
    $this->_phpCMSGlobals['SESSION'] = FALSE;
  }
  $this->_phpCMSGlobals['_GET_POST'] = &$_GET_POST;

  if (!is_array ($DEFAULTS->I18N_POSSIBLE_LANGUAGES)) {
  	$DEFAULTS->I18N_POSSIBLE_LANGUAGES = explode (',', $DEFAULTS->I18N_POSSIBLE_LANGUAGES);
  }
}

function _initActLanguage()
{
  $S_Mode = $this->_getMode();
  switch (strtoupper($S_Mode))
  {
    case 'DIR':
      $this->setLang( $this->_parseDir() );
    break;
    case 'HOST':
      $this->setLang( $this->_parseHost() );
    break;
    case 'SUFFIX':
      $this->setLang( $this->_parseSuffix() );
    break;
    case 'SESSION':
      $this->setLang( $this->_parseSession() );
    break;
    case 'VAR':
      $this->setLang( $this->_parseVar() );
    break;
    default:
      echo '<h1><em>i18n</em> is missconfigurated, incorrect default value: I18N_MODE';
      exit (0);
    break;
  }
  if ($this->getLang(TRUE) === FALSE)
  {
    $this->setLang( $this->getLanguageAccept(), FALSE );
    if (strtoupper($S_Mode) == 'SESSION')
    {
      $this->_phpCMSGlobals['SESSION']->setValue('__I18N__LANG__',$this->getLang());
    }
    //$this->doRedirect();
  }
}

function _parseSuffix()
{
  $S_FileName = $this->_phpCMSGlobals['_GET_POST']['file'];
  $S_FileName = substr ($S_FileName, 0, strlen ($S_FileName) - strlen ($this->_getDefault ('PAGE_EXTENSION')));
  return substr ($S_FileName, strrpos ($S_FileName, '.') + 1, strlen ($S_FileName));
}

function _parseVar()
{
  $A_Params = $this->_phpCMSGlobals['_GET_POST'];
  if(isset($this->_phpCMSGlobals['DEFAULTS']->I18N_PARAMNAME)) {
	  if (isset ($A_Params [$this->_phpCMSGlobals['DEFAULTS']->I18N_PARAMNAME])) {
	  	return $A_Params [$this->_phpCMSGlobals['DEFAULTS']->I18N_PARAMNAME];
	  }

  }
  if (substr($S_Path,3,1) != '/') { return FALSE; }
  return substr($S_Path,1,2);
}

function _parseDir()
{
  $S_Path = $this->_phpCMSGlobals['_GET_POST']['file'];
  if (substr($S_Path,3,1) != '/') { return FALSE; }
  return substr($S_Path,1,2);
}


function _parseSession()
{
  return $this->_phpCMSGlobals['SESSION']->getValue('__I18N__LANG__');
}
function _getDefault ($S_Name)
{
  if(isset($this->_phpCMSGlobals['DEFAULTS']->$S_Name))
  {
    return $this->_phpCMSGlobals['DEFAULTS']->$S_Name;
  } else {
    if (isset($this->_phpCMSGlobals['DEFAULTS']->DEBUG) && 'on' == $this->_phpCMSGlobals['DEFAULTS']->DEBUG)
    {
//    	echo '-1'.$S_Name.'<br />';
//      echo '<h1><em>i18n</em> is missconfigurated, incorrect default value: '.$S_Name.'</h1>';
//      exit (0);
    }
    return FALSE;
  }
}

function _getMode()
{
  return $this->_getDefault('I18N_MODE');
}

}
?>