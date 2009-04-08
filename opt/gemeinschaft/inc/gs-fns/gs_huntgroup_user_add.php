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

function gs_huntgroup_user_add( $hgroup_number, $strategy, $user, $timeout=5 )
{
	if (! preg_match( '/^[a-zA-Z\d]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	$hgroup_number = (int)$hgroup_number;
	if ($hgroup_number < 1)
		return new GsError( 'Bad group ID.' );
	if ( ( $strategy != 'linear' ) && ( $strategy != 'parallel' ) )
		return new GsError( 'Bad ring strategy.' );
	
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
	
	# check group id
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `huntgroups` WHERE `number`='. $hgroup_number );

	if ($num < 1) {
		$sequence_no = 1;
	} else {
		# get max sequence number
		#
		$sequence_no = (int)$db->executeGetOne( 'SELECT MAX(`sequence_no`) FROM `huntgroups` WHERE `number`='. $hgroup_number );
		if ($sequence_no < 1)
			$sequence_no = 1;
		else
			$sequence_no++;

		# user already in the group?
		#
		$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `huntgroups` WHERE `number`='. $hgroup_number .' AND `user_id`='. $user_id );
		if ($num > 0)
			return new GsError( 'User already in the hunt group.' );
	}
	
	# add user to the group
	#
	$ok = $db->execute( 'INSERT INTO `huntgroups` (`number`, `strategy`, `sequence_no`, `user_id`, `timeout`) VALUES ('. $hgroup_number . ',\'' . $strategy . '\',' . $sequence_no . ',' . $user_id . ',' . $timeout .')' );
	if (! $ok)
		return new GsError( 'Failed to add user to the hunt group.' );
		
	if ( $strategy == 'parallel' ) {
		$ok = $db->execute( 'UPDATE `huntgroups` SET `timeout`=' . $timeout . ' WHERE `number`=' . $hgroup_number);
		if (! $ok)
			return new GsError( 'Failed to set huntgroup timeout.' );
	}
	
	return true;
}


?>