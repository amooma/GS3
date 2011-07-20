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

$user_id           = (int)@$_SESSION['sudo_user']['info']['id'];
$user_groups       = gs_group_members_groups_get(array($user_id), 'user');
$permission_groups = gs_group_permissions_get($user_groups, 'phonebook_imported_edit');
$group_members     = gs_group_members_get($permission_groups);

if ( in_array($user_id, $group_members) )
	$is_admin = true;
else
	$is_admin = false;

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";


$per_page = (int)GS_GUI_NUM_RESULTS;

$name         =  trim(@$_REQUEST['name'  ]);
$number       =  trim(@$_REQUEST['number']);
$save_lname   =      trim(@$_REQUEST['slname' ]);
$save_fname   =      trim(@$_REQUEST['sfname' ]);
$save_number  =      trim(@$_REQUEST['snumber']);
$save_group   =      trim(@$_REQUEST['sgroup']);
$page         = (int)(@$_REQUEST['page'  ]);
$delete_entry = (int)trim(@$_REQUEST['delete' ]);
$edit_entry   = (int)trim(@$_REQUEST['edit'   ]);
$save_entry   = (int)trim(@$_REQUEST['save'   ]);

if ($delete_entry > 0) {
	# delete entry
	
	$rs = $DB->execute(
'DELETE FROM `pb_ldap`
WHERE `id`='. $delete_entry
	);
	
}

