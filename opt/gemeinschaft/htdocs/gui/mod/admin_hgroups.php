<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* 
* Author: Henning Holtschneider <henning@loca.net> - LocaNet oHG
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

require_once(GS_DIR . 'inc/gs-fns/gs_huntgroup_del.php');
require_once(GS_DIR . 'inc/gs-fns/gs_huntgroup_user_add.php');
require_once(GS_DIR . 'inc/gs-fns/gs_huntgroup_user_del.php');
require_once(GS_DIR . 'inc/gs-fns/gs_huntgroups_get.php');

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";

$edit     = (int)trim(@$_REQUEST['edit'    ]);
$save     = (int)trim(@$_REQUEST['save'    ]);
$per_page = (int)GS_GUI_NUM_RESULTS;
$page     =      (int)@$_REQUEST['page'    ] ;
$title    =      trim(@$_REQUEST['title'   ]);
$group    = (int)trim(@$_REQUEST['group'   ]);
$delete   = (int)trim(@$_REQUEST['delete'  ]);
$hudelete =      trim(@$_REQUEST['hudelete']);
$user     =      trim(@$_REQUEST['user'    ]);
$timeout  = (int)trim(@$_REQUEST['timeout' ]);
$strategy =      trim(@$_REQUEST['strategy']);
$edit     =      trim(@$_REQUEST['edit'    ]);

if ( $delete ) {
	$ret = gs_huntgroup_del( $delete );
	if (isGsError( $ret )) echo $ret->getMsg();	
}

if ( $hudelete ) {
	$ret = gs_huntgroup_user_del( $group, $hudelete );
	if (isGsError( $ret )) echo $ret->getMsg();
}

if ( $user ) {
	if ( $timeout > 0 )
		$ret = gs_huntgroup_user_add( $group, $strategy, $user, $timeout );
	else
		$ret = gs_huntgroup_user_add( $group, $strategy, $user );
	if (isGsError( $ret )) echo $ret->getMsg();
}

if ( $save ) {
	$sql_query =
'UPDATE `huntgroups` SET
	`strategy`=\''. $strategy .'\'
WHERE `number`='. $save;
	$rs = $DB->execute($sql_query);
}

#####################################################################
#  show hunt group {
#####################################################################
if (! $group) {
	
	$sql_query =
'SELECT `number`, `strategy`, COUNT(*) as `count`
FROM `huntgroups`
GROUP BY `number`
ORDER BY `number`
LIMIT '. ($page*(int)$per_page) .','. (int)$per_page;
	
	$rs = $DB->execute($sql_query);
	
	$num_total = @$DB->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	
?>

<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php 
echo gs_form_hidden($SECTION, $MODULE);
if ($edit > 0) {
	echo '<input type="hidden" name="page" value="', htmlEnt($page), '" />', "\n";
	echo '<input type="hidden" name="save" value="', $edit , '" />', "\n";
}
?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:30px;"><?php echo __('Rufnummer'); ?></th>
	<th style="width:60px;"><?php echo __('Rufschema'); ?></th>
	<th style="width:30px;"><?php echo __('Mitglieder'); ?></th>
	<th style="width:80px;">
<?php
	
	echo ($page+1), ' / ', $num_pages, '&nbsp; ',"\n";
	
	if ($page > 0) {
		echo
		'<a href="', gs_url($SECTION, $MODULE, null, 'page='.($page-1)) ,'" title="', __('zur&uuml;ckbl&auml;ttern'), '" id="arr-prev">',
		'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous.png" />',
		'</a>', "\n";
	} else {
		echo
		'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous_notavail.png" />', "\n";
	}
	
	if ($page < $num_pages-1) {
		echo
		'<a href="', gs_url($SECTION, $MODULE, null, 'page='.($page+1)) ,'" title="', __('weiterbl&auml;ttern'), '" id="arr-next">',
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
			echo '<td class="r">', htmlEnt($r['number']) ,'</td>',"\n";
			if ( $edit == $r['number'] ) {
				echo '<td><select name="strategy">';
				echo '<option value="linear" ', ($r['strategy'] == 'linear' ? ' selected="selected"' : ''), '>linear</option>';
				echo '<option value="parallel" ', ($r['strategy'] == 'parallel' ? ' selected="selected"' : ''), '>parallel</option>';
				echo '</select></td>', "\n";
				echo '<td>', htmlEnt($r['count']) ,'</a></td>',"\n";
				echo '<td>',"\n";
				
				echo '<button type="submit" title="', __('Speichern'), '" class="plain">';
				echo '<img alt="', __('Speichern') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/filesave.png" />';
				echo '</button>' ,"\n";
				
				echo '&nbsp;',"\n";
				
				echo '<a href="', gs_url($SECTION, $MODULE) ,'"><button type="button" title="', __('Abbrechen'), '" class="plain">';
				echo '<img alt="', __('Abbrechen') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/cancel.png" />';
				echo '</button></a>' ,"\n";
				
				echo '</td>',"\n";
			} else {
				echo '<td>', htmlEnt($r['strategy']) ,'</td>',"\n";
				echo '<td><a href="', gs_url( $SECTION, $MODULE, null, 'group='.$r['number'] .'&amp;strategy=' .$r['strategy'] ), '">', htmlEnt($r['count']) ,'</a></td>',"\n";
				echo '<td>',"\n";
				echo '<a href="', gs_url($SECTION, $MODULE, null, 'edit='.$r['number'] .'&amp;page='.$page) ,'" title="', __('bearbeiten'), '"><img alt="', __('bearbeiten'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
				echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='.$r['number'] .'&amp;page='.$page) ,'" title="', __('l&ouml;schen'), '"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
				echo '</td>',"\n";
			}
			echo '</tr>',"\n";
		}
	}
?>

<?php
	if (! $edit) {
		echo '<tr class="', ((++$i % 2) ? 'odd':'even'), '">', "\n";
?>
		<td class="r">
			<input type="text" name="group" value="" size="4" maxlength="10" />
		</td>
		<td><select name="strategy"><option value="linear">linear</option><option value="parallel">parallel</option></td>
		<td>&nbsp;</td>
		<td>
			<button type="submit" name="<?php echo __('Gruppe anlegen'); ?>" class="plain">
				<img alt="<?php echo __('Speichern'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
			</button>
		</td>
<?php
		echo '</tr>',"\n";
	}
?>

</tbody>
</table>
</form>

<?php

}
#####################################################################
#  show hunt group }
#####################################################################



