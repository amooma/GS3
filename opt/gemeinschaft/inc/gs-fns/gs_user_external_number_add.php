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

if (GS_EXTERNAL_NUMBERS_BACKEND === 'ldap') {
	include_once( GS_DIR .'inc/ldap.php' );
}


/***********************************************************
*    adds an external number for a user
***********************************************************/

function gs_user_external_number_add( $user, $number )
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
	
	# add number
	#
	switch (GS_EXTERNAL_NUMBERS_BACKEND) {
		
		case 'ldap':
			$ldap = gs_ldap_connect();
			if (! $ldap)
				return new GsError( 'Could not connect to LDAP server.' );
			
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
			
			$ok = @ldap_mod_add( $ldap, $dn, array(GS_EXTERNAL_NUMBERS_LDAP_PROP => $number) );
			if (! $ok && @ldap_errNo($ldap) != 20) {
				// err #20 is: "Type or value exists"
				return new GsError( 'Failed to add number to LDAP user "'. $dn .'". - '. gs_get_ldap_error($ldap) );
				return false;
			}
			break;
		
		case 'db':
		default:
			$ok = $db->execute( 'REPLACE INTO `users_external_numbers` (`user_id`, `number`) VALUES ('. $user_id .', \''. $db->escape($number) .'\')' );
			if (! $ok)
				return new GsError( 'Failed to add external number.' );
			break;
		
	}
	return true;
}


?>