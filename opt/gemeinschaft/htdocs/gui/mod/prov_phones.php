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

$action = @$_REQUEST['action'];
if (! in_array($action, array('view','details'), true))
	$action = 'view';

function _mac_addr_internal( $mac )
{
	return preg_replace('/[^0-9A-Z]/','', strToUpper($mac));
}

function _mac_addr_display( $mac )
{
	return preg_replace('/\\:$/S','', preg_replace('/.{2}/S','$0:', strToUpper($mac)));
}

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
	$phone_types['siemens-os15'] = 'Siemens OpenStage 15';
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
if (gs_get_conf('GS_TIPTEL_PROV_ENABLED')) {
	$phone_types['tiptel-ip28xs'] = 'Tiptel IP 28xs';
	$phone_types['tiptel-ip280' ] = 'Tiptel IP 280';
	$phone_types['tiptel-ip284' ] = 'Tiptel IP 284';
	$phone_types['tiptel-ip286' ] = 'Tiptel IP 286';
}
if (gs_get_conf('GS_YEALINK_PROV_ENABLED')) {
	$phone_types['yealink-sip-t46G' ] = 'Yealink SIP T46G';
        $phone_types['yealink-sip-t48G' ] = 'Yealink SIP T48G';
}
if (gs_get_conf('GS_POLYCOM_PROV_ENABLED')) {
	$phone_types['polycom-spip-300'] = 'Polycom SoundPoint IP 300';
	$phone_types['polycom-spip-500'] = 'Polycom SoundPoint IP 500';
	$phone_types['polycom-spip-600'] = 'Polycom SoundPoint IP 600';

	$phone_types['polycom-spip-301'] = 'Polycom SoundPoint IP 301';
	$phone_types['polycom-spip-320'] = 'Polycom SoundPoint IP 320';
	$phone_types['polycom-spip-330'] = 'Polycom SoundPoint IP 330';
	$phone_types['polycom-spip-430'] = 'Polycom SoundPoint IP 430';
	$phone_types['polycom-spip-450'] = 'Polycom SoundPoint IP 450';
	$phone_types['polycom-spip-501'] = 'Polycom SoundPoint IP 501';
	$phone_types['polycom-spip-550'] = 'Polycom SoundPoint IP 550';
	$phone_types['polycom-spip-560'] = 'Polycom SoundPoint IP 560';
	$phone_types['polycom-spip-601'] = 'Polycom SoundPoint IP 601';
	$phone_types['polycom-spip-650'] = 'Polycom SoundPoint IP 650';
	$phone_types['polycom-spip-670'] = 'Polycom SoundPoint IP 670';
}


$per_page = (int)GS_GUI_NUM_RESULTS;

$mac_addr_internal = _mac_addr_internal(@$_REQUEST['mac']);
$mac_addr_display  = _mac_addr_display( $mac_addr_internal );
$pbx_id = array_key_exists('pbx_id', $_REQUEST) ? (int)$_REQUEST['pbx_id'] : -1;
$ip_addr = trim(@$_REQUEST['ip']);
$phone_type = @$_REQUEST['phone_type'];
$page = (int)@$_REQUEST['page'];

$search_url =
	'mac='        .urlEncode($mac_addr_internal) .'&amp;'.
	'ip='         .urlEncode($ip_addr          ) .'&amp;'.
	'pbx_id='     .urlEncode($pbx_id           ) .'&amp;'.
	'phone_type=' .urlEncode($phone_type       );


