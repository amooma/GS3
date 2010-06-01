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
$can_continue = @$_SESSION['login_ok'];


$action = @$_REQUEST['action'];

$change_pwd_ok = false;
$change_pwd_msg = '';

if ($action === 'setpwd') {
	$newpwd = trim(@$_REQUEST['change_login_pwd1']);
	if ($newpwd !== trim(@$_REQUEST['change_login_pwd2'])) {
		$change_pwd_msg = __('Die Pa&szlig;w&ouml;rter stimmen nicht &uuml;berein!');
	} elseif ($newpwd === '') {
		$change_pwd_msg = __('Das Pa&szlig;wort darf nicht leer sein!');
	} else {
		gs_keyval_set('setup_pwd', $newpwd);
		$keyval_setup_pwd = $newpwd;
		$change_pwd_msg = __('Das Pa&szlig;wort wurde gespeichert.');
		$change_pwd_ok = true;
	}
}


?>

<br />
<br />

<div style="width:550px; border:1px solid #ccc; margin: 2em auto; padding:0 1em 1em 1em; background-color:#eee;">

<?php
#####################################################################
#                             login {
#####################################################################
if (! @$_SESSION['login_ok']) {
?>

<h1><?php echo __('Login'); ?></h1>
<p align="center" style="color:#e00;">
<?php
	if (! @$_SESSION['login_ok']
	&&  @$_REQUEST['action'] === 'login'
	&&  $login_errmsg != '') {
		echo $login_errmsg;
	} else {
		echo '&nbsp;';
	}
?>
</p>
<form method="post" action="<?php echo GS_URL_PATH; ?>setup/?step=login">
<input type="hidden" name="action" value="login" />
<table cellspacing="1" style="margin:0 auto;">
<tbody>
	<tr>
		<th class="transp"><?php echo __('Benutzer'); ?>:</th>
		<td class="transp"><input type="text" name="login_user" value="sysadmin" size="25" maxlength="30" /></td>
	</tr>
	<tr>
		<th class="transp"><?php echo __('Pa&szlig;wort'); ?>:</th>
		<td class="transp"><input type="password" name="login_pwd" value="" size="25" maxlength="30" /></td>
	</tr>
	<tr>
		<td class="transp">&nbsp;</td>
		<td class="transp"><br /><input type="submit" value="<?php echo __('Einloggen'); ?>" /></td>
	</tr>
</tbody>
</table>
</form>

<p>&nbsp;</p>

<?php
}
#####################################################################
#                             login }
#####################################################################


#####################################################################
#                         change password {
#####################################################################
//elseif ($keyval_setup_pwd == '') {
else {
?>

<h1><?php echo __('Pa&szlig;wort &auml;ndern'); ?></h1>
<p>
	<?php echo __('Bitte setzen Sie ein Pa&szlig;wort um den Setup-Bereich gegen unbefugte Zugriffe zu sch&uuml;tzen. Das Pa&szlig;wort sollte mindestens 8 Zeichen lang sein und aus Buchstaben, Zahlen und Sonderzeichen wie z.B. <code>#</code>, <code>!</code> oder <code>=</code> bestehen.'); ?>
</p>
<p>
	<?php echo __('<b>Achtung:</b> Vergessen Sie dieses Pa&szlig;wort nicht!'); ?>
</p>
<form method="post" action="<?php echo GS_URL_PATH; ?>setup/?step=login">
<input type="hidden" name="action" value="setpwd" />
<?php
if (@$change_pwd_msg != '') {
	echo '<p align="center" style="', ($change_pwd_ok ? 'border:2px solid #0e0; color: #090;' : 'border:2px solid #f00; color: #b00;') ,' padding:0.3em; margin:0.4em 0 0.3em 0;">', $change_pwd_msg ,'</p>' ,"\n";
}
?>
<table cellspacing="1" style="margin:0 auto;">
<tbody>
	<tr>
		<th class="transp r"><?php echo __('Benutzer'); ?></th>
		<td class="transp"><input type="text" name="change_login_user" value="sysadmin" size="25" maxlength="30" disabled="disabled" /></td>
	</tr>
	<tr>
		<th class="transp r"><?php echo __('Neues Pa&szlig;wort'); ?></th>
		<td class="transp"><input type="password" name="change_login_pwd1" value="" size="25" maxlength="30" /></td>
	</tr>
	<tr>
		<th class="transp r"><?php echo __('Pa&szlig;wort wiederholen'); ?></th>
		<td class="transp"><input type="password" name="change_login_pwd2" value="" size="25" maxlength="30" /></td>
	</tr>
	<tr>
		<td class="transp">&nbsp;</td>
		<td class="transp"><br /><input type="submit" value="<?php echo __('Pa&szlig;wort &auml;ndern'); ?>" /></td>
	</tr>
</tbody>
</table>
</form>


<?php
}
#####################################################################
#                         change password }
#####################################################################
?>


<hr />
<?php

//echo '<div class="fl"><a href="', GS_URL_PATH ,'setup/">', 'zur&uuml;ck' ,'</a></div>' ,"\n";
echo '<div class="fr">';
if (gs_keyval_get('setup_pwd') == '')
	$can_continue = false;
/*if ($can_continue) {
	switch ($GS_INSTALLATION_TYPE) {
		# "system-check" unnecessary for the GPBX
		case 'gpbx': $next_step = 'network'     ; break;
		default    : $next_step = 'system-check'; break;
	}*/
	$next_step = 'user';
	echo '<a href="', GS_URL_PATH ,'setup/?step=',$next_step ,'"><big>', __('weiter') ,'</big></a>';
/*} else {
	echo '<span style="color:#999;">', __('weiter') ,'</span>';
}*/
echo '</div>' ,"\n";
echo '<br class="nofloat" />' ,"\n";

?>
</div>