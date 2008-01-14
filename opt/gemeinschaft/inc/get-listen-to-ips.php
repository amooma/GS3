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

require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/log.php' );
if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
	require_once( GS_DIR .'inc/keyval.php' );
}


function gs_get_listen_to_ips( $primary_only=false )
{
	/*
	if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
		# return special address
		//return ($primary_only ? array('255.255.255.255') : array('255.255.255.255'));
		return array('255.255.255.255');
	}
	*/
	if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
		return array(trim(gs_keyval_get('vlan_0_ipaddr')));
	}
	
	$file = GS_DIR .'etc/listen-to-ip';
	if (! @file_exists( $file )) {
		# kann entweder passieren wenn wir ein Gemeinschaft-Node sind
		# (dann ist es extrem schlecht wenn die Datei fehlt) oder wenn
		# wir ein Web-Server ohne Asterisk sind (dann ist es ok)
		gs_log(GS_LOG_DEBUG, "File \"$file\" not found");
		return false;
	}
	if (! is_array($lines = @file( $file ))) {
		gs_log(GS_LOG_DEBUG, "Failed to read \"$file\"");
		return false;
	}
	$ips = array();
	foreach ($lines as $line) {
		$line = trim($line);
		if ($line=='' || @$line[0]=='#') continue;
		if (! preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $line, $m)) continue;
		$ips[] = normalizeIPs( $m[0] );
		if ($primary_only) {
			# only return the first IP address (our main one)
			return $ips;
		}
	}
	// remove duplicates:
	$ips = array_flip(array_flip( $ips ));
	sort($ips);
	return $ips;
}


?>