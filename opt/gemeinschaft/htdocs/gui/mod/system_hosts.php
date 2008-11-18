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

function microtime_float()
{
	list($usec, $sec) = explode(' ', microTime());
	return ((float)$usec + (float)$sec);
}

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";

$host_apis = array(
	''    => '-',
	'm01' => '1.0'
	//'m02' => '1.1'
);

$edit_host   = (int)trim(@$_REQUEST['edit'   ]);
$save_host   = (int)trim(@$_REQUEST['save'   ]);
$per_page    = (int)GS_GUI_NUM_RESULTS;
$page        =     (int)(@$_REQUEST['page'   ]);
$host        =      trim(@$_REQUEST['host'   ]);
$hostid      = (int)trim(@$_REQUEST['hostid' ]);
$comment     =      trim(@$_REQUEST['comment']);
$delete_host = (int)trim(@$_REQUEST['delete' ]);

if ($host) {
	$host = normalizeIPs($host);
	$bInvalHostName = false;
	if (! preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $host)) {
		# not an IP address. => resolve hostname
		$addresses = @gethostbynamel($host);
		
		if (count($addresses) < 1) {
			echo '<div class="errorbox">';
			echo sPrintF(__('Hostname &quot;%s&quot; konnte nicht aufgel&ouml;st werden.'), htmlEnt($host));
			echo '</div>',"\n";
			$bInvalHostName = true;
		}
		elseif (count($addresses) > 1) {
			echo '<div class="errorbox">';
			echo sPrintF(__('Hostname &quot;%s&quot; kann nicht verwendet werden, da er zu mehr als einer IP-Adresse aufgel&ouml;st wird.'), htmlEnt($host));
			echo '</div>',"\n";
			$bInvalHostName = true;
		}
		elseif (count($addresses) == 1) {
			if (strlen($addresses[0]) == 0) {
				echo '<div class="errorbox">';
				echo sPrintF(__('Hostname &quot;%s&quot; konnte nicht aufgel&ouml;st werden.'), htmlEnt($host));
				echo '</div>',"\n";		
				$bInvalHostName = true;
			}
			$host = $addresses[0];
		}
	}
	
	if (! preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $host) ) {
		if (! $bInvalHostName) {
			echo '<div class="errorbox">';
			echo sPrintF(__('Ung&uuml;ltige IP-Adresse &quot;%s&quot;!'), htmlEnt($host));
			echo '</div>',"\n";
		}
	}
	else {
		
		if ($save_host) {
			$sql_query =
'UPDATE `hosts` SET
	`host`=\''. $DB->escape($host) .'\',
	`comment`=\''. $DB->escape($comment) .'\'
WHERE
	`id`='. $save_host .' AND
	`is_foreign`=0'
			;
			$ok = $DB->execute($sql_query);
			$host_id = $save_host;
			if (! $ok) {
				echo '<div class="errorbox">';
				echo sPrintF(__('Fehler beim &Auml;ndern von Host %u'),
					$save_host);
				echo '</div>',"\n";
			}
		}
		else {
			@$DB->execute('OPTIMIZE TABLE `hosts`');  # recalculate next auto-increment value
			@$DB->execute('ANALYZE TABLE `hosts`');
			
			$sql_query =
'INSERT INTO `hosts` (
	`id`,
	`host`,
	`comment`,
	`is_foreign`
) VALUES (
	NULL,
	\''. $DB->escape($host) .'\',
	\''. $DB->escape($comment) .'\',
	0
)'
			;
			$ok = $DB->execute($sql_query);
			if ($ok) {
				$host_id = (int)$DB->getLastInsertId();
			} else {
				$host_id = 0;
				echo '<div class="errorbox">';
				echo sPrintF(__('Fehler beim Hinzuf&uuml;gen von Host &quot;%s&quot;'),
					htmlEnt($host));
				echo '</div>',"\n";
			}
		}
		
		if ($host_id > 0) {
			$DB->execute( 'REPLACE INTO `host_params` (`host_id`, `param`, `value`) VALUES ('. $host_id .', \'api\', \''. $DB->escape(trim(@$_REQUEST['hp_api'])) .'\')' );
			$DB->execute( 'REPLACE INTO `host_params` (`host_id`, `param`, `value`) VALUES ('. $host_id .', \'sip_proxy_from_wan\', \''. $DB->escape(trim(@$_REQUEST['hp_sip_proxy_from_wan'])) .'\')' );
			$DB->execute( 'REPLACE INTO `host_params` (`host_id`, `param`, `value`) VALUES ('. $host_id .', \'sip_server_from_wan\', \''. $DB->escape(trim(@$_REQUEST['hp_sip_server_from_wan'])) .'\')' );
		}
	}
}

