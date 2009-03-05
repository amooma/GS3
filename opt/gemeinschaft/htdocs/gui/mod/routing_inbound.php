<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision:2902 $
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


echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


$action = @$_REQUEST['action'];
$ggid = (int)@$_REQUEST['ggid'];
$id     = (int)@$_REQUEST['id'];
if ($id < 1) $id = 0;

# get gateway groups from DB
#
$rs = $DB->execute( 'SELECT `id`, `name`, `title` FROM `gate_grps` ORDER BY `title`' );
$ggs = array();
while ($r = $rs->fetchRow())
	$ggs[] = $r;




#####################################################################
if ($action == 'move-up' || $action == 'move-down') {
	
	if ($id > 0) {
		gs_db_start_trans($DB);
		$rs = $DB->execute( 'SELECT `id` FROM `routes_in` WHERE `gate_grp_id`='. $ggid .' ORDER BY `ord`' );
		$ord = 4;
		while ($r = $rs->fetchRow()) {
			if ($r['id'] != $id)
				$DB->execute( 'UPDATE `routes_in` SET `ord`='. $ord .' WHERE `id`='. (int)$r['id'] );
			else
				$DB->execute( 'UPDATE `routes_in` SET `ord`='. ($ord + ($action=='move-up' ? -3 : 3)) .' WHERE `id`='. (int)$r['id'] );
			$ord += 2;
		}
		gs_db_commit_trans($DB);
		
		@$DB->execute( 'OPTIMIZE TABLE `routes_in`' );
		@$DB->execute( 'ANALYZE TABLE `routes_in`' );
	}
	
	$action = 'edit';
}
#####################################################################




#####################################################################
elseif ($action == 'save') {
	
	$rs = $DB->execute( 'SELECT `id` FROM `routes_in` WHERE `gate_grp_id`='. $ggid );
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
		
		if ($dbid<1) {
			gs_db_start_trans($DB);
			$ord = (int)$DB->executeGetOne( 'SELECT MAX(`ord`) FROM `routes_in`' ) + 1;
		}
		$query =
($dbid>0 ? 'UPDATE' : 'INSERT INTO') .' `routes_in` SET
	'. ($dbid>0 ? '' : '`id`=NULL,') .'
	'. ($dbid>0 ? '' : '`gate_grp_id`='.$ggid.',' ) .'
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
	`to_ext`=\''. $DB->escape(trim(@$_REQUEST['r_'.$dbid.'_to_ext'])) .'\',
	`descr`=\''. $DB->escape(trim(@$_REQUEST['r_'.$dbid.'_descr'])) .'\'
'. ($dbid>0 ? 'WHERE `id`='. (int)$dbid : '')
		;
		$ok = $DB->execute($query);
		if ($dbid<1) {
			gs_db_commit_trans($DB);
		}
	}
	
	@$DB->execute( 'OPTIMIZE TABLE `routes_in`' );
	@$DB->execute( 'ANALYZE TABLE `routes_in`' );
	
	$action = 'edit';
}
#####################################################################




#####################################################################
elseif ($action == 'del') {
	
	if ($id > 0) {
		$DB->execute( 'DELETE FROM `routes_in` WHERE `gate_grp_id`='. $ggid .' AND `id`='. $id );
		
		@$DB->execute( 'OPTIMIZE TABLE `routes_in`' );
		@$DB->execute( 'ANALYZE TABLE `routes_in`' );
	}
	
	$action = 'edit';
}
#####################################################################





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

?>

<form method="get" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<input type="hidden" name="action" value="edit" />
<?php
echo __('Gateway-Gruppe'),': ';
echo '<select name="ggid" onchange="this.form.submit();" style="font-weight:bold;">',"\n";
foreach ($ggs as $gg) {
	echo '<option value="', $gg['id'] ,'"', ($gg['id']==$ggid ? ' selected="selected"' : '') ,'>', htmlEnt($gg['title']) ,' (', htmlEnt($gg['name']) ,')</option>',"\n";
}
echo '</select> ',"\n";
echo '<input type="submit" value="', __('anzeigen') ,'" />',"\n";
?>
</form>

<hr size="1" />

<?php

