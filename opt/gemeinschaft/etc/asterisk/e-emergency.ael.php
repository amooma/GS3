#!/usr/bin/php -q
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

define( 'GS_VALID', true );  /// this is a parent file

ini_set('implicit_flush', 1);
ob_implicit_flush(1);

error_reporting(0);

require_once( dirName(__FILE__) .'/../../inc/conf.php' );
set_error_handler('err_handler_quiet');


echo "\n";

$police = gs_get_conf('GS_DP_EMERGENCY_POLICE_MAP');
if (! $police) $police = '110';
$tmp = gs_get_conf('GS_DP_EMERGENCY_POLICE');
$numbers = explode(',', $tmp);
if (is_array($numbers)) {
	# remove duplicates:
	//$numbers = array_flip(array_flip($numbers));
	# output:
	foreach ($numbers as $number) {
		$number = trim($number);
		if (! preg_match('/^[0-9]+$/', $number)) continue;
		
		echo "\t", $number, ' => &emergency-call(', $police, ');', "\n";
	}
}

$firedept = gs_get_conf('GS_DP_EMERGENCY_FIRE_MAP');
if (! $firedept) $firedept = '110';
$tmp = gs_get_conf('GS_DP_EMERGENCY_FIRE');
$numbers = explode(',', $tmp);
if (is_array($numbers)) {
	# remove duplicates:
	$numbers = array_flip(array_flip($numbers));
	# output:
	foreach ($numbers as $number) {
		$number = trim($number);
		if (! preg_match('/^[0-9]+$/', $number)) continue;
		
		echo "\t", $number, ' => &emergency-call(', $firedept, ');', "\n";
	}
}

echo "\n";

?>