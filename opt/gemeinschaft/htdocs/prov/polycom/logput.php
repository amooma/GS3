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

define("GS_VALID", true);  /// this is a parent file

define("URL_BASE", "/gemeinschaft/prov/polycom/");
define("LOG_SUBDIR", "logs/");

require_once(dirname(__FILE__) ."/../../../inc/conf.php");

//---------------------------------------------------------------------------

function puterror($status, $body, $log = FALSE)
{
	header("HTTP/1.1 ". $status);
	die("<html><head><title>Error ". $status ."</title></head><body>". $body ."</body></html>");
}

//---------------------------------------------------------------------------

function putfile()
{
	$f = pathinfo($fname = $_SERVER["REQUEST_URI"]);

	if ($f["extension"] != "log")
		puterror("403 Forbidden", "Bad file type in ". $fname);

	$f = fopen(LOG_SUBDIR . ($fname = $f["basename"]), "w");
	if (!$f)
		puterror("409 Create error", "Couldn't create file");
	$s = fopen("php://input", "r");
	if(!$s)
		puterror("404 Input Unavailable", "Couldn't open input");

	while ( $kb = fread($s, 1024) )
		fwrite($f, $kb, 1024);
	fclose($f);
	fclose($s);
	chmod(LOG_SUBDIR . $fname, 0666);
	$fname = URL_BASE . LOG_SUBDIR . $fname;
	header("Location: ". $fname);
	header("HTTP/1.1 201 Created");
	echo "<html><head><title>Success</title></head><body>";
	echo "<p>Created <a href=\"". $fname ."\">". $fname ."</a> OK.</p></body></html>";
}

//---------------------------------------------------------------------------
//---------------------------------------------------------------------------
//---------------------------------------------------------------------------
//--- main()

if ($_SERVER["REQUEST_METHOD"] != "PUT")
{
	header("HTTP/1.1 403 Bad Request");
	exit();
}

if (! gs_get_conf("GS_POLYCOM_PROV_ALLOW_LOG_PUT") )
{
	header("HTTP/1.1 405 Method not allowed");
	exit();
}

$ua = trim(@$_SERVER["HTTP_USER_AGENT"]);
if ( (!preg_match("/PolycomSoundPointIP/", $ua)) && (!preg_match("/PolycomSoundStationIP/", $ua)) )
{
	header("HTTP/1.1 405 Method not allowed");
	exit();
}

putfile();

?>
