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

require_once dirName(__FILE__). '/UtfNormal.php';


function gs_utf8_decompose_to_ascii( $str )
{
	static $map = null;
	if (! is_array($map))
		$map = _gs_utf8_get_map();
	
	$str = UtfNormal::toNFD( strTr($str, $map) );
	
	# return "safe" ASCII without control chars, newlines etc.
	//$str = preg_replace('/[^a-z0-9\-_. *#\'"!$()\/]/i', '', $str);
	$str = preg_replace('/[^\x20-\x7E]/', '', $str);
	return $str;
}


function gs_utf8_decompose_first_to_ascii( $str )
{
	return gs_utf8_decompose_to_ascii( mb_subStr($str,0,1) )
	                                 . mb_subStr($str,1  );
}


function hexUnicodeToUtf8( $hexcp )
{
	return @codepointToUtf8( @hexDec( $hexcp ) );
}


# escapes non-ASCII characters in an UTF-8 string to JavaScript
# style \uXXXX sequences
function utf8_to_unicode_uhex( $str )
{
	return preg_replace(
		'/[\x{00}-\x{1F}\x{7F}-\x{7FFFFFFF}]/uSe',
		'sPrintF("\\u%04x", utf8ToCodepoint("$0"))',
		str_replace(
			array( '\\'  , "\x08", "\x0C", "\n" , "\r" , "\t"  ),
			array( '\\\\', '\\b' , '\\f' , '\\n', '\\r', '\\t' ),
			$str
			)
		);
}

# quotes a string according to RFC 4627 (JSON)
function utf8_json_quote( $str )
{
	return
		'"'.
		str_replace(
			array( '"'  , '/'   ),
			array( '\\"', '\\/' ),
			utf8_to_unicode_uhex( $str )) .
		'"';
}


function _gs_utf8_get_map()
{
	$map = array();
	$lines = @file( dirName(__FILE__). '/Translit.txt' );
	if (! is_array($lines)) return $map;
	foreach ($lines as $line) {
		$line = trim($line);
		if ($line=='' || subStr($line,0,1)=='#') continue;
		
		$tmp = explode(';', $line, 3);
		$char     = rTrim(@$tmp[0]);
		$translit =  trim(@$tmp[1]);
		if (! $translit) $map[$char] = '';
		else {
			$char = hexUnicodeToUtf8( $char );
			$tmp = @preg_split('/\s+/S', $translit);
			if (! is_array($tmp)) continue;
			$t = '';
			foreach ($tmp as $translit)
				$t .= hexUnicodeToUtf8( $translit );
			$map[$char] = $t;
		}
	}
	return $map;
}


?>
