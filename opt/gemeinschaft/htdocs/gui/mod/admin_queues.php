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
* Sascha Daniels <sd@alternative-solution.de>
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
include_once( GS_DIR .'inc/gs-fns/gs_queue_add.php' );
include_once( GS_DIR .'inc/gs-fns/gs_queue_del.php' );
include_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );

$moh_classes = @array_keys(parse_ini_file(GS_DIR.'/etc/asterisk/musiconhold.conf', TRUE));
# fails if Asterisk is on a different server
if (! is_array($moh_classes)) $moh_classes = array();
$moh_classes = array_merge(array('default'), $moh_classes);
$moh_classes = array_values(array_unique($moh_classes));

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";
echo '<script type="text/javascript">
//<![CDATA[
function confirm_delete() {
	return confirm(', utf8_json_quote(__("Wirklich l\xC3\xB6schen?")) ,');
}
//]]>
</script>' ,"\n";

$action = @$_REQUEST['action'];
if (! in_array($action, array('','edit','save','del', 'delstatic', 'addstatic'), true))
	$action = '';

$queue_id = (int)@$_REQUEST['qid'];
$agent_id = (int)@$_REQUEST['aid'];
$per_page = (int)gs_get_conf('GS_GUI_NUM_RESULTS');
if ($per_page < 1) $per_page = 1;
$page     = (int)@$_REQUEST['page'];


#####################################################################
#                               del {
#####################################################################
if ($action === 'del') {
	$ret = gs_queue_del( @$_REQUEST['qname'] );
	if (isGsError( $ret )) echo $ret->getMsg();
	
	$action = '';
}
#####################################################################
#                               del }
#####################################################################



#####################################################################
#                               save {
#####################################################################
if ($action === 'save') {
	/*
	echo "<pre>\n";
	print_r($_REQUEST);
	echo "</pre>\n";
	*/
	
	$name = preg_replace('/[^0-9]/', '', @$_REQUEST['name']);
	$title = trim(@$_REQUEST['_title']);
	$maxlen = abs((int)@$_REQUEST['maxlen']);
	if ($maxlen < 1) $maxlen = 0;
	$announce_holdtime = @$_REQUEST['announce_holdtime'];
	if (! in_array($announce_holdtime, array('yes', 'once', 'no'), true))
		$announce_holdtime = 'yes';
	$wrapuptime = (int)@$_REQUEST['wrapuptime'];
	if ($wrapuptime < 1) $wrapuptime = 0;
	$timeout = (int)@$_REQUEST['timeout'];
	if ($timeout < 1) $timeout = 10;
	$strategy = @$_REQUEST['strategy'];
	if (! in_array($strategy, array('rrmemory', 'leastrecent', 'random', 'fewestcalls', 'ringall'), true))
		$strategy = 'rrmemory';
	$joinempty = @$_REQUEST['joinempty'];
	if (! in_array($joinempty, array('yes', 'no', 'strict'), true))
		$joinempty = 'strict';
	$leavewhenempty = @$_REQUEST['leavewhenempty'];
	if (! in_array($leavewhenempty, array('yes', 'no', 'strict'), true))
		$leavewhenempty = 'yes';
	$musicclass = preg_replace('/[^a-zA-Z0-9\-_]/', '', @$_REQUEST['musicclass']);
	//if (! in_array($musicclass, array('default', ''), true))
	//	$musicclass = 'default';
	$musicclass_db = ($musicclass != '' ? '\''. $DB->escape($musicclass) .'\'' : 'NULL');
	$salutation = (int)@$_REQUEST['salutation'];
	
	$update_additional = false;
	if ($queue_id < 1) {
		$ret = gs_queue_add( $name, $title, $maxlen, (int)@$_REQUEST['_host_id'] );
		if (isGsError( $ret )) echo $ret->getMsg();
		else {
			$queue_id = $DB->executeGetOne( 'SELECT `_id` FROM `ast_queues` WHERE `name`=\''. $DB->escape($name) .'\'' );
			if ($queue_id < 1) $queue_id = 0;
			else {
				$update_additional = true;
			}
		}
	} else {
		
		$update_additional = true;
	}
	if ($update_additional) {
		$DB->execute(
'UPDATE `ast_queues` SET
	`_title`=\''. $DB->escape($title) .'\',
	`maxlen`='. $maxlen .',
	`musicclass`='. $musicclass_db .',
	`_sysrec_id`='. $salutation .',
	`announce_holdtime`=\''. $announce_holdtime .'\',
	`wrapuptime`='. $wrapuptime .',
	`timeout`='. $timeout .',
	`strategy`=\''. $strategy .'\',
	`joinempty`=\''. $joinempty .'\',
	`leavewhenempty`=\''. $leavewhenempty .'\'
WHERE `_id`='.$queue_id
		);
	}
	
	$action = 'edit';
}
#####################################################################
#                               save }
#####################################################################

