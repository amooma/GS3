<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 5603 $
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

define( 'GS_VALID', true );  /// this is a parent file
require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'htdocs/gui/inc/session.php' );
require_once( GS_DIR .'inc/log.php' );

function _not_allowed( $errmsg='' )
{
	@header( 'HTTP/1.0 403 Forbidden', true, 403 );
	@header( 'Status: 403 Forbidden' , true, 403 );
	@header( 'Content-Type: text/plain' );
	echo ($errmsg ? $errmsg : 'Not authorized.');
	exit(1);
}

function _server_error( $errmsg='' )
{
	@header( 'HTTP/1.0 500 Internal Server Error', true, 500 );
	@header( 'Status: 500 Internal Server Error' , true, 500 );
	@header( 'Content-Type: text/plain' );
	echo ($errmsg ? $errmsg : 'Internal Server Error.');
	exit(1);
}

function _not_found( $errmsg='' )
{
	@header( 'HTTP/1.0 404 Not Found', true, 404 );
	@header( 'Status: 404 Not Found' , true, 404 );
	@header( 'Content-Type: text/plain' );
	echo ($errmsg ? $errmsg : 'Not found.');
	exit(1);
}

function _not_modified( $etag='', $attach=false, $fake_filename='' )
{
	header( 'HTTP/1.0 304 Not Modified', true, 304 );
	header( 'Status: 304 Not Modified', true, 304 );
	if (! empty($etag))
		header( 'ETag: '. $etag );
	if (! empty($fake_filename))
		header( 'Content-Disposition: '.($attach ? 'attachment':'inline').'; filename="'.$fake_filename.'"' );
	exit(0);
}



if (! is_array($_SESSION)
||  ! @array_key_exists('sudo_user', @$_SESSION)
||  ! @array_key_exists('info'     , @$_SESSION['sudo_user'])
||  ! @array_key_exists('id'       , @$_SESSION['sudo_user']['info']) )
{
	_not_allowed();
}

$user_id = (int)@$_SESSION['sudo_user']['info']['id'];

$rs = $DB->execute(' SELECT * FROM `pb_prv` WHERE `user_id`='. $user_id );

header("Content-Type: text/plain");
header("Content-Disposition: attachment; filename=prv_pb_".$_SESSION['sudo_user']['info']['user'].".csv");

echo __('Nachname').';'.__('Vorname').';'.__('Nummer')."\r\n";
while ($entry = $rs->fetchRow()) {
	echo $entry['lastname'].";".$entry['firstname'].";".$entry['number']."\r\n";
}


?>
