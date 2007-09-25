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
		AST_MGR_EXT_RINGINUSE => array('v'=>__('anklopfen'), 's'=>'yellow'), //TRANSLATE ME
		AST_MGR_EXT_ONHOLD    => array('v'=>__('halten'   ), 's'=>'red'   )  //TRANSLATE ME
	);
	return array_key_exists($extstate, $states) ? $states[$extstate] : null;
}



# get the peer users from ldap
#
if (! function_exists('gui_monitor_which_peers'))
	die('Error. Failed to get peers.');
$users = gui_monitor_which_peers( @$_SESSION['sudo_user']['name'] );
if (! is_array($users))
	die('Error. Failed to get peers.');
/*
echo "<pre>";
print_r($users);
echo "</pre>";
*/
if (count($users) < 1) die('--');


?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:60px;"><?php echo __('Nummer'); ?></th>
	<th style="width:190px;"><?php echo __('Name'); ?></th>
	<th style="width:80px;"><?php echo __('Status'); ?></th>
	<th style="width:100px;"><?php echo __('Umleitung'); ?></th>
	<th style="width:90px;"><?php echo __('Queues'); ?></th>
	<th style="width:130px;"><?php echo __('Bemerkung'); ?></th>
</tr>
</thead>
<tbody>

<?php

/*
,
	GROUP_CONCAT(`cf`.`source`) `cf_source`,
	GROUP_CONCAT(`cf`.`active`) `cf_active`,
	GROUP_CONCAT(`cf`.`number_std`) `cf_n_std`,
	GROUP_CONCAT(`cf`.`number_var`) `cf_n_var`
	*/
# get the corresponding users from our db
#
$users_sql = array();
foreach ($users as $user)
	$users_sql[] = '\''. $DB->escape($user) .'\'';
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
	`u`.`nobody_index` IS NULL AND
	`u`.`user` IN ('. implode(',',$users_sql) .') AND
	(`cf`.`case` IS NULL OR `cf`.`case`=\'always\')
GROUP BY `u`.`id`
ORDER BY `u`.`lastname`, `u`.`firstname`'
);
$i=0;
while ($user = $rs_users->fetchRow()) {
	
	$queues = explode(',', $user['queues']);
	# this damn function returns array(0=>'') for an empty string
	if (@$queues[0]=='') $queues = array();
	
	echo '<tr class="', ((++$i % 2 == 0) ? 'even':'odd'), '">';
	
	echo '<td>', htmlEnt($user['ext']), '</td>';
	
	echo '<td>', htmlEnt($user['ln']);
	if ($user['fn'] != '') echo ', ', htmlEnt($user['fn']);
	echo '</td>';
	
	$extstate = gs_extstate( $user['host'], $user['ext'] );
	$extinfos[$user['ext']]['info'] = $user;
	$extinfos[$user['ext']]['state'] = $extstate;
	$extstatev = _extstate2v( $extstate );
	if (defined('GS_GUI_MON_NOQUEUEBLUE') && GS_GUI_MON_NOQUEUEBLUE) {
		if ($extstate == AST_MGR_EXT_IDLE && count($queues) < 1) {
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
	echo '<td>', $img, ($extstatev ? $extstatev['v'] : '?'), '</td>';
	
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


<script type="text/javascript">/*<![CDATA[*/
window.setTimeout('document.location.reload();', 8000);
/*]]>*/</script>
