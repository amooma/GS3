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
include_once( GS_DIR .'inc/log.php' );


# the functions in this file implement BOI API "m01" and "m02"


//ini_set('soap.wsdl_cache_enabled', true);
ini_set('soap.wsdl_cache_enabled', false);
# pointless for local WSDL files


function obj2arr_r( $mixed )
{
	if (is_object($mixed)) $mixed = get_object_vars($mixed);
	if (is_array($mixed)) {
		foreach ($mixed as $key => $val) {
			if (is_array($val) || is_object($val)) {
				$mixed[$key] = obj2arr_r($val);
			}
		}
	}
	return $mixed;
}

function _soap_get_item( $arr )
{
	if (! is_array($arr)) {
		$arr = obj2arr_r($arr);
		if (! is_array($arr)) {
			# it might be a string or something
			return array('value'=>$arr);
		}
	}
	if (array_key_exists('item', $arr)) $arr = $arr['item'];
	$item = array();
	foreach ($arr as $k => $v) {
		if (is_array($v)
		&& array_key_exists('key', $v)
		&& array_key_exists('value', $v)
		) {
			$item[$v['key']] = $v['value'];
		} else {
			$item[$k] = $v;
		}
	}
	return $item;
}

function _fix_broken_soap_array( $mixed )
{
	$arr = obj2arr_r($mixed);
	$arr2 = array();
	if (is_array($arr)) {
		if (array_key_exists('Map', $arr)) {
			$arr = $arr['Map'];
		}
		foreach ($arr as $k => $item) {
			$arr2[$k] = _soap_get_item( $item );
		}
	}
	return $arr2;
}


function gs_get_soap_client( $api, $service, $host )
{
	try {
		if (! in_array($api, array('m01', 'm02'), true)) {
			gs_log(GS_LOG_WARNING, 'Invalid foreign host API "'.$api.'"' );
			return false;
		}
		switch ($service) {
			case 'guiintegration' : $path = '/soap/guiServer.php'      ; break;
			case 'generatecall'   : $path = '/soap/callServer.php'     ; break;
			case 'updateextension': $path = '/soap/extensionServer.php'; break;
			default               : return false;
		}
		use_soap_error_handler(false);
		$SoapClient = null;
		$SoapClient = new SoapClient(
			dirName(__FILE__) .'/wsdl/'.$service.'-'.$api.'.wsdl',
			array(
				'location'           => 'http://'.$host.$path,
				'soap_version'       => SOAP_1_2,
				'login'              => null,
				'password'           => null,
				'proxy_host'         => null,
				'proxy_port'         => null,
				'proxy_login'        => null,
				'proxy_password'     => null,
				'local_cert'         => null,
				'passphrase'         => null,
				'encoding'           => 'UTF-8',
				//'compression'        => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_DEFLATE | 5,
				'compression'        => null,  // makes debugging easier
				'trace'              => false,
				'exceptions'         => true,
				'connection_timeout' => 6,
				'user_agent'         => 'Gemeinschaft',
				'features'           => SOAP_SINGLE_ELEMENT_ARRAYS
			)
		);
		gs_log(GS_LOG_DEBUG, 'SOAP call to http://'.$host.$path );
		return $SoapClient;
	}
	catch(SOAPFault $SoapFault) {
		//echo 'SOAP error: ', $SoapFault->faultstring ,"\n";
		//print_r($SoapFault);
		gs_log(GS_LOG_WARNING, 'SOAP error: '. $SoapFault->faultstring .' ('. @$SoapFault->faultcode .')' );
		return false;
	}
}


