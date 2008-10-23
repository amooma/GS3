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

$xml_buf = '';


require_once( '../../../../inc/conf.php' );
require_once( GS_DIR .'inc/db_connect.php' );

function xml($string) {
	global $xml_buf;
	
	$xml_buf .= $string."\n";

}

function xml_output() {
	global $xml_buf;
	header( 'X-Powered-By: Gemeinschaft' );
	header( 'Content-Type: text/xml' );
	header( 'Content-Length: '. strLen($xml_buf) );
	
	echo $xml_buf;

}

function dial_number($number) {
	xml('<?xml version="1.0" encoding="UTF-8" ?>');
	xml('<IppDisplay>');
	xml('<IppScreen ID="1" HiddenCount="0" CommandCount="0">');
	xml('<IppKey Keypad="YES" SendKeys="YES" BufferKeys="NO" BufferLength="0" TermKey="" UrlKey="key" /> ');
	xml('<IppAlert Type="INFO" Delay="3000">');
	xml('<Title>Anruf</Title>');
	xml('<Text>Rufe an: '.$number.'</Text>');
	xml('<Image></Image>');
	xml('</IppAlert>');
	xml('<IppAction Type="MAKECALL">');
	xml('<Number>'.$number.'</Number>');
	xml('</IppAction>');
	xml('</IppScreen>');
	xml('</IppDisplay>');

}

function write_alert($message, $alert_type="ERROR") {
	xml('<?xml version="1.0" encoding="UTF-8" ?>');
	xml('<IppDisplay>');
	xml('<IppScreen ID="1" HiddenCount="0" CommandCount="0">');
	xml('<IppAlert Type="'.$alert_type.'" Delay="5000">');
	xml('<Title>Info</Title>');
	xml('<Text>'.$message.'</Text>');
	xml('<Image></Image>');
	xml('</IppAlert>');
	xml('</IppScreen>');
	xml('</IppDisplay>');
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
	'9' => "[wxyz9]",
);

$user = trim( @ $_REQUEST['user'] );
$phonenumber  = trim( @ $_REQUEST['phonenumber'] );
$search =  trim( @ $_REQUEST['search'] );
$name_search =  trim( @ $_REQUEST['name'] );
$ip_addr = trim ( @ $_REQUEST['ipaddress'] );
$keys =  trim ( @ $_REQUEST['keys'] );
$key = trim ( @ $_REQUEST['key'] );

$keys .= $key;

if ($key == '*') $keys = '';

$type = trim( @$_REQUEST['type'] );

if ($type=='none') {
	$keys='';
}



$tab = trim( @ $_REQUEST['tab'] );
if ($tab) {
	$tab =  @preg_replace('/^internal:/', '', $tab);
	if ($tab == 'XMLPhonebook') $tab='prv';
	$type = $tab;
} 

if (!$user) $user = $phonenumber;

$url= GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'siemens/pb/pb.php';
$img_url = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'siemens/img/';

if (! preg_match('/^\d+$/', $user)) {
	write_alert( 'Benutzer '.$user.' unbekannt!' );
	xml_output();
	exit;
}
//$type = trim( @$_REQUEST['type'] );

if (! in_array( $type, array('gs','prv','imported'), true )) {
	$type = false;
}




$dial = trim( @ $_REQUEST['dial'] );

$db = gs_db_slave_connect();



# get user_id
#

$user_id = (int)$db->executeGetOne( 'SELECT `id` FROM `users` WHERE `current_ip`=\''. $db->escape($ip_addr) .'\'' );

if (!$user_id) $user_id = (int)$db->executeGetOne( 'SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`=\''. $db->escape($user) .'\'' );

if ($user_id < 1) {
	write_alert( 'Unknown user.' );
	xml_output();
	exit;
}

$tmp = array(
	15=>array('k' => 'gs' ,
			'v' => gs_get_conf('GS_PB_INTERNAL_TITLE', "Intern") ),
	25=>array('k' => 'prv',
			'v' => gs_get_conf('GS_PB_PRIVATE_TITLE', "Pers\xC3\xB6nlich" ) )
);
if (gs_get_conf('GS_PB_IMPORTED_ENABLED')) {
	$pos = (int)gs_get_conf('GS_PB_IMPORTED_ORDER', 9) * 10;
	$tmp[$pos] = array(
			'k' => 'imported',
			'v' => gs_get_conf('GS_PB_IMPORTED_TITLE', "Importiert")
	);
}
kSort($tmp);
foreach ($tmp as $arr) {
	$typeToTitle[$arr['k']] = $arr['v'];
}

if ($search) { 

	if (!$name_search) {
		xml('<?xml version="1.0" encoding="UTF-8" ?>');
		xml('<IppDisplay>');
		xml('<IppScreen ID="3" HiddenCount="3" CommandCount="2">');
		xml('<IppForm ItemCount="1">');
		xml('<Title>Name suchen:</Title>');
		xml('<Url>'.$url.'</Url>');
		xml('<IppTextField MaxSize="30" Constraint="ANY" Default="'.$name_search.'" Key="name">');
		xml('<Label>Name:</Label>');
		xml('<Text>'.$name_search.'</Text>');
		xml('</IppTextField>');
		xml('</IppForm>');
		xml('<IppCommand Type="SELECT" Priority="0">');
		xml('<Label>Suchen</Label>');
		xml('<ScreenID>1</ScreenID>');
		xml('</IppCommand>');
		xml('<IppCommand Type="SCREEN" Priority="0">');
		xml('<Label>Abbrechen</Label>');
		xml('<ScreenID>1</ScreenID>');
		xml('</IppCommand>');
		
		xml('<IppHidden Type="VALUE" Key="user">');;
		xml('<Value>'.$user.'</Value>');
		xml('</IppHidden>');
		xml('<IppHidden Type="VALUE" Key="search">');;
		xml('<Value>'.$search.'</Value>');
		xml('</IppHidden>');
		xml('<IppHidden Type="VALUE" Key="tab">');;
		xml('<Value>'.$tab.'</Value>');
		xml('</IppHidden>');
		xml('</IppScreen>');
		xml('</IppDisplay>');
	} else {
		$type = $search;
	}
} 






