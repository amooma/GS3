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
*    (de)activates a call forward for a hunt group, either the
*    standard or variable forward number
***********************************************************/

function gs_huntgroup_callforward_activate( $huntgroup, $source, $case, $active )
{
	if (! preg_match( '/^[\d]+$/', $huntgroup ))
		return new GsError( 'Hunt group must be alphanumeric.' );
	if (! in_array( $source, array('internal','external'), true ))
		return new GsError( 'Source must be internal|external.' );
	if (! in_array( $case, array('always','full','timeout','empty'), true ))
		return new GsError( 'Case must be always|full|timeout|empty.' );
	if (! in_array( $active, array('no','std','var'), true ))
		return new GsError( 'Active must be no|std|var.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# check if hunt group has an entry
	#
	$num = $db->executeGetOne( 'SELECT COUNT(*) FROM `huntgroups_callforwards` WHERE `huntgroup`='. $huntgroup .' AND `source`=\''. $db->escape($source) .'\' AND `case`=\''. $db->escape($case) .'\'' );
	if ($num < 1)
		$ok = $db->execute( 'INSERT INTO `huntgroups_callforwards` (`huntgroup`, `source`, `case`, `number_std`, `number_var`, `active`) VALUES ('. $huntgroup .', \''. $db->escape($source) .'\', \''. $db->escape($case) .'\', \'\', \'\', \'no\')' );
	else
		$ok = true;
	
	# set state
	#
	$ok = $ok && $db->execute(
'UPDATE `huntgroups_callforwards` SET
	`active`=\''. $db->escape($active) .'\'
WHERE
	`huntgroup`='. $huntgroup .' AND
	`source`=\''. $db->escape($source) .'\' AND
	`case`=\''. $db->escape($case) .'\'
LIMIT 1'
	);
	if (! $ok)
		return new GsError( 'Failed to set call forwarding status.' );
	
	# do not allow an empty number to be active
	#
	if ($active != 'no') {
		$field = 'number_'. $active;
		$number = $db->executeGetOne( 'SELECT `'. $field .'` FROM `huntgroups_callforwards` WHERE `huntgroup`='. $huntgroup .' AND `source`=\''. $db->escape($source) .'\' AND `case`=\''. $db->escape($case) .'\'' );
		if (trim($number)=='') {
			$db->execute( 'UPDATE `huntgroups_callforwards` SET `active`=\'no\' WHERE `huntgroup`='. $huntgroup .' AND `source`=\''. $db->escape($source) .'\' AND `case`=\''. $db->escape($case) .'\'' );
			return new GsError( 'Number is empty. Cannot activate call forward.' );
		}
	}
	
	return true;
}


?>