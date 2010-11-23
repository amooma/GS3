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

# have a look at
# http://wiki.snom.com/Settings

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
require_once( GS_DIR .'inc/langhelper.php' );
set_error_handler('err_handler_die_on_err');

function _snom_astlang_to_snomlang( $langcode )
{
	$lang_default = "Deutsch";

	$lang_transtable = Array(
		"de" => "Deutsch",
		"en" => "English(US)",
		"us" => "English(US)",
	);

	$lang_ret = $lang_transtable[$langcode];
	if(strlen($lang_ret) == 0)
		return $lang_default;

	return $lang_ret;
}

function _snom_normalize_version( $appvers )
{
	$tmp = explode('.', $appvers);
	$vmaj = str_pad((int)@$tmp[0], 2, '0', STR_PAD_LEFT);
	$vmin = str_pad((int)@$tmp[1], 2, '0', STR_PAD_LEFT);
	$vsub = str_pad((int)@$tmp[2], 2, '0', STR_PAD_LEFT);
	return $vmaj.'.'.$vmin.'.'.$vsub;
}

function _snomAppCmp( $appv1, $appv2 )
{
	//$appv1 = _snom_normalize_version( $appv1 );  # we trust it has been normalized!
	$appv2 = _snom_normalize_version( $appv2 );
	return strCmp($appv1, $appv2);
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
	@ob_end_clean();
	@ob_start();
	echo '<!-- // ', _snomCnfXmlEsc($msg != '' ? str_replace('--','- -',$msg) : 'Error') ,' // -->',"\n";
	if (! headers_sent()) {
		header( 'Content-Type: text/plain; charset=utf-8' );
		# the Content-Type header is ignored by the Snom
		header( 'Content-Length: '. (int)@ob_get_length() );
	}
	@ob_end_flush();
	exit(1);
}

if (! gs_get_conf('GS_SNOM_PROV_ENABLED')) {
	gs_log( GS_LOG_DEBUG, "Snom provisioning not enabled" );
	_settings_err( 'Not enabled.' );
}


$requester = gs_prov_check_trust_requester();
if (! $requester['allowed']) {
	_settings_err( 'No! See log for details.' );
}

