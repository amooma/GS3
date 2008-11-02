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
$can_continue = true;

if (trim(gs_keyval_get('setup_show')) === 'autoshow')
	gs_keyval_set('setup_show', 'password');


?>

<div style="width:550px; border:1px solid #ccc; margin: 2em auto; padding:0 1em 1em 1em; background-color:#eee;">
<h1><?php echo __('Telefone suchen'); ?></h1>
<p>
	<div class="fr">
	<a href="<?php echo GS_URL_PATH; ?>setup/?step=phones-scan"><button class="button"><?php echo __('Erneut suchen'); ?></button></a>
	</div>
	<?php echo __('Netzwerk nach IP-Telefonen scannen.'); ?>
</p>
<br class="nofloat" />
<hr />

<?php

function mac_addr_to_phone_type( $mac )
{
	static $map = array(
		'00:04:13' => array('title' => 'Snom', 'key' => 'snom')
	);
	$mac_vendor = strToUpper(subStr($mac,0,8));
	return (array_key_exists($mac_vendor, $map))
		? $map[$mac_vendor]
		: array('title' => '?', 'key' => '');
}

$err=0; $out=array();
@exec( 'LANG=C sudo ping -n -i 0.4 -w 2 -b 255.255.255.255 1>>/dev/null 2>>/dev/null', $out, $err );
if ($err !== 0) {
	echo 'Could not ping.';
	if ($err === 127)
		echo ' (<code>ping</code> command not available.)';
	echo '<br />',"\n";
}
else {
	$err=0; $out=array();
	@exec( 'LANG=C sudo arp -n -a 2>>/dev/null', $out, $err );
	if ($err !== 0) {
		echo 'Could not read arp table.';
		if ($err === 127)
			echo ' (<code>arp</code> command not available.)';
		echo '<br />',"\n";
	}
	else {
		echo '<table cellspacing="1">' ,"\n";
		echo '<thead>' ,"\n";
		echo '<tr>' ,"\n";
		echo '<th>', __('MAC-Adr.') ,'</th>' ,"\n";
		echo '<th>', __('IP-Adr.') ,'</th>' ,"\n";
		echo '<th>', __('Typ') ,'</th>' ,"\n";
		echo '<th>&nbsp;</th>' ,"\n";
		echo '</tr>' ,"\n";
		echo '</thead>' ,"\n";
		echo '<tbody>' ,"\n";
		
		$devices = array();
		foreach ($out as $line) {
			$line = strToUpper($line);
			
			if (! preg_match('/([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})/S', $line, $m))
				continue;
			$ip_addr_parts = $m;
			
			if (! preg_match('/([0-9A-F]{2}):([0-9A-F]{2}):([0-9A-F]{2}):([0-9A-F]{2}):([0-9A-F]{2}):([0-9A-F]{2})/S', $line, $m))
				continue;
			$mac_addr = $m[0];
			
			$devices[$mac_addr]['mac'] = $mac_addr;
			$devices[$mac_addr]['ip' ] = $ip_addr_parts;
		}
		unset($out);
		kSort($devices);
		
		$c = 0;
		foreach ($devices as $mac_addr => $device) {
			echo '<tr class="', ($c%2===0 ? 'odd':'even') ,'" height="25">' ,"\n";
			
			//echo '<td><code>', $device['mac'] ,'</code></td>' ,"\n";
			echo '<td style="vertical-align:middle; padding-top:0.3em;"><small><code>', $device['mac'] ,'</code></small></td>' ,"\n";
			
			echo '<td style="vertical-align:middle;">';
			for ($i=1; $i<=4; ++$i) {
				$device['ip'][$i] = lTrim($device['ip'][$i], '0');
				if ($device['ip'][$i] == '') $device['ip'][$i] = '0';
				/*
				echo str_replace(' ', '&nbsp;', str_pad($device['ip'][$i], 3, ' ', STR_PAD_LEFT));
				if ($i < 4) echo '.';
				*/
			}
			$device['ip'][0] =
				$device['ip'][1] .'.'.
				$device['ip'][2] .'.'.
				$device['ip'][3] .'.'.
				$device['ip'][4];
			echo $device['ip'][0];
			echo '</td>' ,"\n";
			
			$phone_type = mac_addr_to_phone_type( $device['mac'] );
			echo '<td style="vertical-align:middle;">', $phone_type['title'] ,'</td>' ,"\n";
			
			
			$can_take_over = ($phone_type['key'] != '');
			/*
			echo '<td>' ,"\n";
			//$is_new = true;
			//$do_take_over = $can_take_over && $is_new;
			if ($can_take_over) {
				echo '<form method="post" action="', baseName($_SERVER['PHP_SELF']) ,'" style="display:inline;">' ,"\n";
				echo '<input type="hidden" name="action" value="take_over" />' ,"\n";
				
				echo '<input type="hidden" name="phones[',$c,'][mac]"  value="', $device['mac'] ,'" />' ,"\n";
				echo '<input type="hidden" name="phones[',$c,'][ip]"   value="', $device['ip'][0] ,'" />' ,"\n";
				echo '<input type="hidden" name="phones[',$c,'][type]" value="', $phone_type['key'] ,'" />' ,"\n";
				
				echo '<input type="submit" value="', '&Uuml;bernehmen' ,'" />' ,"\n";
				echo '</form>' ,"\n";
			}
			echo '</td>' ,"\n";
			*/
			
			echo '<td style="vertical-align:middle;">' ,"\n";
			if ($can_take_over) {
				echo '<iframe src="', GS_URL_PATH ,'setup/phone-takeover.php?ip=',$device['ip'][0] ,'&amp;type=',$phone_type['key'] ,'" height="20" width="250" style="overflow:hidden; border:0; margin:0; padding:0; height:1.8em;"></iframe>' ,"\n";
			}
			echo '</td>' ,"\n";
			
			echo '</tr>' ,"\n";
			++$c;
		}
		echo '</tbody>' ,"\n";
		echo '</table>' ,"\n";
	}
}



?>


<hr />

<?php

echo '<div class="fl"><a href="', GS_URL_PATH ,'setup/?step=dhcpd">', __('zur&uuml;ck') ,'</a></div>' ,"\n";
echo '<div class="fr">';
if ($can_continue)
	echo '<a href="', GS_URL_PATH ,'setup/?step=done"><big>', __('weiter') ,'</big></a>';
else
	echo '<span style="color:#999;">', __('weiter') ,'</span>';
echo '</div>' ,"\n";
echo '<br class="nofloat" />' ,"\n";

?>
</div>
