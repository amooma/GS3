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
*    adds a agent account
***********************************************************/

function gs_agent_add( $agent, $firstname, $number, $pin )
{
	if (! preg_match( '/^[1-9][0-9]{1,9}$/', $number ))
		return new GsError( 'Please use 2-10 digit extension.' );
	if ( ( strlen($pin) > 0 ) && ( ! preg_match( '/^[0-9]+$/', $pin ) ) )
		return new GsError( 'PIN must be numeric.' );
	elseif (strLen($pin) > 10)
		return new GsError( 'PIN too long (max. 10 digits).' );
	$agent = preg_replace('/\s+/', ' ', trim($agent));
	$firstname = preg_replace('/\s+/', ' ', trim($firstname));
	if (! defined('GS_EMAIL_PATTERN_VALID'))
		return new GsError( 'GS_EMAIL_PATTERN_VALID not defined.' );
	
	include_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );

	/*
	# check if agent exists
	#
	$num = $db->executeGetOne( 'SELECT COUNT(*) FROM `agents` WHERE `name`=\''. $db->escape($agent) .'\'' );
	if ($num > 0)
		return new GsError( 'Agent exists.' );
	*/
	# check if number exists
	#
	$num = $db->executeGetOne( 'SELECT COUNT(*) FROM `agents` WHERE `number`=\''. $db->escape($number) .'\'' );
	if ($num > 0)
		return new GsError( 'Agent exists.' );

	# add user
	#
	$ok = $db->execute( 'INSERT INTO `agents` (`name`, `firstname`,`number`, `pin`, `user_id`) VALUES (\''. $db->escape($agent) .'\',\''. $db->escape($firstname) .'\', \''. $db->escape($number) .'\', \''.$db->escape($pin) .'\',   0)' );
	if (! $ok)
		return new GsError( 'Failed to add agent (table agents).' );
	
	return true;
}


?>