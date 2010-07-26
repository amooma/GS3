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
require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'htdocs/gui/inc/session.php' );
require_once( GS_DIR .'inc/get-listen-to-ids.php' );
require_once( GS_DIR .'inc/log.php' );
require_once( GS_DIR .'inc/find_executable.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );
require_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );


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

function _not_modified( $etag='', $attach=false, $fake_filename='' )
{
	header( 'HTTP/1.0 304 Not Modified', true, 304 );
	header( 'Status: 304 Not Modified', true, 304 );
	if (! empty($etag))
		header( 'ETag: '. $etag );
	if (! empty($fake_filename))
		header( 'Content-Disposition: '.($attach ? 'attachment':'inline').'; filename="'.$fake_filename.'"' );
	exit(0);
}

function _to_id3tag_ascii( $str )
{
	return preg_replace(
		'/[^\x20-\x5D\x5F-\x7E]/', '',  # remove "^"
		gs_utf8_decompose_to_ascii( $str ));
}


$formats = array( # internal name to info
	'wav-pcma' => array( 'title'=>'aLaw', 'ext'=>'alaw.wav', 'mime'=>'audio/x-wav'  ),
	//'pcma'     => array( 'title'=>'aLaw', 'ext'=>'al'      , 'mime'=>'audio/PCMA'   ),  # RFC 4856
	'mp3'      => array( 'title'=>'MP3' , 'ext'=>'mp3'     , 'mime'=>'audio/mpeg'   ),  # RFC 3003
	'sun-pcmu' => array( 'title'=>'Au'  , 'ext'=>'au'      , 'mime'=>'audio/basic'  ),  # RFC 2046
	'wav-pcm'  => array( 'title'=>'sLin', 'ext'=>'slin.wav', 'mime'=>'audio/x-wav'  ),
);
# For MIME types see http://www.iana.org/assignments/media-types/audio/
# Keep in sync with mod/voicemail_messages.php


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
$fmt     = preg_replace('/[^a-z0-9\-_]/i', '', @$_REQUEST['fmt']);
if (! array_key_exists($fmt, $formats)) {
	_server_error( 'Unknown format requested.' );
}
$attach  = (@$_REQUEST['disp'] === 'attach');


if ($ext == '') _not_allowed();


if (in_array($fmt, array('mp3', 'wav-pcm', 'sun-pcmu'), true)) {
	$sox  = find_executable('sox', array(
		'/usr/bin/', '/usr/local/bin/', '/usr/sbin/', '/usr/local/sbin/' ));
	if (! $sox) {
		gs_log( GS_LOG_WARNING, 'sox - command not found.' );
		_server_error( 'Failed to convert file.' );
	}
} else {
	$sox = '/bin/false';
}
if (in_array($fmt, array('mp3'), true)) {
	$lame = find_executable('lame', array(
		'/usr/local/bin/', '/usr/bin/', '/usr/local/sbin/', '/usr/sbin/' ));
	if (! $lame) {
		gs_log( GS_LOG_WARNING, 'lame - command not found.' );
		_server_error( 'Failed to convert file.' );
	}
} else {
	$lame = '/bin/false';
}


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

$etag = gmDate('Ymd') .'-'. md5( $user_id .'-'. $fld .'-'. $file .'-'. $info['host_id'] .'-'. $info['orig_time'] .'-'. $info['dur'] .'-'. $info['cidnum'] ) .'-'. $fmt;
$fake_filename = preg_replace('/[^0-9a-z\-_.]/i', '', 'vm_'. $ext .'_'. date('Ymd_Hi', $info['orig_time']) .'_'. subStr(md5(date('s', $info['orig_time']).$info['cidnum']),0,4) .'.'. $formats[$fmt]['ext'] );
if (array_key_exists('HTTP_IF_NONE_MATCH', $_SERVER)
&&  $_SERVER['HTTP_IF_NONE_MATCH'] === $etag) {
	_not_modified( $etag, $attach, $fake_filename );
}

if ($info['dur'] > 900) {  # 900 s = 15 min
	gs_log( GS_LOG_NOTICE, 'Voicemail too long for web.' );
	_server_error( 'File too long.' );
}

if (! gs_get_conf('GS_INSTALLATION_TYPE_SINGLE')) {
	$our_host_ids = @gs_get_listen_to_ids();
	if (! is_array($our_host_ids)) {
		gs_log( GS_LOG_WARNING, 'Failed to get our host IDs.' );
		_server_error( 'Failed to get our host IDs.' );
	}
	$vmmsg_is_on_this_host = in_array($info['host_id'], $our_host_ids, true);
} else {
	$vmmsg_is_on_this_host = true;
}



$vm_dir = '/var/spool/asterisk/voicemail/';
$origorigfile = $vm_dir .'default/'. $ext .'/'. $fld .'/'. $file .'.alaw';
$tmpfile_base = '/tmp/gs-vm-'. preg_replace('/[^0-9]/', '', $ext) .'-'. $fld .'-'. $file;


