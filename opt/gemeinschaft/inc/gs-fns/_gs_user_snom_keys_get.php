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

#
# DEPRECATED - use gs_keys_snom_get()
#


/***********************************************************
*    returns a user's keys on the Snom phone
***********************************************************/

function gs_user_snom_keys_get( $user )
{
	return new GsError( 'DEPRECATED.' );
	
	
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
	
	# get MAC address of phone
	#
	$mac = $db->executeGetOne( 'SELECT `mac_addr` FROM `phones` WHERE `user_id`='. $user_id );
	if (! trim($mac))
		return new GsError( 'User not logged in at any phone.' );
	
	//$prov = trim( @ file_get_contents( 'http://localhost:82/snom.php?mac='. $mac ) );
	$prov = trim( @ file_get_contents( GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'snom/settings.php?mac='. $mac ) );
	if (! $prov)
		return new GsError( 'Failed to get phone settings.' );
	
	$lines = explode("\n", $prov);
	unset($prov);
	$keys = array();
	foreach ($lines as $line) {
		$line = trim($line);
		if (preg_match( '/^fkey(\d+)[^:]*:\s?(.*)/', $line, $m )) {
			$keys[(int)$m[1]] = $m[2];
		}
	}
	unset($lines);
	kSort($keys);
	//print_r($keys);
	
	return $keys;
}


?>