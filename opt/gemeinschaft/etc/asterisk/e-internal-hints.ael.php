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
/**echo "// hints for pickup groups (auto-generated):\n";
$query =
'SELECT
	`pg`.`id` `pg_id`,
	GROUP_CONCAT(`s`.`name` SEPARATOR \',\') `pg_members`
FROM
	`pickupgroups` `pg` JOIN
	`pickupgroups_users` `pu` ON (`pu`.`group_id`=`pg`.`id`) JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`pu`.`user_id`) JOIN
	`users` `u` ON (`u`.`id`=`pu`.`user_id`)'
;
if (! $GS_INSTALLATION_TYPE_SINGLE) {
	$query.= "\n". 'WHERE `u`.`host_id` IN ('. implode(',', $our_ids) .')';
}
$query.= "\n". 'GROUP BY `pg`.`id`';
$rs = $db->execute($query);
if ($rs) {
	while ($r = $rs->fetchRow()) {
		$members = explode(',', $r['pg_members']);
		if (count($members) < 1) continue;

		$devices = array();
		foreach ($members as $ext) {
			$ext = preg_replace('/[^0-9*a-z\-_]/iS', '', $ext);
			if ($ext == '') continue;
			$devices[] = 'SIP/'.$ext;
		}
		if (count($devices) < 1) continue;

		echo 'hint(', implode('&', $devices), ') *8*', str_pad($r['pg_id'],5,'0',STR_PAD_LEFT), ' => {}', "\n";
	}
} else {
	echo "//ERROR\n";
}
echo "// end\n";
echo "\n";
**/
?>