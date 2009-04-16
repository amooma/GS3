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
include_once( GS_DIR .'inc/gs-fns/gs_callforward_activate.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callforward_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callforward_set.php' );
include_once( GS_DIR .'inc/gs-fns/gs_vm_activate.php' );
include_once( GS_DIR .'inc/gs-fns/gs_astphonebuttons.php' );

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
if (! in_array( $type, array('internal','external','std','var','timeout'), true )) {
	$type = false;
}


$db = gs_db_slave_connect();


$tmp = array(
	15=>array('k' => 'internal' ,
	          'v' => gs_get_conf('GS_CLIR_INTERNAL', "von intern") ),
	25=>array('k' => 'external',
	          'v' => gs_get_conf('GS_CLIR_EXTERNAL', "von extern" ) ),

);

kSort($tmp);
foreach ($tmp as $arr) {
	$typeToTitle[$arr['k']] = $arr['v'];
}


$url_snom_callforward = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'snom/callforward.php';
$url_snom_menu = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'snom/menu.php';


$cases = array(
	'always' => 'immer',  
	'busy'   => 'besetzt',
	'unavail'=> 'keine Antw.',  
	'offline'=> 'offline'
);
$actives = array(
	'no'  => 'Aus',
	'std' => 'Std.',
	'var' => 'Tmp.'
);
                                                                


function defineBackKey()
{
	global $softkeys, $keys, $user, $type, $mac, $url_snom_callforward;
	
	
	echo '<SoftKeyItem>',
	       '<Name>#</Name>',
	       '<URL>' ,$url_snom_callforward, '?m=',$mac, '&u=',$user, '</URL>',
	     '</SoftKeyItem>', "\n";
}


function defineBackMenu()
{
	global $user, $type, $mac, $url_snom_menu;
	
	$args = array();
		$args[] = 'm='. $mac;
		$args[] = 'u='. $user;
		$args[] = 't=forward';
	
	echo '<SoftKeyItem>',
	       '<Name>#</Name>',
	       '<URL>', $url_snom_menu, '?', implode('&', $args), '</URL>',
	     '</SoftKeyItem>', "\n";
}




################################## SET FEATURE {

