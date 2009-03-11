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

defined('SKYBLUE') or die(basename(__FILE__));

/**
* xmlHandler class is responsible for all XML parsing tasks. The class
* is not called directly but is loaded as a helper class by the SkyBlue
* Core class. To access the class, your code should call
* $Core->xmlHandler-><functionName>().
*
* To parse an XML file, use the syntax:
*
* $Core->xmlHandler->ParserMain($<path_to_file>);
*
* @package SkyBlue
*/

class xmlHandler 
{
    var $fp   = null;
    var $test = null;
    
    function __construct() 
    {
        ;
    }
    
    function xmlHandler()
    {
        $this->__construct();
    }
    
    function test()
    {
        echo '<h2>'.$this->test.'</h2>';
    }
    
    function xmlToObj($fp) 
    {
        $objs   = array();
        $objs   = $this->ParserMain($fp);
        $newObj = new stdClass;
        
        foreach ($objs as $obj) 
        {
            $k = $obj->title;
            $v = $obj->value;
            $newObj->$k = $v;
        }
        return $newObj;
    }
    
    function ParserMain($xmlfile) 
    {
        $xml_parser = xml_parser_create('');
        $myparser   = new XMLParser;
        xml_set_object($xml_parser, $myparser);
        xml_set_element_handler($xml_parser, "_starttag", "_endtag");
        xml_set_character_data_handler($xml_parser, "_data");
        $fp = fopen($xmlfile,"r")
            or die("Error reading RSS data in $xmlfile");
        
        while ($data = fread($fp, 4096))
            xml_parse($xml_parser, $data, feof($fp))
                or die(sprintf("XML error: %s at line %d", 
                    xml_error_string(xml_get_error_code($xml_parser)), 
                    xml_get_current_line_number($xml_parser)));
        fclose($fp);
        xml_parser_free($xml_parser);
        return $myparser->items;
    }
    
    function xmlDocType() 
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
    }
    
    function xmlRoot($innerXML = '') 
    {
        $xml  = $this->xmlDocType();
        $xml .= '<root>'."\r\n";
        $xml .= $innerXML;
        $xml .= '</root>';
        return $xml;
    }
    
    function xmlObj($innerXML = '', $type='') 
    {
        $xml = '';
        if (is_array($innerXML)) 
        {
            for ($i=0; $i<count($innerXML); $i++) 
            {
                if (!empty($type)) 
                {
                    $type = ' type="'.$type[$i].'"';
                }
                $xml .= "\t".'<obj'.$type.'>'."\r\n";
                $xml .= $innerXML[$i];
                $xml .= "\t".'</obj>';
            }
        } 
        else 
        {
                if (!empty($type)) 
                {
                    $type = ' type="'.$type.'"';
                }
                $xml .= "\t".'<obj'.$type.'>'."\r\n";
                $xml .= $innerXML;
                $xml .= "\t".'</obj>'."\r\n";
        }
        return $xml;
    }
    
    function ObjsToXML($objs, $type='') 
    {
        $xml = '';
        foreach ($objs as $obj) 
        {
            $xml .= '<item ';
            $xml .= isset($obj->id) ? 'id="'.$obj->id.'"'."\r\n" : '' ;
            $count = 0;
            $minus = 0;
            foreach ($obj as $k=>$v) {
                if ($k != 'description' && 
                     $k != 'type' &&
                     $k != 'id' &&
                     trim($k) != '')
                {
                    $count++;
                }
            }
            $i = 0;
            foreach ($obj as $k=>$v) 
            {
                if ($k != 'description' && 
                     $k != 'type' &&
                     $k != 'id' &&
                     trim($k) != '')
                {
                    $indent = '';
                    if (isset($obj->id) || 
                         $i != 0)
                    {
                        $indent = str_repeat(' ', 6);
                    }
                    $xml .= $indent.$k.'="'.htmlentities($v).'"';
                    $xml .= ($i + 1) != $count ? "\r\n" : '' ;
                    $i++;
                }
            }
            $xml .= '>'."\r\n";
            if (isset($obj->description) && 
                        trim($obj->description) != '') 
            {
                $xml .= str_repeat(' ', 6);
                $xml .= '<description><![CDATA[';
                $xml .= trim($obj->description);
                $xml .= ']]></description>'."\r\n";
            }
            $xml .= '</item>'."\r\n"."\r\n";
        }
        $xml = $this->xmlObj($xml, $type);
        $xml = $this->xmlRoot($xml);
        $xml = $this->xmlSafe($xml);
        return $xml;
    }

    function xmlSafe($xml) 
    {
        $xml_arr = explode('&amp;', $xml);
        for ($i=0; $i<count($xml_arr); $i++) 
        {
            $xml_sub = explode('&', $xml_arr[$i]);
            $xml_sub = implode('&amp;', $xml_sub);
            $xml_arr[$i] = $xml_sub;
        }
        $xml = implode('&amp;', $xml_arr);
        return $xml;
    }

}

class XMLParser 
{
    var $insideitem = false;
    var $tag        = '';
    var $type       = '';            // The object type
    var $items      = array();        // The array of objects to be returned
    var $properties = array();        // An array to store tag attributes
    var $element    = null;
    
    function __construct() 
    {
        ;
    }
    
    function XMLParser()
    {
        $this->__construct();
    }

    
    // Child element tag variables
    
    var $description = '';    
    
    function test()
    {
        echo '<h2>xmlParser successfully loaded.</h2>';
    }

    function _starttag($parser, $tagName, $attrs) 
    {
        if ($this->insideitem) 
        {
            $this->tag = $tagName;
        } 
        else  
        {
            
            switch ($tagName) 
            {
                case 'OBJ':
                    $this->element = new stdClass;
                    $this->type = $attrs['TYPE'];
                    break;
                
                case 'ITEM':
                    foreach ($attrs as $k => $v) 
                    {
                        $this->properties[$k] = $v;
                    }
                    $this->insideitem  = true;
                    break;
            }
        }
    }

    function _endtag($parser, $tagName) 
    {
        switch ($tagName) 
        {
            case 'ITEM':
                $this->element->type = $this->type;
                foreach($this->properties as $k => $v) 
                {
                    $k2 = strToLower($k);
                    if (strlen( $k2 ) > 0) $this->element->$k2 = $v;
                    $this->properties[$k] = null;
                }
                $this->items[]    = $this->element;
                $this->element    = null;
                $this->insideitem = false;
                break;
                
            case 'OBJ':
                $this->type = null;
                break;
        }
    }

    function _data($parser, $data) 
    {
        if ($this->insideitem) 
        {
            $index = strToLower($this->tag);
            if (!isset($this->properties[$index])) 
            {
                $this->properties[$index] = null;
            }
            $this->properties[$index] .= $data;
        }
        
    }
}

?>
