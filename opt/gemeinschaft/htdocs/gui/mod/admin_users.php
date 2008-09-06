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

######################################################
##
##   ALL STRINGS IN HERE NEED TO BE TRANSLATED!
##
######################################################

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
require_once( GS_DIR .'inc/boi-soap/boi-api.php' );

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
$save_user   = trim(@$_REQUEST['save'     ]);
$delete_user = trim(@$_REQUEST['delete'   ]);

$cbdelete    = trim(@$_REQUEST['cbdelete' ]);
$cbregexp    = trim(@$_REQUEST['cbregexp' ]);
$cbpin       = trim(@$_REQUEST['cbpin'    ]);

$extnum      = trim(@$_REQUEST['extnum'   ]);
$extnumdel   = trim(@$_REQUEST['extndel'  ]);

$upgroups    =      @$_REQUEST['upgroup'  ] ;
$upgrouped   =      @$_REQUEST['upgrouped'] ;

$ugroup_ed   =      @$_REQUEST['ugroup_ed'] ;
$ugroup_id   = (int)@$_REQUEST['ugroup_id'] ;

$user_fname  = trim(@$_REQUEST['ufname'   ]);
$user_lname  = trim(@$_REQUEST['ulname'   ]);
$user_ext    = trim(@$_REQUEST['uext'     ]);
$user_name   = trim(@$_REQUEST['uuser'    ]);
$user_pin    = trim(@$_REQUEST['upin'     ]);
$user_email  = trim(@$_REQUEST['uemail'   ]);
$user_host   = trim(@$_REQUEST['uhost'    ]);



if ($delete_user) {
	$ret = gs_user_del( $delete_user );
	if (isGsError( $ret )) echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
}
if ($save_user) {
	$ret = gs_user_change( $save_user, $user_pin, $user_fname, $user_lname, $user_host, false, $user_email );
	if (isGsError( $ret )) echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
	if (! isGsError( $ret )) {
		$boi_api = gs_host_get_api((int)$user_host);
		if ($boi_api == '') {
			$uid = (int)$DB->executeGetOne( 'SELECT `id` FROM `users` WHERE `user`=\''. $DB->escape($save_user) .'\'' );
			if ($uid > 0) {
				$DB->execute( 'UPDATE `ast_sipfriends` SET `secret`=\''. $DB->escape(preg_replace('/[^0-9a-zA-Z]/', '', @$_REQUEST['usecret'])) .'\' WHERE `_user_id`='. $uid );
			}
		}
	}
}
if ($user_name) {
	$ret = gs_user_add( $user_name, $user_ext, $user_pin, $user_fname, $user_lname, $user_host, $user_email );
	if (isGsError( $ret )) echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
}
if ($cbdelete) {
	$ret = gs_callblocking_delete( $edit_user, $cbdelete );
	if (isGsError( $ret )) echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
}
if ($extnumdel) {
	$ret = gs_user_external_number_del( $edit_user, $extnumdel );
	if (isGsError( $ret )) echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
}
if ($cbregexp) {
	$ret = gs_callblocking_set( $edit_user, $cbregexp, $cbpin );
	if (isGsError( $ret )) echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
}
if ($extnum) {
	$ret = gs_user_external_number_add( $edit_user, $extnum );
	if (isGsError( $ret )) echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
}



if ($upgrouped && $edit_user) {
	$sql_query = 'DELETE `p`
FROM `pickupgroups_users` `p` , `users` `u`
WHERE
	`p`.`user_id` = `u`.`id` AND
	`u`.`user` = \''.$DB->escape($edit_user).'\'';
	
	$rs = $DB->execute($sql_query);
	
	if (is_array($upgroups)) {
		foreach ($upgroups as $upgroup) {
			if ($upgroup < 1) continue;
			$ret = gs_pickupgroup_user_add( $upgroup, $edit_user );
			if (isGsError( $ret )) echo '<div class="errorbox">', $ret->getMsg() ,'</div>',"\n";
		}
	}
}

