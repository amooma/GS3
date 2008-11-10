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
		gs_log( GS_LOG_DEBUG, 'Using existing DB connection'. ($tag != '' ? ' ('.$tag.')':'') . $caller_info );
		return 2;  # using the existing connection
	}
	gs_log( GS_LOG_DEBUG, 'New DB connection'. ($tag != '' ? ' ('.$tag.')':'') . $caller_info );
	
	if (!( $conn = YADB_newConnection( 'mysql' ) )) {
		$conn = null;
		return false;
	}
	if (!( $conn->connect(
		$host,
		$user,
		$pwd,
		$db,
		array('reuse'=>false)  // do not use. leaves lots of connections
		)))
	{
		$lastNativeError    = @$conn->getLastNativeError();
		$lastNativeErrorMsg = @$conn->getLastNativeErrorMsg();
		gs_log( GS_LOG_WARNING, 'Could not connect to database'. ($tag != '' ? ' ('.$tag.')':'') .'!'. ($lastNativeError ? ' (#'.$lastNativeError.' - '.$lastNativeErrorMsg.')' : '') );
		$conn = null;
		return false;
	}
	@ $conn->setCharSet( 'utf8', 'utf8_unicode_ci' );
	
	return 1;  # opened a new connection
}


function & gs_db_master_connect( $_backtrace_level=0 )
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
	if (! $ret) {
		$null = null;
		return $null;
	}
	
	if (gs_db_slave_is_master() && ! gs_db_is_connected($gs_db_conn_slave)) {
		$gs_db_conn_slave = $gs_db_conn_master;
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
		$gs_db_conn_master = $gs_db_conn_slave;
	}
	return $gs_db_conn_slave;
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