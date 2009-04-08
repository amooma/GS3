<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
*
* Author: Andreas Neugebauer <neugebauer@loca.net> - LocaNet oHG
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
*    deletes a callerid for a user
***********************************************************/

function gs_user_callerid_del( $user, $number, $dest)
{
	if (! preg_match( '/^[a-zA-Z\d\-]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	if (! preg_match( '/^[\d]+$/', $number ))
		return new GsError( 'Number must be numeric.' );
	if ($dest != 'internal' && $dest != 'external')
		return new GsError( 'No destination.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user_id
	#
	$user_id = $db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	if ($user_id < 1)
		return new GsError( 'Unknown user.' );
	
	
	# check if number exists (to return proper err msg)
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `users_callerids` WHERE `user_id`='. $user_id .' AND `number`=\''. $db->escape($number) .'\' AND `dest`=\'' . $db->escape($dest) . '\'' );
	if ($num < 1)
		return new GsError( 'No such number.' );
			
	# delete number
	#
	$ok = $db->execute( 'DELETE FROM `users_callerids` WHERE `user_id`='. $user_id .' AND `number`=\''. $db->escape($number) .'\'  AND `dest`=\'' . $db->escape($dest) . '\'');
	if (! $ok)
		return new GsError( 'Failed to delete callerid.' );
	return true;
}


?>