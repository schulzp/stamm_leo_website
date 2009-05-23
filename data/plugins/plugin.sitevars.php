<?php

/**
* @version        1.1 RC1 2008-11-20 21:18:00 $
* @package        SkyBlueCanvas
* @copyright      Copyright (C) 2005 - 2008 Scott Edwin Lewis. All rights reserved.
* @license        GNU/GPL, see COPYING.txt
* SkyBlueCanvas is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYING.txt for copyright notices and details.
*/

defined('SKYBLUE') or die(basename(__FILE__));

/*
site.name
site.url
site.map
site.contact_name
site.contact_title
site.contact_address
site.contact_city
site.contact_state
site.contact_zip
site.contact_email
site.contact_phone
site.contact_fax
site.slogan

page.id
page.title
page.url
page.modified("F d, Y h:i A")
page.parent.id
page.parent.title
page.menutext
page.keywords
page.description

page.link(*, li)
page.link(1, 2, 3, li)
page.link(parent)
page.link(children)
page.link(first)
page.link(last)
page.link(next)
page.link(previous)

*/

global $Core;

$Core->RegisterEvent('OnRenderPage', 'plgSiteVars');

function plgSiteVars($html) {
    $SiteVars = new SiteVars($html);
    return $SiteVars->getHtml();
}

class SiteVars {
    
    var $html;
    
    function __construct($html) {
        $this->execute($html);
    }
    
    function SiteVars($html) {
        $this->__construct($html);
    }
    
    function getHtml() {
        return $this->html;
    }

	function execute($html) {
		global $Core;
		
		// Handle redirects first
		
		$this->handleRedirect($html);
		
		$pages  = $Core->xmlHandler->parserMain(SB_PAGE_FILE);
		$config = $Core->xmlHandler->parserMain(SB_CONFIG_XML_FILE);
		$config = $config[0];
		
		$page   = $this->getCurrentPage($pages);
	
		$pageId     = $page->id;
		$pageTitle  = $page->title;
		$pageUrl    = $Core->GetLink($pageTitle, $pageId, null, 1, USE_SEF_URLS);
		
		
		$parent = $Core->SelectObj(
			$pages,
			$page->parent
		);
		
		$parentTitle = null;
		if (isset($parent->title)) $parentTitle = $parent->title;
		
		$modified = $this->formatDate($page->modified);
		
		$regex = '/\[\[page.modified\("(.*)"\)\]\]/i';
		preg_match_all($regex, $html, $matches);
		
		if (count($matches) ==  2) {
			for ($i=0; $i<count($matches[0]); $i++) {
				$token = $matches[0][$i];
				$format = $matches[1][$i];
				$html = str_replace(
					$token,
					$this->formatDate($modified, $format),
					$html
				);
			}
		}
		
		$this->html = $this->getLinks($html, $pages, $page);
		
		$this->assign('site.name',            $this->get($config, 'site_name'));
		$this->assign('site.url',             $this->get($config, 'site_url'));
		$this->assign('site.slogan',          $this->get($config, 'site_slogan'));
		
		foreach ($config as $k=>$v) {
		    $this->assign("site.$k", $v);
		}
		
		$this->assign('page.id',              $this->get($page, 'id'));
		$this->assign('page.title',           $this->get($page, 'title'));
		$this->assign('page.menutext',        $this->get($page, 'name'));
		$this->assign('page.url',             $pageUrl);
		$this->assign('page.modified',        $modified);
		$this->assign('page.parent.id',       $this->get($page, 'parent'));
		$this->assign('page.parent.title',    $parentTitle);
		$this->assign('page.keywords',        $this->get($page, 'keywords'));
		$this->assign('page.description',     $this->get($page, 'meta_description'));
		$this->assign('page.name',            $this->get($page, 'name'));
		
		$this->assign('site.map', $this->getSiteMap($pages, $pageId));
	}
	
	function get($subject, $key, $default=null) {
	    if (is_object($subject)) {
	        if (isset($subject->$key)) return $subject->$key;
	        return $default;
	    }
	    else if (is_array($subject)) {
	        if (isset($subject[$key])) return $subject[$key];
	        return $default;
	    }
	    return $default;
	}
	
	function assign($key, $value) {
	    $this->html = str_replace("[[{$key}]]", $value, $this->html);
	}
	
	function getCurrentPage($pages) {
		global $Filter;
		global $Core;
		return $Core->SelectObj(
			$pages, 
			$Filter->get($_GET, 'pid', DEFAULT_PAGE)
		);
	}
	
