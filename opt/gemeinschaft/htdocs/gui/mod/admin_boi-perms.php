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
* Soeren Sprenger <soeren.sprenger@amooma.de>
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

echo '<script type="text/javascript">
//<![CDATA[
function confirm_delete() {
	return confirm(', utf8_json_quote(__("Wirklich l\xC3\xB6schen?")) ,');
}
//]]>
</script>' ,"\n";

$per_page = (int)GS_GUI_NUM_RESULTS;
$action   =      @$_REQUEST['action'   ] ;
$host_id  = (int)@$_REQUEST['p_host_id'] ;
$user_id  = (int)@$_REQUEST['p_user_id'] ;
$page     = (int)@$_REQUEST['page'     ] ;
$u_rname  = trim(@$_REQUEST['u_rname'  ]);
$host     = trim(@$_REQUEST['host'     ]);

if ($action == '') $action = 'view';
if (@$_REQUEST['p_user'] != '') $action = 'add';


if ($action === 'add') {
	$user_id = (int)$DB->executeGetOne(
		'SELECT `id` FROM `users` WHERE `user`=\''. $DB->escape(trim(@$_REQUEST['p_user'])) .'\'' );
	if ($user_id < 1) {
		echo '<div class="errorbox">', sPrintF(__('Benutzer &quot;%s&quot; unbekannt.'), @$_REQUEST['p_user']) ,'</div>' ,"\n";
	} else {
		$perms = '';
		if ((int)@$_REQUEST['p_perm_l'] == 1) $perms.= 'l';
		if ($perms == '') {
			echo '<div class="errorbox">', __('Sie haben keine Berechtigung ausgew&auml;hlt.') ,'</div>' ,"\n";
		} else {
			$ok = $DB->execute(
				'REPLACE INTO `boi_perms` (`user_id`, `host_id`, `roles`) '.
				'VALUES ('. $user_id .', '. $host_id .', \''. $DB->escape($perms) .'\')' );
			if (! $ok) {
				echo '<div class="errorbox">', 'Error.' ,'</div>' ,"\n";
			}
		}
	}
	$action = 'view';
}

if ($action === 'save') {
	$perms = '';
	if ((int)@$_REQUEST['p_perm_l'] == 1) $perms.= 'l';
	if ($perms == '') {
		$ok = $DB->execute(
			'DELETE FROM `boi_perms` '.
			'WHERE '.
				'`host_id`='. $host_id .' AND '.
				'`user_id`='. $user_id .' '.
			'LIMIT 1' );
	} else {
		$ok = $DB->execute(
			'REPLACE INTO `boi_perms` (`user_id`, `host_id`, `roles`) '.
			'VALUES ('. $user_id .', '. $host_id .', \''. $DB->escape($perms) .'\')' );
	}
	if (! $ok) {
		echo '<div class="errorbox">', 'Error.' ,'</div>' ,"\n";
	}
	$action = 'view';
}

if (preg_match('/^del_([0-9]+)_([0-9]+)/', $action, $m)) {
	$host_id = (int)$m[1];
	$user_id = (int)$m[2];
	$DB->execute(
		'DELETE FROM `boi_perms` '.
		'WHERE '.
			'`host_id`='. $host_id .' AND '.
			'`user_id`='. $user_id .' '.
		'LIMIT 1' );
	$action = 'view';
}

if (preg_match('/^edit_([0-9]+)_([0-9]+)/', $action, $m)) {
	$host_id = (int)$m[1];
	$user_id = (int)$m[2];
	$action = 'edit';
}


$where = array();
if ($u_rname) {
	$where[] =
		'`u`.`user` LIKE \''. $DB->escape($u_rname).'%\' OR '.
		'`u`.`firstname` LIKE \''. $DB->escape($u_rname).'%\' OR '.
		'`u`.`lastname` LIKE \''. $DB->escape($u_rname).'%\'';
}
if ($host) {
	$where[] = '`h`.`host` LIKE \'%'. $DB->escape($host).'%\'';
}

?>

<?php

$DB->execute( 'DELETE FROM `boi_perms` WHERE `roles`=\'\'' );
$rs = $DB->execute(
'SELECT	SQL_CALC_FOUND_ROWS
	`p`.`user_id`, `u`.`user`, `u`.`firstname` `u_fn`, `u`.`lastname` `u_ln`,
	`p`.`host_id`, `h`.`host`, `h`.`comment` `h_comment`,
	`p`.`roles`
FROM
	`boi_perms` `p` JOIN
	`users` `u` ON (`u`.`id`=`p`.`user_id`) JOIN
	`hosts` `h` ON (`h`.`id`=`p`.`host_id`)
'. (count($where)>0 ? ' WHERE ('.implode(') AND (',$where).') ' : '') .'
ORDER BY
	`h`.`host`, `u`.`user`
LIMIT
	'. ($page*(int)$per_page) .','. (int)$per_page
);

$num_total = @$DB->numFoundRows();
$num_pages = ceil($num_total / $per_page);

?>

<form method="get" action="<?php echo GS_URL_PATH; ?>">
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:253px;"><?php echo __('Name suchen'); ?></th>
	<th style="width:234px;"><?php echo __('Host suchen'); ?></th>
	<th style="width:100px;"><?php echo __('Seite'), ' ', ($page+1), ' / ', $num_pages; ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td>
		<?php echo gs_form_hidden($SECTION, $MODULE); ?>
		<input type="text" name="u_rname" value="<?php echo htmlEnt($u_rname); ?>" size="25" style="width:200px;" />
		<button type="submit" title="<?php echo __('Name suchen'); ?>" class="plain">
			<img alt="<?php echo __('Suchen'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/search.png" />
		</button>
	</td>
	<td>
		<?php echo gs_form_hidden($SECTION, $MODULE); ?>
		<input type="text" name="host" value="<?php echo htmlEnt($host); ?>" size="15" style="width:130px;" />
		<button type="submit" title="<?php echo __('Nummer suchen'); ?>" class="plain">
			<img alt="<?php echo __('Suchen'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/search.png" />
		</button>
	</td>
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
	<tr>
		<td colspan="2" class="quickchars">
