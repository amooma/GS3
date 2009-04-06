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

require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/log.php' );
require_once( GS_DIR .'inc/gs-lib.php' );
if (gs_get_conf('GS_GUI_PERMISSIONS_METHOD') === 'lvm') {
	require_once( GS_DIR .'inc/ldap.php' );
}
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
@header( 'Content-type: text/html; charset=utf-8' );
@header( 'Pragma: no-cache' );
@header( 'Cache-Control: private, no-cache, must-revalidate' );
@header( 'Expires: 0' );
@header( 'Vary: *' );


# start or bind to session
# (start session even if GS_GUI_SESSIONS==false so $_SESSION is
# superglobal)
ini_set('session.referer_check', '');
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 0);
ini_set('session.use_trans_sid', 0);
ini_set('session.hash_function', 1);
ini_set('session.hash_bits_per_character', 5);
ini_set('session.entropy_length', 0);
if (version_compare(phpVersion(), '5.2.0', '>='))
	session_set_cookie_params( 0, GS_URL_PATH, '', false, false );
else
	session_set_cookie_params( 0, GS_URL_PATH, '', false );
session_name('gemeinschaft');
$_REQUEST['gemeinschaft'] = preg_replace('[^0-9a-zA-Z\-,]', '', @$_REQUEST['gemeinschaft']);
$_COOKIE['gemeinschaft']  = preg_replace('[^0-9a-zA-Z\-,]', '', @$_COOKIE['gemeinschaft']);
if ($_REQUEST['gemeinschaft'] == '') unset($_REQUEST['gemeinschaft']);
if ($_COOKIE['gemeinschaft']  == '') unset($_COOKIE['gemeinschaft']);
session_start();


function parse_http_accept_header( $data )
{
	$accept = array();
	$parts = explode(',', $data);
	foreach ($parts as $part) {
		$val_q = explode(';', $part);
		$q = 10;
		if (array_key_exists(1, $val_q)) {
			$val_q[1] = trim($val_q[1]);
			if (subStr($val_q[1], 0, 2) === 'q=') {
				$q = (int)((float)subStr($val_q[1], 2)*10);
				if     ($q <  0) $q = 0;
				elseif ($q > 10) $q = 10;
			}
		}
		$accept[trim(@$val_q[0])] = $q;
	}
	aRSort($accept);
	return $accept;
}


# set language
#
if (array_key_exists('setlang', $_REQUEST)) {
	@$_SESSION['lang'] = gs_lang_name_locale( @$_REQUEST['setlang'] );
}
else {
	if (! array_key_exists('lang', $_SESSION)) {
		# try to use the best language according to the Accept-Language header
		if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
			$accept_langs = parse_http_accept_header( $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
			foreach ($accept_langs as $accept_lang => $qval) {
				if ($qval > 0) {
					//echo "trying to set $accept_lang ... ";
					$ret = gs_setlang( gs_lang_name_locale( $accept_lang ));
					//FIXME - works for "de-de" but not for "de" - fix gs_setlang()?
					if ($ret) {
						$_SESSION['lang'] = $ret;
						//echo " worked ($ret) ";
						break;
					}
				}
			}
			unset($accept_langs, $accept_lang, $qval, $ret);
		}
	}
}
if (! array_key_exists('lang', $_SESSION)) {
	$_SESSION['lang'] = GS_INTL_LANG;
}
$ret = gs_setlang( $_SESSION['lang'] );
if ($ret) $_SESSION['lang'] = $ret;
$_SESSION['isolang'] = gs_lang_name_internal( $_SESSION['lang'] );
gs_loadtextdomain( 'gemeinschaft-gui' );
gs_settextdomain( 'gemeinschaft-gui' );



# functions to map from some legacy user name to the current one
#

//FIXME - this custom function should probably somehow be moved to
// /etc/gemeinschaft/custom-functions.php or
// /etc/gemeinschaft/custom-functions.examples.php
function _gs_legacy_user_map_lvm( $user )
{
	if (! $user) return false;
	
	if (GS_LDAP_PROP_UID === GS_LDAP_PROP_USER) return $user;
	
	$ldap = gs_ldap_connect();
	$u = gs_ldap_get_first( $ldap, GS_LDAP_SEARCHBASE,
		'('. GS_LDAP_PROP_UID .'='. $user .')',
		array(GS_LDAP_PROP_USER) );
	if (isGsError($u)) {
		//echo $u->$msg;
		echo sPrintF(__('Failed to get user "%s" from LDAP server.'), $user), "\n";
		return false;
	}
	if (! is_array($u)) {
		echo sPrintF(__('User "%s" not found in LDAP database.'), $user), "\n";
		return false;
	}
	$lc_GS_LDAP_PROP_USER = strToLower(GS_LDAP_PROP_USER);
	if (! isSet( $u[$lc_GS_LDAP_PROP_USER]    )) return false;
	if (! isSet( $u[$lc_GS_LDAP_PROP_USER][0] )) return false;
	$ret = $u[$lc_GS_LDAP_PROP_USER][0];
	//if (gs_get_conf('GS_LVM_USER_6_DIGIT_INT')) {
	// this check is not really needed as this is a custom function anyway
		$ret = str_pad($ret, 6, '0', STR_PAD_LEFT);
	//}
	return $ret;
}

