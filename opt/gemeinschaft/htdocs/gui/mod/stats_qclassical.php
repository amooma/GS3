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
include_once( GS_DIR .'inc/pcre_check.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt="" src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


$service_level  = 90;  # 90 s = 1:30 min
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
	$month_d  = -1;  # previous month
}


?>


<form method="get" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="report" />

<label for="ipt-queue_id"><?php echo __('Warteschlange'); ?>:</label>
<select name="queue_id" id="ipt-queue_id">
<?php
$rs = $DB->execute( 'SELECT `_id`, `name`, `_title` FROM `ast_queues` ORDER BY `name`' );
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
$num_days  = (int)date('W', $t);
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
			chart.style.top = '';
			chart.style.left = '';
			chart.style.right = '';
			chart.style.bottom = '';
			chart.style.background = 'transparent';
			toggle.src = '<?php echo GS_URL_PATH; ?>crystal-svg/16/act/window_fullscreen.png';
		} else {
			chart.style.position = 'absolute';
			chart.style.top = '0';
			chart.style.left = '0';
			chart.style.right = '0';
			chart.style.bottom = '0';
			chart.style.background = '#fff';
			toggle.src = '<?php echo GS_URL_PATH; ?>crystal-svg/16/act/window_nofullscreen.png';
		}
	}
}
</script>



<div id="chart" style="">
<img id="chart-fullscreen-toggle" class="fr" style="cursor:pointer; margin:4px 7px 0 0;" title="Fullscreen" alt="Fullscreen" onclick="chart_fullscreen_toggle();" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/window_fullscreen.png" />
<br style="clear:right;" />

<table cellspacing="1" class="phonebook" style="border:1px solid #ccc; background:#fff;">
<thead>
<tr>
	<th style="font-weight:normal;"><?php echo __('Tag'); ?></th>
	<th style="font-weight:normal;"><?php echo __('Anrufe'); ?></th>
	<th style="font-weight:normal;"><?php echo __('Angenommen'); ?></th>
	<th style="font-weight:normal;"><?php echo __('Aufgelegt'); ?></th>
	<th style="font-weight:normal;"><?php echo __('Timeout'); ?></th>
	<th style="font-weight:normal;"><?php echo __('keine Ag.'); ?></th>
	<th style="font-weight:normal;"><?php echo __('Erfolgsquote'); ?></th>
	<th style="font-weight:normal;"><?php echo __('Dauer'), ' &le;', _secs_to_minsecs($service_level); ?></th>
	<th style="font-weight:normal;"><?php echo __('Dauer'), ' &gt;', _secs_to_minsecs($service_level); ?></th>
	<th style="font-weight:normal;"><?php echo '&empty; ', __('Dauer'); ?></th>
	<th style="font-weight:normal;"><?php echo __('Wartez.') ,' &le;', _secs_to_minsecs($waittime_level); ?></th>
	<th style="font-weight:normal;"><?php echo __('Wartez.') ,' &gt;', _secs_to_minsecs($waittime_level); ?></th>
</tr>
</thead>
<tbody>

<?php

for ($day=1; $day<$num_days; ++$day) {
	
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
	echo '<td class="r"',$style_wd,'>', $day ,'.</td>', "\n";
	
	
	# inbound calls
	#
	$num_entered = (int)@$DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event`=\'_ENTER\'
AND '. $sql_time
	);
	echo '<td class="r"',$style_wd,'>', $num_entered ,'</td>', "\n";
	
	
	# connected to an agent
	#
	$num_connected = (int)@$DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event`=\'_CONNECT\'
AND '. $sql_time
	);
	echo '<td class="r"',$style_wd,'>', $num_connected ,'</td>', "\n";
	
	
	# abandoned
	#
	$num_abandoned = (int)@$DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event`=\'_EXIT\'
AND `reason`=\'ABANDON\'
AND '. $sql_time
	);
	echo '<td class="r"',$style_wd,'>', $num_abandoned ,'</td>', "\n";
	
	
	# timeout
	#
	$num_timeout = (int)@$DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event`=\'_EXIT\'
AND `reason`=\'TIMEOUT\'
AND '. $sql_time
	);
	echo '<td class="r"',$style_wd,'>', $num_timeout ,'</td>', "\n";
	
	
	# no queue members
	#
	$num_empty = (int)@$DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event`=\'_EXIT\'
AND `reason`=\'EMPTY\'
AND '. $sql_time
	);
	echo '<td class="r"',$style_wd,'>', $num_empty ,'</td>', "\n";
	
	
	# % connected
	#
	$pct = ($num_connected > 0)
		? round(($num_connected / $num_entered) *100)
		: 0.0;
	echo '<td class="r"',$style_wd,'>', $pct ,'<small>&thinsp;%</small></td>', "\n";
	
	
	# duration <= $service_level
	#
	$num_sl_ok = (int)@$DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event`=\'_COMPLETE\'
AND `reason`<>\'INCOMPAT\'
AND `calldur` IS NOT NULL
AND `calldur`<='. (int)$service_level .'
AND '. $sql_time
	);
	echo '<td class="r"',$style_wd,'>', $num_sl_ok ,'</td>', "\n";
	
	
	# duration > $service_level
	#
	$num_sl_fail = (int)@$DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event`=\'_COMPLETE\'
AND `reason`<>\'INCOMPAT\'
AND `calldur` IS NOT NULL
AND `calldur`>'. (int)$service_level .'
AND '. $sql_time
	);
	echo '<td class="r"',$style_wd,'>', $num_sl_fail ,'</td>', "\n";
	
	
	# average duration
	#
	$avg_calldur = (int)@$DB->executeGetOne(
'SELECT AVG(`calldur`) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event`=\'_COMPLETE\'
AND `reason`<>\'INCOMPAT\'
AND `calldur` IS NOT NULL
AND '. $sql_time
	);
	echo '<td class="r"',$style_wd,'>', _secs_to_minsecs($avg_calldur) ,'</td>', "\n";
	
	
	# duration <= $waittime_level
	#
	$num_wait_ok = (int)@$DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event`=\'_COMPLETE\'
AND `reason`<>\'INCOMPAT\'
AND `waittime` IS NOT NULL
AND `waittime`<='. (int)$waittime_level .'
AND '. $sql_time
	);
	echo '<td class="r"',$style_wd,'>', $num_wait_ok ,'</td>', "\n";
	
	
	# duration > $waittime_level
	#
	$num_wait_fail = (int)@$DB->executeGetOne(
'SELECT COUNT(*) FROM `queue_log` WHERE
    `queue_id`='. $queue_id .'
AND `event`=\'_COMPLETE\'
AND `reason`<>\'INCOMPAT\'
AND `waittime` IS NOT NULL
AND `waittime`>'. (int)$waittime_level .'
AND '. $sql_time
	);
	echo '<td class="r"',$style_wd,'>', $num_wait_fail ,'</td>', "\n";
	
	
	
	
	
	
	
	echo '</tr>', "\n";
}

?>

</tbody>
</table>

</div>

