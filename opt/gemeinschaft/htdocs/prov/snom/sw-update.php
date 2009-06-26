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
require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );
set_error_handler('err_handler_die_on_err');
require_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/cron-rule.php' );


$allow_update    = gs_get_conf('GS_SNOM_PROV_FW_UPDATE');
//$allow_beta      = gs_get_conf('GS_SNOM_PROV_FW_BETA'  );  # no longer used
$allow_v_6_to_7  = gs_get_conf('GS_SNOM_PROV_FW_6TO7'  );

$firmware_path   = '/opt/gemeinschaft/htdocs/prov/snom/sw/';
$firmware_url    = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'snom/sw/';

header( 'Content-Type: text/plain; charset=utf-8' );
# the Content-Type header is ignored by the Snom
header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );

if (! gs_get_conf('GS_SNOM_PROV_ENABLED')) {
	gs_log( GS_LOG_DEBUG, "Snom provisioning not enabled" );
	die();
}
if (! $allow_update) {
	gs_log( GS_LOG_DEBUG, "Snom firmware update not enabled" );
	die();
}

$mac  = preg_replace('/[^0-9A-F]/', '', strToUpper(@$_REQUEST['m']));
if (strLen($mac) != 12) {
	gs_log( GS_LOG_DEBUG, "Bad MAC address \"$mac\"" );
	die();
}


$user = preg_replace('/[^a-z0-9_\-]/i', '', @$_REQUEST['u']);

$ua = trim( @$_SERVER['HTTP_USER_AGENT'] );
if (preg_match('/snom([1-9][0-9]{2})/i', $ua, $m)) {  # e.g. "snom360"
	$phone_type = $m[1];
} else {
	gs_log( GS_LOG_DEBUG, "Could not recognize User-Agent" );
	die();
}
$phone_model = 'snom'.$phone_type;

//gs_log( GS_LOG_DEBUG, "Snom $mac ($phone_type, user $user) checks firmware" );


function _generate_settings( $model, $appl, $rtfs, $lnux )
{
	global
		$firmware_url, $firmware_path,
		/*$allow_beta,*/ $mac, $phone_type, $user;
		
	$file = '';
	if     (!empty($appl)) $file = $model.'-'.$appl.'.bin'  ;
	elseif (!empty($rtfs)) $file = $model.'-'.$rtfs         ;
	elseif (!empty($lnux)) $file = $model.'-'.$lnux.'-l.bin';
	
	if ($file != '') {
		if (subStr($firmware_path,-1) != '/') $firmware_path .= '/';
		$realfile = $firmware_path . $file;
		if (! file_exists($realfile) || ! is_readable($realfile)) {
			# It's important to make sure we don't point the phone to a
			# non-existent file or else the phone needs manual interaction
			# (something like "File not found. Press any key to continue.")
			gs_log( GS_LOG_WARNING, "File \"$realfile\" not found" );
		}
		else {
			$url = $firmware_url . rawUrlEncode($file);
			gs_log( GS_LOG_NOTICE, "Snom $mac ($phone_type, user $user): Update file: \"$file\"" );
			//$ob = 'pnp_config$: off' ."\n";
			$ob = 'firmware: '. $url ."\n";
			if (! headers_sent()) {
				header( 'Content-Length: '. strLen($ob) );
				# avoid chunked transfer-encoding
			}
			echo $ob;
		}
	}
	exit();
}

function _snom_normalize_version( $appvers )
{
	$tmp = explode('.', $appvers);
	$vmaj = str_pad((int)@$tmp[0], 2, '0', STR_PAD_LEFT);
	$vmin = str_pad((int)@$tmp[1], 2, '0', STR_PAD_LEFT);
	$vsub = str_pad((int)@$tmp[2], 2, '0', STR_PAD_LEFT);
	return $vmaj.'.'.$vmin.'.'.$vsub;
}