	function getSiteMap($pages, $pageId) {
		global $Core;
		$map = null;
		for ($i=0; $i<count($pages); $i++) {
			$page =& $pages[$i];
			if (!$page->published) continue;
			if (empty($page->parent)) {
				$map .= '<li>' . $this->getPageLink($page, null) 
					. $this->getChildren($page, $pages) . '</li>';
			}
		}
		return "<ul>\n{$map}\n</ul>\n";
	}
	
	function getChildren($page, $pages) {
		$children = null;
		for ($n=0; $n<count($pages); $n++) {
			if ($page->id != $pages[$n]->parent) continue;
			$children .= '<li>';
			$children .= $this->getPageLink($pages[$n], null);
			$children .= $this->getChildren($pages[$n], $pages);
			$children .= '</li>';
		}
		return (!empty($children) ? "<ul>$children</ul>" : null);
	}
	
	function handleRedirect($html) {
		global $Core;
		$regex = '/\[\[page.redirect\(([^\)]+)\)\]\]/i';
		if (preg_match_all($regex, $html, $matches) && count($matches) == 2) {
			$Core->SBRedirect($matches[1][0]);
		}
	}
	
	function getLinks($html, $pages, $page) {
		global $Core;
		
		$keywords = array(
			'parent',
			'children',
			'previous',
			'next',
			'first',
			'last'
		);
		
		$regex = '/\[\[page.link\(([^\)]+)\)\]\]/i';
		preg_match_all($regex, $html, $matches);
		
		if (count($matches) ==  2) {
			for ($i=0; $i<count($matches[0]); $i++) {
				$token = $matches[0][$i];
				$args  = $matches[1][$i];
				
				$args = explode(',', $args);
				
				$tag = null;
				if (!is_numeric($args[count($args)-1]) && 
					!in_array($args[count($args)-1], $keywords))
				{
					$tag = trim($args[count($args)-1]);
					unset($args[count($args)-1]);
				}
				
				if ($args[0] == "*") {
					$links = null;
					for ($j=0; $j<count($pages); $j++) {
						if (!$pages[$j]->id || !$pages[$j]->published) continue;
						$links .= $this->getPageLink($pages[$j], $tag);
					}
				}
				else if ($args[0] == "parent") {
					$links = null;
					if ($parent = $Core->SelectObj($pages, $page->parent)) {
						$links .= $this->getPageLink($parent, $tag);
					}
				}
				else if ($args[0] == "previous") {
					$links = null;
					for ($j=0; $j<count($pages); $j++) {
						if ($pages[$j]->id == $page->id && isset($pages[$j-1])) {
							$links .= $this->getPageLink($pages[$j-1], $tag, 'Previous');
						}
					}
				}
				else if ($args[0] == "next") {
					$links = null;
					for ($j=0; $j<count($pages); $j++) {
						if ($pages[$j]->id == $page->id && isset($pages[$j+1])) {
							$links .= $this->getPageLink($pages[$j+1], $tag, 'Next');
						}
					}
				}
				else if ($args[0] == "first") {
					$links = null;
					$links .= $this->getPageLink($pages[0], $tag, 'First');
				}
				else if ($args[0] == "last") {
					$links = null;
					$links .= $this->getPageLink($pages[count($pages)-1], $tag, 'Last');
				}
				else if ($args[0] == "children") {
					$links = null;
					for ($j=0; $j<count($pages); $j++) {
						if ($pages[$j]->parent == $page->id) {
							$links .= $this->getPageLink($pages[$j], $tag);
						}
					}
				}
				else {
					$links = null;
					for ($j=0; $j<count($args); $j++) {
						$_page = $Core->SelectObj($pages, trim($args[$j]));
						if (!$_page->id || !$_page->published) continue;
						$links .= $this->getPageLink($_page, $tag);
					}
				}
				$html = str_replace(
					$token,
					$links,
					$html
				);
			}
		}
		return $html;
	}
	
	function getPageLink($page, $tag, $text=null) {
	    global $Router;
		global $Core;
		$link = $Core->HTML->MakeElement(
			'a',
			array('href' => $Router->GetLink($page->id)),
			empty($text) ? $page->title : $text
		);
		if (!empty($tag)) {
			$link = $Core->HTML->MakeElement($tag, array(), $link);
		}
		return $link;
	}
	
	function formatDate($modified, $format=null) {
		if (empty($modified)) return null;
		$format = empty($format) ? "F d, Y h:i A" : $format;
		$modified = str_replace('T', '', $modified);
		$bits = explode('+', $modified);
		$modified = $bits[0];
		return date($format, strtotime($modified));
	}

}

?>