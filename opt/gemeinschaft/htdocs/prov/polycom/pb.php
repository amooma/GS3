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

define("GS_VALID", true); // this is a parent file
require_once(dirname(__FILE__) ."/../../../inc/conf.php");
include_once(GS_DIR ."inc/db_connect.php");
include_once(GS_DIR ."inc/gettext.php");
include_once(GS_DIR ."inc/langhelper.php");
include_once(GS_DIR ."inc/group-fns.php");
include_once( GS_DIR .'inc/string.php' );

header("Content-Type: text/html; charset=utf-8");
header("Expires: 0");
header("Pragma: no-cache");
header("Cache-Control: private, no-cache, must-revalidate");
header("Vary: *");

$phonebook_doctype = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">";

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
        echo "<body><b>". __("Fehler") ."</b>: ". htmlEnt($msg) ."</body>\n";
        echo "</html>\n";

        _ob_send();
}

function getUserID($ext)
{
	global $db;

	if(!preg_match("/^\d+$/", $ext)) _err("Invalid username");

	$user_id = (int) $db->executeGetOne("SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`='". $db->escape($ext) ."'");
	if ($user_id < 1) _err("Unknown user");
	return $user_id;
}

//---------------------------------------------------------------------------

if ( !gs_get_conf('GS_POLYCOM_PROV_ENABLED') )
{
        gs_log(GS_LOG_DEBUG, 'Polycom provisioning not enabled');
        _err('Not enabled.');
}

$type = trim(@$_REQUEST['t']);
if (! in_array($type, array('gs', 'prv', 'imported'), true) )
{
	$type = false;
}

$searchform = (int)trim(@$_REQUEST['searchform']);
$querystring = trim(@$_REQUEST['q']);

$db = gs_db_slave_connect();

$user = trim(@$_REQUEST['u']);
$user_id = getUserID($user);

// setup i18n stuff
gs_setlang(gs_get_lang_user($db, $user, GS_LANG_FORMAT_GS));
gs_loadtextdomain( 'gemeinschaft-gui' );
gs_settextdomain( 'gemeinschaft-gui' );

$tmp = array(
	15 => array(
		'k' => 'gs',
		'v' => gs_get_conf('GS_PB_INTERNAL_TITLE', __("Intern"))),
	25 => array(
		'k' => 'prv',
		'v' => gs_get_conf('GS_PB_PRIVATE_TITLE' , __("Pers\xC3\xB6nlich")))
);

if ( gs_get_conf('GS_PB_IMPORTED_ENABLED') )
{
	$pos = (int) gs_get_conf('GS_PB_IMPORTED_ORDER', 9) * 10;
	$tmp[$pos] = array(
		'k' => 'imported',
		'v' => gs_get_conf('GS_PB_IMPORTED_TITLE', __('Extern'))
	);
}

kSort($tmp);
foreach ($tmp as $arr)
{
	$typeToTitle[$arr['k']] = $arr['v'];
}

$url_polycom_pb = GS_PROV_SCHEME ."://". GS_PROV_HOST . (GS_PROV_PORT ? ":". GS_PROV_PORT : "") . GS_PROV_PATH ."polycom/pb.php";

#################################### INITIAL SCREEN {
if (!$type)
{
	$mac = preg_replace('/[^\dA-Z]/', '', strToUpper(trim(@$_REQUEST['m'])));

	$user_groups = gs_group_members_groups_get(array($user_id), "user");
	$permission_groups = gs_group_permissions_get($user_groups, "phonebook_user");
	$group_members = gs_group_members_get($permission_groups);

	ob_start();

        echo $phonebook_doctype ."\n";
        echo "<html>\n";
        echo "<head><title>". htmlEnt(__("Telefonbuch")) ."</title></head>\n";
        echo "<body><br />\n";

        foreach($typeToTitle as $t => $title)
        {
		$cq = 'SELECT COUNT(*) FROM ';
		switch ($t)
		{
			case 'gs' :
				$cq .= "`users` WHERE `id` IN (". implode(",", $group_members) .") AND `id` != ". $user_id;
				break;
			case 'imported' :
				$cq .= "`pb_ldap`";
				break;
			case 'prv' :
				$cq .= "`pb_prv` WHERE `user_id`=". $user_id;
				break;
			default :
				$cq  = false;
				break;
		}

		$c = $cq ? (" (". (int)@$db->executeGetOne($cq) .")") : "";

                echo "- <a href=\"". $url_polycom_pb ."?m=". $mac ."&amp;u=". $user ."&amp;t=". $t ."\">". htmlEnt($title) . $c ."</a><br />\n";
        }

        echo "</body>\n";

        echo "</html>\n";

	_ob_send();
}

