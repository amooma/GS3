<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 3307 $
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
include_once( GS_DIR .'inc/gs-fns/gs_clir_activate.php' );
include_once( GS_DIR .'inc/gs-fns/gs_clir_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callwaiting_activate.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callwaiting_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_callerids_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_callerid_set.php' );
include_once( GS_DIR .'inc/group-fns.php' );
require_once( GS_DIR .'inc/gettext.php' );
require_once( GS_DIR .'inc/langhelper.php' );

header( 'Content-Type: application/x-snom-xml; charset=utf-8' );
# the Content-Type header is ignored by the Snom
header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );


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

function _err( $msg='' )
{
	@ob_end_clean();
	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>', "\n",
	     '<SnomIPPhoneText>', "\n",
	       '<Title>', 'Error', '</Title>', "\n",
	       '<Text>', snomXmlEsc( 'Error: '. $msg ), '</Text>', "\n",
	     '</SnomIPPhoneText>', "\n";
	_ob_send();
}

function getUserID( $ext )
{
	global $db;
	
	if (! preg_match('/^\d+$/', $ext))
		_err( 'Invalid username' );
	
	$user_id = (int)$db->executeGetOne( 'SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`=\''. $db->escape($ext) .'\'' );
	if ($user_id < 1)
		_err( 'Unknown user' );
	return $user_id;
}


if (! gs_get_conf('GS_SNOM_PROV_ENABLED')) {
	gs_log( GS_LOG_DEBUG, "Snom provisioning not enabled" );
	_err( 'Not enabled' );
}

$type = trim( @$_REQUEST['t'] );
if (! in_array( $type, array('internal', 'external', 'callwaiting', 'cidext', 'cidint'), true )) {
	$type = false;
}


$db = gs_db_slave_connect();

$user = trim( @$_REQUEST['u'] );
$user_id = getUserID( $user );

$user_groups  = gs_group_members_groups_get( array( $user_id ), 'user' );
$members_clip = gs_group_permissions_get ( $user_groups, 'clip_set' );

if ( count ( $members_clip ) <= 0 )
	$show_clip = false;
else
	$show_clip = true;

$members_clir = gs_group_permissions_get ( $user_groups, 'clir_set' );

if ( count ( $members_clir ) <= 0 )
	$show_clir = false;
else
	$show_clir = true;

$members_callwaiting = gs_group_permissions_get ( $user_groups, 'callwaiting_set' );

if ( count ( $members_callwaiting ) <= 0 )
	$show_cw = false;
else
	$show_cw = true;

// setup i18n stuff
gs_setlang(gs_get_lang_user($db, $user, GS_LANG_FORMAT_GS));
gs_loadtextdomain("gemeinschaft-gui");
gs_settextdomain("gemeinschaft-gui");

$tmp = array();

if ( $show_clir ) {

	$tmp[15] = array( 'k' => 'internal' , 'v' => gs_get_conf('GS_CLIR_INTERNAL', __("CLIR Intern")) );
	$tmp[25] = array( 'k' => 'external', 'v' => gs_get_conf('GS_CLIR_EXTERNAL', __("CLIR Extern")) );

}

if ( $show_cw ) {

	$tmp[35] = array( 'k' => 'callwaiting','v' => gs_get_conf('GS_CALLWAITING', __("Anklopfen") ) );

}

if ( $show_clip ) {
	
	$tmp[45] = array('k' => 'cidext','v' => gs_get_conf('GS_CALLERID', __("CID extern") ) );
	$tmp[55] = array('k' => 'cidint', 'v' => gs_get_conf('GS_CALLERID', __("CID intern") ) );
	
}

/*
$tmp = array(
	15=>array('k' => 'internal' ,
	          'v' => gs_get_conf('GS_CLIR_INTERNAL', "CLIR Intern") ),
	25=>array('k' => 'external',
	          'v' => gs_get_conf('GS_CLIR_EXTERNAL', "CLIR Extern" ) ),
	35=>array('k' => 'callwaiting',
	          'v' => gs_get_conf('GS_CALLWAITING', "Anklopfen" ) ),
	45=>array('k' => 'cidext',
		'v' => gs_get_conf('GS_CALLERID', __("CID extern") ) ),
	55=>array('k' => 'cidint',
		'v' => gs_get_conf('GS_CALLERID', __("CID intern") ) )
);
*/

kSort($tmp);
foreach ($tmp as $arr) {
	$typeToTitle[$arr['k']] = $arr['v'];
}


$url_snom_features = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'snom/features.php';
$url_snom_menu = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'snom/menu.php';


