<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1857 $
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

header( 'Content-type: text/plain; charset=utf-8' );

require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'inc/util.php' );
set_error_handler('err_handler_die_on_err');

$mac = preg_replace( '/[^0-9A-F]/', '', strToUpper( @$_REQUEST['mac'] ) );
if (strLen($mac) != 12) {
	gs_log( GS_LOG_NOTICE, "Snom provisioning: Invalid MAC address \"$mac\" (wrong length)" );
	# don't explain this to the users
	die( 'No! See log for details.' );
}
if (hexDec(subStr($mac,0,2)) % 2 == 1) {
	gs_log( GS_LOG_NOTICE, "Snom provisioning: Invalid MAC address \"$mac\" (multicast address)" );
	# don't explain this to the users
	die( 'No! See log for details.' );
}
if ($mac == '000000000000') {
	gs_log( GS_LOG_NOTICE, "Snom provisioning: Invalid MAC address \"$mac\" (huh?)" );
	# don't explain this to the users
	die( 'No! See log for details.' );
}

# make sure the phone is a Snom:
#
if (subStr($mac,0,6) != '000413') {
	gs_log( GS_LOG_NOTICE, "Snom provisioning: MAC address \"$mac\" is not a Snom phone" );
	# don't explain this to the users
	die( 'No! See log for details.' );
}

$ua = trim( @$_SERVER['HTTP_USER_AGENT'] );
//if (isSet( $ua )) {
if (! preg_match('/^Mozilla/i', $ua)
 || ! preg_match('/snom\d{3}/i', $ua) ) {
	gs_log( GS_LOG_WARNING, "Phone with MAC \"$mac\" (Snom) has invalid User-Agent (\"". $ua ."\")" );
	# don't explain this to the users
	die( 'No! See log for details.' );
}
if (preg_match('/^Mozilla\/\d\.\d\s*\(compatible;\s*/i', $ua, $m)) {
	$ua = rTrim(subStr( $ua, strLen($m[0]) ), ' )');
}

# find out the type of the phone:
# user-agents:
# 360: "Mozilla/4.0 (compatible; snom360-SIP 6.5.2; snom360 ramdisk v3.31; snom360 linux 3.25)"
# 370: "Mozilla/4.0 (compatible; snom370-SIP 7.1.2)"
if (preg_match('/snom([1-9][0-9]{2})/i', $ua, $m))  # i.e. "snom360"
	$phone_type = $m[1];
else
	$phone_type = 'unknown';

gs_log( GS_LOG_DEBUG, "Snom phone \"$mac\" asks for settings (UA: ...\"$ua\") - type: $phone_type" );


$newPhoneType = 'snom-'. $phone_type;  # i.e. "snom-360"
# to be used when auto-adding the phone

require_once( GS_DIR .'inc/db_connect.php' );
require_once( GS_DIR .'inc/nobody-extensions.php' );
require_once( GS_DIR .'inc/gs-lib.php' );


$settings = array();

/*
! means writeable by the user, but will not overwrite existing 
$ means writeable by the user, but will overwrite existing (available since version 4.2)
& (or no flag) means read only, but will overwrite existing
*/
function setting($setting, $value, $writeable=false) {
	global $settings;
	/*
	$settings[$setting] = array(
		'v' => $value,
		'f' => ($writeable ? '&' : '&')
	);
	if ($setting=='admin_mode')
		$settings[$setting]['f'] = '$';
	*/
	$settings[$setting] = array(
		'v' => $value,
		'f' => ($writeable ? '$' : '&')
	);
}
function settings_out() {
	global $settings;
	foreach ($settings as $setting => $arr) {
		echo $setting, $arr['f'], ': ', $arr['v'], "\n";
	}
}



$db = gs_db_master_connect();
if (! $db) {
	gs_log( GS_LOG_WARNING, "Snom phone asks for settings - Could not connect to DB" );
	die( 'Could not connect to DB.' );
}

