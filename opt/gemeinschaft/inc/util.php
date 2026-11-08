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


function gs_file_get_contents( $file )
{
	if (@file_exists($file)) {
		return @file_get_contents($file);
	}
	return false;
}


include_once( GS_DIR .'inc/quote_shell_arg.php' );
# scripts which include util.php may rely on log.php being included here

include_once( GS_DIR .'inc/log.php' );
# scripts which include util.php rely on log.php being included here

include_once( GS_DIR .'inc/gettext.php' );
# needed by date_human() (see below)


function normalizeIPs( $str ) {
	//return preg_replace( '/\b0{1,2}(\d*)/', '$1', $str );
	return preg_replace( '/\b0{1,2}(\d+)\b/', '$1', $str );
}


function gs_write_error( $data )
{
	if (! defined('STDERR')) define('STDERR', @fOpen('php://stderr', 'wb'));
	if (php_sapi_name() === 'cli' && STDERR) {
		@fWrite(STDERR, $data, strLen($data));
		@fFlush(STDERR);
	} else {
		echo $data;
	}
}

# error levels introduced in newer versions of PHP:
if (! defined('E_STRICT'           )) define('E_STRICT'           , 1<<11); # since PHP 5
if (! defined('E_RECOVERABLE_ERROR')) define('E_RECOVERABLE_ERROR', 1<<12); # since PHP 5.2
if (! defined('E_DEPRECATED'       )) define('E_DEPRECATED'       , 1<<13); # since PHP 5.3
if (! defined('E_USER_DEPRECATED'  )) define('E_USER_DEPRECATED'  , 1<<14); # since PHP 5.3

# PHP 8 reports several former notices as E_WARNING ("Undefined array key"
# etc.). This code was written with notices turned off and our error handlers
# exit on E_WARNING, so keep treating these as the notices they used to be.
# The same goes for deprecations. Returns true if the error has been dealt with.
#
function gs_err_is_benign( $type, $msg, $file, $line )
{
	if (! ($type & (E_DEPRECATED | E_USER_DEPRECATED))
	&&  ! ($type === E_WARNING && preg_match('/^(?:Undefined (?:variable|array key|property)|Attempt to read property|Trying to access array offset|Uninitialized string offset|Array to string conversion)/S', $msg)))
		return false;
	if (error_reporting() & $type)
		gs_log( GS_LOG_DEBUG, 'PHP: '. $msg .' in '. $file .' on line '. $line );
	return true;
}

function err_handler_die_on_err( $type, $msg, $file, $line )
{
	if (gs_err_is_benign( $type, $msg, $file, $line )) return true;
	switch ($type) {
		case E_NOTICE:
		case E_USER_NOTICE:
		case E_DEPRECATED:
		case E_USER_DEPRECATED:
			if (error_reporting() & $type) {
				gs_log( GS_LOG_NOTICE, 'PHP: '. $msg .' in '. $file .' on line '. $line );
			} else {  # suppressed by @
				//gs_log( GS_LOG_DEBUG , 'PHP: '. $msg .' in '. $file .' on line '. $line .' (suppressed)' );
			}
			break;
		case E_STRICT:
			if (error_reporting() & $type) {
				gs_log( GS_LOG_DEBUG, 'PHP (Strict): '. $msg .' in '. $file .' on line '. $line );
			} else {  # suppressed by @
				gs_log( GS_LOG_DEBUG, 'PHP (strict): '. $msg .' in '. $file .' on line '. $line );
			}
			break;
		case E_ERROR:
		case E_USER_ERROR:
			gs_log( GS_LOG_FATAL  , 'PHP: '. $msg .' in '. $file .' on line '. $line );
			gs_write_error( 'A fatal error occurred. See log for details.'."\n" );
			exit(1);
			break;
		case E_RECOVERABLE_ERROR:
			gs_log( GS_LOG_WARNING, 'PHP: '. $msg .' in '. $file .' on line '. $line );
			break;
		case E_WARNING:
		case E_USER_WARNING:
			if (error_reporting() & $type) {
				gs_log( GS_LOG_WARNING, 'PHP: '. $msg .' in '. $file .' on line '. $line );
				gs_write_error( 'A warning occurred. See log for details.'."\n" );
				exit(1);
			} else {  # suppressed by @
				gs_log( GS_LOG_DEBUG, 'PHP: '. $msg .' in '. $file .' on line '. $line .' (suppressed)' );
			}
			break;
		default:
			gs_log( GS_LOG_WARNING, 'PHP: '. $msg .' in '. $file .' on line '. $line );
			gs_write_error( 'A warning occurred. See log for details.'."\n" );
			exit(1);
			break;
	}
}