# delete files like /tmp/gs-vm-* with mtime < time()-10 minutes
#
@exec( 'find \'/tmp/\' -maxdepth 1 -name \'gs-vm-*\' -type f -mmin +10 | xargs rm -f 1>>/dev/null 2>>/dev/null' );


# get file from remote host if necessary
#

if (! $vmmsg_is_on_this_host) {
	# user is on a different host
	# copy the original file to this host:
	$origfile = $tmpfile_base.'.alaw';
	$cmd = 'sudo scp -o StrictHostKeyChecking=no -o BatchMode=yes '. qsa('root@'. $info['host'] .':'. $origorigfile) .' '. qsa($origfile);
	$err=0; $out=array();
	@exec( $cmd .' 1>>/dev/null 2>>/dev/null', $out, $err );
	if ($err != 0) {
		gs_log( GS_LOG_WARNING, "Could not get voicemail \"$origorigfile\" from node \"".$info['host']."\"." );
		_not_found( 'Could not get file from remote node.' );
	}
} else {
	# user is on this host
	if (! file_exists($origorigfile)) {
		gs_log( GS_LOG_WARNING, "Voicemail \"$origorigfile\" not found on this node." );
		_not_found( 'File not found on this node.' );
	}
	$origfile = $origorigfile;
}

$err=0; $out=array();
@exec( 'sudo chmod a+r '. qsa($origfile) .' 1>>/dev/null 2>>/dev/null', $out, $err );
if ($err != 0) {
	gs_log( GS_LOG_WARNING, 'Can\'t read \"$origfile\".' );
	_server_error( 'Failed to convert file.' );
}


error_reporting(0);
ini_set('display_errors', false);
@set_time_limit(10);


$outfile = $tmpfile_base .'.'. $formats[$fmt]['ext'];

if ($fmt === 'mp3') {
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
	
	$cmd = $sox.' -q -t al '. qsa($origfile) .' -r 8000 -c 1 -s -b 16 -t wav - 2>>/dev/null | '.$lame.' --preset fast standard -m m -a -b 32 -B 96 --quiet --ignore-tag-errors --tt '. qsa($id3_title) .' --ta '. qsa($id3_artist) .' --tl '. qsa($id3_album) .' --tc '. qsa($id3_comment) .' --tg 101 - '. qsa($outfile) .' 2>&1 1>>/dev/null';
	# (ID3 tag genre 101 = "Speech")
	$err=0; $out=array();
	@exec( $cmd, $out, $err );
	if ($err != 0) {
		gs_log( GS_LOG_WARNING, 'Failed to convert voicemail file to '.$fmt.'. ('.trim(implode(' - ',$out)).')' );
		_server_error( 'Failed to convert file.' );
	}
}
elseif ($fmt === 'pcma') {
	# not supported by any players
	
	# nothing to do
	# $origfile == $tmpfile_base.'.alaw';
	$outfile = $origfile;
}
elseif ($fmt === 'wav-pcma') {
	# A-law logarithmic PCM in WAVE container
	# same format as "alaw" in Asterisk, plus an additional file header
	# => no conversion required
	
	/*
	$cmd = $sox.' -q -t al '. qsa($origfile) .' -r 8000 -c 1 -A -b -t wav '. qsa($outfile) .' 1>>/dev/null';
	gs_log( GS_LOG_DEBUG, $cmd);
	$err=0; $out=array();
	@exec( $cmd, $out, $err );
	if ($err != 0) {
		gs_log( GS_LOG_WARNING, 'Failed to convert voicemail file to '.$fmt.'. ('.trim(implode(' - ',$out)).')' );
		_server_error( 'Failed to convert file.' );
	}
	*/
	
	# http://de.wikipedia.org/wiki/RIFF_WAVE#Beispiel_eines_allgemein_lesbaren_WAVE-Dateiformates
	# (RIFF is little-endian!)
	$dlen = (int)@fileSize($origfile);  # 90400
	$wav_alaw_header
		='RIFF'              # chunk ID
		.pack('V', 50+$dlen) # chunk size = file size - 8 = 50+$dlen
		.'WAVE'              # chunk format = WAVE
		
		.'fmt '              # header sub-chunk ID
		.pack('V',    18)    # header sub-chunk length = 18
		.pack('v',     6)    # data format = 6 (Alaw)
		.pack('v',     1)    # channels = 1
		.pack('V',  8000)    # sample rate = 8000
		.pack('V',  8000)    # bytes/second = sample rate * channels * bits/sample / 8 = 8000*1*1 = 8000
		.pack('v',     1)    # block align = channels * bits/sample / 8 = 1*8/8 = 1
		.pack('v',     8)    # bits/sample = 8
		.pack('v',     0)    # optional extra-param size
		//.''                  # optional extra-params
		
		.'fact'              # fact sub-chunk ID
		.pack('V',     4)    # fact sub-chunk length = 4
		.pack('V', $dlen)    # data length = $dlen
		
		.'data'              # data sub-chunk ID
		.pack('V', $dlen)    # data sub-chunk length = $dlen
		;
	
	$outfile = $origfile;  # to soothe the file_exists() check
}
elseif ($fmt === 'sun-pcmu') {
	$cmd = $sox.' -q -t al '. qsa($origfile) .' -r 8000 -c 1 -U -b 8 -t au '. qsa($outfile) .' 1>>/dev/null';
	$err=0; $out=array();
	@exec( $cmd, $out, $err );
	if ($err != 0) {
		gs_log( GS_LOG_WARNING, 'Failed to convert voicemail file to '.$fmt.'. ('.trim(implode(' - ',$out)).')' );
		_server_error( 'Failed to convert file.' );
	}
}
elseif ($fmt === 'wav-pcm') {
	# signed linear PCM in WAVE container
	$cmd = $sox.' -q -t al '. qsa($origfile) .' -r 8000 -c 1 -s -b 16 -t wav '. qsa($outfile) .' 1>>/dev/null';
	$err=0; $out=array();
	@exec( $cmd, $out, $err );
	if ($err != 0) {
		gs_log( GS_LOG_WARNING, 'Failed to convert voicemail file to '.$fmt.'. ('.trim(implode(' - ',$out)).')' );
		_server_error( 'Failed to convert file.' );
	}
}