if ($ugroup_ed && $edit_user) {
	$query =
		'UPDATE `users` SET '.
			'`group_id`='. ($ugroup_id > 0 ? $ugroup_id : 'NULL') .' '.
		'WHERE `user`=\''. $DB->escape($edit_user) .'\'';
	$ok = $DB->execute($query);
}

if (!$edit_user) {
	
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
	`h`.`is_foreign`,
	`hp1`.`value` `hp_route_prefix`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) LEFT JOIN
	`hosts` `h` ON (`h`.`id`=`u`.`host_id`) LEFT JOIN
	`host_params` `hp1` ON (`hp1`.`host_id`=`h`.`id` AND `hp1`.`param`=\'route_prefix\')
WHERE
	`u`.`nobody_index` IS NULL AND (
	`s`.`name` LIKE \''. $DB->escape($number_sql) .'\'
	)
ORDER BY `s`.`name`
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
	`h`.`is_foreign`,
	`hp1`.`value` `hp_route_prefix`
FROM
	`users` `u` JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) LEFT JOIN
	`hosts` `h` ON (`h`.`id`=`u`.`host_id`) LEFT JOIN
	`host_params` `hp1` ON (`hp1`.`host_id`=`h`.`id` AND `hp1`.`param`=\'route_prefix\')
WHERE
	`u`.`nobody_index` IS NULL AND (
	`u`.`lastname` LIKE _utf8\''. $DB->escape($name_sql) .'\' COLLATE utf8_unicode_ci OR
	`u`.`firstname` LIKE _utf8\''. $DB->escape($name_sql) .'\' COLLATE utf8_unicode_ci
	)
ORDER BY `u`.`lastname`, `u`.`firstname`
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
		<th style="width: 70px;"><?php echo __('User'     ); ?></th>
		<th style="width:180px;"<?php if ($number=='') echo ' class="sort-col"'; ?>><?php echo __('Nachname') ,', ', __('Vorname'); ?></th>
		<th style="width: 60px;"<?php if ($number!='') echo ' class="sort-col"'; ?>><?php echo __('Nebenst.' ); /*//TRANSLATEME*/ ?></th>
		<th style="width: 55px;"><?php echo __('PIN'      ); ?></th>
		<th style="width:165px;"><?php echo __('E-Mail'   ); ?></th>
		<th style="width: 42px;"><?php echo __('Host'     ); ?></th>
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
				echo subStr($r['ext'],strLen($r['hp_route_prefix']));
			} else {
				echo $r['ext'];
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
			//echo '<td>', $r['hid'] ,' (',@$hosts[$r['hid']],') </td>' ,"\n";
			echo '<td class="r">', $r['hid'] ,'</td>' ,"\n";
			
			echo '<td>';
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
			
			echo '<td>';
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'edit='. rawUrlEncode($r['usern']) .'&amp;name='. rawUrlEncode($name) .'&amp;number='. rawUrlEncode($number) .'&amp;page='.$page), '" title="',__('bearbeiten'), '"><img alt="',__('bearbeiten'), '" src="',GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='. rawUrlEncode($r['usern']) .'&amp;name='. rawUrlEncode($name) .'&amp;number='. rawUrlEncode($number) .'&amp;page='.$page), '" title="',__('l&ouml;schen'), '"><img alt="',__('entfernen'), '" src="',GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
			echo "</td>\n";
			
			echo '</tr>', "\n";
			@ob_flush(); @flush();
		}
	}
	
	?>
	<tr>
	<?php
	
	if (!$edit_user) {
		
		//FIXME - tr > form is invalid
		echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
		echo gs_form_hidden($SECTION, $MODULE), "\n";
		echo '<input type="hidden" name="name" value="', htmlEnt($name), '" />', "\n";
		echo '<input type="hidden" name="number" value="', htmlEnt($number), '" />', "\n";
	?>
		<td>
			<input type="text" name="uuser" id="ipt-uuser" value="" size="8" maxlength="20" />
		</td>
		<td>
			<input type="text" name="ulname" id="ipt-ulname" value="" size="15" maxlength="40" style="width:80px;" title="<?php echo __('Nachname'); ?>" />,
			<input type="text" name="ufname" id="ipt-ufname" value="" size="15" maxlength="40" style="width:70px;" title="<?php echo __('Vorname'); ?>" />
		</td>
		<td>
			<input type="text" name="uext" id="ipt-uext" value="" size="8" maxlength="10" />
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
			
			$rs_hosts = $DB->execute('SELECT `id`, `host`, `comment` FROM `hosts` WHERE `is_foreign`=0 ORDER BY `id`');
			while ($h = $rs_hosts->fetchRow()) {
				echo '<option value="',$h['id'] ,'"';
				echo ' title="Gemeinschaft ', htmlEnt($h['host']) ,' (', htmlEnt($h['comment']) ,')"';
				if ($h['id'] == $r['hid']) echo ' selected="selected"';
				echo '>', $h['id'] ,'</option>',"\n";
			}
			unset($rs_hosts);
			
			$rs_hosts = $DB->execute('SELECT `id`, `host`, `comment` FROM `hosts` WHERE `is_foreign`=1 ORDER BY `host`');
			if ($rs_hosts->numRows() != 0) {
				echo '<option value="" disabled="disabled">--</option>',"\n";
				while ($h = $rs_hosts->fetchRow()) {
					echo '<option value="',$h['id'] ,'"';
					echo ' title="', __('Fremd-Host') ,' ', htmlEnt($h['host']) ,' (', htmlEnt($h['comment']) ,')"';
					if ($h['id'] == $r['hid']) echo ' selected="selected"';
					echo '>', $h['id'] ,'</option>',"\n";
				}
			}
			unset($rs_hosts);
			
			echo '</select>',"\n";
	?>
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
		<small><?php echo __('Bei Benutern in Filialen mu&szlig; die Nebenstelle inklusive der Route aus Sicht der Zentrale angegeben werden (z.B. 60123410).'); ?></small>
	</p>
	<?php } ?>
	
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
	`u`.`firstname` `fn`, `u`.`lastname` `ln`, `u`.`host_id` `hid`, `u`.`honorific` `hnr`, `u`.`user` `usern`, `s`.`name` `ext` , `u`.`email` `email`, `u`.`pin` `pin`, `u`.`id` `uid`, `s`.`secret`, `u`.`group_id`,
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
	
	
	//$sql_query = 'SELECT `id`, `title` FROM `pickupgroups`';
	
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
		while ($r_pg = $rs->fetchRow()) {
			$pgroups_my[$r_pg['group_id']] = $r_pg['group_id'];
		}
	}
	
	$sql_query =
