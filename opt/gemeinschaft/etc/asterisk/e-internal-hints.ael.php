#!/usr/bin/php -q
<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1691 $
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

echo "\n";

$our_ids = @ gs_get_listen_to_ids();
if (! is_array($our_ids) || count($our_ids)<1) die();

$db = gs_db_slave_connect();
if (! $db) die();

//$rs = $db->execute( 'SELECT `name` FROM `ast_sipfriends` ORDER BY LENGTH(`name`), `name`' );
$rs = $db->execute(
'SELECT `s`.`name`
FROM
	`ast_sipfriends` `s` JOIN
	`users` `u` ON (`u`.`id`=`s`.`_user_id`)
WHERE `u`.`host_id` IN ('. implode(',', $our_ids) .')'
);
if (! $rs) die();
while ($r = $rs->fetchRow()) {
	echo 'hint(SIP/', $r['name'], ') ', $r['name'], ' => {}', "\n";
	echo 'hint(SIP/', $r['name'], ') ***', $r['name'], ' => {}', "\n";
}

echo "\n";


?>