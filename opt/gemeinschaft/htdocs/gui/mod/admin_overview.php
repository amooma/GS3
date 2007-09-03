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
function count_phones_configured( $DB ) {
	$num = (int)$DB->executeGetOne( 'SELECT COUNT(*) FROM `phones`');
	return $num;
}
function count_users_logged_in( $DB ) {
	$num = (int)$DB->executeGetOne(
'SELECT COUNT(*)
FROM
	`phones` `p` JOIN
	`users` `u` ON (`u`.`id`=`p`.`user_id`)
WHERE `u`.`nobody_index` IS NULL'
	);
	return $num;
}

function get_last_phone_date( $DB ) {
	$timestamp = (int)$DB->executeGetOne( 'SELECT MAX(`added`) FROM `phones`');
	return date('j.n.Y, H:i', $timestamp);
}


?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:270px;">
		<span class="sort-col"><?php echo __('Benutzer'); ?></span>
	</th>
	<th style="width:200px;">
		&nbsp;
	</th>
</tr>
</thead>
<tbody>
<tr>
	<th><?php echo __('Eingerichtete Benutzer'); ?>:</th>
	<td><?php echo count_users_configured($DB); ?></td>
</tr>
<tr>
	<th><?php echo __('Eingeloggte Benutzer'); ?>:</th>
	<td><?php echo count_users_logged_in($DB); ?></td>
</tr>
</tbody>
</table>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:270px;">
		<span class="sort-col"><?php echo __('Endger&auml;te'); ?></span>
	</th>
	<th style="width:200px;">
		&nbsp;
	</th>
</tr>
</thead>
<tbody>
<tr>
	<th><?php echo __('Eingerichtete Endger&auml;te'); ?>:</th>
	<td><?php echo count_phones_configured($DB); ?></td>
</tr>
<tr>
	<th><?php echo __('Verwendete Endger&auml;te'); ?>:</th>
	<td><?php echo count_users_logged_in($DB); ?></td>
</tr>
<tr>
	<th><?php echo __('Letztes Endger&auml;t eingetragen'); ?>:</th>
	<td><?php echo get_last_phone_date($DB); ?></td>
</tr>

</tbody>
</table>
