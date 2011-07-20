<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 5500 $
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
	xml('  <IppAlert Type="INFO" Delay="3000">');
	xml('    <Title>'. __('Anruf') .'</Title>');
	xml('    <Text>'. __('Rufe an:') .' '. $number .'</Text>');
	xml('    <Image></Image>');
	xml('  </IppAlert>');
	xml('  <IppAction Type="MAKECALL">');
	xml('    <Number>'. $number .'</Number>');
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
	xml('  <IppAlert Type="'.$alert_type.'" Delay="5000">');
	xml('    <Title>'. __('Fehler') .'</Title>');
	xml('    <Text>'. $message .'</Text>');
	xml('    <Image></Image>');
	xml('  </IppAlert>');
	xml('</IppScreen>');
	xml('</IppDisplay>');
	xml_output();
}

$user         = trim(@$_REQUEST['user'       ]);
$phonenumber  = trim(@$_REQUEST['phonenumber']);

if (! $user) $user = $phonenumber;

$url_prov_siemens = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'siemens/';
$url     = $url_prov_siemens .'dial-log/dlog.php';
$img_url = $url_prov_siemens .'img/';

if (! preg_match('/^\d+$/', $user)) {
	write_alert( 'Unknown user.' );
}

$type = trim(@$_REQUEST['type']);
if (! in_array( $type, array('in','out','missed', 'queue'), true )) {
	$type = false;
}

$dial = trim(@$_REQUEST['dial']);


$db = gs_db_slave_connect();


# get user_id
#
$user_id = (int)$db->executeGetOne( 'SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`=\''. $db->escape($user) .'\'' );
if ($user_id < 1)
	write_alert( 'Unknown user.' );



$typeToTitle = array(
	'out'    => __("Gew\xC3\xA4hlt"),
	'missed' => __("Verpasst"),
	'in'     => __("Angenommen"),
	'queue'  => __("Warteschlangen")
);



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
	xml('<IppScreen ID="1" HiddenCount="1" CommandCount="1">');
	xml('  <IppList Type="IMPLICIT" Count="'. count($typeToTitle) .'">');
	xml('    <Title>'. $user .' - '. __('Anruflisten') .'</Title>');
	xml('    <Url>'.$url.'</Url>');
	$i=0;
	foreach ($typeToTitle as $t => $title) {
		$num_calls = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `dial_log` WHERE `user_id`='. $user_id .' AND `type`=\''. $t .'\'' );
		$i++;
		xml('    <Option ID="'.$i.'" Selected="'.($i===1 ?'TRUE':'FALSE').'" Key="type" Value="'.$t.'">');
		switch ($t) {
			case 'out':
				$image = $img_url.'keyboard.png';
				break;
			case 'missed':
				$image = $img_url.'karm.png';
				break;
			case 'in':
				$image = $img_url.'yast_sysadmin.png';
				break;
			default:
				$image="";
		}
		xml('      <OptionText>'. $title .' ('.$num_calls.')' .'</OptionText>');
		xml('      <Image>'.$image.'</Image>');
		xml('    </Option>');
	}
	xml('  </IppList>');
	xml('  <IppHidden Type="VALUE" Key="user">');
	xml('    <Value>'.$user.'</Value>');
	xml('  </IppHidden>');
	xml('</IppScreen>');
	xml('</IppDisplay>');
	
}


#########################################################
# User dial logs
#########################################################

else {
	if ( $type == queue ){	
			$query =
		'SELECT SQL_CALC_FOUND_ROWS
			`timestamp` `ts`, `number`, `remote_name`, `remote_user_id`
		FROM `dial_log`
		WHERE
			`user_id`='. $user_id .' AND
			`type`=\''. $type .'\'
		ORDER BY `ts` DESC
		LIMIT 20';
	}else{
			 $query =	
		'SELECT SQL_CALC_FOUND_ROWS
			`timestamp` `ts`, `number`, `remote_name`, `remote_user_id`,
		COUNT(*) `num_calls`
		FROM `dial_log`
		WHERE
			`user_id`='. $user_id .' AND
			`type`=\''. $type .'\'
		GROUP BY `number`	
		ORDER BY `ts` DESC
		LIMIT 20';
	}
	$rs = $db->execute($query);
	$per_page = 15;
	$num_total = @$db->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	$entries =  (($num_total > $per_page) ? $per_page : $num_total );
	
	xml('<'.'?xml version="1.0" encoding="UTF-8" ?'.'>');
	xml('<IppDisplay>');
	xml('<IppScreen ID="1" HiddenCount="1" CommandCount="1">');
	xml('  <IppList Type="IMPLICIT" Count="'.($entries+1).'">');
	xml('    <Title>'. $user .' - '. (@$typeToTitle[$type]) .'</Title>');
	xml('    <Url>'.$url.'</Url>');
	
	$i=1;
	xml('    <Option ID="'.$i.'" Selected="TRUE" Key="type" Value="none">');
	xml('      <OptionText>'. __("Zur\xC3\xBCck") .'</OptionText>');
	xml('      <Image>'.$img_url.'previous.png</Image>');
	xml('    </Option>');
	
	while ($r = $rs->fetchRow()) {
		$i++;
		$entry_name = $r['number'];
		if ($r['remote_name'] != '') {
			$entry_name .= ' '. $r['remote_name'];
		}
		setlocale(LC_TIME,gs_get_conf('GS_INTL_LANG').'.utf8');
		if (date('dm') == date('dm', (int)$r['ts']))
			$when = date('H:i', (int)$r['ts']);
		else
			$when = strftime('%d.%b', (int)$r['ts']);
		$entry_name = $when .'  '. $entry_name;
		if ($r['num_calls'] > 1) {
			$entry_name .= ' ('. $r['num_calls'] .')';
		}
		xml('    <Option ID="'.$i.'" Selected="FALSE" Key="dial" Value="'.$r['number'].'">');
		xml('      <OptionText>'.$entry_name.'</OptionText>');
		xml('      <Image></Image>');
		xml('    </Option>');
	}
	
	xml('  </IppList>');
	xml('  <IppHidden Type="VALUE" Key="user">');
	xml('    <Value>'.$user.'</Value>');
	xml('  </IppHidden>');
	xml('</IppScreen>');
	xml('</IppDisplay>');
	
}


#########################################################
# Output
#########################################################

xml_output();

?>
