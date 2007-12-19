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


gs_keyval_is_valid_key( $key )
{
	return (bool)preg_match('/[a-z0-9_\-][a-z0-9_\-.]*/', $key);
}

gs_keyval_get( $key )
{
	if (! gs_keyval_is_valid_key($key)) return null;
	return rawUrlDecode(trim((string)@file_get_contents( '/var/lib/gemeinschaft/vars/'.$key )));
}

gs_keyval_set( $key, $val )
{
	if (! gs_keyval_is_valid_key($key)) return false;
	$val = rawUrlEncode($val);
	$fh = @fOpen( '/var/lib/gemeinschaft/vars/'.$key, 'wb' );
	if (! $fh) return false;
	stream_set_write_buffer($fh, 0);
	if (! @fWrite($fh, $val, strLen($val))) return false;
	@fClose($fh);
	return true;
}


?>