function _gs_boi_update_extension( $api, $host, $route_prefix, $ext, $user, $sip_pwd, $pin, $firstname, $lastname, $email, &$soap_faultcode )
{
	$old_default_socket_timeout = ini_get('default_socket_timeout');
	ini_set('default_socket_timeout', 8);
	$old_soap_wsdl_cache_ttl    = ini_get('soap.wsdl_cache_ttl');
	ini_set('soap.wsdl_cache_ttl'   , 3600);
	
	$soap_faultcode = null;
	$SoapClient = gs_get_soap_client( $api, 'updateextension', $host );
	if (! $SoapClient) return false;
	
	if ($user == '') {  # i.e. delete
		try {
			$ret = @$SoapClient->updateExtension(
				$ext,
				array(
					array('SubscriberPersNr'        => $user ),
					array('SubscriberSIPName'       => $ext ),
					array('SubscriberAccountPasswd' => $sip_pwd ),
					array('SubscriberIntNumber'     =>
						(($user != '') ? (
							'+'.gs_get_conf('GS_CANONIZE_COUNTRY_CODE') .
							(gs_get_conf('GS_CANONIZE_NATL_PREFIX_INTL')
								? gs_get_conf('GS_CANONIZE_NATL_PREFIX') : '') .
							gs_get_conf('GS_CANONIZE_AREA_CODE') .
							gs_get_conf('GS_CANONIZE_LOCAL_BRANCH') .
							$route_prefix .
							$ext
						) : '')
					),
					array('SubscriberVmPin'         => $pin ),
					array('SubscriberName'          => trim($firstname .' '. $lastname) ),
					array('SubscriberVmEmail'       => $email ),
					array('SubscriberOutboundProxy' => '' ),
					array('SubscriberTelType'       => '' )
				),
				'default'  # reset to default values
			);
		}
		catch(SOAPFault $SoapFault) {
			gs_log(GS_LOG_NOTICE, 'SOAP error: '. $SoapFault->faultstring .' ('. @$SoapFault->faultcode .')' );
			//$soap_faultcode = @$SoapFault->faultcode;
			//return false;
		}
	}
	
	try {
		$ret = @$SoapClient->updateExtension(
			$ext,
			array(
				array('SubscriberPersNr'        => $user ),
				array('SubscriberSIPName'       => $ext ),
				array('SubscriberAccountPasswd' => $sip_pwd ),
				array('SubscriberIntNumber'     =>
					(($user != '') ? (
						'+'.gs_get_conf('GS_CANONIZE_COUNTRY_CODE') .
						(gs_get_conf('GS_CANONIZE_NATL_PREFIX_INTL')
							? gs_get_conf('GS_CANONIZE_NATL_PREFIX') : '') .
						gs_get_conf('GS_CANONIZE_AREA_CODE') .
						gs_get_conf('GS_CANONIZE_LOCAL_BRANCH') .
						$route_prefix .
						$ext
					) : '')
				),
				array('SubscriberVmPin'         => $pin ),
				array('SubscriberName'          => trim($firstname .' '. $lastname) ),
				array('SubscriberVmEmail'       => $email ),
				array('SubscriberOutboundProxy' => '' ),
				array('SubscriberTelType'       => '' )
			),
			'update'  # update
		);
		if ($ret === null || $ret === false) {
			gs_log(GS_LOG_WARNING, 'Got empty SOAP response!');
			return false;
		} else {
			$ret = obj2arr_r($ret);
			if (is_array($ret) && array_key_exists('errorcode', $ret))
				$ret = $ret['errorcode'];
			//echo "<pre>", print_r($ret, true) ,"</pre>";
			$error_code = (int)$ret;
			
			# error_code > 0: OK, error_code < 0: error
			return ($error_code > 0);
		}
	}
	catch(SOAPFault $SoapFault) {
		gs_log(GS_LOG_WARNING, 'SOAP error: '. $SoapFault->faultstring .' ('. @$SoapFault->faultcode .')' );
		$soap_faultcode = @$SoapFault->faultcode;
		return false;
	}
	
	ini_set('default_socket_timeout', $old_default_socket_timeout);
	ini_set('soap.wsdl_cache_ttl'   , $old_soap_wsdl_cache_ttl);
}

function gs_boi_update_extension( $api, $host, $route_prefix, $ext, $user, $sip_pwd, $pin, $firstname, $lastname, $email, &$soap_faultcode )
{
	gs_log(GS_LOG_DEBUG, "BOI SOAP: Updating ext. $route_prefix-$ext at host $host" );
	return _gs_boi_update_extension( $api, $host, $route_prefix, $ext, $user, $sip_pwd, $pin, $firstname, $lastname, $email, /*&*/$soap_faultcode );
}

function gs_boi_delete_extension( $api, $host, $route_prefix, $ext, &$soap_faultcode )
{
	gs_log(GS_LOG_DEBUG, "BOI SOAP: Deleting ext. $route_prefix-$ext at host $host" );
	return _gs_boi_update_extension( $api, $host, $route_prefix, $ext, '', '', '', '', '', '', /*&*/$soap_faultcode );
}



function gs_boi_gs_role_to_server_role( $role )
{
	switch ($role) {
		case 'user'      : return 'user';                  # aka "EXT"
		case 'localadmin': return 'agenturadministrator';  # aka "admin"
		case 'sysadmin'  : return 'administrator';         # aka "expert"
		default          : return $role;
	}
}