#####################################################################
#  show members {
#####################################################################
else {
	
	$query =
'SELECT `hg`.`sequence_no`, `hg`.`strategy`, `hg`.`user_id`, `hg`.`timeout`, `u`.`user`, `u`.`lastname`, `u`.`firstname`
FROM `huntgroups` `hg`
JOIN `users` `u` ON (`hg`.`user_id`=`u`.`id`)
WHERE `hg`.`number`=' . $group .
' ORDER BY `hg`.`sequence_no`
LIMIT '. ($page*(int)$per_page) .','. (int)$per_page;
	$rs = $DB->execute($query);
	
	echo '<h3>', __('Mitglieder von Sammelanschluss');
	echo ' <q>', $group;
	echo '</h3>' ,"\n";
		
	$num_total = @$DB->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	
	if (! $edit) {
		echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
		echo gs_form_hidden($SECTION, $MODULE), "\n";
		echo '<input type="hidden" name="group" value="', htmlEnt($group), '" />', "\n";
		echo '<input type="hidden" name="strategy" value="', htmlEnt($strategy), '" />', "\n";
	}
?>
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:120px;"><?php echo __('User'); ?></th>
	<th style="width:230px;"><?php echo __('Name'); ?></th>
	<th style="width:230px;"><?php echo __('Timeout'); ?></th>
	<th style="width: 80px;">
<?php
	echo ($page+1), ' / ', $num_pages, '&nbsp; ',"\n";
	
	if ($page > 0) {
		echo
		'<a href="', gs_url($SECTION, $MODULE, null, 'page='.($page-1) .'&amp;group='.$group), '" title="', __('zur&uuml;ckbl&auml;ttern'), '" id="arr-prev">',
		'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous.png" />',
		'</a>', "\n";
	} else {
		echo
		'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous_notavail.png" />', "\n";
	}
	
	if ($page < $num_pages-1) {
		echo
		'<a href="', gs_url($SECTION, $MODULE, null, 'page='.($page+1) .'&amp;group='.$group), '" title="', __('weiterbl&auml;ttern'), '" id="arr-next">',
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
	$activeids = array();
	if (@$rs) {
		$i = 0;
		$timeout = 0;
		while ($r = $rs->fetchRow()) {
			$activeids[] = $r['user_id'];
			echo '<tr class="', ((++$i % 2) ? 'odd':'even'), '">', "\n";
			
			echo '<td>', htmlEnt($r['user']) ,'</td>',"\n";
			
			echo '<td>', htmlEnt($r['lastname']);
			echo ', ', htmlEnt($r['firstname']);
			echo '</td>',"\n";

			echo '<td>', htmlEnt($r['timeout']) ,'</td>',"\n";
			$timeout = $r['timeout'];

			echo '<td>',"\n";
			
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'hudelete='.$r['user'] .'&amp;group='.$group .'&amp;strategy='.$r['strategy']) ,'" title="', __('l&ouml;schen'), '"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
			
			echo '</td>',"\n";
			echo '</tr>', "\n";
			
		}
	}
	
	if (! $edit) {
		echo '<tr class="', ((++$i % 2) ? 'odd':'even'), '">', "\n";
		echo '<td>&nbsp;</td>';
		echo '<td><select name="user">';
		
		if (count($activeids) > 0)
			$qlimit = 'AND (`id` NOT IN (' . join(',', $activeids) . ')) ';
		else
			$qlimit = '';

		$query =
'SELECT `id`, `user`, `lastname`, `firstname`
FROM `users`
WHERE `user` NOT LIKE \'nobody-%\' ' . $qlimit .
'ORDER BY `user`';

		echo "<pre>$query</pre>";
		$rs = $DB->execute($query);
		if (! $rs) {
			echo 'Database error.';
			exit;
		}
		
		while ($r = $rs->fetchRow())
			echo '<option value="' . $r['user'] . '">' . $r['user'] . ' - ' . $r['lastname'] . ', ' . $r['firstname'] . '</option>';

		echo '</select></td>';
		
?>
		<td>
			<input type="text" name="timeout" value="<?= $timeout ?>" size="15" maxlength="25">
		</td>
		<td>
			<button type="submit" title="<?php echo __('Benutzer hinzuf&uuml;gen'); ?>" class="plain">
				<img alt="<?php echo __('Speichern'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
			</button>
		</td>
<?php
		echo '</tr>', "\n";
	}
?>

</tbody>
</table>
<?php
	if (! $edit) {
		echo '</form>' ,"\n";
	}
	
}
#####################################################################
#  show members }
#####################################################################
?>