####################################################################
#                              static_agents {
###################################################################

if ($action === 'delstatic') {

	if (($queue_id > 0) && ($agent_id > 0)) {
		$DB->execute(
			'DELETE FROM `ast_queue_members` '.
			'WHERE '.
			'`_queue_id` = '.$queue_id.' AND '.
			'`_user_id` = '.$agent_id. ' AND '.
			'`static` = 1'
		);
	}
	$action = 'edit';

}

if ($action === 'addstatic') {
	$q_hid = (int)$DB->executeGetOne(
	'SELECT `_host_id` FROM `ast_queues` '.
	'WHERE `_id`='.$queue_id
	);
	$a_hid = (int)$DB->executeGetOne(
	'SELECT `host_id` from `users` '.
	'WHERE `id`='.$agent_id
	);
	if ($a_hid != $q_hid) {
		echo '<div class="errorbox">';
		echo __('Warteschlange und Agent sind nicht auf dem gleichen Host!');
		echo '</div>',"\n";
		$action = 'edit';
	} else {
		if (($queue_id > 0) && ($agent_id >0)) {
			$queue_name = $DB->executeGetOne(
			'SELECT `name` FROM `ast_queues` WHERE '.
			'`_id`='.$queue_id
			);
			$user_name = $DB->executeGetOne(
			'SELECT `name` FROM `ast_sipfriends_gs` '.
			'WHERE `_user_id`='.$agent_id
			);
			if (($user_name != '') && ($queue_name != '')) {
				$interface = 'SIP/'.$user_name;
				$penalty = $DB->executeGetOne('SELECT `penalty` FROM `penalties` WHERE `_user_id`='.$agent_id);
				if (! $penalty) $penalty='DEFAULT';
				$DB->execute(
				'REPLACE into `ast_queue_members` SET 
				`queue_name` = \''. $DB->escape($queue_name) .'\',
				`interface` = \''. $DB->escape($interface) .'\',
				`_user_id` ='.$agent_id. ', '.
				'`_queue_id` ='.$queue_id. ', '.
				'`static` = 1, `penalty`='.$penalty
				);
				} else {
					echo '<div class="errorbox">';
					echo __('User oder Warteschlange ung&uuml;ltig!');
					echo '</div>',"\n";
				}
		}
	}
	$action = 'edit';
}
####################################################################
#                              static_agents } 
###################################################################






