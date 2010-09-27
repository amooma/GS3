<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1903 $
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
*
* Andreas Neugebauer <neugebauer@loca.net> Locanet oHG
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
require_once( dirName(__FILE__) .'/../../inc/conf.php' );
require_once( GS_DIR .'inc/log.php' );
include_once( GS_DIR .'inc/ami-fns.php' );



function _gs_send_event( $data )
{

	$command = '';
	
	foreach ( $data as $line ) {
	
		if( strlen($line['parm']) > 0 ) {
			$command = $command . $line['parm'] . ': ';
			$command = $command . $line['value'] . "\r\n";
		}
	
	}
	
	$command = $command . "\r\n";
	
	
	$ami = new AMI;
	$ami->ami_login('uevg', 'eSd58', '127.0.0.1', 5038);
	
	
	$res = $ami->ami_send_command($command);
	
	
	$ami->ami_logout();
		
	

}

function _get_ui_head( $type ) 
{

		
	$data = array();
	
	$data[] = array( 'parm' => 'Action', 'value' => 'UserEvent' );
	$data[] = array( 'parm' => 'ActionId', 'value' => '4711' );
	$data[] = array( 'parm' => 'UserEvent', 'value' => $type );
	$data[] = array( 'parm' => 'Source', 'value' => GS_DB_MASTER_HOST );


	return $data;
}

function gs_user_keyset_update( $username )
{
	
	
	$data = _get_ui_head( 'UserKeysetUpdateUI' );	
	
	$data[] = array( 'parm' => 'user', 'value' => $username );
	$data[] = array( 'parm' => 'dbsource', 'value' => GS_DB_MASTER_HOST );
	
	_gs_send_event ( $data );

}

function gs_diversion_changed_ui( $username )
{

	//get the username

	include_once( GS_DIR .'inc/gs-fns/gs_user_name_by_ext.php' );
	
	$user_name =  gs_user_name_by_ext( $username );
	if (isGsError( $user_name )) {
		gs_log ( GS_LOG_WARNING,  $user_name->getMsg() );
		return;
	}
	
	//get the callforwards
	
	include_once( GS_DIR .'inc/gs-fns/gs_callforward_get.php' );
	
	$cf = gs_callforward_get( $user_name );
	
	if (isGsError( $cf )) {
		gs_log ( GS_LOG_WARNING,  $cf->getMsg() );
		return;
	}
	
	$sources = array( 'internal', 'external' );
	$cases = array( 'always', 'busy', 'unavail', 'offline' );
	
	//build a string for internal diversions
	
	$cf_int = array();
	
	foreach ( $cases as $case ) {
	
		$cf_int[] = $cf['internal'][$case]['active'];
	
	} 
	
	$internal = implode ( "/", $cf_int );
	
	//build a string for internal diversions
	
	$cf_ext = array();
	
	foreach ( $cases as $case ) {
	
		$cf_ext[] = $cf['external'][$case]['active'];
	
	} 
	
	$external = implode ( "/", $cf_ext );
	
	$std = $cf['external']['always']['number_std'];
	
	$var = $cf['external']['always']['number_var'];
	
	$vml = $cf['external']['always']['number_vml'];
	
	$timeout = $cf['external']['unavail']['timeout'];
	

	$data = _get_ui_head( 'UserDiversionUI' );	
	
	$data[] = array( 'parm' => 'user', 'value' => $username );
	$data[] = array( 'parm' => 'internal', 'value' => $internal );	
	$data[] = array( 'parm' => 'external', 'value' => $external );	
	$data[] = array( 'parm' => 'std', 'value' => $std );
	$data[] = array( 'parm' => 'var', 'value' => $var );
	$data[] = array( 'parm' => 'vml', 'value' => $vml );
	$data[] = array( 'parm' => 'timeout', 'value' => $timeout );
	
	_gs_send_event ( $data );

	
                                            
}

function gs_dnd_changed_ui( $username, $state="off" )
{
	
	
	$data = _get_ui_head( 'UserDNDUI' );	
	
	$data[] = array( 'parm' => 'user', 'value' => $username );
	$data[] = array( 'parm' => 'state', 'value' => $state );	
	
	_gs_send_event ( $data );

}

function gs_clir_changed_ui ( $username  )
{

	//get the username

	include_once( GS_DIR .'inc/gs-fns/gs_user_name_by_ext.php' );
	
	$user_name =  gs_user_name_by_ext( $username );
	if (isGsError( $user_name )) {
		gs_log ( GS_LOG_WARNING,  $user_name->getMsg() );
		return;
	}

	//get the clir
	
	include_once( GS_DIR .'inc/gs-fns/gs_clir_get.php' );
	
	$clir = gs_clir_get( $user_name );
	
	if (isGsError( $clir )) {
		gs_log ( GS_LOG_WARNING,  $clir->getMsg() );
		return;
	}
	
	
	$data = _get_ui_head( 'UserClirUI' );	
	
	$data[] = array( 'parm' => 'user', 'value' => $username );
	$data[] = array( 'parm' => 'internal', 'value' => $clir['internal_restrict'] );
	$data[] = array( 'parm' => 'external', 'value' => $clir['external_restrict'] );
	
	_gs_send_event ( $data );
}