if ( $type != false && isset($_REQUEST['value']) ) {


	$value = trim( @$_REQUEST['value'] );
	$user = trim( @ $_REQUEST['u'] );
	$user_id = getUserID( $user );
	$user_name = $db->executeGetOne( 'SELECT `user` FROM `users` WHERE `id`=\''. $db->escape($user_id) .'\'' );

	$timeout = (int) $db->executeGetOne( 'SELECT `timeout` FROM `callforwards` WHERE `user_id`=\''. $user_id  .'\' AND `case`=\'unavail\' AND `source`=\'internal\''); 

	
	$num['std'] = $db->executeGetOne( 'SELECT `number_std` FROM `callforwards` WHERE `user_id`=\''. $user_id  .'\' AND `case`=\'unavail\' AND `source`=\'internal\''); 
	$num['var'] = $db->executeGetOne( 'SELECT `number_var` FROM `callforwards` WHERE `user_id`=\''. $user_id  .'\' AND `case`=\'unavail\' AND `source`=\'internal\''); 
	
	$vm['internal'] =  (bool)$db->executeGetOne( 'SELECT `internal_active` FROM `vm` WHERE `user_id`=\''. $user_id  .'\'');
	$vm['external'] =  (bool)$db->executeGetOne( 'SELECT `external_active` FROM `vm` WHERE `user_id`=\''. $user_id  .'\'');
	
	foreach($cases as $case => $v) {
		$internal_val[$case] = 'no';
		$internal_val[$case]  = $db->executeGetOne( 'SELECT `active` FROM `callforwards` WHERE `user_id`=\''. $user_id  .'\' AND `case`=\'' .$case .'\' AND `source`=\'internal\'');
	}
	
	foreach($cases as $case => $v) {
		$external_val[$case] = 'no';
		$external_val[$case]  = $db->executeGetOne( 'SELECT `active` FROM `callforwards` WHERE `user_id`=\''. $user_id  .'\' AND `case`=\'' .$case .'\' AND `source`=\'external\'');
	}
	
	$write = 0;
	
	
	if ( ($type == 'internal' || $type == 'external') &&  isset($_REQUEST['key']) ) {
	
	 	
		$key = trim( @$_REQUEST['key'] );
		if (isset($cases[$key])) {
			if($type == 'internal') {
				$internal_val[$key] = $value;
			}
			if($type == 'external') {
				$external_val[$key] = $value;
			}
		unset($_REQUEST['key']);	
		$write = 1;
		}
		if ($key == 'voicemail') {
		 	$value = (bool)$value;
		 	$vm[$type] = $value;
		 	unset($_REQUEST['key']);
		 	$write = 1;
		}
	
	} else if ( $type == 'timeout' ) {
	
		$value =  abs((int)$value);
		if ($value < 1) $value = 1;
		$timeout = $value;
		
		$write = 1;
		$type = false;
	} else if ( $type == 'var' || $type == 'std' ) {
		$num[$type] =   preg_replace('/[^\d]/', '', $value);
		$write = 1;
		$type = false;
	}

	if( $write == 1 ) {
	 
	 
		foreach ($cases as $case => $gnore2) {
			 $ret = gs_callforward_set( $user_name,'internal', $case, 'std', $num['std'], $timeout );
			 $ret = gs_callforward_set( $user_name,'internal', $case, 'var', $num['var'], $timeout );
			 $ret = gs_callforward_activate( $user_name,'internal',$case,$internal_val[$case]);
		}
		foreach ($cases as $case => $gnore2) {
			 $ret = gs_callforward_set( $user_name,'external', $case, 'std', $num['std'], $timeout );
			 $ret = gs_callforward_set( $user_name,'external', $case, 'var', $num['var'], $timeout );
			 $ret = gs_callforward_activate( $user_name,'external',$case,$external_val[$case]);
		}
		gs_vm_activate( $user_name, 'internal', $vm['internal'] );
		gs_vm_activate( $user_name, 'external', $vm['external'] );
		
		if ( GS_BUTTONDAEMON_USE == true ) {
			$ext =  $db->executeGetOne( 'SELECT `name` FROM `ast_sipfriends` WHERE `_user_id`=\''. $db->escape($user_id) .'\'' );
			gs_buttondeamon_diversion_update( $ext);
		}
	}
	
}

################################# SET FEATURE }  




#################################### SELECT PROBERTIES {
if ( ($type == 'internal' || $type == 'external') && !isset( $_REQUEST['key']) ) {
	
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['m'] )));
	$user = trim( @ $_REQUEST['u'] );
	$user_id = getUserID( $user );
	

	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>',"\n";
	
	$user_id_check = $db->executeGetOne( 'SELECT `user_id` FROM `phones` WHERE `mac_addr`=\''. $db->escape($mac) .'\'' );
	if ($user_id != $user_id_check)
		_err( 'Not authorized' );
	
	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$remote_addr_check = $db->executeGetOne( 'SELECT `current_ip` FROM `users` WHERE `id`=\''. $user_id.'\''   );
	if ($remote_addr != $remote_addr_check)
		_err( 'Not authorized' );
	
	
	$timeout = (int)$db->executeGetOne( 'SELECT `timeout` FROM `callforwards` WHERE `user_id`=\''. $user_id  .'\' AND `case`=\'unavail\' AND `source`=\''.$type.'\''); 

	$vm = $db->executeGetOne( 'SELECT `'. $type .'_active` FROM `vm` WHERE `user_id`=\''. $user_id  .'\'');
	foreach($cases as $case => $v){
		$val[$case] = 'no';
		$val[$case]  = $db->executeGetOne( 'SELECT `active` FROM `callforwards` WHERE `user_id`=\''. $user_id  .'\' AND `case`=\'' .$case .'\' AND `source`=\''.$type.'\'');
	}
	
	echo '<SnomIPPhoneMenu>', "\n";
	echo '<Title>', snomXmlEsc( $typeToTitle[$type] ), '</Title>', "\n";
	foreach($cases as $case => $v){	
		echo '<MenuItem>',"\n";
		echo '<Name>',snomXmlEsc($v . ': ' . $actives[$val[$case]]),'</Name>',"\n";
		echo '<URL>';
		echo  $url_snom_callforward, '?m=',$mac, '&u=',$user, '&t=',$type,'&key=',$case;
		echo '</URL>',"\n";  
		echo '</MenuItem>',"\n";
	}
	//voicemail
	if ($vm == '1')
		$vmstate = 'Ein';
	else
		$vmstate = 'Aus';
		
	echo '<MenuItem>',"\n";
	echo '<Name>',snomXmlEsc('Voicemail: ' . $vmstate),'</Name>',"\n";
	echo '<URL>';
	echo  $url_snom_callforward, '?m=',$mac, '&u=',$user, '&t=',$type,'&key=voicemail';
	echo '</URL>',"\n";  
	echo '</MenuItem>',"\n";
	defineBackKey();
	echo '</SnomIPPhoneMenu>', "\n",
	_ob_send();
}
#################################### SELECT PROBERITES }