function gs_legacy_user_map( $user )
{
	if (gs_get_conf('GS_GUI_USER_MAP_METHOD') === 'lvm') {
		return _gs_legacy_user_map_lvm( $user );
	} else {
		if (! $user) return false;
		return $user;
	}
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
	`u`.`id`, `u`.`user`, `u`.`firstname`, `u`.`lastname`, `s`.`name` `ext`,
	`u`.`host_id`, `h`.`is_foreign` `host_is_foreign`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) LEFT JOIN
	`hosts` `h` ON (`h`.`id`=`u`.`host_id`)
WHERE `u`.`user`=\''. $DB->escape($user) .'\''
);
	if (! $rs)
		die( __('Failed to get user.') );
	if (! ($u = $rs->fetchRow()))
		return false;
	return $u;
}


# define indices
#

if (! array_key_exists('real_user', $_SESSION)) {
	$_SESSION['real_user'] = array();
}
if (! array_key_exists('sudo_user', $_SESSION)) {
	$_SESSION['sudo_user'] = array();
}
if (! array_key_exists('boi_host_id', $_SESSION['sudo_user'])) {
	$_SESSION['sudo_user']['boi_host_id'] = null;
}
if (! array_key_exists('boi_host', $_SESSION['sudo_user'])) {
	$_SESSION['sudo_user']['boi_host'] = null;
}
if (! array_key_exists('boi_role', $_SESSION['sudo_user'])) {
	$_SESSION['sudo_user']['boi_role'] = null;
}



# authenticate the user
#
$login_info   = '';
$login_errmsg = '';
$is_login     = false;

if (! @$_SESSION['login_ok'] && ! @$_SESSION['login_user']) {
	
	$_SESSION['sudo_user']['boi_session'] = null;
	
	require_once( GS_DIR .'htdocs/gui/inc/pamal/pamal.php' );
	
	$PAM = new PAMAL( GS_GUI_AUTH_METHOD );
	$user = $PAM->getUser();
	if (! $user) {
		$_SESSION['login_ok'  ] = false;
		$_SESSION['login_user'] = false;
		$login_info = sPrintF(__('You are not logged in (authentication method: "%s").'), $PAM->getAuthMethod());
		$login_errmsg = __('Benutzer oder Pa&szlig;wort ung&uuml;ltig');
		return;
	}
	$_SESSION['real_user']['_origname'] = preg_replace( '/[^a-z0-9_\-.]/', '', $user );
	
	//if (! @$_SESSION['real_user']['name']) {
		$_SESSION['real_user']['name'] = @gs_legacy_user_map( $_SESSION['real_user']['_origname'] );
	//}
	if (! $_SESSION['real_user']['name']) {
		//die( sPrintF(__('You are not logged in (authentication method: "%s").'), $PAM->getAuthMethod()) );
		$_SESSION['login_ok'  ] = false;
		$_SESSION['login_user'] = false;
		$login_info = sPrintF(__('You are not logged in (authentication method: "%s").'), $PAM->getAuthMethod());
		return;
	}
	
	if (! @$_SESSION['real_user']['info']) {
		if ($_SESSION['real_user']['name'] !== 'sysadmin') {
			$_SESSION['real_user']['info'] = get_user( $_SESSION['real_user']['name'] );
		} else {
			$_SESSION['real_user']['info'] = array(
				'id'              => -1,
				'user'            => $_SESSION['real_user']['name'],
				'firstname'       => '',
				'lastname'        => $_SESSION['real_user']['name'],
				'ext'             => '',
				'host_id'         => 0,
				'host_is_foreign' => 0
			);
		}
	}
	if (! @$_SESSION['real_user']['info']) {
		echo sPrintF(__('Unknown user "%s".'), @$_SESSION['real_user']['name']), "\n";
		exit(1);
	}
	
	$_SESSION['login_ok'  ] = true;
	$_SESSION['login_user'] = $user;
	$_SESSION['sudo_user']['boi_session'] = null;
	$is_login = true;
	gs_log( GS_LOG_DEBUG, 'User '.$user.' logged in (HTTP)' );
}

$_SESSION['sudo_user']['name'] = @$_REQUEST['sudo'];
if (! $_SESSION['sudo_user']['name'])
	$_SESSION['sudo_user']['name'] = $_SESSION['real_user']['name'];

