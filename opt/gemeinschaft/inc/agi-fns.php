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
require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/log.php' );

# The AGI environment is available in $AGI_ENV.
# The Asterisk environment is available in $_ENV['AGI_...'].

$AGI_ENV = array();

function gs_agi_str_esc( $str )
{
	$str = str_replace(
		array( '\\'  , ' '  , "\n" , "\r" , "\t" , '"'   ),
		array( '\\\\', '\\ ', '\\ ', '\\ ', '\\ ', '\\"' ),
		$str );
	return ($str == '' ? '""' : $str);
}

/*
function _gs_agi_read_response()
{
	uSleep(1);
	stream_set_blocking(STDIN, false);
	$buf = '';
	$i=0;
	$select = array(STDIN);  # needs to be passed by reference
	$null   = null;          # needs to be passed by reference
	while (true) {
		//stream_set_timeout(STDIN,1);
		if (stream_select($select, $null, $null, 0, 20000) !== false) {
			$buf .= fRead(STDIN,1);
		}
		
		if (subStr($buf,-1) === "\n") {  # end of line
			$buf = subStr($buf,0,-1);
			break;
		}
		
		++$i;
		if ($i > 200) break;  # 20000 ms * 200 = 4 s
	}
	gs_log(GS_LOG_DEBUG, "AGI command response: $buf");
}
*/

function gs_agi_do( $cmd )
{
	$fail     = array('code' => 500 , 'result' => -1  , 'data' => ''  );
	$response = array('code' => null, 'result' => null, 'data' => null);
	
	if (! @fWrite(STDOUT, $cmd ."\n")) {
		return $fail;
	}
	/*
	if (! in_array(php_sapi_name(), array('cgi'), true)) {
		# the correct way
	*/
		if (! @fFlush(STDOUT)) {
			gs_log( GS_LOG_WARNING, 'Failed to flush StdOut in AGI script!' );
			uSleep(1000);
		}
	/*
	} else {
		# STDOUT is not defined in the "cgi" version of PHP.
		# However, the RedHat/Centos way of running shell scripts in
		# php-cgi instead of php-cli is simply wrong.
		uSleep(10);
	}
	*/
	
	stream_set_blocking(STDIN, true);
	$count = 0;
	$str = '';
	do {
		uSleep(1000);
		stream_set_timeout(STDIN,1);
		$str .= trim(fGetS(STDIN, 4096));
	} while ($str == '' && $count++ < 5);
	//gs_log(GS_LOG_DEBUG, "AGI command response: $str");
	
	if ($count >= 5) {
		return $fail;
	}
	
	$response['code'] = subStr($str,0,3);
	$str = trim(subStr($str,3));
	if (subStr($str,0,1) === '-') {  # multiline response
		$count = 0;
		$str = subStr($str,1) ."\n";
		$line = fGetS(STDIN, 4096);
		while (subStr($line,0,3) !== $response['code'] && $count < 5) {
			$str .= $line;
			$line = fGetS(STDIN, 4096);
			$count = (trim($line) == '') ? $count + 1 : 0;
		}
		if ($count >= 5) {
			return $fail;
		}
	}
	
	$response['result'] = null;
	$response['data'] = '';
	if ($response['code'] !== '200') {
		$response['data'] = $str;
	}
	else {
		$parse = explode(' ', trim($str));
		$in_token = false;
		foreach ($parse as $token) {
			if ($in_token) {
				$response['data'] .= ' '. trim($token, '() ');
				if (subStr($token, strLen($token)-1, 1) === ')') $in_token = false;
			}
			elseif (subStr($token,0,1) === '(') {
				if (subStr($token, strLen($token)-1, 1) !== ')') $in_token = true;
				$response['data'] .= ' '. trim($token, '() ');
			}
			elseif (strPos($token, '=') !== false) {
				$token = explode('=', $token);
				$response[$token[0]] = $token[1];
			}
			elseif ($token != '') {
				$response['data'] .= ' '. $token;
			}
		}
		$response['data'] = trim($response['data']);
	}
	
	return $response;
}

function gs_agi_reponse_is_success( $response )
{
	return (is_array($response) && array_key_exists('code',$response) && $response['code'] === '200');
}

