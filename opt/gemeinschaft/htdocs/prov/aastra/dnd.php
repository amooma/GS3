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
include_once( GS_DIR .'inc/gettext.php' );

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

$user = trim(@$_REQUEST['u']);
$user_id = _get_userid($user);

$user_id_check = $db->executeGetOne("SELECT `user_id` FROM `phones` WHERE `mac_addr`='". $db->escape($mac) ."'");
if($user_id != $user_id_check) _err("Not authorized");

$remote_addr = @$_SERVER["REMOTE_ADDR"];
$remote_addr_check = $db->executeGetOne("SELECT `current_ip` FROM `users` WHERE `id`='". $user_id ."'");
if($remote_addr != $remote_addr_check) _err("Not authorized");

$current_dndstate = $db->executeGetOne("SELECT `active` FROM `dnd` WHERE `_user_id`=". $user_id);
if ($current_dndstate == 'yes') {
	$check = $db->execute("INSERT INTO `dnd`
		(`_user_id`, `active`) VALUES
		(" . $user_id . ", 'no') 
		ON DUPLICATE KEY UPDATE `active` = 'no'");
	if (!$check) _err('Failed to set new DND state.');
	$xml = "<AastraIPPhoneExecute>\n" .
		"	<ExecuteItem URI=\"Led: softkey4=off\"/>\n" .
		"	<ExecuteItem URI=\"" . $prov_url_aastra . 'settings.php?dynamic=1' . "\"/>\n" .
		"</AastraIPPhoneExecute>\n";
} else {
	$check = $db->execute("INSERT INTO `dnd`
		(`_user_id`, `active`) VALUES
		(" . $user_id . ", 'yes') 
		ON DUPLICATE KEY UPDATE `active` = 'yes'");
	if (!$check) _err('Failed to set new DND state.');
	$xml = "<AastraIPPhoneExecute>\n" .
		"	<ExecuteItem URI=\"Led: softkey4=slowflash\"/>\n" .
		"	<ExecuteItem URI=\"" . $prov_url_aastra . 'settings.php?dynamic=1' . "\"/>\n" .
		"</AastraIPPhoneExecute>\n";
}


aastra_transmit_str($xml);

?>