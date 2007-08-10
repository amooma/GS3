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


if (@$_REQUEST['action']=='reboot') {
	
	gs_prov_phone_checkcfg_by_ext( $_SESSION['sudo_user']['info']['ext'], true );
	
} elseif (@$_REQUEST['action']=='setcomment') {
	
	$comment = rTrim(mb_subStr(trim( @$_REQUEST['comment'] ),0,200));
	gs_user_comment_set( $_SESSION['sudo_user']['name'], $comment );
	
}


echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt="" src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";


echo '<br />', "\n";
echo '<h2>', __('Willkommen'), ', ', htmlEnt( $_SESSION['sudo_user']['info']['firstname'] .' '. $_SESSION['sudo_user']['info']['lastname'] ), '!</h2><br />', "\n";

echo '<p>', __('Ihre Durchwahl'), ': <b>', htmlEnt( $_SESSION['sudo_user']['info']['ext'] ), '</b></p><br />', "\n";

?>

<form method="get" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="setcomment" />
<?php echo __('Ihr Kommentar f&uuml;r Kollegen (z.B. &quot;Feierabend&quot;)'); ?>:<br />
<?php
$comment = gs_user_comment_get( $_SESSION['sudo_user']['name'] );
?>
<textarea name="comment" cols="40" rows="2"><?php echo htmlEnt($comment); ?></textarea>
<br />
<input type="submit" value="<?php echo __('Speichern'); ?>" />
</form>

<br />

<div class="fr">
<form method="get" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="reboot" />
<input type="submit" value="<?php echo __('Telefon neustarten'); ?>" />
</form>
</div>

<br style="clear:right" />
