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
	return ( GS_DB_MASTER_HOST === GS_DB_SLAVE_HOST
	      && GS_DB_MASTER_USER === GS_DB_SLAVE_USER
	      && GS_DB_MASTER_PWD  === GS_DB_SLAVE_PWD
	      && GS_DB_MASTER_DB   === GS_DB_SLAVE_DB   );
}


function & gs_db_master_connect()
{
	global $gs_db_conn_master, $gs_db_conn_slave;
	
	if (GS_LOG_LEVEL >= GS_LOG_DEBUG) {
		$bt = debug_backtrace();
		if (is_array($bt) && array_key_exists(0, $bt)) {
			$caller_info = @$bt[0]['file'] .':'. @$bt[0]['line'];
			unset($bt);
		} else {
			$caller_info = 'unknown';
		}
	}
	
	if (getType($gs_db_conn_master) === 'object'
	&&  method_exists($gs_db_conn_master, 'isConnected')
	&&  $gs_db_conn_master->isConnected())
	{
		gs_log( GS_LOG_DEBUG, 'Using the existing master DB connection (for '. $caller_info .')');
		return $gs_db_conn_master;
	}
	gs_log( GS_LOG_DEBUG, 'Opening a new master DB connection (for '. $caller_info .')');
	
	if (!( $db = YADB_newConnection( 'mysql' ) )) {
		$null = null;
		return $null;
	}
	if (!( $db->connect(
		GS_DB_MASTER_HOST,
		GS_DB_MASTER_USER,
		GS_DB_MASTER_PWD,
		GS_DB_MASTER_DB,
		array('reuse'=>false)  // do not use. leaves lots of connections
		)))
	{
		gs_log( GS_LOG_WARNING, 'Could not connect to master database!' );
		$null = null;
		return $null;
	}
	@ $db->setCharSet( 'utf8', 'utf8_unicode_ci' );
	
	$gs_db_conn_master = $db;
	if (gs_db_slave_is_master()) {
		$gs_db_conn_slave = $db;
	}
	return $gs_db_conn_master;
}


function & gs_db_slave_connect()
{
	global $gs_db_conn_slave, $gs_db_conn_master;
	
	if (GS_LOG_LEVEL >= GS_LOG_DEBUG) {
		$bt = debug_backtrace();
		if (is_array($bt) && array_key_exists(0, $bt)) {
			$caller_info = @$bt[0]['file'] .':'. @$bt[0]['line'];
			unset($bt);
		} else {
			$caller_info = 'unknown';
		}
	}
	
	if (getType($gs_db_conn_slave) === 'object'
	&&  method_exists($gs_db_conn_slave, 'isConnected')
	&&  $gs_db_conn_slave->isConnected())
	{
		gs_log( GS_LOG_DEBUG, 'Using the existing slave DB connection (for '. $caller_info .')');
		return $gs_db_conn_slave;
	}
	gs_log( GS_LOG_DEBUG, 'Opening a new slave DB connection (for '. $caller_info .')');
	
	if (!( $db = YADB_newConnection( 'mysql' ) )) {
		$null = null;
		return $null;
	}
	if (!( $db->connect(
		GS_DB_SLAVE_HOST,
		GS_DB_SLAVE_USER,
		GS_DB_SLAVE_PWD,
		GS_DB_SLAVE_DB,
		array('reuse'=>false)  // do not use. leaves lots of connections
		)))
	{
		gs_log( GS_LOG_WARNING, 'Could not connect to slave database!' );
		$null = null;
		return $null;
	}
	@ $db->setCharSet( 'utf8', 'utf8_unicode_ci' );
	
	$gs_db_conn_slave = $db;
	if (gs_db_slave_is_master()) {
		$gs_db_conn_master = $db;
	}
	return $gs_db_conn_slave;
}


?>