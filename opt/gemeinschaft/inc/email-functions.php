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


function quoted_printable_encode_text( $text, $eol="\r\n" )
{
	/*
	if (function_exists('quoted_printable_encode')) {
		//FIXME?
		//...
	}
	*/
	if (function_exists('imap_8bit')) {
		$text = preg_replace('/(?:\r\n|\r|\n)/', "\r\n", $text);  # so imap_8bit() works correctly
		$text = imap_8bit($text);
		$text = str_replace(
			array( '=0D=0A', '=0A', '=0D' ),
			array( "\n"    , "\n" , "\n"  ),
			$text
		);
		$text = preg_replace('/ $/mS', '=20', $text);
		$text = preg_replace('/^\\.$/mS', '=2E', $text);
		if ($eol !== "\n") {
			$text = preg_replace('/\n/S', $eol, $text);
		}
		return rtrim($text);
	}
	else {
		$hex = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
		$lines = preg_split('/(?:\r\n|\r|\n)/', $text);
		$maxlen = 76;
		$out = '';
		foreach ($lines as $line) {
			$linelen_m1 = strlen($line) - 1;
			$lineout = '';
			for ($i=0; $i<=$linelen_m1; ++$i) {
				$c = substr($line, $i, 1);
				$dec = ord($c);
				if ($i === $linelen_m1) {
					if ($dec === 32) {
						# convert space at EOL
						$c = '=20';
					} elseif ($dec === 9) {
						# convert tab at EOL
						$c = '=09';
					}
				} elseif (($dec < 32) || ($dec > 126)) {
					$h2 = floor($dec/16);
					$h1 = floor($dec%16);
					$c = '='.$hex[$h2].$hex[$h1];
				} elseif (($i === 0) && ($dec === 46)) {
					# convert "." at the beginning of a line to "=2E"
					$c = '=2E';
				}
				
				if ((strlen($lineout) + strlen($c)) >= $maxlen) {
					# CRLF is not counted
					$out .= $lineout.'='.$eol;  # soft line break
					$lineout = '';
					# convert "." at the beginning of lineout to "=2E"
					if ($dec === 46) {
						$c = '=2E';
					}
				}
				$lineout .= $c;
			}
			$out .= $lineout.$eol;
		}
		return rtrim($out);
	}
}

?>