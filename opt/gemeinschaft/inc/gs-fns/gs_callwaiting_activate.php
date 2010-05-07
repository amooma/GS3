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
//include_once( GS_DIR .'inc/gs-fns/gs_prov_phone_checkcfg.php' ); //FIXME


/***********************************************************
*    (de)activates call waiting for a user
***********************************************************/

function gs_callwaiting_activate( $user, $active )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	$active = !! $active;
	
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
	
	# (de)activate
	#
	$num = $db->executeGetOne( 'SELECT COUNT(*) FROM `callwaiting` WHERE `user_id`='. $user_id );
	if ($num < 1) {
		$ok = $db->execute( 'INSERT INTO `callwaiting` (`user_id`, `active`) VALUES ('. $user_id .', 0)');
	} else
		$ok = true;
	$ok = $ok && $db->execute( 'UPDATE `callwaiting` SET `active`='. (int)$active .' WHERE `user_id`='. $user_id );
	if (! $ok)
		return new GsError( 'Failed to set call waiting.' );
	
	
	
	# reload phone config
	#
	//$user_name = $db->executeGetOne( 'SELECT `name` FROM `ast_sipfriends` WHERE `_user_id`='. $user_id );
	//@ exec( 'asterisk -rx \'sip notify snom-reboot '. $user_name .'\'' );
	//@ gs_prov_phone_checkcfg_by_user( $user, false ); //FIXME
	
	return true;
}


?>