<?php
/* $Id: class.images_phpcms.php,v 1.3.2.8 2006/06/18 18:07:30 ignatius0815 Exp $ */
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

$DEFAULTS->SCRIPT_PATH = $PHP->GetScriptPath().'/';
$DEFAULTS->SCRIPT_NAME = $PHP->GetScriptName();
$DEFAULTS->SELF = $DEFAULTS->SCRIPT_PATH.$DEFAULTS->SCRIPT_NAME;

function GraphicHeader($CL) {
	global $DEFAULTS,$SERVER_SOFTWARE, $PHP;
	// check for caching
	if(isset($GLOBALS["HTTP_IF_MODIFIED_SINCE"])) {
		if($PHP->API() == 'cgi') {
			Header('Status: 304 Not Modified');
		} else {
			Header('HTTP/1.1 304 Not Modified');
		}
		exit;
	}

	// now write the cache Header
	Header('Content-type: image/gif');
	Header('Content-length: '.$CL);
	Header('Cache-Control: public');
	Header('Last-Modified: '.gmdate("D, d M Y H:i:s", filemtime($DEFAULTS->DOCUMENT_ROOT.$DEFAULTS->SELF).' GMT'));
}

switch($action) {
	case 'home.gif':
		GraphicHeader("173");
		echo base64_decode(
			'R0lGODlhEAAQALMAAAAAAABmAGZmZpkAAJl'.
			'mAP8AZsyZAP+ZAP/MAP//mczMzP///wAAAA'.
			'AAAAAAAAAAACH5BAEAAAEALAAAAAAQABAAQ'.
			'ARaMMhJqwRArZW46gpWEUkWIkABUMK3EIcB'.
			'gKsVkGgwqBSZbbTcRMB5JVA0CmbJXNokhNp'.
			'gUBuVAKhdFVrSLE4i7q8YrBI5hDTAsz0nCI'.
			'jDOhFiKe4EQ/Ref/ojADs=');
		break;

	case 'reload.gif':
		GraphicHeader("164");
		echo base64_decode(
			'R0lGODlhEAAQALMAAAAAAABmZjOZAACZZoa'.
			'Ghv//mcDAwMzMzPj4+P///wAAAAAAAAAAAA'.
			'AAAAAAAAAAACH5BAEAAAYALAAAAAAQABAAQ'.
			'ARR0JBJq7mYoBLK3t0BZEhZCoJ3iKRXoF/B'.
			'XpoZE8nYBgOCujNJDDYI6SSrpHJ1rNlKhCP'.
			'tmUIArs3SC/VDBGuBTVWVHXrCXy23mzZ/gt'.
			'i4XBcBADs=');
		break;

	case 'webscript.gif':
		GraphicHeader("166");
		echo base64_decode(
			'R0lGODlhEAAQALMAADMzM2ZmAGZmM2ZmZpm'.
			'ZAJmZM5mZZszMZpmZmczMzMzM/////wAAAA'.
			'AAAAAAAAAAACH5BAEAAAoALAAAAAAQABAAQ'.
			'ARTUKVJq7o4rTUGCl2hDdm2aeZGXqiZIIi5'.
			'Smn6zjSHdEM6twsD4WAIxH61JDK5SOByyRu'.
			'g1HwhgEjnDjZRUWEAkKBwxMRMCEEgXGYxfW'.
			'aeXD5VRAAAOw==');
		break;

	case 'webpage.gif':
		GraphicHeader("168");
		echo base64_decode(
			'R0lGODlhEAAQALMAAFBPVBZTzWaZZkTM3Zl'.
			'mmaWZP5mZmZnMzP/lmd3c8AAAAAAAAP///w'.
			'AAAAAAAAAAACH5BAEAAAkALAAAAAAQABAAA'.
			'ARVMEkDjCBXGMkn+yBDdRIQghtAGmAQMAgg'.
			'q5xrI68o18LhDrnPKBEo4D4BIOxDA+BstpA'.
			'KUIiCENIE1WrFqqoAqOtEC8ysWYn5RO6Y2C'.
			'BaaUank+73CAA7');
		break;

	case 'text.gif':
		GraphicHeader("149");
		echo base64_decode(
			'R0lGODlhEAAQALMAAAAAAGZmZmaZZplmmZm'.
			'ZmczMzMzM/////wAAAAAAAAAAAAAAAAAAAA'.
			'AAAAAAAAAAACH5BAEAAAYALAAAAAAQABAAA'.
			'ARC0MhJaz3kiDP0qRlwiOT4TcGhrmxBZWzs'.
			'TjBgj+IxS2ncviqbMLczwHyqYm940wGRySe'.
			'TSOlBi0cktsDteouWsCECADs=');
		break;

	case 'security.gif':
		GraphicHeader("172");
		echo base64_decode(
			'R0lGODlhEAAQALMAAAAAAGZmAGZmZgAA/wC'.
			'ZM2aZAJlmAMwAM/8AAJmZAMzMAP//AJmZmc'.
			'zMzMzM/////yH5BAEAAA4ALAAAAAAQABAAA'.
			'ARZ0MkpGb2XvYaxeALAddUDEIRIas2BKIvq'.
			'bcAwAFunIQ2AjxhGozF45DoJ33BISjYWAZI'.
			'kEaMuEoFEwbCdBK6+hXgsnlADPoUikVi3FZ'.
			'KC+p2Y2s1stnSPiQAAOw==');
		break;

	case 'parent.gif':
		GraphicHeader("127");
		echo base64_decode(
			'R0lGODlhEAAQALMAAAAAAABmADPMM8zMzAA'.
			'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
			'AAAAAAAAAAACH5BAEAAAMALAAAAAAQABAAQ'.
			'AQscMhJ6wQC2I31tkAYSuNQfqAQrIGHdmXs'.
			'onR92aba0mJ2fh0chqUJBnFISgQAOw==');
		break;

	case 'move.gif':
		GraphicHeader("166");
		echo base64_decode(
			'R0lGODlhEAAQALMAAAAAAGZmZgAzmQAA/zN'.
			'm/2aZmZlmZpmZmf//mczMzP///wAAAAAAAA'.
			'AAAAAAAAAAACH5BAEAAAkALAAAAAAQABAAQ'.
			'ARTMMlJqzxIZXQUIQogAkmgnIhJWiw2vG9C'.
			'HsFBH+yEaVpxnodVqeKCDWSskVKUa3Z+Qad'.
			'CIAgdDIVrgWKiUg2/VOgy9Zo2CglpCeigEU'.
			'hLINlkRQAAOw==');
		break;

	case 'image.gif':
		GraphicHeader("181");
		echo base64_decode(
			'R0lGODlhEAAQALMAAAAAAAAAZmYAAGZmAGZ'.
			'mZgAA2maZAGaZZtcXAJlmmZmZAJmZZv//AJ'.
			'mZme3t7QAAACH5BAEAAAwALAAAAAAQABAAA'.
			'ARisJF2Uj2S6d2c/1+zaQRoOoRWBN3pADBT'.
			'rK3hDI4NOM0cMCUPDKbY+TStAQDBVB6RnyX'.
			'T0AD8NkEDQOBZvEScKEIggPFGyaWneMZ+FL'.
			'nbDgx9DYftuisPbPj/gHQjg4QaEQAAOw==');
		break;

	case 'folder.gif':
		GraphicHeader("152");
		echo base64_decode(
			'R0lGODlhEAAQALMAAJmZAMzMZv/Mmf//mcz'.
			'MzP/MzP//zP///wAAAAAAAAAAAAAAAAAAAA'.
			'AAAAAAAAAAACH5BAEAAAQALAAAAAAQABAAA'.
			'ARFkMhJq70XaI0lOIYxDAAGBGgabJsXvnA4'.
			'BO5o34NAE4CI58Bd7zcS5IQ+m3E5q+GYOue'.
			'gkDMYC1GeFQgVZE+qcKpDLk8iADs=');
		break;

	case 'edit.gif':
		GraphicHeader("172");
		echo base64_decode(
			'R0lGODlhEAAQALMAAAAAAABmAABmZmZmZgC'.
			'ZmQD//5lmZpmZmZnMzMyZmczMzP///wAAAA'.
			'AAAAAAAAAAACH5BAEAAAEALAAAAAAQABAAQ'.
			'ARZMEgJqrW06L2B+gqQFQJRCpoXToF3KAm4'.
			'LIc4ct0K4JwaXkBVDGGjwECvz6DYOpQMJme'.
			'FRSCZriSPDcUrEFyialfgCnF5ZAVikWD61I'.
			'rkaqJCxD9JZiuIiQAAOw==');
		break;

	case 'delete.gif':
		GraphicHeader("137");
		echo base64_decode(
			'R0lGODlhEAAQALMAAJkAAJlmZswAAMwzM/8'.
			'AAP9mAP+ZM8zMzAAAAAAAAAAAAAAAAAAAAA'.
			'AAAAAAAAAAACH5BAEAAAcALAAAAAAQABAAQ'.
			'AQ28MhJqz2CAH0x4ZMAVIMBVgQVCEGHXRkg'.
			'ujF1TqOburSZUzNLDfPrDGmg22RQEFh2SyV'.
			'veogAADs=');
		exit;

	case 'browse.gif':
		GraphicHeader("182");
		echo base64_decode(
			'R0lGODlhEAAQALMAAAAAMzMzZmZmZhIpmS4'.
			'zzB9mmRdm4WZmzCeq5pmZZsyZZpnl5eXYmc'.
			'zMzAAAAAAAACH5BAEAAA0ALAAAAAAQABAAQ'.
			'ARjsMlJm7mmCaZMwSAoEYNgGQRhIAuSCUQT'.
			'DMQXGkN1I66UEQkGhkJouQQJnI2IKTRqkhy'.
			'tMHBWKLdJZmQ4LC483ja6ucSerK9kcwiFww'.
			'ZBp4a6TgS46YXbhE0ANTZKRCl2FBEAADs=');
		break;

	case 'audio.gif':
		GraphicHeader("173");
		echo base64_decode(
			'R0lGODlhEAAQALMAAAAAAGZmAGZmZmZmmZm'.
			'ZAJmZZv//AJmZmczMzP///wAAAAAAAAAAAA'.
			'AAAAAAAAAAACH5BAEAAAgALAAAAAAQABAAQ'.
			'ARaEMmJAKAzGJRMAgKQHIhwmJl3JaNEYgBh'.
			'sq+ASdp1I0HCqaIWLKbiCHcSQADwQkoIA6Y'.
			'TQTAMChybREvQdFYJm5a68RguonFSZraw1B'.
			'Tl2WhrwgiXQ9Me12EiADs=');
		break;

	case 'env.gif':
		GraphicHeader("954");
		echo base64_decode(
			'R0lGODlhEAAPAPcAAAAAAAAAMwAAZgAAmQA'.
			'AzAAA/wAzAAAzMwAzZgAzmQAzzAAz/wBmAA'.
			'BmMwBmZgBmmQBmzABm/wCZAACZMwCZZgCZm'.
			'QCZzACZ/wDMAADMMwDMZgDMmQDMzADM/wD/'.
			'AAD/MwD/ZgD/mQD/zAD//zMAADMAMzMAZjM'.
			'AmTMAzDMA/zMzADMzMzMzZjMzmTMzzDMz/z'.
			'NmADNmMzNmZjNmmTNmzDNm/zOZADOZMzOZZ'.
			'jOZmTOZzDOZ/zPMADPMMzPMZjPMmTPMzDPM'.
			'/zP/ADP/MzP/ZjP/mTP/zDP//2YAAGYAM2Y'.
			'AZmYAmWYAzGYA/2YzAGYzM2YzZmYzmWYzzG'.
			'Yz/2ZmAGZmM2ZmZmZmmWZmzGZm/2aZAGaZM'.
			'2aZZmaZmWaZzGaZ/2bMAGbMM2bMZmbMmWbM'.
			'zGbM/2b/AGb/M2b/Zmb/mWb/zGb//5kAAJk'.
			'AM5kAZpkAmZkAzJkA/5kzAJkzM5kzZpkzmZ'.
			'kzzJkz/5lmAJlmM5lmZplmmZlmzJlm/5mZA'.
			'JmZM5mZZpmZmZmZzJmZ/5nMAJnMM5nMZpnM'.
			'mZnMzJnM/5n/AJn/M5n/Zpn/mZn/zJn//8w'.
			'AAMwAM8wAZswAmcwAzMwA/8wzAMwzM8wzZs'.
			'wzmcwzzMwz/8xmAMxmM8xmZsxmmcxmzMxm/'.
			'8yZAMyZM8yZZsyZmcyZzMyZ/8zMAMzMM8zM'.
			'ZszMmczMzMzM/8z/AMz/M8z/Zsz/mcz/zMz'.
			'///8AAP8AM/8AZv8Amf8AzP8A//8zAP8zM/'.
			'8zZv8zmf8zzP8z//9mAP9mM/9mZv9mmf9mz'.
			'P9m//+ZAP+ZM/+ZZv+Zmf+ZzP+Z///MAP/M'.
			'M//MZv/Mmf/MzP/M////AP//M///Zv//mf/'.
			'/zP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
			'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
			'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
			'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
			'AAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAN'.
			'gALAAAAAAQAA8AAAiXALFhA0AQgMCDCAUCu'.
			'HZtRCAAgawE4rKHosGFDVmN0AOAlUePXC5m'.
			'HOER4zVWex4OPElyRMiCrCwO1EjzAYAKDgK'.
			'xkmhwYCCSDjoWqGCF1cSeAwsOKDCigE6eCQ'.
			'Nd47K0KStAEK0gfAlAANMCEFkh1flxj1eng'.
			'rAetAJgDwCsgQQ8BHSl50OxeDvqtcuwr9+T'.
			'SAsKHnwwIAA7');
		break;

	case 'logo.gif':
		header('Content-type: image/gif');
		header('Content-length: 2850');
		echo base64_decode(
			'R0lGODlhggBXAPcAAAAAAAAAMwAAZgAAmQAAzAAA/wAzAAAzMwAzZgAzmQAzzAAz/wBmAA'.
			'BmMwBmZgBmmQBmzABm/wCZAACZMwCZZgCZmQCZzACZ/wDMAADMMwDMZgDMmQDMzADM/wD/'.
			'AAD/MwD/ZgD/mQD/zAD//zMAADMAMzMAZjMAmTMAzDMA/zMzADMzMzMzZjMzmTMzzDMz/z'.
			'NmADNmMzNmZjNmmTNmzDNm/zOZADOZMzOZZjOZmTOZzDOZ/zPMADPMMzPMZjPMmTPMzDPM'.
			'/zP/ADP/MzP/ZjP/mTP/zDP//2YAAGYAM2YAZmYAmWYAzGYA/2YzAGYzM2YzZmYzmWYzzG'.
			'Yz/2ZmAGZmM2ZmZmZmmWZmzGZm/2aZAGaZM2aZZmaZmWaZzGaZ/2bMAGbMM2bMZmbMmWbM'.
			'zGbM/2b/AGb/M2b/Zmb/mWb/zGb//5kAAJkAM5kAZpkAmZkAzJkA/5kzAJkzM5kzZpkzmZ'.
			'kzzJkz/5lmAJlmM5lmZplmmZlmzJlm/5mZAJmZM5mZZpmZmZmZzJmZ/5nMAJnMM5nMZpnM'.
			'mZnMzJnM/5n/AJn/M5n/Zpn/mZn/zJn//8wAAMwAM8wAZswAmcwAzMwA/8wzAMwzM8wzZs'.
			'wzmcwzzMwz/8xmAMxmM8xmZsxmmcxmzMxm/8yZAMyZM8yZZsyZmcyZzMyZ/8zMAMzMM8zM'.
			'ZszMmczMzMzM/8z/AMz/M8z/Zsz/mcz/zMz///8AAP8AM/8AZv8Amf8AzP8A//8zAP8zM/'.
			'8zZv8zmf8zzP8z//9mAP9mM/9mZv9mmf9mzP9m//+ZAP+ZM/+ZZv+Zmf+ZzP+Z///MAP/M'.
			'M//MZv/Mmf/MzP/M////AP//M///Zv//mf//zP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
			'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'.
			'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAK'.
			'0ALAAAAACCAFcAQAj+AFsJHEiwoMGDCBMqXMiwocOHECNKnEixosWLCmNwOcTqWjRZrAId'.
			'4hLjRgwGKFOqXMmypcuXKE/G0MglkE2bXEhijBhDDKuQJGEKHUq0qFGjMawEyhmDYgyRQY'.
			'8esgKgqtWrWKtaOXQy5Y1AK65udVkT69KVU7OqFctq6EwrVpomjLEn6lEGS7GOVNkT7NUV'.
			'OWOyCru2sFUrXFaK2WM2ccqerLIGEoN0xQq5BmdCmWm07NWlEo7GOES46gquLb+WBnC6K0'.
			'rPhw/dJRpjhQHWDg3Ytvwyp++cN2bf+O07eGriXEzyRZ58dsuZlqsa2ElQt1XLl51rRzp8'.
			'xc+4yUv+Jj1kwBQrpWBXLN1zjQtg77LAvjWJmfpEA/gNSwdwO6yBEv3xt59+ls301kzhXT'.
			'bTDQMZaCB9BsalUUn2VWjhhRhmqOGGHF5I008g/gQSiKYcItJNJtqU4okhsVJiSIeUiJNO'.
			'HWI0Wkc/cWHFdjz2+FJSNd1U305PheSaWyYlKZ6SDBz5nJJJvgSleCwtaSWTRb0VSFwOPR'.
			'WIk0WlddVeKtV023WBGDfaak+ByQBsWgWCFlVXmWJcSnBWNZVbSV2GUFIS3pWXfn9t5Zhg'.
			'q5022VKT1WTiaogdmROhhbLCRWhHRbdCRtgRlacVlIl2CBR/pZmaX9eh5hWqcfq4Um3+0l'.
			'V0ZoGu1kqUSTlZhtiWu7mH3WXDWfHrClZAoRtcvNo2pIX5/YUdZ27a+ioXBgRyDVhc7AHX'.
			'f8IGIstmgMlnxbUalYCgTbX9NFON7Lbr7rvwxivvvPTWa++9TomXHHPE3WCFkvjWOFwgrM'.
			'jyUTQ/pUiSgU322JWBOgapooxpLhvwVwVfw8pIcUnr8XM53bTUuuxiDNLCH6e8HYJK0Ugd'.
			'xiOrLPPHM9mE2EQamWKqUTeMWtsK4F6mK7IiubkU0LqSydJ5v1rRlko9Cys00D8TS7QY0f'.
			'JlRV0WGxRk1lKacqZWIT6alVKYvhnIVSXs/Cppf7Fy5A2RxQ2i2VidRhv+XF23AuSOs8XA'.
			'Sgl/ybZSnuoZlydU+zYupmluM0A3VoKzdIPYhYPNF9B/8h24LHmLHIi2WXl3aJ6Uonmn5K'.
			'yyJvHodBZ+KG27HfRWx0gNljd2cCmV8Op4rv3Z7I+JETtrZ626Gmu8I/u7drXd1vfPmj8G'.
			't+p3fWo4S3CWENiqxyfu8c/8TceQZQaAPRpVhOsWuadgBagUWcIT9n1MYgIofq3k4yaRbc'.
			'yr3sx6JIElKamASQpOVxLoIKkRC2AXaRZvBDhAoQyHWtZKz2V6JyyNnUcpScERsU4TDS5A'.
			'oXkrYFCFmsWfEtCKgjIbTrWulcKSNClYK7gWAKxgCqdZ5if+gNGRrjqiMSukr10xwM/YrP'.
			'If3TxLQTJp2MNiQsUoOmhqtTEiwa5lE6alazA3CGNOyiMLuBALJCIzEoUCZjuB7EaJSrQN'.
			'ft64Kb8xpCkFbIUECLLHOwqkKXLpGxsHSchCGvKQiEykIhfJyEY68pGQjKQkIVkSR4mEIx'.
			'z5CcF+Yh5OcqREFFPYwibZpeFwxGAgGdHGZvSvpDjolbD8F2JeR7AYnWhkkjRlR1S5lIXB'.
			'0EcyiZjIdIbLQn7lENfwIMc4U8HAIYiWLpMXxq6BRtw1k2Y0uUk0OaSREKHsmuA8F04EaZ'.
			'FuapKZ4Eznq3a1JXJC5EamCI865+kSLXHtIjX+AQo99ykULcFlIhjTEc+gQlAT1aQmYgBO'.
			'bwxqIhMBD08mEgNUDrG60RD0ogcdiTxh8hYdWQEiQSLeUFC3FvdRdCVfiUGpHio5ViyxNW'.
			'USHqF0s4eTchRQJFuIkO6yprwBAEBrIdbs4GSA+bXkcVV5n+7+whrDiIRPfFvIQZ2zuOaY'.
			'RCSkWin+EmVTlC7VNNt7jUyTKpvgHPN4yGPpq2IAhY8mpCbWPIrc4uYk1WhVcpiLjUuQCo'.
			'DkpQSp3qlr3bDnlhG+dUvOmdxn5DQnrIwFJUj1Hk4mazOuSioQS9xDqFRik7w9jTYntJhG'.
			'4OKcr7LGUmE0ZetYo6qvpG4tfoX+bPhQK0a8XUdulbGN7RADuKMo9rU7jC1f4dJW4vZuef'.
			'sT6/J+ahj1MDa35jNI72bD18IQKyTA65ljURODAkpObZAiXnWD6rT30QZWc9nML7+yRKcl'.
			'7BAGVats2aa0lWzpL81RHlveq1H5ZokwChkhDAFrXgter1UtMZNYnvsaI6JJpMAE8EIKJE'.
			'C7HuZLdyEwhBlA4NVZGKy/LCxhohtgWgllS2Nr24al1DqYWq51aIsp2+7nquiVDyIrcCFM'.
			'vrIU3hYte1Ph7VSPajPE1FS8W/Loj2scHf5MxDYuDDE/h6JA7/LlOXz5WRPduRDCdWrK2k'.
			'kSU4S2IAhFcTiv5CD+mXNKEf+wJolSnmcl3eNeFxnsS1wAQDLl1hP3WOsaI1RPiDoCFozI'.
			'0TS8AbOUNEIsGtIkenGxgiz2kORidXEwYEGYUJmSPi7nZmxORKei0Twu0gRFzO75SVEHw0'.
			'P1XAMu1LJCNDSWLJNkKIlXcWJ24uyq7mQQMP7y19YabQUXdsSIOfzSv3yTRFcPRnIdUiJW'.
			'/vPEKNKsSYyGQhHjYhljGZEV0Sjqz/wcDb5ZzZUrYI98PG2hQ2elP00cFpnnXZuqYUc3TU'.
			'R0t9Gzh3pn0YgPUqkZbzC00XkUimxsCr4FBNzCwJsg+hLPuuhjO1t3tyStTBIp68XujXv8'.
			'4yAPucgHR05yRgYEAAA7');
		break;
}

?>