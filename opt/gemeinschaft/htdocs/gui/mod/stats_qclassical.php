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
include_once( GS_DIR .'inc/group-fns.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

echo '<script type="text/javascript" src="', GS_URL_PATH ,'js/tooltips.js"></script>' ,"\n";

$CDR_DB = gs_db_cdr_master_connect();
if (! $CDR_DB) {
	echo 'CDR DB error.';
	return;
}

$duration_level  = 90;  # 90 s = 1:30 min
$waittime_level = 15;  # 15 s


function _secs_to_minsecs( $s )
{
	$s = (int)$s;
	$m = floor($s/60);
	$s = $s - $m*60;
	return $m .':'. str_pad($s, 2, '0', STR_PAD_LEFT);
}

$sudo_url = (@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
	? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);


$action = @$_REQUEST['action'];
if ($action == 'report') {
	$queue_id = (int)@$_REQUEST['queue_id'];
	$month_d  = (int)@$_REQUEST['month'   ];
} else {
	$action   = '';
	$queue_id =  0;
	//$month_d  = -1;  # previous month
	$month_d  = 0;  # current month
}

$user_groups    = gs_group_members_groups_get(Array(@$_SESSION['sudo_user']['info']['id']), 'user');
$queue_groups   = gs_group_members_get(gs_group_permissions_get($user_groups, 'call_stats', 'queue'));

?>


<form method="get" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="report" />

<label for="ipt-queue_id"><?php echo __('Warteschlange'); ?>:</label>
<select name="queue_id" id="ipt-queue_id">
<?php
$rs = $DB->execute( 'SELECT `_id`, `name`, `_title` FROM `ast_queues` WHERE `_id` IN ('.implode(',',$queue_groups).') ORDER BY `name`' );

if ($rs)
	while ($r = $rs->fetchrow()) {
		echo '<option value="',$r['_id'],'"', ($r['_id']==$queue_id ? ' selected="selected"' : ''),'>', $r['name'] ,' (', htmlEnt($r['_title']) ,')' ,'</option>' ,"\n";
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
			return tip(evt, '<?php echo __('Anzahl der Anrufe auf diese Warteschlange'); ?>');
		case 'answered':
			return tip(evt, '<?php echo __('Anzahl der Anrufe auf diese Warteschlange, die von Agenten angenommen wurden'); ?>');
		case 'abandoned':
			return tip(evt, '<?php echo __('Anzahl der Anrufe auf diese Warteschlange, bei denen der Anrufer aufgelegt hat bevor abgehoben wurde'); ?>');
		case 'timeout':
			return tip(evt, '<?php echo __('Anzahl der Anrufe auf diese Warteschlange, die durch eine Zeit&uuml;berschreitung abgebrochen/weitergeleitet wurden. Dies kann auftreten wenn f&uuml;r die Warteschlange eine Weiterleitung nach Zeit eingestellt ist.'); ?>');
		case 'noag':
			return tip(evt, '<?php echo __('Anzahl der Anrufe auf diese Warteschlange, die fehlgeschlagen sind weil keine Agenten eingeloggt/frei waren'); ?>');
		case 'full':
			return tip(evt, '<?php echo __('Anzahl der Anrufe auf diese Warteschlange, die fehlgeschlagen sind weil die maximale Anzahl an Anrufern erreicht war'); ?>');
		case 'squota':
			return tip(evt, '<?php echo __('Verh&auml;ltnis von angenommenen Anrufen zu eingegangenen Anrufen in Prozent'); ?>');
		case 'durl':
			return tip(evt, '<?php echo __('Anzahl der angenommenen Anrufe auf diese Warteschlange, deren Gespr&auml;chsdauer k&uuml;rzer als der angeg. Wert war'); ?>');
		case 'durg':
			return tip(evt, '<?php echo __('Anzahl der angenommenen Anrufe auf diese Warteschlange, deren Gespr&auml;chsdauer l&auml;nger als der angeg. Wert war'); ?>');
		case 'duravg':
			return tip(evt, '<?php echo __('Durchschnittliche Gespr&auml;chsdauer der angenommenen Anrufe auf diese Warteschlange'); ?>');
		case 'holdlsl':
			return tip(evt, '<?php echo __('Anzahl der Anrufe auf diese Warteschlange, bei denen der Anrufer weniger als die angeg. Dauer gewartet hat'); ?>');
		case 'holdgsl':
			return tip(evt, '<?php echo __('Anzahl der Anrufe auf diese Warteschlange, bei denen der Anrufer l&auml;nger als die angeg. Dauer gewartet hat'); ?>');
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
	<th style="font-weight:normal;" onmouseover="mytip(event,'answered');"><?php echo __('Angen.'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'abandoned');"><?php echo __('Absprung'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'timeout');"><?php echo __('Timeout'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'noag');"><?php echo __('keine Ag.'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'full');"><?php echo __('voll'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'squota');"><?php echo __('Erfolgsquote'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'durl');"><?php echo __('Dauer'), ' &le;', _secs_to_minsecs($duration_level); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'durg');"><?php echo __('Dauer'), ' &gt;', _secs_to_minsecs($duration_level); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'duravg');"><?php echo '&empty; ', __('Dauer'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'holdlsl');"><?php echo __('Wartez.') ,' &le;', _secs_to_minsecs($waittime_level); ?></th>
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

$lang_2 = subStr($_SESSION['isolang'],0,2);

function ordinal( $cdnl, $lang, $html=false ) //FIXME
{
	$lang_2 = strToLower(subStr($lang,0,2));
	
	switch ($lang_2) { //FIXME
		case 'de':
			return $cdnl . '.';
			break;
		case 'en':
			$mod_10 = abs($cdnl) % 10;
			$ext = ((abs($cdnl) %100 < 21 && abs($cdnl) %100 > 4) ? 'th'
				: (($mod_10 < 4) ? ($mod_10 < 3) ? ($mod_10 < 2) ? ($mod_10 < 1)
				? 'th' : 'st' : 'nd' : 'rd' : 'th'));
			return $cdnl . ($html ? '<sup>'.$ext.'</sup>' : $ext);
			break;
		default:
			return $cdnl . '.';
			break;
	}
}

for ($day=1; $day<=$num_days; ++$day) {
	
	if ($month_d >= 0 && $day > $today_day) break;
	
	$day_t_start = (int)mkTime(  0, 0, 0 , $m,$day,$y );
	$day_t_end   = (int)mkTime( 23,59,59 , $m,$day,$y );
	$dow         = (int)date('w', $day_t_start);
	$is_weekend  = ($dow==6 || $dow==0);
	$sql_time    = '(`timestamp`>='.$day_t_start .' AND `timestamp`<='.$day_t_end .')';
	switch ($dow) {
	case 5:  # friday
		$style_wd = ' style="border-bottom:1px solid #aaa;"'; break;
	case 0:  # sunday
		$style_wd = ' style="border-bottom:1px solid #666;"'; break;
	default:
		$style_wd = '';
	}
	
	
	echo '<tr class="', ($is_weekend ? 'even':'odd') ,'">', "\n";
	
	
	# day
	#
	echo '<td class="r"',$style_wd,'>';
	echo ordinal( $day, $lang_2, true );
	echo '</td>', "\n";
	
	
	# inbound calls
	#
	$num_entered = (int)@$CDR_DB->executeGetOne(
'SELECT COUNT(DISTINCT(`ast_call_id`)) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event`=\'_ENTER\'
AND '. $sql_time
	);
	echo '<td class="r"',$style_wd,'>', $num_entered ,'</td>', "\n";
	$totals['num_entered'] += $num_entered;
	
	
	# connected to an agent
	#
	$num_connected = (int)@$CDR_DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event`=\'_CONNECT\'
AND '. $sql_time
	);
	echo '<td class="r"',$style_wd,'>', $num_connected ,'</td>', "\n";
	$totals['num_connected'] += $num_connected;
	
	
	# abandoned
	#
	$num_abandoned = (int)@$CDR_DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event`=\'_EXIT\'
AND `reason`=\'ABANDON\'
AND '. $sql_time
	);
	echo '<td class="r"',$style_wd,'>', $num_abandoned ,'</td>', "\n";
	$totals['num_abandoned'] += $num_abandoned;
	
	
	# timeout
	#
	$num_timeout = (int)@$CDR_DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event`=\'_EXIT\'
AND `reason`=\'TIMEOUT\'
AND '. $sql_time
	);
	echo '<td class="r"',$style_wd,'>', $num_timeout ,'</td>', "\n";
	$totals['num_timeout'] += $num_timeout;
	
	
	# no queue members
	#
	$num_empty = (int)@$CDR_DB->executeGetOne(
'SELECT COUNT(DISTINCT(`ast_call_id`)) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event`=\'_EXIT\'
AND `reason`=\'EMPTY\'
AND '. $sql_time
	);
	echo '<td class="r"',$style_wd,'>', $num_empty ,'</td>', "\n";
	$totals['num_empty'] += $num_empty;
	
	
	# queue full
	#
	$num_full = (int)@$CDR_DB->executeGetOne(
'SELECT COUNT(DISTINCT(`ast_call_id`)) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND ((
    `event`=\'_EXIT\'
AND `reason`=\'FULL\')
OR  `event`=\'_EXITFULL\')
AND '. $sql_time
	);
	# the custom event "_EXITFULL" needs to stay here as an intermediate
	# solution because we did not convert it to "_EXIT" with reason "FULL"
	# in sbin/gs-queuelog-to-db up to rev. 2846. fixed in rev. 2847
	echo '<td class="r"',$style_wd,'>', $num_full ,'</td>', "\n";
	$totals['num_full'] += $num_full;
	
	
	# % connected
	#
	$pct_connected = ($num_connected > 0)
		? ($num_connected / $num_entered)
		: 0.0;
	$pct_connected = round($pct_connected*100);
	echo '<td class="r"',$style_wd,'><div class="bargraph" style="width: '.$pct_connected.'%;">'.$pct_connected.'&nbsp;<small>%</small></div></td>', "\n";
	//$totals['pct_connected'] += $pct_connected;
	
	
	# duration <= $duration_level
	#
	$num_dur_lower = (int)@$CDR_DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event`=\'_COMPLETE\'
AND `reason`<>\'INCOMPAT\'
AND `calldur` IS NOT NULL
AND `calldur`<='. (int)$duration_level .'
AND '. $sql_time
	);
	echo '<td class="r"',$style_wd,'>', $num_dur_lower ,'</td>', "\n";
	$totals['num_dur_lower'] += $num_dur_lower;
	
	
	# duration > $duration_level
	#
	$num_dur_higher = (int)@$CDR_DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event`=\'_COMPLETE\'
