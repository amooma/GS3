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
require_once( GS_DIR .'inc/ldap.php' );


echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt="" src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";


//$per_page = (int)GS_GUI_NUM_RESULTS;
$per_page = 50;
# paged results for LDAP is not supported in PHP

$name = trim(@$_REQUEST['name']);
$number = trim(@$_REQUEST['number']);
$page = (int)@$_REQUEST['page'];


if ($number != '') {
	
	# search by number
	
	$search_url = '&amp;number='. urlEncode($number);
	
	$number_filter = str_replace(
		array( '?' ),
		array( '*' ),
		str_replace(
			array( '('   , ')'   , '\\'  , "\x00" ),
			array( '\\28', '\\29', '\\5c', '\\00' ),
			$number
		)
	) .'*';
	$number_filter = preg_replace('/[*]+/', '*', $number_filter);
	$ldap = gs_ldap_connect();
	if (! $ldap) {
		echo __('Could not connect to LDAP server.');
		$results = array();
	} else {
		$results = gs_ldap_get_list( $ldap, GS_LDAP_SEARCHBASE,
			'('. GS_LDAP_PROP_PHONE  .'='. $number_filter. ')',
			array(GS_LDAP_PROP_PHONE, GS_LDAP_PROP_LASTNAME, GS_LDAP_PROP_FIRSTNAME),
			$per_page+1
		);
	}
	$has_more = (! isGsError($results) && count($results) > $per_page);
	if ($has_more)
		unset( $results[count($results)-1] );
	
} else {
	
	# search by name
	
	$number = '';
	$search_url = '&amp;name='. urlEncode($name);
	
	$name_filter = str_replace(
		array( '?' ),
		array( '*' ),
		str_replace(
			array( '('   , ')'   , '\\'  , "\x00" ),
			array( '\\28', '\\29', '\\5c', '\\00' ),
			$name
		)
	) .'*';
	$name_filter = preg_replace('/[*]+/', '*', $name_filter);
	$ldap = gs_ldap_connect();
	if (! $ldap) {
		echo __('Could not connect to LDAP server.');
		$results = array();
	} else {
		$results = gs_ldap_get_list( $ldap, GS_LDAP_SEARCHBASE,
			'(|('. GS_LDAP_PROP_LASTNAME  .'='. $name_filter. ')'
			. '('. GS_LDAP_PROP_FIRSTNAME .'='. $name_filter. '))',
			array(GS_LDAP_PROP_LASTNAME, GS_LDAP_PROP_FIRSTNAME, GS_LDAP_PROP_PHONE),
			$per_page+1
		);
	}
	$has_more = (! isGsError($results) && count($results) > $per_page);
	if ($has_more)
		unset( $results[count($results)-1] );
	
}


?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:270px;"><?php echo __('Name suchen'); ?></th>
	<th style="width:200px;"><?php echo __('Nummer suchen'); ?></th>
	<th style="width:75px;">&nbsp;</th>
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

if ($has_more) {
	//echo 'mehr als ', $per_page, ' Treffer';
	echo sprintf(__('mehr als %d Treffer'), $per_page);
} else
	echo '&nbsp;';

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
	echo '<a href="', gs_url($SECTION, $MODULE), '&amp;name=', htmlEnt($cs), '">', htmlEnt($cd), '</a>', "\n";
}

?>
	</td>
</tr>
</tbody>
</table>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:270px;">
		<?php echo ($number=='') ? '<span class="sort-col">'. __('Name') .'</span>' : __('Name'); ?>
	</th>
	<th style="width:200px;">
		<?php echo ($number=='') ? __('Nummer') : '<span class="sort-col">'. __('Nummer') .'</span>'; ?>
	</th>
	<th style="width:75px;">&nbsp;</th>
</tr>
</thead>
<tbody>

<?php

if (isGsError($results)) {
	
	echo '<tr><td colspan="3">', htmlEnt( $results->getMsg() ), '</td></tr>', "\n";
	
} else {
	
	foreach ($results as $i => $r) {
		echo '<tr class="', (($i % 2 == 0) ? 'odd':'even'), '">', "\n";
		
		echo '<td>', htmlEnt(@$r[GS_LDAP_PROP_LASTNAME][0]);
		if (@$r[GS_LDAP_PROP_FIRSTNAME][0] != '') echo ', ', htmlEnt(@$r[GS_LDAP_PROP_FIRSTNAME][0]);
		echo '</td>', "\n";
		
		echo '<td>', htmlEnt(@$r[GS_LDAP_PROP_PHONE][0]), '</td>', "\n";
		
		echo '<td>';
		$sudo_url =
			(@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
			? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);
		if (@$r[GS_LDAP_PROP_PHONE][0] != $_SESSION['sudo_user']['info']['ext'])
			echo '<a href="', GS_URL_PATH, 'pb-dial.php?n=', htmlEnt(@$r[GS_LDAP_PROP_PHONE][0]), $sudo_url, '" title="', __('w&auml;hlen'), '"><img alt="', __('w&auml;hlen'), '" src="', GS_URL_PATH, 'crystal-svg/16/app/yast_PhoneTTOffhook.png" /></a>';
		else echo '&nbsp;';
		echo '</td>';
		
		echo '</tr>', "\n";
	}
	
}

?>

</tbody>
</table>