#################################### SET CF-STATES {
if ( $type == 'internal' || $type == 'external' && isset( $_REQUEST['key']) ) {
	
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['m'] )));
	$user = trim( @ $_REQUEST['u'] );
	$user_id = getUserID( $user );
	$key = trim( @ $_REQUEST['key'] );

	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>',"\n";
	
	$user_id_check = $db->executeGetOne( 'SELECT `user_id` FROM `phones` WHERE `mac_addr`=\''. $db->escape($mac) .'\'' );
	if ( $user_id != $user_id_check )
		_err( 'Not authorized' );
	
	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$remote_addr_check = $db->executeGetOne( 'SELECT `current_ip` FROM `users` WHERE `id`=\''. $user_id.'\''   );
	if ( $remote_addr != $remote_addr_check )
		_err( 'Not authorized' );
	
	if( $key == 'voicemail' ){
		
		$vm = $db->executeGetOne( 'SELECT `'. $type .'_active` FROM `vm` WHERE `user_id`=\''. $user_id  .'\'');
		echo '<SnomIPPhoneMenu>';
		echo '<Title>', snomXmlEsc( $typeToTitle[$type] . ':  Voicemail'  ), '</Title>', "\n";
		
		echo '<MenuItem';
		if($vm == '1')echo ' sel=true';
		echo'>', "\n";
		echo '<Name>',snomXmlEsc('Ein'),'</Name>',"\n";
		echo '<URL>';
		echo  $url_snom_callforward, '?m=',$mac, '&u=',$user, '&t=',$type,'&key=voicemail&value=1';
		echo '</URL>',"\n";  
		echo '</MenuItem>',"\n";
		
		echo '<MenuItem',"\n";
		if($vm == '0')echo ' sel=true';
		echo'>', "\n";
		echo '<Name>',snomXmlEsc('Aus'),'</Name>',"\n";
		echo '<URL>';
		echo $url_snom_callforward, '?m=',$mac, '&u=',$user, '&t=',$type,'&key=voicemail&value=0';
		echo '</URL>',"\n";  
		echo '</MenuItem>',"\n";
		defineBackKey();
		echo '</SnomIPPhoneMenu>', "\n",
		_ob_send();
	} else if( isset($cases[$key]) ) {

		$val = 'no';
		$val = $db->executeGetOne( 'SELECT `active` FROM `callforwards` WHERE `user_id`=\''. $user_id  .'\' AND `case`=\'' . $key .'\' AND `source`=\''.$type.'\'');
		
		
		echo '<SnomIPPhoneMenu>',"\n";
		echo '<Title>', snomXmlEsc($typeToTitle[$type] . ':  ' . $cases[$key] ), '</Title>', "\n";
		echo '<MenuItem';
		if($val == 'no')echo ' sel=true';
		echo'>', "\n";
		echo '<Name>',snomXmlEsc($actives['no']),'</Name>',"\n";
		echo '<URL>';
		echo  $url_snom_callforward, '?m=',$mac, '&u=',$user, '&t=',$type,'&key='.$key.'&value=no';
		echo '</URL>',"\n";  
		echo '</MenuItem>',"\n";
		
		echo '<MenuItem';
		if($val == 'std')echo ' sel=true';
		echo'>', "\n";
		echo '<Name>',snomXmlEsc($actives['std']),'</Name>',"\n";
		echo '<URL>';
		echo  $url_snom_callforward, '?m=',$mac, '&u=',$user, '&t=',$type,'&key='.$key.'&value=std';
		echo '</URL>',"\n";  
		echo '</MenuItem>',"\n";
		
		echo '<MenuItem';
		if($val == 'var')echo ' sel=true';
		echo'>', "\n";
		echo '<Name>',snomXmlEsc($actives['var']),'</Name>',"\n";
		echo '<URL>';
		echo  $url_snom_callforward, '?m=',$mac, '&u=',$user, '&t=',$type,'&key='.$key.'&value=var';
		echo '</URL>',"\n";  
		echo '</MenuItem>',"\n";
		defineBackKey();
		echo '</SnomIPPhoneMenu>',"\n";		
		
	_ob_send();
	}
	
}
#################################### SET CF-STATES }



