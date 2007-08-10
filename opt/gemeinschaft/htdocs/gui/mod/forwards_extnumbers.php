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
/*
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
*/
echo __('Eigene externe Nummern');
echo '</h2>', "\n";


include_once( GS_DIR .'inc/gs-lib.php' );


$enumbers = gs_user_external_numbers_get( $_SESSION['sudo_user']['name'] );
if (isGsError($enumbers)) {
	echo __('Fehler beim Abfragen.'), '<br />', $enumbers->getMsg();
	die();
}

?>

<p><?php echo __('Au&szlig;er auf interne Nummern d&uuml;rfen Sie auf folgende externe Nummern weiterleiten:'); ?></p>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:200px;"><?php echo __('Externe Nummern'); ?></th>
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
		echo '</tr>', "\n";
	}
}

?>

</tbody>
</table>
<br />
<br />


<p style="max-width:500px;"><?php echo __('Bitte wenden Sie sich an die Administratoren der Telefonanlage, falls die Nummern nicht korrekt sind oder falls weitere hinzugef&uuml;gt werden sollen.'); ?></p>
