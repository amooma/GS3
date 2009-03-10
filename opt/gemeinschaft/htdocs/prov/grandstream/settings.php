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
* Philipp Kempgen <philipp.kempgen@amooma.de>
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

# see
# http://www.grandstream.com/provisioningscenarios.html

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

function _grandstream_normalize_version( $appvers )
{
	$tmp = explode('.', $appvers);
	$v0  = str_pad((int)@$tmp[0], 2, '0', STR_PAD_LEFT);
	$v1  = str_pad((int)@$tmp[1], 2, '0', STR_PAD_LEFT);
	$v2  = str_pad((int)@$tmp[2], 2, '0', STR_PAD_LEFT);
	$v3  = str_pad((int)@$tmp[3], 2, '0', STR_PAD_LEFT);
	return $v0.'.'.$v1.'.'.$v2.'.'.$v3;
}

function _settings_err( $msg='' )
{
	@ob_end_clean();
	@ob_start();
	echo '# ', ($msg != '' ? $msg : 'Error') ,"\n";
	if (! headers_sent()) {
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Length: '. (int)@ob_get_length() );
	}
	@ob_end_flush();
	exit(1);
}

if (! gs_get_conf('GS_GRANDSTREAM_PROV_ENABLED')) {
	gs_log( GS_LOG_DEBUG, "Grandstream provisioning not enabled" );
	_settings_err( 'Not enabled.' );
}


$requester = gs_prov_check_trust_requester();
if (! $requester['allowed']) {
	_settings_err( 'No! See log for details.' );
}

