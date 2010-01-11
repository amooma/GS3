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

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

$CDR_DB = gs_db_cdr_master_connect();
if (! $CDR_DB) {
	echo 'CDR DB error.';
	return;
}

include_once( GS_DIR .'inc/queue-status.php' );
include_once( GS_DIR .'inc/group-fns.php' );

$get_queue_stats_from_db = gs_get_conf('GS_GUI_QUEUE_INFO_FROM_DB');

function _devstate2v( $devstate )
{
	//static $states = array(
	$states = array(
		AST_DEVICE_UNKNOWN     => array('v'=>  ('?'        ), 's'=>'?'     ),
		AST_DEVICE_NOT_INUSE   => array('v'=>__('frei'     ), 's'=>'green' ),
		AST_DEVICE_INUSE       => array('v'=>__('belegt'   ), 's'=>'red'   ),
		AST_DEVICE_BUSY        => array('v'=>__('belegt'   ), 's'=>'red'   ),
		AST_DEVICE_INVALID     => array('v'=>  ('!'        ), 's'=>'?'     ),
		AST_DEVICE_UNAVAILABLE => array('v'=>__('offline'  ), 's'=>'?'     ),
		AST_DEVICE_RINGING     => array('v'=>__('klingelt' ), 's'=>'yellow'),
		AST_DEVICE_RINGINUSE   => array('v'=>__('anklopfen'), 's'=>'yellow'),
		AST_DEVICE_ONHOLD      => array('v'=>__('halten'   ), 's'=>'red'   )
	);
	return array_key_exists($devstate, $states) ? $states[$devstate] : null;
}


$GS_INSTALLATION_TYPE_SINGLE = gs_get_conf('GS_INSTALLATION_TYPE_SINGLE');

$user_groups  = gs_group_members_groups_get(Array(@$_SESSION['sudo_user']['info']['id']), 'user');
$queue_groups = gs_group_members_get(gs_group_permissions_get($user_groups, 'monitor_queues', 'queue'));

if (!is_array($queue_groups) || (count($queue_groups) == 0))  $queue_groups = Array(0);

# get the queues for the current user
#

/*
$sql_query = 
'SELECT `q`.`_id` `id`, `q`.`name` `ext`, `q`.`_title` `title`, `h`.`host`
FROM
	`ast_queue_members` `m` JOIN
	`ast_queues` `q` ON (`q`.`_id`=`m`.`_queue_id`) JOIN
	`hosts` `h` ON (`h`.`id`=`q`.`_host_id`)
WHERE `m`.`_user_id`='. (int)@$_SESSION['sudo_user']['info']['id'] .' OR `q`.`_id` IN ('.implode(',',$queue_groups).')
ORDER BY `q`.`name`';
*/

$sql_query = 
'SELECT `q`.`_id` `id`, `q`.`name` `ext`, `q`.`_title` `title`, `h`.`host` 
FROM 
	`ast_queues` `q` JOIN
	`hosts` `h` ON (`h`.`id`=`q`.`_host_id`)
WHERE `q`.`_id` IN ('.implode(',',$queue_groups).') OR `q`.`_id` IN 
	( SELECT `id` FROM`ast_queue_members` `m` WHERE `m`.`_user_id`='. (int)@$_SESSION['sudo_user']['info']['id'] .') 
ORDER BY `q`.`name`';

$rs_queues = $DB->execute( $sql_query );

if ($rs_queues->numRows()==0) {
	echo __('Sie sind nicht Mitglied einer Warteschlange.') ,'<br />', "\n";
	$slow_refresh = true;
} else {
	$slow_refresh = false;
	while ($queue = $rs_queues->fetchRow()) {
		
		# get queue members from db
		#
		$rs_members = $DB->execute(
'SELECT `m`.`_user_id`, `m`.`interface`, `u`.`firstname` `fn`, `u`.`lastname` `ln`
FROM
	`ast_queue_members` `m` LEFT JOIN
	`users` `u` ON (`u`.`id`=`m`.`_user_id`)
WHERE `m`.`_queue_id`='. (int)@$queue['id'] .'
ORDER BY `m`.`interface`'
		);
		
		# get queue stats from manager interface
		#
		if ($GS_INSTALLATION_TYPE_SINGLE)
			$queue['host'] = '127.0.0.1';
		$queue_stats = gs_queue_status( $queue['host'], $queue['ext'], true, true );
		/*
		echo "<pre>";
		print_r($queue_stats);
		echo "</pre>";
		*/
		
		$queue_title = (trim(@$queue['title']) != '' ? trim(@$queue['title']) : '');
		
		
		if ($get_queue_stats_from_db) {
			
			# override $queue_stats['calls'|'completed'|'abandoned'|'holdtime']
			#
			
			$t = time();
			$now_y = (int)date('Y', $t);
			$now_m = (int)date('n', $t);
			$now_d = (int)date('j', $t);
			
			$day_t_start = (int)mkTime(  0, 0, 0 , $now_m,$now_d,$now_y );
			$day_t_end   = (int)mkTime( 23,59,59 , $now_m,$now_d,$now_y );
			$sql_qlog_today = '(`timestamp`>='.$day_t_start .' AND `timestamp`<='.$day_t_end .')';
			
			$queue_stats['calls'    ] = (int)@$CDR_DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE `queue_id`='. $queue['id'] .'
AND '. $sql_qlog_today .'
AND `event`=\'_ENTER\''
			);
			$queue_stats['completed'] = (int)@$CDR_DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE `queue_id`='. $queue['id'] .'
AND '. $sql_qlog_today .'
AND `event`=\'_CONNECT\''
			);
			$queue_stats['abandoned'] = (int)@$CDR_DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE `queue_id`='. $queue['id'] .'
AND '. $sql_qlog_today .'
AND `event`=\'_EXIT\' AND `reason`=\'ABANDON\''
			);
			$queue_stats['_timeout' ] = (int)@$CDR_DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE `queue_id`='. $queue['id'] .'
