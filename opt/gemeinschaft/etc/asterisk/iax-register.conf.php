#!/usr/bin/php -q
<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2009, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Sebastian Ertz <gemeinschaft@swastel.eisfair.net>
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
require_once( GS_DIR .'inc/log.php' );
set_error_handler('err_handler_quiet');


# get gateways from DB
#
require_once( GS_DIR .'inc/db_connect.php' );
$DB = gs_db_master_connect();
if (! $DB) {
	exit(1);
}
$rs = $DB->execute(
'SELECT
	`g`.`name`, `g`.`host`, `g`.`user`, `g`.`pwd`,
	`gg`.`name` `gg_name`
FROM
	`gates` `g` JOIN
	`gate_grps` `gg` ON (`gg`.`id`=`g`.`grp_id`)
WHERE
	`g`.`type`=\'iax\' AND
	`g`.`host` IS NOT NULL AND
	`g`.`register`= 1
ORDER BY `g`.`id`'
);
while ($gw = $rs->fetchRow()) {
	if ($gw['host'] != '' && $gw['user'] != '') {
		
		// Format for registration is user[:secret]@host[:port]
				
		echo 'register => ', $gw['user'];  # user
		if ($gw['pwd'] != '') {
			echo ':', $gw['pwd'];          # password
		}
		echo '@';
		echo $gw['host'];             # host
		//if ($gw['port'] != '') {
			//echo ':', $gw['port'];         # port
		//}
		echo "\n";
	}
	
}
echo "\n";


?>