if ($_SESSION['sudo_user']['name'] == $_SESSION['real_user']['name']) {
	$_SESSION['sudo_user']['info'] = $_SESSION['real_user']['info'];
} else {
	if (! @$_SESSION['sudo_user']['info']
	||  @$_SESSION['sudo_user']['info']['user'] != $_SESSION['sudo_user']['name'] )
	{
		// info not present (no session support) or sudo user has changed
		$_SESSION['sudo_user']['info'] = get_user( $_SESSION['sudo_user']['name'] );
		if (! @$_SESSION['sudo_user']['info']) {
			echo sPrintF(__('Unknown user "%s".'), @$_SESSION['sudo_user']['name']), "\n";
			$_SESSION['sudo_user']['name'] = $_SESSION['real_user']['name'];
			$_SESSION['sudo_user']['info'] = $_SESSION['real_user']['info'];
		}
		$_SESSION['sudo_user']['boi_session'] = null;
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
	if ($_SESSION['real_user']['name'] === 'sysadmin') {
		# allow sysadmin to edit any account
		//echo "YOU ARE A SYSADMIN";
		$sudo_allowed = true;
	} elseif (preg_match('/\\b'.($_SESSION['real_user']['name']).'\\b/', GS_GUI_SUDO_ADMINS)) {
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
	echo sPrintF(__('You are not allowed to act as "%s".'), @$_SESSION['sudo_user']['name']);
	$_SESSION['sudo_user']['name'] = $_SESSION['real_user']['name'];
	$_SESSION['sudo_user']['info'] = $_SESSION['real_user']['info'];
	$_SESSION['sudo_user']['boi_session'] = null;
}



# BOI
#

if (! array_key_exists('boi_host_id', $_SESSION['sudo_user'])
||  $_SESSION['sudo_user']['boi_host_id'] === null
||  $is_login)
{
	$_SESSION['sudo_user']['boi_host_id'] =
		! $_SESSION['real_user']['info']['host_is_foreign']
		? 0
		: $_SESSION['real_user']['info']['host_id'];
	$_SESSION['sudo_user']['boi_session'] = null;
}
if (! array_key_exists('boi_role', $_SESSION['sudo_user'])
||  $_SESSION['sudo_user']['boi_role'] === null
||  $is_login)
{
	$_SESSION['sudo_user']['boi_role'] =
		! $_SESSION['real_user']['info']['host_is_foreign']
		? 'gs'
		: 'boi-user';
	$_SESSION['sudo_user']['boi_session'] = null;
}

if (gs_get_conf('GS_BOI_ENABLED')) {
	
	//echo "<pre>"; print_r($_SESSION); echo "</pre>";
	
	// set default values for user ...
	
	if ($_SESSION['real_user']['name'] === 'sysadmin') {
		
		
		
		
		
	}

}



$get_host = false;
if (gs_get_conf('GS_BOI_ENABLED') && $is_login) {
	$get_host = true;
}
if (array_key_exists('boi_host_id', $_REQUEST)) {
	$_REQUEST['boi_host_id'] = (int)$_REQUEST['boi_host_id'];
	
	//FIXME check ...
	
	if ($_REQUEST['boi_host_id'] !== $_SESSION['sudo_user']['boi_host_id']) {
		$_SESSION['sudo_user']['boi_session'] = null;
		
		$_SESSION['sudo_user']['boi_host_id'] = $_REQUEST['boi_host_id'];
		if ($_REQUEST['boi_host_id'] < 1) {
			$_SESSION['sudo_user']['boi_host'] = null;
		} else {
			$get_host = true;
		}
	}
}
if ($get_host) {
	if ($_SESSION['sudo_user']['boi_host_id'] < 1) {
		$_SESSION['sudo_user']['boi_host'] = null;
	} else {
		$db = gs_db_slave_connect();
		if (!$db) {
			echo 'Could not connect to DB.';
		} else {
			$_SESSION['sudo_user']['boi_host'] =
				$db->executeGetOne( 'SELECT `host` FROM `hosts` WHERE `id`='. (int)$_SESSION['sudo_user']['boi_host_id'] );
			if (! $_SESSION['sudo_user']['boi_host']) {
				echo 'Failed to get host.';
			}
		}
	}
}

if (array_key_exists('boi_role', $_REQUEST)) {
	$_REQUEST['boi_role'] = $_REQUEST['boi_role'];
	
	//FIXME check ...
	
	if ($_REQUEST['boi_role'] !== $_SESSION['sudo_user']['boi_role']) {
		$_SESSION['sudo_user']['boi_session'] = null;
		
		$_SESSION['sudo_user']['boi_role'] = $_REQUEST['boi_role'];
	}
}




if (! array_key_exists('boi_session', $_SESSION['sudo_user'])) {
	$_SESSION['sudo_user']['boi_session'] = null;
}



?>
