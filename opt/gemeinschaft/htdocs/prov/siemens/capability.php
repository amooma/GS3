<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 2585 $
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
require_once( GS_DIR .'inc/phone-capability.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );


class PhoneCapability_siemens extends PhoneCapability
{
	var $ringtones = array
	(
		# Bellcore-dr (original):
		#
		 1 => array('settings'=>'Ringer%s', 'alertinfo'=>'Bellcore-dr%s'),
		 2 => array('settings'=>'Ringer%s', 'alertinfo'=>'Bellcore-dr%s'),
		 3 => array('settings'=>'Ringer%s', 'alertinfo'=>'Bellcore-dr%s'),
		 4 => array('settings'=>'Ringer%s', 'alertinfo'=>'Bellcore-dr%s'),
		 5 => array('settings'=>'Ringer%s', 'alertinfo'=>'Bellcore-dr%s'),
		
		# Bellcore-dr (other):
		#
		 6 => array('settings'=>'Ringer%s', 'alertinfo'=>'Bellcore-dr%s'),
		 7 => array('settings'=>'Ringer%s', 'alertinfo'=>'Bellcore-dr%s'),
		 8 => array('settings'=>'Ringer%s', 'alertinfo'=>'Bellcore-dr%s'),
		 9 => array('settings'=>'Ringer%s', 'alertinfo'=>'Bellcore-dr%s'),
		10 => array('settings'=>'Ringer%s', 'alertinfo'=>'Bellcore-dr%s'),
		
		# Silent:
		 0 => array('settings'=>'Silent'  , 'alertinfo'=>'Silent'       ),
		
		# Custom:
		-1 => array('settings'=>false     , 'alertinfo'=>true           )
	);
	
	function conv_ringtone( $infile, $outbase )
	{
		if(fileSize($infile) >= 1000000 )
			return new GsError('Datei zu Gross');

		/*
		$outfile = $outbase .'.wav';
		
		/*
		if     (is_executable( '/usr/local/bin/mpg123' ))
			$mpg123 = '/usr/local/bin/mpg123';
		elseif (is_executable( '/usr/bin/mpg123' ))
			$mpg123 = '/usr/bin/mpg123';
		elseif (is_executable( '/bin/mpg123' ))
			$mpg123 = '/bin/mpg123';
		else
			$mpg123 = 'mpg123';
		
		if (strToLower(subStr($infile, -4, 4)) === '.mp3') {
			# convert mp3 to wav first
			$wavfile = $infile .'.wav';
			$cmd = $mpg123 .' -m -2 - -n 1000 -q '. qsa($infile) .' > '. qsa($wavfile) .' 2>>/dev/null';
			# cuts file after 1000 frames (around 2.3 MB, depending on the rate)
			# don't use -r 8000 as that doesn't really work for VBR encoded MP3s
			@exec($cmd, $out, $err);
			if ($err != 0) {
				if (is_file($wavfile)) @unlink( $wavfile );
				return false;
			}
			$infile = $wavfile;
			$rm_tmp = $wavfile;
		} else
			$rm_tmp = false;
		
		$cmd = 'sox '. qsa($infile) .' -c 1 -2 '. qsa($outfile) .' rate 8000 trim 0 125000s 2>>/dev/null';
		# WAV, PCM, 8 kHz, 16 bit, mono
		# "The time for loading the file should not be longer then 3 seconds.
		# Size < 250 KByte."
		# cuts file after 125000 samples (around 245 kB, 15 secs)
		@exec($cmd, $out, $err);
		if ($err != 0) {
			# $err == 2 would be unknown format
			if (is_file($outfile)) @unlink( $outfile );
			if ($rm_tmp && is_file($rm_tmp)) @unlink($rm_tmp);
			return false;
		}
		return $outfile;
		*/
		return $infile;
	}
	
	function _upload_ringtone( $ringtonefile )  # deprecated
	{
		$file = '/opt/gemeinschaft-siemens/conf.php';
		
		if (file_exists($file) && is_readable($file)) {
			include_once($file);
		} else {
			gs_log( GS_LOG_NOTICE, "Siemens provisioning not available" );
			return false;
		}
		
		$fileserver['wan'  ] = gs_get_conf('GS_PROV_SIEMENS_FTP_SERVER_WAN');
		$fileserver['lan'  ] = gs_get_conf('GS_PROV_SIEMENS_FTP_SERVER_LAN');
		//$fileserver['local'] = gs_get_conf('GS_PROV_HOST');
		$ftp_path = '';
		
		$external_ftp_path = gs_get_conf('GS_PROV_SIEMENS_FTP_RINGTONE_PATH');
		if ($external_ftp_path === null) {
			$external_ftp_path = '/';
		}
		
		include_once( GS_DIR .'inc/ftp-filesize.php' );
		$ftp = new GS_FTP_FileSize();
		
		foreach ($fileserver as $file_server) {
			if ($file_server == '')
				continue;
			if (!($ftp->connect($file_server, null,
				gs_get_conf('GS_PROV_SIEMENS_FTP_USER'),
				gs_get_conf('GS_PROV_SIEMENS_FTP_PWD')
			))) {
				gs_log( GS_LOG_WARNING, 'Siemens prov.: Can\'t upload '.$ringtonefile.' file to '.$file_server.' (FTP server failed)' );
			} else {
				$ok = $ftp->upload_file( $ringtonefile, $external_ftp_path );
				$ftp->disconnect();
				if (! $ok) {
					gs_log( GS_LOG_WARNING, 'Failed to copy ringtone to FTP server' );
				}
		 	}
		}
		return true;
	}
}

?>
