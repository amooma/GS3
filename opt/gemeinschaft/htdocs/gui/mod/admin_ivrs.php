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
* Author: Sven Neukirchner <s.neukirchner@konabi.de>
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
include_once( GS_DIR .'inc/gs-fns/gs_ivr_add.php' );
include_once( GS_DIR .'inc/gs-fns/gs_ivr_del.php' );
include_once( GS_DIR .'inc/gs-fns/gs_ivr_update.php' );
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
function enableInput( K_type ) {
	var K_VALUE  = K_type +"_value";
	var K_SELECT = K_type +"_select";
	if (document.getElementById(K_type).value == "repeat" || document.getElementById(K_type).value == "hangup" || document.getElementById(K_type).value == "") {
		document.getElementById(K_VALUE).style.display = "none";
		document.getElementById(K_SELECT).style.display = "none";
	}
	else if (document.getElementById(K_type).value == "extension") {
		document.getElementById(K_VALUE).style.display = "inline";
		document.getElementById(K_SELECT).style.display = "none";
	}
	else {
		document.getElementById(K_VALUE).style.display = "none";
		document.getElementById(K_SELECT).style.display = "inline";
	}
	//document.getElementById(K_VALUE).defaultValue = "";
	//document.getElementById(K_VALUE).value = document.getElementById(K_VALUE).defaultValue;
}
//]]>
</script>' ,"\n";


$action = @$_REQUEST['action'];
if (! in_array($action, array('','edit','save','del'), true))
	$action = '';

$ivr_id = (int)@$_REQUEST['iid'];

$per_page = (int)gs_get_conf('GS_GUI_NUM_RESULTS');
if ($per_page < 1) $per_page = 1;
$page     = (int)@$_REQUEST['page'];


$action_type = array(
	'hangup'	=> __('Auflegen'),
	'extension'	=> __('Rufnummer'),
	'announce'	=> __('Sprachnachricht'),
	'repeat'	=> __('Ansage wiederholen')
);
$keys = array(
	'0'	=> '0',
	'1'	=> '1',
	'2'	=> '2',
	'3'	=> '3',
	'4'	=> '4',
	'5'	=> '5',
	'6'	=> '6',
	'7'	=> '7',
	'8'	=> '8',
	'9'	=> '9',
	'#'	=> 'pound',
	'*'	=> 'star'
);


