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
if ((float)PHP_VERSION < 5.0) {
	# to communicate with HylaFax inc/cn_hylafax.php uses ftp_raw(),
	# which is not available in PHP < 5
	echo 'PHP &gt;= 5 required.';
	return;
}
require_once( GS_DIR .'inc/cn_hylafax.php' );
include_once( GS_DIR .'inc/util.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_pin_get.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


function email_by_username( $user_name )
{
	global $DB;
	
	$query = 'SELECT `email` FROM `users` WHERE
	(`user` = _utf8\''. $DB->escape($user_name) .'\' COLLATE utf8_unicode_ci)';
	return $DB->executeGetOne($query);
}


$user_id    = @$_SESSION['sudo_user']['id'];
$fax_job_id = 0;
$per_page   = 10;
$tsi        = trim(@$_REQUEST['tsi']);
$faxnumber  = trim(@$_REQUEST['faxnumber']);
$resolution = (int) trim(@$_REQUEST['res']);
$document   = trim(@$_REQUEST['doc']);

if (is_array($_FILES)
&&  @array_key_exists('file', @$_FILES)
&&  @$_FILES['file']['error'] == 0
&&  @$_FILES['file']['size'] > 0) {
	$fax_job_id = fax_send(
		$user_id,
		$_SESSION['sudo_user']['name'],
		$faxnumber,
		$tsi,
		$_FILES['file']['tmp_name'],
		email_by_username($_SESSION['sudo_user']['name']),
		$resolution,
		gs_user_pin_get($_SESSION['sudo_user']['name'])
	);
	$file_ok = true;
} else {
	$file_ok = false;
}

if (($document) && ($resolution)) {
	$fax_job_id = fax_send(
		$user_id,
		$_SESSION['sudo_user']['name'],
		$faxnumber,
		$tsi,
		'/docq/'.$document,
		email_by_username($_SESSION['sudo_user']['name']),
		$resolution,
		gs_user_pin_get($_SESSION['sudo_user']['name'])
	);
}




echo '<form enctype="multipart/form-data" method="post" action="', GS_URL_PATH ,'">', "\n";
echo gs_form_hidden($SECTION,$MODULE);

?>

<table cellspacing="1">
<tbody>
<tr>
	<th style="width:150px;"></th>
	<th style="width:350px;"></th>
</tr>

<?php

echo "<tr>\n";
echo '<th>', __('Senderkennung'), '</th>' ,"\n";
echo '<td>';
echo '<select name="tsi">' ,"\n";

if (gs_get_conf('GS_FAX_TSI_PREFIX'))
	echo '<option value="', gs_get_conf('GS_FAX_TSI_PREFIX'), $_SESSION['sudo_user']['info']['ext'] ,'">', gs_get_conf('GS_FAX_TSI_PREFIX'), $_SESSION['sudo_user']['info']['ext'] ,'</option>' ,"\n";

$fax_tsis_global = explode(',',gs_get_conf('GS_FAX_TSI'));
foreach ($fax_tsis_global as $fax_tsi_global) {
	echo '<option value="', trim($fax_tsi_global) ,'">', trim($fax_tsi_global) ,'</option>' ,"\n";
}
echo "</select>\n";
echo "</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo '<th>', __('Faxnummer'), '</th>' ,"\n";
echo '<td>';
echo '<input name="faxnumber" type="text" value="'.$faxnumber.'" style="width:250px;" />';
echo "</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo '<th>', __('Aufl&ouml;sung'), '</th>' ,"\n";
echo '<td>';
echo '<select name="res">' ,"\n";
echo '<option value="98">', __('Normale Aufl&ouml;sung'),': 98 lpi' ,'</option>'."\n";
echo '<option value="196">', __('Hohe Aufl&ouml;sung'),': 196 lpi' ,'</option>'."\n";
echo '<option value="392">', __('Sehr hohe Aufl&ouml;sung'),': 392 lpi' ,'</option>'."\n";
echo "</select>\n";
echo "</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo '<th>', __('Datei'), '</th>' ,"\n";
echo '<td>';
if ($document) echo '<input name="doc" type="text" value="'.$document.'"/>';
else echo '<input name="file" type="file" />';
echo "</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo '<th>&nbsp;</th>' ,"\n";
echo '<th style="width:350px;">';
if ($file_ok) {
	echo baseName($_FILES['file']['name']) ," (", round( (int)$_FILES['file']['size'] / 1024 ) ," kB)\n";
	if ($fax_job_id) echo '', __('wird gesendet.'), ' ID: ', $fax_job_id;
	else             echo ' - ', __('Fehler beim Senden');
}
echo "</th>\n";
echo "</tr>\n";

echo "<tr>\n";
echo '<th>&nbsp;</th>' ,"\n";
echo '<td>';
echo '<button type="submit" title="', __('Senden') ,'" class="plain">';
echo '<img src="', GS_URL_PATH ,'crystal-svg/16/act/fileprint.png" alt="', __('Senden') ,'" />';
echo '</button>';
echo "</td>\n";
echo "</tr>\n";

echo "</tbody>\n";
echo "</table>\n";

echo '</form>';

?>
