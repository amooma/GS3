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

//require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/netmask.php' );
require_once( GS_DIR .'inc/ipaddr-fns.php' );
require_once( GS_DIR .'inc/log.php' );
require_once( GS_DIR .'inc/db_connect.php' );
require_once( GS_DIR .'inc/nobody-extensions.php' );
require_once( GS_DIR .'inc/langhelper.php' );


function gs_prov_check_trust_requester()
{
	global $_SERVER;
	
	$ret = array(
		'allowed'  => false,
		'proxy_ip' => null,
		'phone_ip' => '0.0.0.0'
	);
	
	# find the phone's IP address
	#
	
	$remote_ip = @$_SERVER['REMOTE_ADDR'];
	
	$xff_header = gs_get_conf('GS_PROV_PROXIES_XFF_HEADER');
	$xff_env_key = 'HTTP_'. str_replace('-', '_', strToUpper($xff_header));
	
	if (array_key_exists('HTTP_VIA', $_SERVER)
	||  array_key_exists($xff_env_key, $_SERVER))
	{
		# is a request through a proxy
		$ret['proxy_ip'] = $remote_ip;
		
		# do we trust the proxy's XFF header?
		$proxy_allowed = ip_addr_in_network_list( $remote_ip, gs_get_conf('GS_PROV_PROXIES_TRUST') );
		if (! $proxy_allowed) {
			gs_log(GS_LOG_NOTICE, 'Proxy '. $remote_ip .' is not trusted' );
			return $ret;
		}
		
		if (! array_key_exists($xff_env_key, $_SERVER)) {
			gs_log(GS_LOG_NOTICE, 'No "'. $xff_header .'" header from proxy '. $remote_ip );
			return $ret;
		}
		$xff_chain = explode(',', $_SERVER[$xff_env_key]);
		if (count($xff_chain) < 1) {
			gs_log(GS_LOG_NOTICE, 'Empty "'. $xff_header .'" header from proxy '. $remote_ip );
			return $ret;
		}
		$phone_ip = trim($xff_chain[0]);
		if (! preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $phone_ip)) {
			gs_log(GS_LOG_NOTICE, 'IP address "'.$phone_ip.'" not recognized' );
			return $ret;
		}
		$ret['phone_ip'] = $phone_ip;
	}
	else {
		# direct request without proxy
		$ret['phone_ip'] = $remote_ip;
	}
	$ret['phone_ip'] = trim(normalizeIPs( $ret['phone_ip'] ));
	
	# phone allowed to request settings?
	#
	$ret['allowed'] = ip_addr_in_network_list( $ret['phone_ip'], gs_get_conf('GS_PROV_ALLOW_NET') );
	if (! $ret['allowed']) {
		gs_log(GS_LOG_NOTICE, 'Phone '. $ret['phone_ip'] .' is not allowed to request settings' );
		return $ret;
	}
	return $ret;
}


# check if we know the phone (by MAC address)
#
function gs_prov_user_id_by_mac_addr( $db, $mac_addr )
{
	$mac_addr = preg_replace('/[^0-9A-F\-]/', '', strToUpper($mac_addr));
	$query =
		'SELECT `user_id` '.
		'FROM `phones` '.
		'WHERE `mac_addr`=\''. $db->escape($mac_addr) .'\' '.
		'ORDER BY `id` '.
		'LIMIT 1';
	$user_id = (int)$db->executeGetOne($query);
	gs_log( GS_LOG_DEBUG, "Found user id $user_id for mac addr $mac_addr" );
	
	if ($user_id < 1) {
		# try to fall back to default nobody user for the phone
		$query =
			'SELECT `nobody_index` '.
			'FROM `phones` '.
			'WHERE `mac_addr`=\''. $db->escape($mac_addr) .'\' '.
			'ORDER BY `id` '.
			'LIMIT 1';
		$nobody_index = (int)$db->executeGetOne($query);
		if ($nobody_index > 0) {
			gs_log( GS_LOG_DEBUG, "Found nobody index $nobody_index for mac addr $mac_addr" );
			$query =
				'SELECT `id` '.
				'FROM `users` '.
				'WHERE `nobody_index`='. $nobody_index;
			$user_id = (int)$db->executeGetOne($query);
			if ($user_id > 0) {
				gs_log( GS_LOG_DEBUG, "Found fallback user id $user_id for nobody index $nobody_index" );
				$db->execute(
					'UPDATE `phones` SET '.
						'`user_id`=NULL '.
					'WHERE '.
						'`user_id`='. $user_id
					);
				$ok = $db->execute(
					'UPDATE `phones` SET '.
						'`user_id`='. $user_id .' '.
					'WHERE '.
						'`mac_addr`=\''. $db->escape($mac_addr) .'\' AND '.
						'`nobody_index`='. $nobody_index
					);
				if (! $ok) {
					gs_log( GS_LOG_WARNING, 'DB error' );
					return false;
				}
			} else {
				gs_log( GS_LOG_DEBUG, "Could not find fallback user id for nobody index $nobody_index" );
			}
		} else {
			gs_log( GS_LOG_NOTICE, "Could not find nobody index for mac addr $mac_addr" );
			return false;
		}
	}
	
	return ($user_id > 0 ? $user_id : false);
}


