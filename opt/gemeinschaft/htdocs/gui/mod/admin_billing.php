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
* Billing module (c) 2009-2011 Daniel Scheller / LocaNet oHG
* scheller@loca.net / http://www.loca.net
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

$billing_currency = "EUR";
$billing_pricepermin_default = 0.19;
$billing_vat = 0.19; // percent/100

//--------------------------------------------------------------------------
//--- helpers

function sql_cleanup_string($dbobject, $cleanstring)
{
	$str_sql = str_replace(Array("*", "?"), Array("%", "_"), $cleanstring);
	$str_sql = str_replace("\\%", "*", $str_sql);

	return $dbobject->escape($str_sql);
}

function normalize_date($datestr, $incdays = 0)
{
	return date("Y-m-d", strtotime($datestr) + ($incdays * (24 * 3600)));
}

//--------------------------------------------------------------------------
//--------------------------------------------------------------------------
//--------------------------------------------------------------------------
//--- main()

//--- check if inside gemeinschaft-framework
if((@$_REQUEST["view"] != "print") && (@$_REQUEST["view"] != "csv"))
{
	defined("GS_VALID") or die("No direct access.");

	define("GS_URL_PATH_REAL", GS_URL_PATH);
}
else
{
	//--- we are called using print view - define everything needed

	//--- helper function gs_form_hidden from index.php
	function gs_form_hidden($sect='', $mod='', $sudo_user=null )
	{
	        global $SECTION, $MODULE, $_SESSION;
	        if (! $sudo_user) $sudo_user = @$_SESSION['sudo_user']['name'];
	        $ret = '<input type="hidden" name="s" value="'. $sect .'" />';
	        if ($mod)
	                $ret.= '<input type="hidden" name="m" value="'. $mod .'" />';
	        if ($sudo_user)
	                $ret.= '<input type="hidden" name="sudo" value="'. $sudo_user .'" />';
	        return $ret ."\n";
	}

	//--- helper function htmlEnt from index.php
	function htmlEnt($str)
	{
	        return htmlSpecialChars( $str, ENT_QUOTES, 'UTF-8' );
	}

	//--- setup environment and get configuration and includes
	define("GS_VALID", true);
	require_once(dirname(__FILE__) ."/../../../inc/conf.php");
	require_once(GS_DIR ."inc/util.php");
	include_once(GS_DIR ."inc/gettext.php");
	require_once(GS_DIR ."htdocs/gui/inc/session.php" );
	require_once(GS_HTDOCS_DIR ."inc/modules.php");

	//--- pretend we are a module
	$SECTION = "admin";
	$MODULE = "billing";

	//--- fix GS_URL_PATH
	define("GS_URL_PATH_REAL", str_replace("/mod/", "/", GS_URL_PATH));

	set_error_handler("err_handler_die_on_err");
}

//--- open database
$CDR_DB = gs_db_cdr_master_connect();
if(!$CDR_DB)
{
	echo "CDR DB error.";
	return;
}

//--- print page header stuff
if((@$_REQUEST["view"] != "print") && (@$_REQUEST["view"] != "csv"))
{
	echo "<h2>";

	if(@$MODULES[$SECTION]["icon"])
		echo "<img alt=\"\" src=\"". GS_URL_PATH_REAL . str_replace("%s", "32", $MODULES[$SECTION]["icon"]) ."\" />";

	if(count($MODULES[$SECTION]["sub"]) > 1)
		echo $MODULES[$SECTION]["title"] ." - ";

	echo $MODULES[$SECTION]["sub"][$MODULE]["title"];
	echo "</h2>\n";

	echo "<script type=\"text/javascript\" src=\"". GS_URL_PATH_REAL ."js/arrnav.js\"></script>\n";
}

//--- get request stuff
$dur_start	= trim(@$_REQUEST["dur_start"]);
$dur_end	= trim(@$_REQUEST["dur_end"]);
$caller_source	= trim(@$_REQUEST["caller_source"]);
$price		= trim(@$_REQUEST["price"]);
$privprefixonly = trim(@$_REQUEST["privprefixonly"]);
$vat_value	= trim(@$_REQUEST["vat_value"]);
$bill_base	= trim(@$_REQUEST["bill_base"]);

//--- do checks
unset($errorlist);
$errorlist = Array();

if(strlen($dur_start) <= 0) $errorlist[] = __("Kein g&uuml;ltiger Startzeitpunkt angegeben");
else
{
	$real_dur_start = normalize_date($dur_start, 0);
	if($real_dur_start == "1970-01-01") $errorlist[] = __("Kein g&uuml;ltiger Startzeitpunkt angegeben");
}

