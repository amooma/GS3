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


/***********************************************************
*    change a user account
***********************************************************/

function gs_user_change( $user, $pin, $firstname, $lastname, $host_id_or_ip, $force=false, $email='' )
{
	if (! preg_match( '/^[a-zA-Z\d]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	if (! preg_match( '/^[\d]+$/', $pin ))
		return new GsError( 'PIN must be numeric.' );
	//if (! preg_match( '/^[a-zA-Z\d.\-\_ ]+$/', $firstname ))
	//	return new GsError( 'Invalid characters in first name.' );
	$firstname = preg_replace('/\s+/', ' ', trim($firstname));
	//if (! preg_match( '/^[a-zA-Z\d.\-\_ ]+$/', $lastname ))
	//	return new GsError( 'Invalid characters in last name.' );
	$lastname = preg_replace('/\s+/', ' ', trim($lastname));
	if (! defined('GS_EMAIL_PATTERN_VALID'))
		return new GsError( 'GS_EMAIL_PATTERN_VALID not defined.' );
	if ($email != '' && ! preg_match( GS_EMAIL_PATTERN_VALID, $email ))
		return new GsError( 'E-mail address must be numeric.' );
	
	include_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user_id
	#
	$user_id = (int)$db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	if (! $user_id)
		return new GsError( 'Unknown user.' );
	
	# get old host_id
	#
	$old_host_id = (int)$db->executeGetOne( 'SELECT `host_id` FROM `users` WHERE `id`='. $user_id );
	
	# check if (new) host exists
	#
	$host = gs_host_by_id_or_ip( $host_id_or_ip );	
	if (isGsError( $host ))
		return new GsError( $host->getMsg() );
	if (! is_array( $host ))
		return new GsError( 'Unknown host.' );
	
	if ($old_host_id != $host['id'] && ! $force)
		return new GsError( 'Changing the host will result in loosing mailbox messages etc. and thus will not be done without force.' );
	
	/*
	# check if queue with same ext exists
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `ast_queues` WHERE `name`=\''. $db->escape($ext) .'\'' );
	if ($num > 0)
		return new GsError( 'A queue with that name already exists.' );
	*/
	
	# update user
	#
	$ok = $db->execute( 'UPDATE `users` SET `pin`=\''. $db->escape($pin) .'\', `firstname`=\''. $db->escape($firstname) .'\', `lastname`=\''. $db->escape($lastname) .'\', `email`=\''. $db->escape($email) .'\', `host_id`='. $host['id'] .' WHERE `id`='. $user_id );
	if (! $ok)
		return new GsError( 'Failed to change user.' );
	
	# update sip account
	#
	$calleridname = trim( gs_utf8_decompose_to_ascii( $firstname .' '. $lastname ));
	$ok = $db->execute( 'UPDATE `ast_sipfriends` SET `callerid`=CONCAT(_utf8\''. $db->escape($calleridname) .'\', \' <\', `name`, \'>\') WHERE `_user_id`='. $user_id );
	if (! $ok)
		return new GsError( 'Failed to change SIP account.' );
	
	# update mailbox
	#
	$ok = $db->execute( 'UPDATE `ast_voicemail` SET `password`=\''. $db->escape($pin) .'\', `fullname`=\''. $db->escape($firstname .' '. $lastname) .'\' WHERE `_user_id`='. $user_id );
	if (! $ok)
		return new GsError( 'Failed to change mailbox.' );
	
	# new host?
	#
	if ($host['id'] != $old_host_id) {
		
		# delete from queue members
		#
		$db->execute( 'DELETE FROM `ast_queue_members` WHERE `_user_id`='. $user_id );
		
		# reload dialplan (hints!)
		#
		//@ exec( GS_DIR .'sbin/start-asterisk --dialplan' );  // <-- not the same host!!!
		//$ok = @ gs_asterisks_reload( array($old_host_id), true );
		//$ok = @ gs_asterisks_reload( array($host['id'] ), true );
		$ok = @ gs_asterisks_reload( array($old_host_id), false );
		$ok = @ gs_asterisks_reload( array($host['id'] ), false );
		/*
		if (isGsError( $ok ))
			return new GsError( $ok->getMsg() );
		if (! ok)
			return new GsError( 'Failed to reload dialplan.' );
		*/
		
		# delete from pickup groups
		#
		$db->execute( 'DELETE FROM `pickupgroups_users` WHERE `user_id`='. $user_id );
	}
	
	# get user's sip name
	#
	$ext = $db->executeGetOne( 'SELECT `name` FROM `ast_sipfriends` WHERE `_user_id`='. $user_id );
	if (! $ext)
		return new GsError( 'DB error.' );
	
	# reboot the phone
	#
	//@ shell_exec( 'asterisk -rx \'sip notify snom-reboot '. $ext .'\' >>/dev/null' );
	@ gs_prov_phone_checkcfg_by_ext( $ext, true );
	
	return true;
}


?>