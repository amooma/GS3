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
*    sets a user's DND status
***********************************************************/

function gs_user_dnd_toggle( $user_id )
{
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user_id
	#
	
	$user_name = $db->executeGetOne( 'SELECT `name` FROM `ast_sipfriends` WHERE `_user_id`=\''. $db->escape($user_id) .'\'' );
	if (! $user_name)
		return new GsError( 'Unknown user.' );
	
	$dnd = $db->executeGetOne( 'SELECT `active` FROM `dnd` WHERE `_user_id`=\''. $db->escape($user_id) .'\'' );
	if (! $dnd)
		$dnd = 'no';
	
	# toggle-dnd
	#
	$new_dnd = 'no';
	if($dnd == 'no')$new_dnd = 'yes';
	
	$ok = $db->execute("INSERT INTO `dnd` (`_user_id`, `active`) VALUES (" . $user_id . ", '" . $new_dnd ."') ON DUPLICATE KEY UPDATE `active` = '" . $new_dnd ."'");
	if (! $ok)
		return new GsError( 'Failed to toggle dnd.' );
	else{
		if ( GS_BUTTONDAEMON_USE == true ) 
		{
			
			if( $new_dnd == 'yes' )
				$state = 'on';
			else
				$state = 'off';
			
			gs_dnd_changed_ui( $user_name, $state );
		}
	}
	return $new_dnd;
}


?>