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

#
# DEPRECATED - use the functions in gs_keys_get.php
#


defined('GS_VALID') or die('No direct access.');
include_once( GS_DIR .'inc/gs-lib.php' );


/***********************************************************
*    returns a user's keys on a Snom phone
***********************************************************/

function gs_keys_snom_get( $user )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user_id
	#
	$user_id = (int)$db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	if (! $user_id)
		return new GsError( 'Unknown user.' );
	
	# init keys array
	#
	$default_key = array(
		'type' => 'line',
		'val'  => '',
		'rw'   => true,  # can be changed by the user's prefs
		'ds'   => '',    # short description
		'dl'   => ''     # long description
	);
	$keys = array();
	for ($i=0; $i<=53; ++$i) {
		$keys['f'.$i] = $default_key;
	}
	$keys['f0']['rw'] = false;  # key "fkey0"/"P1" should be set to "line"
	
	# set keys for pickup groups
	#
	$rs = $db->execute(
'SELECT DISTINCT(`p`.`id`) `id`, `p`.`title`
FROM
	`pickupgroups_users` `pu` JOIN
	`pickupgroups` `p` ON (`p`.`id`=`pu`.`group_id`)
WHERE `pu`.`user_id`='. $user_id .'
ORDER BY `p`.`id` LIMIT 6' );
	$k = 6;  # start at key "fkey6"/"P7"
	while ($r = $rs->fetchRow()) {
		$keys['f'.$k]['type'] = 'dest';
		$keys['f'.$k]['val' ] = '*8*'. str_pad($r['id'], 5, '0', STR_PAD_LEFT);
		$keys['f'.$k]['rw'  ] = false;
		$keys['f'.$k]['ds'  ] = 'Grp.'. $r['id'];
		$keys['f'.$k]['dl'  ] = 'Grp. '. (trim($r['title']) != '' ? trim($r['title']) : $r['id']);
		++$k;
	}
	
	# get user defined keys
	#
	$rs = $db->execute( 'SELECT `key`, `number` FROM `softkeys` WHERE `user_id`='. $user_id .' AND `phone`=\'snom\'' );
	while ($r = $rs->fetchRow()) {
		$key = $r['key'];
		if (! is_array( $keys[$key] ))
			$keys[$key] = $default_key;
		if (@$keys[$key]['rw']) {
			$keys[$key]['type'] = 'dest';
			$keys[$key]['val' ] = $r['number'];
		}
	}
	
	return $keys;
}


?>