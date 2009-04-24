<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 6053 $
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

define("GS_VALID", true);		// this is a parent file
require_once(dirname(__FILE__) ."/../../../inc/conf.php");
include_once(GS_DIR ."inc/db_connect.php");
include_once(GS_DIR ."inc/gettext.php");
require_once(GS_DIR ."inc/gs-fns/gs_user_watchedmissed.php");
require_once(GS_DIR ."inc/gs-fns/gs_astphonebuttons.php");

Header("Content-Type: text/html; charset=utf-8");
Header("Expires: 0");
Header("Pragma: no-cache");
Header("Cache-Control: private, no-cache, must-revalidate");
Header("Vary: *");

$diallog_doctype = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";

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
	echo "<body><b>". __("Fehler") ."</b>: ". $msg ."</body>\n";
	echo "</html>\n";

	_ob_send();
}

//---------------------------------------------------------------------------

if(!gs_get_conf("GS_POLYCOM_PROV_ENABLED"))
{
	gs_log(GS_LOG_DEBUG, "Polycom provisioning not enabled");
	_err("Not enabled.");
}

$user = trim(@$_REQUEST["user"]);

if(!preg_match("/^\d+$/", $user)) _err("Not a valid SIP user.");

$type = trim(@$_REQUEST["type"]);
if(!in_array($type, array("in", "out", "missed"), true)) $type = false;

if(isset($_REQUEST["delete"])) $delete = (int) $_REQUEST["delete"];

$db = gs_db_slave_connect();

//--- get user_id
$user_id = (int) $db->executeGetOne("SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`='". $db->escape($user) ."'");
if($user_id < 1) _err("Unknown user.");

$typeToTitle = array(
	"out"    => __("Gew\xC3\xA4hlt"),
	"missed" => __("Verpasst"),
	"in"     => __("Angenommen")
);

ob_start();

$url_polycom_dl = GS_PROV_SCHEME ."://". GS_PROV_HOST . (GS_PROV_PORT ? ":". GS_PROV_PORT : "") . GS_PROV_PATH ."polycom/diallog.php";

if((isset($delete)) && $type)
{
//--- clear list (
	$db->execute(
		"DELETE FROM `dial_log` ".
		"WHERE ".
		"  `user_id`=". $user_id ." AND ".
		"  `type`='" . $type . "'"
	);

//--- ) clear list
}

#################################### INITIAL SCREEN {
if(!$type)
{
	//--- delete outdated entries
	$db->execute("DELETE FROM `dial_log` WHERE `user_id`=". $user_id ." AND `timestamp`<". (time()-(int)GS_PROV_DIAL_LOG_LIFE));

	echo $diallog_doctype ."\n";
	echo "<html>\n";
	echo "<head><title>". __("Anruflisten") ."</title></head>\n";
	echo "<body><br />\n";

	foreach($typeToTitle as $t => $title)
	{
		$num_calls = (int) $db->executeGetOne("SELECT COUNT(*) FROM `dial_log` WHERE `user_id`=". $user_id ." AND `type`='". $t ."'");

		echo "- <a href=\"". $url_polycom_dl ."?user=". $user ."&amp;type=". $t ."\">". $title ."</a><br />\n";
	}

	echo "</body>\n";

	echo "<softkey index=\"1\" label=\"Beenden\" action=\"Softkey:Exit\" />\n";
	echo "<softkey index=\"2\" label=\"\" action=\"\" />\n";
	echo "<softkey index=\"3\" label=\"\" action=\"\" />\n";
	echo "<softkey index=\"4\" label=\"\" action=\"\" />\n";
	echo "</html>\n";
}

#################################### INITIAL SCREEN }



#################################### DIAL LOG {
else
{
	echo $diallog_doctype ."\n";

	echo "<html>\n";
	echo "<head><title>". __("Anruflisten") ." - ". $typeToTitle[$type] ."</title></head>\n";
	echo "<body><br />\n";

	$query =
		"SELECT ".
		"  MAX(`timestamp`) `ts`, ".
		"  `number`, ".
		"  `remote_name`, ".
		"  `remote_user_id`, ".
		"  `queue_id`, ".
		"  COUNT(*) `num_calls` ".
		"FROM `dial_log` ".
		"WHERE ".
		"  `user_id`=". $user_id ." AND ".
		"  `type`='". $type ."'" .
		"GROUP BY `number`, `queue_id` ".
		"ORDER BY `ts` DESC ".
		"LIMIT 20";

	$rs = $db->execute($query);

	if($rs->numRows() == 0)
	{
		echo "<br />Keine Eintr\xC3\xA4ge vom Typ '<b>". $typeToTitle[$type] ."</b>'<br />\n";
	}
	else
	{
		echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100%\">\n";

		echo "<tr>";

		echo "<th width=\"30%\">Datum</th>";
		echo "<th width=\"70%\">Nummer</th></tr>\n";

//		echo "<tbody>";

		while($r = $rs->fetchRow())
		{
			unset($num_calls);

			if($r["num_calls"] > 0)
			{
				$num_calls = (int) $db->executeGetOne(
					"SELECT ".
					"  COUNT(*) ".
					"FROM `dial_log` ".
					"WHERE ".
					"  `user_id`=". $user_id ." AND ".
					"  `number`='". $r["number"] ."' AND ".
					"  `type`='". $type ."' AND ".
					"  `queue_id`". (($r["queue_id"] > 0) ? "=". $r["queue_id"] : " IS NULL") ." AND ".
					"  `read` < 1");
			}

			$entry_name = "";
			if($r["queue_id"] > 0) $entry_name = "WS: ";
			$entry_name .= $r["number"];

			if($r["remote_name"] != "")
			{
				$entry_name .= " ". $r["remote_name"];
			}

			if(date("dm") == date("dm", (int) $r["ts"]))
				$when = date("H:i", (int) $r["ts"]);
			else
				$when = date("d.m.", (int) $r["ts"]);

			echo "<tr>";

			echo "<td width=\"30%\">". $when ."</td>";
			echo "<td width=\"70%\"><a href=\"tel://". $r["number"]."\">". $entry_name;

			if($num_calls > 0) echo " (". $num_calls .")";

			echo "</a></td></tr>\n";
		}

//		echo "</tbody></table>\n";
		echo "</table>\n";
	}

	echo "</body>\n";

//	echo "<softkey index=\"1\" label=\"L\xC3\xB6schen\" action=\"Softkey:Fetch;". $url_polycom_dl ."?user=". $user ."&amp;type=". $type ."&amp;delete=1\" />\n";
	echo "<softkey index=\"1\" label=\"Leeren\" action=\"Softkey:Fetch;". $url_polycom_dl ."?user=". $user ."&amp;type=". $type ."&amp;delete=1\" />\n";
	echo "<softkey index=\"2\" label=\"Beenden\" action=\"Softkey:Exit\" />\n";
	echo "<softkey index=\"3\" label=\"\" action=\"\" />\n";
	echo "<softkey index=\"4\" label=\"\" action=\"\" />\n";
	echo "</html>\n";
	
	if($type == "missed")
	{
	 	gs_user_watchedmissed($user_id);
	}

	if(GS_BUTTONDAEMON_USE == true)
	{
		gs_buttondeamon_missedcalls($user);
	}
}

#################################### DIAL LOG }

_ob_send();

?>
