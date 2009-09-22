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


echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";


$per_page = (int)GS_GUI_NUM_RESULTS;

$name        = trim(@$_REQUEST['name']);
$number      = trim(@$_REQUEST['number']);
$save_name   = trim(@$_REQUEST['sname']);
$save_comment   = trim(@$_REQUEST['scomment']);
$save_number = trim(@$_REQUEST['snumber']);
$page = (int)@$_REQUEST['page'];
$delete_entry = (int)trim(@$_REQUEST['delete']);
$edit_entry   = (int)trim(@$_REQUEST['edit']);
$save_entry   = (int)trim(@$_REQUEST['save']);

//$group_id = (int)$DB->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $DB->escape(@$_SESSION['sudo_user']['name']).'\'' );
$group_id = 'NULL';

if ($delete_entry > 0) {
	# delete entry
	
	$rs = $DB->execute(
'DELETE FROM `blacklist`
WHERE `id`='. $delete_entry
	);
	
}

if  ($save_number != '') {
	# save entry
	
	if ($save_entry < 1) {
		
		$rs = $DB->execute(
'INSERT INTO `blacklist` (`group_id`, `name`, `comment`, `number`) VALUES
('. $group_id .', \''. $DB->escape($save_name) .'\', \''. $DB->escape($save_comment) .'\', \''. $DB->escape($save_number) .'\')'
		);
		
	} else {
		$rs = $DB->execute(
'UPDATE `blacklist` SET `name`=\''. $DB->escape($save_name) .'\', `comment`=\''. $DB->escape($save_comment) .'\', `number`=\''. $DB->escape($save_number) .'\'
WHERE `id`='. $save_entry );

	$save_number = '';
	$save_name = '';
		
	}
}





if ($number != '') {
	
	# search by number
	
	$search_url = 'number='. urlEncode($number);
	
	$number_sql = str_replace(
		array( '*', '?' ),
		array( '%', '_' ),
		$number
	) .'%';

/*
	echo 'SELECT SQL_CALC_FOUND_ROWS
	`id`, `name`, `comment`, `number` 
FROM
	`blacklist` 
WHERE	
	`number` LIKE \''. $DB->escape($number_sql) .'\'
ORDER BY `name`, `comment`
LIMIT '. ($page*(int)$per_page) .','. (int)$per_page;
*/
	

	$rs = $DB->execute(
'SELECT SQL_CALC_FOUND_ROWS
	`id, `name`, `comment`, `number` 
FROM
	`blacklist` 
WHERE	
	`number` LIKE \''. $DB->escape($number_sql) .'\'
ORDER BY `name`, `comment`
LIMIT '. ($page*(int)$per_page) .','. (int)$per_page
	);
	$num_total = @$DB->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	
} else {
	
	# search by name
	
	$number = '';
	$search_url = 'name='. urlEncode($name);
	
	$name_sql = str_replace(
		array( '*', '?' ),
		array( '%', '_' ),
		$name
	) .'%';

/*
echo 'SELECT SQL_CALC_FOUND_ROWS
	`id`, `name`, `comment`, `number`
FROM
	`blacklist` 
WHERE
	( `name` LIKE _utf8\''. $DB->escape($name_sql) .'\' COLLATE utf8_unicode_ci OR
	`comment` LIKE _utf8\''. $DB->escape($name_sql) .'\' COLLATE utf8_unicode_ci )
ORDER BY `name`, `comment`
LIMIT '. ($page*(int)$per_page) .','. (int)$per_page;
*/

	$rs = $DB->execute(
'SELECT SQL_CALC_FOUND_ROWS
	`id`, `name`, `comment`, `number`
FROM
	`blacklist` 
WHERE   
	( `name` LIKE _utf8\''. $DB->escape($name_sql) .'\' COLLATE utf8_unicode_ci OR
	`comment` LIKE _utf8\''. $DB->escape($name_sql) .'\' COLLATE utf8_unicode_ci )
ORDER BY `name`, `comment`
LIMIT '. ($page*(int)$per_page) .','. (int)$per_page
	);
	$num_total = @$DB->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	
}


?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:270px;"><?php echo __('Name/Kommentar suchen'); ?></th>
	<th style="width:200px;"><?php echo __('Nummer suchen'); ?></th>
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
	'<a href="', gs_url($SECTION, $MODULE, null, $search_url), '&amp;page=', ($page-1), '" title="', __('zur&uuml;ckbl&auml;ttern'), '" id="arr-prev">',
	'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/back-cust.png" />',
	'</a>', "\n";
} else {
	echo
	'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/back-cust-dis.png" />', "\n";
}
if ($page < $num_pages-1) {
	echo
	'<a href="', gs_url($SECTION, $MODULE, null, $search_url), '&amp;page=', ($page+1), '" title="', __('weiterbl&auml;ttern'), '" id="arr-next">',
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
	echo '<a href="', gs_url($SECTION, $MODULE, null, 'name='. htmlEnt($cs)), '">', htmlEnt($cd), '</a>', "\n";
}

?>
	</td>
</tr>
</tbody>
</table>



<?php
echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
echo gs_form_hidden($SECTION, $MODULE), "\n";
echo '<input type="hidden" name="name" value="', htmlEnt($name), '" />', "\n";
echo '<input type="hidden" name="number" value="', htmlEnt($number), '" />', "\n";
?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:200px;">
		<?php echo ($number=='') ? '<span class="sort-col">'. __('Name') .'</span>' : _('Name') .', '. __('Kommentar'); ?>
	</th>
	<th style="width:200px;">
		<?php echo ($number=='') ? '<span class="sort-col">'. __('Kommentar') .'</span>' : _('Name') .', '. __('Kommentar'); ?>
	</th>
	<th style="width:200px;">
		<?php echo ($number=='') ? __('Nummer') : '<span class="sort-col">'. __('Nummer'), '</span>'; ?>
	</th>
	<th style="width:100px;">&nbsp;</th>
</tr>
</thead>
<tbody>

<?php

if (@$rs) {
	$i = 0;
	while ($r = $rs->fetchRow()) {
		echo '<tr class="', ((++$i % 2 == 0) ? 'even':'odd'), '">', "\n";
		
		if ($r['id']==$edit_entry) {
 			
			echo '<td>';
			echo '<input type="text" name="sname" value="', htmlEnt($r['name']), '" size="20" maxlength="40" style="width:190px;" />';
			echo '</td>', "\n";
			echo '<td>';
			echo '<input type="text" name="scomment" value="', htmlEnt($r['comment']), '" size="20" maxlength="80" style="width:190px;" />';
			echo '</td>', "\n";
			
			echo '<td>';
			echo '<input type="text" name="snumber" value="', htmlEnt($r['number']), '" size="20" maxlength="25" style="width:190px;" />';
			echo '</td>', "\n";
			
			echo '<td>';
			echo '<input type="hidden" name="save" value="', $r['id'], '" />';
			echo '<input type="hidden" name="page" value="', $page, '" />';
			echo '<button type="submit" title="', __('Eintrag speichern'), '" class="plain">';
			echo '<img alt="', __('speichern'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/filesave.png" />';
			echo '</button>';
			echo '<button type="reset" title="', __('r&uuml;ckg&auml;ngig'), '" class="plain">';
			echo '<img alt="', __('r&uuml;ckg&auml;ngig'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/reload.png" />';
			echo '</button>';
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'page='. $page), '" title="', __('abbrechen'), '"><img alt="', __('abbrechen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/cancel.png" /></a>';
	
			echo '</td>';
			
		} else {
			
			echo '<td>', htmlEnt($r['name']); 
			echo '</td>', "\n";
			echo '<td>', htmlEnt($r['comment']);
			echo '</td>', "\n";
			echo '<td>', htmlEnt($r['number']), '</td>', "\n";
			
			echo '<td>';
			$sudo_url =
				(@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
				? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'edit='.$r['id'] .'&amp;name='. rawUrlEncode($name) .'&amp;number='. rawUrlEncode($number) .'&amp;page='.$page), '" title="', __('bearbeiten'), '"><img alt="', __('bearbeiten'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='.$r['id'] .'&amp;page='.$page), '" title="', __('entfernen'), '"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
			echo '</td>';
			
		}
		
		echo '</tr>', "\n";
	}
}

?>


<tr>
<?php
if ($edit_entry < 1) {
?>
	<td>
		<input type="text" name="sname" value="" size="20" maxlength="40" style="width:190px;" />
	</td>
	<td>	<input type="text" name="scomment" value="" size="20" maxlength="40" style="width:190px;" />
	</td>
	<td>
		<input type="text" name="snumber" value="" size="20" maxlength="25" style="width:190px;" />
	</td>
	<td>
		<button type="submit" title="<?php echo __('Eintrag speichern'); ?>" class="plain">
			<img alt="<?php echo __('Speichern'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
		</button>
		<?php /*echo '<a href="', gs_url($SECTION, $MODULE, null, 'page='.$page), '" title="abbrechen"><img alt="abbrechen" src="', GS_URL_PATH, 'crystal-svg/16/act/cancel.png" /></a>';*/ ?>
	</td>
<?php
}
?>
</tr>

</tbody>
</table>

</form>
