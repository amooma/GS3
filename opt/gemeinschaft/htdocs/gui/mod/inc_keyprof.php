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
* Sebastian Ertz
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
include_once( GS_DIR .'inc/gs-fns/gs_user_phonemodel_get.php' );
include_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );  # for utf8_json_quote()
if (! isSet($is_user_profile)) {
	echo 'Error.';
	return;
}
if ($is_user_profile) {
include_once( GS_DIR .'inc/gs-fns/gs_prov_phone_checkcfg.php' );
}

echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/dialog_box.js"></script>', "\n";


$phone_types = array();
if (gs_get_conf('GS_SNOM_PROV_ENABLED')) {
	$enabled_models = preg_split('/[,\\s]+/', gs_get_conf('GS_PROV_MODELS_ENABLED_SNOM'));
	if (in_array('*', $enabled_models) || in_array('300', $enabled_models))
		$phone_types['snom-300'] = 'Snom 300';
	if (in_array('*', $enabled_models) || in_array('320', $enabled_models))
		$phone_types['snom-320'] = 'Snom 320';
	if (in_array('*', $enabled_models) || in_array('360', $enabled_models))
		$phone_types['snom-360'] = 'Snom 360';
	if (in_array('*', $enabled_models) || in_array('370', $enabled_models))
		$phone_types['snom-370'] = 'Snom 370';
}
/*
# Maybe there will be some reason for enabling keys on Snom M3 phones in future.
if (gs_get_conf('GS_SNOM_PROV_M3_ACCOUNTS')) {
	$phone_types['snom-m3'    ] = 'Snom M3';
}
*/
if (gs_get_conf('GS_SIEMENS_PROV_ENABLED')) {
	$enabled_models = preg_split('/[,\\s]+/', gs_get_conf('GS_PROV_MODELS_ENABLED_SIEMENS'));
	if (in_array('*', $enabled_models) || in_array('os20', $enabled_models))
		$phone_types['siemens-os20'] = 'Siemens OpenStage 20';
	if (in_array('*', $enabled_models) || in_array('os40', $enabled_models))
		$phone_types['siemens-os40'] = 'Siemens OpenStage 40';
	if (in_array('*', $enabled_models) || in_array('os60', $enabled_models))
		$phone_types['siemens-os60'] = 'Siemens OpenStage 60';
	if (in_array('*', $enabled_models) || in_array('os80', $enabled_models))
		$phone_types['siemens-os80'] = 'Siemens OpenStage 80';
}
if (gs_get_conf('GS_AASTRA_PROV_ENABLED')) {
	$enabled_models = preg_split('/[,\\s]+/', gs_get_conf('GS_PROV_MODELS_ENABLED_AASTRA'));
	if (in_array('*', $enabled_models) || in_array('53i', $enabled_models))
		$phone_types['aastra-53i'] = 'Aastra 53i';
	if (in_array('*', $enabled_models) || in_array('55i', $enabled_models))
		$phone_types['aastra-55i'] = 'Aastra 55i';
	if (in_array('*', $enabled_models) || in_array('57i', $enabled_models))
		$phone_types['aastra-57i'] = 'Aastra 57i';
}
if (gs_get_conf('GS_GRANDSTREAM_PROV_ENABLED')) {
	$enabled_models = preg_split('/[,\\s]+/', gs_get_conf('GS_PROV_MODELS_ENABLED_GRANDSTREAM'));
	if (in_array('*', $enabled_models) || in_array('gxp2000', $enabled_models))
		$phone_types['grandstream-gxp2000'] = 'Grandstream GXP 2000';
	if (in_array('*', $enabled_models) || in_array('gxp2010', $enabled_models))
		$phone_types['grandstream-gxp2010'] = 'Grandstream GXP 2010';
	if (in_array('*', $enabled_models) || in_array('gxp2020', $enabled_models))
		$phone_types['grandstream-gxp2020'] = 'Grandstream GXP 2020';
}
if (gs_get_conf('GS_TIPTEL_PROV_ENABLED')) {
	$enabled_models = preg_split('/[,\\s]+/', gs_get_conf('GS_PROV_MODELS_ENABLED_TIPTEL'));
	if (in_array('*', $enabled_models) || in_array('ip284', $enabled_models))
		$phone_types['tiptel-ip284'] = 'Tiptel IP 284';
	if (in_array('*', $enabled_models) || in_array('ip286', $enabled_models))
		$phone_types['tiptel-ip286'] = 'Tiptel IP 286';
}


