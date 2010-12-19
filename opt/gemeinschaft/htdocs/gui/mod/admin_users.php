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
require_once( GS_DIR .'inc/extension-state.php' );
require_once( GS_DIR .'lib/yadb/yadb_mptt.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_add.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_change.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_del.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_external_number_add.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_external_number_del.php' );
include_once( GS_DIR .'inc/gs-fns/gs_pickupgroup_user_add.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callblocking_set.php' );
include_once( GS_DIR .'inc/group-fns.php' );
require_once( GS_DIR .'inc/boi-soap/boi-api.php' );
include_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );
include_once( GS_DIR .'inc/langhelper.php' );

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


function count_users_configured( $DB ) {
	$num = (int)$DB->executeGetOne( 'SELECT COUNT(*) FROM `users` WHERE `nobody_index` IS NULL');
	return $num;
}
function count_users_logged_in( $DB ) {
	$num = (int)$DB->executeGetOne( 'SELECT COUNT(*) FROM `phones` `p` JOIN `users` `u` ON (`u`.`id`=`p`.`user_id`) WHERE `u`.`nobody_index` IS NULL');
	return $num;
}



echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";


$per_page = (int)GS_GUI_NUM_RESULTS;

$name        = trim(@$_REQUEST['name'     ]);
$number      = trim(@$_REQUEST['number'   ]);
$page        = (int)@$_REQUEST['page'     ] ;

$edit_user   = trim(@$_REQUEST['edit'     ]);
$delete_user = trim(@$_REQUEST['delete'   ]);
$action      = trim(@$_REQUEST['action'   ]);
$penalty     = (int)trim(@$_REQUEST['penalty'   ]);
$queue_id     = (int)trim(@$_REQUEST['queue_id'   ]);
if (! in_array($action, array('list','del','add','add-and-view','view','edit','save','insert-group','remove-group','setpenalty','delpenalty'), true))
	$action = 'list';

$cbdelete    = trim(@$_REQUEST['cbdelete' ]);
$cbregexp    = trim(@$_REQUEST['cbregexp' ]);
$cbpin       = trim(@$_REQUEST['cbpin'    ]);

$extnum      = trim(@$_REQUEST['extnum'   ]);
$extnumdel   = trim(@$_REQUEST['extndel'  ]);

$u_pgrps     =      @$_REQUEST['u_pgrps'  ] ;
$u_pgrp_ed   =      @$_REQUEST['u_pgrp_ed'] ;

$u_prv_grp_ed=      @$_REQUEST['u_prv_grp_ed'];
$u_prv_grp_id= (int)@$_REQUEST['u_prv_grp_id'];

$u_groups_ed =      @$_REQUEST['u_groups_ed'];
$u_groups    =      @$_REQUEST['u_groups' ] ;

$user_fname  = trim(@$_REQUEST['ufname'   ]);
$user_lname  = trim(@$_REQUEST['ulname'   ]);
$user_ext    = trim(@$_REQUEST['uext'     ]);
$user_name   = trim(@$_REQUEST['uuser'    ]);
$user_pin    = trim(@$_REQUEST['upin'     ]);
$user_email  = trim(@$_REQUEST['uemail'   ]);
$user_host   = trim(@$_REQUEST['uhost'    ]);

$bp_add_h    = (int)@$_REQUEST['bp_add_h' ] ;
$bp_del_h    = (int)@$_REQUEST['bp_del_h' ] ;

$group       = (int)@$_REQUEST['group'    ] ;

$sort        = @$_REQUEST['sort'];
if (! in_array($sort, array('user', 'name', 'ext', 'email', 'host')) )
	$sort = 'name';
$sortorder   = @$_REQUEST['sortorder'];
if (! in_array($sortorder, array('ASC', 'DESC')) )
	$sortorder = 'ASC';

$pen_avail = range( 0, 9);
if ($edit_user) {
	
	if ($action === 'insert-group') {
		$ret = gs_group_member_add( $group,  $edit_user);
		if (isGsError( $ret )) echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
		sleep(1); //FIXME
		$action = 'view';
	}
	if ($action === 'remove-group') {
		$ret = gs_group_member_del( $group,  $edit_user);
		if (isGsError( $ret )) echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
		sleep(1); //FIXME
		$action = 'view';
	}
	
	$uid = (int)$DB->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $DB->escape($edit_user) .'\'' );
	
	if ($uid == 0)
		echo '<div class="errorbox">', "Error. User not Found." ,'</div>',"\n";	
	
	$groups     = gs_group_info_get(false, 'user');
	
	$groups_my  = gs_group_members_groups_get(Array($uid), 'user', false);
	
}


if ($action === 'del') {
	
	if ($delete_user) {
		$ret = gs_user_del( $delete_user );
		if (isGsError( $ret )) echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
	}
	
	$action = 'list';
}

if ($action === 'add' || $action === 'add-and-view') {
	
	if ($user_name) {
		$user_lang = gs_get_lang_global(GS_LANG_OPT_AST, GS_LANG_FORMAT_AST);
		$ret = gs_user_add( $user_name, $user_ext, $user_pin, $user_fname, $user_lname, $user_lang, $user_host, $user_email );
		if (isGsError( $ret )) echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
		
		if ($action === 'add-and-view') {
			$action = 'view';
			$edit_user = $user_name;
			
			$uid = (int)$DB->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $DB->escape($edit_user) .'\'' );
			
			if ($uid == 0)
				echo '<div class="errorbox">', "Error. User not Found." ,'</div>',"\n";	
			
			$groups     = gs_group_info_get(false, 'user');
			$groups_my  = gs_group_members_groups_get(array($uid), 'user');
			
		} else {
			$action = 'list';
		}
	}
	else {
		$action = 'list';
	}
	
}
if (($action === 'delpenalty') && ($edit_user) && ($uid > 0) && ($queue_id > 0 )) {
	$DB->execute('DELETE from `penalties` WHERE `_user_id`='.$uid.' AND `_queue_id`='.$queue_id);
	$DB->execute('UPDATE `ast_queue_members` SET `penalty`=DEFAULT WHERE `_queue_id`='.$queue_id.' AND `_user_id`='.$uid);
	$action = 'view';
}
if (($action === 'setpenalty') && ($edit_user) && ($uid > 0) && ($queue_id > 0 )) {
	$qhid = (int)$DB->executeGetOne('SELECT `_host_id` FROM `ast_queues` WHERE `_id`='.$queue_id);
	$uhid = (int)$DB->executeGetOne('Select `host_id` FROM `users` WHERE `id`='.$uid);
	if (in_array($penalty, $pen_avail) && ( $qhid == $uhid )){
		$DB->execute('REPLACE INTO `penalties` VALUES ('.$queue_id.','.$uid.','.$qhid.','.$penalty.')');
		$DB->execute('UPDATE `ast_queue_members` SET `penalty`='.$penalty.' WHERE `_queue_id`='.$queue_id.' AND `_user_id`='.$uid);
		$action = 'view';
	}

}
if (($action === 'edit') && ($edit_user) && ($uid > 0)) {
	
	if ($cbdelete) {
		$ret = gs_callblocking_delete( $edit_user, $cbdelete );
		if (isGsError( $ret )) echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
	}
	if ($extnumdel) {
		$ret = gs_user_external_number_del( $edit_user, $extnumdel );
		if (isGsError( $ret )) echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
	}
	if ($bp_del_h > 0) {
		$query = 'DELETE FROM `boi_perms` WHERE `user_id`='.$uid.' AND `host_id`='.$bp_del_h;
		$ok = $DB->execute($query);
	}
}