function _snomAppCmp( $appv1, $appv2 )
{
	//$appv1 = _snom_normalize_version( $appv1 );  # we trust it has been normalized!
	$appv2 = _snom_normalize_version( $appv2 );
	return strCmp($appv1, $appv2);
}




$sw_info = explode(';', $ua);
//gs_log( GS_LOG_DEBUG, "Firmware info: \n". print_r($sw_info, true) );

$app            = false;
$rootfs_jffs2   = false;
$rootfs_ramdisk = false;
$linux          = false;

foreach ($sw_info as $k => $v)
{
	if (preg_match('/^ *'.preg_quote($phone_model).'-SIP ([0-9.]+)/', $v, $m)) {
		$app = $m[1];
		continue;
	}
	
	$tmp = strStr($v, $phone_model.' linux ');
	if (!empty($tmp)) {
		$linux = $tmp;
		$linux = subStr($linux, 14, -1);
		continue;
	}
	
	$tmp = strStr($v, $phone_model.' jffs2 v');
	if (!empty($tmp)) {
		$rootfs_jffs2 = $tmp;
		$rootfs_jffs2 = subStr($rootfs_jffs2, 15);
		$old_rootfs = $rootfs_jffs2;
		continue;
	}
	
	$tmp = strStr($v, 'ramdisk');
	if (!empty($tmp)) {
		$rootfs_ramdisk = $tmp;
		$old_rootfs = $rootfs_ramdisk;
		continue;
	}
}

if (! preg_match('/^[1-9]/', $app)) {
	gs_log( GS_LOG_DEBUG, "Could not recognize app version in UA \"$ua\"" );
	die();
}
gs_log( GS_LOG_DEBUG, "Snom $mac ($phone_type, user $user) has app \"$app\"" );


# see update guides at
# http://wiki.snom.com/Snom360/Firmware
# http://wiki.snom.com/Snom370/Firmware
# http://wiki.snom.com/Firmware/V7/Update_Description


$a = _snom_normalize_version( (!empty($app)) ? $app : '0.0.0' );

$ready_for_v6 =
	   _snomAppCmp($a, '6'  )>=0
	||(_snomAppCmp($a, '5.5')>=0
	&& empty($rootfs_ramdisk)
	&& $rootfs_jffs2 >= '3.36'
	&& $linux >= '3.25'
	);
$ready_for_v6_to_7 =
	   $allow_v_6_to_7
	&& _snomAppCmp($a, '6.5.10')>=0
	&& _snomAppCmp($a, '7'     )< 0
	&& empty($rootfs_ramdisk)
	&& $rootfs_jffs2 >= '3.36'
	&& $linux >= '3.38';


# linux
#
if (_snomAppCmp($a, '7'     )<0) {
	if (! empty($linux)) {
		if ($linux < '3.25') {
			gs_log( GS_LOG_NOTICE, "Phone $mac: Please upgrade the phone's linux from $linux to 3.38" );
			exit(0);
		}
	}
}

# rootfs
#
if (_snomAppCmp($a, '7'     )<0) {
	if (! empty($rootfs_ramdisk)) {
		gs_log( GS_LOG_NOTICE, "Phone $mac: Please upgrade the phone's rootfs from ramdisk to Jffs2-3.36" );
		exit(0);
	}
	elseif (! empty($rootfs_jffs2)) {
		if ($rootfs_jffs2 < '3.36') {
			gs_log( GS_LOG_NOTICE, "Phone $mac: Please upgrade the phone's rootfs from $rootfs_jffs2 to Jffs2-3.36" );
			exit(0);
		}
	}
}


# application
#

if (_snomAppCmp($a, '5'     )<0) {
	gs_log( GS_LOG_NOTICE, "Phone $mac: Please upgrade the firmware from $a to 6.5 or higher" );
}

$db = gs_db_master_connect();
if (! $db) {
	gs_log( GS_LOG_WARNING, "Snom phone asks for firmware - Could not connect to DB" );
	exit(0);
}

