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
require_once( GS_DIR .'inc/get-listen-to-ids.php' );
require_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'inc/db_connect.php' );
require_once( GS_DIR .'inc/group-fns.php' );

echo "\n";

$GS_INSTALLATION_TYPE_SINGLE = gs_get_conf('GS_INSTALLATION_TYPE_SINGLE');

if (! $GS_INSTALLATION_TYPE_SINGLE) {
	$our_ids = @ gs_get_listen_to_ids();
	if (! is_array($our_ids) || count($our_ids)<1) die();
	//FIXME - should probably write a message to gs_log() before dying
} else {
	$our_ids = array();
}

$db = gs_db_slave_connect();
if (! $db) die();
//FIXME - should probably write a message to gs_log() before dying


# hints for extensions
#
//$rs = $db->execute( 'SELECT `name` FROM `ast_sipfriends` ORDER BY LENGTH(`name`), `name`' );
echo "// hints for user extensions (auto-generated):\n";
$query =
'SELECT `s`.`name`
FROM
	`ast_sipfriends` `s` JOIN
	`users` `u` ON (`u`.`id`=`s`.`_user_id`)
WHERE
	`u`.`nobody_index` IS NULL'
;
if (! $GS_INSTALLATION_TYPE_SINGLE) {
	$query.= "\n". 'AND `u`.`host_id` IN ('. implode(',', $our_ids) .')';
}
$rs = $db->execute($query);
if ($rs) {
	while ($r = $rs->fetchRow()) {
		echo 'hint(SIP/', $r['name'] ,') ', $r['name'] ,' => {}', "\n";
		echo 'hint(SIP/', $r['name'] ,') ***', $r['name'] ,' => {}', "\n";
		echo 'hint(Custom:fwd',$r['name'],') fwd', $r['name'] ,' => {}', "\n";
	}
} else {
	echo "//ERROR\n";
	//FIXME - should probably write a message to gs_log()
}
echo "// end\n";
echo "\n";


# hints for pickup groups
#
echo "// hints for pickup groups (auto-generated):\n";
$query =
'SELECT
	`permit` `pg_id`
FROM
	`group_permissions`
WHERE
	`type` = \'group_pickup\'
GROUP BY
	`pg_id`';

$rs = $db->execute($query);
if ($rs) {
	while ($r = $rs->fetchRow()) {
		if ($group_members = gs_group_members_get(Array($r['pg_id']))) {
			$query =
'SELECT
	`name` `ext`
FROM
	`ast_sipfriends`	
WHERE
	`_user_id` IN ('.implode(',',$group_members).')
';
			$rsa = $db->execute($query);
			if ($rsa) {
				$devices = array();
				while ($ra = $rsa->fetchRow()) {
					$ext = preg_replace('/[^0-9*a-z\-_]/iS', '', $ra['ext']);
					if ($ext != '') $devices[] = 'SIP/'.$ext;
				}
				echo 'hint(', implode('&', $devices), ') *8*', str_pad($r['pg_id'],5,'0',STR_PAD_LEFT), ' => {}', "\n";
			}
		}
	}
}

echo "// end\n";
echo "\n";


?>