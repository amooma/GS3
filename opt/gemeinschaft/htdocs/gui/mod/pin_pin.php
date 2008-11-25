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
* + Amooma
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
include_once( GS_DIR .'inc/gs-fns/gs_user_pin_get.php' );
include_once( GS_DIR .'inc/gs-fns/gs_user_pin_set.php' );



echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

if ($_SESSION['sudo_user']['name'] === 'sysadmin') {
	echo 'Not possible for sysadmin. Use the setup.';
	return;
}


if ($_SESSION['sudo_user']['boi_host_id'] > 0
&&  $_SESSION['sudo_user']['boi_role'] !== 'gs') {
	# foreign user
	$show_pin          = true;
	$need_newpinrepeat = false;
} else {
	# normal Gemeinschaft user
	$show_pin          = false;
	$need_newpinrepeat = true;
}


$msg = '';
$success = false;

if (@$_REQUEST['action']==='save') {
		
	$db_pin = gs_user_pin_get($_SESSION['sudo_user']['name']);
	
	$type_old_pin   = trim(@$_REQUEST['oldpin']);
	$new_pin        = trim(@$_REQUEST['newpin']);
	if ($need_newpinrepeat) {
		$new_pin_repeat = trim(@$_REQUEST['newpinrepeat']);
	} else {
		$new_pin_repeat = $new_pin;
	}
		
	if ($db_pin != $type_old_pin) {
		$msg = __('Alte PIN falsch!');
	} elseif ($new_pin != $new_pin_repeat) {
		$msg = __('Die beiden neuen PINs stimmen nicht &uuml;berein!');
	} elseif (strlen($new_pin) < 4) {
		$msg = __('Die neue PIN ist zu kurz!');
	} elseif (strlen($new_pin) > 10) {
		$msg = __('Die neue PIN ist zu lang!');
	} elseif (! preg_match("/^\d+$/", $new_pin)) {
		$msg = __('Die neue PIN ist nicht numerisch!');
	} else {
		$pinerror = gs_user_pin_set( $_SESSION['sudo_user']['name'], $new_pin );
		if (isGsError($pinerror)) {
			$msg = $pinerror->getMsg();
		} else {
			$msg = __('Die PIN wurde ge&auml;ndert.');
			$success = true;
		}
	}
}

?>

<div style="max-width:600px;">
	<img alt=" " src="/gemeinschaft/crystal-svg/16/act/info.png" class="fl" />
	<p style="margin-left:22px;"><?php
		if (gs_get_conf('GS_GUI_AUTH_METHOD') !== 'webseal') {
			echo __('Die PIN dient zum Einloggen an einem Telefon, zum Abfragen des eigenen Anrufbeantworters von einem fremden Telefon aus sowie zur Anmeldung an dieser Weboberfl&auml;che. Die PIN darf ausschlie&szlig;lich Ziffern enthalten, muss mindestens 4 und h&ouml;chstens 10 Stellen lang sein.');
		} else {
			echo __('Die PIN dient zum Einloggen an einem Telefon sowie zum Abfragen des eigenen Anrufbeantworters von einem fremden Telefon aus. Die PIN darf ausschlie&szlig;lich Ziffern enthalten, muss mindestens 4 und h&ouml;chstens 10 Stellen lang sein.');
		}
	?></p>
</div>

<?php if ($msg != '') { ?>
<div class="<?php echo ($success ? 'successbox' : 'errorbox'); ?>">
	<?php echo $msg; ?>
</div>
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
			<?php echo __('Alte PIN'); ?>:
		</td>
<?php
if ($show_pin) {
	$oldpin = gs_user_pin_get($_SESSION['sudo_user']['name']);
	if (isGsError($oldpin) || ! is_string($oldpin)) {
		$oldpin = '';
	}
?>
		<td>
			<input type="text" name="oldpin" value="<?php echo htmlEnt($oldpin); ?>" readonly="readonly" size="10" maxlength="10" />
		</td>
<?php
} else {
?>
		<td>
			<input type="password" name="oldpin" value="" size="10" maxlength="10" />
		</td>
<?php
}
?>
	</tr>
	<tr class="even">
		<td>
			<?php echo __('Neue PIN'); ?>:
		</td>
		<td>
			<input type="<?php echo ($show_pin ? 'text':'password'); ?>" name="newpin" value="" size="10" maxlength="10" />
		</td>
	</tr>
<?php
if ($need_newpinrepeat) {
?>
	<tr class="odd">
		<td>
			<?php echo __('Neue PIN wiederholen'); ?>:
		</td>
		<td>
			<input type="password" name="newpinrepeat" value="" size="10" maxlength="10" />
		</td>
	</tr>
<?php
}
?>
	<tr>
		<td colspan="2" class="transp r">
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
