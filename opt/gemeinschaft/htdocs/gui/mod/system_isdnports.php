<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
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

defined('GS_VALID') or die('No direct access.');

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

echo "<br />\n";


echo '<h3>BRI (mISDN)</h3>' ,"\n";
$err=0; $out=array();
@exec( 'asterisk -rx \'misdn show stacks\' | grep -E \'Port [0-9]+\' 2>>/dev/null', $out, $err );
/*
  * Port 1 Type TE Prot. PMP L2Link DOWN L1Link:DOWN Blocked:0  Debug:0
  * Port 2 Type TE Prot. PMP L2Link DOWN L1Link:DOWN Blocked:0  Debug:0
  * Port 3 Type TE Prot. PMP L2Link DOWN L1Link:DOWN Blocked:0  Debug:0
  * Port 4 Type TE Prot. PMP L2Link DOWN L1Link:DOWN Blocked:0  Debug:0
*/
/*
$out = array(
'  * Port 1 Type TE Prot. PMP L2Link DOWN L1Link:DOWN Blocked:0  Debug:0',
'  * Port 2 Type TE Prot. PMP L2Link DOWN L1Link:DOWN Blocked:0  Debug:0',
'  * Port 3 Type TE Prot. PMP L2Link DOWN L1Link:DOWN Blocked:0  Debug:0',
'  * Port 4 Type TE Prot. PMP L2Link DOWN L1Link:DOWN Blocked:0  Debug:0'
);
$err=0;
*/

if ($err===0) {
	echo '<table cellspacing="1">' ,"\n";
	echo '<thead>' ,"\n";
	echo '<th>Port</th>' ,"\n";
	echo '<th>TE/NT</th>' ,"\n";
	echo '<th>Protokoll</th>' ,"\n";
	echo '<th>L1-Link</th>' ,"\n";
	echo '<th>L2-Link</th>' ,"\n";
	echo '<th>Gesperrt?</th>' ,"\n";
	echo '</thead>' ,"\n";
	echo '<tbody>' ,"\n";
	
	$i=0;
	foreach ($out as $line) {
		if (! preg_match('/ Port[\s:]*([0-9]+)/i', $line, $m)) continue;
		$port = $m[1];
		echo '<tr class="', ($i%2 ? 'even':'odd') ,'">' ,"\n";
		echo '<td class="r">', $port ,'</td>' ,"\n";
		
		if (preg_match('/ Type[\s:]*(TE|NT)/i', $line, $m))
			$type = strToUpper($m[1]);
		else
			$type = null;
		echo '<td>';
		switch ($type) {
			case 'TE': echo 'TE'; break;
			case 'NT': echo 'NT'; break;
			default  : echo '?' ;
		}
		echo '</td>' ,"\n";
		
		if (preg_match('/ Prot\.[\s:]*(PTP|PMP)/i', $line, $m))
			$protocol = strToUpper($m[1]);
		else
			$protocol = null;
		echo '<td>';
		switch ($protocol) {
			case 'PTP': echo 'PTP' ; break;
			case 'PMP': echo 'PTMP'; break;
			default   : echo '?'   ;
		}
		echo '</td>' ,"\n";
		
		if (preg_match('/ L1Link[\s:]*(DOWN|UP)/i', $line, $m))
			$l1link = strToUpper($m[1]);
		else
			$l1link = null;
		echo '<td>';
		switch ($l1link) {
			case 'DOWN': echo 'Down'; break;
			case 'UP'  : echo 'Up'  ; break;
			default    : echo '?'   ;
		}
		echo '</td>' ,"\n";
		
		if (preg_match('/ L2Link[\s:]*(DOWN|UP)/i', $line, $m))
			$l2link = strToUpper($m[1]);
		else
			$l2link = null;
		echo '<td>';
		switch ($l2link) {
			case 'DOWN': echo 'Down'; break;
			case 'UP'  : echo 'Up'  ; break;
			default    : echo '?'   ;
		}
		echo '</td>' ,"\n";
		
		if (preg_match('/ Blocked[\s:]*(0|1)/i', $line, $m))
			$blocked = strToUpper($m[1]);
		else
			$blocked = null;
		echo '<td>';
		switch ($blocked) {
			case '0': echo 'nein'; break;
			case '1': echo 'ja'  ; break;
			default : echo '?'   ;
		}
		echo '</td>' ,"\n";
		
		echo '</tr>' ,"\n";
		++$i;
	}
	
	if ($i === 0) {
		echo '<tr>' ,"\n";
		echo '<td colspan="6">', '--' ,'</td>' ,"\n";
		echo '</tr>' ,"\n";
	}
	
	echo '</tbody>' ,"\n";
	echo '</table>' ,"\n";
	
} else {
	echo "<p>No info available.</p>\n";
}


?>
