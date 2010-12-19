<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2010, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
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
require_once( GS_DIR .'inc/mongr.php');
include_once( GS_DIR .'inc/gs-fns/gs_callforward_get.php');

define( 'GS_EXT_UNKNOWN',	255); # unknown status
define( 'GS_EXT_IDLE',		0);  # all devices idle (but registered)
define( 'GS_EXT_INUSE',		1);  # one or more devices busy
define( 'GS_EXT_BUSY',		2);  # all devices busy
define( 'GS_EXT_OFFLINE',	4);  # all devices unreachable/not registered
define( 'GS_EXT_RINGING',	8);  # one or more devices ringing
define( 'GS_EXT_RINGINUSE',	9);  # ringing and in use
define( 'GS_EXT_ONHOLD',	16); # all devices on hold
define( 'GS_EXT_FWD',		32); # forwarding active

define( 'ST_FREE',		'#fffd3b');
define( 'ST_INUSE',		'#00fd02');
define( 'ST_OFFLINE',		'#fdfeff');
define( 'ST_RINGING',		'#008000');
define( 'ST_RINGINUSE',		'#0080FF');
define( 'ST_UNKNOWN',		'#ff5c43');
define( 'ST_ONHOLD',		'#fd02fd');
define( 'ST_FWD',		'#0002fd');

define( 'CQ_DESKTOP_BG',	'#ffffff');
define( 'CQ_WINDOW_BG',		'#d4d0c7');
define( 'CQ_WINDOW_FG',		'#000000');

$colors = array(
	GS_EXT_UNKNOWN		=> ST_UNKNOWN,
	GS_EXT_IDLE		=> ST_FREE,
	GS_EXT_INUSE		=> ST_INUSE,
	GS_EXT_BUSY		=> ST_INUSE,
	GS_EXT_OFFLINE		=> ST_OFFLINE,
	GS_EXT_RINGING		=> ST_RINGING,
	GS_EXT_RINGINUSE	=> ST_RINGINUSE,
	GS_EXT_ONHOLD		=> ST_ONHOLD,
	GS_EXT_FWD		=> ST_FWD
);

function _secs_to_minsecs( $s )
{
	$s = (int)$s;
	$m = floor($s/60);
	$s = $s - $m*60;
	return $m .':'. str_pad($s, 2, '0', STR_PAD_LEFT);
}



function html_select($name, $items, $default = 0) {
       
       echo '<select name="'.$name.'" onChange="checkval(this)">', "\n";
       foreach ($items as $title => $value) {
		if ($value == $default)
			echo '<option value="',$value,'" selected="selected">',$title,'</option>' ,"\n";
		else
			echo '<option value="',$value,'">',$title,'</option>' ,"\n";
       }
       echo '</select>', "\n";

}

function group_defaults(&$group, $fill = False) {

	if (!is_array($group))  $group = array();

	$group['active']		= True;
	$group['display_columns']	= 3;
	$group['display_width']		= 550;
	$group['display_height']	= 185;
	$group['display_all']		= 0;
	$group['display_comment']	= 0;
	$group['display_forw']		= 0;
	$group['display_extension']	= 2;
	$group['display_name']		= 4;
	$group['members']		= array();
	
       if ($fill) {
		foreach ($fill as $key => $value) {
			if ((string)$value != '') {
				$group[$key] = $value;
			}
		}
       }
}

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

$action = @$_REQUEST['action'];
$tab = (int) @$_REQUEST['tab'];
$user_id = @$_SESSION['sudo_user']['info']['id'];

if (!$user_id) exit;

$user_groups  = gs_group_members_groups_get(Array($user_id), 'user');
$monitor_group_ids = gs_group_permissions_get($user_groups, 'monitor_peers', 'user');

