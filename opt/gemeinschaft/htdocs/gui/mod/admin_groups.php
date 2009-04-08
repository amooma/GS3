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
require_once( GS_DIR .'lib/yadb/yadb_mptt.php' );
include_once( GS_DIR .'inc/gs-fns/gs_groups_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_group_change.php' );
include_once( GS_DIR .'inc/gs-fns/gs_group_del.php' );
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
if (! in_array($action, array('', 'save', 'delete'), true))
	$action = '';


#####################################################################
# save {
#####################################################################
if ($action === 'save') {
	foreach ($_REQUEST as $k => $v) {
		if (! preg_match('/^group-([0-9]+)-name$/', $k, $m))
			continue;
		$group_id       = (int)$m[1];
		$name           = trim(@$_REQUEST['group-'.$group_id.'-name']);
		if ($group_id == 0 && $name == '')
			continue;
		$name = preg_replace('/[^a-z0-9\-_]/', '', strToLower($name));
		$title          = trim(@$_REQUEST['group-'.$group_id.'-title']);
		$key_profile_id = (int)@$_REQUEST['group-'.$group_id.'-softkey_profile_id'];
		if ($key_profile_id < 1) $key_profile_id = null;
		$prov_param_profile_id = (int)@$_REQUEST['group-'.$group_id.'-prov_param_profile_id'];
		if ($prov_param_profile_id < 1) $prov_param_profile_id = null;
		$parent_id      = (int)@$_REQUEST['group-'.$group_id.'-parent_id'];
		if ($parent_id < 1) $parent_id = null;
		$show_ext_modules = (int)@$_REQUEST['group-'.$group_id.'-show_ext_modules'];
		
		$ret = gs_group_change( $group_id, $parent_id, $name, $title, $key_profile_id, $prov_param_profile_id, $show_ext_modules );
		if (isGsError($ret)) {
			echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
		} elseif (! $ret) {
			echo '<div class="errorbox">', sPrintF(__('Gruppe &quot;%s&quot; konnte nicht gespeichert werden.'), htmlEnt($name)) ,'</div>',"\n";
		}
	}
	
	if ( GS_BUTTONDAEMON_USE == true ) {
		gs_buttondeamon_usergroups_update();
	}
	
	$action = '';  # view
}
#####################################################################
# save }
#####################################################################

#####################################################################
# delete {
#####################################################################
if ($action === 'delete') {
	$group_id = (int)@$_REQUEST['id'];
	$ret = gs_group_del( $group_id );
	if (isGsError($ret)) {
		echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
	} elseif (! $ret) {
		echo '<div class="errorbox">', __('Gruppe konnte nicht gel&ouml;scht werden.') ,'</div>',"\n";
	}
	
	if ( GS_BUTTONDAEMON_USE == true ) {
		gs_buttondeamon_usergroup_remove( $group_id );
	}
	
	$action = '';  # view
}
#####################################################################
# delete }
#####################################################################

