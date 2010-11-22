<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2010, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
* Sebastian Ertz <gemeinschaft@swastel.eisfair.net>
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
require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'inc/prov-fns.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );
set_error_handler('err_handler_die_on_err');

function _snom_normalize_version( $appvers )
{
	$tmp = explode('.', $appvers);
	$vmaj = str_pad((int)@$tmp[0], 2, '0', STR_PAD_LEFT);
	$vmin = str_pad((int)@$tmp[1], 2, '0', STR_PAD_LEFT);
	$vsub = str_pad((int)@$tmp[2], 2, '0', STR_PAD_LEFT);
	return $vmaj.'.'.$vmin.'.'.$vsub;
}

function _snomCnfXmlEsc( $str )
{
	return str_replace(
		array('&'    , '<'   , '>'   , '"'   ),
		array('&amp;', '&lt;', '&gt;', '\'\''),
		$str);
}

function _settings_err( $msg='' )
{
	@ob_start();
	echo '<!-- // ', _snomCnfXmlEsc($msg != '' ? str_replace('--','- -',$msg) : 'Error') ,' // -->',"\n";
	if (! headers_sent()) {
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'Content-Length: '. (int)@ob_get_length() );
	}
	@ob_end_flush();
	exit(1);
}

if (gs_get_conf('GS_SNOM_PROV_M9_ACCOUNTS') < 1) {
	gs_log( GS_LOG_DEBUG, "Snom M9 provisioning not enabled" );
	_settings_err( 'Not enabled.' );
}



$requester = gs_prov_check_trust_requester();
if (! $requester['allowed']) {
	_settings_err( 'No! See log for details.' );
}

