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
include_once( GS_DIR .'inc/canonization.php' );
include_once( GS_DIR .'inc/group-fns.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


$action = @$_REQUEST['action'];
if ($action == 'canonize')
	$number = trim(@$_REQUEST['number']);
else
	$number = '030 1234567';


$sudo_url = (@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
	? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);

?>

<p class="text"><?php echo __('Hier k&ouml;nnen Sie &uuml;berpr&uuml;fen, wie nach extern gew&auml;hlte Telefonnummern entsprechend Ihrer Einstellungen kanonisiert und geroutet werden.'); ?></p>


<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="canonize" />

<label for="ipt-number"><?php echo sPrintF(__('Telefonnummer (ohne %s zur Amtsholung)'), '0'); ?>:</label><br />
<input type="text" name="number" id="ipt-number" value="<?php echo $number; ?>" size="25" maxlength="30" />

<input type="submit" value="<?php echo __('Testen'); ?>" />
</form>
<br />



<h3><?php echo __('Kanonisierung'); ?></h3>

<?php
###################################################### CANONIZATION {
if (gs_get_conf('GS_CANONIZE_OUTBOUND')) {
	$canonical = new CanonicalPhoneNumber( $number );
?>

<table cellspacing="1">
<tbody>

<tr class="even">
	<th><?php echo __('Kanonisch'); ?>:</th>
	<td class="r"><tt><?php echo $canonical->norm; ?></tt></td>
</tr>

<tr class="odd">
	<th><?php echo __('International'); ?>:</th>
	<td class="r"><tt><?php echo $canonical->intl; ?></tt></td>
</tr>

<tr class="even">
	<th><?php echo __('National'); ?>:</th>
	<td class="r"><tt><?php echo $canonical->natl; ?></tt></td>
</tr>

<tr class="odd">
	<th><?php echo __('Innerorts'); ?>:</th>
	<td class="r"><tt><?php echo $canonical->locl; ?></tt></td>
</tr>

<tr class="even">
	<th><?php echo __('In eigener Telefonanlage?'); ?>:</th>
	<td><?php echo ($canonical->in_prv_branch ? __('ja') : __('nein')); ?></td>
</tr>

<tr class="odd">
	<th><?php echo __('Durchwahl'); ?>:</th>
	<td class="r"><tt><?php echo $canonical->extn; ?></tt></td>
</tr>

<tr class="even">
	<th><?php echo __('Sondernummer?'); ?>:</th>
	<td><?php echo ($canonical->is_special ? __('ja') : __('nein')); ?></td>
</tr>

<tr class="odd">
	<th><?php echo __('Call-by-Call?'); ?>:</th>
	<td><?php echo ($canonical->is_call_by_call ? __('ja') : __('nein')); ?></td>
</tr>

<tr class="even">
	<th><?php echo __('Ergebnis'); ?>:</th>
	<td class="r"><tt><?php echo $canonical->dial; ?></tt></td>
</tr>

</tbody>
</table>

<br />
<p>
<?php
$dial = '';
switch (@$canonical->errt) {
	case 'empty':
		echo __('Keine Telefonnummer angegeben.');
		break;
	case 'cbc':
		echo __('Der Endanwender soll keine Call-by-Call-Vorwahlen verwenden.');
		break;
	case 'self':
		echo __('Diese Nummer ist innerhalb der eigenen Telefonanlage.');
		break;
	case '':
	default:
		$dial = $canonical->dial;
		echo sPrintF(__('Die Nummer w&uuml;rde als <b>%s</b> mit den Mustern in der Routing-Tabelle verglichen.'), $dial);
}
?>
</p>

<?php
}
###################################################### CANONIZATION }


###################################################### NO CANONIZATION {
else {
	echo '<p class="text">(', __('Kanonisierung ist nicht aktiviert!') ,')</p>', "\n";
	$dial = trim($number);
}
###################################################### NO CANONIZATION }




###################################################### ROUTING {
if ($dial != '') {
?>

<h3><?php echo __('Zutreffende Suchmuster'); ?></h3>

<table cellspacing="1">
<thead>
<tr>
	<th><?php echo __('Wochentage'); ?></th>
	<th><?php echo __('Uhrzeit'); ?></th>
	<th><?php echo __('Gruppe'); ?></th>
	<th><?php echo __('Muster'); ?></th>
	<?php /* ?>
	<th><?php echo sPrintF(__('Route Prio. %d'), 1); ?></th>
	<th><?php echo sPrintF(__('Route Prio. %d'), 2); ?></th>
	<th><?php echo sPrintF(__('Route Prio. %d'), 3); ?></th>
	<?php */ ?>
	<th><?php echo __('Route'); ?></th>
	<th><?php echo __('Fallback'); ?></th>
	<th><?php echo __('Fallback'); ?></th>
	<th><?php echo __('Pr&auml;fix'); ?> <sup>[1]</sup></th>
</tr>
</thead>
<tbody>

<?php
	$wdays = array( 'mo'=>'Mo', 'tu'=>'Di', 'we'=>'Mi', 'th'=>'Do', 'fr'=>'Fr', 'sa'=>'Sa', 'su'=>'So',  );
	
	$rsR = $DB->execute(
'SELECT
	`pattern` `pat`,
	`d_mo`, `d_tu`, `d_we`, `d_th`, `d_fr`, `d_sa`, `d_su`,
	SUBSTR(`h_from`,1,5) `hf`, SUBSTR(`h_to`,1,5) `ht`,
	`gw_grp_id_1` `gg1`, `gw_grp_id_2` `gg2`, `gw_grp_id_3` `gg3`,
	`lcrprfx`, `user_grp_id`
FROM `routes` USE INDEX(`ord`)
WHERE `active`=1
ORDER BY `ord`'
	);
	$i=0;
	while ($route = $rsR->fetchRow()) {
		if (! @preg_match( '/'.$route['pat'].'/', $dial )) continue;
		
		if ($route['user_grp_id']) {
			$group_info = gs_group_info_get( array($route['user_grp_id']) );
			$group_name = @$group_info[0]['title'];
		}
		else {
			$group_name = '('. __("keine") .')';
		}
		
		echo '<tr class="', ($i%2 ? 'even':'odd') ,'">', "\n";
		
		/*
		echo '<td>', "\n";
		$wd_out = array();
		foreach ($wdays as $col => $v)
			if (@$route['d_'.$col]) $wd_out[] = $v;
		echo htmlEnt(implode(', ', $wd_out));
		echo '</td>', "\n";
		*/
		echo '<td class="pre">';
		foreach ($wdays as $col => $v) {
			echo (@$route['d_'.$col] ? htmlEnt($v) : '&nbsp;&nbsp;'), ' ';
		}
		echo '</td>', "\n";
		
		echo '<td class="pre">';
		/*
		echo (subStr($route['hf'],2,3) != ':00')
			? $route['hf']
			: subStr($route['hf'],0,2) .'<span style="color:#999;">'. subStr($route['hf'],2,3) .'</span>';
		*/
		echo subStr($route['hf'],0,2) .'<sup>'. subStr($route['hf'],3,2) .'</sup>';
		echo '&thinsp;-&thinsp;';
		echo subStr($route['ht'],0,2) .'<sup>'. subStr($route['ht'],3,2) .'</sup>';
		echo ' </td>', "\n";
		
		echo '<td>', htmlEnt($group_name) ,' </td>', "\n";
		echo '<td class="pre">', htmlEnt($route['pat']) ,' </td>', "\n";
		
		$gate_grps = array();
		if ($route['gg1'] != 0) $gate_grps[] = (int)$route['gg1'];
		if ($route['gg2'] != 0) $gate_grps[] = (int)$route['gg2'];
		if ($route['gg3'] != 0) $gate_grps[] = (int)$route['gg3'];
		$gg_cnt = 0;
		foreach ($gate_grps as $ggrp_id) {
			$gg_title = $DB->executeGetOne( 'SELECT `title` FROM `gate_grps` WHERE `id`='. $ggrp_id );
			if ($gg_title === null || $gg_title === false) continue;
			echo '<td>', htmlEnt($gg_title) ,'</td>';
			++$gg_cnt;
		}
		for ($k=$gg_cnt; $k<3; ++$k) {
			echo '<td>&nbsp;</td>';
		}
		
		echo '<td class="pre">';
		echo htmlEnt($route['lcrprfx']);
		echo ' </td>', "\n";
		
		echo '</tr>', "\n";
	}
	
	
?>

</tbody>
</table>

<p class="text"><small>(<?php echo __('Dabei ist die Reihenfolge entscheidend; das erste zutreffende Muster gewinnt.'); ?>)</small></p>

<p class="text"><small><sup>[1]</sup> <?php echo __('Pr&auml;fix f&uuml;r LCR (Least Cost Routing). Gilt nur f&uuml;r ISDN-Verbindungen, nicht f&uuml;r SIP.'); ?></small></p>

<?php
}
###################################################### ROUTING }

?>
