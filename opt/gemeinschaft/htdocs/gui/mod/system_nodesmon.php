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
include_once( GS_DIR .'inc/gs-lib.php' );
include_once( GS_DIR .'inc/util.php' );
include_once( GS_DIR .'inc/gs-fns/gs_hosts_get.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );


echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


$warnings = array();

# get nodes from watchdog conf
#
include_once( GS_DIR .'etc/gs-cluster-watchdog.conf' );
$nodesconf = $node;
if (! is_array($nodesconf)) {
	$warnings[] = __('Fehler beim Lesen der Nodes aus der Watchdog-Konfiguration!');
	$nodesconf = array();
}
$nodes = array();
foreach ($nodesconf as $node) {
	$ip = normalizeIPs($node['dynamic_ip']);
	$nodes[$ip] = $node;
	$nodes[$ip]['active'  ] = false;
	$nodes[$ip]['watchdog'] = true;
}

# get hosts from DB and mix with nodes
#
$hosts = gs_hosts_get();
if (isGsError($hosts)) {
	$warnings[] = $hosts->getMsg();
	$hosts = array();
} elseif (! is_array($hosts)) {
	$warnings[] = __('Fehler beim Abfragen der Hosts aus der Datenbank!');
	$hosts = array();
}
foreach ($hosts as $host) {
	$ip = normalizeIPs($host['host']);
	if (! @is_array($nodes[$ip])) {
		$nodes[$ip] = array();
	}
	$nodes[$ip]['host_id' ] = $host['id'];
	$nodes[$ip]['comment' ] = $host['comment'];
	$nodes[$ip]['active'  ] = true;
	$nodes[$ip]['watchdog'] = false;
}
unset($hosts);


if (is_array($warnings) && count($warnings) > 0) {
	foreach ($warnings as $warning) {
?>
<div style="max-width:600px;">
	<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/16/app/important.png" class="fl" />
	<p style="margin:0 0 0 22px; padding:0 0 7px 0;">
		<?php echo $warning; ?>
	</p>
</div>
<?php
	}
}
?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:100px;"><?php echo __('IP (stat.)'); ?></th>
	<th style="width:100px;"><?php echo __('IP (dyn.)'); ?> <sup>[1]</sup></th>
	<th style="width:85px;"><?php echo __('Kommentar'); ?></th>
	<th style="width:25px;"><?php echo __('ID'); ?></th>
	<th style="width:60px;"><?php echo __('Rolle'); ?></th>
	<th style="width:45px;"><?php echo __('Stonith'); ?></th>
	<th style="width:50px;"><?php echo __('Ping'); ?></th>
	<th style="width:60px;"><?php echo __('SIP Ping'); ?></th>
</tr>
</thead>
<tbody>
<?php

function microtime_float()
{
	list($usec, $sec) = explode(' ', microTime());
	return ((float)$usec + (float)$sec);
}


if (false) {
	//echo '<tr><td colspan="3"><i>- keine -</i></td></tr>';
} else {
	$i=0;
	ini_set('implicit_flush', 1);
	ob_implicit_flush(1);
	
	foreach ($nodes as $ip => $node) {
		echo '<tr class="', (++$i%2 ? 'odd':'even'), '">';
		echo '<td>', htmlEnt( @$node['static_ip'] ), '</td>';
		echo '<td>', htmlEnt( $ip ), '</td>';
		echo '<td>', htmlEnt( @$node['comment'] ), '</td>';
		echo '<td>', @$node['host_id'], '</td>';
		echo '<td>', ($node['active']
			? '<span style="color:#0a0;">'. __('Aktiv'  ) .'</span>'
			: '<span style="color:#777;">'. __('Reserve') .'</span>'),
		'</td>';
		echo '<td>';
		if ($node['watchdog'])
			echo '<img alt="ja" src="', GS_URL_PATH, 'crystal-svg/16/act/ok.png" /><sup>&nbsp;</sup>';
		else
			echo '<img alt="nein" src="', GS_URL_PATH, 'crystal-svg/16/act/redled.png" /><sup>[2]</sup>';
		echo '</td>';
		
		echo '<td class="r">';
		$timeout = 1;
		$cmd = 'ping -n -q -w '. $timeout .' -c 1 '. qsa($ip);
		$out = array();
		$start = microtime_float();
		@exec($cmd .' >>/dev/null 2>&1', $out, $ping_err);
		$time = (microtime_float() - $start) * 0.5;  # script startup time
		if ($ping_err==0) {
			echo '<span style="color:#0a0;">', round($time*1000), '&nbsp;ms</span>';
		} else {
			echo '<b style="color:#f00;">?</b>';
		}
		echo '</td>';
		
		if ($node['active']) {
			echo '<td class="r">';
			if ($ping_err === 0) {
				$timeout = 2;
				$cmd = 'PATH=$PATH:/usr/local/bin; '. GS_DIR .'sbin/check-sip-alive '. qsa('sip:checkalive@'. $ip) .' '. $timeout;
				$out = array();
				$start = microtime_float();
				@exec($cmd .' 2>&1', $out, $err);
				$time = (microtime_float() - $start) * 0.7;  # script startup time
				$out = strToUpper(trim(implode("\n", $out)));
				if ($err===0 && subStr($out,0,2)==='OK') {
					echo '<span style="color:#0a0;">', round($time*1000), '&nbsp;ms</span>';
				} else {
					if ($out=='FAIL')
						echo '<b style="color:#f00;">&gt;', $timeout, '&nbsp;s</b>';
					else
						echo '<b style="color:#f00;">?</b>';
				}
			} else
				echo '<b style="color:#f00;">?</b>';
			echo '</td>';
		} else
			echo '<td class="r">-</td>';
		
		echo '</tr>', "\n";
	}
}
?>
</tbody>
</table>

<br />
<br />

<p style="max-width:500px;"><small><sup>[1]</sup> <?php echo __('Dies ist die Adresse, die ggf. per Stonith &uuml;bernommen werden w&uuml;rde. (Die dynamische Adresse hat hier nichts mit DHCP zu tun.)'); /* //TRANSLATE ME */ ?></small></p>

<p style="max-width:500px;"><small><sup>[2]</sup> <?php echo __('Nicht konfiguriert.'); ?></small></p>


<?php
/*
echo "<pre>";
print_r($nodes);
echo "</pre>";
*/
?>

<script type="text/javascript">/*<![CDATA[*/
window.setTimeout('document.location.reload();', 20000);
/*]]>*/</script>
