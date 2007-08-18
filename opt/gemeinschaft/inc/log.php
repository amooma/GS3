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


function gs_log( $level, $msg, $logfile=null )
{
	static $logfiles = array();
	static $levels = array(
		GS_LOG_DEBUG   => 'DEBUG',
		GS_LOG_NOTICE  => 'NOTICE',
		GS_LOG_WARNING => 'WARNING',
		GS_LOG_FATAL   => 'FATAL'
	);
	
	if ($level > GS_LOG_LEVEL) return true;
	
	if (! $logfile) $logfile = GS_LOG_FILE;
	if (@subStr($logfile,0,1) != '/')
		$logfile = '/var/log/gemeinschaft/'. $logfile;
	
	if (! @array_key_exists($logfile, $logfiles)) {
		if (! file_exists($logfile)) {
			 @ exec( 'mkdir -p '. escapeShellArg(dirName($logfile)) .' 1>>/dev/null 2>&1', $out, $err );
			 if ($err != 0) return false;  # probably permission denied
		}
		$logfiles[$logfile] = @ fOpen($logfile, 'ab');  # probably permission denied
		if (! $logfiles[$logfile]) return false;
		//@chmod($logfile, 0666);  # in octal mode!
		@exec('sudo chmod 0666 '. escapeShellArg($logfile) .' 1>>/dev/null 2>>/dev/null');
	}
	$vLevel = @ $levels[$level];
	if (! $vLevel) $vLevel = 'UNKNOWN';
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
	return @fWrite( $logfiles[$logfile], $msg, strLen($msg) );
}


?>