#################################### INITIAL SCREEN }



#################################### SEARCH FORM {

if ($searchform === 1)
{
	$mac = preg_replace("/[^\dA-Z]/", "", strtoupper(trim(@$_REQUEST["m"])));

	ob_start();

	echo $phonebook_doctype ."\n";

	echo "<html>\n";
	echo "<head><title>". htmlEnt(__("Telefonbuch")) ." - ". htmlEnt($typeToTitle[$type]) ."</title></head>\n";
	echo "<body><br />\n";

	echo "<form name=\"search\" method=\"GET\" action=\"". $url_polycom_pb ."\">\n";
	echo "<input type=\"hidden\" name=\"u\" value=\"". $user ."\" />";
	echo "<input type=\"hidden\" name=\"m\" value=\"". $mac ."\" />";
	echo "<input type=\"hidden\" name=\"t\" value=\"". $type ."\" />\n";

	echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100%\">\n";
	echo "<tr>";
	echo "<th align=\"center\" width=\"100%\">". htmlEnt(sprintf(__("Telefonbuch %s durchsuchen")), " '". htmlEnt($typeToTitle[$type]) ."' ") .":</th>";
	echo "</tr>";

	echo "<tr><td align=\"center\" width=\"100%\"><input type=\"text\" name=\"q\" /></td></tr>\n";
	echo "<tr><td align=\"center\" width=\"100%\"><input type=\"submit\" value=\" ". __("Finden") ." \" /></td></tr>\n";
	echo "</table>\n";

	echo "</form>\n";

	echo "</body>\n";

	echo "</html>\n";

	_ob_send();
}

$num_results = (int) gs_get_conf("GS_POLYCOM_PROV_PB_NUM_RESULTS", 10);

#################################### IMPORTED PHONEBOOK {

if( $type === "imported" )
{
	// we don't need $user for this

	ob_start();

	echo $phonebook_doctype ."\n";

	$pagetitle = __("Telefonbuch") ." - ". $typeToTitle[$type];
	$searchsql = "1";
	$noresultsmsg = __("Dieses Telefonbuch enth\xC3\xA4lt keine Eintr\xC3\xA4ge.");

	if (strlen($querystring) > 0)
	{
		$pagetitle .= " ('". $querystring ."')";
		$searchsql = "`lastname` LIKE '%". $querystring ."%' OR `firstname` LIKE '%". $querystring ."%'";
		$noresultsmsg = sprintf(__("Keine Treffer f\xC3\xBCr \"%s\". Dr\xC3\xBCcken Sie 'Zur\xC3\xBCck', um eine neue Suche auszuf\xC3\xBChren."), $querystring);
	}

	echo "<html>\n";
	echo "<head><title>". htmlEnt($pagetitle) ."</title></head>\n";
	echo "<body><br />\n";

	$query =
		"SELECT `lastname` `ln`, `firstname` `fn`, `number` `ext` ".
		"FROM `pb_ldap` ".
		"WHERE ". $searchsql ." ".
		"ORDER BY `lastname`, `firstname` ".
		"LIMIT ". $num_results;

	$rs = $db->execute($query);
	if ($rs->numRows() !== 0)
	{
		echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100%\">\n";

		echo "<tr>";

		echo "<th width=\"50%\">". __("Name") ."</th>";
		echo "<th width=\"50%\">". __("Nummer") ."</th></tr>\n";

		while ( $r = $rs->fetchRow() )
		{
			$name = $r["ln"] .(strlen($r["fn"]) > 0 ? (", ". $r["fn"]) : "");
			$number = $r["ext"];

			echo "<tr>";

			echo "<td width=\"50%\">". htmlEnt($name) ."</td>";
			echo "<td width=\"50%\"><a href=\"tel://". $number."\">". $number ."</a></td></tr>\n";

		}

		echo "</table>\n";
	}
	else
	{
		echo "<br />". htmlEnt($noresultsmsg) ."<br />\n";
	}

	echo "</body>\n";

	echo "<softkey index=\"1\" label=\"". htmlEnt(__("Zur\xC3\xBCck")) ."\" action=\"Softkey:Back\" />\n";
	echo "<softkey index=\"2\" label=\"\" action=\"\" />\n";
	echo "<softkey index=\"3\" label=\"". html(__("Beenden")) ."\" action=\"Softkey:Exit\" />\n";
	echo "<softkey index=\"4\" label=\"\" action=\"\" />\n";
	echo "</html>\n";

	_ob_send();
}

