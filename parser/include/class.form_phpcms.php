<?php
/* $Id: class.form_phpcms.php,v 1.3.2.11 2006/06/18 18:07:29 ignatius0815 Exp $ */
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
   |    Martin Jahn (mjahn)
   |    Henning Poerschke (hpoe)
   +----------------------------------------------------------------------+
*/
if (!defined('PHPCMS_RUNNING')) die('Hacking attempt...');

if(!defined("_FORM_")) {
	define("_FORM_", TRUE);
}

class hidden_value {
	function hidden_value($FIELD_NAME, $FIELD_VALUE) {
		$this->FIELD_NAME = $FIELD_NAME;
		$this->FIELD_VALUE = $FIELD_VALUE;
	}

	function draw_input($FORM) {
		echo '<input type="hidden" name="'.$this->FIELD_NAME.'" value="'.$this->FIELD_VALUE.'">';
	}
}

class checkbox_list {
	function checkbox_list($FIELD_NAME, &$FORM, $FIELD_ARRAY, $SELECT_ARRAY) {
		$this->FIELD_NAME = $FIELD_NAME;
		$this->FIELD_ARRAY = $FIELD_ARRAY;
		$this->SELECT_ARRAY = $SELECT_ARRAY;
	}

	function draw_input($FORM) {
		$entry_count = count($this->FIELD_ARRAY);
		reset($this->FIELD_ARRAY);
		echo '<tr><td align="left" colspan="2">'.$FORM->normal_font;
		while(list($k, $v) = each($this->FIELD_ARRAY)) {
			if(!isset($this->SELECT_ARRAY[$k])) {
				echo '<nobr><input type="checkbox" name="'.$this->FIELD_NAME.'['.$k.']" value="'.$v.'">'.$v.'</nobr>';
			} else {
				echo '<nobr><input type="checkbox" name="'.$this->FIELD_NAME.'['.$k.']" value="'.$v.'" checked>'.$v.'</nobr>';
			}
		}
		echo '</tr>';
	}
}

class select_radio {
	function select_radio($FIELD_NAME, $TITLE, &$FORM, $FIELD_ARRAY, $CHECKED) {
		$this->FIELD_NAME = $FIELD_NAME;
		$this->TITLE = $TITLE;
		$this->FIELD_ARRAY = $FIELD_ARRAY;
		$this->CHECKED = $CHECKED;
	}

	function draw_input($FORM) {
		$entry_count = count($this->FIELD_ARRAY);
		reset($this->FIELD_ARRAY);
		echo '<tr>'.
			'<td nowrap width="'.$FORM->left_size.'">'.$FORM->normal_font.$this->TITLE.'</td>'."\n".
			'<td width="100%">'.$FORM->normal_font;
		while(list($k, $v) = each($this->FIELD_ARRAY)) {
			if(!isset($this->CHECKED[$k])) {
				echo '<nobr><input type="radio" name="'.$this->FIELD_NAME.'" value="'.$v.'" />'.$v.'&nbsp;&nbsp;</nobr>';
			} else {
				echo '<nobr><input type="radio" name="'.$this->FIELD_NAME.'" value="'.$v.'" checked="checked" />'.$v.'&nbsp;&nbsp;</nobr>';
			}
		}
		echo '</td></tr>';
	}
}

class select_box {
	function select_box($FIELD_NAME, $TITLE, &$FORM, $FIELD_ARRAY, $SELECTED) {
		$this->FIELD_NAME = $FIELD_NAME;
		$this->TITLE = $TITLE;
		$this->FIELD_ARRAY = $FIELD_ARRAY;
		$this->selected = $SELECTED;
	}

	function draw_input($FORM) {
		echo '<tr>'.
			'<td nowrap width="'.$FORM->left_size.'">'.$FORM->normal_font.$this->TITLE.'</td>'."\n".
			'<td width="100%">'.$FORM->normal_font.
			'<select name="'.$this->FIELD_NAME .'">';
		reset($this->FIELD_ARRAY);
		while(list($key, $val) = each($this->FIELD_ARRAY)) {
			if($val == $this->selected) {
				echo '<option value="'.$val.'" selected>'.$key;
			} else {
				echo '<option value="'.$val.'">'.$key;
			}
		}
		echo '</select></td></tr>'."\n";
	}
}

