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
*    returns an array of the queues
***********************************************************/

function gs_queues_get()
{
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get queues
	#
	$rs = $db->execute(
'SELECT
	`q`.`_id` `id`, `q`.`name`, `q`.`_title` `title`, `q`.`maxlen`,
	COUNT(`m`.`_user_id`) `num_members`
FROM
	`ast_queues` `q` LEFT JOIN
	`ast_queue_members` `m` ON (m._queue_id=q._id)
GROUP BY `q`.`_id`
ORDER BY `q`.`name`'
	);
	if (! $rs)
		return new GsError( 'Error.' );
	
	$queues = array();
	while ($r = $rs->fetchRow())
		$queues[] = $r;
	return $queues;
}


?>