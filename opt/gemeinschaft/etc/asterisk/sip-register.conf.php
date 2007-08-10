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

echo "\n", $out, "\n";


?>