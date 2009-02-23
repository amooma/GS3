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


function username_prep( $user_name )
{
	return strTr(trim($user_name), array(
		'^'  => '',
		'\\' => '',
		'>'  => '',
		'<'  => '',
		'\`' => '',
		'\'' => '',
		'"'  => ''
	));
}


$per_page = 10;
$page     = (int)@$_REQUEST['page'];
$delete   = trim(@$_REQUEST['delete']);

if ($delete) {
	fax_delete_job($delete);
}

echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";

?>


<table cellspacing="1">
<thead>
<tr>

<?php

$jobs_done = fax_get_jobs_done($_SESSION['sudo_user']['name'], gs_user_pin_get($_SESSION['sudo_user']['name']));
$recdate = array();
$jobid   = array();

if (is_array($jobs_done)) {
	foreach ($jobs_done as $key => $row) {
		$recdate[$key] = $row[32];
		$jobid  [$key] = $row[9];
	}
	
	@array_multisort($recdate, SORT_DESC, $jobid, SORT_ASC, $jobs_done);
	
	unset($recdate);
	unset($jobid);
	
	$jobs_done_count = count($jobs_done);
	
} else $jobs_done_count = 0;

$num_pages = ceil($jobs_done_count / $per_page);
$mod_url = gs_url($SECTION, $MODULE, null, 'id=');


?>
	<th style="width:140px;"><?php echo __('Datum'); ?></th>
	<th style="width:20px;"><?php echo __('Job'); ?></th>
	<th style="width:20px;"><?php echo __('Empf&auml;nger'); ?></th>
	<th style="width:20px;"><?php echo __('Seiten'); ?></th>
	<th style="width:20px;"><?php echo __('Aufl&ouml;sung'); ?></th>
	<th style="width:20px;"><?php echo __('Vers.'); ?></th>
	<th style="width:200px;"><?php echo __('Fehler'); ?></th>
	<th style="width:80px;">
<?php
if ($page > 0) {
	echo
	'<a href="', $mod_url, '&amp;page=',($page-1),'" title="', __('zur&uuml;ckbl&auml;ttern'), '" id="arr-prev">',
	'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous.png" />',
	'</a>', "\n";
} else {
	echo
	'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous_notavail.png" />', "\n";
}
if ($page < $num_pages-1) {
	echo
	'<a href="', $mod_url, '&amp;page=', ($page+1),'" title="', __('weiterbl&auml;ttern'), '" id="arr-next">',
	'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/next.png" />',
	'</a>', "\n";
} else {
	echo
	'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/next_notavail.png" />', "\n";
}
echo ($page+1),'/',$num_pages;
?>
	</th>
</tr>
</thead>
<tbody>

<?php

for ($i=($page*$per_page); $i < ($per_page*$page)+$per_page; $i++) {
	
	if ($i < $jobs_done_count) {
		echo '<tr class="', (($i%2==0) ? 'odd':'even'), '">', "\n";
		
		echo "<td>", date('d.m.y H:i:s', $jobs_done[$i][32]) ,"</td>\n";
		echo "<td>", $jobs_done[$i][9] ,"</td>\n";
		echo "<td>", $jobs_done[$i][4] ,"</td>\n";
		echo "<td>", $jobs_done[$i][13] ,' / ', $jobs_done[$i][22] ,"</td>\n";
		echo "<td>", $jobs_done[$i][15] ," lpi</td>\n";
		echo "<td>", $jobs_done[$i][3] ,"</td>\n";
		//echo "<td>", $jobs_done[$i][30] ,"</td>\n";
		echo "<td>", $jobs_done[$i][16] ,"</td>\n";
		echo "<td>\n";
		echo '<a href="',$mod_url,'&amp;delete=',$jobs_done[$i][9],'&amp;page=',$page,'" title="', __('l&ouml;schen'), '"><img alt="', __('l&ouml;schen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
		echo "</td>\n";	
		echo "</tr>\n";
		
		echo "</tr>\n";
	} 
}

?>

</tbody>
</table>

