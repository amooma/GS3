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
require_once( GS_DIR .'inc/get-listen-to-ips.php' );

if ($argc > 1) {
	$conffile = strToLower($argv[1]);
} else {
	gs_log(GS_LOG_WARNING, 'Called without an argument!');
	exit(1);
}

if (gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
	$ipaddrs = gs_get_listen_to_ips(true);
	if (! is_array($ipaddrs) || count($ipaddrs) < 1) {
		$bindaddr = '0.0.0.0';  # bind to all interfaces which are "UP"
	} else {
		$bindaddr = trim($ipaddrs[0]);
		if (! preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $bindaddr)) {
			$bindaddr = '0.0.0.0';  # bind to all interfaces which are "UP"
		}
	}
} else {
	//$bindaddr = '0.0.0.0';  # bind to all interfaces which are "UP"
	
	$ipaddrs = gs_get_listen_to_ips();
	if (! is_array($ipaddrs) || count($ipaddrs) < 1) {
		$bindaddr = '0.0.0.0';  # bind to all interfaces which are "UP"
	} else {
		if (count($ipaddrs) == 1) {
			$bindaddr = trim($ipaddrs[0]);
			if (! preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $bindaddr)) {
				$bindaddr = '0.0.0.0';  # bind to all interfaces which are "UP"
			}
		}
		else {
			# Asterisk does not support multiple bindaddr statements
			$bindaddr = '0.0.0.0';  # bind to all interfaces which are "UP"
		}
	}
}

if (gs_get_conf('GS_FAX_ENABLED') && $conffile === 'iax') {
	# we have to bind to all interfaces for IAX, because the IAXmodems
	# listen on 127.0.0.1
	# Asterisk does not support multiple bindaddr statements
	$bindaddr = '0.0.0.0';  # bind to all interfaces which are "UP"
}

gs_log(GS_LOG_DEBUG, 'Determined '. $bindaddr .' as our bindaddr for '. strToUpper($conffile) );

if ($conffile === 'sip') {
	echo 'bindaddr=', $bindaddr ,"\n";
}
elseif ($conffile === 'iax') {
	if ($bindaddr === '0.0.0.0')
		echo ';bindaddr=', $bindaddr ,"\n";
	else
		echo 'bindaddr=', $bindaddr ,"\n";
}
else {
	gs_log(GS_LOG_WARNING, 'Argument "'. $conffile .'" is unknown!');
	exit(1);
}

?>