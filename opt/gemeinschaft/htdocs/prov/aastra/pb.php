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

# caution: earlier versions of Aastra firmware do not like
# indented XML

define( 'GS_VALID', true );  /// this is a parent file
require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
include_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/aastra-fns.php' );
include_once( GS_DIR .'inc/gettext.php' );
include_once( GS_DIR .'inc/string.php' );
$xml = '';

function _err( $msg='' )
{
	aastra_textscreen( 'Error', ($msg != '' ? $msg : 'Unknown error') );
	exit(1);
}

function _get_userid()
{
	global $_SERVER, $db;
	
	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$user_id = (int)$db->executeGetOne( 'SELECT `id` FROM `users` WHERE `current_ip`=\''. $db->escape($remote_addr) .'\'' );
	if ($user_id < 1) _err( 'Unknown user.' );
	return $user_id;
}

$type = trim( @$_REQUEST['t'] );
if (! in_array( $type, array('gs','prv','imported', 'gss','prvs'), true )) {
	$type = false;
}
$page  = (int)trim( @$_REQUEST['p'] );
$entry = (int)trim( @$_REQUEST['e'] );
$search = trim( @$_REQUEST['s'] );
$name_search = trim( @$_REQUEST['n'] );


$per_page = (int)gs_get_conf('GS_AASTRA_PROV_PB_NUM_RESULTS', 10);
$db = gs_db_slave_connect();

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


$url_aastra_pb = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'aastra/pb.php';


#################################### SEARCH SCREEN {
if ($search) {
	
	$xml = '<AastraIPPhoneInputScreen type = "string">' ."\n";
	$xml.= '<Title>'. __('Suchen') .'</Title>' ."\n";
	$xml.= '<Prompt>'. __('Name') .':</Prompt>' ."\n";
	$xml.= '<URL>'.$url_aastra_pb .'?t='.$type.'</URL>' ."\n";
	$xml.= '<Parameter>n</Parameter>' ."\n";
	$xml.= '<Default></Default>' ."\n";
	$xml.= '<SoftKey index="1">' ."\n";
	$xml.= '	<Label>'. __('OK') .'</Label>' ."\n";
	$xml.= '	<URI>SoftKey:Submit</URI>' ."\n";
	$xml.= '</SoftKey>' ."\n";
	$xml.= '<SoftKey index="4">' ."\n";
	$xml.= '	<Label>'. __('Abbrechen') .'</Label>' ."\n";
	$xml.= '	<URI>SoftKey:Exit</URI>' ."\n";
	$xml.= '</SoftKey>' ."\n";
	$xml.= '<SoftKey index="2">' ."\n";
	$xml.= '	<Label>&lt;--</Label>' ."\n";
	$xml.= '	<URI>SoftKey:BackSpace</URI>' ."\n";
	$xml.= '</SoftKey>' ."\n";
	$xml.= '</AastraIPPhoneInputScreen>' ."\n";
	
}
#################################### SEARCH SCREEN }


#################################### INITIAL SCREEN {
elseif (! $type) {
	
	$xml = '<AastraIPPhoneTextMenu destroyOnExit="yes" LockIn="no" style="none">' ."\n";
	$xml.= '<Title>'. __('Telefonbuch') .'</Title>' ."\n";
	
	foreach ($typeToTitle as $key => $title) {
		$xml.= '<MenuItem>' ."\n";
		$xml.= '	<Prompt>'. $title .'</Prompt>' ."\n";
		$xml.= '	<URI>'. $url_aastra_pb .'?t='.$key .'</URI>' ."\n";
		//$xml.= '	<Selection>0&amp;menu_pos=1</Selection>' ."\n";
		$xml.= '</MenuItem>' ."\n";
	}
	
	$xml.= '<SoftKey index="1">' ."\n";
	$xml.= '	<Label>'. __('OK') .'</Label>' ."\n";
	$xml.= '	<URI>SoftKey:Select</URI>' ."\n";
	$xml.= '</SoftKey>' ."\n";
	$xml.= '<SoftKey index="4">' ."\n";
	$xml.= '	<Label>'. __('Abbrechen') .'</Label>' ."\n";
	$xml.= '	<URI>SoftKey:Exit</URI>' ."\n";
	$xml.= '</SoftKey>' ."\n";
	
	$xml.= '<SoftKey index="6">' ."\n";
	$xml.= '	<Label>&gt;&gt;</Label>' ."\n";
	$xml.= '	<URI>SoftKey:Select</URI>' ."\n";
	$xml.= '</SoftKey>' ."\n";
	$xml.= '</AastraIPPhoneTextMenu>' ."\n";
	
	
}
#################################### INITIAL SCREEN }


