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

# pull dynamic configuration update from server?
if ( @$_REQUEST['dynamic'] == 1 )
	$dynamic = true;
else
	$dynamic = false;

if ($dynamic == true) {
	header( 'Content-Type: text/xml; charset=utf-8' );
	header( 'Expires: 0' );
	header( 'Pragma: no-cache' );
	header( 'Cache-Control: private, no-cache, must-revalidate' );
	header( 'Vary: *' );
} else {
	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'Expires: 0' );
	header( 'Pragma: no-cache' );
	header( 'Cache-Control: private, no-cache, must-revalidate' );
	header( 'Vary: *' );
}

require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'inc/prov-fns.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );
include_once( GS_DIR .'inc/aastra-fns.php' );
include_once( GS_DIR .'inc/string.php' );
set_error_handler('err_handler_die_on_err');

function _settings_err( $msg='' )
{
	global $dynamic;
	@ob_end_clean();
	@ob_start();
	echo '<!-- // ', ($msg != '' ? str_replace('--','- -',$msg) : 'Error') ,' // -->',"\n";
	if ($dynamic == true) {
		aastra_textscreen( 'Fehler', $msg );
	} else if (! headers_sent()) {
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'Content-Length: '. (int)@ob_get_length() );
	}
	@ob_end_flush();
	exit(1);
}

function setting( $name, $idx, $val, $attrs=null, $writeable=false, $xml=false )
{
	if ($xml === true)
		echo '  <ConfigurationItem><Parameter>', $name , ($idx>0? ' '.$idx :'') ,'</Parameter><Value>', htmlEnt(str_replace(array("\n", "\r"), array(' ', ' '), $val)) , '</Value></ConfigurationItem>', "\n";
	else
		echo ($writeable == false ? '!' : ''), $name , ($idx>0? ' '.$idx :'') ,': ', str_replace(array("\n", "\r"), array(' ', ' '), $val) ,"\n";
}

