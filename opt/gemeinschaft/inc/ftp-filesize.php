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


class GS_FTP_FileSize
{
	var $_host;
	var $_port = null;
	
	var $_conn = null;
	var $_connected = false;
	var $_conn_failed = false;
	
	# constructor for PHP 4
	function GS_FTP_FileSize()
	{
	}
	
	function connect( $host, $port=null, $user='', $pass='' )
	{
		$this->disconnect();
		
		$this->_host = $host;
		$this->_port = $port;
		if (in_array($user, array('', null, false), true)) {
			$user = 'anonymous';
			$pass = 'gemeinschaft@gemeinschaft.local';
		}
		$this->_user = $user;
		$this->_pass = $pass;
		
		if (! extension_loaded('ftp')) {
			gs_log( GS_LOG_WARNING, 'ftp extension for PHP not available' );
			$this->_conn_failed = true;
			return false;
		}
		if (! preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $this->_host)) {
			$ips = getHostByNameL( $this->_host );
			# for some strange reason this is a lot faster than to
			# leave the lookup up to ftp_connect()
			if (! is_array($ips) || count($ips) < 1) {
				gs_log( GS_LOG_WARNING, 'Failed to resolve "'. $this->_host .'"' );
				$this->_conn_failed = true;
				return false;
			}
			$this->_host = $ips[0];
		}
		$this->_conn = ftp_connect( $this->_host, $this->_port, 4 );
		if (! $this->_conn) {
			gs_log( GS_LOG_NOTICE, 'Failed to connect to ftp://'. $this->_host );
			$this->_conn_failed = true;
			return false;
		}
		@ftp_set_option( $this->_conn, FTP_TIMEOUT_SEC, 3 );
		if (! @ftp_login( $this->_conn, $this->_user, $this->_pass )) {
			gs_log( GS_LOG_NOTICE, 'Failed to log in at ftp://'. $this->_host );
			$this->_conn_failed = true;
			@ftp_close( $this->_conn );
			return false;
		}
		$this->_connected = true;
		return true;
	}
	
	function disconnect()
	{
		if ($this->_conn) {
			@ftp_close( $this->_conn );
		}
		$this->_conn = null;
		$this->_connected = false;
		$this->_conn_failed = false;
	}
	
	# returns the size of a file or -1 on error
	#
	function file_size( $file )
	{
		if (! $this->_connected) {
			return -1;
		}
		
		$chdir = false;
		if ($chdir) {
			$orig_dir = @ftp_pwd( $this->_conn );
			$dir = dirName($file);
			if (! @ftp_chdir( $this->_conn, $dir )) {
				gs_log( GS_LOG_NOTICE, 'FTP: Could not change directory to "'.$dir.'"' );
				return -1;
			}
		}
		
		# the SIZE command is not supported by all FTP servers, but
		# the Siemens OpenStage does that as well
		if (function_exists('ftp_raw')) {
			$file_size = -1;
			@ftp_raw( $this->_conn, 'TYPE I' );  # binary mode
			$out = @ftp_raw( $this->_conn, 'SIZE '. ($chdir ? baseName($file) : $file) );
			if (@count($out) < 1) {
				gs_log( GS_LOG_NOTICE, 'FTP server '. $this->_host .' did not answer the SIZE command' );
			}
			else {
				$reply_code = (int)subStr($out[0],0,3);
				switch ($reply_code) {
					case 213:
						$file_size = (int)subStr($out[0],4);
						break;
					case 500:
					case 501:
					case 502:
					case 503:
					case 504:
						gs_log( GS_LOG_NOTICE, 'FTP server '. $this->_host .' does not understand the SIZE command ('. $out[0] .')' );
						break;
					case 550:
					case 553:
						gs_log( GS_LOG_DEBUG, 'File '. $file .' not available on ftp://'. $this->_host .' ('. $out[0] .')' );
						break;
					default:
						gs_log( GS_LOG_NOTICE, 'FTP server '. $this->_host .' returned: '. $out[0] );
				}
			}
		} else {
			$file_size = @ftp_size( $this->_conn, ($chdir ? baseName($file) : $file) );
			if ($file_size < 0) {
				gs_log( GS_LOG_NOTICE, 'Could not get size of file '. $file .' on ftp://'. $this->_host );
			}
		}
		
		if ($chdir) {
			# change the directory back to the original directory
			@ftp_chdir( $this->_conn, $orig_dir );
		}
		return $file_size;
	}
	
	# upload a file
	#
	function upload_file( $local_file, $destdir )
	{
		if (! $this->_connected) return false;
		if (subStr($destdir,-1) !== '/') $destdir.= '/';
		gs_log( GS_LOG_DEBUG, 'Trying to upload '. $local_file.' to '. $destdir . baseName($local_file) .' on ftp://'. $this->_host );
		$ok = @ftp_put( $this->_conn, $destdir.baseName($local_file), $local_file, FTP_BINARY );
		return $ok;
	}
	
}


?>