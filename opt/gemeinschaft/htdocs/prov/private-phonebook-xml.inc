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
* Soeren Sprenger <soeren.sprenger@amooma.de>
* Markus Neubauer <markus.neubauer@email-online.org> - 2015
*  - extending db and preparing for vcards
* you need to alter the db for this version to work:
* 
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

defined('GS_VALID') or die('No direct access.');

require_once( dirName(__FILE__) .'/../../inc/conf.php' );
include_once( GS_DIR .'lib/yadb/yadb.php' );
include_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );
require_once( GS_DIR .'inc/db_connect.php' );

header('Content-type: application/xml');

if ( empty($_REQUEST) && !empty($argv) ) parse_str(implode('&', array_slice($argv, 1)), $_REQUEST);
if ( empty($_REQUEST) && !empty($_GET) ) $_REQUEST = $_GET;

$name         =      trim(@$_REQUEST['name'   ]);
$number       =      trim(@$_REQUEST['number' ]);
$catid        =      trim(@$_REQUEST['catid' ]);
$cat          =      trim(@$_REQUEST['cat' ]);

$user_id = (int)@$_REQUEST['id'];
$user = @$_REQUEST['user'];

if ( empty($catid) && isset($SESSION['catid']) ) $catid=(int)@$SESSION['catid'];
elseif ( empty($catid) ) $catid=0;

$query = 'SELECT `id`, `user` FROM `users` WHERE ';
if ( !empty($user) ) {
	$query .= '`user`=' . $user;
}
elseif ( !empty( $user_id ) ) {
	$query .= '`id`=' . $user_id;
}
elseif ( empty( $user ) && empty( $user_id ) ) die();

$DB = gs_db_master_connect();

$rs = $DB->execute( $query . ' LIMIT 1' );
if ( $r = $rs->fetchRow() ) {
	$user = $r['user'];
	$user_id = $r['id'];
} else die();

if ( empty($catid) && ! empty($cat) ) {
	$query = 'SELECT `id` FROM `pb_category` WHERE `category`=\'' . $cat . '\' LIMIT 1';
	 $catid = (int)$DB->executeGetOne( $query ); 
}


$query = 'SELECT SQL_CALC_FOUND_ROWS '.
			'`p`.`id`, `p`.`lastname`, `p`.`firstname`, `p`.`number` , `p`.`ptype`, `p`.`card_id`'.
		' FROM `pb_prv` `p`';
$where = ' WHERE `p`.`user_id`='. $user_id;

if ($number != '') {

	# search by number
	$name = '';
	$search_url = 'number='. urlEncode($number);
	$number_sql = str_replace(
		array( '*', '?' ),
		array( '%', '_' ),
		$number
	) .'%';
        $where .= ' AND `p`.`number` LIKE \''. $DB->escape($number_sql) .'\' ';

} else {

	# search by name
	$number = '';
	$search_url = 'name='. urlEncode($name);
	$name_sql = str_replace(
		array( '*', '?' ),
		array( '%', '_' ),
		$name
	) .'%';
        $where .= ' AND ( `p`.`lastname` LIKE _utf8\''. $DB->escape($name_sql) .'\' COLLATE utf8_unicode_ci OR '.
                    ' `p`.`firstname` LIKE _utf8\''. $DB->escape($name_sql) .'\' COLLATE utf8_unicode_ci ) ';
}

if ( $catid != 0 ) {
        $search_url .= '&amp;catid='. $catid;
        $query .= ' LEFT JOIN `pb_prv_category` `pc` ON `p`.`id` = `pc`.`prv_id` ';
        $where .= ' AND `pc`.`cat_id` = ' . $catid;
}

$order = ' ORDER BY `p`.`lastname`, `p`.`firstname`, `p`.`pref`, `p`.`ptype` ';

$rs = $DB->execute( $query . $where . $order);

$cs = $DB->execute(
        'SELECT `c`.`category` '.
	'  FROM `pb_category` `c` '.
	' WHERE `c`.`id` =' . $catid
	);
if ( $r = $cs->fetchRow() ) $sel = $r['category'];
else $sel = __('Alle Kategorien');

?>
<Title><?php echo $sel ?></Title>
<Prompt>Prompt</Prompt>
<?php

if (@$rs) {
	$i = 0;
	$last_seen = '';
	while ($r = $rs->fetchRow()) {
	        echo '<DirectoryEntry>' . PHP_EOL;
	        echo '<Name>' . trim( $r['lastname'] . ', ' . $r['firstname'], " ," ) . ' (' . $r['ptype'] . ')</Name>' . PHP_EOL;
	        echo '<Telephone>' . $r['number'] . '</Telephone>' . PHP_EOL;
	        echo '</DirectoryEntry>' . PHP_EOL;
	}
}

if ( $sel != __('Alle Kategorien') ) {
        echo '<SoftKeyItem>' . PHP_EOL;
        echo '<Name>*</Name>' . PHP_EOL;
        echo '<URL>http://' . $_SERVER['SERVER_NAME'] . '/gemeinschaft/prov/snom/private-phonebook-xml.php?user=' . $user . '</URL>' . PHP_EOL;
        echo '</SoftKeyItem>' . PHP_EOL;
}
?>
