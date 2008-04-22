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
		$out .= 'register => '. $outUser .'@gs-'. $name .'/'. $inUser ."\n";
	}
	echo "\n", $out;
	
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
	if ($gw['host'] != '' && $gw['user'] != '') {
		
		//echo 'register => '. $gw['user'] .':'. $gw['pwd'] .':'. $gw['user'] .'@'. $gw['host'] .'/'. $gw['user'] .'' ,"\n";
		
		//echo 'register => '. $gw['user'] .'@'. $gw['name'] ,"\n";
		// WARNING[13092]: chan_sip.c:4650 sip_register: Format for registration is user[:secret[:authuser]]@host[:port][/contact]
		/*
		echo 'register => ', $gw['user'];  # user
		if ($gw['pwd'] != '') {
			echo ':', $gw['pwd'];
			if ($gw['user'] != '') {
				echo ':', $gw['user'];  # authuser
			}
		}
		echo '@', $gw['host'];
		if ($gw['user'] != '') {
			echo '/', $gw['user'];  # contact
		}
		*/
		
		//$gw['user'] .= '@sipdomain.dom';
		if (preg_match('/@([^@]*)$/', $gw['user'], $m)) {
			$gw['fromdomain'] = $m[1];  # domain for the From header. like
			                            # setting fromdomain in the peer definition
			$gw['user'] = subStr($gw['user'], 0, -(strLen($gw['fromdomain'])+1) );
		} else {
			$gw['fromdomain'] = '';
		}
		//print_r($gw);
		
		echo 'register => ', $gw['user'];  # user
		if ($gw['fromdomain'] != '') {
			echo '@', $gw['fromdomain'];   # domain
		}
		if ($gw['pwd'] != '') {
			echo ':', $gw['pwd'];          # password
			if ($gw['user'] != '') {
				echo ':', $gw['user'];     # authuser
			}
		}
		echo '@', $gw['name'];             # peer definition
		if ($gw['user'] != '') {
			echo '/', $gw['user'];         # contact
		}
		echo "\n";
	}
	
}
echo "\n";


?>