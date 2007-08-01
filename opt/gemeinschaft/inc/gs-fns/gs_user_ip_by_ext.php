<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1120 $
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
*    returns the IP address of the phone where the user
*    (by extension) is logged in
***********************************************************/

function gs_user_ip_by_ext( $ext )
{
	if (! preg_match( '/^[\d]+$/', $ext ))
		return new GsError( 'Extension must be numeric.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user_id
	#
	$user_id = (int)$db->executeGetOne( 'SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`=\''. $db->escape( $ext ) .'\'' );
	if (! $user_id)
		return new GsError( 'Unknown extension.' );
	
	# get IP address
	#
	$current_ip = $db->executeGetOne( 'SELECT `current_ip` FROM `users` WHERE `id`='. $user_id );
	if (! $current_ip) return null;
	
	# check if the user is actually logged in at a phone (or if that
	# IP address is an outdated entry)
	#
	$phone_id = (int)$db->executeGetOne( 'SELECT `id` FROM `phones` WHERE `user_id`='. $user_id .' LIMIT 1' );
	if ($phone_id < 1) return null;
	
	return $current_ip;
}


?>