class input_text {
	function input_text($FIELD_NAME, $FIELD_SIZE, $TITLE, &$FORM, $FIELD_VALUE) {
		$this->FIELD_NAME = $FIELD_NAME;
		$this->FIELD_SIZE = $FIELD_SIZE;
		$this->TITLE = $TITLE;
		$this->FIELD_VALUE = $FIELD_VALUE;
	}

	function draw_input($FORM) {
		echo '<tr><td nowrap width="'.$FORM->left_size.'">'.$FORM->normal_font.$this->TITLE.'</font></td>'."\n".
			'<td width="100%">'.$FORM->normal_font.
			'<input type="text" name ="'.$this->FIELD_NAME.
			'" value="'.$this->FIELD_VALUE.'" size="'.$this->FIELD_SIZE.
			'"></font></td></tr>'."\n";
	}
}

class show_text {
	function show_text($TITLE, &$FORM, $FIELD_VALUE) {
		$this->TITLE = $TITLE;
		$this->FIELD_VALUE = $FIELD_VALUE;
	}

	function draw_input($FORM) {
		echo '<tr><td nowrap width="'.$FORM->left_size.'">'.$FORM->normal_font.$this->TITLE.'</font></td>'.
			'<td width="100%">'.$FORM->normal_font.$this->FIELD_VALUE.'</font></td></tr>'."\n";
		}
	}

class show_textarea {
	function show_textarea($TEXT, &$FORM) {
		$this->TEXT = $TEXT;
	}

	function draw_input($FORM) {
		echo '<tr><td width="100%" colspan="2">'.$FORM->normal_font.$this->TEXT.'</td></tr>'."\n";
	}
}

class input_password {
	function input_password($FIELD_NAME, $FIELD_SIZE, $TITLE, &$FORM, $FIELD_VALUE) {
		$this->FIELD_NAME = $FIELD_NAME;
		$this->FIELD_SIZE = $FIELD_SIZE;
		$this->TITLE = $TITLE;
		$this->FIELD_VALUE = $FIELD_VALUE;
	}

	function draw_input($FORM) {
		echo '<tr><td nowrap width="'.$FORM->left_size.'">'.$FORM->normal_font.$this->TITLE.'</font></td>'."\n".
			'<td width="100%">'.$FORM->normal_font.'<input type="password" name ="'.$this->FIELD_NAME.'" value="'.$this->FIELD_VALUE.'" size="'.$this->FIELD_SIZE.'"></font></td></tr>'."\n";
	}
}

class extra_container {
	function extra_container($FIELD) {
		$this->FIELD = $FIELD;
	}

	function draw_input($FORM) {
		echo '<tr><td colspan="2">'.$this->FIELD.'</td></tr>'."\n";
	}
}

class area {
	var $name;
	var $title;
	var $order;
	var $content;
	var $content_counter;

	function area($name, $FORM) {
		$this->name = $name;
		$this->normal_font = $FORM->normal_font;
		$this->bold_font = $FORM->bold_font;
	}

	function set_title($title) {
		$this->title = $title;
	}

	function add_extra_container($FIELD) {
		if(!isset($this->content_counter)) {
			$this->content_counter = 0;
		}
		$this->content[$this->content_counter] = new extra_container($FIELD);
	}

	function add_checkbox_list($FIELD_NAME, &$FORM, $FIELD_ARRAY, $SELECT_ARRAY) {
		if(!isset($this->content_counter)) {
			$this->content_counter = 0;
		}
		$this->content[$this->content_counter] = new checkbox_list($FIELD_NAME, $FORM, $FIELD_ARRAY, $SELECT_ARRAY);
		$this->content_counter++;
	}

	function add_select_radio($FIELD_NAME, $TITLE, &$FORM, $FIELD_ARRAY, $CHECKED) {
		if(!isset($this->content_counter)) {
			$this->content_counter = 0;
		}
		$this->content[$this->content_counter] = new select_radio($FIELD_NAME, $TITLE, $FORM, $FIELD_ARRAY, $CHECKED);
		$this->content_counter++;
	}

