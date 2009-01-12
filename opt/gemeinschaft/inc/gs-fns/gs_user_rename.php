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
include_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_is_valid_name.php' );
include_once( GS_DIR .'inc/gs-fns/gs_host_by_id_or_ip.php' );
include_once( GS_DIR .'inc/gs-fns/gs_prov_phone_checkcfg.php' );


/***********************************************************
*    rename a user
***********************************************************/

function gs_user_rename( $username, $new_username )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $username ))
		return new GsError( 'Username must be alphanumeric.' );
	
	$ret = gs_user_is_valid_name( $new_username );
	if (isGsError($ret)) return $ret;
	elseif (! $ret) return new GsError( 'Invalid new username.' );
	
	if ($new_username === $username) {
		//return new GsError( 'New username = old username.' );
		return true;
	}
	
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
	$user_id = (int)$db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($username) .'\'' );
	if (! $user_id) {
		gs_db_rollback_trans($db);
		return new GsError( "Unknown user \"$username\"." );
	}
	
	# check if new username exists
	#
	$user_id_new_test = (int)$db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($new_username) .'\'' );
	if ($user_id_new_test) {
		gs_db_rollback_trans($db);
		return new GsError( "A user with username \"$new_username\" already exists." );
	}
	
	# get host
	#
	$host_id = (int)$db->executeGetOne( 'SELECT `host_id` FROM `users` WHERE `id`='. $user_id );
	$host = gs_host_by_id_or_ip( $host_id );
	if (isGsError($host) || ! is_array($host)) {
		gs_db_rollback_trans($db);
		return new GsError( 'Host not found.' );
	}
	
	# get info needed for foreign users
	#
	if ($host['is_foreign']) {
		# get user's extension and password
		$rs = $db->execute( 'SELECT `name`, `secret` FROM `ast_sipfriends` WHERE `_user_id`='. $user_id );
		if (! $rs || ! ($r = $rs->fetchRow())) {
			gs_db_rollback_trans($db);
			return new GsError( 'DB error.' );
		}
		$ext     = $r['name'];
		$sip_pwd = $r['secret'];
		
		# get user info
		$rs = $db->execute( 'SELECT `pin`, `firstname`, `lastname`, `email` FROM `users` WHERE `id`='. $user_id );
		if (! $rs || ! ($user_info = $rs->fetchRow())) {
			gs_db_rollback_trans($db);
			return new GsError( 'DB error.' );
		}
	} else {
		$ext = null;
	}
	
	# update user
	#
	$ok = $db->execute( 'UPDATE `users` SET `user`=\''. $db->escape($new_username) .'\' WHERE `id`='. $user_id );
	if (! $ok) {
		@$db->execute( 'UPDATE `users` SET `user`=\''. $db->escape($username) .'\' WHERE `id`='. $user_id );
		gs_db_rollback_trans($db);
		return new GsError( 'Failed to rename user.' );
	}
	
	# rename user on foreign host
	#
	if ($host['is_foreign']) {
		include_once( GS_DIR .'inc/boi-soap/boi-api.php' );
		$api = gs_host_get_api( $host_id );
		switch ($api) {
			case 'm01':
			case 'm02':
				if (! extension_loaded('soap')) {
					@$db->execute( 'UPDATE `users` SET `user`=\''. $db->escape($username) .'\' WHERE `id`='. $user_id );
					gs_db_rollback_trans($db);
					return new GsError( 'Failed to rename user on foreign host (SoapClient not available).' );
				} else {
					$hp_route_prefix = (string)$db->executeGetOne(
						'SELECT `value` FROM `host_params` '.
						'WHERE `host_id`='. (int)$host['id'] .' AND `param`=\'route_prefix\'' );
					$sub_ext = (strLen($ext) > strLen($hp_route_prefix))
						? subStr($ext, strLen($hp_route_prefix)) : $ext;
					gs_log( GS_LOG_DEBUG, "Mapping ext. $ext to $sub_ext for SOAP call" );
					
					include_once( GS_DIR .'inc/boi-soap/boi-soap.php' );
					$soap_faultcode = null;
					$ok = gs_boi_update_extension( $api, $host['host'], $hp_route_prefix, $sub_ext, $new_username, $sip_pwd, $user_info['pin'], $user_info['firstname'], $user_info['lastname'], $user_info['email'], $soap_faultcode );
					if (! $ok) {
						@$db->execute( 'UPDATE `users` SET `user`=\''. $db->escape($username) .'\' WHERE `id`='. $user_id );
						gs_db_rollback_trans($db);
						return new GsError( 'Failed to rename user on foreign host (SOAP error).' );
					}
				}
				break;
			
			case '':
				# host does not provide any API
				gs_log( GS_LOG_NOTICE, 'Renaming user on foreign host '.$host['host'].' without any API' );
				break;
			
			default:
				gs_log( GS_LOG_WARNING, 'Failed to rename user on foreign host '.$host['host'].' - invalid API "'.$api.'"' );
				@$db->execute( 'UPDATE `users` SET `user`=\''. $db->escape($username) .'\' WHERE `id`='. $user_id );
				gs_db_rollback_trans($db);
				return new GsError( 'Failed to rename user on foreign host (Invalid API).' );
		}
	}
	
	# commit transaction
	#
	if (! gs_db_commit_trans($db)) {
		@$db->execute( 'UPDATE `users` SET `user`=\''. $db->escape($username) .'\' WHERE `id`='. $user_id );
		return new GsError( 'Failed to rename user.' );
	}
	
	# reboot the phone
	#
	if ($ext === null) {
		# get user's extension
		$ext = $db->executeGetOne( 'SELECT `name` FROM `ast_sipfriends` WHERE `_user_id`='. $user_id );
	}
	@gs_prov_phone_checkcfg_by_ext( $ext, true );
	
	return true;
}


?>