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
include_once( GS_DIR .'inc/gs-fns/gs_astphonebuttons.php' );
require_once( GS_DIR .'lib/yadb/yadb_mptt.php' );


/***********************************************************
*    deletes a user group
***********************************************************/

function gs_prov_group_del( $id )
{
	$id = (int)$id;
	if ($id < 1)
		return new GsError( 'Invalid group ID.' );
	
	$DB = gs_db_master_connect();
	if (! $DB)
		return new GsError( 'Could not connect to database.' );
	
	$mptt = new YADB_MPTT($DB, 'user_groups', 'lft', 'rgt', 'id');
	if ( GS_BUTTONDAEMON_USE == false ) {
		return $mptt->delete( $id, true );
	}
	else {
		$ret = $mptt->delete( $id, true );
		if ( !isGsError($ret) && $ret ) {
			gs_buttondeamon_usergroup_remove( $id );
		}
	}	
}

function gs_prov_group_del_by_name( $group )
{
	if (! preg_match( '/^[a-z0-9\-_]+$/', $group ))
		return new GsError( 'Group must be alphanumeric.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	$group_id = (int)$db->executeGetOne( 'SELECT `id` FROM `user_groups` WHERE `name`=\''. $db->escape($group) .'\'' );
	if ($group_id < 1)
		return new GsError( 'Unknown group.' );
	
	return gs_prov_group_del( $group_id );
}

?>