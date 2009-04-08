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

# caution: earlier versions of Snom firmware do not like
# indented XML

define( 'GS_VALID', true );  /// this is a parent file
require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
include_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/gettext.php' );

header( 'Content-Type: application/x-snom-xml; charset=utf-8' );
# the Content-Type header is ignored by the Snom
header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );

function snomXmlEsc( $str )
{
	return str_replace(
		array('<', '>', '"'   , "\n"),
		array('_', '_', '\'\'', ' ' ),
		$str);
	# the stupid Snom does not understand &lt;, &gt, &amp;, &quot; or &apos;
	# - neither as named nor as numbered entities
}

function _ob_send()
{
	if (! headers_sent()) {
		header( 'Content-Type: application/x-snom-xml; charset=utf-8' );
		# the Content-Type header is ignored by the Snom
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
		'<SnomIPPhoneText>', "\n",
			'<Title>', __('Fehler'), '</Title>', "\n",
			'<Text>', snomXmlEsc( __('Fehler') .': '. $msg ), '</Text>', "\n",
		'</SnomIPPhoneText>', "\n";
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


if (! gs_get_conf('GS_SNOM_PROV_ENABLED')) {
	gs_log( GS_LOG_DEBUG, "Snom provisioning not enabled" );
	_err( 'Not enabled' );
}

$type = trim( @$_REQUEST['t'] );
if (! in_array( $type, array('gs','prv','imported'), true )) {
	$type = false;
}


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


$url_snom_pb = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'snom/pb.php';



#################################### INITIAL SCREEN {
if (! $type) {
	
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['m'] )));
	$user = trim( @$_REQUEST['u'] );
	$user_id = getUserID( $user );
	
	ob_start();
	echo
		'<?','xml version="1.0" encoding="utf-8"?','>', "\n",
		'<SnomIPPhoneMenu>', "\n",
			'<Title>', __('Telefonbuch') ,'</Title>', "\n\n";
	foreach ($typeToTitle as $t => $title) {
		$cq = 'SELECT COUNT(*) FROM ';
		switch ($t) {
		case 'gs'      : $cq .= '`users` WHERE `nobody_index` IS NULL AND `pb_hide` = 0'; break;
		case 'imported': $cq .= '`pb_ldap`'                           ; break;
		case 'prv'     : $cq .= '`pb_prv` WHERE `user_id`='. $user_id ; break;
		default        : $cq  = false;
		}
		$c = $cq ? (' ('. (int)@$db->executeGetOne( $cq ) .')') : '';
		echo
			'<MenuItem>', "\n",
				'<Name>', snomXmlEsc($title), $c, '</Name>', "\n",
				'<URL>', $url_snom_pb, '?m=',$mac, '&u=',$user, '&t=',$t, '</URL>', "\n",
			'</MenuItem>', "\n\n";
		# in XML the & must normally be encoded as &amp; but not for
		# the stupid Snom!
	}
	echo '</SnomIPPhoneMenu>', "\n";
	_ob_send();
	
}
#################################### INITIAL SCREEN }




$softkeys=array(
	array('label' => '0', 'name' => '0'),
	array('label' => '1', 'name' => '1'),
	array('label' => '2', 'name' => '2'),
	array('label' => '3', 'name' => '3'),
	array('label' => '4', 'name' => '4'),
	array('label' => '5', 'name' => '5'),
	array('label' => '6', 'name' => '6'),
	array('label' => '7', 'name' => '7'),
	array('label' => '8', 'name' => '8'),
	array('label' => '9', 'name' => '9'),
	array('label' => '*', 'name' => 'A'), # asterisk
	//array('label' => '#', 'name' => 'H'), # hash
);