if (($action === 'save') && ($edit_user) && ($uid > 0))  {
	
	$ret = gs_user_change( $edit_user, $user_pin, $user_fname, $user_lname, @$_REQUEST['ulang'], $user_host, false, $user_email );
	if (isGsError( $ret )) echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
	if (! isGsError( $ret )) {
		$boi_api = gs_host_get_api((int)$user_host);
		if ($boi_api == '') {
			$DB->execute( 'UPDATE `ast_sipfriends` SET `secret`=\''. $DB->escape(preg_replace('/[^0-9a-zA-Z]/', '', @$_REQUEST['usecret'])) .'\' WHERE `_user_id`='. $uid );
		}
	}
	
	if ($cbregexp) {
		$ret = gs_callblocking_set( $edit_user, $cbregexp, $cbpin );
		if (isGsError( $ret )) echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
	}
	if ($extnum) {
		$ret = gs_user_external_number_add( $edit_user, $extnum );
		if (isGsError( $ret )) echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
	}
	
	if ($u_pgrp_ed) {
		$sql_query =
			'DELETE `p` '.
			'FROM `pickupgroups_users` `p` , `users` `u` '.
			'WHERE '.
				'`p`.`user_id` = `u`.`id` AND '.
				'`u`.`user` = \''. $DB->escape($edit_user) .'\''
			;
		$ok = $DB->execute($sql_query);
		
		if (is_array($u_pgrps)) {
			foreach ($u_pgrps as $u_pgrp) {
				if ($u_pgrp < 1) continue;
				$ret = gs_pickupgroup_user_add( $u_pgrp, $edit_user );
				if (isGsError( $ret )) echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
			}
		}
	}
	
	if ($u_prv_grp_ed) {
		$query =
			'UPDATE `users` SET '.
				'`group_id`='. ($u_prv_grp_id > 0 ? $u_prv_grp_id : 'NULL') .' '.
			'WHERE `user`=\''. $DB->escape($edit_user) .'\'';
		$ok = $DB->execute($query);
	}
	
	if ($bp_add_h > 0) {
		$user_id = (int)$DB->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $DB->escape($edit_user) .'\'' );
		if ($user_id > 0) {
			$host_exists = $DB->executeGetOne( 'SELECT 1 FROM `hosts` WHERE `id`='. $bp_add_h );
			if ($host_exists) {
				$query = 'REPLACE INTO `boi_perms` (`user_id`, `host_id` , `roles`) VALUES ('. $user_id .', '. $bp_add_h .', \'l\')';
				$ok = $DB->execute($query);
			}
		}
	}
	
	$action = 'list';
}




