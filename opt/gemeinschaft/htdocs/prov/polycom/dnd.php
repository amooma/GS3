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

define("GS_VALID", true);		// this is a parent file

require_once(dirname(__FILE__) ."/../../../inc/conf.php");
require_once(GS_DIR ."inc/db_connect.php");
include_once(GS_DIR ."inc/gs-lib.php");
include_once( GS_DIR .'inc/gettext.php' );
include_once( GS_DIR .'inc/string.php' );
require_once(GS_DIR ."inc/langhelper.php");

Header("Content-Type: text/html; charset=utf-8");
Header("Expires: 0");
Header("Pragma: no-cache");
Header("Cache-Control: private, no-cache, must-revalidate");
Header("Vary: *");

$mainpage_doctype = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";

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

function _err($msg="")
{
	@ob_end_clean();
	ob_start();

	echo "<html>\n";
	echo "<head><title>". __("Fehler") ."</title></head>\n";
	echo "<body><b>". __("Fehler") ."</b>: ". htmlEnt($msg) ."</body>\n";
	echo "</html>\n";

	_ob_send();
}

function getUserID($ext)
{
	global $db;

	if(!preg_match("/^\d+$/", $ext)) return -1;

	$user_id = (int) $db->executeGetOne("SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`='". $db->escape($ext) ."'");
        if($user_id < 1) return -1;

        return $user_id;
}

//---------------------------------------------------------------------------

if ( !gs_get_conf('GS_POLYCOM_PROV_ENABLED') )
{
	gs_log(GS_LOG_DEBUG, 'Polycom provisioning not enabled');
	_err('Not enabled.');
}

$db = gs_db_slave_connect();

$mac = preg_replace("/[^\dA-Z]/", "", strtoupper(trim(@$_REQUEST["m"])));
$user = trim(@$_REQUEST['u']);
$user_id = getUserID($user);

// setup i18n stuff
gs_setlang(gs_get_lang_user($db, $user, GS_LANG_FORMAT_GS));
gs_loadtextdomain( 'gemeinschaft-gui' );
gs_settextdomain( 'gemeinschaft-gui' );

$user_id_check = $db->executeGetOne("SELECT `user_id` FROM `phones` WHERE `mac_addr`='". $db->escape($mac) ."'");
if($user_id != $user_id_check) _err("Not authorized");

$remote_addr = @$_SERVER["REMOTE_ADDR"];
$remote_addr_check = $db->executeGetOne("SELECT `current_ip` FROM `users` WHERE `id`='". $user_id ."'");
if($remote_addr != $remote_addr_check) _err("Not authorized");

ob_start();

$url_polycom_dnd = GS_PROV_SCHEME ."://". GS_PROV_HOST . (GS_PROV_PORT ? ":". GS_PROV_PORT : "") . GS_PROV_PATH ."polycom/dnd.php";

$newdndstate = trim(@$_REQUEST['setdnd']);

if ( ($newdndstate == 'on') || ($newdndstate == 'off') ) {
	$masterdb = gs_db_master_connect();
	if (!$masterdb) _err('Could not connect to database.');

	$check = $db->execute("INSERT INTO `dnd`
		(`_user_id`, `active`) VALUES
		(" . $user_id . ", \"" . $newdndstate . "\") 
		ON DUPLICATE KEY UPDATE `active` = \"" . $newdndstate . "\"");
	if (!$check) _err('Failed to set new DND state.');
}

#################################### MAIN MENU {

$current_dndstate = $db->executeGetOne("SELECT `active` FROM `dnd` WHERE `_user_id`=". $user_id);

echo "<html>\n";
echo "<head><title>". htmlEnt(__("Ruhe/DND")) ."</title></head>\n";
echo "<body><br />\n";

echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100%\">\n";

echo "<tr>";
echo "<th width=\"100%\" align=\"center\">". htmlEnt(__("Ruhe/DND-Status setzen")) .":</th></tr>\n";

echo "<tr><td width=\"100%\" align=\"center\"><a href=\"". $url_polycom_dnd ."?m=". $mac ."&amp;u=". $user ."&amp;setdnd=on\">". (($current_dndstate == "on") ? "*" : "") . htmlEnt(__("Ein")) ."</a></td></tr>\n";
echo "<tr><td width=\"100%\" align=\"center\"><a href=\"". $url_polycom_dnd ."?m=". $mac ."&amp;u=". $user ."&amp;setdnd=off\">". (($current_dndstate == "off") ? "*" : "") . htmlEnt(__("Aus")) ."</a></td></tr>\n";

echo "</table>\n";

echo "</body>\n";

echo "</html>\n";

#################################### MAIN MENU }

_ob_send();

?>