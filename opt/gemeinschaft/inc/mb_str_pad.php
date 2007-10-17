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


# custom function. even mbstring doesn't come with mb_str_pad()
#
if (! function_exists('mb_str_pad')) {
function mb_str_pad( $str, $len, $padstr=' ', $padtype=STR_PAD_RIGHT, $enc='' )
{
	if (! function_exists('mb_strlen'))
		return str_pad( $str, $len, $padstr, $padtype );
	
	if ($enc==='') $enc = mb_internal_encoding();
	$strlen = mb_strLen($str,$enc);
	$flen = $len - $strlen;
	if ($flen <= 0) return $str;
	$pad = str_repeat($padstr, $flen);
	
	switch ($padtype) {
	case STR_PAD_RIGHT:
		$pad = mb_subStr($pad, 0, $flen, $enc);
		return $str . $pad;
	case STR_PAD_LEFT:
		$pad = mb_subStr($pad, 0, $flen, $enc);
		return $pad . $str;
	case STR_PAD_BOTH:
		$flenh = $flen/2;
		$padl = mb_subStr($pad, 0, floor($flenh), $enc);
		$padr = mb_subStr($pad, 0, ceil($flenh), $enc);
		return $padl . $str . $padr;
	default:
		trigger_error('mb_str_pad(): Padding type has to be STR_PAD_LEFT, STR_PAD_RIGHT, or STR_PAD_BOTH.', E_USER_WARNING);
		return null;
	}
}
}


# emulate mbstring's mb_convert_case() for PHP < 4.3
# or PHP without mbstring
#
if (! defined('MB_CASE_UPPER')) define('MB_CASE_UPPER', 0);
if (! defined('MB_CASE_LOWER')) define('MB_CASE_LOWER', 1);
if (! defined('MB_CASE_TITLE')) define('MB_CASE_TITLE', 2);

if (! function_exists('mb_convert_case')) {
function mb_convert_case( $str, $mode, $enc='' )
{
	switch ($mode) {
		case MB_CASE_LOWER: return         strToLower($str) ;
		case MB_CASE_UPPER: return         strToUpper($str) ;
		case MB_CASE_TITLE: return ucWords(strToLower($str));
		default           : return                    $str  ;
	}
}
}


?>