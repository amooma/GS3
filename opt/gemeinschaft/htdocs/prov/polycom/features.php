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
include_once(GS_DIR ."inc/gettext.php");
require_once(GS_DIR ."inc/langhelper.php");
include_once(GS_DIR ."inc/group-fns.php");

include_once(GS_DIR ."inc/gs-lib.php");
include_once(GS_DIR ."inc/gs-fns/gs_clir_activate.php");
include_once(GS_DIR ."inc/gs-fns/gs_clir_get.php");
include_once(GS_DIR ."inc/gs-fns/gs_callwaiting_activate.php");
include_once(GS_DIR ."inc/gs-fns/gs_callwaiting_get.php");

header("Content-Type: text/html; charset=utf-8");
header("Expires: 0");
header("Pragma: no-cache");
header("Cache-Control: private, no-cache, must-revalidate");
header("Vary: *");

$features_doctype = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";

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

	if(!preg_match("/^\d+$/", $ext)) _err("Invalid username");

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

$type = trim(@$_REQUEST["t"]);
if(!in_array($type, array("internal", "external", "callwaiting", "cidext", "cidint"), true))
{
	$type = false;
}

$db = gs_db_slave_connect();

$user = trim(@$_REQUEST["u"]);
$user_id = getUserID($user);

// setup i18n stuff
gs_setlang(gs_get_lang_user($db, $user, GS_LANG_FORMAT_GS));
gs_loadtextdomain("gemeinschaft-gui");
gs_settextdomain("gemeinschaft-gui");

// group permissions
$user_groups  = gs_group_members_groups_get(Array($user_id), "user");
$members_clip = gs_group_permissions_get($user_groups, "clip_set");

if(count($members_clip) <= 0)
	$show_clip = false;
else
	$show_clip = true;

$members_clir = gs_group_permissions_get($user_groups, "clir_set");

if(count($members_clir) <= 0)
	$show_clir = false;
else
	$show_clir = true;

$members_callwaiting = gs_group_permissions_get($user_groups, "callwaiting_set");

if(count($members_callwaiting) <= 0)
	$show_cw = false;
else
	$show_cw = true;

$tmp = Array();

if($show_clir)
{
	$tmp[15] = Array("k" => "internal", "v" => gs_get_conf("GS_CLIR_INTERNAL", __("CLIR Intern")));
	$tmp[25] = Array("k" => "external", "v" => gs_get_conf("GS_CLIR_EXTERNAL", __("CLIR Extern")));
}

if($show_cw)
{
	$tmp[35] = Array("k" => "callwaiting", "v" => gs_get_conf("GS_CALLWAITING", __("Anklopfen")));
}

if($show_clip)
{
	$tmp[45] = Array("k" => "cidext", "v" => gs_get_conf("GS_CALLERID", __("CID extern")));
	$tmp[55] = Array("k" => "cidint", "v" => gs_get_conf("GS_CALLERID", __("CID intern")));
}

ksort($tmp);
foreach($tmp as $arr)
{
	$typeToTitle[$arr["k"]] = $arr["v"];
}

$url_polycom_provdir = GS_PROV_SCHEME ."://". GS_PROV_HOST . (GS_PROV_PORT ? ":". GS_PROV_PORT : "") . GS_PROV_PATH ."polycom/";

$url_polycom_features = $url_polycom_provdir ."features.php";
$url_polycom_menu = $url_polycom_provdir ."configmenu.php";

//////////////////// SET FEATURE {

