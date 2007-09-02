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


######################################################
##
##   Die hier verwendeten Parameter müssen noch
##   escapt / gecastet werden!
##
######################################################


function fax_get_jobs_rec() {
	
	$conn_id = ftp_connect(GS_FAX_SERVER, GS_FAX_PORT);
	if (! $conn_id) return false;
	
	$login_result = ftp_login($conn_id, 'webmanag', 'ablue7');
	if (! $login_result) return false;
	
	if (! ftp_raw($conn_id, 'RCVFMT "%a|%b|%d|%e|%f|%h|%i|%j|%l|%m|%n|%o|%p|%q|%r|%s|%w|%z|%Z"'))
		return false;
	$jobs_r = array();
	$rlist = ftp_rawlist($conn_id,'recvq');
	foreach ($rlist as $rlist_line) {
		$jobs_r[] = explode('|',$rlist_line);
	}
	ftp_close($conn_id);
	return $jobs_r;
}

function fax_get_jobs_done() {
	
	$conn_id = ftp_connect(GS_FAX_SERVER, GS_FAX_PORT);
	if (! $conn_id) return false;
	
	$login_result = ftp_login($conn_id, 'webmanag', 'ablue7');
	if (! $login_result) return false;
	
	if (! ftp_raw($conn_id, 'JOBFMT "%a|%b|%c|%d|%e|%f|%g|%h|%i|%j|%l|%n|%o|%p|%q|%r|%s|%t|%u|%v|%w|%x|%y|%z|%I|%K|%M|%R|%S|%V|%W|%X|%Z"'))
		return false;
	$jobs_r = array();
 	$rlist = ftp_rawlist($conn_id,"doneq");
	foreach ($rlist as $rlist_line) {
		$jobs_r[] = explode('|',$rlist_line);
	}
	ftp_close($conn_id);
	return $jobs_r;
}

function fax_get_jobs_send() {
	
	$conn_id = ftp_connect(GS_FAX_SERVER, GS_FAX_PORT);
	if (! $conn_id) return false;
	
	$login_result = ftp_login($conn_id, 'webmanag', 'ablue7');
	if (! $login_result) return false;
	
	if (! ftp_raw($conn_id, 'JOBFMT "%a|%b|%c|%d|%e|%f|%g|%h|%i|%j|%l|%n|%o|%p|%q|%r|%s|%t|%u|%v|%w|%x|%y|%z|%I|%K|%M|%R|%S|%V|%W|%X|%Z"'))
		return false;
	$jobs_r = array();
 	$rlist = ftp_rawlist($conn_id,"sendq");
	foreach ($rlist as $rlist_line) {
		$jobs_r[] = explode('|',$rlist_line);
	}
	ftp_close($conn_id);
	return $jobs_r;
}

function fax_delete_file( $file ) {
	
	$conn_id = ftp_connect(GS_FAX_SERVER, GS_FAX_PORT);
	if (! $conn_id) return false;
	
	$login_result = ftp_login($conn_id, 'webmanag', 'ablue7');
	if (! $login_result) return false;
	
	if (! ftp_raw($conn_id, 'admin ablue7')) return false;
	$ret_val = ftp_delete($conn_id, $file);
	ftp_close($conn_id);
	return $ret_val;
}

function fax_delete_job( $job ) {
	
	$conn_id = ftp_connect(GS_FAX_SERVER, GS_FAX_PORT);
	if (! $conn_id) return false;
	
	$login_result = ftp_login($conn_id, 'webmanag', 'ablue7');
	if (! $login_result) return false;
	
	if (! ftp_raw($conn_id, 'admin ablue7')) return false;
	$ret_val = ftp_raw($conn_id, 'jdele '.$job);
	ftp_close($conn_id);
	return $ret_val;
}

function fax_kill_job( $job ) {
	
	$conn_id = ftp_connect(GS_FAX_SERVER, GS_FAX_PORT);
	if (! $conn_id) return false;
	
	$login_result = ftp_login($conn_id, 'webmanag', 'ablue7');
	if (! $login_result) return false;
	
	if (! ftp_raw($conn_id, 'admin ablue7')) return false;
	$ret_val = ftp_raw($conn_id, 'jkill '.$job);
	ftp_close($conn_id);
	return $ret_val;
}

