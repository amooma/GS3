<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1119 $
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

require_once( GS_DIR .'inc/util.php' );


function gs_get_listen_to_ips()
{
	$file = GS_DIR .'etc/listen-to-ip';
	if (! @file_exists( $file )) return false;
	if (! is_array($lines = @file( $file ))) return false;
	$ips = array();
	foreach ($lines as $line) {
		$line = trim($line);
		if ($line=='' || @$line[0]=='#') continue;
		if (! preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $line, $m)) continue;
		$ips[] = normalizeIPs( $m[0] );
	}
	// remove duplicates:
	$ips = array_flip(array_flip( $ips ));
	sort($ips);
	return $ips;
}


?>