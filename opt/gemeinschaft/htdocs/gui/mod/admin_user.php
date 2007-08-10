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
require_once( GS_DIR .'inc/extension-state.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt="" src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


function count_users_configured( $DB ) {
	$num = (int)$DB->executeGetOne( 'SELECT COUNT(*) FROM `users` WHERE `nobody_index` IS NULL');
	return $num;
}
function count_users_logged_in( $DB ) {
	$num = (int)$DB->executeGetOne( 'SELECT COUNT(*) FROM `phones` `p` LEFT JOIN `users` `u` ON (`u`.`id`=`p`.`user_id`) WHERE `u`.`nobody_index` IS NULL');
	return $num;
}



echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";


$per_page = (int)GS_GUI_NUM_RESULTS;

$name = trim(@$_REQUEST['name']);
$number = trim(@$_REQUEST['number']);
$page = (int)@$_REQUEST['page'];


if ($number != '') {
	
	# search by number
	
	$search_url = '&amp;number='. urlEncode($number);
	
	$number_sql = str_replace(
		array( '*', '?' ),
		array( '%', '_' ),
		$number
	) .'%';
	$rs = $DB->execute(
'SELECT SQL_CALC_FOUND_ROWS
	`u`.`firstname` `fn`, `u`.`lastname` `ln`, `u`.`host_id` `hid`, `u`.`honorific` `hnr`, `u`.`user` `usern`, `s`.`name` `ext`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE
	`u`.`nobody_index` IS NULL AND (
	`s`.`name` LIKE \''. $DB->escape($number_sql) .'\'
	)
ORDER BY `s`.`name`
LIMIT '. ($page*(int)$per_page) .','. (int)$per_page
	);
	$num_total = @$DB->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	
} else {
	
	# search by name
	
	$number = '';
	$search_url = '&amp;name='. urlEncode($name);
	
	$name_sql = str_replace(
		array( '*', '?' ),
		array( '%', '_' ),
		$name
	) .'%';
	$rs = $DB->execute(
'SELECT SQL_CALC_FOUND_ROWS
	`u`.`firstname` `fn`, `u`.`lastname` `ln`, `u`.`host_id` `hid`,`u`.`honorific` `hnr`, `u`.`user` `usern`, `s`.`name` `ext` 
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE
	`u`.`nobody_index` IS NULL AND (
	`u`.`lastname` LIKE _utf8\''. $DB->escape($name_sql) .'\' COLLATE utf8_unicode_ci OR
	`u`.`firstname` LIKE _utf8\''. $DB->escape($name_sql) .'\' COLLATE utf8_unicode_ci
	)
ORDER BY `u`.`lastname`, `u`.`firstname`
LIMIT '. ($page*(int)$per_page) .','. (int)$per_page
	);
	$num_total = @$DB->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	
}


?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:253px;"><?php echo __('Name suchen'); ?></th>
	<th style="width:234px;"><?php echo __('Nummer suchen'); ?></th>
	<th style="width:100px;"><?php echo __('Seite'), ' ', ($page+1), ' / ', $num_pages; ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td>
		<form method="get" action="<?php echo GS_URL_PATH; ?>">
		<?php echo gs_form_hidden($SECTION, $MODULE); ?>
		<input type="text" name="name" value="<?php echo htmlEnt($name); ?>" size="25" style="width:200px;" />
		<button type="submit" title="<?php echo __('Name suchen'); ?>" class="plain">
			<img alt="<?php echo __('Suchen'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/search.png" />
		</button>
		</form>
	</td>
	<td>
		<form method="get" action="<?php echo GS_URL_PATH; ?>">
		<?php echo gs_form_hidden($SECTION, $MODULE); ?>
		<input type="text" name="number" value="<?php echo htmlEnt($number); ?>" size="15" style="width:130px;" />
		<button type="submit" title="<?php echo __('Nummer suchen'); ?>" class="plain">
			<img alt="<?php echo __('Suchen'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/search.png" />
		</button>
		</form>
	</td>
	<td rowspan="2">
<?php

if ($page > 0) {
	echo
	'<a href="', gs_url($SECTION, $MODULE), $search_url, '&amp;page=', ($page-1), '" title="', __('zur&uuml;ckbl&auml;ttern'), '" id="arr-prev">',
	'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/back-cust.png" />',
	'</a>', "\n";
} else {
	echo
	'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/back-cust-dis.png" />', "\n";
}
if ($page < $num_pages-1) {
	echo
	'<a href="', gs_url($SECTION, $MODULE), $search_url, '&amp;page=', ($page+1), '" title="', __('weiterbl&auml;ttern'), '" id="arr-next">',
	'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/forward-cust.png" />',
	'</a>', "\n";
} else {
	echo
	'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/forward-cust-dis.png" />', "\n";
}

?>
	</td>
</tr>
<tr>
	<td colspan="2" class="quickchars">
<?php

$chars = array();
$chars['#'] = '';
for ($i=65; $i<=90; ++$i) $chars[chr($i)] = chr($i);
foreach ($chars as $cd => $cs) {
	echo '<a href="', gs_url($SECTION, $MODULE), '&amp;name=', htmlEnt($cs), '">', htmlEnt($cd), '</a>', "\n";
}

?>
	</td>
</tr>
</tbody>
</table>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:253px;"><?php echo __('Name'); ?></th>
	<th style="width:70px;"><?php echo __('Nummer'); ?></th>
	<th style="width:93px;"><?php echo __('User'); ?></th>
	<th style="width:45px;"><?php echo __('Host'); ?></th>
	<th style="width:100px;"><?php echo __('Status'); ?></th>
</tr>
</thead>
<tbody>

<?php

$sudo_url = (@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
	? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);

if (@$rs) {
	$i = 0;
	while ($r = $rs->fetchRow()) {
		echo '<tr class="', ((++$i % 2) ? 'odd':'even'), '">', "\n";
		
		echo '<td>', htmlEnt($r['ln']);
		if ($r['fn'] !='') echo ', ', htmlEnt($r['fn']);
		echo '</td>';
		//echo '<td>', htmlEnt($r['fn']),'</td>';	
		//echo '<td>', htmlEnt($r['hnr']), '</td>';	
		echo '<td>', $r['ext'], '</td>';
		echo '<td>', htmlEnt($r['usern']), '</td>';
		echo '<td>', $r['hid'], '</td>';
		echo '<td>';
		$state = gs_extstate_single( $r['ext'] );
		switch ($state) {
		case AST_MGR_EXT_UNKNOWN:
			echo '<img alt="" src="', GS_URL_PATH, 'crystal-svg/16/app/important.png" />&nbsp; ', __('unbekannt');
			break;
		case AST_MGR_EXT_IDLE:
			echo '<img alt="" src="', GS_URL_PATH, 'crystal-svg/16/act/greenled.png" />&nbsp; ', __('bereit');
			break;
		case AST_MGR_EXT_OFFLINE:
			echo '<img alt="" src="', GS_URL_PATH, 'crystal-svg/16/act/free_icon.png" />&nbsp; ', __('offline');
			break;
		case AST_MGR_EXT_INUSE:
			echo '<img alt="" src="', GS_URL_PATH, 'crystal-svg/16/act/redled.png" />&nbsp; ', __('spricht');
			break;
		case AST_MGR_EXT_RINGING:
			echo '<img alt="" src="', GS_URL_PATH, 'crystal-svg/16/app/knotify.png" />&nbsp; ', __('klingelt');
			break;
		default:
			echo $state;
		}
		echo '</td>';
		//echo '<td>', gs_extstate_single( $r['ext'] ), '</td>';
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
	<th style="width:220px;">
		<span class="sort-col"><?php echo __('Benutzer'); ?></span>
	</th>
	<th style="width:75px;">
		&nbsp;
	</th>
</tr>
</thead>
<tbody>
<tr>
	<th><?php echo __('Eingerichtete Benutzer'); ?>:</th>
	<td class="r"><?php echo count_users_configured($DB); ?></td>
</tr>
<tr>
	<th><?php echo __('Eingeloggte Benutzer'); ?>:
	</th>
	<td class="r"><?php echo count_users_logged_in($DB); ?></td>
</tr>
</tbody>
</table>
