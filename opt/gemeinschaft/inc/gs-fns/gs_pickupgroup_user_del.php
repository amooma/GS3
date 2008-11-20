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
include_once( GS_DIR .'inc/gs-fns/gs_prov_phone_checkcfg.php' );


/***********************************************************
*    deletes a user from a pickup group
***********************************************************/

function gs_pickupgroup_user_del( $pgroup_id, $user )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	$pgroup_id = (int)$pgroup_id;
	if ($pgroup_id < 1)
		return new GsError( 'Bad group ID.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# check group id
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `pickupgroups` WHERE `id`='. $pgroup_id );
	if ($num < 1)
		return new GsError( 'Unknown pickup group ID.' );
	
	# get user_id
	#
	$user_id = (int)$db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	if (! $user_id)
		return new GsError( 'Unknown user.' );
	
	# get user's sip name
	#
	$ext = $db->executeGetOne( 'SELECT `name` FROM `ast_sipfriends` WHERE `_user_id`='. $user_id );
	if (! $ext)
		return new GsError( 'DB error.' );
	
	# user in the group?
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `pickupgroups_users` WHERE `group_id`='. $pgroup_id .' AND `user_id`='. $user_id );
	if ($num < 1)
		return new GsError( 'User not in the group.' );
	
	# remove user from the group
	#
	$ok = $db->execute( 'DELETE FROM `pickupgroups_users` WHERE `group_id`='. $pgroup_id .' AND `user_id`='. $user_id );
	if (! $ok)
		return new GsError( 'Failed to remove user from the group.' );
	
	# remove user interface from the pickup group hint
	# ...
	
	# reboot the phone (because of the deleted key)
	#
	//@ shell_exec( 'asterisk -rx \'sip notify snom-reboot '. $ext .'\' >>/dev/null' );
	@ gs_prov_phone_checkcfg_by_ext( $ext, false );
	
	return true;
}


?>