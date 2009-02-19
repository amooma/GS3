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


$file = trim(@$_REQUEST['file']);
$raw  = trim(@$_REQUEST['raw']);

if (!$file) $file = $raw;

if (!file) {
	header('HTTP/1.0 403 Forbidden', true, 403);
	header('Status: 403 Forbidden' , true, 403);
	header('Content-Type: text/plain');
	die( 'Unauthorized.' );
}

$jobs_rec = fax_get_jobs_rec();
$authorized = FALSE;

if (is_array($jobs_rec)) {
	
	foreach ($jobs_rec as $key => $row) {
		if ($row[11] == $_SESSION['sudo_user']['name']) { 
			if  ($row[4]==$file)  {
				$authorized = TRUE;
			}
		} else {
			unset($jobs_rec[$key]);
		}
	}
}

if (!$authorized) {
	header('HTTP/1.0 403 Forbidden', true, 403);
	header('Status: 403 Forbidden' , true, 403);
	header('Content-Type: text/plain');
	die( 'Unauthorized.' );
}

if (! fax_download($file)) {
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
	die( 'Error. Failed to retrieve fax.' );
}

if ($raw) {
	header('Content-Type: image/tiff');
	header('Content-Disposition: attachment; filename="'.$file.'"');
	header('Content-Length: ' . (int)@fileSize('/tmp/'.$file));
	@readFile('/tmp/'.$file);
	@unlink('/tmp/'.$file);
} else {

	$pdf_file = basename($file,".tif").'.pdf';

	@system('cd /var/spool/hylafax/ && /var/spool/hylafax/bin/tiff2pdf -o '. qsa('/tmp/'.$pdf_file) .' '. qsa('/tmp/'.$file));
	unlink('/tmp/'.$file);

	if (!file_exists('/tmp/'.$pdf_file)) {
		header('HTTP/1.0 500 Internal Server Error', true, 500);
		header('Status: 500 Internal Server Error' , true, 500);
		header('Content-Type: text/plain');
		die( 'Error. Failed to convert fax. Try "raw" instead.' );
	}


	header('Content-Type: application/pdf');
	header('Content-Disposition: attachment; filename="'.$pdf_file.'"');
	header('Content-Length: ' . (int)@fileSize('/tmp/'.$pdf_file));
	@readFile('/tmp/'.$pdf_file);
	@unlink('/tmp/'.$pdf_file);
}

?>