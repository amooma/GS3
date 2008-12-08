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

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

$delete_entry = (int)trim(@$_REQUEST['delete' ]);
$save_all     = (int)trim(@$_REQUEST['save'   ]);
$save_name    =      trim(@$_REQUEST['name'   ]);
$user_id      = (int)@$_SESSION['sudo_user']['info']['id'];
$auth_name    =      trim(@$_REQUEST['auth_name']);
$auth         = (int)trim(@$_REQUEST['auth']);
#delete friend
#
if ($delete_entry > 0) {
	$rs = $DB->execute(
	'DELETE FROM `user_watchlist`
	WHERE `buddy_user_id`='. $delete_entry .' AND `user_id`='. (int)@$_SESSION['sudo_user']['info']['id']
	);
}

# save new friend?
#
if ($save_name != '') {
	//get buddy_user_id (user_id) from name
	$buddy_user_id = $DB->executeGetOne('SELECT `id` FROM `users` WHERE `user`=\'' .$DB->escape($save_name).'\'');
	if(!$buddy_user_id) {
		echo '<div class="errorbox">';
		echo 'User nicht gefunden.';
		echo '</div>',"\n";
	}
	else {
		//dont insert twice
		$exists = $DB->executeGetOne('SELECT `buddy_user_id` FROM `user_watchlist` WHERE `user_id`='. $user_id .' AND `buddy_user_id` = ' .$DB->escape($buddy_user_id)  );
		if($exists) {
			echo '<div class="errorbox">';
			echo 'This user is already in your list.<br>';
			echo '</div>',"\n";
		} else {
			# save entry
			$rs = $DB->execute(
			'INSERT INTO `user_watchlist` ( `user_id`, `buddy_user_id`, `status`) VALUES
			('. $user_id .', \''. $buddy_user_id .'\', \'ack\')'
		);
		}
	}
}

# if User asks for save all entrys that differs from the database
if ($save_all == "1") {
	$rs_friends = $DB->execute('SELECT `u`.`id` ,`f`.`status`
	FROM
	`user_watchlist` `f` LEFT JOIN
	`users` `u` ON (`u`.`id`=`f`.`buddy_user_id`)
	WHERE `f`.`user_id`= '.$user_id);
	if (@$rs_friends) {
		while ($r = @$rs_friends->fetchRow()) {
			$val = trim(@$_REQUEST['status_'.$r['id']]);
			if ($val != $r['status']) {
				$ok = $DB->execute('UPDATE `user_watchlist` SET
				`status`=\''. $DB->escape($val) .'\'
				WHERE `buddy_user_id`='. $r['id'].' AND `user_id`= '.$user_id);
				if (!$ok) {
					echo '<div class="errorbox">';
					echo 'Error while updating Friend.<br>';
					echo '</div>',"\n";
				}
			}
		}
	}
}

# if user want to ask an other user to Authorize
if ($auth == "1") {
	if($auth_name != '')
		$buddy_user_id = $DB->executeGetOne('SELECT `id` FROM `users` WHERE `user`=\'' .$DB->escape($auth_name).'\'');

	if($auth_name == '' || !$buddy_user_id) {
		echo '<div class="errorbox">';
		echo 'You have to enter an valid username!<br>';
		echo '</div>',"\n";
	}
	//dont insert twice
	$exists = $DB->executeGetOne('SELECT `buddy_user_id` FROM `user_watchlist` WHERE `user_id`=\'' .$DB->escape($buddy_user_id).'\' AND `buddy_user_id` = '.$user_id );
	if($exists) {
		echo '<div class="errorbox">';
		echo 'Youre already has send an authorize-request to this User!<br>';
		echo '</div>',"\n";
	} else {
		$ok = $DB->execute('INSERT INTO `user_watchlist` ( `user_id`, `buddy_user_id`, `status`) VALUES
		('. $buddy_user_id .', \''. $user_id .'\', \'pnd\')');
		if(!$ok) {
			echo '<div class="errorbox">';
			echo 'Error while sending an authorize-request to this User!<br>';
			echo '</div>',"\n";
		}
	}
	
	

}

# get friends for the current User
#
$rs_friends = $DB->execute('SELECT `u`.`id` ,`u`.`user` , `u`.`firstname` , `u`.`lastname`, `f`.`status`
	FROM
	`user_watchlist` `f` LEFT JOIN
	`users` `u` ON (`u`.`id`=`f`.`buddy_user_id`)
	WHERE `f`.`user_id`= '.(int)@$_SESSION['sudo_user']['info']['id']);

echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
echo gs_form_hidden($SECTION, $MODULE), "\n";
?>

<h2><?php echo __('Kollegen die Ihren Status einsehen wollen:'); ?></h2>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:100px;" class="nobr"><?php echo __('User' ); ?></th>
	<th style="width:100px;" class="nobr"><?php echo __('Vorname' ); ?></th>
	<th style="width:100px;" class="nobr"><?php echo __('Nachname' ); ?></th>
	<th style="width:100px;" class="nobr"><?php echo __('Status'  ); ?></th>
	<th style="width:40px;" class="nobr"></th>
</tr>
</thead>
<tbody>
<?php
if (@$rs_friends) {
	while ($r = @$rs_friends->fetchRow()) {
		echo '<tr>', "\n";
		echo '<td>';
		echo $r['user'];
		echo '</td>';
		echo '<td>';
		echo $r['firstname'];
		echo '</td>';
		echo '<td>';
		echo $r['lastname'];
		echo '</td>';
		echo '<td>';
		echo '<select name="status_'.$r['id'].'" style="min-width:42px;">',"\n";
		echo '<option value="pnd"';
			if ($r['status'] == 'pnd') echo ' selected="selected"';
		echo '>Freischaltung erbeten</option>';
		echo '<option value="ack"';
			if ($r['status'] == 'ack') echo ' selected="selected"';
		echo '>Freigeschaltet</option>';
		echo '<option value="nak"';
			if ($r['status'] == 'nak') echo ' selected="selected"';
		echo '>Blockiert</option>';
		echo '</select>';
		echo '</td>';
		echo '<td>';
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='.$r['id']), '" title="', __('entfernen'), '"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
		echo '</td>';	
		}
		echo '</tr>', "\n";
		}
	echo '<tr>';
	echo '<td>';
	echo '</td>';
	echo '<td>';
	echo '</td>';
	echo '<td>';
	echo '</td>';
	echo '<td>';
	echo '</td>';
	echo '<td>';
	echo '<button type="submit" title="'. __('Speichern'). '" class="plain">
			<img alt="'. __('Speichern').'" src="'. GS_URL_PATH .'crystal-svg/16/act/filesave.png" />
		</button>';
	echo '</td>';
	echo '</tr>';
	echo '<input type="hidden" name="save" value="1" />', "\n";

echo '</form>';
echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
echo gs_form_hidden($SECTION, $MODULE), "\n";
?>

<tr>
<td>
<?php echo __('neuer Kollege:'); ?>
</td>
</tr>
<tr>
	<td>
		<input type="text" name="name" value="" size="15" maxlength="40" style="width:100px;" />
	</td>
	<td>
	</td>
	<td>
	</td>
	<td>
	</td>
	<td>
		<button type="submit" title="<?php echo __('Eintrag speichern'); ?>" class="plain">
			<img alt="<?php echo __('Speichern'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
		</button>
	
	</td>
</tr>
</tbody>
</table>
</form>
<br><br>
<form>
<?php 
echo gs_form_hidden($SECTION, $MODULE), "\n";
echo "<h2>";
echo __('Neue Authorisierung bei einem Kollegen anfordern:');
echo "</h2>";
?>
<br>
<input type="text" name="auth_name" value="" size="15" maxlength="40" style="width:100px;" /> 
<button type="submit" title="<?php echo __('Authorisierung anfordern'); ?>" class="plain">
			<img alt="<?php echo __('Authorisierung anfordern'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
		</button>
<input type="hidden" name="auth" value="1" />
</form>