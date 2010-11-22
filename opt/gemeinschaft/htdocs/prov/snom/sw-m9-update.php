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
* Sebastian Ertz <gemeinschaft@swastel.eisfair.net>
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
require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );
require_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/cron-rule.php' );
set_error_handler('err_handler_die_on_err');


$firmware_path   = '/opt/gemeinschaft/htdocs/prov/snom/sw-m9/';
$firmware_url    = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'snom/sw-m9/';

header( 'Content-Type: text/plain; charset=utf-8' );
header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );

function _snom_normalize_version( $appvers )
{
	$tmp = explode('.', $appvers);
	$vmaj = str_pad((int)@$tmp[0], 2, '0', STR_PAD_LEFT);
	$vmin = str_pad((int)@$tmp[1], 2, '0', STR_PAD_LEFT);
	$vsub = str_pad((int)@$tmp[2], 2, '0', STR_PAD_LEFT);
	return $vmaj.'.'.$vmin.'.'.$vsub;
}

if (gs_get_conf('GS_SNOM_PROV_M9_ACCOUNTS') < 1) {
	gs_log( GS_LOG_DEBUG, "Snom M9 provisioning not enabled" );
	die();
}
if (! gs_get_conf('GS_SNOM_PROV_M9_FW_UPDATE')) {
	gs_log( GS_LOG_DEBUG, "Snom m9 firmware update not enabled" );
	die();
}

$mac = preg_replace( '/[^0-9A-F]/', '', strToUpper( @$_REQUEST['mac'] ) );
if (strLen($mac) !== 12) {
	gs_log( GS_LOG_NOTICE, "Snom M9 provisioning: Invalid MAC address \"$mac\" (wrong length)" );
	# don't explain this to the users
	die();
}
if (hexDec(subStr($mac,0,2)) % 2 == 1) {
	gs_log( GS_LOG_NOTICE, "Snom M9 provisioning: Invalid MAC address \"$mac\" (multicast address)" );
	# don't explain this to the users
	die();
}
if ($mac === '000000000000') {
	gs_log( GS_LOG_NOTICE, "Snom M9 provisioning: Invalid MAC address \"$mac\" (huh?)" );
	# don't explain this to the users
	die();
}

# make sure the phone is a Snom-M9:
#
if ( (subStr($mac,0,6) !== '000413') ) {
	gs_log( GS_LOG_NOTICE, "Snom M9 provisioning: MAC address \"$mac\" is not a Snom M9 phone" );
	# don't explain this to the users
	die();
}

$ua = trim(@$_SERVER['HTTP_USER_AGENT']);
if (! preg_match('/^Mozilla/i', $ua)
||  ! preg_match('/snom\sm9/i', $ua) ) {
	gs_log( GS_LOG_WARNING, "Phone with MAC \"$mac\" (Snom) has invalid User-Agent (\"". $ua ."\")" );
	# don't explain this to the users
	die();
}
if (preg_match('/^Mozilla\/\d\.\d\s*\(compatible;\s*/i', $ua, $m)) {
	$ua = rTrim(subStr( $ua, strLen($m[0]) ), ' )');
}

# get firmware version
$fw_vers = (preg_match('/(\d+\.\d+\.\d+)/i', $ua, $m))
	? $m[0] : '0.0.0';
$fw_vers_nrml = _snom_normalize_version( $fw_vers );


# connect to db
$db = gs_db_master_connect();
if (! $db) {
	gs_log( GS_LOG_WARNING, "Snom M9 phone asks for firmware  - Could not connect to DB" );
	exit(0);
}

# firmware update
#

# get phone_id
$phone_id = (int)$db->executeGetOne( 'SELECT `id` FROM `phones` WHERE `mac_addr`=\''. $db->escape($mac) .'\'' );
if (! $phone_id) {
	gs_log( GS_LOG_WARNING, "DB error" );
	exit(0);
}

# do we have to update to a default version?
#
$fw_was_upgraded_manually = (int)$db->executeGetOne(
	'SELECT `fw_manual_update` '.
	'FROM `phones` '.
	'WHERE `id`='. $phone_id
	);
