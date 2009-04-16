<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* 
* Author: Henning Holtschneider <henning@loca.net> - LocaNet oHG
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

require_once(GS_DIR . 'inc/gs-fns/gs_huntgroup_del.php');
require_once(GS_DIR . 'inc/gs-fns/gs_huntgroup_callforward_activate.php');
require_once(GS_DIR . 'inc/gs-fns/gs_huntgroup_callforward_get.php');
require_once(GS_DIR . 'inc/gs-fns/gs_huntgroup_callforward_set.php');
require_once(GS_DIR . 'inc/gs-fns/gs_huntgroup_user_add.php');
require_once(GS_DIR . 'inc/gs-fns/gs_huntgroup_user_del.php');
require_once(GS_DIR . 'inc/gs-fns/gs_huntgroups_get.php');

function trim_value(&$value)
{
	$value = trim($value);
}

$admins =  split(',',GS_GUI_SUDO_ADMINS);

array_walk($admins, 'trim_value');
if(!in_array($_SESSION[sudo_user][name], $admins)){
	die(__('Nur Administratoren d&uuml;rfen hier &Auml;nderungen vornehmen!'));
}

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
echo __('Rufumleitung Sammelanschl&uuml;sse');
echo '</h2>', "\n";


$sources = array(
	'internal' => __('intern'),
	'external' => __('extern')
);
$cases = array(
	'always' => __('immer'),
	'timeout'=> __('keine Antw.')
);
$actives = array(
	'no'  => '-',
	'std' => __('Std.'),
	'var' => __('Tmp.')
);

$huntgroup = preg_replace('/[^\d]$/', '', @$_REQUEST['huntgroup']);

$huntgroups = @gs_huntgroups_get();
if (isGsError($huntgroups)) {
	echo __('Fehler beim Abfragen der Sammelanschl&uuml;sse.'), ' - ', $huntgroups->getMsg();
	return;  # return to parent file
} elseif (! is_array($huntgroups)) {
	echo __('Fehler beim Abfragen der Sammelanschl&uuml;sse.');
	return;  # return to parent file
}

$warnings = array();

if (@$_REQUEST['action']=='save' && $huntgroup) {
    gs_log( GS_LOG_NOTICE, 'saving hunt group cf for ' . $huntgroup);
	
	$num_std = preg_replace('/[^\d]/', '', @$_REQUEST['num-std']);
	$num_var = preg_replace('/[^\d]/', '', @$_REQUEST['num-var']);
	$timeout = abs((int)@$_REQUEST['timeout']);
	if ($timeout < 1) $timeout = 1;
	
	foreach ($sources as $src => $ignore) {
		foreach ($cases as $case => $gnore2) {
			$ret = gs_huntgroup_callforward_set( $huntgroup, $src, $case, 'std', $num_std, $timeout );
			if (isGsError($ret))
				$warnings['std'] = __('Fehler beim Setzen der Std.-Umleitungsnummer') .' ('. $ret->getMsg() .')';
			$ret = gs_huntgroup_callforward_set( $huntgroup, $src, $case, 'var', $num_var, $timeout );
			if (isGsError($ret))
				$warnings['var'] = __('Fehler beim Setzen der Tempor&auml;ren Umleitungsnummer') .' ('. $ret->getMsg() .')';
			$ret = gs_huntgroup_callforward_activate( $huntgroup, $src, $case, @$_REQUEST[$src.'-'.$case] );
			if (isGsError($ret))
				$warnings['act'] = __('Fehler beim Aktivieren der Umleitungsnummer') .' ('. $ret->getMsg() .')';
		}
	}
	
}



?>

<form method="get" action="<?php echo GS_URL_PATH; ?>">
<?php echo __('Sammelanschluss'); ?>:
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<?php

