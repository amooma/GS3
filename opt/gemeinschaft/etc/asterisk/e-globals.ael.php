#!/usr/bin/php -q
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

ini_set('implicit_flush', 1);
ob_implicit_flush(1);

error_reporting(0);

require_once( dirName(__FILE__) .'/../../inc/conf.php' );
require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/log.php' );
set_error_handler('err_handler_quiet');


$is_sub_system = gs_get_conf('GS_DP_SUBSYSTEM');
echo 'is_sub_system=', ($is_sub_system ? 'yes':'no') ,';',"\n";

$allow_direct_dial = gs_get_conf('GS_DP_ALLOW_DIRECT_DIAL');
echo 'allow_direct_dial=', ($allow_direct_dial ? 'yes':'no') ,';',"\n";

$intl_lang_sounds = false;
foreach(array( gs_get_conf('GS_INTL_LANG_SOUNDS'), 'de-DE', 'de-de', 'en-US', 'en-us', subStr(gs_get_conf('GS_INTL_LANG_SOUNDS'),0,2) ) as $lang) {
	if (! preg_match('/^[a-zA-Z0-9\-_]+$/', $lang)) continue;
	if (@is_dir( '/opt/gemeinschaft/sounds/'.$lang )) {
		$intl_lang_sounds = $lang;
		$intl_ast_lang = substr($intl_lang_sounds, 0, 2);
		break;
	}
}
if (! $intl_lang_sounds) {
	gs_log( GS_LOG_WARNING, 'Sounds not found for INTL_LANG_SOUNDS "'.gs_get_conf('GS_INTL_LANG_SOUNDS').'"' );
	$intl_lang_sounds = 'xx-XX';
	$intl_ast_lang = 'xx';
}
echo 'gs_lang=', $intl_lang_sounds ,';',"\n";
echo 'gs_astlang=', $intl_ast_lang ,';',"\n";

require_once( GS_DIR .'inc/get-listen-to-ips.php' );
$our_ips = gs_get_listen_to_ips(true);
if (count($our_ips) >= 1) {
	$our_ip = $our_ips[0];
}
else {
	$err=0; $out=array();
	@exec( '/opt/gemeinschaft/sbin/getnetifs/getipaddrs 2>>/dev/null', $out, $err );
	$addrs = array();
	if ($err != 0) {
		gs_log( GS_LOG_NOTICE, "getipaddrs failed (exit code $err)" );
		# not really a problem as we don't really need the system_ip
	} else {
		foreach ($out as $line) {
			if (preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $line, $m)) {
				$addrs[] = $m[0];
			}
		}
	}
	//if (($addr = gs_keyval_get('vlan_0_ipaddr'))) $addrs[] = $addr;
	$good_addrs = array();
	foreach ($addrs as $addr) {
		if (subStr($addr,0,4) === '127.'    ) continue;
		if (subStr($addr,0,8) === '169.254.') continue;
		$good_addrs[] = $addr;
	}
	unset($addrs);
	if (count($good_addrs) > 0) {
		$our_ip = $good_addrs[0];
	}
	else {
		$our_ip = '0.0.0.0';
	}
}
echo 'system_ip=', $our_ip ,';',"\n";
# not really useful for technical purposes.
# more like a "system name"


$connid_enabled = gs_get_conf('GS_DP_CONNID');
echo 'connid_enabled=', ($connid_enabled ? '1':'0') ,';',"\n";


?>
