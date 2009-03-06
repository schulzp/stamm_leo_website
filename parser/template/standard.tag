; TAGS.TAG
;
; This file decribes the PHPCMS - tags, wich should be changed
; by the parser.
;
; text to change       :=  text change to
</ZF>                  :=  </tr>
</SF>                  :=  </td>
<SR>                   :=  <td valign="top" width="1000" bgcolor="#ddeedd"><font face="Verdana, Helvetica, Arial, sans-serif" size="2">

<TABELLE>              :=  <table border="0" cellspacing="0" cellpadding="0" width="590" bgcolor="#006600">
                           <tr><td>
                           <table  border="0" cellspacing="2" cellpadding="5" width="100%">
</TABELLE>             :=  </table></td></tr></table>
</TN>                  :=  </table>    
<Z>                    :=  <tr>
</Z>                   :=  </tr>
<S>                    :=  <td bgcolor="#ddeedd" valign="top"><font face="Verdana, Helvetica, Arial, sans-serif" size="2">
<SN>                   :=  <td valign="top" bgcolor="#ddeedd"><font face="Verdana, Helvetica, Arial, sans-serif" size="2">
</S>                   :=  </FONT></TD>
<BILDERRAHMEN>         :=  <table border="0" cellspacing="0" cellpadding="2"><tr><td bgcolor="#006600">
</BILDERRAHMEN>        :=  </td></tr></table>
<F>                    :=  <strong>
</F>                   :=  </strong>
<FT>                   :=  <font class="ft"><font point-size="10pt">
</FT>                  :=  </font></font>
ß                      :=  &szlig;
Ä                      :=  &Auml;
Ö                      :=  &Ouml;
Ü                      :=  &Uuml;
ä                      :=  &auml;
ö                      :=  &ouml;
ü                      :=  &uuml;
<A BLINK=              :=  <a href=
</A BLINK>             :=  </a>
<WICHTIG>              :=  <strong>
</WICHTIG>             :=  </strong>
<US>                   :=  <font face="Verdana, Helvetica, Arial, sans-serif" size="3" color="#006600"><b>
</US>                  :=  </b></font>
<SEITE>                :=  <table border="0" cellspacing="0" cellpadding="2" width="300">
                           <tr><td bgcolor="#006600">
                           <table border="0" cellspacing="0" cellpadding="7" width="100%"><tr><td bgcolor="#ddeedd">
</SEITE>               :=  </td></tr></table></td></tr></table>
;<p>                    :=  <p><font face="Verdana, Helvetica, Arial, sans-serif" size="2">
;</p>                   :=  </font></p>

<q>                   := &#8220;
</q>                  := &#8221;

<li>                   :=  <li><font face="Verdana, Helvetica, Arial, sans-serif" size="2">
</li>                  :=  </font></li>
<anchor="$tag">         := <a name="$tag">&nbsp;</a>

<BLOCKQUOTE>           :=  <blockquote><font face="Verdana, Helvetica, Arial, sans-serif" size="2">
</BLOCKQUOTE>          :=  </font></blockquote>
<B_BOX_START "$title"> :=  <p><font face="Verdana, Helvetica, Arial, sans-serif" size="3" color="#006600"><b>$title</b></font>
<B_BOX_STOP>           :=  </p>
<ACTDATE>              :=  <!-- PAXPHP datum
                                $datum = date("d.m.Y");
                                $uhrzeit = date("H:i");
                                echo $datum," - ",$uhrzeit," Uhr";
                           PAXPHP datum -->

<CURDATE>              :=  <!-- PAXPHP datetime
                                $date = date("M d Y");
                                $time = date("H:i");
                                echo $date," - ",$time," h";
                           PAXPHP datetime -->

<BACKLINK>             :=  <br /><a href="javascript:history.back()"  class="back"><img src="../gif/back.gif" border="0" width="12" heigth="10" alt="back" title="back" /></a>