	function add_hidden_value($FIELD_NAME, $FIELD_VALUE) {
		if(!isset($this->content_counter)) {
			$this->content_counter = 0;
		}
		$this->content[$this->content_counter] = new hidden_value($FIELD_NAME, $FIELD_VALUE);
		$this->content_counter++;
	}

	function add_select_box($FIELD_NAME, $TITLE, &$FORM, $FIELD_ARRAY, $SELECTED) {
		if(!isset($this->content_counter)) {
			$this->content_counter = 0;
		}
		$this->content[$this->content_counter] = new select_box($FIELD_NAME, $TITLE, $FORM, $FIELD_ARRAY, $SELECTED);
		$this->content_counter++;
	}

	function add_input_text($FIELD_NAME, $FIELD_SIZE, $TITLE, &$FORM, $FIELD_VALUE='') {
		if(!isset($this->content_counter)) {
			$this->content_counter = 0;
		}
		$this->content[$this->content_counter] = new input_text($FIELD_NAME, $FIELD_SIZE, $TITLE, $FORM, $FIELD_VALUE);
		$this->content_counter++;
	}

	function add_show_text($TITLE, &$FORM, $FIELD_VALUE='') {
		if(!isset($this->content_counter)) {
			$this->content_counter = 0;
		}
		$this->content[$this->content_counter] = new show_text($TITLE, $FORM, $FIELD_VALUE);
		$this->content_counter++;
	}

	function add_show_textarea($TEXT, &$FORM) {
		if(!isset($this->content_counter)) {
			$this->content_counter = 0;
		}
		$this->content[$this->content_counter] = new show_textarea($TEXT, $FORM);
		$this->content_counter++;
	}

	function add_input_password($FIELD_NAME, $FIELD_SIZE, $TITLE, &$FORM, $FIELD_VALUE='') {
		if (!isset($this->content_counter)) {
			$this->content_counter = 0;
		}
		$this->content [$this->content_counter] = new input_password($FIELD_NAME, $FIELD_SIZE, $TITLE, $FORM, $FIELD_VALUE);
		$this->content_counter++;
	}

