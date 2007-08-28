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
	echo '<img alt="" src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";


function sec_to_hours($sec) {
	$hours = sprintf('%d:%02d:%02d',
		$sec / 3600 % 24,
		$sec / 60 % 60,
		$sec % 60
	);
	return $hours;
}

function query_string( $period, $src, $dst, $dur, $stat )
{
	global $DB;
	
	$query_line = '';
	
	switch ($period) {
	case 'month':
		$query_line = '`calldate` > (NOW()-INTERVAL 1 MONTH)';
		break;
	case 'day':
		$query_line = '`calldate` > (NOW()-INTERVAL 1 DAY)';
		break;
	case 'hour':
		$query_line = '`calldate` > (NOW()-INTERVAL 1 HOUR)';
		break;
	case 'qhour':
		$query_line = '`calldate` > (NOW()-INTERVAL 15 MINUTE)';
		break;
	case 'tmonth':
		$query_line = 'DATE_FORMAT(`calldate`,\'%Y-%m\') =  DATE_FORMAT(CURRENT_DATE(),\'%Y-%m\')';
		break;
	case 'tweek':
		$dow = (int)date('w');
		if ($dow == 0) $dow = 7; 
		$query_line = '`calldate` > (CURRENT_DATE()-INTERVAL '.$dow.' DAY)';
		break;
	case 'today':
		$query_line = 'DATE_FORMAT(`calldate`,\'%Y-%m-%d\') = CURRENT_DATE()';
		break;
	default:
		$query_line = '`calldate` > (NOW()-INTERVAL 7 DAY)';
	}
	
	$src_sql = str_replace(
		array( '*', '?' ),
		array( '%', '_' ),
		$src);
	$dst_sql = str_replace(
		array( '*', '?' ),
		array( '%', '_' ),
		$dst);
	
	if ($src != '') {
		if ($query_line != '') $query_line .= ' AND';
		$query_line .= ' `src` LIKE \''. $DB->escape($src_sql) .'\'';
	}
	
	if ($dst != '') {
		if ($query_line != '') $query_line .= ' AND';
		$query_line .= ' `dst` LIKE \''. $DB->escape($dst_sql).'\'';
	}
	$dur = _sanitize_dur( $dur );
	if ($dur != '') {
		if ($query_line != '') $query_line .= ' AND';		
		$query_line .= ' `billsec` '. $dur .'';		
	}
	if ($stat != '') {
		if ($query_line != '') $query_line .= ' AND';
		$query_line .= ' `disposition`=\''. $DB->escape(strToUpper($stat)) .'\'';
	}
	
	if ($query_line != '') $query_line .= ' AND';
	$query_line .= ' `dst`<>\'s\' AND `dst` NOT LIKE \'*8%\'';
	# do not show calls to any of the "s" or pickup extensions
	
	if ($query_line != '') $query_line = ' WHERE '.$query_line;
	return $query_line;
}


$per_page = (int)GS_GUI_NUM_RESULTS;

$src    = trim(@$_REQUEST['src'   ]);
$dst    = trim(@$_REQUEST['dst'   ]);
$dur    = trim(@$_REQUEST['dur'   ]);
$stat   = trim(@$_REQUEST['stat'  ]);
$period = trim(@$_REQUEST['period']);
$page   = (int)@$_REQUEST['page'  ] ;


function _sanitize_dur( $dur )
{
	if ($dur != '') {
		switch ($dur[0]) {
			case '<':
				if (subStr($dur,1,1) == '=')
					$dur = '<= '. (int)trim(subStr($dur,2));
				else
					$dur = '< '. (int)trim(subStr($dur,1));
				break;
			case '>':
				if (subStr($dur,1,1) == '=')
					$dur = '>= '. (int)trim(subStr($dur,2));
				else
					$dur = '> '. (int)trim(subStr($dur,1));
				break;
			case '=':
				$dur = '= '. (int)trim(subStr($dur,1));
				break;
			default:
				$dur = '> '. (int)trim($dur);
				break;
		}
	} else {
		$dur = '';
	}
	return $dur;
}


$dur = _sanitize_dur( $dur );
$query_string = query_string($period, $src, $dst, $dur, $stat);

/*
echo 'SELECT SQL_CALC_FOUND_ROWS
DATE_FORMAT(calldate,\'%d.%m.%Y %H:%i:%s\') as datum,clid,src,dst,duration,billsec,disposition 
FROM `ast_cdr` '. $query_string;
*/


$rs = $DB->execute( 'DELETE FROM `ast_cdr` WHERE `dst`=\'h\'' );

