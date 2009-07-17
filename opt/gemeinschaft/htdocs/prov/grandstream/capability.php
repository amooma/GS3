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
* Sebastian Ertz
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


class PhoneCapability_grandstream extends PhoneCapability
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
		$outfile = $outbase .'.ul';
		
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
		
		$cmd = 'sox '. qsa($infile) .' -r 8000 -c 1 '. qsa($outfile) .' rate trim 0 65000s 2>>/dev/null';
		# WAV, PCM, 16 kHz, 8 bit, mono
		# cuts file after 65000 samples (around 65 kB)
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
}

?>