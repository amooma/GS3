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
require_once( GS_DIR .'htdocs/gui/inc/session.php' );

$ext = @$_SESSION['sudo_user']['info']['ext'];
$fld = @$_REQUEST['fld'];
$msg = @$_REQUEST['msg'];


$filename = '/tmp/gs-vm-'. $ext .'-'. $fld .'-'. $msg .'.gsm';
if (! file_exists($filename)) {
	header( 'HTTP/1.0 404 Not Found', true, 404 );
	header( 'Status: 404 Not Found' , true, 404 );
	header( 'Content-Type: text/plain' );
	die( 'Not found.' );
}

header( 'Content-Type: audio/x-gsm' );

# set Content-Length to prevent Apache(/PHP?) from using
# "Transfer-Encoding: chunked" which makes the sound file appear too
# short in QuickTime and maybe other players
header( 'Transfer-Encoding: identity' );
header( 'Content-Length: '. (int)@fileSize($filename) );

@readFile( $filename );


@exec( 'sudo rm -rf '. escapeShellArg($filename) .' 1>>/dev/null 2>&1' );
@exec( 'sudo find \'/tmp/\' -maxdepth 1 -name \'gs-vm-*\' -type f -mmin +30 | xargs rm -f 1>>/dev/null 2>&1' );

?>