if (count($huntgroups) <= 25) {
	echo '<select name="huntgroup" onchange="this.form.submit();">', "\n";
	foreach ($huntgroups as $h) {
		echo '<option value="', $h['number'], '"', ($h['number']==$huntgroup ? ' selected="selected"' :''), '>', $h['number'], '</option>', "\n";
	}
	echo '</select>', "\n";
} else {
	echo '<input type="text" name="huntgroup" value="', $huntgroup, '" size="7" maxlength="6" />', "\n";
}

?>
<input type="submit" value="<?php echo __('Anzeigen'); ?>" />
</form>
<hr size="1" />
<br />

<?php

$huntgroup_exists = false;
if ($huntgroup) {
	# get call forwards
	#
	$callforwards = @gs_huntgroup_callforward_get( $huntgroup );
	if (isGsError($callforwards)) {
		echo __('Fehler beim Abfragen der Rufumleitungen des Sammelanschlusses.') .' - '. $callforwards->getMsg();
		return;  # return to parent file
	} elseif (! is_array($callforwards)) {
		echo __('Fehler beim Abfragen der Rufumleitungen des Sammelanschlusses.');
		return;  # return to parent file
	} else
		$huntgroup_exists = true;
}

if (! $huntgroup_exists) {
	echo __('Bitte w&auml;hlen Sie einen Sammelanschluss.');
	return;  # return to parent file
}

echo '<h3>', $huntgroup, '</h3>';

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
if ( @$callforwards['internal']['timeout']['active'] != 'no'
  && @$callforwards['external']['timeout']['active'] != 'no' )
{
	$timeout = ceil((
		(int)@$callforwards['internal']['timeout']['timeout'] +
		(int)@$callforwards['external']['timeout']['timeout']
	)/2);
} elseif (@$callforwards['internal']['timeout']['active'] != 'no') {
	$timeout = (int)@$callforwards['internal']['timeout']['timeout'];
} elseif (@$callforwards['external']['timeout']['active'] != 'no') {
	$timeout = (int)@$callforwards['external']['timeout']['timeout'];
} else {
	$timeout = 15;
}



if (is_array($warnings) && count($warnings) > 0) {
?>
	<div style="max-width:600px;">
	<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/16/app/important.png" class="fl" />
	<p style="margin-left:22px;">
		<?php echo implode('<br />', $warnings); ?>
	</p>
</div>
<?php
}


?>

<form method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<input type="hidden" name="action" value="save" />
<input type="hidden" name="huntgroup" value="<?php echo $huntgroup; ?>" />

<table cellspacing="1">
<thead>
<tr>
	<th colspan="2"><?php echo __('Umleitungsnummern'); ?></th>
</tr>
</thead>
<tbody>
<tr class="even">
	<td style="width:170px;"><?php echo __('Standardnummer'); ?></td>
	<td style="width:299px;">
		<input type="text" name="num-std" value="<?php echo htmlEnt($number_std); ?>" size="30" style="width:220px;" maxlength="25" />
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
	<th colspan="5"><?php echo __('Umleiten in folgenden F&auml;llen'); ?></th>
</tr>
</thead>
<tbody>
<tr class="even">
	<td>&nbsp;</td>
<?php

foreach ($cases as $case => $ctitle) {
	echo '<td style="width:85px;">', $ctitle, '</td>', "\n";
}
//echo '<td style="width:80px;">', __('Mailbox'), '</td>', "\n";

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
	
	/*
	echo '<td>';
	echo '<select name="vm-', $src, '" />', "\n";
	echo '<option value="1"', $s, ($vm[$src .'_active'] ? ' selected="selected"' : ''), '>', __('An'), '</option>', "\n";
	echo '<option value="0"', $s, (!$vm[$src .'_active'] ? ' selected="selected"' : ''), '>', __('Aus'), '</option>', "\n";
	echo '</select>';
	echo '</td>', "\n";
	*/
	
	echo '</tr>', "\n";
}
?>


<tr>
	<td colspan="6" class="quickchars r">
		<br />
		<button type="submit">
			<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
			<?php echo __('Speichern'); ?>
		</button>
	</td>
</tr>
</tbody>
</table>

</form>