if ($action === 'list') {
	
	$sortarray['user'] = 'ASC';
	$sortarray['name'] = 'ASC';
	$sortarray['ext'] = 'ASC';
	$sortarray['email'] = 'ASC';
	$sortarray['host'] = 'ASC';

	if ($sortorder === 'ASC')
		$sortarray[$sort] = 'DESC';
	else
		$sortarray[$sort] = 'ASC';

	switch ($sort) {
	case 'user':
		$order = '`u`.`user` ' . $sortorder;
		break;
	case 'ext':
		$order = '`s`.`name` ' . $sortorder;
		break;
	case 'host':
		$order = '`h`.`comment` ' . $sortorder;
		break;
	default: // name
		$order = '`u`.`lastname` ' . $sortorder . ', `u`.`firstname` ' . $sortorder;
		break;
	}
	
	$use_ldap = false;
	if (! in_array(gs_get_conf('GS_LDAP_HOST'), array(null, false, '', '0.0.0.0'), true)) {
		$use_ldap = true;
		echo '<script type="text/javascript" src="', GS_URL_PATH ,'js/prototype.js"></script>' ,"\n";
	}
	
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
	`u`.`firstname` `fn`, `u`.`lastname` `ln`, `u`.`host_id` `hid`, `u`.`honorific` `hnr`, `u`.`user` `usern`, `s`.`name` `ext`, `u`.`email` `email`, `u`.`pin` `pin`,
	`h`.`is_foreign`, `h`.`comment` `h_comment`,
	`hp1`.`value` `hp_route_prefix`
FROM
	`users` `u` LEFT JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) LEFT JOIN
	`hosts` `h` ON (`h`.`id`=`u`.`host_id`) LEFT JOIN
	`host_params` `hp1` ON (`hp1`.`host_id`=`h`.`id` AND `hp1`.`param`=\'route_prefix\')
WHERE
	`u`.`nobody_index` IS NULL AND (
	`s`.`name` LIKE \''. $DB->escape($number_sql) .'\'
	)
ORDER BY ' . $order . '
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
	`u`.`firstname` `fn`, `u`.`lastname` `ln`, `u`.`host_id` `hid`, `u`.`honorific` `hnr`, `u`.`user` `usern`, `s`.`name` `ext` , `u`.`email` `email`, `u`.`pin` `pin`,
	`h`.`is_foreign`, `h`.`comment` `h_comment`,
	`hp1`.`value` `hp_route_prefix`
FROM
	`users` `u` LEFT JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) LEFT JOIN
	`hosts` `h` ON (`h`.`id`=`u`.`host_id`) LEFT JOIN
	`host_params` `hp1` ON (`hp1`.`host_id`=`h`.`id` AND `hp1`.`param`=\'route_prefix\')
WHERE
	`u`.`nobody_index` IS NULL AND (
	`u`.`lastname` LIKE _utf8\''. $DB->escape($name_sql) .'\' COLLATE utf8_unicode_ci OR
	`u`.`firstname` LIKE _utf8\''. $DB->escape($name_sql) .'\' COLLATE utf8_unicode_ci
	)
ORDER BY ' . $order . '
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
		<th style="width:234px;"><?php echo __('Nebenstelle suchen'); ?></th>
		<th style="width:100px;"><?php echo __('Seite'), ' ', ($page+1), ' / ', $num_pages; ?></th>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td>
			<form method="get" action="<?php echo GS_URL_PATH; ?>">
			<?php echo gs_form_hidden($SECTION, $MODULE); ?>
			<input type="text" name="name" id="ipt-name" value="<?php echo htmlEnt($name); ?>" size="25" style="width:200px;" />
			<script type="text/javascript">/*<![CDATA[*/ try{ document.getElementById('ipt-name').focus(); }catch(e){} /*]]>*/</script>
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
	
<?php
	echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
	echo gs_form_hidden($SECTION, $MODULE), "\n";
	echo '<input type="hidden" name="action" value="add" />', "\n";
	echo '<input type="hidden" name="name" value="', htmlEnt($name), '" />', "\n";
	echo '<input type="hidden" name="number" value="', htmlEnt($number), '" />', "\n";
	echo '<input type="hidden" name="page" value="', (int)$page, '" />', "\n";
	echo '<input type="hidden" name="sort" value="', $sort, '" />', "\n";
	echo '<input type="hidden" name="sortorder" value="', $sortorder, '" />', "\n";
?>
	<table cellspacing="1" class="phonebook">
	<thead>
	<tr>
		<th style="width: 70px;"><?php echo '<a href="', gs_url($SECTION, $MODULE, null, $search_url .'&amp;sort=user&amp;sortorder=' .$sortarray['user']), '">', __('User'), '</a>'; ?></th>
		<th style="width:180px;"><?php echo '<a href="', gs_url($SECTION, $MODULE, null, $search_url .'&amp;sort=name&amp;sortorder=' .$sortarray['name']), '">',__('Nachname') ,', ', __('Vorname'); ?></th>
		<th style="width: 60px;"><?php echo '<a href="', gs_url($SECTION, $MODULE, null, $search_url .'&amp;sort=ext&amp;sortorder=' .$sortarray['ext']), '">',__('Nst.' ); ?></th>
		<th style="width: 55px;"><?php echo __('PIN'      ); ?></th>
		<th style="width:165px;"><?php echo '<a href="', gs_url($SECTION, $MODULE, null, $search_url .'&amp;sort=email&amp;sortorder=' .$sortarray['email']), '">',__('E-Mail'   ); ?></th>
		<th style="width: 42px;"><?php echo '<a href="', gs_url($SECTION, $MODULE, null, $search_url .'&amp;sort=host&amp;sortorder=' .$sortarray['host']), '">',__('Host'     ); ?></th>
		<th style="width: 85px;"><?php echo __('Status'   ); ?></th>
		<th style="width: 55px;">&nbsp;</th>
	</tr>
	</thead>
	<tbody>
	
	<?php
	
	$sudo_url = (@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
		? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);
	
	@ob_flush(); @flush();
	if (@$rs) {
		$i = 0;
		while ($r = $rs->fetchRow()) {
			
			echo '<tr class="', ((++$i % 2) ? 'odd':'even'), '">', "\n";
			
			echo '<td>', htmlEnt($r['usern']), '</td>' ,"\n";
			echo '<td>', htmlEnt($r['ln']);
			if ($r['fn'] !='') echo ', ', htmlEnt($r['fn']);
			echo '</td>' ,"\n";
			//echo '<td>', htmlEnt($r['fn']) ,'</td>' ,"\n";
			//echo '<td>', htmlEnt($r['hnr']) ,'</td>' ,"\n";
			echo '<td>';
			if ($r['hp_route_prefix'] != ''
			&&  subStr($r['ext'],0,strLen($r['hp_route_prefix'])) === $r['hp_route_prefix'])
			{
				echo '<span style="color:#888;">', subStr($r['ext'],0,strLen($r['hp_route_prefix'])) ,'</span>';
				echo ($r['ext'] !== null)
					? subStr($r['ext'],strLen($r['hp_route_prefix']))
					: '&ndash;?&ndash;';
			} else {
				echo ($r['ext'] !== null)
					? $r['ext']
					: '&ndash;?&ndash;';
			}
			echo '</td>' ,"\n";
			echo '<td>', str_repeat('&bull;', strLen($r['pin'])) ,'</td>' ,"\n";
			$email_display = $r['email'];
			if (mb_strLen($email_display) < 20) {
				$email_display = htmlEnt($email_display);
			} else {
				$email_display = htmlEnt(mb_substr($email_display, 0, 18)) .'&#8230;';
			}
			echo '<td>', $email_display ,'</td>' ,"\n";
			echo '<td>', ($r['h_comment'] !== null ? htmlEnt($r['h_comment']) : '&ndash;?&ndash;') ,'</td>' ,"\n";
			
			echo '<td class="nobr">';
			if (! $r['is_foreign']) {
				$state = gs_extstate_single( $r['ext'] );
				switch ($state) {
				case AST_MGR_EXT_UNKNOWN:
					echo '<img alt=" " src="', GS_URL_PATH, 'crystal-svg/16/app/important.png" />&nbsp;', __('?');
					break;
				case AST_MGR_EXT_IDLE:
					echo '<img alt=" " src="', GS_URL_PATH, 'crystal-svg/16/act/greenled.png" />&nbsp;', __('frei');
					break;
				case AST_MGR_EXT_OFFLINE:
					echo '<img alt=" " src="', GS_URL_PATH, 'crystal-svg/16/act/free_icon.png" />&nbsp;', __('offline');
					break;
				case AST_MGR_EXT_INUSE:
				case AST_MGR_EXT_BUSY:
					echo '<img alt=" " src="', GS_URL_PATH, 'crystal-svg/16/act/redled.png" />&nbsp;', __('spricht');
					break;
				case AST_MGR_EXT_RINGING:
					echo '<img alt=" " src="', GS_URL_PATH, 'crystal-svg/16/app/knotify.png" />&nbsp;', __('klingelt');
					break;
				case AST_MGR_EXT_RINGINUSE:
					echo '<img alt=" " src="', GS_URL_PATH, 'crystal-svg/16/app/knotify.png" />&nbsp;', __('Anklopfen');
					break;
				case AST_MGR_EXT_ONHOLD:
					echo '<img alt=" " src="', GS_URL_PATH, 'crystal-svg/16/act/redled.png" />&nbsp;', __('Halten');
					break;
				default:
					echo $state;
				}
			} else {
				echo '<i>(', __('fremd') ,')</i>';
			}
			echo '</td>';
			
			echo '<td class="nobr">';
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'edit='. rawUrlEncode($r['usern']) .'&amp;action=view&amp;name='. rawUrlEncode($name) .'&amp;number='. rawUrlEncode($number) .'&amp;page='.$page .'&amp;sort='.$sort .'&amp;sortorder='.$sortorder), '" title="',__('bearbeiten'), '"><img alt="',__('bearbeiten'), '" src="',GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='. rawUrlEncode($r['usern']) .'&amp;action=del&amp;name='. rawUrlEncode($name) .'&amp;number='. rawUrlEncode($number) .'&amp;page='.$page .'&amp;sort='.$sort .'&amp;sortorder='.$sortorder), '" title="',__('l&ouml;schen'), '" onclick="return confirm_delete();"><img alt="',__('entfernen'), '" src="',GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
			echo "</td>\n";
			
			echo '</tr>', "\n";
			@ob_flush(); @flush();
		}
	}
	
	?>
	<tr>
	<?php
	
	//if (! $edit_user) {
		
	?>
		<td>
			<input type="text" name="uuser" id="ipt-uuser" value="" size="8" maxlength="20" />
		</td>
		<td>
			<input type="text" name="ulname" id="ipt-ulname" value="" size="15" maxlength="40" style="width:80px;" title="<?php echo __('Nachname'); ?>" />,
			<input type="text" name="ufname" id="ipt-ufname" value="" size="15" maxlength="40" style="width:70px;" title="<?php echo __('Vorname'); ?>" />
		</td>
		<td>
			<input type="text" name="uext" id="ipt-uext" value="" size="5" maxlength="11" />
		</td>
		<td>
			<input type="password" name="upin" id="ipt-upin" value="<?php echo mt_rand(100000,999999); ?>" size="5" maxlength="10" />
		</td>
		<td>
			<input type="text" name="uemail" id="ipt-uemail" value="" size="20" maxlength="50" />
		</td>
		<td class="r">