function fax_send( $user_id, $user_name, $to_num, $from_num, $file, $user_email, $resolution ) {
	
	$conn_id = ftp_connect(GS_FAX_SERVER, GS_FAX_PORT);
	if (! $conn_id) return false;
	
	if ($resolution < 98) $resolution = 98; 
	
	$login_result = ftp_login($conn_id, 'webmanag', 'ablue7');
	if (! $login_result) return false;
	
	$ret_val = ftp_raw($conn_id, 'jnew');
	//echo "<pre>\n";
	//print_r($ret_val);
	//echo "</pre>\n";
	$ret_par = explode(' ',$ret_val[0]);
	$jobid = (int)@$ret_par[array_search('jobid:',$ret_par)+1];
	
	if ($jobid) {
		//echo "<pre>\n";
		$ret_val = ftp_put($conn_id, $file, $file, FTP_BINARY);
		//print_r($ret_val);
		$ret_val = ftp_raw($conn_id, 'job '.$jobid);
		//print_r($ret_val);
		$ret_val = ftp_raw($conn_id, 'jparm DIALSTRING '.$to_num);
		//print_r($ret_val);
		$ret_val = ftp_raw($conn_id, 'jparm TSI '.$from_num);
		//print_r($ret_val);
		$ret_val = ftp_raw($conn_id, 'jparm FAXNAME '.$user_id);
		//print_r($ret_val);
		$ret_val = ftp_raw($conn_id, 'jparm FAXNUMBER '.$from_num);
		//print_r($ret_val);
		$ret_val = ftp_raw($conn_id, 'jparm FROMUSER '.$user_name);
		//print_r($ret_val);
		$ret_val = ftp_raw($conn_id, 'jparm LASTTIME 000259');
		//print_r($ret_val);
		$ret_val = ftp_raw($conn_id, 'jparm NOTIFYADDR '.$user_email);
		//print_r($ret_val);
		$ret_val = ftp_raw($conn_id, 'jparm MAXDIALS 6');
		//print_r($ret_val);
		$ret_val = ftp_raw($conn_id, 'jparm MAXTRIES 6');
		//print_r($ret_val);
		$ret_val = ftp_raw($conn_id, 'jparm NOTIFY NONE');
		//print_r($ret_val);
		$ret_val = ftp_raw($conn_id, 'jparm PAGECHOP default');
		//print_r($ret_val);
		$ret_val = ftp_raw($conn_id, 'jparm PAGEWIDTH 209');
		//print_r($ret_val);
		$ret_val = ftp_raw($conn_id, 'jparm PAGELENGTH 296');
		//print_r($ret_val);
		$ret_val = ftp_raw($conn_id, 'jparm SCHEDPRI 127');
		//print_r($ret_val);
		$ret_val = ftp_raw($conn_id, 'jparm SENDTIME NOW');
		//print_r($ret_val);
		$ret_val = ftp_raw($conn_id, 'jparm VRES '.$resolution);
		//print_r($ret_val);
		$ret_val = ftp_raw($conn_id, 'jparm DOCUMENT '.$file);
		//print_r($ret_val);
		$ret_val = ftp_raw($conn_id, 'jsubm '.$jobid);
		//print_r($ret_val);
		//echo "</pre>\n";
	}
	ftp_close($conn_id);
	return ($ret_val ? $jobid : false);
}

function fax_download( $file ) {
	
	$conn_id = ftp_connect(GS_FAX_SERVER, GS_FAX_PORT);
	if (! $conn_id) return false;
	
	$login_result = ftp_login($conn_id, 'webmanag', 'ablue7');
	if (! $login_result) return false;
	
	if (! ftp_raw($conn_id, 'admin ablue7')) return false;
	$ret_val = ftp_get($conn_id, '/tmp/'.$file, 'recvq/'.$file, FTP_BINARY);
	ftp_close($conn_id);
	return $ret_val;
}


?>