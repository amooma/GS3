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
* Author: Andreas Neugebauer, LocaNet oHG <neugebauer@loca.net>
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
require_once( GS_DIR .'inc/gs-fns/gs_agent_pause_unpause.php' );
include_once( GS_DIR .'inc/gettext.php' );
require_once(GS_DIR .'inc/group-fns.php');
include_once( GS_DIR .'inc/string.php' );
include_once( GS_DIR .'inc/aastra-fns.php' );

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
$user_id = _get_userid();
//$user = _get_user ( $user_id );
$sip_user = _get_sipuser ( $user_id );

//get phone-model

$user_id_check = $db->executeGetOne("SELECT `user_id` FROM `phones` WHERE `mac_addr`='". $db->escape($mac) ."'");
if($user_id != $user_id_check) _err("Not authorized");

$remote_addr = @$_SERVER["REMOTE_ADDR"];
$remote_addr_check = $db->executeGetOne("SELECT `current_ip` FROM `users` WHERE `id`='". $user_id ."'");
if($remote_addr != $remote_addr_check) _err("Not authorized");

$agent_id = $db->executeGetOne("SELECT `id` FROM `agents` WHERE `user_id`='". $db->escape($user_id) ."'");
if($agent_id <= 0) _err("Not an agent");

$rs = $db->execute("SELECT SUM(`paused`) AS `paused`, COUNT(`_queue_id`) AS `q_count` FROM `ast_queue_members` WHERE `_user_id`=". $user_id);
if (! $rs || ! ($r = $rs->fetchRow())) {
	_err('DB error.');
}

$q_count = (int)$r['q_count'];
$paused =  (int)$r['paused'];


# check if db is ok 'ast_queue_menbers' is ok for user
#


if ( $q_count <= 0 ) {
	//the user does not have queues
	_err('No queues for user_id ' . $user_id );
}

if ( $paused > 0 ) {

	// the user seems to be paused
	if ( $q_count != $paused ) {
		// the user is not pasused in all queues ( imposible for agents)
		 _err('user_id ' . $user_id . ' is paused in ' .  $paused . ' queues but agent in ' . $q_count . ' queues.' );
	}
	else {
		//everything seems to be fine. So lets toggle (unpause) the user
		$ret = gs_agent_pause_unpause ( $agent_id , false );
		if (isGsError($ret)) {
		//$ret->getMsg()
		  gs_log(GS_LOG_NOTICE, "Could not unpause user " . $sip_user . ": " .  $ret->getMsg() );
                  _err ( $ret->getMsg() );
		}
		gs_log(GS_LOG_NOTICE, "Unpaused user " . $sip_user );
		aastra_textscreen(htmlEnt(__('Agent')), htmlEnt(__('Pause deaktiviert')), 3);
		exit;
	}

}
else {
	//user is notz paused
	$ret = gs_agent_pause_unpause ( $agent_id , true );
	if (isGsError($ret)) {
          //$ret->getMsg()
         gs_log(GS_LOG_NOTICE, "Could not pause user " . $sip_user . ": " .  $ret->getMsg() );
         _err ( $ret->getMsg() );
	}
	gs_log(GS_LOG_NOTICE, "Paused user " . $sip_user );
	aastra_textscreen(htmlEnt(__('Agent')), htmlEnt(__('Pause aktiviert')), 3);
	exit;
}


?>