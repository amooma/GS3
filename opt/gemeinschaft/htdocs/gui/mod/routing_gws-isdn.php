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
include_once( GS_DIR .'inc/gs-lib.php' );
include_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );
include_once( GS_DIR .'inc/pcre_check.php' );
include_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );

$default_dialstrs = array(
	'misdn'   => 'mISDN/g:{gateway}/{number:1}',
	'woomera' => 'Woomera/g{port}/{number:1}',
	//'zap'     => 'Zap/g{port}/{number:1}',
	'dahdi'   => 'DAHDI/g{port}/{number:1}',
	'capi'    => 'Capi/isdn_{gateway}/{number:1}',
	'vpb'     => 'vpb/g{port}/{number:1}',
);
$gw_types = array(
	'misdn'   => 'mISDN',
	'woomera' => 'Woomera (Sangoma)',
	//'zap'     => 'Zaptel',
	'dahdi'   => 'Dahdi (fka. Zaptel)',
	'capi'    => 'CAPI',
	'vpb'     => 'VPB (Voicetronix)',
);

$action = @$_REQUEST['action'];
if (! in_array($action, array( '', 'edit', 'save', 'del' ), true))
	$action = '';


echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";
?>

<script type="text/javascript">
//<![CDATA[

function confirm_delete() {
	return confirm(<?php echo utf8_json_quote(__("Wirklich l\xC3\xB6schen?")); ?>);
}

function updateElement( fieldID, fieldValue ) {
	document.getElementById(fieldID).value = fieldValue;
}

function updateDialstr( fieldID, fieldValue ) {
	dialString = '';
<?php
	foreach ($default_dialstrs as $dialstr_key => $dialstr) {
		echo "\t", 'if (fieldValue == \'', $dialstr_key ,'\') dialString = \'', $dialstr ,'\'' ,"\n";
	}
?>
	document.getElementById(fieldID).value = dialString;
}

//]]>
</script>

<?php

echo '<p class="text">', __('Konfiguration von ISDN-Basisanschl&uuml;ssen (Mehrger&auml;te- oder Anlagenanschl&uuml;sse). Siehe auch Einstellungen der ISDN-Karte(n) im System-Men&uuml;.') ,'</p>',"\n";

$gw_type = strToLower(@$_REQUEST['gw-type']);
if (! array_key_exists($gw_type, $default_dialstrs))
	$gw_type = 'misdn';



#####################################################################
if ($action === 'save') {
	
	$gwid = (int)@$_REQUEST['gw-id'];
	
	if ($gwid < 1) {
		$DB->execute(
'INSERT INTO `gates` (
	`id`,
	`grp_id`,
	`type`,
	`name`,
	`title`,
	`allow_out`,
	`dialstr`,
	`hw_port`
) VALUES (
	NULL,
	NULL,
	\''.$DB->escape( $gw_type ).'\',
	\'gw_tmp_'. rand(100000,999999) .'\',
	\'\',
	0,
	\''. $DB->escape( $default_dialstrs[$gw_type] ) .'\',
	0
)'
		);
		$gwid = (int)$DB->getLastInsertId();
	}
	
	$gw_name = strToLower(@$_REQUEST['gw-title']);
	$gw_name = preg_replace('/[^a-z0-9]/', '', $gw_name);
	$gw_name = subStr('gw_'.$gwid.'_'.$gw_name, 0, 20);
	
	$query =
'UPDATE `gates` SET
	`grp_id` = '. ((int)@$_REQUEST['gw-grp_id'] > 0 ? (int)@$_REQUEST['gw-grp_id'] : 'NULL') .',
	`name` = \''. $DB->escape($gw_name) .'\',
	`type` = \''. $DB->escape($gw_type) .'\',
	`title` = \''. $DB->escape(trim(@$_REQUEST['gw-title'])) .'\',
	`allow_out` = '. (@$_REQUEST['gw-allow_out'] ? 1 : 0) .',
	`dialstr` = \''. $DB->escape(trim(@$_REQUEST['gw-dialstr'])) .'\',
	`hw_port` = '. ((int)@$_REQUEST['gw-hw_port']) .'
