<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2010, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
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
include_once( GS_DIR .'inc/group-fns.php' );
require_once( GS_DIR .'inc/mongr.php');

define( 'GS_EXT_UNKNOWN',	255); # unknown status
define( 'GS_EXT_IDLE',		0);  # all devices idle (but registered)
define( 'GS_EXT_INUSE',		1);  # one or more devices busy
define( 'GS_EXT_BUSY',		2);  # all devices busy
define( 'GS_EXT_OFFLINE',	4);  # all devices unreachable/not registered
define( 'GS_EXT_RINGING',	8);  # one or more devices ringing
define( 'GS_EXT_RINGINUSE',	9);  # ringing and in use
define( 'GS_EXT_ONHOLD',	16); # all devices on hold

define( 'ST_FREE',		'#fffd3b');
define( 'ST_INUSE',		'#00fd02');
define( 'ST_OFFLINE',		'#fdfeff');
define( 'ST_RINGING',		'#008000');
define( 'ST_RINGINUSE',		'#0080FF');
define( 'ST_UNKNOWN',		'#ff5c43');
define( 'ST_ONHOLD',		'#fd02fd');

define( 'CQ_DESKTOP_BG',	'#ffffff');
define( 'CQ_WINDOW_BG',		'#d4d0c7');
define( 'CQ_WINDOW_FG',		'#000000');

$colors = array(
	GS_EXT_UNKNOWN		=> ST_UNKNOWN,
	GS_EXT_IDLE		=> ST_FREE,
	GS_EXT_INUSE		=> ST_INUSE,
	GS_EXT_BUSY		=> ST_INUSE,
	GS_EXT_OFFLINE		=> ST_OFFLINE,
	GS_EXT_RINGING		=> ST_RINGING,
	GS_EXT_RINGINUSE	=> ST_RINGINUSE,
	GS_EXT_ONHOLD		=> ST_ONHOLD
);

function _secs_to_minsecs( $s )
{
	$s = (int)$s;
	$m = floor($s/60);
	$s = $s - $m*60;
	return $m .':'. str_pad($s, 2, '0', STR_PAD_LEFT);
}

function queue_timeout($queue_id, $last_min)
{
	global $CDR_DB;

	$sql_time    = '(`timestamp` >= '.(time() - $last_min * 60).')';

	$ret = (int)@$CDR_DB->executeGetOne(
	'SELECT COUNT(*) FROM `queue_log` WHERE
	`queue_id`='. $queue_id .'
	AND `event`=\'_EXIT\'
	AND `reason`=\'TIMEOUT\'
	AND '. $sql_time
	);

	return $ret;
}

function queue_abandoned($queue_id, $last_min)
{
	global $CDR_DB;

	$sql_time    = '(`timestamp` >= '.(time() - $last_min * 60).')';
	
	$ret = (int)@$CDR_DB->executeGetOne(
	'SELECT COUNT(*) FROM `queue_log` WHERE
	`queue_id`='. $queue_id .'
	AND `event`=\'_EXIT\'
	AND `reason`=\'ABANDON\'
	AND '. $sql_time
	);

	return $ret;
}

function queue_answered($queue_id, $last_min)
{
	global $CDR_DB;

	$sql_time    = '(`timestamp` >= '.(time() - $last_min * 60).')';

	$ret = (int)@$CDR_DB->executeGetOne(
	'SELECT COUNT(*) FROM `queue_log` WHERE
	`queue_id`='. $queue_id .'
	AND `event`=\'_CONNECT\'
	AND '. $sql_time
	);

	return $ret;
}

function queue_calls($queue_id, $last_min)
{
	global $CDR_DB;

	$sql_time    = '(`timestamp` >= '.(time() - $last_min * 60).')';

	$ret = (int)@$CDR_DB->executeGetOne(
	'SELECT COUNT(*) FROM `queue_log` WHERE
	`queue_id`='. $queue_id .'
	AND `event`=\'_ENTER\'
	AND '. $sql_time
	);

	return $ret;
}

