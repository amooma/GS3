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
include_once( GS_DIR .'inc/gs-fns/gs_queue_callforward_activate.php' );
include_once( GS_DIR .'inc/gs-fns/gs_queue_callforward_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_queue_callforward_set.php' );
include_once( GS_DIR .'inc/gs-fns/gs_queue_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_queues_get.php' );

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
/*
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
*/
echo __('Rufumleitung Warteschleifen');
echo '</h2>', "\n";


$sources = array(
	'internal' => __('intern'),
	'external' => __('extern')
);
$cases = array(
	'always' => __('immer'),
	'full'   => __('voll'),
	'timeout'=> __('keine Antw.'),
	'empty'  => __('leer')
);
$actives = array(
	'no'  => '-',
	'std' => __('Std.'),
	'var' => __('Tmp.')
);

$queue_ext = preg_replace('/[^\d]$/', '', @$_REQUEST['queue']);

$queues = @gs_queues_get();
if (isGsError($queues)) {
	echo __('Fehler beim Abfragen der Warteschlangen.'), ' - ', $queues->getMsg();
	return;  # return to parent file
} elseif (! is_array($queues)) {
	echo __('Fehler beim Abfragen der Warteschlangen.');
	return;  # return to parent file
}

$queue = null;
if ($queue_ext != '') {
	foreach ($queues as $q) {
		if ($q['name'] == $queue_ext) {
			$queue = $q;
			break;
		}
	}
}


$warnings = array();

if (@$_REQUEST['action']=='save' && $queue) {
	
	$num_std = preg_replace('/[^\d]/', '', @$_REQUEST['num-std']);
	$num_var = preg_replace('/[^\d]/', '', @$_REQUEST['num-var']);
	$timeout = abs((int)@$_REQUEST['timeout']);
	if ($timeout < 1) $timeout = 1;
	
	foreach ($sources as $src => $ignore) {
		foreach ($cases as $case => $gnore2) {
			$ret = gs_queue_callforward_set( $queue_ext,
				$src, $case, 'std', $num_std, $timeout );
			if (isGsError($ret))
				$warnings['std'] = __('Fehler beim Setzen der Std.-Umleitungsnummer') .' ('. $ret->getMsg() .')';
			$ret = gs_queue_callforward_set( $queue_ext,
				$src, $case, 'var', $num_var, $timeout );
			if (isGsError($ret))
				$warnings['var'] = __('Fehler beim Setzen der Tempor&auml;ren Umleitungsnummer') .' ('. $ret->getMsg() .')';
			$ret = gs_queue_callforward_activate( $queue_ext,
				$src, $case, @$_REQUEST[$src.'-'.$case] );
			if (isGsError($ret))
				$warnings['act'] = __('Fehler beim Aktivieren der Umleitungsnummer') .' ('. $ret->getMsg() .')';
		}
	}
	
}



?>

<form method="get" action="<?php echo GS_URL_PATH; ?>">
<?php echo __('Warteschlange'); ?>:
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<?php

if (count($queues) <= 25) {
	echo '<select name="queue" onchange="this.form.submit();">', "\n";
	foreach ($queues as $q) {
		echo '<option value="', $q['name'], '"', ($q['name']==$queue_ext ? ' selected="selected"' :''), '>', $q['name'], ' (', htmlEnt($q['title']), ')</option>', "\n";
	}
	echo '</select>', "\n";
} else {
	echo '<input type="text" name="queue" value="', $queue_ext, '" size="7" maxlength="6" />', "\n";
}

?>
<input type="submit" value="<?php echo __('Anzeigen'); ?>" />
</form>
<hr size="1" />
<br />

<?php





$queue_exists = false;
if ($queue_ext != '') {
	/*
	$queue = @gs_queue_get( $queue_ext );
	if (isGsError($queue))
		$warnings[] = __('Fehler beim Abfragen der Warteschlange.') .' - '. $queue->getMsg();
	elseif (! is_array($queue))
		$warnings[] = __('Fehler beim Abfragen der Warteschlange.');
	else {
		$cf = @gs_queue_callforward_get( $queue_ext );
		if (isGsError($cf))
			$warnings[] = __('Fehler beim Abfragen der Rufumleitungen der Warteschlange.') .' - '. $cf->getMsg();
		elseif (! is_array($cf))
			$warnings[] = __('Fehler beim Abfragen der Rufumleitungen der Warteschlange.');
		else
			$queue_exists = true;
	}
	*/
	if ($queue) {
		# get call forwards
		#
		$callforwards = @gs_queue_callforward_get( $queue_ext );
		if (isGsError($callforwards)) {
			echo __('Fehler beim Abfragen der Rufumleitungen der Warteschlange.') .' - '. $callforwards->getMsg();
			return;  # return to parent file
		} elseif (! is_array($callforwards)) {
			echo __('Fehler beim Abfragen der Rufumleitungen der Warteschlange.');
			return;  # return to parent file
		} else
			$queue_exists = true;
	}
}
if (! $queue_exists) {
	echo __('Bitte w&auml;hlen Sie eine Warteschleife.');
	return;  # return to parent file
}

echo '<h3>', $queue['name'];
if ($queue['title'] != '')
	echo ' (', htmlEnt($queue['title']), ')';
echo '</h3>';

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
<input type="hidden" name="queue" value="<?php echo $queue_ext; ?>" />

<table cellspacing="1">
<thead>
<tr>
	<th colspan="2"><?php echo __('Zielrufnummern f&uuml; Anrufumleitung'); ?></th>
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
//echo '<td style="width:80px;">', __('AB'), '</td>', "\n";

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
	<td colspan="3">&nbsp;</td>
	<td>
		<?php echo __('nach'); ?>
		<input type="text" name="timeout" value="<?php echo $timeout; ?>" size="3" maxlength="3" class="r" />&nbsp;s
	</td>
	<td>&nbsp;</td>
</tr>
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