<?php
			echo '<select name="uhost" id="ipt-uhost" style="min-width:42px;">',"\n";
			
			echo '<optgroup label="Gemeinschaft">',"\n";
			$rs_hosts = $DB->execute('SELECT `id`, `host`, `comment` FROM `hosts` WHERE `is_foreign`=0 ORDER BY `comment`');
			while ($h = $rs_hosts->fetchRow()) {
				echo '<option value="',$h['id'] ,'"';
				if ($h['id'] == $r['hid']) echo ' selected="selected"';
				$comment = mb_subStr($h['comment'], 0, 20+1);
				if (mb_strLen($comment) > 20)
					$comment = mb_subStr($h['comment'], 0, 20-1) ."\xE2\x80\xA6";
				elseif (trim($comment) === '') $comment = '#'.$h['id'];
				echo ' title="', htmlEnt($h['host']) ,'"';
				echo '>', htmlEnt($comment) ,'</option>',"\n";
			}
			echo '</optgroup>',"\n";
			unset($rs_hosts);
			
			$rs_hosts = $DB->execute('SELECT `id`, `host`, `comment` FROM `hosts` WHERE `is_foreign`=1 ORDER BY `comment`');
			if ($rs_hosts->numRows() != 0) {
				//echo '<option value="" disabled="disabled">--</option>',"\n";
				echo '<optgroup label="', __('Fremd-Hosts') ,'">',"\n";
				while ($h = $rs_hosts->fetchRow()) {
					echo '<option value="',$h['id'] ,'"';
					if ($h['id'] == $r['hid']) echo ' selected="selected"';
					$comment = mb_subStr($h['comment'], 0, 20+1);
					if (mb_strLen($comment) > 20)
						$comment = mb_subStr($h['comment'], 0, 20-1) ."\xE2\x80\xA6";
					elseif (trim($comment) === '') $comment = '#'.$h['id'];
					echo ' title="', htmlEnt($h['host']) ,'"';
					echo '>', htmlEnt($comment) ,'</option>',"\n";
				}
				echo '</optgroup>',"\n";
			}
			unset($rs_hosts);
			
			echo '</select>',"\n";
?>
		</td>
		<td class="nobr">
			&nbsp;
		</td>
		<td class="nobr">
			<button type="submit" title="<?php echo __('Benutzer anlegen'); ?>" class="plain" name="action" value="add">
				<img alt="<?php echo __('Speichern'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
			</button>
			<button type="submit" title="<?php echo __('Benutzer anlegen und bearbeiten'); ?>" class="plain" name="action" value="add-and-view">
				<img alt="<?php echo __('Speichern'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
			</button>
		</td>
		
	<?php
	//}
	?>
	
	</tr>
	
	</tbody>
	</table>
	</form>
	
<?php
	if ($use_ldap) {
?>
<script type="text/javascript">
//<![CDATA[
try {
new Form.Element.EventObserver('ipt-uuser', function() {
if ($('ipt-uuser').present()) {
new Ajax.Request(
	'<?php echo GS_URL_PATH; ?>srv/ldap-user-info.php', {
	//parameters: 'u='+ encodeURIComponent( $F('ipt-uuser') ),
	parameters: $H({ 'u':$F('ipt-uuser') }),
	asynchronous: false,
	method: 'get',
	evalJSON: true,
	onCreate: function() {
		['ipt-ulname', 'ipt-ufname', 'ipt-uext', 'ipt-upin', 'ipt-uemail', 'ipt-uhost'].each( function(v){
			$(v).disable();
			$(v).style.opacity = '0.7';
		});
	},
	onSuccess: function( xhr ) {
		var r = (xhr.responseText||'').evalJSON();
		if (! r) return;
		$('ipt-ulname').value = (r.ln    || '');
		$('ipt-ufname').value = (r.fn    || '');
		$('ipt-uext'  ).value = (r.exten || '');
		$('ipt-uemail').value = (r.email || '');
	},
	onComplete: function() {
		var f = null;
		['ipt-ulname', 'ipt-ufname', 'ipt-uext', 'ipt-upin', 'ipt-uemail', 'ipt-uhost'].each( function(v){
			$(v).enable();
			$(v).style.opacity = '1';
			if (! f && ! $(v).present()) f = v;
		});
		$('ipt-ulname').focus();
		if (f) $(f).focus();
	}
})}}
);
} catch(e){}
//]]>
</script>
<?php
	}
?>
	
	<br />
	
	<table cellspacing="1" class="phonebook">
	<thead>
	<tr>
		<th colspan="2"><span><?php echo __('Benutzer'); ?></span></th>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td><?php echo __('Eingerichtete Benutzer'); ?>:</td>
		<td class="r" style="min-width:4em;"><?php echo count_users_configured($DB); ?></td>
	</tr>
	<tr>
		<td><?php echo __('Eingeloggte Benutzer'); ?>:</td>
		<td class="r"><?php echo count_users_logged_in($DB); ?></td>
	</tr>
	</tbody>
	</table>
	
	<br />
	<?php if (gs_get_conf('GS_BOI_ENABLED')) { ?>
	<p>
		<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/info.png" />
		<small><?php echo __('Bei Benutzern in Filialen mu&szlig; die Nebenstelle inklusive der Route aus Sicht der Zentrale angegeben werden (z.B. 60123410).'); ?></small>
	</p>
	<?php } ?>
	
