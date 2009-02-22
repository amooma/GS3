<?php


/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2009, AMOOMA GmbH, Bachstr. 126, 56566 Neuwied, Germany,
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
*    creates hylafax user authentification file
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

	# create temporary hylafax host/user athentification file
	#
	$fh = @fOpen($authfile,'w');
	if (!$fh) {
		return new GsError( 'Error.' );
	}

	# create admin entry first
	#
	if (gs_get_conf('GS_FAX_HYLAFAX_ADMIN') != '') {
		$crypted = crypt(gs_get_conf('GS_FAX_HYLAFAX_PASS' ), 'pF');
		$user_entry =gs_get_conf('GS_FAX_HYLAFAX_ADMIN') .'@:'.'0'.':'.$crypted.':'.$crypted ."\n";
		fWrite($fh, $user_entry, strLen($user_entry));
	}

	# create user entries
	#
	while ($user = $rs->fetchRow()) {
		$crypted = crypt($user['pin'], 'ml');
		$user_entry = $user['user'].'@:'.$user['id'].':'.$crypted ."\n";
		fWrite($fh, $user_entry, strLen($user_entry));
	}

	#close file
	#
	if (fclose($fh)) return TRUE;
	else return new GsError( 'Error.' );
}

function gs_hylafax_authfile_put( $authfile )
{
	# connect to fax server
	#
	$con_id = @ftp_connect(gs_get_conf('GS_FAX_HYLAFAX_HOST'), gs_get_conf('GS_FAX_HYLAFAX_PORT'), gs_get_conf('GS_FAX_HYLAFAX_TIMEOUT', 20));

	if (! $con_id) {
		return new GsError( 'Error.' );
	}

	# log in as admin user.
	#
	if (! @ftp_login($con_id, gs_get_conf('GS_FAX_HYLAFAX_ADMIN'), gs_get_conf('GS_FAX_HYLAFAX_PASS' ))) {
		return new GsError( 'Error.' );
	}

	# go into admin mode
	#
	if (! @ftp_raw($con_id, 'admin '.gs_get_conf('GS_FAX_HYLAFAX_PASS' ))) {
		return new GsError( 'Error.' );
	}

	# put the local authfile to fax server
	#
	if (! @ftp_put($con_id, gs_get_conf('GS_FAX_HYLAFAX_AUTHFILE', '/etc/hosts.hfaxd'), $authfile, FTP_BINARY)) {
		return new GsError( 'Error.' );
	}

	# close connection
	#
	if (@ftp_close($con_id)) { 
		return TRUE;
	} else return new GsError( 'Error.' );
}

function gs_hylafax_authfile_sync( $authfile = '' )
{
	# This function creates and copies hylafax authentification file to the fax server.
	# It will be assumed that the server is accessible, the file exists and the admin account ist present
	# otherwise the connection will fail. There is a fallback mechanism which copies the file if the fax server
	# is running on the local machine.
	
	if (!$authfile) $authfile = gs_get_conf('GS_FAX_HYLAFAX_TMPAUTHFILE', '/tmp/gs-hylafax-hosts.hfaxd');

	# create authfile locally
	# 
	$res = gs_hylafax_authfile_create( $authfile );

	if ($res !== TRUE) {
		return $res;
	}

	# put authfile to the fax server
	#
	$res = gs_hylafax_authfile_put( $authfile );	

	if ($res !== TRUE) {
		# if ftp put fails, try to copy it locally. 
		# FIXME: Will fail if the fax host is not "127.0.0.1" or "localhost"
		# 
		if ((gs_get_conf('GS_FAX_HYLAFAX_HOST') == '127.0.0.1') || (gs_get_conf('GS_FAX_HYLAFAX_HOST') == 'localhost')) {
			$authfile_dst = gs_get_conf('GS_FAX_HYLAFAX_PATH','/var/spool/hylafax/').
					gs_get_conf('GS_FAX_HYLAFAX_AUTHFILE', '/etc/hosts.hfaxd');

			$res = copy($authfile, $authfile_dst);
			
			if ($res !== TRUE) return new GsError( 'Error.' );

			$res = chown($authfile_dst, gs_get_conf('GS_FAX_HYLAFAX_USER', 'uucp'));

			if ($res !== TRUE) return new GsError( 'Error.' );

			$res = chmod ($authfile_dst, 0600);

			if ($res !== TRUE) return new GsError( 'Error.' );

		}
		return $res;
	}

	return TRUE;
}
?>