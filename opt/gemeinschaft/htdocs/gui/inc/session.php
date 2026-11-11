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
@header( 'X-Frame-Options: SAMEORIGIN' );
@header( 'X-Content-Type-Options: nosniff' );
@header( 'Referrer-Policy: same-origin' );


# start or bind to session
# (start session even if GS_GUI_SESSIONS==false so $_SESSION is
# superglobal)
ini_set('session.referer_check', '');
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);  # never accept a session ID from the URL
ini_set('session.use_strict_mode', 1);   # never adopt a session ID we did not issue
ini_set('session.use_trans_sid', 0);
ini_set('session.hash_function', 1);
ini_set('session.hash_bits_per_character', 5);
ini_set('session.entropy_length', 0);
session_set_cookie_params( array(
	'lifetime' => 0,
	'path'     => GS_URL_PATH,
	'domain'   => '',
	'secure'   => (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] != '' && strToLower($_SERVER['HTTPS']) !== 'off'),
	'httponly' => true,
	'samesite' => 'Lax'
) );
session_name('gemeinschaft');
unset($_REQUEST['gemeinschaft']);
if (array_key_exists('gemeinschaft', $_COOKIE)
&& (! is_string($_COOKIE['gemeinschaft'])
||  ! preg_match('/^[0-9a-zA-Z\-,]+$/', $_COOKIE['gemeinschaft']))) {
	unset($_COOKIE['gemeinschaft']);
}
session_start();



function gs_user_is_admin( $username )
{
	static $admins = null;
	if ($admins === null)
		$admins = gs_get_conf('GS_GUI_SUDO_ADMINS');
	# compare whole names. a word match would make "lisa" an admin
	# as soon as "lisa.meier" is one
	return ($username != ''
		&& in_array((string)$username, preg_split('/[\s,;]+/', (string)$admins, -1, PREG_SPLIT_NO_EMPTY), true));
}


# Slow down guessing of the (numeric) PINs: count failed logins per
# client address.
#
define( 'GS_LOGIN_THROTTLE_MAX'   ,  20 );  # failed attempts ...
define( 'GS_LOGIN_THROTTLE_WINDOW', 600 );  # ... in so many seconds

function _gs_login_throttle_file()
{
	$dir = rTrim(sys_get_temp_dir(), '/') .'/gs-login-throttle';
	if (! @is_dir($dir)) @mkdir($dir, 0700);
	if (! @is_dir($dir) || @is_link($dir)) return false;
	return $dir .'/'. sha1((string)@$_SERVER['REMOTE_ADDR']);
}

function _gs_login_throttle_read( $fh )
{
	$now = time();
	$times = array();
	foreach (explode("\n", (string)@stream_get_contents($fh)) as $t) {
		$t = (int)$t;
		if ($t > $now - GS_LOGIN_THROTTLE_WINDOW && $t <= $now) $times[] = $t;
	}
	return $times;
}

function gs_login_throttled()
{
	$file = _gs_login_throttle_file();
	if (! $file || ! @file_exists($file)) return false;
	$fh = @fOpen($file, 'rb');
	if (! $fh) return false;
	@flock($fh, LOCK_SH);
	$times = _gs_login_throttle_read($fh);
	@flock($fh, LOCK_UN);
	@fClose($fh);
	return (count($times) >= GS_LOGIN_THROTTLE_MAX);
}

function gs_login_throttle_count_failure()
{
	$file = _gs_login_throttle_file();
	if (! $file) return;
	$fh = @fOpen($file, 'cb+');
	if (! $fh) return;
	@flock($fh, LOCK_EX);
	$times = _gs_login_throttle_read($fh);
	$times[] = time();
	@fTruncate($fh, 0);
	@rewind($fh);
	@fWrite($fh, implode("\n", $times));
	@flock($fh, LOCK_UN);
	@fClose($fh);
	if (mt_rand(1,50) === 1) {
		# forget addresses which have been quiet
		foreach ((array)@glob(dirName($file).'/*') as $f) {
			if (@fileMTime($f) < time() - GS_LOGIN_THROTTLE_WINDOW) @unlink($f);
		}
	}
}

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


