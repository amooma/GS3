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

<label for="ipt-queue_id"><?php echo __('$$$ Warteschlange'); ?>:</label>
<select name="queue_id" id="ipt-queue_id">
<?php
$rs = $DB->execute( 'SELECT `_id`, `name`, `_title` FROM `ast_queues` ORDER BY `name`' );
while ($r = $rs->fetchrow()) {
	echo '<option value="',$r['_id'],'"', ($r['_id']==$queue_id ? ' selected="selected"' : ''),'>', $r['name'] ,' (', htmlEnt($r['_title']) ,')' ,'</option>' ,"\n";
}
?>
</select>

&nbsp;&nbsp;&nbsp;

<label for="ipt-month"><?php echo __('$$$ Monat'); ?>:</label>
<select name="month" id="ipt-month">
<?php
$t = time();
for ($i=-3; $i<=0; ++$i) {
	echo '<option value="',$i,'"', ($i==$month_d ? ' selected="selected"' : ''),'>', date('m / Y', (int)strToTime("$i months", $t)) ,'</option>' ,"\n";
}
?>
</select>

&nbsp;&nbsp;&nbsp;

<input type="submit" value="<?php echo __('$$$ Report'); ?>" />
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

<div style="position:absolute; left:189px; right:19px; top:14em; bottom:14px; overflow:scroll; border:1px solid #ccc; background:#fff;">

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="font-weight:normal;"><?php echo __('$$$ Tag'); ?></th>
	<th style="font-weight:normal;"><?php echo __('Anrufe'); ?></th>
	<th style="font-weight:normal;"><?php echo __('Angen.'); ?></th>
	<th style="font-weight:normal;"><?php echo __('Aufgelegt'); ?></th>
	<th style="font-weight:normal;"><?php echo __('Timeout'); ?></th>
	<th style="font-weight:normal;"><?php echo __('keine Ag.'); ?></th>
	<th style="font-weight:normal;"><?php echo __('Erfolg'); ?></th>
	<th style="font-weight:normal;"><?php echo '&le;', _secs_to_minsecs($service_level); ?></th>
	<th style="font-weight:normal;"><?php echo '&gt;', _secs_to_minsecs($service_level); ?></th>
	<th style="font-weight:normal;"><?php echo '&empty; Gespr.'; ?></th>
	<th style="font-weight:normal;"><?php echo __('tW') ,' &le;', _secs_to_minsecs($waittime_level); ?></th>
	<th style="font-weight:normal;"><?php echo __('tW') ,' &gt;', _secs_to_minsecs($waittime_level); ?></th>
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

















<?php return; ?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th><?php echo __('$$$ Aktiv'); ?></th>
	<th><?php echo __('$$$ Muster'); ?><sup>[1]</sup></th>
	<th><?php echo __('$$$ Wochentage'); ?></th>
	<th><?php echo __('$$$ Uhrzeit'); ?></th>
	<th><?php echo __('$$$ Gateway / Fallback'); ?></th>
	<th><?php echo __('$$$ Reihenfolge'); ?></th>
</tr>
</thead>
<tbody>

<?php
$rs = $DB->execute(
'SELECT `id`, `title`, `type`
FROM `gate_grps`
ORDER BY `title`'
);
$gate_grps = array();
while ($r = $rs->fetchRow()) {
	$gate_grps[(int)$r['id']] = array(
		'title'      => $r['title'],
		'title_html' => htmlEnt($r['title']),
		'type'       => $r['type']
	);
}

