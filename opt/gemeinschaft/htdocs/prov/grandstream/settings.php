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

# HTTP_USER_AGENTs
#
# BT100/BT110:	"Grandstream BT110 1.0.8.33"
# BT200/BT201:	"Grandstream BT200 (bt200e.bin:1.1.6.46/boot55e.bin:1.1.6.6)"
# GXP280:	""
# GXP1200:	"Grandstream GXP1200 (gxp1200e.bin:1.1.6.46/boot55e.bin:1.1.6.6)"
# GXP2000:	"Grandstream GXP2000 (gxp2000e.bin:1.1.6.46/boot55e.bin:1.1.6.6)"
# GXP2010:	"Grandstream GXP2010 (gxp2010e.bin:1.1.6.46/boot55e.bin:1.1.6.6)"
# GXP2020:	"Grandstream GXP2020 (gxp2020e.bin:1.1.6.46/boot55e.bin:1.1.6.6)"
# GXV3000:	""
#
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
include_once( GS_DIR .'inc/gs-fns/gs_keys_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_prov_params_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_prov_params_get.php' );
//include_once( GS_DIR .'inc/ringtones-fns.php' ); //FIXME


#
# Warning: The order of parameters is important,
# especially for the BudgeTone 100/110 (BT100/BT110) models!
#

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
#  Passwords (global)
#####################################################################
psetting('P2', gs_get_conf('GS_GRANDSTREAM_PROV_HTTP_PASS'));	# Admin Password
psetting('P196', '');		# End User Password


#####################################################################
#  Network (global)
#####################################################################
psetting('P8' , '0');		# IP Address Type ( 0 = DHCP, 1 = static)

# DHCP
psetting('P82', '');		# PPPoE Account ID
psetting('P83', '');		# PPPoE Password
psetting('P146', 'grandstream-'.$mac );  # DHCP hostname
psetting('P147', '');		# DHCP domain name
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
#  Date and Time (global)
#####################################################################
psetting('P64',  (720 + (int)(((int)date('Z')) / 60)) );  # Timezone Offset ( Offset from GMT in minutes + 720 )
psetting('P75',  /*date('I')*/'0');	# Daylight Saving Time ( 0 = no, 1 = yes ) - we already have that in the timezone offset P64
psetting('P102', '2');			# Date Display Format ( 0 = Y-M-D, 1 = M-D-Y, 2 = D-M-Y )
psetting('P30',  gs_get_conf('GS_GRANDSTREAM_PROV_NTP'));  # NTP Server
if ( in_array($phone_model, array('bt200','gxp1200','gxp2000','gxp2010','gxp2020'), true) ) {
	psetting('P143', '1');		# Allow DHCP Option 2 to override Timezone setting ( 0 = no, 1 = yes )
	psetting('P144', '1');		# Allow DHCP Option 42 to override NTP server
	psetting('P246', '3,2,7,2,0;11,1,7,2,0;60');	# Daylight Saving Time Optional Rule ( maxlength 33 ) (//FIXME)
}


#####################################################################
#  LCD Display (BT200) (global)
#####################################################################
if ( in_array($phone_model, array('bt200'), true) ) {
	psetting('P339', '1');		# Display Account Name instead of Date (0 = no, 1 = yes)
}


#####################################################################
#  LCD Display (GXP) (global)
#####################################################################
if ( in_array($phone_model, array('gxp1200','gxp2000','gxp2010','gxp2020'), true) ) {
	psetting('P322', '0');		# LCD Backlight Always on ( 0 = no, 1 = yes )
	psetting('P1329', '12');	# LCD Contrast
	psetting('P122', '1');		# Time Display Format ( 0 = 12h, 1 = 24h )
	psetting('P123', '0');		# Display Clock instead of Date ( 0 = yes, 1 = no ) ???
	psetting('P338', '0');		# Disable in-call DTMF display ( 0 = no, 1 = yes )
	psetting('P351', '1');		# Disable Missed Call Backlight ( 0 = no, 1 = yes )
}
if ( in_array($phone_model, array('gxp280','gxp1200'), true) ) {
	psetting('P1344', '0');		# Display CID instead of Name for incoming calls ( 0 = no, 1 = yes ) //FIXME
}
if ( in_array($phone_model, array('gxp2010','gxp2020'), true) ) {
	psetting('P334', '4');		# LCD Backlight Brightness, Active ( 0-8, where 0 = off and 8 = brightest )
	psetting('P335', '0');		# LCD Backlight Brightness, Idle   ( 0-8, where 0 = off and 8 = brightest )
}


