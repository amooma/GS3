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

include_once( GS_DIR .'inc/conf.php' );  # should already be included anyway
include_once( GS_DIR .'inc/log.php' );
include_once( GS_DIR .'inc/db_connect.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );


function gs_script_invalid_usage( $usage=null ) {
	echo ( $usage ? $usage : 'Error' ), "\n\n";
	die(1);
}

function gs_script_error( $msg='' ) {
	echo 'Error. ', $msg, "\n\n";
	die(1);
}

class GsError {
	var $_msg = '';
	// the constructor:
	function GsError( $msg ) {
		$this->_msg = $msg;
	}
	function getMsg() {
		return $this->_msg;
	}
}

function isGsError( $err ) {
	return (is_object($err) && strToLower(@ get_class($err))=='gserror');
}

function getBoolByWord( $boolWord ) {
	if ($boolWord===true || $boolWord===1) return true;
	$boolWord = strToLower($boolWord);
	return in_array( $boolWord, array('yes','true','1','on','y'), true );
}


// include_once( the functions of our script functions library.
// they are in separate files so they can easily be worked
// on independently

include_once( GS_DIR .'inc/gs-fns/gs_callforward_set.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callforward_activate.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callforward_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callwaiting_activate.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callwaiting_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_clir_activate.php' );
include_once( GS_DIR .'inc/gs-fns/gs_clir_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_host_by_id_or_ip.php' );
include_once( GS_DIR .'inc/gs-fns/gs_hosts_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_pickupgroup_add.php' );
include_once( GS_DIR .'inc/gs-fns/gs_pickupgroup_show.php' );
include_once( GS_DIR .'inc/gs-fns/gs_pickupgroups_show.php' );
include_once( GS_DIR .'inc/gs-fns/gs_pickupgroup_del.php' );
include_once( GS_DIR .'inc/gs-fns/gs_pickupgroup_user_add.php' );
include_once( GS_DIR .'inc/gs-fns/gs_pickupgroup_user_del.php' );
include_once( GS_DIR .'inc/gs-fns/gs_queue_add.php' );
include_once( GS_DIR .'inc/gs-fns/gs_queue_del.php' );
include_once( GS_DIR .'inc/gs-fns/gs_queue_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_queues_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_queue_callforward_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_queue_callforward_set.php' );
include_once( GS_DIR .'inc/gs-fns/gs_queue_callforward_activate.php' );
include_once( GS_DIR .'inc/gs-fns/gs_ringtones_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_ringtone_set.php' );
include_once( GS_DIR .'inc/gs-fns/gs_users_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_add.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_change.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_del.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_comment_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_comment_set.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_ip_by_user.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_ip_by_ext.php' );
include_once( GS_DIR .'inc/gs-fns/gs_groups_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_group_change.php' );
include_once( GS_DIR .'inc/gs-fns/gs_group_del.php' );
//include_once( GS_DIR .'inc/gs-fns/gs_vm_add.php' );
include_once( GS_DIR .'inc/gs-fns/gs_vm_activate.php' );
include_once( GS_DIR .'inc/gs-fns/gs_vm_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callblocking_set.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callblocking_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_prov_phone_checkcfg.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_external_numbers_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_external_number_add.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_external_number_del.php' );
include_once( GS_DIR .'inc/gs-fns/gs_asterisks_reload.php' );

//include_once( GS_DIR .'inc/gs-fns/gs_user_snom_keys_get.php' );  # really old
//include_once( GS_DIR .'inc/gs-fns/gs_keys_snom_get.php' );  # old
include_once( GS_DIR .'inc/gs-fns/gs_keys_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_keys_get_snom.php' );
include_once( GS_DIR .'inc/gs-fns/gs_keys_get_siemens.php' );
include_once( GS_DIR .'inc/gs-fns/gs_keys_get_unknown.php' );

include_once( GS_DIR .'inc/gs-fns/gs_prov_params_get.php' );
//include_once( GS_DIR .'inc/gs-fns/gs_prov_params_get_unknown.php' );

include_once( GS_DIR .'inc/gs-fns/gs_user_pin_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_pin_set.php' );

include_once( GS_DIR .'inc/gs-fns/gs_user_email_notify_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_email_notify_set.php' );

include_once( GS_DIR .'inc/gs-fns/gs_user_email_address_get.php' );

?>