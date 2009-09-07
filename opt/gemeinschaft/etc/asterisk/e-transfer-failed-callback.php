#!/usr/bin/php -q
<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
*
* Author: Andreas Neugebauer <neugebauer@loca.net> - LocaNet oHG
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

$target = gs_get_conf('GS_CALLBACK_FAILED_EXTENSION');

echo "\n";
echo  "\t\t\t\t", '//(auto-generated) {' ,"\n";
echo "\n";

if ( gs_get_conf('GS_TRANSFER_FAILED_CALLBACK') ) {
	
	echo  "\t\t\t\t", 'if ("${SIPTRANSFER}" = "yes") {', "\n";
	echo  "\t\t\t\t\t", 'Set(callback_user=$["${BLINDTRANSFER}" : "^SIP/([0-9]+)"]);', "\n";
	echo  "\t\t\t\t\t", 'if ("${callback_user}" != "${EXTEN}") {', "\n";
	echo  "\t\t\t\t\t\t", 'Wait(1);', "\n";
	echo  "\t\t\t\t\t\t", 'jump ${callback_user}@from-internal-users;',"\n";
	echo  "\t\t\t\t\t", '}', "\n";
	echo  "\t\t\t\t\t", 'else {', "\n";
	
	if ( strlen ( $target ) <= 0 ) {
		echo  "\t\t\t\t\t\t", 'Busy();',"\n";
	}
	else {
		echo  "\t\t\t\t\t\t", 'jump ' . $target. '@from-internal-users;',"\n";
	}
	
	echo  "\t\t\t\t\t", '}', "\n";
	echo  "\t\t\t\t", '}', "\n";
	
	
}
else {
	echo "\t\t\t\t", '//blind transfer failed: callback disabled' ,"\n\n";
}
echo  "\t\t\t\t", '//} (auto-generated)' ,"\n";
?>