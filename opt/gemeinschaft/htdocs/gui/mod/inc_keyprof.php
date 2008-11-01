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
include_once( GS_DIR .'inc/gs-lib.php' );
include_once( GS_DIR .'inc/gs-fns/gs_keys_get.php' );
include_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );  # for utf8_json_quote()
if ($is_user_profile) {
include_once( GS_DIR .'inc/gs-fns/gs_prov_phone_checkcfg.php' );
}

if (! isSet($is_user_profile)) {
	echo 'Error.';
	return;
}

$phone_types = array();
if (gs_get_conf('GS_SNOM_PROV_ENABLED')) {
	$phone_types['snom-360'    ] = 'Snom 360';
	$phone_types['snom-370'    ] = 'Snom 370';
}
/*
# Maybe there will be some reason for enabling keys on Snom M3 phones in future.
if (gs_get_conf('GS_SNOM_PROV_M3_ACCOUNTS')) {
	$phone_types['snom-m3'    ] = 'Snom M3';
}
*/
if (gs_get_conf('GS_SIEMENS_PROV_ENABLED')) {
	$phone_types['siemens-os40'] = 'Siemens OpenStage 40';
	$phone_types['siemens-os60'] = 'Siemens OpenStage 60';
	$phone_types['siemens-os80'] = 'Siemens OpenStage 80';
}
if (gs_get_conf('GS_AASTRA_PROV_ENABLED')) {
	$phone_types['aastra-53i'] = 'Aastra 53i';
	$phone_types['aastra-55i'] = 'Aastra 55i';
	$phone_types['aastra-57i'] = 'Aastra 57i';
}


$key_functions_snom = array(
	'none'  => __('Leer'),              # none
	'dest'  => __('Nebenstelle'),       # destination (/blf BLF)
	'speed' => __('externes Ziel'),     # external dest.
	'line'  => __('Leitung')            # line
);
$key_function_none_snom = 'none';

$key_functions_siemens = array(
	'f0'  => __('Leer'),                  # clear
	'f1'  => __('Zielwahl'),              # selected dialing
	'f9'  => __('Rufton aus'),            # ringer off
	'f10' => __('Halten'),                # hold
	'f11' => __('Makeln'),                # alternate
	'f13' => __('&Uuml;bergabe'),         # attended transfer
	'f12' => __('&Uuml;berg. v. Melden'), # blind transfer
	'f14' => __('Abweisen'),              # deflect
	'f18' => __('Shift'),                 # shift
	'f24' => __('Kopfh&ouml;rer'),        # headset
	'f25' => __('Nicht st&ouml;ren'),     # do not disturb
	'f29' => __('Rufgruppe'),             # group pickup
	'f30' => __('Kurzwahl'),              # repertory dial
	//'f31' => __('Leitung'),               # line
	'f50' => __('R&uuml;ckfrage'),        # consultation
	'f58' => __('Fn.-Schalter')           # feature toggle
);
$key_function_none_siemens = 'f0';
$key_functions_siemens_shifted_ok = array('f0', 'f1', 'f3',
	'f11', 'f12', 'f13', 'f14', 'f16', 'f17', 'f19', 'f22',
	'f30', 'f45', 'f46', 'f47', 'f48', 'f49', 'f50');
$key_functions_siemens_shifted = array();
foreach ($key_functions_siemens as $k => $v) {
	if (in_array($k, $key_functions_siemens_shifted_ok, true)) {
		$key_functions_siemens_shifted[$k] = $v;
	}
}
unset($key_functions_siemens_shifted_ok);

$key_functions_aastra = array(
	'empty'     => __('Leer'),
	'blf'       => __('Nebenstelle'),
	'speeddial' => __('Zielwahl'),
	//'line'    => __('Leitung'),
	'park'      => __('Parken'),
	'pickup'    => __('Heranholen'),
	'xml'       => __('Applikation'),
	'_callers'  => __('Anrufliste'),   # defined by Gemeinschaft
	'_dir'      => __('Telefonbuch')   # defined by Gemeinschaft
);

$key_function_none_aastra = 'none';


$key_default = array(
	'function'       => '',
	'data'           => '',
	'label'          => '',
	'user_writeable' => 1
);


$action = @$_REQUEST['action'];
if (! in_array($action, array('', 'save', 'save-and-resync', 'delete'), true))
	$action = '';
if ($action === 'save-and-resync' && ! $is_user_profile)
	$action = 'save';

$show_ext_modules = 255;
if (! $is_user_profile) {
	$profile_id = (int)@$_REQUEST['profile_id'];
} else {
	$user_id = (int)@$_SESSION['sudo_user']['info']['id'];
	$profile_id = (int)$DB->executeGetOne(
		'SELECT `softkey_profile_id` FROM `users` WHERE `id`='. $user_id );
	$show_ext_modules = (int)$DB->executeGetOne( 'SELECT `g`.`show_ext_modules` FROM `users` `u` JOIN `user_groups` `g` ON (`g`.`id`=`u`.`group_id`) WHERE `u`.`id`='.$user_id );
}
if ($profile_id < 1) $profile_id = 0;

