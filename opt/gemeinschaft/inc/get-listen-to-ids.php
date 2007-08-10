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

require_once( GS_DIR .'inc/get-listen-to-ips.php' );
include_once( GS_DIR .'inc/db_connect.php' );
//require_once( GS_DIR .'inc/util.php' );

function gs_get_listen_to_ids()
{
	# get our IPs
	#
	$ips = @ gs_get_listen_to_ips();
	if (! is_array($ips)) return array();
	
	# connect to db
	# must be to slave db so we can tell our IDs even if the master is down
	#
	$db = gs_db_slave_connect();
	if (! $db) return array();
	
	# find the corresponding IDs
	#
	$ips_escaped = array();
	foreach ($ips as $ip)
		$ips_escaped[] = '\''. $db->escape( $ip ) .'\'';
	// count($ips) guaranteed to be > 0
	$rs = $db->execute( 'SELECT `id` FROM `hosts` WHERE `host` IN ('. implode(',', $ips_escaped) .')' );
	if (! $rs) return array();
	$ids = array();
	while ($r = $rs->fetchRow())
		$ids[] = (int)$r['id'];
	
	return $ids;
}


?>