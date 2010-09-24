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
*    returns an array of the users
***********************************************************/

function gs_users_get()
{
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get users
	#
	$rs = $db->execute(
'SELECT
	`u`.`id`, `u`.`user`, `u`.`pin`,
	`u`.`lastname`, `u`.`firstname`, `u`.`honorific`, `u`.`email`,
	`s`.`name` `ext`, `s`.`callerid`, `s`.`mailbox`, `s`.`language`,
	`h`.`host`,
	`ug`.`name` `group`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) LEFT JOIN
	`hosts` `h` ON (`h`.`id`=`u`.`host_id`) LEFT JOIN
	`user_groups` `ug` ON (`ug`.`id`=`u`.`group_id`)
ORDER BY
	`u`.`lastname`, `u`.`firstname`, `u`.`honorific`, `u`.`id`'
	);
	if (! $rs)
		return new GsError( 'Error.' );
	
	$users = array();
	while ($r = $rs->fetchRow())
		$users[] = $r;
	return $users;
}


?>
