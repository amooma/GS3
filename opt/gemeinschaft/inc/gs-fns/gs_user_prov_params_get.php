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
*    gets the provisioning parameters for a user and/or phone type
***********************************************************/

function gs_user_prov_params_get( $username, $phone_type=null )
{
	if ($username !== null) {
		if (! preg_match( '/^[a-z0-9\-_.]+$/', $username ))
			return new GsError( 'User must be alphanumeric.' );
	}
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	if ($username !== null) {
		# get user_id
		#
		$rs = $db->execute( 'SELECT `id`, `prov_param_profile_id` FROM `users` WHERE `user`=\''. $db->escape($username) .'\'' );
		if (! $rs)
			return new GsError( 'DB error.' );
		
		$user = $rs->fetchRow();
		if (! $user)
			return new GsError( 'Unknown user.' );
		
		# does a provisioning parameter profile exists for this user?
		if (! $user['prov_param_profile_id']) {
			return array();
		}
	}
	
	$where = array();
	if ($phone_type !== null) {
		$where[] = '`p`.`phone_type`=\''. $db->escape($phone_type) .'\'';
	}
	if ($username === null) {
		$query =
			'SELECT '.
				'`u`.`user` `user`, '.
				'`p`.`phone_type`, `p`.`param`, `p`.`index`, `p`.`value` '.
			'FROM '.
				'`prov_params` `p` LEFT JOIN '.
				'`prov_param_profiles` `pp` ON (`pp`.`id`=`p`.`profile_id`) LEFT JOIN '.
				'`users` `u` ON (`u`.`prov_param_profile_id`=`pp`.`id`) '.
			'WHERE '.
				'`pp`.`is_group_profile`=0 '.
				(count($where)===0 ? '' : ' AND '.implode(' AND ',$where)) .
			'ORDER BY `u`.`user`, `p`.`phone_type`, `p`.`param`, `p`.`index`';
	} else {
		$query =
			'SELECT '.
				'\''. $db->escape($username) .'\' `user`, '.
				'`p`.`phone_type`, `p`.`param`, `p`.`index`, `p`.`value` '.
			'FROM '.
				'`prov_params` `p` '.
			'WHERE '.
				'`p`.`profile_id`='. (int)$user['prov_param_profile_id'] .' '.
				(count($where)===0 ? '' : ' AND '.implode(' AND ',$where)) .
			'ORDER BY `p`.`phone_type`, `p`.`param`, `p`.`index`';
	}
	$rs = $db->execute( $query );
	if (! $rs)
		return new GsError( 'DB error.' );
	
	$params = array();
	while ($r = $rs->fetchRow()) {
		if ($r['index'] == -1) $r['index'] = null;
		$params[] = $r;
	}
	return $params;
}


?>