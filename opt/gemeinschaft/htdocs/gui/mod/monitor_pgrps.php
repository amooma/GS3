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


include_once( GS_DIR .'inc/extension-state.php' );
include_once( GS_DIR .'inc/gs-lib.php' );


/*
//if (! is_array( @$_SESSION['sudo_user']['keys'] )
//||  @$_SESSION['sudo_user']['keys']['t'] < time()-60*1 )
//{
	$_SESSION['sudo_user']['keys']['snom'] = gs_keys_snom_get( @$_SESSION['sudo_user']['name'] );
	//$_SESSION['sudo_user']['keys']['t'] = time();
//}
if (! is_array( $_SESSION['sudo_user']['keys']['snom'] )) {
	$_SESSION['sudo_user']['keys']['snom'] = array();
}
*/


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
		AST_MGR_EXT_RINGINUSE => array('v'=>__('anklopfen'), 's'=>'yellow'),
		AST_MGR_EXT_ONHOLD    => array('v'=>__('halten'   ), 's'=>'red'   )
	);
	return array_key_exists($extstate, $states) ? $states[$extstate] : null;
}


$GS_INSTALLATION_TYPE_SINGLE = gs_get_conf('GS_INSTALLATION_TYPE_SINGLE');


# get the pickup groups for the current user
#
$rs_groups = $DB->execute(
'SELECT `p`.`id`, `p`.`title`
FROM
	`pickupgroups_users` `pu` JOIN
	`pickupgroups` `p` ON (`p`.`id`=`pu`.`group_id`)
WHERE `pu`.`user_id`='. (int)@$_SESSION['sudo_user']['info']['id'] .'
ORDER BY `p`.`id`'
);


if ($rs_groups->numRows()==0) {
	echo __('Sie sind nicht Mitglied einer Pickup-Gruppe.') ,'<br />',"\n";
	return;
} else {
	while ($pgrp = $rs_groups->fetchRow()) {
		
		# get pickup group members from db
		#
		$rs_members = $DB->execute(
'SELECT
	`pu`.`user_id`, `u`.`firstname` `fn`, `u`.`lastname` `ln`,
	`s`.`name` `ext`, `h`.`host`
FROM
	`pickupgroups_users` `pu` LEFT JOIN
	`users` `u` ON (`u`.`id`=`pu`.`user_id`) JOIN
	`ast_sipfriends` `s` ON (`s`.`_user_id`=`u`.`id`) JOIN
	`hosts` `h` ON (`h`.`id`=`u`.`host_id`)
WHERE `pu`.`group_id`='. (int)@$pgrp['id'] .'
ORDER BY `u`.`lastname`, `u`.`firstname`'
		);
		
?>

<h3><?php
$group_title = (trim(@$pgrp['title']) != '' ? trim(@$pgrp['title']) : '');

echo 'Gruppe ', @$pgrp['id'], ($group_title != '' ? (' ('. htmlEnt($group_title) .') ') : ''); ?></h3>
<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:60px;"><?php echo __('Nummer'); ?></th>
	<th style="width:200px;"><?php echo __('Name'); ?></th>
	<th style="width:90px;"><?php echo __('Status'); ?></th>
</tr>
</thead>
<tbody>

<?php

$extinfos = array();
if (@$rs_members) {
	if ($rs_members->numRows() === 0) {
		echo '<tr><td colspan="3"><i>- ', __('keine'), ' -</i></td></tr>';
	} else {
		$i = 0;
		while ($r = $rs_members->fetchRow()) {
			echo '<tr class="', ((++$i % 2 == 0) ? 'even':'odd'), '">';
			
			echo '<td>', htmlEnt($r['ext']), '</td>';
			$sudo_url = (@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
				? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);
			
			echo '<td>', htmlEnt($r['ln']);
			if ($r['fn'] != '') echo ', ', htmlEnt($r['fn']);
			echo '</td>';
			
			if ($GS_INSTALLATION_TYPE_SINGLE)
				$r['host'] = '127.0.0.1';
			$extstate = gs_extstate( $r['host'], $r['ext'] );
			$extinfos[$r['ext']]['info' ] = $r;
			$extinfos[$r['ext']]['state'] = $extstate;
			$extstatev = _extstate2v( $extstate );
			
			if (@$extstatev['s']) {
				$img = '<img alt=" " src="'. GS_URL_PATH;
				switch ($extstatev['s']) {
					case 'green' : $img.= 'crystal-svg/16/act/greenled.png' ; break;
					case 'yellow': $img.= 'crystal-svg/16/act/yellowled.png'; break;
					case 'red'   : $img.= 'crystal-svg/16/act/redled.png'   ; break;
					default      : $img.= 'crystal-svg/16/act/free_icon.png'; break;
				}
				$img.= '" /> ';
			} else
				$img = '<img alt=" " src="'. GS_URL_PATH .'crystal-svg/16/act/free_icon.png" /> ';
			echo '<td>', $img, (@$extstatev['v'] ? $extstatev['v'] : '?'), '</td>';
			
			echo '</tr>', "\n";
		}
	}
}

$grpstate = AST_MGR_EXT_IDLE;
foreach ($extinfos as $ext => $info) {
	if (@$info['info']['ext'] == @$_SESSION['sudo_user']['info']['ext'])
		continue;
	if ($info['state'] & AST_MGR_EXT_RINGING) {
		$grpstate = AST_MGR_EXT_RINGING;
		break;
	}
}
$grpext = '*8'. str_pad(@$pgrp['id'], 5, '0', STR_PAD_LEFT);
$extinfos[$grpext]['state'] = $grpstate;
/*
echo "<pre>";
print_r($extstates);
echo "</pre>";
*/

?>

</tbody>
</table>

<br />

<?php
	}
}
?>


