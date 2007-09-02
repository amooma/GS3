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

defined('GS_VALID') or die('No direct access allowed.');
require_once( GS_DIR .'inc/cn_hylafax.php' );

function sec_to_hours($sec)
{
	$hours = sprintf('%d:%02d:%02d',
		$sec / 3600 % 24,
		$sec / 60 % 60,
		$sec % 60
	);
	return $hours;
}

function username_prep($user_name)
{
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

$per_page = 10;
$page = (int)@$_REQUEST['page'];
$delete   = trim(@$_REQUEST['delete']);

if ($delete) fax_delete_file('/recvq/'.$delete);

echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";

?>


<h2>Empfangen</h2>


<div class="userlist">


<table cellspacing="1" class="userlist">
<thead>
<tr>


<?php

$jobs_rec = fax_get_jobs_rec();

foreach ($jobs_rec as $key => $row) {
	if ($row[11] == $_SESSION['sudo_user']['name']) { 
    		$recdate[$key]  = $row[18];
    		$jobid[$key] = $row[4];
	} else {
		unset($jobs_rec[$key]);

	}
}

array_multisort($recdate, SORT_DESC, $jobid, SORT_ASC, $jobs_rec);

unset($recdate);
unset($jobid);



$jobs_rec_count = count($jobs_rec);
$num_pages = ceil($jobs_rec_count / $per_page);
$mod_url = gs_url($SECTION, $MODULE).'&amp;id=';


?>
	<th style="width:140px;">
		Datum
	</th>
	<th style="width:20px;">
		Sender
	</th>
	<th style="width:20px;">
		Dauer
	</th>
	<th style="width:20px;">
		Gr&ouml;&szlig;e
	</th>
	<th style="width:20px;">
		Seiten
	</th>
	<th style="width:20px;">
		Aufl&ouml;sung
	</th>
	<th style="width:20px;">
		Bps
	</th>
	<th style="width:80px;">
<?php
if ($page > 0) {
		echo
		'<a href="', $mod_url, '&amp;page=',($page-1),'" title="zur&uuml;ckbl&auml;ttern" id="arr-prev">',
		'<img alt="zur&uuml;ck" src="', GS_URL_PATH, 'crystal-svg/16/act/previous.png" />',
		'</a>', "\n";
	} else {
		echo
		'<img alt="zur&uuml;ck" src="', GS_URL_PATH, 'crystal-svg/16/act/previous_notavail.png" />', "\n";
	}
	if ($page < $num_pages-1) {
		echo
		'<a href="', $mod_url, '&amp;page=', ($page+1),'" title="weiterbl&auml;ttern" id="arr-next">',
		'<img alt="weiter" src="', GS_URL_PATH, 'crystal-svg/16/act/next.png" />',
		'</a>', "\n";
	} else {
		echo
		'<img alt="weiter" src="', GS_URL_PATH, 'crystal-svg/16/act/next_notavail.png" />', "\n";
	}
	echo $page+1,"/$num_pages";

	echo "</th>\n";
?>
	
</tr>
</thead>
<tbody>

<?php

$rs=1;

//@$_SESSION['sudo_user']['id'];


for ($i=($page*$per_page); $i < ($per_page*$page)+$per_page; $i++) {

	if ($i < $jobs_rec_count) {
	echo '<tr class="', (($i % 2 == 0) ? 'even':'odd'), '">', "\n";
		
		echo "<td>".date("d.m.y H:i:s",$jobs_rec[$i][18])."</td>\n";
		if ($jobs_rec[$i][15]) echo "<td> ".$jobs_rec[$i][15]." </td>\n";
		else echo "<td> ".$jobs_rec[$i][7]." </td>\n";
		echo "<td> ".$jobs_rec[$i][5]." </td>\n";
		echo "<td> ".round($jobs_rec[$i][10] / 1024)." kb </td>\n";
		echo "<td> ".$jobs_rec[$i][12]." </td>\n";
		echo "<td> ".$jobs_rec[$i][14]." lpi</td>\n";
//		echo '<td> <a href="faxdown.php?file='.$jobs_rec[$i][4].'">';
//		echo $jobs_rec[$i][4]."</a> </td>\n";
		echo "<td> ".$jobs_rec[$i][1]." </td>\n";
		echo "<td>\n";
		echo '<a href="',$mod_url,'&amp;delete=',$jobs_rec[$i][4],'&amp;page=',$page,'" title="entfernen"><img alt="entfernen" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
		echo ' &nbsp; <a href="faxdown.php?file='.$jobs_rec[$i][4].'">';
		echo '<img alt="Fax anzeigen" src="', GS_URL_PATH, 'crystal-svg/16/app/pdf.png" /></a>'."\n";
		echo "</td>\n";	
		echo "</tr>\n";
	echo "</tr>\n";
	} 
}


?>

</tbody>
</table>
</div>






