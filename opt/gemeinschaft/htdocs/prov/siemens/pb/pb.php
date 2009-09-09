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
* Soeren Sprenger <soeren.sprenger@amooma.de>
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
require_once( dirName(__FILE__) .'/../../../../inc/conf.php' );
include_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/gettext.php' );
require_once( GS_DIR .'inc/string.php' );

header( 'Content-Type: text/xml; charset=utf-8' );
header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );

$xml_buf = '';

function xml( $string )
{
	global $xml_buf;
	$xml_buf .= $string."\n";
}

function xml_output()
{
	global $xml_buf;
	@header( 'X-Powered-By: Gemeinschaft' );
	@header( 'Content-Type: text/xml; charset=utf-8' );
	@header( 'Content-Length: '. strLen($xml_buf) );
	echo $xml_buf;
	exit;
}

function dial_number( $number )
{
	xml('<'.'?xml version="1.0" encoding="UTF-8" ?'.'>');
	xml('<IppDisplay>');
	xml('<IppScreen ID="1" HiddenCount="0" CommandCount="0">');
	xml('  <IppKey Keypad="YES" SendKeys="YES" BufferKeys="NO" BufferLength="0" TermKey="" UrlKey="key" />');
	xml('  <IppAlert Type="INFO" Delay="3000">');
	xml('    <Title>'. htmlEnt(__('Anruf')) .'</Title>');
	xml('    <Text>'. htmlEnt(__('Rufe an:')) .' '. htmlEnt($number) .'</Text>');
	xml('    <Image></Image>');
	xml('  </IppAlert>');
	xml('  <IppAction Type="MAKECALL">');
	xml('    <Number>'. htmlEnt($number) .'</Number>');
	xml('  </IppAction>');
	xml('</IppScreen>');
	xml('</IppDisplay>');
	xml_output();
}

function write_alert( $message, $alert_type='ERROR' )
{
	xml('<'.'?xml version="1.0" encoding="UTF-8" ?'.'>');
	xml('<IppDisplay>');
	xml('<IppScreen ID="1" HiddenCount="0" CommandCount="0">');
	xml('  <IppAlert Type="'.htmlEnt($alert_type).'" Delay="5000">');
	xml('    <Title>'. htmlEnt(__('Fehler')) .'</Title>');
	xml('    <Text>'. htmlEnt($message) .'</Text>');
	xml('    <Image></Image>');
	xml('  </IppAlert>');
	xml('</IppScreen>');
	xml('</IppDisplay>');
	xml_output();
}

$keyPatterns = array(  # must be valid in MySQL!
	'0' => "[ -.,_0]",
	'1' => "[ -.,_1]",
	'2' => "[abc2\xC3\xA4]",   # a dieresis
	'3' => "[def3\xC3\xA9]",   # e acute
	'4' => "[ghi4\xC3\xAF]",   # i dieresis
	'5' => "[jkl5]",
	'6' => "[mno6\xC3\xB6]",   # o dieresis
	'7' => "[pqrs7\xC3\x9F]",  # sharp s
	'8' => "[tuv8\xC3\xBC]",   # u dieresis
	'9' => "[wxyz9]"
);


$user         = trim(@$_REQUEST['user'       ]);
$phonenumber  = trim(@$_REQUEST['phonenumber']);
$search       = trim(@$_REQUEST['search'     ]);
$name_search  = trim(@$_REQUEST['name'       ]);
$ip_addr      = trim(@$_REQUEST['ipaddress'  ]);
$keys         = trim(@$_REQUEST['keys'       ]);
$key          = trim(@$_REQUEST['key'        ]);
$type         = trim(@$_REQUEST['type'       ]);
$tab          = trim(@$_REQUEST['tab'        ]);


# workaround for an OpenStage bug. it puts "#" in the URL unescaped
#
if ($user.$phonenumber == '') {
	$url = explode('?', baseName($_SERVER['REQUEST_URI']));
	$params = explode('&',@$url[1]);
	foreach ($params as $param) {
		$values = $params = explode('=',$param);
		switch ($values[0]) {
			case 'key'        : $key         = @$values[1]; break;
			case 'keys'       : $keys        = @$values[1]; break;
			case 'user'       : $user        = @$values[1]; break;
			case 'phonenumber': $phonenumber = @$values[1]; break;
			case 'tab'        : $tab         = @$values[1]; break;
			case 'type'       : $type        = @$values[1]; break;
		}
	}
}


