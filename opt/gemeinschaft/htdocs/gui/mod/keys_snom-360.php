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
include_once( GS_DIR .'inc/gs-fns/gs_keys_get.php' );


echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


$phone_type = 'snom-360';

$GS_Softkeys = gs_get_key_prov_obj( $phone_type, $DB );
if (! $GS_Softkeys->set_user( $_SESSION['sudo_user']['name'] )) {
	echo __('Fehler beim Abfragen.');
	return;
}
if (! $GS_Softkeys->retrieve_keys( $phone_type )) {
	echo __('Fehler beim Abfragen.');
	return;
}
//$keys = $GS_Softkeys->get_keys();

echo '<table cellspacing="1">' ,"\n";
echo '<thead>' ,"\n";
echo '<tr>' ,"\n";
echo '<th>', __('Taste') ,'</th>' ,"\n";
echo '<th>', __('Funktion') ,'</th>' ,"\n";
echo '<th>', __('Nummer/Daten') ,'</th>' ,"\n";
echo '<th>', __('Beschriftung') ,'</th>' ,"\n";
echo '<th>', __('Gesperrt?') ,'</th>' ,"\n";
echo '<th>', __('Gesetzt von') ,'</th>' ,"\n";
echo '</tr>' ,"\n";
echo '</thead>' ,"\n";
echo '<tbody>' ,"\n";
for ($idx=0; $idx<=137; ++$idx) {
	echo '<tr>' ,"\n";
	$key_name = 'f'. str_pad($idx, 3, '0', STR_PAD_LEFT);
	$keydefs = $GS_Softkeys->get_key( $key_name );
	if (array_key_exists('slf', $keydefs)) {
		$key = $keydefs['slf'];
	} elseif (array_key_exists('inh', $keydefs)) {
		$key = $keydefs['inh'];
	}
	echo '<td>', htmlEnt($key['key']) ,'</td>' ,"\n";
	echo '<td>', htmlEnt($key['function']) ,'</td>' ,"\n";
	echo '<td>', htmlEnt($key['data']) ,'</td>' ,"\n";
	echo '<td>', htmlEnt($key['label']) ,'</td>' ,"\n";
	echo '<td>', ($key['user_writeable'] ? __('nein'):__('ja')) ,'</td>' ,"\n";
	echo '<td>';
	switch ($key['_set_by']) {
		case 'g': echo __('Gruppe') ,' #', $key['_setter']; break;
		case 'u': echo __('Benutzer')                     ; break;
		case 'p': echo __('autom.')                       ; break;
		default : echo    '?'                             ; break;
	}
	echo '</td>' ,"\n";
	echo '</tr>' ,"\n";
}
echo '</tbody>' ,"\n";
echo '</table>' ,"\n";



?>