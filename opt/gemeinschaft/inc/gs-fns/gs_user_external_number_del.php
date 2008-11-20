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
include_once( GS_DIR .'inc/gs-fns/gs_user_external_numbers_get.php' );

if (GS_EXTERNAL_NUMBERS_BACKEND === 'ldap') {
	include_once( GS_DIR .'inc/ldap.php' );
}


/***********************************************************
*    deletes an external number for a user
***********************************************************/

function gs_user_external_number_del( $user, $number )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	if (! preg_match( '/^[\d]+$/', $number ))
		return new GsError( 'Number must be numeric.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user_id
	#
	$user_id = $db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	if ($user_id < 1)
		return new GsError( 'Unknown user.' );
	
	
	switch (GS_EXTERNAL_NUMBERS_BACKEND) {
		
		case 'ldap':
			$ldap = gs_ldap_connect();
			if (! $ldap)
				return new GsError( 'Could not connect to LDAP server.' );
			
			# check if number exists (to return proper err msg)
			#
			/*
			$numbers = gs_user_external_numbers_get( $user );
			if (isGsError($numbers))
				return new GsError( $numbers->getMsg() );
			if (! is_array($numbers))
				return new GsError( 'Failed to get numbers from LDAP' );
			if (! in_array($number, $numbers, true))
				return new GsError( 'No such number.' );
			*/
			
			# find ldap user name
			#
			if (GS_LDAP_PROP_UID === GS_LDAP_PROP_USER) {
				$ldap_uid = $user;
				if (gs_get_conf('GS_LVM_USER_6_DIGIT_INT')) {
					$user = preg_replace('/^0+/', '', $user);
					# if the usernames in your LDAP are integers without
					# a leading "0"
				}
			} else {
				if (gs_get_conf('GS_LVM_USER_6_DIGIT_INT')) {
					$user = preg_replace('/^0+/', '', $user);
					# if the usernames in your LDAP are integers without
					# a leading "0"
				}
				$userArr = gs_ldap_get_first( $ldap, GS_LDAP_SEARCHBASE, GS_LDAP_PROP_USER .'='. $user, array(GS_LDAP_PROP_UID) );
				if (isGsError($userArr))
					return new GsError( $userArr->getMsg() );
				if (! is_array($userArr))
					return new GsError( 'Could not find user by "'. GS_LDAP_PROP_USER .'='. $user .'" in search base "'. GS_LDAP_SEARCHBASE .'" in LDAP.' );
				$ldap_uid = @$userArr[strToLower(GS_LDAP_PROP_UID)][0];
				if (strLen($ldap_uid) < 1)
					return new GsError( 'Could not find user by "'. GS_LDAP_PROP_USER .'='. $user .'" in search base "'. GS_LDAP_SEARCHBASE .'" in LDAP.' );
			}
			$dn = GS_LDAP_PROP_UID .'='. $ldap_uid .','. GS_LDAP_SEARCHBASE;
			
			# delete number
			#
			$ok = @ ldap_mod_del( $ldap, $dn, array(GS_EXTERNAL_NUMBERS_LDAP_PROP => $number) );
			if (! $ok) {
				if (@ldap_errNo($ldap) == 16) {
					// err #16 is: "No such attribute"
					return new GsError( 'No such number.' );
				}
				return new GsError( 'Failed to delete number for LDAP user "'. $dn .'". - '. gs_get_ldap_error($ldap) );
			}
			break;
		
		case 'db':
		default:
			# check if number exists (to return proper err msg)
			#
			$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `users_external_numbers` WHERE `user_id`='. $user_id .' AND `number`=\''. $db->escape($number) .'\'' );
			if ($num < 1)
				return new GsError( 'No such number.' );
			
			# delete number
			#
			$ok = $db->execute( 'DELETE FROM `users_external_numbers` WHERE `user_id`='. $user_id .' AND `number`=\''. $db->escape($number) .'\'' );
			if (! $ok)
				return new GsError( 'Failed to delete external number.' );
			break;
		
	}
	return true;
}


?>