function queue_waitmin($queue_id, $last_min)
{
	global $CDR_DB;

	$sql_time    = '(`timestamp` >= '.(time() - $last_min * 60).')';

	$ret = (int)@$CDR_DB->executeGetOne(
	'SELECT MIN(`waittime`) FROM `queue_log` WHERE
	`queue_id`='. $queue_id .'
	AND `event`=\'_EXIT\'
	AND `reason`<>\'INCOMPAT\'
	AND '. $sql_time);

	return $ret;
}



function queue_waitavg($queue_id, $last_min)
{
	global $CDR_DB;
	
	$sql_time    = '(`timestamp` >= '.(time() - $last_min * 60).')';

	$ret = (int)@$CDR_DB->executeGetOne(
	'SELECT AVG(`waittime`) FROM `queue_log` WHERE
	`queue_id`='. $queue_id .'
	AND `event`=\'_EXIT\'
	AND `reason`<>\'INCOMPAT\'
	AND '. $sql_time);

	return $ret;
}

function queue_waitmax($queue_id, $last_min)
{
	global $CDR_DB;
	
	$sql_time    = '(`timestamp` >= '.(time() - $last_min * 60).')';
	
	$ret = (int)@$CDR_DB->executeGetOne(
	'SELECT MAX(`waittime`) FROM `queue_log` WHERE
	`queue_id`='. $queue_id .'
	AND `event`=\'_EXIT\'
	AND `reason`<>\'INCOMPAT\'
	AND '. $sql_time);
	
	return $ret;
}

function queue_callavg($queue_id, $last_min)
{
	global $CDR_DB;

	$sql_time    = '(`timestamp` >= '.(time() - $last_min * 60).')';

	$ret = (int)@$CDR_DB->executeGetOne(
	'SELECT AVG(`calldur`) FROM `queue_log` WHERE
	`queue_id`='. $queue_id .'
	AND `event`=\'_EXIT\'
	AND `reason`<>\'INCOMPAT\'
	AND '. $sql_time);

	return $ret;
}

function queue_callmax($queue_id, $last_min)
{
	global $CDR_DB;

	$sql_time    = '(`timestamp` >= '.(time() - $last_min * 60).')';

	$ret = (int)@$CDR_DB->executeGetOne(
	'SELECT MAX(`calldur`) FROM `queue_log` WHERE
	`queue_id`='. $queue_id .'
	AND `event`=\'_EXIT\'
	AND `reason`<>\'INCOMPAT\'
	AND '. $sql_time);

	return $ret;
}

function queue_callmin($queue_id, $last_min)
{
	global $CDR_DB;

	$sql_time    = '(`timestamp` >= '.(time() - $last_min * 60).')';

	$ret = (int)@$CDR_DB->executeGetOne(
	'SELECT MIN(`calldur`) FROM `queue_log` WHERE
	`queue_id`='. $queue_id .'
	AND `event`=\'_EXIT\'
	AND `reason`<>\'INCOMPAT\'
	AND '. $sql_time);

	return $ret;
}

function html_select($name, $items, $default = 0) {
       
       echo '<select name="'.$name.'" onChange="checkval(this)">', "\n";
       foreach ($items as $title => $value) {
		if ($value == $default)
			echo '<option value="',$value,'" selected="selected">',$title,'</option>' ,"\n";
		else
			echo '<option value="',$value,'">',$title,'</option>' ,"\n";
       }
       echo '</select>', "\n";

}

function select_timebase($name, $default = 0)
{
	$items = array(
		'--' => 0,
		'5m' => 5,
		'10m' => 10,
		'15m' => 15,
		'30m' => 30,
		'1h' => 60,
		'2h' => 120,
		'3h' => 180,
		'8h' => 480,
		'24h' => 1440,
	);

	html_select($name, $items, $default);
}

function queue_defaults(&$queue, $fill = False) {

       if (!is_array($queue))  $queue = array();
       
       $queue['active']			= True;
       $queue['display_columns']	= 3;
       $queue['display_width']		= 550;
       $queue['display_height']		= 185;
       $queue['display_calls']		= 0;
       $queue['display_answered']	= 0;
       $queue['display_abandoned']	= 15;
       $queue['display_timeout']	= 0;
       $queue['display_wait_max']	= 15;
       $queue['display_wait_min']	= 0;
       $queue['display_wait_avg']	= 15;
       $queue['display_call_max']	= 0;
       $queue['display_call_min']	= 0;
       $queue['display_call_avg']	= 0;
       $queue['display_all']		= 0;
       $queue['display_extension']	= 0;
       $queue['display_name']		= 4;

       if ($fill) {
		foreach ($fill as $key => $value) {
			if ((string)$value != '') {
				$queue[$key] = $value;
			}
		}
       }
}

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

$action = @$_REQUEST['action'];

$user_id = @$_SESSION['sudo_user']['info']['id'];

if (!$user_id) exit;

$user_groups  = gs_group_members_groups_get(Array($user_id), 'user');
$queue_ids = gs_group_members_get(gs_group_permissions_get($user_groups, 'monitor_queues', 'queue'));

if ($action == 'save') {

	$colors = array();
       
	for ($status = 0; $status <= 255; ++$status) {
		if (array_key_exists('ec'.$status, $_REQUEST)) {
			$colors[$status] = trim($_REQUEST['ec'.$status]);
		}
	}

	$queues = array();

	foreach ($queue_ids as $queue_id) {
		$queue = array();

		if (array_key_exists('qa'.$queue_id, $_REQUEST) && $_REQUEST['qa'.$queue_id] == "on")
			$queue['active'] = 1;
		else
			$queue['active'] = 0;

		if (array_key_exists('qw'.$queue_id, $_REQUEST))
			$queue['display_width'] = (int)$_REQUEST['qw'.$queue_id];
		if (array_key_exists('qh'.$queue_id, $_REQUEST))
			$queue['display_height'] = (int)$_REQUEST['qh'.$queue_id];
		if (array_key_exists('qx'.$queue_id, $_REQUEST))
			$queue['display_columns'] = (int)$_REQUEST['qx'.$queue_id];
		if (array_key_exists('qcs'.$queue_id, $_REQUEST))
			$queue['display_calls'] = (int)$_REQUEST['qcs'.$queue_id];
		if (array_key_exists('qcc'.$queue_id, $_REQUEST))
			$queue['display_answered'] = (int)$_REQUEST['qcc'.$queue_id];
		if (array_key_exists('qca'.$queue_id, $_REQUEST))
			$queue['display_abandoned'] = (int)$_REQUEST['qca'.$queue_id];
		if (array_key_exists('qct'.$queue_id, $_REQUEST))
			$queue['display_timeout'] = (int)$_REQUEST['qct'.$queue_id];
		if (array_key_exists('qwx'.$queue_id, $_REQUEST))
			$queue['display_wait_max'] = (int)$_REQUEST['qwx'.$queue_id];
		if (array_key_exists('qwn'.$queue_id, $_REQUEST))
			$queue['display_wait_min'] = (int)$_REQUEST['qwn'.$queue_id];
		if (array_key_exists('qwa'.$queue_id, $_REQUEST))
			$queue['display_wait_avg'] = (int)$_REQUEST['qwa'.$queue_id];
		if (array_key_exists('qtx'.$queue_id, $_REQUEST))
			$queue['display_call_max'] = (int)$_REQUEST['qtx'.$queue_id];
		if (array_key_exists('qtn'.$queue_id, $_REQUEST))
			$queue['display_call_min'] = (int)$_REQUEST['qtn'.$queue_id];
		if (array_key_exists('qta'.$queue_id, $_REQUEST))
			$queue['display_call_avg'] = (int)$_REQUEST['qta'.$queue_id];
		if (array_key_exists('qax'.$queue_id, $_REQUEST))
			$queue['display_extension'] = (int)$_REQUEST['qax'.$queue_id];
		if (array_key_exists('qan'.$queue_id, $_REQUEST))
			$queue['display_name'] = (int)$_REQUEST['qan'.$queue_id];
		$queues[$queue_id] = $queue;
	}

	$page = array();

	if (array_key_exists('qx', $_REQUEST))
			$page['columns'] = (int)$_REQUEST['qx'];
	if (array_key_exists('qu', $_REQUEST))
			$page['update'] = (int)$_REQUEST['qu'];
	if (array_key_exists('qr', $_REQUEST))
			$page['reload'] = (int)$_REQUEST['qr'];

	if ($page) {
		$sql_query =
		'REPLACE `monitor`
		(`user_id`, `type`, `'.implode('`,`', array_keys($page)).'`)
		VALUES ('.$user_id.', 1,'.implode(',', $page).')';

		$rs = $DB->execute( $sql_query );
	}

	if ($colors) {
		foreach( $colors as $key => $value ) {
			$sql_query =
			'REPLACE `monitor_colors`
			(`user_id`, `type`, `status`, `color`)
			VALUES ('.$user_id.', 1,'.$key.',\''.$value.'\')';

			$rs = $DB->execute( $sql_query );
		}
        }

	if ($queues) {
		foreach( $queues as $queue_id => $queue ) {
			$sql_query =
			'REPLACE `monitor_queues`
			(`user_id`, `queue_id`, `'.implode('`,`', array_keys($queue)).'`)
			VALUES ('.$user_id.','.$queue_id.','.implode(',', $queue).')';

			$rs = $DB->execute( $sql_query );
		}
	}

	$action = "edit";
}

$sql_query =
'SELECT
`q`.`_id` `id`,
`q`.`name` `ext`,
`q`.`_title` `title`,
`h`.`host`,
`m`.`active`,
`m`.`display_columns`,
`m`.`display_width`,
`m`.`display_height`,
`m`.`display_calls`,
`m`.`display_answered`,
`m`.`display_abandoned`,
`m`.`display_timeout`,
`m`.`display_wait_max`,
`m`.`display_wait_min`,
`m`.`display_wait_avg`,
`m`.`display_call_max`,
`m`.`display_call_min`,
`m`.`display_call_avg`,
`m`.`display_extension`,
`m`.`display_name`
FROM
`ast_queues` `q` JOIN
`hosts` `h` ON (`h`.`id`=`q`.`_host_id`)
LEFT JOIN `monitor_queues` `m` ON (`q`.`_id` = `m`.`queue_id` AND `m`.`user_id` = '.$user_id.')
WHERE '.
	( count($queue_ids) > 0
	? ' `q`.`_id` IN ('.implode(',',$queue_ids).') '
	: ' FALSE '
	).' '.
'ORDER BY `q`.`name` ASC';

$rs = $DB->execute( $sql_query );

$queues = array();
$queue_ids_active = array();

if ($rs) {
	while ($r = $rs->fetchRow()) {
		queue_defaults($queue, $r);
		$queues[$r['ext']] = $queue;
		if ($queue['active']) $queue_ids_active[] = $queue['id'];
	}
}

$sql_query =
'SELECT `display_x`, `display_y`, `columns`, `update`, `reload`
FROM `monitor`
WHERE `user_id` = '.$user_id.'
AND `type` = 1
LIMIT 1';

$rs = $DB->execute( $sql_query );

$queuemon_data = array();
$queuemon_data['columns'] = 2;
$queuemon_data['update'] = 2;
$queuemon_data['reload'] = 120;

if ($rs->numRows() == 1) {
	$r = $rs->fetchRow();
	foreach ($r as $key => $value) {
		if ($value != NULL)  $queuemon_data[$key] = $value;
	}
}

$color_names = array(
	GS_EXT_UNKNOWN 		=> 'unbekannt',
	GS_EXT_IDLE		=> 'frei',
	GS_EXT_INUSE		=> 'anruf',
	GS_EXT_BUSY		=> 'besetzt',
	GS_EXT_OFFLINE		=> 'offline',
	GS_EXT_RINGING		=> 'klingelt',
	GS_EXT_RINGINUSE	=> 'anklopfen',
	GS_EXT_ONHOLD		=> 'halten'
);

$colors = array(
	GS_EXT_UNKNOWN		=> ST_UNKNOWN,
	GS_EXT_IDLE		=> ST_FREE,
	GS_EXT_INUSE		=> ST_INUSE,
	GS_EXT_BUSY		=> ST_INUSE,
	GS_EXT_OFFLINE		=> ST_OFFLINE,
	GS_EXT_RINGING		=> ST_RINGING,
	GS_EXT_RINGINUSE	=> ST_RINGINUSE,
	GS_EXT_ONHOLD		=> ST_ONHOLD
);

$sql_query =
'SELECT `status`, `color`
FROM `monitor_colors`
WHERE `user_id` = '.$user_id.'
AND `type` = 1';

$rs = $DB->execute( $sql_query );

if ($rs) {
	while ($r = $rs->fetchRow()) {
		$colors[$r['status']] = $r['color'];
	}
}

if ($action == 'edit') {
	
	$qh_val = 1;
?>

<script type="text/javascript">

function changebgcolor(color_id, color_value) {
	var el_obj = document.getElementById(color_id);
	el_obj.style.background = color_value;
}

function checkval(el_obj) {

	if (el_obj.name == 'qx') {
		var input_value = parseInt( el_obj.value );
		if ((input_value > 0) && (input_value < 11)) {
			el_obj.value = input_value;
		} else {
			el_obj.value = 2;
		}
		return 1;
	}

	if (el_obj.name == 'qu') {
		var input_value = parseInt( el_obj.value );
		if ((input_value > 0) && (input_value < 3600)) {
			el_obj.value = input_value;
		} else {
			el_obj.value = 4;
		}
		return 1;
	}

	if (el_obj.name == 'qr') {
		var input_value = parseInt( el_obj.value );
		if ((input_value > 0) && (input_value < 3600)) {
			el_obj.value = input_value;
		} else {
			el_obj.value = 2;
		}
		return 1;
	}

	if (el_obj.name.substr(0,2) == 'qw') {
		var input_value = parseInt( el_obj.value );
		if ((input_value > 20) && (input_value < 2049)) {
			el_obj.value = input_value;
		} else {
			el_obj.value = 550;
		}
		return 1;
	}

	if (el_obj.name.substr(0,2) == 'qh') {
		var input_value = parseInt( el_obj.value );
		if ((input_value > 20) && (input_value < 1025)) {
			el_obj.value = input_value;
		} else {
			el_obj.value = 160;
		}
		return 1;
	}

	if (el_obj.name.substr(0,2) == 'qx') {
		var input_value = parseInt( el_obj.value );
		if ((input_value > 0) && (input_value < 11)) {
			el_obj.value = input_value;
		} else {
			el_obj.value = 2;
		}
		return 1;
	}

	if (el_obj.name.substr(0,3) == 'qat') {
		var queue_id = parseInt( el_obj.name.substr(3) );;
		var input_value = parseInt( el_obj.value );

		if (document.forms['edit']['qcs'+queue_id].value != 0) document.forms['edit']['qcs'+queue_id].value = input_value;
		if (document.forms['edit']['qcc'+queue_id].value != 0) document.forms['edit']['qcc'+queue_id].value = input_value;
		if (document.forms['edit']['qca'+queue_id].value != 0) document.forms['edit']['qca'+queue_id].value = input_value;
		if (document.forms['edit']['qct'+queue_id].value != 0) document.forms['edit']['qct'+queue_id].value = input_value;
		if (document.forms['edit']['qwx'+queue_id].value != 0) document.forms['edit']['qwx'+queue_id].value = input_value;
		if (document.forms['edit']['qwn'+queue_id].value != 0) document.forms['edit']['qwn'+queue_id].value = input_value;
		if (document.forms['edit']['qwa'+queue_id].value != 0) document.forms['edit']['qwa'+queue_id].value = input_value;
		if (document.forms['edit']['qtx'+queue_id].value != 0) document.forms['edit']['qtx'+queue_id].value = input_value;
		if (document.forms['edit']['qtn'+queue_id].value != 0) document.forms['edit']['qtn'+queue_id].value = input_value;
		if (document.forms['edit']['qta'+queue_id].value != 0) document.forms['edit']['qta'+queue_id].value = input_value;
		el_obj.value = 0;
		
		return 1;
	}
}

function screenres() {
	var el_obj = document.getElementById('screen_res');
	el_obj.innerHTML = screen.width+"x"+screen.height;
}

function page_init() {
	screenres();
}

window.onload=page_init

</script>

<?php
	echo '<form name="edit" method="post" action="?f=0">',"\n";
	echo gs_form_hidden($SECTION, $MODULE);
	echo '<input type="hidden" name="action" value="save" />' ,"\n";
?>

<table cellspacing="1">
<thead>
	<tr>
		<th style="min-width:24em;" colspan="3">
			Queue Monitor <?php echo __('Anzeigeoptionen'); ?>
		</th>
	</tr>
</thead>
<tbody>
	<tr>
		<th style="min-width:24em;" colspan="3">
			<?php echo __('Seite'); ?>
		</th>
	</tr>
	<tr>
		<td style="width:1em;"></td>
		<th style="min-width:12em;">
			<?php echo __('Aufl&ouml;sung'); ?>
		</th>
		<td style="min-width:12em;">
			<span id="screen_res"></span>
			<?php echo __('Pixel'); ?>
		</td>
	</tr>
	<tr>
		<td style="width:1em;"></td>
		<th style="min-width:12em;">
			&#8660; <?php echo __('Horiz. Warteschlangen'); ?>
		</th>
		<td style="min-width:12em;">
		<input type="text" onChange="checkval(this)" name="qx" value="<?php echo $queuemon_data['columns']; ?>" size="2" maxlength="2" />
		</td>
	</tr>
	<tr>
		<td style="width:1em;"></td>
		<th style="min-width:12em;">
			&#10226; <?php echo __('Update Interval'); ?>
		</th>
		<td style="min-width:12em;">
		<input type="text" onChange="checkval(this)" name="qu" value="<?php echo $queuemon_data['update']; ?>" size="2" maxlength="4" />
		<?php echo __('Sec.'); ?>
		</td>
	</tr>
		<tr>
		<td style="width:1em;"></td>
		<th style="min-width:12em;">
			&#10227; <?php echo __('Reload Interval'); ?>
		</th>
		<td style="min-width:12em;">
		<input type="text" onChange="checkval(this)" name="qr" value="<?php echo $queuemon_data['reload']; ?>" size="2" maxlength="4" />
		<?php echo __('Min.'); ?>
		</td>
	</tr>
	<tr>
		<th id="colors" style="min-width:12em;" colspan="3">
			<?php echo __('Farben'); ?>
		</th>
	</tr>
<?php
	foreach ($colors as $color_id => $value) {
		echo '<tr>',"\n";
		echo '<td></td>',"\n";
		echo '<th style="min-width:12em; background:',$value,';">';
		echo $color_names[$color_id];
		echo '</th>',"\n";
		echo '<td id="ec_'.$color_id.'" style="min-width:12em; background:',$value,';">';
		echo '<input type="text" onKeyup="changebgcolor(\'ec_'.$color_id.'\', this.value)" name="ec'.$color_id.'" value="'.$value.'" size="10" maxlength="10"" />';
		echo '</td>',"\n";
		echo '</tr>',"\n";
	}
?>

</tbody>
</table>

<table cellspacing="1">
<thead>
	<tr>
		<th style="min-width:24em;" colspan="2">
			<?php echo __('Warteschlangen'); ?>
		</th>
		<th style="min-width:2em;" colspan="2">
			<?php echo __('Gr&ouml;&szlig;e'); ?>
		</th>
		<th style="min-width:2em;" colspan="1">
			<?php echo __('Agenten'); ?>
		</th>
		<th style="min-width:2em;" colspan="4">
			<?php echo __('Anrufe'); ?>
		</th>
		<th style="min-width:2em;" colspan="3">
			&#9719; <?php echo __('Wartezeit'); ?>
		</th>
		<th style="min-width:2em;" colspan="3">
			&#9742; <?php echo __('Sprachzeit'); ?>
		</th>
		<th/>
		<th style="min-width:2em;" colspan="2">
			<?php echo __('Agentenanzeige'); ?>
		</th>
	</tr>
</thead>
<tbody>
	<tr>
		<th style="width:1em;" colspan="1"></th>
		<th style="min-width:2em;" colspan="1">
			<?php echo __('Nummer'); ?>
		</th>
		<th style="width:2em;" colspan="1">
			&#8660; <?php echo __('Pixel'); ?>
		</th>
		<th style="width:2em;" colspan="1">
			&#8661; <?php echo __('Pixel'); ?>
		</th>
		<th style="width:2em;" colspan="1">
			&#8660;
		</th>
		<th style="width:2em;" colspan="1">
			&#8721; <?php echo __('Gesamt'); ?>
		</th>
		<th style="width:2em;" colspan="1">
			&#9742; <?php echo __('Angen.'); ?>
			</th>
		<th style="width:2em;" colspan="1">
			&#9822; <?php echo __('Absprung'); ?>
		</th>
		<th style="width:2em;" colspan="1">
			&#9719; <?php echo __('Timeout'); ?>
		</th>
		<th style="width:2em;" colspan="1">
			&#8657; <?php echo __('max.'); ?>
		</th>
		<th style="width:2em;" colspan="1">
			&#8659; <?php echo __('min.'); ?>
		</th>
		<th style="width:2em;" colspan="1">
			&#216; <?php echo __('durch.'); ?>
		</th>
		<th style="width:2em;" colspan="1">
			&#8657; <?php echo __('max.'); ?>
		</th>
		<th style="width:2em;" colspan="1">
			&#8659; <?php echo __('min.'); ?>
		</th>
		<th style="width:2em;" colspan="1">
			&#216; <?php echo __('durch.'); ?>
		</th>
		<th style="width:2em;" colspan="1">
			&#9745;
		</th>
		<th style="width:2em;" colspan="1">
			&#9742; <?php echo __('Nebenstelle'); ?>
		</th>
		<th style="width:2em;" colspan="1">
			&#8943; <?php echo __('Namensanzeige'); ?>
		</th>
	</tr>
<?php
	foreach ($queues as $queue => $data) {
		echo '<tr>',"\n";
		echo '<td>';
		if ($data['active']) $active = ' checked ';
		else $active = '';
		echo '<input type="checkbox" name="qa'.$data['id'].'".'.$active.' />';
		echo '</td>',"\n";
		echo '<th style="min-width:12em;">';
		echo $queue," ",htmlEnt($data['title']);
		echo '</th>',"\n";
		echo '<td>';
		echo '<input type="text" onChange="checkval(this)" name="qw'.$data['id'].'" value="'.$data['display_width'].'" size="4" maxlength="4" />';
		echo '</td>',"\n";
		echo '<td>';
		echo '<input type="text" onChange="checkval(this)" name="qh'.$data['id'].'" value="'.$data['display_height'].'" size="3" maxlength="3" />';
		echo '</td>',"\n";
		echo '<td>';
		echo '<input type="text" onChange="checkval(this)" name="qx'.$data['id'].'" value="'.$data['display_columns'].'" size="2" maxlength="2" />';
		echo '</td>',"\n";
		echo '<td>';
		echo select_timebase('qcs'.$data['id'], $data['display_calls']);
		echo '</td>',"\n";
		echo '<td>';
		echo select_timebase('qcc'.$data['id'], $data['display_answered']);
		echo '</td>',"\n";
		echo '<td>';
		echo select_timebase('qca'.$data['id'], $data['display_abandoned']);
		echo '</td>',"\n";
		echo '<td>';
		echo select_timebase('qct'.$data['id'], $data['display_timeout']);
		echo '</td>',"\n";
		echo '<td>';
		echo select_timebase('qwx'.$data['id'], $data['display_wait_max']);
		echo '</td>',"\n";
		echo '<td>';
		echo select_timebase('qwn'.$data['id'], $data['display_wait_min']);
		echo '</td>',"\n";
		echo '<td>';
		echo select_timebase('qwa'.$data['id'], $data['display_wait_avg']);
		echo '</td>',"\n";
		echo '<td>';
		echo select_timebase('qtx'.$data['id'], $data['display_call_max']);
		echo '</td>',"\n";
		echo '<td>';
		echo select_timebase('qtn'.$data['id'], $data['display_call_min']);
		echo '</td>',"\n";
		echo '<td>';
		echo select_timebase('qta'.$data['id'], $data['display_call_avg']);
		echo '</td>',"\n";
		echo '<td>';
		echo select_timebase('qat'.$data['id'], $data['display_all']);
		echo '</td>',"\n";
		echo '<td>';
		$items = array(
			'--' => 0,
			_('normal') => 1,
			_('fett') => 2
		);
		echo html_select('qax'.$data['id'], $items, $data['display_extension']);
		echo '</td>',"\n";
		echo '<td>';
		$items = array(
			'--' => 0,
			_('Name') => 1,
			_('Vorname') => 2,
			_('Vorname Name') => 3,
			_('Name, Vorname') => 4,
			_('Name, V.') => 5,
			_('V. Name') => 6,
			_('V.N.') => 7,

		);
		echo html_select('qan'.$data['id'], $items, $data['display_name']);
		echo '</td>',"\n";
		echo '</tr>',"\n";
	}
?>
</tbody>
</table>
<table>
<table>
<thead>
<th colspan="4" style="height: 1em;"> </th>
</thead>
<tbody>
<tr>
</tr>
<tr>
<td colspan="1">
<button type="submit" class="plain" title="<?php echo __('Speichern') ?>"><img alt="save" src="<?php echo GS_URL_PATH ,'crystal-svg/16/act/filesave.png'; ?>" /></button>
</td>
<th colspan="1"><?php echo __('Speichern');?></th>
<td colspan="1">
<a href="?f=0" title="<?php echo __('Zur&uuml;ck') ?>"><img alt="back" src="<?php echo GS_URL_PATH ,'crystal-svg/16/act/cancel.png'; ?>" /></a>
</td>
<th colspan="1"><?php echo __('Zur&uuml;ck');?></th>

</tr>
</tbody>
</table>
</form>

<?php
}

if ($action == "") {

	$sql_query = 'SELECT `q`.`queue_name` `queue`, `s`.`name` `ext`, `u`.`firstname`, `u`.`lastname`
FROM `ast_queue_members` `q`
JOIN `ast_sipfriends` `s`
ON (`s`.`_user_id` = `q`.`_user_id`)
JOIN `users` `u`
ON (`u`.`id` = `q`.`_user_id`)
WHERE '.
	( count($queue_ids_active) > 0
	? ' `q`.`_queue_id` IN ('.implode(',',$queue_ids_active).') '
	: ' FALSE '
	).' '.
'ORDER BY `q`.`queue_name`';

	$members = array();
	$rs = $DB->execute( $sql_query );
	
	if ($rs) {
		while ($r = $rs->fetchRow()) {
			$member = array();
			$member['name'] = '';
			if (array_key_exists('display_extension',$queues[$r['queue']])) {
					if ($queues[$r['queue']]['display_extension'] == 1)
						$member['name'] .= htmlEnt($r['ext']).' ';
					if ($queues[$r['queue']]['display_extension'] == 2)
						$member['name'] .= '<b>'.htmlEnt($r['ext']).'</b> ';
				}
			if (array_key_exists('display_name',$queues[$r['queue']])) {
				if ($queues[$r['queue']]['display_name'] == 1)
					$member['name'] .= htmlEnt($r['lastname']);
				if ($queues[$r['queue']]['display_name'] == 2)
					$member['name'] .= htmlEnt($r['firstname']);
				if ($queues[$r['queue']]['display_name'] == 3)
					$member['name'] .= htmlEnt($r['firstname']).' '.htmlEnt($r['lastname']);
				if ($queues[$r['queue']]['display_name'] == 4)
					$member['name'] .= htmlEnt($r['lastname']).', '.htmlEnt($r['firstname']);
				if ($queues[$r['queue']]['display_name'] == 5)
					$member['name'] .= htmlEnt($r['lastname']).', '.htmlEnt(substr($r['firstname'],0,1)).'.';
				if ($queues[$r['queue']]['display_name'] == 6)
					$member['name'] .= htmlEnt(substr($r['firstname'],0,1)).'. '.htmlEnt($r['lastname']);
				if ($queues[$r['queue']]['display_name'] == 7)
					$member['name'] .= htmlEnt(substr($r['firstname'],0,1)).'.'.htmlEnt(substr($r['lastname'],0,1)).'.';
			}

			
			$member['ext'] = $r['ext'];
			if (!array_key_exists($r['queue'], $members))
				$members[$r['queue']] = array();
			if ($queues[$r['queue']]['active']) $members[$r['queue']][] = $member;
		}
	}

	foreach ($members as $queue => $queue_members) {
		$queues[$queue]['members'] = $queue_members;
	}
	
	$queuemon_data['queues'] = array();

	$CDR_DB = gs_db_cdr_master_connect();
	if (! $CDR_DB) {
		echo 'CDR DB error.';
		return;
	}

	$fullscreen = (int) @$_REQUEST['f'];

	$queuemon_data['queues'] = $queues;
	$queuemon_data['colors'] = $colors;
	$_SESSION['queuemon'] = $queuemon_data;

	$member_extensions = array();
	foreach ($queues as $queue => $queue_data) {
		if (!array_key_exists('members', $queue_data)) {
			 $queues[$queue]['members'] = array();
			 continue;
		}

		foreach ($queue_data['members'] as $member) {
			if (array_key_exists($member['ext'], $member_extensions)) {
				$member_extensions[$member['ext']][] = $queue;
			} else {
				$queue_array = array();
				$queue_array[] = $queue;
				$member_extensions[$member['ext']] = $queue_array;
			}
		}
	}

?>
<script type="text/javascript">

var http = false;
var counter = 0;
var counter_fail = 0;
var timestamp = 0;
var colors = new Array();
var members = new Array();
var progress = new Array('&#9676;', '&#9684;', '&#9681;', '&#9685;', '&#9673;');

<?php
	foreach ($colors as $key => $value) {
	echo "colors['$key'] = '$value';\n";
	}

	foreach ($member_extensions as $extension => $exteinsion_data) {
		$queues_str = '\''.implode('\',\'', $exteinsion_data).'\'';
		echo "members['$extension'] = new Array($queues_str);\n";
	}

	echo 'var status_url = \'', GS_URL_PATH, 'srv/queuestatus.php?t=\';';
?>


function clickedfield(element) {
	element.style.background="red";
}

if(navigator.appName == "Microsoft Internet Explorer") {
	http = new ActiveXObject("Microsoft.XMLHTTP");
} else {
	http = new XMLHttpRequest();
}

function to_array( )
{
	var arr = new Object( );
	for (var arg = 0; arg < arguments.length; ++arg )
	{
		arr[arguments[arg][0]] = arguments[arg][1];
	}
	return arr;
}

function read_data()
{
	if(http.readyState == 4) {
		var ret_arr = eval('to_array('+http.responseText+')');
		for (var key in ret_arr) {
			if (key == "ERROR_TEXT") {
				document.getElementById('server_info').innerHTML = ret_arr[key];
			} else {
				counter_fail = 0;
				if (key.charAt(0) == "a") {
					var exten = key.substring(1)
					if (exten in members) {
						for ( var queue in members[exten]) {
							var el_obj = document.getElementById('q'+members[exten][queue]+'_'+key);
							if (el_obj) el_obj.style.background = colors[ret_arr[key]];
						}
					}
				}
				else if (key.charAt(0) == "c") {
					var queue = key.substring(1)
					var el_obj = document.getElementById('q'+queue+'_calls');
					if (el_obj) el_obj.innerHTML = ret_arr[key];
				}
				document.getElementById('server_info').innerHTML = "&#10027; " + progress[(counter % 5)];
			}
		}
		if (!ret_arr)
			document.getElementById('server_info').innerHTML = "NO DATA";
	} else {
		++counter_fail;
		if (counter_fail >= 10) {
			document.getElementById('server_info').innerHTML = "NOT CONNECTED";
			for ( var exten in members) {
				for ( var queue in members[exten]) {
					var el_obj = document.getElementById('q'+members[exten][queue]+'_a'+exten);
					if (el_obj != null) el_obj.style.background = colors[255];
				}
			}
			counter_fail = 11;
			
		}
	}
}

function get_data(request_type)
{
	document.getElementById('server_info').innerHTML = "&#10026; " + progress[(counter % 5)];
	var nowtime = new Date;
	var now = nowtime.getTime();
	
	if ((now - timestamp) < (<?php echo $queuemon_data['update'] * 2000;  ?>)) {
		document.getElementById('server_info').innerHTML = "Delayed..."+counter;
		return 0;
	}
	http.abort();
	timestamp = now;
	http.open("GET", status_url+request_type, true);
	http.onreadystatechange=read_data
	http.send(null);
	timestamp = 0;
}

function data_reload()
{
	var timer=setTimeout("data_reload()", <?php echo $queuemon_data['update'] * 1000 ?>)
	if (counter >= <?php echo ($queuemon_data['reload'] * ceil(60 / $queuemon_data['update'])); ?>) {
		location.reload(true);
		counter = 0;
	} else {
		get_data("m");
		++counter;
	}
}

window.onload=data_reload

</script>


<?php
	echo '<span id="server_info">';
	echo '</span>', "\n";
	if ($fullscreen) {
	echo '<div id="chart" style="position:absolute; left:0px; right:0px; top:0px; bottom:0px; overflow:scroll; border:0px solid #ccc; background:#fff;">'."\n";
	} else {
		echo '<a href="?f=1"><img id="chart-fullscreen-toggle" class="fr" style="cursor:pointer; margin:0 1px 1px 0;" title="Fullscreen" alt="Fullscreen" src="'.GS_URL_PATH.'crystal-svg/16/act/window_fullscreen.png" /></a>'."\n";
		echo '<a href="?action=edit"><img id="chart-edit" class="fr" style="cursor:pointer; margin:0 1px 1px 0;" title="Edit" alt="Edit" src="'.GS_URL_PATH.'crystal-svg/16/act/edit.png" /></a>'."\n";
		echo '<div id="chart" style="position:absolute; left:189px; right:12px; top:12em; bottom:10px; overflow:scroll; border:0px solid #ccc; background:#fff;">'."\n";
	}

	$bg_color = CQ_DESKTOP_BG;
	$fg_color = CQ_WINDOW_FG;

	$qh = 1;
	$qx = 0;
	$qy = 0;
	$qmaxh = 0;

	
	foreach ($queues as $queue => $queue_data) {
		
		$stats = array();

		if ($queue_data['display_calls'])	$stats[] = '&#8721; '.queue_calls($queue_data['id'], $queue_data['display_calls']);
		if ($queue_data['display_answered'])	$stats[] = '&#9742; '.queue_answered($queue_data['id'], $queue_data['display_answered']);
		if ($queue_data['display_abandoned'])	$stats[] = '&#9822; '.queue_abandoned($queue_data['id'], $queue_data['display_abandoned']);
		if ($queue_data['display_timeout'])	$stats[] = '&#9719; '.queue_calls($queue_data['id'], $queue_data['display_timeout']);
		if ($queue_data['display_wait_max'])	$stats[] = '&#9719; &#8657; '._secs_to_minsecs(queue_waitmax($queue_data['id'], $queue_data['display_wait_max']));
		if ($queue_data['display_wait_min'])	$stats[] = '&#9719; &#8659; '._secs_to_minsecs(queue_waitmin($queue_data['id'], $queue_data['display_wait_min']));
		if ($queue_data['display_wait_avg'])	$stats[] = '&#9719; &#216; '._secs_to_minsecs(queue_waitavg($queue_data['id'], $queue_data['display_wait_avg']));
		if ($queue_data['display_call_max'])	$stats[] = '&#9742; &#8657 '._secs_to_minsecs(queue_callmax($queue_data['id'], $queue_data['display_call_max']));
		if ($queue_data['display_call_min'])	$stats[] = '&#9742; &#8659;'._secs_to_minsecs(queue_callmin($queue_data['id'], $queue_data['display_call_min']));
		if ($queue_data['display_call_avg'])	$stats[] = '&#9742; &#216 '._secs_to_minsecs(queue_callavg($queue_data['id'], $queue_data['display_call_avg']));

		

		$st_x = $qx;
		$st_y = $qy;
		
		if ($queue_data['active']) {
				queue_window($st_x,$st_y,$queue_data['display_width'],$queue_data['display_height'],"q".$queue,$queue_data['title'], $queue_data['members'], $queue_data['display_columns'], 0, CQ_WINDOW_BG, CQ_WINDOW_FG, $stats);
				if ($qmaxh < $queue_data['display_height']) $qmaxh = $queue_data['display_height'];
				if ($qh < $queuemon_data['columns']) {
					$qx = $qx + $queue_data['display_width'] + 1 ;
					++$qh;
				} else {
					$qh = 1;
					$qx = 0;
					$qy = $qy + $qmaxh +1;
					$qmaxh = 0;
				}
			}
	}
	echo "</div>\n";
}
?>
