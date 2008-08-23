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


header( 'Content-Type: text/plain; charset=utf-8' );
header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );


require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'inc/prov-fns.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );
set_error_handler('err_handler_die_on_err');

function setting( $name, $idx, $val, $attrs=null, $writeable=false )
{
	echo $name ,': ', str_replace(array("\n", "\r"), array(' ', ' '), $val) ,"\n";
}

function psetting( $name, $val, $writeable=false )
{
	setting( $name, null, $val, null, $writeable );
}

function aastra_get_expansion_modules()
{
	$exp_mod = array();
	if (@$_SERVER['HTTP_X_AASTRA_EXPMOD1']) {
		$exp_mod[0] = 'aastra-'. strToLower($_SERVER['HTTP_X_AASTRA_EXPMOD1']);
		gs_log( GS_LOG_DEBUG, 'Expansion module 1 : '. $exp_mod[0]);
	}	
	if (@$_SERVER['HTTP_X_AASTRA_EXPMOD2']) {
		$exp_mod[1] = 'aastra-'. strToLower($_SERVER['HTTP_X_AASTRA_EXPMOD2']);
		gs_log( GS_LOG_DEBUG, 'Expansion module 2 : '. $exp_mod[1]);
	}
	if (@$_SERVER['HTTP_X_AASTRA_EXPMOD3']) {
		$exp_mod[2] = 'aastra-'. strToLower($_SERVER['HTTP_X_AASTRA_EXPMOD3']);
		gs_log( GS_LOG_DEBUG, 'Expansion module 3 : '. $exp_mod[3]);
	}
	
	return $exp_mod;
}


/*
function aastra_keys_out( $user_id, $phone_type, $module=0 )
{
	global $db;
	
	$module_sql = '';
	if ($module) {
		$module_sql = 'AND `key` LIKE \'expmod'.((int)$module).'%\'';	
	}
	
	$query =
'SELECT
	`key`, `function`, `number`, `title`, `flags`
FROM `softkeys`
WHERE
	`user_id`='. (int)$user_id. ' AND
	`phone_type`=\''. $db->escape($phone_type). '\''.
$module_sql;
	
	$rs = $db->execute( $query );
	if (! $rs) return false;
	
	while ($r = @$rs->fetchRow()) {
		$key_function = 'speeddial';
		if ($r['function'] === 'Dial') $key_function = 'blf';
		
		if (preg_match('/^expmod\d{1}page\d{1}/', $r['key'])) {
			psetting($r['key'], $r['title']);
		} else {
			psetting($r['key'].' type' , $key_function);
			psetting($r['key'].' label', $r['title']);
			psetting($r['key'].' value', $r['number']);
		}
	}
	
	return true;
}
*/

if (! gs_get_conf('GS_AASTRA_PROV_ENABLED')) {
	gs_log( GS_LOG_DEBUG, "Aastra provisioning not enabled" );
	die( 'Not enabled.' );
}

$requester = gs_prov_check_trust_requester();
if (! $requester['allowed']) {
	die( 'No! See log for details.' );
}

$mac = preg_replace( '/[^0-9A-F]/', '', strToUpper( @$_REQUEST['mac'] ) );
if (strLen($mac) !== 12) {
	gs_log( GS_LOG_NOTICE, "Aastra provisioning: Invalid MAC address \"$mac\" (wrong length)" );
	# don't explain this to the users
	die( 'No! See log for details.' );
}
if (hexDec(subStr($mac,0,2)) % 2 == 1) {
	gs_log( GS_LOG_NOTICE, "Aastra provisioning: Invalid MAC address \"$mac\" (multicast address)" );
	# don't explain this to the users
	die( 'No! See log for details.' );
}
if ($mac === '000000000000') {
	gs_log( GS_LOG_NOTICE, "Aastra provisioning: Invalid MAC address \"$mac\" (huh?)" );
	# don't explain this to the users
	die( 'No! See log for details.' );
}

