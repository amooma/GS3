#!/usr/bin/php -q
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

define( 'GS_VALID', true );  /// this is a parent file

ini_set('implicit_flush', 1);
ob_implicit_flush(1);

error_reporting(0);

require_once( dirName(__FILE__) .'/../../inc/conf.php' );
require_once( GS_DIR .'inc/util.php' );
set_error_handler('err_handler_quiet');

include_once( GS_DIR .'inc/gs-lib.php' );
include_once( GS_DIR .'inc/get-listen-to-ips.php' );
include_once( GS_DIR .'inc/netmask.php' );


$our_ips = @ gs_get_listen_to_ips();
if (! is_array($our_ips)) $our_ips = array();
//echo 'Our IPs: ', implode(', ', $our_ips), "\n";

# If at least one of our IP addresses (Gemeinschaft node) is
# a public IP address then assume NAT for the phones.

$nat = 'no';  # use NAT mode only according to RFC 3581 (";rport")
foreach ($our_ips as $ip_addr) {
	//echo '; ', $ip_addr;
	if (ip_addr_in_network($ip_addr, '0.0.0.0/8')
	||  ip_addr_in_network($ip_addr, '10.0.0.0/8')
	||  ip_addr_in_network($ip_addr, '127.0.0.0/8')
	||  ip_addr_in_network($ip_addr, '169.254.0.0/16')
	||  ip_addr_in_network($ip_addr, '172.16.0.0/12')
	||  ip_addr_in_network($ip_addr, '192.168.0.0/16')
	) {
		//echo ' - private' ,"\n";
	} else {
		//echo ' - public' ,"\n";
		$nat = 'yes';  # assume NAT (ignore ";rport")
		break;
	}
}

echo 'nat = ',$nat ,"\n";
echo "\n";


?>