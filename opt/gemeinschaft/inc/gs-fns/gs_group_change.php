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
*    changes (/adds) a user group
***********************************************************/

function gs_group_change( $id, $parent_id, $name, $title, $softkey_profile_id=null, $prov_param_profile_id=null )
{
	$id = (int)$id;
	if ($id < 1) $id = 0;  # add
	$parent_id = (int)$parent_id;
	if ($parent_id < 1) $parent_id = null;
	$name = preg_replace('/[^a-z0-9\-_]/', '', strToLower($name));
	if (! preg_match( '/^[a-z0-9\-_]+$/', $name ))
		return new GsError( 'Invalid group name.' );
	$title = trim($title);
	$softkey_profile_id = (int)$softkey_profile_id;
	if ($softkey_profile_id < 1) $softkey_profile_id = null;
	$prov_param_profile_id = (int)$prov_param_profile_id;
	if ($prov_param_profile_id < 1) $prov_param_profile_id = null;
	
	# connect to db
	#
	$DB = gs_db_master_connect();
	if (! $DB)
		return new GsError( 'Could not connect to database.' );
	
	$mptt = new YADB_MPTT($DB, 'user_groups', 'lft', 'rgt', 'id');
	
	if ($id < 1) {
		# insert
		$id = (int)$mptt->insert($parent_id, array(
			'name'                   => $name,
			'title'                  => $title,
			'softkey_profile_id'     => $softkey_profile_id,
			'prov_param_profile_id'  => $prov_param_profile_id
			));
		if ($id < 1) {
			return new GsError( 'Failed to add group.' );
		}
	}
	if ($id > 0) {
		$ok = $DB->execute(
			'UPDATE `user_groups` SET '.
				'`name`=\''. $DB->escape($name) .'\', '.
				'`title`=\''. $DB->escape($title) .'\', '.
				'`softkey_profile_id`='. ($softkey_profile_id > 0 ? $softkey_profile_id : 'NULL') .', '.
				'`prov_param_profile_id`='. ($prov_param_profile_id > 0 ? $prov_param_profile_id : 'NULL') .' '.
			'WHERE `id`='. $id
			);
		if (! $ok) {
			return new GsError( 'Failed to change group.' );
		}
		return $id;
	}
	return false;
}


?>