AND '. $sql_qlog_today .'
AND `event`=\'_EXIT\' AND `reason`=\'TIMEOUT\''
			);
			$queue_stats['_empty'   ] = (int)@$CDR_DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE `queue_id`='. $queue['id'] .'
AND '. $sql_qlog_today .'
AND `event`=\'_EXIT\' AND `reason`=\'EMPTY\''
			);
			$queue_stats['holdtime' ] = (int)@$CDR_DB->executeGetOne(
'SELECT MAX(`waittime`) FROM `queue_log` WHERE `queue_id`='. $queue['id'] .'
AND '. $sql_qlog_today .'
AND `event`=\'_COMPLETE\' AND `waittime` IS NOT NULL'
			);
			
		}
		
?>

<h2><?php echo @$queue['ext'], ($queue_title != '' ? (' ('. htmlEnt($queue_title) .') ') : ''); ?></h2>


<h3><?php echo __('Statistik'); ?></h3>
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:89px;" class="nobr"><?php echo __('Anrufe'      ); ?></th>
	<th style="width:89px;" class="nobr"><?php echo __('Angenommen'  ); ?></th>
	<th style="width:89px;" class="nobr"><?php echo __('Erfolgreich' ); ?></th>
	<th style="width:89px;" class="nobr"><?php echo __('Absprung'    ); ?></th>
	<?php if ($get_queue_stats_from_db) { ?>
	<th style="width:89px;" class="nobr"><?php echo __('Nicht angen.'); ?></th>
	<th style="width:89px;" class="nobr"><?php echo __('Niemand frei'); ?></th>
	<?php } ?>
	<th style="width:89px;" class="nobr"><?php echo __('Wartezeit'   ); ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td class="r"><?php
		echo (@$queue_stats['calls'    ] !== null
		     ? $queue_stats['calls'    ] : '?');
	?></td>
	<td class="r"><?php
		echo (@$queue_stats['completed'] !== null
		     ? $queue_stats['completed'] : '?');
	?></td>
	<td class="r"><?php
		if (@$queue_stats['completed'] !== null
		&&  @$queue_stats['calls'    ] !== null)
		{
			if ($queue_stats['calls'    ] > 0) {
				echo round($queue_stats['completed'] / $queue_stats['calls'] *100), ' %';
			} else {
				echo '0 %';
			}
		}
	?></td>
	<td class="r"><?php
		echo (@$queue_stats['abandoned'] !== null
		     ? $queue_stats['abandoned'] : '?');
	?></td>
	<?php if ($get_queue_stats_from_db) { ?>
	<td class="r"><?php
		echo (@$queue_stats['_timeout' ] !== null
		     ? $queue_stats['_timeout' ] : '?');
	?></td>
	<td class="r"><?php
		echo (@$queue_stats['_empty'   ] !== null
		     ? $queue_stats['_empty'   ] : '?');
	?></td>
	<?php } ?>
	<td class="r"><?php
	if (@$queue_stats['holdtime'] === null)
		echo '?';
	else {
		$s = @$queue_stats['holdtime'];
		$m = floor($s/60);
		$s = $s - $m*60;
		echo $m, ':', str_pad($s, 2, '0', STR_PAD_LEFT);
	}
	?></td>
</tr>
</tbody>
</table>

<?php
if ($get_queue_stats_from_db) {
	echo '<p class="fr"><small>(', __('Je nach Konfiguration k&ouml;nnen hier kurze Verz&ouml;gerungen auftreten.') ,')</small></p>';
}
?>

