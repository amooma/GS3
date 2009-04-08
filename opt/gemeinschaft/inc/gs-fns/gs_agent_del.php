<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 4818 $
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
*
*  Author: Andreas Neugebauer <neugebauer@loca.net>
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
*    delete a agent account
***********************************************************/

function gs_agent_del( $agent )
{
	if (! preg_match( '/^\d+$/', $agent ))
		return new GsError( 'Agent-number must be numeric.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user_id
	#
	$agent_id = (int)$db->executeGetOne( 'SELECT `id` FROM `agents` WHERE `number`=\''. $db->escape($agent) .'\'' );
	if (! $agent_id)
		return new GsError( 'Unknown agent.' );
	
	# delete user
	#
	$ok = $db->execute( 'DELETE FROM `agents` WHERE `id`='. $agent_id );
	
	
	return true;
}


?>