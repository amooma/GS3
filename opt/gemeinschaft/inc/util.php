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


# error levels introduced in newer versions of PHP:
if (! defined('E_STRICT'           )) define('E_STRICT'           , 1<<11); # since PHP 5
if (! defined('E_RECOVERABLE_ERROR')) define('E_RECOVERABLE_ERROR', 1<<12); # since PHP 5.2
if (! defined('E_DEPRECATED'       )) define('E_DEPRECATED'       , 1<<13); # since PHP 5.3
if (! defined('E_USER_DEPRECATED'  )) define('E_USER_DEPRECATED'  , 1<<14); # since PHP 5.3

function err_handler_die_on_err( $type, $msg, $file, $line )
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
		case E_ERROR:
		case E_USER_ERROR:
			gs_log( GS_LOG_FATAL  , 'PHP: '. $msg .' in '. $file .' on line '. $line );
			echo "A fatal error occurred. See log for details.\n";
			die(1);
			break;
		case E_RECOVERABLE_ERROR:
			gs_log( GS_LOG_WARNING, 'PHP: '. $msg .' in '. $file .' on line '. $line );
			break;
		case E_WARNING:
		case E_USER_WARNING:
			if (error_reporting() != 0) {
				gs_log( GS_LOG_WARNING, 'PHP: '. $msg .' in '. $file .' on line '. $line );
				echo "A warning occurred. See log for details.\n";
				exit(1);
			} else {  # suppressed by @
				gs_log( GS_LOG_DEBUG, 'PHP: '. $msg .' in '. $file .' on line '. $line .' (suppressed)' );
			}
			break;
		default:
			gs_log( GS_LOG_WARNING, 'PHP: '. $msg .' in '. $file .' on line '. $line );
			echo "A warning occurred. See log for details.\n";
			exit(1);
			break;
	}
}

function err_handler_quiet( $type, $msg, $file, $line )
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
		case E_WARNING:
		case E_USER_WARNING:
			if (error_reporting() != 0) {
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


function date_human( $ts )
{
	$old_locale = setLocale(LC_TIME, '0');
	$lang = strToLower(subStr(gs_get_conf('GS_INTL_LANG', 'de_DE'), 0,2));
	switch ($lang) {
		case 'de':
			$l = array('de_DE.UTF-8', 'de_DE.utf8', 'de_DE.iso88591', 'de_DE.iso885915@euro', 'de_DE.ISO8859-1', 'de_DE.ISO8859-15', 'de_DE@euro', 'de_DE', 'de');
			break;
		case 'en':
			$l = array('en_US.utf8', 'en_US.iso88591', 'en_US.ISO8859-1', 'en_US.US-ASCII', 'en_US', 'en');
			break;
		default  :
			$l = array('C');
	}
	setLocale(LC_TIME, $l);
	if (date('Ymd', $ts) == date('Ymd'))
		$dv = __('heute');
	elseif (date('Ymd', $ts) == date('Ymd', strToTime('-1 days', $ts)))
		$dv = __('gestern');
	else
		$dv = strFTime(__('%d. %b'), $ts);
	$ret = $dv .', '. date(__('H:i'), $ts);
	setLocale(LC_TIME, array($old_locale, 'C'));
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
	set_error_handler( create_function(
		'$type, $msg, $file, $line',
		'/* ignore error */'
	));
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
	return addCSlashes( $str, "\\\0\r\n\t\x00..\x1F\x7F..\xFF" );
}


?>