function psetting( $name, $val, $writeable=false, $xml=false )
{
	setting( $name, null, $val, null, $writeable, $xml );
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

function aastra_get_softkeys( $user_id, $phone_type )
{
	global $db;
	global $dynamic;
	$softkeys = array();
	$sql_query = 'SELECT `group_id`, `softkey_profile_id`  FROM `users` WHERE `id`='. $user_id;
	$rs = $db->execute($sql_query);
	if (! $rs) return false;
	$r = $rs->fetchRow();
	$group_id           = (int)@$r['group_id'];
	$softkey_profile_id = (int)@$r['softkey_profile_id'];
	
	if ($group_id) {
		$sql_query = 'SELECT `s`.`key`, `s`.`function`, `s`.`data`, `s`.`label`, `s`.`user_writeable` FROM `softkeys` `s` JOIN `user_groups` `u` ON (`u`.`softkey_profile_id` = `s`.`profile_id`) WHERE `u`.`id` = '.$group_id.' AND `s`.`phone_type` = \''.$phone_type.'\'' ;
		$rs = $db->execute($sql_query);
		if (! $rs) return false;
		while ($r = $rs->fetchRow()) {
			$key_num = (int) preg_replace('/[^0-9]/', '', @$r['key']);
			if ($key_num > 200 && $dynamic == true) {
				# no not provision expansion module in dynamic mode
			} else {
				switch ($phone_type) {
				case 'aastra-57i':
					if ($key_num >=  1) $key_name = 'topsoftkey'.($key_num);
					if ($key_num >100) $key_name = 'softkey'    .($key_num-100);
					if ($key_num >200) $key_name = 'expmod1 key'.($key_num-200);
					if ($key_num >300) $key_name = 'expmod2 key'.($key_num-300);
					if ($key_num >400) $key_name = 'expmod3 key'.($key_num-400);
					break;
				case 'aastra-55i':
					if ($key_num >=  1) $key_name = 'prgkey'     .($key_num);
					if ($key_num >100) $key_name = 'softkey'    .($key_num-100);
					if ($key_num >200) $key_name = 'expmod1 key'.($key_num-200);
					if ($key_num >300) $key_name = 'expmod2 key'.($key_num-300);
					if ($key_num >400) $key_name = 'expmod3 key'.($key_num-400);
					break;
				case 'aastra-53i':
					if ($key_num >100) $key_name = 'softkey'    .($key_num-100);
					if ($key_num >200) $key_name = 'expmod1 key'.($key_num-200);
					if ($key_num >300) $key_name = 'expmod2 key'.($key_num-300);
					if ($key_num >400) $key_name = 'expmod3 key'.($key_num-400);
					break;
				default:
					$key_name = 'prgkey'.$key_num;
				}
				$softkeys[$key_name] = $r;
			}
		}
	}
	
	if ($softkey_profile_id) {
		$sql_query = 'SELECT `key`, `function`, `data`, `label` FROM `softkeys` WHERE `profile_id` = '.$softkey_profile_id.' AND `phone_type` = \''.$phone_type.'\'' ;
		$rs = $db->execute($sql_query);
		if (! $rs) return false;
		while ($r = $rs->fetchRow()) {
			$key_num = (int) preg_replace('/[^0-9]/', '', @$r['key']);
			if ($key_num > 200 && $dynamic == true) {
				# no not provision expansion module in dynamic mode
			} else {
				switch ($phone_type) {
				case 'aastra-57i':
					if ($key_num >=  1) $key_name = 'topsoftkey'.($key_num);
					if ($key_num >100) $key_name = 'softkey'    .($key_num-100);
					if ($key_num >200) $key_name = 'expmod1 key'.($key_num-200);
					if ($key_num >300) $key_name = 'expmod2 key'.($key_num-300);
					if ($key_num >400) $key_name = 'expmod3 key'.($key_num-400);
					break;
				case 'aastra-55i':
					if ($key_num >=  1) $key_name = 'prgkey'     .($key_num);
					if ($key_num >100) $key_name = 'softkey'    .($key_num-100);
					if ($key_num >200) $key_name = 'expmod1 key'.($key_num-200);
					if ($key_num >300) $key_name = 'expmod2 key'.($key_num-300);
					if ($key_num >400) $key_name = 'expmod3 key'.($key_num-400);
					break;
				case 'aastra-53i':
					if ($key_num >100) $key_name = 'softkey'    .($key_num-100);
					if ($key_num >200) $key_name = 'expmod1 key'.($key_num-200);
					if ($key_num >300) $key_name = 'expmod2 key'.($key_num-300);
					if ($key_num >400) $key_name = 'expmod3 key'.($key_num-400);
					break;
				default:
					$key_name = 'prgkey'.$key_num;
				}
				$softkeys[$key_name] = $r;
			}
		}
		
	}
	
	return $softkeys;
}


if (! gs_get_conf('GS_AASTRA_PROV_ENABLED')) {
	gs_log( GS_LOG_DEBUG, "Aastra provisioning not enabled" );
	_settings_err( 'Not enabled.' );
}

$requester = gs_prov_check_trust_requester();
if (! $requester['allowed']) {
	_settings_err( 'No! See log for details.' );
}


if ( isset($_REQUEST['mac']) ) {
	$mac = preg_replace( '/[^0-9A-F]/', '', strToUpper( $_REQUEST['mac'] ) );
	if (strLen($mac) !== 12) {
		gs_log( GS_LOG_NOTICE, "Aastra provisioning: Invalid MAC address \"$mac\" (wrong length)" );
		# don't explain this to the users
		_settings_err( 'No! See log for details.' );
	}
	if (hexDec(subStr($mac,0,2)) % 2 == 1) {
		gs_log( GS_LOG_NOTICE, "Aastra provisioning: Invalid MAC address \"$mac\" (multicast address)" );
		# don't explain this to the users
		_settings_err( 'No! See log for details.' );
	}
	if ($mac === '000000000000') {
		gs_log( GS_LOG_NOTICE, "Aastra provisioning: Invalid MAC address \"$mac\" (huh?)" );
		# don't explain this to the users
		_settings_err( 'No! See log for details.' );
	}

	# make sure the phone is an Aastra:
	#
	if (subStr($mac,0,6) !== '00085D') {
		gs_log( GS_LOG_NOTICE, "Aastra provisioning: MAC address \"$mac\" is not an Aastra phone" );
		# don't explain this to the users
		_settings_err( 'No! See log for details.' );
	}
}

$ua = trim( @$_SERVER['HTTP_USER_AGENT'] );
# "Aastra57i MAC:00-08-5D-1A-98-70 V:2.0.1.1055-0035-00-A1"
# "Aastra57i MAC:00-08-5D-1A-98-70 V:2.1.0.2145-SIP"
# "Aastra57i MAC:00-08-5D-1A-99-01 V:2.1.2.30-SIP"
# "Aastra57i MAC:00-08-5D-1A-98-70 V:2.4.1.37-SIP"
# "Aastra53i MAC:00-08-5D-12-64-11 V:2.4.0.96-SIP"
if (! preg_match('/^Aastra/', $ua)
||  ! preg_match('/MAC:00-08-5D/i', $ua) ) {
	gs_log( GS_LOG_WARNING, "Phone with MAC \"$mac\" (Aastra) has invalid User-Agent (\"". $ua ."\")" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}

# find out the type of the phone:
if (preg_match('/^Aastra([1-9][0-9]{1,4}i?) /', $ua, $m))  # e.g. "Aastra57i"
	$phone_model = $m[1];  # e.g. "57i"
else
	$phone_model = 'unknown';

$phone_type = 'aastra-'.$phone_model;  # e.g. "aastra-57i"
# to be used when auto-adding the phone

$fw_vers = (preg_match('/ V:(\d+\.\d+\.\d+)/', $ua, $m))
	? $m[1] : '0.0.0';
//$fw_vers_nrml = _aastra_normalize_version( $fw_vers );

if (! isset($_REQUEST['mac']) ) {
	if ( preg_match('/\sMAC:(00-08-5D-\w{2}-\w{2}-\w{2})\s/', $ua, $m) )
		$mac = preg_replace( '/[^0-9A-F]/', '', strToUpper($m[1]) );
}

$prov_url_aastra = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'aastra/';

require_once( GS_DIR .'inc/db_connect.php' );
require_once( GS_DIR .'inc/nobody-extensions.php' );
include_once( GS_DIR .'inc/gs-fns/gs_prov_params_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_prov_params_get.php' );

# phone requests global settings
if ( (!isset($_REQUEST['mac'])) && ($dynamic == false) ) {

	gs_log( GS_LOG_DEBUG, "Aastra phone \"$mac\" asks for global settings (UA: ...\"$ua\") - model $phone_model" );

	ob_start();

	psetting('options simple menu'                 , 1, false, false);
	psetting('dhcp'                                , 1, false, false);
	psetting('backlight mode'                      , 1, false, false);
	// psetting('bl on time'                          , 16, false, false);
	psetting('bl on time'                          , 600, false, false);
	psetting('tone set'                            , 'Germany', false, false);
	psetting('language 1'                          , 'lang_de.txt', false, false);
	psetting('language'                            , 1, false, false);
	psetting('time format'                         , 1, false, false);
	psetting('date format'                         , 11, false, false);
	psetting('time zone name'                      , 'DE-Berlin', false, false);
	psetting('xml beep notification'               , 1, false, false);
	psetting('xml status scroll delay'             , 3, false, false);
	psetting('sip update callerid'                 , 1, false, false);
	psetting('admin password'                      , gs_get_conf('GS_AASTRA_PROV_ADMIN_PASS'), false, false);
	psetting('user password'                       , gs_get_conf('GS_AASTRA_PROV_USER_PASS'), false, false);
	psetting('options password enabled'            , 1, false, false);
	psetting('web interface enabled'               , 1, false, false);
	psetting('directed call pickup'                , 1, false, false);
	psetting('directed call pickup prefix'         , '*81*', false, false);
	psetting('sip xml notify event'                , 1, false, false);
	psetting('sip whitelist'                       , 0, false, false);
	psetting('auto resync mode'                    , 3, false, false);
	psetting('auto resync time'                    , '04:00', false, false);
	psetting('auto resync days'                    , 1, false, false);
	psetting('dynamic sip'                         , 1, false, false);
	psetting('call forward disabled'               , 1, false, false);
	psetting('call waiting'                        , 1, false, false);
	psetting('missed calls indicator disabled'     , 1, false, false);
	psetting('sip explicit mwi subscription'       , 1, false, false);
	psetting('sip explicit mwi subscription period', 120, false, false);
	psetting('sip registration retry timer'        , 60, false, false);
	psetting('sip registration timeout retry timer', 30, false, false);
	psetting('sip blf subscription period'         , 120, false, false);
	psetting('sip customized codec'                , 'payload=8;payload=0;payload=115;payload=9;payload=18;silsupp=off', false, false);
	psetting('sip silence suppression'             , 0, false, false);
	//psetting('sip dial plan'                       , 'X+#|XX+*', false, false);
	psetting('lldp'                                , 0, false, false);
	psetting('call hold reminder'                  , 0, false, false);
	psetting('sip diversion display'               , 1, false, false);
	psetting('show call destination name'          , 0, false, false);
	if (gs_get_conf('GS_AASTRA_PROV_FW_UPDATE'))
		psetting('firmware server'                     , $prov_url_aastra.'sw', false, false);
	else
		psetting('firmware server'                     , '', false, false);

	# allow to push config from this host
	psetting('xml application post list', GS_PROV_HOST, false, false);

	# set some default keys
	psetting('services script'    , $prov_url_aastra.'pb.php', false, false);
	psetting('callers list script', $prov_url_aastra.'dial-log.php', false, false);
	psetting('redial script'      , $prov_url_aastra.'dial-log.php?t=out', false, false);
	psetting('redial disabled'    , '0', false, false);

	psetting('sip mode'                , '0', false, false);  # Generic SIP server
	psetting('sip vmail'               , 'voicemail', false, false);
	psetting('sip registrar port'      , '5060', false, false);
	psetting('sip registration period' , '120', false, false);
	psetting('sip outbound proxy port' , '5060', false, false);
	psetting('sip proxy port'          , '5060', false, false);

	psetting('tos sip'  , '26', false, false);
	psetting('tos rtp'  , '46', false, false);
	psetting('tos rtcp' , '', false, false);

	for ($i = 1; $i <= 3; $i++) {
		psetting('expmod'.$i.'page1left'  , '', false, false);
		psetting('expmod'.$i.'page1right' , '', false, false);
		psetting('expmod'.$i.'page2left'  , '', false, false);
		psetting('expmod'.$i.'page2right' , '', false, false);
		psetting('expmod'.$i.'page3left'  , '', false, false);
		psetting('expmod'.$i.'page3right' , '', false, false);
	}
	
	if (! headers_sent()) {
		# avoid chunked transfer-encoding
		header( 'Content-Length: '. @ob_get_length() );
	}
	@ob_flush();
	return;
}

$db = gs_db_master_connect();
if (! $db) {
	gs_log( GS_LOG_WARNING, "Aastra phone asks for settings - Could not connect to DB" );
	_settings_err( 'Could not connect to DB.' );
}

gs_log( GS_LOG_DEBUG, "Aastra phone \"$mac\" asks for local settings (UA: ...\"$ua\") - model $phone_model" );

# do we know the phone?
#
$user_id = @gs_prov_user_id_by_mac_addr( $db, $mac );
if ($user_id < 1) {
	if (! GS_PROV_AUTO_ADD_PHONE) {
		gs_log( GS_LOG_NOTICE, "New phone $mac not added to DB. Enable PROV_AUTO_ADD_PHONE" );
		_settings_err( 'Unknown phone. (Enable PROV_AUTO_ADD_PHONE in order to auto-add)' );
	}
	gs_log( GS_LOG_NOTICE, "Adding new Aastra phone $mac to DB" );
	
	$user_id = @gs_prov_add_phone_get_nobody_user_id( $db, $mac, $phone_type, $requester['phone_ip'] );
	if ($user_id < 1) {
		gs_log( GS_LOG_WARNING, "Failed to add nobody user for new phone $mac" );
		_settings_err( 'Failed to add nobody user for new phone.' );
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
		_settings_err( 'Failed to assign nobody account to phone '. $mac );
	}
}


# get host for user
#
$host = @gs_prov_get_host_for_user_id( $db, $user_id );
if (! $host) {
	_settings_err( 'Failed to find host.' );
}
$pbx = $host;  # $host might be changed if SBC configured


# who is logged in at that phone?
#
$user = @gs_prov_get_user_info( $db, $user_id );
if (! is_array($user)) {
	_settings_err( 'DB error.' );
}


# store the current firmware version in the database:
#
$exp_mods = aastra_get_expansion_modules();
@$db->execute(
	'UPDATE `phones` SET '.
		'`firmware_cur`=\''. $db->escape($fw_vers) .'\', '.
		'`expansion_modules`=\''. $db->escape(implode(';', $exp_mods)) .'\' '.
	'WHERE `mac_addr`=\''. $db->escape($mac) .'\''
	);

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
	$hp_route_prefix = (string)$db->executeGetOne(
		'SELECT `value` FROM `host_params` '.
		'WHERE '.
			'`host_id`='. (int)$user['host_id'] .' AND '.
			'`param`=\'route_prefix\''
		);
	$user_ext = (subStr($user['name'],0,strLen($hp_route_prefix)) === $hp_route_prefix)
		? subStr($user['name'], strLen($hp_route_prefix)) : $user['name'];
	gs_log( GS_LOG_DEBUG, "Mapping ext. ". $user['name'] ." to $user_ext for provisioning - route_prefix: $hp_route_prefix, host id: ". $user['host_id'] );
} else {
	$hp_route_prefix = '';
	$user_ext = $user['name'];
}



ob_start();

if ($dynamic == true) {
	echo '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
	echo '<AastraIPPhoneConfiguration setType="override">',"\n";
}

# reset all visible softkeys
for ($i=1; $i<=12; ++$i) {
	psetting('topsoftkey'.$i.' type', 'none', true, $dynamic);
}
for ($i=1; $i<=12; ++$i) {
	psetting('softkey'.$i.' type'   , 'none', true, $dynamic);
}

if (! $user['nobody_index']) {
	psetting('softkey1 type'   , 'xml', true, $dynamic);
	psetting('softkey1 value'  , $prov_url_aastra.'pb.php', true, $dynamic);
	psetting('softkey1 label'  , __('Tel.buch'), true, $dynamic);

	psetting('softkey2 type'   , 'xml', true, $dynamic);
	psetting('softkey2 value'  , $prov_url_aastra.'dial-log.php', true, $dynamic);
	psetting('softkey2 label'  , __('Anrufliste'), true, $dynamic);

	psetting('softkey3 type'   , 'speeddial', true, $dynamic);
	psetting('softkey3 value'  , 'voicemail', true, $dynamic);
	psetting('softkey3 label'  , __('Voicemail'), true, $dynamic);

	psetting('softkey4 type'   , 'xml', true, $dynamic);
	psetting('softkey4 value'  , $prov_url_aastra.'dnd.php', true, $dynamic);
	$current_dndstate = $db->executeGetOne("SELECT `active` FROM `dnd` WHERE `_user_id`=". $user_id);
	if ($current_dndstate == 'yes')
		psetting('softkey4 label'  , __('Ruhe aus'), true, $dynamic);
	else
		psetting('softkey4 label'  , __('Ruhe'), true, $dynamic);
}

psetting('softkey5 type'   , 'xml', true, $dynamic);
psetting('softkey5 label'  , __('Login'), true, $dynamic);
if ($user['nobody_index'])
	psetting('softkey5 value'  , $prov_url_aastra.'login.php?a=login', true, $dynamic);
else
	psetting('softkey5 value'  , $prov_url_aastra.'login.php', true, $dynamic);

$softkeys = aastra_get_softkeys( $user_id, $phone_type );
if (is_array($softkeys)) {
	foreach ($softkeys as $key_name => $softkey) {
		switch ($softkey['function']) {
		case '_dir':
			$softkey['function'] = 'xml';
			$softkey['data'    ] = $prov_url_aastra.'pb.php';
			$softkey['label'   ] = __('Tel.buch');
			break;
		case '_callers':
			$softkey['function'] = 'xml';
			$softkey['data'    ] = $prov_url_aastra.'dial-log.php';
			$softkey['label'   ] = __('Anrufliste');
			break;
		case '_dnd':
			$softkey['function'] = 'xml';
			$softkey['data'    ] = $prov_url_aastra.'dnd.php';
			$current_dndstate = $db->executeGetOne("SELECT `active` FROM `dnd` WHERE `_user_id`=". $user_id);
			if ($current_dndstate == 'yes')
				$softkey['label'   ] = __('Ruhe aus');
			else
				$softkey['label'   ] = __('Ruhe');
			break;
		case '_fwd':
			$softkey['function'] = 'xml';
			if (strlen($softkey['data']) > 0)
				$softkey['data'    ] = $prov_url_aastra.'cf.php?v='.$softkey['data'];
			else
				$softkey['data'    ] = $prov_url_aastra.'cf.php';
			if (! $softkey['label'])
				$softkey['label'   ] = __('Umleit.');
			break;
		case '_fwd_dlg':
			$softkey['function'] = 'xml';
			$softkey['data'    ] = $prov_url_aastra.'cf.php?d=1';
			if (! $softkey['label'])
				$softkey['label'   ] = __('Umleit.');
			break;
		case '_login':
			$softkey['function'] = 'xml';
			$softkey['label'   ] = __('Login');
			if ($user['nobody_index'])
				$softkey['data'] = $prov_url_aastra.'login.php?a=login';
			else
				$softkey['data'] = $prov_url_aastra.'login.php';
			break;
		}
		psetting($key_name.' type' , $softkey['function'], true, $dynamic);
		psetting($key_name.' value', $softkey['data'    ], true, $dynamic);
		psetting($key_name.' label', $softkey['label'   ], true, $dynamic);
	}
}

#####################################################################
#  SIP
#####################################################################

psetting('sip screen name'         , $user_ext .' '. mb_subStr($user['firstname'],0,1) .'. '. $user['lastname'], true, $dynamic);
psetting('sip display name'        , $user['firstname'].' '.$user['lastname'], true, $dynamic);
psetting('sip user name'           , $user_ext, true, $dynamic);
psetting('sip auth name'           , $user_ext, true, $dynamic);
psetting('sip password'            , $user['secret'], true, $dynamic);
psetting('sip registrar ip'        , $host, true, $dynamic);
psetting('sip outbound proxy'      , ($sip_proxy_and_sbc['sip_proxy_from_wan'] != '' ? $sip_proxy_and_sbc['sip_proxy_from_wan'] : $host), true, $dynamic );
psetting('sip proxy ip'            , $host, true, $dynamic);

#####################################################################
#  other settings
#####################################################################

/*
#setting('language', 0, $prov_url_aastra .'lang_en.txt');  # English. Default
setting('language', 1, $prov_url_aastra .'lang/lang_de.txt');
setting('language', 2, $prov_url_aastra .'lang/lang_fr.txt');
setting('language', 3, $prov_url_aastra .'lang/lang_es.txt');
setting('language', 4, $prov_url_aastra .'lang/lang_it.txt');
# max. 4 custom languages!
#setting('language', 4, $prov_url_aastra .'lang/lang_fr_ca.txt');
#setting('language', 4, $prov_url_aastra .'lang/lang_es_mx.txt');
psetting('language', '1');
*/



#####################################################################
#  Override provisioning parameters (group profile)
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
	gs_log( GS_LOG_WARNING, 'Failed to get provisioning parameters (group)' );
} else {
	foreach ($prov_params as $param_name => $param_arr) {
	foreach ($param_arr as $param_index => $param_value) {
		if ($param_index == -1) {
			# not an array
			gs_log( GS_LOG_DEBUG, "Overriding group prov. param \"$param_name\": \"$param_value\"" );
			setting( $param_name, null        , $param_value, null, true, $dynamic );
		} else {
			# array
			gs_log( GS_LOG_DEBUG, "Overriding group prov. param \"$param_name\"[$param_index]: \"$param_value\"" );
			setting( $param_name, $param_index, $param_value, null, true, $dynamic );
		}
	}
	}
}
unset($prov_params);
unset($GS_ProvParams);


#####################################################################
#  Override provisioning parameters (user profile)
#####################################################################
$prov_params = @gs_user_prov_params_get( $user['user'], $phone_type );
if (! is_array($prov_params)) {
	gs_log( GS_LOG_WARNING, 'Failed to get provisioning parameters (user)' );
} else {
	foreach ($prov_params as $p) {
		if ($p['index'] === null
		||  $p['index'] ==  -1) {
			# not an array
			gs_log( GS_LOG_DEBUG, 'Overriding user prov. param "'.$p['param'].'": "'.$p['value'].'"' );
			setting( $p['param'], null       , $p['value'], null, true, $dynamic );
		} else {
			# array
			gs_log( GS_LOG_DEBUG, 'Overriding user prov. param "'.$p['param'].'"['.$p['index'].']: "'.$p['value'].'"' );
			setting( $p['param'], $p['index'], $p['value'], null, true, $dynamic );
		}
	}
}
unset($prov_params);

if ($dynamic == true)
	echo '</AastraIPPhoneConfiguration>',"\n";

#####################################################################
#  output
#####################################################################
if (! headers_sent()) {
	# avoid chunked transfer-encoding
	header( 'Content-Length: '. @ob_get_length() );
}
@ob_flush();

?>