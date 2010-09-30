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
include_once( GS_DIR .'inc/gs-fns/gs_ami_events.php' );
include_once( GS_DIR .'inc/group-fns.php' );
require_once( GS_DIR .'inc/langhelper.php' );
require_once( GS_DIR .'inc/group-fns.php' );

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

$db = gs_db_slave_connect();

$user = trim( @$_REQUEST['u'] );
$user_id = getUserID( $user );


## Check permissions
#


$user_groups  = gs_group_members_groups_get( array( $user_id ), 'user' );
$members = gs_group_permissions_get ( $user_groups, 'forward' );

if ( count ( $members ) <= 0 )
	_err('Forbidden');

// setup i18n stuff
gs_setlang(gs_get_lang_user($db, $user, GS_LANG_FORMAT_GS));
gs_loadtextdomain("gemeinschaft-gui");
gs_settextdomain("gemeinschaft-gui");

$type = trim( @$_REQUEST['t'] );
if (! in_array( $type, array('internal','external','std','var','timeout'), true )) {
	$type = false;
}


$tmp = array(
	15=>array('k' => 'internal' ,
	          'v' => gs_get_conf('GS_CLIR_INTERNAL', __("von intern")) ),
	25=>array('k' => 'external',
	          'v' => gs_get_conf('GS_CLIR_EXTERNAL', __("von extern")) ),

);

kSort($tmp);
foreach ($tmp as $arr) {
	$typeToTitle[$arr['k']] = $arr['v'];
}


$url_snom_callforward = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'snom/callforward.php';
$url_snom_menu = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'snom/menu.php';


$cases = array(
	'always' => __('immer'),  
	'busy'   => __('besetzt'),
	'unavail'=> __('keine Antw.'),  
	'offline'=> __('offline')
);
$actives = array(
	'no'  => __('Aus'),
	'std' => __('Std.'),
	'var' => __('Tmp.'),
	'vml' => __('AB'),
	'ano' => __('Ansage'),
	'par' => __('Parallel'),
	'trl' => __('Zeitsteuerung')
);
                                                                


function defineBackKey()
{
	global $softkeys, $keys, $user, $type, $mac, $url_snom_callforward;
	
	
	echo '<SoftKeyItem>',
	       '<Name>#</Name>',
	       '<URL>' ,$url_snom_callforward, '?m=',$mac, '&u=',$user, '</URL>',
	     '</SoftKeyItem>', "\n";
	echo '<SoftKeyItem>',
                '<Name>F4</Name>',
                '<Label>' ,snomXmlEsc(__('Zurück')),'</Label>',
                '<URL>',$url_snom_callforward, '?m=',$mac, '&u=',$user, '</URL>',
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
	echo '<SoftKeyItem>',
                '<Name>F4</Name>',
                '<Label>' ,snomXmlEsc(__('Menü')), '</Label>',
                '<URL>', $url_snom_menu, '?', implode('&', $args), '</URL>',
                '</SoftKeyItem>', "\n";
}




################################## SET FEATURE {

