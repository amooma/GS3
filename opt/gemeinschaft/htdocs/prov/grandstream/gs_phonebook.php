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

$remote_ip = trim( @$_SERVER['REMOTE_ADDR'] );  //FIXME
if (! preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $remote_ip)) {
	gs_log( GS_LOG_NOTICE, "Invalid IP address \"". $remote_ip ."\"" );
	# don't explain this to the users
	_err( 'No! See log for details.' );
}

# is grandstream -- not really necessary here
$ua = trim( @$_SERVER['HTTP_USER_AGENT'] );
$ua_parts = explode(' ', $ua);
if (strToLower(@$ua_parts[0]) !== 'grandstream') {
	gs_log( GS_LOG_WARNING, "Phone with IP addr. \"$remote_ip\" has invalid User-Agent (\"". $ua ."\")" );
	_err( 'No! See log for details.' );
}
if (! preg_match('/(gxp|gxv)[0-9]{1,6}/', strToLower(@$ua_parts[1]), $m)) {
	# BT models can't download a phone book
	gs_log( GS_LOG_WARNING, "Phone with IP addr. \"$remote_ip\" has invalid phone type (\"". $ua_parts[1] ."\")" );
	_err( 'No! See log for details.' );
}


# connect to db
$db = gs_db_slave_connect();
if (! $db) {
	gs_log( GS_LOG_WARNING, "Phone with IP addr. \"$remote_ip\" (Grandstream) ask for phonebook - Could not connect to DB" );
	_err( 'No! See log for details.' );
}


# get user_id
$user_id = (int)$db->executeGetOne(
'SELECT `u`.`id`
FROM
	`users` `u`,
	`phones` `p`
WHERE
	`u`.`current_ip`=\''. $db->escape($remote_ip) .'\' AND
	`p`.`user_id`=`u`.`id` AND
	`u`.`nobody_index` IS NULL'
);
if ($user_id < 1)
	_err( 'Unknown user' );


$pb = array();

# INTERNAL phonebook
$pb[15] = array(
	'type'	=> 'gs',
	'title'	=> gs_get_conf('GS_PB_INTERNAL_TITLE', __("Intern")),
	'query'	=> 'SELECT `u`.`lastname` `ln`, `u`.`firstname` `fn`, `s`.`name` `ext`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE `u`.`nobody_index` IS NULL AND `u`.`id`!='.$user_id.'
ORDER BY `u`.`lastname`, `u`.`firstname`
LIMIT 100'
);

# PRIVATE phonebook
$pb[25] = array(
	'type'	=> 'prv',
	'title'	=> gs_get_conf('GS_PB_PRIVATE_TITLE', __("Pers\xC3\xB6nlich")),
	'query'	=> 'SELECT `pb`.`lastname` `ln`, `pb`.`firstname` `fn`, `pb`.`number` `ext`
FROM
	`pb_prv` `pb`
WHERE `pb`.`user_id`='.$user_id.'
ORDER BY `pb`.`lastname`, `pb`.`firstname`
LIMIT 100'
);

//TODO: import phonebook

kSort($pb);


ob_start();

echo '<?','xml version="1.0" encoding="utf-8"?','>' ,"\n";
echo '<AddressBook>' ,"\n";

$pb_entrys = 0;
foreach ($pb as $arr) {
	if ($pb_entrys > 100) break;
	
	$rs = $db->execute($arr['query']);
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
			//echo '<Group>0</Group>', "\n";	# only GXV3140  //TODO
			//echo '<PhotoUrl></PhotoUrl>', "\n";	# only GXV3140  //TODO
			echo '</Contact>' ,"\n";
			++$pb_entrys;
		}
	}
}
echo '</AddressBook>' ,"\n";

if (! headers_sent()) {
	header( 'Content-Type: application/xml' );
	header( 'Content-Length: '. (int)@ob_get_length() );
}
@ob_end_flush();

?>