<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
*
* $Revision$
*
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
*
* Helpers for caching phonetypes for user reassigns/reboots to
* assist in proper phonetype-dependent config rechecking
* Copyright 2010 Daniel Scheller <scheller@loca.net>
* LocaNet oHG, Lindemannstr. 81, 44137 Dortmund, Germany
* http://www.loca.net
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

defined("GS_VALID") or die("No direct access.");

/////////////////////////////////////////////////////////////////////////////
// defines

define("GS_PHONETYPECACHE_MAXAGE", "300"); // seconds

/////////////////////////////////////////////////////////////////////////////
//--- gs_phonetypecache_prune()

function gs_phonetypecache_prune($db)
{
	$timestamp_removebefore = (int) time() - GS_PHONETYPECACHE_MAXAGE; 

	$sql_prune = "DELETE FROM `phones_typecache` "
		."WHERE "
		."`epoch_inserted` < ". @mysql_real_escape_string($timestamp_removebefore) ." ";

	$db->execute($sql_prune);

	return TRUE;
}

/////////////////////////////////////////////////////////////////////////////
//--- gs_phonetypecache_get()

function gs_phonetypecache_get($db, $entrytype, $entryvalue)
{
	$sql_retrieve = "SELECT "
		."`phonetype` "
		."FROM `phones_typecache` "
		."WHERE "
		."`entrytype` = '". @mysql_real_escape_string($entrytype) ."' "
		."AND `value` = '". @mysql_real_escape_string($entryvalue) ."' ";

	return (string) $db->executeGetOne($sql_retrieve);
}

/////////////////////////////////////////////////////////////////////////////
//--- gs_phonetypecache_add()

function gs_phonetypecache_add($db, $entrytype, $entryvalue, $phonetype)
{
	$entry_timestamp = (int) time();

	$sql_reset = "DELETE FROM `phones_typecache` "
		."WHERE "
		."`entrytype` = '". @mysql_real_escape_string($entrytype) ."' "
		."AND `value` = '". @mysql_real_escape_string($entryvalue) ."' ";
	$db->execute($sql_reset);

	$sql_insert = "INSERT INTO `phones_typecache` ( "
		."`entrytype`, `value`, `phonetype`, `epoch_inserted` ) "
		."VALUES ( "
		."'". @mysql_real_escape_string($entrytype) ."', "
		."'". @mysql_real_escape_string($entryvalue) ."', "
		."'". @mysql_real_escape_string($phonetype) ."', "
		." ". @mysql_real_escape_string($entry_timestamp) ." )";
	$db->execute($sql_insert);

	return TRUE;
}

/////////////////////////////////////////////////////////////////////////////
//--- gs_phonetypecache_add_by_uid_to_ext()
//--- Resolves given userid to ext and caches the ext's phone type

function gs_phonetypecache_add_by_uid_to_ext($db, $uid)
{
	$res_extptype = $db->execute("SELECT "
		."`s`.`name` AS `ext`, "
		."`p`.`type` AS `phonetype` "
		."FROM "
		."`phones` `p` LEFT JOIN "
		."`users` `u` ON (`u`.`id`=`p`.`user_id`) LEFT JOIN "
		."`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) "
		."WHERE "
		."`u`.`id` = '". @mysql_real_escape_string($uid) ."' ");

	while($row_extptype = $res_extptype->fetchRow())
	{
		gs_phonetypecache_add($db, "ext", $row_extptype["ext"], $row_extptype["phonetype"]);
	}

	return TRUE;
}

/////////////////////////////////////////////////////////////////////////////
//--- gs_phonetypecache_add_by_ext()
//--- Cache the ext's phone type

function gs_phonetypecache_add_by_ext($db, $ext)
{
	$ptype = $db->executeGetOne("SELECT "
		."`p`.`type` AS `phonetype` "
		."FROM "
		."`phones` `p` LEFT JOIN "
		."`users` `u` ON (`u`.`id`=`p`.`user_id`) LEFT JOIN "
		."`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) "
		."WHERE "
		."`s`.`name` = '". @mysql_real_escape_string($ext) ."' ");

	gs_phonetypecache_add($db, "ext", $ext, $ptype);

	return TRUE;
}

/////////////////////////////////////////////////////////////////////////////
//--- gs_phonetypecache_add_by_ip()
//--- Cache the IP's phone type

function gs_phonetypecache_add_by_ip($db, $ip)
{
	$ptype = $db->executeGetOne("SELECT "
		."`p`.`type` AS `phonetype` "
		."FROM "
		."`phones` `p` LEFT JOIN "
		."`users` `u` ON (`u`.`id`=`p`.`user_id`) LEFT JOIN "
		."`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) "
		."WHERE "
		."`u`.`current_ip` = '". @mysql_real_escape_string($ip) ."' ");

	gs_phonetypecache_add($db, "ip", $ip, $ptype);

	return TRUE;
}

/////////////////////////////////////////////////////////////////////////////
//--- gs_phonetypecache_add_by_ext_to_ip()
//--- Resolves given extension to current ip and caches the ip's phone type

function gs_phonetypecache_add_by_ext_to_ip($db, $ext)
{
	$res_ipptype = $db->execute("SELECT "
		."`u`.`current_ip` AS `ip`, "
		."`p`.`type` AS `phonetype` "
		."FROM "
		."`phones` `p` LEFT JOIN "
		."`users` `u` ON (`u`.`id`=`p`.`user_id`) LEFT JOIN "
		."`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) "
		."WHERE "
		."`s`.`name` = '". @mysql_real_escape_string($ext) ."' ");

	while($row_ipptype = $res_ipptype->fetchRow())
	{
		gs_phonetypecache_add($db, "ip", $row_ipptype["ip"], $row_ipptype["phonetype"]);
	}

	return TRUE;
}

/////////////////////////////////////////////////////////////////////////////
//--- gs_phonetypecache_add_by_uid_to_ip()
//--- Resolves given user_id to current ip and caches the ip's phone type

function gs_phonetypecache_add_by_uid_to_ip($db, $uid)
{
	$res_ipptype = $db->execute("SELECT "
		."`u`.`current_ip` AS `ip`, "
		."`p`.`type` AS `phonetype` "
		."FROM "
		."`phones` `p` LEFT JOIN "
		."`users` `u` ON (`u`.`id`=`p`.`user_id`) LEFT JOIN "
		."`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) "
		."WHERE "
		."`u`.`id` = '". @mysql_real_escape_string($uid) ."' ");

	while($row_ipptype = $res_ipptype->fetchRow())
	{
		gs_phonetypecache_add($db, "ip", $row_ipptype["ip"], $row_ipptype["phonetype"]);
	}

	return TRUE;
}

?>
