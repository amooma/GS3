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
	echo '<img alt="" src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";





if (@$_REQUEST['action']=='save') {
	
	$keys = gs_keys_snom_get( $_SESSION['sudo_user']['name'] );
	if (isGsError($keys)) {
		echo __('Fehler beim Abfragen.'), '<br />', $keys->getMsg();
		die();
	}
	if (! is_array($keys)) {
		echo __('Fehler beim Abfragen.');
		die();
	}
	
	foreach ($_REQUEST as $k => $v) {
		if (subStr($k, 0, 4) != 'key-') continue;
		$kname = subStr($k, 4);
		if (! preg_match('/^f\d{1,2}$/S', $kname)) continue;
		if (! array_key_exists($kname, $keys)) continue;
		if (! @$keys[$kname]['rw']) continue;
		$v = preg_replace('/[^\d*#]/S', '', $v);
		if ($v === @$keys[$kname]['val']) continue;
		//echo "set $kname = $v\n";
		if ($v != '')
			$DB->execute( 'REPLACE INTO `softkeys` (`user_id`, `phone`, `key`, `number`) VALUES ('. (int)@$_SESSION['sudo_user']['info']['id'] .', \'snom\', \''. $DB->escape($kname) .'\', \''. $DB->escape($v) .'\')' );
		else
			$DB->execute( 'DELETE FROM `softkeys` WHERE `user_id`='. (int)@$_SESSION['sudo_user']['info']['id'] .' AND `phone`=\'snom\' AND `key`=\''. $DB->escape($kname) .'\'' );
	}
	
}





//$keys = gs_user_snom_keys_get( $_SESSION['sudo_user']['name'] );
$keys = gs_keys_snom_get( $_SESSION['sudo_user']['name'] );
if (isGsError($keys)) {
	echo __('Fehler beim Abfragen.'), '<br />', $keys->getMsg();
	die();
}
if (! is_array($keys)) {
	echo __('Fehler beim Abfragen.');
	die();
}


?>

<form method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<input type="hidden" name="action" value="save" />

<table cellspacing="1">
<thead>
<tr>
	<th style="width:70px;" class="quickchars">&nbsp;</th>
	<th style="width:340px;"><?php echo __('Tastenbelegung'); ?></th>
	<th style="width:70px;" class="quickchars">&nbsp;</th>
</tr>
</thead>
<tbody>

<?php


$right = 6;
$left = 0;
for ($i=0; $i<12; ++$i) {
	echo '<tr class="', ($i%2 ? 'even':'odd'), '">', "\n";
	
	$knum = ($i%2 ? $left : $right);
	
	$keyv = 'P'. str_replace(' ', '&nbsp;', str_pad($knum+1, 2, ' ', STR_PAD_LEFT));
	
	echo '<td', ($i%2 ? '':' style="background:transparent;"'), '>';
	if ($i % 2)
		echo '<img alt="" src="', GS_URL_PATH, 'img/snom_fkleft_off.gif" /> ', $keyv;
	else
		echo '&nbsp;';
	echo '</td>', "\n";
	
	echo '<td class="', ($i%2 ? 'l':'r'), '">', "\n";
	$keyinfo = @$keys['f'.$knum];
	if (! is_array($keyinfo)) $keyinfo = array();
	$val = @$keyinfo['val'];
	/*
	if (preg_match('/:([^@]*)@/', $val, $m)) {
		# i.e. "dest <sip:*800001@192.168.1.130>"
		$val = $m[1];
	}
	*/
	if (@$keyinfo['rw'])
		echo '<input type="text" name="key-f', $knum, '" value="', htmlEnt($val), '" size="25" class="', ($i%2 ? 'l':'r'), '" maxlength="22" style="width:150px;" tabindex="', 5+$knum, '" />';
	else
		echo '<input type="text" name="key-f', $knum, '" value="', htmlEnt($val), '" size="25" class="', ($i%2 ? 'l':'r'), '" maxlength="22" style="width:150px;" tabindex="', 5+$knum, '" disabled="disabled" readonly="readonly" />';
	echo '</td>', "\n";
	
	echo '<td class="r"', ($i%2 ? ' style="background:transparent;"':''), '>';
	if (!($i % 2))
		echo $keyv, ' <img alt="" src="', GS_URL_PATH, 'img/snom_fkright_off.gif" />';
	else
		echo '&nbsp;';
	echo '</td>', "\n";
	
	echo '</tr>', "\n";
	
	if ($i % 2) ++$left;
	else ++$right;
}


?>

<tr>
	<td colspan="2" class="quickchars r">
		<br />
		<button type="submit">
			<img alt="" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
			<?php echo __('Speichern'); ?>
		</button>
	</td>
</tr>
<tr>
	<td class="quickchars">&nbsp;</td>
	<td class="quickchars">
		<b><?php echo __('Ver&auml;nderungen werden erst dann aktiv, sobald Sie Ihr Telefon neu gestartet haben!'); ?></b>
	</td>
</tr>
</tbody>
</table>

</form>

