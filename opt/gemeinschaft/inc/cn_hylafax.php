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
##   Die hier verwendeten Parameter muessen noch
##   escapt / gecastet werden!
##
######################################################


function fax_get_jobs_rec( $user='', $pass='' )
{
	if ($user == '') {
		$user = gs_get_conf('GS_FAX_HYLAFAX_ADMIN');
		$pass = gs_get_conf('GS_FAX_HYLAFAX_PASS');
		if ($user == '') return false;
	}
	
	$conn_id = ftp_connect(
		gs_get_conf('GS_FAX_HYLAFAX_HOST'),
		gs_get_conf('GS_FAX_HYLAFAX_PORT'));
	if (! $conn_id) return false;
	
	$login_result = ftp_login($conn_id, $user, $pass);
	if (! $login_result) return false;
	
	if (! ftp_raw($conn_id, 'RCVFMT "%a|%b|%d|%e|%f|%h|%i|%j|%l|%m|%n|%o|%p|%q|%r|%s|%w|%z|%Z"'))
		return false;
	
	$jobs_r = array();
	$rlist = ftp_rawlist($conn_id, "recvq");
	
	if (is_array($rlist) && count($rlist)>0) {
		foreach ($rlist as $rlist_line) {
			$jobs_r[] = explode('|',$rlist_line);
		}
	}
	ftp_close($conn_id);
	return $jobs_r;
}


function fax_get_jobs_done( $user='', $pass='' )
{
	if ($user == '') {
		$user = gs_get_conf('GS_FAX_HYLAFAX_ADMIN');
		$pass = gs_get_conf('GS_FAX_HYLAFAX_PASS');
		if ($user == '') return false;
	}
	
	$conn_id = ftp_connect(
		gs_get_conf('GS_FAX_HYLAFAX_HOST'),
		gs_get_conf('GS_FAX_HYLAFAX_PORT'));
	if (! $conn_id) return false;
	
	$login_result = ftp_login($conn_id, $user, $pass);
	if (! $login_result) return false;
	
	if (! ftp_raw($conn_id, 'JOBFMT "%a|%b|%c|%d|%e|%f|%g|%h|%i|%j|%l|%n|%o|%p|%q|%r|%s|%t|%u|%v|%w|%x|%y|%z|%I|%K|%M|%R|%S|%V|%W|%X|%Z"'))
		return false;
	$jobs_r = array();
 	$rlist = ftp_rawlist($conn_id, "doneq");
	
	if (is_array($rlist) && count($rlist)>0) {
		foreach ($rlist as $rlist_line) {
			$jobs_r[] = explode('|',$rlist_line);
		}
	}
	ftp_close($conn_id);
	return $jobs_r;
}


function fax_get_jobs_send( $user='', $pass='' )
{
	if ($user == '') {
		$user = gs_get_conf('GS_FAX_HYLAFAX_ADMIN');
		$pass = gs_get_conf('GS_FAX_HYLAFAX_PASS');
		if ($user == '') return false;
	}
	
	$conn_id = ftp_connect(
		gs_get_conf('GS_FAX_HYLAFAX_HOST'),
		gs_get_conf('GS_FAX_HYLAFAX_PORT'));
	if (! $conn_id) return false;
	
	$login_result = ftp_login($conn_id, $user, $pass);
	if (! $login_result) return false;
	
	if (! ftp_raw($conn_id, 'JOBFMT "%a|%b|%c|%d|%e|%f|%g|%h|%i|%j|%l|%n|%o|%p|%q|%r|%s|%t|%u|%v|%w|%x|%y|%z|%I|%K|%M|%R|%S|%V|%W|%X|%Z"'))
		return false;
	$jobs_r = array();
 	$rlist = ftp_rawlist($conn_id, "sendq");
	
	if (is_array($rlist) && count($rlist)>0) {
		foreach ($rlist as $rlist_line) {
			$jobs_r[] = explode('|',$rlist_line);
		}
	}
	ftp_close($conn_id);
	return $jobs_r;
}


function fax_delete_file( $file, $user='', $pass='' )
{
	if ($user == '') {
		$user = gs_get_conf('GS_FAX_HYLAFAX_ADMIN');
		$pass = gs_get_conf('GS_FAX_HYLAFAX_PASS');
		if ($user == '') return false;
	}
	
	$conn_id = ftp_connect(
		gs_get_conf('GS_FAX_HYLAFAX_HOST'),
		gs_get_conf('GS_FAX_HYLAFAX_PORT'));
	if (! $conn_id) return false;
	
	$login_result = ftp_login($conn_id, $user, $pass);
	if (! $login_result) return false;
	
	if ($user == gs_get_conf('GS_FAX_HYLAFAX_ADMIN'))
		if (! ftp_raw($conn_id, 'admin '.$pass)) return false;
	
	$ret_val = ftp_delete($conn_id, $file);
	ftp_close($conn_id);
	return $ret_val;
}


function fax_delete_job( $job, $user='', $pass='' )
{
	if ($user == '') {
		$user = gs_get_conf('GS_FAX_HYLAFAX_ADMIN');
		$pass = gs_get_conf('GS_FAX_HYLAFAX_PASS');
		if ($user == '') return false;
	}
	
	$conn_id = ftp_connect(
		gs_get_conf('GS_FAX_HYLAFAX_HOST'),
		gs_get_conf('GS_FAX_HYLAFAX_PORT'));
	if (! $conn_id) return false;
	
	$login_result = ftp_login($conn_id, $user, $pass);
	if (! $login_result) return false;
	
	if ($user == gs_get_conf('GS_FAX_HYLAFAX_ADMIN'))
		if (! ftp_raw($conn_id, 'admin '.$pass)) return false;
	
	$ret_val = ftp_raw($conn_id, 'jdele '.$job);
	ftp_close($conn_id);
	return $ret_val;
}