$keyPatterns = array(  # must be valid in MySQL!
  '0' => "[ -.,_0]+",
  '1' => "[ -.,_1]+",
  '2' => "[abc2\xC3\xA4]",   # a dieresis
  '3' => "[def3\xC3\xA9]",   # e acute
  '4' => "[ghi4\xC3\xAF]",   # i dieresis
  '5' => "[jkl5]",
  '6' => "[mno6\xC3\xB6]",   # o dieresis
  '7' => "[pqrs7\xC3\x9F]",  # sharp s
  '8' => "[tuv8\xC3\xBC]",   # u dieresis
  '9' => "[wxyz9]",
  'A' => "." // A=*
  //'H' => '$', // H=#
);
$keyPatternsLike = array(  # for the LIKE comparison
  '0' => array(' ','-','.',',','0'),
  '1' => array(' ','-','.',',','1'),
  '2' => array('a','b','c','2'),
  '3' => array('d','e','f','3'),
  '4' => array('g','h','i','4'),
  '5' => array('j','k','l','5'),
  '6' => array('m','n','o','6'),
  '7' => array('p','q','r','s','7'),
  '8' => array('t','u','v','8'),
  '9' => array('w','x','y','z','9'),
  'A' => array('_')
);


$keys = preg_replace('/[^0-9AH]/', '', @$_REQUEST['k']);
$regex = '';
for ($i=0; $i<strLen($keys); ++$i) {
	$regex .= @$keyPatterns[$keys{$i}];
}


function defineKey( $keyDef )
{
	global $keys, $user, $type, $mac, $url_snom_pb;
	
	$args = array();
	$args[] = 't='. $type;
	$args[] = 'k='. $keys . $keyDef['name'];
	if ($type === 'prv') {
		$args[] = 'm='. $mac;
		$args[] = 'u='. $user;
	}
	echo
		'<SoftKeyItem>',
			'<Name>', $keyDef['label'], '</Name>',
			'<URL>', $url_snom_pb, '?', implode('&', $args), '</URL>',
		'</SoftKeyItem>', "\n";
	# Snom does not understand &amp; !
}
function defineKeys()
{
	global $softkeys;
	
	foreach ($softkeys as $keyDef) {
		defineKey($keyDef);
	}
	defineBackKey();
}
function defineBackKey()
{
	global $softkeys, $keys, $user, $type, $mac, $url_snom_pb;
	
	$args = array();
	$args[] = 't='. $type;
	$args[] = 'k='. subStr($keys,0,-1);
	if ($type === 'prv') {
		$args[] = 'm='. $mac;
		$args[] = 'u='. $user;
	}
	echo
		'<SoftKeyItem>',
			'<Name>#</Name>',
			'<URL>', $url_snom_pb, '?', implode('&', $args), '</URL>',
		'</SoftKeyItem>', "\n";
	# Snom does not understand &amp; !
}


$num_results = (int)gs_get_conf('GS_SNOM_PROV_PB_NUM_RESULTS', 10);



