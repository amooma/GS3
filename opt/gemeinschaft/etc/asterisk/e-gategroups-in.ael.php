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
require_once( GS_DIR .'inc/db_connect.php' );


echo "\n";
echo '// (auto-generated)' ,"\n";
echo "\n";

$db = gs_db_slave_connect();
if (! $db) die();
//FIXME - should probably write a message to gs_log() before dying

$rs = $db->execute( 'SELECT `id`, `name`, `allow_in` FROM `gate_grps`' );
if (! $rs) {
	echo '//ERROR' ,"\n";
	die();
	//FIXME - should probably write a message to gs_log() before dying
}

while ($ggrp = $rs->fetchRow()) {
	
	$name = preg_replace('/[^a-z0-9\-_]/i', '', $ggrp['name']);
	
	echo 'context from-gg-', $name ,' {' ,"\n";
	echo "\t", '_. => {' ,"\n";
	echo "\t\t", 'Set(__is_from_gateway=1);' ,"\n";
	
	if ((int)$ggrp['allow_in']) {
		
		# strip prefix off DID number (sets sets did_ext) and apply
		# redirection rules for inbound calls (sets did_ext_to):
		echo "\t\t", 'AGI(/opt/gemeinschaft/dialplan-scripts/in-route.agi,', (int)$ggrp['id'] ,',${EXTEN});' ,"\n";
		# make it appear as if the caller had dialed the extension without
		# our trunk prefix etc.:
		echo "\t\t", 'Set(CALLERID(dnid)=${did_ext});' ,"\n";
		echo "\t\t", 'Verbose(1,### Incoming call from gw group "', $name ,'". dnid: ${EXTEN} => ext: ${did_ext} => ${did_ext_to});' ,"\n";
		echo "\t\t", 'Goto(from-gateways,${did_ext_to},1);' ,"\n";
		
	} else {
		
		echo "\t\t", 'Verbose(1,### Inbound calls not allowed for gw group "', $name ,'");' ,"\n";
		# tell the caller that they are not allowed to call us.
		echo "\t\t", '// 21 = AST_CAUSE_CALL_REJECTED => SIP 403 Forbidden' ,"\n";
		echo "\t\t", 'Set(PRI_CAUSE=21);' ,"\n";
		echo "\t\t", 'Hangup(21);' ,"\n";
		
	}
	
	echo "\t", '}' ,"\n";
	echo '}' ,"\n";

}

echo "\n";
echo '// end of contexts for incoming calls from the gateway groups' ,"\n";
echo "\n";

?>