#####################################################################
#                               edit {
#####################################################################
if ($action === 'edit') {
	
	echo '<div class="fr"><a href="', gs_url($SECTION, $MODULE, null, 'page='.$page) ,'">', __('zur&uuml;ck') ,'</a></div>' ,"\n";
	
	# get hosts
	#
	$sql_query = 'SELECT `id`, `host`
FROM `hosts`
ORDER BY `id`';
	$rs = $DB->execute($sql_query);
	$hosts = array();
	while ($r = $rs->fetchRow()) {
		$hosts[$r['id']] = $r['host'];
	}

	# get system recordings
	#
	$sql_query =
'SELECT
	`s`.`id` `id`,
	`s`.`description` `description`,
	`s`.`length` `length`
FROM
	`systemrecordings` `s`';

	$rs = $DB->execute($sql_query);
	$recordings = array();
	while ($r = $rs->fetchRow()) {
		$recordings[$r['id']] = $r['description'];
	}	

	# get queue
	#
	if ($queue_id > 0) {
		$rs = $DB->execute(
'SELECT
	`name`, `_host_id`, `_title`, `musicclass`, `_sysrec_id`, `announce_holdtime`, `timeout`, `wrapuptime`, `maxlen`, `strategy`, `joinempty`, `leavewhenempty`
FROM
	`ast_queues`
WHERE
	`_id`='. $queue_id
		);
		$queue = $rs->fetchRow();
		if ($queue['wrapuptime']===null) $queue['wrapuptime'] = 5;
		if ($queue['maxlen'    ]===null) $queue['maxlen'    ] = 50;
		if ($queue['timeout'   ]===null) $queue['timeout'   ] = 10;
	} else {
		$queue_id = 0;
		$queue = array(
			'name'                       => '',
			'_host_id'                   => 0,
			'_title'                     => __('Neue Warteschlange'),
			'musicclass'                 => 'default',
			'_sysrec_id'                 => 0,
			'announce_holdtime'          => 'yes',
			'wrapuptime'                 => 5,
			'maxlen'                     => 255,
			'timeout'                    => 10,
			'strategy'                   => 'rrmemory',
			'joinempty'                  => 'strict',
			'leavewhenempty'             => 'yes'
		);
	}
	
	if (! is_array($queue)) {
		$action = '';
	} else {
		
		echo '<h3>', htmlEnt($queue['name']) ,' (', htmlEnt($queue['_title']) ,')</h3>' ,"\n";
		
		echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
		echo gs_form_hidden($SECTION, $MODULE), "\n";
		echo '<input type="hidden" name="action" value="save" />', "\n";
		echo '<input type="hidden" name="qid" value="', $queue_id , '" />', "\n";
		echo '<input type="hidden" name="qname" value="', $queue['name'] , '" />', "\n";
		echo '<input type="hidden" name="page" value="', $page, '" />', "\n";
		
		echo '<table cellspacing="1">',"\n";
		echo '<tbody>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r" style="width:140px;">', __('Bezeichnung') ,'</th>',"\n";
		echo '<td style="width:350px;">';
		echo '<input type="text" name="_title" value="', htmlEnt($queue['_title']) ,'" size="30" maxlength="30" />', "\n";
		echo '</td>';
		echo '<td class="transp xs gray"><code>queues.conf<code>:</td>',"\n";
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Durchwahl') ,'</th>',"\n";
		echo '<td>';
		//echo '<input type="text" name="name" value="', htmlEnt($queue['name']) ,'" size="8" maxlength="6" />', "\n";
		echo '<input type="text" name="name" value="', htmlEnt($queue['name']) ,'" size="8" maxlength="6" ';
		if ($queue_id > 0) echo 'disabled="disabled" ';
		echo '/>', "\n";
		echo '</td>';
		echo '<td class="transp xs gray"><code>[', htmlEnt($queue['name']) ,']</code></td>',"\n";
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Host') ,'</th>',"\n";
		echo '<td>';
		echo '<select name="_host_id"';
		if ($queue_id > 0) echo ' disabled="disabled"';
		echo '>', "\n";
		foreach ($hosts as $host_id => $host_ip) {
			echo '<option value="', $host_id ,'"';
			if ($host_id == $queue['_host_id']) echo ' selected="selected"';
			echo '>', $host_id ,' (', htmlEnt($host_ip) ,')</option>', "\n";
		}
		echo '</select>';
		echo '</td>';
		echo '<td class="transp xs gray"></td>',"\n";
		echo '</tr>',"\n";

		echo '<tr>',"\n";
		echo '<th class="r">', __('Begr&uuml;&szlig;ung') ,'</th>',"\n";
		echo '<td>';
		echo '<select name="salutation">', "\n";
		echo '<option value="0"', ($queue['_sysrec_id'] ==0 ? ' selected="selected"' : '') ,'>', __('keine') ,'</option>', "\n";
		echo '<option value="" disabled="disabled">-</option>', "\n";
		foreach( $recordings as $rec_id => $desc ) {
			echo '<option value="' . $rec_id .'"', ($queue['_sysrec_id'] ==$rec_id ? ' selected="selected"' : '') ,'>', __($desc) ,'</option>', "\n";
		}
		echo '</select>';
		echo '</td>';
		echo '</tr>',"\n";		

		echo '<tr>',"\n";
		echo '<th class="r">', __('Wartemusik') ,'</th>',"\n";
		echo '<td>';
		echo '<select name="musicclass">', "\n";
		foreach ($moh_classes as $moh_class) {
			echo '<option value="',$moh_class,'"';
			if ($queue['musicclass'] == $moh_class) echo ' selected="selected"';
			echo '>',$moh_class,'</option>',"\n";
		}
		echo '<option value="" disabled="disabled">-</option>', "\n";
		echo '<option value=""';
		if ($queue['musicclass'] == '') echo ' selected="selected"';
		echo '>', __('Klingeln statt Musik') ,'</option>', "\n";
		echo '</select>';
		echo '</td>';
		echo '<td class="transp xs gray"><code>musicclass</code> / <code>Queue(,r)</code></td>',"\n";
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Wartezeit ansagen') ,'</th>',"\n";
		echo '<td>',"\n";
		
		echo '<input type="radio" name="announce_holdtime" id="ipt-announce_holdtime-yes" value="yes" ';
		if ($queue['announce_holdtime']==='yes') echo 'checked="checked" ';
		echo '/>', "\n";
		echo '<label for="ipt-announce_holdtime-yes">', __('ja') ,'</label> &nbsp;', "\n";
		
		echo '<input type="radio" name="announce_holdtime" id="ipt-announce_holdtime-once" value="once" ';
		if ($queue['announce_holdtime']==='once') echo 'checked="checked" ';
		echo '/>', "\n";
		echo '<label for="ipt-announce_holdtime-once">', __('einmal') ,'</label> &nbsp;', "\n";
		
		echo '<input type="radio" name="announce_holdtime" id="ipt-announce_holdtime-no" value="no" ';
		if ($queue['announce_holdtime']==='no') echo 'checked="checked" ';
		echo '/>', "\n";
		echo '<label for="ipt-announce_holdtime-no">', __('nein') ,'</label>', "\n";
		
		echo '</td>';
		echo '<td class="transp xs gray"><code>announce_holdtime = </code><code>yes</code> | <code>once</code> | <code>no</code></td>',"\n";
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Nachbereitungszeit') ,'</th>',"\n";
		echo '<td>';
		echo '<input type="text" name="wrapuptime" value="', $queue['wrapuptime'] ,'" size="3" maxlength="3" class="r" /> s', "\n";
		echo '</td>';
		echo '<td class="transp xs gray"><code>wrapuptime</code></td>',"\n";
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Klingelzeit p. Agent') ,'</th>',"\n";
		echo '<td>';
		echo '<input type="text" name="timeout" value="', $queue['timeout'] ,'" size="3" maxlength="3" class="r" /> s', "\n";
		echo '</td>';
		echo '<td class="transp xs gray"><code>timeout</code></td>',"\n";
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Max. Anrufer') ,'</th>',"\n";
		echo '<td>';
		echo '<input type="text" name="maxlen" value="', $queue['maxlen'] ,'" size="3" maxlength="3" class="r" />', "\n";
		echo '</td>';
		echo '<td class="transp xs gray"><code>maxlen</code></td>',"\n";
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Strategie') ,'</th>',"\n";
		echo '<td>',"\n";
		echo '<select name="strategy">', "\n";
		echo '<option value="rrmemory" title="rrmemory"';
		if ($queue['strategy']==='rrmemory') echo ' selected="selected"';
		echo '>', __('Round-Robin') ,'</option>', "\n";
		echo '<option value="leastrecent" title="leastrecent"';
		if ($queue['strategy']==='leastrecent') echo ' selected="selected"';
		echo '>', __('&auml;ltesten') ,'</option>', "\n";
		echo '<option value="random" title="random"';
		if ($queue['strategy']==='random') echo ' selected="selected"';
		echo '>', __('zuf&auml;llig') ,'</option>', "\n";
		echo '<option value="fewestcalls" title="fewestcalls"';
		if ($queue['strategy']==='fewestcalls') echo ' selected="selected"';
		echo '>', __('am wenigsten Anrufe') ,'</option>', "\n";
		echo '<option value="ringall" title="ringall"';
		if ($queue['strategy']==='ringall') echo ' selected="selected"';
		echo '>', __('alle anklingeln') ,'</option>', "\n";
		echo '</select>';
		echo '</td>';
		echo '<td class="transp xs gray"><code>strategy</code></td>',"\n";
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Eintritt') ,'</th>',"\n";
		echo '<td>',"\n";
		
		echo '<div class="radio_and_label_blocks">', "\n";
		
		echo '<div class="radio_and_label_block">', "\n";
		echo '<input type="radio" name="joinempty" id="ipt-joinempty-yes" value="yes" ';
		if ($queue['joinempty']==='yes') echo 'checked="checked" ';
		echo '/>', "\n";
		echo '<label for="ipt-joinempty-yes">', __('auch wenn keine Agenten angemeldet sind') ,'</label>', "\n";
		echo '</div>', "\n";
		
		echo '<div class="radio_and_label_block">', "\n";
		echo '<input type="radio" name="joinempty" id="ipt-joinempty-no" value="no" ';
		if ($queue['joinempty']==='no') echo 'checked="checked" ';
		echo '/>', "\n";
		echo '<label for="ipt-joinempty-no">', __('nicht wenn keine Agenten angemeldet sind') ,'</label>', "\n";
		echo '</div>', "\n";
		
		echo '<div class="radio_and_label_block">', "\n";
		echo '<input type="radio" name="joinempty" id="ipt-joinempty-strict" value="strict" ';
		if ($queue['joinempty']==='strict') echo 'checked="checked" ';
		echo '/>', "\n";
		echo '<label for="ipt-joinempty-strict">', __('nicht wenn keine Agenten angemeldet sind oder keine Agenten frei sind') ,'</label>', "\n";
		echo '</div>', "\n";
		
		echo '</div>', "\n";
		
		echo '</td>';
		echo '<td class="transp xs gray"><code>joinempty = </code><code>yes</code> | <code>no</code> | <code>strict</code></td>',"\n";
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Austritt') ,'</th>',"\n";
		echo '<td>',"\n";
		
		echo '<div class="radio_and_label_blocks">', "\n";
		
		echo '<div class="radio_and_label_block">', "\n";
		echo '<input type="radio" name="leavewhenempty" id="ipt-leavewhenempty-no" value="no" ';
		if ($queue['leavewhenempty']==='no') echo 'checked="checked" ';
		echo '/>', "\n";
		echo '<label for="ipt-leavewhenempty-no">', __('nie') ,'</label>', "\n";
		echo '</div>', "\n";
		
		echo '<div class="radio_and_label_block">', "\n";
		echo '<input type="radio" name="leavewhenempty" id="ipt-leavewhenempty-yes" value="yes" ';
		if ($queue['leavewhenempty']==='yes') echo 'checked="checked" ';
		echo '/>', "\n";
		echo '<label for="ipt-leavewhenempty-yes">', __('wenn keine Agenten mehr angemeldet sind') ,'</label>', "\n";
		echo '</div>', "\n";
		
		echo '<div class="radio_and_label_block">', "\n";
		echo '<input type="radio" name="leavewhenempty" id="ipt-leavewhenempty-strict" value="strict" ';
		if ($queue['leavewhenempty']==='strict') echo 'checked="checked" ';
		echo '/>', "\n";
		echo '<label for="ipt-leavewhenempty-strict">', __('wenn keine Agenten mehr angemeldet sind oder keine Agenten mehr frei sind') ,'</label>', "\n";
		echo '</div>', "\n";
		
		echo '</div>', "\n";
		
		echo '</td>';
		echo '<td class="transp xs gray"><code>leavewhenempty = </code><code>no</code> | <code>yes</code> | <code>strict</code></td>',"\n";
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<td class="transp">&nbsp;</td>',"\n";
		echo '<td class="transp"><br />',"\n";
		echo '<button type="submit" title="', __('Speichern'), '" class="plain">';
		echo '<img alt="', __('Speichern') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/filesave.png" />
		</button>' ,"\n";
		echo "&nbsp;\n";
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'page='.$page) ,'">';
		echo '<button type="button" title="', __('Abbrechen'), '" class="plain">';
		echo '<img alt="', __('Abbrechen') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/cancel.png" />
		</button></a>' ,"\n";
		echo '</td>',"\n";
		echo '</tr>',"\n";
		echo '</tbody>',"\n";
		echo '</table>',"\n";
		echo '</form>';
		echo '<h3>', __('Statische'), ' ', __('Agenten'), '</h3><p>';
		echo __('Statische'), ' ', __('Agenten'), ' ', __('sind immer an der Warteschlange angemeldet.');
		echo '<table>';
		echo '<tr>';
		echo '<th>', __('Verf&uuml;gbare'), ' ', __('Agenten'), '</th>';
		echo '<th>', __('Angemeldete'), ' ', __('Agenten'), '</th>';
		echo '</tr><tr><td>';
		echo '<div align="right">';
		echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
		echo gs_form_hidden($SECTION, $MODULE), "\n";
		echo '<input type="hidden" name="action" value="addstatic" />', "\n";
		echo '<input type="hidden" name="qid" value="', $queue_id , '" />', "\n";
		$host_id = (int)$DB->executeGetOne('SELECT `_host_id` from `ast_queues` WHERE `_id`='.$queue_id);
		$rs = $DB->execute('SELECT `user`, `name`, `id`, `firstname`, `lastname` from `users`, `ast_sipfriends` WHERE `nobody_index` IS NULL AND `id`=`_user_id` AND `host_id`='.$host_id. '  AND `id` NOT IN (select `_user_id` from `ast_queue_members` where `static`=1 and `_queue_id`='. $queue_id .')');
		echo '<select name="aid" size="10">', "\n";
		while ($user_map = $rs->fetchRow()) {
		echo '<option value="', (int)$user_map['id'], '"', 'title="', htmlEnt( $user_map['lastname']), ', ', htmlEnt( $user_map['firstname']), '"';
		echo '>',  $user_map['name'], ' ', htmlEnt( $user_map['user'] ), '</option>', "\n";
		}
		echo '</select>';
		echo '<button type="submit" title="', __('Hinzuf&uuml;gen'), '" class="plain">';
		echo '<img alt="', __('Hinzuf&uuml;gen') ,'" src="', GS_URL_PATH,'crystal-svg/32/act/forward-cust.png" /></button>' ,"\n";
		echo '</form></div>';
		echo '</td><td>';
		echo '<div align="left">';
		echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
		echo gs_form_hidden($SECTION, $MODULE), "\n";
		echo '<button type="submit" title="', __('Entfernen'), '" class="plain">';
		echo '<img alt="', __('Entfernen') ,'" src="', GS_URL_PATH,'crystal-svg/32/act/back-cust.png" /></button>' ,"\n";
		echo '<input type="hidden" name="action" value="delstatic" />', "\n";
		echo '<input type="hidden" name="qid" value="', $queue_id , '" />', "\n";
		$host_id = (int)$DB->executeGetOne('SELECT `_host_id` from `ast_queues` WHERE `_id`='.$queue_id);
		$rs = $DB->execute('SELECT `user`, `name`, q.`_user_id`, `firstname`, `lastname`  FROM `users` u , `ast_sipfriends` s, `ast_queue_members` q  where `s`.`_user_id`=`q`.`_user_id` AND `u`.`id`=`q`.`_user_id` and `q`.`static`=1 AND `q`.`_queue_id`='. $queue_id);
		echo '<select name="aid" size="10">', "\n";
		while ($user_map = $rs->fetchRow()) {
		echo '<option value="', (int)$user_map['_user_id'], '"', 'title="', htmlEnt( $user_map['lastname']), ', ', htmlEnt( $user_map['firstname']), '"';
		echo '>',  $user_map['name'], ' ', htmlEnt( $user_map['user'] ), '</option>', "\n";
		}
		echo '</select>';
		echo '</form>',"\n";
		echo '</div></td></tr></table>';
	}
	
}
#####################################################################
#                               edit }
#####################################################################


