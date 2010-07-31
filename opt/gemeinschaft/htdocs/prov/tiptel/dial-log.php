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
include_once( GS_DIR .'inc/string.php' );
include_once( GS_DIR .'inc/gettext.php' );

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


if (! gs_get_conf('GS_TIPTEL_PROV_ENABLED')) {
	gs_log( GS_LOG_DEBUG, "Tiptel provisioning not enabled" );
	_err( 'Not enabled.' );
}


$user = trim( @ $_REQUEST['u'] );
if (! preg_match('/^\d+$/', $user))
	_err( 'Not a valid SIP user.' );
$type = trim( @ $_REQUEST['type'] );
if (! in_array( $type, array('in','out','missed','queue'), true ))
	$type = false;


$db = gs_db_slave_connect();

# get user_id
#
$user_id = (int)$db->executeGetOne( 'SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`=\''. $db->escape($user) .'\'' );
if ($user_id < 1)
	_err( 'Unknown user.' );


$typeToTitle = array(
	'out'    => __("Gew\xC3\xA4hlt"),
	'missed' => __("Verpasst"),
	'in'     => __("Angenommen"),
	'queue'  => __("Warteschlangen")
);



ob_start();


$url_tiptel_dl = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'tiptel/dial-log.php';


#################################### INITIAL SCREEN {
if (! $type) {
	
	# delete outdated entries
	#
	$db->execute( 'DELETE FROM `dial_log` WHERE `user_id`='. $user_id .' AND `timestamp`<'. (time()-(int)GS_PROV_DIAL_LOG_LIFE) );
	
	
	
	echo '<?','xml version="1.0" encoding="utf-8"?','>', "\n";
	echo
		'<TiptelIPPhoneTextMenu>', "\n",
			'<Title>', __('Anruflisten') ,'</Title>', "\n";
	
	foreach ($typeToTitle as $t => $title) {
		
		$num_calls = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `dial_log` WHERE `user_id`='. $user_id .' AND `type`=\''. $t .'\'' );
		//if ($num_calls > 0) {
			echo
				"\n",
				'<MenuItem>', "\n",
					'<Prompt>', tiptelXmlEsc( $title ) ,'</Prompt>', "\n",
					'<URI>', $url_tiptel_dl , '</URI>', "\n",
					'<Selection>', tiptelXmlEsc('0&u='.$user.'&type='.$t), '</Selection>', "\n",
				'</MenuItem>', "\n";
		//}
	}
	
	echo
		"\n",
		'</TiptelIPPhoneTextMenu>';
	
}
#################################### INITIAL SCREEN }



#################################### DIAL LOG {
else {
	
	echo '<?','xml version="1.0" encoding="utf-8"?','>', "\n";
	if ( $type === 'queue' ){	
			$query =
		'SELECT
			`timestamp` `ts`, `number`, `remote_name`, `remote_user_id`
		FROM `dial_log`
		WHERE
			`user_id`='. $user_id .' AND
			`type`=\''. $type .'\'
		ORDER BY `ts` DESC
		LIMIT 20';
	} else {
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
		LIMIT 20';
	}
	$rs = $db->execute( $query );
	
	if ($rs->numRows() > 0) {
		echo
			'<TiptelIPPhoneDirectory style="radio">', "\n",
				'<Title>', tiptelXmlEsc( $typeToTitle[$type]), '</Title>', "\n";
		
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
			echo
				"\n",
				'<MenuItem>', "\n",
					'<Prompt>', tiptelXmlEsc( $entry_name ) ,'</Prompt>', "\n",
					'<URI>', tiptelXmlEsc( $r['number'] ) ,'</URI>', "\n",
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
				'<Label></Label>', "\n",
				'<URI></URI>', "\n",
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
		
		echo
			"\n",
			'</TiptelIPPhoneDirectory>';
	} else {
		echo
			'<TiptelIPPhoneTextScreen>', "\n",
				'<Title>', tiptelXmlEsc( $typeToTitle[$type] ), '</Title>', "\n",
				'<Text>', tiptelXmlEsc(__("Keine Eintr\xC3\xA4ge")), '</Text>', "\n",
			'</TiptelIPPhoneTextScreen>', "\n";
	}
	
}
#################################### DIAL LOG }


_ob_send();

?>