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
require_once( GS_DIR .'inc/db_connect.php' );
//require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/log.php' );


function gs_get_listen_to_ids( $primary_only=false )
{
	if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
		# return special host ID
		return array(-1);
	}
	
	# get our IPs
	#
	$ips = @ gs_get_listen_to_ips( $primary_only );
	if (! is_array($ips)) {
		# kann entweder passieren wenn wir ein Gemeinschaft-Node sind
		# (dann ist es extrem schlecht wenn die Datei fehlt) oder wenn
		# wir ein Web-Server ohne Asterisk sind (dann ist es ok)
		gs_log(GS_LOG_DEBUG, "Failed to get our IP addresses");
		return array();
	}
	if (count($ips) < 1) {
		gs_log(GS_LOG_DEBUG, "We're not configured to listen to any IP addresses");
		return array();
	}
	
	# connect to db
	# must be to slave db so we can tell our IDs even if the master is down
	#
	$db = gs_db_slave_connect();
	if (! $db) {
		gs_log(GS_LOG_WARNING, "Failed to connect to the database!");
		return array();
	}
	
	# find the corresponding IDs
	#
	$ips_escaped = array();
	foreach ($ips as $ip)
		$ips_escaped[] = '\''. $db->escape( $ip ) .'\'';
	// count($ips) guaranteed to be > 0
	$rs = $db->execute( 'SELECT `id` FROM `hosts` WHERE `host` IN ('. implode(',', $ips_escaped) .')' );
	if (! $rs) {
		gs_log(GS_LOG_WARNING, "Database error!");
		return array();
	}
	$ids = array();
	while ($r = $rs->fetchRow())
		$ids[] = (int)$r['id'];
	
	return $ids;
}


function gs_get_listen_to_primary_id()
{
	$ids = @gs_get_listen_to_ids(true);
	if (! is_array($ids)) {
		gs_log(GS_LOG_DEBUG, "Failed to get our primary IP address");
		return null;
	}
	if (count($ids) < 1) {
		gs_log(GS_LOG_DEBUG, "Failed to get our primary IP address");
		return null;
	}
	return $ids[0];
}


?>