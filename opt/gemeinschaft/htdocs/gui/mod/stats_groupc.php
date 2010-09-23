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


$duration_level  = 90;  # 90 s = 1:30 min
$waittime_level  = 15;  # 15 s

//CDR Database Connection
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


$action = @$_REQUEST['action'];
if ($action == 'report') {
	$group    = @$_REQUEST['group'];
	$month_d  = (int)@$_REQUEST['month'   ];
} else {
	$action   = '';
	$group    =  '';
	//$month_d  = -1;  # previous month
	$month_d  =  0;  # current month
}

if (!$group) $group = @$_SESSION['sudo_user']['name'];

$user_groups    = gs_group_members_groups_get(Array(@$_SESSION['sudo_user']['info']['id']), 'user');
$select_groups  = gs_group_permissions_get($user_groups, 'call_stats', 'user');
$group_info     = gs_group_info_get($select_groups);

if (array_search($group, $select_groups) === false) {
	$exts_sql = '';
} else {
	$users = gs_group_members_get(Array($group));
	$exts_sql = userids_to_exts( $users );
} 

?>




<form method="get" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="report" />
<label for="ipt-group"><?php echo __('Gruppe'); ?>:</label>
<select name="group" id="group">
<?php

foreach ($group_info AS $group_select) {
	echo '<option value="',$group_select['id'],'"',($group_select['id']==$group ? ' selected="selected"' : ''),'>', $group_select['title'] ,'</option>' ,"\n";
}
?>
</select>

&nbsp;&nbsp;&nbsp;

<label for="ipt-month"><?php echo __('Monat'); ?>:</label>
<select name="month" id="ipt-month">
<?php
$t = time();
for ($i=-3; $i<=0; ++$i) {
	echo '<option value="',$i,'"', ($i==$month_d ? ' selected="selected"' : ''),'>', date('m / Y', (int)strToTime("$i months", $t)) ,'</option>' ,"\n";
}
?>
</select>

&nbsp;&nbsp;&nbsp;

<input type="submit" value="<?php echo __('Report'); ?>" />
</form>

<hr />

<?php

if ($action == '') return;

#####################################################################





$t         = (int)strToTime("$month_d months", $t);
$num_days  = (int)date('t', $t);
$y         = (int)date('Y', $t);
$m         = (int)date('n', $t);
$today_day = (int)date('j', $t);


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
			return tip(evt, '<?php echo __('Tag des gew&auml;hlten Monats'); ?>');
		case 'calls':
			return tip(evt, '<?php echo __('Anzahl der Anrufe f&uuml;r Kollegen aus der Gruppe'); ?>');
		case 'count':
			return tip(evt, '<?php echo __('Anzahl der Anrufe zu Kollegen, die l&auml;nger als 5 Sekunden dauerten (mit Klingeln) und somit in die Erfolgsquote einflie&szlig;en.'); ?>');
		case 'answered':
			return tip(evt, '<?php echo __('Anzahl der Anrufe zu Kollegen, die angenommen wurden'); ?>');
		case 'timeout':
			return tip(evt, '<?php echo __('Anzahl der Anrufe zu Kollegen, bei denen der Anrufer aufgelegt hat bevor abgehoben wurde. Dabei werden alle Anrufe unter 5 Sekunden nicht gewertet.'); ?>');
		case 'full':
			return tip(evt, '<?php echo __('Anzahl der Anrufe zu Kollegen, die fehlgeschlagen sind weil der Teilnehmer besetzt war'); ?>');
		case 'squota':
			return tip(evt, '<?php echo __('Verh&auml;ltnis von angenommenen Anrufen zu eingegangenen Anrufen in Prozent. Dabei werden alle Anrufe unter 5 Sekunden nicht gewertet.'); ?>');
		case 'durl':
			return tip(evt, '<?php echo __('Anzahl der angenommenen Anrufe zu Kollegen, deren Gespr&auml;chsdauer k&uuml;rzer als der angeg. Wert war'); ?>');
		case 'durg':
			return tip(evt, '<?php echo __('Anzahl der angenommenen Anrufe zu Kollegen, deren Gespr&auml;chsdauer l&auml;nger als der angeg. Wert war'); ?>');
		case 'duravg':
			return tip(evt, '<?php echo __('Durchschnittliche Gespr&auml;chsdauer der angenommenen Anrufe'); ?>');
		case 'holdlsl':
			return tip(evt, '<?php echo __('Anzahl der Anrufe zu Kollegen, bei denen der Anrufer weniger als die angeg. Dauer, aber mehr als 5 Sekunden gewartet hat'); ?>');
		case 'holdgsl':
			return tip(evt, '<?php echo __('Anzahl der Anrufe zu Kollegen, bei denen der Anrufer l&auml;nger als die angeg. Dauer gewartet hat'); ?>');
	}
	return undefined;
}
//]]>
</script>

