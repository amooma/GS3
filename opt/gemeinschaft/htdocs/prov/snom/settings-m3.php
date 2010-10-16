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

# Some options may be obsolete but a good point to start reading:
# http://wiki.snom.com/Snom_m3/Configuration/Auto_Provisioning

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
	return $vmaj.'.'.$vmin;
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

if (gs_get_conf('GS_SNOM_PROV_M3_ACCOUNTS') < 1) {
	gs_log( GS_LOG_DEBUG, "Snom M3 provisioning not enabled" );
	_settings_err( 'Not enabled.' );
}



$requester = gs_prov_check_trust_requester();
if (! $requester['allowed']) {
	_settings_err( 'No! See log for details.' );
}

$mac = preg_replace( '/[^0-9A-F]/', '', strToUpper( @$_REQUEST['mac'] ) );
if (strLen($mac) !== 12) {
	gs_log( GS_LOG_NOTICE, "Snom M3 provisioning: Invalid MAC address \"$mac\" (wrong length)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}
if (hexDec(subStr($mac,0,2)) % 2 == 1) {
	gs_log( GS_LOG_NOTICE, "Snom M3 provisioning: Invalid MAC address \"$mac\" (multicast address)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}
if ($mac === '000000000000') {
	gs_log( GS_LOG_NOTICE, "Snom M3 provisioning: Invalid MAC address \"$mac\" (huh?)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}

# make sure the phone is a Snom-M3:
#
if ( (subStr($mac,0,6) !== '000413') && (subStr($mac,0,6) !== '00087B') ) {
	gs_log( GS_LOG_NOTICE, "Snom M3 provisioning: MAC address \"$mac\" is not a Snom M3 phone" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}

$ua = trim(@$_SERVER['HTTP_USER_AGENT']);
if (preg_match('/^Mozilla\/\d\.\d\s*\(compatible;\s*/i', $ua, $m)) {
	$ua = rTrim(subStr( $ua, strLen($m[0]) ), ' )');
}
gs_log( GS_LOG_DEBUG, "Snom model $ua found." );

if (preg_match('/snom-m3/i', $ua, $m))
	$phone_model = 'm3';
else
	$phone_model = 'unknown';

$phone_type = 'snom-'.$phone_model;  # e.g. "snom-m3"
# to be used when auto-adding the phone

$fw_vers = (preg_match('/snom-m3-SIP\/(\d+\.\d+)/', $ua, $m))
	? $m[1] : '0.0';
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

function psetting( $name, $val, $writeable=true )
{
	setting( $name, null, $val, null, $writeable );
}

function _settings_out()
{
	global $settings, $fw_vers_nrml;
	
	header( 'Content-Type: text/plain; charset=utf-8' );
	# the Content-Type header is ignored by the Snom M3
	
	foreach ($settings as $name => $a1) {
		if (! array_key_exists('_is_array', $a1)) {
			echo '%',$name,'%:', str_replace("\0",'\\0', $a1['v']) ,"\n";
		} else {
			foreach ($a1 as $idx => $a2) {
				if ($idx === '_is_array') continue;
				echo '%',$name,$idx,'%:', str_replace("\0",'\\0', $a2['v']) ,"\n";
			}
		}
	}
	unset($settings);
	echo 'END_OF_FILE', "\n";
}


# reset users array
$users = array();
for ($i=0; $i < gs_get_conf('GS_SNOM_PROV_M3_ACCOUNTS'); ++$i) {
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
	gs_log( GS_LOG_WARNING, "Snom M3 phone asks for settings - Could not connect to DB" );
	_settings_err( 'Could not connect to DB.' );
}

# do we know the phone?
#
$users[0]['id'] = (int)@gs_prov_user_id_by_mac_addr( $db, $mac );

# if found the first user try to find the rest
# if not add as many users as configured

if ($users[0]['id'] > 0) {
	for ($i=1; $i<8; ++$i) {
		$user_id = (int)@gs_prov_user_id_by_mac_addr( $db, $mac.'-'.($i+1) );
		if ($user_id > 0 ) $users[$i]['id'] = $user_id;
	}
} else {
	if (! GS_PROV_AUTO_ADD_PHONE) {
		gs_log( GS_LOG_NOTICE, "New Snom M3 phone $mac not added to DB. Enable PROV_AUTO_ADD_PHONE" );
		_settings_err( 'Unknown phone. (Enable PROV_AUTO_ADD_PHONE in order to auto-add)' );
	}
	gs_log( GS_LOG_NOTICE, "Adding new Snom M3 phone $mac to DB" );
	
	$users[0]['id'] = (int)@gs_prov_add_phone_get_nobody_user_id( $db, $mac, $phone_type, $requester['phone_ip'] );
	if ($users[0]['id'] < 1) {
		gs_log( GS_LOG_WARNING, "Failed to add main nobody user for new phone $mac" );
		_settings_err( 'Failed to add main nobody user for new phone.' );
	}
	else {
		for ($i=1; $i < gs_get_conf('GS_SNOM_PROV_M3_ACCOUNTS'); ++$i) {
			gs_log( GS_LOG_NOTICE, 'Adding new virtual Snom M3 phone '.$mac.'-'.($i+1).' to DB' );
			$users[$i]['id'] = (int)@gs_prov_add_phone_get_nobody_user_id( $db, $mac.'-'.($i+1), $phone_type, $requester['phone_ip'] );
			if ($users[$i]['id'] < 1) {
				gs_log( GS_LOG_WARNING, "Failed to add nobody user for new phone $mac-$i" );
				_settings_err( 'Failed to add nobody user for new Snom M3 phone' );
			}
		}
	}
}


foreach ($users as $i => $user) {
	
	# create virtual mac address
	$mac_addr = ($i > 0) ? ($mac.'-'.($i+1)) : $mac;
	
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
psetting('SIP_RPORT_ENABLE', 1);

psetting('SIP_STUN_ENABLE' , 0);
psetting('SIP_STUN_BINDTIME_GUARD'    , 80);
psetting('SIP_STUN_BINDTIME_DETERMINE', 0);
psetting('SIP_STUN_KEEP_ALIVE_TIME'   , 90);
//psetting('NETWORK_STUN_SERVER' ,'"stun01.STUNserver.com"');

psetting('NETWORK_DHCP_CLIENT_TIMEOUT'                    , 5);
psetting('NETWORK_DHCP_CLIENT_BOOT_SERVER'                , 3);
psetting('NETWORK_DHCP_CLIENT_BOOT_SERVER_OPTION'         , 160);
psetting('NETWORK_DHCP_CLIENT_BOOT_SERVER_OPTION_DATATYPE', 1);

//psetting('NETWORK_VLAN_ID'           ,    0);
//psetting('NETWORK_VLAN_USER_PRIORITY',    0);  # Prio. 5 (0|1-7)

#####################################################################
# Network Time
#####################################################################
psetting('NETWORK_SNTP_SERVER'            , '"ptbtime1.ptb.de"');
psetting('NETWORK_SNTP_SERVER_UPDATE_TIME', 255);
psetting('DAY_LIGHT_SAVING'               , 1);
psetting('GMT_TIME_ZONE'                  , 16);

#####################################################################
# Provisioning Server
#####################################################################
psetting('MANAGEMENT_TRANSFER_PROTOCOL', 1);  # 0=TFTP, 1=HTTP
psetting('NETWORK_TFTP_SERVER' , '"'.gs_get_conf('GS_PROV_HOST').'"');
psetting('NETWORK_FWU_SERVER'  , '"'.gs_get_conf('GS_PROV_HOST').'"');
psetting('FWU_TFTP_SERVER_PATH', '"m3/firmware/"');
psetting('VOIP_LOG_AUTO_UPLOAD', 0);

#####################################################################
# Access Settings
#####################################################################
psetting('PINCODE_PROTECTED_SETTINGS', 0);
psetting('VOIP_SETTINGS_PIN_CODE', '"0000"');
psetting('LOCAL_HTTP_SERVER_TEMPLATE_TITLE', ($hp_route_prefix) ? '" SNOM M3 ('.$hp_route_prefix.')"' : '"SNOM M3"' );
psetting('LOCAL_HTTP_SERVER_AUTH_NAME', '"' . gs_get_conf('GS_SNOM_PROV_M3_HTTP_USER') . '"');
psetting('LOCAL_HTTP_SERVER_AUTH_PASS', '"' . gs_get_conf('GS_SNOM_PROV_M3_HTTP_PASS') . '"');
psetting('LOCAL_HTTP_SERVER_ACCESS'   , '34815');

#####################################################################
# General options
#####################################################################
psetting('INFOPUSH_ICO_PRELOAD_URL', '""');
psetting('ENABLE_ENHANCED_IDLE_SCREEN', '0');
psetting('COMMON_PHONEBOOK', '0');

foreach ($users as $i => $user) {
	#####################################################################
	# SIP Server
	#####################################################################
	psetting('SRV_'.$i.'_SIP_UA_DATA_SERVER_PORT'    , $user['port']);
	psetting('SRV_'.$i.'_SIP_UA_DATA_DOMAIN'         , '"'.$user['host'].'"');
	psetting('SRV_'.$i.'_SIP_UA_DATA_PROXY_ADDR'     , '"'.$user['host'].'"');
	psetting('SRV_'.$i.'_SIP_UA_DATA_SERVER_IS_LOCAL', 1);
	psetting('SRV_'.$i.'_SIP_UA_DATA_REREG_TIME'     , 600);
	psetting('SRV_'.$i.'_SIP_UA_DATA_SERVER_TYPE'    , 1);
	
	psetting('SRV_'.$i.'_SIP_UA_CODEC_PRIORITY'      , '1,0,3,4,0xFF');
	psetting('SRV_'.$i.'_SIP_URI_DOMAIN_CONFIG'      , 0);
	psetting('SRV_'.$i.'_DTMF_SIGNALLING'            , 2);
	
	#####################################################################
	# SIP Registration
	#####################################################################
	psetting('SUBSCR_'.$i.'_SIP_UA_DATA_SIP_NAME'      , '"'.$user['ext'].'"');
	psetting('SUBSCR_'.$i.'_SIP_UA_DATA_SIP_NAME_ALIAS', '"'.$user['ext'].'"');
	psetting('SUBSCR_'.$i.'_UA_DATA_AUTH_NAME'         , '"'.$user['ext'].'"');
	psetting('SUBSCR_'.$i.'_UA_DATA_AUTH_PASS'         , '"'.$user['secret'].'"');
	psetting('SUBSCR_'.$i.'_SIP_UA_DATA_VOICE_MAILBOX_NUMBER','"'.$user['mailbox'].'"');
	psetting('SUBSCR_'.$i.'_SIP_UA_DATA_VOICE_MAILBOX_NAME'  ,'""');
	psetting('SUBSCR_'.$i.'_UA_DATA_DISP_NAME'         , '"'.$user['ext'].'"');

	#####################################################################
	# Handset name
	#####################################################################
	psetting('HANDSET_'.($i+1).'_NAME', '"' . $user['ext'] . ' ' . mb_subStr($user['firstname'],0,1) .'. '. $user['lastname'] . '"');
}

for ($i=1; $i<9; ++$i) {
	#####################################################################
	# Handset settings
	#####################################################################
	psetting('HANDSET_'.$i.'_CW'  , 0);
	psetting('HANDSET_'.$i.'_DND' , 0);
	
	#####################################################################
	# Handset to line mapping
	#####################################################################
	psetting('USER_VOIP_LINE_PP'.$i, $i);
	psetting('CALL_GROUPS'.$i      , pow(2,$i)+1 );
	
	#####################################################################
	# Feature codes
	#####################################################################
	setting('FWD_ON_BUSY_ACT_'       , $i, '""');
	setting('FWD_ON_BUSY_DEACT_'     , $i, '""');
	setting('FWD_ON_NO_ANSWER_ACT_'  , $i, '""');
	setting('FWD_ON_NO_ANSWER_DEACT_', $i, '""');
	setting('FWD_UNCOND_ACT_'        , $i, '"*2"');
	setting('FWD_UNCOND_DEACT_'      , $i, '"*2*"');
	
	psetting('COMMON_PHONEBOOK', '1');
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