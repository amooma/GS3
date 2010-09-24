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

######################################################
##
##   ALL STRINGS IN HERE NEED TO BE TRANSLATED!
##
######################################################


defined('GS_VALID') or die('No direct access.');

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

include_once( GS_DIR .'htdocs/gui/inc/permissions.php' );
require_once( GS_DIR .'inc/group-fns.php' );


echo '<script type="text/javascript" src="', GS_URL_PATH ,'js/tooltips.js"></script>' ,"\n";

$CDR_DB = gs_db_cdr_master_connect();
if (! $CDR_DB) {
	echo 'CDR DB error.'; 
	return;
}

$duration_level  = 90;  # 90 s = 1:30 min
$waittime_level = 15;  # 15 s

# connect to CDR master
$CDR_DB = gs_db_cdr_master_connect();
if (! $CDR_DB) {
	echo 'CDR DB error.';
	return;
}


function _secs_to_minsecs( $s )
{
	$s = (int)$s;
	$m = floor($s/60);
	$s = $s - $m*60;
	return $m .':'. str_pad($s, 2, '0', STR_PAD_LEFT);
}

function userids_to_exts( $users )
{
	global $DB;
	
	$users_sql = implode(',',$users);
	
	$rs = $DB->execute(
		'SELECT `name` AS `ext` '.
		'FROM `ast_sipfriends` '.
		'WHERE `_user_id` IN ('. $users_sql .')'
	);
	
	$exts = array();
	
	if ($rs) {
		while ($r = $rs->fetchRow()) {
			$exts[] = '\''.$DB->escape($r['ext']).'\'';
		}
	}
	return implode(',', $exts);
}

$sudo_url = (@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
	? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);

$queue_id = 0;

$action = @$_REQUEST['action'];
if ($action == 'report') {
	$group = @$_REQUEST['group'];
	$queue_id = (int)@$_REQUEST['queue_id'];
	$day_d  = @$_REQUEST['day'   ];
	$h_start = (int) @$_REQUEST['hstart'];
	$h_end = (int) @$_REQUEST['hend'];
} else {
	$action   = '';
	$group =   @$_SESSION['sudo_user']['name'];
	//$month_d  = -1;  # previous month
	$day_d  = 0;  # current month
	$h_start = 7;
	$h_end = 18;
}

if (!$group) $group = @$_SESSION['sudo_user']['name'];

$user_groups    = gs_group_members_groups_get(Array(@$_SESSION['sudo_user']['info']['id']), 'user');
$select_groups  = gs_group_permissions_get($user_groups, 'call_stats','user');

$group_info     = gs_group_info_get($select_groups);

$queue_groups    = gs_group_members_get(gs_group_permissions_get($user_groups, 'call_stats', 'queue'));

if (array_search($group, $select_groups) === FALSE) 
	$exts_sql = '';
else {

	$users = gs_group_members_get(Array($group));
	$exts_sql = userids_to_exts( $users );

} 

if (array_search($queue_id, $queue_groups) === FALSE) 
	$queue_id = 0;

?>




<form method="get" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="report" />
<label for="ipt-group"><?php echo __('Gruppe'); ?>:</label>
<select name="group" id="group">
<?php
echo '<option value="0"> - </option>' ,"\n";
foreach ($group_info AS $group_select) {
	echo '<option value="',$group_select['id'],'"',($group_select['id']==$group ? ' selected="selected"' : ''),'>', $group_select['title'] ,'</option>' ,"\n";
}
?>
</select>

&nbsp;&nbsp;&nbsp;

<label for="ipt-queue_id"><?php echo __('Warteschlange'); ?>:</label>
<select name="queue_id" id="ipt-queue_id">
<?php
echo '<option value="0"> - </option>' ,"\n";
if (count($queue_groups) > 0) {
	$rs = $DB->execute( 'SELECT `_id`, `name`, `_title` FROM `ast_queues` WHERE `_id` IN ('.implode(',',$queue_groups).') ORDER BY `name`' );
	if ($rs) {
		while ($r = $rs->fetchrow()) {
			echo '<option value="',$r['_id'],'"', ($r['_id']==$queue_id ? ' selected="selected"' : ''),'>', $r['name'] ,' (', htmlEnt($r['_title']) ,')' ,'</option>' ,"\n";
		}
	}
}
?>
</select>

&nbsp;&nbsp;&nbsp;

