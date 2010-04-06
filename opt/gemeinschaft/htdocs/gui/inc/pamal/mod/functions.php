<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Sascha Daniels <sd@alternative-solution.de>
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
defined('PAMAL_DIR') or die('No direct access.');

//  Functions to include in new PAMAL classes

// _getUser copy of mod/gemeinschaft.php
function _getUser_gemeinschaft()
{
	$user_entered = strToLower(trim( @$_REQUEST['login_user'] ));
	$pwd_entered  = @$_REQUEST['login_pwd' ]  ;
	
	if ($user_entered=='' || $pwd_entered=='')
		return false;
	
	if ($user_entered === 'sysadmin'
	&&  in_array(gs_get_conf('GS_INSTALLATION_TYPE'), array('gpbx', 'single'), true)
	) {
		require_once( GS_DIR .'inc/keyval.php' );
		$pin = trim(gs_keyval_get('setup_pwd'));
		if ($pin == '') $pin = false;
	}
	else {
		$db = gs_db_slave_connect();
		if (!$db) return false;
		$pin = $db->executeGetOne( 'SELECT `pin` FROM `users` WHERE `user`=\''. $db->escape($user_entered) .'\'' );
	}
	
	return ($pin === $pwd_entered) ? $user_entered : false;
}

// _getUser for LDAP Users
function _getUser_ldap()
{
	$ldaps =  gs_get_conf( 'GS_LDAP_SSL' );
	$ldapproto  = gs_get_conf( 'GS_LDAP_PROTOCOL' );
	$ldapuser   = trim( @$_REQUEST['login_user'] );
	$ldapdn     = gs_get_conf( 'GS_LDAP_PROP_USER' ) . '=' . $ldapuser . ',' . gs_get_conf( 'GS_LDAP_SEARCHBASE' );
	$ldappass   =  @$_REQUEST['login_pwd'];
	$ldaphost = gs_get_conf( 'GS_LDAP_HOST' );
	if ( $ldaps == true ) {
		$ldaphost = 'ldaps://'. $ldaphost;
	} else {
		$ldaphost = 'ldap://'. $ldaphost;
	}
	$ldapconn = @ ldap_connect( $ldaphost );
	@ ldap_set_option( $ldapconn, LDAP_OPT_PROTOCOL_VERSION, (int)$ldapproto );
	if ( !$ldapconn ) {
		gs_log( GS_LOG_WARNING, 'Unable to connect to LDAP server' );
		return false;
	}
	


	if ( $ldapuser== '' || $ldappass== '' )
		return false;

	if ( $ldapconn ) {
		$ldapbind = @ ldap_bind( $ldapconn, $ldapdn, $ldappass );
		if ( $ldapbind ) {
			gs_log( GS_LOG_DEBUG, 'User ' . $ldapdn . ' found!' );
			return $ldapuser;
		}
		else
		{
			gs_log( GS_LOG_DEBUG, 'Unable to bind to LDAP server as ' . $ldapdn . ', ' . ldap_error($ldapconn) );
			$user_not_found=1;
			return false;
		}
	}
}

// Helperfunction for mod/ldap.php
// First check if we have an LDAP USER
// Return true, if USER was found in LDAP 
function _searchUser_ldap()
{
	$ldaps =  gs_get_conf( 'GS_LDAP_SSL' );
	$ldapproto  = gs_get_conf( 'GS_LDAP_PROTOCOL' );
	$ldapuser   = trim( @$_REQUEST['login_user'] );
	$ldapbasedn     =  gs_get_conf( 'GS_LDAP_SEARCHBASE' );
	$ldaphost = gs_get_conf( 'GS_LDAP_HOST' );
	$ldapfilter = gs_get_conf( 'GS_LDAP_PROP_USER' ) . '=' . $ldapuser;
	if ( $ldaps == true ) {
		$ldaphost = 'ldaps://'. $ldaphost;
	} 
	else {
		$ldaphost = 'ldap://'. $ldaphost;
	}
	$ldapconn =  @ ldap_connect( $ldaphost );
	gs_log( GS_LOG_WARNING, "$ldapconn $ldaphost");
	@ ldap_set_option( $ldapconn, LDAP_OPT_PROTOCOL_VERSION, (int)$ldapproto );
	$ldaptestconn  = @ ldap_bind($ldapconn);
	if ( !$ldaptestconn ) {
		gs_log( GS_LOG_WARNING, 'Unable to connect to LDAP server' );
		return false;
	} 
	$ldap_find_user=ldap_search($ldapconn, $ldapbasedn, $ldapfilter );
	$ldap_result = ldap_get_entries($ldapconn, $ldap_find_user);
	if ( $ldap_result["count"] == 0 )
	{
		return false;
	}
	else {
		return true;
	}
}
?>