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
include_once( GS_DIR .'inc/gs-fns/gs_ringtones_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_ringtone_set.php' );
include_once( GS_DIR .'inc/group-fns.php' );
include_once( GS_DIR .'inc/gettext.php' );
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
if (! in_array( $type, array('internal','external'), true )) {
	$type = false;
}


$db = gs_db_slave_connect();

$user = trim( @$_REQUEST['u'] );
$user_id = getUserID( $user );

$user_groups  = gs_group_members_groups_get( array( $user_id ), 'user' );
$members_rt = gs_group_permissions_get ( $user_groups, 'ringtone_set' );

if ( count ( $members_rt ) <= 0 )
	_err( 'Forbidden' );

// setup i18n stuff
gs_setlang(gs_get_lang_user($db, $user, GS_LANG_FORMAT_GS));
gs_loadtextdomain("gemeinschaft-gui");
gs_settextdomain("gemeinschaft-gui");

/*
$typeToTitle = array(
	'imported' => "Firma (aus LDAP)",
	'gs'       => "Firma",  # should normally be "Gemeinschaft"
	'prv'      => "Pers\xC3\xB6nlich",
);
*/
$tmp = array(
	15=>array('k' => 'internal' ,
	          'v' => gs_get_conf('GS_BELLCORE_INTERNAL_TITLE', __("Intern")) ),
	25=>array('k' => 'external',
	          'v' => gs_get_conf('GS_PB_BELLCORE_EXTERNAL', __("Extern")) )
);

kSort($tmp);
foreach ($tmp as $arr) {
	$typeToTitle[$arr['k']] = $arr['v'];
}


$url_snom_bellcore = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'snom/rt.php';
$url_snom_menu = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'snom/menu.php';


function defineBackKey()
{
	global $softkeys, $keys, $user, $type, $mac, $url_snom_bellcore;
	
	
	echo '<SoftKeyItem>',
	       '<Name>#</Name>',
	       '<URL>' ,$url_snom_bellcore, '?m=',$mac, '&u=',$user, '</URL>',
	     '</SoftKeyItem>', "\n";
	# Snom does not understand &amp; !
	echo '<SoftKeyItem>',
		'<Name>F4</Name>',
		'<Label>' ,snomXmlEsc(__('Zurück')), '</Label>',
		'<URL>' ,$url_snom_bellcore, '?m=',$mac, '&u=',$user, '</URL>',
		'</SoftKeyItem>', "\n";
}

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
	echo '<SoftKeyItem>',
		'<Name>F4</Name>',
		'<Label>' ,snomXmlEsc(__('Menü')),'</Label>',
		'<URL>', $url_snom_menu, '?', implode('&', $args), '</URL>',
		'</SoftKeyItem>', "\n";
}





################################## SET RINGTONE {

if($type != false && isset($_REQUEST['bc'])){

	$bc = trim( @$_REQUEST['bc'] );
	//$user = trim( @ $_REQUEST['u'] );
	//$user_id = getUserID( $user );
	$user_name = $db->executeGetOne( 'SELECT `user` FROM `users` WHERE `id`='. $db->escape($user_id),';'  );
	$ok = gs_ringtone_set( $user_name, $type, $bc, true, null) ;
	unset($_REQUEST['bc']);
	
	$type = false;
}


################################# SET RINGTONE }  




#################################### SELECT RINGTONE {
if ($type == 'internal' || $type == 'external') {
	
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
	

	$query = 'SELECT bellcore, file FROM ringtones WHERE user_id=\''. $user_id .'\' AND src=\'' . $type . '\'';
	
	$rs = $db->execute($query);
//	echo "<pre>"; echo $query."\n\n"; print_r($rs); var_dump($rs);echo "</pre>"; exit();
	if ($rs->numRows() !== 0) {
		
		 		
		      
		$r = $rs->fetchRow(); 
		$bellcore = $r['bellcore'];
		$file = $r['file'];
	}
	else{
		$file = '';
		$bellcore = 1;
	
	}
	
		echo '<SnomIPPhoneMenu>', "\n",
		'<Title>', snomXmlEsc( $typeToTitle[$type] ), '</Title>', "\n";
		 		
		
		
		//Lautlos
		echo '<MenuItem';
		if($bellcore == 0 && $file == 'NULL')echo ' sel=true';
		echo '>',"\n";
		echo '<Name>',snomXmlEsc(__('Lautlos')),'</Name>',"\n";
		echo '<URL>';
		echo  $url_snom_bellcore, '?m=',$mac, '&u=',$user, '&t=',$type,'&bc=0';
		echo '</URL>',"\n";  
		echo '</MenuItem>',"\n";
		
		//Bellcore 1 bis 5 (Bellcore 6-10 unterstuetzt snom nicht)
		for($i = 1 ; $i <= 5; $i++){
			echo '<MenuItem';
			if($bellcore == $i && $file == '')echo ' sel=true';
			echo '>',"\n";
			echo '<Name>',snomXmlEsc('Bellcore '.$i),'</Name>',"\n";
			echo '<URL>';
			echo  $url_snom_bellcore, '?m=',$mac, '&u=',$user, '&t=',$type,'&bc=',$i;
			echo '</URL>',"\n";  
			echo '</MenuItem>',"\n";			
		
		}
		//Wenn ein eigenes File definiert wurde, wird diese Option angemeldet
		if($file != ''){
			echo '<MenuItem sel=true>',"\n";
			echo '<Name>',snomXmlEsc($file),'</Name>',"\n";
			echo '<URL>';
			echo  $url_snom_bellcore, '?m=',$mac, '&u=',$user, '&t=',$type,'&bc=99';
			echo '</URL>',"\n";  
			echo '</MenuItem>',"\n";
		}
		 defineBackKey();
		echo '</SnomIPPhoneDirectory>', "\n";

	_ob_send();
}
#################################### PRIVATE PHONEBOOK }

#################################### INITIAL SCREEN {
if (! $type) {
	
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['m'] )));
	
	$user_id_check = $db->executeGetOne( 'SELECT `user_id` FROM `phones` WHERE `mac_addr`=\''. $db->escape($mac) .'\'' );
	if ($user_id != $user_id_check)
		_err( 'Not authorized' );
	
	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$remote_addr_check = $db->executeGetOne( 'SELECT `current_ip` FROM `users` WHERE `id`='. $user_id );
	if ($remote_addr != $remote_addr_check)
		_err( 'Not authorized' );
	

	
	
	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>', "\n",
	     '<SnomIPPhoneMenu>', "\n",
	       '<Title>'. __("Tonsignale") .'</Title>', "\n\n";
	foreach ($typeToTitle as $t => $title) {
		
	$query = 'SELECT bellcore, file FROM ringtones WHERE user_id=\''. $user_id .'\' AND src=\'' . $t . '\'';
	
	$rs = $db->execute($query);
//	echo "<pre>"; echo $query."\n\n"; print_r($rs); var_dump($rs);echo "</pre>"; exit();
	$display = '';
		if ($rs->numRows() !== 0) {
			$r = $rs->fetchRow(); 
			$bellcore = $r['bellcore'];
			$file = $r['file'];
		
			if($file != null)$display = ': '. $file;
			else if($bellcore == 0)$display = ': '. __("Lautlos");
			else if($bellcore >= 1 && $bellcore <= 10)$display = ': Bellcore '.$bellcore;  
		}
		
				
		echo '<MenuItem>', "\n",
		       '<Name>', snomXmlEsc($title.$display), '</Name>', "\n",
		       '<URL>', $url_snom_bellcore, '?m=',$mac, '&u=',$user, '&t=',$t, '</URL>', "\n",
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
