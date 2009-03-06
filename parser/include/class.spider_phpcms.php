<?php
/* $Id: class.spider_phpcms.php,v 1.2.2.11 2006/06/18 18:07:32 ignatius0815 Exp $ */
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
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

$DEFAULTS->DefaultWriteToDir = '/test';
$DEFAULTS->StartAdress = '/index.htm';
$DEFAULTS->SaveFiles   = '.gif, .jpg, .png, .css, .js, .zip, .pdf';
$DEFAULTS->Extensions  = '.html, .htm';

// protocols not to spider
$DEFAULTS->NoProto[0] = 'http:';
$DEFAULTS->NoProto[1] = 'mailto:';
$DEFAULTS->NoProto[2] = 'gopher:';
$DEFAULTS->NoProto[3] = 'ftp:';
$DEFAULTS->NoProto[4] = 'news:';
$DEFAULTS->NoProto[5] = 'telnet:';
$DEFAULTS->NoProto[6] = 'javascript:';
$DEFAULTS->NoProto[7] = 'about:';
$DEFAULTS->NoProto[8] = 'file:';
$DEFAULTS->NoProto[9] = 'https:';

if(!isset($DEFAULTS->IgnorFiles)) {
	$DEFAULTS->IgnorFiles = '';
}
if(!isset($DEFAULTS->SaveFiles)) {
	$DEFAULTS->SaveFiles = '';
}
if(!isset($DEFAULTS->Extensions)) {
	$DEFAULTS->Extensions = '';
}
if(!isset($DEFAULTS->StartAdress)) {
	$DEFAULTS->StartAdress = '';
}
if(!isset($DEFAULTS->DefaultWriteToDir)) {
	$DEFAULTS->DefaultWriteToDir = '';
}

class CheckFile {
	function CheckFile() {
	}

	function DoFile($PathAndFile) {
		global $DEFAULTS, $MESSAGES;

		unset($this->QUERY_STRING);

		if(stristr($PathAndFile, '?')) {
			$this->parms = substr($PathAndFile, strpos($PathAndFile, '?'));
			$this->QUERY_STRING = substr($PathAndFile, strpos($PathAndFile, '?') + 1);
			$PathAndFile = substr($PathAndFile, 0, strpos($PathAndFile, '?'));
		}

		if(stristr($PathAndFile, '#')) {
			$this->anchor = substr($PathAndFile, strpos($PathAndFile, '#') + 1);
			$PathAndFile = substr($PathAndFile, 0, strpos($PathAndFile, '#'));
		}
		if(file_exists($DEFAULTS->DOCUMENT_ROOT.$PathAndFile) AND is_dir($DEFAULTS->DOCUMENT_ROOT.$PathAndFile)) {
			$this->path = $PathAndFile;
			if($this->path == '\\' OR $this->path == '/' OR $this->path == '\\/') {
				$this->path = '';
			}
			if(substr($this->path, -1) == '/') {
				$this->path = substr($this->path, 0, -1);
			}
			$this->name = '';
			$this->extension = '';
			return;
		}

		$this->name = basename($PathAndFile);
		$this->path = dirname($PathAndFile);
		if(substr($this->path, -1) == '/') {
			$this->path = substr($this->path, 0, -1);
		}
		if(strrpos($this->name, '.') > 0) {
			$this->extension = substr($this->name, strrpos($this->name, '.') + 1);
		} else {
			$this->extension = '';
		}

		if($this->path == '\\' OR $this->path == '/' OR $this->path == '\\/') {
			$this->path = '';
		}
		if(!file_exists($DEFAULTS->DOCUMENT_ROOT.$this->path.'/'.$this->name)) {
			$DEFAULTS->ErrorPages[count($DEFAULTS->ErrorPages)] = $this->path.'/'.$this->name;
			$this->error = 'not found';
		}
	}
}

function WriteTextRow($String, $FieldName, $DefaultValue) {
	global $DOCUMENT;
	echo '<tr bgcolor="'.$DOCUMENT->ROW_LIGHT.'">'.
		'<td valign="BOTTOM" width="250">'.$DOCUMENT->TABLE_FONT.$String.':</font></td>'.
		'<td valign="TOP">'.$DOCUMENT->TABLE_FONT.'<input type="TEXT" name="'.$FieldName.'" value="'.$DefaultValue.'" size="10" maxsize="30" style="width:128px;"></font></td>'.
		'</tr>';
}

