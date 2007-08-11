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

include_once( GS_DIR .'inc/log.php' );


function normalizeIPs( $str ) {
	//return preg_replace( '/\b0{1,2}(\d*)/', '$1', $str );
	return preg_replace( '/\b0{1,2}(\d+)\b/', '$1', $str );
}


if (! defined('E_STRICT')) define('E_STRICT', 2048);

function err_handler_die_on_err( $type, $msg, $file, $line )
{
	switch ($type) {
		case E_NOTICE:
		case E_USER_NOTICE:
			if (error_reporting() != 0) {
				gs_log( GS_LOG_NOTICE, 'PHP: '. $msg .' in '. $file .' on line '. $line );
			} else {  # suppressed by @
				//gs_log( GS_LOG_DEBUG , 'PHP: '. $msg .' in '. $file .' on line '. $line .' (suppressed)' );
			}
			break;
		case E_STRICT:
			if (error_reporting() != 0) {
				gs_log( GS_LOG_DEBUG, 'PHP (strict): '. $msg .' in '. $file .' on line '. $line );
			} else {  # suppressed by @
				//gs_log( GS_LOG_DEBUG , 'PHP: '. $msg .' in '. $file .' on line '. $line .' (suppressed)' );
			}
			break;
		case E_ERROR:
		case E_USER_ERROR:
			gs_log( GS_LOG_FATAL  , 'PHP: '. $msg .' in '. $file .' on line '. $line );
			echo "FATAL ERROR.\n";
			die(1);
			break;
		default:
			gs_log( GS_LOG_WARNING, 'PHP: '. $msg .' in '. $file .' on line '. $line );
			echo "WARNING.\n";
			die(1);
			break;
	}
}

function err_handler_quiet( $type, $msg, $file, $line )
{
	switch ($type) {
		case E_NOTICE:
		case E_USER_NOTICE:
			if (error_reporting() != 0) {
				gs_log( GS_LOG_NOTICE, 'PHP: '. $msg .' in '. $file .' on line '. $line );
			} else {  # suppressed by @
				//gs_log( GS_LOG_DEBUG , 'PHP: '. $msg .' in '. $file .' on line '. $line .' (suppressed)' );
			}
			break;
		case E_STRICT:
			if (error_reporting() != 0) {
				gs_log( GS_LOG_DEBUG, 'PHP (strict): '. $msg .' in '. $file .' on line '. $line );
			} else {  # suppressed by @
				//gs_log( GS_LOG_DEBUG , 'PHP: '. $msg .' in '. $file .' on line '. $line .' (suppressed)' );
			}
			break;
		default:
			gs_log( GS_LOG_FATAL  , 'PHP: '. $msg .' in '. $file .' on line '. $line );
			exit(1);
	}
}


function date_human( $ts )
{
	setLocale(LC_TIME, 'de_DE');
	if (date('Ymd', $ts) == date('Ymd'))
		$dv = 'heute';
	elseif (date('Ymd', $ts) == date('Ymd', strToTime('-1 days', $ts)))
		$dv = 'gestern';
	else $dv = strFTime('%d. %b', $ts);
	return $dv .'&nbsp; '. date('H:i', $ts);
}


?>