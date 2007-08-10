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
*    delete a user account
***********************************************************/

function gs_user_del( $user )
{
	if (! preg_match( '/^[a-zA-Z\d]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	
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
	
	# get host_id
	#
	$host_id = (int)$db->executeGetOne( 'SELECT `host_id` FROM `users` WHERE `id`='. $user_id );
	//if (! $host_id)
	//	return new GsError( 'Unknown host.' );
	
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
	
	# delete mailbox
	#
	$ok = $db->execute( 'DELETE FROM `ast_voicemail` WHERE `_user_id`='. $user_id );
	
	# delete sip account
	#
	$ok = $db->execute( 'DELETE FROM `ast_sipfriends` WHERE `_user_id`='. $user_id );
	
	# delete user
	#
	$ok = $db->execute( 'DELETE FROM `users` WHERE `id`='. $user_id );
	
	# do a clean logout from the current phone
	#
	$ok = $db->execute( 'UPDATE `phones` SET `user_id`=NULL WHERE `user_id`='. $user_id );
	
	# reload dialplan (hints!)
	#
	if ($host_id > 0) {
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
	
	return true;
}


?>