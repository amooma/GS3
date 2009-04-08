<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* 
* Author: Daniel Scheller <scheller@loca.net> - LocaNet oHG
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

defined('GS_VALID') or die('No direct access.');
include_once( GS_DIR .'inc/get-listen-to-ids.php' );
include_once( GS_DIR .'inc/gs-fns/gs_hosts_get.php' );

define('AUDIOUPLOAD_DESTDIR', '/opt/gemeinschaft/sys-rec');

//--- output title
echo "<h2>";
if(@$MODULES[$SECTION]["icon"])
	echo "<img alt=\" \" src=\"". GS_URL_PATH . str_replace("%s", "32", $MODULES[$SECTION]["icon"]) ."\" /> ";
if(count($MODULES[$SECTION]["sub"]) > 1)
	echo $MODULES[$SECTION]["title"] ." - ";
echo $MODULES[$SECTION]["sub"][$MODULE]["title"];
echo "</h2>\n";

//--- include some javascript stuff
echo "<script type=\"text/javascript\" src=\"". GS_URL_PATH ."js/arrnav.js\"></script>\n";

//--- get browser get/post stuff
$per_page    = (int)GS_GUI_NUM_RESULTS;
$page        =      (int)@$_REQUEST['page'        ];

$save        = (int)trim(@$_REQUEST['save'        ]);
$edit        = (int)trim(@$_REQUEST['edit'        ]);
$description =      trim(@$_REQUEST['description' ]);
$delete      = (int)trim(@$_REQUEST['delete'      ]);

$newaction   =      trim(@$_REQUEST['newaction'   ]);
$phonenum    = (int)trim(@$_REQUEST['phonenum'    ]);
$playback    = (int)trim(@$_REQUEST['playback'    ]);

$audio_exts = Array('aif', 'aiff', 'wav', 'au', 'al', 'alaw', 'la',
			'ul', 'ulaw', 'lu', 'gsm', 'cdr', 'mp3', 'ogg');

$errormsgs = Array();

//--- helper

function _pack_int($int)
{
	$str = base64_encode(pack("N", $int));
	return preg_replace("/[^a-z\d]/i", "", $str);
}

//-------------------------------------------------------------------------
//-------------------------------------------------------------------------
//--- copy file to other connected nodes
//-------------------------------------------------------------------------
//-------------------------------------------------------------------------

function distribute_file($localfile)
{
	global $errormsgs;

	$hostlist = gs_hosts_get();
	$thishost = @gs_get_listen_to_ids();

	foreach($hostlist as $currenthost)
	{
		unset($islocalhost);
		$islocalhost = FALSE;

		foreach($thishost as $hostid)
		{
			if($currenthost["id"] == $hostid) $islocalhost = TRUE;
		}

		if($islocalhost == FALSE)
		{
			unset($cmd);
			$cmd = "sudo scp -o StrictHostKeyChecking=no -o BatchMode=yes ". qsa($localfile ) ." ". qsa("root@". $currenthost["host"] .":". $localfile);
			@exec($cmd ." 1>>/dev/null 2>>/dev/null", $out, $err);
			if($err != 0)
			{
				gs_log(GS_LOG_WARNING, "Failed to scp system recording '". $localfile ."' to ". $currenthost["host"]);
				$errormsgs[] = sprintf(__('Audiodatei konnte nicht auf Node %s kopiert werden'), $currenthost["host"]);
			}
			else
			{
				unset($cmd);
				$cmd = "sudo ssh -o StrictHostKeyChecking=no -o BatchMode=yes -l root ". qsa($currenthost["host"]) ." ". qsa("chmod a+rw ". qsa($localfile));
				@exec($cmd ." 1>>/dev/null 2>>/dev/null", $out, $err);
				if($err != 0)
				{
					gs_log(GS_LOG_WARNING, "Failed to chmod system recording '". $localfile ."' on ". $currenthost["host"]);
					$errormsgs[] = sprintf(__('Berechtigungen k&ouml;nnen auf Node %s nicht gesetzt werden'), $currenthost["host"]);
				}
			}
		}
	}
}

//-------------------------------------------------------------------------
//-------------------------------------------------------------------------
//--- remove file from other connected nodes
//-------------------------------------------------------------------------
//-------------------------------------------------------------------------