#################################### SET PHONENUMBERS {
if ( $type == 'std' || $type == 'var' && !isset( $_REQUEST['value']) ) {
	
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['m'] )));
	$user = trim( @ $_REQUEST['u'] );
	$user_id = getUserID( $user );
	
	if( $type == 'varnumber')$Title = 'temp. Nummer';
	else $Title = 'Standardnummer';

	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>',"\n";
	
	$user_id_check = $db->executeGetOne( 'SELECT `user_id` FROM `phones` WHERE `mac_addr`=\''. $db->escape($mac) .'\'' );
	if ($user_id != $user_id_check)
		_err( 'Not authorized' );
	
	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$remote_addr_check = $db->executeGetOne( 'SELECT `current_ip` FROM `users` WHERE `id`=\''. $user_id.'\''   );
	if ($remote_addr != $remote_addr_check)
		_err( 'Not authorized' );
	
	
	$number = $db->executeGetOne( 'SELECT `number_' . $type . '` FROM `callforwards` WHERE `user_id`=\''. $user_id  .'\' AND `case`=\'unavail\' AND `source`=\'internal\''); 

	foreach($cases as $case => $v){
		$val[$case] = 'no';
		$val[$case]  = $db->executeGetOne( 'SELECT `active` FROM `callforwards` WHERE `user_id`=\''. $user_id  .'\' AND `case`=\'' .$case .'\' AND `source`=\''.$type.'\'');
	}
		
	echo '<SnomIPPhoneInput>',"\n";
	echo '<Title>',snomXmlEsc($Title),'</Title>',"\n";
	echo '<Prompt>Prompt</Prompt>',"\n";
	echo '<URL>';
	echo  $url_snom_callforward;
	echo '</URL>',"\n";
	echo '<InputItem>',"\n";
	echo '<DisplayName>neue Nummer</DisplayName>',"\n";
	echo '<QueryStringParam>','m=',$mac, '&u=',$user, '&t=',$type,'&value' ,'</QueryStringParam>',"\n";
	echo '<DefaultValue>',$number,'</DefaultValue>',"\n";
	echo '<InputFlags>t</InputFlags>',"\n";
	echo '</InputItem >',"\n";
		
	 defineBackKey();
	echo '</SnomIPPhoneMenu>', "\n";

	_ob_send();
}
#################################### SELECT PHONENUMBERS }

