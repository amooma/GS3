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

# to be called from the gateway to find out the Gemeinschaft
# node to call

/*
URL PARAMETERS:
   ext=    Extension (target number)
*/

@header( 'Content-Type: text/plain; charset=utf-8' );
@header( 'Cache-Control: private, must-revalidate, post-check=30' );
@header( 'Expires: 0' );

$ext = trim(@$_REQUEST['ext']);
if ($ext === '') {
	@header( 'Content-Length: 0' );
	exit();
}

define( 'GS_VALID', true );  /// this is a parent file
@require_once( dirName(__FILE__) .'/../../inc/conf.php' );
@require_once( GS_DIR .'inc/db_connect.php' );

$db = gs_db_slave_connect();
if (! $db) exit();

function _out( $str )
{
	@header( 'Expires: '. @gmDate('D, d M Y h:i:s', time()+30) .' GMT' );
	@header( 'Content-Length: '. strLen($str) );
	echo $str;
	exit();
}

$escaped_ext = $db->escape($ext);

# user?
#
$host = $db->executeGetOne(
'SELECT `h`.`host`
FROM
	`ast_sipfriends` `s` JOIN
	`users` `u` ON (`u`.`id`=`s`.`_user_id`) JOIN
	`hosts` `h` ON (`h`.`id`=`u`.`host_id`)
WHERE `s`.`name`=\''. $escaped_ext .'\'' );
if (! empty($host)) _out($host);

# queue?
#
$host = $db->executeGetOne(
'SELECT `h`.`host`
FROM
	`ast_queues` `q` JOIN
	`hosts` `h` ON (`h`.`id`=`q`.`_host_id`)
WHERE `q`.`name`=\''. $escaped_ext .'\'' );
if (! empty($host)) _out($host);

# conference?
#
$host = $db->executeGetOne(
'SELECT `h`.`host`
FROM
	`conferences` `c` JOIN
	`hosts` `h` ON (`h`.`id`=`c`.`host_id`)
WHERE `c`.`ext`=\''. $escaped_ext .'\'' );
if (! empty($host)) _out($host);

exit();

?>