function gs_prov_gen_sip_pwd()
{
	$rand_from = base_convert('1000',36,10);
	$rand_to   = base_convert('zzzz',36,10);
	return
		base_convert(   rand( $rand_from, $rand_to ),10,36).
		base_convert(mt_rand( $rand_from, $rand_to ),10,36).
		base_convert(mt_rand( $rand_from, $rand_to ),10,36).
		base_convert(   rand( $rand_from, $rand_to ),10,36);
}



# find host for the new nobody user
#
function gs_prov_new_phone_find_boi_host_id( $db, $phone_ip )
{
	if (! gs_get_conf('GS_BOI_ENABLED')) {
		return null;  # Gemeinschaft
	}
	
	# BOI
	$boi_branch_netmask = gs_get_conf('GS_BOI_BRANCH_NETMASK');
	$boi_branch_netmask = subStr($boi_branch_netmask,0,1)==='/'
		? (int)subStr($boi_branch_netmask,1)
		: (int)$boi_branch_netmask;
	$dotted_netmask = ipv4_mask_length_to_dotted( $boi_branch_netmask );
	if (! $dotted_netmask) {
		gs_log( GS_LOG_WARNING, "Invalid BOI_BRANCH_NETMASK $boi_branch_netmask" );
		return false;
	}
	
	$network = ipv4_net_by_addr_and_mask( $phone_ip, $dotted_netmask );
	if (! $network) {
		gs_log( GS_LOG_WARNING, "Invalid branch net $netmask" );
		return false;
	}
	
	$boi_host_ip = ip_addr_network_add_sub( $network, gs_get_conf('GS_BOI_BRANCH_PBX') );
	if (! $boi_host_ip) {
		gs_log( GS_LOG_WARNING, "Invalid branch pbx $boi_host_ip" );
		return false;
	}
	
	$host_id = (int)$db->executeGetOne(
		'SELECT `id` '.
		'FROM `hosts` '.
		'WHERE '.
			'`host`=\''. $db->escape($boi_host_ip) .'\' AND '.
			'`is_foreign`=1'
		);
	if ($host_id > 0) {
		gs_log( GS_LOG_DEBUG, "Phone $phone_ip => foreign PBX $boi_host_ip (ID $host_id)" );
		return $host_id;
	}
	
	# no matching foreign host found, assume Gemeinschaft
	gs_log( GS_LOG_DEBUG, "Found no matching foreign PBX for phone $phone_ip, assuming Gemeinschaft" );
	return null;  # Gemeinschaft
}


