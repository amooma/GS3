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
include_once( GS_DIR .'inc/string.php' );
require_once(GS_DIR ."inc/gs-fns/gs_user_watchedmissed.php");
require_once(GS_DIR ."inc/gs-fns/gs_astphonebuttons.php");

Header("Content-Type: application/xhtml+xml; charset=utf-8");
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
		Header("Content-Type: application/xhtml+xml; charset=utf-8");
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

if(!gs_get_conf("GS_POLYCOM_PROV_ENABLED"))
{
	gs_log(GS_LOG_DEBUG, "Polycom provisioning not enabled");
	_err("Not enabled.");
}

$mac = preg_replace("/[^\dA-Z]/", "", strtoupper(trim(@$_REQUEST["mac"])));
$user = trim(@$_REQUEST["user"]);

if(!preg_match("/^\d+$/", $user)) _err("Not a valid SIP user.");

$db = gs_db_slave_connect();

//--- get user_id
$user_id = (int) $db->executeGetOne("SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`='". $db->escape($user) ."'");
if($user_id < 1) _err("Unknown user.");

//--- get user info
$query_name =
	"SELECT ".
	"  `lastname`, ".
	"  `firstname` ".
	"FROM `users` ".
	"WHERE ".
	"  `id`=". $user_id;

$res_username = $db->execute($query_name);
$user_name = "";

if($res_username->numRows() != 1)
{
	$user_name = "Unbekannt";
}
else
{
	$r_name = $res_username->fetchRow();
	$user_name = (strlen($r_name["firstname"]) > 0 ? (substr($r_name["firstname"], 0, 1) .". ") : "") . $r_name["lastname"];

	unset($r_name);
}

unset($rs_name); unset($query_name);

$displaydatafilename = @realpath(@gs_get_conf("GS_BUTTONDAEMON_DISPLAYDIR") . $user .".display");

$displaydata = "";

if(($displaydatafilename) && (file_exists($displaydatafilename)))
{
	$displaydatafile = @fopen($displaydatafilename, "r");
	if($displaydatafile)
	{
		while(!@feof($displaydatafile))
		{
			$displaydata .= @fread($displaydatafile, 8192);
		}
	}

	@fclose($displaydatafile); unset($displaydatafile);

	$displaydata = strtr(trim($displaydata), Array("\r" => "", "\n" => "<br />"));
}

$url_polycom_base = GS_PROV_SCHEME ."://". GS_PROV_HOST . (GS_PROV_PORT ? ":". GS_PROV_PORT : "") . GS_PROV_PATH ."polycom/";

ob_start();

#################################### IDLE SCREEN {

echo $mainpage_doctype ."\n";

echo "<html>\n";
echo "<head><title>". $user ." ". $user_name ."</title></head>\n";
echo "<body><hr />\n";

if(strlen($displaydata) > 0)
{
	echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100%\">\n";
	echo "<tr><td width=\"100%\" align=\"center\">";

	echo $displaydata;

	echo "</td></tr>\n";
	echo "</table>\n";
}

echo "</body>\n";

echo "</html>\n";

#################################### IDLE SCREEN }

_ob_send();

?>