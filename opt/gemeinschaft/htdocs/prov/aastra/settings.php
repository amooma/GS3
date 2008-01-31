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

define( 'GS_VALID', true );  /// this is a parent file


header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );


require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'inc/util.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );
set_error_handler('err_handler_die_on_err');

function setting( $name, $idx, $val, $attrs=null, $writeable=false )
{
	echo "$name: $val\n";
}

function psetting( $name, $val, $writeable=false )
{
	setting( $name, null, $val, null, $writeable );
}

function aastra_get_keys( $user_id, $model )
{
	global $db;
	
	$query =
'SELECT
	`key`, `function`, `number`, `title`, `flags`
FROM `softkeys`
WHERE
	`user_id`='. $user_id. '
AND
	`phone_type`=\''. $db->escape($model). '\'';
	
	$rs = $db->execute( $query );
	if (! $rs) return false;
	
	while ($r = @$rs->fetchRow()) {
		$key_function = 'speeddial';
		if ($r['function'] == 'Dial') $key_function = 'blf';
		
		psetting($r['key'].' type' , $key_function);
		psetting($r['key'].' label', $r['title']);
		psetting($r['key'].' value', $r['number']);
	}
	
	return true;
}

if (! gs_get_conf('GS_AASTRA_PROV_ENABLED')) {
	gs_log( GS_LOG_DEBUG, "Aastra provisioning not enabled" );
	die( 'Not enabled.' );
}

$mac = preg_replace( '/[^0-9A-F]/', '', strToUpper( @$_REQUEST['mac'] ) );
if (strLen($mac) !== 12) {
	gs_log( GS_LOG_NOTICE, "Aastra provisioning: Invalid MAC address \"$mac\" (wrong length)" );
	# don't explain this to the users
	die( 'No! See log for details.' );
}
if (hexDec(subStr($mac,0,2)) % 2 == 1) {
	gs_log( GS_LOG_NOTICE, "Aastra provisioning: Invalid MAC address \"$mac\" (multicast address)" );
	# don't explain this to the users
	die( 'No! See log for details.' );
}
if ($mac === '000000000000') {
	gs_log( GS_LOG_NOTICE, "Aastra provisioning: Invalid MAC address \"$mac\" (huh?)" );
	# don't explain this to the users
	die( 'No! See log for details.' );
}

# make sure the phone is an Aastra:
#
if (subStr($mac,0,6) !== '00085D') {
	gs_log( GS_LOG_NOTICE, "Aastra provisioning: MAC address \"$mac\" is not an Aastra phone" );
	# don't explain this to the users
	die( 'No! See log for details.' );
}

$ua = trim( @$_SERVER['HTTP_USER_AGENT'] );
//FIXME - do some more checks here

gs_log( GS_LOG_DEBUG, "Aastra phone \"$mac\" asks for settings (UA: ...\"$ua\") " );

$ua_arr = explode(' ', $ua);
$phone_type = str_replace('Aastra', '', $ua_arr[0]);  //FIXME
if ($phone_type == $ua_arr[0]) $phone_type = '57i';
$newPhoneType = 'aastra-'. $phone_type;

$prov_url_aastra = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT==80 ? '' : (':'. GS_PROV_PORT)) . GS_PROV_PATH .'aastra/';

require_once( GS_DIR .'inc/db_connect.php' );
require_once( GS_DIR .'inc/nobody-extensions.php' );
require_once( GS_DIR .'inc/gs-lib.php' );

$db = gs_db_master_connect();
if (! $db) {
	gs_log( GS_LOG_WARNING, "Aastra phone asks for settings - Could not connect to DB" );
	die( 'Could not connect to DB.' );
}

# do we know the phone?
#