function distribute_remove($localfile)
{
	global $errormsgs;

	$hostlist = gs_hosts_get();
	$thishost = @gs_get_listen_to_ids();

	foreach($hostlist as $currenthost)
	{
		unset($islocalhost);
		$islocalhost = FALSE;

		foreach($thishost as $hostid)
		{
			if($currenthost["id"] == $hostid) $islocalhost = TRUE;
		}

		if($islocalhost == FALSE)
		{
			unset($cmd);
			$cmd = "sudo ssh -o StrictHostKeyChecking=no -o BatchMode=yes -l root ". qsa($currenthost["host"]) ." ". qsa("rm ". qsa($localfile));
			@exec($cmd ." 1>>/dev/null 2>>/dev/null", $out, $err);
			if($err != 0)
			{
				gs_log(GS_LOG_WARNING, "Failed to remove system recording '". $localfile ."' from ". $currenthost["host"]);
				$errormsgs[] = sprintf(__('Audiodatei kann nicht von Node %s gel&ouml;scht werden'), $currenthost["host"]);
			}
		}
	}
}

//-------------------------------------------------------------------------
//-------------------------------------------------------------------------
//--- handle new recording by phone
//-------------------------------------------------------------------------
//-------------------------------------------------------------------------

if($newaction === "byphone")
{
	$callfile =
		 "Channel: Local/". $phonenum ."@from-internal-users\n"
		."Context: sys-rec-record\n"
		."Extension: s\n"
		."Priority: 1\n"
		."Set: DIALEDEXTEN=". $phonenum ."\n"
		."CallerID: Sprachaufzeichnung <pbx>\n"
		."MaxRetries: 0\n";

	$filename = "gs-". _pack_int(time()) . rand(100,999) .".call";
	$tmp_filename = "/tmp/".$filename;
	$spoolfile = "/var/spool/asterisk/outgoing/". $filename;

	$cf = @fopen($tmp_filename, "wb");

	if(!$cf)
	{
		gs_log(GS_LOG_WARNING, "Failed to write call file '". $tmp_filename ."'");
		$errormsgs[] = __('Anruf kann nicht initiiert werden');
	}
	@fwrite($cf, $callfile, strlen($callfile));
	@fclose($cf);
	@chmod($tmp_filename, 00666);

	@exec("sudo mv ". qsa($tmp_filename) ." ". qsa($spoolfile) ." 1>>/dev/null 2>>/dev/null", $out, $err);
	if($err !== 0)
	{
		@unlink($tmp_filename);
		gs_log(GS_LOG_WARNING, "Failed to move call file '". $tmp_filename ."' to '". $spoolfile ."'");
		$errormsgs[] = __('Anruf kann nicht initiiert werden');
	}
}

//-------------------------------------------------------------------------
//-------------------------------------------------------------------------
//--- playback a recording to the user
//-------------------------------------------------------------------------
//-------------------------------------------------------------------------

if($playback)
{
	$callfile =
		 "Channel: Local/". $phonenum ."@from-internal-users\n"
		."Context: sys-rec-playback\n"
		."Extension: s\n"
		."Priority: 1\n"
		."Set: ID=". $playback ."\n"
		."CallerID: Sprachaufzeichnung <pbx>\n"
		."MaxRetries: 0\n";

	$filename = "gs-". _pack_int(time()) . rand(100,999) .".call";
	$tmp_filename = "/tmp/".$filename;
	$spoolfile = "/var/spool/asterisk/outgoing/". $filename;

	$cf = @fopen($tmp_filename, "wb");

	if(!$cf)
	{
		gs_log(GS_LOG_WARNING, "Failed to write call file '". $tmp_filename ."'");
		$errormsgs[] = __('Anruf kann nicht initiiert werden');
	}
	@fwrite($cf, $callfile, strlen($callfile));
	@fclose($cf);
	@chmod($tmp_filename, 00666);

	@exec("sudo mv ". qsa($tmp_filename) ." ". qsa($spoolfile) ." 1>>/dev/null 2>>/dev/null", $out, $err);
	if($err !== 0)
	{
		@unlink($tmp_filename);
		gs_log(GS_LOG_WARNING, "Failed to move call file '". $tmp_filename ."' to '". $spoolfile ."'");
		$errormsgs[] = __('Anruf kann nicht initiiert werden');
	}
}

//-------------------------------------------------------------------------
//-------------------------------------------------------------------------
//--- handle new upload recording
//-------------------------------------------------------------------------
//-------------------------------------------------------------------------

