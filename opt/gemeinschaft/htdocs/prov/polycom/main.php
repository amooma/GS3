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
include_once(GS_DIR ."inc/db_connect.php");
include_once(GS_DIR ."inc/gettext.php");
include_once(GS_DIR ."inc/langhelper.php");
include_once( GS_DIR .'inc/string.php' );
//require_once(GS_DIR ."inc/gs-fns/gs_user_watchedmissed.php");

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

//---------------------------------------------------------------------------

if (!gs_get_conf('GS_POLYCOM_PROV_ENABLED'))
{
	gs_log(GS_LOG_DEBUG, 'Polycom provisioning not enabled');
	_err('Not enabled.');
}

$mac = preg_replace("/[^\dA-Z]/", '', strtoupper(trim(@$_REQUEST['mac'])));
$user = trim(@$_REQUEST['user']);

if (!preg_match("/^\d+$/", $user)) _err('Not a valid SIP user.');

$db = gs_db_slave_connect();

// setup i18n stuff
gs_setlang(gs_get_lang_user($db, $user, GS_LANG_FORMAT_GS));
gs_loadtextdomain( 'gemeinschaft-gui' );
gs_settextdomain( 'gemeinschaft-gui' );

//--- get user_id
$user_id = (int) $db->executeGetOne("SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`='". $db->escape($user) ."'");
if ($user_id < 1) _err('Unknown user.');

$menuitems = Array(
	Array(	'file'	=> 'diallog.php?user='. $user .'&mac='. $mac,
		'title'	=> __("Ruflisten")),
	Array(	'file'	=> 'pb.php?u='. $user .'&amp;m='. $mac,
		'title'	=> __("Telefonbuch")),
	Array(	'file'	=> 'configmenu.php?u='. $user .'&amp;m='. $mac,
		'title'	=> __("Konfiguration"))
);

ob_start();

$url_polycom_base = GS_PROV_SCHEME ."://". GS_PROV_HOST . (GS_PROV_PORT ? ":". GS_PROV_PORT : "") . GS_PROV_PATH ."polycom/";

#################################### MAIN MENU {

echo "<html>\n";
echo "<head><title>". htmlEnt(__("Telefonmen\xC3\xBC")) ."</title></head>\n";
echo "<body><br />\n";

foreach($menuitems as $thismenuitem) {
	echo "- <a href=\"". $url_polycom_base . $thismenuitem["file"] ."\">". htmlEnt($thismenuitem["title"]) ."</a><br />\n";
}

echo "</body>\n";

echo "</html>\n";

#################################### MAIN MENU }

_ob_send();

?>
