<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1873 $
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


function aastra_transmit() {
	global $xml_buffer;

	header( 'Content-Type: text/xml' );
	header( 'Content-Length: '.strlen($xml_buffer) );
	header( 'Connection: Close ' );

	echo $xml_buffer;
}

function aastra_push($phone_ip) {
	global $xml_buffer;
	
	$prov_host = gs_get_conf('GS_PROV_HOST', '127.0.0.1');
	$xml_buffer = 'xml='.$xml_buffer;

	$header = "POST / HTTP/1.1\r\n";
	$header.= "Host: $phone_ip\r\n";	
	$header.= "Referer: $prov_host\r\n";
	$header.= "Connection: Keep-Alive\r\n";
	$header.= "Content-Type: text/xml\r\n";
	$header.= "Content-Length: ".strlen($xml_buffer)."\r\n\r\n";
	
	$socket = @fsockopen ( $phone_ip, 80, $error_no, $error_str, 4);
	if($socket) {
		fputs($socket, $header.$xml_buffer);
		flush();
		$response = fgets($socket);
		fclose($socket);
	
	} else return 0;
	if (strpos($response,"200 OK") === false) return 0;
		else return 1; 
}


function aawrite($text) {
	global $xml_buffer;
	$xml_buffer .= $text."\n";
}



?>