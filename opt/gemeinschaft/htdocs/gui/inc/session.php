<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1603 $
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

require_once( GS_DIR .'htdocs/gui/inc/pamal/pamal.php' );
require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'inc/ldap.php' );
require_once( GS_DIR .'inc/db_connect.php' );


# set paths
#
//define( 'GS_HTDOCS_DIR', dirName(__FILE__).'/' );
define( 'GS_HTDOCS_DIR', GS_DIR .'htdocs/gui/' );
$GS_URL_PATH = dirName(@$_SERVER['SCRIPT_NAME']);
if (subStr($GS_URL_PATH,-1,1) != '/') $GS_URL_PATH .= '/';
define( 'GS_URL_PATH', $GS_URL_PATH );
unset($GS_URL_PATH);


# some headers
#
header( 'Content-type: text/html; charset=utf-8' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Expires: 0' );
header( 'Vary: *' );


# start or bind to session
#
# start session even if GS_GUI_SESSIONS==false so $_SESSION is
# superglobal
session_name('gemeinschaft');
session_start();

if (isSet($_REQUEST['setlang'])) {
	$setlang = preg_replace('/[^a-z\d_]/i', '', @$_REQUEST['setlang']);
	@$_SESSION['lang'] = $setlang;
}
if (isSet($_SESSION['lang']))
	gs_setlang( $_SESSION['lang'] );
else
	gs_setlang( GS_INTL_LANG );
gs_loadtextdomain( 'gemeinschaft-gui' );
gs_settextdomain( 'gemeinschaft-gui' );


# function to map from LVM-Memo-ID to LVM-Personalnr.
#
function ldap_user_map( $user )
{
	if (! $user) return false;
	
	if (GS_LDAP_PROP_UID == GS_LDAP_PROP_USER) return $user;
	
	$ldap = gs_ldap_connect();
	$u = gs_ldap_get_first( $ldap, GS_LDAP_SEARCHBASE,
		'('. GS_LDAP_PROP_UID .'='. $user .')',
		array(GS_LDAP_PROP_USER) );
	if (isGsError( $u )) {
		//echo $u->$msg;
		echo sprintf(__('Failed to get LDAP user "%s".'), $user), "\n";
		return false;
	}
	if (! is_array( $u )) {
		echo sprintf(__('No user "%s" in LDAP.'), $user), "\n";
		return false;
	}
	$lc_GS_LDAP_PROP_USER = strToLower(GS_LDAP_PROP_USER);
	if (! isSet( $u[$lc_GS_LDAP_PROP_USER]    )) return false;
	if (! isSet( $u[$lc_GS_LDAP_PROP_USER][0] )) return false;
	$ret = $u[$lc_GS_LDAP_PROP_USER][0];
	if (GS_LVM_USER_6_DIGIT_INT) {
		$ret = str_pad($ret, 6, '0', STR_PAD_LEFT);
	}
	return $ret;
}


# connect to db
#
$DB = @gs_db_master_connect();
if (! $DB) die( 'Could not connect to database.' );


# function to check if it's one of our users:
#
function get_user( $user )
{
	global $DB;
	
	$rs = $DB->execute(
'SELECT
	`u`.`id`, `u`.`user`, `u`.`firstname`, `u`.`lastname`, `s`.`name` `ext`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE `u`.`user`=\''. $DB->escape($user) .'\''
);
	if (! $rs)
		die( __('Failed to get user.') );
	if (! ($u = $rs->fetchRow()))
		return false;
	return $u;
}


# authenticate the user
#
$PAM = new PAMAL( 'webseal' );
$_SESSION['real_user']['_origname'] = preg_replace( '/[^a-z0-9_\-]/', '', $PAM->getUser() );

if (! @$_SESSION['real_user']['name']) {
	$_SESSION['real_user']['name'] = @ldap_user_map( $_SESSION['real_user']['_origname'] );
	if (! $_SESSION['real_user']['name'])
		die( sprintf(__('You are not logged in (authentication method: "%s").'), $PAM->getAuthMethod()) );
}

if (! @$_SESSION['real_user']['info'])
	$_SESSION['real_user']['info'] = get_user( $_SESSION['real_user']['name'] );
if (! @$_SESSION['real_user']['info'])
	die( sprintf(__('Unknown user "%s".'), @$_SESSION['real_user']['name']) );

$_SESSION['sudo_user']['name'] = @$_REQUEST['sudo'];
if (! $_SESSION['sudo_user']['name'])
	$_SESSION['sudo_user']['name'] = $_SESSION['real_user']['name'];

if ($_SESSION['sudo_user']['name'] == $_SESSION['real_user']['name']) {
	$_SESSION['sudo_user']['info'] = $_SESSION['real_user']['info'];
} else {
	if (! @$_SESSION['sudo_user']['info']
	  || @$_SESSION['sudo_user']['info']['user'] != $_SESSION['sudo_user']['name'] )
	{
		// info not present (no session support) or sudo user has changed
		$_SESSION['sudo_user']['info'] = get_user( $_SESSION['sudo_user']['name'] );
		if (! @$_SESSION['sudo_user']['info']) {
			echo sprintf(__('Unknown user "%s".'), @$_SESSION['sudo_user']['name']);
			$_SESSION['sudo_user']['name'] = $_SESSION['real_user']['name'];
			$_SESSION['sudo_user']['info'] = $_SESSION['real_user']['info'];
		}
	}
}


# check if user is allowed to sudo as sudo_user
#
$sudo_allowed = false;
if ($_SESSION['sudo_user']['name'] == $_SESSION['real_user']['name']) {
	# allow to edit own account
	//echo "IT'S *YOUR* ACCOUNT";
	$sudo_allowed = true;
} else {
	if (preg_match('/\\b'.($_SESSION['real_user']['name']).'\\b/', GS_GUI_SUDO_ADMINS)) {
		# allow admins to edit any account
		//echo "YOU ARE AN ADMIN";
		$sudo_allowed = true;
	} else {
		if (GS_GUI_SUDO_EXTENDED) {
			include_once( GS_HTDOCS_DIR .'inc/permissions.php' );
			if (function_exists('gui_sudo_allowed')) {
				# allow/disallow depending on gui_sudo_allowed()
				$sudo_allowed = gui_sudo_allowed(
					$_SESSION['real_user']['name'],
					$_SESSION['sudo_user']['name'] );
				//echo "gui_sudo_allowed() returned ", (int)$sudo_allowed;
			}
		}
	}
}
if (! $sudo_allowed) {
	echo sprintf(__('You are not allowed to act as "%s".'), @$_SESSION['sudo_user']['name']);
	$_SESSION['sudo_user']['name'] = $_SESSION['real_user']['name'];
	$_SESSION['sudo_user']['info'] = $_SESSION['real_user']['info'];
}

?>