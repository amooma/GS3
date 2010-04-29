<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
*
* $Revision$
*
* Copyright 2007-2010, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
* 
* Author: Daniel Scheller <scheller@loca.net>
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

header( 'Content-Type: text/plain; charset=utf-8' );
header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );

require_once( dirname(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'inc/prov-fns.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );
set_error_handler('err_handler_die_on_err' );

//---------------------------------------------------------------------------

$POLYCOM_BOOTROM_IP300IP500 = 'bootrom_ip300ip500.ld';
$POLYCOM_BOOTROM_DEFAULT    = 'bootrom_412.ld';

//---------------------------------------------------------------------------

function _settings_err( $msg='' )
{
	@ob_end_clean();
	@ob_start();

	echo '<!-- // ', ($msg != '' ? str_replace('--','- -',$msg) : 'Error') ,' // -->',"\n";
	if(!headers_sent())
	{
		header('Content-Type: text/plain; charset=utf-8');
		header('Content-Length: '. (int)@ob_get_length());
	}

	@ob_end_flush();
	exit(1);
}

//---------------------------------------------------------------------------

$ua = trim(@$_SERVER['HTTP_USER_AGENT']);

if (preg_match('/PolycomSoundPointIP/', $ua))
{
	$phone_model = ((preg_match('/PolycomSoundPointIP\-SPIP_(\d+)\-UA\//', $ua, $m)) ? $m[1] : 'unknown');
	$phone_type = 'polycom-spip-'. $phone_model;
} else if (preg_match('/PolycomSoundStationIP/', $ua)) {
	$phone_model = ((preg_match('/PolycomSoundStationIP\-SSIP_(\d+)\-UA\//', $ua, $m)) ? $m[1] : 'unknown');
	$phone_type = 'polycom-ssip-'. $phone_model;
} else {
	gs_log( GS_LOG_WARNING, "Phone with MAC \"$mac\" (Polycom) has invalid User-Agent (\"". $ua ."\")" );
	//--- don't explain this to the users
	_settings_err( 'No! See log for details.' );
}

switch($phone_model)
{
	case '300' :
	case '500' :
		$POLYCOM_BOOTROM_FILE = $POLYCOM_BOOTROM_IP300IP500;
		break;
	default :
		$POLYCOM_BOOTROM_FILE = $POLYCOM_BOOTROM_DEFAULT;
}

header( 'Location: '.$POLYCOM_BOOTROM_FILE );
exit();

?>