<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
*
* Author: Henning Holtschneider <henning@loca.net>
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

include_once( GS_DIR .'inc/util.php' );

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

if ($_REQUEST['action']=='save') {

	$dpbin = gs_user_pin_get($_SESSION['sudo_user']['name']);

	$typeoldpin = ($_REQUEST['oldpin']);

	if($dpbin != $typeoldpin) {
		$_REQUEST['error'] = "Alte PIN falsch!";
	} elseif ($_REQUEST['newpin'] != $_REQUEST['newpinrepeat']) {
		$_REQUEST['error'] = "Die beiden neuen PINs stimmen nicht &uuml;berein!";
	} elseif (strlen($_REQUEST['newpin']) > 10) {
		$_REQUEST['error'] = "Die neue PIN ist zu lang!";
	} elseif (strlen($_REQUEST['newpin']) < 4) {
		$_REQUEST['error'] = "Die neue PIN ist zu kurz!";
	} elseif (!preg_match("/^\d+$/", $_REQUEST['newpin'])) {
		$_REQUEST['error'] = "Die neue PIN ist nicht numerisch!";
	} else {
		$pinerror = gs_user_pin_set( $_SESSION['sudo_user']['name'], $_REQUEST['newpin'] );
		
		if (isGsError($pinerror))
			$_REQUEST['error'] = $pinerror->getMsg();
		else
			$_REQUEST['success'] = "Die PIN wurde erfolgreich ge&auml;ndert!";
        }
        
}

?>

<div style="max-width:600px;">
	<img alt=" " src="/gemeinschaft/crystal-svg/16/act/info.png" class="fl" />
		<p style="margin-left:22px;">
			Die PIN dient zur Anmeldung an dieser Weboberfl&auml;che sowie zur Einloggen einer Durchwahl an einem
			Telefon. Die PIN darf ausschlie&szlig;lich Ziffern enthalten, muss mindestens 4 und h&ouml;chstens 10 
			Stellen lang sein.
		</p>
</div>

<?php if ($_REQUEST['success']) { ?>
<table class="successbox">
	<tr><td>
	<?php echo __($_REQUEST['success']); ?>
	</td></tr>
</table>
<?php } elseif ($_REQUEST['error']) { ?>
<table class="errorbox">
	<tr><td>
	<?php echo __($_REQUEST['error']); ?>
	</td></tr>
</table>
<?php } ?>

<form method="post" action="<?php echo gs_url($SECTION, $MODULE); ?>">
<input type="hidden" name="action" value="save" />

<table cellspacing="1">
	<thead>
	<tr>
	<th colspan="2"><?php echo __('PIN &auml;ndern'); ?></th>
	</tr>
	</thead>
	<tbody>
	<tr class="odd">
		<td>
			<?php echo __('Alte PIN:'); /*//TRANSLATE ME*/ ?>
		</td>
		<td>
			<input type="password" name="oldpin" value="" size="10" maxlength="10" />
		</td>
	</tr>
	<tr class="even">
		<td>
			<?php echo __('Neue PIN:'); /*//TRANSLATE ME*/ ?>
		</td>
		<td>
			<input type="password" name="newpin" value="" size="10" maxlength="10" />
		</td>
	</tr>
	<tr class="odd">
		<td>
			<?php echo __('Neue PIN wiederholen:'); /*//TRANSLATE ME*/ ?>
		</td>
		<td>
			<input type="password" name="newpinrepeat" value="" size="10" maxlength="10" />
		</td>
	</tr>
	<tr>
		<td colspan="2" class="quickchars r">
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
