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


include_once( GS_DIR .'htdocs/gui/inc/permissions.php' );
include_once( GS_DIR .'inc/extension-state.php' );
include_once( GS_DIR .'inc/gs-lib.php' );



function _extstate2v( $extstate )
{
	//static $states = array(.......);
	$states = array(
		AST_MGR_EXT_UNKNOWN   => array('v'=>  ('?'        ), 's'=>'?'     ),
		AST_MGR_EXT_IDLE      => array('v'=>__('frei'     ), 's'=>'green' ),
		AST_MGR_EXT_INUSE     => array('v'=>__('belegt'   ), 's'=>'red'   ),
		AST_MGR_EXT_BUSY      => array('v'=>__('belegt'   ), 's'=>'red'   ),
		AST_MGR_EXT_OFFLINE   => array('v'=>__('offline'  ), 's'=>'?'     ),
		AST_MGR_EXT_RINGING   => array('v'=>__('klingelt' ), 's'=>'yellow'),
		AST_MGR_EXT_RINGINUSE => array('v'=>__('anklopfen'), 's'=>'yellow'),
		AST_MGR_EXT_ONHOLD    => array('v'=>__('halten'   ), 's'=>'red'   )
	);
	return array_key_exists($extstate, $states) ? $states[$extstate] : null;
}


$GS_INSTALLATION_TYPE_SINGLE = gs_get_conf('GS_INSTALLATION_TYPE_SINGLE');

# connect to CDR master
$CDR_DB = gs_db_cdr_master_connect();
if (! $CDR_DB) {
	echo 'CDR DB error.';
	return;
}


# get the peer users from ldap
#
if (! function_exists('gui_monitor_which_peers')) {
	echo 'Error. Failed to get peers.';
	return;
}
$users = gui_monitor_which_peers( @$_SESSION['sudo_user']['name'] );
if (! is_array($users)) {
	echo 'Error. Failed to get peers.';
	return;
}
/*
echo "<pre>";
print_r($users);
echo "</pre>";
*/
if (count($users) < 1) {
	echo '<i>- ', htmlEnt( 'keine Kollegen' ) ,' -</i><br />',"\n";
	return;
}

$action = @$_REQUEST['action'];
if (! in_array($action, array('fullscreenoff','fullscreenon'), true))
	$action = 'fullscreenoff';

?>

<div id="chart">

