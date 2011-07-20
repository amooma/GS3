<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
*                    Add-on Siemens provisioning
* 
* $Revision: 366 $
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

//define( 'GS_VALID', true );  /// this is a parent file
defined('GS_VALID') or die('No direct access.');

//require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'inc/prov-fns.php' );
include_once( dirName(__FILE__) .'/conf.php' );
include_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/gs-fns/gs_keys_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_prov_params_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_prov_params_get.php' );

$raw_debug = (bool)gs_get_conf('GS_PROV_SIEMENS_LOG_RAW');
$debug_file = '/var/log/gemeinschaft/siemens-access-debug.log';
$use_mobility = false;

$nonce = '';

function _err_handler_siemens( $type, $msg, $file, $line )
{
	global $nonce;
	if (@$nonce == '') $nonce = '';
	
	switch ($type) {
		case E_NOTICE:
		case E_USER_NOTICE:
			if (error_reporting() != 0)  # not suppressed by @
				gs_log( GS_LOG_NOTICE, 'PHP: '. $msg .' in '. $file .' on line '. $line );
			break;
		case E_STRICT:
			if (error_reporting() != 0)
				gs_log( GS_LOG_DEBUG, 'PHP (Strict): '. $msg .' in '. $file .' on line '. $line );
			else  # suppressed by @
				gs_log( GS_LOG_DEBUG, 'PHP (strict): '. $msg .' in '. $file .' on line '. $line );
			break;
		default:
			if (error_reporting() != 0 || $type != E_USER_WARNING) {
				gs_log( GS_LOG_FATAL  , 'PHP: '. $msg .' in '. $file .' on line '. $line ."\n" );
				@ob_end_clean();
				_dls_response_cleanup( $nonce, 'Error' );
			} else {  # suppressed by @
				gs_log( GS_LOG_NOTICE, 'PHP: '. $msg .' in '. $file .' on line '. $line ."\n" );
			}
	}
}
set_error_handler('_err_handler_siemens');

/*
Um dieses Skript benutzen zu koennen ist die Einrichtung eines
HTTPS-Web-Servers erforderlich, die an anderer Stelle beschrieben
wird.
*/
$orig_url = @$_REQUEST['_url'];
if (subStr($orig_url,0,1) !== '/') $orig_url = '/'.$orig_url;
gs_log( GS_LOG_DEBUG, "Request URL is $orig_url" );



function _get_response_headers_as_string()
{
	global $_SERVER;
	if (! function_exists('headers_list')) {
		gs_log( GS_LOG_DEBUG, 'Can\'t get response headers (PHP < 5)' );
		return false;
	}
	$ret = '';
	$ret.= $_SERVER['SERVER_PROTOCOL'] .' 200 OK'. "\r\n";
	$headers = headers_list();
	foreach ($headers as $header) {
		$ret .= $header ."\r\n";
	}
	$ret .= "\r\n";
	return $ret;
}

function _get_request_headers_as_string()
{
	global $_SERVER, $orig_url;
	$ret = '';
	$ret.= $_SERVER['REQUEST_METHOD'] .' '. $orig_url .' '. $_SERVER['SERVER_PROTOCOL'] ."\r\n";
	foreach ($_SERVER as $k => $v) {
		if (subStr($k,0,5) === 'HTTP_') {
			$k = str_replace(' ','-',ucWords(str_replace('_',' ',strToLower( subStr($k,5) ))));
			$ret .= $k .': '. $v ."\r\n";
		}
	}
	$ret .= "\r\n";
	return $ret;
}


function _write_raw_log( $str, $is_incoming=false )
{
	global $raw_debug, $debug_file, $_SERVER;
	static $complain = true;
	
	if (! $raw_debug) return false;
	
	if (! file_exists($debug_file)) {
		@exec('touch '. escapeShellArg($debug_file) .' 1>>/dev/null 2>>/dev/null', $out, $err);
		if ($err != 0) {
			if ($complain)
				gs_log( GS_LOG_DEBUG, "Siemens prov.: Failed to create \"$debug_file\"" );
			$complain = false;  # complain only once
			return false;
		}
	}
	if (! is_writable($debug_file)) {
		if ($complain)
			gs_log( GS_LOG_DEBUG, "Siemens prov.: File \"$debug_file\" not writable" );
		$complain = false;  # complain only once
		return false;
	}
	$fh = @fOpen($debug_file, 'ab');
	if (! is_resource($fh)) {
		if ($complain)
			gs_log( GS_LOG_DEBUG, "Siemens prov.: Failed to open \"$debug_file\"" );
		$complain = false;  # complain only once
		return false;
	}
	$ok = @fWrite($fh,
		str_repeat(($is_incoming ? '>':'<'), 40) ."\n".
		date('Y-m-d H:i:s') ."  -  ". $str ."\n" );
	if (! $ok) {
		if ($complain)
			gs_log( GS_LOG_DEBUG, "Siemens prov.: Failed to write to \"$debug_file\"" );
		$complain = false;  # complain only once
		return false;
	}
	//@fFlush($fh);
	@fClose($fh);
	return true;
}

function _siemens_xml_esc( $str )
{
	//return htmlSpecialChars($str, ENT_QUOTES, 'UTF-8');
	# the stupid Siemens phone does not understand &lt;, &gt, &amp;
	# - neither as named nor as numbered entities
	# it does understand ' as &apos;, &#039;, &#39;, &#x27;
	# and                " as $quot;, &#034;, &#34;, &#x22;
	return str_replace(
		array('<', '>', '&', '\''   , '"'    ),
		array('' , '' , '+', '&#39;', '&#34;'),
	$str);
}

function _session_start()
{
	# start or bind to session
	#
	ini_set('session.use_cookies'     , 1 );
	ini_set('session.use_only_cookies', 1 );
	ini_set('session.cookie_lifetime' , 0 );
	ini_set('session.cookie_path'     , '/DeploymentService/');
	ini_set('session.cookie_domain'   , '');
	ini_set('session.cookie_secure'   , 1 );
	ini_set('session.cookie_httponly' , 0 );
	ini_set('session.referer_check'   , '');
	ini_set('session.hash_function'   , 0);
	ini_set('session.hash_bits_per_character', 4);
	ini_set('session.use_trans_sid'   , 0 );
	ini_set('url_rewriter.tags'       , '');
	session_name('JSESSIONID'); # same as Siemens DLS uses
	session_start();
	# resulting header is:
	# Set-Cookie: JSESSIONID=e06994048937d1788fe118979b88bfae; path=/DeploymentService; secure
	# original DLS header:
	# Set-Cookie: JSESSIONID=E06994048937D1788FE118979B88BFAE; Path=/DeploymentService; Secure
}

function _dls_message_open( $nonce )
{
	echo '<','?xml version="1.0" encoding="utf-8"?','>',"\n",
		'<DLSMessage',"\n",
		"\t", 'xmlns="http://www.siemens.com/DLS"',"\n",
		"\t", 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"',"\n",
		"\t", 'xsi:schemaLocation="http://www.siemens.com/DLS">',"\n",
		'<Message nonce="', $nonce, '">',"\n";
}
function _dls_message_close()
{
	echo '</Message>',"\n",
	'</DLSMessage>',"\n";
}
function _dls_response_simple( $nonce, $action='CleanUp', $errmsg='' )
{
	$action = preg_replace('/[^a-zA-Z0-9\-_ ]/', '', $action);
	gs_log( GS_LOG_DEBUG, "Siemens prov.: Sending $action" );
	
	@ob_end_clean();
	ob_start();
	_dls_message_open( $nonce );
	echo "\t", '<Action>', $action ,'</Action>',"\n";
	if ($errmsg != '')
		echo '  <!-- ', $errmsg, ' -->', "\n";
	_dls_message_close();
	$ob = ob_get_clean();
	if (! headers_sent()) {
		header( 'X-Powered-By: Gemeinschaft' );
		header( 'Content-Type: text/xml' );
		header( 'Content-Length: '. strLen($ob) );
		/*
		if (in_array(strToLower($action), array('cleanup','restart'), true)) {
		if (is_array($_SERVER) && array_key_exists('HTTP_COOKIE', $_SERVER)) {
			$cookies = explode(';', $_SERVER['HTTP_COOKIE']);
			foreach ($cookies as $cookie) {
				$parts = explode('=', $cookie);
				$cookie_name = trim($parts[0]);
				if ($cookie_name == session_name()) {
					@setCookie( $cookie_name, '', time()-31536000,
						(string)ini_get('session.cookie_path'    ),
						(string)ini_get('session.cookie_domain'  ),
						  (bool)ini_get('session.cookie_secure'  ),
						  (bool)ini_get('session.cookie_httponly')
						);
				}
			}
		}
		}
		*/
	}
	echo $ob;
	if (function_exists('_write_raw_log')) {
		@_write_raw_log("OUR RESPONSE:\n\n". _get_response_headers_as_string() . $ob);
	}
	
	if (in_array(strToLower($action), array('cleanup','restart'), true)) {
		$_SESSION = array();
		//@session_destroy();  // could lead to a fatal error ("session_destroy(): Trying to destroy uninitialized session")
	}
	die();
}
function _dls_response_cleanup( $nonce, $errmsg='' )
{
	_dls_response_simple( $nonce, 'CleanUp', $errmsg );
}
function _dls_response_restart( $nonce, $errmsg='' )
{
	_dls_response_simple( $nonce, 'Restart', $errmsg );
}


function _set_cfg( $key, $i, $val, $force=false )
{
	global $cur_cfg, $new_cfg;
	
	if (! $force) {
		if (! @array_key_exists($key, $cur_cfg)) {
			# phone didn't send this param
			return false;
		}
		if (! @is_array($cur_cfg[$key])) {
			# it's a simple param
			if ( $val != @$cur_cfg[$key]
			||   $val != @$new_cfg[$key] )
				$new_cfg[$key] = $val;
			if (@$new_cfg[$key] == @$cur_cfg[$key])
				unset($new_cfg[$key]);
		} else {
			# it's an array param
			if ( ! @array_key_exists($key, $cur_cfg)
			||   ! @is_array($cur_cfg[$key])
			/*||   ! @array_key_exists($i, $cur_cfg[$key])*/ ) {
				# phone didn't send this array key
				return false;
			}
			if ( $val != @$cur_cfg[$key][$i]
			||   $val != @$new_cfg[$key][$i] ) {
				if ( ! @array_key_exists($key, $new_cfg)
				||   ! @is_array($new_cfg[$key]) )
					$new_cfg[$key] = array();
				$new_cfg[$key][$i] = $val;
			}
			if (@$new_cfg[$key][$i] == @$cur_cfg[$key][$i])
				unset($new_cfg[$key][$i]);
		}
	} else {
		if ($i === null) {
			if ( ! @array_key_exists($key, $cur_cfg)
			||   $val != @$cur_cfg[$key]
			||   $val != @$new_cfg[$key] )
				$new_cfg[$key] = $val;
			if (@$new_cfg[$key] == @$cur_cfg[$key]
			&&  substr($key,0,4) != 'XML-')  # why?
				unset($new_cfg[$key]);
		} else {
			if (! @array_key_exists($key, $new_cfg) || ! is_array($new_cfg[$key]))
				$new_cfg[$key] = array();
			if ( ! @array_key_exists($key, $cur_cfg)
			||   ! @is_array($cur_cfg[$key])
			/*||   ! @array_key_exists($i, $cur_cfg[$key])*/
			||   $val != @$cur_cfg[$key][$i]
			||   $val != @$new_cfg[$key][$i] )
				$new_cfg[$key][$i] = $val;
			if (@$new_cfg[$key][$i] == @$cur_cfg[$key][$i]
			&&  substr($key,0,4) != 'XML-')  # why?
				unset($new_cfg[$key][$i]);
		}
	}
	return true;
}


function _siemens_normalize_version( $appvers )
{
	if (preg_match( '/^V?(\d+)(?:\s*|\.)R?(\d+)\.(\d+)\.(\d+)/i', $appvers, $m)) {
		return
			str_pad($m[1], 2, '0', STR_PAD_LEFT) .'.'.
			str_pad($m[2], 2, '0', STR_PAD_LEFT) .'.'.
			str_pad($m[3], 2, '0', STR_PAD_LEFT) .'.'.
			str_pad($m[4], 3, '0', STR_PAD_LEFT) ;
		# e.g. "V1 R0.12.2" => "01.00.12.002"
	} else {
		return null;
	}
}


if (! gs_get_conf('GS_SIEMENS_PROV_ENABLED')) {
	gs_log( GS_LOG_DEBUG, "Siemens provisioning not enabled" );
	_dls_response_cleanup( '', 'Not enabled.' );
}

$requester = gs_prov_check_trust_requester();
if (! $requester['allowed']) {
	_dls_response_cleanup( '', 'Error. See log for details.' );
}

if ($raw_debug) {
	ob_start();
	
	/*
	echo "SERVER:\n\n";
	print_r(@$_SERVER);
	echo "\n";
	
	echo "REQUEST:\n\n";
	print_r(@$_REQUEST);
	echo "\n";
	*/
	
	//echo "RAW POST:\n\n";
	echo "PHONE ". $requester['phone_ip'] ." SAYS:\n\n";
	echo _get_request_headers_as_string();
	echo @$HTTP_RAW_POST_DATA;
	echo "\n";
	
	$ob = ob_get_clean();
	@_write_raw_log($ob, true);
	unset($ob);
}

