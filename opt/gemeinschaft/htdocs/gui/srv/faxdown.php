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
require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'htdocs/gui/inc/session.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );
require_once( GS_DIR .'inc/cn_hylafax.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_pin_get.php' );


$file = trim(@$_REQUEST['file']);
$raw  = trim(@$_REQUEST['raw']);

if (!$file) $file = $raw;
if (!$file) {
	header('HTTP/1.0 403 Forbidden', true, 403);
	header('Status: 403 Forbidden' , true, 403);
	header('Content-Type: text/plain');
	die( 'Unauthorized.' );
}

if (! fax_download($file, $_SESSION['sudo_user']['name'], gs_user_pin_get($_SESSION['sudo_user']['name']))) {
	header('HTTP/1.0 500 Internal Server Error', true, 500);
	header('Status: 500 Internal Server Error' , true, 500);
	header('Content-Type: text/plain');
	die( 'Error. Failed to retrieve fax from server.' );
}

$raw_file = realPath('/tmp/'.$file);
if (empty($raw_file) || subStr($raw_file,0,5) !== '/tmp/') {
	header('HTTP/1.0 500 Internal Server Error', true, 500);
	header('Status: 500 Internal Server Error' , true, 500);
	header('Content-Type: text/plain');
	die( 'Error. Bad filename.' );
}


if ($raw) {
	# TIFF
	header('Content-Type: image/tiff');
	header('Content-Disposition: attachment; filename="'.$file.'"');
	header('Content-Length: ' . (int)@fileSize('/tmp/'.$file));
	@readFile('/tmp/'.$file);
	@unlink('/tmp/'.$file);
}
else {
	#PDF
	$pdf_file = basename($file,'.tif').'.pdf';
	
	@system('cd /var/spool/hylafax/ && sudo /var/spool/hylafax/bin/tiff2pdf -o '. qsa('/tmp/'.$pdf_file) .' '. qsa('/tmp/'.$file));
	unlink('/tmp/'.$file);
	
	if (! file_exists('/tmp/'.$pdf_file)) {
		header('HTTP/1.0 500 Internal Server Error', true, 500);
		header('Status: 500 Internal Server Error' , true, 500);
		header('Content-Type: text/plain');
		die( 'Error. Failed to convert fax to PDF.' );
	}
	
	header('Content-Type: application/pdf');
	header('Content-Disposition: attachment; filename="'.$pdf_file.'"');
	header('Content-Length: ' . (int)@fileSize('/tmp/'.$pdf_file));
	@readFile('/tmp/'.$pdf_file);
	@system('sudo rm /tmp/'.$pdf_file);
}

?>