$key_functions_snom = array(
	'none'  => __('Leer'),              # none
	'speed' => __('externes Ziel'),     # external dest.
	'dest'  => __('Nebenstelle'),       # destination (//FIXME - auch BLF hiermit machen?)
	'blf'   => __('BLF'),               # BLF
	'line'  => __('Leitung'),           # line
);
$key_function_none_snom = 'none';
$key_functions_blacklist = preg_split('/[\\s,]+/', gs_get_conf('GS_SNOM_PROV_KEY_BLACKLIST'));
foreach ($key_functions_blacklist as $keyfn) {
	if (array_key_exists($keyfn, $key_functions_snom))
		unset($key_functions_snom[$keyfn]);
}

$key_functions_siemens = array(
	'f0'  => __('Leer'),                  # clear
	'f1'  => __('Zielwahl'),              # selected dialing
	'f59' => __('Nebenstelle/BLF'),       # extension
	'f9'  => __('Rufton aus'),            # ringer off
	'f10' => __('Halten'),                # hold
	'f11' => __('Makeln'),                # alternate
	'f13' => __('&Uuml;bergabe'),         # attended transfer
	'f12' => __('&Uuml;berg. v. Melden'), # blind transfer
	'f14' => __('Weiterleiten'),          # deflect
	'f18' => __('Shift'),                 # shift
	//'f22' => __('Konferenz'),             # conference
	'f24' => __('Kopfh&ouml;rer'),        # headset
	'f25' => __('Nicht st&ouml;ren'),     # do not disturb
	'f29' => __('Rufgrp.annahme'),        # group pickup
	'f30' => __('Kurzwahl'),              # repertory dial
	//'f31' => __('Leitung'),               # line
	'f50' => __('R&uuml;ckfrage'),        # consultation
	'f58' => __('Fn.-Schalter'),          # feature toggle   .._shifted_ok?
	'f60' => __('Appl. aufrufen'),        # invoke app       .._shifted_ok?
);
$key_function_none_siemens = 'f0';
$key_functions_blacklist = preg_split('/[\\s,]+/', gs_get_conf('GS_SIEMENS_PROV_KEY_BLACKLIST'));
foreach ($key_functions_blacklist as $keyfn) {
	if (array_key_exists($keyfn, $key_functions_siemens))
		unset($key_functions_siemens[$keyfn]);
}
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
	'_dir'      => __('Telefonbuch'),  # defined by Gemeinschaft
	'_fwd'      => __('Rufumleitung'), # defined by Gemeinschaft
	'_fwd_dlg'  => __('Rufumleitung Dialog'), # defined by Gemeinschaft
	'_login'    => __('Login'),        # defined by Gemeinschaft
	'_dnd'      => __('Ruhe'),         # defined by Gemeinschaft
);
$key_function_none_aastra = 'empty';
$key_functions_blacklist = preg_split('/[\\s,]+/', gs_get_conf('GS_AASTRA_PROV_KEY_BLACKLIST'));
foreach ($key_functions_blacklist as $keyfn) {
	if (array_key_exists($keyfn, $key_functions_aastra))
		unset($key_functions_aastra[$keyfn]);
}

