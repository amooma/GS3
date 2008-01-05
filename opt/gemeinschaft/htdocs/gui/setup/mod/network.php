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

$current_ipaddr   = @$_SERVER['SERVER_ADDR'];
if (! $current_ipaddr
||  subStr($current_ipaddr,0, 4) === '127.'
) {
	$current_ipaddr = gs_keyval_get('vlan_0_ipaddr');
}
$current_netmask  = gs_keyval_get('vlan_0_netmask');
$current_router   = gs_keyval_get('vlan_0_router');
$current_dns1     = gs_keyval_get('vlan_0_dns1');
$current_dns2     = gs_keyval_get('vlan_0_dns2');
$current_ntp1     = gs_keyval_get('vlan_0_ntp1');
$current_ntp2     = gs_keyval_get('vlan_0_ntp2');
$current_ntp3     = gs_keyval_get('vlan_0_ntp3');
$current_ntp4     = gs_keyval_get('vlan_0_ntp4');

$form_ipaddr   = $current_ipaddr;
$form_netmask  = $current_netmask;
$form_router   = $current_router;
$form_dns1     = $current_dns1;
$form_dns2     = $current_dns2;
$form_ntp1     = $current_ntp1;
$form_ntp2     = $current_ntp2;
$form_ntp3     = $current_ntp3;
$form_ntp4     = $current_ntp4;

$errors_html = array();



