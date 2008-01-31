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

require_once( '../../../inc/conf.php' );
require_once( GS_DIR .'inc/db_connect.php' );
require_once( GS_DIR .'inc/gs-fns/gs_aastrafns.php' );
$xml_buffer = '';


function aastra_textscreen($title,$text) {
	aawrite('<AastraIPPhoneTextScreen destroyOnExit="yes">');
	aawrite('<Title>'.$title.'<Title>');
	aawrite('<Text>'.$title.'<Text>');
	aawrite('</AastraIPPhoneTextScreen>');

}

$type = trim( @$_REQUEST['t'] );
if (! in_array( $type, array('gs','prv','imported', 'gss', 'prvs'), true )) {
	$type = false;
}
$page = (int)trim( @$_REQUEST['p'] );

$entry = (int)trim( @$_REQUEST['e'] );


$per_page = (int)gs_get_conf('GS_AASTRA_PROV_PB_NUM_RESULTS', 10);
$db = gs_db_slave_connect();

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

if (!$type) {

	
	
	
	aawrite('<AastraIPPhoneTextMenu destroyOnExit="yes" LockIn="no" style="none">');
	aawrite('<Title>'.__('Telefonbuch').'</Title>');
	
	foreach ($typeToTitle as $key => $title) {
		aawrite('<MenuItem>');
		aawrite('<Prompt>'.$title.'</Prompt>');
		aawrite('<URI>http://'.GS_PROV_HOST.':'.GS_PROV_PORT.'/aastra/pb.php?t='.$key.'</URI>');
		//aawrite('<Selection>0&amp;menu_pos=1</Selection>'."\n";
		aawrite('</MenuItem>');
	} 
	
	aawrite('<SoftKey index="1">');
	aawrite('<Label>OK</Label>');
	aawrite('<URI>SoftKey:Select</URI>');
	aawrite('</SoftKey>');
	aawrite('<SoftKey index="4">');
	aawrite('<Label>'.__('Abbrechen').'</Label>');
	aawrite('<URI>SoftKey:Exit</URI>');
	aawrite('</SoftKey>');
	
	aawrite('<SoftKey index="6">');
	aawrite('<Label>&gt;&gt;</Label>');
	aawrite('<URI>SoftKey:Select</URI>');
	aawrite('</SoftKey>');
	aawrite('</AastraIPPhoneTextMenu>');


} else if ($type=='gs'){

	aawrite('<AastraIPPhoneTextMenu destroyOnExit="yes" LockIn="no" style="none" cancelAction = "http://'.GS_PROV_HOST.':'.GS_PROV_PORT.'/aastra/pb.php">');
	
	$query =
'SELECT `u`.`id` `id`, `u`.`lastname` `ln`, `u`.`firstname` `fn`, `s`.`name` `ext`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE
	`u`.`nobody_index` IS NULL
ORDER BY `u`.`lastname`, `u`.`firstname`
LIMIT '. ($page * (int)$per_page) .','. (int)$per_page;
	
	$rs = $db->execute($query);
	$num_total = @$db->numFoundRows();
	$num_pages = ceil($num_total / $per_page);

	if ($num_pages > 1) $page_title = ($page+1).'/'.$num_pages;
		else $page_title = '';
	aawrite('<Title>'.$typeToTitle[$type].' '.$page_title.'</Title>');

	$rs = $db->execute($query);
	if ($rs->numRows() !== 0) {

		while ($r = $rs->fetchRow()) {
			$name = $r['ln'] .( strLen($r['fn'])>0 ? (', '.$r['fn']) : '' );
			$number = $r['ext'];
			aawrite('<MenuItem>');
			aawrite('<Prompt>'.$name.' - '.$number.'</Prompt>');
			aawrite('<Dial>'.$number.'</Dial>');
			aawrite('<URI>http://'.GS_PROV_HOST.':'.GS_PROV_PORT.'/aastra/pb.php?t=gss&amp;e='.$r['id'].'</URI>');
			aawrite('</MenuItem>');
		}
	}

	aawrite('<SoftKey index="1">');
	aawrite('<Label>OK</Label>');
	aawrite('<URI>SoftKey:Select</URI>');
	aawrite('</SoftKey>');
	aawrite('<SoftKey index="2">');
	aawrite('<Label>'.__('Anrufen').'</Label>');
	aawrite('<URI>SoftKey:Dial2</URI>');
	aawrite('</SoftKey>');
	aawrite('<SoftKey index="4">');
	aawrite('<Label>'.__('Abbrechen').'</Label>');
	aawrite('<URI>SoftKey:Exit</URI>');
	aawrite('</SoftKey>');

	if ($page > 0) {
		aawrite('<SoftKey index="3">');
		aawrite('<Label>&lt;&lt;'.($page).'</Label>');
		aawrite('<URI>http://'.GS_PROV_HOST.':'.GS_PROV_PORT.'/aastra/pb.php?t=prv&amp;p='.($page-1).'</URI>');
		aawrite('</SoftKey>');
	}
	if ($page < $num_pages-1) {
		aawrite('<SoftKey index="6">');
		aawrite('<Label>&gt;&gt;'.($page+2).'</Label>');
		aawrite('<URI>http://'.GS_PROV_HOST.':'.GS_PROV_PORT.'/aastra/pb.php?t=prv&amp;p='.($page+1).'</URI>');
		aawrite('</SoftKey>');
	}

	aawrite('</AastraIPPhoneTextMenu>');


} else if ($type=='prv'){

	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$user_id = $db->executeGetOne( 'SELECT `id` FROM `users` WHERE `current_ip`=\''. $remote_addr.'\'' );

	aawrite('<AastraIPPhoneTextMenu destroyOnExit="yes" LockIn="no" style="none" cancelAction = "http://'.GS_PROV_HOST.':'.GS_PROV_PORT.'/aastra/pb.php">');
	
	
	$query =
'SELECT SQL_CALC_FOUND_ROWS
	`id`, `lastname` `ln`, `firstname` `fn`, `number`
FROM
	`pb_prv`
WHERE
	`user_id`='. $user_id .'
ORDER BY `lastname`, `firstname`
LIMIT '. ($page * (int)$per_page) .','. (int)$per_page;
	
	$rs = $db->execute($query);
	$num_total = @$db->numFoundRows();
	$num_pages = ceil($num_total / $per_page);

	if ($num_pages > 1) $page_title = ($page+1).'/'.$num_pages;
		else $page_title = '';
	aawrite('<Title>'.$typeToTitle[$type].' '.$page_title.'</Title>');

	if ($rs && $rs->numRows() !== 0) {

		while ($r = $rs->fetchRow()) {
			$name = $r['ln'] .( strLen($r['fn'])>0 ? (', '.$r['fn']) : '' );
			aawrite('<MenuItem>');
			aawrite('<Prompt>'.$name.' - '.$r['number'].'</Prompt>');
			aawrite('<Dial>'.$r['number'].'</Dial>');
			aawrite('<URI>http://'.GS_PROV_HOST.':'.GS_PROV_PORT.'/aastra/pb.php?t=prvs&amp;e='.$r['id'].'</URI>');
			aawrite('</MenuItem>');
		}
	}

	aawrite('<SoftKey index="1">');
	aawrite('<Label>OK</Label>');
	aawrite('<URI>SoftKey:Select</URI>');
	aawrite('</SoftKey>');
	aawrite('<SoftKey index="2">');
	aawrite('<Label>'.__('Anrufen').'</Label>');
	aawrite('<URI>SoftKey:Dial2</URI>');
	aawrite('</SoftKey>');
	aawrite('<SoftKey index="4">');
	aawrite('<Label>'.__('Abbrechen').'</Label>');
	aawrite('<URI>SoftKey:Exit</URI>');
	aawrite('</SoftKey>');

	if ($page > 0) {
		aawrite('<SoftKey index="3">');
		aawrite('<Label>&lt;&lt;'.($page).'</Label>');
		aawrite('<URI>http://'.GS_PROV_HOST.':'.GS_PROV_PORT.'/aastra/pb.php?t=prv&amp;p='.($page-1).'</URI>');
		aawrite('</SoftKey>');
	}
	if ($page < $num_pages-1) {
		aawrite('<SoftKey index="6">');
		aawrite('<Label>&gt;&gt;'.($page+2).'</Label>');
		aawrite('<URI>http://'.GS_PROV_HOST.':'.GS_PROV_PORT.'/aastra/pb.php?t=prv&amp;p='.($page+1).'</URI>');
		aawrite('</SoftKey>');
	}

	aawrite('</AastraIPPhoneTextMenu>');


} else if ($type=='prvs'){

	$remote_addr = @$_SERVER['REMOTE_ADDR'];

	$user_id = $db->executeGetOne( 'SELECT `id` FROM `users` WHERE `current_ip`=\''. $remote_addr.'\'' );

	aawrite('<AastraIPPhoneFormattedTextScreen destroyOnExit="yes" cancelAction = "http://'.GS_PROV_HOST.':'.GS_PROV_PORT.'/aastra/pb.php?t=prv">');
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
		aawrite('<Line Align="left">'.$r['ln'].' '.$r['fn'].'</Line>');
		aawrite('<Line Align="right" Size="double">'.$r['number'].'</Line>');
	}


	aawrite('<SoftKey index="1">');
	aawrite('<Label>OK</Label>');
	aawrite('<URI>SoftKey:Select</URI>');
	aawrite('</SoftKey>');
	aawrite('<SoftKey index="2">');
	aawrite('<Label>'.__('Anrufen').'</Label>');
	aawrite('<URI>Dial:'.$r['number'].'</URI>');
	aawrite('</SoftKey>');
	aawrite('<SoftKey index="4">');
	aawrite('<Label>'.__('Abbrechen').'</Label>');
	aawrite('<URI>SoftKey:Exit</URI>');
	aawrite('</SoftKey>');
	aawrite('</AastraIPPhoneFormattedTextScreen>');


} else if ($type=='gss'){

	$remote_addr = @$_SERVER['REMOTE_ADDR'];

	$user_id = $db->executeGetOne( 'SELECT `id` FROM `users` WHERE `current_ip`=\''. $remote_addr.'\'' );

	aawrite('<AastraIPPhoneFormattedTextScreen destroyOnExit="yes" cancelAction = "http://'.GS_PROV_HOST.':'.GS_PROV_PORT.'/aastra/pb.php?t=gs">');
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
		aawrite('<Line Align="left">'.$r['ln'].' '.$r['fn'].'</Line>');
		aawrite('<Line Align="right" Size="double">'.$r['number'].'</Line>');
	}

	aawrite('<SoftKey index="1">');
	aawrite('<Label>OK</Label>');
	aawrite('<URI>SoftKey:Select</URI>');
	aawrite('</SoftKey>');
	aawrite('<SoftKey index="2">');
	aawrite('<Label>'.__('Anrufen').'</Label>');
	aawrite('<URI>Dial:'.$r['number'].'</URI>');
	aawrite('</SoftKey>');
	aawrite('<SoftKey index="4">');
	aawrite('<Label>'.__('Abbrechen').'</Label>');
	aawrite('<URI>SoftKey:Exit</URI>');
	aawrite('</SoftKey>');
	aawrite('<SoftKey index="3">');
	aawrite('<Label>&lt;&lt;</Label>');
	aawrite('<URI>http://'.GS_PROV_HOST.':'.GS_PROV_PORT.'/aastra/pb.php?t=gs</URI>');
	aawrite('</SoftKey>');
	aawrite('<SoftKey index="6">');
	aawrite('<Label>&gt;&gt;</Label>');
	aawrite('<URI>SoftKey:Select</URI>');
	aawrite('</SoftKey>');
	aawrite('</AastraIPPhoneFormattedTextScreen>');

}

aastra_transmit();