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
include_once( GS_DIR .'inc/group-fns.php' );
include_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );
include_once( GS_DIR .'inc/gs-fns/gs_astphonebuttons.php' );

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

$action = @$_REQUEST['action'];
if (! in_array($action, array('', 'save', 'delete', 'edit', 'insert', 'remove', 'add', 'remove-perm', 'insert-perm', 'remove-member', 'insert-member'), true))
	$action = '';

$group_id    = (int)@$_REQUEST['id'];
$group_name  = trim(@$_REQUEST['name']);
$group_title = trim(@$_REQUEST['title']);
$page        = (int)@$_REQUEST['page'];

#####################################################################
# save group
#####################################################################
if ($action === 'save') {

	$ret = gs_group_change( $group_id, $group_name, $group_title );	
	
	 if (isGsError($ret)) {
		echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
	} elseif (! $ret) {
		echo '<div class="errorbox">', __('Gruppe konnte nicht gespeichert werden.') ,'</div>',"\n";
	}

	if ( GS_BUTTONDAEMON_USE == true )
		gs_buttondeamon_usergroups_update();

	sleep(1); // FIXME
	$action = '';  # view
}

#####################################################################
# add group
#####################################################################
if ($action === 'add') {


	$group_type = @$_REQUEST['type'];

	$ret = gs_group_add( $group_name, $group_title, $group_type );

	if (isGsError($ret)) {
		echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
	} elseif (! $ret) {
		echo '<div class="errorbox">', __('Gruppe konnte nicht angelegt werden.') ,'</div>',"\n";
	}	

	if ( GS_BUTTONDAEMON_USE == true )
		gs_buttondeamon_usergroups_update();

	sleep(1); // FIXME
	$action = '';  # view
}

#####################################################################
# delete group
#####################################################################
if ($action === 'delete') {
	$ret = gs_group_del( $group_name );

	if (isGsError($ret)) {
		echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
	} elseif (! $ret) {
		echo '<div class="errorbox">', __('Gruppe konnte nicht gel&ouml;scht werden.') ,'</div>',"\n";
	}
	
	if ( GS_BUTTONDAEMON_USE == true )
		gs_buttondeamon_usergroup_remove( $group_id );
	
	sleep(1); // FIXME
	$action = '';  # view
}

#####################################################################
# include group into an other group
#####################################################################
if ($action === 'insert') {
	$insert = @$_REQUEST['insert'];

	$ret = gs_group_member_add( $group_id, $insert, true );
	
	if (isGsError($ret)) {
		echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
	} elseif (! $ret) {
		echo '<div class="errorbox">', __('Gruppe konnte nicht hinzugef&uuml;gt werden.') ,'</div>',"\n";
	}
	sleep(1); // FIXME
	$action = 'edit';  # view
}

#####################################################################
# remove inclusion of a group
#####################################################################
if ($action === 'remove') {
	$remove = @$_REQUEST['remove'];
	
	$ret = gs_group_member_del( $group_id, $remove, true );
	
	if (isGsError($ret)) {
		echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
	} elseif (! $ret) {
		echo '<div class="errorbox">', __('Gruppe konnte nicht entfernt werden.') ,'</div>',"\n";
	}
	sleep(1); // FIXME
	$action = 'edit';  # view
}

#####################################################################
# add permission to a group
#####################################################################
if ($action === 'insert-perm') {
	$pg_group = (int)@$_REQUEST['group'];
	$pg_permission = trim(@$_REQUEST['permission']);

	$ret = 	gs_group_permission_add($group_id, $pg_group, $pg_permission);

	if (isGsError($ret)) {
		echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
	} elseif (! $ret) {
		echo '<div class="errorbox">', __('Berechtigung konnte nicht hinzugef&uuml;gt werden.') ,'</div>',"\n";
	}
	sleep(1); // FIXME
	$action = 'edit';  # view
}