<?php
if ($action === 'fullscreenon') {
	echo '<a id="chart-fullscreen-toggle" class="fr" style="cursor:pointer; margin:0 1px 1px 0;" href="', gs_url($SECTION, $MODULE, null, 'action=fullscreenoff').'" title="', __('Vollbild'), '"><img alt="', __('Vollbild'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/window_fullscreen.png" /></a>';
} else {
	echo '<a id="chart-fullscreen-toggle" class="fr" style="cursor:pointer; margin:0 1px 1px 0;" href="', gs_url($SECTION, $MODULE, null, 'action=fullscreenon').'" title="', __('Vollbild'), '"><img alt="', __('Vollbild'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/window_fullscreen.png" /></a>';
}


?>
<br style="clear:right;" />


<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:60px;"><?php echo __('Nst.'); ?></th>
	<th style="width:190px;"><?php echo __('Name'); ?></th>
	<th style="width:80px;"><?php echo __('Status'); ?></th>
	<th style="width:100px;"><?php echo __('Umleitung'); ?></th>
	<th style="width:90px;"><?php echo __('Queues'); ?></th>
	<th style="width:130px;"><?php echo __('Bemerkung'); ?></th>
</tr>
</thead>
<tbody>

<?php

# get the corresponding users from our db
#
$tmp = array();
foreach ($users as $user)
	$tmp[] = '\''. $DB->escape($user) .'\'';
$users_sql = implode(',',$tmp);
$rs_users = $DB->execute(
'SELECT
	`u`.`id`, `u`.`firstname` `fn`, `u`.`lastname` `ln`, `u`.`user_comment`,
	`s`.`name` `ext`, `h`.`host`,
	GROUP_CONCAT(DISTINCT CONCAT(`cf`.`source`, \':\', `cf`.`active`, \':\', `cf`.`number_std`, \':\', `cf`.`number_var`) SEPARATOR \';\') `forwards`,
	GROUP_CONCAT(DISTINCT `qm`.`queue_name`) `queues`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) JOIN
	`hosts` `h` ON (`h`.`id`=`u`.`host_id`) LEFT JOIN
	`callforwards` `cf` ON (`cf`.`user_id`=`u`.`id`) LEFT JOIN
	`ast_queue_members` `qm` ON (`qm`.`_user_id`=`u`.`id`)
WHERE
	`u`.`user` IN ('. $users_sql .') AND
	(`cf`.`case` IS NULL OR `cf`.`case`=\'always\')
GROUP BY `u`.`id`
ORDER BY `u`.`lastname`, `u`.`firstname`'
);
$i=0;
while ($user = $rs_users->fetchRow()) {
	
	$queues = explode(',', $user['queues']);
	# this damn function returns array(0=>'') for an empty string
	if (@$queues[0]=='') $queues = array();
	
	echo '<tr class="', ((++$i % 2 === 0) ? 'even':'odd'), '">';
	
	echo '<td>', htmlEnt($user['ext']), '</td>';
	
	echo '<td>', htmlEnt($user['ln']);
	if ($user['fn'] != '') echo ', ', htmlEnt($user['fn']);
	echo '</td>';
	
	if ($GS_INSTALLATION_TYPE_SINGLE)
		$user['host'] = '127.0.0.1';
	$extstate = gs_extstate( $user['host'], $user['ext'] );
	$extinfos[$user['ext']]['info' ] = $user;
	$extinfos[$user['ext']]['state'] = $extstate;
	$extstatev = _extstate2v( $extstate );
	if (gs_get_conf('GS_GUI_MON_NOQUEUEBLUE')) {
		if ($extstate === AST_MGR_EXT_IDLE && count($queues) < 1) {
			# blue LED for available users who are not member of a queue
			$extstatev['s'] = 'blue';
		}
	}
	
	if (@$extstatev['s']) {
		$img = '<img alt=" " src="'. GS_URL_PATH;
		switch ($extstatev['s']) {
			case 'green' : $img.= 'crystal-svg/16/act/greenled.png' ; break;
			case 'yellow': $img.= 'crystal-svg/16/act/yellowled.png'; break;
			case 'red'   : $img.= 'crystal-svg/16/act/redled.png'   ; break;
			case 'blue'  : $img.= 'img/blueled.png'                 ; break;
			default      : $img.= 'crystal-svg/16/act/free_icon.png'; break;
		}
		$img.= '" />&nbsp;';
	} else
		$img = '<img alt=" " src="'. GS_URL_PATH .'crystal-svg/16/act/free_icon.png" />&nbsp;';
	echo '<td>', $img, (@$extstatev['v'] ? $extstatev['v'] : '?'), '</td>';
	
	echo '<td>';
	if (strLen($user['forwards']) < 2)
		echo '&nbsp;';
	else {
		$cfst = explode(';', $user['forwards'], 2);
		$cfs = array();
		foreach ($cfst as $cf) {
			$cf = explode(':', $cf);
			switch (@$cf[1]) {
				case 'std':  $cfs[@$cf[0]] = @$cf[2]; break;
				case 'var':  $cfs[@$cf[0]] = @$cf[3]; break;
				case 'vml':  $cfs[@$cf[0]] =__('AB'); break;
				case 'no' :
				default   :  $cfs[@$cf[0]] = null;
			}
		}
		if (@$cfs['internal'] !== null
		&& @$cfs['internal'] == @$cfs['external']) {
			echo 'i/e: ', $cfs['internal'];
		} elseif (@$cfs['internal'] !== null && @$cfs['external'] !== null) {
			echo 'i: ', $cfs['internal'], '<br />';
			echo 'e: ', $cfs['external'];
		} elseif (@$cfs['internal'] !== null) {
			echo 'i: ', $cfs['internal'];
		} elseif (@$cfs['external'] !== null) {
			echo 'e: ', $cfs['external'];
		} else {
			echo '&nbsp;';
		}
	}
	echo '</td>';
	
	echo '<td>';
	if (count($queues) < 1)
		echo '&nbsp;';
	else
		echo implode(', ', $queues);
	echo '</td>';
	
	echo '<td>';
	echo ($user['user_comment']=='' ? '&nbsp;' : htmlEnt($user['user_comment']));
	echo '</td>';
	
	echo '</tr>', "\n";
}

?>

</tbody>
</table>

<br />

<?php






function _num_calls_dlog_since( $users_sql, $t_from, $type, $external )
{
	global $DB;
	return $DB->executeGetOne(
'SELECT COUNT(*)
FROM
	`users` `u` JOIN
	`dial_log` `d` ON (`d`.`user_id`=`u`.`id`)
WHERE
	`u`.`user` IN ('. $users_sql .') AND
	`d`.`timestamp`>='. (int)$t_from .' AND
	`d`.`type`=\''. $DB->escape($type) .'\' AND
	`d`.`number` '. ($external ? '':'NOT ') .'LIKE \'0%\' AND
	`d`.`number` NOT LIKE \'*%\''
	);
	//FIXME - Amtsholung ist nicht immer "0"
}

function _num_calls_cdr_bysrc_since( $exts_sql, $t_from, $disposition, $external )
{
	global $CDR_DB;
	return $CDR_DB->executeGetOne(
'SELECT COUNT(*)
FROM `ast_cdr`
WHERE
	`src` IN ('. $exts_sql .') AND
	`dst` '. ($external ? '':'NOT ') .'LIKE \'0%\' AND
	`calldate`>=\''. date('Y-m-d H:i:s', $t_from) .'\' AND
	`disposition`=\''. $CDR_DB->escape($disposition) .'\' AND
	`channel` NOT LIKE \'Local/%\' AND
	`dst`<>\'s\' AND
	`dst`<>\'h\''
	);
	//FIXME - Amtsholung ist nicht immer "0"
}

function _num_calls_cdr_bydst_since( $exts_sql, $t_from, $disposition, $external )
{
	global $CDR_DB;
	return $CDR_DB->executeGetOne(
'SELECT COUNT(*)
FROM `ast_cdr`
WHERE
	`dst` IN ('. $exts_sql .') AND
	`src` '. ($external ? '':'NOT ') .'LIKE \'0%\' AND
	`calldate`>=\''. date('Y-m-d H:i:s', $t_from) .'\' AND
	`disposition`=\''. $CDR_DB->escape($disposition) .'\' AND
	`channel` NOT LIKE \'Local/%\' AND
	`dst`<>\'s\' AND
	`dst`<>\'h\''
	);
	//FIXME - Amtsholung ist nicht immer "0"
}

function _users_sql_to_exts_sql( $users_sql )
{
	global $DB;
	$rs = $DB->execute(
'SELECT `s`.`name` `ext`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`)
WHERE
	`u`.`user` IN ('. $users_sql .')'
	);
	$exts = array();
	while ($r = $rs->fetchRow()) {
		$exts[] = '\''.$DB->escape($r['ext']).'\'';
	}
	return implode(',', $exts);
}


?>


<div class="fl" style="clear:right; width:99%;">
	<div class="fl" style="margin:1px;">
		
		<b><?php echo __('Direktgespr&auml;che') ,' (', __('heute') ,')'; ?></b>
		<table cellspacing="1">
		<thead>
		<tr>
			<th style="width:110px;">&nbsp;</th>
			<th style="width:50px;" class="r"><?php echo __('intern'); ?></th>
			<th style="width:50px;" class="r"><?php echo __('extern'); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		
		$now = time();
		$y = (int)date('Y',$now);
		$m = (int)date('n',$now);
		$d = (int)date('j',$now);
		$t_from = mkTime(0,0,0,$m,$d,$y);
		
		$n_calls_in_i     = _num_calls_dlog_since( $users_sql, $t_from, 'in'    , false );
		$n_calls_in_e     = _num_calls_dlog_since( $users_sql, $t_from, 'in'    , true  );
		$n_calls_missed_i = _num_calls_dlog_since( $users_sql, $t_from, 'missed', false );
		$n_calls_missed_e = _num_calls_dlog_since( $users_sql, $t_from, 'missed', true  );
		$n_calls_out_i    = _num_calls_dlog_since( $users_sql, $t_from, 'out'   , false );
		$n_calls_out_e    = _num_calls_dlog_since( $users_sql, $t_from, 'out'   , true  );
		
		echo '<tr>';
		echo '<td>', __('Angenommen'), '</td>';
		echo '<td class="r">', $n_calls_in_i, '</td>';
		echo '<td class="r">', $n_calls_in_e, '</td>';
		echo '</tr>', "\n";
		
		echo '<tr>';
		echo '<td>', __('Verpasst'), '</td>';
		echo '<td class="r">', $n_calls_missed_i, '</td>';
		echo '<td class="r">', $n_calls_missed_e, '</td>';
		echo '</tr>', "\n";
		
		echo '<tr>';
		echo '<td>', __('Gew&auml;hlt'), '</td>';
		echo '<td class="r">', $n_calls_out_i, '</td>';
		echo '<td class="r">', $n_calls_out_e, '</td>';
		echo '</tr>', "\n";
		
		?>
		</tbody>
		</table>
		
	</div>
	<div class="fr" style="margin:1px;">
		
		<b><?php echo __('Gesamt') ,' (', __('heute') ,')'; ?></b>
		<table cellspacing="1">
		<thead>
		<tr>
			<th style="width:110px;">&nbsp;</th>
			<th style="width:50px;" class="r"><?php echo __('nach intern'); ?></th>
			<th style="width:50px;" class="r"><?php echo __('nach extern'); ?></th>
			<th style="width:50px;" class="r"><?php echo __('von intern'); ?></th>
			<th style="width:50px;" class="r"><?php echo __('von extern'); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		
		$now = time();
		$y = (int)date('Y',$now);
		$m = (int)date('n',$now);
		$d = (int)date('j',$now);
		$t_from = mkTime(0,0,0,$m,$d,$y);
		
		$exts_sql = _users_sql_to_exts_sql( $users_sql );
		
		$n_calls_bysrc_answ_i = _num_calls_cdr_bysrc_since( $exts_sql, $t_from, 'ANSWERED' , false );
		$n_calls_bysrc_answ_e = _num_calls_cdr_bysrc_since( $exts_sql, $t_from, 'ANSWERED' , true  );
		$n_calls_bysrc_busy_i = _num_calls_cdr_bysrc_since( $exts_sql, $t_from, 'BUSY'     , false );
		$n_calls_bysrc_busy_e = _num_calls_cdr_bysrc_since( $exts_sql, $t_from, 'BUSY'     , true  );
		$n_calls_bysrc_noan_i = _num_calls_cdr_bysrc_since( $exts_sql, $t_from, 'NO ANSWER', false );
		$n_calls_bysrc_noan_e = _num_calls_cdr_bysrc_since( $exts_sql, $t_from, 'NO ANSWER', true  );
		$n_calls_bysrc_fail_i = _num_calls_cdr_bysrc_since( $exts_sql, $t_from, 'FAILED'   , false );
		$n_calls_bysrc_fail_e = _num_calls_cdr_bysrc_since( $exts_sql, $t_from, 'FAILED'   , true  );
		
		$n_calls_bydst_answ_i = _num_calls_cdr_bydst_since( $exts_sql, $t_from, 'ANSWERED' , false );
		$n_calls_bydst_answ_e = _num_calls_cdr_bydst_since( $exts_sql, $t_from, 'ANSWERED' , true  );
		$n_calls_bydst_busy_i = _num_calls_cdr_bydst_since( $exts_sql, $t_from, 'BUSY'     , false );
		$n_calls_bydst_busy_e = _num_calls_cdr_bydst_since( $exts_sql, $t_from, 'BUSY'     , true  );
		$n_calls_bydst_noan_i = _num_calls_cdr_bydst_since( $exts_sql, $t_from, 'NO ANSWER', false );
		$n_calls_bydst_noan_e = _num_calls_cdr_bydst_since( $exts_sql, $t_from, 'NO ANSWER', true  );
		$n_calls_bydst_fail_i = _num_calls_cdr_bydst_since( $exts_sql, $t_from, 'FAILED'   , false );
		$n_calls_bydst_fail_e = _num_calls_cdr_bydst_since( $exts_sql, $t_from, 'FAILED'   , true  );
		
		echo '<tr>';
		echo '<td>', __('Erfolgreich'), '</td>';
		echo '<td class="r">', $n_calls_bysrc_answ_i, '</td>';
		echo '<td class="r">', $n_calls_bysrc_answ_e, '</td>';
		echo '<td class="r">', $n_calls_bydst_answ_i, '</td>';
		echo '<td class="r">', $n_calls_bydst_answ_e, '</td>';
		echo '</tr>', "\n";
		
		echo '<tr>';
		echo '<td>', __('Besetzt'), '</td>';
		echo '<td class="r">', $n_calls_bysrc_busy_i, '</td>';
		echo '<td class="r">', $n_calls_bysrc_busy_e, '</td>';
		echo '<td class="r">', $n_calls_bydst_busy_i, '</td>';
		echo '<td class="r">', $n_calls_bydst_busy_e, '</td>';
		echo '</tr>', "\n";
		
		echo '<tr>';
		echo '<td>', __('Keine Antwort'), '</td>';
		echo '<td class="r">', $n_calls_bysrc_noan_i, '</td>';
		echo '<td class="r">', $n_calls_bysrc_noan_e, '</td>';
		echo '<td class="r">', $n_calls_bydst_noan_i, '</td>';
		echo '<td class="r">', $n_calls_bydst_noan_e, '</td>';
		echo '</tr>', "\n";
		
		echo '<tr>';
		echo '<td>', __('Stau'), '</td>';
		echo '<td class="r">', $n_calls_bysrc_fail_i, '</td>';
		echo '<td class="r">', $n_calls_bysrc_fail_e, '</td>';
		echo '<td class="r">', $n_calls_bydst_fail_i, '</td>';
		echo '<td class="r">', $n_calls_bydst_fail_e, '</td>';
		echo '</tr>', "\n";
		
		?>
		</tbody>
		</table>
		
	</div>
</div>



<script type="text/javascript">/*<![CDATA[*/
<?php
if ($action === 'fullscreenon') {
?>
var chart = document.getElementById('chart');
var toggle = document.getElementById('chart-fullscreen-toggle');
if (chart && toggle) {
	chart.style.position = 'absolute';
	chart.style.top        = '0';
	chart.style.left       = '0';
	chart.style.right      = '0';
	chart.style.bottom     = '0';
	chart.style.background = '#fff';
	chart.style.padding    = '0.4em 0.8em 0.7em 0.8em';
	toggle.src = '<?php echo GS_URL_PATH; ?>crystal-svg/16/act/window_nofullscreen.png';
	}
<?php
	}
?>

window.setTimeout('document.location.reload();', 12000);
/*]]>*/</script>
