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
include_once( GS_DIR .'inc/pcre_check.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );
include_once( GS_DIR .'inc/gs-fns/gs_groups_get.php' );


echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
/*
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
*/
echo htmlEnt(__('Ausgehende Routen und Least-Cost-Routing'));
echo '</h2>', "\n";


$action = @$_REQUEST['action'];
$id     = (int)@$_REQUEST['id'];
if ($id < 1) $id = 0;

if ($action === 'move-up' || $action === 'move-down') {
	
	if ($id > 0) {
		gs_db_start_trans($DB);
		$rs = $DB->execute( 'SELECT `id` FROM `routes` ORDER BY `ord`' );
		$ord = 4;
		while ($r = $rs->fetchRow()) {
			if ($r['id'] != $id)
				$DB->execute( 'UPDATE `routes` SET `ord`='. $ord .' WHERE `id`='. (int)$r['id'] );
			else
				$DB->execute( 'UPDATE `routes` SET `ord`='. ($ord + ($action=='move-up' ? -3 : 3)) .' WHERE `id`='. (int)$r['id'] );
			$ord += 2;
		}
		gs_db_commit_trans($DB);
		
		@$DB->execute( 'OPTIMIZE TABLE `routes`' );
		@$DB->execute( 'ANALYZE TABLE `routes`' );
	}
	
} elseif ($action === 'save') {
	
	$rs = $DB->execute( 'SELECT `id` FROM `gate_grps`' );
	$ggs = array();
	while ($r = $rs->fetchRow()) $ggs[(int)$r['id']] = true;
	
	$rs = $DB->execute( 'SELECT `id` FROM `user_groups`' );
	$ugs = array();
	while ($r = $rs->fetchRow()) $ugs[(int)$r['id']] = true;
	
	$rs = $DB->execute( 'SELECT `id` FROM `routes`' );
	$db_ids = array();
	while ($r = $rs->fetchRow())
		$db_ids[] = (string)(int)$r['id'];
	$db_ids[] = 0;  # add 0 for the new rule
	
	foreach ($db_ids as $dbid) {
		if (! array_key_exists('r_'.$dbid.'_pattern', $_REQUEST)) continue;
		
		$pattern = trim( @$_REQUEST['r_'.$dbid.'_pattern'] );
		if ($dbid<1 && $pattern == '') continue;
		$pattern = preg_replace('/[^0-9a-zA-Z\-\\\#*+\[\]\{\}\(\)|\^$.,?!=:<>]/', '', $pattern);
		if (is_valid_pcre( '/'.$pattern.'/' )) {
			$set_pattern = '`pattern`=\''. $DB->escape( $pattern ) .'\',';
		} else {
			if ($dbid < 1) continue;  # do not add rule if pattern is invalid
			$set_pattern = '';
		}
		
		$h_from_h = (int)lTrim(@$_REQUEST['r_'.$dbid.'_h_from_h'],' 0');
		if     ($h_from_h <  0) $h_from_h =  0;
		elseif ($h_from_h > 23) $h_from_h = 23;
		$h_from_h = str_pad($h_from_h, 2, '0', STR_PAD_LEFT);
		$h_from_m = (int)lTrim(@$_REQUEST['r_'.$dbid.'_h_from_m'],' 0');
		if     ($h_from_m <  0) $h_from_m =  0;
		elseif ($h_from_m > 59) $h_from_m = 59;
		$h_from_m = str_pad($h_from_m, 2, '0', STR_PAD_LEFT);
		$h_from = $h_from_h.':'.$h_from_m;
		
		$h_to_h = (int)lTrim(@$_REQUEST['r_'.$dbid.'_h_to_h'],' 0');
		if     ($h_to_h <  0) $h_to_h =  0;
		elseif ($h_to_h > 24) $h_to_h = 24;
		$h_to_h = str_pad($h_to_h, 2, '0', STR_PAD_LEFT);
		$h_to_m = (int)lTrim(@$_REQUEST['r_'.$dbid.'_h_to_m'],' 0');
		if     ($h_to_m <  0) $h_to_m =  0;
		elseif ($h_to_m > 59) $h_to_m = 59;
		$h_to_m = str_pad($h_to_m, 2, '0', STR_PAD_LEFT);
		$h_to = $h_to_h.':'.$h_to_m;
		if ($h_to > '24:00') $h_to = '24:00';
		if ($h_to < $h_from) $h_to = $h_from;
		
		$gg1 = (int)@$_REQUEST['r_'.$dbid.'_ggrpid1'];
		$gg2 = (int)@$_REQUEST['r_'.$dbid.'_ggrpid2'];
		$gg3 = (int)@$_REQUEST['r_'.$dbid.'_ggrpid3'];
		
		$ug_id = (int)@$_REQUEST['r_'.$dbid.'_ugrpid'];
		
		if ($dbid<1) {
			gs_db_start_trans($DB);
			$ord = (int)$DB->executeGetOne( 'SELECT MAX(`ord`) FROM `routes`' ) + 1;
		}
		$query =
($dbid>0 ? 'UPDATE' : 'INSERT INTO') .' `routes` SET
	'. ($dbid>0 ? '' : '`id`=NULL,') .'
	`active`='. ((int)(bool)(@$_REQUEST['r_'.$dbid.'_active'])) .',
	'. ($dbid>0 ? '' : '`ord`='.$ord.',') .'
	'. $set_pattern .'
	`d_mo`='. ((int)(bool)@$_REQUEST['r_'.$dbid.'_d_mo']) .',
	`d_tu`='. ((int)(bool)@$_REQUEST['r_'.$dbid.'_d_tu']) .',
	`d_we`='. ((int)(bool)@$_REQUEST['r_'.$dbid.'_d_we']) .',
	`d_th`='. ((int)(bool)@$_REQUEST['r_'.$dbid.'_d_th']) .',
	`d_fr`='. ((int)(bool)@$_REQUEST['r_'.$dbid.'_d_fr']) .',
	`d_sa`='. ((int)(bool)@$_REQUEST['r_'.$dbid.'_d_sa']) .',
	`d_su`='. ((int)(bool)@$_REQUEST['r_'.$dbid.'_d_su']) .',
	`h_from`=\''. $DB->escape($h_from) .'\',
	`h_to`=\''  . $DB->escape($h_to  ) .'\',
	`descr`=\''. $DB->escape(trim(@$_REQUEST['r_'.$dbid.'_descr'])) .'\',
	`lcrprfx`=\''. $DB->escape(preg_replace('/[^0-9*#]/S', '', @$_REQUEST['r_'.$dbid.'_lcrprfx'])) .'\',
	`gw_grp_id_1`='. (array_key_exists($gg1, $ggs) ? $gg1 : '0') .',
	`gw_grp_id_2`='. (array_key_exists($gg2, $ggs) ? $gg2 : '0') .',
	`gw_grp_id_3`='. (array_key_exists($gg3, $ggs) ? $gg3 : '0') .',
	`user_grp_id`='. (array_key_exists($ug_id, $ugs) ? $ug_id : 'NULL') .'
'. ($dbid>0 ? 'WHERE `id`='. (int)$dbid : '')
		;
		$ok = $DB->execute($query);
		//if (!$ok) echo "<pre>\n$query</pre>\n";
		if ($dbid<1) {
			gs_db_commit_trans($DB);
		}
	}
	
	@$DB->execute( 'OPTIMIZE TABLE `routes`' );
	@$DB->execute( 'ANALYZE TABLE `routes`' );
	
} elseif ($action === 'del') {
	
	if ($id > 0) {
		$DB->execute( 'DELETE FROM `routes` WHERE `id`='. $id );
		
		@$DB->execute( 'OPTIMIZE TABLE `routes`' );
		@$DB->execute( 'ANALYZE TABLE `routes`' );
	}
	
}




# get abbreviated names of the days of the week for locale
# (Debian: apt-get install locales locales-all)
#
$oldLocale = setLocale(LC_TIME, '0'); # probably "C"
$lang = @$_SESSION['lang'];
if (! $lang)
	$lang = gs_get_conf('GS_INTL_LANG', 'de_DE');
$lang = strToLower(subStr($lang,0,2));
switch ($lang) {
	case 'de':
		$l = array('de_DE.UTF-8', 'de_DE.utf8', 'de_DE.iso88591', 'de_DE.iso885915@euro', 'de_DE.ISO8859-1', 'de_DE.ISO8859-15', 'de_DE@euro', 'de_DE', 'de');
		break;
	case 'en':
		$l = array('en_US.utf8', 'en_US.iso88591', 'en_US.ISO8859-1', 'en_US.US-ASCII', 'en_US', 'en');
		break;
	default  :
		$l = array('C');
}
$lfound = setLocale(LC_TIME, $l);
if ($lfound === false) {
	$err=0; $out=array();
	exec('locale -a | grep -i '. qsa('^'.$lang.'_') .' 2>>/dev/null', $out, $err);
	if ($err != 0)
		gs_log( GS_LOG_NOTICE, 'Failed to find locales on your system' );
	else {
		$lfound = setLocale(LC_TIME, $out);
		if ($lfound === false) {
			gs_log( GS_LOG_NOTICE, 'Your system does not have any locales like "'. $lang .'_*"' );
		} else {
			gs_log( GS_LOG_NOTICE, 'Using locale "'. $lfound .'" as a fallback' );
		}
	}
}
$wdays = array('mo'=>'Mon', 'tu'=>'Tue', 'we'=>'Wed', 'th'=>'Thu', 'fr'=>'Fri', 'sa'=>'Sat', 'su'=>'Sun');
$wdaysl = array();
foreach ($wdays as $col => $wdca)
	$wdaysl[$col] = mb_subStr(strFTime('%a', strToTime('last '.$wdca)),0,1);
unset($wdays);
setLocale(LC_TIME, array($oldLocale, 'C'));



$sudo_url = (@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
	? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);

?>

<p class="text"><small><?php echo __('Diese W&auml;hlregeln f&uuml;r ausgehende Routen werden von oben nach unten abgearbeitet bis eine Regel zutrifft. Dabei wird die gew&auml;hlte Telefonnummer in der (entsprechend Ihren Einstellungen) kanonisierten <b>nationalen</b> Form oder &quot;wie gew&auml;hlt&quot; mit dem Muster verglichen.'); ?></small></p>




<form method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<input type="hidden" name="action" value="save" />

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th><?php echo __('Aktiv'); ?></th>
	<th><?php echo __('Muster'); ?><sup>[1]</sup></th>
	<th><?php echo __('Wochentage'); ?></th>
	<th><?php echo __('Uhrzeit'); ?></th>
	<th><?php echo __('Benutzer-Gr.'); ?></th>
	<th><?php echo __('Gateway / Fallback'); ?></th>
	<th><?php echo __('Pr&auml;fix'); ?> <sup>[2]</sup></th>
	<th><?php echo __('Reihenfolge'); ?></th>
</tr>
</thead>
<tbody>

<?php
$rs = $DB->execute(
'SELECT `id`, `title`, `type`
FROM `gate_grps`
ORDER BY `title`'
);
$gate_grps = array();
while ($r = $rs->fetchRow()) {
	$gate_grps[(int)$r['id']] = array(
		'title'      => $r['title'],
		'title_html' => htmlEnt($r['title']),
		'type'       => $r['type']
	);
}

$user_groups = gs_groups_get();
if (isGsError($user_groups)) {
	$user_groups = false;
}
if (! is_array($user_groups)) $user_groups = array();

$rs = $DB->execute(
'SELECT
	`id`, `active`, `pattern`,
	`d_mo`, `d_tu`, `d_we`, `d_th`, `d_fr`, `d_sa`, `d_su`, `h_from`, `h_to`,
	`user_grp_id` `ug_id`,
	`gw_grp_id_1` `gg1`, `gw_grp_id_2` `gg2`, `gw_grp_id_3` `gg3`, `lcrprfx`, `descr`
FROM `routes`
ORDER BY `ord`'
);
$i=0;
while ($route = $rs->fetchRow()) {
	
	echo '<tr class="', (($i%2) ? 'even':'odd'), '">', "\n";
	echo '<td colspan="7" style="height:5px; border-top:1px solid #000; padding:0;"></td>';
	echo '</tr>', "\n";
	
	$id = $route['id'];
	echo '<tr class="', (($i%2) ? 'even':'odd'), '">', "\n";
	
	echo '<td>';
	echo '<input type="checkbox" name="r_',$id,'_active" value="1" ', ($route['active'] ? 'checked="checked" ' : ''), '/>';
	echo '</td>', "\n";
	
	echo '<td>';
	echo '<input type="text" name="r_',$id,'_pattern" value="', htmlEnt($route['pattern']), '" size="15" maxlength="30" class="pre" style="font-weight:bold;" />';
	echo '</td>', "\n";
	
	echo '<td style="padding-bottom:0;">',"\n";
	echo '<table cellspacing="0" class="tinytbl">',"\n";
	echo '<tbody>', "\n";
	echo '<tr>', "\n";
	foreach ($wdaysl as $col => $v) {
		echo '<td class="c"><input type="checkbox" name="r_',$id,'_d_',$col,'" id="ipt-r_',$id,'_d_',$col,'" value="1" ', ($route['d_'.$col] ? 'checked="checked" ' : ''), '/></td>';
	}
	echo '</tr>', "\n";
	echo '<tr>', "\n";
	foreach ($wdaysl as $col => $v) {
		echo '<td class="c"><label for="ipt-r_',$id,'_d_',$col,'">', $v, '</label></td>';
	}
	echo '</tr>', "\n";
	echo '</tbody>', "\n";
	echo '</table>', "\n";
	echo '</td>', "\n";
	
	echo '<td>';
	$tmp = explode(':', $route['h_from']);
	$hf = (int)lTrim(@$tmp[0], '0-');
	if     ($hf <  0) $hf =  0;
	elseif ($hf > 23) $hf = 23;
	$hf = str_pad($hf, 2, '0', STR_PAD_LEFT);
	$mf = (int)lTrim(@$tmp[1], '0-');
	if     ($mf <  0) $mf =  0;
	elseif ($mf > 59) $mf = 59;
	$mf = str_pad($mf, 2, '0', STR_PAD_LEFT);
	echo '<span class="nobr">';
	echo '<input type="text" name="r_',$id,'_h_from_h" value="', $hf, '" size="2" maxlength="2" class="r" />:';
	echo '<input type="text" name="r_',$id,'_h_from_m" value="', $mf, '" size="2" maxlength="2" class="r" /> -';
	echo '</span> ', "\n";
	$tmp = explode(':', $route['h_to']);
	$ht = (int)lTrim(@$tmp[0], '0-');
	if     ($ht <  0) $ht =  0;
	elseif ($ht > 24) $ht = 24;
	$ht = str_pad($ht, 2, '0', STR_PAD_LEFT);
	$mt = (int)lTrim(@$tmp[1], '0-');
	if     ($mt <  0) $mt =  0;
	elseif ($mt > 59) $mt = 59;
	$mt = str_pad($mt, 2, '0', STR_PAD_LEFT);
	if ($ht.':'.$mt < $hf.':'.$mf) {
		$ht = $hf;
		$hm = $mf;
	}
	echo '<span class="nobr">';
	echo '<input type="text" name="r_',$id,'_h_to_h" value="', $ht, '" size="2" maxlength="2" class="r" />:';
	echo '<input type="text" name="r_',$id,'_h_to_m" value="', $mt, '" size="2" maxlength="2" class="r" />';
	echo '</span>';
	echo '</td>', "\n";
	
	echo '<td rowspan="2">';
	echo '<select name="r_',$id,'_ugrpid">', "\n";
	$is_root = true;
	$root_level = 0;
	foreach ($user_groups as $node) {
		if ($is_root) {
			$root_level = $node['__mptt_level'];
			$node['id'] = 0;
		}
		echo '<option value="', $node['id'],'"',($route['ug_id'] == $node['id'] ? ' selected="selected"' : ''),'>';
		echo @str_repeat('&nbsp;&nbsp;&nbsp;', $node['__mptt_level']-$root_level);
		if ($is_root) {
			echo '[', htmlEnt(__('alle')) ,']';
			$is_root = false;
		} else {
			echo htmlEnt($node['name']);
		}
		echo '</option>' ,"\n";
	}
	echo '</select><br />', "\n";
	echo '</td>', "\n";
	
	echo '<td rowspan="2">';
	$gw_grp_idxs = array(1,2,3);
	foreach ($gw_grp_idxs as $gw_grp_idx) {
		echo '<select name="r_',$id,'_ggrpid',$gw_grp_idx,'">', "\n";
		$route_ggrp_id = $route['gg'.$gw_grp_idx];
		echo '<option value=""', ($route_ggrp_id == 0 || ! array_key_exists($route_ggrp_id, $gate_grps) ? ' selected="selected"' : ''), '>', '-', '</option>', "\n";
		foreach ($gate_grps as $ggid => $gg) {
			echo '<option value="', $ggid, '"', ($ggid == $route_ggrp_id ? ' selected="selected"' : ''), '>', $gg['title_html'], '</option>', "\n";
		}
		echo '</select><br />', "\n";
	}
	echo '</td>', "\n";
	
	echo '<td rowspan="2">';
	echo '<input type="text" name="r_',$id,'_lcrprfx" value="', htmlEnt($route['lcrprfx']), '" size="6" maxlength="6" />';
	echo '</td>', "\n";
	
	echo '<td rowspan="2" class="r transp">';
	if ($i > 0)
		echo '<a href="', gs_url($SECTION, $MODULE, null, 'action=move-up&amp;id='.$route['id']), '"><img alt="&uarr;" src="', GS_URL_PATH, 'img/move_up.gif" /></a>';
	else
		echo '<img alt="&uarr;" src="', GS_URL_PATH, 'img/move_up_d.gif" />';
	if ($i < $rs->numRows()-1)
		echo '&thinsp;<a href="', gs_url($SECTION, $MODULE, null, 'action=move-down&amp;id='.$route['id']), '"><img alt="&darr;" src="', GS_URL_PATH, 'img/move_down.gif" /></a>';
	else
		echo '&thinsp;<img alt="&darr;" src="', GS_URL_PATH, 'img/move_down_d.gif" />';
	echo ' &nbsp; <a href="', gs_url($SECTION, $MODULE, null, 'action=del&amp;id='.$route['id']), '"><img alt="-;" src="', GS_URL_PATH, 'img/minus.gif" /></a>';
	echo '</td>', "\n";
	
	echo '</tr>', "\n";
	
	
	echo '<tr class="', (($i%2) ? 'even':'odd'), '">', "\n";
	
	echo '<td colspan="2" class="r"><label for="ipt-r_',$id,'_descr">', __('Beschr.:'), '</label></td>';
	
	echo '<td colspan="2">';
	echo '<input type="text" name="r_',$id,'_descr" id="ipt-r_',$id,'_descr" value="', htmlEnt(trim($route['descr'])), '" size="45" maxlength="60" style="width:97%;" />';
	echo '</td>', "\n";
	
	echo '</tr>', "\n";
	++$i;
}


echo '<tr>', "\n";
echo '<td colspan="6" class="transp">&nbsp;</td>', "\n";
echo '<td class="r transp">';
echo '<input type="submit" value="', __('Speichern'), '" />';
echo '</td>', "\n";
echo '</tr>', "\n";


$id = 0;
echo '<tr class="', ((++$i % 2 == 0) ? 'even':'odd'), '">', "\n";

echo '<td>';
echo '<input type="checkbox" name="r_',$id,'_active" value="1" checked="checked" />';
echo '</td>', "\n";

echo '<td>';
echo '<input type="text" name="r_',$id,'_pattern" value="" size="15" maxlength="30" class="pre" style="font-weight:bold;" />';
echo '</td>', "\n";

echo '<td style="padding-bottom:0;">',"\n";
echo '<table cellspacing="0" class="tinytbl">',"\n";
echo '<tbody>', "\n";
echo '<tr>', "\n";
foreach ($wdaysl as $col => $v) {
	echo '<td class="c"><input type="checkbox" name="r_',$id,'_d_',$col,'" id="ipt-r_',$id,'_d_',$col,'" value="1" checked="checked" /></td>';
}
echo '</tr>', "\n";
echo '<tr>', "\n";
foreach ($wdaysl as $col => $v) {
	echo '<td class="c"><label for="ipt-r_',$id,'_d_',$col,'">', $v, '</label></td>';
}
echo '</tr>', "\n";
echo '</tbody>', "\n";
echo '</table>', "\n";
echo '</td>', "\n";

echo '<td>';
echo '<span class="nobr">';
echo '<input type="text" name="r_',$id,'_h_from_h" value="00" size="2" maxlength="2" class="r" />:';
echo '<input type="text" name="r_',$id,'_h_from_m" value="00" size="2" maxlength="2" class="r" /> -';
echo '</span> ', "\n";
echo '<span class="nobr">';
echo '<input type="text" name="r_',$id,'_h_to_h" value="24" size="2" maxlength="2" class="r" />:';
echo '<input type="text" name="r_',$id,'_h_to_m" value="00" size="2" maxlength="2" class="r" />';
echo '</span>';
echo '</td>', "\n";

echo '<td rowspan="2">';
echo '<select name="r_',$id,'_ugrpid">', "\n";
$is_root = true;
$root_level = 0;
foreach ($user_groups as $node) {
	if ($is_root) {
		$root_level = $node['__mptt_level'];
		$node['id'] = 0;
	}
	echo '<option value="', $node['id'],'"',($route['ug_id'] == $node['id'] ? ' selected="selected"' : ''),'>';
	echo @str_repeat('&nbsp;&nbsp;&nbsp;', $node['__mptt_level']-$root_level);
	if ($is_root) {
		echo '[', htmlEnt(__('alle')) ,']';
		$is_root = false;
	} else {
		echo htmlEnt($node['name']);
	}
	echo '</option>' ,"\n";
}
echo '</select><br />', "\n";
echo '</td>', "\n";
echo '</td>', "\n";

echo '<td rowspan="2">';
foreach ($gw_grp_idxs as $gw_grp_idx) {
	echo '<select name="r_',$id,'_ggrpid',$gw_grp_idx,'">', "\n";
	$route_ggrp_id = $route['gg'.$gw_grp_idx];
	echo '<option value="" selected="selected">-</option>', "\n";
	foreach ($gate_grps as $ggid => $gg) {
		echo '<option value="', $ggid, '">', $gg['title_html'], '</option>', "\n";
	}
	echo '</select><br />', "\n";
}
echo '</td>', "\n";

echo '<td rowspan="2">';
echo '<input type="text" name="r_',$id,'_lcrprfx" value="" size="6" maxlength="6" />';
echo '</td>', "\n";

echo '<td rowspan="2" class="r transp">&nbsp;';
echo '</td>', "\n";

echo '</tr>', "\n";


echo '<tr class="', (($i % 2 == 0) ? 'even':'odd'), '">', "\n";

echo '<td colspan="2" class="r"><label for="ipt-r_',$id,'_descr">', __('Beschr.:'), '</label></td>';

echo '<td colspan="2">';
echo '<input type="text" name="r_',$id,'_descr" id="ipt-r_',$id,'_descr" value="" size="45" maxlength="60" style="width:97%;" />';
echo '</td>', "\n";

echo '</tr>', "\n";

?>

</tbody>
</table>
</form>

<br />
<p class="text"><small><sup>[1]</sup> <?php
/*
echo __('PCRE-Syntax ohne <code>/</code> als Begrenzer, d.h. <code>^</code> f&uuml;r den Anfang, <code>$</code> f&uuml;r das Ende, z.B. <code>[5-8]</code> oder <code>[57]</code> f&uuml;r Ziffern-Bereiche, <code>+</code> f&uuml;r eine Wiederholung des vorangehenden Zeichens (1 oder mehr) oder <code>*</code> f&uuml;r 0 oder mehr. Zus&auml;tzlich m&ouml;glich: <code>x</code> f&uuml;r <code>[0-9]</code>, <code>z</code> f&uuml;r <code>[1-9]</code>');
*/
echo __('PCRE-Syntax (&quot;Perl Compatible Regular Expression&quot;) ohne <code>/</code> als Begrenzer, d.h. <code>^</code> f&uuml;r den Anfang, <code>$</code> f&uuml;r das Ende, z.B. <code>[5-8]</code> oder <code>[57]</code> f&uuml;r Ziffern-Bereiche, <code>+</code> f&uuml;r eine Wiederholung des vorangehenden Zeichens (1 oder mehr) oder <code>*</code> f&uuml;r 0 oder mehr.');
?></small></p>

<p class="text"><small><sup>[2]</sup> <?php
echo __('Pr&auml;fix f&uuml;r LCR (Least Cost Routing) in der Form 010<i>xx</i>. Gilt nur f&uuml;r ISDN, nicht f&uuml;r SIP.');
?></small></p>
