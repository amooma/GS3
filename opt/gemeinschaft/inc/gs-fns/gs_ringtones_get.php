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


/***********************************************************
*    gets a user's ringtones
***********************************************************/

function gs_ringtones_get( $user )
{
	if (! preg_match( '/^[a-zA-Z\d]+$/', $user ))
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
	
	$ringtones = array(
		'internal' => array( 'bellcore' => 1, 'file' => null ),
		'external' => array( 'bellcore' => 1, 'file' => null )
	);
	
	# get ringers
	#
	$rs = $db->execute( 'SELECT `src`, `bellcore`, `file` FROM `ringtones` WHERE `user_id`='. $user_id );
	if (! $rs)
		return new GsError( 'Failed to get ringtones.' );
	while ($r = $rs->fetchRow()) {
		$src = $r['src'];
		if (! array_key_exists($src, $ringtones)) continue;
		
		/*
		if (($r['bellcore'] !== null || $r['bellcore'] < 0) && $r['file']) {
			$ringtones[$src]['bellcore'] = null;
			$ringtones[$src]['file'] = $r['file'];
		} else {
			$ringtones[$src]['bellcore'] = (int)$r['bellcore'];
			$ringtones[$src]['file'] = null;
		}
		*/
		
		$ringtones[$src]['bellcore'] = (int)$r['bellcore'];
		$ringtones[$src]['file'] = $r['file'];
	}
	
	return $ringtones;
}


?>