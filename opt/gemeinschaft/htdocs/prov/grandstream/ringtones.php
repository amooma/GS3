<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2009, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* 
* Sebastian Ertz
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

header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );


require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
//require_once( GS_DIR .'inc/gs-lib.php' );
//include_once( GS_DIR .'inc/prov-fns.php' );
set_error_handler('err_handler_die_on_err');

function _send404( $msg='' )
{
	@ob_end_clean();
	@ob_start();
	if (! headers_sent()) {
		header( 'HTTP/1.0 404 Not Found', true, 404 );
		header( 'Status: 404 Not Found' , true, 404 );
	}
	@ob_end_flush();
	exit(1);
}


# check for GRANDSTREAM_PROV_ENABLED
if (! gs_get_conf('GS_GRANDSTREAM_PROV_ENABLED')) {
	gs_log( GS_LOG_DEBUG, "Grandstream provisioning not enabled" );
	_send404();
}

# is parameter 'ring' set and is correct
$ring = trim( @$_REQUEST['ring'] );
if (! preg_match('/[1-3]/', $ring)) {
	gs_log( GS_LOG_NOTICE, "Invalid \"ring".$ring.".bin\" (wrong number)" );
	# don't explain this to the users
	_send404();
}

# is IP addr. right
$remote_ip = trim( @$_SERVER['REMOTE_ADDR'] );  //FIXME
if (! preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $remote_ip)) {
	gs_log( GS_LOG_NOTICE, "Invalid IP address \"". $remote_ip ."\"" );
	# don't explain this to the users
	_send404();
}

# is grandstream
$ua = trim( @$_SERVER['HTTP_USER_AGENT'] );
$ua_parts = explode(' ', $ua);
if (strToLower(@$ua_parts[0]) !== 'grandstream') {
	gs_log( GS_LOG_WARNING, "Phone with IP \"$remote_ip\" (Grandstream) has invalid User-Agent (\"". $ua ."\")" );
	# don't explain this to the users
	_send404();
}

# find out the type of the phone
if (! preg_match('/(bt|gxp|gxv)[0-9]{1,6}/', strToLower(@$ua_parts[1]), $m)) {
	gs_log( GS_LOG_WARNING, "Phone with IP \"$remote_ip\" (Grandstream) has invalid phone_model (\"". $ua ."\")" );
	# don't explain this to the users
	_send404();
}



function checksum($str) {
	$sum = 0;
	for ($i=0; $i <= ((strlen($str) - 1) / 2); $i++) {
		$sum += ord(substr($str,  2 * $i      , 1)) << 8;
		$sum += ord(substr($str, (2 * $i) + 1 , 1));
		$sum &= 0xffff;
	}
	$sum = 0x10000 - $sum;
	return array(($sum >> 8) & 0xff, $sum & 0xff);
}


if ($ring === '1') {
	$source='internal';
}
elseif ($ring === '2') {
	$source='external';
}
else {
	# don't explain this to the users
	_send404();
}


# connect to db
require_once( GS_DIR .'inc/db_connect.php' );
$db = gs_db_slave_connect();
if (! $db) {
	gs_log( GS_LOG_WARNING, "Phone with IP \"$remote_ip\" (Grandstream) asks for ringtone - Could not connect to DB" );
	_send404();
}


#####################################################################
#  check for ringtones
#####################################################################

# get user_id
$user_id = (int)$db->executeGetOne( 'SELECT `id` FROM `users` WHERE `current_ip`=\''. $db->escape($remote_ip) .'\'' );
if (! $user_id) {
	gs_log( GS_LOG_WARNING, "Phone with IP \"$remote_ip\" (Grandstream) asks for ringtone - Could not get user_id" );
	_send404();
}

$ringtones = array(
	'internal' => array( 'bellcore' => 1, 'file' => null ),
	'external' => array( 'bellcore' => 1, 'file' => null )
);