<?php
/*  ###########################################
// not sure what to do with this code

<table cellspacing="1" class="smalltbl">
<thead>
<tr>
	<th style="width:25px;" class="transp">&nbsp;</th>
	<th style="width:25px;" class="transp">&nbsp;</th>
	<th style="width:140px;"><?php echo __('Snom-Tasten'); ?></th>
	<th style="width:25px;" class="transp">&nbsp;</th>
	<th style="width:25px;" class="transp">&nbsp;</th>
</tr>
</thead>
<tbody>

<?php

$keys_snom = @$_SESSION['sudo_user']['keys']['snom'];

if (@count($keys_snom) > 0) {
	$len = 7;
	foreach ($keys_snom as $kname => $kinfo) {
		if (strLen($kinfo['val']) > $len)
			$len = strLen($kinfo['val']);
	}
	$len += 5;
	
	echo "\n";
	$right = 6;
	$left = 0;
	for ($i=0; $i<12; ++$i) {
		$i_even = !($i%2===0);  # :-)
		echo '<tr class="', ($i_even ? 'even':'odd'), '">', "\n";
		
		$knum = ($i_even ? $left : $right);
		$keyv = 'P'. str_replace(' ', '&nbsp;', str_pad($knum+1, 2, ' ', STR_PAD_LEFT));
		$keyinfo = @$keys_snom['f'.$knum];
		if (! is_array($keyinfo)) $keyinfo = array();
		$val = @$keyinfo['val'];
		
		//echo '<td><pre>';
		if (! @is_array($extinfos[$val])) {
			$extstate = gs_extstate_single( $val );
			//$extinfos[$val]['info'] = $r;
			$extinfos[$val]['state'] = $extstate;
		}
		//print_r($extinfos[$val]);
		//echo '</pre></td>';
		
		$state = array_key_exists($val, $extinfos)
			? @$extinfos[$val]['state'] : AST_MGR_EXT_UNKNOWN;
		switch ($state) {
			case AST_MGR_EXT_INUSE:
			case AST_MGR_EXT_BUSY:
			case AST_MGR_EXT_ONHOLD:    $img = 'on'  ; break;
			case AST_MGR_EXT_RINGING:
			case AST_MGR_EXT_RINGINUSE: $img = 'ring'; break;
			case AST_MGR_EXT_IDLE:
			default:                    $img = 'off' ; break;
		}
		
		if (subStr($val,0,2) == '*8') {
			$title = __('Gruppe') .' '. lTrim(subStr($val,2),'0*');
		} elseif (@is_array($extinfos[$val]['info'])) {
			$title = '';
			if (@$extinfos[$val]['info']['fn'] !='')
				$title .= mb_subStr(@$extinfos[$val]['info']['fn'],0,1) .'. ';
			$title .= @$extinfos[$val]['info']['ln'];
		} else {
			$title = $val;
		}
		
		echo '<td class="r transp">';
		echo ($i_even)
			? '<img alt=" " src="'. GS_URL_PATH .'img/snom_fkleft_'.$img.'.gif" />'
			: '&nbsp;';
		echo '</td>', "\n";
		
		echo '<td class="l', ($i_even ? '':' transp'), '">';
		echo ($i_even)
			? $keyv
			: '&nbsp;';
		echo '</td>', "\n";
		
		echo '<td class="', ($i_even ? 'l':'r'), '">', "\n";
		echo htmlEnt($title);
		echo '</td>', "\n";
		
		echo '<td class="r', ($i_even ? ' transp':''), '">';
		echo ($i_even)
			? '&nbsp;'
			: $keyv;
		echo '</td>', "\n";
		
		echo '<td class="l transp">';
		echo ($i_even)
			? '&nbsp;'
			: '<img alt=" " src="'. GS_URL_PATH .'img/snom_fkright_'.$img.'.gif" />';
		echo '</td>', "\n";
		
		echo '</tr>', "\n";
		if ($i_even) ++$left;
		else ++$right;
	}
}

?>

</tbody>
</table>

<?php
*/  ###########################################
?>

<script type="text/javascript">/*<![CDATA[*/
window.setTimeout('document.location.reload();', 9000);
/*]]>*/</script>
