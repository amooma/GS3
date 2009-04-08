<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 4818 $
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
*
* Author: Andreas Neugebauer <neugebauer@loca.net>
*
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
*    adds a agent to a queue
***********************************************************/

function gs_queue_agent_add( $queue_id, $agent )
{
	if (! preg_match( '/^[a-zA-Z\d]+$/', $agent ))
		return new GsError( 'User must be alphanumeric.' );
	$queue_id = (int)$queue_id;
	if ($queue_id < 1)
		return new GsError( 'Bad queue ID.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# check agent id
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `ast_queues` WHERE `_id`='. $queue_id );
	if ($num < 1)
		return new GsError( 'Unknown queue ID.' );
	
	# get agent_id
	#
	$agent_id = (int)$db->executeGetOne( 'SELECT `id` FROM `agents` WHERE `number`=\''. $db->escape($agent) .'\'' );
	if (! $agent_id)
		return new GsError( 'Unknown agent.' );
	
	
	# agent already in the queue?
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `agent_queues` WHERE `agent_id`='. $agent_id .' AND `queue_id`='. $queue_id );
	if ($num > 0)
		return new GsError( 'Agent already in the queue.' );
	
	# add agent to the queue
	#
	$ok = $db->execute( 'INSERT INTO `agent_queues` (`agent_id`, `queue_id`) VALUES ('. $agent_id .', '. $queue_id .')' );
	if (! $ok)
		return new GsError( 'Failed to add agent to the queue.' );
	
	
	return true;
}


?>