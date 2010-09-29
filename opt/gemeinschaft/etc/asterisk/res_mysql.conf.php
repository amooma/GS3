#!/usr/bin/php -q
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

ini_set('implicit_flush', 1);
ob_implicit_flush(1);

error_reporting(0);

require_once( dirName(__FILE__) .'/../../inc/conf.php' );
require_once( GS_DIR .'inc/util.php' );
set_error_handler('err_handler_quiet');
require_once( GS_DIR .'inc/mysql-find-socket.php' );


$socket = gs_mysql_find_socket( gs_get_conf('GS_DB_SLAVE_HOST') );

echo "\n";
if (! $socket) {
	echo 'dbhost = ', gs_get_conf('GS_DB_SLAVE_HOST') ,"\n";
	echo 'dbport = 3306'                              ,"\n";  # MySQL default
} else {
	echo 'dbhost = localhost' ,"\n";  # "localhost" is special
	echo 'dbport = 3306'      ,"\n";  # not used
	echo 'dbsock = ', $socket ,"\n";
}
echo 'dbuser = ', gs_get_conf('GS_DB_SLAVE_USER') ,"\n";
echo 'dbpass = ', gs_get_conf('GS_DB_SLAVE_PWD' ) ,"\n";
echo 'dbname = ', gs_get_conf('GS_DB_SLAVE_DB'  ) ,"\n";
echo 'requirements = warn' ,"\n";
echo "\n";


gs_log(GS_LOG_DEBUG, 'etc/asterisk/res_mysql.conf has just been loaded');

?>