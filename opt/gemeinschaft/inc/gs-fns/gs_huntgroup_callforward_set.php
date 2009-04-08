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
*    set a call forward for a hunt group
***********************************************************/

function gs_huntgroup_callforward_set( $huntgroup, $source, $case, $type, $number, $timeout=20 )
{
	if (! preg_match( '/^[\d]+$/', $huntgroup ))
		return new GsError( 'Hunt group must be numeric.' );
	if (! in_array( $source, array('internal','external'), true ))
		return new GsError( 'Source must be internal|external.' );
	if (! in_array( $case, array('always','full','timeout','empty'), true ))
		return new GsError( 'Case must be always|full|timeout|empty.' );
	if (! in_array( $type, array('std','var'), true ))
		return new GsError( 'Type must be std|var.' );
	$number = preg_replace( '/[^\d]/', '', $number );
	$timeout = (int)$timeout;
	if ($case != 'timeout') $timeout = 0;
	else {
		if ($timeout > 250) $timeout = 250;
		elseif ($timeout < 1) $timeout = 1;
	}
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# check if has call forward
	#
	$num = $db->executeGetOne( 'SELECT COUNT(*) FROM `huntgroups_callforwards` WHERE `huntgroup`='. $huntgroup .' AND `source`=\''. $db->escape($source) .'\' AND `case`=\''. $db->escape($case) .'\'' );
	if ($num < 1)
		$ok = $db->execute( 'INSERT INTO `huntgroups_callforwards` (`huntgroup`, `source`, `case`, `timeout`, `number_std`, `number_var`, `active`) VALUES ('. $huntgroup .', \''. $db->escape($source) .'\', \''. $db->escape($case) .'\', 0, \'\', \'\', \'no\')' );
	else
		$ok = true;
	
	# set call forward
	#
	$field = 'number_'. $type;
	$ok = $ok && $db->execute(
'UPDATE `huntgroups_callforwards` SET
	`'. $field .'`=\''. $db->escape($number) .'\',
	`timeout`='. $timeout .'
WHERE
	`huntgroup`='. $huntgroup .' AND
	`source`=\''. $db->escape($source) .'\' AND
	`case`=\''. $db->escape($case) .'\'
LIMIT 1'
	);
	if (! $ok)
		return new GsError( 'Failed to set call forwarding number.' );
	return true;
}

?>