if     ($key === '*') $keys = @subStr($keys,0,-1);
elseif ($key === '#') $keys = '';
else                  $keys .= $key;

if ($type === 'none') {
	$keys = '';
}



if ($tab) {
	$tab = @preg_replace('/^internal:/', '', $tab);  // ?
	switch ($tab) {
		case 'XMLPhonebook'  : $type = 'prv'      ; break;
		case 'XMLPhonebook_2': $type = 'gs'       ; break;
		case 'XMLPhonebook_3': $type = 'imported' ; break;
	}
}

if (! $user) $user = $phonenumber;

$url_prov_siemens = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'siemens/';
$url     = $url_prov_siemens .'pb/pb.php';
$img_url = $url_prov_siemens .'img/';

if (! preg_match('/^\d+$/', $user)) {
	write_alert( 'Unknown user.' );
}

if (! in_array( $type, array('gs','prv','imported'), true )) {
	$type = false;
}

$dial = trim(@$_REQUEST['dial']);


$db = gs_db_slave_connect();


# get user_id
#
if ($ip_addr != '')
	$user_id = (int)$db->executeGetOne( 'SELECT `id` FROM `users` WHERE `current_ip`=\''. $db->escape($ip_addr) .'\'' );
else $user_id = 0;

if ($user_id < 1) {
	$user_id = (int)$db->executeGetOne( 'SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`=\''. $db->escape($user) .'\'' );
	if ($user_id < 1) {
		write_alert( 'Unknown user.' );
	}
}



$tmp = array(
	15=>array('k' => 'gs' ,
			'v' => gs_get_conf('GS_PB_INTERNAL_TITLE', __("Intern")) ),
	25=>array('k' => 'prv',
			'v' => gs_get_conf('GS_PB_PRIVATE_TITLE' , __("Pers\xC3\xB6nlich")) )
);
if (gs_get_conf('GS_PB_IMPORTED_ENABLED')) {
	$pos = (int)gs_get_conf('GS_PB_IMPORTED_ORDER', 9) * 10;
	$tmp[$pos] = array(
			'k' => 'imported',
			'v' => gs_get_conf('GS_PB_IMPORTED_TITLE', __("Importiert"))
	);
}
kSort($tmp);
foreach ($tmp as $arr) {
	$typeToTitle[$arr['k']] = $arr['v'];
}



#########################################################
# Search
#########################################################

if ($search) {
	if (! $name_search) {
		xml('<'.'?xml version="1.0" encoding="UTF-8" ?'.'>');
		xml('<IppDisplay>');
		xml('<IppScreen ID="3" HiddenCount="3" CommandCount="2">');
		xml('  <IppForm ItemCount="1">');
		xml('    <Title>'. htmlEnt(__('Name suchen')) .':</Title>');
		xml('    <Url>'. htmlEnt($url) .'</Url>');
		xml('    <IppTextField MaxSize="30" Constraint="ANY" Default="'.htmlEnt($name_search).'" Key="name">');
		xml('      <Label>'. htmlEnt(__('Name')) .':</Label>');
		xml('      <Text>'. htmlEnt($name_search) .'</Text>');
		xml('    </IppTextField>');
		xml('  </IppForm>');
		xml('  <IppCommand Type="SELECT" Priority="0">');
		xml('    <Label>'. htmlEnt(__('Suchen')) .'</Label>');
		xml('    <ScreenID>1</ScreenID>');
		xml('  </IppCommand>');
		xml('  <IppCommand Type="SCREEN" Priority="0">');
		xml('    <Label>'. htmlEnt(__('Abbrechen')) .'</Label>');
		xml('    <ScreenID>1</ScreenID>');
		xml('  </IppCommand>');
		
		xml('  <IppHidden Type="VALUE" Key="user">');
		xml('    <Value>'.htmlEnt($user).'</Value>');
		xml('  </IppHidden>');
		xml('  <IppHidden Type="VALUE" Key="search">');
		xml('    <Value>'.htmlEnt($search).'</Value>');
		xml('  </IppHidden>');
		xml('  <IppHidden Type="VALUE" Key="tab">');
		xml('    <Value>'.htmlEnt($tab).'</Value>');
		xml('  </IppHidden>');
		xml('</IppScreen>');
		xml('</IppDisplay>');
	} else {
		$type = $search;
	}
}



