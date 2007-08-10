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

require_once( GS_DIR .'inc/get-listen-to-ids.php' );


/***********************************************************
*    reloads all active asterisks
*    $host_ids=false for all
***********************************************************/

function gs_asterisks_reload( $host_ids, $dialplan_only )
{
	$dialplan_only = !! $dialplan_only;
	if (! $host_ids || ! is_array($host_ids)) $host_ids = false;
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get hosts
	#
	$hosts = @ gs_hosts_get();
	if (isGsError( $hosts ))
		return new GsError( $hosts->getMsg() );
	if (! is_array( $hosts ))
		return new GsError( 'Failed to get hosts.' );
	
	# get out host IDs
	#
	$our_host_ids = @ gs_get_listen_to_ids();
	if (isGsError( $our_host_ids ))
		return new GsError( $our_host_ids->getMsg() );
	if (! is_array( $our_host_ids ))
		return new GsError( 'Failed to get our host IDs.' );
	
	# are we root? so we have to sudo?
	#
	$uid = @ posix_geteuid();
	$uinfo = @ posix_getPwUid($uid);
	$uname = @ $uinfo['name'];
	$sudo = ($uname=='root') ? '' : 'sudo ';
	
	$ok = true;
	foreach ($hosts as $host) {
		if (! $host_ids || in_array($host['id'], $host_ids)) {
			$cmd = '/opt/gemeinschaft/sbin/start-asterisk'. ($dialplan_only ? ' --dialplan' : '');
			if (! in_array($host['id'], $our_host_ids)) {
				$cmd = $sudo .'ssh -o StrictHostKeyChecking=no -o BatchMode=yes -l root '. escapeShellArg($host['host']) .' '. escapeShellArg($cmd);
			}
			@ exec( $sudo . $cmd .' 1>>/dev/null 2>&1', $out, $err );
			$ok = $ok && ($err==0);
		}
	}
	if (! $ok)
		return new GsError( 'Failed to reload Asterisks.' );
	return true;
}


?>