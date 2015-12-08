<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2015, Markus Neubauer, Zeitblomstr. 29, 81735 München, Germany,
* http://www.std-soft.com/
* Markus Neubauer <markus.neubauer@email-online.org>
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
include_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";
echo '<script type="text/javascript">
//<![CDATA[
function confirm_delete() {
	return confirm(', utf8_json_quote(__("Wirklich l\xC3\xB6schen?")) ,');
}
//]]>
</script>' ,"\n";

$per_page = (int)GS_GUI_NUM_RESULTS;

$name         =      trim(@$_REQUEST['name'   ]);
$url          =      trim(@$_REQUEST['url' ]);
$login        =      trim(@$_REQUEST['login' ]);
$pass         =      trim(@$_REQUEST['pass' ]);
$frequency    =      trim(@$_REQUEST['frequency' ]);
$save_url     =      trim(@$_REQUEST['surl']);
$save_login   =      trim(@$_REQUEST['slogin' ]);
$save_pass    =      trim(@$_REQUEST['spass' ]);
$save_frequency =    trim(@$_REQUEST['sfrequency' ]);
$page         = (int)    (@$_REQUEST['page'   ]);
$delete_entry = (int)trim(@$_REQUEST['delete' ]);
$edit_entry   = (int)trim(@$_REQUEST['edit'   ]);
$save_entry   = (int)trim(@$_REQUEST['save'   ]);

$user_id = (int)@$_SESSION['sudo_user']['info']['id'];

if ($delete_entry > 0) {
	# delete entry
	
	$rs = $DB->execute(
'DELETE FROM `pb_cloud`
WHERE `id`='. $delete_entry .' AND `user_id`='. $user_id
	);
	
}

