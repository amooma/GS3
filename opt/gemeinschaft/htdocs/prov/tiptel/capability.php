<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 1 $
* 
* Copyright 2010, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Sebastian Ertz <gemeinschaft@swastel.eisfair.net>
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


class PhoneCapability_tiptel extends PhoneCapability
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
		$outfile = $outbase .'.wav';
		
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
			$cmd = $mpg123 .' -m -w - -n 1000 -q '. qsa($infile) .' > '. qsa($wavfile) .' 2>>/dev/null';
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
		
		$cmd = 'sox '. qsa($infile) .' -c 1 -U '. qsa($outfile) .' rate 8000 trim 0 200000s 2>>/dev/null';
		# WAV, uLaw, 8 kHz, 16 bit, mono
		# "The time for loading the file should not be longer then 3 seconds.
		# Size < 200 KByte."
		# cuts file after 200000 samples (around 200 kB)
		@exec($cmd, $out, $err);
		if ($err != 0) {
			# $err == 2 would be unknown format
			if (is_file($outfile)) @unlink( $outfile );
			if ($rm_tmp && is_file($rm_tmp)) @unlink($rm_tmp);
			return false;
		}
		return $outfile;
		
		//return false;
		//return null;  # not implemented
	}

	function get_firmware_files()
	{
		$firmware_files = glob( GS_DIR .'htdocs/prov/tiptel/fw/*.rom' );
		if (! is_array($firmware_files)) return null;
		
		for($i=0; $i<count($firmware_files); ++$i) {
			$firmware_files[$i] = baseName($firmware_files[$i]);
		}
		
		return $firmware_files;
	}
}

?>