function gs_boi_get_gui_menu( $api, $host, $role, $ext, $session=null )
{
	$old_default_socket_timeout = ini_get('default_socket_timeout');
	ini_set('default_socket_timeout', 8);
	$old_soap_wsdl_cache_ttl    = ini_get('soap.wsdl_cache_ttl');
	ini_set('soap.wsdl_cache_ttl'   , 3600);
	
	gs_log(GS_LOG_DEBUG, 'BOI SOAP: Getting menu for '.$role.', '.$ext.' from '.$host );
	$SoapClient = gs_get_soap_client( $api, 'guiintegration', $host );
	if (! $SoapClient) return false;
	
	try {
		if ($session !== null)
			$SoapClient->__setCookie('session', $session);
		$ret = $SoapClient->getMenu( gs_boi_gs_role_to_server_role($role), $ext );
		/*
		$ret = $SoapClient->__soapCall( 'getMenu',
			array( gs_boi_gs_role_to_server_role($role), $ext),
			null
			);
		*/
		//print_r($ret);
		if ($ret === null || $ret === false) {
			gs_log(GS_LOG_WARNING, 'Got empty SOAP response!');
			return false;
		} else {
			$ret = obj2arr_r($ret);
			if (is_array($ret) && array_key_exists('menu', $ret))
				$ret = $ret['menu'];
			//echo "<pre>", print_r($ret, true) ,"</pre>";
			$ret = _fix_broken_soap_array($ret);
			/*
			$ret = array(
				array('title' => "Anruflisten"     , 'link' => ''     ),
				array('title' => "gew\xC3\xA4hlt"  , 'link' => '/dl/g'),
				array('title' => "verpa\xC3\x9Ft"  , 'link' => '/dl/v'),
				array('title' => "angenommen"      , 'link' => '/dl/a'),
				
				array('title' => "Statistik"       , 'link' => ''     ),
				array('title' => "Queues"          , 'link' => '/s/q' ),
				array('title' => "Gespr\xC3\xA4che", 'link' => '/s/g' )
			);
			*/
			//echo "<pre>", print_r($ret, true) ,"</pre>";
			if (! is_array($ret)) return false;
			
			$menu = array();
			//$main_menu_cnt = 0;
			//$sub_menu_cnt  = 0;
			foreach ($ret as $entry) {
				if (! is_array($entry)) continue;
				if (! array_key_exists('title', $entry)
				||  ! array_key_exists('link' , $entry)) {
					continue;
				}
				
				if (trim($entry['link']) == '') {  # is a main menu
					//++$main_menu_cnt;
					//$menu['boi'.$main_menu_cnt] = array(
					$key = 'boi-'. mb_subStr(preg_replace('/[^a-z0-9\-_]/', '',
						str_replace(
							array("\xC3\xA4" ,"\xC3\xB6" ,"\xC3\xBC" ,"\xC3\x9F"),
							array('ae'       ,'oe'       ,'ue'       ,'ss'      ),
						str_replace(' ', '-',
						mb_strToLower(
						html_entity_decode($entry['title']))))), 0, 20);
					while (array_key_exists($key, $menu)) $key.='-';
					$menu[$key] = array(
						'is_boi' => true,
						'title'  => htmlEnt(html_entity_decode(trim($entry['title']), ENT_QUOTES, 'UTF-8')),
						'icon'   => 'crystal-svg/%s/act/misc.png',
						'sub'    => array()
					);
					# html_entity_decode() is done because the other party already
					# encodes special characters although it shouldn't. don't remove
					# the htmlEnt() around it - doing so would make this vulnerable.
					//$sub_menu_cnt = 0;
				}
				else {
					//$main_menu_idx = count($menu)-1;
					end($menu);
					$main_menu_idx = key($menu);
					if (! array_key_exists($main_menu_idx, $menu)) continue;
					//++$sub_menu_cnt;
					//$menu[$main_menu_idx]['sub']['boi'.$sub_menu_cnt] = 3;
					$key = mb_subStr(preg_replace('/[^a-z0-9\-_]/', '',
						str_replace(
							array("\xC3\xA4" ,"\xC3\xB6" ,"\xC3\xBC" ,"\xC3\x9F"),
							array('ae'       ,'oe'       ,'ue'       ,'ss'      ),
						str_replace(' ', '-',
						mb_strToLower(
						html_entity_decode($entry['title']))))), 0, 20);
					while (array_key_exists($key, $menu[$main_menu_idx]['sub'])) $key.='-';
					if (subStr($entry['link'],0,1) !== '/')
						$entry['link'] = '/'. $entry['link'];
					$menu[$main_menu_idx]['sub'][$key] = array(
						'title'  => htmlEnt(html_entity_decode(trim($entry['title']), ENT_QUOTES, 'UTF-8')),
						'boi_url'=>         trim($entry['link' ])
					);
					# see comment about html_entity_decode() above
				}
			}
			//echo "<pre>"; print_r($menu); echo "</pre>";
			return $menu;
		}
	}
	catch(SOAPFault $SoapFault) {
		//print_r($ret);
		gs_log(GS_LOG_WARNING, 'SOAP error: '. $SoapFault->faultstring .' ('. @$SoapFault->faultcode .')' );
		return false;
	}
	
	ini_set('default_socket_timeout', $old_default_socket_timeout);
	ini_set('soap.wsdl_cache_ttl'   , $old_soap_wsdl_cache_ttl);
}


