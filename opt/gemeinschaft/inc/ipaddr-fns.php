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


function ipv4_mask_length_to_dotted( $cidr_netmask_length )
{
	# a static map is much faster than calculating it
	static $map = array(
		 0 => '0.0.0.0',
		 1 => '128.0.0.0',
		 2 => '192.0.0.0',
		 3 => '224.0.0.0',
		 4 => '240.0.0.0',
		 5 => '248.0.0.0',
		 6 => '252.0.0.0',
		 7 => '254.0.0.0',
		 8 => '255.0.0.0',
		 9 => '255.128.0.0',
		10 => '255.192.0.0',
		11 => '255.224.0.0',
		12 => '255.240.0.0',
		13 => '255.248.0.0',
		14 => '255.252.0.0',
		15 => '255.254.0.0',
		16 => '255.255.0.0',
		17 => '255.255.128.0',
		18 => '255.255.192.0',
		19 => '255.255.224.0',
		20 => '255.255.240.0',
		21 => '255.255.248.0',
		22 => '255.255.252.0',
		23 => '255.255.254.0',
		24 => '255.255.255.0',
		25 => '255.255.255.128',
		26 => '255.255.255.192',
		27 => '255.255.255.224',
		28 => '255.255.255.240',
		29 => '255.255.255.248',
		30 => '255.255.255.252',
		31 => '255.255.255.254',
		32 => '255.255.255.255'
	);
	return @$map[$cidr_netmask_length];
}

function ipv4_mask_dotted_to_length( $dotted_netmask )
{
	# a static map is much faster than calculating it
	static $map = array(
		'0.0.0.0' =>  0,
		'128.0.0.0' =>  1,
		'192.0.0.0' =>  2,
		'224.0.0.0' =>  3,
		'240.0.0.0' =>  4,
		'248.0.0.0' =>  5,
		'252.0.0.0' =>  6,
		'254.0.0.0' =>  7,
		'255.0.0.0' =>  8,
		'255.128.0.0' =>  9,
		'255.192.0.0' => 10,
		'255.224.0.0' => 11,
		'255.240.0.0' => 12,
		'255.248.0.0' => 13,
		'255.252.0.0' => 14,
		'255.254.0.0' => 15,
		'255.255.0.0' => 16,
		'255.255.128.0' => 17,
		'255.255.192.0' => 18,
		'255.255.224.0' => 19,
		'255.255.240.0' => 20,
		'255.255.248.0' => 21,
		'255.255.252.0' => 22,
		'255.255.254.0' => 23,
		'255.255.255.0' => 24,
		'255.255.255.128' => 25,
		'255.255.255.192' => 26,
		'255.255.255.224' => 27,
		'255.255.255.240' => 28,
		'255.255.255.248' => 29,
		'255.255.255.252' => 30,
		'255.255.255.254' => 31,
		'255.255.255.255' => 32
	);
	return @$map[$dotted_netmask];
}

function ipv4_net_by_addr_and_mask( $dotted_ipaddr, $dotted_netmask )
{
	return long2ip( ip2long($dotted_ipaddr) & ip2long($dotted_netmask) );
}

function ipv4_bcast_by_addr_and_mask( $dotted_ipaddr, $dotted_netmask )
{
	return long2ip( ip2long($dotted_ipaddr) | ~ip2long($dotted_netmask) );
}


?>