if($newaction === "upload")
{
	if(!isset($_FILES["audiofile"]))
	{
		$errormsgs[] = __('Datei-Upload fehlgeschlagen!');
	}
	else
	{
		switch(@$_FILES["audiofile"]["error"])
		{
			case UPLOAD_ERR_OK :
				@preg_match("/\.([a-z\d]+)$/", strtolower(@$_FILES["audiofile"]["name"]), $matches);
				$fileext = @$matches[1];

				if(!in_array($fileext, $audio_exts, true))
				{
					$errormsgs[] = sprintf(__('Datei %s ist keine zul&auml;ssige Audio-Datei'), $_FILES["audiofile"]["name"]);
					$errormsgs[] = __('Erlaubte Formate') .": ". implode(", ", $audio_exts);
				}
				elseif(!is_uploaded_file(@$_FILES["audiofile"]["tmp_name"]))
				{
					$errormsgs[] = __('Zugriffsverletzung!');
				}
				else
				{
					//--- user uploaded something valid, proceed...
					$tmpfile_ext = @$_FILES["audiofile"]["tmp_name"] .".". $fileext;
					$thiserr = @rename(@$_FILES["audiofile"]["tmp_name"], $tmpfile_ext);

					if(!$thiserr)
					{
						$errormsgs[] = __('Fehler beim Verarbeiten der hochgeladenen Datei.');
					}
					else
					{
						@chmod($tmpfile_ext, 0666);

						$soxcmd = "sox -q ". qsa($tmpfile_ext) ." -r 8000 -c 1 -s -w -t raw - 2>>/dev/null";
						$audiodata = `$soxcmd`;

						@unlink($tmpfile_ext);

						$audio_length = floor(strlen($audiodata) / 16000);
						if($audio_length < 1)
						{
							$errormsgs[] = __('Fehler bei Dateikonvertierung');
						}
						else
						{
							$audio_md5 = md5($audiodata);
							$audio_destination = AUDIOUPLOAD_DESTDIR ."/".$audio_md5.".sln";

							unset($thiserr);
							$destfile = @fopen($audio_destination, "wb");

							if(!$destfile)
							{
								$errormsgs[] = __('Fehler beim Verarbeiten der hochgeladenen Datei.');
							}
							else
							{
								$writelength = @fwrite($destfile, $audiodata);
								@fclose($destfile);

								if($writelength != strlen($audiodata))
								{
									$errormsgs[] = __('Fehler beim Verarbeiten der hochgeladenen Datei.');
									@unlink($audio_destination);
								}
								else
								{
									@chmod($audio_destination, 0666);
									$audio_newdescr = "Hochgeladene Datei ".@$_FILES["audiofile"]["name"];

									$sql_query =
										'INSERT INTO `systemrecordings` (
											`md5hashname`,
											`description`,
											`length`) VALUES (
											"'. $DB->escape($audio_md5) .'",
											"'. $DB->escape($audio_newdescr) .'",
											"'. $DB->escape($audio_length) .'")';
									$rs = $DB->execute($sql_query);

									$errormsgs[] = __('Audiodatei erfolgreich hochgeladen.');

									distribute_file($audio_destination);
								}
							}
						}
					}
				}
				break;
			case UPLOAD_ERR_INI_SIZE :
			case UPLOAD_ERR_FORM_SIZE :
				$errormsgs[] = __('Hochgeladene Datei zu gro&szlig;.');
				break;
			case UPLOAD_ERR_PARTIAL : 
			case UPLOAD_ERR_NO_FILE :
			default :
				$errormsgs[] = __('Datei-Upload fehlgeschlagen!');
				break;
		}
	}
}

//-------------------------------------------------------------------------
//-------------------------------------------------------------------------
//--- handle entry delete
//-------------------------------------------------------------------------
//-------------------------------------------------------------------------

if($delete)
{
	$sql_query =
'SELECT
`s`.`md5hashname` `md5hashname`
FROM
`systemrecordings` `s`
WHERE
`s`.`id` = "'. $delete .'"';

	$rs = $DB->execute($sql_query);
	$num_total = @$DB->numFoundRows();

	if($num_total != 1)
	{
		$errormsgs[] = __('Es ist ein Datenbankfehler aufgetreten');
	}
	else
	{
		$r = $rs->fetchRow();
		if(strlen($r["md5hashname"]) <= 0)
		{
			$errormsgs[] = __('Es ist ein Datenbankfehler aufgetreten');
		}
		else
		{
			$audio_realpath = AUDIOUPLOAD_DESTDIR ."/".$r["md5hashname"].".sln";
			if(!file_exists($audio_realpath))
			{
				$errormsgs[] = __('Die zugeh&ouml;rige Audiodatei existiert nicht');
			}

			distribute_remove($audio_realpath);
			@unlink($audio_realpath);

			$sql_query =
				'DELETE FROM `systemrecordings`
				WHERE `id`='. $delete;
			$rs = $DB->execute($sql_query);

			$errormsgs[] = __('Audiodatei gel&ouml;scht');
		}
	}
}

