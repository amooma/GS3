<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
*
* $Revision$
*
* Copyright 2007-2010, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
* 
* Author: Daniel Scheller <scheller@loca.net>
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

define("GS_VALID", true);  /// this is a parent file

header("Content-Type: text/plain; charset=utf-8");
header("Expires: 0");
header("Pragma: no-cache");
header("Cache-Control: private, no-cache, must-revalidate");
header("Vary: *");

require_once(dirname(__FILE__) ."/../../../inc/conf.php");
require_once(GS_DIR ."inc/util.php");
require_once(GS_DIR ."inc/gs-lib.php");
require_once(GS_DIR ."inc/prov-fns.php");
require_once(GS_DIR ."inc/quote_shell_arg.php");
require_once(GS_DIR ."inc/langhelper.php");
require_once(GS_DIR ."inc/db_connect.php");
require_once(GS_DIR ."inc/nobody-extensions.php");
include_once(GS_DIR ."inc/gs-fns/gs_prov_params_get.php");
include_once(GS_DIR ."inc/gs-fns/gs_user_prov_params_get.php");

set_error_handler("err_handler_die_on_err");

//---------------------------------------------------------------------------

function _polycom_astlang_to_polycomlang($langcode)
{
	$lang_default = "German_Germany";

	$lang_transtable = Array(
		"de" => "German_Germany",
		"en" => "English_United_Kingdom",
		"us" => "English_United_States",
	);

	$lang_ret = $lang_transtable[$langcode];
	if(strlen($lang_ret) == 0)
		return $lang_default;

	return $lang_ret;
}

//---------------------------------------------------------------------------

function _settings_err($msg="")
{
	@ob_end_clean();
	@ob_start();

	echo "<!-- // ", ($msg != "" ? str_replace("--","- -",$msg) : "Error") ," // -->\n";
	if (!headers_sent())
	{
		header("Content-Type: text/plain; charset=utf-8");
		header("Content-Length: ". (int)@ob_get_length());
	}

	@ob_end_flush();
	exit(1);
}

//---------------------------------------------------------------------------

if (! gs_get_conf('GS_POLYCOM_PROV_ENABLED') )
{
	gs_log(GS_LOG_DEBUG, 'Polycom provisioning not enabled');
	_settings_err('Not enabled.');
}

$requester = gs_prov_check_trust_requester();
if (! $requester['allowed'] )
{
	_settings_err('No! See log for details.');
}

//--- identify polycom phone

$mac = preg_replace('/[^0-9A-F]/', '', strtoupper(@$_REQUEST['mac']));
if ( strlen($mac) !== 12 )
{
	gs_log(GS_LOG_NOTICE, 'Polycom provisioning: Invalid MAC address \"$mac\" (wrong length)');
	//--- don't explain this to the users
	_settings_err('No! See log for details.');
}
if ( hexdec(substr($mac, 0, 2)) % 2 == 1 )
{
	gs_log(GS_LOG_NOTICE, 'Polycom provisioning: Invalid MAC address \"$mac\" (multicast address)');
	//--- don't explain this to the users
	_settings_err('No! See log for details.');
}
if ( $mac === '000000000000' )
{
	gs_log(GS_LOG_NOTICE, 'Polycom provisioning: Invalid MAC address \"$mac\" (huh?)');
	//--- don't explain this to the users
	_settings_err('No! See log for details.');
}

//--- make sure the phone is a Polycom

if ( substr($mac, 0, 6) !== '0004F2' )
{
	gs_log(GS_LOG_NOTICE, 'Polycom provisioning: MAC address \"$mac\" is not a Polycom phone');
	//--- don't explain this to the users
	_settings_err('No! See log for details.');
}

//--- debug
//$ua = 'FileTransport PolycomSoundPointIP-SPIP_601-UA/3.1.2.0392';
//$ua = 'FileTransport PolycomSoundPointIP-SPIP_501-UA/3.1.2.0392';

