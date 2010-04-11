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
if (! in_array($action, array('', 'save', 'edit'), true))
	$action = '';

$group_id    = (int)@$_REQUEST['id'];
$page        = (int)@$_REQUEST['page'];

if (($action == 'save') && ($group_id > 0)) {
	$group_members = gs_group_members_get(Array($group_id));
	foreach ($MODULES as $section) {
		if (@$section['id'])
		if (array_key_exists('m'.$section['id'], $_REQUEST) && ((int)$_REQUEST['m'.$section['id']] == 1) ) {
			if (!in_array($section['id'],$group_members)) {
				gs_group_member_add($group_id, $section['id']);
			}
		} else {
			if (in_array($section['id'],$group_members)) {
				gs_group_member_del($group_id, $section['id']);
			}
		}

		if (array_key_exists('sub', $section))
		foreach ($section['sub'] as $module) {
			if (array_key_exists('m'.$module['id'], $_REQUEST) && ((int)$_REQUEST['m'.$module['id']] == 1) ) {
				if (!in_array($module['id'],$group_members)) {
					gs_group_member_add($group_id, $module['id']);
				}
			} else {
				if (in_array($module['id'],$group_members)) {
					gs_group_member_del($group_id, $module['id']);
				}
			}

		}
	}
	$action = '';
}

if (($action == 'edit') && ($group_id > 0)) {
	$group_members = gs_group_members_get(Array($group_id));
?>
<table cellspacing="1">
<thead>
<tr>
	<th style="min-width:12em;"><?php echo __('Abschnitt'); ?></th>
	<th style="min-width:12em;width:18em;"><?php echo __('Modul'); ?></th>
	<th style="min-width:3em;"><?php echo __('Aktiv'); ?></th>
</tr>
</thead>
<tbody>
<form>
<?php
	echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
	echo '<input type="hidden" name="id" value="', (int)$group_id, '" />', "\n";
	echo '<input type="hidden" name="action" value="save" />', "\n";
	echo '<input type="hidden" name="page" value="', (int)$page, '" />', "\n";

	foreach ($MODULES as $section) {
		$i++;
		
		echo '<tr class="',($i%2===0?'odd':'even'),'">' ,"\n";
		echo '<td>',$section['title'],'</td>' ,"\n";
		echo '<td></td>',"\n";
		echo '<td>';
		echo '<input type="checkbox" ',(in_array($section['id'],$group_members) ? ' checked="yes" ' : ''),'name="m',$section['id'],'" value="1"  />';
		echo '</td>',"\n";
		echo '</tr>' ,"\n";
		
		if (array_key_exists('sub', $section))
		foreach ($section['sub'] as $module) {
			echo '<tr class="',($i%2===0?'odd':'even'),'">' ,"\n";
			echo '<td></td>',"\n";
			echo '<td>',$module['title'],'</td>' ,"\n";
			echo '<td>';
			echo '<input type="checkbox" ',(in_array($module['id'],$group_members) ? ' checked="yes" ' : ''),'name="m',$module['id'],'" value="1"  />';
			echo '</td>',"\n";
			echo '</tr>' ,"\n";
		}

	}
	echo '<tr>', "\n";
	echo '<td colspan="4" class="transp">&nbsp;</td>', "\n";
	echo '<td class="r transp">';
	echo '<input type="submit" value="', __('Speichern'), '" />';
	echo '</td>', "\n";
	echo '</tr>', "\n";

?>
</form>
</tbody>
</table>

<?php
}
else if ($action == '') {

	$groups = gs_group_info_get(false, 'module_gui');
	$sort_key = array();
	$per_page  = (int)gs_get_conf('GS_GUI_NUM_RESULTS', '15');
	$num_total = count($groups);
	$num_pages = ceil($num_total / $per_page);

	$num_sections = count($MODULES);
	$num_modules = 0;
	foreach ($MODULES as $module) {
		$num_modules = $num_modules + count(@$module['sub']);
	}
?>
<table cellspacing="1">
<thead>
<tr>
	<th style="min-width:12em;"><?php echo __('Gruppe'); ?></th>
	<th style="min-width:12em;width:18em;"><?php echo __('Titel'); ?></th>
	<th style="min-width:3em;"><?php echo __('Module'); ?></th>
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
			echo '<td class="r">',count(gs_group_members_get(Array($group['id']))),' / ',($num_sections+$num_modules), '</td>', "\n";
			echo '<td class="r">', "\n";

			echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=edit&amp;id='.$group['id'].'&amp;page='.$page) ,'"><img alt="', __('Bearbeiten') ,'" title="', __('Bearbeiten') ,'" src="', GS_URL_PATH ,'crystal-svg/16/act/edit.png" /></a>';

			echo '</td>', "\n";

			echo '</tr>' ,"\n";

			$i++;
		}

	}
?>
</tbody>
</table>
<?php
}
?>