<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2015, Markus Neubauer, Zeitblomstr. 29, 81735 München, Germany,
* http://www.std-soft.com/
* Markus Neubauer <markus.neubauer@email-online.org>
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
include_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";
echo '<script type="text/javascript">
//<![CDATA[
function confirm_delete() {
	return confirm(', utf8_json_quote(__("Wirklich l\xC3\xB6schen?")) ,');
}
//]]>
</script>' ,"\n";

$per_page = (int)GS_GUI_NUM_RESULTS;

$name         =      trim(@$_REQUEST['name'   ]);
$category          =      trim(@$_REQUEST['category' ]);
$save_category     =      trim(@$_REQUEST['scategory']);
$page         = (int)    (@$_REQUEST['page'   ]);
$delete_entry = (int)trim(@$_REQUEST['delete' ]);
$edit_entry   = (int)trim(@$_REQUEST['edit'   ]);
$save_entry   = (int)trim(@$_REQUEST['save'   ]);

$user_id = (int)@$_SESSION['sudo_user']['info']['id'];

if ($delete_entry > 0) {
	# delete entry
	
	$rs = $DB->execute(
'DELETE FROM `pb_category`
WHERE `id`='. $delete_entry .' AND `user_id`='. $user_id
	);
	
}

if ( $save_category != '' ) {
	# save entry

	if ($save_entry < 1) {
		
		$rs = $DB->execute(
'INSERT INTO `pb_category` (`id`, `user_id`, `category`) VALUES
(NULL, '. $user_id .', \''. $DB->escape($save_category) .'\')'
		);
		
	} else {
		
		$rs = $DB->execute(
'UPDATE `pb_category` SET `category`=\''. $DB->escape($save_category) .'\' 
 WHERE `id`='. $save_entry .' AND `user_id`='. $user_id
		);
		
	}
	$save_category = '';
}


# search by category
	
$search_url = 'category='. urlEncode($category);
	
$category_sql = '%' . str_replace(
	array( '*', '?' ),
	array( '%', '_' ),
	$category
) .'%';
	
$rs = $DB->execute(
	'SELECT SQL_CALC_FOUND_ROWS '.
		'`id`, `category` '.
	'FROM '.
		'`pb_category` '.
	'WHERE '.
		'`category` LIKE \''. $DB->escape($category_sql) .'\' '.
		'AND '.
		'`user_id`='. $user_id .' '.
	'ORDER BY `category` ' .
	'LIMIT '. ($page*(int)$per_page) .','. (int)$per_page
	);
$num_total = @$DB->numFoundRows();
$num_pages = ceil($num_total / $per_page);


?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:350px;"><?php echo __('Kategorie suchen'); ?></th>
	<th style="width:100px;"><?php echo __('Seite'), ' ', ($page+1), ' / ', $num_pages; ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td>
		<form method="get" action="<?php echo GS_URL_PATH; ?>">
		<?php echo gs_form_hidden($SECTION, $MODULE); ?>
		<input type="text" name="category" id="ipt-category" value="<?php echo htmlEnt($category); ?>" size="256" style="width:250px;" />
		<script type="text/javascript">/*<![CDATA[*/ try{ document.getElementById('ipt-category').focus(); }catch(e){} /*]]>*/</script>
		<button type="submit" title="<?php echo __('Kategorie suchen'); ?>" class="plain">
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
	echo '<a href="', gs_url($SECTION, $MODULE, null, 'category='. htmlEnt($cs)), '">', htmlEnt($cd), '</a>', "\n";
}

?>
	</td>
</tr>
</tbody>
</table>



<?php
echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
echo gs_form_hidden($SECTION, $MODULE), "\n";
echo '<input type="hidden" name="category" value="', htmlEnt($category), '" />', "\n";
?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:350px;"<?php if ($category=='') echo ' class="sort-col"'; ?>>
		<?php echo __('Kategorie'); ?>
	</th>
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
			echo '<input type="text" name="scategory" value="', htmlEnt($r['category']), '" size="50" maxlength="256" style="width:300px;" />';
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
			
			echo '<td>', htmlEnt($r['category']);
			echo '</td>', "\n";
			
			echo '<td>';
			$sudo_url =
				(@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
				? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'edit='.$r['id'] .'&amp;category='. rawUrlEncode($category) .'&amp;page='.$page), '" title="', __('bearbeiten'), '"><img alt="', __('bearbeiten'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='.$r['id'] .'&amp;page='.$page), '" title="', __('entfernen'), '" onclick="return confirm_delete();"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
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
		<input type="category" name="scategory" value="" size="50" maxlength="256" style="width:300px;" />
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
<p class="text">Hint: Categories are coming from the cloud and<br>
 &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; must be changed within the cloud to really become active here.</p>
