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
require_once( GS_DIR .'inc/quote_shell_arg.php' );
require_once( GS_DIR .'inc/quoted_printable_encode.php' );


function gs_keyval_is_valid_key( $key )
{
	return (bool)preg_match('/[a-z0-9_\-][a-z0-9_\-.]*/', $key);
}

function gs_keyval_enc( $str )
{
	/*
	return str_replace(' ', '=20',
		quoted_printable_encode($str, false));
	*/
	
	$str = quoted_printable_encode($str, false);
	
	# encode trailing spaces:
	do { $str = preg_replace('/ ( *)$/', '=20$1', $str, -1, $cnt); }
	while ($cnt > 0);
	
	# encode leading spaces:
	do { $str = preg_replace('/^( *) /', '$1=20', $str, -1, $cnt); }
	while ($cnt > 0);
	
	return $str;
}

function gs_keyval_dec( $str )
{
	return quoted_printable_decode( trim( $str ) );
}



function gs_keyval_get( $key )
{
	if (! gs_keyval_is_valid_key($key)) return null;
	$err=0; $out=array();
	@exec( 'sudo cat '. qsa('/var/lib/gemeinschaft/vars/'.$key) .' 2>>/dev/null', $out, $err );
	return ($err===0 ? gs_keyval_dec(implode('',$out)) : '');
}

function gs_keyval_set( $key, $val )
{
	if (! gs_keyval_is_valid_key($key)) return false;
	if ($val === gs_keyval_get($key)) return true;  # unchanged
	/*
	$val = gs_keyval_enc($val);
	$fh = @fOpen( '/var/lib/gemeinschaft/vars/'.$key, 'wb' );
	if (! $fh) return false;
	@stream_set_write_buffer($fh, 0);
	$ok = (bool)@fWrite($fh, $val, strLen($val));
	@fClose($fh);
	return $ok;
	*/
	$cmd =
		'echo -n '. qsa(gs_keyval_enc($val))
		.' > '. qsa('/var/lib/gemeinschaft/vars/'.$key)
		.' 2>>/dev/null';
	$err=0; $out=array();
	@exec( 'sudo sh -c '. qsa($cmd) .' 2>>/dev/null', $out, $err );
	return ($err===0);
}


?>