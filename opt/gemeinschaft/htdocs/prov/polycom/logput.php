<?php

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

	if($f["extension"] != "log") puterror("403 Forbidden", "Bad file type in ". $fname);

	$f = fopen(LOG_SUBDIR . ($fname = $f["basename"]), "w");
	if(!$f) puterror("409 Create error", "Couldn't create file");
	$s = fopen("php://input", "r");
	if(!$s) puterror("404 Input Unavailable", "Couldn't open input");
	while($kb = fread($s, 1024)) fwrite($f, $kb, 1024);
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

if($_SERVER["REQUEST_METHOD"] != "PUT")
{
	header("HTTP/1.1 403 Bad Request");
	exit();
}

if(!gs_get_conf("GS_POLYCOM_PROV_ALLOW_LOG_PUT"))
{
	header("HTTP/1.1 405 Method not allowed");
	exit();
}

$ua = trim(@$_SERVER["HTTP_USER_AGENT"]);
if((!preg_match("/PolycomSoundPointIP/", $ua)) && (!preg_match("/PolycomSoundStationIP/", $ua)))
{
	header("HTTP/1.1 405 Method not allowed");
	exit();
}

putfile();

?>
