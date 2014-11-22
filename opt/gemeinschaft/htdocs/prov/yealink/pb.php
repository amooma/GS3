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

function yealinkXmlEsc( $str )
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
		'<YealinkIPPhoneTextScreen>', "\n",
			'<Title>', yealinkXmlEsc(__('Fehler')), '</Title>', "\n",
			'<Text>', yealinkXmlEsc( __('Fehler') .': '. $msg ), '</Text>', "\n",
		'</YealinkIPPhoneTextScreen>', "\n";
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
	gs_log( GS_LOG_DEBUG, "Yealink provisioning not enabled" );
	_err( 'Not enabled' );
}

$type = trim( @$_REQUEST['t'] );
if (! in_array( $type, array('gs','prv','imported'), true )) {
	$type = false;
}
$page = (int)trim( @$_REQUEST['p'] );
$search = (int)trim( @$_REQUEST['s'] );
$query  = trim( @$_REQUEST['q'] );


$per_page = 10;
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


$url_yealink_pb = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'yealink/pb.php';



#################################### SEARCH SCREEN {
if ($search === 1) {

	$user = trim( @$_REQUEST['u'] );
	$user_id = getUserID( $user );
	
	ob_start();
	
	echo '<?','xml version="1.0" encoding="utf-8"?','>',"\n";
	echo 
		'<YealinkIPPhoneInputScreen>', "\n",
		'<Title>', yealinkXmlEsc(__('Suchen')), '</Title>', "\n",
		'<URL>', yealinkXmlEsc($url_yealink_pb.'?u='.$user.'&t='.$type) ,'</URL>', "\n",
		'<InputField type="string" password="no" editable="yes">', "\n",
			'<Prompt>', yealinkXmlEsc(__('Suche nach')), '</Prompt>', "\n",
			'<Parameter>q</Parameter>',"\n",
		'</InputField>', "\n",
		'</YealinkIPPhoneInputScreen>', "\n";

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
		'<YealinkIPPhoneMenu>', "\n",
			'<Title>', yealinkXmlEsc(__('Telefonbuch')) ,'</Title>', "\n\n";
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
				'<Name>', yealinkXmlEsc($title), $c, '</Name>', "\n",
				'<URL>', yealinkXmlEsc($url_yealink_pb.'?u='.$user.'&t='.$t), '</URL>', "\n",
			'</MenuItem>', "\n\n";
	}
	echo '</YealinkIPPhoneMenu>', "\n";
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
'SELECT SQL_CALC_FOUND_ROWS `u`.`lastname` `ln`, `u`.`firstname` `fn`, `s`.`name` `ext`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE
	`u`.`id` IN ('.implode(',',$group_members).') AND
	`u`.`id`!='.$user_id.'
	'. ($where ? $where : '') .'
ORDER BY `u`.`lastname`, `u`.`firstname`
LIMIT '. ($page * (int)$per_page) .','. (int)$per_page;
	
}
elseif ($type === 'prv') {
	
	$user = trim( @ $_REQUEST['u'] );
	$user_id = getUserID( $user );
	
	$where = '';
	if (strLen($query) > 0)
		$where = 'AND (`pb`.`lastname` LIKE \'%'. $query .'%\' OR `pb`.`firstname` LIKE \'%'. $query .'%\')';
	
	$query =
'SELECT SQL_CALC_FOUND_ROWS `pb`.`lastname` `ln`, `pb`.`firstname` `fn`, `pb`.`number` `ext`
FROM `pb_prv` `pb`
WHERE
	`pb`.`user_id`='.$user_id.'
	'. ($where ? $where : '') .'
ORDER BY `pb`.`lastname`, `pb`.`firstname`
LIMIT '. ($page * (int)$per_page) .','. (int)$per_page;
	
}
elseif ($type === 'imported') {
	
	$query =
'SELECT SQL_CALC_FOUND_ROWS `lastname` `ln`, `firstname` `fn`, `number` `ext`
FROM `pb_ldap`
ORDER BY `lastname`, `firstname`
LIMIT '. ($page * (int)$per_page) .','. (int)$per_page;
	
}
	
if (in_array( $type, array('gs','prv','imported'), true )) {
	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>',"\n";
	
	$rs = $db->execute($query);
	$num_total = @$db->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	
	$page_title = $typeToTitle[$type];
	if ($num_pages > 1) $page_title.= ' '.($page+1).'/'.$num_pages;
	
	if ( $rs && $rs->numRows() !== 0 ) {
		
		echo
			'<YealinkIPPhoneDirectory>', "\n",
				'<Title>', yealinkXmlEsc( $page_title ), '</Title>', "\n";
		while ($r = $rs->fetchRow()) {
			$name = $r['ln'] .( strLen($r['fn'])>0 ? (', '.$r['fn']) : '' );
			$number = $r['ext'];
			echo
				'<DirectoryEntry>',
					'<Name>', yealinkXmlEsc( $name ) ,' (', yealinkXmlEsc( $number ) ,')</Name>',
					'<Telephone>', $number ,'</Telephone>',
				'</DirectoryEntry>', "\n";
		}
		echo "\n";
		
		echo
			'<SoftKey index="1">', "\n",
				'<Name>', yealinkXmlEsc(__("Zur\xC3\xBCck")), '</Name>', "\n",
				'<URL>SoftKey:Exit</URL>', "\n",
			'</SoftKey>', "\n";
		
		echo '<SoftKey index="2">', "\n";
		if($page > 0) {
			echo '<Name>', yealinkXmlEsc('<< '.$page), '</Name>', "\n";
			echo '<URL>', yealinkXmlEsc($url_yealink_pb.'?u='.$user.'&t='.$type.'&p='.($page-1)), '</URL>', "\n";
		} else {
			echo '<Name>', yealinkXmlEsc(__('Suchen')), '</Name>', "\n";
			echo '<URL>', yealinkXmlEsc($url_yealink_pb.'?u='.$user.'&t='.$type.'&s=1'), '</URL>', "\n";
		}
		echo '</SoftKey>', "\n";
		
		echo '<SoftKey index="3">', "\n";
		if($page < $num_pages-1 ) {
			echo '<Name>', yealinkXmlEsc(($page+2).' >>'), '</Name>', "\n";
			echo '<URL>', yealinkXmlEsc($url_yealink_pb.'?u='.$user.'&t='.$type.'&p='.($page+1)), '</URL>', "\n";
		} else {
			echo '<Name></Name>', "\n";
			echo '<URL></URL>', "\n";
		}
		echo '</SoftKey>', "\n";
		
		echo
			'<SoftKey index="4">', "\n",
				'<Name>', yealinkXmlEsc(__("W\xC3\xA4hlen")), '</Name>', "\n",
				'<URL>SoftKey:Dial</URL>', "\n",
			'</SoftKey>', "\n";
		
		echo '</YealinkIPPhoneDirectory>', "\n";
		
	} else {
		
		echo
			'<YealinkIPPhoneTextScreen>', "\n",
				'<Title>', yealinkXmlEsc( $typeToTitle[$type] ), '</Title>', "\n",
				'<Text>', yealinkXmlEsc( __("Dieses Telefonbuch enth\xC3\xA4lt keine Eintr\xC3\xA4ge.") ), '</Text>', "\n",
			'</YealinkIPPhoneTextScreen>', "\n";
		
	}
	_ob_send();
}
#################################### PHONEBOOK }


?>