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
	$is_server = (
			(bool)(int)$db->executeGetOne(
			'SELECT 1 '.
			'FROM `host_params` '.
			'WHERE '.
				'`param` IN (\'sip_proxy_from_wan\', \'sip_server_from_wan\') AND '.
				'`value`=\''. $db->escape($ip) .'\' '.
			'LIMIT 1'
			)
		||
			(bool)(int)$db->executeGetOne(
			'SELECT 1 '.
			'FROM `gates` '.
			'WHERE '.
				'`host`=\''. $db->escape($ip) .'\' '.
			'LIMIT 1'
			)
		);
	if ($is_server) {
		gs_log(GS_LOG_DEBUG, "IP addr. $ip is a server, not a phone");
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
	if (gs_get_conf('GS_GRANDSTREAM_PROV_ENABLED')) {
		_gs_prov_phone_checkcfg_by_ip_do_grandstream( $ip, $reboot );
	}
	if (gs_get_conf('GS_POLYCOM_PROV_ENABLED')) {
		_gs_prov_phone_checkcfg_by_ip_do_polycom( $ip, $reboot );
	}
	if (gs_get_conf('GS_TIPTEL_PROV_ENABLED')) {
		_gs_prov_phone_checkcfg_by_ip_do_tiptel( $ip, $reboot );
	}
	if (gs_get_conf('GS_YEALINK_PROV_ENABLED')) {
		_gs_prov_phone_checkcfg_by_ip_do_yealink( $ip, $reboot );
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
	@ exec( 'wget -O /dev/null -o /dev/null -b --tries=3 --timeout=8 --retry-connrefused -q --user='. qsa(gs_get_conf('GS_SNOM_PROV_M3_HTTP_USER','')) .' --password='. qsa(gs_get_conf('GS_SNOM_PROV_M3_HTTP_PASS','')) .' '. qsa('http://'. $ip .'/reboot.html') . ' >>/dev/null 2>>/dev/null &', $out, $err );
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

	$prov_url_aastra = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'aastra/';
	
	$xmlpi = '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n";
	$xml = '<AastraIPPhoneExecute>' ."\n";
	$xml.= '	<ExecuteItem URI="' . $prov_url_aastra . 'settings.php?dynamic=1" />' ."\n";
	$xml.= '	<ExecuteItem URI="' . $prov_url_aastra . 'expmod.php?module=1&amp;level=1" />' ."\n";
	$xml.= '	<ExecuteItem URI="' . $prov_url_aastra . 'expmod.php?module=2&amp;level=1" />' ."\n";
	$xml.= '	<ExecuteItem URI="' . $prov_url_aastra . 'expmod.php?module=3&amp;level=1" />' ."\n";
	$xml.= '	<ExecuteItem URI="' . $prov_url_aastra . 'expmod.php?module=1&amp;level=2" />' ."\n";
	$xml.= '	<ExecuteItem URI="' . $prov_url_aastra . 'expmod.php?module=2&amp;level=2" />' ."\n";
	$xml.= '	<ExecuteItem URI="' . $prov_url_aastra . 'expmod.php?module=3&amp;level=2" />' ."\n";
	$xml.= '	<ExecuteItem URI="' . $prov_url_aastra . 'expmod.php?module=1&amp;level=3" />' ."\n";
	$xml.= '	<ExecuteItem URI="' . $prov_url_aastra . 'expmod.php?module=2&amp;level=3" />' ."\n";
	$xml.= '	<ExecuteItem URI="' . $prov_url_aastra . 'expmod.php?module=3&amp;level=3" />' ."\n";
	$xml.= '</AastraIPPhoneExecute>' ."\n";
	
	$cmd = 'wget -O /dev/null -o /dev/null -b --tries=3 --timeout=8 --retry-connrefused -q'
		.' '. qsa('http://'.$ip.'/')
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

function _gs_prov_phone_checkcfg_by_ip_do_grandstream( $ip, $reboot=true )
{
	if (_gs_prov_phone_checkcfg_exclude_ip( $ip )) return;
	
	$db = @gs_db_slave_connect();
    if (! $db) {
            gs_log(GS_LOG_WARNING, 'Failed to connect to DB');
            return;
    }

    $type = @$db->executeGetOne('
            SELECT
                    `p`.`type`
            FROM
                    `users` `u`
            JOIN
                    `phones` `p` ON (`p`.`user_id` = `u`.`id`)
            WHERE
                    `u`.`current_ip` = \''. $db->escape($ip) .'\'
    ');

    $model = strtoupper( str_replace( 'grandstream-', '', $type ) );

    if ( preg_match('/^GXP21([0-9]{2})$/', $model) ) {
            reboot_grandstream21xx( $ip, $model );
            return;
    }

	@ exec( '/opt/gemeinschaft/sbin/gs-grandstream-reboot --ip='. qsa($ip) .' >>/dev/null 2>>/dev/null &', $out, $err );
}

function reboot_grandstream21xx($ip_addr, $model)
{
   $socket = fsockopen("$ip_addr","23", $errno, $errstr, 1000);
   if (!$socket) {
   //   echo "$errstr ($errno)<br />\n";
      exit;
   }
   read_up_to("Grandstream $model Command Shell Copyright 2011",$socket);
   fputs($socket, "admin\r\n");
   read_up_to("$model",$socket);
   fputs($socket, "reboot\r\n");
   read_up_to("Rebooting",$socket);
   fputs($socket, "exit\r\n");
   read_up_to("endlessssssssss",$socket);      //this is just to keep it until reboot command execute
   fclose($socket);
}

function read_up_to($string,$socket)
{
   $max_loop = 30;
   $resp = "";
   while (!feof($socket))
   {
      if ($max_loop<0)
         break;
      $max_loop--;
      $resp .= fgets($socket, 10);
     // echo $resp."<br>";
      if (strstr($resp,$string))
      {
       //  echo "found" . $resp . "<br>";
         break;
      }
   }
}

function _gs_prov_phone_checkcfg_by_ip_do_polycom( $ip, $reboot=true )
{
	if (_gs_prov_phone_checkcfg_exclude_ip( $ip )) return;

	//--- Rebooting by SIP message check-sync event is the only way to
	//--- force/trigger a reboot of Polycom phones, according to the
	//--- Polycom support - tested and works for any arbitrary source
	//--- host... At least this provides a (more or less) reliable
	//--- way if the phone got lost from it's registrar and a
	//--- "reboot by ext" is impossible. Note this will most likely
	//--- force the phone to reboot, $reboot is ignored.

	$srchost = "169.254.254.1";
	$dsthost = $ip;

	$srcext = "rebooter";
	$dstext = "polycom";

	$socket = @fsockopen("udp://".$dsthost, 5060, $errno, $errstr, 2);

	$message = "NOTIFY sip:". $dstext ."@". $dsthost .":5060 SIP/2.0\r\n"
	         . "Method: NOTIFY\r\n"
	         . "Resent Packet: False\r\n"
	         . "Via: SIP/2.0/UDP ". $srchost .":5060;branch=1\r\n"
	         . "Via: SIP/2.0/UDP ". $srchost ."\r\n"
	         . "From: <sip:". $srcext ."@". $srchost .":5060>\r\n"
	         . "SIP from address: sip:". $srcext ."@". $srchost .":5060\r\n"
	         . "To: <sip:". $dstext ."@". $dsthost .":5060>\r\n"
	         . "SIP to address: sip:". $dstext ."@". $dsthost .":5060\r\n"
	         . "Event: check-sync\r\n"
	         . "Date: ". strftime("%c %z") ."\r\n"
	         . "Call-ID: 1@". $srchost ."\r\n"
	         . "CSeq: 1300 NOTIFY\r\n"
	         . "Contact: <sip:". $srcext ."@". $srchost .">\r\n"
	         . "Contact Binding: <sip:". $srcext ."@". $srchost .">\r\n"
	         . "URI: <sip:". $srcext ."@". $srchost .">\r\n"
	         . "SIP contact address: sip:". $srcext ."@". $srchost ."\r\n"
	         . "Content-Length: 0\r\n"
	         . "\r\n";

	fwrite($socket, $message);
	fclose($socket);
}

function _gs_prov_phone_checkcfg_by_ip_do_tiptel( $ip, $reboot=true )
{
	if (_gs_prov_phone_checkcfg_exclude_ip( $ip )) return;
	
	@ exec( '/opt/gemeinschaft/sbin/gs-tiptel-reboot --ip='. qsa($ip) .' >>/dev/null 2>>/dev/null &', $out, $err );
}

function _gs_prov_phone_checkcfg_by_ip_do_yealink( $ip, $reboot=true )
{
	if (_gs_prov_phone_checkcfg_exclude_ip( $ip )) return;

	$url = 'http://' . qsa($ip) .'/servlet';

	//open connection
	$ch = curl_init();

	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_POST,3);
	curl_setopt($ch,CURLOPT_POSTFIELDS,'p=settings-autop&q=write&now=true');

	//execute post
	$result = curl_exec($ch);

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
	if (gs_get_conf('GS_GRANDSTREAM_PROV_ENABLED')) {
		_gs_prov_phone_checkcfg_by_ext_do_grandstream( $ext, $reboot );
	}
	if (gs_get_conf('GS_POLYCOM_PROV_ENABLED')) {
		_gs_prov_phone_checkcfg_by_ext_do_polycom( $ext, $reboot );
	}
	if (gs_get_conf('GS_TIPTEL_PROV_ENABLED')) {
		_gs_prov_phone_checkcfg_by_ext_do_tiptel( $ext, $reboot );
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

function _gs_prov_phone_checkcfg_by_ext_do_grandstream( $ext, $reboot=true )
{
	$db = @gs_db_slave_connect();
	if (! $db) {
	       gs_log(GS_LOG_WARNING, 'Failed to connect to DB');
	       return;
	}

	$ip = @$db->executeGetOne(
		'SELECT
		   `u`.`current_ip`
		FROM
		   `ast_sipfriends` `s`
		JOIN
		   `users` `u` ON (`u`.`id`=`s`.`_user_id`)
		WHERE `s`.`name`=\''. $db->escape($ext) .'\''
    );

    if (! $ip || ! preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip)) {
    	gs_log(GS_LOG_WARNING, 'Bad IP');
		return;
    }

    _gs_prov_phone_checkcfg_by_ip_do_grandstream( $ip, $reboot);
}

function _gs_prov_phone_checkcfg_by_ext_do_polycom( $ext, $reboot=true )
{
	$sip_notify = "polycom-check-cfg";
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

function _gs_prov_phone_checkcfg_by_ext_do_tiptel( $ext, $reboot=true )
{
	//FIXME
}

?>