if ($action == 'save') {

	$colors = array();
       
	for ($status = 0; $status <= 255; ++$status) {
		if (array_key_exists('ec'.$status, $_REQUEST)) {
			$colors[$status] = trim($_REQUEST['ec'.$status]);
		}
	}

	$groups = array();

	foreach ($monitor_group_ids as $group_id) {
		$group = array();

		if (array_key_exists('qa'.$group_id, $_REQUEST) && $_REQUEST['qa'.$group_id] == "on")
			$group['active'] = 1;
		else
			$group['active'] = 0;

		if (array_key_exists('qw'.$group_id, $_REQUEST))
			$group['display_width'] = (int)$_REQUEST['qw'.$group_id];
		if (array_key_exists('qh'.$group_id, $_REQUEST))
			$group['display_height'] = (int)$_REQUEST['qh'.$group_id];
		if (array_key_exists('qx'.$group_id, $_REQUEST))
			$group['display_columns'] = (int)$_REQUEST['qx'.$group_id];

		if (array_key_exists('qcx'.$group_id, $_REQUEST))
			$group['display_extension'] = (int)$_REQUEST['qcx'.$group_id];
		if (array_key_exists('qcn'.$group_id, $_REQUEST))
			$group['display_name'] = (int)$_REQUEST['qcn'.$group_id];
		if (array_key_exists('qcf'.$group_id, $_REQUEST))
			$group['display_forw'] = (int)$_REQUEST['qcf'.$group_id];
		if (array_key_exists('qcc'.$group_id, $_REQUEST))
			$group['display_comment'] = (int)$_REQUEST['qcc'.$group_id];
		$groups[$group_id] = $group;
	}

	$page = array();

	if (array_key_exists('qx', $_REQUEST))
			$page['columns'] = (int)$_REQUEST['qx'];
	if (array_key_exists('qu', $_REQUEST))
			$page['update'] = (int)$_REQUEST['qu'];
	if (array_key_exists('qr', $_REQUEST))
			$page['reload'] = (int)$_REQUEST['qr'];
	if (array_key_exists('qt', $_REQUEST))
			$page['columns'] = 0;

	if ($page) {
		$sql_query =
		'REPLACE `monitor`
		(`user_id`, `type`, `'.implode('`,`', array_keys($page)).'`)
		VALUES ('.$user_id.', 2,'.implode(',', $page).')';

		$rs = $DB->execute( $sql_query );
	}

	if ($colors) {
		foreach( $colors as $key => $value ) {
			$sql_query =
			'REPLACE `monitor_colors`
			(`user_id`, `type`, `status`, `color`)
			VALUES ('.$user_id.', 2,'.$key.',\''.$value.'\')';

			$rs = $DB->execute( $sql_query );
		}
        }

	if ($groups) {
		foreach( $groups as $group_id => $group ) {
			$sql_query =
			'REPLACE `monitor_groups`
			(`user_id`, `group_id`, `'.implode('`,`', array_keys($group)).'`)
			VALUES ('.$user_id.','.$group_id.','.implode(',', $group).')';

			$rs = $DB->execute( $sql_query );
		}
	}

	$action = "edit";
}

$sql_query =
'SELECT `display_x`, `display_y`, `columns`, `update`, `reload`
FROM `monitor`
WHERE `user_id` = '.$user_id.'
AND `type` = 2
LIMIT 1';

$rs = $DB->execute( $sql_query );

$extmon_data = array();
$extmon_data['columns'] = 2;
$extmon_data['update'] = 2;
$extmon_data['reload'] = 120;
$extmon_data['tabs'] = False;
$extmon_data['tabs_groups'] = array();

if ($rs->numRows() == 1) {
	$r = $rs->fetchRow();
	foreach ($r as $key => $value) {
		if ($value !== NULL) {
			if ($key == 'columns' && $value == 0) {
				$extmon_data[$key] = 1;
				$extmon_data['tabs'] = True;
			} else $extmon_data[$key] = $value;
		}
	}
}

$groups = array();
$groups_array = gs_group_info_get($monitor_group_ids);

foreach ($groups_array as $group) {
	group_defaults($group);
	$group['memberids'] = gs_group_members_get(array($group['id']));
	$groups[$group['id']] = $group;
}

unset($groups_array);

$sql_query =
'SELECT
`m`.`group_id` `id`,
`m`.`active`,
`m`.`display_columns`,
`m`.`display_width`,
`m`.`display_height`,
`m`.`display_extension`,
`m`.`display_name`,
`m`.`display_forw`,
`m`.`display_comment`
FROM `monitor_groups` `m`
WHERE `m`.`user_id` = '.$user_id;

$rs = $DB->execute( $sql_query );

if ($rs) {
	while ($r = $rs->fetchRow()) {
		group_defaults($groups[$r['id']], $r);
	}
}

if ($extmon_data['tabs'] === True) {
	foreach ($groups as $group_id => $group_data) {
		if ($group_data['active']) {
			$extmon_data['tabs_groups'][$group_id]=$group_data['title'];
			if ($tab == 0) $tab = $group_id;
		}
	}
}

$color_names = array(
	GS_EXT_UNKNOWN 		=> _('unbekannt'),
	GS_EXT_IDLE		=> _('frei'),
	GS_EXT_INUSE		=> _('anruf'),
	GS_EXT_BUSY		=> _('besetzt'),
	GS_EXT_OFFLINE		=> _('offline'),
	GS_EXT_RINGING		=> _('klingelt'),
	GS_EXT_RINGINUSE	=> _('anklopfen'),
	GS_EXT_ONHOLD		=> _('halten'),
	GS_EXT_FWD		=> _('Umleitung aktiv')
);

$colors = array(
	GS_EXT_UNKNOWN		=> ST_UNKNOWN,
	GS_EXT_IDLE		=> ST_FREE,
	GS_EXT_INUSE		=> ST_INUSE,
	GS_EXT_BUSY		=> ST_INUSE,
	GS_EXT_OFFLINE		=> ST_OFFLINE,
	GS_EXT_RINGING		=> ST_RINGING,
	GS_EXT_RINGINUSE	=> ST_RINGINUSE,
	GS_EXT_ONHOLD		=> ST_ONHOLD,
	GS_EXT_FWD		=> ST_FWD
);

$sql_query =
'SELECT `status`, `color`
FROM `monitor_colors`
WHERE `user_id` = '.$user_id.'
AND `type` = 2';

$rs = $DB->execute( $sql_query );

if ($rs) {
	while ($r = $rs->fetchRow()) {
		$colors[$r['status']] = $r['color'];
	}
}

if ($action == 'edit') {
	
	$qh_val = 1;
?>

<script type="text/javascript">

function changebgcolor(color_id, color_value) {
	var el_obj = document.getElementById(color_id);
	el_obj.style.background = color_value;
}

function checkval(el_obj) {
	if (el_obj.name == 'qx') {
		var input_value = parseInt( el_obj.value );
		if ((input_value > -1) && (input_value < 11)) {
			el_obj.value = input_value;
		} else {
			el_obj.value = 2;
		}
		return 1;
	}

	if (el_obj.name == 'qu') {
		var input_value = parseInt( el_obj.value );
		if ((input_value > 0) && (input_value < 3600)) {
			el_obj.value = input_value;
		} else {
			el_obj.value = 4;
		}
		return 1;
	}

	if (el_obj.name == 'qr') {
		var input_value = parseInt( el_obj.value );
		if ((input_value > 0) && (input_value < 3600)) {
			el_obj.value = input_value;
		} else {
			el_obj.value = 2;
		}
		return 1;
	}

	if (el_obj.name.substr(0,2) == 'qw') {
		var input_value = parseInt( el_obj.value );
		if ((input_value > 20) && (input_value < 2049)) {
			el_obj.value = input_value;
		} else {
			el_obj.value = 550;
		}
		return 1;
	}

	if (el_obj.name.substr(0,2) == 'qh') {
		var input_value = parseInt( el_obj.value );
		if ((input_value > 20) && (input_value < 1025)) {
			el_obj.value = input_value;
		} else {
			el_obj.value = 160;
		}
		return 1;
	}

	if (el_obj.name.substr(0,2) == 'qx') {
		var input_value = parseInt( el_obj.value );
		if ((input_value > 0) && (input_value < 11)) {
			el_obj.value = input_value;
		} else {
			el_obj.value = 2;
		}
		return 1;
	}

}

function screenres() {
	var el_obj = document.getElementById('screen_res');
	el_obj.innerHTML = screen.width+"x"+screen.height;
}

function page_init() {
	screenres();
}

window.onload=page_init

</script>

<?php
	echo '<form name="edit" method="post" action="?f=0">',"\n";
	echo gs_form_hidden($SECTION, $MODULE);
	echo '<input type="hidden" name="action" value="save" />' ,"\n";

	if (@$extmon_data['tabs'] === True) $active = 'checked';
	else $active = '';
?>

<table cellspacing="1">
<thead>
	<tr>
		<th style="min-width:24em;" colspan="3">
			Queue Monitor <?php echo __('Anzeigeoptionen'); ?>
		</th>
	</tr>
</thead>
<tbody>
	<tr>
		<th style="min-width:24em;" colspan="3">
			<?php echo __('Seite'); ?>
		</th>
	</tr>
	<tr>
		<td style="width:1em;"></td>
		<th style="min-width:12em;">
			<?php echo __('Aufl&ouml;sung'); ?>
		</th>
		<td style="min-width:12em;">
			<span id="screen_res"></span>
			<?php echo __('Pixel'); ?>
		</td>
	</tr>
	<tr>
		<td style="width:1em;"></td>
		<th style="min-width:12em;">
			 <?php echo __('Tabs benutzen'); ?>
		</th>
		<td style="min-width:12em;">
		<input type="checkbox" name="qt" <?php echo $active; ?> />
		</td>
	</tr>
	<tr>
		<td style="width:1em;"></td>
		<th style="min-width:12em;">
			&#8660; <?php echo __('Horiz. Gruppen'); ?>
		</th>
		<td style="min-width:12em;">
		<input type="text" onChange="checkval(this)" name="qx" value="<?php echo $extmon_data['columns']; ?>" size="2" maxlength="2" />
		</td>
	</tr>
	<tr>
		<td style="width:1em;"></td>
		<th style="min-width:12em;">
			&#10226; <?php echo __('Update Interval'); ?>
		</th>
		<td style="min-width:12em;">
		<input type="text" onChange="checkval(this)" name="qu" value="<?php echo $extmon_data['update']; ?>" size="2" maxlength="4" />
		<?php echo __('Sec.'); ?>
		</td>
	</tr>
		<tr>
		<td style="width:1em;"></td>
		<th style="min-width:12em;">
			&#10227; <?php echo __('Reload Interval'); ?>
		</th>
		<td style="min-width:12em;">
		<input type="text" onChange="checkval(this)" name="qr" value="<?php echo $extmon_data['reload']; ?>" size="2" maxlength="4" />
		<?php echo __('Min.'); ?>
		</td>
	</tr>
	<tr>
		<th id="colors" style="min-width:12em;" colspan="3">
			<?php echo __('Farben'); ?>
		</th>
	</tr>
<?php
	foreach ($colors as $color_id => $value) {
		echo '<tr>',"\n";
		echo '<td></td>',"\n";
		echo '<th style="min-width:12em; background:',$value,';">';
		echo $color_names[$color_id];
		echo '</th>',"\n";
		echo '<td id="ec_'.$color_id.'" style="min-width:12em; background:',$value,';">';
		echo '<input type="text" onKeyup="changebgcolor(\'ec_'.$color_id.'\', this.value)" name="ec'.$color_id.'" value="'.$value.'" size="10" maxlength="10"" />';
		echo '</td>',"\n";
		echo '</tr>',"\n";
	}
?>

</tbody>
</table>

<table cellspacing="1">
<thead>
	<tr>
		<th style="min-width:24em;" colspan="2">
			<?php echo __('Benutzergruppen'); ?>
		</th>
		<th style="min-width:2em;" colspan="2">
			<?php echo __('Gr&ouml;&szlig;e'); ?>
		</th>
		<th style="min-width:2em;" colspan="1">
			<?php echo __('Nebenstellen'); ?>
		</th>
		<th style="min-width:2em;" colspan="4">
			<?php echo __('Benutzerdaten'); ?>
		</th>
	</tr>
</thead>
<tbody>
	<tr>
		<th style="width:1em;" colspan="1"></th>
		<th style="min-width:2em;" colspan="1">
			<?php echo __('Gruppe'); ?>
		</th>
		<th style="width:2em;" colspan="1">
			&#8660; <?php echo __('Pixel'); ?>
		</th>
		<th style="width:2em;" colspan="1">
			&#8661; <?php echo __('Pixel'); ?>
		</th>
		<th style="width:2em;" colspan="1">
			&#8660;
		</th>
		<th style="width:2em;" colspan="1">
			&#9742; <?php echo __('Nebenstelle'); ?>
		</th>
		<th style="width:2em;" colspan="1">
			&#8943; <?php echo __('Namensanzeige'); ?>
		</th>
		<th style="width:2em;" colspan="1">
			 <?php echo __('Kommentar'); ?>
		</th>
		<th style="width:2em;" colspan="1">
			&#8626; <?php echo __('Rufumleitung'); ?>
		</th>
	</tr>
<?php
	foreach ($groups as $group => $data) {
		echo '<tr>',"\n";
		echo '<td>';
		if ($data['active']) $active = ' checked ';
		else $active = '';
		echo '<input type="checkbox" name="qa'.$data['id'].'".'.$active.' />';
		echo '</td>',"\n";
		echo '<th style="min-width:12em;">';
		echo htmlEnt($data['title']);
		echo '</th>',"\n";
		echo '<td>';
		echo '<input type="text" onChange="checkval(this)" name="qw'.$data['id'].'" value="'.$data['display_width'].'" size="4" maxlength="4" />';
		echo '</td>',"\n";
		echo '<td>';
		echo '<input type="text" onChange="checkval(this)" name="qh'.$data['id'].'" value="'.$data['display_height'].'" size="3" maxlength="3" />';
		echo '</td>',"\n";
		echo '<td>';
		echo '<input type="text" onChange="checkval(this)" name="qx'.$data['id'].'" value="'.$data['display_columns'].'" size="2" maxlength="2" />';
		echo '</td>',"\n";
		echo '<td>';
		$items = array(
			'--' => 0,
			_('normal') => 1,
			_('fett') => 2
		);
		echo html_select('qcx'.$data['id'], $items, $data['display_extension']);
		echo '</td>',"\n";
		echo '<td>';
		$items = array(
			'--' => 0,
			_('Name') => 1,
			_('Vorname') => 2,
			_('Vorname Name') => 3,
			_('Name, Vorname') => 4,
			_('Name, V.') => 5,
			_('V. Name') => 6,
			_('V.N.') => 7,
			
		);
		echo html_select('qcn'.$data['id'], $items, $data['display_name']);
		echo '</td>',"\n";

		echo '<td>';
		$items = array(
			'--' => 0,
			_('normal') => 1,
			_('kursiv') => 2,
			_('fett') => 3,
			_('fett+kursiv') => 4,
			_('gek&uuml;rzt') => 5,
		);
		echo html_select('qcc'.$data['id'], $items, $data['display_comment']);
		echo '</td>',"\n";

		echo '<td>';
		$items = array(
			'--' => 0,
			_('Intern') => 1,
			_('eXtern') => 2,
			_('Intern+eXtern') => 3
		);
		echo html_select('qcf'.$data['id'], $items, $data['display_forw']);
		echo '</td>',"\n";
		echo '</tr>',"\n";
	}
?>
</tbody>
</table>
<table>
<table>
<thead>
<th colspan="4" style="height: 1em;"> </th>
</thead>
<tbody>
<tr>
</tr>
<tr>
<td colspan="1">
<button type="submit" class="plain" title="<?php echo __('Speichern') ?>"><img alt="save" src="<?php echo GS_URL_PATH ,'crystal-svg/16/act/filesave.png'; ?>" /></button>
</td>
<th colspan="1"><?php echo __('Speichern');?></th>
<td colspan="1">
<a href="?f=0" title="<?php echo __('Zur&uuml;ck') ?>"><img alt="back" src="<?php echo GS_URL_PATH ,'crystal-svg/16/act/cancel.png'; ?>" /></a>
</td>
<th colspan="1"><?php echo __('Zur&uuml;ck');?></th>

</tr>
</tbody>
</table>
</form>

<?php
}

if ($action == "") {

	$fullscreen = (int) @$_REQUEST['f'];
	
	if ($extmon_data['tabs'] === True)
		foreach ($groups as $group => $group_data) {
			if ($group_data['id'] != $tab) {
				unset($groups[$group]);
			}
		}

	$peers = array();
	foreach ($groups as $group => $group_data) {
		if (!array_key_exists('memberids', $group_data)) continue;
		if (count($group_data['memberids']) == 0) continue;

		$sql_query =
		'SELECT
		`s`.`name` `ext`,
		`u`.`firstname` `firstname`,
		`u`.`lastname` `lastname`,
		`u`.`user_comment` `user_comment`,
		`u`.`id` `id`,
		`h`.`host` `host`
		FROM `ast_sipfriends` `s`
		JOIN `users` `u`
		ON (`u`.`id` = `s`.`_user_id`)
		JOIN `hosts` `h` ON (`h`.`id`=`u`.`host_id`)
		WHERE `u`.`id` IN ( '.implode(',',$group_data['memberids']).')';

		

		$rs = $DB->execute( $sql_query );
		$group_members = array();
		if ($rs) {
			while ($r = $rs->fetchRow()) {
				$member = array();
				if (!array_key_exists($r['ext'], $peers)) {
					$peers[$r['ext']] = array();
					$peers[$r['ext']]['callfw'] = gs_callforward_get_by_uid($r['id']);
				}
				if (!array_key_exists('groups', $peers[$r['ext']])) {
					$peers[$r['ext']]['groups'] = array();
				}
				
				$peers[$r['ext']]['host'] = $r['host'];
				$peers[$r['ext']]['groups'][$group] = True;
				$member['name'] = '';
				if (array_key_exists('display_extension',$groups[$group])) {
					if ($groups[$group]['display_extension'] == 1)
						$member['name'] .= htmlEnt($r['ext']).' ';
					if ($groups[$group]['display_extension'] == 2)
						$member['name'] .= '<big><b>'.htmlEnt($r['ext']).'</b></big> ';
				}
				if (array_key_exists('display_name',$groups[$group])) {
					if ($groups[$group]['display_name'] == 1)
						$member['name'] .= htmlEnt($r['lastname']);
					if ($groups[$group]['display_name'] == 2)
						$member['name'] .= htmlEnt($r['firstname']);
					if ($groups[$group]['display_name'] == 3)
						$member['name'] .= htmlEnt($r['firstname']).' '.htmlEnt($r['lastname']);
					if ($groups[$group]['display_name'] == 4)
						$member['name'] .= htmlEnt($r['lastname']).', '.htmlEnt($r['firstname']);
					if ($groups[$group]['display_name'] == 5)
						$member['name'] .= htmlEnt($r['lastname']).', '.htmlEnt(substr($r['firstname'],0,1)).'.';
					if ($groups[$group]['display_name'] == 6)
						$member['name'] .= htmlEnt(substr($r['firstname'],0,1)).'. '.htmlEnt($r['lastname']);
					if ($groups[$group]['display_name'] == 7)
						$member['name'] .= htmlEnt(substr($r['firstname'],0,1)).'.'.htmlEnt(substr($r['lastname'],0,1)).'.';
				}
				$display_right = '';

				if (array_key_exists('display_comment',$groups[$group])) {
					if ($r['user_comment']) {
						if ($groups[$group]['display_comment'] == 1) {
							$display_right = htmlEnt($r['user_comment']);
						}
						else if ($groups[$group]['display_comment'] == 2) {
							$display_right = '<i>'.htmlEnt($r['user_comment']).'</i>';
						}
						else if ($groups[$group]['display_comment'] == 3) {
							$display_right = '<b>'.htmlEnt($r['user_comment']).'</b>';
						}
						else if ($groups[$group]['display_comment'] == 4) {
							$display_right = '<i><b>'.htmlEnt($r['user_comment']).'</b></i>';
						}
						else if ($groups[$group]['display_comment'] == 5) {
							$max_chars = ($groups[$group]['display_width'] / $groups[$group]['display_columns']) / 10;
							$display_right = '<i>'.htmlEnt(substr($r['user_comment'],0,$max_chars)).'</i>';
						}
					}
				}
				
				if (array_key_exists('display_forw',$groups[$group])) {
					$ext_active = $peers[$r['ext']]['callfw']['external']['always']['active'];
					$int_active = $peers[$r['ext']]['callfw']['internal']['always']['active'];
					$ext_fwdnr  = @$peers[$r['ext']]['callfw']['external']['always']['number_'.$ext_active];
					$int_fwdnr  = @$peers[$r['ext']]['callfw']['internal']['always']['number_'.$int_active];
					
					if ($groups[$group]['display_forw'] == 1) {
						if ($int_active == 'par' )
							$display_right .= '&#8649;';
						else if ($int_active == 'vml' )
							$display_right .= '&#9993;';
						else if ($int_active != 'no' )
							$display_right .= '&#8627;';
					}
					else if ($groups[$group]['display_forw'] == 2) {
						if ($ext_active == 'par' )
							$display_right .= '&#8649;';
						else if ($ext_active == 'vml' )
							$display_right .= '&#9993;';
						else if ($ext_active != 'no')
							$display_right .= '&#8627;';
					}
					else if ($groups[$group]['display_forw'] == 3) {
						if ($int_active == 'par' && $ext_active == 'par' ) $display_right .= '&#8649;';
						else
						if ($int_active == 'vml' && $ext_active == 'vml' ) $display_right .= '&#9993;';
						else
						if (($int_active == 'std' || $int_active == 'var') && ($ext_active == 'std' || $ext_active == 'var')) $display_right .= '&#8627;';
						else {
							if ($int_active != 'no') $display_right .= '&#8614;';
							if ($int_active == 'par') $display_right .= '&#8649;';
							if ($int_active == 'vml') $display_right .= '&#9993;';
							if ($ext_active != 'no') $display_right .= '&#8677;';
							if ($ext_active == 'par') $display_right .= '&#8649;';
							if ($ext_active == 'vml') $display_right .= '&#9993;';
						}
					}

					if ($display_right) $member['name'] .= '<span style="float:right;">'.$display_right.'</span>';
				}
				
				$member['ext'] = $r['ext'];
				$group_members[] = $member;
			}
		}
		$groups[$group]['members'] = $group_members;
	}


	$extmon_data['extensions'] = $peers;
	$extmon_data['colors'] = $colors;
	$_SESSION['extmon'] = $extmon_data;

?>
<script type="text/javascript">

var http = false;
var counter = 0;
var counter_fail = 0;
var timestamp = 0;
var colors = new Array();
var members = new Array();
var callfw = new Array();
var progress = new Array('&#9676;', '&#9684;', '&#9681;', '&#9685;', '&#9673;');

<?php
	foreach ($colors as $key => $value) {
	echo "colors['$key'] = '$value';\n";
	}
	
	foreach ($peers as $peer => $peer_data) {
		$groups_str = '\''.implode('\',\'', array_keys($peer_data['groups'])).'\'';
		echo "members['$peer'] = new Array($groups_str);\n";

		$callfw = 0;
		if ($peer_data['callfw']['external']['always']['active'] != 'par' &&
			$peer_data['callfw']['external']['always']['active'] != 'no') {
			$callfw = 1;
		}
		if ($peer_data['callfw']['internal']['always']['active'] != 'par' &&
			$peer_data['callfw']['internal']['always']['active'] != 'no') {
			$callfw = 1;
		}
		
		echo "callfw['$peer'] = ".$callfw.";\n";
	}
	echo 'var status_url = \'', GS_URL_PATH, 'srv/extensionstatus.php?t=\';';
?>


function clickedfield(element) {
	element.style.background="red";
}

if(navigator.appName == "Microsoft Internet Explorer") {
	http = new ActiveXObject("Microsoft.XMLHTTP");
} else {
	http = new XMLHttpRequest();
}

function to_array( )
{
	var arr = new Object( );
	for (var arg = 0; arg < arguments.length; ++arg )
	{
		arr[arguments[arg][0]] = arguments[arg][1];
	}
	return arr;
}

function read_data()
{
	if(http.readyState == 4) {
		var ret_arr = eval('to_array('+http.responseText+')');
		for (var key in ret_arr) {
			if (key == "ERROR_TEXT") {
				document.getElementById('server_info').innerHTML = ret_arr[key];
			} else {
				counter_fail = 0;
				if (key.charAt(0) == "a") {
					var exten = key.substring(1)
					if (exten in members) {
						for ( var queue in members[exten]) {
							var el_obj = document.getElementById('q'+members[exten][queue]+'_'+key);
							if (el_obj) {
								if ((ret_arr[key] == 0 || ret_arr[key] == 4) && (callfw[exten] != 0))
									el_obj.style.background = colors[32];
								else
									el_obj.style.background = colors[ret_arr[key]];
							}
						}
					}
				}
				else if (key.charAt(0) == "c") {
					var queue = key.substring(1)
					var el_obj = document.getElementById('q'+queue+'_calls');
					if (el_obj) el_obj.innerHTML = ret_arr[key];
				}
				document.getElementById('server_info').innerHTML = "&#10027; " + progress[(counter % 5)];
			}
		}
		if (!ret_arr)
			document.getElementById('server_info').innerHTML = "NO DATA";
	} else {
		++counter_fail;
		if (counter_fail >= 10) {
			document.getElementById('server_info').innerHTML = "NOT CONNECTED";
			for ( var exten in members) {
				for ( var queue in members[exten]) {
					var el_obj = document.getElementById('q'+members[exten][queue]+'_a'+exten);
					if (el_obj != null) el_obj.style.background = colors[255];
				}
			}
			counter_fail = 11;
			
		}
	}
}

function get_data(request_type)
{
	document.getElementById('server_info').innerHTML = "&#10026; " + progress[(counter % 5)];
	
	http.abort();
	http.open("GET", status_url+request_type, true);
	http.onreadystatechange=read_data
	http.send(null);
	timestamp = 0;
}

function data_reload()
{
	var timer=setTimeout("data_reload()", <?php echo $extmon_data['update'] * 1000 ?>)
	if (counter >= <?php echo ($extmon_data['reload'] * ceil(60 / $extmon_data['update'])); ?>) {
		location.reload(true);
		counter = 0;
	} else {
		get_data("m");
		++counter;
	}
}

window.onload=data_reload

</script>


<?php
	echo '<span id="server_info">';
	echo '</span>', "\n";
	if ($fullscreen) {
	echo '<div id="chart" style="position:absolute; left:0px; right:0px; top:0px; bottom:0px; overflow:scroll; border:0px solid #ccc; background:#fff;">'."\n";
	} else {
		echo '<a href="?f=1"><img id="chart-fullscreen-toggle" class="fr" style="cursor:pointer; margin:0 1px 1px 0;" title="Fullscreen" alt="Fullscreen" src="'.GS_URL_PATH.'crystal-svg/16/act/window_fullscreen.png" /></a>'."\n";
		echo '<a href="?action=edit"><img id="chart-edit" class="fr" style="cursor:pointer; margin:0 1px 1px 0;" title="Edit" alt="Edit" src="'.GS_URL_PATH.'crystal-svg/16/act/edit.png" /></a>'."\n";
		echo '<div id="chart" style="position:absolute; left:189px; right:12px; top:12em; bottom:10px; overflow:scroll; border:0px solid #ccc; background:#fff;">'."\n";
	}

	$bg_color = CQ_DESKTOP_BG;
	$fg_color = CQ_WINDOW_FG;

	$qh = 1;
	$qx = 0;
	$qy = 0;
	$qmaxh = 0;

	if ($fullscreen) $fullscreen_str='&f=1';
	echo '<table>'."\n";
	echo '<tr>'."\n";
	
	foreach ($extmon_data['tabs_groups'] as $group_id => $group_title) {
		if ($tab == $group_id) {
			echo '<td class="grouptabsactive">'."\n";
			echo '<a href="?tab='.$group_id.$fullscreen_str.'">'.$group_title.'</a>'."\n";
		} else { 
			echo '<td class="grouptabs">'."\n";
			echo '<a href="?tab='.$group_id.$fullscreen_str.'">'.$group_title.'</a>'."\n";
		}
		echo '</td>'."\n";
	}
	
	echo '</tr>'."\n";
	echo '</table>'."\n";
	if ($extmon_data['tabs'] === True && array_key_exists($tab, $groups)) {
		$group_data = $groups[$tab];
		$st_y = $st_y + 20;
		group_window($st_x,$st_y,$group_data['display_width'],$group_data['display_height'],"q".$group,$group_data['title'], $group_data['members'], $group_data['display_columns'], 0, CQ_WINDOW_BG, CQ_WINDOW_FG, $stats);
	} 
	else foreach ($groups as $group => $group_data) {
		
		$stats = array();	

		$st_x = $qx;
		$st_y = $qy;
		
		if ($group_data['active']) {		
			group_window($st_x,$st_y,$group_data['display_width'],$group_data['display_height'],"q".$group,$group_data['title'], $group_data['members'], $group_data['display_columns'], 0, CQ_WINDOW_BG, CQ_WINDOW_FG, $stats);
			if ($qmaxh < $group_data['display_height']) $qmaxh = $group_data['display_height'];
			if ($qh < $extmon_data['columns']) {
				$qx = $qx + $group_data['display_width'] + 1 ;
				++$qh;
			} else {
				$qh = 1;
				$qx = 0;
				$qy = $qy + $qmaxh +1;
				$qmaxh = 0;
			}
		}
	}

	
	
	echo "</div>\n";
}
?>
