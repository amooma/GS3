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
*    sets (/deletes) a provisioning parameter for a user
***********************************************************/

function gs_user_prov_param_set( $username, $phone_type, $param, $index, $value )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $username ))
		return new GsError( 'User must be alphanumeric.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user_id
	#
	$rs = $db->execute( 'SELECT `id`, `prov_param_profile_id` FROM `users` WHERE `user`=\''. $db->escape($username) .'\'' );
	if (! $rs)
		return new GsError( 'DB error.' );
	
	$user = $rs->fetchRow();
	if (! $user)
		return new GsError( 'Unknown user.' );
	
	if ($value !== null) {
		# check phone type
		$ok = $db->executeGetOne( 'SELECT 1 FROM `phones` WHERE `type`=\''. $db->escape($phone_type) .'\' LIMIT 1' );
		if (! $ok)
			return new GsError( 'Invalid phone type.' );
	}
	
	if ($index === null) $index = -1;
	else $index = (int)$index;
	
	$prov_profile_id = 0;
	
	# does a provisioning parameter profile already exists for this user?
	if (! $user['prov_param_profile_id']) {
		# no -> create a new one
		$ok = $db->execute( 'INSERT INTO `prov_param_profiles` (`is_group_profile`, `title`) VALUES (0 , \''. $db->escape('u-'.$username) .'\')' );
		if (! $ok)
			return new GsError( 'Failed to add a new prov_param_profile' );
		
		$prov_profile_id = (int)$db->getLastInsertId();
		
		if (! $prov_profile_id)
			return new GsError( 'DB error' );
		
		# update user
		$ok = $db->execute( 'UPDATE `users` SET `prov_param_profile_id`='. $prov_profile_id .' WHERE `id`='. $user['id'] );
		if (! $ok)
			return new GsError( 'Failed to assign the new prov_param_profile to the user' );
	}
	else {
		$prov_profile_id = (int)$user['prov_param_profile_id'];
	}
	
	if ($value !== null) {
		# set the parameter
		$ok = $db->execute( 'REPLACE INTO `prov_params` (`profile_id`, `phone_type` , `param` , `index` , `value`) VALUES ('. $prov_profile_id .', \''. $db->escape($phone_type) .'\', \''. $db->escape($param) .'\', '. $index .', \''. $db->escape($value) .'\')' );
		if (! $ok)
			return new GsError( 'Failed to add the parameter to the profile' );
	}
	else {
		# delete the parameter
		$ok = $db->execute(
			'DELETE FROM `prov_params` '.
			'WHERE '.
				'`profile_id`='. $prov_profile_id .' AND '.
				'`phone_type`=\''. $db->escape($phone_type) .'\' AND '.
				'`param`=\''. $db->escape($param) .'\' AND '.
				'`index`='. $index
			);
		if (! $ok)
			return new GsError( 'Failed to delete the parameter' );
	}
	
	return true;
}


?>