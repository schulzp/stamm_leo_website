<?php

defined('SKYBLUE') or die('Bad File Request');

function rss_syndicated($item) {
    if (!isset($item->syndicate)) return true;
    return $item->syndicate;
}

function rss_date($date) {
	return date("D, j M Y H:i:s T", strtotime(str_replace('T', null, $date)));
}

function rss_site_description() {
	global $Core;
	
	if (file_exists(RSS_META_FILE)) {
		$meta = $Core->xmlHandler->ParserMain(RSS_META_FILE);
		foreach ($meta as $m) {
			if ($m->name == 'description') {
				return $m->content;
			}
			continue;
		}
	}
	return RSS_NO_DESCRIPTION;
}

function rss_end_of_sentence($shred) {
	if ($shred{strlen($shred)-1} == '.' || 
		strlen($shred) < 1 || 
		strpos($shred, '.') === false)
	{
		return $shred;
	}
	else {
		$words = explode(' ', $shred);
		for ($i=count($words); $i>0; $i--) {
			$word = $words[$i];
			if ($word == '.' || $word{strlen($word)-1} == '.') {
				$shred = implode(' ', array_slice($words, 0, $i+1));
			}
		}
		return $shred;
	}
}

function rss_text_blob($shred, $length=RSS_TEXT_LENGTH) {
	 if (strlen($shred)<=$length) return $shred;
	 $text = null;
	 $n=0;
	 while (strlen($text) < $length || $n==$length) {
		 $text .= $shred{$n};
		 $n++;
	 }
	 return $text;
}

function rss_story_text($filename) {
	global $Core;
	if (file_exists(SB_STORY_DIR.$filename) && 
		!is_dir(SB_STORY_DIR.$filename))
	{
		// Make sure to retain spacing in inline tags
		$shred = str_replace(">", "> ", 
			$Core->SBReadFile(SB_STORY_DIR.$filename));
		if (!empty($shred)) {
			return rss_text_blob(strip_tags($shred), RSS_TEXT_LENGTH);
			return trim(rss_end_of_sentence(
				substr(strip_tags($shred), 0, RSS_TEXT_LENGTH)
			));
		}
		return RSS_NO_DESCRIPTION;
	}
	return RSS_NO_DESCRIPTION;
}

?>