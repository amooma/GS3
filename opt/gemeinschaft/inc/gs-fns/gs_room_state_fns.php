<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 4818 $
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
*
*  Author: Andreas Neugebauer  <neugebauer@loca.net>
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
include_once( GS_DIR .'inc/gs-lib.php' );


/***************************************************************
*    returns the huntgroup of the user else the user extension
***************************************************************/

function get_room( $ext )
{
	## connect to db
	#
	
	$db = gs_db_slave_connect();
	if ( ! $db )
		return new GsError( 'Could not connect to database.' );

	## check huntgroup
	#
	
	$hg =$db-> executeGetOne(
'SELECT 
	`huntgroups`.`number`
FROM
	`huntgroups`, `ast_sipfriends`
WHERE
	`ast_sipfriends`.`name` = "' . $db->escape($ext) . '" AND `ast_sipfriends`.`_user_id` = `huntgroups`.`user_id`'
	);
	
	if ( $hg )
	{
		return $hg;
	}
	else
	{
		return $ext;
	}
}


/***************************************************************
*    returns the room state else GsError
***************************************************************/

function get_room_state( $extension )
{

	## connect to db
	#
	
	$db = gs_db_slave_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
		
	
	$state = (int)$db->executeGetOne(
'SELECT
	`state`
FROM
	`room_state`
WHERE
	`extension` = "' . $db->escape( $extension ) . '"'
	);
	
	if ( ! $state )
		return new GsError( 'Error. No state for this extension' );
		
	return $state;	
		

}

function get_all_room_states( )
{

	
	## connect to db
	#
	
	$db = gs_db_slave_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
		
	
	$rs = $db->execute(
'SELECT
	`extension`, `state`
FROM
	`room_state`');
	
	if ( ! $rs )
		return new GsError( 'DB-Error.' );

	$result = array();
	
	while ( $res = $rs->fetchRow() ) {
	
		$result[] = $res;	
	
	} 
	
	return $result;

}

/***************************************************************
*    delete a room
***************************************************************/

function delete_room_state( $extension )
{
	
	## connect to db
	#
	
	$db = gs_db_master_connect();
	if ( ! $db )
		return new GsError( 'Could not connect to database.' );
	
	
	$ok = $db->execute( 'DELETE FROM `room_state`  WHERE `extension` ="' . $db->escape($extension) . '"' );
	if ( ! $ok )
		return new GsError( 'Failed to remove room for extension ' . $extension . '.' );
	return true;

}

/***************************************************************
*    set room state
***************************************************************/

function set_room_state( $extension, $state )
{

	## test input
	#
	
	if (! preg_match( '/^[0-9]+$/', $extension ) )
		return new GsError( 'Extension must be numeric.' );
	
	if (! preg_match( '/^[1-3]$/', $state ))
		 return new GsError( 'Hours are not numeric.' );
	
	$state = (int)$state;
	
	if ( $state < 1 || $state > 3 )
		return new GsError( 'State are out of bounds.' );
	

	## connect to db
	#
	$db = gs_db_master_connect();
	if ( ! $db )
		return new GsError( 'Could not connect to database.' );
		
	
	## test foe existence
	#
	
	$exists =(int)$db-> executeGetOne(
'SELECT 
	COUNT( `extension` )
FROM
	`room_state`
WHERE
	`extension` = "' . $db->escape($extension) . '"'
	);
	
	
	if ( $exists == 0 ) {
	
		$query = 'INSERT INTO `room_state`  ( `extension`, `state` ) VALUES ' .
			'("' . $extension . '", ' . $state . ' )';
	
	
		$ok = $db->execute( $query );	
		
		if ( ! $ok )
			return new GsError( 'Database error writing room info' );

		return true;
	
	}
	else if( $exists == 1 ) {
	
		$query = 'UPDATE `room_state` SET `state`=' . $db->escape($state) . 
			' WHERE `extension`="' . $db->escape($extension) . '"';
	
	
		$ok = $db->execute( $query );	
		
		if ( ! $ok )
			return new GsError( 'Database error writing room state' );

		return true;
	
	}
	
	return new GsError( 'Database error' );

}
?>