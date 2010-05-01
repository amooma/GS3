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
include_once( GS_DIR .'inc/gs-fns/gs_callwaiting_activate.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callwaiting_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_clir_activate.php' );
include_once( GS_DIR .'inc/gs-fns/gs_clir_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_get.php' );
include_once( GS_DIR .'inc/group-fns.php' );
include_once( GS_DIR .'inc/ami-fns.php' );

require_once( GS_DIR .'lib/yadb/yadb_mptt.php' );
echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";





if (@$_REQUEST['action']=='save') {
	
	$clir_internal = (@$_REQUEST['clir-internal']=='yes' ? 'yes':'no');
	$clir_external = (@$_REQUEST['clir-external']=='yes' ? 'yes':'no');
	gs_clir_activate( $_SESSION['sudo_user']['name'], 'internal', $clir_internal );
	gs_clir_activate( $_SESSION['sudo_user']['name'], 'external', $clir_external );
	
	$cw = !! @$_REQUEST['callwaiting'];
	# setting this reboots phone, so check if it has really changed
	$cw_old = gs_callwaiting_get( $_SESSION['sudo_user']['name'] );
	if (! isGsError($cw_old)) {
		if ($cw != $cw_old)
			gs_callwaiting_activate( $_SESSION['sudo_user']['name'], $cw );
	}
	
}

$user_groups  = gs_group_members_groups_get(
	array(@$_SESSION['sudo_user']['info']['id']), 'user');
$queues_allowed = gs_group_members_get( gs_group_permissions_get(
	$user_groups, 'login_queues', 'queue'));
$user = gs_user_get( $_SESSION['sudo_user']['name'] );
$queue_ids = @$_REQUEST['queue_id'];

if (@$_REQUEST['action']=='loginqueue' && (! empty($queue_ids))) {
	$ami = new AMI;
	$ami->ami_login('gscc', 'gspass', '127.0.0.1', 5038);
	$agent = $DB->executeGetOne('SELECT `name` FROM `ast_sipfriends` WHERE `_user_id`='.$user['id']);
	// Ich vertraue  gs_group_members_get
	// in dem array sind NUR int()
	$queue_ids = array_intersect($queue_ids, $queues_allowed);
	$rs = $DB->execute(
		'SELECT `_id`, `name` FROM `ast_queues`  WHERE `_host_id`='.$user['host_id'].' 
		AND `_id` IN ('.implode(",", $queue_ids).') 
		AND `_id` NOT IN 
		(SELECT `_queue_id` FROM `ast_queue_members` WHERE `_user_id`='.$user['id']. ')'
	); 
	while ($queue_map = $rs->fetchRow()) {
		$penalty = $DB->executeGetOne('SELECT `penalty` FROM `penalties` WHERE `_user_id`='.$user['id'].' AND `_queue_id`='.$queue_map['_id']);
		if (! $penalty) $penalty='DEFAULT';
		$interface = 'SIP/'.$agent;
		$DB->execute(
			'INSERT INTO `ast_queue_members` SET 
			`_user_id` ='.(int)$user['id'].', '. 
			'`_queue_id` ='.(int)$queue_map['_id'].', '.
			'`queue_name`= \''.$queue_map['name'].'\',
			`interface` = \''.$interface.'\',
			`penalty`='.$penalty
		);
		$ami->ami_send_command(
			'Action: Queuelog'."\n".
			'Queue: '.$queue_map['name']."\n".
			'Interface: '.$agent."\n".
			'Event: AGENTLOGIN'."\r\n\r\n"
		);
	}
$ami->ami_logout();
}

