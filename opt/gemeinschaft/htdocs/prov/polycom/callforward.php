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

define( 'GS_VALID', true );  // this is a parent file

require_once( dirname(__FILE__) .'/../../../inc/conf.php' );
require_once(GS_DIR .'inc/db_connect.php' );
require_once(GS_DIR ."inc/gettext.php");
require_once(GS_DIR ."inc/langhelper.php");

include_once( GS_DIR .'inc/gs-lib.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callforward_activate.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callforward_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callforward_set.php' );
include_once( GS_DIR .'inc/gs-fns/gs_vm_activate.php' );
include_once( GS_DIR .'inc/string.php' );

header( 'Content-Type: text/html; charset=utf-8' );
header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *"' );

$callforward_doctype = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";

//---------------------------------------------------------------------------

function _ob_send()
{
	if (! headers_sent()) {
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
	echo '<body><b>'. __('Fehler') .'</b>: '. htmlEnt($msg) .'</body>',"\n";
	echo '</html>',"\n";

	_ob_send();
}

function getUserID($ext)
{
	global $db;

	if (! preg_match('/^\d+$/', $ext)) _err('Invalid username');

	$user_id = (int)$db->executeGetOne( 'SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`=\''. $db->escape($ext) .'\'' );
	if ($user_id < 1) _err('Unknown user');
	return $user_id;
}

//---------------------------------------------------------------------------

if (! gs_get_conf('GS_POLYCOM_PROV_ENABLED')) {
	gs_log( GS_LOG_DEBUG, 'Polycom provisioning not enabled' );
	_err('Not enabled.');
}

$type = trim(@$_REQUEST['t']);
if (! in_array($type, array('internal', 'external', 'std', 'var', 'timeout'), true) )
{
	$type = false;
}

$db = gs_db_slave_connect();

$user = trim(@$_REQUEST['u']);
$user_id = getUserID($user);

// setup i18n stuff
gs_setlang(gs_get_lang_user($db, $user, GS_LANG_FORMAT_GS));
gs_loadtextdomain( 'gemeinschaft-gui' );
gs_settextdomain( 'gemeinschaft-gui' );

$tmp = array(
	15 => array('k' => 'internal',
	            'v' => gs_get_conf('GS_CLIR_INTERNAL', __('von intern'))),
	25 => array('k' => 'external',
	            'v' => gs_get_conf('GS_CLIR_EXTERNAL', __('von extern'))),
);

ksort($tmp);
foreach($tmp as $arr) {
	$typeToTitle[$arr['k']] = $arr['v'];
}

$url_polycom_provdir = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'. GS_PROV_PORT : '') . GS_PROV_PATH .'polycom/';

$url_polycom_callforward = $url_polycom_provdir .'callforward.php';
$url_polycom_menu = $url_polycom_provdir .'configmenu.php';

$cases = array(
	'always'  => __('immer'),
	'busy'    => __('besetzt'),
	'unavail' => __('keine Antw.'),
	'offline' => __('offline')
);

$actives = array(
	'no'  => __('Aus'),
	'std' => __('Std.'),
	'var' => __('Tmp.')
);

################################## SET FEATURE {

if ( ($type != false) && (isset($_REQUEST['value'])) )
{
	$value = trim(@$_REQUEST['value']);

	$user_name = $db->executeGetOne( 'SELECT `user` FROM `users` WHERE `id`=\''. $db->escape($user_id) .'\'');

	$timeout = (int)$db->executeGetOne( 'SELECT `timeout` FROM `callforwards` WHERE `user_id`=\''. $user_id .'\' AND `case`=\'unavail\' AND `source`=\'internal\'' );

	$num['std'] = $db->executeGetOne( 'SELECT `number_std` FROM `callforwards` WHERE `user_id`=\''. $user_id .'\' AND `case`=\'unavail\' AND `source`=\'internal\'' );
	$num['var'] = $db->executeGetOne( 'SELECT `number_var` FROM `callforwards` WHERE `user_id`=\''. $user_id .'\' AND `case`=\'unavail\' AND `source`=\'internal\'' );

	$vm['internal'] = (bool)$db->executeGetOne( 'SELECT `internal_active` FROM `vm` WHERE `user_id`=\''. $user_id .'\'');
	$vm['external'] = (bool)$db->executeGetOne( 'SELECT `external_active` FROM `vm` WHERE `user_id`=\''. $user_id .'\'');

	foreach ($cases as $case => $v) {
		$internal_val[$case] = 'no';
		$internal_val[$case] = $db->executeGetOne( 'SELECT `active` FROM `callforwards` WHERE `user_id`=\''. $user_id .'\' AND `case`=\''. $case .'\' AND `source`=\'internal\'' );
	}

	foreach($cases as $case => $v) {
		$external_val[$case] = 'no';
		$external_val[$case] = $db->executeGetOne( 'SELECT `active` FROM `callforwards` WHERE `user_id`=\''. $user_id .'\' AND `case`=\''. $case .'\' AND `source`=\'external\'' );
	}

	$write = 0;

	if ( (($type == 'internal') || ($type == 'external')) && (isset($_REQUEST['key'])) ) {
		$key = trim(@$_REQUEST['key']);
		if(isset($cases[$key])) {
			if($type == 'internal')
			{
				$internal_val[$key] = $value;
			}

			if($type == 'external')
			{
				$external_val[$key] = $value;
			}

			unset($_REQUEST['key']);
			$write = 1;
		}

		if($key == 'voicemail')
		{
			$value = (bool) $value;
			$vm[$type] = $value;
			unset($_REQUEST['key']);
			$write = 1;
		}
	} else if ($type == 'timeout') {
		$value = abs((int) $value);
		if ($value < 1)
			$value = 1;
		$timeout = $value;

		$write = 1;
		$type = false;
	} else if ( ($type == 'var') || ($type == 'std') ) {
		$num[$type] = preg_replace("/[^\d]/", '', $value);
		$write = 1;
		$type = false;
	}

	if ($write == 1) {
		foreach ($cases as $case => $gnore2) {
			$ret = gs_callforward_set($user_name, 'internal', $case, 'std', $num['std'], $timeout);
			$ret = gs_callforward_set($user_name, 'internal', $case, 'var', $num['var'], $timeout);
			$ret = gs_callforward_activate($user_name, 'internal', $case, $internal_val[$case]);
		}

		foreach ($cases as $case => $gnore2) {
			$ret = gs_callforward_set($user_name, 'external', $case, 'std', $num['std'], $timeout);
			$ret = gs_callforward_set($user_name, 'external', $case, 'var', $num['var'], $timeout);
			$ret = gs_callforward_activate($user_name, 'external', $case, $external_val[$case]);
		}

		gs_vm_activate($user_name, 'internal', $vm['internal']);
		gs_vm_activate($user_name, 'external', $vm['external']);
	}
}

################################# SET FEATURE }

#################################### SELECT PROPERTIES {

if ( (($type == 'internal') || ($type == 'external')) && (!isset($_REQUEST['key'])) ) {
	$mac = preg_replace('/[^\dA-Z]/', '', strtoupper(trim(@$_REQUEST['m'])));

	ob_start();

	echo $callforward_doctype ."\n";

	$user_id_check = $db->executeGetOne( 'SELECT `user_id` FROM `phones` WHERE `mac_addr`=\''. $db->escape($mac) .'\'' );
	if($user_id != $user_id_check)
		_err('Not authorized');

	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$remote_addr_check = $db->executeGetOne( 'SELECT `current_ip` FROM `users` WHERE `id`=\''. $user_id .'\'' );
	if ($remote_addr != $remote_addr_check)
		_err('Not authorized');

	$timeout = (int)$db->executeGetOne( 'SELECT `timeout` FROM `callforwards` WHERE `user_id`=\''. $user_id .'\' AND `case`=\'unavail\' AND `source`=\''. $type .'\'' );

	$vm = $db->executeGetOne( 'SELECT `'. $type .'_active` FROM `vm` WHERE `user_id`=\''. $user_id .'\'' );
	foreach ($cases as $case => $v)	{
		$val[$case] = 'no';
		$val[$case] = $db->executeGetOne( 'SELECT `active` FROM `callforwards` WHERE `user_id`=\''. $user_id .'\' AND `case`=\''. $case .'\' AND `source`=\''. $type .'\'' );
	}

	echo '<html>',"\n";
	echo '<head><title>'. htmlEnt(__("Rufumleitung")) .' - '. htmlEnt($typeToTitle[$type]) .'</title></head>',"\n";
	echo '<body><br />',"\n";

	echo '<table border="0" cellspacing="0" cellpadding="1" width="100%">',"\n";

	echo '<tr>';

	echo '<th colspan="2" width="100%" align="center">'. htmlEnt(__("Einstellungen")) .' \''. htmlEnt($typeToTitle[$type]) .'\'</th></tr>',"\n";

	foreach ($cases as $case => $v)	{
		echo '<tr>';

		echo '<td width="50%" align="right"><a href="'. $url_polycom_callforward .'?m='. $mac .'&amp;u='. $user .'&amp;t='. $type .'&amp;key='. $case .'">'. $v .':</a></td>';
		echo '<td width="50%" align="left">'. htmlEnt($actives[$val[$case]]) .'</td>';

		echo '</tr>',"\n";
	}

	//voicemail
	if ($vm == '1')
		$vmstate = __('Ein');
	else
		$vmstate = __('Aus');

	echo '<tr>';

	echo '<td width="50%" align="right"><a href="'. $url_polycom_callforward .'?m='. $mac .'&amp;u='. $user .'&amp;t='. $type .'&amp;key=voicemail">'. htmlEnt(__("Voicemail")) .':</a></td>';
	echo '<td width="50%" align="left">'. htmlEnt($vmstate) .'</td>';

	echo '</tr>',"\n";
	echo '</table>',"\n";

	echo "</body>\n";

	echo "</html>\n";

	_ob_send();
}

#################################### SELECT PROPERTIES }

#################################### SET CF-STATES {

if ( (($type == 'internal') || ($type == 'external')) && (isset($_REQUEST['key'])) ) {
	$mac = preg_replace('/[^\dA-Z]/', '', strtoupper(trim(@$_REQUEST['m'])));
	$key = trim(@$_REQUEST['key']);

	ob_start();

	echo $callforward_doctype ."\n";

	$user_id_check = $db->executeGetOne( 'SELECT `user_id` FROM `phones` WHERE `mac_addr`=\''. $db->escape($mac) .'\'' );
	if ($user_id != $user_id_check)
		_err('Not authorized');

	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$remote_addr_check = $db->executeGetOne( 'SELECT `current_ip` FROM `users` WHERE `id`=\''. $user_id .'\'' );
	if ($remote_addr != $remote_addr_check)
		_err('Not authorized');

	if ($key == 'voicemail') {
		$vm = $db->executeGetOne( 'SELECT `'. $type .'_active` FROM `vm` WHERE `user_id`=\''. $user_id  .'\'' );

		echo '<html>',"\n";
		echo '<head><title>'. htmlEnt(__("Rufumleitung")) .' - '. htmlEnt($typeToTitle[$type]) .'</title></head>',"\n";
		echo '<body><br />',"\n";

		echo '<table border="0" cellspacing="0" cellpadding="1" width="100%">',"\n";

		echo '<tr>';
		echo '<th width="100%" align="center">'. htmlEnt($typeToTitle[$type]) .': '. htmlEnt(__("Voicemail")) .'</th></tr>',"\n";

		echo '<tr><td width="100%" align="center"><a href="'. $url_polycom_callforward .'?m='. $mac .'&amp;u='. $user .'&amp;t='. $type .'&amp;key=voicemail&amp;value=1">'. (($vm == 1) ? '*' : '') . htmlEnt(__("Ein")) .'</a></td></tr>',"\n";
		echo '<tr><td width="100%" align="center"><a href="'. $url_polycom_callforward .'?m='. $mac .'&amp;u='. $user .'&amp;t='. $type .'&amp;key=voicemail&amp;value=0">'. (($vm == 0) ? '*' : '') . htmlEnt(__("Aus")) .'</a></td></tr>',"\n";

		echo '</table>',"\n";

		echo "</body>\n";

		echo "</html>\n";

		_ob_send();
	} else if (isset($cases[$key]))	{
		$val = 'no';
		$val = $db->executeGetOne( 'SELECT `active` FROM `callforwards` WHERE `user_id`=\''. $user_id .'\' AND `case`=\''. $key .'\' AND `source`=\''. $type .'\'' );

		echo '<html>',"\n";
		echo '<head><title>'. htmlEnt(__("Rufumleitung")) .' - '. htmlEnt($typeToTitle[$type]) .'</title></head>',"\n";
		echo '<body><br />',"\n";

		echo '<table border="0" cellspacing="0" cellpadding="1" width="100%">',"\n";

		echo '<tr>';
		echo '<th width="100%" align="center">'. htmlEnt($typeToTitle[$type]) .': '. htmlEnt($cases[$key]) .'</th></tr>',"\n";

		echo '<tr><td width="100%" align="center"><a href="'. $url_polycom_callforward .'?m='. $mac .'&amp;u='. $user .'&amp;t='. $type .'&amp;key='. $key .'&amp;value=no">'. (($val == 'no') ? '*' : '') . $actives['no'] .'</a></td></tr>',"\n";
		echo '<tr><td width="100%" align="center"><a href="'. $url_polycom_callforward .'?m='. $mac .'&amp;u='. $user .'&amp;t='. $type .'&amp;key='. $key .'&amp;value=std">'. (($val == 'std') ? '*' : '') . $actives['std'] .'</a></td></tr>',"\n";
		echo '<tr><td width="100%" align="center"><a href="'. $url_polycom_callforward .'?m='. $mac .'&amp;u='. $user .'&amp;t='. $type .'&amp;key='. $key .'&amp;value=var">'. (($val == 'var') ? '*' : '') . $actives['var'] .'</a></td></tr>',"\n";

		echo '</table>',"\n";

		echo '</body>',"\n";

		echo '</html>',"\n";

		_ob_send();
	}
}

#################################### SET CF-STATES }

#################################### SET PHONENUMBERS {

if( (($type == 'std') || ($type == 'var')) && (!isset($_REQUEST['value'])) ) {
	$mac = preg_replace('/[^\dA-Z]/', '', strtoupper(trim(@$_REQUEST['m'])));

	if($type == 'var') $pagetitle = __("Tempor\xC3\xA4re Nummer");
	else $pagetitle = __("Standardnummer");

	ob_start();

	echo $callforward_doctype ."\n";

	$user_id_check = $db->executeGetOne( 'SELECT `user_id` FROM `phones` WHERE `mac_addr`=\''. $db->escape($mac) .'\'' );
	if ($user_id != $user_id_check)
		_err('Not authorized');

	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$remote_addr_check = $db->executeGetOne( 'SELECT `current_ip` FROM `users` WHERE `id`=\''. $user_id .'\'' );
	if ($remote_addr != $remote_addr_check)
		_err('Not authorized');

	$number = $db->executeGetOne( 'SELECT `number_'. $type .'` FROM `callforwards` WHERE `user_id`=\''. $user_id .'\' AND `case`=\'unavail\' AND `source`=\'internal\'' );

	foreach ($cases as $case => $v)	{
		$val[$case] = 'no';
		$val[$case] = $db->executeGetOne( 'SELECT `active` FROM `callforwards` WHERE `user_id`=\''. $user_id .'\' AND `case`=\''. $case .'\' AND `source`=\''. $type .'\'' );
	}

	echo '<html>',"\n";
	echo '<head><title>'. htmlEnt(__("Rufumleitung")) .' - '. htmlEnt($pagetitle) .'</title></head>',"\n";
	echo '<body><br />',"\n";

	echo '<form name="cfdest" method="GET" action="'. $url_polycom_callforward .'">',"\n";
	echo '<input type="hidden" name="u" value="'. $user .'" />';
	echo '<input type="hidden" name="m" value="'. $mac .'" />';
	echo '<input type="hidden" name="t" value="'. $type .'" />',"\n";

	echo '<table border="0" cellspacing="0" cellpadding="1" width="100%">',"\n";
	echo '<tr>';
	echo '<th align="center" width="100%">'. htmlEnt(__("Rufumleitungsziel")) .': '. htmlEnt($pagetitle) .'</th>';
	echo '</tr>';

	echo '<tr><td align="center" width="100%"><input type="text" name="value" value="'. $number .'" /></td></tr>',"\n";
	echo '<tr><td align="center" width="100%"><input type="submit" value=" '. htmlEnt(__("Speichern")) .' " /></td></tr>',"\n";
	echo '</table>',"\n";

	echo '</form>',"\n";

	echo '</body>',"\n";

	echo '</html>',"\n";

	_ob_send();
}

#################################### SELECT PHONENUMBERS }

#################################### SET TIMEOUT {

if ( ($type == 'timeout') && (!isset($_REQUEST['value'])) )
{
	$mac = preg_replace('/[^\dA-Z]/', '', strtoupper(trim(@$_REQUEST['m'])));
	$user_name = $db->executeGetOne( 'SELECT `user` FROM `users` WHERE `id`=\''. $db->escape($user_id) .'\'' );

	$callforwards = gs_callforward_get($user_name);
	$pagetitle = __("Timeout bei keine Antwort");

	ob_start();

	echo $callforward_doctype ."\n";

	$user_id_check = $db->executeGetOne( 'SELECT `user_id` FROM `phones` WHERE `mac_addr`=\''. $db->escape($mac) .'\'' );
	if ($user_id != $user_id_check)
		_err('Not authorized');

	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$remote_addr_check = $db->executeGetOne( 'SELECT `current_ip` FROM `users` WHERE `id`=\''. $user_id .'\'' );
	if ($remote_addr != $remote_addr_check)
		_err('Not authorized');

	$timeout = (int)@$callforwards['internal']['unavail']['timeout'];

	echo '<html>',"\n";
	echo '<head><title>'. htmlEnt(__("Rufumleitung")) .' - '. htmlEnt(__("Timeout")) .'</title></head>',"\n";
	echo '<body><br />',"\n";

	echo '<form name="cfdest" method="GET" action="'. $url_polycom_callforward .'">',"\n";
	echo '<input type="hidden" name="u" value="'. $user .'" />';
	echo '<input type="hidden" name="m" value="'. $mac .'" />';
	echo '<input type="hidden" name="t" value="timeout" />',"\n";

	echo '<table border="0" cellspacing="0" cellpadding="1" width="100%">',"\n";
	echo '<tr>';
	echo '<th align="center" width="100%">'. htmlEnt($pagetitle) .'</th>';
	echo '</tr>';

	echo '<tr><td align="center" width="100%"><input type="text" name="value" value="'. $timeout .'" /></td></tr>',"\n";
	echo '<tr><td align="center" width="100%"><input type="submit" value=" '. htmlEnt(__("Speichern")) .' " /></td></tr>',"\n";
	echo '</table>',"\n";

	echo '</form>',"\n";

	echo '</body>',"\n";

	echo '</html>',"\n";

	_ob_send();
}

#################################### SELECT TIMEOUT }

#################################### INITIAL SCREEN {

if (!$type) {
	$mac = preg_replace('/[^\dA-Z]/', '', strtoupper(trim(@$_REQUEST['m'])));

	ob_start();

	echo $callforward_doctype ."\n";
	echo '<html>',"\n";
	echo '<head><title>'. htmlEnt(__("Rufumleitung")) .'</title></head>',"\n";
	echo '<body><br />',"\n";

	echo '- <a href="'. $url_polycom_callforward .'?m='. $mac .'&amp;u='. $user .'&amp;t=std">'. htmlEnt(__("Standardnummer")) .'</a><br />',"\n";
	echo "- <a href=\"". $url_polycom_callforward ."?m=". $mac ."&amp;u=". $user ."&amp;t=var\">". htmlEnt(__("Tempor\xC3\xA4re Nummer")) ."</a><br />\n";

	foreach($typeToTitle as $t => $title) {
		echo '- <a href="'. $url_polycom_callforward .'?m='. $mac .'&amp;u='. $user .'&amp;t='. $t .'">'. htmlEnt($title) .'</a><br />',"\n";
	}

	echo '- <a href="'. $url_polycom_callforward .'?m='. $mac .'&amp;u='. $user .'&amp;t=timeout">'. htmlEnt(__("Nicht-Antwort-Timeout")) .'</a><br />',"\n";

	echo '</body>',"\n";

	echo '</html>',"\n";

	_ob_send();
}

#################################### INITIAL SCREEN }

?>