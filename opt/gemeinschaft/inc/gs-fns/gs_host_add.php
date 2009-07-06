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

function gs_host_add( $host_ip_or_name, $comment, $foreign=false, $group_id=null, $boi_prefix='', $sip_proxy_from_wan=null, $sip_sbc_from_wan=null )
{
	if (! $host_ip_or_name)
		return new GsError('Invalid host.');
	
	$host = normalizeIPs($host_ip_or_name);
	if (! preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $host)) {
		# not an IP address. => resolve hostname
		$addresses = @gethostbynamel($host);
		
		if (count($addresses) < 1) {
			return new GsError('Could not resolve hostname: '. $host);
		} elseif (count($addresses) > 1) {
			return new GsError('Hostname '. $host .' cannot be used because it\'s resolved to more than one IP addr.');
		} elseif (count($addresses) == 1) {
			if (strlen($addresses[0]) == 0)
				return new GsError('Could not resolve hostname: '. $host);
			$host = $addresses[0];
		}
	}
	else {
		$host = $host_ip_or_name;
	}
	
	$group_id = (int)$group_id;
	if ($group_id < 1) $group_id = null;
	
	if (! $foreign) {
		if ($boi_prefix != '') {
			return new GsError( 'Route prefix not allowed for non-foreign hosts.' );
		}
	} else {
		if (($boi_prefix != '') && (! preg_match('/^[1-9][0-9]*$/', $boi_prefix))) {
			return new GsError( 'Route prefix must be numeric.' );	
		}
	}
	
	if ($sip_proxy_from_wan != '' && ! preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $sip_proxy_from_wan)) {
		return new GsError( 'Invalid IP address of SIP proxy from WAN.' );
	}
	if ($sip_sbc_from_wan != '' && ! preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $sip_sbc_from_wan)) {
		return new GsError( 'Invalid IP address of SIP SBC from WAN.' );
	}
	
	if ($foreign) {
		$api = gs_get_conf('GS_BOI_API_DEFAULT');
	} else {
		$api = '';
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
		`is_foreign`,
		`group_id`
	) VALUES (
		NULL,
		\''. $db->escape($host) .'\',
		\''. $db->escape($comment) .'\',
		'. ($foreign ?1:0) .',
		'. (int)$group_id .'
	)';
	$ok = $db->execute($sql_query);
	if (! $ok)
		return new GsError( 'Failed to add host '. $host );
	$host_id = (int)$db->getLastInsertId();
	if ($host_id < 1)
		return new GsError( 'Failed to add host '. $host );
	
	$db->execute( 'REPLACE INTO `host_params` (`host_id`, `param`, `value`) VALUES ('. $host_id .', \'api\', \''. $db->escape($api) .'\')' );
	if ($sip_proxy_from_wan != '')
		$db->execute( 'REPLACE INTO `host_params` (`host_id`, `param`, `value`) VALUES ('. $host_id .', \'sip_proxy_from_wan\', \''. $db->escape($sip_proxy_from_wan) .'\')' );
	if ($sip_sbc_from_wan != '')
		$db->execute( 'REPLACE INTO `host_params` (`host_id`, `param`, `value`) VALUES ('. $host_id .', \'sip_server_from_wan\', \''. $db->escape($sip_sbc_from_wan) .'\')' );
	if ($foreign)
		$db->execute( 'REPLACE INTO `host_params` (`host_id`, `param`, `value`) VALUES ('. $host_id .', \'route_prefix\', \''. $db->escape($boi_prefix) .'\')' );
	
	return true;
}


?>