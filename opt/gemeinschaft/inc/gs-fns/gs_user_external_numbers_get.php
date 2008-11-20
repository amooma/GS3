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
*    gets external numbers for a user
***********************************************************/

function gs_user_external_numbers_get( $user )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	
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
	
	# get external numbers
	#
	switch (GS_EXTERNAL_NUMBERS_BACKEND) {
		
		case 'ldap':
			//ldapsearch -x -D 'cn=root,dc=example,dc=com' -w secret -b 'ou=People,dc=example,dc=com' '(uid=demo2)' telephoneNumber
			
			$ldap = gs_ldap_connect();
			if (! $ldap)
				return new GsError( 'Could not connect to LDAP server.' );
			
			$ldap_user = $user;
			if (gs_get_conf('GS_LVM_USER_6_DIGIT_INT')) {
				$ldap_user = preg_replace('/^0+/', '', $ldap_user);
				# if the usernames in your LDAP are integers without a
				# leading "0"
			}
			$userArr = gs_ldap_get_first( $ldap, GS_LDAP_SEARCHBASE,
				GS_LDAP_PROP_USER .'='. $ldap_user,
				array(GS_EXTERNAL_NUMBERS_LDAP_PROP)
			);
			if (isGsError($userArr))
				return new GsError( $userArr->getMsg() );
			if (! is_array($userArr)) {
				//return new GsError( 'User "'. GS_LDAP_PROP_USER .'='. $user .','. GS_LDAP_SEARCHBASE .'" not in LDAP.' );
				$numbers = array();
			} else {
				foreach ($userArr as $key => $arr) {
					if (strCaseCmp( $key, GS_EXTERNAL_NUMBERS_LDAP_PROP )==0) {
						$numbers = $arr;
						sort( $numbers );
						break;
					}
				}
			}
			gs_ldap_disconnect( $ldap );
			break;
		
		case 'db':
		default:
			$rs = $db->execute( 'SELECT `number` FROM `users_external_numbers` WHERE `user_id`='. $user_id .' ORDER BY `number`' );
			if (! $rs)
				return new GsError( 'Failed to get external numbers.' );
			
			$numbers = array();
			while ($r = $rs->fetchRow())
				$numbers[] = $r['number'];
			break;
		
	}
	
	return $numbers;
}


?>