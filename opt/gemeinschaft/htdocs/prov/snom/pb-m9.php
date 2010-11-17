<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2010, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
* Sebastian Ertz <gemeinschaft@swastel.eisfair.net>
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

define( 'GS_VALID', true );  /// this is a parent file

header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );


require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'inc/prov-fns.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );
require_once( GS_DIR .'inc/string.php' );
require_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/group-fns.php' );
set_error_handler('err_handler_die_on_err');

function _snom_normalize_version( $appvers )
{
	$tmp = explode('.', $appvers);
	$vmaj = str_pad((int)@$tmp[0], 2, '0', STR_PAD_LEFT);
	$vmin = str_pad((int)@$tmp[1], 2, '0', STR_PAD_LEFT);
	$vsub = str_pad((int)@$tmp[2], 2, '0', STR_PAD_LEFT);
	return $vmaj.'.'.$vmin.'.'.$vsub;
}

function _snomXmlEsc( $str )
{
	return str_replace(
		array('&'    , '<'   , '>'   , '"'   ),
		array('&amp;', '&lt;', '&gt;', '\'\''),
		$str);
}

function _err( $msg='' )
{
	@ob_start();
	echo '<!-- // ', _snomXmlEsc($msg != '' ? str_replace('--','- -',$msg) : 'Error') ,' // -->',"\n";
	if (! headers_sent()) {
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'Content-Length: '. (int)@ob_get_length() );
	}
	@ob_end_flush();
	exit(1);
}

if (gs_get_conf('GS_SNOM_PROV_M9_ACCOUNTS') < 1) {
	gs_log( GS_LOG_DEBUG, "Snom M9 provisioning not enabled" );
	_settings_err( 'Not enabled.' );
}



$requester = gs_prov_check_trust_requester();
if (! $requester['allowed']) {
	_settings_err( 'No! See log for details.' );
}

$mac = preg_replace( '/[^0-9A-F]/', '', strToUpper( @$_REQUEST['mac'] ) );
if (strLen($mac) !== 12) {
	gs_log( GS_LOG_NOTICE, "Snom M9 provisioning: Invalid MAC address \"$mac\" (wrong length)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}
if (hexDec(subStr($mac,0,2)) % 2 == 1) {
	gs_log( GS_LOG_NOTICE, "Snom M9 provisioning: Invalid MAC address \"$mac\" (multicast address)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}
if ($mac === '000000000000') {
	gs_log( GS_LOG_NOTICE, "Snom M9 provisioning: Invalid MAC address \"$mac\" (huh?)" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}

# make sure the phone is a Snom-M9:
#
if ( (subStr($mac,0,6) !== '000413') ) {
	gs_log( GS_LOG_NOTICE, "Snom M9 provisioning: MAC address \"$mac\" is not a Snom M9 phone" );
	# don't explain this to the users
	_settings_err( 'No! See log for details.' );
}

$ua = trim(@$_SERVER['HTTP_USER_AGENT']);
if (! preg_match('/^Mozilla/i', $ua)
||  ! preg_match('/snom\sm9/i', $ua) ) {
	gs_log( GS_LOG_WARNING, "Phone with MAC \"$mac\" (Snom) has invalid User-Agent (\"". $ua ."\")" );
	# don't explain this to the users
	//_settings_err( 'No! See log for details.' );
}


# connect to DB
$db = gs_db_slave_connect();
if (! $db) {
	gs_log( GS_LOG_WARNING, "Snom M9 phone asks for phonebook - Could not connect to DB" );
	_settings_err( 'Could not connect to DB.' );
}



ob_start();
echo '<?','xml version="1.0" encoding="utf-8"?','>' ,"\n";
echo '<tbook complete="true">' ,"\n";

$pb_entrys = 1;

for ($i=1; $i <= gs_get_conf('GS_SNOM_PROV_M9_ACCOUNTS'); ++$i) {
	
	# create virtual mac address
	$mac_addr = ($i > 1) ? ($mac.'-'.$i) : $mac;
	
	# get user_id
	$user_id = (int)$db->executeGetOne(
'SELECT `u`.`id`
FROM
	`users` `u` JOIN
	`phones` `p` ON (`p`.`user_id`=`u`.`id`)
WHERE
	`u`.`current_ip`=\''. $db->escape($requester['phone_ip']) .'\' AND
	`p`.`mac_addr`=\''. $db->escape($mac_addr) .'\''
);
	
	$user_groups       = gs_group_members_groups_get(array($user_id), 'user');
	$permission_groups = gs_group_permissions_get($user_groups, 'phonebook_user');
	$group_members     = gs_group_members_get($permission_groups);
	
	$query = 'SELECT `u`.`lastname` `ln`, `u`.`firstname` `fn`, `s`.`name` `ext`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE
	`u`.`id` IN ('.implode(',', $group_members).') AND
	`u`.`id`!='.$user_id.'
ORDER BY `u`.`lastname`, `u`.`firstname`';
	
	$rs = $db->execute($query);
	if ( $rs && $rs->numRows() !== 0 ) {
		while ($r = $rs->fetchRow()) {
			
			if ($pb_entrys > 500) break;
			
			$lastname  = $r['ln'];
			$firstname = $r['fn'];
			$number    = $r['ext'];
			
			echo '<item context="identity'.$i.'" index="'.$pb_entrys.'">' ,"\n";
			echo '<name>'. _snomXmlEsc($lastname) .' '._snomXmlEsc($firstname) .'</name>' ,"\n";
			echo '<number>'. _snomXmlEsc($number) .'</number>' ,"\n";
			echo '</item>' ,"\n";
			++$pb_entrys;
		}
	}
}

echo '</tbook>' ,"\n";

if (! headers_sent()) {
	header( 'Content-Type: application/xml' );
	header( 'Content-Length: '. (int)@ob_get_length() );
}
@ob_end_flush();

?>