#####################################################################
#  Ringtones (global) //FIXME
#####################################################################
//if ( in_array($phone_model, array('gxp2000','gxp2010','gxp2020'), true) ) {
//	psetting('P105', 'internal');	# Custom ringtone 1, used if incoming caller ID is: ""
//	psetting('P106', 'external');	# Custom ringtone 2, used if incoming caller ID is: ""
//}else{
	psetting('P105', '');		# Custom ringtone 1, used if incoming caller ID is: ""
	psetting('P106', '');		# Custom ringtone 2, used if incoming caller ID is: ""
//}
psetting('P107', '');			# Custom ringtone 3, used if incoming caller ID is: ""
if ( in_array($phone_model, array('bt200','gxp1200','gxp2000'), true) ) {
	psetting('P345', '3,2,7,2,0;11,1,7,2,0;60');	# System ringtone ( maxlength 64 )
}
if ( in_array($phone_model, array('gxp2010','gxp2020'), true) ) {
	//psetting('P345', 'f1=440,f2=480,c=200/400&#59;'	# System ringtone ( maxlength 64 )
}


#####################################################################
#  Call Progress Tones (global) (//FIXME todo)
#####################################################################
/*
if ( in_array($phone_model, array('bt200','gxp1200','gxp2000','gxp2010','gxp2020'), true) ) {
	psetting('P343', 'f1=350,f2=440;');				# Dial Tone
	psetting('P344', 'f1=350,f2=440,c=10/10;');		# Message Waiting
	psetting('P346', 'f1=440,f2=480,c=200/400;');	# Ring Back Tone
	psetting('P347', 'f1=440,f2=440,c=25/525;');	# Call-Waiting Tone
	psetting('P348', 'f1=480,f2=620,c=50/50;');		# Busy Tone
	psetting('P349', 'f1=480,f2=620,c=25/25;');		# Reorder Tone
}
*/


#####################################################################
#  Firmware Upgrade and Provisioning (global)
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
psetting('P193', '1440');		# Firmware Check Interval (in minutes, default: 7 days)
psetting('P240', '0');			# Authenticate Conf File ( 0 = no, 1 = yes )
if ( in_array($phone_model, array('bt200','gxp1200','gxp2000','gxp2010','gxp2020'), true) ) {
	psetting('P145', '0');		# Allow DHCP Option 66 to override server ( 0 = no, 1 = yes ) //FIXME?
}
if ( in_array($phone_model, array('bt110'), true) ) {
	psetting('P242', '');		# Firmware Key (hex) ???
}


#####################################################################
#  Codecs specific for SIP Account 1 (specific)
#####################################################################
# 0 = pcmu (ulaw), 8 = pcma (alaw), 2 = G.726-32, 4 = G.723.1, 15 = G.728, 18 = G.729a/b
psetting('P57', '8');			# Codec 1
psetting('P58', '8');			# Codec 2
psetting('P59', '8');			# Codec 3
psetting('P60', '8');			# Codec 4
psetting('P61', '8');			# Codec 5
psetting('P62', '8');			# Codec 6
psetting('P46', '8');			# Codec 7
psetting('P98', '8');			# Codec 8