	function draw_area(&$FORM) {
		$width = $FORM->width;

		echo '<table border="0" cellspacing="0" cellpadding="0" width="100%">'."\n".
			'<tr>'."\n".
			'<td width="1"><img src="gif/nix.gif" width="1" height="9" border="0" vspace="0" hspace="0"></td>'."\n".
			'<td width="5"><img src="gif/nix.gif" width="5" height="3" border="0" vspace="0" hspace="0"></td>'."\n".
			'<td width="5" rowspan="3"><img src="gif/nix.gif" width="5" height="3" border="0" vspace="0" hspace="0"></td>'."\n".
			'<td rowspan="3"><nobr>'.$this->bold_font.$this->title.':</b></font></nobr></td>'."\n".
			'<td width="5" rowspan="3"><img src="gif/nix.gif" width="5" height="3" border="0" vspace="0" hspace="0"></td>'."\n".
			'<td><img src="gif/nix.gif" width="2" height="3" border="0" vspace="0" hspace="0"></td>'."\n".
			'<td width="10"><img src="gif/nix.gif" width="10" height="3" border="0" vspace="0" hspace="0"></td>'."\n".
			'<td><img src="gif/nix.gif" width="1" height="3" border="0" vspace="0" hspace="0"></td>'."\n".
			'</tr>'."\n";

		echo '<tr>'."\n".
			'<td width = 6 bgcolor ="'.$FORM->border_color.'" colspan = 2><img src="gif/nix.gif" width=6 height=1 border=0 vspace=0 hspace=0></td>'."\n".
			'<td width="85%" bgcolor = "'.$FORM->border_color.'"><img src="gif/nix.gif" width=5 height=1 border=0 vspace=0 hspace=0></td>'."\n".
			'<td width=6 bgcolor = "'.$FORM->border_color.'" colspan=2><img src="gif/nix.gif" width=6 height=1 border=0 vspace=0 hspace=0></td>'."\n".
			'</tr>'."\n";

		echo '<tr>'."\n".
			'<td width=1 bgcolor="'.$FORM->border_color.'"><img src="gif/nix.gif" width=1 height=9 border=0 vspace=0 hspace=0></td>'."\n".
			'<td width=5><img src="gif/nix.gif" width=5 height=3 border=0 vspace=0 hspace=0></td>'."\n".
			'<td><img src="gif/nix.gif" width=2 height=3 border=0 vspace=0 hspace=0></td>'."\n".
			'<td   width=10><img src="gif/nix.gif" width=10 height=3 border=0 vspace=0 hspace=0></td>'."\n".
			'<td bgcolor ="'.$FORM->border_color.'"><img src="gif/nix.gif" width=1 height=3 border=0 vspace=0 hspace=0></td>'."\n".
			'</tr>'."\n";

		echo '<tr>'."\n".
			'<td width=1 bgcolor="'.$FORM->border_color.'"><img src="gif/nix.gif" width=1 height=2 border=0 vspace=0 hspace=0></td>'."\n".
			'<td width=10 colspan=2><img src="gif/nix.gif" width=10 height=3 border=0 vspace=0 hspace=0></td>'."\n".
			'<td colspan = 3 valign=TOP><table border="0" cellspacing="0" cellpadding="2" width="100%">'."\n";
		if(!isset($this->content_counter)) {
			$this->content_counter = 0;
		}
		for($i = 0; $i < $this->content_counter; $i++) {
			$this->content[$i]->draw_input($FORM);
		}
		echo '</table></td>'."\n".
			'<td width=10><img src="gif/nix.gif" width=10 height=3 border=0 vspace=0 hspace=0></td>'."\n".
			'<td bgcolor="'.$FORM->border_color.'"><img src="gif/nix.gif" width=1 height=3 border=0 vspace=0 hspace=0></td>'."\n".
			'</tr>'."\n";


		echo '<tr>'."\n".
			'<td width=1 bgcolor="'.$FORM->border_color.'"><img src="gif/nix.gif" width=1 height=10 border=0 vspace=0 hspace=0></td>'."\n".
			'<td colspan= 6 ><img src="gif/nix.gif" width=1 height=10 border=0 vspace=0 hspace=0></td>'."\n".
			'<td width=1 bgcolor ="'.$FORM->border_color.'"><img src="gif/nix.gif" width=1 height=10 border=0 vspace=0 hspace=0></td>'."\n".
			'</tr>'."\n";

		echo '<tr>'."\n".
			'<td colspan = 8 bgcolor ="'.$FORM->border_color.'"><img src="gif/nix.gif" width=2 height=1 border=0 vspace=0 hspace=0></td>'."\n".
			'</tr>'."\n".
			'</table>'."\n";
	}
}

class form {
	var $bgcolor;
	var $width;
	var $border_color;
	var $font_face;
	var $font_size;
	var $output;
	var $select;
	var $callback;
	var $action;
	var $method;
	var $areas;
	var $s_areas;
	var $buttons;
	var $max_button_size;
	var $area_max_size;
	var $area_left_size;
	var $letter_spacer;

	function form() {
		global $session, $PHP_SELF;

		$this->bgcolor         = '#EEEEEE';
		$this->font_color      = '#000000';
		$this->width           = '400';
		$this->left_size       = '150';
		$this->border_color    = '#000000';
		$this->font_face       = 'Arial,Helvetica,Verdana,sans-serif';
		$this->font_size       = '2';
		$this->action          = $session->write_link($PHP_SELF);
		$this->method          = 'post';
		$this->normal_font     = '<font face="'.$this->font_face.'" size = "'.$this->font_size.'" color="'.$this->font_color.'" style="font-family:'.$this->font_face.'; font-size:'.($this->font_size*6).'px; font-color:'.$this->font_color.';">';
		$this->bold_font       = '<font face="'.$this->font_face.'" size = "'.$this->font_size.'" color="'.$this->font_color.'"  style="font-family:'.$this->font_face.'; font-size:'.($this->font_size*6).'px; font-color:'.$this->font_color.'; font-weight: bold;" ><b>';
		$this->max_button_size = '0';
		$this->area_max_size   = '0';
		$this->area_left_size  = '0';
		$this->area_counter    = '0';
		$this->button_counter  = '0';
		$this->letter_spacer   = '7.5';
	}

