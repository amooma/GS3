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


$gs_is_in_gs_syslog = false;

function gs_log_syslog( $level, $msg, $logfile=null )
{
	global $gs_is_in_gs_syslog;
	
	static $facility = 1;  # 1 = user-level
	static $hostname = null;
	
	static $sock = null;
	static $num_failed = 0;
	
	static $levels = array(  # see RFC 3164
		GS_LOG_DEBUG   => 7,  # 7 = Debug
		                      # 6 = Informational
		GS_LOG_NOTICE  => 5,  # 5 = Notice
		                      # 4 = Warning
		GS_LOG_WARNING => 3,  # 3 = Error
		GS_LOG_FATAL   => 2   # 2 = Critical
		                      # 1 = Alert
		                      # 0 = Emergency
	);
	
	if (@$gs_is_in_gs_syslog) return false;
	# prevent recursive calls to gs_log_syslog()
	
	if ($level > GS_LOG_LEVEL) return true;
	
	$gs_is_in_gs_syslog = true;
	
	
	if ($hostname === null) {
		$hostname = 'localhost';
		$err=0; $out=array();
		@exec( 'hostname 2>>/dev/null', $out, $err );
		if ($err == 0) {
			$ret = preg_replace('/[^\x21-\x7E]/', '', implode('',$out));
			if ($ret != '')
				$hostname = $ret;
		}
	}
	
	if (! $sock) {
		$use_tcp = gs_get_conf('GS_SYSLOG_TCP');
		$timeout = ($use_tcp ? 5:1);
		if ($num_failed > 2) $timeout = 1;
		$sock = @fSockOpen( ($use_tcp ? 'tcp':'udp') .'://'.gs_get_conf('GS_SYSLOG_HOST'), 514, $err, $errmsg, $timeout );
		if (! $sock) {
			++$num_failed;
			$gs_is_in_gs_syslog = false;
			return false;
		}
		@stream_set_blocking($sock, 0);
		@stream_set_timeout ($sock, $timeout);
	}
	
	$slLevel = array_key_exists($level, $levels) ? $levels[$level] : 5;
	$pri = $facility * 8 + $slLevel;
	
	$msg = str_replace(GS_DIR, '', $msg);
	$msg = str_replace("\n", ' ## ', $msg);
	$msg = preg_replace('/[^\x20-\x7E]/', '', $msg);
	
	$dateFn = GS_LOG_GMT ? 'gmDate' : 'date';
	$t = time();
	
	$backtrace = debug_backtrace();
	if (is_array($backtrace) && isSet($backtrace[0])) {
		$line = @$backtrace[0]['line'];
		$file = @$backtrace[0]['file'];
		if (subStr($file, 0, strLen(GS_DIR)) == GS_DIR)
			$file = str_replace(GS_DIR, '', $file);
		if (strLen($file) <= 32) {
			$tag = $file;
		} else {
			$file = baseName($file);
			$tag = subStr($file,0,32);
		}
		$where = $tag .'['.$line.']';
	} else {
		$where = '-' .'['.'0'.']';
	}
	
	$logstr =
		'<'.$pri.'>'.    //'Jan 4 12:12:12 host foo[345]:';
		$dateFn('M',$t) .' '. preg_replace('/^0/', ' ', $dateFn('m',$t)) .' '.
		$dateFn('H:i:s',$t) .' '.
		$hostname .' '.
		$where .':'. $msg;
	if (strLen($logstr) > 1022)
		$logstr = subStr($logstr,0,1022).'..';
	
	$ok = @fWrite( $sock, $logstr, strLen($logstr) );
	$gs_is_in_gs_syslog = false;
	return $ok;
}


?>