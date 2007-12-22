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
require_once( GS_DIR .'inc/find_executable.php' );
$can_continue = false;


?>

<div style="width:500px; border:1px solid #ccc; margin: 2em auto; padding:0 1em 1em 1em; background-color:#eee;">
<h1><?php echo 'Willkommen!'; ?></h1>
<p>
<?php
	$installation_type = gs_get_conf('GS_INSTALLATION_TYPE');
	switch ($installation_type) {
		case 'gpbx':
			echo 'Die folgenden Schritte leiten Sie durch die grundlegende Netzwerk-Konfiguration der <b>GPBX</b>.' ,"\n";
			break;
		default:
			echo 'Die folgenden Schritte leiten Sie durch die grundlegende Netzwerk-Konfiguration der Telefonanlage <b>Gemeinschaft</b>.' ,"\n";
			break;
	}
	echo 'Sie k&ouml;nnen sp&auml;ter jederzeit zum Setup zur&uuml;ckkehren um Einstellungen zu ver&auml;ndern.' ,"\n";
?>
</p>
<p>
<?php
	echo 'Installationsart' ,': <b>';
	switch ($installation_type) {
		case 'gpbx'  :  echo 'GPBX'         ; break;
		case 'single':  echo 'Einzel-Server'; break;
		default      :  echo 'unbekannt'    ; break;
	}
	echo '</b>' ,"\n";
?>
</p>
<hr />
<br />


<?php

echo 'Sprache' ,': <b>', 'Deutsch' ,'</b><br />' ,"\n";

echo 'Ausf&uuml;hrungsrechte' ,': <b>';
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
				$can_continue = true;
			}
		}
	}
}
echo '</b><br />' ,"\n";



?>
<br />
<hr />
<?php

if (! $can_continue) {
	echo 'In diesem Setup-Schritt sind Fehler aufgetreten!' ,"\n";
} else {
	echo '<a class="fr" href="', GS_URL_PATH ,'setup/?step=network">weiter</a>' ,"\n";
	echo '<br class="nofloat" />' ,"\n";
}

?>

</div>
