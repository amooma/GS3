#!/usr/bin/php -q
<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision:$
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


echo "\n";

# get gateways from DB
#
require_once( GS_DIR .'inc/db_connect.php' );
$DB = gs_db_master_connect();
if (! $DB) {
	exit(1);
}
$rs = $DB->execute(
'SELECT
	`g`.`name`, `g`.`hw_port`,
	`gg`.`name` `gg_name`
FROM
	`gates` `g` JOIN
	`gate_grps` `gg` ON (`gg`.`id`=`g`.`grp_id`)
WHERE
	`g`.`type`=\'capi\' AND
	`g`.`hw_port` > 0
ORDER BY `g`.`id`'
);
while ($gw = $rs->fetchRow()) {
	if ($gw['name'] == '') continue;
	
	echo '[', 'isdn_', $gw['name'] ,']' ,"\n";
	echo '; Dial(capi/isdn_', $gw['name'] ,'/...)' ,"\n";
	echo 'isdnmode=msn',"\n";
	echo 'incomingmsn=*' ,"\n";	
	echo 'controller=', $gw['hw_port'] ,"\n";
	echo 'group=', $gw['hw_port'] ,"\n";
	echo 'prefix=0' ,"\n";
	echo 'softdtmf=on' ,"\n";
	echo 'relaxdtmf=on' ,"\n";
	echo 'faxdetect=off' ,"\n";
	echo 'devices=2' ,"\n";
	echo 'context=from-gg-', $gw['gg_name'] ,"\n";
	echo 'setvar=__is_from_gateway=1' ,"\n";
	
	echo "\n";
}


?>