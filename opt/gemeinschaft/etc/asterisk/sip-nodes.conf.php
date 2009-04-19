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
	`g`.`name`, `g`.`host`, `g`.`user`, `g`.`pwd`,
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
	
	if ($gw['host'] == 'sip.1und1.de') {
		echo '[', $gw['name'] ,']' ,"\n";
		echo 'type = peer' ,"\n";
		echo "host = sip.1und1.de\n";
		echo 'username = ' . $gw['user']."\n";
		echo 'secret = ' , $gw['pwd' ] ,"\n";
		echo 'nat = no' ,"\n";
		echo 'setvar=__is_from_gateway=1' ,"\n";
		echo 'context = from-gg-', $gw['gg_name'] ,"\n";
		echo 'fromuser = ' . $gw['user']."\n";
		echo "fromdomain=1und1.de\n";
		echo "insecure=very\n";
		echo "canreinvite=no\n";
		echo "maxexpirey=3600\n";
		echo "defaultexpirey=3600\n";
		echo "qualify=no\n";
		echo "language=de\n";
		echo "disallow=all\n";
		echo "allow=ulaw\n";
		echo "allow=ilbc\n";
		echo "allow=alaw\n";
		echo "allow=g729\n";
		echo "allow=gsm\n";
		echo "allow=slinear\n\n";
	}
	else {
		echo '[', $gw['name'] ,']' ,"\n";
		echo 'type = peer' ,"\n";
		echo 'host = ' , $gw['host'] ,"\n";
		echo 'port = 5060' ,"\n";
		echo 'username = ', $gw['user'] ,"\n";
		echo 'secret = ' , $gw['pwd' ] ,"\n";
		echo 'nat = yes' ,"\n";
		echo 'dtmfmode = rfc2833' ,"\n";
		echo 'insecure = port,invite' ,"\n";
		//echo 'canreinvite = no' ,"\n";
		echo 'canreinvite = nonat' ,"\n";
		echo 'call-limit = 0' ,"\n";
		echo 'registertimeout = 60' ,"\n";
		//echo 't38pt_udptl = yes' ,"\n";
		echo 'setvar=__is_from_gateway=1' ,"\n";
		echo 'context = from-gg-', $gw['gg_name'] ,"\n";
		echo "\n";
	}
	
}


?>