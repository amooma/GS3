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

# checks if an IP address is in a list of comma-separated networks
# in CIDR notation
#
function ip_addr_in_network_list( $ip, $network_list )
{
	$networks = explode(',', $network_list);
	foreach ($networks as $net) {
		if (ip_addr_in_network( $ip, trim(normalizeIPs($net)) )) {
			return true;
		}
	}
	return false;
}

function ip_addr_network_add_sub( $network, $sub_ip )
{
	# simple OR for 2 binary strings, eg.
	#    11000000101010000000000100000000
	#    00000000000000000000000010000010
	# => 11000000101010000000000110000010
	$binary_net = ip_addr_decbin(ip_addr_dec( $network ));
	$binary_sub = ip_addr_decbin(ip_addr_dec( $sub_ip  ));
	if (strLen($binary_net) != 32
	||  strLen($binary_sub) != 32) return false;
	$binary_ip  = '';
	for ($i=0; $i<32; ++$i) {
		$binary_ip .=
			($binary_net{$i}=='1' || $binary_sub{$i}=='1') ? '1':'0';
	}
	$ip = long2ip(binDec( $binary_ip ));
	return $ip ? $ip : false;
}


?>