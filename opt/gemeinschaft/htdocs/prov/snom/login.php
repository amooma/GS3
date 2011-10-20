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
* Author: Henning Holtschneider <henning@loca.net> - LocaNet oHG
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

require_once( '../../../inc/conf.php' );
require_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'inc/gettext.php' );
require_once( GS_DIR .'inc/langhelper.php' );
include_once( GS_DIR .'inc/gs-fns/gs_prov_phone_checkcfg.php' );
require_once( GS_DIR .'inc/gs-fns/gs_ami_events.php' );
include_once( GS_DIR .'inc/prov-phonetypecache.php' );
include_once( GS_DIR .'inc/snom-fns.php' );

header( 'Content-Type: application/x-snom-xml; charset=utf-8' );
# the Content-Type header is ignored by the Snom
header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );

function _ob_send()
{
	if (! headers_sent()) {
		header( 'Content-Type: application/x-snom-xml; charset=utf-8' );
		# the Content-Type header is ignored by the Snom
		header( 'Content-Length: '. (int)@ob_get_length() );
	}
	@ob_end_flush();
	die();
}

function _get_user()
{
	$db = gs_db_slave_connect();
	
	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$user_name = (string)$db->executeGetOne( 'SELECT `user`, `nobody_index` FROM `users` WHERE `current_ip`=\''. $db->escape($remote_addr) .'\'' );
	
	return gs_user_get($user_name);
}

function _get_user_ext( $user_id )
{
	$db = gs_db_slave_connect();
	
	$user_ext = $db->executeGetOne( 'SELECT `name` FROM `ast_sipfriends` WHERE `_user_id`=\''. $db->escape($user_id) .'\'' );

	if (!$user_ext ) {
		snom_textscreen( __('Fehler'), __('Unbekannter Benutzer.') );
		return false;
	}

	return $user_ext;
}

function _logout_user()
{
	$db = gs_db_master_connect();
	
	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	
	# get id of the phone
	#
	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$old_uid = (int)$db->executeGetOne( 'SELECT `id` FROM `users` WHERE `current_ip`=\''. $db->escape($remote_addr) .'\'' );
	if ($old_uid < 1) {
		gs_log( GS_LOG_NOTICE, "Mobility: No user with current IP \"$remote_addr\" in database" );
		return false;
	}
	
	$phone_id = (int)$db->executeGetOne( 'SELECT `id` FROM `phones` WHERE `user_id`='. $old_uid .' LIMIT 1' );
	if ($phone_id < 1) {
		gs_log( GS_LOG_WARNING, "Mobility: Logout attempt for ext. $new_ext - Could not find phone of last user ID $old_uid" );
		
		# try to find the phone by the corresponding nobody index
		$nobody_index = (int)$db->executeGetOne( 'SELECT `nobody_index` FROM `users` WHERE `id`='. $old_uid );
		if ($nobody_index > 0) {
			$phone_id = (int)$db->executeGetOne( 'SELECT `id` FROM `phones` WHERE `nobody_index`='. $nobody_index .' LIMIT 1' );
		}
		if ($phone_id < 1) {
			gs_log( GS_LOG_WARNING, "Mobility: Could not find phone of last nobody_index $nobody_index" );
			
			# reconfigure phone
			#
			gs_prov_phone_checkcfg_by_ip($remote_addr, false);
			
			return false;
		}
	}

	# cache currently used phone types for further use
	gs_phonetypecache_add_by_uid_to_ip($db, $old_uid);
	
	$rs = $db->execute( 'SELECT `id`, `mac_addr`, `nobody_index` FROM `phones` WHERE `user_id`='. $old_uid );
	while ($phone = $rs->fetchRow()) {
		
		# assign the default nobody
		#
		$phone['nobody_index'] = (int)$phone['nobody_index'];
		if ($phone['nobody_index'] < 1) {
			gs_log( GS_LOG_WARNING, "Phone ". $phone['mac_addr'] ." does not have a default nobody user" );
			$new_user_id = null;
		} else {
			$new_user_id = (int)$db->executeGetOne(
				'SELECT `id` FROM `users` WHERE `nobody_index`='. $phone['nobody_index']
				);
			if ($new_user_id < 1) {
				gs_log( GS_LOG_WARNING, "Could not find user with nobody index ". $phone['nobody_index'] ." for phone ". $phone['mac_addr'] );
			}
		}

		gs_log( GS_LOG_DEBUG, "Mobility: Assigning nobody user with ID ". ($new_user_id > 0 ? $new_user_id : 'NULL') ." to phone ". $phone['mac_addr'] );
		$db->execute( 'UPDATE `phones` SET `user_id`='. ($new_user_id > 0 ? $new_user_id : 'NULL') .' WHERE `id`='. (int)$phone['id'] .' AND `user_id`='. $old_uid );

	}
	
	# reconfigure phone
	#
	gs_prov_phone_checkcfg_by_ip($remote_addr, false);
	
	# generate userevent
	#
	if ( GS_BUTTONDAEMON_USE == true ) {

		$user_ext = _get_user_ext ( $old_uid );
		if ( $user_ext ) {
			gs_user_logoff_ui ( $user_ext );
		}
	}

	
	return true;
}


