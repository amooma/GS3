<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* 
* Author: Henning Holtschneider <henning@loca.net> - LocaNet oHG
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
*    adds a user to a pickup group
***********************************************************/

function gs_huntgroup_user_del( $hgroup_number, $user )
{
	if (! preg_match( '/^[a-zA-Z\d]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	$hgroup_number = (int)$hgroup_number;
	if ($hgroup_number < 1)
		return new GsError( 'Bad group ID.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# check group id
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `huntgroups` WHERE `number`='. $hgroup_number );
	if ($num < 1)
		return new GsError( 'Unknown hunt group ID.' . $hgroup_number );
	
	# get user_id
	#
	$user_id = (int)$db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	if (! $user_id)
		return new GsError( 'Unknown user.' );
		
	# user in the group?
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `huntgroups` WHERE `number`='. $hgroup_number .' AND `user_id`='. $user_id );
	if ($num < 1)
		return new GsError( 'User not in the hunt group.' );
	
	# remove user from the group
	#
	$ok = $db->execute( 'DELETE FROM `huntgroups` WHERE `number`='. $hgroup_number .' AND `user_id`='. $user_id );
	if (! $ok)
		return new GsError( 'Failed to remove user from the hunt group.' );	
	return true;
}


?>