#########################################################
# Dial
#########################################################

if ($dial) dial_number($dial);


#########################################################
# Static entry screen
#########################################################

if (! $type) {
	
	xml('<'.'?xml version="1.0" encoding="UTF-8" ?'.'>');
	xml('<IppDisplay>');
	xml('<IppScreen ID="1" HiddenCount="2" CommandCount="1">');
	xml('  <IppKey Keypad="YES" SendKeys="YES" BufferKeys="NO" BufferLength="0" TermKey="" UrlKey="key" />');
	xml('  <IppList Type="IMPLICIT" Count="'. count($typeToTitle) .'">');
	xml('    <Title>'. htmlEnt($user) .' - '. htmlEnt(__('Telefonbuch')) .'</Title>');
	xml('    <Url>'.$url.'</Url>');
	$i=0;
	foreach ($typeToTitle as $t => $title) {
		$i++;
		xml('    <Option ID="'.$i.'" Selected="'.($i===1 ?'TRUE':'FALSE').'" Key="type" Value="'.$t.'">');
		switch ($t) {
			case 'gs':
				$c = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `users` WHERE `nobody_index` IS NULL' );
				$image = $img_url.'contents.png';
				break;
			case 'prv':
				$c = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `pb_prv` WHERE `user_id`='. $user_id );
				$image = $img_url.'yast_sysadmin.png';
				break;
			case 'imported':
				$c = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `pb_ldap`' );
				$image = $img_url.'contents.png';
				break;
			default:
				$c = 0;
				$image = '';
		}
		xml('      <OptionText>'. htmlEnt($title) .' ('.$c.')' .'</OptionText>');
		xml('      <Image>'.htmlEnt($image).'</Image>');
		xml('    </Option>');
	}
	xml('  </IppList>');
	xml('  <IppHidden Type="VALUE" Key="user">');
	xml('    <Value>'.htmlEnt($user).'</Value>');
	xml('  </IppHidden>');
	xml('  <IppHidden Type="VALUE" Key="tab">');
	xml('    <Value>'.htmlEnt($tab).'</Value>');
	xml('  </IppHidden>');
	xml('</IppScreen>');
	xml('</IppDisplay>');
	
}


#########################################################
# Phone Books
#########################################################