function fax_kill_job( $job, $user='', $pass='')
{
	if ($user == '') {
		$user = gs_get_conf('GS_FAX_HYLAFAX_ADMIN');
		$pass = gs_get_conf('GS_FAX_HYLAFAX_PASS');
		if ($user == '') return false;
	}
	
	$conn_id = ftp_connect(
		gs_get_conf('GS_FAX_HYLAFAX_HOST'),
		gs_get_conf('GS_FAX_HYLAFAX_PORT'));
	if (! $conn_id) return false;
	
	$login_result = ftp_login($conn_id, $user, $pass);
	if (! $login_result) return false;
	
	if ($user == gs_get_conf('GS_FAX_HYLAFAX_ADMIN'))
		if (! ftp_raw($conn_id, 'admin '.$pass)) return false;
	
	$ret_val = ftp_raw($conn_id, 'jkill '.$job);
	ftp_close($conn_id);
	return $ret_val;
}


function fax_send( $user_id, $user, $to_num, $from_num, $file, $user_email, $resolution, $pass='' )
{
	if ($user == '') {
		$user = gs_get_conf('GS_FAX_HYLAFAX_ADMIN');
		$pass = gs_get_conf('GS_FAX_HYLAFAX_PASS');
		if ($user == '') return false;
	}
	
	if ($file) {
		$remote_file = dirname($file).'/doc-'.basename($file);
	} else return false;
	
	$conn_id = ftp_connect(
		gs_get_conf('GS_FAX_HYLAFAX_HOST'),
		gs_get_conf('GS_FAX_HYLAFAX_PORT'));
	if (! $conn_id) return false;
	
	if ($resolution < 98) $resolution = 98;
	
	$login_result = ftp_login($conn_id, $user, $pass);
	if (! $login_result) return false;
	
	$ret_val = ftp_raw($conn_id, 'jnew');
	$ret_par = explode(' ',$ret_val[0]);
	$jobid = (int)@$ret_par[array_search('jobid:',$ret_par)+1];
	
	if ($jobid) {
		$ret_val = @ftp_put($conn_id, $remote_file, $file, FTP_BINARY);
		$ret_val = ftp_raw($conn_id, 'job '.$jobid);
		$ret_val = ftp_raw($conn_id, 'jparm DIALSTRING '.$to_num);
		$ret_val = ftp_raw($conn_id, 'jparm TSI '.$from_num);
		$ret_val = ftp_raw($conn_id, 'jparm FAXNAME '.$user_id);
		$ret_val = ftp_raw($conn_id, 'jparm FAXNUMBER '.$from_num);
		$ret_val = ftp_raw($conn_id, 'jparm FROMUSER '.$user);
		$ret_val = ftp_raw($conn_id, 'jparm LASTTIME 000259');
		$ret_val = ftp_raw($conn_id, 'jparm NOTIFYADDR '.$user_email);
		$ret_val = ftp_raw($conn_id, 'jparm MAXDIALS 6');
		$ret_val = ftp_raw($conn_id, 'jparm MAXTRIES 6');
		$ret_val = ftp_raw($conn_id, 'jparm NOTIFY NONE');
		$ret_val = ftp_raw($conn_id, 'jparm PAGECHOP default');
		$ret_val = ftp_raw($conn_id, 'jparm PAGEWIDTH 209');
		$ret_val = ftp_raw($conn_id, 'jparm PAGELENGTH 296');
		$ret_val = ftp_raw($conn_id, 'jparm SCHEDPRI 127');;
		$ret_val = ftp_raw($conn_id, 'jparm SENDTIME NOW');
		$ret_val = ftp_raw($conn_id, 'jparm VRES '.$resolution);
		$ret_val = ftp_raw($conn_id, 'jparm DOCUMENT '.$remote_file);
		$ret_val = ftp_raw($conn_id, 'jsubm '.$jobid);
	}
	ftp_close($conn_id);
	return ($ret_val ? $jobid : false);
}


function fax_download( $file, $user='', $pass='' )
{
	if ($user == '') {
		$user = gs_get_conf('GS_FAX_HYLAFAX_ADMIN');
		$pass = gs_get_conf('GS_FAX_HYLAFAX_PASS');
		if ($user == '') return false;
	}
	
	$conn_id = ftp_connect(
		gs_get_conf('GS_FAX_HYLAFAX_HOST'),
		gs_get_conf('GS_FAX_HYLAFAX_PORT'));
	if (! $conn_id) return false;
	
	$login_result = ftp_login($conn_id, $user, $pass);
	if (! $login_result) return false;
	
	if ($user == gs_get_conf('GS_FAX_HYLAFAX_ADMIN'))
		if (! ftp_raw($conn_id, 'admin '.$pass)) return false;
	
	$ret_val = @ftp_get($conn_id, '/tmp/'.$file, 'recvq/'.$file, FTP_BINARY);
	
	ftp_close($conn_id);
	return $ret_val;
}


?>