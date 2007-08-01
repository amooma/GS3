<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1119 $
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


function remote_exec( $host, $cmd, $timeout=10, &$out, &$err )
{
	$host = trim($host);
	if ($host=='') return false;
	$cmd = trim($cmd);
	if ($cmd=='') return false;
	$full_cmd = GS_DIR .'sbin/remote-exec-do '. escapeShellArg( $host ) .' '. escapeShellArg( $cmd ) .' '. (int)$timeout;
	@ exec( $full_cmd, $buf_out, $buf_err );
	$out = $buf_out;
	$err = (int)$buf_err;
	return true;
}


?>