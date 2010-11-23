<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007-2010, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
* 
* Author: Daniel Scheller <scheller@loca.net>
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

define( 'GS_VALID', true ); // this is a parent file

require_once( dirname(__FILE__) .'/../../../inc/conf.php' );
include_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/gettext.php' );
include_once( GS_DIR .'inc/string.php' );
require_once(GS_DIR ."inc/langhelper.php");

header( 'Content-Type: text/html; charset=utf-8' );
header( 'Expires: 0 ');
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );

$diallog_doctype = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";

//---------------------------------------------------------------------------

function _ob_send()
{
	if (! headers_sent())
	{
		header( 'Content-Type: text/html; charset=utf-8' );
		header( 'Content-Length: '. (int)@ob_get_length() );
	}

	@ob_end_flush();
	die();
}

function _err( $msg='' )
{
	@ob_end_clean();
	ob_start();

	echo '<html>',"\n";
	echo '<head><title>'. __('Fehler') .'</title></head>',"\n";
	echo '<body><b>'. __('Fehler') .'</b>: '. $msg .'</body>',"\n";
	echo '</html>',"\n";

	_ob_send();
}

//---------------------------------------------------------------------------

if (! gs_get_conf('GS_POLYCOM_PROV_ENABLED'))
{
	gs_log( GS_LOG_DEBUG, 'Polycom provisioning not enabled' );
	_err('Not enabled.');
}

$user = trim(@$_REQUEST['user']);

if (! preg_match('/^\d+$/', $user)) _err('Not a valid SIP user.');

$mac = preg_replace("/[^\dA-Z]/", "", strtoupper(trim(@$_REQUEST["mac"])));

$type = trim(@$_REQUEST['type']);
if (! in_array($type, array('in', 'out', 'missed', 'queue'), true)) $type = false;

if (isset($_REQUEST['delete'])) $delete = (int) $_REQUEST['delete'];

$db = gs_db_slave_connect();

// setup i18n stuff
gs_setlang(gs_get_lang_user($db, $user, GS_LANG_FORMAT_GS));
gs_loadtextdomain( 'gemeinschaft-gui' );
gs_settextdomain( 'gemeinschaft-gui' );

//--- get user_id
$user_id = (int)$db->executeGetOne( 'SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`=\''. $db->escape($user) .'\'' );
if ($user_id < 1) _err('Unknown user.');

//--- check user/ip/mac
$user_id_check = $db->executeGetOne("SELECT `user_id` FROM `phones` WHERE `mac_addr`='". $db->escape($mac) ."'");
if ($user_id != $user_id_check) _err("Not authorized");

$remote_addr = @$_SERVER["REMOTE_ADDR"];
$remote_addr_check = $db->executeGetOne("SELECT `current_ip` FROM `users` WHERE `id`=". $user_id);
if ($remote_addr != $remote_addr_check) _err("Not authorized");

unset($remote_addr_check);
unset($remote_addr);
unset($user_id_check);

$typeToTitle = array(
	'out'    => __("Gew\xC3\xA4hlt"),
	'missed' => __('Verpasst'),
	'in'     => __('Angenommen'),
	'queue'  => __('Warteschlangen')
);

ob_start();

$url_polycom_dl = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'. GS_PROV_PORT : '') . GS_PROV_PATH .'polycom/diallog.php';

if ( (isset($delete)) && $type )
{
//--- clear list (
	$db->execute(
		'DELETE FROM `dial_log` '.
		'WHERE '.
		'  `user_id`='. $user_id .' AND '.
		'  `type`=\'' . $type . '\''
	);

//--- ) clear list
}

#################################### INITIAL SCREEN {
if(!$type)
{
	//--- delete outdated entries
	$db->execute( 'DELETE FROM `dial_log` WHERE `user_id`='. $user_id .' AND `timestamp`<'. (time()-(int)GS_PROV_DIAL_LOG_LIFE) );

	echo $diallog_doctype ."\n";
	echo '<html>',"\n";
	echo '<head><title>'. __('Anruflisten') .'</title></head>',"\n";
	echo '<body><br />',"\n";

	foreach($typeToTitle as $t => $title)
	{
		$num_calls = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `dial_log` WHERE `user_id`='. $user_id .' AND `type`=\''. $t .'\'' );

		echo '- <a href="'. $url_polycom_dl .'?user='. $user .'&amp;mac='. $mac .'&amp;type='. $t .'">'. $title .'</a><br />',"\n";
	}

	echo '</body>',"\n";
	echo '</html>',"\n";
}

#################################### INITIAL SCREEN }



#################################### DIAL LOG {
else
{
	echo $diallog_doctype ."\n";

	echo '<html>',"\n";
	echo '<head><title>'. __('Anruflisten') .' - '. $typeToTitle[$type] .'</title></head>',"\n";
	echo '<body><br />',"\n";

	$query =
		'SELECT '.
		'  MAX(`timestamp`) `ts`, '.
		'  `number`, '.
		'  `remote_name`, '.
		'  `remote_user_id`, '.
		'  COUNT(*) `num_calls` '.
		'FROM `dial_log` '.
		'WHERE '.
		'  `user_id`='. $user_id .' AND '.
		'  `type`=\''. $type .'\'' .
		'GROUP BY `number` '.
		'ORDER BY `ts` DESC '.
		'LIMIT 20';

	$rs = $db->execute($query);

	if ($rs->numRows() == 0)
	{
		echo "<br />". __("Keine Eintr\xC3\xA4ge vom Typ") ."'<b>". $typeToTitle[$type] ."</b>'<br />\n";
	}
	else
	{
		echo '<table border="0" cellspacing="0" cellpadding="1" width="100%">',"\n";

		echo '<tr>';

		echo '<th width="30%">', __("Datum"), '</th>';
		echo '<th width="70%">', __("Nummer"), '</th></tr>',"\n";

		while ( $r = $rs->fetchRow() )
		{
			unset($num_calls);

			if ( $r['num_calls'] > 0 )
			{
				$num_calls = (int) $db->executeGetOne(
					'SELECT '.
					'  COUNT(*) '.
					'FROM `dial_log` '.
					'WHERE '.
					'  `user_id`='. $user_id .' AND '.
					'  `number`=\''. $r['number'] .'\' AND '.
					'  `type`=\''. $type .'\'' );
			}

			$entry_name = $r['number'];

			if ($r['remote_name'] != '')
			{
				$entry_name .= ' '. $r['remote_name'];
			}

			if ( date('dm') == date('dm', (int)$r['ts']) )
				$when = date('H:i', (int)$r['ts']);
			else
				$when = date('d.m.', (int) $r['ts']);

			echo '<tr>';

			echo '<td width="30%">'. $when .'</td>';
			echo '<td width="70%"><a href="tel://'. $r['number'].'">'. htmlEnt($entry_name);

			if ($num_calls > 0) echo ' ('. $num_calls .')';

			echo '</a></td></tr>',"\n";
		}

		echo '</table>',"\n";
	}

	echo '</body>',"\n";

	echo '<softkey index="1" label="', __("Leeren"), '" action="Softkey:Fetch;'. $url_polycom_dl .'?user='. $user .'&amp;mac='. $mac .'&amp;type='. $type .'&amp;delete=1" />',"\n";
	echo '<softkey index="2" label="', __("Beenden"), '" action="Softkey:Exit" />',"\n";
	echo '</html>',"\n";
}

#################################### DIAL LOG }

_ob_send();

?>