<label for="ipt-week"><?php echo __('Woche'); ?>:</label>
<select name="day" id="ipt-week">
<?php
$t = time();
$month_d = 0;
for ($i=-12; $i<=0; ++$i) {
	$t         = time() + $day_d;
	$dow       = (int)date('w',$t);
	$t         = (int)strToTime("$i week", $t);
	$t         = (int)strToTime(($dow-1)." days ago", $t);
	$num_days  = (int)date('t', $t);
	$y         = (int)date('Y', $t);
	$m         = (int)date('n', $t);
	$today_day = (int)date('j', $t);
	
	echo '<option value="',$i,'"', (($i==$day_d) ? ' selected="selected"' : ''),'>',date('d.m.Y',$t)."-".date('d.m.Y',$t+345600)," ",'</option>' ,"\n";
}

echo "</select>\n";
echo "&nbsp;&nbsp;&nbsp;";
echo '<select name="hstart" id="ipt-hstart">'."\n";

for ($i=0; $i<=24; ++$i) {
	echo '<option value="',$i,'"', (($i==$h_start) ? ' selected="selected"' : ''),'>',$i,'</option>' ,"\n";
}

echo "</select>\n";
echo "-\n";
echo "</select>\n";
echo '<select name="hend" id="ipt-hend">'."\n";

for ($i=0; $i<=24; ++$i) {
	echo '<option value="',$i,'"', (($i==$h_end) ? ' selected="selected"' : ''),'>',$i,'</option>' ,"\n";
}

echo "</select>\n";

?>
<label><?php echo __('Uhr'); ?></label>
&nbsp;&nbsp;&nbsp;

<input type="submit" value="<?php echo __('Report'); ?>" />
</form>

<hr />

<?php

if ($action == '') return;


?>

<?php /*
<div id="chart" style="position:absolute; left:189px; right:12px; top:14em; bottom:10px; overflow:scroll; border:1px solid #ccc; background:#fff;">
*/ ?>


<script type="text/javascript">
function chart_fullscreen_toggle()
{
	var chart = document.getElementById('chart');
	var toggle = document.getElementById('chart-fullscreen-toggle');
	if (chart && toggle) {
		if (chart.style.position == 'absolute') {
			chart.style.position = 'static';
			chart.style.top        = '';
			chart.style.left       = '';
			chart.style.right      = '';
			chart.style.bottom     = '';
			chart.style.background = 'transparent';
			chart.style.padding    = '0';
			toggle.src = '<?php echo GS_URL_PATH; ?>crystal-svg/16/act/window_fullscreen.png';
		} else {
			chart.style.position = 'absolute';
			chart.style.top        = '0';
			chart.style.left       = '0';
			chart.style.right      = '0';
			chart.style.bottom     = '0';
			chart.style.background = '#fff';
			chart.style.padding    = '0.4em 0.8em 0.7em 0.8em';
			toggle.src = '<?php echo GS_URL_PATH; ?>crystal-svg/16/act/window_nofullscreen.png';
		}
	}
}
</script>



<div id="chart">
<img id="chart-fullscreen-toggle" class="fr" style="cursor:pointer; margin:0 1px 1px 0;" title="Fullscreen" alt="Fullscreen" onclick="chart_fullscreen_toggle();" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/window_fullscreen.png" />
<small>(<?php echo __('Zeiger &uuml;ber Spalten&uuml;berschriften bewegen f&uuml;r Beschreibung'); ?>)</small>
<br style="clear:right;" />

<script type="text/javascript">
//<![CDATA[
function mytip( evt, key )
{
	switch (key) {
		case 'day':
			return tip(evt, '<?php echo __('Tag der Woche'); ?>');
		case 'time':
			return tip(evt, '<?php echo __('Uhrzeit'); ?>');
		case 'rate':
			return tip(evt, '<?php echo __('Erfolgsquote'); ?>');
		case 'answer':
			return tip(evt, '<?php echo __('Anzahl angenommener Anrufe'); ?>');
		case 'missed':
			return tip(evt, '<?php echo __('Anzahl verpasster Anrufe'); ?>');
	}
	return undefined;
}
//]]>
</script>

<table cellspacing="1" class="phonebook" style="border:1px solid #ccc; background:#fff;">
<thead>
<tr>
	<th></th>
	<th colspan="3" style="font-weight:normal;" onmouseover="mytip(event,'day');"><?php echo __('Montag'); ?></th>
	<th colspan="3" style="font-weight:normal;" onmouseover="mytip(event,'day');"><?php echo __('Dienstag'); ?></th>
	<th colspan="3" style="font-weight:normal;" onmouseover="mytip(event,'day');"><?php echo __('Mittwoch'); ?></th>
	<th colspan="3" style="font-weight:normal;" onmouseover="mytip(event,'day');"><?php echo __('Donnerstag'); ?></th>
	<th colspan="3" style="font-weight:normal;" onmouseover="mytip(event,'day');"><?php echo __('Freitag'); ?></th>
