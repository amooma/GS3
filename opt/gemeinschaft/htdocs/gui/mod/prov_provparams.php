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


$phone_types = array();
if (gs_get_conf('GS_SNOM_PROV_ENABLED')) {
	$phone_types['snom-300'    ] = 'Snom 300';
	$phone_types['snom-320'    ] = 'Snom 320';
	$phone_types['snom-360'    ] = 'Snom 360';
	$phone_types['snom-370'    ] = 'Snom 370';
        $phone_types['snom-870'    ] = 'Snom 870';
        $phone_types['snom-760'    ] = 'Snom 760';
        $phone_types['snom-720'    ] = 'Snom 720';
}
if (gs_get_conf('GS_SNOM_PROV_M3_ACCOUNTS')) {
	$phone_types['snom-m3'    ] = 'Snom M3';
}
if (gs_get_conf('GS_SNOM_PROV_M9_ACCOUNTS')) {
	$phone_types['snom-m9'    ] = 'Snom M9';
}
if (gs_get_conf('GS_SIEMENS_PROV_ENABLED')) {
	$phone_types['siemens-os20'] = 'Siemens OpenStage 20';
	$phone_types['siemens-os40'] = 'Siemens OpenStage 40';
	$phone_types['siemens-os60'] = 'Siemens OpenStage 60';
	$phone_types['siemens-os80'] = 'Siemens OpenStage 80';
}
if (gs_get_conf('GS_AASTRA_PROV_ENABLED')) {
	$phone_types['aastra-51i'] = 'Aastra 51i';
	$phone_types['aastra-53i'] = 'Aastra 53i';
	$phone_types['aastra-55i'] = 'Aastra 55i';
	$phone_types['aastra-57i'] = 'Aastra 57i';
}
if (gs_get_conf('GS_YEALINK_PROV_ENABLED')) {
	$phone_types['yealink-sip-t46g' ] = 'Yealink SIP T46G';
	$phone_types['yealink-sip-t48g' ] = 'Yealink SIP T48G';	
}
if (gs_get_conf('GS_GRANDSTREAM_PROV_ENABLED')) {
	$phone_types['grandstream-ht287'  ] = 'Grandstream HT 287';
	$phone_types['grandstream-bt110'  ] = 'Grandstream BT 110';
	$phone_types['grandstream-bt200'  ] = 'Grandstream BT 200';
	$phone_types['grandstream-bt201'  ] = 'Grandstream BT 201';
	$phone_types['grandstream-gxp280' ] = 'Grandstream GXP 280';
	$phone_types['grandstream-gxp1200'] = 'Grandstream GXP 1200';
	$phone_types['grandstream-gxp2000'] = 'Grandstream GXP 2000';
	$phone_types['grandstream-gxp2010'] = 'Grandstream GXP 2010';
	$phone_types['grandstream-gxp2110'] = 'Grandstream GXP 2110';
	$phone_types['grandstream-gxp2020'] = 'Grandstream GXP 2020';
	$phone_types['grandstream-gxv3000'] = 'Grandstream GXV 3000';
	$phone_types['grandstream-gxv3005'] = 'Grandstream GXV 3005';
	$phone_types['grandstream-gxv3140'] = 'Grandstream GXV 3140';
}


$action = @$_REQUEST['action'];
if (! in_array($action, array('', 'save', 'delete'), true))
	$action = '';

$profile_id = (int)@$_REQUEST['profile_id'];
if ($profile_id < 1) $profile_id = 0;