'SELECT `id`, `regexp`, `pin`
FROM `callblocking`
WHERE `user_id`='. $r['uid'] .'
ORDER BY LENGTH(`regexp`) DESC';
	
	$rs = $DB->execute($sql_query);
	$callblocking = array();
	if (@$rs) {
		while ($r_pg = $rs->fetchRow()) {
			$callblocking[$r_pg['id']] = $r_pg;
		}
	}
	
	$sql_query =
'SELECT `number`
FROM `users_external_numbers`
WHERE `user_id`='. $r['uid'] .'
ORDER BY LENGTH(`number`) DESC';
	
	$rs = $DB->execute($sql_query);
	$ext_nums = array();
	if (@$rs) {
		while ($r_pg = $rs->fetchRow()) {
			$ext_nums[] = $r_pg['number'];
		}
	}
	
?>



<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php
echo gs_form_hidden($SECTION, $MODULE), "\n";
echo '<input type="hidden" name="save" value="', htmlEnt($edit_user), '" />', "\n";
?>
<table cellspacing="1">
<thead>
	<tr>
		<th style="width:180px;">
			<?php echo __('Benutzer'); ?>
		</th>
		<td style="width:280px;">
			<?php echo htmlEnt($edit_user); ?>	
		</td>
	</tr>
</thead>
<tbody>
	<tr>
		<th><?php echo __('Nebenstelle'); /*//TRANSLATEME*/ ?>:</th>
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
	</tr>
	<tr>
		<th><?php echo __('Nachname'); ?>:</th>
		<td>
			<input type="text" name="ulname" value="<?php echo htmlEnt($r['ln']); ?>" size="30" maxlength="50" />
		</td>
	</tr>
	<tr>
		<th><?php echo __('Vorname'); ?>:</th>
		<td>
			<input type="text" name="ufname" value="<?php echo htmlEnt($r['fn']); ?>" size="30" maxlength="50" />
		</td>
	</tr>
	<tr>
		<th><?php echo __('PIN'); ?>:</th>
		<td>
			<input type="password" name="upin" value="<?php echo htmlEnt($r['pin']); ?>" size="8" maxlength="10" />
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
	</tr>
	<tr>
		<th><?php echo __('E-Mail'); ?>:</th>
		<td>
			<input type="text" name="uemail" value="<?php echo htmlEnt($r['email']); ?>" size="38" maxlength="60" style="width:97%;" />
		</td>
	</tr>
	<tr>
		<th><?php echo __('Host'); ?>:</th>
		<td>