WHERE `id`='. (int)$gwid
	;
	//echo "<pre>$query</pre>\n";
	$DB->execute($query);
	
	$cmd = '/opt/gemeinschaft/sbin/start-asterisk 1>>/dev/null 2>>/dev/null';
	@exec( 'sudo sh -c '. qsa($cmd) .' 1>>/dev/null 2>>/dev/null' );
	
	$action = '';
}
#####################################################################



#####################################################################
if ($action === 'del') {
	
	$gwid = (int)@$_REQUEST['gw-id'];
	
	$DB->execute( 'DELETE FROM `gate_params` WHERE `gate_id`='.$gwid );
	$DB->execute( 'DELETE FROM `gates` WHERE `id`='.$gwid );
	
	$action = '';
}
#####################################################################



#####################################################################
if ($action === 'edit') {
	$gwid = (int)@$_REQUEST['gw-id'];
?>

<form id="gw-data" method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="save" />
<input type="hidden" name="gw-id" value="<?php echo $gwid; ?>" />

<?php
	if ($gwid > 0) {
		# get gateway from DB
		$rs = $DB->execute( 'SELECT `grp_id`, `type`, `name`, `title`, `allow_out`, `hw_port`, `dialstr` FROM `gates` WHERE `id`='.$gwid );
		$gw = $rs->fetchRow();
		if (! $gw) {
			echo 'Gateway not found.';
			return;
		}
		if (! array_key_exists($gw['type'], $default_dialstrs)) {
			echo 'Gateway not supported.';
			return;
		}
	}
	else {
		$gw = array(
			'grp_id'     => null,
			'type'       => $gw_type,
			'name'       => '',
			'title'      => '',
			'dialstr'    => $default_dialstrs[$gw_type],
			'allow_out'  => 1,
			'hw_port'    => 0
		);
	}
?>


<table cellspacing="1">
<tbody>

<?php
	echo '<tr>',"\n";
	echo '<th style="width:100px;">', __('Titel') ,':</th>',"\n";
	echo '<th style="width:360px;"><input type="text" name="gw-title" value="', htmlEnt($gw['title']) ,'" size="30" maxlength="35" style="font-weight:bold; width:97%;" /></th>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>', __('Name') ,':</th>',"\n";
	echo '<td style="color:#999; font-size:92%;"><tt>', htmlEnt($gw['name']) ,'</tt></td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>', __('Typ') ,':</th>',"\n";
	echo '<td>',"\n";
	echo '<select name="gw-type" onchange="updateDialstr(\'gw-dialstr-ipt\', this.value)">', "\n";
	foreach ($default_dialstrs as $gw_type_key => $gw_type_default_dialstr) {
		echo '<option value="',$gw_type_key,'"';
		if ($gw['type'] == $gw_type_key) echo ' selected="selected"';
		echo '>', htmlEnt($gw_types[$gw_type_key]) ,'</option>',"\n";
	}
	echo '</select>';
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>', __('Port') ,':</th>',"\n";
	echo '<td>',"\n";
	echo '<select name="gw-hw_port" class="r">' ,"\n";
	for ($i=1; $i<=8; ++$i) {
		echo '<option value="', $i ,'"', ($i === $gw['hw_port'] ? ' selected="selected"' : '') ,'>', $i ,' &nbsp;</option>' ,"\n";
	}
	echo '</select>' ,"\n";
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>&nbsp;</th>',"\n";
	echo '<td>';
	echo '<input type="checkbox" name="gw-allow_out" id="ipt-gw-allow_out" value="1" ', ($gw['allow_out'] ? 'checked="checked" ' : '') ,'/> <label for="ipt-gw-allow_out">', __('ausgehende Anrufe zulassen') ,'</label>',"\n";
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>', __('W&auml;hlbefehl') ,' <sup>[1]</sup>:</th>',"\n";
	echo '<td><input type="text" name="gw-dialstr" id="gw-dialstr-ipt" value="', htmlEnt($gw['dialstr']) ,'" size="30" maxlength="50" style="font-family:monospace; width:97%;" /></th>',"\n";
	
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
	echo '<tr>',"\n";
	echo '<th>', __('Gruppe') ,' <sup>[2]</sup>:</th>',"\n";
	echo '<td>';
	echo '<select name="gw-grp_id" style="width:97%;">',"\n";
	echo '<option value=""', ($gw['grp_id'] < 1 ? ' selected="selected"' : '') ,'>-- ', __('nicht zugeordnet') ,' --</option>' ,"\n";
	echo '<option value="" disabled="disabled">', '' ,'</option>' ,"\n";
	$rs = $DB->execute(
'SELECT `id`, `title`
FROM `gate_grps`
ORDER BY `title`'
	);
	while ($gg = $rs->fetchRow()) {
		echo '<option value="', $gg['id'] ,'"', ($gg['id'] === $gw['grp_id'] ? ' selected="selected"' : '') ,'>', htmlEnt($gg['title']) ,'</option>' ,"\n";
	}
	echo '</select>',"\n";
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
?>

</tbody>
</table>

<br />
<button type="submit">
	<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
	<?php echo __('Speichern'); ?>
</button>

<br />
<br />
<br />
<p class="text"><sup>[1]</sup> <?php echo htmlEnt(sPrintF(__("String f\xC3\xBCr den Dial()-Befehl. Dabei wird {number} automatisch von Gemeinschaft durch die zu w\xC3\xA4hlende Rufnummer, {number:1} durch die Rufnummer ohne die erste Ziffer, {port} durch den eingestellten Port und {gateway} durch die interne Bezeichnung \"%s\" ersetzt."), $gw['name'])); ?></p>
<p class="text"><sup>[2]</sup> <?php echo __('Gateways m&uuml;ssen jeweils einer Gateway-Gruppe zugeordnet werden damit sie benutzt werden k&ouml;nnen.'); ?></p>

</form>

<?php
}
#####################################################################