#################################### IMPORTED PHONEBOOK }



#################################### INTERNAL PHONEBOOK {

if ($type === "gs")
{
	$mac = preg_replace("/[^\dA-Z]/", "", strToUpper(trim(@$_REQUEST["m"])));

	$user_groups = gs_group_members_groups_get(array($user_id), "user");
	$permission_groups = gs_group_permissions_get($user_groups, "phonebook_user");
	$group_members = gs_group_members_get($permission_groups);

	ob_start();

	echo $phonebook_doctype ."\n";

	$pagetitle = __("Telefonbuch") ." - ". $typeToTitle[$type];
	$searchsql = "1";
	$noresultsmsg = __("Dieses Telefonbuch enth\xC3\xA4lt keine Eintr\xC3\xA4ge.");

	if ( strlen($querystring) > 0 )
	{
		$pagetitle .= " ('". $querystring ."')";
		$searchsql = "`u`.`lastname` LIKE '%". $querystring ."%' OR `u`.`firstname` LIKE '%". $querystring ."%'";
		$noresultsmsg = sprintf(__("Keine Treffer f\xC3\xBCr \"%s\". Dr\xC3\xBCcken Sie 'Zur\xC3\xBCck', um eine neue Suche auszuf\xC3\xBChren."), $querystring);
	}

	echo "<html>\n";
	echo "<head><title>". htmlEnt($pagetitle) ."</title></head>\n";
	echo "<body><br />\n";


	$query =
		"SELECT `u`.`lastname` `ln`, `u`.`firstname` `fn`, `s`.`name` `ext` ".
		"FROM ".
		"  `users` `u` JOIN ".
		"  `ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) ".
		"WHERE ".
		"  `u`.`id` IN (". implode(",", $group_members) .") AND (".
		"  `u`.`id` != ". $user_id ." ) AND ".
		$searchsql ." ".
		"ORDER BY `u`.`lastname`, `u`.`firstname` ".
		"LIMIT ". $num_results;

	$rs = $db->execute($query);
	if($rs && $rs->numRows() !== 0)
	{
		echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100%\">\n";

		echo "<tr>";

		echo "<th width=\"50%\">". __("Name") ."</th>";
		echo "<th width=\"50%\">". __("Nummer") ."</th></tr>\n";

		while ( $r = $rs->fetchRow() )
		{
			$name = $r["ln"] .(strlen($r["fn"]) > 0 ? (", ". $r["fn"]) : "");
			$number = $r["ext"];

			echo "<tr>";

			echo "<td width=\"50%\">". htmlEnt($name) ."</td>";
			echo "<td width=\"50%\"><a href=\"tel://". $number."\">". $number ."</a></td></tr>\n";

		}

		echo "</table>\n";
	}
	else
	{
		echo "<br />". htmlEnt($noresultsmsg). "<br />\n";
	}

	echo "</body>\n";

	echo "<softkey index=\"1\" label=\"". htmlEnt(__("Zur\xC3\xBCck")) ."\" action=\"Softkey:Back\" />\n";
	echo "<softkey index=\"2\" label=\"". htmlEnt(__("Suchen")) ."\" action=\"Softkey:Fetch;". $url_polycom_pb ."?u=". $user ."&amp;m=". $mac ."&amp;t=". $type ."&amp;searchform=1\" />\n";
	echo "<softkey index=\"3\" label=\"". htmlEnt(__("Beenden")) ."\" action=\"Softkey:Exit\" />\n";
	echo "<softkey index=\"4\" label=\"\" action=\"\" />\n";
	echo "</html>\n";

	_ob_send();
}
#################################### INTERNAL PHONEBOOK }