switch(strtoupper($action)) {
	case 'START':
		// init vars
		set_time_limit(0);
		$DEFAULTS->STEALTH          = 'on';
		$DEFAULTS->SavedFiles       = array();
		$DEFAULTS->ToSpider         = array();
		$DEFAULTS->AllreadySpidered = array();
		$DEFAULTS->CreateDir        = array();
		$DEFAULTS->ErrorPages       = array();
		$DEFAULTS->ResultEntrys     = array();
		$HELPER                     = new helper;
		$CHECK_PAGE                 = new CheckFile;

		// set startadress
		if(isset($startadress)) {
			$DEFAULTS->ToSpider[0] = trim($startadress);
		} else {
			$DEFAULTS->ToSpider[0] = $DEFAULTS->StartAdress;
		}
		if(isset($savefiles)) {
			$DEFAULTS->SaveFiles = split(",", str_replace(' ', '', $savefiles));
		}
		if(isset($extensions)) {
			$DEFAULTS->Extensions = split(",", str_replace(' ', '', $extensions));
		}
		if(isset($redirections)) {
			$DEFAULTS->Redirections = split(",", str_replace(' ', '', $redirections));
		}
		if(isset($redirpage)) {
			$DEFAULTS->RedirPage = split(",", str_replace(' ', '', $redirpage));
		}
		if(isset($ignorfiles)) {
			$DEFAULTS->IgnorFiles = split(",", str_replace(' ', '', $ignorfiles));
		}

		// create Spider Object
		$SPIDER = new spider_phpcms;

		// check or create outputdir
		if(!file_exists($outputdir)) {
			$SPIDER->CreateDir($DEFAULTS->DOCUMENT_ROOT.$outputdir);
		}
		$DEFAULTS->WriteToDir = $DEFAULTS->DOCUMENT_ROOT.$outputdir;

		// start parsing
		$title = 'phpCMS - Spider';
		DrawHeader($title);

		echo '</td></tr></table>'.DrawTopLine($title).'</td></tr></table>'.

			'<table border="0" cellspacing="0" cellpadding="0" width="600"><tr><td align="center">'.
			'<form name="progress">'.
			"\n";

		echo '<table border="0" cellspacing="0" cellpadding="0">'.

			'<tr><td width="60">'.$DOCUMENT->TABLE_FONT.'&nbsp;</font></td>'.
			'<td width="10">'.$DOCUMENT->TABLE_FONT.'&nbsp;&nbsp;</font></td>'.
			'<td align="left" width="25%">'.$DOCUMENT->TABLE_FONT.'0%</font></td>'.
			'<td align="center" width="25%">'.$DOCUMENT->TABLE_FONT.'50%</font></td>'.
			'<td align="right" width="25%">'.$DOCUMENT->TABLE_FONT.'100%</font></td></tr>'.

			'<tr><td align="right">'.$DOCUMENT->TABLE_FONT.$MESSAGES[118].':</font></td>'.
			'<td width="10">'.$DOCUMENT->TABLE_FONT.'&nbsp;&nbsp;</font></td>'.
			'<td colspan="3">'.$DOCUMENT->TABLE_FONT.'<input type="text" value="" name="bar" size="43" style="width=525px"></font></td></tr>'.

			'<tr><td  align="right">'.$DOCUMENT->TABLE_FONT.$MESSAGES[119].':</font></td>'.
			'<td width="10">'.$DOCUMENT->TABLE_FONT.'&nbsp;&nbsp;</font></td>'.
			'<td colspan="3">'.$DOCUMENT->TABLE_FONT.'<input type="text" name="current" value="" size="43" style="width=525px"></font><br /></td></tr>'.

			'<tr><td colspan="5">'.$DOCUMENT->TABLE_FONT.'&nbsp;</font></td></tr>'.
			'<tr><td colspan="5" bgcolor="'.$DOCUMENT->DARK_COLOR.'"><img src="gif/gruen.gif" width="2" height="1"></td></tr>'.
			'<tr><td colspan="5">'.$DOCUMENT->TABLE_FONT.'&nbsp;</font></td></tr>'.

			'</table>'.
			"\n";

		echo '<table border="0" cellspacing="0" cellpadding="0" width="100%">'.
			'<tr>'.
			'<td width="70">'.$DOCUMENT->TABLE_FONT.'&nbsp;</font></td>'.
			'<td>'.$DOCUMENT->TABLE_FONT.'actual number: </font></td>'.
			'<td>'.$DOCUMENT->TABLE_FONT.'<input type="text" name="curnumber" value="" size="4" style="width=100px"></font></td>'.
			'<td>'.$DOCUMENT->TABLE_FONT.'to spider: </font></td>'.
			'<td>'.$DOCUMENT->TABLE_FONT.'<input type="text" name="tospider" value="" size="4" style="width=100px"></font></td>'.
			'</tr>'.

			'<tr><td colspan="5">'.$DOCUMENT->TABLE_FONT.'&nbsp;</font></td></tr>'.
			'<tr><td colspan="5" bgcolor="'.$DOCUMENT->DARK_COLOR.'"><img src="gif/gruen.gif" width="2" height="1"></td></tr>'.
			'<tr><td colspan="5">'.$DOCUMENT->TABLE_FONT.'&nbsp;</font></td></tr>'.

			'</table>'.
			"\n";

		echo '<table border="0" cellspacing="0" cellpadding="0" width="100%">'.
			'<tr>'.
			'<td>'.$DOCUMENT->TABLE_FONT.$MESSAGES[120].':</font></td>'.
			'<td>'.$DOCUMENT->TABLE_FONT.$MESSAGES[57].':</font></td>'.
			'</tr>'.

			'<tr>'.
			'<td>'.$DOCUMENT->TABLE_FONT.'<textarea name="directories" cols="24" rows="7" style="width=295px" wrap="off"></textarea></font></td>'.
			'<td>'.$DOCUMENT->TABLE_FONT.'<textarea name="errors" cols="24" rows="7" style="width=295px" wrap="off"></textarea></font></td>'.
			'</tr>'.

			'<tr><td colspan="2">'.$DOCUMENT->TABLE_FONT.'&nbsp;</font></td></tr>'.
			'<tr><td colspan="2" bgcolor="'.$DOCUMENT->DARK_COLOR.'"><img src="gif/gruen.gif" width="2" height="1"></td></tr>'.
			'<tr><td colspan="2">'.$DOCUMENT->TABLE_FONT.'&nbsp;</font></td></tr>'.

			'</table>'.
			"\n";

		flush();

		echo '</form>'.
			'</td></tr></table>';

		$i = count($DEFAULTS->ToSpider);
		$Counter = 0;

		while($i > 0) {
			$CurrentEntry = $DEFAULTS->ToSpider[0];
			//echo $CurrentEntry.'<br />';

			echo '<script language="Javascript">';
			echo '<!--//'."\n";

			// Status ermitteln
			$AllStat = count($DEFAULTS->ToSpider) + count($DEFAULTS->AllreadySpidered);
			$prozent = ($AllStat / 100);
			$RelStat = floor(count($DEFAULTS->AllreadySpidered) / $prozent);
			if($Counter < ($RelStat / 1.35)) {
				$Counter++;
				echo 'document.progress.bar.value=document.progress.bar.value+"#";';
				flush();
			}

			echo 'document.progress.current.value="'.$CurrentEntry.'";'.
				'document.progress.curnumber.value="'.count ($DEFAULTS->AllreadySpidered).'";'.
				'document.progress.tospider.value="'.count ($DEFAULTS->ToSpider).'";';

			$text = '';
			for($j = 0; $j < count($DEFAULTS->CreateDir); $j++) {
				$temp = substr($DEFAULTS->CreateDir[$j], strlen($DEFAULTS->DOCUMENT_ROOT));
				$text = $text.$temp."\\n";
			}
			echo 'document.progress.directories.value="'.$text.'";';

			$text = '';
			for($j = 0; $j < count($DEFAULTS->ErrorPages); $j++) {
				$text = $text.$DEFAULTS->ErrorPages[$j]."\\n";
			}
			echo 'document.progress.errors.value="'.$text.'";'.
				'//-->'."\n".
				'</script>'."\n";

			if(strlen(trim($CurrentEntry)) > 0) {
				$SPIDER->FileSpider($CurrentEntry);
			}
			$SPIDER->RemoveEntry($CurrentEntry);
			$i = count($DEFAULTS->ToSpider);
		}
		echo '</body></html>';
		break;

	default:
		$title = $MESSAGES[121];
		DrawHeader($title);
		DrawTopLine($title);
		echo '<table border="0" cellspacing="2" cellpadding="2" width="100%">'.
			'<form method="post" action="'.$DEFAULTS->SELF.'">'.
			'<input type="hidden" name="phpcmsaction" value="SPIDER">'.
			'<input type="hidden" name="action" value="START">'.
			'<input type="hidden" name="seceret" value="'.$DEFAULTS->PASS.'">';

		WriteTextRow($MESSAGES[122], 'ignorfiles', $DEFAULTS->IgnorFiles);
		WriteTextRow($MESSAGES[123], 'savefiles', $DEFAULTS->SaveFiles);
		WriteTextRow($MESSAGES[124], 'extensions', $DEFAULTS->Extensions);
		WriteTextRow($MESSAGES[125], 'startadress', $DEFAULTS->StartAdress);
		WriteTextRow($MESSAGES[126], 'outputdir', $DEFAULTS->DefaultWriteToDir);

		echo '<tr bgcolor="'.$DOCUMENT->ROW_LIGHT.'">'.
			'<td valign="bottom">'.$DOCUMENT->TABLE_FONT.'&nbsp;</font></td>'.
			'<td valign="bottom">'.$DOCUMENT->TABLE_FONT.'<input type="submit" name="SUBMIT" value="'.$MESSAGES[127].'"></font></td>'.
			'</tr>';

		echo '</form></table>';
		DrawBottomLine($MESSAGES[128].'!');
		DrawFooter();
		break;
	}

?>