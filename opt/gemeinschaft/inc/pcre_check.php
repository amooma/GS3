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


// helper function
function _pcre_check_counting_err_hdlr( $type, $msg, $file, $line )
{
	global $_pcre_err_cnt;
	
	switch ($type) {
		case E_NOTICE:
		case E_USER_NOTICE:
			break;
		default:
			++$_pcre_err_cnt;
	}
}


/***********************************************************
*    checks if a string is a valid PCRE
***********************************************************/

function is_valid_pcre( $pcre )
{
	global $_pcre_err_cnt;
	
	error_reporting(E_ALL ^ E_NOTICE);
	# set counting error handler:
	set_error_handler('_pcre_check_counting_err_hdlr');
	# try to compile the regex:
	preg_match($pcre, '');
	# any errors?:
	$ok = ($_pcre_err_cnt == 0);
	# restore the error handler to the previous one:
	restore_error_handler();
	return $ok;
}


?>