function _login_user($new_ext, $password)
{
	$db = gs_db_master_connect();
	
	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$new_uid = (int)$db->executeGetOne( 'SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`=\''. $db->escape($new_ext) .'\'' );
	if ($new_uid < 1) {
		# unknown user
		gs_log( GS_LOG_NOTICE, "Mobility: Unknown extension $new_ext" );
		return false;
	}
	$pin = $db->executeGetOne( 'SELECT `pin` FROM `users` WHERE `id`='. $new_uid );
	if (trim($pin)=='') {
		gs_log( GS_LOG_NOTICE, "Mobility: Unknown user or no PIN" );
		return false;
	}
	if ((string)$pin != (string)$password) {
		# wrong password
		gs_log( GS_LOG_NOTICE, "Mobility: Login attempt for ext. $new_ext - Wrong PIN number" );
		return false;
	}
	
	# get id of the phone
	#
	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$old_uid = (int)$db->executeGetOne( 'SELECT `id` FROM `users` WHERE `current_ip`=\''. $db->escape($remote_addr) .'\'' );
	if ($old_uid < 1) {
		gs_log( GS_LOG_NOTICE, "Mobility: No user with current IP \"$remote_addr\" in database" );
		return false;
	}
	
	$phone_id = (int)$db->executeGetOne( 'SELECT `id` FROM `phones` WHERE `user_id`='. $old_uid .' LIMIT 1' );
	if ($phone_id < 1) {
		gs_log( GS_LOG_WARNING, "Mobility: Login attempt for ext. $new_ext - Could not find phone of last user ID $old_uid" );
		
		# try to find the phone by the corresponding nobody index
		$nobody_index = (int)$db->executeGetOne( 'SELECT `nobody_index` FROM `users` WHERE `id`='. $old_uid );
		if ($nobody_index > 0) {
			$phone_id = (int)$db->executeGetOne( 'SELECT `id` FROM `phones` WHERE `nobody_index`='. $nobody_index .' LIMIT 1' );
		}
		if ($phone_id < 1) {
			gs_log( GS_LOG_WARNING, "Mobility: Login attempt for ext. $new_ext - Could not find phone of last nobody_index $nobody_index" );
			return false;
		}
	}
	
	# cache currently used phone types for further use
	gs_phonetypecache_add_by_uid_to_ip($db, $old_uid);
	gs_phonetypecache_add_by_ext_to_ip($db, $new_ext);

	# log out the old user, assign the default nobody
	#
	$rs = $db->execute( 'SELECT `id`, `mac_addr`, `nobody_index`, `user_id` FROM `phones` WHERE `user_id` IN ('. $old_uid .','. $new_uid .') AND `id`<>'. $phone_id );
	while ($phone = $rs->fetchRow()) {
		$phone['nobody_index'] = (int)$phone['nobody_index'];
		$phone['user_id'     ] = (int)$phone['user_id'     ];
		if ($phone['nobody_index'] < 1) {
			gs_log( GS_LOG_WARNING, "Phone ". $phone['mac_addr'] ." does not have a default nobody user" );
			$nobody_user_id = null;
		} else {
			$nobody_user_id = (int)$db->executeGetOne(
				'SELECT `id` FROM `users` WHERE `nobody_index`='. $phone['nobody_index']
				);
			if ($nobody_user_id < 1) {
				gs_log( GS_LOG_WARNING, "Could not find user with nobody index ". $phone['nobody_index'] ." for phone ". $phone['mac_addr'] );
			}
		}
		gs_log( GS_LOG_DEBUG, "Mobility: Assigning nobody user with ID ". ($nobody_user_id > 0 ? $nobody_user_id : 'NULL') ." to phone ". $phone['mac_addr'] );
		$db->execute(
			'UPDATE `phones` SET '.
				'`user_id`='. ($nobody_user_id > 0 ? $nobody_user_id : 'NULL') .' '.
			'WHERE '.
				'`id`='. (int)$phone['id'] .' AND '.
				'`user_id` '. ($phone['user_id']>0 ? '='.$phone['user_id'] : 'IS NULL')
			);
	}
	//$db->execute( 'UPDATE `users` SET `current_ip`=NULL WHERE `id`='. $old_uid );
	
	
	# log in the new user
	#
	$ok = $db->execute( 'UPDATE `phones` SET `user_id`='. $new_uid .' WHERE `id`='. $phone_id );
	if (! $ok) {
		gs_log( GS_LOG_NOTICE, "Mobility: DB error" );
		echo 'SET VARIABLE ret '. gs_agi_str_esc('error') ."\n";
		//fFlush(STDOUT); // <- do not use. not defined in php-cgi!
		die();
	}
	
	# get new phone's IP addr.
	#
	$new_ip_addr = $db->executeGetOne('SELECT `current_ip` FROM `users` WHERE `id`='.$new_uid );
	gs_log( GS_LOG_DEBUG, "Mobility: IP address found for new phone: $new_ip_addr");
	

	# reconfigure old phone
	#
	gs_prov_phone_checkcfg_by_ip($remote_addr, false);
	
	# reconfigure new phone
	#
	if ($new_ip_addr) gs_prov_phone_checkcfg_by_ip($new_ip_addr, false);
	
	# generate userevent
	#
	if ( GS_BUTTONDAEMON_USE == true ) {

		$user_ext = _get_user_ext ( $new_uid );
		if ( $user_ext ) {
			gs_user_login_ui ( $user_ext );
		}
	}

	
	return true;
}