$phone_id = (int)$db->executeGetOne(
	'SELECT `id` '.
	'FROM `phones` '.
	'WHERE `mac_addr`=\''. $db->escape($mac) .'\''
	);
if (! $phone_id) {
	gs_log( GS_LOG_WARNING, "DB error" );
	exit(0);
}

# do we have to upgrade to a default version?
#
$fw_was_upgraded_manually = (int)$db->executeGetOne(
	'SELECT `fw_manual_update`'.
	'FROM `phones` '.
	'WHERE `id`='. $phone_id
	);
if ($fw_was_upgraded_manually) {
	gs_log( GS_LOG_DEBUG, "Firmware was upgraded \"manually\". Not scheduling an upgrade." );
} elseif ($a === null) {
	gs_log( GS_LOG_DEBUG, "Phone did not report its current firmware version." );
} else {
	if(! $ready_for_v6_to_7) {
		$sw_default_vers = _snom_normalize_version(trim(gs_get_conf('GS_SNOM_PROV_FW_DEFAULT_'.$phone_type)));
		$sw_default_name = $sw_default_vers;
	} else {
		$sw_ver = trim(gs_get_conf('GS_SNOM_PROV_FW_FROM6TO7_'.$phone_type));
		$sw_default_vers = _snom_normalize_version($sw_ver);
		$sw_default_name = "from6to7-".$sw_default_vers;
	}
	if (in_array($sw_default_vers, array(null,false,''), true)) {
		if(! $ready_for_v6_to_7) {
			gs_log( GS_LOG_DEBUG, "No default firmware version set in config file" );
		} else {
			gs_log( GS_LOG_DEBUG, "No from6to7 upgrade firmware version set in config file" );
		}
	} else {
		if ('x'.$a != 'x'.$sw_default_vers) {
			gs_log( GS_LOG_NOTICE, "The firmware version ($a) differs from the available version ($sw_default_name), scheduling an upgrade ..." );
			# simply add a provisioning job to the database. this is done to be clean and we can trace the job.
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
					'\'' . $db->escape($sw_default_name) . '\' '.
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
if (! $rs) {
	gs_log( GS_LOG_WARNING, "DB error" );
	exit(0);
}
while ($job = $rs->fetchRow()) {
	if ($job['running']) {
		gs_log( GS_LOG_NOTICE, "Phone $mac: A firmware job is already running" );
		exit(0);
	}
	
	# check cron rule
	$c = new CronRule();
	$ok = $c->set_rule( $job['minute'] .' '. $job['hour'] .' '. $job['day'] .' '. $job['month'] .' '. $job['dow'] );
	if (! $ok) {
		gs_log( GS_LOG_WARNING, "Phone $mac: Job ".$job['id']." has a bad cron rule (". $c->err_msg ."). Deleting ..." );
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
	gs_log( GS_LOG_DEBUG, "Phone $mac: Job ".$job['id'].": Rule matches" );
	
	$new_vers = $job['data'];
	if (subStr($new_vers,0,9)=='from6to7-') {
		$new_vers = _snom_normalize_version( subStr($new_vers,9) );
		$new_app = 'from6to7-'.$new_vers;
	} else {
		$new_vers = _snom_normalize_version( $new_vers );
		$new_app = $new_vers;
	}

	if (subStr($new_vers,0,2)=='00') {
		gs_log( GS_LOG_NOTICE, "Phone $mac: Bad new app vers. $new_app " . subStr($job['data'],0,8) );
		$db->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']).' AND `running`=0' );
		continue;
	}
	if ('x'.$new_app == 'x'.$a) {
		gs_log( GS_LOG_NOTICE, "Phone $mac: App $a == $new_app" );
		$db->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']).' AND `running`=0' );
		continue;
	}
	
	gs_log( GS_LOG_NOTICE, "Phone $mac: Upgrade app $a -> $new_app" );
	$db->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']) );
	_generate_settings( $phone_model, $new_app, null, null );
	
	break;
}


?>