$ua = trim(@$_SERVER['HTTP_USER_AGENT']);
if ( preg_match('/PolycomSoundPointIP/', $ua) )
{
	$phone_model = ((preg_match('/PolycomSoundPointIP\-SPIP_(\d+)\-UA\//', $ua, $m)) ? $m[1] : 'unknown');
	$phone_type = 'polycom-spip-'. $phone_model;
	$fw_vers = ((preg_match('/PolycomSoundPointIP\-SPIP_\d+\-UA\/(.*)/', $ua, $m)) ? $m[1] : '0.0.0.000');
}
else if ( preg_match('/PolycomSoundStationIP/', $ua) )
{
	$phone_model = ((preg_match('/PolycomSoundStationIP\-SSIP_(\d+)\-UA\//', $ua, $m)) ? $m[1] : 'unknown');
	$phone_type = 'polycom-ssip-'. $phone_model;
	$fw_vers = ((preg_match('/PolycomSoundStationIP\-SPIP_\d+\-UA\/(.*)/', $ua, $m)) ? $m[1] : '0.0.0.000');
}
else
{
	gs_log(GS_LOG_WARNING, 'Phone with MAC \"$mac\" (Polycom) has invalid User-Agent (\"'. $ua .'\")');
	//--- don't explain this to the users
	_settings_err('No! See log for details.');
}

gs_log(GS_LOG_DEBUG, 'Polycom phone \"'. $mac .'\" asks for settings (UA: ...\"'. $ua .'\") - model '. $phone_model);
$prov_url_polycom = GS_PROV_SCHEME .'://'. GS_PROV_HOST .(GS_PROV_PORT ? ':'. GS_PROV_PORT : ''). GS_PROV_PATH .'polycom/';

$db = gs_db_master_connect();
if( !$db )
{
	gs_log(GS_LOG_WARNING, 'Polycom phone asks for settings - Could not connect to DB');
	_settings_err('Could not connect to DB.');
}

//--- do we know the phone?

$user_id = @gs_prov_user_id_by_mac_addr($db, $mac);
if ( $user_id < 1 )
{
	if (! GS_PROV_AUTO_ADD_PHONE )
	{
		gs_log(GS_LOG_NOTICE, 'New phone '. $mac .' not added to DB. Enable PROV_AUTO_ADD_PHONE');
		_settings_err('Unknown phone. (Enable PROV_AUTO_ADD_PHONE in order to auto-add)');
	}
	gs_log(GS_LOG_NOTICE, 'Adding new Polycom phone '. $mac .' to DB');

	$user_id = @gs_prov_add_phone_get_nobody_user_id($db, $mac, $phone_type, $requester['phone_ip']);
	if ($user_id < 1)
	{
		gs_log(GS_LOG_WARNING, 'Failed to add nobody user for new phone '. $mac);
		_settings_err('Failed to add nobody user for new phone.');
	}
}

//--- is it a valid user id?

$num = (int) $db->executeGetOne("SELECT COUNT(*) FROM `users` WHERE `id`=". $user_id);
if ($num < 1)
	$user_id = 0;

if ($user_id < 1)
{
	//--- something bad happened, nobody (not even a nobody user) is logged
	//--- in at that phone. assign the default nobody user of the phone:
	$user_id = @gs_prov_assign_default_nobody($db, $mac, null);
	if ($user_id < 1)
		_settings_err('Failed to assign nobody account to phone '. $mac);
}

//--- get host for user

$host = @gs_prov_get_host_for_user_id($db, $user_id);
if (!$host)
	_settings_err('Failed to find host.');

$pbx = $host; //--- $host might be changed if SBC configured

//--- who is logged in at that phone?

$user = @gs_prov_get_user_info($db, $user_id);
if (! is_array($user) )
	_settings_err('DB error.');

//--- get polycom'ized language string from user lang pref
$user_polycomlang = @_polycom_astlang_to_polycomlang($user['language']);

//--- store the current phonetype and firmware version in the database:
@$db->execute(
	"UPDATE `phones` SET ".
		"`firmware_cur`='". $db->escape($fw_vers) ."', ".
		"`type`='". $db->escape($phone_type) ."' ".
	"WHERE `mac_addr`='". $db->escape($mac) ."'");

//--- store the user's current IP address in the database:

