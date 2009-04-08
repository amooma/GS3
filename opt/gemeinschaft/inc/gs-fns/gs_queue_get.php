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
*    returns information about a queue
***********************************************************/

function gs_queue_get( $name )
{
	if (! preg_match( '/^[\d]+$/', $name ))
		return new GsError( 'Queue name must be numeric.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# check if queue exists
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `ast_queues` WHERE `name`=\''. $db->escape($name) .'\'' );
	if ($num < 1)
		return new GsError( 'Unknown queue.' );
	
	# get queue info
	#
	$rs = $db->execute(
'SELECT
	`q`.`_id` `id`, `q`.`name` `ext`, `q`.`_host_id` `host_id`, `h`.`host`,
	`q`.`_title` `title`, `q`.`maxlen` `maxlen`, `q`.`_sysrec_id` `sysrec_id`
FROM
	`ast_queues` `q` LEFT JOIN
	`hosts` `h` ON (`h`.`id`=`q`.`_host_id`)
WHERE `q`.`name`=\''. $db->escape($name) .'\''
	);
	if (! $rs || ! ($queue = $rs->fetchRow()))
		return new GsError( 'Failed to get queue.' );
	
	# get members
	#
	$rs = $db->execute(
'SELECT
	`m`.`interface`,
	`u`.`id`, `u`.`user`, `u`.`firstname`, `u`.`lastname`, `u`.`host_id`, `h`.`host`
FROM
	`ast_queue_members` `m` LEFT JOIN
	`users` `u` ON (`u`.`id`=`m`.`_user_id`) LEFT JOIN
	`hosts` `h` ON (`h`.`id`=`u`.`host_id`)
WHERE `m`.`_queue_id`='. (int)$queue['id']
	);
	if (! $rs)
		return new GsError( 'Failed to get members.' );
	$queue['members'] = array();
	while ($r = $rs->fetchRow()) {
		$queue['members'][] = $r;
	}
	
	return $queue;
}


?>