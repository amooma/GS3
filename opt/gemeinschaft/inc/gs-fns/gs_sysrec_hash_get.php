<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* 
* Author: Andreas Neugebauer <neugebauer@loca.net>
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
*    returns whether the user has CLIR turned on
*    for calls to internal/external
***********************************************************/

function gs_sysrec_hash_get( $sysrec_id )
{
	if (! preg_match( '/^[0-9]+$/', $sysrec_id ))
		return new GsError( 'Sysrecid must be numeric.' );
	
	# connect to db
	#
	$db = gs_db_slave_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	$filename = $db->executeGetOne( 'SELECT `md5hashname` FROM `systemrecordings` WHERE id =' . $sysrec_id );
	
	if ( strlen($filename) > 0 )
		return $filename;
	else
		return new GsError( 'No such sysrec.' );
}


?>