function defineBackMenu()
{
	global $user, $type, $mac, $url_snom_menu;
	
	$args = array();
		$args[] = 'm='. $mac;
		$args[] = 'u='. $user;
	
	echo '<SoftKeyItem>',
	       '<Name>#</Name>',
	       '<URL>', $url_snom_menu, '?', implode('&', $args), '</URL>',
	     '</SoftKeyItem>', "\n";
	echo '<SoftKeyItem>',
		'<Name>F4</Name>',
		'<Label>' ,snomXmlEsc(__('Menü')), '</Label>',
		'<URL>', $url_snom_menu, '?', implode('&', $args), '</URL>',
		'</SoftKeyItem>', "\n";
	# Snom does not understand &amp; !
}

function defineBackKey()
{
	global $softkeys, $keys, $user, $type, $mac, $url_snom_features;
	
	
	echo '<SoftKeyItem>',
		'<Name>#</Name>',
		'<URL>' ,$url_snom_features, '?m=',$mac, '&u=',$user, '</URL>',
		'</SoftKeyItem>', "\n";
		# Snom does not understand &amp; !
	echo '<SoftKeyItem>',
		'<Name>F4</Name>',
		'<Label>' ,snomXmlEsc(__('Zurück')),'</Label>',
		'<URL>' ,$url_snom_features, '?m=',$mac, '&u=',$user, '</URL>',
		'</SoftKeyItem>', "\n";
}






################################## SET FEATURE {

if($type != false && isset($_REQUEST['state'])){

	$state = trim( @$_REQUEST['state'] );
	//$user = trim( @ $_REQUEST['u'] );
	//$user_id = getUserID( $user );
	$user_name = $db->executeGetOne( 'SELECT `user` FROM `users` WHERE `id`=\''. $db->escape($user_id) .'\'' );



	if($type == 'callwaiting' && $show_cw ){
		$oldresult = (int)$db->executeGetOne( 'SELECT `active` FROM `callwaiting` WHERE `user_id`='. $user_id );
		if($state == 'yes' || $state == 'no'){
			if($oldresult == 1 && $state == 'no'){
				gs_callwaiting_activate( $user_name, 0 );
			}
			else if($oldresult == 0 && $state == 'yes'){
				gs_callwaiting_activate( $user_name, 1 );
			}
		}
	}
	else if( $show_clir && ( $type == 'internal' || $type == 'external') ){
		if($state == 'no' || $state == 'yes'){
			gs_clir_activate( $user_name, $type, $state );
		}
		
	}
	else if($type == 'cidint' && $show_clip ){
		gs_user_callerid_set($user_name, $state, 'internal');
	}
	else if($type == 'cidext' && $show_clip ){
		gs_user_callerid_set($user_name, $state, 'external');
	}
	else {
		_err( 'Forbidden');
	}
	
	
	
	$type = false;
                                                                
}


################################# SET FEATURE }  




#################################### SELECT FEATURETYPE {
if (($type == 'internal' || $type == 'external' ||  $type == 'callwaiting') && $type != false) {
	
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['m'] )));
	//$user = trim( @ $_REQUEST['u'] );
	//$user_id = getUserID( $user );
	

	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>',"\n";
	
	$user_id_check = $db->executeGetOne( 'SELECT `user_id` FROM `phones` WHERE `mac_addr`=\''. $db->escape($mac) .'\'' );
	if ($user_id != $user_id_check)
		_err( 'Not authorized' );
	
	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$remote_addr_check = $db->executeGetOne( 'SELECT `current_ip` FROM `users` WHERE `id`='. $user_id );
	if ($remote_addr != $remote_addr_check)
		_err( 'Not authorized' );
	
	$state = "aus";
	if($type == 'callwaiting'){
	
		if ( ! $show_cw )
			_err( "Forbidden" );
		$result = (int)$db->executeGetOne( 'SELECT `active` FROM `callwaiting` WHERE `user_id`='. $user_id );
		if($result == 1)$state = "ein";
		else($state == "aus");
	}
	else{
		if ( ! $show_clir )
			_err( "Forbidden" );
		$result = $db->executeGetOne( 'SELECT `'. $type.'_restrict` FROM `clir` WHERE `user_id`='. $user_id );
		if($result == "yes")$state = "ein";
                else($state == "aus");
	}
	
		
		echo '<SnomIPPhoneMenu>', "\n",
		       '<Title>', snomXmlEsc( $typeToTitle[$type] ), '</Title>', "\n";

		//Lautlos
		echo '<MenuItem';
		if($state == 'aus')echo ' sel=true';
		echo '>',"\n";
		echo '<Name>',snomXmlEsc(__('Aus')),'</Name>',"\n";
		echo '<URL>';
		echo  $url_snom_features, '?m=',$mac, '&u=',$user, '&t=',$type,'&state=no';
		echo '</URL>',"\n";  
		echo '</MenuItem>',"\n";
		
		echo '<MenuItem';
		if($state == 'ein')echo ' sel=true';
		echo '>',"\n";
		echo '<Name>',snomXmlEsc(__('Ein')),'</Name>',"\n";
		echo '<URL>';
		echo  $url_snom_features, '?m=',$mac, '&u=',$user, '&t=',$type,'&state=yes';
		echo '</URL>',"\n";  
		echo '</MenuItem>',"\n";
		
		 defineBackKey();
		echo '</SnomIPPhoneDirectory>', "\n";
		

	_ob_send();
}
#################################### SELECT FEATURETYPE }

