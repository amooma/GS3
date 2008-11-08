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

//define( 'GS_AASTRA_PUSH_MAXLEN', 10000 );  //FIXME - not used


function aastra_transmit()
{
	global $aastra_xml_buffer;
	
	header( 'Content-Type: text/xml' );
	header( 'Content-Length: '.strLen($aastra_xml_buffer) );
	header( 'Connection: Close ' );
	
	$aastra_xml_buffer = utf8_decode($aastra_xml_buffer);
	echo $aastra_xml_buffer;
	$aastra_xml_buffer = '';
	return true;
}

function aastra_push( $phone_ip )
{
	global $aastra_xml_buffer;
	
	$prov_host = gs_get_conf('GS_PROV_HOST');
	$aastra_xml_buffer = 'xml='.$aastra_xml_buffer;
	
	//FIXME - call wget or something. this function should not block
	// for so long!
	
	$header = "POST / HTTP/1.1\r\n";
	$header.= "Host: $phone_ip\r\n";	
	$header.= "Referer: $prov_host\r\n";
	//$header.= "Connection: Keep-Alive\r\n";
	$header.= "Connection: Close\r\n";
	$header.= "Content-Type: text/xml\r\n";
	$header.= "Content-Length: ".strlen($aastra_xml_buffer)."\r\n";
	$header.= "\r\n";
	
	$socket = @fSockOpen( $phone_ip, 80, $error_no, $error_str, 4);
	if (! $socket) {
		gs_log(GS_LOG_NOTICE, "Aastra: Failed to open socket - IP: $phone_ip");
		return 0;
	}
	stream_set_timeout($socket, 4);
	@fWrite($socket, $header.$aastra_xml_buffer);
	@fFlush($socket);
	$response = @fGetS($socket);
	@fClose($socket);
	if (strPos($response, '200 OK') === false) {
		gs_log(GS_LOG_WARNING, "Aastra: Failed to push $ret_val bytes to phone $phone_ip");
		return 0;
	}
	$ret_val = strLen($aastra_xml_buffer);
	gs_log(GS_LOG_DEBUG, "Aastra: Pushed $ret_val bytes to phone $phone_ip");
	$aastra_xml_buffer = '';
	return $ret_val;
}


function aastra_write( $text )
{
	global $aastra_xml_buffer;
	$aastra_xml_buffer .= $text."\n";
}

function aastra_reboot( $phone_ip )
{
	aastra_write('<AastraIPPhoneExecute>');
	aastra_write('<ExecuteItem URI="Command: Reset" />');
	aastra_write('</AastraIPPhoneExecute>');
	$ret_val = aastra_push($phone_ip);
	return $ret_val;
}

function aastra_textscreen( $title, $text )
{
	aastra_write('<AastraIPPhoneTextScreen destroyOnExit="yes">');
	aastra_write('<Title>'.$title.'</Title>');
	aastra_write('<Text>'.$text.'</Text>');
	aastra_write('</AastraIPPhoneTextScreen>');
}

function aastra_push_statusline( $phone_ip, $text, $index=0, $type='', $timeout=3, $beep=false )
{
	aastra_write('<AastraIPPhoneStatus Beep="'. ($beep?'yes':'no') .'">');
	aastra_write('<Session>gemeinschaft</Session>');
	aastra_write('<Message index="'.$index .'"'. ($type != '' ? ' type="'.$type.'"' : '') .' timeout="'.$timeout .'">'. $text .'</Message>');
	aastra_write('</AastraIPPhoneStatus>');
	
	$ret_val = aastra_push($phone_ip);
	
	return $ret_val;
}

?>