# add a new nobody user if necessary, return the user's ID
#
function gs_prov_add_phone_get_nobody_user_id( $db, $mac_addr, $phone_type, $phone_ip )
{
	$add_nobody_locally_if_foreign_failed = false;  # hack
	
	$mac_addr = preg_replace('/[^0-9A-F\-]/', '', strToUpper($mac_addr));
		
	@gs_db_start_trans($db);
	
	# find host for the new nobody user
	#
	$boi_host_id = gs_prov_new_phone_find_boi_host_id( $db, $phone_ip );
	if ($boi_host_id === false) {
		@gs_db_rollback_trans($db);
		return false;
	}
	if ($boi_host_id < 1) {
		# Gemeinschaft
		switch (gs_get_conf('GS_PROV_AUTO_ADD_PHONE_HOST')) {
			case 'last':
				$host_id_sql = 'SELECT MAX(`id`) FROM `hosts` WHERE `is_foreign`=0'; break;
			case 'random':
				$host_id_sql = 'SELECT `id` FROM `hosts` WHERE `is_foreign`=0 ORDER BY RAND() LIMIT 1'; break;
			case 'first':
			default:
				$host_id_sql = 'SELECT MIN(`id`) FROM `hosts` WHERE `is_foreign`=0'; break;
		}
		$host_id = (int)$db->executeGetOne( $host_id_sql );
		if ($host_id < 1) {
			gs_log( GS_LOG_WARNING, 'Could not find a host for adding a nobody user' );
			@gs_db_rollback_trans($db);
			return false;
		}
	} else {
		# foreign host (BOI)
		$host_id = $boi_host_id;
	}
	
	# add a nobody user
	#
	$new_nobody_index = (int)( (int)$db->executeGetOne( 'SELECT MAX(`nobody_index`) FROM `users`' ) + 1 );
	
	$new_nobody_num  = 0;
	$hp_route_prefix = 0;
	$soap_user_ext   = 0;
	if ($boi_host_id > 0) {
		$new_nobody_num = (int)$db->executeGetOne( 'SELECT COUNT(`user`) FROM `users` WHERE `nobody_index` IS NOT NULL AND `host_id`='.$boi_host_id );
		$hp_route_prefix = (string)$db->executeGetOne(
			'SELECT `value` FROM `host_params` '.
			'WHERE `host_id`='. (int)$boi_host_id .' AND `param`=\'route_prefix\'' );
		$username = 'nobody-'.$hp_route_prefix.'-'. str_pad($new_nobody_num, 5, '0', STR_PAD_LEFT);
	}
	else {
		$username = 'nobody-'. str_pad($new_nobody_index, 5, '0', STR_PAD_LEFT);
	}
	$ok = $db->execute(
		'INSERT INTO `users` '.
		'(`id`, `user`, `pin`, `firstname`, `lastname`, `honorific`, `email`, `nobody_index`, `host_id`) '.
		'VALUES '.
		'(NULL, \''. $db->escape($username) .'\', \'\', \'\', \'\', \'\', \'\', '. $new_nobody_index .', '. $host_id .')'
		);
	if (! $ok
	||  ! ($user_id = (int)$db->getLastInsertId())) {
		gs_log( GS_LOG_WARNING, 'Failed to add nobody user '. $username .' to database' );
		@gs_db_rollback_trans($db);
		return false;
	} else {
		//gs_log( GS_LOG_DEBUG, 'Nobody user '. $username .' added to database (pending)' );
	}
	
	# add a SIP account:
	#
	if ($boi_host_id > 0) {
		$user_ext = $hp_route_prefix . gs_nobody_index_to_extension( $new_nobody_num, true );
		$soap_user_ext =               gs_nobody_index_to_extension( $new_nobody_num, true );
		//$user_ext = $hp_route_prefix . $soap_user_ext;
	}
	else {
		$user_ext = gs_nobody_index_to_extension( $new_nobody_index, false );
	}
	$sip_pwd = gs_prov_gen_sip_pwd();
	$ok = $db->execute(
		'INSERT INTO `ast_sipfriends` '.
		'(`_user_id`, `name`, `secret`, `context`, `callerid`, `setvar`, `language`) '.
		'VALUES '.
		'('. $user_id .', \''. $db->escape($user_ext) .'\', \''. $db->escape($sip_pwd) .'\', \'from-internal-nobody\', _utf8\''. $db->escape(GS_NOBODY_CID_NAME . $new_nobody_index .' <'.$user_ext.'>') .'\', \''. $db->escape('__user_id='.$user_id .';__user_name='.$user_ext) .'\', \''. gs_get_lang_global(GS_LANG_OPT_GS, GS_LANG_FORMAT_AST) .'\')'
		);
	if (! $ok) {
		gs_log( GS_LOG_WARNING, 'Failed to add a nobody user' );
		@gs_db_rollback_trans($db);
		return false;
	}
	
	# add nobody user at foreign host?
	#
	if ($boi_host_id > 0) {
		gs_log( GS_LOG_DEBUG, "Adding a nobody user at foreign host ID $boi_host_id" );
		
		if (! gs_get_conf('GS_BOI_ENABLED')) {
			gs_log( GS_LOG_WARNING, 'Failed to add nobody user on foreign host (BOI not enabled)' );
			@gs_db_rollback_trans($db);
			return false;
		}
		include_once( GS_DIR .'inc/boi-soap/boi-api.php' );
		$api = gs_host_get_api( $boi_host_id );
		switch ($api) {
			case 'm01':
			case 'm02':
				if (! extension_loaded('soap')) {
					gs_log( GS_LOG_WARNING, 'Failed to add nobody user on foreign host (SoapClient not available)' );
					@gs_db_rollback_trans($db);
					return false;
				}
				include_once( GS_DIR .'inc/boi-soap/boi-soap.php' );
				$boi_host = $db->executeGetOne( 'SELECT `host` FROM `hosts` WHERE `id`='. $host_id );
				if (! $boi_host) {
					gs_log( GS_LOG_WARNING, 'DB error: Failed to get host' );
					@gs_db_rollback_trans($db);
					return false;
				}
				$soap_faultcode = null;
				$ok = gs_boi_update_extension( $api, $boi_host, '', $soap_user_ext, $username, $sip_pwd, '', '', '', '', $soap_faultcode );
				if (! $ok) {
					gs_log( GS_LOG_WARNING, "Failed to add nobody user $username on foreign host $boi_host (SOAP error)" );
					if (! $add_nobody_locally_if_foreign_failed) { // normal behavior
						@gs_db_rollback_trans($db);
						return false;
					} else {  //FIXME - remove me - ugly hack
						$host_id = 1;
						gs_log( GS_LOG_DEBUG, "Failed to add nobody user on foreign host. Updating user $username, id: $user_id to host id $host_id" );
						
						$ok = $db->execute(
							'UPDATE `users` SET '.
								'`host_id`='. $host_id .' '.
							'WHERE '.
								'`id`='. $user_id
							);
						if (! $ok) {
							gs_log( GS_LOG_WARNING, "Failed to update nobody user $username at host id $host_id" );
							@gs_db_rollback_trans($db);
							return false;
						}
					}
				}
				break;
			
			case '':
				# host does not provide any API
				gs_log( GS_LOG_NOTICE, 'Adding user '.$username.' on foreign host '.$boi_host_id.' without any API' );
				break;
			
			default:
				gs_log( GS_LOG_WARNING, 'Failed to add user '.$username.' on foreign host '.$boi_host_id.' - invalid API "'.$api.'"' );
				@gs_db_rollback_trans($db);
				return false;
		}
	}
	
	# add the phone - but if it already exist only update the nobody-user
	#
	$old_id = $db->executeGetOne( 'SELECT `id` FROM `phones` WHERE `mac_addr`=\''.$mac_addr.'\'' );
	
	if ($old_id) {
		$ok = $db->execute( 'UPDATE `phones` SET `nobody_index`='. $new_nobody_index .' WHERE `id`='.$old_id );
		if (! $ok) {
			gs_log( GS_LOG_WARNING, "Failed to update nobody_index $new_nobody_index of phone $mac_addr" );
			@gs_db_rollback_trans($db);
			return false;
		}
	} else {
		$ok = $db->execute(
			'INSERT INTO `phones` '.
			'(`id`, `type`, `mac_addr`, `user_id`, `nobody_index`, `added`) '.
			'VALUES '.
			'(NULL, \''. $db->escape($phone_type) .'\', \''. $db->escape($mac_addr) .'\', '. $user_id .', '. $new_nobody_index .', '. time() .')'
			);
		if (! $ok) {
			gs_log( GS_LOG_WARNING, "Failed to add new phone $mac_addr" );
			@gs_db_rollback_trans($db);
			return false;
		}
	}
	$ok = @gs_db_commit_trans($db);
	if (! $ok) {
		gs_log( GS_LOG_WARNING, 'DB error' );
		return false;
	}
	return $user_id;
}