/*

asterisk login (zb *991234):
- in db nachsehen, von welchem sip-acct der user kommt
- da die mac addr löschen und bei seinem acct eintragen
- reboot senden

asterisk logout:
- in db nachsehen, von welchem sip-acct der user kommt
- die mac addr löschen
- reboot senden

nach reboot (-> php skript):
- nachsehen, bei welchem sip acct die mac addr eingetr ist
falls gefunden:
- provision senden
falls nicht:
- mac addr bei einem unbenutzten nobody eintragen
  (_user_id=NULL, regseconds < time()-24*3600)
- secret ändern
- provision senden

*/



# do we know the phone?
#

/*
$query =
'SELECT `user_id`, `vlan_id`
FROM `phones`
WHERE `mac_addr`=\''. $mac .'\'
ORDER BY `id` LIMIT 1';
$rs = $db->execute( $query );
$r = $rs->fetchRow();
if ($r) {
	$user_id = (int)$r['user_id'];
	$vlan_id = (int)$r['vlan_id'];
*/
$query =
'SELECT `user_id`
FROM `phones`
WHERE `mac_addr`=\''. $mac .'\'
ORDER BY `id` LIMIT 1';
$rs = $db->execute( $query );
$r = $rs->fetchRow();
if ($r) {
	$user_id = (int)$r['user_id'];
} else {
	if (! GS_PROV_AUTO_ADD_PHONE) {
		gs_log( GS_LOG_NOTICE, "Unknown Snom phone \"$mac\" not added to DB" );
		die( 'Unknown phone. (Set GS_PROV_AUTO_ADD_PHONE = true in order to auto-add)' );
	}
	gs_log( GS_LOG_NOTICE, "Adding new Snom phone $mac to DB" );
	
	# add a nobody user:
	#
	$newNobodyIndex = (int)( (int)$db->executeGetOne( 'SELECT MAX(`nobody_index`) FROM `users`' ) + 1 );
	$user_code = 'nobody-'. str_pad($newNobodyIndex, 5, '0', STR_PAD_LEFT);
	switch (GS_PROV_AUTO_ADD_PHONE_HOST) {
		case 'last':
			$host_id_sql = 'SELECT MAX(`id`) FROM `hosts`'; break;
		case 'random':
			$host_id_sql = 'SELECT `id` FROM `hosts` ORDER BY RAND() LIMIT 1'; break;
		case 'first':
		default:
			$host_id_sql = 'SELECT MIN(`id`) FROM `hosts`'; break;
	}
	$db->execute( 'INSERT INTO `users` (`id`, `user`, `pin`, `firstname`, `lastname`, `honorific`, `nobody_index`, `host_id`) VALUES (NULL, \''. $user_code .'\', \'\', \'\', \'\', \'\', '. $newNobodyIndex .', ('. $host_id_sql .'))' );
	$user_id = (int)$db->getLastInsertId();
	if ($user_id < 1) die( 'Unknown phone. Failed to add nobody user.' );
	
	# add a SIP account:
	#
	//$user_name = '9'. str_pad($newNobodyIndex, 5, '0', STR_PAD_LEFT);
	$user_name = gs_nobody_index_to_extension( $newNobodyIndex );
	$secret = rand(10000000,99999999) . mt_rand(10000000,99999999) . rand(10000000,99999999);
	$db->execute( 'INSERT INTO `ast_sipfriends` (`_user_id`, `name`, `secret`, `context`, `callerid`, `setvar`) VALUES ('. $user_id .', \''. $user_name .'\', \''. $db->escape($secret) .'\', \'from-internal-nobody\', _utf8\''. $db->escape(GS_NOBODY_CID_NAME . $newNobodyIndex) .' <'. $user_name .'>\', \'__user_id='. $user_id .';__user_name='. $user_name .'\')' );
	
	# add the phone:
	#
	$db->execute( 'INSERT INTO `phones` (`id`, `type`, `mac_addr`, `user_id`, `nobody_index`, `added`) VALUES (NULL, \''. $db->escape($newPhoneType) .'\', \''. $mac .'\', '. $user_id .', '. $newNobodyIndex .', '. time() .')' );
	
	unset($user_name);
}



