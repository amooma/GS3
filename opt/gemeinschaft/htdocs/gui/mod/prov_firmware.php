<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2010, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
* Sebastian Ertz <gemeinschaft@swastel.eisfair.net>
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
include_once( GS_DIR .'inc/phone-capability.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


$phone_types = glob( GS_DIR .'htdocs/prov/*/capability.php' );
if (! is_array($phone_types)) $phone_types = array();
for ($i=0; $i<count($phone_types); ++$i) {
	$phone_types[$i] = baseName(dirName($phone_types[$i]));
}

foreach ($phone_types as $phone_type) {
	include_once( GS_DIR .'htdocs/prov/'. $phone_type .'/capability.php' );
	$class = 'PhoneCapability_'. $phone_type;
	if (! class_exists($class)) {
		gs_log(GS_LOG_WARNING, $phone_type .': Class broken.' );
		//$errors[] = $phone_type .': Class broken.';
		continue;
	}
	$PhoneCapa = new $class;
	
	$firmware_files = $PhoneCapa->get_firmware_files();
	if(!is_array($firmware_files) || $firmware_files == null) {
		continue;
	}
	echo '<table cellspacing="1">' ,"\n";
	echo '<thead>' ,"\n";
	echo '<tr><th>',strToUpper($phone_type),'</th></tr>' ,"\n";
	echo '</thead>' ,"\n";
	echo '<tbody>' ,"\n";
	
	foreach($firmware_files as $firmware_file) {
		echo '<tr><td>', $firmware_file, '</td></tr>' ,"\n";
	}
	
	echo '</tbody>' ,"\n";
	echo '</table>' ,"\n";
	echo '<br />' ,"\n";
	echo "\n";
}

?>