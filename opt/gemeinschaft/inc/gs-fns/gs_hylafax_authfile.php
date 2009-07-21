<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 0 $
* 
* Copyright 2009, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
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
include_once( GS_DIR .'inc/gs-lib.php' );


/***********************************************************
*    creates HylaFax user authentication file
***********************************************************/

function gs_hylafax_authfile_create( $authfile )
{
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get user list
	#
	$rs = $db->execute(
'SELECT `id`, `user`, `pin`
FROM `users`
WHERE `nobody_index` IS NULL
ORDER BY `id`'
	);
	if (! $rs)
		return new GsError( 'Error.' );
	
	# create temporary hylafax host/user authentication file
	#
	if (file_exists($authfile) && (! is_writable($authfile))) {
		@exec( 'sudo rm -f '. qsa($authfile) .' 2>>/dev/null' );
	}
	
	$fh = @fOpen($authfile,'w');
	if (! $fh) {
		return new GsError( 'Failed to open HylaFax authfile.' );
	}
	
	# create admin entry first
	#
	if (gs_get_conf('GS_FAX_HYLAFAX_ADMIN') != '') {
		$crypted = crypt(gs_get_conf('GS_FAX_HYLAFAX_PASS' ), 'pF');
		$user_entry = gs_get_conf('GS_FAX_HYLAFAX_ADMIN') .'@:'.'0'.':'.$crypted.':'.$crypted ."\n";
		fWrite($fh, $user_entry, strLen($user_entry));
	}
	
	# create user entries
	#
	while ($user = $rs->fetchRow()) {
		$crypted = crypt($user['pin'], 'ml');
		$user_entry = $user['user'].'@:'.$user['id'].':'.$crypted ."\n";
		fWrite($fh, $user_entry, strLen($user_entry));
	}
	
	# close file
	#
	if (@fclose($fh)) return true;
	else return new GsError( 'Error.' );
}


/***********************************************************
*    pushes HylaFax user authentication file to server
***********************************************************/

function gs_hylafax_authfile_put( $authfile )
{
	# connect to fax server
	#
	$con_id = @ftp_connect(gs_get_conf('GS_FAX_HYLAFAX_HOST'), gs_get_conf('GS_FAX_HYLAFAX_PORT'), gs_get_conf('GS_FAX_HYLAFAX_TIMEOUT', 20));
	if (! $con_id) {
		return new GsError( 'Failed to connect to HylaFax server.' );
	}
	
	# log in as admin user
	#
	if (! @ftp_login($con_id, gs_get_conf('GS_FAX_HYLAFAX_ADMIN'), gs_get_conf('GS_FAX_HYLAFAX_PASS' ))) {
		return new GsError( 'Failed to log in at HylaFax server.' );
	}
	
	# go into admin mode
	#
	if (! @ftp_raw($con_id, 'admin '.gs_get_conf('GS_FAX_HYLAFAX_PASS' ))) {
		return new GsError( 'Failed to switch to admin mode at HylaFax server.' );
	}
	
	# put the local authfile to fax server
	#
	# HylaFax convention: absolute path, relative to Hylafax dir (/var/spool/hylafax/)
	if (! @ftp_put($con_id, '/etc/hosts.hfaxd', $authfile, FTP_BINARY)) {
		return new GsError( 'Failed to push authfile to HylaFax server.' );
	}
	
	# close connection
	#
	if (@ftp_close($con_id)) return true;
	else return new GsError( 'Error.' );
}


/***********************************************************
*    creates HylaFax user authentication file and copies it
*    to the fax server.
***********************************************************/

function gs_hylafax_authfile_sync( $authfile = '' )
{
	# It will be assumed that the server is accessible, that the file
	# exists and that the admin account exists otherwise the
	# connection will fail.
	# There is a fallback mechanism which copies the file instead of
	# FTP put'ting it if the fax server is running on the local machine.
	
	if (! $authfile) $authfile = '/tmp/gs-hylafax-hosts.hfaxd-'.rand(100000,999999);
	
	# create authfile locally
	#
	$result = gs_hylafax_authfile_create( $authfile );
	if ($result !== true) {
		clearStatCache();
		if (file_exists($authfile)) {
			@exec( 'sudo rm -f '. qsa($authfile) .' 2>>/dev/null' );
		}
		return $result;
	}
	
	# put authfile to the fax server
	#
	$ret = gs_hylafax_authfile_put( $authfile );
	if ($ret !== true) {
		# if ftp put fails, try to copy it locally.
		//FIXME: Will fail if the fax host is not "127.0.0.1" or "localhost"
		#
		if ((gs_get_conf('GS_FAX_HYLAFAX_HOST') === '127.0.0.1')
		||  (gs_get_conf('GS_FAX_HYLAFAX_HOST') === 'localhost')) {
			$authfile_dst =
				gs_get_conf('GS_FAX_HYLAFAX_PATH', '/var/spool/hylafax/').
				'etc/hosts.hfaxd';
			
			$err=0; $out=array();
			@exec( 'sudo mv '. qsa($authfile) .' '. qsa($authfile_dst) .' 2>>/dev/null', $out, $err );
			if ($err !== 0) {
				@exec( 'sudo rm -f '. qsa($authfile) .' 2>>/dev/null' );
				return new GsError( 'Error updating fax authentication on localhost.' );
			}
			
			$err=0; $out=array();
			@exec( 'sudo chown '. qsa(gs_get_conf('GS_FAX_HYLAFAX_USER', 'uucp')) .' '. qsa($authfile_dst) .' 2>>/dev/null', $out, $err );
			if ($err != 0) {
				@exec( 'sudo rm -f '. qsa($authfile) .' 2>>/dev/null' );
				return new GsError( 'Error updating fax authentication on localhost.' );
			}
			
			$err=0; $out=array();
			@exec( 'sudo chmod '. '0600' .' '. qsa($authfile_dst) .' 2>>/dev/null', $out, $err );
			if ($err !== 0) {
				@exec( 'sudo rm -f '. qsa($authfile) .' 2>>/dev/null' );
				return new GsError( 'Error updating fax authentication on localhost.' );
			}
			
			$ret = true;
			@exec( 'sudo rm -f '. qsa($authfile) .' 2>>/dev/null' );
			
			if (@is_dir('/etc/hylafax')) {  # Debian
				@exec( 'sudo cp '. qsa($authfile_dst) .' '. qsa('/etc/hylafax/hosts.hfaxd') .' 2>>/dev/null' );
			}
		}
	}
	clearStatCache();
	if (file_exists($authfile)) {
		@exec( 'sudo rm -f '. qsa($authfile) .' 2>>/dev/null' );
	}
	return $ret;
}

?>