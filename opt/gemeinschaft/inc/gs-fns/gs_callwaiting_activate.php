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
//include_once( GS_DIR .'inc/gs-fns/gs_prov_phone_checkcfg.php' ); //FIXME


/***********************************************************
*    (de)activates call waiting for a user
***********************************************************/

function gs_callwaiting_activate( $user, $active )
{
	if (! preg_match( '/^[a-z0-9\-_.]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	$active = !! $active;
	
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
	$user_ext = $db->executeGetOne('SELECT `s`.`name` `ext`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE `u`.`user`=\''. $db->escape($user) .'\''
	);
	if (! $user_ext)
		return new GsError( 'Unknown user.' );
	
	# (de)activate
	#
	$num = $db->executeGetOne( 'SELECT COUNT(*) FROM `callwaiting` WHERE `user_id`='. $user_id );
	if ($num < 1) {
		$ok = $db->execute( 'INSERT INTO `callwaiting` (`user_id`, `active`) VALUES ('. $user_id .', 0)');
	} else
		$ok = true;
	$ok = $ok && $db->execute( 'UPDATE `callwaiting` SET `active`='. (int)$active .' WHERE `user_id`='. $user_id );
	if (! $ok)
		return new GsError( 'Failed to set call waiting.' );
	
	$call   //= "Channel: Local/". $from_num_dial ."\n"
		= "Channel: local/toggle@toggle-cwait-hint\n"
		. "MaxRetries: 0\n"
		. "WaitTime: 15\n"
		. "Context: toggle-cwait-hint\n"
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
	
	
	# reload phone config
	#
	//$user_name = $db->executeGetOne( 'SELECT `name` FROM `ast_sipfriends` WHERE `_user_id`='. $user_id );
	//@ exec( 'asterisk -rx \'sip notify snom-reboot '. $user_name .'\'' );
	//@ gs_prov_phone_checkcfg_by_user( $user, false ); //FIXME
	
	return true;
}


?>