<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* 
* Author: Andreas Neugebauer <neugebauer@loca.net>
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


/***********************************************************
*    sets a user's PIN
***********************************************************/

function gs_user_pin_set( $user, $pin='' )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	if (! preg_match( '/^[0-9]+$/', $pin ))
		return new GsError( 'PIN must be numeric.' );
	if (strLen($pin) < 3)
		return new GsError( 'PIN too short (min. 3 digits).' );
	elseif (strLen($pin) > 10)
		return new GsError( 'PIN too long (max. 10 digits).' );
	
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
	$user_id = $db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	if (! $user_id) {
		gs_db_rollback_trans($db);
		return new GsError( 'Unknown user.' );
	}
	
	# set PIN
	#
	$ok = $db->execute( 'UPDATE `users` SET `pin`=\''. $db->escape($pin) .'\' WHERE `id`='. $user_id );
	if (! $ok) {
		gs_db_rollback_trans($db);
		return new GsError( 'Failed to set PIN.' );
	}
	$ok = $db->execute( 'UPDATE `ast_voicemail` SET `password`=\''. $db->escape($pin) .'\' WHERE `_user_id`='. $user_id );
	if (! $ok) {
		gs_db_rollback_trans($db);
		return new GsError( 'Failed to change PIN.' );
	}
	
	# get host
	#
	$host_id = (int)$db->executeGetOne( 'SELECT `host_id` FROM `users` WHERE `id`='. $user_id );
	if ($host_id < 1) {
		gs_db_rollback_trans($db);
		return new GsError( 'Failed to change PIN.' );
	}
	$host = gs_host_by_id_or_ip($host_id);
	if (isGsError($host)) {
		gs_db_rollback_trans($db);
		return new GsError( 'Failed to change PIN. ('. $host->getMsg() .')' );
	}
	if (! is_array($host)) {
		gs_db_rollback_trans($db);
		return new GsError( 'Failed to change PIN.' );
	}
	
	# change PIN on foreign host
	#
	if ($host['is_foreign']) {
		include_once( GS_DIR .'inc/boi-soap/boi-api.php' );
		$api = gs_host_get_api( $host['id'] );
		switch ($api) {
			case 'm01':
			case 'm02':
				$rs = $db->execute(
					'SELECT '.
						'`u`.`firstname`, `u`.`lastname`, `u`.`email`, '.
						'`s`.`secret` `sip_pwd`, `s`.`name` `ext` '.
					'FROM '.
						'`users` `u` JOIN '.
						'`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) '.
					'WHERE `u`.`id`='. $user_id
					);
				if (! $userinfo = $rs->getRow()) {
					gs_db_rollback_trans($db);
					return new GsError( 'Failed to get user.' );
				}
				$ext = $userinfo['ext'];
				
				$hp_route_prefix = (string)$db->executeGetOne(
					'SELECT `value` FROM `host_params` '.
					'WHERE `host_id`='. (int)$host['id'] .' AND `param`=\'route_prefix\'' );
				$sub_ext = (strLen($ext) > strLen($hp_route_prefix))
					? subStr($ext, strLen($hp_route_prefix)) : $ext;
				gs_log( GS_LOG_DEBUG, "Mapping ext. $ext to $sub_ext for SOAP call" );
				
				//if (! class_exists('SoapClient')) {
				if (! extension_loaded('soap')) {
					gs_db_rollback_trans($db);
					return new GsError( 'Failed to change PIN on foreign host (SoapClient not available).' );
				}
				include_once( GS_DIR .'inc/boi-soap/boi-soap.php' );
				$soap_faultcode = null;
				$ok = gs_boi_update_extension( $api, $host['host'], $hp_route_prefix, $sub_ext, $user, $userinfo['sip_pwd'], $pin, $userinfo['firstname'], $userinfo['lastname'], $userinfo['email'], $soap_faultcode );
				if (! $ok) {
					gs_db_rollback_trans($db);
					return new GsError( 'Failed to change PIN on foreign host (SOAP error).' );
				}
				break;
			
			case '':
				# host does not provide any API
				gs_log( GS_LOG_NOTICE, 'Changing PIN of user '.$user.' on foreign host '.$host['host'].' without any API' );
				break;
			
			default:
				gs_log( GS_LOG_WARNING, 'Failed to change PIN of user '.$user.' on foreign host '.$host['host'].' - invalid API "'.$api.'"' );
				gs_db_rollback_trans($db);
				return new GsError( 'Failed to add user on foreign host (Invalid API).' );
		}
	}
	
	# commit transaction
	#
	if (! gs_db_commit_trans($db)) {
		return new GsError( 'Failed to change PIN.' );
	}
	return true;
}


?>