# make sure the phone is an Aastra:
#
if (subStr($mac,0,6) !== '00085D') {
	gs_log( GS_LOG_NOTICE, "Aastra provisioning: MAC address \"$mac\" is not an Aastra phone" );
	# don't explain this to the users
	die( 'No! See log for details.' );
}

$ua = trim( @$_SERVER['HTTP_USER_AGENT'] );
//FIXME - do some more checks here

$ua_arr = explode(' ', $ua);
$phone_model = str_replace('Aastra', '', $ua_arr[0]);  //FIXME
if ($phone_model === @$ua_arr[0]) $phone_model = '57i';
$phone_type = 'aastra-'.$phone_model;

gs_log( GS_LOG_DEBUG, "Aastra phone \"$mac\" asks for settings (UA: ...\"$ua\") - model $phone_model" );

$prov_url_aastra = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'aastra/';

require_once( GS_DIR .'inc/db_connect.php' );
require_once( GS_DIR .'inc/nobody-extensions.php' );
require_once( GS_DIR .'inc/gs-lib.php' );

$db = gs_db_master_connect();
if (! $db) {
	gs_log( GS_LOG_WARNING, "Aastra phone asks for settings - Could not connect to DB" );
	die( 'Could not connect to DB.' );
}


# do we know the phone?
#
$user_id = @gs_prov_user_id_by_mac_addr( $db, $mac );
if ($user_id < 1) {
	if (! GS_PROV_AUTO_ADD_PHONE) {
		gs_log( GS_LOG_NOTICE, "New phone $mac not added to DB. Enable PROV_AUTO_ADD_PHONE" );
		die( 'Unknown phone. (Enable PROV_AUTO_ADD_PHONE in order to auto-add)' );
	}
	gs_log( GS_LOG_NOTICE, "Adding new Aastra phone $mac to DB" );
	
	$user_id = @gs_prov_add_phone_get_nobody_user_id( $db, $mac, $phone_type, $requester['phone_ip'] );
	if ($user_id < 1) {
		gs_log( GS_LOG_WARNING, "Failed to add nobody user for new phone $mac" );
		die( 'Failed to add nobody user for new phone.' );
	}
}


# is it a valid user id?
#
$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `users` WHERE `id`='. $user_id );
if ($num < 1)
	$user_id = 0;

if ($user_id < 1) {
	# something bad happened, nobody (not even a nobody user) is logged
	# in at that phone. assign the default nobody user of the phone:
	$user_id = @gs_prov_assign_default_nobody( $db, $mac, null );
	if ($user_id < 1) {
		die( 'Failed to assign nobody account to phone '. $mac );
	}
}


# get host for user
#
$host = @gs_prov_get_host_for_user_id( $db, $user_id );
if (! $host) {
	die( 'Failed to find host.' );
}
$pbx = $host;  # $host might be changed if SBC configured


# who is logged in at that phone?
#
$user = @gs_prov_get_user_info( $db, $user_id );
if (! is_array($user)) {
	die( 'DB error.' );
}


# store the user's current IP address in the database:
#
if (! @gs_prov_update_user_ip( $db, $user_id, $requester['phone_ip'] )) {
	gs_log( GS_LOG_WARNING, 'Failed to store current IP addr of user ID '. $user_id );
}


# get SIP proxy to be set as the phone's outbound proxy
#
$sip_proxy_and_sbc = gs_prov_get_wan_outbound_proxy( $db, $requester['phone_ip'], $user_id );
if ($sip_proxy_and_sbc['sip_server_from_wan'] != '') {
	$host = $sip_proxy_and_sbc['sip_server_from_wan'];
}