# Refuse requests which another site made the browser send (CSRF).
# The session cookie is "SameSite=Lax", so the only cross-site request
# still carrying it is a link (top-level GET). Plain navigation
# (section, module, user, language) stays possible so that links to
# the GUI keep working; a link carrying more than that has to be
# confirmed by the user.
#
function gs_request_origin()
{
	if (array_key_exists('HTTP_SEC_FETCH_SITE', $_SERVER)) {
		# "none": typed or bookmarked by the user
		return in_array(strToLower(trim($_SERVER['HTTP_SEC_FETCH_SITE'])),
			array('same-origin','none'), true) ? 'same' : 'cross';
	}
	# browsers send no Fetch Metadata over plain HTTP
	$src = '';
	if     (array_key_exists('HTTP_ORIGIN' , $_SERVER)) $src = $_SERVER['HTTP_ORIGIN'];
	elseif (array_key_exists('HTTP_REFERER', $_SERVER)) $src = $_SERVER['HTTP_REFERER'];
	if (! is_string($src) || $src == '') return 'unknown';
	$src_host = strToLower((string)@parse_url($src, PHP_URL_HOST));
	$our_host = strToLower(preg_replace('/:\d+$/', '', (string)@$_SERVER['HTTP_HOST']));
	return ($src_host !== '' && $src_host === $our_host) ? 'same' : 'cross';
}

function gs_csrf_refuse()
{
	gs_log( GS_LOG_NOTICE, 'Refused cross-site request from '. @$_SERVER['REMOTE_ADDR'] );
	@header( 'HTTP/1.0 403 Forbidden', true, 403 );
	@header( 'Status: 403 Forbidden' , true, 403 );
	die( 'Cross-site request refused.' );
}

$gs_request_origin = gs_request_origin();
if ($gs_request_origin !== 'same') {
	if (! in_array(strToUpper((string)@$_SERVER['REQUEST_METHOD']), array('GET','HEAD'), true)) {
		# a browser names the origin of every cross-site POST. without
		# one it is not a browser (and has no cookie of the user)
		if ($gs_request_origin === 'cross') gs_csrf_refuse();
	}
	elseif (baseName((string)@$_SERVER['SCRIPT_FILENAME']) !== 'index.php') {
		# the helper scripts (srv/...) are not linked to from elsewhere
		if (count($_GET) > 0) gs_csrf_refuse();
	}
	else {
		$gs_csrf_confirm = false;
		foreach ($_GET as $gs_csrf_k => $gs_csrf_v) {
			if (! in_array((string)$gs_csrf_k, array('s','m','sudo','setlang','_gs_ok'), true)) {
				$gs_csrf_confirm = true;
				break;
			}
		}
		if ($gs_csrf_confirm
		&&  is_string(@$_GET['_gs_ok'])
		&&  is_string(@$_SESSION['csrf_confirm_token'])
		&&  hash_equals($_SESSION['csrf_confirm_token'], $_GET['_gs_ok'])) {
			# the user confirmed it
			$gs_csrf_confirm = false;
		}
		if ($gs_csrf_confirm) {
			if (! is_string(@$_SESSION['csrf_confirm_token'])) {
				$_SESSION['csrf_confirm_token'] = bin2hex(random_bytes(16));
			}
			$gs_csrf_url = preg_replace('/([?&])_gs_ok=[^&]*&?/', '$1', (string)@$_SERVER['REQUEST_URI']);
			$gs_csrf_url.= (strPos($gs_csrf_url, '?') === false ? '?' : '&') .'_gs_ok='. $_SESSION['csrf_confirm_token'];
			gs_log( GS_LOG_NOTICE, 'Asking for confirmation of a request from outside the GUI ('. @$_SERVER['REMOTE_ADDR'] .')' );
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' ,"\n";
			echo '<html xmlns="http://www.w3.org/1999/xhtml">' ,"\n";
			echo '<head>' ,"\n";
			echo '<title>Gemeinschaft</title>' ,"\n";
			echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' ,"\n";
			echo '</head>' ,"\n";
			echo '<body>' ,"\n";
			echo '<br /><p align="center">', htmlSpecialChars(__("Dieser Aufruf kam nicht aus der Gemeinschaft-Oberfl\xC3\xA4che. Wenn Sie ihn nicht selbst veranla\xC3\x9Ft haben, schlie\xC3\x9Fen Sie diese Seite."), ENT_QUOTES) ,'</p>' ,"\n";
			echo '<p align="center"><tt>', htmlSpecialChars((string)@$_SERVER['REQUEST_URI'], ENT_QUOTES) ,'</tt></p>' ,"\n";
			echo '<p align="center"><a href="', htmlSpecialChars($gs_csrf_url, ENT_QUOTES) ,'">', htmlSpecialChars(__('Trotzdem fortfahren'), ENT_QUOTES) ,'</a></p>' ,"\n";
			echo '</body>' ,"\n";
			echo '</html>';
			exit;
		}
		unset($gs_csrf_confirm, $gs_csrf_k, $gs_csrf_v);
	}
}
unset($gs_request_origin, $_GET['_gs_ok'], $_REQUEST['_gs_ok']);




