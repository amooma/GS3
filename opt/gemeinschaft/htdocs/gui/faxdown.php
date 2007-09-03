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

####################################################################
#
#  //FIXME. We should check permissions!
#
####################################################################

define( 'GS_VALID', true );  /// this is a parent file
require_once( dirName(__FILE__) .'/../../inc/conf.php' );
require_once( GS_DIR .'htdocs/gui/inc/session.php' );

$fld = @$_REQUEST['fld'];
$msg = @$_REQUEST['msg'];

require_once( GS_DIR .'inc/cn_hylafax.php' );
//start_session();

$file = trim(@$_REQUEST['file']);

if (! $file) {
	header( 'HTTP/1.0 404 Not Found', true, 404 );
	header( 'Status: 404 Not Found' , true, 404 );
	die( 'File not found.' );
}

if (! fax_download($file)) {
	header( 'HTTP/1.0 500 Internal Server Error', true, 500 );
	header( 'Status: 500 Internal Server Error' , true, 500 );
	die( 'Error.' );
}


$fnamel_pre = strLen(strRChr($file, '.'));
$fnamel_all = strLen($file);
$fname      = subStr($file, 0, $fnamel_all - $fnamel_pre);

@system('cd /var/spool/hylafax/ && /var/spool/hylafax/bin/tiff2pdf -o '. escapeShellArg('/tmp/'.$fname.'.pdf') .' '. escapeShellArg('/tmp/'.$file));

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="'.$fname.'.pdf"');
header('Content-Length: ' . fileSize('/tmp/'.$fname.'.pdf'));

readFile('/tmp/'.$fname.'.pdf');


?>