<table cellspacing="1" class="phonebook" style="border:1px solid #ccc; background:#fff;">
<thead>
<tr>
	<th style="font-weight:normal;" onmouseover="mytip(event,'day');"><?php echo __('Tag'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'calls');"><?php echo __('Anrufe'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'count');"><?php echo __('gez.'); ?></th>
	
	<th style="font-weight:normal;" onmouseover="mytip(event,'answered');"><?php echo __('angen.'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'timeout');"><?php echo __('verp.'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'busy');"><?php echo __('besetzt'); ?></th>
	
	<th style="font-weight:normal;" onmouseover="mytip(event,'squota');"><?php echo __('Erfolgsquote'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'durl');"><?php echo __('Dauer'), ' &le;', _secs_to_minsecs($duration_level); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'durg');"><?php echo __('Dauer'), ' &gt;', _secs_to_minsecs($duration_level); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'duravg');"><?php echo '&empty; ', __('Dauer'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'holdlsl');"><?php echo __('Wartez.') ,'  0:5-', _secs_to_minsecs($waittime_level); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'holdgsl');"><?php echo __('Wartez.') ,' &gt;', _secs_to_minsecs($waittime_level); ?></th>
</tr>
</thead>
<tbody>

<?php

$totals = array(
	'num_entered'   => 0,
	'num_connected' => 0,
	'num_abandoned' => 0,
	'num_timeout'   => 0,
	'num_empty'     => 0,
	'num_full'      => 0,
	//'pct_connected' => 0,
	'num_dur_lower' => 0,
	'num_dur_higher'=> 0,
	//'avg_calldur'   => 0,
	'num_wait_ok'   => 0,
	'num_wait_fail' => 0
);

//$exts_sql = "2798,3536,1083,1023,1591,1679,3528,2737,2736,1761,1328,1843,1084,2937";
//$exts_sql = "9993";


$day_m_end   = (int)mkTime( 23,59,59 , $m,$num_days,$y );
$day_m_start = (int)mkTime(  0, 0, 0 , $m,        1,$y );

$user_name = @$_SESSION['sudo_user']['name'];

$table = 'cdr_tmp_'.$user_name;

$ok = $CDR_DB->execute( 'DROP TABLE IF EXISTS `'.$table.'`' );

$sql_query =
	'CREATE TEMPORARY TABLE `'.$table.'` TYPE=HEAP '.
		'SELECT * FROM `ast_cdr` WHERE '.
			'( `calldate` >= \''. date('Y-m-d H:i:s', $day_m_start) .'\' AND '.
			'  `calldate` <= \''. date('Y-m-d H:i:s', $day_m_end) .'\' ) AND '.
			'  `dst` IN ('. $exts_sql .') AND '.
			'  `channel` NOT LIKE \'Local/%\' AND '.
			'  `dstchannel` NOT LIKE \'SIP/gs-0%\' AND '.
			'  `dst` <> \'s\' AND '.
			'  `dst` <> \'h\' '
	;
if (! $CDR_DB->execute( $sql_query )) {
	echo '<div class="errorbox">', "Fehler beim Anlegen einer temporären Tabelle!" ,'</div>',"\n";
}

