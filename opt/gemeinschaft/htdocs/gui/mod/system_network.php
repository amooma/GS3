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

defined('GS_VALID') or die('No direct access.');
include_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/find_executable.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";



echo '<h3>Interfaces</h3>',"\n";

$etc_network_interfaces = '/etc/network/interfaces';

if (! file_exists($etc_network_interfaces)) {
	echo "File \"$etc_network_interfaces\" not found.\n";
} else {
	$file = @file($etc_network_interfaces);
	if (empty($file)) {
		echo "Failed to read \"$etc_network_interfaces\".\n";
	} else {
		$out = '';
		foreach ($file as $line) {
			$line = rTrim($line);
			if (trim($line)=='') continue;
			if (preg_match('/^\s*#/S', $line)) continue;
			
			if (preg_match('/^\s+/S', $line, $m)) {
				$indent = '&nbsp;&nbsp;&nbsp; ';
				$line = subStr($line, strLen($m[0]));
			} else
				$indent = '';
			
			$out .= $indent . htmlEnt($line) ."\n";
		}
		echo '<pre>',"\n";
		echo $out;
		echo '</pre>',"\n";
	}
}
echo '<br />', "\n";



echo '<h3>ifconfig</h3>',"\n";

$ifconfig = find_executable('ifconfig', array(
	'/sbin/', '/bin', '/usr/sbin/', '/usr/bin', '/usr/local/sbin/', '/usr/local/bin'
	));
if (empty($ifconfig)) {
	echo 'Could not find ifconfig.',"\n";
} else {
	$err=0; $out=array();
	@exec( '/sbin/ifconfig 2>>/dev/null', $out, $err );
	if ($err != 0) {
		echo 'Could not read ifconfig.',"\n";
	} else {
		echo '<pre>',"\n";
		foreach ($out as $line) {
			$line = htmlEnt($line);
			$line = preg_replace( '/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/S', ' <b style="color:#00e;">$1</b>', $line);
			if (preg_match('/^([a-z0-9\-_:.]+)(.*)/iS', $line, $m))
				echo '<b style="color:#00e;">', $m[1] ,'</b>', $m[2], "\n";
			else
				echo $line, "\n";
		}
		echo '</pre>',"\n";
	}
}



?>
