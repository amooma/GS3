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



echo '<h3>Extension 99</h3>' ,"\n";

$err=0; $out=array();
@exec( GS_DIR .'ivrs/gen-ivr-pretty.php', $out, $err );
if ($err != 0) {
	echo "<p>Error.</p>\n";
} else {
	$out = trim(implode("\n", $out));
	/*
	echo '<form method="post" action="', GS_URL_PATH,'">',"\n";
	
	//echo "<pre style=\"margin:0.1em 0.5em 1.2em 0.5em;\">";
	echo '<textarea name="" cols="70" rows="50" style="font-family:Courier, monospace; font-size:10px; line-height:1.1em; padding:0.3em; white-space:pre; color:#000; background:#eee;" disabled="disabled">',"\n";
	echo htmlEnt($out);
	//echo "\n</pre>\n";
	echo "\n</textarea>\n";
	
	echo '</form>',"\n";
	*/
	
	echo '<pre cols="80" style="font-family:Courier, monospace; font-size:10px; line-height:1.1em; padding:0.4em; white-space:pre; color:#000; background:#f0f0f0; border-width:1px; border-style:solid; border-color:#aaa #ddd #ddd #aaa;">',"\n";
	$out = htmlEnt($out);
	$out = preg_replace('/(?<=[ \t])([0-9*#tioa]{1,8})(?=[ \t]+(PLAY|APP))/S', '<span style="color:#00e;">$1</span>', $out);
	$out = preg_replace('/(PLAY|APP)/S', '<span style="color:#0c0;">$1</span>', $out);
	echo $out;
	echo "\n</pre>\n";
}


echo '<br />',"\n";
echo '<b>Generierter Dialplan:</b>',"\n";

$err=0; $out=array();
@exec( GS_DIR .'ivrs/gen-ivr.php', $out, $err );
if ($err != 0) {
	echo "<p>Error.</p>\n";
} else {
	$out = trim(implode("\n", $out));
	echo '<pre cols="80" style="font-family:Courier, monospace; font-size:10px; line-height:1.1em; padding:0.4em; white-space:pre; color:#000; background:#f0f0f0; border-width:1px; border-style:solid; border-color:#aaa #ddd #ddd #aaa;">',"\n";
	echo htmlEnt($out);
	echo "\n</pre>\n";
}




?>
