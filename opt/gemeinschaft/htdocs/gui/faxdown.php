<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1873 $
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

require_once( GS_DIR .'inc/cn_hylafax.php' );
//start_session();

$file = trim(@$_REQUEST['file']);

$user_id = $ext = @$_SESSION['sudo_user']['id'];

if ($file) {

	if (fax_download($file)) {
		$fnamel_pre=strlen(strrchr($file,"."));
		$fnamel_all=strlen($file);
		$fname = substr($file,0,$fnamel_all-$fnamel_pre);
		system("/opt/gemeinschaft/sbin/fax_to_pdf /tmp/".$file." /tmp/".$fname.".pdf");
		
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="'.$fname.'.pdf"');
		header('Content-Length: ' . filesize("/tmp/".$fname.".pdf"));
	
		readfile("/tmp/".$fname.".pdf");
	}
} else {
	header( 'HTTP/1.0 404 Not Found', true, 404 );
	header( 'Status: 404 Not Found' , true, 404 );
	header( 'Content-Type: text/plain' );
	die( 'Not found.' );
}



?>