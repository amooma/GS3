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
include_once( GS_DIR .'inc/langhelper.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_is_valid_name.php' );
include_once( GS_DIR .'inc/gs-fns/gs_asterisks_reload.php' );
include_once( GS_DIR .'inc/gs-fns/gs_host_by_id_or_ip.php' );
include_once( GS_DIR .'inc/gs-fns/gs_hylafax_authfile.php' );


/***********************************************************
*    adds a user account
***********************************************************/

function gs_user_add( $user, $ext, $pin, $firstname, $lastname, $language, $host_id_or_ip, $email, $group_id=NULL, $reload=true )
{
	$ret = gs_user_is_valid_name( $user );
	if (isGsError($ret)) return $ret;
	elseif (! $ret) return new GsError( 'Invalid username.' );
	
	if (! preg_match( '/^[1-9][0-9]{1,9}$/', $ext ))
		return new GsError( 'Please use 2-10 digit extension.' );
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
	if ($group_id != null && $group_id != '' && ! preg_match( '/^[0-9]+$/', $group_id ))
		return new GsError( 'Group ID must be numeric.' );
	$group_id = (int)$group_id;
	if ($group_id < 1)
		$group_id = null;
	include_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# start transaction
	#
	gs_db_start_trans($db);
	
	# check if user exists
	#
	$num = $db->executeGetOne( 'SELECT COUNT(*) FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	if ($num > 0) {
		gs_db_rollback_trans($db);
		return new GsError( 'User exists.' );
	}
	
	# check if ext exists
	#
	$num = $db->executeGetOne( 'SELECT COUNT(*) FROM `ast_sipfriends` WHERE `name`=\''. $db->escape($ext) .'\'' );
	if ($num > 0) {
		gs_db_rollback_trans($db);
		return new GsError( 'Extension exists.' );
	}
	
	# check if queue with same ext exists
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `ast_queues` WHERE `name`=\''. $db->escape($ext) .'\'' );
	if ($num > 0) {
		gs_db_rollback_trans($db);
		return new GsError( 'A queue with that name already exists.' );
	}
	
	# check if ivr exists
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `ivrs` WHERE `name`=\''. $db->escape($ext) .'\'' );
	if ($num > 0)
		return new GsError( 'A ivr with that extension already exists.' );
		
	
	# check if host exists
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
	
	# check if user group exists
	#
	if ($group_id > 0) {
		$num = $db->executeGetOne( 'SELECT 1 FROM `user_groups` WHERE `id`='. (int)$group_id );
		if ($num < 1) {
			gs_db_rollback_trans($db);
			return new GsError( 'Unknown user group ID '. $group_id );
		}
	}
	
	# add user
	#
	$ok = $db->execute( 'INSERT INTO `users` (`id`, `user`, `pin`, `firstname`, `lastname`, `email`, `nobody_index`, `host_id`, `group_id`) VALUES (NULL, \''. $db->escape($user) .'\', \''. $db->escape($pin) .'\', _utf8\''. $db->escape($firstname) .'\', _utf8\''. $db->escape($lastname) .'\', _utf8\''. $db->escape($email) .'\', NULL, '. $host['id'] .', '. ($group_id > 0 ? $group_id : 'NULL') .' )' );
	if (! $ok) {
		gs_db_rollback_trans($db);
		return new GsError( 'Failed to add user (table users).' );
	}
	
	# get user_id
	#
	$user_id = (int)$db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	if (! $user_id) {
		gs_db_rollback_trans($db);
		return new GsError( 'DB error.' );
	}
	
	# add sip account
	#
	$callerid = trim( gs_utf8_decompose_to_ascii( $firstname .' '. $lastname )) .' <'. $ext .'>';
	$sip_pwd = rand(10000,99999).rand(10000,99999);
	$ok = $db->execute( 'INSERT INTO `ast_sipfriends` (`_user_id`, `name`, `secret`, `callerid`, `mailbox`, `setvar`, `language`) VALUES ('. $user_id .', \''. $db->escape($ext) .'\', \''. $db->escape($sip_pwd) .'\', _utf8\''. $db->escape($callerid) .'\', \''. $db->escape($ext) .'\', \''. $db->escape('__user_id='. $user_id .';__user_name='. $ext) .'\', \''. $db->escape($language) .'\')' );
	if (! $ok) {
		gs_db_rollback_trans($db);
		return new GsError( 'Failed to add user (table ast_sipfriends).' );
	}
	
	# add mailbox
	#
	if (! $host['is_foreign']) {
		$ok = $db->execute( 'INSERT INTO `ast_voicemail` (`_uniqueid`, `_user_id`, `mailbox`, `password`, `email`, `fullname`) VALUES (NULL, '. $user_id .', \''. $db->escape($ext) .'\', \''. $db->escape($pin) .'\', \'\', _utf8\''. $db->escape($firstname .' '. $lastname) .'\')' );
		if (! $ok) {
			gs_db_rollback_trans($db);
			return new GsError( 'Failed to add user (table ast_voicemail).' );
		}
	}
	
	# add mailbox (de)active entry
	#
	if (! $host['is_foreign']) {
		$ok = $db->execute( 'INSERT INTO `vm` (`user_id`, `internal_active`, `external_active`) VALUES ('. $user_id .', 0, 0)' );
		if (! $ok) {
			gs_db_rollback_trans($db);
			return new GsError( 'Failed to add user (table vm).' );
		}
	}
	
	# add user on foreign host
	#
	if ($host['is_foreign']) {
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
					gs_db_rollback_trans($db);
					return new GsError( 'Failed to add user on foreign host (SoapClient not available).' );
				}
				include_once( GS_DIR .'inc/boi-soap/boi-soap.php' );
				$soap_faultcode = null;
				$ok = gs_boi_update_extension( $api, $host['host'], $hp_route_prefix, $sub_ext, $user, $sip_pwd, $pin, $firstname, $lastname, $email, $soap_faultcode );
				if (! $ok) {
					gs_db_rollback_trans($db);
					return new GsError( 'Failed to add user on foreign host (SOAP error).' );
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
	
	# commit transaction
	#
	if (! gs_db_commit_trans($db)) {
		return new GsError( 'Failed to add user.' );
	}
	
	# reload dialplan (hints!)
	#
	if (! $host['is_foreign'] && $reload) {
		$ok = @ gs_asterisks_reload( array($host['id']), true );
		if (isGsError( $ok ))
			return new GsError( $ok->getMsg() );
		if (! $ok)
			return new GsError( 'Failed to reload dialplan.' );
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