if (! gs_get_conf('GS_SNOM_PROV_ENABLED')) {
	gs_log( GS_LOG_DEBUG, "Snom provisioning not enabled" );
	snom_textscreen( __('Fehler'), __('Nicht aktiviert') );
}

$db = gs_db_slave_connect();

$u = _get_user();
$phone_id = (int)$db->executeGetOne( 'SELECT `id` FROM `phones` WHERE `user_id`='. $u["id"] .' LIMIT 1' );

$action = trim( @$_REQUEST['a'] );
if (! in_array( $action, array('login', 'logout', 'restart'), true )) {
	$action = false;
}

$type = trim( @$_REQUEST['t'] );
if (! in_array( $action, array('user', 'queue'), true )) {
	$type = 'user';
}

$user     =  trim( @$_REQUEST['u'] );
$password =  trim( @$_REQUEST['p'] );

// setup i18n stuff
gs_setlang( gs_get_lang_user($db, $u, GS_LANG_FORMAT_GS) );
gs_loadtextdomain( 'gemeinschaft-gui' );
gs_settextdomain( 'gemeinschaft-gui' );

$url_snom_login = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'snom/login.php';

#################################### INITIAL SCREEN {

if (! $action ) {

	ob_start();
	echo
		'<?','xml version="1.0" encoding="utf-8"?','>', "\n",
		'<SnomIPPhoneMenu>', "\n",
			'<Title>', __('Login'), '</Title>', "\n\n";

	if ($u['nobody_index'])
		echo
			'<MenuItem>', "\n",
				'<Name>', __('Benutzer anmelden'), '</Name>', "\n",
				'<URL>', $url_snom_login, '?a=login', '</URL>', "\n",
			'</MenuItem>', "\n\n";
	else
		echo
			'<MenuItem>', "\n",
				'<Name>', __('Benutzer wechseln'), '</Name>', "\n",
				'<URL>', $url_snom_login, '?a=login', '</URL>', "\n",
			'</MenuItem>', "\n\n";

	if (! $u['nobody_index'])
		echo
			'<MenuItem>', "\n",
				'<Name>', __('Benutzer abmelden'), '</Name>', "\n",
				'<URL>', $url_snom_login, '?a=logout', '</URL>', "\n",
			'</MenuItem>', "\n\n";

	if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE'))
		echo
			'<MenuItem>', "\n",
				'<Name>', __('Telefon neu starten'), '</Name>', "\n",
				'<URL>', $url_snom_login, '?a=restart', '</URL>', "\n",
			'</MenuItem>', "\n\n";

	echo '</SnomIPPhoneMenu>', "\n";
	_ob_send();

}

