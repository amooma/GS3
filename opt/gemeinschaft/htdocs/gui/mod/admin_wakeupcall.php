<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* 
* Author: Andreas Neugebauer <neugebauer@loca.net> - LocaNet oHG
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

require_once(GS_DIR . 'inc/gs-fns/gs_wake_up_call_fns.php');

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";

$edit     = (int)trim(@$_REQUEST['edit'    ]);
$save     = trim(@$_REQUEST['save'    ]);
$title    =      trim(@$_REQUEST['title'   ]);
$add_hour    = (int)trim(@$_REQUEST['add_hour'   ]);
$add_minute    = (int)trim(@$_REQUEST['add_minute'   ]);
$delete   = (int)trim(@$_REQUEST['delete'  ]);

if ( $delete ) {
	$ret = delete_alert_by_target( $delete );
	if (isGsError( $ret )) echo $ret->getMsg();	
}


if ( $save ) {
	
	
	$targets = explode( ",", $save );
	for( $i = 0; $i < count( $targets ); $i++ ) {
		
		$targets[$i] = trim( $targets[$i] );
		
		if ( ! ctype_digit ( $targets[$i] ) ) {
			echo 'Only numeric targets are allowed<br>' . "\n";
			unset( $targets );
			break;
		}
	
	
	}
	if ( is_array( $targets ) ) {
		for( $i = 0; $i < count( $targets ); $i++ ) {
		
			$ret = set_alert_time_by_target( $targets[$i], $add_hour, $add_minute );
			if (isGsError( $ret )) echo $ret->getMsg();
			
		}
	
	}
}

#####################################################################
#  show wakeup calls {
#####################################################################
	
	$sql_query =
'SELECT `target`, `hour`, `minute`
FROM `wakeup_calls`
ORDER BY `target`';

	
	$rs = $DB->execute($sql_query);
	
	
?>

<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php 
echo gs_form_hidden($SECTION, $MODULE);
if ($edit > 0) {
	echo '<input type="hidden" name="save" value="', $edit , '" />', "\n";
}
?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:120px;"><?php echo __('Rufnummer'); ?></th>
	<th style="width:120px;"><?php echo __('Weckzeit'); ?></th>
	<th style="width:80px;"></th>
</tr>
</thead>
<tbody>

<?php
	$res = array();
	if (@$rs) {
		$i = 0;
		
		while ($rt = $rs->fetchRow()) {
		
			$res[] = $rt;	
		
		}
		
		asort( $res);
		
		foreach( $res as $r ) {		

			echo '<tr class="', ((++$i % 2) ? 'odd':'even'), '">', "\n";
			echo '<td class="r">', htmlEnt($r['target']) ,'</td>',"\n";
			if ( $edit == $r['target'] ) {
				echo '<td class="c">';

	$hf = (int)lTrim(@$r['hour'], '0-');
	if     ($hf <  0) $hf =  0;
	elseif ($hf > 23) $hf = 23;
	$hf = str_pad($hf, 2, '0', STR_PAD_LEFT);
	$mf = (int)lTrim(@$r['minute'], '0-');
	if     ($mf <  0) $mf =  0;
	elseif ($mf > 59) $mf = 59;
	$mf = str_pad($mf, 2, '0', STR_PAD_LEFT);
	echo '<span class="nobr">';
	echo '<input type="text" name="add_hour" value="', $hf, '" size="2" maxlength="2" class="r" />:';
	echo '<input type="text" name="add_minute" value="', $mf, '" size="2" maxlength="2" class="r" />';
	echo '</span> ', "\n";


				echo '</td>', "\n";


				echo '<td>',"\n";

				echo '<button type="submit" title="', __('Speichern'), '" class="plain">';
				echo '<img alt="', __('Speichern') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/filesave.png" />';
				echo '</button>' ,"\n";
				
				echo '&nbsp;',"\n";
				
				echo '<a href="', gs_url($SECTION, $MODULE) ,'"><button type="button" title="', __('Abbrechen'), '" class="plain">';
				echo '<img alt="', __('Abbrechen') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/cancel.png" />';
				echo '</button></a>' ,"\n";
				
				echo '</td>',"\n";
			} else {
				echo '<td class="c">', htmlEnt( str_pad( $r['hour'], 2, '0', STR_PAD_LEFT ) ),':',
					htmlEnt( str_pad( $r['minute'], 2, '0', STR_PAD_LEFT ) ) ,'</td>',"\n";
				echo '<td>',"\n";
				echo '<a href="', gs_url($SECTION, $MODULE, null, 'edit='.$r['target']) ,'" title="', __('bearbeiten'), '"><img alt="', __('bearbeiten'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
				echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='.$r['target'] ) ,'" title="', __('l&ouml;schen'), '"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
				echo '</td>',"\n";
			}
			echo '</tr>',"\n";
		}
	}
?>

<?php
	if (! $edit) {
		echo '<tr class="', ((++$i % 2) ? 'odd':'even'), '">', "\n";
?>
		<td class="r">
			<input type="text" name="save" value="" size="20" maxlength="10" />
		</td>
		<td class="c">
		<span class="nobr">
		<input type="text" name="add_hour" value="00" size="2" maxlength="2" class="r" />:<input type="text" name="add_minute" value="00" size="2" maxlength="2" class="r" />
		</span>
		</td>
		<td>
			<button type="submit" name="<?php echo __('Weckruf anlegen'); ?>" class="plain">
				<img alt="<?php echo __('Speichern'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
			</button>
		</td>
<?php
		echo '</tr>',"\n";
	}
?>

</tbody>
</table>
</form>

<?php

#####################################################################
#  show wakeup_calls }
#####################################################################
?>