<?php
		echo '<select name="uhost">',"\n";
		
		echo '<optgroup label="Gemeinschaft">',"\n";
		$rs_hosts = $DB->execute('SELECT `id`, `host`, `comment` FROM `hosts` WHERE `is_foreign`=0 ORDER BY `id`');
		while ($h = $rs_hosts->fetchRow()) {
			echo '<option value="',$h['id'] ,'"';
			if ($h['id'] == $r['hid']) echo ' selected="selected"';
			$comment = mb_subStr($h['comment'], 0, 25+1);
			if (mb_strLen($comment) > 25)
				$comment = mb_subStr($h['comment'], 0, 25-1) ."\xE2\x80\xA6";
			echo '>', htmlEnt($h['host']) ,' (#', $h['id'] ,', ', htmlEnt($comment) ,')</option>',"\n";
		}
		echo '</optgroup>',"\n";
		unset($rs_hosts);
		
		$rs_hosts = $DB->execute('SELECT `id`, `host`, `comment` FROM `hosts` WHERE `is_foreign`=1 ORDER BY `host`');
		if ($rs_hosts->numRows() != 0) {
			echo '<optgroup label="', __('Fremd-Hosts') ,'">',"\n";
			while ($h = $rs_hosts->fetchRow()) {
				echo '<option value="',$h['id'] ,'"';
				if ($h['id'] == $r['hid']) echo ' selected="selected"';
				$comment = mb_subStr($h['comment'], 0, 25+1);
				if (mb_strLen($comment) > 25)
					$comment = mb_subStr($h['comment'], 0, 25-1) ."\xE2\x80\xA6";
				echo '>', htmlEnt($h['host']) ,' (#', $h['id'] ,', ', htmlEnt($comment) ,')</option>',"\n";
			}
			echo '</optgroup>',"\n";
		}
		unset($rs_hosts);
		
		echo '</select>',"\n";
?>
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
echo '<input type="hidden" name="edit" value="', htmlEnt($edit_user), '" />', "\n";
echo '<input type="hidden" name="ugroup_ed" value="yes" />', "\n";
?>
<table cellspacing="1">
<thead>
	<tr>
		<th style="width:180px;">
			<?php echo __('Benutzergruppe'); ?>
		</th>
		<td style="width:280px;">
<?php
		$mptt = new YADB_MPTT($DB, 'user_groups', 'lft', 'rgt', 'id');
		$u_groups = $mptt->get_tree_as_list( null );
		echo '<select name="ugroup_id">',"\n";
		echo '<option value=""';
		if ($r['group_id'] == '')
			echo ' selected="selected"';
		echo '>- ', __('keine') ,' -</option>',"\n";
		if (is_array($u_groups)) {
			$is_root_node = true;
			$root_level = 0;
			foreach ($u_groups as $u_group) {
				if ($is_root_node) {  # skip root node
					$root_level = $u_group['__mptt_level'];
					$is_root_node = false;
					continue;
				}
				echo '<option value="', $u_group['id'] ,'"';
				if ($r['group_id'] == $u_group['id'])
					echo ' selected="selected"';
				echo '>';
				echo @str_repeat('&nbsp;&nbsp;&nbsp;', $u_group['__mptt_level']-$root_level-1);
				echo htmlEnt($u_group['title']);
				echo '</option>' ,"\n";
			}
		}
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

