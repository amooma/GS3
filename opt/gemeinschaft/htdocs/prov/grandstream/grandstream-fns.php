<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2009, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* 
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

defined('GS_VALID') or die('No direct accress.');


function grandstream_binary_output_checksum( $str )
{
	$sum = 0;
	for ($i=0; $i <= ((strLen($str) - 1) / 2); $i++) {
		$sum += ord(subStr($str,  2 * $i      , 1)) << 8;
		$sum += ord(subStr($str, (2 * $i) + 1 , 1));
		$sum &= 0xffff;
	}
	$sum = 0x10000 - $sum;
	return array(($sum >> 8) & 0xff, $sum & 0xff);
}


function grandstream_binary_output( $header, $body )
{
	# $body length must be divisible by 2
	if ((strLen($body) % 2) == 1) $body .= chr(0);
	
	# get length
	$body_length = strLen($body);
	$header_length = count($header);
	$out_length = $header_length + $body_length;
	
	// 00 01 02 03 - out_length / 2
	$header[0] = (($out_length / 2) >> 24) & 0xff;
	$header[1] = (($out_length / 2) >> 16) & 0xff;
	$header[2] = (($out_length / 2) >>  8) & 0xff;
	$header[3] = (($out_length / 2)      ) & 0xff;
	
	# assemble output
	$arr = $header;
	array_unshift($arr, 'C'.$header_length);
	$initstr = call_user_func_array('pack', $arr);
	$checktext = $initstr . $body;
	
	array_splice($header, 4, 2, grandstream_binary_output_checksum($checktext));
	
	$arr = $header;
	array_unshift($arr, 'C'.$header_length);
	$initstr = call_user_func_array('pack', $arr);
	$out = $initstr . $body;
	
	return $out;	
}

?>