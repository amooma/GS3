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
* Soeren Sprenger <soeren.sprenger@amooma.de>
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
include_once( GS_DIR .'inc/log.php' );
include_once( GS_DIR .'inc/ldap.php' );


/***********************************************************
*    find a user in LDAP
***********************************************************/

function gs_ldap_user_search( $user )
{
	if (! preg_match('/^[a-z0-9\-_.]+$/', $user))
		return new GsError( 'User must be alphanumeric.' );
	
	$GS_LDAP_HOST = gs_get_conf('GS_LDAP_HOST');
	if (in_array($GS_LDAP_HOST, array(null, false, '', '0.0.0.0'), true)) {
		return new GsError( 'LDAP not configured.' );
	}
	if (! preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $GS_LDAP_HOST)) {
		$tmp = getHostByName($GS_LDAP_HOST);
		if ($tmp == $GS_LDAP_HOST) {
			return new GsError( 'Failed to look up LDAP server.' );
		}
		$GS_LDAP_HOST = $tmp;
	}
	$tmp = @ip2long($GS_LDAP_HOST);
	if (in_array($tmp, array(false, null, -1, 0), true)) {
		return new GsError( 'LDAP not configured (bad IP address).' );
	}
	
	if (!($ldap_conn = gs_ldap_connect(
		$GS_LDAP_HOST
	))) {
		return new GsError( 'Could not connect to LDAP server.' );
	}

	$req_props = array();
	$GS_LDAP_PROP_FIRSTNAME = trim(gs_get_conf('GS_LDAP_PROP_FIRSTNAME'));
	$GS_LDAP_PROP_LASTNAME  = trim(gs_get_conf('GS_LDAP_PROP_LASTNAME'));
	$GS_LDAP_PROP_EMAIL     = trim(gs_get_conf('GS_LDAP_PROP_EMAIL'));
	$GS_LDAP_PROP_PHONE     = trim(gs_get_conf('GS_LDAP_PROP_PHONE'));
	if ($GS_LDAP_PROP_FIRSTNAME != '') $req_props[] = $GS_LDAP_PROP_FIRSTNAME;
	if ($GS_LDAP_PROP_LASTNAME  != '') $req_props[] = $GS_LDAP_PROP_LASTNAME;
	if ($GS_LDAP_PROP_EMAIL     != '') $req_props[] = $GS_LDAP_PROP_EMAIL;
	if ($GS_LDAP_PROP_PHONE     != '') $req_props[] = $GS_LDAP_PROP_PHONE;
	
	$users_arr = gs_ldap_get_list( $ldap_conn, gs_get_conf('GS_LDAP_SEARCHBASE'),
		gs_get_conf('GS_LDAP_PROP_USER') .'='. $user,
		$req_props,
		2
	);
	//print_r($users_arr);
	
	@gs_ldap_disconnect( $ldap_conn );
	if (isGsError($users_arr))
		return $users_arr;
	if (! is_array($users_arr) || count($users_arr) < 1)
		return new GsError( 'User "'.$user.'" not found in LDAP.' );
	if (count($users_arr) > 1)
		return new GsError( 'LDAP search did not return a unique user for "'.$user.'".' );
	$user_arr = $users_arr[0];
	unset($users_arr);
	
	$user_info = array(
		'fn'    => null,
		'ln'    => null,
		'email' => null,
		'exten' => null
	);
	if (array_key_exists($GS_LDAP_PROP_FIRSTNAME, $user_arr)) {
		$user_info['fn'] = @$user_arr[$GS_LDAP_PROP_FIRSTNAME][0];
	}
	if (array_key_exists($GS_LDAP_PROP_LASTNAME, $user_arr)) {
		$user_info['ln'] = @$user_arr[$GS_LDAP_PROP_LASTNAME][0];
	}
	if (array_key_exists($GS_LDAP_PROP_EMAIL, $user_arr)) {
		$user_info['email'] = @$user_arr[$GS_LDAP_PROP_EMAIL][0];
	}
	if (array_key_exists($GS_LDAP_PROP_PHONE, $user_arr)) {
		require_once( GS_DIR .'inc/canonization.php' );
		$phone = @$user_arr[$GS_LDAP_PROP_PHONE][0];
		$phone = preg_replace('/[^0-9+#*]/', '', $phone);
		$cpn = new CanonicalPhoneNumber($phone);
		if ($cpn->in_prv_branch) {
			$user_info['exten'] = $cpn->extn;
		}
		unset($cpn);
	}
	unset($user_arr);
	gs_log( GS_LOG_DEBUG, 'Found user "'.$user.'" ('. trim($user_info['fn'].' '.$user_info['ln']) .') in LDAP' );
	return $user_info;
}

?>