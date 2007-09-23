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
set_error_handler('err_handler_die_on_err');


$allow_update    = false;  //FIXME - needs config param
$allow_beta      = false;  //FIXME - needs config param
$allow_v_6_to_7  = false;  //FIXME - needs config param

$allow_only_specified_mac_addrs = false;
$allowed_mac_addrs = array(
	//'00:04:13:23:08:A4',
	//'00:04:13:00:00:02',
	//'00:04:13:00:00:03'
);


$firmware_url_snom          = 'http://provisioning.snom.com/download/';
$firmware_url_snom_from6to7 = 'http://provisioning.snom.com/from6to7/';
$firmware_path              = '/opt/gemeinschaft/htdocs/prov/snom/sw/';

$firmware_url = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT==80 ? '' : (':'. GS_PROV_PORT)) . GS_PROV_PATH .'snom/sw/';


header( 'Content-Type: text/plain; charset=utf-8' );
# the Content-Type header is ignored by the Snom
header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );

if (! gs_get_conf('GS_SNOM_ENABLED', true)) {
	gs_log( GS_LOG_DEBUG, "Snom provisioning not enabled" );
	die( 'Not enabled.' );
}
if (! $allow_update) {
	gs_log( GS_LOG_DEBUG, "Snom firmware update not enabled" );
	die( 'Not enabled.' );
}

$mac  = preg_replace('/[^0-9A-F]/', '', strToUpper(@$_REQUEST['m']));
/*
if (strLen($mac) != 12) {
	gs_log( GS_LOG_DEBUG, "Bad MAC address \"$mac\"" );
	die();
}
*/
if ($allow_only_specified_mac_addrs) {
	$mac_allowed = false;
	foreach ($allowed_mac_addrs as $allowed_mac) {
		$allowed_mac = preg_replace('/[^0-9A-F]/', '', strToUpper($allowed_mac));
		if ($allowed_mac === $mac) {
			$mac_allowed = true;
			break;
		}
	}
	if (! $mac_allowed) {
		gs_log( GS_LOG_DEBUG, "MAC address not allowed to upgrade firmware" );
		die();
	}
}


$user = preg_replace('/[^a-z0-9_\-]/i', '', @$_REQUEST['u']);

