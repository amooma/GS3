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

function _mac_addr_display( $mac )
{
	return preg_replace('/\\:$/S','', preg_replace('/.{2}/S','$0:', strToUpper($mac)));
}

$per_page = (int)GS_GUI_NUM_RESULTS;

$page = (int)@$_REQUEST['page'];

$search_url = '';


#####################################################################
#  view {
#####################################################################
//if ($action === 'view') {
	
	//echo "<pre>"; print_r($_REQUEST); echo "</pre>";
	$where = array();
	
	$query =
		'SELECT SQL_CALC_FOUND_ROWS '.
			'`j`.`id`, `j`.`inserted`, `j`.`running`, `j`.`trigger`, '.
			'`p`.`mac_addr`, `j`.`type`, `j`.`immediate`, '.
			'`j`.`minute`, `j`.`hour`, `j`.`day`, `j`.`month`, `j`.`dow`, '.
			'`j`.`data` '.
		'FROM '.
			'`prov_jobs` `j` LEFT JOIN '.
			'`phones` `p` ON (`p`.`id`=`j`.`phone_id`) '.
		(count($where)===0 ? '' : 'WHERE '.implode(' AND ',$where) ) .' '.
		'ORDER BY `j`.`inserted` '.
		'LIMIT '. ($page*$per_page) .','. $per_page
		;
	$rs_jobs = $DB->execute($query);
	$num_total = @$DB->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	
?>

<table cellspacing="1">
<tbody>
<tr>
	<th style="width:100px;"><?php echo __('Seite'), ' ', ($page+1), ' / ', $num_pages; ?></th>
</tr>
<tr>
	<td rowspan="2">
<?php
	if ($page > 0) {
		echo
		'<a href="', gs_url($SECTION, $MODULE, null, $search_url .'&amp;page='.($page-1)), '" title="', __('zur&uuml;ckbl&auml;ttern'), '" id="arr-prev">',
		'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/back-cust.png" />',
		'</a>', "\n";
	} else {
		echo
		'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/back-cust-dis.png" />', "\n";
	}
	if ($page < $num_pages-1) {
		echo
		'<a href="', gs_url($SECTION, $MODULE, null, $search_url .'&amp;page='.($page+1)), '" title="', __('weiterbl&auml;ttern'), '" id="arr-next">',
		'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/forward-cust.png" />',
		'</a>', "\n";
	} else {
		echo
		'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/forward-cust-dis.png" />', "\n";
	}
?>
	</td>
</tr>
</tbody>
</table>

<br />
<table cellspacing="1">
<thead>
<tr>
	<th style="min-width:10em;"  rowspan="2"><?php echo __('Erzeugt'    ); ?></th>
	<th style="min-width:0.5em;" rowspan="2"><?php echo    'r'           ; ?></th>
	<th style="min-width:0.5em;" rowspan="2"><?php echo    'i'           ; ?></th>
	<th style="min-width: 4em;"  rowspan="2"><?php echo __('Initiator'); ?></th>
	<th style="min-width:11em;"  rowspan="2"><?php echo __('Telefon'    ); ?></th>
	<th style="min-width: 5em;"  rowspan="2"><?php echo __('Art'        ); ?></th>
	<th style="min-width: 5em;"  rowspan="2"><?php echo __('Daten'   ); ?></th>
	<th colspan="5" class="c"><?php echo __('Cron-Regel'); ?></th>
</tr>
<tr>
	<th class="c" style="min-width: 1em;"><?php echo __('Min.'); ?></th>
	<th class="c" style="min-width: 1em;"><?php echo __('Std.'); ?></th>
	<th class="c" style="min-width: 1em;"><?php echo __('Tag' ); ?></th>
	<th class="c" style="min-width: 1em;"><?php echo __('Mon.'); ?></th>
	<th class="c" style="min-width: 1em;"><?php echo __('WT'  ); ?></th>
</tr>
</thead>
<tbody>
<?php

$i=0;
while ($job = $rs_jobs->fetchRow()) {
	echo '<tr class="',($i%2===0?'odd':'even'),'">' ,"\n";
	
	echo '<td>', htmlEnt(date('Y-m-d H:i:s', $job['inserted'])) ,'</td>' ,"\n";
	echo '<td>', ($job['running'] ? 'r':'') ,'</td>' ,"\n";
	echo '<td>', ($job['immediate'] ? 'i':'') ,'</td>' ,"\n";
	echo '<td>', htmlEnt($job['trigger']) ,'</td>' ,"\n";
	echo '<td><tt>', htmlEnt(_mac_addr_display($job['mac_addr'])) ,'</tt></td>' ,"\n";
	echo '<td>', htmlEnt($job['type']) ,'</td>' ,"\n";
	echo '<td>', htmlEnt($job['data']) ,'</td>' ,"\n";
	
	echo '<td class="c">', htmlEnt($job['minute']) ,'</td>' ,"\n";
	echo '<td class="c">', htmlEnt($job['hour'  ]) ,'</td>' ,"\n";
	echo '<td class="c">', htmlEnt($job['day'   ]) ,'</td>' ,"\n";
	echo '<td class="c">', htmlEnt($job['month' ]) ,'</td>' ,"\n";
	echo '<td class="c">', htmlEnt($job['dow'   ]) ,'</td>' ,"\n";
	
	echo '</tr>' ,"\n";
	++$i;
}

?>
</tbody>
</table>


<?php
//}
#####################################################################
#  view }
#####################################################################