/*
$query =
'SELECT `user_id`, `vlan_id`
FROM `phones`
WHERE `mac_addr`=\''. $mac .'\'
ORDER BY `id` LIMIT 1';
$rs = $db->execute( $query );
$r = $rs->fetchRow();
if ($r) {
	$user_id = (int)$r['user_id'];
	$vlan_id = (int)$r['vlan_id'];
*/
$query =
'SELECT `user_id`
FROM `phones`
WHERE `mac_addr`=\''. $mac .'\'
ORDER BY `id` LIMIT 1';
$rs = $db->execute( $query );
$r = $rs->fetchRow();
if ($r) {
	$user_id = (int)$r['user_id'];
} else {
	if (! GS_PROV_AUTO_ADD_PHONE) {
		gs_log( GS_LOG_NOTICE, "Unknown Aastra phone \"$mac\" not added to DB" );
		die( 'Unknown phone. (Set GS_PROV_AUTO_ADD_PHONE = true in order to auto-add)' );
	}
	gs_log( GS_LOG_NOTICE, "Adding new Aastra phone $mac to DB" );
	
	# add a nobody user:
	#
	$newNobodyIndex = (int)( (int)$db->executeGetOne( 'SELECT MAX(`nobody_index`) FROM `users`' ) + 1 );
	$user_code = 'nobody-'. str_pad($newNobodyIndex, 5, '0', STR_PAD_LEFT);
	switch (GS_PROV_AUTO_ADD_PHONE_HOST) {
		case 'last':
			$host_id_sql = 'SELECT MAX(`id`) FROM `hosts`'; break;
		case 'random':
			$host_id_sql = 'SELECT `id` FROM `hosts` ORDER BY RAND() LIMIT 1'; break;
		case 'first':
		default:
			$host_id_sql = 'SELECT MIN(`id`) FROM `hosts`'; break;
	}
	$db->execute( 'INSERT INTO `users` (`id`, `user`, `pin`, `firstname`, `lastname`, `honorific`, `email`, `nobody_index`, `host_id`) VALUES (NULL, \''. $user_code .'\', \'\', \'\', \'\', \'\', \'\', '. $newNobodyIndex .', ('. $host_id_sql .'))' );
	$user_id = (int)$db->getLastInsertId();
	if ($user_id < 1) die( 'Unknown phone. Failed to add nobody user.' );
	
	# add a SIP account:
	#
	//$user_name = '9'. str_pad($newNobodyIndex, 5, '0', STR_PAD_LEFT);
	$user_name = gs_nobody_index_to_extension( $newNobodyIndex );
	$secret = rand(10000000,99999999) . mt_rand(10000000,99999999) . rand(10000000,99999999);
	$db->execute( 'INSERT INTO `ast_sipfriends` (`_user_id`, `name`, `secret`, `context`, `callerid`, `setvar`) VALUES ('. $user_id .', \''. $user_name .'\', \''. $db->escape($secret) .'\', \'from-internal-nobody\', _utf8\''. $db->escape(GS_NOBODY_CID_NAME . $newNobodyIndex) .' <'. $user_name .'>\', \'__user_id='. $user_id .';__user_name='. $user_name .'\')' );
	
	# add the phone:
	#
	$db->execute( 'INSERT INTO `phones` (`id`, `type`, `mac_addr`, `user_id`, `nobody_index`, `added`) VALUES (NULL, \''. $db->escape($newPhoneType) .'\', \''. $mac .'\', '. $user_id .', '. $newNobodyIndex .', '. time() .')' );
	
	unset($user_name);
}

# is it a valid user id?
#

$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `users` WHERE `id`='. $user_id );
if ($num < 1)
	$user_id = 0;

