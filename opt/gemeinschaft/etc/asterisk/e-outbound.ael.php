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
set_error_handler('err_handler_quiet');


echo "\n";

$type = strToLower(gs_get_conf('GS_DP_OUTBOUND'));
if (! $type) $type = 'sip';
switch ($type) {
	case 'sip':
		/*
		SIPAddHeader(X-GS-clir: ${clir});
		SIPAddHeader(X-GS-origext: ${origext});
		SIPAddHeader(X-GS-forwards: ${forwards});
		SIPAddHeader(X-GS-user_name: ${user_name});
		SIPAddHeader(X-GS-user_id: ${user_id});
		*/
		echo "\t", 'Verbose(1,### Outgoing call via the gateway. User: ${user_name}\, CallerID: ${CALLERID(all)}\, Number: ${mnumber}\, Zone: ${zone});', "\n";
		echo "\t", 'Dial(SIP/${mnumber}@${gateway_host},300);', "\n";
		break;
	case 'zap':
		echo "\t", 'Verbose(1,### Outgoing call via ISDN. User: ${user_name}\, CallerID: ${CALLERID(all)}\, Number: ${mnumber}\, Zone: ${zone});', "\n";
		echo "\t", 'Dial(Zap/r1/${mnumber},300);', "\n";
		break;
	default:
		echo "\t", 'Verbose(1,### Don\'t know how to dial outbound!);', "\n";
}

echo "\n";

?>