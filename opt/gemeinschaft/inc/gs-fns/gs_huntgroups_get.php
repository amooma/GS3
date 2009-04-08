<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 4716 $
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* 
* Author: Henning Holtschneider <henning@loca.net> - LocaNet oHG
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


/***********************************************************
*    returns an array of the queues
***********************************************************/

function gs_huntgroups_get()
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
	`number`
FROM
	`huntgroups`
GROUP BY `number`
ORDER BY `number`'
	);
	if (! $rs)
		return new GsError( 'Error.' );
	
	$huntgroups = array();
	while ($r = $rs->fetchRow())
		$huntgroups[] = $r;	
	return $huntgroups;
}


?>