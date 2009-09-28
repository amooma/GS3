<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2009, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
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
include_once( GS_DIR .'inc/group-fns.php' );
include_once( GS_DIR .'inc/gs-fns/gs_host_by_id_or_ip.php' );
//include_once( GS_DIR .'inc/gs-fns/gs_prov_phone_checkcfg.php' );
//include_once( GS_DIR .'inc/gs-fns/gs_asterisks_reload.php' );
//include_once( GS_DIR .'inc/gs-fns/gs_user_del.php' );


/***********************************************************
*    delete a host
***********************************************************/

function gs_host_del( $host,  $force=FALSE )
{
	if (! preg_match( '/^[0-9\.]+$/', $host ))
		return new GsError( 'Host must be a numeric ID or IP address.' );
	
	$host = gs_host_by_id_or_ip( $host );
	
	if (isGsError($host))   return new GsError(  $host->getMsg() );
	if (! is_array($host))  return new GsError( 'Cannot retrieve host ID.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	$count_users = $db->executeGetOne( 'SELECT COUNT(`id`) FROM `users` WHERE `host_id`=\''. $db->escape($host['id']) .'\'' );
	
	if ($count_users > 0)
		 return new GsError( 'Cannot delete host. Delete '.$count_users.' user(s) on this host first.' );
	
	#delete host from all groups
	#
	gs_group_members_purge_by_type('host', Array($host['id']));

	# delete host
	#
	$rs = $db->execute( 'DELETE from `hosts` WHERE `id`=\''. $db->escape($host['id']) .'\'' );
	if (! $rs)
		return new GsError( 'Could not delete host '.$host['id'] );
	
	return true;
}


?>