if(($type != false) && (isset($_REQUEST["state"])))
{
	$state = trim(@$_REQUEST['state']);
	$user_name = $db->executeGetOne("SELECT `user` FROM `users` WHERE `id`='". $db->escape($user_id) ."'");

	if($type == "callwaiting" && $show_cw)
	{
		$oldresult = (int)$db->executeGetOne("SELECT `active` FROM `callwaiting` WHERE `user_id`=". $user_id);
		if(($state == "yes") || ($state == "no"))
		{
			if(($oldresult == 1) && ($state == "no"))
			{
				gs_callwaiting_activate($user_name, 0);
			}
			else if(($oldresult == 0) && ($state == "yes"))
			{
				gs_callwaiting_activate($user_name, 1);
			}
		}
	}
	else if($show_clir && (($type == "internal") || ($type == "external")))
	{
		if(($state == "no") || ($state == "yes"))
		{
			gs_clir_activate($user_name, $type, $state);
		}
	}
	else if($type == "cidint" && $show_clip)
	{
		gs_user_callerid_set($user_name, $state, "internal");
	}
	else if($type == "cidext" && $show_clip)
	{
		gs_user_callerid_set($user_name, $state, "external");
	}
	else
	{
		_err("Forbidden");
	}

	$type = false;
}

//////////////////// SET FEATURE }

//////////////////// SELECT FEATURETYPE {

if((($type == "internal") || ($type == "external") || ($type == "callwaiting")) && ($type != false))
{
	$mac = preg_replace("/[^\dA-Z]/", "", strtoupper(trim(@$_REQUEST["m"])));

	ob_start();

	echo $features_doctype ."\n";

	$user_id_check = $db->executeGetOne("SELECT `user_id` FROM `phones` WHERE `mac_addr`='". $db->escape($mac) ."'");
	if($user_id != $user_id_check)
		_err("Not authorized");

	$remote_addr = @$_SERVER["REMOTE_ADDR"];
	$remote_addr_check = $db->executeGetOne("SELECT `current_ip` FROM `users` WHERE `id`='". $user_id ."'");
	if($remote_addr != $remote_addr_check)
		_err("Not authorized");

	$state = "aus";
	if($type == "callwaiting")
	{
		if(!$show_cw)
		{
			_err("Forbidden");
		}

		$result = (int)$db->executeGetOne("SELECT `active` FROM `callwaiting` WHERE `user_id`=". $user_id);
		if ($result == 1) $state = "ein";
		else $state = "aus";
	}
	else
	{
		if(!$show_clir)
		{
			_err("Forbidden");
		}

		$result = $db->executeGetOne("SELECT `". $type ."_restrict` FROM `clir` WHERE `user_id`=". $user_id);
		if ($result == "yes") $state = "ein";
                else $state = "aus";
	}

	echo "<html>\n";
	echo "<head><title>". __("Dienstmerkmale") ." - ". $typeToTitle[$type] ."</title></head>\n";
	echo "<body><br />\n";

	echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100%\">\n";

	echo "<tr>";
	echo "<th width=\"100%\" align=\"center\">". __("Dienstmerkmale") .": ". $typeToTitle[$type] ."</th></tr>\n";

	echo "<tr><td width=\"100%\" align=\"center\"><a href=\"". $url_polycom_features ."?m=". $mac ."&amp;u=". $user ."&amp;t=". $type ."&amp;state=no\">". (($state == "aus") ? "*" : "") . __("Aus") ."</a></td></tr>\n";
	echo "<tr><td width=\"100%\" align=\"center\"><a href=\"". $url_polycom_features ."?m=". $mac ."&amp;u=". $user ."&amp;t=". $type ."&amp;state=yes\">". (($state == "ein") ? "*" : "") . __("Ein") ."</a></td></tr>\n";

	echo "</table>\n";
	echo "</body>\n";
	echo "</html>\n";

	_ob_send();
}

//////////////////// SELECT FEATURETYPE }

//////////////////// SELECT CID {

