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
* Soeren Sprenger <soeren.sprenger@amooma.de>
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
include_once( GS_DIR .'inc/gs-fns/gs_pickupgroup_add.php' );
include_once( GS_DIR .'inc/gs-fns/gs_pickupgroup_del.php' );
include_once( GS_DIR .'inc/gs-fns/gs_pickupgroup_user_add.php' );
include_once( GS_DIR .'inc/gs-fns/gs_pickupgroup_user_del.php' );
include_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );
include_once( GS_DIR .'inc/gs-fns/gs_astphonebuttons.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
//echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo __('Rufannahme-Gruppen');
echo '</h2>', "\n";

echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";
echo '<script type="text/javascript">
//<![CDATA[
function confirm_delete() {
	return confirm(', utf8_json_quote(__("Wirklich l\xC3\xB6schen?")) ,');
}
//]]>
</script>' ,"\n";

$edit     = (int)trim(@$_REQUEST['edit'    ]);
$save     = (int)trim(@$_REQUEST['save'    ]);
$per_page = (int)GS_GUI_NUM_RESULTS;
$page     =      (int)@$_REQUEST['page'    ] ;
$title    =      trim(@$_REQUEST['title'   ]);
$group    = (int)trim(@$_REQUEST['group'   ]);
$delete   = (int)trim(@$_REQUEST['delete'  ]);
$pudelete =      trim(@$_REQUEST['pudelete']);
$user     =      trim(@$_REQUEST['user'    ]);

if ($delete) {
	$ret = gs_pickupgroup_del( $delete );
	if ( GS_BUTTONDAEMON_USE == true ) {
		gs_buttondeamon_group_del( $delete );
	}
	if (isGsError( $ret )) echo $ret->getMsg();
}

if ($pudelete) {
	$ret = gs_pickupgroup_user_del( $group, $pudelete );
	if (isGsError( $ret )) echo $ret->getMsg();
	if ( GS_BUTTONDAEMON_USE == true ) {
		$userinfo = gs_user_get($pudelete);
		gs_buttondeamon_group_update($userinfo['ext']);
	}
}

if ($title && !$save) {
	$ret = gs_pickupgroup_add( $title );
	if (isGsError( $ret )) echo $ret->getMsg();
}	

if ($save) {
	$sql_query =
'UPDATE `pickupgroups` SET
	`title`=\''. $DB->escape($title) .'\'
WHERE `id`='. $save;
	$rs = $DB->execute($sql_query);
}

if ($group && $user) {
	$ret = gs_pickupgroup_user_add( $group, $user );
	if (isGsError( $ret )) echo $ret->getMsg();
	if ( GS_BUTTONDAEMON_USE == true ) {
		$userinfo = gs_user_get($user);
		gs_buttondeamon_group_update($userinfo['ext']);
	}
}



#####################################################################
#  show pickup group {
#####################################################################
if (! $group) {
	
	$sql_query =
'SELECT SQL_CALC_FOUND_ROWS 
	`p`.`id` `id`, `p`.`title` `title`,
	COUNT(`pu`.`user_id`) `num_members`
FROM
	`pickupgroups` `p` LEFT JOIN
	`pickupgroups_users` `pu` ON (`pu`.`group_id`=`p`.`id`)
GROUP BY `p`.`id`
ORDER BY `p`.`id`
LIMIT '. ($page*(int)$per_page) .','. (int)$per_page;
	
	$rs = $DB->execute($sql_query);
	
	$num_total = @$DB->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	
?>

<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<?php
if ($edit > 0) {
	echo '<input type="hidden" name="page" value="', htmlEnt($page), '" />', "\n";
	echo '<input type="hidden" name="save" value="', $edit , '" />', "\n";
}
?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:30px;"><?php echo __('ID'); ?></th>
	<th style="width:150px;"><?php echo __('Bezeichnung'); ?></th>
	<th style="width:30px;"><?php echo __('Mitglieder'); ?></th>
	<th style="width:80px;">
<?php
	
	//echo __('S.') ,' ';
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
			
			if ($edit === $r['id']) {
				
				echo '<td class="r">', htmlEnt($r['id']) ,'</td>',"\n";
				
				echo '<td>';	
				echo '<input type="text" name="title" value="', htmlEnt($r['title']) ,'" size="25" maxlength="40" />';	
				echo '</td>',"\n";
				
				echo '<td class="r">', $r['num_members'] ,'</td>',"\n";
				
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
				
				echo '<td class="r">', htmlEnt($r['id']) ,'</td>',"\n";
				
				echo '<td>', htmlEnt($r['title']) ,'</td>',"\n";
				
				echo '<td class="r">',"\n";
				echo '<a href="', gs_url($SECTION, $MODULE, null, 'group='.$r['id']) ,'" title="', __('l&ouml;schen'), '">',
				$r['num_members'] ,'</a>';
				echo '</td>',"\n";
				
				echo '<td>',"\n";
				
				echo '<a href="', gs_url($SECTION, $MODULE, null, 'edit='.$r['id'] .'&amp;page='.$page) ,'" title="', __('bearbeiten'), '"><img alt="', __('bearbeiten'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
				
				echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='.$r['id'] .'&amp;page='.$page) ,'" title="', __('l&ouml;schen'), '" onclick="return confirm_delete();"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
				
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
		<td>&nbsp;</td>
		<td>
			<input type="text" name="title" value="" size="25" maxlength="40" />
		</td>
		<td>&nbsp;</td>
		<td>
			<button type="submit" title="<?php echo __('Gruppe anlegen'); ?>" class="plain">
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
#  show pickup group }
#####################################################################



#####################################################################
#  show members {
#####################################################################
else {
	
	$query =
'SELECT `title`
FROM `pickupgroups`
WHERE `id`='. $group;
	$rs = $DB->execute($query);
	$pgrp = $rs->fetchRow();
	if (! $pgrp) {
		echo 'Group not found!';
		return;
	}
	
	echo '<h3>', __('Mitglieder der Rufannahme-Gruppe');
	echo ' <q>', htmlEnt($pgrp['title']) ,'</q> (ID ', $group ,')';
	echo '</h3>' ,"\n";
		
	$sql_query =
'SELECT SQL_CALC_FOUND_ROWS 
	`u`.`user` `user`, `u`.`lastname` `ln`,
	`u`.`firstname` `fn`, `u`.`id` `id`
FROM
	`pickupgroups_users` `pu` JOIN
	`users` `u` ON (`u`.`id`=`pu`.`user_id`)
WHERE
	`pu`.`group_id`='.$group.'
ORDER BY `u`.`user`
LIMIT '. ($page*(int)$per_page) .','. (int)$per_page;
	
	$rs = $DB->execute($sql_query);
	
	$num_total = @$DB->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	
	
	if (! $edit) {
		echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
		echo gs_form_hidden($SECTION, $MODULE), "\n";
		echo '<input type="hidden" name="group" value="', htmlEnt($group), '" />', "\n";
	}
?>
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:120px;"><?php echo __('User'); ?></th>
	<th style="width:230px;"><?php echo __('Name'); ?></th>
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
	if (@$rs) {
		$i = 0;
		while ($r = $rs->fetchRow()) {
			echo '<tr class="', ((++$i % 2) ? 'odd':'even'), '">', "\n";
			
			echo '<td>', htmlEnt($r['user']) ,'</td>',"\n";
			
			echo '<td>', htmlEnt($r['ln']);
			echo ', ', htmlEnt($r['fn']);
			echo '</td>',"\n";
			
			echo '<td>',"\n";
			
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'pudelete='.$r['user'] .'&amp;group='.$group) ,'" title="', __('l&ouml;schen'), '"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
			
			echo '</td>',"\n";
			echo '</tr>', "\n";
			
		}
	}
	
	if (! $edit) {
		echo '<tr class="', ((++$i % 2) ? 'odd':'even'), '">', "\n";
?>
		<td>
			<input type="text" name="user" value="" size="15" maxlength="25" />
		</td>
		<td>&nbsp;</td>
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
