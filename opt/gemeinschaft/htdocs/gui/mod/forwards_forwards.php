<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1752 $
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
/*
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
*/
echo __('Rufumleitung');
echo '</h2>', "\n";


$sources = array(
	'internal' => __('intern'),
	'external' => __('extern')
);
$cases = array(
	'always' => __('immer'),
	'busy'   => __('besetzt'),
	'unavail'=> __('keine Antw.'),
	//'offline'=> __('offline/DND'),
	'offline'=> __('offline')
);
$actives = array(
	'no'  => '-',
	'std' => __('Std.'),
	'var' => __('Tmp.')
);




$warnings = array();

if (@$_REQUEST['action']=='save') {
	
	$num_std = preg_replace('/[^\d]/', '', @$_REQUEST['num-std']);
	$num_var = preg_replace('/[^\d]/', '', @$_REQUEST['num-var']);
	$timeout = abs((int)@$_REQUEST['timeout']);
	if ($timeout < 1) $timeout = 1;
	
	if ($num_std=='')
		$warnings['std-empty'] = __('Sie sollten eine Std.-Umleitungsnummer angeben! Sie wird f&uuml;r die Nicht-St&ouml;ren-Funktion am Telefon ben&ouml;tigt.');
	
	foreach ($sources as $src => $ignore) {
		foreach ($cases as $case => $gnore2) {
			$ret = gs_callforward_set( $_SESSION['sudo_user']['name'],
				$src, $case, 'std', $num_std, $timeout );
			if (isGsError($ret))
				$warnings['std'] = __('Fehler beim Setzen der Std.-Umleitungsnummer') .' ('. $ret->getMsg() .')';
			$ret = gs_callforward_set( $_SESSION['sudo_user']['name'],
				$src, $case, 'var', $num_var, $timeout );
			if (isGsError($ret))
				$warnings['var'] = __('Fehler beim Setzen der Tempor&auml;ren Umleitungsnummer') .' ('. $ret->getMsg() .')';
			$ret = gs_callforward_activate( $_SESSION['sudo_user']['name'],
				$src, $case, @$_REQUEST[$src.'-'.$case] );
			if (isGsError($ret))
				$warnings['act'] = __('Fehler beim Aktivieren der Umleitungsnummer') .' ('. $ret->getMsg() .')';
		}
	}
	
	$vm_internal = (bool)@$_REQUEST['vm-internal'];
	$vm_external = (bool)@$_REQUEST['vm-external'];
	gs_vm_activate( $_SESSION['sudo_user']['name'], 'internal', $vm_internal );
	gs_vm_activate( $_SESSION['sudo_user']['name'], 'external', $vm_external );
	
}




# get call forwards
#
$callforwards = gs_callforward_get( $_SESSION['sudo_user']['name'] );
if (isGsError($callforwards)) {
	echo __('Fehler beim Abfragen.'), '<br />', $callforwards->getMsg();
	return;  # return to parent file
}

# find best match for std number
#
$number_std = '';
foreach ($callforwards as $_source => $_cases) {
	foreach ($_cases as $_case => $_info) {
		if ($_info['number_std'] != '') {
			$number_std = $_info['number_std'];
			break;
		}
	}
}
foreach ($callforwards as $_source => $_cases) {
	foreach ($_cases as $_case => $_info) {
		if ($_info['number_std'] != '' && $_info['active']=='std') {
			$number_std = $_info['number_std'];
			break;
		}
	}
}
if ($number_std=='')
	$warnings['std-empty'] = __('Sie sollten eine Std.-Umleitungsnummer angeben! Sie wird f&uuml;r die Nicht-St&ouml;ren-Funktion am Telefon ben&ouml;tigt.');

# find best match for var number
#
$number_var = '';
foreach ($callforwards as $_source => $_cases) {
	foreach ($_cases as $_case => $_info) {
		if ($_info['number_var'] != '') {
			$number_var = $_info['number_var'];
			break;
		}
	}
}
foreach ($callforwards as $_source => $_cases) {
	foreach ($_cases as $_case => $_info) {
		if ($_info['number_var'] != '' && $_info['active']=='var') {
			$number_var = $_info['number_var'];
			break;
		}
	}
}

