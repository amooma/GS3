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
*    full validation of a username (to be used before
*    adding/renaming)
***********************************************************/

function gs_user_is_valid_name( $username )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $username ))
		return new GsError( 'Username must be lowercase alphanumeric.' );
	
	if (strLen($username) < 2)
		return new GsError( 'Username must have 2 characters or more.' );
	
	if (strLen($username) > 20)
		return new GsError( 'Username can\'t have more than 20 characters.' );
	
	if (preg_match( '/^[.]/', $username ))
		return new GsError( 'Username must not start in ".".' );
	
	if (! preg_match( '/^[a-z0-9\-_][a-z0-9\-_.]+$/', $username ))
		return new GsError( 'Invalid username.' );
	
	if (in_array($username, array(
		'sysadmin', 'admin', 'root', 'setup', 'my', 'gemeinschaft',
		'prov', 'img', 'js', 'mon', 'styles', 'soap', 'srv'
		), true)
	||  preg_match('/^nobody-/', $username))
	{
		return new GsError( sPrintF('"%s" cannot be used as a username.', $username) );
	}
	
	return true;
}


?>