//-------------------------------------------------------------------------
//-------------------------------------------------------------------------
//--- handle entry edit
//-------------------------------------------------------------------------
//-------------------------------------------------------------------------

if($save)
{
	$sql_query =
'UPDATE `systemrecordings` SET
`description`=\''. $DB->escape($description) .'\'
WHERE `id`='. $save;
	$rs = $DB->execute($sql_query);
}

//-------------------------------------------------------------------------
//-------------------------------------------------------------------------
//--- output overview list
//-------------------------------------------------------------------------
//-------------------------------------------------------------------------

//------------
//--- get data from db

$sql_query =
'SELECT
	`s`.`id` `id`,
	UNIX_TIMESTAMP(`s`.`date`) `date`,
	`s`.`description` `description`,
	`s`.`length` `length`
FROM
	`systemrecordings` `s`
ORDER BY `s`.`id`
LIMIT '. ($page*(int)$per_page) .','. (int)$per_page;

$rs = $DB->execute($sql_query);

$num_total = @$DB->numFoundRows();
$num_pages = ceil($num_total / $per_page);

//------------
//--- display errors if any (

if(is_array($errormsgs) && (count($errormsgs) > 0))
{
	unset($thismsg);
	echo "<div style=\"max-width:600px;\">\n";

	foreach($errormsgs as $thismsg)
	{
		echo "<img alt=\"\" src=\"". GS_URL_PATH ."crystal-svg/16/app/important.png\" class=\"fl\" />";
		echo "<p style=\"margin-left:22px;\">". $thismsg ."</p>\n";
	}

	echo "</div>\n";
}

//--- ) display errors if any
//------------

unset($playback_opts);
$playback_opts = Array();

echo "<h3>". __('Vorhandene Audiodateien') ."</h3>\n";

//------------
//--- output form (
echo "<form method=\"post\" action=\"". GS_URL_PATH ."\">\n";
echo gs_form_hidden($SECTION, $MODULE);

if($edit > 0)
{
	echo "<input type=\"hidden\" name=\"page\" value=\"". htmlEnt($page) ."\" />\n";
	echo "<input type=\"hidden\" name=\"save\" value=\"". $edit ."\" />\n";
}

//------------
//--- table header (
echo "<table cellspacing=\"1\" class=\"phonebook\">\n";
echo "<thead>\n";
echo "<tr>\n";
echo "\t<th style=\"width:30px;\">". __('ID') ."</th>\n";
echo "\t<th style=\"width:150px;\">". __('Beschreibung') ."</th>\n";
echo "\t<th style=\"width:30px;\">". __('L&auml;nge') ."</th>\n";
echo "\t<th style=\"width:30px;\">". __('Erstellt') ."</th>\n";
echo "\t<th style=\"width:80px;\">";

//------------
//--- page indicator and arrows (
echo (($num_pages > 0) ? ($page+1) : "0")." / ". $num_pages ."&nbsp;\n";

if($page > 0)
{
	echo
	 "\t\t<a href=\"". gs_url($SECTION, $MODULE, null, "page=".($page-1)) ."\" title=\"". __('zur&uuml;ckbl&auml;ttern') ."\" id=\"arr-prev\">"
	."<img alt=\"". __('zur&uuml;ck') ."\" src=\"". GS_URL_PATH ."crystal-svg/16/act/previous.png\" />"
	."</a>\n";
}
else
{
	echo
	"\t\t<img alt=\"". __('zur&uuml;ck') ."\" src=\"". GS_URL_PATH ."crystal-svg/16/act/previous_notavail.png\" />\n";
}

if($page < $num_pages-1)
{
	echo
	 "\t\t<a href=\"". gs_url($SECTION, $MODULE, null, "page=".($page+1)) ."\" title=\"". __('weiterbl&auml;ttern') ."\" id=\"arr-next\">"
	."<img alt=\"". __('weiter') ."\" src=\"". GS_URL_PATH ."crystal-svg/16/act/next.png\" />"
	."</a>\n";
}
else
{
	echo
	"\t\t<img alt=\"". __('weiter') ."\" src=\"". GS_URL_PATH ."crystal-svg/16/act/next_notavail.png\" />\n";
}

