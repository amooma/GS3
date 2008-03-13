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


/***********************************************************
*    adds a user account
***********************************************************/

function gs_user_add( $user, $ext, $pin, $firstname, $lastname, $host_id_or_ip, $email )
{
	if (! preg_match( '/^[a-zA-Z\d]+$/', $user ))
		return new GsError( 'User must be alphanumeric.' );
	if (! preg_match( '/^[1-9][0-9]{1,9}$/', $ext ))
		return new GsError( 'Please use 2-10 digit extension.' );
	if (! preg_match( '/^[\d]+$/', $pin ))
		return new GsError( 'PIN must be numeric.' );
	//if (! preg_match( '/^[a-zA-Z\d.\-\_ ]+$/', $firstname ))
	//	return new GsError( 'Invalid characters in first name.' );
	$firstname = preg_replace('/\s+/', ' ', trim($firstname));
	//if (! preg_match( '/^[a-zA-Z\d.\-\_ ]+$/', $lastname ))
	//	return new GsError( 'Invalid characters in last name.' );
	$lastname = preg_replace('/\s+/', ' ', trim($lastname));
	if (! defined('GS_EMAIL_PATTERN_VALID'))
		return new GsError( 'GS_EMAIL_PATTERN_VALID not defined.' );
	if ($email != '' && ! preg_match( GS_EMAIL_PATTERN_VALID, $email ))
		return new GsError( 'E-mail address must be numeric.' );
	
	include_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# check if user exists
	#
	$num = $db->executeGetOne( 'SELECT COUNT(*) FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	if ($num > 0)
		return new GsError( 'User exists.' );
	
	# check if ext exists
	#
	$num = $db->executeGetOne( 'SELECT COUNT(*) FROM `ast_sipfriends` WHERE `name`=\''. $db->escape($ext) .'\'' );
	if ($num > 0)
		return new GsError( 'Extension exists.' );
	
	# check if queue with same ext exists
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `ast_queues` WHERE `name`=\''. $db->escape($ext) .'\'' );
	if ($num > 0)
		return new GsError( 'A queue with that name already exists.' );
	
	# check if host exists
	#
	$host = gs_host_by_id_or_ip( $host_id_or_ip );	
	if (isGsError( $host ))
		return new GsError( $host->getMsg() );
	if (! is_array( $host ))
		return new GsError( 'Unknown host.' );
	
	# add user
	#
	$ok = $db->execute( 'INSERT INTO `users` (`id`, `user`, `pin`, `firstname`, `lastname`, `email`, `nobody_index`, `host_id`) VALUES (NULL, \''. $db->escape($user) .'\', \''. $db->escape($pin) .'\', _utf8\''. $db->escape($firstname) .'\', _utf8\''. $db->escape($lastname) .'\', _utf8\''. $db->escape($email) .'\', NULL, '. $host['id'] .')' );
	if (! $ok)
		return new GsError( 'Failed to add user (table users).' );
	
	# get user_id
	#
	$user_id = (int)$db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	if (! $user_id)
		return new GsError( 'DB error.' );
	
	# add sip account
	#
	$callerid = trim( gs_utf8_decompose_to_ascii( $firstname .' '. $lastname )) .' <'. $ext .'>';
	$ok = $db->execute( 'INSERT INTO `ast_sipfriends` (`_user_id`, `name`, `secret`, `callerid`, `mailbox`, `setvar`) VALUES ('. $user_id .', \''. $db->escape($ext) .'\', \''. $db->escape(rand(10000,99999).rand(10000,99999)) .'\', _utf8\''. $db->escape($callerid) .'\', \''. $db->escape($ext) .'\', \''. $db->escape('__user_id='. $user_id .';__user_name='. $ext) .'\')' );
	if (! $ok)
		return new GsError( 'Failed to add user (table ast_sipfriends).' );
	
	# add mailbox
	#
	$ok = $db->execute( 'INSERT INTO `ast_voicemail` (`_uniqueid`, `_user_id`, `mailbox`, `password`, `email`, `fullname`) VALUES (NULL, '. $user_id .', \''. $db->escape($ext) .'\', \''. $db->escape($pin) .'\', \'\', _utf8\''. $db->escape($firstname .' '. $lastname) .'\')' );
	if (! $ok)
		return new GsError( 'Failed to add user (table ast_voicemail).' );
	
	# add mailbox (de)active entry
	#
	$ok = $db->execute( 'INSERT INTO `vm` (`user_id`, `internal_active`, `external_active`) VALUES ('. $user_id .', 0, 0)' );
	if (! $ok)
		return new GsError( 'Failed to add user (table vm).' );
	
	# reload dialplan (hints!)
	#
	//@ exec( GS_DIR .'sbin/start-asterisk --dialplan' );  // <-- not the same host!!!
	//$ok = @ gs_asterisks_reload( array($host['id']), true );
	$ok = @ gs_asterisks_reload( array($host['id']), false );
	if (isGsError( $ok ))
		return new GsError( $ok->getMsg() );
	if (! $ok)
		return new GsError( 'Failed to reload dialplan.' );
	
	return true;
}


?>