#####################################################################
#  view {
#####################################################################
if ($action === 'view') {
	
	//echo "<pre>"; print_r($_REQUEST); echo "</pre>";
	$where = array();
	if ($mac_addr_internal != '') {
		$where[] = '`p`.`mac_addr` LIKE \'%'. $DB->escape($mac_addr_internal) .'%\'';
	}
	if ($pbx_id === 0) {
		$where[] = '`h`.`is_foreign`=0';
	} elseif ($pbx_id > 0) {
		$where[] = '`h`.`id`='. $pbx_id;
	}
	if ($ip_addr != '') {
		$where[] = '`u`.`current_ip` LIKE \''. $DB->escape($ip_addr) .'%\'';
	}
	if ($phone_type != '') {
		$where[] = '`p`.`type`=\''. $DB->escape($phone_type) .'\'';
	}
	
	$query =
		'SELECT SQL_CALC_FOUND_ROWS '.
			'`p`.`id`, `p`.`mac_addr`, `p`.`type`, `p`.`firmware_cur`, '.
			'`u`.`firstname`, `u`.`lastname`, `u`.`current_ip`, '.
			'`u`.`id` `user_id`, `u`.`nobody_index`, '.
			'`s`.`name` `exten` '.
		'FROM '.
			'`phones` `p` LEFT JOIN '.
			'`users` `u` ON (`u`.`id`=`p`.`user_id`) LEFT JOIN '.
			'`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) LEFT JOIN '.
			'`hosts` `h` ON (`h`.`id`=`u`.`host_id`) '.
		(count($where)===0 ? '' : 'WHERE '.implode(' AND ',$where) ) .' '.
		'ORDER BY `p`.`mac_addr` '.
		'LIMIT '. ($page*$per_page) .','. $per_page
		;
	$rs_phones = $DB->execute($query);
	$num_total = @$DB->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	
?>

<form method="get" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<table cellspacing="1">
<tbody>
<tr>
	<th style="width:240px;"><?php echo __('MAC-Adresse'); ?></th>
	<th style="width:240px;"><?php echo __('IP-Adresse'); ?></th>
	<th style="width:100px;"><?php echo __('Seite'), ' ', ($page+1), ' / ', $num_pages; ?></th>
</tr>
<tr>
	<td>
		<input type="text" name="mac" value="<?php echo $mac_addr_display; ?>" size="17" maxlength="30" />
	</td>
	<td>
		<input type="text" name="ip" value="<?php echo $ip_addr; ?>" size="15" maxlength="15" />
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
	<th><?php echo __('Anlage'); ?></th>
	<th><?php echo __('Telefon-Typ'); ?></th>
	<td class="transp">&nbsp;</td>
</tr>
<tr>
	<td>
		<select name="pbx_id">
			<option value="-1"<?php if (-1 == $pbx_id) echo ' selected="selected"'; ?>><?php echo __('alle'); ?></option>
			<option value="0"<?php if (0 == $pbx_id) echo ' selected="selected"'; ?>><?php echo __('Zentrale'); ?></option>
<?php
	$rs = $DB->execute( 'SELECT `id`, `host`, `comment` FROM `hosts` WHERE `is_foreign`=1 ORDER BY `comment`' );
	if ($rs->numRows() > 0)
		echo '<option value="" disabled="disabled">-</option>', "\n";
	while ($r = $rs->fetchRow()) {
		echo '<option value="', $r['id'] ,'"';
		if ($r['id'] == $pbx_id) echo ' selected="selected"';
		echo '>', htmlEnt(mb_subStr($r['comment'],0,20)), ' (', htmlEnt($r['host']) ,')' ,'</option>' ,"\n";
	}
?>
		</select>
	</td>
	<td>
		<select name="phone_type">
			<option value=""><?php echo __('alle'); ?></option>
<?php
	foreach ($phone_types as $phone_type_name => $phone_type_display) {
		echo '<option value="', $phone_type_name ,'"';
		if ($phone_type_name === $phone_type)
			echo ' selected="selected"';
		echo '>', $phone_type_display ,'</option>' ,"\n";
	}
?>
		</select>
	</td>
	<td>
		<button type="submit" title="<?php echo __('Suchen'); ?>" class="plain">
			<img alt="<?php echo __('Suchen'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/search.png" />
		</button>
	</td>
</tr>
</tbody>
</table>
</form>

<br />
<table cellspacing="1">
<thead>
<tr>
	<th style="min-width:10em;"><?php echo __('MAC-Adresse'); ?></th>
	<th style="min-width:10em;"><?php echo __('Telefon-Typ'); ?></th>
	<th style="min-width: 7em;"><?php echo __('IP-Adresse' ); ?></th>
	<th style="min-width:14em;"><?php echo __('Benutzer'   ); ?></th>
	<th style="min-width: 3em;"><?php echo __('Nebenst.'   ); ?></th>
	<th style="min-width: 3em;"><?php echo __('Firmware'   ); ?></th>
	<?php /* <th style="min-width: 3em;"><?php echo __('Upgrade'    ); ?></th> */ ?>
</tr>
</thead>
<tbody>
<?php

$i=0;
while ($phone = $rs_phones->fetchRow()) {
	echo '<tr class="',($i%2===0?'odd':'even'),'">' ,"\n";
	
	echo '<td><tt>', htmlEnt(_mac_addr_display($phone['mac_addr'])) ,'</tt></td>' ,"\n";
	
	echo '<td>', htmlEnt(array_key_exists($phone['type'], $phone_types) ? $phone_types[$phone['type']] : $phone['type']) ,'</td>' ,"\n";
	
	echo '<td>';
	if ($phone['user_id'] > 0) {
		if ($phone['current_ip'] != null) {
			echo htmlEnt($phone['current_ip']);
		} else {
			echo '-';
		}
	} else {
		echo '?';
	}
	echo '</td>' ,"\n";
	
	echo '<td>';
	if ($phone['user_id'] > 0) {
		if ($phone['nobody_index'] < 1) {
			echo htmlEnt(trim( $phone['firstname'] .' '. $phone['lastname'] ));
		} else {
			echo '-';
		}
	} else {
		echo '?';
	}
	echo '</td>' ,"\n";
	
	echo '<td>';
	if ($phone['user_id'] > 0) {
		if ($phone['exten'] != '') {
			echo htmlEnt($phone['exten']);
		} else {
			echo '-';
		}
	} else {
		echo '?';
	}
	echo '</td>' ,"\n";
	
	echo '<td>';
	if ($phone['firmware_cur'] != '') {
		echo htmlEnt($phone['firmware_cur']);
	} else {
		echo '?';
	}
	echo '</td>' ,"\n";
	
	/*
	echo '<td>';
	echo '<select name="firmware_upgrade">' ,"\n";
	echo '<option value="">-</option>' ,"\n";
	//...
	echo '</select>' ,"\n";
	echo '</td>' ,"\n";
	*/
	
	echo '</tr>' ,"\n";
	++$i;
}

?>
</tbody>
</table>


<?php
}
#####################################################################
#  view }
#####################################################################
