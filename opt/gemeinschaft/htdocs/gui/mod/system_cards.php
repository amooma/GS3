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


//echo '<h3>lspci</h3>' ,"\n";
$err=0; $out=array();
exec( 'lspci -m -n', $out, $err );
/*
$out = array(
	'09:00.0 "Class 0200" "14e4" "164c" -r12 "1028" "01b2"',
	'0c:00.0 "Class 0604" "11ab" "1111" "" ""',
	'0d:08.0 "Class 0780" "d161" "0220" -r02 "0004" "0000"',
	'0f:00.0 "Class 0604" "8086" "0329" -r09 "" ""',
	'00:00.0 "Class 0000" "e159" "0001" "b1d9" "0003"',
	'00:00.0 "Class 0204" "e159" "0001" "0000" "0000"',
);
*/

if ($err===0) {
	echo '<table cellspacing="1">' ,"\n";
	echo '<thead>' ,"\n";
	echo '<th>Vendor</th>' ,"\n";
	echo '<th>Device</th>' ,"\n";
	echo '<th>Rev.</th>' ,"\n";
	echo '</thead>' ,"\n";
	echo '<tbody>' ,"\n";
	
	$i=0;
	foreach ($out as $line) {
		preg_match_all('/"([^"]*)"/', $line, $m, PREG_PATTERN_ORDER);
		if (count($m[1]) < 5) continue;
		
		$class = strToLower($m[1][0]);
		$m2 = array();
		if (preg_match('/\b([0-9a-z]{4})\b/S', $class, $m2))
			$class = $m2[1];
		
		$vendorid = strToLower($m[1][1]);
		$m2 = array();
		if (preg_match('/\b([0-9a-z]{4})\b/S', $vendorid, $m2))
			$vendorid = $m2[1];
		
		$devid = strToLower($m[1][2]);
		$m2 = array();
		if (preg_match('/\b([0-9a-z]{4})\b/S', $devid, $m2))
			$devid = $m2[1];
		
		$devid2 = strToLower($m[1][3]);
		$m2 = array();
		if (preg_match('/\b([0-9a-z]{4})\b/S', $devid2, $m2))
			$devid2 = $m2[1];
		
		$devid3 = strToLower($m[1][4]);
		$m2 = array();
		if (preg_match('/\b([0-9a-z]{4})\b/S', $devid3, $m2))
			$devid3 = $m2[1];
		
		$revision = '';
		$m2 = array();
		if (preg_match('/\s+-r([0-9a-z]{2})\b/S', $line, $m2))
			$revision = $m2[1];
		
		
		$vendor   = '?';
		$descr    = '?';
		$is_known = false;
		
		if     ($vendorid === 'd161') {  # Digium
			$is_known = true;
			$vendor = 'Digium';
			switch ($devid) {
				case '0120': $descr = 'TE120p (1-port PRI, 3rd gen., PCIe 3.3v)'; break;
				case '0205': $descr = 'TE205p/TE207p (2-port PRI, 3rd gen.)'; break;
				case '0210': $descr = 'TE210p/TE212p (2-port PRI, 3rd gen.)'; break;
				case '0220': $descr = 'TE220/TE220b (2-port PRI, 3rd gen., PCIe 3.3v)'; break;
				case '0405': $descr = 'TE405p/TE407p (4-port PRI, 3rd gen., 5.0v)'; break;
				case '0406': $descr = 'TE406p (4-port PRI, 3rd gen., 5.0v)'; break;
				case '0410': $descr = 'TE410p/TE412p (4-port PRI, 3rd gen., 3.3v)'; break;
				case '0411': $descr = 'TE411p (4-port PRI, 3rd gen., 3.3v)'; break;
				case '0420': $descr = 'TE420/TE420b (4-port PRI, 3rd gen., PCIe 3.3v)'; break;
				case '2400': $descr = 'TDM2400 (analog)'; break;
				case 'b410': $descr = 'B410p (4-port BRI)'; break;
			}
		}
		elseif ($vendorid === 'e159') {  # Tiger Jet Network
			$is_known = true;
			$vendor = 'Tiger Jet Network';
			switch ($devid) {
				case '0001':
					if     ($devid2 === '0059' && $devid3 === '0001')
						$descr = '3XX (128k ISDN-S/T)';
					elseif ($devid2 === '0059' && $devid3 === '0003')
						$descr = '3XX (128k ISDN-U)';
					elseif ($devid2 === '00a7' && $devid3 === '0001')
						$descr = 'Teles S0 ISDN';
					elseif ($devid2 === '8086' && $devid3 === '0003')
						$descr = 'Digium X100p/X101p (analog FXO)';
					elseif ($devid2 === 'b1d9' && $devid3 === '0003')
						$descr = 'Digium TDM400p/A400p (4x analog FXO/FXS)';
					else
						$descr = '3XX (modem/ISDN)';
					break;
				case '0002': $descr = '100APC (ISDN)'; break;
			}
		}
		elseif ($vendorid === 'e159') {  # Xilinx
			$is_known = true;
			$vendor = 'Xilinx';
			switch ($devid) {
				case '0205': $descr = 'Digium TE205p (2-port PRI, 1st/2nd gen.)'; break;
				case '0210': $descr = 'Digium TE210p (2-port PRI, 1st/2nd gen.)'; break;
				case '0314': $descr = 'Digium TE405p/TE410p (4-port PRI, 1st gen.)'; break;
				case '0405': $descr = 'Digium TE405p (4-port PRI, 2nd gen.)'; break;
				case '0410': $descr = 'Digium TE410p (4-port PRI, 2nd gen.)'; break;
			}
		}
		elseif ($vendorid === '1057') {  # Motorola
			$is_known = true;
			$vendor = 'Motorola';
			switch ($devid) {
				case '5608': $descr = 'Digium X100p (analog FXO)'; break;
			}
		}
		else {
			if     ($class === '0204') {
				$is_known = true;
				$descr = 'unknown ISDN controller';
			}
			elseif ($class === '0780') {
				$is_known = true;
				$descr = 'unknown communication controller';
			}
		}
		
		if ($is_known) {
			echo '<tr class="', ($i%2 ? 'even':'odd') ,'">' ,"\n";
			echo '<td>', htmlEnt($vendor) ,'</td>' ,"\n";
			echo '<td>', htmlEnt($descr) ,'</td>' ,"\n";
			echo '<td class="r">', ($revision != '' ? $revision : '&nbsp;') ,'</td>' ,"\n";
			echo '</tr>' ,"\n";
			++$i;
		}
	}
	
	if ($i == 0) {
		echo '<tr>' ,"\n";
		echo '<td colspan="3">', '--' ,'</td>' ,"\n";
		echo '</tr>' ,"\n";
	}
	
	echo '</tbody>' ,"\n";
	echo '</table>' ,"\n";
	
} else
	echo "<p><code>lspci</code> not found.</p>\n";





?>
