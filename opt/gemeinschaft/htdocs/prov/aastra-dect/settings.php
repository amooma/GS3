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
* Author: Henning Holtschneider <henning@loca.net> - LocaNet oHG
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

$ext = trim( @$_REQUEST['ext'] );
	
header( 'Content-Type: text/plain; charset=utf-8' );
header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );

require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'inc/prov-fns.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );
include_once( GS_DIR .'inc/aastra-fns.php' );
set_error_handler('err_handler_die_on_err');

function _settings_err( $msg='' )
{
	@ob_end_clean();
	@ob_start();
	echo '# Error: ', $msg, "\n";
	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'Content-Length: '. (int)@ob_get_length() );

	@ob_end_flush();
	exit(1);
}

function setting( $name, $val )
{
	echo $name, "=", $val, "\n";
}

if (! gs_get_conf('GS_AASTRA_PROV_ENABLED')) {
	gs_log( GS_LOG_DEBUG, "Aastra provisioning not enabled" );
	_settings_err( 'Not enabled.' );
}

$requester = gs_prov_check_trust_requester();
if (! $requester['allowed']) {
	_settings_err( 'No! See log for details.' );
}

require_once( GS_DIR .'inc/db_connect.php' );
require_once( GS_DIR .'inc/nobody-extensions.php' );
include_once( GS_DIR .'inc/gs-fns/gs_prov_params_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_prov_params_get.php' );

# phone requests global settings
if ( strlen($ext) < 1 ) {

	gs_log( GS_LOG_DEBUG, "Aastra OMM asks for global settings" );

	ob_start();

	setting("UDS_CommonUpdateInterval", 6);
	setting("UDS_UseExternalUsers", "YES");
	setting("UD_SosNumber", "112");
	setting("UD_ManDownNumber", "112");
	setting("UD_Pin", "1234");
	setting("UD_UpdateInterval", 4);
	setting("UD_Locatable", "FALSE");
	setting("UD_LocatingPermission", "FALSE");
	setting("UD_Tracking", "FALSE");
	setting("UD_AllowMsgSend", "FALSE");
	setting("UD_AllowVcardSend", "FALSE");
	setting("UD_AllowVcardRecv", "FALSE");
	setting("UD_KeepLocalDir", "FALSE");

	@ob_flush();
	return;
}

$db = gs_db_master_connect();
if (! $db) {
	gs_log( GS_LOG_WARNING, "Aastra phone asks for settings - Could not connect to DB" );
	_settings_err( 'Could not connect to DB.' );
}

gs_log( GS_LOG_DEBUG, "Aastra OMM asks for user settings of extension " . $ext );

$user_id = (int)$db->executeGetOne( 'SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`=\''. $db->escape($ext) .'\'' );

if ($user_id < 1) {
	gs_log( GS_LOG_WARNING, "Unknown user " . $user );
	_settings_err( 'Unknown user' );
}

$pin = $db->executeGetOne( 'SELECT `pin` FROM `users` WHERE `id`='. $user_id );
if (trim($pin)=='') {
	gs_log( GS_LOG_WARNING, "Unable to read PIN for user " . $user );
	_settings_err( 'Unknown user' );
}

$user = @gs_prov_get_user_info( $db, $user_id );
$displayname = $user["lastname"];
if (strlen($user["firstname"]) > 0)
	$displayname = substr($user["firstname"], 0, 1) . ". " . $displayname;
	
setting("UD_PinDel", "FALSE");
setting("UD_Pin", $pin);
setting("UD_UpdateInterval", 1);
setting("UD_Number", $ext);
setting("UD_Name", $displayname);
setting("UD_SosNumber", "112");
setting("UD_ManDownNumber", "112");
setting("UD_SipAccount", $ext);
setting("UD_SipPassword", $user["secret"]);
setting("UD_Locatable", "FALSE");
setting("UD_LocatingPermission", "FALSE");
setting("UD_Tracking", "FALSE");
setting("UD_AllowMsgSend", "FALSE");
setting("UD_AllowVcardSend", "FALSE");
setting("UD_AllowVcardRecv", "FALSE");
setting("UD_KeepLocalDir", "FALSE");

# store the user's current IP address in the database:
#
if (! @gs_prov_update_user_ip( $db, $user_id, $requester['phone_ip'] )) {
	gs_log( GS_LOG_WARNING, 'Failed to store current IP addr of user ID '. $user_id );
}



#####################################################################
if (! headers_sent()) {
	# avoid chunked transfer-encoding
	header( 'Content-Length: '. @ob_get_length() );
}
@ob_flush();

?>