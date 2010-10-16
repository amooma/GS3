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

# caution: earlier versions of Aastra firmware do not like
# indented XML

define( 'GS_VALID', true );  /// this is a parent file
require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
include_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/aastra-fns.php' );
include_once( GS_DIR .'inc/gettext.php' );
include_once( GS_DIR .'inc/gs-fns/gs_prov_phone_checkcfg.php' );
include_once( GS_DIR .'inc/string.php' );

$xml = '';

function _err( $msg='' )
{
	aastra_textscreen( 'Error', ($msg != '' ? $msg : 'Unknown error') );
	exit(1);
}

function _get_user()
{
	$db = gs_db_slave_connect();
	
	$remote_addr = @$_SERVER['REMOTE_ADDR']; //FIXME
	$user_name = (string)$db->executeGetOne( 'SELECT `user`, `nobody_index` FROM `users` WHERE `current_ip`=\''. $db->escape($remote_addr) .'\'' );
	
	return gs_user_get($user_name);
}

function _logout_user()
{
	$db = gs_db_master_connect();
	
	$remote_addr = @$_SERVER['REMOTE_ADDR']; //FIXME
	
	# get id of the phone
	#
	$remote_addr = @$_SERVER['REMOTE_ADDR']; //FIXME
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
			gs_log( GS_LOG_WARNING, "Mobility: Could not find phone of last nobody_index $nobody_index" );
			
			# reboot phone
			#
			gs_prov_phone_checkcfg_by_ip( $remote_addr,true );
			
			return false;
		}
	}
	
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
	
	# reboot phone
	#
	gs_prov_phone_checkcfg_by_ip( $remote_addr,true );
	
	return true;
}


function _login_user($new_ext, $password)
{
	$db = gs_db_master_connect();
	
	$remote_addr = @$_SERVER['REMOTE_ADDR']; //FIXME
	$new_uid = (int)$db->executeGetOne( 'SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`=\''. $db->escape($new_ext) .'\'' );
	if ($new_uid < 1) {
		# unknown user
		gs_log( GS_LOG_NOTICE, "Mobility: Unknown extension $new_ext" );
		return false;
	}
	$pin = $db->executeGetOne( 'SELECT `pin` FROM `users` WHERE `id`='. $new_uid );
	if (trim($pin)=='') {
		gs_log( GS_LOG_NOTICE, "Mobility:Unknown user or no PIN" );
		return false;
	}
	if ((string)$pin != (string)$password) {
		# wrong password
		gs_log( GS_LOG_NOTICE, "Mobility: Login attempt for ext. $new_ext - Wrong PIN number" );
		return false;
	}
	
	# get id of the phone
	#
	$remote_addr = @$_SERVER['REMOTE_ADDR']; //FIXME
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
	
	# reboot old phone
	#
	gs_prov_phone_checkcfg_by_ip( $remote_addr,true );
	
	# reboot new phone
	#
	if ($new_ip_addr) gs_prov_phone_checkcfg_by_ip( $new_ip_addr ,true );
	
	return true;
}

$u = _get_user();

if (! $u) {
	aastra_textscreen('Error', __('Fehler bei der Zuordnung der IP:') .' '. @$_SERVER['REMOTE_ADDR']); //FIXME
	die();
}

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

$url_aastra_login = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'aastra/login.php';

if ($action === 'restart') {
	$xml =	"<AastraIPPhoneExecute Beep=\"yes\">\n" .
		"	<ExecuteItem URI=\"Command: FastReboot\"/>\n" .
		"</AastraIPPhoneExecute>\n";
	aastra_transmit_str($xml);
}

if ($action === 'logout' && $type === 'user') {
	if (! _logout_user()) {
		aastra_textscreen('Error',__('Abmelden nicht erfolgreich!'), 0, true);
	} else {
		aastra_textscreen('Info', __('Benutzer erfolgreich abgemeldet.'), 3);
	}
}

