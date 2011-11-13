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
include_once( GS_DIR .'inc/gs-fns/gs_keys_get.php' );


/***********************************************************
*    class to get a user's keys on a Snom phone
***********************************************************/

//FIXME - make the class extend a base class
class GS_Softkeys_snom
{
	var $_db          = null;
	var $_user_id     = null;
	var $_user_name   = null;
	var $_profile_id  = null;
	var $_keys        = null;
	var $_default_key = array(
		'key'            => '',
		'function'       => 'line',
		'data'           => '',
		'label'          => 'Line',
		'user_writeable' => 1,
		'_set_by'        => 'p',  # provisioning
		'_setter'        => null
	);
	
	# constructor for PHP 4
	function GS_Softkeys_snom( $db=null )
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
	
	function set_profile_id( $profile_id )
	{
		$this->_profile_id = (int)$profile_id;
		return true;
	}
	
	function retrieve_keys( $phone_type, $variables=array() )
	{
		if ($this->_user_id > 0)
			$this->_keys = gs_keys_get_by_user( $this->_user_name, $phone_type );
		else
			$this->_keys = gs_keys_get_by_profile( $this->_profile_id, $phone_type );
		
		if (isGsError($this->_keys)
		||  ! is_array($this->_keys)) {
			gs_log( GS_LOG_NOTICE, (isGsError($this->_keys) ? $this->_keys->getMsg() : 'Failed to get softkeys') );
			$this->_keys = null;
			return false;
		}
		
		if (is_array($variables) && count($variables) > 0) {
			$search  = array_keys  ($variables);
			$replace = array_values($variables);
			unset($variables);
			
			foreach ($this->_keys as $key_name => $key_defs) {
				foreach ($key_defs as $inh_slf => $key_def) {
					if ($this->_keys[$key_name][$inh_slf]['data'] != '') {
						$this->_keys[$key_name][$inh_slf]['data'] =
							str_replace($search, $replace, $key_def['data']);
					}
				}
			}
		}
		
		# key "fkey0"/"P1" should be set to "line"
		#
		$this->_keys['f000']['slf'] = array(
			'key'            => 'f000',
			'function'       => 'line',
			'data'           => '',
			'label'          => 'Line',
			'user_writeable' => 0,
			'_set_by'        => 'p',  # provisioning
			'_setter'        => null
		);
		
		
		# get the pickup groups
		#
		$pgroups = array();
		$rs = $this->_db->execute(
			'SELECT DISTINCT(`p`.`id`) `id`, `p`.`title` '.
			'FROM '.
				'`pickupgroups_users` `pu` JOIN '.
				'`pickupgroups` `p` ON (`p`.`id`=`pu`.`group_id`) '.
			'WHERE `pu`.`user_id`='. ((int)$this->_user_id) .' '.
			'ORDER BY `p`.`id` '.
			'LIMIT 10' );
		while ($r = $rs->fetchRow()) {
			$pgroups[$r['id']] = $r['title'];
		}
		
		
		# fix some key definitions
		#
		foreach ($this->_keys as $key_name => $key_defs) {
			foreach ($key_defs as $inh_slf => $key_def) {
			
				# make sure the user does not set keys for pickup groups
				# which he/she does not belong to
				#
				if (in_array($key_def['function'], array('dest', 'blf'), true)
				&&  subStr($key_def['data'],0,2) === '*8') {
					if (preg_match('/(?:^|[:])\*8\*([0-9]+)/S', $key_def['data'], $m)) {
						$pgrpid = (int)lTrim($m[1],'0');
					} else {
						$pgrpid = 0;
					}
					if ($pgrpid > 0) {
						if (! array_key_exists($pgrpid, $pgroups))
							$pgrpid = 0;
					}
					if ($pgrpid < 1) {
						unset($this->_keys[$key_name][$inh_slf]);
					} else {
						$this->_keys[$key_name][$inh_slf]['data' ] =
							'*8*'. str_pad($pgrpid,5,'0',STR_PAD_LEFT);
						$title = mb_subStr(trim($pgroups[$pgrpid]),0,20);
						$this->_keys[$key_name][$inh_slf]['label'] =
							'Grp. '. ($title != '' ? $title : $pgrpid);
						unset($pgroups[$pgrpid]);
					}
				}
			}
		}
		
		# find free keys for the remaining pickup groups (if any)
		#
		//FIXME ?
		
		return true;
	}
	
	function get_keys()
	{
		return $this->_keys;
	}
	
	function get_key( $key_name )
	{
		if (is_array($this->_keys)
		&&  array_key_exists($key_name, $this->_keys)) {
			return $this->_keys[$key_name];
		} else {
			$key = $this->_default_key;
			$key['key'] = $key_name;
			return array('slf' => $key);
		}
	}
}


?>