AND `reason`<>\'INCOMPAT\'
AND `calldur` IS NOT NULL
AND `calldur`>'. (int)$duration_level .'
AND '. $sql_time
	);
	echo '<td class="r"',$style_wd,'>', $num_dur_higher ,'</td>', "\n";
	$totals['num_dur_higher'] += $num_dur_higher;
	
	
	# average duration
	#
	$avg_calldur = (int)@$CDR_DB->executeGetOne(
'SELECT AVG(`calldur`) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event`=\'_COMPLETE\'
AND `reason`<>\'INCOMPAT\'
AND `calldur` IS NOT NULL
AND '. $sql_time
	);
	echo '<td class="r"',$style_wd,'>', _secs_to_minsecs($avg_calldur) ,'</td>', "\n";
	//$totals['avg_calldur'] += $avg_calldur;
	
	
	# waittime <= $waittime_level
	#
	$num_wait_ok = (int)@$CDR_DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event` IN (\'_COMPLETE\', \'_EXIT\')
AND `waittime` IS NOT NULL
AND `waittime`<='. (int)$waittime_level .'
AND '. $sql_time
	);
	echo '<td class="r"',$style_wd,'>', $num_wait_ok ,'</td>', "\n";
	$totals['num_wait_ok'] += $num_wait_ok;
	
	
	# waittime > $waittime_level
	#
	$num_wait_fail = (int)@$CDR_DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event` IN (\'_COMPLETE\', \'_EXIT\')
AND `waittime` IS NOT NULL
AND `waittime`>'. (int)$waittime_level .'
AND '. $sql_time
	);
	echo '<td class="r"',$style_wd,'>', $num_wait_fail ,'</td>', "\n";
	$totals['num_wait_fail'] += $num_wait_fail;
	
	
	echo '</tr>', "\n";
}
--$day;


