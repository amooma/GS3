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
include_once( GS_DIR .'inc/gs-fns/gs_callforward_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_external_numbers_get.php' );


/***********************************************************
*    set a call forward for a user
***********************************************************/

function gs_callforward_set( $user, $source, $case, $type, $number, $timeout=20 )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	if (! in_array( $source, array('internal','external'), true ))
		return new GsError( 'Source must be internal|external.' );
	if (! in_array( $case, array('always','busy','unavail','offline'), true ))
		return new GsError( 'Case must be always|busy|unavail|offline.' );
	if (! in_array( $type, array('std','var'), true ))
		return new GsError( 'Type must be std|var.' );
	$number = preg_replace( '/[^\d]/', '', $number );
	$timeout = (int)$timeout;
	if ($case != 'unavail') $timeout = 0;
	else {
		if ($timeout > 250) $timeout = 250;
		elseif ($timeout < 1) $timeout = 1;
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
	
	# check if has call forward entry
	#
	$num = $db->executeGetOne( 'SELECT COUNT(*) FROM `callforwards` WHERE `user_id`='. $user_id .' AND `source`=\''. $db->escape($source) .'\' AND `case`=\''. $db->escape($case) .'\'' );
	if ($num < 1)
		$ok = $db->execute( 'INSERT INTO `callforwards` (`user_id`, `source`, `case`, `timeout`, `number_std`, `number_var`, `active`) VALUES ('. $user_id .', \''. $db->escape($source) .'\', \''. $db->escape($case) .'\', 0, \'\', \'\', \'no\')' );
	else
		$ok = true;
	
	# set call forward
	#
	if (gs_get_conf('GS_DP_FORWARD_REQ_EXT_NUM')) {
		if ($number != '' && subStr($number,0,1)==='0' && $number != '0') {
		//FIXME - use the rules for canonization to check if the number
		// is external
			$enumbers = gs_user_external_numbers_get( $user );
			if (isGsError($enumbers))
				return new GsError( $enumbers->getMsg() );
			if (! is_array($enumbers))
				return new GsError( 'Failed to get external numbers.' );
			
			if (! in_array($number, $enumbers))
				return new GsError( 'Number not in external numbers.' );
		}
	}
	
	$field = 'number_'. $type;
	$ok = $ok && $db->execute(
'UPDATE `callforwards` SET
	`'. $field .'`=\''. $db->escape($number) .'\',
	`timeout`='. $timeout .'
WHERE
	`user_id`='. $user_id .' AND
	`source`=\''. $db->escape($source) .'\' AND
	`case`=\''. $db->escape($case) .'\'
LIMIT 1'
	);
	if (! $ok)
		return new GsError( 'Failed to set call forwarding number.' );
	return true;
}



/***********************************************************
*    set a call forward number for a user
***********************************************************/

function gs_callforward_number_set( $user, $source, $type, $number )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	if (! in_array( $source, array('internal','external'), true ))
		return new GsError( 'Source must be internal|external.' );
	if (! in_array( $type, array('std','var'), true ))
		return new GsError( 'Type must be std|var.' );
	$number = preg_replace( '/[^\d]/', '', $number );
	
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
	
	# get user_code
	#
	$user_code = $db->executeGetOne( 'SELECT `user` FROM `users` WHERE `id`='. $user_id );
	
	# get all call forwards
	#
	$cf = @ gs_callforward_get( $user_code );
	if (isGsError( $cf ))
		gs_script_error( 'Could not get call forwards ('. $cf->getMsg() .')' );
	foreach ($cf[$source] as $case => $arr) {
		@ gs_callforward_set( $user_code, $source, $case, $type, $number );
	}
	return true;
}



/***********************************************************
*    set the unavailable timeout for a user
***********************************************************/

function gs_callforward_timeout_set( $user, $timeout=20 )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	$timeout = (int)$timeout;
	if ($timeout > 250) $timeout = 250;
	elseif ($timeout < 1) $timeout = 1;
	
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
	
	# get user_code
	#
	$user_code = $db->executeGetOne( 'SELECT `user` FROM `users` WHERE `id`='. $user_id );
	
	# get all call forwards
	#
	$cf = @ gs_callforward_get( $user_code );
	if (isGsError( $cf )) gs_script_error( 'Could not get call forwards ('. $cf->getMsg() .')' );
	foreach ($cf as $source => $arr1) {
		foreach ($arr1 as $case => $arr) {
			if ($case=='unavail') {
				@ gs_callforward_set( $user_code, $source, $case, 'std', $arr['number_std'], $timeout );
				@ gs_callforward_set( $user_code, $source, $case, 'var', $arr['number_var'], $timeout );
			}
		}
	}
	return true;
}


?>