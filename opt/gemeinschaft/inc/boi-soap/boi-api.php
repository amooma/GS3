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
include_once( GS_DIR .'inc/log.php' );
include_once( GS_DIR .'inc/db_connect.php' );


function gs_host_get_api( $host_id )
{
	if ($host_id < 1) return '__fail_api';
	$db = gs_db_master_connect();
	if (! $db) {
		gs_log(GS_LOG_WARNING, 'Could not connect to DB');
		return '__fail_api';
	}
	$api = $db->executeGetOne(
		'SELECT `value` '.
		'FROM `host_params` '.
		'WHERE '.
			'`host_id`='. (int)$host_id .' AND '.
			'`param`=\'api\''
		);
	return (string)$api;
}


?>