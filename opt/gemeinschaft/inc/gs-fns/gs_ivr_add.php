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
* Author: Sven Neukirchner <s.neukirchner@konabi.de>
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
include_once( GS_DIR .'inc/gs-fns/gs_host_by_id_or_ip.php' );


/***********************************************************
*    creates a queue
***********************************************************/

function gs_ivr_add( $name, $title, $timeout, $host_id_or_ip, $announcement )
{
	if (! preg_match( '/^[\d]+$/', $name ))
		return new GsError( 'IVR extension must be numeric.' );
	$title = trim($title);
	$timeout = (int)$timeout;
	if ($timeout < 3) $timeout = 3;
	if (!$announcement)
		return new GsError( 'annoucement file must be entered.' );

	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );

	# check if ivr exists
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `ivrs` WHERE `name`=\''. $db->escape($name) .'\'' );
	if ($num > 0)
		return new GsError( 'A ivr with that extension already exists.' );

	# check if queue exists
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `ast_queues` WHERE `name`=\''. $db->escape($name) .'\'' );
	if ($num > 0)
		return new GsError( 'A queue with that extension already exists.' );

	# check if SIP user with same name exists
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `ast_sipfriends` WHERE `name`=\''. $db->escape($name) .'\'' );
	if ($num > 0)
		return new GsError( 'A SIP user with that extension already exists.' );

	# check if host exists
	#
	$host = gs_host_by_id_or_ip( $host_id_or_ip );
	if (isGsError( $host ))
		return new GsError( $host->getMsg() );
	if (! is_array( $host ))
		return new GsError( 'Unknown host.' );

	# add ivr
	#
	$ok = $db->execute(
'INSERT INTO `ivrs` (
	`id`,
	`name`,
	`host_id`,
	`title`


) VALUES (
	NULL,
	\''. $db->escape($name) .'\',
	'. (int)$host['id'] .',
	\''. $db->escape($title) .'\'


)' );
	if (! $ok)
		return new GsError( 'Failed to add ivr.' );

	return true;
}


?>