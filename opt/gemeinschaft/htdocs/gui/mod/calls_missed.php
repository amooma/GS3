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
require_once( GS_DIR .'inc/util.php' );


echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";


$per_page = (int)GS_GUI_NUM_RESULTS;

$page = (int)@$_REQUEST['page'];



$rs = $DB->execute(
'SELECT SQL_CALC_FOUND_ROWS
	MAX(`d`.`timestamp`) `ts`, `d`.`number`, `d`.`remote_name`,
	`u`.`id` `r_uid`, `u`.`lastname` `r_ln`, `u`.`firstname` `r_fn`
FROM
	`dial_log` `d` LEFT JOIN
	`users` `u` ON (`u`.`id`=`d`.`remote_user_id`)
WHERE
	`d`.`user_id`='. (int)@$_SESSION['sudo_user']['info']['id'] .' AND
	`d`.`type`=\'missed\' AND
	`d`.`timestamp`>'. (time()-GS_PROV_DIAL_LOG_LIFE) .' AND
	`d`.`number` <> \''. $DB->escape( @$_SESSION['sudo_user']['info']['ext'] ) .'\'
GROUP BY `d`.`number`
ORDER BY `ts` DESC
LIMIT '. ($page*(int)$per_page) .','. (int)$per_page
);
$num_total = @$DB->numFoundRows();
$num_pages = ceil($num_total / $per_page);


?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:140px;"><?php echo __('Nummer'); ?></th>
	<th style="width:210px;"><?php echo __('Name'); ?></th>
	<th style="width:120px;"><span class="sort-col"><?php echo __('Datum'); ?></span></th>
	<th style="width:100px;"><?php echo __('Seite'), ' ', ($page+1), ' / ', $num_pages; ?></th>
</tr>
</thead>
<tbody>
<tr class="even">
	<td colspan="3">&nbsp;</td>
	<td>
<?php

if ($page > 0) {
	echo
	'<a href="', gs_url($SECTION, $MODULE), '&amp;page=', ($page-1), '" title="', __('zur&uuml;ckbl&auml;ttern'), '" id="arr-prev">',
	'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/back-cust.png" />',
	'</a>', "\n";
} else {
	echo
	'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/back-cust-dis.png" />', "\n";
}
if ($page < $num_pages-1) {
	echo
	'<a href="', gs_url($SECTION, $MODULE), '&amp;page=', ($page+1), '" title="', __('weiterbl&auml;ttern'), '" id="arr-next">',
	'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/forward-cust.png" />',
	'</a>', "\n";
} else {
	echo
	'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/forward-cust-dis.png" />', "\n";
}

?>
	</td>
</tr>

<?php

if (@$rs) {
	$i = 0;
	while ($r = $rs->fetchRow()) {
		echo '<tr class="'. ((++$i % 2 == 0) ? 'even':'odd') .'">';
		echo '<td>', htmlEnt($r['number']), '</td>';
		
		if (! $r['r_uid'])
			$name = $r['remote_name'];
		else {
			$name = '';
			if ($r['r_fn'] != '') $name .= $r['r_fn'] .', ';
			$name .= $r['r_ln'];
		}
		echo '<td>', htmlEnt($name), '</td>';
		
		echo '<td>', date_human($r['ts']), '</td>';
		
		$sudo_url = (@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
			? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);
		echo '<td>';
		if ($r['number'] != $_SESSION['sudo_user']['info']['ext'])
			echo '<a href="', GS_URL_PATH, 'pb-dial.php?n=', htmlEnt($r['number']), $sudo_url, '" title="', __('w&auml;hlen'), '"><img alt="', __('w&auml;hlen'), '" src="', GS_URL_PATH, 'crystal-svg/16/app/yast_PhoneTTOffhook.png" /></a>';
		else echo '&nbsp;';
		echo '</td>';
		echo '</tr>', "\n";
	}
}

?>

</tbody>
</table>