</tr>

<tr>
	<th style="font-weight:normal;" onmouseover="mytip(event,'time');"><?php echo __('Zeit'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'rate');"><?php echo __('Erfolgsquote'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'answer');"><?php echo __('ang.'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'missed');"><?php echo __('verp.'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'rate');"><?php echo __('Erfolgsquote'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'answer');"><?php echo __('ang.'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'missed');"><?php echo __('verp.'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'rate');"><?php echo __('Erfolgsquote'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'answer');"><?php echo __('ang.'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'missed');"><?php echo __('verp.'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'rate');"><?php echo __('Erfolgsquote'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'answer');"><?php echo __('ang.'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'missed');"><?php echo __('verp.'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'rate');"><?php echo __('Erfolgsquote'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'answer');"><?php echo __('ang.'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'missed');"><?php echo __('verp.'); ?></th>
	
</tr>
</thead>
<tbody>

<?php

//$t = time()  + (3600 * 24 * 7 * $day_d );;

$t         = time() + $day_d;
$dow        = (int)date('w',$t);
$t         = (int)strToTime("$day_d week", $t);
$t         = (int)strToTime(($dow-1)." days ago", $t);

$t_year   = (int)date('Y', $t);
$t_month  = (int)date('n', $t);
$t_day    = (int)date('j', $t );

$day_w_start = (int)mkTime(  0, 0, 0 , $t_month,$t_day,$t_year );
$day_w_end  = $day_w_start + (3600 * 24 * 5 - 1); 

$user_name = @$_SESSION['sudo_user']['name'];

$table = 'cdr_tmp_'.$user_name;

$ok = $CDR_DB->execute( 'DROP TABLE IF EXISTS `'.$table.'`' );

$sql_query =
	'CREATE TEMPORARY TABLE `'.$table.'` TYPE=HEAP '.
		'SELECT * FROM `ast_cdr` WHERE '.
			'( `calldate` >= \''. date('Y-m-d H:i:s', $day_w_start) .'\' AND '.
			'  `calldate` <= \''. date('Y-m-d H:i:s', $day_w_end) .'\' ) AND '.
			'  `dst` IN ('. $exts_sql .') AND '.
			'  `channel` NOT LIKE \'Local/%\' AND '.
			'  `dstchannel` NOT LIKE \'SIP/gs-0%\' AND '.
			'  `dst` <> \'s\' AND '.
			'  `dst` <> \'h\' '
	;
if (! $CDR_DB->execute( $sql_query )) {
	echo '<div class="errorbox">', "Fehler beim Anlegen einer temporären Tabelle!" ,'</div>',"\n";
}

//echo "START date : $t_day.$t_month.$t_year<br>\n";