#####################################################################
#  SIP Account 1
#####################################################################
if ( in_array($phone_model, array('gxp1200','gxp2000','gxp2010','gxp2020'), true) ) {
	psetting('P271', '1');		# Account 1: Active ( 0 = no, 1 = yes )
	psetting('P270', $user_ext .' '. mb_subStr($user['firstname'],0,1) .'. '. $user['lastname']);	# Account Name ( maxlength 96 )
}
if ( in_array($phone_model, array('bt200'), true) ) {
	psetting('P270', $user_ext);	# Account Name ( maxlength 96 )
}
psetting('P47', $host);			# SIP Server
psetting('P48', $sip_proxy_and_sbc['sip_proxy_from_wan']);  # Outbound Proxy
psetting('P35', $user_ext);		# SIP User ID
psetting('P36', $user_ext);		# Authentication ID
psetting('P34', $user['secret']);	# SIP Authentication Password (cleartext)
psetting('P3',  $user['callerid']);	# Display (CallerID) Name
psetting('P103', '0');			# Use DNS SRV ( 0 = no, 1 = yes )
psetting('P63', '1');			# UserID is phone number ( 0 = no, 1 = yes )
psetting('P31', '1');			# SIP Registration ( 0 = no register, 1 = register )
psetting('P81', '1');			# Unregister on Reboot ( 0 = no, 1 = yes )
if ( in_array($phone_model, array('bt110'), true) ) {
	psetting('P239', '300');	# Register Expiration (in seconds, default: 3600)
}
if ( in_array($phone_model, array('bt200','gxp1200','gxp2000','gxp2010','gxp2020'), true) ) {
	psetting('P32', '5');		# Register Expiration (in minutes, default: 60)
}
psetting('P40', '5060');		# Local SIP Port ( default: 5060 )
psetting('P197', '');			# Proxy Require  //FIXME
psetting('P73', '1');			# Send DTMF Type ( 0 = audio, 1 = RFC2833, 2 = SIP INFO )
psetting('P29', '0');			# Early Dial ( 0 = no, 1 = yes, use only if proxy supports 484 response)
psetting('P66', '' );			# Dial Plan Prefix
psetting('P90', '0');			# Auto Answer ( 0 = no, 1 = yes )
psetting('P191', '0');			# Enable Call Features ( 0 = no, 1 = yes)
//if( in_array($phone_model, array('bt110','bt200','gxp1200'), true) && gs_ringtone_is_set_by_user_id($db, 'internal', $user_id) ) {
//	psetting('P104', '1');		# Default ring tone
//}
//else{
	psetting('P104', '0');		# Default ring tone
//}
psetting('P65', '0');			# Send Anonymous ( 0 = no, 1 = yes)
psetting('P268', '0');			# Anonymous Method ( 0 = use From header, 1 = use Privacy header )
psetting('P198', '100');		# Special Feature ( 100 = standard, default: 100)
psetting('P99', '0');			# Subscribe for MWI ( 0 = no, 1 = yes )  //FIXME
psetting('P52', '1');			# STUN NAT Traversal ( 0 = yes, 1 = no )

