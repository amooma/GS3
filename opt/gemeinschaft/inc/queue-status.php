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
include_once( GS_DIR .'inc/extension-state.php' );

# These states are the internal device states (AST_DEVICE_...)
# from Asterisk's devicestate.h
define( 'AST_DEVICE_UNKNOWN'    , 0 );  # valid but unknown state
define( 'AST_DEVICE_NOT_INUSE'  , 1 );  # registered, idle
define( 'AST_DEVICE_INUSE'      , 2 );  # in use
define( 'AST_DEVICE_BUSY'       , 3 );  # busy
define( 'AST_DEVICE_INVALID'    , 4 );  # invalid
define( 'AST_DEVICE_UNAVAILABLE', 5 );  # unavailable
define( 'AST_DEVICE_RINGING'    , 6 );  # ringing
define( 'AST_DEVICE_RINGINUSE'  , 7 );  # in use and ringing
define( 'AST_DEVICE_ONHOLD'     , 8 );  # on hold

function ast_devstate2str( $devstate )
{
	# from Asterisk's devicestate.c
	static $states = array(
		AST_DEVICE_UNKNOWN     => 'Unknown',
		AST_DEVICE_NOT_INUSE   => 'Not in use',
		AST_DEVICE_INUSE       => 'In use',
		AST_DEVICE_BUSY        => 'Busy',
		AST_DEVICE_INVALID     => 'Invalid',
		AST_DEVICE_UNAVAILABLE => 'Unavailable',
		AST_DEVICE_RINGING     => 'Ringing',
		AST_DEVICE_RINGINUSE   => 'Ring+Inuse',
		AST_DEVICE_ONHOLD      => 'On Hold'
	);
	return array_key_exists($devstate, $states) ? $states[$devstate] : null;
}

function extstate_to_devstate( $extstate )
{
	static $states = array(
		AST_MGR_EXT_UNKNOWN   => AST_DEVICE_UNKNOWN,
		AST_MGR_EXT_IDLE      => AST_DEVICE_NOT_INUSE,
		AST_MGR_EXT_INUSE     => AST_DEVICE_INUSE,
		AST_MGR_EXT_BUSY      => AST_DEVICE_BUSY,
		AST_MGR_EXT_OFFLINE   => AST_DEVICE_UNAVAILABLE,
		AST_MGR_EXT_RINGING   => AST_DEVICE_RINGING,
		AST_MGR_EXT_RINGINUSE => AST_DEVICE_RINGINUSE,
		AST_MGR_EXT_ONHOLD    => AST_DEVICE_ONHOLD
	);
	return array_key_exists($extstate, $states)
		? $states[$extstate]
		: AST_DEVICE_UNKNOWN;
}