# get ringers
$rs = $db->execute( 'SELECT `src`, `bellcore`, `file` FROM `ringtones` WHERE `user_id`='. $user_id );
if (! $rs) _send404();
while ($r = $rs->fetchRow()) {
	$src = $r['src'];
	if (! array_key_exists($src, $ringtones)) continue;
		$ringtones[$src]['bellcore'] = (int)$r['bellcore'];
		$ringtones[$src]['file'] = $r['file'];
	}

if (@$ringtones['internal']['file'] && @$ringtones['external']['file'])
	$ringtones['externel']['file'] = null;

# is a file
if (! @$ringtones[$source]['file']) _send404();

if ($ringtones[$source]['file'] === 'somefile') _send404();


#####################################################################
#  load AUDIO file
#####################################################################
$ringtone_file = '../ringtones/'. $ringtones[$source]['file'] .'-grandstream.ul';

if (! file_exists($ringtone_file) || ! is_readable($ringtone_file) ) {
	gs_log( GS_LOG_WARNING, "Phone with IP \"$remote_ip\" (Grandstream): ".$ringtones[$source]['file']."-grandstream.ul not exists or not readable" );
	_send404();
}

$ringtone_filehandle = fopen($ringtone_file, 'rb');
if ($ringtone_filehandle) {
	$audio = fread($ringtone_filehandle, filesize($ringtone_file));
	fclose($ringtone_filehandle);
} else {
	gs_log( GS_LOG_WARNING, "Phone with IP \"$remote_ip\" (Grandstream) can't load ". $ringtones[$source]['file'] ."-grandstream.ul" );
	_send404();
}


#####################################################################
#  create BODY
#####################################################################
$body = $audio;
if ( (strLen($body)%2) == 1 ) $body .= chr(0);
$body_length = strLen($body);


#####################################################################
#  create HEADER
#####################################################################
$header_length = 512;
$out_length = $header_length + $body_length;

$year  = date('Y');
$month = date('n');
$day   = date('j');
$hour  = date('G');
$min   = date('i');

$header = array();

// 00 01 02 03 - out_length / 2
$header[] = (($out_length / 2) >> 24) & 0xff;
$header[] = (($out_length / 2) >> 16) & 0xff;
$header[] = (($out_length / 2) >>  8) & 0xff;
$header[] = (($out_length / 2)      ) & 0xff;

// 04 05 - put checksum in later
$header[] = 0x00;
$header[] = 0x00;

// 06 07 07 09 - version (1.0.0.0)
$header[] = 0x01;
$header[] = 0x00;
$header[] = 0x00;
$header[] = 0x00;

// 0a 0b - year
$header[] = ($year >> 8) & 0xff;
$header[] =  $year       & 0xff;
// 0c - month
$header[] = $month;
// 0d - day
$header[] = $day;
// 0e - hour
$header[] = $hour;
// 0f - min
$header[] = $min;

// 10 - name, seems always be ring.bin (name field is 16 chars)
$string="ring.bin";
for($i=0; $i<strLen($string); $i++)    $header[] = ord(subStr($string,$i,1));
for($i=0; $i<16-strLen($string); $i++) $header[] = 0x00;

// fill to $header_length with 0x00
while( count($header) < $header_length ) {
	$header[] = 0x00;
}


#####################################################################
#  Assemble output
#####################################################################
$arr = $header;
array_unshift($arr, 'C'.$header_length);
$initstr = call_user_func_array('pack', $arr);
$checktext = $initstr . $body;

array_splice($header, 4, 2, checksum($checktext));

$arr = $header;
array_unshift($arr, 'C'.$header_length);
$initstr = call_user_func_array('pack', $arr);
$out = $initstr . $body;


#####################################################################
#  output
#####################################################################
ob_start();
echo $out;
if (! headers_sent() ) {
	header( 'Content-Type: application/octet-stream' );
	header( 'Content-Length: '. (int)@ob_get_length() );
}
@ob_flush();
  
?>