$rs = $DB->execute(
'SELECT
	`id`, `active`, `pattern`,
	`d_mo`, `d_tu`, `d_we`, `d_th`, `d_fr`, `d_sa`, `d_su`, `h_from`, `h_to`,
	`gw_grp_id_1` `gg1`, `gw_grp_id_2` `gg2`, `gw_grp_id_3` `gg3`, `descr`
FROM `routes`
ORDER BY `ord`'
);
$i=0;
while ($route = $rs->fetchRow()) {
	$id = $route['id'];
	echo '<tr class="', (($i%2) ? 'even':'odd'), '">', "\n";
	
	echo '<td>';
	echo '<input type="checkbox" name="r_',$id,'_active" value="1" ', ($route['active'] ? 'checked="checked" ' : ''), '/>';
	echo '</td>', "\n";
	
	echo '<td>';
	echo '<input type="text" name="r_',$id,'_pattern" value="', htmlEnt($route['pattern']), '" size="17" maxlength="30" class="pre" style="font-weight:bold;" />';
	echo '</td>', "\n";
	
	echo '<td>';
	foreach ($wdaysl as $col => $v) {
		echo '<nobr><input type="checkbox" name="r_',$id,'_d_',$col,'" id="ipt-r_',$id,'_d_',$col,'" value="1" ', ($route['d_'.$col] ? 'checked="checked" ' : ''), '/>';
		echo '<label for="ipt-r_',$id,'_d_',$col,'">', $v, '</label></nobr>';
	}
	echo '</td>', "\n";
	
	echo '<td>';
	$tmp = explode(':', $route['h_from']);
	$hf = (int)lTrim(@$tmp[0], '0-');
	if     ($hf <  0) $hf =  0;
	elseif ($hf > 23) $hf = 23;
	$hf = str_pad($hf, 2, '0', STR_PAD_LEFT);
	$mf = (int)lTrim(@$tmp[1], '0-');
	if     ($mf <  0) $mf =  0;
	elseif ($mf > 59) $mf = 59;
	$mf = str_pad($mf, 2, '0', STR_PAD_LEFT);
	echo '<nobr><input type="text" name="r_',$id,'_h_from_h" value="', $hf, '" size="2" maxlength="2" />';
	echo ':<input type="text" name="r_',$id,'_h_from_m" value="', $mf, '" size="2" maxlength="2" /></nobr>';
	$tmp = explode(':', $route['h_to']);
	$ht = (int)lTrim(@$tmp[0], '0-');
	if     ($ht <  0) $ht =  0;
	elseif ($ht > 24) $ht = 24;
	$ht = str_pad($ht, 2, '0', STR_PAD_LEFT);
	$mt = (int)lTrim(@$tmp[1], '0-');
	if     ($mt <  0) $mt =  0;
	elseif ($mt > 59) $mt = 59;
	$mt = str_pad($mt, 2, '0', STR_PAD_LEFT);
	if ($ht.':'.$mt < $hf.':'.$mf) {
		$ht = $hf;
		$hm = $mf;
	}
	echo ' - <nobr><input type="text" name="r_',$id,'_h_to_h" value="', $ht, '" size="2" maxlength="2" />';
	echo ':<input type="text" name="r_',$id,'_h_to_m" value="', $mt, '" size="2" maxlength="2" /></nobr>';
	echo '</td>', "\n";
	
	echo '<td rowspan="2">';
	$gw_grp_idxs = array(1,2,3);
	foreach ($gw_grp_idxs as $gw_grp_idx) {
		echo '<select name="r_',$id,'_ggrpid',$gw_grp_idx,'">', "\n";
		$route_ggrp_id = $route['gg'.$gw_grp_idx];
		echo '<option value=""', ($route_ggrp_id == 0 || ! array_key_exists($route_ggrp_id, $gate_grps) ? ' selected="selected"' : ''), '>', '-', '</option>', "\n";
		foreach ($gate_grps as $ggid => $gg) {
			echo '<option value="', $ggid, '"', ($ggid == $route_ggrp_id ? ' selected="selected"' : ''), '>', $gg['title_html'], '</option>', "\n";
		}
		echo '</select><br />', "\n";
	}
	echo '</td>', "\n";
	
	echo '<td rowspan="2" class="r transp">';
	if ($i > 0)
		echo '<a href="', gs_url($SECTION, $MODULE), '&amp;action=move-up&amp;id=', $route['id'], '"><img alt="&uarr;" src="', GS_URL_PATH, 'img/move_up.gif" /></a>';
	else
		echo '<img alt="&uarr;" src="', GS_URL_PATH, 'img/move_up_d.gif" />';
	if ($i < $rs->numRows()-1)
		echo ' <a href="', gs_url($SECTION, $MODULE), '&amp;action=move-down&amp;id=', $route['id'], '"><img alt="&darr;" src="', GS_URL_PATH, 'img/move_down.gif" /></a>';
	else
		echo ' <img alt="&darr;" src="', GS_URL_PATH, 'img/move_down_d.gif" />';
	echo ' &nbsp; <a href="', gs_url($SECTION, $MODULE), '&amp;action=del&amp;id=', $route['id'], '"><img alt="-;" src="', GS_URL_PATH, 'img/minus.gif" /></a>';
	echo '</td>', "\n";
	
	echo '</tr>', "\n";
	
	echo '<tr class="', (($i%2) ? 'even':'odd'), '">', "\n";
	echo '<td colspan="2" class="r"><label for="ipt-r_',$id,'_descr">', __('$$$ Beschr.:'), '</label></td>';
	
	echo '<td colspan="2">';
	echo '<input type="text" name="r_',$id,'_descr" id="ipt-r_',$id,'_descr" value="', htmlEnt(trim($route['descr'])), '" size="45" maxlength="60" style="width:97%; font-weight:bold;" />';
	echo '</td>', "\n";
	
	echo '</tr>', "\n";
	++$i;
}


