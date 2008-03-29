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

######################################################
##
##   ALL STRINGS IN HERE NEED TO BE TRANSLATED!
##
######################################################

defined('GS_VALID') or die('No direct access.');
include_once( GS_DIR .'inc/keyval.php' );
include_once( GS_DIR .'inc/pcre_check.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


if (gs_get_conf('GS_INSTALLATION_TYPE') !== 'gpbx') {
	echo 'Only for INSTALLATION_TYPE &quot;gpbx&quot;';
	return;
}


$action = @$_REQUEST['action'];
if ($action === 'save') {
	
	$canonize_outbound = (bool)@$_REQUEST['canonize_outbound'];
	gs_keyval_set('canonize_outbound', $canonize_outbound ? 'yes':'no');
	
	$canonize_intl_prefix = @$_REQUEST['canonize_intl_prefix'];
	$canonize_intl_prefix = preg_replace('/[^0-9*#]/', '', $canonize_intl_prefix);
	gs_keyval_set('canonize_intl_prefix', $canonize_intl_prefix);
	
	$canonize_country_code = @$_REQUEST['canonize_country_code'];
	$canonize_country_code = preg_replace('/[^0-9*#]/', '', $canonize_country_code);
	gs_keyval_set('canonize_country_code', $canonize_country_code);
	
	$canonize_natl_prefix = @$_REQUEST['canonize_natl_prefix'];
	$canonize_natl_prefix = preg_replace('/[^0-9*#]/', '', $canonize_natl_prefix);
	gs_keyval_set('canonize_natl_prefix', $canonize_natl_prefix);
	
	$canonize_natl_prefix_intl = (bool)@$_REQUEST['canonize_natl_prefix_intl'];
	gs_keyval_set('canonize_natl_prefix_intl', $canonize_natl_prefix_intl ? 'yes':'no');
	
	$canonize_area_code = @$_REQUEST['canonize_area_code'];
	$canonize_area_code = preg_replace('/[^0-9*#]/', '', $canonize_area_code);
	gs_keyval_set('canonize_area_code', $canonize_area_code);
	
	$canonize_local_branch = @$_REQUEST['canonize_local_branch'];
	$canonize_local_branch = preg_replace('/[^0-9*#]/', '', $canonize_local_branch);
	gs_keyval_set('canonize_local_branch', $canonize_local_branch);
	
	$canonize_special = @$_REQUEST['canonize_special'];
	$canonize_special = trim($canonize_special);
	if (is_valid_pcre( '/'.$canonize_special.'/' ))
		gs_keyval_set('canonize_special', $canonize_special);
	else
		echo '<b>', 'Invalid pattern!' ,'</b><br />' ,"\n";
	
	$canonize_cbc_prefix = @$_REQUEST['canonize_cbc_prefix'];
	$canonize_cbc_prefix = preg_replace('/[^0-9*#]/', '', $canonize_cbc_prefix);
	gs_keyval_set('canonize_cbc_prefix', $canonize_cbc_prefix);
	
}



?>
<p class="text"><?php echo __('Festlegung der vom Standort abh&auml;ngigen Rufnummernteile, die sich auf die Kanonisierung auswirken. Bitte halten Sie sich an die angegebenen Beispiele!'); ?></p>

<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="save" />

<table cellspacing="1">
<tbody>

<?php
	$val = (trim(gs_keyval_get('canonize_outbound')) === 'yes');
?>
<tr>
	<th class="r">
		&nbsp;
	</th>
	<td>
		<input type="checkbox" name="canonize_outbound" id="ipt-canonize_outbound" value="1"<?php if ($val) echo ' checked="checked"'; ?> />
		<label for="ipt-canonize_outbound"><b><?php echo __('Kanonisieren?'); ?></b></label>
	</td>
	<td class="transp">
		<small><?php echo __('Kanonisierung beim abgehenden W&auml;hlen aktiv? (empfohlen)'); ?></small>
	</td>
</tr>

<tr>
	<td colspan="3" class="transp"><small>&nbsp;</small></td>
</tr>

<?php
	$val = trim(gs_keyval_get('canonize_intl_prefix'));
?>
<tr>
	<th class="r">
		<label for="ipt-canonize_intl_prefix"><?php echo __('Pr&auml;fix international'); ?>:</label>
	</th>
	<td>
		<input type="text" name="canonize_intl_prefix" id="ipt-canonize_intl_prefix" value="<?php echo htmlEnt($val); ?>" size="10" maxlength="5" class="_admincfg" />
	</td>
	<td class="transp">
		<small><?php echo __('In Deutschland: <code>00</code>. Geben Sie hier nicht <code>+</code> ein!'); ?></small>
	</td>
</tr>

<?php
	$val = trim(gs_keyval_get('canonize_country_code'));
?>
<tr>
	<th class="r">
		<label for="ipt-canonize_country_code"><?php echo __('Landesvorwahl'); ?>:</label>
	</th>
	<td>
		<input type="text" name="canonize_country_code" id="ipt-canonize_country_code" value="<?php echo htmlEnt($val); ?>" size="10" maxlength="4" class="_admincfg" />
	</td>
	<td class="transp">
		<small><?php echo __('In Deutschland: <code>49</code>'); ?></small>
	</td>
</tr>

<?php
	$val = trim(gs_keyval_get('canonize_natl_prefix'));
?>
<tr>
	<th class="r">
		<label for="ipt-canonize_natl_prefix"><?php echo __('Ausscheidungsziffer'); ?>:</label>
	</th>
	<td>
		<input type="text" name="canonize_natl_prefix" id="ipt-canonize_natl_prefix" value="<?php echo htmlEnt($val); ?>" size="10" maxlength="3" class="_admincfg" />
	</td>
	<td class="transp">
		<small><?php echo __('In Deutschland: <code>0</code>'); ?></small>
	</td>
</tr>

<?php
	$val = (trim(gs_keyval_get('canonize_natl_prefix_intl')) === 'yes');
?>
<tr>
	<th class="r">
		&nbsp;
	</th>
	<td>
		<input type="checkbox" name="canonize_natl_prefix_intl" id="ipt-canonize_natl_prefix_intl" value="1"<?php if ($val) echo ' checked="checked"'; ?> />
		<label for="ipt-canonize_natl_prefix_intl"><b><?php echo __('Internat. mit Ortspr&auml;fix?'); ?></b></label>
	</td>
	<td class="transp">
		<small><?php echo __('In Deutschland: nein'); ?></small>
	</td>
</tr>

<?php
	$val = trim(gs_keyval_get('canonize_area_code'));
?>
<tr>
	<th class="r">
		<label for="ipt-canonize_area_code"><?php echo __('Ortsvorwahl'); ?>:</label>
	</th>
	<td>
		<input type="text" name="canonize_area_code" id="ipt-canonize_area_code" value="<?php echo htmlEnt($val); ?>" size="10" maxlength="5" class="_admincfg" />
	</td>
	<td class="transp">
		<small><?php echo __('z.B. Koblenz: <code>261</code>, Neuwied: <code>2631</code>, Berlin: <code>30</code>'); ?></small>
	</td>
</tr>

<?php
	$val = trim(gs_keyval_get('canonize_local_branch'));
?>
<tr>
	<th class="r">
		<label for="ipt-canonize_local_branch"><?php echo __('Kopfnummer'); ?>:</label>
	</th>
	<td>
		<input type="text" name="canonize_local_branch" id="ipt-canonize_local_branch" value="<?php echo htmlEnt($val); ?>" size="10" maxlength="8" class="_admincfg" />
	</td>
	<td class="transp">
		<small><?php echo __('Der auf die Ortsvorwahl folgende, f&uuml;r alle Durchwahlen feststehende Teil.'); ?></small>
	</td>
</tr>

<tr>
	<td colspan="3" class="transp"><small>&nbsp;</small></td>
</tr>

<?php
	$val = trim(gs_keyval_get('canonize_special'));
?>
<tr>
	<th class="r">
		<label for="ipt-canonize_special"><?php echo __('Besondere'); ?>:</label>
	</th>
	<td colspan="2">
		<input type="text" name="canonize_special" id="ipt-canonize_special" value="<?php echo htmlEnt($val); ?>" size="35" maxlength="50" class="_admincfg" />
	</td>
</tr>
<tr>
	<th class="r transp">
		&nbsp;
	</th>
	<td colspan="2" class="transp">
		<small><?php echo __('PCRE-Muster f&uuml;r Nummern ohne Vorwahl, z.B. <code>110</code>, <code>112</code>, <code>19222</code>. In Deutschland: <code>^1(?:1[0-9]{1,5}|9222)</code>'); ?></small>
	</td>
</tr>

<?php
	$val = trim(gs_keyval_get('canonize_cbc_prefix'));
?>
<tr>
	<th class="r">
		<label for="ipt-canonize_cbc_prefix"><?php echo __('Call-by-Call-Pr&auml;fix'); ?>:</label>
	</th>
	<td>
		<input type="text" name="canonize_cbc_prefix" id="ipt-canonize_cbc_prefix" value="<?php echo htmlEnt($val); ?>" size="10" maxlength="8" class="_admincfg" />
	</td>
	<td class="transp">
		<small><?php echo __('In Deutschland: <code>010</code>'); ?></small>
	</td>
</tr>

<tr>
	<td class="transp">&nbsp;</td>
	<td class="transp">
		<br />
		<input type="submit" value="<?php echo __('Speichern'); ?>" />
	</td>
	<td class="transp">&nbsp;</td>
</tr>

</tbody>
</table>

</form>

<br />