if ( in_array($phone_model, array('bt200','gxp1200','gxp2000','gxp2010','gxp2020'), true) ) {
	psetting('P138', '20');		# SIP Registration Failure Retry Wait Time ( seconds, default: 20 )
	psetting('P209', '100');	# SIP T1 Timeout ( 50 = 0.5 sec, 100 = 1 sec, 200 = 2 sec, default: 100 )
	psetting('P250', '400');	# SIP T2 Timeout ( 200 = 2 sec, 400 = 4 sec, 800 = 8 sec, default: 400 )
	psetting('P130', '1');		# SIP Transport ( 1 = udp, 2 = tcp )
	psetting('P131', '0');		# Use RFC3581 Symmetric Routing ( 0 = no, 1 = yes )
	psetting('P139', '20');		# Delayed Call Forward Wait Time ( Alowed range 1-120sec, default: 20 )
	psetting('P260', '180');	# Session Expiration ( in seconds, default: 180 )
	psetting('P261', '90');		# Min-SE ??? (in seconds, default and minimum: 90 )
	psetting('P1328', '300');	# Ring Timeout ( in seconds, between 30-3600, default: 60 )
	psetting('P262', '0');		# Caller Request Timer ( 0 = no, 1 = yes )
	psetting('P263', '0');		# Callee Request Timer ( 0 = no, 1 = yes )
	psetting('P264', '0');		# Force Timer ( 0 = no, 1 = yes )
	psetting('P266', '0');		# UAC Specify Refresher ( 0 = Omit (Recommended), 1 = UAC, 2 = UAS )
	psetting('P267', '1');		# UAS Specify Refresher ( 1 = UAC, 2 = UAS (when UAC did not specify refresher tag) )
	psetting('P265', '1');		# Force Invite ( 0 = no, 1 = yes )
	psetting('P272', '1');		# Enable 100rel ( 0 = no, 1 = yes )
	psetting('P129', '0');		# Anonymous Call Rejection ( 0 = no, 1 = yes )
	psetting('P298', '0');		# Allow Auto Answer by Call-Info ( 0 = no, 1 = yes )
	psetting('P299', '1');		# Turn off speaker on remote disconnect ( 0 = no, 1 = yes )
	psetting('P258', '0');		# Check SIP User ID for incoming INVITE ( 0 = no, 1 = yes )
	psetting('P135', '0');		# Refer-To Use Target Contact ( 0 = no, 1 = yes )
	psetting('P137', '0');		# Disable Media Attribute in SDP ( 0 = no, 1 = yes )
	psetting('P183', '0');		# SRTP Mode ( 0 = disabled, 1 = enabled but not forced, 2 = enabled and forced, 3 = optional )
}
if ( in_array($phone_model, array('bt110'), true) ) {
	psetting('P74', '0');		# Send Flash DTMF event ( 0 = no, 1 = yes)
	psetting('P243', '0');		# Allow incoming SIP message from SIP proxy only ( 0 = no, 1 = yes)
	psetting('P241', '0');		# Allow conf SIP account in Basic settings ( 0 = no, 1 = yes )
	psetting('P244', '0');		# Override MTU Size ( maxlength 4 )
	psetting('P109', '0');		# Allow outgoing call without Registration ( 0 = no, 1 = yes )
}
if ( in_array($phone_model, array('bt200'), true) ) {
	psetting('P187', '1');		# Disable Call Log ( 0 = no, 1 = yes )
}
if ( in_array($phone_model, array('gxp1200','gxp2000','gxp2010','gxp2020'), true) ) {
	psetting('P188', '0');		# PUBLISH for Presence ( 0 = no, 1 = yes )
	psetting('P182', '0');		# Call Log ( 0 = Log All Calls, 1 = ???, 2 = Disable Call Log )
}
if ( in_array($phone_model, array('gxp2000','gxp2010','gxp2020'), true) ) {
	psetting('P134', '');		# eventlist BLF URI ???
}


#####################################################################
#  SIP Account 2 (GXP)
#####################################################################
if ( in_array($phone_model, array('gxp1200','gxp2000','gxp2010','gxp2020'), true) ) {
	psetting('P401', '0');		# Account 2: Active ( 0 = no, 1 = yes )
}


#####################################################################
#  SIP Account 3 (GXP)
#####################################################################
if ( in_array($phone_model, array('gxp2000','gxp2010','gxp2020'), true) ) {
	psetting('P501', '0');		# Account 3: Active ( 0 = no, 1 = yes )
}


#####################################################################
#  SIP Account 4 (GXP)
#####################################################################
if ( in_array($phone_model, array('gxp2000','gxp2010','gxp2020'), true) ) {
	psetting('P601', '0');		# Account 4: Active ( 0 = no, 1 = yes )
}


#####################################################################
#  SIP Account 5 (GXP)
#####################################################################
if ( in_array($phone_model, array('gxp2020'), true) ) {
	psetting('P1701', '0');		# Account 5: Active ( 0 = no, 1 = yes )
}


#####################################################################
#  SIP Account 6 (GXP)
#####################################################################
if ( in_array($phone_model, array('gxp2020'), true) ) {
	psetting('P1801', '0');		# Account 6: Active ( 0 = no, 1 = yes )
}


#####################################################################
#  SIP global settings (global)
#####################################################################
psetting('P79', '101');			# DTMF Payload Type ( default: 101)
psetting('P84', '20');			# Keep-Alive Interval ( in seconds, default: 20)
psetting('P71', '' );			# Offhook Auto Dial (extension)
psetting('P76', '' );			# STUN Server
psetting('P101', '');			# Use NAT IP ( if specified, this IP address is used for SIP/SDP message )
psetting('P91', '1');			# Disable Call Waiting ( 0 = no, 1 = yes )  //FIXME

# RTP global Ports
psetting('P39', '5004');	# Local RTP Port ( 1024-65535, default: 5004 )
psetting('P78', '0');		# Use random RTP port ( 0 = no, 1 = yes )