if ($user_id < 1) {
	# something bad happened, nobody (not even a nobody user) is logged
	# in at that phone. assign an unused nobody user:
	
	$user_id = (int)$db->executeGetOne(
'SELECT `u`.`id`
FROM
	`users` `u` JOIN
	`phones` `p` ON (`p`.`nobody_index`=`u`.`nobody_index`)
WHERE
	`p`.`mac_addr`=\''. $mac .'\' AND
	`p`.`nobody_index`>=1
LIMIT 1'
	);
	if ($user_id < 1) {
		$user_id = (int)$db->executeGetOne(
'SELECT `id`
FROM `users`
WHERE
	`nobody_index` IS NOT NULL AND
	`id` NOT IN (SELECT `user_id` FROM `phones` WHERE `user_id` IS NOT NULL)
ORDER BY RAND() LIMIT 1'
	);
		if ($user_id < 1)
			die( 'No unused nobody accounts left.' );
	}
	$ok = $db->execute( 'UPDATE `phones` SET `user_id`='. $user_id .' WHERE `mac_addr`=\''. $mac .'\' LIMIT 1'  );
	if (! $ok) die( 'DB error.' );
	$rs = $db->execute( $query );
	$r = $rs->fetchRow();
	if (! $r) die( 'Failed to assign nobody account to phone "'. $mac .'".' );
	$user_id = (int)$r['user_id'];
}
if ($user_id < 1) die( 'DB error.' );

# if no host specified, select one (randomly?)
#

$host = $db->executeGetOne(
'SELECT `h`.`host`
FROM
	`users` `u` LEFT JOIN
	`hosts` `h` ON (`h`.`id`=`u`.`host_id`)
WHERE `u`.`id`='. $user_id .'
LIMIT 1'
	);

if (! $host) {
	$rs = $db->execute( 'SELECT `host` FROM `hosts` ORDER BY RAND() LIMIT 1' );
	$r = $rs->fetchRow();
	if (! $r)
		die( 'No hosts known.' );
	
	$host = trim( $r['host'] );
}

# who is logged in at that phone?
#

$rs = $db->execute(
'SELECT
	`u`.`user`, `u`.`firstname`, `u`.`lastname`, `u`.`honorific`,
	`s`.`name`, `s`.`secret`, `s`.`callerid`, `s`.`mailbox`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE `u`.`id`='. $user_id
);
$user = $rs->fetchRow();
if (! $user) {
	die( 'DB error.' );
}

# get the IP address of the phone:
#
$phoneIP = @ normalizeIPs( @$_SERVER['REMOTE_ADDR'] );
/*
//FIXME - we should add a setting AASTRA_PROV_TRUST_PROXIES = '192.168.1.7, 192.168.1.8'
if (isSet( $_SERVER['HTTP_X_FORWARDED_FOR'] )) {
	if (preg_match( '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', lTrim( $_SERVER['HTTP_X_FORWARDED_FOR'] ), $m ))
		$phoneIP = isSet( $m[0] ) ? @ normalizeIPs( $m[0] ) : null;
}
if (isSet( $_SERVER['HTTP_X_REAL_IP'] )) {
	if (preg_match( '/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', lTrim( $_SERVER['HTTP_X_REAL_IP'] ), $m ))
		$phoneIP = isSet( $m[0] ) ? @ normalizeIPs( $m[0] ) : null;
}
*/

if ($phoneIP) {
	# unset all ip addresses which are the same as the new one and
	# thus cannot be valid any longer:
	$db->execute( 'UPDATE `users` SET `current_ip`=NULL WHERE `current_ip`=\''. $db->escape($phoneIP) .'\'' );
}
# store new ip address:
$db->execute( 'UPDATE `users` SET `current_ip`='. ($phoneIP ? ('\''. $db->escape($phoneIP) .'\'') : 'NULL') .' WHERE `id`='. $user_id );

#get aastra softkeys
aastra_get_keys( $user_id, $newPhoneType );


psetting('sip mode'                , '0');  # ?
psetting('sip screen name'         , $user['name'] .' '. mb_subStr($user['firstname'],0,1) .'. '. $user['lastname']);
psetting('sip display name'        , $user['firstname'].' '.$user['lastname']);
psetting('sip user name'           , $user['name']);
psetting('sip vmail'               , $user['mailbox']);
psetting('sip auth name'           , $user['name']);
psetting('sip password'            , $user['secret']);
psetting('sip registrar ip'        , $host);
psetting('sip registrar port'      , '5060');
psetting('sip registration period' , '3600');
psetting('sip outbound proxy'      , $host);
psetting('sip outbound proxy port' , '5060');

?>