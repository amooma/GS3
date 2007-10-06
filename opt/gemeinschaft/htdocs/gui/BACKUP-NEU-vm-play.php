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

define( 'GS_VALID', true );  /// this is a parent file
require_once( dirName(__FILE__) .'/../../inc/conf.php' );
require_once( GS_DIR .'htdocs/gui/inc/session.php' );
require_once( GS_DIR .'inc/get-listen-to-ids.php' );


function _not_allowed( $errmsg='' )
{
	@header( 'HTTP/1.0 403 Forbidden', true, 403 );
	@header( 'Status: 403 Forbidden' , true, 403 );
	@header( 'Content-Type: text/plain' );
	echo ($errmsg ? $errmsg : 'Not authorized.');
	exit(1);
}

function _server_error( $errmsg='' )
{
	@header( 'HTTP/1.0 500 Internal Server Error', true, 500 );
	@header( 'Status: 500 Internal Server Error' , true, 500 );
	@header( 'Content-Type: text/plain' );
	echo ($errmsg ? $errmsg : 'Internal Server Error.');
	exit(1);
}

function _not_found( $errmsg='' )
{
	@header( 'HTTP/1.0 404 Not Found', true, 404 );
	@header( 'Status: 404 Not Found' , true, 404 );
	@header( 'Content-Type: text/plain' );
	echo ($errmsg ? $errmsg : 'Not found.');
	exit(1);
}

function _to_id3tag_ascii( $str )
{
	return $str;
}

function _escapeShellArg( $str )
{
	# escapeShellArg() is broken! it returns an un-quoted empty string
	# for empty strings. that does not count as a shell argument!
	$ret = escapeShellArg($str);
	return (trim($ret) != '' ? $ret : '\'\'');
}


@header( 'Vary: *' );
@header( 'Cache-Control: private, must-revalidate' );


if (! is_array($_SESSION)
||  ! @array_key_exists('sudo_user', @$_SESSION)
||  ! @array_key_exists('info'     , @$_SESSION['sudo_user'])
||  ! @array_key_exists('id'       , @$_SESSION['sudo_user']['info']) )
{
	_not_allowed();
}

$user_id = (int)@$_SESSION['sudo_user']['info']['id'];
$ext     =      @$_SESSION['sudo_user']['info']['ext'];
$fld     = preg_replace('/[^a-z0-9\-_]/i', '', @$_REQUEST['fld']);
$file    = preg_replace('/[^a-z0-9\-_]/i', '', @$_REQUEST['msg']);

if ($ext == '') _not_allowed();


$rs = $DB->execute(
'SELECT
	`m`.`host_id`, `m`.`orig_time`, `m`.`dur`, `m`.`cidnum`, `m`.`cidname`, `m`.`listened_to`, `m`.`orig_mbox`,
	`h`.`host`
FROM
	`vm_msgs` `m` LEFT JOIN
	`hosts` `h` ON (`h`.`id`=`m`.`host_id`)
WHERE
	`m`.`user_id`=\''. $user_id .'\' AND
	`m`.`folder`=\''. $DB->escape($fld) .'\' AND
	`m`.`file`=\''. $DB->escape($file) .'\''
);
$info = $rs->fetchRow();
if (! $info) {
	_not_found();
}
if ($info['dur'] > 900) {  # 900 s = 15 min
	_server_error( 'File too long.' );
}

$our_host_ids = @gs_get_listen_to_ids();
if (! is_array($our_host_ids)) {
	_server_error( 'Failed to get our host IDs.' );
}



$vm_dir = '/var/spool/asterisk/voicemail/';
$origorigfile = $vm_dir .'default/'. $ext .'/'. $fld .'/'. $file .'.alaw';
$tmpfile_base = '/tmp/gs-vm-'. preg_replace('/[^0-9]/', '', $ext) .'-'. $fld .'-'. $file;