if (($save_url != '' || $save_login != '') && ($save_pass != '')) {
	# save entry

	// check or correct frequency
	if ( empty($save_frequency) ) {
		$save_frequency='1d';
	} else {
		$p = substr($save_frequency, -1);
		if ( ! ('h' == $p || 'd' == $p || 'm' == $p) ) {
			$save_frequency='1d';
		}
	}
	
	if ($save_entry < 1) {
		
		$rs = $DB->execute(
'INSERT INTO `pb_cloud` (`id`, `user_id`, `url`, `login`, `pass`, `frequency`, `next_poll`) VALUES
(NULL, '. $user_id .', \''. $DB->escape($save_url) .'\', \''. $DB->escape($save_login) .'\', des_encrypt(\''. $save_pass .'\',\'' . $save_login. '\'), \''. $DB->escape($save_frequency) .'\', NOW())'
		);
		
	} else {
		
		$rs = $DB->execute(
'UPDATE `pb_cloud` SET `url`=\''. $DB->escape($save_url) .'\', `login`=\''. $DB->escape($save_login) .'\', `pass`=des_encrypt(\''. $save_pass .'\',\'' . $save_login .'\'), `frequency`=\'' . $save_frequency . '\', `next_poll`=NOW() 
 WHERE `id`='. $save_entry .' AND `user_id`='. $user_id
		);
	$save_url = '';
	$save_login = '';
	$save_pass = '';
	$save_frequency = '';
		
	}
}





if ($url != '') {
	
	# search by url
	
	$search_url = 'url='. urlEncode($url);
	
	$url_sql = '%' . str_replace(
		array( '*', '?' ),
		array( '%', '_' ),
		$url
	) .'%';
	
	$rs = $DB->execute(
		'SELECT SQL_CALC_FOUND_ROWS '.
			'`id`, `url`, `login`, cast(des_decrypt(`pass`,`login`) as char(16)) as pass, `frequency` '.
		'FROM '.
			'`pb_cloud` '.
		'WHERE '.
			'`url` LIKE \''. $DB->escape($url_sql) .'\' '.
			'AND '.
			'`user_id`='. $user_id .' '.
		'ORDER BY `url`, `login` '.
		'LIMIT '. ($page*(int)$per_page) .','. (int)$per_page
		);
	$num_total = @$DB->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	
} else {
	
	# search by login
	
	$search_url = 'login='. urlEncode($login);
	
	$login_sql = str_replace(
		array( '*', '?' ),
		array( '%', '_' ),
		$login
	) .'%';
	
	$rs = $DB->execute(
		'SELECT SQL_CALC_FOUND_ROWS '.
			'`id`, `url`, `login`, cast(des_decrypt(`pass`,`login`) as char(16)) as pass, `frequency` '.
		'FROM '.
			'`pb_cloud` '.
		'WHERE '.
			'`login` LIKE \''. $DB->escape($login_sql) .'\' '.
			'AND '.
			'`user_id`='. $DB->escape($user_id).' '.
		'ORDER BY `login`, `url` '.
		'LIMIT '. ($page*(int)$per_page) .','. (int)$per_page
		);
	$num_total = @$DB->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	
}


?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:270px;"><?php echo __('URL suchen'); ?></th>
	<th style="width:200px;"><?php echo __('Login suchen'); ?></th>
	<th style="width:100px;"><?php echo __('Seite'), ' ', ($page+1), ' / ', $num_pages; ?></th>
</tr>
</thead>
<tbody>
<tr>
	<td>
		<form method="get" action="<?php echo GS_URL_PATH; ?>">
		<?php echo gs_form_hidden($SECTION, $MODULE); ?>
		<input type="text" name="url" id="ipt-url" value="<?php echo htmlEnt($url); ?>" size="256" style="width:200px;" />
		<script type="text/javascript">/*<![CDATA[*/ try{ document.getElementById('ipt-url').focus(); }catch(e){} /*]]>*/</script>
		<button type="submit" title="<?php echo __('URL suchen'); ?>" class="plain">
			<img alt="<?php echo __('Suchen'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/search.png" />
		</button>
		</form>
	</td>
	<td>
		<form method="get" action="<?php echo GS_URL_PATH; ?>">
		<?php echo gs_form_hidden($SECTION, $MODULE); ?>
		<input type="text" name="login" value="<?php echo htmlEnt($login); ?>" size="32" style="width:130px;" />
		<button type="submit" title="<?php echo __('Login suchen'); ?>" class="plain">
			<img alt="<?php echo __('Suchen'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/search.png" />
		</button>
		</form>
	</td>
	<td rowspan="2">
<?php

if ($page > 0) {
	echo
	'<a href="', gs_url($SECTION, $MODULE, null, $search_url), '&amp;page=', ($page-1), '" title="', __('zur&uuml;ckbl&auml;ttern'), '" id="arr-prev">',
	'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/back-cust.png" />',
	'</a>', "\n";
} else {
	echo
	'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/32/act/back-cust-dis.png" />', "\n";
}
if ($page < $num_pages-1) {
	echo
	'<a href="', gs_url($SECTION, $MODULE, null, $search_url), '&amp;page=', ($page+1), '" title="', __('weiterbl&auml;ttern'), '" id="arr-next">',
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
	echo '<a href="', gs_url($SECTION, $MODULE, null, 'login='. htmlEnt($cs)), '">', htmlEnt($cd), '</a>', "\n";
}

?>
	</td>
</tr>
</tbody>
</table>



<?php
echo '<form method="post" action="', GS_URL_PATH, '">', "\n";
echo gs_form_hidden($SECTION, $MODULE), "\n";
echo '<input type="hidden" name="url" value="', htmlEnt($url), '" />', "\n";
echo '<input type="hidden" name="login" value="', htmlEnt($login), '" />', "\n";
?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:270px;"<?php if ($url=='') echo ' class="sort-col"'; ?>>
		<?php echo __('vCard URL'); ?> <sup>[1]</sup>
	</th>
	<th style="width:200px;"<?php if ($login!='') echo ' class="sort-col"'; ?>>
		<?php echo __('Login'); ?>
	</th>
	<th style="width:200px;">
		<?php echo __('Password'); ?>
	</th>
	<th style="width:50px;">
		<?php echo __('Schedule'); ?><sup>[2]</sup>
	</th>
	<th style="width:100px;">&nbsp;</th>
</tr>
</thead>
<tbody>

<?php

if (@$rs) {
	$i = 0;
	while ($r = $rs->fetchRow()) {
		echo '<tr class="', ((++$i % 2 == 0) ? 'even':'odd'), '">', "\n";
		
		if ($r['id']==$edit_entry) {
 			
			echo '<td>';
			echo '<input type="url" name="surl" value="', htmlEnt($r['url']), '" size="15" maxlength="256" style="width:250px;" />';
			echo '</td>', "\n";
			
			echo '<td>';
			echo '<input type="text" name="slogin" value="', htmlEnt($r['login']), '" size="15" maxlength="32" style="width:150px;" />';
			echo '</td>', "\n";
			
			echo '<td>';
			echo '<input type="password" name="spass" value="', htmlEnt($r['pass']), '" size="15" maxlength="16" style="width:150px;" />';
			echo '</td>', "\n";
			
			echo '<td>';
			echo '<input type="text" name="sfrequency" value="', htmlEnt($r['frequency']), '" size="3" maxlength="3" style="width:150px;" />';
			echo '</td>', "\n";
			
			echo '<td>';
			echo '<input type="hidden" name="save" value="', $r['id'], '" />';
			echo '<input type="hidden" name="page" value="', $page, '" />';
			echo '<button type="submit" title="', __('Eintrag speichern'), '" class="plain">';
			echo '<img alt="', __('speichern'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/filesave.png" />';
			echo '</button>';
			echo '<button type="reset" title="', __('r&uuml;ckg&auml;ngig'), '" class="plain">';
			echo '<img alt="', __('r&uuml;ckg&auml;ngig'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/reload.png" />';
			echo '</button>';
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'page='. $page), '" title="', __('abbrechen'), '"><img alt="', __('abbrechen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/cancel.png" /></a>';
	
			echo '</td>';
			
		} else {
			
			echo '<td>', htmlEnt($r['url']);
			echo '</td>', "\n";
			
			echo '<td>', htmlEnt($r['login']), '</td>', "\n";
			
			echo '<td>', str_repeat('&bull;', strLen($r['pass'])), '</td>', "\n";
			
			echo '<td>', htmlEnt($r['frequency']), '</td>', "\n";
			
			echo '<td>';
			$sudo_url =
				(@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
				? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'edit='.$r['id'] .'&amp;name='. rawUrlEncode($name) .'&amp;number='. rawUrlEncode($number) .'&amp;page='.$page), '" title="', __('bearbeiten'), '"><img alt="', __('bearbeiten'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='.$r['id'] .'&amp;page='.$page), '" title="', __('entfernen'), '" onclick="return confirm_delete();"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
			echo '</td>';
			
		}
		
		echo '</tr>', "\n";
	}
}

?>


<tr>
<?php
if ($edit_entry < 1) {
?>
	<td>
		<input type="url" name="surl" value="" size="30" maxlength="256" style="width:250px;" />
	</td>
	<td>
		<input type="text" name="slogin" value="" size="15" maxlength="32" style="width:150px;" />
	</td>
	<td>
		<input type="password" name="spass" value="" size="15" maxlength="16" style="width:150px;" />
	</td>
	<td>
		<input type="text" name="sfrequency" value="" size="3" maxlength="3" style="width:50px;" />
	</td>
	<td>
		<button type="submit" title="<?php echo __('Eintrag speichern'); ?>" class="plain">
			<img alt="<?php echo __('Speichern'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
		</button>
		<?php /*echo '<a href="', gs_url($SECTION, $MODULE, null, 'page='.$page), '" title="abbrechen"><img alt="abbrechen" src="', GS_URL_PATH, 'crystal-svg/16/act/cancel.png" /></a>';*/ ?>
	</td>
<?php
}
?>
</tr>

</tbody>
</table>

</form>
<br>
<p class="text">Hint: vCards automatically will be fetched on new entry or record changes, besides schedule.</p>
<p class="text"><sup>[1]</sup> URL: Enter the full vCard URL including scheme from your provider. Naiv examples:</p>
<div class="text">
<dl class="text">
<dt class="text">ownCloud</dt><dd class="text">https://example.com/remote.php/carddav/addressbooks/{resource|principal|username}/{collection}/</dd>
<dt class="text">memotoo</dt><dd class="text">https://sync.memotoo.com/cardDAV/</dd>
<dt class="text">SabreDAV</dt><dd class="text">https://example.com/addressbooks/{resource|principal|username}/{collection}/</dd>
<dt class="text">radicale</dt><dd class="text">https://example.com/radicale/{resource|principal|username}/{collection}/</dd>
<dt class="text">SOGo</dt><dd class="text">https://example.com/SOGo/dav/{resource|principal|username}/Contacts/{collection}/</dd>
<dt class="text">DAViCal</dt><dd class="text">https://example.com/{resource|principal|username}/{collection}/</dd>
<dt class="text">Apple Addressbook Server</dt><dd class="text">https://example.com/addressbooks/users/{resource|principal|username}/{collection}/</dd>
</dl>
</div>
<p class="text"><sup>[2]</sup> Schedule: Refresh in h=hour, d=day, m=month etc., two digits followed by one char allowed<br> &nbsp; &nbsp; &nbsp; Example: Where '4h' is every 4 hours (~ also during night). Defaults to '1d'</p>
