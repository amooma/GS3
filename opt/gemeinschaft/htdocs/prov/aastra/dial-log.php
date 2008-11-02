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
include_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/aastra-fns.php' );
include_once( GS_DIR .'inc/gettext.php' );

$xml_buffer = '';

function _err( $msg='' )
{
	//aastra_textscreen( 'Error', ($msg != '' ? $msg : 'Unknown error') );
	exit(1);  //FIXME - return XML
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
if (! in_array( $type, array('in','out','missed', 'ind','outd','missedd'), true )) {
	$type = false;
}

$timestamp = (int)@$_REQUEST['e'];
$number = trim( @$_REQUEST['n'] );

$num_results = (int)gs_get_conf('GS_AASTRA_PROV_PB_NUM_RESULTS', 10);
$db = gs_db_slave_connect();

$typeToTitle = array(
	'out'    => __("Gew\xC3\xA4hlt"),
	'missed' => __("Verpasst"),
	'in'     => __("Angenommen")
);

$user_id = _get_userid();


$url_aastra_dl = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'aastra/dial-log.php';


if (! $type) {
	
	# delete outdated entries
	#
	@$db->execute( 'DELETE FROM `dial_log` WHERE `user_id`='. $user_id .' AND `timestamp`<'. (time()-(int)GS_PROV_DIAL_LOG_LIFE) );
	
	
	aastra_write('<AastraIPPhoneTextMenu destroyOnExit="yes" LockIn="no" style="none">');
	aastra_write('<Title>'. __('Anrufliste') .'</Title>');
	
	foreach ($typeToTitle as $key => $title) {
		aastra_write('<MenuItem>');
		aastra_write('<Prompt>'.$title.'</Prompt>');
		aastra_write('<URI>'. $url_aastra_dl .'?t='.$key.'</URI>');
		//aastra_write('<Selection>0&amp;menu_pos=1</Selection>'."\n";
		aastra_write('</MenuItem>');
	} 
	
	aastra_write('<SoftKey index="1">');
	aastra_write('<Label>'. __('OK') .'</Label>');
	aastra_write('<URI>SoftKey:Select</URI>');
	aastra_write('</SoftKey>');
	aastra_write('<SoftKey index="4">');
	aastra_write('<Label>'. __('Abbrechen') .'</Label>');
	aastra_write('<URI>SoftKey:Exit</URI>');
	aastra_write('</SoftKey>');
	aastra_write('</AastraIPPhoneTextMenu>');
	
} elseif ($type==='out' || $type==='in' || $type==='missed') {
	
	aastra_write('<AastraIPPhoneTextMenu destroyOnExit="yes" LockIn="no" style="none" cancelAction="'. $url_aastra_dl .'">');
	aastra_write('<Title>'. $typeToTitle[$type] .'</Title>');
	
	$query =
'SELECT
	MAX(`timestamp`) `ts`, `number`, `remote_name`, `remote_user_id`,
	COUNT(*) `num_calls`
FROM `dial_log`
WHERE
	`user_id`='. $user_id .' AND
	`type`=\''. $type .'\'
GROUP BY `number`
ORDER BY `ts` DESC
LIMIT '.$num_results;
	
	//echo $query;
	
	$rs = $db->execute( $query );
	if ($rs) {
		while ($r = $rs->fetchRow()) {
			
			$entry_name = $r['number'];
			if ($r['remote_name'] != '') {
				$entry_name .= ' '. $r['remote_name'];
			}
			if (date('dm') == date('dm', (int)$r['ts']))
				$when = date('H:i', (int)$r['ts']);
			else
				$when = date('d.m.', (int)$r['ts']);
			$entry_name = $when .'  '. $entry_name;
			if ($r['num_calls'] > 1) {
				$entry_name .= ' ('. $r['num_calls'] .')';
			}
			aastra_write('<MenuItem>');
			aastra_write('<Prompt>'. $entry_name .'</Prompt>');
			aastra_write('<Dial>'. $r['number'] .'</Dial>');
			aastra_write('<URI>'. $url_aastra_dl .'?t='.$type.'d&amp;e='.$r['ts'] .'</URI>');
			aastra_write('</MenuItem>');
			
		}
	}
	
	aastra_write('<SoftKey index="1">');
	aastra_write('<Label>'. __('OK') .'</Label>');
	aastra_write('<URI>SoftKey:Select</URI>');
	aastra_write('</SoftKey>');
	aastra_write('<SoftKey index="2">');
	aastra_write('<Label>'. __('Anrufen') .'</Label>');
	aastra_write('<URI>SoftKey:Dial2</URI>');
	aastra_write('</SoftKey>');
	aastra_write('<SoftKey index="4">');
	aastra_write('<Label>'. __('Abbrechen') .'</Label>');
	aastra_write('<URI>SoftKey:Exit</URI>');
	aastra_write('</SoftKey>');
	
	aastra_write('</AastraIPPhoneTextMenu>');
	
	
} elseif ($type==='outd' || $type==='ind' || $type==='missedd') {
	
	$type = substr($type,0,strlen($type)-1);
	aastra_write('<AastraIPPhoneFormattedTextScreen destroyOnExit="yes" cancelAction="'. $url_aastra_dl .'?t='.$type.'">');
	
	$query =
'SELECT
	`d`.`timestamp` `ts`, `d`.`number` `number`, `d`.`remote_name` `remote_name`, `d`.`remote_user_id` `remote_user_id`, `u`.`firstname` `fn`, `u`.`lastname` `ln`,
	COUNT(*) `num_calls`
FROM
	`dial_log` `d` LEFT JOIN
	`users` `u` ON (`u`.`id`=`d`.`remote_user_id`)
WHERE
	`d`.`user_id`='. $user_id .' AND
	`d`.`type`=\''. $type .'\' AND
	`d`.`timestamp`='. $timestamp .'
GROUP BY `number`
LIMIT 1';
	
	//echo $query;
	
	$rs = $db->execute( $query );
	if ($rs->numRows() !== 0) {
		
		$r = $rs->fetchRow();
		
		$name = '';
		if ($r['remote_name'] != '') {
			if ($r['ln'] != '') $name = $r['ln'];
			if ($r['ln'] != '') $name.= ', '.$r['fn'];
			if ($name == '') $name = $r['remote_name'];
		} 
		
		$when = date('d.m.Y H:i:s', (int)$r['ts']);
		
		if ($r['num_calls'] > 1) {
			$num_calls = ' ('. $r['num_calls'] .')';
		}		
		
		aastra_write('<Line Align="left">'. $name .'</Line>');
		aastra_write('<Line Align="right" Size="double">'. $r['number'] .'</Line>');
		aastra_write('<Line Align="left">'. $when .'</Line>');
	}
	
	aastra_write('<SoftKey index="2">');
	aastra_write('<Label>'. __('Anrufen') .'</Label>');
	aastra_write('<URI>Dial:'. $r['number'] .'</URI>');
	aastra_write('</SoftKey>');
	aastra_write('<SoftKey index="4">');
	aastra_write('<Label>'. __('Abbrechen') .'</Label>');
	aastra_write('<URI>SoftKey:Exit</URI>');
	aastra_write('</SoftKey>');
	
	aastra_write('</AastraIPPhoneFormattedTextScreen>');
	
}

aastra_transmit();

?>