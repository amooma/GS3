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
if (! in_array( $type, array('internal','external','callwaiting'), true )) {
	$type = false;
}


$db = gs_db_slave_connect();



$tmp = array(
	15=>array('k' => 'internal' ,
	          'v' => gs_get_conf('GS_CLIR_INTERNAL', "CLIR Intern") ),
	25=>array('k' => 'external',
	          'v' => gs_get_conf('GS_CLIR_EXTERNAL', "CLIR Extern" ) ),
	35=>array('k' => 'callwaiting',
	          'v' => gs_get_conf('GS_CALLWAITING', "Anklopfen" ) )
);

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
}






################################## SET FEATURE {

if($type != false && isset($_REQUEST['state'])){

	$state = trim( @$_REQUEST['state'] );
	$user = trim( @ $_REQUEST['u'] );
	$user_id = getUserID( $user );
	$user_name = $db->executeGetOne( 'SELECT `user` FROM `users` WHERE `id`=\''. $db->escape($user_id) .'\'' );



	if($type == 'callwaiting'){
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
	else if($type == 'internal' || $type == 'external'){
		if($state == 'no' || $state == 'yes'){
			gs_clir_activate( $user_name, $type, $state );
		}
		
	}
	
	
	
	$type = false;
                                                                
}


################################# SET FEATURE }  




#################################### SELECT FEATURETYPE {
if (($type == 'internal' || $type == 'external' ||  $type == 'callwaiting') && $type != false) {
	
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['m'] )));
	$user = trim( @ $_REQUEST['u'] );
	$user_id = getUserID( $user );
	

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
		$result = (int)$db->executeGetOne( 'SELECT `active` FROM `callwaiting` WHERE `user_id`='. $user_id );
		if($result == 1)$state = "ein";
		else($state == "aus");
	}
	else{
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
		echo '<Name>',snomXmlEsc('Aus'),'</Name>',"\n";
		echo '<URL>';
		echo  $url_snom_features, '?m=',$mac, '&u=',$user, '&t=',$type,'&state=no';
		echo '</URL>',"\n";  
		echo '</MenuItem>',"\n";
		
		echo '<MenuItem';
		if($state == 'ein')echo ' sel=true';
		echo '>',"\n";
		echo '<Name>',snomXmlEsc('Ein'),'</Name>',"\n";
		echo '<URL>';
		echo  $url_snom_features, '?m=',$mac, '&u=',$user, '&t=',$type,'&state=yes';
		echo '</URL>',"\n";  
		echo '</MenuItem>',"\n";
		
		 defineBackKey();
		echo '</SnomIPPhoneDirectory>', "\n";
		

	_ob_send();
}
#################################### SELECT FEATURETYPE }



#################################### INITIAL SCREEN {
if (! $type) {
	
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['m'] )));
	$user = trim( @$_REQUEST['u'] );
	$user_id = getUserID( $user );
	
	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>', "\n",
	     '<SnomIPPhoneMenu>', "\n",
	       '<Title>Dienstmerkmale</Title>', "\n\n";
	foreach ($typeToTitle as $t => $title) {
	
	$state = ": aus";
	if($t == 'callwaiting'){
		$result = (int)$db->executeGetOne( 'SELECT `active` FROM `callwaiting` WHERE `user_id`='. $user_id );
		if($result == 1)$state = ": ein";
		else($state == ": aus");
	}
	else{
		$result = $db->executeGetOne( 'SELECT `'. $t.'_restrict` FROM `clir` WHERE `user_id`='. $user_id );
		if($result == "yes")$state = ": ein";
                else($state == ": aus");
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