if ( $type != false && isset($_REQUEST['value']) ) {



	$value = trim( @$_REQUEST['value'] );
	$user_name = $db->executeGetOne( 'SELECT `user` FROM `users` WHERE `id`=\''. $db->escape($user_id) .'\'' );

	$callforwards = gs_callforward_get( $user_name );
	
	$timeout = (int) $callforwards['internal']['unavail']['timeout']; 


	$num['std'] = $callforwards['internal']['unavail']['number_std']; 
	$num['var'] = $callforwards['internal']['unavail']['number_var']; 

	
	foreach($cases as $case => $v) {
		$internal_val[$case] = 'no';
		$internal_val[$case]  = $callforwards['internal'][$case]['active'];		
	}
	
	foreach($cases as $case => $v) {
		$external_val[$case] = 'no';
		$external_val[$case]  = $callforwards['external'][$case]['active'];
	}
	
	$write = 0;
	
	
	if ( ($type == 'internal' || $type == 'external') &&  isset($_REQUEST['key']) ) {
	
	 	
	 	
		$key = trim( @$_REQUEST['key'] );
		if (isset($cases[$key])) {
		
			
			if($type == 'internal') {
				$internal_val[$key] = $value;
				if( $value == "vml" )
					$callforwards['internal'][$key]['number_vml'] = $target;
			}
			if($type == 'external') {
				$external_val[$key] = $value;
				if( $value == "vml" )
					$callforwards['external'][$key]['number_vml'] = $target;
			}
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
			 $ret = gs_callforward_set( $user_name,'internal', $case, 'vml', $callforwards['internal'][$case]['number_vml'], $timeout );
			 $ret = gs_callforward_activate( $user_name,'internal',$case,$internal_val[$case]);
		}
		foreach ($cases as $case => $gnore2) {
			 $ret = gs_callforward_set( $user_name,'external', $case, 'std', $num['std'], $timeout );
			 $ret = gs_callforward_set( $user_name,'external', $case, 'var', $num['var'], $timeout );
			 $ret = gs_callforward_set( $user_name,'external', $case, 'vml', $callforwards['external'][$case]['number_vml'], $timeout );
			 $ret = gs_callforward_activate( $user_name,'external',$case,$external_val[$case]);
		}
		
		if ( GS_BUTTONDAEMON_USE == true ) {
			gs_diversion_changed_ui ( $user );
		}
	}
	
}

################################# SET FEATURE }  




#################################### SELECT PROBERTIES {
if ( ($type == 'internal' || $type == 'external') && !isset( $_REQUEST['key']) ) {
	
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['m'] )));
	$user_name = $db->executeGetOne( 'SELECT `user` FROM `users` WHERE `id`=\''. $db->escape($user_id) .'\'' );
	

	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>',"\n";
	
	$user_id_check = $db->executeGetOne( 'SELECT `user_id` FROM `phones` WHERE `mac_addr`=\''. $db->escape($mac) .'\'' );
	if ($user_id != $user_id_check)
		_err( 'Not authorized' );
	
	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$remote_addr_check = $db->executeGetOne( 'SELECT `current_ip` FROM `users` WHERE `id`=\''. $user_id.'\''   );
	if ($remote_addr != $remote_addr_check)
		_err( 'Not authorized' );
	
	$callforwards = gs_callforward_get( $user_name );

	foreach($cases as $case => $v){
		$val[$case] = 'no';
		$val[$case]  = $callforwards[$type][$case]['active'];
		if ( $val[$case] == "vml"  ) {

			if ( $callforwards[$type][$case]['number_vml'] == "vm*" . $user )
				$val[$case] = "ano";
		}
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
	defineBackKey();
	echo '</SnomIPPhoneMenu>', "\n",
	_ob_send();
}
#################################### SELECT PROBERITES }



#################################### SET CF-STATES {
if ( $type == 'internal' || $type == 'external' && isset( $_REQUEST['key']) ) {



	$number = $db->executeGetOne( 'SELECT `number_std` FROM `callforwards` WHERE `user_id`=\''. $user_id  .'\' AND `case`=\'unavail\' AND `source`=\'internal\''); 
	
	if ( ! $number || strlen ( trim ( $number ) ) <= 0 )
		unset ( $actives['std'] );
	
	$number = $db->executeGetOne( 'SELECT `number_var` FROM `callforwards` WHERE `user_id`=\''. $user_id  .'\' AND `case`=\'unavail\' AND `source`=\'internal\''); 
		
	if ( ! $number || strlen ( trim ( $number ) ) <= 0 )
		unset ( $actives['var'] );



	# Test for timerules
	$id = (int)$db->executeGetOne('SELECT `_user_id` from `cf_timerules` WHERE `_user_id`=' . $user_id );                                                                                        

	if ( $id ) {
		$actives['trl'] = __('Zeit');
	}

	# Test parallel call
	$id = (int)$db->executeGetOne('SELECT `_user_id` from `cf_parallelcall` WHERE `_user_id`=' . $user_id  );
	if( $id ) {
		$actives['par'] = __('Parallel');
	}

	
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['m'] )));
	$user_name = $db->executeGetOne( 'SELECT `user` FROM `users` WHERE `id`=\''. $db->escape($user_id) .'\'' );
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
	


		$val = 'no';
		$val = $db->executeGetOne( 'SELECT `active` FROM `callforwards` WHERE `user_id`=\''. $user_id  .'\' AND `case`=\'' . $key .'\' AND `source`=\''.$type.'\'');
		
		if ( $val == "vml"  ) {
			$vml_target = $db->executeGetOne( 'SELECT `number_vml` FROM `callforwards` WHERE `user_id`=\''. $user_id  .'\' AND `case`=\'' . $key .'\' AND `source`=\''.$type.'\'');
			if ( $vml_target == "vm*" . $user )
				$val = "ano";
		} 
		
		
		
		
		echo '<SnomIPPhoneMenu>',"\n";
		echo '<Title>', snomXmlEsc($typeToTitle[$type] . ':  ' . $cases[$key] ), '</Title>', "\n";
		
		
		foreach( $actives as $mod => $desc ) {
		
			echo '<MenuItem';
			if($val == $mod)echo ' sel=true';
			echo'>', "\n";
			echo '<Name>',snomXmlEsc($desc),'</Name>',"\n";
			echo '<URL>';
			echo  $url_snom_callforward, '?m=',$mac, '&u=',$user, '&t=',$type,'&key='.$key.'&value=' . $mod;
			echo '</URL>',"\n";  
			echo '</MenuItem>',"\n";
		
		}
		
		defineBackKey();
		echo '</SnomIPPhoneMenu>',"\n";
		
	_ob_send();
	
	
}
#################################### SET CF-STATES }