#####################################################################
if ($action === 'edit') {
?>

<p class="text"><small><?php echo __('Diese W&auml;hlregeln f&uuml;r eingehende Routen werden von oben nach unten abgearbeitet bis eine Regel zutrifft. Dabei wird die gew&auml;hlte Durchwahl mit dem Muster verglichen.'); ?></small></p>



<form method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<input type="hidden" name="ggid" value="<?php echo $ggid; ?>" />
<input type="hidden" name="action" value="save" />

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th><?php echo __('Aktiv'); ?></th>
	<th><?php echo __('Wochentage'); ?></th>
	<th><?php echo __('Uhrzeit'); ?></th>
	<th><?php echo __('Muster'); ?><sup>[1]</sup></th>
	<th><?php echo __('Ziel'); ?></th>
	<th style="width:20em;"><?php echo __('Beschreibung'); ?></th>
	<th><?php echo __('Reihenfolge'); ?></th>
</tr>
</thead>
<tbody>

<?php
	
	$rs = $DB->execute(
'SELECT
	`id`, `active`, `pattern`,
	`d_mo`, `d_tu`, `d_we`, `d_th`, `d_fr`, `d_sa`, `d_su`, `h_from`, `h_to`,
	`to_ext`, `descr`
FROM `routes_in`
WHERE `gate_grp_id`='. $ggid .'
ORDER BY `ord`'
	);
	$i=0;
	while ($route = $rs->fetchRow()) {
		
		$id = $route['id'];
		echo '<tr class="', (($i%2) ? 'even':'odd'), '">', "\n";
		
		echo '<td>';
		echo '<input type="checkbox" name="r_',$id,'_active" value="1" ', ($route['active'] ? 'checked="checked" ' : ''), '/>';
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
		
		echo '<td>';
		echo '<input type="text" name="r_',$id,'_pattern" value="', htmlEnt($route['pattern']), '" size="10" maxlength="30" class="pre" style="font-weight:bold;" />';
		echo '</td>', "\n";
		
		echo '<td>';
		echo '<input type="text" name="r_',$id,'_to_ext" value="', htmlEnt($route['to_ext']), '" size="8" maxlength="10" class="pre" style="font-weight:bold;" />';
		echo '</td>', "\n";
		
		echo '<td>';
		echo '<input type="text" name="r_',$id,'_descr" id="ipt-r_',$id,'_descr" value="', htmlEnt(trim($route['descr'])), '" size="25" maxlength="60" style="width:97%;" />';
		echo '</td>', "\n";
		
		echo '<td>';
		if ($i > 0)
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'ggid='.$ggid .'&amp;action=move-up&amp;id='.$route['id']), '"><img alt="&uarr;" src="', GS_URL_PATH, 'img/move_up.gif" /></a>';
		else
			echo '<img alt="&uarr;" src="', GS_URL_PATH, 'img/move_up_d.gif" />';
		if ($i < $rs->numRows()-1)
			echo '&thinsp;<a href="', gs_url($SECTION, $MODULE, null, 'ggid='.$ggid .'&amp;action=move-down&amp;id='.$route['id']), '"><img alt="&darr;" src="', GS_URL_PATH, 'img/move_down.gif" /></a>';
		else
			echo '&thinsp;<img alt="&darr;" src="', GS_URL_PATH, 'img/move_down_d.gif" />';
		echo ' &nbsp; <a href="', gs_url($SECTION, $MODULE, null, 'ggid='.$ggid .'&amp;action=del&amp;id='.$route['id']), '"><img alt="-;" src="', GS_URL_PATH, 'img/minus.gif" /></a>';
		echo '</td>', "\n";
		
		echo '</tr>', "\n";
		++$i;
	}
	
	
	echo '<tr>', "\n";
	echo '<td colspan="6" class="transp">&nbsp;</td>', "\n";
	echo '<td class="transp">';
	echo '<input type="submit" value="', __('Speichern'), '" />';
	echo '</td>', "\n";
	echo '</tr>', "\n";
	
	
	$id = 0;
	echo '<tr class="', ($i%2 ? 'even':'odd'), '">', "\n";
	
	echo '<td>';
	echo '<input type="checkbox" name="r_',$id,'_active" value="1" />';
	echo '</td>', "\n";
	
	echo '<td>',"\n";
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
	
	echo '<td>';
	echo '<input type="text" name="r_',$id,'_pattern" value="" size="10" maxlength="30" class="pre" style="font-weight:bold;" />';
	echo '</td>', "\n";
	
	echo '<td>';
	echo '<input type="text" name="r_',$id,'_to_ext" value="', htmlEnt($route['to_ext']), '" size="8" maxlength="10" class="pre" style="font-weight:bold;" />';
	echo '</td>', "\n";
	
	echo '<td>';
	echo '<input type="text" name="r_',$id,'_descr" id="ipt-r_',$id,'_descr" value="" size="25" maxlength="60" style="width:97%;" />';
	echo '</td>', "\n";
	
	echo '<td>&nbsp;';
	echo '</td>', "\n";
	
	echo '</tr>', "\n";
	++$i;
	
	
	
	echo '<tr>', "\n";
	echo '<td colspan="7" class="transp">&nbsp;</td>', "\n";
	echo '</tr>', "\n";
	
	
	
	echo '<tr class="', ($i%2 ? 'even':'odd'), '">', "\n";
	
	echo '<td class="transp">';
	echo '<input type="checkbox" value="1" checked="checked" disabled="disabled" />';
	echo '</td>', "\n";
	
	echo '<td class="transp" style="padding-bottom:0;">',"\n";
	echo '<table cellspacing="0" class="tinytbl" style="color:#888;">',"\n";
	echo '<tbody>', "\n";
	echo '<tr>', "\n";
	foreach ($wdaysl as $col => $v) {
		echo '<td class="c transp"><input type="checkbox" value="1" checked="checked" disabled="disabled" /></td>';
	}
	echo '</tr>', "\n";
	echo '<tr>', "\n";
	foreach ($wdaysl as $col => $v) {
		echo '<td class="c transp"><label>', $v, '</label></td>';
	}
	echo '</tr>', "\n";
	echo '</tbody>', "\n";
	echo '</table>', "\n";
	echo '</td>', "\n";
	
	echo '<td class="transp">';
	echo '<span class="nobr">';
	echo '<input type="text" value="00" size="2" maxlength="2" class="r" disabled="disabled" />:';
	echo '<input type="text" value="00" size="2" maxlength="2" class="r" disabled="disabled" /> -';
	echo '</span> ', "\n";
	echo '<span class="nobr">';
	echo '<input type="text" value="24" size="2" maxlength="2" class="r" disabled="disabled" />:';
	echo '<input type="text" value="00" size="2" maxlength="2" class="r" disabled="disabled" />';
	echo '</span>';
	echo '</td>', "\n";
	
	echo '<td class="transp">';
	echo '<input type="text" value="^(.*)" size="10" maxlength="30" class="pre" disabled="disabled" />';
	echo '</td>', "\n";
	
	echo '<td class="transp">';
	echo '<input type="text" value="$1" size="8" maxlength="10" class="pre" disabled="disabled" />';
	echo '</td>', "\n";
	
	echo '<td class="transp">';
	echo '<input type="text" value="', __('1:1 DID -&gt; Extension') ,'" size="25" maxlength="60" style="width:97%;" disabled="disabled" />';
	echo '</td>', "\n";
	
	echo '<td class="transp">&nbsp;';
	echo '</td>', "\n";
	
	echo '</tr>', "\n";
	++$i;
	
?>

</tbody>
</table>
</form>

<br />
<br />

<p class="text"><small><sup>[1]</sup> <?php
	echo __('PCRE-Syntax (&quot;Perl Compatible Regular Expression&quot;) ohne <code>/</code> als Begrenzer, d.h. <code>^</code> f&uuml;r den Anfang, <code>$</code> f&uuml;r das Ende, z.B. <code>[5-8]</code> oder <code>[57]</code> f&uuml;r Ziffern-Bereiche, <code>+</code> f&uuml;r eine Wiederholung des vorangehenden Zeichens (1 oder mehr) oder <code>*</code> f&uuml;r 0 oder mehr.');
?></small></p>


<?php
}
#####################################################################
?>

