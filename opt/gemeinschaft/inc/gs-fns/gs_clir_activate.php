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
include_once( GS_DIR .'inc/gs-fns/gs_astphonebuttons.php' );

/***********************************************************
*    (de)activates CLIR for a user for calls to
*    internal/external
***********************************************************/

function gs_clir_activate( $user, $dest, $active )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	if (! in_array( $dest, array('internal','external'), true ))
		return new GsError( 'Dest must be internal|external.' );
	if (! in_array( $active, array('no','yes','once'), true ))
		return new GsError( 'Active must be no|yes|once.' );
	
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
	
	# check if has entry
	#
	$num = $db->executeGetOne( 'SELECT COUNT(*) FROM `clir` WHERE `user_id`='. $user_id );
	if ($num < 1) {
		$ok = $db->execute( 'INSERT INTO `clir` (`user_id`, `internal_restrict`, `external_restrict`) VALUES ('. $user_id .', \'no\', \'no\')' );
	} else
		$ok = true;
	
	# set clir
	#
	$field = $dest .'_restrict';
	$ok = $ok && $db->execute( 'UPDATE `clir` SET `'. $field .'`=\''. $active .'\' WHERE `user_id`='. $user_id );
	if (! $ok)
		return new GsError( 'Failed to set CLIR.' );
	if ( GS_BUTTONDAEMON_USE == true ) {
		$user_name = $db->executeGetOne( 'SELECT `name` FROM `ast_sipfriends` WHERE `_user_id`=\''. $db->escape($user_id) .'\'' );
		if (! $user_name)
			return new GsError( 'Unknown user.' );
		gs_buttondeamon_clir_update($user_name);
	}
	return true;
}


?>