if ($action === 'login' && $type === 'user') {

	if ($user && $password) {
		if (! _login_user($user, $password)) {
			snom_textscreen( __('Fehler'), __('Falsche Durchwahl oder PIN!') );
		} else {
			snom_textscreen( __('Info'), __('Benutzer angemeldet.'), 3);
		}
	} else {
		echo
			'<?','xml version="1.0" encoding="utf-8"?','>', "\n",
			'<SnomIPPhoneInput>', "\n",
				'<Title>', __('Login'), '</Title>', "\n";
		if ($user) {
			echo
				'<Prompt>', __('PIN'), '</Prompt>', "\n",
				'<URL>', $url_snom_login, '</URL>', "\n",
				'<InputItem>', "\n",
				'<DisplayName>', __('PIN'), '</DisplayName>', "\n",
				'<QueryStringParam>a=login&u=', $user, '&p</QueryStringParam>', "\n",
				'<DefaultValue/>', "\n",
				'<InputFlags>pn</InputFlags>', "\n",
				'</InputItem>', "\n";
		} else {
			echo
				'<Prompt>', __('Durchwahl'), '</Prompt>', "\n",
				'<URL>', $url_snom_login, '</URL>', "\n",
				'<InputItem>', "\n",
				'<DisplayName>', __('Durchwahl'), '</DisplayName>', "\n",
				'<QueryStringParam>a=login&u', '</QueryStringParam>', "\n",
				'<DefaultValue/>', "\n",
				'<InputFlags>n</InputFlags>', "\n",
				'</InputItem>', "\n";
		}
		echo
			'</SnomIPPhoneInput>', "\n";
	}

}

if ($action === 'logout' && $type === 'user') {
	if (! _logout_user()) {
		snom_textscreen( __('Fehler'), __('Abmelden fehlgeschlagen!') );
	} else {
		snom_textscreen( __('Info'), __('Benutzer abgemeldet.'), 3);
	}
}

if ($action === 'restart') {
	snom_textscreen( __('Info'), __('Neustart. Bitte warten.') );
	gs_prov_phone_checkcfg_by_ip(@$_SERVER['REMOTE_ADDR'], true);
}
?>