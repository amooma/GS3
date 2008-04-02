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


function gs_db_master_connect()
{
	if (!( $db = YADB_newConnection( 'mysql' ) )) return null;
	if (!( $db->connect(
		GS_DB_MASTER_HOST,
		GS_DB_MASTER_USER,
		GS_DB_MASTER_PWD,
		GS_DB_MASTER_DB,
		array('reuse'=>false)  // do not use. leaves lots of connections
		)))
	{
		gs_log( GS_LOG_WARNING, 'Could not connect to master database!' );
		return null;
	}
	@ $db->setCharSet( 'utf8', 'utf8_unicode_ci' );
	return $db;
}

function gs_db_slave_connect()
{
	if (!( $db = YADB_newConnection( 'mysql' ) )) return null;
	if (!( $db->connect(
		GS_DB_SLAVE_HOST,
		GS_DB_SLAVE_USER,
		GS_DB_SLAVE_PWD,
		GS_DB_SLAVE_DB,
		array('reuse'=>false)  // do not use. leaves lots of connections
		)))
	{
		gs_log( GS_LOG_WARNING, 'Could not connect to slave database!' );
		return null;
	}
	@ $db->setCharSet( 'utf8', 'utf8_unicode_ci' );
	return $db;
}


?>