#####################################################################
if ($action == '') {
?>

<table cellspacing="1">
<thead>
<tr>
	<th style="width:150px;"><?php echo __('Anschlu&szlig;'); ?></th>
	<th style="width:150px;"><?php echo __('Gruppe'); ?></th>
	<th style="width:45px;"><?php echo __('Port'); ?></th>
	<th style="width:55px;">&nbsp;</th>
</tr>
</thead>
<tbody>
<?php
	
	$gw_types_sql = '\''. implode('\',\'', array_keys($default_dialstrs)) .'\'';
	
	# get gateways from DB
	#
	$rs = $DB->execute(
'SELECT
	`g`.`id`, `g`.`name`, `g`.`title`, `g`.`hw_port`,
	`gg`.`id` `gg_id`, `gg`.`title` `gg_title`
FROM
	`gates` `g` LEFT JOIN
	`gate_grps` `gg` ON (`gg`.`id`=`g`.`grp_id`)
WHERE
	`g`.`type` IN ('. $gw_types_sql .')
ORDER BY `g`.`grp_id`, `g`.`title`'
	);
	$i=0;
	while ($gw = $rs->fetchRow()) {
		echo '<tr class="', ($i%2===0?'odd':'even') ,'">',"\n";
		
		echo '<td><b>', htmlEnt($gw['title']) ,'</b></td>',"\n";
		
		echo '<td>';
		if ($gw['gg_id'] > 0) {
			echo htmlEnt($gw['gg_title']);
		} else {
			echo '<i>-- ', __('nicht zugeordnet') ,' --</i>';
		}
		echo '</td>',"\n";
		
		echo '<td class="r" style="padding-right:1.5em;">', htmlEnt($gw['hw_port']) ,'</td>',"\n";
		
		echo '<td>',"\n";
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=edit&amp;gw-id='.$gw['id']) ,'" title="', __('bearbeiten'), '"><img alt="', __('bearbeiten'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=del&amp;gw-id='.$gw['id']) ,'" title="', __('l&ouml;schen'), '" onclick="return confirm_delete();"><img alt="', __('l&ouml;schen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
		echo '</td>',"\n";
		
		echo '</tr>',"\n";
		++$i;
	}
	echo '<tr class="', ($i%2===0?'odd':'even') ,'">',"\n";
	echo '<td colspan="3" class="transp">&nbsp;</td>',"\n";
	echo '<td class="transp">',"\n";
	echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=edit&amp;gw-id=0') ,'" title="', __('hinzuf&uuml;gen'), '"><img alt="', __('hinzuf&uuml;gen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
	echo '</td>',"\n";
	echo '</tr>',"\n";
	
?>
</tbody>
</table>

<?php
}
#####################################################################


?>
