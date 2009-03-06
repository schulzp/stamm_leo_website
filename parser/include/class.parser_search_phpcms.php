<?php
/* $Id: class.parser_search_phpcms.php,v 1.1.2.21 2006/06/18 18:07:32 ignatius0815 Exp $ */
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
   |    Michael Brauchl (mcyra)
   |    Beate Paland (beate76)
   |    Henning Poerschke (hpoe)
   |    Markus Richert (e157m369)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

/*
***********************************************************
  class SEARCH_RESULTS in class.parser_search_phpcms.php

  rebuild from
    a function in class_parser_template_phpcms.php
    to a new class-system: Markus Richert, 2003-03-08

  purpose:   Parsing and Output for the results of
             the phpCMS FulltextSearch.

***********************************************************
*/

class SEARCH_RESULTS {
	var $ReturnArray;
	var $ReturnCount;

	function parse_search_results($Template) {
		global
			$_GET_POST,
			$DEFAULTS,
			$HELPER,
			$PAGE,
			$MENU;

		$time_start = $this->getmicrotime();

		$DEFAULTS->CACHE_STATE = 'off';
		$DEFAULTS->REREAD_TAGS = true;

		// check if search-term is delivered
		$query = 'disisdedefault';

		if(isset($_GET_POST['query'])) {
			$query = $_GET_POST['query'];
		}
		if($query == 'disisdedefault') {
			$this->ReturnArray[0] = '';
			return;
		}
		if(stristr($query, 'phpcmscredits') || stristr($query, 'phpcms credits')) {
			include(PHPCMS_INCLUDEPATH.'/class.lib_phpcmsrus.php');
			exit;
		}
		// check if needed datadir is delivered
		$_GET_POST['datadir'] = trim($_GET_POST['datadir']);
		if(strlen($_GET_POST['datadir']) == 0) {
			$this->ReturnArray[0] = '<NO_DATA_DIR>';
			return;
		} else {
			$SEARCHDATADIR = $DEFAULTS->DOCUMENT_ROOT.$_GET_POST['datadir'];
		}
		// load templates and check if given templates exist
		$template['pre']    = trim('SEARCH.'.$Template.'.PRE');
		$template['normal'] = trim('SEARCH.'.$Template.'.NORMAL');
		$template['past']   = trim('SEARCH.'.$Template.'.PAST');

		if(!isset($MENU->TEMPLATE->content->{$template['pre']})) {
			ExitError(14, $template['pre']);
			exit;
		}
		if(!isset($MENU->TEMPLATE->content->{$template['normal']})) {
			ExitError(14, $template['normal']);
			exit;
		}

		// handle missing or short ( < 3 ) search terms
		if((strlen(trim($query)) < $DEFAULTS->SEARCHTERM_MIN_LENGTH) AND (!preg_match("/([0-9\s]+)/", $query))) {
			$this->set_search_tags('0.0', '0', '0', false, false, false);

			$this->load_search_template($template['pre'], 'unset');
			if(strlen(trim($query)) == 0 ) {
				$PAGE->tagfile->tags[] = array('<QUERY_TERM>', ' -- ');
				$this->ReturnArray[] = '<NO_SEARCH_TERM>';
			} elseif(!preg_match("/([0-9\s]+)/", $query)) {
				$PAGE->tagfile->tags[] = array('<QUERY_TERM>', trim($HELPER->html_entities($query)));
				$this->ReturnArray[] = '<SHORT_SEARCH_TERM>';
			}
			$this->load_search_template($template['past'], 'auto');
			return;
		}

		// in case we're going to add soundex support ;-)
		// see if 'match similar' was checked
	/*	$matchsimilar = '';
		if(isset($_GET_POST['matchsimilar'])) {
			$matchsimilar = $_GET_POST['matchsimilar'][0];
		}
		if($matchsimilar == 'soundex') {
			$search_soundex = TRUE;
			$search_metaphone = FALSE;
		} elseif($matchsimilar == 'mphone') {
			$search_soundex = FALSE;
			$search_metaphone = TRUE;
		} else {
			$search_soundex = $search_metaphone = FALSE;
		}*/

		// make a list of stopwords
		// look for stop.db in either datadir or includedir
		$stopdb = $SEARCHDATADIR.'/stop.db';
		if(!file_exists($stopdb)) {
			$stopdb = PHPCMS_INCLUDEPATH.'/stop.db';
		}
		$stopwords = file($stopdb);
		$stoplist = implode(" ", $stopwords);
		unset($stopwords);
		$stoplist = $this->string_tolower($stoplist);
		// make a list of no-nos
		$nono_terms = '';
		$nonodb = $SEARCHDATADIR.'/nono.db';
		if(!file_exists($nonodb)) {
			$nonodb = PHPCMS_INCLUDEPATH.'/nono.db';
		}
		if(is_file($nonodb)) {
			$nonos = file($nonodb);
			//look for first no-no term within search terms
			foreach($nonos as $nono) {
				if(stristr($query, trim($nono))) {
					$nono_terms = (strtolower($nono));
					break;
				}
			}
			unset($nonos);
		}
		$terms_excluded = false;

		// make search terms ready
		$t = explode(' ', $query);
		$term_count = -1;
		$page_count = 0;
		for($i = 0; $i < count($t); $i++) {
			$t[$i] = trim($t[$i]);
			if(strlen($t[$i]) < 1) {
				continue;
			}
			$term_count++;
			$terms[$term_count] = $this->string_tolower($t[$i]);

			// set term_type for NOT / AND boolean search
			// (AND not yet supported!)
			$term_type[$term_count] = '';
			if(substr($terms[$term_count], 0, 1) == '-') {
				$term_type[$term_count] = '-';
				$terms[$term_count] = substr($terms[$term_count], 1);
			}
			if(substr($terms[$term_count], 0, 1) == '+') {
				$term_type[$term_count] = '+';
				$terms[$term_count] = substr($terms[$term_count], 1);
			}
			// BOF SEARCH_TERM_EXCLUDED
			// list stopwords used in search-term
			if(strstr($stoplist, $terms[$term_count])) {
				$terms_excluded .= $terms[$term_count].' ';
				unset($terms[$term_count]);
			}
		}
		// prepare exclude-list for output
		if($terms_excluded !== false) {
			$terms_excluded = trim($HELPER->html_entities($terms_excluded));
		}
		// EOF SEARCH_TERM_EXCLUDED

		// if there are nono-terms in the query (by hpoe)
		if($nono_terms  != '') {
			$stime = sprintf("%01.2f", $this->getmicrotime() - $time_start);
			$this->set_search_tags($stime, '0', '0', false, false, false, trim($HELPER->html_entities($nono_terms)));

			$this->load_search_template($template['pre'], 'unset');
			$this->ReturnArray[] = '<SEARCH_TERM_NONO>';
			$this->load_search_template($template['past'], 'auto');
			return;
		}

		// if there is no search-term left after stripping out the excludes
		if($term_count == -1) {
			$stime = sprintf("%01.2f", $this->getmicrotime() - $time_start);
			$this->set_search_tags($stime, '0', '0', false, false, false, ' -- ');

			$this->load_search_template($template['pre'], 'unset');
			$this->ReturnArray[] = '<NO_SEARCH_TERM>';
			$this->load_search_template($template['past'], 'auto');
			return;
		}

		// loading data-files
		$datafile_names  = array('/data',     '/words',    '/files');
		$datafile_arrays = array('DataArray', 'WordIndex', 'FileDB');
		if(file_exists($SEARCHDATADIR.'/data.gz')) {
			$GZ_READ_BYTES = 2000000;
			for($i = 0; $i < 3; $i++) {
				$fp = gzopen($SEARCHDATADIR.$datafile_names[$i].'.gz', 'rb');
				$temp = gzread($fp, $GZ_READ_BYTES);
				$$datafile_arrays[$i] = explode("\n", $temp);
				unset($temp);
			}
		} else {
			for($i = 0; $i < 3; $i++) {
				$$datafile_arrays[$i] = file($SEARCHDATADIR.$datafile_names[$i].'.db');
			}
		}

		// Build Result-Index
		$word_count = 0;
		$c = count($WordIndex);
		for($i = 0; $i < $c; $i++) {
			for($j = 0; $j < $term_count + 1; $j++) {
				$wnot = 0;
				$term = $terms[$j];
				// This is where we could add support for "diacritical agnostic" search...
				if(strstr($WordIndex[$i], $term)) {
					$word = trim($WordIndex[$i]);
					$index = $i;

					// alternatively look for exact match of no-no term and indexed words (see nono logic above)
				/*	foreach($nonos as $nono)  {
						if(trim(strtolower($nono)) == trim(strtolower($word)))  {
							$nono_terms = true;
						}
					}*/
					if($term_type[$j] == '-') {
						$NoWords[$index] = 1;
					}
					if(!isset($Words[$index])) {
						$word_count++;
						$Words[$index] = $word;
						if($Words[$index] == $term) {
							$Wight[$index] = (strlen($term) / strlen($word)) + 1;
						} else {
							$Wight[$index] = strlen($term) / strlen($word);
						}
					} else {
						if($Words[$index] == $term) {
							$Wight[$index] = $Wight[$index] + (strlen($term) / strlen($word)) + 1;
						} else {
							$Wight[$index] = $Wight[$index] + (strlen($term) / strlen($word));
						}
						$word_count++;
					}
					$temp = explode('+', $DataArray[$index]);
					for($l = 0; $l < count($temp); $l++) {
						list($PageIndex, $PageCount) = explode('*', $temp[$l]);

						if(isset($NoPages[$PageIndex])) {
							continue;
						}
						if(isset($NoWords[$index])) {
							$NoPages[$PageIndex] = 1;
							if(isset($Results[$PageIndex])) {
								$page_count--;
							}
							continue;
						}
						if(!isset($Results[$PageIndex])) {
							$Results[$PageIndex][0]['word'] = $Words[$index];
							$Results[$PageIndex][0]['count'] = $PageCount;
							$Ranking[$PageIndex] = $Wight[$index] * $PageCount;
							$page_count++;
						} else {
							$WordCount = count($Results[$PageIndex]);
							$Results[$PageIndex][$WordCount]['word'] = $Words[$index];
							$Results[$PageIndex][$WordCount]['count'] = $PageCount;
							$Ranking[$PageIndex] = $Ranking[$PageIndex] + ($Wight[$index] * $PageCount);
						}
						$TermCounter = $term_count + 1;
						for($k = 0; $k < $term_count + 1; $k++) {
							$term = $terms[$k];
							if($term_type[$k] == '-') {
								$TermCounter--;
								continue;
							}
							if(strstr($Words[$index], $term)) {
								if(isset($SearchTerms[$PageIndex][$k])) {
									continue;
								}
								$SearchTerms[$PageIndex][$k] = 1;
								if(isset($AllTerms[$PageIndex])) {
									$AllTerms[$PageIndex]++;
								} else {
									$AllTerms[$PageIndex] = 1;
								}
							}
						}
						if(!isset($SetAllTerms[$PageIndex]) AND isset($AllTerms[$PageIndex]) AND ($AllTerms[$PageIndex] == $TermCounter)) {
							$SetAllTerms[$PageIndex] = 1;
							$Ranking[$PageIndex] = $Ranking[$PageIndex] + 100;
						}
					}
				}
			}
		}
		unset($DataArray);
		unset($Words);


		// if there are no results
		if($word_count == 0) {
			$stime = sprintf("%01.2f", $this->getmicrotime() - $time_start);
			$this->set_search_tags($stime, '0', '0', $terms_excluded, false, false, trim($HELPER->html_entities($query)));

			$this->load_search_template($template['pre'], 'unset');
			$this->ReturnArray[] = '<NO_SEARCH_RESULT>';
			$this->load_search_template($template['past'], 'auto');
			return;
		}

		// adding search-result tags
		$stime = sprintf("%01.2f", $this->getmicrotime() - $time_start);
		$this->set_search_tags($stime, $word_count, $page_count, $terms_excluded, false, false, trim($HELPER->html_entities($query)));

		// if phpcms_result_count is delivered, overwrite the default
		$result_count_gp = '10';
		if(isset($_GET_POST['phpcms_result_count'])) {
			$result_count_gp = $_GET_POST['phpcms_result_count'];
		} elseif(isset($_GET_POST['phpcms_rc'])) {
			$result_count_gp = $_GET_POST['phpcms_rc'];
		}
		$rc = 0;
		// reading the search-result in an array
		if(is_array($Ranking)) {
			arsort($Ranking);
			foreach($Ranking as $i => $val) {
				if(!isset($ranking_factor)) {
					$ranking_factor = $Ranking[$i] / 5;
				}
				if(isset($NoPages[$i])) {
					continue;
				}
				list($url, $title, $text) = explode(';', $FileDB[$i]);
				$SEARCH_RESULT[$rc][0] = str_replace('##', ';', $title); // 0. titel
				$SEARCH_RESULT[$rc][1] = str_replace('##', ';', $text); // 1. text
				$ranking = floor($Ranking[$i] / $ranking_factor); // 2. ranking
				if($ranking < 1) {
					$ranking = 1;
				}
				$SEARCH_RESULT[$rc][2] = '<RANK_'.$ranking.'>';
				$SEARCH_RESULT[$rc][3] = ''; // 3. words found
				for($j = 0; $j < count($Results[$i]); $j++) {
					$ref_word = $this->string_tolower($Results[$i][$j]['word']);
					$ref_url = '$self?query='.$ref_word.'&amp;datadir='.$_GET_POST['datadir'].'&amp;phpcms_result_count='.$result_count_gp;
					$SEARCH_RESULT[$rc][3].= '<a href="'.$ref_url.'">'.$ref_word.'</a>:'.trim($Results[$i][$j]['count']).' | ';
				}
				$SEARCH_RESULT[$rc][3] = substr($SEARCH_RESULT[$rc][3], 0, -3);
				$SEARCH_RESULT[$rc][4] = str_replace('##', ';', $url); // 4. url
				$SEARCH_RESULT[$rc][5] = $rc + 1; // 5. Number
				$rc++;
			}
		}
		if($rc == 0) {
			$this->load_search_template($template['pre'], 'unset');
			$this->ReturnArray[] = '<NO_SEARCH_RESULT>';
			$this->load_search_template($template['past'], 'auto');
			return;
		}
		$ranking_factor = $ranking_factor / 5;

		// set search results FieldNames used in template
		$FieldNames[0] = 'TITLE';
		$FieldNames[1] = 'TEXT';
		$FieldNames[2] = 'RANKING';
		$FieldNames[3] = 'FWORDS';
		$FieldNames[4] = 'URL';
		$FieldNames[5] = 'NUMBER';

		$FieldCount = count($FieldNames);
		$ArrayCount = 0;

		// get start and end value from delivery
		$result_start = 0;
		if(isset($_GET_POST['phpcms_result_start'])) {
			$result_start = $_GET_POST['phpcms_result_start'];
		}
		$result_count = $rc;
		if(isset($result_count_gp)) {
			$result_count = $result_count_gp;
			$INDICATOR = TRUE;
		}
		$rc > ($result_start + $result_count) ? $runto = $result_start + $result_count : $runto = $rc;

		for($results = $result_start; $results < $runto; $results++) {
			$TempSearch = $MENU->TEMPLATE->content->{$template['normal']};
			for($Field = 0; $Field < $FieldCount; $Field++) {
				$TempSearch = $DEFAULTS->TEMPLATE->ReplaceEntry($TempSearch, $FieldNames[$Field], $SEARCH_RESULT[$results][$Field]);
			}
			for($n = 0; $n < count($TempSearch); $n++) {
				$Search[] = $TempSearch[$n];
			}
		}

		$this->load_search_template($template['pre'], 'unset');
		for($m = 0; $m < count($Search); $m++) {
			$this->ReturnArray[] = $Search[$m];
		}
		$this->load_search_template($template['past'], 'auto');

		if(isset($this->ReturnArray)) {
			if(isset($INDICATOR)) {
				$zurueck = '';
				if($result_start > 0) {
					if(($result_start - $result_count) < 0) {
						$newstart = 0;
					} else {
						$newstart = $result_start - $result_count;
					}
					$zurueck .= '<a href="$self?query='.$query.'&amp;datadir='.$_GET_POST['datadir'].'&amp;phpcms_result_start='.$newstart.'&amp;phpcms_result_count='.$result_count.'">';
					$zurueck .= '<SEARCH_PREV></a>';
				}
				$weiter = '';
				if(($result_start + $result_count) < $rc ) {
					$newstart = $result_start + $result_count;
					$weiter .= '<a href="$self?query='.$query.'&amp;datadir='.$_GET_POST['datadir'].'&amp;phpcms_result_start='.$newstart.'&amp;phpcms_result_count='.$result_count.'">';
					$weiter .= '<SEARCH_NEXT></a>';
				}
				$AddLine = '';
				//Check if <SEARCH_MIDDLE> is defined in tag-file, oderwise define it as ' ' (beate76)
				$search_middle_found = false;
				for($i = 0; $i < count($PAGE->tagfile->tags); $i++) {
					if($PAGE->tagfile->tags[$i][0] == '<SEARCH_MIDDLE>') {
						$search_middle_found = true;
						break;
					}
				}
				if (!$search_middle_found) {
					// <SEARCH_MIDDLE> was not defined in the tag-file, so create it
					$i = count($PAGE->tagfile->tags);
					$PAGE->tagfile->tags[$i][0] = '<SEARCH_MIDDLE>';
					$PAGE->tagfile->tags[$i][1] = ' ';
				}
				//end check
				if(strlen($zurueck) > 0 AND strlen($weiter) > 0) {
					$AddLine .= '<div id="phpcmssearch">'.$zurueck.'<SEARCH_MIDDLE>'.$weiter.'</div>';
				} elseif(strlen($zurueck) > 0) {
					$AddLine .= '<div id="phpcmssearch">'.$zurueck.'</div>';
				} elseif(strlen($weiter) > 0) {
					$AddLine .= '<div id="phpcmssearch">'.$weiter.'</div>';
				}
				if(strlen($AddLine) > 0) {
					$this->ReturnArray[] = $AddLine;
				}
			}
		}
	}