if ($action === 'save') {
	
	/*
	echo "<pre>\n";
	print_r($_REQUEST);
	echo "</pre>\n";
	*/
	
	define( 'GS_VALIDATION_OK'    , 3 );
	define( 'GS_VALIDATION_EMPTY' , 2 );
	define( 'GS_VALIDATION_WARN'  , 1 );
	define( 'GS_VALIDATION_ERR'   , 0 );
	
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
	
	function _is_invalid_ip_addr_by_format( $addr )
	{
		return !preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $addr);
	}
	
	function _is_invalid_ip_addr_by_net( $addr )
	{
		return (
			   subStr($addr,0, 2) === '0.'
			|| subStr($addr,0, 3) === '39.'
			|| subStr($addr,0, 4) === '127.'
			|| subStr($addr,0, 6) === '128.0.'
			|| subStr($addr,0, 8) === '169.254.'
			|| subStr($addr,0, 8) === '191.255.'
			|| subStr($addr,0, 8) === '192.0.0.'
			|| subStr($addr,0, 8) === '192.0.2.'
			|| subStr($addr,0,10) === '192.88.99.'
			|| subStr($addr,0,12) === '223.255.255.'
			|| subStr($addr,0, 4) === '224.'
			|| subStr($addr,0, 4) === '240.'
			|| subStr($addr,0,15) === '255.255.255.255'
		);
	}
	
	function _is_empty_ip_addr( $addr )
	{
		$addr = trim($addr);
		return (
			   $addr == ''
			|| preg_match('/^0*\.0*\.0*\.0*$/', $addr)
		);
	}
	
	function _input_validate_ipaddr( &$addr, &$errmsg )
	{
		$errmsg = '';
		if (_is_invalid_ip_addr_by_format( $addr )) {
			$errmsg = 'Ung&uuml;ltige IP-Adresse!';
			return GS_VALIDATION_ERR;
		}
		$addr = _normalize_ip_addr( $addr );
		if ($addr === null) {
			$errmsg = 'Ung&uuml;ltige IP-Adresse!';
			return GS_VALIDATION_EMPTY;
		}
		if (_is_invalid_ip_addr_by_net( $addr )) {
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
	
	function _input_validate_netmask( &$netmask, &$errmsg )
	{
		$errmsg = '';
		if (! in_array($netmask, array('/8', '/16', '/24'), true)) {
			$errmsg = 'Ung&uuml;ltige Netzmaske!';
			return GS_VALIDATION_ERR;
		}
		return GS_VALIDATION_OK;
	}
	
	function _input_validate_router( &$addr, &$errmsg )
	{
		$errmsg = '';
		if (_is_invalid_ip_addr_by_format( $addr )) {
			$errmsg = 'Ung&uuml;ltige Router-Adresse!';
			return GS_VALIDATION_ERR;
		}
		$addr = _normalize_ip_addr( $addr );
		if ($addr === null) {
			$errmsg = 'Ung&uuml;ltige Router-Adresse!';
			return GS_VALIDATION_EMPTY;
		}
		if (_is_invalid_ip_addr_by_net( $addr )) {
			$errmsg = 'Ung&uuml;ltige Router-Adresse!';
			return GS_VALIDATION_ERR;
		}
		if (preg_match('/255/', $addr)
		||  subStr($addr, -2) === '.0') {
			$errmsg = 'Ung&uuml;ltige Router-Adresse!';
			return GS_VALIDATION_ERR;
		}
		return GS_VALIDATION_OK;
	}
	
	function _input_validate_dns( &$addr, &$errmsg )
	{
		$errmsg = '';
		if (_is_empty_ip_addr( $addr )) {
			$addr = '';
			$errmsg = 'Leere DNS-Server-Adresse!';
			return GS_VALIDATION_EMPTY;
		}
		if (_is_invalid_ip_addr_by_format( $addr )) {
			$errmsg = 'Ung&uuml;ltige DNS-Server-Adresse!';
			return GS_VALIDATION_ERR;
		}
		$addr = _normalize_ip_addr( $addr );
		if ($addr === null) {
			$errmsg = 'Ung&uuml;ltige DNS-Server-Adresse!';
			return GS_VALIDATION_EMPTY;
		}
		if (_is_invalid_ip_addr_by_net( $addr )) {
			$errmsg = 'Ung&uuml;ltige DNS-Server-Adresse!';
			return GS_VALIDATION_ERR;
		}
		if (preg_match('/255/', $addr)
		||  subStr($addr, -2) === '.0') {
			$errmsg = 'Ung&uuml;ltige DNS-Server-Adresse!';
			return GS_VALIDATION_ERR;
		}
		return GS_VALIDATION_OK;
	}
	
	function _input_validate_ntp( &$addr, &$errmsg )
	{
		$errmsg = '';
		if (_is_empty_ip_addr( $addr )) {
			$addr = '';
			$errmsg = 'Leere NTP-Server-Adresse!';
			return GS_VALIDATION_EMPTY;
		}
		if (preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $addr)) {
			# is IP address
			$addr = _normalize_ip_addr( $addr );
			if ($addr === null) {
				$errmsg = 'Ung&uuml;ltige NTP-Server-Adresse!';
				return GS_VALIDATION_EMPTY;
			}
			if (_is_invalid_ip_addr_by_net( $addr )) {
				$errmsg = 'Ung&uuml;ltige NTP-Server-Adresse!';
				return GS_VALIDATION_ERR;
			}
			if (preg_match('/255/', $addr)
			||  subStr($addr, -2) === '.0') {
				$errmsg = 'Ung&uuml;ltige NTP-Server-Adresse!';
				return GS_VALIDATION_ERR;
			}
		} else {
			# is name
			$addr = preg_replace('[^a-z0-9.\-_]', '', strToLower($addr));
			$addrs = getHostByNameL( $addr );
			if (! is_array($addrs) || count($addrs) < 1) {
				$errmsg = 'NTP-Server-Adresse kann nicht aufgel&ouml;st werden!';
				return GS_VALIDATION_WARN;
				# might be resolvable with the new network settings
			}
		}
		return GS_VALIDATION_OK;
	}
	
	function _complain_html( $errmsg, $ignorable=false )
	{
		global $errors_html;
		$errors_html[] = '<p style="border:2px solid #f00; color: #b00; padding:0.3em; margin:0.4em 0 0.3em 0"><b>'. ($ignorable ? 'Warnung!' : 'Fehler!') .'</b> '. $errmsg .'</p>';
	}
	
	
	
	$err_cnt  = 0;
	$warn_cnt = 0;
	
	$form_ipaddr =
		@$_REQUEST['ipaddr'][0] .'.'.
		@$_REQUEST['ipaddr'][1] .'.'.
		@$_REQUEST['ipaddr'][2] .'.'.
		@$_REQUEST['ipaddr'][3] ;
	$validation_result = _input_validate_ipaddr( $form_ipaddr, $errmsg );
	if ($validation_result !== GS_VALIDATION_OK) {
		$err_cnt++;
		_complain_html( $errmsg );
	}
	
	$form_netmask = @$_REQUEST['netmask'];
	$validation_result = _input_validate_netmask( $form_netmask, $errmsg );
	if ($validation_result !== GS_VALIDATION_OK) {
		$err_cnt++;
		_complain_html( $errmsg );
	}
	
	$form_router =
		@$_REQUEST['router'][0] .'.'.
		@$_REQUEST['router'][1] .'.'.
		@$_REQUEST['router'][2] .'.'.
		@$_REQUEST['router'][3] ;
	$validation_result = _input_validate_router( $form_router, $errmsg );
	if ($validation_result !== GS_VALIDATION_OK) {
		$err_cnt++;
		_complain_html( $errmsg );
	}
	
	$form_dns1 =
		@$_REQUEST['dns1'][0] .'.'.
		@$_REQUEST['dns1'][1] .'.'.
		@$_REQUEST['dns1'][2] .'.'.
		@$_REQUEST['dns1'][3] ;
	$validation_result = _input_validate_dns( $form_dns1, $errmsg );
	if ($validation_result !== GS_VALIDATION_OK) {
		$err_cnt++;
		_complain_html( $errmsg .' (1)' );
	}
	
	$form_dns2 =
		@$_REQUEST['dns2'][0] .'.'.
		@$_REQUEST['dns2'][1] .'.'.
		@$_REQUEST['dns2'][2] .'.'.
		@$_REQUEST['dns2'][3] ;
	$validation_result = _input_validate_dns( $form_dns2, $errmsg );
	if ($validation_result !== GS_VALIDATION_OK) {
		if ($validation_result === GS_VALIDATION_EMPTY) {
			$form_dns2 = '';
		} else {
			$err_cnt++;
			_complain_html( $errmsg .' (2)' );
		}
	}
	
	$form_ntp1 = @$_REQUEST['ntp1'];
	$validation_result = _input_validate_ntp( $form_ntp1, $errmsg );
	if ($validation_result !== GS_VALIDATION_OK) {
		if ($validation_result === GS_VALIDATION_WARN) {
			$warn_cnt++;
			_complain_html( $errmsg .' (1)', true );
		} else {
			$err_cnt++;
			_complain_html( $errmsg .' (1)' );
		}
	}
	
	$form_ntp2 = @$_REQUEST['ntp2'];
	$validation_result = _input_validate_ntp( $form_ntp2, $errmsg );
	if ($validation_result !== GS_VALIDATION_OK) {
		if ($validation_result === GS_VALIDATION_EMPTY) {
			$form_ntp2 = '';
		} elseif ($validation_result === GS_VALIDATION_WARN) {
			$warn_cnt++;
			_complain_html( $errmsg .' (2)', true );
		} else {
			$err_cnt++;
			_complain_html( $errmsg .' (2)' );
		}
	}
	
	$form_ntp3 = @$_REQUEST['ntp3'];
	$validation_result = _input_validate_ntp( $form_ntp3, $errmsg );
	if ($validation_result !== GS_VALIDATION_OK) {
		if ($validation_result === GS_VALIDATION_EMPTY) {
			$form_ntp3 = '';
		} elseif ($validation_result === GS_VALIDATION_WARN) {
			$warn_cnt++;
			_complain_html( $errmsg .' (3)', true );
		} else {
			$err_cnt++;
			_complain_html( $errmsg .' (3)' );
		}
	}
	
	$form_ntp4 = @$_REQUEST['ntp4'];
	$validation_result = _input_validate_ntp( $form_ntp4, $errmsg );
	if ($validation_result !== GS_VALIDATION_OK) {
		if ($validation_result === GS_VALIDATION_EMPTY) {
			$form_ntp4 = '';
		} elseif ($validation_result === GS_VALIDATION_WARN) {
			$warn_cnt++;
			_complain_html( $errmsg .' (4)', true );
		} else {
			$err_cnt++;
			_complain_html( $errmsg .' (4)' );
		}
	}
	
}


