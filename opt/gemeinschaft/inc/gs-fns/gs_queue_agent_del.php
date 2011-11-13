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
*    delete an agent from a queue
***********************************************************/

function gs_queue_agent_del( $queue_id, $agent )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $agent ))
		return new GsError( 'User must be alphanumeric.' );
	$queue_id = (int)$queue_id;
	if ($queue_id < 1)
		return new GsError( 'Bad queue ID.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# check queue id
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `ast_queues` WHERE `_id`='. $queue_id );
	if ($num < 1)
		return new GsError( 'Unknown queue ID.' );
	
	# get agent_id
	#
	$agent_id = (int)$db->executeGetOne( 'SELECT `id` FROM `agents` WHERE `number`=\''. $db->escape($agent) .'\'' );
	if (! $agent_id)
		return new GsError( 'Unknown agent.' );
	
	# delete agent to the queue
	#
	$ok = $db->execute( 'DELETE FROM `agent_queues` WHERE `agent_id`='. $agent_id .' AND `queue_id`= '. $queue_id  );
	if (! $ok)
		return new GsError( 'Failed to delete agent from queue.' );
	
	
	return true;
}


?>