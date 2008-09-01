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
*    returns an array of the user groups
***********************************************************/

function gs_groups_get()
{
	$DB = gs_db_master_connect();
	if (! $DB)
		return new GsError( 'Could not connect to database.' );
	
	$mptt = new YADB_MPTT($DB, 'user_groups', 'lft', 'rgt', 'id');
	$groups = $mptt->get_tree_as_list( null );
	if (! is_array($groups)) {
		return new GsError( 'Failed to get the list of groups' );
	}
	return $groups;
}


?>