?>

<form method="post" action="<?php echo GS_URL_PATH ,'setup/?step=network'; ?>">
<input type="hidden" name="action" value="save" />

<table cellspacing="1">
<tbody>
<tr>
	<th width="100"><?php echo 'IP-Adresse'; ?></th>
	<td>
<?php
		$ipaddr_parts = explode('.', $form_ipaddr);
		for ($i=0; $i<=3; ++$i) {
			$part = (int)lTrim(@$ipaddr_parts[$i], '0 ');
			echo '<input type="text" name="ipaddr[',$i,']" size="3" maxlength="3" class="r pre" value="', $part ,'" />';
			if ($i < 3) echo '.';
		}
		echo ' &nbsp; <small>(', '<a target="_blank" href="http://de.wikipedia.org/wiki/IP-Adresse">?</a>' ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo 'Netzmaske'; ?></th>
	<td>
<?php
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
			echo '<option value="',$net,'"', ($net===$form_netmask ? ' selected="selected"' : '') ,'>',$net,' (',$mask,')</option>' ,"\n";
		}
		echo '</select>' ,"\n";
		echo ' &nbsp; <small>(', '<a target="_blank" href="http://de.wikipedia.org/wiki/Netzmaske">?</a>' ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo 'Router'; ?></th>
	<td>
