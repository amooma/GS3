<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
*
* $Revision: 5949 $
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
*    deletes a ivr
***********************************************************/

function gs_ivr_del( $name )
{
	if (! preg_match( '/^[\d]+$/', $name ))
		return new GsError( 'IVR name must be numeric.' );

	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );

	$CDR_DB = gs_db_cdr_master_connect();
	if (! $CDR_DB) {
		echo 'CDR DB error.';
		return;
	}

	# check if ivr exists
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `ivrs` WHERE `name`=\''. $db->escape($name) .'\'' );
	if ($num < 1)
		return new GsError( 'Unknown ivr.' );

	# get ivr_id
	#
	$ivr_id = (int)$db->executeGetOne( 'SELECT `id` FROM `ivrs` WHERE `name`=\''. $db->escape($name) .'\'' );
	if ($ivr_id < 1)
		return new GsError( 'Unknown ivr.' );

	# delete ivr
	#
	$ok = $db->execute( 'DELETE FROM `ivrs` WHERE `id`='. $ivr_id .' LIMIT 1' );

	if (! $ok)
		return new GsError( 'Failed to delete ivr.' );

	return true;
}


?>