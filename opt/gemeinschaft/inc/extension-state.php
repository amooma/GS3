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


# These states are used in the manager API (since 1.4?) (see
# action_extensionstate() in manager.c, enum ast_extension_states in
# pbx.h, ast_extension_state() and ast_extension_state2() in pbx.c).
# They are different from the device states (AST_DEVICE_...)!
#
define( 'AST_MGR_EXT_UNKNOWN'  ,        -1  );  # no hint for the extension
define( 'AST_MGR_EXT_IDLE'     ,         0  );  # all devices idle (but registered)
define( 'AST_MGR_EXT_INUSE'    , 1<<0 /* 1*/);  # one or more devices busy
define( 'AST_MGR_EXT_BUSY'     , 1<<1 /* 2*/);  # all devices busy
define( 'AST_MGR_EXT_OFFLINE'  , 1<<2 /* 4*/);  # all devices unreachable/not registered
define( 'AST_MGR_EXT_RINGING'  , 1<<3 /* 8*/);  # one or more devices ringing
define( 'AST_MGR_EXT_ONHOLD'   , 1<<4 /*16*/);  # all devices on hold

define( 'AST_MGR_EXT_RINGINUSE', AST_MGR_EXT_INUSE |  # one or more devices busy
                                 AST_MGR_EXT_RINGING  # and one or more devices
                                      /* 9*/);        # ringing



function gs_extstate( $host, $exts )
{
	static $hosts = array();
	
	if (! is_array($exts)) {
		$exts = array($exts);
		$return_single = true;
	} else {
		$return_single = false;
	}
	
	if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE'))
		$host = '127.0.0.1';
	
	if (! isSet($hosts[$host])) {
		$hosts[$host] = array(
			'sock'    => null,
			'lasttry' => 0
		);
	}
	if (! is_resource($hosts[$host]['sock'])) {
		
		if ($hosts[$host]['lasttry'] > time()-60) {
			# we have tried less than a minute ago
			$hosts[$host]['lasttry'] = time();
			return $return_single ? AST_MGR_EXT_UNKNOWN : array();
		}
		$hosts[$host]['lasttry'] = time();
		
		$sock = @ fSockOpen( $host, 5038, $err, $errMsg, 2 );
		if (! is_resource($sock)) {
			gs_log( GS_LOG_WARNING, 'Connection to AMI on '.$host.' failed' );
			return $return_single ? AST_MGR_EXT_UNKNOWN : array();
		}
		$data = _sock_read( $sock, 3, '/[\\r\\n]/' );
		if (! preg_match('/^Asterisk [^\/]+\/(\d(?:\.\d)?)/mis', $data, $m)) {
			gs_log( GS_LOG_WARNING, 'Incompatible Asterisk manager interface on '.$host );
			$m = array(1=>'0.0');
		} else {
			if ($m[1] > '1.1') {
				# Asterisk 1.4: manager 1.0
				# Asterisk 1.6: manager 1.1
				gs_log( GS_LOG_NOTICE, 'Asterisk manager interface on '.$host.' speaks a new protocol version ('.$m[1].')' );
				# let's try anyway and hope to understand it
			}
		}
		$hosts[$host]['sock'] = $sock;
		$req = "Action: Login\r\n"
		     . "Username: ". "gscc" ."\r\n"
		     . "Secret: ". "gspass" ."\r\n"  //FIXME
		     . "Events: off\r\n"
		     . "\r\n";
		@ fWrite( $sock, $req, strLen($req) );
		@ fFlush( $sock );
		$data = _sock_read( $sock, 3, '/\\r\\n\\r\\n/S' );
		if ($data === false) {
			gs_log( GS_LOG_WARNING, 'Authentication to AMI on '.$host.' failed (timeout)' );
			$hosts[$host]['sock'] = null;
			return $return_single ? AST_MGR_EXT_UNKNOWN : array();
		}
		elseif (! preg_match('/Authentication accepted/i', $data)) {
			gs_log( GS_LOG_WARNING, 'Authentication to AMI on '.$host.' failed' );
			$hosts[$host]['sock'] = null;
			return $return_single ? AST_MGR_EXT_UNKNOWN : array();
		}
	} else {
		$sock = $hosts[$host]['sock'];
	}
	
	$states = array();
	foreach ($exts as $ext) {
		$req = "Action: ExtensionState\r\n"
		     . "Context: to-internal-users\r\n"  // "to-internal-users" or "default"
		     . "Exten: ". $ext ."\r\n"
		     . "\r\n";
		@ fWrite( $sock, $req, strLen($req) );
		@ fFlush( $sock );
		$resp = trim( _sock_read( $sock, 3, '/\\r\\n\\r\\n/S' ) );
		//echo "\n$resp\n\n";
		$states[$ext] = AST_MGR_EXT_UNKNOWN;
		if (! preg_match('/^Response:\s*Success/is', $resp)) continue;
		if (! preg_match('/^Exten:\s*([\da-z]+)/mis', $resp, $m)) continue;
		$resp_ext = $m[1];
		if (! preg_match('/^Status:\s*(-?\d+)/mis', $resp, $m)) continue;
		$resp_state = (int)$m[1];
		$states[$resp_ext] = $resp_state;
	}
	
	if ($return_single) {
		return array_key_exists( $exts[0], $states )
			? $states[$exts[0]]
			: AST_MGR_EXT_UNKNOWN;
	} else {
		return $states;
	}
}