#################################### INTERNAL PHONEBOOK {
elseif ($type==='gs') {
	
	$search_url = 'name='. urlEncode($name_search);
	
	$name_sql = str_replace(
		array( '*', '?' ),
		array( '%', '_' ),
		$name_search
	) .'%';
	
	$query =
'SELECT SQL_CALC_FOUND_ROWS
	`u`.`id` `id`, `u`.`lastname` `ln`, `u`.`firstname` `fn`, `s`.`name` `ext`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE
	`u`.`nobody_index` IS NULL AND (
	`u`.`lastname` LIKE _utf8\''. $db->escape($name_sql) .'\' COLLATE utf8_unicode_ci
	)
ORDER BY `u`.`lastname`, `u`.`firstname`
LIMIT '. ($page * (int)$per_page) .','. (int)$per_page;
	
	$rs = $db->execute($query);
	$num_total = @$db->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	
	if ($name_search) $page_title = $name_search;
	else $page_title = $typeToTitle[$type];
	if ($num_pages > 1) $page_title.= ' '.($page+1).'/'.$num_pages;
	
	$rs = $db->execute($query);
	if ($rs->numRows() !== 0) {
		
		$xml = '<AastraIPPhoneTextMenu destroyOnExit="yes" LockIn="no" style="none" cancelAction="'. $url_aastra_pb .'">' ."\n";
		$xml.= '<Title>'. $page_title .'</Title>' ."\n";
		while ($r = $rs->fetchRow()) {
			$name = $r['ln'] .( strLen($r['fn'])>0 ? (', '.$r['fn']) : '' );
			$number = $r['ext'];
			$xml.= '<MenuItem>' ."\n";
			$xml.= '	<Prompt>'. htmlEnt($name) .' - '. htmlEnt($number) .'</Prompt>' ."\n";
			$xml.= '	<Dial>'. $number .'</Dial>' ."\n";
			$xml.= '	<URI>'. $url_aastra_pb .'?t=gss&amp;e='.$r['id'] .'</URI>' ."\n";
			$xml.= '</MenuItem>' ."\n";
		}
		
		$xml.= '<SoftKey index="1">' ."\n";
		$xml.= '	<Label>'. __('OK') .'</Label>' ."\n";
		$xml.= '	<URI>SoftKey:Select</URI>' ."\n";
		$xml.= '</SoftKey>' ."\n";
		$xml.= '<SoftKey index="2">' ."\n";
		$xml.= '	<Label>'. __('Anrufen') .'</Label>' ."\n";
		$xml.= '	<URI>SoftKey:Dial2</URI>' ."\n";
		$xml.= '</SoftKey>' ."\n";
		$xml.= '<SoftKey index="4">' ."\n";
		$xml.= '	<Label>'. __('Abbrechen') .'</Label>' ."\n";
		$xml.= '	<URI>SoftKey:Exit</URI>' ."\n";
		$xml.= '</SoftKey>' ."\n";
		$xml.= '	<SoftKey index="5">' ."\n";
		$xml.= '	<Label>'. __('Suchen') .'</Label>' ."\n";
		$xml.= '<URI>'. $url_aastra_pb .'?t=gs&amp;s=1</URI>' ."\n";
		$xml.= '</SoftKey>' ."\n";
		
		if ($page > 0) {
			$xml.= '<SoftKey index="3">' ."\n";
			$xml.= '	<Label>&lt;&lt;'.($page).'</Label>' ."\n";
			$xml.= '	<URI>'. $url_aastra_pb .'?t=gs&amp;p='.($page-1).'&amp;n='.$name_search .'</URI>' ."\n";
			$xml.= '</SoftKey>' ."\n";
		}
		if ($page < $num_pages-1) {
			$xml.= '<SoftKey index="6">' ."\n";
			$xml.= '	<Label>&gt;&gt;'.($page+2).'</Label>' ."\n";
			$xml.= '	<URI>'. $url_aastra_pb .'?t=gs&amp;p='.($page+1).'&amp;n='.$name_search .'</URI>' ."\n";
			$xml.= '</SoftKey>' ."\n";
		}
		
		$xml.= '</AastraIPPhoneTextMenu>' ."\n";
		
	}
	else {
		if ($name_search) {
			aastra_textscreen(
				__('Nicht gefunden'),
				sprintF(__('Eintrag &quot;%s&quot; nicht gefunden.'), $name_search)
				);
		} else {
			aastra_textscreen(
				$page_title,
				__('Kein Eintrag'));
		}
	}
	
	
}
#################################### INTERNAL PHONEBOOK }


