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


function gs_agi_str_esc( $str )
{
	$str = str_replace(
		array( '\\'  , ' '  , "\n" , "\r" , "\t" , '"'   ),
		array( '\\\\', '\\ ', '\\ ', '\\ ', '\\ ', '\\"' ),
		$str );
	return ($str == '' ? '""' : $str);
}

function gs_agi_verbose( $str, $level=1 )
{
	echo 'VERBOSE '. gs_agi_str_esc($str) .' '. (int)$level ."\n";
}

/*
function gs_agi_set_variable( $name, $val )
{
	if (! preg_match('/^[a-zA-Z0-9_]+$/', $name)) return false;
	echo 'SET VARIABLE '. $name .' '. gs_agi_str_esc($val) ."\n";
	return true;
}
*/

?>