if ($delete_host) {
	
	gs_db_start_trans($DB);
	
	# delete BOI permissions
	@$DB->execute( 'DELETE FROM `boi_perms` WHERE `host_id`='. $delete_host );
	
	# delete host params
	@$DB->execute( 'DELETE FROM `host_params` WHERE `host_id`='. $delete_host );
	
	$sql_query =
'DELETE FROM `hosts` 
WHERE
	`id`='. $delete_host .' AND
	`is_foreign`=0'
	;
	$DB->execute($sql_query);
	
	if (! gs_db_commit_trans($DB)) {
		echo '<div class="errorbox">';
		echo sPrintF(__('Host &quot;%s&quot; konnte nicht gel&ouml;scht werden.'),
			htmlEnt($host));
		echo '</div>',"\n";
	}
	
	@$DB->execute('OPTIMIZE TABLE `hosts`');  # recalculate next auto-increment value
	@$DB->execute('ANALYZE TABLE `hosts`');
}



# get nodes from watchdog conf
#
@include_once( GS_DIR .'etc/gs-cluster-watchdog.conf' );
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


$sql_query =
	'SELECT SQL_CALC_FOUND_ROWS '.
		'`h`.`id`, `h`.`host`, `h`.`comment`, `h`.`group_id`, '.
		'`p1`.`value` `hp_api`, '.
		'`p2`.`value` `hp_sip_proxy_from_wan`, '.
		'`p3`.`value` `hp_sip_server_from_wan` '.
	'FROM '.
		'`hosts` `h` LEFT JOIN '.
		'`host_params` `p1` ON (`p1`.`host_id`=`h`.`id` AND `p1`.`param`=\'api\') LEFT JOIN '.
		'`host_params` `p2` ON (`p2`.`host_id`=`h`.`id` AND `p2`.`param`=\'sip_proxy_from_wan\') LEFT JOIN '.
		'`host_params` `p3` ON (`p3`.`host_id`=`h`.`id` AND `p3`.`param`=\'sip_server_from_wan\') '.
	'WHERE `h`.`is_foreign`=0 '.
	'ORDER BY `h`.`id` '.
	'LIMIT '. ($page*(int)$per_page) .','. (int)$per_page;

$rs = $DB->execute($sql_query);

$num_total = @$DB->numFoundRows();
$num_pages = ceil($num_total / $per_page);

?>	

<div style="max-width:600px;">
	<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/16/app/important.png" class="fl" />
	<p style="margin-left:22px; font-size:1.5em; font-weight:bold; line-height:1.2em; color:#d00;">
		<?php echo __('Achtung! Durch un&uuml;berlegte &Auml;nderungen k&ouml;nnen Sie das ordnungsgem&auml;&szlig;e Funktionieren von Gemeinschaft erheblich beeintr&auml;chtigen!'); ?>
	</p>
</div>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:55px;" class="sort-col"><?php echo __('ID'); ?></th>
	<th style="width:120px;"><?php echo __('IP (stat.)'); ?> <sup>[1]</sup></th>
	<th style="width:125px;"><?php echo __('IP (VoIP)'); ?> <sup>[2]</sup></th>
	<th style="width:140px;"><?php echo __('Kommentar'); ?></th>
	<th style="width:125px;"><?php echo __('SIP-Proxy WAN'); ?> <sup>[3]</sup></th>
	<th style="width:125px;"><?php echo __('SIP-SBC WAN'); ?> <sup>[4]</sup></th>
	<th style="width:60px;"><?php echo __('Rolle'); ?></th>
	<th style="width:45px;"><?php echo __('Stonith'); ?></th>
	<th style="width:50px;"><?php echo __('Ping'); ?></th>
	<th style="width:60px;"><?php echo __('SIP Ping'); ?></th>	
	<th style="width:80px;">
<?php
echo ($page+1), ' / ', $num_pages, '&nbsp; ',"\n";

