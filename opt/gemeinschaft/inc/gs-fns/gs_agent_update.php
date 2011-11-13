<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 4898 $
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* 
* Author: Andreas Neugebauer <neugebauer@loca.net>
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
*    update agent-settings
***********************************************************/

function gs_agent_update( $agent, $pin='', $name, $firstname )
{
	if (! preg_match( '/^\d+$/', $agent ))
		return new GsError( 'User must be numeric.' );
	if ( ( strlen($pin) > 0 ) && ( ! preg_match( '/^[0-9]+$/', $pin ) ) )
		return new GsError( 'PIN must be numeric.' );
	elseif (strLen($pin) > 10)
		return new GsError( 'PIN too long (max. 10 digits).' );
	$name = preg_replace('/\s+/', ' ', trim($name));
	$firstname = preg_replace('/\s+/', ' ', trim($firstname));	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get agent_id
	#
	$agent_id = $db->executeGetOne( 'SELECT `id` FROM `agents` WHERE `number`=\''. $db->escape($agent) .'\'' );
	if (! $agent_id)
		return new GsError( 'Unknown user.' );
	
	# set PIN
	#
	$ok = $db->execute( 'UPDATE `agents` SET `pin`=\''. $db->escape($pin) .'\', `name`=\''. $db->escape($name) .'\', `firstname`=\''. $db->escape($firstname) .'\' WHERE `id`='. $agent_id );
	if (! $ok)
		return new GsError( 'Failed to set PIN.' );
	return true;
}


?>