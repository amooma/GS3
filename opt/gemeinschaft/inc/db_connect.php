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

include_once( GS_DIR .'lib/yadb/yadb.php' );
include_once( GS_DIR .'inc/log.php' );


$gs_db_conn_master = null;
$gs_db_conn_slave  = null;
$gs_db_cdr_conn_master = null;

function gs_db_slave_is_master()
{
	return
		(  GS_DB_MASTER_HOST === GS_DB_SLAVE_HOST
		&& GS_DB_MASTER_USER === GS_DB_SLAVE_USER
		&& GS_DB_MASTER_PWD  === GS_DB_SLAVE_PWD
		&& GS_DB_MASTER_DB   === GS_DB_SLAVE_DB   );
}


function gs_db_is_connected( &$conn )
{
	return
		(  getType($conn) === 'object'
		&& method_exists($conn, 'isConnected')
		&& $conn->isConnected() );
}



function gs_sql_is_readonly( $sql )
{
	return preg_match( '/^[\\s\\(]*(?:SELECT|SET\\s+(?:NAMES|collation|autocommit|TRANSACTION|@*(?!global)|SESSION)|START\\s+TRANS|COMMIT|BEGIN|ROLLBACK|SHOW|DESCRIBE|EXPLAIN)/iS', $sql);
	# Note: COMMIT does not mean something has been written.
	# The "bad" queries are: UPDATE, INSERT, DELETE, REPLACE, ALTER, CREATE ...
}

function gs_yadb_sql_query_cb_readonly( &$yadbConn, $sql, $bindParams=null )
{
	if (! $yadbConn->getCustomAttr('w')
	&&  ! gs_sql_is_readonly($sql)) {
		//$sql = str_replace(array("\n","\r","\t"), array('\\n','\\r','\\t'), $sql);
		$sql = preg_replace('/\\s+/', ' ', $sql);
		gs_log( GS_LOG_WARNING, 'Non-read-only SQL query on slave DB in fallback mode: "'.$sql.'"' );
		return false;  # do not execute the query
	}
	return true;
}


function gs_db_connect( &$conn/*=null*/, $tag='', $host, $user, $pwd, $db=null, $_backtrace_level=0 )
{
	$caller_info = '';
	if (GS_LOG_LEVEL >= GS_LOG_DEBUG) {
		$bt = debug_backtrace();
		if (is_array($bt) && array_key_exists($_backtrace_level, $bt)) {
			$caller_info = ' for '. @$bt[$_backtrace_level]['file'] .':'. @$bt[$_backtrace_level]['line'] .'';
			unset($bt);
		}
	}
	
	if (gs_db_is_connected($conn)) {
		//gs_log( GS_LOG_DEBUG, 'Using existing'. ($tag != '' ? ' "'.$tag.'"':'') .' DB connection'. ($conn->getCustomAttr('w') ? '':' (read-only)') . $caller_info );
		// don't flood the log
		return 2;  # using the existing connection
	}
	gs_log( GS_LOG_DEBUG, 'New'. ($tag != '' ? ' "'.$tag.'"':'') .' DB connection' . $caller_info );
	
	if (!( $conn = YADB_newConnection( 'mysql' ) )) {
		$conn = null;
		return false;
	}
	if (!( $conn->connect(
		$host,
		$user,
		$pwd,
		$db,
		array(
			'reuse'=>false,  # do not use. leaves lots of connections
			'timeout'=>8
		))))
	{
		$lastNativeError    = @$conn->getLastNativeError();
		$lastNativeErrorMsg = @$conn->getLastNativeErrorMsg();
		gs_log( GS_LOG_WARNING, 'Could not connect to'. ($tag != '' ? ' "'.$tag.'"':'') .' database!'. ($lastNativeError ? ' (#'.$lastNativeError.' - '.$lastNativeErrorMsg.')' : '') );
		$conn = null;
		return false;
	}
	@ $conn->setCharSet( 'utf8', 'utf8_unicode_ci' );
	
	return 1;  # opened a new connection
}


