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


/***********************************************************
*    sets a user's email notification setting
***********************************************************/

function gs_user_email_notify_set( $user, $notify )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	$notify = (int)$notify;
	if (! in_array($notify, array(0,1,2), true))
		return new GsError( 'Notify must be 0, 1 or 2.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user_id
	#
	$user_id = $db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	if (! $user_id)
		return new GsError( 'Unknown user.' );
	
	if ($notify != 0) {
		
		# get host_id
		#
		$host_id = (int)$db->executeGetOne( 'SELECT `host_id` FROM `users` WHERE `id`='. $user_id );
		
		# get is_foreign
		#
		$is_foreign = (int)$db->executeGetOne( 'SELECT `is_foreign` FROM `hosts` WHERE `id`='. $host_id );
		if ($is_foreign)
			return new GsError( 'Can\'t activate email notification for foreign user.' );
	}
	
	# set email notification
	#
	$ok = $db->execute( 'UPDATE `vm` SET `email_notify`='. (int)$notify .' WHERE `user_id`='. $user_id );
	if (! $ok)
		return new GsError( 'Failed to set email notification.' );
	return true;
}


?>