for ($day=1; $day<=$num_days; ++$day) {
	
	if ($month_d >= 0 && $day > $today_day) break;
	
	$day_t_start = (int)mkTime(  0, 0, 0 , $m,$day,$y );
	$day_t_end   = (int)mkTime( 23,59,59 , $m,$day,$y );
	$day_t_end_month  = (int)mkTime( 23,59,59 , $m,$num_days,$y );
	$dow         = (int)date('w', $day_t_start);
	$is_weekend  = ($dow==6 || $dow==0);
	
	$sql_query = 
		'SELECT COUNT(*) '.
		'FROM `'.$table.'` '.
		'WHERE '.
		'( `calldate`>=\''. date('Y-m-d H:i:s', $day_t_start) .'\' AND '.
		'  `calldate`<=\''. date('Y-m-d H:i:s', $day_t_end) .'\' )';
	
	$n_calls_in = (int) $CDR_DB->executeGetOne( $sql_query );
	
	switch ($dow) {
		case 5:  # friday
			$style_wd = ' style="border-bottom:1px solid #aaa;"'; break;
		case 0:  # sunday
			$style_wd = ' style="border-bottom:1px solid #666;"'; break;
		default:
			$style_wd = '';
	}
	
	
	echo '<tr class="', ($is_weekend ? 'even':'odd') ,'">', "\n";
	echo '<td class="r"',$style_wd,'>', $day ,'.</td>', "\n";
	
	
	if ($n_calls_in) {
		
		$sql_query = 
			'SELECT COUNT(*) '.
			'FROM `'.$table.'` '.
			'WHERE '.
			'( `calldate` >= \''. date('Y-m-d H:i:s', $day_t_start) .'\' AND '.
			'  `calldate` <= \''. date('Y-m-d H:i:s', $day_t_end) .'\' ) AND '.
			'( `disposition` = \''. $DB->escape('BUSY') .'\' OR '.
			'  `dcontext` = \'program-cc\')';
		
		$n_calls_busy = (int) $CDR_DB->executeGetOne( $sql_query );
		
		$sql_query = 
			'SELECT COUNT(*) '.
			'FROM `'.$table.'` '.
			'WHERE '.
			'( `calldate` >= \''. date('Y-m-d H:i:s', $day_t_start) .'\' AND '.
			'  `calldate` <= \''. date('Y-m-d H:i:s', $day_t_end) .'\' ) AND '.
			'  `disposition` = \''. $DB->escape('ANSWERED') .'\' AND '.
			'  `duration` > 5 AND '.
			'  `billsec` IS NOT NULL AND '.
			'  `billsec` <= '. (int)$duration_level;
		
		$n_calls_dur_lower = $CDR_DB->executeGetOne( $sql_query );
		
		$sql_query = 
			'SELECT COUNT(*) '.
			'FROM `'.$table.'` '.
			'WHERE '.
			'( `calldate` >= \''. date('Y-m-d H:i:s', $day_t_start) .'\' AND '.
			'  `calldate` <= \''. date('Y-m-d H:i:s', $day_t_end) .'\' ) AND '.
			'  `disposition` = \''. $DB->escape('ANSWERED') .'\' AND '.
			'  `billsec` > '. (int)$duration_level;
		
		$n_calls_dur_higher = $CDR_DB->executeGetOne( $sql_query );
		
		$sql_query = 
			'SELECT AVG(`billsec`) '.
			'FROM `'.$table.'` '.
			'WHERE '.
			'( `calldate` >= \''. date('Y-m-d H:i:s', $day_t_start) .'\' AND '.
			'`calldate` <= \''. date('Y-m-d H:i:s', $day_t_end) .'\' ) AND '.
			'`disposition` = \''. $DB->escape('ANSWERED') .'\' AND '.
			'`billsec` > 0';
		
		$calls_dur_avg = $CDR_DB->executeGetOne( $sql_query );
		
		$sql_query = 
			'SELECT COUNT(*) '.
			'FROM `'.$table.'` '.
			'WHERE '.
			'( `calldate`>=\''. date('Y-m-d H:i:s', $day_t_start) .'\' AND '.
			'  `calldate`<=\''. date('Y-m-d H:i:s', $day_t_end) .'\' ) AND '.
			'( `disposition`=\''. $DB->escape('ANSWERED') .'\' OR '.
			'  `disposition`=\''. $DB->escape('NO ANSWER') .'\') AND '.
			'  `duration` > 5 AND '.
			'( `duration` - `billsec`) <= '. (int)$waittime_level;
		
		$n_calls_wait_lower = $CDR_DB->executeGetOne( $sql_query );	
		
		$sql_query = 
			'SELECT COUNT(*) '.
			'FROM `'.$table.'` '.
			'WHERE '.
			'( `calldate`>=\''. date('Y-m-d H:i:s', $day_t_start) .'\' AND '.
			'  `calldate`<=\''. date('Y-m-d H:i:s', $day_t_end) .'\' ) AND '.
			'( `disposition`=\''. $DB->escape('ANSWERED') .'\' OR '.
			'  `disposition`=\''. $DB->escape('NO ANSWER') .'\') AND '.
			'  `duration` IS NOT NULL AND '.
			'( `duration` - `billsec`) > '. (int)$waittime_level;
		
		$n_calls_wait_higher = $CDR_DB->executeGetOne( $sql_query );	
		
		$n_calls_in_stat     = $n_calls_wait_lower + $n_calls_wait_higher;
		$n_calls_answer      = $n_calls_dur_lower + $n_calls_dur_higher;
	//	$n_calls_noanswer    = $n_calls_in - $n_calls_answer - $n_calls_busy;
		$n_calls_noanswer    = $n_calls_in_stat - $n_calls_answer;	
		
		$pct_connected       = ($n_calls_answer > 0)
			? ($n_calls_answer / $n_calls_in_stat)
			: 0.0;
		$pct_connected = round($pct_connected*100);
		
		$totals['n_calls_in'          ] += $n_calls_in;
		$totals['n_calls_in_stat'     ] += $n_calls_in_stat;
		$totals['n_calls_answer'      ] += $n_calls_answer;
		$totals['n_calls_noanswer'    ] += $n_calls_noanswer;
		$totals['n_calls_busy'        ] += $n_calls_busy;
		$totals['n_calls_dur_lower'   ] += $n_calls_dur_lower;
		$totals['n_calls_dur_higher'  ] += $n_calls_dur_higher;
		$totals['calls_dur_avg'       ] += $calls_dur_avg;
		$totals['n_calls_wait_lower'  ] += $n_calls_wait_lower;
		$totals['n_calls_wait_higher' ] += $n_calls_wait_higher;
		
		
	//	echo '<tr class="', ($is_weekend ? 'even':'odd') ,'">', "\n";
	//	echo '<td class="r"',$style_wd,'>', $day                 ,'.</td>', "\n";
		echo '<td class="r"',$style_wd,'>', $n_calls_in          ,'</td>', "\n";
		echo '<td class="r"',$style_wd,'>', $n_calls_in_stat     ,'</td>', "\n";
		echo '<td class="r"',$style_wd,'>', $n_calls_answer      ,'</td>', "\n";
		echo '<td class="r"',$style_wd,'>', $n_calls_noanswer    ,'</td>', "\n";
		echo '<td class="r"',$style_wd,'>', $n_calls_busy        ,'</td>', "\n";
	//	echo '<td class="r"',$style_wd,'>', $pct_connected       ,' <small>%</small></td>', "\n";
		echo '<td class="r"',$style_wd,'><div class="bargraph" style="width: ',$pct_connected,'%;">', $pct_connected ,'&nbsp;<small>%</small></div></td>', "\n";
		echo '<td class="r"',$style_wd,'>', $n_calls_dur_lower   ,'</td>', "\n";
		echo '<td class="r"',$style_wd,'>', $n_calls_dur_higher  ,'</td>', "\n";
		echo '<td class="r"',$style_wd,'>', _secs_to_minsecs($calls_dur_avg) ,'</td>', "\n";
		echo '<td class="r"',$style_wd,'>', $n_calls_wait_lower  ,'</td>', "\n";
		echo '<td class="r"',$style_wd,'>', $n_calls_wait_higher ,'</td>', "\n";
		
	} else {
		
		for ($i=0; $i < 11; $i++) {
			echo '<td class="r"',$style_wd,'></td>', "\n";
		}
		
	}
	echo '</tr>', "\n";
	
}
--$day;


