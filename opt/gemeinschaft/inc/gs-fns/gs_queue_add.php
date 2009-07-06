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
include_once( GS_DIR .'inc/gs-fns/gs_host_by_id_or_ip.php' );


/***********************************************************
*    creates a queue
***********************************************************/

function gs_queue_add( $name, $title, $maxlen, $host_id_or_ip )
{
	if (! preg_match( '/^[\d]+$/', $name ))
		return new GsError( 'Queue extension must be numeric.' );
	$title = trim($title);
	$maxlen = (int)$maxlen;
	if ($maxlen < 0)
		return new GsError( 'Maxlen must be 0 or more.' );
	if ($maxlen > 255)
		return new GsError( 'Maxlen must be 255 or less.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# check if queue exists
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `ast_queues` WHERE `name`=\''. $db->escape($name) .'\'' );
	if ($num > 0)
		return new GsError( 'A queue with that extension already exists.' );
	
	# check if SIP user with same name exists
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `ast_sipfriends` WHERE `name`=\''. $db->escape($name) .'\'' );
	if ($num > 0)
		return new GsError( 'A SIP user with that extension already exists.' );
	
	# check if host exists
	#
	$host = gs_host_by_id_or_ip( $host_id_or_ip );
	if (isGsError( $host ))
		return new GsError( $host->getMsg() );
	if (! is_array( $host ))
		return new GsError( 'Unknown host.' );
	
	# add queue
	#
	$ok = $db->execute(
'INSERT INTO `ast_queues` (
	`_id`,
	`name`,
	`_host_id`,
	`_title`,
	`musicclass`,
	`timeout`,
	`autopause`,
	`setinterfacevar`,
	`periodic_announce_frequency`,
	`announce_frequency`,
	`announce_holdtime`,
	`retry`,
	`maxlen`,
	`strategy`,
	`joinempty`,
	`leavewhenempty`,
	`ringinuse`
) VALUES (
	NULL,
	\''. $db->escape($name) .'\',
	'. (int)$host['id'] .',
	\''. $db->escape($title) .'\',
	\'default\',
	15,
	\'no\',
	\'yes\',
	60,
	90,
	\'yes\',
	3,
	'. $maxlen .',
	\'rrmemory\',
	\'strict\',
	\'yes\',
	\'no\'
)' );
	if (! $ok)
		return new GsError( 'Failed to add queue.' );
	
	return true;
}


?>