function & gs_db_master_connect( $_backtrace_level=0, $read_fallback_slave=true )
{
	global $gs_db_conn_master, $gs_db_conn_slave;
	
	$ret = gs_db_connect(
		$gs_db_conn_master,
		'master',
		GS_DB_MASTER_HOST,
		GS_DB_MASTER_USER,
		GS_DB_MASTER_PWD,
		GS_DB_MASTER_DB,
		++$_backtrace_level
	);
	if ($ret === 1) {  # new connection
		$gs_db_conn_master->setCustomAttr('w',true);  # writeable
	}
	elseif (! $ret) {
		if ($read_fallback_slave) {
			# if the consumer wants a connection to the master database
			# but the master is down, give them a connection to the slave
			# instead and attach the gs_yadb_sql_query_cb_readonly()
			# callback to make it read-only. if someone tries to write
			# to the connection that callback deliberately makes the
			# query fail.
			gs_log( GS_LOG_NOTICE, 'Failed to connect to master database. Fallback to slave (read-only) ...' );
			$gs_db_conn_slave = gs_db_slave_connect();
			if (! $gs_db_conn_slave) {
				$null = null;
				return $null;
			}
			$gs_db_conn_slave->setCustomAttr('w',false);  # not writeable
			$gs_db_conn_slave->setQueryCb('gs_yadb_sql_query_cb_readonly');
			$gs_db_conn_master = $gs_db_conn_slave;  # do not use "=&" here
			return $gs_db_conn_slave;
		}
		$null = null;
		return $null;
	}
	
	if (gs_db_slave_is_master() && ! gs_db_is_connected($gs_db_conn_slave)) {
		$gs_db_conn_slave =& $gs_db_conn_master;
	}
	return $gs_db_conn_master;
}


function & gs_db_slave_connect( $_backtrace_level=0 )
{
	global $gs_db_conn_slave, $gs_db_conn_master;
	
	$ret = gs_db_connect(
		$gs_db_conn_slave,
		'slave',
		GS_DB_SLAVE_HOST,
		GS_DB_SLAVE_USER,
		GS_DB_SLAVE_PWD,
		GS_DB_SLAVE_DB,
		++$_backtrace_level
	);
	if (! $ret) {
		$null = null;
		return $null;
	}
	
	if (gs_db_slave_is_master() && ! gs_db_is_connected($gs_db_conn_master)) {
		$gs_db_conn_master =& $gs_db_conn_slave;
	}
	return $gs_db_conn_slave;
}

function & gs_db_cdr_master_connect( $_backtrace_level=0 )
{
	global $gs_db_cdr_conn_master;
	$ret=NULL;

	if (GS_DB_CDR_MASTER_HOST != '') {
		gs_log( GS_LOG_DEBUG, 'Opening new CDR-Master connection...' );
		$ret = gs_db_connect(
			$gs_db_cdr_conn_master,
			'master',
			GS_DB_CDR_MASTER_HOST,
			GS_DB_CDR_MASTER_USER,
			GS_DB_CDR_MASTER_PWD,
			GS_DB_CDR_MASTER_DB,
			++$_backtrace_level
			);
	} else {
	$ret = gs_db_connect(
			$gs_db_cdr_conn_master,
			'master',
			GS_DB_MASTER_HOST,
			GS_DB_MASTER_USER,
			GS_DB_MASTER_PWD,
			GS_DB_MASTER_DB,
			++$_backtrace_level
			);
	}

	if (! $ret) {
		gs_log( GS_LOG_ERROR, 'Failed to connect to CDR-master database!' );
		return $null;
	}
	return $gs_db_cdr_conn_master;
}


function gs_db_start_trans( &$dbConn )
{
	if (gs_get_conf('GS_DB_MASTER_TRANSACTIONS')) {
		@$dbConn->startTrans();
	}
}

function gs_db_commit_trans( &$dbConn )
{
	if (gs_get_conf('GS_DB_MASTER_TRANSACTIONS')) {
		return @$dbConn->completeTrans();
	}
	return true;
}

function gs_db_rollback_trans( &$dbConn )
{
	if (gs_get_conf('GS_DB_MASTER_TRANSACTIONS')) {
		return @$dbConn->completeTrans(false);
	}
	return false;
}


?>