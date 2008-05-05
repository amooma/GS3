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


/***********************************************************
*    sets a user's PIN
***********************************************************/

function gs_user_pin_set( $user, $pin='' )
{
	
	if (! preg_match( '/^[\w\d]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	if (! preg_match( '/^[\d]+$/', $pin ))
		return new GsError( 'PIN must be numeric.' );
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user_id
	#
	$user_id = $db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	$firstname = $db->executeGetOne( 'SELECT `Firstname` FROM `users` WHERE `id`=\''. $db->escape($user_id) .'\'' );
	$lastname = $db->executeGetOne( 'SELECT `Lastname` FROM `users` WHERE `id`=\''. $db->escape($user_id) .'\'' );
	if (! $user_id)
		return new GsError( 'Unknown user.' );
	
	# set PIN
	#
	$ok = $db->execute( 'UPDATE `users` SET `pin`=\''. $db->escape($pin) .'\' WHERE `id`='. $user_id );
	if (! $ok)
		return new GsError( 'Failed to set pin.' );
        $ok = $db->execute( 'UPDATE `ast_voicemail` SET `password`=\''. $db->escape($pin) .'\', `fullname`=\''. $db->escape($firstname .' '. $lastname) .'\' WHERE `_user_id`='. $user_id );
	if (! $ok)
		return new GsError( 'Failed to change mailbox.' );
	return true;
}


?>