#####################################################################
#  Codec global settings (global)
#####################################################################
psetting('P37', '2');			# Voice Frames per TX ( 10/20/32/64 frames for G711/G726/G723/other codecs respectively )
psetting('P96', '97');			# iLBC payload type ( between 96 and 127, default: 97 )
psetting('P97', '0');			# iLBC frame size ( 0 = 20ms, 1 = 30ms )
psetting('P50', '0');			# Silence Suppression ( 0 = no, 1 = yes )
psetting('P49', '0');			# G.723 Encoding Frame rate ( 0 = 6.3 kb/s, 1 = 5.3 kb/s )


#####################################################################
#  Syslog Server (global)
#####################################################################
psetting('P207', '');		# Syslog server  //FIXME
psetting('P208', '0');		# Syslog level ( 0 = none, 1 = DEBUG, 2 = INFO, 3 = WARNING, 4 = ERROR )  //FIXME


#####################################################################
#  Phonebook (GXP) (global)
#####################################################################
if ( in_array($phone_model, array('gxp1200','gxp2000','gxp2010','gxp2020'), true) ) {
	psetting('P330', '1');	# Enable Phonebook XML ( 0 = disable, 1 = http, 2 = tftp )
	psetting('P331', rTrim($prov_url_grandstream,'/'));	# Phonebook XML server path ( maxlength 128 )
	psetting('P332', '0');	# Phonebook Download Interval ( in minutes, between 0-720 ) //FIXME
	psetting('P333', '1');	# Remove manually-edited entries on download ( 0 = no, 1 = yes )
}


#####################################################################
#  LDAP Directory (GXP) (global)
#####################################################################
if ( in_array($phone_model, array('gxp1200','gxp2000','gxp2010','gxp2020'), true) ) {
	psetting('P1304', '');	# LDAP Directory server path ( maxlength 128 )
}


#####################################################################
#  Idle Screen (GXP) (global)
#####################################################################
if ( in_array($phone_model, array('gxp2000','gxp2010','gxp2020'), true) ) {
	psetting('P340', '0');	# Enable Idle Screen XML ( 0 = disable, 1 = http, 2 = tftp )  //FIXME?
	psetting('P341', $prov_url_grandstream);  # Idle Screen XML server path ( maxlength 128 )  //FIXME?
	# grandstream automatically adds "/gs_screen.xml" to the URL
	# e.g. prov/grandstream/gs_screen.xml
	# todo: script to change idle_screen for nobody or user
}
if ( in_array($phone_model, array('gxp2020'), true) ) {
	psetting('P1343', '0');	# use custom filename ( 0 = no, 1 = yes )
}


#####################################################################
#  XML-Application (GXP) (global) //FIXME|TODO
#####################################################################
if ( in_array($phone_model, array('gxp2020'), true) ) {
	psetting('P337', '');	# server path ( maxlength 128 )
	psetting('P352', '');	# softkey label ( maxlength 128 )
}


#####################################################################
#  Display Language (GXP) (global)
#####################################################################
if ( in_array($phone_model, array('gxp1200','gxp2000','gxp2010','gxp2020'), true) ) {
	psetting('P342', '3');		# display language ( 0 = english, 2 = chinese, 3 = P399 )
	psetting('P399', 'german');	# language file prefix ( e.g. german => gxp_german.lpf, maxlength 32 )
}


#####################################################################
#  Misc (global)
#####################################################################
psetting('P88', '0');		# Lock keypad update ( 0 = no, 1 = yes )
psetting('P85', '2');		# No Key Entry Timeout ( seconds, default: 4 )
psetting('P72', '0');		# Use # as Dial Key ( 0 = no, 1 = yes )


#####################################################################
#  Misc (BT200) (global)
#####################################################################
if ( in_array($phone_model, array('bt200'), true) ) {
	psetting('P245', '8');		# Onhook Threshold ( 0 = off, 2 = 200ms, 4 = 400ms, ..., 12 = 1200ms )
	psetting('P1340', '1');		# Disable DND ( 0 = no, 1 = yes ) (keine Anzeige)
}


