<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision:2656 $
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
include_once( GS_DIR .'inc/gs-lib.php' );
include_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );


echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";








# get gateway groups from DB
#
$rs = $DB->execute( 'SELECT `id`, `title`, `type` FROM `gate_grps` ORDER BY `title`' );
$gate_grps = array();
while ($r = $rs->fetchRow())
	$gate_grps[] = $r;

foreach ($gate_grps as $gate_grp) {
	
?>

<table cellspacing="1" style="width:600px;">
<tbody>

<?php
	echo '<tr>',"\n";
	
	echo '<th style="width:18px;">';
	//echo '<img alt=" " src="', GS_URL_PATH ,'crystal-svg/16/fld/network.png" />';
	echo '</th>',"\n";
	
	echo '<th colspan="2" style="width:175px;">';
	echo htmlEnt($gate_grp['title']);
	echo '</th>',"\n";
	
	echo '<th colspan="2" style="width:370px;">';
	switch (strToLower($gate_grp['type'])) {
		case 'balance': echo 'load balance'; break;
		default       : echo htmlEnt($gate_grp['type']);
	}
	echo '</th>',"\n";
	
	echo '</tr>',"\n";
	
	$rs = $DB->execute( 'SELECT `id`, `type`, `name`, `title`, `allow_out`, `allow_in`, `dialstr` FROM `gates` WHERE `grp_id`='. $gate_grp['id'] );
	$i=0;
	while ($gw = $rs->fetchRow()) {
		echo '<tr class="', ($i%2?'even':'odd') ,'">',"\n";
		
		echo '<td style="width:18px;">', '<img alt=" " src="', GS_URL_PATH ,'crystal-svg/16/fld/socket.png" />' ,'</td>',"\n";
		
		echo '<td style="width:30px;">';
		switch (strToLower($gw['type'])) {
			case 'sip': echo 'SIP'; break;
			case 'zap': echo 'Zap'; break;
			default   : echo htmlEnt($gw['type']);
		}
		echo '</td>',"\n";
		
		echo '<td style="width:130px;">', htmlEnt($gw['title']) ,'</td>',"\n";
		
		echo '<td style="width:170px;"><tt>', htmlEnt($gw['name']) ,'</tt></td>',"\n";
		
		echo '<td style="width:170px;"><tt>', htmlEnt($gw['dialstr']) ,'</tt></td>',"\n";
		
		echo '</tr>',"\n";
		++$i;
	}
	if ($i==0) {
		echo '<tr>',"\n";
		echo '<td>&nbsp;</td>',"\n";
		echo '<td colspan="4">-</td>',"\n";
		echo '</tr>',"\n";
	}
?>

</tbody>
</table>
<br />

<?php
}
?>

