<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 3307 $
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
*
* APS for Polycom SoundPoint IP phones
* (c) 2009 Daniel Scheller / LocaNet oHG
* mailto:scheller@loca.net
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

define("GS_VALID", true); // this is a parent file

require_once( dirname(__FILE__) .'/../../../inc/conf.php' );
require_once(GS_DIR ."inc/db_connect.php");
include_once(GS_DIR ."inc/gs-lib.php");
include_once(GS_DIR ."inc/gs-fns/gs_user_external_numbers_get.php");
include_once(GS_DIR ."inc/gettext.php");
require_once(GS_DIR ."inc/langhelper.php");

header("Content-Type: text/html; charset=utf-8");
header("Expires: 0");
header("Pragma: no-cache");
header("Cache-Control: private, no-cache, must-revalidate");
header("Vary: *");

$extnumbers_doctype = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";

//---------------------------------------------------------------------------

function _ob_send()
{
	if(!headers_sent())
	{
		Header("Content-Type: text/html; charset=utf-8");
		Header("Content-Length: ". (int) @ob_get_length());
	}

	@ob_end_flush();
	die();
}

function _err($msg = "")
{
	@ob_end_clean();
	ob_start();

	echo "<html>\n";
	echo "<head><title>". __("Fehler") ."</title></head>\n";
	echo "<body><b>". __("Fehler") ."</b>: ". $msg ."</body>\n";
	echo "</html>\n";

	_ob_send();
}

function getUserID($ext)
{
	global $db;

	if (!preg_match("/^\d+$/", $ext)) _err('Invalid username');

	$user_id = (int) $db->executeGetOne("SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`='". $db->escape($ext) ."'");
	if($user_id < 1) _err("Unknown user");
	return $user_id;
}

//---------------------------------------------------------------------------

if(!gs_get_conf("GS_POLYCOM_PROV_ENABLED"))
{
        gs_log(GS_LOG_DEBUG, "Polycom provisioning not enabled");
        _err("Not enabled.");
}

$db = gs_db_slave_connect();

$user = trim(@$_REQUEST['u']);
$user_id = getUserID($user);

// setup i18n stuff
gs_setlang(gs_get_lang_user($db, $user, GS_LANG_FORMAT_GS));
gs_loadtextdomain( 'gemeinschaft-gui' );
gs_settextdomain( 'gemeinschaft-gui' );

$url_polycom_extnumbers = GS_PROV_SCHEME ."://". GS_PROV_HOST . (GS_PROV_PORT ? ":". GS_PROV_PORT : "") . GS_PROV_PATH ."polycom/extnumbers.php";
$url_polycom_menu = GS_PROV_SCHEME ."://". GS_PROV_HOST . (GS_PROV_PORT ? ":". GS_PROV_PORT : "") . GS_PROV_PATH ."polycom/configmenu.php";

#################################### INITIAL SCREEN {

$mac = preg_replace("/[^\dA-Z]/", "", strtoupper(trim(@$_REQUEST['m'])));

$user_name = $db->executeGetOne("SELECT `user` FROM `users` WHERE `id`='". $db->escape($user_id) ."'");

$enumbers = gs_user_external_numbers_get($user_name);

if(isGsError($enumbers))
{
	_err("Fehler beim Abfragen.");
 }

ob_start();
echo $phonemenu_doctype ."\n";

echo "<html>\n";
echo "<head><title>". __("Externe Rufnummern") ."</title></head>\n";
echo "<body><br />\n";

if (sizeof($enumbers) <= 0) {
	echo __("Keine externen Rufumleitungsziele hinterlegt") .".<br />\n";
} else {
	echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100%\">\n";

	echo "<tr><th width=\"100%\" align=\"left\">". __("M\xC3\xB6gliche externe Rufumleitungsziele") .":</th></tr>\n";

	foreach($enumbers as $extnumber)
	{
		echo "<tr><td width=\"100%\">". $extnumber ."</td></tr>\n";
	}

	echo "</table>\n";
}

echo "</body>\n";

echo "</html>\n";

_ob_send();

#################################### INITIAL SCREEN }

?>