function gs_prov_assign_default_nobody( $db, $mac_addr='', $boi_host_id=null )
{
	$mac_addr = preg_replace('/[^0-9A-F]/', '', strToUpper($mac_addr));
	
	# try to restore
	$user_id = (int)$db->executeGetOne(
		'SELECT `u`.`id` '.
		'FROM '.
			'`users` `u` JOIN '.
			'`phones` `p` ON (`p`.`nobody_index`=`u`.`nobody_index`) '.
		'WHERE '.
			'`p`.`mac_addr`=\''. $db->escape($mac_addr) .'\' AND '.
			'`p`.`nobody_index`>=1 '.
		'LIMIT 1'
		);
	if ($user_id < 1) {
		gs_log( GS_LOG_NOTICE, 'Default nobody user of phone "'.$mac_addr.'" not found' );
		/*  // do *not* assign an unused nobody. that wouldn't be BOI-safe
		$user_id = (int)$db->executeGetOne(
			'SELECT `id` '.
			'FROM `users` '.
			'WHERE '.
				'`nobody_index` IS NOT NULL AND '.
				'`id` NOT IN (SELECT `user_id` FROM `phones` WHERE `user_id` IS NOT NULL) '.
			'ORDER BY RAND() '.
			'LIMIT 1'
			);
		if ($user_id < 1) {
			gs_log( GS_LOG_WARNING, 'No unused nobody accounts left.' );
			return false;
		}
		*/
		//FIXME - add a new nobody?
		return false;
	}
	$db->execute(
		'UPDATE `phones` SET '.
			'`user_id`=NULL '.
		'WHERE `user_id`='. $user_id );
	$ok = $db->execute(
		'UPDATE `phones` SET '.
			'`user_id`='. $user_id .' '.
		'WHERE `mac_addr`=\''. $db->escape($mac_addr) .'\' '.
		'LIMIT 1' );
	if (! $ok) {
		gs_log( GS_LOG_WARNING, 'Failed to assign nobody account to phone '. $mac_addr );
		return false;
	}
	return $user_id;
}