#####################################################################
#                               del {
#####################################################################
if ($action === 'del') {
	
	$ret = gs_ivr_del( @$_REQUEST['iname'] );
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
	
#	echo "<pre>\n";
#	print_r($_REQUEST);
#	echo "</pre>\n";
	
	
	$name          = preg_replace('/[^0-9]/', '', @$_REQUEST['name']);
	$title         = trim(@$_REQUEST['title']);
	$announcement  = @$_REQUEST['announcement'];
	$timeout       = preg_replace('/[^0-9]/', '', @$_REQUEST['timeout']);
	$retry         = preg_replace('/[^0-9]/', '', @$_REQUEST['retry']);
	
	
	$values = array ();
	
	$options = $keys;
	$options['t'] = 't';
	$options['i'] = 'i';
	
	foreach ($options as $key => $v) {
		# is the action a valid action
		if (! array_key_exists( @$_REQUEST['key_'.$v.'_type'] , $action_type )) {
			$value['key_'.$v.'_type'] = "";
			$value['key_'.$v.'_value']= "";
		}
		else {
			$value['key_'.$v.'_type'] = @$_REQUEST['key_'.$v.'_type'];
			if ($value['key_'.$v.'_type'] === 'extension') {
				$value['key_'.$v.'_value'] = preg_replace('/[^vm0-9]/', '', @$_REQUEST['key_'.$v.'_value']);
				if ($value['key_'.$v.'_value'] == '0' || strlen( $value['key_'.$v.'_value'] ) <= 0) {
					$value['key_'.$v.'_value'] = '';
					
					if ($v === 't' || $v === 'i')
						$value['key_'.$v.'_type'] = 'hangup';
					else {
						$value['key_'.$v.'_type'] = '';
					}
				}
			}
			elseif ($value['key_'.$v.'_type'] === 'announce') {
				$value['key_'.$v.'_value'] = preg_replace('/[^0-9]/', '', @$_REQUEST['key_'.$v.'_select']);
				if ($value['key_'.$v.'_value'] == '0' || strlen( $value['key_'.$v.'_value'] ) <= 0) {
					# there is no soundfile selected, so we have to select the default value
					$value['key_'.$v.'_value'] = '';
					
					if ($v === 't' || $v === 'i')
						$value['key_'.$v.'_type'] = 'hangup';
					else
						$value['key_'.$v.'_type'] = '';
					}
			}
			else {
				$value['key_'.$v.'_value'] = "";
			}
		}
	}
	
	$update_additional = false;
	if ($ivr_id < 1) {
		$ret = gs_ivr_add( $name, $title, $timeout, (int)@$_REQUEST['host_id'], $announcement );
		if (isGsError( $ret )) echo $ret->getMsg();
		else {
			$ivr_id = $DB->executeGetOne( 'SELECT `id` FROM `ivrs` WHERE `name`=\''. $DB->escape($name) .'\'' );
			if ($ivr_id < 1) $ivr_id = 0;
			else {
				$update_additional = true;
			}
		}
	} else {
			$update_additional = true;
			$ret = gs_ivr_update($announcement);
			if (isGsError( $ret )) {
				echo $ret->getMsg();
				$update_additional = false;
			}
			if ($timeout < 3) $timeout = 3;
	}
	
	
	if ($update_additional) {
		
		$DB->execute(
'UPDATE `ivrs` SET
	`title`           = \''. $DB->escape($title) .'\',
	`announcement`    = \''. $announcement .'\',
	`timeout`         = \''. $timeout .'\',
	`retry`           = \''. $retry .'\',
	`key_0_type`      = \''. $value['key_0_type'] .'\',
	`key_0_value`     = \''. $value['key_0_value'] .'\',
	`key_1_type`      = \''. $value['key_1_type'] .'\',
	`key_1_value`     = \''. $value['key_1_value'] .'\',
	`key_2_type`      = \''. $value['key_2_type'] .'\',
	`key_2_value`     = \''. $value['key_2_value'] .'\',
	`key_3_type`      = \''. $value['key_3_type'] .'\',
	`key_3_value`     = \''. $value['key_3_value'] .'\',
	`key_4_type`      = \''. $value['key_4_type'] .'\',
	`key_4_value`     = \''. $value['key_4_value'] .'\',
	`key_5_type`      = \''. $value['key_5_type'] .'\',
	`key_5_value`     = \''. $value['key_5_value'] .'\',
	`key_6_type`      = \''. $value['key_6_type'] .'\',
	`key_6_value`     = \''. $value['key_6_value'] .'\',
	`key_7_type`      = \''. $value['key_7_type'] .'\',
	`key_7_value`     = \''. $value['key_7_value'] .'\',
	`key_8_type`      = \''. $value['key_8_type'] .'\',
	`key_8_value`     = \''. $value['key_8_value'] .'\',
	`key_9_type`      = \''. $value['key_9_type'] .'\',
	`key_9_value`     = \''. $value['key_9_value'] .'\',
	`key_pound_type`  = \''. $value['key_pound_type'] .'\',
	`key_pound_value` = \''. $value['key_pound_value'] .'\',
	`key_star_type`   = \''. $value['key_star_type'] .'\',
	`key_star_value`  = \''. $value['key_star_value'] .'\',
	`t_action_type`   = \''. $value['key_t_type'] .'\',
	`t_action_value`  = \''. $value['key_t_value'] .'\',
	`i_action_type`   = \''. $value['key_i_type'] .'\',
	`i_action_value`  = \''. $value['key_i_value'] .'\'
WHERE `id`='.$ivr_id
		);
	}
	
	@exec('sudo '. qsa(GS_DIR.'sbin/start-asterisk') .' --dialplan' .' 1>>/dev/null 2>>/dev/null &' );
	
	$action = 'edit';
}
#####################################################################
#                               save }
#####################################################################




#####################################################################
#                               edit {
#####################################################################


