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
include_once( GS_DIR .'inc/gs-lib.php' );


/***********************************************************
*    returns an array of the provisioning jobs
***********************************************************/

function gs_prov_jobs_get()
{
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get jobs
	#
	$rs = $db->execute(
		'SELECT '.
			'`j`.`id`, `j`.`inserted`, `j`.`running`, `j`.`trigger`, '.
			'`p`.`mac_addr`, `j`.`type`, `j`.`immediate`, '.
			'`j`.`minute`, `j`.`hour`, `j`.`day`, `j`.`month`, `j`.`dow`, '.
			'`j`.`data` '.
		'FROM '.
			'`prov_jobs` `j` LEFT JOIN '.
			'`phones` `p` ON (`p`.`id`=`j`.`phone_id`) '.
		'ORDER BY `j`.`inserted`'
		);
	if (! $rs)
		return new GsError( 'DB error.' );
	
	$jobs = array();
	while (! $rs->EOF) {
		$jobs[] = $rs->fetchRow();
	}
	return $jobs;
}

?>