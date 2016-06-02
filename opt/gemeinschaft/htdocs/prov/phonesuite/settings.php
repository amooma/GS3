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

# have a look at
# http://phonesuite.de/hlp/de/ast/ast/auto_configuration.htm

define( 'GS_VALID', true );  /// this is a parent file

header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );


require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'inc/prov-fns.php' );
set_error_handler('err_handler_die_on_err');

if (! gs_get_conf('GS_PHONESUITE_PROV_ENABLED')) {
	gs_log( GS_LOG_DEBUG, "Phonesuite provisioning not enabled" );
	_settings_err( 'Not enabled.' );
}


function build_taco_string($assoc_arr) { 
	$content = ""; 
	foreach ($assoc_arr as $key=>$elem) { 
		$content .= "[".$key."]\n"; 
		foreach ($elem as $key2=>$elem2) { 
			if(is_array($elem2)) 
			{ 
				for($i=0;$i<count($elem2);$i++) 
				{ 
					$content .= $key2."[]=".$elem2[$i]."\n"; 
				} 
			} 
			else if($elem2==="") $content .= $key2."=\n"; 
			else $content .= $key2."=".$elem2."\n"; 
		} 
	} 

	return $content;
}


require_once( GS_DIR .'inc/db_connect.php' );


$db = gs_db_master_connect();
if (! $db) {
	gs_log( GS_LOG_WARNING, "Phonesuite asks for settings - Could not connect to DB" );
	_settings_err( 'Could not connect to DB.' );
}

# is it a valid user?
#
$user = @$_REQUEST['user'] ? @$_REQUEST['user'] : @$_REQUEST['sudo'];
$user_id = (int)$db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`="'. $user .'"' );
if ($user_id < 1)
	$user_id = 0;

if ($user_id < 1) {
	die("User " . $user . " does not exist");
}

# get host for user
#
$host = @gs_prov_get_host_for_user_id( $db, $user_id );
if (! $host) {
	_settings_err( 'Failed to find host.' );
}
$pbx = $host;  # $host might be changed if SBC configured


# who is requesting configuration?
#
$user = @gs_prov_get_user_info( $db, $user_id );
if (! is_array($user)) {
	_settings_err( 'DB error.' );
}


#####################################################################
#  General
#####################################################################

$settings = Array(
	'AsteriskServer_IP' 		=> gs_get_conf('GS_PHONESUITE_PROV_HOST'),
	'AsteriskServer_Username'	=> gs_get_conf('GS_PHONESUITE_PROV_AMI_USERNAME'),
	'AsteriskServer_Pwd'		=> gs_get_conf('GS_PHONESUITE_PROV_AMI_SECRET'),
	'AsteriskLine_Channel'		=> 'SIP/' . $user['name'],
	'AsteriskLine_PhoneNumber'	=> $user['name'],
	'AsteriskLine_Username'		=> $user['callerid'],
	'AsteriskLine_AutoAnswerMode'	=> gs_get_conf('GS_PHONESUITE_PROV_AUTO_ANSWER_MODE', 0),
);

if(gs_get_conf('GS_PHONESUITE_PROV_LICENSEKEY') !== null) {
	$settings = array_merge($settings, Array(
		'LicenseKey'			=> gs_get_conf('GS_PHONESUITE_PROV_LICENSEKEY'),
		'LicenseeName'			=> gs_get_conf('GS_PHONESUITE_PROV_LICENSEE'),
	));
}

$output = build_taco_string(Array('ALL' => $settings), true);

header( 'Content-Type: text/x-ini' );
header( 'Content-Disposition: attachment;filename=phonesuite-config-'. $user['user'] .'.taco' );
print $output;

unset($output);
unset($settings);

?>