echo '<tr>', "\n";
echo '<td colspan="5" class="transp">&nbsp;</td>', "\n";
echo '<td class="r transp">';
echo '<input type="submit" value="', __('$$$ Speichern'), '" />';
echo '</td>', "\n";
echo '</tr>', "\n";


$id = 0;
echo '<tr class="', ((++$i % 2 == 0) ? 'even':'odd'), '">', "\n";

echo '<td>';
echo '<input type="checkbox" name="r_',$id,'_active" value="1" checked="checked" />';
echo '</td>', "\n";

echo '<td>';
echo '<input type="text" name="r_',$id,'_pattern" value="" size="17" maxlength="30" class="pre" style="font-weight:bold;" />';
echo '</td>', "\n";

echo '<td>';
foreach ($wdaysl as $col => $v) {
	echo '<nobr><input type="checkbox" name="r_',$id,'_d_',$col,'" id="ipt-r_',$id,'_d_',$col,'" value="1" checked="checked" />';
	echo '<label for="ipt-r_',$id,'_d_',$col,'">', $v, '</label></nobr>';
}
echo '</td>', "\n";

echo '<td>';
echo '<nobr><input type="text" name="r_',$id,'_h_from_h" value="00" size="2" maxlength="2" />';
echo ':<input type="text" name="r_',$id,'_h_from_m" value="00" size="2" maxlength="2" /></nobr>';
echo ' - <nobr><input type="text" name="r_',$id,'_h_to_h" value="24" size="2" maxlength="2" />';
echo ':<input type="text" name="r_',$id,'_h_to_m" value="00" size="2" maxlength="2" /></nobr>';
echo '</td>', "\n";

echo '<td rowspan="2">';
foreach ($gw_grp_idxs as $gw_grp_idx) {
	echo '<select name="r_',$id,'_ggrpid',$gw_grp_idx,'">', "\n";
	$route_ggrp_id = $route['gg'.$gw_grp_idx];
	echo '<option value="" selected="selected">-</option>', "\n";
	foreach ($gate_grps as $ggid => $gg) {
		echo '<option value="', $ggid, '">', $gg['title_html'], '</option>', "\n";
	}
	echo '</select><br />', "\n";
}
echo '</td>', "\n";

echo '<td rowspan="2" class="r transp">&nbsp;';
echo '</td>', "\n";

echo '</tr>', "\n";

echo '<tr class="', (($i % 2 == 0) ? 'even':'odd'), '">', "\n";
echo '<td colspan="2" class="r"><label for="ipt-r_',$id,'_descr">', __('$$$ Beschr.:'), '</label></td>';

echo '<td colspan="2">';
echo '<input type="text" name="r_',$id,'_descr" id="ipt-r_',$id,'_descr" value="', htmlEnt(trim($route['descr'])), '" size="45" maxlength="60" style="width:97%;font-weight:bold;" />';
echo '</td>', "\n";

echo '</tr>', "\n";

?>

</tbody>
</table>

<br />
<p class="text"><small><sup>[1]</sup> <?php
/*
echo __('$$$ PCRE-Syntax ohne <code>/</code> als Begrenzer, d.h. <code>^</code> f&uuml;r den Anfang, <code>$</code> f&uuml;r das Ende, z.B. <code>[5-8]</code> oder <code>[57]</code> f&uuml;r Ziffern-Bereiche, <code>+</code> f&uuml;r eine Wiederholung des vorangehenden Zeichens (1 oder mehr) oder <code>*</code> f&uuml;r 0 oder mehr. Zus&auml;tzlich m&ouml;glich: <code>x</code> f&uuml;r <code>[0-9]</code>, <code>z</code> f&uuml;r <code>[1-9]</code>');
*/
echo __('$$$ PCRE-Syntax (&quot;Perl Compatible Regular Expression&quot;) ohne <code>/</code> als Begrenzer, d.h. <code>^</code> f&uuml;r den Anfang, <code>$</code> f&uuml;r das Ende, z.B. <code>[5-8]</code> oder <code>[57]</code> f&uuml;r Ziffern-Bereiche, <code>+</code> f&uuml;r eine Wiederholung des vorangehenden Zeichens (1 oder mehr) oder <code>*</code> f&uuml;r 0 oder mehr.');
?></small></p>
