<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
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


// helper function
function _counting_err_handler( $type, $msg, $file, $line ) {
	global $errCnt;
	switch ($type) {
		case E_NOTICE:
		case E_USER_NOTICE:
			break;
		default:
			++$errCnt;
	}
}


/***********************************************************
*    sets call blocking for a user
***********************************************************/

function gs_callblocking_set( $user, $regexp, $pin )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	
	if ((! preg_match( '/^[\d]+$/', $pin )) and (! empty($pin)))
		return new GsError( 'PIN must be numeric.' );
	
	$regexp = trim($regexp);
	# make sure the regexp compiles:
	if ($regexp) {
		$regexp = preg_replace('/[^\d^$?!.:{}()\[\]\\=bBwW]/', '', $regexp);
		error_reporting(E_ALL ^ E_NOTICE);
		$errCnt = 0;
		set_error_handler('_counting_err_handler');
		$errCntBefore = $errCnt;
		preg_match( ''. $regexp .'', 'ignored' );
		$failed = $errCnt > $errCntBefore;
		restore_error_handler();
		if ($failed)
			return new GsError( 'Not a valid RegExp.' );
	}
	
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user_id
	#
	$user_id = $db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	if (! $user_id)
		return new GsError( 'Unknown user.' );
	
	# set outgoing callblocking
	#
	$num = $db->executeGetOne( 'SELECT COUNT(*) FROM `callblocking` WHERE `user_id`='. $user_id.' AND `regexp`=\''. $regexp.'\'' );
	if ($num < 1) {
		$ok = $db->execute( 'INSERT INTO `callblocking` (`user_id`, `regexp`, `pin`) VALUES ('. $user_id .', \''. $db->escape($regexp) .'\', \''. $db->escape($pin) .'\')');
	} else {
		$ok = $db->execute( 'UPDATE `callblocking` SET `pin`=\''. $db->escape($pin) .'\' WHERE `user_id`='. $user_id .' AND `regexp`=\''. $db->escape($regexp) .'\'');
	}
	
	if (! $ok)
		return new GsError( 'Failed to set outgoing call blocking entry.' );
	
	return true;
}


function gs_callblocking_delete( $user, $regexp )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	$regexp = trim($regexp);
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user_id
	#
	$user_id = $db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	if (! $user_id)
		return new GsError( 'Unknown user.' );
	
	# delete outgoing callblocking
	#
	$ok = $db->execute( 'DELETE FROM `callblocking` WHERE `user_id`='. $user_id .' AND `regexp`=\''. $db->escape($regexp) .'\'');
	
	if (! $ok)
		return new GsError( 'Failed to delete outgoing call blocking entry.' );
	
	return true;
}

?>