<br />


<h3><?php echo __('Mitglieder'); ?></h3>
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:65px;"><?php echo __('Nebenst.'); ?></th>
	<th style="width:215px;"><?php echo __('Name'); ?></th>
	<th style="width:89px;"><?php echo __('Status'); ?></th>
	<?php if (GS_GUI_QUEUE_SHOW_NUM_CALLS) { ?>
	<th style="width:50px;" class="r"><?php echo __('Anrufe'); ?></th>
	<th style="width:110px;" class="r"><?php echo __('zuletzt'); ?></th>
	<?php } ?>
</tr>
</thead>
<tbody>

<?php

if (@$rs_members) {
	if ($rs_members->numRows() === 0) {
		echo '<tr><td colspan="5"><i>- ', __('keine'), ' -</i></td></tr>';
	} else {
		$i = 0;
		while ($r = $rs_members->fetchRow()) {
			echo '<tr class="'. ((++$i % 2 == 0) ? 'even':'odd') .'">';
			
			$interface = $r['interface'];
			
			$memberinfo = @$queue_stats['members'][$interface];
			
			if (strToUpper(subStr($interface,0,4))=='SIP/')
				$interface = subStr($interface,4);
			echo '<td>', htmlEnt($interface), '</td>';
			//$sudo_url = (@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
			//	? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);
			
			echo '<td>', htmlEnt($r['ln']);
			if ($r['fn'] != '') echo ', ', htmlEnt($r['fn']);
			echo '</td>';
			
			$devstate = _devstate2v( @$memberinfo['devstate'] );
			if (@$devstate['s']) {
				$img = '<img alt=" " src="'. GS_URL_PATH;
				switch ($devstate['s']) {
					case 'green' : $img.= 'crystal-svg/16/act/greenled.png' ; break;
					case 'yellow': $img.= 'crystal-svg/16/act/yellowled.png'; break;
					case 'red'   : $img.= 'crystal-svg/16/act/redled.png'   ; break;
					default      : $img.= 'crystal-svg/16/act/free_icon.png'; break;
				}
				$img.= '" /> ';
			} else
				$img = '<img alt=" " src="'. GS_URL_PATH .'crystal-svg/16/act/free_icon.png" /> ';
			echo '<td>', $img, (@$devstate['v'] ? $devstate['v'] : '?'), '</td>';
			
			if (GS_GUI_QUEUE_SHOW_NUM_CALLS) {
				echo '<td class="r">', (@$memberinfo['calls'] !== null ? @$memberinfo['calls'] : '?'), '</td>';
				
				echo '<td class="r">', (@$memberinfo['lastcall'] > 0 ? date_human( @$memberinfo['lastcall'] ) : '-'), '</td>';
			}
			
			echo '</tr>', "\n";
		}
	}
}

?>

</tbody>
</table>

<br />


<h3><?php echo __('Anrufer(p)'); ?></h3>
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:30px;" class="r"><?php echo __('Pos.'); ?></th>
	<th style="width:273px;"><?php echo __('Rufnummer'); ?></th>
	<th style="width:65px;" class="r"><?php echo __('Wartezeit'); ?></th>
</tr>
</thead>
<tbody>

<?php

if (is_array( @$queue_stats['callers'] )) {
	if (count( $queue_stats['callers'] ) < 1) {
		echo '<tr><td colspan="3"><i>- ', __('keine'), ' -</i></td></tr>';
	} else {
		$i = 0;
		foreach ($queue_stats['callers'] as $pos => $caller) {
			echo '<tr class="', ((++$i % 2 == 0) ? 'even':'odd'), '">';
			
			echo '<td>', $pos, '</td>';
			
			echo '<td>';
			if ($caller['cidnum'] != '') {
				echo htmlEnt( $caller['cidnum'] );
			} else
				echo '<i>', __('unbekannt'), '</i>';
			if ($caller['cidname'] != '')
				echo ' &nbsp; (', htmlEnt( $caller['cidname'] ), ')';
			echo '</td>';
			
			$waittime = $caller['wait'];
			if ($waittime === null) $wait = '?';
			else {
				$mins = (int)floor($waittime / 60);
				$secs = str_pad((int)($waittime - $mins*60), 2, '0', STR_PAD_LEFT);
				$wait = $mins .':'. $secs;
			}
			echo '<td class="r">', $wait, '</td>';
			
			echo '</tr>', "\n";
		}
	}
}

?>

</tbody>
</table>
<br />

<?php
	}
}

?>

<script type="text/javascript">/*<![CDATA[*/
window.setTimeout('document.location.reload();', <?php echo $slow_refresh ? '40000':'15000'; ?>);
/*]]>*/</script>