# functions to map from some legacy user name to the current one
#

//FIXME - this custom function should probably somehow be moved to
// /etc/gemeinschaft/custom-functions.php or
// /etc/gemeinschaft/custom-functions.examples.php
function _gs_legacy_user_map_lvm( $user )
{
	global $dispatcher_errors_html;
	
	if (! $user) return false;
	
	if (GS_LDAP_PROP_UID === GS_LDAP_PROP_USER) return $user;
	
	$ldap = gs_ldap_connect();
	$u = gs_ldap_get_first( $ldap, GS_LDAP_SEARCHBASE,
		'('. GS_LDAP_PROP_UID .'='. $user .')',
		array(GS_LDAP_PROP_USER) );
	if (isGsError($u)) {
		//echo $u->$msg;
		$dispatcher_errors_html[] = sPrintF(htmlEnt(__('Failed to get user "%s" from LDAP server.')), $user);
		return false;
	}
	if (! is_array($u)) {
		$dispatcher_errors_html[] = sPrintF(htmlEnt(__('User "%s" not found in LDAP database.')), $user);
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
	
	$login_attempted =
		(  is_string(@$_REQUEST['login_user']) && $_REQUEST['login_user'] != ''
		&& is_string(@$_REQUEST['login_pwd' ]) && $_REQUEST['login_pwd' ] != '');
	if ($login_attempted && gs_login_throttled()) {
		gs_log( GS_LOG_NOTICE, 'Too many failed logins from '. @$_SERVER['REMOTE_ADDR'] .', refusing' );
		$_SESSION['login_ok'  ] = false;
		$_SESSION['login_user'] = false;
		$login_info = __('Zu viele Fehlversuche. Bitte versuchen Sie es in einigen Minuten erneut.');
		$login_errmsg = $login_info;
		return;
	}
	
	$PAM = new PAMAL( GS_GUI_AUTH_METHOD );
	$user = $PAM->getUser();
	if (! $user) {
		if ($login_attempted) gs_login_throttle_count_failure();
		$_SESSION['login_ok'  ] = false;
		$_SESSION['login_user'] = false;
		$login_info = sPrintF(__('You are not logged in (authentication method: "%s").'), $PAM->getAuthMethod());
		$login_errmsg = __('Benutzer oder Pa&szlig;wort ung&uuml;ltig');
		return;
	}
	$_SESSION['real_user']['_origname'] = preg_replace( '/[^A-Za-z0-9_\-.]/', '', $user );
	
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
		$dispatcher_errors_html[] = sPrintF(htmlEnt(__('Unknown user "%s".')), @$_SESSION['real_user']['name']);
		return;
	}
	
	# new session ID, so one planted before the login is worthless
	@session_regenerate_id(true);
	$_SESSION['login_ok'  ] = true;
	$_SESSION['login_user'] = $user;
	$_SESSION['sudo_user']['boi_session'] = null;
	$is_login = true;
	gs_log( GS_LOG_DEBUG, 'User '.$user.' logged in (HTTP)' );
}

