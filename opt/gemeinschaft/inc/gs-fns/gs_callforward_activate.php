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
*    (de)activates a call forward for a user, either the
*    standard or variable forward number
***********************************************************/

function gs_callforward_activate( $user, $source, $case, $active )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	if (! in_array( $source, array('internal','external'), true ))
		return new GsError( 'Source must be internal|external.' );
	if (! in_array( $case, array('always','busy','unavail','offline'), true ))
		return new GsError( 'Case must be always|busy|unavail|offline.' );
	if (! in_array( $active, array('no','std','var','vml','trl','par'), true ))
		return new GsError( 'Active must be no|std|var|vml|trl|par.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user_id
	#
	$user_id = $db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	if (! $user_id)
		return new GsError( 'Unknown user.' );
	
	# check if user has an entry
	#
	$num = $db->executeGetOne( 'SELECT COUNT(*) FROM `callforwards` WHERE `user_id`='. $user_id .' AND `source`=\''. $db->escape($source) .'\' AND `case`=\''. $db->escape($case) .'\'' );
	if ($num < 1)
		$ok = $db->execute( 'INSERT INTO `callforwards` (`user_id`, `source`, `case`, `number_std`, `number_var`, `number_vml`, `active`) VALUES ('. $user_id .', \''. $db->escape($source) .'\', \''. $db->escape($case) .'\', \'\', \'\', \'\', \'no\')' );
	else
		$ok = true;
	
	# set state
	#
	$ok = $ok && $db->execute(
'UPDATE `callforwards` SET
	`active`=\''. $db->escape($active) .'\'
WHERE
	`user_id`='. $user_id .' AND
	`source`=\''. $db->escape($source) .'\' AND
	`case`=\''. $db->escape($case) .'\'
LIMIT 1'
	);
	if (! $ok)
		return new GsError( 'Failed to set call forwarding status.' );
	
	# do not allow an empty number to be active
	#
	if ($active != 'no' && $active != 'trl' && $active != 'par') {
		$field = 'number_'. $active;
		$number = $db->executeGetOne( 'SELECT `'. $field .'` FROM `callforwards` WHERE `user_id`='. $user_id .' AND `source`=\''. $db->escape($source) .'\' AND `case`=\''. $db->escape($case) .'\'' );
		if (trim($number)=='') {
			$db->execute( 'UPDATE `callforwards` SET `active`=\'no\' WHERE `user_id`='. $user_id .' AND `source`=\''. $db->escape($source) .'\' AND `case`=\''. $db->escape($case) .'\'' );
			return new GsError( 'Number is empty. Cannot activate call forward.' );
		}
	}
	
	return true;
}


?>