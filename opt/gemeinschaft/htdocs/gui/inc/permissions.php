<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1769 $
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

require_once( GS_DIR .'htdocs/gui/inc/pamal/pamal.php' );
require_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'inc/ldap.php' );


/*
In eine ggf. vom Admin anpaßbare Funktion ausgelagert, da es sich
generisch nicht definieren läßt, daß für die Editier-Rechte die
ersten 4 Stellen der LVM-Kostenstelle aus dem LDAP ausschlaggebend
sind.
Muß true oder false zurückgeben.

Info für LVM: Der Funktion wird die Personalnummer übergeben.

Um diese Kostenstellen-Lookups abzuschalten einfach in der
inc/conf.php GS_GUI_SUDO_EXTENDED auf false setzen.
*/


function gui_sudo_allowed( $real_user, $sudo_user )
{
	$kkr = @_get_kostenstellen( $real_user );
	if ($kkr == false || ! is_array( $kkr )) return false;
	$kks = @_get_kostenstellen( $sudo_user );
	if ($kks == false || ! is_array( $kks )) return false;
	
	foreach ($kkr as $kr)
		foreach ($kks as $ks)
			if (subStr($kr,0,4) == subStr($ks,0,4)) return true;
	return false;
}


function _get_kostenstellen( $user )
{
	//$kostenstelle_prop = 'carLicense';
	$kostenstelle_prop = 'lvmkostenstelle';
	
	$ldap = gs_ldap_connect();
	if (GS_LVM_USER_6_DIGIT_INT) {
		$user = preg_replace('/^0+/', '', $user);
		// sind im LVM-LDAP ohne führende 0
	}
	$u = gs_ldap_get_first( $ldap, GS_LDAP_SEARCHBASE,
		'('. GS_LDAP_PROP_USER .'='. $user .')',
		array($kostenstelle_prop) );
	if (isGsError( $u )) return false;
	if (! is_array( $u )) {
		echo "Failed to get (". GS_LDAP_PROP_USER ."=". $user .") from LDAP.\n";
		return false;
	}
	$kostenstelle_prop = strToLower($kostenstelle_prop);
	if (! is_array( $u[$kostenstelle_prop] )) return array();
	return $u[$kostenstelle_prop];
}


function gui_monitor_which_peers( $sudo_user )
{
	$kks = @_get_kostenstellen( $sudo_user );
	if ($kks == false || ! is_array( $kks )) return false;
	
	//$kostenstelle_prop = 'carLicense';
	$kostenstelle_prop = 'lvmkostenstelle';
	$limit = 100;
	
	$filter = '';
	foreach ($kks as $ks) {
		$filter .= '('. $kostenstelle_prop .'='. subStr($ks,0,4) .'*)';
	}
	$filter = '(|'. $filter .')';
	//echo $filter, "<br />\n";
	
	$ldap = gs_ldap_connect();
	$matches = gs_ldap_get_list( $ldap, GS_LDAP_SEARCHBASE,
		$filter, array(GS_LDAP_PROP_USER), (int)$limit );
	if (isGsError( $matches )) return false;
	if (! is_array( $matches )) return false;
	/*
	echo "<pre>";
	print_r($matches);
	echo "</pre>";
	*/
	
	$lc_GS_LDAP_PROP_USER = strToLower(GS_LDAP_PROP_USER);
	$peers = array();
	foreach ($matches as $match) {
		if (! is_array( $match[$lc_GS_LDAP_PROP_USER] )) continue;
		foreach ($match[$lc_GS_LDAP_PROP_USER] as $mm) {
			if (GS_LVM_USER_6_DIGIT_INT) {
				$mm = str_pad($mm, 6, '0', STR_PAD_LEFT);
				// sind im LVM-LDAP ohne führende 0
			}
			$peers[] = $mm;
		}
	}
	/*
	echo "<pre>";
	print_r($peers);
	echo "</pre>";
	*/
	
	return $peers;
}


?>