	function set_bgcolor($color) {
		$this->bgcolor = $color;
	}

	function set_target($target) {
		$this->target = $target;
	}

	function set_font_color($color) {
		$this->font_color = $color;
	}

	function set_border_color($color) {
		$this->border_color = $color;
	}

	function set_width($width) {
		$this->width = $width;
	}

	function set_left_size($width) {
		$this->left_size = $width;
	}

	function set_select($select) {
		$this->select = $select;
	}

	function set_callback($callback) {
		$this->callback = $callback;
	}

	function add_area($name) {
		$this->areas[$name] = new area($name, $this);
		if(!isset($this->area_counter)) {
			$this->area_counter = 0;
		}
		$this->areas[$name]->order = $this->area_counter;
		$this->area_counter++;
	}

	function set_area_title($name, $title) {
		$this->areas[$name]->set_title($title);
	}

	function add_area_extra_container($name, $FIELD) {
		$this->areas[$name]->add_extra_container($FIELD);
	}

	function add_area_hidden_value($name, $FIELD_NAME, $FIELD_VALUE) {
		$this->areas[$name]->add_hidden_value($FIELD_NAME, $FIELD_VALUE);
	}

	function add_area_checkbox_list($name, $FIELD_NAME, $FIELD_ARRAY, $SELECT_ARRAY) {
		$this->areas[$name]->add_checkbox_list($FIELD_NAME, $this, $FIELD_ARRAY, $SELECT_ARRAY) ;
	}

	function add_area_select_radio($name, $FIELD_NAME, $TITLE, $FIELD_ARRAY, $SELECT_ARRAY) {
		$this->areas[$name]->add_select_radio($FIELD_NAME, $TITLE, $this, $FIELD_ARRAY, $SELECT_ARRAY) ;
	}

	function add_area_select_box($name,$FIELD_NAME, $TITLE, $FIELD_ARRAY, $SELECTED='') {
		$this->areas[$name]->add_select_box($FIELD_NAME, $TITLE, $this, $FIELD_ARRAY, $SELECTED);
	}

	function add_area_show_text($name, $TITLE, $FIELD_VALUE='') {
		$this->areas[$name]->add_show_text($TITLE, $this, $FIELD_VALUE);
	}

	function add_area_show_textarea($name, $TEXT) {
		$this->areas[$name]->add_show_textarea($TEXT, $this);
	}

	function add_area_input_text($name, $FIELD_NAME, $FIELD_SIZE, $TITLE, $FIELD_VALUE='') {
		$this->areas[$name]->add_input_text($FIELD_NAME, $FIELD_SIZE, $TITLE, $this, $FIELD_VALUE);
	}

	function add_area_input_password($name, $FIELD_NAME, $FIELD_SIZE, $TITLE, $FIELD_VALUE='') {
		$this->areas[$name]->add_input_password($FIELD_NAME, $FIELD_SIZE, $TITLE, $this, $FIELD_VALUE);
	}

	function add_button($TYPE, $NAME, $VALUE, $JS="") {
		if(!isset($this->button_counter)) {
			$this->button_counter = 0;
		}
		if(strlen($VALUE) > $this->max_button_size) {
			$this->max_button_size = strlen($VALUE);
		}
		$this->button[$this->button_counter]['type'] = $TYPE;
		$this->button[$this->button_counter]['value'] = $VALUE;
		$this->button[$this->button_counter]['name'] = $NAME;
		$this->button[$this->button_counter]['js'] = $JS;
		$this->button_counter++;
	}

	function sort_areas() {
		while(list($name) = each($this->areas)) {
			$this->s_areas[$this->areas[$name]->order] = $name;
		}
	}