#################################### SET TIMEOUT {
if ( $type == 'timeout' && !isset( $_REQUEST['value']) ) {
	
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['m'] )));
	$user = trim( @ $_REQUEST['u'] );
	$user_id = getUserID( $user );
	$user_name = $db->executeGetOne( 'SELECT `user` FROM `users` WHERE `id`=\''. $db->escape($user_id) .'\'' );
	
	$callforwards = gs_callforward_get( $user_name );
	
	$Title = 'Timeout bei keine Antwort';

	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>',"\n";
	
	$user_id_check = $db->executeGetOne( 'SELECT `user_id` FROM `phones` WHERE `mac_addr`=\''. $db->escape($mac) .'\'' );
	if ( $user_id != $user_id_check )
		_err( 'Not authorized' );
	
	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$remote_addr_check = $db->executeGetOne( 'SELECT `current_ip` FROM `users` WHERE `id`=\''. $user_id.'\''   );
	if ( $remote_addr != $remote_addr_check )
		_err( 'Not authorized' );
	# find best match for unavail timeout
	#
	/* 
	if ( @$callforwards['internal']['unavail']['active'] != 'no'
	  && @$callforwards['external']['unavail']['active'] != 'no' )
	  {
		$timeout = ceil((
			(int)@$callforwards['internal']['unavail']['timeout'] +
			(int)@$callforwards['external']['unavail']['timeout']
		)/2);
	} elseif (@$callforwards['internal']['unavail']['active'] != 'no') {
		$timeout = (int)@$callforwards['internal']['unavail']['timeout'];
	} elseif (@$callforwards['external']['unavail']['active'] != 'no') {
		$timeout = (int)@$callforwards['external']['unavail']['timeout'];
	} else {
		$timeout = 15;
	}
	*/
	$timeout = (int)@$callforwards['internal']['unavail']['timeout'];
	
	echo '<SnomIPPhoneInput>',"\n";
	echo '<Title>',snomXmlEsc($Title),'</Title>',"\n";
	echo '<Prompt>Prompt</Prompt>',"\n";
	echo '<URL>';
	echo  $url_snom_callforward;
	echo '</URL>',"\n";
	echo '<InputItem>',"\n";
	echo '<DisplayName>neue Timeout</DisplayName>',"\n";
	echo '<QueryStringParam>','m=',$mac, '&u=',$user, '&t=timeout&value' ,'</QueryStringParam>',"\n";
	echo '<DefaultValue>',$timeout,'</DefaultValue>',"\n";
	echo '<InputFlags>n</InputFlags>',"\n";
	echo '</InputItem >',"\n";
		
	defineBackKey();
	echo '</SnomIPPhoneMenu>', "\n";

	_ob_send();
}
#################################### SELECT TIMEOUT}


#################################### INITIAL SCREEN {
if (! $type) {
	
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['m'] )));
	$user = trim( @$_REQUEST['u'] );
	$user_id = getUserID( $user );
	
	
	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>', "\n",
	     '<SnomIPPhoneMenu>', "\n",
	       '<Title>Rufumleitung</Title>', "\n\n";
	
	echo '<MenuItem>', "\n",
	        '<Name>', snomXmlEsc('Standardnummer'), '</Name>', "\n",
	        '<URL>', $url_snom_callforward, '?m=',$mac, '&u=',$user, '&t=std', '</URL>', "\n",
	        '</MenuItem>', "\n\n";
	echo '<MenuItem>', "\n",
	        '<Name>', snomXmlEsc('temp. Nummer'), '</Name>', "\n",
	        '<URL>', $url_snom_callforward, '?m=',$mac, '&u=',$user, '&t=var', '</URL>', "\n",
	        '</MenuItem>', "\n\n";
	                                                                   
	
	foreach ($typeToTitle as $t => $title) {
		
		echo '<MenuItem>', "\n",
		       '<Name>', snomXmlEsc($title), '</Name>', "\n",
		       '<URL>', $url_snom_callforward, '?m=',$mac, '&u=',$user, '&t=',$t, '</URL>', "\n",
		     '</MenuItem>', "\n\n";
		# in XML the & must normally be encoded as &amp; but not for
		# the stupid Snom!

	}
	
	echo '<MenuItem>',"\n";
	echo '<Name>',snomXmlEsc('Timeout keine Antw. '),'</Name>',"\n";
	echo '<URL>';
	echo  $url_snom_callforward, '?m=',$mac, '&u=',$user, '&t=timeout';
	echo '</URL>',"\n";  
	echo '</MenuItem>',"\n";
	
	 defineBackMenu();	
	echo '</SnomIPPhoneMenu>', "\n";
	
	_ob_send();	
}

#################################### INITIAL SCREEN }

?>