# get extension without route prefix
#
if (gs_get_conf('GS_BOI_ENABLED')) {
	$hp_route_prefix = (string)$DBM->executeGetOne(
		'SELECT `value` FROM `host_params` '.
		'WHERE '.
			'`host_id`='. (int)$user['host_id'] .' AND '.
			'`param`=\'route_prefix\''
		);
	$user_ext = (subStr($user['name'],0,strLen($hp_route_prefix)) === $hp_route_prefix)
		? subStr($user['name'], strLen($hp_route_prefix)) : $user['name'];
		gs_log( GS_LOG_DEBUG, "Mapping ext. ". $user['name'] ." to $user_ext for provisioning - route_prefix: $hp_route_prefix, host id: ". $user['host_id'] );
} else {
	$user_ext = $user['name'];
}



ob_start();


# allow to push config from this host
psetting('xml application post list', GS_PROV_HOST);


# set some default keys
psetting('services script'    , $prov_url_aastra.'pb.php');
psetting('callers list script', $prov_url_aastra.'dial-log.php');

/* //FIXME
psetting('softkey1 type'      , 'xml');
psetting('softkey1 label'     , __('Tel.buch'));
psetting('softkey1 value'     , $prov_url_aastra.'pb.php');

psetting('softkey2 type'      , 'xml');
psetting('softkey2 label'     , __('Anrufliste'));
psetting('softkey2 value'     , $prov_url_aastra.'dial-log.php');
*/


# get softkeys
//aastra_keys_out( $user_id, $phone_type );

# get softkeys on expansion modules
/*  //FIXME
$exp_mods = aastra_get_expansion_modules();
foreach ($exp_mods as $key => $exp_mod) {
	aastra_keys_out( $user_id, $exp_mod, ($key+1));
}
*/



#####################################################################
#  SIP
#####################################################################

psetting('sip mode'                , '0');  # ?
psetting('sip screen name'         , $user_ext .' '. mb_subStr($user['firstname'],0,1) .'. '. $user['lastname']);
psetting('sip display name'        , $user['firstname'].' '.$user['lastname']);
psetting('sip user name'           , $user_ext);
psetting('sip vmail'               , 'voicemail');
psetting('sip auth name'           , $user_ext);
psetting('sip password'            , $user['secret']);
psetting('sip registrar ip'        , $host);
psetting('sip registrar port'      , '5060');
psetting('sip registration period' , '3600');
psetting('sip outbound proxy'      , ($sip_proxy_and_sbc['sip_proxy_from_wan'] != '' ? $sip_proxy_and_sbc['sip_proxy_from_wan'] : $host) );
psetting('sip outbound proxy port' , '5060');



#####################################################################
#  Override provisioning parameters
#####################################################################

$prov_params = null;
$GS_ProvParams = gs_get_prov_params_obj( $phone_type );
if ($GS_ProvParams->set_user( $user['user'] )) {
	if ($GS_ProvParams->retrieve_params( $phone_type, array(
		'{GS_PROV_HOST}'      => gs_get_conf('GS_PROV_HOST'),
		'{GS_P_PBX}'          => $pbx,
		'{GS_P_EXTEN}'        => $user_ext,
		'{GS_P_ROUTE_PREFIX}' => $hp_route_prefix,
		'{GS_P_USER}'         => $user['user']
	) )) {
		$prov_params = $GS_ProvParams->get_params();
	}
}
if (! is_array($prov_params)) {
	gs_log( GS_LOG_WARNING, 'Failed to get provisioning parameters' );
} else {
	foreach ($prov_params as $param_name => $param_arr) {
	foreach ($param_arr as $param_index => $param_value) {
		if ($param_index == -1) {
			# not an array
			gs_log( GS_LOG_DEBUG, "Overriding prov. param \"$param_name\": \"$param_value\"" );
			setting( $param_name, null        , $param_value );
		} else {
			# array
			gs_log( GS_LOG_DEBUG, "Overriding prov. param \"$param_name\"[$param_index]: \"$param_value\"" );
			setting( $param_name, $param_index, $param_value );
		}
	}
	}
}



#####################################################################
#  output
#####################################################################
if (! headers_sent()) {
	# avoid chunked transfer-encoding
	header( 'Content-Length: '. @ob_get_length() );
}
@ob_flush();

?>