<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 5712 $
* 
* Copyright 2009, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Philipp Kempgen <philipp.kempgen@amooma.de>
* 
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
* MA 02110-1301, USA.
\*******************************************************************/

header( 'Content-Type: text/x-component' );
//header( 'Content-Type: text/plain' );

header( 'Cache-Control: public, max-age=300' );
header( 'Expires: '. gmDate('D, d M Y H:i:s', time()+300) .' GMT');

define( 'GS_VALID', true );  /// this is a parent file


header( 'ETag: '. gmDate('Ymd').'-'.md5($_SERVER['SCRIPT_NAME']) );


function _not_modified()
{
	header( 'HTTP/1.0 304 Not Modified', true, 304 );
	header( 'Status: 304 Not Modified', true, 304 );
	exit();
}

if (array_key_exists('HTTP_IF_NONE_MATCH', $_SERVER)
&&  $_SERVER['HTTP_IF_NONE_MATCH'] === gmDate('Ymd').'-'.md5($_SERVER['SCRIPT_NAME']) ) {
	_not_modified();
}
/*
$tmp = gmDate('D, d M Y');
if (array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER)
&&  subStr($_SERVER['HTTP_IF_MODIFIED_SINCE'],0,strLen($tmp)) == $tmp ) {
	_not_modified();
}
*/


ob_start();

?><public:component lightWeight="true">
<public:attach event="onclick" onevent="button_click()" />
<script type="text/javascript" language="JavaScript">

/*
Gemeinschaft
(c) Amooma GmbH, Philipp Kempgen
GNU GPL
$Revision: 3960 $
*/

function button_click()
{
	try {
		var btn = (this || element || null);
		if (! btn) return;
		if (! btn.attributes) return;

		var val_attr = null;
		if (btn.attributes['value']) val_attr = btn.attributes['value'];
		if (! val_attr) {
			if (btn.attributes.getNamedItem) val_attr = btn.attributes.getNamedItem('value');
			if (! val_attr) return;
		}
		var orig_val = (val_attr.value || val_attr.nodeValue || '');
		
		if (btn.offsetWidth && btn.offsetHeight) {
			btn.style.width  = parseInt(btn.offsetWidth ) +'px';
			btn.style.height = parseInt(btn.offsetHeight) +'px';
			if ((typeof btn.style.overflow)=='string')
				btn.style.overflow = 'hidden';
			if ((typeof btn.style.filter)=='string')
				btn.style.filter = 'Alpha(opacity=20)';
		}
		btn.value = orig_val;
	}
	catch(e){}
}

</script>
</public:component>

<?php

if (! headers_sent())
	header( 'Content-Length: '. (int)@ob_get_length() );
@ob_end_flush();
exit();

?>