if (@$_REQUEST['action']=='logoutqueue'  && (! empty($queue_ids))) {
	$ami = new AMI;
	$ami->ami_login('gscc', 'gspass', '127.0.0.1', 5038);
	$agent = $DB->executeGetOne('SELECT `name` FROM `ast_sipfriends` WHERE `_user_id`='.$user['id']);
	$interface = 'SIP/'.$agent;
	$queue_ids = array_intersect($queue_ids, $queues_allowed);
	$rs = $DB->execute(
		'SELECT `queue_name` FROM `ast_queue_members` WHERE 
		`_user_id`='.$user['id'].'
		AND `static`= 0
		AND `_queue_id` IN ('.implode(",", $queue_ids).')'
	);
	while ($queue_map = $rs->fetchRow()) {
			$ami->ami_send_command(
				'Action: Queuelog'."\n".
				'Queue: '.$queue_map['queue_name']."\n".
				'Interface: '.$agent."\n".
				'Event: AGENTLOGOFF'."\r\n\r\n"
			);
	}
	$DB->execute('DELETE from `ast_queue_members` WHERE
		`_user_id`='.$user['id'].'
		AND `static`= 0 
		AND `_queue_id` IN ('.implode(",", $queue_ids).')'
	);}


$clir = gs_clir_get( $_SESSION['sudo_user']['name'] );
if (isGsError($clir)) {
	echo __('Fehler beim Abfragen.'), '<br />', $clir->getMsg();
	die();
}

$callwaiting = gs_callwaiting_get( $_SESSION['sudo_user']['name'] );
if (isGsError($callwaiting)) {
	echo __('Fehler beim Abfragen.'), '<br />', $callwaiting->getMsg();
	die();
}

?>

<form method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<input type="hidden" name="action" value="save" />

<table cellspacing="1">
<thead>
<tr>
	<th colspan="2"><?php echo __('Dienstmerkmale'); ?></th>
	<th class="quickchars">&nbsp;</th>
</tr>
</thead>
<tbody>
<tr>
	<td style="width:170px;"><?php echo __('CLIR nach intern'); ?></td>
	<td style="width:130px;">
		<input type="radio" name="clir-internal" value="yes" id="ipt-clir-internal-yes" <?php if ($clir['internal_restrict']=='yes') echo 'checked="checked" '; ?>/>
			<label for="ipt-clir-internal-yes"><?php echo __('an'); ?></label>
		<input type="radio" name="clir-internal" value="no" id="ipt-clir-internal-no" <?php if ($clir['internal_restrict'] != 'yes') echo 'checked="checked" '; ?>/>
			<label for="ipt-clir-internal-no"><?php echo __('aus'); ?></label>
	</td>
	<td rowspan="2" style="width:200px;">
		<small><?php echo __('Rufnummernunterdr&uuml;ckung. Bei <q>an</q> wird die Nummer unterdr&uuml;ckt<sup>[1]</sup>, bei <q>aus</q> wird sie mitgesendet.'); ?></small>
	</td>
</tr>
<tr>
	<td><?php echo __('CLIR nach extern'); ?></td>
	<td>
		<input type="radio" name="clir-external" value="yes" id="ipt-clir-external-yes" <?php if ($clir['external_restrict']=='yes') echo 'checked="checked" '; ?>/>
			<label for="ipt-clir-external-yes"><?php echo __('an'); ?></label>
		<input type="radio" name="clir-external" value="no" id="ipt-clir-external-no" <?php if ($clir['external_restrict'] != 'yes') echo 'checked="checked" '; ?>/>
			<label for="ipt-clir-external-no"><?php echo __('aus'); ?></label>
	</td>
</tr>
<tr>
	<td><?php echo __('Anklopfen'); ?></td>
	<td>
		<input type="radio" name="callwaiting" value="1" id="ipt-callwaiting-1" <?php if ($callwaiting) echo 'checked="checked" '; ?>/>
			<label for="ipt-callwaiting-1"><?php echo __('an'); ?></label>
		<input type="radio" name="callwaiting" value="0" id="ipt-callwaiting-0" <?php if (! $callwaiting) echo 'checked="checked" '; ?>/>
			<label for="ipt-callwaiting-0"><?php echo __('aus'); ?></label>
	</td>
	<td>
		<small><?php echo __('Das Verhalten ist ggf. von Ihrem Endger&auml;t abh&auml;ngig.'); ?></small>
	</td>