#####################################################################
# remove permission from a group
#####################################################################
if ($action === 'remove-perm') {
	$pg_group = (int)@$_REQUEST['group'];
	$pg_permission = trim(@$_REQUEST['permission']);

	$ret = 	gs_group_permission_del($group_id, $pg_group, $pg_permission);

	if (isGsError($ret)) {
		echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
	} elseif (! $ret) {
		echo '<div class="errorbox">', __('Berechtigung konnte nicht entfernt werden.') ,'</div>',"\n";
	}
	sleep(1); // FIXME
	$action = 'edit';  # view
}

#####################################################################
# add member to a group
#####################################################################
if ($action === 'insert-member') {
	$pg_member = trim(@$_REQUEST['member']);

	$ret = 	gs_group_member_add($group_id, $pg_member);

	if (isGsError($ret)) {
		echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
	} elseif (! $ret) {
		echo '<div class="errorbox">', __('Mitglied konnte nicht hinzugef&uuml;gt werden.') ,'</div>',"\n";
	}
	sleep(1); // FIXME
	$action = 'edit';  # view
}

#####################################################################
# remove member from a group
#####################################################################
if ($action === 'remove-member') {
	$pg_member = trim(@$_REQUEST['member']);

	$ret = 	gs_group_member_del($group_id, $pg_member);

	if (isGsError($ret)) {
		echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
	} elseif (! $ret) {
		echo '<div class="errorbox">', __('Mitglied konnte nicht entfernt werden.') ,'</div>',"\n";
	}
	sleep(1); // FIXME
	$action = 'edit';  # view
}