//if ($orig_url !== '/DeploymentService/LoginService') {
if (subStr($orig_url, 0, 18) !== '/DeploymentService') {
	if (subStr($orig_url, 0, 11) === '/ringtones/') {
		$ringtone_basename = subStr($orig_url,11);
		gs_log( GS_LOG_NOTICE, "Siemens prov.: Ringtone requested: ". $ringtone_basename );
		@ob_start();
		if (preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-_.]+$/', $ringtone_basename)) {
			$file = '/opt/gemeinschaft/htdocs/prov/ringtones/'. $ringtone_basename;
			$ok = @readFile($file);
		}
		$ob = @ob_get_clean();
		if (! headers_sent()) {
			header( 'X-Powered-By: Gemeinschaft' );
			header( 'Content-Type: audio/mpeg' );
			header( 'Content-Length: '. strLen($ob) );
		}
		echo $ob;
		unset($ob);
		@_write_raw_log("OUR RESPONSE:\n\n". _get_response_headers_as_string() );
		exit(0);
	}
	gs_log( GS_LOG_NOTICE, "Siemens prov.: Invalid URL (\"$orig_url\")" );
	# don't explain this to the user
	_dls_response_cleanup( '', 'Error. See log for details.' );
}
if (@$_SERVER['REQUEST_METHOD'] !== 'POST') {
	gs_log( GS_LOG_NOTICE, "Siemens prov.: Request method must be \"POST\"" );
	# don't explain this to the user
	_dls_response_cleanup( '', 'Error. See log for details.' );
}
if (isSet($HTTP_RAW_POST_DATA)) {
	$raw_post = $HTTP_RAW_POST_DATA;
} else {
	$raw_post = file_get_contents('php://input');
	if (trim($raw_post) == '') {
		gs_log( GS_LOG_NOTICE, "Siemens prov.: Raw HTTP POST data not available" );
		# don't explain this to the user
		_dls_response_cleanup( '', 'Error. See log for details.' );
	}
}
if (strLen($raw_post) > 1000000) {
	gs_log( GS_LOG_NOTICE, "Siemens prov.: Message bigger than 1 MB" );
	# don't explain this to the user
	_dls_response_cleanup( '', 'Error. See log for details.' );
}





_session_start();

$raw_post_start = subStr($raw_post, 0, 1500);

if (! preg_match( '/<WorkpointMessage/', $raw_post_start )) {
	gs_log( GS_LOG_NOTICE, "Siemens prov.: Invalid request" );
	# don't explain this to the user
	_dls_response_cleanup( '', 'Error. See log for details.' );
}

if (! preg_match( '/nonce\s*=\s*"([0-9a-fA-F]+)"/', $raw_post_start, $m )) {
	gs_log( GS_LOG_NOTICE, "Siemens prov.: Nonce missing" );
	# don't explain this to the user
	_dls_response_cleanup( $nonce, 'Error. See log for details.' );
}
$nonce = $m[1];



function get_device_uuid()
{
	global $raw_post_start, $nonce, $_SESSION;
	
	if (! preg_match( '/"mac-addr"[^>]*>\s*([^<\s]{1,64})/', $raw_post_start, $m )) {
		gs_log( GS_LOG_NOTICE, "Siemens prov.: Missing MAC addr / device identifier" );
		# don't explain this to the user
		_dls_response_cleanup( $nonce, 'Error. See log for details.' );
	}
	$device_id = trim(preg_replace('/[^0-9A-Z:.\-_+]/', '', strToUpper($m[1])));
	if (preg_match( '/^[0-9A-F]{2}[:\-][0-9A-F]{2}[:\-][0-9A-F]{2}[:\-][0-9A-F]{2}[:\-][0-9A-F]{2}[:\-][0-9A-F]{2}$/', $device_id )) {
		# it's a MAC address
		$mac = preg_replace('/[^0-9A-F]/', '', $device_id);
		if (strLen($mac) != 12) {
			gs_log( GS_LOG_NOTICE, "Siemens prov.: Invalid MAC address \"$mac\" (wrong length)" );
			# don't explain this to the user
			_dls_response_cleanup( $nonce, 'Error. See log for details.' );
		}
		if (! in_array(subStr($mac,0,6), array('0001E3', '001AE8'))) {
			gs_log( GS_LOG_NOTICE, "Siemens prov.: MAC address \"$mac\" is not a Siemens phone" );
			# don't explain this to the user
			_dls_response_cleanup( $nonce, 'Error. See log for details.' );
		}
		$ret_device_id = false;
	} elseif (preg_match( '/^[0-9A-Z:.\-_+]+$/', $device_id)) {
		# it's a client identifier (softphone)
		//$mac = 'FF'. subStr(md5($device_id), 0, 10);  ///FIXME
		$mac = 'S'. str_pad(strToUpper(subStr( base_convert(md5( $device_id ),16,36), 0,11)),11,'0');  ///FIXME
		// e.g. "S5AUZQAIT99K"
		$ret_device_id = true;
	} else {
		gs_log( GS_LOG_NOTICE, "Siemens prov.: Invalid MAC addr / device identifier" );
		# don't explain this to the user
		_dls_response_cleanup( $nonce, 'Error. See log for details.' );
	}
	
	if (isSet($_SESSION) && is_array($_SESSION)) {
		if (! @array_key_exists('device_id', $_SESSION)) {
			$_SESSION['device_id'] = $device_id;
			$_SESSION['mac'] = $mac;
			$_SESSION['dev'] = ($ret_device_id ? $device_id : '');
		} else {
			if ($device_id != $_SESSION['device_id']) {
				gs_log( GS_LOG_NOTICE, "Siemens prov.: No session for MAC addr / device identifier \"$device_id\"" );
				_dls_response_cleanup( $nonce );
			}
		}
	}
	
	return array( 'mac'=>$mac, 'dev'=>($ret_device_id ? $device_id : '') );
}


function _deploy_file( $type, $ftp_path, $filename, $wan=null )
{
	global $nonce;
	global $requester;
	
	$type = strToUpper($type);
	
	$deploy_via_http = (gs_get_conf('GS_SIEMENS_PROV_PREFER_HTTP') && $type === 'RINGTONE');
	
	if (! $deploy_via_http) {  # FTP
		if ($wan === null) {
			include_once( GS_DIR .'inc/netmask.php' );
			$wan = ip_addr_in_network_list( $requester['phone_ip'], gs_get_conf('GS_PROV_LAN_NETS'));
		}
		gs_log( GS_LOG_DEBUG, 'Phone '.$requester['phone_ip'].' is in '.($wan?'WAN':'LAN') );
		
		$file_server = gs_get_conf(
			($wan ? 'GS_PROV_SIEMENS_FTP_SERVER_WAN' : 'GS_PROV_SIEMENS_FTP_SERVER_LAN'),
			gs_get_conf('GS_PROV_HOST'));
		
		if ($ftp_path == '' || $ftp_path === '/') $ftp_path = './';
		$external_ftp_path = gs_get_conf('GS_PROV_SIEMENS_FTP_PATH');
		if ($external_ftp_path !== null) {
			if (subStr($external_ftp_path,-1) !== '/')
				$external_ftp_path .= '/';
			$ftp_path = $external_ftp_path . $ftp_path;
		}
		unset($external_ftp_path);
	} else {  # HTTPS
		switch ($type) {
			case 'RINGTONE': $ftp_path = '/ringtones/';
		}
		$http_url = 'https://'. gs_get_conf('GS_PROV_HOST') .':18443'. $ftp_path . $filename;
	}
	
	if (! $deploy_via_http) {  # FTP
		include_once( GS_DIR .'inc/ftp-filesize.php' );
		$ftp = new GS_FTP_FileSize();
		if (!($ftp->connect($file_server, null,
			gs_get_conf('GS_PROV_SIEMENS_FTP_USER'),
			gs_get_conf('GS_PROV_SIEMENS_FTP_PWD')
		))) {
			gs_log( GS_LOG_DEBUG, 'Siemens prov.: Can\'t deploy '.$type.' file (FTP server failed)' );
			return false;
		} else {
			$filesize = $ftp->file_size( $ftp_path . $filename );
			$ftp->disconnect();
			if ($filesize < 0) {
				gs_log( GS_LOG_DEBUG, 'Siemens prov.: Can\'t deploy '.$type.' file ('. $ftp_path . $filename .' does not exist)' );
				return false;
			}
			elseif ($filesize < 1) {
				gs_log( GS_LOG_DEBUG, 'Siemens prov.: Can\'t deploy '.$type.' file ('. $ftp_path . $filename .' empty)' );
				return false;
			}
		}
	} else {  # HTTPS
		// check if file is reachable via HTTPS ...
	}
	
	@ob_end_clean();
	ob_start();
	_dls_message_open( $nonce );
	echo "\t", '<Action>', ($type==='APP' ?'Software':'File'),'Deployment</Action>',"\n";
	echo "\t", '<ItemList>',"\n";
	
	//echo '<Item name="file-action">'  , 'deploy' ,'</Item>',"\n";
	echo '<Item name="file-type">'    , _siemens_xml_esc($type) ,'</Item>',"\n";
	# 0=normal, 1=immediate:
	echo '<Item name="file-priority">', ($type==='APP'?'0':'1'), '</Item>',"\n";
	if (! $deploy_via_http) {  # FTP
		echo '<Item name="file-server">'  , _siemens_xml_esc($file_server) ,'</Item>',"\n";
		echo '<Item name="file-port">'    , '21' ,'</Item>',"\n";
		echo '<Item name="file-username">', _siemens_xml_esc(gs_get_conf('GS_PROV_SIEMENS_FTP_USER')) ,'</Item>',"\n";
		echo '<Item name="file-pwd">'     , _siemens_xml_esc(gs_get_conf('GS_PROV_SIEMENS_FTP_PWD')) ,'</Item>',"\n";
		echo '<Item name="file-path">'    , _siemens_xml_esc($ftp_path) ,'</Item>',"\n";
		echo '<Item name="file-name">'    , _siemens_xml_esc($filename) ,'</Item>',"\n";
	} else {  # HTTPS
		echo '<Item name="file-https-base-url">', _siemens_xml_esc($http_url) ,'</Item>',"\n";
	}
	
	echo "\t", '</ItemList>',"\n";
	_dls_message_close();
	$ob = ob_get_clean();
	
	if (! headers_sent()) {
		header( 'X-Powered-By: Gemeinschaft' );
		header( 'Content-Type: text/xml' );
		header( 'Content-Length: '. strLen($ob) );
	}
	echo $ob;
	@_write_raw_log("OUR RESPONSE:\n\n". _get_response_headers_as_string() . $ob);
	die();
}

/*
function _delete_file( $type, $filename )
{
	global $nonce;
	global $requester;
	
	$type = strToUpper($type);
	if ($type === 'APP') {
		gs_log( GS_LOG_WARNING, 'Siemens prov.: Can\'t delete firmware file' );
		return false;
	}
	
	@ob_end_clean();
	ob_start();
	_dls_message_open( $nonce );
	echo "\t", '<Action>', ($type==='APP' ?'Software':'File'),'Deployment</Action>',"\n";
	echo "\t", '<ItemList>',"\n";
	
	echo '<Item name="file-action">'  , 'delete' ,'</Item>',"\n";
	echo '<Item name="file-name">'    , _siemens_xml_esc($filename) ,'</Item>',"\n";
	echo '<Item name="file-type">'    , _siemens_xml_esc($type) ,'</Item>',"\n";
	# 0=normal, 1=immediate:
	echo '<Item name="file-priority">', ($type==='APP'?'0':'1'), '</Item>',"\n";
	
	echo "\t", '</ItemList>',"\n";
	_dls_message_close();
	$ob = ob_get_clean();
	
	if (! headers_sent()) {
		header( 'X-Powered-By: Gemeinschaft' );
		header( 'Content-Type: text/xml' );
		header( 'Content-Length: '. strLen($ob) );
	}
	echo $ob;
	@_write_raw_log("OUR RESPONSE:\n\n". _get_response_headers_as_string() . $ob);
	die();
}
*/


//if (preg_match( '/maxItems\s*=\s*"(-[0-9]+)"/', $raw_post_start, $m )) {
//	if ((int)$m[1] != -1)
if (preg_match( '/fragment\s*=\s*"([^"]*)"/', $raw_post_start, $m )) {
	if ($m[1] !== 'final')  # "next"
		gs_log( GS_LOG_WARNING, 'Siemens prov.: We don\'t support fragmented messages' );
	# our implementation is broken (according to the protocol specs)
	# but fixing this would mean a *lot* of changes here and there was
	# never a situation when a phone sent anything else but maxItems="-1"
	# resp. fragment="final"
}