if ($action === 'login' && $type === 'user') {
	
	if ($user && $password) {
		if (! _login_user($user, $password)) {
			aastra_textscreen('Error',__('Falsche Durchwahl oder PIN!'), 0, true);
		} else {
			aastra_textscreen('Info', __('Benutzer erfolgreich angemeldet.'), 3);
		}
	} else {
		if ($user)
			$highlight = 3;
		else
			$highlight = 2;
		
		$xml = '<AastraIPPhoneInputScreen type="string" destroyOnExit="yes" displayMode="condensed" defaultIndex="'.$highlight.'">' ."\n";
		$xml.= '<Title>'. __('Login') .'</Title>' ."\n";
		$xml.= '<URL>'.$url_aastra_login .'?a='.$action.'</URL>' ."\n";
		$xml.= '<Default></Default>' ."\n";
		$xml.= '<InputField type="empty"></InputField>' ."\n";
		$xml.= '<InputField type="number">' ."\n";
		$xml.= '	<Prompt>'.htmlEnt(__('Durchwahl')) .':</Prompt>' ."\n";
		$xml.= '	<Default>'.$user.'</Default>' ."\n";
		$xml.= '	<Parameter>u</Parameter>' ."\n";
		$xml.= '	<Selection>1</Selection>' ."\n";
		$xml.= '</InputField>' ."\n";
		$xml.= '<InputField type="number" password="yes">' ."\n";
		$xml.= '	<Prompt>PIN:</Prompt>' ."\n";
		$xml.= '	<Default>'.$password.'</Default>' ."\n";
		$xml.= '	<Parameter>p</Parameter>' ."\n";
		$xml.= '	<Selection>2</Selection>' ."\n";
		$xml.= '</InputField>' ."\n";
		$xml.= '<SoftKey index="1">' ."\n";
		$xml.= '	<Label>'. __('OK') .'</Label>' ."\n";
		$xml.= '	<URI>SoftKey:Submit</URI>' ."\n";
		$xml.= '</SoftKey>' ."\n";
		$xml.= '<SoftKey index="4">' ."\n";
		$xml.= '	<Label>'. __('Abbrechen') .'</Label>' ."\n";
		$xml.= '	<URI>SoftKey:Exit</URI>' ."\n";
		$xml.= '</SoftKey>' ."\n";
		$xml.= '<SoftKey index="2">' ."\n";
		$xml.= '	<Label>&lt;--</Label>' ."\n";
		$xml.= '	<URI>SoftKey:BackSpace</URI>' ."\n";
		$xml.= '</SoftKey>' ."\n";
		$xml.= '</AastraIPPhoneInputScreen>' ."\n";
	}
}



elseif (! $action) {
	
	$xml = '<AastraIPPhoneTextMenu destroyOnExit="yes" LockIn="no" style="none">' ."\n";
	if (! $u['nobody_index'])
		$xml.= '<Title>'. __('Benutzer').': '.$u['user'].'</Title>' ."\n";
	
	$xml.= '<MenuItem>' ."\n";
	if ($u['nobody_index'])
		$xml.= '	<Prompt>'. htmlEnt(__('Benutzer anmelden')) .'</Prompt>' ."\n";
	else
		$xml.= '	<Prompt>'. htmlEnt(__('Benutzer wechseln')) .'</Prompt>' ."\n";
	$xml.= '	<URI>'. $url_aastra_login .'?a=login</URI>' ."\n";
	$xml.= '</MenuItem>' ."\n";
	
	if (! $u['nobody_index']) {
		$xml.= '<MenuItem>' ."\n";
		$xml.= '	<Prompt>'. htmlEnt(__('Benutzer abmelden')) .'</Prompt>' ."\n";
		$xml.= '	<URI>'. $url_aastra_login .'?a=logout</URI>' ."\n";
		$xml.= '</MenuItem>' ."\n";
	}

	$xml.= '<MenuItem>' ."\n";
	$xml.= '	<Prompt></Prompt>' ."\n";
	$xml.= '</MenuItem>' ."\n";
	
	$xml.= '<MenuItem>' ."\n";
	$xml.= '	<Prompt>'. htmlEnt(__('Telefon neu starten')) .'</Prompt>' ."\n";
	$xml.= '	<URI>'. $url_aastra_login .'?a=restart</URI>' ."\n";
	$xml.= '</MenuItem>' ."\n";
	
	$xml.= '<SoftKey index="1">' ."\n";
	$xml.= '	<Label>'. __('OK') .'</Label>' ."\n";
	$xml.= '	<URI>SoftKey:Select</URI>' ."\n";
	$xml.= '</SoftKey>' ."\n";
	$xml.= '<SoftKey index="4">' ."\n";
	$xml.= '	<Label>'. __('Abbrechen') .'</Label>' ."\n";
	$xml.= '	<URI>SoftKey:Exit</URI>' ."\n";
	$xml.= '</SoftKey>' ."\n";
	
	$xml.= '<SoftKey index="6">' ."\n";
	$xml.= '	<Label>&gt;&gt;</Label>' ."\n";
	$xml.= '	<URI>SoftKey:Select</URI>' ."\n";
	$xml.= '</SoftKey>' ."\n";
	$xml.= '</AastraIPPhoneTextMenu>' ."\n";
	
}

aastra_transmit_str( $xml );

?>