#########################################################
# Static entry screen
#########################################################

if ($dial) dial_number($dial);

if (!$type) {
	xml('<?xml version="1.0" encoding="UTF-8" ?>');
	xml('<IppDisplay>');
	xml('<IppScreen ID="1" HiddenCount="2" CommandCount="1">');
	xml('<IppKey Keypad="YES" SendKeys="YES" BufferKeys="NO" BufferLength="0" TermKey="" UrlKey="key" /> ');
	xml('<IppList Type="IMPLICIT" Count="'.count($typeToTitle).'">');
	xml('<Title>'.$user.' - Telefonbuch</Title>');
	xml('<Url>'.$url.'</Url>');
	$i=0;
	foreach ($typeToTitle as $t => $title) {
		
		switch ($t) {
			case 'gs' : 
				$num_calls = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `users` WHERE `nobody_index` IS NULL' );
				$image = $img_url.'contents.png';
				break;
			case 'prv':
				 $num_calls = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `pb_prv` WHERE `user_id` ='. $user_id );
				$image = $img_url.'yast_sysadmin.png';
				break;
			default : $num_calls = 0;

		}
		$i++;
		if ($i==1) xml('<Option ID="'.$i.'" Selected="TRUE" Key="type" Value="'.$t.'">');
			else xml('<Option ID="'.$i.'" Selected="FALSE" Key="type" Value="'.$t.'">');
		xml('<OptionText>'.$title.' ('.$num_calls.')'.'</OptionText>');
		xml('<Image>'.$image.'</Image>');
		xml('</Option>');
	}
	xml('</IppList>');
	xml('<IppHidden Type="VALUE" Key="user">');;
	xml('<Value>'.$user.'</Value>');
	xml('</IppHidden>');
	xml('<IppHidden Type="VALUE" Key="tab">');;
	xml('<Value>'.$tab.'</Value>');
	xml('</IppHidden>');
	xml('</IppScreen>');
	xml('</IppDisplay>');

} else {

#########################################################
# Phone Books
#########################################################

	$page = 0;
	$per_page = 150;

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
	}

	switch ($type) {
	
	case 'gs' :
		$query =
'SELECT SQL_CALC_FOUND_ROWS 
	`u`.`id` `id`, `u`.`lastname` `ln`, `u`.`firstname` `fn`, `s`.`name` `number`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE
	`u`.`nobody_index` IS NULL AND (
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
	`u`.`id` `id`, `u`.`lastname` `ln`, `u`.`firstname` `fn`, `s`.`name` `number`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE
	`u`.`nobody_index` IS NULL AND (
	`u`.`lastname` LIKE _utf8\''. $db->escape($name_sql) .'\' COLLATE utf8_unicode_ci 
	)
ORDER BY `u`.`lastname`, `u`.`firstname`
LIMIT '. ($page * (int)$per_page) .','. (int)$per_page;		
		break;
	default:	
		$query = '';
	}


	$rs = $db->execute( $query );
	$num_total = @$db->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	$entries =  (($num_total > $per_page) ? $per_page : $num_total );

	xml('<?xml version="1.0" encoding="UTF-8" ?>');
	xml('<IppDisplay>');
	xml('<IppScreen ID="1" HiddenCount="3" CommandCount="1">');
	xml('<IppKey Keypad="YES" SendKeys="YES" BufferKeys="NO" BufferLength="0" TermKey="" UrlKey="key" /> ');
	xml('<IppList Type="IMPLICIT" Count="'.($entries+1).'">');
	xml('<Title>'.$user.' - Telefonbuch: '.$typeToTitle[$type].'</Title>');
	xml('<Url>'.$url.'</Url>');
	$i=1;
	if (1) {
		xml('<Option ID="'.$i.'" Selected="TRUE" Key="type" Value="none">');
		xml('<OptionText>Zurück</OptionText>');
		xml('<Image>'.$img_url.'previous.png</Image>');
		xml('</Option>');
	}
	if ($num_total > 6) {
		$i++;
		xml('<Option ID="'.$i.'" Selected="FALSE" Key="search" Value="'.$type.'">');
		xml('<OptionText>Suchen</OptionText>');
		xml('<Image>'.$img_url.'search.png</Image>');
		xml('</Option>');
	}
	
	while ($r = $rs->fetchRow()) {
		$i++;   
		$entry_name = $r['ln'].', '.$r['fn'].' - '. $r['number'];
		xml('<Option ID="'.$i.'" Selected="FALSE" Key="dial" Value="'.$r['number'].'">');
		xml('<OptionText>'.$entry_name.'</OptionText>');
		xml('<Image></Image>');
		xml('</Option>');
	}
	
	xml('</IppList>');
	xml('<IppHidden Type="VALUE" Key="user">');
	xml('<Value>'.$user.'</Value>');
	xml('</IppHidden>');
	xml('<IppHidden Type="VALUE" Key="tab">');;
	xml('<Value>'.$tab.'</Value>');
	xml('</IppHidden>');
	xml('<IppHidden Type="VALUE" Key="keys">');;
	xml('<Value>'.$keys.'</Value>');
	xml('</IppHidden>');
	xml('</IppScreen>');
	xml('</IppDisplay>');

	
}

xml_output();

?>
