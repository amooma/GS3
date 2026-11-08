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

include_once( GS_DIR .'inc/util.php' );
include_once( GS_DIR .'inc/log.php' );
include_once( GS_DIR .'inc/db_connect.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );


function gs_script_invalid_usage( $usage=null )
{
	gs_write_error( ($usage ? $usage : 'Error') ."\n\n" );
	die(1);
}

function gs_script_error( $msg='' )
{
	gs_write_error( 'Error. '. $msg ."\n\n" );
	die(1);
}

class GsError
{
	var $_msg = '';
	// the constructor:
	function __construct( $msg ) {
		$this->_msg = $msg;
	}
	function getMsg() {
		return $this->_msg;
	}
}

function isGsError( $err )
{
	return (is_object($err) && strToLower(@ get_class($err))=='gserror');
}

function getBoolByWord( $boolWord )
{
	if ($boolWord===true || $boolWord===1) return true;
	$boolWord = strToLower($boolWord);
	return in_array( $boolWord, array('yes','true','1','on','y'), true );
}


?>