# is it a valid user id?
#

$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `users` WHERE `id`='. $user_id );
if ($num < 1)
	$user_id = 0;

if ($user_id < 1) {
	# something bad happened, nobody (not even a nobody user) is logged
	# in at that phone. assign an unused nobody user:
	
	$user_id = (int)$db->executeGetOne(
'SELECT `u`.`id`
FROM
	`users` `u` JOIN
	`phones` `p` ON (`p`.`nobody_index`=`u`.`nobody_index`)
WHERE
	`p`.`mac_addr`=\''. $mac .'\' AND
	`p`.`nobody_index`>=1
LIMIT 1'
	);
	if ($user_id < 1) {
		$user_id = (int)$db->executeGetOne(
'SELECT `id`
FROM `users`
WHERE
	`nobody_index` IS NOT NULL AND
	`id` NOT IN (SELECT `user_id` FROM `phones` WHERE `user_id` IS NOT NULL)
ORDER BY RAND() LIMIT 1'
	);
		if ($user_id < 1)
			die( 'No unused nobody accounts left.' );
	}
	$ok = $db->execute( 'UPDATE `phones` SET `user_id`='. $user_id .' WHERE `mac_addr`=\''. $mac .'\' LIMIT 1'  );
	if (! $ok) die( 'DB error.' );
	$rs = $db->execute( $query );
	$r = $rs->fetchRow();
	if (! $r) die( 'Failed to assign nobody account to phone "'. $mac .'".' );
	$user_id = (int)$r['user_id'];
}
if ($user_id < 1) die( 'DB error.' );



# if no host specified, select one (randomly?)
#

$host = $db->executeGetOne(
'SELECT `h`.`host`
FROM
	`users` `u` LEFT JOIN
	`hosts` `h` ON (`h`.`id`=`u`.`host_id`)
WHERE `u`.`id`='. $user_id .'
LIMIT 1'
	);

if (! $host) {
	$rs = $db->execute( 'SELECT `host` FROM `hosts` ORDER BY RAND() LIMIT 1' );
	$r = $rs->fetchRow();
	if (! $r)
		die( 'No hosts known.' );
	
	$host = trim( $r['host'] );
}



# who is logged in at that phone?
#

$rs = $db->execute(
'SELECT
	`u`.`user`, `u`.`firstname`, `u`.`lastname`, `u`.`honorific`,
	`s`.`name`, `s`.`secret`, `s`.`callerid`, `s`.`mailbox`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE `u`.`id`='. $user_id
);
$user = $rs->fetchRow();
if (! $user) {
	die( 'DB error.' );
}

# change the ip account's secret for improved security and to kick
# phones who did not get a reboot
#
# EDIT: don't do that! -> race condition
# the Snoms try to authenticate with their old account after reboot
# before they fetch theit new settings
#
//sRand(); mt_sRand();
//$secret = rand(10000, 99999) . mt_rand(10000, 99999) . rand(100000, 999999);
/*
$now = time();
sRand((int)subStr($now,-3,1));
$secret = rand(1,9) . strRev(subStr($now,0,-3));
//$secret = subStr(time(), 0, -3);
//$secret = '1234';
$db->execute( 'UPDATE `ast_sipfriends` SET `secret`=\''. $db->escape($secret) .'\' WHERE `_user_id`='. (int)$user_id );
$user['secret'] = $secret;
*/


# store the user's current IP address in the database:
#

# get the IP address of the phone:
#
$phoneIP = @ normalizeIPs( @$_SERVER['REMOTE_ADDR'] );
if (isSet( $_SERVER['HTTP_X_FORWARDED_FOR'] )) {
	if (preg_match( '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', lTrim( $_SERVER['HTTP_X_FORWARDED_FOR'] ), $m ))
		$phoneIP = isSet( $m[0] ) ? @ normalizeIPs( $m[0] ) : null;
}
if (isSet( $_SERVER['HTTP_X_REAL_IP'] )) {
	if (preg_match( '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', lTrim( $_SERVER['HTTP_X_REAL_IP'] ), $m ))
		$phoneIP = isSet( $m[0] ) ? @ normalizeIPs( $m[0] ) : null;
}

