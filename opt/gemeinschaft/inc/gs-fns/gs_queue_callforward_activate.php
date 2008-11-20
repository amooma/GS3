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
include_once( GS_DIR .'inc/gs-lib.php' );


/***********************************************************
*    (de)activates a call forward for a queue, either the
*    standard or variable forward number
***********************************************************/

function gs_queue_callforward_activate( $queue, $source, $case, $active )
{
	if (! preg_match( '/^[\d]+$/', $queue ))
		return new GsError( 'Queue must be numeric.' );
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
	
	# get queue_id
	#
	$queue_id = $db->executeGetOne( 'SELECT `_id` FROM `ast_queues` WHERE `name`=\''. $db->escape($queue) .'\'' );
	if (! $queue_id)
		return new GsError( 'Unknown queue.' );
	
	# check if queue has an entry
	#
	$num = $db->executeGetOne( 'SELECT COUNT(*) FROM `queue_callforwards` WHERE `queue_id`='. $queue_id .' AND `source`=\''. $db->escape($source) .'\' AND `case`=\''. $db->escape($case) .'\'' );
	if ($num < 1)
		$ok = $db->execute( 'INSERT INTO `queue_callforwards` (`queue_id`, `source`, `case`, `number_std`, `number_var`, `active`) VALUES ('. $queue_id .', \''. $db->escape($source) .'\', \''. $db->escape($case) .'\', \'\', \'\', \'no\')' );
	else
		$ok = true;
	
	# set state
	#
	$ok = $ok && $db->execute(
'UPDATE `queue_callforwards` SET
	`active`=\''. $db->escape($active) .'\'
WHERE
	`queue_id`='. $queue_id .' AND
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
		$number = $db->executeGetOne( 'SELECT `'. $field .'` FROM `queue_callforwards` WHERE `queue_id`='. $queue_id .' AND `source`=\''. $db->escape($source) .'\' AND `case`=\''. $db->escape($case) .'\'' );
		if (trim($number)=='') {
			$db->execute( 'UPDATE `queue_callforwards` SET `active`=\'no\' WHERE `queue_id`='. $queue_id .' AND `source`=\''. $db->escape($source) .'\' AND `case`=\''. $db->escape($case) .'\'' );
			return new GsError( 'Number is empty. Cannot activate call forward.' );
		}
	}
	
	return true;
}


?>