if (($save_lname != '' || $save_fname != '') && ($save_number != '')) {
	# save entry
	
	if ($save_entry < 1) {
		
		$rs = $DB->execute(
'INSERT INTO `pb_ldap` (`lastname`, `firstname`, `number`, `group_id`) VALUES
(\''. $DB->escape($save_lname) .'\', \''. $DB->escape($save_fname) .'\', \''. $DB->escape($save_number) .'\', ' . $save_group .')'
		);
		
	} else {
		
		$rs = $DB->execute(
'UPDATE `pb_ldap` SET `lastname`=\''. $DB->escape($save_lname) .'\', `firstname`=\''. $DB->escape($save_fname) .'\', `number`=\''. $DB->escape($save_number) .'\', `group_id`=' . $save_group . '
WHERE `id`='. $save_entry
		);
		unset($save_number);
		unset($save_lname);
		unset($save_fname);
		unset($save_entry);
		
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
	$rs = $DB->execute(
		'SELECT SQL_CALC_FOUND_ROWS '.
			'`id`, `firstname` `fn`, `lastname` `ln`, `number` `ext`, `group_id` '.
		'FROM `pb_ldap` '.
		'WHERE `number` LIKE \''. $DB->escape($number_sql) .'\' '.
		'AND `group_id` IN ('. implode(',', $user_groups) .') '.
		'ORDER BY `number` '.
		'LIMIT '. ($page*(int)$per_page) .','. (int)$per_page
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
	$rs = $DB->execute(
		'SELECT SQL_CALC_FOUND_ROWS '.
			'`id`, `firstname` `fn`, `lastname` `ln`, `number` `ext`, `group_id` '.
		'FROM `pb_ldap` '.
		'WHERE '.
			'(`lastname` LIKE _utf8\''. $DB->escape($name_sql) .'\' COLLATE utf8_unicode_ci OR '.
			'`firstname` LIKE _utf8\''. $DB->escape($name_sql) .'\' COLLATE utf8_unicode_ci) '.
			'AND ( `group_id` IN ('. implode(',', $user_groups) .') ) '.
		'ORDER BY `lastname`, `firstname`, `user`, `number` DESC '.
		'LIMIT '. ($page*(int)$per_page) .','. (int)$per_page
		);
	$num_total = @$DB->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	
}


?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:270px;"><?php echo __('Name suchen'); ?></th>
	<th style="width:200px;"><?php echo __('Nummer suchen'); ?></th>
<?php
	if ($is_admin == true)
		echo '<th style="width:120px;">&nbsp;</th>';
?>
	<th style="width:100px;"><?php echo __('Seite'), ' ', ($page+1), ' / ', $num_pages; ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td>
		<form method="get" action="<?php echo GS_URL_PATH; ?>">
		<?php echo gs_form_hidden($SECTION, $MODULE); ?>
		<input type="text" name="name" id="ipt-name" value="<?php echo htmlEnt($name); ?>" size="25" style="width:200px;" />
		<script type="text/javascript">/*<![CDATA[*/ try{ document.getElementById('ipt-name').focus(); }catch(e){} /*]]>*/</script>
		<button type="submit" title="<?php echo __('Name suchen'); ?>" class="plain">
			<img alt="<?php echo __('Suchen'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/search.png" />
		</button>
		</form>
	</td>
	<td <?php echo ($is_admin == true ? 'colspan=2' : ''); ?>>
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
	'<img alt="weiter" src="', GS_URL_PATH, 'crystal-svg/32/act/forward-cust.png" />',
	'</a>', "\n";
} else {
	echo
	'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/forward-cust-dis.png" />', "\n";
}

?>
	</td>
</tr>
<tr>
	<td colspan="<?php echo ($is_admin == true ? '3' : '2'); ?>" class="quickchars">
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
	<th style="width:270px;"<?php if ($number=='') echo ' class="sort-col"'; ?>>
		<?php echo __('Name'); ?>
	</th>
	<th style="width:200px;"<?php if ($number!='') echo ' class="sort-col"'; ?>>
		<?php echo __('Nummer'); ?>
	</th>
<?php
	if ($is_admin == true)
		echo '<th style="width:120px;">' . __('Gruppe') . '</th>';
?>
	<th style="width:100px;"></th>
</tr>
</thead>
<tbody>

<?php

if (@$rs) {
	$i = 0;
	while ($r = $rs->fetchRow()) {
		echo '<tr class="', ((++$i % 2 == 0) ? 'even':'odd'), '">', "\n";
		
		/*
		echo '<td>', htmlEnt($r['ln']);
		if ($r['fn'] != '') echo ', ', htmlEnt($r['fn']);
		echo '</td>', "\n";
		
		echo '<td>', htmlEnt($r['ext']), '</td>', "\n";

		if ($is_admin == true) {
			$group_info = gs_group_info_get(array($r['group_id']));
			echo '<td>' . $group_info[0]['name'] . '</td>';
		}
				
		echo '<td>';
		$sudo_url =
			(@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
			? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);
		if ($r['ext'] != $_SESSION['sudo_user']['info']['ext'])
			echo '<a href="', GS_URL_PATH, 'srv/pb-dial.php?n=', rawUrlEncode($r['ext']), $sudo_url, '" title="', __('w&auml;hlen'), '"><img alt="', __('w&auml;hlen'), '" src="', GS_URL_PATH, 'crystal-svg/16/app/yast_PhoneTTOffhook.png" /></a>';
		else echo '&nbsp;';
		echo '</td>';
		*/
		if ($r['id']==$edit_entry) {
 			
			echo '<td>';
			echo '<input type="text" name="slname" value="', htmlEnt($r['ln']), '" size="15" maxlength="40" style="width:125px;" /><input type="text" name="sfname" value="', htmlEnt($r['fn']), '" size="15" maxlength="40" style="width:115px;" />';
			echo '</td>', "\n";
			
			echo '<td>';
			echo '<input type="text" name="snumber" value="', htmlEnt($r['ext']), '" size="15" maxlength="25" style="width:150px;" />';
			echo '</td>', "\n";

			echo '<td>';
			echo '<select name="sgroup">';
			$groups = gs_group_info_get();
			foreach ($groups as $group) {
				if ( in_array(1, $user_groups) || in_array($group['id'], $user_groups) )
					echo '<option value=' . $group['id'] . '>' . $group['name'] . '</option>' . "\n";
			}
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
			
			echo '<td>', htmlEnt($r['ln']);
			if ($r['fn'] != '') echo ', ', htmlEnt($r['fn']);
			echo '</td>', "\n";
		
			echo '<td>', htmlEnt($r['ext']), '</td>', "\n";

			if ($is_admin == true) {
				$group_info = gs_group_info_get(array($r['group_id']));
				echo '<td>' . $group_info[0]['name'] . '</td>';
			}
				
			echo '<td>';
			$sudo_url =
				(@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
				? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);
			if ($r['ext'] != $_SESSION['sudo_user']['info']['ext'])
				echo '<a href="', GS_URL_PATH, 'srv/pb-dial.php?n=', rawUrlEncode($r['ext']), $sudo_url, '" title="', __('w&auml;hlen'), '"><img alt="', __('w&auml;hlen'), '" src="', GS_URL_PATH, 'crystal-svg/16/app/yast_PhoneTTOffhook.png" /></a> &nbsp; ';

			if ($is_admin == true) {
				echo '<a href="', gs_url($SECTION, $MODULE, null, 'edit='.$r['id'] .'&amp;name='. rawUrlEncode($name) .'&amp;number='. rawUrlEncode($number) .'&amp;page='.$page), '" title="', __('Bearbeiten'), '"><img alt="', __('bearbeiten'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
				echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='.$r['id'] .'&amp;page='.$page), '" title="', __('entfernen'), '" onclick="return confirm_delete();"><img alt="', __('Entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
			}
			echo '</td>';
			
		}

		
		echo '</tr>', "\n";
	}
}

?>

<tr>
<?php
if ( ($is_admin == true) && ($edit_entry < 1) ) {
?>
	<td>
		<input type="text" name="slname" value="" size="15" maxlength="40" style="width:125px;" /><input type="text" name="sfname" value="" size="15" maxlength="40" style="width:115px;" />
	</td>
	<td>
		<input type="text" name="snumber" value="" size="15" maxlength="25" style="width:150px;" />
	</td>
	<td>
		<select name="sgroup">
<?php
	$groups = gs_group_info_get();
	foreach ($groups as $group) {
		if ( in_array(1, $user_groups) || in_array($group['id'], $user_groups) )
			echo '<option value=' . $group['id'] . '>' . $group['name'] . '</option>' . "\n";
	}
?>
		</select>
	</td>
	<td>
		<button type="submit" title="<?php echo __('Eintrag speichern'); ?>" class="plain">
			<img alt="<?php echo __('Speichern'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
		</button>
	</td>
<?php
}
?>
</tr>

</tbody>
</table>

</form>
