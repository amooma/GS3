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

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";

$edit     = (int)trim(@$_REQUEST['edit'  ]);
$save     = (int)trim(@$_REQUEST['save'  ]);
$per_page = (int)GS_GUI_NUM_RESULTS;
$page     =      (int)@$_REQUEST['page'  ] ;
$queue    =      trim(@$_REQUEST['queue' ]);
$maxlen   = (int)trim(@$_REQUEST['maxlen']);
$title    =      trim(@$_REQUEST['title' ]);
$hostid   = (int)trim(@$_REQUEST['host'  ]);
$delete   =      trim(@$_REQUEST['delete']);


if ($delete) {
	$ret_val = gs_queue_del( $delete );
	if (isGsError( $ret_val )) echo $ret_val->getMsg();
}

if ($queue) {
	$ret_val = gs_queue_add( $queue, $title, $maxlen, $hostid );
	if (isGsError( $ret_val )) echo $ret_val->getMsg();
}	

if ($save) {
	if ($maxlen < 0) $maxlen = 0;
	if ($maxlen > 255) $maxlen = 255;
	
	$sql_query = 'UPDATE `ast_queues` 
		SET `_title`=\''. $DB->escape($title) .'\',
		`maxlen`=\''. $DB->escape($maxlen) .'\'
		WHERE `_id`='. $save;
	$rs = $DB->execute($sql_query);
}



$sql_query = 'SELECT `id`, `host`
FROM `hosts`
ORDER BY `id` ASC';
$rs = $DB->execute($sql_query);

if (@$rs) {
	while ($r = $rs->fetchRow()) {
		$hosts[$r['id']] = $r['host'];
	}
}


$sql_query = 'SELECT SQL_CALC_FOUND_ROWS 
	`q`.`_id` `id`, `q`.`name`, `q`.`_title` `title`, `q`.`maxlen`, `q`.`_host_id` `host_id`,
	COUNT(`m`.`_user_id`) `num_members`
FROM
	`ast_queues` `q` LEFT JOIN
	`ast_queue_members` `m` ON (m._queue_id=q._id)
GROUP BY `q`.`_id`
ORDER BY `q`.`name`
LIMIT '. ($page*(int)$per_page) .','. (int)$per_page;

$rs = $DB->execute($sql_query);

$num_total = @$DB->numFoundRows();
$num_pages = ceil($num_total / $per_page);

?>	

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:30px;"><?php echo __('ID'); ?></th>
	<th style="width:80px;"><?php echo __('Queue'); ?></th>
	<th style="width:150px;"><?php echo __('Bezeichnung'); ?></th>
	<th style="width:80px;"><?php echo __('L&auml;nge'); ?></th>
	<th style="width:150px;"><?php echo __('Host'); ?></th>
	<th style="width:30px;"><?php echo __('Mitglieder'); ?></th>
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
		
		if ($edit == $r['id']){
			echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
			echo gs_form_hidden($SECTION, $MODULE), "\n";
			echo '<input type="hidden" name="page" value="', htmlEnt($page), '" />', "\n";
			echo '<input type="hidden" name="save" value="', $r['id'] , '" />', "\n";
			
			echo '<td>', htmlEnt($r['id']);
			echo '</td>';
			
			echo '<td>', htmlEnt($r['name']);
			echo '</td>';
			
			echo '<td>';	
			echo '<input type="text" name="title" value="'.htmlEnt($r['title']).'" size="25" maxlength="40" />';	
			echo '</td>';
			
			echo '<td>';
			echo '<input type="text" name="maxlen" value="'.htmlEnt($r['maxlen']).'" size="5" maxlength="3" />';	
			echo '</td>';
			
			echo '<td>', $r['host_id'].' ('.@$hosts[$r['host_id']].') </td>';
			
			echo '<td>', htmlEnt($r['num_members']),'</td>';
			
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
			
			echo '<td>', htmlEnt($r['name']);
			echo '</td>';
			
			echo '<td>', htmlEnt($r['title']),'</td>';
			
			echo '<td>', htmlEnt($r['maxlen']);
			echo '</td>';
			
			echo '<td>', $r['host_id'].' ('.@$hosts[$r['host_id']].') </td>';
			echo '<td>', htmlEnt($r['num_members']),'</td>';	
			
			echo "<td>\n";
			
			echo '<a href="', gs_url($SECTION, $MODULE), '&amp;edit=', $r['id'], '&amp;page='.$page.'" title="', __('bearbeiten'), '"><img alt="', __('bearbeiten'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
			
			echo '<a href="', gs_url($SECTION, $MODULE), '&amp;delete=', $r['name'], '&amp;page='.$page.'" title="', __('l&ouml;schen'), '"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
			
		}
		
		echo "</td>\n";
		echo '</tr>', "\n";
	}
}

?>
<tr>
<?php

if (!$edit) {
	
	echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
	echo gs_form_hidden($SECTION, $MODULE), "\n";
?>
	<td>
		
	</td>
	<td>
		<input type="text" name="queue" value="" size="5" maxlength="5" />
	</td>
	<td>
		<input type="text" name="title" value="" size="25" maxlength="40" />
	</td>
	<td>
		<input type="text" name="maxlen" value="" size="5" maxlength="3" />
	</td>
	<td>
<?php
		echo "<select name=\"host\" > ";
		foreach ($hosts as $key => $host) {
			echo '<option value="'.$key."\">$key ($host)</option>\n";
			
		}
		echo "</select></td>\n";
?>
	</td>
	
	<td></td>
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

