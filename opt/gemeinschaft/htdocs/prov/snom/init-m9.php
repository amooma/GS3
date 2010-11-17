<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2010, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
* Sebastian Ertz <gemeisnchaft@swastel.eisfair.net>
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

header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );


require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'inc/prov-fns.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );
set_error_handler('err_handler_die_on_err');

function _snomCnfXmlEsc( $str )
{
	return str_replace(
		array('&'    , '<'   , '>'   , '"'   ),
		array('&amp;', '&lt;', '&gt;', '\'\''),
		$str);
}

function _settings_err( $msg='' )
{
	@ob_start();
	echo '<!-- // ', _snomCnfXmlEsc($msg != '' ? str_replace('--','- -',$msg) : 'Error') ,' // -->',"\n";
	if (! headers_sent()) {
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'Content-Length: '. (int)@ob_get_length() );
	}
	@ob_end_flush();
	exit(1);
}

if (gs_get_conf('GS_SNOM_PROV_M9_ACCOUNTS') < 1) {
	gs_log( GS_LOG_DEBUG, "Snom M9 provisioning not enabled" );
	_settings_err( 'Not enabled.' );
}



$requester = gs_prov_check_trust_requester();
if (! $requester['allowed']) {
	_settings_err( 'No! See log for details.' );
}

$mac = preg_replace( '/[^0-9A-F]/', '', strToUpper( @$_REQUEST['mac'] ) );
if (strLen($mac) !== 12) {
	gs_log( GS_LOG_NOTICE, "Snom M9 provisioning: Invalid MAC address \"$mac\" (wrong length)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}
if (hexDec(subStr($mac,0,2)) % 2 == 1) {
	gs_log( GS_LOG_NOTICE, "Snom M9 provisioning: Invalid MAC address \"$mac\" (multicast address)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}
if ($mac === '000000000000') {
	gs_log( GS_LOG_NOTICE, "Snom M9 provisioning: Invalid MAC address \"$mac\" (huh?)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}

# make sure the phone is a Snom-M9:
#
if ( (subStr($mac,0,6) !== '000413') && (subStr($mac,0,6) !== '00087B') ) {
	gs_log( GS_LOG_NOTICE, "Snom M9 provisioning: MAC address \"$mac\" is not a Snom M9 phone" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}

$ua = trim(@$_SERVER['HTTP_USER_AGENT']);
if (preg_match('/^Mozilla\/\d\.\d\s*\(compatible;\s*/i', $ua, $m)) {
	$ua = rTrim(subStr( $ua, strLen($m[0]) ), ' )');
}
gs_log( GS_LOG_DEBUG, "Snom model $ua found." );

if (preg_match('/snom m9/i', $ua, $m))
	$phone_model = 'm9';
else
	$phone_model = 'unknown';

$phone_type = 'snom-'.$phone_model;  # e.g. "snom-m9"
# to be used when auto-adding the phone

gs_log( GS_LOG_DEBUG, "Snom phone \"$mac\" asks for settings (UA: ...\"$ua\") - model: $phone_model" );

$prov_url_snom = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'snom/';


#####################################################################
#  output
#####################################################################
ob_start();
header( 'Content-Type: application/xml; charset=utf-8' );
echo '<','?xml version="1.0" encoding="utf-8"?','>', "\n";
echo '<setting-files>', "\n";
echo '<file url="', $prov_url_snom ,'sw-m9-update.php?mac={mac}" />', "\n";
echo '<file url="', $prov_url_snom ,'settings-m9.php?mac={mac}" />', "\n";
echo '<file url="', $prov_url_snom ,'pb-m9.php?mac={mac}" />', "\n";
echo '</setting-files>', "\n";
if (! headers_sent()) {
	# avoid chunked transfer-encoding
	header( 'Content-Length: '. (int)@ob_get_length() );
}
@ob_flush();

?>