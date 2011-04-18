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
include_once( GS_DIR .'inc/log.php' );

@header( 'Vary: *' );
@header( 'Cache-Control: private, must-revalidate' );
@header( 'Content-Type: text/plain; charset=utf-8' );

function _not_allowed( $errmsg='' )
{
	@header( 'HTTP/1.0 403 Forbidden', true, 403 );
	@header( 'Status: 403 Forbidden' , true, 403 );
	@header( 'Content-Type: text/plain; charset=utf-8' );
	echo '/*  ', ($errmsg ? $errmsg : 'Not authorized.') ,'  */';
	gs_log( GS_LOG_NOTICE, ($errmsg ? $errmsg : 'LDAP lookup: Not authorized') );
	exit(1);
}

function _server_error( $errmsg='' )
{
	@header( 'HTTP/1.0 500 Internal Server Error', true, 500 );
	@header( 'Status: 500 Internal Server Error' , true, 500 );
	@header( 'Content-Type: text/plain; charset=utf-8' );
	echo '/*  ', ($errmsg ? $errmsg : 'Internal Server Error.') ,'  */';
	gs_log( GS_LOG_NOTICE, ($errmsg ? $errmsg : 'LDAP lookup: Error') );
	exit(1);
}

function _not_found( $errmsg='' )
{
	@header( 'HTTP/1.0 404 Not Found', true, 404 );
	@header( 'Status: 404 Not Found' , true, 404 );
	@header( 'Content-Type: text/plain; charset=utf-8' );
	echo '/*  ', ($errmsg ? $errmsg : 'Not found.') ,'  */';
	gs_log( GS_LOG_DEBUG, ($errmsg ? $errmsg : 'LDAP lookup: User not found') );
	exit(1);
}

$admin_ids = gs_group_members_get(array(gs_group_id_get('admins')));
if (! in_array(@$_SESSION['real_user']['info']['id'], $admin_ids) )
{
	_not_allowed();
}
        
if (! array_key_exists('u', $_REQUEST)) {
	_not_found( 'Username not specified.' );
}
$user = $_REQUEST['u'];

include_once( GS_DIR .'inc/gs-fns/gs_ldap_user_search.php' );
$user_info = gs_ldap_user_search( $user );
if (isGsError($user_info))
	_server_error( $user_info->getMsg() );
if (! is_array($user_info))
	_server_error( 'Failed to look up user "'.$user.'" in LDAP.' );


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