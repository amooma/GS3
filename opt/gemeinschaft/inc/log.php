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
require_once( GS_DIR .'inc/quote_shell_arg.php' );


$gs_is_in_gs_log = false;

function gs_log( $level, $msg, $logfile=null )
{
	global $gs_is_in_gs_log;
	
	static $log_to = null;
	static $logfiles = array();
	static $levels = array(
		GS_LOG_DEBUG   => array('v'=>'debug', 'sll'=>LOG_DEBUG   ),
		GS_LOG_NOTICE  => array('v'=>'note' , 'sll'=>LOG_INFO    ),
		GS_LOG_WARNING => array('v'=>'WARN' , 'sll'=>LOG_WARNING ),
		GS_LOG_FATAL   => array('v'=>'ERROR', 'sll'=>LOG_ERR     )
	);
	static $syslog_opened = false;
	static $syslog_facility = null;
	
	if (@$gs_is_in_gs_log) return false;
	# prevent recursive calls to gs_log()
	
	if ($level > GS_LOG_LEVEL) return true;
	
	$gs_is_in_gs_log = true;
	
	if ($log_to === null) $log_to = gs_get_conf('GS_LOG_TO');
	
	$level_info = array_key_exists($level, $levels)
		? $levels[$level] : array('v'=>'???? ', 'sll'=>LOG_WARNING);
	//$msg = str_replace(GS_DIR, '<GS_DIR>', $msg);
	$msg = str_replace(GS_DIR, '', $msg);
	$backtrace = debug_backtrace();
	if (is_array($backtrace) && isSet($backtrace[0])) {
		$file = @$backtrace[0]['file'];
		if (subStr($file, 0, strLen(GS_DIR)) === GS_DIR) {
			$file = str_replace(GS_DIR, '', $file);
		}
		$line = @$backtrace[0]['line'];
	} else {
		$file = '';
		$line = 0;
	}
	
	if ($log_to === 'file') {
		
		$dateFn = GS_LOG_GMT ? 'gmDate' : 'date';
		if (strLen($line) < 4)
			$line = str_pad($line, 4, ' ', STR_PAD_LEFT);
		$msg = $dateFn('Y-m-d H:i:s') .' ['. str_pad($level_info['v'], 5) .'] '. $file.':'.$line.': '. $msg ."\n";
		if (! $logfile) $logfile = GS_LOG_FILE;
		if (@subStr($logfile,0,1) != '/')
			$logfile = '/var/log/gemeinschaft/'. $logfile;
		
		if (! @array_key_exists($logfile, $logfiles)) {
			$sudo = (posix_getEUid()==0 ? '' : 'sudo ');
			if (! @file_exists($logfile)) {
				 $err=0; $out=array();
				 @exec( $sudo.'mkdir -p '. qsa(dirName($logfile)) .' 1>>/dev/null 2>>/dev/null', $out, $err );
				 if ($err != 0) {  # probably permission denied
					$gs_is_in_gs_log = false;
					return false;
				 }
			}
			$logfiles[$logfile] = @fOpen($logfile, 'ab');  # might fail if permission denied
			if (! $logfiles[$logfile]) {
				$gs_is_in_gs_log = false;
				return false;
			}
			//@chmod($logfile, 0666);  # in octal mode!
			@exec( $sudo.'chmod 0666 '. qsa($logfile) .' 1>>/dev/null 2>>/dev/null');
		}
		$ok = @fWrite( $logfiles[$logfile], $msg, strLen($msg) );
		
	}
	elseif ($log_to === 'syslog') {
		
		if ($syslog_facility === null) {
			$fac_name = strToUpper(gs_get_conf('GS_LOG_SYSLOG_FACILITY'));
			if (in_array($fac_name, array(
				'LOCAL0', 'LOCAL1', 'LOCAL2', 'LOCAL3',
				'LOCAL4', 'LOCAL5', 'LOCAL6', 'LOCAL7',
				'USER', 'MAIL', 'DAEMON', 'AUTH', 'AUTHPRIV',
				'SYSLOG', 'LPR', 'NEWS', 'UUCP', 'CRON'
				), true)
			&&  defined('LOG_'.$fac_name))
			{
				$syslog_facility = constant('LOG_'.$fac_name);
			} else {
				$syslog_facility = LOG_USER;
			}
		}
		
		if (subStr($file,-4)==='.php') $file = subStr($file,0,-4);
		if (strLen($file) <= 32) {
			$tag = $file;
		} else {
			$tag = baseName($file);
		}
		$msg = $tag.'#'.$line.': ('.$level_info['v'].') '. $msg;
		if (! $syslog_opened) {
			if (! $syslog_facility) {
				$syslog_facility = LOG_LOCAL5;
			}
			$syslog_opened = @openLog( 'gemeinschaft', LOG_ODELAY, $syslog_facility );
		}
		$sll = @$level_info['sll'];
		if ($sll === null) $sll = LOG_WARNING;
		$ok = @sysLog( $sll, addCSlashes($msg, "\\\0\r\n\t\x00..\x1F\x7F..\xFF") );
		
	}
	else {
		$ok = false;
	}
	
	$gs_is_in_gs_log = false;
	return $ok;
}


?>