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


include_once( GS_DIR .'inc/pci-cards-detect.php' );

$cards = gs_pci_cards_detect();
if (! is_array($cards)) {
	echo "<p><tt>lspci</tt> not found.</p>\n";
	return;
}


echo '<table cellspacing="1">' ,"\n";
echo '<thead>' ,"\n";
echo '<th>Vendor</th>' ,"\n";
echo '<th>Device</th>' ,"\n";
echo '<th>Rev.</th>' ,"\n";
echo '</thead>' ,"\n";
echo '<tbody>' ,"\n";

$i=0;
foreach ($cards as $c) {
	if ($c['known']) {
		echo '<tr class="', ($i%2 ? 'even':'odd') ,'">' ,"\n";
		echo '<td>', ($c['vendor'] != '' ? htmlEnt($c['vendor']) : '?') ,'</td>' ,"\n";
		echo '<td>', ($c['descr'] != '' ? htmlEnt($c['descr']) : '&nbsp;') ,'</td>' ,"\n";
		echo '<td class="r">', ($c['revision'] != '' ? htmlEnt($c['revision']) : '&nbsp;') ,'</td>' ,"\n";
		echo '</tr>' ,"\n";
		++$i;
	}
}
if ($i == 0) {
	echo '<tr>' ,"\n";
	echo '<td colspan="3">', '--' ,'</td>' ,"\n";
	echo '</tr>' ,"\n";
}

echo '</tbody>' ,"\n";
echo '</table>' ,"\n";


?>
