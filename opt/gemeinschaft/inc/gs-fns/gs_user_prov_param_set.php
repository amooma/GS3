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
include_once( GS_DIR .'inc/db_connect.php' );


/***********************************************************
*    set a provisioning parameter for a user
***********************************************************/

function gs_user_prov_param_set( $username, $index, $phone_type, $param, $value )
{
	if (! preg_match( '/^[a-zA-Z\d\-]+$/', $username ))
		return new GsError( 'User must be alphanumeric.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user_id
	#
	$res = $db->execute( 'SELECT * FROM `users` WHERE `user`=\''. $db->escape($username) .'\'' );
	
	if (! $res)
		return new GsError( 'Error.' );
	
	$user = $res->fetchRow();
	if (! $user)
		return new GsError( 'Error.' );
	
	if ($user['id'] < 1)
		return new GsError( 'Unknown user.' );
	
	$id = 0;
	
	# does a provisioning parameter profile already exists for this user?
	if (! $user['prov_profile_id']) {
		# no -> create a new one
		$ok = $db->execute( 'INSERT INTO `prov_param_profiles` (`is_group_profile`, `title`) VALUES (0 , \''. $db->escape('u-'.$username) .'\')' );
		if (! $ok)
			return new GsError( 'Failed to add a new prov_param_profile' );
		
		$id = (int)$db->executeGetOne( 'SELECT `id` FROM `prov_param_profiles` WHERE `title`=\''. $db->escape('u-'.$username) .'\'' );
		
		if (! $id)
			return new GsError( 'Error' );
		
		# update user
		$ok = $db->execute( 'UPDATE `users` SET `prov_profile_id`='. $id .' WHERE `id`='. $user['id'] );
		if (! $ok)
			return new GsError( 'Failed to assing the new prov_param_profile to the user' );
	}
	else {
		$id = (int)$user['prov_profile_id'];
	}
	
	# add the parameter to the profile
	$ok = $db->execute( 'REPLACE INTO `prov_params` (`profile_id`, `phone_type` , `param` , `index` , `value`) VALUES ('. $id .', \'siemens-os60\', \''. $db->escape($param) .'\', -1, \''. $db->escape($value) .'\')' );
	if (! $ok)
		return new GsError( 'Failed to add the parameter to the profile' );
	
	return true;
}


?>