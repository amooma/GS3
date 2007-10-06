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
	
	static $logfiles = array();
	static $levels = array(
		GS_LOG_DEBUG   => 'DEBG',
		GS_LOG_NOTICE  => 'NOTE',
		GS_LOG_WARNING => 'WARN',
		GS_LOG_FATAL   => 'FATL'
	);
	
	if (@$gs_is_in_gs_log) return false;
	# prevent recursive calls to gs_log()
	
	if ($level > GS_LOG_LEVEL) return true;
	
	$gs_is_in_gs_log = true;
	
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
		$logfiles[$logfile] = @fOpen($logfile, 'ab');  # probably permission denied
		if (! $logfiles[$logfile]) {
			$gs_is_in_gs_log = false;
			return false;
		}
		//@chmod($logfile, 0666);  # in octal mode!
		@exec( $sudo.'chmod 0666 '. qsa($logfile) .' 1>>/dev/null 2>>/dev/null');
	}
	$vLevel = array_key_exists($level, $levels) ? $levels[$level] : '????';
	//$msg = str_replace(GS_DIR, '<GS_DIR>', $msg);
	$msg = str_replace(GS_DIR, '', $msg);
	$dateFn = GS_LOG_GMT ? 'gmDate' : 'date';
	$backtrace = debug_backtrace();
	if (is_array($backtrace) && isSet($backtrace[0])) {
		$line = @$backtrace[0]['line'];
		if (strLen($line) < 4)
			$line = str_pad($line, 4, ' ', STR_PAD_LEFT);
		$file = @$backtrace[0]['file'];
		if (subStr($file, 0, strLen(GS_DIR)) == GS_DIR)
			$file = str_replace(GS_DIR, '', $file);
		$where = $file .':'. $line .':';
	} else
		$where = '';
	$msg = $dateFn('Y-m-d H:i:s') .' ['. $vLevel .'] '. $where .' '. $msg ."\n";
	$ok = @fWrite( $logfiles[$logfile], $msg, strLen($msg) );
	$gs_is_in_gs_log = false;
	return $ok;
}


?>