#####################################################################
# edit
#####################################################################
if ($action == 'edit') {

	$group            = gs_group_info_get(Array($group_id));
	$group		  = $group[0];

	$groups_same_type = gs_group_info_get(false, $group['type']);
	$group_includes_ids = gs_group_includes_get(Array($group_id), true, true);
	$group_includes   = gs_group_info_get(array_diff($group_includes_ids , Array($group_id)));

	$members_c_dir = count(gs_group_members_get(Array($group['id']), false));
	$members_c_all = count(gs_group_members_get(Array($group['id']), true));


	echo '<form method="post" action="'.GS_URL_PATH.'">';
	echo gs_form_hidden($SECTION, $MODULE);
	//echo '<input type="hidden" name="action" value="save" />' ,"\n";
	echo '<input type="hidden" name="page" value="', $page ,'" />' ,"\n";
	echo '<input type="hidden" name="id" value="', $group['id'] ,'" />' ,"\n";

?>

<table cellspacing="1">
<thead>
	<tr>
		<th style="min-width:24em;" colspan="2">
			<?php echo __('Gew&auml;hlte Gruppe'); ?>
		</th>
	</tr>
</thead>
<tbody>
	<tr>
		<th style="min-width:12em;">
			<?php echo __('ID'); ?>
		</th>
		<td style="min-width:12em;">
			<?php echo $group['id']; ?>	
		</td>
	</tr>
	<tr>
		<th style="min-width:12em;">
			<?php echo __('Name'); ?>
		</th>
		<td style="min-width:12em;">
			<?php echo '<input type="text" name="name" value="', htmlEnt($group['name']) ,'" size="25" maxlength="50" style="width:96%;" />'; ?>	
		</td>
	</tr>

	<tr>
		<th><?php echo __('Titel'); ?>:</th>
		<td><?php echo '<input type="text" name="title" value="', htmlEnt($group['title']) ,'" size="25" maxlength="50" style="width:96%;" />'; ?></td>
	</tr>
	<tr>
		<th><?php echo __('Typ'); ?>:</th>
		<td><?php echo htmlEnt($group['type']); ?></td>
	</tr>
	<tr>
		<th><?php echo __('Mitglieder direkt'); ?>:</th>
		<td><?php echo htmlEnt($members_c_dir); ?></td>
	</tr>
	<tr>
		<th><?php echo __('Mitglieder gesamt'); ?>:</th>
		<td><?php echo htmlEnt($members_c_all); ?></td>
	</tr>

	<tr>
		<th></th>
		<td>
<?php
		echo  '<button type="submit" name="action" value="save" title="', __('Speichern') ,'" class="plain"><img alt="', __('Speichern') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/filesave.png" /></button>';
		echo '&nbsp;&nbsp;';
		echo  '<button type="cancel" title="', __('Abbrechen') ,'" class="plain"><img alt="', __('Abbrechen') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/cancel.png" /></button>';
?>
		</td>
	</tr>
</tbody>
</table>
</form>
<br>
<table cellspacing="1">
<thead>
<tr>
	<th style="min-width:21em;" colspan="5"><?php echo __('Untergruppen der Gruppe '), ' "',htmlEnt($group['name']),'"'; ?></th>
</tr>

<tr>
	<th style="min-width:12em;"><?php echo __('Gruppe'); ?></th>
	<th style="min-width:12em;width:18em;"><?php echo __('Titel'); ?></th>
	<th style="min-width:5em;"><?php echo __('Typ'); ?></th>
	<th style="min-width:3em;"><?php echo __('Mitglieder'); ?></th>
	<th style="min-width:1em;"></th>
</tr>
</thead>
<tbody>
<?php
	
	$i = 0;
	if ((count($groups_same_type) - count($group_includes) - 1) ) {
		echo '<tr class="',($i%2===0?'odd':'even'),'">' ,"\n";
		echo '<form method="post" action="'.GS_URL_PATH.'">';
		echo gs_form_hidden($SECTION, $MODULE);
		echo '<input type="hidden" name="action" value="insert" />' ,"\n";
		echo '<td class="l nobr" colspan="2">';
		echo '<select name="insert">', "\n";
		foreach ($groups_same_type as $group_same_type) {
			if (($group_same_type['id'] == $group['id']) || (in_array($group_same_type['id'], $group_includes_ids))) {

			} else
			echo '<option value="',$group_same_type['name'] ,'">',$group_same_type['name'], ' -- ', $group_same_type['title'],'</option>' ,"\n";
		}
		echo '</select>', "\n";
		echo '</td>', "\n";
		echo '<td>',$group['type']  ,'</td>', "\n";
		echo '<td class="r" colspan="2">', "\n";
		echo  '<button type="submit" name="id" value="'.$group['id'].'" title="', __('Gruppe Einf&uuml;gen') ,'" class="plain"><img alt="', __('Einf&uuml;gen') ,'" src="', GS_URL_PATH,'img/plus.gif" /></button>';
		echo '</td>', "\n";
		echo '</tr>' ,"\n";
		echo '</form>',"\n";
	}

	foreach ($group_includes as $group_include) {
		echo '<tr class="',($i%2===0?'odd':'even'),'">' ,"\n";
		echo '<td class="l nobr">';
		echo $group_include['name']  ,'</td>', "\n";
		echo '<td>',$group_include['title']  ,'</td>', "\n";	
		echo '<td>',$group_include['type']  ,'</td>', "\n";
		echo '<td class="r">',count(gs_group_members_get(Array($group_include['id']))), '</td>', "\n";
		echo '<td class="r">', "\n";
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=remove&amp;id='.$group['id'].'&amp;remove='.$group_include['name']) ,'"><img alt="', __('Entfernen') ,'" title="', __('Entfernen') ,'" src="', GS_URL_PATH ,'img/minus.gif" /></a>';
		echo '</td>', "\n";
		echo '</tr>' ,"\n";
		$i++;
	}

?>

</tbody>
</table>

<br>
<table cellspacing="1">
<thead>
<tr>
	<th style="min-width:21em;" colspan="3"><?php echo __('Berechtigungen der Gruppe'), ' "',htmlEnt($group['name']),'"'; ?></th>
</tr>

<tr>
	
	<th style="min-width:10em;"><?php echo __('Berechtigung'); ?></th>
	<th style="min-width:10em;"><?php echo __('auf Gruppe'); ?></th>
	<th style="min-width:1em;"></th>
</tr>
</thead>
<tbody>
<?php
	$group_permissions = gs_group_permissions_get_names($group['id']);

	echo '<tr class="',($i%2===0?'odd':'even'),'">' ,"\n";
	echo '<form method="post" action="'.GS_URL_PATH.'">';
	echo gs_form_hidden($SECTION, $MODULE);
	echo '<input type="hidden" name="page" value="'.$page.'" />' ,"\n";
	echo '<input type="hidden" name="id" value="'.$group_id.'" />' ,"\n";
	echo '<td>';
	echo '<select name="permission">', "\n";
	foreach (gs_group_permission_types_get() as $perm_type) {
		echo '<option value="',$perm_type,'">',$perm_type ,'</option>' ,"\n";
	}
	echo '</select>', "\n";
	echo '</td>', "\n";
	echo '<td>';
	echo '<select name="group">', "\n";
	foreach (gs_group_info_get() as $group_info) {
		echo '<option value="',$group_info['id'],'">',$group_info['name'],' -- ', $group_info['title'],'</option>' ,"\n";
	}
	echo '</select>', "\n";
	echo '</td>', "\n";
	echo '<td class="r" colspan="2">', "\n";
	echo  '<button type="submit" name="action" value="insert-perm" title="', __('Berechtigung Einf&uuml;gen') ,'" class="plain"><img alt="', __('Einf&uuml;gen') ,'" src="', GS_URL_PATH,'img/plus.gif" /></button>';
	echo '</td>', "\n";
	echo '</tr>' ,"\n";
	echo '</form>',"\n";
	
	
	
	$i=0;
	foreach ($group_permissions as $group_permission) {
		echo '<tr class="',($i%2===0?'odd':'even'),'">' ,"\n";
		echo '<td>',$group_permission['permission']  ,'</td>', "\n";
		echo '<td>',$group_permission['name']  ,'</td>', "\n";
		echo '<td class="r">', "\n";
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=remove-perm&amp;id='.$group['id'].'&amp;page='.$page.'&amp;group='.$group_permission['id'].'&amp;permission='.$group_permission['permission']) ,'"><img alt="', __('Entfernen') ,'" title="', __('Entfernen') ,'" src="', GS_URL_PATH ,'img/minus.gif" /></a>';
		echo '</td>', "\n";
		echo '</tr>' ,"\n";
		$i++;
	}

?>

</tbody>
</table>

<br>
<table cellspacing="1">
<thead>
<tr>
	<th style="min-width:21em;" colspan="4"><?php echo __('Verbindungen der Gruppe'), ' "',htmlEnt($group['name']),'"'; ?></th>
</tr>

<tr>
	<th style="min-width:10em;"><?php echo __('Typ'); ?></th>
	<th style="min-width:10em;"><?php echo __('Key'); ?></th>
	<th style="min-width:10em;"><?php echo __('Verbindung'); ?></th>
	<th style="min-width:1em;"></th>
</tr>
</thead>
<tbody>
<?php
	$group_externals = gs_group_connections_get($group['id']);
/*
	echo '<tr class="',($i%2===0?'odd':'even'),'">' ,"\n";
	echo '<form method="post" action="'.GS_URL_PATH.'">';
	echo gs_form_hidden($SECTION, $MODULE);
	echo '<input type="hidden" name="page" value="'.$page.'" />' ,"\n";
	echo '<input type="hidden" name="id" value="'.$group_id.'" />' ,"\n";
	echo '<td>';
	echo '<select name="external">', "\n";
	foreach (gs_group_external_types_get() as $ext_type) {
		echo '<option value="',$ext_type,'">',$ext_type ,'</option>' ,"\n";
	}
	echo '</select>', "\n";
	echo '</td>', "\n";
	echo '<td>';
	echo '<input type="text" name="key" value="id" size="20" maxlength="20" style="width:96%;" />';
	echo '</td>', "\n";
	echo '<td>';
	echo '<input type="text" name="connection" value="" size="20" maxlength="255" style="width:96%;" />';
	echo '</td>', "\n";
	echo '<td class="r" colspan="2">', "\n";
	echo  '<button type="submit" name="action" value="insert-perm" title="', __('Berechtigung Einf&uuml;gen') ,'" class="plain"><img alt="', __('Einf&uuml;gen') ,'" src="', GS_URL_PATH,'img/plus.gif" /></button>';
	echo '</td>', "\n";
	echo '</tr>' ,"\n";
	echo '</form>',"\n";
*/	
	$i=0;
	foreach ($group_externals as $group_external) {
		echo '<tr class="',($i%2===0?'odd':'even'),'">' ,"\n";
		echo '<td>',$group_external['type']  ,'</td>', "\n";
		echo '<td>',$group_external['key']  ,'</td>', "\n";
		echo '<td>',$group_external['connection']  ,'</td>', "\n";
		/*
		echo '<td class="r">', "\n";
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=remove-perm&amp;id='.$group['id'].'&amp;page='.$page.'&amp;type='.$group_external['type'].'&amp;permission='.$group_external['key']) ,'"><img alt="', __('Entfernen') ,'" title="', __('Entfernen') ,'" src="', GS_URL_PATH ,'img/minus.gif" /></a>';
		echo '</td>', "\n";
		*/
		echo '<td></td>', "\n";
		echo '</tr>' ,"\n";
		$i++;
	}

?>

</tbody>
</table>

<br>
<table cellspacing="1">
<thead>
<tr>
	<th style="min-width:21em;" colspan="4"><?php echo __('Mitglieder der Gruppe'), ' "',htmlEnt($group['name']),'"'; ?></th>
</tr>

<tr>
	<th style="min-width:10em;"><?php echo __('Typ'); ?></th>
	<th style="min-width:10em;"><?php echo __('Mitglied'); ?></th>
	<th style="min-width:1em;"></th>
</tr>
</thead>
<tbody>
<?php

	$group_members = gs_group_members_get_names($group['id']);

	if (!gs_group_connections_get($group['id'])) {
		echo '<tr class="',($i%2===0?'odd':'even'),'">' ,"\n";
		echo '<form method="post" action="'.GS_URL_PATH.'">';
		echo gs_form_hidden($SECTION, $MODULE);
		echo '<input type="hidden" name="page" value="'.$page.'" />' ,"\n";
		echo '<input type="hidden" name="id" value="'.$group_id.'" />' ,"\n";
		echo '<td>';
		echo '</td>', "\n";
		echo '<td>';
		
		if ($group['type'] === 'module_gui') {
			echo '<select name="member">', "\n";
			foreach($MODULES as $section) {
				if (! in_array($section['id'], gs_group_members_get(array($group['id']))) ) {
					echo '<option value="', $section['id'], '"', 'title="', htmlEnt($section['title']) ,'">';
					echo $section['title'];
					echo '</option>', "\n";
				}
				if (array_key_exists('sub', $section)) {
					foreach($section['sub'] as $module) {
						if (! in_array($module['id'], gs_group_members_get(array($group['id']))) ) {
							echo '<option value="', $module['id'], '"', 'title="', htmlEnt($section['title']), ' - ', htmlEnt($module['title']) ,'">';
							echo $section['title'], ' - ', $module['title'];
							echo '</option>', "\n";
						}
					}
				}
			}
			echo '</select>', "\n";
		} else {
			echo '<input type="text" name="member" value="Member" size="20" maxlength="20" style="width:96%;" />';
		}
		
		echo '</td>', "\n";
		echo '<td class="r" colspan="2">', "\n";
		echo  '<button type="submit" name="action" value="insert-member" title="', __('Mitglied Einf&uuml;gen') ,'" class="plain"><img alt="', __('Einf&uuml;gen') ,'" src="', GS_URL_PATH,'img/plus.gif" /></button>';
		echo '</td>', "\n";
		echo '</tr>' ,"\n";
		echo '</form>',"\n";
	}

	$i=0;
	if ($group['type'] === 'module_gui') {
		foreach ($MODULES as $section) {
			if (in_array($section['id'], gs_group_members_get(array($group['id']))) ) {
				$i++;
				echo '<tr class="',($i%2===0?'odd':'even'),'">' ,"\n";
				echo '<td>',$group['type'] ,'</td>', "\n";
				echo '<td>',$section['title'] ,'</td>', "\n";
				echo '<td class="r">', "\n";
				if (!gs_group_connections_get($group['id']))
					echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=remove-member&amp;id='.$group['id'].'&amp;page='.$page.'&amp;type=module_gui&amp;member='.$section['id']) ,'"><img alt="', __('Entfernen') ,'" title="', __('Entfernen') ,'" src="', GS_URL_PATH ,'img/minus.gif" /></a>';
				echo '</td>', "\n";
				echo '</tr>' ,"\n";
			}
			if (array_key_exists('sub', $section)) {
				foreach($section['sub'] as $module) {
					if (in_array($module['id'], gs_group_members_get(array($group['id']))) ) {
						echo '<tr class="',($i%2===0?'odd':'even'),'">' ,"\n";
						echo '<td>',$group['type'] ,'</td>', "\n";
						echo '<td>',$section['title'], ' - ', $module['title'] ,'</td>', "\n";
						echo '<td class="r">', "\n";
						if (!gs_group_connections_get($group['id']))
							echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=remove-member&amp;id='.$group['id'].'&amp;page='.$page.'&amp;type=module_gui&amp;member='.$module['id']) ,'"><img alt="', __('Entfernen') ,'" title="', __('Entfernen') ,'" src="', GS_URL_PATH ,'img/minus.gif" /></a>';
						echo '</td>', "\n";
						echo '</tr>' ,"\n";
						//$i++;
					}
				}
			}
		}
	} else {
		foreach ($group_members as $group_member) {
			echo '<tr class="',($i%2===0?'odd':'even'),'">' ,"\n";
			echo '<td>',$group_member['type']  ,'</td>', "\n";
			echo '<td>',$group_member['member']  ,'</td>', "\n";
			echo '<td class="r">', "\n";
			if (!gs_group_connections_get($group['id']))
				echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=remove-member&amp;id='.$group['id'].'&amp;page='.$page.'&amp;type='.$group_member['type'].'&amp;member='.$group_member['member']) ,'"><img alt="', __('Entfernen') ,'" title="', __('Entfernen') ,'" src="', GS_URL_PATH ,'img/minus.gif" /></a>';
			echo '</td>', "\n";
			echo '</tr>' ,"\n";
			$i++;
		}
	}

	echo '</tbody>'."\n";
	echo '</table>'."\n";

}