if ($phoneIP) {
	# unset all ip addresses which are the same as the new one and
	# thus cannot be valid any longer:
	$db->execute( 'UPDATE `users` SET `current_ip`=NULL WHERE `current_ip`=\''. $db->escape($phoneIP) .'\'' );
}
# store new ip address:
$db->execute( 'UPDATE `users` SET `current_ip`='. ($phoneIP ? ('\''. $db->escape($phoneIP) .'\'') : 'NULL') .' WHERE `id`='. $user_id );




#
# General
#
setting('language'         , 'Deutsch', true);
setting('web_language'     , 'Deutsch', true);
setting('display_method'   , 'display_name_number', true);
setting('tone_scheme'      , 'GER'    );
setting('date_us_format'   , 'off'    , true);
setting('time_24_format'   , 'on'     , true);
setting('message_led_other', 'off'    );
setting('use_backlight'    , 'on'     , true);
//setting('headset_device'   , 'headset_rj', true);  # würde Default auf Headset am RJ14-Stecker setzen
setting('headset_device'   , 'none', true);
setting('ethernet_detect'  , 'on'     );  # Warnung falls kein Ethernet
setting('ethernet_replug'  , 'reboot' );
setting('reboot_after_nr'  , '5'      );  # nach 5 Min. ohne Registrierung neu starten
setting('admin_mode'       , 'off'    , true);  # wenn die Einstellung nicht writable ist, ist auch kein Admin-Login möglich
setting('admin_mode_password'         , '0000');
setting('admin_mode_password_confirm' , '0000');


#
# Network / Advanced Network / SIP
#
setting('dhcp'                 , 'on' );
//setting('netmask'              , '255.255.255.0'); # leave it up to the DHCP
//setting('gateway'              , '192.168.1.1');
setting('filter_registrar'     , 'off');  # so we can reboot the phone even if not registered
setting('enable_timer_support' , 'on' );
setting('session_timer'        , '300');  # default 3600
setting('dirty_host_ttl'       , '0'  );
setting('challenge_response'   , 'off');
setting('challenge_reboot'     , 'off');
setting('challenge_checksync'  , 'off');
//setting('network_id_port'      , '5060');  # feste Vorgabe von 5060 funktioniert nicht im VLAN
setting('network_id_port'      , ''   );
setting('tcp_listen'           , 'off');
setting('offer_gruu'           , 'off');
setting('short_form'           , 'on' );  # kurze SIP-Header verwenden
setting('subscription_delay'   , '5'  );
setting('subscription_expiry'  , '3600');  # default 3600
setting('terminate_subscribers_on_reboot', 'on');
setting('publish_presence'     , 'off');  # unterstützt Asterisk (noch?) nicht
setting('presence_timeout'     , '30' );  # default 15 (Minuten)
setting('user_phone'           , 'off');  # user=phone in SIP URIs is deprecated
setting('require_prack'        , 'on' );  # default
setting('refer_brackets'       , 'off');  # default
setting('offer_mpo'            , 'on' );  # default: off
setting('register_http_contact', 'off');
setting('support_rtcp'         , 'on' );  # default
setting('signaling_tos'        , '160');  # default: 160, 160 = CS 5
setting('codec_tos'            , '184');  # default: 160, 184 = EF
setting('dtmf_payload_type'    , '101');  # default
setting('sip_proxy'            , ''   );
setting('eth_net'              , 'auto');
setting('eth_pc'               , 'auto');
setting('redirect_ringing'     , 'off', true);
setting('disable_blind_transfer', 'off');
setting('disable_deflection'   , 'off');
setting('watch_arp_cache'      , '1'  );  # default 0
/*
if ($vlan_id < 1) {
	setting('vlan', '' );
	setting('vlan_id', '' );
} else {
	//setting('vlan', ($vlan_id < 1 ? '' : 'x 0') );
	//setting('vlan', $vlan_id .' 5' );  # Prio. 5 (0|1-7)
	setting('vlan', $vlan_id );
	setting('vlan_id', $vlan_id );
}
*/
//setting('vlan_qos', '5' );  # Prio. 5 (0|1-7)  muß ggf. getestet werden


