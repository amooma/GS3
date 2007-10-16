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


function gs_send_phone_desktop_msg( $ip, $port, $ext, $registrar, $text, $extra=array() )
{
	static $sock = null;
	static $lAddr = '0.0.0.0';
	static $lPort = 0;
	static $have_sockets = null;
	
	if ($have_sockets === null) {
		$have_sockets = function_exists('socket_create');
		// about 15 to 45 % faster
	}
	
	if (is_array($extra) && array_key_exists('fake_callid', $extra))
		$fake_callid = $extra['fake_callid'];
	else
		$fake_callid = rand(1000000000,2000000000);
	
	if ($have_sockets) {
		if (! $sock) {
			$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
			if (! $sock) return false;
			//socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
			//socket_bind($sock, '0.0.0.0', 12345);
			@socket_getSockName($sock, $lAddr, $lPort);
			//echo "Local socket is at $lAddr:$lPort\n";
			@socket_set_nonblock($sock);
		}
	} else {
			$sock = @fSockOpen('udp://'.$ip, $port, $err, $errmsg, 1);
			if (! $sock) return false;
			@stream_set_blocking($sock, 0);
			@stream_set_timeout($sock, 1);
	}
	
	$sipmsg =
	'MESSAGE sip:'. $ext .'@'. $ip .' SIP/2.0' ."\r\n".
	'Via: SIP/2.0/UDP '.$registrar.':'.$lPort ."\r\n".
	'To: sip:'.$ext.'@'.$ip .'' ."\r\n".
	'Call-ID: '.$fake_callid.'@'.$registrar ."\r\n".
	'CSeq: 1 MESSAGE' ."\r\n".
	'Content-Type: text/plain; charset=utf-8' ."\r\n".
	'Max-Forwards: 9' ."\r\n".
	'From: sip:fake@'.$registrar.':'.$lPort.';tag=fake' ."\r\n".
	'Content-Length: '.strLen($text) ."\r\n".
	'Content-Disposition: desktop' ."\r\n".
	"\r\n".
	$text;
	
	if ($have_sockets) {
		return @socket_sendto( $sock, $sipmsg, strLen($sipmsg), 0, $ip, $port );
	} else {
		$written = @fWrite($sock, $sipmsg, strlen($sipmsg));
		@fClose($sock);
		return $written;
	}
}

/*
$fake_callid = rand(1000000000,2000000000);
$start = microTime(true);
$i=1000;
while (!($i===0)) {
gs_send_phone_msg( '192.168.1.219', 5060, '555', '192.168.1.130', @date('Y-m-d H:i:s'), array('fake_callid'=>$fake_callid) );
--$i;
}
$end = microTime(true);
echo 'Took: ', ($end-$start) ,' us', "\n";
*/

?>