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

defined('GS_VALID') or die('No direct access.');
include_once( GS_DIR .'inc/gs-lib.php' );


/***********************************************************
*    returns a user's call forwards
***********************************************************/

function gs_callforward_get_by_uid( $user_id )
{
	if (! preg_match( '/^[0-9]+$/', $user_id ))
		return new GsError( 'User ID must be numeric.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get states
	#
	$sources = array( 'internal', 'external' );
	$cases = array( 'always', 'busy', 'unavail', 'offline' );
	$forwards = array();
	foreach ($sources as $source) {
		foreach ($cases as $case) {
			$rs = $db->execute( 'SELECT `active`, `number_std`, `number_var`, `number_vml`, `timeout`, `vm_rec_id` FROM `callforwards` WHERE `user_id`='. $user_id .' AND `source`=\''. $source .'\' AND `case`=\''. $case .'\'' );
			if ($r = $rs->fetchRow()) {
				if (! in_array( $r['active'], array('no','std','var','vml','trl','par'), true ))
					$r['active'] = 'no';
				$forwards[$source][$case] = $r;
			} else {
				$forwards[$source][$case] = array(
					'active'     => 'no',
					'number_std' => '',
					'number_var' => '',
					'number_vml' => '',
					'timeout'    => 20,
					'vm_rec_id'  => 0
				);
			}
			if ($case != 'unavail') $forwards[$source][$case]['timeout'] = null;
		}
	}
	return $forwards;
}

function gs_callforward_get( $user )
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
	$user_id = $db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	if (! $user_id)
		return new GsError( 'Unknown user.' );

	# get states
	#
	$sources = array( 'internal', 'external' );
	$cases = array( 'always', 'busy', 'unavail', 'offline' );
	$forwards = array();
	foreach ($sources as $source) {
		foreach ($cases as $case) {
			$rs = $db->execute( 'SELECT `active`, `number_std`, `number_var`, `number_vml`, `timeout`, `vm_rec_id` FROM `callforwards` WHERE `user_id`='. $user_id .' AND `source`=\''. $source .'\' AND `case`=\''. $case .'\'' );
			if ($r = $rs->fetchRow()) {
				if (! in_array( $r['active'], array('no','std','var','vml','trl','par'), true ))
					$r['active'] = 'no';
				$forwards[$source][$case] = $r;
			} else {
				$forwards[$source][$case] = array(
					'active'     => 'no',
					'number_std' => '',
					'number_var' => '',
					'number_vml' => '',
					'timeout'    => 20,
					'vm_rec_id'  => 0
				);
			}
			if ($case != 'unavail') $forwards[$source][$case]['timeout'] = null;
		}
	}
	return $forwards;
}

?>