function err_handler_quiet( $type, $msg, $file, $line )
{
	if (gs_err_is_benign( $type, $msg, $file, $line )) return true;
	switch ($type) {
		case E_NOTICE:
		case E_USER_NOTICE:
		case E_DEPRECATED:
		case E_USER_DEPRECATED:
			if (error_reporting() & $type) {
				gs_log( GS_LOG_NOTICE, 'PHP: '. $msg .' in '. $file .' on line '. $line );
			} else {  # suppressed by @
				//gs_log( GS_LOG_DEBUG , 'PHP: '. $msg .' in '. $file .' on line '. $line .' (suppressed)' );
			}
			break;
		case E_STRICT:
			if (error_reporting() & $type) {
				gs_log( GS_LOG_DEBUG, 'PHP (Strict): '. $msg .' in '. $file .' on line '. $line );
			} else {  # suppressed by @
				gs_log( GS_LOG_DEBUG, 'PHP (strict): '. $msg .' in '. $file .' on line '. $line );
			}
			break;
		case E_RECOVERABLE_ERROR:
			gs_log( GS_LOG_WARNING, 'PHP: '. $msg .' in '. $file .' on line '. $line );
			break;
		case E_WARNING:
		case E_USER_WARNING:
			if (error_reporting() & $type) {
				gs_log( GS_LOG_WARNING, 'PHP: '. $msg .' in '. $file .' on line '. $line );
				exit(1);
			} else {  # suppressed by @
				gs_log( GS_LOG_DEBUG, 'PHP: '. $msg .' in '. $file .' on line '. $line .' (suppressed)' );
			}
			break;
		default:
			gs_log( GS_LOG_FATAL  , 'PHP: '. $msg .' in '. $file .' on line '. $line );
			exit(1);
	}
}


function gs_shutdown_fn()
{
	# log fatal E_ERROR and E_PARSE errors which the error handler cannot catch
	if (function_exists('error_get_last')) {  # PHP >= 5.2
		$e = error_get_last();
		if (is_array($e)) {
			if ($e['type'] === E_ERROR  # non-catchable fatal error
			||  $e['type'] === E_PARSE  # parse error (e.g. syntax error)
			) {
				err_handler_quiet( $e['type'], $e['message'], $e['file'], $e['line'] );
			}
		}
	}
}
register_shutdown_function('gs_shutdown_fn');


# replacement for strftime(), which is deprecated since PHP 8.1. Knows the
# conversion specifiers we use. Takes the language for the names of days
# and months instead of depending on the locales installed on the system.
#
function gs_strftime( $format, $ts=null, $lang='en' )
{
	static $names = array(
		'en' => array(
			'a' => array('Sun','Mon','Tue','Wed','Thu','Fri','Sat'),
			'b' => array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec')
		),
		'de' => array(
			'a' => array('So','Mo','Di','Mi','Do','Fr','Sa'),
			'b' => array('Jan','Feb',"M\xC3\xA4r",'Apr','Mai','Jun','Jul','Aug','Sep','Okt','Nov','Dez')
		)
	);
	$ts = ($ts === null) ? time() : (int)$ts;
	$n = array_key_exists($lang, $names) ? $names[$lang] : $names['en'];
	
	return preg_replace_callback( '/%(.)/s', function( $m ) use ($ts, $n) {
		switch ($m[1]) {
			case 'a': return $n['a'][(int)date('w', $ts)];
			case 'b': return $n['b'][(int)date('n', $ts) - 1];
			case 'c': return $n['a'][(int)date('w', $ts)] .' '. $n['b'][(int)date('n', $ts) - 1]
			                 .' '. str_pad(date('j', $ts), 2, ' ', STR_PAD_LEFT) . date(' H:i:s Y', $ts);
			case 'd': return date('d', $ts);
			case 'H': return date('H', $ts);
			case 'm': return date('m', $ts);
			case 'M': return date('i', $ts);
			case 'S': return date('s', $ts);
			case 'y': return date('y', $ts);
			case 'Y': return date('Y', $ts);
			case 'z': return date('O', $ts);
			case '%': return '%';
		}
		return $m[0];
	}, $format );
}


function date_human( $ts )
{
	$lang = strToLower(subStr(gs_get_conf('GS_INTL_LANG', 'de_DE'), 0,2));
	if (date('Ymd', $ts) == date('Ymd'))
		$dv = __('heute');
	elseif (date('Ymd', $ts) == date('Ymd', strToTime('-1 days', $ts)))
		$dv = __('gestern');
	else
		$dv = gs_strftime(__('%d. %b'), $ts, $lang);
	$ret = $dv .', '. date(__('H:i'), $ts);
	return $ret;
}


function sec_to_hours( $sec )
{
	return sPrintF('%d:%02d:%02d',
		$sec / 3600     ,
		$sec / 60   % 60,
		$sec        % 60
	);
}



# like posix_isatty() but suppresses the "cannot seek on a pipe" warning
# which occurs for posix_isatty(STDOUT)
#
function gs_isatty( $fd )
{
	if (! function_exists('posix_isatty')) return false;
	set_error_handler( function() { /* ignore error */ } );
	$isatty = @posix_isatty($fd);
	@restore_error_handler();
	return $isatty;
}

# cli scripts could use the return value of this to determine if
# they want to pretty-print their output or otherwise use a parseable
# output format
#
function gs_stdout_is_console()
{
	if (! defined('STDOUT')) return false;
	return gs_isatty(STDOUT);
}


function gs_add_c_slashes( $str )
{
	return addCSlashes( (string)$str, "\\\0\r\n\t\x00..\x1F\x7F..\xFF" );
}


?>