function gs_prov_get_host_for_user_id( $db, $user_id )
{
	$user_id = (int)$user_id;
	$host = $db->executeGetOne(
		'SELECT `h`.`host` '.
		'FROM '.
			'`users` `u` LEFT JOIN '.
			'`hosts` `h` ON (`h`.`id`=`u`.`host_id`) '.
		'WHERE `u`.`id`='. $user_id .' '.
		'LIMIT 1'
		);
	
	/*  // never used this code anyway
	# if no host specified, select one randomly
	if (! $host) {
		//FIXME - not BOI-safe
		$host = $db->executeGetOne( 'SELECT `host` FROM `hosts` ORDER BY RAND() LIMIT 1' );
		if (! $host) {
			gs_log( GS_LOG_WARNING, 'No hosts known.' );
			return false;
		}
	}
	*/
	
	if (! $host) {
		gs_log( GS_LOG_WARNING, 'Failed to find host for user ID '. $user_id );
	}
	return $host;
}


function gs_prov_get_user_info( $db, $user_id )
{
	$rs = $db->execute(
		'SELECT '.
			'`u`.`user`, `u`.`firstname`, `u`.`lastname`, `u`.`honorific`, `u`.`nobody_index`, `u`.`host_id`, '.
			'`s`.`name`, `s`.`secret`, `s`.`callerid`, `s`.`mailbox`, `s`.`language` '.
		'FROM '.
			'`users` `u` JOIN '.
			'`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) '.
		'WHERE `u`.`id`='. (int)$user_id
		);
	if (! $rs) {
		gs_log( GS_LOG_WARNING, 'DB error' );
		return false;
	}
	$user = $rs->fetchRow();
	if (! is_array($user)) {
		gs_log( GS_LOG_WARNING, 'DB error' );
		return false;
	}
	return $user;
}


/*
function gs_prov_assign_user( $mac_addr, $phone_type )
{
	$mac_addr = preg_replace('/[^0-9A-F]/', '', strToUpper($mac_addr));
	$user_id = @gs_prov_add_phone_get_nobody_user_id( $mac_addr, $phone_type );
	if ($user_id < 1) {
		$user_id = @gs_prov_find_free_nobody( $mac_addr, null )  //FIXME - BOI
		if ($user_id < 1) {
			return false;
		}
	}
	return $user_id;
}
*/


