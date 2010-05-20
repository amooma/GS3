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
*    returns the alert time else GsError
***************************************************************/

function get_alert_time_by_target( $target )
{

	## connect to db
	#
	
	$db = gs_db_slave_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );


		
	$rs = $db->execute(
'SELECT
	`wakeup_calls`.`hour`, `wakeup_calls`.`minute`
FROM
	`wakeup_calls`
WHERE
	`wakeup_calls`.`target` = "' . $db->escape( $target ) . '"'
	);
	
	if ( ! $rs )
		return new GsError( 'Error.' );
		
	$alert = false;
	
	if ( ! $r = $rs->fetchRow() )
		return $alert;
	
	$alert = $r;
	
	return $alert;	
		

}

/***************************************************************
*    returns all extensions that have to be notified at
*    the specified time else GsError
***************************************************************/

function get_alert_tagets_by_time( $hour, $minute )
{

	## test input
	#
	
	
	if (! preg_match( '/^[0-9]{1,2}$/', $hour ))
		 return new GsError( 'Hours are not numeric.' );
	
	$hour = (int)$hour;
	
	if ( $hour < 0 || $hour >= 24 )
		return new GsError( 'Hours are out of bounds.' );
	
	if (! preg_match( '/^[0-9]{1,2}$/', $minute ))
		return new GsError( 'Minutes are not numeric.' );
	
	$minute = (int)$minute;
	
	if ( $minute < 0 || $minute >= 60 )
		return new GsError( 'Minutes are out of bounds.' );
	
	## connect to db
	#
	
	$db = gs_db_slave_connect();
	if ( ! $db )
		return new GsError( 'Could not connect to database.' );
	
	$rs = $db->execute(
'SELECT
	`wakeup_calls`.`target`
FROM
	`wakeup_calls`
WHERE
	`hour` = ' . $db->escape( $hour ) . ' AND `minute` =' . $db->escape( $minute )
	);
	
	if ( ! $rs )
		return new GsError( 'Error.' );
		
	$targets = array();
	
	while(  $r = $rs->fetchRow() ) {
	
		$targets[] = $r['target'];
	
	}
	
	return $targets;	

	
	

}


/***************************************************************
*    delete a single wakeupcall by extension
***************************************************************/

function delete_alert_by_target( $target )
{
	
	## connect to db
	#
	
	$db = gs_db_master_connect();
	if ( ! $db )
		return new GsError( 'Could not connect to database.' );
	
	
	$ok = $db->execute( 'DELETE FROM `wakeup_calls`  WHERE `target` ="' . $db->escape($target) . '"' );
	if ( ! $ok )
		return new GsError( 'Failed to remove wakeup-call for extension ' . $target . '.' );
	return true;

}


/***************************************************************
*    delete all wakeupcalls by time
***************************************************************/

function delete_alert_by_time( $hour, $minute )
{
	## test input
	#
	
	
	if (! preg_match( '/^[0-9]{1,2}$/', $hour ))
		 return new GsError( 'Hours are not numeric.' );
	
	$hour = (int)$hour;
	
	if ( $hour < 0 || $hours >= 24 )
		return new GsError( 'Hours are out of bounds.' );
	
	if (! preg_match( '/^[0-9]{1,2}$/', $minute ))
		return new GsError( 'Minutes are not numeric.' );
	
	$minute = (int)$minute;
	
	if ( $minute < 0 || $minute >= 60 )
		return new GsError( 'Minutes are out of bounds.' );
	
	
	## connect to db
	#
	
	$db = gs_db_master_connect();
	if ( ! $db )
		return new GsError( 'Could not connect to database.' );
	
	
	$ok = $db->execute( 'DELETE FROM `wakeup_calls`  WHERE `hour` =' . $db->escape($minute) .
		' AND `minute` =' . $db->escape($minute)  );
	if ( ! $ok )
		return new GsError( 'Failed to remove wakeup-call for extension ' . $target . '.' );
	return true;

}

/***************************************************************
*    set alert time for an extension
***************************************************************/

function set_alert_time_by_target( $target, $hour, $minute )
{

	## test input
	#
	
	if (! preg_match( '/^[0-9]+$/', $target ) )
		return new GsError( 'Taget must be numeric.' );
	
	if (! preg_match( '/^[0-9]{1,2}$/', $hour ))
		 return new GsError( 'Hours are not numeric.' );
	
	$hour = (int)$hour;
	
	if ( $hour < 0 || $hour >= 24 )
		return new GsError( 'Hours are out of bounds.' );
	
	if (! preg_match( '/^[0-9]{1,2}$/', $minute ))
		return new GsError( 'Minutes are not numeric.' );
	
	$minute = (int)$minute;
	
	if ( $minute < 0 || $minute >= 60 )
		return new GsError( 'Minutes are out of bounds.' );
	

	## connect to db
	#
	$db = gs_db_master_connect();
	if ( ! $db )
		return new GsError( 'Could not connect to database.' );
		
	
	## test foe existence
	#
	
	$exists =(int)$db-> executeGetOne(
'SELECT 
	COUNT( `target` )
FROM
	`wakeup_calls`
WHERE
	`target` = "' . $db->escape($target) . '"'
	);
	
	
	if ( $exists == 0 ) {
	
		$query = 'INSERT INTO `wakeup_calls`  ( `target`, `hour`, `minute` ) VALUES ' .
			'("' . $target . '", ' . $hour . ', ' . $minute . ' )';
	
	
		$ok = $db->execute( $query );	
		
		if ( ! $ok )
			return new GsError( 'Database error writing wakeup info' );

		return true;
	
	}
	else if ( $exists == 1 ) {
	
		$query = 'UPDATE `wakeup_calls` SET `hour`=' . $db->escape($hour) . 
			', `minute`=' . $db->escape($minute) . ' WHERE `target`="' .
			$db->escape($target) . '"';
	
	
		$ok = $db->execute( $query );	
		
		if ( ! $ok )
			return new GsError( 'Database error writing wakeup info' );

		return true;
	
	}
	
	return new GsError( 'Database error' );

}
?>