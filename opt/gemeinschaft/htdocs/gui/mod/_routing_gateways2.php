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

die();


defined('GS_VALID') or die('No direct access.');

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";

$types = Array("zap", "sip");

$edit     = (int)trim(@$_REQUEST['edit'   ]);
$save     = (int)trim(@$_REQUEST['save'   ]);
$per_page = (int)GS_GUI_NUM_RESULTS;
$page     =      (int)@$_REQUEST['page'   ] ;
$type     =      trim(@$_REQUEST['type'   ]);
$group    = (int)trim(@$_REQUEST['group'  ]);
$title    =      trim(@$_REQUEST['title'  ]);
$name     =      trim(@$_REQUEST['name'   ]);
$dialstr  =      trim(@$_REQUEST['dialstr']);
$allow_in =     (trim(@$_REQUEST['in'     ]) ? 1 : 0);
$allow_out=     (trim(@$_REQUEST['out'    ]) ? 1 : 0);
$delete   = (int)trim(@$_REQUEST['delete' ]);

if ($delete) {
	$sql_query =
'DELETE FROM `gates`
WHERE `id`='.$delete;
	$DB->execute($sql_query);
}

if ($save) {
	$sql_query =
'UPDATE `gates` SET
	`title`=\''. $DB->escape($title) .'\',
	`allow_in`='. $allow_in .',
	`allow_out`='. $allow_out .',
	`type`=\''. $DB->escape($type) .'\',
	`name`=\''. $DB->escape($name) .'\',
	`dialstr`=\''. $DB->escape($dialstr) .'\',
	`grp_id`='. $group .'
WHERE `id`='. $save;
	$DB->execute($sql_query);
} else {
	if ($name && $title) {
		$sql_query =
'INSERT INTO `gates` (
	`id`,
	`grp_id`,
	`type`,
	`name`,
	`title`,
	`allow_out`,
	`allow_in`,
	`dialstr`
) VALUES (
	NULL,
	'.$group.',
	\''.$DB->escape($type).'\',
	\''.$DB->escape($name).'\',
	\''.$DB->escape($title).'\',
	'.$allow_out.',
	'.$allow_in.',
	\''.$DB->escape($dialstr).'\'
)';
		$DB->execute($sql_query);
	}
}



$sql_query =
'SELECT `id`, `title`
FROM `gate_grps`
ORDER BY `id` ASC';
$rs = $DB->execute($sql_query);

if (@$rs) {
	while ($r = $rs->fetchRow()) {
		$gates[$r['id']] = $r['title'];
	}
}

$sql_query =
'SELECT SQL_CALC_FOUND_ROWS 
	`g`.`id`, `g`.`type`, `g`.`name`, `g`.`title`, `g`.`allow_out`, `g`.`allow_in`, `g`.`dialstr`,
	`gg`.`title` `gtitle`, `gg`.`type` `gtype`, `gg`.`id` `gid`
FROM
	`gates` `g` JOIN
	`gate_grps` `gg` ON (`gg`.`id`=`g`.`grp_id`)
LIMIT '. ($page*(int)$per_page) .','. (int)$per_page;

$rs = $DB->execute($sql_query);

$num_total = @$DB->numFoundRows();
$num_pages = ceil($num_total / $per_page);

?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:80px;"><?php echo __('Gateway'); ?></th>
	<th style="width:80px;"><?php echo __('Bezeichnung'); ?></th>
	<th style="width:40px;"><?php echo __('Typ'); ?></th>
	<th style="width:80px;"><?php echo __('W&auml;hlstring'); ?></th>
	<th style="width:30px;"><?php echo __('Eing.'); ?></th>
	<th style="width:30px;"><?php echo __('Ausg.'); ?></th>
	<th style="width:30px;"><?php echo __('Gruppe'); ?></th>
	<th style="width:80px;">
