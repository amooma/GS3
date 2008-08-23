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
*    checks if an ID or IP is among the configured hosts
*    and returns the host
***********************************************************/

function gs_host_by_id_or_ip( $id_or_ip )
{
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	$id_or_ip = trim($id_or_ip);
	
	if ((string)$id_or_ip === (string)(int)$id_or_ip) {
		$rs = $db->execute( 'SELECT `id`, `host`, `comment`, `is_foreign` FROM `hosts` WHERE `id`='. (int)$id_or_ip );
	} else {
		if (! preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $id_or_ip))
			return new GsError( 'Unknown host.' );
		
		$rs = $db->execute( 'SELECT `id`, `host`, `comment`, `is_foreign` FROM `hosts` WHERE `host`=\''. $db->escape($id_or_ip) .'\'' );
	}
	if (! $rs)
		return new GsError( 'Failed to get host.' );
	$host = $rs->fetchRow();
	if (! is_array($host))
		return new GsError( 'Unknown host.' );
	
	return $host;
}


?>