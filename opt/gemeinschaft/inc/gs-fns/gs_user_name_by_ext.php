<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
*
* Andreas Neugebauer <neugebauer@loca.net> Locanet oHG
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
*    returns the users username
***********************************************************/

function gs_user_name_by_ext( $ext )
{
	if (! preg_match( '/^[\d]+$/', $ext ))
		return new GsError( 'Extension must be numeric.' );
	
	# connect to db
	#
	$db = gs_db_slave_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user_id
	#
	$user_name = $db->executeGetOne( 'SELECT `user` FROM `users` `u`, `ast_sipfriends` `a` WHERE `a`.`name`=\''. $db->escape( $ext ) .'\' AND `a`.`_user_id` = `u`.`id`' );
	if (! $user_name)
		return new GsError( 'Unknown user for extension ' . $ext . '.' );
	
	return $user_name;
}


?>