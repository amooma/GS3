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

define( 'GS_VALID', true );  /// this is a parent file
require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'htdocs/gui/inc/session.php' );
include_once( GS_DIR .'inc/gs-lib.php' );

@header( 'Vary: *' );
@header( 'Cache-Control: private, must-revalidate' );
@header( 'Content-Type: text/plain; charset=utf-8' );

function _not_allowed( $errmsg='' )
{
	@header( 'HTTP/1.0 403 Forbidden', true, 403 );
	@header( 'Status: 403 Forbidden' , true, 403 );
	@header( 'Content-Type: text/plain; charset=utf-8' );
	echo '/*  ', ($errmsg ? $errmsg : 'Not authorized.') ,'  */';
	exit(1);
}

function _server_error( $errmsg='' )
{
	@header( 'HTTP/1.0 500 Internal Server Error', true, 500 );
	@header( 'Status: 500 Internal Server Error' , true, 500 );
	@header( 'Content-Type: text/plain; charset=utf-8' );
	echo '/*  ', ($errmsg ? $errmsg : 'Internal Server Error.') ,'  */';
	exit(1);
}

function _not_found( $errmsg='' )
{
	@header( 'HTTP/1.0 404 Not Found', true, 404 );
	@header( 'Status: 404 Not Found' , true, 404 );
	@header( 'Content-Type: text/plain; charset=utf-8' );
	echo '/*  ', ($errmsg ? $errmsg : 'Not found.') ,'  */';
	exit(1);
}

if (! is_array($_SESSION)
||  ! @array_key_exists('sudo_user', @$_SESSION)
||  ! @array_key_exists('info'     , @$_SESSION['sudo_user'])
||  ! @array_key_exists('id'       , @$_SESSION['sudo_user']['info'])
) {
	_not_allowed();
}

if ($_SESSION['real_user']['name'] !== 'sysadmin'
&&  ! preg_match('/\\b'.($_SESSION['real_user']['name']).'\\b/', GS_GUI_SUDO_ADMINS)
) {
	_not_allowed();
}

if (! array_key_exists('u', $_REQUEST)) {
	_not_found( 'Username not specified.' );
}
$user = $_REQUEST['u'];
if (! preg_match('/^[a-z0-9\-_]+$/', $user)) {
	_not_found( 'Invalid username.' );
}

$GS_LDAP_HOST = gs_get_conf('GS_LDAP_HOST');
if (in_array($GS_LDAP_HOST, array(null, false, '', '0.0.0.0'), true)) {
	_server_error( 'LDAP not configured.' );
}
if (! preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $GS_LDAP_HOST)) {
	$tmp = getHostByName($GS_LDAP_HOST);
	if ($tmp == $GS_LDAP_HOST) {
		_server_error( 'Failed to look up LDAP server.' );
	}
	$GS_LDAP_HOST = $tmp;
}
$tmp = @ip2long($GS_LDAP_HOST);
if (in_array($tmp, array(false, null, -1, 0), true)) {
	_server_error( 'LDAP not configured (bad IP address).' );
}

require_once( GS_DIR .'inc/ldap.php' );
if (!($ldap_conn = gs_ldap_connect(
	$GS_LDAP_HOST
))) {
	_server_error( 'Could not connect to LDAP server.' );
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
	_server_error( $user_arr->getMsg() );
if (! is_array($users_arr) || count($users_arr) < 1)
	_not_found( 'User not found in LDAP.' );
if (count($users_arr) > 1)
	_server_error( 'LDAP search did not return a unique user.' );
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



require_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );

@header( 'Content-Type: application/json' );  # RFC 4627
ob_start();
echo "{\n";
$i=0;
foreach ($user_info as $k => $v) {
	if ($i > 0) echo ",\n";
	echo '"',$k,'": ';
	if ($v === null) echo 'null';
	else echo utf8_json_quote($v);
	++$i;
}
echo "\n}\n";
if (! headers_sent()) {
	header( 'Content-Length: '. @ob_get_length() );
}
@ob_end_flush();

?>