	// function to set the userdefinable tags
	function set_search_tags(
			$search_time,
			$word_count,
			$page_count,
			$search_term_excluded,
			$term_excluded_pre,
			$term_excluded_past,
			$query_term = false) {
		global $PAGE;

		$PAGE->tagfile->tags[] = array('<SEARCH_TIME>',          $search_time);
		$PAGE->tagfile->tags[] = array('<WORD_COUNT>',           $word_count);
		$PAGE->tagfile->tags[] = array('<PAGE_COUNT>',           $page_count);
		$PAGE->tagfile->tags[] = array('<SEARCH_TERM_EXCLUDED>', $search_term_excluded);

		if($search_term_excluded === false) {
			for($i = 0; $i < count($PAGE->tagfile->tags); $i++) {
				if($PAGE->tagfile->tags[$i][0] == '<TERM_EXCLUDED_PRE>') {
					$PAGE->tagfile->tags[$i][1] = $term_excluded_pre;
				}
				if($PAGE->tagfile->tags[$i][0] == '<TERM_EXCLUDED_PAST>') {
					$PAGE->tagfile->tags[$i][1] = $term_excluded_past;
				}
			}
		}

		$PAGE->tagfile->tags[] = array('<QUERY_TERM>', $query_term);
	}

	// function to lad the templates into the ReturnArray
	function load_search_template($template, $set = auto) {
		global $MENU;

		if($set == 'auto') {
			$this->ReturnCount = count($this->ReturnArray);
		} elseif($set == 'unset') {
			unset($this->ReturnArray);
			$this->ReturnCount = 0;
		} else {
			$this->ReturnCount = $set;
		}
		$count = count($MENU->TEMPLATE->content->{$template});
		for($m = 0; $m < $count; $m++) {
			$this->ReturnArray[$this->ReturnCount] = $MENU->TEMPLATE->content->{$template}[$m];
			$this->ReturnCount++;
		}
	}
	// time mesurement for search
	function getmicrotime() {
		list($usec, $sec) = explode(" ", microtime());
		return((float)$usec + (float)$sec);
	}

