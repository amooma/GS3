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
* Author: Henning Holtschneider, LocaNet oHG <henning@loca.net>
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
require_once( GS_DIR .'inc/prov-fns.php' );
include_once( GS_DIR .'inc/aastra-fns.php' );
include_once( GS_DIR .'inc/gettext.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callforward_activate.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callforward_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callforward_set.php' );
include_once( GS_DIR .'inc/string.php' );

function _err( $msg='' )
{
	aastra_textscreen( 'Error', ($msg != '' ? $msg : 'Unknown error'), 0, true );
	exit(1);
}

function _get_userid()
{
	global $_SERVER, $db;
	
	$remote_addr = @$_SERVER['REMOTE_ADDR'];
	$user_id = (int)$db->executeGetOne( 'SELECT `id` FROM `users` WHERE `current_ip`=\''. $db->escape($remote_addr) .'\'' );
	if ($user_id < 1) _err( 'Unknown user.' );
	return $user_id;
}

if ( !gs_get_conf('GS_AASTRA_PROV_ENABLED') )
{
	gs_log(GS_LOG_NOTICE, 'Aastra provisioning not enabled');
	_err('Not enabled.');
}

$db = gs_db_master_connect();

$prov_url_aastra = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'aastra/';

$ua = trim( @$_SERVER['HTTP_USER_AGENT'] );
if ( preg_match('/\sMAC:(00-08-5D-\w{2}-\w{2}-\w{2})\s/', $ua, $m) )
	$mac = preg_replace( '/[^0-9A-F]/', '', strToUpper($m[1]) );

$user_id = _get_userid();
$user = @gs_prov_get_user_info( $db, $user_id );
if (! is_array($user)) {
	_err( 'DB error.' );
}

$user_id_check = $db->executeGetOne("SELECT `user_id` FROM `phones` WHERE `mac_addr`='". $db->escape($mac) ."'");
if($user_id != $user_id_check) _err("Not authorized");

$remote_addr = @$_SERVER["REMOTE_ADDR"];
$remote_addr_check = $db->executeGetOne("SELECT `current_ip` FROM `users` WHERE `id`='". $user_id ."'");
if($remote_addr != $remote_addr_check) _err("Not authorized");

$action = trim(@$_REQUEST['a']);
$value  = trim(@$_REQUEST['v']);
$dialog = trim(@$_REQUEST['d']);

$url_aastra_cf = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'aastra/cf.php';

$callforwards = gs_callforward_get( $user['user'] );
if (isGsError($callforwards))
	_err( htmlEnt(__('Fehler')) . ': ' . htmlEnt($callforwards->getMsg()) );

// if callforward is active, do not display cf dialog. Disable call forward instead
if ($callforwards['internal']['always']['active'] == 'var')
	unset($dialog);

if ($dialog) {

	$xml = '<AastraIPPhoneInputScreen type="string" destroyOnExit="yes" displayMode="condensed" defaultIndex="1">' ."\n";
	$xml.= '<Title>'. htmlEnt(__('Rufumleitung')) .'</Title>' ."\n";
	$xml.= '<URL>'.$url_aastra_cf.'</URL>' ."\n";
	$xml.= '<Default></Default>' ."\n";
	$xml.= '<InputField type="empty"></InputField>' ."\n";
	$xml.= '<InputField type="number">' ."\n";
	$xml.= '	<Prompt>'.htmlEnt(__('Ziel')) .':</Prompt>' ."\n";
	$xml.= '	<Default>'.$callforwards['internal']['always']['number_var'].'</Default>' ."\n";
	$xml.= '	<Parameter>v</Parameter>' ."\n";
	$xml.= '	<Selection>1</Selection>' ."\n";
	$xml.= '</InputField>' ."\n";
	$xml.= '<SoftKey index="1">' ."\n";
	$xml.= '	<Label>'. htmlEnt(__('OK')) .'</Label>' ."\n";
	$xml.= '	<URI>SoftKey:Submit</URI>' ."\n";
	$xml.= '</SoftKey>' ."\n";
	$xml.= '<SoftKey index="4">' ."\n";
	$xml.= '	<Label>'. htmlEnt(__('Abbrechen')) .'</Label>' ."\n";
	$xml.= '	<URI>SoftKey:Exit</URI>' ."\n";
	$xml.= '</SoftKey>' ."\n";
	$xml.= '<SoftKey index="2">' ."\n";
	$xml.= '	<Label>&lt;--</Label>' ."\n";
	$xml.= '	<URI>SoftKey:BackSpace</URI>' ."\n";
	$xml.= '</SoftKey>' ."\n";
	$xml.= '</AastraIPPhoneInputScreen>' ."\n";
	
	aastra_transmit_str( $xml );

} else {

	if ( ($callforwards['internal']['always']['active'] == 'var')
		|| ($callforwards['external']['always']['active'] == 'var')
	   )
	{
		$callforwards['internal']['always']['active'] = 'no';
		$callforwards['external']['always']['active'] = 'no';
	} else {
		if ($value)
			$callforwards['internal']['always']['number_var'] = $value;
		if (! $callforwards['internal']['always']['number_var'] )
			_err(htmlEnt(__('Kein Umleitungsziel konfiguriert.')));
		$callforwards['external']['always']['number_var'] = $callforwards['internal']['always']['number_var'];
		$callforwards['internal']['always']['active'] = 'var';
		$callforwards['external']['always']['active'] = 'var';
	}

	gs_callforward_set($user['user'], 'internal', 'always', 'var', $callforwards['internal']['always']['number_var'], $callforwards['internal']['always']['timeout']);
	gs_callforward_set($user['user'], 'external', 'always', 'var', $callforwards['external']['always']['number_var'], $callforwards['external']['always']['timeout']);
	gs_callforward_activate($user['user'], 'internal', 'always', $callforwards['internal']['always']['active']);
	gs_callforward_activate($user['user'], 'external', 'always', $callforwards['external']['always']['active']);

	if ($callforwards['internal']['always']['active'] == 'var')
		aastra_textscreen(htmlEnt(__('Rufumleitung')), htmlEnt(__('Rufumleitung aktiviert')), 3);
	else
		aastra_textscreen(htmlEnt(__('Rufumleitung')), htmlEnt(__('Rufumleitung deaktiviert')), 3);

}
?>