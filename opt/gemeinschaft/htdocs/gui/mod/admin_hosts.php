<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 2364 $
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
require_once( GS_DIR .'inc/extension-state.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";

$edit_host   = (int)trim(@$_REQUEST['edit']);
$save_host   = (int)trim(@$_REQUEST['save']);
$per_page = (int)GS_GUI_NUM_RESULTS;
$page = (int)@$_REQUEST['page'];
$host   = trim(@$_REQUEST['host']);
$hostid  = (int)trim(@$_REQUEST['hostid']);
$comment  = trim(@$_REQUEST['comment']);

$delete_host  = (int)trim(@$_REQUEST['delete']);

if ($host) {
	if ($save_host) {
		$sql_query = 'UPDATE `hosts` 
		SET `host`=\''. $DB->escape($host) .'\',
		`comment`=\''. $DB->escape($comment) .'\'
		WHERE `id`='. $save_host;
		$rs = $DB->execute($sql_query);

	} else {
		if ($hostid == 0) $hostid='NULL';
		$sql_query = 'INSERT INTO `hosts`
		(`id`, `host`, `comment`) 
		VALUES ('.$hostid.', \''. $DB->escape($host) .'\' ,  \''. $DB->escape($comment) .'\' ) ';
		$rs = $DB->execute($sql_query);
	}
}

if ($delete_host) {
	$sql_query = 'DELETE from `hosts` 
	WHERE `id`='. $delete_host;
	$rs = $DB->execute($sql_query);
}


$sql_query = 'SELECT SQL_CALC_FOUND_ROWS `id`, `host`, `comment`
FROM `hosts`
ORDER BY `id` ASC
LIMIT '. ($page*(int)$per_page) .','. (int)$per_page;

$rs = $DB->execute($sql_query);

$num_total = @$DB->numFoundRows();
$num_pages = ceil($num_total / $per_page);

?>	

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:50px;"><?php echo __('Nummer'); ?></th>
	<th style="width:200px;"><?php echo __('Host IP'); ?></th>
	<th style="width:200px;"><?php echo __('Kommentar'); ?></th>
	<th style="width:80px;">
<?php
	echo ($page+1), ' / ', $num_pages, "&nbsp; \n";

	if ($page > 0) {
		echo
		'<a href="',  gs_url($SECTION, $MODULE), '&amp;page=', ($page-1), '" title="', __('zur&uuml;ckbl&auml;ttern'), '" id="arr-prev">',
		'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous.png" />',
		'</a>', "\n";
	} else {
		echo
		'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous_notavail.png" />', "\n";
	}
	
	if ($page < $num_pages-1) {
		echo
		'<a href="',  gs_url($SECTION, $MODULE), '&amp;page=', ($page+1), '" title="', __('weiterbl&auml;ttern'), '" id="arr-next">',
		'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/next.png" />',
		'</a>', "\n";
	} else {
		echo
		'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/next_notavail.png" />', "\n";
	}
	
?>
		</th>
</tr>
</thead>
<tbody>

<?php

$sudo_url = (@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
	? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);

if (@$rs) {
	$i = 0;
	while ($r = $rs->fetchRow()) {
		echo '<tr class="', ((++$i % 2) ? 'odd':'even'), '">', "\n";

		if ($edit_host == $r['id']){
			echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
			echo gs_form_hidden($SECTION, $MODULE), "\n";
			echo '<input type="hidden" name="page" value="', htmlEnt($page), '" />', "\n";
			echo '<input type="hidden" name="save" value="', $r['id'] , '" />', "\n";
			echo '<td>', htmlEnt($r['id']);
			echo '</td>';
	
			echo '<td>';
			echo '<input type="text" name="host" value="'.htmlEnt($r['host']).'" size="20" maxlength="25" />';
			echo '</td>';
	
			echo '<td>';	
			echo '<input type="text" name="comment" value="'.htmlEnt($r['comment']).'" size="25" maxlength="25" />';	
			echo '</td>';	
			echo '<td>';
			echo '<button type="submit" title="', __('Speichern'), '" class="plain">';
			echo '<img alt="', __('Speichern') ,'" src="',GS_URL_PATH,'crystal-svg/16/act/filesave.png" />
			</button>'."\n";
			echo "&nbsp;\n";
			echo '<button type="cancel" title="', __('Abbrechen'), '" class="plain">';
			echo '<img alt="', __('Abbrechen') ,'" src="',GS_URL_PATH,'crystal-svg/16/act/cancel.png" />
			</button>'."\n";
			echo '</form>';
		
		} else {
			
			echo '<td>', htmlEnt($r['id']);
			echo '</td>';
	
			echo '<td>', htmlEnt($r['host']);
			echo '</td>';
	
			echo '<td>', htmlEnt($r['comment']),'</td>';	
			//echo '<td>', htmlEnt($r['hnr']), '</td>';	
			echo '<td>';
			
			echo '<a href="', gs_url($SECTION, $MODULE), '&amp;edit=', $r['id'], '&amp;page='.$page.'" title="', __('bearbeiten'), '"><img alt="', __('bearbeiten'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
			
			echo '<a href="', gs_url($SECTION, $MODULE), '&amp;delete=', $r['id'], '&amp;page='.$page.'" title="', __('l&ouml;schen'), '"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
		
		}

		echo "</td>\n";
		echo '</tr>', "\n";
		
	}
}

?>
<tr>
<?php

if (!$edit_host) {

	echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
	echo gs_form_hidden($SECTION, $MODULE), "\n";
?>
	<td>
		<input type="text" name="hostid" value="" size="5" maxlength="25" />
	</td>
	<td>
		<input type="text" name="host" value="" size="20" maxlength="25" />
	</td>
	<td>
		<input type="text" name="comment" value="" size="25" maxlength="45" />
	</td>
	<td>
		<button type="submit" title="<?php echo __('Host anlegen'); ?>" class="plain">
			<img alt="<?php echo __('Speichern'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
		</button>
	</td>

	</form>
<?php
}
?>

</tr>

</tbody>
</table>

