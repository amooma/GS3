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

$gsDefaultNobodyPattern        = '9xxxxx';
// will only be used if NOBODY_EXTEN_PATTERN has bad syntax
$gsDefaultNobodyPatternForeign = '9xxxxx';
// will only be used if BOI_NOBODY_EXTEN_PATTERN has bad syntax


// don't call from anywhere except in this file
function gs_conf_nobody_pattern( $at_boi_host=false )
{
	global $gsDefaultNobodyPattern, $gsDefaultNobodyPatternForeign;
	
	$pattern = strToLower(trim(
		$at_boi_host ? GS_BOI_NOBODY_EXTEN_PATTERN : GS_NOBODY_EXTEN_PATTERN ));
	if (! preg_match('/^\d+x+$/', $pattern))
		$pattern = $gsDefaultNobodyPattern;
	return $pattern;
}


function gs_nobody_pattern()
{
	return '_'. str_replace('x', 'X', gs_conf_nobody_pattern(false));
}

function gs_nobody_index_to_extension( $index, $at_boi_host=false )
{
	$start = (int)preg_replace('/[^\d]/', '0', gs_conf_nobody_pattern($at_boi_host));
	return (string)( $start + (int)$index );
}

function gs_is_nobody_extension( $ext, $at_boi_host=false )
{
	$nobody_rxpattern = '/' . preg_replace('/[^\d]/', '\d', gs_conf_nobody_pattern($at_boi_host)) . '/';

	return (int)preg_match($nobody_rxpattern, (string)$ext);
}

?>
