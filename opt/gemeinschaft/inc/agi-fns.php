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

# The AGI environment is going to be made available in $AGI_ENV by this library.
# The Asterisk environment is available in $_ENV['AST_...'].

$AGI_ENV = array();

$agi_basename = baseName($_SERVER['SCRIPT_FILENAME']);
$agi_log_prefix = $agi_basename.'['.getMyPid().']| ';

function gs_agi_log( $level, $msg )
{
	global $agi_log_prefix;
	return gs_log( $level, $agi_log_prefix.$msg, 'agi.log', true );
}

gs_agi_log( GS_LOG_DEBUG, 'Launched ---------------------------' );
$log_cmdline = baseName($argv[0]);
for ($i=1; $i<$argc; ++$i) {
	$log_cmdline.= ' '. ($argv[$i] != '' ? escapeShellArg($argv[$i]) : '\'\'');
}
gs_agi_log( GS_LOG_DEBUG, 'Cmdline: '.$log_cmdline );
unset($log_cmd);


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
		if ($i > 200) break;  # 20000 us * 200 = 4 s
	}
	gs_log( GS_LOG_DEBUG, "AGI command response: $buf" );
}
*/

function gs_agi_do( $cmd )
{
	global $agi_basename;
	
	$fail     = array('code' => 500 , 'result' => -1  , 'data' => ''  );
	$response = array('code' => null, 'result' => null, 'data' => null);
	
	$cmd = trim($cmd);
	if ($cmd === '') {
		trigger_error( "Empty AGI command.", E_USER_NOTICE );
		return $fail;
	}
	
	if (@fWrite(STDOUT, $cmd."\n", strLen($cmd)+1) === false) {
		trigger_error( "AGI command \"$cmd\" failed! Could not write to StdOut.", E_USER_WARNING );
		return $fail;
	}
	gs_agi_log( GS_LOG_DEBUG, 'Tx << '.$cmd );
	/*
	if (! in_array(php_sapi_name(), array('cgi'), true)) {
		# the correct way
	*/
		if (! @fFlush(STDOUT)) {
			gs_agi_log( GS_LOG_WARNING, 'Failed to flush StdOut!' );
			gs_log( GS_LOG_WARNING, "Failed to flush StdOut in AGI script $agi_basename!" );
			@ob_flush(); @flush();
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
	gs_agi_log( GS_LOG_DEBUG, 'Rx >> '.$str );
	
	if ($count >= 5) {
		trigger_error( "AGI command \"$cmd\" failed! Could not read response.", E_USER_WARNING );
		return $fail;
	}
	
	$response['code'] = subStr($str,0,3);
	$str = trim(subStr($str,3));
	if (subStr($str,0,1) === '-') {  # multi-line response
		$count = 0;
		$str = subStr($str,1) ."\n";
		$line = fGetS(STDIN, 4096);
		gs_agi_log( GS_LOG_DEBUG, 'Rx >> '.$line );
		while (subStr($line,0,3) !== $response['code'] && $count < 5) {
			$str .= $line;
			$line = fGetS(STDIN, 4096);
			gs_agi_log( GS_LOG_DEBUG, 'Rx >> '.$line );
			$count = (trim($line) == '') ? $count + 1 : 0;
		}
		if ($count >= 5) {
			trigger_error( "AGI command \"$cmd\" failed! Could not read multi-line response.", E_USER_WARNING );
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
	$str = preg_replace('/^(#*)(?: *)/', '$1 -GS- ', $str);  //FIXME?
	return gs_agi_do_bool( 'VERBOSE '. gs_agi_str_esc($str) .' '. (int)$level );
}



$gs_in_agi_err = false;

function gs_agi_err( $msg='')
{
	global $gs_in_agi_err;
	
	if (@$gs_in_agi_err) return;  # prevent recursive calls by gs_err_handler_agi()
	$gs_in_agi_err = true;
	
	@gs_agi_verbose( '### '.($msg != '' ? $msg : 'An error occurred.'), 1 );
	@gs_agi_hangup();
	
	$gs_in_agi_err = false;
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
	global $agi_basename;
	
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
	gs_agi_log( GS_LOG_DEBUG, 'Done -------------------------------' );
}
register_shutdown_function('gs_agi_shutdown_fn');


function gs_agi_read_line()
{
	$buf = '';
	$i=0;
	$select = array(STDIN);  # needs to be passed by reference
	$null   = null;          # needs to be passed by reference
	stream_set_blocking(STDIN, true);
	stream_set_timeout(STDIN, 1);
	while (true) {
		if (stream_select($select, $null, $null, 0, 500000) > 0) {
			$buf .= fGetS(STDIN, 8192);
			if (subStr($buf,-1) === "\n") {  # end of line
				//$buf = subStr($buf,0,-1);
				$buf = rTrim($buf);
				break;
			}
		}
		if (++$i > 10) {  # 500000 us * 10 = 5 s
			gs_agi_log( GS_LOG_WARNING, "Timeout while waiting for input. Buffer is \"$buf\"." );
			if ($buf === '') return false;
			break;
		}
	}
	//gs_agi_log( GS_LOG_DEBUG, "LINE: $buf (".strLen($buf).")" );
	return $buf;
}

function gs_get_proc_info( $pid )
{
	if (! preg_match('/^[0-9]+$/', $pid)) return false;
	$proc_status_file = '/proc/'.$pid.'/status';
	if (! file_exists($proc_status_file)) return false;
	$proc_status = @file_get_contents($proc_status_file);
	if ($proc_status == '') return false;
	$info = array('name'=>null, 'ppid'=>null);
	if (preg_match('/^Name:[\t ]*(.*)/mi', $proc_status, $m))
		$info['name'] = $m[1];
	if (preg_match('/^PPid:[\t ]*(.*)/mi', $proc_status, $m))
		$info['ppid'] = $m[1];
	return $info;
}

function gs_get_proc_parents_info()
{
	$bt = array();
	$ppid = posix_getPPid();
	do {
		$info = gs_get_proc_info($ppid);
		if (! is_array($info) || $info['ppid'] === null) break;
		$bt[] = $info['name'] . ' ('.$ppid.')';
		$ppid = (int)$info['ppid'];
	} while (true);
	return $bt;
}

function gs_agi_read_agi_env()
{
	global $AGI_ENV, $agi_basename;
	
	# depending on PHP's variables_order ini setting (default: "EGPCS")
	# the environment may or may not be available in $_ENV or $_SERVER.
	if (! @array_key_exists('AST_AGI_DIR', $_ENV)
	&&  ! @array_key_exists('AST_AGI_DIR', $_SERVER)
	&&  trim(@getEnv('AST_AGI_DIR') == '')
	) {
		gs_agi_log( GS_LOG_FATAL, 'AGI script was invoked without Asterisk environment!' );
		gs_log( GS_LOG_FATAL, 'AGI script '.$agi_basename.' was invoked without Asterisk environment!' );
		$bt = gs_get_proc_parents_info();
		gs_agi_log( GS_LOG_DEBUG, 'Parents: '.implode(' <- ',$bt) );
		gs_log( GS_LOG_DEBUG, 'Parents: '.implode(' <- ',$bt) );
		echo 'VERBOSE '. gs_agi_str_esc( 'No Asterisk environment!' ) .' '. 1 ."\n";
		echo 'HANGUP' ."\n";
		exit(1);
	}
	
	gs_agi_log( GS_LOG_DEBUG, 'Reading AGI headers ...' );
	$lines_read = 0;
	
	$lines_read = 0;
	$read_some_agi_headers = false;
	while (true) {
		$line = gs_agi_read_line();
		if ($line !== false) {
			gs_agi_log( GS_LOG_DEBUG, 'Rx >> '.$line );
		}
		if (++$lines_read > 100) {
			gs_agi_log( GS_LOG_WARNING, 'Received more than 100 AGI lines!' );
			gs_agi_err( 'Received more than 100 AGI headers!' );
		}
		if (strLen($line) < 3) {  # end of AGI environment
			if (! $read_some_agi_headers) {
				/*
				gs_agi_log( GS_LOG_NOTICE, 'Unexpected end of AGI headers! Continuing to read ...' );
				continue;
				*/
				gs_agi_log( GS_LOG_FATAL, 'AGI script was invoked without AGI environment!' );
				gs_log( GS_LOG_FATAL, 'AGI script '.$agi_basename.' was invoked without AGI environment!' );
				$bt = gs_get_proc_parents_info();
				gs_agi_log( GS_LOG_DEBUG, 'Parents: '.implode(' <- ',$bt) );
				echo 'VERBOSE '. gs_agi_str_esc( 'No AGI environment!' ) .' '. 1 ."\n";
				echo 'HANGUP' ."\n";
				exit(1);
			}
			break;
		}
		if (! preg_match('/^agi_([^:]+): ?([^\n\r]*)/Sm', $line, $m)) {
			gs_agi_log( GS_LOG_WARNING, "Received invalid AGI header \"$line\"!" );
			continue;
		}
		$AGI_ENV[$m[1]] = $m[2];
		$read_some_agi_headers = true;
	}
	
	gs_agi_log( GS_LOG_DEBUG, 'Done reading AGI headers.' );
}

gs_agi_read_agi_env();



function gs_agi_set_variable( $name, $val )
{
	if (! preg_match('/^[a-zA-Z0-9_()]+$/', $name)) {
		trigger_error( "AGI: Invalid variable name \"$name\"!", E_USER_WARNING );
		return false;
	}
	return gs_agi_do_bool( 'SET VARIABLE '. $name .' '. gs_agi_str_esc($val) );
}

function gs_agi_get_variable( $name )
{
	if (! preg_match('/^[a-zA-Z0-9_()]+$/', $name)) {
		trigger_error( "AGI: Invalid variable name \"$name\"!", E_USER_WARNING );
		return false;
	}
	$response = gs_agi_do( 'GET VARIABLE '. $name );
	if (gs_agi_reponse_is_success($response)) {
		return ($response['result'] == 1) ? $response['data'] : null;
	} else {
		return false;
	}
}

function gs_agi_hangup( $channel='' )
{
	if ($channel != '' && ! preg_match('/^[a-zA-Z0-9_\-\/]+$/', $channel)) {
		trigger_error( "AGI: Invalid channel name \"$channel\"!", E_USER_WARNING );
		return false;
	}
	return gs_agi_do_bool( 'HANGUP'. ($channel != '' ? ' '.gs_agi_str_esc($channel) : '') );
}



?>
