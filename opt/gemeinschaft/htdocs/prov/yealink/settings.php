<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1 $
* 
* Copyright 2010, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Dirk Markwardt <dm@markwardt-software.de>
* Mirco Bartels
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

function _yealink_astlang_to_yealinklang($langcode)
{
	$lang_default = 'German';
	
	$lang_transtable = array(
		'de' => 'German',
		'en' => 'English',
		'us' => 'English',
	);
	
	$lang_ret = $lang_transtable[$langcode];
	if(strlen($lang_ret) == 0)
		return $lang_default;
	
	return $lang_ret;
}

function _yealink_normalize_version( $fwvers )
{
	$tmp = explode('.', $fwvers);
	$v0  = str_pad((int)@$tmp[0], 2, '0', STR_PAD_LEFT);
	$v1  = str_pad((int)@$tmp[1], 2, '0', STR_PAD_LEFT);
	$v2  = str_pad((int)@$tmp[2], 2, '0', STR_PAD_LEFT);
	$v3  = str_pad((int)@$tmp[3], 2, '0', STR_PAD_LEFT);
	return $v0.'.'.$v1.'.'.$v2.'.'.$v3;
}

function _yealink_fwcmp( $fwvers1, $fwvers2 )
{
	//$fwvers1 = _yealink_normalize_version( $fwvers1 );  # we trust it has been normalized!
	$fwvers2 = _yealink_normalize_version( $fwvers2 );
	return strCmp($fwvers1, $fwvers2);
}

function _settings_err( $msg='' )
{
	@ob_end_clean();
	@ob_start();
	echo '# ', ($msg != '' ? $msg : 'Error') ,"\n";
	gs_log( GS_LOG_DEBUG, $msg );	
	if (! headers_sent()) {
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'Content-Length: '. (int)@ob_get_length() );
	}
	@ob_end_flush();
	exit(1);
}

function _redirect_tiptel()
{
	$prov_url_tiptel =  GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH.'tiptel/'.$_REQUEST['mac'].'.cfg';
	
	gs_log( GS_LOG_DEBUG, 'Redirecting to Tiptel Provisoning: '.$prov_url_tiptel );

	$options  = array('http' => array('user_agent' => @$_SERVER['HTTP_USER_AGENT']));
	$context  = stream_context_create($options);
	$response = file_get_contents($prov_url_tiptel, false, $context);
	
	ob_start();
	echo $response;
	if (! headers_sent()) {
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'Content-Length: '. (int)@ob_get_length() );		
	}	
	@ob_end_flush();
	exit(1);
}

if (! gs_get_conf('GS_YEALINK_PROV_ENABLED')) {
	gs_log( GS_LOG_DEBUG, "Yealink provisioning not enabled" );
	_settings_err( 'Not enabled.' );
}

$requester = gs_prov_check_trust_requester();
if (! $requester['allowed']) {
	_settings_err( 'No! See log for details.' );
}

