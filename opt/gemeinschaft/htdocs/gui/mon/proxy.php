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
require_once( dirName(__FILE__) .'/../../../inc/conf.php' );


$host = '127.0.0.1';
$port = 5039;
$maxtime = 35;

#header( 'Content-Type: text/json' );
header( 'Content-Type: application/json' );  # RFC 4627
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Expires: 0' );
header( 'Vary: *' );

ini_set('implicit_flush', 1);
ob_implicit_flush(1);

@ini_set('max_execution_time', $maxtime+2);

$sock = @fSockOpen( $host, $port, $err, $errMsg, 5 );
if (! is_resource($sock)) {
	header( 'HTTP/1.0 500 Internal Server Error', true, 500 );
	header( 'Status: 500 Internal Server Error', true, 500 );
	die();
}

@stream_set_blocking( $sock, false );
$tStart = time();
$cnt_no_data = 0;
while (! @fEof( $sock ) && time() < $tStart+$maxtime) {
	$data = @fRead( $sock, 8190 );
	if (strLen($data) > 0) {
		$cnt_no_data = 0;
		echo $data;
	} else {
		if (++$cnt_no_data > 500) {
			# we sleep 0.01 secs so this is 5 secs
			die();
		}
	}
	uSleep(10000);  # sleep 0.01 secs
}


?>