# delete files like /tmp/gs-vm-* with mtime < time()-30 minutes
#
//@exec( 'find \'/tmp/\' -maxdepth 1 -name \'gs-vm-*\' -type f -mmin +30 | xargs rm -f 1>>/dev/null 2>>/dev/null' );


# get file from remote host if necessary
#

if (! in_array($info['host_id'], $our_host_ids, true)) {
	# user is on a different host
	# copy the original file to this host:
	$origfile = $tmpfile_base.'.alaw';
	$cmd = 'sudo scp -o StrictHostKeyChecking=no -o BatchMode=yes '. _escapeShellArg('root@'. $host['host'] .':'. $origorigfile) .' '. _escapeShellArg($origfile);
	$err=0; $out=array();
	@exec( $cmd .' 1>>/dev/null 2>>/dev/null', $out, $err );
	if ($err != 0) _not_found( 'Could not get file from remote node.' );
} else {
	# user is on this host
	if (! file_exists($origorigfile)) _not_found( 'File not found on this node.' );
	$origfile = $origorigfile;
}


# convert file from original format (aLaw) to WAV (signed linear PCM,
# 8000 Hz sampling rate, 16 bits/sample), then to MP3
#

$id3_artist  = $info['cidnum'] . ($info['cidname'] != '' ? ' ('.$info['cidname'].')' : '');
$id3_album   = subStr(_to_id3tag_ascii( $ext         ),0,30);
$id3_title   = date('Y-m-d H:i', $info['orig_time']) .' - '. $id3_artist;
$id3_artist  = subStr(_to_id3tag_ascii( $id3_artist  ),0,30);
$id3_title   = subStr(_to_id3tag_ascii( $id3_title   ),0,30);
$id3_comment = '';
if ($info['orig_mbox'] != $ext
&&  $info['orig_mbox'] != '') {
	$id3_comment .= '<< '.$info['orig_mbox'];
}
$id3_comment = subStr(_to_id3tag_ascii( $id3_comment ),0,28);


$outfile = $tmpfile_base.'.mp3';
$cmd = 'sox -q -t al '. _escapeShellArg($origfile) .' -r 8000 -c 1 -s -w -t wav - 2>>/dev/null | lame --preset fast standard -m m -a -b 32 -B 96 --quiet --ignore-tag-errors --tt '. _escapeShellArg($id3_title) .' --ta '. _escapeShellArg($id3_artist) .' --tl '. _escapeShellArg($id3_album) .' --tc '. _escapeShellArg($id3_comment) .' --tg 101 - '. _escapeShellArg($outfile) .' 1>>/dev/null 2>>/dev/null';
//echo $cmd;die();
# (ID3 tag genre 101 = "Speech")
$err=0; $out=array();
@exec( $cmd, $out, $err );
if (! file_exists($outfile)) _not_found( 'Failed to convert file.'."\n".$cmd );


# the correct MIME type for "mp3" files is "audio/mpeg", see
# http://www.iana.org/assignments/media-types/audio/
# http://www.rfc-editor.org/rfc/rfc3003.txt
@header( 'Content-Type: audio/mpeg' );

$fake_filename = preg_replace('/[^0-9a-z\-_.]/i', '', 'vmsg_'. $ext .'_'. date('Ymd_Hi', $info['orig_time']) .'_'. subStr(md5(date('s', $info['orig_time']).$info['cidnum']),0,2) .'.mp3' );
@header( 'Content-Disposition: inline; filename='.$fake_filename );

# set Content-Length to prevent Apache(/PHP?) from using
# "Transfer-Encoding: chunked" which makes the sound file appear too
# short in QuickTime and maybe other players
@header( 'Transfer-Encoding: identity' );
@header( 'Content-Length: '. (int)@fileSize($outfile) );

@readFile( $outfile );


//@exec( 'sudo rm -rf '. _escapeShellArg($filename) .' 1>>/dev/null 2>&1' );
//@exec( 'sudo find \'/tmp/\' -maxdepth 1 -name \'gs-vm-*\' -type f -mmin +30 | xargs rm -f 1>>/dev/null 2>&1' );


?>