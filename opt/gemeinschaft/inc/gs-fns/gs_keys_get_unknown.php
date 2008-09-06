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
*    class to get a user's keys on an unknown phone
*    (just passes keys through!)
***********************************************************/

//FIXME - make the class extend a base class
class GS_Softkeys_unknown
{
	var $_db          = null;
	var $_user_id     = null;
	var $_user_name   = null;
	var $_profile_id  = null;
	var $_keys        = null;
	var $_default_key = array(
		'key'            => '',
		'function'       => '',
		'data'           => '',
		'label'          => '',
		'user_writeable' => 1,
		'_set_by'        => 'p',  # provisioning
		'_setter'        => null
	);
	
	# constructor for PHP 4
	function GS_Softkeys_unknown( $db=null )
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