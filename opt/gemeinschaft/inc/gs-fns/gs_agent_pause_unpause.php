<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 4088 $
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

require_once( dirName(__FILE__) .'/../../inc/conf.php' );
include_once( GS_DIR .'inc/gs-lib.php' );
include_once( GS_DIR .'inc/gs-fns/gs_ami_events.php' );

/***********************************************************
*    sets a agents pause status
***********************************************************/

function gs_agent_pause_unpause( $agent_id, $pause, $reason='' )
{
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user_id
	#
	
	$user_id = $db->executeGetOne( 'SELECT `user_id` FROM `agents` WHERE `id`=\''. $db->escape($agent_id) .'\'' );
	if ( ! $user_id || $user_id <= 0 )
		return new GsError( 'Unknown agent.' );
	
	# get sip user
	#
	
	$user_name = $db->executeGetOne( 'SELECT `name` FROM `ast_sipfriends` WHERE `_user_id`=\''. $db->escape($user_id) .'\'' );
	if ( ! $user_name  )
		return new GsError( 'Unknown user.' );
		
		
	# switch pause state
	#

	gs_queuepause_unpause (  $user_name, $pause, $reason );
	
	return true;
		

	
}


?>