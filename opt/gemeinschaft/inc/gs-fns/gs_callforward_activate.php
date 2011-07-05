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
	if (! in_array( $active, array('no','std','var','vml','ano','trl','par'), true ))
		return new GsError( 'Active must be no|std|var|vml|ano|trl|par.' );
	
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
		
	# get user_ext 
	#
	$user_ext = $db->executeGetOne( 'SELECT `name` FROM `ast_sipfriends` WHERE `_user_id`=\''. $db->escape($user_id) .'\'' );
	if (! $user_ext)
		return new GsError( 'Unknown user extension.' );			
	
	# check if user has an entry
	#
	$num = $db->executeGetOne( 'SELECT COUNT(*) FROM `callforwards` WHERE `user_id`='. $user_id .' AND `source`=\''. $db->escape($source) .'\' AND `case`=\''. $db->escape($case) .'\'' );
	if ($num < 1)
		$ok = $db->execute( 'INSERT INTO `callforwards` (`user_id`, `source`, `case`, `number_std`, `number_var`, `number_vml`, `active`) VALUES ('. $user_id .', \''. $db->escape($source) .'\', \''. $db->escape($case) .'\', \'\', \'\', \'\', \'no\')' );
	else
		$ok = true;
	
	
	# do not allow time rules if no time rules  are defined
	#
	
	if ( $active == 'trl'  ) {
		
		$id = (int)$db->executeGetOne('SELECT `_user_id` from `cf_timerules` WHERE `_user_id`=' . $user_id );

		if ( ! $id ) {
			return new GsError( 'No time rules defined. Cannot activate call forward.' );
		}
	}
	# do not allow parallel calls if no parallel targets  are defined
	#
	else if ( $active == 'par'  ) {
		
		$id = (int)$db->executeGetOne('SELECT `_user_id` from `cf_parallelcall` WHERE `_user_id`=' . $user_id  );

		if ( ! $id ) {
			return new GsError( 'No parsllel call tragets. Cannot activate call forward.' );
		}
	}
	
	
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
	if ($active == 'std' || $active == 'var' ) {
		$field = 'number_'. $active;
		$number = $db->executeGetOne( 'SELECT `'. $field .'` FROM `callforwards` WHERE `user_id`='. $user_id .' AND `source`=\''. $db->escape($source) .'\' AND `case`=\''. $db->escape($case) .'\'' );
		if (trim($number)=='') {
			$db->execute( 'UPDATE `callforwards` SET `active`=\'no\' WHERE `user_id`='. $user_id .' AND `source`=\''. $db->escape($source) .'\' AND `case`=\''. $db->escape($case) .'\'' );
			return new GsError( 'Number is empty. Cannot activate call forward.' );
		}
	}
	
	if($case === 'always') {
		$call   //= "Channel: Local/". $from_num_dial ."\n"
			= "Channel: local/toggle@toggle-cfwd-hint\n"
			. "MaxRetries: 0\n"
			. "WaitTime: 15\n"
			. "Context: toggle-cfwd-hint\n"
			. "Extension: toggle\n"
			. "Callerid: $user <Toggle>\n"
			. "Setvar: __user_id=".  $user_id ."\n"
			. "Setvar: __user_name=".  $user_ext ."\n"
			. "Setvar: CHANNEL(language)=". gs_get_conf('GS_INTL_ASTERISK_LANG','de') ."\n"
			. "Setvar: __is_callfile_origin=1\n"  # no forwards and no mailbox on origin side
			. "Setvar: __callfile_from_user=".  $user_ext ."\n"
			. "Setvar: __record_file=".  $filename ."\n"
			;

		$filename = '/tmp/gs-'. $user_id .'-'. time() .'-'. rand(10000,99999) .'.call';

		$cf = @fOpen( $filename, 'wb' );
		if (! $cf) {
			gs_log( GS_LOG_WARNING, 'Failed to write call file "'. $filename .'"' );
			return new GsError( 'Failed to write call file.' );
		}
		@fWrite( $cf, $call, strLen($call) );
		@fClose( $cf );
		@chmod( $filename, 00666 );

		$spoolfile = '/var/spool/asterisk/outgoing/'. baseName($filename);


		if (! gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
			$our_host_ids = @gs_get_listen_to_ids();
			if (! is_array($our_host_ids)) $our_host_ids = array();
			$user_is_on_this_host = in_array( $_SESSION['sudo_user']['info']['host_id'], $our_host_ids );
		} else {
			$user_is_on_this_host = true;
		}

		if ($user_is_on_this_host) {
			# the Asterisk of this user and the web server both run on this host
			$err=0; $out=array();
			@exec( 'sudo mv '. qsa($filename) .' '. qsa($spoolfile) .' 1>>/dev/null 2>>/dev/null', $out, $err );
			if ($err != 0) {
				@unlink( $filename );
				gs_log( GS_LOG_WARNING, 'Failed to move call file "'. $filename .'" to "'. $spoolfile .'"' );
				return new GsError( 'Failed to move call file.' );
			}
		} else {
			$cmd = 'sudo scp -o StrictHostKeyChecking=no -o BatchMode=yes '. qsa( $filename ) .' '. qsa( 'root@'. $user['host'] .':'. $filename );
			//echo $cmd, "\n";
			@exec( $cmd .' 1>>/dev/null 2>>/dev/null', $out, $err );
			@unlink( $filename );
			if ($err != 0) {
				gs_log( GS_LOG_WARNING, 'Failed to scp call file "'. $filename .'" to '. $user['host'] );
				return new GsError( 'Failed to scp call file.' );
			}
			//remote_exec( $user['host'], $cmd, 10, $out, $err ); // <-- does not use sudo!
			$cmd = 'sudo ssh -o StrictHostKeyChecking=no -o BatchMode=yes -l root '. qsa( $user['host'] ) .' '. qsa( 'mv '. qsa( $filename ) .' '. qsa( $spoolfile ) );
			//echo $cmd, "\n";
			@exec( $cmd .' 1>>/dev/null 2>>/dev/null', $out, $err );
			if ($err != 0) {
				gs_log( GS_LOG_WARNING, 'Failed to mv call file "'. $filename .'" on '. $user['host'] .' to "'. $spoolfile .'"' );
				return new GsError( 'Failed to mv call file on remote host.' );
			}
		}
	}
	return true;
}


?>