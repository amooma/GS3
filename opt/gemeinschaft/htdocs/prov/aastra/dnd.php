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
* Author: Henning Holtschneider, LocaNet oHG <henning@loca.net>
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

define( 'GS_VALID', true );  /// this is a parent file
require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
include_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/aastra-fns.php' );
require_once( GS_DIR .'inc/gs-fns/gs_ami_events.php' );
require_once( GS_DIR .'inc/gs-fns/gs_user_phonemodel_get.php' );
include_once( GS_DIR .'inc/gettext.php' );
require_once(GS_DIR .'inc/group-fns.php');

$xml = '';

function _err( $msg='' )
{
	aastra_textscreen( 'Error', ($msg != '' ? $msg : 'Unknown error') );
	exit(1);
}

function _get_userid()
{
	global $_SERVER, $db;
	
	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$user_id = (int)$db->executeGetOne( 'SELECT `id` FROM `users` WHERE `current_ip`=\''. $db->escape($remote_addr) .'\'' );
	if ($user_id < 1) _err( 'Unknown user.' );
	return $user_id;
}

function _get_user( $user_id )
{
	global $db;
	
	$user = $db->executeGetOne( 'SELECT `user` FROM `users` WHERE `id`=\''. $db->escape($user_id) .'\'' );
	if (!$user ) _err( 'Unknown user.' );
	return $user;
}

function _get_sipuser( $user_id )
{
	global $db;
	
	$user = $db->executeGetOne( 'SELECT `name` FROM `ast_sipfriends` WHERE `_user_id`=\''. $db->escape($user_id) .'\'' );
	if (!$user ) _err( 'Unknown sip user.' );
	return $user;
}


if ( !gs_get_conf('GS_AASTRA_PROV_ENABLED') )
{
	gs_log(GS_LOG_NOTICE, 'Aastra provisioning not enabled');
	_err('Not enabled.');
}

$db = gs_db_master_connect();

$prov_url_aastra = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'aastra/';

$ua = trim( @$_SERVER['HTTP_USER_AGENT'] );
if ( preg_match('/\sMAC:(00-08-5D-\w{2}-\w{2}-\w{2})\s/', $ua, $m) )
	$mac = preg_replace( '/[^0-9A-F]/', '', strToUpper($m[1]) );

//$user = trim(@$_REQUEST['u']);
$user_id = _get_userid($user);
$user = _get_user ( $user_id );

// Check permissions
$user_groups = gs_group_members_groups_get(Array($user_id), "user");
$members = gs_group_permissions_get($user_groups, "dnd_set");

//get phone-model

$phone = gs_user_phonemodel_get( $user );
$dnd_softkey = 4;

if ( $phone == 'aastra-6739i' )
	$dnd_softkey = 2;

// exit if access is not granted
if(count($members) <= 0) {
	_err( 'Not permitted' );
}

$user_id_check = $db->executeGetOne("SELECT `user_id` FROM `phones` WHERE `mac_addr`='". $db->escape($mac) ."'");
if($user_id != $user_id_check) _err("Not authorized");

$remote_addr = @$_SERVER["REMOTE_ADDR"];
$remote_addr_check = $db->executeGetOne("SELECT `current_ip` FROM `users` WHERE `id`='". $user_id ."'");
if($remote_addr != $remote_addr_check) _err("Not authorized");

$current_dndstate = $db->executeGetOne("SELECT `active` FROM `dnd` WHERE `_user_id`=". $user_id);
gs_log(GS_LOG_NOTICE, "current_dndstate: " . $current_dndstate . " " . $user_id);
if ($current_dndstate == 'yes') {
	$check = $db->execute("INSERT INTO `dnd`
		(`_user_id`, `active`) VALUES
		(" . $user_id . ", 'no') 
		ON DUPLICATE KEY UPDATE `active` = 'no'");
	if (!$check) _err('Failed to set new DND state.');
	
	$xml = "<AastraIPPhoneExecute>\n" .
		"	<ExecuteItem URI=\"Led: softkey" . $dnd_softkey . "=off\"/>\n" .
		"	<ExecuteItem URI=\"" . $prov_url_aastra . 'settings.php?dynamic=1' . "\"/>\n" .
		"</AastraIPPhoneExecute>\n";
} else {
	$check = $db->execute("INSERT INTO `dnd`
		(`_user_id`, `active`) VALUES
		(" . $user_id . ", 'yes') 
		ON DUPLICATE KEY UPDATE `active` = 'yes'");
	if (!$check) _err('Failed to set new DND state.');
	$xml = "<AastraIPPhoneExecute>\n" .
		"	<ExecuteItem URI=\"Led: softkey" . $dnd_softkey  . "=slowflash\"/>\n" .
		"	<ExecuteItem URI=\"" . $prov_url_aastra . 'settings.php?dynamic=1' . "\"/>\n" .
		"</AastraIPPhoneExecute>\n";
}

if ( GS_BUTTONDAEMON_USE == true ) {

	$peer = _get_sipuser ( $user_id );
	$newstate = "off";
	if ($current_dndstate == 'no')
		$newstate = "on";	
	if ( $peer ) {
		gs_dnd_changed_ui ( $peer, $newstate );
	}
}

aastra_transmit_str($xml);

?>