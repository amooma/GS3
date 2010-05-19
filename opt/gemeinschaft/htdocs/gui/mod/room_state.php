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

require_once(GS_DIR . 'inc/gs-fns/gs_room_state_fns.php');

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
$state    = (int)trim(@$_REQUEST['state'   ]);
$delete   = trim(@$_REQUEST['delete'  ]);


$states = array( __( 'unbekannt' ), __( 'nicht gereinigt' ), __( 'gereinigt' ), __( 'gereinigt und gepr&uuml;ft' ) );

if ( $delete ) {
	$ret = delete_room_state( $delete );
	if (isGsError( $ret )) echo $ret->getMsg();	
}


if ( $save ) {
	
	if ( ! ctype_digit ( $save ) ) {
		echo 'Only numeric extensions are allowed<br>' . "\n";
	}
	else {
		$ret = set_room_state( $save, $state );
		if (isGsError( $ret )) echo $ret->getMsg();
	}
	
}

#####################################################################
#  show room state calls {
#####################################################################
	
	$sql_query =
'SELECT `extension`, `state`
FROM `room_state`
ORDER BY `extension`';

	
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
	<th style="width:80px;"><?php echo __('Raum'); ?></th>
	<th style="width:120px;"><?php echo __('Status'); ?></th>
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
			echo '<td class="r">', htmlEnt($r['extension']) ,'</td>',"\n";
			if ( $edit == $r['extension'] ) {
				echo '<td><select name="state">';
				
				for ( $i = 1 ; $i < count( $states ) ; $i++ ) {
					echo '<option value="' , $i  , '" ', ($r['state'] == $i ? ' selected="selected"' : ''), '>', $states[$i] ,'</option>';
				}
				echo '</select></td>', "\n";

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
				echo '<td class="c">',  $states[$r['state']] , '</td>',"\n";
				echo '<td>',"\n";
				echo '<a href="', gs_url($SECTION, $MODULE, null, 'edit='.$r['extension']) ,'" title="', __('bearbeiten'), '"><img alt="', __('bearbeiten'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
				echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='.$r['extension'] ) ,'" title="', __('l&ouml;schen'), '"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
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
			<input type="text" name="save" value="" size="10" maxlength="10" />
		</td>
		<td class="c">
		<span class="nobr">
		<select name="state">
		<?php

		for ( $i = 1 ; $i < count( $states) ; $i++ ) {
			echo '<option value="' , $i  , '" ', ( $i == 1 ? ' selected="selected"' : ''), '>', $states[$i] ,'</option>';
		}
		?>
		</select>
		</span>
		</td>
		<td>
			<button type="submit" name="<?php echo __('Zimmerstatus setzen'); ?>" class="plain">
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
#  show soom state }
#####################################################################
?>