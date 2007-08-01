<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1119 $
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

include_once( GS_DIR .'inc/util.php' );


function ip_addr_dec( $ip )
{
	$ip = trim(normalizeIPs($ip));
	if (! preg_match('/(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})/', $ip, $m))
		return 0;
	$ipDec = 0;
	for ($i=4; $i>0; --$i)
			$ipDec += ($m[$i] < 256 ? $m[$i] : 255) * pow(256,4-$i);
	return $ipDec;
	//$ipBin = str_pad(decBin($ipDec), 32, '0', STR_PAD_LEFT);
	//return $ipBin;
}

function ip_addr_decbin( $ipDec )
{
	return str_pad(decBin($ipDec), 32, '0', STR_PAD_LEFT);
}

function ip_addr_in_network( $ip, $networkDef )
{
	$ipDec = ip_addr_dec( $ip );
	if ($ipDec < 1) return false;
	$ipBin = ip_addr_decbin( $ipDec );
	//echo "ip     : $ipBin\n";
	
	$nDef = explode('/', $networkDef, 2);
	if (count($nDef) < 2) $nDef[1] = '32'; // 255.255.255.255
	$nIpDec = ip_addr_dec( $nDef[0] );
	//if ($nIpDec < 1) return false;
	$nIpBin = ip_addr_decbin( $nIpDec );
	//echo "netip  : $nIpBin\n";
	
	$nMaskDec = ip_addr_dec( $nDef[1] );
	$nMaskBin = ($nMaskDec > 1)
		? ip_addr_decbin( $nMaskDec )
		: str_repeat('1', (int)$nDef[1]) . str_repeat('0', 32-(int)$nDef[1]);
	//echo "netmask: $nMaskBin\n";
	
	for ($i=0; $i<32; ++$i) {
		if ($nMaskBin{$i}=='1' && ($ipBin{$i} != $nIpBin{$i}))
			return false;
	}
	return true;
}


?>