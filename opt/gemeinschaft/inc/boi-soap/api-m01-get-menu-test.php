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
require_once( dirName(__FILE__) .'/../../inc/conf.php' );
include_once( GS_DIR .'inc/util.php' );
include_once( GS_DIR .'inc/boi-soap/boi-soap.php' );


if ($argc < 2) {
	echo "Arg 1 must be the host!\n";
	exit(1);
}
$host = @$argv[1];

if ($argc < 3) {
	echo "Arg 2 must be the role!\n";
	exit(1);
}
$role = @$argv[2];

if ($argc < 4) {
	echo "Arg 3 must be the extension!\n";
	exit(1);
}
$ext = @$argv[3];


$menu = gs_boi_get_gui_menu( 'm01', $host, $role, $ext );
var_export($menu);
echo "\n";


?>