if (! file_exists($outfile)) {
	gs_log( GS_LOG_WARNING, 'Failed to convert voicemail file.' );
	_server_error( 'Failed to convert file.' );
}


/*
$intermedfile = $tmpfile_base.'.sln.wav';

$cmd = 'sox -t al '. qsa($origfile) .' -r 8000 -c 1 -s -w -t wav '. qsa($intermedfile);
$err=0; $out=array();
@exec( $cmd, $out, $err );
if ($err != 0) {
	gs_log( GS_LOG_WARNING, 'Failed to convert voicemail file.' );
	_server_error( 'Failed to convert file.' );
}
if (! file_exists($intermedfile)) {
	gs_log( GS_LOG_WARNING, 'Failed to convert voicemail file.' );
	_server_error( 'Failed to convert file.' );
}

$cmd = 'lame --preset fast standard -m m -a -b 32 -B 96 --quiet --ignore-tag-errors --tt '. qsa($id3_title) .' --ta '. qsa($id3_artist) .' --tl '. qsa($id3_album) .' --tc '. qsa($id3_comment) .' --tg 101 '. qsa($intermedfile) .' '. qsa($outfile);
$err=0; $out=array();
@exec( $cmd, $out, $err );
if ($err != 0) {
	gs_log( GS_LOG_WARNING, 'Failed to convert voicemail file.' );
	_server_error( 'Failed to convert file.' );
}
if (! file_exists($outfile)) {
	gs_log( GS_LOG_WARNING, 'Failed to convert voicemail file.' );
	_server_error( 'Failed to convert file.' );
}
*/



@header( 'Content-Type: '. $formats[$fmt]['mime'] );
$fake_filename = preg_replace('/[^0-9a-z\-_.]/i', '', 'vm_'. $ext .'_'. date('Ymd_Hi', $info['orig_time']) .'_'. subStr(md5(date('s', $info['orig_time']).$info['cidnum']),0,4) .'.'. $formats[$fmt]['ext'] );
@header( 'Content-Disposition: '.($attach ? 'attachment':'inline').'; filename="'.$fake_filename.'"' );
@header( 'ETag: '. $etag );

# set Content-Length to prevent Apache(/PHP?) from using
# "Transfer-Encoding: chunked" which makes the sound file appear too
# short in QuickTime and maybe other players
@header( 'Transfer-Encoding: identity' );

if ($fmt === 'wav-pcma') {
	@header( 'Content-Length: '. ((int)strLen($wav_alaw_header) + (int)@fileSize($origfile)) );
	echo $wav_alaw_header;
	@readFile( $origfile );
}
else {
	@header( 'Content-Length: '. (int)@fileSize($outfile) );
	@readFile( $outfile );
}



@ob_start();  # so there's no output after the content

if (! @$info['listened_to']) {
	@$DB->execute(
'UPDATE `vm_msgs` SET `listened_to`=1
WHERE
	`user_id`=\''. $user_id .'\' AND
	`folder`=\''. $DB->escape($fld) .'\' AND
	`file`=\''. $DB->escape($file) .'\''
	);
}

//@exec( 'sudo rm -rf '. qsa($outfile) .' 1>>/dev/null 2>>/dev/null' );

@ob_clean();

?>