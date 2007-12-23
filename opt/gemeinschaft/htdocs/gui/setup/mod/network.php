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

$action = @$_REQUEST['action'];


/*
$ifconfig = find_executable('ifconfig', array(
	'/sbin/', '/bin/', '/usr/sbin/', '/usr/bin/'
));
*/

?>

<div style="width:550px; border:1px solid #ccc; margin: 2em auto; padding:0 1em 1em 1em; background-color:#eee;">
<h1><?php echo 'Netzwerk'; ?></h1>
<p>
<?php
	switch ($GS_INSTALLATION_TYPE) {
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
<?php

echo "<pre>\n";
print_r($_REQUEST);
echo "</pre>\n";

define( 'GS_VALIDATION_OK'  , 2 );
define( 'GS_VALIDATION_WARN', 1 );
define( 'GS_VALIDATION_ERR' , 0 );

function _normalize_ip_addr( $addr )
{
	$addr = preg_replace('/[^0-9.]/', '', $addr);
	if ($addr == '') return null;
	$addr_parts = explode('.', $addr);
	$addr = '';
	for ($i=0; $i<=3; ++$i) {
		$part = (int)lTrim(@$addr_parts[$i], '0 ');
		if     ($part > 255) $part = 255;
		elseif ($part <   0) $part =   0;
		$addr .= $part;
		if ($i < 3) $addr .= '.';
	}
	return $addr;
}

function _input_validate_ipaddr( &$addr, &$errmsg )
{
	$addr = _normalize_ip_addr( $addr );
	$errmsg = '';
	if ($addr === null) {
		$errmsg = 'IP-Adresse leer!';
		return GS_VALIDATION_ERR;
	}
	if (subStr($addr,0, 2) === '0.'
	||  subStr($addr,0, 3) === '39.'
	||  subStr($addr,0, 4) === '127.'
	||  subStr($addr,0, 6) === '128.0.'
	||  subStr($addr,0, 8) === '169.254.'
	||  subStr($addr,0, 8) === '191.255.'
	||  subStr($addr,0, 8) === '192.0.0.'
	||  subStr($addr,0, 8) === '192.0.2.'
	||  subStr($addr,0,10) === '192.88.99.'
	||  subStr($addr,0,12) === '223.255.255.'
	||  subStr($addr,0, 4) === '224.'
	||  subStr($addr,0, 4) === '240.'
	||  subStr($addr,0,15) === '255.255.255.255'
	) {
		$errmsg = 'Ung&uuml;ltige IP-Adresse!';
		return GS_VALIDATION_ERR;
	}
	if (preg_match('/255/', $addr)
	||  subStr($addr, -2) === '.0') {
		$errmsg = 'Ung&uuml;ltige IP-Adresse!';
		return GS_VALIDATION_ERR;
	}
	return GS_VALIDATION_OK;
}







$addr = @$_REQUEST['ipaddr'][0] .'.'. @$_REQUEST['ipaddr'][1] .'.'. @$_REQUEST['ipaddr'][2] .'.'. @$_REQUEST['ipaddr'][3];
echo _input_validate_ipaddr( $addr, $errmsg );




?>

<form method="post" action="<?php echo GS_URL_PATH ,'setup/?step=network'; ?>">
<input type="hidden" name="action" value="save" />

<table cellspacing="1">
<tbody>
<tr>
	<th width="100"><?php echo 'IP-Adresse'; ?></th>
	<td>
<?php
		$ipaddr_parts = explode('.', @$_SERVER['SERVER_ADDR']);
		for ($i=0; $i<=3; ++$i) {
			$part = (int)lTrim(@$ipaddr_parts[$i], '0 ');
			echo '<input type="text" name="ipaddr[',$i,']" size="3" maxlength="3" class="r pre" value="', $part ,'" />';
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
		$netmask = gs_keyval_get('vlan_0_netmask');
		/*
		$netmask_parts = explode('.', $netmask);
		for ($i=0; $i<=3; ++$i) {
			$part = (int)lTrim(@$netmask_parts[$i], '0 ');
			echo '<input type="text" name="netmask[',$i,']" size="3" maxlength="3" class="r pre" value="', $part ,'" />';
			if ($i < 3) echo '.';
		}
		*/
		echo '<select name="netmask" class="pre">' ,"\n";
		$options = array(
			 8 => '255.0.0.0',
			16 => '255.255.0.0',
			24 => '255.255.255.0'
		);
		foreach ($options as $net => $mask) {
			$net = '/'.$net;
			echo '<option value="',$net,'"', ($net===$netmask ? ' selected="selected"' : '') ,'>',$net,' (',$mask,')</option>' ,"\n";
		}
		echo '</select>' ,"\n";
		echo ' &nbsp; <small>(', '<a target="_blank" href="http://de.wikipedia.org/wiki/Netzmaske">http://de.wikipedia.org/wiki/Netzmaske</a>' ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo 'Router'; ?></th>
	<td>
<?php
		$router = gs_keyval_get('vlan_0_router');
		$router_parts = explode('.', $router);
		for ($i=0; $i<=3; ++$i) {
			$part = (int)lTrim(@$router_parts[$i], '0 ');
			echo '<input type="text" name="router[',$i,']" size="3" maxlength="3" class="r pre" value="', $part ,'" />';
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
		$dns1 = gs_keyval_get('vlan_0_dns1');
		if (trim($dns1) != '') {
			$dns1_parts = explode('.', $dns1);
			for ($i=0; $i<=3; ++$i) {
				$dns1_parts[$i] = (int)lTrim(@$dns1_parts[$i], '0 ');
			}
		} else {
			$dns1_parts = array('','','','');
		}
		for ($i=0; $i<=3; ++$i) {
			echo '<input type="text" name="dns1[',$i,']" size="3" maxlength="3" class="r pre" value="', @$dns1_parts[$i] ,'" />';
			if ($i < 3) echo '.';
		}
?>
	</td>
</tr>
<tr>
	<th><?php echo 'DNS-Server (2)'; ?></th>
	<td>
<?php
		$dns2 = gs_keyval_get('vlan_0_dns2');
		if (trim($dns2) != '') {
			$dns2_parts = explode('.', $dns2);
			for ($i=0; $i<=3; ++$i) {
				$dns2_parts[$i] = (int)lTrim(@$dns2_parts[$i], '0 ');
			}
		} else {
			$dns2_parts = array('','','','');
		}
		for ($i=0; $i<=3; ++$i) {
			echo '<input type="text" name="dns2[',$i,']" size="3" maxlength="3" class="r pre" value="', @$dns2_parts[$i] ,'" />';
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
		$ntp1_addr = gs_keyval_get('vlan_0_ntp1');
		echo '<input type="text" name="ntp1" size="30" maxlength="50" class="pre" value="', $ntp1_addr ,'" />' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo 'NTP-Server (2)'; ?></th>
	<td>
<?php
		$ntp2_addr = gs_keyval_get('vlan_0_ntp2');
		echo '<input type="text" name="ntp2" size="30" maxlength="50" class="pre" value="', $ntp2_addr ,'" />' ,"\n";
		echo ' &nbsp; <small>(', 'optional' ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo 'NTP-Server (3)'; ?></th>
	<td>
<?php
		$ntp3_addr = gs_keyval_get('vlan_0_ntp3');
		echo '<input type="text" name="ntp3" size="30" maxlength="50" class="pre" value="', $ntp3_addr ,'" />' ,"\n";
		echo ' &nbsp; <small>(', 'optional' ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo 'NTP-Server (4)'; ?></th>
	<td>
<?php
		$ntp4_addr = gs_keyval_get('vlan_0_ntp4');
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
		<input type="submit" value="<?php echo 'Speichern'; ?>" />
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