	function draw_buttons() {
		if(!isset($this->button)) {
			return;
		}
		$size  = $this->font_size * 6;
		$style = 'font-family:'.$this->font_face.'; font-size:';
		$style.= $size.'px; font-color:'.$this->font_color;
		$style.= '; width='.$this->max_button_size * 10.5 ;
		echo '<table border=0 cellspacing=0 cellpadding=0 width=100%>'."\n".
			'<tr><td><img src="gif/nix.gif" width=2 height=7 border=0 vspace=0 hspace=0></td></tr>'."\n".
			'<tr><td align=right><table border=0 cellspacing=0 cellpadding=0><tr>'."\n";
		for($i = 0; $i < count($this->button); $i++) {
			echo '<td><img src="gif/nix.gif" width=4 height=7 border=0 vspace=0 hspace=0></td>'."\n".
				'<td>'.$this->normal_font.'<input type="'.$this->button[$i]['type'].'" name ="'.$this->button[$i]['name'].'" value="'.$this->button[$i]['value'].'" '."\n";
			if(strlen($this->button[$i]['js']) > 0) {
				echo 'onClick=\''.$this->button[$i]['js'].'\' ';
			}
			echo 'style="'.$style.'"></font></td>'."\n";
		}
		echo '</tr></table></td></tr>'."\n".
			'</table>';
	}

	function compose_form() {
		echo '<table border="0" cellspacing="0" cellpadding="1" bgcolor="'.$this->border_color.'" width="'.$this->width.'"><tr><td>'."\n".
			'<table border="0" cellspacing="0" cellpadding="10" bgcolor="'.$this->bgcolor.'" width="100%"><tr>'."\n".
			'<form method="'.$this->method.'" action="'.$this->action.'"';
		if(isset($this->target)) {
			echo ' target="'.$this->target.'"';
		}
		//patch by mjahn 2003-01-20
		//echo '><input type="hidden" name="select" value="'.$this->select.'"><input type="hidden" name="callback" value="'.$this->callback.'"><td>';
		echo '><input type="hidden" name="select" value="';
		if (isset($this->select)) {
			echo $this->select;
		}
		echo '"><input type="hidden" name="callback" value="';
		if (isset($this->callback)) {
			echo $this->callback;
		}
		echo '"><td>';
		// end patch by mjahn

		$this->sort_areas();

		for($i = 0; $i < count($this->s_areas); $i++) {
			$name = $this->s_areas[$i];
			$this->areas[$name]->draw_area($this);
		}
		$this->draw_buttons();
		echo '</td></form></tr></table></td></tr></table>'."\n";
	}
}

class get_form {
	function get_form() {
		global $HTTP_GET_VARS, $HTTP_POST_VARS;

		if(isset($HTTP_GET_VARS)) {
			reset($HTTP_GET_VARS);
			while(list($key, $value) = each($HTTP_GET_VARS)) {
				$this->{$key} = $value;
				$this->{"u_$key"} = strtoupper($value);
			}
		}
		if(isset($HTTP_POST_VARS)) {
			reset($HTTP_POST_VARS);
			while(list($key, $value) = each($HTTP_POST_VARS)) {
				$this->{$key} = $value;
				if (is_string($value)) {
					$this->{"u_$key"} = strtoupper($value);
				} else {
					$this->{"u_$key"} = $value;
				}
			}
		}
	}

	function check_buchst($str) {
		$g = 'ABCDEFGHIJKLMNOPQRSTUVWXYZßÖÄÜ';
		$k = 'abcdefghijklmnopqrstuvwxyzäöü';
		$a = $g.$k;

		for($i = 0; $i < strlen($str); $i++) {
			if(!strstr($a, $str[$i])) {
				return false;
			}
		}
		return true;
	}

	function check_email($str) {
		if(!strstr($str, '@')) {
			return false;
		}
		$AddPos = strpos($str, '@');
		$PartOne = substr($str, 0, $AddPos);
		$PartTwo = substr($str, $AddPos+1);
		if(trim($PartOne) == '') {
			return false;
		}
		if(!strstr($PartTwo, '.')) {
			return false;
		}
		return true;
	}

	function get_var($key) {
		if(isset($this->$key)) {
			return $this->$key;
		} else {
			return '';
		}
	}

	function set_var($key,$val) {
		if(isset($key) && isset($val)) {
			$this->$key = $val;
		}
	}
}

?>