if (! @gs_prov_update_user_ip($db, $user_id, $requester['phone_ip']) )
	gs_log(GS_LOG_WARNING, 'Failed to store current IP addr of user ID '. $user_id);

//--- get callwaiting state
$callwaiting = (int) $db->executeGetOne('SELECT `active` FROM `callwaiting` WHERE `user_id`='. $user_id);

//--- get SIP proxy to be set as the phone's outbound proxy

$sip_proxy_and_sbc = gs_prov_get_wan_outbound_proxy($db, $requester['phone_ip'], $user_id);
if ( $sip_proxy_and_sbc['sip_server_from_wan'] != '' )
	$host = $sip_proxy_and_sbc['sip_server_from_wan'];

//--- get extension without route prefix

if ( gs_get_conf('GS_BOI_ENABLED') )
{
	$hp_route_prefix = (string)$db->executeGetOne(
		"SELECT `value` FROM `host_params` ".
		"WHERE ".
			"`host_id`=". (int)$user["host_id"] ." AND ".
			"`param`=\'route_prefix\'");
	$user_ext = (substr($user["name"], 0, strlen($hp_route_prefix)) === $hp_route_prefix)
		? substr($user["name"], strlen($hp_route_prefix)) : $user["name"];
	gs_log(GS_LOG_DEBUG, "Mapping ext. ". $user["name"] ." to ". $user_ext ." for provisioning - route_prefix: ". $hp_route_prefix .", host id: ". $user["host_id"]);
}
else
{
	$hp_route_prefix = '';
	$user_ext = $user['name'];
}

//--- check if this phone has the XHTML microbrowser and prepare vars
//--- and phone features accordingly. E.g. IP300 and IP500 don't have a
//--- firmware with microbrowser capabilities.

switch ($phone_model)
{
	case '300' :
	case '500' :
		$phone_has_microbrowser = FALSE;
		$phone_use_internal_divertion = '1';
		break;
	default :
		$phone_has_microbrowser = TRUE;
		$phone_use_internal_divertion = '0';
		break;
}

//--- print configuration

echo "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n";
echo "<phone1>\n";
echo "   <reg ";

$proxy_or_host = ($sip_proxy_and_sbc["sip_proxy_from_wan"] != "" ? $sip_proxy_and_sbc["sip_proxy_from_wan"] : $host);
$user_address = $user_ext ."@". $proxy_or_host;

echo "reg.1.displayName=\"".$user["firstname"] ." ".$user["lastname"] ."\" ";
echo "reg.1.address=\"". $user_address ."\" ";
echo "reg.1.label=\"". $user_ext ."\" ";
echo "reg.1.type=\"private\" ";
echo "reg.1.lcs=\"0\" ";
echo "reg.1.csta=\"\" ";
echo "reg.1.thirdPartyName=\"". $user_address ."\" ";
echo "reg.1.auth.userId=\"". $user_ext ."\" ";
echo "reg.1.auth.password=\"". $user["secret"] ."\" ";
echo "reg.1.auth.optimizedInFailover=\"\" ";
echo "reg.1.musicOnHold.uri=\"\" ";
echo "reg.1.server.1.address=\"". $host ."\" ";
echo "reg.1.server.1.port=\"5060\" ";
echo "reg.1.server.1.transport=\"UDPOnly\" ";
echo "reg.1.server.1.expires=\"3600\" ";
echo "reg.1.server.1.expires.overlap=\"1800\" ";
echo "reg.1.server.1.register=\"1\" ";
echo "reg.1.server.1.retryTimeOut=\"\" ";
echo "reg.1.server.1.retryMaxCount=\"\" ";
echo "reg.1.server.1.expires.lineSeize=\"\" ";
echo "reg.1.server.1.lcs=\"0\" ";
echo "reg.1.outboundProxy.address=\"". $proxy_or_host ."\" ";
echo "reg.1.outboundProxy.port=\"5060\" ";
echo "reg.1.outboundProxy.transport=\"UDPOnly\" ";
echo "reg.1.acd-login-logout=\"0\" ";
echo "reg.1.acd-agent-available=\"0\" ";
echo "reg.1.proxyRequire=\"\" ";
echo "reg.1.ringType=\"2\" ";
echo "reg.1.lineKeys=\"\" ";
echo "reg.1.callsPerLineKey=\"8\" ";
echo "reg.1.bargeInEnabled=\"\" ";
echo "reg.1.serverFeatureControl.dnd=\"1\" ";
echo "reg.1.serverFeatureControl.cf=\"1\" ";
echo "reg.1.strictLineSeize=\"\" ";