if(strlen($dur_end) <= 0) $errorlist[] = __("Kein g&uuml;ltiger Endzeitpunkt angegeben");
else
{
	$real_dur_end = normalize_date($dur_end, 1);
	if($real_dur_end == "1970-01-01") $errorlist[] = __("Kein g&uuml;ltiger Endzeitpunkt angegeben");
}

if(strlen($caller_source) <= 0) $errorlist[] = __("Keine Nebenstelle angegeben");

if(strlen($price) <= 0) $price = $billing_pricepermin_default;

if(strlen($vat_value) <= 0) $vat_value = $billing_vat;
else $vat_value = ($vat_value / 100);

$real_price = (float) str_replace(",", ".", $price);
$bill_base = (float) str_replace(",", ".", $bill_base);

if((@$_REQUEST["view"] != "print") && (@$_REQUEST["view"] != "csv"))
{

//--- show query form {
?>

<form method="get" action="<?php echo GS_URL_PATH_REAL; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:140px;"><?php echo __('Zeitraum'); ?></th>
	<th style="width:140px;"><?php echo __('Nebenstelle'); ?></th>
	<th style="width:140px;"><?php echo __("Preise in") ." ". $billing_currency ." ". __("(brutto)"); ?></th>
	<th>&nbsp;</th>
</tr>
</thead>
<tbody>
<tr>
	<td valign="top">
		Von:<br /><input type="text" name="dur_start" value="<?php echo htmlEnt($dur_start); ?>" size="10" style="width:100px;" maxlength="10" /><br />
		Bis (einschl.):<br /><input type="text" name="dur_end" value="<?php echo htmlEnt($dur_end); ?>" size="10" style="width:100px;" maxlength="10" />
	</td>
	<td valign="top">
		Nummer:<br />
		<input type="text" name="caller_source" value="<?php echo htmlEnt($caller_source); ?>" size="10" style="width:100px;" /><br />
		Gespr&auml;che:<br />
		<input type="checkbox" name="privprefixonly"<?php if($privprefixonly == "on") echo " checked=\"checked\""; ?>" />Nur Privat
	</td>
	<td valign="top">
		Sockelpreis:<br />
		<input type="text" name="bill_base" value="<?php echo htmlEnt(str_replace(".", ",", sprintf("%.02f", $bill_base))); ?>" size="10" style="width:100px;" /><br />
		Minutenpreis:<br />
		<input type="text" name="price" value="<?php echo htmlEnt(str_replace(".", ",", sprintf("%.02f", $real_price))); ?>" size="10" style="width:100px;" /><br />
		MwSt.-Satz (in %):<br />
		<input type="text" name="vat_value" value="<?php echo htmlEnt($vat_value * 100); ?>" size="10" style="width:100px;" />
	</td>
	<td valign="top" width="30">
		<button class="plain" type="submit" title="<?php echo __("Erstellen"); ?>">
			<img alt="<?php echo __("Erstellen"); ?>" src="<?php echo GS_URL_PATH_REAL; ?>crystal-svg/16/act/edit.png" />
		</button>
	</td>
</tr>

</tbody>
</table>
</form><br />
<?php
//--- } show query form

}

