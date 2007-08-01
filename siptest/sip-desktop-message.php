#!/usr/bin/php -q
<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1131 $
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

$dest_user = '2002';
$dest_ip   = '192.168.1.201';
$dest_port = 5060;



function send_sip_packet( $ip, $port, $packet, $source_ip=false )
{
	$spoof = $source_ip ? ('-s \''. $source_ip .'\'') : '';
	$p = pOpen( 'netcat -u -n -p 5060 -w 1 -q 0 '. $spoof .' '. $ip .' '. $port .' >>/dev/null', 'wb' );
	fWrite($p, $packet, strLen($packet));
	fClose($p);
}

function gen_sip_message( $dest_ip, $dest_port, $dest_user, $msg, $source_ip='127.0.0.1' )
{
	sRand();
	return 'MESSAGE sip:'. $dest_user .'@'. $dest_ip .':'. $dest_port .' SIP/2.0
Via: SIP/2.0/UDP '. $source_ip .':32805;branch=0;rport;alias
To: sip:'. $dest_user .'@'. $dest_ip .':'. $dest_port .'
Call-ID: '. (rand(1000000000,9999999999)) .'@'. $source_ip .'
CSeq: 1 MESSAGE
Content-Type: text/plain
From: sip:bot@'. $source_ip .':32805;tag=0
Content-Length: '. strLen($msg) .'
Content-Disposition: desktop

'. $msg;
}

$sip = gen_sip_message( $dest_ip, $dest_port, $dest_user, 'Anruf in Gruppe '. rand(1,99) );

send_sip_packet( $dest_ip, $dest_port, $sip );

?>