#################################### PRIVATE PHONEBOOK {
elseif ($type==='prv') {
	
	$search_url = 'name='. urlEncode($name_search);
	
	$name_sql = str_replace(
		array( '*', '?' ),
		array( '%', '_' ),
		$name_search
	) .'%';
	
	//$user_id = 31;
	$user_id = _get_userid();
	
	$query =
'SELECT SQL_CALC_FOUND_ROWS
	`id`, `lastname` `ln`, `firstname` `fn`, `number`
FROM
	`pb_prv`
WHERE
	`user_id`='. $user_id .' AND (
	`lastname` LIKE _utf8\''. $db->escape($name_sql) .'\' COLLATE utf8_unicode_ci
	)
ORDER BY `lastname`, `firstname`
LIMIT '. ($page * (int)$per_page) .','. (int)$per_page;
	
	$rs = $db->execute($query);
	$num_total = @$db->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	
	if ($name_search) $page_title = $name_search;
	else $page_title = $typeToTitle[$type];
	if ($num_pages > 1) $page_title.= ' '.($page+1).'/'.$num_pages;
	
	if ($rs && $rs->numRows() !== 0) {
		
		$xml = '<AastraIPPhoneTextMenu destroyOnExit="yes" LockIn="no" style="none" cancelAction="'. $url_aastra_pb .'">' ."\n";
		$xml.= '<Title>'. $page_title .'</Title>' ."\n";
		
		while ($r = $rs->fetchRow()) {
			$name = $r['ln'] .( strLen($r['fn'])>0 ? (', '.$r['fn']) : '' );
			$xml.= '<MenuItem>' ."\n";
			$xml.= '	<Prompt>'. htmlEnt($name) .' - '. htmlEnt($r['number']) .'</Prompt>' ."\n";
			$xml.= '	<Dial>'. $r['number'] .'</Dial>' ."\n";
			$xml.= '	<URI>'. $url_aastra_pb .'?t=prvs&amp;e='.$r['id'] .'</URI>' ."\n";
			$xml.= '</MenuItem>' ."\n";
		}
		
		$xml.= '<SoftKey index="1">' ."\n";
		$xml.= '	<Label>'. __('OK') .'</Label>' ."\n";
		$xml.= '	<URI>SoftKey:Select</URI>' ."\n";
		$xml.= '</SoftKey>' ."\n";
		$xml.= '<SoftKey index="2">' ."\n";
		$xml.= '	<Label>'. __('Anrufen') .'</Label>' ."\n";
		$xml.= '	<URI>SoftKey:Dial2</URI>' ."\n";
		$xml.= '</SoftKey>' ."\n";
		$xml.= '<SoftKey index="4">' ."\n";
		$xml.= '	<Label>'. __('Abbrechen') .'</Label>' ."\n";
		$xml.= '	<URI>SoftKey:Exit</URI>' ."\n";
		$xml.= '</SoftKey>' ."\n";
		$xml.= '<SoftKey index="5">' ."\n";
		$xml.= '	<Label>'. __('Suchen') .'</Label>' ."\n";
		$xml.= '	<URI>'. $url_aastra_pb .'?t='.$type.'&amp;s=1</URI>' ."\n";
		$xml.= '</SoftKey>' ."\n";
		
		if ($page > 0) {
			$xml.= '<SoftKey index="3">' ."\n";
			$xml.= '	<Label>&lt;&lt;'.($page).'</Label>' ."\n";
			$xml.= '	<URI>'. $url_aastra_pb .'?t=prv&amp;p='.($page-1).'&amp;n='.$name_search .'</URI>' ."\n";
			$xml.= '</SoftKey>' ."\n";
		}
		if ($page < $num_pages-1) {
			$xml.= '<SoftKey index="6">' ."\n";
			$xml.= '	<Label>&gt;&gt;'.($page+2).'</Label>' ."\n";
			$xml.= '	<URI>'. $url_aastra_pb .'?t=prv&amp;p='.($page+1).'&amp;n='.$name_search .'</URI>' ."\n";
			$xml.= '</SoftKey>' ."\n";
		}
		
		$xml.= '</AastraIPPhoneTextMenu>' ."\n";
		
	}
	else {
		if ($name_search) {
			aastra_textscreen(
				__('Nicht gefunden'),
				sprintF(__('Eintrag &quot;%s&quot; nicht gefunden.'), $name_search)
				);
		} else {
			aastra_textscreen(
				$page_title,
				__('Kein Eintrag'));
		}
	}
	
	
}
#################################### PRIVATE PHONEBOOK }


