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
* Language code helper functions to ease handling of INTL_LANG etc.
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
include_once(GS_DIR ."inc/gs-lib.php");

/////////////////////////////////////////////////////////////////////////////
// defines

define("GS_LANG_OPT_AST", "1");
define("GS_LANG_OPT_GS", "2");

define("GS_LANG_FORMAT_AST", "1");
define("GS_LANG_FORMAT_GS", "2");

/////////////////////////////////////////////////////////////////////////////
// translation table for 2-letter -> iso conversion

$gs_lang_transtable = Array(
	"de" => "de-DE",
	"en" => "en-US",
	"us" => "en-US",
);

/////////////////////////////////////////////////////////////////////////////
//--- gs_lang_ast2gs()

function gs_lang_ast2gs($langcode)
{
	global $gs_lang_transtable;

	$langcode_fixed = strtolower(substr(trim($langcode), 0, 2));
	if(strlen($langcode_fixed) != 2) return FALSE;

	$langcode_gs = $gs_lang_transtable[$langcode_fixed];
	if(strlen($langcode_gs) <= 0)
	{
		return $langcode_gs ."-". strtoupper($langcode_gs);
	}

	return $langcode_gs;
}

/////////////////////////////////////////////////////////////////////////////
//--- gs_lang_gs2ast()

function gs_lang_gs2ast($langcode)
{
	return(substr($langcode, 0, 2));
}

/////////////////////////////////////////////////////////////////////////////
//--- gs_get_lang_user()

function gs_get_lang_user($db, $userid, $gs_lang_format)
{
	$lang_user = (string)$db->executeGetOne(
		'SELECT `language` FROM `ast_sipfriends` '.
		'WHERE '.
			'`ast_sipfriends`.`name`='. (int)$userid
		);

	switch($gs_lang_format)
	{
		case GS_LANG_FORMAT_AST:
			return substr($lang_user, 0, 2);
			break;
		case GS_LANG_FORMAT_GS:
			return gs_lang_ast2gs($lang_user);
			break;
		default:
			return false;
			break;
	}

	return false;
}

/////////////////////////////////////////////////////////////////////////////
//--- gs_get_lang_global()

function gs_get_lang_global($gs_lang_opt, $gs_lang_format)
{
	$lang = "";

	switch($gs_lang_opt)
	{
		case GS_LANG_OPT_AST:
			$lang_src = gs_get_conf("GS_INTL_ASTERISK_LANG");
			break;
		case GS_LANG_OPT_GS:
			$lang_src = gs_get_conf("GS_INTL_LANG");
			break;
		default:
			return false;
	}

	switch($gs_lang_format)
	{
		case GS_LANG_FORMAT_AST:
			return substr($lang_src, 0, 2);
			break;
		case GS_LANG_FORMAT_GS:
			if($gs_lang_opt == GS_LANG_OPT_GS)
				return $lang_src;

			return gs_lang_ast2gs($lang_src);
			break;
		default:
			return false;
			break;
	}

	return false;
}

?>
