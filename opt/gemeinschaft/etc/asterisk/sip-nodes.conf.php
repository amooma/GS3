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

function printparam( $param, $value, &$userparamarray, $html=false ) {
	if ($param == '')
		return;
	$printbr = true;

	if (array_key_exists($param, $userparamarray)) {
		if ($userparamarray[$param] != '')
			echo $param ." = ". $userparamarray[$param];
		else
			$printbr = false;
		unset($userparamarray[$param]);
	} else {
		echo $param ." = ". $value;
	}
	if ($printbr) {
		if ($html)
			echo "<br \>";
		echo "\n";
	}
}

ini_set('implicit_flush', 1);
ob_implicit_flush(1);

error_reporting(0);

require_once( dirName(__FILE__) .'/../../inc/conf.php' );
require_once( GS_DIR .'inc/util.php' );
set_error_handler('err_handler_quiet');

if (! gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
	
	require_once( GS_DIR .'inc/get-listen-to-ids.php' );
	require_once( GS_DIR .'inc/gs-lib.php' );
	include_once( GS_DIR .'inc/gs-fns/gs_hosts_get.php' );
	
	$our_ids = @ gs_get_listen_to_ids();
	if (! is_array($our_ids)) $our_ids = array();
	//echo 'OUR IDS: ', implode(', ', $our_ids), "\n";
	
	$hosts = @ gs_hosts_get();
	if (isGsError( $hosts )) $hosts = array();
	if (! $hosts)            $hosts = array();
	//echo "HOSTS:\n"; print_r($hosts);
	
	$min_our_ids = (count($our_ids) > 0) ? min($our_ids) : 0;
	$outUser = 'gs-'. str_pad( $min_our_ids, 4, '0', STR_PAD_LEFT );
	
	$out = '';
	foreach ($hosts as $host) {
		if (in_array( (int)$host['id'], $our_ids )) {
			//echo "SKIPPING ", $host['id'], "\n";
			continue;
		} else
			//echo "DOING ", $host['id'], "\n";
		
		# it's one of the other nodes
		
		$inUser = 'gs-'. str_pad( $host['id'], 4, '0', STR_PAD_LEFT );
		$inPass = 'thiS is rEally seCret.';
		$inPass = subStr( str_replace(
			array( '+', '/', '=' ),
			array( '', '', ''  ),
			base64_encode( $inPass )
		), 0, 25 );
		$outPass = $inPass;
		
		$name = str_pad( $host['id'], 4, '0', STR_PAD_LEFT );
		$out .= '
[gs-'. $name .'](node-user)
host='. $host['host'] .'
defaultip='. $host['host'] .'
username='. $inUser .'
secret='. $inPass .'
setvar=__from_node=yes

[gs-'. $name .'](node-peer)
host='. 'dynamic' .'
defaultip='. $host['host'] .'
username='. $outUser .'
fromuser='. $outUser .'
secret='. $outPass .'
setvar=__from_node=yes
';
	}
	echo $out;
	
}
echo "\n";



