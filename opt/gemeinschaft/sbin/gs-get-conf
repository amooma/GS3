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

#
# non-PHP scripts can use this interface to get
# Gemeinschaft config params
#

define( 'GS_VALID', true );  /// this is a parent file
require_once( dirName(__FILE__) .'/../inc/conf.php' );

if ($argc < 2) {
	echo "Usage: ./", baseName(__FILE__) ," CONFIG_PARAM\n";
	exit(1);
}
$param = $argv[1];
if (subStr($param,0,3) === 'GS_') {
	echo "Please don't use the \"GS_\" prefix.\n";
	exit(1);
}
$param = 'GS_'.$param;
$val = gs_get_conf($param);
echo serialize($val), "\n";


?>