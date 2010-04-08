<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 4441 $
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
*
* Author: Andreas Neugebauer <neugebauer@loca.net> - LocaNet oHG
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

# caution: earlier versions of Snom firmware do not like
# indented XML

define( 'GS_VALID', true );  /// this is a parent file

require_once( '../../../inc/conf.php' );
require_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/gs-lib.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_dnd_toggle.php' );
include_once( GS_DIR .'inc/group-fns.php' );

function snomXmlEsc( $str )
{
	return str_replace(
		array('<', '>', '"'   , "\n"),
		array('_', '_', '\'\'', ' ' ),
		$str);
	# the stupid Snom does not understand &lt;, &gt, &amp;, &quot; or &apos;
	# - neither as named nor as numbered entities
}

function _ob_send()
{
	if (! headers_sent()) {
		header( 'Content-Type: application/x-snom-xml; charset=utf-8' );
		# the Content-Type header is ignored by the Snom
		header( 'Content-Length: '. (int)@ob_get_length() );
	}
	@ob_end_flush();
	die();
}

function getUserID( $ext )
{
	global $db;
	
	if (! preg_match('/^\d+$/', $ext))return -1;
	
	$user_id = (int)$db->executeGetOne( 'SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`=\''. $db->escape($ext) .'\'' );
	if ($user_id < 1)
		_return -1;
	return $user_id;
}



$type = trim( @$_REQUEST['t'] );

$db = gs_db_slave_connect();

$user = trim( @$_REQUEST['u'] );
$user_id = getUserID( $user );


## Check permissions
#

$user_groups  = gs_group_members_groups_get( array( $user_id ), 'user' );
$members = gs_group_permissions_get ( $user_groups, 'dnd_set' );

if ( count ( $members ) <= 0 )
	exit(1);
	
$url_snom_dnd = GS_PROV_SCHEME .'://'. GS_PROV_HOST .(GS_PROV_PORT==80 ? '' : (':'. GS_PROV_PORT)). GS_PROV_PATH .'snom/dnd.php';



#################################### INITIAL SCREEN {
if ($type == 1) {
	
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['m'] )));
	echo $user_id;

	$user_id_check = $db->executeGetOne( 'SELECT `user_id` FROM `phones` WHERE `mac_addr`=\''. $db->escape($mac) .'\'' );
	if ($user_id != $user_id_check)
		exit(1);
	
	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$remote_addr_check = $db->executeGetOne( 'SELECT `current_ip` FROM `users` WHERE `id`=\''. $user_id.'\''   );
	if ($remote_addr != $remote_addr_check)
		exit(1);

	if($user_id > 0){
		$ret = gs_user_dnd_toggle($user_id);
	}
	
		
	
}
#################################### INITIAL SCREEN }




?>