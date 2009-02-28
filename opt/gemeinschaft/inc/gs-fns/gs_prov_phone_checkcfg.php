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

require_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/gs-lib.php' );
include_once( GS_DIR .'inc/gs-fns/gs_hosts_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_ip_by_ext.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_ip_by_user.php' );

function _gs_prov_phone_checkcfg_exclude_ip( $ip )
{
	$db = gs_db_slave_connect();
	if (! $db) return false;
	$is_server = (int)$db->executeGetOne(
		'SELECT 1 '.
		'FROM `host_params` '.
		'WHERE '.
			'`param` IN (\'sip_proxy_from_wan\', \'sip_server_from_wan\') AND '.
			'`value`=\''. $db->escape($ip) .'\' '.
		'LIMIT 1'
		);
	if ($is_server) {
		gs_log(GS_LOG_DEBUG, "IP $ip is a server, not a phone");
		return true;
	}
	return false;
}


/***********************************************************
*    make a phone re-check it's config and
*    optionally reboot
***********************************************************/

function gs_prov_phone_checkcfg_by_user( $usercode, $reboot=true )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $usercode ))
		return new GsError( 'User must be alphanumeric.' );
	
	$ip = gs_user_ip_by_user( $usercode );
	if (isGsError( $ip ))
		return new GsError( $ip->getMsg() );
	
	$userArr = gs_user_get( $usercode );
	if (isGsError( $userArr ))
		return new GsError( $userArr->getMsg() );
	if (! is_array($userArr))
		return new GsError( 'Failed to get user from DB.' );
	$ext = $userArr['ext'];
	
	gs_log(GS_LOG_DEBUG, "phone_checkcfg by user \"$usercode\", ip \"$ip\", ext \"$ext\"");
	
	//echo "       IP: $ip\n";
	$ok1 = _gs_prov_phone_checkcfg_by_ip_do( $ip, $reboot );
	//echo "Extension: $ext\n";
	$ok2 = _gs_prov_phone_checkcfg_by_ext_do( $ext, $reboot );
	return $ok1 || $ok2;
}

function gs_prov_phone_checkcfg_by_ext( $ext, $reboot=true )
{
	if (! preg_match( '/^[\d]+$/', $ext ))
		return new GsError( 'Extension must be numeric.' );
	
	$ip = gs_user_ip_by_ext( $ext );
	if (isGsError( $ip ))
		return new GsError( $ip->getMsg() );
	
	gs_log(GS_LOG_DEBUG, "phone_checkcfg by ext \"$ext\", ip \"$ip\"");
	
	//echo "       IP: $ip\n";
	$ok1 = _gs_prov_phone_checkcfg_by_ip_do( $ip, $reboot );
	//echo "Extension: $ext\n";
	$ok2 = _gs_prov_phone_checkcfg_by_ext_do( $ext, $reboot );
	return $ok1 || $ok2;
}

