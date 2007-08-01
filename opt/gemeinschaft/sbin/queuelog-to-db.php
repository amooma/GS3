#!/usr/bin/php -q
<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1126 $
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


######################################################################
#
#   Inserts Asterisk's queue log into the database. This is done
#   using lograte to rotate /var/log/asterisk/queue_log in place
#   (see etc/logrotate-queues.conf).
#
######################################################################

echo "NOT FINISHED\n";
die(1);

define( 'GS_VALID', true );  /// this is a parent file
require_once( dirName(__FILE__) .'/../inc/conf.php' );
include_once( GS_DIR .'inc/db_connect.php' );

$DB = gs_db_master_connect();


if (processLog())
	sleep(1);
else
	sleep(10);





function processLog()
{
	global $DB;
	
	# rotate the log file
	#
	exec( 'logrotate -f -s /dev/null \''. GS_DIR .'etc/logrotate-queues.conf\' >>/dev/null 2>&1', $out, $err );
	if (! file_exists( '/var/log/asterisk/queue_log.1' ))
		return false;
	
	# process the log file
	#
	$fh = @fOpen( '/var/log/asterisk/queue_log.1', 'rb' );
	if (! is_resource( $fh )) return false;
	while ($line = fGetS( $fh, 10000 )) {
		
		$r = dbEntryFromLogLine( $line );
		if (! $r) continue;
		
		if ($r['uid'] < 1 ) $r['uid'] = 'NULL';
		if ($r['qid'] < 1 ) $r['qid'] = 'NULL';
		if (! $r['callid']) $r['callid'] = 'NULL';
		
		$r['ts'      ] = (int)$r['ts'];
		$r['evt'     ] = '\''. $DB->escape($r['evt']) .'\'';
		$r['reason'  ] = dbQuoteOrNull( $r['reason'  ] );
		$r['cid'     ] = dbQuoteOrNull( $r['cid'     ] );
		$r['pos'     ] = dbQuoteOrNull( $r['pos'     ] );
		$r['opos'    ] = dbQuoteOrNull( $r['opos'    ] );
		$r['waittime'] = dbQuoteOrNull( $r['waittime'] );
		$r['calldur' ] = dbQuoteOrNull( $r['calldur' ] );
		$r['logindur'] = dbQuoteOrNull( $r['logindur'] );
		$r['info'    ] = dbQuoteOrNull( $r['info'    ] );
		
		$DB->execute(
'INSERT INTO `queue_log` (
	`queue_id`,
	`timestamp`,
	`event`,
	`reason`,
	`ast_call_id`,
	`user_id`,
	`caller`,
	`pos`,
	`origpos`,
	`waittime`,
	`logindur`,
	`calldur`,
	`info`
) VALUES (
	'. $r['qid'     ] .',
	'. $r['ts'      ] .',
	'. $r['evt'     ] .',
	'. $r['reason'  ] .',
	'. $r['callid'  ] .',
	'. $r['uid'     ] .',
	'. $r['cid'     ] .',
	'. $r['pos'     ] .',
	'. $r['opos'    ] .',
	'. $r['waittime'] .',
	'. $r['logindur'] .',
	'. $r['calldur' ] .',
	'. $r['info' ] .'
)' 	);
		
	}
	
	# remove the log file
	#
	@exec( 'rm -f \'/var/log/asterisk/queue_log.1\' >>/dev/null 2>&1' );
	return true;
}


function dbQuoteOrNull( $val ) {
	global $DB;
	return $val ? ('\''. $DB->escape($val) .'\'') : 'NULL';
}

