<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2009, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
* Sören Sprenger <soeren.sprenger@amooma.de>
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
include_once( GS_DIR .'inc/gs-fns/gs_user_is_valid_name.php' );
include_once( GS_DIR .'inc/gs-fns/gs_prov_phone_checkcfg.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );

/***********************************************************
*  logs off a user
***********************************************************/

function gs_user_logout( $user, $reboot=true )
{
	$ret = gs_user_is_valid_name( $user );
	if (isGsError($ret)) return $ret;
	elseif (! $ret) return new GsError( 'Invalid username.' );	
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user_id
	#
	$user_id = $db->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $db->escape($user) .'\'' );
	if ($user_id < 1)
		return new GsError( 'Unknown user.' );
	
	$ip_addr = $db->executeGetOne('SELECT `current_ip` FROM `users` WHERE `id`='.$user_id );
	
	$rs = $db->execute( 'SELECT `id`, `mac_addr`, `nobody_index` FROM `phones` WHERE `user_id`='. $user_id );
	
	while ($phone = $rs->fetchRow()) {
		# assign the default nobody
		#
		$phone['nobody_index'] = (int)$phone['nobody_index'];
		if ($phone['nobody_index'] < 1) {
			$new_user_id = null;
		} else {
			$new_user_id = (int)$db->executeGetOne(
				'SELECT `id` FROM `users` WHERE `nobody_index`='. $phone['nobody_index']
				);
			if ($new_user_id < 1) {
				//?
			}
		}
		$db->execute( 'UPDATE `phones` SET `user_id`='. ($new_user_id > 0 ? $new_user_id : 'NULL') .' WHERE `id`='. (int)$phone['id'] .' AND `user_id`='. $user_id );
	}
	
	# log out of all queues
	#
	$user_ext = $db->executeGetOne( 'SELECT `name` FROM `ast_sipfriends` WHERE `_user_id`='. $user_id );
	$user_ext = preg_replace('/[^0-9]/', '', $user_ext);
	if ($user_ext != '') {
		ob_start();
		@exec( GS_DIR.'dialplan-scripts/fake-agi-env.php'
		. ' '. qsa(GS_DIR.'dialplan-scripts/queue-login-logout.agi') .' '. qsa($user_ext) .' 0 logoutall 1>>/dev/null 2>>/dev/null' );
		ob_end_clean();
	}
	
	# restart phone
	#
	if ($ip_addr != '') $ret = @ gs_prov_phone_checkcfg_by_ip( $ip_addr, $reboot );
	if (isGsError( $ret )) gs_script_error( $ret->getMsg() );
	
	return true;
}


?>