# get gateways from DB
#
require_once( GS_DIR .'inc/db_connect.php' );
$DB = gs_db_master_connect();
if (! $DB) {
	exit(1);
}
$rs = $DB->execute(
'SELECT
	`g`.`id`, `g`.`name`, `g`.`host`, `g`.`proxy`, `g`.`user`, `g`.`pwd`,
	`gg`.`name` `gg_name`
FROM
	`gates` `g` JOIN
	`gate_grps` `gg` ON (`gg`.`id`=`g`.`grp_id`)
WHERE
	`g`.`type`=\'sip\' AND
	`g`.`host` IS NOT NULL
ORDER BY `g`.`id`'
);
while ($gw = $rs->fetchRow()) {
	if ($gw['name'] == '') continue;
	if ($gw['host'] == '') continue;
	
	$nat            = 'yes';
	//$canreinvite    = 'no';
	$canreinvite    = 'nonat';
	
	$qualify        = 'yes';
	$maxexpiry      =  185;
	$defaultexpiry  =  145;
	
	$codecs_allow = array();
	$codecs_allow['alaw'   ] = true;
	$codecs_allow['ulaw'   ] = false;
	
	//$fromdomain     = 'gemeinschaft.localdomain';
	$fromdomain     = null;
	$fromuser       = null;
	
	if (preg_match('/@([^@]*)$/', $gw['user'], $m)) {
		# set domain in the From header
		$fromdomain = $m[1];
		$gw['user'] = subStr($gw['user'], 0, -strLen($m[0]));
		
		# assume that this SIP provider requires the username
		# instead of the caller ID number in the From header (and
		# that the caller ID is to be set in a P-Preferred-Identity
		# header)
		$fromuser   = $gw['user'];
		
		# also assume that this gateway is a SIP provider and
		# that re-invites will not work
		$canreinvite    = 'no';
	}
	
	if ($gw['proxy'] == null || $gw['proxy'] === $gw['host']) {
		$gw['proxy'] = null;
	}
	
	
	if (strToLower($gw['host']) === 'sip.1und1.de') {  # special settings for 1und1.de
		//$canreinvite    = 'no';
		
		//$fromdomain     = '1und1.de';
		//$fromuser       = $gw['user'];
		
		$qualify        = 'no';
		$maxexpiry      = 3600;
		$defaultexpiry  = 3600;
		
		$codecs_allow['alaw'   ] = true;
		$codecs_allow['ulaw'   ] = true;
		$codecs_allow['ilbc'   ] = true;
		$codecs_allow['gsm'    ] = true;
		$codecs_allow['g729'   ] = true;
		$codecs_allow['slinear'] = true;
	}
	elseif (strToLower($gw['host']) === 'sipgate.de') {  # special settings for SipGate.de
		//$canreinvite    = 'no';
		//$fromdomain     = 'sipgate.de';
		//$fromuser       = $gw['user'];
	}
	elseif (preg_match('/\\.sipgate\\.de$/i', $gw['host'])) {  # special settings for SipGate.de
		# sipconnect.sipgate.de, SipGate "Team" trunk
		//$fromuser       = $gw['user'];
		//$canreinvite    = 'no';
	}
	
	
	$userparamarray = array();
	$g_params = $DB->execute('SELECT * FROM `gate_params` WHERE `gate_id` ='.$gw['id']);
	while ($param = $g_params->fetchRow())
		$userparamarray[$param['param']] = $param['value'];

	echo '[', $gw['name'] ,']' ,"\n";
	printparam( 'type', 'peer', $userparamarray);
	printparam( 'host', $gw['host'], $userparamarray);
	printparam( 'port', '5060', $userparamarray);
	printparam( 'username', $gw['user'], $userparamarray);
	printparam( 'secret', $gw['pwd'], $userparamarray);
	
	if ($gw['proxy'] != null) {
		printparam( 'outboundproxy', $gw['proxy'], $userparamarray);
	}
	if ($fromdomain != null) {
		printparam( 'fromdomain', $fromdomain, $userparamarray);
	}
	if ($fromuser != null) {
		printparam( 'fromuser', $fromuser, $userparamarray);
	}
	printparam( 'insecure', 'port,invite', $userparamarray);
	printparam( 'nat', $nat, $userparamarray);
	printparam( 'canreinvite', $canreinvite, $userparamarray);
	printparam( 'dtmfmode', 'rfc2833', $userparamarray);
	printparam( 'call-limit', '0', $userparamarray);
	printparam( 'registertimeout', '60', $userparamarray);
	printparam( 'setvar=__is_from_gateway', '1', $userparamarray);
	printparam( 'context', 'from-gg-'.$gw['gg_name'], $userparamarray);
	printparam( 'qualify', $qualify, $userparamarray);
	printparam( 'language', 'de', $userparamarray);
	printparam( 'maxexpiry', $maxexpiry, $userparamarray);
	printparam( 'defaultexpiry', $defaultexpiry, $userparamarray);
	printparam( 'disallow', 'all', $userparamarray);
	foreach ($codecs_allow as $codec => $allowed) {
		if ($allowed) {
			printparam( 'allow', $codec, $userparamarray);
		}
	}
	foreach ($userparamarray as $param => $value) {
		printparam( $param, '', $userparamarray);
	}
	echo "\n";
}

?>