</tr>


<tr>
	<td colspan="6" class="quickchars r">
		<br />
		<br />
		<button type="submit">
			<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
			<?php echo __('Speichern'); ?>
		</button>
	</td>
</tr>
</tbody>
</table>
</form>
<?php
$rs = $DB->execute('SELECT `_queue_id` FROM `ast_queue_members` WHERE  `static`=0 AND `_user_id` ='.$user['id']); 
while ( $queues = $rs->fetchrow()) {
	$queues_in =  explode(',', $queues['_queue_id']);
}
if (empty($queues_in)) {
	$queues_avail = $queues_allowed;
} else {
	$queues_avail = array_diff( $queues_allowed, $queues_in); 
}
?>
<table>
<tr><th colspan="2">
<?php echo __('Warteschlangen');?>
</th></tr>
<tr><th>
<?php echo __('Verf&uuml;gbare');?>
</th><th>
<?php echo __('Angemeldet');?>
</th></tr>
<tr><td colspan="2"><small>
<?php echo __('Mehrfachauswahl mit gedr&uuml;ckter <q>STRG</q> oder <q>SHIFT</q> Taste m&ouml;glich.');?>
</small></td></tr>
<tr><td>
<form method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<input type="hidden" name="action" value="loginqueue" />
<select name="queue_id[]" size="5" multiple="multiple">
<?php
$rs = $DB->execute(
	'SELECT `_id`, `name`, `_title` FROM `ast_queues`  
	WHERE `_host_id`='.$user['host_id'].' 
	AND `_id` IN ('.implode(",", $queues_avail).') 
	AND `_id` NOT IN 
	(SELECT `_queue_id` FROM `ast_queue_members` WHERE `_user_id`='.$user['id']. ')'
);
while ( $queue_map = $rs->fetchrow()) {
	echo $queue_map['name'], $queue_map['_title'], "\n";
	echo '<option value="', (int)$queue_map['_id'], '"', 'title="', htmlEnt( $queue_map['_title']),'"';
	echo '>',  $queue_map['name'], ' ', $queue_map['_title'], '</option>', "\n";
}
?>
</select>
<?php
echo '<button type="submit" title="', __('Anmelden'), '" class="plain">';
echo '<img alt="', __('Anmelden') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/next.png" /></button>', "\n";
?></form>
</td><td>
<form method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<input type="hidden" name="action" value="logoutqueue" />
<?php
echo '<button type="submit" title="', __('Abmelden'), '" class="plain">';
echo '<img alt="', __('Abmelden') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/previous.png" /></button>', "\n";
?>
<select name="queue_id[]" size="5" multiple="multiple">
<?php
$rs = $DB->execute('SELECT `queue_name`, `_title`, `_queue_id`, `static` FROM `ast_queue_members`, `ast_queues` WHERE `_user_id`='.$user['id'].'  AND `_queue_id`=`_id`');
while ( $queue_map = $rs->fetchrow()) {
	echo '<option value="', (int)$queue_map['_queue_id'], '"', 'title="', htmlEnt( $queue_map['_title']),'"';
	if ($queue_map['static'] == '1') echo 'disabled="disabled"'; 
	echo '>',  $queue_map['queue_name'], ' ', $queue_map['_title'], '</option>', "\n";
}
?>
</select>
</form>
</td>

</tr></table>
<br />
<br />
<p class="small" style="max-width:48em;">
	<sup>[1]</sup>
	<?php echo __('Bei Anrufen nach extern wird u.U. trotz CLIR nur die Nebenstelle unterdr&uuml;ckt aber vom Provider die Hauptnummer (-0) des Anlagenanschlusses &uuml;bertragen.'); ?>
</p>