//--- ) page indicator and arrows
//------------

echo "\t</th>\n";
echo "</tr>\n";
echo "</thead>\n";

//--- ) table header
//------------
//--- table body (

echo "<tbody>\n";

if(@$rs)
{
	$i = 0;
	while($r = $rs->fetchRow())
	{
		//--- collect playback select options here...
		$playback_opts[] = "<option value=\"". $r["id"] ."\">". $r["id"] ." - ". htmlEnt($r["description"]) ."</option>";

		unset($r_length); unset($r_created);
		$r_length = (floor($r['length'] / 60)).":". sprintf("%02d", ($r['length'] % 60));

		$r_created = strftime("%d.%m.%y,&nbsp;%H:%M:%S", $r['date']);

		echo "<tr class=\"". ((++$i % 2) ? "odd" : "even") ."\">\n";

		if($edit === $r['id'])
		{
			echo "<td class=\"r\">". htmlEnt($r['id']) ."</td>\n";

			echo "<td>";
			echo "<input type=\"text\" name=\"description\" value=\"". htmlEnt($r['description']) ."\" size=\"25\" maxlength=\"150\" />";
			echo "</td>\n";

			echo "<td class=\"r\">". $r_length ."</td>\n";
			echo "<td class=\"r\">". $r_created ."</td>\n";

			echo "<td>";
			echo "<button type=\"submit\" title=\"". __('Speichern') ."\" class=\"plain\">";
			echo "<img alt=\"". __('Speichern') ."\" src=\"". GS_URL_PATH ."crystal-svg/16/act/filesave.png\" />";
			echo "</button>\n";

			echo "&nbsp;\n";

			echo "<a href=\"". gs_url($SECTION, $MODULE) ."\"><button type=\"button\" title=\"". __('Abbrechen') ."\" class=\"plain\">";
			echo "<img alt=\"". __('Abbrechen') ."\" src=\"". GS_URL_PATH ."crystal-svg/16/act/cancel.png\" />";
			echo "</button></a>\n";

			echo "</td>\n";
		}
		else
		{
			echo "<td class=\"r\">". htmlEnt($r['id']) ."</td>\n";

			echo "<td>". htmlEnt($r['description']) ."</td>\n";

			echo "<td class=\"r\">". $r_length ."</td>\n";
			echo "<td class=\"r\">". $r_created ."</td>\n";

			echo "<td>\n";
			echo "<a href=\"". gs_url($SECTION, $MODULE, null, "playback=". $r['id'] ."&amp;page=". $page ."&amp;phonenum=". $_SESSION["real_user"]["info"]["ext"]) ."\" title=\"". __('abspielen') ."\"><img alt=\"". __('abspielen') ."\" src=\"". GS_URL_PATH ."crystal-svg/16/app/kmix.png\" /></a> &nbsp; ";
			echo "<a href=\"". gs_url($SECTION, $MODULE, null, "edit=". $r['id'] ."&amp;page=".$page) ."\" title=\"". __('bearbeiten') ."\"><img alt=\"". __('bearbeiten') ."\" src=\"". GS_URL_PATH ."crystal-svg/16/act/edit.png\" /></a> &nbsp; ";
			echo "<a href=\"". gs_url($SECTION, $MODULE, null, "delete=". $r['id'] ."&amp;page=".$page) ."\" title=\"". __('l&ouml;schen') ."\"><img alt=\"". __('entfernen') ."\" src=\"". GS_URL_PATH ."crystal-svg/16/act/editdelete.png\" /></a>";
			echo "</td>\n";
		}

		echo "</tr>\n";
	}
}

echo "</tbody>\n";

//--- ) table body
//------------

echo "</table>\n";

//--- ) table header
//------------

echo "</form>\n";

//--- ) output form
//------------

//-------------------------------------------------------------------------
//-------------------------------------------------------------------------
//--- review existing system recording
//-------------------------------------------------------------------------
//-------------------------------------------------------------------------

echo "<h3>". __('Audiodatei abspielen') ."</h3>\n";

echo "<form method=\"post\" action=\"". GS_URL_PATH ."\">\n";
echo gs_form_hidden($SECTION, $MODULE);

