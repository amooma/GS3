<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1120 $
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


/***********************************************************
*    info about a pickup group
***********************************************************/

function gs_pickupgroup_show( $pgroup_id )
{
	$pgroup_id = (int)$pgroup_id;
	if ($pgroup_id < 1)
		return new GsError( 'Bad group ID.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# check group id
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `pickupgroups` WHERE `id`='. $pgroup_id );
	if ($num < 1)
		return new GsError( 'Unknown pickup group ID.' );
	
	$info = array();
	
	# get group title
	#
	$title = $db->executeGetOne( 'SELECT `title` FROM `pickupgroups` WHERE `id`='. $pgroup_id );
	$info['title'] = $title;
	
	# get users
	#
	$info['users'] = array();
	$rs = $db->execute(
'SELECT
	`u`.`user`, `u`.`firstname`, `u`.`lastname`,
	`s`.`name`, `u`.`host_id`
FROM
	`pickupgroups_users` `pu` JOIN
	`users` `u` ON (`u`.`id`=`pu`.`user_id`) JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`pu`.`user_id`)
WHERE `pu`.`group_id`='. $pgroup_id );
	while ($user = $rs->fetchRow()) {
		$info['users'][$user['user']] = array(
			'firstname' => $user['firstname'],
			'lastname'  => $user['lastname'],
			'name'      => $user['name'],
			'host_id'   => $user['host_id']
		);
	}
	
	
	return $info;
}


?>