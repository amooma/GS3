<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 3307 $
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
*
* Author: Andreas Neugebauer <neugebauer@loca.net> - LocaNet oHG
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

require_once( '../../../inc/conf.php' );
require_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/gs-lib.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_external_numbers_get.php');
require_once( GS_DIR .'inc/gettext.php' );
require_once( GS_DIR .'inc/langhelper.php' );


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
	echo '<?','xml version="1.0" encoding="utf-8"?','>', "\n",
	     '<SnomIPPhoneText>', "\n",
	       '<Title>', 'Error', '</Title>', "\n",
	       '<Text>', snomXmlEsc( 'Error: '. $msg ), '</Text>', "\n",
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
if (! in_array( $type, array(), true )) {
	$type = false;
}


$db = gs_db_slave_connect();

$user = trim( @$_REQUEST['u'] );
$user_id = getUserID( $user );

// setup i18n stuff
gs_setlang(gs_get_lang_user($db, $user, GS_LANG_FORMAT_GS));
gs_loadtextdomain("gemeinschaft-gui");
gs_settextdomain("gemeinschaft-gui");

$tmp = array();
if (gs_get_conf('GS_PB_IMPORTED_ENABLED')) {
	$pos = (int)gs_get_conf('GS_PB_IMPORTED_ORDER', 9) * 10;
	$tmp[$pos] = array(
	          'k' => 'imported',
	          'v' => gs_get_conf('GS_PB_IMPORTED_TITLE', __("Importiert"))
	);
}
kSort($tmp);
foreach ($tmp as $arr) {
	$typeToTitle[$arr['k']] = $arr['v'];
}


$url_snom_extnumbers = GS_PROV_SCHEME .'://'. GS_PROV_HOST .(GS_PROV_PORT==80 ? '' : (':'. GS_PROV_PORT)). GS_PROV_PATH .'snom/extnumbers.php';
$url_snom_menu = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'snom/menu.php';


#################################### INITIAL SCREEN {
if (! $type) {
	
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['m'] )));
	$user_name = $db->executeGetOne( 'SELECT `user` FROM `users` WHERE `id`=\''. $db->escape($user_id) .'\'' );
	
	$enumbers = gs_user_external_numbers_get( $user_name );
	if (isGsError($enumbers)) {
	        _err('Fehler beim Abfragen.');
		
	 }
	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>', "\n",
	     '<SnomIPPhoneMenu>', "\n",
	       '<Title>'. __("externe Nummern") .'</Title>', "\n\n";
	foreach ($enumbers as $extnumber) {
		echo '<MenuItem>', "\n",
		       '<Name>', snomXmlEsc($extnumber), '</Name>', "\n",
		       '<URL>',$url_snom_menu,'?t=forward&m=',$mac ,'&u=', $user,'</URL>', "\n",
		     '</MenuItem>', "\n\n";
		# in XML the & must normally be encoded as &amp; but not for
		# the stupid Snom!
	}
	defineBackMenu();
	echo '</SnomIPPhoneMenu>', "\n";
	_ob_send();
	
}
#################################### INITIAL SCREEN }


function defineBackMenu()
{
	global $user, $type, $mac, $url_snom_menu;
	
	$args = array();
		$args[] = 'm='. $mac;
		$args[] = 'u='. $user;
		$args[] = 't=forward';
	
	echo '<SoftKeyItem>',
		'<Name>#</Name>',
		'<URL>', $url_snom_menu, '?', implode('&', $args), '</URL>',
		'</SoftKeyItem>', "\n";
	echo '<SoftKeyItem>',
		'<Name>F1</Name>',
		'<Label>' ,snomXmlEsc(__('Zurück')),'</Label>',
		'<URL>', $url_snom_menu, '?', implode('&', $args), '</URL>',
		'</SoftKeyItem>', "\n";
	echo '<SoftKeyItem>',
		'<Name>F4</Name>',
		'<Label>' ,snomXmlEsc(__('Zurück')),'</Label>',
		'<URL>', $url_snom_menu, '?', implode('&', $args), '</URL>',
		'</SoftKeyItem>', "\n";
	# Snom does not understand &amp; !
}


?>