$ua = trim( @$_SERVER['HTTP_USER_AGENT'] );
if (preg_match('/snom([1-9][0-9]{2})/i', $ua, $m)) {  # i.e. "snom360"
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
		$firmware_url_snom, $firmware_url_snom_from6to7,
		$firmware_url, $firmware_path,
		$allow_beta, $mac, $phone_type, $user;
		
	$file = '';
	if (!empty($appl)) {
		/*
		if (! $allow_beta)
                           $file = $model.'-'.$appl.'-beta-SIP-j.bin';
		else
		*/
                           $file = $model.'-'.$appl.'.bin';
	}
	elseif (!empty($rtfs)) $file = $model.'-'.$rtfs;
	elseif (!empty($lnux)) $file = $model.'-'.$lnux.'-l.bin';
	
	if ($file != '') {
		if (subStr($firmware_path,-1) != '/') $firmware_path .= '/';
		$realfile = $firmware_path . $file;
		if (! file_exists($realfile) || ! is_readable($realfile)) {
			# It's important to make sure we don't point the phone to a
			# non-existent file or else the phone needs manual interaction
			# (something like "File not found. Press any key to continue.")
			
			# special directories for
			# ...-3.38-l.bin
			# ...-update6to7-7.1.6-bf.bin
			if (preg_match('/-3\.38-l\.bin$/i', $file)
			||  preg_match('/-update6to7-7\.1\.6-bf\.bin$/i', $file))
				$wget_url = $firmware_url_snom_from6to7 . $file;
			else
				$wget_url = $firmware_url_snom          . $file;
			gs_log( GS_LOG_WARNING, "Please  cd ". escapeShellArg($firmware_path) ." && wget ". escapeShellArg($wget_url) );
		} else {
			$url = $firmware_url . rawUrlEncode($file);
			gs_log( GS_LOG_NOTICE, "Snom $mac ($phone_type, user $user): Update file: \"$file\"" );
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
	$tmp = strStr($v, $phone_model.'-SIP ');
	if (!empty($tmp)) {
		$app = $tmp;
		if (@$app[strLen($app)-1] === ')')
			$app = subStr($app, 12, -1);
		else
			$app = subStr($app, 12);
		$app = preg_replace('/[^0-9.]/', '', $app);
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

$ready_for_v6 =     _snomAppCmp($a, '6'  )>=0
                ||( _snomAppCmp($a, '5.5')>=0
                &&  empty($rootfs_ramdisk)
                &&  $rootfs_jffs2 >= '3.36'
                &&  $linux >= '3.25'
                );
$ready_for_v6_to_7 =    $allow_v_6_to_7
                    && _snomAppCmp($a, '6.5.10')>=0
                    && _snomAppCmp($a, '7'     )< 0
                    && empty($rootfs_ramdisk)
                    && $rootfs_jffs2 >= '3.36'
                    && $linux >= '3.38';


#################################################################
#  application
#################################################################

$new_app = '';
if (! empty($app))
{
	$a = _snom_normalize_version( $app );
	
	if (! $allow_beta)
	{
		if         (_snomAppCmp($a, '5'     )<0)  $new_app = '5.5a-SIP-j';
		elseif ($ready_for_v6)
		{
			if     (_snomAppCmp($a, '6.5.10')<0)  $new_app = '6.5.10-SIP-j';
			elseif (_snomAppCmp($a, '7')>=0)
			{
				if (_snomAppCmp($a, '7.1.6' )<0)  $new_app = '7.1.6-SIP-f';
			}
		}
	} else
	{
		if         (_snomAppCmp($a, '5'     )<0)  $new_app = '5.5a-SIP-j';
		elseif ($ready_for_v6)
		{
			if     (_snomAppCmp($a, '6'     )<0)  $new_app = '6.5.10-SIP-j';
			elseif (_snomAppCmp($a, '6.5.12')<0)  $new_app = '6.5.12-beta-SIP-j';
			elseif (_snomAppCmp($a, '7'     )<0) {
				if ($ready_for_v6_to_7) {
					gs_log( GS_LOG_NOTICE, "Snom $mac ($phone_type, user $user): Ready to go from v. 6 to 7" );
												$new_app = 'update6to7-7.1.6-bf';
					# special from6to7 app
				}
			}
			elseif (_snomAppCmp($a, '7')>=0)
			{
				if   (_snomAppCmp($a, '7.0.17')<0) {
					# "Phones running version 7 but below 7.0.17 are still
					# on the old multiple flash partition structure and
					# have to be downgraded as usual to v6 first"
												//$new_app = '6.5.10-SIP-j';
												$new_app = '6.5.12-beta-SIP-j';
				}
				elseif (_snomAppCmp($a, '7.1.19')<0) {
												$new_app = '7.1.19-beta-SIP-f';
				}
			}
		}
	}
}
if ($new_app != '') {
	gs_log( GS_LOG_NOTICE, "Snom $mac ($phone_type, user $user): Update app $app -> $new_app" );
	_generate_settings( $phone_model, $new_app, null, null );
}


#################################################################
#  rootfs
#################################################################

if (_snomAppCmp($a, '7'     )<0) {
	$new_rootfs = '';
	$old_rootfs = '';
	if (! empty($rootfs_ramdisk))
	{
									$new_rootfs = 'ramdiskToJffs2-3.36-br.bin';
	}
	elseif (! empty($rootfs_jffs2))
	{
		if ($rootfs_jffs2 < '3.36') {
									$new_rootfs = 'ramdiskToJffs2-3.36-br.bin';
		}
	}
	if ($new_rootfs != '') {
		gs_log( GS_LOG_NOTICE, "Snom $mac ($phone_type, user $user): Update rootfs $old_rootfs -> $new_rootfs" );
		_generate_settings( $phone_model, null, $new_rootfs, null );
	}
}


#################################################################
#  linux
#################################################################

if (_snomAppCmp($a, '7'     )<0) {
	$new_linux = '';
	if (! empty($linux))
	{
		if       ($linux < '3.25') {
										$new_linux = '3.25';
		} elseif (_snomAppCmp($a, '6')>=0
		&&        $allow_v_6_to_7
		&&        $linux < '3.38') {
			# 3.38 is a special from6to7 linux
										$new_linux = '3.38';
		}
	}
	if ($new_linux != '') {
		gs_log( GS_LOG_NOTICE, "Snom $mac ($phone_type, user $user): Update linux $linux -> $new_linux" );
		_generate_settings( $phone_model, null, null, $new_linux );
	}
}


?>