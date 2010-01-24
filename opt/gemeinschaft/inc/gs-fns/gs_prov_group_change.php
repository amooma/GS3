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
require_once( GS_DIR .'lib/yadb/yadb_mptt.php' );

/***********************************************************
*    changes (/adds) a user group
***********************************************************/

function gs_prov_group_change( $id, $parent_id, $name_new, $title, $softkey_profile_id=null, $prov_param_profile_id=null, $show_ext_modules=255 )
{
	$id = (int)$id;
	if ($id < 1) $id = 0;  # add
	$parent_id = (int)$parent_id;
	if ($parent_id < 1) $parent_id = null;
	
	$name_new = preg_replace('/[^a-z0-9\-_]/', '', strToLower($name_new));
	if (! preg_match( '/^[a-z0-9\-_]+$/', $name_new ))
		return new GsError( 'Invalid group name.' );
	$title = trim($title);
	$softkey_profile_id = (int)$softkey_profile_id;
	if ($softkey_profile_id < 1) $softkey_profile_id = null;
	$prov_param_profile_id = (int)$prov_param_profile_id;
	if ($prov_param_profile_id < 1) $prov_param_profile_id = null;
	if ($show_ext_modules === null) {
		$show_ext_modules = 255;
	} else {
		$show_ext_modules = (int)$show_ext_modules;
		if ($show_ext_modules > 255
		||  $show_ext_modules <   0 ) $show_ext_modules = 255;
	}
	
	# connect to db
	#
	$DB = gs_db_master_connect();
	if (! $DB)
		return new GsError( 'Could not connect to database.' );
	
	$mptt = new YADB_MPTT($DB, 'user_groups', 'lft', 'rgt', 'id');
	
	if ($id < 1) {
		# add
		if ($parent_id < 1) {
			//return new GsError( 'Failed to add group without parent.' );
			$root_node = $mptt->find_root_node();
			if (! is_array($root_node))
				return new GsError( 'Failed to find top-level group.' );
			$parent_id = (int)@$root_node['id'];
			if ($parent_id < 1) {
				return new GsError( 'Failed to add group without parent.' );
			}
		}
		$id = (int)$mptt->insert($parent_id, array(
			'name'                   => $name_new,
			'title'                  => $title,
			'softkey_profile_id'     => $softkey_profile_id,
			'prov_param_profile_id'  => $prov_param_profile_id,
			'show_ext_modules'       => $show_ext_modules
			));
		if ($id < 1) {
			return new GsError( 'Failed to add group.' );
		}
	}
	if ($id > 0) {
		$ok = $DB->execute(
			'UPDATE `user_groups` SET '.
				'`name`=\''. $DB->escape($name_new) .'\', '.
				'`title`=\''. $DB->escape($title) .'\', '.
				'`softkey_profile_id`='. ($softkey_profile_id > 0 ? $softkey_profile_id : 'NULL') .', '.
				'`prov_param_profile_id`='. ($prov_param_profile_id > 0 ? $prov_param_profile_id : 'NULL') .', '.
				'`show_ext_modules`='. ($show_ext_modules) .' '.
			'WHERE `id`='. $id
			);
		if (! $ok) {
			return new GsError( 'Failed to change group.' );
		}
		return $id;
	}
	return false;
}

function gs_prov_group_change_by_name( $name, $parent_name, $name_new, $title, $softkey_profile_id=null, $prov_param_profile_id=null, $show_ext_modules=255 )
{
	if ($name !== null && $name !== '') {
		if (! preg_match( '/^[a-z0-9\-_]+$/', $name ))
			return new GsError( 'Group name must be alphanumeric.' );
	}
	if (! preg_match( '/^[a-z0-9\-_]+$/', $name_new ))
		return new GsError( 'Group name must be alphanumeric.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	if ($name === null || $name === '') {
		# add
		$id = null;
	} else {
		$id = (int)$db->executeGetOne( 'SELECT `id` FROM `user_groups` WHERE `name`=\''. $db->escape($name) .'\'' );
		if ($id < 1)
			return new GsError( 'Unknown group "'.$name.'".' );
	}
	
	if ($parent_name === null
	||  $parent_name === false
	||  $parent_name === '')
	{
		$parent_id = null;
	} else {
		if (! preg_match( '/^[a-z0-9\-_]+$/', $parent_name ))
			return new GsError( 'Parent group name must be alphanumeric.' );
		$parent_id = (int)$db->executeGetOne( 'SELECT `id` FROM `user_groups` WHERE `name`=\''. $db->escape($parent_name) .'\'' );
		if ($parent_id < 1)
			return new GsError( 'Parent group not found.' );
	}
	
	return gs_prov_group_change( $id, $parent_id, $name_new, $title, $softkey_profile_id, $prov_param_profile_id, $show_ext_modules );
}

function gs_prov_group_add( $parent_id, $name, $title, $softkey_profile_id=null, $prov_param_profile_id=null, $show_ext_modules=255 )
{
	return gs_prov_group_change( null, $parent_id, $name, $title, $softkey_profile_id, $prov_param_profile_id, $show_ext_modules );
}

function gs_prov_group_add_by_name( $parent_name, $name, $title, $softkey_profile_id=null, $prov_param_profile_id=null, $show_ext_modules=255 )
{
	return gs_prov_group_change_by_name( null, $parent_name, $name, $title, $softkey_profile_id, $prov_param_profile_id, $show_ext_modules );
}

?>