$mac = preg_replace( '/[^0-9A-F]/', '', strToUpper( @$_REQUEST['mac'] ) );
if (strLen($mac) !== 12) {
	gs_log( GS_LOG_NOTICE, "Grandstream provisioning: Invalid MAC address \"$mac\" (wrong length)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}
if (hexDec(subStr($mac,0,2)) % 2 == 1) {
	gs_log( GS_LOG_NOTICE, "Grandstream provisioning: Invalid MAC address \"$mac\" (multicast address)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}
if ($mac === '000000000000') {
	gs_log( GS_LOG_NOTICE, "Grandstream provisioning: Invalid MAC address \"$mac\" (huh?)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}

# make sure the phone is a Grandstream:
#
if (subStr($mac,0,6) !== '000B82') {
	gs_log( GS_LOG_NOTICE, "Grandstream provisioning: MAC address \"$mac\" is not a Grandstream phone" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}

$ua = trim( @$_SERVER['HTTP_USER_AGENT'] );
$ua_parts = explode(' ', $ua);
if (strToLower(@$ua_parts[0]) !== 'grandstream') {
	gs_log( GS_LOG_WARNING, "Phone with MAC \"$mac\" (Grandstream) has invalid User-Agent (\"". $ua ."\")" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}
gs_log( GS_LOG_DEBUG, "Grandstream model $ua found." );

# find out the type of the phone:
if (preg_match('/(bt|gxp|gxv)[0-9]{1,6}/', strToLower(@$ua_parts[1]), $m))  # e.g. "bt110" or "gxp2020"
	$phone_model = $m[0];
else
	$phone_model = 'unknown';

$phone_type = 'grandstream-'.$phone_model;  # e.g. "grandstream-bt110" or "grandstream-gxp2020"
# to be used when auto-adding the phone

# find out the firmware version of the phone
$fw_vers = (preg_match('/(\d+\.\d+\.\d+\.\d+)/', @$ua_parts[2], $m))
	? $m[1] : '0.0.0.0';
$fw_vers_nrml = _grandstream_normalize_version( $fw_vers );

gs_log( GS_LOG_DEBUG, "Grandstream phone \"$mac\" asks for settings (UA: ...\"$ua\") - model: $phone_model" );

$prov_url_grandstream = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'grandstream/';


require_once( GS_DIR .'inc/db_connect.php' );
require_once( GS_DIR .'inc/nobody-extensions.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callforward_get.php' );
//include_once( GS_DIR .'inc/gs-fns/gs_keys_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_prov_params_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_prov_params_get.php' );


$settings = array();

function psetting( $name, $val='' )
{
	global $settings;
	if (! preg_match('/^P[0-9]{1,4}$/S', $name)) continue;
	$settings[$name] = $val;
}

function _settings_out()
{
	global $settings;
	$data = '';
	foreach ($settings as $name => $val) {
		$val = str_replace(array('&','='), array('',''), $val);
		$data .= $name .'='. $val .'&';
	}
	$data .= 'gnkey=0b82';
	return $data;
}

function checksum( $str )
{
	$sum = 0;
	for ($i=0; $i <= ((strlen($str) - 1) / 2); $i++) {
		$sum += ord(substr($str, (2*$i)   , 1)) << 8;
		$sum += ord(substr($str, (2*$i)+1 , 1));
		$sum &= 0xffff;
	}
	$sum = 0x10000 - $sum;
	return array(($sum >> 8) & 0xff, $sum & 0xff);
}


$db = gs_db_master_connect();
if (! $db) {
	gs_log( GS_LOG_WARNING, "Grandstream phone asks for settings - Could not connect to DB" );
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
	gs_log( GS_LOG_NOTICE, "Adding new Grandstream phone $mac to DB" );
	
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
@$db->execute(
	'UPDATE `phones` SET '.
		'`firmware_cur`=\''. $db->escape($fw_vers_nrml) .'\' '.
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




#####################################################################
#  Passwords
#####################################################################
psetting('P2', gs_get_conf('GS_GRANDSTREAM_PROV_HTTP_PASS'));	# Admin Password
psetting('P196', '');		# End User Password


#####################################################################
#  Network
#####################################################################
psetting('P8' , '0');		# IP Address Type ( 0 = DHCP, 1 = static)

# DHCP
psetting('P82', '');		# PPPoE Account ID
psetting('P83', '');		# PPPoE Password
psetting('P146', 'grandstream-'.$mac );  # DHCP hostname
psetting('P147', '');		# DHCP domain
psetting('P148', '');		# DHCP vendor class ID
psetting('P92', '');		# Preferred DNS server (octet 0)
psetting('P93', '');		# Preferred DNS server (octet 1)
psetting('P94', '');		# Preferred DNS server (octet 2)
psetting('P95', '');		# Preferred DNS server (octet 3)

# static
psetting('P9' , '');		# IP Address (octet 0)
psetting('P10', '');		# IP Address (octet 1)
psetting('P11', '');		# IP Address (octet 2)
psetting('P12', '');		# IP Address (octet 3)
psetting('P13', '');		# Subnet Mask (octet 0)
psetting('P14', '');		# Subnet Mask (octet 1)
psetting('P15', '');		# Subnet Mask (octet 2)
psetting('P16', '');		# Subnet Mask (octet 3)
psetting('P17', '');		# Default Router (octet 0)
psetting('P18', '');		# Default Router (octet 1)
psetting('P19', '');		# Default Router (octet 2)
psetting('P20', '');		# Default Router (octet 3)
psetting('P21', '');		# DNS Server 1 (octet 0)
psetting('P22', '');		# DNS Server 1 (octet 1)
psetting('P23', '');		# DNS Server 1 (octet 2)
psetting('P24', '');		# DNS Server 1 (octet 3)
psetting('P25', '');		# DNS Server 2 (octet 0)
psetting('P26', '');		# DNS Server 2 (octet 1)
psetting('P27', '');		# DNS Server 2 (octet 2)
psetting('P28', '');		# DNS Server 2 (octet 3)

/*
psetting('P41', '');		# TFTP server IP address (octet 0)
psetting('P42', '');		# TFTP server IP address (octet 1)
psetting('P43', '');		# TFTP server IP address (octet 2)
psetting('P44', '');		# TFTP server IP address (octet 3)
*/

# QoS
psetting('P38', '48');		# Layer 3 QoS
psetting('P51', '0');		# Layer 2 QoS: 802.1q VLAN Tag ( maxlength 5 )
psetting('P87', '0');		# Layer 2 QoS: 802.1p priority value ( maxlength 5 )


#####################################################################
#  Date and Time
#####################################################################
psetting('P64',  (720 + (int)(((int)date('Z')) / 60)) );  # Timezone Offset ( Offset from GMT in minutes + 720 )
psetting('P75',  date('I'));	# Daylight Saving Time ( 0 = no, 1 = yes )
psetting('P102', '2');			# Date Display Format ( 0 = Y-M-D, 1 = M-D-Y, 2 = D-M-Y )
psetting('P30',  gs_get_conf('GS_GRANDSTREAM_PROV_NTP'));  # NTP Server
if (subStr($phone_model,0,3) === 'gxp') {
	psetting('P143', '1');		# Allow DHCP Option 2 to override Timezone setting ( 0 = no, 1 = yes )
	psetting('P144', '1');		# Allow DHCP Option 42 to override NTP server
	psetting('P246', '3,2,7,2,0;11,1,7,2,0;60');	# Daylight Saving Time Optional Rule ( maxlength 33 ) (//FIXME)
}


#####################################################################
#  LCD Display
#####################################################################
if (subStr($phone_model,0,3) === 'gxp') {
	psetting('P322', '0');		# LCD Backlight Always on (0 = no, 1 = yes)
	psetting('P1329', '12');	# LCD Contrast
	psetting('P122', '1');		# Time Display Format (0 = 12h, 1 = 24h)
	psetting('P123', '0');		# Display Clock instead of Date (0 = yes, 1 = no) ???
	psetting('P338', '0');		# Disable in-call DTMF display ( 0 = no, 1 = yes )
	psetting('P351', '0');		# Disable Missed Call Backlight ( 0 = no, 1 = yes)	
}


#####################################################################
#  Ringtones
#####################################################################
psetting('P104', '0');		# Default ring tone
psetting('P105', '');		# Custom ringtone 1, used if incoming caller ID is: ""
psetting('P106', '');		# Custom ringtone 2, used if incoming caller ID is: ""
psetting('P107', '');		# Custom ringtone 3, used if incoming caller ID is: ""
if (subStr($phone_model,0,3) === 'gxp') {
	psetting('P345', '3,2,7,2,0;11,1,7,2,0;60');	# System ringtone ( maxlength 64 )
}


#####################################################################
#  Call Progress Tones (//FIXME todo)
#####################################################################
/*
if (subStr($phone_model,0,3) === 'gxp') {
	psetting('P343', 'f1=350,f2=440;');				# Dial Tone
	psetting('P344', 'f1=350,f2=440,c=10/10;');		# Message Waiting
	psetting('P346', 'f1=440,f2=480,c=200/400;');	# Ring Back Tone
	psetting('P347', 'f1=440,f2=440,c=25/525;');	# Call-Waiting Tone
	psetting('P348', 'f1=480,f2=620,c=50/50;');		# Busy Tone
	psetting('P349', 'f1=480,f2=620,c=25/25;');		# Reorder Tone
}
*/


#####################################################################
#  Firmware Upgrade and Provisioning
#####################################################################
psetting('P212', '1');			# Upgrade via ( 0 = TFTP, 1 = HTTP )
psetting('P192', rTrim($prov_url_grandstream,'/'));  # TFTP/HTTP Firmware Update Server ( based on P212 ) //FIXME?
psetting('P237', rTrim($prov_url_grandstream,'/'));  # TFTP/HTTP Config Server ( based on P212 )
psetting('P232', '');			# Firmware File Prefix
psetting('P233', '');			# Firmware File Suffix
psetting('P234', '');			# Config File Prefix
psetting('P235', '');			# Config File Suffix
psetting('P238', '0');			# Check for new Firmware ( 0 = every time, 1 = only when suffix/prefix changes, 2 = never )
psetting('P194', '1');			# Automatic Update ( 0 = no, 1 = yes )
psetting('P193', '60');			# Firmware Check Interval (in minutes, default: 7 days)
//psetting('P242', '');			# Firmware Key (hex) ???
psetting('P240', '0');			# Authenticate Conf File ( 0 = no, 1 = yes )
if (subStr($phone_model,0,3) === 'gxp' ) {
	psetting('P145', '0');		# Allow DHCP Option 66 to override server ( 0 = no, 1 = yes ) //FIXME?
}


#####################################################################
#  Codecs (SIP Account 1?)
#####################################################################
# 0 = pcmu (ulaw), 8 = pcma (alaw), 2 = G.726-32, 4 = G.723.1, 15 = G.728, 18 = G.729a/b
psetting('P57', '8');			# Codec 1
psetting('P58', '8');			# Codec 2
psetting('P59', '8');			# Codec 3
psetting('P60', '8');			# Codec 4
psetting('P61', '8');			# Codec 5
psetting('P62', '8');			# Codec 6

psetting('P46', '8');			# Codec 7 ?
psetting('P98', '8');			# Codec 8 ?


#####################################################################
#  Codec settings
#####################################################################
psetting('P37', '2');			# Voice Frames per TX ( 10/20/32/64 frames for G711/G726/G723/other codecs respectively )
psetting('P96', '97');			# iLBC payload type ( between 96 and 127, default: 97 )
psetting('P97', '0');			# iLBC frame size ( 0 = 20ms, 1 = 30ms )
psetting('P50', '0');			# Silence Suppression ( 0 = no, 1 = yes )
psetting('P49', '0');			# G.723 Encoding Frame rate ( 0 = 6.3 kb/s, 1 = 5.3 kb/s )


#####################################################################
#  SIP Account 1
#####################################################################
if (subStr($phone_model,0,3) === 'gxp') {
	psetting('P271', '1');		# Account 1: Active ( 0 = no, 1 = yes )
	psetting('P270', 'Gemeinschaft');	# Account Name ( maxlength 96 )
}
psetting('P47', $host);			# SIP Server
psetting('P48', $sip_proxy_and_sbc['sip_proxy_from_wan']);  # Outbound Proxy
psetting('P35', $user_ext);		# SIP User ID
psetting('P36', $user_ext);		# Authentication ID
psetting('P34', $user['secret']);	# SIP Authentication Password (cleartext)
psetting('P3',  $user['callerid']);	# Display (CallerID) Name
psetting('P31', '1');			# SIP Registration ( 0 = no register, 1 = register )
psetting('P81', '1');			# Unregister on Reboot ( 0 = no, 1 = yes)
if (subStr($phone_model,0,3) === 'gxp')
	psetting('P32', '5');		# Register Expiration (in minutes, default: 60)
if (subStr($phone_model,0,2) === 'bt')
	psetting('P239', '300');	# Register Expiration (in seconds, default: 3600)
psetting('P63', '1');			# UserID is phone number ( 0 = no, 1 = yes)
psetting('P65', '0');			# Send Anonymous ( 0 = no, 1 = yes)
psetting('P73', '2');			# Send DTMF Type ( 0 = audio, 1 = RFC2833, 2 = SIP INFO )
//psetting('P272', '1');			# Enable 100rel. ( 0 = no, 1 = yes)
psetting('P191', '0');			# Enable Call Features ( 0 = no, 1 = yes)


#####################################################################
#  SIP Account 2
#####################################################################
if (subStr($phone_model,0,3) === 'gxp') {
	psetting('P401', '0');		# Account 2: Active ( 0 = no, 1 = yes )
}


#####################################################################
#  SIP Account 3
#####################################################################
if (subStr($phone_model,0,3) === 'gxp') {
	psetting('P501', '0');		# Account 2: Active ( 0 = no, 1 = yes )
}


#####################################################################
#  SIP Account 4
#####################################################################
if (subStr($phone_model,0,3) === 'gxp') {
	psetting('P601', '0');		# Account 2: Active ( 0 = no, 1 = yes )
}


#####################################################################
#  SIP settings
#####################################################################
psetting('P79', '101');			# DTMF Payload Type ( default: 101)
psetting('P84', '20');			# Keep-Alive Interval ( in seconds, default: 20)
psetting('P71', '' );			# Offhook Auto Dial (extension)
psetting('P76', '' );			# STUN Server
psetting('P52', '1');			# STUN NAT Traversal ( 0 = no, 1 = yes)

# Ports
psetting('P78', '0');		# Use random (RTP?) port ( 0 = no, 1 = yes )
psetting('P39', '5004');	# Local RTP Port ( 1024-65535, default 5004)
psetting('P40', '5060');	# Local SIP Port ( default 5060)


#####################################################################
#  Syslog Server
#####################################################################
psetting('P207', '' );		# Syslog server  //FIXME
psetting('P208', '0');		# Syslog level ( 0 = none, 1 = DEBUG, 2 = INFO, 3 = WARNING, 4 = ERROR )  //FIXME
if (subStr($phone_model,0,2) === 'bt') {
	psetting('P243', '0');		# Allow incoming SIP message from SIP proxy only ( 0 = no, 1 = yes)
}


#####################################################################
#  LDAP Directory (GXP)
#####################################################################
if (subStr($phone_model,0,3) === 'gxp') {
	psetting('P1304', '');	# LDAP Directory server path ( maxlength 128 )
}


#####################################################################
#  Display Language (GXP)
#####################################################################
if (subStr($phone_model,0,3) === 'gxp') {
	psetting('P342', '3');	# display language ( 0 = english, 2 = chinese, 3 = P399 )
	psetting('P399', 'german');	# language file prefix ( e.g. german => gxp_german.lpf, maxlength 32 )
}


#####################################################################
#  Keys (Buttons)
#####################################################################
psetting('P33', 'voicemail');	# VoiceMail Dial String


#####################################################################
#  Misc
#####################################################################
psetting('P88', '0');			# Lock keypad update ( 0 = no, 1 = yes)
psetting('P85', '2');			# No Key Entry Timeout (seconds)


#####################################################################
#  Misc (//FIXME todo)
#####################################################################

psetting('P99', '0');			# Subscribe for MWI
psetting('P74', '0');			# Send Flash DTMF event ( 0 = no, 1 = yes)
psetting('P91', '1');			# Disable Call Waiting ( 0 = disabled, 1 = enable)  //FIXME
psetting('P90', '0');			# Auto Answer ( 0 = no, 1 = yes)
psetting('P66', '' );			# Dial Plan Prefix
psetting('P72', '0');			# Use # as Dial Key ( 0 = no, 1 = yes)
psetting('P29', '0');			# Early Dial ( 0 = no, 1 = yes, use only if proxy supports 484 response)

psetting('P101', '');			# Use NAT IP ( 0 = no, 1 = yes)
psetting('P103', '0');			# Account 1: Use DNS SRV ( 0 = no, 1 = yes)


#####################################################################
#  Misc (BT)
#####################################################################
if (subStr($phone_model,0,2) === 'bt') {
}


#####################################################################
#  Misc (GXP)
#####################################################################
if (subStr($phone_model,0,3) === 'gxp') {
	psetting('P336', '0');		# Mute Speaker Ringer ( 0 = no, 1 = yes )
	psetting('P186', '0');		# Disable Call-Waiting Tone ( 0 = no, 1 = yes )
	psetting('P1310', '1');		# Disable Direct IP Calls ( 0 = no, 1 = yes )
	psetting('P184', '0');		# Use Quick IP-call mode ( 0 = no, 1 = yes )
	psetting('P1311', '0');		# Disable Conference ( 0 = no, 1 = yes )	
	psetting('P1339', '0');		# Enable MPK sending DTMF ( 0 = no, 1 = yes )
	psetting('P1340', '0');		# Disable DND ( 0 = disable, 1 = enable )
	psetting('P1301', '0');		# Headset TX gain (dB) ( 0 = 0dB, 1 = -6dB, 2 = +6dB )
	psetting('P1302', '0');		# Headset RX gain (dB) ( 0 = 0dB, 1 = -6dB, 2 = +6dB )
}



#####################################################################
#  Keys
#####################################################################

# reset all keys
//... //FIXME?

/*
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
	foreach ($softkeys as $key_name => $key_defs) {
		if (array_key_exists('slf', $key_defs)) {
			$key_def = $key_defs['slf'];
		} elseif (array_key_exists('inh', $key_defs)) {
			$key_def = $key_defs['inh'];
		} else {
			continue;
		}
		$key_idx = (int)lTrim(subStr($key_name,1),'0');
		if ($key_idx > $max_key) continue;
		setting('fkey', $key_idx, $key_def['function'] .' '. $key_def['data'], array('context'=>'active'));
	}
}
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
			//setting( $param_name, null        , $param_value );
			psetting($param_name,               $param_value );
		} else {
			# array
			gs_log( GS_LOG_NOTICE, "Group prov. param \"$param_name\"[$param_index]: Grandstream does not support arrays" );
			//gs_log( GS_LOG_DEBUG, "Overriding group prov. param \"$param_name\"[$param_index]: \"$param_value\"" );
			//setting( $param_name, $param_index, $param_value );
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
			//setting( $p['param'], null       , $p['value'] );
			psetting($p['param'],              $p['value'] );
		} else {
			# array
			gs_log( GS_LOG_NOTICE, 'User prov. param "'.$p['param'].'"['.$p['index'].']: Grandstream does not support arrays"' );
			//gs_log( GS_LOG_DEBUG, 'Overriding user prov. param "'.$p['param'].'"['.$p['index'].']: "'.$p['value'].'"' );
			//setting( $p['param'], $p['index'], $p['value'] );
		}
	}
}
unset($prov_params);



#####################################################################
#  Assemble output
#####################################################################

$string = _settings_out();
$outlength  = strlen($string) + 15;
$outlength -= $outlength % 16;
$cleartext  = $string;
for ($i=0; $i < $outlength - strlen($string); ++$i) {
	$cleartext .= "\000";
}
$outlength += 16;

$initbytes = array(
	0,
	0,
	(($outlength / 2) >> 8) & 0xff,
	(($outlength / 2)     ) & 0xff,
	0,
	0,
	intval(subStr($mac,  0, 2), 16),
	intval(subStr($mac,  2, 2), 16),
	intval(subStr($mac,  4, 2), 16),
	intval(subStr($mac,  6, 2), 16),
	intval(subStr($mac,  8, 2), 16),
	intval(subStr($mac, 10, 2), 16),
	13, # CR
	10, # LF
	13, # CR
	10  # LF
	);

$arr = $initbytes;
array_unshift($arr, 'C16');
$initstr = call_user_func_array('pack', $arr);
$checktext = $initstr . $cleartext;

array_splice($initbytes, 4, 2, checksum($checktext));

$arr = $initbytes;
array_unshift($arr, 'C16');
$initstr = call_user_func_array('pack', $arr);
$bin = $initstr . $cleartext;

#####################################################################
#  output
#####################################################################
ob_start();
echo $bin;
if (! headers_sent()) {
	header( 'Content-Type: application/octet-stream' );
	# avoid chunked transfer-encoding
	header( 'Content-Length: '. (int)@ob_get_length() );
}
@ob_flush();

?>