if ($page > 0) {
	echo
	'<a href="', gs_url($SECTION, $MODULE, null, 'page='.($page-1)), '" title="', __('zur&uuml;ckbl&auml;ttern'), '" id="arr-prev">',
	'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous.png" />',
	'</a>', "\n";
} else {
	echo
	'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous_notavail.png" />', "\n";
}

if ($page < $num_pages-1) {
	echo
	'<a href="', gs_url($SECTION, $MODULE, null, 'page='.($page+1)), '" title="', __('weiterbl&auml;ttern'), '" id="arr-next">',
	'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/next.png" />',
	'</a>', "\n";
} else {
	echo
	'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/next_notavail.png" />', "\n";
}

?>
	</th>
</tr>
</thead>
<tbody>

<?php

@ob_flush(); @flush();

if (@$rs) {
	$i = 0;
	while ($r = $rs->fetchRow()) {
		echo '<tr class="', ((++$i % 2) ? 'odd':'even'), '">', "\n";
		
		$ip = normalizeIPs($r['host']);
		if (! @is_array($nodes[$ip])) {
			$nodes[$ip] = array();
		}

		$nodes[$ip]['host_id' ] = $r['id'];
		$nodes[$ip]['comment' ] = $r['comment'];
		$nodes[$ip]['active'  ] = true;
		$nodes[$ip]['watchdog'] = false;

		if ($edit_host == $r['id']) {
			
			echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
			echo gs_form_hidden($SECTION, $MODULE), "\n";
			echo '<input type="hidden" name="page" value="', htmlEnt($page), '" />', "\n";
			echo '<input type="hidden" name="save" value="', $r['id'] , '" />', "\n";
			
			echo '<td class="r">', htmlEnt($r['id']) ,'</td>',"\n";
			
			echo '<td>', htmlEnt( @$nodes[$ip]['static_ip'] ) ,'</td>',"\n";
			
			echo '<td>';
			echo '<input type="text" name="host" value="', htmlEnt($r['host']) ,'" size="15" maxlength="30" style="width:95%;" />';
			echo '</td>',"\n";
			
			echo '<td>';
			echo '<input type="text" name="comment" value="', htmlEnt($r['comment']) ,'" size="20" maxlength="45" style="width:95%;" />';
			echo '</td>',"\n";
			
			/*
			echo '<td>';
			echo '<select name="hp_api">' ,"\n";
			foreach ($host_apis as $api => $title) {
				echo '<option value="', htmlEnt($api) ,'"';
				if ($api == $r['hp_api']) echo ' selected="selected"';
				echo '>', htmlEnt($title) ,'</option>' ,"\n";
			}
			echo '</select>';
			echo '</td>',"\n";
			*/
			
			echo '<td>';
			echo '<input type="text" name="hp_sip_proxy_from_wan" value="', htmlEnt($r['hp_sip_proxy_from_wan']) ,'" size="15" maxlength="15" style="width:95%;" />';
			echo '</td>',"\n";
			
			echo '<td>';
			echo '<input type="text" name="hp_sip_server_from_wan" value="', htmlEnt($r['hp_sip_server_from_wan']) ,'" size="15" maxlength="15" style="width:95%;" />';
			echo '</td>',"\n";
			
			echo '<td>', ($nodes[$ip]['active']
				? '<span style="color:#0a0;">'. __('Aktiv'  ) .'</span>'
				: '<span style="color:#777;">'. __('Reserve') .'</span>');
			echo '</td>',"\n";
			
			echo '<td>';
			if ($nodes[$ip]['watchdog'])
				echo '<img alt="', __('ja') ,'" src="', GS_URL_PATH, 'crystal-svg/16/act/ok.png" /><sup>&nbsp;</sup>';
			else
				echo '<img alt="', __('nein') ,'" src="', GS_URL_PATH, 'crystal-svg/16/act/redled.png" /><sup>[2]</sup>';
			echo '</td>',"\n";
			
			echo '<td class="r">';
			$timeout = 1;
			$cmd = 'ping -n -q -w '. $timeout .' -c 1 '. qsa($ip);
			$out = array();
			$start = microtime_float();
			@exec($cmd .' >>/dev/null 2>>/dev/null', $out, $ping_err);
			$time = (microtime_float() - $start) * 0.5;  # script startup time
			if ($ping_err === 0) {
				echo '<span style="color:#0a0;">', round($time*1000), '&nbsp;ms</span>';
			} else {
				echo '<b style="color:#f00;">?</b>';
			}
			echo '</td>',"\n";
			
			if ($nodes[$ip]['active']) {
				echo '<td class="r">';
				if ($ping_err === 0) {
					$timeout = 2;
					$cmd = 'PATH=$PATH:/usr/local/bin; '. GS_DIR .'sbin/check-sip-alive '. qsa('sip:checkalive@'. $ip) .' '. $timeout;
					$out = array();
					$start = microtime_float();
					@exec($cmd .' 2>&1', $out, $err);
					$time = (microtime_float() - $start) * 0.8;  # script startup time
					$out = strToUpper(trim(implode("\n", $out)));
					if ($err===0 && subStr($out,0,2)==='OK') {
						echo '<span style="color:#0a0;">', round($time*1000), '&nbsp;ms</span>';
					} else {
						if ($out==='FAIL')
							echo '<b style="color:#f00;">&gt;', $timeout, '&nbsp;s</b>';
						else
							echo '<b style="color:#f00;">?</b>';
					}
				} else
					echo '<b style="color:#f00;">?</b>';
				echo '</td>',"\n";
			} else
				echo '<td class="r">-</td>',"\n";
			
			echo '<td>';
			
			echo '<button type="submit" title="', __('Speichern'), '" class="plain">';
			echo '<img alt="', __('Speichern') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/filesave.png" />';
			echo '</button>' ,"\n";
			echo "&nbsp;\n";
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'page='.$page) ,'">';
			echo '<button type="button" title="', __('Abbrechen'), '" class="plain">';
			echo '<img alt="', __('Abbrechen') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/cancel.png" />';
			echo '</button></a>' ,"\n";
			
			echo '</td>',"\n";
			
			echo '</form>',"\n";
			
		} else {
			
			echo '<td class="r">', htmlEnt($r['id']) ,'</td>',"\n";
			
			echo '<td>', htmlEnt( @$nodes[$ip]['static_ip'] ) ,'</td>',"\n";
			
			echo '<td>', htmlEnt($r['host']) ,'</td>',"\n";
			
			echo '<td>', htmlEnt($r['comment']) ,'</td>',"\n";
			
			//echo '<td>', htmlEnt(array_key_exists($r['hp_api'], $host_apis) ? $host_apis[$r['hp_api']] : $r['hp_api']) ,'</td>',"\n";
			
			echo '<td>', htmlEnt($r['hp_sip_proxy_from_wan']) ,'</td>',"\n";
			
			echo '<td>', htmlEnt($r['hp_sip_server_from_wan']) ,'</td>',"\n";
			
			echo '<td>', ($nodes[$ip]['active']
				? '<span style="color:#0a0;">'. __('Aktiv'  ) .'</span>'
				: '<span style="color:#777;">'. __('Reserve') .'</span>');
			echo '</td>',"\n";
			
			echo '<td>';
			if ($nodes[$ip]['watchdog'])
				echo '<img alt="', __('ja') ,'" src="', GS_URL_PATH, 'crystal-svg/16/act/ok.png" /><sup>&nbsp;</sup>';
			else
				echo '<img alt="', __('nein') ,'" src="', GS_URL_PATH, 'crystal-svg/16/act/redled.png" /><sup>[2]</sup>';
			echo '</td>',"\n";
			
			@ob_flush(); @flush();
			echo '<td class="r">';
			$timeout = 1;
			$cmd = 'ping -n -q -w '. $timeout .' -c 1 '. qsa($ip);
			$out = array();
			$start = microtime_float();
			@exec($cmd .' >>/dev/null 2>>/dev/null', $out, $ping_err);
			$time = (microtime_float() - $start) * 0.5;  # script startup time
			if ($ping_err === 0) {
				echo '<span style="color:#0a0;">', round($time*1000), '&nbsp;ms</span>';
			} else {
				echo '<b style="color:#f00;">?</b>';
			}
			echo '</td>',"\n";
			
			if ($nodes[$ip]['active']) {
				@ob_flush(); @flush();
				echo '<td class="r">';
				if ($ping_err === 0) {
					$timeout = 2;
					$cmd = 'PATH=$PATH:/usr/local/bin; '. GS_DIR .'sbin/check-sip-alive '. qsa('sip:checkalive@'. $ip) .' '. $timeout;
					$out = array();
					$start = microtime_float();
					@exec($cmd .' 2>&1', $out, $err);
					$time = (microtime_float() - $start) * 0.8;  # script startup time
					$out = strToUpper(trim(implode("\n", $out)));
					if ($err===0 && subStr($out,0,2)==='OK') {
						echo '<span style="color:#0a0;">', round($time*1000), '&nbsp;ms</span>';
					} else {
						if ($out==='FAIL')
							echo '<b style="color:#f00;">&gt;', $timeout, '&nbsp;s</b>';
						else
							echo '<b style="color:#f00;">?</b>';
					}
				} else
					echo '<b style="color:#f00;">?</b>';
				echo '</td>',"\n";
			} else {
				echo '<td class="r">-</td>',"\n";
			}
			
			echo '<td>';
			
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'edit='.$r['id'] .'&amp;page='.$page) ,'" title="', __('bearbeiten'), '"><img alt="', __('bearbeiten'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
			
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='.$r['id'] .'&amp;page='.$page) ,'" title="', __('l&ouml;schen'), '"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
			
			echo '</td>',"\n";
		}
		
		echo '</tr>', "\n";
		@ob_flush(); @flush();
	}
}

