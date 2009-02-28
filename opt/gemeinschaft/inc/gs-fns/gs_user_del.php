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
include_once( GS_DIR .'inc/gs-fns/gs_hylafax_authfile.php' );


/***********************************************************
*    delete a user account
***********************************************************/

function gs_user_del( $user )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user_id, nobody_index and softkey_profile_id
	#
	$rs = $db->execute( 'SELECT `id`, `nobody_index`, `softkey_profile_id`, `prov_param_profile_id` FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	if (! $rs)
		return new GsError( 'DB error.' );
	if (! ($r = $rs->fetchRow()))
		return new GsError( 'Unknown user.' );
	$user_id            = (int)$r['id'];
	$softkey_profile_id = (int)$r['softkey_profile_id'];
	$prov_profile_id    = (int)$r['prov_param_profile_id'];
	/*
	if ($r['nobody_index'] > 0)
		return new GsError( 'Cannot delete nobody user.' );
	*/
	
	# get host_id
	#
	$host_id = (int)$db->executeGetOne( 'SELECT `host_id` FROM `users` WHERE `id`='. $user_id );
	//if (! $host_id)
	//	return new GsError( 'Unknown host.' );
	
	$host = gs_host_by_id_or_ip( $host_id );
	if (isGsError($host) || ! is_array($host)) {
		$host = false;
	}
	
	# get user's sip name
	#
	$ext = $db->executeGetOne( 'SELECT `name` FROM `ast_sipfriends` WHERE `_user_id`='. $user_id );
	
	# reboot phone
	#
	//$user_name = $db->executeGetOne( 'SELECT `name` FROM `ast_sipfriends` WHERE `_user_id`='. $user_id );
	//@ shell_exec( 'asterisk -rx \'sip notify snom-reboot '. $user_name .'\' >>/dev/null' );
	@ gs_prov_phone_checkcfg_by_user( $user, true );
	
	
	# delete clir settings
	#
	$db->execute( 'DELETE FROM `clir` WHERE `user_id`='. $user_id );
	
	# delete dial log
	#
	$db->execute( 'DELETE FROM `dial_log` WHERE `user_id`='. $user_id );
	$db->execute( 'UPDATE `dial_log` SET `remote_user_id`=NULL WHERE `remote_user_id`='. $user_id );
	
	# delete call waiting settings
	#
	$db->execute( 'DELETE FROM `callwaiting` WHERE `user_id`='. $user_id );
	
	# delete call forward settings
	#
	$db->execute( 'DELETE FROM `callforwards` WHERE `user_id`='. $user_id );
	
	# delete from pickup groups
	#
	$db->execute( 'DELETE FROM `pickupgroups_users` WHERE `user_id`='. $user_id );
	
	# delete from queue members
	#
	$db->execute( 'DELETE FROM `ast_queue_members` WHERE `_user_id`='. $user_id );
	
	# delete external numbers
	#
	$db->execute( 'DELETE FROM `users_external_numbers` WHERE `user_id`='. $user_id );
	
	# delete mailbox settings
	#
	$db->execute( 'DELETE FROM `vm` WHERE `user_id`='. $user_id );
	
	# delete private phonebook
	#
	$db->execute( 'DELETE FROM `pb_prv` WHERE `user_id`='. $user_id );
	
	# delete mailbox
	#
	$db->execute( 'DELETE FROM `ast_voicemail` WHERE `_user_id`='. $user_id );
	
	# delete callblocking rules
	#
	$db->execute( 'DELETE FROM `callblocking` WHERE `user_id`='. $user_id );
	
	# delete sip account
	#
	$db->execute( 'DELETE FROM `ast_sipfriends` WHERE `_user_id`='. $user_id );
	
	# delete BOI permissions
	#
	$db->execute( 'DELETE FROM `boi_perms` WHERE `user_id`='. $user_id );
	
	# delete ringtones
	#
	$db->execute( 'DELETE FROM `ringtones` WHERE `user_id`='. $user_id );
	
	# delete softkeys
	#
	if ($softkey_profile_id > 0) {
		$db->execute( 'DELETE FROM `softkeys` WHERE `profile_id`='. $softkey_profile_id );
		$db->execute( 'DELETE FROM `softkey_profiles` WHERE `id`='. $softkey_profile_id .' AND `is_user_profile`=1' );
	}
	
	# delete prov_params
	#
	if ($prov_profile_id > 0) {
		$db->execute( 'DELETE FROM `prov_params` WHERE `profile_id`='. $prov_profile_id );
		$db->execute( 'DELETE FROM `prov_param_profiles` WHERE `id`='. $prov_profile_id .' AND `is_group_profile`=0' );
	}
	
	# delete watchlist buddies
	#
	$db->execute( 'DELETE FROM `user_watchlist` WHERE `user_id`='. $user_id );
	$db->execute( 'DELETE FROM `user_watchlist` WHERE `buddy_user_id`='. $user_id );
	
	# delete instant messaging
	#
	$db->execute( 'DELETE FROM `instant_messaging` WHERE `user_id`='. $user_id );
	
	# do a clean logout from the current phone
	#
	$db->execute( 'UPDATE `phones` SET `user_id`=NULL WHERE `user_id`='. $user_id );
	
	# delete user
	#
	$db->execute( 'DELETE FROM `users` WHERE `id`='. $user_id );
	
	# reload dialplan (hints!)
	#
	if ($host_id > 0) {
		if (is_array($host) && ! $host['is_foreign']) {
			//@ exec( GS_DIR .'sbin/start-asterisk --dialplan' );  // <-- not the same host!!!
			//$ok = @ gs_asterisks_reload( array($host_id), true );
			$ok = @ gs_asterisks_reload( array($host_id), false );
			/*
			if (isGsError( $ok ))
				return new GsError( $ok->getMsg() );
			if (! ok)
				return new GsError( 'Failed to reload dialplan.' );
			*/
		}
	}
	
	# delete user on foreign host
	#
	if (is_array($host) && $host['is_foreign']) {
		if (trim($ext) != '') {
			include_once( GS_DIR .'inc/boi-soap/boi-api.php' );
			$api = gs_host_get_api( $host['id'] );
			switch ($api) {
				case 'm01':
				case 'm02':
					$hp_route_prefix = (string)$db->executeGetOne(
						'SELECT `value` FROM `host_params` '.
						'WHERE `host_id`='. (int)$host['id'] .' AND `param`=\'route_prefix\'' );
					$sub_ext = (subStr($ext,0,strLen($hp_route_prefix)) === $hp_route_prefix)
						? subStr($ext, strLen($hp_route_prefix)) : $ext;
					gs_log( GS_LOG_DEBUG, "Mapping ext. $ext to $sub_ext for SOAP call" );
					
					//if (! class_exists('SoapClient')) {
					if (! extension_loaded('soap')) {
						return new GsError( 'Failed to delete user on foreign host (SoapClient not available).' );
					}
					include_once( GS_DIR .'inc/boi-soap/boi-soap.php' );
					$soap_faultcode = null;
					$ok = gs_boi_delete_extension( $api, $host['host'], $hp_route_prefix, $sub_ext, $soap_faultcode );
					if (! $ok) {
						return new GsError( 'Failed to delete user on foreign host (SOAP error).' );
					}
					break;
				
				case '':
					# host does not provide any API
					gs_log( GS_LOG_NOTICE, 'Deleting user '.$user.' on foreign host '.$host['host'].' without any API' );
					break;
				
				default:
					gs_log( GS_LOG_WARNING, 'Failed to delete user '.$user.' on foreign host '.$host['host'].' - invalid API "'.$api.'"' );
					return new GsError( 'Failed to delete user on foreign host (Invalid API).' );
			}
		}
	}
	
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