<?php
}
else {
	
	$rs = $DB->execute(
'SELECT
	`u`.`firstname` `fn`, `u`.`lastname` `ln`, `u`.`host_id` `hid`, `u`.`honorific` `hnr`, `u`.`user` `usern`, `s`.`name` `ext` , `u`.`email` `email`, `u`.`pin` `pin`, `u`.`id` `uid`, `s`.`secret`, `s`.`language`, `u`.`group_id`,
	`hp1`.`value` `hp_route_prefix`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) LEFT JOIN
	`hosts` `h` ON (`h`.`id`=`u`.`host_id`) LEFT JOIN
	`host_params` `hp1` ON (`hp1`.`host_id`=`h`.`id` AND `hp1`.`param`=\'route_prefix\')
WHERE
	`u`.`user` = \''. $DB->escape($edit_user) .'\''
	);
	
	if ($rs) {
		$r = $rs->fetchRow();
		$hid = $r['hid'];
	} else {
		$hid = 0;
	}
	
	$boi_api = ($hid > 0) ? gs_host_get_api($hid) : '__fail_api';
	
	
	$sql_query =
'SELECT `p`.`id`, `p`.`title`, `u`.`host_id`
FROM
	`pickupgroups` `p` LEFT JOIN
	`pickupgroups_users` `pu` ON (`p`.`id`=`pu`.`group_id`) LEFT JOIN
	`users` `u` ON (`u`.`id`=`pu`.`user_id`)
WHERE
	`u`.`host_id` = '.$r['hid'].' OR
	`u`.`host_id` IS NULL
GROUP BY `p`.`id`';
	
	$rs = $DB->execute($sql_query);
	$pgroups = array();
	if (@$rs) {
		while ($r_pg = $rs->fetchRow()) {
			$pgroups[$r_pg['id']] = $r_pg['title'];
		}
	}
	
	$sql_query =
'SELECT `p`.`group_id`
FROM
	`pickupgroups_users` `p` JOIN
	`users` `u` ON (`p`.`user_id`=`u`.`id`)
WHERE `u`.`user` = \''.$DB->escape($edit_user).'\'';
	
	$rs = $DB->execute($sql_query);
	$pgroups_my = array();
	if (@$rs) {
		while ($r_pu = $rs->fetchRow()) {
			$pgroups_my[$r_pu['group_id']] = $r_pu['group_id'];
		}
	}
	
	$sql_query =
'SELECT `id`, `regexp`, `pin`
FROM `callblocking`
WHERE `user_id`='. (int)$r['uid'] .'
ORDER BY LENGTH(`regexp`) DESC';
	
	$rs = $DB->execute($sql_query);
	$callblocking = array();
	if (@$rs) {
		while ($r_cb = $rs->fetchRow()) {
			$callblocking[$r_cb['id']] = $r_cb;
		}
	}
	
	$sql_query =
'SELECT `number`
FROM `users_external_numbers`
WHERE `user_id`='. (int)$r['uid'] .'
ORDER BY LENGTH(`number`) DESC';
	
	$rs = $DB->execute($sql_query);
	$ext_nums = array();
	if (@$rs) {
		while ($r_en = $rs->fetchRow()) {
			$ext_nums[] = $r_en['number'];
		}
	}
	
	if (gs_get_conf('GS_BOI_ENABLED')) {
	$sql_query =
		'SELECT '.
			'`p`.`host_id`, `p`.`roles`, '.
			'`h`.`comment`, `h`.`host` '.
		'FROM '.
			'`boi_perms` `p` JOIN '.
			'`hosts` `h` ON (`h`.`id`=`p`.`host_id`) '.
		'WHERE `p`.`user_id`='. (int)$r['uid'] .' '.
		'ORDER BY `h`.`comment`';
	
	$rs = $DB->execute($sql_query);
	$boi_perms = array();
	if (@$rs) {
		while ($r_bp = $rs->fetchRow()) {
			$boi_perms[] = $r_bp;
		}
	}
	}
	
?>



<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php
echo gs_form_hidden($SECTION, $MODULE), "\n";
echo '<input type="hidden" name="edit" value="', htmlEnt($edit_user), '" />', "\n";
echo '<input type="hidden" name="action" value="save" />', "\n";
echo '<input type="hidden" name="name" value="', htmlEnt($name), '" />', "\n";
echo '<input type="hidden" name="number" value="', htmlEnt($number), '" />', "\n";
echo '<input type="hidden" name="page" value="', (int)$page, '" />', "\n";
echo '<input type="hidden" name="sort" value="', $sort, '" />', "\n";
echo '<input type="hidden" name="sortorder" value="', $sortorder, '" />', "\n";
?>

<button type="submit" title="<?php echo __('Speichern'); ?>" class="plain" style="margin:3px 1px 3px 450px;">
	<img alt="<?php echo __('Speichern'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
</button>

<table cellspacing="1">
<thead>
	<tr>
		<th colspan="2">
			Allgemeine Einstellungen
		</th>
	</tr>
	<tr>
		<th style="width:180px;">
			<?php echo __('Benutzer'); ?>
		</th>
		<td style="width:280px;">
			<?php echo htmlEnt($edit_user); ?>
		</td>
		<td class="transp xs gray">
			&nbsp;
		</td>
	</tr>
</thead>
<tbody>
	<tr>
		<th><?php echo __('Nebenstelle'); ?>:</th>
		<td>
			<?php
				if ($r['hp_route_prefix'] != ''
				&&  subStr($r['ext'],0,strLen($r['hp_route_prefix'])) === $r['hp_route_prefix'])
				{
					echo '<span style="color:#888;">', subStr($r['ext'],0,strLen($r['hp_route_prefix'])) ,'</span>';
					echo subStr($r['ext'],strLen($r['hp_route_prefix']));
				} else {
					echo $r['ext'];
				}
			?>
		</td>
		<td class="transp xs gray">
			&larr; <?php echo htmlEnt(__("der SIP-Benutzername")); ?>
		</td>
	</tr>
	<tr>
		<th><?php echo __('Nachname'); ?>:</th>
		<td>
			<input type="text" name="ulname" value="<?php echo htmlEnt($r['ln']); ?>" size="30" maxlength="50" />
		</td>
		<td class="transp xs gray">
			&nbsp;
		</td>
	</tr>
	<tr>
		<th><?php echo __('Vorname'); ?>:</th>
		<td>
			<input type="text" name="ufname" value="<?php echo htmlEnt($r['fn']); ?>" size="30" maxlength="50" />
		</td>
		<td class="transp xs gray">
			&nbsp;
		</td>
	</tr>
	<tr>
		<th><?php echo __('PIN'); ?>:</th>
		<td>
			<input type="text" name="upin" value="<?php echo htmlEnt($r['pin']); ?>" size="8" maxlength="10" />
		</td>
		<td class="transp xs gray">
			&nbsp;
		</td>
	</tr>
	<tr>
		<th><?php echo __('SIP-Pa&szlig;wort'); ?>:</th>
		<td>
			<?php
				if ($boi_api == '') {
					echo '<input type="text" name="usecret" value="', htmlEnt($r['secret']) ,'" size="16" maxlength="16" />' ,"\n";
				} else {
					echo htmlEnt($r['secret']);
				}
			?>
		</td>
		<td class="transp xs gray">
			&larr; <?php echo htmlEnt(__("das SIP-Passwort")); ?>
		</td>
	</tr>
	<tr>
		<th><?php echo __('E-Mail'); ?>:</th>
		<td>
			<input type="text" name="uemail" value="<?php echo htmlEnt($r['email']); ?>" size="38" maxlength="60" style="width:97%;" />
		</td>
		<td class="transp xs gray">
			&nbsp;
		</td>
	</tr>
	<tr>
		<th><?php echo __('Sprache'); ?>:</th>
		<td>
			<select name="ulang">
				<option value="de"<?php echo (($r['language'] === 'de') ? ' selected' : ''); ?>><?php echo htmlEnt(__("Deutsch"  )); ?> (de-DE)</option>
				<option value="en"<?php echo (($r['language'] === 'en') ? ' selected' : ''); ?>><?php echo htmlEnt(__("Englisch" )); ?> (en-US)</option>
			</select>
		</td>
		<td class="transp xs gray">
			&nbsp;
		</td>
	</tr>
	<tr>
		<th><?php echo __('Host'); ?>:</th>
		<td>
<?php
		echo '<select name="uhost">',"\n";
		
		echo '<optgroup label="Gemeinschaft">',"\n";
		$rs_hosts = $DB->execute('SELECT `id`, `host`, `comment` FROM `hosts` WHERE `is_foreign`=0 ORDER BY `comment`');
		while ($h = $rs_hosts->fetchRow()) {
			echo '<option value="',$h['id'] ,'"';
			if ($h['id'] == $r['hid']) echo ' selected="selected"';
			$comment = mb_subStr($h['comment'], 0, 25+1);
			if (mb_strLen($comment) > 25)
				$comment = mb_subStr($h['comment'], 0, 25-1) ."\xE2\x80\xA6";
			elseif (trim($comment) === '') $comment = '#'.$h['id'];
			echo '>', htmlEnt($comment) ,' (', htmlEnt($h['host']) ,')</option>',"\n";
		}
		echo '</optgroup>',"\n";
		unset($rs_hosts);
		
		$rs_hosts = $DB->execute('SELECT `id`, `host`, `comment` FROM `hosts` WHERE `is_foreign`=1 ORDER BY `comment`');
		if ($rs_hosts->numRows() != 0) {
			echo '<optgroup label="', __('Fremd-Hosts') ,'">',"\n";
			while ($h = $rs_hosts->fetchRow()) {
				echo '<option value="',$h['id'] ,'"';
				if ($h['id'] == $r['hid']) echo ' selected="selected"';
				$comment = mb_subStr($h['comment'], 0, 25+1);
				if (mb_strLen($comment) > 25)
					$comment = mb_subStr($h['comment'], 0, 25-1) ."\xE2\x80\xA6";
				elseif (trim($comment) === '') $comment = '#'.$h['id'];
				echo '>', htmlEnt($comment) ,' (', htmlEnt($h['host']) ,')</option>',"\n";
			}
			echo '</optgroup>',"\n";
		}
		unset($rs_hosts);
		
		echo '</select>',"\n";
?>
		</td>
		<td class="transp xs gray">
			&larr; <?php echo htmlEnt(__("der SIP-Registrar/-Server")); ?>
		</td>
	</tr>
</tbody>
</table>

<br />

<?php
echo '<input type="hidden" name="u_prv_grp_ed" value="yes" />', "\n";
?>
<table cellspacing="1">
<tbody>
	<tr>
		<th style="width:180px;">
			<?php echo __('Provisioning-Gruppe'); ?>
		</th>
		<td style="width:280px;">
<?php
		$mptt = new YADB_MPTT($DB, 'user_groups', 'lft', 'rgt', 'id');
		$u_prv_groups = $mptt->get_tree_as_list( null );
		echo '<select name="u_prv_grp_id">',"\n";
		echo '<option value=""';
		if ($r['group_id'] == '')
			echo ' selected="selected"';
		echo '>- ', __('keine') ,' -</option>',"\n";
		if (is_array($u_prv_groups)) {
			$is_root_node = true;
			$root_level = 0;
			foreach ($u_prv_groups as $u_prv_group) {
				if ($is_root_node) {  # skip root node
					$root_level = $u_prv_group['__mptt_level'];
					$is_root_node = false;
					continue;
				}
				echo '<option value="', $u_prv_group['id'] ,'"';
				if ($r['group_id'] == $u_prv_group['id'])
					echo ' selected="selected"';
				echo '>';
				echo @str_repeat('&nbsp;&nbsp;&nbsp;', $u_prv_group['__mptt_level']-$root_level-1);
				if ($u_prv_group['title'])
					echo htmlEnt($u_prv_group['title']);
				else
					echo htmlEnt($u_prv_group['name']);
				echo '</option>' ,"\n";
			}
		}
		echo '</select>',"\n";
?>
		</td>
	</tr>
</tbody>
</table>

<br />

<?php
/*echo '<input type="hidden" name="u_pgrp_ed" value="yes" />', "\n";*/
?>
<!-- <table cellspacing="1">
<tbody>
	<tr>
		<th style="width:180px;" class="l t">
			<?php/** echo __('Rufannahme-Gruppe'); **/?>
		</th>
		<td style="width:280px;"> -->
<?php
/**		echo '<select multiple="multiple" name="u_pgrps[]" size="4">',"\n";
		foreach ($pgroups as $key => $pgroup) {
			echo '<option value="',$key,'"';
			if (@$pgroups_my[$key]) echo ' selected="selected"';
			echo '>', $key ,' (', htmlEnt($pgroup) ,')</option>',"\n";
		}
		echo '<option value=""></option>',"\n";
		echo '</select>',"\n";**/
?>
<!--
		</td>
	</tr>
</tbody>
</table>

<br />
-->
<table cellspacing="1">
<thead>
	<tr>
		<th style="width:180px;">
			<?php echo __('Anrufsperre'); ?>
		</th>
		<th style="width:217px;">
			<?php echo __('Entsperr-PIN'); ?>
		</th>
		<th style="width:50px;">
			&nbsp;
		</th>
	</tr>
</thead>
<tbody>
<?php
	$i=0;
	foreach ($callblocking as $key => $cb_entry) {
		echo '<tr class="',($i%2===0 ?'odd':'even'),'">' ,"\n";
		
		echo "<td>\n";
		echo htmlEnt($cb_entry['regexp']);
		echo "</td>\n";
		
		echo "<td>\n";
		echo htmlEnt($cb_entry['pin']);
		echo "</td>\n";
		
		echo "<td>\n";
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'cbdelete='. rawUrlEncode($cb_entry['regexp']) .'&amp;edit='. rawUrlEncode($edit_user) .'&amp;action=edit' .'&amp;name='. rawUrlEncode($name) .'&amp;number='. rawUrlEncode($number) .'&amp;page='.$page .'&amp;sort='.$sort .'&amp;sortorder='.$sortorder), '" title="', __('entfernen'), '">';
		echo '<img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
		echo "</td>\n";
		
		echo "</tr>\n";
		++$i;
	}
	
	echo '<tr class="',($i%2===0 ?'odd':'even'),'">' ,"\n";
	
	echo "<td>\n";
	echo '<input type="text" name="cbregexp" value="" size="20" maxlength="40" />';
	echo "</td>\n";
	
	echo "<td>\n";
	echo '<input type="text" name="cbpin" value="" size="20" maxlength="40" />';
	echo "</td>\n";
	
	echo "<td>\n";
	/*
	echo '<button type="submit" title="', __('Speichern') ,'" class="plain">', "\n";
	echo '<img alt="', __('Speichern') ,'" src="', GS_URL_PATH ,'crystal-svg/16/act/filesave.png" />', "\n";
	echo "</button>\n";
	*/
	echo "</td>\n";
	
	echo "</tr>\n";