for ($hour=$h_start; $hour<=$h_end; ($hour=$hour+0.5)) {
	$hour_t_start = (int)mkTime( 0 , 0, 0 , $t_month,$t_day,$t_year ) + (3600 * $hour);
	$hour_t_end = $hour_t_start + 1799;
	$time_str = date('H:i',$hour_t_start );
	$sql_time    = '(`timestamp`>='.$hour_t_start .' AND `timestamp`<='.$hour_t_end .')';
	$style_wd = '';

	echo '<tr>', "\n";      
	echo '<td class="r"',$style_wd,'>', $time_str ,'</td>', "\n";

	for ($day=0 ; $day < 5; $day++) {
	
		$hour_t_start = (int)mkTime( 0 , 0, 0 , $t_month ,$t_day+$day ,$t_year ) + (3600 * $hour);
		$hour_t_end = $hour_t_start + 1799;
		$time_str = date('H:i',$hour_t_start );
		$sql_time    = '(`a`.`timestamp`>='.$hour_t_start .' AND `a`.`timestamp`<='.$hour_t_end .') AND (`b`.`timestamp`>='.$hour_t_start.' AND `b`.`timestamp`<='.($hour_t_end + 86400).')';
	

		$queue_num_connected = (int)@$CDR_DB->executeGetOne(
		'SELECT COUNT(*) FROM `queue_log` `a`, `queue_log` `b` WHERE
		`a`.`queue_id`='. $queue_id .'
		AND `a`.`ast_call_id` = `b`.`ast_call_id`
		AND `a`.`event`=\'_ENTER\'
		AND `b`.`event`=\'_CONNECT\'
		AND '. $sql_time
			);

	 $sql_time    = '(`timestamp`>='.$hour_t_start .' AND `timestamp`<='.$hour_t_end .')';

	 $num_entered = (int)@$CDR_DB->executeGetOne(
	'SELECT COUNT(DISTINCT(`ast_call_id`)) FROM `queue_log` WHERE
	`queue_id`='. $queue_id .'
	AND `event`=\'_ENTER\'
	AND '. $sql_time
		);

	if ($exts_sql) {
	
		$sql_query =
	'SELECT COUNT(*)
	FROM `'.$table.'`
	WHERE
	`dst` IN ('. $exts_sql .') AND
	( `calldate`>=\''. date('Y-m-d H:i:s', $hour_t_start) .'\' AND 
	`calldate`<=\''. date('Y-m-d H:i:s', $hour_t_end) .'\' ) AND
	`disposition`=\''. $CDR_DB->escape('ANSWERED') .'\' AND
	`duration` > 5 AND
	`billsec` IS NOT NULL AND
	`channel` NOT LIKE \'Local/%\' AND
	`dstchannel` NOT LIKE \'SIP/gs-0%\' AND
	`dst`<>\'s\' AND
	`dst`<>\'h\'';
	
		$n_calls_answer = $CDR_DB->executeGetOne( $sql_query );
	
	$sql_query =
	'SELECT COUNT(*)
	FROM `'.$table.'`
	WHERE
	`dst` IN ('. $exts_sql .') AND
	( `calldate`>=\''. date('Y-m-d H:i:s', $hour_t_start) .'\' AND 
	`calldate`<=\''. date('Y-m-d H:i:s', $hour_t_end) .'\' ) AND
	( `disposition`=\''. $CDR_DB->escape('ANSWERED') .'\' OR  `disposition`=\''. $CDR_DB->escape('NO ANSWER') .'\')  AND
	`duration` > 5 AND
	`channel` NOT LIKE \'Local/%\' AND
	`dstchannel` NOT LIKE \'SIP/gs-0%\' AND
	`dst`<>\'s\' AND
	`dst`<>\'h\'';
	
		$n_calls_in = $CDR_DB->executeGetOne( $sql_query );
	
	} else {
		$n_calls_answer = 0;
		$n_calls_in = 0;
	}
	
		$calls_in_stat =  $n_calls_in + $num_entered;
		$num_connected = $queue_num_connected + $n_calls_answer;
	
		$pct_connected = ($num_connected > 0)
		? ($num_connected / $calls_in_stat  )
		: 0.0;
		$pct_connected = round($pct_connected*100);
	
		$calls_in_stat_day[$day] = @$calls_in_stat_day[$day] + $calls_in_stat ;
		$num_connected_day[$day] = @$num_connected_day[$day] + $num_connected;
	
	
		if ($calls_in_stat) {
		echo '<td class="c"'."\n";
		echo '<div class="bargraph" style="width: '.$pct_connected.'%;">'.$pct_connected.'&nbsp;%</div>'."\n";
		echo '</td>', "\n";
		echo '<td class="r"',$style_wd,'>', $num_connected,'</td>', "\n";
		echo '<td class="r"',$style_wd,'>', ($calls_in_stat-$num_connected) ,'</td>', "\n";
	} else {
		echo '<td class="r"',$style_wd,'>', '</td>', "\n";
		echo '<td class="r"',$style_wd,'>', '</td>', "\n";
		echo '<td class="r"',$style_wd,'>', '</td>', "\n";
	
	}

}

	echo '</tr>', "\n";

}
$style = 'style="border-top:3px solid #b90; background:#feb; line-height:2.5em;"';
echo '<tr>', "\n";
echo '<td class="r"',$style,'><b>&sum;</b></td>', "\n";
for ($day=0 ; $day < 5; $day++) {

	$pct_connected = ($num_connected_day[$day] > 0)
		? ($num_connected_day[$day] / $calls_in_stat_day[$day]  )
		: 0.0;
	$pct_connected = round($pct_connected*100);

	echo '<td class="c" ',$style,'>'."\n";
	echo '<div class="bargraph" style="width: '.$pct_connected.'%;">'.$pct_connected.'&nbsp;%</div>'."\n";
	echo '</td>', "\n";
	echo '<td class="r"',$style,'>', $num_connected_day[$day],'</td>', "\n";
	echo '<td class="r"',$style,'>', ($calls_in_stat_day[$day]-$num_connected_day[$day]) ,'</td>', "\n";
}
echo '</tr>', "\n";

$ok = $CDR_DB->execute( 'DROP TABLE `'.$table.'`' );


?>

</tbody>
</table>

</div>