#
# Web-Server
#
setting('webserver_type'  , 'http_https');
setting('http_scheme'     , 'off');  # off = Basic, on = Digest
setting('http_user'       , GS_PROV_SNOM_HTTP_USER );
setting('http_pass'       , GS_PROV_SNOM_HTTP_PASS );
setting('http_port'       , '80' );
setting('https_port'      , '443');
setting('web_logout_timer', '5'  );
setting('with_flash'      , 'on' , true);


#
# Audio
#
setting('pickup_indication'    , 'on' );  # Piepton wenn Pickup möglich
setting('keytones'             , 'on' );
setting('holding_reminder'     , 'on' );
setting('alert_info_playback'  , 'on' );
setting('mute'                 , 'off', true);  # mute mic off
setting('disable_speaker'      , 'off', true);  # disable casing speaker off
if ($phone_type >= '370') {
	setting('vol_handset_mic'      ,  '4' , true);  # 1 - 8, Default: 4
	setting('vol_headset_mic'      ,  '6' , true);  # 1 - 8, Default: 4
	setting('vol_speaker_mic'      ,  '4' , true);  # 1 - 8, Default: 4
	setting('vol_speaker'          ,  '5' , true);  # 0 - 15, Default: 8
	setting('vol_handset'          , '10' , true);  # 0 - 15, Default: 8
	setting('vol_headset'          , '10' , true);  # 0 - 15, Default: 8
	setting('vol_ringer'           ,  '8' , true);  # 1 - 15
} else {
	setting('vol_handset_mic'      ,  '3' , true);  # 1 - 8, Default: 4
	setting('vol_headset_mic'      ,  '6' , true);  # 1 - 8, Default: 4
	setting('vol_speaker_mic'      ,  '6' , true);  # 1 - 8, Default: 4
	setting('vol_speaker'          , '10' , true);  # 0 - 15, Default: 8
	setting('vol_handset'          , '10' , true);  # 0 - 15, Default: 8
	setting('vol_headset'          , '10' , true);  # 0 - 15, Default: 8
	setting('vol_ringer'           ,  '7' , true);  # 1 - 15
}
setting('mwi_notification'     , 'silent');  # keine akustischen Hinweise wenn neue Nachrichten
setting('mwi_dialtone'         , 'stutter', true);  # stotternder Wählton wenn neue Nachrichten
setting('silence_compression'  , 'off');  # kann Asterisk (noch?) nicht
setting('ringer_headset_device', 'speaker');  # Klingeltonausgabe bei Kopfhörer (headset|speaker)


#
# Behavior
#
setting('callpickup_dialoginfo'  , 'on' , true);
setting('show_xml_pickup'        , 'on' );
setting('ringing_time'           , '500');  # wird im Dialplan begrenzt
setting('block_url_dialing'      , 'on' );  # nur Ziffern erlauben
setting('ringer_animation'       , 'on' , true);
setting('xml_notify'             , 'on' );
setting('redundant_fkeys'        , 'off', true);
setting('text_softkey'           , 'off');
setting('edit_alpha_mode'        , '123', true);
setting('overlap_dialing'        , 'off');
setting('auto_logoff_time'       , ''   );
setting('keyboard_lock'          , 'off');
setting('keyboard_lock_pw'       , ''   );
setting('keyboard_lock_emergency', '911 112 110 999 19222');  # default
setting('ldap_server'            , ''   );
setting('answer_after_policy'    , 'idle');
setting('call_join_xfer'         , 'off');
setting('guess_number'           , 'off');
setting('partial_lookup'         , 'off');
setting('deny_all_feature'       , 'off');  # keine Telefon-interne Blacklist
setting('audio_device_indicator' , 'on' );
setting('intercom_enabled'       , 'off');  # brauchen wir (noch?) nicht
setting('cmc_feature'            , 'off');
setting('cancel_on_hold'         , 'on' );
setting('cancel_missed'          , 'on' );
setting('cancel_desktop'         , 'on' );
setting('cw_dialtone'            , 'on' );
setting('auto_connect_indication', 'on' );
setting('auto_connect_type'      , 'auto_connect_type_handsfree');
setting('privacy_in'             , 'off');  # accept anonymous calls
setting('privacy_out'            , 'off');  # send caller-id
setting('auto_dial'              , 'off');
setting('conf_hangup'            , 'on' , true);
setting('no_dnd'                 , 'off');