function dbEntryFromLogLine( $line )
{
	$line = trim($line);
	if ($line=='') return false;
	$entry = explode('|', $line);
	@list( $ts, $callid, $queue, $iface, $evt, $d1, $d2, $d3, $d4 ) = $entry;
	if ($callid=='NONE' || $callid=='NULL') $callid = false;
	
	# get user id:
	$agentExt = extractAgentExtFromIface( $iface );
	$user_id = $agentExt ? (int)userExtToID( $agentExt ) : false;
	
	# get queue id:
	$queue_id = queueNameToID( $queue );
	
	$row = array(
		'ts'    => $ts,
		'callid'=> $callid,
		'qid'   => $queue_id,
		'uid'   => $user_id,
		'evt'   => $evt,
		'reason'  => null,
		'cid'     => null,
		'pos'     => null,
		'opos'    => null,
		'waittime'=> null,
		'calldur' => null,
		'logindur'=> null,
		'info'    => null
	);
	
	switch ($evt) {
		case 'ENTERQUEUE':
			$row['evt'     ] = '_ENTER';
			$row['cid'     ] = $d2;
			break;
		case 'ABANDON':
			$row['evt'     ] = '_EXIT';
			$row['reason'  ] = 'ABANDON';
			$row['pos'     ] = $d1;
			$row['opos'    ] = $d2;
			$row['waittime'] = $d3;
			break;
		case 'CONNECT':
			$row['evt'     ] = '_CONNECT';
			$row['waittime'] = $d1;
			break;
		case 'COMPLETEAGENT':
			$row['evt'     ] = '_COMPLETE';
			$row['reason'  ] = 'AGENT';
			$row['waittime'] = $d1;
			$row['calldur' ] = $d2;
			$row['opos'    ] = $d3;
			break;
		case 'COMPLETECALLER':
			$row['evt'     ] = '_COMPLETE';
			$row['reason'  ] = 'CALLER';
			$row['waittime'] = $d1;
			$row['calldur' ] = $d2;
			$row['opos'    ] = $d3;
			break;
		case 'RINGNOANSWER':
			$row['evt'     ] = '_RINGNOANSWER';
			break;
		case 'AGENTDUMP':
			$row['evt'     ] = '_EXIT';
			$row['reason'  ] = 'DUMP';
			break;
		case 'AGENTLOGIN':
		case 'AGENTCALLBACKLOGIN':
			$row['evt'     ] = '_AGENTLOGIN';
			break;
		case 'AGENTLOGOFF':
			$row['evt'     ] = '_AGENTLOGOFF';
			$row['logindur'] = $d2;
			break;
		case 'AGENTCALLBACKLOGOFF':
			$row['evt'     ] = '_AGENTLOGOFF';
			$row['logindur'] = $d2;
			$row['reason'  ] = $d3;
			break;
		case 'EXITWITHKEY':
			$row['evt'     ] = '_EXIT';
			$row['reason'  ] = 'KEY';
			$row['pos'     ] = $d1;
			break;
		case 'EXITWITHTIMEOUT':
			$row['evt'     ] = '_EXIT';
			$row['reason'  ] = 'TIMEOUT';
			$row['pos'     ] = $d1;
			break;
		case 'RINGNOANSWER':
			$row['evt'     ] = '_RINGNOANSWER';
			break;
		case 'SYSCOMPAT':
			$row['evt'     ] = '_COMPLETE';
			$row['reason'  ] = 'INCOMPAT';
			break;
		case 'TRANSFER':
			$row['evt'     ] = '_COMPLETE';
			$row['reason'  ] = 'TRANSFER';
			$row['waittime'] = $d3;
			$row['calldur' ] = $d4;
			$row['info'    ] = $d1 .'@'. $d2;
			break;
	}
	
	return $row;
}


function queueNameToID( $name )
{
	global $DB;
	static $queues;
	if ($name=='NONE' || $name=='NULL') return false;
	if (! isSet( $queues[$name] )) {
		$id = (int)$DB->executeGetOne( 'SELECT `_id` FROM `ast_queues` WHERE `name`=\''. $DB->escape($name) .'\'' );
		$queues[$name] = ($id > 0) ? $id : false;
	}
	return $queues[$name];
}


function userExtToID( $ext )
{
	global $DB;
	static $users;
	if (! isSet( $users[$ext] )) {
		$id = (int)$DB->executeGetOne( 'SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`=\''. $DB->escape($ext) .'\'' );
		$users[$ext] = ($id > 0) ? $id : false;
	}
	return $users[$ext];
}


function extractAgentExtFromIface( $interface )
{
	if (preg_match( '/^[^\/]*\/([\da-zA-Z]+)/', trim($interface), $m ))
		return $m[1];
	return false;
}



?>