?>reg.2.displayName="" reg.2.address="" reg.2.label="" reg.2.type="private" reg.2.lcs="" reg.2.csta="" reg.2.thirdPartyName="" reg.2.auth.userId="" reg.2.auth.password="" reg.2.auth.optimizedInFailover="" reg.2.server.1.address="" reg.2.server.1.port="" reg.2.server.1.transport="DNSnaptr" reg.2.server.2.transport="DNSnaptr" reg.2.server.1.expires="" reg.2.server.1.expires.overlap="" reg.2.server.1.register="" reg.2.server.1.retryTimeOut="" reg.2.server.1.retryMaxCount="" reg.2.server.1.expires.lineSeize="" reg.2.outboundProxy.address="" reg.2.outboundProxy.port="" reg.2.outboundProxy.transport="" reg.2.acd-login-logout="0" reg.2.acd-agent-available="0" reg.2.proxyRequire="" reg.2.ringType="2" reg.2.lineKeys="" reg.2.callsPerLineKey="" reg.2.bargeInEnabled="" reg.2.serverFeatureControl.dnd="" reg.2.serverFeatureControl.cf="" reg.2.strictLineSeize="" reg.3.displayName="" reg.3.address="" reg.3.label="" reg.3.type="private" reg.3.lcs="" reg.3.csta="" reg.3.thirdPartyName="" reg.3.auth.userId="" reg.3.auth.password="" reg.3.auth.optimizedInFailover="" reg.3.server.1.address="" reg.3.server.1.port="" reg.3.server.1.transport="DNSnaptr" reg.3.server.2.transport="DNSnaptr" reg.3.server.1.expires="" reg.3.server.1.expires.overlap="" reg.3.server.1.register="" reg.3.server.1.retryTimeOut="" reg.3.server.1.retryMaxCount="" reg.3.server.1.expires.lineSeize="" reg.3.outboundProxy.address="" reg.3.outboundProxy.port="" reg.3.outboundProxy.transport="" reg.3.acd-login-logout="0" reg.3.acd-agent-available="0" reg.3.proxyRequire="" reg.3.ringType="2" reg.3.lineKeys="" reg.3.callsPerLineKey="" reg.3.bargeInEnabled="" reg.3.serverFeatureControl.dnd="" reg.3.serverFeatureControl.cf="" reg.3.strictLineSeize="" reg.4.displayName="" reg.4.address="" reg.4.label="" reg.4.type="private" reg.4.lcs="" reg.4.csta="" reg.4.thirdPartyName="" reg.4.auth.userId="" reg.4.auth.password="" reg.4.auth.optimizedInFailover="" reg.4.server.1.address="" reg.4.server.1.port="" reg.4.server.1.transport="DNSnaptr" reg.4.server.2.transport="DNSnaptr" reg.4.server.1.expires="" reg.4.server.1.expires.overlap="" reg.4.server.1.register="" reg.4.server.1.retryTimeOut="" reg.4.server.1.retryMaxCount="" reg.4.server.1.expires.lineSeize="" reg.4.outboundProxy.address="" reg.4.outboundProxy.port="" reg.4.outboundProxy.transport="" reg.4.acd-login-logout="0" reg.4.acd-agent-available="0" reg.4.proxyRequire="" reg.4.ringType="2" reg.4.lineKeys="" reg.4.callsPerLineKey="" reg.4.bargeInEnabled="" reg.4.serverFeatureControl.dnd="" reg.4.serverFeatureControl.cf="" reg.4.strictLineSeize="" reg.5.displayName="" reg.5.address="" reg.5.label="" reg.5.type="private" reg.5.lcs="" reg.5.csta="" reg.5.thirdPartyName="" reg.5.auth.userId="" reg.5.auth.password="" reg.5.auth.optimizedInFailover="" reg.5.server.1.address="" reg.5.server.1.port="" reg.5.server.1.transport="DNSnaptr" reg.5.server.2.transport="DNSnaptr" reg.5.server.1.expires="" reg.5.server.1.expires.overlap="" reg.5.server.1.register="" reg.5.server.1.retryTimeOut="" reg.5.server.1.retryMaxCount="" reg.5.server.1.expires.lineSeize="" reg.5.outboundProxy.address="" reg.5.outboundProxy.port="" reg.5.outboundProxy.transport="" reg.5.acd-login-logout="0" reg.5.acd-agent-available="0" reg.5.proxyRequire="" reg.5.ringType="2" reg.5.lineKeys="" reg.5.callsPerLineKey="" reg.5.bargeInEnabled="" reg.5.serverFeatureControl.dnd="" reg.5.serverFeatureControl.cf="" reg.5.strictLineSeize="" reg.6.displayName="" reg.6.address="" reg.6.label="" reg.6.type="private" reg.6.lcs="" reg.6.csta="" reg.6.thirdPartyName="" reg.6.auth.userId="" reg.6.auth.password="" reg.6.auth.optimizedInFailover="" reg.6.server.1.address="" reg.6.server.1.port="" reg.6.server.1.transport="DNSnaptr" reg.6.server.2.transport="DNSnaptr" reg.6.server.1.expires="" reg.6.server.1.expires.overlap="" reg.6.server.1.register="" reg.6.server.1.retryTimeOut="" reg.6.server.1.retryMaxCount="" reg.6.server.1.expires.lineSeize="" reg.6.outboundProxy.address="" reg.6.outboundProxy.port="" reg.6.outboundProxy.transport="" reg.6.acd-login-logout="0" reg.6.acd-agent-available="0" reg.6.proxyRequire="" reg.6.ringType="2" reg.6.lineKeys="" reg.6.callsPerLineKey="" reg.6.bargeInEnabled="" reg.6.serverFeatureControl.dnd="" reg.6.serverFeatureControl.cf="" reg.6.strictLineSeize=""/>\n";
   <call>
      <donotdisturb call.donotdisturb.perReg="0"/>
      <autoOffHook call.autoOffHook.1.enabled="0" call.autoOffHook.1.contact="" call.autoOffHook.2.enabled="0" call.autoOffHook.2.contact="" call.autoOffHook.3.enabled="0" call.autoOffHook.3.contact="" call.autoOffHook.4.enabled="0" call.autoOffHook.4.contact="" call.autoOffHook.5.enabled="0" call.autoOffHook.5.contact="" call.autoOffHook.6.enabled="0" call.autoOffHook.6.contact=""/>
      <missedCallTracking call.missedCallTracking.1.enabled="1" call.missedCallTracking.2.enabled="1" call.missedCallTracking.3.enabled="1" call.missedCallTracking.4.enabled="1" call.missedCallTracking.5.enabled="1" call.missedCallTracking.6.enabled="1"/>
      <serverMissedCall call.serverMissedCall.1.enabled="0" call.serverMissedCall.2.enabled="0" call.serverMissedCall.3.enabled="0" call.serverMissedCall.4.enabled="0" call.serverMissedCall.5.enabled="0" call.serverMissedCall.6.enabled="0"/>
      <callWaiting call.callWaiting.enabled="1" call.callWaiting.ring="beep"/>
   </call>
   <divert divert.1.contact="" divert.1.autoOnSpecificCaller="1" divert.1.sharedDisabled="1" divert.2.contact="" divert.2.autoOnSpecificCaller="1" divert.2.sharedDisabled="1" divert.3.contact="" divert.3.autoOnSpecificCaller="1" divert.3.sharedDisabled="1" divert.4.contact="" divert.4.autoOnSpecificCaller="1" divert.4.sharedDisabled="1" divert.5.contact="" divert.5.autoOnSpecificCaller="1" divert.5.sharedDisabled="1" divert.6.contact="" divert.6.autoOnSpecificCaller="1" divert.6.sharedDisabled="1">
      <fwd divert.fwd.1.enabled="0" divert.fwd.2.enabled="0" divert.fwd.3.enabled="0" divert.fwd.4.enabled="0" divert.fwd.5.enabled="0" divert.fwd.6.enabled="0"/>
      <busy divert.busy.1.enabled="0" divert.busy.1.contact="" divert.busy.2.enabled="0" divert.busy.2.contact="" divert.busy.3.enabled="0" divert.busy.3.contact="" divert.busy.4.enabled="0" divert.busy.4.contact="" divert.busy.5.enabled="0" divert.busy.5.contact="" divert.busy.6.enabled="0" divert.busy.6.contact=""/>
      <noanswer divert.noanswer.1.enabled="0" divert.noanswer.1.timeout="60" divert.noanswer.1.contact="" divert.noanswer.2.enabled="0" divert.noanswer.2.timeout="60" divert.noanswer.2.contact="" divert.noanswer.3.enabled="0" divert.noanswer.3.timeout="60" divert.noanswer.3.contact="" divert.noanswer.4.enabled="0" divert.noanswer.4.timeout="60" divert.noanswer.4.contact="" divert.noanswer.5.enabled="0" divert.noanswer.5.timeout="60" divert.noanswer.5.contact="" divert.noanswer.6.enabled="0" divert.noanswer.6.timeout="60" divert.noanswer.6.contact=""/>
      <dnd divert.dnd.1.enabled="0" divert.dnd.1.contact="" divert.dnd.2.enabled="0" divert.dnd.2.contact="" divert.dnd.3.enabled="0" divert.dnd.3.contact="" divert.dnd.4.enabled="0" divert.dnd.4.contact="" divert.dnd.5.enabled="0" divert.dnd.5.contact="" divert.dnd.6.enabled="0" divert.dnd.6.contact=""/>
   </divert>
   <dialplan dialplan.1.impossibleMatchHandling="0" dialplan.1.removeEndOfDial="0" dialplan.1.applyToUserSend="1" dialplan.1.applyToUserDial="1" dialplan.1.applyToCallListDial="0" dialplan.1.applyToDirectoryDial="0" dialplan.2.impossibleMatchHandling="0" dialplan.2.removeEndOfDial="0" dialplan.2.applyToUserSend="1" dialplan.2.applyToUserDial="1" dialplan.2.applyToCallListDial="0" dialplan.2.applyToDirectoryDial="0" dialplan.3.impossibleMatchHandling="0" dialplan.3.removeEndOfDial="0" dialplan.3.applyToUserSend="1" dialplan.3.applyToUserDial="1" dialplan.3.applyToCallListDial="0" dialplan.3.applyToDirectoryDial="0" dialplan.4.impossibleMatchHandling="0" dialplan.4.removeEndOfDial="0" dialplan.4.applyToUserSend="1" dialplan.4.applyToUserDial="1" dialplan.4.applyToCallListDial="0" dialplan.4.applyToDirectoryDial="0" dialplan.5.impossibleMatchHandling="0" dialplan.5.removeEndOfDial="0" dialplan.5.applyToUserSend="1" dialplan.5.applyToUserDial="1" dialplan.5.applyToCallListDial="0" dialplan.5.applyToDirectoryDial="0" dialplan.6.impossibleMatchHandling="0" dialplan.6.removeEndOfDial="0" dialplan.6.applyToUserSend="1" dialplan.6.applyToUserDial="1" dialplan.6.applyToCallListDial="0" dialplan.6.applyToDirectoryDial="0">
      <digitmap dialplan.1.digitmap="" dialplan.1.digitmap.timeOut="" dialplan.2.digitmap="" dialplan.2.digitmap.timeOut="" dialplan.3.digitmap="" dialplan.3.digitmap.timeOut="" dialplan.4.digitmap="" dialplan.4.digitmap.timeOut="" dialplan.5.digitmap="" dialplan.5.digitmap.timeOut="" dialplan.6.digitmap="" dialplan.6.digitmap.timeOut=""/>
      <routing>
         <server dialplan.1.routing.server.1.address="" dialplan.1.routing.server.1.port="" dialplan.2.routing.server.1.address="" dialplan.2.routing.server.1.port="" dialplan.3.routing.server.1.address="" dialplan.3.routing.server.1.port="" dialplan.4.routing.server.1.address="" dialplan.4.routing.server.1.port="" dialplan.5.routing.server.1.address="" dialplan.5.routing.server.1.port="" dialplan.6.routing.server.1.address="" dialplan.6.routing.server.1.port=""/>
         <emergency dialplan.1.routing.emergency.1.value="" dialplan.1.routing.emergency.1.server.1="" dialplan.2.routing.emergency.1.value="" dialplan.2.routing.emergency.1.server.1="" dialplan.3.routing.emergency.1.value="" dialplan.3.routing.emergency.1.server.1="" dialplan.4.routing.emergency.1.value="" dialplan.4.routing.emergency.1.server.1="" dialplan.5.routing.emergency.1.value="" dialplan.5.routing.emergency.1.server.1="" dialplan.6.routing.emergency.1.value="" dialplan.6.routing.emergency.1.server.1=""/>
      </routing>
   </dialplan>
   <msg msg.bypassInstantMessage="1">
      <mwi msg.mwi.1.subscribe="" msg.mwi.1.callBackMode="contact" msg.mwi.1.callBack="mailbox" msg.mwi.2.subscribe="" msg.mwi.2.callBackMode="registration" msg.mwi.2.callBack="" msg.mwi.3.subscribe="" msg.mwi.3.callBackMode="registration" msg.mwi.3.callBack="" msg.mwi.4.subscribe="" msg.mwi.4.callBackMode="registration" msg.mwi.4.callBack="" msg.mwi.5.subscribe="" msg.mwi.5.callBackMode="registration" msg.mwi.5.callBack="" msg.mwi.6.subscribe="" msg.mwi.6.callBackMode="registration" msg.mwi.6.callBack=""/>
   </msg>
   <nat nat.ip="" nat.signalPort="" nat.mediaPortStart="" nat.keepalive.interval=""/>
   <attendant attendant.uri="" attendant.reg="" attendant.ringType=""/>
   <roaming_buddies roaming_buddies.reg=""/>
   <roaming_privacy roaming_privacy.reg=""/>
   <user_preferences up.analogHeadsetOption="0" up.offHookAction.none="" up.pictureFrame.folder="" up.pictureFrame.timePerImage="" up.screenSaver.enabled="0" up.screenSaver.waitTime=""/>
   <acd acd.reg="" acd.stateAtSignIn=""/>
