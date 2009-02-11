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
include_once( GS_DIR .'inc/gs-fns/gs_queue_add.php' );
include_once( GS_DIR .'inc/gs-fns/gs_queue_del.php' );
include_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );

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
if (! in_array($action, array('','edit','save','del'), true))
	$action = '';

$queue_id = (int)@$_REQUEST['qid'];

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
	$musicclass = @$_REQUEST['musicclass'];
	if (! in_array($musicclass, array('default', ''), true))
		$musicclass = 'default';
	$musicclass_db = ($musicclass != '' ? '\''. $DB->escape($musicclass) .'\'' : 'NULL');
	
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
	
	# get queue
	#
	if ($queue_id > 0) {
		$rs = $DB->execute(
'SELECT
	`name`, `_host_id`, `_title`, `musicclass`, `announce_holdtime`, `timeout`, `wrapuptime`, `maxlen`, `strategy`, `joinempty`, `leavewhenempty`
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
		echo '<th style="width:140px;" class="r">', __('Durchwahl') ,'</th>',"\n";
		echo '<td style="width:350px;">';
		//echo '<input type="text" name="name" value="', htmlEnt($queue['name']) ,'" size="8" maxlength="6" />', "\n";
		echo '<input type="text" name="name" value="', htmlEnt($queue['name']) ,'" size="8" maxlength="6" ', ($queue_id > 0 ? 'disabled="disabled" ' : '') ,'/>', "\n";
		echo '</td>';
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Bezeichnung') ,'</th>',"\n";
		echo '<td>';
		echo '<input type="text" name="_title" value="', htmlEnt($queue['_title']) ,'" size="30" maxlength="30" />', "\n";
		echo '</td>';
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Host') ,'</th>',"\n";
		echo '<td>';
		echo '<select name="_host_id"', ($queue_id > 0 ? ' disabled="disabled"' : '') ,'>', "\n";
		foreach ($hosts as $host_id => $host_ip) {
			echo '<option value="', $host_id ,'"', ($host_id == $queue['_host_id'] ? ' selected="selected"' : '') ,'>', $host_id ,' (', htmlEnt($host_ip) ,')</option>', "\n";
		}
		echo '</select>';
		echo '</td>';
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Wartemusik') ,'</th>',"\n";
		echo '<td>';
		echo '<select name="musicclass">', "\n";
		echo '<option value="default"', ($queue['musicclass'] ==='default' ? ' selected="selected"' : '') ,'>default</option>', "\n";
		echo '<option value="" disabled="disabled">-</option>', "\n";
		echo '<option value=""', ($queue['musicclass'] !=='default' ? ' selected="selected"' : '') ,'>', __('Klingeln statt Musik') ,'</option>', "\n";
		echo '</select>';
		echo '</td>';
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Wartezeit ansagen') ,'</th>',"\n";
		echo '<td>',"\n";
		
		echo '<input type="radio" name="announce_holdtime" id="ipt-announce_holdtime-yes" value="yes" ', ($queue['announce_holdtime']==='yes' ? 'checked="checked" ' : '') ,'/>', "\n";
		echo '<label for="ipt-announce_holdtime-yes">', __('ja') ,'</label> &nbsp;', "\n";
		
		echo '<input type="radio" name="announce_holdtime" id="ipt-announce_holdtime-once" value="once" ', ($queue['announce_holdtime']==='once' ? 'checked="checked" ' : '') ,'/>', "\n";
		echo '<label for="ipt-announce_holdtime-once">', __('einmal') ,'</label> &nbsp;', "\n";
		
		echo '<input type="radio" name="announce_holdtime" id="ipt-announce_holdtime-no" value="no" ', ($queue['announce_holdtime']==='no' ? 'checked="checked" ' : '') ,'/>', "\n";
		echo '<label for="ipt-announce_holdtime-no">', __('nein') ,'</label>', "\n";
		
		echo '</td>';
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Nachbereitungszeit') ,'</th>',"\n";
		echo '<td>';
		echo '<input type="text" name="wrapuptime" value="', $queue['wrapuptime'] ,'" size="3" maxlength="3" class="r" /> s', "\n";
		echo '</td>';
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Klingelzeit p. Agent') ,'</th>',"\n";
		echo '<td>';
		echo '<input type="text" name="timeout" value="', $queue['timeout'] ,'" size="3" maxlength="3" class="r" /> s', "\n";
		echo '</td>';
		echo '</tr>',"\n";		
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Max. Anrufer') ,'</th>',"\n";
		echo '<td>';
		echo '<input type="text" name="maxlen" value="', $queue['maxlen'] ,'" size="3" maxlength="3" class="r" />', "\n";
		echo '</td>';
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Strategie') ,'</th>',"\n";
		echo '<td>',"\n";
		echo '<select name="strategy">', "\n";
		echo '<option value="rrmemory"', ($queue['strategy']==='rrmemory' ? ' selected="selected"' : '') ,'>', __('Round-Robin') ,'</option>', "\n";
		echo '<option value="leastrecent"', ($queue['strategy']==='leastrecent' ? ' selected="selected"' : '') ,'>', __('&auml;ltesten') ,'</option>', "\n";
		echo '<option value="random"', ($queue['strategy']==='random' ? ' selected="selected"' : '') ,'>', __('zuf&auml;llig') ,'</option>', "\n";
		echo '<option value="fewestcalls"', ($queue['strategy']==='fewestcalls' ? ' selected="selected"' : '') ,'>', __('am wenigsten Anrufe') ,'</option>', "\n";
		echo '<option value="ringall"', ($queue['strategy']==='ringall' ? ' selected="selected"' : '') ,'>', __('alle anklingeln') ,'</option>', "\n";
		echo '</select>';
		echo '</td>';
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Eintritt') ,'</th>',"\n";
		echo '<td>',"\n";
		echo '<input type="radio" name="joinempty" id="ipt-joinempty-yes" value="yes" ', ($queue['joinempty']==='yes' ? 'checked="checked" ' : '') ,'/>', "\n";
		echo '<label for="ipt-joinempty-yes">', __('auch wenn keine Agenten angemeldet') ,'</label>', "\n";
		echo '<br />',"\n";
		echo '<input type="radio" name="joinempty" id="ipt-joinempty-no" value="no" ', ($queue['joinempty']==='no' ? 'checked="checked" ' : '') ,'/>', "\n";
		echo '<label for="ipt-joinempty-no">', __('nicht wenn keine Agenten angemeldet') ,'</label>', "\n";
		echo '<br />',"\n";
		echo '<input type="radio" name="joinempty" id="ipt-joinempty-strict" value="strict" ', ($queue['joinempty']==='strict' ? 'checked="checked" ' : '') ,'/>', "\n";
		echo '<label for="ipt-joinempty-strict">', __('nicht wenn keine Agenten angemeldet und frei') ,'</label>', "\n";
		echo '</td>';
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Austritt') ,'</th>',"\n";
		echo '<td>',"\n";
		
		echo '<input type="radio" name="leavewhenempty" id="ipt-leavewhenempty-no" value="no" ', ($queue['leavewhenempty']==='no' ? 'checked="checked" ' : '') ,'/>', "\n";
		echo '<label for="ipt-leavewhenempty-no">', __('nie') ,'</label>', "\n";
		echo '<br />',"\n";
		
		echo '<input type="radio" name="leavewhenempty" id="ipt-leavewhenempty-yes" value="yes" ', ($queue['leavewhenempty']==='yes' ? 'checked="checked" ' : '') ,'/>', "\n";
		echo '<label for="ipt-leavewhenempty-yes">', __('wenn keine Agenten mehr angemeldet') ,'</label>', "\n";
		echo '<br />',"\n";
		
		echo '<input type="radio" name="leavewhenempty" id="ipt-leavewhenempty-strict" value="strict" ', ($queue['leavewhenempty']==='strict' ? 'checked="checked" ' : '') ,'/>', "\n";
		echo '<label for="ipt-leavewhenempty-strict">', __('wenn keine Agenten mehr angemeldet und frei') ,'</label>', "\n";
		
		echo '</td>';
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
		
		echo '</form>',"\n";
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