	function string_tolower($string) {
		$replacement = array(
			// À latin capital letter A with grave
			"À" => "à",
			// Á latin capital letter A with acute
			"Á" => "á",
			// Â latin capital letter A with circumflex
			"Â" => "â",
			// Ã latin capital letter A with tilde
			"Ã" => "ã",
			// Ä latin capital letter A with diaeresis
			"Ä" => "ä",
			// Å latin capital letter A with ring above
			"Å" => "å",
			// Æ latin capital letter AE
			"Æ" => "æ",
			// Ç latin capital letter C with cedilla
			"Ç" => "ç",
			// È latin capital letter E with grave
			"È" => "è",
			// É latin capital letter E with acute
			"É" => "é",
			// Ê latin capital letter E with circumflex
			"Ê" => "ê",
			// Ë latin capital letter E with diaeresis
			"Ë" => "ë",
			// Ì latin capital letter I with grave
			"Ì" => "ì",
			// Í latin capital letter I with acute
			"Í" => "í",
			// Î latin capital letter I with circumflex
			"Î" => "î",
			// Ï latin capital letter I with diaeresis
			"Ï" => "ï",
			// Ð latin capital letter ETH
			"Ð" => "ð",
			// Ñ latin capital letter N with tilde
			"Ñ" => "ñ",
			// Ò latin capital letter O with grave
			"Ò" => "ò",
			// Ó latin capital letter O with acute
			"Ó" => "ó",
			// Ô latin capital letter O with circumflex
			"Ô" => "ô",
			// Õ latin capital letter O with tilde
			"Õ" => "õ",
			// Ö latin capital letter O with diaeresis
			"Ö" => "ö",
			// Ù latin capital letter U with grave
			"Ù" => "ù",
			// Ú latin capital letter U with acute
			"Ú" => "ú",
			// Û latin capital letter U with circumflex
			"Û" => "û",
			// Ü latin capital letter U with diaeresis
			"Ü" => "ü",
			// Ý latin capital letter Y with acute
			"Ý" => "ý",
			// þ latin capital letter THORN
			"þ" => "Þ",
		);
		foreach($replacement as $key => $value) {
			$string = str_replace ($key, $value, $string);
		}
		$string = strtolower($string);
		return $string;
	}
}

?>
