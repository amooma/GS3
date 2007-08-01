#!/usr/bin/php -q
<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1128 $
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
require_once( dirName(__FILE__) .'/../inc/conf.php' );
require_once( GS_DIR .'inc/remote-exec.php' );

if ($argc != 4) {
	echo 'Usage: ', baseName($argv[0]), " <host> <command> <timeout>\n";
	die(1);
}
$host    =      trim($argv[1]);
$cmd     =      trim($argv[2]);
$timeout = (int)trim($argv[3]);

remote_exec( $host, $cmd, $timeout, $out, $err );
echo implode("\n", $out), "\n";
if     ($err==  0) $errMsg = 'OK';
elseif ($err==119) $errMsg = 'SSH ASKS FOR PASSWORD';
elseif ($err==118) $errMsg = 'SSH ASKS FOR PASSPHRASE';
elseif ($err==117) $errMsg = 'NO ROUTE TO HOST';
elseif ($err==116) $errMsg = 'CONNECTION REFUSED';
elseif ($err==110) $errMsg = 'TIMEOUT';
elseif ($err==120) $errMsg = 'UNKNOWN REMOTE EXEC ERROR';
elseif ($err==  1) $errMsg = 'UNKNOWN ERROR';
elseif ($err==127) $errMsg = 'COMMAND NOT FOUND';
else               $errMsg = 'REMOTE COMMAND RETURNED '. $err;
echo 'Exit code: ', $err, ' (', $errMsg, ')', "\n";


?>