$month_t_start = (int)mkTime(  0, 0, 0, $m, 1   , $y );
$month_t_end   = (int)mkTime( 23,59,59, $m, $day, $y );


$pct_connected_month = ($totals['n_calls_answer' ] > 0)
	? ($totals['n_calls_answer' ] / $totals['n_calls_in_stat'   ])
	: 0.0;
$pct_connected_month = round($pct_connected_month*100);

$calls_dur_avg_month = 0;
$calls_dur_month = 0;

if ( $totals['n_calls_answer'] ) {
	
	$sql_query =
		'SELECT AVG(`billsec`) '.
		'FROM `'.$table.'` '.
		'WHERE '.
		'( `calldate`>=\''. date('Y-m-d H:i:s', $month_t_start) .'\' AND '.
		'  `calldate`<=\''. date('Y-m-d H:i:s', $month_t_end) .'\' ) AND '.
		'  `disposition`=\''. $DB->escape('ANSWERED') .'\' AND '.
		'`billsec` > 0';
	
	$calls_dur_avg_month = $CDR_DB->executeGetOne( $sql_query );
}

if ($totals['n_calls_answer'] > 0) {

	$sql_query =
		'SELECT SUM(`billsec`) '.
		'FROM `'.$table.'` '.
		'WHERE '.
		'( `calldate`>=\''. date('Y-m-d H:i:s', $month_t_start) .'\' AND '.
		'  `calldate`<=\''. date('Y-m-d H:i:s', $month_t_end) .'\' ) AND '.
		'  `disposition`=\''. $DB->escape('ANSWERED') .'\' AND '.
		'`billsec` > 0';
	
	$calls_dur_month = $CDR_DB->executeGetOne( $sql_query );
}