?>
<tr>
<?php

if (!$edit_host) {
	echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
	echo gs_form_hidden($SECTION, $MODULE), "\n";
?>
	<td class="r">
		&nbsp;
	</td>
	<td>&nbsp;</td>
	<td>
		<input type="text" name="host" value="" size="15" maxlength="30" style="width:95%;" />
	</td>
	<td>
		<input type="text" name="comment" value="" size="20" maxlength="45" style="width:95%;" />
	</td>
	<td>
		<input type="text" name="hp_sip_proxy_from_wan" value="" size="15" maxlength="15" style="width:95%;" />
	</td>
	<td>
		<input type="text" name="hp_sip_server_from_wan" value="" size="15" maxlength="15" style="width:95%;" />
	</td>
	<td colspan="4">
		&nbsp;
		<input type="hidden" name="hp_api" value="" />
	</td>
	<td>
		<button type="submit" title="<?php echo __('Host anlegen'); ?>" class="plain">
			<img alt="<?php echo __('Speichern'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
		</button>
	</td>

</form>

<?php
}
@ob_flush(); @flush();
?>

</tr>

</tbody>
</table>

<br />
<p>
	<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/info.png" />
	<small><?php echo __('&Auml;nderungen ben&ouml;tigen einen Dialplan-Reload und Reload von Asterisk'); ?></small>