function gs_extstate_single( $ext )
{
	if (! gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
		include_once( GS_DIR .'inc/db_connect.php' );
		$db = @ gs_db_slave_connect();
		if (! $db) {
			gs_log( GS_LOG_FATAL, 'Could not connect to slave DB!' );
			return AST_MGR_EXT_UNKNOWN;
		}
		$host = $db->executeGetOne(
'SELECT `h`.`host`
FROM
	`ast_sipfriends` `s` JOIN
	`users` `u` ON (`u`.`id`=`s`.`_user_id`) JOIN
	`hosts` `h` ON (`h`.`id`=`u`.`host_id`)
WHERE `s`.`name`=\''. $db->escape($ext) .'\'' );
		if (! $host)  # not a user
			return AST_MGR_EXT_UNKNOWN;
	}
	else {
		$host = '127.0.0.1';
	}
	
	return gs_extstate( $host, $ext );
}

function gs_extstate_callable( $ext ) {

	
	include_once( GS_DIR .'inc/db_connect.php' );
	$db = @ gs_db_slave_connect();
	if (! $db) {
		gs_log( GS_LOG_FATAL, 'Could not connect to slave DB!' );
		return AST_MGR_EXT_UNKNOWN;
	}


	
	//user and parallel call
	$rs = $db->execute(
'SELECT `a`.`_user_id`, `a`.`host`, `c`.`active` FROM `ast_sipfriends` `a`
	LEFT JOIN `callforwards` `c` ON ( `a`.`_user_id`= `c`.`user_id` 
		AND `c`.`source`="internal" AND `c`.`case`="always")
	WHERE `a`.`name`="' . $ext . '"');
	if ( ! $rs )	
		return new GsError( 'DB Error.' );
	if ( ! $user = $rs->FetchRow() ) {
		return new GsError( 'No extension ' . $ext  );	
	}
	
	if( $user['active'] == '' || $user['active'] == 'no' ) {
		//this is a user, no callforwards
		$state = gs_extstate ( $user[ 'host' ], $ext );
		return gs_ast_extstate_try_cc( $state );
	
	}
	else if ( $user['active'] == 'par' ) {
		//this is a user, parallel call enabled
		$rs = $db->execute(
'SELECT `a`.`name`, `a`.`host` 
	FROM `ast_sipfriends` `a`, `cf_parallelcall` `c` 
	WHERE `c`.`_user_id`= ' . $user ['_user_id'] . ' AND
		`c`.`number`=`a`.`name`');
		if ( ! $rs ) {
			return new GsError( 'DB Error.' . $ext  );
		}
		
		$count = 0;
		$hosts = array();
		
		while( $peer = $rs->FetchRow() ) {
			$count++;
			
			$host_id= $peer['host_id'];
			$hosts[$host_id][] = $peer['name'];
		}
		
		//no callforward users
		if( $count == 0 )
			return false;
			
		$allstates = array ();
		
		foreach ( $hosts as $host => $peers ) {
		
			$states =  gs_extstate( $host, $peers );
			if ( is_array ( $states )) {
				$allstates = array_merge ( $allstates , $states);
			}
		}
		
		
		foreach ( $allstates as $singlestate ) {
			
			if ( $singlestate != AST_MGR_EXT_IDLE )
				return false;
		}
		
		return true;
			
	}
	else {
		//any other callforward
		return false;
	}
	
	return false;
		
}


function _sock_read( $sock, $timeout, $stop_regex )
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
	if ($data === '') {
		# assume timeout
		return false;
	}
	return $data;
}

function gs_ast_extstate_offer_cc( $state )
{
	return ($state > AST_MGR_EXT_UNKNOWN);
	# don't offer call completion if there is no hint for the extension
	# as the state would never change
}

function gs_ast_extstate_try_cc( $state )
{
	return ($state == AST_MGR_EXT_IDLE);
	# try to initiate a callback if both the caller and the callee are
	# in idle state
}


?>