#####################################################################
#                             view list {
#####################################################################
if ($action === '') {
	
	# get hosts
	#
	$sql_query = 'SELECT `id`, `host`
FROM `hosts`
ORDER BY `id`';
	$rs = $DB->execute($sql_query);
	$hosts = array();
	while ($r = $rs->fetchRow()) {
		$hosts[$r['id']] = $r['host'];
	}
	
	# get queues
	#
	$sql_query =
'SELECT SQL_CALC_FOUND_ROWS
	`_id`, `name`, `_host_id`, `_title`, `maxlen`
FROM `ast_queues`
ORDER BY `name`
LIMIT '. ($page*(int)$per_page) .','. (int)$per_page;
	
	$rs = $DB->execute($sql_query);
	
	$num_total = @$DB->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	
?>

<table cellspacing="1">
<thead>
<tr>
	<th style="width:75px;"><?php echo __('Warteschlange'); ?> <small>&darr;</small></th>
	<th style="width:150px;"><?php echo __('Bezeichnung'); ?></th>
	<th style="width:50px;"><?php echo __('L&auml;nge'); ?></th>
	<th style="width:135px;"><?php echo __('Host'); ?></th>
	<th style="width:90px;" class="r">
<?php
		echo '<nobr>', ($page+1) ,' / ', $num_pages ,'</nobr> &nbsp; ',"\n";
		echo '<nobr>';
		if ($page > 0) {
			echo
			'<a href="',  gs_url($SECTION, $MODULE, null, 'page='.($page-1)), '" title="', __('zur&uuml;ckbl&auml;ttern'), '" id="arr-prev">',
			'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous.png" />',
			'</a>', "\n";
		} else {
			echo
			'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous_notavail.png" />', "\n";
		}
		if ($page < $num_pages-1) {
			echo
			'<a href="',  gs_url($SECTION, $MODULE, null, 'page='.($page+1)), '" title="', __('weiterbl&auml;ttern'), '" id="arr-next">',
			'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/next.png" />',
			'</a>', "\n";
		} else {
			echo
			'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/next_notavail.png" />', "\n";
		}
		echo '</nobr>';
?>
	</th>
</tr>
</thead>
<tbody>

<?php
	$i=0;
	while ($r = $rs->fetchRow()) {
		echo '<tr class="', ((++$i%2) ? 'odd':'even'), '">', "\n";
		
		echo '<td>', htmlEnt($r['name']) ,'</td>',"\n";
		
		echo '<td>', htmlEnt($r['_title']) ,'</td>',"\n";
		
		echo '<td class="r">', $r['maxlen'] ,'</td>',"\n";
		
		echo '<td>', $r['_host_id'] ,' (', @$hosts[$r['_host_id']] ,')</td>',"\n";
		
		echo '<td class="">',"\n";
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=edit&amp;qid='.$r['_id'] .'&amp;page='.$page) ,'" title="', __('bearbeiten'), '"><img alt="', __('bearbeiten'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=del&amp;qid='.$r['_id'] .'&amp;qname='.$r['name'] .'&amp;page='.$page) ,'" title="', __('l&ouml;schen'), '" onclick="return confirm_delete();"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
		echo '</td>',"\n";
		
		echo '</tr>',"\n";
	}
	echo '<tr class="', ((++$i%2) ? 'odd':'even'), '">', "\n";
	echo '<td colspan="4" class="transp">&nbsp;</td>',"\n";
	echo '<td class="transp">',"\n";
	echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=edit&amp;qid=0&amp;page='.$page) ,'" title="', __('neue Warteschlange anlegen'), '"><img alt="', __('hinzuf&uuml;gen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
	echo '</td>',"\n";
	echo '</tr>',"\n";
?>

</tbody>
</table>

<?php
}
#####################################################################
#                             view list }
#####################################################################




echo '<br />',"\n";
echo '<p class="text"><img alt=" " src="', GS_URL_PATH ,'crystal-svg/16/act/info.png" /> ', __('Es kann etwa 1 Minute dauern bis &Auml;nderungen aktiv werden.') ,'</p>',"\n";


?>