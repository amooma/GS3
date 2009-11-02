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
*    returns a queue's call forwards
***********************************************************/

function gs_queue_callforward_get( $queue )
{
	if (! preg_match( '/^[\d]+$/', $queue ))
		return new GsError( 'Queue must be numeric.' );
	
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
	
	# get states
	#
	$sources = array( 'internal', 'external' );
	$cases = array( 'always', 'full', 'timeout', 'empty' );
	$forwards = array();
	foreach ($sources as $source) {
		foreach ($cases as $case) {
			$rs = $db->execute( 'SELECT `active`, `number_std`, `number_var`, `number_vml`, `vm_rec_id`, `timeout` FROM `queue_callforwards` WHERE `queue_id`='. $queue_id .' AND `source`=\''. $source .'\' AND `case`=\''. $case .'\'' );
			if ($r = $rs->fetchRow()) {
				if (! in_array( $r['active'], array('no','std','var','vml','trl','par'), true ))
					$r['active'] = 'no';
				$forwards[$source][$case] = $r;
			} else {
				$forwards[$source][$case] = array(
					'active'     => 'no',
					'number_std' => '',
					'number_var' => '',
					'number_vml' => '',
					'timeout'    => 20
				);
			}
			if ($case != 'timeout') $forwards[$source][$case]['timeout'] = null;
		}
	}
	return $forwards;
}


?>