function gs_boi_start_gui_session( $api, $host, $role, $ext, &$soap_faultcode )
{
	$old_default_socket_timeout = ini_get('default_socket_timeout');
	ini_set('default_socket_timeout', 8);
	$old_soap_wsdl_cache_ttl    = ini_get('soap.wsdl_cache_ttl');
	ini_set('soap.wsdl_cache_ttl'   , 3600);
	
	$soap_faultcode = null;
	gs_log(GS_LOG_DEBUG, 'BOI SOAP: Starting session for '.$role.', '.$ext.' at '.$host );
	$SoapClient = gs_get_soap_client( $api, 'guiintegration', $host );
	if (! $SoapClient) return false;
	
	try {
		$ret = @$SoapClient->createSession( gs_boi_gs_role_to_server_role($role), $ext );
		if ($ret === null || $ret === false) {
			gs_log(GS_LOG_WARNING, 'Got empty SOAP response!');
			return false;
		} else {
			//echo "<pre>", print_r($ret, true) ,"</pre>";
			$ret = obj2arr_r($ret);
			//echo "<pre>", print_r($ret, true) ,"</pre>";
			if (is_array($ret) && array_key_exists('session_id', $ret))
				$ret = $ret['session_id'];
			//echo "<pre>", print_r($ret, true) ,"</pre>";
			return (string)$ret;
		}
	}
	catch(SOAPFault $SoapFault) {
		//print_r($ret);
		gs_log(GS_LOG_WARNING, 'SOAP error: '. $SoapFault->faultstring .' ('. @$SoapFault->faultcode .')' );
		$soap_faultcode = @$SoapFault->faultcode;
		return false;
	}
	
	ini_set('default_socket_timeout', $old_default_socket_timeout);
	ini_set('soap.wsdl_cache_ttl'   , $old_soap_wsdl_cache_ttl);
}


function gs_boi_call_init( $api, $host, $user, $to, $from, $cidnum, $clir, $is_private )
{
	gs_log(GS_LOG_DEBUG, "BOI SOAP: Init call on $host for user: $user from number: $from to number: $to, CLIR: ".($clir ?'yes':'no').", private: ".($is_private ?'yes':'no') );
	
	$old_default_socket_timeout = ini_get('default_socket_timeout');
	ini_set('default_socket_timeout', 8);
	$old_soap_wsdl_cache_ttl    = ini_get('soap.wsdl_cache_ttl');
	ini_set('soap.wsdl_cache_ttl'   , 3600);
	
	$SoapClient = gs_get_soap_client( $api, 'generatecall', $host );
	if (! $SoapClient) return false;
	
	try {
		$ret = @$SoapClient->generateCall(
			array(
				array('CTIOutboundUser'   => $user ),
				array('CTIOutboundTo'     => $to ),
				array('CTIOutboundFrom'   => $from ),
				array('CTIOutboundCIDnum' => $cidnum ),
				array('CTIOutboundCLIR'   => $clir ),
				array('CTIOutboundPrv'    => $is_private )
			)
		);
		if ($ret === null || $ret === false) {
			gs_log(GS_LOG_WARNING, 'Got empty SOAP response!');
			return false;
		} else {
			$ret = obj2arr_r($ret);
			if (is_array($ret) && array_key_exists('errorcode', $ret))
				$ret = $ret['errorcode'];
			$error_code = (int)$ret;
			# error_code > 0: OK, error_code < 0: error
			return ($error_code > 0);
		}
	}
	catch(SOAPFault $SoapFault) {
		gs_log(GS_LOG_WARNING, 'SOAP error: '. $SoapFault->faultstring .' ('. @$SoapFault->faultcode .')' );
		return false;
	}
	
	ini_set('default_socket_timeout', $old_default_socket_timeout);
	ini_set('soap.wsdl_cache_ttl'   , $old_soap_wsdl_cache_ttl);
}


?>