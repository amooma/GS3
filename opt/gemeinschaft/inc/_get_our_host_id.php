<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision:3061 $
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


/*                                                              */
/*                          DEPRECATED                          */
/*                                                              */


defined('GS_VALID') or die('No direct access.');

require_once( GS_DIR .'inc/db_connect.php' );
require_once( GS_DIR .'inc/util.php' );


function getOurHostID()
{
	$db = gs_db_slave_connect();
	
	$rs = $db->execute( 'SELECT `id`, `host` FROM `hosts`' );
	$hosts = array();
	while ($r = $rs->fetchRow())
		$hosts[] = $r;
	
	$ips = array();
	foreach ($hosts as $h) {
		$h['host'] = trim( normalizeIPs( $h['host'] ) );
		if (preg_match( '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $h['host'] ))
			$ips[$h['host']] = $h['id'];
		else {
			$tmp = getHostByNameL( $h['host'] );
			if (is_array($tmp)) {
				foreach ($tmp as $ip)
					$ips[normalizeIPs($ip)] = $h['id'];
			}
		}
	}
	unset($hosts);
	
	$ifconfig = normalizeIPs(trim(@shell_exec( 'ifconfig 2>>/dev/null' )));
	foreach ($ips as $ip => $hostid) {
		if (strPos( $ifconfig, $ip ) !== false)
			return $hostid;
	}
	return false;
}


?>