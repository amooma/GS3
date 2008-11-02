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
require_once( GS_DIR .'inc/keyval.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


if (gs_keyval_get('dhcp_daemon_start') !== 'yes') {
	echo __('Der DHCP-Server auf diesem System ist nicht aktiviert.');
	return;
}


if (file_exists('/var/lib/dhcp3/dhcpd.leases')) {
	# Debian 4
	$leases_file = '/var/lib/dhcp3/dhcpd.leases';
}
elseif (file_exists('/var/lib/dhcp/dhcpd.leases')) {
	# Debian 5?
	$leases_file = '/var/lib/dhcp/dhcpd.leases';
}
elseif (file_exists('/var/lib/dhcpd/dhcpd.leases')) {
	# RedHat
	$leases_file = '/var/lib/dhcpd/dhcpd.leases';
}
else {
	echo 'dhcpd.leases not found.';
	return;
}

if (@fileSize($leases_file) > 1000000) {
	# around 4000 leases
	echo 'Leases file too large to read.';
	return;
}



function un_octal_escape( $str )
{
	return preg_replace_callback('/\\\([0-7]{1,3})/S', create_function(
		'$m',
		'return chr(octDec($m[1]));'
	), $str);
}

function binary_to_hex( $str )
{
	$ret = '';
	$c = strLen($str);
	for ($i=0; $i<$c; ++$i) {
		if ($i !== 0) $ret .= '-';
		$ret .= str_pad(decHex(ord(@$str{$i})),2,'0',STR_PAD_LEFT);
	}
	return $ret;
}



$data = @gs_file_get_contents($leases_file);

//echo "<pre>\n";
$leases = array();
$m = array();
preg_match_all('/^lease\s+([0-9.]{7,15})\s+\{[^}]*\}/msS', $data, $m, PREG_SET_ORDER);
unset($data);
foreach ($m as $lease_def) {
	
	$ip_addr = $lease_def[1];
	$lease = array(
		'start' => null,
		'end'   => null,
		'hwt'   => null,
		'mac'   => null,
		'name'  => null,
		//'uid'   => null,
		'bstate'=> 'active'
	);
	$lease_def = subStr($lease_def[0], 6+strLen($ip_addr)+1);
	$lm = array();
	preg_match_all('/^\s*([a-z\-]+)\s*([^;\n$]*)/mS', $lease_def, $lm, PREG_SET_ORDER);
	foreach ($lm as $line_def) {
		switch ($line_def[1]) {
			
			case 'starts':
				if (preg_match('/^[0-6]\s+([0-9]{4})\/([0-9]{2})\/([0-9]{2})\s+([0-9]{2}):([0-9]{2}):([0-9]{2})/S', $line_def[2], $p)) {
					$lease['start'] = gmMkTime(
						(int)lTrim($p[4],'0'), (int)lTrim($p[5],'0'), (int)lTrim($p[6],'0'),
						(int)lTrim($p[2],'0'), (int)lTrim($p[3],'0'), (int)lTrim($p[1],'0'));
				}
				break;
			
			case 'ends':
				if (preg_match('/^[0-6]\s+([0-9]{4})\/([0-9]{2})\/([0-9]{2})\s+([0-9]{2}):([0-9]{2}):([0-9]{2})/S', $line_def[2], $p)) {
					$lease['end'] = gmMkTime(
						(int)lTrim($p[4],'0'), (int)lTrim($p[5],'0'), (int)lTrim($p[6],'0'),
						(int)lTrim($p[2],'0'), (int)lTrim($p[3],'0'), (int)lTrim($p[1],'0'));
				}
				elseif (preg_match('/^never/S', $line_def[2], $p)) {
					$lease['end'] = PHP_INT_MAX;
				}
				break;
			
			case 'hardware':
				if (preg_match('/^([a-z0-9\-_]+)\s+(([0-9a-zA-Z]{1,2}:?)+)/S', $line_def[2], $p)) {
					$lease['hwt'] = $p[1];
					switch ($lease['hwt']) {
						case 'ethernet'   : $lease['hwt'] = 'en'; break;
						case 'unknown-0'  : $lease['hwt'] = 'u0'; break;
						case 'unknown-1'  : $lease['hwt'] = 'u1'; break;
						case 'unknown-2'  : $lease['hwt'] = 'u2'; break;
						case 'unknown-3'  : $lease['hwt'] = 'u3'; break;
						case 'unknown-4'  : $lease['hwt'] = 'u4'; break;
						case 'unknown-5'  : $lease['hwt'] = 'u5'; break;
						case 'unknown-6'  : $lease['hwt'] = 'u6'; break;
						case 'unknown-7'  : $lease['hwt'] = 'u7'; break;
						case 'unknown-8'  : $lease['hwt'] = 'u8'; break;
						case 'unknown-9'  : $lease['hwt'] = 'u9'; break;
						case 'token-ring' : $lease['hwt'] = 'tr'; break;
						case 'fddi'       : $lease['hwt'] = 'fd'; break;
						default           : $lease['hwt'] = '?' ;
					}
					$lease['mac'] = $p[2];
				}
				break;
			
			case 'client-hostname':
				if (preg_match('/^"?([a-zA-Z0-9\-_.]+)/S', $line_def[2], $p)) {
					$lease['name'] = $p[1];
				}
				break;
			
			/*
			case 'uid':
				if (preg_match('/^"([^";\n]+)/S', $line_def[2], $p)) {
					$lease['uid'] = binary_to_hex(un_octal_escape($p[1]));
				}
				elseif (preg_match('/^(([0-9a-zA-Z]{1,2}:?)+)/S', $line_def[2], $p)) {
					$lease['uid'] = $p[1];
				}
				break;
			*/
			
			case 'binding':
				if (preg_match('/^state\s+([a-z0-9]+)/S', $line_def[2], $p)) {
					$lease['bstate'] = $p[1];
				}
				break;
			
			case 'abandoned':
				$lease['abd'] = true;
				break;
			
			case 'set':
				# custom variable in the lease
				$setm = array();
				preg_match_all('/^\s*([a-z\-_]+)\s*=?\s*([^;\n$]*)/mS', $line_def[2], $setm, PREG_SET_ORDER);
				foreach ($setm as $set_def) {
					switch ($set_def[1]) {
						case 'vendor-class-identifier':
							$val = trim($set_def[2], '"');
							$lease['vci'] = $val;
							break;
					}
				}
				break;
		}
	}
	
	if ($lease['bstate'] !== 'active') continue;
	$leases[$ip_addr] = $lease;
	
	//print_r($lease_def);
	//print_r($lease);
}

//echo "</pre>\n";

?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th><?php echo __('IP-Adresse'); ?></th>
	<th><?php echo __('MAC-Adresse'); ?></th>
	<th><?php echo __('G&uuml;ltig von'); ?></th>
	<th><?php echo __('G&uuml;ltig bis'); ?></th>
	<th><?php echo __('Name'); ?></th>
	<th><?php echo __('Hersteller / Modell'); ?></th>
</tr>
</thead>
<tbody>
<?php
	$i=0;
	foreach ($leases as $ip_addr => $lease) {
		if ($lease['bstate'] !== 'active') continue;
		
		echo '<tr class="', ($i%2===0 ? 'odd':'even') ,'">';
		echo '<td>', htmlEnt($ip_addr) ,'</td>';
		echo '<td><tt>', htmlEnt($lease['mac']);
		if ($lease['hwt'] !== 'en') echo ' (', htmlEnt($lease['hwt']) ,')';
		echo '</tt></td>';
		echo '<td>', htmlEnt(date('d.m.Y, H:i:s', $lease['start'])) ,'</td>';
		echo '<td>';
		if ($lease['end'] === null)
			echo '?';
		elseif ($lease['end'] < PHP_INT_MAX)
			echo htmlEnt(date('d.m.Y, H:i:s', $lease['end']));
		else
			echo __('endlos');
		if (array_key_exists('abd',$lease) && $lease['abd'])
			echo ' (a!)';
		echo '</td>';
		echo '<td>', htmlEnt(strToLower($lease['name'])) ,'</td>';
		
		echo '<td>';
		switch (strToUpper(subStr($lease['mac'],0,8))) {
			case '00:04:13': echo 'Snom'        ;
				if (array_key_exists('vci',$lease)) {
					if     (strToLower(subStr($lease['vci'],0,7)) === 'snom-m3')
						echo ' (M3)';
					elseif (strToLower(subStr($lease['vci'],0,4)) === 'snom')
						echo ' (Snom)';
				}
				break;
			case '00:04:F2': echo 'Polycom'     ; break;
			case '00:01:E3': echo 'Siemens'     ; break;
			case '00:08:5D': echo 'Aastra'      ; break;
			case '00:0B:82': echo 'Grandstream' ; break;
			default        : echo '&nbsp;'      ;
		}
		echo '</td>';
		echo '</tr>' ,"\n";
		++$i;
	}
	
	unset($leases);
?>
</tbody>
</table>