$mac = preg_replace( '/[^0-9A-F]/', '', strToUpper( @$_REQUEST['mac'] ) );
if (strLen($mac) !== 12) {
	gs_log( GS_LOG_NOTICE, "Snom provisioning: Invalid MAC address \"$mac\" (wrong length)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}
if (hexDec(subStr($mac,0,2)) % 2 == 1) {
	gs_log( GS_LOG_NOTICE, "Snom provisioning: Invalid MAC address \"$mac\" (multicast address)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}
if ($mac === '000000000000') {
	gs_log( GS_LOG_NOTICE, "Snom provisioning: Invalid MAC address \"$mac\" (huh?)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}

# make sure the phone is a Snom:
#
if (subStr($mac,0,6) !== '000413') {
	gs_log( GS_LOG_NOTICE, "Snom provisioning: MAC address \"$mac\" is not a Snom phone" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}

$ua = trim( @$_SERVER['HTTP_USER_AGENT'] );
if (! preg_match('/^Mozilla/i', $ua)
||  ! preg_match('/snom[0-9]{3}/i', $ua) ) {
	gs_log( GS_LOG_WARNING, "Phone with MAC \"$mac\" (Snom) has invalid User-Agent (\"". $ua ."\")" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}
if (preg_match('/^Mozilla\/\d\.\d\s*\(compatible;\s*/i', $ua, $m)) {
	$ua = rTrim(subStr( $ua, strLen($m[0]) ), ' )');
}

# find out the type of the phone:
# user-agents:
# 360: "Mozilla/4.0 (compatible; snom360-SIP 6.5.2; snom360 ramdisk v3.31; snom360 linux 3.25)"
# 370: "Mozilla/4.0 (compatible; snom370-SIP 7.1.2)"
if (preg_match('/snom([1-9][0-9]{2})/i', $ua, $m))  # e.g. "snom360"
	$phone_model = $m[1];
else
	$phone_model = 'unknown';

$phone_type = 'snom-'.$phone_model;  # e.g. "snom-360"
# to be used when auto-adding the phone

$fw_vers = (preg_match('/snom[0-9]{3}-SIP\s+(\d+\.\d+\.\d+)/', $ua, $m))
	? $m[1] : '0.0.0';
$fw_vers_nrml = _snom_normalize_version( $fw_vers );

gs_log( GS_LOG_DEBUG, "Snom phone \"$mac\" asks for settings (UA: ...\"$ua\") - model: $phone_model" );

$prov_url_snom = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'snom/';


require_once( GS_DIR .'inc/db_connect.php' );
require_once( GS_DIR .'inc/nobody-extensions.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callforward_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_keys_get.php' );
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

function _add_to_cat( &$cats, $setting_name, $line )
{
	switch ($setting_name) {
		case 'fkey'      :  $cat = 'function-keys'    ; break;
		case '_gui_lang' :  $cat = 'gui-languages'    ; break;
		case '_web_lang' :  $cat = 'web-languages'    ; break;
		//case 'phrases'   :  $cat = 'phrases'          ; break;
		//case 'phonebook' :  $cat = 'phone-book'       ; break;
		//case 'dialplan'  :  $cat = 'dialplan'         ; break;
		//case 'firmware'  :  $cat = 'firmware-settings'; break;
		case 'firmware'  :  $cat = ''                 ; break;
		default          :  $cat = 'phone-settings'   ;
	}
	if ($cat != '') {
		if (! array_key_exists($cat, $cats)) $cats[$cat] = array();
		$cats[$cat][] = $line;
	}
}

function _settings_out()
{
	global $settings, $fw_vers_nrml, $prov_url_snom;
	
	if (_snomAppCmp($fw_vers_nrml, '7.1.8') <0)
	{
		header( 'Content-Type: text/plain; charset=utf-8' );
		# the Content-Type header is ignored by the Snom
		
		foreach ($settings as $name => $a1) {
			if (subStr($name,0,1) === '_') continue;
			
			if (! array_key_exists('_is_array', $a1)) {
				echo $name, ($a1['w'] ? '$':'&'), ': ', $a1['v'], "\n";
			} else {
				if ($name !== 'fkey') {
					foreach ($a1 as $idx => $a2) {
						if ($idx === '_is_array') continue;
						
						echo $name,$idx, ($a2['w'] ? '$':'&'), ': ', $a2['v'], "\n";
					}
				} else {
					foreach ($a1 as $idx => $a2) {
						if ($idx === '_is_array') continue;
						
						echo $name,$idx, ($a2['w'] ? '$':'&'), ': ', $a2['v'], "\n";
						if (is_array($a2['a'])
						&&  array_key_exists('context', $a2['a']))
						{
							echo $name,'_context',$idx, ($a2['w'] ? '$':'&'), ': ', $a2['a']['context'], "\n";
						}
					}
				}
			}
			/*
				! means writeable by the user, but will not overwrite existing
				$ means writeable by the user, but will overwrite existing (available since version 4.2)
				& (or no flag) means read only, but will overwrite existing
			*/
		}
		unset($settings);
	} else
	{
		header( 'Content-Type: application/xml; charset=utf-8' );
		echo '<','?xml version="1.0" encoding="utf-8"?','>', "\n";
		echo '<settings>', "\n\n";
		
		$xml_cats = array();
		foreach ($settings as $name => $a1) {
			if (! array_key_exists('_is_array', $a1)) {
				$attrstr = '';
				if (is_array($a1['a'])) {
					foreach ($a1['a'] as $attr => $attrval) {
						$attrstr .= ' '.$attr.'="'._snomCnfXmlEsc($attrval).'"';
					}
				}
				$line = '<'.$name. $attrstr.' perm="'. ($a1['w'] ? 'RW':'R') .'">'. _snomCnfXmlEsc( $a1['v'] ) .'</'.$name.'>';
				_add_to_cat( $xml_cats, $name, $line );
			} else {
				if ($name==='_gui_lang' || $name==='_web_lang') {
					foreach ($a1 as $idx => $a2) {
						if ($idx === '_is_array') continue;
						
						$attrstr = '';
						if (is_array($a2['a'])) {
							foreach ($a2['a'] as $attr => $attrval) {
								$attrstr .= ' '.$attr.'="'._snomCnfXmlEsc($attrval).'"';
							}
						}
						$line = '<language url="'. $prov_url_snom .'sw/'. _snomCnfXmlEsc( $a2['v'] ) .'" name="'. _snomCnfXmlEsc( $idx ) .'"'. $attrstr.' />';
						
						$langfile      = GS_DIR .'htdocs/prov/snom/sw/'.$a2['v'];
						$langfile_lock = $langfile.'.lock';
						$langfiles_dir = dirName($langfile).'/';
						$langfile_url  = 'http://provisioning.snom.com/config/snom'.$a2['v'];
						
						if (file_exists($langfile)
						&&  is_readable($langfile)
						&&  file_exists($langfile.'.done'))
						{
							//gs_log(GS_LOG_DEBUG, '---1');
							_add_to_cat( $xml_cats, $name, $line );
						} else
						{
							/*
							gs_log(GS_LOG_DEBUG, '---2');
							if (! file_exists($langfiles_dir)) {
								gs_log(GS_LOG_DEBUG, '---3');
								@exec( 'sudo mkdir -p '. qsa($langfiles_dir) .' 1>>/dev/null 2>>/dev/null' );
							}
							if (file_exists($langfiles_dir)) {
								gs_log(GS_LOG_DEBUG, '---4');
								$err=0; $out=array();
								@exec( 'sudo touch '. qsa($langfile_lock) .' && sudo chmod 666 '. qsa($langfile_lock) .' 1>>/dev/null 2>>/dev/null', $out, $err );
								if ($err==0) {
									gs_log(GS_LOG_DEBUG, '---5');
									$lockfh = @fOpen( $langfile_lock, 'wb' );
									if ($lockfh) {
										gs_log(GS_LOG_DEBUG, '---6');
										$would_block = false;
										if (@flock($lockfh, LOCK_SH, $would_block)) {
											gs_log(GS_LOG_DEBUG, '---7');
											if (! $would_block) {
												gs_log(GS_LOG_DEBUG, '---8');
												gs_log( GS_LOG_NOTICE, 'Trying to wget '. qsa($langfile_url) );
												@exec( 'sudo cd '. qsa($langfiles_dir) .' && sudo wget -q -a /dev/null -t 1 -T 300 -nc -c '. qsa($langfile_url) .' && sudo touch '. qsa($langfile.'.done') .' 1>>/dev/null 2>>/dev/null &' );
											}
											@flock($lockfh, LOCK_UN);
										}
										@fClose($lockfh);
									}
								}
							}
							*/
							gs_log( GS_LOG_NOTICE, 'Please  cd '. qsa($langfiles_dir) .' && wget '. qsa($langfile_url) .' && touch '. qsa(baseName($langfile).'.done') );
						}
					}
				} else {
					foreach ($a1 as $idx => $a2) {
						if ($idx === '_is_array') continue;
						
						$attrstr = '';
						if (is_array($a2['a'])) {
							foreach ($a2['a'] as $attr => $attrval) {
								$attrstr .= ' '.$attr.'="'._snomCnfXmlEsc($attrval).'"';
							}
						}
						$line = '<'.$name. $attrstr.' idx="'.$idx.'" perm="'. ($a2['w'] ? 'RW':'R') .'">'. _snomCnfXmlEsc( $a2['v'] ) .'</'.$name.'>';
						_add_to_cat( $xml_cats, $name, $line );
					}
				}
			}
		}
		unset($settings);
		foreach ($xml_cats as $cat => $lines) {
			echo "\t", '<',$cat,'>' ,"\n";
			foreach ($lines as $line) {
				echo $line, "\n";
			}
			echo "\t", '</',$cat,'>' ,"\n\n";
		}
		unset($xml_cats);
		
		echo '</settings>', "\n";
	}
}



$db = gs_db_master_connect();
if (! $db) {
	gs_log( GS_LOG_WARNING, "Snom phone asks for settings - Could not connect to DB" );
	_settings_err( 'Could not connect to DB.' );
}

/*

asterisk login (zb *991234):
- in db nachsehen, von welchem sip-acct der user kommt
- da die mac addr loeschen und bei seinem acct eintragen
- reboot senden

asterisk logout:
- in db nachsehen, von welchem sip-acct der user kommt
- die mac addr loeschen
- reboot senden

nach reboot (-> php skript):
- nachsehen, bei welchem sip acct die mac addr eingetr ist
falls gefunden:
- provision senden
falls nicht:
- mac addr bei einem unbenutzten nobody eintragen
  (_user_id=NULL, regseconds < time()-24*3600)
- secret aendern
- provision senden

*/



# do we know the phone?
#
$user_id = @gs_prov_user_id_by_mac_addr( $db, $mac );
if ($user_id < 1) {
	if (! GS_PROV_AUTO_ADD_PHONE) {
		gs_log( GS_LOG_NOTICE, "New phone $mac not added to DB. Enable PROV_AUTO_ADD_PHONE" );
		_settings_err( 'Unknown phone. (Enable PROV_AUTO_ADD_PHONE in order to auto-add)' );
	}
	gs_log( GS_LOG_NOTICE, "Adding new Snom phone $mac to DB" );
	
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

$user_snomlang = @_snom_astlang_to_snomlang($user['language']);

# change the ip account's secret for improved security and to kick
# phones who did not get a reboot
#
# EDIT: don't do that! -> race condition
# the Snoms try to authenticate with their old account after reboot
# before they fetch their new settings
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
#  General
#####################################################################

psetting('language'         , $user_snomlang, true);
psetting('web_language'     , $user_snomlang, true);
psetting('display_method'   , 'display_name_number');
psetting('tone_scheme'      , 'GER'    );
psetting('date_us_format'   , 'off'    , true);
psetting('time_24_format'   , 'on'     , true);
psetting('message_led_other', 'on'     );
psetting('use_backlight'    , 'on'     , true);  # always | on | off | dim (370, >= 7.1.33)
//psetting('headset_device'   , 'headset_rj', true);  # wuerde Default auf Headset am RJ14-Stecker setzen
//psetting('headset_device'   , 'none'   , true);  # setting this would disable the headset even during a call
psetting('ethernet_detect'  , 'on'     );  # Warnung falls kein Ethernet
psetting('ethernet_replug'  , 'reboot' );
psetting('reboot_after_nr'  , '5'      );  # nach 5 Min. ohne Registrierung neu starten
psetting('admin_mode'       , 'off'    , true);  # wenn die Einstellung nicht writable ist, ist auch kein Admin-Login moeglich
psetting('admin_mode_password'         , '0000');
psetting('admin_mode_password_confirm' , '0000');
psetting('ignore_security_warning', 'on');



#####################################################################
#  Network / Advanced Network / SIP
#####################################################################

psetting('dhcp'                 , 'on' );
//psetting('netmask'              , '255.255.255.0'); # leave it up to the DHCP
//psetting('gateway'              , '192.168.1.1');
psetting('filter_registrar'     , 'off');  # so we can reboot the phone even if not registered
psetting('enable_timer_support' , 'on' );
psetting('timer_support'        , 'on' );
psetting('session_timer'        , '140');  # default: 3600
psetting('retry_after_failed_register', '70' );  # in seconds, default: 300
psetting('dirty_host_ttl'       , '0'  );
psetting('challenge_response'   , 'off');
psetting('challenge_reboot'     , 'off');
psetting('challenge_checksync'  , 'off');
//psetting('network_id_port'      , '5060');  # fixed setting of 5060 does not work in
                                              # some VLANs
psetting('network_id_port'      , '5060');  # necessary so we can send custom messages
                                            # to the phone
psetting('tcp_listen'           , 'off');
psetting('offer_gruu'           , 'off');
psetting('short_form'           , 'on' );  # kurze SIP-Header verwenden
psetting('subscription_delay'   , '2'  );
psetting('subscription_expiry'  , '80' );  # default: 3600
psetting('terminate_subscribers_on_reboot', 'on');
psetting('publish_presence'     , 'off');  # unterstuetzt Asterisk nicht
psetting('presence_timeout'     , '15' );  # default: 15 (minutes)
psetting('sip_retry_t1'         , '900');  # default: 500 (ms)
psetting('user_phone'           , 'off');  # user=phone in SIP URIs is deprecated
psetting('require_prack'        , 'off');  # default: on
psetting('send_prack'           , 'off');  # default: on
psetting('refer_brackets'       , 'off');  # default: off
psetting('encode_display_name'  , 'on' );
psetting('offer_mpo'            , 'on' );  # default: off
psetting('register_http_contact', 'off');
psetting('support_rtcp'         , 'on' );  # default: on
psetting('signaling_tos'        , '160');  # default: 160, 160 = CS 5
psetting('codec_tos'            , '184');  # default: 160, 184 = EF
psetting('dtmf_payload_type'    , '101');  # default: 101
psetting('sip_proxy'            , $sip_proxy_and_sbc['sip_proxy_from_wan'] );
psetting('emergency_proxy'      , $sip_proxy_and_sbc['sip_proxy_from_wan'] );
psetting('eth_net'              , 'auto');
psetting('eth_pc'               , 'auto');
psetting('redirect_ringing'     , 'off' );
psetting('disable_blind_transfer', 'off');
psetting('disable_deflection'   , 'off');
psetting('watch_arp_cache'      , '1'  );  # default: 0
psetting('max_forwards'         , '30' );  # default: 70
psetting('support_idna'         , 'off');
psetting('reject_calls_with_603', 'off');  # rejects calls with 603 instead of 486
psetting('naptr_sip_uri'        , 'off');
psetting('rtp_port_start'       , '16384');
psetting('rtp_port_end'         , '32767');
psetting('rtp_keepalive'        , 'on' );
/*
if ($vlan_id < 1) {
	psetting('vlan', '' );
} else {
	psetting('vlan', $vlan_id .' '. '5' );  # Prio. 5 (0|1-7)
}
*/
psetting('vlan_port_tagging', 'off' );

# SNMP
# see http://wiki.snom.com/SNMP
psetting('snmp_port'            , '161');  # default: 161
# allowed client addresses in CIDR or dotted decimal notation, multiple
# entries separated by space. e.g. "192.168.0.0/16":
psetting('snmp_trusted_addresses', '');

# multicast (for multicast paging and text messages? as an alternative
# to finding the provisioning server via DHCP?)
psetting('multicast_listen'     , 'off');
psetting('multicast_address'    , ''   );  # sip.mcast.net = 224.0.1.75
psetting('multicast_port'       , ''   );



#####################################################################
#  Web-Server
#####################################################################

psetting('webserver_type'  , 'http_https');
psetting('http_scheme'     , 'off');  # off = Basic, on = Digest
psetting('http_user'       , gs_get_conf('GS_SNOM_PROV_HTTP_USER', '') );
psetting('http_pass'       , gs_get_conf('GS_SNOM_PROV_HTTP_PASS', '') );
psetting('http_port'       , '80' );
psetting('https_port'      , '443');
psetting('web_logout_timer', '10' );
psetting('with_flash'      , 'off' , true);

psetting('http_proxy'      , '' );  # IP address or URL
psetting('http_client_user', '' );
psetting('http_client_pass', '' );



#####################################################################
#  Audio
#####################################################################

psetting('pickup_indication'    , 'on' );  # Piepton wenn Pickup moeglich
psetting('keytones'             , 'on' );
psetting('holding_reminder'     , 'on' );
psetting('alert_info_playback'  , 'on' );
psetting('mute'                 , 'off', true);  # mute mic off
psetting('disable_speaker'      , 'off', true);  # disable casing speaker off
psetting('dtmf_speaker_phone'   , 'off', true);
psetting('release_sound'        , 'off');
if ($phone_model >= '370') {
	psetting('vol_handset_mic'      ,  '5' , true);  # 1 - 8, Default: 4
	psetting('vol_headset_mic'      ,  '6' , true);  # 1 - 8, Default: 4
	psetting('vol_speaker_mic'      ,  '4' , true);  # 1 - 8, Default: 4
	psetting('vol_speaker'          ,  '5' , true);  # 0 - 15, Default: 8
	psetting('vol_handset'          , '10' , true);  # 0 - 15, Default: 8
	psetting('vol_headset'          , '10' , true);  # 0 - 15, Default: 8
	psetting('vol_ringer'           ,  '8' , true);  # 1 - 15
} else {
	psetting('vol_handset_mic'      ,  '4' , true);  # 1 - 8, Default: 4
	psetting('vol_headset_mic'      ,  '6' , true);  # 1 - 8, Default: 4
	psetting('vol_speaker_mic'      ,  '6' , true);  # 1 - 8, Default: 4
	psetting('vol_speaker'          , '10' , true);  # 0 - 15, Default: 8
	psetting('vol_handset'          , '11' , true);  # 0 - 15, Default: 8
	psetting('vol_headset'          , '10' , true);  # 0 - 15, Default: 8
	psetting('vol_ringer'           ,  '7' , true);  # 1 - 15
}
psetting('mwi_notification'     , 'silent');  # keine akustischen Hinweise wenn neue Nachrichten
psetting('mwi_dialtone'         , 'stutter');  # stotternder Waehlton wenn neue Nachrichten
psetting('silence_compression'  , 'off');  # kann Asterisk (noch?) nicht
psetting('ringer_headset_device', 'speaker');  # Klingeltonausgabe bei Kopfhoerer (headset|speaker)



#####################################################################
#  Behavior
#####################################################################

psetting('callpickup_dialoginfo'  , 'on' );
psetting('show_xml_pickup'        , 'on' );
psetting('show_name_dialog'       , 'off');
psetting('ringing_time'           , '500');  # wird im Dialplan begrenzt
psetting('block_url_dialing'      , 'on' );  # nur Ziffern erlauben
psetting('ringer_animation'       , 'on' , true);
psetting('xml_notify'             , 'on' );
psetting('redundant_fkeys'        , 'off', true);
psetting('text_softkey'           , 'off');
psetting('edit_alpha_mode'        , '123', true);
psetting('overlap_dialing'        , 'off');
psetting('auto_logoff_time'       , ''   );
psetting('enable_keyboard_lock'   , 'on' );
psetting('keyboard_lock'          , 'off');
psetting('keyboard_lock_pw'       , ''   );
psetting('keyboard_lock_emergency', '911 112 110 999 19222');  # default
psetting('ldap_server'            , ''   );
psetting('answer_after_policy'    , 'idle');
psetting('call_join_xfer'         , 'off');
psetting('guess_number'           , 'off');
psetting('partial_lookup'         , 'off');
psetting('deny_all_feature'       , 'off');  # keine Telefon-interne Blacklist
psetting('audio_device_indicator' , 'on' );
psetting('intercom_enabled'       , 'off');  # brauchen wir (noch?) nicht
psetting('cmc_feature'            , 'off');
psetting('cancel_on_hold'         , 'on' );
psetting('cancel_missed'          , 'on' );
psetting('cancel_desktop'         , 'off');
psetting('cw_dialtone'            , 'on' , true);
psetting('auto_connect_indication', 'on' );
psetting('auto_connect_type'      , 'auto_connect_type_handsfree');
psetting('privacy_in'             , 'off');  # accept anonymous calls
psetting('privacy_out'            , 'off');  # send caller-id
psetting('auto_dial'              , 'off');
psetting('conf_hangup'            , 'on' , true);
psetting('auto_redial'            , 'off');  # automatic redial on busy
psetting('auto_redial_value'      , '10' );  # redial after (sec)
psetting('idle_offhook'           , 'off');
psetting('speaker_receive_call'   , 'on' );
psetting('global_missed_counter'  , 'on' );
psetting('transfer_on_hangup'     , 'on' );
psetting('aoc_amount_display'     , 'off');  # off | charged | balance
psetting('aoc_pulse_currency'     , ''   );  # e.g. "EUR" | "USD" | "$"
psetting('aoc_cost_pulse'         , '1'  );  # e.g. 0.02
psetting('max_boot_delay'         , '0'  );  # ? in seconds, default: 0
psetting('mailbox_active'         , 'off');  # pay attention to the mailbox of the
                                             # active identity only?
psetting('speaker_dialer'         , 'on' );
psetting('no_dnd'                 , 'off');
$dnd_mode = 'off';
$cf = gs_callforward_get( $user['user'] );
if (! isGsError($cf) && is_array($cf)) {
	if ( @$cf['internal']['always']['active'] != 'no'
	  || @$cf['external']['always']['active'] != 'no' )
	{
		$dnd_mode = 'on';  //FIXME - bad hack!
	}
}
psetting('dnd_mode'               , $dnd_mode, true);
psetting('dnd_on_code'            , 'dnd-on');
psetting('dnd_off_code'           , 'dnd-off');
psetting('preselection_nr'        , '');



#####################################################################
#  Redirection
#####################################################################

psetting('redirect_event'      , 'none');  # Umleitung nicht auf dem Tel. machen
psetting('redirect_number'     , '');
psetting('redirect_busy_number', '');
psetting('redirect_time_number', '');
psetting('redirect_always_on_code' , '');
psetting('redirect_always_off_code', '');
psetting('redirect_busy_on_code'   , '');
psetting('redirect_busy_off_code'  , '');
psetting('redirect_time_on_code'   , '');
psetting('redirect_time_off_code'  , '');
psetting('redirect_time'       , '');



#####################################################################
#  Time
#####################################################################

//psetting('ntp_server'       , '192.168.1.11');  # dem DHCP ueberlassen
psetting('ntp_refresh_timer'  , rand(1780,1795));  # default 3600
# a little random offset to avoid load spikes on the network and server
psetting('timezone'           , 'GER+1', true);
//psetting('utc_offset'         , date('Z'), true);  # no need to set this



#####################################################################
#  Account 1
#####################################################################

$i = 1;
setting('user_active'             ,$i, 'on' );
setting('user_sipusername_as_line',$i, 'on' );  # "broken registrar"
setting('user_srtp'               ,$i, 'off');  # keine Verschluesselung
setting('user_symmetrical_rtp'    ,$i, 'off');
setting('user_expiry'             ,$i, '60' );  # default: 3600,
                                                # valid: 60, 600, 3600, 7200, 28800, 86400
setting('user_subscription_expiry',$i, '60' );  # default: 3600
setting('user_server_type'        ,$i, ((_snomAppCmp($fw_vers_nrml, '7.3.2') >0) ? 'asterisk':'default'));
# default = Standard, broadsoft = Broadsoft, sylantro = Sylantro,
# pbxnsip = PBXnSIP, telepo = Telepo, metaswitch = MetaSwitch,
# nortel = Nortel (since firmware 7.3.2), asterisk = Asterisk
# (since firmware 7.3.10)
setting('ring_after_delay'        ,$i, ''   );
setting('user_send_local_name'    ,$i, 'on' );  # send display name to caller
setting('user_dtmf_info'          ,$i, 'off');
setting('user_mailbox'            ,$i, 'mailbox');
setting('user_dp_exp'             ,$i, ''   );  # see http://wiki.snom.com/Settings/user_dp_exp
setting('user_dp_str'             ,$i, ''   );  # see http://wiki.snom.com/Dial_Plan
setting('user_dp'                 ,$i, ''   );
setting('user_q'                  ,$i, '1.0');
setting('user_descr_contact'      ,$i, 'off');  # not supported by Asterisk anyway
setting('user_ice'                ,$i, 'off');  # not supported by Asterisk anyway
setting('user_moh'                ,$i, ''   );
setting('user_auto_connect'       ,$i, 'off');
setting('user_remove_all_bindings',$i, 'off');
setting('user_alert_info'         ,$i, ''   );
setting('user_pic'                ,$i, ''   );
# sends Call-Info: icon="http://example.com/example.jpg" header
setting('keepalive_interval'      ,$i, '14' );
setting('user_full_sdp_answer'    ,$i, 'on' );
setting('user_failover_identity'  ,$i, 'none');
setting('user_xml_screen_url'     ,$i, ''   );
setting('user_event_list_subscription',$i, 'off');
setting('user_event_list_uri'         ,$i, '');
setting('user_presence_subscription'  ,$i, 'off');
setting('user_presence_host'          ,$i, '');
setting('user_presence_buddy_list_uri',$i, '');
setting('presence_state'              ,$i, 'online', null, true);
setting('stun_server'             ,$i, ''   );
setting('stun_binding_interval'   ,$i, ''   );

setting('codec1_name',$i, '8');  # g711a
setting('codec2_name',$i, '0');  # g711u
setting('codec3_name',$i, '3');  # gsm (full rate)
setting('codec4_name',$i, '2');  # g726-32
setting('codec5_name',$i, '9');  # g722
setting('codec6_name',$i,'18');  # g729a
setting('codec7_name',$i, '4');  # g723.1
setting('codec_size' ,$i,'20');  # 20 ms, valid values: 20, 30, 40, 60
# G.723.1 needs 30 or 60 ms. All other codecs work with 20, 40 and 60 ms only.

setting('user_host'          ,$i, $host);
setting('user_outbound'      ,$i, $sip_proxy_and_sbc['sip_proxy_from_wan']);
setting('user_proxy_require' ,$i, '');
setting('user_shared_line'   ,$i, 'off');
setting('user_name'          ,$i, $user_ext);
setting('user_pname'         ,$i, $user_ext);
setting('user_pass'          ,$i, $user['secret']);
//setting('user_hash'          ,$i, md5($user['secret']));
//setting('user_hash'          ,$i, md5($user_ext .':'. $host .':'. $user['secret']));
//setting('user_hash'          ,$i, md5($user_ext .':'. 'asterisk' .':'. $user['secret']));
setting('user_realname'      ,$i, $user['callerid']);
setting('user_idle_text'     ,$i, $user_ext .' '. mb_subStr($user['firstname'],0,1) .'. '. $user['lastname']);
setting('record_missed_calls'  ,$i, 'on' );
setting('record_dialed_calls'  ,$i, 'off');
setting('record_received_calls',$i, 'off');

# andere Accounts
#
for ($i=2; $i<=12; ++$i) {
	setting('user_active', $i , 'off', null, true);
}

# Account 1 als aktiv setzen
#
psetting('active_line' , '1');



#####################################################################
#  Kurzwahlen loeschen
#####################################################################

for ($i=0; $i<=32; ++$i) {
	setting('speed', $i, '');
}



#####################################################################
#  Debug
#####################################################################

psetting('flood_tracing', 'on');
psetting('log_level'    , '5' );  # Log level of the maintenance web page.
                                  # 9 is the most verbose mode. 0-9, Default: 5
psetting('syslog_server', ''  );
psetting('lcserver1'    , ''  );
psetting('vpn_netcatserver', ''  );



#####################################################################
#  Action URLs
#####################################################################

/*
# (for building redial menu)
psetting('action_incoming_url', 'http://192.168.1.11/snom/dial-log.php?user=$user_name1&mac=$mac&type=in&remote=$remote');
psetting('action_outgoing_url', 'http://192.168.1.11/snom/dial-log.php?user=$user_name1&mac=$mac&type=out&remote=$remote');
psetting('action_missed_url', 'http://192.168.1.11/snom/dial-log.php?user=$user_name1&mac=$mac&type=missed&remote=$remote');
*/
psetting('action_incoming_url'       , '');
psetting('action_outgoing_url'       , '');
psetting('action_missed_url'         , '');
psetting('action_dnd_on_url'         , '');
psetting('action_dnd_off_url'        , '');
psetting('action_redirection_on_url' , '');
psetting('action_redirection_off_url', '');
psetting('action_offhook_url'        , '');
psetting('action_onhook_url'         , '');
psetting('action_setup_url'          , '');
psetting('action_reg_failed'         , '');
psetting('action_connected_url'      , '');
psetting('action_disconnected_url'   , '');
psetting('action_log_on_url'         , '');
psetting('action_log_off_url'        , '');



#####################################################################
#  Snom-Sondertasten
#####################################################################

psetting('dkey_help'     , 'keyevent F_HELP'      );
psetting('dkey_snom'     , 'keyevent F_SNOM'      );
psetting('dkey_conf'     , 'keyevent F_CONFERENCE');
psetting('dkey_transfer' , 'keyevent F_TRANSFER'  );
psetting('dkey_hold'     , 'keyevent F_R'         ); # or F_HOLD
psetting('dkey_dnd'      , 'keyevent F_DND'       );
psetting('dkey_record'   , 'keyevent F_REC'       );
psetting('dkey_directory', 'keyevent F_ADR_BOOK'  ); # or F_DIRECTORY
psetting('dkey_menu'     , 'keyevent F_MENU'      );
psetting('dkey_redial'   , 'keyevent F_REDIAL'    );

psetting('dkey_directory', 'url '. $prov_url_snom .'pb.php?m=$mac&u=$user_name1');
psetting('dkey_redial'   , 'url '. $prov_url_snom .'dial-log.php?user=$user_name1&mac=$mac');
# so geht die Retrieve-Taste auch ohne neue Nachrichten:
psetting('dkey_retrieve' , 'speed voicemail');



#####################################################################
#  Keys
#####################################################################

# reset all keys
#
$max_key = 12+(42*3) -1;
/* //FIXME?
if ($phone_model == '300') {
	# the snom 300 has no function keys that should be configured
	# because it's keys are used for standard functions
	$max_key = -1;
}
*/
for ($i=0; $i<=$max_key; ++$i) {
	setting('fkey'        , $i, 'line', array('context'=>'active'));
	//setting('fkey_context', $i, 'active');
}

# pre-defined keys for snom 300
#
if ($phone_model == '300') {
	setting('fkey', 2, 'url '. $prov_url_snom .'dial-log.php?user=$user_name1&mac=$mac', array('context'=>'active'));
	setting('fkey', 3, 'url '. $prov_url_snom .'pb.php?m=$mac&u=$user_name1', array('context'=>'active'));
	setting('fkey', 4, 'keyevent F_TRANSFER', array('context'=>'active'));
	setting('fkey', 5, 'keyevent F_MUTE', array('context'=>'active'));
}

/*  //FIXME

# user defined keys
#
$keys = gs_keys_snom_get( $user['user'] );
if (is_array($keys)) {
	foreach ($keys as $kname => $kinfo) {
		if (! preg_match('/^f(\d{1,2})$/S', $kname, $m)) continue;
		if ((int)@$m[1] > $max_key) continue;
		if (trim(@$kinfo['val']) != '')
			setting('fkey',@$m[1], 'dest <sip:'. @$kinfo['val'] .'@'. $host .'>|*81*', array('context'=>'active'));
	}
}

# keys for pickup groups
#
$rs = $db->execute( 'SELECT DISTINCT(`p`.`id`) `id` FROM `pickupgroups_users` `pu` JOIN `pickupgroups` `p` ON (`p`.`id`=`pu`.`group_id`) WHERE `pu`.`user_id`='. $user_id .' ORDER BY `p`.`id` LIMIT 6' );
$key = 6;
while ($r = $rs->fetchRow()) {
	if ($key > $max_key) continue;
	setting('fkey',$key, 'dest <sip:*8*'. str_pad($r['id'], 5, '0', STR_PAD_LEFT) .'@'. $host .'>|*82', array('context'=>'active'));
	//psetting('fkey_context'. $key, '1');
	//psetting('fkey_context'. $key, 'active');
	++$key;
}
*/

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
		if ($key_def['function'] == "blf"){
			if (substr($key_def['data'], 0,1)  == '*' ) { 
				setting('fkey', $key_idx, $key_def['function'] .' <sip:'. $key_def['data'].'@'.$host.'>', array('context'=>'active'));
			} else {
				setting('fkey', $key_idx, $key_def['function'] .' <sip:'. $key_def['data'].'@'.$host.'>|*81*', array('context'=>'active'));
			}
		} else {
			setting('fkey', $key_idx, $key_def['function'] .' '. $key_def['data'], array('context'=>'active'));
		}
	}
}


# GUI softkeys
#
/*
psetting('gui_fkey1', 'F_DIALOG');
psetting('gui_fkey2', 'F_CALL_LIST');
psetting('gui_fkey3', 'F_ADR_BOOK');
psetting('gui_fkey4', 'F_PRESENCE');
*/
psetting('gui_fkey1', '');
psetting('gui_fkey2', '');
psetting('gui_fkey3', '');
psetting('gui_fkey4', '');
# these settings do not use the idx attribute in XML



#####################################################################
#  Klingeltoene
#####################################################################

psetting('alert_internal_ring_text', 'alert-internal');
psetting('alert_external_ring_text', 'alert-external');
psetting('alert_group_ring_text'   , 'alert-group');

# eigener Klingelton koennte so gesetzt werden statt per Alert-Info-Header:
//psetting('custom_melody_url', 'http://...');
# ist aber nur moeglich fuer "Adressbuchklingeltoene"!?
psetting('custom_melody_url', '');
setting('user_custom', 1, '');
# PCM 8 kHz 16 bit/sample (linear) mono WAV
# see http://wiki.snom.com/Settings/custom_melody_url for examples

# default fallback ringtone:
psetting('ring_sound'               , 'Ringer1');  # Ringer[1-10] / Silent
# default ringtone for identity 1:
setting('user_ringer', 1            , 'Ringer1');  # Ringer[1-10] / Silent / Custom

# Alert-Info-Klingeltoene:
psetting('alert_internal_ring_sound', 'Ringer2');  # Alert-Info: alert-internal
psetting('alert_external_ring_sound', 'Ringer3');  # Alert-Info: alert-external
psetting('alert_group_ring_sound'   , 'Ringer4');  # Alert-Info: alert-group

# Adressbuchklingeltoene (wir benutzen nicht das Telefon-interne Telefonbuch, diese Einstellungen werden also nicht benutzt):
psetting('friends_ring_sound'       , 'Ringer1');
psetting('family_ring_sound'        , 'Ringer1');
psetting('colleagues_ring_sound'    , 'Ringer1');
psetting('vip_ring_sound'           , 'Ringer1');



#####################################################################
#  Misc
#####################################################################

//psetting('subscribe_config', 'off');
# "The phone can subscribe to setting changes delivered via SIP when
# this option is switched to "on"."
//psetting('pnp_config', 'off');
# "If turned to on, the phone will try to retrieve its settings via
# a Plug-and-Play (PnP) Server. Modern SIP PBXs/Proxys can provide
# the PnP configuration data for the snom phones. Please refer to the
# manual of your PBX/Proxy. If the PnP configuration fails, the phone
# will try to get the settings from a setting server)."
psetting('subscribe_config', 'off');
//if (in_array(gs_get_conf('GS_INSTALLATION_TYPE'), array('gpbx'), true))
if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE'))
	psetting('pnp_config'      , 'on');  # make the Snom send a SUBSCRIBE to 224.0.1.75 (sip.mcast.net)
else
	psetting('pnp_config'      , 'off');

# call waiting (Anklopfen) aktiviert? //FIXME
#
//psetting('call_waiting', 'off');  # ""Call Waiting (CW)" can be enabled ("on", "visual only", "ringer") or disabled ("off")."
//$callwaiting = (int)$db->executeGetOne( 'SELECT `active` FROM `callwaiting` WHERE `user_id`='. $user_id );
//psetting('call_waiting', ($callwaiting ? 'visual only' : 'off'));
psetting('call_waiting', 'visual only');

psetting('call_completion', 'off');
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

psetting('peer_to_peer_cc', 'off');

psetting('dual_audio_handsfree', 'off', true);

psetting('show_call_status', 'off');
# if turned on the call progress is shown in the headline of the call
# progress window e.g. (100 Trying, 180 Ringing etc).

psetting('show_local_line', 'off');

psetting('logon_wizard', 'off');

psetting('settings_refresh_timer', (3600*2+rand(0,900)));
# a little random offset to avoid load spikes on the network and server



#####################################################################
#  Firmware
#####################################################################

# see
# http://wiki.snom.com/Settings/update_policy
# http://www.snom.com/wiki/index.php/Settings/firmware_interval
# http://www.snom.com/wiki/index.php/Settings/firmware_status
# http://www.snom.com/wiki/index.php/Mass_deployment#Firmware_configuration_file

if (gs_get_conf('GS_SNOM_PROV_FW_UPDATE')) {
	
	psetting('update_policy', 'auto_update');
	# "settings_only" : loads only the settings, no firmware update
	# "ask_for_update": the user is prompted to acknowledge the firmware update
	# "auto_update"   : the user is not prompted to acknowledge the firmware update
	
	psetting('firmware_interval', '1440');  # 1440 mins = 24 hrs
	psetting('firmware_status', $prov_url_snom .'sw-update.php?m='.$mac .'&u='.$user['name'] );
	# http, https, tftp are supported
	# default: /snom360/snom360-firmware.htm, /snom370/snom370-firmware.htm
}
else {
	psetting('update_policy', 'settings_only');
	psetting('firmware_status', '' );
}



#####################################################################
#  UI Strings
#####################################################################

psetting('calling_title'     , 'lang_calling');
psetting('connected_title'   , 'lang_connected');
psetting('disconnected_title', 'lang_terminated_finished');
psetting('ringing_title'     , 'lang_ringing');
psetting('held_by_title'     , 'lang_held_by');
psetting('enter_number_title', 'lang_enter_number');

/*
if (preg_match('/snom3[0-9]0-SIP\s+(\d+)\.(\d+)\.(\d+)/', $ua, $m)) {
	if ((int)$m[1]===7 && (int)$m[2]===1 && (int)$m[3]===19) {
		# 7.1.19 is a beta firmware which has English strings only
		if (strToLower(subStr(gs_get_conf('GS_INTL_LANG'),0,2))==='de') {
			psetting('calling_title'     , 'Rufaufbau');
			psetting('connected_title'   , 'Verbunden');
			psetting('disconnected_title', 'Getrennt');
			psetting('ringing_title'     , 'Anruf');
			psetting('held_by_title'     , 'Gehalten');
			psetting('enter_number_title', 'Nummer eingeben');
		}
	}
}
*/



#####################################################################
#  Language files
#####################################################################

$lang_releases = array(  # in descending order!
	'7.3.30',
	'7.1.33',
	'7.1.30',
	'7.1.19',
	'7.1.17',
	'7.1.10',
	'7.1.8' ,
	'7.1.6' ,
	'7.0.23',
	'7.0.18'
);

$lang_vers = null;
foreach ($lang_releases as $lang_release) {
	if (_snomAppCmp($fw_vers_nrml, $lang_release) >=0) {
		$lang_vers = $lang_release;
		break;
	}
}
if ($lang_vers) {
	$langdir = 'lang-'.$lang_vers.'/';
	
	# for a list of available languages see
	# http://provisioning.snom.com/config/
	# http://provisioning.snom.com/config/snomlang-7.1.19/
	# etc.
	
	//setting( '_gui_lang', 'Cestina'      , $langdir.'gui_lang_CZ.xml' );
	//setting( '_gui_lang', 'Dansk'        , $langdir.'gui_lang_DK.xml' );
	setting( '_gui_lang', 'Deutsch'      , $langdir.'gui_lang_DE.xml' );
	setting( '_gui_lang', 'English(US)'  , $langdir.'gui_lang_EN.xml' );
	//setting( '_gui_lang', 'English(UK) ' , $langdir.'gui_lang_UK.xml' );
	//setting( '_gui_lang', 'Espanol'      , $langdir.'gui_lang_SP.xml' );
	//setting( '_gui_lang', 'Francais'     , $langdir.'gui_lang_FR.xml' );
	//setting( '_gui_lang', 'Italiano'     , $langdir.'gui_lang_IT.xml' );
	//setting( '_gui_lang', 'Japanese'     , $langdir.'gui_lang_JP.xml' );
	//setting( '_gui_lang', 'Nederlands'   , $langdir.'gui_lang_NL.xml' );
	//setting( '_gui_lang', 'Norsk'        , $langdir.'gui_lang_NO.xml' );
	//setting( '_gui_lang', 'Polski'       , $langdir.'gui_lang_PL.xml' );
	//setting( '_gui_lang', 'Portugues'    , $langdir.'gui_lang_PT.xml' );
	//setting( '_gui_lang', 'Russian'      , $langdir.'gui_lang_RU.xml' );
	//setting( '_gui_lang', 'Slovencina'   , $langdir.'gui_lang_SK.xml' );
	//setting( '_gui_lang', 'Suomi'        , $langdir.'gui_lang_FI.xml' );
	//setting( '_gui_lang', 'Svenska'      , $langdir.'gui_lang_SW.xml' );  # gui lang: SW, web lang: SV!
	//setting( '_gui_lang', 'Turkce'       , $langdir.'gui_lang_TR.xml' );
	
	//setting( '_web_lang', 'Cestina'      , $langdir.'web_lang_CZ.xml' );
	//setting( '_web_lang', 'Dansk'        , $langdir.'web_lang_DK.xml' );
	setting( '_web_lang', 'Deutsch'      , $langdir.'web_lang_DE.xml' );
	setting( '_web_lang', 'English(US)'  , $langdir.'web_lang_EN.xml' );
	////setting( '_web_lang', 'English(UK)'  , $langdir.'web_lang_UK.xml' );
	//setting( '_web_lang', 'Espanol'      , $langdir.'web_lang_SP.xml' );
	//setting( '_web_lang', 'Francais'     , $langdir.'web_lang_FR.xml' );
	//setting( '_web_lang', 'Italiano'     , $langdir.'web_lang_IT.xml' );
	//setting( '_web_lang', 'Japanese'     , $langdir.'web_lang_JP.xml' );
	//setting( '_web_lang', 'Nederlands'   , $langdir.'web_lang_NL.xml' );
	//setting( '_web_lang', 'Norsk'        , $langdir.'web_lang_NO.xml' );
	////setting( '_web_lang', 'Polski'       , $langdir.'web_lang_PL.xml' );
	//setting( '_web_lang', 'Portugues'    , $langdir.'web_lang_PT.xml' );
	//setting( '_web_lang', 'Russian'      , $langdir.'web_lang_RU.xml' );
	////setting( '_web_lang', 'Slovencina'   , $langdir.'web_lang_SK.xml' );
	//setting( '_web_lang', 'Suomi'        , $langdir.'web_lang_FI.xml' );
	//setting( '_web_lang', 'Svenska'      , $langdir.'web_lang_SV.xml' );  # gui lang: SW, web lang: SV!
	//setting( '_web_lang', 'Turkce'       , $langdir.'web_lang_TR.xml' );
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
