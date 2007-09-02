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

######################################################
##
##   ALL STRINGS IN HERE NEED TO BE TRANSLATED!
##
######################################################

defined('GS_VALID') or die('No direct access.');
require_once( GS_DIR .'inc/cn_hylafax.php' );
include_once( GS_DIR .'inc/util.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt="" src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


function username_prep($user_name) {
	$user_name_str = strtr(trim($user_name), array(
			'^' => '',
			'\\' => '',
			'>' => '',
			'<' => '',
			'\`' => '',
			'\'' => '',
			'"' => ''
		));

	return $user_name_str;

}

function uid_by_name ($user_name) {
	global $DB;

	$sql_query = 'SELECT `id` FROM `users`
WHERE
	( `user` =_utf8\''. $DB->escape($user_name) .'\' COLLATE utf8_unicode_ci ) ';

	$user_id = $DB->executeGetOne($sql_query);

	return $user_id;
}

function email_by_name ($user_name) {
	global $DB;

	$sql_query = 'SELECT `email` FROM `users`
WHERE
	( `user` =_utf8\''. $DB->escape($user_name) .'\' COLLATE utf8_unicode_ci ) ';

	$user_email = $DB->executeGetOne($sql_query);

	return $user_email;
}


$user_id = @$_SESSION['sudo_user']['id'];
$fax_job_id = 0;
//$user_id = $_SESSION['user_id'];
$per_page = 10;
$tsi = trim(@$_REQUEST['tsi']);
$faxnumber   = trim(@$_REQUEST['faxnumber']);
$resolution   = (int) trim(@$_REQUEST['res']);

if ((array_key_exists('file',$_FILES)) && ($_FILES['file']['error'] == 0)) {
	$fax_job_id = fax_send($user_id, $_SESSION['sudo_user']['name'], $faxnumber, $tsi, $_FILES['file']['tmp_name'],email_by_name($_SESSION['sudo_user']['name']),$resolution);

	//print_r($_FILES);
}

echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";

?>


<?php

echo '<form enctype="multipart/form-data" method="post" action="'.GS_URL_PATH.'">', "\n";
echo gs_form_hidden($SECTION,$MODULE);

echo '<table cellspacing="1">', "\n";
echo '<tbody>', "\n";
echo "<tr>\n";
echo '<th style="width:150px;">';
echo "";
echo "</th>\n";
echo '<th style="width:350px;">';

echo "</th>\n";
echo "</tr>\n";	

echo "<tr>\n";
echo '<th>';
echo "Senderkennung";
echo "</th>\n";
echo '<td>';
echo '<select name="tsi"> style="width:250px;"'."\n";
echo '<option value="'.GS_FAX_TSI_PREFIX.$_SESSION['sudo_user']['info']['ext'].'">'.GS_FAX_TSI_PREFIX.$_SESSION['sudo_user']['info']['ext'].'</option>'."\n";

$fax_tsis_global = explode(",",GS_FAX_TSI);

foreach ($fax_tsis_global as $fax_tsi_global) {
	echo '<option value="'.trim($fax_tsi_global).'">'.trim($fax_tsi_global).'</option>'."\n";
}
echo "</select>\n";
//echo '<input name="tsi" type="text" style="width:250px;">';
echo "</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo '<th>';
echo "Faxnummer";
echo "</th>\n";
echo '<td>';
echo '<input name="faxnumber" type="text" style="width:250px;">';
echo "</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo '<th>';
echo "Aufl&ouml;sung";
echo "</th>\n";
echo '<td>';
echo '<select name="res"> style="width:250px;"'."\n";
echo '<option value="98">Normal</option>'."\n";
echo '<option value="196">Hoch</option>'."\n";
//echo '<option value="298">Sehr hoch</option>'."\n";
echo "</select>\n";
echo "</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo '<th>';
echo "Datei";
echo "</th>\n";
echo '<td>';
echo '<input name="file" type="file" style="width:350px;">';
echo "</td>\n";
echo "</tr>\n";

echo "<tr>\n";
echo '<th style="width:150px;">';
echo "Upload";
echo "</th>\n";
echo '<th style="width:350px;">';
if ((array_key_exists('file',$_FILES)) && ($_FILES['file']['error'] == 0))
	echo $_FILES['file']['name']." (".round( (int)$_FILES['file']['size'] / 1024 )." kb)\n";
if ($fax_job_id) echo " wird gesendet. ID: $fax_job_id \n";
echo "</th>\n";
echo "</tr>\n";


echo "<tr>\n";
echo '<th>';
echo "</th>\n";
echo '<td>';
echo '<button type="submit" title="Senden" class="plain">';
echo '<img src="'.GS_URL_PATH.'crystal-svg/16/act/fileprint.png" />';
echo '</button>';
echo '<button type="reset" title="r&uuml;ckg&auml;ngig" class="plain">';
echo '<img alt="r&uuml;ckg&auml;ngig" src="', GS_URL_PATH, 'crystal-svg/16/act/cancel.png" />';
echo '</button>';

echo "</td>\n";
echo "</tr>\n";

echo "</tbody>\n";
echo "</table>\n";


echo '</form>';

?>



<?php
/*
echo "<pre>\n";
var_dump($_SESSION);
echo $_SESSION['sudo_user']['info']['ext'];
echo "</pre>\n";
*/
?>
