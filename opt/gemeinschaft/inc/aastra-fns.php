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

function aastra_transmit_str( $xml )
{
	if ($xml == '') return true;
	
	if (! preg_match('/^<'.'\?xml/', $xml)) {
		$xmlpi = '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
	} else {
		$xmlpi = '';
	}
	
	@header( 'Content-Type: text/xml; charset=utf-8' );
	@header( 'Content-Length: '. (strLen($xmlpi) + strLen($xml)) );
	//@header( 'Connection: Close' );
	echo $xmlpi, $xml;
	return true;
}

function aastra_push_str( $phone_ip, $xml )
{
	$prov_host = gs_get_conf('GS_PROV_HOST');
	
	//FIXME - call wget or something. this function should not block
	// for so long!
	// see _gs_prov_phone_checkcfg_by_ip_do_aastra() in
	// opt/gemeinschaft/inc/gs-fns/gs_prov_phone_checkcfg.php
	
	//$xml = utf8_decode($xml);
	if (subStr($xml,0,5) !== '<'.'?xml') {
		$xmlpi = '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
	} else {
		$xmlpi = '';
	}
	
	$data = "POST / HTTP/1.1\r\n";
	$data.= "Host: $phone_ip\r\n";
	$data.= "Referer: $prov_host\r\n";
	$data.= "Connection: Close\r\n";
	$data.= "Content-Type: text/xml; charset=utf-8\r\n";
	$data.= "Content-Length: ". (strLen('xml=') + strLen($xmlpi) + strLen($xml)) ."\r\n";
	$data.= "\r\n";
	$data.= 'xml='. $xmlpi . $xml;
	
	$socket = @fSockOpen( $phone_ip, 80, $error_no, $error_str, 4 );
	if (! $socket) {
		gs_log(GS_LOG_NOTICE, "Aastra: Failed to open socket - IP: $phone_ip");
		return 0;
	}
	stream_set_timeout($socket, 4);
	$bytes_written = (int)@fWrite($socket, $data, strLen($data));
	@fFlush($socket);
	$response = @fGetS($socket);
	@fClose($socket);
	if (strPos($response, '200') === false) {
		gs_log(GS_LOG_WARNING, "Aastra: Failed to push XML to phone $phone_ip");
		return 0;
	}
	gs_log(GS_LOG_DEBUG, "Aastra: Pushed $bytes_written bytes to phone $phone_ip");
	return $bytes_written;
}

/*
function aastra_reboot( $phone_ip )
{
	$xml = '<AastraIPPhoneExecute>' ."\n";
	$xml.= '	<ExecuteItem URI="Command: Reset" />' ."\n";
	$xml.= '</AastraIPPhoneExecute>' ."\n";
	
	return aastra_push_str( $phone_ip, $xml );
	
	// see _gs_prov_phone_checkcfg_by_ip_do_aastra() in
	// opt/gemeinschaft/inc/gs-fns/gs_prov_phone_checkcfg.php
}
*/

function aastra_textscreen( $title, $text, $timeout=0, $beep=false )
{
	$xml = '<AastraIPPhoneTextScreen';
	if ((int)$timeout > 0)
		$xml .= ' Timeout="' . (int)$timeout . '"';
	if ($beep == true)
		$xml .= ' Beep="yes"';
	$xml.= '>' ."\n";
	$xml.= '	<Title>'. $title .'</Title>' ."\n";
	$xml.= '	<Text>'. $text .'</Text>' ."\n";
	$xml.= '</AastraIPPhoneTextScreen>' ."\n";
	
	aastra_transmit_str( $xml );
}

function aastra_push_statusline( $phone_ip, $text, $index=0, $type='', $timeout=3, $beep=false )
{
	$xml = '<AastraIPPhoneStatus Beep="'. ($beep?'yes':'no') .'">' ."\n";
	$xml.= '	<Session>gemeinschaft</Session>' ."\n";
	$xml.= '	<Message index="'.$index .'"'. ($type != '' ? ' type="'.$type.'"' : '') .' timeout="'.$timeout .'">'. $text .'</Message>' ."\n";
	$xml.= '</AastraIPPhoneStatus>' ."\n";
	
	return aastra_push_str( $phone_ip, $xml );
}

?>