#################################### IMPORTED PHONEBOOK {
if ($type === 'imported') {
	
	// we don't need $user for this
	
	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>',"\n";
	
	$likeFn = false;
	$likeLn = false;
	if (strLen($keys) > 0 && is_array($keyPatternsLike[$keys{0}])) {
		$likeFn = array();
		$likeLn = array();
		foreach ($keyPatternsLike[$keys{0}] as $char) {
			$l = 'LIKE \''. $db->escape($char) .'%\'';
			$likeFn[] = '`firstname` '. $l;
			$likeLn[] = '`lastname` '.  $l;
		}
		$likeFn = '('. implode(' OR ', $likeFn) .')';
		$likeLn = '('. implode(' OR ', $likeLn) .')';
	}
	
	$where = '';
	if ($keys != '') {
		$where = '
	  ('. ($likeLn ? ($likeLn .' AND ') : '') .'
	  `lastname`  REGEXP \'^'. $db->escape($regex) .'\' ) OR
	  ('. ($likeFn ? ($likeFn .' AND ') : '') .'
	  `firstname` REGEXP \'^'. $db->escape($regex) .'\' )';
	}
	$query =
'SELECT `lastname` `ln`, `firstname` `fn`, `number` `ext`
FROM `pb_ldap`
'. ($where ? ('WHERE ('. $where .')') : '') .'
ORDER BY `lastname`, `firstname`
LIMIT '. $num_results;
	$rs = $db->execute($query);
	if ($rs->numRows() !== 0) {
		
		echo
			'<SnomIPPhoneDirectory>', "\n",
				'<Title>', snomXmlEsc( $typeToTitle[$type] ), '</Title>', "\n",
				'<Prompt>Prompt</Prompt>', "\n";
		while ($r = $rs->fetchRow()) {
			$name = $r['ln'] .( strLen($r['fn'])>0 ? (', '.$r['fn']) : '' );
			$number = $r['ext'];
			echo
				'<DirectoryEntry>',
					'<Name>', snomXmlEsc( $name ) ,'</Name>',
					'<Telephone>', $number ,'</Telephone>',
				'</DirectoryEntry>', "\n";
		}
		defineKeys();
		echo '</SnomIPPhoneDirectory>', "\n";
		
	} else {
		
		echo
			'<SnomIPPhoneText>', "\n",
				'<Title>', snomXmlEsc( $typeToTitle[$type] ), '</Title>', "\n",
				'<Prompt>Prompt</Prompt>', "\n";
		if (strLen($keys) > 0) {
			echo '<Text>', snomXmlEsc( sPrintF(__("Keine Treffer f\xC3\xBCr \"%s\". Dr\xC3\xBCcken Sie # um die letzte Eingabe zu widerrufen."), $keys) ), '</Text>', "\n";
		} else {
			echo '<Text>', snomXmlEsc( __("Dieses Telefonbuch enth\xC3\xA4lt keine Eintr\xC3\xA4ge.") ), '</Text>', "\n";
		}
		defineBackKey();
		echo '</SnomIPPhoneText>', "\n";
		
	}
	_ob_send();
}
#################################### IMPORTED PHONEBOOK }



#################################### INTERNAL PHONEBOOK {
if ($type === 'gs') {
	
	// we don't need $user for this
	
	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>',"\n";
	
	$likeFn = false;
	$likeLn = false;
	if (strLen($keys) > 0 && is_array($keyPatternsLike[$keys{0}])) {
		$likeFn = array();
		$likeLn = array();
		foreach ($keyPatternsLike[$keys{0}] as $char) {
			$l = 'LIKE \''. $db->escape($char) .'%\'';
			$likeFn[] = '`u`.`firstname` '. $l;
			$likeLn[] = '`u`.`lastname` '.  $l;
		}
		$likeFn = '('. implode(' OR ', $likeFn) .')';
		$likeLn = '('. implode(' OR ', $likeLn) .')';
	}
	
	$where = '';
	if ($keys != '') {
		$where = '
	  ('. ($likeLn ? ($likeLn .' AND ') : '') .'
	  `u`.`lastname`  REGEXP \'^'. $db->escape($regex) .'\' ) OR
	  ('. ($likeFn ? ($likeFn .' AND ') : '') .'
	  `u`.`firstname` REGEXP \'^'. $db->escape($regex) .'\' )';
	}
	$query =
'SELECT `u`.`lastname` `ln`, `u`.`firstname` `fn`, `s`.`name` `ext`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE
	`u`.`pb_hide` = 0 AND
	`u`.`nobody_index` IS NULL
	'. ($where ? ('AND ('. $where .')') : '') .'
ORDER BY `u`.`lastname`, `u`.`firstname`
LIMIT '. $num_results;
	$rs = $db->execute($query);
	if ($rs->numRows() !== 0) {
		
		echo
			'<SnomIPPhoneDirectory>', "\n",
				'<Title>', snomXmlEsc( $typeToTitle[$type] ), '</Title>', "\n",
				'<Prompt>Prompt</Prompt>', "\n";
		while ($r = $rs->fetchRow()) {
			$name = $r['ln'] .( strLen($r['fn'])>0 ? (', '.$r['fn']) : '' );
			$number = $r['ext'];
			echo
				'<DirectoryEntry>',
					'<Name>', snomXmlEsc( $name ) ,' (', snomXmlEsc( $number ) ,')</Name>',
					'<Telephone>', $number ,'</Telephone>',
				'</DirectoryEntry>', "\n";
		}
		defineKeys();
		echo '</SnomIPPhoneDirectory>', "\n";
		
	} else {
		
		echo
			'<SnomIPPhoneText>', "\n",
				'<Title>', snomXmlEsc( $typeToTitle[$type] ), '</Title>', "\n",
				'<Prompt>Prompt</Prompt>', "\n";
		if (strLen($keys) > 0) {
			echo '<Text>', snomXmlEsc( sPrintF(__("Keine Treffer f\xC3\xBCr \"%s\". Dr\xC3\xBCcken Sie # um die letzte Eingabe zu widerrufen."), $keys) ), '</Text>', "\n";
		} else {
			echo '<Text>', snomXmlEsc( __("Dieses Telefonbuch enth\xC3\xA4lt keine Eintr\xC3\xA4ge.") ), '</Text>', "\n";
		}
		defineBackKey();
		echo '</SnomIPPhoneText>', "\n";
		
	}
	_ob_send();
}
#################################### INTERNAL PHONEBOOK }



#################################### PRIVATE PHONEBOOK {
if ($type === 'prv') {
	
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['m'] )));
	$user = trim( @ $_REQUEST['u'] );
	$user_id = getUserID( $user );
	
	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>',"\n";
	
	$user_id_check = $db->executeGetOne( 'SELECT `user_id` FROM `phones` WHERE `mac_addr`=\''. $db->escape($mac) .'\'' );
	if ($user_id != $user_id_check)
		_err( 'Not authorized' );
	
	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$remote_addr_check = $db->executeGetOne( 'SELECT `current_ip` FROM `users` WHERE `id`='. $user_id );
	if ($remote_addr != $remote_addr_check)
		_err( 'Not authorized' );
	
	$likeFn = false;
	$likeLn = false;
	if (strLen($keys) > 0 && is_array($keyPatternsLike[$keys{0}])) {
		$likeFn = array();
		$likeLn = array();
		foreach ($keyPatternsLike[$keys{0}] as $char) {
			$l = 'LIKE \''. $db->escape($char) .'%\'';
			$likeFn[] = '`firstname` '. $l;
			$likeLn[] = '`lastname` '.  $l;
		}
		$likeFn = '('. implode(' OR ', $likeFn) .')';
		$likeLn = '('. implode(' OR ', $likeLn) .')';
	}
	
	$where = '';
	if ($keys != '') {
		$where = '
	  ('. ($likeLn ? ($likeLn .' AND ') : '') .'
	  `lastname`  REGEXP \'^'. $db->escape($regex) .'\' ) OR
	  ('. ($likeFn ? ($likeFn .' AND ') : '') .'
	  `firstname` REGEXP \'^'. $db->escape($regex) .'\' )';
	}
	$query =
'SELECT `lastname` `ln`, `firstname` `fn`, `number`
FROM
	`pb_prv`
WHERE
	`user_id`='. $user_id .'
	'. ($where ? ('AND ('. $where .')') : '') .'
ORDER BY `lastname`, `firstname`
LIMIT '. $num_results;
	$rs = $db->execute($query);
	if ($rs->numRows() !== 0) {
		
		echo
			'<SnomIPPhoneDirectory>', "\n",
				'<Title>', snomXmlEsc( $typeToTitle[$type] ), '</Title>', "\n",
				'<Prompt>Prompt</Prompt>', "\n";
		while ($r = $rs->fetchRow()) {
			$name = $r['ln'] .( strLen($r['fn'])>0 ? (', '.$r['fn']) : '' );
			$number = $r['number'];
			echo
				'<DirectoryEntry>',
					'<Name>', snomXmlEsc( $name ) ,'</Name>',
					'<Telephone>', $number ,'</Telephone>',
				'</DirectoryEntry>', "\n";
		}
		defineKeys();
		echo '</SnomIPPhoneDirectory>', "\n";
		
	} else {
		
		echo
			'<SnomIPPhoneText>', "\n",
				'<Title>', snomXmlEsc( $typeToTitle[$type] ), '</Title>', "\n",
				'<Prompt>Prompt</Prompt>', "\n";
		if (strLen($keys) > 0) {
			echo '<Text>', snomXmlEsc( sPrintF(__("Keine Treffer f\xC3\xBCr \"%s\". Dr\xC3\xBCcken Sie # um die letzte Eingabe zu widerrufen."), $keys) ), '</Text>', "\n";
		} else {
			echo '<Text>', snomXmlEsc( __("Ihr pers\xC3\xB6nliches Telefonbuch enth\xC3\xA4lt keine Eintr\xC3\xA4ge.") ), '</Text>', "\n";
		}
		defineBackKey();
		echo '</SnomIPPhoneText>', "\n";
		
	}
	_ob_send();
}
#################################### PRIVATE PHONEBOOK }


?>