if(($type == "cidint") || ($type == "cidext"))
{
	if(!$show_clip)
	{
		_err("Forbidden");
	}

	if($type == "cidext") $target = "external";
	else $target = "internal";

	$mac = preg_replace("/[^\dA-Z]/", "", strtoupper(trim(@$_REQUEST["m"])));
	$user_name = $db->executeGetOne("SELECT `user` FROM `users` WHERE `id`='". $db->escape($user_id) ."'");

	$enumbers = gs_user_callerids_get($user_name);
	if(isGsError($enumbers))
	{
		_err("Fehler beim Abfragen.");
	}

	$selected = true;
	foreach($enumbers as $number)
	{
		if($number["selected"] === 1)
		{
			$selected = false;
		}
	}

	ob_start();

	echo "<html>\n";
	echo "<head><title>". __("Dienstmerkmale") ." - ". $typeToTitle[$type] ."</title></head>\n";
	echo "<body><br />\n";

	echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100%\">\n";

	echo "<tr>";
	echo "<th width=\"100%\" align=\"center\">". __("Dienstmerkmale") .": ". $typeToTitle[$type] ."</th></tr>\n";

	echo "<tr><td width=\"100%\" align=\"center\"><a href=\"". $url_polycom_features ."?m=". $mac ."&amp;u=". $user ."&amp;t=". $type ."&amp;state=\">". (($selected == true) ? "*" : "") . $user ."</a></td></tr>\n";

	foreach($enumbers as $extnumber)
	{
		if($extnumber["dest"] != $target) continue;

		echo "<tr><td width=\"100%\" align=\"center\"><a href=\"". $url_polycom_features ."?m=". $mac ."&amp;u=". $user ."&amp;t=". $type ."&amp;state=". $extnumber["number"] ."\">". (($extnumber["selected"] == 1) ? "*" : "") . $extnumber["number"] ."</a></td></tr>\n";
	}

	echo "</table>\n";
	echo "</body>\n";
	echo "</html>\n";
}

//////////////////// SELECT CID }

//////////////////// INITIAL SCREEN {

if(!$type)
{
	$mac = preg_replace("/[^\dA-Z]/", "", strtoupper(trim(@$_REQUEST["m"])));

	ob_start();

	echo $features_doctype ."\n";

	echo "<html>\n";
	echo "<head><title>". __("Einstellungen") ." - ". __("Dienstmerkmale") ."</title></head>\n";
	echo "<body><br />\n";

	echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100%\">\n";

	echo "<tr>";
	echo "<th width=\"100%\" align=\"center\" colspan=\"2\">". __("Dienstmerkmale") ."</th></tr>\n";

	foreach($typeToTitle as $t => $title)
	{
		$state = __("aus");

		if($t == "callwaiting")
		{
			$result = (int)$db->executeGetOne("SELECT `active` FROM `callwaiting` WHERE `user_id`=". $user_id);
			if($result == 1)
				$state = __("ein");
			else
				$state = __("aus");
		}
		else if($t == "cidext")
		{
			unset($result);
			$result = $db->executeGetOne("SELECT `number` FROM `users_callerids` WHERE `selected`=1 AND `dest`='external' AND `user_id`=". $user_id);
			if($result)
				$state = $result;
			else
				$state = $user;
		}
		else if($t == "cidint")
		{
			unset($result);
			$result = $db->executeGetOne("SELECT `number` FROM `users_callerids` WHERE `selected`=1 AND `dest`='internal' AND `user_id`=". $user_id);
			if($result)
				$state = $result;
			else
				$state = $user;
		}
		else
		{
			$result = $db->executeGetOne("SELECT `". $t ."_restrict` FROM `clir` WHERE `user_id`=". $user_id);
			if ($result == 'yes')
				$state = __("ein");
	                else
	                	$state = __("aus");
		}

		echo "<tr>";

		echo "<td width=\"50%\" align=\"right\"><a href=\"". $url_polycom_features ."?m=". $mac ."&amp;u=". $user ."&amp;t=". $t ."\">". $title .":</a></td>";
		echo "<td width=\"50%\" align=\"left\">". $state ."</td>";

		echo "</tr>";
	}

	echo "</table>\n";
	echo "</body>\n";
	echo "</html>\n";

	_ob_send();
}

//////////////////// INITIAL SCREEN }

?>
