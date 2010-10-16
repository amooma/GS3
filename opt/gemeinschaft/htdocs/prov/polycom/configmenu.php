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
require_once( GS_DIR .'inc/db_connect.php' );
include_once( GS_DIR .'inc/gettext.php' );
require_once(GS_DIR ."inc/langhelper.php");
include_once( GS_DIR .'inc/string.php' );

header( 'Content-Type: text/html; charset=utf-8' );
header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-Control: private, no-cache, must-revalidate' );
header( 'Vary: *' );

$phonemenu_doctype = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";

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
        echo '<head><title>'. htmlEnc(__('Fehler')) .'</title></head>',"\n";
        echo '<body><b>'. htmlEnc(__('Fehler')) .'</b>: '. $msg .'</body>',"\n";
        echo '</html>',"\n";

        _ob_send();
}

function getUserID($ext)
{
	global $db;
	
	if (!preg_match('/^\d+$/', $ext)) _err('Invalid username');
	
	$user_id = (int)$db->executeGetOne( 'SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`=\''. $db->escape($ext) .'\'' );
	if ($user_id < 1) _err('Unknown user');
	return $user_id;
}

//---------------------------------------------------------------------------

if (!gs_get_conf('GS_POLYCOM_PROV_ENABLED'))
{
        gs_log(GS_LOG_DEBUG, 'Polycom provisioning not enabled');
        _err('Not enabled.');
}

$type = trim(@$_REQUEST['t']);
if (!in_array($type, array('forward'), true)) {
	$type = false;
}

$user = trim(@$_REQUEST['u']);
if(!preg_match('/^\d+$/', $user)) _err('Not a valid SIP user.');

$db = gs_db_slave_connect();

// setup i18n stuff
gs_setlang(gs_get_lang_user($db, $user, GS_LANG_FORMAT_GS));
gs_loadtextdomain( 'gemeinschaft-gui' );
gs_settextdomain( 'gemeinschaft-gui' );

$url_polycom_provdir = GS_PROV_SCHEME .'://'. GS_PROV_HOST . (GS_PROV_PORT ? ':'. GS_PROV_PORT : '') . GS_PROV_PATH .'polycom/';
$url_polycom_menu = $url_polycom_provdir .'configmenu.php';

#################################### INITIAL SCREEN {

if(!$type) {
	$mac = preg_replace('/[^\dA-Z]/', '', strtoupper(trim(@$_REQUEST['m'])));
	$user = trim(@$_REQUEST['u']);

	ob_start();

	echo $phonemenu_doctype ."\n";
	echo '<html>',"\n";
	echo "<head><title>". htmlEnt(__("Konfigurationsmen\xC3\xBC")) ."</title></head>\n";
	echo '<body><br />',"\n";

	echo '- <a href="'. $url_polycom_menu .'?m='. $mac .'&amp;u='. $user .'&amp;t=forward">'. htmlEnt(__("Rufumleitung")) .'</a><br />',"\n";
	echo '- <a href="'. $url_polycom_provdir .'features.php?m='. $mac .'&amp;u='. $user .'&amp;t=forward">'. htmlEnt(__("Dienstmerkmale")) .'</a><br />',"\n";
//	echo '- <a href="'. $url_polycom_provdir .'rt.php?m='. $mac .'&amp;u='. $user .'&amp;t=forward">'. __("Klingelt\xC3\xB6ne") .'</a><br />',"\n";
	echo '- <a href="Key:Setup">'. htmlEnt(__("Lokale Telefoneinstellungen")) .'</a><br />',"\n";

	echo '</body>',"\n";

	echo "</html>\n";

	_ob_send();
}

#################################### INITIAL SCREEN }


#################################### FORWARD SCREEN {

if ($type == 'forward') {
	$mac = preg_replace('/[^\dA-Z]/', '', strtoupper(trim(@$_REQUEST['m'])));
	$user = trim(@$_REQUEST['u']);
	
	ob_start();

	echo $phonemenu_doctype ."\n";
	echo '<html>',"\n";
	echo '<head><title>'. htmlEnt(__("Rufumleitung")) .'</title></head>',"\n";
	echo '<body><br />',"\n";


	echo '- <a href="'. $url_polycom_provdir .'callforward.php?m='. $mac .'&amp;u='. $user .'">'. htmlEnt(__("Rufumleitung")) .'</a><br />',"\n";
	echo '- <a href="'. $url_polycom_provdir .'extnumbers.php?m='. $mac .'&amp;u='. $user .'">'. htmlEnt(__("Externe Nummern")) .'</a><br />',"\n";

	echo '</body>',"\n";

	echo '</html>',"\n";

	_ob_send();
}

#################################### FORWARD SCREEN }

?>