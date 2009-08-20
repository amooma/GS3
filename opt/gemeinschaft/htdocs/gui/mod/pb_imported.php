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


echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";


$per_page = (int)GS_GUI_NUM_RESULTS;

$name   =  trim(@$_REQUEST['name'  ]);
$number =  trim(@$_REQUEST['number']);
$page   = (int)(@$_REQUEST['page'  ]);


if ($number != '') {
	
	# search by number
	
	$search_url = 'number='. urlEncode($number);
	
	$number_sql = str_replace(
		array( '*', '?' ),
		array( '%', '_' ),
		$number
	) .'%';
	$rs = $DB->execute(
		'SELECT SQL_CALC_FOUND_ROWS '.
			'`firstname` `fn`, `lastname` `ln`, `number` `ext` '.
		'FROM `pb_ldap` '.
		'WHERE `number` LIKE \''. $DB->escape($number_sql) .'\' '.
		'ORDER BY `number` '.
		'LIMIT '. ($page*(int)$per_page) .','. (int)$per_page
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
		'SELECT SQL_CALC_FOUND_ROWS '.
			'`firstname` `fn`, `lastname` `ln`, `number` `ext` '.
		'FROM `pb_ldap` '.
		'WHERE '.
			'`lastname` LIKE _utf8\''. $DB->escape($name_sql) .'\' COLLATE utf8_unicode_ci OR '.
			'`firstname` LIKE _utf8\''. $DB->escape($name_sql) .'\' COLLATE utf8_unicode_ci '.
		'ORDER BY `lastname`, `firstname`, `user`, `number` DESC '.
		'LIMIT '. ($page*(int)$per_page) .','. (int)$per_page
		);
	$num_total = @$DB->numFoundRows();
	$num_pages = ceil($num_total / $per_page);
	
}


?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:270px;"><?php echo __('Name suchen'); ?></th>
	<th style="width:200px;"><?php echo __('Nummer suchen'); ?></th>
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
	'<img alt="weiter" src="', GS_URL_PATH, 'crystal-svg/32/act/forward-cust.png" />',
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
	<th style="width:270px;"<?php if ($number=='') echo ' class="sort-col"'; ?>>
		<?php echo __('Name'); ?>
	</th>
	<th style="width:200px;"<?php if ($number!='') echo ' class="sort-col"'; ?>>
		<?php echo __('Nummer'); ?>
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
		
		echo '<td>', htmlEnt($r['ln']);
		if ($r['fn'] != '') echo ', ', htmlEnt($r['fn']);
		//if (@subStr($r['ext'],0,1)=='0') echo ' <small>(extern)</small>';
		echo '</td>', "\n";
		
		echo '<td>', htmlEnt($r['ext']), '</td>', "\n";
		
		echo '<td>';
		$sudo_url =
			(@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
			? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);
		if ($r['ext'] != $_SESSION['sudo_user']['info']['ext'])
			echo '<a href="', GS_URL_PATH, 'srv/pb-dial.php?n=', rawUrlEncode($r['ext']), $sudo_url, '" title="', __('w&auml;hlen'), '"><img alt="', __('w&auml;hlen'), '" src="', GS_URL_PATH, 'crystal-svg/16/app/yast_PhoneTTOffhook.png" /></a>';
		else echo '&nbsp;';
		echo '</td>';
		
		echo '</tr>', "\n";
	}
}

?>

</tbody>
</table>
