<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* 
* Author: Henning Holtschneider <henning@loca.net> - LocaNet oHG
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


/***********************************************************
*    returns a hunt group's call forwards
***********************************************************/

function gs_huntgroup_callforward_get( $huntgroup )
{
	if (! preg_match( '/^[\d]+$/', $huntgroup ))
		return new GsError( 'Hunt group must be numeric.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get states
	#
	$sources = array( 'internal', 'external' );
	$cases = array( 'always', 'full', 'timeout', 'empty' );
	$forwards = array();
	foreach ($sources as $source) {
		foreach ($cases as $case) {
			$rs = $db->execute( 'SELECT `active`, `number_std`, `number_var`, `timeout` FROM `huntgroups_callforwards` WHERE `huntgroup`='. $huntgroup .' AND `source`=\''. $source .'\' AND `case`=\''. $case .'\'' );
			if ($r = $rs->fetchRow()) {
				if (! in_array( $r['active'], array('no','std','var'), true ))
					$r['active'] = 'no';
				$forwards[$source][$case] = $r;
			} else {
				$forwards[$source][$case] = array(
					'active'     => 'no',
					'number_std' => '',
					'number_var' => '',
					'timeout'    => 20
				);
			}
			if ($case != 'timeout') $forwards[$source][$case]['timeout'] = null;
		}
	}
	return $forwards;
}


?>