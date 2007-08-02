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
	echo '<img alt="" src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


/*
echo '<br />', "\n";
echo '<h2>', __('Willkommen'), ', ', htmlEnt( $_SESSION['sudo_user']['info']['firstname'] .' '. $_SESSION['sudo_user']['info']['lastname'] ), '!</h2><br />', "\n";

echo '<p>', __('Ihre Durchwahl'), ': <b>', htmlEnt( $_SESSION['sudo_user']['info']['ext'] ), '</b></p><br />', "\n";
*/

?>

<div style="text-align:center; width:auto; margin:1em 100px 0 0;" />
<span style="color:#e00;"><?php echo (@$login_errmsg != '' && trim(@$_REQUEST['login_user']) != '') ? $login_errmsg : '&nbsp;'; ?></span>
<div style="border:1px solid #ddd; text-align:left; width:200px; margin:0 auto; background:#eee; padding:20px 25px;" />
<form method="get" action="<?php echo GS_URL_PATH; ?>">
<input type="hidden" name="s" value="home" />
<input type="hidden" name="m" value="home" />

<label for="ipt-login_user"><?php echo __('Benutzername'); ?>:</label><br />
<input name="login_user" id="ipt-login_user" type="text" size="15" maxlength="20" value="<?php echo @$_REQUEST['login_user']; ?>" style="width:150px; font-size:1.2em;" /><br />

<label for="ipt-login_pwd"><?php echo __('Pa&szlig;wort'); ?>:</label><br />
<input name="login_pwd" id="ipt-login_pwd" type="text" size="15" maxlength="20" value="" style="width:150px; font-size:1.2em;" /><br />

<br />
<div style="text-align:right;">
	<input type="submit" value="<?php echo __('Einloggen'); ?>" />
</div>
</form>
</div>
</div>

<br />



<br style="clear:right" />