function gs_agi_do_bool( $cmd )
{
	return gs_agi_reponse_is_success( gs_agi_do( $cmd ));
}



function gs_agi_verbose( $str, $level=1 )
{
	return gs_agi_do_bool( 'VERBOSE '. gs_agi_str_esc($str) .' '. (int)$level );
}





function gs_agi_err( $msg='')
{
	gs_agi_verbose( '### '.($msg != '' ? $msg : 'An error occurred.'), 1 );
	$ok = gs_agi_do_bool( 'HANGUP' );
	exit(1);
}

function gs_err_handler_agi( $type, $msg, $file, $line )
{
	switch ($type) {
		case E_NOTICE:
		case E_USER_NOTICE:
		case E_DEPRECATED:
		case E_USER_DEPRECATED:
			if (error_reporting() != 0) {
				gs_log( GS_LOG_NOTICE, 'PHP: '. $msg .' in '. $file .' on line '. $line );
			} else {  # suppressed by @
				//gs_log( GS_LOG_DEBUG , 'PHP: '. $msg .' in '. $file .' on line '. $line .' (suppressed)' );
			}
			break;
		case E_STRICT:
			if (error_reporting() != 0) {
				gs_log( GS_LOG_DEBUG, 'PHP (Strict): '. $msg .' in '. $file .' on line '. $line );
			} else {  # suppressed by @
				gs_log( GS_LOG_DEBUG, 'PHP (strict): '. $msg .' in '. $file .' on line '. $line );
			}
			break;
		case E_RECOVERABLE_ERROR:
			gs_log( GS_LOG_WARNING, 'PHP: '. $msg .' in '. $file .' on line '. $line );
			break;
		case E_ERROR:
		case E_USER_ERROR:
			gs_log( GS_LOG_FATAL  , 'PHP: '. $msg .' in '. $file .' on line '. $line );
			gs_agi_err('A fatal error occurred. See log for details.');
			break;
		case E_WARNING:
		case E_USER_WARNING:
		default:
			if (error_reporting() != 0) {
				gs_log( GS_LOG_WARNING, 'PHP: '. $msg .' in '. $file .' on line '. $line );
				gs_agi_err('A warning occurred. See log for details.');
			} else {  # suppressed by @
				gs_log( GS_LOG_DEBUG, 'PHP: '. $msg .' in '. $file .' on line '. $line .' (suppressed)' );
			}
			break;
	}
}
@restore_error_handler();
set_error_handler('gs_err_handler_agi');

function gs_agi_shutdown_fn()
{
	# log fatal E_ERROR and E_PARSE errors which the error handler cannot catch
	if (function_exists('error_get_last')) {  # PHP >= 5.2
		$e = error_get_last();
		if (is_array($e)) {
			if ($e['type'] === E_ERROR  # non-catchable fatal error
			||  $e['type'] === E_PARSE  # parse error (e.g. syntax error)
			) {
				gs_err_handler_agi( $e['type'], $e['message'], $e['file'], $e['line'] );
			}
		}
	}
}
register_shutdown_function('gs_agi_shutdown_fn');


function gs_agi_read_agi_env()
{
	global $AGI_ENV;
	
	$lines_read = 0;
	stream_set_blocking(STDIN, true);
	while (! fEof(STDIN)) {
		$line = fGetS(STDIN);
		if (strLen($line) < 3) break;  # end of AGI environment
		if (! preg_match('/^agi_([^:]+): ?([^\n\r]*)/Sm', $line, $m))
			break;
		$AGI_ENV[$m[1]] = $m[2];
		if (++$lines_read > 100) break;
	}
}

gs_agi_read_agi_env();



function gs_agi_set_variable( $name, $val )
{
	if (! preg_match('/^[a-zA-Z0-9_]+$/', $name)) return false;
	return gs_agi_do_bool( 'SET VARIABLE '. $name .' '. gs_agi_str_esc($val) );
}

function gs_agi_get_variable( $name )
{
	if (! preg_match('/^[a-zA-Z0-9_]+$/', $name)) return false;
	$response = gs_agi_do( 'GET VARIABLE '. $name );
	if (gs_agi_reponse_is_success($response)) {
		return ($response['result'] == 1) ? $response['data'] : null;
	} else {
		return false;
	}
}



?>