if(sizeof($errorlist) > 0)
{
	echo "<h3>". __("Abrechnung kann nicht erstellt werden") .":</h3>\n";
	echo "<ul>";

	foreach($errorlist as $thiserror)
	{
		echo "<li>". $thiserror ."</li>\n";
	}
	echo "</ul>\n";
}
else
{
	if((@$_REQUEST["view"] != "print") && (@$_REQUEST["view"] != "csv"))
	{
		$printview_linkstr = 
			"<a href=\"". GS_URL_PATH_REAL ."mod/". $SECTION ."_". $MODULE .".php?view=print".
			"&s=". $SECTION ."&m=". $MODULE ."&sudo=". $_REQUEST["sudo"] .
			"&dur_start=". $dur_start .
			"&dur_end=". $dur_end .
			"&caller_source=". $caller_source .
			"&bill_base=". $bill_base .
			"&price=". $price .
			"&vat_value=". (100 * $vat_value) .
			"&privprefixonly=". $privprefixonly .
			"\" target=\"_blank\">" .
			"<img alt=\"". __("Druckansicht") ."\" src=\"". GS_URL_PATH_REAL ."crystal-svg/16/act/search.png\" />&nbsp;Druckansicht</a>";

		$csvview_linkstr = 
			"<a href=\"". GS_URL_PATH_REAL ."mod/". $SECTION ."_". $MODULE .".php?view=csv".
			"&s=". $SECTION ."&m=". $MODULE ."&sudo=". $_REQUEST["sudo"] .
			"&dur_start=". $dur_start .
			"&dur_end=". $dur_end .
			"&caller_source=". $caller_source .
			"&bill_base=". $bill_base .
			"&price=". $price .
			"&vat_value=". (100 * $vat_value) .
			"&privprefixonly=". $privprefixonly .
			"\" target=\"_blank\">" .
			"<img alt=\"". __("CSV-Datei") ."\" src=\"". GS_URL_PATH_REAL ."crystal-svg/16/act/contents.png\" />&nbsp;CSV-Datei</a>";

		echo $printview_linkstr ."&nbsp;|&nbsp;". $csvview_linkstr ."<br /><br />\n";
	}
	else if(@$_REQUEST["view"] != "csv")
	{
		$header_string = __("Abrechnung Telefonverbindungen");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo @$_SESSION['isolang']; ?>" xml:lang="<?php echo @$_SESSION['isolang']; ?>">
<head><!--<![CDATA[
                Gemeinschaft
  @(_)=====(_)  (c) 2007-2008, amooma GmbH - http://www.amooma.de
 @   / ### \    Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
 @  |  ###  |   Philipp Kempgen <philipp.kempgen@amooma.de>
  @@|_______|   Peter Kozak <peter.kozak@amooma.de>
                                                      GNU GPL ]]>-->
<title><?php
	switch ($GS_INSTALLATION_TYPE) {
		case 'gpbx': echo    'GPBX'                         ; break;
		//default    : echo __('Gemeinschaft Telefon-Manager');
		default    : echo    'LocaPhone';
	}
?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH_REAL; ?>styles/original.css" />
<?php if ($GUI_ADDITIONAL_STYLESHEET = gs_get_conf('GS_GUI_ADDITIONAL_STYLESHEET')) { ?>
<link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH_REAL; ?>styles/<?php echo rawUrlEncode($GUI_ADDITIONAL_STYLESHEET); ?>" />
<?php } ?>
<link rel="shortcut icon" type="image/x-icon" href="<?php echo GS_URL_PATH_REAL; ?>favicon.ico" />
<?php
	if (array_key_exists('is_boi', $MODULES[$SECTION])
	&&  $MODULES[$SECTION]['is_boi'])
	{
		echo '<script type="text/javascript" src="', GS_URL_PATH_REAL ,'js/anti-xss.js"></script>', "\n";
	}
	
	$reverse_proxy = gs_get_conf('GS_BOI_GUI_REVERSE_PROXY');
	if (! preg_match('/^https?:\/\//', $reverse_proxy))
		$reverse_proxy = 'http://'.$reverse_proxy;
	if (subStr($reverse_proxy,-1) != '/')
		$reverse_proxy.= '/';
	
?>
<!-- for stupid MSIE: -->
<!--[if lt IE 7]><link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH_REAL; ?>styles/msie-fix-6.css" /><![endif]-->
<!--[if gte IE 7]><link rel="stylesheet" type="text/css" href="<?php echo GS_URL_PATH_REAL; ?>styles/msie-fix-7.css" /><![endif]-->
<!--[if lt IE 8]><style type="text/css">button {behavior: url("<?php echo GS_URL_PATH_REAL; ?>js/msie-button-fix.htc.php?msie-sucks=.htc");}</style><![endif]-->
<!--[if lt IE 7]><style type="text/css">img {behavior: url("<?php echo GS_URL_PATH_REAL; ?>js/pngbehavior.htc.php?msie-sucks=.htc");}</style><![endif]-->
<meta http-equiv="imagetoolbar" content="no" />
<meta http-equiv="pragma" content="no-cache" />
<meta http-equiv="cache-control" content="no-cache" />
</head>
<body>
<?php

		echo "<h2>". htmlEnt($header_string) ."</h2>\n";
	}

	$dstmatch = "(`dst` REGEXP '^[0-9]' OR `userfield` REGEXP '^[0-9]')";
	if($privprefixonly == "on") $dstmatch = "`dst` LIKE '*7*%'";

	$cdr_sql =
		"SELECT calldate, dst, userfield, duration, billsec, disposition, DATE_FORMAT(`calldate`, '%d.%m.%Y, %H:%i:%s') AS `formatteddate` ".
		"FROM `ast_cdr` ".
		"WHERE `src` = '". $caller_source ."' ".
		"AND ". $dstmatch ." ".
		"AND (calldate >= '". $real_dur_start ."' AND calldate <= '". $real_dur_end ."') ".
		"ORDER BY calldate ASC ";

	$bill_res = $CDR_DB->execute($cdr_sql);
	$bill_num = $CDR_DB->numFoundRows();

	$bill_sum = 0;
	$bill_minutes_sum = 0;
	$bill_calls_sum = 0;
	$bill_calls_sum_billed = 0;

	if($bill_num <= 0) echo "<h3>". __("Keine Einzelverbindungsdaten gefunden") .".</h3>\n";
	else
	{
		if(@$_REQUEST["view"] == "csv")
		{
			Header("Content-Type: text/comma-separated-values");
			Header("Content-Disposition: attachment; filename=\"gespraechsdaten.csv\"");

			echo "datumzeit;anrufziel;disposition;dauer;minutenpreis;preis;vat\n";
		}
		else
		{
			echo "<table cellspacing=\"1\" class=\"phonebook\">\n";
			echo "<thead>\n";
			echo "<tr>\n";
			echo "        <th style=\"width:200px;\">". __("Datum/Zeit") ."</th>\n";
			echo "        <th style=\"width:200px;\">". __("Anrufziel") ."</th>\n";
			echo "        <th style=\"width:150px;\">". __("Disposition") ."</th>\n";
			echo "        <th style=\"width:150px;\">". __("Dauer") ."</th>\n";
			echo "        <th style=\"width:120px;\">". __("Preis (netto)") ."</th>\n";
			echo "</tr>\n";
			echo "</thead>\n";
			echo "<tbody>\n";
		}

		$oddeven = 0;

		$real_price_novat = round(($real_price / (1 + $vat_value)), 5);

		while($bill_row = $bill_res->fetchRow())
		{
			$oddeven_class = ((++$oddeven % 2 == 0) ? "even" : "odd");

			$disposition_text = "";
			$bill_minutes = 0;
			$bill_thisprice = 0;
			$bill_dest = "";

			switch($bill_row["disposition"])
			{
				case "ANSWERED":
					$disposition_text = __("angenommen");
					break;
				case "NO ANSWER":
					$disposition_text = __("keine Antwort");
					break;
				case "FAILED":
					$disposition_text = __("fehlgeschlagen");
					break;
				case "BUSY":
					$disposition_text = __("besetzt");
					break;
				default:
					$disposition_text = __("unbekannt");
					break;
			}

			$bill_minutes = ceil($bill_row["billsec"] / 60);
			$bill_thisprice = ($bill_minutes * $real_price_novat);

			// work around possible dialout cdr quirk
			if($bill_row["dst"] == "s") $bill_tmpdest = $bill_row["userfield"];
			else $bill_tmpdest = $bill_row["dst"];

			if($privprefixonly == "on") $bill_dest = str_replace("*7*", "", $bill_tmpdest);
			else $bill_dest = $bill_tmpdest;

			if(substr($bill_dest, 0, 1) != "0") $bill_thisprice = 0;
			else if(substr($bill_dest, 0, 5) == "00800") $bill_thisprice = 0;

			else if(substr($bill_dest, 0, 4) == "0110") $bill_thisprice = 0;
			else if(substr($bill_dest, 0, 4) == "0112") $bill_thisprice = 0;

			$bill_calls_sum++;

			if($bill_thisprice != 0)
			{
				$bill_minutes_sum += $bill_minutes;
				$bill_sum += $bill_thisprice;
				$bill_calls_sum_billed++;
			}

			$bill_price = str_replace(".", ",", sprintf("%.05f", $bill_thisprice));

			if(@$_REQUEST["view"] == "csv")
			{
				echo "". $bill_row["formatteddate"] .";";
				echo "'". $bill_dest ."';";
				echo "". $disposition_text .";";
				echo "". sec_to_hours($bill_row["billsec"]) .";";
				echo "". str_replace(".", ",", $real_price_novat) .";";
				echo "". $bill_price .";";
				echo "". (100 * $vat_value) ."\n";
			}
			else
			{
				echo "<tr class=\"". $oddeven_class ."\">";
				echo "<td>". htmlEnt($bill_row["formatteddate"]) ."</td>";
				echo "<td>". htmlEnt($bill_dest) ."</td>";
				echo "<td>". htmlEnt($disposition_text) ."</td>";
				echo "<td>". htmlEnt(sec_to_hours($bill_row["billsec"])) ."</td>";
				echo "<td>". $billing_currency ." ". htmlEnt($bill_price) ."</td>";
				echo "</td>\n";
				echo "</tr>\n";
			}

			//echo "<pre>"; print_r($bill_row); echo "</pre>";
		}

		if(@$_REQUEST["view"] != "csv")
		{
			echo "</tbody>\n";
			echo "</table><br />\n";
		}
	}

	if(@$_REQUEST["view"] != "csv")
	{
		$bill_sum_brutto = $bill_base + ($bill_minutes_sum * $real_price);
		$bill_base_novat = round(($bill_base / (1 + $vat_value)), 5);

		echo "<table cellspacing=\"1\" class=\"phonebook\">\n";
		echo "<thead>\n";
		echo "<tr><th colspan=\"4\" style=\"width:872px;\">\n";
		echo "<span class=\"sort-col\" style=\"font-size:1.5em\">". __("Gesamt f. Nebenstelle") ." ". $caller_source .", ". __("Abrechnungszeitraum") .": ". $dur_start." - ". $dur_end ."</span>\n";
		echo "</th></tr>\n";
		echo "</thead>\n";
		echo "<tbody>\n";

		echo "<tr>";
		echo "<th align=\"right\" style=\"text-align:right; width:220px;\">". __("Verbindungen Gesamt") .":&nbsp;</th>\n";
		echo "<td class=\"r\" style=\"text-align:left; width:126px;\">". $bill_calls_sum ."</td>";
		echo "<th align=\"right\" style=\"text-align:right; width:290px;\">". __("Preis pro Gespr&auml;chsminute") ." (netto):&nbsp;</th>\n";
		echo "<td class=\"r\" style=\"font-style:italic; text-align:right; width:196px;\">". $billing_currency ." ". str_replace(".", ",", sprintf("%.5f", $real_price_novat)) ."</td>";
		echo "</tr>\n";

		echo "<tr>";
		echo "<th align=\"right\" style=\"text-align:right; width:220px;\">". __("Verbindungen berechnet") .":&nbsp;</th>\n";
		echo "<td class=\"r\" style=\"text-align:left; width:126px;\">". $bill_calls_sum_billed ."</td>";

		echo "<th align=\"right\" style=\"text-align:right; width:290px;\">". __("Sockelpreis") ." (netto):&nbsp;</th>\n";
		echo "<td class=\"r\" style=\"text-align:right; width:196px;\">". $billing_currency ." ". str_replace(".", ",", sprintf("%.5f", $bill_base_novat)) ."</td>";
		echo "</tr>\n";

		echo "<tr>";
		echo "<th align=\"right\" style=\"text-align:right; width:220px;\">". __("Summe Verbindungsminuten") .":&nbsp;</th>\n";
		echo "<td class=\"r\" style=\"text-align:left; width:126px;\">". $bill_minutes_sum ."</td>";

		echo "<th align=\"right\" style=\"text-align:right; width:290px;\">". __("Summe Verbindungsentgelte") ." (netto):&nbsp;</th>\n";
		echo "<td class=\"r\" style=\"text-align:right; width:196px;\">". $billing_currency ." ". str_replace(".", ",", sprintf("%.05f", $bill_sum)) ."</td>";
		echo "</tr>\n";

		echo "<tr>";
		echo "<th colspan=\"3\" align=\"right\" style=\"text-align:right;\">". __("Gesamt") ." (netto):&nbsp;</th>\n";
		echo "<td class=\"r\" style=\"text-align:right; width:196px; font-weight:bold; \">". $billing_currency ." ". str_replace(".", ",", sprintf("%.2f", round(($bill_base_novat + $bill_sum), 2))) ."</td>";
		echo "</tr>\n";

		echo "<tr>";
		echo "<th colspan=\"3\" align=\"right\" style=\"text-align:right;\">". __("zzgl.") ." ". (100 * $vat_value) ."% ". __("MwSt.") .":&nbsp;</th>\n";
		echo "<td class=\"r\" style=\"text-align:right; width:196px; font-weight:bold; \">". $billing_currency ." ". str_replace(".", ",", sprintf("%.2f", round(($bill_sum_brutto - ($bill_base_novat + $bill_sum)), 2))) ."</td>";
		echo "</tr>\n";

		echo "<tr>";
		echo "<th colspan=\"3\" align=\"right\" style=\"text-align:right;\">". __("Gesamt") ." (brutto):&nbsp;</th>\n";
		echo "<td class=\"r\" style=\"text-align:right; width:196px; font-weight:bold; \">". $billing_currency ." ". str_replace(".", ",", sprintf("%.2f", round($bill_sum_brutto, 2))) ."</td>";
		echo "</tr>\n";

		echo "</tbody></table>\n";
	}
}

?>