?>
</tbody>
</table>

<br />

<table cellspacing="1">
<thead>
	<tr>
		<th style="width:180px;">
			<?php echo __('Externe Rufnummern'); ?>
		</th>
		<th style="width:217px;">
			<?php echo __('Rufnummer'); ?>
		</th>
		<th style="width:50px;">
			&nbsp;
		</th>
	</tr>
</thead>
<tbody>
<?php
	$i=0;
	foreach ($ext_nums as $ext_num) {
		echo '<tr class="',($i%2===0 ?'odd':'even'),'">' ,"\n";
		
		echo "<td>&nbsp;</td>\n";
		
		echo "<td>\n";
		echo htmlEnt($ext_num);
		echo "</td>\n";
		
		echo "<td>\n";
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'extndel='.$ext_num .'&amp;edit='. rawUrlEncode($edit_user) .'&amp;action=edit' .'&amp;name='. rawUrlEncode($name) .'&amp;number='. rawUrlEncode($number) .'&amp;page='.$page .'&amp;sort='.$sort .'&amp;sortorder='.$sortorder), '" title="', __('entfernen'), '"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
		echo "</td>\n";
		
		echo "</tr>\n";
		++$i;
	}
	
	echo '<tr class="',($i%2===0 ?'odd':'even'),'">' ,"\n";
	
	echo "<td>&nbsp;</td>\n";
	
	echo "<td>\n";
	echo '<input type="text" name="extnum" value="" size="20" maxlength="40" />';
	echo "</td>\n";
	
	echo "<td>\n";
	/*
	echo '<button type="submit" title="', __('Speichern') ,'" class="plain">', "\n";
	echo '<img alt="', __('Speichern') ,'" src="', GS_URL_PATH ,'crystal-svg/16/act/filesave.png" />', "\n";
	echo "</button>\n";
	*/
	echo "</td>\n";
	
	echo "</tr>\n";