if (preg_match( '/<ReasonForContact[^>]*>\s*(start-up|solicited|local-changes)/', $raw_post_start, $m )
) {
	
	$dev_arr = get_device_uuid();
	$mac = @$dev_arr['mac'];
	//unset($raw_post_start);
	unset($raw_post);
	
	$reason = $m[1];
	gs_log( GS_LOG_DEBUG, "Siemens prov.: Phone \"$mac\" contacts us ($reason)" );
	
	
	$DBM = gs_db_master_connect();
	if (! $DBM) {
		gs_log( GS_LOG_WARNING, "Could not connect to database" );
		_dls_response_cleanup( $nonce, 'Error. See log for details.' );
	}
	$db_device = (@$dev_arr['dev'] ? $DBM->escape($dev_arr['dev']) : '');
	$c = @$DBM->executeGetOne( 'SELECT COUNT(*) FROM `prov_siemens` WHERE `mac_addr`=\''. $mac .'\' AND `device`=\''. $db_device .'\'' );
	if ($c===false || $c===null) {
		$t = $DBM->executeGetOne( 'SHOW TABLES LIKE \'prov_siemens\'' );
		if ($t === 'prov_siemens') {
			gs_log( GS_LOG_FATAL, "Bad table \"prov_siemens\"?" );
			_dls_response_cleanup( $nonce, 'Error. See log for details.' );
		}
		gs_log( GS_LOG_NOTICE, "Table \"prov_siemens\" is missing" );
		$ok = @$DBM->execute(
'CREATE TABLE `prov_siemens` (
	`mac_addr` char(12) character set ascii NOT NULL,
	`device` varchar(64) character set ascii NOT NULL,
	`t_last_contact` int(10) unsigned NOT NULL default \'0\',
	`type` varchar(25) character set ascii NOT NULL,
	`sw_vers` varchar(15) character set ascii NOT NULL,
	`t_sw_deployed` int(10) unsigned NOT NULL default \'0\',
	`t_ldap_deployed` int(10) unsigned NOT NULL default \'0\',
	`t_logo_deployed` int(10) unsigned NOT NULL default \'0\',
	KEY `mac_addr` (`mac_addr`),
	KEY `sw_vers` (`sw_vers`(12)),
	KEY `t_last_contact` (`t_last_contact`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci'
		);
		if (! $ok) {
			gs_log( GS_LOG_FATAL, "Failed to create table \"prov_siemens\"" );
			_dls_response_cleanup( $nonce, 'Error. See log for details.' );
		}
		gs_log( GS_LOG_NOTICE, "Table \"prov_siemens\" created" );
		
		$c = @$DBM->executeGetOne( 'SELECT COUNT(*) FROM `prov_siemens` WHERE `mac_addr`=\''. $mac .'\' AND `device`=\''. $db_device .'\'' );
		if ($c===false || $c===null) {
			gs_log( GS_LOG_FATAL, "Bad table \"prov_siemens\"?" );
			_dls_response_cleanup( $nonce, 'Error. See log for details.' );
		}
	}
	if ($c < 1) {
		$DBM->execute( 'INSERT INTO `prov_siemens` (`mac_addr`, `device`, `t_last_contact`) VALUES (\''. $mac .'\', \''. (@$dev_arr['dev'] ? $DBM->escape($dev_arr['dev']) : '') .'\', '. time() .')' );
		gs_log( GS_LOG_DEBUG, "Phone \"$mac\" added to \"prov_siemens\"" );
	} else {
		@$DBM->execute( 'UPDATE `prov_siemens` SET `t_last_contact`='. time() .' WHERE `mac_addr`=\''. $mac .'\' AND `device`=\''. $db_device .'\' AND `t_last_contact`<'. (time()-25) );
	}
	
	
	
	# firmware deployment
	#
	$sw_deploy = false;
	if (gs_get_conf('GS_PROV_SIEMENS_SW_UPDATE')) {
		if (preg_match( '/"related-software-version"[^>]*>\s*([VR \d.]+)/i', $raw_post_start, $m)) {
			$sw_vers = _siemens_normalize_version( $m[1] );
		} else {
			$sw_vers = null;
		}
		if ($sw_vers != null) {
			$tmp = explode('.', $sw_vers);
			$sw_vers_v
				= 'V'.((int)lTrim(@$tmp[0],'0'))
				.' R'.((int)lTrim(@$tmp[1],'0'))
				. '.'.((int)lTrim(@$tmp[2],'0'))
				. '.'.((int)lTrim(@$tmp[3],'0'));
			# e.g. "01.00.12.002" => "V1 R0.12.2"
			//gs_log( GS_LOG_DEBUG, "sw_vers: $sw_vers  -  sw_vers_v: $sw_vers_v" );
			
			# store the current firmware version in the database:
			@$DBM->execute(
				'UPDATE `phones` SET '.
					'`firmware_cur`=\''. $DBM->escape($sw_vers) .'\' '.
				'WHERE `mac_addr`=\''. $DBM->escape($mac) .'\''
				);
			
			if (preg_match( '/"related-device-type"[^>]*>\s*OpenStage\s*(\d+)/i', $raw_post_start, $m)) {
				$openstage_model = $m[1];
				
				$t_sw_deployed = (int)$DBM->executeGetOne( 'SELECT `t_sw_deployed` FROM `prov_siemens` WHERE `mac_addr`=\''. $mac .'\' AND `device`=\''. $db_device .'\'' );
				if ($t_sw_deployed > time()-(60*15)) {
					gs_log( GS_LOG_NOTICE, "Firmware was deployed to \"$mac\" less than 15 minutes ago. Don't even check now." );
				} else {
					
					/*
					# check if we have a newer version
					$fws = @glob( dirName(__FILE__) .'/firmware/os'. $openstage_model .'/'.'*'.'/opera_bind.img' );
					if (is_array($fws)) {
						rSort($fws);
						if (array_key_exists(0, $fws)) {
							$sw_vers_new = baseName(dirName( $fws[0] ));
							if (preg_match('/^(\d{2})\.(\d{2})\.(\d{2})\.(\d{3})$/', $sw_vers_new, $m)) {
								if ($sw_vers_new > $sw_vers) {
									$sw_vers_new_v =
										'V' . ((string)(int)ltrim($m[1],'0')) .
										' R'. ((string)(int)ltrim($m[2],'0')) .
										'.' . ((string)(int)ltrim($m[3],'0')) .
										'.' . ((string)(int)ltrim($m[4],'0')) ;
									$sw_deploy = true;
									
									# safety check to prevent infinite firmware
									# deployment loops: make sure that the version
									# in the image file is higher than what the
									# phone has:
									$out=array(); $err=0;
									@exec('export LC_ALL=C; grep -a -o -E -i '
										. escapeShellArg( 'V[0-9]{1,2} R[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,4}' )
										.' '. escapeShellArg($fws[0]), $out, $err);
									if ($err == 1) {
										gs_log( GS_LOG_WARNING, "Siemens prov.: Bad firmware image \"os{$openstage_model}/{$sw_vers_new}/opera_bind.img\"" );
										$sw_deploy = false;
									} elseif ($err != 0) {
										gs_log( GS_LOG_WARNING, "Siemens prov.: Unknown error" );
										$sw_deploy = false;
									} else {
										$out = trim(implode(" ", $out));
										if (! preg_match('/V(\d{1,2})\s*R(\d{1,2})\.(\d{1,2})\.(\d{1,4})/i', $out, $m)) {
											gs_log( GS_LOG_WARNING, "Siemens prov.: Bad firmware image \"os{$openstage_model}/{$sw_vers_new}/opera_bind.img\" ($out)" );
											$sw_deploy = false;
										} else {
											$sw_vers_file =
												str_pad($m[1], 2, '0', STR_PAD_LEFT) .'.'.
												str_pad($m[2], 2, '0', STR_PAD_LEFT) .'.'.
												str_pad($m[3], 2, '0', STR_PAD_LEFT) .'.'.
												str_pad($m[4], 3, '0', STR_PAD_LEFT) ;
											$sw_vers_file_v =
												'V' . ((string)(int)ltrim($m[1],'0')) .
												' R'. ((string)(int)ltrim($m[2],'0')) .
												'.' . ((string)(int)ltrim($m[3],'0')) .
												'.' . ((string)(int)ltrim($m[4],'0')) ;
											$image_mismatch = false;
											if ($sw_vers_file != $sw_vers_new) {
												$image_mismatch = true;
												gs_log( GS_LOG_NOTICE, "Siemens prov.: Firmware image \"os{$openstage_model}/{$sw_vers_new}/opera_bind.img\" looks like \"$sw_vers_file\". Wrong directory name?" );
												if (gs_get_conf('GS_PROV_SIEMENS_SW_UPDATE_PRE')) {
													gs_log( GS_LOG_NOTICE, "Siemens prov.: Mismatching directory names might be ok if you deploy pre-release versions" );
												}
												sleep(1);
											}
											if ($sw_vers_file == $sw_vers) {
												gs_log( ($image_mismatch ? GS_LOG_NOTICE : GS_LOG_DEBUG), "Siemens prov.: Firmware image \"os{$openstage_model}/{$sw_vers_new}/opera_bind.img\" looks like \"$sw_vers_file_v\", phone already has that" );
												$sw_deploy = false;
											}
											elseif ($sw_vers_file < $sw_vers) {
												if (! gs_get_conf('GS_PROV_SIEMENS_SW_UPDATE_PRE')) {
													gs_log( ($image_mismatch ? GS_LOG_NOTICE : GS_LOG_DEBUG), "Siemens prov.: Firmware image \"os{$openstage_model}/{$sw_vers_new}/opera_bind.img\" looks like \"$sw_vers_file_v\", phone already has \"$sw_vers_v\"" );
													$sw_deploy = false;
												} else {
													gs_log( GS_LOG_NOTICE, "Siemens prov.: Firmware image \"os{$openstage_model}/{$sw_vers_new}/opera_bind.img\" looks like \"$sw_vers_file_v\" and phone already has \"$sw_vers_v\" but the config tells us to deploy" );
													sleep(1);
												}
											}
										}
									}
								}
							}
						}
					}
					*/
					
					$phone_id = (int)$DBM->executeGetOne(
						'SELECT `id` '.
						'FROM `phones` '.
						'WHERE `mac_addr`=\''. $DBM->escape($mac) .'\''
						);
					if (! $phone_id) $phone_id = 0;
					
					# do we have to upgrade to a default version?
					#
					$fw_was_upgraded_manually = (int)$DBM->executeGetOne(
						'SELECT `fw_manual_update`'.
						'FROM `phones` '.
						'WHERE `id`='. $phone_id
						);
					if ($fw_was_upgraded_manually) {
						gs_log( GS_LOG_DEBUG, "Firmware was upgraded \"manually\". Not scheduling an upgrade." );
					} elseif ($sw_vers === null) {
						gs_log( GS_LOG_DEBUG, "Phone did not report its current firmware version." );
					} else {
						$sw_default_vers = _siemens_normalize_version(trim(gs_get_conf('GS_SIEMENS_PROV_FW_DEFAULT_OS'.$openstage_model)));
						if (in_array($sw_default_vers, array(null,false,''), true)) {
							gs_log( GS_LOG_DEBUG, "No default firmware version set in config file" );
						} else {
							if ('x'.$sw_vers != 'x'.$sw_default_vers) {
								gs_log( GS_LOG_NOTICE, "The firmware version ($sw_vers) differs from the default version ($sw_default_vers), scheduling an upgrade ..." );
								# simply add a provisioning job to the database. this is done to be clean and we can trace the job.
								$ok = $DBM->execute(
									'INSERT INTO `prov_jobs` ('.
										'`id`, '.
										'`inserted`, '.
										'`running`, '.
										'`trigger`, '.
										'`phone_id`, '.
										'`type`, '.
										'`immediate`, '.
										'`minute`, '.
										'`hour`, '.
										'`day`, '.
										'`month`, '.
										'`dow`, '.
										'`data` '.
									') VALUES ('.
										'NULL, '.
										((int)time()) .', '.
										'0, '.
										'\'client\', '.
										((int)$phone_id) .', '.
										'\'firmware\', '.
										'0, '.
										'\'*\', '.
										'\'*\', '.
										'\'*\', '.
										'\'*\', '.
										'\'*\', '.
										'\'' . $DBM->escape($sw_default_vers) . '\' '.
									')'
								);
							}
						}
					}
					
					# check provisioning jobs
					#
					$rs = $DBM->execute(
						'SELECT `id`, `running`, `minute`, `hour`, `day`, `month`, `dow`, `data` '.
						'FROM `prov_jobs` '.
						'WHERE `phone_id`='.$phone_id.' AND `type`=\'firmware\' '.
						'ORDER BY `running` DESC, `inserted`' );
					
					if (! $rs) {
						gs_log( GS_LOG_WARNING, "DB error" );
					} else {
						
						include_once( GS_DIR .'inc/cron-rule.php' );
						while ($job = $rs->fetchRow()) {
							
							if ($job['running']) {
								gs_log( GS_LOG_NOTICE, "Phone $mac: A firmware job is already running" );
								break;
							}
							
							# check cron rule
							$c = new CronRule();
							$ok = $c->set_rule( $job['minute'] .' '. $job['hour'] .' '. $job['day'] .' '. $job['month'] .' '. $job['dow'] );
							if (! $ok) {
								gs_log( GS_LOG_WARNING, "Phone $mac: Job ".$job['id']." has a bad cron rule (". $c->err_msg ."). Deleting ..." );
								$DBM->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']).' AND `running`=0' );
								unset($c);
								continue;
							}
							if (! $c->validate_time()) {
								gs_log( GS_LOG_DEBUG, "Phone $mac: Job ".$job['id'].": Rule does not match" );
								unset($c);
								continue;
							}
							unset($c);
							gs_log( GS_LOG_DEBUG, "Phone $mac: Job ".$job['id'].": Rule matches" );
							
							$sw_vers_new = _siemens_normalize_version( $job['data'] );
							if ($sw_vers_new === null) {
								gs_log( GS_LOG_NOTICE, "Phone $mac: Bad new app vers.: ". $job['data'] );
								$DBM->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']).' AND `running`=0' );
								continue;
							}
							if ('x'.$sw_vers_new == 'x'.$sw_vers) {
								gs_log( GS_LOG_NOTICE, "Phone $mac: Firmware $sw_vers == $sw_vers_new" );
								$DBM->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']).' AND `running`=0' );
								continue;
							}
							$fw_file = dirName(__FILE__) .'/firmware/os'. $openstage_model .'/'.$sw_vers_new.'/opera_bind.img';
							if (! file_exists($fw_file)) {
								gs_log( GS_LOG_NOTICE, "Phone $mac: File \"$fw_file\" not found" );
								$DBM->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']).' AND `running`=0' );
								continue;
							}
							
							# safety check to prevent infinite firmware
							# deployment loops
							$out=array(); $err=0;
							@exec('export LC_ALL=C; grep -a -o -E -i '
								. escapeShellArg( 'V[0-9]{1,2} R[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,4}' )
								.' '. escapeShellArg($fw_file), $out, $err);
							if ($err == 1) {
								gs_log( GS_LOG_WARNING, "Siemens prov.: Bad firmware image \"os{$openstage_model}/{$sw_vers_new}/opera_bind.img\"" );
								$DBM->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']).' AND `running`=0' );
								continue;
							} elseif ($err != 0) {
								gs_log( GS_LOG_WARNING, "Siemens prov.: Unknown error" );
								$DBM->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']).' AND `running`=0' );
								continue;
							}
							$out = trim(implode(' ', $out));
							if (! preg_match('/V(\d{1,2})\s*R(\d{1,2})\.(\d{1,2})\.(\d{1,4})/i', $out, $m)) {
								gs_log( GS_LOG_WARNING, "Siemens prov.: Bad firmware image \"os{$openstage_model}/{$sw_vers_new}/opera_bind.img\" ($out)" );
								$DBM->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']).' AND `running`=0' );
								continue;
							}
							$sw_vers_file =
								str_pad($m[1], 2, '0', STR_PAD_LEFT) .'.'.
								str_pad($m[2], 2, '0', STR_PAD_LEFT) .'.'.
								str_pad($m[3], 2, '0', STR_PAD_LEFT) .'.'.
								str_pad($m[4], 3, '0', STR_PAD_LEFT) ;
							$sw_vers_file_v =
								'V' . ((string)(int)ltrim($m[1],'0')) .
								' R'. ((string)(int)ltrim($m[2],'0')) .
								'.' . ((string)(int)ltrim($m[3],'0')) .
								'.' . ((string)(int)ltrim($m[4],'0')) ;
							$image_mismatch = false;
							if ($sw_vers_file != $sw_vers_new) {
								$image_mismatch = true;
								gs_log( GS_LOG_NOTICE, "Siemens prov.: Firmware image \"os{$openstage_model}/{$sw_vers_new}/opera_bind.img\" looks like \"$sw_vers_file\". Wrong directory name?" );
								if (gs_get_conf('GS_PROV_SIEMENS_SW_UPDATE_PRE')) {
									gs_log( GS_LOG_NOTICE, "Siemens prov.: Mismatching directory names might be ok if you deploy pre-release versions" );
								}
							}
							if ($sw_vers_file == $sw_vers) {
								gs_log( ($image_mismatch ? GS_LOG_NOTICE : GS_LOG_DEBUG), "Siemens prov.: Firmware image \"os{$openstage_model}/{$sw_vers_new}/opera_bind.img\" looks like \"$sw_vers_file_v\", phone already has that" );
								$DBM->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']).' AND `running`=0' );
								continue;
							}
							if ($sw_vers_file < $sw_vers) {
								if (! gs_get_conf('GS_PROV_SIEMENS_SW_UPDATE_PRE')) {
									gs_log( ($image_mismatch ? GS_LOG_NOTICE : GS_LOG_DEBUG), "Siemens prov.: Firmware image \"os{$openstage_model}/{$sw_vers_new}/opera_bind.img\" looks like \"$sw_vers_file_v\", phone already has \"$sw_vers_v\"" );
									$DBM->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']).' AND `running`=0' );
									continue;
								} else {
									gs_log( GS_LOG_NOTICE, "Siemens prov.: Firmware image \"os{$openstage_model}/{$sw_vers_new}/opera_bind.img\" looks like \"$sw_vers_file_v\" and phone already has \"$sw_vers_v\" but the config tells us to deploy" );
								}
							}
							
							$sw_deploy = true;
							gs_log( GS_LOG_NOTICE, "Phone $mac: Upgrade app $sw_vers -> $sw_vers_new" );
							$DBM->execute( 'DELETE FROM `prov_jobs` WHERE `id`='.((int)$job['id']) );
							
							break;
						}
					}
					
				}
			}
		}
	}
	if ($sw_deploy) {
		gs_log( GS_LOG_NOTICE, "Siemens prov.: Firmware upgrade \"$sw_vers\" -> \"$sw_vers_new\")" );
		
		@$DBM->execute(
'UPDATE `prov_siemens` SET
	`t_sw_deployed`='. time() .',
	`type`=\''. $DBM->escape('os'. @$openstage_model) .'\'
WHERE `mac_addr`=\''. $mac .'\' AND `device`=\''. $db_device .'\''
		);
		
		_deploy_file( 'APP',
			'os'.$openstage_model .'/'. $sw_vers_new .'/', 'opera_bind.img' );
	}
	
	
	# normal response: request all params
	#
	header( 'X-Powered-By: Gemeinschaft' );
	header( 'Content-Type: text/xml' );
	ob_start();
	_dls_message_open( $nonce );
	echo "\t", '<Action>ReadAllItems</Action>',"\n";
	_dls_message_close();
	$ob = ob_get_clean();
	header( 'Content-Length: '. strLen($ob) );
	echo $ob;
	@_write_raw_log("OUR RESPONSE:\n\n". _get_response_headers_as_string() . $ob);
	die();
	
}
elseif (preg_match( '/<ReasonForContact\s+.{0,80}?action\s*=\s*"ReadAllItems"(?:\s+status\s*=\s*"([^"]*)")?/', $raw_post_start, $m )) {
	
	$dev_arr = get_device_uuid();
	$mac = @$dev_arr['mac'];
	unset($raw_post_start);
	$reply_to_status = array_key_exists(1, $m) ? $m[1] : 'accepted';
	gs_log( GS_LOG_DEBUG, "Phone $mac reports status for ReadAllItems: $reply_to_status" );
	if ($reply_to_status === 'failed') {
		gs_log( GS_LOG_NOTICE, "Siemens prov.: Phone $mac failed to send its settings" );
		_dls_response_cleanup( $nonce, '' );
	}
	elseif ($reply_to_status === 'busy') {
		gs_log( GS_LOG_NOTICE, "Siemens prov.: Phone $mac is too busy to send its settings" );
		# add a prov_job to make sure the phone will be triggered again
		$DBM = gs_db_master_connect();
		if (! $DBM) {
			gs_log( GS_LOG_WARNING, "Could not connect to database" );
			_dls_response_cleanup( $nonce, 'Error. See log for details.' );
		}
		$phone_id = (int)$DBM->executeGetOne('SELECT `id` FROM `phones` WHERE `mac_addr`=\''. $DBM->escape($mac) .'\'' );
		if ($phone_id > 0) {
			gs_log( GS_LOG_NOTICE, "Siemens prov.: Inserting a prov-job to make sure phone $mac is triggered again" );
			$DBM->execute(
				'INSERT INTO `prov_jobs` ('.
					'`id`, '.
					'`inserted`, '.
					'`running`, '.
					'`trigger`, '.
					'`phone_id`, '.
					'`type`, '.
					'`immediate`, '.
					'`minute`, '.
					'`hour`, '.
					'`day`, '.
					'`month`, '.
					'`dow` '.
				') VALUES ('.
					'NULL, '.
					((int)time()) .', '.
					'0, '.
					'\'server\', '.
					((int)$phone_id) .', '.
					'\'settings\', '.
					'1, '.
					'\'*\', '.
					'\'*\', '.
					'\'*\', '.
					'\'*\', '.
					'\'*\' '.
				')'
			);
		}
		_dls_response_cleanup( $nonce, 'Phone is busy' );
	}
	
	if (! preg_match_all( '/<Item\s+.{0,50}?name\s*=\s*"([a-z\d\-_*]+)"\s*(?:index\s*=\s*"(\d+)")?[^>]*>([^<]*)/i', $raw_post, $matches, PREG_SET_ORDER )) {
		gs_log( GS_LOG_NOTICE, "Siemens prov.: Invalid list of items" );
		# don't explain this to the user
		_dls_response_cleanup( $nonce, 'Error. See log for details.' );
	}
	unset($raw_post);
	$cur_cfg = array();
	foreach ($matches as $m) {
		/*
		if (! array_key_exists(2,$m) || $m[2]=='')
			$items[$m[1]] = $m[3];
		else
			$items[$m[1].'|'.$m[2]] = $m[3];
		*/
		if (! array_key_exists(2,$m) || $m[2]=='')
			$cur_cfg[$m[1]]        = $m[3];
		else
			$cur_cfg[$m[1]][$m[2]] = $m[3];
	}
	unset($matches);
	
	
	$phone_model = 'unknown';
	if (array_key_exists('related-device-type', $cur_cfg)) {  # e.g. "OpenStage 60"
		$tmp = $cur_cfg['related-device-type'];
		if (is_string($tmp)) {
			$tmp = strToLower(trim( $tmp ));
			if (preg_match('/^openstage\s*(\d+)/', $tmp, $m))
				$phone_model = 'os'.$m[1];
		}
		unset($tmp);
	}
	$phone_type = 'siemens-'.$phone_model;  # e.g. "siemens-os60"
	# to be used when auto-adding the phone
	
	$_SESSION['phone_model'] = $phone_model;
	
	
	/*
	if (preg_match( '/"software-version"[^>]*>\s*([^<]*)/i', $raw_post, $m ))
		$software_version = trim($m[1]);
	else
		$software_version = '';
	*/
	$sw_vers_v =
		(  array_key_exists('software-version', $cur_cfg)
		&& is_string($cur_cfg['software-version']) )
		? trim($cur_cfg['software-version']) : '';
	$sw_vers = _siemens_normalize_version( $sw_vers_v );
	if ($sw_vers == null) $sw_vers = '';
	
	
	$phone_color = '';
	$hw_compat = true;
	if (array_key_exists('part-number', $cur_cfg)) {
		# see http://wwww.wiki.siemens-enterprise.com/index.php/OpenStage_Hardware_revisions
		# or http://wiki.siemens-enterprise.com/wiki/OpenStage_Hardware_revisions
		$tmp = $cur_cfg['part-number'];
		if (is_string($tmp)) {
			$tmp = strToUpper(trim( $tmp ));
			if (preg_match('/^S30817-S740([0-9])-[A-Z]10([0-9])-([0-9]+)/', $tmp, $m)) {
				switch ($m[1]) {
					case '1':  # OpenStage 20
					case '2':  # OpenStage 40
					case '3':  # OpenStage 60
						switch ($m[2]) {
							case '1': $phone_color = 'ice'    ; break;
							case '3': $phone_color = 'lava'   ; break;
						}
						break;
					case '4':  # OpenStage 80
						switch ($m[2]) {
							case '1': $phone_color = 'silver' ; break;
							case '3': $phone_color = 'lava'   ; break;  # not available
						}
						break;
				}
				$m[3] = (int)lTrim($m[3],'0');
				switch ($m[1]) {
					case '1':  # OpenStage 20
						switch ($m[2]) {
							case '1': if ($m[3]<14) $hw_compat = false; break;
							case '3': if ($m[3]< 2) $hw_compat = false; break;
						}
						break;
					case '2':  # OpenStage 40
						switch ($m[2]) {
							case '1': if ($m[3]<20) $hw_compat = false; break;
							case '3': if ($m[3]< 2) $hw_compat = false; break;
						}
						break;
					case '3':  # OpenStage 60
						switch ($m[2]) {
							case '1': if ($m[3]<15) $hw_compat = false; break;
							case '3': if ($m[3]< 1) $hw_compat = false; break;
						}
						break;
					case '4':  # OpenStage 80
						switch ($m[2]) {
							case '1': if ($m[3]<10) $hw_compat = false; break;
							case '3': if ($m[3]< 0) $hw_compat = false; break;
						}
						break;
				}
			}
		}
		unset($tmp);
		unset($m);
	}
	
	
	gs_log( GS_LOG_DEBUG, "Phone $mac is model $phone_model, firmware vers. $sw_vers, color: ". ($phone_color ? $phone_color : 'unknown') );
	if (! $hw_compat) {
		gs_log( GS_LOG_DEBUG, "Phone $mac has an old hardware revision" );
		# the page doesn't explain what the means
	}
	
	
	include_once( GS_DIR .'inc/nobody-extensions.php' );
	include_once( GS_DIR .'inc/gs-lib.php' );
	
	$DBM = gs_db_master_connect();
	if (! $DBM) {
		gs_log( GS_LOG_WARNING, "Could not connect to database" );
		_dls_response_cleanup( $nonce, 'Error. See log for details.' );
	}
	
	$db_device = (@$dev_arr['dev'] ? $DBM->escape($dev_arr['dev']) : '');
	@$DBM->execute(
'UPDATE `prov_siemens` SET
	`sw_vers`=\''. $DBM->escape($sw_vers) .'\',
	`type`=\''. $DBM->escape($phone_model) .'\'
WHERE `mac_addr`=\''. $DBM->escape($mac) .'\' AND `device`=\''. $DBM->escape($db_device) .'\''
	);
	
	
	# do we know the phone?
	#
	$user_id = @gs_prov_user_id_by_mac_addr( $DBM, $mac );
	//gs_log( GS_LOG_DEBUG, "user_id at phone $mac is $user_id" );
	if ($user_id < 1) {
		if (! GS_PROV_AUTO_ADD_PHONE) {
			gs_log( GS_LOG_NOTICE, "New phone $mac not added to DB. Enable PROV_AUTO_ADD_PHONE" );
			_dls_response_cleanup( $nonce, 'Error. Unknown phone.' );
		}
		gs_log( GS_LOG_NOTICE, "Adding new Siemens phone $mac to DB" );
		
		$user_id = @gs_prov_add_phone_get_nobody_user_id( $DBM, $mac, $phone_type, $requester['phone_ip'] );
		if ($user_id < 1) {
			gs_log( GS_LOG_WARNING, "Failed to add nobody user for new phone $mac" );
			_dls_response_restart( $nonce, 'Failed to add nobody user for new phone.' );
		}
	}
	
	
	# is it a valid user id?
	#
	$num = (int)$DBM->executeGetOne( 'SELECT COUNT(*) FROM `users` WHERE `id`='. $user_id );
	if ($num < 1)
		$user_id = 0;
	
	if ($user_id < 1) {
		# something bad happened, nobody (not even a nobody user) is logged
		# in at that phone. assign the default nobody user of the phone:
		$user_id = @gs_prov_assign_default_nobody( $DBM, $mac, null );
		if ($user_id < 1) {
			gs_log( GS_LOG_WARNING, "Failed to assign nobody account to phone $mac" );
			_dls_response_cleanup( $nonce, 'DB error' );
		}
	}
	
	
	# get host for user
	#
	$host = @gs_prov_get_host_for_user_id( $DBM, $user_id );
	if (! $host) {
		_dls_response_cleanup( $nonce, 'Error. See log for details.' );
	}
	$pbx = $host;  # $host might be changed if SBC configured
	
	
	# who is logged in at that phone?
	#
	$user = @gs_prov_get_user_info( $DBM, $user_id );
	if (! is_array($user)) {
		_dls_response_cleanup( $nonce, 'DB error' );
	}
	
	
	# store the current firmware version in the database:
	#
	@$DBM->execute(
		'UPDATE `phones` SET '.
			'`firmware_cur`=\''. $DBM->escape($sw_vers) .'\' '.
		'WHERE `mac_addr`=\''. $DBM->escape($mac) .'\''
		);
	
	
	# store the user's current IP address in the database:
	#
	if (! @gs_prov_update_user_ip( $DBM, $user_id, $requester['phone_ip'] )) {
		gs_log( GS_LOG_WARNING, 'Failed to store current IP addr of user ID '. $user_id );
	}
	
	
	# get SIP proxy to be set as the phone's outbound proxy
	#
	$sip_proxy_and_sbc = gs_prov_get_wan_outbound_proxy( $DBM, $requester['phone_ip'], $user_id );
	if ($sip_proxy_and_sbc['sip_server_from_wan'] != '') {
		$host = $sip_proxy_and_sbc['sip_server_from_wan'];
	}
	
	
	# get extension without route prefix
	#
	if (gs_get_conf('GS_BOI_ENABLED')) {
		$hp_route_prefix = (string)$DBM->executeGetOne(
			'SELECT `value` FROM `host_params` '.
			'WHERE '.
				'`host_id`='. (int)$user['host_id'] .' AND '.
				'`param`=\'route_prefix\''
			);
		$user_ext = (subStr($user['name'],0,strLen($hp_route_prefix)) === $hp_route_prefix)
			? subStr($user['name'], strLen($hp_route_prefix)) : $user['name'];
		gs_log( GS_LOG_DEBUG, "Mapping ext. ". $user['name'] ." to $user_ext for provisioning - route_prefix: $hp_route_prefix, host id: ". $user['host_id'] );
	} else {
		$hp_route_prefix = '';
		$user_ext = $user['name'];
	}
	
	
	
	$new_cfg = array();
	
	
	########################################
	# Admin tags
	########################################
	
	# Network
	#
	_set_cfg('dhcp'               , null, 'true' );
	_set_cfg('hostname'           , null, 'os'.strToLower($mac) );
	_set_cfg('e164-hostname'      , null, 'false' );
	
	
	# Mobility
	#
	_set_cfg('mobility-enabled'   , null, ($use_mobility ? 'true':'false') );
	_set_cfg('mobility-password-on-logoff"', null, 'false' );
	
	
	# SIP Environment
	#
	_set_cfg('e164'               , null, $user_ext );
	_set_cfg('sip-user-id'        , null, $user_ext );
	_set_cfg('reg-id'             , null, $user_ext ); # ???
	_set_cfg('reg-number'         , null, $user_ext ); # ???
	_set_cfg('fully-qualified-phone-no', null, $user_ext ); # ???
	_set_cfg('sip-pwd'            , null, $user['secret'] );
	_set_cfg('sip-name'           , null, _siemens_xml_esc( mb_subStr( rTrim(preg_replace('/<[^>]*>/', '', $user['callerid'])), 0, 24)) );
	_set_cfg('register-by-name'   , null, 'false' );
	$tmp = $user_ext .' ';
	if ($user['firstname'] != '') $tmp .= mb_subStr($user['firstname'],0,1);
	if ($user['lastname' ] != '') $tmp .= mb_subStr($user['lastname' ],0,3);
	$tmp = _siemens_xml_esc( mb_subStr(rTrim($tmp), 0, 24) );
	_set_cfg('display-id'         , null, _siemens_xml_esc($tmp) ); # max. 24 chars
	_set_cfg('display-id-unicode' , null, _siemens_xml_esc($tmp) ); #  "
	_set_cfg('use-display-id'     , null, 'true' );
	
	_set_cfg('reg-addr'           , null, $host );
	_set_cfg('reg-port'           , null, '5060' );
	
	_set_cfg('registrar-addr'     , null, $host );
	_set_cfg('registrar-port'     , null, '5060' );
	
	_set_cfg('outbound-proxy'     , null, ($sip_proxy_and_sbc['sip_proxy_from_wan'] != '' ? 'true':'false') );
	_set_cfg('outbound-proxy-user', null, ($sip_proxy_and_sbc['sip_proxy_from_wan'] != '' ? 'true':'false') );
	
	_set_cfg('sgnl-gateway-addr'     , null, ($sip_proxy_and_sbc['sip_proxy_from_wan'] != '' ? $sip_proxy_and_sbc['sip_proxy_from_wan'] : $host) );
	_set_cfg('sgnl-gateway-addr-user', null, ($sip_proxy_and_sbc['sip_proxy_from_wan'] != '' ? $sip_proxy_and_sbc['sip_proxy_from_wan'] : $host) );
	_set_cfg('sgnl-gateway-port'     , null, '5060' );
	_set_cfg('sgnl-gateway-port-user', null, '5060' );
	_set_cfg('sgnl-route'            , null, '0' );
	
	//_set_cfg('mwi-e164'           , null, $host );
	//_set_cfg('mwi-e164'           , null, 'asterisk' );
	_set_cfg('mwi-e164'           , null, '' );
	# default 5004. must be even! (odd is for RTCP)
	_set_cfg('rtp-base-port'      , null, '5004' );
	_set_cfg('default-domain'     , null, '' );
	# 0=UDP, 1=TCP, 2=TLS:
	_set_cfg('sip-transport'      , null, '0' );
	_set_cfg('sip-transport-user' , null, '0' );
	# 0=other, 1=HiQ8000 (, 2=Broadsoft, 3=Sylantro ?):
	_set_cfg('server-type'        , null, '0' );
	_set_cfg('session-timer'      , null, 'true' );
	_set_cfg('session-duration'   , null, '3600' );
	_set_cfg('reg-ttl'            , null, '3600' );
	_set_cfg('realm'              , null, 'gemeinschaft.local' );
	_set_cfg('emergency-e164'     , null, '110' );
	_set_cfg('voice-mail-e164'    , null, 'voicemail' );
	//_set_cfg('system-description' , null, '' );
	//_set_cfg('system-name'        , null, '' );
	//_set_cfg('system-name-unicode', null, '' );
	
	
	# SIP Features
	#
	_set_cfg('auto-answer'           , null, 'false' );
	_set_cfg('beep-on-auto-answer'   , null, 'true' );
	_set_cfg('auto-reconnect'        , null, 'false' ); #???
	_set_cfg('beep-on-auto-reconnect', null, 'true' );
	_set_cfg('permit-decline-call'   , null, 'true' );
	_set_cfg('transfer-on-ring'      , null, 'false' );
	_set_cfg('join-allowed-in-conference', null, 'true' );
	# keys for pickup groups:  //FIXME?
	$rs = $DBM->execute( 'SELECT DISTINCT(`p`.`id`) `id` FROM `pickupgroups_users` `pu` JOIN `pickupgroups` `p` ON (`p`.`id`=`pu`.`group_id`) WHERE `pu`.`user_id`='. $user_id .' ORDER BY `p`.`id` LIMIT 1' );
	if ($r = $rs->fetchRow())
		_set_cfg('pickup-group-uri'      , null, '*8*'. str_pad($r['id'],5,'0', STR_PAD_LEFT), true );
	else
		_set_cfg('pickup-group-uri'      , null, '' );
	# 0=Off, 1=Hot, 2=Warm - ???:
	//_set_cfg('hot-line-warm-line'    , null, '' );
	_set_cfg('hot-line-warm-line-digits', null, '' );
	_set_cfg('initial-digit-timer'   , null, '30' );
	_set_cfg('conference-factory-uri', null, 'conf@'.$host );
	_set_cfg('callback-busy-allow'   , null, 'false' );
	_set_cfg('callback-busy-code'    , null, '' ); # only HiPath 8000
	_set_cfg('callback-ring-allow'   , null, 'false' );
	_set_cfg('callback-ring-code'    , null, '' ); # only HiPath 8000
	_set_cfg('callback-cancel-code'  , null, '' ); # only HiPath 8000
	_set_cfg('park-server'           , null, '' ); # park@server
	$call_waiting = (bool)(int)$DBM->executeGetOne( 'SELECT `active` FROM `callwaiting` WHERE `user_id`='. $user_id );
	_set_cfg('call-waiting-enabled'  , null, ($call_waiting ?'true':'false') );
	
	
	# Quality of Service
	#
	_set_cfg('qos-layer2'            , null, 'true' );
	_set_cfg('l2qos-voice'           , null, '5' ); # 0-7
	_set_cfg('l2qos-signalling'      , null, '3' ); # 0-7
	_set_cfg('l2qos-default'         , null, '0' );
	
	_set_cfg('qos-layer3'            , null, 'true' );
	_set_cfg('l3qos-voice'           , null, '46' ); # 0-63, 46=EF
	_set_cfg('l3qos-signalling'      , null, '26' ); # 0-63, 26=AF31
	
	# 0=Manual, 1=DHCP:
	_set_cfg('vlan-method'           , null, '1' );
	//_set_cfg('vlan-id'               , null, '' ); # 0-4095
	
	
	# File Transfer & Phone Download settings
	#
	//...
	
	
	# Time & Date
	#
	$daylight_savings_offset = 60;
	# docs say -12 to 12 which is wrong! it's in minutes:
	$tz_offset = round((int)date('Z')/60);
	$daylight_save = 'false';
	if ((int)date('I')==1) {
		$tz_offset -= $daylight_savings_offset;
		$daylight_save = 'true';
	}
	_set_cfg('sntp-tz-offset'        , null, $tz_offset ); # -720 - 720
	_set_cfg('daylight-save'         , null, $daylight_save );
	_set_cfg('daylight-save-minutes' , null, $daylight_savings_offset );
	//_set_cfg('time'                  , null, time().'000' );
	
	
	# SNMP traps
	#
	$snmp_traps_addr =
		(defined('GS_PROV_SIEMENS_SNMP_TRAP_ADDR') && constant('GS_PROV_SIEMENS_SNMP_TRAP_ADDR'))
		? constant('GS_PROV_SIEMENS_SNMP_TRAP_ADDR') : '';
	if ($snmp_traps_addr != '' && ! preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $snmp_traps_addr))
		$snmp_traps_addr = '';
	$snmp_traps_port =
		(defined('GS_PROV_SIEMENS_SNMP_TRAP_PORT') && constant('GS_PROV_SIEMENS_SNMP_TRAP_PORT')>0)
		? (int)constant('GS_PROV_SIEMENS_SNMP_TRAP_PORT') : 162;
	
	_set_cfg('snmp-trap-addr'          , null, $snmp_traps_addr );
	_set_cfg('snmp-trap-port'          , null, $snmp_traps_port );
	_set_cfg('snmp-trap-pwd'           , null, 'snmp' );
	_set_cfg('snmp-traps-active'       , null, 'false' );
	
	_set_cfg('diagnostic-trap-addr'    , null, $snmp_traps_addr );
	_set_cfg('diagnostic-trap-port'    , null, $snmp_traps_port );
	_set_cfg('diagnostic-trap-pwd'     , null, 'snmp' );
	_set_cfg('diagnostic-traps-active' , null, 'false' );
	_set_cfg('diagnostic-snmp-active'  , null, 'false' );
	
	_set_cfg('qdc-collection-unit-addr', null, $snmp_traps_addr );
	_set_cfg('qdc-collection-unit-port', null, '12010' );
	_set_cfg('qdc-trap-pwd'            , null, 'QOSDC' );
	_set_cfg('qdc-snmp-active'         , null, 'false' );
	_set_cfg('qdc-qcu-active'          , null, 'false' );
	
	
	# SNMP gets
	#
	_set_cfg('snmp-queries-allowed'  , null, 'false' );
	_set_cfg('snmp-pwd'              , null, '' );
	
	
	# Speech
	#
	_set_cfg('disable-microphone'    , null, 'false' );
	_set_cfg('loudspeech-enabled'    , null, 'true' );
	# just meaningful for G.711?:
	_set_cfg('audio-silence-suppression', null, 'false' );
	
	_set_cfg('codec-type'            , 1, '0'   , true ); # G.711
	# will be ulaw if country-iso=US and alaw for everything else
	_set_cfg('codec-allowed'         , 1, 'true', true );
	# 0=10ms, 1=20ms, 2=Automatic
	_set_cfg('codec-packet-size'     , 1, '2'   , true );
	_set_cfg('codec-rank'            , 1, '1'   , true );
	
	_set_cfg('codec-type'            , 2, '1'   , true ); # G.722
	_set_cfg('codec-allowed'         , 2, 'true', true );
	_set_cfg('codec-packet-size'     , 2, '2'   , true );
	_set_cfg('codec-rank'            , 2, '3'   , true );
	
	_set_cfg('codec-type'            , 3, '2'   , true ); # G.729
	_set_cfg('codec-allowed'         , 3, 'true', true );
	_set_cfg('codec-packet-size'     , 3, '2'   , true );
	_set_cfg('codec-rank'            , 3, '2'   , true );
	
	
	# LAN Ports
	#
	# LAN port:
	_set_cfg('port1'                   , null, '0' ); # 0=Automatic (speed)
	# PC port:
	_set_cfg('port2'                   , null, '0' ); # "
	# 0=Disabled, 1=Enabled, 2=Mirror:
	_set_cfg('port2-mode'               , null, '1' );
	# auto-detect crossover cables:
	_set_cfg('port2-auto-mdix-enabled' , null, 'true' );
	
	
	# Multiline operations
	#
	# 0=idle line, 1=primary line, 2=last line, 3=none:
	_set_cfg('originating-line-preference', null, '0' );
	 # 0=ringing line, 1=ringing primary, 2=incoming, 3=incoming primary, 4=none:
	_set_cfg('terminating-line-preference', null, '0' );
	# 0=Hold, 1=Release:
	_set_cfg('line-key-operating-mode'    , null, '0' );
	# 0=No ring, 1=Alert ring, 2=Default, 3=Alert Beep:
	_set_cfg('line-rollover-type'         , null, '3' );
	_set_cfg('line-rollover-volume'       , null, '2' ); # 1-5
	_set_cfg('line-registration-leds'     , null, 'true' );
	_set_cfg('keyset-use-focus'           , null, 'true' );
	_set_cfg('keyset-remote-forward-ind'  , null, 'true' );
	_set_cfg('keyset-reservation-timer'   , null, '60' ); # 0-300
	
	
	# Dial Plan & Dialling properties
	#
	_set_cfg('dial-plan-enabled'     , null, 'true' );
	
	# (they modify search results from LDAP for example:)
	_set_cfg('Canonical-dialing-international-prefix', null, '00'  ); # 00
	_set_cfg('Canonical-dialing-local-country-code'  , null, '49'  ); # 49
	_set_cfg('Canonical-dialing-national-prefix'     , null, '0'   ); # 0
	_set_cfg('Canonical-dialing-local-area-code'     , null, '251' ); # 251
	_set_cfg('Canonical-dialing-local-node'          , null, '702' ); # 702
	_set_cfg('Canonical-dialing-external-access'     , null, '0'   ); # ?
	_set_cfg('Canonical-dialing-operator-code'       , null, ''    );
	_set_cfg('Canonical-dialing-emergency-number'    , null, ''    );
	# 0=Not required, 1=For external numbers:
	_set_cfg('Canonical-dialing-dial-needs-access-code'  , null, '0' );
	# 0=Use national code, 1=Leave as +:
	_set_cfg('Canonical-dialing-dial-needs-intGWcode'    , null, '0' );
	# do not prefix numbers shorter than 10 characters with anything,
	# i.e. treat them as local branch numbers:
	_set_cfg('Canonical-dialing-min-local-number-length' , null, '10');
	_set_cfg('Canonical-dialing-extension-initial-digits', null, '' ); # ?
	# 0=Local enterprise form, 1=Always add node, 2=Use external numbers:
	_set_cfg('Canonical-dialing-dial-internal-form'  , null, '0' );
	# 0=Local public form, 1=National public form, 2=International form:
	_set_cfg('Canonical-dialing-dial-external-form'  , null, '0' );
	for ($i=1; $i<=50; ++$i)
		_set_cfg('Canonical-lookup-local-code'        , $i, '' );
	for ($i=1; $i<=50; ++$i)
		_set_cfg('Canonical-lookup-international-code', $i, '' );
	
	
	# Feature access
	#
	# 0=Context, 1=Hot keypad
	_set_cfg('hot-keypad-dialing'    , null, '0' );
	
	
	# Applications
	#
	//_set_cfg('ldap-server-address'   , null, '' );
	_set_cfg('ldap-transport'        , null, '0' );  # 0=TCP, 1=TLS
	_set_cfg('ldap-server-address'   , null, '' );  # '192.168.1.135'
	_set_cfg('ldap-server-port'      , null, '389' );
	# 0=Anonymous, 1=Simple, 2=Digest:
	_set_cfg('ldap-authentication'   , null, '1' );
	//_set_cfg('ldap-user'             , null, 'CN=root,DC=company,DC=de' );
	_set_cfg('ldap-user'             , null, '' );
	//_set_cfg('ldap-pwd'              , null, 'secret' );
	_set_cfg('ldap-pwd'              , null, '' );
	_set_cfg('ldap-max-responses'    , null, '25' );
	
	
	# Survivability
	#
	_set_cfg('backup-addr'           , null, '' );
	_set_cfg('backup-registration'   , null, 'false' );
	
	
	# QoS Data Collection
	#
	_set_cfg('qdc-qcu-active'        , null, 'false' );
	
	
	# Admin Password
	#
	_set_cfg('min-admin-passw-length', null, '6' ); # 6 - 24
	//_set_cfg('admin-pwd'             , null, '123456', true ); # write-only
	_set_cfg('admin-pwd'             , null, '000000', true ); # write-only
	//_set_cfg('admin-pwd-unicode'     , null, '000000', true ); # write-only
	# must be exactly 6 digits. "000000" means no password. default "123456"
	
	
	# Locked Config Menus
	#
	# locked-config-menus[1-66] == false
	_set_cfg('default-locked-config-menus', null, 'true' ); # "unknown item"
	for ($i=1; $i<=66; ++$i) {
		_set_cfg('locked-config-menus'   , $i, 'true' );
	}
	
	# locked-local-function-menus[1-10] == false
	_set_cfg('default-locked-local-function-menus', null, 'true' ); # "unknown item"
	for ($i=1; $i<=10; ++$i) {
		_set_cfg('locked-local-function-menus'   , $i, 'true' );
		# 2 = user password
	}
	
	
	# misc / unknown
	#
	//_set_cfg('dls-contact-interval'  , null, '65' );  # "contact gap", default: 300 [s]
	_set_cfg('dls-mode-secure'       , null, '0' );
	_set_cfg('dls-chunk-size'        , null, '5492' );  # default: 5492
	_set_cfg('default-passw-policy'  , null, 'false' );
	_set_cfg('deflect-destination'   , null, '' );
	# Skin: 0=Crystal Sea, 1=Warm Grey
	_set_cfg('display-skin'          , null, ($user['nobody_index'] < 1 ? '0':'1') );
	_set_cfg('enable-bluetooth-interface', null, 'true' );
	_set_cfg('usb-access-enabled'    , null, 'false' );
	_set_cfg('usb-backup-enabled'    , null, 'false' );
	_set_cfg('line-button-mode'      , null, '0' );
	_set_cfg('lock-forwarding'       , null, '' );
	_set_cfg('loudspeaker-function-mode', null, '0' );
	_set_cfg('max-pin-retries'       , null, '' );
	_set_cfg('inactivity-timeout'    , null, '30' );
	_set_cfg('not-used-timeout'      , null, '2' );
	_set_cfg('passw-char-set'        , null, '0' );
	_set_cfg('refuse-call'           , null, 'true' );
	_set_cfg('restart-password'      , null, '' );
	_set_cfg('time-format'           , null, '0' ); # 0=24 h, 1=12 h
	_set_cfg('uaCSTA-enabled'        , null, 'false' );
	_set_cfg('enable-test-interface' , null, 'false' );
	_set_cfg('enable-WBM'            , null, 'true' );
	_set_cfg('pixelsaver-timeout'    , null, '2' ); # 2 hours?
	_set_cfg('voice-message-dial-tone', null, '' );
	_set_cfg('call-pickup-allowed'   , null, 'true' );
	_set_cfg('group-pickup-tone-allowed', null, 'true' );
	_set_cfg('group-pickup-as-ringer'   , null, 'false' );
	_set_cfg('group-pickup-alert-type'  , null, '0' );
	_set_cfg('default-profile'       , null, '' );
	_set_cfg('count-medium-priority' , null, '5' );
	_set_cfg('timer-medium-priority' , null, '60' );  # 1 - 999
	_set_cfg('timer-high-priority'   , null, '5' );  # 0 - 999
	_set_cfg('dss-sip-detect-timer'  , null, '10' );
	_set_cfg('dss-sip-deflect'       , null, 'false' );
	_set_cfg('dss-sip-refuse'        , null, 'false' );
	_set_cfg('feature-availability'  , 22, 'false' );
	_set_cfg('feature-availability'  , 23, 'true' );
	_set_cfg('feature-availability'  , 26, 'true' );
	_set_cfg('local-control-feature-availability', null, 'false' );
	//_set_cfg('server-based-features'  , null, 'false' ); # "not implemented"
	//_set_cfg('server-based-forwarding', null, 'false' ); # "not implemented"
	//_set_cfg('server-based-dnd'       , null, 'false' ); # "not implemented"
	//_set_cfg('phone-port'            , null, '5060' );
	for ($i=0; $i<=41; ++$i) {
		_set_cfg('trace-level'       , $i, '0' ); # Off
	}
	
	
	if (subStr($phone_model,0,2)==='os')
		$openstage_type = (int)subStr($phone_model,2);
	else
		$openstage_type = 0;  # unknown
	
	
	########################################
	# Keys
	########################################
	
	_set_cfg('default-locked-function-keys', null, 'true' ); # "unknown item"
	
	_set_cfg('blf-code', null, '*81*' );  # pickup prefix for softkey function 59 (BLF)
	
	if ($openstage_type > 0) {
		
		# valid keys
		#
		if ($openstage_type >= 40) {
			for ($i=   1; $i<=   6; ++$i) $keys[] = $i; # normal keys
			if ($openstage_type >= 60) {
				$keys[] =    7;
				$keys[] =    8;
				if ($openstage_type >= 80) {
					$keys[] =    9;
				}
			}
			for ($i=1001; $i<=1006; ++$i) $keys[] = $i; # normal keys (shifted)
			if ($openstage_type >= 60) {
				$keys[] = 1007;
				$keys[] = 1008;
				if ($openstage_type >= 80) {
					$keys[] = 1009;
				}
			}
		}
		for ($i= 301; $i<= 312; ++$i) $keys[] = $i; # keys on 1st module
		for ($i=1301; $i<=1312; ++$i) $keys[] = $i; # keys on 1st module (shifted)
		for ($i= 401; $i<= 412; ++$i) $keys[] = $i; # keys on 2nd module
		for ($i=1401; $i<=1412; ++$i) $keys[] = $i; # keys on 2nd module (shifted)
		
		# lock all keys
		#
		foreach ($keys as $i) {
			_set_cfg('locked-function-keys', $i, 'true' );
		}
		
		# reset keys which the phone reported as being defined
		#
		if (array_key_exists('function-key-def', $cur_cfg)
		&& is_array($cur_cfg['function-key-def'])) {
			foreach($cur_cfg['function-key-def'] as $i => $val) {
				_set_cfg('function-key-def'    , $i, '0' ); # 0 = clear
				_set_cfg('key-label'           , $i, '' );
				_set_cfg('key-label-unicode'   , $i, '' );
				//_set_cfg('locked-function-keys', $i, 'true' );
			}
		}
		
		/*
		# shift key
		#
		_set_cfg('function-key-def'    , 1, '18', true);  # 18 = shift
		_set_cfg('key-label'           , 1, "Shift" );
		_set_cfg('key-label-unicode'   , 1, "Shift", true );
		_set_cfg('locked-function-keys', 1, 'true', true );
		
		# DND key
		#
		//FIXME - DND-Key should toggle(?)
		_set_cfg('function-key-def'    , 2, '25', true);  # 25 = do not disturb
		_set_cfg('key-label'           , 2, "Nicht st\xC3\xB6ren" );
		_set_cfg('key-label-unicode'   , 2, "Nicht st\xC3\xB6ren", true );
		_set_cfg('locked-function-keys', 2, 'true', true );
		*/
		
		/*
		# keys for pickup groups
		#
		$rs = $DBM->execute( 'SELECT DISTINCT(`p`.`id`) `id` FROM `pickupgroups_users` `pu` JOIN `pickupgroups` `p` ON (`p`.`id`=`pu`.`group_id`) WHERE `pu`.`user_id`='. $user_id .' ORDER BY `p`.`id` LIMIT 1' );
		$key = 3;
		$i=0;
		while ($r = $rs->fetchRow()) {
			$label = 'ggPickUp';
			if ($rs->numRows() > 1) $label .= ' '.(++$i);
			//_set_cfg('function-key-def'    , $key, '1', true);  # 1 = selected dialling
			_set_cfg('function-key-def'    , $key, '29', true);  # 29 = group pickup
			_set_cfg('key-label'           , $key, $label );
			_set_cfg('key-label-unicode'   , $key, $label, true );
			_set_cfg('locked-function-keys', $key, 'true', true );
			//_set_cfg('select-dial'         , $key, '*8'. str_pad($r['id'],5,'0', STR_PAD_LEFT), true );
			++$key;
		}
		*/
		
		/*
		_set_cfg('function-key-def'    , 3, '29', true);  # 29 = group pickup
		_set_cfg('key-label'           , 3, "PickUp" );
		_set_cfg('key-label-unicode'   , 3, "PickUp", true );
		_set_cfg('locked-function-keys', 3, 'true', true );
		# see pickup-group-uri
		*/
		
		/*
		_set_cfg('function-key-def'    , 4, '31', true);  # 31 = line
		_set_cfg('key-label'           , 4, "Leitung" );
		_set_cfg('key-label-unicode'   , 4, "Leitung", true );
		_set_cfg('locked-function-keys', 4, 'true', true );
		_set_cfg('line-short-desc'     , 4, "Leitung" );
		_set_cfg('line-short-desc-unicode', 4, "Leitung", true );
		_set_cfg('line-primary'        , 4, 'true', true );
		_set_cfg('line-hunt-sequence'  , 4, '0', true );
		_set_cfg('line-ring-delay'     , 4, '0', true );
		_set_cfg('line-hot-line-warm-line', 4, '0', true );
		_set_cfg('line-hld'            , 4, '' );
		_set_cfg('line-hld-active'     , 4, 'false', true );
		_set_cfg('line-sip-uri'        , 4, $user_ext, true );
		_set_cfg('line-sip-realm'      , 4, 'gemeinschaft.local', true );
		_set_cfg('line-sip-user-id'    , 4, $user_ext, true );
		_set_cfg('line-sip-pwd'        , 4, $user['secret'], true );
		# 0=shared, 1=private, 2=unknown ?:
		_set_cfg('line-shared-type'    , 4, '1', true );
		_set_cfg('line-hidden'         , 4, 'true', true );
		_set_cfg('line-int-allow'      , 4, 'true', true );
		_set_cfg('line-mlo-pos'        , 4, '0', true );
		*/
		
		/*
		_set_cfg('function-key-def'    , 7, '1', true);  # 1 = selected dialling
		_set_cfg('key-label'           , 7, "Pickup 3" );
		_set_cfg('key-label-unicode'   , 7, "Pickup 3", true );
		_set_cfg('locked-function-keys', 7, 'true', true );
		_set_cfg('select-dial'         , 7, '*800003', true );
		*/
		
		/*
		_set_cfg('function-key-def'    , 8, '1', true);  # 1 = selected dialling
		_set_cfg('key-label'           , 8, "Dial 555" );
		_set_cfg('key-label-unicode'   , 8, "Dial 555", true );
		_set_cfg('locked-function-keys', 8, 'true', true );
		_set_cfg('select-dial'         , 8, '555', true );
		*/
		
		$softkeys = null;
		$GS_Softkeys = gs_get_key_prov_obj( $phone_type );
		if ($GS_Softkeys->set_user( $user['user'] )) {
			if ($GS_Softkeys->retrieve_keys( $phone_type, array(
				'{GS_PROV_HOST}'      => gs_get_conf('GS_PROV_HOST'),
				'{GS_P_PBX}'          => $pbx,
				'{GS_P_EXTEN}'        => $user_ext,
				'{GS_P_ROUTE_PREFIX}' => $hp_route_prefix,
				'{GS_P_USER}'         => $user['user']
			) )) {
				$softkeys = $GS_Softkeys->get_keys();
			}
		}
		if (! is_array($softkeys)) {
			gs_log( GS_LOG_WARNING, 'Failed to get softkeys' );
		} else {
			foreach ($softkeys as $key_name => $key_defs) {
				if (array_key_exists('slf', $key_defs)) {
					$key_def = $key_defs['slf'];
				} elseif (array_key_exists('inh', $key_defs)) {
					$key_def = $key_defs['inh'];
				} else {
					continue;
				}
				$key_idx = (int)lTrim(subStr($key_name,1),'0');
				_set_cfg('function-key-def'    , $key_idx, subStr($key_def['function'],1), true );
				_set_cfg('key-label'           , $key_idx, _siemens_xml_esc($key_def['label']) );
				_set_cfg('key-label-unicode'   , $key_idx, _siemens_xml_esc($key_def['label']), true );
				_set_cfg('locked-function-keys', $key_idx, 'true', true );
				switch ($key_def['function']) {
					case 'f1' :  # selected dialing
						_set_cfg('select-dial'         , $key_idx, _siemens_xml_esc($key_def['data']), true );
						break;
					case 'f14':  # deflect
						_set_cfg('key-deflect-destination', $key_idx, _siemens_xml_esc($key_def['data']), true );
						break;
					case 'f30':  # repertory dial
						_set_cfg('repertory-dial'         , $key_idx, _siemens_xml_esc($key_def['data']), true );
						break;
					case 'f58':  # stimulus feature toggle
						$parts = explode('|',$key_def['data']);
						if (count($parts) < 2) $parts[1] = $parts[0];
						if (count($parts) < 3) $parts[2] = $parts[0];
						_set_cfg('stimulus-feature-code'   , $key_idx, _siemens_xml_esc($parts[0]), true );
						_set_cfg('stimulus-led-control-uri', $key_idx, _siemens_xml_esc($parts[1]), true );
						_set_cfg('stimulus-DTMF-sequence'  , $key_idx, _siemens_xml_esc(preg_replace('/[^0-9#*a-d]/i', '', $parts[2])), true );
						break;
					case 'f59':  # extension, BLF
						# (influenced by blf-code setting)
						# The value can optionally have a "|<flags>" attachment.
						# Possible flags are a (= audible), p (= popup).
						# If not specified the default is "|ap" (audible and popup).
						$parts = explode('|',$key_def['data']);
						if (count($parts) < 2) $parts[1] = 'ap';
						//_set_cfg('stimulus-led-control-uri', $key_idx, _siemens_xml_esc('sip:'. $parts[0] .'@'.$host), true );
						_set_cfg('stimulus-led-control-uri', $key_idx, _siemens_xml_esc($parts[0]),  true );
						_set_cfg('blf-audible'             , $key_idx, (strPos($parts[1],'a')===false ? 'false':'true'), true );
						_set_cfg('blf-popup'               , $key_idx, (strPos($parts[1],'p')===false ? 'false':'true'), true );
						break;
					case 'f60':  # invoke pre-configured (stored) XML application
						_set_cfg('FPK-app-name'            , $key_idx, _siemens_xml_esc($key_def['data']), true );
						break;
				}
			}
		}
		
	}
	
	
	########################################
	# XML Applications
	########################################

	if ($openstage_type >= 40) {

		/*gs_log(GS_LOG_DEBUG, 'Dumping Config Array...');
		ob_start();
		print_r($cur_cfg);
		$var = ob_get_contents();
		ob_end_clean();
		$fp=fopen('/tmp/gs_var_dump.'.$user_ext ,'w');
		fputs($fp,$var);
		fclose($fp);*/

		$isforeign = $DBM->executeGetOne( 'SELECT `is_foreign` FROM `hosts` WHERE `id`='. (int)$user['host_id'] );
		$XMLAPPS = array();
		if ($isforeign) 
			$XMLAPPS = $GS_PROV_SIEMENS_BOI_XML_APPS;
		else
			$XMLAPPS = $GS_PROV_SIEMENS_XML_APPS;
		$idx = 0;

		if (array_key_exists('XML-app-name', $cur_cfg)) {
			//get all Apps that have to be deleted
			//get all Apps that have to be updated
			$current_apps =  $cur_cfg['XML-app-name'];
			foreach ($current_apps as $cur_app_name => $cur_app) {
				$bFound = false;
				//is this Application in the Current config?
				foreach ($XMLAPPS as $new_app_name => $new_app) {
					if ($cur_app == $new_app['XML-app-name']) {
						gs_log(GS_LOG_DEBUG, "XML-Application: " . $cur_app . " is currently deployed." );
						//FIXME: Check if something has changed
						/*$bChanged=false;
						foreach ($new_app as $param => $value) {
							if (array_key_exists( $param, $cur_cfg ) {
								
							} else {
								_set_cfg(
								$bChanged = true;
							}
							
						}
						//if something has changed, set appname and transport too:
						*/
						$bFound=true;
						break;
					}
				}
				if(!$bFound) {
					++$idx;
					//delete the Application
					gs_log(GS_LOG_DEBUG, "Deleting XML-Application: " . $cur_app );
					_set_cfg('XML-app-action', $idx, 'delete', true );
					_set_cfg('XML-app-name', $idx, $cur_app, true );
				}
			}
		}

		//loop all defined XML Applications
		foreach ($XMLAPPS as $appname => $app) {
			++$idx;
			gs_log( GS_LOG_DEBUG, "Deploying XML application: $appname" );
			_set_cfg('XML-app-transport'       , $idx, (strToLower(GS_PROV_SCHEME)==='https' ? '1' : '0'), true );
			_set_cfg('XML-app-action'          , $idx, 'update', true );
			
			# loop and write all settings in this application
			foreach ($app as $keyname => $key ) {
				if ($key == '{GS_PROV_HOST}')
					$key = GS_PROV_HOST;
				else if ($key == '{GS_PROV_PORT}')
					$key = GS_PROV_PORT;
				else if ($key == '{GS_P_PBX}')
					$key =  $pbx;
				else if ($key == '{GS_P_EXTEN}')
					$key =  $user_ext;
				else if ($key == '{GS_P_ROUTE_PREFIX}')
					$key =  $hp_route_prefix;
				else if ($key == '{GS_P_USER}')
					$key =  $user['user'];
				_set_cfg( $keyname, $idx, $key, true );
			}
		}


	}
	
	
	
	########################################
	# User Tags
	########################################
	
	# User Password
	_set_cfg('min-user-passw-length' , null, '6' ); # 6 - 24
	//_set_cfg('user-pwd'              , null, '123456' ); # write-only
	//_set_cfg('user-pwd-unicode'      , null, '123456' ); # write-only
	_set_cfg('user-pwd'              , null, '000000' ); # write-only
	//_set_cfg('user-pwd-unicode'      , null, '000000' ); # write-only
	# must be exactly 6 digits. "000000" means no password. default "000000"
	
	# Country & Language
	# G.711 codec will be ulaw for country-iso=US and alaw for
	# everything else:
	_set_cfg('country-iso'           , null, 'DE' );
	_set_cfg('language-iso'          , null, 'de' );
	_set_cfg('date-format'           , null, '0' ); # DD.MM.YYYY
	

	if ($openstage_type >= 40) {

	$t_logo_deployed = (int)$DBM->executeGetOne( 'SELECT `t_logo_deployed` FROM `prov_siemens` WHERE `mac_addr`=\''. $mac .'\' AND `device`=\''. $db_device .'\'' );
	if ($t_logo_deployed > time()-(60*10)) {
		gs_log( GS_LOG_NOTICE, "Logo/Screensaver was deployed to \"$mac\" less than 10 minutes ago. Don't even check now." );
	} else if ($t_logo_deployed < 11 && $t_logo_deployed > 0) {
		gs_log( GS_LOG_NOTICE, "Last logo/screensaver/ringtone deployment failed with error ".$t_logo_deployed." ! Will try it again next time, not now.");
	} else {
	
		########################################
		# Screensaver Images 
		########################################
		
		gs_log(GS_LOG_DEBUG, 'Checking for screensaver images ...');

		unset($screensaver_images); unset($screensaver_images_temp); unset($thisscreensaver_image);

		if (array_key_exists('phone_model', $_SESSION)
			&&  ! in_array($_SESSION['phone_model'], array('unknown',''), true)
			&&  $_SESSION['phone_model'] >= 'os60')
		{
			switch ($_SESSION['phone_model']) {
				case 'os40': $fileext = 'bmp'; break;
				default    : $fileext = 'png';
			}

			$screensaver_images_temp = @explode(',',gs_get_conf('GS_PROV_SIEMENS_WALLPAPER'));
			$screensaver_images = Array();

			foreach($screensaver_images_temp as $thisscreensaver_image)
			{
				$screensaver_images[] = @sPrintF($thisscreensaver_image, $_SESSION['phone_model'], $fileext);
			}

			unset($fileext); unset($thisscreensaver_image); unset($screensaver_images_temp);
		}

		if (is_array( $screensaver_images ) && (count( $screensaver_images ) > 0) )
		{ # if a file was found
			$bDeployfile = true;
			# check if any files already exists on that phone
			if (array_key_exists('file-deployment-type', $cur_cfg)) {
				$image_file_idxs = array_keys($cur_cfg['file-deployment-type'], 'SCREENSAVER');
				gs_log(GS_LOG_DEBUG, 'Found '. count($image_file_idxs) .' screensaver images in phone memory.');
				foreach ($image_file_idxs as $file_idx) {
					# if the file has the same name, keep it and do not deploy again

					$image_idx = array_search($cur_cfg['file-deployment-name'][$file_idx],$screensaver_images);				
					if ($image_idx !== FALSE) {
						gs_log(GS_LOG_DEBUG, 'Image '. $screensaver_images[$image_idx] .' already deployed.');
						$bDeployfile = false;
					} else {  # if the file is not our file, delete it
						gs_log(GS_LOG_DEBUG, 'Deleting image '. $cur_cfg['file-deployment-name'][$file_idx] );
						_set_cfg('file-type'  , $file_idx, 'SCREENSAVER', true);
						_set_cfg('file-action', $file_idx, 'delete', true);
						_set_cfg('file-name'  , $file_idx, $cur_cfg['file-deployment-name'][$file_idx], true);
						//FIXME - _set_cfg() und _deploy_file() ?
						$bDeployfile = false;
					}
				}
			}
			if ($bDeployfile) {
				gs_log(GS_LOG_DEBUG, 'Deploying image file: '. $screensaver_images[0]);
				_deploy_file( 'SCREENSAVER', '', $screensaver_images[0] );
			}
		}

		unset( $screensaver_images );

		########################################
		# Background Logo Images 
		########################################
		
		gs_log(GS_LOG_DEBUG, 'Checking for background logo ...');

		unset($background_logo);

		if (array_key_exists('phone_model', $_SESSION)
			&&  ! in_array($_SESSION['phone_model'], array('unknown',''), true)
			&&  $_SESSION['phone_model'] >= 'os40')
		{
			switch ($_SESSION['phone_model']) {
				case 'os40': $fileext = 'bmp'; break;
				default    : $fileext = 'png';
			}

			$background_logo = @sPrintF(gs_get_conf('GS_PROV_SIEMENS_LOGO'), $_SESSION['phone_model'], $fileext);
		}

		if ( $background_logo )
		{ # if a file was found
			$bDeployfile = true;
			# check if any files already exists on that phone
			if (array_key_exists('file-deployment-type', $cur_cfg)) {
				$image_file_idxs = array_keys($cur_cfg['file-deployment-type'], 'LOGO');
				gs_log(GS_LOG_DEBUG, 'Found logo "'. $cur_cfg['file-deployment-name'][$file_idx]  .'" in phone memory.');
				foreach ($image_file_idxs as $file_idx) {
					# if the file has the same name, keep it and do not deploy again

					if ($cur_cfg['file-deployment-name'][$file_idx] === $background_logo) {
						gs_log(GS_LOG_DEBUG, 'Image "'. $background_logo .'" already deployed.');
						$bDeployfile = false;
					} else {  # if the file is not our file, delete it
						gs_log(GS_LOG_DEBUG, 'Deleting image '. $cur_cfg['file-deployment-name'][$file_idx] );
						_set_cfg('file-type'  , $file_idx, 'LOGO', true);
						_set_cfg('file-action', $file_idx, 'delete', true);
						_set_cfg('file-name'  , $file_idx, $cur_cfg['file-deployment-name'][$file_idx], true);
						//FIXME - _set_cfg() und _deploy_file() ?
					}
				}
			}
			if ($bDeployfile) {
				gs_log(GS_LOG_DEBUG, 'Deploying logo file: '. $background_logo);
				_deploy_file( 'LOGO', '', $background_logo);
			}
		}
		
		unset( $background_logo );

	}

	}

	########################################
	# Ringtones
	########################################
	
	if ($t_logo_deployed < 10 && $t_logo_deployed > 0) {
		gs_log( GS_LOG_NOTICE, "Last logo/screensaver/ringtone deployment failed with error ".$t_logo_deployed." ! Will set phone configuration first.");
		@$DBM->execute( 'UPDATE `prov_siemens` SET `t_logo_deployed`=0 WHERE `mac_addr`=\''. $mac .'\' AND `device`=\''. $db_device .'\'' );
	} else {	
		gs_log(GS_LOG_DEBUG, 'Checking for ringtone file ...');
		$rs_ringer = $DBM->execute( 'SELECT `bellcore`, `file` FROM `ringtones` WHERE `user_id`='. $user_id.' AND `src`=\'internal\'' );
		$ringer = $rs_ringer->getRow();
		if (! is_array($ringer)) $ringer = array('bellcore'=>1, 'file'=>null);
		
		if (in_array($ringer['file'], array(null,''), true)) {
			# if no custom ringtone file found set only the built-in ringer
			gs_log(GS_LOG_DEBUG, 'Setting ringtone melody '. $ringer['bellcore']);
			_set_cfg('ringer-melody'       , null, $ringer['bellcore'] );
			_set_cfg('ringer-audio-file'   , null, '' );
		}
		else { # if a file was found
			$bDeployfile = true;
			# check if any files already exists on that phone
			if (array_key_exists('file-deployment-type', $cur_cfg)) {
				$ringtone_file_idxs = array_keys($cur_cfg['file-deployment-type'], 'RINGTONE');
				gs_log(GS_LOG_DEBUG, 'Found '. count($ringtone_file_idxs) .' ringtones in phone memory.');
				foreach ($ringtone_file_idxs as $file_idx) {
					# if the file has the same name, keep it and do not deploy again
					//if ($cur_cfg['file-deployment-name'][$file_idx] === $ringer['file'].'-siemens.mp3') {
					if (subStr($cur_cfg['file-deployment-name'][$file_idx], 0, strLen($ringer['file'].'-')) === $ringer['file'].'-') {
						gs_log(GS_LOG_DEBUG, 'Ringtone '. $ringer['file'] .' already deployed.');
						$bDeployfile = false;
					} else {  # if the file is not our file, delete it
						gs_log(GS_LOG_DEBUG, 'Deleting ringtone '. $cur_cfg['file-deployment-name'][$file_idx] );
						_set_cfg('file-type'  , $file_idx, 'RINGTONE', true);
						_set_cfg('file-action', $file_idx, 'delete', true);
						_set_cfg('file-name'  , $file_idx, $cur_cfg['file-deployment-name'][$file_idx], true);
						 $bDeployfile = false;
						//FIXME - _set_cfg() und _deploy_file() ?
					}
				}
			}
			if ($bDeployfile) {
				gs_log(GS_LOG_DEBUG, 'Deploying ringtone file: '. $ringer['file']);
				_deploy_file( 'RINGTONE', '', $ringer['file'].'-siemens.mp3' );
			}
			# fallback:
			_set_cfg('ringer-melody'       , null, '2' );
			_set_cfg('ringer-tone-sequence', null, '2' );
			_set_cfg('ringer-audio-file'   , null, $ringer['file'].'-siemens.mp3' );
		}

	}

	########################################
	# Override provisioning parameters (group profile)
	########################################
	
	$prov_params = null;
	$GS_ProvParams = gs_get_prov_params_obj( $phone_type );
	if ($GS_ProvParams->set_user( $user['user'] )) {
		if ($GS_ProvParams->retrieve_params( $phone_type, array(
			'{GS_PROV_HOST}'      => gs_get_conf('GS_PROV_HOST'),
			'{GS_P_PBX}'          => $pbx,
			'{GS_P_EXTEN}'        => $user_ext,
			'{GS_P_ROUTE_PREFIX}' => $hp_route_prefix,
			'{GS_P_USER}'         => $user['user']
		) )) {
			$prov_params = $GS_ProvParams->get_params();
		}
	}
	if (! is_array($prov_params)) {
		gs_log( GS_LOG_WARNING, 'Failed to get provisioning parameters (group)' );
	} else {
		foreach ($prov_params as $param_name => $param_arr) {
		foreach ($param_arr as $param_index => $param_value) {
			if ($param_index == -1) {
				# not an array
				gs_log( GS_LOG_DEBUG, "Overriding group prov. param \"$param_name\": \"$param_value\"" );
				_set_cfg( _siemens_xml_esc($param_name), null        , _siemens_xml_esc($param_value), true );
			} else {
				# array
				gs_log( GS_LOG_DEBUG, "Overriding group prov. param \"$param_name\"[$param_index]: \"$param_value\"" );
				_set_cfg( _siemens_xml_esc($param_name), $param_index, _siemens_xml_esc($param_value), true );
			}
		}
		}
	}
	unset($prov_params);
	unset($GS_ProvParams);
	
	
	#####################################################################
	#  Override provisioning parameters (user profile)
	#####################################################################
	$prov_params = @gs_user_prov_params_get( $user['user'], $phone_type );
	if (! is_array($prov_params)) {
		gs_log( GS_LOG_WARNING, 'Failed to get provisioning parameters (user)' );
	} else {
		foreach ($prov_params as $p) {
			if ($p['index'] === null
			||  $p['index'] ==  -1) {
				# not an array
				gs_log( GS_LOG_DEBUG, 'Overriding user prov. param "'.$p['param'].'": "'.$p['value'].'"' );
				_set_cfg( _siemens_xml_esc($p['param']), null       , _siemens_xml_esc($p['value']), true );
			} else {
				# array
				gs_log( GS_LOG_DEBUG, 'Overriding user prov. param "'.$p['param'].'"['.$p['index'].']: "'.$p['value'].'"' );
				_set_cfg( _siemens_xml_esc($p['param']), $p['index'], _siemens_xml_esc($p['value']), true );
			}
		}
	}
	unset($prov_params);
	
	
	
	unset($cur_cfg);
	
	if (count($new_cfg)==0) {
		gs_log( GS_LOG_DEBUG, "Siemens prov.: No changes required for phone \"$mac\"" );
		_dls_response_cleanup( $nonce );
	}
	
	gs_log( GS_LOG_DEBUG, "Siemens prov.: Writing changed params to phone \"$mac\"" );
	
	header( 'X-Powered-By: Gemeinschaft' );
	header( 'Content-Type: text/xml' );
	ob_start();
	_dls_message_open( $nonce );
	echo "\t", '<Action>WriteItems</Action>',"\n",
	     "\t", '<ItemList>',"\n";
	foreach ($new_cfg as $key => $val) {
		if (! is_array($val)) {
			echo '<Item name="', $key, '">', $val, '</Item>',"\n";
		} else {
			foreach ($val as $index => $v) {
				echo '<Item name="', $key, '" index="', $index, '">', $v, '</Item>',"\n";
			}
		}
		//FIXME - XML escaping!
	}
	echo "\t", '</ItemList>',"\n";
	_dls_message_close();
	$ob = ob_get_clean();
	header( 'Content-Length: '. strLen($ob) );
	echo $ob;
	@_write_raw_log("OUR RESPONSE:\n\n". _get_response_headers_as_string() . $ob);
	die();
	
}
elseif (preg_match( '/<ReasonForContact\s+.{0,80}?action\s*=\s*"WriteItems"(?:\s+status\s*=\s*"([^"]*)")?/', $raw_post_start, $m )) {
	
	unset($raw_post_start);
	//unset($raw_post);
	$dev_arr = array( 'mac'=>@$_SESSION['mac'], 'dev'=>@$_SESSION['dev'] );
	$mac = @$dev_arr['mac'];
	$reply_to_status = array_key_exists(1, $m) ? $m[1] : 'accepted';
	gs_log( GS_LOG_DEBUG, "Phone $mac reports status for WriteItems: $reply_to_status" );
	if ($reply_to_status === 'failed') {
		gs_log( GS_LOG_NOTICE, "Siemens prov.: Phone $mac failed to store settings" );
		_dls_response_cleanup( $nonce, '' );
	}
	elseif ($reply_to_status === 'busy') {
		$DBM = gs_db_master_connect();
		if (! $DBM) {
			gs_log( GS_LOG_WARNING, "Could not connect to database" );
			_dls_response_cleanup( $nonce, 'Error. See log for details.' );
		}
		
		gs_log( GS_LOG_NOTICE, "Siemens prov.: Phone $mac is too busy to store settings" );
		# add a prov_job to make sure the phone is triggered again
		$phone_id = (int)$DBM->executeGetOne('SELECT `id` FROM `phones` WHERE `mac_addr`=\''. $DBM->escape($mac) .'\'' );
		if ($phone_id > 0) {
			gs_log( GS_LOG_NOTICE, "Siemens prov.: Inserting a prov-job to make sure phone $mac is triggered again" );
			$DBM->execute(
				'INSERT INTO `prov_jobs` ('.
					'`id`, '.
					'`inserted`, '.
					'`running`, '.
					'`trigger`, '.
					'`phone_id`, '.
					'`type`, '.
					'`immediate`, '.
					'`minute`, '.
					'`hour`, '.
					'`day`, '.
					'`month`, '.
					'`dow` '.
				') VALUES ('.
					'NULL, '.
					((int)time()) .', '.
					'0, '.
					'\'server\', '.
					((int)$phone_id) .', '.
					'\'settings\', '.
					'1, '.
					'\'*\', '.
					'\'*\', '.
					'\'*\', '.
					'\'*\', '.
					'\'*\' '.
				')'
			);
		}
		_dls_response_cleanup( $nonce, 'Phone is busy' );
	}
	
	if (preg_match_all( '/<Item\s+.{0,50}?name\s*=\s*"([a-z\d\-_*]+)"\s*(?:index\s*=\s*"(\d+)")?\s*status\s*=\s*"([^"]*)"/i', $raw_post, $matches, PREG_SET_ORDER )) {
		$failed_items = array();
		foreach ($matches as $m) {
			/*
			if (! array_key_exists(2,$m) || $m[2]=='')
				$items[$m[1]] = $m[3];
			else
				$items[$m[1].'|'.$m[2]] = $m[3];
			*/
			if (! array_key_exists(2,$m) || $m[2]=='')
				$failed_items[$m[1]]        = $m[3];
			else
				$failed_items[$m[1]][$m[2]] = $m[3];
		}
		unset($matches);
		foreach ($failed_items as $k => $v1) {
			if (! is_array($v1)) {
				gs_log( GS_LOG_DEBUG, 'Phone '.$mac.' reports "'.$v1.'" for param "'.$k.'"' );
			} else {
				foreach ($v1 as $idx => $v2) {
				gs_log( GS_LOG_DEBUG, 'Phone '.$mac.' reports "'.$v2.'" for param "'.$k.'"['.$idx.']' );
				}
			}
		}
		unset($failed_items);
	}
	unset($raw_post);
	
	$DBM = gs_db_master_connect();
	if (! $DBM) {
		gs_log( GS_LOG_WARNING, "Could not connect to database" );
		_dls_response_cleanup( $nonce, 'Error. See log for details.' );
	}
	$db_device = (@$dev_arr['dev'] ? $DBM->escape($dev_arr['dev']) : '');
	
	$rs = $DBM->execute( 'SELECT `t_ldap_deployed` `ldap`, `t_logo_deployed` `logo` FROM `prov_siemens` WHERE `mac_addr`=\''. $mac .'\' AND `device`=\''. $db_device .'\'' );
	$t_deployed = $rs ? $rs->fetchRow() : null;
	if ($t_deployed) {
		foreach ($t_deployed as $k => $v) {
			$t_deployed[$k] = (int)$v;
		}
	} else {
		$t_deployed = array( 'ldap'=>0, 'logo'=>0 );
	}
	
	# LDAP template
	#
	$file_base = 'ldap_template.txt';
	if (file_exists($file_base)
	&& ($t_deployed['ldap'] < time()-7*86400
	||  $t_deployed['ldap'] < @fileMTime( dirName(__FILE__).'/firmware/'.$file_base )
	)) {
		gs_log( GS_LOG_DEBUG, "Siemens prov.: LDAP template available for \"$mac\"" );
		_deploy_file( 'LDAP', './', $file_base );
	}
		
	unset($t_deployed);
	
	# no files available
	_dls_response_cleanup( $nonce );
	
}
elseif (preg_match( '/<ReasonForContact[^>]*>\s*status/', $raw_post_start )
) {
	
	$dev_arr = get_device_uuid();
	$mac = @$dev_arr['mac'];
	unset($raw_post_start);
	//unset($raw_post);
	gs_log( GS_LOG_DEBUG, "Phone $mac reports file deployment status" );
	
	if (preg_match( '/name\s*=\s*"file-deployment-type"[^>]*>\s*([A-Za-z]+)/', $raw_post, $m )) {
		$file_deployment_type = strToUpper($m[1]);
	} else {
		$file_deployment_type = '';
	}
	if (preg_match( '/name\s*=\s*"file-deployment-status"[^>]*>\s*([a-zA-Z0-9.,\-_]+)/', $raw_post, $m )) {
		$file_deployment_status = strToLower($m[1]);
		switch ($file_deployment_type) {
		case 'LDAP'       : $file_deployment_type_v = 'LDAP template'     ; break;
		case 'LOGO'       : $file_deployment_type_v = 'background image'  ; break;
		case 'RINGTONE'   : $file_deployment_type_v = 'ringtone'          ; break;
		case 'SCREENSAVER': $file_deployment_type_v = 'screensaver image' ; break;
		case 'PIC'        : $file_deployment_type_v = 'picture clip'      ; break;
		default           : $file_deployment_type_v = '"'.$file_deployment_type.'"';
		}
		if ($file_deployment_type_v !== false) {
			switch ($file_deployment_status) {
			case 'ok':
				gs_log( GS_LOG_DEBUG, "Siemens prov.: $file_deployment_type_v deployed to \"$mac\"" );
				break;
			case 'failed':
				gs_log( GS_LOG_WARNING, "Siemens prov.: Deployment of $file_deployment_type_v to \"$mac\" failed (FTP server down?)" );
				break;
			default:
				gs_log( GS_LOG_WARNING, "Siemens prov.: Deployment of $file_deployment_type_v to \"$mac\" failed ($file_deployment_status)" );
			}
		}
		if ($file_deployment_status === 'ok') {
			$db_device = (@$dev_arr['dev'] ? $DBM->escape($dev_arr['dev']) : '');
			switch ($file_deployment_type) {
			case 'LDAP': $col = 't_ldap_deployed'; break;
			case 'LOGO': $col = 't_logo_deployed'; break;
			case 'SCREENSAVER': $col = 't_logo_deployed'; break;
			default    : $col = false;
			}
			if ($col) {
				$DBM = gs_db_master_connect();
				if (! $DBM) {
					gs_log( GS_LOG_WARNING, "Could not connect to database" );
					_dls_response_cleanup( $nonce, 'Error. See log for details.' );
				}
				@$DBM->execute( 'UPDATE `prov_siemens` SET `'. $col .'`='. time() .' WHERE `mac_addr`=\''. $mac .'\' AND `device`=\''. $db_device .'\'' );
			}
		} else {
			if ($file_deployment_type === 'RINGTONE') {
				gs_log( GS_LOG_WARNING, "Siemens prov.: Deployment of ringtone for \"$mac\" failed " );
				$DBM = gs_db_master_connect();
				if (! $DBM) {
					gs_log( GS_LOG_WARNING, "Could not connect to database" );
					_dls_response_cleanup( $nonce, 'Error. See log for details.' );
				}
				@$DBM->execute( 'UPDATE `prov_siemens` SET `t_logo_deployed`=1 WHERE `mac_addr`=\''. $mac .'\' AND `device`=\''. $db_device .'\'' );
			}
		}
	}
	
	_dls_response_cleanup( $nonce );
	
}
elseif (preg_match( '/<ReasonForContact[^>]*>\s*inventory-changes/', $raw_post_start )
) {
	
	//$dev_arr = get_device_uuid();
	//$mac = @$dev_arr['mac'];
	unset($raw_post_start);
	unset($raw_post);
	gs_log( GS_LOG_DEBUG, "Phone reports inventory changes" );
	
	_dls_response_cleanup( $nonce );
	
}
elseif (preg_match( '/<ReasonForContact[^>]*>\s*clean-up/', $raw_post_start )
) {
	
	unset($raw_post_start);
	unset($raw_post);
	gs_log( GS_LOG_DEBUG, "Phone wants to end the communication" );
	
	_dls_response_cleanup( $nonce );
	
}
else {
	
	unset($raw_post_start);
	unset($raw_post);
	
	gs_log( GS_LOG_NOTICE, "Siemens prov.: Can't understand the message" );
	_dls_response_cleanup( $nonce, 'Unknown message' );
	
}


?>
