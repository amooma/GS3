<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 4820 $
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* 
* Author: Andreas Neugebauer <neugebauer@loca.net>
*
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

######################################################
##
##   ALL STRINGS IN HERE NEED TO BE TRANSLATED!
##
######################################################

defined('GS_VALID') or die('No direct access.');
require_once( GS_DIR .'inc/extension-state.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_agent_add.php' );
include_once( GS_DIR .'inc/gs-fns/gs_agent_del.php' );
include_once( GS_DIR .'inc/gs-fns/gs_agents_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_agent_update.php' );
include_once( GS_DIR .'inc/gs-fns/gs_queue_agent_add.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


function count_agents_configured( $DB ) {
	$num = (int)$DB->executeGetOne( 'SELECT COUNT(*) FROM `agents`');
	return $num;
}
function count_agents_logged_in( $DB ) {
	$num = (int)$DB->executeGetOne( 'SELECT COUNT(*) FROM `agents` WHERE `user_id` !=0');
	return $num;
}



echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";


$per_page = (int)GS_GUI_NUM_RESULTS;

$name        = trim(@$_REQUEST['name'     ]);
$number      = trim(@$_REQUEST['number'   ]);
$page        = (int)@$_REQUEST['page'     ] ;
$edit_agent   = trim(@$_REQUEST['edit'     ]);
$save_agent   = trim(@$_REQUEST['save'     ]);
$delete_agent = trim(@$_REQUEST['delete'   ]);

$upqueues    =      @$_REQUEST['upqueues'  ] ;
$upqueued   =      @$_REQUEST['upqueued'] ;

$agent_name   = trim(@$_REQUEST['aname'    ]);
$agent_lastname   = trim(@$_REQUEST['alastname'    ]);
$agent_firstname   = trim(@$_REQUEST['afirstname'    ]);
$agent_number   = trim(@$_REQUEST['anumber'    ]);
$agent_pin    = trim(@$_REQUEST['apin'     ]);


if ($delete_agent) {
	$ret = gs_agent_del( $delete_agent );
	if (isGsError( $ret )) echo $ret->getMsg();
}
if ($save_agent) {
	$ret = gs_agent_update( $save_agent, $agent_pin, $agent_lastname, $agent_firstname );
	if (isGsError( $ret )) echo $ret->getMsg();
}
if ($agent_name) {
	$ret = gs_agent_add( $agent_name, $agent_firstname, $agent_number, $agent_pin );
	if (isGsError( $ret )) echo $ret->getMsg();
}


if ($upqueued && $edit_agent) {
	$sql_query = 'DELETE `q`
FROM `agent_queues` `q` , `agents` `a`
WHERE
	`q`.`agent_id` = `a`.`id` AND
	`a`.`number` = \''.$DB->escape($edit_agent).'\'';
	
	$rs = $DB->execute($sql_query);
	
	if (is_array($upqueued)) {
		foreach ($upqueued as $upqueue) {
			if ($upqueue < 1) continue;
			$ret = gs_queue_agent_add( $upqueue, $edit_agent );
			if (isGsError( $ret )) echo $ret->getMsg();
		}
	}
}

if (!$edit_agent) {
	
	if ($number != '') {
		
		# search by number
		
		$search_url = 'number='. urlEncode($number);
		
		$number_sql = str_replace(
			array( '*', '?' ),
			array( '%', '_' ),
			$number
		) .'%';
		 $rs = $DB->execute(
'SELECT SQL_CALC_FOUND_ROWS
	`name`, `firstname`, `number`, `pin`,`user_id`, `paused`
FROM
	`agents`
WHERE
	`number` LIKE \''. $DB->escape($number_sql) .'\'
	
ORDER BY `number`
LIMIT '. ($page*(int)$per_page) .','. (int)$per_page
		);
		$num_total = @$DB->numFoundRows();
		$num_pages = ceil($num_total / $per_page);
		
	} else {
		
		# search by name
		
		$number = '';
		$search_url = 'name='. urlEncode($name);
		
		$name_sql = str_replace(
			array( '*', '?' ),
			array( '%', '_' ),
			$name
		) .'%';
		$rs = $DB->execute( 
'SELECT SQL_CALC_FOUND_ROWS
	`name`, `firstname`, `number`, `pin`, `user_id`, `paused`
FROM
	`agents`
WHERE
	`name` LIKE _utf8\''. $DB->escape($name_sql) .'\' COLLATE utf8_unicode_ci
	
ORDER BY `name`
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
		'<a href="', gs_url($SECTION, $MODULE, null, $search_url .'&amp;page='.($page-1)), '" title="', __('zur&uuml;ckbl&auml;ttern'), '" id="arr-prev">',
		'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/back-cust.png" />',
		'</a>', "\n";
	} else {
		echo
		'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/back-cust-dis.png" />', "\n";
	}
	if ($page < $num_pages-1) {
		echo
		'<a href="', gs_url($SECTION, $MODULE, null, $search_url .'&amp;page='.($page+1)), '" title="', __('weiterbl&auml;ttern'), '" id="arr-next">',
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
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'name='. htmlEnt($cs)), '">', htmlEnt($cd), '</a>', "\n";
	}
	
?>
		</td>
	</tr>
	</tbody>
	</table>
	
	<table cellspacing="1" class="phonebook">
	<thead>
	<tr>
		<th style="width:200px;" class="sort-col"><?php echo __('Nachname'),', ', __('Vorname'); ?></th>
		<th style="width: 60px;"><?php echo __('Nummer' ); /*//TRANSLATEME*/ ?></th>
		<th style="width: 55px;"><?php echo __('PIN'      ); ?></th>
		<th style="width: 85px;"><?php echo __('Status'   ); ?></th>
		<th style="width: 55px;">&nbsp;</th>
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
			
			echo '<td>', $r['name'],", ", htmlEnt($r['firstname']), '</td>' ,"\n";
			echo '<td>', htmlEnt($r['number']), '</td>' ,"\n";
			echo '<td>', str_repeat('*', strLen($r['pin'])) ,'</td>' ,"\n";
			
			echo '<td>';
			if($r['paused'] === 1){
				echo '<img alt=" " src="', GS_URL_PATH, 'crystal-svg/16/act/yellowled.png" />&nbsp;', __('pausiert');
			}
			else{
				switch ($r['user_id']) {
				case 0:
					echo '<img alt=" " src="', GS_URL_PATH, 'crystal-svg/16/act/free_icon.png" />&nbsp;', __('offline');
					break;
			
				default:
					echo '<img alt=" " src="', GS_URL_PATH, 'crystal-svg/16/act/greenled.png" />&nbsp;', __('bereit');
					break;
				
				}
			}
			echo '</td>';
			
			echo '<td>';
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'edit='. rawUrlEncode($r['number']) .'&amp;name='. rawUrlEncode($name) .'&amp;number='. rawUrlEncode($number) .'&amp;page='.$page), '" title="',__('bearbeiten'), '"><img alt="',__('bearbeiten'), '" src="',GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='. rawUrlEncode($r['number']) .'&amp;name='. rawUrlEncode($name) .'&amp;number='. rawUrlEncode($number) .'&amp;page='.$page), '" title="',__('l&ouml;schen'), '"><img alt="',__('entfernen'), '" src="',GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
			echo "</td>\n";
			
			echo '</tr>', "\n";
			
		}
	}
	
	?>
	<tr>
	<?php
	
	if (!$edit_agent) {
		
		//FIXME - tr > form is invalid
		echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
		echo gs_form_hidden($SECTION, $MODULE), "\n";
		echo '<input type="hidden" name="name" value="', htmlEnt($name), '" />', "\n";
		echo '<input type="hidden" name="number" value="', htmlEnt($number), '" />', "\n";
	?>
		<td>
			<input type="text" name="aname" value="" size="15" maxlength="40" style="width:80px;" title="<?php echo __('Nachname'); ?>" />,
			<input type="text" name="afirstname" value="" size="15" maxlength="40" style="width:80px;" title="<?php echo __('Vorname'); ?>" />
		</td>
		<td>
			<input type="text" name="anumber" value="" size="8" maxlength="10" />
		</td>
		<td>
			<input type="password" name="apin" value="" size="5" maxlength="10" />
		</td>
		<td>
			&nbsp;
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
		<th colspan="2">
			<span class="sort-col"><?php echo __('Benutzer'); ?></span>
		</th>
	</tr>
	</thead>
	<tbody>
	<tr>
		<th><?php echo __('Eingerichtete Benutzer'); ?>:</th>
		<td class="r" style="min-width:4em;"><?php echo count_agents_configured($DB); ?></td>
	</tr>
	<tr>
		<th><?php echo __('Eingeloggte Benutzer'); ?>:
		</th>
		<td class="r"><?php echo count_agents_logged_in($DB); ?></td>
	</tr>
	</tbody>
	</table>
<?php
} else {
	/*
	$sql_query = 'SELECT `id`, `title`
	FROM `pickupgroups`';
	$rs = $DB->execute($sql_query);
	
	$pgroups = array();
	if (@$rs) {
		while ($r = $rs->fetchRow()) {
			$pgroups[$r['id']] = $r['title'];
		}
	}
	*/

	$rs = $DB->execute(
'SELECT
	 `name`, `number`, `firstname`, `pin`,`user_id`
FROM
	`agents`
WHERE
	`number` = \''. $DB->escape($edit_agent) .'\''
	);
	
	if ($rs) $r = $rs->fetchRow();
	

	$sql_query =
'SELECT `_id`, `name`, `_title`
FROM
	`ast_queues`
GROUP BY `_id`';


	$rs = $DB->execute($sql_query);
	$pqueues = array();
	if (@$rs) {
		while ($r_pg = $rs->fetchRow()) {
			$pqueues[$r_pg['_id']] =  $r_pg['name'] . " " . $r_pg['_title'];
		}
	}
	
	$sql_query =
'SELECT `agent_queues`.`queue_id`
FROM
	`agent_queues`, `agents`
WHERE 
	`agents`.`id` = `agent_queues`.`agent_id`
	AND `agents`.`number` = \''.$DB->escape($edit_agent).'\'';

	$rs = $DB->execute($sql_query);
	$pqueues_my = array();
	if (@$rs) {
		while ($r_pg = $rs->fetchRow()) {
			$pqueues_my[$r_pg['queue_id']] = $r_pg['queue_id'];
		}
	}
	

	
?>



<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php
echo gs_form_hidden($SECTION, $MODULE), "\n";
echo '<input type="hidden" name="save" value="', htmlEnt($edit_agent), '" />', "\n";
?>
<table cellspacing="1">
<thead>
	<tr>
		<th style="width:180px;">
			<?php echo __('Name'); ?>
		</th>
		<td>
			<input type="text" name="alastname" value="<?php echo htmlEnt($r['name']); ?>" size="30" maxlength="50" />
		</td>
	</tr>
</thead>
<tbody>
	<tr>
		<th><?php echo __('Vorname'); ?>:</th>
		<td>
			<input type="text" name="afirstname" value="<?php echo htmlEnt($r['firstname']); ?>" size="30" maxlength="50" />
		</td>
	</tr>
	<tr>
		<th><?php echo __('Nummer'); /*//TRANSLATEME*/ ?>:</th>
		<td>
			<?php echo $r['number']; ?>
		</td>
	</tr>

	<tr>
		<th><?php echo __('PIN'); ?>:</th>
		<td>
			<input type="text" name="apin" value="<?php echo htmlEnt($r['pin']); ?>" size="8" maxlength="10" />
		</td>
	</tr>


	<tr>
		<th>&nbsp;</th>
		<th>
			<button type="submit" title="<?php echo __('Speichern'); ?>" class="plain">
				<img alt="<?php echo __('Speichern'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
			</button>
		</th>
	</tr>
</tbody>
</table>
</form>

<br />

<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php
echo gs_form_hidden($SECTION, $MODULE), "\n";
echo '<input type="hidden" name="edit" value="', htmlEnt($edit_agent), '" />', "\n";
echo '<input type="hidden" name="upqueued" value="yes" />', "\n";
?>
<table cellspacing="1">
<thead>
	<tr>
		<th style="width:180px;">
			<?php echo __('Queues'); ?>
		</th>
		<td style="width:280px;">
<?php
		echo '<select multiple="multiple" name="upqueued[]" size="8">',"\n";
		foreach ($pqueues as $key => $pqueue) {
			echo '<option value="',$key,'"';
			if (@$pqueues_my[$key]) echo ' selected="selected"';
			echo '>', $key ,' (', htmlEnt($pqueue) ,')</option>',"\n";
		}
		echo '<option value=""></option>',"\n";
		echo '</select>',"\n";
?>		
		</td>
	</tr>
</thead>
<tbody>
	<tr>
		<th>&nbsp;</th>
		<th>
			<button type="submit" title="<?php echo __('Speichern'); ?>" class="plain">
				<img alt="<?php echo __('Speichern'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
			</button>
		</th>
	</tr>
</tbody>
</table>
</form>


<?php
} 
?>
