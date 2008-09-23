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
*    adds a new host
***********************************************************/

function gs_host_add( $host_ip_or_name, $comment,  $sip_proxy_from_wan=null, $sip_sbc_from_wan=null )
{
	if ($host_ip_or_name) {
		$host = normalizeIPs($host_ip_or_name);
		$bInvalHostName = false;
		if (! preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $host)) {
			# not an IP address. => resolve hostname
			$addresses = @gethostbynamel($host);
			
			if (count($addresses) < 1) 
				return new GsError('Could not resolve hostname: ' .$host);
			elseif (count($addresses) > 1)
				return new GsError('Hostname '. $host . ' cannot be used because its resolved to more than one IP.');
			elseif (count($addresses) == 1) {
				if (strlen($addresses[0]) == 0) 
					return new GsError('Could not resolve hostname: ' .$host);
				$host = $addresses[0];
			}
		}
		else 
		$host = $host_ip_or_name;
	}
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	$db->execute('OPTIMIZE TABLE `hosts`');  # recalculate next auto-increment value
	$db->execute('ANALYZE TABLE `hosts`');

	$sql_query =
	'INSERT INTO `hosts` (
		`id`,
		`host`,
		`comment`,
		`is_foreign`
	) VALUES (
		NULL,
		\''. $db->escape($host) .'\',
		\''. $db->escape($comment) .'\',
		0
	)';
	$ok = $db->execute($sql_query);
	if ($ok) 
		$host_id = (int)$db->getLastInsertId();
	else 
		return new GsError( 'Fehler beim Hinzufuegen von Host: ' .$host);
			
	if ($host_id > 0) {
		$db->execute( 'REPLACE INTO `host_params` (`host_id`, `param`, `value`) VALUES ('. $host_id .', \'api\', \''. $db->escape(trim(@$_REQUEST['hp_api'])) .'\')' );
		$db->execute( 'REPLACE INTO `host_params` (`host_id`, `param`, `value`) VALUES ('. $host_id .', \'sip_proxy_from_wan\', \''. $db->escape(trim(@$_REQUEST['hp_sip_proxy_from_wan'])) .'\')' );
		$db->execute( 'REPLACE INTO `host_params` (`host_id`, `param`, `value`) VALUES ('. $host_id .', \'sip_server_from_wan\', \''. $db->escape(trim(@$_REQUEST['hp_sip_server_from_wan'])) .'\')' );
	}
		
	return true;
}


?>