?>
</tbody>
</table>

<?php
if (gs_get_conf('GS_BOI_ENABLED')) {
?>
<br />

<table cellspacing="1">
<thead>
	<tr>
		<th style="width:180px;">
			<?php echo __('Lokale Admin-Rechte'); ?>
		</th>
		<th style="width:217px;" colspan="2">
			<?php echo __('Anlage'); ?>
		</th>
		<th style="width:50px;">
			&nbsp;
		</th>
	</tr>
</thead>
<tbody>
<?php
	$i=0;
	foreach ($boi_perms as $p) {
		if (strPos($p['roles'],'l') === false) continue;
		echo '<tr class="',($i%2===0 ?'odd':'even'),'">' ,"\n";
		echo "<td>&nbsp;</td>\n";
		
		echo '<td>', htmlEnt($p['comment']) ,'</td>' ,"\n";
		
		echo '<td>', htmlEnt($p['host']) ,'</td>' ,"\n";
		
		echo "<td>\n";
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'bp_del_h='.$p['host_id'] .'&amp;edit='. rawUrlEncode($edit_user) .'&amp;action=edit' .'&amp;name='. rawUrlEncode($name) .'&amp;number='. rawUrlEncode($number) .'&amp;page='.$page .'&amp;sort='.$sort .'&amp;sortorder='.$sortorder), '" title="', __('entfernen'), '"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
		echo "</td>\n";
		
		echo "</tr>\n";
		++$i;
	}
	
	echo '<tr class="',($i%2===0 ?'odd':'even'),'">' ,"\n";
	echo "<td>&nbsp;</td>\n";
	
	echo '<td colspan="2">' ,"\n";
	echo '<select name="bp_add_h">' ,"\n";
	echo '<option value="">', __('hinzuf&uuml;gen ...') ,'</option>',"\n";
	$rs_hosts = $DB->execute(
		'SELECT `id`, `host`, `comment` '.
		'FROM `hosts` '.
		'WHERE `is_foreign`=1 AND `id` NOT IN ( '.
			'SELECT `host_id` '.
			'FROM `boi_perms` '.
			'WHERE `user_id`='.(int)$r['uid'].' AND `roles`<>\'\' '.
		') '.
		'ORDER BY `comment`'
		);
	echo '<optgroup label="', __('Fremd-Hosts') ,'">',"\n";
	while ($h = $rs_hosts->fetchRow()) {
		echo '<option value="',$h['id'],'">';
		$comment = mb_subStr($h['comment'], 0, 25+1);
		if (mb_strLen($comment) > 25)
			$comment = mb_subStr($h['comment'], 0, 25-1) ."\xE2\x80\xA6";
		elseif (trim($comment) === '') $comment = '#'.$h['id'];
		echo htmlEnt($comment) ,' -- ', htmlEnt($h['host']);
		if ($h['id'] == $r['hid']) echo ' (&bull;)';
		echo '</option>',"\n";
	}
	echo '</optgroup>',"\n";
	echo '</select>' ,"\n";
	echo '</td>' ,"\n";
	
	echo "<td>\n";
	//echo '[save]';
	echo "</td>\n";
	
	echo "</tr>\n";
?>
</tbody>
</table>
<?php
}
?>

<button type="submit" title="<?php echo __('Speichern'); ?>" class="plain" style="margin:3px 1px 3px 450px;">
	<img alt="<?php echo __('Speichern'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
</button>

</form>

<?php
//FIXME - invalid XHTML! {
?>
<br />

<table cellspacing="1">
<thead>
<tr>
	<th colspan="5"><?php echo __('Benutzergruppen '); ?></th>
</tr>

<tr>
	<th style="width:130px;"><?php echo __('Gruppe'); ?></th>
	<th style="width:180px;"><?php echo __('Titel'); ?></th>
	<th style="width:30px;"><?php echo __('Typ'); ?></th>
	<th style="width:50px;"><?php echo __('Mitglieder'); ?></th>
	<th style="width:24px;"></th>
