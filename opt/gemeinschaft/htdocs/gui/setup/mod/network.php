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

<div style="width:550px; border:1px solid #ccc; margin: 2em auto; padding:0 1em 1em 1em; background-color:#eee;">
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
		echo ' &nbsp; <small>(', '<a target="_blank" href="http://de.wikipedia.org/wiki/IP-Adresse">http://de.wikipedia.org/wiki/IP-Adresse</a>' ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo 'Netzmaske'; ?></th>
	<td>
<?php
		$netmask = gs_keyval_get('vlan_data_netmask');
		$netmask_parts = explode('.', $netmask);
		for ($i=0; $i<=3; ++$i) {
			$part = lTrim(@$netmask_parts[$i], '0');
			if ($part == '') $part = '0';
			echo '<input type="text" name="netmask_',$i ,'" size="3" maxlength="3" class="r pre" value="', $part ,'" />';
			if ($i < 3) echo '.';
		}
		echo ' &nbsp; <small>(', '<a target="_blank" href="http://de.wikipedia.org/wiki/Netzmaske">http://de.wikipedia.org/wiki/Netzmaske</a>' ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo 'Router'; ?></th>
	<td>
<?php
		$router = gs_keyval_get('vlan_data_router');
		$router_parts = explode('.', $router);
		for ($i=0; $i<=3; ++$i) {
			$part = lTrim(@$router_parts[$i], '0');
			if ($part == '') $part = '0';
			echo '<input type="text" name="router_',$i ,'" size="3" maxlength="3" class="r pre" value="', $part ,'" />';
			if ($i < 3) echo '.';
		}
		echo ' &nbsp; <small>(', '<a target="_blank" href="http://de.wikipedia.org/wiki/Router">http://de.wikipedia.org/wiki/Router</a>' ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo 'DNS-Server (1)'; ?></th>
	<td>
<?php
		$dns1 = gs_keyval_get('vlan_data_dns1');
		if (trim($dns1) != '') {
			$dns1_parts = explode('.', $dns1);
			for ($i=0; $i<=3; ++$i) {
				$dns1_parts[$i] = lTrim(@$dns1_parts[$i], '0 ');
				if ($dns1_parts[$i] == '') $dns1_parts[$i] = '0';
			}
		} else {
			$dns1_parts = array('','','','');
		}
		for ($i=0; $i<=3; ++$i) {
			echo '<input type="text" name="dns1_',$i ,'" size="3" maxlength="3" class="r pre" value="', @$dns1_parts[$i] ,'" />';
			if ($i < 3) echo '.';
		}
?>
	</td>
</tr>
<tr>
	<th><?php echo 'DNS-Server (2)'; ?></th>
	<td>
<?php
		$dns2 = gs_keyval_get('vlan_data_dns2');
		if (trim($dns2) != '') {
			$dns2_parts = explode('.', $dns2);
			for ($i=0; $i<=3; ++$i) {
				$dns2_parts[$i] = lTrim(@$dns2_parts[$i], '0 ');
				if ($dns2_parts[$i] == '') $dns2_parts[$i] = '0';
			}
		} else {
			$dns2_parts = array('','','','');
		}
		for ($i=0; $i<=3; ++$i) {
			echo '<input type="text" name="dns2_',$i ,'" size="3" maxlength="3" class="r pre" value="', @$dns2_parts[$i] ,'" />';
			if ($i < 3) echo '.';
		}
		echo ' &nbsp; <small>(', 'optional' ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo 'NTP-Server (1)'; ?></th>
	<td>
<?php
		$ntp1_addr = gs_keyval_get('vlan_data_ntp1');
		echo '<input type="text" name="ntp1" size="30" maxlength="50" class="pre" value="', $ntp1_addr ,'" />' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo 'NTP-Server (2)'; ?></th>
	<td>
<?php
		$ntp2_addr = '1.de.pool.ntp.org';
		echo '<input type="text" name="ntp2" size="30" maxlength="50" class="pre" value="', $ntp2_addr ,'" />' ,"\n";
		echo ' &nbsp; <small>(', 'optional' ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo 'NTP-Server (3)'; ?></th>
	<td>
<?php
		$ntp3_addr = '2.de.pool.ntp.org';
		echo '<input type="text" name="ntp3" size="30" maxlength="50" class="pre" value="', $ntp3_addr ,'" />' ,"\n";
		echo ' &nbsp; <small>(', 'optional' ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo 'NTP-Server (4)'; ?></th>
	<td>
<?php
		$ntp4_addr = '3.de.pool.ntp.org';
		echo '<input type="text" name="ntp4" size="30" maxlength="50" class="pre" value="', $ntp4_addr ,'" />' ,"\n";
		echo ' &nbsp; <small>(', 'optional' ,')</small>' ,"\n";
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
