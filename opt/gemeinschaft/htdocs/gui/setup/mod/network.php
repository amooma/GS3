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
<h1><?php echo 'Netzwerk'; ?></h1>
<p>
<?php
	$installation_type = gs_get_conf('GS_INSTALLATION_TYPE');
	switch ($installation_type) {
		case 'gpbx':
			echo 'Bitte stellen Sie die gew&uuml;nschte IP-Adresse der GPBX ein. Geben Sie au&szlig;erdem die Netzmaske Ihres Netzwerkes sowie die Adresse Ihres Routers, Nameservers und Zeitservers an.' ,"\n";
			echo 'Die Netzwerkkarte der GPBX wird dann auf diese Werte eingestellt.' ,"\n";
			break;
		default:
			echo 'Bitte stellen Sie die gew&uuml;nschte IP-Adresse des Gemeinschafts-Servers ein. Geben Sie au&szlig;erdem die Netzmaske Ihres Netzwerkes sowie die Adresse Ihres Routers, Nameservers und Zeitservers an.' ,"\n";
			echo 'Die Netzwerkkarte wird dann auf diese Werte eingestellt.' ,"\n";
			break;
	}
?>
</p>
<hr />
<br />

<form method="post" action="<?php echo GS_URL_PATH ,'setup/?step=network'; ?>">
<input type="hidden" name="action" value="check" />

<table cellspacing="1">
<tbody>
<tr>
	<th width="100"><?php echo 'IP-Adresse'; ?></th>
	<td>
<?php
		$ipaddr_parts = explode('.', @$_SERVER['SERVER_ADDR']);
		for ($i=0; $i<=3; ++$i) {
			$part = lTrim(@$ipaddr_parts[$i], '0');
			if ($part == '') $part = '0';
			echo '<input type="text" name="ipaddr_',$i ,'" size="3" maxlength="3" class="r pre" value="', $part ,'" />';
			if ($i < 3) echo '.';
		}
?>
	</td>
</tr>
<tr>
	<th><?php echo 'Netzmaske'; ?></th>
	<td>
<?php
		$netmask_parts = explode('.', '255.255.0.0');
		for ($i=0; $i<=3; ++$i) {
			$part = lTrim(@$netmask_parts[$i], '0');
			if ($part == '') $part = '0';
			echo '<input type="text" name="netmask_',$i ,'" size="3" maxlength="3" class="r pre" value="', $part ,'" />';
			if ($i < 3) echo '.';
		}
?>
	</td>
</tr>
<tr>
	<th><?php echo 'Router'; ?></th>
	<td>
<?php
		$router_parts = explode('.', '0.0.0.1');
		for ($i=0; $i<=3; ++$i) {
			$part = lTrim(@$router_parts[$i], '0');
			if ($part == '') $part = '0';
			echo '<input type="text" name="router_',$i ,'" size="3" maxlength="3" class="r pre" value="', $part ,'" />';
			if ($i < 3) echo '.';
		}
?>
	</td>
</tr>
<tr>
	<th><?php echo 'DNS (1)'; ?></th>
	<td>
<?php
		$dns_parts = explode('.', '0.0.0.1');
		for ($i=0; $i<=3; ++$i) {
			$part = lTrim(@$dns_parts[$i], '0');
			if ($part == '') $part = '0';
			echo '<input type="text" name="dns1_',$i ,'" size="3" maxlength="3" class="r pre" value="', $part ,'" />';
			if ($i < 3) echo '.';
		}
?>
	</td>
</tr>
<tr>
	<th><?php echo 'DNS (2)'; ?></th>
	<td>
<?php
		$dns_parts = explode('.', '0.0.0.1');
		for ($i=0; $i<=3; ++$i) {
			$part = lTrim(@$dns_parts[$i], '0');
			if ($part == '') $part = '0';
			echo '<input type="text" name="dns2_',$i ,'" size="3" maxlength="3" class="r pre" value="', $part ,'" />';
			if ($i < 3) echo '.';
		}
		echo ' (', 'optional' ,')' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo 'NTP (1)'; ?></th>
	<td>
<?php
		$ntp_parts = explode('.', '0.0.0.1');
		echo '<input type="text" name="ntp1_',$i ,'" size="30" maxlength="50" class="pre" value="';
		for ($i=0; $i<=3; ++$i) {
			$part = lTrim(@$ntp_parts[$i], '0');
			if ($part == '') $part = '0';
			echo $part;
			if ($i < 3) echo '.';
		}
		echo '" />' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo 'NTP (2)'; ?></th>
	<td>
<?php
		$ntp_parts = explode('.', '0.0.0.1');
		echo '<input type="text" name="ntp1_',$i ,'" size="30" maxlength="50" class="pre" value="';
		for ($i=0; $i<=3; ++$i) {
			$part = lTrim(@$ntp_parts[$i], '0');
			if ($part == '') $part = '0';
			echo $part;
			if ($i < 3) echo '.';
		}
		echo '" />' ,"\n";
		echo ' (', 'optional' ,')' ,"\n";
?>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td>
		<br />
		<input type="reset" value="<?php echo 'Zur&uuml;cksetzen'; ?>" />
		<input type="submit" value="<?php echo 'Eingaben pr&uuml;fen'; ?>" />
	</td>
</tr>
</tbody>
</table>

</form>



<br />
<hr />
<?php

echo '<a class="fl" href="', GS_URL_PATH ,'setup/">zur&uuml;ck</a>' ,"\n";

$can_continue = true;
if (! $can_continue) {
	echo 'In diesem Setup-Schritt sind Fehler aufgetreten!' ,"\n";
} else {
	echo '<a class="fr" href="', GS_URL_PATH ,'setup/?step=network">weiter</a>' ,"\n";
}
echo '<br class="nofloat" />' ,"\n";

?>

</div>