$phone_type = preg_replace('/[^a-z0-9\-]/', '', @$_REQUEST['phone_type']);
if ($profile_id < 1) $phone_type = '';
if ($phone_type == '') {
	if (gs_get_conf('GS_SNOM_PROV_ENABLED')) {
		if     (array_key_exists('snom-300', $phone_types)) $phone_type = 'snom-300';
		elseif (array_key_exists('snom-320', $phone_types)) $phone_type = 'snom-320';
		elseif (array_key_exists('snom-360', $phone_types)) $phone_type = 'snom-360';
		elseif (array_key_exists('snom-370', $phone_types)) $phone_type = 'snom-370';
		elseif (array_key_exists('snom-870', $phone_types)) $phone_type = 'snom-870';
		elseif (array_key_exists('snom-760', $phone_types)) $phone_type = 'snom-760';
		elseif (array_key_exists('snom-720', $phone_types)) $phone_type = 'snom-720';
	} else
	if (gs_get_conf('GS_SNOM_PROV_M3_ACCOUNTS')) {
		if     (array_key_exists('snom-m3', $phone_types)) $phone_type = 'snom-m3';
	} else
	if (gs_get_conf('GS_SIEMENS_PROV_ENABLED')) {
		if     (array_key_exists('siemens-os20', $phone_types)) $phone_type = 'siemens-os20';
		elseif (array_key_exists('siemens-os40', $phone_types)) $phone_type = 'siemens-os40';
		elseif (array_key_exists('siemens-os60', $phone_types)) $phone_type = 'siemens-os60';
		elseif (array_key_exists('siemens-os80', $phone_types)) $phone_type = 'siemens-os80';
	} else
	if (gs_get_conf('GS_AASTRA_PROV_ENABLED')) {
		if     (array_key_exists('aastra-51i', $phone_types)) $phone_type = 'aastra-51i';
		elseif (array_key_exists('aastra-53i', $phone_types)) $phone_type = 'aastra-53i';
		elseif (array_key_exists('aastra-55i', $phone_types)) $phone_type = 'aastra-55i';
		elseif (array_key_exists('aastra-57i', $phone_types)) $phone_type = 'aastra-57i';
	} else
	if (gs_get_conf('GS_GRANDSTREAM_PROV_ENABLED')) {
		if     (array_key_exists('grandstream-ht287'  , $phone_types)) $phone_type = 'grandstream-ht287';
		elseif (array_key_exists('grandstream-bt110'  , $phone_types)) $phone_type = 'grandstream-bt110';
		elseif (array_key_exists('grandstream-bt200'  , $phone_types)) $phone_type = 'grandstream-bt200';
		elseif (array_key_exists('grandstream-bt201'  , $phone_types)) $phone_type = 'grandstream-bt201';
		elseif (array_key_exists('grandstream-gxp280' , $phone_types)) $phone_type = 'grandstream-gxp280';
		elseif (array_key_exists('grandstream-gxp1200', $phone_types)) $phone_type = 'grandstream-gxp1200';
		elseif (array_key_exists('grandstream-gxp2000', $phone_types)) $phone_type = 'grandstream-gxp2000';
		elseif (array_key_exists('grandstream-gxp2010', $phone_types)) $phone_type = 'grandstream-gxp2010';
		elseif (array_key_exists('grandstream-gxp2020', $phone_types)) $phone_type = 'grandstream-gxp2020';
		elseif (array_key_exists('grandstream-gxv3000', $phone_types)) $phone_type = 'grandstream-gxv3000';
		elseif (array_key_exists('grandstream-gxv3005', $phone_types)) $phone_type = 'grandstream-gxv3005';
	}
}