#################################### SELECT CID{

if ($type == 'cidint' || $type == 'cidext') {
	
	if ( ! $show_clip )
		_err( "Forbidden" );
	
	if( $type == 'cidext')
		$target = 'external';
	else
		$target = 'internal';
	
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['m'] )));
	//$user = trim( @$_REQUEST['u'] );
	//$user_id = getUserID( $user );
	
	$user_name = $db->executeGetOne( 'SELECT `user` FROM `users` WHERE `id`=\''. $db->escape($user_id) .'\'' );
	
	$enumbers = gs_user_callerids_get( $user_name );
	if (isGsError($enumbers)) {
		_err('Fehler beim Abfragen.');
		
	 }
	
	$selected = true; 
	foreach($enumbers as $number){
		if($number['selected']===1)
			$selected = false;
	
	}
	 
	 
	 
	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>', "\n",
		'<SnomIPPhoneMenu>', "\n",
		'<Title>'. __("Cid") .'</Title>', "\n\n";
		echo '<MenuItem';
		if($selected == true)echo ' sel=true';
		echo '>', "\n",
		'<Name>', snomXmlEsc($user), '</Name>', "\n",
		'<URL>',$url_snom_features,'?t=',$type,'&m=',$mac ,'&u=', $user,'&state=</URL>', "\n",
		'</MenuItem>', "\n\n";
	foreach ($enumbers as $extnumber) {
	
		if($extnumber['dest'] != $target) continue;

		echo '<MenuItem';
		if($extnumber['selected'] === 1)echo ' sel=true'; 
		echo'>', "\n",
			'<Name>', snomXmlEsc($extnumber['number']), '</Name>', "\n",
			'<URL>',$url_snom_features,'?t=',$type,'&m=',$mac ,'&u=', $user,'&state=',$extnumber['number'],'</URL>', "\n",
			'</MenuItem>', "\n\n";
		# in XML the & must normally be encoded as &amp; but not for
		# the stupid Snom!
	}
	defineBackKey();
	echo '</SnomIPPhoneMenu>', "\n";
	_ob_send();
	
}


#################################### SELECT CID}

#################################### INITIAL SCREEN {
if (! $type) {
	
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['m'] )));
	$user = trim( @$_REQUEST['u'] );
	$user_id = getUserID( $user );
	
	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>', "\n",
	     '<SnomIPPhoneMenu>', "\n",
	       '<Title>'. __("Dienstmerkmale") .'</Title>', "\n\n";
	foreach ($typeToTitle as $t => $title) {
	
	$state = ": ". __("aus");
	if($t == 'callwaiting'){
		$result = (int)$db->executeGetOne( 'SELECT `active` FROM `callwaiting` WHERE `user_id`='. $user_id );
		if($result == 1)$state = ": ". __("ein");
		else($state == ": ". __("aus"));
	}
	else if($t == 'cidext'){
		unset($result);
		$result = $db->executeGetOne( 'SELECT `number` FROM `users_callerids` WHERE `selected`=1 AND `dest`=\'external\' AND `user_id`='. $user_id );
		if($result)
			$state =  ": " . $result;
		else
			$state = ": " .$user;
	}
	else if($t == 'cidint'){
		unset($result);
		$result = $db->executeGetOne( 'SELECT `number` FROM `users_callerids` WHERE `selected`=1 AND `dest`=\'internal\' AND `user_id`='. $user_id );
		if($result)
			$state =  ": " . $result;
		else
			$state = ": " .$user;
	}
	else{
		$result = $db->executeGetOne( 'SELECT `'. $t.'_restrict` FROM `clir` WHERE `user_id`='. $user_id );
		if($result == "yes")$state = ": ". __("ein");
                else($state == ": ". __("aus"));
	}
		
		echo '<MenuItem>', "\n",
		       '<Name>', snomXmlEsc($title.$state), '</Name>', "\n",
		       '<URL>', $url_snom_features, '?m=',$mac, '&u=',$user, '&t=',$t, '</URL>', "\n",
		     '</MenuItem>', "\n\n";
		# in XML the & must normally be encoded as &amp; but not for
		# the stupid Snom!
	}
	defineBackMenu();
	echo '</SnomIPPhoneMenu>', "\n";
	_ob_send();
	
}
#################################### INITIAL SCREEN }


?>
