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
require_once( GS_DIR .'inc/extension-state.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


function count_users_configured( $DB ) {
	$num = (int)$DB->executeGetOne( 'SELECT COUNT(*) FROM `users` WHERE `nobody_index` IS NULL');
	return $num;
}
function count_users_logged_in( $DB ) {
	$num = (int)$DB->executeGetOne( 'SELECT COUNT(*) FROM `phones` `p` LEFT JOIN `users` `u` ON (`u`.`id`=`p`.`user_id`) WHERE `u`.`nobody_index` IS NULL');
	return $num;
}



echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";


$per_page = (int)GS_GUI_NUM_RESULTS;

$name = trim(@$_REQUEST['name']);
$number = trim(@$_REQUEST['number']);
$page = (int)@$_REQUEST['page'];
$edit_user   = trim(@$_REQUEST['edit']);
$save_user   = trim(@$_REQUEST['save']);
$delete_user   = trim(@$_REQUEST['delete']);

$cbdelete = trim(@$_REQUEST['cbdelete']);
$cbregexp = trim(@$_REQUEST['cbregexp']);
$cbpin = trim(@$_REQUEST['cbpin']);

$extnum = trim(@$_REQUEST['extnum']);
$extnumdel = trim(@$_REQUEST['extndel']);

$upgroups = @$_REQUEST['upgroup'];
$upgrouped = @$_REQUEST['upgrouped'];

$user_fname   = trim(@$_REQUEST['ufname']);
$user_lname   = trim(@$_REQUEST['ulname']);
$user_ext   = trim(@$_REQUEST['uext']);
$user_name   = trim(@$_REQUEST['uuser']);
$user_pin   = trim(@$_REQUEST['upin']);
$user_email   = trim(@$_REQUEST['uemail']);
$user_host   = trim(@$_REQUEST['uhost']);


$sql_query = 'SELECT `id`, `host`
FROM `hosts`
ORDER BY `id` ASC';
$rs = $DB->execute($sql_query);

if (@$rs) {
	while ($r = $rs->fetchRow()) {
		$hosts[$r['id']] = $r['host'];
	}
}

if ($delete_user) {
	$ret_val = gs_user_del( $delete_user );
	if (isGsError( $ret_val )) echo $ret_val->getMsg();
	
}

if ($save_user) {
	$ret_val = gs_user_change( $save_user, $user_pin, $user_fname, $user_lname, $user_host, false, $user_email );
	if (isGsError( $ret_val )) echo $ret_val->getMsg();
	
}

if ($user_name) {
	$ret_val = gs_user_add( $user_name, $user_ext, $user_pin, $user_fname, $user_lname, $user_host, $user_email );
	if (isGsError( $ret_val )) echo $ret_val->getMsg();
}

if ($cbdelete) {
	$ret_val = gs_callblocking_delete( $edit_user, $cbdelete );
	if (isGsError( $ret_val )) echo $ret_val->getMsg();
}

if ($extnumdel) {
	$ret_val = gs_user_external_number_del( $edit_user, $extnumdel );
	if (isGsError( $ret_val )) echo $ret_val->getMsg();
}


if ($cbregexp) {
	$ret_val = gs_callblocking_set( $edit_user, $cbregexp, $cbpin );
	if (isGsError( $ret_val )) echo $ret_val->getMsg();
}

if ($extnum) {
	$ret_val = gs_user_external_number_add( $edit_user, $extnum );
	if (isGsError( $ret_val )) echo $ret_val->getMsg();
}



if ($upgrouped && $edit_user) {
	$sql_query = '
	DELETE `p`
	FROM `pickupgroups_users` `p` , `users` `u`
	WHERE `p`.`user_id` = `u`.`id` 
	AND `u`.`user` = \''.$DB->escape($edit_user).'\'';

	$rs = $DB->execute($sql_query);
	
	if (is_array($upgroups)) {
		foreach ($upgroups as $upgroup) {
			$ret_val = gs_pickupgroup_user_add( $upgroup, $edit_user );	
			if (isGsError( $ret_val )) echo $ret_val->getMsg();
		}
	}
}

if (!$edit_user) {

	
	if ($number != '') {
		
		# search by number
		
		$search_url = '&amp;number='. urlEncode($number);
		
		$number_sql = str_replace(
			array( '*', '?' ),
			array( '%', '_' ),
			$number
		) .'%';
		$rs = $DB->execute(
	'SELECT SQL_CALC_FOUND_ROWS
		`u`.`firstname` `fn`, `u`.`lastname` `ln`, `u`.`host_id` `hid`, `u`.`honorific` `hnr`, `u`.`user` `usern`, `s`.`name` `ext`, `u`.`email` `email`, `u`.`pin` `pin`
	FROM
		`users` `u` JOIN
		`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
	WHERE
		`u`.`nobody_index` IS NULL AND (
		`s`.`name` LIKE \''. $DB->escape($number_sql) .'\'
		)
	ORDER BY `s`.`name`
	LIMIT '. ($page*(int)$per_page) .','. (int)$per_page
		);
		$num_total = @$DB->numFoundRows();
		$num_pages = ceil($num_total / $per_page);
		
	} else {
		
		# search by name
		
		$number = '';
		$search_url = '&amp;name='. urlEncode($name);
		
		$name_sql = str_replace(
			array( '*', '?' ),
			array( '%', '_' ),
			$name
		) .'%';
		$rs = $DB->execute(
	'SELECT SQL_CALC_FOUND_ROWS
		`u`.`firstname` `fn`, `u`.`lastname` `ln`, `u`.`host_id` `hid`,`u`.`honorific` `hnr`, `u`.`user` `usern`, `s`.`name` `ext` , `u`.`email` `email`, `u`.`pin` `pin`
	FROM
		`users` `u` JOIN
		`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
	WHERE
		`u`.`nobody_index` IS NULL AND (
		`u`.`lastname` LIKE _utf8\''. $DB->escape($name_sql) .'\' COLLATE utf8_unicode_ci OR
		`u`.`firstname` LIKE _utf8\''. $DB->escape($name_sql) .'\' COLLATE utf8_unicode_ci
		)
	ORDER BY `u`.`lastname`, `u`.`firstname`
	LIMIT '. ($page*(int)$per_page) .','. (int)$per_page
		);
		$num_total = @$DB->numFoundRows();
		$num_pages = ceil($num_total / $per_page);
		
	}
	
	
	?>
	
	<table cellspacing="1" class="phonebook">
	<thead>
	<tr>
		<th style="width:253px;"><?php echo __('Name suchen'); ?></th>
		<th style="width:234px;"><?php echo __('Nummer suchen'); ?></th>
		<th style="width:100px;"><?php echo __('Seite'), ' ', ($page+1), ' / ', $num_pages; ?></th>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td>
			<form method="get" action="<?php echo GS_URL_PATH; ?>">
			<?php echo gs_form_hidden($SECTION, $MODULE); ?>
			<input type="text" name="name" value="<?php echo htmlEnt($name); ?>" size="25" style="width:200px;" />
			<button type="submit" title="<?php echo __('Name suchen'); ?>" class="plain">
				<img alt="<?php echo __('Suchen'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/search.png" />
			</button>
			</form>
		</td>
		<td>
			<form method="get" action="<?php echo GS_URL_PATH; ?>">
			<?php echo gs_form_hidden($SECTION, $MODULE); ?>
			<input type="text" name="number" value="<?php echo htmlEnt($number); ?>" size="15" style="width:130px;" />
			<button type="submit" title="<?php echo __('Nummer suchen'); ?>" class="plain">
				<img alt="<?php echo __('Suchen'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/search.png" />
			</button>
			</form>
		</td>
		<td rowspan="2">
	<?php
	
	if ($page > 0) {
		echo
		'<a href="', gs_url($SECTION, $MODULE), $search_url, '&amp;page=', ($page-1), '" title="', __('zur&uuml;ckbl&auml;ttern'), '" id="arr-prev">',
		'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/back-cust.png" />',
		'</a>', "\n";
	} else {
		echo
		'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/back-cust-dis.png" />', "\n";
	}
	if ($page < $num_pages-1) {
		echo
		'<a href="', gs_url($SECTION, $MODULE), $search_url, '&amp;page=', ($page+1), '" title="', __('weiterbl&auml;ttern'), '" id="arr-next">',
		'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/forward-cust.png" />',
		'</a>', "\n";
	} else {
		echo
		'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/forward-cust-dis.png" />', "\n";
	}
	
	?>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="quickchars">
	<?php
	
	$chars = array();
	$chars['#'] = '';
	for ($i=65; $i<=90; ++$i) $chars[chr($i)] = chr($i);
	foreach ($chars as $cd => $cs) {
		echo '<a href="', gs_url($SECTION, $MODULE), '&amp;name=', htmlEnt($cs), '">', htmlEnt($cd), '</a>', "\n";
	}
	
	?>
		</td>
	</tr>
	</tbody>
	</table>
	
	<table cellspacing="1" class="phonebook">
	<thead>
	<tr>
		<th style="width:253px;"><?php echo __('Name, Vorname'); ?></th>
		<th style="width:70px;"><?php echo __('Nummer'); ?></th>
		<th style="width:93px;"><?php echo __('User'); ?></th>
		<th style="width:93px;"><?php echo __('PIN'); ?></th>
		<th style="width:93px;"><?php echo __('eMail'); ?></th>
		<th style="width:45px;"><?php echo __('Host'); ?></th>
		<th style="width:100px;"><?php echo __('Status'); ?></th>
		<th style="width:55px;"> </th>
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
			
			echo '<td>', htmlEnt($r['ln']);
			if ($r['fn'] !='') echo ', ', htmlEnt($r['fn']);
			echo '</td>';
			//echo '<td>', htmlEnt($r['fn']),'</td>';	
			//echo '<td>', htmlEnt($r['hnr']), '</td>';	
			echo '<td>', $r['ext'], '</td>';
			echo '<td>', htmlEnt($r['usern']), '</td>';
			echo "<td>".str_repeat("*",strlen($r['pin']))."</td>\n";
			echo '<td>', htmlEnt($r['email']), '</td>';
			echo '<td>', $r['hid'].' ('.@$hosts[$r['hid']].') </td>';
			echo '<td>';
			$state = gs_extstate_single( $r['ext'] );
			switch ($state) {
			case AST_MGR_EXT_UNKNOWN:
				echo '<img alt=" " src="', GS_URL_PATH, 'crystal-svg/16/app/important.png" />&nbsp; ', __('unbekannt');
				break;
			case AST_MGR_EXT_IDLE:
				echo '<img alt=" " src="', GS_URL_PATH, 'crystal-svg/16/act/greenled.png" />&nbsp; ', __('bereit');
				break;
			case AST_MGR_EXT_OFFLINE:
				echo '<img alt=" " src="', GS_URL_PATH, 'crystal-svg/16/act/free_icon.png" />&nbsp; ', __('offline');
				break;
			case AST_MGR_EXT_INUSE:
			case AST_MGR_EXT_BUSY:
				echo '<img alt=" " src="', GS_URL_PATH, 'crystal-svg/16/act/redled.png" />&nbsp; ', __('spricht');
				break;
			case AST_MGR_EXT_RINGING:
				echo '<img alt=" " src="', GS_URL_PATH, 'crystal-svg/16/app/knotify.png" />&nbsp; ', __('klingelt');
				break;
			case AST_MGR_EXT_RINGINUSE:
				echo '<img alt=" " src="', GS_URL_PATH, 'crystal-svg/16/app/knotify.png" />&nbsp; ', __('Anklopfen');  //TRANSLATE ME
				break;
			case AST_MGR_EXT_ONHOLD:
				echo '<img alt=" " src="', GS_URL_PATH, 'crystal-svg/16/act/redled.png" />&nbsp; ', __('Halten');  //TRANSLATE ME
				break;
			default:
				echo $state;
			}
			echo '</td>';
			echo '<td>';
				echo '<a href="', gs_url($SECTION, $MODULE), '&amp;edit=', $r['usern'], '&amp;name=', rawUrlEncode($name), '&amp;number=', rawUrlEncode($number), '&amp;page=', $page, '" title="', __('bearbeiten'), '"><img alt="', __('bearbeiten'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
				echo '<a href="', gs_url($SECTION, $MODULE), '&amp;delete=', $r['usern'], '&amp;name=', rawUrlEncode($name), '&amp;number=', rawUrlEncode($number), '&amp;page=', $page, '" title="', __('l&ouml;schen'), '"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
			echo "</td>\n";
			echo '</tr>', "\n";
			
		}
	}
	
	?>
	<tr>
	<?php
	
	if (!$edit_user) {
	
		echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
		echo gs_form_hidden($SECTION, $MODULE), "\n";
		echo '<input type="hidden" name="name" value="', htmlEnt($name), '" />', "\n";
		echo '<input type="hidden" name="number" value="', htmlEnt($number), '" />', "\n";
	?>
		<td>
			<input type="text" name="ulname" value="" size="15" maxlength="40" style="width:125px;" /><input type="text" name="ufname" value="" size="15" maxlength="40" style="width:115px;" />
		</td>
		<td>
			<input type="text" name="uext" value="" size="5" maxlength="25"  />
		</td>
		<td>
			<input type="text" name="uuser" value="" size="5" maxlength="25"  />
		</td>
		<td>
			<input type="password" name="upin" value="" size="5" maxlength="25" />
		</td>
		<td>
			<input type="text" name="uemail" value="" size="15" maxlength="25" />
		</td>
		
		<td>
	<?php		echo "<select name=\"uhost\" > ";
			foreach ($hosts as $key => $host) {
				echo '<option value="'.$key."\">$key ($host)</option>\n";
				
			}
			echo "</select></td>\n";
	?>
		</td>
		<td>
			
		</td>
		<td>
			<button type="submit" title="<?php echo __('Benutzer anlegen'); ?>" class="plain">
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
	<table cellspacing="1" class="phonebook">
	<thead>
	<tr>
		<th style="width:220px;">
			<span class="sort-col"><?php echo __('Benutzer'); ?></span>
		</th>
		<th style="width:75px;">
			&nbsp;
		</th>
	</tr>
	</thead>
	<tbody>
	<tr>
		<th><?php echo __('Eingerichtete Benutzer'); ?>:</th>
		<td class="r"><?php echo count_users_configured($DB); ?></td>
	</tr>
	<tr>
		<th><?php echo __('Eingeloggte Benutzer'); ?>:
		</th>
		<td class="r"><?php echo count_users_logged_in($DB); ?></td>
	</tr>
	</tbody>
	</table>
<?php
} else {

/*
	$sql_query = 'SELECT `id`, `title`
	FROM `pickupgroups`';
	$rs = $DB->execute($sql_query);

	if (@$rs) {
		while ($r = $rs->fetchRow()) {
			$pgroups[$r['id']] = $r['title'];
		}
	}
*/
	

		$rs = $DB->execute(
	'SELECT 
		`u`.`firstname` `fn`, `u`.`lastname` `ln`, `u`.`host_id` `hid`,`u`.`honorific` `hnr`, `u`.`user` `usern`, `s`.`name` `ext` , `u`.`email` `email`, `u`.`pin` `pin`, `u`.`id` `uid`
	FROM
		`users` `u` JOIN
		`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
	WHERE
		`u`.`user` = \''. $DB->escape($edit_user) .'\'');	
	
	if ($rs) $r = $rs->fetchRow();
	
	
	$sql_query = 'SELECT `id`, `title`
	FROM `pickupgroups`';

	$sql_query ='SELECT `p`.`id`, `p`.`title`, `x`.`host_id`
	FROM `pickupgroups` `p` 
	LEFT JOIN `pickupgroups_users` `u`
	ON `p`.`id` = `u`.`group_id`
	LEFT JOIN `users` `x` 
	ON `x`.`id` = `u`.`user_id`
	WHERE `x`.`host_id` = '.$r['hid'].' OR `x`.`host_id` IS NULL
	GROUP BY `p`.`id`';

	$rs = $DB->execute($sql_query);

	if (@$rs) {
		while ($r_pg = $rs->fetchRow()) {
			$pgroups[$r_pg['id']] = $r_pg['title'];
		}
	}

	$sql_query = 'SELECT `p`.`group_id` 
	FROM `pickupgroups_users` `p` 
	JOIN `users` `x` 
	ON `p`.`user_id` = `x`.`id` 
	WHERE `x`.`user` = \''.$DB->escape($edit_user).'\'';

	$rs = $DB->execute($sql_query);

	if (@$rs) {
		while ($r_pg = $rs->fetchRow()) {
			$pgroups_my[$r_pg['group_id']] = $r_pg['group_id'];
		}
	}

	$sql_query =
	'SELECT `id`, `regexp`, `pin` FROM `callblocking` WHERE `user_id`='. $r['uid'] .' ORDER BY LENGTH(`regexp`) DESC';

	$rs = $DB->execute($sql_query);
	$callblocking=Array();
	if (@$rs) {
		while ($r_pg = $rs->fetchRow()) {
			$callblocking[$r_pg['id']] = $r_pg;
		}
	}


	

	$sql_query =
	'SELECT `number` FROM `users_external_numbers` WHERE `user_id`='. $r['uid'] .' ORDER BY LENGTH(`number`) DESC';

	$rs = $DB->execute($sql_query);
	$ext_nums=Array();
	if (@$rs) {
		while ($r_pg = $rs->fetchRow()) {
			$ext_nums[] = $r_pg['number'];
		}
	}

?>


<table cellspacing="1" class="phonebook">
	<form>
	<thead>
<?php
	echo gs_form_hidden($SECTION, $MODULE), "\n";
	echo '<input type="hidden" name="save" value="', htmlEnt($edit_user), '" />', "\n";
?>	
	<tr>
		<th style="width:220px;">
			<span class="sort-col"><?php echo __('Benutzer'); ?></span>
		</th>
		<td style="width:150px;">
			<?php echo htmlEnt($edit_user); ?>	
		</td>
	</tr>
	</thead>
	<tbody>
	<tr>
		<th><?php echo __('Durchwahl'); ?>:</th>
		<td ><?php echo $r['ext']; ?></td>
	</tr>
	<tr>
		<th><?php echo __('Name'); ?>:
		</th>
		<td >
			<input type="text" name="ulname" value="<?php echo htmlEnt($r['ln']); ?>" size="20" maxlength="40"  />	
		</td>
	</tr>
	<tr>
		<th><?php echo __('Vorname'); ?>:
		</th>
		<td >
			<input type="text" name="ufname" value="<?php echo htmlEnt($r['fn']); ?>" size="20" maxlength="40"  />	
		</td>
	</tr>
	<tr>
		<th><?php echo __('PIN'); ?>:
		</th>
		<td >
			<input type="password" name="upin" value="<?php echo htmlEnt($r['pin']); ?>" size="20" maxlength="40"  />	
		</td>
	</tr>
	<tr>
		<th><?php echo __('eMail'); ?>:
		</th>
		<td >
			<input type="text" name="uemail" value="<?php echo htmlEnt($r['email']); ?>" size="20" maxlength="40"  />	
		</td>
	</tr>
	<tr>
		<th><?php echo __('Host'); ?>:
		</th>
		<td >
<?php				echo "<select name=\"uhost\" > ";
				foreach ($hosts as $key => $host) {
					echo '<option value="'.$key."\" ";
					if ($r['hid'] == $key) echo 'selected=\"selected\" ';
					echo ">$key ($host)</option>\n";
					
				}
				echo "</select></td>\n";
?>		
		</td>
	</tr>
	<tr>
		<th>
		</th>
		<th>
			<button type="submit" title="<?php echo __('Speichern'); ?>" class="plain">
				<img alt="<?php echo __('Speichern'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
			</button>
		</th>
	</tr>
	</tbody>
	</form>
	</table>
	<br>
	<table>
	<form>
<?php
	echo gs_form_hidden($SECTION, $MODULE), "\n";
	echo '<input type="hidden" name="edit" value="', htmlEnt($edit_user), '" />', "\n";
	echo '<input type="hidden" name="upgrouped" value="yes" />', "\n";
?>
	<thead>
		<tr>
		<th style="width:220px;">
			<span class="sort-col"><?php echo __('Pickupgruppe'); ?></span>
		</th>
		<td style="width:215px;">
<?php
				echo '<select multiple="multiple" name="upgroup[]" size="4">',"\n";
				
				foreach ($pgroups as $key => $pgroup) {
					$c++;
					echo '<option value="'.$key.'" ';
					if (@$pgroups_my[$key]) echo 'selected="selected" ';
					echo ">$key ($pgroup)</option>\n";
				}
				echo '<option value=""></option>';
				echo "</select></td>\n";
?>		
		</td>
	</tr>
	</thead>
	<tbody>
	<tr>
		<th>
		</th>
		<th>
			<button type="submit" title="<?php echo __('Speichern'); ?>" class="plain">
				<img alt="<?php echo __('Speichern'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
			</button>
		</th>
	</tr>
	</tbody>
	</form>
	</table>
	
	<br>
	<table>
	<thead>
	<tr>
		<th style="width:220px;">
			<span class="sort-col"><?php echo __('Anrufsperre'); ?></span>
		</th>
		<th style="width:150px;">
			<span ><?php echo __('Entsperr PIN'); ?></span>	
		</th>
		<th style="width:50px;">
			
		</th>
	</tr>
	</thead>
	<tbody>
<?php	
	foreach ($callblocking as $key => $cb_entry) {
		echo "<tr>\n";
		echo "<td>\n";
			echo htmlEnt($cb_entry['regexp']);	
		echo "</td>\n";
		echo "<td >\n";
			echo htmlEnt($cb_entry['pin']);	
		echo "</td>\n";
		echo "<td>\n";
		echo '<a href="', gs_url($SECTION, $MODULE);
		echo '&amp;cbdelete='. htmlEnt($cb_entry['regexp']) .'&amp;edit=', htmlEnt($edit_user), '" title="', __('entfernen'), '">';
		echo '<img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
		echo "</td>\n";		
		echo "</tr>\n";
	}
	echo "<tr>\n";
	echo "<form>\n";
	echo gs_form_hidden($SECTION, $MODULE), "\n";
	echo '<input type="hidden" name="edit" value="', htmlEnt($edit_user), '" />', "\n";
	echo "<td>\n";
		echo '<input type="text" name="cbregexp" value="" size="20" maxlength="40"  />';	
	echo "</td>\n";
	echo "<td >\n";
		echo '<input type="text" name="cbpin" value="" size="20" maxlength="40"  />';	
	echo "</td>\n";
	echo "<td>\n";
	echo '<button type="submit" title="'.__('Speichern').'" class="plain">';
	echo '<img alt="'.__('Speichern').'" src="'.GS_URL_PATH.'crystal-svg/16/act/filesave.png" />';
	echo "</button>\n";
	echo "</td>\n";
	echo "</form>\n";
	echo "</tr>\n";
?>
	</tbody>
	</table>

<br>
	<table>
	<thead>
	<tr>
		<th style="width:220px;">
			<span class="sort-col"><?php echo __('Externe Rufnummern'); ?></span>
		</th>
		<th style="width:150px;">
			<span ><?php echo __('Rufnummer'); ?></span>	
		</th>
		<th style="width:50px;">
			
		</th>
	</tr>
	</thead>
	<tbody>
<?php	
	foreach ($ext_nums as $ext_num) {
		echo "<tr>\n";
		echo "<td></td>\n";
		echo "<td>\n";
			echo htmlEnt($ext_num);	
		echo "</td>\n";
		echo "<td>\n";
		echo '<a href="', gs_url($SECTION, $MODULE), '&amp;extndel='. $ext_num .'&amp;edit=', $edit_user, '" title="', __('entfernen'), '"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
		echo "</td>\n";		
		echo "</tr>\n";
	}
	echo "<tr>\n";
	echo "<form>\n";
	echo gs_form_hidden($SECTION, $MODULE), "\n";
	echo '<input type="hidden" name="edit" value="', htmlEnt($edit_user), '" />', "\n";
	echo "<td></td>\n";
	echo "<td>\n";
		echo '<input type="text" name="extnum" value="" size="20" maxlength="40"  />';	
	echo "</td>\n";
	echo "<td>\n";
	echo '<button type="submit" title="'.__('Speichern').'" class="plain">';
	echo '<img alt="'.__('Speichern').'" src="'.GS_URL_PATH.'crystal-svg/16/act/filesave.png" />';
	echo "</button>\n";
	echo "</td>\n";
	echo "</form>\n";
	echo "</tr>\n";
?>
	</tbody>
	</table>

<?php
} 
?>