#################################### PRIVATE PHONEBOOK (calling?) {
elseif ($type==='prvs') {
	
	$user_id = _get_userid();
	
	$xml = '<AastraIPPhoneFormattedTextScreen destroyOnExit="yes" cancelAction="'. $url_aastra_pb .'?t=prv">' ."\n";
	
	$query =
'SELECT `id`, `lastname` `ln`, `firstname` `fn`, `number`
FROM
	`pb_prv`
WHERE
	`user_id`='. $user_id.'
AND
	`id`='. $entry;
	
	$rs = $db->execute($query);
	if ($rs->numRows() !== 0) {
		$r = $rs->fetchRow();
		$xml.= '<Line Align="left">'. $r['ln'].' '.$r['fn'] .'</Line>' ."\n";
		$xml.= '<Line Align="right" Size="double">'. $r['number'] .'</Line>' ."\n";
	}
	
	$xml.= '<SoftKey index="1">' ."\n";
	$xml.= '	<Label>'. __('OK') .'</Label>' ."\n";
	$xml.= 	'<URI>SoftKey:Select</URI>' ."\n";
	$xml.= '</SoftKey>' ."\n";
	$xml.= '<SoftKey index="2">' ."\n";
	$xml.= '	<Label>'. __('Anrufen') .'</Label>' ."\n";
	$xml.= '	<URI>Dial:'.$r['number'].'</URI>' ."\n";
	$xml.= '</SoftKey>' ."\n";
	$xml.= '<SoftKey index="4">' ."\n";
	$xml.= '	<Label>'. __('Abbrechen') .'</Label>' ."\n";
	$xml.= '	<URI>SoftKey:Exit</URI>' ."\n";
	$xml.= '</SoftKey>' ."\n";
	$xml.= '</AastraIPPhoneFormattedTextScreen>' ."\n";
	
}
#################################### PRIVATE PHONEBOOK (calling?) }


#################################### INTERNAL PHONEBOOK (calling?) {
elseif ($type==='gss') {
	
	$user_id = _get_userid();
	
	$xml = '<AastraIPPhoneFormattedTextScreen destroyOnExit="yes" cancelAction="'. $url_aastra_pb .'?t=gs">' ."\n";
	
	$query =
'SELECT `u`.`id` `id`, `u`.`lastname` `ln`, `u`.`firstname` `fn`, `s`.`name` `number`
FROM
	`users` `u` JOIN
`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE
	`u`.`id`='.$entry;
	
	$rs = $db->execute($query);
	if ($rs->numRows() !== 0) {
		$r = $rs->fetchRow();
		$xml.= '<Line Align="left">'. $r['ln'].' '.$r['fn'] .'</Line>' ."\n";
		$xml.= '<Line Align="right" Size="double">'. $r['number'] .'</Line>' ."\n";
	}
	
	$xml.= '<SoftKey index="1">' ."\n";
	$xml.= '	<Label>'. __('OK') .'</Label>' ."\n";
	$xml.= '	<URI>SoftKey:Select</URI>' ."\n";
	$xml.= '</SoftKey>' ."\n";
	$xml.= '<SoftKey index="2">' ."\n";
	$xml.= '	<Label>'. __('Anrufen') .'</Label>' ."\n";
	$xml.= '	<URI>Dial:'.$r['number'].'</URI>' ."\n";
	$xml.= '</SoftKey>' ."\n";
	$xml.= '<SoftKey index="4">' ."\n";
	$xml.= '	<Label>'. __('Abbrechen') .'</Label>' ."\n";
	$xml.= '	<URI>SoftKey:Exit</URI>' ."\n";
	$xml.= '</SoftKey>' ."\n";
	$xml.= '<SoftKey index="3">' ."\n";
	$xml.= '	<Label>&lt;&lt;</Label>' ."\n";
	$xml.= '	<URI>'. $url_aastra_pb .'?t=gs</URI>' ."\n";
	$xml.= '</SoftKey>' ."\n";
	$xml.= '<SoftKey index="6">' ."\n";
	$xml.= '	<Label>&gt;&gt;</Label>' ."\n";
	$xml.= '	<URI>SoftKey:Select</URI>' ."\n";
	$xml.= '</SoftKey>' ."\n";
	$xml.= '</AastraIPPhoneFormattedTextScreen>' ."\n";
	
}
#################################### INTERNAL PHONEBOOK (calling?) }


#################################### IMPORTED PHONEBOOK {
elseif ($type==='imported') {
	//FIXME
}
#################################### IMPORTED PHONEBOOK }


aastra_transmit_str( $xml );

?>