#####################################################################
# save {
#####################################################################
if ($action === 'save') {
	//echo "<pre>"; print_r($_REQUEST); echo "</pre>";
	
	if ($profile_id < 1) {
		$ok = $DB->execute(
			'INSERT INTO `prov_param_profiles` '.
			'(`id`, `is_group_profile`, `title`) '.
			'VALUES '.
			'(NULL, 1, \''. $DB->escape(trim(@$_REQUEST['profile_title'])) .'\')' );
		if ($ok) {
			$profile_id = $DB->getLastInsertId();
		}
	} else {
		//echo "<pre>\n"; print_r($_REQUEST); echo "\n</pre>\n";
		$DB->execute(
			'UPDATE `prov_param_profiles` SET '.
				'`title`=\''. $DB->escape(trim(@$_REQUEST['profile_title'])) .'\' '.
			'WHERE '.
				'`id`='. $profile_id .' AND '.
				'`is_group_profile`=1' );
		
		$DB->execute(
			'DELETE FROM `prov_params` '.
			'WHERE '.
				'`profile_id`='. $profile_id .' AND '.
				'`phone_type`=\''. $DB->escape($phone_type) .'\''
			);
		foreach ($_REQUEST as $k => $v) {
			if (! preg_match('/^param-([0-9]+)-param/S', $k, $m)) continue;
			$idx = $m[1];
			/*
			$param_delete  =  (int)(@$_REQUEST['param-'.$idx.'-del'  ]);
			if ($param_delete) continue;
			*/
			$param_param   =       (@$_REQUEST['param-'.$idx.'-param']);
			$param_index   =   trim(@$_REQUEST['param-'.$idx.'-index']);
			$param_value   =       (@$_REQUEST['param-'.$idx.'-value']);
			
			$param_param = trim(preg_replace('/[^a-zA-Z0-9\-._ ]/', '', $param_param));
			if ($param_param === '') continue;
			$param_index = ($param_index === '' ? -1 : (int)$param_index);
			
			$DB->execute(
				'INSERT INTO `prov_params` '.
				'(`profile_id`, `phone_type`, `param`, `index`, `value`) '.
				'VALUES '.
				'('. $profile_id .', \''. $DB->escape($phone_type) .'\', \''. $DB->escape($param_param) .'\', '. $param_index .', \''. $DB->escape($param_value) .'\')'
				);
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
	
	if (! @$_REQUEST['delete_confirm']) {
		echo '<div class="errorbox">', __('L&ouml;schen mu&szlig; best&auml;tigt werden.') ,'</div>' ,"\n";
	} else {
		$DB->execute(
			'DELETE '.
			'FROM `prov_params` '.
			'WHERE '.
				'`profile_id`='. $profile_id .' AND '.
				'`phone_type`=\''. $DB->escape($phone_type) .'\'' );
		$num = $DB->executeGetOne(
			'SELECT COUNT(*) '.
			'FROM `prov_params` '.
			'WHERE '.
				'`profile_id`='. $profile_id );
		if ($num < 1) {
			$DB->execute(
				'UPDATE `user_groups` '.
				'SET `prov_param_profile_id`=NULL '.
				'WHERE `prov_param_profile_id`='. $profile_id );
			/*
			$DB->execute(
				'UPDATE `users` '.
				'SET `prov_param_profile_id`=NULL '.
				'WHERE `prov_param_profile_id`='. $profile_id );
			*/
			$DB->execute(
				'DELETE FROM `prov_params` '.
				'WHERE `profile_id`='. $profile_id );
			$DB->execute(
				'DELETE FROM `prov_param_profiles` '.
				'WHERE '.
					'`id`='. $profile_id .' AND '.
					'`is_group_profile`=1' );
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

?>
<p class="text"><small><?php echo __('Achtung! Das Setzen von Parametern beeinflu&szlig;t direkt das Auto-Provisioning f&uuml;r den ausgew&auml;hlten Endger&auml;te-Typ. Eine &Uuml;berpr&uuml;fung der eingegebenen Parameter und Werte findet nicht statt. Lesen Sie bitte genau die technische Dokumentation des Telefon-Herstellers.'); ?></small></p>

<form method="get" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<table cellspacing="1">
<thead>
<tr>
	<th><?php echo __('Provisioning-Parameter-Profil'); ?></th>
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
	'FROM `prov_param_profiles` '.
	'WHERE `is_group_profile`=1 '.
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
?>
<hr />
<br />

<?php




if ($profile_id) {
	$rs = $DB->execute(
		'SELECT `title` '.
		'FROM `prov_param_profiles` '.
		'WHERE '.
			'`id`='. $profile_id .' AND '.
			'`is_group_profile`=1' );
	$profile_info = $rs->fetchRow();
	if (! $profile_info) {
		//echo 'Profile not found.';
		# no message, we might have come here after a delete operation
		return;
	}
} else {
	$profile_info = array(
		'title' => __('Neues Parameter-Profil') .' '. base_convert(time(), 10, 36)
	);
}

if (array_key_exists($phone_type, $phone_types))
	$phone_type_title = $phone_types[$phone_type];
else
	$phone_type_title = $phone_type;


if ($profile_id) {
	echo '<form method="post" action="', gs_url($SECTION, $MODULE) ,'">' ,"\n";
	echo gs_form_hidden($SECTION, $MODULE);
	echo '<input type="hidden" name="action" value="delete" />' ,"\n";
	echo '<input type="hidden" name="profile_id" value="', $profile_id ,'" />' ,"\n";
	echo '<input type="hidden" name="phone_type" value="', $phone_type ,'" />' ,"\n";
	
	echo '<div class="fr">' ,"\n";
	echo '<input type="checkbox" name="delete_confirm" id="ipt-delete_confirm" value="1" />';
	echo '<label for="ipt-delete_confirm">', __('Parameter-Profil l&ouml;schen') ,'</label>' ,"\n";
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

<table cellspacing="1">
<tbody>
<tr>
	<th><?php echo __('Profil-Bezeichnung'); ?>:</th>
	<td><input type="text" name="profile_title" value="<?php echo htmlEnt($profile_info['title']); ?>" size="30" maxlength="50" /></td>
</tr>
</tbody>
</table>
<?php
	
if (! $profile_id) {  # do not show params for new profiles
	
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
	$save_bt.= '<button type="submit" title="'. __('Speichern') .'">' ."\n";
	$save_bt.= '<img alt=" " src="'. GS_URL_PATH .'crystal-svg/16/act/filesave.png" />' ."\n";
	$save_bt.= __('Speichern') ."\n";
	$save_bt.= '</button>' ."\n";
	$save_bt.= '</p>' ."\n";
	
	//echo '<p><b>', sPrintF(__('Softkeys am %s'), htmlEnt($phone_type_title)) ,'</b></p>' ,"\n";
	
?>

<?php

$rs = $DB->execute(
	'SELECT `param`, `index`, `value` '.
	'FROM `prov_params` '.
	'WHERE '.
		'`profile_id`='. $profile_id .' AND '.
		'`phone_type`=\''. $DB->escape($phone_type) .'\' '.
	'ORDER BY `param`, `index`');
$paramdefs = array();
while ($r = $rs->fetchRow()) {
	$paramdefs[$r['param']][$r['index']] = array('value'=>$r['value']);
}



if (preg_match('/^snom-/', $phone_type)) {
	if (preg_match('/^snom-3/', $phone_type)) {
		echo '<a target="_blank" href="', __('http://wiki.snom.com/Settings/OEM') ,'">', sPrintF(__('Dokumentation der %s-Parameter'), 'Snom-3xx') ,'</a>';
	}
	else {
		echo '<a target="_blank" href="', __('http://wiki.snom.com/') ,'">', sPrintF(__('%s-Dokumentation'), 'Snom') ,'</a>';
	}
}
elseif (preg_match('/^siemens-/', $phone_type)) {
	if (preg_match('/^siemens-os/', $phone_type)) {
		echo '<a target="_blank" href="', __('http://wiki.siemens-enterprise.com/index.php/OpenStage_SIP') ,'">', sPrintF(__('Dokumentation %s'), 'Siemens OpenStage') ,'</a>';
	}
	else {
		echo '<a target="_blank" href="', __('http://wiki.siemens-enterprise.com/index.php/Phones') ,'">', sPrintF(__('Dokumentation %s'), 'Siemens') ,'</a>';
	}
}
elseif (preg_match('/^aastra-/', $phone_type)) {
	if (preg_match('/^aastra-5/', $phone_type)) {
		echo '<a target="_blank" href="', __('http://www.aastra-detewe.de/cps/rde/xchg/aastra-detewe/hs.xsl/21552.htm') ,'">', sPrintF(__('Dokumentation %s'), 'Aastra 5xi') ,'</a>';
	}
	else {
		echo '<a target="_blank" href="', __('http://www.aastra-detewe.de/cps/rde/xchg/aastra-detewe/hs.xsl/21552.htm') ,'">', sPrintF(__('Dokumentation %s'), 'Aastra') ,'</a>';
	}
}



#################################################################
#  params for all phone types {
#################################################################
//if (in_array($phone_type, array('siemens-os60', 'siemens-os80'), true)) {
	
	echo $save_bt;
	
	echo '<table cellspacing="1">' ,"\n";
	echo '<thead>' ,"\n";
	echo '<tr>' ,"\n";
	echo '	<th>', __('Parameter') ,'</th>' ,"\n";
	echo '	<th>', __('Index') ,' <sup>[1]</sup></th>' ,"\n";
	echo '	<th>', __('Wert') ,' <sup>[2]</sup></th>' ,"\n";
	//echo '	<th>&nbsp;</th>' ,"\n";
	echo '</tr>' ,"\n";
	echo '</thead>' ,"\n";
	echo '<tbody>' ,"\n";
	
	$i=0;
	foreach ($paramdefs as $param_name => $param_arr) {
	foreach ($param_arr as $param_index => $param_info) {
		echo '<tr class="', ($i%2 ? 'even':'odd'), '">', "\n";
		
		echo '<td>' ,"\n";
		echo '<input type="text" name="param-',$i,'-param" value="', htmlEnt($param_name) ,'" size="25" maxlength="50" tabindex="', (10+$i) ,'" />' ,"\n";
		echo '</td>' ,"\n";
		
		echo '<td>' ,"\n";
		echo '<input type="text" name="param-',$i,'-index" value="', htmlEnt($param_index >= 0 ? $param_index : '') ,'" size="5" maxlength="5" tabindex="', (10+$i) ,'" class="r" />' ,"\n";
		echo '</td>' ,"\n";
		
		echo '<td>' ,"\n";
		echo '<input type="text" name="param-',$i,'-value" value="', htmlEnt($param_info['value']) ,'" size="25" maxlength="100" tabindex="', (10+$i) ,'" />' ,"\n";
		echo '</td>' ,"\n";
		
		/*
		echo '<td>' ,"\n";
		echo '<input type="checkbox" name="param-',$i,'-del" id="ipt-param-',$i,'-del" value="1" />';
		echo '<label for="ipt-param-',$i,'-del"><img alt="', __('L&ouml;schen') ,'" src="', GS_URL_PATH ,'crystal-svg/16/act/editdelete.png" /></label>' ,"\n";
		echo '</td>' ,"\n";
		*/
		
		echo '</tr>', "\n";
		++$i;
	}
	}
	
	echo '<tr class="', ($i%2 ? 'even':'odd'), '">', "\n";
	
	echo '<td>' ,"\n";
	echo '<input type="text" name="param-',$i,'-param" value="" size="25" maxlength="50" tabindex="', (10+$i) ,'" />' ,"\n";
	echo '</td>' ,"\n";
	
	echo '<td>' ,"\n";
	echo '<input type="text" name="param-',$i,'-index" value="" size="5" maxlength="5" tabindex="', (10+$i) ,'" class="r" />' ,"\n";
	echo '</td>' ,"\n";
	
	echo '<td>' ,"\n";
	echo '<input type="text" name="param-',$i,'-value" value="" size="25" maxlength="100" tabindex="', (10+$i) ,'" />' ,"\n";
	echo '</td>' ,"\n";
	
	/*
	echo '<td>' ,"\n";
	echo '&nbsp;' ,"\n";
	echo '</td>' ,"\n";
	*/
	
	echo '</tr>', "\n";
	
	echo '</tbody>' ,"\n";
	echo '</table>' ,"\n";
	echo $save_bt;
	
	
	echo '<p class="text"><small><sup>[1]</sup> ', __('Index f&uuml;r Parameter die einen Eintrag in einem Array definieren. Bei Parametern die kein Array sind das Feld leer lassen. Lesen Sie die technische Dokumentation des Telefon-Herstellers.') ,'</small></p>' ,"\n";
	
	echo '<p class="text" style="padding-bottom:0.1em;"><small><sup>[2]</sup> ', __('M&ouml;gliche Variablen in den Werten') ,':</small></p>',"\n";
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
	
//}
#################################################################
#  } params for all phone types
#################################################################

#################################################################
#  params for unknown phone type {
#################################################################
/*
else {
	echo '<br />',"\n";
	echo '<div class="errorbox">';
	echo htmlEnt(sPrintF('Don\'t know how to display params for phone type "%s".', $phone_type_title));
	echo '</div>' ,"\n";
}
*/
#################################################################
#  } params for unknown phone type
#################################################################
	
}

?>
</form>

<?php
}
#####################################################################
# view }
#####################################################################
?>