<?php
	echo ($page+1), ' / ', $num_pages, "&nbsp; \n";
	
	if ($page > 0) {
		echo
		'<a href="', gs_url($SECTION, $MODULE, null, 'page='.($page-1)), '" title="', __('zur&uuml;ckbl&auml;ttern'), '" id="arr-prev">',
		'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous.png" />',
		'</a>', "\n";
	} else {
		echo
		'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous_notavail.png" />', "\n";
	}
	
	if ($page < $num_pages-1) {
		echo
		'<a href="', gs_url($SECTION, $MODULE, null, 'page='.($page+1)), '" title="', __('weiterbl&auml;ttern'), '" id="arr-next">',
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

if (@$rs) {
	$i = 0;
	while ($r = $rs->fetchRow()) {
		echo '<tr class="', ((++$i % 2) ? 'odd':'even'), '">', "\n";
		
		if ($edit == $r['id']) {
			echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
			echo gs_form_hidden($SECTION, $MODULE), "\n";
			echo '<input type="hidden" name="page" value="', htmlEnt($page), '" />', "\n";
			echo '<input type="hidden" name="save" value="', $r['id'] , '" />', "\n";
			
			echo '<td>';
			echo '<input type="text" name="name" value="', htmlEnt($r['name']) ,'" size="20" maxlength="40" />';
			echo '</td>',"\n";
			
			echo '<td>';
			echo '<input type="text" name="title" value="', htmlEnt($r['title']) ,'" size="20" maxlength="40" />';
			echo '</td>',"\n";
			
			echo '<td>';
			echo '<select name="type">',"\n";
			foreach ($types as $key => $type) {
				echo '<option value="', htmlEnt($type) ,'"', (($r['type']==$type) ? ' selected="selected"' : ''), '>', htmlEnt($type) ,'</option>',"\n";
			}
			echo '</select>',"\n";
			echo '</td>',"\n";
			
			echo '</td>';
			echo '<td>';
			echo '<input type="text" name="dialstr" value="', htmlEnt($r['dialstr']) ,'" size="20" maxlength="40" />';
			echo '</td>',"\n";
			
			echo '<td>';
			echo '<input type="checkbox" name="in"', ($r['allow_in'] ? ' checked="checked"' : '') ,' />';
			echo '</td>',"\n";
			
			echo '<td>';
			echo '<input type="checkbox" name="out"', ($r['allow_out'] ? ' checked="checked"' : '') ,' />';
			echo '</td>',"\n";
			
			echo '<td>';
			echo '<select name="group">',"\n";
			foreach ($gates as $key => $gate) {
				echo '<option value="', htmlEnt($key) ,'"', (($r['gid']==$key) ? ' selected="selected"' : '') ,'>', htmlEnt($gate) ,'</option>',"\n";
			}
			echo '</select>',"\n";
			echo '</td>',"\n";
			
			echo '<td>',"\n";
			echo '<button type="submit" title="', __('Speichern'), '" class="plain">';
			echo '<img alt="', __('Speichern') ,'" src="',GS_URL_PATH,'crystal-svg/16/act/filesave.png" />
			</button>',"\n";
			echo "&nbsp;\n";
			echo '<button type="reset" title="', __('Abbrechen'), '" class="plain">';
			echo '<img alt="', __('Abbrechen') ,'" src="',GS_URL_PATH,'crystal-svg/16/act/cancel.png" />
			</button>',"\n";
			echo '</td>',"\n";
			
			echo '</form>',"\n";
			
		} else {
			echo '<td>', htmlEnt($r['name']) ,'</td>',"\n";
			
			echo '<td>', htmlEnt($r['title']) ,'</td>',"\n";
			
			echo '<td>', htmlEnt($r['type']) ,'</td>',"\n";
			
			echo '<td>', htmlEnt($r['dialstr']) ,'</td>',"\n";
			
			echo '<td>';
			echo '<input type="checkbox" name="allow_in"', ($r['allow_in'] ? ' checked="checked"' : '') ,' disabled="disabled" />';
			//echo ($r['allow_in'] ? '&#x2714;' : '&#x237B;');
			echo '</td>',"\n";
			
			echo '<td>';
			echo '<input type="checkbox" name="allow_out"', ($r['allow_out'] ? ' checked="checked"' : '') ,' disabled="disabled" />';
			//echo ($r['allow_out'] ? '&#x2714;' : '&#x237B;');
			echo '</td>',"\n";
			
			echo '<td>', htmlEnt($r['gtitle']) ,'</td>',"\n";
			
			echo '<td>',"\n";
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'edit='.$r['id'] .'&amp;page='.$page) ,'" title="', __('bearbeiten'), '"><img alt="', __('bearbeiten'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='.$r['id'] .'&amp;page='.$page) ,'" title="', __('l&ouml;schen'), '"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
			echo '</td>',"\n";
		}
		
		echo '</tr>',"\n";
	}
}
?>

<tr>
<?php

if (! $edit) {
	echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
	echo gs_form_hidden($SECTION, $MODULE), "\n";
	
?>
	<td>
		<input type="text" name="name" value="" size="20" maxlength="40" />
	</td>
	<td>
		<input type="text" name="title" value="" size="20" maxlength="40" />
	</td>
	<td>
<?php
		echo '<select name="type">',"\n";
		foreach ($types as $key => $type) {
			echo '<option value="', htmlEnt($type) ,'">', htmlEnt($type) ,'</option>',"\n";
		}
		echo '</select>',"\n";
?>
	</td>
	<td>
		<input type="text" name="dialstr" value="" size="20" maxlength="40" />
	</td>
	<td>
		<input type="checkbox" name="in" />
	</td>
	<td>
		<input type="checkbox" name="out" />
	</td>
	<td>
<?php
		echo '<select name="group">',"\n";
		foreach ($gates as $key => $gate) {
			echo '<option value="', htmlEnt($key) ,'">', htmlEnt($gate) ,'</option>',"\n";
		}
		echo '</select>',"\n";
?>
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


<br />
<p>
	<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/info.png" />
	<small><?php echo __('&Auml;nderungen ben&ouml;tigen einen Dialplan-Reload und Reload von Asterisk'); ?></small>
</p>