$dnd_mode = 'off';
$cf = gs_callforward_get( $user['user'] );
if (! isGsError($cf) && is_array($cf)) {
	if ( @$cf['internal']['always']['active'] != 'no'
	  || @$cf['external']['always']['active'] != 'no' )
	{
		$dnd_mode = 'on';
	}
}
setting('dnd_mode'               , $dnd_mode, true);
setting('dnd_on_code'            , 'dnd-on');
setting('dnd_off_code'           , 'dnd-off');
setting('preselection_nr'        , '');


#
# Redirection
#
setting('redirect_event'      , 'none');  # Umleitung nicht auf dem Tel. machen
setting('redirect_number'     , '');
setting('redirect_busy_number', '');
setting('redirect_time_number', '');
setting('redirect_always_on_code' , '');
setting('redirect_always_off_code', '');
setting('redirect_busy_on_code'   , '');
setting('redirect_busy_off_code'  , '');
setting('redirect_time_on_code'   , '');
setting('redirect_time_off_code'  , '');


#
# Time
#
//setting('ntp_server'       , '192.168.1.11');  # dem DHCP überlassen
setting('ntp_refresh_timer'  , rand(1780,1795));  # default 3600
setting('timezone'           , 'GER+1', true);
//setting('utc_offset'         , date('Z'), true);  # no need to set this


#
# Update
#
setting('update_policy', 'settings_only');


#
# Account 1
#
//setting('user_host1'               , '192.168.1.11');
setting('user_active1'             , 'on');
setting('user_sipusername_as_line1', 'on' );  # "broken registrar"
setting('user_srtp1'               , 'off');  # keine Verschluesselung
setting('user_symmetrical_rtp1'    , 'off');
setting('user_expiry1'             , '70' );  # neu registrieren, default: 86400
setting('ring_after_delay1'        , '1'  , true);  # mit 1 Sek. Verzögerung klingeln
//setting('user_send_local_name1'    , 'on' );  # send display name to caller
setting('user_send_local_name1'    , 'off');
setting('user_dtmf_info1'          , 'off');
setting('user_mailbox1'            , 'mailbox');
setting('user_dp_str1'             , ''   );
setting('keepalive_interval1'      , '14' );

setting('codec1_name1', '8');  # alaw (g711a)
setting('codec2_name1', '0');  # ulaw (g711u)
setting('codec3_name1', '3');  # gsm (full rate)
setting('codec4_name1', '2');  # g726-32
setting('codec5_name1', '9');  # g722
setting('codec6_name1','18');  # g729a
setting('codec7_name1', '4');  # g723.1
setting('codec_size1' ,'20');  # 20 ms

#
# andere Accounts
#
for ($i=2; $i<=12; ++$i) {
	setting('user_active'.$i , 'off', true);
}

#
# Account 1 als aktiv setzen
#
setting('active_line' , '1', true);

#
# Kurzwahlen löschen
#
for ($i=0; $i<=32; ++$i) {
	setting('speed'. $i, '');
}

#
# Debug
#
setting('flood_tracing', 'on');
setting('log_level'    , '5' );  # 0-9, Default: 5


