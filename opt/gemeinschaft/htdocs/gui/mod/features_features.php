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
include_once( GS_DIR .'inc/gs-fns/gs_callwaiting_activate.php' );
include_once( GS_DIR .'inc/gs-fns/gs_callwaiting_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_clir_activate.php' );
include_once( GS_DIR .'inc/gs-fns/gs_clir_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_callerid_set.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_callerids_get.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";





if (@$_REQUEST['action']=='save') {
	
	$clir_internal = (@$_REQUEST['clir-internal']=='yes' ? 'yes':'no');
	$clir_external = (@$_REQUEST['clir-external']=='yes' ? 'yes':'no');
	gs_clir_activate( $_SESSION['sudo_user']['name'], 'internal', $clir_internal );
	gs_clir_activate( $_SESSION['sudo_user']['name'], 'external', $clir_external );
	
	$cw = !! @$_REQUEST['callwaiting'];
	# setting this reboots phone, so check if it has really changed
	$cw_old = gs_callwaiting_get( $_SESSION['sudo_user']['name'] );
	if (! isGsError($cw_old)) {
		if ($cw != $cw_old)
			gs_callwaiting_activate( $_SESSION['sudo_user']['name'], $cw );
	}
	if(isset($_REQUEST['callerid_ext'])){
		$callerid_num = $_REQUEST['callerid_ext'];
		
		$ok = gs_user_callerid_set( $_SESSION['sudo_user']['name'], $callerid_num , 'external');
		if (isGsError( $ok )) echo $ok->getMsg();
	}
	if(isset($_REQUEST['callerid_int'])){
		$callerid_num = $_REQUEST['callerid_int'];
		
		$ok = gs_user_callerid_set( $_SESSION['sudo_user']['name'], $callerid_num , 'internal');
		if (isGsError( $ok )) echo $ok->getMsg();
	}
	
}







$clir = gs_clir_get( $_SESSION['sudo_user']['name'] );
if (isGsError($clir)) {
	echo __('Fehler beim Abfragen.'), '<br />', $clir->getMsg();
	die();
}

$callwaiting = gs_callwaiting_get( $_SESSION['sudo_user']['name'] );
if (isGsError($callwaiting)) {
	echo __('Fehler beim Abfragen.'), '<br />', $callwaiting->getMsg();
	die();
}

?>

<form method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<input type="hidden" name="action" value="save" />

<table cellspacing="1">
<thead>
<tr>
	<th colspan="2"><?php echo __('Dienstmerkmale'); ?></th>
	<th class="quickchars">&nbsp;</th>
</tr>
</thead>
<tbody>
<tr>
	<td style="width:170px;"><?php echo __('CLIR nach intern'); ?></td>
	<td style="width:130px;">
		<input type="radio" name="clir-internal" value="yes" id="ipt-clir-internal-yes" <?php if ($clir['internal_restrict']=='yes') echo 'checked="checked" '; ?>/>
			<label for="ipt-clir-internal-yes"><?php echo __('an'); ?></label>
		<input type="radio" name="clir-internal" value="no" id="ipt-clir-internal-no" <?php if ($clir['internal_restrict'] != 'yes') echo 'checked="checked" '; ?>/>
			<label for="ipt-clir-internal-no"><?php echo __('aus'); ?></label>
	</td>
	<td rowspan="2" style="width:200px;">
		<small><?php echo __('Rufnummernunterdr&uuml;ckung. Bei <q>an</q> wird die Nummer unterdr&uuml;ckt<sup>[1]</sup>, bei <q>aus</q> wird sie mitgesendet.'); ?></small>
	</td>
</tr>
<tr>
	<td><?php echo __('CLIR nach extern'); ?></td>
	<td>
		<input type="radio" name="clir-external" value="yes" id="ipt-clir-external-yes" <?php if ($clir['external_restrict']=='yes') echo 'checked="checked" '; ?>/>
			<label for="ipt-clir-external-yes"><?php echo __('an'); ?></label>
		<input type="radio" name="clir-external" value="no" id="ipt-clir-external-no" <?php if ($clir['external_restrict'] != 'yes') echo 'checked="checked" '; ?>/>
			<label for="ipt-clir-external-no"><?php echo __('aus'); ?></label>
	</td>
</tr>
<tr>
	<td><?php echo __('Anklopfen'); ?></td>
	<td>
		<input type="radio" name="callwaiting" value="1" id="ipt-callwaiting-1" <?php if ($callwaiting) echo 'checked="checked" '; ?>/>
			<label for="ipt-callwaiting-1"><?php echo __('an'); ?></label>
		<input type="radio" name="callwaiting" value="0" id="ipt-callwaiting-0" <?php if (! $callwaiting) echo 'checked="checked" '; ?>/>
			<label for="ipt-callwaiting-0"><?php echo __('aus'); ?></label>
	</td>
	<td>
		<small><?php echo __('Das Verhalten ist ggf. von Ihrem Endger&auml;t abh&auml;ngig.'); ?></small>
	</td>
	</tr>

<?php
if ( gs_get_conf( 'GS_USER_SELECT_CALLERID' ) ) {
	echo "<tr>\n";
	$numbers =  gs_user_callerids_get( $_SESSION['sudo_user']['name'] );
	if (isGsError( $numbers )) echo $numbers->getMsg();
	$sel = " selected";
	foreach ($numbers as $number) {
		if ($number['dest'] != 'external') continue;
		
		if ($number['selected'] == 1) $sel = "";
	}                                                                                                 
	
	echo "<td>", __('Angezeigte Rufnummer extern') ,"</td>\n";
	echo "<td>\n";
	echo '<select name="callerid_ext" size="1">', "\n";
	echo '<option value= ""',$sel,'>' , __('Standardnummer'),"</option>\n";
	foreach ($numbers as $number) {
		if ($number['dest'] != 'external') continue;
		$sel ="";
		$num = $number['number'];
		if ($number['selected'] == 1) $sel =" selected";
		echo '<option value="',$num,'" ',$sel ,">",$num,"</option>\n";
	}
	echo "</select>";
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	
	$sel_int = " selected";
	foreach ($numbers as $number) {
		if ($number['dest'] != 'internal') continue;
		if ($number['selected'] == 1) $sel_int = "";
	}                                                                                                 
	
	echo "<td>", __('Angezeigte Rufnummer intern') ,"</td>\n";
	echo "<td>\n";
	echo '<select name="callerid_int" size="1">', "\n";
	echo '<option value= ""',$sel_int,'>' , __('Standardnummer'),"</option>\n";
	foreach ($numbers as $number) {
		if ($number['dest'] != 'internal') continue;
		$sel_int = "";
		$num = $number['number'];
		if ($number['selected'] == 1) $sel_int =" selected";
		echo '<option value="',$num,'" ',$sel_int ,">",$num,"</option>\n";
	}
	echo "</select>";
	echo "</td>\n";
	echo "</tr>\n";
}
?>

<tr>
	<td colspan="6" class="quickchars r">
		<br />
		<br />
		<button type="submit">
			<img alt=" " src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
			<?php echo __('Speichern'); ?>
		</button>
	</td>
</tr>
</tbody>
</table>
</form>

<br />
<br />
<p class="small" style="max-width:48em;">
	<sup>[1]</sup>
	<?php echo __('Bei Anrufen nach extern wird u.U. trotz CLIR nur die Nebenstelle unterdr&uuml;ckt aber vom Provider die Hauptnummer (-0) des Anlagenanschlusses &uuml;bertragen.'); ?>
</p>
