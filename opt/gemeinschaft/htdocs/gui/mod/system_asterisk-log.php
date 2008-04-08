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

echo "<br />\n";


@exec( 'sudo asterisk -rx \'core set verbose 1\' 1>>/dev/null 2>>/dev/null', $out, $err );


echo '<b>', 'tail -n 250 /var/log/asterisk/messages' ,'</b>' ,"\n";
$err=0; $out=array();
@exec( 'sudo tail -n 250 /var/log/asterisk/messages 2>>/dev/null | grep -v -i '. qsa('Empty Extension') .' 2>>/dev/null', $out, $err );
if ($err !== 0) {
	echo '-';
	return;
}
echo '<pre style="background:#eee; border:1px solid #ccc; padding:0.2em; overflow:scroll;">' ,"\n";
foreach ($out as $line) {
	echo htmlEnt($line) ,"\n";
}
echo '</pre>' ,"\n";


?>