<?php
		$router_parts = explode('.', $form_router);
		for ($i=0; $i<=3; ++$i) {
			$part = (int)lTrim(@$router_parts[$i], '0 ');
			echo '<input type="text" name="router[',$i,']" size="3" maxlength="3" class="r pre" value="', $part ,'" />';
			if ($i < 3) echo '.';
		}
		echo ' &nbsp; <small>(', '<a target="_blank" href="http://de.wikipedia.org/wiki/Router">?</a>' ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo 'DNS-Server (1)'; ?></th>
	<td>
<?php
		if (trim($form_dns1) != '') {
			$dns1_parts = explode('.', $form_dns1);
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
		if (trim($form_dns2) != '') {
			$dns2_parts = explode('.', $form_dns2);
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
		echo '<input type="text" name="ntp1" size="30" maxlength="50" class="pre" value="', $form_ntp1 ,'" />' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo 'NTP-Server (2)'; ?></th>
	<td>
<?php
		echo '<input type="text" name="ntp2" size="30" maxlength="50" class="pre" value="', $form_ntp2 ,'" />' ,"\n";
		echo ' &nbsp; <small>(', 'optional' ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo 'NTP-Server (3)'; ?></th>
	<td>
<?php
		echo '<input type="text" name="ntp3" size="30" maxlength="50" class="pre" value="', $form_ntp3 ,'" />' ,"\n";
		echo ' &nbsp; <small>(', 'optional' ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<th><?php echo 'NTP-Server (4)'; ?></th>
	<td>
<?php
		echo '<input type="text" name="ntp4" size="30" maxlength="50" class="pre" value="', $form_ntp4 ,'" />' ,"\n";
		echo ' &nbsp; <small>(', 'optional' ,')</small>' ,"\n";
?>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td>
<?php
	if ($action === 'save') {
		if (@is_array($errors_html) && count($errors_html) > 0) {
			foreach ($errors_html as $error_html) {
				echo $error_html ,"\n";
			}
		} else {
			echo '<br />',"\n";
		}
		if ($err_cnt < 1 && $warn_cnt > 0) {
			echo '<input type="checkbox" name="dont_warn" id="ipt-dont_warn" value="1" /> <label for="ipt-dont_warn">', 'Warnungen ignorieren' ,'</label><br />' ,"\n";
		}
	} else {
		echo '<br />',"\n";
	}
?>
		<input type="submit" value="<?php echo 'Speichern'; ?>" />
	</td>
</tr>
</tbody>
</table>

</form>



<br />
<hr />
<?php

echo '<div class="fl"><a href="', GS_URL_PATH ,'setup/">', 'zur&uuml;ck' ,'</a></div>' ,"\n";
echo '<div class="fr">';
if ($can_continue)
	echo '<a href="', GS_URL_PATH ,'setup/?step=network"><big>', 'weiter' ,'</big></a>';
else
	echo '<span style="color:#999;">', 'weiter' ,'</span>';
echo '</div>' ,"\n";
echo '<br class="nofloat" />' ,"\n";

?>

</div>