$phone_type = preg_replace('/[^a-z0-9\-]/', '', @$_REQUEST['phone_type']);
if (! $is_user_profile) {
	if ($profile_id < 1) $phone_type = '';
}
if ($phone_type != '' && ! array_key_exists($phone_type, $phone_types)) {
	$phone_type = '';
}
if ($phone_type == '') {
	if (gs_get_conf('GS_SNOM_PROV_ENABLED')) {
		if     (array_key_exists('snom-360', $phone_types)) $phone_type = 'snom-360';
		elseif (array_key_exists('snom-370', $phone_types)) $phone_type = 'snom-370';
	} else
	if (gs_get_conf('GS_SIEMENS_PROV_ENABLED')) {
		if     (array_key_exists('siemens-os40', $phone_types)) $phone_type = 'siemens-os40';
		elseif (array_key_exists('siemens-os60', $phone_types)) $phone_type = 'siemens-os60';
		elseif (array_key_exists('siemens-os80', $phone_types)) $phone_type = 'siemens-os80';
	} else
	if (gs_get_conf('GS_AASTRA_PROV_ENABLED')) {
		if     (array_key_exists('aastra-57i', $phone_types)) $phone_type = 'aastra-57i';
	}
}
if (in_array($phone_type, array('snom-360', 'snom-370'), true)) {
	$phone_layout = 'snom';
	$key_function_none = $key_function_none_snom;
} elseif (in_array($phone_type, array('siemens-os40', 'siemens-os60', 'siemens-os80'), true)) {
	$phone_layout = 'siemens';
	$key_function_none = $key_function_none_siemens;
} elseif (in_array($phone_type, array('aastra-53i', 'aastra-55i', 'aastra-57i'), true)) {
	$phone_layout = 'aastra';
	$key_function_none = $key_function_none_aastra;
} else {
	$phone_layout = false;
	$key_function_none = false;
}


