<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2010, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
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

require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
//require_once( GS_DIR .'inc/util.php' );
//require_once( GS_DIR .'inc/gs-lib.php' );
require_once( GS_DIR .'inc/prov-fns.php' );
set_error_handler('err_handler_die_on_err');
require_once( GS_DIR .'inc/string.php' );
require_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/group-fns.php' );

header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );


function _tiptel_xml_esc( $str )
{
	return htmlEnt( $str );
}

function _err( $msg='' )
{
	@ob_end_clean();
	@ob_start();
	echo '<!-- ', _tiptel_xml_esc( __('Fehler') .': '. $msg ) ,' -->',"\n";
	if (! headers_sent()) {
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'Content-Length: '. (int)@ob_get_length() );
	}
	@ob_end_flush();
	exit(1);
}

if (! gs_get_conf('GS_TIPTEL_PROV_ENABLED')) {
	gs_log( GS_LOG_DEBUG, "Tiptel provisioning not enabled" );
	_err( 'Not enabled' );
}

$type = trim( @$_REQUEST['t'] );
if (! in_array( $type, array('gs','prv','imported'), true )) {
	_err( 'Not allowed' );
}

$mac = strToUpper(trim( @$_REQUEST['m'] ));

//FIXME - we need authentication here

$requester = gs_prov_check_trust_requester();
if (! $requester['allowed']) {
	_err( 'No! See log for details.' );
}

# connect to db
$db = gs_db_slave_connect();
if (! $db) {
	gs_log( GS_LOG_WARNING, "Phone with MAC \"$mac\" (Tiptel) ask for phonebook - Could not connect to DB" );
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


if ($type === 'gs') { # INTERNAL phonebook
	$user_groups       = gs_group_members_groups_get(array($user_id), 'user');
	$permission_groups = gs_group_permissions_get($user_groups, 'phonebook_user');
	$group_members     = gs_group_members_get($permission_groups);

	$pb = array(
		'type'	=> 'gs',
		'title'	=> gs_get_conf('GS_PB_INTERNAL_TITLE', __("Intern")),
		'query'	=> 'SELECT `u`.`lastname` `ln`, `u`.`firstname` `fn`, `s`.`name` `ext`
		FROM
		`users` `u` JOIN
		`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
		WHERE
			`u`.`id` IN ('.implode(',', $group_members).') AND
			`u`.`id`!='.$user_id.'
		ORDER BY `u`.`lastname`, `u`.`firstname`'
	);
}
elseif ($type ==='prv') { # PRIVATE phonebook
	$pb = array(
		'type'	=> 'prv',
		'title'	=> gs_get_conf('GS_PB_PRIVATE_TITLE', __("Pers\xC3\xB6nlich")),
		'query'	=> 'SELECT `pb`.`lastname` `ln`, `pb`.`firstname` `fn`, `pb`.`number` `ext`
		FROM
		`pb_prv` `pb`
		WHERE `pb`.`user_id`='.$user_id.'
		ORDER BY `pb`.`lastname`, `pb`.`firstname`'
	);
}
elseif ($type === 'imported') { # IMPORTED phonebook
	if (! gs_get_conf('GS_PB_IMPORTED_ENABLED'))
		_err( 'Not allowed' );
		
	$pb = array(
		'type'	=> 'imported',
		'title'	=> gs_get_conf('GS_PB_IMPORTED_TITLE', __("Extern")),
		'query'	=> 'SELECT `lastname` `ln`, `firstname` `fn`, `number` `ext`
		FROM `pb_ldap`
		ORDER BY `lastname`, `firstname`'
	);
}


ob_start();

echo '<?','xml version="1.0" encoding="utf-8"?','>' ,"\n";
echo '<TiptelIPPhoneDirectory>' ,"\n";
echo '<Title>'. tiptelXmlEsc($pb['title']) .'</Title>', "\n";

$rs = $db->execute( $pb['query'] );

if ( $rs && $rs->numRows() !== 0 ) {
	while ($r = $rs->fetchRow()) {
		$lastname  = $r['ln'];
		$firstname = $r['fn'];
		$number    = $r['ext'];

		echo '<DirectoryEntry>' ,"\n";
		echo '<Name>'. tiptelXmlEsc($lastname) .', '. tiptelXmlEsc($firstname) .' ('. tiptelXmlEsc($number) .')</Name>', "\n";
		echo '<Telephone>'. tiptelXmlEsc($number) .'</Telephone>', "\n";
		echo '</DirectoryEntry>' ,"\n";
	}
}

echo '</TiptelIPPhoneDirectory>' ,"\n";

if (! headers_sent()) {
	header( 'Content-Type: application/xml' );
	header( 'Content-Length: '. (int)@ob_get_length() );
}
@ob_end_flush();

?>
