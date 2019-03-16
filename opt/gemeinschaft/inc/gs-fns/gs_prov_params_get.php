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


/***********************************************************
*    returns an array of the provisioning parameters
***********************************************************/

function gs_prov_params_get( $username, $phone_type )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $username ))
		return new GsError( 'User must be alphanumeric.' );
	if (! preg_match( '/^[a-z0-9\-_]+$/', $phone_type ))
		return new GsError( 'Phone type be alphanumeric.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user
	#
	$rs = $db->execute(
		'SELECT `id`, `group_id` '.
		'FROM `users` '.
		'WHERE `user`=\''. $db->escape($username) .'\''
		);
	if (! $rs)
		return new GsError( 'DB error.' );
	$user = $rs->fetchRow();
	if (! $user)
		return new GsError( 'No such user.' );
	
	# get keys for nested groups
	#
	$params = array();
	if ($user['group_id'] > 0) {
		$mptt = new YADB_MPTT($db, 'user_groups', 'lft', 'rgt', 'id');
		$path = @$mptt->get_path_to_node( $user['group_id'], true );
		if (! is_array($path))
			return new GsError( 'DB error.' );
		foreach ($path as $group) {
			if ($group['prov_param_profile_id'] > 0) {				
				//echo 'Get group\'s prov param profile, id '. $group['prov_param_profile_id'] ."\n";
				$rs = $db->execute(
					'SELECT `param`, `index`, `value` '.
					'FROM `prov_params` '.
					'WHERE '.
						'`profile_id`='. $group['prov_param_profile_id'] .' AND '.
						'`phone_type`=\''. $db->escape($phone_type) .'\' '.
					'ORDER BY `param`, `index`' );
				while ($r = $rs->fetchRow()) {
					$r['_set_by'] = 'g';
					$r['_setter'] = $user['group_id'];
					
					$params[$r['param']][$r['index']] = $r['value'];
				}
				
			}
		}
	}

	# get keys for phonetype
	if($user['group_id'] < 1) {
		$mptt = new YADB_MPTT($db, 'user_groups', 'lft', 'rgt', 'title');
		$group = $mptt->_db_query( 'SELECT * FROM user_groups WHERE `title`=\''. $db->escape($phone_type) .'\'' );		
		if (! $group)
			return new GsError( 'DB error.' );
		if ($group['prov_param_profile_id'] > 0) {				
			//echo 'Get group\'s prov param profile, id '. $group['prov_param_profile_id'] ."\n";
			$rs = $db->execute(
				'SELECT `param`, `index`, `value` '.
				'FROM `prov_params` '.
				'WHERE '.
					'`profile_id`='. $group['prov_param_profile_id'] .' AND '.
					'`phone_type`=\''. $db->escape($phone_type) .'\' '.
				'ORDER BY `param`, `index`' );
			while ($r = $rs->fetchRow()) {
				$r['_set_by'] = 'g';
				$r['_setter'] = $user['group_id'];
				
				$params[$r['param']][$r['index']] = $r['value'];
			}
		}
	}

	return $params;
}




/***********************************************************
*    returns the corresponding GS_ProvParams_* class
***********************************************************/

function gs_get_prov_params_obj( $phone_type, $db_conn=null )
{
	/*
	if (! preg_match('/^([a-z0-9]+)/', $phone_type, $m)) {
		return false;
	}
	$phone_type_main = $m[1];
	$classname = 'GS_ProvParams_'.$phone_type_main;
	if (class_exists($classname)) {
		$GS_Softkeys = new $classname( $db_conn );
		return is_object($GS_Softkeys) ? $GS_Softkeys : false;
	}
	*/
	$classname = 'GS_ProvParams_unknown';
	if (class_exists($classname)) {
		$GS_Softkeys = new $classname( $db_conn );
		return is_object($GS_Softkeys) ? $GS_Softkeys : false;
	}
	return false;
}



/***********************************************************
*    class to get a user's prov params on an unknown phone
*    (just passes params through!)
***********************************************************/

//FIXME - make the class extend a base class
class GS_ProvParams_unknown
{
	var $_db          = null;
	var $_user_id     = null;
	var $_user_name   = null;
	var $_params      = null;
	
	# constructor for PHP 4
	function GS_ProvParams_unknown( $db=null )
	{
		if ($db) {
			$this->_db = $db;
		}
		if (! $db) {
			$this->_db = gs_db_master_connect();
		}
	}
	
	function set_user( $user_name )
	{
		$user_id = (int)$this->_db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $this->_db->escape($user_name) .'\'' );
		if ($user_id < 1) return false;
		$this->_user_id   = $user_id;
		$this->_user_name = $user_name;
		return true;
	}
	
	function retrieve_params( $phone_type, $variables=array() )
	{
		$this->_params = gs_prov_params_get( $this->_user_name, $phone_type );
		if (isGsError( $this->_params )
		||  ! is_array($this->_params)) {
			$this->_params = null;
			return false;
		}
		if (is_array($variables) && count($variables) > 0) {
			$search  = array_keys  ($variables);
			$replace = array_values($variables);
			unset($variables);
			
			foreach ($this->_params as $param => $arr) {
				foreach ($arr as $index => $val) {
					$this->_params[$param][$index] = str_replace($search, $replace, $val);
				}
			}
		}
		return true;
	}
	
	function get_params()
	{
		return $this->_params;
	}
}


?>