$mac = preg_replace( '/[^0-9A-F]/', '', strToUpper( @$_REQUEST['mac'] ) );
if (strLen($mac) !== 12) {
	gs_log( GS_LOG_NOTICE, "Yealink provisioning: Invalid MAC address \"$mac\" (wrong length)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}
if (hexDec(subStr($mac,0,2)) % 2 == 1) {
	gs_log( GS_LOG_NOTICE, "Yealink provisioning: Invalid MAC address \"$mac\" (multicast address)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}
if ($mac === '000000000000') {
	gs_log( GS_LOG_NOTICE, "Yealink provisioning: Invalid MAC address \"$mac\" (huh?)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}

# make sure the phone is a Yealink:
#
if (subStr($mac,0,6) !== '001565') {
	gs_log( GS_LOG_NOTICE, "Yealink provisioning: MAC address \"$mac\" is not a Yealink phone" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}

# HTTP_USER_AGENTs
# 
# yealink SIP-T46G: "Yealink SIP-T46G 28.72.0.25 00:15:65:5b:53:84"
#
$ua = trim( @$_SERVER['HTTP_USER_AGENT'] );
$ua_parts = explode(' ', $ua);
# redirect Tiptel phones to Tiptel provisioning
if (strToLower(@$ua_parts[0]) == 'tiptel') {
	gs_log( GS_LOG_WARNING, "Phone with MAC \"$mac\" (Tiptel) has invalid User-Agent (\"". $ua ."\"); Redirecting to Tiptel provisioning!" );

	_redirect_tiptel();
}
else if (strToLower(@$ua_parts[0]) !== 'yealink') {
	gs_log( GS_LOG_WARNING, "Phone with MAC \"$mac\" (Yealink) has invalid User-Agent (\"". $ua ."\")" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}

gs_log( GS_LOG_DEBUG, "Yealink model $ua found." );

# find out the type of the phone:
if (preg_match('/SIP-(T46G|T48G)/', @$ua_parts[1], $m))  {    # e.g. "SIP-T46G", "SIP-T48G" or "SIP-T22P"
	$phone_model =  'SIP-'.$m[1];
	$phone_model_config = 'SIP_'.$m[1];
}
else
	$phone_model = 'unknown';

$phone_type = 'yealink-'.strToLower($phone_model);  # e.g. "yealink-sip-t46g" or "yealink-sip-t48g"
# to be used when auto-adding the phone

# find out the firmware version of the phone
$fw_vers = (preg_match('/(\d+\.\d+\.\d+\.\d+)/', @$ua_parts[2], $m))
	? $m[1] : '0.0.0.0';

$fw_vers_nrml = _yealink_normalize_version( $fw_vers );

gs_log( GS_LOG_DEBUG, "Yealink phone \"$mac\" asks for settings (UA: ...\"$ua\") - model: $phone_model" );

$prov_url_yealink = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'yealink/';


require_once( GS_DIR .'inc/db_connect.php' );
require_once( GS_DIR .'inc/nobody-extensions.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callforward_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_keys_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_prov_params_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_prov_params_get.php' );
include_once( GS_DIR .'inc/cron-rule.php' );


$settings = array();

function psetting( $param, $val='' )
{
	global $settings;
	
	$settings[$param] = $val;
}

function _settings_out() //TODO Variablen löschen (warte auf RE von Yealink)
{
	global $settings;

	echo "#!version:1.0.0.1\n";

	foreach ($settings as $param => $val) {
		echo $param .' = '. $val . "\n";
	}
}


$db = gs_db_master_connect();
if (! $db) {
	gs_log( GS_LOG_WARNING, "Yealink phone asks for settings - Could not connect to DB" );
	_settings_err( 'Could not connect to DB.' );
}

# do we know the phone?
#
$user_id = @gs_prov_user_id_by_mac_addr( $db, $mac );
if ($user_id < 1) {
	if (! GS_PROV_AUTO_ADD_PHONE) {
		gs_log( GS_LOG_NOTICE, "New phone $mac not added to DB. Enable PROV_AUTO_ADD_PHONE" );
		_settings_err( 'Unknown phone. (Enable PROV_AUTO_ADD_PHONE in order to auto-add)' );
	}
	gs_log( GS_LOG_NOTICE, "Adding new Yealink phone $mac to DB" );
	
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


# store the current firmware version and type information in the database:
#
@$db->execute(
	'UPDATE `phones` SET '.
		'`firmware_cur`=\''. $db->escape($fw_vers_nrml) .'\', '.
		'`type`=\''. $db->escape($phone_type) .'\' '.
	'WHERE `mac_addr`=\''. $db->escape($mac) .'\''
	);


# firmware update
#
if (! gs_get_conf('GS_YEALINK_PROV_FW_UPDATE')) {
	gs_log( GS_LOG_DEBUG, 'Yealink firmware update not enabled' );
} else {
	# get phone_id
	$phone_id = (int)$db->executeGetOne( 'SELECT `id` FROM `phones` WHERE `mac_addr`=\''. $db->escape($mac) .'\'' );
	
	# do we have to update to a default version?
	#
	$fw_was_upgraded_manually = (int)$db->executeGetOne(
		'SELECT `fw_manual_update` '.
		'FROM `phones` '.
		'WHERE `id`='. $phone_id
		);
	if ($fw_was_upgraded_manually) {
		gs_log( GS_LOG_DEBUG, "Phone $mac: Firmware was upgraded \"manually\". Not scheduling an upgrade." );
	} else {
		$fw_default_vers = _yealink_normalize_version(trim(gs_get_conf('GS_YEALINK_PROV_FW_DEFAULT_'.strToUpper($phone_model_config))));
		if (in_array($fw_default_vers, array(null, false,''), true)) {
			gs_log( GS_LOG_DEBUG, "Phone $mac: No default firmware set in config file" );
		} elseif (subStr($fw_default_vers,0,2) === '00') {
			gs_log( GS_LOG_DEBUG, "Phone $mac: Bad default firmware set in config file: $fw_default_vers" );
		} else {
			if ($fw_vers_nrml != $fw_default_vers) {
				gs_log( GS_LOG_NOTICE, "Phone $mac: The Firmware version ($fw_vers_nrml) differs from the default version ($fw_default_vers), scheduling an upgrade ..." );
				# simply add a provisioning job to the database. This is done to be clean and we cann trace the job.
				$ok = $db->execute(
					'INSERT INTO `prov_jobs` ('.
						'`id`, '.
						'`inserted`, '.
						'`running`, '.
						'`trigger`, '.
						'`phone_id`, '.
						'`type`, '.
						'`immediate`, '.
						'`minute`, '.
						'`hour`, '.
						'`day`, '.
						'`month`, '.
						'`dow`, '.
						'`data` '.
					') VALUES ('.
						'NULL, '.
						((int)time()) .', '.
						'0, '.
						'\'client\', '.
						((int)$phone_id) .', '.
						'\'firmware\', '.
						'0, '.
						'\'*\', '.
						'\'*\', '.
						'\'*\', '.
						'\'*\', '.
						'\'*\', '.
						'\''. $db->escape($fw_default_vers) .'\''.
					')'
				);
			}
		}
	}
	
	# check provisioning jobs
	#
	$rs = $db->execute(
		'SELECT `id`, `running`, `minute`, `hour`, `day`, `month`, `dow`, `data` '.
		'FROM `prov_jobs` '.
		'WHERE `phone_id`='.$phone_id.' AND `type`=\'firmware\' '.
		'ORDER BY `running` DESC, `inserted`' );
	/*if (! $rs) {
		gs_log( GS_LOG_WARNING, "DB error" );
		return;
	}*/
	while ($job = $rs->fetchRow()) {
		if ($job['running']) {
			break;
		}
		
		# check cron rule
		$c = new CronRule();
		$ok = $c->set_Rule( $job['minute'] .' '. $job['hour'] .' '. $job['day'] .' '. $job['month'] .' '. $job['dow'] );
		if (! $ok) {
			gs_log( GS_LOG_WARNING, "Phone $mac: Job ".$job['id']." has a bad cron rule (". $c->errMsg ."). Deleting ..." );
			$db->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']).' AND `running`=0' );
			unset($c);
			continue;
		}
		if (! $c->validate_time()) {
			gs_log( GS_LOG_DEBUG, "Phone $mac: Job ".$job['id'].": Rule does not match" );
			unset($c);
			continue;
		}
		unset($c);
		gs_log( GS_LOG_DEBUG, "Phone $mac: Job ".$job['id'].": Rule match" );
		
		$fw_new_vers = _yealink_normalize_version( $job['data'] );
		if (subStr($fw_new_vers,0,2)=='00') {
			gs_log( GS_LOG_NOTICE, "Phone $mac: Bad new fw version $fw_new_vers" );
			$db->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']).' AND `running`=0' );
			continue;
		}
		if ( (subStr($fw_new_vers,0,2) != '18' && $phone_model === 'ip28xs')
		  || (subStr($fw_new_vers,0,2) != '09' && $phone_model === 'ip280')
		  || (subStr($fw_new_vers,0,2) != '06' && $phone_model === 'ip284')
		  || (subStr($fw_new_vers,0,2) != '02' && $phone_model === 'ip286') ) {
			gs_log( GS_LOG_NOTICE, "Phone $mac: Bad new fw version $fw_new_vers for $phone_model" );
			$db->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']).' AND `running`=0' );
			continue;
		}
		$firmware_path = '/opt/gemeinschaft/htdocs/prov/yealink/fw/'.$fw_new_vers.'.rom';
		if ( ! file_exists($firmware_path) || ! is_readable($firmware_path) ) {
			gs_log( GS_LOG_NOTICE, "Phone $mac: ".$firmware_path." not exits or not readable" );
			$db->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']).' AND `running`=0' );
			continue;
		}
		if ( $fw_new_vers == $fw_vers_nrml ) {
			gs_log( GS_LOG_NOTICE, "Phone $mac: FW $fw_vers_nrml == $fw_new_vers" );
			$db->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']).' AND `running`=0' );
			continue;
		}
		
		gs_log( GS_LOG_NOTICE, "Phone $mac: Upgrade FW $fw_vers_nrml -> $fw_new_vers" );
		
		# Configure
		###It configures the access URL of the firmware file.
		###The default value is blank.It takes effect after a reboot.
		psetting('firmware.url', $prov_url_yealink.'fw/'.$fw_new_vers.'.rom');
		
		break;
	}
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

# Phonetype Check
if ( in_array($phone_type, array('yealink-sip-t46g','yealink-sip-t48g'), true) ) {

	#####################################################################
	#  Common provisioning parameters (applicable to SIP-T28P/T26P/T22P/T20P/T21P/T19P/T46G/T42G/T41P IP phones running firmware version 72 or later)
	#####################################################################

	# Language
	##It configures the language of the web user interface.
	##Chinese_S is only applicable to SIP-T19P, SIP-T21P and SIP-T46G IP phones.
	##Chinese_T is only applicable to SIP-T46G IP phones.
	##French, Portuguese and Spanish are not applicable to SIP-T19P and SIP-T21P IP phones.
	psetting('lang.wui', 'English');
	##It configures the language of the phone user interface.
	##Chinese_S and Chinese_T are only applicable to SIP-T19P, SIP-T21P and SIP-T46G IP phones.
	##The default value is English.
	psetting('lang.gui', _yealink_astlang_to_yealinklang($user['language']));

	# Network LLDP enable (for identifying IP phone on LLDP-enabled switches)
	psetting('network.lldp.enable', '1');
	psetting('network.lldp.packet_interval', '60');

	# Remote Phonebook
	###X ranges from 1 to 5
	###remote_phonebook.data.X.url =   
	###remote_phonebook.data.X.name = 
	psetting('remote_phonebook.data.1.url', $prov_url_yealink.'pb.php?u='.$user_ext);
	psetting('remote_phonebook.data.1.name', 'Tel.buch');
	###Except T41P/T42G Models
	psetting('remote_phonebook.display_name', 'Tel.buch');

	# Show Remote Phonebook on Home Screen
	psetting('programablekey.2.type', '47');
	psetting('programablekey.2.line', '1');
	psetting('programablekey.2.value', '');
	psetting('programablekey.2.label', 'Tel.buch');

	##It enables or disables the phone to perform a remote phone book search when receiving an incoming call.
	##0-Disabled,1-Enabled.
	##The default value is 0.
	psetting('features.remote_phonebook.enable', '0');
	##It configures the interval (in seconds) for the phone to update the data of the remote phone book from the remote phone book server.
	##The default value is 21600.Integer from 3600 to 2592000.
	psetting('features.remote_phonebook.flash_time', '21600');

	# Ringtone
	# exp.: tftp://192.168.1.100/Ring9.wav
	# http://192.168.178.26/gemeinschaft/prov/ringtones/admin-int-fgz-tiptel.wav
	#psetting('ringtone.url', 'http://192.168.178.26/gemeinschaft/prov/ringtones/admin-int-fgz-tiptel.wav');
	#psetting('ringtone.delete', '');
	#Delete all the custom ringtones uploaded through auto provisioning
	#psetting('ringtone.delete', 'http://localhost/all');

	# Country Tone
	# Custom,Australia,Austria, Brazil,Belgium,China, Czech,Denmark,Finland,France,Germany,Great Britain,Greece,Hungary,Lithuania,India, Italy,Japan,Mexico, New Zealand,
	# Netherlands,Norway,Portugal,Spain,Switzerland,Sweden,Russia, UnitedStates, Chile,Czech ETSI
	psetting('voice.tone.country', 'Germany');

	# DND
	psetting('features.dnd.enable', '0');
	psetting('features.dnd.on_code', 'dnd-on');
	psetting('features.dnd.off_code', 'dnd-off');
	
	# Display own Extension
	psetting('features.show_default_account', '1');
	
	# Security
	###Define the login username and password of the user, var and administrator.
	###If you change the username of the administrator from "admin" to "admin1", your new administrator's username should be configured as: security.user_name.admin = admin1.
	###If you change the password of the administrator from "admin" to "admin1pwd", your new administrator's password should be configured as: security.user_password = admin1:admin1pwd.
	###The following examples change the user's username to "user23" and the user's password to "user23pwd".
	###security.user_name.user = user23
	###security.user_password = user23:user23pwd
	###The following examples change the var's username to "var55" and the var's password to "var55pwd".
	###security.user_name.var = var55
	###security.user_password = var55:var55pwd
	#psetting('security.user_name.user', '');
	#psetting('security.user_name.admin', '');
	#psetting('security.user_name.var', '');
	psetting('security.user_password', 'admin:'.gs_get_conf('GS_YEALINK_PROV_HTTP_PASS'));
		

	#####################################################################
	#  MAC-specific provisioning parameters (applicable to SIP-T28P/T26P/T22P/T20P/T21P/T19P/T46G/T42G/T41P IP phones running firmware version 72 or later)
	#####################################################################
	# Account1 Basic Settings 
	psetting('account.1.enable', '1'); # 0 = disable, 1 = enable
	psetting('account.1.label', $user_ext .' '. mb_subStr($user['firstname'],0,1) .'. '. $user['lastname']);
	psetting('account.1.display_name', $user['callerid']);
	psetting('account.1.auth_name', $user_ext);
	psetting('account.1.user_name', $user_ext);
	psetting('account.1.password', $user['secret']);
	psetting('account.1.outbound_proxy_enable', '0'); # 0 = disable, 1 = enable
	psetting('account.1.outbound_host', '');
	psetting('account.1.outbound_port', '5061');
	##It configures the local SIP port for account 1. The default value is 5060.
	psetting('account.1.sip_listen_port', '5060');
	##It configures the transport type for account 1. 0-UDP,1-TCP,2-TLS,3-DNS-NAPTR
	psetting('account.1.transport', '0'); ##The default value is 0.

	// # Failback
	psetting('account.1.sip_server.1.address', $host);
	psetting('account.1.sip_server.1.port', '5060');
	psetting('account.1.sip_server.1.expires', '120');

	// # Register Advanced
	// ##It configures the SIP server type for account X.0-Default,2-BroadSoft,4-Cosmocom,6-UCAP
	// ##The default value is 0.
	psetting('account.1.sip_server_type', '0');

	psetting('voice_mail.number.1', 'voicemail');
	psetting('account.1.subscribe_mwi', '1');
    psetting('account.1.display_mwi.enable', '1');
	psetting('account.1.subscribe_mwi_to_vm', '1');

	# Codecs
	psetting('account.1.codec.1.enable', '1');
	psetting('account.1.codec.2.enable', '1');
	psetting('account.1.codec.3.enable', '1');
	psetting('account.1.codec.4.enable', '0');
	psetting('account.1.codec.5.enable', '0');
	psetting('account.1.codec.6.enable', '0');
	psetting('account.1.codec.7.enable', '0');
	psetting('account.1.codec.8.enable', '0');
	psetting('account.1.codec.9.enable', '0');
	psetting('account.1.codec.10.enable', '0');
	psetting('account.1.codec.11.enable', '0');
	
	psetting('account.1.codec.1.payload_type', 'PCMA');
	psetting('account.1.codec.2.payload_type', 'PCMU');
	psetting('account.1.codec.3.payload_type', 'G722');
	psetting('account.1.codec.4.payload_type', 'G723_53');
	psetting('account.1.codec.5.payload_type', 'G723_63');
	psetting('account.1.codec.6.payload_type', 'G729');
	psetting('account.1.codec.7.payload_type', 'iLBC');
	psetting('account.1.codec.8.payload_type', 'G726-16');
	psetting('account.1.codec.9.payload_type', 'G726-24');
	psetting('account.1.codec.10.payload_type', 'G726-32');
	psetting('account.1.codec.11.payload_type', 'G726-40');	
	
	psetting('account.1.codec.1.priority', '1');
	psetting('account.1.codec.2.priority', '2');
	psetting('account.1.codec.3.priority', '3');
	psetting('account.1.codec.4.priority', '0');
	psetting('account.1.codec.5.priority', '0');
	psetting('account.1.codec.6.priority', '0');
	psetting('account.1.codec.7.priority', '0');
	psetting('account.1.codec.8.priority', '0');
	psetting('account.1.codec.9.priority', '0');
	psetting('account.1.codec.10.priority', '0');
	psetting('account.1.codec.11.priority', '0');
	
	psetting('account.1.codec.1.rtpmap', '8'); # PCMA
	psetting('account.1.codec.2.rtpmap', '0'); # PCMU
	psetting('account.1.codec.3.rtpmap', '9'); # G722
	psetting('account.1.codec.4.rtpmap', '4'); # G723_53
	psetting('account.1.codec.5.rtpmap', '4'); # G723_63
	psetting('account.1.codec.6.rtpmap', '18'); # G729
	psetting('account.1.codec.7.rtpmap', '106'); # iLBC
	psetting('account.1.codec.8.rtpmap', '103'); # G726-16
	psetting('account.1.codec.9.rtpmap', '104'); # G726-24
	psetting('account.1.codec.10.rtpmap', '102'); # G726-32
	psetting('account.1.codec.11.rtpmap', '105'); # G726-40
	
	psetting('account.1.ptime', '20');      # 20ms
	
	// psetting('account.1.unregister_on_reboot', '');
	// psetting('account.1.sip_trust_ctrl', '');
	// psetting('account.1.proxy_require', '');
	// psetting('account.1.srv_ttl_timer_enable', '');
	// psetting('account.1.register_mac', '1'); # 0 = disable, 1 = enable
	// psetting('account.1.register_line', '1'); # 0 = disable, 1 = enable
	// psetting('account.1.reg_fail_retry_interval', '30'); # 0 to 1800, default 30

	// # NAT
	// ##It enables or disables the NAT traversal for account X.0-Disabled,1-Enabled
	// ##The default value is 0.
	// psetting('account.1.nat.nat_traversal', '');
	// ##It configures the IP address or domain name of the STUN server for account X.
	// ##The default value is blank.
	// psetting('account.1.nat.stun_server', '');
	// ##It configures the port of the STUN server for account X.
	// ##The default value is 3478.
	// psetting('account.1.nat.stun_port', '');
	// ##It configures the type of keep-alive packets sent by the phone to the NAT device to keep the communication port open so that NAT can continue to function for account X.
	// ##The default value is 1.
	// psetting('account.1.nat.udp_update_enable', '');
	// ##It configures the keep-alive interval (in seconds) for account X.
	// ##The default value is 30.Integer from 15 to 2147483647
	// psetting('account.1.nat.udp_update_time', '');
	// ##It enables or disables NAT Rport feature for account X.0-Disabled,1-Enabled
	// ##The default value is 0.
	// psetting('account.1.nat.rport', '');

	// # Pickup
	// ##It enables or disables the phone to pick up a call according to the SIP header of dialog-info for account X
	// ##0-Disabled,1-Enabled.
	// ##The default value is 0.
	// Must be DISABLED to work on Asterisk PBX
	psetting('account.1.dialoginfo_callpickup', '0');
	// ##It configures the group pickup code for account X.
	// ##The default value is blank.
	psetting('account.1.group_pickup_code', '');
	// ##It configures the directed pickup code for account X.
	// ##The default value is blank.
	psetting('account.1.direct_pickup_code', '*81*');

	# Time
	##It configures the time zone.For more available time zones, refer to Time Zones on page 215.
	##The default value is +8.
	psetting('local_time.time_zone', '+1');
	psetting('local_time.time_zone_name', 'Germany(Berlin)');
	##It configures the time zone name.For more available time zone names, refer to Time Zones on page 215.
	##The default time zone name is China(Beijing).
	psetting('local_time.ntp_server1', gs_get_conf('GS_YEALINK_PROV_NTP'));
	psetting('local_time.ntp_server2', gs_get_conf('GS_YEALINK_PROV_NTP'));     # override default chinese NTP server
	## NTP setting fron DHCP has high priority
	psetting('local_time.manual_ntp_srv_prior','0');
	psetting('local_time.interval'  , strval(rand(980,1020)));  # default 1000
	psetting('local_time.time_format', '1');
	psetting('local_time.date_format', '5');    # 0-WWW MMM DD; 1-DD-MMM-YY; 2-YYYY-MM-DD; 3-DD/MM/YYYY; 4-MM/DD/YY; 5-DD MMM YYYY; 6-WWW DD MMM
	
	#######################################################################################
	##                                   Features Pickup(Except T20P model)              ##       
	#######################################################################################
	##It enables or disables the phone to display the GPickup soft key when the phone is in the pre-dialing screen.
	##0-Disabled,1-Enabled.
	##The default value is 0.
	psetting('features.pickup.group_pickup_enable', '0');

	##It configures the group call pickup code.
	##The default value is blank.
	psetting('features.pickup.group_pickup_code', '');

	##It enables or disables the phone to display the DPickup soft key when the phone is in the pre-dialing screen.
	##0-Disabled,1-Enabled.
	##The default value is 0.
	psetting('features.pickup.direct_pickup_enable', '1');

	##It configures the directed call pickup code.
	##The default value is blank.
	psetting('features.pickup.direct_pickup_code', '*81*');

	##It enables or disables the phone to display a visual alert when the monitored user receives an incoming call.
	##0-Disabled,1-Enabled.
	##The default value is 0.
	psetting('features.pickup.blf_visual_enable', '1');

	##It enables or disables the phone to play an audio alert when the monitored user receives an incoming call.
	##0-Disabled,1-Enabled.
	##The default value is 0.
	psetting('features.pickup.blf_audio_enable', '0');

	#####################################################################
	#  Keys
	#####################################################################

	$max_keys = 10;

	# RESET KEYS
	for ($i=1; $i <= $max_keys; $i++) {
		psetting('linekey.'.$i.'.line', '0');
		psetting('linekey.'.$i.'.value', '');
		psetting('linekey.'.$i.'.pickup_value', '');
		psetting('linekey.'.$i.'.extension', '');
		psetting('linekey.'.$i.'.type', '0');
		psetting('linekey.'.$i.'.xml_phonebook', '0');
		psetting('linekey.'.$i.'.label', '');
	}

	# TODO: RESET PROGRAMMABLE KEYS?
	# TODO: RESET EXP KEYS?

	$softkeys = null;
	$GS_Softkeys = gs_get_key_prov_obj( $phone_type );
	if ($GS_Softkeys->set_user( $user['user'] )) {
		if ($GS_Softkeys->retrieve_keys( $phone_type, array(
			'{GS_PROV_HOST}'      => gs_get_conf('GS_PROV_HOST'),
			'{GS_P_PBX}'          => $pbx,
			'{GS_P_EXTEN}'        => $user_ext,
			'{GS_P_ROUTE_PREFIX}' => $hp_route_prefix,
			'{GS_P_USER}'         => $user['user']
		) )) {
			$softkeys = $GS_Softkeys->get_keys();
		}
	}
	if (! is_array($softkeys)) {
		gs_log( GS_LOG_WARNING, 'Failed to get softkeys' );
	} else {
		gs_log( GS_LOG_DEBUG, 'Num Softkeys to set: '.count($softkeys) );

		foreach ($softkeys as $key_name => $key_defs) {
			if (array_key_exists('slf', $key_defs)) {
				$key_def = $key_defs['slf'];
			} elseif (array_key_exists('inh', $key_defs)) {
				$key_def = $key_defs['inh'];
			} else {
				continue;
			}
			
			$key_idx = (int)lTrim(subStr($key_name,1),'0');
			if ($key_def['function'] === 'f0') continue;

			#######################################################################################
			##                                   Line Keys                                       ##       
			#######################################################################################	
			if ($key_idx >= 1 && $key_idx <= $max_keys ) {
				gs_log( GS_LOG_DEBUG, 'Set LineKey('.$key_idx.') value=\''.$key_def['data'].'\' type=\''.subStr($key_def['function'],1).'\' label=\''.$key_def['label'].'\'' );

				###It configures the desired line to apply the key feature.Integer from 1 to 6
				psetting('linekey.'.$key_idx.'.line', 1);
				###It configures the value of the line key feature.
				###For example, when setting the key feature to BLF, it configures the number of the monitored user.
				###The default value is blank.
				psetting('linekey.'.$key_idx.'.value', $key_def['data']);
				# for BLF
				if (subStr($key_def['function'],1) == 16 ) {
					###It configures the pickup code for BLF feature or conference ID followed by the # sign for Meet-Me conference feature.
					###It only applies to BLF and Meet-Me conference features.
					###The default value is blank
					psetting('linekey.'.$key_idx.'.pickup_value', '*81*');
				}
				###It configures the key feature for the line key X.
				#The valid types are: 
				#0-NA 1-Conference 2-Forward 3-Transfer 4-Hold 5-DND 7-Call Return 8-SMS 9-Directed Pickup   
				#10-Call Park 11-DTMF 12-Voice Mail 13-Speed Dial 14-Intercom 15-Line 16-BLF 17-URL 18-Group Listening  
				#20-Private Hold 22-XML Group 23-Group Pickup 24-Multicast Paging 25-Record 27-XML Browser
				#34-Hot Desking 35-URL Record 38-LDAP 39-BLF List   
				#40-Prefix 41-Zero Touch 42-ACD 45-Local Group 46-Network Group 49-Custom Button   
				#50-Keypad Lock 55-Meet-Me Conference 56-Retrieve Park 57-Hoteling 58-ACD Grace 59-Sisp Code   
				#60-Emergency 61-Directory
				#----
				#0-NA £¨Only for T41/T42/T46)
				#22-XML Group (Not support T20)
				#38-LDAP (Not support T20)
				#46-Network Group (Not support T20)
				#8-SMS (Only support T21/T46/T22/T26/T28)
				#17-URL (Only support T41/T42/T46)
				#49-Custom Button (Only support T20/T22/T26/T28)
				psetting('linekey.'.$key_idx.'.type', subStr($key_def['function'],1));
				###It configures the desired local group/XML group/network group for the line key X.
				###It only applies to the Local Group, XML Group and Network Group features.
				###XML Group and Network Group features are not applicable to SIP-T20P IP phones.
				// psetting('linekey.'.$key_idx.'.xml_phonebook', '0');
				###It configures the label displayed on the LCD screen for each line key.
				###The default value is blank.
				psetting('linekey.'.$key_idx.'.label', $key_def['label']);
			}
			
			# TODO: Programmable Keys
			# TODO: Keys on Expansion Modul
		}
	}	
}


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

gs_log( GS_LOG_DEBUG, "Retrieved ".count($prov_params)." Group-Profile parameters for phone_type: ".$phone_type );

if (! is_array($prov_params)) {
	gs_log( GS_LOG_WARNING, 'Failed to get provisioning parameters (group)' );
} else {
	foreach ($prov_params as $param_name => $param_arr) {
	foreach ($param_arr as $param_index => $param_value) {
		if ($param_index == -1) {
			# not an array
			if (! array_key_exists($param_name, $settings)) {
				# new parameter
				gs_log( GS_LOG_DEBUG, "Group prov. param \"$param_name\": \"$param_value\"" );
			} else {
				# override parameter
				gs_log( GS_LOG_DEBUG, "Overriding group prov. param \"$param_name\": \"$param_value\"" );
			}
			
			psetting($param_name, $param_value);
		} else {
			# array
			gs_log( GS_LOG_NOTICE, "Group prov. param \"$param_name\"[$param_index]: Yealink does not support arrays" );			
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

gs_log( GS_LOG_DEBUG, "Retrieved ".count($prov_params)." User-Profile parameters for phone_type: ".$phone_type.", user: ".$user['user'] );

if (! is_array($prov_params)) {
	gs_log( GS_LOG_WARNING, 'Failed to get provisioning parameters (user)' );
} else {
	foreach ($prov_params as $p) {
		if ($p['index'] === null
		||  $p['index'] ==  -1) {
			# not an array
			if (! array_key_exists($p['param'], $settings)) {
				# new parameter
				gs_log( GS_LOG_DEBUG, 'User prov. param "'.$p['param'].'": "'.$p['value'].'"' );
			} else {
				# override parameter
				gs_log( GS_LOG_DEBUG, 'Overriding user prov. param "'.$p['param'].'": "'.$p['value'].'"' );
			}
			
			psetting($p['param'], $p['value']);
		} else {
			# array
			gs_log( GS_LOG_NOTICE, 'User prov. param "'.$p['param'].'"['.$p['index'].']: Yealink does not support arrays"' );			
		}
	}
}
unset($prov_params);


#####################################################################
#  output
#####################################################################
ob_start();
_settings_out();
if (! headers_sent()) {
	header( 'Content-Type: text/plain; charset=utf-8' );
	# avoid chunked transfer-encoding
	header( 'Content-Length: '. (int)@ob_get_length() );
}
@ob_flush();

?>