#####################################################################
# view {
#####################################################################
if ($action == '') {
?>

<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="save" />
<table cellspacing="1">
<thead>
<tr>
	<th style="min-width:12em;"><?php echo __('Gruppe'); ?></th>
	<th style="min-width:12em;width:18em;"><?php echo __('Titel'); ?></th>
	<th style="min-width:5em;"><?php echo __('Tastenprofil'); ?></th>
	<th style="min-width:5em;"><?php echo __('Prov.-Param.-Profil'); ?></th>
	<th style="min-width:5em;"><?php echo __('Erw.-Mod.'); ?><sup>[1]</sup></th>
	<th style="min-width:3em;">&nbsp;</th>
</tr>
</thead>
<tbody>
<?php

$groups = gs_groups_get();
if (isGsError($groups)) {
	echo '<tr><td colspan="5">', $groups->getMsg() ,'</td></tr>';
} else {
	$softkey_profiles = array();
	$rs = $DB->execute('SELECT `id`, `title` FROM `softkey_profiles` WHERE `is_user_profile`=0 ORDER BY `title`');
	while ($r = $rs->fetchRow()) {
		$softkey_profiles[$r['id']] = array('title'=>$r['title']);
	}
	
	$prov_param_profiles = array();
	$rs = $DB->execute('SELECT `id`, `title` FROM `prov_param_profiles` WHERE `is_group_profile`=1 ORDER BY `title`');
	while ($r = $rs->fetchRow()) {
		$prov_param_profiles[$r['id']] = array('title'=>$r['title']);
	}
	
	$i=0;
	$is_root = true;
	$root_level = 0;
	foreach ($groups as $node) {
		if ($is_root) {  # skip root node
			$root_level = $node['__mptt_level'];
			$is_root = false;
			continue;
		}
		echo '<tr class="',($i%2===0?'odd':'even'),'">' ,"\n";
		
		echo '<td class="l nobr">';
		echo @str_repeat('&nbsp;&nbsp;&nbsp;', $node['__mptt_level']-$root_level-1);
		echo '<img alt="&bull;" src="', GS_URL_PATH ,'img/tree.gif' ,'" />';
		echo '<input type="text" name="group-',$node['id'],'-name" value="', htmlEnt($node['name']) ,'" size="12" maxlength="20" />';
		echo '</td>' ,"\n";
		
		echo '<td>', '<input type="text" name="group-',$node['id'],'-title" value="', htmlEnt($node['title']) ,'" size="25" maxlength="50" style="width:96%;" />' ,'</td>' ,"\n";
		
		echo '<td>', "\n";
		echo '<select name="group-',$node['id'],'-softkey_profile_id">', "\n";
		echo '<option value=""';
		if ($node['softkey_profile_id'] < 1)
			echo ' selected="selected"';
		echo '>--</option>' ,"\n";
		foreach ($softkey_profiles as $profile_id => $profile) {
			echo '<option value="',$profile_id ,'"';
			if ($node['softkey_profile_id'] == $profile_id)
				echo ' selected="selected"';
			echo '>', htmlEnt($profile['title']) ,'</option>' ,"\n";
		}
		echo '</select>', "\n";
		echo '</td>', "\n";
		
		echo '<td>', "\n";
		echo '<select name="group-',$node['id'],'-prov_param_profile_id">', "\n";
		echo '<option value=""';
		if ($node['prov_param_profile_id'] < 1)
			echo ' selected="selected"';
		echo '>--</option>' ,"\n";
		foreach ($prov_param_profiles as $profile_id => $profile) {
			echo '<option value="',$profile_id ,'"';
			if ($node['prov_param_profile_id'] == $profile_id)
				echo ' selected="selected"';
			echo '>', htmlEnt($profile['title']) ,'</option>' ,"\n";
		}
		echo '</select>', "\n";
		echo '</td>', "\n";
		
		echo '<td>', "\n";
		echo '<select name="group-',$node['id'],'-show_ext_modules">', "\n";
		for ($num_e_m=0; $num_e_m<=3; ++$num_e_m) {
			echo '<option value="',$num_e_m ,'"';
			if ($node['show_ext_modules'] == $num_e_m)
				echo ' selected="selected"';
			echo '>', $num_e_m ,'</option>' ,"\n";
		}
		if ($node['show_ext_modules'] > 3 && $node['show_ext_modules'] < 255)
			echo '<option value="', $node['show_ext_modules'] ,'" selected="selected">', $node['show_ext_modules'] ,'</option>' ,"\n";
		echo '<option value="255"';
		if ($node['show_ext_modules'] == 255)
			echo ' selected="selected"';
		echo '>', __('alle') ,'</option>' ,"\n";
		echo '</select>', "\n";
		echo '</td>', "\n";
		
		echo '<td class="r">', "\n";
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=delete&amp;id='.$node['id']) ,'" onclick="return confirm_delete();"><img alt="', __('L&ouml;schen') ,'" title="', __('L&ouml;schen') ,'" src="', GS_URL_PATH ,'crystal-svg/16/act/editdelete.png" /></a>';
		echo '</td>', "\n";
		
		echo '</tr>' ,"\n";
		++$i;
	}
	
	echo '<tr>' ,"\n";
	
	echo '<td colspan="5" class="transp">&nbsp;</td>', "\n";
	
	echo '<td class="transp r">', "\n";
	echo '<button type="submit" class="plain" title="', __('Speichern') ,'"><img alt="', __('Speichern') ,'" src="', GS_URL_PATH ,'crystal-svg/16/act/filesave.png" /></button>';
	echo '</td>' ,"\n";
	
	echo '</tr>' ,"\n";
	
	$i=0;
	echo '<tr class="',($i%2===0?'odd':'even'),'">' ,"\n";
	
	echo '<td class="l nobr">';
	echo __('Neue Gruppe') ,'<br />';
	echo '<input type="text" name="group-0-name" value="" size="12" maxlength="20" /><br />';
	echo __('als Kind von') ,'<br />',"\n";
	echo '<select name="group-0-parent_id">' ,"\n";
	$is_root = true;
	$root_level = 0;
	foreach ($groups as $node) {
		if ($is_root) {
			$root_level = $node['__mptt_level'];
		}
		echo '<option value="', $node['id'] ,'">';
		echo @str_repeat('&nbsp;&nbsp;&nbsp;', $node['__mptt_level']-$root_level);
		if ($is_root) {
			echo __('Wurzel-Gruppe');
			$is_root = false;
		} else {
			echo htmlEnt($node['name']);
		}
		echo '</option>' ,"\n";
	}
	echo '</select>' ,"\n";
	echo '</td>' ,"\n";
	
	echo '<td>&nbsp;<br /><input type="text" name="group-0-title" value="" size="25" maxlength="50" style="width:96%;" /></td>' ,"\n";
	
	echo '<td>&nbsp;<br />', "\n";
	echo '<select name="group-0-softkey_profile_id">', "\n";
	echo '<option value="" selected="selected">--</option>' ,"\n";
	foreach ($softkey_profiles as $profile_id => $profile) {
		echo '<option value="',$profile_id ,'"';
		echo '>', htmlEnt($profile['title']) ,'</option>' ,"\n";
	}
	echo '</select>', "\n";
	echo '</td>', "\n";
	
	echo '<td>&nbsp;<br />', "\n";
	echo '<select name="group-0-prov_param_profile_id">', "\n";
	echo '<option value="" selected="selected">--</option>' ,"\n";
	foreach ($prov_param_profiles as $profile_id => $profile) {
		echo '<option value="',$profile_id ,'"';
		echo '>', htmlEnt($profile['title']) ,'</option>' ,"\n";
	}
	echo '</select>', "\n";
	echo '</td>', "\n";
	
	echo '<td>&nbsp;<br />', "\n";
	echo '<select name="group-0-show_ext_modules">', "\n";
	for ($num_e_m=0; $num_e_m<=3; ++$num_e_m) {
		echo '<option value="',$num_e_m ,'"';
		echo '>', $num_e_m ,'</option>' ,"\n";
	}
	echo '<option value="255" selected="selected">', __('alle') ,'</option>',"\n";
	echo '</select>', "\n";
	echo '</td>', "\n";
	
	echo '<td class="r">&nbsp;<br />', "\n";
	echo '<button type="submit" class="plain" title="', __('Speichern') ,'"><img alt="', __('Speichern') ,'" src="', GS_URL_PATH ,'crystal-svg/16/act/filesave.png" /></button>';
	echo '</td>', "\n";
	
	echo '</tr>' ,"\n";
	++$i;
}
?>
</tbody>
</table>
</form>

<?php
	echo '<br /><br />',"\n";
	echo '<small>DB-Analyse' ,':',"\n";
	$mptt = new YADB_MPTT($DB, 'user_groups', 'lft', 'rgt', 'id');
	echo 'MPTT-Struktur ist ', ($mptt->quick_sanity_check() ? 'in Ordnung' : 'FEHLERHAFT') ,'</small><br />',"\n";
	echo '<br />',"\n";
	echo '<p class="text small"><sup>[1]</sup> ', __('Bestimmt wieviele Telefon-Erweiterungsmodule/-Beistellmodule in der Benutzer-Tastenbelegungsmaske angezeigt werden.') ,'</p>' ,"\n";
}
#####################################################################
# view }
#####################################################################


?>