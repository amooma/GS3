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
include_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/gettext.php' );
include_once( GS_DIR .'inc/string.php' );
include_once( GS_DIR .'inc/group-fns.php' );

header( 'Content-Type: application/xml; charset=utf-8' );
header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );

function tiptelXmlEsc( $str )
{
	return htmlEnt( $str );
}

function _ob_send()
{
	if (! headers_sent()) {
		header( 'Content-Type: application/xml; charset=utf-8' );
		header( 'Content-Length: '. (int)@ob_get_length() );
	}
	@ob_end_flush();
	die();
}

function _err( $msg='' )
{
	@ob_end_clean();
	ob_start();
	echo
		'<?','xml version="1.0" encoding="utf-8"?','>', "\n",
		'<TiptelIPPhoneTextScreen>', "\n",
			'<Title>', __('Fehler'), '</Title>', "\n",
			'<Text>', tiptelXmlEsc( __('Fehler') .': '. $msg ), '</Text>', "\n",
		'</TiptelIPPhoneTextScreen>', "\n";
	_ob_send();
}

function getUserID( $ext )
{
	global $db;
	
	if (! preg_match('/^\d+$/', $ext))
		_err( 'Invalid username' );
	
	$user_id = (int)$db->executeGetOne( 'SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`=\''. $db->escape($ext) .'\'' );
	if ($user_id < 1)
		_err( 'Unknown user' );
	return $user_id;
}


if (! gs_get_conf('GS_TIPTEL_PROV_ENABLED')) {
	gs_log( GS_LOG_DEBUG, "Tiptel provisioning not enabled" );
	_err( 'Not enabled' );
}

$type = trim( @$_REQUEST['t'] );
if (! in_array( $type, array('gs','prv','imported'), true )) {
	$type = false;
}

$search = (int)trim( @$_REQUEST['s'] );
$query  = trim( @$_REQUEST['q'] );


$db = gs_db_slave_connect();


/*
$typeToTitle = array(
	'imported' => "Firma (aus LDAP)",
	'gs'       => "Firma",  # should normally be "Gemeinschaft"
	'prv'      => "Pers\xC3\xB6nlich",
);
*/
$tmp = array(
	15=>array(
		'k' => 'gs' ,
		'v' => gs_get_conf('GS_PB_INTERNAL_TITLE', __("Intern")) ),
	25=>array(
		'k' => 'prv',
		'v' => gs_get_conf('GS_PB_PRIVATE_TITLE' , __("Pers\xC3\xB6nlich")) )
);
if (gs_get_conf('GS_PB_IMPORTED_ENABLED')) {
	$pos = (int)gs_get_conf('GS_PB_IMPORTED_ORDER', 9) * 10;
	$tmp[$pos] = array(
		'k' => 'imported',
		'v' => gs_get_conf('GS_PB_IMPORTED_TITLE', __("Extern"))
	);
}
kSort($tmp);
foreach ($tmp as $arr) {
	$typeToTitle[$arr['k']] = $arr['v'];
}


$url_snom_pb = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'tiptel/pb.php';



#################################### SEARCH SCREEN {
if ($search === 1) {

	$user = trim( @$_REQUEST['u'] );
	$user_id = getUserID( $user );
	
	ob_start();
	
	echo '<?','xml version="1.0" encoding="utf-8"?','>',"\n";
	echo 
		'<TiptelIPPhoneInputScreen>', "\n",
		'<Title>Suchen</Title>', "\n",
		'<URL>', tiptelXmlEsc($url_snom_pb.'?u='.$user.'&t='.$type) ,'</URL>', "\n",
		'<InputField type="string" password="no" editable="yes">', "\n",
			'<Prompt>Suche nach</Prompt>', "\n",
			'<Parameter>q</Parameter>',"\n",
		'</InputField>', "\n",
		'</TiptelIPPhoneInputScreen>', "\n";

	_ob_send();
}
#################################### SEARCH SCREEN }



#################################### INITIAL SCREEN {
if (! $type) {
	
	$user = trim( @$_REQUEST['u'] );
	$user_id = getUserID( $user );
	
	$user_groups       = gs_group_members_groups_get(array($user_id), 'user');
	$permission_groups = gs_group_permissions_get($user_groups, 'phonebook_user');
	$group_members     = gs_group_members_get($permission_groups);
	
	ob_start();
	echo
		'<?','xml version="1.0" encoding="utf-8"?','>', "\n",
		'<TiptelIPPhoneTextMenu>', "\n",
			'<Title>', __('Telefonbuch') ,'</Title>', "\n\n";
	foreach ($typeToTitle as $t => $title) {
		$cq = 'SELECT COUNT(*) FROM ';
		switch ($t) {
		case 'gs'      : $cq .= '`users` WHERE `id` IN ('.implode(',',$group_members).') AND `id`!='.$user_id; break;
		case 'imported': $cq .= '`pb_ldap`'                           ; break;
		case 'prv'     : $cq .= '`pb_prv` WHERE `user_id`='. $user_id ; break;
		default        : $cq  = false;
		}
		$c = $cq ? (' ('. (int)@$db->executeGetOne( $cq ) .')') : '';
		echo
			'<MenuItem>', "\n",
				'<Prompt>', tiptelXmlEsc($title), $c, '</Prompt>', "\n",
				'<URI>', tiptelXmlEsc($url_snom_pb), '</URI>', "\n",
				'<Selection>', tiptelXmlEsc('0&u='.$user.'&t='.$t),'</Selection>', "\n",
			'</MenuItem>', "\n\n";
	}
	echo '</TiptelIPPhoneTextMenu>', "\n";
	_ob_send();
	
}
#################################### INITIAL SCREEN }



