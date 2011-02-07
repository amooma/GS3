<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1903 $
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
require_once( dirName(__FILE__) .'/../../inc/conf.php' );
include_once( GS_DIR .'inc/get-listen-to-ips.php' );

/***********************************************************
*    returns an array of the users
***********************************************************/


function get_abd_socket( )
{

	$ips = gs_get_listen_to_ips ( true );
	$opts = array('socket' => array('bindto' => $ips[0].':0'));
	$context = stream_context_create($opts);
	$socket = stream_socket_client("tcp://" . GS_BUTTONDAEMON_HOST .":" .  GS_BUTTONDAEMON_PORT, $errno, $errstr, 3, STREAM_CLIENT_ASYNC_CONNECT, $context);
	
	return $socket;
}


function gs_buttondeamon_reload_keys( $username )
{
	
	$socket = get_abd_socket();

	if ( $socket ) {

		$message = "reload Peer " . $username .
			" All" .
			"\n";

		fwrite( $socket, $message );
		fwrite( $socket, "quit\n" );
		fclose( $socket );
	}
}


function gs_buttondeamon_queue_update( $username,  $queue, $state )
{
	
	$socket = get_abd_socket();
  
	if ( $socket ) {

		$message = "set Peer " . $username .
			" Queue " . $queue .
			" State " . $state .  
			"\n";

		fwrite( $socket, $message );
		fwrite( $socket, "quit\n" );
		fclose( $socket );
	}
                                        
}

function gs_buttondeamon_diversion_update( $username )
{
	
	$socket = get_abd_socket();
	
	if ( $socket ) {

 		$message = "set Peer " . $username .
			" Diversion update" .
			"\n";

		fwrite( $socket, $message );
		fwrite( $socket, "quit\n" );
		fclose( $socket );
	}
                                            
}

function gs_buttondeamon_dnd_update( $username )
{
	
	$socket = get_abd_socket();

	if ( $socket ) {

		$message = "set Peer " . $username .
			" DND update" .
			"\n";

		fwrite( $socket, $message );
		fwrite( $socket, "quit\n" );
		fclose( $socket );
	}

}

function gs_buttondeamon_clir_update( $username )
{
	
	$socket = get_abd_socket();

	if ( $socket ) {

		$message = "set Peer " . $username .
			" CLIR update" .
			"\n";

		fwrite( $socket, $message );
		fwrite( $socket, "quit\n" );
		fclose( $socket );
	}

}

function gs_buttondeamon_clip_update( $username )
{

	$socket = get_abd_socket();	
  
	if ( $socket ) {

		$message = "set Peer " . $username .
			" CLIP update" .
	 		"\n";

		fwrite( $socket, $message );
		fwrite( $socket, "quit\n" );
		fclose( $socket );
	}

}

function gs_buttondeamon_user_usergroupgroup_update( $username )
{

	$socket = get_abd_socket();	
  
	if ( $socket ) {

		$message = "set Peer " . $username .
			" UserGroup update" .
	 		"\n";

		fwrite( $socket, $message );
		fwrite( $socket, "quit\n" );
		fclose( $socket );
	}

}

function gs_buttondeamon_group_update( $username )
{

	$socket = get_abd_socket();	

	if ( $socket ) {

  		$message = "set Peer " . $username .
        	" PickupGroups update" .
	        "\n";

		fwrite( $socket, $message );
        fwrite( $socket, "quit\n" );
        fclose( $socket );
 	}
                                            
}


function gs_buttondeamon_group_del( $group )
{

	$socket = get_abd_socket();	
     
	if ( $socket ) {

		$message = "remove System PickupGroup " . $group .  "\n";

 		fwrite( $socket, $message );
 		fwrite( $socket, "quit\n" );
		fclose( $socket );
	}
}
                                                                                                         

                                                                                                         

function gs_buttondeamon_missedcalls( $username )
{
	
	$socket = get_abd_socket();	$ips = gs_get_listen_to_ips ( true );
  
	if ( $socket ) {

		$message = "set Peer " . $username .
			" MissedCalls update" .
			"\n";

		fwrite( $socket, $message );
		fwrite( $socket, "quit\n" );
		fclose( $socket );
	}

}

function gs_buttondeamon_missedqueuecalls($username)
{
	
	$socket = get_abd_socket();
  
	if ( $socket ) {

		$message = "set Peer " . $username .
			" MissedQueueCalls update" .
			"\n";

		fwrite( $socket, $message );
		fwrite( $socket, "quit\n" );
		fclose( $socket );
	}
}

function gs_buttondeamon_remove_peer( $username )
{

	$socket = get_abd_socket();

	if ( $socket ) {
    
 		$message = "remove Peer " . $username .
			" Entity" .
			"\n";
                               
		fwrite( $socket, $message );
		fwrite( $socket, "quit\n" );
		fclose( $socket ); 
	}

}

function gs_buttondeamon_reload()
{

	$socket = get_abd_socket();

	if ( $socket ) {

		$message = "reload System\n";
                               
		fwrite($socket, $message);
		$ret = array();
		while ( !feof( $socket ) ) {
			$a = fgets( $socket );
			$ret[] = $a;
			if ( substr_count($a,"OK" ) > 0 ) break;
			if ( substr_count($a,"Error" ) > 0 ) break;
		}

		fwrite( $socket, "quit\n" );
		fclose( $socket );
        return $ret;
	}

}

function gs_buttondeamon_usergroup_remove( $usergroup )
{

	$socket = get_abd_socket();

	if ( $socket ) {
    
 		$message = "remove System UserGroup " . $usergroup .
			"\n";
                               
		fwrite( $socket, $message );
		fwrite( $socket, "quit\n" );
		fclose( $socket );
	}

}

function gs_buttondeamon_usergroups_update()
{

	$socket = get_abd_socket();

	if ( $socket ) {

		$message = "set System UserGroups update" . 
			"\n";

		fwrite( $socket, $message );
		fwrite( $socket, "quit\n" );
		fclose( $socket );
	}

}

function gs_buttondeamon_softkeyprofile_remove( $profile )
{

	$socket = get_abd_socket();
	
	if ( $socket ) {
    
		$message = "remove System SoftkeyProfile " . $profile .
			"\n";

		fwrite( $socket, $message );
		fwrite( $socket, "quit\n" );
		fclose( $socket );
	}

}

function gs_buttondeamon_softkeyprofile_update( $profile )
{

	$socket = get_abd_socket();

	if ( $socket ) {
    
		$message = "set System SoftkeyProfile " . $profile .
			" update" .
			"\n";

		fwrite( $socket, $message );
		fwrite( $socket, "quit\n" );
		fclose( $socket );
	}
}

function gs_buttondeamon_version( )
{

	$socket = get_abd_socket();

	if ( $socket ) {

		$message = "show System version" . "\n";

		fwrite( $socket, $message );
		for ( $i = 0; $i < 4; $i++ ) {
			$version = fgets  ( $socket );
		}
		fwrite( $socket, "quit\n" );
		fclose( $socket );
		return $version ;
	}
	return;
}
?>