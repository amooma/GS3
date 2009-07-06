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


/***********************************************************
*    returns an array of the hosts
***********************************************************/

function gs_hosts_get( $foreign=false, $group_id=null )
{
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get hosts
	#
	$where = array();
	if ($foreign !== null) {
		$where[] = '`is_foreign`='. ($foreign ? '1':'0');
	}
	if ($group_id !== null) {
		$where[] = '`group_id`='. (int)$group_id;
	}
	$query =
'SELECT `id`, `host`, `comment`, `is_foreign`, `group_id`
FROM `hosts`
'. (count($where)===0 ? '' : ('WHERE '.implode(' AND ', $where))) .'
ORDER BY `is_foreign`,`host`'
	;
	$rs = $db->execute($query);
	if (! $rs)
		return new GsError( 'Error.' );
	
	$hosts = array();
	while ($r = $rs->fetchRow())
		$hosts[] = $r;
	return $hosts;
}


?>