else {
	$page = 0;
	$per_page = 15; # Number of phonebook entries sent to the phone.
	
	$name_sql = str_replace(
		array( '*', '?' ),
		array( '%', '_' ),
		$name_search
	) .'%';
	
	if ($keys) {
		$key_array = str_split($keys);
		$key_sql = ' AND `lastname` REGEXP \'^';
		foreach ($key_array as $search_key) {
			$key_sql .= $keyPatterns[$search_key];
		}
		$key_sql .= '\'';
	} else {
		$key_sql = '';
	}
	
	switch ($type) {
	case 'gs' :
		include_once( GS_DIR .'inc/group-fns.php' );
		$user_groups       = gs_group_members_groups_get(Array($user_id), 'user');
		$permission_groups = gs_group_permissions_get($user_groups, 'phonebook_user');
		$group_members     = gs_group_members_get($permission_groups);

		$query =
'SELECT SQL_CALC_FOUND_ROWS
	`u`.`id` `id`, `u`.`lastname` `ln`, `u`.`firstname` `fn`, `s`.`name` `number`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE
	`u`.`id` IN ('.implode(',', $group_members).') AND (
	`u`.`lastname` LIKE _utf8\''. $db->escape($name_sql) .'\' COLLATE utf8_unicode_ci
	) '.$key_sql.'
ORDER BY `u`.`lastname`, `u`.`firstname`
LIMIT '. ($page * (int)$per_page) .','. (int)$per_page;
		break;
	case 'prv' :
		$query =
'SELECT SQL_CALC_FOUND_ROWS
	`id`, `lastname` `ln`, `firstname` `fn`, `number`
FROM
	`pb_prv`
WHERE
	`user_id`='. $user_id .' AND (
	`lastname` LIKE _utf8\''. $db->escape($name_sql) .'\' COLLATE utf8_unicode_ci
	) '.$key_sql.'
ORDER BY `lastname`, `firstname`
LIMIT '. ($page * (int)$per_page) .','. (int)$per_page;
		break;
	case 'imported':
		$query =
'SELECT SQL_CALC_FOUND_ROWS
	`lastname` `ln`, `firstname` `fn`, `number`
FROM
	`pb_ldap`
WHERE
	( `lastname` LIKE _utf8\''. $db->escape($name_sql) .'\' COLLATE utf8_unicode_ci
	) '.$key_sql.'
ORDER BY `lastname`, `firstname`
LIMIT '. ($page * (int)$per_page) .','. (int)$per_page;
		break;
	default:
		$query = '';
	}
	
	$rs = $db->execute($query);
	$num_total = @$db->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	$entries =  (($num_total > $per_page) ? $per_page : $num_total );
	
	xml('<'.'?xml version="1.0" encoding="UTF-8" ?'.'>');
	xml('<IppDisplay>');
	xml('<IppScreen ID="1" HiddenCount="3" CommandCount="1">');
	xml('  <IppKey Keypad="YES" SendKeys="YES" BufferKeys="NO" BufferLength="0" TermKey="" UrlKey="key" />');
	xml('  <IppList Type="IMPLICIT" Count="'.($entries+1).'" Columns="3">');
	if ($keys == '')
		xml('    <Title>'. htmlEnt(__('Telefonbuch')) .' '. htmlEnt(@$typeToTitle[$type]) .' ('.$num_total.')' .'</Title>');
	else
		xml('    <Title>'. htmlEnt(__('Telefonbuch')) .' '. htmlEnt(@$typeToTitle[$type]) .' ('.$num_total.')' .' : '.htmlEnt($keys) .'</Title>');
	xml('    <Url>'. htmlEnt($url) .'</Url>');
	
	$i=1;
	//if (true) {
		xml('    <Option ID="'.$i.'" Selected="FALSE" Key="type" Value="none">');
		xml('      <OptionText>'. htmlEnt(__("Zur\xC3\xBCck")) .'</OptionText>');
		xml('      <OptionText> </OptionText>');
		xml('      <OptionText> </OptionText>');
		xml('      <Image>'. htmlEnt($img_url).'previous.png</Image>');
		xml('    </Option>');
	//}
	# Alternative search method. Not really necessary anymore due to new keypad functions.
	/*
	if ($num_total > 6) {
		$i++;
		xml('    <Option ID="'.$i.'" Selected="FALSE" Key="search" Value="'.$type.'">');
		xml('      <OptionText>'. __('Suchen') .'</OptionText>');
		xml('      <Image>'.$img_url.'search.png</Image>');
		xml('    </Option>');
	}
	*/
	while ($r = $rs->fetchRow()) {
		$i++;
		$selected = ($num_total == 1) ? 'TRUE':'FALSE';  # select first entry if there's only 1
		xml('    <Option ID="'.$i.'" Selected="'.$selected.'" Key="dial" Value="'. htmlEnt($r['number']) .'">');
		xml('      <OptionText State="NORMAL">'. htmlEnt($r['ln']) .', '. htmlEnt($r['fn']) .'</OptionText>');
		xml('      <OptionText>'. htmlEnt(@substr($r['number'],0,5)) .' </OptionText>');
		xml('      <OptionText>'. htmlEnt(@substr($r['number'],5)) .' </OptionText>');
		xml('      <Image></Image>');
		xml('    </Option>');
	}
	
	xml('  </IppList>');
	xml('  <IppHidden Type="VALUE" Key="user">');
	xml('    <Value>'.htmlEnt($user).'</Value>');
	xml('  </IppHidden>');
	xml('  <IppHidden Type="VALUE" Key="tab">');
	xml('    <Value>'.htmlEnt($tab).'</Value>');
	xml('  </IppHidden>');
	xml('  <IppHidden Type="VALUE" Key="keys">');
	xml('    <Value>'.htmlEnt($keys).'</Value>');
	xml('  </IppHidden>');
	xml('</IppScreen>');
	xml('</IppDisplay>');
	
}


#########################################################
# Output
#########################################################

xml_output();

?>
