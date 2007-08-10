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

define( 'TIVOLI_VALID', true );  /// this is a parent file
include_once( dirName(__FILE__) .'/conf.php' );


$sessionName = 'TIVOLI';

session_name( $sessionName );
session_start();

//$_SESSION['isauth'] = false;

if (@$_REQUEST['_x_tivoli_action']=='login') {
	$tivoliUser = @$_REQUEST['_x_tivoli_username'];
	$tivoliPass = @$_REQUEST['_x_tivoli_password'];
	if (isSet($tivoliUsers[$tivoliUser]) && $tivoliUsers[$tivoliUser]==$tivoliPass) {
		$_SESSION['user'] = $tivoliUser;
		$_SESSION['isauth'] = true;
	}
}
if (! @$_SESSION['isauth']) {
	tivoli_login_screen();
}

switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		tivoli_http_request();
		break;
	case 'POST':
		tivoli_http_request();
		break;
	default:
		my_internal_server_error();
}





function tivoli_login_screen()
{
	header( 'Content-type: text/html; charset=utf-8' );
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html><head>
<title>Tivoli Login</title>
</head><body style="text-align:center;">
<h1 align="center">Tivoli Login</h1>
<br />
<div style="width:200px; margin:auto; padding:1em; background:#ddd; text-align:left;">
<form action="http://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] .'" method="post">
<input type="hidden" name="_x_tivoli_action" value="login" />
User:<br />
<input type="text" name="_x_tivoli_username" value="'. htmlSpecialChars( @$_REQUEST['_x_tivoli_username'] ) .'" size="20" maxlength="50" /><br />
<br />
Passwort:<br />
<input type="text" name="_x_tivoli_password" value="" size="20" maxlength="50" /><br />
<br />
<input type="submit" value="Einloggen" />
</form>
</div>
</body></html>
';
	die();
}


function tivoli_http_request()
{
	global $scheme, $host, $port, $timeout, $sessionName;
	
	/*
	echo "<pre>";
	print_r($_SERVER);
	print_r($_FILES);
	echo "</pre>";
	*/
	if (is_array($_FILES) && count($_FILES) > 0)
		$errout = 'File uploads cannot be handled by the WebSeal simulation script.';
	else
		$errout = '';
	
	$postVals = array();
	if ($_SERVER['REQUEST_METHOD']=='POST') {
		foreach ($_POST as $k => $v) {
			if (subStr($k,0,10) != '_x_tivoli_')
				$postVals[] = urlEncode($k) .'='. urlEncode($v);
		}
	}
	$postData = implode('&', $postVals);
	
	$headers = array();
	foreach ($_SERVER as $k => $v) {
		if (subStr($k,0,5)=='HTTP_') {
			$k = strToLower(str_replace('_','-',subStr($k,5)));
			$headers[$k] = $v;
		}
	}
	$headers['accept-encoding'] = 'identity';
	$headers['connection'] = 'close';
	$headers['host'] = $host .':'. $port;
	unset( $headers['keep-alive'] );
	$headers['iv-user'] = $_SESSION['user'];
	if ($_SERVER['REQUEST_METHOD']=='POST') {
		$headers['content-type'] = 'application/x-www-form-urlencoded';
		$headers['content-length'] = strLen($postData);
	}
	if (isSet( $headers['cookie'] )) {
		$headers['cookie'] = preg_replace( '/'. $sessionName .'=[^\s]*/', '', $headers['cookie'] );
	}
	if (isSet( $headers['referer'] )) {
		$headers['referer'] = str_replace(
			'http://'. $_SERVER['HTTP_HOST'] .'/',
			'http://'. $host .':'. $port .'/',
			$headers['referer'] );
		if ($port==80) {
			$headers['referer'] = str_replace( ':80', '', $headers['referer'] );
		}
	}
	
	$req = $_SERVER['REQUEST_METHOD'] .' '. $_SERVER['REQUEST_URI'] .' HTTP/1.0'."\r\n";
	foreach ($headers as $k => $v) {
		$req.= ucWords($k) .': '. $v ."\r\n";
	}
	$req.= "\r\n";
	if ($_SERVER['REQUEST_METHOD']=='POST')
		$req.= $postData;
	unset($headers);
	
	$sock = @fSockOpen( 'tcp://'.$host, $port, $err, $errMsg, $timeout );
	if (!$sock) {
		echo "Could not connect to host $host, port $port\n";
		return false;
	}
	fWrite($sock, $req, strLen($req));
	unset($req);
	
	$response = '';
	while (!fEof($sock)) {
		$response.= fRead($sock,10000);
	}
	my_http_handle_response( $response, $errout );
}


function my_http_handle_response( $response, $errout )
{
	$tmp = @explode("\r\n\r\n", $response, 2);
	if (count($tmp) != 2) {
		my_internal_server_error( $response );
	}
	
	if (! preg_match( '/^HTTP\/1\.\d\s+(\d{3})\s+([^\r\n]*)\r\n/', $response, $m ))
		my_internal_server_error( $response );
	$respStatusCode = $m[1];
	$respStatusMsg  = $m[2];
	$headersStr = subStr( $tmp[0], strLen($m[0]) );
	$body       = $tmp[1];
	unset($tmp);
	
	$headers = array();
	preg_match_all( '/^([a-zA-Z\-]+):\s*([^\r\n]*)/m', $headersStr, $m, PREG_SET_ORDER );
	foreach ($m as $ignore => $match) {
		$headers[strToLower($match[1])] = $match[2];
	}
	$headers['connection'] = 'close';
	unset( $headers['keep-alive'] );
	
	header( 'HTTP/1.0 '. $respStatusCode .' '. $respStatusMsg, true, $respStatusCode );
	foreach ($headers as $k => $v) {
		header( ucWords($k) .': '. $v );
	}
	
	echo my_modify_html( $body );
	if ($errout != '')
		echo '<h1>', $errout, '</h1>';
}


function my_modify_html( $html )
{
	global $host, $port;
	$html = str_replace(
		'http://'. $host .':'. $port .'/',
		'http://'. $_SERVER['HTTP_HOST'] .'/',
		$html );
	if ($port==80) {
		$html = str_replace(
		'http://'. $host .'/',
		'http://'. $_SERVER['HTTP_HOST'] .'/',
		$html );
	}
	return $html;
}


function my_internal_server_error( $response='' )
{
	if (! headers_sent()) {
		header( 'HTTP/1.0 500 Internal Server Error', true, 500 );
		header( 'Content-type: text/html' );
		header( 'Connection: close' );
		header( 'Transfer-encoding: none' );
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html><head>
<title>500 Internal Server Error</title>
</head><body>
<h1>Internal Server Error</h1>
';
		if ($response) {
			echo '<hr />
<br />
Server message (for debugging):
<pre style="background:#ddd; border:1px solid #aaa; padding:0.5em;">
', htmlSpecialChars($response), '
</pre>
';
		}
		echo '</body></html>
';
	}
	die();
}


?>