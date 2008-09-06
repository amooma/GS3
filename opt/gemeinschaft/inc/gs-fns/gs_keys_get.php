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
include_once( GS_DIR .'lib/yadb/yadb_mptt.php' );
include_once( GS_DIR .'inc/gs-fns/gs_keys_get_unknown.php' );


/***********************************************************
*    returns an array of the softkeys
***********************************************************/

function gs_keys_get_by_profile_or_user( $profile_id, $username, $phone_type )
{
	$profile_id = (int)$profile_id;
	if ($profile_id < 1 && $username == '')
		return new GsError( 'Neither profile_id nor user specified.' );
	if ($profile_id > 0 && $username != '')
		return new GsError( 'Both profile_id and user specified.' );
	if (! preg_match( '/^[a-z0-9\-_]+$/', $phone_type ))
		return new GsError( 'Phone type must be alphanumeric.' );
	
	//gs_log( GS_LOG_DEBUG, "Getting keys: profile_id: $profile_id, user: $username, " );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	$user_id  = 0;
	$group_id = 0;
	
	# get user
	#
	if ($username != '') {
		$rs = $db->execute(
			'SELECT `id`, `group_id`, `softkey_profile_id` '.
			'FROM `users` '.
			'WHERE `user`=\''. $db->escape($username) .'\''
			);
		if (! $rs)
			return new GsError( 'DB error.' );
		$r = $rs->fetchRow();
		if (! $r)
			return new GsError( "User $username not found." );
		$user_id    = (int)$r['id'];
		$group_id   = (int)$r['group_id'];
		$profile_id = (int)$r['softkey_profile_id'];
		//gs_log( GS_LOG_DEBUG, "Getting keys: profile_id: $profile_id, user_id: $user_id, group_id: $group_id" );
	}
	if ($profile_id < 1 && $user_id < 1)
		return new GsError( 'Neither profile_id nor user_id specified.' );
	
	# is it a user's profile?
	#
	if ($user_id > 0) {
		$is_user_profile = true;
	} else {
		$rs = $db->execute(
			'SELECT `is_user_profile` '.
			'FROM `softkey_profiles` '.
			'WHERE `id`='. $profile_id );
		if (!($r = $rs->getRow()))
			return new GsError( 'Softkey profile not found.' );
		$is_user_profile = (bool)(int)$r['is_user_profile'];
	}
	
	# check if we should inherit from any groups
	#
	if ($is_user_profile) {  # user profile
		if ($user_id < 1) {
			# find the user which this profile belongs to and the group of
			# this user
			$rs = $db->execute(
				'SELECT `id`, `group_id` '.
				'FROM `users` '.
				'WHERE `softkey_profile_id`='. $profile_id );
			if ($rs->numRows() == 1) {
				# profile belongs to exactly one user. find parent groups.
				$r = $rs->fetchRow();
				$group_id = (int)$r['group_id'];
				/*
				if ($user_id !== (int)$r['id']) {
					gs_log( GS_LOG_WARNING, "Function probably called with wrong user_id" );
				}
				*/
				$user_id  = (int)$r['id'];
			}
			else {
				# profile belongs to no user or to more than one user or
				# to an unknown number of users. can't find parent groups.
				gs_log( GS_LOG_WARNING, "Softkey profile $profile_id belongs to more than one user" );
			}
			//gs_log( GS_LOG_DEBUG, "Getting keys: profile_id: $profile_id, user_id: $user_id, group_id: $group_id" );
		}
		/*
		else {
			# find the group and profile_id of this user
			$rs = $db->execute(
				'SELECT `softkey_profile_id`, `group_id` '.
				'FROM `users` '.
				'WHERE `id`='. $user_id );
			$r = $rs->fetchRow();
			if (! $r) {
				return new GsError( "User $user_id not found." );
			}
			$group_id = (int)$r['group_id'];
			if ($profile_id !== (int)$r['softkey_profile_id']) {
				gs_log( GS_LOG_WARNING, "Function probably called with wrong profile_id" );
			}
			$profile_id  = (int)$r['softkey_profile_id'];
			//gs_log( GS_LOG_DEBUG, "Getting keys: profile_id: $profile_id, user_id: $user_id, group_id: $group_id" );
		}
		*/
	}
	else {  # group profile
		# find the group(s) which this profile belongs to (if any)
		$rs = $db->execute(
			'SELECT `id` '.
			'FROM `user_groups` '.
			'WHERE `softkey_profile_id`='. $profile_id );
		if ($rs->numRows() == 1) {
			# profile belongs to exactly one group. find parent groups.
			$group_id = (int)$rs->getField('id');
		}
		else {
			# profile belongs to no group or to more than one group or
			# to an unknown number of groups. can't find parent groups.
		}
		//gs_log( GS_LOG_DEBUG, "Getting keys: profile_id: $profile_id, user_id: $user_id, group_id: $group_id" );
	}
	
	$keys = array();
	
	if ($group_id > 0) {
		# find parent groups
		$mptt = new YADB_MPTT($db, 'user_groups', 'lft', 'rgt', 'id');
		$path = @$mptt->get_path_to_node( $group_id, true );
		if (! is_array($path))
			return new GsError( 'DB error.' );
		foreach ($path as $group) {
			if ($group['softkey_profile_id'] > 0) {
				//gs_log( GS_LOG_DEBUG, "Getting keys: group_id: ".$group['id']." (profile_id: ".$group['softkey_profile_id'].")" );
				$rs = $db->execute(
					'SELECT `key`, `function`, `data`, `label`, `user_writeable` '.
					'FROM `softkeys` '.
					'WHERE '.
						'`profile_id`='. $group['softkey_profile_id'] .' AND '.
						'`phone_type`=\''. $db->escape($phone_type) .'\' '.
					'ORDER BY `key`' );
				while ($r = $rs->fetchRow()) {
					if ($r['function'] === '') continue;  # inherited
					
					/*
					$keyname = $r['key'];
					unset($r['key']);
					$keys[$keyname] = $r;
					*/
					
					$r['_set_by'] = 'g';
					$r['_setter'] = $group['id'];
					
					if ($group['id'] != $group_id) {  # inherited
						$keys[$r['key']]['inh'] = $r;
					} else {  # defined by this group
						if (! $is_user_profile) {
							$keys[$r['key']]['slf'] = $r;
						} else {
							$keys[$r['key']]['inh'] = $r;
						}
					}
				}
			}
		}
	}
	
	if (($is_user_profile || $group_id < 1) && $profile_id > 0) {
		//gs_log( GS_LOG_DEBUG, "Getting keys: user_id: $user_id, profile_id: $profile_id" );
		$rs = $db->execute(
			'SELECT `key`, `function`, `data`, `label`, `user_writeable` '.
			'FROM `softkeys` '.
			'WHERE '.
				'`profile_id`='. $profile_id .' AND '.
				'`phone_type`=\''. $db->escape($phone_type) .'\' '.
			'ORDER BY `key`' );
		while ($r = $rs->fetchRow()) {
			if (array_key_exists($r['key'], $keys)
			&&  array_key_exists('inh', $keys[$r['key']])
			&&  ! $keys[$r['key']]['inh']['user_writeable']) {
				# user not allowed to overwrite key
				continue;
			}
			
			/*
			$keyname = $r['key'];
			unset($r['key']);
			$keys[$keyname] = $r;
			*/
			
			if ($is_user_profile) {
				$r['_set_by'] = 'u';
				$r['_setter'] = $user_id;
			} else {
				$r['_set_by'] = 'g';
				$r['_setter'] = $group_id;
			}
			
			$keys[$r['key']]['slf'] = $r;
		}
	}
	
	return $keys;
}


