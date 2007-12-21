<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision:2991 $
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

require_once( GS_DIR .'inc/find_executable.php' );



echo 'Ausf&uuml;hrungsrechte: ';
/*
$sapi = php_sapi_name();
if (subStr($sapi, 0, 6) !== 'apache') {
	echo 'UNBEKANNT (kein Apache)';
} else {
	if (subStr($sapi, 6, 1) === '2')
		$apachectl_basename = 'apache2ctl';
	else
		$apachectl_basename = 'apachectl';
	$apachectl = find_executable($apachectl_basename, array(
		'/usr/sbin/',
		'/usr/bin/'
	));
	if (! $apachectl) {
		echo 'UNBEKANNT ('.$apachectl_basename.' nicht gefunden)';
	}
	$err=0; $out=array();
	@exec( $apachectl.' -V 2>>/dev/null | grep -a SERVER_CONFIG_FILE 2>>/dev/null', $out, $err );
	if ($err !== 0) {
		echo 'UNBEKANNT ('.$apachectl_basename.' nicht ausf&uuml;hrbar)';
	} else {
		if (preg_match('/SERVER_CONFIG_FILE="(\/[^"]+)/', implode("\n",$out), $m))
			$apache_conf = $m[1];
		elseif (preg_match('/(\/etc\/(?:apache|http)[a-z0-9\/\.\-_]+)/', implode("\n",$out), $m))
			$apache_conf = $m[1];
		else
			$apache_conf = null;
		if (! $apache_conf) {
			echo 'UNBEKANNT (Apache-Konfiguration nicht gefunden)';
		} else {
			echo $apache_conf;
			// ...
		}
	}
}
*/

/*
$err=0; $out=array();
@exec( 'cat /etc/sudoers | grep -v -E '. escapeShellArg('^\s*(#|$)') .' 2>>/dev/null', $out, $err );
if ($err !== 0) {
	echo 'FEHLER';
} else {
	$user_info = posix_getpwuid(posix_geteuid());
	print_r($out);
}
*/

$user_info = @posix_getpwuid(@posix_geteuid());
$username = @$user_info['name'];
unset($user_info);

$expect_basename = 'expect';
$expect = find_executable($expect_basename, array(
	'/usr/bin/',
	'/usr/sbin/',
	'/usr/local/bin/',
	'/usr/local/sbin/'
));
if (! $expect) {
	echo sPrintF('FEHLER (&quot;%s&quot; nicht gefunden)', $expect_basename);
} else {
	$gs_sbin_dir = '/opt/gemeinschaft/sbin/';
	$sudo_check      = $gs_sbin_dir .'sudo-check';
	$sudo_sudo_check = $gs_sbin_dir .'sudo-sudo-check';
	if (! file_exists($sudo_check)
	||  ! file_exists($sudo_sudo_check)) {
		echo 'FEHLER (Test-Skript nicht gefunden)';
	} else {
		$err=0; $out=array();
		@exec( $sudo_check .' 1>>/dev/null 2>>/dev/null', $out, $err );
		if ($err !== 0) {
			echo sPrintF('FEHLER (User &quot;%s&quot; hat nicht gen&uuml;gend Rechte)', $username);
		} else {
			$err=0; $out=array();
			@exec( $sudo_sudo_check .' 1>>/dev/null 2>>/dev/null', $out, $err );
			if ($err !== 0) {
				echo sPrintF('FEHLER (User &quot;%s&quot; hat nicht gen&uuml;gend Rechte)', 'root');
			} else {
				echo 'OK';
			}
		}
	}
}
echo '<br />' ,"\n";


?>