</tr>
</thead>
<tbody>
<?php
	
	$groups_my_info     = gs_group_info_get($groups_my, 'user');
	
	$i = 0;
	
	if ( (count($groups_my_info) - count($groups) - 1) ) {
		echo '<tr class="',($i%2===0?'odd':'even'),'">' ,"\n";
		echo '<form method="post" action="'.GS_URL_PATH.'">';
		echo gs_form_hidden($SECTION, $MODULE);
		echo '<input type="hidden" name="action" value="insert-group" />' ,"\n";
		echo '<input type="hidden" name="page" value="'.$page.'" />' ,"\n";
		echo '<input type="hidden" name="sort" value="', $sort, '" />', "\n";
		echo '<input type="hidden" name="sortorder" value="', $sortorder, '" />', "\n";
		echo '<input type="hidden" name="number" value="'.rawUrlEncode($number).'" />' ,"\n";
		echo '<input type="hidden" name="name" value="'.rawUrlEncode($name).'" />' ,"\n";
		echo '<input type="hidden" name="edit" value="'.rawUrlEncode($edit_user).'" />' ,"\n";
		echo '<td class="l nobr" colspan="2">';
		echo '<select name="group">', "\n";
		foreach ($groups as $group) {
			if (!in_array($group['id'], $groups_my) && !gs_group_connections_get($group['id']))
				echo '<option value="',$group['id'] ,'">',$group['name'], ' -- ',$group['title'] ,'</option>' ,"\n";
		}
		echo '</select>', "\n";
		echo '</td>', "\n";
		echo '<td>',$group['type']  ,'</td>', "\n";
		echo '<td class="r" colspan="2">', "\n";
		echo  '<button type="submit" name="id" value="'.$group['id'].'" title="', __('Gruppe Einf&uuml;gen') ,'" class="plain"><img alt="', __('Einf&uuml;gen') ,'" src="', GS_URL_PATH,'img/plus.gif" /></button>';
		echo '</td>', "\n";
		echo '</tr>' ,"\n";
		echo '</form>',"\n";
	}
	
	foreach ($groups_my_info as $group) {
		echo '<tr class="',($i%2===0?'odd':'even'),'">' ,"\n";
		echo '<td class="l nobr">';
		echo $group['name']  ,'</td>', "\n";
		echo '<td>',$group['title']  ,'</td>', "\n";	
		echo '<td>',$group['type']  ,'</td>', "\n";
		echo '<td class="r">',count(gs_group_members_get(Array($group['id']))), '</td>', "\n";
		echo '<td class="r">', "\n";
		
		if (!gs_group_connections_get($group['id']))
			echo '<a href="', gs_url($SECTION, $MODULE, null,'&amp;edit='. rawUrlEncode($edit_user) .'&amp;name='. rawUrlEncode($name) .'&amp;number='. rawUrlEncode($number) .'&amp;page='.$page  .'&amp;sort='.$sort .'&amp;sortorder='.$sortorder . '&amp;action=remove-group&amp;group='.$group['id']) ,'"><img alt="', __('Entfernen') ,'" title="', __('Entfernen') ,'" src="', GS_URL_PATH ,'img/minus.gif" /></a>';
		echo '</td>', "\n";
		echo '</tr>' ,"\n";
		$i++;
	}
	
?>

</tbody>
</table>

<?php
//FIXME - invalid XHTML! }
?>
<br />

<?php

	echo '<table><thead><tr>';
	echo '<th colspan="3">', __('Vorhandene Skills'), '</th></tr>';
	echo '<tr>';
	echo '<th style="width:348px;">', __('Warteschlange'), '</th>';
	echo '<th style="width:58px;">', __('Skill'), '</th>';
	echo '<th style="width:38px;"></th>';
	echo '</thead><tbody>';

	echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
	echo gs_form_hidden($SECTION, $MODULE);
	echo '<input type="hidden" name="name" value="', htmlEnt($name), '" />', "\n";
	echo '<input type="hidden" name="number" value="', htmlEnt($number), '" />', "\n";
	echo '<input type="hidden" name="page" value="', (int)$page, '" />', "\n";
	echo '<input type="hidden" name="sort" value="', $sort, '" />', "\n";
	echo '<input type="hidden" name="sortorder" value="', $sortorder, '" />', "\n";

	$uhid = $DB->executeGetOne('SELECT `host_id` FROM `users` WHERE `id`='.$uid);
	$rs = $DB->execute('SELECT `_id`, `name`, `_title` FROM `ast_queues` WHERE `_host_id`='.$uhid.' AND `_id` NOT IN (SELECT `_queue_id` FROM `penalties` WHERE `_user_id`='.$uid.')');

	if ($DB->numFoundRows() > 0) {
		echo '<tr><td>';
		echo '<select name="queue_id">';
		while ($queue_map = $rs->fetchRow()) {
			echo '<option value="', (int)$queue_map['_id'], '"', 'title="', htmlEnt( $queue_map['_title']),'"';
			echo '>',  $queue_map['name'], ' ', $queue_map['_title'], '</option>', "\n";
		}
		echo '</select>';
		echo '</td>';
		echo '<td>';
		echo '<select name="penalty">';
		foreach ($pen_avail as $pen) {
			echo '<option value="', $pen,  '">', $pen, ' </option>';
		}
		echo '</select>';
		echo '</td>';
		echo '<td>';
		echo '<button type="submit" title="', __('Speichern'), '" class="plain">';
		echo '<img alt="', __('Hinzuf&uuml;gen') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/filesave.png" /></button>' ,"\n";
		echo '<input type="hidden" name="action" value="setpenalty" />', "\n";
		echo '<input type="hidden" name="edit" value="', rawUrlEncode($edit_user), '">', "\n";
		echo '</td>';
		echo '</tr>';
	}
	echo '</form>';

	echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
	echo gs_form_hidden($SECTION, $MODULE);
	echo '<input type="hidden" name="name" value="', htmlEnt($name), '" />', "\n";
	echo '<input type="hidden" name="number" value="', htmlEnt($number), '" />', "\n";
	echo '<input type="hidden" name="page" value="', (int)$page, '" />', "\n";
	echo '<input type="hidden" name="sort" value="', $sort, '" />', "\n";
	echo '<input type="hidden" name="sortorder" value="', $sortorder, '" />', "\n";

	$rs = $DB->execute('SELECT `q`.`name`, `penalty`, `_title`, `u`.`host_id`, `p`.`_queue_id` from `users` u,  `penalties` p, `ast_queues` q WHERE `p`.`_user_id`='.$uid.' AND `p`.`_user_id`=`u`.`id` AND `q`.`_id`=`p`.`_queue_id`');
	while ($pen_map = $rs->fetchRow()) {
		echo '<tr><td>', $pen_map['name'], ' ', $pen_map['_title'], '</td>';
		echo '<td>';
		echo '<select name="penalty">';
		foreach ($pen_avail as $pen) {
			if ($pen_map['penalty'] == $pen) {
				echo '<option value="', $pen, '" selected="selected">';
				echo $pen, '</option>';
			} else {
				echo '<option value="', $pen,  '">', $pen, ' </option>';
			}
					}
		echo '</select>';
		echo '</td>';
		echo '<td class="l">';
			echo '<button type="submit" title="', __('Speichern'), '" class="plain">';
			echo '<img alt="', __('Speichern') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/filesave.png" /></button>' ,"\n";
			echo '<input type="hidden" name="action" value="setpenalty" />', "\n";
			echo '<input type="hidden" name="queue_id" value="', $pen_map['_queue_id'], '" />', "\n";
			echo '<input type="hidden" name="edit" value="', rawUrlEncode($edit_user), '">', "\n";
			echo '<input type="hidden" name="name" value="', htmlEnt($name), '" />', "\n";
			echo '<input type="hidden" name="number" value="', htmlEnt($number), '" />', "\n";
			echo '<input type="hidden" name="page" value="', (int)$page, '" />', "\n";
			echo '<input type="hidden" name="sort" value="', $sort, '" />', "\n";
			echo '<input type="hidden" name="sortorder" value="', $sortorder, '" />', "\n";
			echo '</form>';
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'queue_id='.$pen_map['_queue_id'] .'&amp;edit='. rawUrlEncode($edit_user) .'&amp;action=delpenalty' .'&amp;name='. rawUrlEncode($name) ), '" title="', __('entfernen'), '"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
		echo '</td>';
		echo '</tr>';
	}
	echo '</tbody></table>';

?>
<?php
}
?>