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
require_once( GS_DIR .'lib/yadb/yadb_mptt.php' );


/***********************************************************
*    adds a user group
***********************************************************/

function gs_group_add($group_id, $group_name, $title, $parent_id, $key_profile_id, $prov_param_profile_id)
{
	if ($key_profile_id < 1) $key_profile_id = null;
	if ($prov_param_profile_id < 1) $prov_param_profile_id = null;
	if ($parent_id < 1) $parent_id = null;
	
	if ($group_name == '')
		return new GsError( 'group_name is empty!' );
	
	# connect to db
	#
	$DB = gs_db_master_connect();
	if (! $DB)
		return new GsError( 'Could not connect to database.' );
		
	$mptt = new YADB_MPTT($DB, 'user_groups', 'lft', 'rgt', 'id');
	
	if ($group_id < 1) {
			# insert
			$group_id = $mptt->insert($parent_id, array(
					'name'                  => $group_name,
					'title'                 => $title,
					'softkey_profile_id'    => $key_profile_id,
					'prov_param_profile_id' => $prov_param_profile_id
					));
		}
	if ($group_id > 0) {
			$DB->execute(
				'UPDATE `user_groups` SET '.
					'`name`=\''. $DB->escape($group_name) .'\', '.
					'`title`=\''. $DB->escape($title) .'\', '.
					'`softkey_profile_id`='. ($key_profile_id > 0 ? $key_profile_id : 'NULL') .', '.
					'`prov_param_profile_id`='. ($prov_param_profile_id > 0 ? $prov_param_profile_id : 'NULL') .' '.
				'WHERE `id`='. $group_id
				);
		}
}
	
	
?>