if ($fw_was_upgraded_manually) {
	gs_log( GS_LOG_DEBUG, "Phone $mac: Firmware was upgraded \"manually\". Not scheduling an upgrade." );
} else {
	$fw_default_vers = _snom_normalize_version(trim(gs_get_conf('GS_SNOM_PROV_M9_FW_DEFAULT')));
	if (in_array($fw_default_vers, array(null, false,''), true)) {
		gs_log( GS_LOG_DEBUG, "Phone $mac: No default firmware set in config file" );
	} elseif (subStr($fw_default_vers,0,2) === '00') {
		gs_log( GS_LOG_DEBUG, "Phone $mac: Bad default firmware set in config file" );
	} else {
		if ($fw_vers_nrml != $fw_default_vers) {
			gs_log( GS_LOG_NOTICE, "Phone $mac: The Firmware version ($fw_vers_nrml) differs from the default version ($fw_default_vers), scheduling an upgrade ..." );
			# simply add a provisioning job to the database. This is done to be clean and we cann trace the job.
			$ok = $db->execute(
				'INSERT INTO `prov_jobs` ('.
					'`id`, '.
					'`inserted`, '.
					'`running`, '.
					'`trigger`, '.
					'`phone_id`, '.
					'`type`, '.
					'`immediate`, '.
					'`minute`, '.
					'`hour`, '.
					'`day`, '.
					'`month`, '.
					'`dow`, '.
					'`data` '.
				') VALUES ('.
					'NULL, '.
					((int)time()) .', '.
					'0, '.
					'\'client\', '.
					((int)$phone_id) .', '.
					'\'firmware\', '.
					'0, '.
					'\'*\', '.
					'\'*\', '.
					'\'*\', '.
					'\'*\', '.
					'\'*\', '.
					'\''. $db->escape($fw_default_vers) .'\''.
				')'
			);
		}
	}
}


# check provisioning jobs
#
$rs = $db->execute(
	'SELECT `id`, `running`, `minute`, `hour`, `day`, `month`, `dow`, `data` '.
	'FROM `prov_jobs` '.
	'WHERE `phone_id`='.$phone_id.' AND `type`=\'firmware\' '.
	'ORDER BY `running` DESC, `inserted`' );
while ($job = $rs->fetchRow()) {
	if ($job['running']) {
		break;
	}
		
	# check cron rule
	$c = new CronRule();
	$ok = $c->set_Rule( $job['minute'] .' '. $job['hour'] .' '. $job['day'] .' '. $job['month'] .' '. $job['dow'] );
	if (! $ok) {
		gs_log( GS_LOG_WARNING, "Phone $mac: Job ".$job['id']." has a bad cron rule (". $c->errMsg ."). Deleting ..." );
		$db->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']).' AND `running`=0' );
		unset($c);
		continue;
	}
	if (! $c->validate_time()) {
		gs_log( GS_LOG_DEBUG, "Phone $mac: Job ".$job['id'].": Rule does not match" );
		unset($c);
		continue;
	}
	unset($c);
	gs_log( GS_LOG_DEBUG, "Phone $mac: Job ".$job['id'].": Rule match" );
	
	$fw_new_vers = _snom_normalize_version( $job['data'] );
	if (subStr($fw_new_vers,0,2)=='00') {
		gs_log( GS_LOG_NOTICE, "Phone $mac: Bad new fw version $fw_new_vers" );
		$db->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']).' AND `running`=0' );
		continue;
	}
	$firmware_name = 'm9-'.$fw_new_vers.'.bin';
	if ( ! file_exists($firmware_path.$firmware_name) || ! is_readable($firmware_path.$firmware_name) ) {
		gs_log( GS_LOG_NOTICE, "Phone $mac: ".$firmware_path.$firmware_name." not exits or not readable" );
		$db->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']).' AND `running`=0' );
		continue;
	}
	if ( $fw_new_vers == $fw_vers_nrml ) {
		gs_log( GS_LOG_NOTICE, "Phone $mac: FW $fw_vers_nrml == $fw_new_vers" );
		$db->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']).' AND `running`=0' );
		continue;
	}
	
	gs_log( GS_LOG_NOTICE, "Phone $mac: Upgrade FW $fw_vers_nrml -> $fw_new_vers" );

	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>' ,"\n";
	echo '<firmware-settings>' ,"\n";
	echo '<firmware>', $firmware_url, $firmware_name, '</firmware>' ,"\n";
	echo '</firmware-settings>' ,"\n";

	if (! headers_sent()) {
		header( 'Content-Type: application/xml' );
		header( 'Content-Length: '. (int)@ob_get_length() );
	}
	@ob_end_flush();
	
	exit(0);
}


?>