<?php
	
	$chars = array();
	$chars['#'] = '';
	for ($i=65; $i<=90; ++$i) $chars[chr($i)] = chr($i);
	foreach ($chars as $cd => $cs) {
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'u_rname='. htmlEnt($cs)), '">', htmlEnt($cd), '</a>', "\n";
	}
	
?>
		</td>
	</tr>
</tbody>
</table>
</form>


<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<table cellspacing="1">
<thead>
<tr>
	<th style="width:20em;" colspan="2" class="sort-col"><?php echo __('Host'); ?></th>
	<th style="width:18em;" colspan="2"><?php echo __('Benutzer'); ?></th>
	<th style="width:8em;"><?php echo __('Admin-Rechte'); ?></th>
	<th style="width:4.5em;">&nbsp;</th>
</tr>
</thead>
<tbody>
<?php

$i=0;
while ($r = $rs->fetchRow()) {
	echo '<tr class="', ($i%2===0 ? 'odd':'even') ,'">' ,"\n";
	echo '<td>', $r['host'] ,'</td>' ,"\n";
	echo '<td>', htmlEnt($r['h_comment']) ,'</td>' ,"\n";
	echo '<td>', $r['user'] ,'</td>' ,"\n";
	echo '<td>', htmlEnt($r['u_fn']) ,' ', htmlEnt($r['u_ln']) ,'</td>' ,"\n";
	echo '<td>' ,"\n";
	echo '<span class="nobr">';
	if ($action !== 'edit'
	||  $r['host_id'] != $host_id
	||  $r['user_id'] != $user_id) {
		echo '<input type="checkbox"';
		if (strPos($r['roles'], 'l') !== false)
			echo ' checked="checked"';
		echo ' disabled="disabled" />';
		echo '<label>', __('Lokal') ,'</label>';
	} else {
		echo '<input type="checkbox" name="p_perm_l" id="ipt-p_perm_l" value="1"';
		if (strPos($r['roles'], 'l') !== false)
			echo ' checked="checked"';
		echo ' />';
		echo '<label for="ipt-p_perm_l">', __('Lokal') ,'</label>';
	}
	echo '</span>' ,"\n";
	echo '</td>' ,"\n";
	echo '<td>' ,"\n";
	if ($action !== 'edit') {
		echo '<button type="submit" name="action" value="edit_'.$r['host_id'].'_'.$r['user_id'].'" class="plain" title="', __('Regel bearbeiten') ,'"><img alt="', __('Bearbeiten') ,'" src="', GS_URL_PATH ,'crystal-svg/16/act/edit.png" /></button>&nbsp;';
		echo '<button type="submit" name="action" value="del_'.$r['host_id'].'_'.$r['user_id'].'" class="plain" title="', __('Regel l&ouml;schen') ,'" onclick="return confirm_delete();"><img alt="', __('L&ouml;schen') ,'" src="', GS_URL_PATH ,'crystal-svg/16/act/editdelete.png" /></button>';
	} else {
		if ($r['host_id'] == $host_id
		&&  $r['user_id'] == $user_id) {
			echo '<input type="hidden" name="p_host_id" value="', $host_id ,'" />';
			echo '<input type="hidden" name="p_user_id" value="', $user_id ,'" />';
			echo '<button type="submit" name="action" value="save" class="plain" title="', __('Regel speichern') ,'"><img alt="', __('Speichern') ,'" src="', GS_URL_PATH ,'crystal-svg/16/act/filesave.png" /></button>';
		}
	}
	echo '</td>' ,"\n";
	echo '</tr>' ,"\n";
	++$i;
}

if ($action !== 'edit') {
	echo '<tr class="', ($i%2===0 ? 'odd':'even') ,'">' ,"\n";
	echo '<td colspan="2"><select name="p_host_id">' ,"\n";
	$rs = $DB->execute(
		'SELECT `id`, `host`, `comment` '.
		'FROM `hosts` '.
		'WHERE `is_foreign`=1 '.
		'ORDER BY `host`'
		);
	while ($host = $rs->fetchRow()) {
		echo '<option value="', $host['id'] ,'" title="', htmlEnt($host['comment']) ,'">', $host['host'] ,'</option>' ,"\n";
	}
	echo '</select></td>' ,"\n";
	echo '<td colspan="2"><input type="text" name="p_user" size="10" /></td>' ,"\n";
	echo '<td>' ,"\n";
	echo '<span class="nobr">';
	echo '<input type="checkbox" name="p_perm_l" id="ipt-p_perm_l" value="1"';
	echo ' checked="checked"';
	echo ' />';
	echo '<label for="ipt-p_perm_l">', __('Lokal') ,'</label>';
	echo '</span>' ,"\n";
	echo '</td>' ,"\n";
	echo '<td>' ,"\n";
	echo '<button type="submit" class="plain" title="', __('Regel speichern') ,'"><img alt="', __('Speichern') ,'" src="', GS_URL_PATH ,'crystal-svg/16/act/filesave.png" /></button>';
	echo '</td>' ,"\n";
	echo '</tr>' ,"\n";
}

?>
</tbody>
</table>

</form>

