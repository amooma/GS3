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
*    deletes a queue
***********************************************************/

function gs_queue_del( $name )
{
	if (! preg_match( '/^[\d]+$/', $name ))
		return new GsError( 'Queue name must be numeric.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	$CDR_DB = gs_db_cdr_master_connect();
	if (! $CDR_DB) {
		echo 'CDR DB error.';
		return;
	}
	
	# check if queue exists
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `ast_queues` WHERE `name`=\''. $db->escape($name) .'\'' );
	if ($num < 1)
		return new GsError( 'Unknown queue.' );
	
	# get queue_id
	#
	$queue_id = (int)$db->executeGetOne( 'SELECT `_id` FROM `ast_queues` WHERE `name`=\''. $db->escape($name) .'\'' );
	if ($queue_id < 1)
		return new GsError( 'Unknown queue.' );
	
	# delete queue members
	#
	$db->execute( 'DELETE FROM `ast_queue_members` WHERE `_queue_id`='. $queue_id );

	# delete agent relations
	#
	$db->execute( 'DELETE FROM `agent_queues` WHERE `_queue_id`='. $queue_id );
	
	# delete queue callforwards
	#
	$db->execute( 'DELETE FROM `queue_callforwards` WHERE `queue_id`='. $queue_id );
	
	# delete queue log
	#
	$CDR_DB->execute( 'DELETE FROM `queue_log` WHERE `queue_id`='. $queue_id );
	
	# delete queue
	#
	$ok = $db->execute( 'DELETE FROM `ast_queues` WHERE `_id`='. $queue_id .' LIMIT 1' );
	
	if (! $ok)
		return new GsError( 'Failed to delete queue.' );
	
	return true;
}


?>