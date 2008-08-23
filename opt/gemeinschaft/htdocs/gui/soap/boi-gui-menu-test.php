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

# this is a SOAP test server for the BOI GUI integration


define( 'GS_VALID', true );  /// this is a parent file
require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/log.php' );
set_error_handler('err_handler_die_on_err');


gs_log(GS_LOG_DEBUG, '# BOI SOAP test server called by '. $_SERVER['REMOTE_ADDR'] );
//gs_log(GS_LOG_DEBUG, '# '. print_r($_SERVER, true) );



class BoiGuiServer
{
	function getMenu( $role, $ext )
	{
		$session = (is_array($_COOKIE) && array_key_exists('session', $_COOKIE))
			? @$_COOKIE['session'] : null;
		//return new SoapFault( 'xy', 'something is wrong' );
		$menu = array(
			array('title' => "Anruflisten"      , 'link' => ''     ),
			array('title' => "gew\xC3\xA4hlt"   , 'link' => '/dl/g'),
			array('title' => "verpa\xC3\x9Ft"   , 'link' => '/dl/v'),
			array('title' => "angenommen"       , 'link' => '/dl/a'),
			
			array('title' => "Statistik"        , 'link' => ''     ),
			array('title' => "Queues"           , 'link' => '/s/q' ),
			array('title' => "Gespr\xC3\xA4che" , 'link' => '/s/g' ),
			
			array('title' => "Debug"            , 'link' => ''     ),
			array('title' => "role: $role"      , 'link' => '/d/r' ),
			array('title' => "ext: $ext"        , 'link' => '/d/e' ),
			array('title' => "session: $session", 'link' => '/d/s' ),
		);
		$ret = new stdClass();
		$ret->menu = $menu;
		return $ret;
		////return array('menu'=>$menu);
		//return $menu;
	}
	
	function createSession( $role, $ext )
	{
		return 'sess-'.$role.'-'.$ext.'-'.time();
	}
}



ini_set('soap.wsdl_cache_enabled', false);
ini_set('soap.wsdl_cache_ttl'   , 50);
ini_set('default_socket_timeout', 8);

$service = 'guiintegration';
$api = gs_get_conf('GS_BOI_API_DEFAULT');
switch ($api) {
	case 'm01':
	case 'm02':
		$SoapServer = new SoapServer(
			GS_DIR .'inc/boi-soap/wsdl/'.$service.'-'.$api.'.wsdl',
			array(
				'soap_version'       => SOAP_1_2,
				'encoding'           => 'UTF-8',
				'trace'              => false,
				'exceptions'         => true,
				'features'           => SOAP_SINGLE_ELEMENT_ARRAYS,
				'connection_timeout' => 7,
				'cache_wsdl'         => false
			)
		);
		$SoapServer->setClass('BoiGuiServer');
		//print_r($SoapServer);
		$SoapServer->handle();
		gs_log(GS_LOG_DEBUG, '# BOI SOAP test server done' );
		break;
	default:
		gs_log(GS_LOG_FATAL, '# BOI SOAP test server: Unknown API "'.$api.'"' );
		echo 'Unknown API "'.$api.'"';
}


?>