if ($action === 'edit') {
	
	/*	
	
	// its also possible to make a target selection for the keys for queue, user, ivr 
	// but proberly bad when having lots of users
	
	
	
	$rs = $DB->execute( 'SELECT `name` FROM `ast_queues`');
	$queues_ext = array();
	while ($r = $rs->fetchRow()) {
		$queues_ext[] = $r['name'];
	}
	
	$rs = $DB->execute( 'SELECT `name` FROM `ast_sipfriends`');
	$sipfriends_ext = array();
	while ($r = $rs->fetchRow()) {
		$sipfriends_ext[] = $r['name'];
	}
	
	$rs = $DB->execute( 'SELECT `name` FROM `ivrs`');
	$ivrs_ext = array();
	while ($r = $rs->fetchRow()) {
		$ivrs_ext[] = $r['name'];
	}
	
	
	echo "<pre>\n";
	print_r($queues_ext);
	print_r($sipfriends_ext);
	print_r($ivrs_ext);
	echo "</pre>\n";
	
*/
	
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
	
	# get ivr
	#
	if ($ivr_id > 0) {
		$rs = $DB->execute(
			'SELECT * FROM `ivrs` WHERE	`id`='. $ivr_id	);
	 	$ivr = $rs->fetchRow();
	}
	else {
		$ivr_id = 0;
		$ivr = array(
			'name'             => '',
			'host_id'          => 0,
			'title'            => __('Neues Sprachmenü'),
			'announcement'     => '',
			'timeout'          => 5,
			'retry'            => 3,
			'key_0_type'       => '',
			'key_0_value'      => '',
			'key_1_type'       => '',
			'key_1_value'      => '',
			'key_2_type'       => '',
			'key_2_value'      => '',
			'key_3_type'       => '',
			'key_3_value'      => '',
			'key_4_type'       => '',
			'key_4_value'      => '',
			'key_5_type'       => '',
			'key_5_value'      => '',
			'key_6_type'       => '',
			'key_6_value'      => '',
			'key_7_type'       => '',
			'key_7_value'      => '',
			'key_8_type'       => '',
			'key_8_value'      => '',
			'key_9_type'       => '',
			'key_9_value'      => '',
			'key_pound_type'   => '',
			'key_pound_value'  => '',
			'key_star_type'    => '',
			'key_star_value'   => '',
			't_action_type'    => '',
			't_action_value'   => '',
			'i_action_type'    => '',
			'i_action_value'   => ''
		);
	}
	
	
	if (! is_array($ivr)) {
		$action = '';
	} else {
		
		echo '<h3>', htmlEnt($ivr['name']) ,' (', htmlEnt($ivr['title']) ,')</h3>' ,"\n";
		
		echo '<form name="ivr_form" method="post" action="', GS_URL_PATH, '" onload="enableinput()">', "\n";
		echo gs_form_hidden($SECTION, $MODULE), "\n";
		echo '<input type="hidden" name="action" value="save" />', "\n";
		echo '<input type="hidden" name="iid" value="', $ivr_id , '" />', "\n";
		echo '<input type="hidden" name="iname" value="', $ivr['name'] , '" />', "\n";
		echo '<input type="hidden" name="page" value="', $page, '" />', "\n";
		
		echo '<table cellspacing="1">',"\n";
		echo '<tbody>',"\n";
		
		echo '<tr>',"\n";
		echo '<th style="width:140px;" class="r">', __('Durchwahl') ,'</th>',"\n";
		echo '<td style="width:350px;">';
		echo '<input type="text" name="name" value="', htmlEnt($ivr['name']) ,'" size="8" maxlength="6" ', ($ivr_id > 0 ? 'disabled="disabled" ' : '') ,'/>', "\n";
		echo '</td>';
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Bezeichnung') ,'</th>',"\n";
		echo '<td>';
		echo '<input type="text" name="title" value="', htmlEnt($ivr['title']) ,'" size="30" maxlength="30" />', "\n";
		echo '</td>';
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Host') ,'</th>',"\n";
		echo '<td>';
		echo '<select name="host_id"', ($ivr_id > 0 ? ' disabled="disabled"' : '') ,'>', "\n";
		foreach ($hosts as $host_id => $host_ip) {
			echo '<option value="', $host_id ,'"', ($host_id == $ivr['host_id'] ? ' selected="selected"' : '') ,'>', $host_id ,' (', htmlEnt($host_ip) ,')</option>', "\n";
		}
		echo '</select>';
		echo '</td>';
		echo '</tr>',"\n";

		echo '<tr>',"\n";
		echo '<th class="r">', __('Ansagedatei') ,'</th>',"\n";
		echo '<td>';
		echo '<select name="announcement">', "\n";
		//echo '<option value="0"', ($queue['_sysrec_id'] == 0 ? ' selected="selected"' : '') ,'>', __('keine') ,'</option>', "\n";
		echo '<option value="0">', __('keine') ,'</option>', "\n";
		echo '<option value="" disabled="disabled">-</option>', "\n";
		foreach ($recordings as $rec_id => $desc) {
			echo '<option value="' . $rec_id .'"', ( htmlEnt($ivr['announcement']) == $rec_id ? ' selected="selected"' : '') ,'>', __($desc) ,'</option>', "\n";
		}
		echo '</select>';
		echo '</td>';
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Antwortzeit') ,'</th>',"\n";
		echo '<td>',"\n";
		echo '<input type="text" name="timeout" value="', $ivr['timeout'] ,'" size="3" maxlength="3" class="r" />',"\n";
		echo '</td>';
		echo '</tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Wiederholungen') ,'</th>',"\n";
		echo '<td>',"\n";
		echo '<input type="text" name="retry" value="', $ivr['retry'] ,'" size="3" maxlength="3" class="r" />', "\n";
		echo '</td>';
		echo '</tr>',"\n";
		
		echo '<tr><td class="transp" colspan="2">&nbsp;</td></tr>',"\n";
		
		foreach ($keys as $key => $v) {
			echo '<tr>',"\n";
			echo '<th class="r">', __('Taste '. $key .'') ,'</th>',"\n";
			echo '<td>',"\n";
			echo '<select name="key_'.$v.'_type" id="key_'.$v.'" "style="width:150px; margin:2px 5px 1px 0px;" onChange="enableInput(\'key_'.$v.'\');">', "\n";
			echo '<option value=""'.($ivr['key_'.$v.'_type'] == '' ? ' selected="selected"' : '').'>---</option>\n';
				foreach ($action_type as $action => $description) {
				echo '<option value="'. $action .'"'. ($action == $ivr['key_'.$v.'_type'] ? ' selected="selected"' : ''). '>'. $description . '</option>', "\n";
			}
			
			if ($ivr['key_'.$v.'_type'] === 'extension') {
				$ivr['key_'.$v.'_value_select'] = 0;
			}
			elseif ($ivr['key_'.$v.'_type'] === 'announce') {
				$ivr['key_'.$v.'_value_select'] = $ivr['key_'.$v.'_value'];
				$ivr['key_'.$v.'_value'] = '';
			}
			else {
				$ivr['key_'.$v.'_value_select'] = '';
			}
			
			echo '</select>', "\n";
			echo '<input type="text" name="key_'.$v.'_value" id="key_'.$v.'_value" value="', $ivr['key_'.$v.'_value'] ,'" size="10" maxlength="20" class="r" ', ($ivr['key_'.$v.'_type'] === 'extension' ? '' : ' style="display:none"' ), '/>', "\n";
			
			echo '<select name="key_'.$v.'_select" id="key_'.$v.'_select" ', ($ivr['key_'.$v.'_type'] === 'announce' ? '' : ' style="display:none"' ) ,'" >', "\n";
			echo '<option value="0"', ( htmlEnt( $ivr['key_'.$v.'_value_select']) == 0 ? ' selected="selected"' : '') ,'>', __('keine') ,'</option>', "\n";
			echo '<option value="" disabled="disabled">-</option>', "\n";
			foreach ($recordings as $rec_id => $desc) {
				echo '<option value="' . $rec_id .'"', ( htmlEnt( $ivr['key_'.$v.'_value_select']) == $rec_id ? ' selected="selected"' : '') ,'>', __($desc) ,'</option>', "\n";
			}
			echo '</select>';
			
			echo '</td>';
			echo '</tr>',"\n";
		}
		
		
		echo '<tr><td class="transp" colspan="2">&nbsp;</td></tr>',"\n";
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Ziel bei keiner Eingabe') ,'</th>',"\n";
		echo '<td>',"\n";
		echo '<select name="key_t_type" id="t_action" "style="width:150px; margin:2px 5px 1px 0px;" onChange="enableInput(\'t_action\');">', "\n";
		foreach ($action_type as $action => $description) {
			echo '<option value="', $action, '"', ($action == $ivr['t_action_type'] ? ' selected="selected"' : ''), '>', $description, '</option>', "\n";
		}
		echo '</select>', "\n";
		
		if ($ivr['t_action_type'] === 'extension') {
			$ivr['t_action_value_select'] = '0';
		}
		elseif ($ivr['t_action_type'] === 'announce') {
			$ivr['t_action_value_select'] = $ivr['t_action_value'];
			$ivr['t_action_value'] = '0';
		}
		else {
			$ivr['t_action_value_select'] = '';
		}
		
		echo '<input type="text" name="key_t_value" id="t_action_value" value="', $ivr['t_action_value'] ,'" size="10" maxlength="20" class="r" ', ($ivr['t_action_type'] == "extension" ? '' : ' style="display:none"'), '/>', "\n";
		
		echo '<select name="key_t_select" id="t_action_select" ', ($ivr['t_action_type'] === 'announce' ? '' : ' style="display:none"' ) ,'" >', "\n";
		echo '<option value="0"', ( htmlEnt( $ivr["t_action_value_select"]) == 0 ? ' selected="selected"' : '') ,'>', __('keine') ,'</option>', "\n";
		echo '<option value="" disabled="disabled">-</option>', "\n";
		foreach ($recordings as $rec_id => $desc) {
			echo '<option value="' . $rec_id .'"', ( htmlEnt( $ivr["t_action_value_select"]) == $rec_id ? ' selected="selected"' : '') ,'>', __($desc) ,'</option>', "\n";
		}
		echo '</select>';

		echo '</td>';
		echo '</tr>',"\n";
		
		
		echo '<tr>',"\n";
		echo '<th class="r">', __('Ziel bei falscher Eingabe') ,'</th>',"\n";
		echo '<td>',"\n";
		echo '<select name="key_i_type" id="i_action" "style="width:150px; margin:2px 5px 1px 0px;" onChange="enableInput(\'i_action\');">', "\n";
		foreach ($action_type as $action => $description) {
			echo '<option value="', $action, '"', ($action == $ivr['i_action_type'] ? ' selected="selected"' : ''), '>', $description, '</option>', "\n";
		}
		echo '</select>', "\n";
		
		if ($ivr['i_action_type'] === 'extension') {
			$ivr['i_action_value_select'] = '0';
		}
		elseif ($ivr['i_action_type'] === 'announce') {
			$ivr['i_action_value_select'] = $ivr['i_action_value'];
			$ivr['i_action_value'] = '0';
		}
		else {
			$ivr['i_action_value_select'] = '';
		}
		
		
		echo '<input type="text" name="key_i_value" id="i_action_value" value="', $ivr['i_action_value'] ,'" size="10" maxlength="20" class="r" ', ($ivr['i_action_type'] === 'extension' ? '' : ' style="display:none"' ), '/>', "\n";
		
		echo '<select name="key_i_select" id="i_action_select" ', ($ivr['i_action_type'] === 'announce' ? '' : ' style="display:none"' ) ,'" >', "\n";
		echo '<option value="0"', ( htmlEnt( $ivr['i_action_value_select']) == 0 ? ' selected="selected"' : '') ,'>', __('keine') ,'</option>', "\n";
		echo '<option value="" disabled="disabled">-</option>', "\n";
		foreach ($recordings as $rec_id => $desc) {
			echo '<option value="' . $rec_id .'"', ( htmlEnt( $ivr['i_action_value_select']) == $rec_id ? ' selected="selected"' : '') ,'>', __($desc) ,'</option>', "\n";
		}
		echo '</select>';
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
	
	# get ivrs
	#
	$sql_query =
'SELECT SQL_CALC_FOUND_ROWS
	`id`, `name`, `host_id`, `title`
FROM `ivrs`
ORDER BY `name`
LIMIT '. ($page*(int)$per_page) .','. (int)$per_page;
	
	$rs = $DB->execute($sql_query);
	
	$num_total = @$DB->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	
?>

<table cellspacing="1">
<thead>
<tr>
	<th style="width:85px;"><?php echo __('Sprachmen&uuml;'); ?> <small>&darr;</small></th>
	<th style="width:150px;"><?php echo __('Bezeichnung'); ?></th>
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
		
		echo '<td>', htmlEnt($r['title']) ,'</td>',"\n";
		
		echo '<td>', $r['host_id'] ,' (', @$hosts[$r['host_id']] ,')</td>',"\n";
		
		echo '<td class="">',"\n";
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=edit&amp;iid='.$r['id'] .'&amp;page='.$page) ,'" title="', __('bearbeiten'), '"><img alt="', __('bearbeiten'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=del&amp;iid='.$r['id'] .'&amp;iname='.$r['name'] .'&amp;page='.$page) ,'" title="', __('l&ouml;schen'), '" onclick="return confirm_delete();"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
		echo '</td>',"\n";
		
		echo '</tr>',"\n";
	}
	echo '<tr class="', ((++$i%2) ? 'odd':'even'), '">', "\n";
	echo '<td colspan="3" class="transp">&nbsp;</td>',"\n";
	echo '<td class="transp">',"\n";
	echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=edit&amp;iid=0&amp;page='.$page) ,'" title="', __('neues Sprachmenu anlegen'), '"><img alt="', __('hinzuf&uuml;gen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
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



?>
