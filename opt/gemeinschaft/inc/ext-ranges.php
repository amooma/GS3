<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007-2026, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
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

defined('GS_VALID') or die('No direct access.');


# accepts strings like '200-205, 444, 555, 100-105'
function get_ext_ranges( $ext_ranges )
{
	$ext_ranges = explode(',', (string)$ext_ranges);
	$ret = array(
		'ranges'  => array(),
		'singles' => array()
	);
	foreach ($ext_ranges as $range) {
		$range = trim($range);
		if (! preg_match('/^([0-9]+)(?:\s*-\s*([0-9]+))?$/S', $range, $m)) continue;
		if (! array_key_exists(2, $m) || $m[2]===$m[1]) {
			$ret['singles'][] = $m[1];
		} else {
			if ($m[1] < $m[2])
				$ret['ranges'][] = array($m[1], $m[2]);
			else
				$ret['ranges'][] = array($m[2], $m[1]);
		}
	}
	return $ret;
}

# Users with an extension in the given ranges, keyed by extension:
# array('fn'=>first name, 'ln'=>last name)
function gs_ext_ranges_users( $db, $ext_ranges )
{
	$ext_ranges = get_ext_ranges( $ext_ranges );
	$users = array();
	foreach ($ext_ranges['ranges'] as $range) {
		_gs_ext_ranges_fetch( $db, $users,
			'`s`.`name`>=\''. $db->escape($range[0]) .'\' AND `s`.`name`<=\''. $db->escape($range[1]) .'\'',
			$range );
	}
	if ($ext_ranges['singles']) {
		$sql_in = array();
		foreach ($ext_ranges['singles'] as $ext)
			$sql_in[] = '\''. $db->escape($ext) .'\'';
		_gs_ext_ranges_fetch( $db, $users,
			'`s`.`name` IN ('. implode(',', $sql_in) .')' );
	}
	return $users;
}

# Adds the users matching $where to $users. With $range, skips names
# that fall inside the string range but not inside the numeric one.
function _gs_ext_ranges_fetch( $db, &$users, $where, $range=null )
{
	$rs = $db->execute(
		'SELECT `s`.`name` `ext`, `u`.`firstname` `fn`, `u`.`lastname` `ln` '.
		'FROM `users` `u` JOIN `ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) '.
		'WHERE '. $where );
	while ($r = $rs->fetchRow()) {
		if ($range
		&&  ((int)$r['ext'] < $range[0] || (int)$r['ext'] > $range[1])) continue;
		$users[$r['ext']] = array(
			'fn' => $r['fn'],
			'ln' => $r['ln']
		);
	}
}

?>