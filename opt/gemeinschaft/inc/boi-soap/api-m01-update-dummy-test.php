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


$nobody_index = rand(1,9999);
$nbistr       = str_pad($nobody_index, 5, '0', STR_PAD_LEFT);
$ext          = '86'.$nbistr;
$user         = 'nobody-'.$nbistr;
$sip_pwd      = 'abcdefghijklmn'. rand(10,99);
$pin          = rand(1000,999999);
$firstname    = '';
$lastname     = '';
$email        = '';


$ret = gs_boi_update_extension( 'm01', $host, $ext, $user, $sip_pwd, $pin, $firstname, $lastname, $email, $soap_fault );
var_export($ret);
echo "\n";


?>