function gs_prov_get_wan_outbound_proxy( $db, $phone_ip, $user_id )
{
	$ret = array(
		'sip_proxy_from_wan' => '',
		'sip_server_from_wan' => ''
	);
	if (ip_addr_in_network_list( $phone_ip, gs_get_conf('GS_PROV_LAN_NETS') )) {
		gs_log( GS_LOG_DEBUG, "Phone $phone_ip is in LAN => no SIP proxy" );
		return $ret;
	}
	$rs = $db->execute(
		'SELECT `h`.`id`, `h`.`host` '.
		'FROM '.
			'`users` `u` JOIN '.
			'`hosts` `h` ON (`h`.`id`=`u`.`host_id`) '.
		'WHERE `u`.`id`='. $user_id );
	if (! $rs || ! ($host = $rs->getRow())) {
		gs_log( GS_LOG_WARNING, "Failed to find host for user id $user_id" );
		return $ret;
	}
	if (! ip_addr_in_network_list( $host['host'], gs_get_conf('GS_PROV_LAN_NETS') )) {
		gs_log( GS_LOG_DEBUG, "Host ". $host['host'] ." is in WAN => no SIP proxy" );
		return $ret;
	}
	
	# phone in WAN, host in LAN => get outbound proxy for the phone
	$ret['sip_proxy_from_wan'] = $db->executeGetOne(
		'SELECT `value` '.
		'FROM `host_params` '.
		'WHERE '.
			'`host_id`='. (int)$host['id'] .' AND '.
			'`param`=\'sip_proxy_from_wan\''
		);
	if (! $ret['sip_proxy_from_wan']) {
		$ret['sip_proxy_from_wan'] = '';
	} else {
		if (! preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $ret['sip_proxy_from_wan'])) {
			gs_log( GS_LOG_WARNING, 'Invalid proxy IP addr. "'. $ret['sip_proxy_from_wan'] .'"' );
			$ret['sip_proxy_from_wan'] = '';
		}
	}
	
	# phone in WAN, host in LAN => get server for the phone
	$ret['sip_server_from_wan'] = $db->executeGetOne(
		'SELECT `value` '.
		'FROM `host_params` '.
		'WHERE '.
			'`host_id`='. (int)$host['id'] .' AND '.
			'`param`=\'sip_server_from_wan\''
		);
	if (! $ret['sip_server_from_wan']) {
		$ret['sip_server_from_wan'] = '';
	} else {
		if (! preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $ret['sip_server_from_wan'])) {
			gs_log( GS_LOG_WARNING, 'Invalid SBC IP addr. "'. $ret['sip_server_from_wan'] .'"' );
			$ret['sip_server_from_wan'] = '';
		}
	}
	
	gs_log( GS_LOG_DEBUG, "Phone $phone_ip in WAN, host ". $host['host'] ." in LAN => sip_proxy_from_wan: ". $ret['sip_proxy_from_wan'] .", sip_server_from_wan: ". $ret['sip_server_from_wan'] );
	return $ret;
}


# store the user's current IP address in the database:
#
function gs_prov_update_user_ip( $db, $user_id, $phone_ip_addr )
{
	if (! preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $phone_ip_addr))
		$phone_ip_addr = null;
	
	# unset all IP addresses which are the same as the new one and
	# thus cannot be valid any longer:
	if ($phone_ip_addr) {
		$db->execute(
			'UPDATE `users` SET '.
				'`current_ip`=NULL '.
			'WHERE `current_ip`=\''. $db->escape($phone_ip_addr) .'\'' );
	}
	# store new IP address:
	$user_id = (int)$user_id;
	if ($user_id > 0) {
		$ok = $db->execute(
			'UPDATE `users` SET '.
				'`current_ip`='. ($phone_ip_addr ? ('\''. $db->escape($phone_ip_addr) .'\'') : 'NULL') .' '.
			'WHERE `id`='. $user_id );
	} else {
		$ok = false;
	}
	return $ok;
}




?>