$ok = $CDR_DB->execute( 'DROP TABLE `'.$table.'`' );


$style = 'style="border-top:3px solid #b90; background:#feb; line-height:2.5em;"';
echo '<tr>', "\n";

echo '<td class="r" ',$style,'><b>&sum;</b></td>', "\n";
echo '<td class="r" ',$style,'>', $totals['n_calls_in'          ] ,'</td>', "\n";
echo '<td class="r" ',$style,'>', $totals['n_calls_in_stat'     ] ,'</td>', "\n";
echo '<td class="r" ',$style,'>', $totals['n_calls_answer'      ] ,'</td>', "\n";
echo '<td class="r" ',$style,'>', $totals['n_calls_noanswer'    ] ,'</td>', "\n";
echo '<td class="r" ',$style,'>', $totals['n_calls_busy'        ] ,'</td>', "\n";
//echo '<td class="r" ',$style,'>', $pct_connected_month ,' <small>%</small></td>', "\n";
echo '<td class="r" ',$style,'><div class="bargraph" style="width: ',$pct_connected_month,'%;">', $pct_connected_month ,'&nbsp;<small>%</small></div></td>', "\n";
echo '<td class="r" ',$style,'>', $totals['n_calls_dur_lower'   ] ,'</td>', "\n";
echo '<td class="r" ',$style,'>', $totals['n_calls_dur_higher'  ] ,'</td>', "\n";
echo '<td class="r" ',$style,'>', _secs_to_minsecs($calls_dur_avg_month) ,'</td>', "\n";
echo '<td class="r" ',$style,'>', $totals['n_calls_wait_lower'  ] ,'</td>', "\n";
echo '<td class="r" ',$style,'>', $totals['n_calls_wait_higher' ] ,'</td>', "\n";

echo '</tr>', "\n";

?>

</tbody>
</table>


<p style="padding:0.5em 0;"><?php echo __('Gespr&auml;chsminuten'), ': &nbsp; ', round($calls_dur_month/60); ?></p>

</div>