function gs_clip_changed_ui ( $username  )
{

	//get the username

	include_once( GS_DIR .'inc/gs-fns/gs_user_name_by_ext.php' );
	
	$user_name =  gs_user_name_by_ext( $username );
	if (isGsError( $user_name )) {
		gs_log ( GS_LOG_WARNING,  $user_name->getMsg() );
		return;
	}

	//get the clip
	
	include_once( GS_DIR .'inc/gs-fns/gs_user_callerids_get.php' );
	
	$clip = gs_user_callerids_get( $user_name );
	
	if (isGsError( $clip )) {
		gs_log ( GS_LOG_WARNING,  $clip->getMsg() );
		return;
	}
	
	$clip_act = array( 'internal' => $username,  'external' => $username );
	
	foreach ( $clip as $setting ) {
	
		if ( $setting['selected'] === 1 ) {
		
			$clip_act[$setting['dest']] = $setting['number'];
		
		}
	
	}
	
	$data = _get_ui_head( 'UserClipUI' );	
	
	$data[] = array( 'parm' => 'user', 'value' => $username );
	$data[] = array( 'parm' => 'internal', 'value' => $clip_act['internal'] );
	$data[] = array( 'parm' => 'external', 'value' => $clip_act['external'] );
	
	_gs_send_event ( $data );
}

function gs_usergroup_update_ui( $username )
{
	
	
	$data = _get_ui_head( 'UsergroupUpdateUI' );	
	
	$data[] = array( 'parm' => 'user', 'value' => $username );
	$data[] = array( 'parm' => 'dbsource', 'value' => GS_DB_MASTER_HOST );

	_gs_send_event ( $data );

}

function gs_usergroup_update_all_ui( )
{
	
	
	$data = _get_ui_head( 'UsergroupsUpdateAllUI' );	
	
	$data[] = array( 'parm' => 'dbsource', 'value' => GS_DB_MASTER_HOST );

	_gs_send_event ( $data );

}

function gs_usergroup_remove_ui( $group )
{
	
	
	$data = _get_ui_head( 'UsergroupRemoveUI' );	
	
	$data[] = array( 'parm' => 'group', 'value' => $group );
	$data[] = array( 'parm' => 'dbsource', 'value' => GS_DB_MASTER_HOST );

	_gs_send_event ( $data );

}


function gs_pickupgroup_update_ui( $username )
{
	
	
	$data = _get_ui_head( 'PickupgroupUpdateUI' );	
	
	$data[] = array( 'parm' => 'user', 'value' => $username );
	$data[] = array( 'parm' => 'dbsource', 'value' => GS_DB_MASTER_HOST );

	_gs_send_event ( $data );

}


function gs_pickupgroup_remove_ui( $group )
{
	
	
	$data = _get_ui_head( 'PickupgroupRemoveUI' );	
	
	$data[] = array( 'parm' => 'group', 'value' => $group );

	_gs_send_event ( $data );

}

function gs_user_missedcalls_ui( $username  )
{
	
	//get the username

	include_once( GS_DIR .'inc/gs-fns/gs_user_name_by_ext.php' );
	
	$user_name =  gs_user_name_by_ext( $username );
	if (isGsError( $user_name )) {
		gs_log ( GS_LOG_WARNING,  $user_name->getMsg() );
		return;
	}
	
	//get missedcalls
	
	include_once( GS_DIR .'inc/gs-fns/gs_user_missedcalls_get.php' );
	
	$count = gs_user_missedcalls_get( $user_name );
	
	$data = _get_ui_head( 'UserMissedcallsUI' );	
	
	$data[] = array( 'parm' => 'user', 'value' => $username );
	$data[] = array( 'parm' => 'count', 'value' => $count );	
	
	_gs_send_event ( $data );

}

function gs_user_remove_ui( $username )
{
	
	
	$data = _get_ui_head( 'UserRemoveUI' );	
	
	$data[] = array( 'parm' => 'user', 'value' => $username );

	_gs_send_event ( $data );

}

function gs_diallog_purge_ui()
{
	
	
	$data = _get_ui_head( 'DiallogPurgeUI' );	
	
	_gs_send_event ( $data );

}

function gs_softkeyprofile_remove_ui( $profile )
{
	
	
	$data = _get_ui_head( 'SoftkeyProfileRemoveUI' );	
	
	$data[] = array( 'parm' => 'profile', 'value' => $profile );
	$data[] = array( 'parm' => 'dbsource', 'value' => GS_DB_MASTER_HOST );

	_gs_send_event ( $data );

}

function gs_softkeyprofile_update_ui( $profile )
{
	
	
	$data = _get_ui_head( 'SoftkeyProfileUpdateUI' );	
	
	$data[] = array( 'parm' => 'profile', 'value' => $profile );
	$data[] = array( 'parm' => 'dbsource', 'value' => GS_DB_MASTER_HOST );

	_gs_send_event ( $data );

}


function gs_user_language_changed_ui( $username, $lang )
{
	
	include_once( GS_DIR .'inc/gs-fns/gs_user_get.php' );

	$user = gs_user_get ( $username );
	if (isGsError( $username )) {
		gs_log ( GS_LOG_WARNING,  $user_name->getMsg() );
		return;
	}
	
	$data = _get_ui_head( 'UserLanguageChangedUI' );	
	
	$data[] = array( 'parm' => 'user', 'value' => $user['ext'] );
	$data[] = array( 'parm' => 'language', 'value' => $lang );

	_gs_send_event ( $data );

}

?>