# find best match for unavail timeout
#
if ( @$callforwards['internal']['unavail']['active'] != 'no'
  && @$callforwards['external']['unavail']['active'] != 'no' )
{
	$timeout = ceil((
		(int)@$callforwards['internal']['unavail']['timeout'] +
		(int)@$callforwards['external']['unavail']['timeout']
	)/2);
} elseif (@$callforwards['internal']['unavail']['active'] != 'no') {
	$timeout = (int)@$callforwards['internal']['unavail']['timeout'];
} elseif (@$callforwards['external']['unavail']['active'] != 'no') {
	$timeout = (int)@$callforwards['external']['unavail']['timeout'];
} else {
	$timeout = 15;
}


# get vm states
#
$vm = gs_vm_get( $_SESSION['sudo_user']['name'] );
if (isGsError($vm)) {
	echo __('Fehler beim Abfragen.'), '<br />', $vm->getMsg();
	return;  # return to parent file
}



if (is_array($warnings) && count($warnings) > 0) {
?>
	<div style="max-width:600px;">
	<img alt="" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/app/important.png" class="fl" />
	<p style="margin-left:22px;">
		<?php echo implode('<br />', $warnings); ?>
	</p>
</div>
<?php
}
?>

<form method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<input type="hidden" name="action" value="save" />

<table cellspacing="1">
<thead>
<tr>
	<th colspan="2"><?php echo __('Umleitungsnummern'); ?></th>
</tr>
</thead>
<tbody>
<tr class="even">
	<td style="width:170px;"><?php echo __('Standardnummer'); ?></td>
	<td style="width:392px;">
		<input type="text" name="num-std" value="<?php echo htmlEnt($number_std); ?>" size="30" style="width:220px;" maxlength="25" />
		<small>(<?php echo __('nicht leer!'); ?>)</small>
	</td>
</tr>
<tr class="even">
	<td><?php echo __('Tempor&auml;re Nummer'); ?></td>
	<td>
		<input type="text" name="num-var" value="<?php echo htmlEnt($number_var); ?>" size="30" style="width:220px;" maxlength="25" />
	</td>
</tr>
</tbody>
</table>

<br />

<table cellspacing="1">
<thead>
<tr>
	<th colspan="6"><?php echo __('Umleiten in folgenden F&auml;llen'); ?></th>
</tr>
</thead>
<tbody>
<tr class="even">
	<td>&nbsp;</td>
<?php

foreach ($cases as $case => $ctitle) {
	echo '<td style="width:85px;">', $ctitle, '</td>', "\n";
}
echo '<td style="width:80px;">', __('Mailbox'), '</td>', "\n";

?>
</tr>
<?php
foreach ($sources as $src => $srctitle) {
	echo '<tr>';
	echo '<td style="width:90px;">', __('von'), ' ', $srctitle, '</td>';
	
	foreach ($cases as $case => $ctitle) {
		echo '<td>';
		echo '<select name="', $src, '-', $case, '" />', "\n";
		foreach ($actives as $active => $atitle) {
			$s = ($callforwards[$src][$case]['active'] == $active) ? ' selected="selected"' : '';
			echo '<option value="', $active, '"', $s, '>', $atitle, '</option>', "\n";
		}
		echo '</select>';
		echo '</td>', "\n";
	}
	
	echo '<td>';
	echo '<select name="vm-', $src, '" />', "\n";
	echo '<option value="1"', $s, ($vm[$src .'_active'] ? ' selected="selected"' : ''), '>', __('An'), '</option>', "\n";
	echo '<option value="0"', $s, (!$vm[$src .'_active'] ? ' selected="selected"' : ''), '>', __('Aus'), '</option>', "\n";
	echo '</select>';
	echo '</td>', "\n";
	
	echo '</tr>', "\n";
}
?>

<tr>
	<td colspan="3">&nbsp;</td>
	<td>
		<?php echo __('nach'); ?>
		<input type="text" name="timeout" value="<?php echo $timeout; ?>" size="3" maxlength="3" class="r" />&nbsp;s
	</td>
	<td colspan="2">&nbsp;</td>
</tr>
<tr>
	<td colspan="6" class="quickchars">
		<small><?php echo __('Achtung: Ihre Mailbox wird nur dann aktiv, wenn Sie keine Weiterleitung eingestellt haben.'); ?></small>
	</td>
</tr>
<tr>
	<td colspan="6" class="quickchars r">
		<br />
		<button type="submit">
			<img alt="" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
			<?php echo __('Speichern'); ?>
		</button>
	</td>
</tr>
</tbody>
</table>

</form>

