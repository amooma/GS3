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
require_once( GS_DIR .'inc/prov-fns.php' );
include_once( GS_DIR .'inc/group-fns.php' );
include_once( GS_DIR .'inc/aastra-fns.php' );
include_once( GS_DIR .'inc/gettext.php' );
include_once( GS_DIR .'inc/string.php' );
include_once( GS_DIR .'inc/gs-fns/gs_ami_events.php' );

function _err( $msg='' )
{
	aastra_textscreen( 'Error', ($msg != '' ? $msg : 'Unknown error'), 0, true );
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
$url_aastra_agent = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'aastra/agent.php';

$agent  = trim(@$_REQUEST['a']);
$pin    = trim(@$_REQUEST['p']);

$ua = trim( @$_SERVER['HTTP_USER_AGENT'] );
if ( preg_match('/\sMAC:(00-08-5D-\w{2}-\w{2}-\w{2})\s/', $ua, $m) )
	$mac = preg_replace( '/[^0-9A-F]/', '', strToUpper($m[1]) );

$user_id = _get_userid();
$user = @gs_prov_get_user_info( $db, $user_id );
if (! is_array($user)) {
	_err( 'DB error.' );
}

$user_id_check = $db->executeGetOne("SELECT `user_id` FROM `phones` WHERE `mac_addr`='". $db->escape($mac) ."'");
if($user_id != $user_id_check) _err("Not authorized");

$remote_addr = @$_SERVER["REMOTE_ADDR"];
$remote_addr_check = $db->executeGetOne("SELECT `current_ip` FROM `users` WHERE `id`='". $user_id ."'");
if($remote_addr != $remote_addr_check) _err("Not authorized");

# check permissions
#

$user_groups  = gs_group_members_groups_get( array( $user_id ), 'user' );
$members = gs_group_permissions_get ( $user_groups, 'agent' );
if ( count($members ) <= 0 )
        _err(__('Keine Berechtigung'));

# get agent
#
if ($agent)
	$agent_id = (int)$db->executeGetOne( 'SELECT `id` FROM `agents` WHERE `number`="'.$agent.'"' );
else
	$agent_id = (int)$db->executeGetOne( 'SELECT `id` FROM `agents` WHERE `user_id`='.$user_id );

$rs = $db->execute( 'SELECT 1 FROM `agents` WHERE `id`='.$agent_id.' AND `user_id` > 0' );
if (! $rs)
	_err(htmlEnt(__('Fehler bei Agentenanmeldung')));

if ($rs->numRows()) {
	$db->execute( 'DELETE FROM `ast_queue_members` WHERE `_user_id`='. $user_id );
	$db->execute( 'UPDATE `agents` SET `user_id`=0, `paused`=0 WHERE `id`='. $agent_id );
	$agent_number = $db->executeGetOne( 'SELECT `number` FROM `agents` WHERE `id`='.$agent_id );
	gs_queue_logoff_ui($user['name'], '*');
	gs_agent_logoff_ui($agent_number);
	aastra_textscreen(htmlEnt(__('Agent')), htmlEnt(__('Agent abgemeldet')), 3);
	exit;
}

if ($agent && isset($pin)) {
	$rs = $db->execute( 'SELECT 1 FROM `agents` WHERE `number`="'.$db->escape($agent).'" AND `pin`="'.$db->escape($pin).'"' );
	if (! $rs)
		_err(htmlEnt(__('Fehler bei Agentenanmeldung')));

	if ($rs->numRows()) {
		$rs = $db->execute( 'SELECT `agent_queues`.`queue_id`, `agent_queues`.`penalty`, `ast_queues`.`_host_id` `host_id`, `ast_queues`.`name` FROM `agent_queues`, `ast_queues` WHERE `ast_queues`.`_id` = `agent_queues`.`queue_id` AND `agent_id`='.$agent_id );
		if (! $rs)
			_err(htmlEnt(__('Fehler bei Agentenanmeldung')));
		if (!$rs->numRows())
			_err(htmlEnt(__('Keine Warteschlange konfiguriert')));

		$queue_set = array();
		while ($queue = $rs->fetchRow()) {
			$queue_id = (int)$queue['queue_id'];
			$host_id = (int)$queue['host_id'];
			$name = $queue['name'];
			$penalty = (int)$queue['penalty'];
			$queue_set[] = array('id'=>$queue_id, 'host'=>$host_id, 'name'=>$name, 'penalty' => $penalty );
	
		}

		foreach ($queue_set as $queue) {
			$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `ast_queue_members` WHERE `_queue_id`='. $queue['id'] .' AND `_user_id`='. $user_id );
			if ($num > 0) // user is already logged in on that queue
				_err(htmlEnt(__('Benutzer ist bereits angemeldet')));
		}

		foreach ($queue_set as $queue) {
 			$db->execute( 'INSERT INTO `ast_queue_members` (`queue_name`, `_queue_id`, `interface`, `penalty`, `_user_id`) VALUES (\''. $db->escape($queue['name']) .'\', '. $queue['id'] .', \''. $db->escape( 'SIP/'. $user['name'] ) .'\', '. $db->escape($queue['penalty']) .', ' . $user_id .')' );
			gs_queue_login_ui($user['name'], $queue['name']);
		}

		$db->execute( 'UPDATE `agents` SET `user_id`='.$user_id.' WHERE `id`='.$agent_id );
		gs_agent_login_ui($agent, $user['name']);
		aastra_textscreen(htmlEnt(__('Agent')), htmlEnt(__('Agent angemeldet')), 3);
		exit;
	}
}

if ($agent)
	$index = 3;
else
	$index = 2;

$xml = '<AastraIPPhoneInputScreen type="string" destroyOnExit="yes" displayMode="condensed" defaultIndex="'.$index.'">' ."\n";
$xml.= '<Title>'. htmlEnt(__('Rufumleitung')) .'</Title>' ."\n";
$xml.= '<URL>'.$url_aastra_agent.'</URL>' ."\n";
$xml.= '<Default></Default>' ."\n";
$xml.= '<InputField type="empty"></InputField>' ."\n";
$xml.= '<InputField type="number">' ."\n";
$xml.= '	<Prompt>'.htmlEnt(__('Agent')) .':</Prompt>' ."\n";
$xml.= '	<Default>'.$agent.'</Default>' ."\n";
$xml.= '	<Parameter>a</Parameter>' ."\n";
$xml.= '	<Selection>1</Selection>' ."\n";
$xml.= '</InputField>' ."\n";
$xml.= '<InputField type="number">' ."\n";
$xml.= '	<Prompt>'.htmlEnt(__('PIN')) .':</Prompt>' ."\n";
$xml.= '	<Default></Default>' ."\n";
$xml.= '	<Parameter>p</Parameter>' ."\n";
$xml.= '	<Selection>1</Selection>' ."\n";
$xml.= '</InputField>' ."\n";
$xml.= '<SoftKey index="1">' ."\n";
$xml.= '	<Label>'. htmlEnt(__('OK')) .'</Label>' ."\n";
$xml.= '	<URI>SoftKey:Submit</URI>' ."\n";
$xml.= '</SoftKey>' ."\n";
$xml.= '<SoftKey index="4">' ."\n";
$xml.= '	<Label>'. htmlEnt(__('Abbrechen')) .'</Label>' ."\n";
$xml.= '	<URI>SoftKey:Exit</URI>' ."\n";
$xml.= '</SoftKey>' ."\n";
$xml.= '<SoftKey index="2">' ."\n";
$xml.= '	<Label>&lt;--</Label>' ."\n";
$xml.= '	<URI>SoftKey:BackSpace</URI>' ."\n";
$xml.= '</SoftKey>' ."\n";
$xml.= '</AastraIPPhoneInputScreen>' ."\n";
	
aastra_transmit_str( $xml );

?>