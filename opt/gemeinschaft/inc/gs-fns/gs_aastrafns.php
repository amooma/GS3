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

define( 'GS_AASTRA_PUSH_MAXLEN', 10000 );

function aastra_transmit() {
	global $aastra_xml_buffer;

	header( 'Content-Type: text/xml' );
	header( 'Content-Length: '.strlen($aastra_xml_buffer) );
	header( 'Connection: Close ' );

	echo $aastra_xml_buffer;
	$aastra_xml_buffer='';
	return 1;
}

function aastra_push($phone_ip) {
	global $aastra_xml_buffer;
	
	$prov_host = gs_get_conf('GS_PROV_HOST');
	$aastra_xml_buffer = 'xml='.$aastra_xml_buffer;

	$header = "POST / HTTP/1.1\r\n";
	$header.= "Host: $phone_ip\r\n";	
	$header.= "Referer: $prov_host\r\n";
	//$header.= "Connection: Keep-Alive\r\n";
	$header.= "Connection: Close\r\n";
	$header.= "Content-Type: text/xml\r\n";
	$header.= "Content-Length: ".strlen($aastra_xml_buffer)."\r\n\r\n";
	
	$socket = @fsockopen ( $phone_ip, 80, $error_no, $error_str, 4);
	if($socket) {
		fputs($socket, $header.$aastra_xml_buffer);
		flush();
		$response = fgets($socket);
		fclose($socket);
	
	} else return 0;
	if (strpos($response,"200 OK") === false) {
		gs_log(GS_LOG_WARNING, "AASTRA: Failed to push $ret_val bytes to phone $phone_ip");
		return 0;
	}
	$ret_val = strlen($aastra_xml_buffer);
	gs_log(GS_LOG_DEBUG, "AASTRA: Pushed $ret_val bytes to phone $phone_ip");
	$aastra_xml_buffer='';
	return $ret_val;
}


function aawrite($text) {
	global $aastra_xml_buffer;
	$aastra_xml_buffer .= $text."\n";
}



?>