$key_functions_grandstream = array(
	'empty' => __('Leer'),
	'f0'    => __('Zielwahl'),  # Speed Dial
	'f1'    => __('BLF'),  //FIXME
	//'f2'    => __('Presence Watcher'),  //FIXME
	//'f3'    => __('Eventlist BLF'),  //FIXME
);
$key_function_none_grandstream = 'empty';
$key_functions_blacklist = preg_split('/[\\s,]+/', gs_get_conf('GS_GRANDSTREAM_PROV_KEY_BLACKLIST'));
foreach ($key_functions_blacklist as $keyfn) {
	if (array_key_exists($keyfn, $key_functions_grandstream))
		unset($key_functions_grandstream[$keyfn]);
}

$key_functions_tiptel = array(
	'f0'  => __('Leer'),
	'f13' => __('Zielwahl'),	# SpeedDial
	'f16' => __('BLF'),		# BLF
	//'f1'  => __('Konferenz'),	# Conference
	//'f2'  => __('Forward'),	# Forward
	'f3'  => __('&Uuml;bergabe'),	# Transfer
	'f4'  => __('Halten'),		# Hold
	'f5'  => __('Nicht st&ouml;ren'),	# DND
	//'f6'  => __('Redial'),	# Redial
	//'f7'  => __('CallReturn'),	# Call Return
	//'f8'  => __('SMS'),		# SMS
	//'f9'  => __('CallPickup'),	# Call Pickup
	//'f10' => __('CallPark'),	# Call Park
	//'f11' => __('Custom'),	# Custom
	//'f12' => __('Voicemail'),	# Voicemail
	//'f14' => __('Intercom'),	# Intercom
	//'f15' => __('Leitung'),	# Line (for line key only)
	//'f17' => __('URL'),	# URL
	//'f18' => __('GroupListening'),	# Group Listening
	//'f19' => __('PublicHold'),	# Public Hold
	//'f20' => __('PrivateHold'),	# Private Hold
	//'f27' => __('XML Browser'),	# XML Browser
);
$key_function_none_tiptel = 'f0';
$key_functions_blacklist = preg_split('/[\\s,]+/', gs_get_conf('GS_TIPTEL_PROV_KEY_BLACKLIST'));
foreach ($key_functions_blacklist as $keyfn) {
	if (array_key_exists($keyfn, $key_functions_tiptel))
		unset($key_functions_tiptel[$keyfn]);
}



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
if( $is_user_profile && $phone_type == '' ) {
	$phone_type = gs_user_phonemodel_get( @$_SESSION['sudo_user']['name'] );
}
if ($phone_type != '' && ! array_key_exists($phone_type, $phone_types)) {
	$phone_type = '';
}
if ($phone_type == '') {
	if (gs_get_conf('GS_SNOM_PROV_ENABLED')) {
		if     (array_key_exists('snom-300', $phone_types)) $phone_type = 'snom-300';
		elseif (array_key_exists('snom-320', $phone_types)) $phone_type = 'snom-320';
		elseif (array_key_exists('snom-360', $phone_types)) $phone_type = 'snom-360';
		elseif (array_key_exists('snom-370', $phone_types)) $phone_type = 'snom-370';
	} else
	if (gs_get_conf('GS_SIEMENS_PROV_ENABLED')) {
		if     (array_key_exists('siemens-os20', $phone_types)) $phone_type = 'siemens-os20';
		elseif (array_key_exists('siemens-os40', $phone_types)) $phone_type = 'siemens-os40';
		elseif (array_key_exists('siemens-os60', $phone_types)) $phone_type = 'siemens-os60';
		elseif (array_key_exists('siemens-os80', $phone_types)) $phone_type = 'siemens-os80';
	} else
	if (gs_get_conf('GS_AASTRA_PROV_ENABLED')) {
		if     (array_key_exists('aastra-53i', $phone_types)) $phone_type = 'aastra-53i';
		elseif (array_key_exists('aastra-55i', $phone_types)) $phone_type = 'aastra-55i';
		elseif (array_key_exists('aastra-57i', $phone_types)) $phone_type = 'aastra-57i';
	} else
	if (gs_get_conf('GS_GRANDSTREAM_PROV_ENABLED')) {
		if     (array_key_exists('grandstream-gxp2000', $phone_types)) $phone_type = 'grandstream-gxp2000';
		elseif (array_key_exists('grandstream-gxp2010', $phone_types)) $phone_type = 'grandstream-gxp2010';
		elseif (array_key_exists('grandstream-gxp2020', $phone_types)) $phone_type = 'grandstream-gxp2020';
	} else
	if (gs_get_conf('GS_TIPTEL_PROV_ENABLED')) {
		if     (array_key_exists('tiptel-ip284', $phone_types)) $phone_type = 'tiptel-ip284';
		elseif (array_key_exists('tiptel-ip286', $phone_types)) $phone_type = 'tiptel-ip286';
	}
}
if (in_array($phone_type, array('snom-300', 'snom-320', 'snom-360', 'snom-370'), true)) {
	$phone_layout = 'snom';
	$key_function_none = $key_function_none_snom;
} elseif (in_array($phone_type, array('siemens-os20', 'siemens-os40', 'siemens-os60', 'siemens-os80'), true)) {
	$phone_layout = 'siemens';
	$key_function_none = $key_function_none_siemens;
} elseif (in_array($phone_type, array('aastra-53i', 'aastra-55i', 'aastra-57i'), true)) {
	$phone_layout = 'aastra';
	$key_function_none = $key_function_none_aastra;
} elseif (in_array($phone_type, array('grandstream-gxp2000', 'grandstream-gxp2010', 'grandstream-gxp2020'), true)) {
	$phone_layout = 'grandstream';
	$key_function_none = $key_function_none_grandstream;
} elseif (in_array($phone_type, array('tiptel-ip284', 'tiptel-ip286'), true)) {
	$phone_layout = 'tiptel';
	$key_function_none = $key_function_none_tiptel;
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
			
			$key_function = preg_replace('/[^a-zA-Z0-9\-_.]/S', '', $key_function);
			
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
<?php /*if (! $is_user_profile) { ?>
<br />
<?php }*/ ?>
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
	
	//echo '<br style="clear:right;" />' ,"\n";
	
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
	if (el.name.substr(el.name.length -9) === '-function') {
		// IE does not understand substr(-X)
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

if (navigator
&&  navigator.userAgent
&&  navigator.userAgent.indexOf('MSIE') != -1
) {
	// IE calls onchange handler too late for checkboxes.
	// until a solution is found disable the JavaScript enhancements:
	gs_key_fn = function(el){ return; };
}

var gs_phone_layout = '<?php echo $phone_layout; ?>';
var gs_dlg_helper_kname = '';
var gs_dlg_helper_kfunc = '';
function gs_key_fn_h( el )
{
	gs_key_fn( el );
	
	try {
		var kname = el.name.split('-')[1];
		if (kname === undefined || kname === null || kname === '') return;
		var kfn   = el.value;
		gs_dlg_helper_kname = kname;
		gs_dlg_helper_kfunc = kfn;
		
		if (gs_phone_layout == 'siemens' && kfn == 'f59') {
			// key f59 'BLF code ...'
			// dialog for choosing the attributes for the BLF
			<?php
			$innerhtml = '<table cellspacing="1"><tr><td>'. __('Nummer') .':</td>';
			$innerhtml.= '<td><input name="helper_number" type="text" size="30" maxlength="30" /></td></tr>';
			$innerhtml.= '<tr><td>'. __('Tonmeldung') .':</td><td><input type="checkbox" name="helper_audible" /></td></tr>';
			$innerhtml.= '<tr><td>'. __('Dialogfenster') .':</td><td><input type="checkbox" name="helper_popup" /></td></tr></table>';
			$innerhtml.= '<div align="center"><a href="#" title="'. __('OK') .'" onclick="return gs_dlg_ok();"><img alt="'. __('OK') .'" src="'. GS_URL_PATH .'crystal-svg/32/act/button_ok.png" /></a>';
			$innerhtml.= ' <a href="#" title="'. __('Abbrechen') .'" onclick="return gs_dlg_abort();"><img alt="'. __('Abbrechen') .'" src="'. GS_URL_PATH .'crystal-svg/32/act/button_cancel.png" /></a></div>';
			?>
			showDialog('<?php echo __("Parameter f&uuml;r das Besetztlampenfeld"); ?>', '<?php echo $innerhtml; ?>');
			try {
				var data = document.getElementsByName('key-'+kname+'-data')[0].value;
				var flags = (data.indexOf('|') > -1) ? data.split('|')[1] : 'ap';
				document.getElementsByName('helper_number' )[0].value
					=  data.split('|')[0];
				document.getElementsByName('helper_audible')[0].checked
					= flags.indexOf('a') > -1;
				document.getElementsByName('helper_popup'  )[0].checked
					= flags.indexOf('p') > -1;
			} catch(e){}
		}
		else if (gs_phone_layout == 'siemens' && kfn == 'f60') {
			// key f60 'Appl...'
			// dialog for choosing the different applications ...
			<?php
			//TODO: Add this to a table in database ...
			/*
			$SIEMENS_XML_APPS = array(
				'phonebook' => array(
					'server' => '192.168.23.2',
					'path'   => '/prov' ),
				'dial_log' => array(
					'server' => $PROV_HOST,
					'path'   => '/prov' )
			);
			
			$innerhtml = '<br />'. __('Bitte w&auml;hlen Sie eine Applikation aus') .':<br /><br />';
			$innerhtml.= '<select name="helper_apps" size="1">';
			foreach ($SIEMENS_XML_APPS as $app => $appname) {
				$innerhtml.= '<option>'. $app .'</option>';
			}
			$innerhtml.= '</select>';
			$innerhtml.= '<br /><br /><div align="center"><a href="#" title="'. __('OK') .'" onclick="return gs_dlg_ok();"><img alt="'. __('OK') .'" src="'. GS_URL_PATH .'crystal-svg/32/act/button_ok.png" /></a>';
			$innerhtml.= ' <a href="#" title="'. __('Abbrechen') .'" onclick="return gs_dlg_abort();"><img alt="'. __('Abbrechen') .'" src="'. GS_URL_PATH .'crystal-svg/32/act/button_cancel.png" /></a></div>';
			*/
			?>
			<?php /*
			showDialog('<?php echo __("Applikation ausw&auml;hlen"); ?>', '<?php echo $innerhtml; ?>');
			*/ ?>
		}
	}
	catch(e){}
}

function gs_dlg_ok()
{
	try {
		var data_el = document.getElementsByName('key-'+gs_dlg_helper_kname+'-data')[0];
		
		if (gs_phone_layout == 'siemens' && gs_dlg_helper_kfunc == 'f59') {
			// get number
			var data    = document.getElementsByName('helper_number')[0].value;
			// get audible
			var audible = document.getElementsByName('helper_audible')[0].checked;
			// get popup
			var popup   = document.getElementsByName('helper_popup')[0].checked;
			
			//if (audible || popup) {
				data += '|';
				if (audible) data += 'a';
				if (popup  ) data += 'p';
			//}
			data_el.value = data;
		}
		else if (gs_phone_layout == 'siemens' && gs_dlg_helper_kfunc == 'f60') {
			//var data = document.getElementsByName('helper_apps')[0].value;
		}
	}
	catch(e){}
	hideDialog();
}

function gs_dlg_abort()
{
	hideDialog();
	return true;
}

//]]>
</script>
<?php





#################################################################
#  keys of snom, siemens, ... {
#################################################################
if ($phone_layout) {
	
	echo '<h3 style="margin:0; padding:1px 0; font-size:100%;">', htmlEnt($phone_type_title) ,'</h3>', "\n";
	
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
		switch ($phone_type) {
			case 'snom-300':
				$key_levels[0]['to'  ] =    5;
				break;
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
				$key_levels[0]['to'  ] =   -1;
				$key_levels[1]['to'  ] =   -1;
				//unset($key_levels[0]);
				//unset($key_levels[1]);
				break;
		}
		break;
	case 'aastra':
	case 'aastra':
		//if ($show_ext_modules >= 0) {
			$key_levels = array();
		//}
		switch ($phone_type) {
			case 'aastra-57i':
				$key_levels[0]['title']= htmlEnt($phone_type_title) .' &ndash; '. __('Obere Tasten');
				$key_levels[0]['from'] =    1;
				$key_levels[0]['to'  ] =   10;

				if ($show_ext_modules >= 1) {
					$key_levels[1]['title']= htmlEnt($phone_type_title) .' &ndash; '. __('Erweiterung 1');
					$key_levels[1]['from'] =  201;
					$key_levels[1]['to'  ] =  260;
				}

				if ($show_ext_modules >= 2) {
					$key_levels[2]['title']= htmlEnt($phone_type_title) .' &ndash; '. __('Erweiterung 2');
					$key_levels[2]['from'] =  301;
					$key_levels[2]['to'  ] =  360;
				}

				if ($show_ext_modules >= 3) {
					$key_levels[3]['title'] = htmlEnt($phone_type_title) .' &ndash; '. __('Erweiterung 3');
					$key_levels[3]['from']  =  401;
					$key_levels[3]['to'  ]  =  460;
				}

				break;
			case 'aastra-55i':
				$key_levels[0]['title']= htmlEnt($phone_type_title) .' &ndash; '. __('Obere Tasten');
				$key_levels[0]['from'] =    1;
				$key_levels[0]['to'  ] =    6;

				if ($show_ext_modules >= 1) {
					$key_levels[1]['title']= htmlEnt($phone_type_title) .' &ndash; '. __('Erweiterung 1');
					$key_levels[1]['from'] =  201;
					$key_levels[1]['to'  ] =  260;
				}

				if ($show_ext_modules >= 2) {
					$key_levels[2]['title']= htmlEnt($phone_type_title) .' &ndash; '. __('Erweiterung 2');
					$key_levels[2]['from'] =  301;
					$key_levels[2]['to'  ] =  360;
				}

				if ($show_ext_modules >= 3) {
					$key_levels[3]['title'] = htmlEnt($phone_type_title) .' &ndash; '. __('Erweiterung 3');
					$key_levels[3]['from']  =  401;
					$key_levels[3]['to'  ]  =  460;
				}

				break;
			case 'aastra-53i':
				$key_levels[0]['title']= htmlEnt($phone_type_title) .' &ndash; '. __('Obere Tasten');
				$key_levels[0]['from'] =  103;
				$key_levels[0]['to'  ] =  106;

				if ($show_ext_modules >= 1) {
					$key_levels[1]['title']= htmlEnt($phone_type_title) .' &ndash; '. __('Erweiterung 1');
					$key_levels[1]['from'] =  201;
					$key_levels[1]['to'  ] =  260;
				}

				if ($show_ext_modules >= 2) {
					$key_levels[2]['title']= htmlEnt($phone_type_title) .' &ndash; '. __('Erweiterung 2');
					$key_levels[2]['from'] =  301;
					$key_levels[2]['to'  ] =  360;
				}

				if ($show_ext_modules >= 3) {
					$key_levels[3]['title'] = htmlEnt($phone_type_title) .' &ndash; '. __('Erweiterung 3');
					$key_levels[3]['from']  =  401;
					$key_levels[3]['to'  ]  =  460;
				}

				break;
			/*
			case 'aastra-51i':
				break;
			*/
		}
		break;
	case 'grandstream':
		//if ($show_ext_modules >= 0) {
			$key_levels = array(
				0 => array('from'=>   0, 'to'=>   6, 'shifted'=>false,
					'title'=> htmlEnt($phone_type_title))
			);
			switch ($phone_type) {
				case 'grandstream-gxp2010':
					$key_levels[0]['title'] = htmlEnt($phone_type_title).' &ndash; '. __('Linke Tasten');
					$key_levels[0]['to'   ] =  8;
					$key_levels[1]['title'] = htmlEnt($phone_type_title).' &ndash; '. __('Rechte Tasten');
					$key_levels[1]['from' ] =  9;
					$key_levels[1]['to'   ] = 17;
				break;
			}
		//}
		if ($show_ext_modules >= 1) {
			$key_levels += array(
				2 => array('from'=>  100, 'to'=>  113, 'shifted'=>false,
					'title'=> __('Erweiterungs-Modul') .' 1 '. __('Spalte') .' 1')
			);
			$key_levels += array(
				3 => array('from'=>  114, 'to'=>  127, 'shifted'=>false,
					'title'=> __('Erweiterungs-Modul') .' 1 '. __('Spalte') .' 2')
			);
			$key_levels += array(
				4 => array('from'=>  128, 'to'=>  141, 'shifted'=>false,
					'title'=> __('Erweiterungs-Modul') .' 1 '. __('Spalte') .' 3')
			);
			$key_levels += array(
				5 => array('from'=>  142, 'to'=>  155, 'shifted'=>false,
					'title'=> __('Erweiterungs-Modul') .' 1 '. __('Spalte') .' 4')
			);
		}
		if ($show_ext_modules >= 2) {
			$key_levels += array(
				6 => array('from'=>  156, 'to'=>  169, 'shifted'=>false,
					'title'=> __('Erweiterungs-Modul') .' 2 '. __('Spalte') .' 1')
			);
			$key_levels += array(
				7 => array('from'=>  170, 'to'=>  183, 'shifted'=>false,
					'title'=> __('Erweiterungs-Modul') .' 2 '. __('Spalte') .' 2')
			);
			$key_levels += array(
				8 => array('from'=>  184, 'to'=>  197, 'shifted'=>false,
					'title'=> __('Erweiterungs-Modul') .' 2 '. __('Spalte') .' 3')
			);
			$key_levels += array(
				9 => array('from'=>  198, 'to'=>  211, 'shifted'=>false,
					'title'=> __('Erweiterungs-Modul') .' 2 '. __('Spalte') .' 4')
			);
		}
		break;
	case 'tiptel':
		//if ($show_ext_modules >= 0) {
			$key_levels = array(
				0 => array('from'=>   1, 'to'=>   10, 'shifted'=>false,
					'title'=> htmlEnt($phone_type_title))
			);
		//}
		if ($show_ext_modules >= 1) {
			$key_levels += array(
				1 => array('from'=> 100, 'to'=> 118, 'shifted'=>false,
					'title'=> __('Erweiterungs-Modul') .' 1 '. __('Spalte') .' 1')
			);
			$key_levels += array(
				2 => array('from'=> 119, 'to'=> 137, 'shifted'=>false,
					'title'=> __('Erweiterungs-Modul') .' 1 '. __('Spalte') .' 2')
			);
		}
		if ($show_ext_modules >= 2) {
			$key_levels += array(
				3 => array('from'=> 200, 'to'=> 218, 'shifted'=>false,
					'title'=> __('Erweiterungs-Modul') .' 2 '. __('Spalte') .' 1')
			);
			$key_levels += array(
				4 => array('from'=> 219, 'to'=> 237, 'shifted'=>false,
					'title'=> __('Erweiterungs-Modul') .' 2 '. __('Spalte') .' 2')
			);
		}
		if ($show_ext_modules >= 3) {
			$key_levels += array(
				5 => array('from'=> 300, 'to'=> 318, 'shifted'=>false,
					'title'=> __('Erweiterungs-Modul') .' 3 '. __('Spalte') .' 1')
			);
			$key_levels += array(
				6 => array('from'=> 319, 'to'=> 337, 'shifted'=>false,
					'title'=> __('Erweiterungs-Modul') .' 3 '. __('Spalte') .' 2')
			);
		}
		break;
	}
	
	//if (in_array($phone_layout, array('snom', 'grandstream', 'tiptel'), true)) {
	if (in_array($phone_layout, array('tiptel'), true)) {
		$have_key_label = false;
		$table_cols = 5;
	} else {
		$have_key_label = true;
		$table_cols = 6;
	}
	
	echo '<table cellspacing="1">' ,"\n";
	echo '<tbody>' ,"\n";
	
	$is_first_table = true;
	foreach ($key_levels as $key_level_idx => $key_level_info) {
		
		if (! $is_first_table) {
			echo '<tr><td colspan="',$table_cols,'" class="transp">&nbsp;</td></tr>' ,"\n";
		} else {
			$is_first_table = false;
		}
		echo '<tr>' ,"\n";
		echo '	<th colspan="',$table_cols,'" class="c m" style="padding:0.6em;">', $key_level_info['title'] ,'</th>' ,"\n";
		echo '</tr>' ,"\n";
		echo '<tr>' ,"\n";
		echo '	<th style="min-width:3.2em;">', __('Taste') ,'</th>' ,"\n";
		echo '	<th class="c">:=</th>' ,"\n";
		echo '	<th>', __('Funktion') ,'</th>' ,"\n";
		echo '	<th>', __('Nummer/Daten') ,'</th>' ,"\n";
		if ($have_key_label) {
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
			} elseif ($phone_layout === 'grandstream') {
				$keyv = 'T'. str_replace(' ','&nbsp;', str_pad($knum+1    , 2, ' ', STR_PAD_LEFT));
				if ($knum >= 100) $keyv = 'T'. str_replace(' ','&nbsp;', str_pad($knum-99 , 3, ' ', STR_PAD_LEFT));
			} elseif ($phone_layout === 'tiptel') {
				if ($knum >=   1) $keyv = 'T'. str_replace(' ','&nbsp;', str_pad($knum     , 2, ' ', STR_PAD_LEFT));
				if ($knum >= 100) $keyv = 'T'. str_replace(' ','&nbsp;', str_pad($knum-99  , 3, ' ', STR_PAD_LEFT));
				if ($knum >= 200) $keyv = 'T'. str_replace(' ','&nbsp;', str_pad($knum-199 , 3, ' ', STR_PAD_LEFT));
				if ($knum >= 300) $keyv = 'T'. str_replace(' ','&nbsp;', str_pad($knum-299 , 3, ' ', STR_PAD_LEFT));
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
				case 'tiptel':
					if ($key_level_idx > 0)
						echo ' class="', ($i%2===($key_level_idx+1)%2 ? 'l':'r') ,'"';
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
			echo ' onchange="gs_key_fn_h(this);">' ,"\n";
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
			case 'grandstream':
				$fns =& $key_functions_grandstream;
				break;
			case 'tiptel':
				$fns =& $key_functions_tiptel;
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
			
			if ($have_key_label) {
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
	echo '<br />' ,"\n";

	if (in_array($phone_type, array('snom-300','snom-320','snom-360','snom-370','grandstream-gxp2000','grandstream-gxp2010','grandstream-gxp2020'), true))
		echo '<a href="',GS_URL_PATH ,'srv/key-layout.php?phone_type=',$phone_type,'&user_id=',$user_id,'"><img alt="PDF" src="', GS_URL_PATH, 'crystal-svg/16/mime/pdf.png" /></a>'."\n"; 

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
		&&  el.name.substr(el.name.length -4) === '-set'
		// IE does not understand substr(-X)
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