if ($action == '') {

$groups = gs_group_info_get();
$sort_key = array();
$per_page  = (int)gs_get_conf('GS_GUI_NUM_RESULTS', '15');
$num_total = count($groups);
$num_pages = ceil($num_total / $per_page);

?>

<table cellspacing="1">
<thead>
<tr>
	<th style="min-width:12em;"><?php echo __('Gruppe'); ?></th>
	<th style="min-width:12em;width:18em;"><?php echo __('Titel'); ?></th>
	<th style="min-width:5em;"><?php echo __('Typ'); ?></th>
	<th style="min-width:3em;"><?php echo __('U.Gr.'); ?></th>
	<th style="min-width:3em;"><?php echo __('Mitglieder'); ?></th>
	<th style="min-width:3em;">
<?php
echo ($page+1), ' / ', $num_pages, '&nbsp; ',"\n";

if ($page > 0) {
	echo
	'<a href="', gs_url($SECTION, $MODULE, null, 'page='.($page-1)), '" title="', __('zur&uuml;ckbl&auml;ttern'), '" id="arr-prev">',
	'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous.png" />',
	'</a>', "\n";
} else {
	echo
	'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous_notavail.png" />', "\n";
}

if ($page < $num_pages-1) {
	echo
	'<a href="', gs_url($SECTION, $MODULE, null, 'page='.($page+1)), '" title="', __('weiterbl&auml;ttern'), '" id="arr-next">',
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

<?php

foreach($groups as $key => $group) {
	$sort_key[$key] = $group['name'];
}

array_multisort($sort_key, SORT_ASC, SORT_STRING, $groups);

if (isGsError($groups)) {
	echo '<tr><td colspan="5">', $groups->getMsg() ,'</td></tr>';
} else {
	$i = 1;
	foreach ($groups as $group) {

		if (($i > ($per_page * ($page+1))) || ($i < ($per_page *  $page + 1)))  {
			$i++;
			continue;
		}

		$groups_same_type = gs_group_info_get(false, $group['type']);
		$group_includes_ids = gs_group_includes_get(Array($group['id']), true, true);
		$group_includes   = gs_group_info_get(array_diff($group_includes_ids , Array($group['id'])));

		echo '<tr class="',($i%2===0?'odd':'even'),'">' ,"\n";
			
		echo '<td class="l nobr">';
		echo $group['name']  ,'</td>', "\n";
		echo '<td>',$group['title']  ,'</td>', "\n";
		echo '<td>',$group['type']  ,'</td>', "\n";
		echo '<td>', count($group_includes_ids) ,'</td>', "\n";
		echo '<td class="r">',count(gs_group_members_get(Array($group['id']), false)),' / ',count(gs_group_members_get(Array($group['id']))), '</td>', "\n";
		echo '<td class="r">', "\n";

		echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=edit&amp;id='.$group['id'].'&amp;page='.$page) ,'"><img alt="', __('Bearbeiten') ,'" title="', __('Bearbeiten') ,'" src="', GS_URL_PATH ,'crystal-svg/16/act/edit.png" /></a>';

		echo '&nbsp;&nbsp;';

		echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=delete&amp;name='.$group['name'].'&amp;page='.$page) ,'" onclick="return confirm_delete();"><img alt="', __('L&ouml;schen') ,'" title="', __('L&ouml;schen') ,'" src="', GS_URL_PATH ,'crystal-svg/16/act/editdelete.png" /></a>';

		echo '</td>', "\n";

		echo '</tr>' ,"\n";
		
		$i++;
	}
	
	echo '<tr>' ,"\n";
	
	echo '<td colspan="5" class="transp">&nbsp;</td>', "\n";
	
	echo '<td class="transp r">', "\n";
//	echo '<button type="submit" class="plain" title="', __('Speichern') ,'"><img alt="', __('Speichern') ,'" src="', GS_URL_PATH ,'crystal-svg/16/act/filesave.png" /></button>';
	echo '</td>' ,"\n";
	
	echo '</tr>' ,"\n";
	
	$i=0;
	echo '<form method="post" action="'.GS_URL_PATH.'">', "\n";
	echo gs_form_hidden($SECTION, $MODULE);
	echo '<input type="hidden" name="action" value="add" />' ,"\n";

	echo '<tr class="',($i%2===0?'odd':'even'),'">' ,"\n";
	
	echo '<td class="l nobr">', "\n";
	echo '<input type="text" name="name" value="" size="20" maxlength="20" />', "\n";
	echo '</td>' ,"\n";
	
	echo '<td class="l nobr">', "\n";
	echo '<input type="text" name="title" value="" size="25" maxlength="50" style="width:96%;" />',"\n";
	echo '</td>' ,"\n";
	echo '<td>' ,"\n";
	echo '<select name="type">', "\n";
	foreach (gs_group_types_get() as $group_type) {
		echo '<option value="',$group_type,'">',$group_type ,'</option>' ,"\n";
	}
	echo '</select>', "\n";
	echo '</td>' ,"\n";
	echo '<td class="r" colspan="3">&nbsp;<br />', "\n";
	echo '<button type="submit" class="plain" title="', __('Speichern') ,'"><img alt="', __('Speichern') ,'" src="', GS_URL_PATH ,'crystal-svg/16/act/filesave.png" /></button>';
	echo '</td>', "\n";
	
	echo '</tr>' ,"\n";
	echo '</form>' ,"\n";

	++$i;

}
?>
</tbody>
</table>

<?php
}
#####################################################################
# view }
#####################################################################

?>