function gs_keys_get_by_profile( $profile_id, $phone_type )
{
	return gs_keys_get_by_profile_or_user( $profile_id, null, $phone_type );
}

function gs_keys_get_by_user( $username, $phone_type )
{
	return gs_keys_get_by_profile_or_user( null, $username, $phone_type );
}

# legacy alias for gs_keys_get_by_user()
function gs_keys_get( $username, $phone_type )
{
	return gs_keys_get_by_user( $username, $phone_type );
}



/***********************************************************
*    returns the corresponding GS_Softkeys_* class
***********************************************************/

function gs_get_key_prov_obj( $phone_type, $db_conn=null )
{
	if (! preg_match('/^([a-z0-9]+)/', $phone_type, $m)) {
		return false;
	}
	$phone_type_main = $m[1];
	$classname = 'GS_Softkeys_'.$phone_type_main;
	if (class_exists($classname)) {
		$GS_Softkeys = new $classname( $db_conn );
		return is_object($GS_Softkeys) ? $GS_Softkeys : false;
	}
	$classname = 'GS_Softkeys_unknown';
	if (class_exists($classname)) {
		$GS_Softkeys = new $classname( $db_conn );
		return is_object($GS_Softkeys) ? $GS_Softkeys : false;
	}
	return false;
}



?>