#####################################################################
#  Misc (GXP) (global)
#####################################################################
if ( in_array($phone_model, array('gxp1200','gxp2000','gxp2010','gxp2020'), true) ) {
	psetting('P186', '0');		# Disable Call-Waiting Tone ( 0 = no, 1 = yes )
	psetting('P1340', '0');		# Disable DND ( 0 = no, 1 = yes )
	psetting('P1339', '0');		# Enable MPK sending DTMF ( 0 = no, 1 = yes )
}

if ( in_array($phone_model, array('gxp1200','gxp2010','gxp2020'), true) ) {
	psetting('P1312', '0');		# Headset Key Mode ( 0 = default moder, 1 = Toggle Headset/Speaker )
}

if ( in_array($phone_model, array('gxp2010','gxp2020'), true) ) {
	psetting('P1303', '');		# Intercom User ID ( maxlength 64 )
	psetting('P1300', '0');		# Headset Port Type ( 0 = 2.5mm, 1 = RJ22 )
}

#####################################################################
#  Misc (BT2 & GXP) (global)
#####################################################################
if ( in_array($phone_model, array('bt200','gxp1200','gxp2000','gxp2010','gxp2020'), true) ) {
	psetting('P336', '0');		# Mute Speaker Ringer ( 0 = no, 1 = yes )
	psetting('P1310', '1');		# Disable Direct IP Calls ( 0 = no, 1 = yes )
	psetting('P184', '0');		# Use Quick IP-call mode ( 0 = no, 1 = yes )
	psetting('P1311', '0');		# Disable Conference ( 0 = no, 1 = yes )	
	psetting('P1301', '0');		# Headset TX gain (dB) ( 0 = 0dB, 1 = -6dB, 2 = +6dB )
	psetting('P1302', '0');		# Headset RX gain (dB) ( 0 = 0dB, 1 = -6dB, 2 = +6dB )
	psetting('P1341', '0');		# Disable Transfer ( 0 = no, 1 = yes )
}


#####################################################################
#  Keys (Buttons)
#####################################################################
psetting('P33', 'voicemail');		# VoiceMail Dial String


#####################################################################
#  Keys (GXP) (global) //TODO GXP1200
#####################################################################

# 1 parameter - Key Mode ( 0 = Speed Dial, 1 = BLF, 2 = Presence Watcher, 3 = eventlist BLF)
# 2 parameter - which Account ( 0 = 1, 1 = 2, 2 = 3, 3 = 4)
# 3 parameter - Display Name ( maxlength 32 )
# 4 parameter - User ID ( maxlength 64 )

# reset all keys
if ( in_array($phone_model, array('gxp2000','gxp2010','gxp2020'), true) ) {
	# reset all keys on phone
	#
	# key layout:
	# Key 1: P323 P301 P302 P303
	# Key 2: P324 P304 P305 P306
	# Key 3: P325 P307 P308 P309
	# ...	
	# Key 7: P329 P319 P320 P321
	$max_keys = 7;
	for ($i=0; $i<$max_keys; ++$i) {
		psetting('P'.($i  +323), '0');
		psetting('P'.($i*3+301), '0');
		psetting('P'.($i*3+302), '' );
		psetting('P'.($i*3+303), '' );
	}
	if ( in_array($phone_model, array('gxp2010'), true) ) {
		# key layout (only GXP2010)
		# Key  8: P353 P354 P355 P356
		# Key  9: P357 P358 P359 P360
		# Key 10: P361 P362 P363 P364
		# ...
		# Key 18: P393 P394 P395 P396
		$max_keys = 11;
		for ($i=0; $i<$max_keys; ++$i) {
			psetting('P'.($i*4+353), '0');
			psetting('P'.($i*4+354), '0');
			psetting('P'.($i*4+355), '' );
			psetting('P'.($i*4+356), '' );
		}
	}
	
	# reset all keys on ext 1
	#
	# key layout:
	# Key  1: P6001 P6201 P6401 P6601
	# Key  2: P6002 P6202 P6402 P6602
	# Key  3: P6003 P6203 P6403 P6603
	# ...
	# Key 56: P6056 P6256 P6456 P6656
	$max_keys = 56;
	for ($i=0; $i<$max_keys; ++$i) {
		psetting('P'.($i+6001), '0');
		psetting('P'.($i+6201), '0');
		psetting('P'.($i+6401), '' );
		psetting('P'.($i+6601), '' );
	}
	
	# reset all keys on ext 2
	#
	# key layout:
	# Key  57: P6057 P6257 P6457 P6657
	# Key  58: P6058 P6258 P6458 P6658
	# Key  59: P6059 P6259 P6459 P6659
	# ...
	# Key 112: P6112 P6312 P6512 P6712
	$max_keys = 56;
	for ($i=0; $i<$max_keys; ++$i) {
		psetting('P'.($i+6057), '0');
		psetting('P'.($i+6257), '0');
		psetting('P'.($i+6457), '' );
		psetting('P'.($i+6657), '' );
	}
}