$mac = preg_replace( '/[^0-9A-F]/', '', strToUpper( @$_REQUEST['mac'] ) );
if (strLen($mac) !== 12) {
	gs_log( GS_LOG_NOTICE, "Snom M9 provisioning: Invalid MAC address \"$mac\" (wrong length)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}
if (hexDec(subStr($mac,0,2)) % 2 == 1) {
	gs_log( GS_LOG_NOTICE, "Snom M9 provisioning: Invalid MAC address \"$mac\" (multicast address)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}
if ($mac === '000000000000') {
	gs_log( GS_LOG_NOTICE, "Snom M9 provisioning: Invalid MAC address \"$mac\" (huh?)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}

# make sure the phone is a Snom-M9:
#
if ( (subStr($mac,0,6) !== '000413') ) {
	gs_log( GS_LOG_NOTICE, "Snom M9 provisioning: MAC address \"$mac\" is not a Snom M9 phone" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}

$ua = trim(@$_SERVER['HTTP_USER_AGENT']);
if (! preg_match('/^Mozilla/i', $ua)
||  ! preg_match('/snom\sm9/i', $ua) ) {
	gs_log( GS_LOG_WARNING, "Phone with MAC \"$mac\" (Snom) has invalid User-Agent (\"". $ua ."\")" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}
if (preg_match('/^Mozilla\/\d\.\d\s*\(compatible;\s*/i', $ua, $m)) {
	$ua = rTrim(subStr( $ua, strLen($m[0]) ), ' )');
}
gs_log( GS_LOG_DEBUG, "Snom model $ua found." );

if (preg_match('/snom\sm9/i', $ua, $m))
	$phone_model = 'm9';
else
	$phone_model = 'unknown';

$phone_type = 'snom-'.$phone_model;  # e.g. "snom-m9"
# to be used when auto-adding the phone

$fw_vers = (preg_match('/(\d+\.\d+\.\d+)/i', $ua, $m))
	? $m[0] : '0.0.0';
$fw_vers_nrml = _snom_normalize_version( $fw_vers );

gs_log( GS_LOG_DEBUG, "Snom phone \"$mac\" asks for settings (UA: ...\"$ua\") - model: $phone_model" );

require_once( GS_DIR .'inc/db_connect.php' );
require_once( GS_DIR .'inc/nobody-extensions.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callforward_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_prov_params_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_prov_params_get.php' );


$settings = array();

function setting( $name, $idx, $val, $attrs=null, $writeable=false )
{
	global $settings;
	
	if ($idx === null || $idx === false || $idx < 0) {
		$settings[$name] = array(
			'v' => $val,
			'w' => $writeable,
			'a' => $attrs
		);
	} else {
		if (! array_key_exists($name, $settings)
		||  ! is_array($settings[$name])) {
			$settings[$name] = array(
				'_is_array' => true
			);
		}
		$settings[$name][$idx] = array(
			'v' => $val,
			'w' => $writeable,
			'a' => $attrs
		);
	}
}

function psetting( $name, $val, $writeable=false )
{
	setting( $name, null, $val, null, $writeable );
}

function _settings_out()
{
	global $settings;
	
	header( 'Content-Type: application/xml; charset=utf-8' );
	echo '<','?xml version="1.0" encoding="utf-8"?','>', "\n";
	echo '<settings>' ,"\n";
	echo '<phone-settings>' ,"\n";
	foreach ($settings as $name => $a1) {
		if (! array_key_exists('_is_array', $a1)) {
			//echo '%',$name,'%:', str_replace("\0",'\\0', $a1['v']) ,"\n";
			echo '<',$name,' perm="'. ($a1['w'] ? 'RW':'R') .'">', $a1['v'] ,'</',$name,'>' ,"\n";
		} else {
			foreach ($a1 as $idx => $a2) {
				if ($idx === '_is_array') continue;
				echo '<',$name,' idx="'. $idx .'" perm="'. ($a2['w'] ? 'RW':'R') .'">', $a2['v'] ,'</',$name,'>' ,"\n";
			}
		}
	}
	echo '</phone-settings>' ,"\n";
	echo '</settings>' ,"\n";
}


# reset users array
$users = array();
for ($i=1; $i <= gs_get_conf('GS_SNOM_PROV_M9_ACCOUNTS'); ++$i) {
	$users[$i] = array();
	$users[$i]['name'        ] = '';
	$users[$i]['user'        ] = '';
	$users[$i]['ext'         ] = '';
	$users[$i]['mailbox'     ] = '';
	$users[$i]['secret'      ] = '';
	$users[$i]['host'        ] = '';
	$users[$i]['proxy'       ] = '';
	$users[$i]['port'        ] = 0;
	$users[$i]['id'          ] = null;
	$users[$i]['nobody_index'] = null;
}


$db = gs_db_master_connect();
if (! $db) {
	gs_log( GS_LOG_WARNING, "Snom M9 phone asks for settings - Could not connect to DB" );
	_settings_err( 'Could not connect to DB.' );
}


# main user

# do we know the phone?
#
$users[1]['id'] = (int)@gs_prov_user_id_by_mac_addr( $db, $mac );
if ($users[1]['id'] < 1) {
	if (! GS_PROV_AUTO_ADD_PHONE) {
		gs_log( GS_LOG_NOTICE, "New phone $mac not added to DB. Enable PROV_AUTO_ADD_PHONE" );
		_settings_err( 'Unknown phone. (Enable PROV_AUTO_ADD_PHONE in order to auto-add)' );
	}
	gs_log( GS_LOG_NOTICE, "Adding new Snom M9 phone $mac to DB" );

	$users[1]['id'] = (int)@gs_prov_add_phone_get_nobody_user_id( $db, $mac, $phone_type, $requester['phone_ip'] );
	if ($users[1]['id'] < 1) {
		gs_log( GS_LOG_WARNING, "Failed to add main nobody user for new phone $mac" );
		_settings_err( 'Failed to add main nobody user for new phone.' );
	}
}

for ($i=2; $i <= gs_get_conf('GS_SNOM_PROV_M9_ACCOUNTS'); ++$i)
{
	$users[$i]['id'] = (int)@gs_prov_user_id_by_mac_addr( $db, $mac.'-'.$i );
	
	if ($users[$i]['id'] < 1) {
		if (! GS_PROV_AUTO_ADD_PHONE) {
			gs_log( GS_LOG_NOTICE, "New phone $mac not added to DB. Enable PROV_AUTO_ADD_PHONE" );
			_settings_err( 'Unknown phone. (Enable PROV_AUTO_ADD_PHONE in order to auto-add)' );
		}
		gs_log( GS_LOG_NOTICE, 'Adding new virtual Snom M9 phone '.$mac.'-'.$i.' to DB' );
		
		$users[$i]['id'] = (int)@gs_prov_add_phone_get_nobody_user_id( $db, $mac.'-'.$i, $phone_type, $requester['phone_ip'] );
		if ($users[$i]['id'] < 1) {
			gs_log( GS_LOG_WARNING, "Failed to add nobody user for new phone $mac-$i" );
			_settings_err( 'Failed to add nobody user for new phone.' );
		}
	}
}

foreach ($users as $i => $user) {

	# create virtual mac address
	$mac_addr = ($i > 1) ? ($mac.'-'.$i) : $mac;
	
	# is it a valid user id?
	#
	if ($user['id'] > 0) {
		$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `users` WHERE `id`='. (int)$user['id'] );
	} else {
		$num = 0;
	}
	if ($num < 1) {
		$users[$i]['id'] = (int)@gs_prov_assign_default_nobody( $db, $mac_addr, null );
		if ($users[$i]['id'] < 1) {
			_settings_err( 'Failed to assign nobody account to phone '. $mac_addr );
		}
	}
	
	# who is logged in at that phone?
	#
	$user = @gs_prov_get_user_info( $db, $user['id'] );
	if (! is_array($users[$i])) {
		_settings_err( 'DB error.' );
	}
	
        $user['id'] = $users[$i]['id'];
        $users[$i]['name'        ] = $user['name'        ];
        $users[$i]['mailbox'     ] = $user['mailbox'     ];
        $users[$i]['secret'      ] = $user['secret'      ];
        $users[$i]['nobody_index'] = $user['nobody_index'];
        $users[$i]['user'        ] = $user['user'        ];
        $users[$i]['firstname'   ] = $user['firstname'   ];
        $users[$i]['lastname'    ] = $user['lastname'    ];
	
	# get host for user
	#
	$users[$i]['host'] = @gs_prov_get_host_for_user_id( $db, $user['id'] );
	if (! $users[$i]['host']) {
		_settings_err( 'Failed to find host.' );
	}
	$pbx = $users[$i]['host'];  # $host might be changed if SBC configured
	
	# store the current firmware version in the database:
	#
	@$db->execute(
		'UPDATE `phones` SET '.
			'`firmware_cur`=\''. $db->escape($fw_vers_nrml) .'\' '.
		'WHERE `mac_addr`=\''. $db->escape($mac_addr) .'\''
		);
	
	# store the user's current IP address in the database:
	#
	@$db->execute(
		'UPDATE `users` SET '.
			'`current_ip`=\''. $db->escape($requester['phone_ip']) .'\' '.
		'WHERE `id`=\''. (int)$user['id'] .'\''
		);
	
	# get SIP proxy to be set as the phone's outbound proxy
	#
	$sip_proxy_and_sbc = gs_prov_get_wan_outbound_proxy( $db, $requester['phone_ip'], $user['id'] );
	if ($sip_proxy_and_sbc['sip_server_from_wan'] != '') {
		$users[$i]['host'] = $sip_proxy_and_sbc['sip_server_from_wan'];
	}
	
	# get extension without route prefix
	#
	$hp_route_prefix = '';
	if (gs_get_conf('GS_BOI_ENABLED')) {
		$hp_route_prefix = (string)$db->executeGetOne(
			'SELECT `value` FROM `host_params` '.
			'WHERE '.
				'`host_id`='. (int)$user['host_id'] .' AND '.
				'`param`=\'route_prefix\''
			);
		$users[$i]['ext'] = ($hp_route_prefix)
			? subStr($users[$i]['name'], strLen($hp_route_prefix)) : $users[$i]['name'];
		gs_log( GS_LOG_DEBUG, "Mapping ext. ". $users[$i]['name'] ." to ".$users[$i]['ext']." for provisioning - route_prefix: $hp_route_prefix, host id: ". $users[$i]['host_id'] );
	} else {
		$users[$i]['ext'] = $user['name'];
	}
	
}



#####################################################################
# Network settings
#####################################################################
psetting('phone_name', 'snom-m9');
#psetting('assert_id', '');
psetting('dhcp', 'on');
#psetting('ntp_server', '');
psetting('vlan_id', '0');
psetting('vlan_priority', '0');
#psetting('provisioing_server', '');
psetting('settings_refresh_time', '0');
#psetting('sip_client_port', '');
psetting('rtp_type_of_service', '160');
psetting('sip_type_of_service', '160');
psetting('allow_check_sync', 'on');
psetting('stun_server', '');
psetting('stun_interval', '5');


#####################################################################
# Time and Language settings
#####################################################################
psetting('language', 'de');
psetting('tones', '3');
psetting('zone_id', '343',true);

# zone_id:
#343 = Deutschland


#####################################################################
# Security settings
#####################################################################
psetting('http_username', gs_get_conf('GS_SNOM_PROV_M9_HTTP_USER') );
psetting('http_password', gs_get_conf('GS_SNOM_PROV_M9_HTTP_PASS') );
psetting('base_pin', gs_get_conf('GS_SNOM_PROV_M9_BASE_PIN') );
psetting('session_timeout', '360');
psetting('http_port', '80');
psetting('https_port', '443');
psetting('http_client_user', '');
psetting('http_client_password', '');
psetting('http_proxy_user', '');
psetting('http_proxy_password', '');
psetting('emergency_proxy', '');
psetting('emergency_numbers', '');


#####################################################################
# USER settings
#####################################################################
for ($i=1; $i<10; ++$i)
	setting('user_active', $i, 'off' );	

foreach ($users as $i => $user) {
	setting('user_active', $i, 'on');
	setting('user_realname', $i, $user['ext'] . ' ' . mb_subStr($user['firstname'],0,1) .'. '. $user['lastname']);
	setting('user_name', $i, $user['ext']);
	setting('user_host', $i, $user['host']);
	setting('user_outbound', $i, '');
	setting('user_authname', $i, $user['ext']);
	setting('user_pass', $i, $user['secret']);
	setting('user_mailbox', $i, $user['mailbox']);
	setting('user_pbxtype', $i, 'asterisk');
	setting('rtp_encryption', $i, 'off');
	setting('user_country_code', $i, '');
	setting('area_code', $i, '');
	setting('user_expiry', $i, '3600');
	setting('user_sip_info', $i, '0');
	setting('conference_uri', $i, '');
	
	setting('codec1_name', $i, '8');
	setting('codec2_name', $i, '0');
	setting('codec3_name', $i, '3');
	setting('codec4_name', $i, '2');
	setting('codec5_name', $i, '9');
	setting('codec6_name', $i, '18');
	setting('codec7_name', $i, '4');
	setting('user_payload_type', $i, '96');
	setting('propose_length', $i, 'false');
	setting('packet_length', $i, '20');
	
	setting('user_dnd', $i, 'off');
	setting('user_forward_mode', $i, '0');
	setting('user_forward_number', $i, '');
	setting('user_forward_timeout', $i, '10');
	setting('user_ear_protection', $i, 'off');
	#setting('user_starcode_prefix', $i, '*90');  //TODO
	setting('user_transfer_on_hangup', $i, 'on');
	setting('allow_intercom_calls', $i, 'on');
}


foreach ($users as $i => $user) {
#####################################################################
#  Override provisioning parameters (group profile)
#####################################################################
	$param_count = 0;
	if (! $user['nobody_index']) {
		$prov_params = null;
		$GS_ProvParams = gs_get_prov_params_obj( $phone_type );
		
		if ($GS_ProvParams->set_user( $user['user'] )) {
			if ($GS_ProvParams->retrieve_params( $phone_type, array(
				'{GS_PROV_HOST}'      => gs_get_conf('GS_PROV_HOST'),
				'{GS_P_PBX}'          => $pbx,
				'{GS_P_EXTEN}'        => $user['ext'],
				'{GS_P_ROUTE_PREFIX}' => $hp_route_prefix,
				'{GS_P_USER}'         => $user['user']
			) )) {
				$prov_params = $GS_ProvParams->get_params();
			}
		}
		
		if (! is_array($prov_params)) {
			gs_log( GS_LOG_WARNING, 'Failed to get provisioning parameters (group)' );
		} else {
			$param_count = $param_count + count($prov_params);
			foreach ($prov_params as $param_name => $param_arr) {
			foreach ($param_arr as $param_index => $param_value) {
				if ($param_index == -1) {
					# not an array
					gs_log( GS_LOG_DEBUG, "Overriding group prov. param \"$param_name\": \"$param_value\"" );
					setting( $param_name, null        , $param_value );
				} else {
					# array
					gs_log( GS_LOG_DEBUG, "Overriding group prov. param \"$param_name\"[$param_index]: \"$param_value\"" );
					setting( $param_name, $param_index, $param_value );
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
			$param_count = $param_count + count($prov_params);
			foreach ($prov_params as $p) {
				if ($p['index'] === null
				||  $p['index'] ==  -1) {
					# not an array
					gs_log( GS_LOG_DEBUG, 'Overriding user prov. param "'.$p['param'].'": "'.$p['value'].'"' );
					setting( $p['param'], null       , $p['value'] );
				} else {
					# array
					gs_log( GS_LOG_DEBUG, 'Overriding user prov. param "'.$p['param'].'"['.$p['index'].']: "'.$p['value'].'"' );
					setting( $p['param'], $p['index'], $p['value'] );
				}
			}
		}
		unset($prov_params);
	}
	
	# ignore parameters of other users on the same gateway
	if ($param_count > 0) break;
}



#####################################################################
#  output
#####################################################################
ob_start();
_settings_out();
if (! headers_sent()) {
	# avoid chunked transfer-encoding
	header( 'Content-Length: '. (int)@ob_get_length() );
}
@ob_flush();

?>