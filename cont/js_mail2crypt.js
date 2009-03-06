/* $Id: js_mail2crypt.js,v 1.1.2.4 2003/12/02 23:16:00 mjahn Exp $ */
/* 
   +----------------------------------------------------------------------+
   | phpCMS Content Management System - Version 1.2.0
   +----------------------------------------------------------------------+
   | phpCMS is Copyright (c) 2001-2003 by Michael Brauchl 
   | and Contributing phpCMS Team Members
   | Mail2Crypt Copyright (c) 2002-2003 Henning Poerschke
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
*/

/*###################################################################
# js_mail2crypt.js, Version 1.5 (Feb 13 2003)
# (c) 2002-2003 by Henning Poerschke - WebMediaConception.com
# Released under the GNU General Public License
#
# To be used in conjunction with phpCMS 1.1.8+ with phpMail2Crypt 1.4.1+
#
# This script was inspired by Spam Vaccine by matterform.com and Character Encoder by Mike McGrath.
#
# Note though that js_mailcrypt.js does something quite different than, and in addition to, these.
# The idea of replacing characters in e-mail addresses by their respective ASCII codes is by no means
# new, nor original.
#
# While the SpamVaccine script alledgedly "juggles the pieces around", the ASCII code is still there unmodified.
# Enter phpMail2Crypt -- it actually scrambles the ASCII so it's practically impossible for spambots
# to detect.
#
# The touchy part is the <noscript></noscript>. Here of course, the ASCII cannot be scrambled since
# this would render non-js browsers unable to unscramble and read it.
#
# This is addressed by using an image for the @ character.
# Thus there is no discernable e-mail address in this part either.
#
# In order to encrypt your e-mail addresses manually you may use 
# Unicode Character Encoder by Mike McGrath:
# http://website.lineone.net/~mike_mcgrath/ 
# http://javascript.internet.com/passwords/character-encoder.html
#####################################################################*/

function showmail(nospamplease, idontlikespam, nothanks, no, way) {

  var str_out = '';
  var num_out = '';
  var num_in;
  var hex = '0123456789abcdef';

  num_out = nospamplease;
  for(i = 0; i < num_out.length; i += 2) {
    num_in1 = '';
    num_in = parseInt(num_out.substr(i,2)) + 23;
    while (num_in != 0) {
      num_in1 = hex.charAt(num_in%16)+num_in1;
      num_in = num_in >> 4;
    }
    num_in = unescape('%' + num_in1);
    str_out += num_in;
    str_out = unescape(str_out);
  }
	nospamplease = str_out;
	speakfriendandenter = '&#109;&#97;&#105;&#108;&#116;&#111;:' + nospamplease;

	voila = '<a href="' + speakfriendandenter + '" title="'+ nospamplease + '">';
	
	if((idontlikespam != "") && (nothanks != "")) {
		voila = idontlikespam  + " " + voila;
	}
	else if((idontlikespam != "") && (nothanks == "")) {
		voila += idontlikespam;
	}
	if(nothanks != "") {
		voila += '<img src="' + nothanks + '" width="' + no + '" height="' + way + '" border="0" alt="' + nospamplease + '" />';
	}
	else if((idontlikespam == "") && (nothanks == "")) {
		voila += nospamplease ;
	}
	voila += '</a>';
	document.write(voila);
	nospamplease = "";
	idontlikespam = "";
	nothanks = "";

} // end showmail