if (in_array($phone_model, array('gxp2000', 'gxp2020'), true)) $max_key =  7;
if (in_array($phone_model, array('gxp2010'           ), true)) $max_key = 18;


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
		if ($key_idx > $max_key-1) continue;
		if ($key_def['function'] === 'empty') continue;
		//setting('fkey', $key_idx, $key_def['function'] .' '. $key_def['data'], array('context'=>'active'));
		
		if ($key_idx < 7) {  # gxp2000, gxp2010, gxp2020
			psetting('P'.($key_idx  +323), subStr($key_def['function'],1));
			//psetting('P'.($key_idx*3+301), '0');
			//psetting('P'.($key_idx*3+302), '');
			psetting('P'.($key_idx*3+303), $key_def['data']);
		} elseif ($key_idx >= 7) {  # gxp2010
			psetting('P'.(($key_idx-7)*4+353), subStr($key_def['function'],1));
			//psetting('P'.(($key_idx-7)*4+354), '');
			//psetting('P'.(($key_idx-7)*4+355), '');
			psetting('P'.(($key_idx-7)*4+356), $key_def['data']);
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
if (! is_array($prov_params)) {
	gs_log( GS_LOG_WARNING, 'Failed to get provisioning parameters (group)' );
} else {
	foreach ($prov_params as $param_name => $param_arr) {
	foreach ($param_arr as $param_index => $param_value) {
		if ($param_index == -1) {
			# not an array
			if (! array_key_exists($param_name, $settings)) {
				# don't set unknown parameters because the order is important
				gs_log( GS_LOG_NOTICE, "Group prov. param \"$param_name\": Unknown parameter" );
				continue;
			}
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
			if (! array_key_exists($p['param'], $settings)) {
				# don't set unknown parameters because the order is important
				gs_log( GS_LOG_NOTICE, "User prov. param \"$param_name\": Unknown parameter" );
				continue;
			}
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
#  create BODY
#####################################################################
$body = _settings_out();
if ( (strLen($body)%2) == 1 ) $body .= 0x00;	# auffuellen mit 0x00, damit durch 2 teilbar
$body_length = strLen($body);


#####################################################################
#  create HEADER
#####################################################################
$header_length = 16;
$out_length = $header_length + $body_length;

$header = array();

// 00 01 02 04 - out_length / 2 
$header[] = (($out_length / 2) >> 24) & 0xff;
$header[] = (($out_length / 2) >> 16) & 0xff;
$header[] = (($out_length / 2) >>  8) & 0xff;
$header[] = (($out_length / 2)      ) & 0xff;

// 04 05 - put checksum in later
$header[] = 0x00;
$header[] = 0x00;

// 06 07 08 09 0a 0b - MAC address
$header[] = intval(subStr($mac,  0, 2), 16);
$header[] = intval(subStr($mac,  2, 2), 16);
$header[] = intval(subStr($mac,  4, 2), 16);
$header[] = intval(subStr($mac,  6, 2), 16);
$header[] = intval(subStr($mac,  8, 2), 16);
$header[] = intval(subStr($mac, 10, 2), 16);

// 0c 0d 0e 0f - CR LF CR LF
$header[] = 0x0d; # CR
$header[] = 0x0a; # LF
$header[] = 0x0d; # CR
$header[] = 0x0a; # LF


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
if (! headers_sent()) {
	header( 'Content-Type: application/octet-stream' );
	# avoid chunked transfer-encoding
	header( 'Content-Length: '. (int)@ob_get_length() );
}
@ob_flush();

?>