echo "<table cellspacing=\"1\" class=\"phonebook\">\n";
echo "<thead>\n";
echo "<tr>\n";
echo "\t<th>". __('Audiodatei') ."</th>\n";
echo "\t<th>". __('Ihre Durchwahl') ."</th>\n";
echo "\t<th>&nbsp;</th>\n";
echo "\t</th>\n";
echo "</tr>\n";
echo "</thead>\n";

echo "<tbody>\n";

echo "<tr class=\"odd\">\n";
echo "<td>";

echo "<select name=\"playback\" size=\"1\" style=\"width:250px;\">";
foreach($playback_opts as $thisopt) { echo $thisopt; }
echo "</select>\n";

echo "</td>\n";

echo "<td>";
echo "<input type=\"text\" name=\"phonenum\" value=\"\" size=\"7\" maxlength=\"8\" />";
echo "</td>\n";
echo "<td>";

echo "<button type=\"submit\" title=\"". __('Anrufen &amp; abspielen') ."\" class=\"plain\">";
echo "<img alt=\"". __('Anrufen &amp; abspielen') ."\" src=\"". GS_URL_PATH ."crystal-svg/16/app/kmix.png\" />";
echo "</button>";

echo "</td></tr>\n";


echo "</tbody>\n";
echo "</table></form><br>\n";

//-------------------------------------------------------------------------
//-------------------------------------------------------------------------
//--- new system recording input
//-------------------------------------------------------------------------
//-------------------------------------------------------------------------

echo "<br><br><h3>". __('Neue Audiodatei') ."</h3>\n";

//------------
//--- form to record by phone (

echo "<form method=\"post\" action=\"". GS_URL_PATH ."\">\n";
echo gs_form_hidden($SECTION, $MODULE);
echo "<input type=\"hidden\" name=\"newaction\" value=\"byphone\">\n";

//------------
//--- table header (
echo "<table cellspacing=\"1\" class=\"phonebook\">\n";
echo "<thead>\n";
echo "<tr>\n";
echo "\t<th style=\"width:350px;\" colspan=\"2\">". __('Per Telefon aufzeichnen:') ."</th>\n";
echo "</tr>\n";
echo "</thead>\n";

//--- ) table header
//------------

echo "<tbody>\n";

echo "<tr class=\"odd\">\n";
echo "<td style=\"width:320px;\">". __('Ihre interne Durchwahl:') ."&nbsp;";
echo "<input type=\"text\" name=\"phonenum\" value=\"\" size=\"7\" maxlength=\"8\" />";

echo "</td>\n";
echo "<td>";

echo "<button type=\"submit\" title=\"". __('Anrufen &amp; aufzeichnen') ."\" class=\"plain\">";
echo "<img alt=\"". __('Anrufen &amp; aufzeichnen') ."\" src=\"". GS_URL_PATH ."crystal-svg/16/app/yast_PhoneTTOffhook.png\" />";
echo "</button>";

echo "</td>\n";
echo "</tr>\n";
echo "</table><br>\n";
echo "</form>\n";

//--- ) form to record by phone
//------------


//------------
//--- form to upload file (

echo "<form method=\"post\" action=\"". GS_URL_PATH ."\" enctype=\"multipart/form-data\">\n";
echo gs_form_hidden($SECTION, $MODULE);
echo "<input type=\"hidden\" name=\"newaction\" value=\"upload\">\n";

//------------
//--- table header (
echo "<table cellspacing=\"1\" class=\"phonebook\">\n";
echo "<thead>\n";
echo "<tr>\n";
echo "\t<th style=\"width:350px;\" colspan=\"2\">". __('Audiodatei hochladen:') ."</th>\n";
echo "</tr>\n";
echo "</thead>\n";

//--- ) table header
//------------

echo "<tbody>\n";

echo "<tr class=\"odd\">\n";
echo "<td style=\"width:320px;\">". __('Datei:') ."&nbsp;";
echo "<input type=\"file\" name=\"audiofile\" size=\"45\" style=\"font-size:10px; width:230px;\" accept=\"audio/*\" />\n";

echo "</td>\n";
echo "<td>";

echo "<button type=\"submit\" title=\"". __('Hochladen') ."\" class=\"plain\">";
echo "<img alt=\"". __('Hochladen') ."\" src=\"". GS_URL_PATH ."crystal-svg/16/act/filesave.png\" />";
echo "</button>";

echo "</td>\n";
echo "</tr>\n";
echo "</table><br>\n";
echo "</form>\n";

//--- ) form to record by phone
//------------


?>