$rs = $DB->execute(
'SELECT SQL_CALC_FOUND_ROWS
	DATE_FORMAT(`calldate`, \'%d.%m.%Y %H:%i:%s\') `datum`, `clid`, `src`, `dst`, `duration`, `billsec`, `disposition` 
FROM `ast_cdr` '. $query_string .'
ORDER BY `calldate` DESC
LIMIT '. ($page*(int)$per_page) .','. (int)$per_page
);

$num_total = @$DB->numFoundRows();
$num_pages = ceil($num_total / $per_page);

$sum_talktime = (int)@$DB->executeGetOne( 'SELECT SUM(`billsec`) FROM `ast_cdr` '. $query_string);
$sum_calltime = (int)@$DB->executeGetOne( 'SELECT SUM(`duration`) FROM `ast_cdr` '. $query_string);
// $num_total_not_null = (int) $DB->executeGetOne( 'SELECT COUNT(*) FROM `ast_cdr` WHERE `billsec` > 0');


$mod_url = gs_url($SECTION, $MODULE)
	.'&amp;src='   . rawUrlEncode($src)
	.'&amp;dst='   . rawUrlEncode($dst)
	.'&amp;dur='   . rawUrlEncode($dur)
	.'&amp;stat='  . rawUrlEncode($stat)
	.'&amp;period='. rawUrlEncode($period);


?>

<form method="get" action="<?php echo GS_URL_PATH; ?>">	
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:140px;"><?php echo __('Zeitraum'); ?></th>
	<th style="width:140px;"><?php echo __('Anrufer'); ?></th>
	<th style="width:140px;"><?php echo __('Ziel'); ?></th>
	<th style="width:100px;"><?php echo __('Anrufdauer'); ?></th>
	<th style="width:140px;" class="r">
		<?php echo __('Seite'), ' ', ($page+1), ' / ', $num_pages; ?></th>
	<th style="width:50px;">
<?php

if ($page > 0) {
	echo
	'<a href="', $mod_url, '&amp;page=', ($page-1), '" title="', __('zur&uuml;ckbl&auml;ttern'), '" id="arr-prev">',
	'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous.png" />',
	'</a>', "\n";
} else {
	echo
	'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous_notavail.png" />', "\n";
}

if ($page < $num_pages-1) {
	echo
	'<a href="', $mod_url, '&amp;page=', ($page+1), '" title="', __('weiterbl&auml;ttern'), '" id="arr-next">',
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
<tr>
	<td>
		<select name="period" onchange="this.form.submit();"> 
		<option value ="all" <?php echo ($period=='all') ? 'selected="selected">' : '>'; ?><?php echo __('alle'); ?></option>
		<option value ="month" <?php echo ($period=='month') ? 'selected="selected">' : '>'; ?><?php echo __('ein Monat'); ?></option>
		<option value ="tmonth" <?php echo ($period=='tmonth') ? 'selected="selected">' : '>'; ?><?php echo __('diesen Monat'); ?></option>
		<option value ="" <?php echo ($period=='') ? 'selected="selected">' : '>'; ?><?php echo __('eine Woche'); ?></option>
		<option value ="tweek" <?php echo ($period=='tweek') ? 'selected="selected">' : '>'; ?><?php echo __('diese Woche'); ?></option>
		<option value ="day" <?php echo ($period=='day') ? 'selected="selected">' : '>'; ?><?php echo __('ein Tag'); ?></option>
		<option value ="today" <?php echo ($period=='today') ? 'selected="selected">' : '>'; ?><?php echo __('heute'); ?></option>
		<option value ="hour" <?php echo ($period=='hour') ? 'selected="selected">' : '>'; ?><?php echo __('eine Stunde'); ?></option>
		<option value ="qhour" <?php echo ($period=='qhour') ? 'selected="selected">' : '>'; ?><?php echo __('15 Minuten'); ?></option>
		</select>
	</td>
	<td>
		<input type="text" name="src" value="<?php echo htmlEnt($src); ?>" size="25" style="width:130px;" />	
	</td>
	<td>
		<input type="text" name="dst" value="<?php echo htmlEnt($dst); ?>" size="15" style="width:130px;" />
	</td>
	<td>
		<input type="text" name="dur" value="<?php echo htmlEnt($dur); ?>" size="8" style="width:60px;" />&nbsp;s
	</td>
	<td>
		<select name="stat" style="width:135px;" onchange="this.form.submit();">
		<option value="" <?php echo ($stat=='') ? 'selected="selected">' : '>'; ?><?php echo __('alle Verb.'); ?></option>
		<option value="answered" <?php echo ($stat=='answered') ? 'selected="selected">' : '>'; ?><?php echo __('beantwortet'); ?></option>
		<option value="no answer" <?php echo ($stat=='no answer') ? 'selected="selected">' : '>'; ?><?php echo __('keine Antwort'); ?></option>
		<option value="busy" <?php echo ($stat=='busy') ? 'selected="selected">' : '>'; ?><?php echo __('besetzt'); ?></option>
		<option value="failed" <?php echo ($stat=='failed') ? 'selected="selected">' : '>'; ?><?php echo __('fehlgeschlagen'); ?></option>
		</select>
	</td>
	<td>
		<button type="submit" title="<?php echo __('Suchen'); ?>" class="plain">
			<img alt="<?php echo __('Suchen'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/search.png" />
		</button>
		<?php echo '<a href="'.gs_url($SECTION, $MODULE).'&page='.$page.'"><img alt="', __('Abbrechen'), '" src="', GS_URL_PATH,'crystal-svg/16/act/cancel.png" /></a>'; ?>
	</td>
</tr>

</tbody>
</table>
</form>


<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:140px;"><?php echo __('Zeit'); ?></th>
	<th style="width:140px;"><?php echo __('Anrufer'); ?></th>
	<th style="width:140px;"><?php echo __('Ziel'); ?></th>
	<th style="width:100px;"><?php echo __('Anrufdauer'); ?></th>
	<th style="width:140px;"><?php echo __('Status'); ?></th>
</tr>
</thead>
<tbody>

<?php

$sudo_url = (@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
	? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);

if (@$rs) {
	$i = 0;
	while ($r = $rs->fetchRow()) {
		echo '<tr class="', ((++$i % 2 == 0) ? 'even':'odd'), '">';
		echo '<td>', htmlEnt($r['datum']),'</td>';
		echo '<td>', htmlEnt($r['src']),'</td>';		
		echo '<td>', htmlEnt($r['dst']), '</td>';
		echo '<td>', sec_to_hours($r['billsec']), '</td>';
		
		echo '<td>';
		
		switch ($r['disposition']) {
		case 'ANSWERED':
			echo '<img src="', GS_URL_PATH, 'crystal-svg/16/act/greenled.png" /> ', __('beantwortet');			
			break;
		case 'NO ANSWER':
			echo '<img src="', GS_URL_PATH, 'crystal-svg/16/act/free_icon.png" /> ', __('keine Antwort');			
			break;
		case 'FAILED':
			echo '<img src="', GS_URL_PATH, 'crystal-svg/16/act/redled.png" /> ', __('fehlgeschlagen');			
			break;
		case 'BUSY':
			echo '<img src="', GS_URL_PATH, 'crystal-svg/16/act/yellowled.png" /> ', __('besetzt');			
			break;
		default:
			echo __('unbekannt');
		}		
		
		echo '</td>';
		echo '</tr>', "\n";
	}
}

?>

</tbody>
</table>


<br />
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:260px;">
		<span class="sort-col"><?php echo __('Verbindungsstatistik'); ?></span>
	</th>
	<th style="width:140px;">
		&nbsp;
	</th>
</tr>
</thead>
<tbody>
<tr>
	<th><?php echo __('Anrufe'); ?>:</th>
	<td><?php echo $num_total; ?></td>
</tr>
<tr>
	<th><?php echo __('Anrufdauer insges.'); ?>:</th>
	<td><?php echo sec_to_hours( $sum_talktime ); ?></td>
</tr>
<tr>
	<th><?php echo __('Anrufdauer im Durchschnitt'); ?>:</th>
	<td><?php

$where = trim( $query_string );
if (strToUpper(subStr($where,0,5)) == 'WHERE')
	$where = $where.' AND `billsec` > 0';
else
	$where = 'WHERE `billsec` > 0';

$num_total_with_talktime = $DB->executeGetOne( 'SELECT COUNT(*) FROM `ast_cdr` '. $where );

if ($num_total_with_talktime > 0)
	echo sec_to_hours( $sum_talktime / $num_total_with_talktime );
//else echo '<i>', __('keine Anrufe'), '</i>';
else echo '<i>', sec_to_hours( 0 ), '</i>';

?>
	</td>
</tr>
<tr>
	<th><?php echo __('Verbindungszeit'); ?>:</th>
	<td><?php echo sec_to_hours( $sum_calltime ); ?></td>
</tr>
</tbody>
</table>