#
# Snom-Sondertasten
#
setting('dkey_help'     , 'keyevent F_HELP'      );
//setting('dkey_snom'     , 'url http://192.168.1.11/snom/webapps/menu.xml');
setting('dkey_snom'     , 'keyevent F_SNOM'      );
setting('dkey_conf'     , 'keyevent F_CONFERENCE');
setting('dkey_transfer' , 'keyevent F_TRANSFER'  );
setting('dkey_hold'     , 'keyevent F_R'         ); # or F_HOLD
setting('dkey_dnd'      , 'keyevent F_DND'       );
setting('dkey_record'   , 'keyevent F_REC'       );
setting('dkey_directory', 'keyevent F_ADR_BOOK'  ); # or F_DIRECTORY
setting('dkey_menu'     , 'keyevent F_MENU'      );
//setting('dkey_directory', 'url http://192.168.1.11/snom/webapps/simplebook/simplebook.php');
setting('dkey_directory', 'url '. GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT==80 ? '' : (':'. GS_PROV_PORT)) . GS_PROV_PATH .'snom/pb.php?m=$mac&u=$user_name1');
setting('dkey_redial', 'url '. GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT==80 ? '' : (':'. GS_PROV_PORT)) . GS_PROV_PATH .'snom/dial-log.php?user=$user_name1');
# so geht die Retrieve-Taste auch ohne neue Nachrichten:
setting('dkey_retrieve', 'speed mailbox');


#
# Action URLs for building Redial menu
#
//setting('action_incoming_url', 'http://192.168.1.11/snom/dial-log.php?user=$user_name1&type=in&remote=$remote');
//setting('action_outgoing_url', 'http://192.168.1.11/snom/dial-log.php?user=$user_name1&type=out&remote=$remote');
//setting('action_missed_url', 'http://192.168.1.11/snom/dial-log.php?user=$user_name1&type=missed&remote=$remote');
setting('action_incoming_url', '');
setting('action_outgoing_url', '');
setting('action_missed_url'  , '');


#
# Account 1 aus der DB
#
setting('user_host1'          , $host);
setting('user_outbound1'      , '');  # outbound SIP proxy
setting('user_proxy_require1' , '');
setting('user_shared_line1'   , 'off');
setting('user_name1'          , $user['name']);
setting('user_pname1'         , $user['name']);
//setting('user_pname1'         , $user['name']);  # not needed for Asterisk
setting('user_pass1'          , $user['secret']);
setting('user_realname1'      , $user['callerid']);
setting('user_idle_text1'     , $user['name'] .' '. mb_subStr($user['firstname'],0,1) .'. '. $user['lastname']);
setting('record_missed_calls1', 'off');


#
# Keys
#
# reset all keys
#
for ($i=0; $i<=53; ++$i) {
	setting('fkey'. $i, 'line');
	setting('fkey_context'. $i, 'active');
}

# keys for pickup groups
#

$rs = $db->execute( 'SELECT DISTINCT(`p`.`id`) `id` FROM `pickupgroups_users` `pu` JOIN `pickupgroups` `p` ON (`p`.`id`=`pu`.`group_id`) WHERE `pu`.`user_id`='. $user_id .' ORDER BY `p`.`id` LIMIT 6' );
$key = 6;
while ($r = $rs->fetchRow()) {
	setting('fkey'. $key, 'dest <sip:*8'. str_pad($r['id'], 5, '0', STR_PAD_LEFT) .'@'. $host .'>');
	//setting('fkey_context'. $key, '1');
	setting('fkey_context'. $key, 'active');
	++$key;
}

$keys = gs_keys_snom_get( $user['user'] );
if (is_array($keys)) {
	foreach ($keys as $kname => $kinfo) {
		if (! preg_match('/^f(\d{1,2})$/S', $kname, $m)) continue;
		if (trim(@$kinfo['val']) != '')
			setting('fkey'.@$m[1], 'dest <sip:'. @$kinfo['val'] .'@'. $host .'>');
	}
}

/*
setting('gui_fkey1', 'F_DIALOG');
setting('gui_fkey2', 'F_CALL_LIST');
setting('gui_fkey3', 'F_ADR_BOOK');
setting('gui_fkey4', 'F_PRESENCE');
*/
setting('gui_fkey1', '');
setting('gui_fkey2', '');
setting('gui_fkey3', '');
setting('gui_fkey4', '');


