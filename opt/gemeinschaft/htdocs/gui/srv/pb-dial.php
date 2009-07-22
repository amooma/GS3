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

@header( 'HTTP/1.0 204 No Content', true, 204 );
@header( 'Status: 204 No Content' , true, 204 );
@header( 'Vary: *' );


define( 'GS_VALID', true );  /// this is a parent file
require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'inc/log.php' );
require_once( GS_DIR .'htdocs/gui/inc/session.php' );

$number = @$_REQUEST['n'];
$user   = @$_SESSION['sudo_user']['name'];

gs_log( GS_LOG_DEBUG, "Dialling from web phonebook from user \"$user\" to \"$number\" ..." );

$url = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'call-init.php?user='. rawUrlEncode($user) .'&to='. rawUrlEncode($number) .'&'. @session_name() .'='. @session_id();

ini_set('default_socket_timeout', 4);
/*$out =*/ @file_get_contents( $url );

?>