#################################### PHONEBOOK {
if ($type === 'gs') {
	
	$user = trim( @ $_REQUEST['u'] );
	$user_id = getUserID( $user );
	
	$user_groups       = gs_group_members_groups_get(array($user_id), 'user');
	$permission_groups = gs_group_permissions_get($user_groups, 'phonebook_user');
	$group_members     = gs_group_members_get($permission_groups);
	
	$where = '';
	if (strLen($query) > 0)
		$where = 'AND (`u`.`lastname` LIKE \'%'. $query .'%\' OR `u`.`firstname` LIKE \'%'. $query .'%\')';
	
	$query =
'SELECT `u`.`lastname` `ln`, `u`.`firstname` `fn`, `s`.`name` `ext`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE
	`u`.`id` IN ('.implode(',',$group_members).') AND
	`u`.`id`!='.$user_id.'
	'. ($where ? $where : '') .'
ORDER BY `u`.`lastname`, `u`.`firstname`';
//LIMIT '. $num_results;
	
}
elseif ($type === 'prv') {
	
	$user = trim( @ $_REQUEST['u'] );
	$user_id = getUserID( $user );
	
	$where = '';
	if (strLen($query) > 0)
		$where = 'AND (`pb`.`lastname` LIKE \'%'. $query .'%\' OR `pb`.`firstname` LIKE \'%'. $query .'%\')';
	
	$query =
'SELECT `pb`.`lastname` `ln`, `pb`.`firstname` `fn`, `pb`.`number` `ext`
FROM `pb_prv` `pb`
WHERE
	`pb`.`user_id`='.$user_id.'
	'. ($where ? $where : '') .'
ORDER BY `pb`.`lastname`, `pb`.`firstname`';
	
}
elseif ($type === 'imported') {
	
	$query =
'SELECT `lastname` `ln`, `firstname` `fn`, `number` `ext`
FROM `pb_ldap`
ORDER BY `lastname`, `firstname`';
	
}
	
if (in_array( $type, array('gs','prv','imported'), true )) {
	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>',"\n";
	
	$rs = $db->execute($query);
	if ( $rs && $rs->numRows() !== 0 ) {
		
		echo
			'<TiptelIPPhoneDirectory>', "\n",
				'<Title>', tiptelXmlEsc( $typeToTitle[$type] ), '</Title>', "\n";
		while ($r = $rs->fetchRow()) {
			$name = $r['ln'] .( strLen($r['fn'])>0 ? (', '.$r['fn']) : '' );
			$number = $r['ext'];
			echo
				'<MenuItem>',
					'<Prompt>', tiptelXmlEsc( $name ) ,' (', tiptelXmlEsc( $number ) ,')</Prompt>',
					'<URI>', $number ,'</URI>',
				'</MenuItem>', "\n";
		}
		echo
			"\n",
			'<SoftKey index="1">', "\n",
				'<Label>', tiptelXmlEsc(__("Zur\xC3\xBCck")), '</Label>', "\n",
				'<URI>SoftKey:Exit</URI>', "\n",
			'</SoftKey>', "\n",
			"\n",
			'<SoftKey index="2">', "\n",
				'<Label>', tiptelXmlEsc(__('Suchen')), '</Label>', "\n",
				'<URI>', tiptelXmlEsc($url_snom_pb.'?u='.$user.'&t='.$type.'&s=1'), '</URI>', "\n",
			'</SoftKey>', "\n",
			"\n",
			'<SoftKey index="3">', "\n",
				'<Label></Label>', "\n",
				'<URI></URI>', "\n",
			'</SoftKey>', "\n",
			"\n",
			'<SoftKey index="4">', "\n",
				'<Label>', tiptelXmlEsc(__("W\xC3\xA4hlen")), '</Label>', "\n",
				'<URI>SoftKey:Dial</URI>', "\n",
			'</SoftKey>', "\n";
		
		echo '</TiptelIPPhoneDirectory>', "\n";
		
	} else {
		
		echo
			'<TiptelIPPhoneTextScreen>', "\n",
				'<Title>', tiptelXmlEsc( $typeToTitle[$type] ), '</Title>', "\n",
				'<Text>', tiptelXmlEsc( __("Dieses Telefonbuch enth\xC3\xA4lt keine Eintr\xC3\xA4ge.") ), '</Text>', "\n",
			'</TiptelIPPhoneTextScreen>', "\n";
		
	}
	_ob_send();
}
#################################### PHONEBOOK }


?>