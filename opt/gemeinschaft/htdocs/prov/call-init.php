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

/*
URL PARAMETERS:
   user=    Benutzername
   to=      Zielrufnummer
   
   optional:
   from=    Ausgangsnummer
   cidnum=  Caller-ID-Nummer
   /callerid=
   clir=1   zur Rufnummernunterdr�ckung
   prv=1    f�r Privatgespr�ch
*/


define( 'GS_VALID', true );  /// this is a parent file
require_once( dirName(__FILE__) .'/../../inc/conf.php' );
require_once( GS_DIR .'inc/netmask.php' );
require_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'inc/get-listen-to-ids.php' );
require_once( GS_DIR .'inc/remote-exec.php' );


header( 'Content-Type: text/plain; charset=utf-8' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Expires: 0' );
header( 'Vary: *' );


function die_not_allowed( $msg ) {
	if (! headers_sent()) {
		header( 'HTTP/1.0 403 Forbidden', true, 403 );
		header( 'Status: 403 Forbidden' , true, 403 );
	}
	die( $msg );
}
function die_invalid( $msg ) {
	if (! headers_sent()) {
		header( 'HTTP/1.0 400 Bad Request', true, 400 );
		header( 'Status: 400 Bad Request' , true, 400 );
	}
	die( $msg );
}
function die_ok( $msg ) {
	if (! headers_sent()) {
		//header( 'HTTP/1.0 204 No Content', true, 204 );
		//header( 'Status: 204 No Content' , true, 204 );
		header( 'HTTP/1.0 200 OK', true, 200 );
		header( 'Status: 200 OK' , true, 200 );
	}
	die( $msg );
}
function die_error( $msg ) {
	if (! headers_sent()) {
		header( 'HTTP/1.0 500 Internal Server Error', true, 500 );
		header( 'Status: 500 Internal Server Error' , true, 500 );
	}
	die( $msg );
}

function _normalize_number( $number ) {
	$number = preg_replace('/^\+/', '00', trim($number));
	$number = preg_replace('/[^\d*#]/', '', $number);
	return $number;
}

function _pack_int( $int ) {
	$str = base64_encode(pack('N', $int ));
	return preg_replace('/[^a-z\d]/i', '', $str);
}



$remote_ip = @ $_SERVER['REMOTE_ADDR'];

$networks = explode(',', GS_CALL_INIT_FROM_NET);
$allowed = false;
foreach ($networks as $net) {
	if (ip_addr_in_network( $remote_ip, trim($net) )) {
		$allowed = true;
		break;
	}
}
if ($allowed)
	gs_log( GS_LOG_DEBUG, "IP $remote_ip is in $net => allowed to init call" );
else {
	gs_log( GS_LOG_NOTICE, "IP $remote_ip is not in GS_CALL_INIT_FROM_NET => not allowed to init call" );
	die_not_allowed( 'You are not allowed to init a call.' );
}



# user
#
if (! isSet( $_REQUEST['user'] ))
	die_invalid( 'No user code specified. Use user=' );
$user_code = trim( $_REQUEST['user'] );
if (defined('GS_LVM_USER_6_DIGIT_INT') && GS_LVM_USER_6_DIGIT_INT) {
	# hack to compare user names as if they were integers
	# padded to 6 digits
	$user_code = lTrim($user_code, '0');
	if (strLen($user_code) < 6)
		$user_code = str_pad($user_code, 6, '0', STR_PAD_LEFT);
}
if (! defined('GS_LVM_CALL_INIT_USERS_500000') || ! GS_LVM_CALL_INIT_USERS_500000 || $user_code < '500000') {
	$user = @ gs_user_get( $user_code );
	if (isGsError( $user ))
		die_invalid( $user->getMsg() );
	if ($user['nobody_index'] > 0)
		die_not_allowed( 'Nobody user. Not allowed to init a call.' );
} else {
	$user = array(
		'id'          => 0,
		'user'        => $user_code,
		'pin'         => null,
		'lastname'    => '(Agenturmitarbeiter)',
		'firstname'   => '',
		'honorific'   => '',
		'nobody_index'=> null,
		'ext'         => '',
		'callerid'    => 'Anonymous <anonymous>',
		'mailbox'     => '',
		'host_id'     => 0,
		'host'        => '0.0.0.0'
	);
}


# from number
#
if (! isSet( $_REQUEST['from'] ))
	$from_num = false;  // default
else {
	$from_num = _normalize_number( $_REQUEST['from'] );
}
if ($from_num == $user['ext'])
	$from_num = false;
if ($from_num) {
	if (! defined('GS_LVM_CALL_INIT_USERS_500000') || ! GS_LVM_CALL_INIT_USERS_500000 || $user_code < '500000') {
		$user_external_numbers = @ gs_user_external_numbers_get( $user_code );
		if (isGsError($user_external_numbers)) {
			gs_log( GS_LOG_WARNING, $user_external_numbers->getMsg() );
			$user_external_numbers = array();
		}
		if (! is_array($user_external_numbers))
			$user_external_numbers = array();
		if (! in_array($from_num, $user_external_numbers, true)) {
			gs_log( GS_LOG_WARNING, 'User '. $user_code .' does not have "'. $from_num .'" in external numbers. Falling back to default number "'. $user['ext'] .'".' );
			$from_num = false;
		}
	} else {
		$user_external_numbers = array();
	}
} else {
	if (defined('GS_LVM_CALL_INIT_USERS_500000') && GS_LVM_CALL_INIT_USERS_500000 && $user_code >= '500000') {
		die_invalid( 'Agenturmitarbeiter - must use from=' );
	}
}


# cidnum
#
if (isSet( $_REQUEST['cidnum'] ))
	$cidnum = _normalize_number( $_REQUEST['cidnum'] );
elseif (isSet( $_REQUEST['callerid'] ))
	$cidnum = _normalize_number( $_REQUEST['callerid'] );
else
	$cidnum = false;  # default

if ($cidnum == $user['ext'])
	$cidnum = false;
if ($cidnum) {
	if (! defined('GS_LVM_CALL_INIT_USERS_500000') || ! GS_LVM_CALL_INIT_USERS_500000 || $user_code < '500000') {
		if (! is_array($user_external_numbers)) {
			# we might already have that
			$user_external_numbers = @ gs_user_external_numbers_get( $user_code );
			if (isGsError($user_external_numbers)) {
				gs_log( GS_LOG_WARNING, $user_external_numbers->getMsg() );
				$user_external_numbers = array();
			}
			if (! is_array($user_external_numbers))
				$user_external_numbers = array();
		}
		if (! in_array($cidnum, $user_external_numbers, true)) {
			gs_log( GS_LOG_WARNING, 'User '. $user_code .' does not have "'. $cidnum .'" in external numbers. Falling back to default number (from=) as CIDnum.' );
			$cidnum = false;
		}
	} else {
		$cidnum = false;
	}
}



# to number
#
if (! isSet( $_REQUEST['to'] ))
	die_invalid( 'No phone number specified. Use to=' );
$to_num = _normalize_number( $_REQUEST['to'] );

if ( ($from_num && $to_num == $from_num)
  || (! $from_num && $to_num == $user['ext']) )
{
	# from_num and to_num must not be the same - would probably result
	# in voicemail picking up the phone
	gs_log( GS_LOG_NOTICE, 'Won\'t make a call when from and to numbers are equal ('. $to_num .').' );
	die_invalid( 'Can\'t make a call from "'. ($from_num ? $from_num : $user['ext']) .'" to "'. $to_num .'".' );
}


# CLIR
#
if (! isSet( $_REQUEST['clir'] ))
	$clir = false;
else {
	$clir = strToLower(trim( $_REQUEST['clir'] ));
	$clir = ($clir > 0 || $clir=='yes' || $clir=='true');
}

# private call
#
if (! isSet( $_REQUEST['prv'] ))
	$prv = false;
else {
	$prv = strToLower(trim( $_REQUEST['prv'] ));
	$prv = ($prv > 0 || $prv=='yes' || $prv=='true');
}
$prvPrefix = $prv ? '*7*' : '';




gs_log( GS_LOG_DEBUG, "Init call - user: $user_code, from: ". ($from_num ? $from_num : '(default)') .", to: $to_num, clir:". ($clir ? 'yes':'no') );

if (! $clir) {
	if (! defined('GS_LVM_CALL_INIT_USERS_500000') || ! GS_LVM_CALL_INIT_USERS_500000 || $user_code < '500000') {
		$firstname_abbr = mb_subStr($user['firstname'],0,1);
		$firstname_abbr = ($firstname_abbr != '') ? $firstname_abbr .'. ' : '';
		if (! $cidnum)
			$callerid = $firstname_abbr . $user['lastname'] .' <'. ($from_num ? $from_num : $user['ext']) .'>';
		else
			$callerid = $firstname_abbr . $user['lastname'] .' <'. ($cidnum ? $cidnum : ($from_num ? $from_num : $user['ext'])) .'>';
	} else {
		$callerid = 'Agenturmitarbeiter <anonymous>';
	}
} else {
	$callerid = 'Anonymous <anonymous>';
}

$call = "Channel: Local/". ($from_num ? $from_num : $user['ext']) ."\n"
      . "MaxRetries: 0\n"
      //. "RetryTime: 5\n"
      . "WaitTime: 15\n"
      . "Context: from-internal-users\n"
      . "Extension: $prvPrefix$to_num\n"
      . "Callerid: $callerid\n"
      . "Setvar: __user_id=". $user['id'] ."\n"
      . "Setvar: __user_name=". $user['ext'] ."\n"
      . "Setvar: CHANNEL(language)=de\n"
      . "Setvar: __is_callcompletion=1\n"  # prevent vm from answering?
;
//echo $call;

$filename = '/tmp/gs-'. $user['id'] .'-'. _pack_int(time()) . rand(100,999) .'.call';
$cf = @ fOpen( $filename, 'wb' );
if (! $cf) {
	gs_log( GS_LOG_WARNING, 'Failed to write call file "'. $filename .'"' );
	die_error( 'Failed to write call file.' );
}
@ fWrite( $cf, $call, strLen($call) );
@ fClose( $cf );
@ chmod( $filename, 00666 );

$spoolfile = '/var/spool/asterisk/outgoing/'. baseName($filename);


$our_host_ids = @ gs_get_listen_to_ids();
if (! is_array($our_host_ids)) $our_host_ids = array();

if (defined('GS_LVM_CALL_INIT_USERS_500000') && GS_LVM_CALL_INIT_USERS_500000 && $user_code >= '500000') {
	$hosts = gs_hosts_get();
	if (isGsError($hosts) || ! is_array($hosts) || count($hosts)<1)
		die_error( 'Could not get hosts.' );
	//$host = $hosts[rand(0,count($hosts)-1)];
	$host = $hosts[0];
	$user['host_id'] = $host['id'];
	$user['host']    = $host['host'];
}

if (in_array( $user['host_id'], $our_host_ids )) {
	
	# the Asterisk of this user and the web server both run on this host
	
	$ok = @ rename( $filename, $spoolfile );
	if ($ok < 1) {
		@ unlink( $filename );
		gs_log( GS_LOG_WARNING, 'Failed to move call file "'. $filename .'" to "'. '/var/spool/asterisk/outgoing/'. baseName($filename) .'"' );
		die_error( 'Failed to move call file.' );
	}
	
} else {
	
	$cmd = 'sudo scp -o StrictHostKeyChecking=no -o BatchMode=yes '. escapeShellArg( $filename ) .' '. escapeShellArg( 'root@'. $user['host'] .':'. $filename );
	//echo $cmd, "\n";
	@ exec( $cmd .' 1>>/dev/null 2>&1', $out, $err );
	@ unlink( $filename );
	if ($err != 0) {
		gs_log( GS_LOG_WARNING, 'Failed to scp call file "'. $filename .'" to '. $user['host'] );
		die_error( 'Failed to scp call file.' );
	}
	//remote_exec( $user['host'], $cmd, 10, $out, $err ); // <-- does not use sudo!
	$cmd = 'sudo ssh -o StrictHostKeyChecking=no -o BatchMode=yes -l root '. escapeShellArg( $user['host'] ) .' '. escapeShellArg( 'mv '. escapeShellArg( $filename ) .' '. escapeShellArg( $spoolfile ) );
	//echo $cmd, "\n";
	@ exec( $cmd .' 1>>/dev/null 2>&1', $out, $err );
	if ($err != 0) {
		gs_log( GS_LOG_WARNING, 'Failed to mv call file "'. $filename .'" on '. $user['host'] .' to "'. $spoolfile .'"' );
		die_error( 'Failed to mv call file on remote host.' );
	}
	
}

die_ok( "OK. Calling $to_num from ". ($from_num ? $from_num : $user['ext']) ." ..." );


?>