<br />

<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php
echo gs_form_hidden($SECTION, $MODULE), "\n";
echo '<input type="hidden" name="edit" value="', htmlEnt($edit_user), '" />', "\n";
echo '<input type="hidden" name="upgrouped" value="yes" />', "\n";
// upgrouped? edit pickup group?
?>
<table cellspacing="1">
<thead>
	<tr>
		<th style="width:180px;">
			<?php echo __('Pickupgruppe'); ?>
		</th>
		<td style="width:280px;">
<?php
		echo '<select multiple="multiple" name="upgroup[]" size="4">',"\n";
		foreach ($pgroups as $key => $pgroup) {
			echo '<option value="',$key,'"';
			if (@$pgroups_my[$key]) echo ' selected="selected"';
			echo '>', $key ,' (', htmlEnt($pgroup) ,')</option>',"\n";
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

<br />

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
	foreach ($callblocking as $key => $cb_entry) {
		echo "<tr>\n";
		
		echo "<td>\n";
		echo htmlEnt($cb_entry['regexp']);	
		echo "</td>\n";
		
		echo "<td>\n";
		echo htmlEnt($cb_entry['pin']);	
		echo "</td>\n";
		
		echo "<td>\n";
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'cbdelete='. rawUrlEncode($cb_entry['regexp']) .'&amp;edit='. rawUrlEncode($edit_user)), '" title="', __('entfernen'), '">';
		echo '<img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
		echo "</td>\n";		
		
		echo "</tr>\n";
	}
	
	echo "<tr>\n";
	//FIXME - tr > form is invalid
	echo '<form method="post" action="', GS_URL_PATH  ,'">', "\n";
	echo gs_form_hidden($SECTION, $MODULE), "\n";
	echo '<input type="hidden" name="edit" value="', htmlEnt($edit_user), '" />', "\n";
	
	echo "<td>\n";
	echo '<input type="text" name="cbregexp" value="" size="20" maxlength="40" />';	
	echo "</td>\n";
	
	echo "<td>\n";
	echo '<input type="text" name="cbpin" value="" size="20" maxlength="40" />';	
	echo "</td>\n";
	
	echo "<td>\n";
	echo '<button type="submit" title="', __('Speichern') ,'" class="plain">', "\n";
	echo '<img alt="', __('Speichern') ,'" src="', GS_URL_PATH ,'crystal-svg/16/act/filesave.png" />', "\n";
	echo "</button>\n";
	echo "</td>\n";
	
	echo "</form>\n";
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
	foreach ($ext_nums as $ext_num) {
		echo "<tr>\n";
		
		echo "<td>&nbsp;</td>\n";
		
		echo "<td>\n";
		echo htmlEnt($ext_num);	
		echo "</td>\n";
		
		echo "<td>\n";
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'extndel='.$ext_num .'&amp;edit='. rawUrlEncode($edit_user)), '" title="', __('entfernen'), '"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
		echo "</td>\n";		
		
		echo "</tr>\n";
	}
	
	echo "<tr>\n";
	//FIXME - tr > form is invalid
	echo '<form method="post" action="', GS_URL_PATH  ,'">', "\n";
	echo gs_form_hidden($SECTION, $MODULE), "\n";
	echo '<input type="hidden" name="edit" value="', htmlEnt($edit_user), '" />', "\n";
	
	echo "<td>&nbsp;</td>\n";
	
	echo "<td>\n";
	echo '<input type="text" name="extnum" value="" size="20" maxlength="40" />';	
	echo "</td>\n";
	
	echo "<td>\n";
	echo '<button type="submit" title="', __('Speichern') ,'" class="plain">', "\n";
	echo '<img alt="', __('Speichern') ,'" src="', GS_URL_PATH ,'crystal-svg/16/act/filesave.png" />', "\n";
	echo "</button>\n";
	echo "</td>\n";
	
	echo "</form>\n";
	echo "</tr>\n";
?>
</tbody>
</table>

<?php
}
?>