function gs_prov_phone_checkcfg_by_ip( $ip, $reboot=true )
{
	if (! preg_match( '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip ))
		return new GsError( 'Not a valid IP address.' );
	
	/*
	$db = gs_db_master_connect();
	$rs = $db->execute(
'SELECT `s`.`name`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE `u`.`current_ip`=\''. $db->escape($ip) .'\'
' );
	*/
	
	gs_log(GS_LOG_DEBUG, "phone_checkcfg by ip \"$ip\"");
	
	//echo "       IP: $ip\n";
	$ok1 = _gs_prov_phone_checkcfg_by_ip_do( $ip, $reboot );
	//echo "Extension: $ext\n";
	$ok2 = false;
	return $ok1 || $ok2;
}

function gs_prov_phone_checkcfg_all( $reboot=true )
{
	$db = gs_db_master_connect();
	
	gs_log(GS_LOG_DEBUG, 'phone_checkcfg all phones');
	
	$rs = $db->execute(
'SELECT `s`.`name`, `u`.`current_ip` `ip`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)'
	);
	while ($r = $rs->fetchRow()) {
		if ($r['ip'])
			_gs_prov_phone_checkcfg_by_ip_do( $r['ip'], $reboot );
		_gs_prov_phone_checkcfg_by_ext_do( $ext, $reboot );
	}
	return true;
}


// PRIVATE:
function _gs_prov_phone_checkcfg_by_ip_do( $ip, $reboot=true )
{
	if (! preg_match( '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip ))
		return false;
	
	gs_log(GS_LOG_DEBUG, "do phone_checkcfg by ip \"$ip\"");
	
	/*
	$db = @gs_db_slave_connect();
	if (! $db) {
		gs_log(GS_LOG_WARNING, 'Failed to connect to DB');
		return false;
	}
	$rs = @$db->execute(
'SELECT DISTINCT(`p`.`type`)
FROM
	`users` `u` JOIN
	`phones` `p` ON (`p`.`user_id`=`u`.`id`)
WHERE
	`u`.`current_ip`=\''. $db->escape($ip) .'\''
	);
	if (! $rs) {
		gs_log(GS_LOG_WARNING, 'DB error');
		return false;
	}
	$is_snom    = false;
	$is_siemens = false;
	while ($r = $rs->fetchRow()) {
		$tmp = strToLower($r['type']);
		if     (subStr($tmp,0,4)==='snom')
			$is_snom    = true;
		elseif (subStr($tmp,0,7)==='siemens')
			$is_siemens = true;
	}
	
	# no elseif here!:
	if ($is_snom) {
		gs_log(GS_LOG_DEBUG, "do phone_checkcfg by ip \"$ip\" (snom)");
		_gs_prov_phone_checkcfg_by_ip_do_snom   ( $ip, $reboot );
	}
	if ($is_siemens) {
		gs_log(GS_LOG_DEBUG, "do phone_checkcfg by ip \"$ip\" (siemens)");
		_gs_prov_phone_checkcfg_by_ip_do_siemens( $ip, $reboot );
	}
	if (! $is_snom && ! $is_siemens) {
		# we don't know the type of that phone, just try everything
		gs_log(GS_LOG_NOTICE, "Not sure how to sync phone");
		gs_log(GS_LOG_DEBUG, "do phone_checkcfg by ip \"$ip\" (unknown phone type)");
		_gs_prov_phone_checkcfg_by_ip_do_snom   ( $ip, $reboot );
		_gs_prov_phone_checkcfg_by_ip_do_siemens( $ip, $reboot );
	}
	*/
	# damn - we did already removed the user id from the phones table
	
	if (gs_get_conf('GS_SNOM_PROV_ENABLED')) {
		_gs_prov_phone_checkcfg_by_ip_do_snom   ( $ip, $reboot );
	}
	if (gs_get_conf('GS_SNOM_PROV_M3_ACCOUNTS')) {
		_gs_prov_phone_checkcfg_by_ip_do_snom_m3( $ip, $reboot );
	}
	if (gs_get_conf('GS_SIEMENS_PROV_ENABLED')) {
		_gs_prov_phone_checkcfg_by_ip_do_siemens( $ip, $reboot );
	}
	if (gs_get_conf('GS_AASTRA_PROV_ENABLED')) {
		_gs_prov_phone_checkcfg_by_ip_do_aastra ( $ip, $reboot );
	}
	
	//return $err == 0;
	return true;
}

// REALLY PRIVATE! CAREFUL WITH PARAMS - NO VALIDATION!
function _gs_prov_phone_checkcfg_by_ip_do_snom( $ip, $reboot=true )
{
	if (_gs_prov_phone_checkcfg_exclude_ip( $ip )) return;
	
	@ exec( 'wget -O /dev/null -o /dev/null -b --tries=3 --timeout=8 --retry-connrefused -q --user='. qsa(gs_get_conf('GS_SNOM_PROV_HTTP_USER','')) .' --password='. qsa(gs_get_conf('GS_SNOM_PROV_HTTP_PASS','')) .' '. qsa('http://'. $ip .'/confirm.htm?REBOOT=yes') . ' >>/dev/null 2>>/dev/null &', $out, $err );
	// Actually the value after REBOOT= does not matter.
	// Is there a check-sync URL *without* reboot?
}

// REALLY PRIVATE! CAREFUL WITH PARAMS - NO VALIDATION!
function _gs_prov_phone_checkcfg_by_ip_do_snom_m3( $ip, $reboot=true )
{
	if (_gs_prov_phone_checkcfg_exclude_ip( $ip )) return;
	
	// The M3 has to be rebooted to read its config.
	@ exec( 'wget -O /dev/null -o /dev/null -b --tries=3 --timeout=8 --retry-connrefused -q --user='. qsa(gs_get_conf('GS_SNOM_PROV_HTTP_USER','')) .' --password='. qsa(gs_get_conf('GS_SNOM_PROV_HTTP_PASS','')) .' '. qsa('http://'. $ip .'/reboot.html') . ' >>/dev/null 2>>/dev/null &', $out, $err );
}


// REALLY PRIVATE! CAREFUL WITH PARAMS - NO VALIDATION!
function _gs_prov_phone_checkcfg_by_ip_do_siemens( $ip, $reboot=true, $pre_sleep=0 )
{
	$file = '/opt/gemeinschaft-siemens/prov-checkcfg.php';
	
	if (file_exists( $file ) && is_readable( $file )) {
		include_once( $file );
		@_gs_siemens_prov_phone_checkcfg_by_ip_do_siemens( $ip, $reboot, $pre_sleep );
	} else {
		gs_log(GS_LOG_NOTICE, 'Siemens provisioning not available');
	}
}

function _gs_prov_phone_checkcfg_by_ip_do_aastra( $ip, $reboot=true )
{
	if (_gs_prov_phone_checkcfg_exclude_ip( $ip )) return;
	
	$prov_host = gs_get_conf('GS_PROV_HOST');
	
	$xmlpi = '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
	$xml = '<AastraIPPhoneExecute>' ."\n";
	$xml.= '	<ExecuteItem URI="Command: Reset" />' ."\n";
	$xml.= '</AastraIPPhoneExecute>' ."\n";
	
	$cmd = 'wget -O /dev/null -o /dev/null -b --tries=3 --timeout=8 --retry-connrefused -q'
		.' '. qsa('http://'.$ip.'/')
		.' --referer='. qsa('http://'.$prov_host.'/')
		.' -U '. qsa('')
		.' --no-http-keep-alive'
		.' --header='. qsa('Connection: Close')
		.' --header='. qsa('Host: '. $ip)
		.' --header='. qsa('Content-Type: text/xml; charset=utf-8')
		# Content-Type: text/xml is wrong because "xml=..." is not XML,
		# but that's how the Aastra wants it.
		.' --header='. qsa('Content-Length: '. (strLen('xml=') + strLen($xmlpi) + strLen($xml)))
		.' --post-data '. qsa('xml='. $xmlpi . $xml)
		.' >>/dev/null 2>>/dev/null &'
		;
	unset($xml);
	unset($xmlpi);
	$err=0; $out=array();
	@ exec( $cmd, $out, $err );
	unset($cmd);
	return ($err == 0);
}


// PRIVATE:
function _gs_prov_phone_checkcfg_by_ext_do( $ext, $reboot=true )
{
	if (! preg_match( '/^[\d]+$/', $ext ))
		return new GsError( 'Extension must be numeric.' );
	
	gs_log(GS_LOG_DEBUG, "do phone_checkcfg by ext \"$ext\"");
	
	/*
	$db = @gs_db_slave_connect();
	if (! $db) {
		gs_log(GS_LOG_WARNING, 'Failed to connect to DB');
		return false;
	}
	$phone_type = strToLower( (string)@$db->executeGetOne(
'SELECT `p`.`type`
FROM
	`ast_sipfriends` `s` JOIN
	`phones` `p` ON (`p`.`user_id`=`s`.`_user_id`)
WHERE
	`s`.`name`=\''. $db->escape($ext) .'\''
	));  # remember ast_sipfriends.name is unique
	
	if       (subStr($phone_type,0,4)==='snom') {
		gs_log(GS_LOG_DEBUG, "do phone_checkcfg by ext \"$ext\" (snom)");
		_gs_prov_phone_checkcfg_by_ext_do_snom   ( $ext, $reboot );
	}
	elseif (subStr($phone_type,0,7)==='siemens') {
		gs_log(GS_LOG_DEBUG, "do phone_checkcfg by ext \"$ext\" (siemens)");
		_gs_prov_phone_checkcfg_by_ext_do_siemens( $ext, $reboot );
	}
	else {
		# we don't know the type of that phone, just try everything
		gs_log(GS_LOG_NOTICE, "Not sure how to sync phone of type \"$phone_type\"");
		gs_log(GS_LOG_DEBUG, "do phone_checkcfg by ext \"$ext\" (unknown phone type)");
		_gs_prov_phone_checkcfg_by_ext_do_snom   ( $ext, $reboot );
		_gs_prov_phone_checkcfg_by_ext_do_siemens( $ext, $reboot );
	}
	*/
	// damn - we have already removed the user id from the phones table
	
	if (gs_get_conf('GS_SNOM_PROV_ENABLED')) {
		_gs_prov_phone_checkcfg_by_ext_do_snom   ( $ext, $reboot );
	}
	if (gs_get_conf('GS_SNOM_PROV_M3_ACCOUNTS')) {
		_gs_prov_phone_checkcfg_by_ext_do_snom_m3( $ext, $reboot );
	}
	if (gs_get_conf('GS_SIEMENS_PROV_ENABLED')) {
		_gs_prov_phone_checkcfg_by_ext_do_siemens( $ext, $reboot );
	}
	if (gs_get_conf('GS_AASTRA_PROV_ENABLED')) {
		_gs_prov_phone_checkcfg_by_ext_do_aastra ( $ext, $reboot );
	}
	
	//return $err == 0;
	return true;
}

// REALLY PRIVATE! CAREFUL WITH PARAMS - NO VALIDATION!
function _gs_prov_phone_checkcfg_by_ext_do_snom( $ext, $reboot=true )
{
	$sip_notify = $reboot ? 'snom-reboot' : 'snom-check-cfg';
	@exec( 'sudo asterisk -rx \'sip notify '. $sip_notify .' '. $ext .'\' >>/dev/null 2>>/dev/null &', $out, $err );
	
	$hosts = @gs_hosts_get(false);
	if (isGsError($hosts)) {
		gs_log(GS_LOG_WARNING, 'Failed to get hosts - '. $hosts->getMsg());
	} elseif (! is_array($hosts)) {
		gs_log(GS_LOG_WARNING, 'Failed to get hosts');
	} else {
		$cmd = 'asterisk -rx \'sip notify '. $sip_notify .' '. $ext .'\'';
		foreach ($hosts as $host) {
			@exec( 'sudo ssh -o StrictHostKeyChecking=no -o BatchMode=yes -l root '. qsa($host['host']) .' '. qsa($cmd) .' >>/dev/null 2>>/dev/null &' );
		}
	}
}

// REALLY PRIVATE! CAREFUL WITH PARAMS - NO VALIDATION!
function _gs_prov_phone_checkcfg_by_ext_do_snom_m3( $ext, $reboot=true )
{
	# We will run into trouble if the IP addr. is not in the database anymore.
	# see _gs_prov_phone_checkcfg_by_ext_do_siemens()
	
	$db = @gs_db_slave_connect();
	if (! $db) {
		gs_log(GS_LOG_WARNING, 'Failed to connect to DB');
		return;
	}
	$ip = @$db->executeGetOne(
'SELECT `u`.`current_ip`
FROM
	`ast_sipfriends` `s` JOIN
	`users` `u` ON (`u`.`id`=`s`.`_user_id`)
WHERE `s`.`name`=\''. $db->escape($ext) .'\''
	);
	
	if (! $ip || ! preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip)) {
		gs_log(GS_LOG_WARNING, 'Bad IP');
		return;
	}
	
	_gs_prov_phone_checkcfg_by_ip_do_snom_m3( $ip, $reboot, 2 );
}

// REALLY PRIVATE! CAREFUL WITH PARAMS - NO VALIDATION!
function _gs_prov_phone_checkcfg_by_ext_do_siemens( $ext, $reboot=true )
{
	# not implemented for Siemens phones
	# possible? the public docs indicate that it is possible
	#
	# This should send a SIP NOTIFY. Don't be clever and do a database
	# lookup from ext to IP. That would result in 2 ContactMe requests in
	# a *very* short time. See _gs_prov_phone_checkcfg_by_ip_do_siemens()
	# for a description of why that is bad.
	
	# edit: Sorry, we don't really have a choice here. If the phone
	# should miss the checkcfg_by_ip we would never be able to sync
	# the phone again because we have already deleted the last known
	# IP address
	
	$db = @gs_db_slave_connect();
	if (! $db) {
		gs_log(GS_LOG_WARNING, 'Failed to connect to DB');
		return;
	}
	$ip = @$db->executeGetOne(
'SELECT `u`.`current_ip`
FROM
	`ast_sipfriends` `s` JOIN
	`users` `u` ON (`u`.`id`=`s`.`_user_id`)
WHERE `s`.`name`=\''. $db->escape($ext) .'\''
	);
	if (! $ip || ! preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip)) {
		gs_log(GS_LOG_WARNING, 'Bad IP');
		return;
	}
	
	_gs_prov_phone_checkcfg_by_ip_do_siemens( $ip, $reboot, 2 );
}

function _gs_prov_phone_checkcfg_by_ext_do_aastra( $ext, $reboot=true )
{
	# We will run into trouble if the IP addr. is not in the database anymore.
	# see _gs_prov_phone_checkcfg_by_ext_do_siemens()
	
	$db = @gs_db_slave_connect();
	if (! $db) {
		gs_log(GS_LOG_WARNING, 'Failed to connect to DB');
		return;
	}
	$ip = @$db->executeGetOne(
'SELECT `u`.`current_ip`
FROM
	`ast_sipfriends` `s` JOIN
	`users` `u` ON (`u`.`id`=`s`.`_user_id`)
WHERE `s`.`name`=\''. $db->escape($ext) .'\''
	);
	if (! $ip || ! preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip)) {
		gs_log(GS_LOG_WARNING, 'Bad IP');
		return;
	}
	
	_gs_prov_phone_checkcfg_by_ip_do_aastra( $ip, $reboot, 2 );
}

?>