function gs_queue_status( $host, $ext, $getMembers, $getCallers )
{
	static $hosts = array();
	
	if (! isSet($hosts[$host])) {
		$hosts[$host] = array(
			'sock'    => null,
			'lasttry' => 0
		);
	}
	if (! is_resource($hosts[$host]['sock'])) {
		
		if ($hosts[$host]['lasttry'] > time()-3600) {
			# we have tried less than a minute ago
			$hosts[$host]['lasttry'] = time();
			return false;
		}
		$hosts[$host]['lasttry'] = time();
		
		$sock = @ fSockOpen( $host, 5038, $err, $errMsg, 4 );
		if (! is_resource($sock)) return false;
		$hosts[$host]['sock'] = $sock;
		$req = "Action: Login\r\n"
		     . "Username: ". "gscc" ."\r\n"
		     . "Secret: ". "gspass" ."\r\n"
		     . "Events: off\r\n"
		     . "\r\n";
		@ fWrite( $sock, $req, strLen($req) );
		@ fFlush( $sock );
		$data = _sock_read2( $sock, 5, '/\\r\\n\\r\\n/' );
		if (! preg_match('/Authentication accepted/i', $data)) {
			$hosts[$host]['sock'] = null;
			return false;
		}
	} else
		$sock = $hosts[$host]['sock'];
	
	$queue_stats = array(
		'maxlen'    => null,
		'calls'     => null,
		'holdtime'  => null,
		'completed' => null,
		'abandoned' => null,
		'sl'        => null,
		'slp'       => null
	);
	if ($getMembers) $queue_stats['members'] = array();
	if ($getCallers) $queue_stats['callers'] = array();
	$default_member = array(
		'dynamic'   => null,
		'calls'     => null,
		'lastcall'  => null,
		'devstate'  => null,
		'paused'    => null
	);
	$default_caller = array(
		'channel'   => null,
		'cidnum'    => null,
		'cidname'   => null,
		'wait'      => null
	);
	
	$req = "Action: QueueStatus\r\n"
		  . "Queue: ". $ext ."\r\n"
		  . "\r\n";
	@ fWrite( $sock, $req, strLen($req) );
	@ fFlush( $sock );
	$resp = trim( _sock_read2( $sock, 2, '/Event:\s*QueueStatusComplete\\r\\n\\r\\n/i' ) );
	//echo "\n$resp\n\n";
	if (! preg_match('/^Response:\s*Success/is', $resp)) return false;
	$resp = preg_split('/\\r\\n\\r\\n/S', $resp);
	/*
	echo "<pre>";
	print_r($resp);
	echo "</pre>";
	*/
	$manager_ok = false;
	foreach ($resp as $pkt) {
		$pkt = lTrim($pkt);
		if (preg_match('/^Event:\s*QueueParams/is', $pkt)) {
			
			if (! preg_match('/^Queue:\s*'. $ext .'/mis', $pkt)) continue;
			
			//echo $pkt, "\n\n";
			if (preg_match('/^Max:\s*(\d+)/mis', $pkt, $m))
				$queue_stats['maxlen'] = ((int)$m[1] > 0 ? (int)$m[1] : null);
			if (preg_match('/^Calls:\s*(\d+)/mis', $pkt, $m))
				$queue_stats['calls'] = (int)$m[1];
			if (preg_match('/^Holdtime:\s*(\d+)/mis', $pkt, $m))
				$queue_stats['holdtime'] = (int)$m[1];
			if (preg_match('/^Completed:\s*(\d+)/mis', $pkt, $m))
				$queue_stats['completed'] = (int)$m[1];
			if (preg_match('/^Abandoned:\s*(\d+)/mis', $pkt, $m))
				$queue_stats['abandoned'] = (int)$m[1];
			if (preg_match('/^ServiceLevel:\s*(\d+)/mis', $pkt, $m))
				$queue_stats['sl'] = (int)$m[1];
			if (preg_match('/^ServiceLevelPerf:\s*(\d+?(\.\d+)?)/mis', $pkt, $m))
				$queue_stats['slp'] = (float)$m[1];
			
			$manager_ok = true;
			
		} elseif ($getMembers && preg_match('/^Event:\s*QueueMember/is', $pkt)) {
			
			if (! preg_match('/^Queue:\s*'. $ext .'/mis', $pkt)) continue;
			if (! preg_match('/^Location:\s*([A-Z\d\/]+)/mis', $pkt, $m)) continue;
			$loc = $m[1];
			$queue_stats['members'][$loc] = $default_member;
			
			//echo $pkt, "\n\n";
			if (preg_match('/^Membership:\s*([a-z]+)/mis', $pkt, $m))
				$queue_stats['members'][$loc]['dynamic'] = ($m[1] != 'static');
			if (preg_match('/^CallsTaken:\s*(\d+)/mis', $pkt, $m))
				$queue_stats['members'][$loc]['calls'] = (int)$m[1];
			if (preg_match('/^LastCall:\s*(\d+)/mis', $pkt, $m))
				$queue_stats['members'][$loc]['lastcall'] = (int)$m[1];
			if (preg_match('/^Status:\s*(\d+)/mis', $pkt, $m))
				$queue_stats['members'][$loc]['devstate'] = (int)$m[1];
			if (preg_match('/^Paused:\s*(\d+)/mis', $pkt, $m))
				$queue_stats['members'][$loc]['paused'] = ((int)$m[1] > 0);
			
		} elseif ($getCallers && preg_match('/^Event:\s*QueueEntry/is', $pkt)) {
			
			if (! preg_match('/^Queue:\s*'. $ext .'/mis', $pkt)) continue;
			if (! preg_match('/^Position:\s*(\d+)/mis', $pkt, $m)) continue;
			$pos = (int)$m[1];
			$queue_stats['callers'][$pos] = $default_caller;
			
			//echo $pkt, "\n\n";
			if (preg_match('/^Channel:\s*([^\n\r]+)/mis', $pkt, $m))
				$queue_stats['callers'][$pos]['dynamic'] = trim($m[1]);
			if (preg_match('/^CallerID:\s*([^\n\r]+)/mis', $pkt, $m))
				$queue_stats['callers'][$pos]['cidnum'] = (strToLower(trim($m[1])) != 'unknown' ? trim($m[1]) : null);
			if (preg_match('/^CallerIDName:\s*([^\n\r]+)/mis', $pkt, $m))
				$queue_stats['callers'][$pos]['cidname'] = (strToLower(trim($m[1])) != 'unknown' ? trim($m[1]) : null);
			if (preg_match('/^Wait:\s*(\d+)/mis', $pkt, $m))
				$queue_stats['callers'][$pos]['wait'] = (int)$m[1];
			
		}
	}
	
	if (! $manager_ok && $getMembers) {
		# failed to get information about the queue from the manager
		# interface. this happens after a reload of Asterisk when
		# no call has entered the queue using Queue() yet
		
		$queue_stats['calls'    ] = 0;
		$queue_stats['completed'] = 0;
		$queue_stats['abandoned'] = 0;
		$queue_stats['holdtime' ] = 0;
		
		include_once( GS_DIR .'inc/db_connect.php' );
		$db = @ gs_db_slave_connect();
		if (! $db) {
			return $queue_stats;
		}
		
		$maxlen = (int)$db->executeGetOne( 'SELECT `maxlen` FROM `ast_queues` WHERE `name`=\''. $db->escape($ext) .'\'' );
		$queue_stats['maxlen'] = ($maxlen > 0 ? $maxlen : null);
		
		$rs = $db->execute( 'SELECT `interface` FROM `ast_queue_members` WHERE `queue_name`=\''. $db->escape($ext) .'\'' );
		$queue_members = array();
		while ($r = $rs->fetchRow()) {
			if (strToUpper(subStr($r['interface'],0,4)) == 'SIP/')
				$queue_members[] = subStr($r['interface'],4);
			else
				$queue_members[] = $r['interface'];
		}
		if (count($queue_members) < 1)
			return $queue_stats;
		
		foreach ($queue_members as $queue_member)
			$queue_stats['members']['SIP/'.$queue_member] = $default_member;
		
		$ext_states = @gs_extstate( $host, $queue_members );
		if (! is_array($ext_states)) {
			return $queue_stats;
		}
		
		foreach ($queue_members as $queue_member)
			$queue_stats['members']['SIP/'.$queue_member]['devstate']
				= extstate_to_devstate( @$ext_states[$queue_member] );
	}
	/*
	echo "<pre>";
	print_r($queue_stats);
	echo "</pre>";
	*/
	
	return $queue_stats;
}

function _sock_read2( $sock, $timeout, $stop_regex )
{
	if (! is_resource($sock)) return false;
	@ stream_set_blocking( $sock, false );
	//stream_set_timeout( $sock, 5 );  // not really used here
	$tStart = time();
	$data = '';
	while (! @ fEof( $sock ) && time() < $tStart+$timeout) {
		$data .= @ fRead( $sock, 8192 );
		if (@ preg_match($stop_regex, $data)) break;
		uSleep(1000);  # sleep 0.001 secs
	}
	return $data;
}


?>