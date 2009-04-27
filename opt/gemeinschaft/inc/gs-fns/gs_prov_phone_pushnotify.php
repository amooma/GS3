<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* 
* Author: Henning Holtschneider <henning@loca.net> - LocaNet oHG
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

require_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/gs-lib.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_ip_by_ext.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_phonemodel_get.php' );

// Public
function gs_prov_phone_pushnotify ( $user_ext, $message )
{
	if (! preg_match( '/^\d+$/', $user_ext )) {
		gs_log( GS_LOG_WARNING, 'User must be numeric.' );
		return new GsError( 'User must be numeric.' );
	}
	
	$ip = gs_user_ip_by_ext( $user_ext );
	if (isGsError( $ip )) {
		gs_log( GS_LOG_WARNING, $user_ext . ': ' . $ip->getMsg() );
		return new GsError( $ip->getMsg() );
	}
	
	$phonemodel = gs_user_phonemodel_get( $user_ext );
	
	if ( preg_match( '/^polycom.+$/', $phonemodel ) )
		_gs_prov_phone_pushnotify_polycom( $ip, $message );
}

// Private
function _gs_prov_phone_pushnotify_polycom ( $ip, $message )
{
	$phone_user = gs_get_conf('GS_POLYCOM_PROV_HTTP_USER');
	$phone_pass = gs_get_conf('GS_POLYCOM_PROV_HTTP_PASS');
	$polycom_pushpath = "/push";

	if ( !$fp = fsockopen($ip, 80, $errno, $errstr, 15) )
		return false;

	// first do the non-authenticated header so that the server
	// sends back a 401 error containing its nonce and opaque

	$out  = "POST " . $polycom_pushpath . " HTTP/1.1\r\n";
	$out .= "Host: " . $ip . "\r\n";
	$out .= "Content-type: application/x-www-form-urlencoded\r\n";
	$out .= "Content-Length: " . strlen( $message ) . "\r\n";
	$out .= "Connection: Close\r\n\r\n";
	$out .= $message;

	fwrite( $fp, $out );

	// read the reply and look for the WWW-Authenticate element

	$response = "";
	while ( !feof($fp) )
		$response .= fgets( $fp, 512 );
	fclose( $fp );

	$authpos = strpos( $response, "WWW-Authenticate:" );

	if( $authpos !== false )
		$authresponse = trim( substr($response, $authpos + 18) );

	if ( strlen( $authresponse) > 0 ) {
		$authdata = trim( substr($authresponse, 0, strpos($authresponse, "\n")) );
		// split up the WWW-Authenticate string to find digest-realm,nonce and opaque values
		// if qop value is presented as a comma-seperated list (e.g auth,auth-int) then it won't
		// be retrieved correctly
		// but that doesn't matter because going to use 'auth' anyway
		$authdataarr = explode( ',', $authdata );
	}

	$autharr = array();
    
	foreach( $authdataarr as $el )
	{
		$elarr = explode( '=', $el );
		// the substr here is used to remove the double quotes from the values
		$autharr[@trim($elarr[0])] = @substr( $elarr[1], 1, strlen($elarr[1])-2 );
	}

	// these are all the vals required from the server
	$nonce = @$autharr["nonce"];
	$opaque = @$autharr["opaque"];
	$drealm = @$autharr["Digest realm"];

	// client nonce can be anything since this authentication session is not going to be persistent
	// likewise for the cookie - just call it MyCookie
	$cnonce = "push";
    
	// calculate the hashes of A1 and A2 as described in RFC 2617
	$a1 = $phone_user . ':' . $drealm . ':' . $phone_pass;
	$a2 = 'POST:' . $polycom_pushpath;
	$ha1 = md5( $a1 );
	$ha2 = md5( $a2 );

	// calculate the response hash as described in RFC 2617
	$concat = $ha1 . ':' . $nonce . ':00000001:' . $cnonce . ':auth:' . $ha2;
	$response = md5( $concat );

	// put together the Authorization Request Header
	$out  = "POST " . $polycom_pushpath . " HTTP/1.1\r\n";
	$out .= "Host: " . $ip . "\r\n";
	$out .= "Connection: Close\r\n";
	$out .= "Content-type: application/x-www-form-urlencoded\r\n";
	$out .= "Content-Length: " . strlen( $message ) . "\r\n";
	$out .= "Cookie: cookie=XMLPush\r\n";
	$out .= "Authorization: Digest username=\"" . $phone_user . "\", realm=\"" . $drealm . 
		"\", qop=\"auth\", algorithm=\"MD5\", uri=\"/push\", nonce=\"" . $nonce .
		"\", nc=00000001, cnonce=\"". $cnonce .
		"\", opaque=\"" . $opaque . "\", response=\"" . $response .
		"\"\r\n\r\n";

	$out .= $message;

	if ( !$fp = fsockopen($ip, 80, $errno, $errstr, 15) )
		return false;
    
	fwrite( $fp, $out );

	// read in a string which is the contents of the required file
	/*
	$reqresult = "";
	while(!feof($fp))
	{
		$reqresult .= fgets($fp, 512);
	}

	fclose($fp);

	return $reqresult;
	*/
}

?>