#####################################################################
# save {
#####################################################################
if ($action === 'save' || $action === 'save-and-resync') {
	//echo "<pre>"; print_r($_REQUEST); echo "</pre>";
	
	$do_save_keys = false;
	if ($is_user_profile) {
		$_REQUEST['profile_title'] = 'u-'. @$_SESSION['sudo_user']['name'];
		$do_save_keys = true;
	}
	if ($profile_id > 0) {
		$do_save_keys = true;
	} else {
		$do_save_keys = $do_save_keys || false;
		$ok = $DB->execute(
			'INSERT INTO `softkey_profiles` '.
				'(`id`, `is_user_profile`, `title`) '.
			'VALUES '.
				'(NULL, '. (int)$is_user_profile .', \''. $DB->escape(trim(@$_REQUEST['profile_title'])) .'\')' );
		if ($ok) {
			$profile_id = (int)$DB->getLastInsertId();
			if ($is_user_profile) {
				$ok = $DB->execute(
					'UPDATE `users` '.
					'SET `softkey_profile_id`='. $profile_id .' '.
					'WHERE `id`='. $user_id );
				if (! $ok) $do_save_keys = false;
			}
		}
	}
	if ($do_save_keys && $profile_id > 0) {
		if (array_key_exists('profile_title', $_REQUEST) && ! $is_user_profile) {
			$DB->execute(
				'UPDATE `softkey_profiles` SET '.
					'`title`=\''. $DB->escape(trim(@$_REQUEST['profile_title'])) .'\' '.
				'WHERE '.
					'`id`='. $profile_id .' AND '.
					'`is_user_profile`='. (int)$is_user_profile );
		}
		$save_keys = array();
		foreach ($_REQUEST as $k => $v) {
			if (! preg_match('/^key-([a-z][a-z0-9]{0,7})-x$/S', $k, $m)) continue;
			if ($v != '1') continue;
			$key = $m[1];
			$key_inherit       =    !(@$_REQUEST['key-'.$key.'-set'     ]);
			$key_function      = trim(@$_REQUEST['key-'.$key.'-function']);
			$key_data          = trim(@$_REQUEST['key-'.$key.'-data'    ]);
			$key_label         = trim(@$_REQUEST['key-'.$key.'-label'   ]);
			if (! $is_user_profile)
			$key_user_writable =    !(@$_REQUEST['key-'.$key.'-locked'  ]);
			else
			$key_user_writable = true;
			
			# keys without a function don't have a label or data
			if ($key_function === $key_function_none) {
				$key_label = '';
				$key_data  = '';
			}
			
			// validate function ? ...
			
			if ($key_inherit || $key_function == '') {  # inherit
				$key_inherit  = true;
				$key_function = '';
				$key_data     = '';
				$key_label    = '';
			}
			
			$save_keys[] = '('. $profile_id .', \''. $DB->escape($phone_type) .'\', \''. $DB->escape($key) .'\', \''. $DB->escape($key_function) .'\', \''. $DB->escape($key_data) .'\', \''. $DB->escape($key_label) .'\', '. (int)$key_user_writable .')';
		}
		//echo "<pre>"; print_r($save_keys); echo "</pre>";
		if (count($save_keys) < 1) {
			$ok = true;
		} else {
			# save
			$ok = $DB->execute(
				'REPLACE INTO `softkeys` '.
					'(`profile_id`, `phone_type`, `key`, `function`, `data`, `label`, `user_writeable`) '.
				'VALUES '."\n".
					implode(",\n", $save_keys) );
			
			# keys without a function don't have a label or data
			$DB->execute(
				'UPDATE `softkeys` SET '.
					'`label`=\'\', '.
					'`data`=\'\' '.
				'WHERE '.
					'`profile_id`='. $profile_id .' AND '.
					'`phone_type`=\''. $DB->escape($phone_type) .'\' AND '.
					'`function`=\''. $DB->escape($key_function_none) .'\'' );
			
			# delete unnecessary (inherited) entries
			$DB->execute(
				'DELETE FROM `softkeys` '.
				'WHERE '.
					'`phone_type`=\''. $DB->escape($phone_type) .'\' AND '.
					'`function`=\'\'' );
		}
		unset($save_keys);
		if (! $ok) {
			echo '<div class="errorbox">', __('Fehler beim Speichern') ,'</div>' ,"\n";
		}
	}
	
	if ($action === 'save-and-resync') {
		$ret = gs_prov_phone_checkcfg_by_user( @$_SESSION['sudo_user']['name'], false );
		if (isGsError($ret) || ! $ret) {
			// does not happen
			echo '<div class="errorbox">', __('Fehler beim Aktualisieren des Telefons') ,'</div>' ,"\n";
		}
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
	//echo "<pre>"; print_r($_REQUEST); echo "</pre>";
	
	if (! $is_user_profile) {
		if (! @$_REQUEST['delete_confirm']) {
			echo '<div class="errorbox">', __('L&ouml;schen mu&szlig; best&auml;tigt werden.') ,'</div>' ,"\n";
		} else {
			$DB->execute(
				'DELETE '.
				'FROM `softkeys` '.
				'WHERE '.
					'`profile_id`='. $profile_id .' AND '.
					'`phone_type`=\''. $DB->escape($phone_type) .'\'' );
			$num = $DB->executeGetOne(
				'SELECT COUNT(*) '.
				'FROM `softkeys` '.
				'WHERE '.
					'`profile_id`='. $profile_id );
			if ($num < 1) {
				$DB->execute(
					'UPDATE `user_groups` '.
					'SET `softkey_profile_id`=NULL '.
					'WHERE `softkey_profile_id`='. $profile_id );
				$DB->execute(
					'UPDATE `users` '.
					'SET `softkey_profile_id`=NULL '.
					'WHERE `softkey_profile_id`='. $profile_id );
				$DB->execute(
					'DELETE FROM `softkeys` '.
					'WHERE `profile_id`='. $profile_id );
				$DB->execute(
					'DELETE FROM `softkey_profiles` '.
					'WHERE '.
						'`id`='. $profile_id .' AND '.
						'`is_user_profile`='. (int)$is_user_profile );
			}
		}
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
	
	$show_variables = false;
	
?>
<form method="get" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<table cellspacing="1">
<?php if (! $is_user_profile) { ?>
<thead>
<tr>
	<th><?php echo __('Tastenbelegungs-Profil'); ?></th>
	<th><?php echo __('Telefon-Typ'); ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td>
		<select name="profile_id">
<?php
$rs = $DB->execute(
	'SELECT `id`, `title` '.
	'FROM `softkey_profiles` '.
	'WHERE `is_user_profile`=0 '.
	'ORDER BY `title`' );
while ($r = $rs->fetchRow()) {
	echo '<option value="', $r['id'] ,'"';
	if ($r['id'] == $profile_id)
		echo ' selected="selected"';
	echo '>', htmlEnt($r['title']) ,'</option>' ,"\n";
}
?>
		</select>
	</td>
<?php } else { ?>
<tbody>
<tr>
	<th class="m"><?php echo __('Telefon-Typ'); ?></th>
<?php } ?>
	<td>
		<select name="phone_type">
<?php
foreach ($phone_types as $phone_type_name => $phone_type_title) {
	echo '<option value="', $phone_type_name ,'"';
	if ($phone_type_name === $phone_type)
		echo ' selected="selected"';
	echo '>', htmlEnt($phone_type_title) ,'</option>' ,"\n";
}
?>
		</select>
	</td>
	<td class="transp">
		<input type="submit" value="<?php echo __('Zeigen'); ?>" />
	</td>
</tr>
</tbody>
</table>
</form>
<?php
echo '<small>(', __('Vor dem Wechsel ggf. &Auml;nderungen speichern!') ,')</small><br />' ,"\n";

#####################################################################

?>
<hr />
<?php if (! $is_user_profile) { ?>
<br />
<?php } ?>
<?php

if (! $is_user_profile) {
	if ($profile_id) {
		$rs = $DB->execute(
			'SELECT `title` '.
			'FROM `softkey_profiles` '.
			'WHERE '.
				'`id`='. $profile_id .' AND '.
				'`is_user_profile`=0' );
		$profile_info = $rs->fetchRow();
		if (! $profile_info) {
			//echo 'Profile not found.';
			# no message, we might have come here after a delete operation
			return;
		}
	} else {
		$profile_info = array(
			'title' => __('Neues Tastenprofil') .' '. base_convert(time(), 10, 36)
		);
	}
} else {
	$profile_info = array(
		'title' => 'u'
	);
}

if (array_key_exists($phone_type, $phone_types))
	$phone_type_title = $phone_types[$phone_type];
else
	$phone_type_title = $phone_type;


if ($profile_id && ! $is_user_profile) {
	echo '<form method="post" action="', gs_url($SECTION, $MODULE) ,'">' ,"\n";
	echo gs_form_hidden($SECTION, $MODULE);
	echo '<input type="hidden" name="action" value="delete" />' ,"\n";
	echo '<input type="hidden" name="profile_id" value="', $profile_id ,'" />' ,"\n";
	echo '<input type="hidden" name="phone_type" value="', $phone_type ,'" />' ,"\n";
	
	echo '<div class="fr">' ,"\n";
	echo '<input type="checkbox" name="delete_confirm" id="ipt-delete_confirm" value="1" />';
	echo '<label for="ipt-delete_confirm">', __('Tastenbelegungs-Profil l&ouml;schen') ,'</label>' ,"\n";
	echo '<button type="submit" title="', __('L&ouml;schen') ,'">' ,"\n";
	echo '<img alt=" " src="', GS_URL_PATH ,'crystal-svg/16/act/editdelete.png" />' ,"\n";
	echo __('L&ouml;schen') ,"\n";
	echo '</button>' ,"\n";
	echo '</div>' ,"\n";
	
	echo '</form>' ,"\n";
}
?>

<form method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="save" />
<input type="hidden" name="profile_id" value="<?php echo $profile_id; ?>" />
<input type="hidden" name="phone_type" value="<?php echo $phone_type; ?>" />

<?php if (! $is_user_profile) { ?>
<table cellspacing="1">
<tbody>
<tr>
	<th><?php echo __('Profil-Bezeichnung'); ?>:</th>
	<td><input type="text" name="profile_title" value="<?php echo htmlEnt($profile_info['title']); ?>" size="30" maxlength="50" /></td>
</tr>
</tbody>
</table>
<?php } ?>
<?php
	
if (! $profile_id && ! $is_user_profile) {  # do not show keys for new profiles or users' profiles
	
?>
<p class="l">
	<button type="submit" title="<?php echo __('Anlegen'); ?>">
		<?php echo __('Anlegen'); ?>
	</button>
</p>
<?php
	
} else {
	
	if ($phone_type == '') {
		reset($phone_types);
		$phone_type = @key($phone_types);
	}
	
	echo '<br style="clear:right;" />' ,"\n";
	
	$save_bt = '<p class="r">' ."\n";
	$save_bt.= '<button type="submit" title="'. __('Speichern') .'" name="action" value="save">' ."\n";
	$save_bt.= '<img alt=" " src="'. GS_URL_PATH .'crystal-svg/16/act/filesave.png" />' ."\n";
	$save_bt.= __('Speichern') ."\n";
	$save_bt.= '</button>' ."\n";
	if ($is_user_profile) {
		$save_bt.= '<button type="submit" title="'. __('Speichern und Telefon aktualisieren') .'" name="action" value="save-and-resync">' ."\n";
		$save_bt.= '<img alt=" " src="'. GS_URL_PATH .'crystal-svg/16/act/filesave.png" />' ."\n";
		$save_bt.= __('Speichern und Telefon aktualisieren') ."\n";
		$save_bt.= '</button>' ."\n";
	}
	$save_bt.= '</p>' ."\n";
	
	//echo '<p><b>', sPrintF(__('Softkeys am %s'), htmlEnt($phone_type_title)) ,'</b></p>' ,"\n";
	
?>

<?php

/*
$rs = $DB->execute(
	'SELECT `key`, `function`, `data`, `label`, `user_writeable` '.
	'FROM `softkeys` '.
	'WHERE '.
		'`profile_id`='. $profile_id .' AND '.
		'`phone_type`=\''. $DB->escape($phone_type) .'\' '.
	'ORDER BY `key`');
$softkeys = array();
while ($r = $rs->fetchRow()) {
	$softkeys[$r['key']] = $r;
}
//echo "<pre>"; print_r($softkeys); echo "</pre>\n";
*/

$softkeys = null;
$GS_Softkeys = gs_get_key_prov_obj( $phone_type );
if ($is_user_profile
	? $GS_Softkeys->set_user( @$_SESSION['sudo_user']['name'] )
	: $GS_Softkeys->set_profile_id( $profile_id )
) {
	if ($GS_Softkeys->retrieve_keys( $phone_type )) {
		$softkeys = $GS_Softkeys->get_keys();
	}
}
if (! is_array($softkeys)) {
	echo '<div class="errorbox">', 'Failed to get softkeys.' ,'</div>' ,"\n";
	return;
}
//echo "<pre>"; print_r($softkeys); echo "</pre>\n";

?>

<script type="text/javascript">
//<![CDATA[

<?php
echo 'var gs_keys_inherited = {';
$i=0;
foreach ($softkeys as $keyname => $keydefs) {
	if (array_key_exists('inh', $keydefs)) {
		$inh = $keydefs['inh'];  # inherited
		if ($inh['function'] != '') {
			if ($i===0) ++$i; else echo ',';
			echo "\n",'"',$keyname,'":{';
			echo  '"f":' , utf8_json_quote($inh['function']);
			echo ',"d":' , utf8_json_quote($inh['data']);
			echo ',"l":' , utf8_json_quote($inh['label']);
			echo ',"uw":',           (int)($inh['user_writeable']);
			echo '}';
		}
	}
}
echo "\n};\n";
?>

function gs_key_fn( el )
{
try {
	var kname = el.name.split('-')[1];
	if (kname === undefined || kname === null || kname === '') return;
	var inh = null;
	if (typeof(gs_keys_inherited) !== 'undefined') inh = gs_keys_inherited[kname] || null;
	if (el.name.substr(-9) === '-function') {
		var inherit = (el.value == '');
		el = el.form.elements.namedItem('key-'+kname+'-set');
		if (! el) return;
		el.checked = ! inherit;
	}
	var f;
	if (! el.checked) {  // inherited
		if ((f = el.form.elements.namedItem('key-'+kname+'-function' ))) {
			f.disabled = true;
			if (f.value != '') f._ed = f.selectedIndex;
			//f.selectedIndex = 0;
			for (var i=0; i<f.options.length; ++i)
				if (f.options[i].defaultSelected)
					f.selectedIndex = i;
			f.value = inh ? inh.f || '' : '';
		}
		if ((f = el.form.elements.namedItem('key-'+kname+'-data'     ))) {
			f.disabled = true;
			f._ed = f.value;
			f.value = inh ? inh.d || '' : '';
		}
		if ((f = el.form.elements.namedItem('key-'+kname+'-label'    ))) {
			f.disabled = true;
			f._ed = f.value;
			f.value = inh ? inh.l || '' : '';
		}
		if ((f = el.form.elements.namedItem('key-'+kname+'-locked'   ))) {
			f.disabled = true;
			f._ed = f.checked;
			f.checked = inh ? (! inh.uw) || false : false;
		}
	} else {
		if ((f = el.form.elements.namedItem('key-'+kname+'-function' ))) {
			f.disabled = false;
			if (f._ed !== undefined) f.selectedIndex = f._ed;
			if (f.value == '') {
				for (var i=0; i<f.options.length; ++i) {
					if (f.options[i].value != '') {
						f.selectedIndex = i;
						break;
					}
				}
			}
		}
		if ((f = el.form.elements.namedItem('key-'+kname+'-data'     ))) {
			f.disabled = false;
			if (f._ed !== undefined) f.value = f._ed;
		}
		if ((f = el.form.elements.namedItem('key-'+kname+'-label'    ))) {
			f.disabled = false;
			if (f._ed !== undefined) f.value = f._ed;
		}
		if ((f = el.form.elements.namedItem('key-'+kname+'-locked'   ))) {
			f.disabled = false;
			if (f._ed !== undefined) f.checked = f._ed;
		}
	}
} catch(e){}
}

//]]>
</script>
<?php





#################################################################
#  keys of snom, siemens, ... {
#################################################################
if ($phone_layout) {
	
	$show_variables = true;
	
	echo $save_bt;
	
	switch ($phone_layout) {
	case 'snom':
		//if ($show_ext_modules >= 0) {
			$key_levels = array(
				0 => array('from'=>   0, 'to'=>  11, 'shifted'=>false,
					'title'=> htmlEnt($phone_type_title))
			);
		//}
		if ($show_ext_modules >= 1) {
			$key_levels += array(
				1 => array('from'=>  12, 'to'=>  32, 'shifted'=>false,
					'title'=> __('Erweiterungs-Modul') .' 1')
			);
		}
		if ($show_ext_modules >= 2) {
			$key_levels += array(
				2 => array('from'=>  33, 'to'=>  53, 'shifted'=>false,
					'title'=> __('Erweiterungs-Modul') .' 2')
			);
		}
		break;
	case 'siemens':
		//if ($show_ext_modules >= 0) {
			$key_levels = array(
				0 => array('from'=>   1, 'to'=>   9, 'shifted'=>false,
					'title'=> htmlEnt($phone_type_title)),
				1 => array('from'=>1001, 'to'=>1009, 'shifted'=>true,
					'title'=> htmlEnt($phone_type_title) .', '. __('Shift-Ebene'))
			);
		//}
		if ($show_ext_modules >= 1) {
			$key_levels += array(
				2 => array('from'=> 301, 'to'=> 312, 'shifted'=>false,
					'title'=> __('Erweiterungs-Modul') .' 1'),
				3 => array('from'=>1301, 'to'=>1312, 'shifted'=>true,
					'title'=> __('Erweiterungs-Modul') .' 1, '. __('Shift-Ebene')),
			);
		}
		if ($show_ext_modules >= 2) {
			$key_levels += array(
				4 => array('from'=> 401, 'to'=> 412, 'shifted'=>false,
					'title'=> __('Erweiterungs-Modul') .' 2'),
				5 => array('from'=>1401, 'to'=>1412, 'shifted'=>true,
					'title'=> __('Erweiterungs-Modul') .' 2, '. __('Shift-Ebene'))
			);
		}
		switch ($phone_type) {
			case 'siemens-os60':
				$key_levels[0]['to'  ] =    8;
				$key_levels[1]['to'  ] = 1008;
				break;
			case 'siemens-os40':
				$key_levels[0]['to'  ] =    6;
				$key_levels[1]['to'  ] = 1006;
				break;
			case 'siemens-os20':
				$key_levels[0]['from'] =    0;
				$key_levels[1]['from'] =    0;
				$key_levels[0]['to'  ] =    0;
				$key_levels[1]['to'  ] =    0;
				break;
		}
		break;
	case 'aastra':
		//if ($show_ext_modules >= 0) {
		//}
		switch ($phone_type) {
			case 'aastra-57i':
				$key_levels[0]['title']= htmlEnt($phone_type_title) .' &ndash; '. __('Obere Tasten');
				$key_levels[1]['title']= htmlEnt($phone_type_title) .' &ndash; '. __('Untere Tasten');
				$key_levels[0]['from'] =    1;
				$key_levels[0]['to'  ] =   10;
				$key_levels[1]['from'] =  101;
				$key_levels[1]['to'  ] =  120;
				break;
			case 'aastra-55i':
				$key_levels[0]['title']= htmlEnt($phone_type_title) .' &ndash; '. __('Obere Tasten');
				$key_levels[1]['title']= htmlEnt($phone_type_title) .' &ndash; '. __('Untere Tasten');
				$key_levels[0]['from'] =    1;
				$key_levels[0]['to'  ] =    6;
				$key_levels[1]['from'] =  101;
				$key_levels[1]['to'  ] =  120;
				break;
			case 'aastra-53i':
				$key_levels[0]['title']= htmlEnt($phone_type_title) .' &ndash; '. __('Untere Tasten');
				$key_levels[0]['from'] =  101;
				$key_levels[0]['to'  ] =  120;
				break;
		}
		/*
		if ($show_ext_modules >= 1) {
			$key_levels += array(
				//FIXME
			);
		}
		if ($show_ext_modules >= 2) {
			$key_levels += array(
				//FIXME
			);
		}
		*/
		break;
	}
	
	if ($phone_layout === 'snom') $table_cols = 5;
	else                          $table_cols = 6;
	
	echo '<table cellspacing="1">' ,"\n";
	echo '<tbody>' ,"\n";
	
	foreach ($key_levels as $key_level_idx => $key_level_info) {
		
		if ($key_level_idx > 0) {
			echo '<tr><td colspan="',$table_cols,'" class="transp">&nbsp;</td></tr>' ,"\n";
		}
		echo '<tr>' ,"\n";
		echo '	<th colspan="',$table_cols,'" class="c m" style="padding:0.6em;">', $key_level_info['title'] ,'</th>' ,"\n";
		echo '</tr>' ,"\n";
		echo '<tr>' ,"\n";
		echo '	<th style="min-width:3.2em;">', __('Taste') ,'</th>' ,"\n";
		echo '	<th class="c">:=</th>' ,"\n";
		echo '	<th>', __('Funktion') ,'</th>' ,"\n";
		echo '	<th>', __('Nummer/Daten') ,'</th>' ,"\n";
		if ($phone_layout !== 'snom') {
			echo '	<th>', __('Beschriftung') ,'</th>' ,"\n";
		}
		echo '	<th>', __('Gesch&uuml;tzt?') ,'</th>' ,"\n";
		echo '</tr>' ,"\n";		
		
		switch ($phone_layout) {
			case 'snom':
				switch ($key_level_idx) {
					case 0: $left =  0; $right =  6; break;
					case 1: $left = 12; $right = 23; break;
					case 2: $left = 33; $right = 44; break;
				}
				break;
		}
		
		$row = 0;
		for ($i=$key_level_info['from']; $i<=$key_level_info['to']; ++$i) {
			
			if ($phone_layout === 'snom') {
				$knum  = ($i%2===($key_level_idx+1)%2 ? $left : $right);
				$knump = str_pad($knum, 3, '0', STR_PAD_LEFT);
			} else {
				$knum  = $i;
				$knump = str_pad($knum, 4, '0', STR_PAD_LEFT);
			}
			
			if ($phone_layout === 'snom') {
				$keyv = 'P'. str_replace(' ', '&nbsp;', str_pad($knum+1, 2, ' ', STR_PAD_LEFT));
			} elseif ($phone_layout === 'siemens') {
				$keyv = 'F';
				switch (subStr($knump,1,1)) {
					case '0': $keyv.= '0'; break;
					case '3': $keyv.= '1'; break;
					case '4': $keyv.= '2'; break;
					default : $keyv.= '?';
				}
				$keyv.= subStr($knump,-2);
				switch (subStr($knump,0,1)) {
					case '0': $keyv.= '&nbsp;'; break;
					case '1': $keyv.= 's'; break;
					default : $keyv.= '?';
				}
			} elseif ($phone_layout === 'aastra') {
				if ($knum >=   1) $keyv = 'T'. str_replace(' ','&nbsp;', str_pad($knum    , 2, ' ', STR_PAD_LEFT));
				if ($knum >= 100) $keyv = 'D'. str_replace(' ','&nbsp;', str_pad($knum-100, 2, ' ', STR_PAD_LEFT));
			} else {
				$keyv = 'F'.$knump;
			}
			
			$keydefs = @$softkeys['f'.$knump];
			if (! is_array($keydefs))
				$keydefs = array();
			if (! array_key_exists('inh', $keydefs))
				$keydefs['inh'] = $key_default;
			if (! array_key_exists('slf', $keydefs))
				$keydefs['slf'] = $key_default;
			if (! $is_user_profile) {
				$key = $keydefs['slf'];
				$can_write = true;
				$is_slf = true;
			} else {
				if ($keydefs['inh']['user_writeable']) {
					$key = $keydefs['slf'];
					//$can_write = (bool)(int)$key['user_writeable'];
					$can_write = true;
					$is_slf = true;
				} else {
					$key = $keydefs['inh'];
					$can_write = false;
					$is_slf = false;
				}
			}
			if ($can_write) {
				if (array_key_exists('_set_by', $key) && $key['_set_by'] === 'p'
				&&  ! $key['user_writeable']) {
					$can_write = false;
				}
			}
			
			/*
			if (preg_match('/:([^@]*)@/', $key['data'], $m)) {
				# i.e. "dest <sip:*800001@192.168.1.130>"
				$key['data'] = $m[1];
			}
			*/
			/*
			if (preg_match('/^\\*8/', $key['data'], $m)) {
				$key['data'] = 'PickUp';
			}
			*/
			
			echo '<tr class="', ($row%2 ? 'even':'odd'), ' m">', "\n";
			
			echo '<td style="font-size:96%;"';
			switch ($phone_layout) {
				case 'snom':
					echo ' class="', ($i%2===($key_level_idx+1)%2 ?'l':'r') ,'"';
					break;
			}
			echo '>';
			echo $keyv;
			echo '</td>' ,"\n";
			
			echo '<td>' ,"\n";
			echo '<input type="hidden" name="key-f',$knump,'-x" value="1" />';
			echo '<input type="checkbox" name="key-f',$knump,'-set" value="1"';
			if ($key['function'] != '' && $can_write && $is_slf) echo ' checked="checked"';
			if (! $can_write) echo ' disabled="disabled"';
			echo ' onchange="gs_key_fn(this);" />';
			echo '</td>' ,"\n";
			
			echo '<td>' ,"\n";
			echo '<select name="key-f',$knump,'-function"';
			if (! $can_write) echo ' disabled="disabled"';
			echo ' onchange="gs_key_fn(this);">' ,"\n";
			echo '<option value=""';
			if ('' == $key['function'])
				echo ' selected="selected"';
			/*
			if ($keydefs['inh']['function'] == '') {
				echo ' title="-"';
			} else {
				echo ' title="', @$key_functions_siemens[$keydefs['inh']['function']];
				if ($keydefs['inh']['data'] != '')
					echo ': ', htmlEnt($keydefs['inh']['data']);
				echo '"';
			}
			*/
			echo '>- ', __('erben') ,' -</option>' ,"\n";
			//echo '<option value="" disabled="disabled">&mdash;&mdash;&mdash;</option>' ,"\n";
			switch ($phone_layout) {
			case 'siemens':
				switch (subStr($knump,0,1)) {
					case '0': $fns =& $key_functions_siemens        ; break;
					case '1': $fns =& $key_functions_siemens_shifted; break;
				}
				break;
			case 'snom':
				$fns =& $key_functions_snom;
				break;
			case 'aastra':
				$fns =& $key_functions_aastra;
				break;
			}
			foreach ($fns as $function => $title) {
				//if ($can_write || $function === $key['function']) {
					echo '<option value="', $function ,'" title="', $function ,'"';
					if ($function === $key['function'])
						echo ' selected="selected"';
					echo '>', ($title) ,'</option>' ,"\n";
				//}
			}
			echo '</select>' ,"\n";
			echo '</td>' ,"\n";
			
			echo '<td>' ,"\n";
			echo '<input type="text" name="key-f',$knump,'-data" value="', htmlEnt($key['data']) ,'" size="25" maxlength="100" tabindex="', (10+$knum) ,'"';
			if (! $can_write) echo ' disabled="disabled"';
			echo ' />' ,"\n";
			echo '</td>' ,"\n";
			
			if ($phone_layout !== 'snom') {
				echo '<td>' ,"\n";
				echo '<input type="text" name="key-f',$knump,'-label" value="', htmlEnt($key['label']) ,'" size="15" maxlength="20" tabindex="', (10+$knum) ,'"';
				if (! $can_write) echo ' disabled="disabled"';
				echo ' />' ,"\n";
				echo '</td>' ,"\n";
			}
			
			echo '<td>' ,"\n";
			if (! $is_user_profile) {
				echo '<input type="checkbox" name="key-f',$knump,'-locked" id="ipt-key-f',$knump,'-locked" value="1"', ($key['user_writeable'] ? '':' checked="checked"');
				if (! $can_write) echo ' disabled="disabled"';
				echo ' />';
				echo '<label for="ipt-key-f',$knump,'-locked"><img alt="', __('Gesch&uuml;tzt') ,'" src="', GS_URL_PATH ,'img/locked.gif" /></label>' ,"\n";
			} else {
				if ($key['user_writeable']) {
					echo '&nbsp;';
				} else {
					echo '<img alt="', __('Gesch&uuml;tzt') ,'" src="', GS_URL_PATH ,'img/locked.gif" />';
				}
			}
			echo '</td>' ,"\n";
			
			echo '</tr>', "\n";
			++$row;
			switch ($phone_layout) {
			case 'snom':
				if ($i%2===($key_level_idx+1)%2) ++$left;
				else ++$right;
				break;
			}
		}
	}
	echo '</tbody>' ,"\n";
	echo '</table>' ,"\n";
	echo $save_bt;
}
#################################################################
#  } keys of snom, siemens, ...
#################################################################

#################################################################
#  keys of unknown phone type {
#################################################################
else {
	echo '<br />',"\n";
	echo '<div class="errorbox">';
	echo htmlEnt(sPrintF('Don\'t know how to display keys of phone type "%s".', $phone_type_title));
	echo '</div>' ,"\n";
}
#################################################################
#  } keys of unknown phone type
#################################################################
	
}

?>
</form>

<script type="text/javascript">
//<![CDATA[
try {
for (var i=0; i<document.forms.length; ++i) {
	for (var j=0; j<document.forms[i].length; ++j) {
		var el = document.forms[i][j];
		if (el.type
		&&  el.type === 'checkbox'
		&&  el.name
		&&  el.name.substr(0,4) === 'key-'
		&&  el.name.substr(-4) === '-set'
		&&  ! el.checked  // inherited
		) {
			gs_key_fn(el);
		}
	}
}
} catch(e){}
//]]>
</script>


<?php
	if ($show_variables && ! $is_user_profile) {
		echo '<p class="text" style="padding-bottom:0.1em;"><small>', __('M&ouml;gliche Variablen im Datenfeld') ,':</small></p>',"\n";
		echo '<table cellspacing="1">' ,"\n";
		echo '<tbody>' ,"\n";
		echo '<tr>' ,"\n";
		echo '	<td class="small"><tt>{GS_PROV_HOST}</tt></td>' ,"\n";
		echo '	<td class="small">', __('IP-Adresse des Provisioning-Servers') ,'</td>' ,"\n";
		echo '</tr>' ,"\n";
		echo '<tr>' ,"\n";
		echo '	<td class="small"><tt>{GS_P_PBX}</tt></td>' ,"\n";
		echo '	<td class="small">', __('IP-Adresse der Heimat-Telefonanlage') ,'</td>' ,"\n";
		echo '</tr>' ,"\n";
		echo '<tr>' ,"\n";
		echo '	<td class="small"><tt>{GS_P_EXTEN}</tt></td>' ,"\n";
		echo '	<td class="small">', __('Nebenstelle') ,'</td>' ,"\n";
		echo '</tr>' ,"\n";
		if (gs_get_conf('GS_BOI_ENABLED')) {
		echo '<tr>' ,"\n";
		echo '	<td class="small"><tt>{GS_P_ROUTE_PREFIX}</tt></td>' ,"\n";
		echo '	<td class="small">', __('ggf. Route zum Fremd-Host') ,'</td>' ,"\n";
		echo '</tr>' ,"\n";
		}
		echo '<tr>' ,"\n";
		echo '	<td class="small"><tt>{GS_P_USER}</tt></td>' ,"\n";
		echo '	<td class="small">', __('Benutzername') ,'</td>' ,"\n";
		echo '</tr>' ,"\n";
		echo '</tbody>' ,"\n";
		echo '</table>' ,"\n";
		echo '<br />' ,"\n";
	}
}
#####################################################################
# view }
#####################################################################
?>
