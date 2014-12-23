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
require_once( GS_DIR .'inc/prov-fns.php' );
set_error_handler('err_handler_die_on_err');
require_once( GS_DIR .'inc/string.php' );
require_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/group-fns.php' );


function _grandstream_xml_esc( $str )
{
	/*
	return str_replace(
		array('&'    , '"'     , '\''    , '<'   , '>'   ),
		array('&amp;', '&quot;', '&apos;', '&gt;', '&lt;'),
		utf8_strip_invalid( $str ));
	*/
	return htmlEnt( $str );
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

$requester = gs_prov_check_trust_requester();
if (! $requester['allowed']) {
	_err( 'No! See log for details.' );
}

$mac = preg_replace( '/[^0-9A-F]/', '', strToUpper( @$_REQUEST['mac'] ) );
if (strLen($mac) !== 12) {
	gs_log( GS_LOG_NOTICE, "Grandstream phonebook: Invalid MAC address \"$mac\" (wrong length)" );
	# don't explain this to the users
	_err( 'No! See log for details.' );
	}
if (hexDec(subStr($mac,0,2)) % 2 == 1) {
	gs_log( GS_LOG_NOTICE, "Grandstream phonebook: Invalid MAC address \"$mac\" (multicast address)" );
	# don't explain this to the users
	_err( 'No! See log for details.' );
}
if ($mac === '000000000000') {
	gs_log( GS_LOG_NOTICE, "Grandstream phonebook: Invalid MAC address \"$mac\" (huh?)" );
	# don't explain this to the users
	_err( 'No! See log for details.' );
}

# make sure the phone is a Grandstream:
#
if (subStr($mac,0,6) !== '000B82') {
	gs_log( GS_LOG_NOTICE, "Grandstream phonebook: MAC address \"$mac\" is not a Grandstream phone" );
	# don't explain this to the users
	_err( 'No! See log for details.' );
}

# is grandstream -- not really necessary here
$ua = trim( @$_SERVER['HTTP_USER_AGENT'] );
$ua_parts = explode(' ', $ua);
if (strToLower(@$ua_parts[0]) !== 'grandstream') {
	gs_log( GS_LOG_WARNING, "Phone with MAC \"$mac\" has invalid User-Agent (\"". $ua ."\")" );
	_err( 'No! See log for details.' );
}
if (! (preg_match('/(gxp|gxv)[0-9]{1,6}/', strToLower(@$ua_parts[1]), $m) | preg_match('/(gxp|gxv)[0-9]{1,6}/', strToLower(@$ua_parts[3]), $m))){
	# BT models can't download a phone book
	gs_log( GS_LOG_WARNING, "Phone with MAC \"$mac\" has invalid phone type (\"". $ua_parts[1] ."\")" );
	_err( 'No! See log for details.' );
}


# connect to db
$db = gs_db_slave_connect();
if (! $db) {
	gs_log( GS_LOG_WARNING, "Phone with MAC \"$mac\" (Grandstream) ask for phonebook - Could not connect to DB" );
	_err( 'No! See log for details.' );
}


# get user_id
$user_id = (int)$db->executeGetOne(
'SELECT `u`.`id`
FROM
	`users` `u` JOIN
	`phones` `p` ON (`p`.`user_id`=`u`.`id`)
WHERE
	`u`.`current_ip`=\''. $db->escape($requester['phone_ip']) .'\' AND
	`p`.`mac_addr`=\''. $db->escape($mac) .'\''
);
if ($user_id < 1)
	_err( 'Unknown user' );

$user_groups       = gs_group_members_groups_get(array($user_id), 'user');
$permission_groups = gs_group_permissions_get($user_groups, 'phonebook_user');
$group_members     = gs_group_members_get($permission_groups);


$pb = array();

# INTERNAL phonebook
$pb[15] = array(
	'type'	=> 'gs',
	'title'	=> gs_get_conf('GS_PB_INTERNAL_TITLE', __("Intern")),
	'query'	=> 'SELECT `u`.`lastname` `ln`, `u`.`firstname` `fn`, `s`.`name` `ext`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE
	`u`.`id` IN ('.implode(',', $group_members).') AND
	`u`.`id`!='.$user_id.'
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

# IMPORTED phonebook
if (gs_get_conf('GS_PB_IMPORTED_ENABLED')) {
	$pos = (int)gs_get_conf('GS_PB_IMPORTED_ORDER', 9) * 10;
	$pb[$pos] = array(
		'type'  => 'imported',
		'title' => gs_get_conf('GS_PB_IMPORTED_TITLE', __("Extern")),
		'query' => 'SELECT `lastname` `ln`, `firstname` `fn`, `number` `ext`
		FROM `pb_ldap`
		ORDER BY `lastname`, `firstname`'
	);
}

kSort($pb);


ob_start();

echo '<?','xml version="1.0" encoding="utf-8"?','>' ,"\n";
echo '<AddressBook>' ,"\n";

$pb_entrys = 0;
foreach ($pb as $arr) {
	if ($pb_entrys > 100) break;
	
	$rs = $db->execute($arr['query']);
	if ( $rs && $rs->numRows() !== 0 ) {
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
