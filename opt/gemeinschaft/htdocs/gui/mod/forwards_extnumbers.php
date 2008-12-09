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
/*
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
*/
echo __('Eigene externe Nummern');
echo '</h2>', "\n";


include_once( GS_DIR .'inc/gs-lib.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_external_numbers_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_external_number_add.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_external_number_del.php' );


$add_number    = trim(@$_REQUEST['add'   ]);
$delete_number = trim(@$_REQUEST['delete']);

if ($delete_number != '') {
	$ret = gs_user_external_number_del( $_SESSION['sudo_user']['name'], $delete_number );
	if (isGsError($ret)) {
		echo '<div class="errorbox">';
		echo htmlEnt(__("Fehler beim L\xC3\xB6schen")) ,'<br />', htmlEnt($ret->getMsg());
		echo '</div>',"\n";
	}
	elseif (! $ret) {
		echo '<div class="errorbox">';
		echo htmlEnt(__("Fehler beim L\xC3\xB6schen"));
		echo '</div>',"\n";
	}
}

if ($add_number != '') {
	$ret = gs_user_external_number_add( $_SESSION['sudo_user']['name'], $add_number );
	if (isGsError($ret)) {
		echo '<div class="errorbox">';
		echo htmlEnt(__("Fehler beim Speichern")) ,'<br />', htmlEnt($ret->getMsg());
		echo '</div>',"\n";
	}
	elseif (! $ret) {
		echo '<div class="errorbox">';
		echo htmlEnt(__("Fehler beim Speichern"));
		echo '</div>',"\n";
	}
}

$enumbers = gs_user_external_numbers_get( $_SESSION['sudo_user']['name'] );
if (isGsError($enumbers)) {
	echo '<div class="errorbox">';
	echo htmlEnt(__("Fehler beim Abfragen")) ,'<br />', htmlEnt($enumbers->getMsg());
	echo '</div>',"\n";
	return;
}
elseif (! is_array($enumbers)) {
	echo '<div class="errorbox">';
	echo __("Fehler beim Abfragen");
	echo '</div>',"\n";
	return;
}

?>

<p><?php echo __('Au&szlig;er auf interne Nummern d&uuml;rfen Sie auf folgende externe Nummern weiterleiten:'); ?></p>

<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:200px;"><?php echo __('Externe Nummern'); ?></th>
	<th style="width:20px;"></th>
</tr>
</thead>
<tbody>

<?php

if (count($enumbers) < 1) {
	echo '<tr><td><i>- ', __('keine'), ' -</i></td></tr>';
} else {
	foreach ($enumbers as $enumber) {
		echo '<tr>';
		echo '<td>', htmlEnt( $enumber ), '</td>';
		echo '<td>';
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='.urlEncode($enumber)), '" title="', __('entfernen'), '"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
		echo '</td>';
		echo '</tr>', "\n";
	}
}

echo '<tr>';
echo '<td>';
echo '<input type="text" name="add" value="" size="20" maxlength="30" />';
echo '</td>';
echo '<td>';
echo '<button type="submit" title="', __('Eintrag speichern') ,'" class="plain"><img alt="', __('Speichern') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/filesave.png" /></button>';
echo '</td>';
echo '</tr>';
?>

</tbody>
</table>
</form>
<br />
<br />


