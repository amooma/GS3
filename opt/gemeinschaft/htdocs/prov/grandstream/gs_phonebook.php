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

# this is the phonebook stored on the phone

define( 'GS_VALID', true );  /// this is a parent file

header( 'Expires: 0' );
header( 'Pragma: no-chache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );

require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
//require_once( GS_DIR .'inc/util.php' );
//require_once( GS_DIR .'inc/gs-lib.php' );
set_error_handler('err_handler_die_on_err');

require_once( GS_DIR .'inc/db_connect.php' );


function _grandstream_xml_esc( $str )
{
	//return htmlSpecialChars( $str, ENT_QUOTES, 'UTF-8' ); //?
	return str_replace(
		array('&'    , '"'     , '\''    , '<'   , '>'   ),
		array('&amp;', '&quot;', '&apos;', '&gt;', '&lt;'),
		$str);
}

function _err( $msg='' )
{
	@ob_end_clean();
	@ob_start();
	echo '<!-- ', _grandstream_xml_esc( __('Fehler') .': '. $msg ) ,' -->',"\n";
	if (! headers_sent()) {
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'Content-Length: '. (int)@ob_get_length() );
	}
	@ob_end_flush();
	exit(1);
}

if (! gs_get_conf('GS_GRANDSTREAM_PROV_ENABLED')) {
	gs_log( GS_LOG_DEBUG, "Grandstream provisioning not enabled" );
	_err( 'Not enabled' );
}

//FIXME - we need authentication here

# is grandstream -- not really necessary here
$ua = trim( @$_SERVER['HTTP_USER_AGENT'] );
$ua_parts = explode(' ', $ua);
if (strToLower(@$ua_parts[0]) !== 'grandstream') {
	gs_log( GS_LOG_WARNING, "Phone with IP \"". $_SERVER['REMOTE_ADDR'] ."\" has invalid User-Agent (\"". $ua ."\")" );
	_err( 'No! See log for details.' );
}
if (! preg_match('/(gxp|gxv)[0-9]{1,6}/', strToLower(@$ua_parts[1]), $m)) {
	# BT models can't download a phone book
	gs_log( GS_LOG_WARNING, "Phone with IP \"". $_SERVER['REMOTE_ADDR'] ."\" has invalid phone type (\"". $ua_parts[1] ."\")" );
	_err( 'No! See log for details.' );
}


# db connect
$db = gs_db_slave_connect();

# ln, fn, ext
$query =
'SELECT `u`.`lastname` `ln`, `u`.`firstname` `fn`, `s`.`name` `ext`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE `u`.`nobody_index` IS NULL
ORDER BY `u`.`lastname`, `u`.`firstname`
LIMIT 100';

$rs = $db->execute($query);


ob_start();

echo '<?','xml version="1.0" encoding="utf-8"?','>' ,"\n";
echo '<AddressBook>' ,"\n";

if ($rs->numRows() !== 0 ) {
	while ($r = $rs->fetchRow()) {
		$lastname  = $r['ln'];
		$firstname = $r['fn'];
		$number    = $r['ext'];
		
		echo '<Contact>' ,"\n";
		echo '<LastName>'. _grandstream_xml_esc($lastname) .'</LastName>' ,"\n";
		echo '<FirstName>'. _grandstream_xml_esc($firstname) .'</FirstName>' ,"\n";
		echo '<Phone>' ,"\n";
		echo '<phonenumber>'. _grandstream_xml_esc($number) .'</phonenumber>' ,"\n";
		echo '<accountindex>0</accountindex>' ,"\n";
		echo '</Phone>' ,"\n";
		echo '</Contact>' ,"\n";
	}
}
echo '</AddressBook>' ,"\n";

if (! headers_sent()) {
	header( 'Content-Type: application/xml' );
	header( 'Content-Length: '. (int)@ob_get_length() );
}
@ob_end_flush();    

?>