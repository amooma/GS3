<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Rev$
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
   user=    user name
   to=      phone number
   file=    PDF or PostScript file
   tsi=    Fax TSI (default $FAX_TSI[0])
   res=    resolution in lpi (default 98)
*/

define( 'GS_VALID', true );  /// this is a parent file
require_once( dirName(__FILE__) .'/../../inc/conf.php' );
require_once( GS_DIR .'inc/cn_hylafax.php' );
require_once( GS_DIR .'inc/netmask.php' );
require_once( GS_DIR .'inc/gs-fns/gs_user_email_address_get.php' );
require_once( GS_DIR .'inc/gs-fns/gs_user_pin_get.php' );

header( 'Content-Type: text/plain; charset=utf-8' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Expires: 0' );
header( 'Vary: *' );

function die_not_allowed( $msg ) 
{
	if (! headers_sent()) {
		header( 'HTTP/1.0 403 Forbidden', true, 403 );
		header( 'Status: 403 Forbidden' , true, 403 );
	}
	die( $msg );
}

function die_invalid( $msg ) 
{
	if (! headers_sent()) {
		header( 'HTTP/1.0 400 Bad Request', true, 400 );
		header( 'Status: 400 Bad Request' , true, 400 );
	}
	die( $msg );
}

function die_ok( $msg ) 
{
	if (! headers_sent()) {
		//header( 'HTTP/1.0 204 No Content', true, 204 );
		//header( 'Status: 204 No Content' , true, 204 );
		header( 'HTTP/1.0 200 OK', true, 200 );
		header( 'Status: 200 OK' , true, 200 );
	}
	die( $msg );
}

function die_error( $msg ) 
{
	if (! headers_sent()) {
		header( 'HTTP/1.0 500 Internal Server Error', true, 500 );
		header( 'Status: 500 Internal Server Error' , true, 500 );
	}
	die( $msg );
}

function get_user_id( $user ) 
{
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user_id
	#
	$user_id = (int)$db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	
	if ($user_id < 1)
		return new GsError( 'Unknown user.' );

	return $user_id;
}

# check if client's IP is allowed to send faxes
#
$remote_ip = @$_SERVER['REMOTE_ADDR'];
$networks = explode(',', gs_get_conf('GS_FAX_INIT_FROM_NET'));
$allowed = false;
foreach ($networks as $net) {
	if (ip_addr_in_network( $remote_ip, trim($net) )) {
		$allowed = true;
		$net = trim($net);
		break;
	}
}
if ($allowed) {
	gs_log( GS_LOG_DEBUG, "IP $remote_ip is in $net => allowed to accept fax request from" );
} else {
	gs_log( GS_LOG_NOTICE, "IP $remote_ip is not in GS_CALL_INIT_FROM_NET => not allowed to accept fax request from" );
	die_not_allowed( 'You are not allowed to send a fax.' );
}

$user_id    = 0;

$tsi        = trim(@$_REQUEST['tsi']);
$to         = trim(@$_REQUEST['to']);
$resolution = (int) trim(@$_REQUEST['res']);
$user       = trim(@$_REQUEST['user']);
$sidnum     = trim(@$_REQUEST['cidnum']);
$local_file = trim(@$_REQUEST['file']);

if ($user != '') {
	$user_id = get_user_id($user);
}

# check if user is known to GS
#
if ($user_id < 1) {
	gs_log( GS_LOG_NOTICE, "User \"$user\" unknown" );
	die_not_allowed( 'You are not allowed to send a fax.' );
}

# get email address and PIN 
#
$email = gs_user_email_address_get( $user );
$pin   = gs_user_pin_get( $user );

# use default fax TSI if not provided in http request
#
if ($tsi == '') {
	$fax_tsis_global = explode(',',gs_get_conf('GS_FAX_TSI'));
	if ( array_key_exists(0, $fax_tsis_global) && $fax_tsis_global[0] != '')
		$tsi = $fax_tsis_global[0];
	else
		$tsi = '0';
}

# if no local file is specified check if ist's provided in http request
#
if ( ($local_file == '') && 
	is_array($_FILES) &&
	array_key_exists('file', $_FILES) &&
	($_FILES['file']['error'] == 0) &&
	($_FILES['file']['size']   > 0) ) {
	
	$local_file = $_FILES['file']['tmp_name'];
}


# invoke function from the fax library
#
if ($local_file != '') {
	$local_file = gs_get_conf('GS_FAX_INIT_DOCDIR', '') . '/'. preg_replace('/\.\./', '', $local_file);

	if (file_exists($local_file))
		$fax_job_id = fax_send(
			$user_id,
			$user,
			$to,
			$tsi,
			$local_file,
			$email,
			$resolution,
			$pin
		);
}

# result
#
if (isset($fax_job_id) && ($fax_job_id >= 1))
	die_ok( 'Fax job sent with id: '.$fax_job_id);
else
	die_error( 'Fax job not accepted' );