</p>

<p style="max-width:500px;"><small><sup>[1]</sup> <?php echo __('Zus&auml;tzliche statische IP-Adresse die von Gemeinschaft nicht ver&auml;ndert wird.'); ?></small></p>

<p style="max-width:500px;"><small><sup>[2]</sup> <?php echo __('Dies ist die f&uuml;r Gemeinschaft prim&auml;re IP-Adresse, die bei einem Ausfall von einem der anderen Nodes per Stonith und Gratuitous ARP &uuml;bernommen werden w&uuml;rde.'); ?>
<?php echo "\n", __('Wenn Sie in dieses Feld einen Hostnamen eintragen wird er automatisch permanent zu einer IP-Adresse aufgel&ouml;st.'); ?></small></p>

<p style="max-width:500px;"><small><sup>[3]</sup> <?php echo sPrintF(__('Diese IP-Adresse wird beim Provisioning von Telefonen im WAN (also au&szlig;erhalb der LAN-Netze &quot;%s&quot;) die einen Registrar im LAN haben als Outbound Proxy gesetzt (d.h. inbound aus Sicht von Gemeinschaft). F&uuml;r keinen Proxy das Feld leer lassen.'), htmlEnt(gs_get_conf('GS_PROV_LAN_NETS'))); ?></small></p>

<p style="max-width:500px;"><small><sup>[4]</sup> <?php echo sPrintF(__('Diese IP-Adresse wird beim Provisioning von Telefonen im WAN (also au&szlig;erhalb der LAN-Netze &quot;%s&quot;) die einen Registrar im LAN haben als Server/Registrar gesetzt. (Mu&szlig; vom Session Border Controller auf den tats&auml;chlichen Server umgeleitet werden.) F&uuml;r den normalen Server das Feld leer lassen.'), htmlEnt(gs_get_conf('GS_PROV_LAN_NETS'))); ?></small></p>