$month_t_start = (int)mkTime(  0, 0, 0, $m, 1   , $y );
$month_t_end   = (int)mkTime( 23,59,59, $m, $day, $y );
$sql_time_month = '(`timestamp`>='.$month_t_start .' AND `timestamp`<='.$month_t_end .')';

$sum_calldur_month = (int)@$CDR_DB->executeGetOne(
'SELECT SUM(`calldur`) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event`=\'_COMPLETE\'
AND `reason`<>\'INCOMPAT\'
AND `calldur` IS NOT NULL
AND '. $sql_time_month
);

$avg_calldur_month = ($totals['num_connected'] > 0)
	? $sum_calldur_month / $totals['num_connected']
	: 0;

$pct_connected_month = ($totals['num_connected'] > 0)
		? ($totals['num_connected'] / $totals['num_entered'])
		: 0.0;
$pct_connected_month = round($pct_connected_month*100);


$style = 'style="border-top:3px solid #b90; background:#feb; line-height:2.5em;"';
echo '<tr>', "\n";

echo '<td class="r" ',$style,'><b>&sum;</b></td>', "\n";

echo '<td class="r" ',$style,'>', $totals['num_entered'   ] ,'</td>', "\n";
echo '<td class="r" ',$style,'>', $totals['num_connected' ] ,'</td>', "\n";
echo '<td class="r" ',$style,'>', $totals['num_abandoned' ] ,'</td>', "\n";
echo '<td class="r" ',$style,'>', $totals['num_timeout'   ] ,'</td>', "\n";
echo '<td class="r" ',$style,'>', $totals['num_empty'     ] ,'</td>', "\n";
echo '<td class="r" ',$style,'>', $totals['num_full'      ] ,'</td>', "\n";
echo '<td class="r" ',$style,'>', $pct_connected_month ,' <small>%</small></td>', "\n";
echo '<td class="r" ',$style,'>', $totals['num_dur_lower' ] ,'</td>', "\n";
echo '<td class="r" ',$style,'>', $totals['num_dur_higher'] ,'</td>', "\n";
echo '<td class="r" ',$style,'>', _secs_to_minsecs($avg_calldur_month) ,'</td>', "\n";
echo '<td class="r" ',$style,'>', $totals['num_wait_ok'   ] ,'</td>', "\n";
echo '<td class="r" ',$style,'>', $totals['num_wait_fail' ] ,'</td>', "\n";

echo '</tr>', "\n";

?>

</tbody>
</table>


<p style="padding:0.5em 0;"><?php echo __('Gespr&auml;chsminuten'), ': &nbsp; ', round($sum_calldur_month/60); ?></p>

</div>

