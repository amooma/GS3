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
* Author: Henning Holtschneider <henning@loca.net>
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

$module = (int)@$_REQUEST['module'];
$level = (int)@$_REQUEST['level'];
/*
if (!$module)
	exit;
*/

header( 'Content-Type: text/xml; charset=utf-8' );
header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );

require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'inc/prov-fns.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );
include_once( GS_DIR .'inc/aastra-fns.php' );
set_error_handler('err_handler_die_on_err');

function _settings_err( $msg='' )
{
	@ob_end_clean();
	@ob_start();
	echo '<!-- // ', ($msg != '' ? str_replace('--','- -',$msg) : 'Error') ,' // -->',"\n";
	aastra_textscreen( 'Fehler', $msg );
	@ob_end_flush();
	exit(1);
}

function setting( $name, $idx, $val, $attrs=null, $writeable=false )
{
	echo '  <ConfigurationItem><Parameter>', $name , ($idx>0? ' '.$idx :'') ,'</Parameter><Value>', str_replace(array("\n", "\r"), array(' ', ' '), $val) , '</Value></ConfigurationItem>', "\n";
}

function psetting( $name, $val, $writeable=false )
{
	setting( $name, null, $val, null, $writeable );
}

function aastra_get_softkeys( $user_id, $phone_type, $modtype, $modnum, $level )
{
	global $db, $mac;
	$softkeys = array();


	switch ($modtype) {

		case 'aastra-560m':
			switch ($modnum) {
				case 1:
					$minkey = 201 + (($level-1) * 20);
					$maxkey = $minkey + 19;
					$offset = 200;
					break;
				case 2:
					$minkey = 301 + (($level-1) * 20);
					$maxkey = $minkey + 19;
					$offset = 300;
					break;
				case 3:
					$minkey = 401 + (($level-1) * 20);
					$maxkey = $minkey + 19;
					$offset = 400;
					break;
			}
			break;
		
		case 'aastra-536m':
			switch ($modnum) {
				case 1:
					$minkey = 201;
					$maxkey = $minkey + 35;
					$offset = 200;
					break;
				case 2:
					$minkey = 301;
					$maxkey = $minkey + 35;
					$offset = 300;
					break;
				case 3:
					$minkey = 401;
					$maxkey = $minkey + 35;
					$offset = 400;
					break;
			}
			break;
		
		default:
			gs_log( GS_LOG_NOTICE, "Unknown expansion module '$modtype' on phone $mac");
			break;
	}

	for ($i = ($minkey - $offset); $i <= ($maxkey - $offset); $i++) {
		$softkeys['expmod'.$modnum.' key'.$i]['function'] = 'none';
	}

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
			if ( $key_num >= $minkey && $key_num <= ($maxkey) ) {
				$key_name = 'expmod'.$modnum.' key'   .($key_num-$offset);
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
			if ( $key_num >= $minkey && $key_num <= ($maxkey) ) {
				$key_name = 'expmod'.$modnum.' key'   .($key_num-$offset);
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

$db = gs_db_master_connect();
if (! $db) {
	gs_log( GS_LOG_WARNING, "Aastra phone asks for expansion module settings - Could not connect to DB" );
	_settings_err( 'Could not connect to DB.' );
}

gs_log( GS_LOG_NOTICE, "Aastra phone \"$mac\" asks for expansion module settings - expansion module $module - page $level" );

# do we know the phone?
#
$user_id = @gs_prov_user_id_by_mac_addr( $db, $mac );
if ($user_id < 1) {
	gs_log( GS_LOG_NOTICE, "Will not provision expansion module for unknown phone $mac" );
	_settings_err( 'Unknown phone.' );
}

# get expansion modules of the phone
#
$expmods = explode( ';', $db->executeGetOne('SELECT `expansion_modules` FROM `phones` WHERE `mac_addr` = "'. $mac . '"') );
if (!is_array($expmods)) {
	gs_log( GS_LOG_NOTICE, "Phone $mac does not have any expansion modules" );
	exit;
}

# is it a valid user id?
#
$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `users` WHERE `id`='. $user_id );
if ($num < 1)
	$user_id = 0;

if ($user_id < 1) {
	gs_log( GS_LOG_NOTICE, "Will not provision expansion module for unknown phone $mac");
	_settings_err( 'Unknown phone.' );
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


ob_start();

echo '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
echo '<AastraIPPhoneConfiguration setType="override">',"\n";

if (!@$expmods[($module-1)]) {
	gs_log( GS_LOG_DEBUG, "Expansion module $module not present on phone $mac" );
	psetting('dummysetting', '', true);
	echo '</AastraIPPhoneConfiguration>',"\n";

	if (! headers_sent()) {
		# avoid chunked transfer-encoding
		header( 'Content-Length: '. @ob_get_length() );
	}
	@ob_flush();
	exit;
}

if ( ($expmods[($module-1)] == 'aastra-536m') && ($level > 1 ) ) {
	gs_log( GS_LOG_DEBUG, "Expansion module does not have level $level on phone $mac" );
	psetting('dummysetting', '', true);
	echo '</AastraIPPhoneConfiguration>',"\n";

	if (! headers_sent()) {
		# avoid chunked transfer-encoding
		header( 'Content-Length: '. @ob_get_length() );
	}
	@ob_flush();
	exit;
}

$softkeys = aastra_get_softkeys( $user_id, $phone_type, $expmods[($module-1)], $module, $level );
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
		psetting($key_name.' type' , $softkey['function'], true);
		if (isset($softkey['data']))
			psetting($key_name.' value', $softkey['data'    ], true);
		if (isset($softkey['label']))
			psetting($key_name.' label', $softkey['label'   ], true);
	}
}

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