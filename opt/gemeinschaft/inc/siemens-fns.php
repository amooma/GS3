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
* Author: Henning Holtschneider <henning@loca.net> - LocaNet oHG
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

function siemens_push_str( $phone_ip, $postdata )
{
	$prov_host = gs_get_conf('GS_PROV_HOST');
	
	$data = "POST /server_push.html/ServerPush HTTP/1.1\r\n";
	$data.= "User-Agent: Gemeinschaft\r\n";
	$data.= "Host: $phone_ip:8085\r\n";
	$data.= "Accept: text/html, image/gif, image/jpeg, *; q=.2, */*; q=.2\r\n";
	$data.= "Connection: keep-alive\r\n";
	$data.= "Content-Type: application/x-www-form-urlencoded\r\n";
	$data.= "Content-Length: ". strLen($postdata) ."\r\n\r\n";
	$data.= $postdata;
	
	$socket = @fSockOpen( $phone_ip, 8085, $error_no, $error_str, 4 );
	if (! $socket) {
		gs_log(GS_LOG_NOTICE, "Siemens: Failed to open socket - IP: $phone_ip");
		return 0;
	}
	stream_set_timeout($socket, 4);
	$bytes_written = (int)@fWrite($socket, $data, strLen($data));
	@fFlush($socket);
	$response = @fGetS($socket);
	@fClose($socket);
	if (strPos($response, '200') === false) {
		gs_log(GS_LOG_WARNING, "Siemens: Failed to push to phone $phone_ip");
		return 0;
	}
	gs_log(GS_LOG_DEBUG, "Siemens: Pushed $bytes_written bytes to phone $phone_ip");
	return $bytes_written;
}

?>