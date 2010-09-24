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

defined('GS_VALID') or die('No direct access.');
include_once( GS_DIR .'inc/gs-lib.php' );
include_once( GS_DIR .'inc/gs-fns/gs_host_by_id_or_ip.php' );
include_once( GS_DIR .'inc/gs-fns/gs_prov_phone_checkcfg.php' );
include_once( GS_DIR .'inc/gs-fns/gs_asterisks_reload.php' );
include_once( GS_DIR .'inc/gs-fns/gs_asterisks_prune_peer.php' );
include_once( GS_DIR .'inc/gs-fns/gs_hylafax_authfile.php' );
include_once( GS_DIR .'inc/gs-fns/gs_ami_events.php' );

/***********************************************************
*    change a user account
***********************************************************/

function gs_user_change( $user, $pin, $firstname, $lastname, $language, $host_id_or_ip, $force=false, $email='', $reload=true, $pb_hide=false, $drop_call=false, $drop_target='' )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	if (! preg_match( '/^[0-9]+$/', $pin ))
		return new GsError( 'PIN must be numeric.' );
	if (strLen($pin) < 3)
		return new GsError( 'PIN too short (min. 3 digits).' );
	elseif (strLen($pin) > 10)
		return new GsError( 'PIN too long (max. 10 digits).' );
	//if (! preg_match( '/^[a-zA-Z\d.\-\_ ]+$/', $firstname ))
	//	return new GsError( 'Invalid characters in first name.' );
	$firstname = preg_replace('/\s+/', ' ', trim($firstname));
	//if (! preg_match( '/^[a-zA-Z\d.\-\_ ]+$/', $lastname ))
	//	return new GsError( 'Invalid characters in last name.' );
	$lastname = preg_replace('/\s+/', ' ', trim($lastname));

	// prepare language code
	$language = substr(trim($language), 0, 2);
	if (strlen($language) != 2)
		return new GsError( 'Invalid language code.' );

	if (! defined('GS_EMAIL_PATTERN_VALID'))
		return new GsError( 'GS_EMAIL_PATTERN_VALID not defined.' );
	if ($email != '' && ! preg_match( GS_EMAIL_PATTERN_VALID, $email ))
		return new GsError( 'Invalid e-mail address.' );
		
	$pb_hide = (int)$pb_hide;
	
	$drop_call = (int)$drop_call;
	
	$drop_target = trim( $drop_target );
	
	if ( $drop_target != '' &&  ! preg_match( '/^(vm|vm\*)?[0-9]+$/', $drop_target ))
		return new GsError( 'Drop target must be numeric.' );
	
	
	include_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# start transaction
	#
	gs_db_start_trans($db);
	
	# get user_id
	#
	$user_id = (int)$db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	if (! $user_id) {
		gs_db_rollback_trans($db);
		return new GsError( 'Unknown user.' );
	}
	
	# get old host_id
	#
	$old_host_id = (int)$db->executeGetOne( 'SELECT `host_id` FROM `users` WHERE `id`='. $user_id );
	
	$old_host = gs_host_by_id_or_ip( $old_host_id );
	if (isGsError($old_host) || ! is_array($old_host)) {
		$old_host = false;
	}
	
	# get user's peer name (extension)
	#
	$ext = $db->executeGetOne( 'SELECT `name` FROM `ast_sipfriends` WHERE `_user_id`='. $user_id );
	
	# check if (new) host exists
	#
	$host = gs_host_by_id_or_ip( $host_id_or_ip );
	if (isGsError( $host )) {
		gs_db_rollback_trans($db);
		return new GsError( $host->getMsg() );
	}
	if (! is_array( $host )) {
		gs_db_rollback_trans($db);
		return new GsError( 'Unknown host.' );
	}
	
	if ($old_host_id != $host['id'] && ! $force) {
		gs_db_rollback_trans($db);
		return new GsError( 'Changing the host will result in loosing mailbox messages etc. and thus will not be done without force.' );
	}
	
	/*
	# check if queue with same ext exists
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `ast_queues` WHERE `name`=\''. $db->escape($ext) .'\'' );
	if ($num > 0) {
		gs_db_rollback_trans($db);
		return new GsError( 'A queue with that name already exists.' );
	}
	*/
	
	# update user
	#
	$ok = $db->execute( 'UPDATE `users` SET `pin`=\''. $db->escape($pin) .'\', `firstname`=\''. $db->escape($firstname) .'\', `lastname`=\''. $db->escape($lastname) .'\', `email`=\''. $db->escape($email) .'\', `pb_hide`=' . $pb_hide . ', `host_id`='. $host['id'] .' WHERE `id`='. $user_id );
	if (! $ok) {
		gs_db_rollback_trans($db);
		return new GsError( 'Failed to change user.' );
	}
	
	# update sip account (including language code)
	#
	$calleridname = trim( gs_utf8_decompose_to_ascii( $firstname .' '. $lastname ));
	$ok = $db->execute( 'UPDATE `ast_sipfriends` SET `callerid`=CONCAT(_utf8\''. $db->escape($calleridname) .'\', \' <\', `name`, \'>\'), `language`=\''. $db->escape($language) .'\' WHERE `_user_id`='. $user_id );
	if (! $ok) {
		gs_db_rollback_trans($db);
		return new GsError( 'Failed to change SIP account.' );
	}
	else {
		if ( GS_BUTTONDAEMON_USE == true ) {
			gs_user_language_changed_ui ( $user , preg_replace('/[^0-9a-zA-Z]/', '', @$_REQUEST['ulang']) ) ;
		}
	}
	
	# update dropping the call
	#
	
	$drop_exists = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `user_calldrop` WHERE `user_id`='. $user_id );
	if ( $drop_exists <= 0 ) {
		$db->execute( 'INSERT INTO `user_calldrop` ( `user_id`, `number`, `drop_call` ) 
			VALUES ( ' . $user_id . ', \'' . $drop_target  . '\', ' . $drop_call . ' )'   );
	}
	else {
		$db->execute( 'UPDATE `user_calldrop` SET `drop_call`=' . $drop_call . ', `number`=\'' . $drop_target  .  '\'  WHERE `user_id`=' . $user_id );
	}
	
	
	# delete stuff not used for users on foreign hosts
	#
	if ($host['is_foreign']) {
		$db->execute( 'DELETE FROM `clir` WHERE `user_id`='. $user_id );
		$db->execute( 'DELETE FROM `dial_log` WHERE `user_id`='. $user_id );
		$db->execute( 'DELETE FROM `callforwards` WHERE `user_id`='. $user_id );
		$db->execute( 'DELETE FROM `pickupgroups_users` WHERE `user_id`='. $user_id );
		$db->execute( 'DELETE FROM `ast_queue_members` WHERE `_user_id`='. $user_id );
		$db->execute( 'DELETE FROM `vm` WHERE `user_id`='. $user_id );
		$db->execute( 'DELETE FROM `ast_voicemail` WHERE `_user_id`='. $user_id );
	}
	
	# update mailbox
	#
	if (! $host['is_foreign']) {
		$ok = $db->execute( 'UPDATE `ast_voicemail` SET `password`=\''. $db->escape($pin) .'\', `fullname`=\''. $db->escape($firstname .' '. $lastname) .'\' WHERE `_user_id`='. $user_id );
		if (! $ok) {
			gs_db_rollback_trans($db);
			return new GsError( 'Failed to change mailbox.' );
		}
	}
	
	# new host?
	#
	if ($host['id'] != $old_host_id) {
		
		# delete from queue members
		#
		$db->execute( 'DELETE FROM `ast_queue_members` WHERE `_user_id`='. $user_id );
		
		# delete from pickup groups
		#
		$db->execute( 'DELETE FROM `pickupgroups_users` WHERE `user_id`='. $user_id );
	}
	
	# get info needed for foreign hosts
	#
	if ((is_array($old_host) && $old_host['is_foreign']) || $host['is_foreign']) {
		# get user's sip name and password
		$rs = $db->execute( 'SELECT `name`, `secret` FROM `ast_sipfriends` WHERE `_user_id`='. $user_id );
		if (! $rs || ! ($r = $rs->fetchRow())) {
			gs_db_rollback_trans($db);
			return new GsError( 'DB error.' );
		}
		$ext     = $r['name'];
		$sip_pwd = $r['secret'];
	}
	
	# modify user on foreign host(s)
	#
	if ($host['id'] != $old_host_id) {
		# host changed. delete user on old host and add on new one
		if (is_array($old_host) && $old_host['is_foreign']) {
			include_once( GS_DIR .'inc/boi-soap/boi-api.php' );
			$api = gs_host_get_api( $old_host_id );
			switch ($api) {
				case 'm01':
				case 'm02':
					//if (! class_exists('SoapClient')) {
					if (! extension_loaded('soap')) {
						gs_db_rollback_trans($db);
						return new GsError( 'Failed to delete user on old foreign host (SoapClient not available).' );
					} else {
						$hp_route_prefix = (string)$db->executeGetOne(
							'SELECT `value` FROM `host_params` '.
							'WHERE `host_id`='. (int)$old_host['id'] .' AND `param`=\'route_prefix\'' );
						$sub_ext = (subStr($ext,0,strLen($hp_route_prefix)) === $hp_route_prefix)
							? subStr($ext, strLen($hp_route_prefix)) : $ext;
						gs_log( GS_LOG_DEBUG, "Mapping ext. $ext to $sub_ext for SOAP call" );
						
						include_once( GS_DIR .'inc/boi-soap/boi-soap.php' );
						$soap_faultcode = null;
						$ok = gs_boi_delete_extension( $api, $old_host['host'], $hp_route_prefix, $sub_ext, $soap_faultcode );
						if (! $ok) {
							gs_db_rollback_trans($db);
							return new GsError( 'Failed to delete user on old foreign host (SOAP error).' );
						}
					}
					break;
				
				case '':
					# host does not provide any API
					gs_log( GS_LOG_NOTICE, 'Deleting user '.$user.' on foreign host '.$old_host['host'].' without any API' );
					break;
				
				default:
					gs_log( GS_LOG_WARNING, 'Failed to delete user '.$user.' on foreign host '.$old_host['host'].' - invalid API "'.$api.'"' );
					gs_db_rollback_trans($db);
					return new GsError( 'Failed to delete user on foreign host (Invalid API).' );
			}
		}
		if ($host['is_foreign']) {
			include_once( GS_DIR .'inc/boi-soap/boi-api.php' );
			$api = gs_host_get_api( $host['id'] );
			switch ($api) {
				case 'm01':
				case 'm02':
					//if (! class_exists('SoapClient')) {
					if (! extension_loaded('soap')) {
						gs_db_rollback_trans($db);
						return new GsError( 'Failed to add user on new foreign host (SoapClient not available).' );
					} else {
						$hp_route_prefix = (string)$db->executeGetOne(
							'SELECT `value` FROM `host_params` '.
							'WHERE `host_id`='. (int)$host['id'] .' AND `param`=\'route_prefix\'' );
						$sub_ext = (subStr($ext,0,strLen($hp_route_prefix)) === $hp_route_prefix)
							? subStr($ext, strLen($hp_route_prefix)) : $ext;
						gs_log( GS_LOG_DEBUG, "Mapping ext. $ext to $sub_ext for SOAP call" );
						
						include_once( GS_DIR .'inc/boi-soap/boi-soap.php' );
						$soap_faultcode = null;
						$ok = gs_boi_update_extension( $api, $host['host'], $hp_route_prefix, $sub_ext, $user, $sip_pwd, $pin, $firstname, $lastname, $email, $soap_faultcode );
						if (! $ok) {
							gs_db_rollback_trans($db);
							return new GsError( 'Failed to add user on new foreign host (SOAP error).' );
						}
					}
					break;
				
				case '':
					# host does not provide any API
					gs_log( GS_LOG_NOTICE, 'Adding user '.$user.' on foreign host '.$host['host'].' without any API' );
					break;
				
				default:
					gs_log( GS_LOG_WARNING, 'Failed to add user '.$user.' on foreign host '.$host['host'].' - invalid API "'.$api.'"' );
					gs_db_rollback_trans($db);
					return new GsError( 'Failed to add user on foreign host (Invalid API).' );
			}
		}
	} else {
		# host did not change
		if ($host['is_foreign']) {
			include_once( GS_DIR .'inc/boi-soap/boi-api.php' );
			$api = gs_host_get_api( $host['id'] );
			switch ($api) {
				case 'm01':
				case 'm02':
					//if (! class_exists('SoapClient')) {
					if (! extension_loaded('soap')) {
						gs_db_rollback_trans($db);
						return new GsError( 'Failed to modify user on foreign host (SoapClient not available).' );
					} else {
						$hp_route_prefix = (string)$db->executeGetOne(
							'SELECT `value` FROM `host_params` '.
							'WHERE `host_id`='. (int)$host['id'] .' AND `param`=\'route_prefix\'' );
						$sub_ext = (subStr($ext,0,strLen($hp_route_prefix)) === $hp_route_prefix)
							? subStr($ext, strLen($hp_route_prefix)) : $ext;
						gs_log( GS_LOG_DEBUG, "Mapping ext. $ext to $sub_ext for SOAP call" );
						
						include_once( GS_DIR .'inc/boi-soap/boi-soap.php' );
						$soap_faultcode = null;
						$ok = gs_boi_update_extension( $api, $host['host'], $hp_route_prefix, $sub_ext, $user, $sip_pwd, $pin, $firstname, $lastname, $email, $soap_faultcode );
						if (! $ok) {
							gs_db_rollback_trans($db);
							return new GsError( 'Failed to modify user on foreign host (SOAP error).' );
						}
					}
					break;
				
				case '':
					# host does not provide any API
					gs_log( GS_LOG_NOTICE, 'Modifying user '.$user.' on foreign host '.$host['host'].' without any API' );
					break;
				
				default:
					gs_log( GS_LOG_WARNING, 'Failed to modify user '.$user.' on foreign host '.$host['host'].' - invalid API "'.$api.'"' );
					gs_db_rollback_trans($db);
					return new GsError( 'Failed to modify user on foreign host (Invalid API).' );
			}
		}
	}
	
	# commit transaction
	#
	if (! gs_db_commit_trans($db)) {
		return new GsError( 'Failed to modify user.' );
	}
	
	# new host?
	#
	if ($host['id'] != $old_host_id) {
		# reload dialplan (hints!)
		#
		if (is_array($old_host) && ! $old_host['is_foreign']) {
			$ok = @ gs_asterisks_prune_peer( $ext, array($old_host_id) );
			if ($reload) @ gs_asterisks_reload( array($old_host_id), true );
		}
		if (! $host['is_foreign']) {
			if ($reload) @ gs_asterisks_reload( array($host['id'] ), true );
		}
		if ( GS_BUTTONDAEMON_USE == true ) {
			$user_name = $db->executeGetOne( 'SELECT `name` FROM `ast_sipfriends` WHERE `_user_id`=\''. $db->escape($user_id) .'\'' );
			if (! $user_name)
				return new GsError( 'Unknown user.' );
			gs_user_remove_ui($user_name);
		}
	} else {
		$ok = @ gs_asterisks_prune_peer( $ext, array($host['id']) );
	}
	
	# reboot the phone
	#
	//@ shell_exec( 'asterisk -rx \'sip notify snom-reboot '. $ext .'\' >>/dev/null' );
	@ gs_prov_phone_checkcfg_by_ext( $ext, true );
	
	# update fax authentication file if fax enabled
	#
	if (gs_get_conf('GS_FAX_ENABLED')) {
		$ok = gs_hylafax_authfile_sync();
		if (isGsError( $ok ))
			return new GsError( $ok->getMsg() );
		if (! $ok)
			return new GsError( 'Failed to update fax authentication file.' );
	}
	
	return true;
}


?>