</phone1>
<?php

//--- generate language preference
$sipapp_langsettings = "";

$sipapp_langsettings .= "  <localization>\n";
$sipapp_langsettings .= "    <multilingual>\n";
$sipapp_langsettings .= "      <language lcl.ml.lang=\"". $user_polycomlang ."\"/>\n";
$sipapp_langsettings .= "    </multilingual>\n";
$sipapp_langsettings .= "  </localization>\n";

//--- configuration for xhtml browser and key remappings on phones where
//--- the feature is supported

if ($phone_has_microbrowser)
{

//--- efk configuration - provide phone macros for url handling

	echo "<efk>\n";
	echo "   <version efk.version=\"1\"/>\n";
	echo "   <efklist";
	echo " efk.efklist.1.mname=\"gsdiallog\" efk.efklist.1.status=\"1\" efk.efklist.1.action.string=\"". $prov_url_polycom ."diallog.php?user=". $user_ext ."&amp;mac=". $mac ."\"";
	echo " efk.efklist.2.mname=\"gsphonebook\" efk.efklist.2.status=\"1\" efk.efklist.2.action.string=\"". $prov_url_polycom ."pb.php?m=". $mac ."&amp;u=". $user_ext ."\"";
	echo " efk.efklist.3.mname=\"gsdnd\" efk.efklist.3.status=\"1\" efk.efklist.3.action.string=\"". $prov_url_polycom ."dnd.php?m=". $mac ."&amp;u=". $user_ext ."\"";
	echo " efk.efklist.4.mname=\"gsmenu\" efk.efklist.4.status=\"1\" efk.efklist.4.action.string=\"". $prov_url_polycom ."configmenu.php?m=". $mac ."&amp;u=". $user_ext ."\"";
	echo " efk.efklist.5.mname=\"gsmainmenu\" efk.efklist.5.status=\"1\" efk.efklist.5.action.string=\"". $prov_url_polycom ."main.php?mac=". $mac ."&amp;user=". $user_ext ."\"";

	echo " />\n";
	echo "</efk>\n";

	echo "<sip>\n";
	echo "   <keys key.scrolling.timeout=\"1\"";

	//--- key remappings for SoundPoint IP 301
	//--- 23 = DND key
	echo " key.IP_300.23.function.prim=\"SpeedDial\" key.IP_300.23.subPoint.prim=\"3\"";

	//--- key remappings for SoundPoint IP 501
	//--- 30 = 'Call Lists' key, 32 = 'Directories' key, 9 = DND key
	echo " key.IP_500.30.function.prim=\"SpeedDial\" key.IP_500.30.subPoint.prim=\"1\"";
	echo " key.IP_500.32.function.prim=\"SpeedDial\" key.IP_500.32.subPoint.prim=\"2\"";
	echo " key.IP_500.9.function.prim=\"SpeedDial\" key.IP_500.9.subPoint.prim=\"3\"";

	//--- key remappings for SoundPoint IP 600 and 601
	//--- 30 = 'Directories' key
	echo " key.IP_600.30.function.prim=\"SpeedDial\" key.IP_600.30.subPoint.prim=\"2\"";
	echo " key.IP_600.9.function.prim=\"SpeedDial\" key.IP_600.9.subPoint.prim=\"3\"";

	//--- key remappings for SoundPoint IP 650 and 670
	//--- 30 = 'Directories' key
	echo " key.IP_650.30.function.prim=\"SpeedDial\" key.IP_650.30.subPoint.prim=\"2\"";
	echo " key.IP_650.9.function.prim=\"SpeedDial\" key.IP_650.9.subPoint.prim=\"3\"";

	//--- end of remappings

	echo "/>\n";

	//--- softkey remapping
	echo "   <softkey ";
	echo "softkey.feature.forward=\"0\" ";
	if(($phone_model == "320") || ($phone_model == "321") || ($phone_model == "330") || ($phone_model == "331"))
	{
		echo "softkey.feature.directories=\"0\" ";
		echo "softkey.feature.callers=\"0\" ";

		echo "softkey.2.label=\"Menü\" ";
		echo "softkey.2.action=\"!gsmainmenu\" ";
		echo "softkey.2.enable=\"1\" ";
		echo "softkey.2.use.idle=\"1\" ";
	}
	echo "/>\n";

	//--- XHTML push message preparation
	echo "   <applications>\n";
	echo "      <push apps.push.messageType=\"0\" apps.push.serverRootURL=\"/push\" apps.push.username=\"" . gs_get_conf("GS_POLYCOM_PROV_HTTP_USER") . "\" apps.push.password=\"" . gs_get_conf("GS_POLYCOM_PROV_HTTP_PASS") . "\"/>\n";
	echo "   </applications>\n";

	//--- Microbrowser settings
	echo "   <microbrowser>\n";
	echo "      <main mb.main.home=\"". $prov_url_polycom ."main.php?user=". $user_ext ."&amp;mac=". $mac ."\" />\n";
	//echo "      <main mb.main.idleTimeout=\"60\" mb.main.statusbar=\"0\" mb.main.home=\"". $prov_url_polycom ."main.php?user=". $user_ext ."&amp;mac=". $mac ."\" />\n";
	//echo "      <idleDisplay mb.idleDisplay.home=\"". $prov_url_polycom ."idle.php?user=". $user_ext ."&amp;mac=". $mac ."\" mb.idleDisplay.refresh=\"10\"/>\n";
	echo "   </microbrowser>\n";

	//--- add language settings
	echo $sipapp_langsettings;

	//--- close sip application settings
	echo "</sip>\n";

} //--- Microbrowser settings
else
{ //--- non-microbrowser phones need at least the user's langpref
	echo "<sip>\n";
	echo $sipapp_langsettings;
	echo "</sip>\n";
}

?>
