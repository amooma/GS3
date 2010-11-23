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
include_once( GS_DIR .'inc/langhelper.php' );

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


if (! gs_get_conf('GS_SNOM_PROV_ENABLED')) {
	gs_log( GS_LOG_DEBUG, "Snom provisioning not enabled" );
	_err( 'Not enabled.' );
}


$user = trim( @ $_REQUEST['user'] );
if (! preg_match('/^\d+$/', $user))
	_err( 'Not a valid SIP user.' );

$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['mac'] )));

$type = trim( @ $_REQUEST['type'] );
if (! in_array( $type, array('in','out','missed','queue'), true ))
	$type = false;

$db = gs_db_slave_connect();

# get user_id
#
$user_id = (int)$db->executeGetOne( 'SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`=\''. $db->escape($user) .'\'' );
if ($user_id < 1)
	_err( 'Unknown user.' );

# user/ip/mac check
$user_id_check = $db->executeGetOne( 'SELECT `user_id` FROM `phones` WHERE `mac_addr`=\''. $db->escape($mac) .'\'' );
if ($user_id != $user_id_check) _err( 'Not authorized' );

$remote_addr = @$_SERVER['REMOTE_ADDR'];
$remote_addr_check = $db->executeGetOne( 'SELECT `current_ip` FROM `users` WHERE `id`='. $user_id );
if ($remote_addr != $remote_addr_check) _err( 'Not authorized' );

unset($remote_addr_check);
unset($remote_addr);
unset($user_id_check);

// setup i18n stuff
gs_setlang( gs_get_lang_user($db, $user, GS_LANG_FORMAT_GS) );
gs_loadtextdomain( 'gemeinschaft-gui' );
gs_settextdomain( 'gemeinschaft-gui' );

$typeToTitle = array(
	'out'    => __("Gew\xC3\xA4hlt"),
	'missed' => __("Verpasst"),
	'in'     => __("Angenommen"),
	'queue'  => __("Warteschlangen")
);



ob_start();


$url_snom_dl = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'snom/dial-log.php';

#################################### INITIAL SCREEN {
if (! $type) {
	
	# delete outdated entries
	#
	$db->execute( 'DELETE FROM `dial_log` WHERE `user_id`='. $user_id .' AND `timestamp`<'. (time()-(int)GS_PROV_DIAL_LOG_LIFE) );
	
	
	
	echo '<?','xml version="1.0" encoding="utf-8"?','>', "\n";
	echo
		'<SnomIPPhoneMenu>', "\n",
			'<Title>', __('Anruflisten') ,'</Title>', "\n";
	
	foreach ($typeToTitle as $t => $title) {
		
		$num_calls = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `dial_log` WHERE `user_id`='. $user_id .' AND `type`=\''. $t .'\'' );
		//if ($num_calls > 0) {
			echo
				"\n",
				'<MenuItem>', "\n",
					'<Name>', snomXmlEsc( $title ) ,'</Name>', "\n",
					'<URL>', $url_snom_dl ,'?user=',$user, '&mac=',$mac, '&type=',$t, '</URL>', "\n",
				'</MenuItem>', "\n";
			# Snom does not understand &amp; !
		//}
	}
	
	echo
		"\n",
		'</SnomIPPhoneMenu>';
	
}
#################################### INITIAL SCREEN }



#################################### DIAL LOG {
else {
	
	echo '<?','xml version="1.0" encoding="utf-8"?','>', "\n";
	if ($type === 'queue'){	
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
	
	echo
		'<SnomIPPhoneDirectory>', "\n",
			'<Title>', snomXmlEsc( $typeToTitle[$type] ) ,
			($rs->numRows() == 0 ? ' ('.snomXmlEsc(__('keine')).')' : '') ,
			'</Title>', "\n";
	
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
			'<DirectoryEntry>', "\n",
				'<Name>', snomXmlEsc( $entry_name ) ,'</Name>', "\n",
				'<Telephone>', snomXmlEsc( $r['number'] ) ,'</Telephone>', "\n",
			'</DirectoryEntry>', "\n";
		
	}
	
	echo
		"\n",
		'</SnomIPPhoneDirectory>';
	
}
#################################### DIAL LOG }


_ob_send();

?>