#################################### PRIVATE PHONEBOOK {

if ( $type === "prv" )
{
	$mac = preg_replace("/[^\dA-Z]/", "", strtoupper(trim(@$_REQUEST["m"])));

	ob_start();

	echo $phonebook_doctype ."\n";

	$pagetitle = __("Telefonbuch") ." - ". $typeToTitle[$type];
	$searchsql = "1";

	$noresultsmsg = __("Ihr pers\xC3\xB6nliches Telefonbuch enth\xC3\xA4lt keine Eintr\xC3\xA4ge.");

	if ( strlen($querystring) > 0 )
	{
		$pagetitle .= " ('". $querystring ."')";
		$searchsql = "`lastname` LIKE '%". $querystring ."%' OR `firstname` LIKE '%". $querystring ."%'";
		$noresultsmsg = sprintf(__("Keine Treffer f\xC3\xBCr \"%s\". Dr\xC3\xBCcken Sie 'Zur\xC3\xBCck', um eine neue Suche auszuf\xC3\xBChren."), $querystring);
	}

	echo "<html>\n";
	echo "<head><title>". htmlEnt($pagetitle) ."</title></head>\n";
	echo "<body><br />\n";

	$user_id_check = $db->executeGetOne("SELECT `user_id` FROM `phones` WHERE `mac_addr`='". $db->escape($mac) ."'");
	if ($user_id != $user_id_check)
		_err("Not authorized");

	$remote_addr = @$_SERVER["REMOTE_ADDR"];
	$remote_addr_check = $db->executeGetOne("SELECT `current_ip` FROM `users` WHERE `id`=". $user_id);
	if ($remote_addr != $remote_addr_check)
		_err("Not authorized");

	$query =
		"SELECT `lastname` `ln`, `firstname` `fn`, `number` ".
		"FROM ".
		"  `pb_prv` ".
		"WHERE ".
		"  `user_id`=". $user_id ." AND ".
		$searchsql ." ".
		"ORDER BY `lastname`, `firstname` ".
		"LIMIT ". $num_results;

	$rs = $db->execute($query);
	if ($rs->numRows() !== 0)
	{
		echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100%\">\n";

		echo "<tr>";

		echo "<th width=\"50%\">". htmlEnt(__("Name")) ."</th>";
		echo "<th width=\"50%\">". htmlEnt(__("Nummer")) ."</th></tr>\n";

		while ( $r = $rs->fetchRow() )
		{
			$name = $r["ln"] .(strlen($r["fn"]) > 0 ? (", ". $r["fn"]) : "");
			$number = $r["number"];

			echo "<tr>";

			echo "<td width=\"50%\">". htmlEnt($name) ."</td>";
			echo "<td width=\"50%\"><a href=\"tel://". $number."\">". $number ."</a></td>";

			echo "</tr>\n";
		}

		echo "</table>\n";
	}
	else
	{
		echo "<br />". htmlEnt($noresultsmsg) ."<br />\n";
	}

	echo "</body>\n";

	echo "<softkey index=\"1\" label=\"". __("Zur\xC3\xBCck") ."\" action=\"Softkey:Back\" />\n";
	echo "<softkey index=\"2\" label=\"". __("Suchen") ."\" action=\"Softkey:Fetch;". $url_polycom_pb ."?u=". $user ."&amp;m=". $mac ."&amp;t=". $type ."&amp;searchform=1\" />\n";
	echo "<softkey index=\"3\" label=\"". __("Beenden") ."\" action=\"Softkey:Exit\" />\n";
	echo "<softkey index=\"4\" label=\"\" action=\"\" />\n";
	echo "</html>\n";

	_ob_send();
}

#################################### PRIVATE PHONEBOOK }

?>