#################################### SET PHONENUMBERS {
if ( $type == 'std' || $type == 'var' && !isset( $_REQUEST['value']) ) {
	
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['m'] )));
	
	if( $type == 'varnumber')$Title = __('temp. Nummer');
	else $Title = __('Standardnummer');

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
		
	echo '<SnomIPPhoneInput>',"\n";
	echo '<Title>',snomXmlEsc($Title),'</Title>',"\n";
	echo '<Prompt>'. __("Prompt") .'</Prompt>',"\n";
	echo '<URL>';
	echo  $url_snom_callforward;
	echo '</URL>',"\n";
	echo '<InputItem>',"\n";
	echo '<DisplayName>'. __("neue Nummer") .'</DisplayName>',"\n";
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
	$user_name = $db->executeGetOne( 'SELECT `user` FROM `users` WHERE `id`=\''. $db->escape($user_id) .'\'' );
	
	$callforwards = gs_callforward_get( $user_name );
	
	$Title = __('Timeout bei keine Antwort');

	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>',"\n";
	
	$user_id_check = $db->executeGetOne( 'SELECT `user_id` FROM `phones` WHERE `mac_addr`=\''. $db->escape($mac) .'\'' );
	if ( $user_id != $user_id_check )
		_err( 'Not authorized' );
	
	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$remote_addr_check = $db->executeGetOne( 'SELECT `current_ip` FROM `users` WHERE `id`=\''. $user_id.'\''   );
	if ( $remote_addr != $remote_addr_check )
		_err( 'Not authorized' );

	$timeout = (int)@$callforwards['internal']['unavail']['timeout'];
	
	echo '<SnomIPPhoneInput>',"\n";
	echo '<Title>',snomXmlEsc($Title),'</Title>',"\n";
	echo '<Prompt>'. __("Prompt") .'</Prompt>',"\n";
	echo '<URL>';
	echo  $url_snom_callforward;
	echo '</URL>',"\n";
	echo '<InputItem>',"\n";
	echo '<DisplayName>'. __("neue Timeout") .'</DisplayName>',"\n";
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
	
	
	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>', "\n",
	     '<SnomIPPhoneMenu>', "\n",
	       '<Title>'. __("Rufumleitung") .'</Title>', "\n\n";
	
	echo '<MenuItem>', "\n",
	        '<Name>', snomXmlEsc(__('Standardnummer')), '</Name>', "\n",
	        '<URL>', $url_snom_callforward, '?m=',$mac, '&u=',$user, '&t=std', '</URL>', "\n",
	        '</MenuItem>', "\n\n";
	echo '<MenuItem>', "\n",
	        '<Name>', snomXmlEsc(__('temp. Nummer')), '</Name>', "\n",
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
	echo '<Name>',snomXmlEsc(__('Timeout keine Antw. ')),'</Name>',"\n";
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