#
# Klingeltöne
#
setting('alert_internal_ring_text', 'alert-internal');
setting('alert_external_ring_text', 'alert-external');
setting('alert_group_ring_text'   , 'alert-group');

# eigener Klingelton könnte so gesetzt werden statt per Alert-Info-Header:
//setting('custom_melody_url', 'http://...');
# ist aber nur möglich für "Adressbuchklingeltöne"!?

# Standard Fallback-Klingelton:
setting('ring_sound'               , 'Ringer1');  # Ringer[1-10] / Silent

# Alert-Info-Klingeltöne:
setting('alert_internal_ring_sound', 'Ringer2');  # Alert-Info: alert-internal
setting('alert_external_ring_sound', 'Ringer3');  # Alert-Info: alert-external
setting('alert_group_ring_sound'   , 'Ringer4');  # Alert-Info: alert-group

# Adressbuchklingeltöne (wir benutzen nicht das Telefon-interne Telefonbuch, diese Einstellungen werden also nicht benutzt):
setting('friends_ring_sound'       , 'Ringer1');
setting('family_ring_sound'        , 'Ringer1');
setting('colleagues_ring_sound'    , 'Ringer1');
setting('vip_ring_sound'           , 'Ringer1');



//setting('user_alert_info1', 'http://skdjhsjd.wav');
// "The HTTP(S) URL may point to a standard 8 kHz mono 16-bit sample WAV file."


# Misc
#

//setting('subscribe_config', 'off');  # "The phone can subscribe to setting changes delivered via SIP when this option is switched to "on"."
//setting('pnp_config', 'off');  # "If turned to on, the phone will try to retrieve its settings via a Plug-and-Play (PnP) Server. Modern SIP PBXs/Proxys can provide the PnP configuration data for the snom phones. Please refer to the manual of your PBX/Proxy. If the PnP configuration fails, the phone will try to get the settings from a setting server)."
setting('subscribe_config', 'off');
setting('pnp_config'      , 'off');


# call waiting (Anklopfen) aktiviert?
#
//setting('call_waiting', 'off');  # ""Call Waiting (CW)" can be enabled ("on", "visual only", "ringer") or disabled ("off")."
$callwaiting = (int)$db->executeGetOne( 'SELECT `active` FROM `callwaiting` WHERE `user_id`='. $user_id );
setting('call_waiting', ($callwaiting ? 'visual only' : 'off'));


setting('call_completion', 'off');
/*
Note: Apparently, the Call completion feature of the SNOM 360 interferes
with subscriptions to Asterisk. Call completion also uses subscriptions
to monitor the called peers state, which Asterisk misunderstands. As
soon as a subscribed extension is dialed from the SNOM 360, Asterisk
ends the subscription (stating timeout). I experienced this with a SNOM
360 w/ firmware 6.3 and Asterisk 1.2.9.1-BRIstuffed-0.3.0-PRE-1r

I experienced this with standard Asterisk (not BriStuff) 1.4.
Fixed? See
http://bugs.digium.com/view.php?id=6728
http://bugs.digium.com/view.php?id=6740
-- Philipp
*/

setting('peer_to_peer_cc', 'off');

setting('dual_audio_handsfree', 'off', true);

setting('show_call_status', 'off');
# if turned on the call progress is shown in the headline of the call
# progress window e.g. (100 Trying, 180 Ringing etc).

setting('presence_state1', 'online', true);

setting('show_local_line', 'off');


# Firmware
#
# see
# http://www.snom.com/wiki/index.php/Settings/firmware_interval
# http://www.snom.com/wiki/index.php/Settings/firmware_status
# http://www.snom.com/wiki/index.php/Mass_deployment#Firmware_configuration_file


settings_out();

/*
$fh = fOpen( './access.txt', 'ab' );
fWrite( $fh, "access at ". date('Y-m-d H:i:s') ." ?". $_SERVER['QUERY_STRING'] ."\n" );
fClose( $fh );
*/

?>