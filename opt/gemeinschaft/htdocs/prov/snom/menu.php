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
if (! in_array( $type, array('forward'), true )) {
	$type = false;
}


$db = gs_db_slave_connect();

$tmp = array();
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


$url_snom_provdir = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'snom/';
$url_snom_menu = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'.GS_PROV_PORT : '') . GS_PROV_PATH .'snom/menu.php';



#################################### INITIAL SCREEN {
if (! $type) {
	
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['m'] )));
	$user = trim( @$_REQUEST['u'] );
	
		
	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>', "\n",
	     '<SnomIPPhoneMenu>', "\n",
	       '<Title>Konfigurationsmenue</Title>', "\n\n";
	
	echo '<MenuItem>', "\n",
		'<Name>', snomXmlEsc('Rufumleitung'), '</Name>', "\n",
		'<URL>',$url_snom_menu,'?m=',$mac, '&u=',$user, '&t=forward</URL>', "\n",
		'</MenuItem>', "\n\n";
	echo '<MenuItem>', "\n",
		'<Name>', snomXmlEsc('Dienstmerkmale'), '</Name>', "\n",
		'<URL>',$url_snom_provdir,'features.php?m=',$mac, '&u=',$user,'</URL>', "\n",
		'</MenuItem>', "\n\n";
	echo '<MenuItem>', "\n",
		'<Name>', snomXmlEsc('Klingeltoene'), '</Name>', "\n",
		'<URL>',$url_snom_provdir,'rt.php?m=',$mac, '&u=',$user,'</URL>', "\n",
		'</MenuItem>', "\n\n";
		# in XML the & must normally be encoded as &amp; but not for
		# the stupid Snom!
	
	defineBackKey();
	echo '</SnomIPPhoneMenu>', "\n";
	_ob_send();
	
}
#################################### INITIAL SCREEN }




function defineBackKey()
{
	global $user, $type, $mac, $url_snom_menu;
	
	$args = array();
		$args[] = 'm='. $mac;
		$args[] = 'u='. $user;
	
	echo '<SoftKeyItem>',
	       '<Name>#</Name>',
	       '<URL>', $url_snom_menu, '?', implode('&', $args), '</URL>',
	     '</SoftKeyItem>', "\n";
}

#################################### FORWARD SCREEN {
if ( $type == 'forward') {
	
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim( @$_REQUEST['m'] )));
	$user = trim( @$_REQUEST['u'] );
	
		
	
	ob_start();
	echo '<?','xml version="1.0" encoding="utf-8"?','>', "\n",
	     '<SnomIPPhoneMenu>', "\n",
	       '<Title>Rufumleitung</Title>', "\n\n";
	
	echo '<MenuItem>', "\n",
		'<Name>', snomXmlEsc('Rufumleitung'), '</Name>', "\n",
		'<URL>',$url_snom_provdir,'callforward.php?m=',$mac, '&u=',$user, '</URL>', "\n",
		'</MenuItem>', "\n\n";
	echo '<MenuItem>', "\n",
		'<Name>', snomXmlEsc('externe Nummern'), '</Name>', "\n",
		'<URL>',$url_snom_provdir,'extnumbers.php?m=',$mac, '&u=',$user,'</URL>', "\n",
		'</MenuItem>', "\n\n";

	
	defineBackKey();
	echo '</SnomIPPhoneMenu>', "\n";
	_ob_send();
	
}
#################################### FORWARD SCREEN }




?>