$_SESSION['sudo_user']['name'] = is_string(@$_REQUEST['sudo']) ? $_REQUEST['sudo'] : '';
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
			if (! in_array(@$_SESSION['sudo_user']['name'], array('my'), true)) {
				$dispatcher_errors_html[] = sPrintF(htmlEnt(__('Unknown user "%s".')), @$_SESSION['sudo_user']['name']);
			}
			$_SESSION['sudo_user']['name'] = $_SESSION['real_user']['name'];
			$_SESSION['sudo_user']['info'] = $_SESSION['real_user']['info'];
		}
		$_SESSION['sudo_user']['boi_session'] = null;
	}
	if ($_SESSION['sudo_user']['name'] != $_SESSION['real_user']['name']) {
		# use the name as it is stored, not as it was requested
		$_SESSION['sudo_user']['name'] = $_SESSION['sudo_user']['info']['user'];
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
	} elseif (gs_user_is_admin(@$_SESSION['real_user']['name'])) {
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
	//$dispatcher_errors_html[] = sPrintF(htmlEnt(__('You are not allowed to act as "%s".')), @$_SESSION['sudo_user']['name']);
	$dispatcher_errors_html[] = sPrintF(htmlEnt(__("Sie d\xC3\xBCrfen nicht als %s agieren.")), '<q>'.htmlEnt(@$_SESSION['sudo_user']['name']).'</q>');
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



# which branch offices and roles the logged-in user may choose. this
# is what the selection in the GUI offers
#
function gs_boi_host_allowed( $host_id )
{
	global $DB;
	
	$host_id = (int)$host_id;
	$real = $_SESSION['real_user'];
	if ($real['name'] === 'sysadmin' || gs_user_is_admin(@$real['name'])) {
		if ($host_id < 1) return true;
		return ((int)$DB->executeGetOne( 'SELECT COUNT(*) FROM `hosts` WHERE `id`='. $host_id .' AND `is_foreign`=1' ) > 0);
	}
	if (! $real['info']['host_is_foreign']) {
		if ($host_id < 1) return true;
	} else {
		if ($host_id === (int)$real['info']['host_id']) return true;
		if ($host_id < 1) return false;
	}
	return ((int)$DB->executeGetOne(
		'SELECT COUNT(*) FROM `hosts` `h` JOIN `boi_perms` `p` ON (`p`.`host_id`=`h`.`id`) '.
		'WHERE `h`.`id`='. $host_id .' AND `p`.`user_id`='. (int)$real['info']['id'] .' AND `h`.`is_foreign`=1 AND `p`.`roles`<>\'\''
		) > 0);
}

function gs_boi_role_allowed( $role )
{
	global $DB;
	
	$real = $_SESSION['real_user'];
	if ($real['name'] === 'sysadmin' || gs_user_is_admin(@$real['name'])) {
		return in_array($role, array('gs','boi-user','boi-localadmin','boi-sysadmin'), true);
	}
	if (! $real['info']['host_is_foreign']) {
		return ($role === 'gs');
	}
	if ($role === 'boi-user') return true;
	if ($role === 'boi-localadmin') {
		$db_roles = (string)$DB->executeGetOne( 'SELECT `roles` FROM `boi_perms` WHERE `user_id`='. (int)$real['info']['id'] );
		return (strPos($db_roles,'l') !== false);
	}
	return false;
}

if (! gs_get_conf('GS_BOI_ENABLED')
||  ! @$_SESSION['login_ok']) {
	unset($_REQUEST['boi_host_id'], $_REQUEST['boi_role']);
}
if (array_key_exists('boi_host_id', $_REQUEST)
&&  ! gs_boi_host_allowed( $_REQUEST['boi_host_id'] )) {
	gs_log( GS_LOG_NOTICE, 'User '. @$_SESSION['real_user']['name'] .' is not allowed to select host '. (int)$_REQUEST['boi_host_id'] );
	unset($_REQUEST['boi_host_id']);
}
if (array_key_exists('boi_role', $_REQUEST)
&&  ! gs_boi_role_allowed( $_REQUEST['boi_role'] )) {
	gs_log( GS_LOG_NOTICE, 'User '. @$_SESSION['real_user']['name'] .' is not allowed to select the requested role' );
	unset($_REQUEST['boi_role']);
}

$get_host = false;
if (gs_get_conf('GS_BOI_ENABLED') && $is_login) {
	$get_host = true;
}
if (array_key_exists('boi_host_id', $_REQUEST)) {
	$_REQUEST['boi_host_id'] = (int)$_REQUEST['boi_host_id'];
	
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
	
	if ($_REQUEST['boi_role'] !== $_SESSION['sudo_user']['boi_role']) {
		$_SESSION['sudo_user']['boi_session'] = null;
		
		$_SESSION['sudo_user']['boi_role'] = $_REQUEST['boi_role'];
	}
}




if (! array_key_exists('boi_session', $_SESSION['sudo_user'])) {
	$_SESSION['sudo_user']['boi_session'] = null;
}



?>