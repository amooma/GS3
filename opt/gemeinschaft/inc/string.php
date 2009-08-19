<?php
/*******************************************************************\
*	    Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Philipp Kempgen <philipp.kempgen@amooma.de>
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


# Checks if preg_match('//u') can be used to detect invalid UTF-8
# strings.
#
function _utf8_check_test_preg_detects_invalid()
{
	$seqs = array(
		"\x80"                     => false, # invalid 1-octet sequence
		"\xC0"                     => false, # invalid 1-octet sequence
		"\xC3\x28"                 => false, # invalid 2-octet sequence
		"\xC1\xA1"                 => false, # invalid 2-octet sequence (overlong \x61)
		"\xE0\x80"                 => false, # invalid 2-octet sequence (tail too short)
		"\xA0\xA1"                 => false, # invalid sequence identifier
		"\xE2\x28\xA1"             => false, # invalid 3-octet sequence (in 2nd octet)
		"\xE2\x82\x28"             => false, # invalid 3-octet sequence (in 3rd octet)
		"\xF0\x28\x8C\xBC"         => false, # invalid 4-octet sequence (in 2nd octet)
		"\xF0\x90\x28\xBC"         => false, # invalid 4-octet sequence (in 3rd octet)
		"\xF0\x28\x8C\x28"         => false, # invalid 4-octet sequence (in 4th octet)
		"\xF8\xA1\xA1\xA1\xA1"     => false, # valid 5-octet sequence (but not Unicode!)
		"\xFC\xA1\xA1\xA1\xA1\xA1" => false, # valid 6-octet sequence (but not Unicode!)
		"\xED\xA0\x81\xED\xB0\x80" => false, # invalid surrogate
		"\xE2\x82\xAC"             => true,  # valid
		"\xF0\x9D\x84\x9E"         => true,  # valid
	);
	foreach ($seqs as $seq => $valid) {
		//echo 'soll: ', var_export($valid, true) ,', ist: ', var_export( (bool)preg_match('/^/uS', $seq), true ) ,"\n";
		if ((bool)@preg_match('/^/u', $seq) !== $valid) return false;
	}
	return true;
}


$utf8_check_test_preg_detects_invalid = _utf8_check_test_preg_detects_invalid();


# Checks if a string is valid UTF-8.
#

if ($utf8_check_test_preg_detects_invalid) {
	
	# About 2.5 times as fast as mb_check_encoding().
	
	function utf8_is_valid( $str )
	{
		return (bool)@preg_match('/^/uS', $str);
	}
}
/*
elseif (function_exists('mb_check_encoding')) {
	
	function utf8_is_valid( $str )
	{
		return mb_check_encoding( $str, 'UTF-8' );
	}
}
*/
else {
	
	# About 10 times as slow as preg_match('//u').
	
	function utf8_is_valid( $str )
	{
		return (bool)preg_match( '/^(?:
		  [\x00-\x7E]                       # ASCII
		| [\xC2-\xDF][\x80-\xBF]            # non-overlong 2-byte
		|  \xE0[\xA0-\xBF][\x80-\xBF]       # excluding overlongs
		| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
		|  \xED[\x80-\x9F][\x80-\xBF]       # excluding surrogates
		|  \xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
		| [\xF1-\xF3][\x80-\xBF]{3}         # planes 4-15
		|  \xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
		)*$/xS', $str );
	}
}


# Strips non-UTF-8 sequences from a string.
#

if ($utf8_check_test_preg_detects_invalid) {
	
	# check utf8_is_valid() before actually doing any work
	
	function utf8_strip_invalid( $str, $replacement=true )
	{
		if (utf8_is_valid( $str )) return $str;
		# about 30 % overhead for bad strings but 2.5 times as fast
		# for good strings
		preg_match_all( '/(?:
			  [\x00-\x7E]+                      # ASCII
			| [\xC2-\xDF][\x80-\xBF]            # non-overlong 2-byte
			|  \xE0[\xA0-\xBF][\x80-\xBF]       # excluding overlongs
			| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
			|  \xED[\x80-\x9F][\x80-\xBF]       # excluding surrogates
			|  \xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
			| [\xF1-\xF3][\x80-\xBF]{3}         # planes 4-15
			|  \xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
			)+/xS',
			($replacement ? '_'.$str.'_' : $str),
			$mm, PREG_PATTERN_ORDER );
		return $replacement
			? subStr(implode("\xEF\xBF\xBD", $mm[0]), 1,-1)  # U+FFFD
			:        implode(''            , $mm[0]);
	}
}
else {
	
	# skip slow utf8_is_valid() shortcut
	
	function utf8_strip_invalid( $str, $replacement=true )
	{
		preg_match_all( '/(?:
			  [\x00-\x7E]+                      # ASCII
			| [\xC2-\xDF][\x80-\xBF]            # non-overlong 2-byte
			|  \xE0[\xA0-\xBF][\x80-\xBF]       # excluding overlongs
			| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
			|  \xED[\x80-\x9F][\x80-\xBF]       # excluding surrogates
			|  \xF0[\x90-\xBF][\x80-\xBF]{2}    # planes 1-3
			| [\xF1-\xF3][\x80-\xBF]{3}         # planes 4-15
			|  \xF4[\x80-\x8F][\x80-\xBF]{2}    # plane 16
			)+/xS',
			($replacement ? '_'.$str.'_' : $str),
			$mm, PREG_PATTERN_ORDER );
		return $replacement
			? subStr(implode("\xEF\xBF\xBD", $mm[0]), 1,-1)  # U+FFFD
			:        implode(''            , $mm[0]);
	}
}


unset($utf8_check_test_preg_detects_invalid);


# Converts special characters to XML entities.
# "&" (ampersand) becomes "&amp;", "<" (left angle bracket) becomes
# "&lt;", ">" (right angle bracket) becomes "&gt;", """ (double quote)
# becomes "&quot;", "'" (single quote) becomes "&#039;" (instead of
# the "&apos;" XML entity for compatibility with old web browsers).
# Makes sure that the returned string is valid UTF-8 by stripping
# invalid sequences.
#
//function xml_esc( $str )
function htmlEnt( $str )
{
	return htmlSpecialChars(
		utf8_strip_invalid( $str ),  # makes this function about 4 times as slow
		ENT_QUOTES, 'ISO-8859-1' );
	# It's not necessary to specify the "UTF-8" charset here, because
	# all the special characters are in the single-byte ASCII range
	# "[\x00-\x7E]" and don't appear anywhere else in UTF-8 strings.
	# Also note that htmlSpecialChars(,,'UTF-8') would not return
	# anything for strings containing invalid sequences.
}

